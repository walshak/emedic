<?php

// Handle discount if applicable

$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='DISCOUNT TO PATIENTS'");
if ($stmt->rowCount() > 0) {
    $debit_gl_account_discount = $stmt->fetchColumn();
} else {
    throw new Exception('Transaction Error! This Ledger Does Not Exist: DISCOUNT TO PATIENTS');
}

$item_services = 'Discount-Voucher/ ' . $emr;
$ref_value = 'DscVoucher';

// Credit patient receivable
$V_CREDIT_ENTRY = billing(
    $db,
    $emr,
    $emr,
    $insurance_no,
    $ref_value,
    null,
    $item_services,
    'CREDIT',
    0,
    $v_discount,
    null,
    $value_date,
    $auth_code,
    null,
    $wallet_account,
    $lg_ref_no,
    0
);

if ($V_CREDIT_ENTRY !== 'success') {
    throw new Exception('Error saving bill for credit entry.');
}

$V_DEBIT_ENTRY = billing(
    $db,
    $emr,
    $emr,
    $insurance_no,
    $ref_value,
    null,
    $item_services,
    'DEBIT',
    $v_discount,
    0,
    null,
    $value_date,
    $auth_code,
    null,
    $debit_gl_account_discount,
    $lg_ref_no,
    0
);

if ($V_DEBIT_ENTRY !== 'success') {
    throw new Exception('Error saving bill for discount entry.');
}

// Clear patient services table
if ($V_CREDIT_ENTRY === 'success' && $V_DEBIT_ENTRY === 'success') {
    $updateSQL = "UPDATE vouchers_inventory SET 
                            hospital_no = :hospital_no,
                            patient_name = :patient_name,
                            issued_by = :issued_by,
                            issued_date = :issued_date,
                            used_status = '1' 
                            WHERE voucher_code = :voucher_code";

    $stmt = $db->prepare($updateSQL);
    $stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
    $stmt->bindParam(':patient_name', $patient_name, PDO::PARAM_STR);
    $stmt->bindParam(':issued_by', $_SESSION['fullname'], PDO::PARAM_STR);
    $stmt->bindParam(':issued_date', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':voucher_code', $auth_code, PDO::PARAM_STR);
    $stmt->execute();
} else {
    throw new Exception('No records updated in patient_ap_services.');
}

$ref_value = null;
