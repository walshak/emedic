<br>


<div class="row">
	<div class="col-sm-6 b-r">

		<?php if ($isOnAdmission == true && $adm_status == 3) { ?>
			<?php if ($_SESSION['fullname'] != '' and $_SESSION['id'] != '') { ?>

				<table width="100%">
					<tr>
						<td>
							<h4 align="right"><strong style="color:black; font-size: 16px;">View Patient's Charts</strong>: &nbsp;</h4>
						</td>
						<td>
							<select name='show_chart_view' id='show_chart_view' class="form-control" required style="font-size: 17px; border-color:darkolivegreen; ">
								<option selected='selected' value=''>-- Select Chart Type and Click Show>>--</option>
								<option value='Drug'>Drug/Injection Chart</option>
								<option value='Fluid'>Fluid Chart</option>
								<option value='Feeding'>Feeding Chart</option>
								<option value='Oxygen'>Oxygen Chart</option>
								<option value='Blood_sugar'>Blood Sugar Chart</option>
								<option value='adm_note'>Admission Note/Procedure Chart</option>
								<option value='ward_to_icu_chart'>Ward to ICU/Treatment Transfer Chart</option>
								<option value='seizure'>Seizure Chart</option>
							</select>
						</td>
						<td>&nbsp;</td>
						<td>
							<label for=""></label>
							<input type="button" name="previous" value="Show Charts" onClick="view_charts()" class="btn btn-success" />
						</td>
					</tr>
				</table>
				<br>

				<div class="form-group">
					<strong style="font-size:17px; "> Select Document type: <i style="color: orangered;"><u>Ward Round / Plan / Treatment</u></i>&nbsp; & Type below: </strong>
					<select class="form-control" name="note_type_" id="note_type_" style="font-size: 17px; border-color:darkolivegreen; ">
						<option value="">-- Select Here --</option>
						<option value="ward_round">Doctor's Ward Round</option>
						<option value="plan">Doctor's Plan</option>
						<option value="treatment">New Treatment</option>

					</select>
				</div>

				<div class="form_sep">
					<div id="edit___mode" style="color: red;"></div>
					<div name="mgt_notes_wards" id="mgt_notes_wards" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px; border-color: black;"></div>
				</div>



				<hr>
				<h3 style="color: black;">Update or Add Patient Diagnosis (Optional)</h3>

				<label for="search_icdcodes_input" style="color: sienna;">
					Enter a diagnosis name or ICD-10/ICPC-2 code, then click “+” to add.
				</label>


				<input type="hidden" value="1" name="diagnosis_group_ward_round" id="icdcode_group_ward_round">


				<div class="p-4" style="padding: 10px;">


					<table width="100%">
						<tr>
							<td width="95%">

								<textarea
									class="typeahead form-control"
									data-provide="typeahead"
									id="search_icdcodes_input_ward_round"
									name="search_icdcodes_input_ward_round"
									cols="30"
									rows="1"
									style="font-size:17px"
									placeholder="Enter Diagnosis Here">
</textarea>
							</td>
							<td width="5%">
								<a class="btn btn-success" onclick="addDiagnosis_ward_rounds()" id="addSelectedDrugBtn" title="Add More Diagnosis" style="font-size: 16px;">
									<i class="fa fa-plus"></i> </a>
							</td>
						</tr>
					</table>

					<select name="comment" id="comment_ward_round" class="form-control" style="font-size:16px;">
						<option value="" selected>-- Select Comment --</option>
						<option value="Query">Query</option>
						<option value="Differential">Differential</option>
						<option value="Confirmed">Confirmed</option>
						<option value="N/A">Not Applicable</option>
					</select>

					<select name="comment2" id="comment2_ward_round" class="form-control" style="font-size:16px;">
						<option value="" selected>-- Select Comment II --</option>
						<option value="Acute">Acute</option>
						<option value="Chronic">Chronic</option>
						<option value="Recurrent">Recurrent</option>
						<option value="N/A">Not Applicable</option>
					</select>

					<input type="hidden" name="diagnosisInputList_input_ward_round" id="diagnosisInputList_input_ward_round" class="form-control">
					<input type="hidden" name="diagnosisInput_ward_round" id="diagnosisInput_ward_round" cols="30" rows="10">
					<div id="diagnosisSelected_ward_round"></div>
				</div>

				<div class="form_sep">
					<label for="reg_textarea_message" class="" style="color: black;">Diagnosis (Other Comments):</label>
					<textarea name="comment3" id="comment3_ward_round" cols="30" rows="3" class="form-control" style="font-size:15px" placeholder="Other Diagnosis Here"></textarea>
				</div>

				<br>
				<div class="form_sep">
					<div class="pull-left">
						<button class="btn btn-primary" id="save-ward_round-note">Save Note</button>
					</div>
				</div>





				<input type="hidden" id="doctor_name" value="<?= $_SESSION['fullname']; ?>">
				<input type="hidden" id="doctor_id" value="<?= $_SESSION['id']; ?>">
				<input type="hidden" name="id" id="notes_sn">
				<input type="hidden" name="date_entry" id="date_entry">
				<input type="hidden" name="date_entry2" id="date_entry2">
				<input type="hidden" name="mode" id="mode">
				<input type="hidden" name="notes_type" id="notes_type">
				<input type="hidden" name="hospital_no" id="hospital_no" value="<?= $hospital_no; ?>">
				<input type="hidden" name="appointment_number" id="appointment_number" value="<?= $appointment_number; ?>">

			<?php } else {
				header("location:index.php");
			} ?>


		<?php } else { ?>

			<?php if ($adm_status == '0' and ($_SESSION['rights'] == 'AD' or $_SESSION['rights'] == 'DR')) { ?>
				<h3>Patient Not On-Admission</h3>
				<small style="color: brown; ">Request Pending at Nursing Station</small>
			<?php } else { ?>
				<h4 align="center" style="color: crimson; ">Patient is NOT currently On-Admission</h4>
		<?php }
		} ?>


	</div>
	<div class="col-sm-6">

		<table width="100%">
			<tr>
				<td>
					<?php if ($isOnAdmission == true && $adm_status == 3) { ?>
						<?php $details = $appointment_number . '__' . $hospital_no . '__' . $patient_insurance . '__' . $patient_access_type . '__' . $interest . '__' . $add_minus; ?>
						<input type="button" name="edit_users" value="Review and Bill Patient" data-target="#modal" id="<?php echo $details; ?>"
							<?php if ($_SESSION['rights'] == 'NS') { ?> disabled <?php } ?> class="btn btn-primary service_review_link" />
					<?php } ?>
				</td>

				<td>
					<input type="button" name="previous" value="View Admitted Reason(s)" data-target="#modal" id="<?php echo $hospital_no; ?>"
						class="btn btn-danger previous_adm_reason" />
				</td>
				<td>
					<?php if ($isOnAdmission == true && $adm_status == 3) { ?>
						<?php
						$setdate = date('Y-m-d H:i:s');
						$date1 = new DateTime($setdate);
						$date2 = new DateTime($date_admit);
						$diff = $date2->diff($date1);
						$day = $diff->format('%a');
						$hr = $diff->format('%h');

						echo '' . $diff->format('<strong style="font-size:15px">%a</strong> Day(s)<strong style="font-size:15px"> %h</strong> hr(s) / <strong>On-Admission</strong>') . '<br><strong style="font-size:14px; color:#C60">
		Date Admitted:</strong>
		<strong style="font-size:14px;">' . date('d M,Y h:i:s a', strtotime($date_admit)) . '</strong>'; ?>
					<?php } ?>
				</td>
			</tr>
		</table>

		<?php if ($isOnAdmission == true && $adm_status == 3) { ?>
			<hr>

			<div id="data_displayed_ward_round">
				<strong style="color: red;">Loading Doctor's Ward Round and other Notes ... Please Wait!</strong>
			</div>

		<?php } ?>



	</div>
</div>



<div class="modal inmodal fade" id="discharge_request_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="min-height: 500px;width:90%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""> Discharge Patient</h4>
			</div>
			<div class="modal-body">
				<form action="" method='POST' id='subject' name='subject'>
					<div>
						<label for=""> Select Discharge Status:</label>
						<select name='discharge_status' id='discharge_status' class="form-control" required>
							<option selected='selected' value=''>Search & Select Discharge Status</option>
							<option value='Recovered'>Recovered</option>
							<option value='Improved'>Improved</option>
							<option value='Death'>Death</option>
							<option value='Home'>Home</option>
							<option value='Discharge'>Discharged</option>
							<option value='Mortuary'>Mortuary</option>
							<option value='Transfered'>Transfered</option>
							<option value='Referred'>Referred</option>
							<option value='Signed Against MD'>Signed Against MD</option>
							<option value='Not Applicable'>Not Applicable</option>
						</select>
					</div>
					<br>
					<div id="treatment_summary_wrapp">
						<label for="discharge_note"> Refferal Notes:</label>
						<textarea name="refferal_notes" id="refferal_notes" style="min-height:150px" cols="30" rows="5" class="form-control trumbowygEditor" required>
						<div>
							
							<table border="2" width="100%">
									
									<tr>
										<th><b> Name of Patient: <?= isset($patient_name) ? $patient_name : ''; ?></b> </th>
										<th><b> Hospital No:</b> <?= $hospital_no; ?></th>
									</tr>
									<tr>
										<th><b> Phone: <?= isset($patient_info->phone) ? $patient_info->phone : ''; ?></b> </th>
										<th><b> NOK Phone:</b> </th>
									</tr>
									<tr>
										<th><b> Date Admitted: <?= isset($date_admit) ? date('D d M, Y', strtotime("" . $date_admit)) : ''; ?></b> </th>
										<th><b> Date Referred: <?= date('D d M, Y'); ?></b> </th>
									</tr>
									<tr>
										<td colspan="2"><b> Reason for Admission: <?= isset($reason_adm) ? $reason_adm : ''; ?></b> </td>
									</tr>
									<tr>
										<td colspan="2"><b> Diagnosis at Attendance:</b> </td>
									</tr>
									<tr>
										<td colspan="2">
											<div>
													<b> Treatment Summary:</b> 		
													<br>
													<br>
													<br>
													<br>
													<br>
													<br>
													<br>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<div>
													<b> Reason for for Referal:</b> 		
													<br>
													<br>
													<br>
													<br>
													<br>
													<br>
													<br>
													<br>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<div >
													<h5 style="text-align: right;"> REFERRING DOCTOR: <?= $_SESSION['fullname']; ?> </h5>		
													
											</div>
										</td>
									</tr>
							</table>

						</div>
				</textarea>
					</div>
					<!-- <div id="diagnosis_at_admittance_wrap">
           		 <label for="discharge_note"> Diagnosis At Admittance:</label>
                <textarea name="diagnosis_at_admittance" id="diagnosis_at_admittance" cols="30" rows="5" class="form-control trumbowygEditor" required></textarea>
            </div> -->
					<!-- <div id="treatment_summary_wrap">
           		 <label for="discharge_note"> Treatment Summary:</label>
                <textarea name="treatment_summary" id="treatment_summary" style="max-height:150px" cols="30" rows="5" class="form-control trumbowygEditor"  required>
						<div>
							<h4>Treatment Summary:</h4>
							<br>
							<br>
							<br>
							<h4> Reason for Referal:</h4>
						</div>
				</textarea>
            </div> -->
					<div id="discharge_note_wrap">

						<label for="discharge_note"> Discharge Note:</label>
						<textarea name="discharge_note" id="discharge_note" cols="30" rows="5" class="form-control " required></textarea>
					</div>
					<!-- <div id="reason_for_referal_wrap">
            <label for="discharge_note"> Reason for Referal:</label>
                <textarea name="reason_for_referal" id="reason_for_referal" cols="30" rows="5" class="form-control trumbowygEditor" required></textarea>
            </div> -->
					<hr>
					<input type="hidden" name="admission_sn" value="<?php echo $row["sn"]; ?>" />
					<input type="hidden" name="hosp_no" value="<?php echo $hospital_no; ?>" />
					<input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>" />
					<input type="hidden" name="appt_no" value="<?php echo $appointment_number; ?>" />
					<input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>" />
					<input type="hidden" name="admit_type" value="<?php echo $admit_type; ?>" />
					<p class="text-center">
						<button class='btn btn-success btn-sm' type='submit' name='send_discharge_request_btn' id='send_discharge_request_btn'>Send Discharge Request</button>
						<button class='btn btn-primary btn-sm' type='submit' name='send_discharge_request_btn' id='send_discharge_request_btn2'>Send Discharge & Print Request</button>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#treatment_summary_wrapp').hide();
		$('#send_discharge_request_btn2').hide();
		// $('#reason_for_referal_wrap').hide();
		// $('#treatment_summary_wrap').hide();
		function watchReferal(discharge_status) {
			if (discharge_status === 'Referred') {
				$('#treatment_summary_wrapp').show();
				$('#send_discharge_request_btn').hide();
				$('#send_discharge_request_btn2').show();

				// $('#diagnosis_at_admittance_wrap').show();
				// $('#reason_for_referal_wrap').show();
				// $('#treatment_summary_wrap').show();
				$('#discharge_note_wrap').hide();

				$('#discharge_note').removeAttr('required');
				// $('#treatment_summary').attr('required', 'required');
				// $('#reason_for_referal').attr('required', 'required');
				// $('#diagnosis_at_admittance').attr('required', 'required');
			} else {
				$('#send_discharge_request_btn').show();
				$('#send_discharge_request_btn2').hide();
				$('#reason_for_referal_wrap').hide();
				// $('#diagnosis_at_admittance_wrap').hide();
				// $('#reason_for_referal_wrap').hide();
				// $('#treatment_summary_wrap').hide();
				$('#discharge_note_wrap').show();

				$('#discharge_note').attr('required', 'required');
				// $('#treatment_summary').removeAttr('required');
				// $('#reason_for_referal').removeAttr('required');
				// $('#diagnosis_at_admittance').removeAttr('required');
			}
		}

		// Watch for changes in the discharge status dropdown
		$('#discharge_status').change(function() {
			const selectedStatus = $(this).val();
			watchReferal(selectedStatus);
		});
	});
</script>