<?php
require("class.PHPMailer.php");

$mail = new PHPMailer();

$mail->IsSMTP();                                      // set mailer to use SMTP
$mail->Host = "mail.bitsysnethosting.com";  // specify main and backup server
$mail->SMTPAuth = true;     // turn on SMTP authentication
$mail->Username = "cpes@cpes.futminna.edu.ng";  // SMTP username
$mail->Password = "cpes123cpes"; // SMTP password

$mail->From = "from@example.com";
$mail->FromName = "Mailer";
$mail->AddAddress("kenny@futminna.edu.ng", "Josh Adams");
$mail->AddReplyTo("info@example.com", "Information");


$mail->Subject = "Here is the subject";
$mail->Body    = "This is the HTML message body <b>in bold!</b>";
$mail->AltBody = "This is the body in plain text for non-HTML mail clients";

if(!$mail->Send())
{
   echo "Message could not be sent. <p>";
   echo "Mailer Error: " . $mail->ErrorInfo;
   exit;
}

echo "Message has been sent";
?>