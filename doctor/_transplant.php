<?php

$setdate = date('Y-m-d');

if (isset($_GET['del_trs'])) {
	
	$id=$_GET['del_trs'];
	$pp = explode('__', $id);
	
	$id= $pp[0];
	$app_no= $pp[1];
	
				$trans_note_stmt = $db->prepare( "SELECT id FROM transplants_donors WHERE transplant_id ='$id'" );
				$trans_note_stmt->execute();
		   		if ($trans_note_stmt->rowCount() == 0) {

					$trans_note_stmt = $db->prepare( "SELECT id FROM transplant_notes WHERE transplant_id ='$id'" );
				$trans_note_stmt->execute();
		   		if ($trans_note_stmt->rowCount() == 0) {
					
					
					
		$trans_note_stmt = $db->prepare( "SELECT sn FROM patient_ap_services 
		WHERE  app_no = '$app_no' and serv_group = 'Transplant' and paystatus='1'" );
				$trans_note_stmt->execute();
		   		if ($trans_note_stmt->rowCount() == 0) {				
					
					
					
    $insertSQL = 'DELETE FROM  transplants WHERE id = ?';
    $sql = $db->prepare( $insertSQL );
    $save = $sql->execute( array( $id ) );	
					   
	$insertSQL = "DELETE FROM patient_ap_services WHERE app_no = '$app_no' and serv_group = 'Transplant' and paystatus='0'";
    $sql = $db->prepare( $insertSQL );
    $save = $sql->execute();	
							
					       $error_status = 2;
            $error_msg = 'Success : Deleted';		
				}else{
					
					       $error_status = 1;
            $error_msg = 'Error : Unable to Deleted.';
				}
				
				
				
				
				}
					
					

	}
}



if (isset($_REQUEST['addPostOptNoteBtn'])) {
	
    $data['id'] = $_POST['transplant_id'];
    $data['hla_machine'] = $_POST['hla_machine'];
    $data['dsa_findings'] = $_POST['dsa_findings'];
    $data['plasma_exchange'] = $_POST['plasma_exchange'];
    $data['surgical_notes'] = $_POST['surgical_notes'];
    $data['warm_ischemic_time'] = $_POST['warm_ischemic_time'];
    $data['cold_ischemic_time'] = $_POST['cold_ischemic_time'];

    $error_msg = 'Opps something went wrong...';
    $error_status = 1;
    $update = $Transplant->update($data, $data['id']);
    if ($update == true) {
        $error_status = 2;
        $error_msg = 'Saved Successfully';
    }
}


if (isset($_REQUEST['addDonorBtn'])) {

    $patient_info = $Patient->get(['hospital_no' => $_POST['hospital_no']]);

    $gender = null;
    if (!empty($patient_info)) {
        $gender = $patient_info->gender;
    }


    $data['transplant_id'] = $_POST['transplant_id'];
    $data['hospital_no'] = $_POST['hospital_no'];
    $data['name'] = $_POST['name'];
    $data['phone_number'] = $_POST['phone_number'];
    $data['address'] = $_POST['address'];
    $data['nok_name'] = $_POST['nok_name'];
    $data['nok_phone_number'] = $_POST['nok_phone_number'];
    $data['nok_address'] = $_POST['nok_address'];
    $data['comment'] = null;
    $data['percentage'] = null;
    $data['blood_group'] = $_POST['blood_group'];
    $data['gender'] = $gender;
    $data['genotype'] = $_POST['genotype'];

    $error_msg = 'Opps something went wrong...';
    $error_status = 1;



    $percent = intval($data['percentage']);

    if ($percent < 0 || $percent > 100) {
        $error_msg = 'The percentage value is invalid';
    } else {
        $data['is_matched'] = false;
        if ($percent >= 60) {
            $data['is_matched']  =  true;
        }


        $addDonor = $Transplant->addDonor($data);

        if ($addDonor == true) {
            $error_status = 2;
            $error_msg = 'Saved successfully';
            $update = $db->prepare("UPDATE enrollee SET blood_g = ?, geno_type = ? WHERE hospital_no = ? ");
            $update = $update->execute(array(
                $data['blood_group'],
                $data['genotype'],
                $data['hospital_no']
            ));
        } else {
            $error_msg = $addDonor;
        }
    }
}


  if(isset($_GET['trs']))
    {
    

include_once('_transplant_details.php');
    }else{
        if(isset($_GET['reports'])){
            include_once('_transplant_reports.php');
        }else{	
            
    ?>
<div class="ibox ">
<div class="ibox-title">
    <h5>Transplant</h5>
    <div class="ibox-tools">
       
    
</div>
</div>

	
	
	
	
	
<div class="ibox-content">

	    <div class="row">
			
			<div class="col-sm-6 b-r">
			<div align="center">
			<a class="btn btn-app" data-toggle="modal" data-target="#bookTransplantModal"><i class="fa fa-user"></i> + New Request</a>
			<?php
			if (isset($_GET['patient']) and ($_SESSION['rights'] =='DR' or $_SESSION['rights'] =='NS')) { ?>
			<a  href="patient.php?hosp_no=<?php echo cleanInput($_GET['patient']);?>" class="btn btn-app btn-danger " ><i class="fa fa-home "></i> Patient Dashboard </a>
			<?php }
			?>
			<a href="index.php?transplant" class="btn btn-app"><i class="fa fa-list"></i> Transplant List </a>
			<a href="<?php echo $editFormAction.'&donor-pool';?>" class="btn btn-app"><i class="fa fa-user-md"></i> Donor Pool </a>
			<a href="<?php echo $editFormAction.'&reports';?>" class="btn btn-app"><i class="fa fa-search"></i> Reports </a>
			</div>
				</div>	
				
				
			<div class="col-sm-6">
			<H2>Transplant Patients List </H2>	
				   <form action="index.php?transplant" method="POST" name="subject">
                        <table width="100%">
							<tr>
								<td width="60%">
                            <label for="reg_input_no" class="req">Select Patient</label>
                            <select name="hospital_no" class="input-sm chosen-select" style="width:350px;" required>
                                <option value="">Search</option>
                                <?php
   $stmt = $db->prepare("SELECT distinct hospital_no,patient_name from transplants order by patient_name");
   $stmt->execute();
      while ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {?>
    <option value="<?php echo $rw['hospital_no'] . '__' . $rw['patient_name']; ?>">
		<?php echo $rw['hospital_no'] . ' ' . $rw['patient_name'] ?>
								</option>
                                <?php
                                }
                                ?>
                            </select>
								</td>
								<td width="40%">
									 <label>.</label><br>
				 &nbsp;&nbsp;<button type="submit" class="btn btn-primary btn-sm" name="patient_trs_display" >Display</button>
									&nbsp; | &nbsp;
									<a href="index.php?transplant" class="btn btn-default btn-sm">Refresh</a>
								</td>	
							</tr>
                        </table>
				
				</form>
				
			</div>
				
				
		
			
			
		</div>
	
	
	
    <?php 
	  		if(isset($_GET['donor-pool'])){
			include_once('_transplant_pools.php');	
			}else{
                if(!isset($_GET['reports'])){
	               include_once('_transplant_request_list.php');
                }
			}

        }
	  
    ?>
</div>
</div>
    <?php
    }
?>

<?php

?>