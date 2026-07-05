<?php


if (isset($_POST['adm_hospital'])) {
	session_start();
	require_once('../Connections/Conn.php');

	$hospital_no = $_POST['adm_hospital'];
	$app_no = $_POST['app_no'];
	$date_entry = $_POST['date_entry'] . ':' . $_POST['time_entry'];
	$nursing_diag = $_POST['nursing_diag'];
	$nursing_orders_objective = $_POST['nursing_orders_objective'];
	$nursing_intervention = $_POST['nursing_intervention'];
	$scientific_rationale = $_POST['scientific_rationale'];
	$evaluation = $_POST['evaluation'];
	$prepared_by = $_SESSION['fullname']; ////['nurses_name'];

	// var_dump($_POST['time_entry']);
	// die();

	$sql = $db->prepare("INSERT INTO nursing_care_plan_chart (app_no, hospital_no, date_entry,nursing_diag, nursing_orders_objective,nursing_intervention, scientific_rationale, evaluation, prepared_by) 
	VALUES (:app_no, :hospital_no, :date_entry,:nursing_diag, :nursing_orders_objective,:nursing_intervention,:scientific_rationale, :evaluation, :prepared_by)");
	$sql->bindParam(':app_no', $app_no, PDO::PARAM_STR);
	$sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
	$sql->bindParam(':date_entry', $date_entry, PDO::PARAM_STR);
	$sql->bindParam(':nursing_diag', $nursing_diag, PDO::PARAM_STR);
	$sql->bindParam(':nursing_orders_objective', $nursing_orders_objective, PDO::PARAM_STR);
	$sql->bindParam(':nursing_intervention', $nursing_intervention, PDO::PARAM_STR);
	$sql->bindParam(':scientific_rationale', $scientific_rationale, PDO::PARAM_STR);
	$sql->bindParam(':evaluation', $evaluation, PDO::PARAM_STR);
	$sql->bindParam(':prepared_by', $prepared_by, PDO::PARAM_STR);
	$sql->execute();
	if ($sql) {
		// $error_status = 2;
		echo 'Data Added Successfully!';
	} else {
		echo 'Saving Not Successfully!';
	}

	exit;
}



?>


<div class="row">
	<div class="col-lg-8">
		<div class="ibox ">
			<div class="ibox-content">


				<?php

				session_start();
				require_once('../Connections/Conn.php');
				$hospital_no = $_POST['hospital_no'];

				$appointment_number = $_POST['appointment_number'];

				$stmt = $db->prepare("SELECT app_no FROM admission WHERE hospital_no=:hospital_no and adm_status='3' order by sn desc limit 1");
				$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$app_no = $row['app_no'];
				}
				if ($app_no == '') {
					$app_no = $appointment_number;
				}

				$stmtt = $db->prepare("SELECT * 
					FROM nursing_care_plan_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 30");
				$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
				$stmtt->execute();
				if ($stmtt->rowCount() > 0) { ?>

					<h4>Nursing Care Plan</h4>
					<div class="panel-heading">
					</div>

					<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
						<thead>
							<tr>
								<th data-toggle="true">Date</th>
								<th data-toggle="true">Nursing Diagnosis</th>
								<th data-toggle="true">Nursing Orders & Objectives</th>
								<th data-toggle="true">Nursing Intervention</th>
								<th data-toggle="true">Scientific Rationale</th>
								<th data-toggle="true">Evaluation</th>
								<th data-toggle="true">Nurses Name</th>
								<th data-toggle="true"></th>
							</tr>
						</thead>
						<tbody>
							<?php while ($row = $stmtt->fetch(PDO::FETCH_ASSOC)) { ?>
								<tr>
									<td><?php echo date('d M, Y H:i:s', strtotime($row['date_entry'])); ?></td>
									<td><?php echo $row['nursing_diag']; ?></td>
									<td><?php echo $row['nursing_orders_objective']; ?></td>
									<td><?php echo $row['nursing_intervention']; ?></td>
									<td><?php echo $row['scientific_rationale']; ?></td>
									<td><?php echo $row['evaluation']; ?></td>
									<td><?php echo $row['prepared_by']; ?></td>
									<td>
										<?php if ($_SESSION['fullname'] == $row['prepared_by']) { ?>
											<a href="patient.php?hosp_no=<?= $hospital_no; ?>&nursing_care_plan=<?php echo $row['sn']; ?>&del=1" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
										<?php } ?>

									</td>

									<?php
									//  }
									?>
								</tr>
							<?php 	} ?>

						</tbody>
						<tfoot class="hide-if-no-paging">
							<tr>
								<td colspan="6" class="text-center">
									<ul class="pagination pagination-sm"></ul>
								</td>
							</tr>
						</tfoot>
					</table>
				<?php } ?>
				<hr>

				<?php
				$stmtt = $db->prepare("SELECT * FROM nursing_care_plan_chart WHERE hospital_no=:hospital_no and app_no!=:app_no order by sn desc limit 30");
				$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
				$stmtt->execute();
				if ($stmtt->rowCount() > 0) { ?>

					<h4 style="color: darkred; "><u>Previous</u> Nursing Care Plan</h4>
					<div class="panel-heading">
					</div>

					<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
						<thead>
							<tr>
								<th data-hide="phone,tablet">Appt. No.</th>
								<th data-toggle="true">Date</th>
								<th data-toggle="true">Nursing Diagnosis</th>
								<th data-toggle="true">Nursing Orders & Objectives</th>
								<th data-toggle="true">Nursing Intervention</th>
								<th data-toggle="true">Scientific Rationale</th>
								<th data-toggle="true">Evaluation</th>
								<th data-toggle="true">Nurses Name</th>
							</tr>
						</thead>
						<tbody>
							<?php while ($row = $stmtt->fetch(PDO::FETCH_ASSOC)) { ?>
								<tr>
									<td><?php echo $row['app_no']; ?></td>
									<td><?php echo date('d M, Y H:i:s', strtotime($row['date_entry'])); ?></td>
									<td><?php echo $row['nursing_diag']; ?></td>
									<td><?php echo $row['nursing_orders_objective']; ?></td>
									<td><?php echo $row['nursing_intervention']; ?></td>
									<td><?php echo $row['scientific_rationale']; ?></td>
									<td><?php echo $row['evaluation']; ?></td>
									<td><?php echo $row['prepared_by']; ?></td>
								</tr>
							<?php 	} ?>

						</tbody>
						<tfoot class="hide-if-no-paging">
							<tr>
								<td colspan="6" class="text-center">
									<ul class="pagination pagination-sm"></ul>
								</td>
							</tr>
						</tfoot>
					</table>
				<?php } ?>


			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="ibox ">
			<div class="ibox-content">
				<h2 align="center">Nursing Care Plan</h2>
				<hr>
				<div class="form_sep">
					<label for="date_entry" class="req">Date:</label>
					<div class="input-group date">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input type="date-time-local" name="date_entry" id="date_entry" value="<?= date('Y-m-d'); ?>" class="form-control" required>
					</div>
				</div>

				<div class="form_sep">
					<label for="time_entry" class="req">Time:</label>
					<div class="input-group time">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input type="time" id="time_entry" value="<?= date('H:i'); ?>" name="time_entry" class="form-control" required>
					</div>
				</div>

				<div class="form_sep">
					<label for="nursing_diag" class="req">Nursing Diagnosis:</label>
					<textarea name="nursing_diag" id="nursing_diag" cols="30" rows="2" class="form-control" required></textarea>

				</div>

				<div class="form_sep">
					<label for="nursing_orders_objective" class="req">Nursing Orders & Objective:</label>
					<textarea name="nursing_orders_objective" id="nursing_orders_objective" cols="30" rows="2" class="form-control" required></textarea>
				</div>

				<div class="form_sep">
					<label for="nursing_intervention" class="req">Nursing Intervention:</label>
					<textarea name="nursing_intervention" id="nursing_intervention" cols="30" rows="2" class="form-control" required></textarea>

				</div>

				<div class="form_sep">
					<label for="scientific_rationale" class="">Scientific Rationale:</label>
					<textarea name="scientific_rationale" id="scientific_rationale" cols="30" rows="2" class="form-control"></textarea>
				</div>

				<div class="form_sep">
					<label for="evaluation" class="">Evaluation:</label>
					<textarea name="evaluation" id="evaluation" cols="30" rows="2" class="form-control"></textarea>
				</div>

				<div class="form_sep">
					<input type="hidden" id="app_no" value="<?php echo $app_no; ?>">
					<input type="hidden" id="hospital_no" value="<?php echo $hospital_no; ?>">

					<button class="btn btn-primary" id="save-mgt-button_nursing_plan">Save</button>

				</div>


			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function() {

		$(document).on('click', '#save-mgt-button_nursing_plan', function() {

			var app_no = $("#app_no").val();
			var hospital_no = $("#hospital_no").val();
			var date_entry = $("#date_entry").val();
			var time_entry = $("#time_entry").val();
			var nursing_diag = $("#nursing_diag").val();
			var nursing_orders_objective = $("#nursing_orders_objective").val();
			var nursing_intervention = $("#nursing_intervention").val();
			var scientific_rationale = $("#scientific_rationale").val();
			var evaluation = $("#evaluation").val();
			//	var nurses_name = $("#nurses_name").val();	

			if (nursing_orders_objective != '' && date_entry != '' && nursing_intervention != '' && nursing_diag != '') {

				$.ajax({
					type: "POST",
					url: "../inc/nursing_care_plan_chart.php",
					data: {
						app_no: app_no,
						adm_hospital: hospital_no,
						date_entry: date_entry,
						time_entry: time_entry,
						nursing_diag: nursing_diag,
						nursing_orders_objective: nursing_orders_objective,
						nursing_intervention: nursing_intervention,
						scientific_rationale: scientific_rationale,
						evaluation: evaluation
					},
					cache: false,
					success: function(result) {
						toastr.success(result, 'Attention', {
							timeOut: 5000
						});
						window.location.href = "patient.php?hosp_no=" + hospital_no + '&nursing_care_plan';
					}
				});
			} else {
				toastr.error('Invalid Data Entries', 'Error', {
					timeOut: 5000
				});

			}

		});

	});
</script>