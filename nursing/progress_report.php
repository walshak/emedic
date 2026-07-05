<?php


if (isset($_POST['save_custom_template'])) {

	$stmt_22 = $db->prepare("SELECT * FROM template_doctors WHERE template_name=:template_name");
	$stmt_22->bindParam(':template_name', $_POST['create_template'], PDO::PARAM_STR);
	$stmt_22->execute();

	if ($stmt_22->rowCount() == 0) {
		$sql = $db->prepare("INSERT INTO template_doctors (template_name,template) 
	VALUES (:template_name,:template)");
		$sql->bindParam(':template_name', $_POST['template_title'], PDO::PARAM_STR);
		$sql->bindParam(':template', $_POST['create_template'], PDO::PARAM_STR);
		$sql->execute();
	} else {
		$updateSQL = "UPDATE template_doctors SET template=:template WHERE template_name=:template_title";
		$sql = $db->prepare($updateSQL);
		$sql->bindParam(':template', $_POST['create_template'], PDO::PARAM_STR);
		$sql->bindParam(':template_title', $_POST['template_title'], PDO::PARAM_STR);
		$sql->execute();
	}
}


if (isset($_GET["action"])) {

	$stmtx = $db->prepare("SELECT template,template_name FROM template_doctors WHERE sn=:doc_template");
	$stmtx->bindValue(':doc_template', $_GET["action"], PDO::PARAM_STR);
	$stmtx->execute();
	$stmt_logCOUNT = $stmtx->rowCount();
	if ($stmt_logCOUNT > 0) {
		$rowx = $stmtx->fetch(PDO::FETCH_ASSOC);
		$template = $rowx['template'];
		$template_name = $rowx['template_name'];
	} else {
		$template = 'Type Consultation Notes';
	}
	$current_tab = 'mgt';
}
?>

<div class="row">






	<div class="col-lg-6">
		<?php if ($admission_info != null && $adm_status == 3) { ?>

			<?php /*?>		<div class="row">
		<div class="col-lg-6">		
		<div class="form-group">
		<label>Choose Note Template</label>
			<select class="form-control" name="doc_template" id="doc_template" onChange="load_template()">
				<option value="">-- Select --</option>
				<?php		
				$stmt2= $db->query("SELECT * FROM template_doctors ");
				if($stmt2->rowCount()>0){ 
				while($row=$stmt2->fetch(PDO::FETCH_ASSOC)){
				?>
				<option value="<?php echo $row['sn']; ?>"><?php echo $row['template_name']; ?></option>
				<?php } }?>
			</select> 
		</div> 	
		</div> 	
		<div class="col-lg-6">
			
			
		<div class="form-group">
			<label>Design your Note Template</label><br>
			<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#design_template_mdll">
			Click Here
			</button>				
		</div> 
		</div> 
		</div> 
		<?php */ ?>


			<div class="form_sep">
				<h3>Type Nursing Report</h3>
				<div id="edit__mode" style="color: red;"></div>
				<div name="mgt_notes" id="mgt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size:16px;"></div>
			</div>

			<div class="form_sep">
				<div class="pull-left">
					<button class="btn btn-primary" id="save-progress-note">Save Note</button>
				</div>
			</div>
			<input type="hidden" name="id" id="notes_sn">

			<input type="hidden" name="mode" id="mode">

			<input type="hidden" name="mode_typpe" id="mode_typpe" value="save_progress_note_button">
			<input type="hidden" name="hospital_no" id="hospital_no" value="<?= $hospital_no; ?>">
			<input type="hidden" name="appointment_number" id="appointment_number" value="<?= $appointment_number; ?>">
		<?php } else { ?>
			<h4 align="center" style="color: crimson; ">Patient is NOT currently On-Admission</h4>
		<?php } ?>
	</div>

	<div class="col-lg-6">
		<?php //include("../inc/_progress_notes_hx.php") 
		?>
	</div>

</div>


<div class="modal inmodal" id="design_template_mdll" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content animated bounceInRight">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

				<h4 class="modal-title">Create Documentation Template</h4>
			</div>

			<div class="modal-body" id="">
				<form method="post" id="" action="nursing_template.php">

					<div class="form-group">
						<label>Template Title </label>
						<input type="text" name="template_title" placeholder="Enter Template Ittle" class="form-control" required>
					</div>


					<label>Paste Template Below and Save</label>
					<div class="mail-text h-200">
						<textarea name="create_template" id="create_template" class="trumbowygEditor"></textarea>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<button class="btn btn-success" type="submit" name="save_custom_template" id="">Save</button>
					</div>

					<input type="text" name="hospital" value="<?= $hospital_no; ?>">
				</form>
			</div>
		</div>
	</div>
</div>


<script>

</script>