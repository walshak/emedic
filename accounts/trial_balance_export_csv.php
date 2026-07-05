<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: trial_balance.php');
    exit;
}

$start_date = $_GET['start'];
$end_date = $_GET['end'];
$show_crdr = isset($_GET['show_crdr']) && $_GET['show_crdr'] == '1';

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    header('Location: trial_balance.php');
    exit;
}

// Set CSV headers for download
$filename = 'Trial_Balance_' . $start_date . '_to_' . $end_date . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for proper UTF-8 support in Excel
fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

// Get hospital information
$hospital_name = isset($_SESSION['h_name']) ? $_SESSION['h_name'] : 'Hospital';

// Write header information
fputcsv($output, [$hospital_name]);
fputcsv($output, ['Trial Balance']);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($end_date))]);
fputcsv($output, []); // Empty row

// Write column headers
if ($show_crdr) {
    fputcsv($output, ['Level', 'Account Code', 'Account Name', 'Debit', 'Credit', 'Balance']);
} else {
    fputcsv($output, ['Level', 'Account Code', 'Account Name', 'Balance']);
}

try {
    // Modified query: Get all raw transaction data without summation
    $sql = "
    SELECT 
        cl.class_name,
        cl.cid as class_id,
        cg.name as group_name,
        cg.id as group_id,
        ca.account_code,
        ca.account_name,
        ch.transc_type,
        ch.dr_amt,
        ch.cr_amt
    FROM chart_class cl
    LEFT JOIN chart_groups cg ON cg.class_id = cl.cid
    LEFT JOIN chart_accounts ca ON ca.account_group = cg.id
    LEFT JOIN chart_ledger ch ON ch.account_no = ca.account_code 
        AND DATE(ch.date_entry2) BETWEEN ? AND ?
    WHERE ca.account_code IS NOT NULL
    AND ch.patient_stt_status!=1
    ORDER BY cl.cid, cg.id, ca.account_code
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // PHP arrays to store account summations
    $account_data = [];
    $class_order = [];
    $group_order = [];

    // Process raw data and calculate summations in PHP
    foreach ($results as $row) {
        $account_code = $row['account_code'];

        // Initialize account if not exists
        if (!isset($account_data[$account_code])) {
            $account_data[$account_code] = [
                'class_name' => $row['class_name'],
                'class_id' => $row['class_id'],
                'group_name' => $row['group_name'],
                'group_id' => $row['group_id'],
                'account_name' => $row['account_name'],
                'total_debit' => 0,
                'total_credit' => 0
            ];

            // Store ordering information
            if (!in_array($row['class_id'], $class_order)) {
                $class_order[] = $row['class_id'];
            }
            if (!in_array($row['group_id'], $group_order)) {
                $group_order[] = $row['group_id'];
            }
        }

        // Sum up debits and credits in PHP - Sum all debits and credits based on transaction type
        if ($row['transc_type'] == 'DEBIT') {
            $account_data[$account_code]['total_debit'] += (floatval($row['dr_amt']) + floatval($row['cr_amt']));
        } elseif ($row['transc_type'] == 'CREDIT') {
            $account_data[$account_code]['total_credit'] += (floatval($row['dr_amt']) + floatval($row['cr_amt']));
        }
    }

    // Filter out accounts with no activity
    $account_data = array_filter($account_data, function ($account) {
        return $account['total_debit'] > 0 || $account['total_credit'] > 0;
    });

    // Sort accounts by class and group order
    uasort($account_data, function ($a, $b) {
        if ($a['class_id'] != $b['class_id']) {
            return $a['class_id'] - $b['class_id'];
        }
        if ($a['group_id'] != $b['group_id']) {
            return $a['group_id'] - $b['group_id'];
        }
        return strcmp($a['account_code'], $b['account_code']);
    });

    $current_class = '';
    $current_group = '';
    $total_debits = 0;
    $total_credits = 0;

    foreach ($account_data as $account_code => $account) {
        $debit = $account['total_debit'];
        $credit = $account['total_credit'];

        // Write class header if changed
        if ($current_class != $account['class_name']) {
            if ($current_class != '') {
                fputcsv($output, []); // Empty row between classes
            }
            $current_class = $account['class_name'];

            if ($show_crdr) {
                fputcsv($output, ['CLASS', '', $current_class, '', '', '']);
            } else {
                fputcsv($output, ['CLASS', '', $current_class, '']);
            }
            $current_group = ''; // Reset group
        }

        // Write group header if changed
        if ($current_group != $account['group_name']) {
            $current_group = $account['group_name'];

            if ($show_crdr) {
                fputcsv($output, ['GROUP', '', $current_group, '', '', '']);
            } else {
                fputcsv($output, ['GROUP', '', $current_group, '']);
            }
        }

        // Calculate balance
        $balance = $debit - $credit;
        $balance_display = '';
        if ($balance > 0) {
            $balance_display = number_format($balance, 2) . ' DR';
        } elseif ($balance < 0) {
            $balance_display = number_format(abs($balance), 2) . ' CR';
        } else {
            $balance_display = '0.00';
        }

        // Write account data
        if ($show_crdr) {
            $debit_display = $debit > 0 ? number_format($debit, 2) : '';
            $credit_display = $credit > 0 ? number_format($credit, 2) : '';
            fputcsv($output, [
                'ACCOUNT',
                $account_code,
                $account['account_name'],
                $debit_display,
                $credit_display,
                $balance_display
            ]);
        } else {
            fputcsv($output, [
                'ACCOUNT',
                $account_code,
                $account['account_name'],
                $balance_display
            ]);
        }

        $total_debits += $debit;
        $total_credits += $credit;
    }

    // Write totals
    fputcsv($output, []); // Empty row
    fputcsv($output, []); // Empty row

    if ($show_crdr) {
        fputcsv($output, ['TOTALS', '', '', number_format($total_debits, 2), number_format($total_credits, 2), '']);
    } else {
        fputcsv($output, ['TOTALS', '', '', '']);
    }

    // Additional total information
    fputcsv($output, ['Total Debits: ' . number_format($total_debits, 2) . ' DR']);
    fputcsv($output, ['Total Credits: ' . number_format($total_credits, 2) . ' CR']);
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
} catch (Exception $e) {
    // Write error message to CSV
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('Trial Balance CSV Export Error: ' . $e->getMessage());
}

// Close output stream
fclose($output);
exit;
