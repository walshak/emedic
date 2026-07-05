<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../../doctor/objects.php');
include('../../doctor/helpers.php');


if (isset($_REQUEST['populateVaccine'])) {
    $hospital_no = $_REQUEST['hospital_no'];
    $sql = "SELECT * FROM  immunization_vaccines  WHERE hospital_no = ? ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$hospital_no]);
    echo  $vaccines_given = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}



if (isset($_POST['delete_vaccine'])) {
    $vaccine_id = $_POST['delete_vaccine_id'];
    $patient_ap_services_id = $_POST['patient_ap_services_id'];

    $stmt = $db->prepare("DELETE FROM immunization_vaccines 
    WHERE id = :id 
    AND DATE_SUB(CURDATE(), INTERVAL 2 DAY) <= date_given");
    $stmt->bindParam(':id', $vaccine_id, PDO::PARAM_INT);

    if ($stmt->execute()) {

        $stmt = $db->prepare("DELETE FROM patient_ap_services WHERE sn = :sn");
        $stmt->bindParam(':sn', $patient_ap_services_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Vaccine deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete vaccine']);
    }

    exit;
}



$action = $_POST['action'];

if ($action == 'enrol') {
    $request = json_decode(json_encode(
        [
            "hospital_no" => $_POST['hospital_no'],
            "appointment_number" => $_POST['appointment_number'],
            "mother_name" => $_POST['mother_name'],
            "father_name" => $_POST['father_name'],
            "gestation_at_birth" => $_POST['gestation_at_birth'],
            "neonatal_complication" => $_POST['neonatal_complication'],
            "mode_of_delivery" => $_POST['mode_of_delivery']
        ]
    ));



    if (empty($Immunization->get(['hospital_no' => $request->hospital_no]))) {
        $enrol = $Immunization->enrol($request);
        if ($enrol) {
            echo json_encode(["status" => 200, "message" => "Enrolled Successfully..."]);
        } else {
            echo json_encode(["status" => 505, "message" => "Oops! Something went wrong..."]);
        }
    } else {
        echo json_encode(["status" => 401, "message" => "Oops! Enrolled before..."]);
    }



    exit;
}

if ($action == 'addVaccine') {


    $antigen_arr = explode('||', $_POST['antigen']);
    $drug_sn = $antigen_arr[0];
    $vaccine_name = $antigen_arr[1];
    $hosp_price =  $antigen_arr[2];
    $ext_price = $antigen_arr[3];
    $data =  [
        "hospital_no" => $_POST['hospital_no'],
        $_POST['hospital_no'],
        "appointment_number" => $_POST['appointment_number'],
        "antigen" => $drug_sn,
        "next_vaccination_date" => $_POST['next_vaccination_date'],
        "comment" => $_POST['comment'],
        "vaccine_name" => $vaccine_name,
        "date_given" => $_POST['date_given'],
        "given_by" => $_SESSION['fullname'],
        "created_by" => $_SESSION['id']
    ];


    $serv_group = "External Service";
    $cat_type = "Vaccination";
    $dept_id = $_SESSION['dept_id'];

    $patient_info = $Patient->getByHospitalNo($_POST['hospital_no']);
    $interest = $patient_info->interest;
    $insurance_type = $patient_info->insurance_type;
    $insurance_no = $patient_info->insurance_no;
    $services_access = $patient_info->services_access;
    $add_minus = $patient_info->add_minus;
    $payment_mode = $patient_info->payment_mode;

    $app_no = $_POST['appointment_number'];
    $hospital_no = $_POST['hospital_no'];

    $target_sn = $drug_sn;
    $NHIS_DRUG_CONSUMBL_STATE = 0;
    $_tariff_table = "hmo_stocks_tariff";

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
        $NHIS_DRUG_CONSUMBL_STATE
    );

    $claim_amt = $amount_invoice["claim_amt"];
    $amount_paying = $amount_invoice["amount_paying"];
    $pay_mode = $amount_invoice["pay_mode"];
    $ccop_int_charge = $amount_invoice["ccop_int_charge"];
    $item_amt = $amount_invoice["amount_paying"];


    if ($Immunization->is_vaccine_given($data) == false) {
        $date = date('Y-m-d');
        if (intval($amount_invoice["amount_paying"]) >= 0 || intval($hosp_price) >= 0) {
            $stmt = $db->prepare("INSERT INTO patient_ap_services (
                app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, remarks,
                drug_status, invoice_status, invoice_no, prepared_by, transact_date, pay, pay_mode
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Execute the statement with the provided data
            $stmt->execute([
                $_POST['appointment_number'],
                $_POST['hospital_no'],
                $services_access,
                $serv_group,
                $cat_type,
                $dept_id,
                $drug_sn,
                $vaccine_name,
                $amount_invoice["item_amt"],
                $amount_invoice["claim_amt"],
                $amount_invoice["ccop_int_charge"],
                1, // Quantity is set to 1
                "", // Remarks
                '1', // Drug status
                1, // Invoice status
                $amount_invoice["invoice_no"],
                $_SESSION['fullname'],
                $date,
                $amount_invoice["amount_paying"],
                $amount_invoice["pay_mode"]
            ]);

            // Get the last inserted ID
            $patient_ap_services_id = $db->lastInsertId();
        } else {
            $patient_ap_services_id = null;
        }


        $data['patient_ap_services_id'] = $patient_ap_services_id;
        $addVaccine = $Immunization->addVaccine($data);
        if ($addVaccine) {
            echo json_encode(["status" => 200, "message" => "Vaccine Given..."]);
        } else {
            echo json_encode(["status" => 505, "message" => "Oops! Something went wrong..."]);
        }
    } else {
        echo json_encode(["status" => 401, "message" => "Oops! Saved before..."]);
    }
}
