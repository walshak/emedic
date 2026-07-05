<?php include("../Connections/Conn.php");?>

<?php
session_start();


if(isset($_GET['del'])){
	
    $labrequest_no = $_GET['del'];
    $emr = $_GET['emr'];
    
    try {
        // Begin transaction
        $db->beginTransaction();
        $delete1 = $db->prepare("DELETE FROM patient_ap_services WHERE drug_sn = ? AND paystatus = '0'");
        $deleted1 = $delete1->execute(array($labrequest_no));
    
        if ($deleted1) {
            // Delete from lab_manage
            $delete2 = $db->prepare("DELETE FROM lab_manage WHERE labrequest_no = ?");
            $deleted2 = $delete2->execute(array($labrequest_no));
    
            if ($deleted2) {
                // Commit transaction if both deletions were successful
                $db->commit();
                header("location:xsale.php?emr=$emr&investigation&deleted");
                exit();
            } else {
              
                $db->rollBack();
                echo "Error deleting from lab_manage.";
            }
        } else {
           
            $db->rollBack();
            echo "Error deleting from patient_ap_services.";
        }
    } catch (Exception $e) {
        // Rollback transaction in case of an exception
        $db->rollBack();
        echo "Failed: " . $e->getMessage();
    }
    
}
?>

<!DOCTYPE html>
<html>
	<?php include("../inc/header.php"); ?>
<body>

<div id="wrapper">
   <?php include("../inc/nav_side.php"); ?>

<div id="page-wrapper" class="gray-bg">
   <?php include("../inc/nav_header.php"); ?>
   

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Patients</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.html">Home</a>
            </li>
            <li class="active">
                <strong>Patient Search</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

        <div class="wrapper wrapper-content  animated fadeInRight">
        
       <div class="row">
                 <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5> Search Patients Data</h5>
                            <div class="ibox-tools">
                               
        <!--                        <input type="button" name="edit" value="Add New Patient" data-target="#myModal5" 
     class="btn btn-primary btn-xs add_new_sale" />-->
     
                            </div>
                        </div>
                            <div class="ibox-content">
                            	
                                
<div class="row">
<form action="xsale.php" method="POST" id="subject" name="subject" enctype="multipart/form-data" >


<div class="col-md-4"> 

    <div id="">
    <label for="reg_input_no" class="req">External Patients</label>
        <select name="ex_patient"  class="input-sm chosen-select" style="width:350px;"  >
         <option selected="selected" value="">Search and Select Patient</option>
        <?php $stmt = $db->query("SELECT transc_code, cust_name, referral FROM pharm_ext order by transc_code");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $row["transc_code"].'__'. $row["cust_name"].'__'. $row["referral"]; ?>"><?php echo $row["transc_code"] .' '. $row["cust_name"]; ?></option>
        <?php } ?>
        </select>
    </div>
                    

</div>


<div class="col-md-4"> 

<div id="">
            <label for="reg_input_no" class="req">Enter Patient's Hospital Number: </label>
				<input type="text" name="in_patient" class="form-control" >
        </div>

</div>

<div class="col-md-4"> 
                             
<div id="">
<label for="reg_input_no" class="req"><strong style="color:#F00">Click Apply to ADD/VIEW Request</strong></label><br>
<button class="btn btn-primary btn btn-sm" type="submit" name="apply_approve" >Apply</button> 
</div>
</div>

</form>
</div>

<hr>
<div class="row">

<div class="col-md-12"> 

			<?php
	
	
if ($_POST['in_patient']!='' and $_POST['ex_patient']!='' ){ ?>
	
	<strong style="color:#F00">Select Either Hospital or External Patient. Choose one patient type at a time </strong>
	
<?php }else{
		
	if(isset($_POST['in_patient']) and $_POST['in_patient']!=''){
			$in_patient =trim($_POST['in_patient']);
			$hosp_no =trim($_POST['in_patient']);
		
				$status = 'active';
		$stmt_en =$db->prepare("SELECT i.insurance_name,i.interest,i.add_minus,i.insurance_type,i.payment_mode,e.hmo_no,e.surname,e.fname,
			e.gender,e.addr,e.discount_set FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no
			WHERE hospital_no=:hospital_no and status=:status");
		$stmt_en->bindValue(':hospital_no', $in_patient, PDO::PARAM_STR);
		$stmt_en->bindValue(':status', $status, PDO::PARAM_STR);
		$stmt_en->execute();
		if($stmt_en->rowCount()>0){
			
					$row=$stmt_en->fetch(PDO::FETCH_ASSOC);
						$insurance_name=$row['insurance_name'];	
						$insurance=$row['insurance_type'];
						$interest=$row['interest'];	
						$add_minus=$row['add_minus'];	
						$insurance_no=$row['hmo_no'];
						$patient_name=$row['surname'] . ', ' . $row['fname'];
						$names=$row['surname'] . ', ' . $row['fname'];
						?>
          <h4>Hospital #: <?php  echo $in_patient; ?> / Names: <?php  echo $patient_name; ?></h4>
          <h4>Insurance #: <?php  echo $insurance_no; ?> /  <?php  echo $insurance_type; ?></h4>
<?php }else{ ?>
		<strong style="color: red;">Patient Details Not Available!</strong>	
<?php } ?>	
	
	
                                 <?php if ($_SESSION['request']==1 and $in_patient!='' and $stmt_en->rowCount()>0){?>
                <input type="button" name="edit" value="Add New Request" data-target="#myModal5" id="<?php 
				echo $in_patient .'__' .$patient_name .'__' .$insurance.'__' .$interest.'__' .$insurance_no.'__' .$add_minus.'__IN__self'; 
																									 ?>" class="btn btn-primary btn-xs lab_request" /> 
                
   				&nbsp; | &nbsp;               
             <a href="mgt.php?emr=<?php echo $hosp_no; ?>" class="btn btn-success btn-xs">View Request</a>              
			<?php if( $_SESSION['bill'] == 1){ ?>
 				&nbsp; | &nbsp;               
             <a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>&inv" class="btn btn-danger btn-xs">Billing</a>
             <?php } ?>
	
      <?php } ?>
	
	             
  &nbsp; | &nbsp;
    <a href="xsale.php?emr=<?php echo $hosp_no; ?>&investigation" class="btn btn-info btn-xs">View Investigation(s)</a> 
     								
								<?php } ?>
                                
								<?php 
								
		if(isset($_POST['ex_patient']) and $_POST['ex_patient']!=''){
            
									$ex_patient =$_POST['ex_patient'];
									$part=explode("__", $ex_patient);
									
									$patient_name=$part['1'];
									$hosp_no=$part['0'];
									$referral=$part['2'];
									?>
          <h4>Hospital #: <?php  echo $part['0']; ?> / Names: <?php  echo $part['1']; ?></h4>                                  
	<?php if ($_SESSION['request']==1 and $hosp_no!=''){?>
                          <input type="button" name="edit" value="Add New Request" data-target="#myModal5" id="<?php echo $hosp_no .'__' .$patient_name.'__'.'insurance'.'__'.'int'.'__'.'insur_no'.'__'.'add'.'__EX__'.$referral; ?>" class="btn btn-primary btn-xs lab_request" /> 

   				&nbsp; | &nbsp;               
             <a href="xsale.php?emr=<?php echo $hosp_no; ?>&investigations" class="btn btn-success btn-xs">View Request</a>              

			<?php if( $_SESSION['bill'] == 1){ ?>
 				&nbsp; | &nbsp;               
             <a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>&inv" class="btn btn-danger btn-xs">Billing</a>
             <?php } ?>
	
             	&nbsp; | &nbsp;
              <input type="button"  name="edit" value="Edit Patient" data-target="#myModal5" id="<?php echo $part['0'].'__' .$part['1']; ?>" 
     class="btn btn-warning btn-xs edit_ex" />   
             
  &nbsp; | &nbsp;
    <a href="xsale.php?emr=<?php echo $hosp_no; ?>&investigation" class="btn btn-info btn-xs">View Investigation(s)</a>     
	
	
      <?php } ?>                              
								<?php }
								
}?>                                
						
		
            
 
</div>

</div>
                                	
                            </div>
                        </div>
                        
           </div>             
         </div>               
                        
                
            <div class="row">
                 <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Patients List</h5>
<div class="ibox-tools">
     
                            </div>                            
                        </div>
                        <div class="ibox-content">
							
	<?php if(isset($_GET['investigation'])){ 
			$emr = $_GET['emr'];
	$stmt2 =$db->prepare("SELECT l.*, p.* FROM lab_manage l INNER JOIN patient_ap_services p 
				on p.drug_sn = l.labrequest_no
			WHERE l.patient=:hospital_no order by l.sn desc limit 30");
		$stmt2->bindValue(':hospital_no', $emr, PDO::PARAM_STR);
		$stmt2->execute();
	if($stmt2->rowCount()>0){?>
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                                                <thead>
                                                <tr>
                                                    <th data-toggle="true">#</th>
                                                    <th data-toggle="true">Investigation</th>
                                                    <th data-toggle="true">Section</th>
                                                    <th data-toggle="true">Request Date</th>
                                                    <th data-toggle="true">Request_by</th>
													
                                                    <th data-toggle="true">Amount</th>
                                                    <th data-toggle="true">.</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                    while($roww=$stmt2->fetch(PDO::FETCH_ASSOC)) { 
														$labrequest_no = $roww['labrequest_no'];
													?>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php echo $roww['test_name']; ?></td>
                                                        <td><?php echo $roww['section']; ?></td>
                                                        <td><?php echo date('d-m-Y H:i', strtotime($roww['request_date'])); ?></td>
                                                        <td><?php echo $roww['request_by']; ?></td>
													
                                                        <td><?php echo $roww['pay']; ?></td>
                                                        <td>
															<?php if($roww['data_capture_status']=='approve'){?>
<?php /*?><a href="printlab_single.php?labrequest_no=<?= $labrequest_no ?>"  class="btn btn-success btn-xs">Print</a>
<?php */?>														
															<?php } ?>
															
															
															<?php if($roww['paystatus']=='0' and $roww['data_capture_status']!='approve'){?>
																<a href="xsale.php?emr=<?= $emr ?>&investigation&del=<?= $labrequest_no ?>" 
																   onclick="return confirm('Are you sure you want to Delete?');"
																    class="btn btn-warning btn-xs">Delete</a>
															<?php } ?>
														</td>
                                                  
                                                        
</tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                </tbody>
                                                </table>
					 
				<?php }else{ ?>	 
					 <strong>No Records to Display</strong>
				<?php }?>	 
							
	<?php }else{ ?>							
							
    <form action="xsale.php" method="POST" id="subject" name="subject" enctype="multipart/form-data" >
                    
                                        <table width="70%" cellpadding="2"><tr><td>          
                    <strong>Filter By Date</strong>
                    <div class="form-group" id="">
                        <div class="input-daterange input-group" id="datepicker">
                        <input type="date" class="form-control" name="start" value="<?php echo date("Y-m-d"); ?>"/>
                        <span class="input-group-addon">to</span>
                        <input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
                        </div>
                    </div>
                   </td>
                   
                   <td>
                   
           Sort by Referral <strong>[Optional]</strong>
            </td>
            <td>
            <select name="referral"  class="input-sm chosen-select" style="width:350px;" >
             <option selected="selected" value="">Select referrals</option>
       
	   <?php  $stmt = $db->query("SELECT name FROM referrals order by name");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $row["name"]; ?>"><?php echo $row["name"]; ?></option>
            <?php } ?>
            </select>
                               
                   </td>
                   
                   
                   <td style="padding-left:10px;"><button class="btn btn-success btn-sm" type="submit" name="apply_date" >Apply</button></td></tr></table>
    </form>                    
                        
<?php



			if(isset($_POST['apply_date'])){
				
				$start=$_POST['start'];
				$end=$_POST['end'];
		
						if(isset($_POST['referral']) and $_POST['referral']!=''){
				 $ref=$_POST['referral'];
				$stmt2=$db->query("SELECT * FROM pharm_ext WHERE referral='$ref' order by sn DESC");
						}else{
				$stmt2=$db->query("SELECT * FROM pharm_ext WHERE date_ap BETWEEN '$start' AND '$end' order by sn DESC");
						}
							
			}else{
	$Current_date=date("Y-m-d");
	$stmt2=$db->query("SELECT * FROM pharm_ext WHERE description='investigation' and date_ap='$Current_date' order by sn DESC");
			}
		
		if($stmt2->rowCount()>0){?>
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                                                <thead>
                                                <tr>
                                                    <th data-toggle="true">#</th>
                                                    <th data-toggle="true">Patient Code</th>
                                                    <th data-toggle="true">Name</th>
                                                    <th data-toggle="true">Contact</th>
                                                    <th data-toggle="true">Phone</th>
                                                    <th data-toggle="true">Address</th>
                                                    <th data-toggle="true">Referral</th>
                                                    <th data-toggle="true">.</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                    while($roww=$stmt2->fetch(PDO::FETCH_ASSOC)) {  ?>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php echo $roww['transc_code']; ?></td>
                                                        <td><?php echo $roww['cust_name']; ?></td>
                                                        <td><?php echo $roww['description']; ?></td>
                                                        <td><?php echo $roww['phone']; ?></td>
                                                        <td><?php echo $roww['address']; ?></td>
                                                        <td><?php echo $roww['referral']; ?></td>
                                                        <td>
                                   <input type="button" <?php if ($_SESSION['request']==0){?> disabled <?php } ?> name="add_req" value="Add New Request" data-target="#myModal5" id="<?php echo $roww['transc_code'] .'__' .$roww['cust_name'].'__EX__'.$roww['referral']; ?>" 
     class="btn btn-primary btn-xs lab_request" />  
     
	<?php if( $_SESSION['bill'] == 1){ ?><br>
	&nbsp; | &nbsp;
    <a href="../billing/pacct.php?emr=<?php echo $roww['transc_code']; ?>&inv" class="btn btn-danger btn-xs">Billing</a>
	<?php } ?>														
     
     &nbsp; | &nbsp;  
      
     <input type="button"  name="edit" value="Edit Patient" data-target="#myModal5" id="<?php echo $roww['transc_code'].'__' .$roww['cust_name']; ?>" 
     class="btn btn-warning btn-xs edit_ex" /> 
															
     &nbsp; | &nbsp;
    <a href="xsale.php?emr=<?php echo $roww['transc_code']; ?>&investigation" class="btn btn-info btn-xs">View Investigation(s)</a>                           
                                                        
</tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                </tbody>
                                                </table>
    <?php }else{echo '<br>No Data Available!';} ?>
						
						
						
	<?php } ?>
                            
                        </div>
                    </div>


                </div>
            </div>

        </div>
           
                    </div>
        </div>
        
<?php include("../inc/lab_mdl.php")?>        

<?php include("../inc/footer_scripts.php"); ?>

 		<?php if(isset($_GET['sv'])){ ?>
    <script>toastr.success('Data Save Successfully See the patient on list below ...', 'Saved', {timeOut: 5000})</script>
    <?php } ?>

    
        <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
   
   <script>
 
 
			
        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });



            /* Init DataTables */
            var oTable = $('#editable').dataTable();

            /* Apply the jEditable handlers to the table */
            oTable.$('td').editable( '../example_ajax.php', {
                "callback": function( sValue, y ) {
                    var aPos = oTable.fnGetPosition( this );
                    oTable.fnUpdate( sValue, aPos[0], aPos[1] );
                },
                "submitdata": function ( value, settings ) {
                    return {
                        "row_id": this.parentNode.getAttribute('id'),
                        "column": oTable.fnGetPosition( this )[2]
                    };
                },

                "width": "90%",
                "height": "100%"
            } );


        });

        function fnClickAddRow() {
            $('#editable').dataTable().fnAddData( [
                "Custom row",
                "New row",
                "New row",
                "New row",
                "New row" ] );

        }
    </script>
     
</body>

</html>
