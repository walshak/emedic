<?php
include("../Connections/Conn.php");
session_start();

// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hosp_no'], $_POST['labrequest_no'], $_POST['type_patient'])) {
        $hosp_no = $_POST['hosp_no'];
        $labrequest_no = $_POST['labrequest_no'];
        $type_patient = $_POST['type_patient'];
        $external_email = isset($_POST['email']) ? $_POST['email'] : '';

        try {
            sync_results_to_instant_result($db, $hosp_no, ($type_patient != 'IN'), $external_email);
            echo "Result sent successfully.";
        } catch (Exception $e) {
            error_log("Error syncing results: " . $e->getMessage() . ' on line ' . $e->getLine());
            http_response_code(500); // Internal Server Error
            echo "An error occurred while sending the result. " . $e->getMessage() . ' on line ' . $e->getLine();
        }
    } else {
        http_response_code(400); // Bad Request
        echo "Invalid parameters.";
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo "Invalid request method.";
}

function sync_results_to_instant_result($db, $enrollee, $is_external = false, $external_email = '')
{
    try {
        $facility_info_stmt = $db->prepare("SELECT * FROM hospital_details LIMIT 1");
        $facility_info_stmt->execute();
        $facility_info = $facility_info_stmt->fetch(PDO::FETCH_ASSOC);

        $f = array(
            'facility_id' => isset($facility_info['code']) ? $facility_info['code'] : $facility_info['name'],
            'payment_required' => (strpos($facility_info['banks'], 'paystack') !== false) ? true : false,
            'facility_name' => $facility_info['name']
        );

        if (!$is_external) {
            $patient_info_stmt = $db->prepare("SELECT surname, fname, oname, gender, dob, email FROM enrollee WHERE hospital_no = ? LIMIT 1");
            $patient_info_stmt->execute(array($enrollee));
            $patient_info = $patient_info_stmt->fetch(PDO::FETCH_ASSOC);

            $p = array(
                'name' => $patient_info['surname'] . ', ' . $patient_info['fname'] . ' ' . (isset($patient_info['oname']) ? $patient_info['oname'] : ''),
                'dob' => $patient_info['dob'],
                'email' => (!empty($external_email)) ? $external_email : $patient_info['email'],
                'gender' => $patient_info['gender']
            );

            $lab_info_stmt = $db->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ?");
            $lab_info_stmt->execute(array($enrollee, 'approve'));
            $lab_info = $lab_info_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $patient_info_stmt = $db->prepare("SELECT cust_name, gender, dob FROM pharm_ext WHERE transc_code = ? LIMIT 1");
            $patient_info_stmt->execute(array($enrollee));
            $patient_info = $patient_info_stmt->fetch(PDO::FETCH_ASSOC);

            $p = array(
                'name' => isset($patient_info['cust_name']) ? $patient_info['cust_name'] : '',
                'dob' => $patient_info['dob'],
                'email' => (!empty($external_email)) ? $external_email : '',
                'gender' => $patient_info['gender']
            );

            $lab_info_stmt = $db->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ?");
            $lab_info_stmt->execute(array($enrollee, 'approve'));
            $lab_info = $lab_info_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $res_array = array();

        foreach ($lab_info as $lab) {
            array_push(
                $res_array,
                array(
                    'result_name' => $lab['test_name'],
                    'result_html' => $lab['field_value'],
                    'date' => date("Y-m-d", strtotime($lab['result_date'])),
                    'staff' => $lab['lab_sci_name']
                )
            );
        }

        $data = array(
            'facility_id' => $f['facility_id'],
            'payment_required' => $f['payment_required'],
            'facility_name' => $f['facility_name'],
            'patient_email' => $p['email'],
            'patient_name' => $p['name'],
            'patient_dob' => $p['dob'],
            'patient_gender' => $p['gender'],
            'results' => $res_array
        );

        //var_dump($data);
        //die();

        // cURL to send data to the API
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://instantresult.ng/api/sync-results',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL error: $error_msg");
        }

        curl_close($curl);

        $response_data = json_decode($response, true);


        //echo $response;
    } catch (Exception $e) {
        error_log("Error in sync_results_to_instant_result: " . $e->getMessage() . ' on line ' . $e->getLine());
        throw $e;
    }
}
