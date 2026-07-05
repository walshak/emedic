<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: invoice_reports.php');
    exit;
}

$start_date = $_GET['start'];
$end_date = $_GET['end'];

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    header('Location: invoice_reports.php');
    exit;
}

// Set CSV headers for download
$filename = 'Invoice_Reports_' . $start_date . '_to_' . $end_date . '.csv';
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
fputcsv($output, ['Invoice Settlement Reports']);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($end_date))]);
fputcsv($output, []); // Empty row

// Fetch Data
$sql = "SELECT c.invoice_no, c.date_entry2 as date_entry, c.cr_amt as dr_amt, s.sn, s.name 
        FROM chart_ledger AS c 
        INNER JOIN stock_company AS s ON c.insurance_no = s.sn 
        WHERE c.date_entry2 BETWEEN :start AND :end
        AND transc_type = 'CREDIT'";

if (isset($_GET['invoice_no']) && $_GET['invoice_no'] != '') {
    $invoice_no = $_GET['invoice_no'];
    $sql .= " AND c.invoice_no = :invoice_no";
}

$reports = $db->prepare($sql);
$reports->bindParam(':start', $start_date);
$reports->bindParam(':end', $end_date);
if (isset($invoice_no)) {
    $reports->bindParam(':invoice_no', $invoice_no);
}
$reports->execute();
$reports = $reports->fetchAll(PDO::FETCH_ASSOC);

function groupDataExport($data) {
    $grouped = [];
    foreach ($data as $entry) {
        $supplier = $entry['sn'];
        $inv_no = isset($entry['invoice_no']) ? $entry['invoice_no'] : 'INITIAL CREDITS/PURCHASE';

        if (!isset($grouped[$supplier])) {
            $grouped[$supplier] = [
                'name' => $entry['name'],
                'sn' => $supplier,
                'invoices' => []
            ];
        }

        if (!isset($grouped[$supplier]['invoices'][$inv_no])) {
            $grouped[$supplier]['invoices'][$inv_no] = [];
        }

        $grouped[$supplier]['invoices'][$inv_no][] = $entry;
    }
    return $grouped;
}

$groupedData = groupDataExport($reports);
$account_payable = 2124;

try {
    foreach ($groupedData as $supplier => $supplierData) {
        fputcsv($output, ['Supplier:', $supplierData['name']]);
        fputcsv($output, []);
        
        foreach ($supplierData['invoices'] as $inv_no => $transactions) {
            $inv_display = ($inv_no == 'INITIAL CREDITS/PURCHASE') ? $inv_no : 'INVOICE NO - ' . $inv_no;
            fputcsv($output, [$inv_display]);
            fputcsv($output, ['#', 'Date Entry', ($inv_no == 'INITIAL CREDITS/PURCHASE' ? 'Credit Amount' : 'Debit Amount')]);
            
            $total = 0;
            $index = 1;
            foreach ($transactions as $transaction) {
                $total += $transaction['dr_amt'];
                fputcsv($output, [
                    $index++,
                    $transaction['date_entry'] !== NULL ? $transaction['date_entry'] : 'N/A',
                    number_format($transaction['dr_amt'], 2)
                ]);
            }
            fputcsv($output, ['Total', '', number_format($total, 2)]);
            fputcsv($output, []);
        }
        
        // Supplier Summary
        $stmt = $db->query("SELECT 
            sum(dr_amt) as TOTAL_DEBITS, 
            sum(cr_amt) as TOTAL_CREDITS
            FROM chart_ledger 
            WHERE insurance_no='$supplier' 
            and account_no='$account_payable' 
            AND patient_stt_status!=1
            AND DATE(date_entry2) BETWEEN '$start_date' AND '$end_date'");

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $TOTAL_CREDITS = $row['TOTAL_CREDITS'];
            $TOTAL_DEBITS = $row['TOTAL_DEBITS'];
            $bal = $TOTAL_CREDITS - $TOTAL_DEBITS;
        } else {
            $TOTAL_DEBITS = $TOTAL_CREDITS = $bal = 0;
        }
        
        fputcsv($output, ['Total Debits:', number_format($TOTAL_DEBITS, 2)]);
        fputcsv($output, ['Total Credits:', number_format($TOTAL_CREDITS, 2)]);
        fputcsv($output, ['Balance to be Paid:', number_format($bal, 2)]);
        fputcsv($output, []); // Empty row between suppliers
        fputcsv($output, []); // Empty row between suppliers
    }
    
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    
} catch (Exception $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('Invoice Reports CSV Export Error: ' . $e->getMessage());
}

fclose($output);
exit;
