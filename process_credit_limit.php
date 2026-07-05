 <?php
    session_start();
    include("Connections/Conn.php");
    if (isset($_POST['save_credit_limit'])) {

        $credit_option = $_POST['credit_option'];

        if (isset($credit_option) && isset($_POST['credit_limit']) && isset($_POST['hospital_no'])) {
            $credit_limit = (int)$_POST['credit_limit'];
            $hospital_no = $_POST['hospital_no'];
            $affectedRows = 0;
            $message = '';
            $alertType = 'danger';
            $desc = '';
            $action = '';

            switch ($credit_option) {
                case '1': // Appointment
                    $sql = "UPDATE apptm SET cr = :credit_limit WHERE hospital_no = :hospital_no AND status = 'checkin'";
                    $successMsg = "Credit limit updated successfully for current appointment(s)";
                    $failMsg = "No active check-in found";
                    $desc = "Updated appointment credit limit to $credit_limit for $hospital_no";
                    $action = "Update Appointment Credit";
                    break;

                case '2': // Admission
                    $sql = "UPDATE admission SET adm_credit_limit = :credit_limit WHERE hospital_no = :hospital_no AND adm_status = '3'";
                    $successMsg = "Credit limit updated successfully for current admission";
                    $failMsg = "No active admission found";
                    $desc = "Updated admission credit limit to $credit_limit for $hospital_no";
                    $action = "Update Admission Credit";
                    break;

                case '3': // Enrollee - All future
                    $sql = "UPDATE enrollee SET credit_limit = :credit_limit WHERE hospital_no = :hospital_no";
                    $successMsg = "Credit limit updated successfully for current and all future visits";
                    $failMsg = "No active data found";
                    $desc = "Updated enrollee credit limit to $credit_limit for $hospital_no";
                    $action = "Update Enrollee Credit";
                    break;

                case '4': // Department min_amount_adm
                    $sql = "
                        UPDATE department d
                        JOIN admission a ON d.sn = a.dept_id
                        SET d.min_amount_adm = :credit_limit
                        WHERE a.hospital_no = :hospital_no AND a.adm_status = '3'
                    ";
                    $successMsg = "Department Mina updated successfully";
                    $failMsg = "No matching department/admission found or already up to date";
                    $desc = "Updated department minimum admission amount to $credit_limit for $hospital_no";
                    $action = "Update Department Credit";
                    break;

                case '5': // Insurance (Family)
                    $sql = "
                        UPDATE insurance_tbl i
                        JOIN enrollee e ON e.hmo_no = i.insurance_no
                        SET i.credit_setup = :credit_limit
                        WHERE e.hospital_no = :hospital_no AND i.insurance_type = 'Family'
                    ";
                    $successMsg = "Family folder credit limit updated successfully";
                    $failMsg = "No matching record found or not a family folder member";
                    $desc = "Updated family folder insurance credit to $credit_limit for $hospital_no";
                    $action = "Update Insurance Credit";
                    break;

                case '6': // Hospital-wide default
                    $sql = "UPDATE hospital_details SET credit_limit_status = :credit_limit WHERE sn = 1";
                    $successMsg = "General credit limit updated for all patients. You must log out for it to take effect.";
                    $failMsg = "No active data found";
                    $desc = "Updated hospital-wide credit limit to $credit_limit";
                    $action = "Update Global Credit Limit";
                    break;

                default:
                    echo '<div class="alert alert-danger"><h3>Invalid credit option selected.</h3></div>';
                    return;
            }

            // Execute SQL
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':credit_limit', $credit_limit, PDO::PARAM_INT);
            if (strpos($sql, ':hospital_no') !== false) {
                $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            }

            $stmt->execute();
            $affectedRows = $stmt->rowCount();

            // Prepare output
            if ($affectedRows > 0) {
                $alertType = 'success';
                ///$message = $successMsg . ': ' . $affectedRows;
                $message = $successMsg;

                // Log action
                $setdatetime = date("Y-m-d H:i:s");

                // Prevent duplicate logs
                $chk = $db->prepare("SELECT * FROM patient_staff_logs WHERE descriptions = :desc AND date_and_time = :datetime");
                $chk->bindParam(':desc', $desc);
                $chk->bindParam(':datetime', $setdatetime);
                $chk->execute();

                if ($chk->rowCount() == 0 && $desc != '') {
                    $log = $db->prepare("INSERT INTO patient_staff_logs (descriptions, staff_name, action, date_and_time)
                                         VALUES (:descriptions, :staff_name, :action, :date_and_time)");
                    $log->bindParam(':descriptions', $desc);
                    $log->bindParam(':staff_name', $_SESSION['fullname']);
                    $log->bindParam(':action', $action);
                    $log->bindParam(':date_and_time', $setdatetime);
                    $log->execute();
                }
            } else {
                $message = $failMsg;
            }

            echo '<div class="alert alert-' . $alertType . '" id="creditAlert">
                <h3>' . $message . '</h3>
            </div>';
        }
    }

    if (isset($_GET["can_y_credit"])) {

        $setdate_cancel = date('Y-m-d H:00:00');

        $can_y_credit = base64_decode(base64_decode(base64_decode($_GET["can_y_credit"])));
        $pp = explode('____', $can_y_credit); // Format: hospital_no____type____amount

        $hospital_no = $pp[0];
        $type = $pp[1];
        $amount = $pp[2];

        $staff_name = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Unknown Staff';
        $payment_remarks = "Personal Credit Limit Cancelled Amount: {$amount} @ Date: {$setdate_cancel}";

        try {
            $db->beginTransaction();
            $rowsUpdated = 0;

            if ($type == 'personal') {
                $stmt = $db->prepare("UPDATE enrollee SET credit_limit = 0 WHERE hospital_no = ?");
                $stmt->execute([$hospital_no]);
                $rowsUpdated += $stmt->rowCount();
            } elseif ($type == 'apptm') {
                $stmt = $db->prepare("UPDATE apptm SET cr = 0 WHERE hospital_no = ? AND status='checkin'");
                $stmt->execute([$hospital_no]);
                $rowsUpdated += $stmt->rowCount();
            } elseif ($type == 'personal_adm') {
                $stmt = $db->prepare("UPDATE admission SET adm_credit_limit = 0 WHERE hospital_no = ? AND adm_status='3'");
                $stmt->execute([$hospital_no]);
                $rowsUpdated += $stmt->rowCount();
            } elseif ($type == 'family') {

                // get hmo_no and 
                $stm = $db->prepare("SELECT hmo_no FROM enrollee WHERE hospital_no = ?");
                $stm->execute([$hospital_no]);
                if ($stm->rowCount() == 1) {
                    $row_return = $stm->fetch(PDO::FETCH_ASSOC);
                    $hmo_no = $row_return['hmo_no'];
                    $stmt = $db->prepare("UPDATE insurance_tbl SET credit_setup = 0 WHERE insurance_no = ?");
                    $stmt->execute([$hmo_no]);
                    $rowsUpdated += $stmt->rowCount();
                }

                $payment_remarks = $payment_remarks . ' /HMO NO.:  ' . $hmo_no;
            }

            // If any update occurred, proceed to insert remark
            if ($rowsUpdated > 0) {
                // Check if the remark already exists
                $checkStmt = $db->prepare("
                    SELECT COUNT(*) 
                    FROM patients_remarks_tbl 
                    WHERE hospital_no = ? AND remark = ? AND staff_name = ?
                ");
                $checkStmt->execute([$hospital_no, $payment_remarks, $staff_name]);
                $exists = $checkStmt->fetchColumn();

                if (!$exists) {
                    $insertStmt = $db->prepare("
                        INSERT INTO patients_remarks_tbl 
                        (hospital_no, service_list, total_amount, remark, staff_name) 
                        VALUES (:hospital_no, :list_desc, :cash, :remark, :staff_name)
                    ");
                    $insertStmt->execute([
                        ':hospital_no' => $hospital_no,
                        ':list_desc' => null,
                        ':cash' => $amount,
                        ':remark' => $payment_remarks,
                        ':staff_name' => $staff_name
                    ]);
                    echo '<div class="alert alert-success" id="creditAlert">
                    <h3>Credit Limit Canceled successfully.</h3>
                </div>';
                } else {
                    echo '<div class="alert alert-danger" id="creditAlert">
                    <h3>Credit Limit already exists. No duplicate inserted.</h3>
                </div>';
                }
            } else {

                echo '<div class="alert alert-danger" id="creditAlert">
                        <h3>No matching record was updated. Credit Limit not canceled.</h3>
                      </div>';
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();

            echo '<div class="alert alert-danger">
            <h3>Error occurred</h3>' . $e->getMessage() .
                '</div>';
        }
    }


    echo '<script>
    setTimeout(function() {
        var alertBox = document.getElementById("creditAlert");
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(function() {
                alertBox.remove();
            }, 500);
        }
    }, 3000); // 10 seconds
</script>';
