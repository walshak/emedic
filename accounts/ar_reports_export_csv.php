<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Validate input parameters
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    header('Location: ar_reports.php');
    exit;
}

$start = $_GET['start'];
$end = $_GET['end'];
$include_anonymous = isset($_GET['include_anonymous']) ? $_GET['include_anonymous'] : 0;

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start) || !DateTime::createFromFormat('Y-m-d', $end)) {
    header('Location: ar_reports.php');
    exit;
}

// Set CSV headers for download
$filename = 'AR_Reports_' . $start . '_to_' . $end . '.csv';
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
fputcsv($output, ['Accounts Receivable Reports']);
fputcsv($output, ['Period: ' . date('d M Y', strtotime($start)) . ' to ' . date('d M Y', strtotime($end))]);
fputcsv($output, []); // Empty row


$account_receivable = 2124; 
$patient_account_receivable = 2121; 
$hmo_account_receivable = 1502; 

try {
    // Get supplier receivables 
    $supplier_query = "SELECT 
        COALESCE(s.name, 'Anonymous') as supplier_name,
        c.insurance_no,
		c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :account_receivable THEN c.dr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :account_receivable2 THEN c.cr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        stock_company s ON c.insurance_no = s.sn
    WHERE 
        c.account_no = :account_receivable3 AND
		(hospital_no = '' or hospital_no IS NULL) AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $supplier_query .= " AND s.name IS NOT NULL";
    }

    $params = array(
        ':account_receivable' => $account_receivable,
        ':account_receivable2' => $account_receivable,
        ':account_receivable3' => $account_receivable,
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
    $supplier_receivables = $supplier_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Patient categories
    $external_patients_query = "SELECT
		COALESCE(pe.cust_name, 'Anonymous') as patient_name,
			c.hospital_no,
			c.lg_ref_no,
			c.insurance_no,
			'External' as patient_type,
			SUM(c.dr_amt) as total_debits,
			SUM(c.cr_amt) as total_credits,
			SUM(c.dr_amt) - SUM(c.cr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			pharm_ext pe ON c.hospital_no = pe.transc_code
		WHERE
			c.account_no = :patient_account_receivable AND
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
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) - SUM(c.cr_amt) as balance
	FROM
		chart_ledger c
	LEFT JOIN
		enrollee e ON c.hospital_no = e.hospital_no
	WHERE
		c.account_no = :patient_account_receivable AND
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
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) - SUM(c.cr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			insurance_tbl i ON c.insurance_no = i.insurance_no
		WHERE
			c.account_no = :patient_account_receivable AND
			c.insurance_no NOT IN ('EX0001', '1000', 'private', '') AND
			c.insurance_no IS NOT NULL AND
		DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $family_patients_query .= " AND i.insurance_name IS NOT NULL";
    }
    $family_patients_query .= " GROUP BY c.insurance_no HAVING balance > 0";

    $patient_params = array(
        ':patient_account_receivable' => $patient_account_receivable,
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

    $external_receivables = $external_stmt->fetchAll(PDO::FETCH_ASSOC);
    $private_receivables = $private_stmt->fetchAll(PDO::FETCH_ASSOC);
    $family_receivables = $family_stmt->fetchAll(PDO::FETCH_ASSOC);

    $patient_receivables = array_merge($external_receivables, $private_receivables, $family_receivables);

    $total_external_receivable = 0;
    $total_private_receivable = 0;
    $total_family_receivable = 0;

    foreach ($external_receivables as $patient) {
        $total_external_receivable += abs($patient['balance']);
    }

    foreach ($private_receivables as $patient) {
        $total_private_receivable += abs($patient['balance']);
    }

    foreach ($family_receivables as $patient) {
        $total_family_receivable += abs($patient['balance']);
    }

    $total_patient_receivable = $total_external_receivable + $total_private_receivable + $total_family_receivable;

    $total_supplier_receivable = 0;
    foreach ($supplier_receivables as $supplier) {
        $total_supplier_receivable += abs($supplier['balance']);
    }

    // Get HMO receivables
    $hmo_query = "SELECT 
        COALESCE(i.insurance_name, 'Anonymous') as hmo_name,
        c.insurance_no,
        c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :hmo_account THEN c.dr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :hmo_account2 THEN c.cr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        insurance_tbl i ON c.insurance_no = i.insurance_no
    WHERE 
        c.account_no = :hmo_account3 AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $hmo_query .= " AND i.insurance_name IS NOT NULL";
    }

    $hmo_params = array(
        ':hmo_account' => $hmo_account_receivable,
        ':hmo_account2' => $hmo_account_receivable,
        ':hmo_account3' => $hmo_account_receivable,
        ':start' => $start,
        ':end' => $end
    );

    if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        $hmo_query .= " AND (c.insurance_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $hmo_params[':invoice_no'] = $invoice_no;
        $hmo_params[':lg_ref_no'] = $invoice_no;
    }

    $hmo_query .= " GROUP BY c.insurance_no HAVING balance > 0";

    $hmo_stmt = $db->prepare($hmo_query);
    foreach ($hmo_params as $param_name => $param_value) {
        $hmo_stmt->bindValue($param_name, $param_value);
    }
    $hmo_stmt->execute();
    $hmo_receivables = $hmo_stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_hmo_receivable = 0;
    foreach ($hmo_receivables as $hmo) {
        $total_hmo_receivable += abs($hmo['balance']);
    }

    $grand_total = $total_supplier_receivable + $total_patient_receivable + $total_hmo_receivable;

    // Start Writing CSV
    
    fputcsv($output, ['Summary']);
    fputcsv($output, ['Total Supplier Receivables', number_format($total_supplier_receivable, 2)]);
    fputcsv($output, ['Patient Receivables Breakdown', '']);
    fputcsv($output, ['    External Patients', number_format($total_external_receivable, 2)]);
    fputcsv($output, ['    Private Patients', number_format($total_private_receivable, 2)]);
    fputcsv($output, ['    Family Patients', number_format($total_family_receivable, 2)]);
    fputcsv($output, ['Total Patient Receivables', number_format($total_patient_receivable, 2)]);
    fputcsv($output, ['Total HMO Receivables', number_format($total_hmo_receivable, 2)]);
    fputcsv($output, ['Grand Total Receivables', number_format($grand_total, 2)]);
    fputcsv($output, []); // Empty row
    
    // Supplier Receivables Table
    fputcsv($output, ['Supplier Receivables']);
    if (empty($supplier_receivables)) {
        fputcsv($output, ['No supplier receivables found in the selected period']);
    } else {
        fputcsv($output, ['Supplier Name', 'Insurance ID', 'Balance Receivable']);
        foreach ($supplier_receivables as $supplier) {
            fputcsv($output, [$supplier['supplier_name'], $supplier['insurance_no'] ?: 'N/A', number_format(abs($supplier['balance']), 2)]);
        }
    }
    fputcsv($output, []); // Empty row

    // HMO Receivables Table
    fputcsv($output, ['HMO Receivables']);
    if (empty($hmo_receivables)) {
        fputcsv($output, ['No HMO receivables found in the selected period']);
    } else {
        fputcsv($output, ['HMO Name', 'Insurance ID', 'Balance Receivable']);
        foreach ($hmo_receivables as $hmo) {
            fputcsv($output, [$hmo['hmo_name'], $hmo['insurance_no'] ?: 'N/A', number_format(abs($hmo['balance']), 2)]);
        }
    }
    fputcsv($output, []); // Empty row

    // Patient Receivables Table
    fputcsv($output, ['Patient Receivables']);
    if (empty($patient_receivables)) {
        fputcsv($output, ['No patient receivables found in the selected period']);
    } else {
        fputcsv($output, ['Patient Name', 'Hospital No/Insurance ID', 'Patient Type', 'Total Debits', 'Total Credits', 'Balance Receivable']);
        foreach ($patient_receivables as $patient) {
            fputcsv($output, [
                $patient['patient_name'], 
                $patient['hospital_no'] ?: $patient['insurance_no'], 
                $patient['patient_type'],
                number_format($patient['total_debits'], 2),
                number_format($patient['total_credits'], 2),
                number_format(abs($patient['balance']), 2)
            ]);
        }
    }

    fputcsv($output, []); // Empty row
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    
} catch (Exception $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
    error_log('AR Reports CSV Export Error: ' . $e->getMessage());
}

fclose($output);
exit;
