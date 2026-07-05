<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'eSender/vendor/phpmailer/src/Exception.php';
require 'eSender/vendor/phpmailer/src/PHPMailer.php';
require 'eSender/vendor/phpmailer/src/SMTP.php';

    //Load composer's autoloader


    $email = 'clickhab@yahoo.com';
    $subject = 'hello';
    $message = 'happy day ahead...............';


    $mail = new PHPMailer(true);                            
    try {
        //Server settings
        $mail->isSMTP();                                     
    $mail->Host       = 'smtp.mailtrap.io';                     //Set the SMTP server to send through
        $mail->SMTPAuth = true;                             
       // $mail->Username = 'plapolyportal@plapolyportal.com.ng';     
     //   $mail->Username = 'custechportal1@gmail.com';     
      //  $mail->Password = 'CUSTECH@ePortal1';    
$mail->Username   = '35f68f920342c4';                     //SMTP username
    $mail->Password   = 'a7087672797f8b';                               //SMTP password
		
      //  $mail->Password = 'plapolyportal.com.ng';             
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );                         
        $mail->SMTPSecure = 'ssl';                           
        $mail->Port = 465;                                   

        //Send Email
      ///  $mail->setFrom('plapolyportal@plapolyportal.com.ng');
        $mail->setFrom('custechportal1@gmail.com');
        
        //Recipients
        $mail->addAddress($email);              
        $mail->addReplyTo('no-reply@gmail.com');
        
        //Content
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
		
      $result = 'Message has been sent';
	   $result_status=1;
    } catch (Exception $e) {
	   $result= 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
		 $result_status=0;
    }
echo $result;


