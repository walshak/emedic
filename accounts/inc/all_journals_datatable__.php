<?php
include_once '../../Connections/Conn.php';
include_once 'functions.php';

$current_fiscal_year = get_active_year();

$current_fiscal_year_start = $current_fiscal_year['begin'];
$current_fiscal_year_end = $current_fiscal_year['end'];

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$prep = $_POST['prep'];
$gl_acct = $_POST['gl_acct'];
$DR_CR = $_POST['DR_CR'];
$account_payable = $_POST['account_payable'];
$hmo_dues = $_POST['hmo_dues'];
$account_recievables = $_POST['account_recievables'];
$patient_deposit = $_POST['patient_deposit'];
$search_value = $_POST['search']['value'];

$columns = array(
    0 => 'sn',
    1 => 'account',
    2 => 'description',
    3 => 'dr',
    4 => 'cr',
    5 => 'balance',
    6 => 'prepared_by',
    7 => 'date_entry2',
    8 => 'actions'
);

// Fetch all transactions within the fiscal year
$fiscalSql = "SELECT dr_amt, cr_amt 
              FROM chart_ledger 
              WHERE DATE(date_entry2) BETWEEN ? AND ?";
$fiscalParams = array($current_fiscal_year_start, $current_fiscal_year_end);

$fiscalStmt = $db->prepare($fiscalSql);
$fiscalStmt->execute($fiscalParams);
$fiscalResults = $fiscalStmt->fetchAll(PDO::FETCH_ASSOC);

$fiscalDR = 0;
$fiscalCR = 0;
foreach ($fiscalResults as $entry) {
    if ($entry['dr_amt'] > 0) {
        $fiscalDR += $entry['dr_amt'];
    }
    if ($entry['cr_amt'] > 0) {
        $fiscalCR += $entry['cr_amt'];
    }
}

$fiscalBalance = $fiscalCR - $fiscalDR;

// Prepare the main query with filters
$sql = "SELECT lg_ref_no as ref, prepared_by, date_entry2, account_no, item_services, dr_amt, cr_amt, transc_type, insurance_no, hospital_no, can_delete, sn
        FROM chart_ledger 
        WHERE DATE(date_entry2) BETWEEN ? AND ?";

$params = array($start_date, $end_date);

if ($prep != '' && $gl_acct == '') {
    $sql .= " AND prepared_by = ?";
    $params[] = $prep;
} else if ($gl_acct != '' && $prep == '') {
    $sql .= " AND account_no = ?";
    $params[] = $gl_acct;
} elseif ($prep != '' && $gl_acct != '') {
    $sql .= " AND account_no = ? AND prepared_by = ?";
    $params[] = $gl_acct;
    $params[] = $prep;
}

if (isset($DR_CR) && ($DR_CR == 'CREDIT' || $DR_CR == 'DEBIT')) {
    $sql .= " AND transc_type = ?";
    $params[] = $DR_CR;
}

if ($account_payable != '') {
    $sql .= " AND insurance_no = ? and account_no=2124";
    $params[] = $account_payable;
}

if ($hmo_dues != '') {
    $sql .= " AND insurance_no = ? and account_no=1114";
    $params[] = $hmo_dues;
}

if ($account_recievables != '') {
    $sql .= " AND hospital_no = ? and account_no=1114";
    $params[] = $account_recievables;
}

if ($patient_deposit != '') {
    $sql .= " AND hospital_no = ? and account_no=2121";
    $params[] = $patient_deposit;
}

if (!empty($search_value)) {
    $sql .= " AND (lg_ref_no LIKE ? OR prepared_by LIKE ? OR date_entry2 LIKE ? OR item_services LIKE ?)";
    $searchValue = "%" . $search_value . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

// Count total records without pagination
$totalSql = $sql;
$stmt2 = $db->prepare($totalSql);
$stmt2->execute($params);
$totalRecords = $stmt2->rowCount();

$totalDR = 0;
$totalCR = 0;
$totalBalance = 0; // Adjust this calculation based on your balance logic

$displayLength = intval($_POST['length']);
$displayStart = intval($_POST['start']);

if ($displayLength > $totalRecords - $displayStart) {
    $displayLength = $totalRecords - $displayStart;
}

// Add ORDER BY and LIMIT for pagination
$sql .= " ORDER BY " . $columns[$_POST['order'][0]['column']] . " " . $_POST['order'][0]['dir'];
$sql .= " LIMIT " . $displayStart . ", " . $displayLength;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$all_ref_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array();
$sn = $displayStart + 1;
$runningDR = 0;
$runningCR = 0;
$runningBalance = $fiscalBalance;

foreach ($all_ref_results as $entry) {

    //lets get the totals for dr and cr up until the current entry, not minding any filters
    $r = $db->prepare("SELECT SUM(dr_amt) as running_dr FROM chart_ledger where sn <= :entry_sn");
    $runningDR = $r->execute([$entry['sn']]);
    $runningDR = $r->fetch(PDO::FETCH_ASSOC)['running_dr'];


    $r = $db->prepare("SELECT SUM(cr_amt) as running_cr FROM chart_ledger where sn <= :entry_sn");
    $runningCR = $r->execute([$entry['sn']]);
    $runningCR = $r->fetch(PDO::FETCH_ASSOC)['running_cr'];

    // var_dump($runningCR, $runningDR, $entry['sn']);
    // die();


    $runningBalance = $runningCR - $runningDR;

    $ed_btn = '<a href="?ref=' . $entry['ref'] . '"><i class="fa fa-edit"></i></a>';
    if ($entry['can_delete'] == 1) {
        $del_btn = '<br><br><a href="?del=' . $entry['ref'] . '" class="text-danger" onclick="return confirm(\'Are you sure you wish to delete this entry and its associated entries?, this action cannot be undone.!!!\')"><i class="fa fa-trash"></i></a>';
    } else {
        $del_btn = '';
    }

    $row = array(
        'sn' => $sn,
        'account' => $entry['account_no'] == 2124 ? getInsuranceName($entry['insurance_no']) : getAccountName($entry['account_no']),
        'description' => $entry['item_services'],
        'dr' => $entry['transc_type'] == 'DEBIT' ? number_format($entry['dr_amt'], 2) : 0,
        'cr' => $entry['transc_type'] == 'CREDIT' ? number_format($entry['cr_amt'], 2) : 0,
        'balance' => (number_format($runningBalance, 2)),
        'prepared_by' => $entry['prepared_by'],
        'date_entry2' => date('d M, Y', strtotime($entry['date_entry2'])),
        'actions' => $ed_btn . $del_btn
    );

    $data[] = $row;
    $sn++;
}
$totalDR = $runningDR;
$totalCR = $runningCR;
$totalBalance = $runningCR - $runningDR;

$output = array(
    'draw' => intval($_POST['draw']),
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalRecords,
    'data' => $data,
    'totals' => array(
        'totalDR' => number_format($totalDR, 2),
        'totalCR' => number_format($totalCR, 2),
        'totalBalance' => (number_format($totalBalance, 2))
    )
);

echo json_encode($output);
