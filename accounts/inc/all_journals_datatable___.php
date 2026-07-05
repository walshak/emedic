<?php
include_once '../../Connections/Conn.php';
include_once 'functions.php';

// Error logging function
function logError($message)
{
    error_log($message . PHP_EOL); // Adjust the log path
}

try {
    // Fetch the active fiscal year
    $current_fiscal_year = get_active_year();
    $current_fiscal_year_start = $current_fiscal_year['begin'];
    $current_fiscal_year_end = $current_fiscal_year['end'];

    // Get POST data with fallback to avoid undefined index notices (PHP 5.6 compatible way)
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $prep = isset($_POST['prep']) ? $_POST['prep'] : '';
    $gl_acct = isset($_POST['gl_acct']) ? $_POST['gl_acct'] : '';
    $DR_CR = isset($_POST['DR_CR']) ? $_POST['DR_CR'] : '';
    $account_payable = isset($_POST['account_payable']) ? $_POST['account_payable'] : '';
    $hmo_dues = isset($_POST['hmo_dues']) ? $_POST['hmo_dues'] : '';
    $account_recievables = isset($_POST['account_recievables']) ? $_POST['account_recievables'] : '';
    $patient_deposit = isset($_POST['patient_deposit']) ? $_POST['patient_deposit'] : '';
    $search_value = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

    // Define table columns
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

    // Prepare the opening balance query with filters
    $openingSql = "SELECT dr_amt, cr_amt 
                   FROM chart_ledger 
                   WHERE DATE(date_entry2) >= ? AND DATE(date_entry2) < ?";
    $openingParams = array($current_fiscal_year_start, $start_date);

    // Add conditions dynamically
    if (!empty($prep)) {
        $openingSql .= " AND prepared_by = ?";
        $openingParams[] = $prep;
    }
    if (!empty($gl_acct)) {
        $openingSql .= " AND account_no = ?";
        $openingParams[] = $gl_acct;
    }
    if (!empty($DR_CR) && ($DR_CR == 'CREDIT' || $DR_CR == 'DEBIT')) {
        $openingSql .= " AND transc_type = ?";
        $openingParams[] = $DR_CR;
    }
    if (!empty($account_payable)) {
        $openingSql .= " AND insurance_no = ? AND account_no = 2124";
        $openingParams[] = $account_payable;
    }
    if (!empty($hmo_dues)) {
        $openingSql .= " AND insurance_no = ? AND account_no = 1114";
        $openingParams[] = $hmo_dues;
    }
    if (!empty($account_recievables)) {
        $openingSql .= " AND hospital_no = ? AND account_no = 1114";
        $openingParams[] = $account_recievables;
    }
    if (!empty($patient_deposit)) {
        $openingSql .= " AND hospital_no = ? AND account_no = 2121";
        $openingParams[] = $patient_deposit;
    }

    // Execute the opening balance query
    $openingStmt = $db->prepare($openingSql);
    $openingStmt->execute($openingParams);

    // Fetch opening balances one by one
    $openingDR = 0;
    $openingCR = 0;
    while ($entry = $openingStmt->fetch(PDO::FETCH_ASSOC)) {
        $openingDR += isset($entry['dr_amt']) ? $entry['dr_amt'] : 0;
        $openingCR += isset($entry['cr_amt']) ? $entry['cr_amt'] : 0;
    }
    $openingBalance = $openingDR - $openingCR;

    // Prepare the main query
    $sql = "SELECT lg_ref_no as ref, prepared_by, date_entry2, account_no, item_services, dr_amt, cr_amt, transc_type, insurance_no, hospital_no, can_delete, sn
            FROM chart_ledger 
            WHERE DATE(date_entry2) BETWEEN ? AND ?";
    $params = array($start_date, $end_date);

    if (!empty($prep)) {
        $sql .= " AND prepared_by = ?";
        $params[] = $prep;
    }
    if (!empty($gl_acct)) {
        $sql .= " AND account_no = ?";
        $params[] = $gl_acct;
    }
    if (!empty($DR_CR)) {
        $sql .= " AND transc_type = ?";
        $params[] = $DR_CR;
    }
    if (!empty($account_payable)) {
        $sql .= " AND insurance_no = ? AND account_no = 2124";
        $params[] = $account_payable;
    }
    if (!empty($hmo_dues)) {
        $sql .= " AND insurance_no = ? AND account_no = 1114";
        $params[] = $hmo_dues;
    }
    if (!empty($account_recievables)) {
        $sql .= " AND hospital_no = ? AND account_no = 1114";
        $params[] = $account_recievables;
    }
    if (!empty($patient_deposit)) {
        $sql .= " AND hospital_no = ? AND account_no = 2121";
        $params[] = $patient_deposit;
    }

    // Search filter
    if (!empty($search_value)) {
        $searchValue = "%" . $search_value . "%";
        $sql .= " AND (lg_ref_no LIKE ? OR prepared_by LIKE ? OR date_entry2 LIKE ? OR item_services LIKE ?)";
        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
    }

    // Count total records without pagination
    $stmt2 = $db->prepare($sql);
    $stmt2->execute($params);
    $totalRecords = $stmt2->rowCount();

    // Pagination
    $displayLength = intval(isset($_POST['length']) ? $_POST['length'] : 10);
    $displayStart = intval(isset($_POST['start']) ? $_POST['start'] : 0);
    if ($displayLength > $totalRecords - $displayStart) {
        $displayLength = $totalRecords - $displayStart;
    }

    // Order by and limit
    $sql .= " ORDER BY " . $columns[$_POST['order'][0]['column']] . " " . $_POST['order'][0]['dir'];
    $sql .= " LIMIT " . $displayStart . ", " . $displayLength;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Fetch data one by one
    $data = array();
    $sn = $displayStart + 1;
    $runningDR = $openingDR;
    $runningCR = $openingCR;
    $runningBalance = $openingBalance;
    $totalDR = $openingDR;
    $totalCR = $openingCR;

    while ($entry = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($entry['dr_amt'] > 0) {
            $runningDR += $entry['dr_amt'];
            $totalDR += $entry['dr_amt'];
        }
        if ($entry['cr_amt'] > 0) {
            $runningCR += $entry['cr_amt'];
            $totalCR += $entry['cr_amt'];
        }

        $runningBalance = $runningDR - $runningCR;

        $ed_btn = '<a href="?ref=' . $entry['ref'] . '"><i class="fa fa-edit"></i></a>';
        $del_btn = $entry['can_delete'] == 1 ? '<br><br><a href="?del=' . $entry['ref'] . '" class="text-danger" onclick="return confirm(\'Are you sure you wish to delete this entry?\')"><i class="fa fa-trash"></i></a>' : '';

        $data[] = array(
            'sn' => $sn,
            'account' => $entry['account_no'] == 2124 ? getInsuranceName($entry['insurance_no']) : getAccountName($entry['account_no']),
            'description' => $entry['item_services'],
            'dr' => $entry['transc_type'] == 'DEBIT' ? number_format($entry['dr_amt'], 2) : 0,
            'cr' => $entry['transc_type'] == 'CREDIT' ? number_format($entry['cr_amt'], 2) : 0,
            'balance' => number_format($runningBalance, 2),
            'prepared_by' => $entry['prepared_by'],
            'date_entry2' => date('d M, Y', strtotime($entry['date_entry2'])),
            'actions' => $ed_btn . $del_btn
        );

        $sn++;
    }

    $totalDR = $totalDR - $openingDR;
    $totalCR = $totalCR - $openingCR;
    $runningBalance = $runningBalance - $openingBalance;

    // Output data
    echo json_encode(array(
        'draw' => intval($_POST['draw']),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data,
        'totals' => array(
            'totalDR' => number_format($totalDR, 2),
            'totalCR' => number_format($totalCR, 2),
            'totalBalance' => number_format($runningBalance, 2)
        ),
        'opening_totals' => array(
            'openingDR' => number_format($openingDR, 2),
            'openingCR' => number_format($openingCR, 2),
            'openingBalance' => number_format($openingBalance, 2)
        ),

        'closing_totals' => array(
            'closingDR' => number_format($openingDR + $totalDR, 2),
            'closingCR' => number_format($openingCR + $totalCR, 2),
            'closingBalance' => number_format($openingBalance + $runningBalance, 2)
        )
    ));
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    echo json_encode(array("error" => "An error occurred while fetching data."));
}
