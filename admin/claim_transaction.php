<?php
session_start();

$Department = isset($_POST['Department']) ? trim($_POST['Department']) : '';
$validation_status = isset($_POST['validation_status2']) ? intval($_POST['validation_status2']) : 0;
$start = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to = isset($_POST['to_date']) ? $_POST['to_date'] : '';
$hmo_type = isset($_POST['hmo_type']) ? trim($_POST['hmo_type']) : '';
$hmo_nhis = isset($_POST['hmo_nhis']) ? $_POST['hmo_nhis'] : [];

$dept_s = $Department ? "AND ap.dept_id = :dept_id" : "";

// Clean slate
$db->exec("TRUNCATE TABLE temp_claim");

echo '<div style="text-align:center; font-size:20px;">';
echo 'HMO/Corporate Reports: ' . htmlspecialchars($hmo_type) . '<br>';
echo 'Between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to)) . '<br>';
echo $validation_status == 1 ? '<strong>Posted Claims</strong>' : 'Not Posted Claims';
echo '</div><hr>';

// Aggregate totals
$totalClaims = $totalInterests = $totalPayments = 0;

if (!empty($hmo_nhis)) {
	$db->beginTransaction();

	$stmtInsertFull = $db->prepare("INSERT INTO temp_claim(a1, a2, a3, a4, a5, a6, a7) VALUES (:a1, :a2, :a3, :a4, :a5, :a6, :a7)");

	foreach ($hmo_nhis as $hmo_nhis_no) {
		list($hmo_no, $hmo_name) = explode("__", $hmo_nhis_no);

		echo "<h3>" . htmlspecialchars($hmo_name) . "</h3>";

		$sql = "
            SELECT enl.hospital_no, ap.* 
            FROM enrollee AS enl
            INNER JOIN patient_ap_services AS ap ON ap.hospital_no = enl.hospital_no
            WHERE enl.hmo_no = :hmo_no AND enl.insurance = :insurance
            AND ap.process_claim = :validation_status
            AND DATE(ap.date_entry) BETWEEN :start_date AND :end_date
            $dept_s
        ";

		$stmt = $db->prepare($sql);
		$stmt->bindValue(':hmo_no', $hmo_no);
		$stmt->bindValue(':insurance', $hmo_type);
		$stmt->bindValue(':validation_status', $validation_status);
		$stmt->bindValue(':start_date', $start);
		$stmt->bindValue(':end_date', $to);
		if ($Department) {
			$stmt->bindValue(':dept_id', $Department);
		}
		$stmt->execute();

		$claimHmo = $interestHmo = $payHmo = 0;

		if ($stmt->rowCount() > 0) {
			echo '<table class="table table-bordered" style="font-size:13px;"><thead><tr>
                    <th>Hospital #</th><th>Item/Service</th><th>Qty</th><th>Claim</th>
                    <th>Interest</th><th>Entered/Disp by</th><th>Date</th>
                  </tr></thead><tbody>';

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$claim_amt = floatval($row['claim_amt']);
				$interest_amt = floatval($row['interest']);
				$pay_amt = floatval($row['pay']);

				$prepared_by = htmlspecialchars($row['prepared_by']) . ' / ' . htmlspecialchars($row['dsp_by']);
				$date_entry = date('d, M Y h:i', strtotime($row['date_entry']));

				echo "<tr>
                        <td>{$row['hospital_no']}</td>
                        <td>{$row['item_services']}</td>
                        <td>{$row['qty']}</td>
                        <td>" . number_format($claim_amt, 2) . "</td>
                        <td>" . number_format($interest_amt, 2) . "</td>
                        <td>{$prepared_by}</td>
                        <td>{$date_entry}</td>
                      </tr>";

				$stmtInsertFull->execute([
					':a1' => $row['hospital_no'],
					':a2' => $row['item_services'],
					':a3' => $row['qty'],
					':a4' => $claim_amt,
					':a5' => $interest_amt,
					':a6' => $prepared_by,
					':a7' => $date_entry
				]);

				$claimHmo += $claim_amt;
				$interestHmo += $interest_amt;
				$payHmo += $pay_amt;
			}

			echo "</tbody></table>";

			echo "<h3>Total Claim: " . number_format($claimHmo, 2) . " | Interest: " . number_format($interestHmo, 2) . "</h3><br>";

			$totalClaims += $claimHmo;
			$totalInterests += $interestHmo;
			$totalPayments += $payHmo;
		}
	}

	if ($totalClaims > 0) {
		$db->exec("INSERT INTO temp_claim (a4, a5) VALUES (" . floatval($totalClaims) . ", " . floatval($totalInterests) . ")");
		echo "<hr><h2>Grand Total (Claim): " . number_format($totalClaims, 2) . "</h2>";
		echo "<h2>Grand Total (Interest): " . number_format($totalInterests, 2) . "</h2>";
	}

	$db->commit();
} else {
	echo "<div style='color:red; text-align:center;'>Invalid Selection</div>";
}

?>
<!-- Action Buttons -->
<form action="download_code.php" method="POST" id="subject" name="subject">
	<div class="form_sep">
		<div class="pull-left" style="margin-right:100px;">
			<a href="index.php?cptclaim" style="font-size:20px;" class="btn btn-danger btn-large">Close</a>
		</div>

		<div class="pull-right" style="margin-right:100px;">
			<button class="btn btn-success btn-large" type="button" onclick="Clickheretoprint()"><i class="icon-print"></i> Print</button>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<button class="btn btn-primary btn-large" type="submit" name="apply_rpt">Download</button>
		</div>
	</div>
</form>