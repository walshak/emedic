<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

include('inc/functions.php');

$years = $db->query('SELECT * FROM chart_fiscal_year');

$years = $years->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['edit_year'])) {
	if ($_POST['start'] != '' && $_POST['end'] != '') {
		try {
			$db->beginTransaction();
			$start = $_POST['start'];
			$end = $_POST['end'];
			$active = $_POST['active'];
			$id = $_POST['year_id'];

			if ($active == 1) {
				$close_all = $db->prepare("UPDATE chart_fiscal_year SET closed = 1");
				$close_all->execute();

				$update = $db->prepare("UPDATE chart_fiscal_year SET begin = ?, end = ?, closed = ? WHERE id = ?");
				$update = $update->execute([$start, $end, 0, $id]);
			} else {
				$update = $db->prepare("UPDATE chart_fiscal_year SET begin = ?, end = ? WHERE id = ?");
				$update = $update->execute([$start, $end, $id]);
			}

			$db->commit();
			set_flash_message('Fiscal Year updated successfully', 'success');
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Failed to update fiscal Year', 'danger');
			// header('Location:?');
		}
	}
}

if (isset($_POST['add_year'])) {
	if ($_POST['start'] != '' && $_POST['end'] != '') {
		try {
			$db->beginTransaction();
			$start = $_POST['start'];
			$end = $_POST['end'];
			$active = $_POST['active'];

			if ($active == 1) {
				$close_all = $db->prepare("UPDATE chart_fiscal_year SET closed = 1");
				$close_all->execute();

				$insert = $db->prepare("INSERT INTO chart_fiscal_year(begin, end, closed) VALUES(?,?,?)");
				$insert = $insert->execute([$start, $end, 0]);
			} else {
				$insert = $db->prepare("INSERT INTO chart_fiscal_year(begin, end, closed) VALUES(?,?,?)");
				$insert = $insert->execute([$start, $end, 1]);
			}

			$db->commit();
			set_flash_message('Fiscal Year updated successfully', 'success');
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Failed to update fiscal Year', 'danger');
			// header('Location:?');
		}
	}
}
?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php include("nav_side.php"); ?>




		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../inc/nav_header.php'; ?>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">

								<h5>Accounts Dashboard - Fiscal Years</h5>
							</div>
							<div class="ibox-content">

								<!-- <a href="?add" class="btn btn-warning"><i class="fa fa-plus"></i>&nbsp; ADD FICAL YEAR</a>&nbsp; : &nbsp; -->
								<a href="fiscal_years.php" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;REFRESH</a>&nbsp; : &nbsp;
								<a href="index.php" class="btn btn-danger" style="color: black; "><i class="fa fa-close"></i>&nbsp;CLOSE</a>
								<hr>
								<?php
								echo show_flash_msg();
								?>
								<?php if (isset($_GET['id'])) : ?>
									<?php
									$year_id = $_GET['id'];
									$year = $db->prepare('SELECT * FROM chart_fiscal_year WHERE id = ?');
									$year->execute([$year_id]);
									$year = $year->fetch();

									$last_year = $db->prepare('SELECT * FROM chart_fiscal_year WHERE id < ? ORDER BY id DESC LIMIT 1');
									$last_year->execute([$year_id]);
									$last_year = $last_year->fetch();
									?>


									<h3>Editing year '<?= $year['begin'] ?> - <?= $year['end'] ?>'</h3>
									<form action="" method="post">
										<div class="form-group">
											<label for="start">Start</label>
											<input type="hidden" name="year_id" value="<?= $year_id ?>" required>
											<input type="date" name="start" class="form-control" id="start" value="<?= $year['begin'] ?>" <?= (($year['closed'] == 1) ? 'readonly' : '') ?> min="<?= date('Y-m-d', strtotime($last_year['end'] . ' +1 day')) ?>" required>
											<br>
											<label for="end">End</label>
											<input type="date" name="end" class="form-control" id="end" value="<?= $year['end'] ?>" <?= (($year['closed'] == 1) ? 'readonly' : '') ?> required>
											<br>
											<label for="active">Mark as Active</label>
											<input type="checkbox" name="active" id="actve" value="1">
										</div>
										<button type="submit" name="edit_year" class="btn btn-success" onclick="return confirm('Are you sure you wish to update this fiscal Year?')">Update Year</button>
									</form>

								<?php endif ?>

								<?php if (isset($_GET['add'])) : ?>
									<?php
									$last_year = $db->prepare('SELECT * FROM chart_fiscal_year WHERE closed = 1 ORDER BY id DESC LIMIT 1');
									$last_year->execute();
									$last_year = $last_year->fetch();
									?>
									<hr>
									<h3>Create year</h3>
									<form action="" method="post">
										<div class="form-group">
											<label for="start">Start</label>
											<input type="date" name="start" class="form-control" id="start" value="<?= date('Y-m-d', strtotime($last_year['end'] . ' +1 day')) ?>" min="<?= date('Y-m-d', strtotime($last_year['end'] . ' +1 day')) ?>" required>
											<br>
											<label for="end">End</label>
											<input type="date" name="end" value="<?= date('Y-m-d', strtotime($last_year['end'] . ' +365 days')) ?>" min="<?= date('Y-m-d', strtotime($last_year['end'] . ' +1 day')) ?>" class="form-control" id="end" required>
											<br>
											<label for="active">Mark as Active</label>
											<input type="checkbox" name="active" id="actve" value="1">
										</div>
										<button type="submit" name="add_year" class="btn btn-success" onclick="return confirm('Are you sure you wish to create this fiscal Year?')">Create Year</button>
									</form>
									<hr>
								<?php endif ?>

								<div class="table-responsive" id="chart_groups">
									<table class="table">
										<thead>
											<th>S/N</th>
											<th>Start</th>
											<th>End</th>
											<th>Status</th>
											<th>Action</th>
										</thead>
										<tbody>
											<?php foreach ($years as $year) { ?>
												<tr>
													<td>#</td>
													<td><?php echo $year['begin']; ?></td>
													<td><?php echo $year['end']; ?></td>
													<td><?php echo (($year['closed'] == 0) ? "<span class='badge badge-success'>Open</span>" : "<span class='badge badge-secondary'>Closed</span>"); ?></td>
													<td>
														<?php if ($year['closed'] == 0): ?>
															<button class="btn btn-warning" onclick="showClosingModal(<?php echo $year['id']; ?>, '<?php echo $year['end']; ?>')">
																<i class="fa fa-lock"></i> Close Year
															</button>
														<?php endif; ?>
													</td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>

								<button class="btn btn-primary" onclick="printDiv('chart_groups')"><i class="fa fa-print"></i>&nbsp; Print Report</button>
								<br>
							</div>
						</div>
					</div>
				</div>
				<?php include '../inc/footer.php'; ?>

			</div>
		</div>

		<!-- Account closing modal-->
		<div class="modal fade" id="yearClosingModal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
						<h4 class="modal-title">Close Fiscal Year</h4>
					</div>
					<div class="modal-body">
						<form id="closingForm">
							<input type="hidden" id="current_year_id" name="current_year_id">
							<div class="form-group">
								<label for="new_year_start">New Fiscal Year Start Date</label>
								<input type="date" class="form-control" id="new_year_start" name="new_year_start" required>
							</div>
							<div class="form-group">
								<label for="new_year_end">New Fiscal Year End Date</label>
								<input type="date" class="form-control" id="new_year_end" name="new_year_end" required>
							</div>
						</form>
						<div id="closingStatus" class="alert" style="display: none;"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-warning" id="confirmClosing">
							<i class="fa fa-lock"></i> Close Fiscal Year
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php include '../inc/footer_scripts.php'; ?>


		<script>
			<?php
			if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
				timeOut: 5000
			})
			<?php } ?>
		</script>

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
		<script>
			function printDiv(divId) {
				var content = document.getElementById(divId).innerHTML;
				var popupWindow = window.open('', '_blank', 'width=600,height=600');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>' + document.title + '</title>');
				// Reference the external stylesheet from the main page
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				popupWindow.print();
			}
		</script>
		<script>
			function showClosingModal(yearId, currentYearEnd) {
				// Calculate default dates for new fiscal year
				const endDate = new Date(currentYearEnd);
				const startDate = new Date(endDate);
				startDate.setDate(startDate.getDate() + 1);

				const defaultEndDate = new Date(startDate);
				defaultEndDate.setFullYear(defaultEndDate.getFullYear() + 1);
				defaultEndDate.setDate(defaultEndDate.getDate() - 1);

				// Set the values in the modal
				$('#current_year_id').val(yearId);
				$('#new_year_start').val(startDate.toISOString().split('T')[0]);
				$('#new_year_end').val(defaultEndDate.toISOString().split('T')[0]);

				// Show the modal
				$('#yearClosingModal').modal('show');
			}

			$(document).ready(function() {
				$('#confirmClosing').click(function() {
					const btn = $(this);
					const form = $('#closingForm');

					// Basic validation
					if (!form[0].checkValidity()) {
						form[0].reportValidity();
						return;
					}

					// Disable button and show loading state
					btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

					// Hide any previous status messages
					$('#closingStatus').hide();

					// Collect form data
					const formData = {
						current_year_id: $('#current_year_id').val(),
						new_year_start: $('#new_year_start').val(),
						new_year_end: $('#new_year_end').val()
					};

					// Send AJAX request
					$.ajax({
						url: 'close_fiscal_year.php',
						type: 'POST',
						data: formData,
						dataType: 'json',
						success: function(response) {
							if (response.success) {
								$('#closingStatus')
									.removeClass('alert-danger')
									.addClass('alert-success')
									.html('<i class="fa fa-check"></i> ' + response.message)
									.show();

								// Reload the page after 2 seconds
								setTimeout(function() {
									window.location.reload();
								}, 2000);
							} else {
								$('#closingStatus')
									.removeClass('alert-success')
									.addClass('alert-danger')
									.html('<i class="fa fa-times"></i> ' + response.message)
									.show();

								// Re-enable the button
								btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Close Fiscal Year');
							}
						},
						error: function() {
							$('#closingStatus')
								.removeClass('alert-success')
								.addClass('alert-danger')
								.html('<i class="fa fa-times"></i> An error occurred while processing your request')
								.show();

							// Re-enable the button
							btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Close Fiscal Year');
						}
					});
				});
			});
		</script>
</body>

</html>