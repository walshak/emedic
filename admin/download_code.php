<?php

require_once('../Connections/Conn.php');


	
//$result = mysqli_query($db, "SELECT * FROM temp_claim", MYSQLI_USE_RESULT);
$date = date('d M');

header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=\"export_table_$date.csv\"");
header("Content-Transfer-Encoding: binary");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


if(isset($_POST['apply_rpt2'])){
	
		$stt=$db->prepare("SELECT * FROM temp_claim2");
		$stt->execute(); 
		$output = fopen('php://output', 'w');
	
		fputcsv($output, array('-','-','-','-','-','-','-','-','-'));

		while($roww=$stt->fetch(PDO::FETCH_ASSOC))
    {
    fputcsv($output, $roww);
    }

fclose($output);
	
}else{
	
	$stt=$db->prepare("SELECT * FROM temp_claim");
	$stt->execute(); 
$output = fopen('php://output', 'w');
	fputcsv($output, array('Hospital','Drug Name','Qty','Claim','Interest','Entered/Disp by','Date'));

		while($roww=$stt->fetch(PDO::FETCH_ASSOC))
    {
    fputcsv($output, $roww);
    }

fclose($output);


}



?>