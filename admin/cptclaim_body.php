<?php
$grand_total_cliam_validated = 0;
$grand_total_cliam_nonvalidated = 0;

if ($target == 'last_month' || $target == 'month/yr') {
	$part = explode("/", $cptclaim);
	$hmo_no = $target == 'last_month' ? $part[0] : $insurance_type;
	$last_month = $target == 'last_month' ? $part[1] : $month;
	$last_yr = $target == 'last_month' ? $part[2] : $year;

	$last_date = date('t', strtotime("$last_yr-$last_month-01"));
	$dates = "AND en.hmo_no='$hmo_no' AND MONTH(ap.date_entry)='$last_month' AND YEAR(ap.date_entry)='$last_yr'";
	$dates2 = "AND MONTH(date_entry)='$last_month' AND YEAR(date_entry)='$last_yr'";
	$target_date = "$last_yr-$last_month-01/$last_yr-$last_month-$last_date/$hmo_no/cpt_$hmo_no";

	$enter_recovered_claim = 1;
	$print_all = 1;
} elseif ($target == 'dates') {
	$start = $_POST['start'];
	$end = $_POST['end'];
	list($hmo_no) = explode("___", $_POST['insurance_tbl']);

	$dates = "AND en.hmo_no = '$hmo_no' AND DATE(ap.date_entry) BETWEEN '$start' AND '$end'";
	$dates2 = "AND DATE(date_entry) BETWEEN '$start' AND '$end'";
	$target_date = "$start/$end/dates/cpt_dates";
} elseif ($target == 'daily') {
	$setdate = date("Y-m-d");
	$dates = "AND DATE(ap.date_entry)='$setdate'";
	$dates2 = "AND DATE(date_entry)='$setdate'";
	$target_date = "$setdate/$setdate/daily/cpt_daily";
}

// Fetch insurance info if available
if (!empty($hmo_no)) {
	$insurance_query = $db->prepare("SELECT insurance_name FROM insurance_tbl WHERE insurance_no = ?");
	$insurance_query->execute([$hmo_no]);
	$insurance_row = $insurance_query->fetch(PDO::FETCH_ASSOC);
	if ($insurance_row) {
		$insurance_name = $insurance_row['insurance_name'];
		echo "<div style=\"font:bold 18px 'Arial';\">$insurance_name</div><hr>";
	}
}

// Fetch validated and non-validated claims in grouped form
function fetchClaims($db, $dates, $dates2, $process_claim)
{
	$query = "
        SELECT en.surname, en.fname, en.hospital_no, SUM(ap.claim_amt) AS total_claim
        FROM patient_ap_services AS ap
        INNER JOIN enrollee AS en ON ap.hospital_no = en.hospital_no
        WHERE ap.paystatus = '1' AND ap.process_claim = ? AND ap.claim_amt > 0 $dates
        GROUP BY en.hospital_no
    ";
	$stmt = $db->prepare($query);
	$stmt->execute([$process_claim]);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// VALIDATED CLAIMS
$validated_claims = fetchClaims($db, $dates, $dates2, 1);

if (count($validated_claims) > 0) {
	echo "<h2>" . htmlspecialchars($insurance_name) . "</h2>";
	echo "<h3 style=\"color: #00F\">Validated Claims</h3><br>";
	echo "<table class=\"table table-striped table-bordered table-hover dataTables-example\">
            <thead>
                <tr>
                    <th>No</th>
                    <th>EMR #</th>
                    <th>Surname</th>
                    <th>First Name</th>
                    <th>Insured Claim</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>";

	$n = 1;
	foreach ($validated_claims as $claim) {
		$grand_total_cliam_validated += $claim['total_claim'];
		echo "<tr>
                <td>$n</td>
                <td>{$claim['hospital_no']}</td>
                <td>{$claim['surname']}</td>
                <td>{$claim['fname']}</td>
                <td>" . number_format($claim['total_claim'], 2, '.', ',') . "</td>
                <td><a href=\"index.php?claims={$claim['hospital_no']}&dates=$target_date\" target=\"_blank\">View Claims</a></td>
            </tr>";
		$n++;
	}
	echo "</tbody></table><hr>";
	echo "<strong>Total Claims:</strong> <strong style=\"font-size:18px;\">" . number_format($grand_total_cliam_validated, 2, '.', ',') . "</strong><hr>";
} else {
	echo "<h3 style=\"color:#C03\">No Validated Claims</h3><hr>";
}

// NON-VALIDATED CLAIMS
$nonvalidated_claims = fetchClaims($db, $dates, $dates2, 0);

if (count($nonvalidated_claims) > 0) {
	echo "<h3 style=\"color: #00F\">Non Validated Claims</h3><br>";
	echo "<table class=\"table table-striped table-bordered table-hover dataTables-example\">
            <thead>
                <tr>
                    <th>No</th>
                    <th>EMR #</th>
                    <th>Surname</th>
                    <th>First Name</th>
                    <th>Insured Claim</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>";

	$n = 1;
	foreach ($nonvalidated_claims as $claim) {
		$grand_total_cliam_nonvalidated += $claim['total_claim'];
		echo "<tr>
                <td>$n</td>
                <td>{$claim['hospital_no']}</td>
                <td>{$claim['surname']}</td>
                <td>{$claim['fname']}</td>
                <td>" . number_format($claim['total_claim'], 2, '.', ',') . "</td>
                <td><a href=\"index.php?claims={$claim['hospital_no']}&dates=$target_date\" target=\"_blank\">View Claims</a></td>
            </tr>";
		$n++;
	}
	echo "</tbody></table><hr>";
	echo "<strong>Total Claims:</strong> <strong style=\"font-size:18px;\">" . number_format($grand_total_cliam_nonvalidated, 2, '.', ',') . "</strong><br>";
} else {
	echo "<h3 style=\"color:#C03\">All Claims Validated</h3><hr>";

	if (!empty($enter_recovered_claim)) {
		echo "<strong>Enter Claim <br>Settlement for the month: <br>{$last_month} / {$last_yr}</strong><br>";
		echo "<input type=\"button\" name=\"add_insur\" value=\"Enter Claim Settlement\" data-target=\"#modal\" id=\"{$hmo_no}/{$last_month}/{$last_yr}/{$grand_total_cliam_validated}/" . count($validated_claims) . "\" class=\"btn btn-primary btn-sm claim_amount\" />";
	}

	if (!empty($print_all)) {
		echo "<form action=\"index.php?cptclaim\" method=\"POST\">
                <input type=\"hidden\" name=\"hmo_no\" value=\"$hmo_no\">
                <input type=\"hidden\" name=\"month\" value=\"$last_month\">
                <input type=\"hidden\" name=\"year\" value=\"$last_yr\">
                <input type=\"hidden\" name=\"dates\" value=\"$dates\">
                <input type=\"hidden\" name=\"dates2\" value=\"$dates2\">
                <button class=\"btn btn-info btn-sm\" type=\"submit\" name=\"print_report\"><i class=\"fa fa-print\"></i> &nbsp;Print</button>
              </form>";
	}
}

if ($target != 'daily') {
	echo "<hr><a href=\"index.php?cptclaim\"><button class=\"btn btn-danger btn-sm\"><i class=\"icon-print\"></i> Close</button></a>";
}
