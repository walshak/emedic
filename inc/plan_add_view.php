     <?php
		session_start();
		include("../Connections/Conn.php");
		///include("_session.php");
		include('../doctor/objects.php');
		include('../doctor/helpers.php');

		if (isset($_POST['delete_notes'])) {

			$response = array(
				'status' => 0,
				'message' => ''
			);

			$id = $_POST['delete_notes'];
			$delete = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
			$deleted = $delete->execute(array($id));

			if ($deleted) {

				$response['message'] = 'Successful!';
				$response['status'] = '0';
				echo  json_encode($response);
				exit;
			} else {
				$response['message'] = 'Not Successful!';
				$response['status'] = '1';
				echo  json_encode($response);
				exit;
			}
		}

		if (isset($_POST['edit_number_prgress'])) {
			$response = array(
				'notes' => '',
				'notes_sn' => ''
			);

			$edit_sn = $_POST['edit_number_prgress'];
			$stmt_getd = $db->prepare("SELECT notes FROM notes WHERE sn = '$edit_sn'");
			$stmt_getd->execute();
			$rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC);
			$response['notes'] = $rwxx['notes'];
			$response['notes_sn'] = $edit_sn;
			echo  json_encode($response);
			exit;
		}


		if (isset($_POST['edit_sn'])) {

			$response = array(
				'notes' => '',
				'notes_sn' => ''
			);

			$edit_sn = $_POST['edit_sn'];
			$stmt_getd = $db->prepare("SELECT notes,date_entry,date_entry2,notes_type FROM notes WHERE sn = '$edit_sn'");
			$stmt_getd->execute();
			$rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC);
			$response['plan_note_2'] = $rwxx['notes'];
			$response['date_entry'] = $rwxx['date_entry'];
			$response['date_entry2'] = $rwxx['date_entry2'];
			$response['note_type_2'] = $rwxx['notes_type'];
			$response['notes_sn'] = $edit_sn;
			echo  json_encode($response);
			exit;
		}

		if (isset($_POST['edit_notes_update'])) {
			///review_note:review_note,edit_notes_update:notes_sn
			$response = array(
				'status' => 0,
				'message' => ''
			);

			$sn = cleanInput($_POST['edit_notes_update']);
			$review_note = cleanInput($_POST['review_note']);
			$date_entry = ($_POST['date_entry']);
			$date_entry2 = ($_POST['date_entry2']);
			$note_type_ = ($_POST['note_type_']);
			$hospital_no = ($_POST['hospital_no']);

			$save_note = editNotes($db, $sn, $review_note, $date_entry, $date_entry2);
			if ($save_note == true) {

				if ($note_type_ == 'plan' && isset($_SESSION['notify_pharm']) && $_SESSION['notify_pharm'] == 1) {
					$roles = ['PH', 'NS']; // Pharmacy and Nursing Staff
					foreach ($roles as $role) {
						$link = $role == 'PH' ? 'index.php?presc&hos_no=' : 'index.php?hosp_no=';
						$message_ = 'Plan has been Edited for patient ' . $hospital_no .
							', for Invoice. <a href="' . $link . $hospital_no . '">View Patient</a>';
						global_notify_($db, 'notifyurgent', $role, '<b>Edited Plan</b>', $message_);
					}
				}

				$response['message'] = 'Successful!';
				$response['status'] = '0';
				echo  json_encode($response);
			} else {
				$response['message'] = 'Not Successful!';
				$response['status'] = '1';
				echo  json_encode($response);
			}
			exit;
		}



		if (isset($_POST['add_plan_notes'])) {

			$response = array(
				'status' => 0,
				'message' => ''
			);

			$plan_notes = $_POST['add_plan_notes'];
			$appointment_number = $_POST['appointment_number'];
			$hospital_no = $_POST['hospital_no'];
			$note_type_ = $_POST['note_type_'];

			$save_note = saveToNotes($db, $appointment_number, $hospital_no, $plan_notes, 'DR', $note_type_,  $_SESSION['fullname'], $specialty = null, $created_by = $_SESSION["id"], true);
			if ($save_note == true) {
				/* 				if ($note_type_ == 'plan' && isset($_SESSION['notify_pharm']) && $_SESSION['notify_pharm'] == 1) {
					$roles = ['PH', 'NS']; // Pharmacy and Nursing Staff
					foreach ($roles as $role) {
						$link = $role == 'PH' ? 'index.php?presc&hos_no=' : 'index.php?hosp_no=';
						$message_ = 'New Plan has been added for patient ' . $hospital_no .
							', for Invoice. <a href="' . $link . $hospital_no . '">View Patient</a>';
						global_notify_($db, 'notifyurgent', $role, '<b>New Plan</b>', $message_);
					}
				} */

				$response['message'] = 'Successful!';
				$response['status'] = '0';
				echo  json_encode($response);
				exit;
			} else {
				$response['message'] = 'Not Successful!';
				$response['status'] = '1';
				echo  json_encode($response);
				exit;
			}
		}



		if (isset($_POST['patient_note_preview'])) {

			$sn = 1;
			$total_srvices = 0;
			$hospital_no = $_POST['patient_note_preview'];
			$appointment_number = $_POST['appointment_number'];
		?>


     	<div style=" max-height:400px; overflow:auto">

     		<?php
				$stmt_getd = $db->prepare("SELECT * FROM notes WHERE hospital_no = '$hospital_no' AND notes_type IN ('plan','pre-adm') AND status='1' order by sn desc limit 20");
				$stmt_getd->execute();
				if ($stmt_getd->rowCount() > 0) { ?>

     			<hr>
     			<table class="table table-bordered table-striped table-hover table-responsive" style="font-size: 15px; ">
     				<thead>
     					<tr>
     						<th>#</th>
     						<th>Details</th>
     					</tr>
     				</thead>
     				<tbody>

     					<?php
							while ($rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC)) {

								if ($rwxx['date_entry2'] != '') {
									$date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($rwxx['date_entry2'])) . ' & ';
									$date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($rwxx['date_entry'])) . '<br>';
									$date_ .= "<b>Number of Edits </b>" .  $rwxx['no_updates'];
								} else {
									$date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($rwxx['date_entry'])) . '<br>';
								}
								$date_ = "<div style='font-size:14px;'>$date_</div>";


							?>
     						<tr>
     							<td width="2%"><?= $sn++; ?></td>
     							<td><?php
										if ($rwxx["notes_type"] == 'plan') {
											$t_t = "Doctor's Plan: ";
										} elseif ($rwxx["notes_type"] == 'pre-adm') {
											$t_t = "Pre Admission Note: ";
										}
										echo '<b><i>' . $t_t  . '</i></b>'  . $rwxx["notes"]; ?>
     								<br>
     								<small><b><i>Entered by: <?php echo $rwxx['prepared_by']; ?>
     											<?php echo $date_; ///formatDateTime_($rwxx['date_entry']); 
													?></i></b></small>
     								<br>

     								<?php if (date_diff_day($rwxx['date_entry']) < 1 && $_SESSION['fullname'] == $rwxx['prepared_by']) { ?>
     									<button type="button" class="btn btn-xs btn-warning" id="edit" onclick="edit_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-edit"></i>&nbsp;Edit</button>
     								<?php } ?>


     							</td>
     						</tr>
     					<?php } ?>
     				</tbody>
     			</table>
     		<?php

					exit;
				} else { ?>
     			<h3>No Records Available </h3>
     		<?php  } ?>
     	</div>
     	<?php

			exit;
		}


		if (isset($_POST['medication_hx_hosp'])) {
			$hospital_no = $_POST['medication_hx_hosp'];

			$sql = "SELECT DISTINCT item_services, sn, app_no, remarks, prepared_by, claim_amt, pay, date_entry,paystatus
            FROM patient_ap_services 
            WHERE hospital_no = ? AND serv_group = 'Pharmacy' 
            ORDER BY date_entry DESC 
            LIMIT 150";

			$drug_hx_stmt = $db->prepare($sql);
			$drug_hx_stmt->execute([$hospital_no]);

			if ($drug_hx_stmt->rowCount() > 0) {
				$drug_hx = $drug_hx_stmt->fetchAll(PDO::FETCH_ASSOC);
			?>

     		<h2>Search for a Drug Below</h2>
     		<input type="text" id="drugSearch" placeholder="Search drug..." class="form-control mb-2">

     		<div style="max-height:800px; overflow:auto">
     			<table id="drugTable" class="table table-striped table-bordered table-hover">
     				<thead>
     					<tr>
     						<th>No</th>
     						<th>Drug</th>
     						<th>Prescription</th>
     						<th>Drug prescription by</th>
     						<th>Date</th>
     					</tr>
     				</thead>
     				<tbody>
     					<?php $n = 1;
							foreach ($drug_hx as $drug_) { ?>
     						<tr>
     							<td><?= $n++; ?></td>
     							<td>
     								<?= ($drug_['item_services']); ?><br>
     								<?php if ($drug_['paystatus'] == 1): ?>
     									<span style="color:blue;">Paid</span>
     								<?php else: ?>
     									<span style="color:red;">Not Paid</span>
     								<?php endif; ?>
     							</td>
     							<td><?= ($drug_['remarks']); ?></td>
     							<td><?= ($drug_['prepared_by']); ?></td>
     							<td><?= date('d M, Y h:i A', strtotime($drug_['date_entry'])); ?></td>
     						</tr>
     					<?php } ?>
     				</tbody>
     			</table>
     		</div>

     		<script>
     			// Simple filter for Drug column
     			document.getElementById('drugSearch').addEventListener('keyup', function() {
     				var filter = this.value.toLowerCase();
     				var rows = document.querySelectorAll('#drugTable tbody tr');

     				rows.forEach(function(row) {
     					var drugCell = row.cells[1].innerText.toLowerCase(); // Drug column
     					if (drugCell.indexOf(filter) > -1) {
     						row.style.display = '';
     					} else {
     						row.style.display = 'none';
     					}
     				});
     			});
     		</script>



     	<?php
			} else {
				echo '<strong>No Record(s) Available!</strong>';
			}
		}

		if (isset($_POST['add_view_plan'])) {
			$add_view_plan = isset($_POST['add_view_plan']) ? $_POST['add_view_plan'] : '';

			$ppt = explode("___", $add_view_plan);
			$hospital_no = isset($ppt[0]) ? trim($ppt[0]) : '';
			$edit_modee = isset($ppt[1]) ? trim($ppt[1]) : '';
			$appointment_number = isset($ppt[2]) ? trim($ppt[2]) : '';
			$adm_status = isset($ppt[3]) ? trim($ppt[3]) : '';

			$status = '0'; // Default

			if (!empty($hospital_no) && !empty($appointment_number)) {
				$stmt2 = $db->prepare("SELECT sn FROM notes WHERE hospital_no = ? AND app_no = ? AND status = '1' LIMIT 1");
				$stmt2->execute([$hospital_no, $appointment_number]);

				if ($stmt2->fetchColumn()) {
					$status = '1';
				}
			}

			if ($adm_status == 3) {
				$status = '1';
			}
			?>

     	<form action="" id="remita_form2" name="remita_form2" method="POST">

     		<div class="form-group">
     			<h4>Enter Medication Plan / Pre-Admission Note</h4>
     			<div id="edit__mode" style="color: red;"></div>

     			<div class="form-group">
     				<label for="reg_input_no" class="">Select Note Type</label>
     				<select id="note_type_2" name="note_type_2" class="form-control">

     					<option value="" selected>--select--</option>
     					<option value="plan">Plan</option>
     					<option value="pre-adm">Pre Admission Notes</option>
     				</select>
     			</div>
     			<textarea id="plan_note_2" name="plan_note_2" class="form-control" cols="45" rows="5" style="font-size:18px" required></textarea>
     		</div>

     		<input type="hidden" name="hospital_no" id="hospital_no" value="<?= $hospital_no; ?>">
     		<input type="hidden" name="appointment_number" id="appointment_number" value="<?= $appointment_number; ?>">

     		<input type="hidden" name="mode" id="mode_2" value="">
     		<input type="hidden" name="notes_sn" id="notes_sn" value="">
     		<input type="hidden" name="" id="date_entryy" value="">
     		<input type="hidden" name="" id="date_entryy2" value="">

     		<button class="btn btn-sm btn-success" id="add_note_btn" onclick="add_plan_note()" type="button"
     			<?php if ($edit_modee == '0' or $status == '0') { ?> disabled <?php } ?>>
     			<span class="btn-text">Add Notes</span><span class="loading line" style="display:none;"></span>
     		</button>

     		<?php if ($status == '0') { ?><br><strong style="color: red;">Please, you need to document on the Consultation tab before adding a plan.</strong> <?php } ?>

     	</form>
     	<?php ///} 
			?>

     	<div id="data_displayed"><strong style="color: red;">Loading ... Please Wait!</strong></div>


     <?php } ?>





     <script>
     	function add_plan_note() {

     		var plan_note = document.getElementById('plan_note_2').value;
     		var hospital_no = document.getElementById('hospital_no').value;
     		var appointment_number = document.getElementById('appointment_number').value;
     		var note_type_ = document.getElementById('note_type_2').value;
     		var mode = document.getElementById('mode_2').value;

     		if (plan_note != '' && hospital_no != '' && note_type_ != '' && mode == '') {

     			/* 		var rr = confirm("Are you sure you want to Save Note?");
     					if (rr === true) { */

     			$(".btn-text").html("Wait");
     			$(".btn .loading").show();
     			document.getElementById("add_note_btn").disabled = true;

     			$.ajax({
     				url: "../inc/plan_add_view.php",
     				method: "POST",
     				data: {
     					add_plan_notes: plan_note,
     					note_type_: note_type_,
     					hospital_no: hospital_no,
     					appointment_number: appointment_number
     				},
     				success: function(data) {

     					var json = JSON.parse(data);
     					//alert(json["status"]);
     					if (json["status"] == 0) {

     						patient_note_review();
     						refresh_form();
     						document.getElementById('plan_note_2').value = '';

     						toastr.info(json["message"], 'Saved', {
     							timeOut: 5000
     						});
     					} else {

     						toastr.error(json["message"], 'Error', {
     							timeOut: 5000
     						});
     						refresh_form();
     					}
     				}
     			});
     			//	}


     		} else if (plan_note != '' && mode == 'edit') {

     			var notes_sn = document.getElementById('notes_sn').value;
     			var plan_note = document.getElementById('plan_note_2').value;
     			var date_entry = document.getElementById('date_entryy').value;
     			var date_entry2 = document.getElementById('date_entryy2').value;

     			$(".btn-text").html("Wait");
     			$(".btn .loading").show();
     			document.getElementById("add_note_btn").disabled = true;

     			$.ajax({
     				url: "../inc/plan_add_view.php",
     				method: "POST",
     				data: {
     					review_note: plan_note,
     					edit_notes_update: notes_sn,
     					note_type_: note_type_,
     					date_entry: date_entry,
     					hospital_no: hospital_no,
     					date_entry2: date_entry2
     				},
     				success: function(data) {
     					var json = JSON.parse(data);

     					if (json["status"] == 0) {

     						document.getElementById("add_note_btn").disabled = true;
     						document.getElementById("plan_note_2").value = '';
     						patient_note_review();
     						refresh_form();
     						toastr.success(json["message"], 'Updated', {
     							timeOut: 5000
     						});
     					} else {
     						toastr.error(json["message"], 'Error', {
     							timeOut: 5000
     						});
     						refresh_form();

     					}
     				}
     			});

     		} else {
     			toastr.error('Invalid Data Entries', 'Error', {
     				timeOut: 5000
     			});
     			exit;
     		}

     	}



     	patient_note_review();

     	function patient_note_review() {

     		var hospital_no = document.getElementById('hospital_no').value;
     		var appointment_number = document.getElementById('appointment_number').value;

     		$.ajax({
     			url: "../inc/plan_add_view.php",
     			method: "POST",
     			data: {
     				patient_note_preview: hospital_no,
     				appointment_number: appointment_number
     			},
     			success: function(data) {
     				document.getElementById('data_displayed').innerHTML = data;

     			}
     		});
     	}



     	function refresh_form() {
     		$(".btn-text").html("Add Notes");
     		$(".btn .loading").hide();
     		document.getElementById("add_note_btn").disabled = false;
     		document.getElementById('edit__mode').innerHTML = '';

     	}



     	function edit_notes(sn) {

     		$.ajax({
     			url: "../inc/plan_add_view.php",
     			method: "POST",
     			data: {
     				edit_sn: sn
     			},
     			success: function(data) {

     				var json = JSON.parse(data);

     				///document.getElementById('plan_note_2').innerHTML = '';
     				document.getElementById('plan_note_2').value = json["plan_note_2"];
     				document.getElementById('note_type_2').value = json["note_type_2"];
     				document.getElementById('mode_2').value = 'edit';
     				document.getElementById('notes_sn').value = json["notes_sn"];
     				document.getElementById('date_entryy').value = json["date_entry"];
     				document.getElementById('date_entryy2').value = json["date_entry2"];
     				document.getElementById('edit__mode').innerHTML = '<strong>[ Edit Note Below ]</strong>';
     				document.getElementById('add_note_btn').innerHTML = 'Save Edited Notes';
     			}
     		});

     	}


     	function delete_notes(sn, sale_sn) {

     		var rr = confirm("Are you sure you want to DELETE?");
     		if (rr === true) {
     			$.ajax({
     				url: "../inc/plan_add_view.php",
     				method: "POST",
     				data: {
     					delete_notes: sn
     				},
     				success: function(data) {

     					//// alert(data);

     					var json = JSON.parse(data);
     					//alert(json["status"]);
     					if (json["status"] == 0) {
     						patient_note_review();
     						refresh_form();
     						toastr.success(json["message"], 'Deleted', {
     							timeOut: 5000
     						});
     					} else {
     						toastr.error(json["message"], 'Error', {
     							timeOut: 5000
     						});

     					}
     				}
     			});
     		}
     	}
     </script>