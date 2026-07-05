 
<?php require_once('../Connections/Conn.php');
$currentMonthYear = date('m_Y'); // Format: MM_YYYY
$tableName = "drug_charts_inven_" . $currentMonthYear;
$tableName = "drug_charts_inven";
?>
  <?php include("../inc/footer_scripts.php"); ?> 
<script>
		window.page_num = 1;
	window.month_year = null;
	window.adm_id = "<?= $adm_id;?>"

	function chart_by_adm(admn){
		window.adm_id = admn
		chart_type()
	}
		
	
	function chart_type(month_year=window.month_year, page=window.page_num, drug_name){
			window.page_num = page;
			window.month_year = month_year;
		var url_file=document.getElementById("chart_type_id").value; 
		var hospital_no=document.getElementById("hospital_no").value; 
		var appointment_number=document.getElementById("appointment_number").value; 
			
		if(url_file!=''){	
			$("#div_form_chart").show();
				toastr.success('Wait...', 'Waiting .. busy', {timeOut: 5000 });
			$.ajax({
				url: url_file,
				method: "POST",
				data: { url_file,hospital_no, appointment_number, month_year:window.month_year, 
					page: window.page_num, drug_name, adm_id:window.adm_id},
				success: function(response) {
				  $("#div_form_chart").html(response);
				toastr.clear();
				},
				error: function(err){console.log(err)}
			});
		
		}else{
			  	$("#div_form_chart").hide();
		}
			
	}
</script>

<?php


if(isset($_GET['del_drug'])){
	$sn = $_GET['del_drug'];
	  $delete = $db->prepare("DELETE FROM drug_charts WHERE sn = ?");
    $deleted = $delete->execute(array($sn));
	?>
	<script>
		chart_type()
	</script>
	<?php
}

if(isset($_GET['del_drug_time'])){
	$sn = $_GET['del_drug_time'];
	  $delete = $db->prepare("DELETE FROM drug_chart_timing WHERE id = ?");
    $deleted = $delete->execute(array($sn));
}

if(isset($_GET['del_drug_time_taken'])){
	$sn = $_GET['del_drug_time_taken'];
	  $delete = $db->prepare("DELETE FROM $tableName WHERE sn = ?");
    $deleted = $delete->execute(array($sn));
	?>
	<script>
		chart_type()
	</script>
	<?php
}

if(isset($_GET['resume_drug'])){
	$sn = $_GET['resume_drug'];
	$hosp_no = $_GET['hosp_no'];
	$remarks_ = 'On-going';
	$remarks_2 = 'Chart Resumed';
            $stmt = $db->prepare("UPDATE drug_charts SET remarks=:remarks,status=:status,discontinued_by=:discontinued_by WHERE sn = :drug_chart_id AND hospital_no = :hospital_no ");
            $stmt->bindParam(':remarks', $remarks_2, PDO::PARAM_STR);
            $stmt->bindParam(':status', $remarks_, PDO::PARAM_STR);
            $stmt->bindParam(':discontinued_by', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt->bindParam(':drug_chart_id', $sn, PDO::PARAM_STR);
            $stmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
            $saved = $stmt->execute();
			?>
	<script>
		chart_type()
	</script>
	<?php
}



if (isset($_POST["add_med"])) {	
			date_default_timezone_set('Africa/Lagos');
			$setdate= date("Y-m-d H:i:s");
			$C_date=date("Y-m-d H:i:s");
			$hosp_no=$_POST['hosp_no'];
			$hospital_no=$_POST['hosp_no'];
			$nDate =$_POST['sDate'];
			$nTime=$_POST['sTime'];
			$starting_time=$nDate . ' ' . $nTime;
	//$nextDueTime=date('h:i:s a',strtotime('+'.$_POST['frequency'].' hour',strtotime($_POST['sTime'])));
	 $reminder_status=1;

	$frequency=$_POST['frequency'];
	
	
	if($frequency=='Daily'){
		$Interval_t='Days';
		$CN_date = date("Y-m-d H:i:s",strtotime($C_date." +1 day"));
	}elseif($frequency=='Start' or $frequency=='PRN' or $frequency=='BD' or $frequency=='NOCTE' or $frequency=='Alternate'){
		$Interval_t='Days';
		$CN_date = date("Y-m-d H:i:s",strtotime($C_date." +1 day"));
	}elseif($frequency=='Weekly'){
		$Interval_t='Weekly';
		$CN_date = date("Y-m-d H:i:s",strtotime($C_date." +1 week"));
	}elseif($frequency=='Monthly'){
		$Interval_t='Monthly';
		$CN_date = date("Y-m-d H:i:s",strtotime($C_date." +1 month"));
	}else{
		$parts=explode(" ",$frequency);
			$hr=$parts[0];
			$hour_name=$parts[1];
			if($hour_name=='Hourly'){
				$Interval_t='Hourly';
				$CN_date = date("Y-m-d H:i:s",strtotime($C_date." +$hr hours"));
			}else{
				$Interval_t='Days';
				$CN_date = date("Y-m-d H:i:s",strtotime($C_date." +1 day"));
			}	
			
	}
	
$stmt =$db->prepare("SELECT * FROM drug_charts 
WHERE hospital_no=:hospital_no and sale_sn=:sale_sn and 
drug_sn=:drug_sn and remarks=:remarks and
sDate=:sDate and sTime=:sTime and
status=:status and date_time=:date_time and
captured_by=:captured_by");
	
$stmt->bindValue(':hospital_no', $hosp_no, PDO::PARAM_STR);
$stmt->bindValue(':sale_sn', $_POST['sale_sn'], PDO::PARAM_STR);
$stmt->bindValue(':drug_sn', $_POST['drug_sn'], PDO::PARAM_STR);
$stmt->bindValue(':remarks', $_POST['remarks'], PDO::PARAM_STR);
$stmt->bindValue(':sDate', $_POST['sDate'], PDO::PARAM_STR);
$stmt->bindValue(':sTime', $_POST['sTime'], PDO::PARAM_STR);
//$stmt->bindValue(':nextDueTime', $CN_date, PDO::PARAM_STR);
$stmt->bindValue(':status', $Ongoing, PDO::PARAM_STR);
$stmt->bindValue(':date_time', $setdate, PDO::PARAM_STR);
$stmt->bindValue(':captured_by', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->execute();
		if($stmt->rowCount()==0){ 
				
			$Ongoing='On-going';
				$sql = $db->prepare("INSERT INTO drug_charts (hospital_no,sale_sn,drug_sn,drug_name,route,frequent,dose,remarks,sDate,sTime,nextDueTime,status,date_time,captured_by, adm_id) VALUES (:hospital_no,:sale_sn,:drug_sn,:drug_name,:route,:frequent,:dose,:remarks,:sDate,:sTime,:nextDueTime,:status,:date_time,:captured_by,:adm_id)");
					$sql->bindParam(':hospital_no',$hosp_no, PDO::PARAM_STR);
					$sql->bindParam(':sale_sn',$_POST['sale_sn'], PDO::PARAM_STR);
					$sql->bindParam(':drug_sn',$_POST['drug_sn'], PDO::PARAM_STR);
					$sql->bindParam(':drug_name',$_POST['drug_name'], PDO::PARAM_STR);
					$sql->bindParam(':route',$_POST['Route'], PDO::PARAM_STR);
					$sql->bindParam(':frequent',$_POST['frequency'], PDO::PARAM_STR);
					$sql->bindParam(':dose',$_POST['dosage'], PDO::PARAM_STR);
					$sql->bindParam(':remarks',$_POST['remarks'], PDO::PARAM_STR);
					$sql->bindParam(':sDate',$_POST['sDate'], PDO::PARAM_STR);
					$sql->bindParam(':sTime',$_POST['sTime'], PDO::PARAM_STR);
					$sql->bindParam(':nextDueTime',$CN_date, PDO::PARAM_STR);
					$sql->bindParam(':status',$Ongoing, PDO::PARAM_STR);
					$sql->bindParam(':date_time',$setdate, PDO::PARAM_STR);
					$sql->bindParam(':captured_by',$_SESSION['fullname'], PDO::PARAM_STR);
					$sql->bindParam(':adm_id',$adm_id, PDO::PARAM_STR);
					$sql->execute();
			}else{
			   $error_status = 1;
           $error_msg = 'error : Already Exist!';
			//exit;
		}
	
if ($sql) {
				$notes='<strong>Given</strong>';
		$sql = $db->prepare("INSERT INTO $tableName (hospital_no,sale_sn,drug_sn,date_given,time_given,notes,captured_by) VALUES (:hospital_no,:sale_sn,:drug_sn,:date_given,:time_given,:notes,:captured_by)");
					$sql->bindParam(':hospital_no',$hosp_no, PDO::PARAM_STR);
					$sql->bindParam(':sale_sn',$_POST['sale_sn'], PDO::PARAM_STR);
					$sql->bindParam(':drug_sn',$_POST['drug_sn'], PDO::PARAM_STR);
					$sql->bindParam(':date_given',$_POST['sDate'], PDO::PARAM_STR);
					$sql->bindParam(':time_given',$_POST['sTime'], PDO::PARAM_STR);
					$sql->bindParam(':notes',$notes, PDO::PARAM_STR);
					$sql->bindParam(':captured_by',$_SESSION['fullname'], PDO::PARAM_STR);
					$sql->execute();
	
       if ($sql) {
		   $error_status = 2;
           $error_msg = 'Success : Drug Chart Was Added Successfully!';
       }
	}
}

if (isset($_POST["add_med_chart"])) {
	
$notes='<strong>'.$_POST['notes'].'</strong>';
	
$stmt =$db->prepare("SELECT * FROM $tableName 
WHERE hospital_no=:hospital_no and sale_sn=:sale_sn and 
drug_sn=:drug_sn and date_given=:date_given and
time_given=:time_given and
notes=:notes and
captured_by=:captured_by");
	
$stmt->bindValue(':hospital_no', $hosp_no, PDO::PARAM_STR);
$stmt->bindValue(':sale_sn', $_POST['sale_sn'], PDO::PARAM_STR);
$stmt->bindValue(':drug_sn', $_POST['drug_sn'], PDO::PARAM_STR);
$stmt->bindValue(':date_given', $_POST['date_given'], PDO::PARAM_STR);
$stmt->bindValue(':time_given', $_POST['time_given'], PDO::PARAM_STR);
$stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
$stmt->bindValue(':captured_by', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->execute();
	
	if($stmt->rowCount()==0){ 
		$sql = $db->prepare("INSERT INTO $tableName(hospital_no,sale_sn,drug_sn,date_given,time_given,notes,captured_by) VALUES (:hospital_no,:sale_sn,:drug_sn,:date_given,:time_given,:notes,:captured_by)");
					$sql->bindParam(':hospital_no',$_POST['hosp_no'], PDO::PARAM_STR);
					$sql->bindParam(':sale_sn',$_POST['sale_sn'], PDO::PARAM_STR);
					$sql->bindParam(':drug_sn',$_POST['drug_sn'], PDO::PARAM_STR);
					$sql->bindParam(':date_given',$_POST['date_given'], PDO::PARAM_STR);
					$sql->bindParam(':time_given',$_POST['time_given'], PDO::PARAM_STR);
					$sql->bindParam(':notes',$notes, PDO::PARAM_STR);
					$sql->bindParam(':captured_by',$_SESSION['fullname'], PDO::PARAM_STR);
					$sql->execute();	
	
		$updateSQL = "UPDATE drug_charts SET status=:status,captured_by=:captured_by,nextDueTime=:nextDueTime WHERE sale_sn=:sale_sn";
$sql = $db->prepare($updateSQL);
$sql->bindParam(':status', $_POST['adm_s'], PDO::PARAM_STR);
$sql->bindParam(':captured_by', $_SESSION['fullname'], PDO::PARAM_STR);
$sql->bindParam(':nextDueTime', $_POST['CN_date'], PDO::PARAM_STR);	
$sql->bindParam(':sale_sn', $_POST['sale_sn'], PDO::PARAM_STR);
$sql->execute();

		
       if ($sql) {
		   $error_status = 2;
           $error_msg = 'Success : Medication Chart Was Added Successfully!';
       }
	}else{
		   $error_status = 1;
           $error_msg = 'error : Already Exist!';
	}
	
}

if (isset($_GET["dv"])) {
	$del=$_GET['dv'];
	
$stmt = $db->query("SELECT sale_sn FROM $tableName WHERE sn='$del'");
					if($stmt->rowCount()==1){
						$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
						$sale_sn=$row_rstSelect['sale_sn'];	
////=======================
						
		$updateSQL = "DELETE FROM drug_charts WHERE sale_sn=:sale_sn";
		$stmt_22 = $db->prepare($updateSQL);
		$stmt_22->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
		$stmt_22->execute();				
///-------------------
						
		$updateSQL = "DELETE FROM $tableName WHERE sn=:sn";
		$stmt_22 = $db->prepare($updateSQL);
		$stmt_22->bindParam(':sn', $del, PDO::PARAM_STR);
		$stmt_22->execute();						
		}
}


if (isset($_GET["cl"])) {
	$sale_sn=$_GET['cl'];
$status='Discontinue';
$updateSQL = "UPDATE drug_charts SET status=:status,captured_by=:status_by_staff WHERE sale_sn=:sale_sn";
$sql = $db->prepare($updateSQL);
$sql->bindParam(':status', $status, PDO::PARAM_STR);
$sql->bindParam(':status_by_staff', $_SESSION['fullname'], PDO::PARAM_STR);
$sql->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
$sql->execute();	
}

if (isset($_GET["fn"])) {
	$sale_sn=$_GET['fn'];

$status='Completed';
$updateSQL = "UPDATE drug_charts SET status=:status,captured_by=:status_by_staff WHERE sale_sn=:sale_sn";
$sql = $db->prepare($updateSQL);
$sql->bindParam(':status', $status, PDO::PARAM_STR);
$sql->bindParam(':status_by_staff', $_SESSION['fullname'], PDO::PARAM_STR);
$sql->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
$sql->execute();	
}


if (isset($_GET["st"])) {
	$sale_sn=$_GET['st'];

	$status='On-going';
$updateSQL = "UPDATE drug_charts SET status=:status,captured_by=:status_by_staff WHERE sale_sn=:sale_sn";
$sql = $db->prepare($updateSQL);
$sql->bindParam(':status', $status, PDO::PARAM_STR);
$sql->bindParam(':status_by_staff', $_SESSION['fullname'], PDO::PARAM_STR);
$sql->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
$sql->execute();
	
}



if (isset($_POST["add_feeding"])) {
	$app_no=$_POST['app_no'];
	$hosp_no=$_POST['hosp_no'];
	$setdate=date('Y-m-d h:i:s a');
$time_date = date('d, M Y', strtotime($_POST['ddate'])). ' - ' . date('h:i:s a', strtotime($_POST['ttime']));

	
$stmt =$db->prepare("SELECT * FROM feedings 
WHERE app_no=:app_no and hospital_no=:hospital_no and 
nature_feed=:nature_feed and quantity_feed=:quantity_feed and
feed_comment=:feed_comment and
time_feeding=:time_feeding and
prepared_by=:prepared_by");
	
$stmt->bindValue(':app_no', $_POST['app_no'], PDO::PARAM_STR);
$stmt->bindValue(':hospital_no', $_POST['hosp_no'], PDO::PARAM_STR);
$stmt->bindValue(':nature_feed', $_POST['nature'], PDO::PARAM_STR);
$stmt->bindValue(':quantity_feed', $_POST['qty'], PDO::PARAM_STR);
$stmt->bindValue(':feed_comment', $_POST['Comment'], PDO::PARAM_STR);
$stmt->bindValue(':time_feeding', $time_date, PDO::PARAM_STR);
$stmt->bindValue(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->execute();
	
	if($stmt->rowCount()==0){ 	
	
		$sql = $db->prepare("INSERT INTO feedings (app_no, hospital_no,nature_feed,quantity_feed,feed_comment,time_feeding,prepared_by,date_entry) 
		VALUES (:app_no, :hospital_no,:nature_feed,:quantity_feed,:feed_comment,:time_feeding,:prepared_by,:date_entry)");
					$sql->bindParam(':app_no',$_POST['app_no'], PDO::PARAM_STR);
					$sql->bindParam(':hospital_no',$_POST['hosp_no'], PDO::PARAM_STR);
					$sql->bindParam(':nature_feed',$_POST['nature'], PDO::PARAM_STR);
					$sql->bindParam(':quantity_feed',$_POST['qty'], PDO::PARAM_STR);
					$sql->bindParam(':feed_comment',$_POST['Comment'], PDO::PARAM_STR);
					$sql->bindParam(':time_feeding',$time_date, PDO::PARAM_STR);
					$sql->bindParam(':prepared_by',$_SESSION['fullname'], PDO::PARAM_STR);
					$sql->bindParam(':date_entry',$time_date, PDO::PARAM_STR);
					$sql->execute();
	
			
       if ($sql) {
		   $error_status = 2;
           $error_msg = 'Success : Data Added Successfully!';
       }else{
		   $error_status = 1;
           $error_msg = 'error : Saving Not Successfully!';
		   
	   }
	}else{
		   $error_status = 1;
           $error_msg = 'error : Already Exist!';
	}
	
}


/* Delete  */ 
if(isset($_GET['fd']))
{
	$del_id=$_GET['fd'];
	$fullname=$_SESSION['fullname'];
	
		$updateSQL = "DELETE FROM feedings WHERE sn='$del_id' and prepared_by='$fullname'";
		$stmt_22 = $db->prepare($updateSQL);
		$stmt_22->bindParam(':sn', $del, PDO::PARAM_STR);
		$stmt_22->execute();
	
}


if(isset($_GET['blood_sugar_chart']))
{
	$del_id=$_GET['blood_sugar_chart'];
	$fullname=$_SESSION['fullname'];
	
		$updateSQL = "DELETE FROM blood_sugar_chart WHERE sn='$del_id' and prepared_by='$fullname'";
		$stmt_22 = $db->prepare($updateSQL);
		$stmt_22->bindParam(':sn', $del, PDO::PARAM_STR);
		$stmt_22->execute();
	
}



if ( isset($_POST["fluids_remarks"])) {
	
	$remarks_=$_POST['remarks_'];	
	$hospital_no_rmk=$_POST['hospital_no_rmk'];
	$one =1;
	$zero=0;
	///
	
$updateSQL = "UPDATE fluidchart SET start_close_status=:one,remarks=:remarks 
WHERE hospital_no=:hospital_no and start_close_status=:zero";
$sql = $db->prepare($updateSQL);
$sql->bindParam(':one', $one, PDO::PARAM_STR);
$sql->bindParam(':remarks', $remarks_, PDO::PARAM_STR);
$sql->bindParam(':hospital_no', $hospital_no_rmk, PDO::PARAM_STR);
$sql->bindParam(':zero', $zero, PDO::PARAM_STR);
$sql->execute();	
	
}




if ( isset($_POST["add_output"])) {
	$app_no=$_POST['app_no'];
 	$hospital_no=$_POST['hosp_no'];
	$entry_date = $_POST['entry_date'];
	$entry_time = $_POST['entry_time'];
	$starting = $_POST['starting'];
	
	
		if(isset($_POST['oral_amount']) and $_POST['oral_amount']!=''){
			
				if($_POST['oral_remark2']!=''){ $oral_remark2 =  ' (' . $_POST['oral_remark2'].')'; }else{$oral_remark2='';}
				
			$fluid_type='Oral';
			$amount = $_POST['oral_amount'];
			$remarks = $_POST['oral_remark'] . $oral_remark2;
			
			$chart = 'in';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}		
		if(isset($_POST['intravenous_amount']) and $_POST['intravenous_amount']!=''){
			
				if($_POST['intravenous_remark2']!=''){ $intravenous_remark2 =  ' (' . $_POST['intravenous_remark2'].')'; }else{$intravenous_remark2='';}
			
			$fluid_type='Intravenous (IV)';
			$amount = $_POST['intravenous_amount'];
			$remarks = $_POST['intravenous_remark'] .$intravenous_remark2;
			
			$chart = 'in';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}	
		if(isset($_POST['tube_amount']) and $_POST['tube_amount']!=''){
			$fluid_type='Tube';
			$amount = $_POST['tube_amount'];
			$remarks = $_POST['tube_remarks'];
			
			$chart = 'in';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}		
		if(isset($_POST['blood_amount']) and $_POST['blood_amount']!=''){
			$fluid_type='Blood';
			$amount = $_POST['blood_amount'];
			$remarks = $_POST['blood_remarks'];
			
			$chart = 'in';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}
		if(isset($_POST['others_amount']) and $_POST['others_amount']!=''){
			//$fluid_type='Blood';
			$fluid_type = $_POST['input_others'];
			$amount = $_POST['others_amount'];
			$remarks = $_POST['others_remark'];
			
			$chart = 'in';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}
	
	
	
		
		if(isset($_POST['urine_amount']) and $_POST['urine_amount']!=''){
			
				if($_POST['urine_remark2']!=''){ $urine_remark2 =  ' (' . $_POST['urine_remark2'].')'; }else{$urine_remark2='';}
			$fluid_type='Urine';
			$amount = $_POST['urine_amount'];
			$remarks = $_POST['urine_remark'] .$urine_remark2;
			
			$chart = 'out';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}		
	
	if(isset($_POST['stool_amount']) and $_POST['stool_amount']!=''){
			
				if($_POST['stool_remark2']!=''){ $stool_remark2 =  ' (' . $_POST['stool_remark2'].')'; }else{$stool_remark2='';}
			$fluid_type='Stool';
			$amount = $_POST['stool_amount'];
			$remarks = $_POST['stool_remark'] .$stool_remark2;
			
			$chart = 'out';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}	
	if(isset($_POST['drainage_amount']) and $_POST['drainage_amount']!=''){
			
				if($_POST['drainage_remark2']!=''){ $drainage_remark2 =  ' (' . $_POST['drainage_remark2'].')'; }else{$drainage_remark2='';}
			$fluid_type='Drainage';
			$amount = $_POST['drainage_amount'];
			$remarks = $_POST['drainage_remark'] .$drainage_remark2;
			
			$chart = 'out';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}	
	if(isset($_POST['vomit_amount']) and $_POST['vomit_amount']!=''){
			
				if($_POST['vomit_remark2']!=''){ $vomit_remark2 =  ' (' . $_POST['vomit_remark2'].')'; }else{$vomit_remark2='';}
			$fluid_type='Vomit';
			$amount = $_POST['vomit_amount'];
			$remarks = $_POST['vomit_remark'] .$vomit_remark2;
			
			$chart = 'out';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}	
	if(isset($_POST['ufgoal_amount']) and $_POST['ufgoal_amount']!=''){
			
			$fluid_type='UF Goal';
			$amount = $_POST['ufgoal_amount'];
			$remarks = $_POST['ufgoal_remark'];
			
			$chart = 'out';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}	
	
	if(isset($_POST['others_output']) and $_POST['others_output']!=''){
			//$fluid_type='Blood';
			$fluid_type = $_POST['others_output'];
			$amount = $_POST['others_amount_output'];
			$remarks = $_POST['others_remark_output'];
			
			$chart = 'out';
			$query = save_input_output_fuilds($db,$fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting);
		}
	

}




function save_input_output_fuilds($db, $fluid_type,$amount,$remarks,$entry_date,$chart,$app_no,$hospital_no,$entry_time,$starting){
	
	
$stmt =$db->prepare("SELECT * FROM fluidchart 
WHERE app_no=:app_no and hospital_no=:hospital_no and 
fluid_type=:fluid_type and amount=:amount and
remark=:remark and
nurse=:nurse and
chart=:chart and
entry_date=:entry_datetime and
entry_time=:entry_time");
	
$stmt->bindValue(':app_no', $app_no, PDO::PARAM_STR);
$stmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmt->bindValue(':fluid_type', $fluid_type, PDO::PARAM_STR);
$stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
$stmt->bindValue(':remark', $remarks, PDO::PARAM_STR);
$stmt->bindValue(':nurse', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->bindValue(':chart', $chart, PDO::PARAM_STR);
$stmt->bindValue(':entry_datetime', $entry_date, PDO::PARAM_STR);
$stmt->bindValue(':entry_time', $entry_time, PDO::PARAM_STR);
$stmt->execute();
	
	if($stmt->rowCount()==0){ 		
	
	    $query = "INSERT INTO fluidchart (app_no,hospital_no,fluid_type, amount, remark, nurse, chart, entry_date,entry_time,start_code) 
		VALUES (:app_no,:hospital_no,:fluid_type, :amount, :remark, :nurse, :chart, :entry_date, :entry_time,:start_code)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':app_no', $app_no);
        $stmt->bindParam(':hospital_no', $hospital_no);
        $stmt->bindParam(':fluid_type', $fluid_type);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':remark', $remarks);
        $stmt->bindParam(':nurse', $_SESSION['fullname']);
        $stmt->bindParam(':chart', $chart);
        $stmt->bindParam(':entry_date', $entry_date);
        $stmt->bindParam(':entry_time', $entry_time); 
		$stmt->bindParam(':start_code', $starting);
	$stmt->execute();
}
}



if(isset($_GET['in_out']))
{
	$del_id=$_GET['in_out'];
	$fullname=$_SESSION['fullname'];
	
		$updateSQL = "DELETE FROM fluidchart WHERE id='$del_id' and nurse='$fullname'";
		$stmt_22 = $db->prepare($updateSQL);
		$stmt_22->bindParam(':sn', $del, PDO::PARAM_STR);
		$stmt_22->execute();
	
}



?>

        <div class="gray-bg">
       
			
		    <div class="row">
            				
				
			<div class="col-sm-4">	
			<div class="form-group">
			<h3 style="color:black; ">Choose Chart Template</h3>
				<select class="form-control" name="chart_type" id="chart_type_id" onChange="chart_type()" style="font-size: 17px; border-color:darkolivegreen; ">
				<option value="">-- Select --</option>
				<option value="../inc/intake_out.php"
						<?php if(isset($_POST["add_intake"]) or isset($_POST["add_output"]) or isset($_GET['in_out']) or isset($_GET['fluid'])){?>
						selected <?php } ?>
						>Fluid Chart - Intake and Output Record</option>
				<option value="../inc/feeding.php"
						<?php if (isset($_POST["add_feeding"]) or isset($_GET["fd"]) or isset($_GET["feeding"])) {?> selected<?php } ?>
						>Feeding Chart</option>
				<option value="../inc/_drug_charts.php" <?php 
						if (isset($_POST["add_med"]) or isset($_POST["add_med_chart"]) 
							or isset($_GET["dv"]) or isset($_GET["cl"]) or isset($_GET["fn"]) or isset($_GET["med_chart"]) 
			  or isset($_GET["st"]) or isset($_GET["del_drug"])or isset($_GET["del_drug_time"])or isset($_GET["del_drug_time_taken"])or isset($_GET["resume_drug"])) { 
						?>selected<?php } ?> >Medication/Drug/Injection Chart</option>				
				<option value="../inc/admission_note.php" <?php 
						if (isset($_GET["adm_note"]) ) { 
						?>selected<?php } ?> >Admission Note</option>
					
				<option value="../inc/oxygen_consumption_chart.php" <?php 
						if (isset($_GET["oxygen"]) ) { 
						?>selected<?php } ?> >Oxygen Consumption Chart</option>
					
				<option value="../inc/blood_sugar_chart.php" <?php 
						if (isset($_GET["blood_sugar"])  or isset($_GET["blood_sugar_chart"])  ) { 
						?>selected<?php } ?> >Blood Sugar</option>
					
			<option value="../inc/seizure_chart.php" <?php 
						if (isset($_GET["seizure_chart"]) ) { 
						?>selected<?php } ?> >Seizure Chart</option>		
					
			<option value="../inc/ward_to_icu_chart.php" <?php 
						if (isset($_GET["ward_icu"]) ) { 
						?>selected<?php } ?> >Ward to ICU</option>		
				</select> 
			</div> 
			<input type="hidden" value="<?= $hospital_no; ?> " id="hospital_no">
			<input type="hidden" value="<?= $appointment_number; ?> " id="appointment_number">
			</div> 
			<div class="col-sm-4"></div> 
			<div class="col-sm-4">
				<br>
				<a href="../gen_services.php?hosp_no=<?= base64_encode(base64_encode($hospital_no.'||'.$appointment_number)); ?>"  class="btn btn-success" style="color: white; font-size: 15px; "><i class="fa fa-plus"></i>&nbsp;Other Documentation</a></div> 
					
				
			</div>             
		</div>
	<div id="div_form_chart"></div>	

	<div class="modal inmodal fade" id="admin_drug_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog" >
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Administer Drug</h4>
				</div>
					<div class="modal-body" id="admin_drug"> 
						
    <form action="patient.php?hosp_no=" method="POST">
                            
							<div class="form_sep">   
									<label for="reg_input_no" class="req">Timing </label>
									<select name="allcate_programmes[]" data-placeholder=" --Select Course  --" class="chosen-select" multiple="" 
									style="width: 350px;" tabindex="-1" id="drug_chart_timing_select"> 
									<option value="" selected>--Select--</option>
										
										<input type="checkbox"> Daily
										<input type="checkbox"> Daily
										<input type="checkbox">Three Times Daily
										<input type="checkbox">Four Times Daily
										<input type="checkbox">Every 6 Hours
										<input type="checkbox">Every 8 Hours
										<input type="checkbox">Every 12 Hours
										<input type="checkbox"> Weekly
									</select> 
									
									
									</div>                    
										
							</form> 
					</div>
			</div>
		</div>
	</div>
<div class="modal inmodal fade" id="admin_drug_chart_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="width: 900px;" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Administer Drug Chart</h4>
			</div>
                <div class="modal-body" id="admin_drug_chart">  
                </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="administer_now_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xs"  >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Administer Drug</h4>
			</div>
                <div class="modal-body" id="admin_drug_chart">  
					<label for="timeGiven">Time Given</label>
					<input type="time" name="timeGeven" class="form-control" value="<?= date('h:i');?>" id="timeGeven"/>
                </div>
			
                <div class="modal-body" id="admin_drug_chart">  
					<label for="timeGiven">Dasage</label>
					<input type="text" name="dosage_given"  class="form-control" value="" id="dosage_given"/>
                </div>			
			
			
				<button type="submit" class="btn btn-primary" name="administerDrug" id="administerDrug" style="display: block; width:100%;">Save</button>  
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="discontinue_drug_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xs"  >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Dis-continue Drug</h4>
			</div>
                <div class="modal-body" id="admin_drug_chart">  
					<label for="timeGiven">Reason why you want to discontinue <span id="drugToDiscontinue" class="text-danger"></span>?</label>
					<input type="text" name="reasonToDiscontinue" class="form-control" id="reasonToDiscontinue"k placeholder="Write your reasons here..."/>
                </div>
				<button type="submit" class="btn btn-primary" name="discontinueDrug" id="discontinueDrug" style="display: block; width:100%;">Discontinue Drug</button>  
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="remarks_drug_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xs"  >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Enter Remarks for this Medication</h4>
			</div>
                <div class="modal-body" id="admin_drug_chart">  
					<label for="timeGiven"><span id="" class="text-danger"></span>Enter Remarks</label>
					<input type="text" name="remarks" class="form-control" id="remarks" placeholder=""/>
                </div>
				<button type="submit" class="btn btn-primary" name="save_remarks" id="save_remarks" style="display: block; width:100%;">Save Remarks</button>  
        </div>
    </div>
</div>



 
    <script>
	<?php if (isset($_POST["add_med"]) or isset($_POST["add_med_chart"])
			or isset($_GET["dv"]) or isset($_GET["cl"]) or isset($_GET["fn"]) or isset($_GET["fd"]) 
			  or isset($_GET["st"]) or isset($_POST["add_feeding"]) or
			  isset($_GET["feeding"]) or isset($_GET["med_chart"]) or 
			  isset($_POST["add_intake"]) or isset($_POST["add_output"]) or isset($_GET['in_out']) or 
			  isset($_GET['fluid']) or isset($_GET['adm_note']) or isset($_GET['oxygen']) or isset($_POST["fluids_remarks"]) or
			  isset($_GET['ward_icu']) or isset($_GET['seizure_chart']) or isset($_GET['blood_sugar'])or isset($_GET['del_drug'])or isset($_GET['del_drug_time'])or isset($_GET['del_drug_time_taken'])or isset($_GET['resume_drug'])) { ?>
			chart_type();
	<?php } ?>	
		


	
	$(document).on('click', '.administer_drug', function(){  
           let hospital_no = $(this).attr("hosp"); 
           let drug_chart = $(this).attr("drug_chart"); 
			  		
	  if(hospital_no != '') {  
                $.ajax({  
                     url:"administer_drug.php",  
                     method:"POST",  
                     data:{loadDrugChartTiming:true, drug_chart:drug_chart, hospital_no:hospital_no}, 
                     success:function(data){ 
					 $('.modal-title').text('Administer Medications'); 
                          $('#admin_drug').html(data);  
                          $('#admin_drug_modal').modal('show');  
                     }  
                });  
           }          
      }); 

	  
	$(document).on('click', '#newDrugChartting', function(){  
           var hospital_no = $(this).attr("hosp"); 
	  if(hospital_no != '') {  
                $.ajax({  
                     url:"administer_drug.php",  
                     method:"POST",  
                     data:{loadNewDrugChart:true, hospital_no:hospital_no}, 
                     success:function(data){ 
					  
					 $('.modal-title').text('New Medication Chart'); 
                          $('#admin_drug').html(data);  
                          $('#admin_drug_modal').modal('show');  
						  $("#drug_chart_timing_select").chosen();
						  $("#drug_chart_timing_select").empty();
						  $("#drug_chart_timing_select").append(data);
						  $('#drug_chart_timing_select').trigger('chosen:updated');
                     }  
                });  
           }          
      });

	  
	  $(document).on('click', '#saveDrugChart', function(){  
           const drugToChart = $("#drugToChart").val(); 
           const toStartDate = $("#toStartDate").val(); 
           const hospital_no =  $("#drugToChartHospital_no").val();  

	  		if(hospital_no != '') {  
                $.ajax({ url:"administer_drug.php",  
                     method:"POST",  
                     data:{
						saveDrugChart:true, hospital_no:hospital_no, 
						drugToChart:drugToChart, toStartDate:toStartDate
					}, success:function(data){ 
						toastr.success('',data.message);
						chart_type()
                     }  
                });  
           }          
      });


	  $(document).on('click', '#saveDrugChartTiming', function(){  
		   const hospital_no = $(this).attr("hosp"); 
		   const drug_chart = $(this).attr("drug_chart");  
		   const drugChatTiming = $("#drugChatTiming").val(); 

	  		if(drugChatTiming != '') {  
                $.ajax({ url:"administer_drug.php",  
                     method:"POST",  
                     data:{
						saveDrugChartTiming:true, hospital_no:hospital_no, 
						drug_chart:drug_chart, drugChatTiming:drugChatTiming
					}, success:function(data){ 
						toastr.success('', data.message);
						chart_type()
						$("#drugChatTiming").val(""); 
						// $('#admin_drug_modal').modal('hide');  
                     }  
                });  
           }          
      });
	  
	  var drug_chart = null; 
	  var chart_timing = null;  
	  var sale_sn = null;  
	  var drug_sn = null;  
	  var hosp = null;  
	  var day = null;  

	  $(document).on('click', '.administer_drug_btn', function(){
		chart_timing = $(this).attr("chart_timing"); 
		drug_chart = $(this).attr("drug_chart");    
		drug_sn = $(this).attr("drug_sn");    
		sale_sn = $(this).attr("sale_sn");    
		hosp = $(this).attr("hosp");    
		day = $(this).attr("day");    
		dosage = $(this).attr("dosage");  
		document.getElementById("dosage_given").value = $(this).attr("dosage");;  
		$('#administer_now_modal').modal('show'); 
		
	  });

	  $(document).on('click', '#administerDrug', function(){
		var dosage_given = document.getElementById("dosage_given").value;  
		const timeGeven = $("#timeGeven").val(); 
		const administerDrug = true;
			$.ajax({ url:"administer_drug.php",  
                     method:"POST",  
                     data:{ administerDrug, chart_timing, drug_chart, timeGeven, hosp, drug_sn, sale_sn, day, dosage_given}
					 , success:function(data){ 
						toastr.success('', data.message);
						chart_type();
						$('#administer_now_modal').modal('hide'); 
                     }  
                });
	  });

	  $(document).on('change', '#filterByMonth', function(){  
		const month_year = $(this).val(); 
		const [year, month] = month_year.split('-');

		//alert(month_year);
		chart_type(month_year)
		
	  });

	  


	//   $(document).on('click', '#newDrugChartting', function(){  
	// 	$('#admin_drug_chart_modal').modal('show');  
	//   });	
		
      $(document).on('click', '.administer_drug_chart', function(){  
           var drug_details_chart = $(this).attr("id"); 
		     // var res = note_id.split("__"); 
			  
		             if(drug_details_chart != '')  
           {  
                $.ajax({  
                     url:"administer_drug.php",  
                     method:"POST",  
                     data:{drug_details_chart:drug_details_chart}, 
                     success:function(data){ 
					  
					 $('.modal-title').text('Administer Medications'); 
                          $('#admin_drug_chart').html(data);  
                          $('#admin_drug_chart_modal').modal('show');  
                     }  
                });  
           }            
      });	

	  	
	  $(document).on('click', '.discontinueDrugBtn', function(){  
		drug_chart = $(this).attr("drug_chart");      
		hosp = $(this).attr("hosp");
		var drug_name = $(this).attr("drug_name");
		$('#drugToDiscontinue').html(drug_name); 
		$('#discontinue_drug_modal').modal('show'); 
		          
      });		  
		
		

		
		
		
		
	  
	  $(document).on('click', '#discontinueDrug', function(){  
		const reasonToDiscontinue = $("#reasonToDiscontinue").val(); 
		if(reasonToDiscontinue.trim() != ''){
			const discontinueDrug = true;
				$.ajax({ url:"administer_drug.php",  
						method:"POST",  
						data:{ discontinueDrug, reasonToDiscontinue, drug_chart, hosp, hosp}
						, success:function(data){ 
							toastr.success('', data.message);
							chart_type();
							$('#discontinue_drug_modal').modal('hide'); 
						}  
					});
		}
	
	  });	  
		
	 


	$(document).on('click', '.enter_remark_btn', function(){  
		drug_chart = $(this).attr("drug_chart");      
		hosp = $(this).attr("hosp");
		remarks = $(this).attr("remarks");
		document.getElementById("remarks").value= remarks; 
		var drug_name = $(this).attr("drug_name");
		$('#remarks_drug_modal').modal('show'); 
		          
      });	
		
	$(document).on('click', '#save_remarks', function(){ 

		const remarks = $("#remarks").val(); 
		if(remarks.trim() != ''){
			const drug_remarks = true;
			

			
				$.ajax({ url:"administer_drug.php",  
						method:"POST",  
						data:{ drug_remarks, remarks, drug_chart, hosp, hosp}
						, success:function(data){ 

							
							toastr.success('', data.message);
							chart_type();
							$('#remarks_drug_modal').modal('hide'); 
						}  
					});
		}
	
	  });
		
		
    </script>

