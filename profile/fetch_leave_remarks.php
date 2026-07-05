<?php
include("../Connections/Conn.php");
// new code
if (isset($_POST['request_id'])) {
    //sleep(10);
    $request_id = $_POST['request_id'];
    $stmt = $db->prepare("SELECT * FROM hrlvapply WHERE sn = ?");
    $stmt->execute([$request_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    //echo $request_id;
    echo json_encode($data);
}
if(isset($_POST['ECode']) && isset($_POST['leave_type'])){
    $year = $_POST['year'];
    $leave_type = $_POST['leave_type'];
    $ECode = $_POST['ECode'];
    $start_date = $_POST['start_date'];
	
	
	$response = array( 
    'status' => 0, 
	'days' =>'',
	'message' =>''
	); 
	
	
    $stmt = $db->prepare("SELECT p.*, p.days as days_taken, l.days as total_days from hrlvapply p inner join hrlv l on p.type_leave = l.leave_type where p.year=? and p.type_leave=? and p.ECode=?");
    $stmt->execute([$year, $leave_type, $ECode]);
			$apply_status='';
		$days_spent=0;
		$approve=0;	$pending=0; $finish=0;
	while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
	//	$total_days=$data['total_days'];
		
		if (strtoupper($data['status']) =='APPROVE'){
			$approve=$approve+1;
			$days_spent=$days_spent+$data['days_taken'];
		}
		
		if (strtoupper($data['status']) =='PENDING'){
			$pending=$pending+1;
			//$days_spent=$days_spent+$data['days'];
		}	
		
		if (strtoupper($data['status']) =='FINISH'){
			$finish=$finish+1;
			$days_spent=$days_spent+$data['days_taken'];
		}	
		
	}
	
	
$stmt = $db->prepare("SELECT days,apply_type from hrlv where leave_type=?");
$stmt->execute([$leave_type]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$total_days=$data['days'];
$apply_type=$data['apply_type'];
	
$bal=$total_days-$days_spent;
	
	if($bal<=0){
		/// error ///	
		$response['message']='Invalid Request - No Days Available!';
		$response['days']='0';
		$response['status']='1';
		echo  json_encode($response);
		
	}else{
		if($pending>=1){
			
			$response['message']='Request Already Exist!';
			$response['days']='0';
			$response['status']='1';
			echo  json_encode($response); 
		}else{
			$response['message']='';
			$response['days']=$bal;
			$response['status']='0';
			echo  json_encode($response); 
		}
		
	}
    
}
// new code
