<table width="100%">
	<tr>
		<td>
			<?php if ($window_mode == 'bill') { ?>
				<a href="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>"
					class="btn btn-xs btn-info"
					style="font-size: 13px; color: white;">
					<i class="fa fa-repeat"></i>&nbsp;&nbsp;Refresh
				</a>
			<?php } ?>

			<?php if ($window_mode != 'bill') { ?>
                                <?php
                                $hdNurseStmt = $db->query("SELECT nurses_can_fully_admit_discharge FROM hospital_details LIMIT 1");
                                $hdNurseRow = $hdNurseStmt->fetch(PDO::FETCH_ASSOC);
                                $nurseFullPerm = (!empty($hdNurseRow['nurses_can_fully_admit_discharge']) && $hdNurseRow['nurses_can_fully_admit_discharge'] == 1);
                                if ($nurseFullPerm && !$isOnAdmission) { ?>
                                        <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#admit_patient_modal_nurse" style="font-size: 13px; color: white;">
                                                <i class="fa fa-bed"></i>&nbsp;Initiate Admission
                                        </a>
                                <?php } ?>

				<a href="patient_bill.php?hosp_no=<?= $hospital_no ?>&Invoice" class="btn btn-sm btn-info" style="font-size: 13px; color: white;"><i class="fa fa-stack-overflow"></i>&nbsp;&nbsp;View Billing</a>

				<?php if (in_array(strtoupper($sex), ['F', 'FEMALE'])) { ?>
					<input type="button" name="on_cr" value="Add Labour Summary" data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-danger btn-sm labour_summary" style="font-size: 13px; color: white;" />
				<?php } ?>

				<a href="patient_bill.php?hosp_no=<?= $hospital_no ?>&Services" class="btn btn-sm btn-primary" style="font-size: 13px; color: white;"><i class="fa fa-joomla"></i>&nbsp;Add Services</a>
				<a href="patient_bill.php?hosp_no=<?= $hospital_no ?>&Consumable" class="btn btn-sm btn-success" style="font-size: 13px; color: white;"><i class="fa fa-joomla"></i>&nbsp;Add Consumables</a>
				<a href="index.php?procedure&patient=<?= $hospital_no; ?>" class="btn btn-sm btn-success" style="font-size: 13px; color: white;"><i class="fa fa-scissors"></i> &nbsp;Procedure</a>
				<a href="document.php?token=<?= $hospital_no; ?>" class="btn btn-sm btn-primary" title="Patient's Document">&nbsp;<i class="fa fa-file"></i>&nbsp;Documents</a>
				<a href="#" class="btn btn-sm btn-danger" onclick="openModal_fx('patient-alert-modal')" arial-modal="patient-alert-modal">&nbsp;<i class="fa fa-exclamation-triangle" title="Set Alert"></i>Alert</a>
				<a href="#" class="btn btn-sm btn-primary" title="Vaccine" id="immunization-button">Vacine</a>
			<?php } ?>


			<?php if ($window_mode == 'bill') {
				$ur_l = "patient.php?hosp_no=$hospital_no";
				$l_ = "Return";
				$l_color = "danger";
				$icon = "reply-all";
			} else {
				$ur_l = "patient.php?hosp_no=$hospital_no";
				$l_ = "Refresh";
				$l_color = "default";
				$icon = "repeat";
			} ?>

			<a href="<?= $ur_l; ?>" class="btn btn-sm btn-w-m btn-<?= $l_color; ?>" style="font-size: 13px; color: white;"><i class="fa fa-<?= $icon; ?>"></i>&nbsp;&nbsp;<?= $l_; ?></a>

			&nbsp;:&nbsp;
			<?php if ($_SESSION['dialysis_visible'] == '1') { ?>
				<a href="dialysis.php?patient=<?= $hospital_no; ?>" class="btn btn-xs btn-w-m btn-primary" id="dialysis-request-btn" <?php ($isOnAppoint || $admission_info != null ? "" : "disabled") ?> style="color: white; font-size: 14px; ">DIALYSIS</a>
			<?php } ?>
			<a href="../admission_billing.php?emr=<?= $hospital_no; ?>" class="btn btn-sm btn-primary">
				View Billing Report
			</a>

		</td>

	</tr>
</table>


<div id="msg"></div>
<?php
$complain = null;
$stmt = $db->prepare("SELECT complain,prepared_by FROM c_d_remarks 
	WHERE hospital_no=? and cat_type='DH' and complain!='' and
	(complain not like '%none%' or complain not like '%NULL%' or complain not like '%nil%' 
	or complain not like '%nil%') ORDER BY sn DESC LIMIT 1");
$stmt->execute(array($hospital_no));
if ($stmt->rowCount() > 0) {
	$allergies_row = $stmt->fetch(PDO::FETCH_ASSOC);
	$complain = $allergies_row['complain'];
	$count_me = strlen($complain);
	if ($count_me > 0) {
?>

		<span class="blink"><strong style="color: chocolate; ">Allergies:</strong> </span>
		<?php echo '<strong>' . $allergies_row['complain'] . '</strong>' . '<br><small><i>Entered by</i>: ' . $allergies_row['prepared_by'] . '</small>'; ?>
<?php }
} ?>