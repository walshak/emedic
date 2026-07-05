<?php
$stmt = $db->prepare("SELECT * FROM chart_ledger 
                      WHERE lg_ref_no = :lg_ref_no 
                      AND sale_sn = :sale_sn");
$stmt->execute(array(
    ':lg_ref_no' => $lg_ref_no,
    ':sale_sn'   => $sale_sn
));
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($entries) == 0) {
    throw new Exception("No entries found to reverse.");
}

$insert = $db->prepare("
    INSERT INTO chart_ledger (
        app_no, hospital_no, insurance_no, ref_value, sale_sn, item_services, bank_name,
        transc_type, dr_amt, cr_amt, bal, prepared_by, date_entry, date_entry2,
        auth_code, account_no, lg_ref_no, fiscal_year
    ) VALUES (
        :app_no, :hospital_no, :insurance_no, :ref_value, :sale_sn, :item_services, :bank_name,
        :transc_type, :dr_amt, :cr_amt, :bal, :prepared_by, :date_entry, :date_entry2,
        :auth_code, :account_no, :lg_ref_no, :fiscal_year
    )
");

$today       = date("Y-m-d");
$datetime    = date("Y-m-d H:i:s");
$auth_code   = null;
$prepared_by = $_SESSION['fullname'];

$success_count = 0; 
$fail_count = 0;

foreach ($entries as $row) {

    // Reverse debit <-> credit
    if (strtoupper($row['transc_type']) == 'DEBIT') {
        $transc_type = 'CREDIT';
        $dr_amt = 0;
        $cr_amt = $row['dr_amt'];
    } else {
        $transc_type = 'DEBIT';
        $dr_amt = $row['cr_amt'];
        $cr_amt = 0;
    }

    $done = $insert->execute(array(
        ':app_no'        => $row['app_no'],
        ':hospital_no'   => $row['hospital_no'],
        ':insurance_no'  => $row['insurance_no'],
        ':ref_value'     => null,
        ':sale_sn'       => $row['sale_sn'],
        ':item_services' => trim($row['item_services']) . " (REVERSAL)",
        ':bank_name'     => null,
        ':transc_type'   => $transc_type,
        ':dr_amt'        => $dr_amt,
        ':cr_amt'        => $cr_amt,
        ':bal'           => null,
        ':prepared_by'   => $prepared_by,
        ':date_entry'    => $datetime,
        ':date_entry2'   => $today,
        ':auth_code'     => $auth_code,
        ':account_no'    => $row['account_no'],
        ':lg_ref_no'     => $row['lg_ref_no'],
        ':fiscal_year'   => $row['fiscal_year']
    ));

    if ($done) {
        $success_count++;
    } else {
        $fail_count++;
    }
}

// Determine final status (no echo)
if ($success_count > 0 && $fail_count == 0) {
    $status = "success";   // all reversed
} elseif ($success_count > 0 && $fail_count > 0) {
    $status = "partial";   // some reversed, some failed
} else {
    $status = "failed";    // nothing saved
}

// Now you can use $status anywhere below
// e.g.:
// if ($status == "success") { ... }
// else if ($status == "failed") { ... }




$save_done;