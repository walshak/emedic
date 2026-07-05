<?php
require_once('Connections/Conn.php');

if (isset($_POST["hospital_no"])) {

    $hospital_no = $_POST["hospital_no"];
    $today = date("Y-m-d");

    try {
        $db->beginTransaction();

        // Check only admissions needing today's billing
        $stmt_ADM = $db->prepare("
        SELECT * FROM admission 
        WHERE adm_status = '3' 
        AND hospital_no = :hospital_no 
        AND admit_type = 'admit_p'");
        $stmt_ADM->execute([
            ':hospital_no' => $hospital_no
        ]);

        if ($stmt_ADM->rowCount() > 0) {
            $row = $stmt_ADM->fetch(PDO::FETCH_ASSOC);
            $date_admit = substr($row['date_admit'], 0, 10);
            $app_no = $row['app_no'];
            $billable = strtolower(trim($row['billable']));
            $room_bed = $row['room_bed'];

            // Skip if discharge requested or not billable
            $chkDischarge = $db->prepare("SELECT 1 FROM discharge_fellowup WHERE hospital_no = ?");
            $chkDischarge->execute([$hospital_no]);


            // Billing window: admission to today
            $start = new DateTime($date_admit);
            $end = new DateTime($today);
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end);

            // Fetch reference rows once
            $refStmt = $db->prepare("
            SELECT * FROM patient_ap_services 
            WHERE hospital_no = :hospital_no 
			AND cat_type != 'Bed Space/Accommodation'
            AND tag = 'auto' 
            ORDER BY date_entry DESC");

            $refStmt->execute([':hospital_no' => $hospital_no]);
            $refRows = $refStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($refRows)) {
                $date_entry_first = $refRows[0]['date_entry'];
                $formatted_date_entry = date("Y-m-d", strtotime($date_entry_first));
                echo 'FIRST DAY: ' . $formatted_date_entry . '<br>';
            }

            $totalRows = count($refRows);
            if ($today == $formatted_date_entry && $totalRows > 0) {
                echo "Already Done/Update to date";
                exit;
            }

            if (empty($refRows)) {
                // Update latest date from auto_deduct2 if no auto_deduct rows found
                $stmt = $db->prepare("
                SELECT MAX(DATE(date_entry)) AS latest_date
                FROM patient_ap_services 
                WHERE hospital_no = :hospital_no
				AND cat_type != 'Bed Space/Accommodation'
                AND DATE(date_entry) BETWEEN :date_from AND :date_to
                AND tag = 'auto2'");
                $stmt->execute([
                    ':hospital_no' => $hospital_no,
                    ':date_from' => $date_admit,
                    ':date_to' => $today
                ]);
                $max_date = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($max_date['latest_date'])) {
                    $db->prepare("
                    UPDATE patient_ap_services 
                    SET tag = 'auto' 
                    WHERE hospital_no = :hospital_no 
                    AND DATE(date_entry) = :latest_date
                    AND tag = 'auto2'")->execute([
                        ':hospital_no' => $hospital_no,
                        ':latest_date' => $max_date['latest_date']
                    ]);
                }
            } else {
                echo '<br>' . '<b>NO AUTO-DEDUCTED FOUND</b><br>';
            }

            // Main loop for billing check and insert
            foreach ($period as $date) {
                echo $charge_date = $date->format("Y-m-d");
                echo '<br>';
                $chk = $db->prepare("
                SELECT remarks, tag, sn FROM patient_ap_services 
                WHERE hospital_no = :hospital_no 
				AND cat_type != 'Bed Space/Accommodation'
                AND DATE(date_entry) = :charge_date 
                AND (tag = 'auto' OR tag = 'auto2')
            ");
                $chk->execute([
                    ':hospital_no' => $hospital_no,
                    ':charge_date' => $charge_date
                ]);

                echo $chk->rowCount() . '-' . $charge_date . '<br>';

                if ($chk->rowCount() > 0 && $charge_date != $today) {
                    continue;
                }

                if ($chk->rowCount() == 0 && !empty($refRows)) {
                    foreach ($refRows as $refRow) {
                        $claim_amt = $refRow['claim_amt'] / $refRow['qty'];
                        $claim_interest = $refRow['interest'] / $refRow['qty'];
                        $amt_paying = $refRow['pay'] / $refRow['qty'];

                        $invoice_no = date('m') . sprintf('%06d', mt_rand(0, 999999));
                        $remarks = '';
                        $tag = '';

                        $tag = ($refRow['tag'] === 'auto' || $refRow['tag'] === 'auto2') ? 'auto' : $refRow['tag'];

                        if ($charge_date != $today && $tag === 'auto') {
                            $tag = 'auto2';
                        }


                        $one = 1;
                        $transact_date = '';
                        $CurDateHR = $charge_date . ' 08:00:00';
                        $cat_type_check = $refRow['cat_type'];
                        $drug_sn = $refRow['drug_sn'];
                        $invoice_by = 'system';

                        $check = $db->prepare("SELECT COUNT(*) FROM patient_ap_services 
                        WHERE DATE(date_entry) = :date_entry AND hospital_no = :hospital_no  AND cat_type = :cat_type AND drug_sn = :drug_sn 
						AND invoice_by = 'system' AND cat_type != 'Bed Space/Accommodation'  
						AND (tag = 'auto' OR tag = 'auto2')");
                        $check->execute([
                            ':date_entry' => $charge_date,
                            ':hospital_no' => $hospital_no,
                            ':cat_type' => $cat_type_check,
                            ':drug_sn' => $drug_sn
                        ]);

                        if ($check->fetchColumn() == 0) {


                            $sql = $db->prepare("INSERT INTO patient_ap_services 
						(app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, tag, hosp_price, claim_amt, interest, qty, remarks, invoice_status, invoice_no, invoice_date, prepared_by, date_entry, transact_date, pay, pay_mode, cr,invoice_by) 
						VALUES (:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :tag, :hosp_price, :claim_amt, :interest, :qty, :remarks, :invoice_status, :invoice_no, :invoice_date, :prepared_by, :date_entry, :transact_date, :pay, :pay_mode, :cr,:invoice_by)");

                            $sql->execute([
                                ':app_no' => $app_no,
                                ':hospital_no' => $hospital_no,
                                ':access' => $refRow['access'],
                                ':serv_group' => $refRow['serv_group'],
                                ':cat_type' => $refRow['cat_type'],
                                ':dept_id' => $refRow['dept_id'],
                                ':drug_sn' => $refRow['drug_sn'],
                                ':item_services' => $refRow['item_services'],
                                ':tag' => $tag,
                                ':hosp_price' => $refRow['hosp_price'],
                                ':claim_amt' => $claim_amt,
                                ':interest' => $claim_interest,
                                ':qty' => $one,
                                ':remarks' => $remarks,
                                ':invoice_status' => $one,
                                ':invoice_no' => $invoice_no,
                                ':invoice_date' => $today,
                                ':prepared_by' => $refRow['prepared_by'],
                                ':date_entry' => $CurDateHR,
                                ':transact_date' => NULL,
                                ':pay' => $amt_paying,
                                ':pay_mode' => $refRow['pay_mode'],
                                ':cr' => $one,
                                ':invoice_by' => $invoice_by
                            ]);
                        }
                    }
                }
            }

            $db->prepare("
            UPDATE admission 
            SET accom_gen_date = :today 
            WHERE hospital_no = :hospital_no AND adm_status = '3'")->execute([
                ':today' => $today,
                ':hospital_no' => $hospital_no
            ]);

            if (!empty($refRows)) {
                $db->prepare("
                UPDATE patient_ap_services
                SET remarks = CASE 
                    WHEN remarks = 'auto' THEN 'auto2'
                    ELSE remarks
                END
                WHERE hospital_no = :hospital_no 
                AND DATE(date_entry) = :latest_date 
                AND (tag = 'auto')
            ")->execute([
                    ':hospital_no' => $hospital_no,
                    ':latest_date' => $formatted_date_entry
                ]);
            }

            $db->commit();
            echo "Billing check and update completed successfully.";
        } else {
            echo "Billing Has Been Created Already for count = " . $stmt_ADM->rowCount();;
        }
    } catch (Exception $e) {
        $db->rollBack();
        echo "Transaction failed: " . $e->getMessage();
    }
}
