<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: general_ledger_sheet.php');
    exit;
}

$start_date = $_GET['start'];
$end_date = $_GET['end'];

// Optional filter parameters
$filter_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$filter_group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;
$filter_account_code = isset($_GET['account_code']) ? $_GET['account_code'] : null;

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    header('Location: general_ledger_sheet.php');
    exit;
}

// Set filename based on filter type
$filename_parts = ['General_Ledger'];
if ($filter_account_code) {
    $filename_parts[] = 'Account_' . $filter_account_code;
} elseif ($filter_group_id) {
    $filename_parts[] = 'Group_' . $filter_group_id;
} elseif ($filter_class_id) {
    $filename_parts[] = 'Class_' . $filter_class_id;
}
$filename_parts[] = $start_date . '_to_' . $end_date . '.csv';
$filename = implode('_', $filename_parts);

// Set CSV headers for download
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for proper UTF-8 support in Excel
fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

// Get hospital information
$hospital_name = isset($_SESSION['h_name']) ? $_SESSION['h_name'] : 'Hospital';

// Determine export type for header
$export_title = 'General Ledger Sheet';
if ($filter_account_code) {
    $export_title = 'Account Ledger: ' . $filter_account_code;
} elseif ($filter_group_id) {
    $export_title = 'Group Ledger: Group ID ' . $filter_group_id;
} elseif ($filter_class_id) {
    $export_title = 'Class Ledger: Class ID ' . $filter_class_id;
}

// Write header information
fputcsv($output, [$hospital_name]);
fputcsv($output, [$export_title]);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($end_date))]);
fputcsv($output, []); // Empty row

try {
    // Build WHERE clause based on filters
    $where_conditions = ["ca.account_code IS NOT NULL", "ch.sn IS NOT NULL"];
    $params = [$start_date, $end_date];
    
    if ($filter_account_code) {
        $where_conditions[] = "ca.account_code = ?";
        $params[] = $filter_account_code;
    } elseif ($filter_group_id) {
        $where_conditions[] = "cg.id = ?";
        $params[] = $filter_group_id;
    } elseif ($filter_class_id) {
        $where_conditions[] = "cl.cid = ?";
        $params[] = $filter_class_id;
    }
    
    // OPTIMIZED APPROACH: Single query to get all ledger data with hierarchy
    $sql = "
    SELECT 
        cl.class_name,
        cl.cid as class_id,
        cg.name as group_name,
        cg.id as group_id,
        ca.account_code,
        ca.account_name,
        ch.date_entry2,
        ch.lg_ref_no,
        ch.ref_value,
        ch.item_services,
        ch.hospital_no,
        ch.insurance_no,
        ch.transc_type,
        ch.dr_amt,
        ch.cr_amt,
        ch.prepared_by,
        ch.sn
    FROM chart_class cl
    LEFT JOIN chart_groups cg ON cg.class_id = cl.cid
    LEFT JOIN chart_accounts ca ON ca.account_group = cg.id
    LEFT JOIN chart_ledger ch ON ch.account_no = ca.account_code 
        AND DATE(ch.date_entry2) BETWEEN ? AND ?
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY cl.cid, cg.id, ca.account_code, ch.date_entry2 ASC, ch.sn ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize data by hierarchy
    $ledger_data = [];
    $totals = [
        'main_dr' => 0,
        'main_cr' => 0,
        'classes' => [],
        'groups' => [],
        'accounts' => []
    ];

    foreach ($results as $row) {
        $class_id = $row['class_id'];
        $group_id = $row['group_id'];
        $account_code = $row['account_code'];

        // Initialize structure
        if (!isset($ledger_data[$class_id])) {
            $ledger_data[$class_id] = [
                'class_name' => $row['class_name'],
                'groups' => []
            ];
            $totals['classes'][$class_id] = ['dr' => 0, 'cr' => 0];
        }

        if (!isset($ledger_data[$class_id]['groups'][$group_id])) {
            $ledger_data[$class_id]['groups'][$group_id] = [
                'group_name' => $row['group_name'],
                'accounts' => []
            ];
            $totals['groups'][$group_id] = ['dr' => 0, 'cr' => 0];
        }

        if (!isset($ledger_data[$class_id]['groups'][$group_id]['accounts'][$account_code])) {
            $ledger_data[$class_id]['groups'][$group_id]['accounts'][$account_code] = [
                'account_name' => $row['account_name'],
                'entries' => []
            ];
            $totals['accounts'][$account_code] = ['dr' => 0, 'cr' => 0, 'running_balance' => 0];
        }

        // Add entry
        $entry = [
            'date' => $row['date_entry2'],
            'ref' => $row['lg_ref_no'],
            'description' => $row['ref_value'],
            'item_services' => $row['item_services'],
            'hospital_no' => $row['hospital_no'],
            'insurance_no' => $row['insurance_no'],
            'transc_type' => $row['transc_type'],
            'dr_amt' => floatval($row['dr_amt']),
            'cr_amt' => floatval($row['cr_amt']),
            'prepared_by' => $row['prepared_by']
        ];

        $ledger_data[$class_id]['groups'][$group_id]['accounts'][$account_code]['entries'][] = $entry;

        // Calculate totals
        if ($entry['transc_type'] == 'DEBIT') {
            $amount = $entry['dr_amt'];
            $totals['accounts'][$account_code]['dr'] += $amount;
            $totals['accounts'][$account_code]['running_balance'] += $amount;
            $totals['groups'][$group_id]['dr'] += $amount;
            $totals['classes'][$class_id]['dr'] += $amount;
            $totals['main_dr'] += $amount;
        } else {
            $amount = $entry['cr_amt'];
            $totals['accounts'][$account_code]['cr'] += $amount;
            $totals['accounts'][$account_code]['running_balance'] -= $amount;
            $totals['groups'][$group_id]['cr'] += $amount;
            $totals['classes'][$class_id]['cr'] += $amount;
            $totals['main_cr'] += $amount;
        }
    }

    // Write main totals
    fputcsv($output, ['MAIN TOTALS']);
    fputcsv($output, ['Total Debits', number_format($totals['main_dr'], 2)]);
    fputcsv($output, ['Total Credits', number_format($totals['main_cr'], 2)]);
    $main_net = $totals['main_dr'] - $totals['main_cr'];
    fputcsv($output, ['Net', number_format(abs($main_net), 2) . ($main_net >= 0 ? ' DR' : ' CR')]);
    fputcsv($output, []); // Empty row

    // Write detailed ledger data
    fputcsv($output, ['Level', 'Code', 'Name', 'Date', 'Ref', 'Description', 'Debit', 'Credit', 'Balance', 'By']);

    foreach ($ledger_data as $class_id => $class_info) {
        // Write class header
        $class_dr = $totals['classes'][$class_id]['dr'];
        $class_cr = $totals['classes'][$class_id]['cr'];
        $class_net = $class_dr - $class_cr;
        
        fputcsv($output, [
            'CLASS',
            '',
            $class_info['class_name'],
            '',
            '',
            'Class Totals - DR: ' . number_format($class_dr, 2) . ' CR: ' . number_format($class_cr, 2) . ' Net: ' . number_format(abs($class_net), 2) . ($class_net >= 0 ? ' DR' : ' CR'),
            '',
            '',
            '',
            ''
        ]);

        foreach ($class_info['groups'] as $group_id => $group_info) {
            // Write group header
            $group_dr = $totals['groups'][$group_id]['dr'];
            $group_cr = $totals['groups'][$group_id]['cr'];
            $group_net = $group_dr - $group_cr;
            
            fputcsv($output, [
                'GROUP',
                '',
                $group_info['group_name'],
                '',
                '',
                'Group Totals - DR: ' . number_format($group_dr, 2) . ' CR: ' . number_format($group_cr, 2) . ' Net: ' . number_format(abs($group_net), 2) . ($group_net >= 0 ? ' DR' : ' CR'),
                '',
                '',
                '',
                ''
            ]);

            foreach ($group_info['accounts'] as $account_code => $account_info) {
                if (empty($account_info['entries'])) continue;

                // Write account header
                $acc_dr = $totals['accounts'][$account_code]['dr'];
                $acc_cr = $totals['accounts'][$account_code]['cr'];
                $acc_net = $acc_dr - $acc_cr;
                
                fputcsv($output, [
                    'ACCOUNT',
                    $account_code,
                    $account_info['account_name'],
                    '',
                    '',
                    'Account Totals - DR: ' . number_format($acc_dr, 2) . ' CR: ' . number_format($acc_cr, 2) . ' Net: ' . number_format(abs($acc_net), 2) . ($acc_net >= 0 ? ' DR' : ' CR'),
                    '',
                    '',
                    '',
                    ''
                ]);

                // Write account entries
                $running_balance = 0;
                foreach ($account_info['entries'] as $entry) {
                    // Calculate running balance
                    if ($entry['transc_type'] == 'DEBIT') {
                        $running_balance += $entry['dr_amt'];
                    } else {
                        $running_balance -= $entry['cr_amt'];
                    }

                    // Format description
                    $description = $entry['description'];
                    if ($entry['hospital_no']) {
                        $description = 'Pt: ' . $entry['hospital_no'] . ' - ' . $description;
                    }
                    if ($entry['insurance_no']) {
                        $description .= ' (Ins/Sup: ' . $entry['insurance_no'] . ')';
                    }
                    if ($entry['item_services']) {
                        $description .= ' | ' . $entry['item_services'];
                    }

                    fputcsv($output, [
                        'ENTRY',
                        $account_code,
                        $account_info['account_name'],
                        date('d-M-y', strtotime($entry['date'])),
                        $entry['ref'],
                        $description,
                        $entry['dr_amt'] > 0 ? number_format($entry['dr_amt'], 2) : '',
                        $entry['cr_amt'] > 0 ? number_format($entry['cr_amt'], 2) : '',
                        number_format(abs($running_balance), 2) . ($running_balance >= 0 ? ' DR' : ' CR'),
                        $entry['prepared_by']
                    ]);
                }

                fputcsv($output, []); // Empty row after each account
            }
        }
        fputcsv($output, []); // Empty row after each class
    }

    // Write final summary
    fputcsv($output, ['SUMMARY']);
    fputcsv($output, ['Period', $start_date . ' to ' . $end_date]);
    fputcsv($output, ['Total Debits', number_format($totals['main_dr'], 2) . ' DR']);
    fputcsv($output, ['Total Credits', number_format($totals['main_cr'], 2) . ' CR']);
    fputcsv($output, ['Net Balance', number_format(abs($main_net), 2) . ($main_net >= 0 ? ' DR' : ' CR')]);
    fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);

} catch (Exception $e) {
    // Write error message to CSV
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('General Ledger CSV Export Error: ' . $e->getMessage());
}

// Close output stream
fclose($output);
exit;
?>
