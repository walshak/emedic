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

								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addYearModal"><i class="fa fa-plus"></i>&nbsp; ADD FISCAL YEAR</button>
								<a href="fiscal_years.php" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;REFRESH</a>
								<a href="index.php" class="btn btn-danger" style="color: black; "><i class="fa fa-close"></i>&nbsp;CLOSE</a>
								<hr>
								<?php
								echo show_flash_msg();
								
								$active_year_stmt = $db->query("SELECT * FROM chart_fiscal_year WHERE closed = 0 LIMIT 1");
								$active_year = $active_year_stmt->fetch(PDO::FETCH_ASSOC);
								?>
								
								<?php if ($active_year): ?>
								<div class="alert alert-info">
								    <h4><i class="fa fa-info-circle"></i> Active Fiscal Year: <strong><?= date('M d, Y', strtotime($active_year['begin'])) ?> to <?= date('M d, Y', strtotime($active_year['end'])) ?></strong></h4>
								    <p>All new journal entries will be strictly locked to this period. Closing this year will calculate retained earnings, carry forward balances, and lock the period from further entries.</p>
								</div>
								<?php else: ?>
								<div class="alert alert-warning">
								    <h4><i class="fa fa-warning"></i> No Active Fiscal Year</h4>
								    <p>Please create and activate a fiscal year. Journal entries cannot be posted without an active accounting period.</p>
								</div>
								<?php endif; ?>
								

								

								<div class="table-responsive" id="chart_groups">
									<table class="table table-striped table-bordered table-hover dataTables-example" id="fiscal_years_table">
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
														<button class="btn btn-info btn-sm" onclick="showEditModal(<?php echo $year['id']; ?>, '<?php echo $year['begin']; ?>', '<?php echo $year['end']; ?>', <?php echo $year['closed']; ?>)">
															<i class="fa fa-edit"></i> Edit
														</button>
														<?php if ($year['closed'] == 0): ?>
															<button class="btn btn-warning btn-sm" onclick="showClosingModal(<?php echo $year['id']; ?>, '<?php echo $year['end']; ?>')">
																<i class="fa fa-lock"></i> Close Year
															</button>
														<?php else: ?>
															<button class="btn btn-danger btn-sm" onclick="showRollbackModal(<?php echo $year['id']; ?>)">
																<i class="fa fa-unlock"></i> Reopen Year
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

		
		<!-- Add Year Modal -->
		<div class="modal fade" id="addYearModal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
						<h4 class="modal-title">Create Fiscal Year</h4>
					</div>
					<form action="" method="post">
					<div class="modal-body">
						<?php
						$last_year = $db->prepare('SELECT * FROM chart_fiscal_year ORDER BY id DESC LIMIT 1');
						$last_year->execute();
						$last_year = $last_year->fetch();
						$min_date = $last_year ? date('Y-m-d', strtotime($last_year['end'] . ' +1 day')) : date('Y-01-01');
						$default_end = $last_year ? date('Y-m-d', strtotime($last_year['end'] . ' +365 days')) : date('Y-12-31');
						?>
						<div class="form-group">
							<label for="start">Start Date</label>
							<input type="date" name="start" class="form-control" value="<?= $min_date ?>" required>
						</div>
						<div class="form-group">
							<label for="end">End Date</label>
							<input type="date" name="end" class="form-control" value="<?= $default_end ?>" required>
						</div>
						<div class="form-group">
							<label for="active">Mark as Active</label>
							<input type="checkbox" name="active" value="1">
							<span class="help-block m-b-none">Activating this year will automatically close any currently active year.</span>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" name="add_year" class="btn btn-primary">Create Year</button>
					</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Edit Year Modal -->
		<div class="modal fade" id="editYearModal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
						<h4 class="modal-title">Edit Fiscal Year</h4>
					</div>
					<form action="" method="post">
					<div class="modal-body">
						<input type="hidden" name="year_id" id="edit_year_id">
						<div class="form-group">
							<label for="start">Start Date</label>
							<input type="date" name="start" id="edit_start" class="form-control" required>
						</div>
						<div class="form-group">
							<label for="end">End Date</label>
							<input type="date" name="end" id="edit_end" class="form-control" required>
						</div>
						<div class="form-group">
							<label for="active">Mark as Active</label>
							<input type="checkbox" name="active" id="edit_active" value="1">
							<span class="help-block m-b-none">Activating this year will automatically close any currently active year.</span>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" name="edit_year" class="btn btn-success">Update Year</button>
					</div>
					</form>
				</div>
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
						<div id="closingPreviewContainer" class="well" style="background: #fdfdfd;">
							<div class="text-center" id="closingPreviewLoader">
								<i class="fa fa-spinner fa-spin fa-3x"></i><br>Calculating Closing Preview...
							</div>
							<div id="closingPreviewContent" style="display:none;"></div>
						</div>
						<hr>
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

		<!-- Rollback closing modal-->
		<div class="modal fade" id="yearRollbackModal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
						<h4 class="modal-title text-danger"><i class="fa fa-warning"></i> Reopen Fiscal Year</h4>
					</div>
					<div class="modal-body">
						<div class="alert alert-danger">
							<strong>Warning:</strong> Reopening a fiscal year is a destructive action that deletes closing entries and the subsequent fiscal year.
						</div>
						<div id="rollbackPreviewContainer" class="well" style="background: #fdfdfd;">
							<div class="text-center" id="rollbackPreviewLoader">
								<i class="fa fa-spinner fa-spin fa-3x"></i><br>Analyzing Rollback Impact...
							</div>
							<div id="rollbackPreviewContent" style="display:none;"></div>
						</div>
						<input type="hidden" id="rollback_year_id">
						<div id="rollbackStatus" class="alert" style="display: none;"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-danger" id="confirmRollback" disabled>
							<i class="fa fa-unlock"></i> Confirm Reopen Year
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
				setTimeout(function() {
				    popupWindow.focus();
				    popupWindow.print();
				}, 1000);
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

				$('#confirmClosing').prop('disabled', true);
				$('#closingPreviewLoader').show();
				$('#closingPreviewContent').hide();

				$.post('preview_close_fiscal_year.php', { current_year_id: yearId }, function(response) {
					if (response.success) {
						const d = response.data;
						let html = '<h3 class="text-center">Projected Net Income</h3>';
						html += '<table class="table table-bordered">';
						html += '<tr><td><strong>Total Income</strong></td><td class="text-right text-success">' + Number(d.income).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td></tr>';
						html += '<tr><td><strong>Total Expenses</strong></td><td class="text-right text-danger">' + Number(d.expenses).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td></tr>';
						html += '<tr class="active"><td><strong>Net Income (to Retained Earnings)</strong></td><td class="text-right"><strong>' + Number(d.net_income).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</strong></td></tr>';
						html += '</table>';
						$('#closingPreviewContent').html(html).show();
						$('#confirmClosing').prop('disabled', false);
					} else {
						$('#closingPreviewContent').html('<div class="alert alert-danger">Failed to load preview: ' + response.message + '</div>').show();
					}
					$('#closingPreviewLoader').hide();
				}, 'json').fail(function() {
					$('#closingPreviewContent').html('<div class="alert alert-danger">Failed to communicate with server for preview.</div>').show();
					$('#closingPreviewLoader').hide();
				});

				// Show the modal
				$('#yearClosingModal').modal('show');
			}

			function showRollbackModal(yearId) {
				$('#rollback_year_id').val(yearId);
				$('#confirmRollback').prop('disabled', true);
				$('#rollbackPreviewLoader').show();
				$('#rollbackPreviewContent').hide();
				$('#rollbackStatus').hide();

				$.post('preview_rollback_fiscal_year.php', { fiscal_year_id: yearId }, function(response) {
					if (response.success) {
						const d = response.data;
						if (d.can_rollback) {
							let html = '<ul class="list-group">';
							html += '<li class="list-group-item text-danger"><i class="fa fa-trash"></i> <strong>' + d.closing_entries_to_delete + '</strong> closing entries will be deleted.</li>';
							html += '<li class="list-group-item text-danger"><i class="fa fa-trash"></i> <strong>' + d.opening_entries_to_delete + '</strong> opening entries in the next year will be deleted.</li>';
							if (d.next_year_to_delete) {
								html += '<li class="list-group-item text-danger"><i class="fa fa-trash"></i> The subsequent fiscal year period will be deleted.</li>';
							}
							html += '</ul><p class="text-success"><i class="fa fa-check"></i> Safe to rollback. No manual transactions will be lost.</p>';
							$('#rollbackPreviewContent').html(html).show();
							$('#confirmRollback').prop('disabled', false);
						} else {
							let html = '<div class="alert alert-danger"><i class="fa fa-ban"></i> <strong>Cannot Rollback!</strong><br><ul style="padding-left: 20px; margin-top: 10px;">';
							d.blocking_reasons.forEach(function(r) { html += '<li>' + r + '</li>'; });
							html += '</ul></div>';
							$('#rollbackPreviewContent').html(html).show();
						}
					} else {
						$('#rollbackPreviewContent').html('<div class="alert alert-danger">Failed to load preview: ' + response.message + '</div>').show();
					}
					$('#rollbackPreviewLoader').hide();
				}, 'json').fail(function() {
					$('#rollbackPreviewContent').html('<div class="alert alert-danger">Failed to communicate with server for preview.</div>').show();
					$('#rollbackPreviewLoader').hide();
				});

				$('#yearRollbackModal').modal('show');
			}

			
			function showEditModal(id, start, end, closed) {
			    $('#edit_year_id').val(id);
			    $('#edit_start').val(start);
			    $('#edit_end').val(end);
			    
			    if (closed == 1) {
			        $('#edit_active').prop('checked', false);
			        // Optionally make it readonly if closed
			    } else {
			        $('#edit_active').prop('checked', true);
			    }
			    
			    $('#editYearModal').modal('show');
			}
			
			$(document).ready(function() {
			    $('#fiscal_years_table').DataTable({
			        pageLength: 25,
			        responsive: true,
			        order: [[1, "desc"]]
			    });
			});

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

				$('#confirmRollback').click(function() {
					const btn = $(this);
					btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
					$('#rollbackStatus').hide();

					$.ajax({
						url: 'rollback_close_fiscal_year.php',
						type: 'POST',
						data: { fiscal_year_id: $('#rollback_year_id').val() },
						dataType: 'json',
						success: function(response) {
							if (response.success) {
								$('#rollbackStatus')
									.removeClass('alert-danger')
									.addClass('alert-success')
									.html('<i class="fa fa-check"></i> ' + response.message)
									.show();
								setTimeout(function() { window.location.reload(); }, 2000);
							} else {
								$('#rollbackStatus')
									.removeClass('alert-success')
									.addClass('alert-danger')
									.html('<i class="fa fa-times"></i> ' + response.message)
									.show();
								btn.prop('disabled', false).html('<i class="fa fa-unlock"></i> Confirm Reopen Year');
							}
						},
						error: function() {
							$('#rollbackStatus')
								.removeClass('alert-success')
								.addClass('alert-danger')
								.html('<i class="fa fa-times"></i> An error occurred while processing your request')
								.show();
							btn.prop('disabled', false).html('<i class="fa fa-unlock"></i> Confirm Reopen Year');
						}
					});
				});
			});
		</script>
</body>

</html>