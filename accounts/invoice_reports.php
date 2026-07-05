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

// Get parameters
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-01-01');
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-12-31');

// get income and expense classes
$classes = $db->query('SELECT * FROM chart_class WHERE 1');

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

$account_payable = 2124; //account code for account payable group, we will use it to get the balace of each supplier from our ledger

// Group data by supplier and invoice number
function groupData($data)
{
	$grouped = [];
	$t = 0;
	foreach ($data as $entry) {
		$supplier = $entry['sn'];
		$invoice_no = isset($entry['invoice_no']) ? $entry['invoice_no'] : 'INITIAL CREDITS/PURCHASE';

		if (!isset($grouped[$supplier])) {
			$grouped[$supplier] = [
				'name' => $entry['name'],
				'sn' => $supplier,
				'invoices' => []
			];
		}

		if (!isset($grouped[$supplier]['invoices'][$invoice_no])) {
			$grouped[$supplier]['invoices'][$invoice_no] = [];
		}

		$grouped[$supplier]['invoices'][$invoice_no][] = $entry;
	}
	return $grouped;
}



if (isset($_GET['start']) && isset($_GET['end'])) {
	$the_stetment = 1;

	$start = $_GET['start'];
	$end = $_GET['end'];

	// Base SQL query
	$sql = "SELECT c.invoice_no, c.date_entry2 as date_entry, c.cr_amt as dr_amt, s.sn, s.name 
            FROM chart_ledger AS c 
            INNER JOIN stock_company AS s ON c.insurance_no = s.sn 
            WHERE c.date_entry2 BETWEEN :start AND :end
			AND transc_type = 'CREDIT'";

	// Check if invoice_no is set
	if (isset($_GET['invoice_no']) && $_GET['invoice_no'] != '') {
		$invoice_no = $_GET['invoice_no'];
		$sql .= " AND c.invoice_no = :invoice_no";
	}

	// Prepare and execute the query
	$reports = $db->prepare($sql);

	// Bind the date parameters
	$reports->bindParam(':start', $start);
	$reports->bindParam(':end', $end);

	// Bind the invoice number parameter if it is set
	if (isset($invoice_no)) {
		$reports->bindParam(':invoice_no', $invoice_no);
	}

	$reports->execute();
	$reports = $reports->fetchAll(PDO::FETCH_ASSOC);

	$groupedData = groupData($reports);
}
include 'inc/functions.php';
redirect_to_active_year();

$invoices = $db->prepare('SELECT DISTINCT c.invoice_no, s.sn, s.name FROM chart_ledger AS c INNER JOIN stock_company AS s ON c.insurance_no = s.sn');
$invoices->execute();
$invoices = $invoices->fetchAll(PDO::FETCH_ASSOC);

?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">

								<h5>Accounting Dashboard</h5>


							</div>
							<div class="ibox-content">

									<!-- Filter Form -->
									<div class="row">
										<div class="col-md-12">
											<form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">

												<!-- Date Selection Row -->
												<div class="row mb-3">
													<div class="col-md-3">
														<div class="form-group">
															<label for="year" class="control-label"><strong>Year</strong></label>
															<select name="year" id="year_select" class="form-control">
																<?php for ($y = 2020; $y <= date('Y') + 1; $y++) : ?>
																	<option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>><?php echo $y; ?></option>
																<?php endfor; ?>
															</select>
														</div>
													</div>

													<div class="col-md-3">
														<div class="form-group">
															<label for="start" class="control-label"><strong>Start Date</strong></label>
															<input type="date" id="start" name="start" class="form-control" value="<?php echo $start_date; ?>" required>
														</div>
													</div>

													<div class="col-md-3">
														<div class="form-group">
															<label for="end" class="control-label"><strong>End Date</strong></label>
															<input type="date" id="end" name="end" class="form-control" value="<?php echo $end_date; ?>" required>
														</div>
													</div>
													
													<div class="col-md-3">
														<div class="form-group">
															<label for="invoice_no" class="control-label"><strong>Invoice</strong></label>
															<select name="invoice_no" id="invoice_no" class="form-control select2">
																<option value="">--select invoice--</option>
																<?php foreach ($invoices as $iv) : ?>
																	<?php if ($iv['invoice_no'] != null && $iv['invoice_no'] != '') : ?>
																		<option value="<?= $iv['invoice_no'] ?>" <?php echo (isset($_GET['invoice_no']) && $_GET['invoice_no'] == $iv['invoice_no']) ? 'selected' : ''; ?>><?= $iv['invoice_no'] ?> - <?= $iv['name'] ?></option>
																	<?php endif ?>
																<?php endforeach ?>
															</select>
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

								<?php if (isset($the_stetment)) { ?>



									<div id="chart_groups">
										<h1>Invoice Settlement Reports</h1>
										<h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending
											<?php echo date('d M Y', strtotime($_GET['end'])); //echo $_GET['end']; 
											?></h3>

										<?php foreach ($groupedData as $supplier => $supplierData) : ?>
											<div class="panel panel-primary">
												<div class="panel-heading">
													<h3 class="panel-title"><?php echo htmlspecialchars($supplierData['name']); ?></h3>
												</div>
												<div class="panel-body">
													<?php foreach ($supplierData['invoices'] as $invoice_no => $transactions) : ?>
														<div class="panel panel-default">
															<div class="panel-heading">
																<h4 class="panel-title">
																	<?php if ($invoice_no == 'INITIAL CREDITS/PURCHASE') : ?>
																		<?php echo htmlspecialchars($invoice_no); ?>
																	<?php else : ?>
																		INVOICE NO - <?php echo htmlspecialchars($invoice_no); ?>
																	<?php endif ?>
																</h4>
															</div>
															<table class="table table-striped">
																<thead>
																	<tr>
																		<th>#</th>
																		<th>Date Entry</th>
																		<th>
																			<?php if ($invoice_no == 'INITIAL CREDITS/PURCHASE') : ?>
																				Credit Amount
																			<?php else : ?>
																				Debit Amount
																			<?php endif ?>
																		</th>
																	</tr>
																</thead>
																<tbody>
																	<?php
																	$total = 0;
																	foreach ($transactions as $index => $transaction) :
																		$total += $transaction['dr_amt'];
																	?>
																		<tr>
																			<td><?php echo htmlspecialchars($index + 1); ?></td>
																			<td><?php echo htmlspecialchars($transaction['date_entry'] !== NULL ? $transaction['date_entry'] : 'N/A'); ?></td>
																			<td><?php echo htmlspecialchars(number_format($transaction['dr_amt'], 2)); ?></td>
																		</tr>
																	<?php endforeach; ?>
																</tbody>
																<tfoot>
																	<tr>
																		<th colspan="2">Total</th>
																		<th><?php echo htmlspecialchars(number_format($total, 2)); ?></th>
																	</tr>
																</tfoot>
															</table>
														</div>
													<?php endforeach; ?>
												</div>
												<div class="panel-footer">
													<!--lets get the amout payable to each supplier, and how much has been paide to them and also the balace  -->
													<?php
													$stmt = $db->query("SELECT 
													sum(dr_amt) as TOTAL_DEBITS, 
													sum(cr_amt) as TOTAL_CREDITS
													FROM chart_ledger 
													WHERE insurance_no='$supplier' 
													and account_no='$account_payable' 
													AND patient_stt_status!=1
													AND DATE(date_entry2) BETWEEN '$start' AND '$end'");

													if ($stmt->rowCount() > 0) {
														$row = $stmt->fetch(PDO::FETCH_ASSOC);
														$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
														$TOTAL_DEBITS = $row['TOTAL_DEBITS'];

														$bal = $TOTAL_CREDITS - $TOTAL_DEBITS;
													} else {
														$TOTAL_DEBITS = $TOTAL_CREDITS = $bal = 0;
													}
													?>
													<div class="row">
														<div class="col-xs-4">
															<strong>Total Debits:</strong> <?php echo htmlspecialchars(number_format($TOTAL_DEBITS, 2)); ?>
														</div>
														<div class="col-xs-4">
															<strong>Total Credits:</strong> <?php echo htmlspecialchars(number_format($TOTAL_CREDITS, 2)); ?>
														</div>
														<div class="col-xs-4">
															<strong>Balance to be Paid:</strong> <?php echo htmlspecialchars(number_format($bal, 2)); ?>
														</div>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php } ?>

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
			$(document).ready(function() {
				$('.select2').select2();
			});
		</script>

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
		<script>
			// Hospital header HTML for printouts
			var hospitalHeader = `<?php echo addslashes($hospital_table); ?>`;

			// Auto-update date range when year changes
			document.getElementById('year_select').addEventListener('change', function() {
				const year = this.value;
				document.getElementById('start').value = `${year}-01-01`;
				document.getElementById('end').value = `${year}-12-31`;
			});

			// Quick date shortcut functions
			function setCurrentMonth() {
				const now = new Date();
				const year = now.getFullYear();
				const month = String(now.getMonth() + 1).padStart(2, '0');
				document.getElementById('start').value = `${year}-${month}-01`;
				const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
				document.getElementById('end').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year_select').value = year;
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
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function setCurrentYear() {
				const year = new Date().getFullYear();
				document.getElementById('start').value = `${year}-01-01`;
				document.getElementById('end').value = `${year}-12-31`;
				document.getElementById('year_select').value = year;
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
				document.getElementById('year_select').value = year;
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
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function exportToCSV() {
				var startDate = document.querySelector('input[name="start"]').value;
				var endDate = document.querySelector('input[name="end"]').value;
				var invoiceNo = document.querySelector('select[name="invoice_no"]').value;

				if (!startDate || !endDate) {
					alert('Please select start and end dates first.');
					return;
				}

				var exportUrl = 'invoice_reports_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate) + '&invoice_no=' + encodeURIComponent(invoiceNo);

				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'Invoice_Reports_' + startDate + '_to_' + endDate + '.csv';
				link.style.display = 'none';

				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}

			function printDiv(divId) {
				var content = document.getElementById(divId).innerHTML;
                var startDate = document.getElementById('start') ? document.getElementById('start').value : '';
                var endDate = document.getElementById('end') ? document.getElementById('end').value : '';
                var invoiceSelect = document.querySelector('select[name="invoice_no"]');
                var invoiceText = invoiceSelect && invoiceSelect.value !== '' ? invoiceSelect.options[invoiceSelect.selectedIndex].text : 'All Invoices';
                
                var filterSummary = '<div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; font-family: arial;">' +
                    '<h2 style="margin:0; padding:0; color: #333;">Invoice Reports</h2>' +
                    '<p style="margin:5px 0 0 0; font-size: 14px; color: #555;">' +
                    '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate + ' &nbsp;|&nbsp; ' +
                    '<strong>Invoice:</strong> ' + invoiceText +
                    '</p></div>';

				var popupWindow = window.open('', '_blank', 'width=900,height=900');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>Invoice Reports</title>');
				// Reference the external stylesheet from the main page
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body { font-size: 14px; color: #222; background: #fff; }
                        .table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px;}
                        .table td, .table th { padding: 5px; border: 1px solid #ddd; }
                        a { text-decoration: none !important; color: inherit !important; }
						.panel { border: 1px solid #ddd; margin-bottom: 20px; }
						.panel-heading { background-color: #f5f5f5; padding: 10px; border-bottom: 1px solid #ddd; }
						.panel-title { margin: 0; font-size: 16px; }
						.panel-body { padding: 15px; }
						.panel-footer { background-color: #f5f5f5; padding: 10px; border-top: 1px solid #ddd; }
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