<?php
include("../Connections/Conn.php");
session_start();

// Required PHPMailer Use statements
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

require_once '../dompdf/autoload.inc.php'; //include DomPDF library

// Use TCPDF for encryption since DomPDF doesn't support it natively
require_once('../TCPDF/tcpdf.php');
require_once('../fpdi/fpdi.php');

use Dompdf\Dompdf;
use Dompdf\Options;


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
                $pdf_password
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

function generateSecurePDF($html_body, $user_password, $owner_password = null)
{
    // === Step 1: Generate PDF using DomPDF ===
    $options = new Options();
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);

    // Load HTML and render PDF
    $dompdf->loadHtml($html_body);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save DomPDF output to a temporary file
    $temp_pdf = tempnam(sys_get_temp_dir(), 'secure_pdf') . '.pdf';
    file_put_contents($temp_pdf, $dompdf->output());

    // === Step 2: Load PDF into TCPDF (via FPDI v1.6) for Security ===
    $pdf = new FPDI();
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->SetMargins(10, 10, 10);

    // Set metadata
    $pdf->SetCreator('Webmedic Hospital Management System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Secure Investigation Report');
    $pdf->SetSubject('Confidential Report');
    $pdf->SetKeywords('Hospital, Report, Secure, PDF');

    // Import DomPDF-generated PDF
    $page_count = $pdf->setSourceFile($temp_pdf);
    for ($i = 1; $i <= $page_count; $i++) {
        $pdf->AddPage();
        $tplId = $pdf->importPage($i);
        $pdf->useTemplate($tplId);
    }

    // Ensure owner password is set
    if ($owner_password === null) {
        $owner_password = md5($user_password); // Simple hash for PHP 5.6
    }

    // Set document protection
    $pdf->SetProtection(
        array('print', 'copy', 'modify'), // Allowed actions
        $user_password,                   // User password
        $owner_password,                  // Owner password
        0,                                // Protection mode
        'RC4-128'                         // Use RC4-128 encryption (compatible with TCPDF v2 & PHP 5.6)
    );

    // Generate unique filename
    $pdf_filename = 'secure_investigation_results_' . date('Y-m-d_His') . '.pdf';

    // Output secure PDF
    $pdf_content = $pdf->Output($pdf_filename, 'S');

    // Clean up temporary file
    unlink($temp_pdf);

    return [
        'content' => $pdf_content,
        'filename' => $pdf_filename
    ];
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
    $pdf_password = ''
) {
    try {
        $facility_info_stmt = $pdo->prepare("SELECT * FROM hospital_details LIMIT 1");
        $facility_info_stmt->execute();
        $facility_info = $facility_info_stmt->fetch(PDO::FETCH_ASSOC);


        $f = array(
            'facility_id' => isset($facility_info['code']) ? $facility_info['code'] : $facility_info['name'],
            'payment_required' => (strpos($facility_info['banks'], 'paystack') !== false) ? true : false,
            'facility_name' => $facility_info['name']
        );

        $test_to_send_string = "'" . implode("','", $test_to_send) . "'";

        if (!$is_external) {
            $patient_info_stmt = $pdo->prepare("SELECT surname, fname, oname, gender, dob, email FROM enrollee WHERE hospital_no = ? LIMIT 1");
            $patient_info_stmt->execute(array($enrollee));
            $patient_info = $patient_info_stmt->fetch(PDO::FETCH_ASSOC);

            $p = array(
                'name' => $patient_info['surname'] . ', ' . $patient_info['fname'] . ' ' . (isset($patient_info['oname']) ? $patient_info['oname'] : ''),
                'dob' => $patient_info['dob'],
                'email' => (!empty($external_email)) ? $external_email : $patient_info['email'],
                'gender' => $patient_info['gender']
            );

            // $lab_info_stmt = $pdo->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ? AND lab_manage.labrequest_no IN (?)");
            $lab_info_stmt = $pdo->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ? AND lab_manage.labrequest_no IN ({$test_to_send_string})");
            $lab_info_stmt->execute(array($enrollee, 'approve'));
            $lab_info = $lab_info_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $patient_info_stmt = $pdo->prepare("SELECT cust_name, gender, dob FROM pharm_ext WHERE transc_code = ? LIMIT 1");
            $patient_info_stmt->execute(array($enrollee));
            $patient_info = $patient_info_stmt->fetch(PDO::FETCH_ASSOC);

            $p = array(
                'name' => isset($patient_info['cust_name']) ? $patient_info['cust_name'] : '',
                'dob' => $patient_info['dob'],
                'email' => (!empty($external_email)) ? $external_email : '',
                'gender' => $patient_info['gender']
            );

            // $lab_info_stmt = $pdo->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ? AND lab_manage.labrequest_no IN (?)");
            $lab_info_stmt = $pdo->prepare("SELECT lab_manage.*, lab_result.* FROM lab_manage LEFT JOIN lab_result ON lab_manage.labrequest_no = lab_result.lab_no WHERE lab_manage.patient = ? AND lab_manage.data_capture_status = ? AND lab_manage.labrequest_no IN ({$test_to_send_string})");
            $lab_info_stmt->execute(array($enrollee, 'approve'));
            $lab_info = $lab_info_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $res_array = array();
        // print_r($lab_info);
        // die();
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
        if ($service_to_use == 'instant_res') {

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
        } else if ($service_to_use == 'facility') {
            //get facility smtp details from facilty infoand use smtp.
            // Create email content
            $subject = "Investigation Results from " . $f['facility_name'];

            // Get and encode logo image
            $logo_path = "../img/logo.png";
            $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $logo_base64 = '';
            $min_width = 400;

            if (file_exists($logo_path)) {
                // Get original image dimensions
                list($orig_width, $orig_height) = getimagesize($logo_path);

                // Calculate new dimensions maintaining aspect ratio
                $new_width = $orig_width;
                $new_height = $orig_height;

                if ($orig_width < $min_width) {
                    $new_width = $min_width;
                    $new_height = floor($orig_height * ($min_width / $orig_width));
                }

                // Create new image
                $source = imagecreatefrompng($logo_path);
                $destination = imagecreatetruecolor($new_width, $new_height);

                // Preserve transparency for PNG
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);

                // Resize image
                imagecopyresampled(
                    $destination,
                    $source,
                    0,
                    0,
                    0,
                    0,
                    $new_width,
                    $new_height,
                    $orig_width,
                    $orig_height
                );

                // Capture the new image data
                ob_start();
                imagepng($destination);
                $logo_data = ob_get_clean();

                // Encode the resized image
                $logo_base64 = base64_encode($logo_data);

                // Clean up
                imagedestroy($source);
                imagedestroy($destination);
            }

            // Build HTML email body with styled template
            $html_body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { 
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    /* Existing styles */
                    .result-content td {
                        border: 1px solid gray;
                        max-width: 0; /* Forces cell to respect width constraints */
                        overflow: hidden;
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                        white-space: normal;
                    }
                    .result-content table {
                        width: 100% !important;
                        table-layout: fixed !important;
                        border-collapse: collapse;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    /* New styles to handle nested content */
                    .result-content {
                        width: 100%;
                    }
                    /* Handle any nested tables within result-content */
                    .result-content table table {
                        width: 100% !important;
                        table-layout: fixed !important;
                        font-size: inherit;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    /* Handle cells within nested tables */
                    .result-content table table td {
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                        white-space: normal;
                        min-width: 0;
                        max-width: none;
                    }
                    /* Force images to be responsive */
                    .result-content img {
                        max-width: 100% !important;
                        height: auto !important;
                    }
                    /* Rest of your existing styles... */
                    .header-table {
                        width: 100%;
                        margin-bottom: 30px;
                        border-bottom: 2px solid #eee;
                        padding-bottom: 20px;
                    }
                    .patient-info {
                        background-color: #f8f9fa;
                        padding: 15px;
                        border-radius: 5px;
                        margin-bottom: 20px;
                    }
                    .result-section {
                        margin-bottom: 30px;
                        border: 1px solid #dee2e6;
                        padding: 15px;
                        border-radius: 5px;
                    }
                    .result-header {
                        background-color: #f8f9fa;
                        padding: 10px;
                        margin: -15px -15px 15px -15px;
                        border-bottom: 1px solid #dee2e6;
                        border-radius: 5px 5px 0 0;
                    }
                    .footer {
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 2px solid #eee;
                        font-size: 12px;
                        color: #6c757d;
                    }
                    .logo-container {
                        display: block;
                        width: 100%;
                        min-width: 300px;
                        position: relative;
                        padding: 10px;
                    }
                    .logo-image {
                        display: block;
                        width: 100%;
                        height: auto;
                        object-fit: contain;
                        object-position: left center;
                    }
                </style>
            </head>
            <body>
                <table class='header-table' cellpadding='5' cellspacing='5' border='0'>
                    <tr>
                        <td width='50%' align='left'>";

            // Add logo if available, otherwise show text
            if ($logo_base64) {
                $html_body .= "
            <div class='logo-container'>
                <img src='data:image/{$logo_type};base64,{$logo_base64}' 
                     class='logo-image'
                     alt='{$facility_info['name']} Logo'
                     width='400px'
                />
            </div>";
            } else {
                $html_body .= "<h2>{$facility_info['name']}</h2>";
            }

            $html_body .= "
                        </td>
                        <td width='50%' align='right'>
                            <div style='font-size:18px; font-weight:bold;'>{$facility_info['name']}</div>
                            <br>
                            <div style='font-size:14px'>
                                {$facility_info['address']}<br><br>
                                {$facility_info['phones']}
                            </div>
                        </td>
                    </tr>
                </table>

                <div class='patient-info'>
                    <h2 style='margin-top:0;'>Patient Information</h2>
                    <table style='width:100%'>
                        <tr>
                            <td><strong>Name:</strong> " . htmlspecialchars($p['name']) . "</td>
                            <td><strong>Gender:</strong> " . htmlspecialchars($p['gender']) . "</td>
                        </tr>
                        <tr>
                            <td><strong>Date of Birth:</strong> " . htmlspecialchars($p['dob']) . "</td>
                            <td><strong>Report Dispatch Date:</strong> " . date('Y-m-d') . "</td>
                        </tr>
                    </table>
                </div>

                <h2>Investigation Results</h2>";

            foreach ($res_array as $result) {
                $html_body .= "
                <div class='result-section'>
                    <!-- 
                    <div class='result-header'>
                        <h3 style='margin:0;'>" . htmlspecialchars($result['result_name']) . "</h3>
                    </div>
                     -->
                    <table style='width:100%; margin-bottom:10px;'>
                        <tr>
                            <td><strong>Date:</strong> " . htmlspecialchars($result['date']) . "</td>
                            <td><strong>Staff:</strong> " . htmlspecialchars($result['staff']) . "</td>
                        </tr>
                    </table>
                    <div class='result-content'>" . $result['result_html'] . "</div>
                </div>";
            }

            // Modify the footer to include mention of PDF attachment
            $html_body .= "
                <div class='footer'>
                    <p>This report was generated by " . htmlspecialchars($f['facility_name']) . "</p>
                </div>
            </body>
            </html>";

            $html_body_email = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { 
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    .header-table {
                        width: 100%;
                        margin-bottom: 30px;
                        border-bottom: 2px solid #eee;
                        padding-bottom: 20px;
                    }
                    .footer {
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 2px solid #eee;
                        font-size: 12px;
                        color: #6c757d;
                    }
                    .logo-container {
                        display: block;
                        width: 100%;
                        min-width: 300px;
                        position: relative;
                        padding: 10px;
                    }
                    
                    .logo-image {
                        display: block;
                        width: 100%;
                        height: auto;
                        object-fit: contain;
                        object-position: left center;
                    }
                </style>
            </head>
            <body>
                <table class='header-table' cellpadding='5' cellspacing='5' border='0'>
                    <tr>
                        <td width='50%' align='left'>";

            // Add logo if available, otherwise show text
            if ($logo_base64) {
                $html_body_email .= "
                    <div class='logo-container'>
                        <img src='data:image/{$logo_type};base64,{$logo_base64}' 
                            class='logo-image'
                            alt='{$facility_info['name']} Logo'
                            width='400px'
                        />
                    </div>";
            } else {
                $html_body_email .= "<h2>{$facility_info['name']}</h2>";
            }

            $html_body_email .= "
                        </td>
                        <td width='50%' align='right'>
                            <div style='font-size:18px; font-weight:bold;'>{$facility_info['name']}</div>
                            <br>
                            <div style='font-size:14px'>
                                {$facility_info['address']}<br><br>
                                {$facility_info['phones']}
                            </div>
                        </td>
                    </tr>
                </table>";

            // Modify the footer to include mention of PDF attachment
            $html_body_email .= "
                <hr>
                <p>Kindly find attached Your Investigation results</p>
                <p>You might be required to supply a security key before viewing the contents of the report. Kindly contact (contatct info above) the facility if you have questions</p>
                <hr>
                <div class='footer'>
                    <p>This report was generated by " . htmlspecialchars($f['facility_name']) . "</p>
                    <p><small>This is an automated email. Please do not reply to this message.</small></p>
                </div>
            </body>
            </html>";

            // Generate a unique filename for the PDF
            $pdf_filename = 'investigation_results_' . urlencode($p['name']) . '_' . date('Y-m-d_His') . '.pdf';

            if ($secure_pdf == 'no') {
                // Generate PDF version of the report
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html_body);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdf_content = $dompdf->output();
            } else {
                $pdf_content = generateSecurePDF($html_body, (!empty($pdf_password)) ? $pdf_password : $enrollee, 'webmedic')['content'];
            }



            // Check if SMTP details are available
            if (
                !empty($facility_info['smtp_host']) && !empty($facility_info['smtp_username']) &&
                !empty($facility_info['smtp_password'])
            ) {
                // Use PHPMailer with SMTP
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = $facility_info['smtp_host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $facility_info['smtp_username'];
                    $mail->Password = $facility_info['smtp_password'];
                    $mail->SMTPSecure = ($facility_info['smtp_encryption']) ? $facility_info['smtp_encryption'] : 'tls';
                    $mail->Port = ($facility_info['smtp_port']) ? $facility_info['smtp_port'] : 587;

                    // Recipients
                    $mail->setFrom($facility_info['smtp_username'], $f['facility_name']);
                    $mail->addAddress($p['email']);

                    // Add PDF attachment
                    $mail->addStringAttachment($pdf_content, $pdf_filename, 'base64', 'application/pdf');

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $html_body_email;
                    $mail->AltBody = strip_tags(str_replace(['<br>', '</div>'], "\n", $html_body_email));

                    $mail->send();
                    $response_data = ['success' => true, 'message' => 'Email sent successfully via SMTP with PDF attachment'];
                } catch (Exception $e) {
                    error_log("SMTP email sending failed: " . $mail->ErrorInfo);
                    throw new Exception("Email sending failed: " . $mail->ErrorInfo);
                }
            } else {
                // Fallback to mail() function with attachment
                $boundary = md5(time());

                $headers = array(
                    'MIME-Version: 1.0',
                    'From: ' . $f['facility_name'] . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
                    'Reply-To: noreply@' . $_SERVER['HTTP_HOST'],
                    'X-Mailer: PHP/' . phpversion(),
                    'Content-Type: multipart/mixed; boundary=' . $boundary
                );

                // Email body with attachment
                $message = "--" . $boundary . "\r\n";
                $message .= "Content-Type: text/html; charset=UTF-8\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $message .= chunk_split(base64_encode($html_body_email)) . "\r\n";

                // Add PDF attachment
                $message .= "--" . $boundary . "\r\n";
                $message .= "Content-Type: application/pdf; name=\"" . $pdf_filename . "\"\r\n";
                $message .= "Content-Disposition: attachment; filename=\"" . $pdf_filename . "\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $message .= chunk_split(base64_encode($pdf_content)) . "\r\n";
                $message .= "--" . $boundary . "--";

                $sent = mail($p['email'], $subject, $message, implode("\r\n", $headers));

                if ($sent) {
                    $response_data = ['success' => true, 'message' => 'Email sent successfully via mail() with PDF attachment'];
                } else {
                    throw new Exception("Failed to send email via mail() function");
                }
            }
        }

        //echo $response;
    } catch (Exception $e) {
        error_log("Error in sync_results_to_instant_result: " . $e->getMessage() . ' on line ' . $e->getLine());
        throw $e;
    }
}
