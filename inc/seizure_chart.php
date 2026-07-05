<?php
session_start();
require_once('../Connections/Conn.php');


// ✅ DELETE OPERATION (AJAX)
if (isset($_POST['delete_sn'])) {
	$sn = $_POST['delete_sn'];
	$fullname = $_SESSION['fullname'];

	$stmt = $db->prepare("SELECT date_entry, prepared_by FROM seizure_chart WHERE sn=:sn");
	$stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$row) {
		echo 'Record not found!';
		exit;
	}

	$entry_time = strtotime($row['date_entry']);
	$time_diff = time() - $entry_time;

	if ($row['prepared_by'] != $fullname) {
		echo 'You can only delete records you created!';
		exit;
	}

	if ($time_diff > 86400) { // 24 hours = 86400 seconds
		echo 'You can only delete records within 24 hours of creation!';
		exit;
	}

	$stmt = $db->prepare("DELETE FROM seizure_chart WHERE sn=:sn AND prepared_by=:fullname");
	$stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
	$stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
	$stmt->execute();

	echo ($stmt->rowCount() > 0) ? 'Deleted Successfully!' : 'Delete failed!';
	exit;
}


// ✅ UPDATE OPERATION (AJAX)
if (isset($_POST['edit_sn'])) {
	$sn = $_POST['edit_sn'];
	$duration = $_POST['duration'];
	$intervention = $_POST['intervention'];
	$fullname = $_SESSION['fullname'];

	// Check time limit and ownership
	$stmt = $db->prepare("SELECT date_entry, prepared_by FROM seizure_chart WHERE sn=:sn");
	$stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$row) {
		echo 'Record not found!';
		exit;
	}

	$entry_time = strtotime($row['date_entry']);
	$time_diff = time() - $entry_time;

	if ($row['prepared_by'] != $fullname) {
		echo 'You can only edit records you created!';
		exit;
	}

	if ($time_diff > 86400) { // more than 24 hrs
		echo 'You can only edit records within 24 hours of creation!';
		exit;
	}

	$stmt = $db->prepare("UPDATE seizure_chart 
        SET duration=:duration, intervention=:intervention 
        WHERE sn=:sn AND prepared_by=:fullname");
	$stmt->bindParam(':duration', $duration, PDO::PARAM_STR);
	$stmt->bindParam(':intervention', $intervention, PDO::PARAM_STR);
	$stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
	$stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
	$stmt->execute();

	echo ($stmt->rowCount() > 0) ? 'Updated Successfully!' : 'No changes made!';
	exit;
}


// ✅ INSERT OPERATION (AJAX, Duplicate-Proof)
if (isset($_POST['adm_hospital'])) {
	$hospital_no = $_POST['adm_hospital'];
	$app_no = $_POST['app_no'];
	$date_entry = $_POST['date_entry'] . " " . date('h:i:s a');
	$duration = trim($_POST['duration']);
	$intervention = trim($_POST['intervention']);
	$prepared_by = $_SESSION['fullname'];

	// 🔍 Prevent accidental duplicate (same user + same content within 1 min)
	$check = $db->prepare("
        SELECT sn FROM seizure_chart 
        WHERE hospital_no = :hospital_no 
        AND app_no = :app_no
        AND prepared_by = :prepared_by
        AND duration = :duration
        AND intervention = :intervention
        AND TIMESTAMPDIFF(MINUTE, date_entry, NOW()) < 1
    ");
	$check->bindParam(':hospital_no', $hospital_no);
	$check->bindParam(':app_no', $app_no);
	$check->bindParam(':prepared_by', $prepared_by);
	$check->bindParam(':duration', $duration);
	$check->bindParam(':intervention', $intervention);
	$check->execute();

	if ($check->rowCount() > 0) {
		echo 'Duplicate entry detected (recent submission)!';
		exit;
	}

	$sql = $db->prepare("INSERT INTO seizure_chart (app_no, hospital_no, date_entry, duration, intervention, prepared_by) 
        VALUES (:app_no, :hospital_no, :date_entry, :duration, :intervention, :prepared_by)");
	$sql->bindParam(':app_no', $app_no);
	$sql->bindParam(':hospital_no', $hospital_no);
	$sql->bindParam(':date_entry', $date_entry);
	$sql->bindParam(':duration', $duration);
	$sql->bindParam(':intervention', $intervention);
	$sql->bindParam(':prepared_by', $prepared_by);
	$sql->execute();

	echo ($sql) ? 'Data Added Successfully!' : 'Saving Failed!';
	exit;
}
?>


<div class="row">
	<div class="col-lg-8">
		<div class="ibox">
			<div class="ibox-content">

				<?php
				require_once('../Connections/Conn.php');
				$hospital_no = $_POST['hospital_no'];
				$appointment_number = $_POST['appointment_number'];

				$stmt = $db->prepare("SELECT app_no FROM admission WHERE hospital_no=:hospital_no and adm_status='3' order by sn desc limit 1");
				$stmt->bindParam(':hospital_no', $hospital_no);
				$stmt->execute();
				$app_no = ($stmt->rowCount() > 0) ? $stmt->fetch(PDO::FETCH_ASSOC)['app_no'] : $appointment_number;

				$stmtt = $db->prepare("SELECT * FROM seizure_chart WHERE hospital_no=:hospital_no AND app_no=:app_no ORDER BY sn DESC LIMIT 30");
				$stmtt->bindParam(':hospital_no', $hospital_no);
				$stmtt->bindParam(':app_no', $app_no);
				$stmtt->execute();

				if ($stmtt->rowCount() > 0) { ?>
					<h4>SEIZURE CHART</h4>
					<table id="resp_table" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Duration</th>
								<th>Intervention</th>
								<th>Nurse</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="seizure_table_body">
							<?php while ($row = $stmtt->fetch(PDO::FETCH_ASSOC)) {
								$can_edit = ($_SESSION['fullname'] == $row['prepared_by'] && (time() - strtotime($row['date_entry'])) <= 86400);
							?>
								<tr data-sn="<?php echo $row['sn']; ?>">
									<td><?php echo date('d M, Y h:i:s a', strtotime($row['date_entry'])); ?></td>
									<td class="duration"><?php echo htmlspecialchars($row['duration']); ?></td>
									<td class="intervention"><?php echo htmlspecialchars($row['intervention']); ?></td>
									<td><?php echo htmlspecialchars($row['prepared_by']); ?></td>
									<td>
										<?php if ($can_edit) { ?>
											<a href="#" class="edit-row text-info" data-sn="<?php echo $row['sn']; ?>">Edit</a> |
											<a href="#" class="delete-row text-danger" data-sn="<?php echo $row['sn']; ?>">Delete</a>
										<?php } else { ?>
											<span class="text-muted">Locked</span>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } ?>

			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="ibox">
			<div class="ibox-content">
				<h2 align="center">SEIZURE CHART</h2>
				<hr>

				<form id="patient_admission_note">
					<div class="form_sep">
						<label>Date:</label>
						<input type="date" id="date_entry_seizure" class="form-control" required>
					</div>

					<div class="form_sep">
						<label>Duration:</label>
						<textarea id="duration" rows="2" class="form-control" required></textarea>
					</div>

					<div class="form_sep">
						<label>Intervention:</label>
						<textarea id="intervention" rows="2" class="form-control" required></textarea>
					</div>

					<input type="hidden" id="app_no" value="<?php echo $app_no; ?>">
					<input type="hidden" id="hospital_no" value="<?php echo $hospital_no; ?>">

					<button type="submit" class="btn btn-primary" id="save_seizure">Save</button>
				</form>
			</div>
		</div>
	</div>
</div>



<script>
	$(document).ready(function() {

		// ✅ ADD ENTRY (fixed)
		$(document).on('submit', '#patient_admission_note', function(e) {
			e.preventDefault();
			var app_no = $("#app_no").val();
			var hospital_no = $("#hospital_no").val();
			var date_entry = $("#date_entry_seizure").val();
			var duration = $("#duration").val();
			var intervention = $("#intervention").val();

			if (duration && date_entry && intervention) {
				$.ajax({
					type: "POST",
					url: "../inc/seizure_chart.php",
					data: {
						app_no,
						adm_hospital: hospital_no,
						date_entry,
						duration,
						intervention
					},
					beforeSend: function() {
						$("#save_seizure").prop("disabled", true).text("Saving...");
					},
					success: function(result) {
						toastr.success(result);
						$("#save_seizure").prop("disabled", false).text("Save");
						$("#patient_admission_note")[0].reset();
						loadSeizureList(hospital_no, app_no);
					},
					error: function() {
						toastr.error('Error saving data');
						$("#save_seizure").prop("disabled", false).text("Save");
					}
				});
			} else toastr.error('Please fill all fields');
		});


		// ✅ DELETE ENTRY
		$(document).on('click', '.delete-row', function(e) {
			e.preventDefault();
			var sn = $(this).data('sn');
			var row = $(this).closest('tr');
			if (confirm('Are you sure you want to delete this record?')) {
				$.ajax({
					type: "POST",
					url: "../inc/seizure_chart.php",
					data: {
						delete_sn: sn
					},
					success: function(response) {
						if (response.toLowerCase().includes('success')) {
							toastr.success(response);
							row.fadeOut(300, function() {
								$(this).remove();
							});
						} else toastr.warning(response);
					},
					error: function() {
						toastr.error('Delete failed');
					}
				});
			}
		});


		// ✅ OPEN EDIT MODAL
		$(document).on('click', '.edit-row', function(e) {
			e.preventDefault();
			var sn = $(this).data('sn');
			var row = $(this).closest('tr');
			var duration = row.find('.duration').text();
			var intervention = row.find('.intervention').text();

			$('#edit_sn').val(sn);
			$('#edit_duration').val(duration);
			$('#edit_intervention').val(intervention);
			$('#editModal').modal('show');
		});


		// ✅ SAVE EDIT
		$(document).on('click', '#save_edit', function() {
			var sn = $('#edit_sn').val();
			var duration = $('#edit_duration').val();
			var intervention = $('#edit_intervention').val();
			if (duration && intervention) {
				$.ajax({
					type: "POST",
					url: "../inc/seizure_chart.php",
					data: {
						edit_sn: sn,
						duration,
						intervention
					},
					success: function(response) {
						if (response.toLowerCase().includes('success')) {
							toastr.success(response);
							$('tr[data-sn="' + sn + '"] .duration').text(duration);
							$('tr[data-sn="' + sn + '"] .intervention').text(intervention);
							$('#editModal').modal('hide');
						} else toastr.warning(response);
					},
					error: function() {
						toastr.error('Update failed');
					}
				});
			} else toastr.error('All fields required');
		});


		// ✅ OPTIONAL: Reload list dynamically
		function loadSeizureList(hospital_no, app_no) {
			$.post("../inc/seizure_chart.php", {
				hospital_no,
				appointment_number: app_no
			}, function(data) {
				$(".col-lg-8 .ibox-content").html($(data).find(".col-lg-8 .ibox-content").html());
			});
		}

	});
</script>