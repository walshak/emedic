<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

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

//get income and expense classes
$classes = $db->query("SELECT * FROM chart_class WHERE cid = 4 OR cid = 5");

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

// Get parameters
$fiscal_year_id = isset($_GET['fiscal_year']) && $_GET['fiscal_year'] != '' ? $_GET['fiscal_year'] : null;

// Determine default dates from fiscal year
if ($fiscal_year_id) {
    $stmt = $db->prepare("SELECT begin, end FROM chart_fiscal_year WHERE id = ?");
    $stmt->execute([$fiscal_year_id]);
    $fy = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->query("SELECT id, begin, end FROM chart_fiscal_year WHERE closed = 0 LIMIT 1");
    $fy = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fy) {
        $fiscal_year_id = $fy['id'];
    }
}

if (isset($fy) && $fy) {
    $default_start = $fy['begin'];
    $default_end = $fy['end'];
} else {
    $default_start = date('Y-01-01');
    $default_end = date('Y-12-31');
}

$start_date = isset($_GET['start']) ? $_GET['start'] : $default_start;
$end_date = isset($_GET['end']) ? $_GET['end'] : $default_end;

$_GET['start'] = $start_date;
$_GET['end'] = $end_date;

$the_stetment = 1;
include('inc/functions.php');
redirect_to_active_year();
?>

<body class="fixed-navigation">
    <style>
                                /* Hide original headings in the print popup to prevent duplication */
                        body > h1, body > h3, .text-center.mb-4 { display: none !important; }
                        @media print {
            a[href]:after {
                content: none !important;
            }
        }
    </style>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg sidebar-content">

            <?php include '../../inc/nav_header.php'; ?>
            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">

                                <h5>Accounts Dashboard</h5>


                            </div>
                            <div class="ibox-content">

                                	<!-- Filter Form -->
									<div class="row">
										<div class="col-md-12">
											<form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">

												<!-- Date Selection Row -->
												<div class="row mb-3">
													<div class="col-md-3">
														<?php echo render_fiscal_year_filter($fiscal_year_id); ?>
													</div>

													<div class="col-md-3">
														<div class="form-group">
															<label for="start" class="control-label"><strong>Start Date</strong></label>
															<input type="date" id="start" name="start" class="form-control" value="<?php echo $start_date; ?>">
														</div>
													</div>

													<div class="col-md-3">
														<div class="form-group">
															<label for="end" class="control-label"><strong>End Date</strong></label>
															<input type="date" id="end" name="end" class="form-control" value="<?php echo $end_date; ?>">
														</div>
													</div>

													<div class="col-md-3">
														<div class="form-group">
															<label class="control-label"><strong>Actions</strong></label>
															<div style="margin-top: 8px;">
																<button type="submit" class="btn btn-primary" title="Click to fetch data">
																	<i class="fa fa-search"></i> <strong>DISPLAY</strong>
																</button>
																<button type="button" class="btn btn-success" onclick="exportToCSV()" title="Export to CSV">
																	<i class="fa fa-download"></i> Export CSV
																</button>
																<button type="button" class="btn btn-primary" onclick="printDiv('chart_groups')" title="Print Report">
																	<i class="fa fa-print"></i> Print
																</button>
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


                                <?php if (isset($the_stetment)) : ?>
                                    <div class="table-responsive" id="chart_groups">

                                        <h1>Income Statment</h1>
                                        <h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending
                                            <?php echo date('d M Y', strtotime($_GET['end'])); //echo $_GET['end']; 
                                            ?></h3>
                                        <br>


                                        <table class="table" style="font-size: 17px; ">

                                            <tbody>
                                                <?php $netTotal = 0; ?>
                                                <tr>

                                                    <?php
                                                    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
                                                    $groups->execute([4]);
                                                    $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    <?php
                                                    foreach ($groups as $group) :
                                                        $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];
                                                    ?>

                                                        <?php if ($gTotal > 0 or $gTotal < 0) { ?>
                                                <tr>
                                                    <td>&nbsp;</td>

                                                    <td>
                                                        <strong>
                                                            <i>
                                                                <a href="ledger_entries.php?level=group&id=<?php echo $group['id']; ?>&start=<?php echo $_GET['start']; ?>&end=<?php echo $_GET['end']; ?>" target="_blank"><?= $group['name'] ?>
                                                                </a>
                                                            </i>
                                                        </strong>
                                                    </td>
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <?php echo number_format(abs($gTotal), 2); ?>

                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php endforeach ?>

                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th colspan="2">
                                                <h3>&nbsp;&nbsp;TOTAL INCOME</h3>
                                                <?php $cTotal = getClassTotal(4, $_GET['start'], $_GET['end'])['bal']; ?>
                                            </th>
                                            <th><i><strong><?= number_format(makePositive($cTotal, ''), 2); ?></strong></i></th>
                                        </tr>

                                        <!------------ EXPENSES --------------->




                                        <tr>
                                            <td colspan="2">

                                                <br>
                                                <h2><i><strong>EXPENSES</strong></i></h2>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>

                                            <?php
                                            $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
                                            $groups->execute([5]);
                                            $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            <?php
                                            foreach ($groups as $group) :
                                                $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];
                                            ?>

                                                <?php if ($gTotal > 0 or $gTotal < 0) { ?>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>
                                                <strong>
                                                    <i>
                                                        <a href="ledger_entries.php?level=group&id=<?php echo $group['id']; ?>&start=<?php echo $_GET['start']; ?>&end=<?php echo $_GET['end']; ?>" target="_blank"><?= $group['name'] ?>
                                                        </a>
                                                    </i>
                                                </strong>
                                            </td>
                                            <td><?php echo number_format(abs($gTotal), 2); ?></td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    <?php } ?>
                                <?php endforeach ?>

                                </tr>
                                <tr>
                                    <th colspan="2">TOTAL EXPENSES:
                                        <?php $cTotal = getClassTotal(5, $_GET['start'], $_GET['end'])['bal']; ?>
                                    </th>
                                    <th><strong><?= number_format(makePositive($cTotal, ''), 2); ?></strong></th>
                                    <th>&nbsp;</th>
                                </tr>

                                <tr>
                                    <td colspan="2">

                                        <br>
                                        <h2><i><strong>NET PROFIT :


                                                    <span style="text-decoration: underline double; border-top: 1px solid #000; display: inline-block;  padding: 5px;">

                                                        <?= format_accounting(makePositive(getClassTotal('4', $_GET['start'], $_GET['end'])['bal']) -
                                                            makePositive(getClassTotal('5', $_GET['start'], $_GET['end'])['bal']), '') ?></span>

                                                </strong></i></h2>
                                    </td>
                                    <td colspan="2">




                                    </td>
                                </tr>


                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>


                <?php include '../../inc/footer.php'; ?>

            </div>
        </div>


        <?php include('../modal_lock.php'); ?>
        <?php include '/inc/footer_scripts.php'; ?>


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
				document.getElementById('year').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function exportToCSV() {
				var startDate = document.querySelector('input[name="start"]').value;
				var endDate = document.querySelector('input[name="end"]').value;

				if (!startDate || !endDate) {
					alert('Please select start and end dates first.');
					return;
				}

				var exportUrl = 'income_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate);

				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'Income_Statement_' + startDate + '_to_' + endDate + '.csv';
				link.style.display = 'none';

				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}

            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var startDate = document.getElementById('start') ? document.getElementById('start').value : '';
                var endDate = document.getElementById('end') ? document.getElementById('end').value : '';
                
                var filterSummary = '<div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; font-family: arial;">' +
                    '<h2 style="margin:0; padding:0; color: #333;">Income Statement</h2>' +
                    '<p style="margin:5px 0 0 0; font-size: 14px; color: #555;">' +
                    '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate +
                    '</p></div>';

                var popupWindow = window.open('', '_blank', 'width=900,height=900');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>Income Statement Report</title>');
                // Reference the external stylesheet from the main page
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body { font-size: 14px; color: #222; background: #fff; }
                        .table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px;}
                        .table td { padding: 5px; border: none !important; }
                        a { text-decoration: none !important; color: inherit !important; }
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

        <script src="../js/idle.js"></script>
</body>

</html>