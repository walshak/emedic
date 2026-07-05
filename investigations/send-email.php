<?php

if(isset($_POST['text2'])){
	
	$subject='hello';
	$message = $_POST['text2'];
	$email = $_POST['email'];
	
	///$email='clickhab@gmail.com';
	include('../testMail/index.php');
	
	if($result_status==1){
		echo 'Message has been sent';
	}else{
		echo 'Unable to send message!';
	}  
}

?>