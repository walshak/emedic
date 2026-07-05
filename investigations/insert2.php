<?php  include("../Connections/Conn.php");?>

<?php
session_start();

if($_POST["MM_update"]=='add_discount_insert'){

	$setdate=date("Y-m-d");

	if($_POST['service_type']=='All Services'){
			$status='On-going';
		}else{
			$status='Pending';
		}
	
 $history=$_POST['discount_charge'] . ' - ' . $_POST['mode'] . ' = ' . $_POST['mode_value'] . ' - Duration: ' . $_POST['how_long'] . ' - Count: ' . $_POST['specify_count'] . ' - Service Type: ' . $_POST['service_type'];
 $sql = "INSERT INTO patient_discount (individual_group, individual_group_no, individual_group_name, discount_charge, percentage_flat, percentage_flat_value, duration, specify_count, apply_to_services, services_items, count_bal, setby, history, status_date, status) 
 VALUES (:individual_group, :individual_group_no, :individual_group_name, :discount_charge, :percentage_flat, :percentage_flat_value, :duration, :specify_count, :apply_to_services, :services_items, :count_bal, :setby, :history, :status_date, :status)";

$stmt = $db->prepare($sql);
$stmt->bindParam(':individual_group', 'individual', PDO::PARAM_STR);
$stmt->bindParam(':individual_group_no', $_POST['emr'], PDO::PARAM_STR);
$stmt->bindParam(':individual_group_name', $_POST['patient_name'], PDO::PARAM_STR);
$stmt->bindParam(':discount_charge', $_POST['discount_charge'], PDO::PARAM_STR);
$stmt->bindParam(':percentage_flat', $_POST['mode'], PDO::PARAM_STR);
$stmt->bindParam(':percentage_flat_value', $_POST['mode_value'], PDO::PARAM_STR);
$stmt->bindParam(':duration', $_POST['how_long'], PDO::PARAM_STR);
$stmt->bindParam(':specify_count', $_POST['specify_count'], PDO::PARAM_STR);
$stmt->bindParam(':apply_to_services', $_POST['service_type'], PDO::PARAM_STR);
$stmt->bindParam(':services_items', '', PDO::PARAM_STR); // Assuming services_items is a string column
$stmt->bindParam(':count_bal', $_POST['specify_count'], PDO::PARAM_STR);
$stmt->bindParam(':setby', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->bindParam(':history', $history, PDO::PARAM_STR);
$stmt->bindParam(':status_date', $setdate, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);

$stmt->execute();



}

?>




