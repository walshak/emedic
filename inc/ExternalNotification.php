<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

// We use __DIR__ to ensure absolute paths relative to this inc/ directory
@require_once __DIR__ . '/../investigations/eSender/vendor/phpmailer/src/Exception.php';
@require_once __DIR__ . '/../investigations/eSender/vendor/phpmailer/src/PHPMailer.php';
@require_once __DIR__ . '/../investigations/eSender/vendor/phpmailer/src/SMTP.php';

if (file_exists(__DIR__ . '/../dbase/patient_dbase/vendor/autoload.php')) {
    @require_once __DIR__ . '/../dbase/patient_dbase/vendor/autoload.php';
}
if (file_exists(__DIR__ . '/../TCPDF/tcpdf.php')) {
    @require_once __DIR__ . '/../TCPDF/tcpdf.php';
}
if (file_exists(__DIR__ . '/../fpdi/fpdi.php')) {
    @require_once __DIR__ . '/../fpdi/fpdi.php';
}

class ExternalNotification
{
    private $pdo;
    private $facility_info;
    private $smtpHost;
    private $smtpUser;
    private $smtpPass;
    private $smtpPort;
    private $smtpEncry;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;

        // Fetch hospital details for SMTP and Twilio credentials
        $stmt = $pdo->query("SELECT *, smtp_host, smtp_username, smtp_password, smtp_port, smtp_encryption FROM hospital_details LIMIT 1");
        $this->facility_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($this->facility_info) {
            $this->smtpHost = $this->facility_info['smtp_host'];
            $this->smtpUser = $this->facility_info['smtp_username'];
            $this->smtpPass = $this->facility_info['smtp_password'];
            $this->smtpPort = $this->facility_info['smtp_port'];
            $this->smtpEncry = $this->facility_info['smtp_encryption'];
        } else {
            $this->facility_info = [
                'name' => 'Hospital',
                'address' => '',
                'phones' => '',
                'code' => '',
                'banks' => ''
            ];
        }
    }

    public function getFacilityInfo()
    {
        return $this->facility_info;
    }

    /**
     * Send an SMS using Twilio (native cURL)
     */
    public function sendSms($phoneNumber, $message)
    {
        $sid = $this->facility_info['twilio_sid'];
        $token = $this->facility_info['twilio_auth_token'];
        $from = $this->facility_info['twilio_phone_number'];

        if (empty($sid) || empty($token) || empty($from)) {
            error_log("Twilio credentials not configured.");
            return false;
        }

        // Clean phone number (ensure E.164 format roughly, or assume user provides valid)
        // Twilio requires E.164 format. E.g., +234...
        if (strpos($phoneNumber, '+') !== 0) {
            $phoneNumber = '+' . ltrim($phoneNumber, '0');
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";

        $data = [
            'To' => $phoneNumber,
            'From' => $from,
            'Body' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 201 || $http_code == 200) {
            return true;
        } else {
            error_log("Twilio SMS failed: " . print_r($response, true));
            return false;
        }
    }

    /**
     * Send welcome Email and SMS to a newly registered patient
     */
    public function sendWelcome($patientInfo)
    {
        $email = isset($patientInfo['email']) ? $patientInfo['email'] : '';
        $phone = isset($patientInfo['phone']) ? $patientInfo['phone'] : '';
        $name = isset($patientInfo['name']) ? $patientInfo['name'] : '';

        // Prepare variables
        $hospitalName = $this->facility_info['name'];
        $patientId = isset($patientInfo['hospital_no']) ? $patientInfo['hospital_no'] : '';

        // SMS
        $smsTemplate = !empty($this->facility_info['welcome_sms_template']) 
            ? $this->facility_info['welcome_sms_template'] 
            : "Welcome [PatientName] to [HospitalName]! Your Hospital ID is [PatientID]. We're glad to have you with us.";
            
        if (!empty($phone)) {
            $smsMsg = str_replace(
                ['[PatientName]', '[HospitalName]', '[PatientID]'],
                [$name, $hospitalName, $patientId],
                $smsTemplate
            );
            $this->sendSms($phone, $smsMsg);
        }

        // Email
        $emailTemplate = !empty($this->facility_info['welcome_email_template']) 
            ? $this->facility_info['welcome_email_template'] 
            : "<p>Dear [PatientName],</p><p>Welcome to <strong>[HospitalName]</strong>! We are delighted to have you as our patient.</p><p>Your Hospital ID is: <strong>[PatientID]</strong></p><p>Please keep this ID safe as you will need it for future visits.</p><p>Thank you for choosing us.</p>";
            
        if (!empty($email)) {
            $emailBodyContent = str_replace(
                ['[PatientName]', '[HospitalName]', '[PatientID]'],
                [$name, $hospitalName, $patientId],
                $emailTemplate
            );
            
            $logoPath = __DIR__ . '/../img/logo.png';
            $hasLogo = file_exists($logoPath);

            $html_body_email = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
                    .header-table { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee; font-size: 12px; color: #6c757d; }
                </style>
            </head>
            <body>
                <table class='header-table' cellpadding='5' cellspacing='5' border='0'>
                    <tr>
                        <td width='50%' align='left'>";
            if ($hasLogo) {
                $html_body_email .= "<img src='cid:hospital_logo' alt='{$hospitalName} Logo' width='200px'/>";
            } else {
                $html_body_email .= "<h2>{$hospitalName}</h2>";
            }
            $html_body_email .= "
                        </td>
                        <td width='50%' align='right'>
                            <div style='font-size:18px; font-weight:bold;'>{$hospitalName}</div>
                            <br>
                            <div style='font-size:14px'>
                                {$this->facility_info['address']}<br><br>
                                {$this->facility_info['phones']}
                            </div>
                        </td>
                    </tr>
                </table>
                <hr>
                <div>
                    {$emailBodyContent}
                </div>
                <hr>
                <div class='footer'>
                    <p>This is an automated welcome message from " . htmlspecialchars($hospitalName) . ".</p>
                </div>
            </body>
            </html>";

            $subject = "Welcome to " . $hospitalName;
            $this->sendEmail($email, $name, $subject, $html_body_email);
        }
    }

    /**
     * Helper to send standard email without attachment
     */
    public function sendEmail($toEmail, $toName, $subject, $htmlBody)
    {
        return $this->dispatchEmail($toEmail, $subject, $htmlBody);
    }

    /**
     * Push lab results to InstantResult.ng
     */
    public function sendInstantResultApi($patient, $resultsArray)
    {
        $data = array(
            'facility_id' => isset($this->facility_info['code']) ? $this->facility_info['code'] : $this->facility_info['name'],
            'payment_required' => (strpos($this->facility_info['banks'], 'paystack') !== false) ? true : false,
            'facility_name' => $this->facility_info['name'],
            'patient_email' => $patient['email'],
            'patient_name' => $patient['name'],
            'patient_dob' => $patient['dob'],
            'patient_gender' => $patient['gender'],
            'results' => $resultsArray
        );

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
        return json_decode($response, true);
    }

    /**
     * Send Email with Secure PDF Attachment
     */
    public function sendEmailWithSecurePdf($patient, $resultsArray, $securePdf = 'no', $pdfPassword = '', $logoBase64 = '', $logoType = '')
    {
        $facility_info = $this->facility_info;
        $subject = "Investigation Results from " . $facility_info['name'];

        // Build HTML for the PDF body
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
                .result-content td {
                    border: 1px solid gray;
                    max-width: 0;
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
                .result-content { width: 100%; }
                .result-content table table {
                    width: 100% !important;
                    table-layout: fixed !important;
                    font-size: inherit;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .result-content table table td {
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    white-space: normal;
                    min-width: 0;
                    max-width: none;
                }
                .result-content img {
                    max-width: 100% !important;
                    height: auto !important;
                }
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
        if ($logoBase64) {
            $html_body .= "<div class='logo-container'><img src='data:image/{$logoType};base64,{$logoBase64}' class='logo-image' alt='{$facility_info['name']} Logo' width='400px'/></div>";
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
                        <td><strong>Name:</strong> " . htmlspecialchars($patient['name']) . "</td>
                        <td><strong>Gender:</strong> " . htmlspecialchars($patient['gender']) . "</td>
                    </tr>
                    <tr>
                        <td><strong>Date of Birth:</strong> " . htmlspecialchars($patient['dob']) . "</td>
                        <td><strong>Report Dispatch Date:</strong> " . date('Y-m-d') . "</td>
                    </tr>
                </table>
            </div>

            <h2>Investigation Results</h2>";

        foreach ($resultsArray as $result) {
            $html_body .= "
            <div class='result-section'>
                <table style='width:100%; margin-bottom:10px;'>
                    <tr>
                        <td><strong>Date:</strong> " . htmlspecialchars($result['date']) . "</td>
                        <td><strong>Staff:</strong> " . htmlspecialchars($result['staff']) . "</td>
                    </tr>
                </table>
                <div class='result-content'>" . $result['result_html'] . "</div>
            </div>";
        }

        $html_body .= "
            <div class='footer'>
                <p>This report was generated by " . htmlspecialchars($facility_info['name']) . "</p>
            </div>
        </body>
        </html>";

        // Generate Email Body
        $html_body_email = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
                .header-table { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee; font-size: 12px; color: #6c757d; }
            </style>
        </head>
        <body>
            <table class='header-table' cellpadding='5' cellspacing='5' border='0'>
                <tr>
                    <td width='50%' align='left'>";
        if ($logoBase64) {
            $html_body_email .= "<img src='cid:hospital_logo' alt='{$facility_info['name']} Logo' width='200px'/>";
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
            </table>
            <hr>
            <p>Kindly find attached Your Investigation results</p>
            <p>You might be required to supply a security key before viewing the contents of the report. Kindly contact the facility if you have questions</p>
            <hr>
            <div class='footer'>
                <p>This report was generated by " . htmlspecialchars($facility_info['name']) . "</p>
                <p><small>This is an automated email. Please do not reply to this message.</small></p>
            </div>
        </body>
        </html>";

        $pdf_filename = 'investigation_results_' . urlencode($patient['name']) . '_' . date('Y-m-d_His') . '.pdf';

        if ($securePdf == 'no') {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html_body);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdf_content = $dompdf->output();
        } else {
            $pdf_content = $this->generateSecurePDF($html_body, $pdfPassword, 'webmedic')['content'];
        }

        return $this->dispatchEmail($patient['email'], $subject, $html_body_email, $pdf_content, $pdf_filename);
    }

    private function dispatchEmail($toEmail, $subject, $htmlBody, $pdfContent = null, $pdfFilename = null)
    {
        $facility_info = $this->facility_info;

        if (!empty($facility_info['smtp_host']) && !empty($facility_info['smtp_username']) && !empty($facility_info['smtp_password'])) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $facility_info['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $facility_info['smtp_username'];
                $mail->Password = $facility_info['smtp_password'];
                $mail->SMTPSecure = ($facility_info['smtp_encryption']) ? $facility_info['smtp_encryption'] : 'tls';
                $mail->Port = ($facility_info['smtp_port']) ? $facility_info['smtp_port'] : 587;

                $mail->setFrom($facility_info['smtp_username'], $facility_info['name']);
                $mail->addAddress($toEmail);

                if ($pdfContent) {
                    $mail->addStringAttachment($pdfContent, $pdfFilename, 'base64', 'application/pdf');
                }

                $logoPath = __DIR__ . '/../img/logo.png';
                if (file_exists($logoPath)) {
                    $mail->addEmbeddedImage($logoPath, 'hospital_logo');
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->AltBody = strip_tags(str_replace(['<br>', '</div>'], "\n", $htmlBody));

                $mail->send();
                return ['success' => true, 'message' => 'Email sent via SMTP'];
            } catch (Exception $e) {
                error_log("SMTP email failed: " . $mail->ErrorInfo);
                throw new Exception("Email sending failed: " . $mail->ErrorInfo);
            }
        } else {
            // Fallback to mail()
            $boundary = md5(time());
            $headers = array(
                'MIME-Version: 1.0',
                'From: ' . $facility_info['name'] . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
                'Reply-To: noreply@' . $_SERVER['HTTP_HOST'],
                'X-Mailer: PHP/' . phpversion(),
                'Content-Type: multipart/mixed; boundary=' . $boundary
            );

            $message = "--" . $boundary . "\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($htmlBody)) . "\r\n";

            if ($pdfContent) {
                $message .= "--" . $boundary . "\r\n";
                $message .= "Content-Type: application/pdf; name=\"" . $pdfFilename . "\"\r\n";
                $message .= "Content-Disposition: attachment; filename=\"" . $pdfFilename . "\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $message .= chunk_split(base64_encode($pdfContent)) . "\r\n";
            }
            $message .= "--" . $boundary . "--";

            $sent = mail($toEmail, $subject, $message, implode("\r\n", $headers));
            if ($sent) {
                return ['success' => true, 'message' => 'Email sent via mail()'];
            } else {
                throw new Exception("Failed to send email via mail() function");
            }
        }
    }

    private function generateSecurePDF($html_body, $user_password, $owner_password = null)
    {
        $options = new Options();
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html_body);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $temp_pdf = tempnam(sys_get_temp_dir(), 'secure_pdf') . '.pdf';
        file_put_contents($temp_pdf, $dompdf->output());

        $pdf = new \FPDI();
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetCreator('Webmedic HMS');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Secure Investigation Report');
        $pdf->SetSubject('Confidential Report');

        $page_count = $pdf->setSourceFile($temp_pdf);
        for ($i = 1; $i <= $page_count; $i++) {
            $pdf->AddPage();
            $tplId = $pdf->importPage($i);
            $pdf->useTemplate($tplId);
        }

        if ($owner_password === null) {
            $owner_password = md5($user_password);
        }

        $pdf->SetProtection(
            array('print', 'copy', 'modify'),
            $user_password,
            $owner_password,
            0,
            'RC4-128'
        );

        $pdf_filename = 'secure_investigation_results_' . date('Y-m-d_His') . '.pdf';
        $pdf_content = $pdf->Output($pdf_filename, 'S');
        unlink($temp_pdf);

        return [
            'content' => $pdf_content,
            'filename' => $pdf_filename
        ];
    }
}
