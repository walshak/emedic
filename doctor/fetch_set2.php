 <?php include("../Connections/Conn.php");?>

<?php
session_start();



 


 if(isset($_POST["lab_test_no"])) {
	$lb=$_POST["lab_test_no"];

	 
	 $stmt2=$db->query("SELECT test_id,test_name,approved_by,lab_cat FROM lab_manage WHERE labrequest_no='$lb' and data_capture_status='approve' and section='Laboratory'");
					$row_test=$stmt2->fetch(PDO::FETCH_ASSOC);
			
							$labrequest_no=$lb; 
								$test_id=$row_test['test_id']; 
						$test_name=$row_test['test_name']; 
					$approved_by=$row_test['approved_by'];
						$lab_cat=$row_test['lab_cat'];
					$labrequest_no=$lb;
					
					//// get field type 		
	$stmtget=$db->query("SELECT field_type,sn FROM lab_scan_fields WHERE test_no='$test_id'");
	 				$row_get=$stmtget->fetch(PDO::FETCH_ASSOC);
						  $field_type=$row_get['field_type'];
							 	//$field_no=$row_test['sn'];

		$stmt23=$db->query("SELECT * FROM lab_result WHERE lab_no='$lb' and test_no='$test_id'");
		$rowxx=$stmt23->fetch(PDO::FETCH_ASSOC);	
								 
		$test_name=$rowxx['test_name']; 
		$comment=$rowxx['comment'];
		$notes=$rowxx['notes'];
		$result_date=$rowxx['result_date'];
		$lab_sci_name=$rowxx['lab_sci_name']; 
		$lab_sci_speciality=$rowxx['lab_sci_speciality'];
		$spm=$rowxx['specimen_collected']; 
		$test_result=$rowxx['field_value']; 
		$field_name=$rowxx['field_name']; 
		$field_ref=$rowxx['field_ref']; 
	 
										
													 
								  ?>

      	<?php include("printlab_bdy.php");?>
        <?php ///include("signatures.php");	 
	 
 }



if(isset($_POST["lab_test_no_radio"])) {
	$lb=$_POST["lab_test_no_radio"];


	     $stmt=$db->query("SELECT lab.*, e.phone, e.insurance,e.gender,e.nationality,e.addr,e.dob FROM lab_manage as lab inner join enrollee as e on e.hospital_no=lab.patient WHERE lab.labrequest_no='$lb'");
			if($stmt->rowCount()>0){
					$roww=$stmt->fetch(PDO::FETCH_ASSOC);
					$nat=$roww['nationality'];
					$gender=$roww['gender'];
					$addr=$roww['addr'];
					$insurance=$roww['insurance'];	
					$phone=$roww['phone'];
					$birthDate=$roww['dob'];
			}

?>
	

<table cellpadding="5" cellspacing="5" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                    <tr>
                             <td><strong>Investigation Requested</strong>:</td>
                             <td><?php echo $roww['test_name']; ?></td>
<td><strong>Requested Date</strong>:</td>
<td>&nbsp;<?php echo date('d-m-Y',strtotime($roww['request_date']));?></td> 

<td><strong>Result Date</strong>:</td>
<td>&nbsp;<?php echo date('d-m-Y',strtotime($roww['result_date']));?></td>                             
                       
                                    </tr>
                                    </table>                                 
                                   
                                </div>

                                
                            </div>

                      <div class="table-responsive m-t">
                       <body oncopy="return false" oncut="return false" onpaste="return false">
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:16px; font-family:Arial, Helvetica, sans-serif;">
                                    <tbody>
        <tr><td style="border-bottom: 1px solid #000; border-top: 1px solid #000; border-left:none; font-size:16px; font-family:Arial, Helvetica, sans-serif;">
		<div align="left"><?php echo $roww['result_note'] ?></div> </td>
                </tr>
                
                </tbody>
                </table>
                 </body>   
<hr>
              <table align="right"  style="font-size:12px; font-family:Arial, Helvetica, sans-serif;">
                <tr>
                    <td width="40%"><strong><?php echo '<strong>'. $roww['lab_sci_name'].'</strong>';?></strong> <BR><?php echo $roww['lab_sci_speciality']; ?>
                    </td>
                    <td width="15%">&nbsp;</td>
    
                    <td width="40%">Dr.&nbsp; <?php echo  $roww['approved_by']; ?><br><strong>Radiologist</strong></td>
                    <td width="5%">&nbsp;</td>
                </tr>
                <tr>
                    <td width="">
                    	
                    </td>
                    <td width="">&nbsp;</td>
    
                    <td width="">
                    <?php 
								$approved_by=$roww['approved_by'];
							$stmt=$db->query("Select username from admin_users where fullname like '%$approved_by%'");
								if ($stmt->rowCount()>0){ 
										$row=$stmt->fetch(PDO::FETCH_ASSOC);
											$uname=$row['username'];
								?>
                                
	                    <img src="<?php if(file_exists(staff_p . 'sign_'.$uname . '.'. 'jpg')){ echo staff_p . 'sign_'.$uname . '.'. 'jpg';  ?> " height="100" width="170"> <?php }else{echo '______________________________'; }?>
								
                                <?php }else{
									
								}
					
					?>
                    </td>
                    <td width="">&nbsp;</td>
                </tr>                            
</tbody>
                                </table>

<?php }

?>


