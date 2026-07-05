<?php

if (isset($_GET['cnlcr'])) {
	$cnlcr = trim($_GET['cnlcr']);
	$cnlcr = base64_decode(base64_decode(base64_decode($cnlcr)));
	// Reset invoice & credit flags for unpaid service
	$stmt = $db->prepare("
			UPDATE patient_ap_services 
			SET med_dosage_unit = 1,
			cr = 0,
			invoice_status = 0 
			WHERE sn = :cnl 
			  AND paystatus = 0
		");
	$stmt->bindParam(':cnl', $cnlcr, PDO::PARAM_STR);

	if ($stmt->execute()) {
		// Fetch service details for logging
		$stmt = $db->prepare("
				SELECT item_services, pay, qty, hospital_no 
				FROM patient_ap_services 
				WHERE sn = :cnlcr
			");
		$stmt->bindParam(':cnlcr', $cnlcr, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$rmks = 'EMR' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];
		} else {
			$rmks = 'Record not found for cancellation.';
		}

		// Log action
		$desc   = $rmks;
		$staff  = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Unknown Staff';
		$pid    = $cnlcr;
		$pname  = ''; // can be set if available
		$action = 'Credit Inv.Cancelled';

		include_once("../logs.php");
	}
}



if (isset($_POST['process_inv'])) {

	try {

		$hosp_no = $_POST['emr']; ///php 5.6;
		$dept_id = $_SESSION['dept_id'];

		if (!$hosp_no) {
			throw new Exception("Hospital number missing.");
		}

		// Items selected by user
		$SystemSelected = $_POST['inv'];

		if (empty($SystemSelected)) {
			throw new Exception("No item selected. Invoice not generated.");
		}

		include_once("../inc/utilities.php");   // Contains INV()

		$saveCount = 0;
		$med_frequency = 1;   /// disable nursing to delete after invoicing
		$errors = [];

		// Prepare SELECT in advance (re-used inside loop)
		$stmtSelect = $db->prepare("
            SELECT sn, claim_amt, pay, qty
            FROM patient_ap_services
            WHERE hospital_no = :hosp_no AND sn = :sn
        ");

		// Prepare UPDATE once
		$stmtUpdate = $db->prepare("
            UPDATE patient_ap_services SET 
                invoice_no      = :invoice_no,
                invoice_status  = :invoice_status,
                claim_amt       = :claim_amt,
                pay             = :pay,
                qty             = :qty,
                drug_status     = :drug_status, 
                invoice_by      = :invoice_by, 
                invoice_date    = :invoice_date, 
                med_frequency   = :med_frequency,
                paystatus       = :paystatus
            WHERE hospital_no  = :hospital_no
              AND sn           = :sn
        ");

		$invoice_by  = $_SESSION['fullname'];
		$invoice_date = date('Y-m-d');
		$invoice_status = 1;
		$drug_status = 1;

		foreach ($SystemSelected as $sn) {

			// 1. Fetch service record
			$stmtSelect->execute([
				':hosp_no' => $hosp_no,
				':sn'      => $sn
			]);

			$row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

			if (!$row) {
				$errors[] = "Item SN {$sn} not found.";
				continue;
			}

			$claim_amt  = (float)$row['claim_amt'];
			$pay        = (float)$row['pay'];
			$qty        = (int)$row['qty'];

			if ($qty <= 0) {
				$errors[] = "Invalid quantity for item SN {$sn}.";
				continue;
			}

			// Generate Invoice No
			$invoice_no = INV();

			// Determine paystatus
			$paystatus = ($claim_amt > 0) ? 1 : 0;

			// 2. UPDATE row
			$stmtUpdate->execute([
				':invoice_no'     => $invoice_no,
				':invoice_status' => $invoice_status,
				':claim_amt'      => $claim_amt,
				':pay'            => $pay,
				':qty'            => $qty,
				':drug_status'    => $drug_status,
				':invoice_by'     => $invoice_by,
				':invoice_date'   => $invoice_date,
				':med_frequency'  => $med_frequency,
				':paystatus'      => $paystatus,
				':hospital_no'    => $hosp_no,
				':sn'             => $sn
			]);

			if ($stmtUpdate->rowCount() > 0) {
				$saveCount++;
			} else {
				$errors[] = "Item SN {$sn} not updated.";
			}
		}

		// ===============================
		// SUCCESS OR ERROR MESSAGE
		// ===============================
		if ($saveCount > 0) {

			$error_status = 2;
			$error_msg = "Success: Invoice generated for {$saveCount} item(s).";

			if (!empty($errors)) {
				$error_msg .= " Some issues occurred: " . implode(" | ", $errors);
			}
		} else {
			$error_status = 1;
			$error_msg = "Error: Invoice not generated. " . implode(" | ", $errors);
		}
	} catch (Exception $e) {

		// Global failure
		$error_status = 1;
		$error_msg = "Exception: " . $e->getMessage();
	}
}



// ==============================
// PATIENT BILLING & TRANSFER SUMMARY
// ==============================

// Initialize variables
$hos_no          = $hospital_no;
$emr             = $hospital_no;
$patient_type    = $patient_insurance;
$referral_name   = null;
$total_inv       = null;
$post_claimm     = '0';
$general_credit_limit = $_SESSION['credit_limit_status'];

// Fetch current balance
$balance_info = call_current_balance($db, $emr, $general_credit_limit);
$current_balance = $balance_info["current_balance"];

// Determine admission date range
$__from_date__ = $__to_date__ = date('Y-m-d');
$adm_count = 0;

$stmt = $db->prepare("SELECT date_admit FROM admission WHERE hospital_no = :emr AND adm_status = '3' LIMIT 1");
$stmt->execute([':emr' => $hospital_no]);
if ($rowx = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$__from_date__ = date('Y-m-d', strtotime($rowx['date_admit']));
	$adm_count = 1;
}

// ==============================
// MAIN DISPLAY
// ==============================
?>
<?php
// ==============================
// INITIAL DEFINITIONS
// ==============================
$amt_due = $hmo_amt_due = 0;
$s = 1;

// Ensure required variables exist
$hos_no        = isset($hos_no) ? $hos_no : $hospital_no;
$from_date = isset($__from_date__) && $__from_date__ !== ''
	? $__from_date__
	: date('Y-m-d');

$to_date = isset($__to_date__) && $__to_date__ !== ''
	? $__to_date__
	: date('Y-m-d');


// Main base query
$base_query = "
    SELECT *
    FROM patient_ap_services
    WHERE hospital_no = :hos_no
      AND DATE(date_entry) BETWEEN :from_date AND :to_date
      AND paystatus = 0
      AND (invoice_status = 0 OR invoice_status = 1)
";

// ==============================
// RENDER REUSABLE SERVICE TABLE
// ==============================
function renderServiceTable(PDO $db, $query, $params, $title, $bg_color, &$counter)
{
	$stmt = $db->prepare($query);
	$stmt->execute($params);

	if ($stmt->rowCount() === 0) {
		return [
			'credit'  => 0,
			'inv'     => 0,
			'pending' => 0,
			'claim'   => 0
		];
	}

	$totals = ['credit' => 0, 'inv' => 0, 'pending' => 0, 'claim' => 0];
?>

	<table class="table table-striped" width="100%">
		<tr>
			<th>#</th>
			<th><?= htmlspecialchars($title) ?></th>
			<th>Qty</th>
			<th>Amount</th>
			<th>Claims</th>
			<th>Status</th>
			<th>Date Entered</th>
			<th>Entered By</th>
		</tr>

		<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
			$totals['claim'] += (float)$row['claim_amt'];
			$pay = (float)$row['pay'];
		?>

			<tr>
				<td></td>
				<td><?= $counter++ . ' - ' . htmlspecialchars($row['item_services']); ?></td>
				<td><?= (int)$row['qty']; ?></td>
				<td><?= number_format($pay); ?></td>
				<td><?= number_format($row['claim_amt']); ?></td>
				<td>
					<?php
					if ($row['cr'] == 1) {
						$totals['credit'] += $pay;
						echo '<strong style="color:#F00">Credit</strong>';
					} elseif ($row['invoice_status'] == 1) {
						$totals['inv'] += $pay;
						echo '<strong style="color:#F00">Invoiced</strong>';
					} else {
						$totals['pending'] += $pay;
						echo '<strong style="color:cornflowerblue;">Not Invoiced</strong>';
					}
					?>
				</td>
				<td><?= date('d M,y h:i a', strtotime($row['date_entry'])); ?></td>
				<td><?= htmlspecialchars($row['prepared_by']); ?></td>
			</tr>

		<?php endwhile; ?>
	</table>

	<table width="100%" style="font-size: 16px; background-color: <?= $bg_color ?>;">
		<tr>
			<td>&nbsp;</td>
			<td>
				<h4><?= htmlspecialchars($title) ?></h4>
			</td>
			<td width="25%">CREDIT/BILLED: <strong><?= number_format($totals['credit']) ?></strong></td>
			<td width="25%">INVOICED: <strong><?= number_format($totals['inv']) ?></strong></td>
			<td width="25%">PENDING: <strong><?= number_format($totals['pending']) ?></strong></td>
		</tr>
	</table>
	<br>

<?php
	return $totals;
}
?>

<!-- HTML BEGINS -->
<form method="post" action="patient_bill.php?hosp_no=<?= htmlspecialchars($hos_no); ?>&Invoice">

	<?php
	$params = [
		':hos_no'     => $hos_no,
		':from_date'  => $from_date,
		':to_date'    => $to_date
	];

	// PHARMACY
	$totals_pharm = renderServiceTable(
		$db,
		$base_query . " AND serv_group='Pharmacy' ORDER BY date_entry",
		$params,
		"Pharmacy",
		"aqua",
		$s
	);

	// LAB / RADIOLOGY
	$totals_lab = renderServiceTable(
		$db,
		$base_query . " AND serv_group IN ('Laboratory','Radiology') ORDER BY date_entry",
		$params,
		"Investigations",
		"aquamarine",
		$s
	);

	// OTHER SERVICES
	$totals_other = renderServiceTable(
		$db,
		$base_query . " AND  serv_group IN ('Consultation','Registration','Medical Services','Other Services') ORDER BY date_entry",
		$params,
		"Other Services",
		"cornsilk",
		$s
	);
	?>

	<!-- NURSING SERVICES -->
	<?php
	$nursing_query = "
    SELECT *
    FROM patient_ap_services
    WHERE hospital_no = :hos_no
      AND invoice_status != 3
      AND paystatus = 0
      AND (med_dosage_unit = 0 OR med_dosage_unit IS NULL)
      AND (serv_group='Nursing Services' OR cat_type='Nursing Consumable') ORDER BY date_entry";

	$stmt = $db->prepare($nursing_query);
	$stmt->execute([':hos_no' => $hos_no]);
	$nursing = $nursing_inv = $nursing_cr = $nursing_claim = 0;

	if ($stmt->rowCount() > 0):
	?>


		<h1>Nursing Department:</h1>
		<table class="table table-striped">
			<tr>
				<th>#</th>
				<th>Nursing</th>
				<th>Qty</th>
				<th>Amount</th>
				<th>Claims</th>
				<th>Status</th>
				<th>Date Entered</th>
				<th>Entered By</th>
				<th>Action</th>
				<th></th>
			</tr>

			<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
				$pay = (float)$row['pay'];
				$nursing_claim += (float)$row['claim_amt'];
			?>
				<tr>
					<td><input type="checkbox" value="<?= $row['sn']; ?>" name="inv[]" /></td>
					<td><?= $s++ . ' - ' . htmlspecialchars($row['item_services']); ?></td>
					<td><?= (int)$row['qty']; ?></td>
					<td><?= number_format($pay); ?></td>
					<td><?= number_format($row['claim_amt']); ?></td>
					<td>
						<?php
						if ($row['cr'] == 1) {
							$nursing_cr += $pay;
							echo '<strong style="color:#F00">Credit</strong>';
						} elseif ($row['invoice_status'] == 1) {
							$nursing_inv += $pay;
							echo '<strong style="color:#F00">Invoiced</strong>';
						} else {
							$nursing += $pay;
							echo '<strong style="color:cornflowerblue;">Not Invoiced</strong>';
						}
						?>
					</td>
					<td><?= date('d M,y h:i a', strtotime($row['date_entry'])); ?></td>
					<td><?= htmlspecialchars($row['prepared_by']); ?></td>


					<td>

						<?php


						$dateEntry = strtotime($row['date_entry']);
						$now = time();
						$hours48 = 12 * 60 * 60;

						if (($now - $dateEntry) <= $hours48 && $_SESSION['unit_head'] == 1) {
							$sn_q = base64_encode(base64_encode(base64_encode($row['sn'])));
						?>
							<a href="patient_bill.php?hosp_no=<?= $hosp_no ?>&Invoice&cnlcr=<?= $sn_q; ?>"
								class="btn btn-danger btn-xs"
								onclick="return confirm('Do you want to cancel credit invoice?');">
								Cancel
							</a>
						<?php
						}
						?>
					</td>
					<td><?= $row['invoice_status'] == 1 ? '<strong style="color:dodgerblue;">PayNow</strong>' : 'Inv.' ?></td>
				</tr>
			<?php endwhile; ?>

		</table>

		<table width="100%" style="font-size:16px; background-color:antiquewhite;">
			<tr>
				<td>&nbsp;</td>
				<td>
					<h4>Nursing / Consumables</h4>
				</td>
				<td width="25%">CREDIT/BILLED: <strong><?= number_format($nursing_cr); ?></strong></td>
				<td width="25%">INVOICED: <strong><?= number_format($nursing_inv); ?></strong></td>
				<td width="25%">PENDING: <strong><?= number_format($nursing); ?></strong></td>
			</tr>
		</table>

	<?php endif; ?>

	<hr>
	<div class="note-section" style="border-left: 5px solid brown; padding: 10px; background-color: #f9f3ef;">
		<h2 style="color:brown; margin: 0;">NOTE:</h2>
	</div>


	<p style="font-size: 18px;">
		Click the button below to refresh <strong>Bed Charges / Nursing Care</strong> services
		if they did not appear on the bill.
		<br>
		After refreshing, please return to this page.
	</p>

	<a href="patient.php?hosp_no=<?= $hos_no ?>&adm" class="btn btn-info btn-sm">
		Refresh Bed Charges / Nursing Care
	</a>

	<br><br>

	<p style="font-size: 18px;">
		Items marked with <strong>“PayNow”</strong> are ready for payment and do not require an invoice.
	</p>

	&nbsp;&nbsp;

	<button class="btn btn-danger btn-sm"
		type="submit"
		name="process_inv"
		onclick="return confirm('Are you sure you want to execute this action? Once invoice is generated/Post Claim, you cannot delete it.')">
		Generate Invoice / Post Claim
	</button>

	<hr>

	<!-- GRAND TOTALS -->
	<?php
	$grand_credit  = $totals_pharm['credit'] + $totals_lab['credit'] + $totals_other['credit'] + $nursing_cr;
	$grand_inv     = $totals_pharm['inv'] + $totals_lab['inv'] + $totals_other['inv'] + $nursing_inv;
	$grand_pending = $totals_pharm['pending'] + $totals_lab['pending'] + $totals_other['pending'] + $nursing;
	?>

	<table width="100%" style="font-size:16px; background-color:antiquewhite;">
		<tr>
			<td>&nbsp;</td>
			<td>
				<h4>Grand Total:</h4>
			</td>
			<td width="25%">CREDIT/BILLED: <strong><?= number_format($grand_credit); ?></strong></td>
			<td width="25%">INVOICED: <strong><?= number_format($grand_inv); ?></strong></td>
			<td width="25%">PENDING: <strong><?= number_format($grand_pending); ?></strong></td>
		</tr>
	</table>

	<hr>
	<h3>Current Deposit:
		<?= $current_balance <= 0 ? '<strong style="color:red;">No Deposit</strong>' : '₦' . number_format($current_balance, 2); ?>
	</h3>
	<input type="hidden" name="emr" value="<?= $hos_no; ?>">
</form>

<!-- PRINT FORM -->
<form method="post" action="../adm_transc.php">
	<input type="hidden" name="start" value="<?= $from_date; ?>" />
	<input type="hidden" name="end" value="<?= date('Y-m-d'); ?>" />
	<input type="hidden" name="emr" value="<?= $hos_no; ?>">
	<input type="hidden" name="target" value="nursing/patient_bill.php?hosp_no=<?= $hos_no ?>&Invoice">

	<button type="submit" class="btn btn-success" name="submit_">
		PRINT ADMISSION BILLING & INVOICE
	</button>
</form>