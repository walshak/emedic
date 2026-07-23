<?php

$current_date = date('Y-m-d H:i:s');
$fiscal_year = date('Y');
$prepared_by = 'unknown';
$item_services = 'OLD WEBMEDIC TRANSFERRED';
// Fetch current balance
if ($insurance_type === 'Family') {

    $stmt = $db->prepare("
        SELECT bal, hospital_no 
        FROM patient_billing 
        WHERE insurance_no = :insurance_no 
        ORDER BY sn DESC 
        LIMIT 1
    ");
    $stmt->execute([':insurance_no' => $insurance_no]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $old_bal = isset($row['bal']) ? $row['bal'] : 0;
    ///$emr     = isset($row['hospital_no']) ? $row['hospital_no'] : $emr;
} else {

    $stmt = $db->prepare("
        SELECT bal 
        FROM patient_billing 
        WHERE hospital_no = :hosp_no 
        ORDER BY sn DESC 
        LIMIT 1
    ");
    $stmt->execute([':hosp_no' => $emr]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $old_bal = isset($row['bal']) ? $row['bal'] : 0;
    $insurance_no = '1000';
}

if ($old_bal > 0) {
    $current_balance = $old_bal;
    $stmt_ = null;

    if ($insurance_no === '1000') {

        $stmt_ = $db->prepare("
            SELECT app_no 
            FROM chart_ledger 
            WHERE item_services = :item_services 
            AND hospital_no = :hosp_no
        ");

        $stmt_->execute([
            ':item_services' => $item_services,
            ':hosp_no'       => $emr
        ]);
    } elseif ($insurance_type === 'Family') {

        $stmt_ = $db->prepare("
            SELECT app_no 
            FROM chart_ledger 
            WHERE item_services = :item_services 
            AND insurance_no = :insurance_no
        ");

        $stmt_->execute([
            ':item_services' => $item_services,
            ':insurance_no'  => $insurance_no
        ]);
    }

    // SAFE check (instead of rowCount)
    if ($stmt_ && $stmt_->fetch(PDO::FETCH_ASSOC) === false) {
        echo $lg_ref_no = time() . $emr;


        // Helper function to save ledger entry
        function saveLedgerEntry($db, $app_no, $hosp_no, $insurance_no, $item_services, $transc_type, $dr_amt, $cr_amt, $account_no, $lg_ref_no, $prepared_by, $fiscal_year, $patient_stt_status = 0)
        {
            // Check if entry exists
            $stmt = $db->prepare("
        SELECT app_no FROM chart_ledger 
        WHERE hospital_no = :hosp_no 
        AND item_services = :item_services 
        AND insurance_no = :insurance_no 
        AND account_no = :account_no");
            $stmt->execute([
                ':hosp_no' => $hosp_no,
                ':item_services' => $item_services,
                ':insurance_no' => $insurance_no,
                ':account_no' => $account_no
            ]);

            if ($stmt->rowCount() > 0) return 'exists';

            $insertSQL = "
        INSERT INTO chart_ledger (
            app_no, hospital_no, insurance_no, item_services,
            transc_type, dr_amt, cr_amt, prepared_by, date_entry, date_entry2,
            account_no, lg_ref_no, fiscal_year, patient_stt_status
        ) VALUES (
            :app_no, :hosp_no, :insurance_no, :item_services,
            :transc_type, :dr_amt, :cr_amt, :prepared_by, :date_entry, :date_entry2,
            :account_no, :lg_ref_no, :fiscal_year, :patient_stt_status
        )";

            $stmt = $db->prepare($insertSQL);
            $stmt->execute([
                ':app_no' => $app_no,
                ':hosp_no' => $hosp_no,
                ':insurance_no' => $insurance_no,
                ':item_services' => $item_services,
                ':transc_type' => $transc_type,
                ':dr_amt' => $dr_amt,
                ':cr_amt' => $cr_amt,
                ':prepared_by' => $prepared_by,
                ':date_entry' => date('Y-m-d H:i:s'),
                ':date_entry2' => date('Y-m-d'),
                ':account_no' => $account_no,
                ':lg_ref_no' => $lg_ref_no,
                ':fiscal_year' => $fiscal_year,
                ':patient_stt_status' => $patient_stt_status
            ]);

            return $stmt->rowCount() > 0 ? 'success' : 'failed';
        }

        // Save both DEBIT and CREDIT entries
        $entries = [
            ['type' => 'DEBIT',  'dr' => $current_balance, 'cr' => 0, 'account' => '1612'],
            ['type' => 'CREDIT', 'dr' => 0, 'cr' => $current_balance, 'account' => '2121']
        ];

        foreach ($entries as $entry) {
            $result = saveLedgerEntry(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $item_services,
                $entry['type'],
                $entry['dr'],
                $entry['cr'],
                $entry['account'],
                $lg_ref_no,
                $prepared_by,
                $fiscal_year
            );
        }
        if ($result === 'success') {
            header("Location: pacct.php?emr=$emr&pay=RMS entries saved successfully");
        }
    } else {
        $current_balance = $current_balance_RMS;
        /// $emr = $_RMS_emr;
    }
}
