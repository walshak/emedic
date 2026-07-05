<?php
$mgt_notes = null;
$mgt_template = null;
$template_id = null;

if (isset($_POST["hospital_manage_save"])) {


    try {
        // Start the transaction
        $db->beginTransaction();

        // Prepare and execute the update statement
        $manage_status = $_POST['manage_status'];
        $manage_as_type = $_POST['manage_as_type'];
        $patient_manage_hx = $_POST['patient_manage_hx'];
        $doctor_id = $_POST['doctor_id'];
        $hospital_no_manage = $_POST['hospital_no_manage'];
        $vip = $_POST['vip'];

        if ($manage_as_type == 'Cancel' && $vip == 0) {
            $doctor_id = '';
        } elseif ($manage_as_type == 'Cancel' && $vip == 1) {
            $doctor_id = '';
            $vip_setup_status = 0;
        } elseif ($manage_as_type == 'VIP') {
            $vip_setup_status = 1;
        } else {
            $vip_setup_status = 0;
        }

        $update = $db->prepare("UPDATE enrollee SET patient_manage_by = ?, patient_manage_hx = ?, vip = ? WHERE hospital_no = ?");
        $update->bindParam(1, $doctor_id);
        $update->bindParam(2, $patient_manage_hx);
        $update->bindParam(3, $vip_setup_status);
        $update->bindParam(4, $hospital_no_manage);
        $update->execute();

        if ($vip == 1 && $manage_status == 2 && $manage_as_type == 'Cancel') {
            /// unset other memebers
            $update = $db->prepare("UPDATE manage_patients_vip_staff SET status = 0 WHERE hospital_no = '$hospital_no_manage'");
            $update->execute();
        } elseif ($manage_as_type == 'VIP') {
            if (!empty($_POST["staff_list"]) && is_array($_POST["staff_list"])) {
                foreach ($_POST["staff_list"] as $staff_list_id) {
                    $pp = explode("__", $staff_list_id);
                    $id = $pp[0];
                    $rights = $pp[1];

                    $checkSQL = "SELECT * FROM manage_patients_vip_staff WHERE hospital_no = ? AND user_id = ? AND status=1";
                    $checkStmt = $db->prepare($checkSQL);
                    $checkStmt->execute(array($hospital_no_manage, $id));
                    if ($checkStmt->rowCount() == 0) {
                        $insertSQL = "INSERT INTO manage_patients_vip_staff (hospital_no, user_id, rights, who_created) VALUES (?, ?, ?, ?)";
                        $sql = $db->prepare($insertSQL);
                        $save = $sql->execute(array($hospital_no_manage, $id, $rights, $_SESSION['id']));
                    }
                }
            }


            /// exit;
        } else {


            if ($manage_status == 1 or $manage_status == 2) {
                $update = $db->prepare("UPDATE manage_patients_vip_staff SET status = 0 WHERE hospital_no = '$hospital_no_manage'");
                $update->execute();
            }

            if ($manage_status == 1 or $manage_status == 0) {
                // change
                $checkSQL = "SELECT * FROM manage_patients_vip_staff WHERE hospital_no = ? AND user_id = ? AND status=1";
                $checkStmt = $db->prepare($checkSQL);
                $checkStmt->execute(array($hospital_no_manage, $_SESSION['id']));
                if ($checkStmt->rowCount() == 0) {
                    $insertSQL = "INSERT INTO manage_patients_vip_staff (hospital_no, user_id, rights, who_created) VALUES (?, ?, ?, ?)";
                    $sql = $db->prepare($insertSQL);
                    $save = $sql->execute(array($hospital_no_manage, $_SESSION['id'], $_SESSION['rights'], $_SESSION['id']));
                }
            }
        }


        // Commit the transaction
        $db->commit();
        header("Location: patient.php?hosp_no=$hospital_no_manage&mgt");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $db->rollBack();
        echo "Failed to update and insert data: " . $e->getMessage();
    }
}

if (isset($_GET["del_admission_request"])) {

    $hospital_no = $_GET['hosp_no'];

    $insertSQL = 'DELETE FROM admission WHERE hospital_no = ?  AND  adm_status = 0 ';
    $sql = $db->prepare($insertSQL);
    $save = $sql->execute(array($hospital_no));

    $error_status = 1;
    $error_msg = 'Error : Admission Request Not CANCELLED ';

    if ($save == true) {
        $error_status = 2;
        $error_msg = 'Success : Admission Request CANCELLED Successfully!';
        $adm_status = 5;
    }
}

if (isset($_POST["add_accomodation"])) {

    $reason_adm = $_POST['reason_adm'];
    $created_by = $_POST['created_by'];
    $created_by_name = $_POST['created_by_name'];
    $appointment_number = $_POST['appointment_number'];
    $hosp_no = $_POST['hosp_no'];
    $date_admit = date('Y-m-d H:i:s');
    $adm_status = '0';
    $admit_type = 'admit_o';
    $stmt2 = $db->query("SELECT * FROM admission WHERE hospital_no='$hosp_no' AND (adm_status = 0 or adm_status = 3)");

    if ($stmt2->rowCount() == 0) {
        $insertSQL = "INSERT INTO  admission (hospital_no, app_no, doc_incharge, adm_doctor_id, reason_adm, adm_status, date_admit, admit_type) 
		values (?, ?, ?, ?, ?, ?, ?,?)";
        $sql = $db->prepare($insertSQL);
        $save = $sql->execute(array($hosp_no, $appointment_number, $created_by_name, $created_by, $reason_adm, $adm_status, $date_admit, $admit_type));
        $error_status = 1;
        $error_msg = 'Error : Admission Request Not Saved';

        if ($save) {
            $error_status = 2;
            $error_msg = 'Success : Successfully. Click on the Admission Data Tab to Continue ...';
            $current_tab = 'adm';
        }
    } else {
        $error_status = 2;
        $error_msg = 'Success : Already on admission ';
    }
}

if (isset($_POST["load-mgt-template"])) {
    $current_tab = 'mgt';

    if ($_POST['template_select'] == 'blank') {
        $mgt_template = ' -- Blank --';
    } else {
        $template_id = intVal(cleanInput($_POST['template_select']));
        $stmt = $db->prepare("SELECT template FROM  services_templates  WHERE id = ? ");
        $stmt->execute(array($template_id));
        if ($stmt->rowCount() > 0) {
            $mgt_template_arr = $stmt->fetch(PDO::FETCH_ASSOC);
            $mgt_template = $mgt_template_arr['template'];
        }
    }
}

if (isset($_POST['save_progress_note_button'])) {

    $error_status = 1;
    $error_msg = 'Error : Progress note is not saved';

    $hospital_no = $_POST['hospital_no'];
    $notes = $_POST['pro_note_' . $hospital_no];

    $appointment_number = $_POST['appointment_number'];
    $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, 'DR', 'note',  $_SESSION['fullname'], null,  $_SESSION["id"]);
    if ($save_note) {
        $error_status = 2;
        $error_msg = 'Success : Progress note is saved';
    }
    lockApppointment($db, $appointment_number);
    $current_tab = 'adm';
}

////////////// Delete note action
if (isset($_POST['delete-note-btn'])) {
    $error_status = 1;
    $error_msg = 'Error : Something went wrong';
    $sn = intval($_POST['id']);
    $service_id = intval($_POST['service_id']);
    $notes_type = $_POST['notes_type'];
    $app_no = $_POST['app_no'];


    $delete = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
    $deleted = $delete->execute(array($sn));

    if ($deleted) {
        if ($notes_type == 'serv_review') {
            /// delete payment
            $delete = $db->prepare("DELETE FROM  patient_ap_services  WHERE app_no = ? AND drug_sn = ?  AND serv_group = 'Review' ");
            $deleted = $delete->execute(array($app_no, $service_id));
        }
        $error_status = 2;
        $error_msg = 'Success : Note Deleted';
    }
}

///////////////////// Update note action
if (isset($_POST['save_edit_note_btn'])) {
    $error_status = 1;
    $error_msg = 'Error : Note is not saved';
    $sn = intval($_POST['id']);
    $notes = $_POST['edit_note_editor'];
    $hospital_no = $_POST['hospital_no'];
    $date_entry2 = $_POST['date_entry2'];

    $now_setdate = date('Y-m-d H:i:s');

    $plainText = strip_tags($notes);
    $safeText = htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8');
    $wordCount = str_word_count($safeText);

    if (!empty($notes) && trim($notes) !== '' && $wordCount >= 5) {

        $check = $db->prepare("SELECT * FROM notes WHERE sn = ? ");
        $check->execute(array($sn));
        if ($check->rowCount() > 0) {
            $row = $check->fetch(PDO::FETCH_ASSOC);
            $prev_note = $row['notes'];

            if ($notes != $prev_note) {

                $stmt = $db->prepare("SELECT COUNT(*) FROM notes WHERE date_entry2 = ? AND hospital_no =?");
                $stmt->execute(array($date_entry2, $hospital_no));
                $rowCount = $stmt->fetchColumn() + 1;

                $update = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
                $update->execute(array($sn));

                $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, service_id,dept_id,date_entry2,no_updates)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)');
                $updated = $stmt->execute(
                    array(
                        $row['app_no'],
                        $row['hospital_no'],
                        $notes,
                        $row['tag'],
                        $row['notes_type'],
                        $_SESSION['fullname'],
                        $now_setdate,
                        $row['specialty'],
                        $_SESSION['id'],
                        $row['service_id'],
                        $_SESSION['dept_id'],
                        $date_entry2,
                        $rowCount
                    )
                );

                if ($updated) {
                    $error_status = 2;
                    $error_msg = 'Success : Note Saved';
                }
            } else {
                $error_msg = 'Error : Note same as previous';
            }
        } else {
            $error_msg = 'Error : Note not found!';
        }
    } else {
        $error_msg = 'Error : Invalid notes! Please type at least 5 more words before you continue.';
    }
    ///   header("location:patient.php?hosp_no=$hospital_no");
}

if (isset($_POST['delete_btn'])) {
    /// delete specialist request 
    $sn = $_POST['id'];
    $insertSQL = 'DELETE FROM notes WHERE sn=?';
    $sql = $db->prepare($insertSQL);
    $save = $sql->execute(array($sn));
}
if (isset($_POST['delete_btn_supa'])) {
    /// delete specialist request 
    $sn = $_POST['id'];
    $update = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
    $update->execute(array($sn));
}

////////////////////////////// ADMISSION ACTION

if (isset($_POST['cancel_discharge_request_btn'])) {

    $sn = $_POST['sn'];
    $appt_no = $_POST['appt_no'];
    $hospital_no = $_POST['hospital_no'];
    $patient_name = $_POST['patient_name'];

    $stmt = $db->prepare('SELECT * FROM discharge_fellowup WHERE hospital_no=?');
    $stmt->execute(array($hospital_no));
    if ($stmt->rowCount() >  0) {

        try {
            $db->beginTransaction();

            $insertSQL = 'DELETE FROM  discharge_fellowup WHERE hospital_no=:hospital_no';
            $sql = $db->prepare($insertSQL);
            $sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $save = $sql->execute();

            $stmt = $db->prepare("SELECT claim_amt,pay,sn,qty FROM patient_ap_services WHERE hospital_no=:hospital_no and invoice_status='1' and remarks='auto_deduct' and paystatus='0'");
            $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                while ($row_rs = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $claim_amt = $row_rs['claim_amt'] / $row_rs['qty'];
                    $pay = $row_rs['pay'] / $row_rs['qty'];
                    $search = $row_rs['sn'];
                    $one = '1';
                    //// reset invoice ////
                    $zero = '0';
                    $emty = '';
                    $updateSQL = "UPDATE patient_ap_services 
                SET invoice_status=:invoice_status,claim_amt=:claim_amt,pay=:pay,drug_status=:drug_status,
                invoice_date=:invoice_date,invoice_by=:invoice_by ,qty=:qty 
                WHERE sn=:sn";
                    $sql = $db->prepare($updateSQL);
                    $sql->bindParam(':invoice_status', $zero, PDO::PARAM_STR);
                    $sql->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                    $sql->bindParam(':pay', $pay, PDO::PARAM_STR);
                    $sql->bindParam(':drug_status', $zero, PDO::PARAM_STR);
                    $sql->bindParam(':invoice_date', $emty, PDO::PARAM_STR);
                    $sql->bindParam(':invoice_by', $emty, PDO::PARAM_STR);
                    $sql->bindParam(':qty', $one, PDO::PARAM_STR);
                    $sql->bindParam(':sn', $search, PDO::PARAM_STR);
                    $sql->execute();
                }
            }

            $db->commit();

            if ($save == true) {
                $error_status = 2;
                $error_msg = 'Success : Discharge Request Cancelled ';
                $adm_status = 3;
            } else {
                $error_status = 1;
                $error_msg = 'Error : Discharge Cancel Request Not Successfully!';
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $error_status = 1;
            $error_msg = 'Error : ' . $e->getMessage();
        }
    }
}

///////////////////// SEE SPECIALIST REQUEST //////////////////
if (isset($_POST['see_a_specialist_btn'])) {

    $appointment_type = $_POST['appointment_type'];

    if ($appointment_type == "see_specialist") {
        $error_status = 1;
        $error_msg = 'Error : Request note not sent';

        $hospital_no = cleanInput($_POST['hosp_no']);
        $notes = cleanInput($_POST['request_note_description']);

        $specialist = cleanInput($_POST['specialist']);

        if ($specialist != '') {
            $notes = $notes . '<br>' . $specialist;
        }

        $appointment_number = cleanInput($_POST['app_no']);
        $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, 'DR', 'REQ_REMINDER',  $_SESSION['fullname'], null,  $_SESSION["id"]);
        if ($save_note) {
            $error_status = 2;
            $error_msg = 'Success : Request sent';
            lockApppointment($db, $appointment_number);
        }
    } else {

        // Retrieve form data
        $appointment_type = $_POST['appointment_type'];
        $request_note_description = $_POST['request_note_description'];
        $appointment_date = $_POST['appointment_date']; ///. ' ' . $_POST['appointment_time']; // Combine date and time
        $hospital_no = $_POST['hosp_no'];
        $app_no = $_POST['app_no'];

        // Prepare the SQL statement
        $sql = "INSERT INTO apptm_fellowup (hospital_no,appt_no, request_note_description, date_time, service_type,doctor_name) 
            VALUES (:hospital_no,:appt_no, :request_note_description, :date_time, :service_type, :doctor_name)";

        // Prepare the statement
        $stmt = $db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':hospital_no', $hospital_no);
        $stmt->bindParam(':appt_no', $app_no);
        $stmt->bindParam(':request_note_description', $request_note_description);
        $stmt->bindParam(':date_time', $appointment_date);
        $stmt->bindParam(':service_type', $appointment_type);
        $stmt->bindParam(':doctor_name', $_SESSION['fullname']);

        // Execute the statement
        if ($stmt->execute()) {
            $error_status = 2;
            $error_msg = 'Success : Request sent';
        } else {
            $error_status = 1;
            $error_msg = 'Error : Not Successfully!';
        }
    }
}

if (isset($_POST['add_admit_request'])) {

    $appointment_number = $_POST['app_no'];
    $hosp_no = $_POST['hosp_no'];
    $reason_adm = $_POST['reason_adm'];
    $admit_type = $_POST['admit_type'];
    $created_by = $_POST['created_by'];
    $created_by_name = $_POST['created_by_name'];
    $date_admit = date('Y-m-d H:i:s');
    $adm_status = '0';

    $error_status = 1;
    $error_msg = 'Error: Admission Request Not Saved';

    try {
        // Check if patient is already admitted (status 0 or 3)
        $check_stmt = $db->prepare("SELECT 1 FROM admission WHERE hospital_no = ? AND adm_status IN (0, 3) LIMIT 1");
        $check_stmt->execute([$hosp_no]);

        if ($check_stmt->rowCount() === 0) {
            $insertSQL = "INSERT INTO admission (hospital_no, app_no, doc_incharge, adm_doctor_id, reason_adm, adm_status, date_admit, admit_type)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $db->prepare($insertSQL);
            $save = $sql->execute([
                $hosp_no,
                $appointment_number,
                $created_by_name,
                $created_by,
                $reason_adm,
                $adm_status,
                $date_admit,
                $admit_type
            ]);

            if ($save) {
                $error_status = 2;
                $error_msg = 'Success: Admission Request Saved Successfully!';
            }
        } else {
            $error_status = 2;
            $error_msg = 'Success: Patient is already on admission.';
        }
    } catch (PDOException $e) {
        $error_status = 1;
        $error_msg = 'Database Error: ' . $e->getMessage();
    }
}

if (isset($_POST['cancel_admit'])) {
    $admission_sn = $_POST['admission_sn'];
    $hosp_no = $_POST['hosp_no'];
    $adm_status = '1';
    $updateSQL = 'UPDATE admission SET adm_status=:adm_status WHERE sn=:sn_numb';
    $sql = $db->prepare($updateSQL);
    $sql->bindParam(':adm_status', $adm_status, PDO::PARAM_STR);
    $sql->bindParam(':sn_numb', $admission_sn, PDO::PARAM_STR);
    $sql->execute();
}

if (isset($_POST['cancel_adminssion_request_'])) {

    $hospital_no = $_POST['hospital_no'];
    $insertSQL = 'DELETE FROM admission WHERE hospital_no = ?  AND  adm_status = 0 ';
    $sql = $db->prepare($insertSQL);
    $save = $sql->execute(array($hospital_no));

    $error_status = 1;
    $error_msg = 'Error : Admission Request Not CANCELLED ';

    if ($save == true) {
        $error_status = 2;
        $error_msg = 'Success : Admission Request CANCELLED Successfully!';
        $adm_status = 5;
    }
}

if (isset($_POST['send_discharge_request_btn'])) {

    $appt_no = $_POST['appt_no'];
    $hospital_no = $_POST['hospital_no'];
    $patient_name = $_POST['patient_name'];
    $discharge_status = $_POST['discharge_status'];
    $discharge_note = $_POST['discharge_note'];
    $admit_type = $_POST['admit_type'];

    if ($admit_type == 'admit_o') {
        try {
            $db->beginTransaction();

            $adm_status_current = '3'; // Currently admitted (active status)
            $adm_status_discharge = '4'; // Discharged
            $date_discharge = date('Y-m-d H:i:s');
            $discharge_by_nurse = 'System Discharge'; // Optional usage if needed

            $updateSQL = "
                UPDATE admission 
                SET 
                    adm_status = :new_status,
                    date_discharge = :date_discharge,
                    discharge_name = :discharge_name,
                    discharge_doc_id = :discharge_doc_id,
                    discharge_note = :discharge_note
                WHERE 
                    hospital_no = :hospital_no AND adm_status = :current_status
            ";

            $stmt = $db->prepare($updateSQL);
            $stmt->bindValue(':new_status', $adm_status_discharge, PDO::PARAM_STR);
            $stmt->bindValue(':date_discharge', $date_discharge, PDO::PARAM_STR);
            $stmt->bindValue(':discharge_name', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt->bindValue(':discharge_doc_id', $_SESSION['id'], PDO::PARAM_STR);
            $stmt->bindValue(':discharge_note', $discharge_note, PDO::PARAM_STR);
            $stmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $stmt->bindValue(':current_status', $adm_status_current, PDO::PARAM_STR);
            $stmt->execute();

            $db->commit();

            $error_status = 2;
            $error_msg = 'Success: Patient discharged successfully!';
        } catch (PDOException $e) {
            $db->rollBack();
            $error_status = 1;
            $error_msg = 'Error: ' . $e->getMessage();
        }
    } else {

        $refferal_notes = isset($_POST['refferal_notes']) ? $_POST['refferal_notes'] : '';
        $now_setdate = date('Y-m-d H:i:s');
        $date_ = date('Y-m-d');
        $time_ = date('h:i:s');
        $auth_expire_date = $date_;

        $stmt = $db->prepare('SELECT * FROM discharge_fellowup WHERE hospital_no = ?');
        $stmt->execute(array($hospital_no));

        if ($stmt->rowCount() == 0 && isset($_SESSION['fullname']) && $_SESSION['fullname'] != '') {
            try {
                $db->beginTransaction();

                // Insert into discharge_fellowup
                $insertSQL = "INSERT INTO discharge_fellowup (
                    hospital_no, appt_no, patient_name, app_by, referal_doc, ap_type, 
                    auth_code, auth_expire_date, date_ap, ap_time, status, queue_lock, discharge_note
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 2, ?)";
                $sql = $db->prepare($insertSQL);
                $save = $sql->execute(array(
                    $hospital_no,
                    $appt_no,
                    $patient_name,
                    '-',
                    $_SESSION['fullname'],
                    1,
                    '',
                    $auth_expire_date,
                    $date_,
                    $time_,
                    $discharge_status,
                    $discharge_note
                ));

                if ($save) {
                    // Referral-specific entry
                    if ($discharge_status == 'Referred') {
                        $refSQL = "INSERT INTO medical_report_task (
                            hospital_no, notes, created_at, created_by, created_by_name, 
                            action, report_type, status, token
                        ) VALUES (?, ?, ?, ?, ?, 'save', 'Referral', '1', ?)";
                        $refStmt = $db->prepare($refSQL);
                        $refStmt->execute(array(
                            $hospital_no,
                            $refferal_notes,
                            $now_setdate,
                            $_SESSION['id'],
                            $_SESSION['fullname'],
                            uniqid()
                        ));
                    }

                    // Update admission table
                    $updateSQL = "UPDATE admission SET 
                        discharge_status = :discharge_status,
                        discharge_name = :discharge_name,
                        discharge_note = :discharge_note
                        WHERE hospital_no = :hospital_no AND adm_status = 3";
                    $sql = $db->prepare($updateSQL);
                    $sql->bindParam(':discharge_status', $discharge_status, PDO::PARAM_STR);
                    $sql->bindParam(':discharge_name', $_SESSION['fullname'], PDO::PARAM_STR);
                    $sql->bindParam(':discharge_note', $discharge_note, PDO::PARAM_STR);
                    $sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                    $sql->execute();

                    // Update diagnosis_tracking table
                    $trackingStmt = $db->prepare("UPDATE diagnosis_tracking 
                        SET adm_id = ?, discharge_status = ?
                        WHERE hospital_no = ? AND app_no = ?");
                    $trackingStmt->execute(array($appt_no, $discharge_status, $hospital_no, $appt_no));

                    // Handle patient death alert
                    if ($discharge_status == 'Death') {
                        $death_alert = '<div align="center"><h1>Discharge Status:<br>DEATH</h1></div>';
                        $deathStmt = $db->prepare("INSERT INTO tbl_patient_alerts (
                            hospital_no, alert, created_at, created_by
                        ) VALUES (?, ?, ?, ?)");
                        $deathStmt->execute(array($hospital_no, $death_alert, $date_, $_SESSION['fullname']));
                    }

                    $db->commit();
                    $error_status = 2;
                    $error_msg = 'Success: Discharge Request Sent Successfully!';
                    $adm_status = 3;
                } else {
                    $db->rollBack();
                    $error_status = 1;
                    $error_msg = 'Error: Discharge Request Not Successful';
                }
            } catch (Exception $e) {
                $db->rollBack();
                $error_status = 1;
                $error_msg = 'Error: Discharge Request Failed - ' . $e->getMessage();
            }
        } else {
            $error_status = 1;
            $error_msg = 'Error: Discharge Request Not Successful';
        }
    }
}

if (isset($_POST['start_billing'])) {

    $hosp_no = $_POST['hosp_no'];
    $app_no = $_POST['app_no'];
    $admit_date = $_POST['admit_date'];
    $admit_time = $_POST['admit_time'];
    $add_minus = $_POST['add_minus'];
    $payment_mode = $_POST['payment_mode'];
    $insurance_no = $_POST['insurance_no'];
    $insurance = $_POST['insurance_type'];
    $interest = $_POST['interest'];
    $room_bed_sn_ = $_POST['room_bed_sn_'];
    $admission_sn = $_POST['admission_sn'];

    $admit_date_time = date("$admit_date $admit_time");

    if (!empty($_REQUEST['services_name'])) {
        if (date('Y-m-d H:i:s') > $admit_date_time) {

            try {
                $db->beginTransaction();

                $stmtx = $db->prepare("
                    SELECT 
                        room_name,
                        bed_no,
                        hosp_price,
                        nhis_price,
                        access,
                        sn,
                        ext_price,
                        dept_id
                    FROM bed_mgt
                    WHERE sn = :sn
                ");
                $stmtx->execute([':sn' => $room_bed_sn_]);
                $row_bed = $stmtx->fetch(PDO::FETCH_ASSOC);

                if ($row_bed) {
                    $service_name   = $row_bed["room_name"] . '(bed ' . $row_bed["bed_no"] . ')';
                    $hosp_price     = $row_bed["hosp_price"];
                    $nhis_price     = $row_bed["nhis_price"];
                    $service_access = $row_bed["access"];
                    $item_sn        = $row_bed["sn"];
                    $ext_price      = $row_bed["ext_price"];
                    $dept_id        = $row_bed["dept_id"];
                }

                if (in_array($insurance, ['Family', 'Private(Self)'], true)) {
                    $select = $db->prepare("
                            SELECT min_amount_adm, require_amount_b4_adm
                            FROM department
                            WHERE sn = :dept_id
                              AND require_amount_b4_adm > 1
                              AND min_amount_adm > 0
                        ");
                    $select->execute([':dept_id' => $dept_id]);
                    $dept_data = $select->fetch(PDO::FETCH_ASSOC);

                    if ($dept_data !== false) {
                        $min_amount_adm = $dept_data['min_amount_adm'];
                        $require_amount_b4_adm = $dept_data['require_amount_b4_adm'];

                        $general_credit_limit = $_SESSION['credit_limit_status'];
                        $items = call_current_balance($db, $hosp_no, $general_credit_limit);

                        $current_balance = $items["current_balance"];
                        $require_amount_b4_adm = ($require_amount_b4_adm / 100) * $min_amount_adm;

                        if ($current_balance < $require_amount_b4_adm) {
                            header("location:patient.php?hosp_no=$hosp_no&adm_req&error_regamt=$require_amount_b4_adm&min=$min_amount_adm");
                            exit;
                        }
                    }
                }

                $target_sn = $item_sn;
                $_tariff_table = "hmo_bed_tariff";
                include("../inc/price_calc.php");

                $setdate = $admit_date_time;
                $serv_group = 'Nursing Services';
                $cat_type = 'Bed Space/Accommodation';
                $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
                $fullname = $_SESSION['fullname'];
                $invoice_status = 0;

                $query = build_query_save(
                    $db,
                    $app_no,
                    $hosp_no,
                    $service_access,
                    $serv_group,
                    $cat_type,
                    $dept_id,
                    $item_sn,
                    $service_name,
                    $claim_amt,
                    $ccop_int_charge,
                    $item_amt,
                    $invoice_no,
                    $fullname,
                    $setdate,
                    $amt_paying,
                    $pay_mode,
                    $invoice_status
                );
                if ($query == 1) {
                    $billable = 'yes';
                    $updateSQL = 'UPDATE admission SET billable=:billable WHERE sn=:sn_numb';
                    $sql = $db->prepare($updateSQL);
                    $sql->bindParam(':billable', $billable, PDO::PARAM_STR);
                    $sql->bindParam(':sn_numb', $admission_sn, PDO::PARAM_STR);
                    $sql->execute();
                } else {
                    throw new Exception('Error saving bill');
                }

                foreach ($_POST['services_name'] as $services_name) {

                    $item_sn = $services_name;
                    $stmt = $db->prepare("SELECT * FROM prices_table where sn='$item_sn'");
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $rowCat = $stmt->fetch(PDO::FETCH_ASSOC);
                        $service_name = $rowCat['item_service'];
                        $coverage = $rowCat['coverage'];
                        $part = explode('/', $coverage);
                        $private = $part[0];
                        $nhis = $part[1];
                        $nhis = strtoupper($nhis);
                        $hosp_price = $rowCat['hosp_price'];
                        $ext_price = $rowCat['ext_price'];
                        $nhis_price = $rowCat['nhis_price'];
                        $cat_type = $rowCat['price_table'];
                        $serv_group = 'Nursing Services';
                        $dept_id = $rowCat['dept'];
                    }

                    ///------------------------------------------------
                    $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
                    $fullname = $_SESSION['fullname'];

                    $target_sn = $item_sn;
                    $_tariff_table = "hmo_medical_tariff";
                    include("../inc/price_calc.php");

                    $invoice_status = 0;
                    $query = build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $item_sn, $service_name, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status);

                    if ($query != 1) {
                        throw new Exception('Error saving bill');
                    }
                }

                $db->commit();
                header("location:patient.php?hosp_no=$hosp_no&adm");
                $error_status = 2;
                $error_msg = 'Success : Bill Started Successfully!';
            } catch (Exception $e) {
                $db->rollBack();
                $error_status = 1;
                $error_msg = 'Error : ' . $e->getMessage();
            }
        } else {

            $error_status = 1;
            $error_msg = 'Error : Set Admission to Less or Current Date!';
        }
    } else {
        $error_status = 1;
        $error_msg = 'Error : Nursing Services Not Selected!';
    }
}

if (isset($_POST['change_adm_admit_p'])) {

    try {
        $db->beginTransaction();

        $admit_type = $_POST['admit_type']; // admit_p /// admit_o
        $admission_sn = $_POST['admission_sn'];
        $admission_date = $_POST['admission_date'];
        $payment_mode = $_POST['payment_mode'];
        $add_minus = $_POST['add_minus'];
        $hosp_no = $_POST['hosp_no'];
        $dept_id = $_SESSION['dept_id'];
        $services_list = !empty($_REQUEST['services_name']) ? 1 : 0;

        //// this is code set all previous autodeduct to auto deduct to 2 just incase there is any ////////////////
        $updates = [
            'remarks' => ['auto_deduct', 'auto_deduct2'],
            'tag' => ['auto', 'auto2']
        ];
        foreach ($updates as $column => $values) {
            $updateSQL = "UPDATE patient_ap_services SET $column=:$values[1] WHERE $column=:$values[0] and hospital_no=:hospital_no";
            $sql = $db->prepare($updateSQL);
            $sql->bindParam(":{$values[1]}", $values[1], PDO::PARAM_STR);
            $sql->bindParam(":{$values[0]}", $values[0], PDO::PARAM_STR);
            $sql->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
            $sql->execute();
        }

        if ($admission_date < date('Y-m-d H:i:s')) {

            $insurance_no = $_POST['insurance_no'];
            $app_no = $_POST['app_no'];
            $hosp_no = $_POST['hosp_no'];
            $insurance = $_POST['insurance_type'];
            $interest = $_POST['interest'];
            $floor = $_POST['floor'];
            $isServiceBillable = 'yes';

            $admit_date_time = date('Y-m-d H:i:s');
            $setdate = $admit_date_time;
            $serv_group = 'Nursing Services';
            $cat_type = 'Bed Space/Accommodation';
            $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
            $fullname = $_SESSION['fullname'];
            $adm_status = '3';

            $room_bed = explode('_', $_POST['room_bed2']);
            $service_name = $room_bed[0];
            $hosp_price = $room_bed[1];
            $nhis_price = $room_bed[2];
            $service_access = $room_bed[3];
            $item_sn = $room_bed[4];
            $ext_price = $room_bed[5];
            $dept_id = $room_bed[6];

            $target_sn = $item_sn;
            $_tariff_table = "hmo_bed_tariff";
            include("../inc/price_calc.php");


            $admit_type = 'admit_p';
            $updateSQL = 'UPDATE admission SET room_bed=:room_bed,room_bed_sn=:room_bed_sn,floor=:floor,adm_status=:adm_status,
            date_admit=:date_admit,billable=:billable,dept_id=:dept_id,accom_gen_date=:accom_gen_date,admit_type=:admit_type WHERE sn=:sn_numb';
            $sql = $db->prepare($updateSQL);

            $sql->bindParam(':room_bed', $service_name, PDO::PARAM_STR);
            $sql->bindParam(':room_bed_sn', $item_sn, PDO::PARAM_STR);
            $sql->bindParam(':floor', $floor, PDO::PARAM_STR);
            $sql->bindParam(':adm_status', $adm_status, PDO::PARAM_STR);
            $sql->bindParam(':date_admit', $admit_date_time, PDO::PARAM_STR);
            $sql->bindParam(':billable', $isServiceBillable, PDO::PARAM_STR);
            $sql->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
            $sql->bindParam(':accom_gen_date', $admit_date_time, PDO::PARAM_STR);
            $sql->bindParam(':admit_type', $admit_type, PDO::PARAM_STR);
            $sql->bindParam(':sn_numb', $admission_sn, PDO::PARAM_STR);
            $sql->execute();


            if ($sql->rowCount() > 0) {
                $invoice_status = 0;
                $query = build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $item_sn, $service_name, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status);
                if ($query != 1) {
                    throw new Exception('Error saving bill');
                }

                if ($query == 1 && $services_list == 1) {
                    foreach ($_POST['services_name'] as $services_name) {
                        $item_sn = $services_name;
                        $stmt = $db->prepare("SELECT * FROM prices_table where sn='$item_sn'");
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $rowCat = $stmt->fetch(PDO::FETCH_ASSOC);
                            $service_name = $rowCat['item_service'];
                            $coverage = $rowCat['coverage'];
                            $part = explode('/', $coverage);
                            $private = $part[0];
                            $nhis = $part[1];
                            $nhis = strtoupper($nhis);
                            $hosp_price = $rowCat['hosp_price'];
                            $ext_price = $rowCat['ext_price'];
                            $nhis_price = $rowCat['nhis_price'];
                            $cat_type = $rowCat['price_table'];
                            $serv_group = 'Nursing Services';
                            $dept_id = $rowCat['dept'];
                        }

                        $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
                        $fullname = $_SESSION['fullname'];

                        $target_sn = $item_sn;
                        $_tariff_table = "hmo_medical_tariff";
                        include("../inc/price_calc.php");


                        //// 
                        if ($admit_type == 'admit_o') {
                            $setdate = date('Y-m-d H:i:s');
                            $invoice_status = 1;
                        }

                        $query = build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $item_sn, $service_name, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status);
                        if ($query != 1) {
                            throw new Exception('Error saving bill');
                        }
                    }
                }
            }
        } else {
            $error_status = 1;
            $error_msg = 'Error : Set Admission to Less or Current Date!';
        }
        $db->commit();
        header("location:patient.php?hosp_no=$hosp_no&adm");
    } catch (Exception $e) {
        $db->rollBack();
        $error_status = 1;
        $error_msg = 'Error : ' . $e->getMessage();
    }
}

if (isset($_POST['add_admit'])) {

    if (!empty($_SESSION['dept_id']) && !empty($_SESSION['fullname'])) {

        $admit_type = $_POST['admit_type']; // admit_p /// admit_o
        $hosp_no = $_POST['hosp_no'];
        $dept_id = $_SESSION['dept_id'];
        $services_list = !empty($_REQUEST['services_name']) ? 1 : 0;

        //// this is code set all previous autodeduct to auto deduct to 2 just incase there is any ////////////////

        // remarks
        $sql1 = $db->prepare("
        UPDATE patient_ap_services 
        SET remarks = 'old_auto_deduct' 
        WHERE remarks IN ('auto_deduct','auto_deduct2') 
        AND hospital_no = :hospital_no");
        $sql1->execute([':hospital_no' => $hosp_no]);

        // tag
        $sql2 = $db->prepare("
        UPDATE patient_ap_services 
        SET tag = 'old_auto_deduct' 
        WHERE tag IN ('auto','auto2') 
        AND hospital_no = :hospital_no");
        $sql2->execute([':hospital_no' => $hosp_no]);

        $admit_date = $_POST['admit_date'];
        $admit_time = $_POST['admit_time'];
        $add_minus = $_POST['add_minus'];
        $payment_mode = $_POST['payment_mode'];
        $insurance_no = $_POST['insurance_no'];
        $insurance = $_POST['insurance_type'];
        $interest = $_POST['interest'];
        $floor = $_POST['floor'];
        $isServiceBillable = $_POST['isServiceBillable'];

        $admit_date_time = date("$admit_date $admit_time");
        $setdate = $admit_date_time;
        $serv_group = 'Nursing Services';
        $cat_type = 'Bed Space/Accommodation';
        $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
        $fullname = $_SESSION['fullname'];
        $adm_status = '3';

        if ($admit_type == 'admit_o') {
            $setdate = $admit_date_time = date('Y-m-d H:i:s');
        }

        if (date('Y-m-d H:i:s') >= $admit_date_time) {

            $stmt = $db->prepare("SELECT sn, app_no FROM admission where hospital_no=:hosp_no and adm_status='0' ORDER BY sn DESC LIMIT 1");
            $stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                $sn_numb = $row['sn'];
                $app_no = $row['app_no'];

                try {
                    $db->beginTransaction();

                    if ($admit_type == 'admit_p') {

                        $room_bed = explode('_', $_POST['bed_avail']);
                        $service_name = $room_bed[0];
                        $hosp_price = $room_bed[1];
                        $nhis_price = $room_bed[2];
                        $service_access = $room_bed[3];
                        $item_sn = $room_bed[4];
                        $ext_price = $room_bed[5];
                        $dept_id = $room_bed[6];

                        if (
                            $isServiceBillable === 'billable' &&
                            $admit_type === 'admit_p' &&
                            in_array($insurance, ['Family', 'Corporate', 'Private(Self)'], true)
                        ) {

                            $select = $db->prepare("
                            SELECT min_amount_adm, require_amount_b4_adm
                            FROM department
                            WHERE sn = :dept_id
                              AND require_amount_b4_adm > 1
                              AND min_amount_adm > 0
                        ");
                            $select->execute([':dept_id' => $dept_id]);
                            $dept_data = $select->fetch(PDO::FETCH_ASSOC);

                            if ($dept_data !== false) {
                                $min_amount_adm = $dept_data['min_amount_adm'];
                                $require_amount_b4_adm = $dept_data['require_amount_b4_adm'];

                                $general_credit_limit = $_SESSION['credit_limit_status'];
                                $items = call_current_balance($db, $hosp_no, $general_credit_limit);

                                $current_balance = $items["current_balance"];
                                $require_amount_b4_adm = ($require_amount_b4_adm / 100) * $min_amount_adm;

                                if ($current_balance < $require_amount_b4_adm) {
                                    header("location:patient.php?hosp_no=$hosp_no&adm_req&error_regamt=$require_amount_b4_adm&min=$min_amount_adm");
                                    exit;
                                }
                            }
                        }

                        $target_sn = $item_sn;
                        $_tariff_table = "hmo_bed_tariff";
                        include("../inc/price_calc.php");

                        $billable = ($isServiceBillable == 'billable') ? 'yes' : 'no';
                        if ($isServiceBillable == 'billable' and $services_list == 0) {
                            header("location:patient.php?hosp_no=$hosp_no&adm_req&svr_nt");
                            exit;
                        }
                    } else {
                        $service_name = null;
                        $item_sn = null;
                        $floor = null;
                        $billable = null;
                    }

                    $updateSQL = 'UPDATE admission SET care_giver=:care_giver,care_giver_phone=:care_giver_phone,
                                relationship=:relationship,room_bed=:room_bed,room_bed_sn=:room_bed_sn,floor=:floor,adm_status=:adm_status,nurse_adm_by=:nurse_adm_by,
                                date_admit=:date_admit,billable=:billable,dept_id=:dept_id,accom_gen_date=:accom_gen_date WHERE sn=:sn_numb';
                    $sql = $db->prepare($updateSQL);
                    $sql->bindParam(':care_giver', $_POST['care_giver'], PDO::PARAM_STR);
                    $sql->bindParam(':care_giver_phone', $_POST['phone'], PDO::PARAM_STR);
                    $sql->bindParam(':relationship', $_POST['relation'], PDO::PARAM_STR);
                    $sql->bindParam(':room_bed', $service_name, PDO::PARAM_STR);
                    $sql->bindParam(':room_bed_sn', $item_sn, PDO::PARAM_STR);
                    $sql->bindParam(':floor', $floor, PDO::PARAM_STR);
                    $sql->bindParam(':adm_status', $adm_status, PDO::PARAM_STR);
                    $sql->bindParam(':nurse_adm_by', $_SESSION['id'], PDO::PARAM_STR);
                    $sql->bindParam(':date_admit', $admit_date_time, PDO::PARAM_STR);
                    $sql->bindParam(':billable', $billable, PDO::PARAM_STR);
                    $sql->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                    $sql->bindParam(':accom_gen_date', $admit_date_time, PDO::PARAM_STR);
                    $sql->bindParam(':sn_numb', $sn_numb, PDO::PARAM_STR);
                    $sql->execute();

                    if ($sql->rowCount() > 0) {

                        if ($isServiceBillable == 'billable' && $services_list == 1 && $admit_type == 'admit_p') {
                            $status = 1;
                            $updateSQL = "UPDATE bed_mgt SET status=:status WHERE sn=:sn";
                            $sql = $db->prepare($updateSQL);
                            $sql->bindParam(':status', $status, PDO::PARAM_STR);
                            $sql->bindParam(':sn', $item_sn, PDO::PARAM_STR);
                            $sql->execute();

                            $invoice_status = 0;
                            $query = build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $item_sn, $service_name, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status);
                            if ($query != 1) {
                                throw new Exception('Error saving bill');
                            }
                        } elseif ($admit_type == 'admit_o') {
                            $query = 1;
                        }

                        if ($query == 1 && $services_list == 1) {
                            foreach ($_POST['services_name'] as $services_name) {
                                $item_sn = $services_name;
                                $stmt = $db->prepare("SELECT * FROM prices_table where sn='$item_sn'");
                                $stmt->execute();

                                if ($stmt->rowCount() > 0) {
                                    $rowCat = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $service_name = $rowCat['item_service'];
                                    $coverage = $rowCat['coverage'];
                                    $part = explode('/', $coverage);
                                    $private = $part[0];
                                    $nhis = $part[1];
                                    $nhis = strtoupper($nhis);
                                    $hosp_price = $rowCat['hosp_price'];
                                    $ext_price = $rowCat['ext_price'];
                                    $nhis_price = $rowCat['nhis_price'];
                                    $cat_type = $rowCat['price_table'];
                                    $serv_group = 'Nursing Services';
                                    $dept_id = $rowCat['dept'];
                                }

                                $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
                                $fullname = $_SESSION['fullname'];

                                $target_sn = $item_sn;
                                $_tariff_table = "hmo_medical_tariff";
                                include("../inc/price_calc.php");

                                //// 
                                if ($admit_type == 'admit_o') {
                                    $setdate = date('Y-m-d H:i:s');
                                    $invoice_status = 1;
                                }

                                $query = build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $item_sn, $service_name, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status);
                                if ($query != 1) {
                                    throw new Exception('Error saving bill');
                                }
                            }
                        }
                    }

                    $db->commit();

                    header("location:patient.php?hosp_no=$hosp_no&adm");
                } catch (Exception $e) {
                    $db->rollBack();
                    $error_status = 1;
                    $error_msg = 'Error : ' . $e->getMessage();
                }
            } else {
                $error_status = 1;
                $error_msg = 'Error : No Admission Request!';
            }
        } else {
            $error_status = 1;
            $error_msg = 'Error : Set Admission to Less or Current Date!';
        }
    } else {
        $error_status = 1;
        $error_msg = 'Error : Department Not Available!';
    }
}

if (isset($_POST['change_adm'])) {

    if (!isset($_SESSION['fullname']) || $_SESSION['fullname'] == '') {
        echo 'Error: No Department!';
        exit;
    }

    $hosp_no = $_POST['hosp_no'];
    $app_no = $_POST['app_no'];
    $interest = $_POST['interest'];
    $insurance = $_POST['insurance_type'];
    $add_minus = $_POST['add_minus'];
    $payment_mode = $_POST['payment_mode'];
    $insurance_no = $_POST['insurance_no'];
    $floor = $_POST['floor2'];
    $billable = $_POST['billable'];
    $admit_type = $_POST['admit_type'];
    $room_bed_sn = $_POST['room_bed_sn'];
    $service_access = null;
    $save_count = 0;
    $serv_group = 'Nursing Services';
    $cat_type = 'Bed Space/Accommodation';
    $remarks = 'auto_deduct';

    $replace_room_bed = explode('_', $_POST['room_bed2']);
    $dept_id_room_bed = $replace_room_bed[6];


    if ($dept_id_room_bed && $billable === 'yes' && in_array($insurance, ['Family', 'Corporate', 'Private(Self)'], true)) {

        $select = $db->prepare("
        SELECT min_amount_adm, require_amount_b4_adm
        FROM department
        WHERE sn = :dept_id
          AND require_amount_b4_adm > 1
          AND min_amount_adm > 0");
        $select->execute([':dept_id' => $dept_id_room_bed]);
        $dept_data = $select->fetch(PDO::FETCH_ASSOC);

        if ($dept_data !== false) {
            $min_amount_adm = $dept_data['min_amount_adm'];
            $require_amount_b4_adm = $dept_data['require_amount_b4_adm'];

            $general_credit_limit = $_SESSION['credit_limit_status'];
            $items = call_current_balance($db, $hosp_no, $general_credit_limit);

            $current_balance = $items["current_balance"];
            $require_amount_b4_adm = ($require_amount_b4_adm / 100) * $min_amount_adm;

            if ($current_balance < $require_amount_b4_adm) {
                header("location:patient.php?hosp_no=$hosp_no&adm_req&error_regamt=$require_amount_b4_adm&min=$min_amount_adm");
                exit;
            }
        }
    }

    try {

        $db->beginTransaction();

        if (!empty($_POST['services_name']) && $billable == 'yes') {

            $nursing_services_selected = $_POST['services_name'];

            /*  print_r($nursing_services_selected);
            echo '<br>';
 */

            $stmt2 = $db->prepare("SELECT sn,item_service FROM prices_table WHERE nusing_setauth = '1'");
            $stmt2->execute();
            $data = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $row2) {


                $nursing_services_id = $row2['sn'];
                ///echo $row2['item_service'];
                //echo '<br>';

                $stmt90 = $db->prepare("SELECT * FROM patient_ap_services 
        WHERE drug_sn = :nursing_services_id 
        AND remarks LIKE :remarks 
        AND hospital_no = :hosp_no");

                $stmt90->execute([
                    ':nursing_services_id' => $nursing_services_id,
                    ':remarks' => "%$remarks%", // ✅ wildcard added
                    ':hosp_no' => $hosp_no
                ]);

                if ($stmt90->rowCount() > 0) {
                    /// THIS IS CANCLLLED
                    if (!in_array($nursing_services_id, $nursing_services_selected)) {

                        $stmt = $db->prepare("SELECT COUNT(DISTINCT drug_sn) AS row_count  FROM patient_ap_services 
                        WHERE hospital_no = :hosp_no AND remarks = 'auto_deduct'");
                        $stmt->execute([':hosp_no' => $hosp_no]);
                        $row_count = $stmt->fetchColumn();  /// to avoid deleting all

                        if ($row_count > 1) {

                            $cancelTodaySQL = "
                                UPDATE patient_ap_services 
                                SET paystatus = 3, 
                                    pay_mode = 'cancel', 
                                    remarks = 'cancel', 
                                    cr = 0, 
                                    invoice_status = 3
                                WHERE drug_sn = :nursing_services_id 
                                AND hospital_no = :hosp_no 
                                AND paystatus = 0
                                AND DATE(date_entry) = CURDATE()
                            ";
                            $db->prepare($cancelTodaySQL)->execute([
                                ':nursing_services_id' => $nursing_services_id,
                                ':hosp_no' => $hosp_no
                            ]);


                            $resetOtherSQL = "
                                UPDATE patient_ap_services 
                                SET remarks = NULL
                                WHERE drug_sn = :nursing_services_id 
                                AND hospital_no = :hosp_no 
                                AND DATE(date_entry) <> CURDATE()
                            ";
                            $db->prepare($resetOtherSQL)->execute([
                                ':nursing_services_id' => $nursing_services_id,
                                ':hosp_no' => $hosp_no
                            ]);

                            $save_count++;
                        }
                    }
                } else {


                    if (in_array($nursing_services_id, $nursing_services_selected)) {
                        $stmt = $db->prepare("SELECT * FROM prices_table WHERE sn = :sn");
                        $stmt->execute([':sn' => $nursing_services_id]);

                        if ($stmt->rowCount() > 0) {
                            $rowCat = $stmt->fetch(PDO::FETCH_ASSOC);
                            $service_name_nur_care = $rowCat['item_service'];
                            $coverage = $rowCat['coverage'];
                            $part = explode('/', $coverage);
                            $private = $part[0];
                            $nhis = strtoupper($part[1]);
                            $hosp_price = $rowCat['hosp_price'];
                            $ext_price = $rowCat['ext_price'];
                            $nhis_price = $rowCat['nhis_price'];
                            $cat_type = $rowCat['price_table'];
                            $dept_id =  $rowCat['dept'];

                            $setdate = date('Y-m-d H:i:s');
                            $invoice_no = date('m') . sprintf('%06d', mt_rand(0, 999999));
                            $fullname = $_SESSION['fullname'];

                            $target_sn = $nursing_services_id;
                            $_tariff_table = "hmo_medical_tariff";
                            include("../inc/price_calc.php");

                            $invoice_status = 0;
                            $query = build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $nursing_services_id, $service_name_nur_care, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status);
                            if ($query == 1) {
                                $save_count++;
                            } else {
                                /// throw new Exception('Error saving billing');
                            }
                        }
                    }
                }
            }
        } elseif ($dept_id_room_bed && $billable == 'yes') {
            header("Location: patient.php?hosp_no=" . urlencode($hosp_no) . "&adm&error=" . urlencode("Invalid Nursing Services. Select At least One Service!"));
            exit;
        }



        ///////////////==============================  DIVISION B/WEEN SERVICES & BED CHANGE..................

        if ($billable == 'no') {

            $replace_room_bed = explode('_', $_POST['room_bed2']);
            list($service_name, $hosp_price, $nhis_price, $service_access, $item_sn, $ext_price, $dept_id_room_bed) = $replace_room_bed;

            $db->prepare("UPDATE bed_mgt SET status = '0' WHERE sn = :sn")->execute([':sn' => $room_bed_sn]);
            $db->prepare("UPDATE bed_mgt SET status = '1' WHERE sn = :sn")->execute([':sn' => $item_sn]);

            $updateSQL = "UPDATE admission 
              SET room_bed = :room_bed, 
                  room_bed_sn = :room_bed_sn, 
                  floor = :floor, 
                  dept_id = :dept_id 
              WHERE room_bed_sn = :old_sn 
                AND hospital_no = :hosp_no 
                AND adm_status = '3'";
            $_stmt_ = $db->prepare($updateSQL);
            $_stmt_->execute([
                ':room_bed'   => $service_name,
                ':room_bed_sn' => $item_sn,
                ':floor'      => $floor,
                ':dept_id'    => $dept_id_room_bed,
                ':old_sn'     => $room_bed_sn,
                ':hosp_no'    => $hosp_no
            ]);
        } elseif ($_POST['room_bed2'] != '') {


            $serv_group = 'Nursing Services';
            $cat_type = 'Bed Space/Accommodation';

            $replace_room_bed = explode('_', $_POST['room_bed2']);
            list($service_name, $hosp_price, $nhis_price, $service_access, $item_sn, $ext_price, $dept_id_room_bed) = $replace_room_bed;

            $target_sn = $item_sn;
            $_tariff_table = "hmo_bed_tariff";
            include("../inc/price_calc.php");

            $selectSQL = "SELECT sn, paystatus
              FROM patient_ap_services
              WHERE hospital_no = :hospital_no 
                AND drug_sn = :drug_sn_2 
                AND cat_type = :cat_type 
              ORDER BY date_entry DESC
              LIMIT 1";
            $stmt = $db->prepare($selectSQL);
            $stmt->execute([
                ':hospital_no' => $hosp_no,
                ':drug_sn_2'   => $room_bed_sn,
                ':cat_type'    => $cat_type
            ]);

            $lastRow = $stmt->fetch(PDO::FETCH_ASSOC);

            // Step 2: Decide insert or update
            if ($lastRow && (int)$lastRow['paystatus'] === 1) {
                // Case 1: Last row is PAID → Insert new record
                $setdate = date('Y-m-d H:i:s');
                $invoice_no = date('m') . sprintf('%06d', mt_rand(0, 999999));
                $invoice_status = 1;

                $query2 = build_query_save(
                    $db,
                    $app_no,
                    $hosp_no,
                    $service_access,
                    $serv_group,
                    $cat_type,
                    $dept_id_room_bed,
                    $item_sn,
                    $service_name,
                    $claim_amt,
                    $ccop_int_charge,
                    $item_amt,
                    $invoice_no,
                    $fullname,
                    $setdate,
                    $amt_paying,
                    $pay_mode,
                    $invoice_status
                );

                if ($query2 != 1) {
                    throw new Exception('Error saving billing: ' . $query2);
                }
            } elseif ($lastRow && (int)$lastRow['paystatus'] === 0) {
                // Case 2: Last row is UNPAID → Update it
                $sn_ = $lastRow['sn'];

                $updateSQL = "UPDATE patient_ap_services 
                  SET item_services = :item_services, 
                      hosp_price   = :hosp_price, 
                      claim_amt    = :claim_amt, 
                      pay          = :pay, 
                      drug_sn      = :drug_sn,
                      dept_id      = :dept_id 
                  WHERE sn = :sn";

                $stmt = $db->prepare($updateSQL);
                $stmt->execute([
                    ':item_services' => $service_name,
                    ':hosp_price'    => $item_amt,
                    ':claim_amt'     => $claim_amt,
                    ':pay'           => $amt_paying,
                    ':drug_sn'       => $item_sn,
                    ':dept_id'       => $dept_id_room_bed,
                    ':sn'            => $sn_
                ]);
            } else {
                // Case 3: No previous row found → Insert new record
                $setdate = date('Y-m-d H:i:s');
                $invoice_no = date('m') . sprintf('%06d', mt_rand(0, 999999));
                $invoice_status = 1;

                $query2 = build_query_save(
                    $db,
                    $app_no,
                    $hosp_no,
                    $service_access,
                    $serv_group,
                    $cat_type,
                    $dept_id_room_bed,
                    $item_sn,
                    $service_name,
                    $claim_amt,
                    $ccop_int_charge,
                    $item_amt,
                    $invoice_no,
                    $fullname,
                    $setdate,
                    $amt_paying,
                    $pay_mode,
                    $invoice_status
                );

                if ($query2 != 1) {
                    throw new Exception('Error saving bill');
                }
            }


            if ($stmt->rowCount() > 0 || $query2 > 0) {

                $db->prepare("UPDATE bed_mgt SET status = '0' WHERE sn = :sn")->execute([':sn' => $room_bed_sn]);
                $db->prepare("UPDATE bed_mgt SET status = '1' WHERE sn = :sn")->execute([':sn' => $item_sn]);

                $stmt_ = $db->prepare("UPDATE admission 
                    SET room_bed = :room_bed, 
                        room_bed_sn = :room_bed_sn, 
                        floor = :floor, 
                        dept_id = :dept_id 
                    WHERE room_bed_sn = :old_sn 
                    AND hospital_no = :hosp_no 
                    AND adm_status = '3'");
                $stmt_->execute([
                    ':room_bed' => $service_name,
                    ':room_bed_sn' => $item_sn,
                    ':floor' => $floor,
                    ':dept_id' => $dept_id_room_bed,
                    ':old_sn' => $room_bed_sn,
                    ':hosp_no' => $hosp_no
                ]);

                if ($stmt_->rowCount() > 0) {
                    $save_count++;
                }
            } else {
                $db->rollBack();
                header("Location: patient.php?hosp_no=" . urlencode($hosp_no) . "&adm&error=" . urlencode("Unable to change the bed or department."));
                exit;
            }
        }

        $db->commit();
        if ($billable == 'no' && $_stmt_->rowCount() > 0) {
            $msg = "Save Successfully!";
            $type = "save";
        } elseif ($save_count > 0) {
            $msg = "Save Successfully!";
            $type = "save";
        } elseif ($_POST['room_bed2'] == '') {
            $msg = "Cannot delete all automatic invoices. At least one must remain.";
            $type = "error";
        } else {
            $msg = "Unable to make changes.";
            $type = "error";
        }
         header("Location: patient.php?hosp_no=" . urlencode($hosp_no) . "&adm&{$type}=" . urlencode($msg));
          exit;
    } catch (Exception $e) {
        $db->rollBack();
        header("Location: patient.php?hosp_no=$hosp_no&adm&error=" . urlencode($e->getMessage()));
        /// echo 'Error: ' . $e->getMessage();
    }
}

if (isset($_POST['change_adm_date_time'])) {

    $app_no = $_POST['app_no'];
    $hosp_no = $_POST['hosp_no'];
    $time_adm_change = $_POST['time_adm_change'];
    $date_adm_change = $_POST['date_adm_change'];
    $admission_sn = $_POST['admission_sn'];
    $billable_ = $_POST['billable_'];

    $time = strtotime('H:i:s', $time_adm_change);
    $date_admit = $date_adm_change . ' ' . $time_adm_change;

    $current_d = date('Y-m-d H:i:s');

    if ($current_d > $date_admit) {

        $updateSQL = "UPDATE admission SET date_admit=:date_admit WHERE sn=:adm_sn";
        $sql = $db->prepare($updateSQL);
        $sql->bindParam(':date_admit', $date_admit, PDO::PARAM_STR);
        $sql->bindParam(':adm_sn', $admission_sn, PDO::PARAM_STR);
        $sql->execute();

        $count = $sql->rowCount();

        if ($count > 0 and $billable_ == 'yes') {
            $remarks = 'auto_deduct';
            $updateSQL = "UPDATE patient_ap_services SET date_entry=:date_entry WHERE hospital_no=:hospital_no and remarks=:remarks";
            $sql = $db->prepare($updateSQL);
            $sql->bindParam(':date_entry', $date_admit, PDO::PARAM_STR);
            $sql->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
            $sql->bindParam(':remarks', $remarks, PDO::PARAM_STR);
            $sql->execute();
            $count_2 = $sql->rowCount();
            if ($count_2 > 0) {
                $error_status = 2;
                $error_msg = 'Succcess : Saved Successfully';
            } else {
                $error_status = 1;
                $error_msg = 'Error : Unable to Saved';
            }
        }
    } else {
        $error_status = 1;
        $error_msg = 'Error : Invalid Back Date';
    }
}

if (isset($_GET['dlv'])) {
    $dlv = $_GET['dlv'];

    $deleteSQL = 'DELETE FROM v_others WHERE sn = ?';
    $sql = $db->prepare($deleteSQL);
    $save = $sql->execute(array($dlv));

    $error_status = 1;
    $error_msg = 'Error: Unable to Delete';

    if ($save == true) {
        $error_status = 2;
        $error_msg = 'Success: Deleted';
        $adm_status = 5;
    }
}

if (isset($_GET['dlpb'])) {
    $dlpb = $_GET['dlpb'];

    $deleteSQL = 'DELETE FROM v_bp WHERE sn = ?';
    $sql = $db->prepare($deleteSQL);
    $save = $sql->execute(array($dlpb));

    $error_status = 1;
    $error_msg = 'Error: Unable to Delete';

    if ($save == true) {
        $error_status = 2;
        $error_msg = 'Success: Deleted';
        $adm_status = 5;
    }
}

function build_query_save($db, $app_no, $hosp_no, $service_access, $serv_group, $cat_type, $dept_id, $item_sn, $service_name, $claim_amt, $ccop_int_charge, $item_amt, $invoice_no, $fullname, $setdate, $amt_paying, $pay_mode, $invoice_status)
{
    $setdate_checker = $invoice_date = date('Y-m-d');
    $defaultQuantity = '1';
    $initialPayStatus = '0';
    $auto_deduct = 'auto_deduct';
    if ($fullname != '') {
        $invoice_status = 1;
    }
    $tag = '';

    try {
        $stmt = $db->prepare("SELECT sn FROM patient_ap_services 
            WHERE hospital_no=:hosp_no AND drug_sn=:item_sn AND paystatus=:initialPayStatus 
            AND remarks=:auto_deduct AND DATE(date_entry)=:setdate_checker");
        $stmt->execute([
            ':hosp_no' => $hosp_no,
            ':item_sn' => $item_sn,
            ':initialPayStatus' => $initialPayStatus,
            ':auto_deduct' => $auto_deduct,
            ':setdate_checker' => $setdate_checker
        ]);

        $created_by = $_SESSION['id'];

        if ($stmt->rowCount() == 0) {
            $sql = $db->prepare("INSERT INTO patient_ap_services 
                (app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, 
                item_services, tag, hosp_price, claim_amt, interest, qty, remarks, invoice_status,
                invoice_no,invoice_date, prepared_by,invoice_by, date_entry, paystatus, pay, pay_mode, cr, created_by) 
                VALUES (:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, 
                :drug_sn, :item_services, :tag, :hosp_price, :claim_amt, :interest, :qty, 
                :remarks,:invoice_status, :invoice_no,:invoice_date, :prepared_by,:invoice_by, :date_entry, :paystatus, :pay, :pay_mode, :cr, :created_by)");

            $sql->execute([
                ':app_no' => $app_no,
                ':hospital_no' => $hosp_no,
                ':access' => $service_access,
                ':serv_group' => $serv_group,
                ':cat_type' => $cat_type,
                ':dept_id' => $dept_id,
                ':drug_sn' => $item_sn,
                ':item_services' => $service_name,
                ':tag' => $tag,
                ':hosp_price' => $item_amt,
                ':claim_amt' => $claim_amt,
                ':interest' => $ccop_int_charge,
                ':qty' => $defaultQuantity,
                ':remarks' => $auto_deduct,
                ':invoice_status' => $invoice_status,
                ':invoice_no' => $invoice_no,
                ':invoice_date' => $invoice_date,
                ':prepared_by' => $fullname,
                ':invoice_by' => $fullname,
                ':date_entry' => $setdate,
                ':paystatus' => $initialPayStatus,
                ':pay' => $amt_paying,
                ':pay_mode' => $pay_mode,
                ':cr' => $defaultQuantity,
                ':created_by' => $created_by
            ]);

            return $sql->rowCount() > 0;
        } else {
            return false;
        }
    } catch (Exception $e) {
        // Log the error or take other appropriate action
        return false;
    }
}

if (isset($_POST['discharge_patien'])) {

    $hosp_no = $_POST['hosp_no'];
    $room_bed_sn = $_POST['room_bed_sn'];
    $Total_Pay = $_POST['Total_Pay'];

    $stmt = $db->prepare("SELECT * FROM discharge_fellowup WHERE hospital_no = ?");
    $stmt->execute(array($hosp_no));

    if ($stmt->rowCount() > 0 && isset($_SESSION['fullname']) && $_SESSION['fullname'] != '') {
        try {
            $db->beginTransaction();

            // Free the bed
            $status = 0;
            $updateBed = $db->prepare("UPDATE bed_mgt SET status = :status WHERE sn = :sn");
            $updateBed->bindParam(':status', $status, PDO::PARAM_INT);
            $updateBed->bindParam(':sn', $room_bed_sn, PDO::PARAM_STR);
            $updateBed->execute();

            // Update admission status
            $adm_status = '4';
            $adm_status2 = '3';
            $date_discharge = date('Y-m-d H:i:s');

            $updateAdm = $db->prepare("UPDATE admission SET 
                adm_status = :adm_status,
                date_discharge = :date_discharge,
                discharge_by_nurse = :discharge_by_nurse 
                WHERE hospital_no = :hospital_no AND adm_status = :adm_status2");
            $updateAdm->bindParam(':adm_status', $adm_status, PDO::PARAM_STR);
            $updateAdm->bindParam(':date_discharge', $date_discharge, PDO::PARAM_STR);
            $updateAdm->bindParam(':discharge_by_nurse', $_SESSION['fullname'], PDO::PARAM_STR);
            $updateAdm->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
            $updateAdm->bindParam(':adm_status2', $adm_status2, PDO::PARAM_STR);
            $updateAdm->execute();

            if ($updateAdm->rowCount() > 0) {

                // Remove from discharge follow-up table
                $deleteFellowup = $db->prepare("DELETE FROM discharge_fellowup WHERE hospital_no = ?");
                $deleteFellowup->execute(array($hosp_no));

                if ($deleteFellowup->rowCount() > 0) {
                    $error_status = 2;
                    $error_msg = 'Success: Patient Discharged Successfully!';
                }

                // remarks
                $sql1 = $db->prepare("
                    UPDATE patient_ap_services 
                    SET remarks = 'old_auto_deduct' 
                    WHERE remarks IN ('auto_deduct','auto_deduct2') 
                    AND hospital_no = :hospital_no");
                $sql1->execute([':hospital_no' => $hosp_no]);

                // tag
                $sql2 = $db->prepare("
                    UPDATE patient_ap_services 
                    SET tag = 'old_auto_deduct' 
                    WHERE tag IN ('auto','auto2') 
                    AND hospital_no = :hospital_no");
                $sql2->execute([':hospital_no' => $hosp_no]);

                $db->commit();

                // Notify finance if outstanding
                if ($Total_Pay > 0 && $_SESSION['credit_discharge_notification'] == 1) {
                    $msg = 'This Patient EMR: ' . $hosp_no . ' Discharged with Outstanding Bill. /By ' . $_SESSION['fullname'];
                    global_notify_($db, 'rights', 'RE%', $msg, 'Credit Alerts', $_SESSION['fullname']);
                    global_notify_($db, 'rights', 'AC%', $msg, 'Credit Alerts', $_SESSION['fullname']);
                }
            } else {
                $db->rollBack();
                $error_status = 1;
                $error_msg = 'Error: Unable to Discharge Patient!';
            }
        } catch (Exception $e) {
            $db->rollBack();
            $error_status = 1;
            $error_msg = 'Error: Unable to Discharge Patient - ' . $e->getMessage();
        }
    } else {
        $error_status = 1;
        $error_msg = 'Error: Unable to Discharge';
    }
}

if (isset($_GET['adm_note'])) {
    $del_id = $_GET['adm_note'];
    $updateSQL = "DELETE FROM patient_admission_note WHERE sn=:sn";
    $stmt_22 = $db->prepare($updateSQL);
    $stmt_22->bindParam(':sn', $del_id, PDO::PARAM_STR);
    $stmt_22->execute();
}

if (isset($_GET['oxygen'])) {
    $del_id = $_GET['oxygen'];
    $updateSQL = "DELETE FROM oxygen_consumption_chart WHERE sn=:sn";
    $stmt_22 = $db->prepare($updateSQL);
    $stmt_22->bindParam(':sn', $del_id, PDO::PARAM_STR);
    $stmt_22->execute();
}

if (isset($_POST['saveMgt'])) {
    session_start();
    include("../Connections/Conn.php");
    include('../doctor/objects.php');
    include('../doctor/helpers.php');

    $notes = $_POST['mgt_notes'];
    $appointment_number = cleanInput($_POST['app_no']);
    $hospital_no = cleanInput($_POST['hospital_no']);
    $doctor_name = cleanInput($_POST['doctor_name']);
    $doctor_id = cleanInput($_POST['doctor_id']);


    $plainText = strip_tags($notes);
    $safeText = htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8');
    $wordCount = str_word_count($safeText);

    if (!empty($notes) && trim($notes) !== '' && $wordCount >= 3) {


        $error_status = 1;
        $error_msg = 'Error :  Notes not saved...';
        $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, 'NS', 'note',  $doctor_name, null, $doctor_id, true);
        if ($save_note) {
            ///echo '';
            $error_status = 1;
            $error_msg = 'Success :  Saved Successful.';
        } else {
            $error_status = 2;
            $error_msg = 'Error :  Saved Successful.';
        }
    } else {
        ///error_status = 1;
        echo "Unable to save notes. Please provide enough details.";
    }
    ///exit;
}

if (isset($_POST['saveMgt_ward_round'])) {
    session_start();
    include("../Connections/Conn.php");
    include('../doctor/objects.php');
    include('../doctor/helpers.php');
    $diagnosis = '';
    $check_if_empty = 0;

    $notes = $_POST['mgt_notes'];
    ///$diagnosis_ = $_POST['search_icdcodes_input_ward_round'];
    $comment_ward_round = $_POST['comment_ward_round'];
    $comment2_ward_round = $_POST['comment2_ward_round'];
    $comment3_ward_round = $_POST['comment3_ward_round'];
    $diagnosis_ = $_POST['diagnosisInput_ward_round'];




    ///// SELECTED DIAGNOSIS
    if ($diagnosis_ != '') {
        $diagnosis = '<i><strong>' . 'Diagnosis: ' . '</strong></i><br>';
        $diagnosis = $diagnosis . $diagnosis_;
        $check_if_empty = 1;
    }

    $diagnosis2 = '';

    if ($comment_ward_round != '' && $diagnosis_ != '') {
        $diagnosis2 .= ' [ ' . $comment_ward_round . ' ]';
        $check_if_empty = 1;
    }
    if ($comment2_ward_round != '' && $diagnosis_ != '') {
        $diagnosis2 .= ' [ ' . $comment2_ward_round . ' ]';
        $check_if_empty = 1;
    }
    if ($comment3_ward_round != '' && $diagnosis_ != '') {
        $diagnosis2 .= ' [ ' . $comment3_ward_round . ' ]';
        $check_if_empty = 1;
    }

    $diagnosis = $diagnosis . $diagnosis2;
    $notes =  $notes . ($diagnosis ? '<br>' . $diagnosis : '');

    $appointment_number = cleanInput($_POST['app_no']);
    $hospital_no = cleanInput($_POST['hospital_no']);
    $note_type_ = cleanInput($_POST['note_type_']);

    $doctor_name = cleanInput($_POST['doctor_name']);
    $doctor_id = cleanInput($_POST['doctor_id']);

    $error_status = 1;
    $error_msg = 'Error :  Notes not saved...';


    $plainText = strip_tags($notes);
    $safeText = htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8');
    $wordCount = str_word_count($safeText);

    if ($diagnosis_ != '') {
        $error_status = 1;
        $error_msg = 'Error : Documentation Notes not saved...';
        $save_note = saveToNotes($db, $appointment_number, $hospital_no, $diagnosis_, 'DR', 'D', $doctor_name, null, $doctor_id, true);
    }


    if (!empty($notes) && trim($notes) !== '' && $wordCount >= 5) {

        $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, 'DR', $note_type_,  $doctor_name, null, $doctor_id, true);
        if ($save_note) {
            echo 'Saved Successful';
            /*             $rights = 'NS';
            // $db, $type, $pattern, $title, $message, $sender = null
            global_notify_(
                $db,
                'rights',
                $rights . '%',
                '<b>New Ward Round Notes for ' . $hospital_no . '</b>',
                'View Patient ' . "<a href='patient.php?hosp_no=" . htmlspecialchars($hospital_no) . "'> Click Here </a>",
                $doctor_name
            ); */
        } else {
            echo 'Data Not Successful';
        }
    } else {
        echo 'Error : Invalid notes! Please type at least 5 more words before you continue.';
    }
    exit;
}
