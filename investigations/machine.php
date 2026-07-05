<?php include("../Connections/Conn.php");?>

<?php
session_start();

if (isset($_POST["cancel"])) {
		header("location:manage.php");				  
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
        <h2>Equipment Maintenance Logsheet</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.html">Home</a>
            </li>
            <li class="active">
                <strong>Device Management</strong>
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
                            <h5>Add Device & Details</h5>
                            <div class="ibox-tools">
        <input type="button" name="edit" value="Add New Device" data-target="#myModal5" id="" 
        class="btn btn-primary btn-xs add_machine" />

                            </div>                              
                        </div>
                        <div class="ibox-content">
                        
                        <form action="machine.php" method="post">
                      <table width="100%"><tr><td>
                <div class="form_sep">
                      
                    <label for="reg_input_no" class="req">Equipment Name</label>
                    <select name="device_name_search"  class="chosen-select" style="width:350px;" >
                    <option selected="selected" value="">Search and Select Device</option>
                    <?php 
		$stmt=$db->query("SELECT * FROM invsti_machine order by device_name");
					while ($row=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
                    <option value="<?php echo $row["device_name"]; ?>"><?php echo $row["device_name"]; ?></option>
                    <?php } ?>
                    </select>
                 </div>
</td><td style="padding-left:10px">
                 
                 
                 
<div class="form_sep">
<label for="" class="">.</label><br>
<button class="btn btn-success btn-sm" type="submit" name="show_details" ><i class="fa fa-check"></i>&nbsp;&nbsp; Show Detials</button>
</div>
</td></tr>
</table>
				</form>
                        </div>
                        
                        
                    </div>


                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Add Device & Details</h5>
                            <div class="ibox-tools">

                            </div>                              
                        </div>
                        <div class="ibox-content">

<?php

/// if there is any pending or OVER DUE REQUEST
if (!isset($_POST["show_details"])) {
	$one=1;
    $stmt = $db->prepare("SELECT * FROM invsti_machine WHERE due_status = :due_status ORDER BY sn DESC");
    $stmt->bindValue(':due_status', $one, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {?>

       <div class="alert alert-danger"><strong>Pending Device(s) Due Maintenance ... </strong></div>
       
       <table class="table table-striped table-bordered table-hover" >                 
                        <thead>
                        <tr>
                            <th data-toggle="true">#</th>
                            <th data-toggle="true">Equipment Name</th>
                            <th data-toggle="true">Service Interval Count </th>
                            <th data-toggle="true">Maintenance Type</th>
                            <th data-toggle="true">Total Investigation Recorded</th>
                            <th data-toggle="true">Due Date</th>
                            <th data-toggle="true">Last Investigation Count</th>
                          </thead>
                        <tbody>

                            <?php 
                                $n=1;
                                while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
                                ?>
                               <tr>  
             <td><?php echo $n;?></td>
             <td><?php echo $roww['device_name']; ?></td>
             <td><?php echo $roww['service_every_count']; ?></td>
             <td><?php echo $roww['service_every_type']; ?></td>
             <td><?php echo $roww['total_invsti']; ?></td>
             <td><?php if($roww['next_due_date']=='' or $roww['next_due_date']=='0000-00-00'){echo'--';}else{
				 echo date("d M Y",strtotime($roww['next_due_date']));} ?></td>
             <td><?php echo $roww['investigation_count']; ?></td>
                                </tr>
                            <?php 
                               $n++;	
                            }?>
                                
                        </tbody>
                        </table>
                        
			<?php } 	
		}



if (isset($_POST["show_details"])) {
	 $device_name_search=$_POST["device_name_search"];

     $stmt = $db->prepare("SELECT * FROM invsti_machine WHERE device_name = :device_name");
     $stmt->bindValue(':device_name', $device_name_search, PDO::PARAM_STR);
     $stmt->execute();
     
     if ($stmt->rowCount() > 0) {
	 		$row=$stmt->fetch(PDO::FETCH_ASSOC);
				$device_no=$row['sn'];
					$device_name=$row['device_name'];
					$effective_date=$row['effective_date'];
					$s_count=$row['service_every_count'];
					$s_type=$row['service_every_type'];
					$investi_count=$row['investigation_count'];
				$next_due_date=$row['next_due_date'];
				$total_invsti=$row['total_invsti'];
	 ?>
	 	
	       <table class="table table-striped table-bordered table-hover" >                 

<tr>
<th data-toggle="true">Device Name</th>
<th data-toggle="true">Label /  Serial </th>
<th data-toggle="true">Manufacturer / Contact Person </th>
<th data-toggle="true">Date Manufacture / Purchase  </th>
<th data-toggle="true">Date Put into Service</th>
</tr>

<tr>  
<td><?php echo $device_name;?></td>
<td><?php echo $row['label'] . ' / ' . $row['serial']; ?></td>
<td><?php echo $row['manufacturer'] .' / '. $row['manufacturer_contact_person']; ?></td>
<td><?php echo date('d,M y', strtotime($row['date_manufacture'])) .' / '. date('d,M y', strtotime($row['date_purchase'])); ?></td>
<td><?php echo date('d,M y', strtotime($row['date_put_service']));?></td>

</tr>

<tr>
<th data-toggle="true">Service Provider</th>
<th data-toggle="true">Service Provider Contact</th>
<th data-toggle="true">Location Equipment</th>
<th data-toggle="true">Frequence</th>
<th data-toggle="true"></th>

</tr>

<tr>  
             <td><?php echo $row['service_provider']; ?></td>
             <td><?php echo $row['service_provider_contact']; ?></td>
             <td><?php echo $row['location_equipment']; ?></td>
             <td><?php echo $row['service_every_count'] . ' - ' . $row['service_every_type'] . ' Interval'; ?></td>
             <td>            
          <input type="button" name="edit" value="Assign Investigation & View" data-target="#myModal5" id="<?php echo $device_no .'__'.$device_name; ?>" class="btn btn-warning btn-xs assign_investi" />
</td>
                                </tr>
</table>                       
   	<div class="">
    <div class="pull-left">
    
          <input type="button" name="edit" value="Routine (Record Information)" data-target="#myModal5" id="<?php echo $device_no .'__'.$device_name; ?>" class="btn btn-success btn-xs routine" />
    

    </div>
	
    <div class="pull-right">
	<form action="machine.php" method="post">
     <button class="btn btn-white btn-sm" type="submit" name="show_logs" id="show_logs" ><i class="fa fa-check"></i>&nbsp;Show Maintenance Logs</button>
     <input type="hidden" name="device_no2" value="<?php echo $device_no; ?>" />
      <input type="hidden" name="device_name" value="<?php echo $device_name; ?>" />
    </form>
    </div>
    </div>
    <br>
  
    
    <hr>	 
	<?php }
}
?>

<?php

	if (isset($_POST["device_no2"])) {
		
//// details here	
$stmt = $db->prepare("SELECT * FROM invsti_machine_logs WHERE device_no = :device_no ORDER BY sn DESC LIMIT 100");
$stmt->bindValue(':device_no', $_POST["device_no2"], PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {?>
                <strong><?php echo $_POST["device_name"]; ?></strong><hr>
                        
       <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                        <thead>
                        <tr>
                            <th data-toggle="true">#</th>
                            <th data-toggle="true">Total Investigation</th>
                            <th data-toggle="true">Report Dates</th>
                            <th data-toggle="true">Description</th>
                            <th data-toggle="true">Maintenance by</th>
                            <th data-toggle="true">Supervise by</th>
                            <th data-toggle="true">Remarks</th>
                            <th data-toggle="true">Next Due Date</th>
                          </thead>
                        <tbody>

                            <?php 
                                $n=1;
                                while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
                                ?>
                               <tr>  
             <td><?php echo $n;?></td>
             <td><?php echo $roww['total_invsti']; ?></td>
             <td><?php echo date('d,M y', strtotime($roww['maintenance_date'])); ?></td>
             <td><?php echo $roww['Description_maintenance']; ?></td>
             <td><?php echo $roww['Maintenance_performed_by']; ?></td>
             <td><?php echo $roww['supervise_by']; ?></td>
             <td><?php echo $roww['remarks']; ?></td>
             <td><?php echo date('d,M y', strtotime($roww['next_due_date']));?></td>
                                </tr>
                            <?php 
                               $n++;	
                            }?>
                                
                        </tbody>
                        </table>
                        
			<?php }else{
				echo '<br>No Records Available!';} 	
			}
?>
                            
                        </div>
                    </div>


                </div>
                
<div class="modal inmodal fade" id="add_machine_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Equipment Maintenance</h4>
			</div>

                <div class="modal-body">  
                
                     <form method="POST" id="add_machine_form">
  
                        
            <div class="form_sep">
                  <label for="reg_input_no" class="">Name of the piece of equipment:</label>
                <input type="text" id="device_name" name="device_name" class="form-control">
            </div>
            
            <div class="form_sep">
			<table width="100%"><tr><td>
            <div class="form_sep">
                  <label for="reg_input_no" class="">Label</label>
                <input type="text" id="label" name="label" class="form-control">
            </div></td><td>
            
            <div class="form_sep">
                  <label for="reg_input_no" class="">Serial</label>
                <input type="text" id="serial" name="serial" class="form-control">
            </div> 
            </td></tr></table>
            </div>
              
            <div class="form_sep">
                  <label for="reg_input_no" class="">Manufacturer:</label>
                <input type="text" id="manufacturer" name="manufacturer" class="form-control">
            </div>    
 
            <div class="form_sep">
                  <label for="reg_input_no" class="">Manufacturer's contact person + contact details:</label>
                <input type="text" id="manufacturer_details" name="manufacturer_details" class="form-control">
            </div>     
            <div class="form_sep" id="data_1">
                <label class=""><strong>Date of purchase:</strong></label>
                <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="date_purchase" class="form-control" value="<?php echo date("Y-m-d");?>">
                </div>
            </div>      
                   
            <div class="form_sep" id="data_1">
                <label class="font-noraml"><strong>Date put into Service:</strong></label>
                <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="date_put_service" class="form-control" value="<?php echo date("Y-m-d");?>">
                </div>
            </div>            
                                                          
            <div class="form_sep" id="data_1">
                <label class="font-noraml"><strong>Date of  Manufacture</strong></label>
                <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="date_manufacture" class="form-control" value="<?php echo date("Y-m-d");?>">
                </div>
            </div> 
              <div class="form_sep">
                  <label for="reg_input_no" class="">Service provider (for maintenance and calibration):</label>
                <input type="text" id="service_provider" name="service_provider" class="form-control">
            </div>             
              <div class="form_sep">
                  <label for="reg_input_no" class="">Service provider contact person + contact details:</label>
                <input type="text" id="service_provider_contact" name="service_provider_contact" class="form-control">
            </div>  
                          <div class="form_sep">
                  <label for="reg_input_no" class="">Location of Equipment</label>
                <input type="text" id="location_equip" name="location_equip" class="form-control">
            </div>             
            
  			<div class="form_sep">
            
            <table width="100%"><tr><td>
            <label for="reg_input_no" class="req"><strong>Frequency of maintenance:</strong></label>
                 <select name="service_after_type" id="service_after_type" class="form-control">      
              <option selected="selected" value="">Select Service Type...</option>
            
                        <option value="Days">Days Interval</option>
                        <option value="Investigation">By investigation done </option>
                                                    </select>
                </td><td>                                    
                                                    
                  <label for="reg_input_no" class="req"><strong>Enter Count/interval Here</strong></label>
                  <input type="number" id="service_after_no" min="1" name="service_after_no" class="form-control" placeholder="Enter Number Counts" >
                  </td>
                  </tr></table>
                  
              
            </div> 
            
                        <div class="form_sep" id="data_1">
                <label class="font-noraml">Starting/Effective Date</label>
                <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="effective_date" class="form-control" value="<?php echo date("Y-m-d");?>">
                </div>
            </div> 
                     
            <br />
                <div class="form_sep">
                 <input type="submit" name="save" id="save" value="Save" class="btn btn-success bt-sm" />
              <input type="hidden" name="MM_update" value="add_machine" />
                </div>
 	 </form> 
 
                
                </div>
                
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="routine_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""></h4>
			</div>

                <div class="modal-body">  
                
                <?php
				if($next_due_date>date("Y-m-d") and $s_type=='Days'){?>
             	   <div class="alert alert-success">This device is not due for maintenance!</div>
                <?php }elseif($next_due_date<=date("Y-m-d") and $s_type=='Days'){ ?>
             	   <div class="alert alert-danger">This device is due for maintenance!</div>
				<?php }
				
				
				if ($s_type=='Investigation'){
	
							$bal=$s_count-$investi_count;		
						if($s_count>$investi_count){ ?>
                        <div class="alert alert-success">This device is not due for maintenance!<br> <?php  echo '<strong>Total Done: ' .$investi_count.'</strong>' . ' / <strong> Remaining Balance due: '. $bal. '</strong>';?></div>
                        <?php }else{ ?>
                         <div class="alert alert-danger">This device is due for maintenance!<br>
          <?php  echo '<strong>Total Done: ' .$investi_count.'</strong>' .  ' / <strong> Service Limit: '. $s_count. '</strong>';?>
                         </div>
                        <?php } ?>
			<?php }
				?>
                
            <form method="POST" id="routine_form">
            <div class="form_sep">
                  <label for="reg_input_no" class="req">Description of maintenance:</label>
               <textarea name="Description_maintenance" id="Description_maintenance" cols="45" rows="3" maxlength="160" class=" form-control"></textarea>  
                
            </div>
                   
            <div class="form_sep">
                  <label for="reg_input_no" class="req">Maintenance performed by:</label>
                <input type="text" id="Maintenance_performed" name="Maintenance_performed" class="form-control">
            </div> 
                               
            <div class="form_sep">
                  <label for="reg_input_no" class="req">Validation performed/Supervisor by:</label>
                <input type="text" id="Supervisor" name="Supervisor" class="form-control" value="<?php echo $_SESSION['fullname']; ?>">
            </div>
           
            <div class="form_sep" id="data_1">
                <label class="req"><strong>Next maintenance planned on (date):</strong></label>
                <div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" name="next_due_date" class="form-control" value="<?php echo $row['next_due_date'];?>">
                </div>
            </div> 
                      
  			<div class="form_sep">
   <label for="reg_input_no" class="">Remarks:</label>
               <textarea name="remarks" id="remarks" cols="45" rows="1" maxlength="160" class=" form-control"></textarea>  
            </div> 
                        
                     
            <br />
                <div class="form_sep">
                 <input type="submit" name="save" id="save" value="Save" class="btn btn-success btn-sm" />
              <input type="hidden" name="MM_update" value="routine_add" />
              <input type="hidden" name="s_type" value="<?php echo $s_type; ?>" />
              <input type="hidden" name="next_due_date" value="<?php echo $row['next_due_date']; ?>" />
              <input type="hidden" name="s_count" value="<?php echo $s_count; ?>" />
              <input type="hidden" name="investi_count" value="<?php echo $investi_count; ?>" />
                <input type="hidden" name="device_no" value="<?php echo $device_no; ?>" />
                <input type="hidden" name="total_invsti" value="<?php echo $total_invsti=$total_invsti + $investi_count; ?>" />
                </div>
 	 </form> 

                
                </div>
                
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="assign_invest_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Assign Investigation</h4>
			</div>
                            <div class="modal-body" id="assign_invest_form">  
                </div>
                
        </div>
    </div>
</div>

                
            </div>

        </div>
           
                    </div>
        </div>

<?php include("../inc/footer_scripts.php"); ?>

   <script>
   
		$('#data_1 .input-group.date').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true
		});

	  $(document).on('click', '.assign_investi', function(){ 
	  // $('#view_lab_option_modal').modal('hide'); 
           var device_id = $(this).attr("id"); 
		             if(device_id != '')  
           {  
                $.ajax({  
                     url:"fetch_set.php",  
                     method:"POST",  
                     data:{device_id:device_id}, 
                     success:function(data){  
					
                          $('#assign_invest_modal').modal('show');  
						  $('#assign_invest_form').html(data); 
                     }  
                });  
           }            
      });	

			
	  $(document).on('click', '.add_machine', function(){  
	  
	   $('.modal-title').text('New Device');
	     $('#add_machine_form')[0].reset(); 
         $('#add_machine_modal').modal('show');
      });
	  
      
	  $('#add_machine_form').on("submit", function(event){  
           event.preventDefault(); 
		   
		   if($('#device_name').val() == "")  
           {  
                swal("Device Name is required"); 
           }
		   else if($('#service_after_no').val() == "")  
           {  
                swal("Service Number Count is required"); 
           }
	   else if($('#service_after_type').val() == "")  
           {  
                swal("Service After Type is required"); 
           }		   		   
		   else
		   {
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#add_machine_form').serialize(),  
                     beforeSend:function(){  
                          $('#save').val("Saving");  
                     },  
                     success:function(data){  
			swal({ title: 'Success!', text: 'Saved Successfully', timer: 1000 })
			$('#add_machine_modal').modal('hide');
			window.location.reload() ;
                     },
					 complete:function(){  
                          $('#save').val("Saved");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
		   }
      });	  
	  
	  
	  
	  
	$(document).on('click', '.assign_test_del', function(){ 
	var assign_no = $(this).attr("id"); 
	
	var res = assign_no.split("__");
	
		             if(assign_no != '')  
           {  
                $.ajax({  
                     url:"delete.php",  
                     method:"POST",  
                     data:{assign_no:res[0]}, 
                     success:function(data){  
					
					var device_no= res[1] + '__' + res[2];
					
                $.ajax({  
                     url:"fetch_set.php",  
                     method:"POST",  
                     data:{device_id:device_no}, 
                     success:function(data){  
					
                          $('#assign_invest_modal').modal('show');  
						  $('#assign_invest_form').html(data); 
                     }  
                }); 					
					
					 // $('#view_lab_modal').modal('show');  
					//$('#view_lab_body').html(data); 
                     }  
                });  
           }            
      });  
	    
	  $('#routine_form').on("submit", function(event){  
           event.preventDefault(); 
		   
		   if($('#Description_maintenance').val() == "")  
           {  
                swal("Description maintenance is required"); 
           }
		   else if($('#Maintenance_performed').val() == "")  
           {  
                swal("Maintenance performed is required"); 
           }
	   		else if($('#next_due_date').val() == "")  
           {  
                swal("Next due date is required"); 
           }
	   		else if($('#Supervisor').val() == "")  
           {  
                swal("Supervisor is required"); 
           }		   		   		   
		   else
		   {
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#routine_form').serialize(),  
                     beforeSend:function(){  
                          $('#save').val("Saving");  
                     },  
                     success:function(data){  
			swal({ title: 'Success!', text: 'Saved Successfully', timer: 1000 })
			$('#routine_modal').modal('hide');
			window.location.reload() ;
                     },
					 complete:function(){  
                          $('#save').val("Saved");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
		   }
      });		
	
	
	
	
	 
	$(document).on('click', '.add_machine', function(){  
	   $('.modal-title').text('New Device');
	     $('#add_machine_form')[0].reset(); 
         $('#add_machine_modal').modal('show');
      });
	  
	$(document).on('click', '.routine', function(){  
	  
	  	var device_name = $(this).attr("id");
	var res = device_name.split("__");
	
	   $('.modal-title').text(res[1]);
	     $('#routine_form')[0].reset(); 
         $('#routine_modal').modal('show');
      });	 
	  	  
		  
		$(".chosen-select").chosen({ allow_single_deselect: true, enable_search_threshold: 10,no_results_text:'Oops, nothing found!', width:"100%" });
		$('.chosen-drop').css({"width": "100%", "white-space": "nowrap"})




	  
	  </script>
    
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
