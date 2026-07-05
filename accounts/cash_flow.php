<?php 
include('../Connections/Conn.php');
session_start();
include('inc/header.php');

// Fetch hospital details from the database
$hospital_details_query = 'SELECT * FROM hospital_details LIMIT 1';
$hospital_details_stmt = $db->prepare($hospital_details_query);
$hospital_details_stmt->execute();
$hospital_details = $hospital_details_stmt->fetch(PDO::FETCH_ASSOC);

// Construct the hospital table HTML
$hospital_table = '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">';
$hospital_table .= '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
$hospital_table .= '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
$hospital_table .= '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
$hospital_table .= '</table><br>';

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$fiscal_year_id = isset($_GET['fiscal_year']) && $_GET['fiscal_year'] != '' ? $_GET['fiscal_year'] : null;
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-01-01');
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-12-31');
?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php
        include("../../inc/nav_accounts_side_bar.php");
		$shw_side = 'yes';
?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>
			<div class="wrapper wrapper-content">
					<div class="row">
						<div class="col-lg-12">
							<div class="ibox float-e-margins">
								<div class="ibox-title">

									<h5>Accounts Dashboard <?php echo date('h:i a'); ?></h5>
								</div>
								<div class="ibox-content">
									<!-- Filter Form -->
									<div class="row">
										<div class="col-md-12">
											<form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">

												<!-- Date Selection Row -->
												<div class="row mb-3">
													<div class="col-md-4">
														<?php echo render_fiscal_year_filter($fiscal_year_id); ?>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="start" class="control-label"><strong>Start Date</strong></label>
															<input type="date" id="start" name="start" class="form-control" value="<?php echo $start_date; ?>" required>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="end" class="control-label"><strong>End Date</strong></label>
															<input type="date" id="end" name="end" class="form-control" value="<?php echo $end_date; ?>" required>
														</div>
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-12">
														<div class="form-group" style="text-align: right;">
															<label class="control-label">&nbsp;</label>
															<div style="margin-top: 8px;">
																<button type="submit" class="btn btn-primary" title="Click to fetch data">
																	<i class="fa fa-search"></i> <strong>DISPLAY</strong>
																</button>
                                                                <button class="btn btn-success" type="button" onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Report</i></button>
															</div>
														</div>
													</div>
												</div>

												<!-- Quick Date Shortcuts -->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group" style="margin-bottom: 0;">
															<label class="control-label"><strong>Quick Date Shortcuts</strong></label>
															<div style="margin-top: 5px;">
																<button type="button" class="btn btn-xs btn-info" onclick="setCurrentMonth()">
																	<i class="fa fa-calendar"></i> Current Month
																</button>
																<button type="button" class="btn btn-xs btn-info" onclick="setCurrentQuarter()">
																	<i class="fa fa-calendar-alt"></i> Current Quarter
																</button>
																<button type="button" class="btn btn-xs btn-info" onclick="setCurrentYear()">
																	<i class="fa fa-calendar-year"></i> Current Year
																</button>
																<button type="button" class="btn btn-xs btn-warning" onclick="setLastMonth()">
																	<i class="fa fa-backward"></i> Last Month
																</button>
																<button type="button" class="btn btn-xs btn-warning" onclick="setLastQuarter()">
																	<i class="fa fa-step-backward"></i> Last Quarter
																</button>
															</div>
														</div>
													</div>
												</div>

											</form>
										</div>
									</div>

									<div class="row" id="chart_groups">
                                        <div class="col-md-12">
                                            <h1>Cash Flow Statement</h1>
                                            <p>This report has not been implemented yet.</p>
                                        </div>
									</div>
								</div>
							</div>
						</div>
					</div>


				<?php include '../../inc/footer.php'; ?>

			</div>
		</div>


		<?php include '/inc/footer_scripts.php'; ?>


		<script>
            // Hospital header HTML for printouts
			var hospitalHeader = `<?php echo addslashes($hospital_table); ?>`;



            // Quick date shortcut functions
            function setCurrentMonth() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                document.getElementById('start').value = `${year}-${month}-01`;
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                document.getElementById('end').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
                document.getElementById('start').closest('form').submit();
            }

            function setCurrentQuarter() {
                const now = new Date();
                const year = now.getFullYear();
                const quarter = Math.floor(now.getMonth() / 3);
                const startMonth = String(quarter * 3 + 1).padStart(2, '0');
                const endMonth = String(quarter * 3 + 3).padStart(2, '0');
                document.getElementById('start').value = `${year}-${startMonth}-01`;
                const lastDay = new Date(year, quarter * 3 + 3, 0).getDate();
                document.getElementById('end').value = `${year}-${endMonth}-${String(lastDay).padStart(2, '0')}`;
                document.getElementById('start').closest('form').submit();
            }

            function setCurrentYear() {
                const year = new Date().getFullYear();
                document.getElementById('start').value = `${year}-01-01`;
                document.getElementById('end').value = `${year}-12-31`;
                document.getElementById('start').closest('form').submit();
            }

            function setLastMonth() {
                const now = new Date();
                const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const year = lastMonth.getFullYear();
                const month = String(lastMonth.getMonth() + 1).padStart(2, '0');
                document.getElementById('start').value = `${year}-${month}-01`;
                const lastDay = new Date(year, lastMonth.getMonth() + 1, 0).getDate();
                document.getElementById('end').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
                document.getElementById('start').closest('form').submit();
            }

            function setLastQuarter() {
                const now = new Date();
                const currentQuarter = Math.floor(now.getMonth() / 3);
                const lastQuarter = currentQuarter === 0 ? 3 : currentQuarter - 1;
                const year = currentQuarter === 0 ? now.getFullYear() - 1 : now.getFullYear();
                const startMonth = String(lastQuarter * 3 + 1).padStart(2, '0');
                const endMonth = String(lastQuarter * 3 + 3).padStart(2, '0');
                document.getElementById('start').value = `${year}-${startMonth}-01`;
                const lastDay = new Date(year, lastQuarter * 3 + 3, 0).getDate();
                document.getElementById('end').value = `${year}-${endMonth}-${String(lastDay).padStart(2, '0')}`;
                document.getElementById('start').closest('form').submit();
            }

			<?php
            if (isset($error_status) && $error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } elseif (isset($error_status) && $error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
				timeOut: 5000
			})
			<?php } ?>

            function printDiv(divId) {
				var content = document.getElementById(divId).innerHTML;
                var startDate = document.getElementById('start') ? document.getElementById('start').value : '';
                var endDate = document.getElementById('end') ? document.getElementById('end').value : '';
                
                var filterSummary = '<div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; font-family: arial;">' +
                    '<h2 style="margin:0; padding:0; color: #333;">Cash Flow Statement</h2>' +
                    '<p style="margin:5px 0 0 0; font-size: 14px; color: #555;">' +
                    '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate +
                    '</p></div>';

				var popupWindow = window.open('', '_blank', 'width=900,height=900');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>Cash Flow Statement</title>');
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body { font-size: 14px; color: #222; background: #fff; }
                        .table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px;}
                        .table td, .table th { padding: 5px; border: 1px solid #ddd; }
                        a { text-decoration: none !important; color: inherit !important; }
                        .dataTables_filter, .dataTables_paginate, .dataTables_info, .dataTables_length { display: none; }
                                                /* Hide original headings in the print popup to prevent duplication */
                        body > h1, body > h3, .text-center.mb-4 { display: none !important; }
                        @media print {
                            a[href]:after { content: none !important; }
                        }
                    </style>
                `);
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(hospitalHeader);
                popupWindow.document.write(filterSummary);
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				setTimeout(function() {
				    popupWindow.focus();
				    popupWindow.print();
				}, 1000);
			}
		</script>

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
</body>

</html>