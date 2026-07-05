<?php  

 $table="";
 $CurDateHR= date("Y-m-d H:i:s");
// Set values for parameters
$task = 'auth_code';
$status = 'active';
$count = 0; // Assuming count is initialized to 0 or defined elsewhere

 $staff_log=$_SESSION['username'];
 
 	$stmt=$db->query("SELECT * FROM check_list WHERE task='auth_code' and staff_log='$staff_log'");
		if($stmt->rowCount()==0){

// Assuming $db is your PDO database connection object

$insertSQL = "INSERT INTO check_list (task, cur_date, next_update_date, status, count, staff_log) 
              VALUES (:task, :cur_date, :next_update_date, :status, :count, :staff_log)";

$stmt = $db->prepare($insertSQL);

// Bind parameters
$stmt->bindParam(':task', $task, PDO::PARAM_STR);
$stmt->bindParam(':cur_date', $CurDateHR, PDO::PARAM_STR);
$stmt->bindParam(':next_update_date', $CurDateHR, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':count', $count, PDO::PARAM_INT); // Assuming count is an integer
$stmt->bindParam(':staff_log', $_SESSION['username'], PDO::PARAM_STR);



$stmt->execute();

}



$stmt=$db->query("SELECT * FROM check_list WHERE task='auth_code' and next_update_date<='$CurDateHR' and staff_log='$staff_log'");
		if($stmt->rowCount()>0){

 $m=date("m"); 
 $y=date("Y");
 
		$stmt_chk=$db->query("SELECT patient_name,hospital_no,services_name,auth_code,ap_date_time FROM apptm where MONTH(date_ap)='$m' and YEAR(date_ap)='$y' and (insurance='PHIS' or insurance='NHIS') and ap_type='2'");		
	if($stmt_chk->rowCount()>0){

	while($row=$stmt_chk->fetch(PDO::FETCH_ASSOC)) {
		
		$patient_name=$row['patient_name'];
		$hospital_no=$row['hospital_no'];
		$services_name=$row['services_name'];
		$auth_code=$row['auth_code'];
		
			if (strlen($auth_code)<=2){
		$ap_date_time=date("d,M y",strtotime($row['ap_date_time']));
				$auth_table.="<tr><td>$hospital_no</td><td>$patient_name</td><td>$services_name</td><td>$ap_date_time</td></tr>";
			}
							}
	}

			/// add hour 
	$nxt_due_date = date("Y-m-d H:i:s",strtotime($CurDateHR." +2 hours"));
$updateSQL2 ="UPDATE check_list SET next_update_date='$nxt_due_date',count=count+1 WHERE task='auth_code' and staff_log='$staff_log'";
				$db->query($updateSQL2);
	

 } else{
	$auth_table='';
}

?>
                      
