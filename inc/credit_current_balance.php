<?php
/// Accounts receivable is a debit,

function call_current_balance($db, $emr, $general_credit_limit)
{
    $appointment_credit_limit = 0;
    $referral_sn = null;

    $fiscal_year_stmt = $db->query("
    SELECT begin, end 
    FROM chart_fiscal_year 
    WHERE closed = '0' 
    ORDER BY begin DESC 
    LIMIT 1");

    $row = $fiscal_year_stmt->fetch(PDO::FETCH_ASSOC);
    $begin = $row['begin'];
    $end   = $row['end'];

    $stmt_en = $db->prepare("
	SELECT i.insurance_name,
	i.interest,
	i.insurance_type,
	e.nhis_no,
	i.insurance_no,
	i.payment_mode,
	i.add_minus,
	e.surname,
	e.fname,
	e.oname,
	e.gender,
	e.addr,
	e.age,
	e.discount_set, 
	e.phone, 
	e.credit_limit AS personal_cr_limit,
	e.vip,
	e.email,
	i.credit_setup AS insurance_cr_limit 
	FROM enrollee e 
	JOIN insurance_tbl i ON e.hmo_no = i.insurance_no 
	WHERE e.hospital_no = :hospital_no AND i.status = 'active' 
	LIMIT 1");

    $stmt_en->execute([':hospital_no' => $emr]);
    $row = $stmt_en->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        extract($row);

        $patient_name = "$surname, $fname $oname";
        $nhis_no = $nhis_no;
        $names = $patient_name;
        $age_ = $age;

        $save_insurance_no = $insurance_no;
        $insur_title = (in_array(strtolower($insurance_type), ['family', 'corporate_deleted']))
            ? "$insurance_name <i>[$insurance_type]</i>"
            : $insurance_name;

        $patient_type  = $insurance_type;
        $referral_name = ' ';

        // Choose condition based on insurance type
        $idField = (in_array(strtolower($insurance_type), ['family', 'corporate_deleted']))
            ? 'insurance_no'
            : 'hospital_no';

        $id_value = (in_array(strtolower($insurance_type), ['family', 'corporate_deleted']))
            ? $insurance_no
            : $emr;

        // Accounts (constants, safe to embed in SQL)
        $wallet_account          = 2121;
        $Patient_Bill_Receivable = 1502;

        // Build single harmonized SQL (embed account numbers directly)
        $ledgerSQL = "
    SELECT 
        -- General Wallet Balance
        COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 2 THEN cr_amt END),0)
        - COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 2 THEN dr_amt END),0)
        AS current_balance,

        -- Wallet Actual Balance (excluding patient_stt_status = 1)
        COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 1 THEN cr_amt END),0)
        - COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 1 THEN dr_amt END),0)
        AS current_balance_actual,

        -- Patient Receivable Balance
        COALESCE(SUM(CASE WHEN account_no = {$Patient_Bill_Receivable} THEN dr_amt END),0)
        - COALESCE(SUM(CASE WHEN account_no = {$Patient_Bill_Receivable} THEN cr_amt END),0)
        AS account_receivable_balance

    FROM chart_ledger
    WHERE $idField = :id_value AND DATE(date_entry2) BETWEEN :start AND :end";

        $stmt = $db->prepare($ledgerSQL);
        $stmt->execute(array(
            ':id_value' => $id_value,
            ':start'    => $begin,
            ':end'      => $end
        ));

        $ledgerRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // Assign balances
        $current_balance = $wallet_amount = isset($ledgerRow['current_balance']) ? (float)$ledgerRow['current_balance'] : 0;
        $current_balance_actual     = isset($ledgerRow['current_balance_actual']) ? (float)$ledgerRow['current_balance_actual'] : 0;
        $Account_receivable_balance = isset($ledgerRow['account_receivable_balance']) ? (float)$ledgerRow['account_receivable_balance'] : 0;

        // Step 3: Compute pending unpaid services

        if ($insurance_no != 1000) {
            // Get all hospital_nos for the insurance HMO
            $stmt = $db->prepare("SELECT hospital_no FROM enrollee WHERE hmo_no = ?");
            $stmt->execute([$insurance_no]);
            $hospitalNos = $stmt->fetchAll(PDO::FETCH_COLUMN); // fetch as simple array
            if (!empty($hospitalNos)) {
                // Prepare placeholders for IN clause
                $placeholders = rtrim(str_repeat('?,', count($hospitalNos)), ',');
                $sql = "
                    SELECT SUM(pay) AS total_credits 
                    FROM patient_ap_services 
                    WHERE hospital_no IN ($placeholders)
                    AND paystatus = 0 
                    AND (
                        (remarks LIKE '%auto_deduct%' AND cr = 1) 
                        OR 
                        (drug_status = 1 AND cr = 1)
                    )";
                $stmt = $db->prepare($sql);
                $stmt->execute($hospitalNos);
            } else {
                $totalCredits = 0;
            }
        } else {
            // Search by single hospital_no ($emr)
            $stmt = $db->prepare("
                SELECT SUM(pay) AS total_credits 
                FROM patient_ap_services 
                WHERE hospital_no = ? 
                AND paystatus = 0 
                AND (
                    (remarks LIKE '%auto_deduct%' AND cr = 1) 
                    OR 
                    (drug_status = 1 AND cr = 1)
                )");
            $stmt->execute([$emr]);
        }

        // Fetch total credits if statement executed
        if (isset($stmt)) {
            $payRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalCredits = isset($payRow['total_credits']) ? (float)$payRow['total_credits'] : 0;
        }

        $Total_total_credits = $totalCredits;

        $select = $db->prepare("
        SELECT d.min_amount_adm, d.min_amount_condition,a.dept_id as admitted_dept, a.adm_credit_limit as personal_adm_credit_limit
        FROM admission a
        INNER JOIN department d ON d.sn = a.dept_id
        WHERE a.adm_status = 3 AND a.hospital_no = :emr
        LIMIT 1");
        $select->execute([':emr' => $emr]);
        $dept_data = $select->fetch(PDO::FETCH_ASSOC);

        if ($dept_data !== false) {
            $min_amount_adm = $dept_data['min_amount_adm'];
            $percent_credit_limit = $dept_data['min_amount_condition'];
            $personal_adm_credit_limit = $dept_data['personal_adm_credit_limit'];
            $admitted_dept = $dept_data['admitted_dept'];
        } else {
            $min_amount_adm = 0;
            $percent_credit_limit = 0;
            $personal_adm_credit_limit = 0;
            $admitted_dept = null;
            /// check current appointment table ///
            $stmt = $db->prepare("
		SELECT cr 
		FROM apptm 
		WHERE hospital_no = :hospital_no AND status = 'checkin' 
		ORDER BY appt_no DESC 
		LIMIT 1");
            $stmt->execute([':hospital_no' => $emr]);
            $row_d = $stmt->fetch(PDO::FETCH_ASSOC);
            $appointment_credit_limit = $row_d ? $row_d['cr'] : 0;
        }

        $address = $addr;
    } else {

        $patient_type = 'Referral';
        $general_credit_limit = 0;
        $stmt = $db->prepare("SELECT p.*, r.name as referral_name, r.sn as referral_sn FROM pharm_ext p LEFT JOIN referrals r ON p.referral = r.sn WHERE transc_code = ?");
        $stmt->execute([$emr]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $patient_name   = $row['cust_name'];
            $names   = $row['cust_name'];
            $gender         = $row['gender'];
            $address        = $row['address'];
            $referral_name  = $row['referral_name'];
            $referral_sn  = $row['referral_sn'];
            $discount_set   = $row['discount_set'];
            $phone          = $row['phone'];
            $age_          = null;
            $email          = $row['email_address'];
            $insurance_type = 'EX';
            $insurance_no   = $save_insurance_no = 'EX0001';

            // Accounts (constants, safe to embed directly in SQL)
            $wallet_account          = 2121;
            $Patient_Bill_Receivable = 1502;

            // Harmonized SQL using CASE WHEN
            $ledgerSQL = "
                SELECT 
                    -- General Wallet Balance
                    COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 2 THEN cr_amt END),0)
                    - COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 2 THEN dr_amt END),0)
                    AS current_balance,

                    -- Wallet Actual Balance (excluding patient_stt_status = 1)
                    COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 1 THEN cr_amt END),0)
                    - COALESCE(SUM(CASE WHEN account_no = {$wallet_account} AND patient_stt_status != 1 THEN dr_amt END),0)
                    AS current_balance_actual,

                    -- Patient Receivable Balance
                    COALESCE(SUM(CASE WHEN account_no = {$Patient_Bill_Receivable} THEN dr_amt END),0)
                    - COALESCE(SUM(CASE WHEN account_no = {$Patient_Bill_Receivable} THEN cr_amt END),0)
                    AS account_receivable_balance

                FROM chart_ledger
                WHERE hospital_no = ? AND DATE(date_entry2) BETWEEN ? AND ?";

            // Prepare & execute with positional params
            $stmt = $db->prepare($ledgerSQL);
            $stmt->execute([$emr, $begin, $end]);

            // Fetch and safely convert values
            if ($balance = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $current_balance            = (float) $balance['current_balance'];
                $current_balance_actual     = (float) $balance['current_balance_actual'];
                $Account_receivable_balance = (float) $balance['account_receivable_balance'];
            } else {
                // Fallback if fetch fails
                $current_balance = $current_balance_actual = $Account_receivable_balance = 0.0;
            }
        } else {
            error_log("Missing external patient for transc_code: " . $emr);
            header("Location: index.php?Nex");
            exit;
        }
    }

    if ($insurance_cr_limit > 0) {
        $credit_limit = $insurance_cr_limit;
        ///echo "Credit limit source: Insurance Credit Limit";
        $label = "Insurance Credit Limit";
    } else {


        $admission_limit = ($percent_credit_limit / 100) * $min_amount_adm;
        $limits = [
            'Personal Credit Limit'     => $personal_cr_limit,
            'Appointment Credit Limit'  => $appointment_credit_limit,
            'General Credit Limit'      => $general_credit_limit,
            'Admission Limit'           => $admission_limit,
            'Personal Current Admission Limit'  => $personal_adm_credit_limit
        ];

        // Get the max value
        $credit_limit = max($personal_cr_limit, $appointment_credit_limit, $admission_limit, $general_credit_limit, $personal_adm_credit_limit);
        foreach ($limits as $label => $value) {
            if ($value == $credit_limit) {
                /// echo "Credit limit source: $label";
                break;
            }
        }
    }


    $working_balance = $current_balance;

    if ($working_balance <= 0 or $working_balance == 0.00) {
        $working_balance = abs($working_balance);
        $bal_credit_limit = $credit_limit - $working_balance;


        $bal_credit_limit = $bal_credit_limit - $Total_total_credits;
        $entitled_to = $bal_credit_limit;
        $group = "Negative/Zero Balance" . 'workin balance: ' . $working_balance . ' Total credits: ' . $Total_total_credits;
    } elseif ($Total_total_credits > 0 && $working_balance > 0) {

        $bal_credit_limit = $credit_limit + $current_balance;
        $bal_credit_limit = $bal_credit_limit - $Total_total_credits;
        $entitled_to = $bal_credit_limit;
        $group = "Positive Balance with Pending Credits";
    } elseif ($Total_total_credits <= 0 && $working_balance > 0) {

        $bal_credit_limit = 0;
        $entitled_to = $credit_limit + $current_balance;
        $group = "Positive Balance with No Pending Credits";
    } else {

        $bal_credit_limit = 0;
        $entitled_to = $credit_limit  + $current_balance;
        $group = "Positive Balance with No Pending Credits 2";
    }

    return [
        'patient_name'       => $patient_name,
        'nhis_no'            => $nhis_no,
        'names'              => $names,
        'surname'            => $surname,
        'fname'              => $fname,
        'oname'              => $oname,
        'gender'             => $gender,
        'address'            => $address,
        'referral_name'      => $referral_name,
        'referral_sn'        => $referral_sn,
        'discount_set'       => $discount_set,
        'phone'              => $phone,
        'age'              => $age_,
        'email'              => $email,
        'insurance_type'     => $insurance_type,
        'insurance_no'       => $insurance_no,
        'save_insurance_no'  => $save_insurance_no,
        'wallet_amount'      => $wallet_amount,
        'bal_credit_limit'   => $entitled_to,
        'current_balance'    => $current_balance,
        'add_minus'          => $add_minus,
        'interest'           => $interest,
        'patient_type'       => $patient_type,
        'addr'               => $addr,
        'insur_title'        => $insur_title,
        'vip'                => $vip,
        'credit_type'        => $label,
        'wallet_account'        => $wallet_account,
        'insurance_name'        => $insurance_name,
        'Total_total_credits'        => $Total_total_credits,
        'insurance_cr_limit'        => $insurance_cr_limit,
        'appointment_credit_limit'        => $appointment_credit_limit,
        'personal_adm_credit_limit'        => $personal_adm_credit_limit,
        'personal_cr_limit'        => $personal_cr_limit,
        'credit_limit'        => $credit_limit,
        'entitled_to'        => $entitled_to,
        'admission_limit'        => $admission_limit,
        'admitted_dept'        => $admitted_dept,
        'payment_mode'        => $payment_mode,  /// 0 = CLAIM 1 = PAY
        'Patient_Bill_Receivable'        => $Account_receivable_balance,
        'current_balance_actual'        => $current_balance_actual,
        'min_amount_adm'        => $min_amount_adm,
        'group'        => $group
    ];
}

/*

$items = call_current_balance($db, $emr, $general_credit_limit);

$current_balance =  "34343434"; //$items["current_balance"];
$patient_name      = $items['patient_name'];
$nhis_no           = $items['nhis_no'];
$names             = $items['names'];
$surname           = $items['surname'];
$fname             = $items['fname'];
$oname             = $items['oname'];
$gender            = $items['gender'];
$address           = $items['address'];
$referral_name     = $items['referral_name'];
$discount_set      = $items['discount_set'];
$phone             = $items['phone'];
$email             = $items['email'];
$insurance_type    = $items['insurance_type'];
$insurance_no      = $items['insurance_no'];
$save_insurance_no = $items['save_insurance_no'];
$wallet_amount     = $items['wallet_amount'];
$bal_credit_limit  = $items['bal_credit_limit'];
$credit_limit  = $items['bal_credit_limit'];
$add_minus         = $items['add_minus'];
$interest          = $items['interest'];
$patient_type      = $items['patient_type'];
$addr              = $items['addr'];
$insur_title       = $items['insur_title'];
$vip               = $items['vip'];
$label             = $items['credit_type']; // Note: original key was 'credit_type', mapped to $label
*/