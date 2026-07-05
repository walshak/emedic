<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');



if (isset($_POST["drug_search"])) {

    $hospital_no = $_POST["hospital_no"];
    $stmt = $db->prepare("SELECT DISTINCT item_services name  FROM patient_ap_services WHERE hospital_no = ? AND drug_status = 1 ");
    $stmt->execute([$hospital_no]);
    if ($stmt->rowCount() == 0) {
        $list = ["Not Given"];
    } else {
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    echo json_encode($list);
    exit;
}

if (isset($_POST["drug_stocks"])) {
    //  $drug_stocks = $DrugStock->all();
    $sql = "SELECT product_name name, sn id FROM stock_table WHERE status = 'active' AND hosp_price > 0 ";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $drug_stocks = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));

    $prescription_list_stmt = $db->prepare("SELECT DISTINCT remarks name FROM patient_ap_services WHERE remarks IS NOT NULL AND remarks !='' ");
    $prescription_list_stmt->execute();
    $prescription_list = $prescription_list_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => 200, "data" => $drug_stocks, 'prescription_list' => $prescription_list, 'message' => 'Loaded']);
    exit;
}



if (isset($_POST["loadLabAndRad"])) {
    echo json_encode([
        "status" => 200,
        'labtests' => $Investigation->get(['category' => 'Laboratory'], true),
        'radiologies' => $Investigation->get(['category' => 'Radiology'], true),
        'message' => 'Loaded'
    ]);
    exit;
}


if (isset($_POST["patient_appointment_medications"])) {
    $message = '';

    $hospital_no = $_POST["hospital_no"];
    $appointment_number = null;
    $SQL_STRING = " ";
    if (isset($_POST["appointment_number"])) {
        if (!empty($_POST["appointment_number"])) {
            $appointment_number = $_POST["appointment_number"];

            $SQL_STRING = " AND app_no = '$appointment_number' ";
        }
    }


    //// SELECTED drugs for a given appointment
    $selected_drugs_stmt = $db->prepare("SELECT sn,drug_sn,pay ,created_by, prepared_by,item_services,pay_mode, claim_amt, med_frequency, med_dosage, med_dosage_unit, med_duration, med_duration_unit,prescription,remarks,invoice_status   
    FROM patient_ap_services WHERE hospital_no = ? $SQL_STRING  and serv_group = 'Pharmacy' AND drug_status = 0 ORDER BY sn DESC ");
    $selected_drugs_stmt->execute(array($hospital_no));
    $selected_drugs_rows = $selected_drugs_stmt->fetchAll(PDO::FETCH_ASSOC);
    $selected_drug_stocks = [];
    foreach ($selected_drugs_rows as $key => $selected_drugs_row) {

        $drug_stmt_row = $DrugStock->find($selected_drugs_row["drug_sn"]);

        if ($selected_drugs_row["pay_mode"] == 'claim') {
            $drug_stmt_row->cash_price = $selected_drugs_row['claim_amt'];
        } else {
            $drug_stmt_row->cash_price = $selected_drugs_row["pay"];
        }

        $drug_stmt_row->ap_services_id = $selected_drugs_row["sn"];
        $drug_stmt_row->med_dosage = $selected_drugs_row["med_dosage"];
        $drug_stmt_row->med_dosage_unit = $selected_drugs_row["med_dosage_unit"];
        $drug_stmt_row->med_frequency = $selected_drugs_row["med_frequency"];
        $drug_stmt_row->med_duration = $selected_drugs_row["med_duration"];
        $drug_stmt_row->med_duration_unit = $selected_drugs_row["med_duration_unit"];
        $drug_stmt_row->prescription = $selected_drugs_row["prescription"];
        $drug_stmt_row->remarks = $selected_drugs_row["remarks"];
        $drug_stmt_row->invoice_status = $selected_drugs_row["invoice_status"];
        $drug_stmt_row->created_by = $selected_drugs_row["created_by"];
        $drug_stmt_row->prepared_by = $selected_drugs_row["prepared_by"];

        if ($selected_drugs_row['created_by'] != $_SESSION['id']) {
            $drug_stmt_row->invoice_status = 1;
        }

        array_push($selected_drug_stocks, $drug_stmt_row);
    }


    echo json_encode(["status" => 200, "data" => $selected_drug_stocks, 'message' => $message]);
    exit;
}





if (isset($_POST["represcribe"])) {
    $message = 'Failed';
    $status = 401;
    $count = 0;

    $selected = $_POST["selected"];
    $appointment_number = $_POST["appointment_number"];
    $hospital_no = $_POST["hospital_no"];
    foreach ($selected as $key => $id) {
        $app_service =  $PatientApService->find($id);
        if (!empty($app_service)) {
            try {

                $patient_info = $Patient->getByHospitalNo($hospital_no);
                $ap_type = 0;
                if (!empty($patient_info)) {
                    $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
                    $insurance_name = $patient_info->insurance_name;
                    $interest = $patient_info->interest;
                    $insurance_type = $patient_info->insurance_type;
                    $services_access = $patient_info->services_access;
                    $insurance_no = $patient_info->insurance_no;
                    $payment_mode = $patient_info->payment_mode;
                    $add_minus = $patient_info->add_minus;

                    $drug_stmt_row = $DrugStock->find($app_service->drug_sn);

                    $target_sn = $app_service->drug_sn;
                    $NHIS_DRUG_CONSUMBL_STATE = 1;
                    $_tariff_table = "hmo_stocks_tariff	";
                    $amount_invoice = new_service_amount_cal(
                        $db,
                        $hospital_no,
                        $appointment_number,
                        $interest,
                        $insurance_type,
                        $insurance_no,
                        $drug_stmt_row->hosp_price,
                        $drug_stmt_row->cash_price,
                        $drug_stmt_row->nhis_price,
                        $services_access,
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
                    $hosp_price = $amount_invoice["item_amt"];




                    $stmt = $db->prepare("INSERT INTO patient_ap_services (app_no,hospital_no,access,serv_group,cat_type,dept_id,drug_sn,item_services,hosp_price,
                claim_amt,interest,qty,remarks,
          drug_status,invoice_status,invoice_no,prepared_by,date_entry,transact_date,pay,pay_mode,paystatus,process_claim, med_frequency, med_dosage, med_dosage_unit, 
          med_duration, med_duration_unit, prescription,created_by) 
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?, ?, ?, ?, ?,?, ?, ?) ");

                    $date = date('Y-m-d');
                    $save = $stmt->execute(
                        array(
                            $appointment_number,
                            $app_service->hospital_no,
                            $services_access,
                            $app_service->serv_group,
                            $app_service->cat_type,
                            $app_service->dept_id,
                            $app_service->drug_sn,
                            $app_service->item_services,
                            $drug_stmt_row->hosp_price,
                            $amount_invoice["claim_amt"],
                            $amount_invoice["ccop_int_charge"],
                            1,
                            $app_service->remarks,
                            '0',
                            "",
                            $amount_invoice["invoice_no"],
                            $_SESSION['fullname'],
                            date('Y-m-d'),
                            NULL,
                            $amount_invoice["amount_paying"],
                            $amount_invoice["pay_mode"],
                            '0',
                            '0',
                            $app_service->med_frequency,
                            $app_service->med_dosage,
                            $app_service->med_dosage_unit,
                            $app_service->med_duration,
                            $app_service->med_duration_unit,
                            $app_service->remarks,
                            $_SESSION['id']
                        )
                    );
                    if ($save === true) {
                        $count++;
                    }
                }
            } catch (Exception $e) {
                // var_dump($e);
            }
        }
    }

    $selected_drug_stocks = [];
    if ($count > 0) {
        $message = 'Re-prescribed successfully...';
        $status = 200;

        //// SELECTED drugs for a given appointment
        //// SELECTED drugs for a given appointment


        $SQL_STRING = " ";
        if (isset($_POST["appointment_number"])) {
            if (!empty($_POST["appointment_number"])) {
                $appointment_number = $_POST["appointment_number"];

                $SQL_STRING = " AND app_no = '$appointment_number' ";
            }
        }
        $selected_drugs_stmt = $db->prepare("SELECT sn,drug_sn,pay ,created_by, prepared_by,item_services,pay_mode, claim_amt, med_frequency, med_dosage, med_dosage_unit, med_duration, med_duration_unit,prescription,remarks,invoice_status   
   FROM patient_ap_services WHERE hospital_no = ? $SQL_STRING  and serv_group = 'Pharmacy' AND drug_status = 0 ORDER BY sn DESC ");
        $selected_drugs_stmt->execute(array($hospital_no));
        $selected_drugs_rows = $selected_drugs_stmt->fetchAll(PDO::FETCH_ASSOC);
        $selected_drug_stocks = [];
        foreach ($selected_drugs_rows as $key => $selected_drugs_row) {

            $drug_stmt_row = $DrugStock->find($selected_drugs_row["drug_sn"]);

            if ($selected_drugs_row["pay_mode"] == 'claim') {
                $drug_stmt_row->cash_price = $selected_drugs_row['claim_amt'];
            } else {
                $drug_stmt_row->cash_price = $selected_drugs_row["pay"];
            }

            $drug_stmt_row->ap_services_id = $selected_drugs_row["sn"];
            $drug_stmt_row->med_dosage = $selected_drugs_row["med_dosage"];
            $drug_stmt_row->med_dosage_unit = $selected_drugs_row["med_dosage_unit"];
            $drug_stmt_row->med_frequency = $selected_drugs_row["med_frequency"];
            $drug_stmt_row->med_duration = $selected_drugs_row["med_duration"];
            $drug_stmt_row->med_duration_unit = $selected_drugs_row["med_duration_unit"];
            $drug_stmt_row->prescription = $selected_drugs_row["prescription"];
            $drug_stmt_row->remarks = $selected_drugs_row["remarks"];
            $drug_stmt_row->invoice_status = $selected_drugs_row["invoice_status"];
            $drug_stmt_row->created_by = $selected_drugs_row["created_by"];
            $drug_stmt_row->prepared_by = $selected_drugs_row["prepared_by"];

            if ($selected_drugs_row['created_by'] != $_SESSION['id']) {
                $drug_stmt_row->invoice_status = 1;
            }

            array_push($selected_drug_stocks, $drug_stmt_row);
        }
    }

    echo json_encode(["status" => $status, "data" => $selected_drug_stocks, 'message' => $message]);
    exit;
}
