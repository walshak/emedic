<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: income.php');
    exit;
}

$start_date = $_GET['start'];
$end_date = $_GET['end'];

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    header('Location: income.php');
    exit;
}

// Set CSV headers for download
$filename = 'Income_Statement_' . $start_date . '_to_' . $end_date . '.csv';
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
fputcsv($output, ['Income Statement']);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($end_date))]);
fputcsv($output, []); // Empty row

try {
    
    // INCOME
    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
    $groups->execute([4]);
    $income_groups = $groups->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($income_groups as $group) {
        $gTotal = getGroupTotal($group['id'], $start_date, $end_date)['bal'];
        if ($gTotal > 0 || $gTotal < 0) {
            fputcsv($output, [$group['name'], number_format(abs($gTotal), 2)]);
        }
    }
    
    $cTotalIncome = getClassTotal(4, $start_date, $end_date)['bal'];
    fputcsv($output, ['TOTAL INCOME', number_format(makePositive($cTotalIncome, ''), 2)]);
    fputcsv($output, []); // Empty row
    
    // EXPENSES
    fputcsv($output, ['EXPENSES', '']);
    
    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
    $groups->execute([5]);
    $expense_groups = $groups->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($expense_groups as $group) {
        $gTotal = getGroupTotal($group['id'], $start_date, $end_date)['bal'];
        if ($gTotal > 0 || $gTotal < 0) {
            fputcsv($output, [$group['name'], number_format(abs($gTotal), 2)]);
        }
    }
    
    $cTotalExpenses = getClassTotal(5, $start_date, $end_date)['bal'];
    fputcsv($output, ['TOTAL EXPENSES', number_format(makePositive($cTotalExpenses, ''), 2)]);
    fputcsv($output, []); // Empty row
    
    // NET PROFIT
    $net_profit = makePositive(getClassTotal('4', $start_date, $end_date)['bal']) - makePositive(getClassTotal('5', $start_date, $end_date)['bal']);
    fputcsv($output, ['NET PROFIT', number_format($net_profit, 2)]);
    
    fputcsv($output, []); // Empty row
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    
} catch (Exception $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('Income Statement CSV Export Error: ' . $e->getMessage());
}

fclose($output);
exit;
