<div class="modal inmodal fade" id="print_medical_report_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""> Medical Reports</h4>
			</div>

			<div class="modal-body" id="medical_report_modal_body">
				<?php
				$sn = 1;
				$med_report_sent_stmt = $db->prepare("SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1'  AND action != 'draft'  ORDER BY id DESC");
				$med_report_sent_stmt->execute(array($hospital_no));
				if ($med_report_sent_stmt->rowCount() > 0) {
					$med_reports = $med_report_sent_stmt->fetchAll(PDO::FETCH_ASSOC);
					echo '
							<table class="table table-striped table-bordered table-hover dataTables-example-med-report">
								<thead>
										<tr>
											<th>#</th>
											<th>Compiled By</th>
											<th class="text-center">Date Created</th>
											<th class="text-center">Status</th>
											<th></th>
										</tr>
								</thead>
							<tbody>
						';

					foreach ($med_reports as $key => $med_report) {
						$id = $med_report["id"];
						$action = $med_report["action"];
						$created_at = $med_report["created_at"];
						$created_at_format = date('d M Y', strtotime("" . $created_at));

						if ($action == 'printed') {
							$btn = '<a class="btn btn-xs btn-primary">Preview</a>';
						}

						if ($action == 'save') {
							$action = 'Waiting for printing';
							$btn = '';
						}
						echo '
									<tr>
										<td>' . $sn++ . '</td>
										<td>' . $med_report["created_by_name"] . '</td>
										<td class="text-center">' . $created_at_format . '</td>
										<td class="text-center">' . ucfirst($action) . '</td>
										<td class="text-center"><a class="btn btn-xs btn-primary" href="../doctor/preview_med_report.php?token=' . base64_encode("medical_report_id-" . $id) . '" target="_BLANK"><i class="fa fa-print"></i> Preview & Print</a></td>
									</tr>
								';
					}
					echo '</tbody></table>';
				}else{
				    echo '<p class="text-center text-danger"> No Medical Report(s) found</p>';
                }
				?>

			</div>
		</div>
	</div>
</div>
