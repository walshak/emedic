<?php

include("../Connections/Conn.php");
session_start();

$setdate = date('Y-m-d H:i:s');
$zero = 0;
$one = 1;
$empty = '';
$adm_status = null;

if (isset($_POST['inv'])) {
    // Check if 'inv' is set and is an array


    $ledger_TX = 1;
    $sql = $db->prepare("INSERT INTO patient_ap_bill_group (ledger_id) VALUES (:ledger_id)");
    $sql->bindParam(':ledger_id', $ledger_TX, PDO::PARAM_STR);

    // Execute the statement and check if it was successful
    if ($sql->execute()) {
        // Get the last inserted ID
        $last_id = $db->lastInsertId();

        // Check if last_id is greater than zero
        if ($last_id > 0) {
            $last_id = str_pad($last_id, 3, "0", STR_PAD_LEFT);


            if ($last_id != '') {
                if (isset($_POST['inv']) && is_array($_POST['inv'])) {
                    $selectedRequests = $_POST['inv'];

                    // Loop through each selected request
                    foreach ($selectedRequests as $request) {

                        $updateSQL = "UPDATE lab_manage SET bill = :bill WHERE labrequest_no = :labrequest_no AND bill is null";
                        $stmt_update = $db->prepare($updateSQL);

                        // Bind parameters
                        $stmt_update->bindParam(':bill', $last_id, PDO::PARAM_STR);
                        $stmt_update->bindParam(':labrequest_no', $request, PDO::PARAM_STR);
                        $stmt_update->execute();
                    }

                    // Optionally, you can return a summary response
                    echo 'Total requests processed: ' . count($selectedRequests);
                } else {
                    echo 'No Item checkboxes selected.';
                }
            } else {
                echo 'Empty Lab Number';
            }
        } else {
            echo "Insertion failed, no ID returned.";
        }
    } else {
        // Handle the error if the execution failed
        echo "Insertion failed: " . implode(", ", $sql->errorInfo());
    }
}

if ($_POST["MM_update"] == 'insert_form_fields') {
    // Check if the field already exists for the given test_no
    $stmt = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_no AND field = :field_name");
    $stmt->bindParam(':test_no', $_POST["test_no"], PDO::PARAM_STR);
    $stmt->bindParam(':field_name', $_POST["field_name"], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // If the field does not exist, insert it into lab_scan_fields table
        $add_row = "INSERT INTO lab_scan_fields(test_no, field, field_type, reference) 
                    VALUES (:test_no, :field_name, :field_type, :Reference)";
        $stmt_add = $db->prepare($add_row);
        $stmt_add->bindParam(':test_no', $_POST["test_no"], PDO::PARAM_STR);
        $stmt_add->bindParam(':field_name', $_POST["field_name"], PDO::PARAM_STR);
        $stmt_add->bindParam(':field_type', $_POST["field_type"], PDO::PARAM_STR);
        $stmt_add->bindParam(':Reference', $_POST["Reference"], PDO::PARAM_STR);
        $stmt_add->execute();
    }

    // Include the refresh.php file
    include("refresh.php");

    // Call the lab_scan_fields function with appropriate parameters
    $sub = lab_scan_fields($test_id, $test_name, $field);
}


if ($_POST["MM_update"] == 'add_tips_info') {
    $username = $_POST['username'];
    $tips = $_POST['msg'];

    // Prepare the SQL statement
    $stmt = $db->prepare("UPDATE admin_users SET my_tips = :tips WHERE username = :username");

    // Bind parameters and execute the statement
    $stmt->bindParam(':tips', $tips, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}


if ($_POST["MM_update"] == 'change_password') {
    $username = $_POST['username'];
    $password = md5(strtolower($_POST['re_password']));

    // Prepare the SQL statement
    $stmt = $db->prepare("UPDATE admin_users SET password = :password WHERE username = :username");

    // Bind parameters and execute the statement
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}


if ($_POST["MM_update"] == 'Add_scan_request') {
    // Prepare the SQL statement
    $stmt = $db->prepare("UPDATE lab_manage SET data_capture_status = 'delete' WHERE labrequest_no = :scan_request_no");

    // Bind parameter and execute the statement
    $stmt->bindParam(':scan_request_no', $_POST['scan_request_no'], PDO::PARAM_STR);
    $stmt->execute();
}


if ($_POST["MM_update"] == 'approve_scan_request') {
    $RQ_No = $_POST["approve_scan_request"];

    // Include necessary file
    include("apr_combl.php");

    // Set the date
    $setdate = date('Y-m-d H:i:s');

    $stmt = $db->prepare("UPDATE lab_manage SET data_capture_status = 'result', result_date = :result_date WHERE labrequest_no = :RQ_No");

    $stmt->bindParam(':result_date', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':RQ_No', $RQ_No, PDO::PARAM_STR);
    $stmt->execute();
}


if ($_POST["MM_update"] == 'take_specimen') {

    $setdate = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE lab_manage SET collected_specimen = :collected_specimen, collected_by = :collected_by, collected_notes = :collected_notes, collected_date = :collected_date, data_capture_status = :data_capture_status WHERE labrequest_no = :labrequest_no");

    $stmt->bindParam(':collected_specimen', $_POST["Specimen"], PDO::PARAM_STR);
    $stmt->bindParam(':collected_by', $_SESSION["fullname"], PDO::PARAM_STR);
    $stmt->bindParam(':collected_notes', $_POST["specimen_notes"], PDO::PARAM_STR);
    $stmt->bindParam(':collected_date', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':data_capture_status', 'specimen', PDO::PARAM_STR);
    $stmt->bindParam(':labrequest_no', $_POST['labrequest_no'], PDO::PARAM_STR);

    $stmt->execute();


    if (isset($_POST["credit_status"])) {
        $credit_status = $_POST["credit_status"];
        $paystatus_title = $_POST["paystatus_title"];
        $setdate = date("Y-m-d");

        if ($paystatus_title == "claim") {
            $stmt2 = $db->prepare("UPDATE patient_ap_services SET invoice_status = :invoice_status, invoice_no = :invoice_no, dsp_by = :dsp_by, transact_date = :transact_date, paystatus = :paystatus WHERE drug_sn = :labrequest_no");

            $invoice_status = '1';
            $paystatus = '1';

            $stmt2->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
            $stmt2->bindParam(':invoice_no', $_POST['invoice_no'], PDO::PARAM_STR);
            $stmt2->bindParam(':dsp_by', $_SESSION["fullname"], PDO::PARAM_STR);
            $stmt2->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
            $stmt2->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
            $stmt2->bindParam(':labrequest_no', $_POST['labrequest_no'], PDO::PARAM_STR);

            $stmt2->execute();
        }

        if ($credit_status == 1) {
            $stmt2 = $db->prepare("UPDATE patient_ap_services SET cr = :cr WHERE drug_sn = :labrequest_no");

            $cr = '1';

            $stmt2->bindParam(':cr', $cr, PDO::PARAM_STR);
            $stmt2->bindParam(':labrequest_no', $_POST['labrequest_no'], PDO::PARAM_STR);

            $stmt2->execute();
        }
    }

    include_once("refresh.php");
    $sub = queue();
}


if ($_POST["MM_update"] == 'add_field_values') {
    $stmt = $db->prepare("SELECT * FROM lab_scan_rlts_values WHERE field_id_no = :field_opt_no AND options = :options");
    $stmt->bindParam(':field_opt_no', $_POST["field_opt_no"], PDO::PARAM_STR);
    $stmt->bindParam(':options', $_POST["options"], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $add_row = $db->prepare("INSERT INTO lab_scan_rlts_values(field_id_no, options, reference) VALUES (:field_opt_no, :options, :Reference)");
        $add_row->bindParam(':field_opt_no', $_POST["field_opt_no"], PDO::PARAM_STR);
        $add_row->bindParam(':options', $_POST["options"], PDO::PARAM_STR);
        $add_row->bindParam(':Reference', $_POST["Reference"], PDO::PARAM_STR);
        $add_row->execute();
    }
}

if ($_POST["MM_update"] == 'add_field_option') {
    $stmt = $db->prepare("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_opt_no AND options = :options");
    $stmt->bindParam(':field_opt_no', $_POST["field_opt_no"], PDO::PARAM_STR);
    $stmt->bindParam(':options', $_POST["options"], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $add_row = $db->prepare("INSERT INTO lab_scan_rlts_opt(field_id_no, options) VALUES (:field_opt_no, :options)");
        $add_row->bindParam(':field_opt_no', $_POST["field_opt_no"], PDO::PARAM_STR);
        $add_row->bindParam(':options', $_POST["options"], PDO::PARAM_STR);
        $add_row->execute();
    }
}

if ($_POST["MM_update"] == 'fill_result_form') {

    $labrequest_no = $_POST["labrequest_no"];

    if (isset($_POST['input_ty']) and $_POST['input_ty'] != '') {
        $input_ty = $_POST['input_ty'];
    } else {
        //	header("location:mgt.php");
    }

    if ($input_ty == 'values' and !empty($_POST['fvalue'])) {

        ///$deleteSQL = "DELETE FROM lab_scan_input_results WHERE lab_request_no='$labrequest_no'"; $db->exec($deleteSQL);

        for ($i = 0; $i < count($_POST['fvalues']); $i++) {

            $stmt_chk = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :labrequest_no AND test_no = :test_no AND field_no = :field_no AND value_sn = :value_sn");
            $stmt_chk->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
            $stmt_chk->bindParam(':test_no', $_POST['test_noo'][$i], PDO::PARAM_STR);
            $stmt_chk->bindParam(':field_no', $_POST['row_fields_fields'][$i], PDO::PARAM_STR);
            $stmt_chk->bindParam(':value_sn', $_POST['values_sn'][$i], PDO::PARAM_STR);
            $stmt_chk->execute();

            if ($stmt_chk->rowCount() == 0) {
                if ($_POST['fvalues'][$i] == '') {
                    $result = 'NIL';
                } else {
                    $result = $_POST['fvalues'][$i];
                }

                $sql = "INSERT INTO lab_scan_input_results (lab_request_no, test_no, field_no, value_sn, value_title, value_ref, result) 
									VALUES (:lab_request_no, :test_no, :field_no, :value_sn, :value_title, :value_ref, :result)";
                $stmt = $db->prepare($sql);

                // Bind parameters
                $stmt->bindParam(':lab_request_no', $_POST['labrequest_no'], PDO::PARAM_STR);
                $stmt->bindParam(':test_no', $_POST['test_noo'][$i], PDO::PARAM_STR);
                $stmt->bindParam(':field_no', $_POST['row_fields_fields'][$i], PDO::PARAM_STR);
                $stmt->bindParam(':value_sn', $_POST['values_sn'][$i], PDO::PARAM_STR);
                $stmt->bindParam(':value_title', $_POST['values_title'][$i], PDO::PARAM_STR);
                $stmt->bindParam(':value_ref', $_POST['values_fre'][$i], PDO::PARAM_STR);
                $stmt->bindParam(':result', $result, PDO::PARAM_STR); // Ensure $result holds the correct value

                // Execute the statement
                $stmt->execute();
            } else {
                //// result was eneter before////
                $rwxs = $stmt_chk->fetch(PDO::FETCH_ASSOC);
                if ($_POST['fvalues'][$i] == '') {
                    $new_result = 'NIL';
                } else {
                    $new_result = $_POST['fvalues'][$i];
                }


                if ($_POST['values_sn'][$i] == $rwxs['value_sn'] and $new_result == $rwxs['result']) {
                } else {

                    $sql = "INSERT INTO lab_scan_input_results_old (lab_request_no, test_no, field_no, value_sn, value_title, value_ref, result) 
											VALUES (:lab_request_no, :test_no, :field_no, :value_sn, :value_title, :value_ref, :result)";
                    $stmt = $db->prepare($sql);

                    // Bind parameters
                    $stmt->bindParam(':lab_request_no', $rwxs['lab_request_no'], PDO::PARAM_STR);
                    $stmt->bindParam(':test_no', $rwxs['test_no'], PDO::PARAM_STR);
                    $stmt->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
                    $stmt->bindParam(':value_sn', $rwxs['value_sn'], PDO::PARAM_STR);
                    $stmt->bindParam(':value_title', $rwxs['value_title'], PDO::PARAM_STR);
                    $stmt->bindParam(':value_ref', $rwxs['value_ref'], PDO::PARAM_STR);
                    $stmt->bindParam(':result', $rwxs['result'], PDO::PARAM_STR);

                    // Execute the statement
                    $stmt->execute();


                    //// main table
                    /// update manage lab table
                    $setdate = date('Y-m-d H:i:s');
                    $sql = "UPDATE lab_scan_input_results 
        SET result = :result 
        WHERE lab_request_no = :lab_request_no 
        AND test_no = :test_no 
        AND field_no = :field_no 
        AND value_sn = :value_sn";
                    $stmt = $db->prepare($sql);

                    // Bind parameters
                    $stmt->bindParam(':result', $_POST['fvalues'][$i], PDO::PARAM_STR);
                    $stmt->bindParam(':lab_request_no', $labrequest_no, PDO::PARAM_STR); // Assuming $labrequest_no is already defined
                    $stmt->bindParam(':test_no', $rwxs['test_no'], PDO::PARAM_STR);
                    $stmt->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
                    $stmt->bindParam(':value_sn', $rwxs['value_sn'], PDO::PARAM_STR);

                    // Execute the statement
                    $stmt->execute();
                }
                //////////////////====================================================================================

            }
        }
    }

    //	$deleteSQL = "DELETE FROM lab_result WHERE lab_no='$labrequest_no'"; $db->exec($deleteSQL);


    for ($i = 0; $i < count($_POST['fvalue']); $i++) {

        $sql = "SELECT * FROM lab_result WHERE field_no = :field_no AND field_name = :field_name AND lab_no = :lab_no";
        $stmt = $db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':field_no', $_POST['fno'][$i], PDO::PARAM_STR);
        $stmt->bindParam(':field_name', $_POST['fname'][$i], PDO::PARAM_STR);
        $stmt->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR); // Assuming $labrequest_no is already defined

        // Execute the statement
        $stmt->execute();

        // Check if rows were returned
        if ($stmt->rowCount() == 0) {

            /////////////////////////// NEW LAB RESULTS ////////////////////////////////

            if ($_POST['fvalue'][$i] == '') {
                $result = 'NIL';
            } else {
                $result = $_POST['fvalue'][$i];
            }


            $sql = "INSERT INTO lab_result (field_no, field_name, field_value, field_ref, lab_no, test_no, test_name, specimen_collected, comment, notes, RQ_type, result_date, lab_sci_name, lab_sci_speciality, entered_by) 
									VALUES (:field_no, :field_name, :field_value, :field_ref, :lab_no, :test_no, :test_name, :specimen_collected, :comment, :notes, :RQ_type, :result_date, :lab_sci_name, :lab_sci_speciality, :entered_by)";
            $stmt = $db->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':field_no', $_POST['fno'][$i], PDO::PARAM_STR);
            $stmt->bindParam(':field_name', $_POST['fname'][$i], PDO::PARAM_STR);
            $stmt->bindParam(':field_value', $result, PDO::PARAM_STR); // Assuming $result holds the correct value
            $stmt->bindParam(':field_ref', $_POST['fre'][$i], PDO::PARAM_STR);
            $stmt->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR); // Assuming $labrequest_no is already defined
            $stmt->bindParam(':test_no', $_POST['test_id'], PDO::PARAM_STR);
            $stmt->bindParam(':test_name', $_POST['test_name'], PDO::PARAM_STR);
            $stmt->bindParam(':specimen_collected', $_POST['Specimen'][$i], PDO::PARAM_STR);
            $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
            $stmt->bindParam(':notes', $_POST['notes'], PDO::PARAM_STR);
            $stmt->bindParam(':RQ_type', $_POST['RQ_type'], PDO::PARAM_STR);
            $stmt->bindParam(':result_date', $setdate, PDO::PARAM_STR); // Assuming $setdate is defined as 'Y-m-d H:i:s'
            $stmt->bindParam(':lab_sci_name', $_POST['lab_sci_name'], PDO::PARAM_STR);
            $stmt->bindParam(':lab_sci_speciality', $_POST['lab_sci_speciality'], PDO::PARAM_STR);
            $stmt->bindParam(':entered_by', $_POST['entered_by'], PDO::PARAM_STR);

            // Execute the statement
            $stmt->execute();


            /////////////////////////// END NEW LAB RESULTS ////////////////////////////////
        } else {
            //// result was eneter before////
            $rwxs = $stmt_chk->fetch(PDO::FETCH_ASSOC);
            if ($_POST['fvalue'][$i] == '') {
                $new_result = 'NIL';
            } else {
                $new_result = $_POST['fvalue'][$i];
            }
            if ($_POST['fno'][$i] == $rwxs['field_no'] and $new_result == $rwxs['field_value']) {
            } else {
                //// SAVE OLD RESULT % UPDATE MAIN TABLE

                $sqlInsertOld = "INSERT INTO lab_result_old (field_no, field_name, field_value, field_ref, lab_no, test_no, test_name, comment, RQ_type, result_date) 
													VALUES (:field_no, :field_name, :field_value, :field_ref, :lab_no, :test_no, :test_name, :comment, :RQ_type, :result_date)";
                $stmtInsertOld = $db->prepare($sqlInsertOld);

                // Bind parameters
                $stmtInsertOld->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':field_name', $rwxs['field_name'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':field_value', $rwxs['field_value'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':field_ref', $rwxs['field_ref'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR); // Assuming $labrequest_no is defined
                $stmtInsertOld->bindParam(':test_no', $rwxs['test_no'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':test_name', $rwxs['test_name'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':comment', $rwxs['comment'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':RQ_type', $rwxs['RQ_type'], PDO::PARAM_STR);
                $stmtInsertOld->bindParam(':result_date', $rwxs['result_date'], PDO::PARAM_STR);

                // Execute the statement
                $stmtInsertOld->execute();

                //// main table
                /// update manage lab table
                $setdate = date('Y-m-d H:i:s');

                $sqlUpdate = "UPDATE lab_result SET field_value = :field_value, comment = :comment, result_date = :result_date 
              WHERE lab_no = :lab_no AND field_no = :field_no";
                $stmtUpdate = $db->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':field_value', $_POST['fvalue'][$i], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':result_date', $setdate, PDO::PARAM_STR); // Assuming $setdate is defined
                $stmtUpdate->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR); // Assuming $labrequest_no is defined
                $stmtUpdate->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
                $stmtUpdate->execute();
            }
        }
    }

    $data_capture_status = 'result'; // Example value, replace with actual logic or variable
    $labrequest_no = $_POST['labrequest_no']; // Assuming this is properly sanitized and validated

    $sqlUpdate = "UPDATE lab_manage SET data_capture_status = :data_capture_status WHERE labrequest_no = :labrequest_no";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':data_capture_status', $data_capture_status, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
    $stmtUpdate->execute();
}


if ($_POST["MM_update"] == 'Add_lab_request') {

    try {
        // Start transaction
        $db->beginTransaction();

        $setdate = date('Y-m-d H:i:s');
        $setdate2 = date('Y-m-d');
        $sn = 0;
        $adm_status = 0;
        $patient = $_POST["patient_no"];
        $patient_name = $_POST["patient_name"];
        $rights = $_POST["rights"];

        $stmt = $db->prepare("SELECT i.interest, i.insurance_type, i.insurance_name, i.insurance_no, i.add_minus, i.payment_mode FROM enrollee AS e INNER JOIN insurance_tbl AS i ON e.hmo_no = i.insurance_no WHERE e.hospital_no = :hospital_no AND i.status = 'active' LIMIT 1");
        $stmt->bindParam(':hospital_no', $patient, PDO::PARAM_STR);
        $stmt->execute();

        if ($rwxx = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Found enrollee insurance info
            $interest       = $rwxx['interest'];
            $insurance      = $rwxx['insurance_type'];
            $insurance_name = $rwxx['insurance_name'];
            $insurance_no   = $rwxx['insurance_no'];
            $add_minus      = $rwxx['add_minus'];
            $payment_mode   = $rwxx['payment_mode'];
            $buz = "IN";

            // Admission check - get latest admission with status '3'
            $stmt_adm = $db->prepare("SELECT app_no FROM admission WHERE adm_status = '3' AND hospital_no = :hospital_no ORDER BY sn DESC LIMIT 1");
            $stmt_adm->bindParam(':hospital_no', $patient, PDO::PARAM_STR);
            $stmt_adm->execute();
            if ($row_adm = $stmt_adm->fetch(PDO::FETCH_ASSOC)) {
                $adm_status = 1;
                $adm_code = $row_adm['app_no'];
            } else {
                $adm_status = 0;
            }
        } else {
            // Fall back: try to get referral from pharm_ext
            $stmt2 = $db->prepare("	SELECT referral FROM pharm_ext 	WHERE transc_code = :transc_code LIMIT 1");
            $stmt2->bindParam(':transc_code', $patient, PDO::PARAM_STR);
            $stmt2->execute();

            if ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $Referred = htmlspecialchars($roww['referral']);
            } else {
                $Referred = ''; // fallback default if nothing is found
            }
            $buz = "EX";
        }
        $gn = mt_rand(10000, 99999);
        // Try to fetch today's lab request for this patient
        $stmt = $db->prepare("
			SELECT group_id 
			FROM lab_manage 
			WHERE patient = :patient 
			  AND DATE(request_date) = :request_date
			LIMIT 1
		");
        $stmt->bindValue(':patient', $patient, PDO::PARAM_STR);
        $stmt->bindValue(':request_date', $setdate2, PDO::PARAM_STR);
        $stmt->execute();

        // Use existing group_id if available and no admission
        if ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $group_id = ($adm_status === '0') ? $roww['group_id'] : 'ADM_' . $adm_code;
        } else {
            // New group ID for walk-in or not admitted
            $group_id = date('d') . $gn;
        }


        if (!empty($_POST["specimen"]) && is_array($_POST["specimen"])) {
            // Safely implode selected specimen values into a string
            $all_specimen = implode(', ', array_map('trim', $_POST["specimen"]));
        } else {
            $all_specimen = '';
        }


        if (isset($_POST["lab_request"]) and $_POST["lab_request"] != '') {
            $values = $_POST['lab_request'];
            $combo_test = 0;

            $stmt = $db->query("SELECT MAX(sn) AS max_sn FROM lab_manage");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $sn = ($row && $row['max_sn']) ? ($row['max_sn'] + 1) : 1;


            foreach ($values as $a) {
                $lab_request = $a;
                $part = explode("__", $lab_request);
                $test_sn = $part[0];
                $test_name = $part[1];
                $dept_id = $part[2];
                $hosp_price = $part[3];
                $nhis_price = $part[4];
                $coverage = $part[5];
                $nhis_type = $part[6];
                $section = $part[7];
                $ext_price = $part[8];
                $combo_test = $part[9];

                // Determine billing based on patient type
                if ($buz === 'IN') {
                    $target_sn = $test_sn;
                    $NHIS_DRUG_CONSUMBL_STATE = null;
                    $_tariff_table = "hmo_investigation_tariff";
                    include("../inc/price_calc.php");
                } else {
                    // External patient pricing
                    $amt_paying       = ($ext_price > 0) ? $ext_price : $hosp_price;
                    $pay_mode         = "cash";
                    $claim_amt        = 0;
                    $ccop_int_charge  = 0;
                }

                // Generate appointment number (e.g., 030725 for 3rd July 2025)
                $appointment_no = date('dmy');

                // Set test category and prefix code
                if ($section === 'Laboratory') {
                    $cat_type = 'Laboratory Test';
                    $code     = 'LB';
                } else {
                    $cat_type = 'Scan/Imaging';
                    $code     = 'RD';
                }

                $setdate2 = date("Y-m-d");
                $queue = "queue";
                $C = ($combo_test == 1) ? "C" : "S";
                $lab_reqno = $code . $C . date('d') . $patient . sprintf('%08d', $sn);
                $stmt = $db->prepare("SELECT * FROM lab_manage 
                WHERE patient = :patient 
                AND test_name = :test_name 
                AND data_capture_status = :data_capture_status 
                AND DATE(request_date) = :request_date");

                $stmt->bindParam(':patient', $patient, PDO::PARAM_STR);
                $stmt->bindParam(':test_name', $test_name, PDO::PARAM_STR);
                $stmt->bindParam(':data_capture_status', $queue, PDO::PARAM_STR);
                $stmt->bindParam(':request_date', $setdate2, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $doctor_name = ucwords($_POST["doctor_name"]);
                    $stmt2 = $db->prepare("INSERT INTO lab_manage(app_no, labrequest_no, patient, patient_name, test_id, test_name, lab_cat, section, group_id, business_service_center, referral, preferred_specimen, request_note, request_date, request_date2, request_by, lab_combos, requesting_physician) 
                VALUES (:app_no, :labrequest_no, :patient, :patient_name, :test_id, :test_name, :lab_cat, :section, :group_id, :business_service_center, :referral, :preferred_specimen, :request_note, :request_date, :request_date2, :request_by, :lab_combos, :requesting_physician)");

                    $stmt2->bindParam(':app_no', $appointment_no, PDO::PARAM_STR);
                    $stmt2->bindParam(':labrequest_no', $lab_reqno, PDO::PARAM_STR);
                    $stmt2->bindParam(':patient', $patient, PDO::PARAM_STR);
                    $stmt2->bindParam(':patient_name', $patient_name, PDO::PARAM_STR);
                    $stmt2->bindParam(':test_id', $test_sn, PDO::PARAM_STR);
                    $stmt2->bindParam(':test_name', $test_name, PDO::PARAM_STR);
                    $stmt2->bindParam(':lab_cat', $dept_id, PDO::PARAM_STR);
                    $stmt2->bindParam(':section', $section, PDO::PARAM_STR);
                    $stmt2->bindParam(':group_id', $group_id, PDO::PARAM_STR);
                    $stmt2->bindParam(':business_service_center', $buz, PDO::PARAM_STR);
                    $stmt2->bindParam(':referral', $Referred, PDO::PARAM_STR);
                    $stmt2->bindParam(':preferred_specimen', $all_specimen, PDO::PARAM_STR);
                    $stmt2->bindParam(':request_note', $_POST["request_note"], PDO::PARAM_STR);
                    $stmt2->bindParam(':request_date', $setdate, PDO::PARAM_STR);
                    $stmt2->bindParam(':request_date2', $setdate2, PDO::PARAM_STR);
                    $stmt2->bindParam(':request_by', $_SESSION["fullname"], PDO::PARAM_STR);
                    $stmt2->bindParam(':lab_combos', $combo_test, PDO::PARAM_STR);
                    $stmt2->bindParam(':requesting_physician', $doctor_name, PDO::PARAM_STR);
                    $stmt2->execute();

                    $sn++;
                    $specimen = '';

                    $three = 3;
                    $one = 1;
                    $stmt = $db->prepare("INSERT INTO patient_ap_services(app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, remarks, drug_status, invoice_status, invoice_no, prepared_by, created_by, dsp_by, date_entry, transact_date, pay, pay_mode, paystatus, process_claim) 
                VALUES (:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, :interest, :qty, :remarks, :drug_status, :invoice_status, :invoice_no, :prepared_by, :created_by, :dsp_by, :date_entry, :transact_date, :pay, :pay_mode, :paystatus, :process_claim)");
                    $stmt->bindParam(':app_no', $appointment_no, PDO::PARAM_STR);
                    $stmt->bindParam(':hospital_no', $patient, PDO::PARAM_STR);
                    $stmt->bindValue(':access', $three, PDO::PARAM_STR);
                    $stmt->bindParam(':serv_group', $section, PDO::PARAM_STR);
                    $stmt->bindParam(':cat_type', $cat_type, PDO::PARAM_STR);
                    $stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                    $stmt->bindParam(':drug_sn', $lab_reqno, PDO::PARAM_STR);
                    $stmt->bindParam(':item_services', $test_name, PDO::PARAM_STR);
                    $stmt->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR);
                    $stmt->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                    $stmt->bindParam(':interest', $ccop_int_charge, PDO::PARAM_STR);
                    $stmt->bindValue(':qty', $one, PDO::PARAM_STR);
                    $stmt->bindValue(':remarks', $empty, PDO::PARAM_STR);
                    $stmt->bindValue(':drug_status', $zero, PDO::PARAM_STR);
                    $stmt->bindValue(':invoice_status', $zero, PDO::PARAM_STR);
                    $stmt->bindValue(':invoice_no', $empty, PDO::PARAM_STR);
                    $stmt->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
                    $stmt->bindParam(':created_by', $_SESSION['id'], PDO::PARAM_STR);
                    $stmt->bindValue(':dsp_by', $empty, PDO::PARAM_STR);
                    $stmt->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
                    $stmt->bindValue(':transact_date', null, PDO::PARAM_STR);
                    $stmt->bindParam(':pay', $amt_paying, PDO::PARAM_STR);
                    $stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
                    $stmt->bindValue(':paystatus', $zero, PDO::PARAM_STR);
                    $stmt->bindValue(':process_claim', $zero, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }

        // Commit transaction
        $db->commit();
        echo json_encode(["status" => "1", "patient" => $patient, "rights" => $rights, "buz" => $buz]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        echo "Failed: " . $e->getMessage();
    }

    //include_once("refresh.php");
    //$sub = queue();
}



if ($_POST["MM_update"] == 'add_combos_option') {
    $values = $_POST['lab_request'];
    foreach ($values as $a) {
        $test_id = $a;

        $stmt = $db->prepare("SELECT * FROM lab_combos_items WHERE test_id = :test_id AND combos_id = :com_id");
        $stmt->bindParam(':test_id', $test_id, PDO::PARAM_STR);
        $stmt->bindParam(':com_id', $_POST["com_id"], PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $com_id = $_POST["com_id"];
            $stmtx = $db->prepare("INSERT INTO lab_combos_items(combos_id, test_id) VALUES (:com_id, :test_id)");
            $stmtx->bindParam(':com_id', $com_id, PDO::PARAM_STR);
            $stmtx->bindParam(':test_id', $test_id, PDO::PARAM_STR);
            $stmtx->execute();
        }
    }
}


if ($_POST["MM_update"] == 'edit_lab_body') {

    $string = $_POST["lab_test_name2"];
    $charactersToRemove = '*/=%",;:\'.'; // Add '.' to the list
    $pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
    $cleanedString = preg_replace($pattern, '', $string);

    $update = $db->prepare("UPDATE lab_scan 
                        SET test=:cleanedString, 
                            category=:category, 
                            sub_category=:sub_category, 
                            dept=:Department2, 
                            combo_test=:labcombos,
                            doctor_result_status=:doctor_result_status
                        WHERE sn=:lab_test_no2");

    $update->bindParam(':cleanedString', $cleanedString, PDO::PARAM_STR);
    $update->bindParam(':category', $_POST['category'], PDO::PARAM_STR);
    $update->bindParam(':sub_category', $_POST['sub_category'], PDO::PARAM_STR);
    $update->bindParam(':Department2', $_POST['Department2'], PDO::PARAM_STR);
    $update->bindParam(':labcombos', $_POST['labcombos'], PDO::PARAM_STR);
    $update->bindParam(':doctor_result_status', $_POST['doctor_can_upload'], PDO::PARAM_STR);
    $update->bindParam(':lab_test_no2', $_POST['lab_test_no2'], PDO::PARAM_INT); // Assuming lab_test_no2 is an integer

    $update->execute();
    include("refresh.php");
    $sub = labs();
}


if ($_POST["MM_update"] == 'manage_price_body') {

    if ($_POST["NHIS_price"] > 0) {
        $coverage = "PRIVATE/NHIS";
        $insurance_type = $_POST['insurance_type'];
    } else {
        $coverage = "PRIVATE";
        $insurance_type = "-";
    }

    // Assuming $db is your PDO connection object
    $update = $db->prepare("UPDATE lab_scan 
                        SET coverage=:coverage, 
                            insurance_type=:insurance_type, 
                            hosp_price=:hosp_price, 
                            ext_price=:external_price, 
                            nhis_price=:NHIS_price 
                        WHERE sn=:lab_test_no");

    $update->bindParam(':coverage', $_POST['coverage'], PDO::PARAM_STR);
    $update->bindParam(':insurance_type', $_POST['insurance_type'], PDO::PARAM_STR);
    $update->bindParam(':hosp_price', $_POST['hosp_price'], PDO::PARAM_STR);
    $update->bindParam(':external_price', $_POST['external_price'], PDO::PARAM_STR);
    $update->bindParam(':NHIS_price', $_POST['NHIS_price'], PDO::PARAM_STR);
    $update->bindParam(':lab_test_no', $_POST['lab_test_no'], PDO::PARAM_INT); // Assuming lab_test_no is an integer

    $update->execute();


    include("refresh.php");
    $sub = labs();
}

if ($_POST["MM_update"] == 'Add_Test_Body') {

    $string = $_POST["labname"];
    $sub_category = $_POST["sub_category"];

    $charactersToRemove = '*/=%",;:\'.'; // Add '.' to the list
    $pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
    $cleanedString = preg_replace($pattern, '', $string);

    $stmt = $db->prepare("SELECT * FROM lab_scan WHERE test = :test");
    $stmt->bindParam(':test', $cleanedString, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $part = explode("__", $_POST['Department']);
        $dept_id = $part[0];
        $dept_type = $part[1];
        $LC = isset($_POST["labcombos"]) ? 1 : 0;

        // Assuming $db is your PDO connection object
        $insertSQL = $db->prepare("INSERT INTO lab_scan (test, category, sub_category, dept, combo_test) 
                           VALUES (:test, :category, :sub_category, :dept, :combo_test)");

        $insertSQL->bindParam(':test', $cleanedString, PDO::PARAM_STR);
        $insertSQL->bindParam(':category', $dept_type, PDO::PARAM_STR);
        $insertSQL->bindParam(':sub_category', $sub_category, PDO::PARAM_STR);
        $insertSQL->bindParam(':dept', $dept_id, PDO::PARAM_STR); // Adjust the data type if dept_id is not a string
        $insertSQL->bindParam(':combo_test', $LC, PDO::PARAM_STR);
        $insertSQL->execute();

        $msg = "";
    }

    include("refresh.php");
    $sub = labs();
}


if ($_POST["MM_update"] == 'add_new_combos_form') {


    // Check if the combos_name already exists in lab_combos
    $stmt = $db->prepare("SELECT * FROM lab_combos WHERE combos_name = :combos_name");
    $stmt->bindParam(':combos_name', $_POST["combos"], PDO::PARAM_STR);
    $stmt->execute();

    // If no rows are returned, insert the new combos_name
    if ($stmt->rowCount() == 0) {
        $insertSQL = $db->prepare("INSERT INTO lab_combos (combos_name) VALUES (:combos_name)");
        $insertSQL->bindParam(':combos_name', $_POST['combos'], PDO::PARAM_STR);
        $insertSQL->execute();
    }


    include("refresh.php");
    $sub = combos();
}

if ($_POST["MM_update"] == 'add_reagent') {
    //
    // Check if the reagent already exists in lab_reagent
    $stmt = $db->prepare("SELECT * FROM lab_reagent WHERE reagent = :reagent");
    $stmt->bindParam(':reagent', $_POST["agent_name"], PDO::PARAM_STR);
    $stmt->execute();

    // If no rows are returned, insert the new reagent
    if ($stmt->rowCount() == 0) {
        $insertSQL = $db->prepare("INSERT INTO lab_reagent (reagent, qty_test) VALUES (:reagent, :qty_test)");
        $insertSQL->bindParam(':reagent', $_POST['agent_name'], PDO::PARAM_STR);
        $insertSQL->bindParam(':qty_test', $_POST['agent_qty'], PDO::PARAM_STR);
        $insertSQL->execute();
    }


    include("refresh.php");
    $sub = reagent();
}


if ($_POST["MM_update"] == 'add_labtest_consumable') {

    $lab_test = $_POST['lab_test'];

    foreach ($lab_test as $a) {
        $lab_test = $a;

        $part = explode("__", $lab_test);
        $test_sn = $part[0];
        $test_name = $part[1];

        $updateStmt = $db->prepare("UPDATE lab_scan SET consumable_setup = :consumable_setup WHERE sn = :sn");
        $consumable_setup_value = '1';
        $updateStmt->bindParam(':consumable_setup', $consumable_setup_value, PDO::PARAM_STR);
        $updateStmt->bindParam(':sn', $test_sn, PDO::PARAM_STR);
        $updateStmt->execute();
    }

    //include("refresh.php");
    //$sub=reagent();
}

if ($_POST["MM_update"] == 'adding_stocks') {

    $stock_id = $_POST['stock_id'];

    if ($stock_id == '') {

        $capture_date = date("Y-m-d");
        $stmt = $db->prepare("SELECT * FROM lab_stocks WHERE stock_name = :stock_name");
        $stmt->bindParam(':stock_name', $_POST["stockname"], PDO::PARAM_STR);
        $stmt->execute();

        $whole_units = $_POST['qty'] * $_POST['units'];

        if ($stmt->rowCount() == 0) {

            $stmt = $db->prepare("INSERT INTO lab_stocks(stock_name, cat, buying_cost, batch_no, qty, stock_total_unit, whole_units, measurement, reorder_level, expire_date, date_captured) VALUES(:stock_name, :cat, :buying_cost, :batch_no, :qty, :stock_total_unit, :whole_units, :measurement, :reorder_level, :expire_date, :date_captured)");

            $stmt->bindParam(':stock_name', $_POST['stockname'], PDO::PARAM_STR);
            $stmt->bindParam(':cat', $_POST['category'], PDO::PARAM_STR);
            $stmt->bindParam(':buying_cost', $_POST['purchase_cost'], PDO::PARAM_STR);
            $stmt->bindParam(':batch_no', $_POST['batchno'], PDO::PARAM_STR);
            $stmt->bindParam(':qty', $_POST['qty'], PDO::PARAM_INT);
            $stmt->bindParam(':stock_total_unit', $_POST['units'], PDO::PARAM_INT);
            $stmt->bindParam(':whole_units', $whole_units, PDO::PARAM_INT);
            $stmt->bindParam(':measurement', $_POST['measurement'], PDO::PARAM_STR);
            $stmt->bindParam(':reorder_level', $_POST['reorder'], PDO::PARAM_INT);
            $stmt->bindParam(':expire_date', $_POST['expired'], PDO::PARAM_STR);
            $stmt->bindParam(':date_captured', $capture_date, PDO::PARAM_STR);
            $stmt->execute();
        }
    } else {

        $stmt = $db->prepare("UPDATE lab_stocks SET stock_name=:stock_name, cat=:cat, buying_cost=:buying_cost, stock_total_unit=:stock_total_unit, measurement=:measurement, reorder_level=:reorder_level, expire_date=:expire_date, status=:status WHERE stock_sn=:stock_sn");

        $stmt->bindParam(':stock_name', $_POST['stockname'], PDO::PARAM_STR);
        $stmt->bindParam(':cat', $_POST['category'], PDO::PARAM_STR);
        $stmt->bindParam(':buying_cost', $_POST['purchase_cost'], PDO::PARAM_STR);
        $stmt->bindParam(':stock_total_unit', $_POST['units'], PDO::PARAM_INT);
        $stmt->bindParam(':measurement', $_POST['measurement'], PDO::PARAM_STR);
        $stmt->bindParam(':reorder_level', $_POST['reorder'], PDO::PARAM_INT);
        $stmt->bindParam(':expire_date', $_POST['expired'], PDO::PARAM_STR);
        $stmt->bindParam(':status', 'active', PDO::PARAM_STR);
        $stmt->bindParam(':stock_sn', $stock_id, PDO::PARAM_INT);

        $stmt->execute();
    }
}


if ($_POST["MM_update"] == 'add_labtest_consumable_items') {

    $lab_test = $_POST['lab_test'];

    $part = explode("__", $lab_test);
    $test_sn = $part[0];
    $test_name = $part[1];

    $stmt = $db->prepare("SELECT * FROM lab_test_consumble WHERE lab_test_no = :test_no AND consumable_no = :consumable");
    $stmt->bindParam(':test_no', $_POST['test_no'], PDO::PARAM_STR);
    $stmt->bindParam(':consumable', $_POST['consumable'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO lab_test_consumble(lab_test_no, consumable_no, qty_test) VALUES (:lab_test_no, :consumable_no, :qty_test)");

        $stmt->bindParam(':lab_test_no', $_POST['test_no'], PDO::PARAM_STR);
        $stmt->bindParam(':consumable_no', $_POST['consumable'], PDO::PARAM_STR);
        $stmt->bindParam(':qty_test', $_POST['whole_qty'], PDO::PARAM_INT);

        $stmt->execute();
    }
}


if ($_POST["MM_update"] == 'adding_cat') {

    $stmt = $db->prepare("SELECT * FROM lab_stocks_cat WHERE name=:name");
    $stmt->bindParam(':name', $_POST['cat'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $insertStmt = $db->prepare("INSERT INTO lab_stocks_cat(name) VALUES(:name)");
        $insertStmt->bindParam(':name', $_POST['cat'], PDO::PARAM_STR);
        $insertStmt->execute();
    }
}


if ($_POST["MM_update"] == 'approve_results_2') {

    if (isset($_POST["abnormal"])) {
        $ab = 1;
    } else {
        $ab = 0;
    }

    $RQ_No = $_POST["approve_id"];
    // include("apr_combl.php");

    $status = "approve";
    $stmt = $db->prepare("UPDATE lab_manage SET abnormal_results=:abnormal_results, data_capture_status=:data_capture_status, approved_by=:approved_by, result_date=:result_date WHERE labrequest_no=:labrequest_no");

    $stmt->bindParam(':abnormal_results', $ab, PDO::PARAM_STR);
    $stmt->bindParam(':data_capture_status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':approved_by', $_POST["approve_by"], PDO::PARAM_STR);
    $stmt->bindParam(':result_date', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':labrequest_no', $RQ_No, PDO::PARAM_STR);

    $stmt->execute();


    include_once("refresh.php");
    $approve_all = 0;
    $error_sel = 0;
    $sub = approve($approve_all, $error_sel);
}



if ($_POST["MM_update"] == 'stock_inven') {


    $setdate = date('Y-m-d H:i:s');
    //	check if inventor exist before
    $mgt_stock_id = $_POST["mgt_stock_id"];
    $expire_date = $_POST["mgt_expired"];

    $qty_type = $_POST["qty_type"];
    $old_qty = $_POST["mgt_old_qty"];
    $new_qty = $_POST["new_qty"];
    //	$TotalUnits=$_POST["mgt_unit"];
    $stmt = $db->prepare("SELECT bal FROM lab_stocks_inven WHERE stock_sn=:stock_sn ORDER BY sn DESC LIMIT 1");
    $stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
        //$qty=$new_qty * $TotalUnits;
        if ($qty_type == 1) {
            $c_qty = $new_qty + $row_rstSelect['bal'];
            $qtyIN = $new_qty;
            $qtyOUT = 0;
            $cust_patient_type = 'IN';
        } else {
            $c_qty = $row_rstSelect['bal'] - $new_qty;
            $qtyIN = 0;
            $qtyOUT = $new_qty;
            $cust_patient_type = 'OUT';
        }

        $inven_desc = $_POST["desc"] . '' . $_POST["desc2"];
        $captured_date = $setdate;
        $enter_by = $_SESSION['fullname'];
        $cust_patient_id = '';
        $sale_sn = '';
        $return_status = 0;


        $add_stmt = $db->prepare("INSERT INTO lab_stocks_inven(stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, cust_patient_id, cust_patient_type, return_status, enter_by, captured_date) VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date)");
        $add_stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
        $add_stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
        $add_stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
        $add_stmt->bindParam(':batch', $batch, PDO::PARAM_STR);
        $add_stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
        $add_stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
        $add_stmt->bindParam(':bal', $c_qty, PDO::PARAM_INT);
        $add_stmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
        $add_stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
        $add_stmt->bindParam(':return_status', $return_status, PDO::PARAM_STR);
        $add_stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
        $add_stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
        $add_stmt->execute();


        $upd_stmt = $db->prepare("UPDATE lab_stocks SET qty=:qty, expire_date=:expire_date, status=:status WHERE stock_sn=:stock_sn");
        $upd_stmt->bindParam(':qty', $c_qty, PDO::PARAM_INT);
        $upd_stmt->bindParam(':expire_date', $expire_date, PDO::PARAM_STR);
        $upd_stmt->bindParam(':status', 'active', PDO::PARAM_STR); // Assuming 'active' is a fixed value
        $upd_stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);

        $upd_stmt->execute();
    } else {
        // call information


        if ($old_qty > 0) {
            $inven_desc = 'Opening Stock';
            $batch = ' ';
            $qtyIN = $old_qty;
            $qtyOUT = 0;
            $c_qty = $old_qty;
            $captured_date = $setdate;
            $enter_by = $_SESSION['fullname'];
            $cust_patient_id = '';
            $cust_patient_type = 'IN';
            $sale_sn = '';
            $return_status = 0;

            $add_stmt = $db->prepare("INSERT INTO lab_stocks_inven(stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, cust_patient_id, cust_patient_type, return_status, enter_by, captured_date) VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date)");
            $add_stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
            $add_stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
            $add_stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
            $add_stmt->bindParam(':batch', $batch, PDO::PARAM_STR);
            $add_stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
            $add_stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
            $add_stmt->bindParam(':bal', $c_qty, PDO::PARAM_INT);
            $add_stmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
            $add_stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
            $add_stmt->bindParam(':return_status', $return_status, PDO::PARAM_STR);
            $add_stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
            $add_stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
            $add_stmt->execute();


            $upd_stmt = $db->prepare("UPDATE lab_stocks SET qty=:qty, expire_date=:expire_date, status=:status WHERE stock_sn=:stock_sn");
            $upd_stmt->bindParam(':qty', $c_qty, PDO::PARAM_INT);
            $upd_stmt->bindParam(':expire_date', $expire_date, PDO::PARAM_STR);
            $upd_stmt->bindValue(':status', 'active', PDO::PARAM_STR); // Assuming 'active' is a fixed value
            $upd_stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
            $upd_stmt->execute();
        }

        ///
        if ($new_qty > 0) {
            //	$qty=$new_qty * $TotalUnits;
            if ($qty_type == 1) {
                $c_qty = $old_qty + $new_qty;
                $qtyIN = $new_qty;
                $qtyOUT = 0;
                $cust_patient_type = 'IN';
            } else {
                $c_qty = $old_qty - $new_qty;
                $qtyIN = 0;
                $qtyOUT = $new_qty;
                $cust_patient_type = 'OUT';
            }
            //$drug_sn=sanitize($_POST["drug_sn"]);

            $inven_desc = $_POST["desc"] . '' . $_POST["desc2"];
            $captured_date = $setdate;
            $enter_by = $_SESSION['fullname'];
            $cust_patient_id = '-';
            $sale_sn = ' ';
            $return_status = 0;

            // Prepare the SQL statement with placeholders
            $add_stmt = $db->prepare("INSERT INTO lab_stocks_inven(stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, cust_patient_id, cust_patient_type, return_status, enter_by, captured_date) VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date)");

            // Bind parameters
            $add_stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
            $add_stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
            $add_stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
            $add_stmt->bindParam(':batch', $batch, PDO::PARAM_STR);
            $add_stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
            $add_stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
            $add_stmt->bindParam(':bal', $c_qty, PDO::PARAM_INT);
            $add_stmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
            $add_stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
            $add_stmt->bindParam(':return_status', $return_status, PDO::PARAM_STR);
            $add_stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
            $add_stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);

            // Execute the statement
            $add_stmt->execute();


            // Prepare the SQL statement with placeholders
            $upd_stmt = $db->prepare("UPDATE lab_stocks SET qty=:qty, expire_date=:expire_date, status=:status WHERE stock_sn=:stock_sn");

            // Bind parameters
            $upd_stmt->bindParam(':qty', $c_qty, PDO::PARAM_INT);
            $upd_stmt->bindParam(':expire_date', $expire_date, PDO::PARAM_STR);
            $upd_stmt->bindValue(':status', 'active', PDO::PARAM_STR); // Assuming 'active' is a fixed value
            $upd_stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);

            // Execute the statement
            $upd_stmt->execute();
        }
    }
}


if ($_POST["MM_update"] == 'external_patient_add') {

    $setdate = date('Y-m-d');
    $stmt = $db->query("SELECT sn FROM pharm_ext ORDER BY sn DESC LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
        $SN = 1 + $row_rstSelect['sn'];
        $transc_code = 'EX' . $SN;
    } else {
        $SN = 1;
        $transc_code = 'EX' . $SN;
    }

    $sn = substr($transc_code, 2);

    if ($_POST['age'] > 0) {
        $age = $_POST['age'];
        $C_date = date("Y-m-d");
        $datebate = date("Y-m-d H:i:s", strtotime($C_date . " -$age year"));
    } else {

        $datebate = $_POST['dob'];
    }


    $discount_set = 0;
    $referral = $_POST['referral_'];
    $stmt = $db->query("SELECT sn FROM patient_discount where individual_group_no='$referral' and status='On-going'");
    if ($stmt->rowCount() > 0) {
        $discount_set = 1;
    }



    // Prepare the SQL statement with placeholders
    $stmt = $db->prepare("INSERT INTO pharm_ext(sn, transc_code, cust_name, description, gender, dob, phone, address, email_address, referral, date_ap, captured_by,discount_set) VALUES (:sn, :transc_code, :cust_name, :description, :gender, :dob, :phone, :address, :email_address, :referral, :date_ap, :captured_by, :discount_set)");

    // Bind parameters
    $stmt->bindParam(':sn', $sn, PDO::PARAM_STR);
    $stmt->bindParam(':transc_code', $transc_code, PDO::PARAM_STR);
    $stmt->bindParam(':cust_name', $_POST['name'], PDO::PARAM_STR);
    $stmt->bindValue(':description', 'investigation', PDO::PARAM_STR); // Assuming 'investigation' is a fixed value
    $stmt->bindParam(':gender', $_POST['gender'], PDO::PARAM_STR);
    $stmt->bindParam(':dob', $datebate, PDO::PARAM_STR); // Assuming $datebate is already formatted correctly
    $stmt->bindParam(':phone', $_POST['phone'], PDO::PARAM_STR);
    $stmt->bindParam(':address', $_POST['addr'], PDO::PARAM_STR);
    $stmt->bindParam(':email_address', $_POST['email_address'], PDO::PARAM_STR);
    $stmt->bindParam(':referral', $_POST['referral_'], PDO::PARAM_STR);
    $stmt->bindParam(':date_ap', $setdate, PDO::PARAM_STR); // Assuming $setdate is already formatted correctly
    $stmt->bindParam(':captured_by', $_SESSION['fullname'], PDO::PARAM_STR);
    $stmt->bindParam(':discount_set', $discount_set, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
}



if ($_POST["MM_update"] == 'update_ext_patient') {

    /// email_address = :email_address,
    // Prepare the SQL statement with placeholders // email_address = :email_address,

    $discount_set = 0;
    $referral = $_POST['referral_edit'];
    $stmt = $db->query("SELECT sn FROM patient_discount where individual_group_no='$referral' and status='On-going'");
    if ($stmt->rowCount() > 0) {
        $discount_set = 1;
    }



    $stmt = $db->prepare("UPDATE pharm_ext SET cust_name=:cust_name, gender=:gender, dob=:dob, phone=:phone, address=:address, email_address=:email_address, referral=:referral , discount_set=:discount_set WHERE transc_code=:transc_code");

    // Bind parameters
    $stmt->bindParam(':cust_name', $_POST['name_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':gender', $_POST['gender_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':dob', $_POST['dob_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':phone', $_POST['phone_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':address', $_POST['addr_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':email_address', $_POST['email_addr_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':referral', $_POST['referral_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':transc_code', $_POST['transc_code_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':discount_set', $discount_set, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    // Prepare the SQL statement with placeholders
    $stmt = $db->prepare("UPDATE lab_manage SET patient_name=:patient_name, referral=:referral WHERE patient=:patient");

    // Bind parameters
    $stmt->bindParam(':patient_name', $_POST['name_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':referral', $_POST['referral_edit'], PDO::PARAM_STR);
    $stmt->bindParam(':patient', $_POST['transc_code_edit'], PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
}


if ($_POST["MM_update"] == 'edit_profile') {

    $stmt = $db->prepare("UPDATE admin_users SET title=:title, fullname=:fullname, email=:email, phone_number=:phone_number, about_me=:about_me WHERE username=:username");

    // Bind parameters
    $stmt->bindParam(':title', $_POST['title'], PDO::PARAM_STR);
    $stmt->bindParam(':fullname', $_POST['name'], PDO::PARAM_STR);
    $stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $stmt->bindParam(':phone_number', $_POST['phone'], PDO::PARAM_STR);
    $stmt->bindParam(':about_me', $_POST['about_me'], PDO::PARAM_STR);
    $stmt->bindParam(':username', $_POST['username'], PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
}

if ($_POST["MM_update"] == 'take_investigation') {

    $setdate = date('Y-m-d H:i:s');
    // Prepare the SQL statement with placeholders
    $stmt = $db->prepare("UPDATE lab_manage SET collected_by=:collected_by, collected_date=:collected_date, data_capture_status=:data_capture_status WHERE labrequest_no=:labrequest_no");

    // Bind parameters
    $stmt->bindParam(':collected_by', $_SESSION["fullname"], PDO::PARAM_STR);
    $stmt->bindParam(':collected_date', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':data_capture_status', 'capture', PDO::PARAM_STR); // Assuming 'capture' is a string
    $stmt->bindParam(':labrequest_no', $_POST['labrequest_no'], PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();


    if (isset($_POST["credit_status"])) {
        $credit_status = $_POST["credit_status"];
        $paystatus_title = $_POST["paystatus_title"];

        $setdate = date("Y-m-d");
        // Update invoice status and details if paystatus_title is "claim"
        if ($paystatus_title == "claim") {
            $stmt2 = $db->prepare("UPDATE patient_ap_services 
                           SET invoice_status=:invoice_status, 
                               invoice_no=:invoice_no, 
                               dsp_by=:dsp_by, 
                               transact_date=:transact_date, 
                               paystatus=:paystatus 
                           WHERE drug_sn=:drug_sn");

            $stmt2->bindParam(':invoice_status', '1', PDO::PARAM_STR);
            $stmt2->bindParam(':invoice_no', $_POST['invoice_no'], PDO::PARAM_STR);
            $stmt2->bindParam(':dsp_by', $_SESSION["fullname"], PDO::PARAM_STR);
            $stmt2->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
            $stmt2->bindParam(':paystatus', '1', PDO::PARAM_STR);
            $stmt2->bindParam(':drug_sn', $_POST['labrequest_no'], PDO::PARAM_STR);

            $stmt2->execute();
        }

        // Update credit status if $credit_status is 1
        if ($credit_status == 1) {
            $stmt2 = $db->prepare("UPDATE patient_ap_services 
                           SET cr=:cr 
                           WHERE drug_sn=:drug_sn");

            $stmt2->bindParam(':cr', '1', PDO::PARAM_STR);
            $stmt2->bindParam(':drug_sn', $_POST['labrequest_no'], PDO::PARAM_STR);

            $stmt2->execute();
        }
    }
    include_once("refresh.php");
    $sub = queue();
}



if ($_POST["MM_update"] == 'add_machine') {


    $s_count = $_POST['service_after_no'];
    $s_type = $_POST['service_after_type'];
    $e_date = $_POST['effective_date'];

    //if($s_type=='Days'){
    //	$N_date = date('Y-m-d', strtotime($e_date. ' + 2 days'));
    $N_date = date("Y-m-d", strtotime($e_date . " +$s_count day"));
    //	}else{
    //	$N_date=' ';
    //	}

    //header("location:m.php");
    // Check if device_name exists in invsti_machine table
    $stmt = $db->prepare("SELECT * FROM invsti_machine WHERE device_name = :device_name");
    $stmt->bindParam(':device_name', $_POST['device_name'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Prepare INSERT statement
        $insertStmt = $db->prepare("INSERT INTO invsti_machine (
                                    device_name, label, serial, manufacturer, manufacturer_contact_person, 
                                    date_manufacture, date_purchase, date_put_service, service_provider, 
                                    service_provider_contact, location_equipment, service_every_count, 
                                    service_every_type, effective_date, next_due_date
                                ) VALUES (
                                    :device_name, :label, :serial, :manufacturer, :manufacturer_details, 
                                    :date_manufacture, :date_purchase, :date_put_service, :service_provider, 
                                    :service_provider_contact, :location_equip, :service_after_no, 
                                    :service_after_type, :effective_date, :next_due_date
                                )");

        // Bind parameters
        $insertStmt->bindParam(':device_name', $_POST['device_name'], PDO::PARAM_STR);
        $insertStmt->bindParam(':label', $_POST['label'], PDO::PARAM_STR);
        $insertStmt->bindParam(':serial', $_POST['serial'], PDO::PARAM_STR);
        $insertStmt->bindParam(':manufacturer', $_POST['manufacturer'], PDO::PARAM_STR);
        $insertStmt->bindParam(':manufacturer_details', $_POST['manufacturer_details'], PDO::PARAM_STR);
        $insertStmt->bindParam(':date_manufacture', $_POST['date_manufacture'], PDO::PARAM_STR);
        $insertStmt->bindParam(':date_purchase', $_POST['date_purchase'], PDO::PARAM_STR);
        $insertStmt->bindParam(':date_put_service', $_POST['date_put_service'], PDO::PARAM_STR);
        $insertStmt->bindParam(':service_provider', $_POST['service_provider'], PDO::PARAM_STR);
        $insertStmt->bindParam(':service_provider_contact', $_POST['service_provider_contact'], PDO::PARAM_STR);
        $insertStmt->bindParam(':location_equip', $_POST['location_equip'], PDO::PARAM_STR);
        $insertStmt->bindParam(':service_after_no', $_POST['service_after_no'], PDO::PARAM_INT);
        $insertStmt->bindParam(':service_after_type', $_POST['service_after_type'], PDO::PARAM_STR);
        $insertStmt->bindParam(':effective_date', $_POST['effective_date'], PDO::PARAM_STR);
        $insertStmt->bindParam(':next_due_date', $N_date, PDO::PARAM_STR);

        // Execute the INSERT statement
        $insertStmt->execute();
    }
}


if ($_POST["MM_update"] == 'add_device_invest') {

    // Check if the combination of investigation_name and device_no exists in invsti_machine_settings table
    $stmt = $db->prepare("SELECT * FROM invsti_machine_settings WHERE investigation_name = :invest_name AND device_no = :device_no");
    $stmt->bindParam(':invest_name', $_POST['invest_name'], PDO::PARAM_STR);
    $stmt->bindParam(':device_no', $_POST['device_no'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Prepare INSERT statement
        $insertStmt = $db->prepare("INSERT INTO invsti_machine_settings (device_no, investigation_name) VALUES (:device_no, :invest_name)");

        // Bind parameters
        $insertStmt->bindParam(':device_no', $_POST['device_no'], PDO::PARAM_STR);
        $insertStmt->bindParam(':invest_name', $_POST['invest_name'], PDO::PARAM_STR);

        // Execute the INSERT statement
        $insertStmt->execute();
    }
}


if ($_POST["MM_update"] == 'routine_add') {

    $setdate = date("Y-m-d");
    $s_type = $_POST['s_type'];
    $s_count = $_POST['s_count'];

    $N_date = date("Y-m-d", strtotime($setdate . " +$s_count day"));

    //header("location:main.php");
    // Check if the device_no and maintenance_date combination exists in invsti_machine_logs table
    $stmt = $db->prepare("SELECT * FROM invsti_machine_logs WHERE device_no = :device_no AND maintenance_date = :maintenance_date");
    $stmt->bindParam(':device_no', $_POST['device_no'], PDO::PARAM_STR);
    $stmt->bindParam(':maintenance_date', $setdate, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Prepare INSERT statement for invsti_machine_logs table
        $insertStmt = $db->prepare("INSERT INTO invsti_machine_logs (device_no, total_invsti, maintenance_date, Description_maintenance, Maintenance_performed_by, supervise_by, remarks, next_due_date) 
                                VALUES (:device_no, :total_invsti, :maintenance_date, :description_maintenance, :maintenance_performed_by, :supervise_by, :remarks, :next_due_date)");

        // Bind parameters for INSERT statement
        $insertStmt->bindParam(':device_no', $_POST['device_no'], PDO::PARAM_STR);
        $insertStmt->bindParam(':total_invsti', $_POST['investi_count'], PDO::PARAM_STR);
        $insertStmt->bindParam(':maintenance_date', $setdate, PDO::PARAM_STR);
        $insertStmt->bindParam(':description_maintenance', $_POST['Description_maintenance'], PDO::PARAM_STR);
        $insertStmt->bindParam(':maintenance_performed_by', $_POST['Maintenance_performed'], PDO::PARAM_STR);
        $insertStmt->bindParam(':supervise_by', $_SESSION['fullname'], PDO::PARAM_STR);
        $insertStmt->bindParam(':remarks', $_POST['remarks'], PDO::PARAM_STR);
        $insertStmt->bindParam(':next_due_date', $N_date, PDO::PARAM_STR);

        // Execute the INSERT statement
        $insertStmt->execute();

        // Prepare UPDATE statement for invsti_machine table
        $updateStmt = $db->prepare("UPDATE invsti_machine 
                                SET total_invsti = :total_invsti, next_due_date = :next_due_date, effective_date = :effective_date, investigation_count = :investigation_count, due_status = :due_status 
                                WHERE sn = :device_no");

        // Bind parameters for UPDATE statement
        $updateStmt->bindParam(':total_invsti', $_POST['total_invsti'], PDO::PARAM_STR);
        $updateStmt->bindParam(':next_due_date', $N_date, PDO::PARAM_STR);
        $updateStmt->bindParam(':effective_date', $setdate, PDO::PARAM_STR);
        $updateStmt->bindValue(':investigation_count', '0', PDO::PARAM_STR); // Assuming this value is set as '0' based on your original SQL
        $updateStmt->bindValue(':due_status', '0', PDO::PARAM_STR); // Assuming this value is set as '0' based on your original SQL
        $updateStmt->bindParam(':device_no', $_POST['device_no'], PDO::PARAM_STR);

        // Execute the UPDATE statement
        $updateStmt->execute();
    }
}


if ($_POST["MM_update"] == 'what_to_do') {

    //    note_date username
    $note_time = $_POST['note_time'];
    $note_date = $_POST['note_date'];

    $new_date_time = date("Y-m-d H:i:s", strtotime($note_date . ' ' . $note_time));
    $stmt = $db->prepare("INSERT INTO admin_users_what_todo (note, date_time, username) VALUES (:note, :date_time, :username)");

    // Bind parameters to the prepared statement
    $stmt->bindParam(':note', $_POST['note'], PDO::PARAM_STR);
    $stmt->bindParam(':date_time', $new_date_time, PDO::PARAM_STR);
    $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
}



if ($_POST["MM_update"] == 'add_discount_insert') {

    $setdate = date("Y-m-d");

    if ($_POST['service_type'] == 'All Services') {
        $status = 'On-going';
    } else {
        $status = 'Pending';
    }

    $stmt = $db->prepare("INSERT INTO patient_discount (individual_group, individual_group_no, individual_group_name, discount_charge, percentage_flat, percentage_flat_value, duration, specify_count, apply_to_services, services_items, count_bal, setby, history, status_date, status) 
                      VALUES (:individual_group, :individual_group_no, :individual_group_name, :discount_charge, :percentage_flat, :percentage_flat_value, :duration, :specify_count, :apply_to_services, :services_items, :count_bal, :setby, :history, :status_date, :status)");

    // Bind parameters to the prepared statement
    $stmt->bindParam(':individual_group', 'individual', PDO::PARAM_STR);
    $stmt->bindParam(':individual_group_no', $_POST['emr'], PDO::PARAM_STR);
    $stmt->bindParam(':individual_group_name', $_POST['patient_name'], PDO::PARAM_STR);
    $stmt->bindParam(':discount_charge', $_POST['discount_charge'], PDO::PARAM_STR);
    $stmt->bindParam(':percentage_flat', $_POST['mode'], PDO::PARAM_STR);
    $stmt->bindParam(':percentage_flat_value', $_POST['mode_value'], PDO::PARAM_STR);
    $stmt->bindParam(':duration', $_POST['how_long'], PDO::PARAM_STR);
    $stmt->bindParam(':specify_count', $_POST['specify_count'], PDO::PARAM_STR);
    $stmt->bindParam(':apply_to_services', $_POST['service_type'], PDO::PARAM_STR);
    $stmt->bindValue(':services_items', '', PDO::PARAM_STR); // Assuming services_items is a string and defaulted to empty string
    $stmt->bindParam(':count_bal', $_POST['specify_count'], PDO::PARAM_STR); // Assuming count_bal is the same as specify_count
    $stmt->bindParam(':setby', $_SESSION['fullname'], PDO::PARAM_STR);
    $stmt->bindParam(':history', $history, PDO::PARAM_STR);
    $stmt->bindParam(':status_date', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
}
