<?php
$stmt = $db->prepare("SELECT account_code FROM chart_accounts WHERE account_code = :account_code LIMIT 1");
$stmt->execute([':account_code' => 2121]);
$wallet_account = $stmt->fetchColumn();
$error_wallet = $wallet_account === false ? 1 : 0;

$noExist = '0';
$stmt_en = $db->prepare("SELECT 
    i.insurance_name,
    i.interest,
    i.insurance_type,
    e.nhis_no,
    i.insurance_no,
    i.add_minus,
    e.surname,
    e.fname,
    e.oname,
    e.gender,
    e.addr,
    e.discount_set, 
    e.phone, 
    e.credit_limit,
    e.vip,
    e.email 
    FROM enrollee as e 
    INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no 
    WHERE hospital_no=:hospital_no and status='active'");

$stmt_en->bindParam(':hospital_no', $emr);
$stmt_en->execute();

if ($stmt_en->rowCount() > 0) {
    $row = $stmt_en->fetch(PDO::FETCH_ASSOC);
    extract($row);

    $patient_name = "$surname, $fname $oname";
    $nhis_no = $nhis_no;
    $names = $patient_name;
    $discount_set = $discount_set;

    if ($insurance_type == 'Family') {
        $save_insurance_no = $insurance_no;
        $insur_title = "$insurance_name <i>[$insurance_type]</i>";
        $active_bal = 'family';
    } else {
        $save_insurance_no = $insurance_no;
        $insur_title = $insurance_name;
    }

    $patient_type = $insurance_type;
    $referral_name = ' ';

    if ($insurance_type === 'Family') {
        $stmt = $db->prepare("SELECT COALESCE(SUM(cr_amt), 0) - COALESCE(SUM(dr_amt), 0) AS balance FROM chart_ledger WHERE insurance_no = ? AND account_no = ?");
        $stmt->execute([$insurance_no, $wallet_account]);
    } else {
        $stmt = $db->prepare("SELECT COALESCE(SUM(cr_amt), 0) - COALESCE(SUM(dr_amt), 0) AS balance FROM chart_ledger WHERE hospital_no = ? AND account_no = ?");
        $stmt->execute([$emr, $wallet_account]);
    }

    /// $current_balance = $wallet_amount = (float)$stmt->fetchColumn();

    $query = "
	SELECT 
		COALESCE(SUM(CASE WHEN cr = 2 THEN pay ELSE 0 END), 0) AS debit,
		COALESCE(SUM(CASE WHEN cr = 1 THEN pay ELSE 0 END), 0) AS credit
	FROM patient_ap_services
	WHERE hospital_no = ? AND paystatus = 0";
    $stmt = $db->prepare($query);
    $stmt->execute([$emr]);
    list($Total_total_debit, $Total_total_credits) = array_map('floatval', array_values($stmt->fetch(PDO::FETCH_ASSOC)));
    $credit_limit = 0; ///-= $Total_total_credits;
} else {

    // External patient type
    $patient_type = 'Referral';
    $stmt = $db->prepare("SELECT cust_name,gender,address,referral,discount_set,phone FROM pharm_ext WHERE transc_code = ?");
    $stmt->execute([$emr]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Assign patient details
        $patient_name   = $row['cust_name'];
        $gender         = $row['gender'];
        $address        = $row['address'];
        $referral_name  = $row['referral'];
        $discount_set   = $row['discount_set'];
        $phone          = $row['phone'];
        $email          = '';  // Presumably email isn't stored
        $insurance_type = 'EX';
        $insurance_no   = $save_insurance_no = 'EX0001';
        $current_balance = $wallet_amount = 0.00;

        // Get wallet balance
        // Prepare and execute query to get wallet balance
        $stmt = $db->prepare("
    SELECT 
        COALESCE(SUM(cr_amt), 0) AS TOTAL_CREDITS, 
        COALESCE(SUM(dr_amt), 0) AS TOTAL_DEBITS
    FROM chart_ledger 
    WHERE hospital_no = ? AND account_no = ?
");

        $stmt->execute([$emr, $wallet_account]);

        // Fetch and safely convert values
        if ($balance = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $TOTAL_CREDITS   = (float) $balance['TOTAL_CREDITS'];
            $TOTAL_DEBITS    = (float) $balance['TOTAL_DEBITS'];
            $wallet_amount   = $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
        } else {
            // Fallback in case fetch fails
            $TOTAL_CREDITS = $TOTAL_DEBITS = $wallet_amount = $current_balance = 0.0;
        }
    } else {
        error_log("Missing external patient for transc_code: " . $emr);
        header("Location: index.php?Nex");
        exit;
    }
}
