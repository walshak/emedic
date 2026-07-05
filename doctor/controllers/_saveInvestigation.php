<?php

session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');

$request = json_decode(json_encode(
    [
        "appointment_number" => $_POST['appointment_number'],
        "hospital_no" => $_POST['hospital_no'],
        "investigation" => $_POST['investigation'],
        'action' => $_POST['action']
    ]
));


if (isset($request->investigation) && isset($request->appointment_number)) {

    if ($request->action == 'add' and $_SESSION['fullname'] != '') {
        try {
            $db->beginTransaction();
            $save = false;

            if (empty($request->appointment_number)) {
                $request->appointment_number = $request->hospital_no;
            }

            $patient_info = $Patient->getByHospitalNo($request->hospital_no);
            $ap_type = 0;
            if (!empty($patient_info)) {
                $patient_name =  $patient_info->surname . ' ' . $patient_info->fname . ' ' . $patient_info->oname;
                $insurance_name = $patient_info->insurance_name;
                $interest = $patient_info->interest;
                $insurance_type = $patient_info->insurance_type;
                $services_access = $patient_info->services_access;
                $insurance_no = $patient_info->insurance_no;
                $payment_mode = $patient_info->payment_mode;
                $add_minus = $patient_info->add_minus;
            } else {
                echo json_encode(["status" => 404, "message" => "Invalid Patient Number"]);
                exit;
            }

            //// Investigation
            $investigation = $request->investigation;

            $category = $investigation->category;
            $sub_category = $investigation->sub_category;

            if ($category == 'Laboratory') {
                $code = "LB";
                $cat_type = ($sub_category != '') ? $sub_category : "Laboratory Test";
            } else {
                $code = "RD";
                $cat_type = ($sub_category != '') ? $sub_category : "Scan/Imaging";
            }



            for ($i = 1; $i <= intval($investigation->quantity); $i++) {
                $setdate2 = date("Y-m-d");
                $datetime = date("Y-m-d H:i:s");
                $lab_reqno = generateRequestNo($db, $code, $request->appointment_number, $request->hospital_no, $investigation->combo_test);
                $lab_cats = ["Laboratory" => 1, "Radiology" => 3];
                $data_capture_status = 'queue';
                $setdate2 = date("Y-m-d");
                $testname = $investigation->test;

                $hosp_price = $investigation->hosp_price;
                $ext_price = $investigation->ext_price;
                $nhis_price = $investigation->nhis_price;
                $item_sn = $investigation->sn;
                $dept_id = $investigation->dept;
                $serv_group = $investigation->category;

                $target_sn = $item_sn;
                $NHIS_DRUG_CONSUMBL_STATE = 0;
                $_tariff_table = "hmo_investigation_tariff";

                $amount_invoice = new_service_amount_cal(
                    $db,
                    $request->hospital_no,
                    $request->appointment_number,
                    $interest,
                    $insurance_type,
                    $insurance_no,
                    $hosp_price,
                    $ext_price,
                    $nhis_price,
                    $service_access,
                    $add_minus,
                    $payment_mode,
                    $_tariff_table,
                    $target_sn,
                    $NHIS_DRUG_CONSUMBL_STATE
                );

                $claim_amt = $amount_invoice["claim_amt"];
                $amount_paying = $amount_invoice["amount_paying"];
                $pay_mode = $amount_invoice["pay_mode"];
                $ccop_int_charge = $amount_invoice["ccop_int_charge"];

                if ($investigation->quantity > 1) {
                    $testname = $investigation->test . '(' . $i . ')';
                }

                $data = json_decode(json_encode([
                    'app_no' => $request->appointment_number,
                    'labrequest_no' => $lab_reqno,
                    'patient' => $request->hospital_no,
                    'patient_name' => $patient_name,
                    'test_id' => $investigation->sn,
                    'test_name' => $testname,
                    'lab_cat' => $investigation->dept,
                    'section' => $investigation->category,
                    'group_id' => $request->appointment_number,
                    'preferred_specimen' => $investigation->specimen,
                    'request_note' => $investigation->request_note,
                    'request_date' => $datetime,
                    'request_date2' => $setdate2,
                    'request_by' => $_SESSION['fullname'],
                    'lab_combos' => $investigation->combo_test,
                    'created_by' => $_SESSION['id'],
                    'doctor_result_status' => $investigation->doctor_result_status,
                    'amount_paying' => $amount_paying
                ]));


                $investigationSaved = $Investigation->save($data);

                if (!$investigationSaved) {
                    $db->rollback();
                    echo json_encode(["status" => 500, "message" => "An error occurred while saving the investigation data."]);
                    exit;
                } else {

                    $drug_sn = $lab_reqno;
                    $item_services = $testname;

                    $save = save_patient_ap_service(
                        $db,
                        $request->appointment_number,
                        $request->hospital_no,
                        $services_access,
                        $serv_group,
                        $cat_type,
                        $dept_id,
                        $drug_sn,
                        $testname,
                        $amount_invoice["item_amt"],
                        $claim_amt,
                        $ccop_int_charge,
                        "",
                        $amount_invoice["invoice_no"],
                        $_SESSION['fullname'],
                        $amount_paying,
                        $pay_mode,
                        " ",
                        " ",
                        " ",
                        " ",
                        " ",
                        " ",
                        true
                    );

                    if (!$save) {
                        $db->rollback();
                        echo json_encode(["status" => 500, "message" => "An error occurred while saving the data."]);
                        exit;
                    }
                }
            }

            lockApppointment($db, $request->appointment_number);
            echo json_encode(["status" => 200, "message" => $save]);
            $db->commit();



            /*             if ($save && isset($_SESSION['notify_lab']) && $_SESSION['notify_lab'] == 1) {
                // Check admission and notify Nurses if patient is admitted
                $stmt = $db->prepare("SELECT 1 FROM admission WHERE hospital_no = ? AND adm_status = 3 LIMIT 1");
                $stmt->execute(array($request->hospital_no));

                if ($stmt->fetchColumn()) {
                    // Reuse the same message but correct the hos_no param if needed
                    $view_link_ns = '<a href="mgt.php?hosp_no=' .  $request->hospital_no . '">View Patient</a>';
                    $message_ns = 'New Investigation Request for this Patient on Admission ' .  $request->hospital_no . ' ' . $view_link_ns;

                    global_notify_($db, 'rights2', $code, '<b>New Investigation for this Patient On-Admission</b>', $message_ns);
                } else {
                    $message_ = 'New Investigation(s) have been added for patient ' . $request->hospital_no .
                        '. <a href="mgt.php?hosp_no=' . $request->hospital_no . '">View Patient</a>';
                    global_notify_($db, 'notify', $code, '<b>New Investigation</b>', $message_);
                }
            } */

            exit;
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode([
                "status" => 500,
                "message" => "Error: " . $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine()
            ]);
            exit;
        }
    }


    ///////////////////////// REMOVE ACTION ////////////////////////////////////////////
    if ($request->action == 'remove') {
        $investigation = $request->investigation;

        $checkLabManagestmt = $db->prepare("SELECT * FROM  lab_manage 
          WHERE app_no = ? AND patient = ? AND test_id = ? AND data_capture_status = 'queue' AND created_by = ? 
            ORDER BY sn DESC   ");

        $checkLabManagestmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                $investigation->sn,
                $_SESSION["id"]
            )
        );
        if ($checkLabManagestmt->rowCount() > 0) {
            $row = $checkLabManagestmt->fetch(PDO::FETCH_ASSOC);
            $labrequest_no = $row['labrequest_no'];
            $test_name = $row['test_name'];
            $patient_name = $row['patient_name'];
            $group_id = $row['group_id'];
            $patient = $row['patient'];
            $rmks = 'Hosp/No.' . $patient . '/ ' . $test_name . '/GrouupID: ' . $group_id . '/Date: ' . $row['request_date'];

            $stmtCheck = $db->prepare("SELECT sn FROM patient_ap_services 
                WHERE paystatus = 0 AND app_no = ? AND hospital_no = ? AND drug_sn = ? AND created_by = ? ORDER BY sn DESC ");
            $stmtCheck->execute([$request->appointment_number, $request->hospital_no, $labrequest_no, $_SESSION["id"]]);

            if ($stmtCheck->rowCount() >= 1) {

                try {
                    // Begin transaction
                    $db->beginTransaction();

                    // Prepare and execute the first DELETE statement
                    $deleted = $db->prepare("DELETE FROM patient_ap_services WHERE drug_sn = :labrequest_no AND paystatus = 0");
                    $deleted->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
                    $deleted->execute();
                    $deletedCount = $deleted->rowCount();

                    if ($deletedCount > 0) {
                        // Prepare and execute the second DELETE statement
                        $deleted2 = $db->prepare("DELETE FROM lab_manage WHERE labrequest_no = :labrequest_no");
                        $deleted2->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
                        $deleted2->execute();
                        $deletedCount2 = $deleted2->rowCount();

                        if ($deletedCount2 > 0) {
                            // Log the action if both deletes are successful
                            $staff = $_SESSION['fullname'];
                            $pid = $patient;
                            $item_sn = $labrequest_no;
                            $pname = $patient_name;
                            $desc = $rmks;
                            $action = 'Request Delete';
                            include("../../logs.php");
                            $db->commit();

                            echo json_encode(["status" => 200, "message" => 'Removed...']);
                            exit;
                        } else {
                            $db->rollBack();
                            echo json_encode(["status" => 200, "message" => "Failed..."]);
                            exit;
                        }
                    } else {
                        $db->rollBack();
                        echo json_encode(["status" => 200, "message" => "Failed..."]);
                        exit;
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(["status" => 500, "message" => "An error occurred: " . $e->getMessage()]);
                    exit;
                }
            } else {

                echo json_encode(["status" => 401, "message" => 'Access Denied: You cannot remove this Investigation']);
                exit;
            }
        } else {
            echo json_encode(["status" => 401, "message" => 'Access Denied: Specimen taken']);
            exit;
        }
    }
}
