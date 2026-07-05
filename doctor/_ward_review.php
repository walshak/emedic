<?php
session_start();
include("../Connections/Conn.php");
///include("_session.php");
include('objects.php');
include('helpers.php');

if (isset($_POST['add_new_notes'])) {

	$response = array(
		'status' => 0,
		'message' => ''
	);

	$sn = cleanInput($_POST['add_new_notes']);
	$notes = ($_POST['review_note']);
	$tag = 'DR';
	$notes_type = 'serv_review';

	//// call details 
	$stmt_getd = $db->prepare("SELECT * FROM notes WHERE sn = '$sn'");
	$stmt_getd->execute();
	$rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC);
	$appointment_number = $rwxx['app_no'];
	$hospital_no = $rwxx['hospital_no'];
	$specialty = $rwxx['specialty'];
	$date_entry = date('Y-m-d'); ///
	$service_id = $rwxx['service_id'];
	$created_by = $_SESSION['id'];
	$prepared_by = $_SESSION['fullname'];

	$now_setdate = date('Y-m-d H:i:s');

	$stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, service_id)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
	$stmt->execute(
		array(
			$appointment_number,
			$hospital_no,
			$notes,
			$tag,
			$notes_type,
			$prepared_by,
			$now_setdate,
			$specialty,
			$created_by,
			$service_id
		)
	);

	if ($stmt->rowCount() > 0) {


		/* 		$roles = ['PH_Cancelled', 'NS__cancleed']; // Pharmacy and Nursing Staff
		foreach ($roles as $role) {
			$link = $role == 'PH' ? 'index.php?presc&hos_no=' : 'index.php?hosp_no=';
			$message_ = 'New Doctor Reviewed Note has been entered for patient ' . $hospital_no .
				'. <a href="' . $link . $hospital_no . '">View Patient</a>';
			global_notify_($db, 'rights2', $role, '<b>New Doctor Reviewed Notes</b>', $message_);
		}
 */

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

if (isset($_POST['delete_notes'])) {

	$response = array(
		'status' => 0,
		'message' => ''
	);

	$id = $_POST['delete_notes'];
	$sale_sn = $_POST['sale_sn'];

	$check = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = ? and paystatus = '0' ");
	$check->execute(array($sale_sn));
	if ($check->rowCount() > 0) {
		/// delete not paid yet
		$row = $check->fetch(PDO::FETCH_ASSOC);
		$app_no = $row['app_no'];
		$hospital_no = $row['hospital_no'];
		$service_id = $row['drug_sn'];

		$delete = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
		$deleted = $delete->execute(array($id));

		if ($deleted) {
			// if($notes_type == 'serv_review'){
			/// delete payment
			$delete = $db->prepare("DELETE FROM  patient_ap_services  WHERE hospital_no = ? AND app_no = ? AND drug_sn = ?  AND serv_group = 'Review' ");
			$deleted = $delete->execute(array($hospital_no, $app_no, $service_id));
			//  }
			$response['message'] = 'Successful!';
			$response['status'] = '0';
			echo  json_encode($response);
			exit;
		}

		$response['message'] = 'Not Successful!';
		$response['status'] = '1';
		echo  json_encode($response);
		exit;
	} else {
		$response['message'] = 'Not Successful!';
		$response['status'] = '1';
		echo  json_encode($response);
	}
	exit;
}

if (isset($_POST['edit_sn'])) {

	$response = array(
		'notes' => '',
		'notes_sn' => ''
	);

	$edit_sn = $_POST['edit_sn'];
	$stmt_getd = $db->prepare("SELECT notes,date_entry,date_entry2 FROM notes WHERE sn = '$edit_sn'");
	$stmt_getd->execute();
	$rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC);
	$response['notes'] = $rwxx['notes'];
	$response['date_entry'] = $rwxx['date_entry'];
	$response['date_entry2'] = $rwxx['date_entry2'];
	$response['notes_sn'] = $edit_sn;
	echo  json_encode($response);
	exit;
}

if (isset($_POST['patient_review'])) {

	$sn = 1;
	$total_srvices = 0;
	$hospital_no = $_POST['patient_review'];

	$stmt = $db->prepare("SELECT 
	sn, paystatus, pay, item_services, drug_sn, app_no,pay_mode,claim_amt,created_by,date_entry, prepared_by
	FROM patient_ap_services 
	WHERE (serv_group = 'Review' or serv_group = 'Doctor Review') AND hospital_no = $hospital_no order by sn desc limit 15 ");
	$stmt->execute();
	if ($stmt->rowCount() > 0) { ?>


		<div style=" max-height:300px; overflow:auto">
			<table class="table table-bordered table-striped table-hover table-responsive">
				<thead>
					<tr>
						<th>#</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?php
					while ($pending_service = $stmt->fetch()) {

						$app_no = $pending_service["app_no"];
						$sale_sn = $pending_service["sn"];
						$created_by = $pending_service["created_by"];
						$prepared_by = $pending_service["prepared_by"];
						$drug_sn = $pending_service["drug_sn"];
						$date_entry = $pending_service["date_entry"];
						$date_entry = substr($date_entry, 0, -9);


						$stmt_getd = $db->prepare("SELECT * FROM notes WHERE app_no = '$app_no'  AND created_by = '$created_by' AND hospital_no = '$hospital_no' AND service_id = '$drug_sn' AND date(date_entry) = '$date_entry' AND status='1' order by sn desc");
						$stmt_getd->execute();
						while ($rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC)) {

							$amount = $pending_service["pay_mode"] == 'claim' ? $pending_service["claim_amt"] : $pending_service["pay"];
							$total_srvices += $amount;
					?>
							<tr>
								<td><?= $sn++; ?></td>
								<td><?php
									echo '<strong>' . $pending_service["item_services"] . '</strong><br>';
									echo $rwxx["notes"]; ?>
									<br>
									<?php echo '<b>[ Amount:' . number_format($amount) . ' /' . ($pending_service["paystatus"] == '1' ? "Paid" : "Not Paid") . ']</b>';	?><br>
									<small><b><i>Entered by: <?php echo $rwxx['prepared_by']; ?>
												Date: <?php echo formatDateTime_($rwxx['date_entry']); ?></i></b></small>
									<br>

									<?php if (date_diff_day($date_entry) < 1 && $_SESSION['fullname'] == $prepared_by) { ?>
										<button type="button" class="btn btn-xs btn-warning" id="edit" onclick="edit_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-edit"></i>&nbsp;Edit</button>
									<?php  }	?>

									<?php if (date_diff_day($date_entry) < 1) { ?>
										<button type="button" class="btn btn-xs btn-success" id="add" onclick="add_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-plus"></i>&nbsp;Add More Notes</button>
									<?php  }	?>





								</td>
							</tr>
					<?php	}
					} ?>
				</tbody>
			</table>
		</div>
		<h3>Total Amount: =N= <?= number_format($total_srvices) ?></h3>
	<?php } else {
		// echo 'No services ';
	}
}

if (isset($_POST['edit_notes_update'])) {
	///review_note:review_note,edit_notes_update:notes_sn
	$response = array(
		'status' => 0,
		'message' => ''
	);

	$sn = cleanInput($_POST['edit_notes_update']);
	$review_note = $_POST['review_note'];
	$date_entry2 = $_POST['date_entry2'];
	$date_entry = $_POST['date_entry'];
	$hospital_no = $_POST['hospital_no'];
	$notes_type = $_POST['notes_type'];

	$save_note = editNotes($db, $sn, $review_note, $date_entry, $date_entry2);

	if ($save_note == true) {
		if ($_SESSION['rights'] == 'DR' && ($notes_type == 'treatment' || $notes_type == 'plan')) {
			$roles = ['PH', 'NS']; // Pharmacy and Nursing Staff
			foreach ($roles as $role) {
				$link = $role == 'PH' ? 'index.php?presc&hos_no=' : 'index.php?hosp_no=';
				$message_ = 'The plan has been edited for the patient ' . $hospital_no .
					' <a href="' . $link . $hospital_no . '">View Patient</a>';
				global_notify_($db, 'rights2', $role, '<b>Patient Care Plan Edited by Doctor</b>', $message_);
			}
		}
		$response['message'] = 'Successful!';
		$response['status'] = '0';
		echo  json_encode($response);
	} else {
		$response['message'] = 'Invalid notes or you must type atleast 5 words or updating notes that already exist!';
		$response['status'] = '1';
		echo  json_encode($response);
	}
	exit;
}

if (isset($_POST['servie_id'])) {
	$nhis_price = null;

	$response = array(
		'status' => 0,
		'message' => ''
	);

	$review_service = cleanInput($_POST['servie_id']);
	$review_note = ($_POST['review_note']);
	$hospital_no = cleanInput($_POST['hospital_no']);
	$appointment_number = cleanInput($_POST['appointment_number']);
	$interest = cleanInput($_POST['interest']);
	$insurance = cleanInput($_POST['patient_insurance']);
	$ap_type = cleanInput($_POST['ap_type']);


	$review_service_arr = explode("||", $review_service);
	$service_id = $review_service_arr[0];
	$hosp_price = $review_service_arr[1];
	$dept_id = $review_service_arr[2];
	$item_service = $review_service_arr[3];
	$nhis_price = $review_service_arr[4];
	$cat_type = $review_service_arr[5];
	$cash_price = $hosp_price;
	$error_status = 1;
	$error_msg = 'Error : something went wrong ';

	$serv_group = 'Medical Services';
	$cat_type = 'Doctor Review';

	$patient_info = $Patient->getByHospitalNo($hospital_no);
	$ap_type = 0;
	if (!empty($patient_info)) {
		$insurance_name = $patient_info->insurance_name;
		$interest = $patient_info->interest;
		$insurance_type = $patient_info->insurance_type;
		$services_access = $patient_info->services_access;
		$insurance_no = $patient_info->insurance_no;
		$payment_mode = $patient_info->payment_mode;
		$add_minus = $patient_info->add_minus;

		try {
			$db->beginTransaction();

			$target_sn = $service_id;
			$NHIS_DRUG_CONSUMBL_STATE = 0;
			$_tariff_table = "hmo_medical_tariff";
			$amount_invoice = new_service_amount_cal(
				$db,
				$hospital_no,
				$appointment_number,
				$interest,
				$insurance_type,
				$insurance_no,
				$hosp_price,
				$ext_price,
				$nhis_price,
				$services_access,
				$add_minus,
				$payment_mode,
				$_tariff_table,
				$target_sn,
				$NHIS_DRUG_CONSUMBL_STATE,
				true
			);

			$stmt = $db->prepare("INSERT INTO patient_ap_services (
    app_no,hospital_no,access,serv_group,cat_type,dept_id,drug_sn,item_services,
    tag,hosp_price,claim_amt,interest,qty,remarks,drug_status,invoice_status,
    invoice_no,invoice_date,invoice_by,prepared_by,transact_date,pay,pay_mode,
    paystatus,process_claim,created_by
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			$date = date('Y-m-d H:i:s');
			$transact_date = null;
			$save = $stmt->execute(array(
				$appointment_number,
				$hospital_no,
				1,
				$serv_group,
				$cat_type,
				$dept_id,
				$service_id,
				$item_service,
				'',
				$hosp_price,
				$amount_invoice["claim_amt"],
				$amount_invoice["ccop_int_charge"],
				1,
				'',
				'0',
				1,
				$amount_invoice["invoice_no"],
				$date,
				$_SESSION['fullname'],
				$_SESSION['fullname'],
				NULL,
				$amount_invoice["amount_paying"],
				$amount_invoice["pay_mode"],
				0,
				0,
				$_SESSION['id']
			));

			if (!$save) {
				$errorInfo = $stmt->errorInfo();
				throw new Exception("patient_ap_services insert failed: " . $errorInfo[2]);
			}

			if ($save == true) {
				$save_note = saveToNotes($db, $appointment_number, $hospital_no, $review_note, 'DR', 'serv_review', $_SESSION['fullname'], $item_service,  $_SESSION["id"], false, $service_id);
				$error_status = 2;
				$error_msg = 'Success : Saved';

				$db->commit();


				$response['message'] = 'Successful!';
				$response['status'] = '0';
				echo json_encode($response);
			} else {
				$db->rollBack();

				$response['message'] = 'Not Successful!';
				$response['status'] = '1';
				echo json_encode($response);
			}
		} catch (Exception $e) {
			$db->rollBack();
			$response['message'] = 'Error: ' . $e->getMessage();
			$response['status'] = '1';
			echo json_encode($response);
		}
	}
	exit;
}

if (isset($_POST['prv_adm_hx'])) {
	$hospital_no = $_POST['prv_adm_hx'];
	$stmt = $db->prepare("SELECT * FROM admission WHERE hospital_no = '$hospital_no' AND (adm_status = 4 or adm_status = 3)  order by sn desc");
	$stmt->execute();
	if ($stmt->rowCount() > 0) { ?>

		<h3>Previous Admission History</h3><br>
		<div style=" max-height:600px; overflow:auto">
			<table class="table table-bordered table-striped table-hover table-responsive" style="font-size: 15px; ">
				<thead>
					<tr>
						<th>#</th>
						<th width="50%">Reason Admitted</th>
						<th width="50%">Discharge Notes</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$n = 1;
					while ($pending_service = $stmt->fetch()) {
						if ($pending_service["date_discharge"] != '') {
							$dd = date('d M, Y: H:i:s a ', strtotime($pending_service["date_discharge"]));
						} else {
							$dd = 'N/A';
						}

					?>
						<tr>

							<td><?= $n++; ?></td>
							<td><?= $pending_service["reason_adm"] .
									'<br><i><b>' . date('d M, Y: H:i:s a ', strtotime($pending_service["date_admit"])) . '</b></i>' .
									'<br><i><b>Admitted by:</b> </i></b>' . $pending_service["doc_incharge"]; ?>
							</td>
							<td><?= $pending_service["discharge_note"] .
									'<br><i><b>' . $dd . '</b></i>' .
									'<br> <b>Room Admitted:</b> ' . $pending_service["room_bed"] .
									'<br><i><b>Discharged by:</b> </i></b>' . $pending_service["discharge_name"]; ?>
							</td>
						</tr>


					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } else { ?>
		<h3>No Record Available!</h3>
	<?php }
}

if (isset($_POST['add_review_post'])) {
	///$details=$appointment_number .'__'.$hospital_no.'__'. $patient_insurance .'__'. $patient_access_type;
	$add_review_post = $_POST['add_review_post'];
	$pp = explode("__", $add_review_post);
	$appointment_number = $pp[0];
	$hospital_no = $pp[1];
	$patient_insurance = $pp[2];
	$patient_access_type = $pp[3];
	$appointment_interest = $pp[4];
	$add_minus = $pp[5];

	?>
	<div id="loader" class="loader"></div>
	<form action="" id="remita_form2" name="remita_form2" method="POST">
		<div class="form-group">
			<h4>Select <u>Type</u> of Service </h4>
			<select name="review_servie_id" class="form-control" style="font-size:15px" id="selected_review_servie_id" onChange="refresh_form()">
				<option value=""> -- Select from list -- </option>
				<?php
				try {
					$stmt = $db->prepare("SELECT sn, item_service, hosp_price, dept, nhis_price,category FROM prices_table WHERE price_table = 'Medical Services' ORDER BY item_service");
					$stmt->execute();

					while ($service = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$value = $service["sn"] . '||' . $service["hosp_price"] . '||' . $service["dept"] . '||' . $service["item_service"] . '||' . $service["nhis_price"] . '||' . $service["category"];
						$label = $service["item_service"] . ' ( ' . number_format($service["hosp_price"]) . ' )';
						echo "<option value=\"$value\">$label</option>";
					}
				} catch (PDOException $e) {
					// Handle exception (optional)
					echo "<option value=\"\">Error fetching data</option>";
				}
				?>
			</select>

		</div>

		<div class="form-group">
			<h4>Enter Review Notes for the selected service below:</h4>
			<div id="edit__mode" style="color: red;"></div>
			<textarea name="review_note" class="form-control" cols="45" rows="5" placeholder="" style="font-size:18px" id="selected_review_note" required></textarea>
		</div>
		<button type="button" class="btn btn-sm btn-success" id="pay_now" name="pay_now" onclick="service_bill()">Add Notes and Bill Patient</button>
		<hr>

		<div id="show_record"><strong style="color: red;">Loading ... Please Wait!</strong></div>

		<input type="hidden" name="hospital_no" id="hospital_no" value="<?= $hospital_no; ?>">
		<input type="hidden" name="appointment_number" id="appointment_number" value="<?= $appointment_number; ?>">
		<input type="hidden" name="insurance" id="insurance" value="<?= $patient_insurance; ?>">
		<input type="hidden" name="ap_type" id="ap_type" value="<?= $patient_access_type; ?>">
		<input type="hidden" name="interest" id="interest" value="<?= $appointment_interest; ?>">
		<input type="hidden" name="add_minus" id="add_minus" value="<?= $add_minus; ?>">

		<input type="hidden" name="mode" id="mode" value="">
		<input type="hidden" name="notes_sn" id="notes_sn">
		<input type="hidden" name="" id="date_entryy">
		<input type="hidden" name="" id="date_entryy2">

		<div class="modal-footer">

			<button type="button" class="btn btn-sm btn-default " data-dismiss="modal">Close</button>
		</div>
	</form>
<?php

}

?>



<script>
	patient_review();

	function patient_review() {

		$('#loader').show();

		var hospital_no = document.getElementById('hospital_no').value;
		var appointment_number = document.getElementById('appointment_number').value;


		$.ajax({
			url: "../inc/_ward_review_fetch.php",
			method: "POST",
			data: {
				patient_review: hospital_no,
				appointment_number: appointment_number
			},
			success: function(data) {
				$('#show_record').html(data);
				///.getElementById('data_displayed').value = data;

			}
		});
	}


	function edit_notes(sn) {

		$.ajax({
			url: "_ward_review.php",
			method: "POST",
			data: {
				edit_sn: sn
			},
			success: function(data) {



				var json = JSON.parse(data);
				document.getElementById('selected_review_note').innerHTML = '';
				document.getElementById('selected_review_note').value = json["notes"];
				document.getElementById('date_entryy').value = json["date_entry"];
				document.getElementById('date_entryy2').value = json["date_entry2"];


				document.getElementById('mode').value = 'edit';
				document.getElementById('notes_sn').value = json["notes_sn"];
				document.getElementById('edit__mode').innerHTML = '<strong>[ Edit Note Below ]</strong>';
				document.getElementById('pay_now').innerHTML = 'Save Edited Notes';
				document.getElementById("pay_now").disabled = false;
				document.getElementById('selected_review_servie_id').value = '';

			}
		});

	}


	function add_notes(sn) {
		document.getElementById('mode').value = 'add';
		document.getElementById('notes_sn').value = sn;
		document.getElementById('edit__mode').innerHTML = '<strong>[ Add New Note Below ]</strong>';
		document.getElementById('pay_now').innerHTML = 'Save Notes';
		document.getElementById("pay_now").disabled = false;
		document.getElementById('selected_review_servie_id').value = '';

	}


	function delete_notes(sn, sale_sn) {

		var rr = confirm("Are you sure you want to DELETE?");
		if (rr === true) {
			$.ajax({
				url: "_ward_review.php",
				method: "POST",
				data: {
					delete_notes: sn,
					sale_sn: sale_sn
				},
				success: function(data) {

					//// alert(data);

					var json = JSON.parse(data);
					//alert(json["status"]);
					if (json["status"] == 0) {
						patient_review();
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



	function refresh_form() {
		document.getElementById("pay_now").disabled = false;
		document.getElementById("mode").value = '';
		document.getElementById("edit__mode").innerHTML = '';
		document.getElementById('pay_now').innerHTML = 'Add Notes and Bill Patient';
		document.getElementById('selected_review_note').value = '';
	}


	function service_bill() {

		var servie_id = document.getElementById('selected_review_servie_id').value;
		var review_note = document.getElementById('selected_review_note').value;
		var hospital_no = document.getElementById('hospital_no').value;
		var appointment_number = document.getElementById('appointment_number').value;
		var patient_insurance = document.getElementById('insurance').value;
		var interest = document.getElementById('interest').value;
		var ap_type = document.getElementById('ap_type').value;
		var mode = document.getElementById('mode').value;
		var date_entryy2 = document.getElementById('date_entryy2').value;
		var date_entryy = document.getElementById('date_entryy').value;
		///var selected_review_note = document.getElementById('selected_review_note').value;	

		if (servie_id != '' && review_note != '' && mode == '') {

			var rr = confirm("Are you sure you want to Save and Bill this Patient?");
			if (rr === true) {

				$.ajax({
					url: "_ward_review.php",
					method: "POST",
					data: {
						servie_id: servie_id,
						review_note: review_note,
						hospital_no: hospital_no,
						appointment_number: appointment_number,
						patient_insurance: patient_insurance,
						ap_type: ap_type,
						date_entryy2: date_entryy2,
						date_entryy: date_entryy,
						interest: interest
					},
					success: function(data) {
						var json = JSON.parse(data);
						//alert(json["status"]);
						if (json["status"] == 0) {

							document.getElementById("pay_now").disabled = true;
							document.getElementById("selected_review_note").value = '';
							patient_review();
							toastr.info(json["message"], 'Saved', {
								timeOut: 5000
							});
						} else {
							toastr.error(json["message"], 'Error', {
								timeOut: 5000
							});

						}
					}
				});

			} else {
				setTimeout(function() {
					$("#overlay").fadeOut();
				}, 500);
				exit;
			}

		} else if (review_note != '' && mode == 'edit') {

			var notes_sn = document.getElementById('notes_sn').value;
			var review_note = document.getElementById('selected_review_note').value;
			var date_entry = document.getElementById('date_entryy').value;
			var date_entry2 = document.getElementById('date_entryy2').value;

			$.ajax({
				url: "_ward_review.php",
				method: "POST",
				data: {
					review_note: review_note,
					edit_notes_update: notes_sn,
					date_entry: date_entry,
					date_entry2: date_entry2,
				},
				success: function(data) {
					var json = JSON.parse(data);
					//alert(json["status"]);
					if (json["status"] == 0) {

						document.getElementById("pay_now").disabled = true;
						document.getElementById("selected_review_note").value = '';
						patient_review();
						refresh_form();
						toastr.success(json["message"], 'Updated', {
							timeOut: 5000
						});
					} else {
						toastr.error(json["message"], 'Error', {
							timeOut: 5000
						});

					}
				}
			});

		} else if (review_note != '' && mode == 'add') {

			var notes_sn = document.getElementById('notes_sn').value;
			var review_note = document.getElementById('selected_review_note').value;

			$.ajax({
				url: "../doctor/_ward_review.php",
				method: "POST",
				data: {
					review_note: review_note,
					add_new_notes: notes_sn
				},
				success: function(data) {
					var json = JSON.parse(data);
					//alert(json["status"]);
					if (json["status"] == 0) {

						document.getElementById("pay_now").disabled = true;
						document.getElementById("selected_review_note").value = '';
						patient_review();
						refresh_form();
						toastr.success(json["message"], 'Note Added', {
							timeOut: 5000
						});
					} else {
						toastr.error(json["message"], 'Error', {
							timeOut: 5000
						});

					}
				}
			});


		} else {
			toastr.error('Invalid Service Selection or Empty Notes', 'Error', {
				timeOut: 5000
			});
		}
	}
</script>