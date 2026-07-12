<?php
include("../Connections/Conn.php");
session_start();

// Required PHPMailer Use statements
require_once '../inc/ExternalNotification.php';


// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hosp_no'], $_POST['labrequest_no'], $_POST['type_patient'], $_POST['test_to_send'])) {
        $hosp_no = $_POST['hosp_no'];
        $labrequest_no = $_POST['labrequest_no'];
        $type_patient = $_POST['type_patient'];
        $test_to_send = $_POST['test_to_send'];
        $service_to_use = $_POST['service_to_use'];
        $external_email = isset($_POST['email']) ? $_POST['email'] : '';
        $send_sms = (isset($_POST['send_sms']) && $_POST['send_sms'] == 'yes') ? true : false;
        $external_phone = isset($_POST['phone']) ? $_POST['phone'] : '';

        $encrypt_res = (isset($_POST['encrypt_pdf']) && $_POST['encrypt_pdf'] == 'yes') ? 'yes' : 'no';
        $pdf_password = (isset($_POST['pdf_password'])) ? $_POST['pdf_password'] : $hosp_no;

        $test_to_send = explode(',', $test_to_send);

        try {
            sync_results_to_instant_result(
                $db,
                $hosp_no,
                ($type_patient != 'IN'),
                $external_email,
                $test_to_send,
                $service_to_use,
                $encrypt_res,
                $pdf_password,
                $send_sms,
                $external_phone
            );
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

function formatLabResult($lab)
{
    $result_html = '';

    // Start table
    $result_html .= '<table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;"><tbody>';

    // Header row
    $result_html .= '<tr>';
    $result_html .= '<td width="33%" style="border-bottom:1px solid #000; border-top:1px solid #000;"><strong>' . $lab['test_name'] . '</strong></td>';
    $result_html .= '<td width="33%" style="border-bottom:1px solid #000; border-top:1px solid #000; text-align:left">' .
        ($lab['section'] == 'Laboratory' ? 'Specimen: ' . $lab['collected_specimen'] : '') . '</td>';
    $result_html .= '<td width="33%" style="border-bottom:1px solid #000; border-top:1px solid #000; text-align:left"></td>';
    $result_html .= '</tr>';

    // Content based on field type
    switch ($lab['field_type']) {
        case 'values':
            // Fetch multiple results from lab_scan_input_results
            $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :labrequest_no AND test_no = :test_id");
            $stmt->execute(array(
                ':labrequest_no' => $lab['labrequest_no'],
                ':test_id' => $lab['test_id']
            ));

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result_html .= '<tr>';
                $result_html .= '<td width="33%" style="border-bottom:1px solid #000; text-align:left">' . $row['value_title'] . '</td>';
                $result_html .= '<td width="33%" style="border-bottom:1px solid #000; text-align:left"><strong>Result:</strong> ' . $row['result'] . '</td>';
                $result_html .= '<td width="33%" style="border-bottom:1px solid #000; text-align:left">Ref.: ' . $row['value_ref'] . '</td>';
                $result_html .= '</tr>';
            }
            break;

        case 'value':
        case 'options':
            $result_html .= '<tr>';
            $result_html .= '<td width="33%" style="border-bottom:1px solid #000; text-align:left">' . $lab['field_name'] . '</td>';
            $result_html .= '<td width="33%" style="border-bottom:1px solid #000; text-align:left"><strong>Result:</strong> ' . $lab['field_value'] . '</td>';
            $result_html .= '<td width="33%" style="border-bottom:1px solid #000; text-align:left">Ref.: ' . $lab['field_ref'] . '</td>';
            $result_html .= '</tr>';
            break;

        case 'report':
        default:
            if (!empty($lab['field_ref'])) {
                $result_html .= '<tr>';
                $result_html .= '<td><strong>Reference</strong></td>';
                $result_html .= '<td colspan="2" style="border-bottom:1px solid #000; text-align:left">' . $lab['field_ref'] . '</td>';
                $result_html .= '</tr>';
            }
            $result_html .= '<tr>';
            $result_html .= '<td colspan="3" style="font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">' . $lab['field_value'] . '</td>';
            $result_html .= '</tr>';
            break;
    }

    // Add comment if exists
    if (!empty($lab['comment'])) {
        $result_html .= '<tr>';
        $result_html .= '<td style="border-bottom:1px solid #000; text-align:left"><strong>Comment</strong></td>';
        $result_html .= '<td colspan="2" style="border-bottom:1px solid #000; text-align:left">' . $lab['comment'] . '</td>';
        $result_html .= '</tr>';
    }

    // Footer row
    $result_html .= '<tr>';
    $result_html .= '<td colspan="2" style="border-bottom:1px solid #000; text-align:left"><strong>Prepared By</strong>: ' .
        $lab['lab_sci_name'] . ' [ ' . $lab['lab_sci_speciality'] . ' ]</td>';
    $result_html .= '<td>Result Date:&nbsp; ' . date("d-m-Y", strtotime($lab['result_date'])) . '</td>';
    $result_html .= '</tr>';

    // Close table
    $result_html .= '</tbody></table>';

    return $result_html;
}

function sync_results_to_instant_result(
    $pdo,
    $enrollee,
    $is_external = false,
    $external_email = '',
    $test_to_send = [],
    $service_to_use = 'instant_res',
    $secure_pdf = 'no',
    $pdf_password = '',
    $send_sms = false,
    $external_phone = ''
) {
    try {
        require_once '../inc/ExternalNotification.php';
        $notifier = new ExternalNotification($pdo);
        $test_to_send_string = "'" . implode("','", $test_to_send) . "'";

        if (!$is_external) {
            $patient_info_stmt = $pdo->prepare("SELECT surname, fname, oname, gender, dob, email, phone FROM enrollee WHERE hospital_no = ? LIMIT 1");
            $patient_info_stmt->execute(array($enrollee));
            $patient_info = $patient_info_stmt->fetch(PDO::FETCH_ASSOC);

            $p = array(
                'name' => $patient_info['surname'] . ', ' . $patient_info['fname'] . ' ' . (isset($patient_info['oname']) ? $patient_info['oname'] : ''),
                'dob' => $patient_info['dob'],
                'email' => (!empty($external_email)) ? $external_email : $patient_info['email'],
                'phone' => (!empty($external_phone)) ? $external_phone : $patient_info['phone'],
                'gender' => $patient_info['gender']
            );

            $lab_info_stmt = $pdo->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ? AND lab_manage.labrequest_no IN ({$test_to_send_string})");
            $lab_info_stmt->execute(array($enrollee, 'approve'));
            $lab_info = $lab_info_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $patient_info_stmt = $pdo->prepare("SELECT cust_name, gender, dob, phone_no FROM pharm_ext WHERE transc_code = ? LIMIT 1");
            $patient_info_stmt->execute(array($enrollee));
            $patient_info = $patient_info_stmt->fetch(PDO::FETCH_ASSOC);

            $p = array(
                'name' => isset($patient_info['cust_name']) ? $patient_info['cust_name'] : '',
                'dob' => $patient_info['dob'],
                'email' => (!empty($external_email)) ? $external_email : '',
                'phone' => (!empty($external_phone)) ? $external_phone : (isset($patient_info['phone_no']) ? $patient_info['phone_no'] : ''),
                'gender' => $patient_info['gender']
            );

            $lab_info_stmt = $pdo->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ? AND lab_manage.labrequest_no IN ({$test_to_send_string})");
            $lab_info_stmt->execute(array($enrollee, 'approve'));
            $lab_info = $lab_info_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $res_array = array();
        foreach ($lab_info as $lab) {
            array_push(
                $res_array,
                array(
                    'result_name' => $lab['test_name'],
                    'result_html' => formatLabResult($lab),
                    'date' => date("Y-m-d", strtotime($lab['result_date'])),
                    'staff' => $lab['lab_sci_name']
                )
            );
        }

        if ($service_to_use == 'instant_res') {
            $notifier->sendInstantResultApi($p, $res_array);
        } else if ($service_to_use == 'facility') {
            $logo_path = "../img/logo.png";
            $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $logo_base64 = '';
            
            if (file_exists($logo_path)) {
                $min_width = 400;
                list($orig_width, $orig_height) = getimagesize($logo_path);
                $new_width = $orig_width;
                $new_height = $orig_height;

                if ($orig_width < $min_width) {
                    $new_width = $min_width;
                    $new_height = floor($orig_height * ($min_width / $orig_width));
                }

                $source = imagecreatefrompng($logo_path);
                $destination = imagecreatetruecolor($new_width, $new_height);
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);

                imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);

                ob_start();
                imagepng($destination);
                $logo_data = ob_get_clean();
                $logo_base64 = base64_encode($logo_data);

                imagedestroy($source);
                imagedestroy($destination);
            }
            
            $notifier->sendEmailWithSecurePdf($p, $res_array, $secure_pdf, $pdf_password, $logo_base64, $logo_type);
        }

        if ($send_sms && !empty($p['phone'])) {
            $facility = $notifier->getFacilityInfo();
            $msg = "Dear {$p['name']}, your investigation results from {$facility['name']} are ready.";
            if ($service_to_use == 'facility') {
                $msg .= " Please check your email.";
            } else if ($service_to_use == 'instant_res') {
                $msg .= " You can view them online.";
            }
            $notifier->sendSms($p['phone'], $msg);
        }

    } catch (Exception $e) {
        error_log("Error in sync_results_to_instant_result: " . $e->getMessage() . ' on line ' . $e->getLine());
        throw $e;
    }
}