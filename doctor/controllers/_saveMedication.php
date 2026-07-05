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
        "drug" => $_POST['drug'],
        'action' => $_POST['action']
    ]
));


if (empty($request->appointment_number)) {
    $request->appointment_number = $request->hospital_no;
}

if (isset($request->drug) && isset($request->appointment_number)) {

    if ($request->action == 'add') {

        try {

            $save = false;

            // Fetch patient info
            $patient_info = $Patient->getByHospitalNo($request->hospital_no);
            if (empty($patient_info)) {
                throw new Exception("Invalid Patient No.");
            }

            // Extract patient info
            $insurance_name = $patient_info->insurance_name;
            $interest = $patient_info->interest;
            $insurance_type = $patient_info->insurance_type;
            $services_access = $patient_info->services_access;
            $insurance_no = $patient_info->insurance_no;
            $payment_mode = $patient_info->payment_mode;
            $add_minus = $patient_info->add_minus;

            $drug = $request->drug;
            $serv_group = "Pharmacy";
            $dept_id = $_SESSION['dept_id'];
            $dispensory_status = $_SESSION['main_or_dispensory_requisition'];

            // Get dispensory department
            if ($_SESSION['dispensory'] == 1) {
                $dept_dispensory_id = $dept_id;
            } else {
                $dept_stmt = $db->prepare("SELECT sn FROM department WHERE department = 'Pharmacy' LIMIT 1");
                if (!$dept_stmt->execute()) {
                    throw new Exception("Unable to fetch department.");
                }

                $department_info = $dept_stmt->fetch();
                if (!$department_info) {
                    throw new Exception("Pharmacy department not found.");
                }

                $dept_dispensory_id = $department_info['sn'];
            }

            // Pricing and medication validations
            $cat_type = $drug->category ?: "Pharmacy";
            $drug_sn = $drug->sn;
            $item_services = $drug->product_name;
            $hosp_price = $drug->hosp_price;
            $ext_price = $drug->cash_price ?: $hosp_price;
            $nhis_price = $drug->nhis_price;

            $qty = ($drug->qty > 0) ? $drug->qty : 1;

            // Validate dosage/duration
            $drug->med_dosage = max(1, intval($drug->med_dosage));
            $drug->med_duration = max(1, intval($drug->med_duration));

            // Calculate pricing
            $target_sn = $drug_sn;
            $_tariff_table = "hmo_stocks_tariff";

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
                $services_access,
                $add_minus,
                $payment_mode,
                $_tariff_table,
                $target_sn,
                1
            );

            if (!$amount_invoice) {
                throw new Exception("Failed to calculate invoice amount.");
            }

            // Check stock balance (MLUTH dispensory mode)
            if ($dispensory_status == 1) {
                $stmt = $db->prepare("SELECT bal FROM stock_table_inven 
                                  WHERE stock_sn = :stock_sn 
                                  AND cust_patient_id = :dept_id 
                                  ORDER BY sn DESC LIMIT 1");

                $stmt->execute([
                    ':stock_sn' => $drug_sn,
                    ':dept_id' => $dept_id
                ]);

                $r = $stmt->fetch(PDO::FETCH_ASSOC);
                $dispensory_status = ($r && $r['bal'] > 0) ? 1 : 0;
            }

            // Check existing draft request
            $PatientApService_info = $PatientApService->get([
                'date_entry' => date('Y-m-d') . ' 00:00:00',
                'hospital_no' => $request->hospital_no,
                'invoice_status' => 0,
                'paystatus' => '0',
                'drug_sn' => $drug_sn
            ]);

            if (!empty($PatientApService_info)) {
                throw new Exception("Sorry, you have a pending medication request.");
            }

            // Multiply total by quantity
            $amount_paying = $amount_invoice["amount_paying"] * $qty;
            $claim_amt = $amount_invoice["claim_amt"] * $qty;

            // Save medication
            $save = save_patient_ap_service_medication(
                $db,
                $request->appointment_number,
                $request->hospital_no,
                $services_access,
                $serv_group,
                $cat_type,
                $dept_id,
                $dept_dispensory_id,
                $dispensory_status,
                $drug_sn,
                $item_services,
                $amount_invoice["item_amt"],
                $claim_amt,
                $amount_invoice["ccop_int_charge"],
                "",
                $amount_invoice["invoice_no"],
                $_SESSION['fullname'],
                $amount_paying,
                $amount_invoice["pay_mode"],
                $qty,
                $drug->med_frequency,
                $drug->med_dosage,
                $drug->med_dosage_unit,
                $drug->med_duration,
                $drug->med_duration_unit,
                $drug->remarks,
                true,
                0,
                $drug->med_remark
            );

            if (!$save) {
                throw new Exception("Failed to save medication request.");
            }

            // Lock appointment
            lockApppointment($db, $request->appointment_number);


/*             if ($save && isset($_SESSION['notify_pharm']) && $_SESSION['notify_pharm'] == 1) {
                $hospital_no = $request->hospital_no;
                $view_link = '<a href="index.php?presc&hos_no=' . $hospital_no . '">View Patient</a>';
                $message_ = 'New medication(s) have been added for patient ' . $hospital_no . ', for Invoice. ' . $view_link;

                // Notify Pharmacy
                //function global_notify_($db, $type, $pattern, $title, $message, $sender = null)
                global_notify_($db, 'notify', 'PH', '<b>New Medication</b>', $message_);

                // Check admission and notify Nurses if patient is admitted
                $stmt = $db->prepare("SELECT 1 FROM admission WHERE hospital_no = ? AND adm_status = 3 LIMIT 1");
                $stmt->execute(array($hospital_no));

                if ($stmt->fetchColumn()) {
                    // Reuse the same message but correct the hos_no param if needed
                    $view_link_ns = '<a href="index.php?presc&hosp_no=' . $hospital_no . '">View Patient</a>';
                    $message_ns = 'New medication(s) have been added for patient ' . $hospital_no . ', for Invoice. ' . $view_link_ns;

                    global_notify_($db, 'rights', 'NS', '<b>New Medication for Patient On-Admission</b>', $message_ns);
                }
            } */

            echo json_encode([
                "status" => 200,
                "message" => "Request posted"
            ]);
        } catch (Exception $e) {

            // You catch ALL errors here
            echo json_encode([
                "status" => 500,
                "message" => $e->getMessage()
            ]);
        }

        exit;
    }


    if ($request->action == 'remove') {
        $drug = $request->drug;
        $mesage = 'Cannot remove, Already Paid';
        $status = 401;

        $PatientApService_ = $PatientApService->get(
            [
                'sn' => $drug->ap_services_id,
                'drug_sn' => $drug->sn,
                'serv_group' => 'Pharmacy',
                'app_no' => $request->appointment_number,
                'hospital_no' => $request->hospital_no
            ]
        );



        if ($PatientApService_->created_by == $_SESSION["id"]) {
            if (!empty($PatientApService_)) {
                /// not paid for the service
                if ($PatientApService_->invoice_status == 0) {
                    $delete = $PatientApService->delete(
                        [

                            'app_no' => $request->appointment_number,
                            'sn' => $drug->ap_services_id,
                            'drug_sn' => $drug->sn,
                            'serv_group' => 'Pharmacy',
                            'hospital_no' => $request->hospital_no,
                            'invoice_status' => 0
                        ]
                    );

                    $mesage = $delete ? "Removed" : "Action Failed";
                    $status = $delete ? 200 : 401;
                } else {
                    $mesage =  "Soryy, Drug has already been invoiced";
                }
            }
        } else {
            $mesage =  "Soryy, You cannot remove this drug";
        }

        echo json_encode(["status" => $status, "message" => $mesage]);
        exit;
    }

    if ($request->action == 'editPrescription') {
        $sn = intval($_POST['sn']);

        $remarks = $_POST['remarks'];
        $mesage = 'Cannot Change Prescription';

        $PatientApService_ = $PatientApService->find($sn);

        if (!empty($PatientApService_)) {
            if ($PatientApService_->created_by == $_SESSION["id"]) {
                /// not paid for the service
                $stmt = $db->prepare("UPDATE patient_ap_services SET remarks = ? WHERE sn = ? ");
                $stmt->execute([$remarks, $sn]);

                $mesage =  "Saved";
            } else {
                $mesage =  "Sorry, You cannot Edit this Prescription";
            }
        } else {
            $mesage =  "Sorry, You cannot Edit this Prescription";
        }

        echo json_encode(["status" => 200, "message" => $mesage]);
        exit;
    }
}
