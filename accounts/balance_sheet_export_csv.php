<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: balance_sheet.php');
    exit;
}

$start_date = $_GET['start'];
$end_date = $_GET['end'];

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    header('Location: balance_sheet.php');
    exit;
}

// Set CSV headers for download
$filename = 'Balance_Sheet_' . $start_date . '_to_' . $end_date . '.csv';
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
fputcsv($output, ['Balance Sheet']);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($end_date))]);
fputcsv($output, []); // Empty row

try {
    // ASSETS
    fputcsv($output, ['Assets', '']);
    
    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
    $groups->execute([1]);
    $asset_groups = $groups->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($asset_groups as $group) {
        $gTotal = getGroupTotal($group['id'], $start_date, $end_date)['bal'];
        if ($gTotal > 0) {
            fputcsv($output, [$group['name'], number_format(abs($gTotal), 2)]);
        }
    }
    
    $cTotalAssets = getClassTotal(1, $start_date, $end_date)['bal'];
    fputcsv($output, ['TOTAL OF ASSETS', number_format(abs($cTotalAssets), 2)]);
    fputcsv($output, []); // Empty row
    
    // LIABILITIES
    fputcsv($output, ['Liabilities', '']);
    
    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
    $groups->execute([2]);
    $liability_groups = $groups->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($liability_groups as $group) {
        $gTotal = getGroupTotal($group['id'], $start_date, $end_date)['bal'];
        if ($gTotal > 0 || $gTotal < 0) {
            fputcsv($output, [$group['name'], number_format(abs($gTotal), 2)]);
        }
    }
    
    $cTotalLiabilities = getClassTotal(2, $start_date, $end_date)['bal'];
    fputcsv($output, ['TOTAL OF LIABILITIES', number_format(abs($cTotalLiabilities), 2)]);
    fputcsv($output, []); // Empty row
    
    // EQUITY
    fputcsv($output, ['Equity', '']);
    $Over_total_Equity = 0;
    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
    $groups->execute([3]);
    $equity_groups = $groups->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($equity_groups as $group) {
        $gTotal = getGroupTotal($group['id'], $start_date, $end_date)['bal'];
        $Over_total_Equity = $Over_total_Equity + $gTotal;
        if ($gTotal > 0 || $gTotal < 0) {
            fputcsv($output, [$group['name'], number_format(abs($gTotal), 2)]);
            $Over_total_Equity = $Over_total_Equity + abs($gTotal);
        }
    }
    
    $net = (makePositive(getClassTotal('1', $start_date, $end_date)['bal']) - 
            makePositive(getClassTotal('2', $start_date, $end_date)['bal'] + 
                         getClassTotal(3, $start_date, $end_date)['bal']));
                         
    fputcsv($output, ['Retained Earning', number_format($net, 2)]);
    
    $cTotalEquity = getClassTotal(3, $start_date, $end_date)['bal'];
    fputcsv($output, ['TOTAL OF EQUITY', number_format(abs($cTotalEquity) + $net, 2)]);
    fputcsv($output, []); // Empty row
    
    // GRAND TOTAL
    $liabilty = getClassTotal('2', $start_date, $end_date)['bal'];
    fputcsv($output, ['TOTAL OF LIABILITIES + EQUITY', number_format(abs($cTotalEquity) + $net + abs($liabilty), 2)]);
    
    fputcsv($output, []); // Empty row
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    
} catch (Exception $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('Balance Sheet CSV Export Error: ' . $e->getMessage());
}

fclose($output);
exit;
