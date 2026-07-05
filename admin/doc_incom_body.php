<div align="center"><strong>Begining of Report for <?php echo htmlspecialchars($prepared_by); ?></strong></div><br>

<?php

/* ---------------- SERVICE SETUP ---------------- */

$part = '';
$header = '';
$code = '';

if ($Services == 'All Services') {
	$header = 'All Services';
	$code = 'n';
} elseif ($Services == 'Nursing Services') {
	$part = "serv_group='Nursing Services' AND ";
	$header = 'Nursing Services';
	$code = 'n';
} elseif ($Services == 'Investigations') {
	$part = "(serv_group='Radiology' OR serv_group='Laboratory') AND ";
	$header = 'Investigations';
	$code = 'i';
} elseif ($Services == 'Pharmacy') {
	$part = "(serv_group='Pharmacy' OR serv_group='Nursing Consumable') AND ";
	$header = 'Medications/Consumable';
	$code = 'p';
} elseif ($Services == 'Medical Services') {
	$part = "serv_group='Medical Services' AND ";
	$header = 'Surgeries/Procedures';
	$code = 's';
} elseif ($Services == 'Other Services') {
	$part = "serv_group='Other Services' AND ";
	$header = 'Other Services';
	$code = 'o';
} elseif ($Services == 'Consultation') {
	$part = "serv_group='Consultation' AND ";
	$header = 'Consultation';
	$code = 'c';
}

/* ---------------- QUERY ---------------- */

if ($Services == 'Consultation') {

	$part = "serv_group='Consultation' AND ";
	$header = 'Consultation';
	$code = 'c';

	$sql = "
        SELECT p.*
        FROM patient_ap_services p
        INNER JOIN apptm a ON a.appt_no = p.app_no
        WHERE {$part}
              paystatus = '1'
          AND pay_mode != 'writeoff'
          AND doctor_id = :doctor_id
          AND DATE(transact_date) BETWEEN :start AND :end
        ORDER BY serv_group, cat_type, transact_date
    ";

	$stmt = $db->prepare($sql);
	$stmt->execute([
		':doctor_id' => $doctor_id,
		':start' => $start,
		':end' => $end
	]);
} else {

	$sql = "
    SELECT *
    FROM patient_ap_services
    WHERE {$part}
          (created_by = :doctor_id
           OR prepared_by = :prepared_by1
           OR dsp_by = :prepared_by2)
      AND paystatus = '1'
      AND pay_mode != 'writeoff'
      AND DATE(transact_date) BETWEEN :start AND :end
    ORDER BY serv_group, cat_type, transact_date
";

	$stmt = $db->prepare($sql);
	$stmt->execute([
		':doctor_id'     => $doctor_id,
		':prepared_by1'  => $prepared_by,
		':prepared_by2'  => $prepared_by,
		':start'         => $start,
		':end'           => $end
	]);
}

/* ---------------- FETCH ONCE (FIX) ---------------- */

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {

	/* ---------------- DETAILS REPORT ---------------- */

	if ($report == 'details') {
?>
		<h4><?php echo $header; ?></h4>
		<table class="table table-striped table-bordered" style="font-size:15px;">
			<thead>
				<tr>
					<th>#</th>
					<th>Hospital/No</th>
					<th>Description</th>
					<th width="10%">Qty</th>
					<th width="10%">Price</th>
					<th width="10%">Paid</th>
					<th width="10%">Insured</th>
					<th width="15%">Status</th>
					<th width="12%">Date</th>
				</tr>
			</thead>
			<tbody>

				<?php
				$n = 1;
				foreach ($rows as $rwx) {

					$claim_amt = (float)$rwx['claim_amt'];
					$pay       = (float)$rwx['pay'];

					$T_claim += $claim_amt;
					$T_pay   += $pay;

					$PClaim += $claim_amt;
					$PPay   += $pay;

					$GrandPClaim += $claim_amt;
					$GrandPPay   += $pay;
				?>
					<tr>
						<td><?php echo $n++; ?></td>
						<td><?php echo $rwx['hospital_no']; ?></td>
						<td><?php echo $rwx['item_services']; ?></td>
						<td><?php echo $rwx['qty']; ?></td>
						<td><?php echo $rwx['hosp_price']; ?></td>
						<td align="right"><?php echo number_format($pay); ?></td>
						<td align="right"><?php echo number_format($claim_amt); ?></td>
						<td><strong><?php echo ($rwx['drug_status'] == '1') ? 'Delivered' : 'Paid'; ?></strong></td>
						<td><?php echo date('d M, y', strtotime($rwx['transact_date'])); ?></td>
					</tr>
				<?php } ?>

				<tr>
					<td colspan="5"><strong>Total</strong></td>
					<td align="right"><strong><?php echo number_format($T_pay); ?></strong></td>
					<td align="right"><strong><?php echo number_format($T_claim); ?></strong></td>
					<td colspan="2"></td>
				</tr>

			</tbody>
		</table>

<?php
	}
	/* ---------------- SUMMARY REPORT ---------------- */ else {

		foreach ($rows as $rwx) {
			$T_claim += $rwx['claim_amt'];
			$T_pay   += $rwx['pay'];

			$PClaim += $rwx['claim_amt'];
			$PPay   += $rwx['pay'];

			$GrandPClaim += $rwx['claim_amt'];
			$GrandPPay   += $rwx['pay'];
		}

		$total_amount = $T_claim + $T_pay;

		if ($total_amount > 0) {

			$insert = $db->prepare("
                INSERT INTO temp_doc_income
                (doctor_name, services_type, total_amtpaid, total_insured, total_amount, staffname)
                VALUES (:doctor, :service, :paid, :insured, :total, :staff)
            ");

			$insert->execute([
				':doctor' => $prepared_by,
				':service' => $header,
				':paid' => $T_pay,
				':insured' => $T_claim,
				':total' => $total_amount,
				':staff' => $_SESSION['fullname']
			]);
		}

		$T_claim = 0;
		$T_pay   = 0;
	}
}

/* ---------------- FOOTER ---------------- */

echo '<div align="center"><strong>Total Insured Bills:</strong> ' . number_format($PClaim) .
	' | <strong>Total Amount Paid:</strong> ' . number_format($PPay) . '</div>';

echo '<div align="center"><small><strong> End of Report for ' .
	htmlspecialchars($prepared_by) . '</strong></small></div><hr>';

$PClaim = 0;
$PPay   = 0;
