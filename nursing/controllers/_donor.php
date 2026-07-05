<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');




$request = post_request();


if (isset($_POST["addDonor"])) {
    $patient_info = $Patient->get(['hospital_no' => $request->hospital_no]);
    $transplant_id = $request->transplant_id;
    $blood_group = $request->blood_group;
    $genotype = $request->genotype;
    $gender = null;
    if (!empty($patient_info)) {
        $gender = $patient_info->gender;
    }

    $data['transplant_id'] = $_POST['transplant_id'];
    $data['name'] = $_POST['name'];
    $data['phone_number'] = $_POST['phone_number'];
    $data['address'] = $_POST['address'];
    $data['nok_name'] = $_POST['nok_name'];
    $data['nok_phone_number'] = $_POST['nok_phone_number'];
    $data['nok_address'] = $_POST['nok_address'];
    $data['cross_match_value'] = null;
    $data['percentage'] = null;
    $data['blood_group'] = $_POST['blood_group'];
    $data['gender'] = $gender;
    $data['genotype'] = $_POST['genotype'];

    $error_msg = 'Opps something went wrong...';
    $status = 401;



    $percent = intval($data['percentage']);

    if ($percent < 0 || $percent > 100) {
        $error_msg = 'The percentage value is invalid';
    } else {
        $data['is_matched'] = false;
        if ($percent >= 60) {
            $data['is_matched']  =  true;
        }


        $addDonor = $Transplant->addDonor($data);

        if ($addDonor == true) {
            $status = 200;
            $error_msg = 'Saved successfully...';

            $update = $db->prepare("UPDATE enrollee SET blood_g = ?, geno_type = ? WHERE hospital_no = ? ");
            $update = $update->execute(array(
                $blood_group,
                $genotype,
                $hospital_no
            ));
        } else {
            $error_msg = $addDonor;
        }
    }

    $donor_list = $Transplant->getDonor(['transplant_id' => $transplant_id], true);
    echo json_encode(["message" => $error_msg, "status" => $status, "data" => $donor_list]);
    exit;
}
