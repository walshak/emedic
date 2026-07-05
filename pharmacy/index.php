<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");

$nn = null;
$insurance_name = null;
$insurance = null;
$insurance_status = null;
$desccc = null;
$staff = null;
$searchdrug_inv = null;
$qty_err  = null;
$error_status  = null;
$hos_no  = null;


function checkReminders()
{
	global $db;
	// Get the current date
	$current_date = date('Y-m-d');
	// Prepare the SQL query to select reminders based on the days_before_reminder setting
	$stmt = $db->prepare("
        SELECT pharm_drug_reminder.*, 
               stock_table.product_name, 
               enrollee.surname, 
               enrollee.fname, 
               enrollee.oname, 
               CASE WHEN pharm_drug_reminder.reminder_date < :current_date1 THEN true ELSE false END AS overdue
        FROM pharm_drug_reminder
        LEFT JOIN stock_table ON stock_table.sn = pharm_drug_reminder.drug_sn
        LEFT JOIN enrollee ON enrollee.hospital_no = pharm_drug_reminder.hos_no
        WHERE DATE_SUB(pharm_drug_reminder.reminder_date, INTERVAL pharm_drug_reminder.days_before_reminder DAY) <= :current_date2
              AND pharm_drug_reminder.status = 'enabled'
    ");
	$stmt->bindParam(':current_date1', $current_date);
	$stmt->bindParam(':current_date2', $current_date);
	$stmt->execute();

	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$reminders = checkReminders();

//dismiss a reminder
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['dismiss_rem'])) {
	$sn_rem = $_GET['dismiss_rem'];
	if ($sn_rem != '') {
		$stmt = $db->prepare("UPDATE pharm_drug_reminder SET status = 'disabled' WHERE sn = :sn_rem");
		$stmt->bindParam(':sn_rem', $sn_rem);
		$stmt->execute();
	} else {
		$stmt = $db->prepare("UPDATE pharm_drug_reminder SET status = 'disabled'");
		$stmt->execute();
	}

	// echo "<script>
	// 	toastr.success('Done', 'Success', {
	// 			timeOut: 5000
	// 		})
	// </script>";
	if (isset($hos_no) && $hos_no != '') {
		header("Location: index.php?presc&hos_no=$hos_no&sv=1");
	} else {
		header("Location: index.php?sv=1");
	}
}
?>

<!DOCTYPE html>
<html>
<?php
include("../inc/header.php");
?>

<?php

if (!isset($_GET['presc'])) {
	include_once("../inc/alert_msg.php");
}
if (isset($_POST['apply_reversed'])) {

	$from_date = ($_POST['from_date']);
	$to_date = ($_POST['to_date']);

	header("location:index.php?Reversed=$from_date/$to_date");
}


if (isset($_POST['apply_requester'])) {

	$decodedData = base64_decode($_POST['requesters_drugs']);
	///echo $decodedData;
	// presc&hos_no=306639

	$pp = explode("__", $decodedData);
	$R_hospital_no = $pp[0];
	$requester = base64_encode($pp[1]);
	header("location:index.php?presc&hos_no=$R_hospital_no&RQ=$requester");
}

if (isset($_POST['skip_popup'])) {
	if ($_SESSION["skip_popup"] == '') {
		$_SESSION["skip_popup"] = 'off';
	} else {
		$_SESSION["skip_popup"] = '';
	}
} elseif (isset($_POST['skip_popup_2'])) {
	$_SESSION["skip_popup"] = '';
}
?>

<body>
	<div id="wrapper">
		<?php include("nav_side.php"); ?>
		<div id="page-wrapper" class="gray-bg">
			<?php include("nav_header.php"); ?>

			<div class="wrapper wrapper-content">

				<?php
				if (strtoupper($_SESSION['password']) == 'STAFF123') {
					header("location:../profile/index.php?profile=$uname&changepassword");
				}

				if (isset($_GET['presc'])) {
					include("presc.php");
				} elseif (isset($_POST['pay_for_deposit'])) {

					$pro_inv = $_REQUEST['inv'];
					if (!empty($pro_inv)) {

						for ($i = 0; $i < count($pro_inv); $i++) {
							$inv_id = $pro_inv[$i];
							$break = explode("__", $inv_id);
							$sn = $break[0];
							$item = $break[1];
							$pay = $break[2];
							$claim = $break[3];
							$qty = $break[4];
						}
					}
				} elseif (isset($_GET['invoice'])) {
					//   include("invoice.php");
					include("inv.php");
				} elseif (isset($_GET['rpt'])) {
					include("report.php");
				} elseif (isset($_GET['print'])) {
					include("print.php");
				} elseif (isset($_GET['price'])) {
					include("price_enquiry.php");
				} else {
					include("dashb.php");
				}
				?>



				<div id="pharmacyAlertBox" class="pharm-queue-alert" style="display:none; cursor:pointer; background:#e2f0ff; padding:10px; border-radius:5px;">
					💊 New Prescriptions (View):
					<strong id="pharmacy_count">0</strong>
				</div>


				<div class="modal inmodal" id="document_note_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
					<div class="modal-dialog modal-xl">
						<div class="modal-content animated bounceInRight">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

								<h4 class="modal-title">Patient's Documentation</h4>
							</div>

							<div class="modal-body" id="document_note_body">


							</div>
						</div>
					</div>
				</div>


				<div class="modal inmodal fade" id="get_xe_patient_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title">External Patients</h4>
							</div>
							<div class="modal-body">
								<div id="get_xe_patient_body" style="height:100px;">
									<div class="input-group">
										<input type="text" id="ex_patient_input" class="form-control" placeholder="Enter number without the EX & Press Enter Key or Click On GO ..." autofocus>
										<span class="input-group-btn">
											<button id="ex_patient_go" class="btn btn-primary" type="button">Go</button>
										</span>
									</div>
									<div id="ex_patient_feedback" style="margin-top:10px;"></div>
								</div>
							</div>
						</div>
					</div>
				</div>


				<div class="modal inmodal fade" id="staff_alert_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="">Staff Alert !</h4>
							</div>

							<div class="modal-body" id="claims_body">

								<?php if ($display_status == 1) { ?>

									<form action="index.php" method="POST">

										<p><?php echo $my_note; ?></p>

										<button type="submit" name="read_msg" class="btn btn-primary">Yes, I have read it</button>
									</form>

								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="pharmacyModal">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h4>New Prescriptions</h4>
							</div>

							<div class="modal-body" style="max-height:400px; overflow:auto;">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Hospital No</th>
											<th>Time</th>
											<th>Prepared By</th>
											<th>Total Drugs</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody id="pharmacy_body"></tbody>
								</table>
							</div>

							<div class="modal-footer">
								<button class="btn btn-secondary" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>

				<?php include('../modal_lock.php'); ?>
				<?php $inv_count = $nn; ?>
			</div>

			<?php
			$hospital_no = $hos_no;
			include("../inc/patient_alert.php"); ?>
			<?php include("../inc/footer.php"); ?>
		</div>
	</div>

	<script src="../js/jquery-ui.min.js"></script>
	<?php include("../inc/footer_scripts.php"); ?>

	<script>
		<?php

		if ($error_status == 1) { ?>
			toastr.error('<?php echo $error_msg ?>', 'Error', {
				timeOut: 5000
			})

		<?php } else if ($error_status == 2) {
		?>
			toastr.success(' <?php echo $error_msg ?> ', 'Success', {
				timeOut: 5000
			})
		<?php

		}

		?>


		display_requester();

		function display_requester() {
			var requester = 100;

			$.ajax({
				url: "get-requester.php",
				data: {
					requester_post: requester
				},
				type: 'POST',
				success: function(response) {
					///	alert(response);

					$("#requesters_drugs").html(response);
					// Initialize Chosen library after AJAX request has completed
					$(".chosen-select").chosen();
					// Update Chosen library to reflect new options
					$(".chosen-select").trigger("chosen:updated");
				}
			});
		}


		function display_alert(hosp_no) {
			$.ajax({
				url: "../inc/set_alert_patients.php",
				method: "POST",
				data: {
					check_alert: hosp_no
				},
				success: function(data) {
					document.getElementById("patient-alert-tbody").innerHTML = data;
				}
			});
		}


		///display_requester();
	</script>

	<?php if (isset($reminders) && !empty($reminders)) { ?>
		<!-- Reminder Alert Modal -->
		<div class="modal fade" id="reminderAlertModal" tabindex="-1" role="dialog" aria-labelledby="reminderAlertModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title" id="reminderAlertModalLabel">Refill Reminder</h4>
					</div>
					<div class="modal-body">
						<p>You have upcoming or overdue refill reminders for the following drugs:</p>
						<table class="table table-sm table-stripped">
							<thead>
								<th>Drug</th>
								<th>Patient</th>
								<th>Due Date</th>
								<th>Action</th>
							</thead>
							<tbody>
								<?php foreach ($reminders as $reminder) { ?>
									<tr>
										<td><?php echo $reminder['product_name'] ?></td>
										<td><?php echo $reminder['surname'] . ' ' . $reminder['fname'] . ' ' . $reminder['oname'] ?></td>
										<td><?php echo date('d M, Y', strtotime($reminder['reminder_date'])) ?> <?php echo ($reminder['overdue'] == 1) ? '&nbsp;<span class=\'text-danger\'>Overdue</span>' : '' ?></td>
										<td>
											<a href="?dismiss_rem=<?php echo $reminder['sn'] ?>" class="btn btn-xs btn-success">Dismiss</a>
											<br>
											<br>
											<a href="index.php?presc&hos_no=<?php echo $reminder['hos_no'] ?>" class="btn btn-xs btn-primary">View Patient</a>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
						<a href="?dismiss_rem" class="btn btn-danger" onclick="return confirm('Are you sure you wish to dismiss all existing reminders?')">Dismiss All Reminders</a>

					</div>
				</div>
			</div>
		</div>
		<script>
			var reminderAlertModal = $('#reminderAlertModal');
			var modal = reminderAlertModal.modal('show');
			modal.show();
		</script>
	<?php } ?>

	<?php if ($display_status == 1) { ?>
		<script>
			$(document).ready(function() {
				$("#staff_alert_modal").modal('show');
			});
		</script>
	<?php  } ?>

	<?php if (isset($_GET['sv'])) { ?>
		<script>
			toastr.success('Saved Successfully!', 'Success', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if (isset($_GET['err'])) { ?>
		<script>
			toastr.error('Not Saved Successfully!', 'Error', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if (isset($_GET['ss'])) { ?>
		<script>
			toastr.success('Apply Claim Successfully!', 'Success', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if (isset($_GET['s1'])) { ?>
		<script>
			toastr.success('Reversed Successfully!', 'Success', {
				timeOut: 5000
			})
		</script>
	<?php } ?>
	<?php if (isset($_GET['dp'])) { ?>
		<script>
			toastr.success('Drug Dispensed Successful!', 'Successful', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if (isset($_GET['Nex'])) { ?>
		<script>
			toastr.error('This patient Number Does Not Exist. Please try again!', 'Error', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if ($qty_err == 1  or isset($_GET['q'])) { ?>
		<script>
			toastr.error('Insufficient Stock Quantity', 'Error', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if (isset($_GET['runing_err'])) { ?>
		<script>
			toastr.error('Insufficient Stock Quantity <br> Confirm from Notes button/Store <br> Cancel/Delete add New Request', 'Error', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<script>
		<?php if ($display_status == 1) { ?>
			$(document).ready(function() {
				$("#discharge_booking_modal").modal('show');
			});
		<?php  } ?>
	</script>
	<script>
		<?php
		//echo $skip_popup='pop_up'.$hos_no;
		if (isset($_SESSION["skip_popup"]) and $_SESSION["skip_popup"] == '') { ?>
			$(document).ready(function() {
				$("#med_pop_up").modal('show');
			});
		<?php  } ?>
	</script>

	<script>
		$(document).ready(function() {
			$.ajax({
				url: "get_invoiced_data.php",
				method: "POST",
				data: {
					request: true
				},
				success: function(data) {

					$('#request-table-container').html(data);
				}
			});
		});


		function handleApplyTask2() {
			var to_date = $('#to_date').val();
			var from_date = $('#from_date').val();
			$.ajax({
				url: "get_invoiced_data.php",
				method: "POST",
				data: {
					encounter: true,
					to_date: to_date,
					from_date: from_date
				},
				success: function(data) {
					$('#encounter-table-container').html(data);
				}
			});
		}

		function handleFloorChange(selectElement) {
			const selectedValue = selectElement.value;
			$.ajax({
				url: "get_invoiced_data.php",
				method: "POST",
				data: {
					adm: true,
					floor: selectedValue
				},
				success: function(data) {


					toastr.success('Loading ...', '', {
						timeOut: 500
					});
					$('#adm-table-container').html(data);
				}
			});
		}


		function myFunction_inPatientBtn() {
			$.ajax({
				url: "get_invoiced_data.php",
				method: "POST",
				data: {
					adm: true
				},
				success: function(data) {
					toastr.success('Loading Request ...', '', {
						timeOut: 500
					});
					$('#adm-table-container').html(data);
					$('#in_patient_modal').modal('show');
				}
			});

		}



		function get_ex_patients() {
			$('#get_xe_patient_modal').modal('show');
		}

		// Search and redirect
		function redirectExPatient() {
			var term = $('#ex_patient_input').val().trim();
			$(document).ready(function() {
				$.ajax({
					url: "get_invoiced_data.php",
					method: "POST",
					data: {
						get_ex: true,
						term: term
					},
					success: function(data) {
						if (data) {

							///alert(data);  /// /index.php?presc&hos_no=EX100#
							window.location.href = 'index.php?presc&hos_no=' + data;
						} else {
							$('#ex_patient_feedback').html('<span style="color:red;">No matching patient found.</span>');
						}
					}
				});
			});
		}

		// Events
		$('#ex_patient_go').click(redirectExPatient);
		$('#ex_patient_input').keypress(function(e) {
			if (e.which === 13) { // Enter key
				e.preventDefault();
				redirectExPatient();
			}
		});
		$('#get_xe_patient_modal').on('shown.bs.modal', function() {
			$('#ex_patient_input').val('').focus();
			$('#ex_patient_feedback').html('');
		});



		<?php if (isset($_GET['adm'])) { ?>
			$(document).ready(function() {
				$.ajax({
					url: "get_invoiced_data.php",
					method: "POST",
					data: {
						adm: true
					},
					success: function(data) {
						toastr.success('Loading Drugs Request ...', '', {
							timeOut: 500
						});
						$('#adm-table-container').html(data);
					}
				});
			});
		<?php } ?>
		<?php if (isset($_GET['vst'])) { ?>
			$(document).ready(function() {
				$.ajax({
					url: "get_invoiced_data.php",
					method: "POST",
					data: {
						encounter: true
					},
					success: function(data) {
						$('#encounter-table-container').html(data);
					}
				});
			});
		<?php } ?>

		<?php if (isset($_GET['pend'])) { ?>
			$(document).ready(function() {
				$.ajax({
					url: "get_invoiced_data.php",
					method: "POST",
					data: {
						pend: true
					},
					success: function(data) {
						toastr.success('Loading Drugs Request ...', '', {
							timeOut: 500
						});
						$('#pend-table-container').html(data);
					}
				});
			});
		<?php } ?>


		<?php if (isset($_GET['invoiced'])) { ?>

			$(document).ready(function() {
				$.ajax({
					url: "get_invoiced_data.php",
					method: "POST",
					data: {
						invoiced: true
					},
					success: function(data) {
						toastr.success('Loading Invoiced Request ...', '', {
							timeOut: 500
						});
						$('#invoiced-table-container').html(data);
					},
					error: function() {
						toastr.error('Failed to load data.');
					}
				});
			});


		<?php } ?>

		<?php if (isset($_GET['Reversed'])) {

		?>

			$(document).ready(function() {
				$.ajax({
					url: "get_invoiced_data.php",
					method: "POST",
					data: {
						Reversed: true,
						from_date: '<?php echo explode("/", $_GET['Reversed'])[0] ?>',
						to_date: '<?php echo explode("/", $_GET['Reversed'])[1] ?>'
					},
					success: function(data) {
						toastr.success('Loading Reversed Drugs ...', '', {
							timeOut: 500
						});
						$('#Reversed-table-container').html(data);
					},
					error: function() {
						toastr.error('Failed to load data.');
					}
				});
			});


		<?php } ?>


		<?php if (isset($_GET['invoiced2'])) { ?>

			$(document).ready(function() {
				$.ajax({
					type: 'POST',
					url: 'get_invoiced_data.php',
					data: {
						invoiced: true
					},
					dataType: 'json',
					success: function(data) {


						alert(data);

						/*
						toastr.success('Loading Drugs Request ...', '', {
						timeOut: 500
						});
						var tableHtml = '<table class="table table-striped table-bordered table-hover dataTables-example">';
							tableHtml += '<thead>
								<tr>
									<th>Date</th>
									<th>Hospital</th>
									<th></th>
								</tr>
							</thead>';
							tableHtml += '<tbody>';

								$.each(data, function(index, row) {
								tableHtml += '<tr>';
									tableHtml += '<td>' + row.date + '</td>';
									tableHtml += '<td>' + row.hospital_no + '</td>';
									tableHtml += '<td>' + row.action + '</td>';
									tableHtml += '</tr>';
								});
								tableHtml += '</tbody>
						</table>';
						$('#invoiced-table-container').html(tableHtml); */
					}
				});

			});

		<?php } ?>
	</script>


	<script type="text/javascript">
		$(document).ready(function() {
			$.ajax({
				type: 'GET',
				url: 'ajax_patient_count.php',
				dataType: 'json',
				success: function(data) {
					$('#seen-today-count').text(data.seen_today);
					$('#dsp-today-count').text(data.dsp_today);
					$('#exp-today-count').text(data.exp_today);
					$('#odr-today-count').text(data.odr_today);
					$('#cr-today-count').text(data.cr_today);
					$('#exp_six_months_count').text(data.exp_six_months_count);
					$('#exp_three_months_count').text(data.exp_three_months_count);
				}
			});
		});



		<?php if (isset($_POST['print_presciptn'])) { ?>
			$('#print_prescription').modal('show');
		<?php } ?>

		function print_patient_prescriptn(deposit_reciept) {
			var printWindow = window.open('', 'PRINTOUT', 'height=400,width=600');
			printWindow.document.write('<html><head><title>PRINTOUT</title></head><body>');
			printWindow.document.write(document.getElementsByClassName(deposit_reciept)[0].innerHTML);
			printWindow.document.write('</body></html>');
			printWindow.print();
			printWindow.close();
		}

		function pick_me(nn) {
			var gn;
			gn = 'add_m_' + nn;
			document.getElementById(gn).checked = true;

		}


		$(document).on('click', '.view_notes', function() {
			var note_inv_id = $(this).attr("id");
			var res = note_inv_id.split("__");


			if (note_inv_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						note_inv_id: res[0] + '__' + res[1] + '__' + res[2]
					},
					success: function(data) {

						$('.modal-title').text('Notes: ' + res[1]);

						$('#view_notes_body').html(data);
						$('#view_notes_modal').modal('show');
						pharm_doctor_chat();
					}
				});
			}
		});


		$(document).on('click', '.add_drug', function() {
			var add_drug_id = $(this).attr("id");
			var res = add_drug_id.split("__");

			if (add_drug_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						add_drug_id: res[0] + '__' + res[1] + '__' + res[2] + '__' + res[3]
					},
					success: function(data) {

						$('.modal-title').text('Adding : ' + res[1]);

						$('#add_drug_body').html(data);
						$('#add_drug_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.refill_drug', function() {
			var refill_drug_id = $(this).attr("id");
			if (refill_drug_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						refill_drug_id: refill_drug_id
					},
					success: function(data) {

						$('.modal-title').text('Refill Drug');

						$('#refill_body').html(data);
						$('#refill_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.bio_data_link', function() {
			var bio_data_id = $(this).attr("id");
			///	alert();

			if (bio_data_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						bio_data_id: bio_data_id
					},
					success: function(data) {

						$('.modal-title').text('Patient Bio - Data');

						$('#bio_data_body').html(data);
						$('#bio_data_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.reverse', function() {
			var reverse_id = $(this).attr("id");

			if (reverse_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						reverse_id: reverse_id
					},
					success: function(data) {

						$('.modal-title').text('Reverse Drug');

						$('#reverse_body').html(data);
						$('#reverse_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.past_medication', function() {
			var hosp_no = $(this).attr("id");

			if (hosp_no != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						past_medication: hosp_no
					},
					success: function(data) {

						$('.modal-title').text('Medication Details');

						$('#past_medication_body').html(data);
						$('#past_medication_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.plan', function() {
			var hosp_no = $(this).attr("id");

			if (hosp_no != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						plan: hosp_no
					},
					success: function(data) {

						$('.modal-title').text('Plan Details');

						$('#plan_body').html(data);
						$('#plan_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '#encounters_form_submit_btn', function() {



			var hosp_no = $(this).data("id");
			var c_type = $('#encounters_form_c_type').val();
			var start_date = $('#encounters_form_start_date').val();
			var end_date = $('#encounters_form_end_date').val();



			if (hosp_no != '') {

				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						encounters: hosp_no,
						encounters_form_c_type: c_type,
						encounters_form_start_date: start_date,
						encounters_form_end_date: end_date
					},
					success: function(data) {

						$('.modal-title').text('Encounters Details');

						$('#encounters_body').html(data);
						$('#encounters_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.encounters_form', function() {
			var hosp_no = $(this).attr("id");

			if (hosp_no != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "GET",
					data: {
						encounters_form: 1,
						hos_no: hosp_no
					},
					success: function(data) {

						console.log(data);
						$('.modal-title').text('Encounters Details');

						$('#encounters_body_form').html(data);
						$('#encounters_modal').modal('show');
					}
				});
			}
		});


		$(document).on('click', '.lab_res_modal_form', function() {

			var hosp_no = $(this).attr("id");

			if (hosp_no != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "GET",
					data: {
						lab_res_modal_form: 1,
						hos_no: hosp_no
					},
					success: function(data) {

						$('.modal-title').text('Recent Investigation Results Details');

						$('#encounters_body_form').html(data);
						$('#encounters_modal').modal('show');
					}
				});
			}
		});

		$(document).on('click', '#lab_res_modal_form_submit_btn', function() {

			var hosp_no = $(this).data("id");
			var c_type = $('#lab_res_modal_form_c_type').val();
			var start_date = $('#lab_res_modal_form_start_date').val();
			var end_date = $('#lab_res_modal_form_end_date').val();

			if (hosp_no != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						lab_res_modal: hosp_no,
						lab_res_modal_form_c_type: c_type,
						lab_res_modal_form_start_date: start_date,
						lab_res_modal_form_end_date: end_date
					},
					success: function(data) {

						// $('.modal-title').text('Recent Investigation Results Details');

						$('#encounters_body').html(data);
						// $('#encounters_modal').modal('show');
					}
				});
			}
		});

		$(document).ready(function() {
			$("#inv_item").hide();
			$("#staff_status").hide();
			$("#rhmo_id").hide();

			$('#rpt_type').on('change', function() {


				if (this.value == 'inv_drug') {
					$("#inv_item").show();
					$("#staff_status").hide();
					$("#rhmo_id").hide();
				}

				if (this.value == 'rhmo') {
					$("#inv_item").hide();
					$("#rhmo_id").show();
					$("#staff_status").hide();
				}

				if (this.value != 'rhmo' && this.value != 'inv_drug') {
					$("#inv_item").hide();
					$("#rhmo_id").hide();
					$("#staff_status").show();
				}


			});
		});


		$(document).on('click', '.dsp_oncredit', function() {
			var dsp_oncredit_id = $(this).attr("id");

			if (dsp_oncredit_id != '') {
				$.ajax({
					url: "../pharmacy/fetch_set.php",
					method: "POST",
					data: {
						dsp_oncredit_id: dsp_oncredit_id
					},
					success: function(data) {

						$('.modal-title').text('Dispense On/Credit');

						$('#dsp_oncredit_body').html(data);
						$('#dsp_oncredit_modal').modal('show');
					}
				});
			}
		});





		$(document).ready(function() {
			var input = document.getElementById("search");
			input.addEventListener("keyup", function(event) {
				if (event.keyCode === 13) {
					event.preventDefault();
					document.getElementById("apply_action").click();
				}
			});
		});


		function search_patient() {
			document.getElementById('apply_action').innerHTML = 'Wait ..';
			document.getElementById("apply_action").disabled = true;

			///	var search_detials = document.getElementById("search").value;
			var text = document.getElementById("search").value;
			let search_detials = text.replace(/^\s+|\s+$/gm, '');

			let length = search_detials.length;

			if (length < 4) {
				toastr.error('Search must be more than 4 characters', 'Invalid Data ', {
					timeOut: 5000
				});
				document.getElementById('apply_action').innerHTML = 'Search';
				document.getElementById("apply_action").disabled = false;
				///exit;
				return 0;

			}
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					search_detials: search_detials
				},
				success: function(data) {

					setTimeout(function() {
						$("#overlay").fadeOut();
					}, 500);
					//toastr.info(data, 'Attention', {timeOut: 5000})//
					console.log(data)
					if (data.redirect != undefined) {
						window.location = 'index.php?presc&hos_no=' + data.hosp_no;
					} else {

						document.getElementById('apply_action').innerHTML = 'Search';
						document.getElementById("apply_action").disabled = false;

						if (data.trim() == 'NotFound') {
							toastr.error('Not match found', 'Error', {
								timeOut: 5000
							})
						} else {
							$('.modal-title').text('Search Patient');
							$('#patient_search_body').html(data);
							$('#patient_search_modal').modal('show');
						}
					}
				}
			});


		}


		$(document).on('click', '.document_note', function() {
			var add_review = $(this).attr("id");
			$.ajax({
				url: "../inc/documentation.php",
				method: "POST",
				data: {
					add_documentation: add_review
				},
				success: function(data) {

					$('.modal-title').text("Patient's Documentation");
					$('#document_note_mdl').modal('show');
					$('#document_note_body').html(data);

					patient_review();
				}
			});
		});


		function service_bill() {
			var review_note = document.getElementById('selected_review_note').value;
			var hospital_no = document.getElementById('hospital_no').value;
			var dept_id = document.getElementById('dept_id').value;
			var mode = document.getElementById('mode').value;

			if (review_note != '' && mode == 'new') {

				var rr = confirm("Are you sure you want to Save?");
				if (rr === true) {

					$.ajax({
						url: "../inc/documentation.php",
						method: "POST",
						data: {
							review_note: review_note,
							hospital_no: hospital_no,
							dept_id: dept_id
						},
						success: function(data) {

							var json = JSON.parse(data);
							//alert(json["status"]);
							if (json["status"] == 1) {

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

				///alert(notes_sn);
				///alert(review_note);

				$.ajax({
					url: "../inc/documentation.php",
					method: "POST",
					data: {
						review_note_edit: review_note,
						edit_notes_update: notes_sn
					},
					success: function(data) {
						var json = JSON.parse(data);

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
				toastr.error('Empty Notes', 'Error', {
					timeOut: 5000
				});
			}
		}



		function patient_review() {

			/// $('#loader').show();

			var hospital_no = document.getElementById('hospital_no').value;

			$.ajax({
				url: "../inc/_ward_review_fetch.php",
				method: "POST",
				data: {
					patient_review_doc: hospital_no
				},
				success: function(data) {

					document.getElementById('data_displayed').innerHTML = data;

				}
			});
		}



		function edit_notes(sn) {

			$.ajax({
				url: "../inc/_ward_review_fetch.php",
				method: "POST",
				data: {
					edit_sn: sn
				},
				success: function(data) {

					var json = JSON.parse(data);
					document.getElementById('selected_review_note').innerHTML = '';
					document.getElementById('selected_review_note').value = json["notes"];
					document.getElementById('mode').value = 'edit';
					document.getElementById('notes_sn').value = json["notes_sn"];
					document.getElementById('edit__mode').innerHTML = '<strong>[ Edit Note Below ]</strong>';
					document.getElementById('pay_now').innerHTML = 'Save Edited Notes';
					document.getElementById("pay_now").disabled = false;
					//document.getElementById('selected_review_servie_id').value='';
				}
			});

		}

		function notify_requester() {

			var drug_name_ = document.getElementById('drug_name_').value;
			var reciever = document.getElementById('reciever').value;
			var drug_note = document.getElementById('drug_note').value;
			var patient_hosp_no = document.getElementById('patient_hosp_no').value;
			var patient_name = document.getElementById('patient_name').value;

			if (drug_note != '') {

				$.ajax({
					url: "../inc/_ward_review_fetch.php",
					method: "POST",
					data: {
						notify_requester: patient_hosp_no,
						patient_name: patient_name,
						drug_name_: drug_name_,
						reciever: reciever,
						drug_note: drug_note
					},
					success: function(data) {

						var json = JSON.parse(data);
						//alert(json["status"]);
						if (json["status"] == 0) {

							document.getElementById("notify_requester_btn").disabled = true;
							//document.getElementById("selected_review_note").value ='';
							pharm_doctor_chat();
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
				toastr.error('Empty Notes', 'Error', {
					timeOut: 5000
				});
			}
		}



		function pharm_doctor_chat() {

			var patient_hosp_no = document.getElementById('patient_hosp_no').value;
			var drug_name_ = document.getElementById('drug_name_').value;
			$.ajax({
				url: "../inc/_ward_review_fetch.php",
				method: "POST",
				data: {
					pharm_doctor_chat: patient_hosp_no,
					drug_name_: drug_name_
				},
				success: function(data) {

					document.getElementById('pharm_doctor_chat_displayed').innerHTML = data;

				}
			});
		}


		function delete_notes(sn) {

			var rr = confirm("Are you sure you want to DELETE?");
			if (rr === true) {
				$.ajax({
					url: "../inc/documentation.php",
					method: "POST",
					data: {
						delete_notes: sn
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

		function delete_notes_chat(sn) {

			var rr = confirm("Are you sure you want to DELETE?");
			if (rr === true) {
				$.ajax({
					url: "../inc/documentation.php",
					method: "POST",
					data: {
						delete_notes_chat: sn
					},
					success: function(data) {
						var json = JSON.parse(data);
						//alert(json["status"]);
						if (json["status"] == 0) {
							pharm_doctor_chat();
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



		$(document).ready(function() {
			$('#Patient_discharge_councelling_form').hide();
			$('#MEDICATION_INTERVENTION_FORM').hide();
			$('#MEDICATION_RECONCILE_FORM').hide();

			function watchReferal(pharmacy_form) {
				if (pharmacy_form === 'Patient_discharge_councelling_form') {
					$('#Patient_discharge_councelling_form').show();
					$('#MEDICATION_INTERVENTION_FORM').hide();
					$('#MEDICATION_RECONCILE_FORM').hide();


				} else if (pharmacy_form == 'MEDICATION_INTERVENTION_FORM') {
					$('#MEDICATION_INTERVENTION_FORM').show();
					$('#Patient_discharge_councelling_form').hide();
					$('#MEDICATION_RECONCILE_FORM').hide();
				} else if (pharmacy_form == 'MEDICATION_RECONCILE_FORM') {
					$('#MEDICATION_INTERVENTION_FORM').hide();
					$('#Patient_discharge_councelling_form').hide();
					$('#MEDICATION_RECONCILE_FORM').show();
				} else {
					$('#Patient_discharge_councelling_form').hide();
					$('#MEDICATION_INTERVENTION_FORM').hide();
					$('#MEDICATION_RECONCILE_FORM').hide();
				}
			}

			// Watch for changes in the clinical forms status dropdown
			$('#pharmacy_form').change(function() {
				const selectedStatus = $(this).val();
				watchReferal(selectedStatus);
			});
		});

		/////================================ BEGINING VALIDATION

		$(document).on('click', '.validate_package', function() {


			var validate_package_id = $(this).attr("id");
			if (validate_package_id != '') {
				$.ajax({
					url: "../validate_package.php",
					method: "POST",

					data: {
						validate_package_id: validate_package_id
					},

					success: function(data) {

						$('.modal-title').text('Validate Package');

						$('#validate_package_body').html(data);
						$('#validate_package_modal').modal('show');
					}
				});
			}
		});

		function un_validate_pckage_item(tag) {
			if (confirm("Are you sure you want to UNDO?")) {
				$.ajax({
					url: "../validate_package.php",
					method: "POST",
					data: {
						un_validate_: tag
					},
					success: function(data) {

						document.getElementById("undo_" + tag).disabled = true;
						document.getElementById("undo_" + tag).innerHTML = 'Done';
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error("Error:", textStatus, errorThrown);
					}
				});
			}
		}

		function validate_pckage_item(tag) {
			if (confirm("Are you sure you want to proceed with this action?")) {
				$.ajax({
					url: "../validate_package.php",
					method: "POST",
					data: {
						validate_: tag
					},
					success: function(data) {
						document.getElementById("undo_" + tag).disabled = true;
						document.getElementById("undo_" + tag).innerHTML = 'Done';
					}
				});
			}
		}



		////========================================= VALIDATIONS
	</script>



	<!-- Data Tables -->
	<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
	<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
	<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

	<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
	<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
	<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>
	<link rel="stylesheet" href="../js/select2/css/select2.min.css">
	<script src="../js/select2/js/select2.min.js"></script>

	<!-- Custom CSS to make Select2 taller and show more options -->
	<style>
		.expiring-item {
			color: red;
			font-weight: bold;
		}

		.select2-container .select2-selection--single {
			height: 45px !important;
			display: flex;
			align-items: center;
		}

		.select2-results__options {
			max-height: 400px !important;
			width: 100% !important;

			/* Increase dropdown height */
		}
	</style>

	<!-- Initialize Select2 for  -->
	<script>
		$(document).ready(function() {
			const medicationsData = <?php echo json_encode($items); ?>;

			$('#medications').select2({
				placeholder: "Search and Select items",
				allowClear: true,
				data: medicationsData,
				templateResult: function(item) {
					if (!item.id) return item.text; // Return placeholder
					// Add red highlight if the item is about to expire
					if (item.is_expiring) {
						return $('<span class="expiring-item">' + item.text + '</span>');
					}
					return item.text;
				},
				templateSelection: function(item) {
					return item.text;
				}
			});
		});
	</script>

	<script>
		$(document).ready(function() {
			$('.trumbowygEditor').trumbowyg({
				btns: [
					['viewHTML'],
					['undo', 'redo'], // Only supported in Blink browsers
					['formatting'],
					['strong', 'em', 'del'],
					['superscript', 'subscript'],
					['fontsize'],
					['foreColor', 'backColor'],
					['link'],
					['insertImage'],
					['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
					['unorderedList', 'orderedList'],
					['horizontalRule'],
					['removeformat'],
					['fullscreen']
				],
				plugins: {
					fontsize: {
						sizeList: [
							'12px',
							'14px',
							'16px',
							'18px',
							'20px',
							'24px',
							'32px',
							'48px',
						]
					}
				}
			});


		});



		$(document).ready(function() {
			if (window.location.hash) {
				var target = $(window.location.hash);
				if (target.length) {
					$('html, body').animate({
						scrollTop: target.offset().top
					}, 600); // 600ms smooth scroll
				}
			}
		});

		$(document).ready(function() {
			if (window.location.hash === '#drug-section') {
				const target = $('#drug-section');
				if (target.length) {
					$('html, body').animate({
						scrollTop: target.offset().top
					}, 600);
				}
			}
		});

		$('.dataTables-example').dataTable({
			responsive: true,
			"pageLength": 50,
			"lengthMenu": [
				[50, 100, 200, 500, -1],
				[50, 100, 200, 500, "All"]
			],
			"dom": 'T<"clear">lfrtip',
			"tableTools": {
				"sSwfPath": "../js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
			}
		});



		$('#data_5 .input-daterange').datepicker({
			keyboardNavigation: false,
			forceParse: false,
			autoclose: true
		});
	</script>

	<script>
		function Clickheretoprint() {
			var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
			disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
			var content_vlue = document.getElementById("content").innerHTML;

			var docprint = window.open("", "", disp_setting);
			docprint.document.open();
			docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
			docprint.document.write(content_vlue);
			docprint.document.close();
			docprint.focus();
		}
	</script>

	<script src="../js/idle.js"></script>


	<script>
		var previousPharmacyCount = localStorage.getItem('pharmacy_count') ?
			parseInt(localStorage.getItem('pharmacy_count')) : 0;

		function checkPharmacy() {

			$.ajax({
				url: 'fetch_pharmacy_count.php',
				method: 'GET',
				dataType: 'json',
				success: function(data) {

					///alert(data.total);

					let count = parseInt(data.total) || 0;

					if (count > 0) {

						$('#pharmacyAlertBox').fadeIn();
						$('#pharmacy_count').text(count);

						if (count > previousPharmacyCount) {

							// 🔊 Sound
							let sound = new Audio('../sounds/notification.wav');
							sound.play().catch(() => {});

							// 🔵 Flash
							$('#pharmacyAlertBox')
								.css('background', '#cce5ff')
								.fadeOut(200).fadeIn(200)
								.fadeOut(200).fadeIn(200);

							setTimeout(() => {
								$('#pharmacyAlertBox').css('background', '#e2f0ff');
							}, 3000);
						}

					} else {
						$('#pharmacyAlertBox').fadeOut();
					}

					previousPharmacyCount = count;
					localStorage.setItem('pharmacy_count', count);
				}
			});
		}


		// CLICK → LOAD MODAL
		$('#pharmacyAlertBox').on('click', function() {

			$('#pharmacyModal').modal('show');

			$.ajax({
				url: 'fetch_pharmacy_list.php',
				success: function(data) {
					$('#pharmacy_body').html(data);
				}
			});

		});


		// RUN
		setInterval(checkPharmacy, 60000);
		checkPharmacy();
	</script>
</body>

</html>