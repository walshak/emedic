<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
$zero = 0;
$one = 1;
$queue = 'queue';


if (isset($_POST["employee_id"])) {
	// Assuming $db is your PDO database connection object
	$query = "SELECT * FROM tbl_employee WHERE id = :employee_id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(':employee_id', $_POST["employee_id"], PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	echo json_encode($row);
}


if (isset($_POST["enable_labTest_id"])) {
	// Assuming $db is your PDO database connection object
	$update = "UPDATE lab_scan SET status = :status WHERE sn = :sn";
	$stmt = $db->prepare($update);
	$stmt->bindParam(':status', $zero, PDO::PARAM_STR);
	$stmt->bindParam(':sn', $_POST['enable_labTest_id'], PDO::PARAM_INT);
	$stmt->execute();

	// Optionally handle the refresh and assignment to $sub (if needed)
	include("refresh.php");
	$sub = labs(); // Assuming this function returns something
}



if (isset($_POST["disable_labTest_id"])) {
	// Assuming $db is your PDO database connection object
	$update = "UPDATE lab_scan SET status = :status WHERE sn = :sn";
	$stmt = $db->prepare($update);
	$stmt->bindParam(':status', $one, PDO::PARAM_STR);
	$stmt->bindParam(':sn', $_POST['disable_labTest_id'], PDO::PARAM_INT);
	$stmt->execute();

	// Optionally handle the refresh and assignment to $sub (if needed)
	include("refresh.php");
	$sub = labs(); // Assuming this function returns something
}


if (isset($_POST["field_id"])) {
	// Assuming $db is your PDO database connection object
	$query = "SELECT * FROM lab_scan_fields WHERE sn = :field_id ORDER BY sn";
	$stmt = $db->prepare($query);
	$stmt->bindParam(':field_id', $_POST["field_id"], PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	echo json_encode($row);
}



if (isset($_POST["field_id_opt"])) {
	// Assuming $db is your PDO database connection object
	$query = "SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_id_opt";
	$stmt = $db->prepare($query);
	$stmt->bindParam(':field_id_opt', $_POST["field_id_opt"], PDO::PARAM_INT);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		echo '<strong>Current List: </strong><br><br>';
		while ($row_d = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo ' - ' . htmlspecialchars($row_d['options']) . '<br>';
		}
	} else {
		echo 'No Data';
	}
}



if (isset($_POST["labrequest_no_results"]) or isset($_POST["labrequest_no_results_cancel"])) {
	if (isset($_POST["labrequest_no_results"])) {
		$labrequest_no = $_POST["labrequest_no_results"];
	} elseif (isset($_POST["labrequest_no_results_cancel"])) {
		$labrequest_no = $_POST["labrequest_no_results_cancel"];
		$status = "cancel";
	}

	//

	$stmt_lb = $db->query("SELECT patient,patient_name,test_id FROM lab_manage WHERE labrequest_no = :labrequest_no");
	$stmt_lb->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	$stmt_lb->execute();

	if ($stmt_lb->rowCount() > 0) {
		$row_dd = $stmt_lb->fetch(PDO::FETCH_ASSOC);
		$patient_no = $row_dd['patient'];
		$patient_name = $row_dd['patient_name'];
		$test_id = $row_dd['test_id'];
		// Use fetched data as needed
	} else {
		// Handle case where no data is found
	}
	/////////////-------------------------------------------------------------------------------------------

?>

	<h4>Patient # / Name: &nbsp; &nbsp; <?php echo $patient_no . ' / ' . $patient_name; ?></h4>
	<hr>
	<?php

	$stmtChk = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :labrequest_no AND lab_combos = '1'");
	$stmtChk->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	$stmtChk->execute();

	if ($stmtChk->rowCount() == 0) {
	?>

		<?php

		///// check if there is existing resulst
		$stmt_rlstX = $db->prepare("SELECT * FROM lab_scan_input_results_old WHERE lab_request_no = :labrequest_no");
		$stmt_rlstX->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
		$stmt_rlstX->execute();

		if ($stmt_rlstX->rowCount() > 0) { ?>
			<strong style="font-size:15px; color:#F00;">Previous Results Captured</strong>
			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>Field</th>
						<th>Previous Value</th>
						<th>Reference</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($row_dx = $stmt_rlstX->fetch(PDO::FETCH_ASSOC)) { ?>
						<tr>
							<td><?php echo $row_dx['value_title']; ?></td>
							<td><?php echo $row_dx['result']; ?></td>
							<td><?php if ($row_dx['value_ref'] != '') {
									echo $row_dx['value_ref'];
								} ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<hr>
		<?php } ?>

		<?php
		$stmt_rlstX = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :labrequest_no");
		$stmt_rlstX->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
		$stmt_rlstX->execute();

		if ($stmt_rlstX->rowCount() > 0) { ?>

			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>Field</th>
						<th>Value</th>
						<th>Reference</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($row_dx = $stmt_rlstX->fetch(PDO::FETCH_ASSOC)) { ?>
						<tr>
							<td><?php echo $row_dx['value_title']; ?></td>
							<td><?php echo $row_dx['result']; ?></td>
							<td><?php if ($row_dx['value_ref'] != '') {
									echo $row_dx['value_ref'];
								} ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<?php } else {

			$stmt_rlst = $db->prepare("SELECT * FROM lab_result_old WHERE lab_no = :labrequest_no");
			$stmt_rlst->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$stmt_rlst->execute();

			if ($stmt_rlst->rowCount() > 0) { ?>
				<strong style="font-size:15px; color:#F00">Previous Results Captured</strong>
				<table class="table table-striped table-bordered table-hover dataTables-example">
					<thead>
						<tr>
							<th>Field</th>
							<th>Previous Value</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($row_d = $stmt_rlst->fetch(PDO::FETCH_ASSOC)) { ?>
							<tr>
								<td> <?php echo $row_d['field_name'];
										if ($row_d['field_ref'] != '') {
											echo '<br>' . $row_d['field_ref'];
										} ?> </td>
								<td> <?php echo $row_d['field_value']; ?> </td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<hr>
			<?php } ?>


			<?php
			$stmt_rlst = $db->prepare("SELECT * FROM lab_result WHERE lab_no = :labrequest_no");
			$stmt_rlst->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$stmt_rlst->execute();

			if ($stmt_rlst->rowCount() > 0) { ?>
				<table class="table table-striped table-bordered table-hover dataTables-example">
					<thead>
						<tr>
							<th>Field</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($row_d = $stmt_rlst->fetch(PDO::FETCH_ASSOC)) { ?>
							<tr>
								<td> <?php echo $row_d['field_name'];
										if ($row_d['field_ref'] != '') {
											echo '<br>' . $row_d['field_ref'];
										} ?> </td>
								<td> <?php echo $row_d['field_value']; ?> </td>
							</tr>
						<?php } ?>
					</tbody>
				</table>

	<?php		}
		}
		$combo = 0;
	} else {
		/// Lab Combination Test ////
		$combo = 1;
	}
	?>

	<table class="table table-striped table-bordered table-hover dataTables-example">
		<thead>
		</thead>
		<tbody>


			<?php

			$stmt_lbx = $db->prepare("SELECT * FROM lab_result WHERE lab_no = :labrequest_no");
			$stmt_lbx->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$stmt_lbx->execute();
			$row_ddx = $stmt_lbx->fetch(PDO::FETCH_ASSOC);

			if ($row_ddx['notes'] != '') {
				echo '<tr><td>NOTE</td><td>' . $row_ddx['notes'] . '</td></tr>';
			}
			if ($row_ddx['comment'] != '') {
				echo '<tr><td>COMMENT</td><td>' . $row_ddx['comment'] . '</td></tr>';
			}
			if ($row_ddx['lab_sci_speciality'] != '') {
				echo '<tr><td>' . $row_ddx['lab_sci_speciality'] . '</td><td>' . $row_ddx['lab_sci_name'] . '</td></tr>';
			}
			if ($row_ddx['entered_by'] != '') {
				echo '<tr><td>Entered By</td><td>' . $row_ddx['entered_by'] . '</td></tr>';
			}

			$test_id = $row_dd['test_id'];
			$group_id = $row_dd['group_id'];

			$stmt2 = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_id AND field_type = 'report'");
			$stmt2->bindParam(':test_id', $test_id, PDO::PARAM_STR);
			$stmt2->execute();

			if ($stmt2->rowCount() > 0) {
				$edit_report = 1;
			} else {
				$edit_report = '';
			}


			?>


			<?php if ($row_dd['abnormal_results'] == 1) { ?>
				<div class="alert alert-danger">
					Attention Required. Abnormal Results
				</div>
			<?php } ?>


			<?php if ($row_dd['data_capture_status'] == 'reject') { ?>

				<input type="button" name="reject" value="Approve Results" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-success btn-xs approve_reject_confirmation" />

				|



				<?php if ($edit_report == 1 and $_SESSION['fullname'] == $row_ddx['entered_by']) { ?>
					<a href="fillrslt_sheet.php?edit=<?php echo $labrequest_no; ?>" class="btn btn-info btn-xs">Edit Results</a>
				<?php } elseif ($combo == 1 and $_SESSION['fullname'] == $row_ddx['entered_by']) { ?>
					<a href="fillrslt_sheet.php?cb=<?php echo $test_id . '/' . $labrequest_no . '/' . $patient_no; ?>" class="btn btn-info btn-xs">Edit Results</a>
				<?php } elseif ($_SESSION['fullname'] == $row_ddx['entered_by']) { ?>
					<input type="button" name="edit" value="Edit Results" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-info btn-xs edit_results" />
				<?php } else { ?>
					<strong>Editable by: </strong><?php echo $row_ddx['entered_by']; ?>
				<?php } ?>

				<hr>
			<?php } ?>


			<?php if ($row_dd['data_capture_status'] == 'result') { ?>

				<table width="100%">
					<tr>
						<td>
							<form method="POST" id="approve_results_form">
								<!--<div class="checkbox i-checks"><label> <input type="checkbox" name="abnormal" id="abnormal" value="1"> <i></i> Check If Results is Abnormal.</label></div>-->
								<br><br>

								<button class="btn btn-success btn-xs" type="submit" name="save">Approve Results</button>

								&nbsp;
								|
								&nbsp;



								<?php if ($edit_report == 1  and $_SESSION['fullname'] == $row_ddx['entered_by']) { ?>
									<a href="fillrslt_sheet.php?edit=<?php echo $labrequest_no; ?>" class="btn btn-info btn-xs">Edit Results</a>
								<?php } elseif ($combo == 1  and $_SESSION['fullname'] == $row_ddx['entered_by']) { ?>
									<a href="fillrslt_sheet.php?cb=<?php echo $test_id . '/' . $labrequest_no . '/' . $patient_no; ?>" class="btn btn-info btn-xs">Edit Results</a>
								<?php } elseif ($_SESSION['fullname'] == $row_ddx['entered_by']) { ?>
									<input type="button" name="edit" value="Edit Results" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-info btn-xs edit_results" />
								<?php } else { ?>
									<strong>Editable by: </strong><?php echo $row_ddx['entered_by']; ?>
								<?php } ?>

								<input type="hidden" name="MM_update" value="approve_results_2" />
								<input type="hidden" name="combo" value="<?php echo $test_id; ?>" />

								<input type="hidden" name="test_id" value="<?php echo $row_dd['test_id']; ?>" />
								<input type="hidden" name="desc" value="<?php echo $row_dd['labrequest_no'] . '/' .  $row_dd['patient_name']; ?>" />
								<input type="hidden" name="patient_id" value="<?php echo $row_dd['patient']; ?>" />
								<input type="hidden" name="approve_by" value="<?php echo $_SESSION['fullname']; ?>" />
								<input type="hidden" name="patient_type" value="<?php echo $row_dd['business_service_center']; ?>" />
								<input type="hidden" name="approve_id" id="approve_id" value="<?php echo $labrequest_no; ?>" />



							</form>

						</td>
						<td align="right">
							<br><br><br>
							<input type="button" name="reject" value="Reject Results" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-danger btn-xs reject_confirmation" />

						</td>
					</tr>
				</table>
			<?php } ?>



		<?php
	}



	if (isset($_POST["capture_take_spm_id"])) {

		$stmt = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :labrequest_no");
		$stmt->bindParam(':labrequest_no', $_POST["capture_take_spm_id"], PDO::PARAM_STR);
		$stmt->execute();
		$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

		$collected_datetime = !empty($row_d['collected_date']) ? date('Y-m-d\TH:i', strtotime($row_d['collected_date'])) : date('Y-m-d\TH:i');
		$collected_by = !empty($row_d['collected_by']) ? 'Collected by: ' . trim($row_d['collected_by']) . '<hr>' : '';
		$collected_specimen = !empty($row_d['collected_specimen']) ? trim($row_d['collected_specimen']) : '';
		$preferred_specimen = !empty($row_d['preferred_specimen']) ? trim($row_d['preferred_specimen']) : '';

		$default_specimen = $collected_specimen ?: $preferred_specimen;

		$specimens = [
			"Aspirate",
			"Urine",
			"Blood",
			"C.S.F",
			"Ear Swab",
			"Eye Swab",
			"Fluids",
			"No Specimen Required",
			"Pap Smear",
			"Semen",
			"Skin Scraping",
			"Sputum",
			"Stool",
			"Throat Swab",
			"Tissue",
			"Urethral Swab",
			"Bence Jones Protein (Urine)",
			"Vaginal Swab",
			"Wound Swab"
		];
		?>

			<h3 style="color:brown; ">Indicate whether the specimen has been taken or an image has been captured while preparing the investigation results:</h3>
			<hr>

			<h3>Investigation Name: <?php echo htmlspecialchars($row_d['test_name']); ?></h3>


			<?php if (!empty($row_d['preferred_specimen'])): ?>
				<h3>Preferred Specimen: <?php echo htmlspecialchars($row_d['preferred_specimen']); ?></h3>
			<?php endif; ?>

			<?php if (!empty($row_d['request_note'])): ?>
				<h3>Request Notes: <?php echo htmlspecialchars($row_d['request_note']); ?></h3>
			<?php endif; ?>

			<?php if (!empty($collected_by)): ?>
				<h3>Collected By: <?php echo htmlspecialchars($collected_by); ?></h3>
			<?php endif; ?>

			<!-- Date and Time of Collection -->
			<div class="form-group">
				<label for="datetime" style="font-size:14px; color: black;">Date and Time of Collection</label>
				<input type="datetime-local" name="datetime" id="datetime"
					class="form-control"
					value="<?php echo htmlspecialchars($collected_datetime); ?>" required>
			</div>


			<!-- Collected Notes Textarea -->
			<div class="form-group">
				<label for="collected_notes" style="font-size:14px; color: black;">Collection Notes (Maximum Characters 100)</label>
				<textarea name="collected_notes" id="collected_notes" rows="3"
					class="form-control"
					maxlength="100"
					placeholder="Enter any remarks about the specimen collection..."><?php echo isset($row_d['collected_notes']) ? htmlspecialchars($row_d['collected_notes']) : ''; ?></textarea>
			</div>

			<?php if ($row_d['section'] == 'Laboratory') {
				$data_capture_status_ = 'specimen'; ?>
				<!-- Select Specimen -->
				<div class="form-group">
					<label for="speciment_taken_collector" style="font-size:14px; color: black;">Select Specimen</label>
					<select name="speciment_taken" class="form-control" id="speciment_taken_collector">
						<option value="">Select...</option>
						<?php foreach ($specimens as $specimen): ?>
							<option value="<?php echo htmlspecialchars($specimen); ?>"
								<?php echo ($default_specimen === $specimen) ? 'selected' : ''; ?>>
								<?php echo htmlspecialchars($specimen); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<?php } else {
				if ($collected_by == '' or $collected_specimen == '') {
					$data_capture_status_ = 'capture'
				?>
					<h3>Are you want to capture Image or Scan?</h3>
					<input type="hidden" value="capture" id="speciment_taken_collector">
			<?php }
			} ?>


			<!-- Select Specimen -->
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-btn">
						<button type="button" class="btn btn-primary" onclick="specimen_taken_()">
							Confirm Specimen/Capture
						</button>
					</span>
				</div>
			</div>

			<input type="hidden" value="<?= $_POST["capture_take_spm_id"]; ?>" id="labrequest_no_taken">
			<input type="hidden" value="<?= $data_capture_status_; ?>" id="data_capture_status_">

		<?php	}


	if (isset($_POST["note_lab_id"])) {

		$stmt = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :labrequest_no");
		$stmt->bindParam(':labrequest_no', $_POST["note_lab_id"], PDO::PARAM_STR);
		$stmt->execute();
		$row_d = $stmt->fetch(PDO::FETCH_ASSOC);
		$IN_EX = $row_d['business_service_center'];

		if ($IN_EX == 'IN') {
			$stmt = $db->prepare("SELECT gender, blood_g, age, phone, hmo_no FROM enrollee WHERE hospital_no = :patient");
			$stmt->bindParam(':patient', $row_d['patient'], PDO::PARAM_STR);
			$stmt->execute();
			$row_dX = $stmt->fetch(PDO::FETCH_ASSOC);

			$age = $row_dX['age'];
			$hmo_no = $row_dX['hmo_no'];

			$stmtss = $db->prepare("SELECT insurance_name FROM insurance_tbl WHERE insurance_no = :hmo_no");
			$stmtss->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
			$stmtss->execute();
			$row_dX3 = $stmtss->fetch(PDO::FETCH_ASSOC);

			$hmo_name = $row_dX3['insurance_name'];
		} elseif ($IN_EX == 'EX') {
			$stmt = $db->prepare("SELECT gender, dob, phone FROM pharm_ext WHERE transc_code = :patient");
			$stmt->bindParam(':patient', $row_d['patient'], PDO::PARAM_STR);
			$stmt->execute();
			$row_dX = $stmt->fetch(PDO::FETCH_ASSOC);

			$birthDate = $row_dX['dob'];
			$Current_date = date('Y-m-d');
			$date1 = new DateTime($Current_date);
			$date2 = new DateTime($birthDate);
			$diff = $date2->diff($date1);
			$age = $diff->format('%y');
		}


		?>

			<table class="table table-bordered">
				<tbody>

					<tr>
						<td>Patient ID: </td>
						<td><?php echo $row_d['patient'] . ' / ' . $row_d['patient_name']; ?></td>
					</tr>
					<tr>
						<td>Patient Details: </td>
						<td><?php echo '<strong>Gender</strong>: ' . $row_dX['gender'] .  ' | <strong>Age</strong>: ' . $age . ' | <strong>Phone#</strong>: ' . $row_dX['phone'] . '<br>' . $hmo_name; ?></td>
					</tr>
					<tr>
						<td>Requested Date</td>
						<td><?php if ($row_d['request_date'] != '') {
								echo date("d M Y H:i:s a ", strtotime($row_d['request_date']));
							} else {
								echo '';
							} ?></td>
					</tr>
					<tr>
						<td>Requested By</td>
						<td><?php echo $row_d['request_by']; ?></td>
					</tr>
					<tr>
						<td>Request Note</td>
						<td><?php echo $row_d['request_note']; ?></td>
					</tr>

					<tr>
						<td>Preferred Specimen</td>
						<td><?php echo $row_d['preferred_specimen']; ?></td>
					</tr>
					<tr>
						<td>Specimen Used</td>
						<td><?php echo $row_d['collected_specimen']; ?></td>
					</tr>

					<?php
					$can_clear = false;
					if (
						isset($_SESSION['fullname'], $row_d['collected_by'], $row_d['collected_date']) &&
						$_SESSION['fullname'] === $row_d['collected_by']
					) {
						$collected_time = strtotime($row_d['collected_date']);
						$days_difference = (time() - $collected_time) / (60 * 60 * 24);
						if ($days_difference <= 14) {
							$can_clear = true;
						}
					}
					?>

					<tr>
						<td>Collected By</td>
						<td>
							<?php echo htmlspecialchars($row_d['collected_by']); ?>
							<?php if ($can_clear): ?>
								&nbsp;&nbsp;
								<a href="?clear_field=collected_by&labrequest_no=<?php echo urlencode($row_d['labrequest_no']); ?>" onclick="return confirm('Are you sure you want to clear Collected By?');">Clear</a>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td>Collected Notes</td>
						<td>
							<?php echo htmlspecialchars($row_d['collected_notes']); ?>
							<?php if ($can_clear && $row_d['collected_notes'] != ''): ?>
								&nbsp;&nbsp;
								<a href="?clear_field=collected_notes&labrequest_no=<?php echo urlencode($row_d['labrequest_no']); ?>" onclick="return confirm('Are you sure you want to clear Collected Notes?');">Clear</a>
							<?php endif; ?>
						</td>
					</tr>


					<tr>
						<td>Collected Date</td>
						<td><?php if ($row_d['collected_date'] != '') {
								echo date("d M Y H:i:s a ", strtotime($row_d['collected_date']));
							} else {
								echo '';
							} ?></td>
					</tr>

					<tr>
						<td>Entered By</td>
						<td><?php echo $row_d['entered_by']; ?></td>
					</tr>
					<?php if ($row_d['lab_sci_speciality'] != '') { ?>
						<tr>
							<td><?php echo $row_d['lab_sci_speciality'] . ': '; ?></td>
							<td><?php echo $row_d['lab_sci_name']; ?></td>
						</tr>
					<?php } ?>
					<tr>
						<td>Approved</td>
						<td><?php if ($row_d['data_capture_status'] == 'approve') {
								echo 'Yes';
							} else {
								echo 'No';
							} ?></td>
					</tr>
					<?php if ($row_d['approved_by'] != '') { ?>
						<tr>
							<td>Approved By</td>
							<td><?php echo $row_d['approved_by']; ?></td>
						</tr>
					<?php } ?>
					<tr>
						<td>Result Date</td>
						<td><?php if ($row_d['result_date'] != '') {
								echo date("d M Y H:i:s a ", strtotime($row_d['result_date']));
							} else {
								echo '';
							} ?></td>
					</tr>

				</tbody>
			</table>

		<?php } ?>




		<?php

		if (isset($_POST["lab_request_no"])) {
			// Assuming $db is your PDO connection object
			$query = "SELECT * FROM lab_manage WHERE labrequest_no = :labrequest_no ORDER BY sn";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':labrequest_no', $_POST["lab_request_no"], PDO::PARAM_STR);
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				echo json_encode($row);
			} else {
				echo json_encode(['error' => 'No records found']);
			}
		}



		if (isset($_POST["labrequest_reject_confirm"])) {

			$labrequest_no = $_POST["labrequest_reject_confirm"];
		?>

			<form method="POST" id="reject_result_form">
				<strong>Are You Sure Want To Reject This Result Entered for ?</strong>
				<hr>
				<strong>Lab Request #<?php echo $labrequest_no; ?></strong><br><br />

				<input type="button" name="reject" value="Reject Results" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-danger btn-xs reject_results" />

				&nbsp; &nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp; &nbsp;
				<input type="button" name="cancel" value="Cancel" data-target=".slacker-modal" id="" class="btn btn-warning btn-xs cancel_results" />
			</form>

		<?php }






		if (isset($_POST["labrequest_reject_no"])) {

			$RQ_No = $_POST["labrequest_reject_no"];
			include("apr_combl.php");
			$setdate = date('Y-m-d H:i:s');

			$updateQuery = "UPDATE lab_manage SET data_capture_status = :status WHERE labrequest_no = :labrequest_no";
			$stmt = $db->prepare($updateQuery);
			$stmt->bindParam(':status', 'reject', PDO::PARAM_STR);
			$stmt->bindParam(':labrequest_no', $RQ_No, PDO::PARAM_STR);
			$stmt->execute();

			include_once("refresh.php");
			$sub = approve();
		}


		if (isset($_POST["yes_cancel_lab_req_ID"])) {
			$setdate = date('Y-m-d H:i:s');
			$updateQuery = "UPDATE lab_manage SET data_capture_status = :status WHERE labrequest_no = :labrequest_no";
			$stmt = $db->prepare($updateQuery);
			$stmt->bindValue(':status', 'delete', PDO::PARAM_STR);
			$stmt->bindValue(':labrequest_no', $_POST["yes_cancel_lab_req_ID"], PDO::PARAM_STR);
			$stmt->execute();

			include_once("refresh.php");
			$sub = queue();
		}




		if (isset($_POST["labrequest_approve_no"])) {
			$setdate = date('Y-m-d H:i:s');
			$updateQuery = "UPDATE lab_manage SET data_capture_status = :status WHERE labrequest_no = :labrequest_no";
			$stmt = $db->prepare($updateQuery);
			$stmt->bindValue(':status', 'approve', PDO::PARAM_STR);
			$stmt->bindValue(':labrequest_no', $_POST["labrequest_approve_no"], PDO::PARAM_STR);
			$stmt->execute();

			include_once("refresh.php");
			$sub = xancel(); // Assuming xancel() is a function defined in refresh.php
		}



		if (isset($_POST["reorder_request_post_id"])) {
			$labrequest_no = $_POST["reorder_request_post_id"];
			$setdate = date('Y-m-d H:i:s');

			$updateQuery = "UPDATE lab_manage SET data_capture_status = :status, request_date = :request_date WHERE labrequest_no = :labrequest_no";
			$stmt = $db->prepare($updateQuery);
			$stmt->bindValue(':status', 'queue', PDO::PARAM_STR);
			$stmt->bindValue(':request_date', $setdate, PDO::PARAM_STR);
			$stmt->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$stmt->execute();

			include_once("refresh.php");
			$sub = xancel(); // Assuming xancel() is a function defined in refresh.php
		}



		if (isset($_POST["labrequest_approve_confirm"])) {

			$labrequest_no = $_POST["labrequest_approve_confirm"];
		?>

			<form method="POST" id="reject_result_form">
				<strong>Are You Sure Want To Approve This Result Entered for ?</strong>
				<hr>
				<strong>Lab Request #<?php echo $labrequest_no; ?></strong><br><br />

				<input type="button" name="reject" value="Approve Results" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-success btn-xs approve_results" />

				&nbsp;
				<input type="button" name="cancel" value="Cancel" data-target=".slacker-modal" id="" class="btn btn-default btn-xs cancel_approve" />
			</form>

		<?php }


		if (isset($_POST["lab_requestCancel"])) {

			$labrequest_no = $_POST["lab_requestCancel"];
		?>

			<form method="POST" id="reject_result_form">
				<strong>Are You Sure Want To Cancel This Request?</strong>
				<hr>
				<strong>Investigation Request #<?php echo $labrequest_no; ?></strong><br><br />

				<input type="button" name="yes_cancel" value="Cancel Request" data-target=".slacker-modal" id="<?php echo $labrequest_no; ?>" class="btn btn-success btn-xs yes_cancel_lab_request" />

				&nbsp;
				<input type="button" name="cancel" value="Cancel" data-target=".slacker-modal" id="" class="btn btn-default btn-xs cancel_request_scrn" />
			</form>

		<?php }

		if (isset($_POST["labrequest_no_add"]) or isset($_POST["labrequest_no_edit"])) {

			if (isset($_POST["labrequest_no_add"])) {
				$status = "new";
				$labrequest_no = $_POST["labrequest_no_add"];
			} elseif (isset($_POST["labrequest_no_edit"])) {
				$status = "edit";
				$labrequest_no = $_POST["labrequest_no_edit"];
			}

			$stmt = $db->prepare("SELECT test_id, test_name, preferred_specimen, collected_specimen, patient_name, patient, group_id FROM lab_manage WHERE labrequest_no = :labrequest_no");
			$stmt->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$row_lab = $stmt->fetch(PDO::FETCH_ASSOC);

				$test_id = $row_lab['test_id'];
				$test_name = $row_lab['test_name'];
				$preferred_specimen = $row_lab['preferred_specimen'];
				$patient_name = $row_lab['patient_name'];
				$patient = $row_lab['patient'];
				$group_id = $row_lab['group_id'];
				$collected_specimen_first = $row_lab['collected_specimen'];
			}


			$stmtf = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_id");
			$stmtf->bindValue(':test_id', $test_id, PDO::PARAM_STR);
			$stmtf->execute();
			$row_fields = $stmtf->fetch(PDO::FETCH_ASSOC);

			$field_type = $row_fields['field_type'];
			$field_sn = $row_fields['sn'];
			$reference = $row_fields['reference'];
			$field_name = $row_fields['field'];

			$sspm = $db->prepare("SELECT * FROM lab_result WHERE test_no = :test_id AND lab_no = :labrequest_no");
			$sspm->bindValue(':test_id', $test_id, PDO::PARAM_STR);
			$sspm->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$sspm->execute();

			if ($sspm->rowCount() > 0) {
				$rwsp = $sspm->fetch(PDO::FETCH_ASSOC);

				$collected_specimen = $rwsp['specimen_collected'];
				$field_value = $rwsp['field_value'];
				$result_comment = $rwsp['comment'];
				$result_note = $rwsp['notes'];
				$RQ_type = $rwsp['RQ_type'];
				$edit = 1;
			} else {
				$collected_specimen = $collected_specimen_first;
				$field_value = '';
				$result_comment = '';
				$result_note = '';
				$RQ_type = '';
			}


		?>

			<h4>Patient # / Name: &nbsp; &nbsp; <?php echo $patient . ' / ' . $patient_name; ?></h4>
			<hr>


			<form method="POST" id="fill_result_form">
				<div class="form_sep">
					<strong>FILL RESULT:&nbsp; <?php echo $row_lab['test_name'] ?></strong>
				</div>

				<?php if ($field_type != 'report') { ?>

					<div class="form_sep">
						Preferred Specimen :<?php if ($preferred_specimen != '') {
												echo $preferred_specimen;
											} else {
												echo ' Not Available';
											} ?>
					</div>



					<div class="form_sep">
						<label for="reg_input_no" class="">Specimen</label>
						<select name="Specimen[]" data-placeholder="Select" class="chosen-select" style="width:350px;">
							<?php if ($collected_specimen != '') { ?>
								<option value="<?php echo $collected_specimen; ?>" selected="selected"><?php echo $collected_specimen; ?></option>
							<?php } else { ?>
								<option value="">Select...</option>
							<?php } ?>
							<option value="Aspirate">Aspirate</option>
							<option value="Urine">Urine</option>
							<option value="Blood">Blood</option>
							<option value="C.S.F">C.S.F</option>
							<option value="Ear Swab">Ear Swab</option>
							<option value="Eye Swab">Eye Swab</option>
							<option value="Fluids">Fluids</option>
							<option value="No Specimen Required">No Specimen Required</option>
							<option value="Pap Smear">Pap Smear</option>
							<option value="Semen">Semen</option>
							<option value="Skin Scraping">Skin Scraping</option>
							<option value="Sputum">Sputum</option>
							<option value="Stool">Stool</option>
							<option value="Throat Swab">Throat Swab</option>
							<option value="Tissue">Tissue</option>
							<option value="Urethral Swab">Urethral Swab</option>
							<option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
							<option value="Viginal Swab">Viginal Swab</option>
							<option value="Wound Swab">Wound Swab</option>
						</select>
					</div>


				<?php } ?>

				<?php
				if ($status == "new") { ?>


					<?php if ($field_type != 'report') { ?>

						<div class="form_sep">
							<table width="100%">
								<tr>
									<td><label for="reg_input_no" class=""><?php echo $field_name; ?></label></td>
									<td align="right">
										<div align="right"><label for="reg_input_no" style="text-align:right"><?php if ($reference != '') {
																													echo 'Reference' . '( ' . $reference . ' )';
																												} ?></label></div>
									</td>
								</tr>
							</table>

							<?php if ($field_type == 'options') {

								$stmt_opt = $db->prepare("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_sn ORDER BY sn");
								$stmt_opt->bindValue(':field_sn', $field_sn, PDO::PARAM_STR);
								$stmt_opt->execute();

								if ($stmt_opt->rowCount() > 0) {
									echo '<select name="fvalue[]" class="form-control" data-required="true">';
									echo '<option selected="selected" value="">Select ...</option>';

									while ($row_opt = $stmt_opt->fetch(PDO::FETCH_ASSOC)) {
										echo '<option value="' . htmlspecialchars($row_opt['options']) . '">' . htmlspecialchars($row_opt['options']) . '</option>';
									}

									echo '</select>';
								}
							} elseif ($field_type == 'values') {
								////////////// VALUES
								$stmt_values = $db->prepare("SELECT * FROM lab_scan_rlts_values WHERE field_id_no = :field_sn ORDER BY sn");
								$stmt_values->bindValue(':field_sn', $field_sn, PDO::PARAM_STR);
								$stmt_values->execute();

								if ($stmt_values->rowCount() > 0) {
									echo '<table width="100%" cellpadding="5" cellspacing="5">';

									while ($row_vxls = $stmt_values->fetch(PDO::FETCH_ASSOC)) {
										echo '<tr>';
										echo '<td><input type="text" name="fvalues[]" class="form-control" placeholder="Enter result for ' . htmlspecialchars($row_vxls['options']) . '"></td>';
										echo '<td>&nbsp;<strong>Ref.:</strong>&nbsp;' . htmlspecialchars($row_vxls['reference']) . '</td>';
										echo '<input type="hidden" name="values_fre[]" value="' . htmlspecialchars($row_vxls['reference']) . '" />';
										echo '<input type="hidden" name="values_title[]" value="' . htmlspecialchars($row_vxls['options']) . '" />';
										echo '<input type="hidden" name="values_sn[]" value="' . htmlspecialchars($row_vxls['sn']) . '" />';
										echo '<input type="hidden" name="rq_no[]" value="' . htmlspecialchars($labrequest_no) . '" />';
										echo '<input type="hidden" name="test_noo[]" value="' . htmlspecialchars($row_lab['test_id']) . '" />';
										echo '<input type="hidden" name="row_fields_fields[]" value="' . htmlspecialchars($field_sn) . '" />';
										echo '</tr>';
									}

									echo '</table>';
									echo '<input type="hidden" name="input_ty" value="values" />';
									echo '<input type="hidden" name="fvalue[]" class="form-control">';
								}
							} elseif ($field_type == 'value') {

								//// SINGLE
								$input_type = 'single';
							?>
								<input type="hidden" name="input_ty" value="single" />
								<input type="text" name="fvalue[]" class="form-control" placeholder="Enter result for <?php echo $field_name; ?>">

							<?php } else { ?>

								<strong style="color:#F00">No Result Template Set. Set Result Template before you can continue</strong>
							<?php } ?>

							<input type="hidden" name="fname[]" value="<?php echo $field_name; ?>" />
							<input type="hidden" name="fre[]" value="<?php echo $reference; ?>" />
							<input type="hidden" name="fno[]" value="<?php echo $field_sn; ?>" />
							<input type="hidden" name="labrequest_no" value="<?php echo $labrequest_no; ?>" />
							<input type="hidden" name="test_id" value="<?php echo $test_id; ?>" />
							<input type="hidden" name="test_name" value="<?php echo $test_name; ?>" />


						</div>

						<?php }
				} elseif ($status == "edit") {

					//echo "am here";

					// ========================================= EDIT MODE ==========================================		

					$test_id = $row_lab['test_id'];

					$stmtf = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_id");
					$stmtf->bindValue(':test_id', $test_id, PDO::PARAM_STR);
					$stmtf->execute();
					$row_fields = $stmtf->fetch(PDO::FETCH_ASSOC);

					$field_type = $row_fields['field_type'];
					$field_sn = $row_fields['sn'];
					$reference = $row_fields['reference'];
					$field_name = $row_fields['field'];


					if ($field_type == 'options' or $field_type == 'value') {



						$stmt_edit = $db->prepare("SELECT * FROM lab_result WHERE lab_no = :labrequest_no AND test_no = :test_id");
						$stmt_edit->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
						$stmt_edit->bindValue(':test_id', $test_id, PDO::PARAM_STR);
						$stmt_edit->execute();

						if ($stmt_edit->rowCount() > 0) {
							$row_fields = $stmt_edit->fetch(PDO::FETCH_ASSOC); ?>

							<div class="form_sep">
								<table width="100%">
									<tr>
										<td><label for="reg_input_no" class=""><?php echo $field_name; ?></label></td>
										<td align="right">
											<div align="right"><label for="reg_input_no" style="text-align:right"><?php if ($reference != '') {
																														echo 'Reference' . '( ' . $reference . ' )';
																													} ?></label></div>
										</td>
									</tr>
								</table>

								<?php if ($field_type == 'options') {

									$stmt_opt = $db->prepare("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_sn ORDER BY sn");
									$stmt_opt->bindValue(':field_sn', $field_sn, PDO::PARAM_STR);
									$stmt_opt->execute();

									if ($stmt_opt->rowCount() > 0) { ?>
										<select name="fvalue[]" class="form-control" data-required="true">
											<option selected="selected" value="<?php echo $row_fields['field_value']; ?>">
												<?php echo $row_fields['field_value']; ?></option>
											<?php while ($row_opt = $stmt_opt->fetch(PDO::FETCH_ASSOC)) {	?>
												<option value="<?php echo $row_opt['options']; ?>"><?php echo $row_opt['options']; ?></option>
											<?php } ?>
										</select>
									<?php } ?>

								<?php } elseif ($field_type == 'value') { ?>

									<input type="hidden" name="input_ty" value="single" />
									<input type="text" name="fvalue[]" class="form-control" placeholder="Enter result for <?php echo $field_name; ?>" value="<?php echo $row_fields['field_value']; ?>">
							<?php }
							}
							?>



							<?php } else {


							/// VALUESS 
							$stmt_values = $db->prepare("SELECT * FROM lab_scan_input_results WHERE field_no = :field_sn AND lab_request_no = :labrequest_no ORDER BY value_sn");
							$stmt_values->bindValue(':field_sn', $field_sn, PDO::PARAM_STR);
							$stmt_values->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
							$stmt_values->execute();

							if ($stmt_values->rowCount() > 0) { ?>
								<div class="form_sep">
									<table width="100%" cellpadding="5" cellspacing="5">
										<?php while ($row_vxls = $stmt_values->fetch(PDO::FETCH_ASSOC)) { ?>
											<tr>
												<td><?php echo $row_vxls['value_title']; ?> :</td>
												<td><input type="text" name="fvalues[]" class="form-control" value="<?php echo $row_vxls['result']; ?>" placeholder="Enter result for <?php echo $row_vxls['value_title']; ?>"></td>
												<td>&nbsp;<strong>Ref.:</strong>&nbsp;<?php echo $row_vxls['value_ref']; ?>
													<input type="hidden" name="values_fre[]" value="<?php echo $row_vxls['value_ref']; ?>" />
													<input type="hidden" name="values_title[]" value="<?php echo $row_vxls['value_title']; ?>" />
													<input type="hidden" name="values_sn[]" value="<?php echo $row_vxls['value_sn']; ?>" />
													<input type="hidden" name="rq_no[]" value="<?php echo $labrequest_no; ?>" />
													<input type="hidden" name="test_noo[]" value="<?php echo $row_lab['test_id']; ?>" />

													<input type="hidden" name="row_fields_fields[]" value="<?php echo $field_sn; ?>" />
												</td>
											</tr>
										<?php } ?>
									</table>
								</div>
								<input type="hidden" name="input_ty" value="values" />
								<input type="hidden" name="fvalue[]" value="values">
							<?php } ?>


						<?php } ?>




						<input type="hidden" name="fname[]" value="<?php echo $field_name; ?>" />
						<input type="hidden" name="fre[]" value="<?php echo $reference; ?>" />
						<input type="hidden" name="fno[]" value="<?php echo $field_sn; ?>" />
						<input type="hidden" name="labrequest_no" value="<?php echo $labrequest_no; ?>" />



					<?php
				}

				///===================== END OF EDIT MODE ===========================================			


					?>

					<?php if ($field_type == 'report') { ?>
						<hr>

						<a href="fillrslt_sheet.php?g=<?php echo $group_id . '/' . $patient; ?>" class="btn btn-success btn-xs"><i class="fa fa-comment"></i>&nbsp; Click to Add Result</a>

					<?php } else { ?>
						<hr>
						<div class="form_sep">
							<label for="reg_input_no" class="">COMMENT</label>
							<textarea name="comment" id="comment" cols="15" rows="2" class="form-control" data-minlength="10" placeholder="Enter result for COMMENT"><?php if ($result_comment != '') {
																																											echo $result_comment;
																																										} else {
																																											echo "";
																																										} ?></textarea>
						</div>
						<div class="form_sep">
							<input type="hidden" value=" " name="notes" />
							<!--  <textarea name="notes" id="notes" cols="15" rows="2" class="form-control" data-minlength="10" placeholder="Note"><</textarea>
-->
						</div>

						<div class="form_sep">

							<table width="100%">
								<tr>
									<td><input type="submit" name="Save" id="Save" value="Save" class="btn btn-success btn-xs" /></td>
									<td align="right">
										<a href="" class="btn btn-warning btn-xs Cancel_lab_request">Close</a>
							</table>

							<input type="hidden" name="RQ_type" id="RQ_type" value="<?php if ($RQ_type != '') {
																						echo $RQ_type;
																					} else {
																						echo 'sl';
																					} ?>" />
							<input type="hidden" name="Total_field_count" id="Total_field_count" value="<?php echo $Total_field_count; ?>" />
							<input type="hidden" name="test_id" value="<?php echo $test_id; ?>" />
							<input type="hidden" name="test_name" value="<?php echo $test_name; ?>" />

							<?php
							if ($_SESSION['speciality'] == "Data Operator") { ?>
								<input type="hidden" name="entered_by" value="<?php echo $_SESSION['fullname']; ?>" />
								<input type="hidden" name="lab_sci_name" value="" />
								<input type="hidden" name="lab_sci_speciality" value="" />
							<?php } else { ?>
								<input type="hidden" name="entered_by" value="<?php echo $_SESSION['fullname']; ?>" />
								<input type="hidden" name="lab_sci_name" value="<?php echo $_SESSION['fullname']; ?>" />
								<input type="hidden" name="lab_sci_speciality" value="<?php echo $_SESSION['speciality']; ?>" />
							<?php } ?>
							<input type="hidden" name="MM_update" value="fill_result_form" />

						</div>
			</form>
		<?php } ?>

	<?php }


		if (isset($_POST["collect_specimen_lab_request_no"])) {

			$stmt = $db->prepare("SELECT l.business_service_center, l.test_name, l.request_note, l.preferred_specimen, l.labrequest_no, l.patient, l.patient_name, p.pay, p.claim_amt, p.invoice_status, p.invoice_no, p.paystatus, p.hospital_no, p.cr 
	FROM lab_manage AS l 
	INNER JOIN patient_ap_services AS p ON l.labrequest_no = p.drug_sn 
	WHERE l.section = 'Laboratory' AND l.labrequest_no = :labrequest_no");

			$stmt->bindParam(':labrequest_no', $_POST["collect_specimen_lab_request_no"], PDO::PARAM_STR);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$patient_no = $row['patient'];
			$patient_name = $row['patient_name'];
			$preferred_specimen = $row['preferred_specimen'];
			$EX_IN = $row['business_service_center'];

			// Count number of tests
			$stmt2 = $db->prepare("SELECT SUM(p.pay) AS total_pay, COUNT(l.group_id) AS count 
			FROM lab_manage AS l 
			INNER JOIN patient_ap_services AS p ON p.drug_sn = l.labrequest_no 
			WHERE l.section = 'Laboratory' AND l.group_id = :group_id AND l.data_capture_status = 'queue'");

			$stmt2->bindParam(':group_id', $_POST["group_id"], PDO::PARAM_STR);
			$stmt2->execute();
			$rowe = $stmt2->fetch(PDO::FETCH_ASSOC);

			$total_pay = $rowe['total_pay'];

			if (isset($_POST["nxt_testCount"])) {
				$testCount = $_POST["nxt_testCount"];
			} else {
				$testCount = $rowe['count'] + 1;
				$Total_tests = $rowe['count'];
			}

			$testCount = $testCount - 1;

			/// check admisssion status
			$hospital_no = $row['hospital_no'];

			// Check admission status
			$adm_stmt = $db->prepare("SELECT app_no FROM admission WHERE adm_status = '3' AND hospital_no = :hospital_no ORDER BY sn DESC LIMIT 1");
			$adm_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$adm_stmt->execute();

			if ($adm_stmt->rowCount() > 0) {
				$adm_status = "1";

				// Calculate outstanding balance for credits
				$stmt4 = $db->prepare("SELECT SUM(pay) AS outstanding FROM patient_ap_services WHERE hospital_no = :hospital_no AND paystatus = :paystatus AND cr = :cr");
				$stmt4->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmt4->bindParam(':paystatus', $zero, PDO::PARAM_STR);
				$stmt4->bindParam(':cr', $one, PDO::PARAM_STR);
				$stmt4->execute();

				$rowOut = $stmt4->fetch(PDO::FETCH_ASSOC);
				$credit_bal = $rowOut['outstanding'];
			} else {
				$adm_status = "0";
				$credit_bal = 0;
			}


			$test_name = $row['test_name'];
			$request_note = $row['request_note'];
			$preferred_specimen = $row['preferred_specimen'];
			$pay = $row['pay'];
			$paystatus = $row['paystatus'];
			$claim_amt = $row['claim_amt'];
			$invoice_status = $row['invoice_status'];

			if ($row['invoice_status'] == '0') {
				$invoice_no = $hospital_no . '' . mt_rand(100, 999);
				$invoice_status = '1';
			} else {
				$invoice_no = $row['invoice_no'];
				$invoice_status = '1';
			}

			if ($row['claim_amt'] > 0 and $row['pay'] == 0 and $row['cr'] == 0) {
				$spm_right = "1";
				$test_amt = $row['claim_amt'] . ' <i>[Insured Bill]</i>';
				$msg = "Amount Covered by Insurance";
				$clour = "b";
				$paystatus_title = "claim";
			} else {
				$paystatus_title = "cash";
				$test_amt = $row['pay'] . ' <i>[Amount]</i>';
				/////  PAYING TESTING LISTINGS

				if ($paystatus == "1") {
					$spm_right = "1";
					$credit_status = "0";
					$msg = "Amount Paid";
					$clour = "b";
				} elseif ($paystatus == "0" and $_SESSION["oncredit"] == "1") {
					$msg = "You want to run investigation on credit?";
					$credit_status = "1";
					$spm_right = 1;
					$clour = "r";
				} elseif ($paystatus == "0" and $_SESSION["oncredit_adm"] == "1" and $adm_status == "1") {
					$msg = "You want to run investigation on credit?";
					$clour = "r";
					$spm_right = "1";
					$credit_status = "1";
				} else {
					$msg = "Payment pending pay before investigation";
					$clour = "r";
					$spm_right = "0";
					$credit_status = "0";
				}
				///=========================================	
			}
	?>

		<?php if ($credit_bal > 0) { ?>
			<div class="alert alert-danger ">
				<strong>Total Credits: <?php echo 'N ' . $credit_bal; ?></strong>
			</div>
		<?php } ?>

		<h4>Patient # / Name: &nbsp; &nbsp; <?php echo $patient_no . ' / ' . $patient_name; ?></h4>
		<table class="table table-bordered">
			<tbody>
				<tr>
					<td>Test Amount</td>
					<td><?php echo 'N' . $test_amt; ?></td>
				</tr>
				<tr>
					<td>Total Test(s) / Amount</td>
					<td><?php echo $Total_tests . ' / ' .  $total_pay; ?></td>
				</tr>
				<tr>
					<td>Patient Status</td>
					<td><?php if ($adm_status == 1) { ?> <strong style="color:#00C">Admitted Patient</strong> <?php } else {
																												echo "Out Patient";
																											} ?></td>
				</tr>
				<tr>
					<td>Requested Notes</td>
					<td><?php echo $row['request_note']; ?></td>
				</tr>
				<tr>
					<td>Preferred Specimen</td>
					<td><?php echo $row['preferred_specimen']; ?></td>
				</tr>


			</tbody>
		</table>

		<form method="POST" id="collect_specimen_form">
			<?php if ($spm_right == "1") { ?>
				<div class="form_sep">
					<label for="reg_input_no" class="req">Take Specimen Taken</label>
					<select name="Specimen" id="Specimen" class="form-control" required>

						<?php if ($preferred_specimen != '') { ?>
							<option value="<?php echo $preferred_specimen; ?>" selected="selected"><?php echo $preferred_specimen; ?></option>
						<?php } else { ?>
							<option selected="selected" value="">Select...</option>
						<?php } ?>

						<option value="Aspirate">Aspirate</option>
						<option value="Urine">Urine</option>
						<option value="Blood">Blood</option>
						<option value="C.S.F">C.S.F</option>
						<option value="Ear Swab">Ear Swab</option>
						<option value="Eye Swab">Eye Swab</option>
						<option value="Fluids">Fluids</option>
						<option value="No Specimen Required">No Specimen Required</option>
						<option value="Pap Smear">Pap Smear</option>
						<option value="Semen">Semen</option>
						<option value="Skin Scraping">Skin Scraping</option>
						<option value="Sputum">Sputum</option>
						<option value="Stool">Stool</option>
						<option value="Throat Swab">Throat Swab</option>
						<option value="Tissue">Tissue</option>
						<option value="Urethral Swab">Urethral Swab</option>
						<option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
						<option value="Viginal Swab">Viginal Swab</option>
						<option value="Wound Swab">Wound Swab</option>
					</select>
				</div>
				<input type="hidden" name="specimen_notes" value=" " />
				<hr>

				<div class="form_sep">

					<?php if ($msg != "") { ?>
						<?php if ($clour == 'r') { ?>
							<strong style="color: #FFF; background-color:#F00; height:12px;">
							<?php } else { ?>
								<strong style="color: #FFF; background-color: #00C; height:12px;">
								<?php } ?>
								<?php echo $msg; ?></strong><?php } ?>
				</div>
				<hr>
				<div class="pull-left">
					<input type="submit" name="save" id="save" value="<?php if ($testCount > 1) {
																			echo 'Save & Take Next Specimen';
																		} else {
																			echo 'Save & Close';
																		} ?>" class="btn btn-success btn-xs" />
				</div>

				<div class="pull-right">
					<a href="mgt.php" class="btn btn-danger btn-xs"><i class="fa fa-times"></i>&nbsp; Close</a>
				</div>

			<?php } else { ?>
				<div class="alert alert-danger">
					<?php echo $msg; ?>
				</div>
			<?php } ?>


			<input type="hidden" id="lab_test" name="lab_test" />
			<input type="hidden" name="labrequest_no" id="labrequest_no" value="<?php echo $_POST["collect_specimen_lab_request_no"]; ?>" />
			<input type="hidden" name="MM_update" value="take_specimen" />
			<input type="hidden" name="staffname" value="<?php echo $fullname; ?>" />
			<input type="hidden" id="pay" name="pay" class="form-control" />
			<input type="hidden" id="invoice_no" name="invoice_no" value="<?php echo $invoice_no; ?>" />
			<input type="hidden" id="invoice_status" name="invoice_status" value="<?php echo $invoice_status; ?>" />
			<input type="hidden" id="paystatus_title" name="paystatus_title" value="<?php echo $paystatus_title; ?>" />

			<input type="hidden" id="credit_status" name="credit_status" value="<?php echo $credit_status; ?>" />

			<?php
			if ($testCount > 0) {

				$collect_specimen_lab_request_no = $_POST["collect_specimen_lab_request_no"];
				$group_id = $_POST["group_id"];

				$sql = "SELECT test_name, labrequest_no 
					FROM lab_manage 
					WHERE section = 'Laboratory' 
					AND labrequest_no != :labrequest_no 
					AND group_id = :group_id 
					AND data_capture_status = :data_capture_status 
					ORDER BY sn DESC";

				$stmt = $db->prepare($sql);
				$stmt->bindParam(':labrequest_no', $collect_specimen_lab_request_no, PDO::PARAM_STR);
				$stmt->bindParam(':group_id', $group_id, PDO::PARAM_STR);
				$stmt->bindValue(':data_capture_status', $queue, PDO::PARAM_STR);
				$stmt->execute();

				$rownxt = $stmt->fetch(PDO::FETCH_ASSOC);

			?>
				<input type="hidden" name="nxt_test_name" id="nxt_test_name" value="<?php echo $rownxt['test_name']; ?>" />
				<input type="hidden" name="nxt_labrequest_no" id="nxt_labrequest_no" value="<?php echo $rownxt['labrequest_no']; ?>" />
				<input type="hidden" name="group_id" id="group_id" value="<?php echo $_POST["group_id"]; ?>" />
				<input type="hidden" name="nxt_testCount" id="nxt_testCount" value="<?php echo $testCount; ?>" />


			<?php } ?>

		</form>

	<?php } ?>

	<?php


	if (isset($_POST["capture_request_no"])) {


		$capture_request_no = $_POST["capture_request_no"];
		$group_id = $_POST["group_id"];

		// Select query for fetching data from lab_manage and patient_ap_services tables
		$sql = "SELECT l.test_name, l.request_note, l.preferred_specimen, l.labrequest_no, p.pay, p.claim_amt, p.invoice_status, p.invoice_no, p.paystatus, p.hospital_no, p.cr
			FROM lab_manage AS l
			INNER JOIN patient_ap_services AS p ON l.labrequest_no = p.drug_sn
			WHERE l.labrequest_no = :labrequest_no";

		$stmt = $db->prepare($sql);
		$stmt->bindParam(':labrequest_no', $capture_request_no, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		// Count the number of tests and calculate total pay
		$sql2 = "SELECT SUM(p.pay) AS total_pay, COUNT(l.group_id) AS count
			 FROM lab_manage AS l
			 INNER JOIN patient_ap_services AS p ON p.drug_sn = l.labrequest_no
			 WHERE l.group_id = :group_id AND p.paystatus = :paystatus AND l.data_capture_status = :data_capture_status";

		$stmt2 = $db->prepare($sql2);
		$stmt2->bindParam(':group_id', $group_id, PDO::PARAM_STR);
		$stmt2->bindValue(':paystatus', $zero, PDO::PARAM_STR);
		$stmt2->bindValue(':data_capture_status', $queue, PDO::PARAM_STR);
		$stmt2->execute();
		$rowe = $stmt2->fetch(PDO::FETCH_ASSOC);

		$total_pay = $rowe['total_pay'];
		$total_tests = $rowe['count'];

		$hospital_no = $row['hospital_no'];

		// Checking admission status
		$adm_status = "0";
		$credit_bal = 0;

		// Query to check admission status
		$stmt_adm = $db->prepare("SELECT app_no FROM admission WHERE adm_status='3' AND hospital_no=:hospital_no AND date_discharge='' ORDER BY sn DESC LIMIT 1");
		$stmt_adm->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
		$stmt_adm->execute();

		if ($stmt_adm->rowCount() > 0) {
			$adm_status = "1";

			// Query to calculate outstanding
			$stmt_outstanding = $db->prepare("SELECT SUM(pay) AS outstanding FROM patient_ap_services WHERE hospital_no=:hospital_no AND paystatus=:paystatus AND cr=:cr");
			$stmt_outstanding->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmt_outstanding->bindValue(':paystatus', $zero, PDO::PARAM_STR);
			$stmt_outstanding->bindValue(':cr', $one, PDO::PARAM_STR);
			$stmt_outstanding->execute();

			$rowOut = $stmt_outstanding->fetch(PDO::FETCH_ASSOC);
			$credit_bal = $rowOut['outstanding'];
		}

		$test_name = $row['test_name'];
		$request_note = $row['request_note'];
		$preferred_specimen = $row['preferred_specimen'];
		$pay = $row['pay'];
		$paystatus = $row['paystatus'];
		$claim_amt = $row['claim_amt'];
		$invoice_status = $row['invoice_status'];

		if ($row['invoice_status'] == '0') {
			$invoice_no = $hospital_no . '' . mt_rand(100, 999);
			$invoice_status = '1';
		} else {
			$invoice_no = $row['invoice_no'];
			$invoice_status = '1';
		}

		if ($row['claim_amt'] > 0 and $row['pay'] == 0 and $row['cr'] == 0) {
			$spm_right = "1";
			$test_amt = $row['claim_amt'] . ' <i>[Insured Bill]</i>';
			$msg = "Amount Covered by Insurance";
			$clour = "b";
			$paystatus_title = "claim";
		} else {
			$paystatus_title = "cash";
			$test_amt = $row['pay'] . ' <i>[Amount]</i>';
			/////  PAYING TESTING LISTINGS

			if ($paystatus == "1") {
				$spm_right = "1";
				$credit_status = "0";
				$msg = "Amount Paid";
				$clour = "b";
			} elseif ($paystatus == "0" and $_SESSION["oncredit"] == "1") {
				$msg = "Are you sure you want perform this investigation on Credit";
				$credit_status = "1";
				$spm_right = 1;
				$clour = "r";
			} elseif ($paystatus == "0" and $_SESSION["oncredit_adm"] == "1" and $adm_status == "1") {
				$msg = "Are you sure you want perform this investigation on Credit";
				$clour = "r";
				$spm_right = "1";
				$credit_status = "1";
			} else {
				$msg = "Payment Pending Settle Before You Can Perform Test";
				$clour = "r";
				$spm_right = "0";
				$credit_status = "0";
			}
			///=========================================	
		}
	?>

		<?php if ($credit_bal > 0) { ?>
			<div class="alert alert-danger ">
				<strong>Total Credits: <?php echo 'N ' . $credit_bal; ?></strong>
			</div>
		<?php } ?>

		<h4>Are you sure this request has been captured in the request room?</h4>
		<table class="table table-bordered">
			<tbody>

				<tr>
					<td>Investigation Amount</td>
					<td><?php echo 'N' . $test_amt; ?></td>
				</tr>
				<tr>
					<td>Total Investigation(s) / Amount</td>
					<td><?php echo $Total_tests . ' / ' .  $total_pay; ?></td>
				</tr>
				<tr>
					<td>Patient Status</td>
					<td><?php if ($adm_status == 1) {
							echo "Admitted Patient";
						} else {
							echo "Out Patient";
						} ?></td>
				</tr>
				<tr>
					<td>Requested Notes</td>
					<td><?php echo $row['request_note']; ?></td>
				</tr>

			</tbody>
		</table>

		<form method="POST" id="investigation_form">
			<?php if ($spm_right == "1") { ?>




				<div class="form_sep">

					<?php if ($msg != "") { ?>
						<?php if ($clour == 'r') { ?>
							<strong style="color: #FFF; background-color:#F00; height:12px;">
							<?php } else { ?>
								<strong style="color: #FFF; background-color: #00C; height:12px;">
								<?php } ?>
								<?php echo $msg; ?></strong><?php } ?>
				</div>
				<div class="form_sep">
					<input type="submit" name="save" id="save" value="Save" class="btn btn-success btn-xs" />
				</div>
			<?php } else { ?>
				<div class="alert alert-danger">
					<?php echo $msg; ?>
				</div>
			<?php } ?>


			<input type="hidden" id="lab_test" name="lab_test" />
			<input type="hidden" name="labrequest_no" id="labrequest_no" value="<?php echo $_POST["capture_request_no"]; ?>" />
			<input type="hidden" name="MM_update" value="take_investigation" />
			<input type="hidden" name="staffname" value="<?php echo $fullname; ?>" />
			<input type="hidden" id="pay" name="pay" class="form-control" />
			<input type="hidden" id="invoice_no" name="invoice_no" value="<?php echo $invoice_no; ?>" />
			<input type="hidden" id="invoice_status" name="invoice_status" value="<?php echo $invoice_status; ?>" />
			<input type="hidden" id="paystatus_title" name="paystatus_title" value="<?php echo $paystatus_title; ?>" />

			<input type="hidden" id="credit_status" name="credit_status" value="<?php echo $credit_status; ?>" />

		</form>




	<?php } ?>


	<script>
		$(".chosen-select").chosen({
			allow_single_deselect: true,
			enable_search_threshold: 10,
			no_results_text: 'Oops, nothing found!',
			width: "100%"
		});
		$('.chosen-drop').css({
			"width": "100%",
			"white-space": "nowrap"
		})


		$('#fill_result_form').on("submit", function(event) {
			event.preventDefault();

			$.ajax({
				url: "insert.php",
				method: "POST",
				data: $('#fill_result_form').serialize(),
				beforeSend: function() {
					$('#Save').val("Saving");
				},
				success: function(data) {
					//swal({ title: 'Success!', text: 'Added Successfully', timer: 1000 })
					//		 $('#preferred_specimen').val("");
					// $('#fill_result_Modal').modal('hide');  
					// $('#test_fields').html(data);

					//	 var nxt_test_name = $('#nxt_test_name2').val();
					//	var nxt_labrequest_no = $('#nxt_labrequest_no2').val();
					//	var group_id = $('#group_id2').val();
					//	var nxt_testCount = $('#nxt_testCount2').val();
					//	
					//	if (nxt_labrequest_no !='' && nxt_testCount > 0 )
					//	{


					//		$.ajax({  
					//				 url:"fetch.php",  
					//			 method:"POST", 
					//data:{labrequest_no_add:nxt_labrequest_no,group_id:group_id,testCount:nxt_testCount}, 
					//	 success:function(data){  

					//		 $('.modal-title').text('Fill Result for: ' + nxt_test_name); 
					//			   $('#fill_result_Modal').modal('show');
					//			   $('#fill_result_body').html(data);  

					//		 }  
					//		});		
					//	}
					//else
					//	{
					//		
					//	}

					location.href = "mgt.php";

				},
				complete: function() {
					$('#insert').val("Insert");
				},
				error: function(data) {

					alert("Oops...", "Something went wrong :(", "error");
					swal({
						title: 'Oops...!',
						text: 'Something went wrong ',
						type: 'error',
						timer: 500
					})
				}
			});

		});


		$('#edit_result_form').on("submit", function(event) {
			event.preventDefault();

			$.ajax({
				url: "insert.php",
				method: "POST",
				data: $('#edit_result_form').serialize(),
				beforeSend: function() {
					$('#Save').val("Updating");
				},
				success: function(data) {
					swal({
						title: 'Success!',
						text: 'Updated Successfully',
						timer: 1000
					})
					$('#preferred_specimen').val("");
					$('#edit_result_form').modal('hide');
					//$('#test_fields').html(data);  
				},
				complete: function() {
					$('#insert').val("Insert");
				},
				error: function(data) {

					alert("Oops...", "Something went wrong :(", "error");
					swal({
						title: 'Oops...!',
						text: 'Something went wrong ',
						type: 'error',
						timer: 500
					})
				}
			});

		});





		$('#collect_specimen_form').on("submit", function(event) {
			event.preventDefault();

			$.ajax({
				url: "insert.php",
				method: "POST",
				data: $('#collect_specimen_form').serialize(),
				beforeSend: function() {
					$('#save').val("Saving");
				},
				success: function(data) {
					// swal({ title: 'Success!', text: 'Saved Successfully', timer: 1000 })
					// $('#take_speciment_Modal').modal('hide');

					var nxt_test_name = $('#nxt_test_name').val();
					var nxt_labrequest_no = $('#nxt_labrequest_no').val();
					var group_id = $('#group_id').val();
					var nxt_testCount = $('#nxt_testCount').val();

					if (nxt_labrequest_no != '' && nxt_testCount > 0) {


						$.ajax({
							url: "fetch.php",
							method: "POST",
							data: {
								collect_specimen_lab_request_no: nxt_labrequest_no,
								group_id: group_id,
								testCount: nxt_testCount
							},
							success: function(data) {

								$('.modal-title').text('Take Specimen for:' + nxt_test_name);
								$('#take_speciment_Modal').modal('show');
								$('#take_speciment_body').html(data);

							}
						});
					} else {
						window.location.reload();
					}

				},
				complete: function() {
					$('#insert').val("Insert");
				},
				error: function(data) {

					alert("Oops...", "Something went wrong :(", "error");
					swal({
						title: 'Oops...!',
						text: 'Something went wrong ',
						type: 'error',
						timer: 500
					})
				}
			});
			//   }
		});







		$('#approve_results_form').on("submit", function(event) {
			event.preventDefault();

			$.ajax({
				url: "insert.php",
				method: "POST",
				data: $('#approve_results_form').serialize(),
				beforeSend: function() {
					$('#save').val("Updating");
				},
				success: function(data) {
					toastr.success('Approved Successfully!', 'Attention', {
						timeOut: 5000
					});
					// alert('Approve Successfully');
					//swal({ title: 'Success!', text: 'Approve Successfully', timer: 250 })


					$('#view_results_modal').modal('hide');
					$('#refresh_approve').html(data);
					//  href.location="mgt.php?rp";
				},
				complete: function() {
					$('#insert').val("Insert");
				},
				error: function(data) {

					alert("Oops...", "Something went wrong :(", "error");
					//swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
				}
			});

		});



		$('#investigation_form').on("submit", function(event) {
			event.preventDefault();

			$.ajax({
				url: "insert.php",
				method: "POST",
				data: $('#investigation_form').serialize(),
				beforeSend: function() {
					$('#save').val("Saving");
				},
				success: function(data) {
					// swal({ title: 'Success!', text: 'Saved Successfully', timer: 1000 })
					$('#capture_modal').modal('hide');
					window.location.reload();

				},
				complete: function() {
					$('#insert').val("Insert");
				},
				error: function(data) {

					alert("Oops...", "Something went wrong :(", "error");
					swal({
						title: 'Oops...!',
						text: 'Something went wrong ',
						type: 'error',
						timer: 500
					})
				}
			});
		});
	</script>