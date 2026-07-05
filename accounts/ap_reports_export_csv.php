<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: ap_reports.php');
    exit;
}

$start = $_GET['start'];
$end = $_GET['end'];
$include_anonymous = isset($_GET['include_anonymous']) ? $_GET['include_anonymous'] : 0;

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start) || !DateTime::createFromFormat('Y-m-d', $end)) {
    header('Location: ap_reports.php');
    exit;
}

// Set CSV headers for download
$filename = 'AP_Reports_' . $start . '_to_' . $end . '.csv';
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
fputcsv($output, ['Accounts Payable Reports']);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start)) . ' to ' . date('d M Y', strtotime($end))]);
fputcsv($output, []); // Empty row

$account_payable = 2124; 
$patient_account_payable = 2121; 

try {
    // Get supplier payables
    $supplier_query = "SELECT 
        COALESCE(s.name, 'Anonymous') as supplier_name,
        c.insurance_no,
		c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :account_payable THEN c.cr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :account_payable2 THEN c.dr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        stock_company s ON c.insurance_no = s.sn
    WHERE 
        c.account_no = :account_payable3 AND
		(hospital_no = '' or hospital_no IS NULL) AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $supplier_query .= " AND s.name IS NOT NULL";
    }

    $params = array(
        ':account_payable' => $account_payable,
        ':account_payable2' => $account_payable,
        ':account_payable3' => $account_payable,
        ':start' => $start,
        ':end' => $end
    );

    if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        $supplier_query .= " AND (c.invoice_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";

        $params[':invoice_no'] = $invoice_no;
        $params[':lg_ref_no'] = $invoice_no;
    }

    $supplier_query .= " GROUP BY c.insurance_no HAVING balance > 0";

    $supplier_stmt = $db->prepare($supplier_query);
    foreach ($params as $param_name => $param_value) {
        $supplier_stmt->bindValue($param_name, $param_value);
    }
    $supplier_stmt->execute();
    $supplier_payables = $supplier_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Patient categories
    $external_patients_query = "SELECT
		COALESCE(pe.cust_name, 'Anonymous') as patient_name,
			c.hospital_no,
			c.lg_ref_no,
			c.insurance_no,
			'External' as patient_type,
			SUM(c.cr_amt) as total_credits,
			SUM(c.dr_amt) as total_debits,
			SUM(c.cr_amt) - SUM(c.dr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			pharm_ext pe ON c.hospital_no = pe.transc_code
		WHERE
			c.account_no = :patient_account_payable AND
			c.insurance_no = 'EX0001' AND
			DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $external_patients_query .= " AND pe.cust_name IS NOT NULL";
    }
    $external_patients_query .= " GROUP BY c.hospital_no HAVING balance > 0";

    $private_patients_query = "SELECT
	COALESCE(CONCAT(e.fname, ' ', e.surname), 'Anonymous') as patient_name,
		c.hospital_no,
		c.lg_ref_no,
		c.insurance_no,
		'Private' as patient_type,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) - SUM(c.dr_amt) as balance
	FROM
		chart_ledger c
	LEFT JOIN
		enrollee e ON c.hospital_no = e.hospital_no
	WHERE
		c.account_no = :patient_account_payable AND
		(c.insurance_no = '1000' OR c.insurance_no = 'private') AND
		DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $private_patients_query .= " AND (e.fname IS NOT NULL AND e.surname IS NOT NULL)";
    }
    $private_patients_query .= " GROUP BY c.hospital_no HAVING balance > 0";

    $family_patients_query = "SELECT
		COALESCE(i.insurance_name, 'Anonymous') as patient_name,
		'' as hospital_no,
		c.lg_ref_no,
		c.insurance_no,
		'Family' as patient_type,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) - SUM(c.dr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			insurance_tbl i ON c.insurance_no = i.insurance_no
		WHERE
			c.account_no = :patient_account_payable AND
			c.insurance_no NOT IN ('EX0001', '1000', 'private', '') AND
			c.insurance_no IS NOT NULL AND
		DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $family_patients_query .= " AND i.insurance_name IS NOT NULL";
    }
    $family_patients_query .= " GROUP BY c.insurance_no HAVING balance > 0";

    $patient_params = array(
        ':patient_account_payable' => $patient_account_payable,
        ':start' => $start,
        ':end' => $end
    );

    if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        $external_patients_query .= " AND (c.hospital_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $private_patients_query .= " AND (c.hospital_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $family_patients_query .= " AND (c.insurance_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $patient_params[':invoice_no'] = $invoice_no;
        $patient_params[':lg_ref_no'] = $invoice_no;
    }

    $external_stmt = $db->prepare($external_patients_query);
    $private_stmt = $db->prepare($private_patients_query);
    $family_stmt = $db->prepare($family_patients_query);

    foreach ($patient_params as $param_name => $param_value) {
        $external_stmt->bindValue($param_name, $param_value);
        $private_stmt->bindValue($param_name, $param_value);
        $family_stmt->bindValue($param_name, $param_value);
    }

    $external_stmt->execute();
    $private_stmt->execute();
    $family_stmt->execute();

    $external_payables = $external_stmt->fetchAll(PDO::FETCH_ASSOC);
    $private_payables = $private_stmt->fetchAll(PDO::FETCH_ASSOC);
    $family_payables = $family_stmt->fetchAll(PDO::FETCH_ASSOC);

    $patient_payables = array_merge($external_payables, $private_payables, $family_payables);

    $total_external_payable = 0;
    $total_private_payable = 0;
    $total_family_payable = 0;

    foreach ($external_payables as $patient) {
        $total_external_payable += $patient['balance'];
    }

    foreach ($private_payables as $patient) {
        $total_private_payable += $patient['balance'];
    }

    foreach ($family_payables as $patient) {
        $total_family_payable += $patient['balance'];
    }

    $total_patient_payable = $total_external_payable + $total_private_payable + $total_family_payable;

    $total_supplier_payable = 0;
    foreach ($supplier_payables as $supplier) {
        $total_supplier_payable += $supplier['balance'];
    }

    $grand_total = $total_supplier_payable + $total_patient_payable;

    // Start Writing CSV
    
    fputcsv($output, ['Summary']);
    fputcsv($output, ['Total Supplier Payables', number_format($total_supplier_payable, 2)]);
    fputcsv($output, ['Patient Payables Breakdown', '']);
    fputcsv($output, ['    External Patients', number_format($total_external_payable, 2)]);
    fputcsv($output, ['    Private Patients', number_format($total_private_payable, 2)]);
    fputcsv($output, ['    Family Patients', number_format($total_family_payable, 2)]);
    fputcsv($output, ['Total Patient Payables', number_format($total_patient_payable, 2)]);
    fputcsv($output, ['Grand Total Payables', number_format($grand_total, 2)]);
    fputcsv($output, []); // Empty row
    
    // Supplier Payables Table
    fputcsv($output, ['Supplier Payables']);
    if (empty($supplier_payables)) {
        fputcsv($output, ['No supplier payables found in the selected period']);
    } else {
        fputcsv($output, ['Supplier Name', 'Insurance ID', 'Balance Payable']);
        foreach ($supplier_payables as $supplier) {
            fputcsv($output, [$supplier['supplier_name'], $supplier['insurance_no'] ?: 'N/A', number_format($supplier['balance'], 2)]);
        }
    }
    fputcsv($output, []); // Empty row

    // Patient Payables Table
    fputcsv($output, ['Patient Payables']);
    if (empty($patient_payables)) {
        fputcsv($output, ['No patient payables found in the selected period']);
    } else {
        fputcsv($output, ['Patient Name', 'Hospital No/Insurance ID', 'Patient Type', 'Total Credits', 'Total Debits', 'Balance Payable']);
        foreach ($patient_payables as $patient) {
            fputcsv($output, [
                $patient['patient_name'], 
                $patient['hospital_no'] ?: $patient['insurance_no'], 
                $patient['patient_type'],
                number_format($patient['total_credits'], 2),
                number_format($patient['total_debits'], 2),
                number_format($patient['balance'], 2)
            ]);
        }
    }

    fputcsv($output, []); // Empty row
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    
} catch (Exception $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('AP Reports CSV Export Error: ' . $e->getMessage());
}

fclose($output);
exit;
