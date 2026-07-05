<?php
session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");

$response_main = array(
    'message' => '',
    'url' => '',
    'status' => ''
);

if (isset($_POST['paynow_final']) && isset($_POST['emr']) && isset($_SESSION['fullname'])) {

    $emr = $_POST['emr'];

    $general_credit_limit =    $_SESSION['credit_limit_status'];
    $items = call_current_balance($db, $emr, $general_credit_limit);
    $current_balance   = $items["current_balance"];
    $insurance_type    = $items['insurance_type'];
    $insurance_no      = $items['insurance_no'];
    $wallet_amount     = $items['wallet_amount'];
    $wallet_account    = $items['wallet_account'];

    $update = "DELETE FROM saleprint WHERE hos_no='$emr'";
    $db->exec($update);

    $paymethod = $_POST['paymethod'];
    $ref_no = $_POST['ref_no'];
    $bank_name = $_POST['bank_name'];
    $transaction_code = $_POST['transaction_code'];
    $MYwallet_amount = $_POST['wallet_amount'];
    $payment_remarks = $_POST['payment_remarks'];
    $insurance_type = $_POST['insurance_type'];
    $insurance_no = $_POST['save_insurance_no'];
    $debt_post = $_POST['debt_post'];
    $patient_name = $_POST['patient_name'];
    $v_discount = $_POST['v_discount'];
    $value_date = $_POST['value_date'];
    $auth_code = $_POST['auth_code'];
    $discount_set = $_POST['discount_set'];

    $discount_insurance_no = $_POST['discount_insurance_no'];
    $dsc_chr_type = $_POST['dsc_chr_type'];

    $service_group_desc = '';
    $list_desc = $_POST["item"];
    $Roll_array = explode(",", $list_desc);
    $resultM_count = count($Roll_array);
    for ($i = 0; $i < $resultM_count; $i++) {
        $inv_idd = $Roll_array[$i];
        $break = explode("__", $inv_idd);
        if ($break[12] == 'Medical Services') {
            $my_break = $break[10];
        } else {
            $my_break = $break[12];
        }
        if (strpos($service_group_desc, $my_break) !== false) {
        } else {
            $service_group_desc .= $my_break . ',';
        }
    }
    $service_group_desc = rtrim($service_group_desc, ',');

    if ($paymethod === 'pay_from_patient_wallet' || $paymethod === 'Bill_to' || $paymethod === 'writeoff') {
        try {
            $list = $_POST["item"];
            if (!empty($list)) {
                $batch_name_array = array_map('trim', explode(",", $list));
                $all_items = [];

                foreach ($batch_name_array as $inv_id) {
                    $break = explode("__", $inv_id);

                    if (isset($break[2], $break[1])) {
                        $item_services = trim($break[2]);
                        $pay = trim($break[1]);

                        if ($item_services !== '' && $pay !== '') {
                            $all_items[] = "{$item_services}({$pay})";
                        }
                    }
                }

                $all_items_here = implode(", ", $all_items);
                $stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no,service_list,total_amount, remark, staff_name,bill_to_who) 
				VALUES (:hospital_no,:list_desc,:cash, :remark, :staff_name,:bill_to_who)");

                $stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
                $stmt->bindParam(':list_desc', $all_items_here, PDO::PARAM_STR);
                $stmt->bindParam(':cash', $_POST['cash'], PDO::PARAM_STR);
                $stmt->bindParam(':remark', $payment_remarks, PDO::PARAM_STR);
                $stmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
                $stmt->bindParam(':bill_to_who', $_POST['auth_staff'], PDO::PARAM_STR);

                if ($stmt->execute()) {
                    ///echo json_encode(['status' => 0, 'message' => 'Remark successfully added.']);
                } else {
                    echo json_encode(['status' => 1, 'message' => 'Failed to add remark.']);
                    exit;
                }
            } else {
                echo json_encode(['status' => 1, 'message' => 'Failed to Get All Services.']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 1, 'message' => 'Database Error: ' . $e->getMessage()]);
            exit;
        }
    }

    if ($paymethod == 'post_discount') {
        $bank_name = '';
        include('post_discount_charges.php');
    } elseif (($paymethod == 'POS' or $paymethod == 'CASHPOS') and $bank_name == '') {
        $response_main['message'] = 'Select Bank Name !';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
    } elseif (($paymethod == 'Transfer'  or $paymethod == 'CASHTransfer')  and $bank_name == '') {

        $response_main['message'] = 'Select Bank Name !';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
    } elseif ($paymethod == 'PostCredit') {
        $bank_name = '';
        include('PostCredit.php');
    } elseif ($paymethod == 'pay_from_patient_wallet') {
        $bank_name = '';
        include('pay_another_wallet.php');
    } elseif ($paymethod == 'Wallet') {
        $bank_name = '';
        include('wallet.php');
    } elseif ($paymethod == 'Bill_to' or $paymethod == 'writeoff') {
        $bank_name = '';
        include('bill_to_account.php');
    } else {
        include('others.php');
    }
}

if (isset($_POST['final_save_money'])) {

    /// SAVE FINAL
    $credit_gl_account = $_POST['credit_gl_account'];
    $wallet_account = $_POST['wallet_account'];
    $hosp_no_giver = $_POST['hosp_no_giver'];
    $hosp_numb = $_POST['hosp_no_giver'];
    $bank_name = $_POST['bank_name'];
    $ref_no = $_POST['ref_no'];
    if ($ref_no != '') {
        $ref = " /Ref: " . $ref_no . ' /BNK: ' . $bank_name;
    }


    // Step 1: Prepare the SQL Query
    $sql = "SELECT * FROM billing_dep_confirm WHERE hospitla_no = :hosp_no_giver ORDER BY sn DESC LIMIT 1";

    // Step 2: Prepare the Statement
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':hosp_no_giver', $hosp_no_giver, PDO::PARAM_STR);

    // Step 3: Execute the Query
    $stmt->execute();
    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);

    // Step 4: Use the Retrieved Data
    if ($row_d) {
        $hosp_no_beneficiary = $row_d['hosp_no'];
        $auth_code = $row_d['auth_code'];
        $auth_amount = $row_d['amount'];
        $transc_type = $row_d['transc_type'];
        $mode_pay = $row_d['mode_pay'];
        $insurance_no = $row_d['insurance_no'];
        $sale_sn = 'bill_' . $row_d['sn'];

        // Optionally, you can proceed with further operations using these variables
    } else {
        // Handle the case where no rows are found (optional)
    }


    if ($transc_type == 'Deposit') {

        try {
            // Start the transaction
            $db->beginTransaction();

            /// deposit code
            $emr = $hosp_no_giver;
            $lg_ref_no = time() . $emr;
            $ref_value = $mode_pay;
            $setdate = date("Y-m-d H:");

            // Default values
            $TOTAL_CREDITS = 0;
            $TOTAL_DEBITS  = 0;

            if ($insurance_no == '1000') {
                $sql = "SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS 
            FROM chart_ledger 
            WHERE hospital_no = :hospital_no 
              AND account_no  = :account_no";
                $params = array(':hospital_no' => $emr, ':account_no' => $credit_gl_account);
            } else {
                $sql = "SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS 
            FROM chart_ledger 
            WHERE insurance_no = :insurance_no 
              AND account_no   = :account_no";
                $params = array(':insurance_no' => $insurance_no, ':account_no' => $credit_gl_account);
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $TOTAL_CREDITS = (float) $row['TOTAL_CREDITS'];
                $TOTAL_DEBITS  = (float) $row['TOTAL_DEBITS'];
            }

            $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;

            if ($current_balance < 0) {

                ///CHECK DEBT ACCT & post DEBT account_code=1502 =====================/////===================
                ///-------------------------------------------------------------------/////-------------------

                if ($insurance_no == '1000') {
                    $stmt_ckh = $db->prepare("SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS 
                          FROM chart_ledger 
                          WHERE hospital_no = ? AND account_no = 1502");
                    $stmt_ckh->execute([$emr]);
                } else {
                    $stmt_ckh = $db->prepare("SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS 
                          FROM chart_ledger 
                          WHERE insurance_no = ? AND account_no = 1502");
                    $stmt_ckh->execute([$insurance_no]);
                }


                if ($stmt_ckh && $stmt_ckh->rowCount() > 0) {

                    $row = $stmt_ckh->fetch(PDO::FETCH_ASSOC);
                    $TOTAL_DEBITS = (float)($row['TOTAL_DEBITS']);
                    $TOTAL_CREDITS = (float)($row['TOTAL_CREDITS']);

                    $acct_recievabl_bal = $TOTAL_DEBITS - $TOTAL_CREDITS;
                    if ($acct_recievabl_bal > 0) {
                        if ($acct_recievabl_bal >= $auth_amount) {
                            $amt_settle = 0;
                            $acct_recievabl_bal = $auth_amount;
                        } else {
                            $amt_settle = $auth_amount - $acct_recievabl_bal;
                        }
                    }

                    if ($acct_recievabl_bal != 0) {  /// there was somthing beforee
                        ///========================== ACCOUNT RECIEVABLE ==================================
                        $dr_amt = $bal = $sale_sn = 0;
                        $item_services = 'DEBT SETTLED/ ' . $row_d['descrip'] . $ref . ' EMR: ' . $emr;
                        $credit_gl_account_acct_recievable = 1502;
                        $transc_type = "DEBIT";
                        $CREDIT_ENTRY = billing(
                            $db,
                            $emr,
                            $emr,
                            $insurance_no,
                            $ref_value,
                            $sale_sn,
                            $item_services,
                            $transc_type,
                            $dr_amt,
                            $acct_recievabl_bal,
                            $bal,
                            $setdate,
                            $auth_code,
                            $bank_name,
                            $credit_gl_account_acct_recievable,
                            $lg_ref_no,
                            0
                        );

                        if ($CREDIT_ENTRY !== 'success') {
                            throw new Exception('Error saving AR: ');
                        }
                    }
                }
                ///========================== ACCOUNT RECIEVABLE ==================================

                if ($amt_settle == 0) {
                    $item_services = 'ACCT. RECVBL CR/ ' . $row_d['descrip'] . $ref . ' EMR: ' . $emr;
                    $dr_amt = 0;
                    $transc_type = 'CREDIT';
                    $CREDIT_ENTRY = billing(
                        $db,
                        $emr,
                        $emr,
                        $insurance_no,
                        $ref_value,
                        $sale_sn,
                        $item_services,
                        $transc_type,
                        $dr_amt,
                        $auth_amount,  //// auth_amount
                        $bal,
                        $setdate,
                        $auth_code,
                        $bank_name,
                        $credit_gl_account,
                        $lg_ref_no,
                        1
                    );

                    if ($CREDIT_ENTRY !== 'success') {
                        throw new Exception('Error saving bill: DEPOSIT 1ST ENTRY.');
                    }
                }


                if ($amt_settle > 0) {
                    // Common values
                    $dr_amt = 0;
                    $transc_type = 'CREDIT';

                    // === 1st Deposit Entry ===
                    $item_services = 'ACCT. RECVBL CR BAL/ ' . $row_d['descrip'] . $ref . ' EMR: ' . $emr;
                    $CREDIT_ENTRY = billing(
                        $db,
                        $emr,
                        $emr,
                        $insurance_no,
                        $ref_value,
                        $sale_sn,
                        $item_services,
                        $transc_type,
                        $dr_amt,
                        $amt_settle,
                        $bal,
                        $setdate,
                        $auth_code,
                        $bank_name,
                        $credit_gl_account,
                        $lg_ref_no,
                        0
                    );

                    if ($CREDIT_ENTRY !== 'success') {
                        throw new Exception('Error saving bill: DEPOSIT 1ST ENTRY.');
                    }

                    // === 2nd Deposit Entry ===
                    $item_services = 'ACCT. RECVBL DEDUCT./ ' . $row_d['descrip'] . $ref . ' EMR: ' . $emr;
                    $CREDIT_ENTRY = billing(
                        $db,
                        $emr,
                        $emr,
                        $insurance_no,
                        $ref_value,
                        $sale_sn,
                        $item_services,
                        $transc_type,
                        $dr_amt,
                        $acct_recievabl_bal,
                        $bal,
                        $setdate,
                        $auth_code,
                        $bank_name,
                        $credit_gl_account,
                        $lg_ref_no,
                        1
                    );

                    if ($CREDIT_ENTRY !== 'success') {
                        throw new Exception('Error saving bill: DEPOSIT 2ND ENTRY.');
                    }
                }
                ///========================== DEPOSIT AMT  ==================================


            } else {
                $item_services = 'Amount Deposit: ' . $row_d['descrip'] . $ref . ' EMR: ' . $emr;
                $patient_stt_status = 0;
                $cr_amt = $auth_amount;
                $dr_amt = 0;
                $transc_type = 'CREDIT';
                $CREDIT_ENTRY = billing(
                    $db,
                    $emr,
                    $emr,
                    $insurance_no,
                    $ref_value,
                    $sale_sn,
                    $item_services,
                    $transc_type,
                    $dr_amt,
                    $auth_amount,
                    $bal,
                    $setdate,
                    $auth_code,
                    $bank_name,
                    $credit_gl_account,
                    $lg_ref_no,
                    0
                );

                if ($CREDIT_ENTRY !== 'success') {
                    throw new Exception('Error saving bill:' . $CREDIT_ENTRY);
                }
            }

            $bal = 0;
            $item_services = $row_d['descrip'] . $ref . ' EMR: ' . $emr;
            $ref_value = $mode_pay;
            $paymethod = $mode_pay;

            $isBankPayment = in_array($paymethod, ['POS', 'Transfer']) && !empty($bank_name);
            $account_name = $isBankPayment ? $bank_name : 'Main Cash';

            $stmt = $db->prepare("SELECT account_code FROM chart_accounts WHERE account_name = :account_name AND account_group = '5' LIMIT 1");
            $stmt->bindParam(':account_name', $account_name);
            $stmt->execute();
            $debit_gl_account = '';
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $debit_gl_account = $row['account_code'];
            }

            if (!empty($credit_gl_account) && !empty($debit_gl_account) && !empty($insurance_no)) {

                $dr_amt = $auth_amount;
                $cr_amt = 0;
                $transc_type = 'DEBIT';
                $DEBIT_ENTRY = billing(
                    $db,
                    $emr,
                    $emr,
                    $insurance_no,
                    $ref_value,
                    $sale_sn,
                    $item_services,
                    $transc_type,
                    $dr_amt,
                    $cr_amt,
                    $bal,
                    $setdate,
                    $auth_code,
                    $bank_name,
                    $debit_gl_account,
                    $lg_ref_no,
                    0
                );

                if ($DEBIT_ENTRY !== 'success') {
                    throw new Exception('Error saving bill:' . $DEBIT_ENTRY);
                }

                if ($CREDIT_ENTRY == 'success' and $DEBIT_ENTRY == 'success') {

                    // Construct the DELETE query

                    // Delete any existing billing confirmations for the patient
                    $deleteSql = "DELETE FROM billing_dep_confirm WHERE hospitla_no = :emr";
                    $stmtDelete = $db->prepare($deleteSql);
                    $stmtDelete->bindParam(':emr', $emr, PDO::PARAM_STR);
                    $stmtDelete->execute();

                    // Fetch the latest debit transaction for the patient
                    $selectSql = "SELECT sn FROM chart_ledger WHERE hospital_no = :emr AND cr_amt > 0 ORDER BY sn DESC LIMIT 1";
                    $stmtSelect = $db->prepare($selectSql);
                    $stmtSelect->bindParam(':emr', $emr, PDO::PARAM_STR);
                    $stmtSelect->execute();

                    $sn = null;
                    if ($row = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                        $sn = $row['sn'];
                    }

                    $db->commit();

                    $response_main['message'] = 'Amount Deposited Into Patient Account.';
                    $response_main['status'] = '2';
                    $response_main['url'] = $sn;
                    echo json_encode($response_main);
                    exit;
                } else {


                    $response_main['message'] = 'Deposit Not Successful!';
                    $response_main['status'] = '1';
                    echo json_encode($response_main);
                    exit;
                }
            } else {

                $response_main['message'] = 'Invalid Ledger / Account Does Not Exist!';
                $response_main['status'] = '1';
                echo json_encode($response_main);
                exit;
            }
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $db->rollBack();
            $response_main['message'] = 'An Error Has Occurred: ' . $e->getMessage();
            $response_main['status'] = '1'; // Error status

            echo json_encode($response_main);
            exit;
        }
    } elseif ($transc_type == 'Transfer') {

        /// GIVER CODE
        $emr = $hosp_no_giver;
        $bank_name = '';
        $auth_amount = $_POST['auth_amount'];
        $lg_ref_no = time();
        $setdate = date('Y-m-d H:i');


        //// DEBIT THE PATIENT 
        $bal = 0;
        $item_services = 'TRANSFERED TO: ' . $hosp_no_beneficiary . '/' . $row_d['descrip'] . ' EMR: ' . $emr;
        $ref_value = 'cash';


        $dr_amt = $auth_amount;
        $cr_amt = 0;
        $transc_type = 'DEBIT';
        $DEBIT_ENTRY = billing(
            $db,
            $emr,
            $emr,
            $insurance_no,
            $ref_value,
            $sale_sn,
            $item_services,
            $transc_type,
            $dr_amt,
            $cr_amt,
            $bal,
            $setdate,
            $auth_code,
            $bank_name,
            $wallet_account,
            $lg_ref_no,
            0
        );

        /// BENEFICIARY
        $emr = $hosp_no_beneficiary;
        $bal = 0;
        $item_services = 'TRANSFERED FROM: ' . $hosp_no_giver . '/ ' . $row_d['descrip'];
        $ref_value = $mode_pay;

        /// CREDIT THE PATIENT ACCOUNT


        $cr_amt = $auth_amount;
        $dr_amt = 0;
        $transc_type = 'CREDIT';
        $CREDIT_ENTRY = billing(
            $db,
            $emr,
            $emr,
            $insurance_no,
            $ref_value,
            $sale_sn,
            $item_services,
            $transc_type,
            $dr_amt,
            $cr_amt,
            $bal,
            $setdate,
            $auth_code,
            $bank_name,
            $wallet_account,
            $lg_ref_no,
            0
        );

        if ($CREDIT_ENTRY == 'success' and $DEBIT_ENTRY == 'success' and $insurance_no != '') {
            if ($auth_code != '') {
                $sub_add = voucher_update($db, $emr, $patient_name, $setdate, $auth_code);
            }
            $response_main['message'] = 'Transfered Was Successful!';
            echo json_encode($response_main);
            exit;
        } else {
            //// reverse and delete  /// auth_amount
            $delete_dt = "DELETE FROM chart_ledger WHERE hospital_no = :hospital_no AND sale_sn = :sale_sn";
            try {
                $stmt = $db->prepare($delete_dt);
                $stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
                $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                $stmt->execute();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }


            $response_main['message'] = 'Transfer Not Successful!';
            $response_main['status'] = '1';
            echo json_encode($response_main);
            exit;
        }
    } elseif ($transc_type == 'Refund') {

        $setdate = date('Y-m-d H:i');

        $emr = $hosp_no_giver;
        $general_credit_limit =    $_SESSION['credit_limit_status'];
        $items = call_current_balance($db, $emr, $general_credit_limit);
        $current_balance =  $items["current_balance"];
        $patient_name      = $items['patient_name'];
        $names             = $items['names'];
        $gender            = $items['gender'];
        $address           = $items['address'];
        $referral_name     = $items['referral_name'];
        $discount_set      = $items['discount_set'];
        $insurance_type    = $items['insurance_type'];
        $insurance_no      = $items['insurance_no'];
        $save_insurance_no = $items['save_insurance_no'];
        $wallet_amount     = $items['wallet_amount'];


        $item_services = 'REFUND/' . $row_d['descrip'] . ' EMR: ' . $emr;
        $ref_value = 'Refund';
        $lg_ref_no = time() . $emr;
        // Determine account name based on payment mode
        $accountName = ($mode_pay === 'POS' || $mode_pay === 'Transfer') && $bank_name !== ''
            ? $bank_name
            : 'Main Cash';

        // Fetch the account code from chart_accounts
        $stmt = $db->prepare("SELECT account_code FROM chart_accounts WHERE account_name = :account_name AND account_group = '5'");
        $stmt->bindParam(':account_name', $accountName, PDO::PARAM_STR);
        $stmt->execute();

        $debit_gl_account = '';
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $debit_gl_account = $row['account_code'];
        }


        if ($wallet_account != '' and $debit_gl_account != '' and $auth_amount > 0 and $insurance_no != '') {

            ///$insurance_no='';
            $dr_amt = $auth_amount;
            $cr_amt = 0;
            $transc_type = 'DEBIT';
            $DEBIT_ENTRY = billing(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                $transc_type,
                $dr_amt,
                $cr_amt,
                $bal,
                $setdate,
                $auth_code,
                $bank_name,
                $wallet_account,
                $lg_ref_no,
                0
            );

            $cr_amt = $auth_amount;
            $dr_amt = 0;
            $transc_type = 'CREDIT';
            $CREDIT_ENTRY = billing(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                $transc_type,
                $dr_amt,
                $cr_amt,
                $bal,
                $setdate,
                $auth_code,
                $bank_name,
                $debit_gl_account,
                $lg_ref_no,
                0
            );



            if ($CREDIT_ENTRY == 'success' and $DEBIT_ENTRY == 'success') {
                if ($auth_code != '') {
                    $sub_add = voucher_update($db, $emr, $patient_name, $setdate, $auth_code);
                }
                $stmt = $db->prepare("SELECT sn FROM chart_ledger WHERE hospital_no = :hospital_no AND cr_amt > 0 ORDER BY sn DESC LIMIT 1");

                try {
                    $stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
                    $stmt->execute();
                    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);
                    $sn = $row_d['sn'];
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }


                $response_main['message'] = 'Amount Refunded.';
                $response_main['status'] = '3';
                $response_main['url'] = $sn;
                echo json_encode($response_main);
                exit;
            } else {
                //// reverse and delete  /// auth_amount
                $delete_dt = "DELETE FROM chart_ledger WHERE lg_ref_no = :lg_ref_no";

                $stmt = $db->prepare($delete_dt);

                try {
                    $stmt->bindParam(':lg_ref_no', $lg_ref_no, PDO::PARAM_STR);
                    $stmt->execute();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }


                $response_main['message'] = 'Unable to Refund Amount';
                $response_main['status'] = '1';
                echo json_encode($response_main);
                exit;
            }
        } else {
            $response_main['message'] = 'Unable to Refund Amount';
            $response_main['status'] = '1';
            echo json_encode($response_main);
            exit;
        }
    }
}


function billing(
    $db,
    $app_no,
    $hosp_no,
    $hmo_no,
    $ref_value,
    $sale_sn,
    $item_services,
    $transc_type,
    $dr_amt,
    $cr_amt,
    $bal,
    $setdate,
    $auth_code,
    $bank_name,
    $account_no,
    $lg_ref_no,
    $patient_stt_status
) {
    // Prepare date variables
    $my_setdate = !empty($setdate) ? $setdate . ' ' . date('H:i') : date('Y-m-d H:i');
    $my_setdate2 = !empty($setdate) ? $setdate : date('Y-m-d');

    try {
        $stmt = $db->query("SELECT id FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
        $fiscal_year = $stmt->fetchColumn();

        // Check if the record already exists
        $stmt = $db->prepare("SELECT app_no FROM chart_ledger 
                                                              WHERE app_no = :app_no 
                                                              AND hospital_no = :hosp_no 
                                                              AND item_services = :item_services 
                                                              AND ref_value = :ref_value 
                                                              AND sale_sn = :sale_sn 
                                                              AND transc_type = :transc_type 
                                                              AND date_entry = :my_setdate 
                                                              AND dr_amt = :dr_amt 
                                                              AND cr_amt = :cr_amt 
                                                              AND account_no = :account_no");
        $stmt->execute([
            ':app_no' => $app_no,
            ':hosp_no' => $hosp_no,
            ':item_services' => $item_services,
            ':ref_value' => $ref_value,
            ':sale_sn' => $sale_sn,
            ':transc_type' => $transc_type,
            ':my_setdate' => $my_setdate,
            ':dr_amt' => $dr_amt,
            ':cr_amt' => $cr_amt,
            ':account_no' => $account_no,
        ]);

        if ($stmt->rowCount() == 0) {
            // Insert new record into chart_ledger table
            $insertSQL = "INSERT INTO chart_ledger (app_no, hospital_no, insurance_no, ref_value, sale_sn, item_services, bank_name,
                                                           transc_type, dr_amt, cr_amt, bal, prepared_by, date_entry, date_entry2, auth_code,
                                                           account_no, lg_ref_no, fiscal_year,patient_stt_status) 
                                                          VALUES 
                                                          (:app_no, :hosp_no, :insurance_no, :ref_value, :sale_sn, :item_services, :bank_name,
                           :transc_type, :dr_amt, :cr_amt, :bal, :prepared_by, :date_entry, :date_entry2, :auth_code,
                           :account_no, :lg_ref_no, :fiscal_year, :patient_stt_status)";

            $stmt = $db->prepare($insertSQL);
            $stmt->execute([
                ':app_no' => $app_no,
                ':hosp_no' => $hosp_no,
                ':insurance_no' => $hmo_no,
                ':ref_value' => $ref_value,
                ':sale_sn' => $sale_sn,
                ':item_services' => $item_services,
                ':bank_name' => $bank_name,
                ':transc_type' => $transc_type,
                ':dr_amt' => $dr_amt,
                ':cr_amt' => $cr_amt,
                ':bal' => $bal,
                ':prepared_by' => $_SESSION['fullname'], // Default to 'unknown' if not set
                ':date_entry' => $my_setdate2,
                ':date_entry2' => $my_setdate2,
                ':auth_code' => $auth_code,
                ':account_no' => $account_no,
                ':lg_ref_no' => $lg_ref_no,
                ':fiscal_year' => $fiscal_year,
                ':patient_stt_status' => $patient_stt_status
            ]);

            // Check if the insert was successful
            if ($stmt->rowCount() > 0) {
                return 'success';
            } else {
                return 'error2'; // Insert failed, though this is unlikely with a prepared statement
            }
        } else {
            return 'exists'; // Record already exists
        }
    } catch (Exception $e) {
        // Log the error for debugging purposes
        error_log($e->getMessage());
        return $e->getMessage(); ///'error'; // Return a generic error message
    }
}
