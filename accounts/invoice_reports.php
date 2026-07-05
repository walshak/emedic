<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

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

									<form action="" method="get" class="form-inline">
										<div class="form_sep" align="">
											<label for="">Start Date</label>
											<input type="date" name="start" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>" required>

											&nbsp;

											<label for="">End Date</label>
											<input type="date" name="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>" required>
											&nbsp;
											<label for="invoice_no">Invoice</label>
											<select name="invoice_no" id="invoice_no" class="form-control select2">
												<option value="">--select invoice--</option>
												<?php foreach ($invoices as $iv) : ?>
													<?php if ($iv['invoice_no'] != null && $iv['invoice_no'] != '') : ?>
														<option value="<?= $iv['invoice_no'] ?>"><?= $iv['invoice_no'] ?> - <?= $iv['name'] ?></option>
													<?php endif ?>
												<?php endforeach ?>
											</select>

											<button type="submit" class="btn btn-primary">Display </button>
										</div>
										<br>
									</form>

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


									<button class="btn btn-success" onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Invoice Report</i></button>
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

		<script src="../js/idle.js"></script>
</body>

</html>