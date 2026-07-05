<?php

if (isset($_REQUEST['updateMedService'])) {

    $error_status = 1;
    $error_msg = 'Oops! Something went wrong..';
    $app_service_tbl_id = intval(cleanInput($_REQUEST['app_service_tbl_id_post']));
    $note_id = intval(cleanInput($_REQUEST['note_token']));
    $consultant_id = intval(cleanInput($_POST['consultant_id']));
    $consultant_name = cleanInput($_POST['consultant_name']);
    $notes = $_POST['genserviceNote'];
    $serviceTemplate = cleanInput($_POST['serviceTemplate']);
    $app_service_tbl_id = cleanInput($_POST['app_service_tbl_id']);
    $app_service_id = cleanInput($_POST['app_service_id']);
    $labrequest_no = cleanInput($_POST['app_service_id']);
    $updated_by_name = cleanInput($_POST['updated_by_name']);
    $service = cleanInput($_POST['service']);
    $cr = cleanInput($_POST['cr']);

    $template_id = null;
    $template_name = null;
    if (!empty($serviceTemplate)) {
        $data = explode('||', $serviceTemplate);
        $template_id = $data[0];
        $template_name = $data[1];
    }


    $isCompleted = false;
    if (isset($_POST['isCompleted'])) {
        $isCompleted = true;
    }


    $stmt = $db->prepare("UPDATE notes_services SET notes = ?, consultant_id = ?, consultant_name = ?, isCompleted = ? , template_id = ?,
         template_name = ? WHERE id = ? ");
    $stmt->execute([$notes, $consultant_id, $consultant_name, $isCompleted, $template_id, $template_name, $note_id]);

    $fullname = $_SESSION['fullname'];
    $fullname = str_replace("'", "", $fullname);
    $stmt = $db->prepare("UPDATE  patient_ap_services SET drug_status =1,dsp_by='$fullname',cr='$cr' WHERE sn=?");
    $stmt->execute(array($app_service_tbl_id));


    //// 
    if ($updated_by_name == 'doctor_result_status') {

        $stmt = $db->prepare("SELECT test_id,test_name FROM lab_manage WHERE labrequest_no=:labrequest_no");
        $stmt->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
            $test_id = $rowx['test_id'];
            $test_name = $rowx['test_name'];
        }

        ///===============================================================================
        $data_capture_status = 'approve';
        $lab_sci_speciality = $_SESSION['speciality'];
        $result_date = date("Y-m-d H:i:s");
        $sms_ = 0;
        $empty = '';
        $RQ_type = 'sl';

        $stmt = $db->prepare("SELECT * FROM lab_result WHERE lab_no=:lab_no and test_no=:test_id");
        $stmt->bindValue(':lab_no', $labrequest_no, PDO::PARAM_STR);
        $stmt->bindValue(':test_id', $test_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {





            $sql = $db->prepare("INSERT INTO lab_result (field_value,lab_no,test_no,test_name,specimen_collected,comment,notes,RQ_type,result_date,lab_sci_name,lab_sci_speciality,
									entered_by) VALUES (:field_value,:lab_no,:test_no,:test_name,:specimen_collected,:comment,:notes,:RQ_type,:result_date,:lab_sci_name,:lab_sci_speciality,
									:entered_by)");

            $sql->bindParam(':field_value', $notes, PDO::PARAM_STR);
            $sql->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR);
            $sql->bindParam(':test_no', $test_id, PDO::PARAM_STR);
            $sql->bindParam(':test_name', $test_name, PDO::PARAM_STR);
            $sql->bindParam(':specimen_collected', $empty, PDO::PARAM_STR);
            $sql->bindParam(':comment', $empty, PDO::PARAM_STR);
            $sql->bindParam(':notes', $notes, PDO::PARAM_STR);
            $sql->bindParam(':RQ_type', $RQ_type, PDO::PARAM_STR);
            $sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
            $sql->bindParam(':lab_sci_name', $consultant_name, PDO::PARAM_STR);
            $sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
            $sql->bindParam(':entered_by', $consultant_name, PDO::PARAM_STR);
            $sql->execute();
        } else {

            $updateSQL = "UPDATE lab_result SET field_value=:field_value,lab_sci_name=:lab_sci_name,
								lab_sci_speciality=:lab_sci_speciality,entered_by=:entered_by,result_date=:result_date,
								comment=:comment 
								WHERE lab_no=:lab_no and test_no=:test_no";
            $sql = $db->prepare($updateSQL);
            $sql->bindParam(':field_value', $notes, PDO::PARAM_STR);
            $sql->bindParam(':lab_sci_name', $consultant_name, PDO::PARAM_STR);
            $sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
            $sql->bindParam(':entered_by', $consultant_name, PDO::PARAM_STR);
            $sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
            $sql->bindParam(':comment', $empty, PDO::PARAM_STR);
            $sql->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR);
            $sql->bindParam(':test_no', $test_id, PDO::PARAM_STR);
            $sql->execute();
            //// keep track

            $sql = $db->prepare("INSERT INTO lab_result_old (field_value,lab_no,test_no,test_name,comment,RQ_type,result_date,lab_sci_name,lab_sci_speciality,entered_by) 
									VALUES (:field_value,:lab_no,:test_no,:test_name,:comment,:RQ_type,:result_date,:lab_sci_name,:lab_sci_speciality,:entered_by)");

            $sql->bindParam(':field_value', $notes, PDO::PARAM_STR);
            $sql->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR);
            $sql->bindParam(':test_no', $test_id, PDO::PARAM_STR);
            $sql->bindParam(':test_name', $test_name, PDO::PARAM_STR);
            $sql->bindParam(':comment', $empty, PDO::PARAM_STR);
            $sql->bindParam(':RQ_type', $RQ_type, PDO::PARAM_STR);
            $sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
            $sql->bindParam(':lab_sci_name', $consultant_name, PDO::PARAM_STR);
            $sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
            $sql->bindParam(':entered_by', $consultant_name, PDO::PARAM_STR);
            $sql->execute();
        }


        $updateSQL = "UPDATE lab_manage SET result_note=:result_note,lab_sci_name=:lab_sci_name,lab_sci_speciality=:lab_sci_speciality,
						entered_by=:entered_by,result_date=:result_date,data_capture_status=:data_capture_status,approved_by=:approved_by ,sms_status=:sms_status 
						WHERE labrequest_no=:labrequest_no";
        $sql = $db->prepare($updateSQL);
        $sql->bindParam(':result_note', $notes, PDO::PARAM_STR);
        $sql->bindParam(':lab_sci_name', $consultant_name, PDO::PARAM_STR);
        $sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
        $sql->bindParam(':entered_by', $consultant_name, PDO::PARAM_STR);
        $sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
        $sql->bindParam(':data_capture_status', $data_capture_status, PDO::PARAM_STR);
        $sql->bindParam(':approved_by', $consultant_name, PDO::PARAM_STR);
        $sql->bindParam(':sms_status', $sms_, PDO::PARAM_STR);
        $sql->bindParam(':labrequest_no', $app_service_id, PDO::PARAM_STR);
        $sql->execute();
    }



    $error_status = 2;
    $error_msg = 'Saved';
}

if (isset($_REQUEST['post_med_service_req'])) {

    $error_status = 1;
    $error_msg = 'Oops! Something went wrong..';
    $qty_serv_med = $_REQUEST['qty_serv_med'];
    $app_service_tbl_id = intval(cleanInput($_REQUEST['app_service_tbl_id']));
    if (!isset($app_no)) {
        $app_no = $hospital_no;
    }

    $notes = '';
    $stmt = $db->prepare("SELECT sn, category,price_table, insurance_type,dept, hosp_price,ext_price,nhis_price, item_service,consumable_setup FROM  prices_table  WHERE sn = ? ");
    $stmt->execute([$app_service_tbl_id]);
    if ($stmt->rowCount() > 0) {
        $serviceInfo = $stmt->fetch();
        $item_service = $serviceInfo['item_service'];
        $cat_type = $serviceInfo['category'];
        $serv_group = $serviceInfo['price_table'];
        $app_service_tbl_id = $serviceInfo['sn'];
        $hosp_price = $serviceInfo['hosp_price'];
        $ext_price = $serviceInfo['ext_price'];
        $nhis_price = $serviceInfo['nhis_price'];
        $dept_id = $serviceInfo['dept'];
        $is_free = $serviceInfo['consumable_setup'];


        $target_sn = $app_service_tbl_id;
        $NHIS_DRUG_CONSUMBL_STATE = 0;
        $_tariff_table = "hmo_medical_tariff";

        $amount_invoice = new_service_amount_cal(
            $db,
            $hospital_no,
            $app_no,
            $interest,
            $insurance_type,
            $insurance_no,
            $hosp_price,
            $ext_price,
            $nhis_price,
            $services_access,
            $add_minus,
            $payment_mode,
            $_tariff_table,
            $target_sn,
            $NHIS_DRUG_CONSUMBL_STATE,
            true
        );

        $claim_amt = $amount_invoice["claim_amt"];
        $amount_paying_single = $amount_invoice["amount_paying"];
        $pay_mode = $amount_invoice["pay_mode"];
        $ccop_int_charge = $amount_invoice["ccop_int_charge"];
        $invoice_no = $amount_invoice["invoice_no"];

        $amount_paying = $amount_paying_single  *  $qty_serv_med;
        $invoice_status = 1;
        $paystatus = 0;

        /// insert into notes_services
        try {
            // Begin transaction
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO patient_ap_services (
    app_no,hospital_no,access,serv_group,cat_type,dept_id,drug_sn,item_services,
    tag,hosp_price,claim_amt,interest,qty,remarks,drug_status,invoice_status,
    invoice_no,invoice_date,invoice_by,prepared_by,transact_date,pay,pay_mode,
    paystatus,process_claim,created_by
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");


            $date = date('Y-m-d H:i:s');
            $transact_date = null;

            $save = $stmt->execute(array(
                $app_no,
                $hospital_no,
                1,
                $serv_group,
                $cat_type,
                $dept_id,
                $app_service_tbl_id,
                $item_service,
                '',
                $hosp_price,
                $claim_amt,
                $ccop_int_charge,
                $qty_serv_med,
                '',
                '0',
                $invoice_status,
                $invoice_no,
                $date,
                $_SESSION['fullname'],
                $_SESSION['fullname'],
                NULL,
                $amount_paying,
                $pay_mode,
                $paystatus,
                '0',
                $_SESSION['id']
            ));

            if (!$save) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("patient_ap_services insert failed: " . $errorInfo[2]);
            }

            // Get inserted ID
            $id = $db->lastInsertId();

            $now_setdate = date('Y-m-d H:i:s');
            $stmt2 = $db->prepare("INSERT INTO notes_services (
        hospital_no, app_no, notes, app_service_tbl_id, created_at, 
        created_by, prepared_by, status, service, app_service_id, is_free
    ) VALUES (?, ?, ?, ?, ?, ?, ?, '1', ?, ?, ?)");

            $save2 = $stmt2->execute([
                $hospital_no,
                $app_no,
                $notes,
                $app_service_tbl_id,
                $now_setdate,
                $_SESSION['id'],
                $_SESSION['fullname'],
                $item_service,
                $id,
                $is_free
            ]);

            if (!$save2) {
                $errorInfo = $stmt2->errorInfo();
                throw new Exception("notes_services insert failed: " . $errorInfo[2]);
            }

            // Commit transaction if all successful
            $db->commit();

            $error_status = 2;
            $error_msg = 'Saved';
            //  echo json_encode(["status" => $error_status, "msg" => $error_msg, "id" => $id]);
        } catch (Exception $e) {
            // Only rollback if still in a transaction
            if ($db->inTransaction()) {
                try {
                    $db->rollBack();
                } catch (Exception $rollbackError) {
                    error_log("Rollback failed: " . $rollbackError->getMessage());
                }
            }

            $error_status = 1;
            $error_msg = $e->getMessage();

            // Optional: log detailed PDO error for debugging
            error_log("Error inserting med service: " . $error_msg);
        }
    }
}
