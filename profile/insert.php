<?php  include("../Connections/Conn.php"); ?>

<?php
session_start();

$setdate=date('Y-m-d H:i:s');	


if(isset($_POST["dept_inven_id"])) { 
	$dept_id=$_POST["dept_inven_id"];  
	$stmt=$db->query("SELECT distinct s.product_name, s.sn FROM stock_table s 
	inner join stock_table_inven c on s.sn=c.stock_sn where c.cust_patient_id='$dept_id' and c.bal>0 order by c.sn desc"); 
				if ($stmt->rowCount()>0){
?>

<div id="confirm" style="background-color: red" align="center">
	<h2 style="color: white;">Are you sure you want to submit this deduction to the Inventory? Revert is impossible once submitted! </h3>

</div>

<div class="form_sep">
<label for="reg_input_no" class="">Select Stock you wish to Deduct</label>
<select name="stock_sn" id="stock_sn_sn" class="form-control" style="font-size:14px" required>
<option  selected value="">-- select--</option>
<?php while ($row=$stmt->fetch(PDO::FETCH_ASSOC)){ $stock_sn = $row['sn'];?>
<?php 
$stmtx=$db->query("SELECT bal FROM stock_table_inven 
where cust_patient_id='$dept_id' and stock_sn='$stock_sn' ORDER BY sn DESC LIMIT 1"); 
		if ($stmtx->rowCount()>0){ 
			$rocw=$stmtx->fetch(PDO::FETCH_ASSOC); 
			$bal=$rocw['bal'];
		}else{
			$bal=0;
		}?>	
		<?php if($bal>0){ ?>	  
 <option value="<?php echo $row["sn"]; ?>"><?php echo  $row['product_name']; ?></option>
		<?php }else{ ?>
				   <option value="">No Stock Available to Deduct!</option>

        <?php }} ?>
				  
			</select>
            </div>

	<?php 
	
$stmtx=$db->query("SELECT department_type FROM department where sn='$dept_id'"); 	
	$rww=$stmtx->fetch(PDO::FETCH_ASSOC); 
	 $department_type=$rww['department_type'];
	///echo $dept_id;
										 
			if($department_type=='Nursing services'){?>
																	 
			<?php 
			}elseif($department_type=='Laboratory' or $department_type=='Radiology'){ ?>
			<div class="form_sep">
			<div class="pull-left" id="total_qty_test"></div>
			<div class="pull-right" id="total_consmbl_used"></div>
			</div>
			<?php  }
	?>
                <div class="form_sep"> 
                <label for="reg_input_no" class="req">Enter Quantity to deduct</label>
                <input type="number" id="new_qty" name="new_qty" class="form-control" onkeyup="sum();" min="1" required>             
                </div>
            <?php ///if($department_type=='Laboratory' or $department_type=='Radiology'){ ?>
					
	<!--		<div class="form_sep" id="">
			<label for="reg_input_no" class="req">Enter Remark/Describe Deduction Type</label>
			<input type="text" id="remark" name="remark" class="form-control" placeholder="" maxlength="50" required>                
			</div>
			<input type="hidden" name="deduction_mode" id="deduction_mode" value="Deparmental" />

				<div class="form_sep" id="deduct_consumbled">
				<table><tr><td>	
				<input name="deduct_consumble" id="deduct_consumble" type="checkbox" style="display:block; height:18px; width:18px;"></td>
					<td style="padding-top: 5px;">&nbsp;<strong>Deducted Stock Based Consumble Used</strong></td></tr>
				</table>
				</div>-->

			<?php ///}else{?>
	          <div class="form_sep">
            <label for="reg_input_no" class="">Choose Patient Billable/Departmental Use</label>
              <select name="deduction_mode" id="deduction_mode" class="form-control" style="font-size:14px" required>
              	<option  selected value="">-- select--</option>
              	<option  value="Deparmental">Departmental Used </option>
              	<option  value="billable">Bill Patient </option>
			</select>
            </div>

			<div class="form_sep" id="departmental_use">
			<label for="reg_input_no" class="req">Enter Remark/Describe Deduction Type</label>
			<input type="text" id="remark" name="remark" class="form-control" placeholder="" maxlength="50" required>                
			</div>

			<?php //} ?>
	

			
			<?php
	
	$CurDateHR = date("Y-m-d H:i:s");
    $apptm = $db->query("SELECT 
	patient_name,hospital_no 
	FROM apptm 
	WHERE (status='future' or status='checkin') and ap_date_time<='$CurDateHR'");
?>
      
	  <div class="form_sep" id="billable"> 
	<label for="reg_select">Enter Hospital Number</label>
	<select name="hospital_num" id="hospital_num" class="form-control">
		<option selected="selected" value="">Select or Skip</option>
	<?php	while ($row = $apptm->fetch(PDO::FETCH_ASSOC)) { ?>				
			<option value="<?= $row['hospital_no'] ?>"><?= $row['patient_name']; ?></option>
	<?php } ?>
			</select>
	</div>
	

<div class="form_sep">
<table><tr><td>	
<input name="auth_code" id="billable_cmd" type="checkbox" style="display:block; height:18px; width:18px;"></td>
	<td style="padding-top: 5px;">&nbsp;<strong>Check to confirm patient/Compute Billing</strong></td></tr>
</table>
</div>
			<div id="display_billable"></div>
			</div>


<div class="form_sep">
     <div class="pull-left" >
<button type="button" id="remove_form_stock" onClick="remove_form_stock()" class="btn btn-success">Confirm</button>	 
<button type="button" id="remove_form_stock_final" onClick="remove_form_stock_final()" class="btn btn-warning">Submit</button>	 
        </div>   
        
             <div class="pull-right" >
	<a href="invsti_rq.php" class="btn btn-danger">Close</a>
             </div>   
			</div>
  
                    <br>
	<input type="hidden" name="billable_detail" id="billable_detail" value="" />
	<input type="hidden" name="dept_id" id="dept_id" value="<?php echo $dept_id; ?>" />
	<input type="hidden" name="department_type" id="department_type" value="<?php echo $department_type; ?>" />
	<input type="hidden" name="fullname" id="fullname" value="<?php echo $_SESSION['fullname']; ?>" />

<?php }else{ ?>

<strong>No Approved Records to Displayed!</strong>
 
<?php
										}
  }
?>


 <script>
	 
$(document).ready(function() {
	
	
	$("#remove_form_stock_final").hide();
	$("#confirm").hide();
	
$("#hospital_num").change(function() {
		document.getElementById("billable_cmd").checked = false;
		document.getElementById('billable_detail').value='';
		document.getElementById("display_billable").innerHTML='';
});
	
	
$("#remove_form_stock_final").click(function(){
	
	
	
		var stock_sn_sn = document.getElementById('stock_sn_sn').value;
		var new_qty = document.getElementById('new_qty').value;
		var deduction_mode = document.getElementById('deduction_mode').value;
		var remark = document.getElementById('remark').value;
		var hospital_num = document.getElementById('hospital_num').value;
		var billable_detail = document.getElementById('billable_detail').value;
		var billable_cmd = document.getElementById('billable_cmd').value;
		var dept_id = document.getElementById('dept_id').value;
		var fullname = document.getElementById('fullname').value;
		var department_type = document.getElementById('department_type').value;	
	
			if(new_qty<=0) {	
				alert('Error: Quantity must be greater than zero!');
				exit;
			}
	
			if(billable_detail=='' && deduction_mode=='billable') {	
				alert('Error: Checked to Confirm Patient details or Set to departmental Use.');
				exit;
			}
			if(remark=='' && deduction_mode=='Deparmental'){
				alert('Error: Remarks Not Available!');
				exit;					 
			}
/*
 if (document.getElementById('deduct_consumble').checked) {
	var deduct_consumable='yes';
	 var r = confirm("You checked deduct Stock based on consumable used. Are you sure you want to continue .. ?");
       if (r === false) {
           return false;
        }
}else{*/
	var deduct_consumable='';
//}

	///alert();
	
	$.ajax({
		url:"fetch.php",
		data:{final_submission:stock_sn_sn,new_qty:new_qty,deduction_mode:deduction_mode,remark:remark,hospital_num:hospital_num,billable_cmd:billable_cmd,dept_id:dept_id,fullname:fullname,billable_detail:billable_detail,deduct_consumable:deduct_consumable},
		type:'POST',
		success:function(response) {
			$("#remove_form_stock_final").hide();
			$("#confirm").hide();
			toastr.success(response, 'Attention', {timeOut: 5000})
		}
	});
	
	
	
});
	
$("#remove_form_stock").click(function(){
	///alert();  
		//var conf=0;
			var deduction_mode = document.getElementById('deduction_mode').value;
			var billable_detail = document.getElementById('billable_detail').value;
			var remark = document.getElementById('remark').value;
			var new_qty = document.getElementById('new_qty').value;
	
			if(new_qty<=0) {	
				alert('Error: Quantity must be greater than Zero!');
				exit;
			}
	
			if(billable_detail=='' && deduction_mode=='billable') {	
				alert('Error: Checked to Confirm Patient details or Set to Departmental Use.');
				exit;
			}
			
			if(remark=='' && deduction_mode=='Deparmental'){
				alert('Error: Remarks Not Available!');
				exit;					 
			}
	
		$("#confirm").show();

//	document.getElementById("remove_form_stock").innerHTML='Confirm';
		$("#remove_form_stock_final").show();
		$("#remove_form_stock").hide();
	
});
	
						  
$("#billable_cmd").change(function() {
	
		var hospital_num = document.getElementById('hospital_num').value;
		var stock_sn_sn = document.getElementById('stock_sn_sn').value;
		var new_qty = document.getElementById('new_qty').value;
	
			if(new_qty<=0) {	
				alert('Error: Quantity Must Be greater than Zero!');
				document.getElementById("billable_cmd").checked = false;
				exit;
			}
			
		if(hospital_num=='') {	
				alert('Error: Enter Hospital Number');
				document.getElementById("billable_cmd").checked = false;
				exit;
			}
	
	
		$.ajax({
			url:"fetch.php",
			data:{confirm_patient:hospital_num,stock_sn_sn:stock_sn_sn,new_qty:new_qty},
			type:'POST',
			success:function(response) {
				var json = JSON.parse(response);
				
				if(json["msg"]==''){
					
					toastr.success('Successful', 'Success', {timeOut: 5000});
					document.getElementById("billable_detail").value=json["detail"];
					document.getElementById("display_billable").innerHTML=json["detail2"];
				
				}else{
					toastr.error(json["msg"], 'Error', {timeOut: 5000});
					document.getElementById("billable_cmd").checked = false;
				}
				
			}
		});
});
						  
						 						  
	
$("#departmental_use").hide();
$("#billable").hide();
	
	
$("#deduction_mode").change(function() {
		///alert();
		var deduction_mode = document.getElementById('deduction_mode').value;
		
		if(deduction_mode=='Deparmental'){
			$("#billable").hide(); $("#departmental_use").show()
		}else if(deduction_mode=='billable'){
			$("#billable").show(); $("#departmental_use").hide()
		}
	
	
	
});
	   
	   
					 
						 
$("#stock_sn_sn").change(function() {
	
	///alert();
	
	var dept_id = document.getElementById('dept_id').value;	
	var department_type = document.getElementById('department_type').value;	
	var stock_sn_sn = document.getElementById('stock_sn_sn').value;	
	
if(department_type=='Laboratory' || department_type=='Radiology'){

	document.getElementById("total_qty_test").innerHTML='';
	document.getElementById("total_consmbl_used").innerHTML='';	

	$.ajax({
			url:"fetch.php",
			data:{check_for_consumble:dept_id,department_type:department_type,stock_sn_sn:stock_sn_sn},
			type:'POST',
			success:function(response) {
				///alert (response);
				
				var json = JSON.parse(response);
				
	//			alert(json["qty_deduct_status"]);
				
				if(json["consumable_status"]==1){
					///alert(json["qty_deduct_status"]);
					
					$("#total_investigation_conduct").show();
					$("#total_qty_test").show();
					document.getElementById("total_qty_test").innerHTML='Total Test(s) Conducted : ' + json["total_test_conduct"];
					document.getElementById("total_consmbl_used").innerHTML=json["main_msg"];						
				}
				else{
		$("#total_investigation_conduct").hide();
		$("#total_qty_test").hide();						
				}
			}
		});
	}
});

});
	  
	 
</script>
 
 