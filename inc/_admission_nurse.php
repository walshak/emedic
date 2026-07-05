<br>
<div class="row">
	<div class="col-sm-12">
		<table width="100%">
			<tr>
				<td width="14%">
					<input type="button" name="previous" value="View Past Admitted Reason(s)" data-target="#modal" id="<?php echo $hospital_no; ?>"
						class="btn btn-success previous_adm_reason" />
				</td>

				<?php if ($admission_info != null && $adm_status == 3) { ?>

					<td width="9%">

						<?php
						$cr = '1';
						$paystatus = '0';
						$stmt = $db->prepare("SELECT sn FROM patient_ap_services WHERE hospital_no=:hosp_no and cr=:cr and paystatus=:paystatus");
						$stmt->bindValue(':hosp_no', $hosp_no, PDO::PARAM_STR);
						$stmt->bindValue(':cr', $cr, PDO::PARAM_STR);
						$stmt->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
						$stmt->execute();
						?>

						<input type="button" name="dischgr" value="DISCHARGE" data-target="#modal"
							id="<?php echo $hosp_no . '___' . $room_bed_sn; ?>" <?php if ($discharge_request == 0) { ?>disabled<?php } ?>
							class="btn btn-<?php if ($discharge_request == 0) { ?>default<?php } else { ?>danger<?php } ?> btn-bg discharge_patient" />


					</td>
					<td width="25%">
						<?php
						$setdate = date('Y-m-d H:i:s');
						$date1 = new DateTime($setdate);
						$date2 = new DateTime($date_admit);
						$diff = $date2->diff($date1);
						$day = $diff->format('%a');
						$hr = $diff->format('%h');

						if ($admit_type == 'admit_p') {

							echo '' . $diff->format('<strong style="font-size:15px">%a</strong> Day(s)<strong style="font-size:15px"> %h</strong> hr(s) / <strong>On-Admission</strong>') . '<br><strong style="font-size:14px; color:#C60">
		Date:</strong>
		<strong style="font-size:14px;">' . date('d M, Y h:i:s a', strtotime($date_admit)) . '</strong>'; ?>

							<?php if ($_SESSION['rights'] == 'NS' and $_SESSION['unit_head'] == '1') {
								if ($discharge_request == 0 and $day == 0 and $hr <= 23) { ?>
									<a data-toggle="modal" class="btn btn-warning btn-xs" href="#change_date">Edit Date</a>
						<?php }
							}
						} else {
							echo '' . $diff->format('<strong style="font-size:15px">%a</strong> Day(s)<strong style="font-size:15px"> %h</strong> hr(s) / <strong style="color:red; ">On-Observation</strong>') . '<br><strong style="font-size:14px; color:#C60">
							Date:</strong>
							<strong style="font-size:14px;">' . date('d M, Y h:i:s a', strtotime($date_admit)) . '</strong>';
						}
						?>
					</td>

					<td width="20%">
						<div style=" font-size:14px; color:#300"><strong><?php echo $room_bed; ?></strong></div>
						<?php if ($_SESSION['rights'] == 'NS' && $admit_type == 'admit_o') { ?>
							<a data-toggle="modal" class="btn btn-danger btn-xs" href="#change_accomodation">Change Admission Type</a>
						<?php } ?>
						<?php if ($discharge_request == 0 && $_SESSION['rights'] == 'NS' && $admit_type == 'admit_p') { ?>
							<a data-toggle="modal" class="btn btn-warning btn-xs" href="#change_accomodation">Change Room / Dept</a>
							<?php if ($billable_ == 'yes' and $_SESSION['unit_head'] == '1') { ?>
								<a href="patient.php?hosp_no=<?= $hospital_no; ?>&stopbill" onclick="return confirm('Are you sure you want to STOP Daily Accommodation Billing?')" class="btn btn-danger btn-xs">Stop Bill</a>
							<?php } ?>
						<?php } ?>
					</td>
					<td>
						<table width="100%" style="font-size:14px">
							<tr>
								<td><strong>Care Giver:</strong></td>
								<td><?php echo $care_giver; ?>

									<a data-toggle="modal" class="btn btn-danger btn-xs" href="#change_care_giver"><?php echo ($care_giver == '') ? 'Add Care Giver' : 'Change'; ?></a>

								</td>
							</tr>
							<tr>
								<td><strong>Phone / Relationship:</strong></td>
								<td><?php echo $care_giver_phone . ' / ' . $relationship; ?></td>
							</tr>
						</table>
					</td>


				<?php } else { ?>

					<td>


					</td>
				<?php } ?>

			</tr>
		</table>


		<?php
		if (!empty($discharge_note and $discharge_request == 1)) {
			echo '<strong>Discharge Note:</strong> ' . $discharge_note;
		}

		if ($stmt->rowCount() > 0 and  $discharge_request == 1) {
			echo '<hr>';
		?>
			<div class="warning">
				<p style="color: red;"><strong>Attention: &nbsp;</strong>Patient has outstanding bill to Pay!</p>

			</div>
		<?php } ?>
	</div>
</div>
<hr>
<div class="row">

	<div class="col-lg-6">
		<?php if ($admission_info != null && $adm_status == 3) { ?>
			<?php if ($_SESSION['fullname'] != '' and $_SESSION['id'] != '') { ?>

				<div class="form_sep">
					<div id="edit__mode" style="color: red;"></div>
					<div name="mgt_notes_nurse" id="mgt_notes_nurse" class="trumbowygEditor" cols="30" rows="10" style="font-size:17px;"></div>
				</div>

				<div class="form_sep">
					<div class="pull-left">
						<button class="btn btn-primary" id="save-progress-note">Save Note</button>
					</div>
				</div>
				<input type="hidden" name="id" id="notes_sn">
				<input type="hidden" name="mode" id="mode">
				<input type="hidden" id="date_entry">
				<input type="hidden" id="date_entry2">

				<input type="hidden" id="doctor_name" value="<?= $_SESSION['fullname']; ?>">
				<input type="hidden" id="doctor_id" value="<?= $_SESSION['id']; ?>">


				<input type="hidden" name="mode_typpe" id="mode_typpe" value="save_progress_note_button">
				<input type="hidden" name="hospital_no" id="hospital_no" value="<?= $hospital_no; ?>">
				<input type="hidden" name="appointment_number" id="appointment_number" value="<?= $appointment_number; ?>">
			<?php } else {
				header("location:index.php");
			} ?>


			<?php } else {

			if (strtoupper($_SESSION['h_code']) != 'ZMKC') {
				$stmt = $db->prepare("SELECT * FROM diagnosis WHERE item = 'Birth injury to femur (ICD10: P132)' AND description = '1'");
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					$admission_ = $stmt->rowCount();
					/// disable admission ///
				} else {
					$admission_ = 0;
				}
			} else {
				$admission_ = 0;
			}

			if ($adm_status == '0' && $_SESSION['rights'] == 'NS' && $admission_ == 0):

				if (isset($_GET['error_regamt'])) {
					$require_amount_b4_adm = $_GET['error_regamt'];
					$min = $_GET['min'];
			?>

					<div class='alert alert-danger'>
						<h3>Insufficient deposit. A minimum of =N= <?php echo number_format($require_amount_b4_adm); ?>
							from the required =N=<?php echo number_format($min); ?> deposit must be paid before the patient can be admitted.</h3>
					</div>
				<?php
				}
				$ty_p = ($admit_type == 'admit_p') ? '<i>IN-PATIENT ADMISSION</i>' : '<i>24 HOURS EMERGENCY/ADMISSION ON OBSERVATION</i>' ?>

				<h3 style="color: crimson;">
					This patient has been sent for <?= $ty_p; ?>. See the reason below:
				</h3>

				<hr>

				<h3><?= htmlspecialchars($reason_adm); ?></h3>
				<?php
				// Convert admission date to DateTime
				$dateAdmitObj = new DateTime($date_admit);
				$now = new DateTime();
				$diff = $now->diff($dateAdmitObj);
				$ago = '';
				if ($diff->days > 0) {
					$ago .= $diff->days . ' day' . ($diff->days > 1 ? 's ' : ' ');
				}
				if ($diff->h > 0) {
					$ago .= $diff->h . ' hour' . ($diff->h > 1 ? 's ' : ' ');
				}
				if ($diff->i > 0 && $diff->days == 0) {
					$ago .= $diff->i . ' minute' . ($diff->i > 1 ? 's ' : ' ');
				}
				$ago = trim($ago) . ' ago';
				$formattedDate = $dateAdmitObj->format("d-m-Y g:i A");
				?>
				<p>
					<i>Requested by:</i>
					<?= htmlspecialchars($doc_incharge); ?> —
					<?= $formattedDate; ?> (<?= $ago; ?>)
				</p>
				<hr>
				<?php if ($admit_type == 'admit_o'): ?>
					<table>
						<tr>
							<td>

								<b style="color:brown; font-size:14px; ">24hrs Accident and Emergency</b><br>
								<a class="btn btn-danger" data-toggle="modal" href="#admit_modal">
									Admit Patient to Observation
								</a>
							</td>
							<td>
								&nbsp; <br><b> - OR - </b> &nbsp;
							</td>
							<td>
								<b style="color:brown; font-size:14px; ">.</b><br>
								<a class="btn btn-warning"
									href="patient.php?hosp_no=<?= $hospital_no ?>&adm_req&__sn_=<?= $admission_sn ?>"
									onclick="return confirm('Are you sure you want to change this patient to In-Patient (Main Admission)?');">
									Change to In-Patient (Main Admission)
								</a>

							</td>
						</tr>
					</table>

				<?php else: ?>
					<a class="btn btn-danger" data-toggle="modal" href="#admit_modal">
						Click Here to Admit Patient
					</a>
				<?php endif; ?>

				<?php if (strtoupper($services_name) == 'EMERGENCY'): ?>
					&nbsp;&nbsp; : &nbsp;&nbsp;
					<a href="patient.php?hosp_no=<?= urlencode($hospital_no); ?>&del_admission_request"
						class="btn btn-warning"
						onclick="return confirm('Are you sure you want to DELETE this admission request?');">
						Delete Admission Request
					</a>
				<?php endif; ?>

			<?php else: ?>

				<hr>
				<h4 style="color:brown;">Admission rights are disabled.</h4>

			<?php endif; ?>
		<?php } ?>
	</div>

	<div class="col-lg-6">
		<div class="" id="_progress_notes_hx"></div>
	</div>

</div>




<div class="modal inmodal" id="discharge_patient_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Discharge Patient</h4>
			</div>
			<div class="modal-body" id="discharge_patient_body">

			</div>
		</div>
	</div>
</div>


























<?php if ($_SESSION['rights'] == 'NS') { ?>

	<div class="modal inmodal" id="change_date" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">


		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Change Admission Date </h4>
				</div>
				<div class="modal-body" id="">

					<form action="patient.php?hosp_no=<?= $hosp_no; ?>&adm_req" method="POST" id="subject" name="subject">

						<div class="form_sep">
							<label for="reg_select" class="req">Select New Date</label>
							<input type="date" name="date_adm_change" class="form-control" required />
							<input type="time" name="time_adm_change" class="form-control" required />
						</div>

						<br />
						<div align="center">
							<button class="btn btn-success" type="submit" name="change_adm_date_time" id="change_adm_date_time" onclick="return confirm('Are you sure you want to CHANGE Accommodation Date?')">Save New Date</button>
						</div>
						<input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
						<input type="hidden" name="hosp_no" value="<?php echo $hospital_no; ?>" />
						<input type="hidden" name="admission_sn" value="<?php echo $admission_sn; ?>" />
						<input type="hidden" name="billable_" value="<?php echo $billable_; ?>" />
					</form>
				</div>
			</div>
		</div>
	</div>


	<div class="modal inmodal" id="change_accomodation" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">


		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				</div>
				<div class="modal-body" id="">

					<form action="patient.php?hosp_no=<?= $hosp_no; ?>&adm_req"
						method="POST"
						id="subject"
						name="subject">
						<h4>Change Accommodation/Add or Remove Nursing Services</h4>


						<div class="form_sep">
							<label for="reg_select" class="">Select Floor</label>
							<select name="floor2" id="floor2" class="form-control">
								<option selected="selected" value="">Select Floor</option>
								<?php

								$status = '0';
								$stmtx = $db->prepare("SELECT distinct tips FROM bed_mgt where tips!='' order by tips");
								$stmtx->execute();
								$stmt_logCOUNT = $stmtx->rowCount();
								if ($stmt_logCOUNT > 0) {
									while ($row_bed = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
										<option value="<?php echo $row_bed["tips"]; ?>"><?php echo $row_bed["tips"]; ?></option>
								<?php }
								} ?>


							</select>
						</div>

						<div class="form_sep">
							<label for="reg_select" class="">Available Rooms</label>
							<select name="room_bed2" id="room_bed2" class="form-control">
								<option value="">-- Not Applicable --</option>
							</select>
						</div>


						<div class="form_sep">
							<label for="reg_select" class="req" style="font-size:14px">Choose/Remove Nursing Services To Be Billed Authomatically!</label><br>
							<strong style="color:chocolate;">Add or remove Nursing Services to adjust auto-billing without altering accommodations</strong>
							<?php


							$nursing_services = array();
							if ($admit_type == 'admit_p') {
								$att = "and invoice_status=0";
							} else {
								$att = "and invoice_status=1";
							}
							// Fetch distinct drug_sn for nursing services
							$stmt2 = $db->prepare("
								SELECT DISTINCT drug_sn 
								FROM patient_ap_services 
								WHERE hospital_no = :hosp_no 
								AND (remarks = 'auto_deduct' OR remarks = 'auto_deduct2')
							");
							$stmt2->execute([':hosp_no' => $hosp_no]);

							// Store nursing services in an associative array (fast lookup)
							$nursing_services = array_flip($stmt2->fetchAll(PDO::FETCH_COLUMN));

							// Fetch price table rows
							$stmtx = $db->query("
								SELECT * 
								FROM prices_table 
								WHERE ext_price > 0 
								AND hosp_price > 0 
								AND nusing_setauth = 1
							");

							while ($row_emp = $stmtx->fetch(PDO::FETCH_ASSOC)) {
								$isChecked = isset($nursing_services[$row_emp['sn']]);
							?>
								<input type="checkbox"
									name="services_name[]"
									value="<?= htmlspecialchars($row_emp['sn']) ?>"
									style="height: 15px; width: 15px;"
									<?= $isChecked ? 'checked' : '' ?>>
								&nbsp;<?= htmlspecialchars($row_emp['item_service']) ?>
								(<?= number_format($row_emp['hosp_price']) ?>)
								<?= $isChecked ? '<strong style="color: red;">Exist!</strong>' : '' ?>
								<br>
							<?php
							}
							?>


						</div>




						<br />
						<div align="center">
							<?php if ($admit_type == 'admit_o') { ?>
								<button class="btn btn-success" type="submit" name="change_adm_admit_p" id="change_adm_admit_p" onclick="return confirm('Are you sure you want to CHANGE Admission Type  ?')">Change Admission</button>
							<?php } else { ?>
								<button class="btn btn-success" type="submit" name="change_adm" id="change_adm" onclick="return confirm('Are you sure you want to CHANGE Accommodation ?')">Save Change</button>
							<?php } ?>
						</div>
						<input type="hidden" name="room_bed_sn" value="<?php echo $room_bed_sn; ?>" />
						<input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
						<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
						<input type="hidden" name="ap_type" value="<?php echo $patient_access_type; ?>" />
						<input type="hidden" name="interest" value="<?php echo $interest; ?>" />
						<input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
						<input type="hidden" name="admission_sn" value="<?php echo $admission_sn; ?>" />
						<input type="hidden" name="add_minus" value="<?php echo $add_minus; ?>" />
						<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />
						<input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
						<input type="hidden" name="billable" value="<?php echo $billable_; ?>" />
						<input type="hidden" name="admit_type" value="<?php echo $admit_type; ?>" />
						<input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>" />
						<input type="hidden" name="admission_date" value="<?php echo $date_admit; ?>" />
					</form>
				</div>
			</div>
		</div>
	</div>



	<div class="modal inmodal" id="change_care_giver" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body" id="">

					<form action="patient.php?hosp_no=<?= $hosp_no; ?>&adm_req" method="POST">
						<h4>Care Giver Information</h4>
						<div class="form_sep">
							<label for="reg_select" class="">Primary Care Giver:</label>
							<input type="text" id="care_giver" name="care_giver_info" maxlength="50" class="form-control" value="<?= $care_giver; ?>" required>
						</div>
						<div class="form_sep">
							<label for="reg_select" class="">Phone Number:</label>
							<input type="text" id="phone" name="phone_info" maxlength="11" class="form-control" value="<?= $care_giver_phone ?>" required>
						</div>
						<div class="form_sep">
							<label for="reg_select" class="">Relationship:</label>
							<input type="text" id="relation" name="relation_info" maxlength="40" class="form-control" value="<?= $relationship ?>" required>
						</div>

						<div class="form_sep">
							<button class="btn btn-success" type="submit" name="change_care_giver_" id="" onclick="return confirm('Are you sure you want to Save/Update ?')">Save</button>
						</div>

						<input type="hidden" name="hosp_no_info" value="<?php echo $hosp_no; ?>" />
						<input type="hidden" name="admission_sn" value="<?php echo $admission_sn; ?>" />

					</form>
				</div>
			</div>
		</div>
	</div>



	<div class="modal inmodal" id="admit_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="">Admission Request</h4>
				</div>
				<div class="modal-body" id="adm_request">

					<form action="patient.php?hosp_no=<?= $hosp_no; ?>&adm_req" method="POST" id="subject" name="subject">

						<?php if ($admit_type == 'admit_p') { ?>

							<div class="form_sep">
								<label for="reg_select" class="req">Select Floor</label>
								<select name="floor" id="floor" class="form-control" required>
									<option selected="selected" value="">Select Floor</option>
									<?php

									$status = '0';
									$stmtx = $db->prepare("SELECT distinct tips FROM bed_mgt where tips!='' order by tips");
									$stmtx->execute();
									$stmt_logCOUNT = $stmtx->rowCount();
									if ($stmt_logCOUNT > 0) {
										while ($row_bed = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $row_bed["tips"]; ?>"><?php echo $row_bed["tips"]; ?></option>
									<?php }
									} ?>


								</select>
							</div>


							<div class="form_sep">
								<label for="reg_select" class="req">Available Rooms</label>
								<select name="bed_avail" id="bed_avail" class="form-control" required>
									<option value="">-- Not Applicable --</option>
								</select>
							</div>

							<div class="form_sep">
								<table width="100%">
									<tr>
										<td>
											<div class="form_sep">
												<label for="reg_input_no" class="req">Admission Date</label>

												<div class="form-group" id="data_1">
													<input type="date" name="admit_date" id="admit_date" class="form-control" maxlength="10" value="<?php echo date("Y-m-d"); ?>" required>
												</div>
											</div>
										</td>
										<td>
											<div class="form-group">
												<label for="reg_select" class="req">Admission Time (24hrs Format)</label>
												<input type="time" name="admit_time" id="admit_time" class="form-control" value="<?php echo date('H:i') ?>" maxlength="5" required>
											</div>

										</td>
									</tr>
								</table>

							</div>

						<?php } ?>

						<div class="form_sep">
							<label for="reg_select" class="req" style="font-size:14px">Choose Nursing Services To Be Billed Authomatically!</label><br>
							<?php

							$price_table = 'Nursing Services';
							$stmtx = $db->prepare("SELECT * FROM prices_table 
		WHERE price_table=:price_table and ext_price > 0 and hosp_price > 0 and nusing_setauth=1");
							$stmtx->bindValue(':price_table', $price_table, PDO::PARAM_STR);
							$stmtx->execute();
							?>



							<?php while ($row_emp = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
								<input type="checkbox" name="services_name[]" value="<?php echo $row_emp['sn']; ?>" style="height: 15px; width: 15px;">
								&nbsp;<?php echo $row_emp['item_service']; ?><br>


							<?php } ?>





						</div>




						<div class="form_sep">

							<label for="reg_select" class="">Primary Care Giver:</label>
							<input type="text" id="care_giver" name="care_giver" maxlength="50" class="form-control">
						</div>
						<div class="form_sep">
							<label for="reg_select" class="">Phone Number:</label>
							<input type="text" id="phone" name="phone" maxlength="11" class="form-control">
						</div>
						<div class="form_sep">
							<label for="reg_select" class="">Relationship:</label>
							<input type="text" id="relation" name="relation" maxlength="40" class="form-control">
						</div>
						<br />
						<?php if ($admit_type == 'admit_p') { ?>
							<div class="form_sep">
								<strong>Check below to Auto Bill Patient Room/Nursing Service Daily</strong>
								<h4><input type="checkbox" name="isServiceBillable" id="isServiceBillable" value="billable" checked onclick="validate()">
									<span style="color: red;"> Is it Billable ?</span>
								</h4>
							</div>

						<?php } ?>

						<table width="100%">
							<tr>
								<td><button class="btn btn-success" type="submit" name="add_admit" id="add_admit" onclick="return confirm('Are you sure you want to Admit Patient ?')">
										<?php echo ($admit_type == 'admit_p') ? 'Admit Patient' : 'Admit Patient to Observation'; ?> </button>
								</td>
								<td>
									<div align="right">

										<a href="patient.php?hosp_no=<?= $hosp_no; ?>&adm_req" class="btn btn-danger">Close</a>
									</div>
								</td>
							</tr>
						</table>


						<input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
						<input type="hidden" name="admit_type" value="<?php echo $admit_type; ?>" />
						<input type="hidden" name="dept_id" value="<?php echo $_SESSION['dept_id']; ?>" />
						<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
						<input type="hidden" name="ap_type" value="<?php echo $ap_type; ?>" />
						<input type="hidden" name="interest" value="<?php echo $interest; ?>" />
						<input type="hidden" name="insurance_type" value="<?php echo $patient_insurance; ?>" />
						<input type="hidden" name="admission_sn" value="<?php echo $admission_sn; ?>" />
						<input type="hidden" name="add_minus" value="<?php echo $add_minus; ?>" />
						<input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>" />
						<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />
						<input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />

					</form>
				</div>
			</div>
		</div>
	</div>

<?php } ?>



<script type=text/javascript>
	function validate() {
		if (document.getElementById('isServiceBillable').checked) {
			/// alert("checked") ;
		} else {
			alert("Are you sure you dont want to charge for Accommodation and Nursing Services!")
		}
	}
</script>


<?php if (isset($_GET['openmodal'])): ?>
	<script>
		$(document).ready(function() {
			$('#admit_modal').modal('show');
		});
	</script>
<?php endif; ?>