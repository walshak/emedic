<table width="90%">
	<tr>
		<td>
			<?php

			if ($_SESSION['rights'] != 'PY') { /// physiotheraphy

				if ($isOnAdmission == null) { /// not on admission
			?>
					<div id='_admit_status_' style="color: red;"></div>
					<a href="#" class="btn btn-xs btn-w-m btn-danger" id="send-admission-request-btn"
						style="color: white; font-size: 13px; " <?php if ($isOnAppoint == false) { ?> disabled <?php } ?>>
						ADMIT PATIENT</a>

				<?php } else if ($adm_status == 0 and $isOnAppoint == True) { /// sent admission request

					if ($admit_type == 'admit_o') {
						echo '<b style="color:red;">24hr Observation</b>';
					}
				?>
					<form action="patient.php?hosp_no=<?= $hospital_no; ?>" method="POST">
						<button type="submit" class="btn btn-xs btn-w-m btn-danger" name="cancel_adminssion_request_" onclick="return confirm('Are you sure you want to CANCEL Admission request ?')" style="color: white; font-size: 13px; ">Cancel Admission</button>
						<input type="hidden" name="admission_sn" value="<?= $adm_id; ?>" />
						<input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>" />
					</form>

					<?php } else if ($adm_status == '3') { /// on admission


					$discharge_request = 0;

					$stmt = $db->prepare('SELECT 1 FROM discharge_fellowup WHERE hospital_no = ? LIMIT 1');
					$stmt->execute([$hospital_no]);

					if ($stmt->fetchColumn()) {
						// Discharge request exists
						$discharge_request = 1;
					?>
						<form action="patient.php?hosp_no=<?= htmlspecialchars($hospital_no, ENT_QUOTES, 'UTF-8'); ?>" method="POST" onsubmit="return confirm('Are you sure you want to Cancel Discharge Request?')">
							<button type="submit" class="btn btn-xs btn-w-m btn-danger" name="cancel_discharge_request_btn" style="color: white; font-size: 13px;">
								Cancel Discharge
							</button>
							<input type="hidden" name="admission_sn" value="<?= htmlspecialchars($adm_id, ENT_QUOTES, 'UTF-8'); ?>" />
							<input type="hidden" name="hosp_no" value="<?= htmlspecialchars($hospital_no, ENT_QUOTES, 'UTF-8'); ?>" />
							<input type="hidden" name="hospital_no" value="<?= htmlspecialchars($hospital_no, ENT_QUOTES, 'UTF-8'); ?>" />
							<input type="hidden" name="appt_no" value="<?= htmlspecialchars($appointment_number, ENT_QUOTES, 'UTF-8'); ?>" />
							<input type="hidden" name="patient_name" value="<?= htmlspecialchars($patient_name, ENT_QUOTES, 'UTF-8'); ?>" />
						</form>
					<?php
					} else {
						// No discharge request yet
					?>
						<a href="#" class="btn btn-xs btn-w-m btn-danger" data-toggle="modal" data-target="#discharge_request_modal" style="color: white; font-size: 14px;">
							DISCHARGE
						</a>
			<?php
					}
				}
			}
			?>
			<a href="#" class="btn btn-xs btn-w-m btn-success " id="consumable-btn" style="color: white; font-size: 14px;">ADD ITEMS</a>
			<input type="hidden" name="hospital_no_add_consumble" id="hospital_no_add_consumble" value="<?= $hospital_no; ?>" />
		</td>

		<?php


		if ($_SESSION['rights'] != 'PY') {  /// physiotheraphy 
		?>

			<td>
				<a href="index.php?procedure&patient=<?php echo $hospital_no; ?>" class="btn btn-xs btn-w-m btn-success" <?php ($isOnAppoint || $admission_info != null ? "" : "disabled") ?> style="color: white; font-size: 14px; "><i class="fa fa-scissors"></i> &nbsp; PROCEDURE</a>

				<?php if ($_SESSION['dialysis_visible'] == '1') { ?>
					<a href="dialysis.php?patient=<?= $hospital_no; ?>" class="btn btn-xs btn-w-m btn-primary" id="dialysis-request-btn" <?php ($isOnAppoint || $admission_info != null ? "" : "disabled") ?> style="color: white; font-size: 14px; ">DIALYSIS</a>
				<?php } elseif ($_SESSION['ivf'] == '1') { ?>
					<a href="index.php?ivf_form&patient=<?= $hospital_no; ?>" class="btn btn-xs btn-w-m btn-primary" id="ivf-request-btn" <?php ($isOnAppoint || $admission_info != null ? "" : "disabled") ?> style="color: white; font-size: 14px; ">IVF</a>
				<?php } else { ?>
					<a href="patient.php?hosp_no=<?= $hospital_no; ?>" class="btn btn-xs btn-w-m btn-primary" style="color: white; font-size: 14px; ">..</a>

				<?php } ?>
			</td>
		<?php } ?>

		<td>
			<a href="document.php?token=<?= $hospital_no; ?>" class="btn btn-xs btn-primary" title="Patient's Document" style="color: white; font-size: 13px; "><i class="fa fa-file"></i>&nbsp;<b>Doc.</b></a>
			<a href="#" class="btn btn-xs btn-danger" onclick="openModal_fx('patient-alert-modal')" arial-modal="patient-alert-modal" style="color: white; font-size: 13px; "><i class="fa fa-exclamation-triangle" title="Set Alert"></i>&nbsp;<b>Alert</b></a>
			<a href="med_report.php?hosp_no=<?= $hospital_no; ?>" class="btn btn-xs btn-warning" title="Medical Report" style="color: white; font-size: 13px; "><i class="fa fa-folder-open"></i>&nbsp;MED. REPORT</a>

		</td>

	</tr>
</table>
<a href="#" class="btn btn-xs btn-w-m btn-primary " id="see-a-specialist-notes-btn" <?php ($isOnAppoint || $admission_info != null ? "" : "disabled") ?> style="color: white; font-size: 14px; " onclick="load_bookings()">Book/See Specialist</a>
<input type="button" name="edit_users" value="Add Note" data-target="#modal" style="color: white; font-size: 13px; " id="<?php echo $hospital_no . '___' . $plan_edit_mode . '___' . $appointment_number . '___' . $adm_status; ?>" class=" btn-xs btn-success add_view_plan" />

<?php if (!isset($_GET['editMedService'])) {
	$editFormAction = "patient.php?hosp_no=$hospital_no";
} ?>
<a href="<?= $editFormAction; ?>" class="btn btn-xs btn-w-m btn-default" style="color: white; font-size: 14px; "><i class="fa fa-repeat"></i>&nbsp;&nbsp;<i><b>REFRESH</b></i></a>

<div id="msg">
</div>
<?php
$complain = null;

$stmt = $db->prepare("
    SELECT complain, prepared_by 
    FROM c_d_remarks 
    WHERE hospital_no = ? 
      AND cat_type = 'DH' 
      AND complain != '' 
      AND LOWER(complain) NOT LIKE '%none%' 
      AND LOWER(complain) NOT LIKE '%null%' 
      AND LOWER(complain) NOT LIKE '%nil%' 
    ORDER BY sn DESC 
    LIMIT 1
");
$stmt->execute([$hospital_no]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$complain = trim($row['complain']);
	if (strlen($complain) > 0) {
?>
		<span class="blink">
			<strong style="color: chocolate;">Allergies:</strong>
		</span>
		<strong><?= htmlspecialchars($complain, ENT_QUOTES, 'UTF-8'); ?></strong>
		<br>
		<small><i>Entered by:</i> <?= htmlspecialchars($row['prepared_by'], ENT_QUOTES, 'UTF-8'); ?></small>
		<small><a href="#" id="new-allergy-btn">[ Edit ]</a></small>
<?php
	}
}
?>