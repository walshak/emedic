<?php
if (isset($_POST["upload_pass"])) {
	$ecode=$_POST["pass_code"];
	$upload_type=$_POST["upload_type"];
	$filename=$upload_type .'_'.$_POST["pass_code"];

define('UPLOADPATH', '../uploads/staff/');
  define('MAXFILESIZE', 300000);  

	 
$file_foto_type = $_FILES['file_foto']['type'];
$file_foto_size = $_FILES['file_foto']['size'];
$TempSrc= $_FILES['file_foto']['tmp_name']; // Temp name of image file stored in PHP tmp folder
	
//exit;
		if (($file_foto_type == 'image/jpeg')|| ($file_foto_type == 'image/jpg')) {
					$pixid = $filename.'.'.'jpg';
					$target = UPLOADPATH . $pixid; //generate the destination path
		   	 	  move_uploaded_file($_FILES['file_foto']['tmp_name'], $target);
			}

header("location:index.php?Add&ECode=$ecode");
}

?>
