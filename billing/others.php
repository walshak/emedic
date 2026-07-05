<?php

$outstanding_balance = 0;
$cash = $_POST['cash'];
$cash_split = $_POST['cash_split'];
$hmo_no = $_POST['save_insurance_no'];
$app_no = $_POST['emr'];
$hosp_no = $_POST['emr'];
$grp_idv_no = $_POST['grp_idv_no'];
$bank_name = $_POST['bank_name'];
$bank_name_2 = $_POST['bank_name'];

$dsc_voucher_set = 0;
$rdd = rand(0, 10);
$lg_ref_no = time() . $rdd . $emr;
$SPLIT_ = '';

$words = explode(' ', $patient_name);
if (count($words) > 3) {
    array_pop($words);
    $patient_name = implode(' ', $words);
}


try {
    // Start the transaction
    $db->beginTransaction();

    //==============WARNINGGGINGGG XXXXXXXXX !!!!!!!!!!!!!!===== CHECK FOR DEBTS FISRT

    $general_credit_limit =    $_SESSION['credit_limit_status'];
    $items = call_current_balance($db, $emr, $general_credit_limit);
    $initial_current_balance   = $items["current_balance"];
    $acct_recievabl_bal   = $items["Patient_Bill_Receivable"];
    $wallet_account   = $items["wallet_account"];
    $referral_sn   = $items["referral_sn"];

    // sanitize: remove commas and cast to float
    $initial_current_balance = floatval(str_replace(',', '', $initial_current_balance));
    $acct_recievabl_bal = floatval(str_replace(',', '', $acct_recievabl_bal));

    // compare with tolerance
    $epsilon = 0.005; // adjust tolerance as needed (0.005 tolerates cent-level rounding)
    if ($initial_current_balance < 0 && abs(abs($initial_current_balance) - $acct_recievabl_bal) < $epsilon) {

        ///CHECK DEBT ACCT & post DEBT account_code=1502 =====================/////===================
        ///-------------------------------------------------------------------/////-------------------
        if ($acct_recievabl_bal > 0) {
            if ($acct_recievabl_bal >= $cash) {
                $amt_settle = 0;
                $acct_recievabl_bal = $cash;
            } else {
                $amt_settle = $cash - $acct_recievabl_bal;
            }
            $patient_stt_status = 1;
            $item_services = 'AR DEBT PAID/ ' . $patient_name . ' (' . $hosp_no . ')';
        }
    } else {
        $patient_stt_status = 0;
        $item_services = $service_group_desc . '/ ' . $patient_name . ' (' . $hosp_no . ')';
    }

    ////===========================END OF CHECKING DEBT ===============================================



    ///throw new Exception('Error saving bill: ' . $insurance_no);


    // Handle cash split transactions
    if (($paymethod == 'CASHPOS' or $paymethod == 'CASHTransfer') and $cash_split > 0) {

        if ($cash_split >= $cash) {
            $response_main['message'] = 'An Error Has Occured. Cash Split Should Not Be More Than or Equal to Total Sum!';
            $response_main['status'] = '1';   //////=================================================== 1 ERROR
            echo  json_encode($response_main);
            exit;
        }

        $bal = $cash_split;
        $new_amt_payment = $bal;
        $ref = $paymethod; /// CASHPOS OR CHASH TRANSFER
        $ref_value = 'cash';
        $sale_sn = $transaction_code;
        $payment_method = 'cash';

        $stmt = $db->prepare("
        SELECT account_code 
        FROM chart_accounts 
        WHERE account_name = :account_name 
          AND account_group = :account_group 
        LIMIT 1");
        $stmt->execute([
            ':account_name'  => 'Main Cash',
            ':account_group' => '5'
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $debit_gl_account = $row ? $row['account_code'] : '';

        if ($debit_gl_account !== '' && $wallet_account !== '' && $insurance_no !== '') {
            $item_services = 'CASH: ' . $service_group_desc . '/ ' . $patient_name . ' (' . $hosp_no . ')';
            $DEBIT_ENTRY = billing(
                $db,
                $app_no,
                $hosp_no,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                'DEBIT',
                $cash_split,
                0,
                0,
                $value_date,
                $auth_code,
                null,
                $debit_gl_account,
                $lg_ref_no,
                0
            );

            if ($DEBIT_ENTRY !== 'success') {
                throw new Exception('Error saving bill: ');
            }

            // Credit the patient account
            $CREDIT_ENTRY = billing(
                $db,
                $app_no,
                $hosp_no,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                'CREDIT',
                0,
                $cash_split,
                $bal,
                $value_date,
                $auth_code,
                null,
                $wallet_account,
                $lg_ref_no,
                $patient_stt_status
            );

            if ($CREDIT_ENTRY !== 'success') {
                throw new Exception('Error saving bill: ');
            }




            $SPLIT_ = 'okay';
        } else {
            throw new Exception('Error: Missing account information.');
        }
    } else {
        $SPLIT_ = 'okay';
    }

    /// END OF SPLITING CODE =================================================================================		

    // Handle cash transactions
    if ($cash > 0) {

        $bank_name = $bank_name_2;
        if (($paymethod == 'CASHPOS' or $paymethod == 'CASHTransfer') and $cash_split > 0) {
            $dr_amt = $cash - $cash_split;
            $bal = $bal + $dr_amt;
        } else {
            $dr_amt = $cash;
            $bal = $cash + $outstanding_balance;
        }

        $new_amt_payment = $bal;

        if ($ref_no != '') {
            $ref = "Ref: " . $ref_no . ' /BNK: ' . $bank_name;
        } else {
            $ref = '';
        }

        if ($paymethod == 'CASHPOS' and $cash_split > 0) {
            $ref_value = 'POS';
        } elseif ($paymethod == 'CASHTransfer' and $cash_split > 0) {
            $ref_value = 'Transfer';
        } else {
            $ref_value = $_POST['paymethod'];
        }

        $sale_sn = $transaction_code;

        //// GET PAYMENT METHOD /////
        $account_name = '';


        if ($initial_current_balance < 0 && round(abs($initial_current_balance), 2) > round($cash, 2)) {
            $service_group_desc = 'Debt Settlement';
        }


        if (
            in_array($paymethod, ['POS', 'CASHPOS', 'CASHTransfer', 'Transfer'])
            && !empty($bank_name)
        ) {
            $account_name = $bank_name;
            $item_services = 'Bank/' . $service_group_desc . '/ ' . $patient_name . ' (' . $hosp_no . ')';
        } elseif (
            !in_array($paymethod, ['POS', 'CASHPOS', 'CASHTransfer', 'Transfer'])
            || empty($bank_name)
        ) {
            $account_name = 'Main Cash';
            $item_services = 'CASH/' . $service_group_desc . '/ ' . $patient_name . ' (' . $hosp_no . ')';
        }


        $stmt = $db->prepare("
        SELECT account_code 
        FROM chart_accounts 
        WHERE account_name = :account_name 
          AND account_group = '5'
        LIMIT 1");

        $stmt->execute([':account_name' => $account_name]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $debit_gl_account = $row ? $row['account_code'] : '';

        // Verify required fields before proceeding
        if (!empty($debit_gl_account) && !empty($wallet_account) && !empty($insurance_no)) {

            $bal_zero = 0;
            $DEBIT_ENTRY = billing(
                $db,
                $app_no,
                $hosp_no,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                'DEBIT',
                $dr_amt,
                0,
                $bal_zero,
                $value_date,
                $auth_code,
                $bank_name,
                $debit_gl_account,
                $lg_ref_no,
                0
            );

            if ($DEBIT_ENTRY !== 'success') {
                throw new Exception('Error saving bill: ');
            }

            // Credit the patient account
            $cr_amt = $dr_amt;
            $CREDIT_ENTRY = billing(
                $db,
                $app_no,
                $hosp_no,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                'CREDIT',
                0,
                $cr_amt,
                $bal,
                $value_date,
                $auth_code,
                $bank_name,
                $wallet_account,
                $lg_ref_no,
                $patient_stt_status
            );

            if ($CREDIT_ENTRY !== 'success') {
                throw new Exception('Error saving bill: ');
            }
        }
    } elseif (($paymethod == 'CASHPOS' or $paymethod == 'CASHTransfer') and $cash_split > 0 and $cash <= 0) {
        $new_amt_payment = $new_amt_payment;
    } else {
        $new_amt_payment = $current_balance;
    }


    // Handle other operations...
    // Handle other operations...

    if ($initial_current_balance < 0 && abs(abs($initial_current_balance) - $acct_recievabl_bal) < $epsilon) {

        if ($acct_recievabl_bal != 0) {  /// there was somthing beforee
            ///========================== ACCOUNT RECIEVABLE ==================================
            $item_services = 'AR Amount Settled' . '(' . $emr . ')';
            $credit_gl_account_acct_recievable = 1502;
            $CREDIT_ENTRY = billing(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $ref_value,
                0,
                $item_services,
                'CREDIT',
                0,
                $acct_recievabl_bal,
                0,
                $setdate,
                $auth_code,
                null,
                $credit_gl_account_acct_recievable,
                $lg_ref_no,
                0
            );

            if ($CREDIT_ENTRY !== 'success') {
                throw new Exception('Error Saving AR ERROR ');
            }
        }
        ///========================== ACCOUNT RECIEVABLE ==================================

        if ($amt_settle > 0) {
            // Common values
            $item_services = 'AR CREDIT BAL:' . $ref . '(' . $emr . ')';
            $CREDIT_ENTRY = billing(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $ref_value,
                $sale_sn,
                $item_services,
                'CREDIT',
                0,
                $amt_settle,
                0,
                $setdate,
                $auth_code,
                $bank_name,
                '2121',
                $lg_ref_no,
                2
            );

            if ($CREDIT_ENTRY !== 'success') {
                throw new Exception('Error saving bill: AR CREDIT ERROR.');
            }
        }
        ///========================== DEPOSIT AMT  ==================================
    }


    if (!empty($_REQUEST['item']) && $DEBIT_ENTRY == 'success' && $SPLIT_ == 'okay' && $insurance_no != '') {
        $ref_value = null;
        $pay_mode = null;
        $list = $_POST["item"];
        $batch_name_array = explode(",", $list);
        $result_count = count($batch_name_array);
        $sv = 0;

        for ($i = 0; $i < $result_count; $i++) {
            $inv_id = $batch_name_array[$i];
            $break = explode("__", $inv_id);
            $sale_sn = $break[0];
            $pay = $break[1];
            $item_services = $break[2];
            $discount = !empty($break[4]) ? $break[4] : 0;
            $charge   = !empty($break[5]) ? $break[5] : 0;
            $dura = $break[6];
            $post_type = $break[7];
            $count_bal = $break[8];
            $dsc_chr_set = $break[9];
            $cat_type = $break[10];
            $cr = $break[11];
            $service_group = $break[12];
            $departmentName = $break[13];
            $service_type = $break[14];
            $sn_service = $break[15];

            $credit_gl_account = null;

            // Step 1: Try to get credit account based on department name
            if (!empty($departmentName)) {
                $stmt = $db->prepare("
							SELECT account_code 
							FROM chart_accounts 
							WHERE account_name = :account_name
							LIMIT 1");
                $stmt->execute([':account_name' => $departmentName]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $credit_gl_account = $row['account_code'];
                }
            }

            // Step 2: Fallback using service group/category logic if not found
            if ($credit_gl_account === null) {
                $credit_gl_account = getCreditGLAccount($db, $service_group, $cat_type);
            }

            $ref_value = 'Payment: Cashier Posting';
            $print_afterpay = 1;

            if ($credit_gl_account != '') {

                $general_credit_limit =    $_SESSION['credit_limit_status'];
                $items = call_current_balance($db, $hosp_no, $general_credit_limit);
                $MYwallet_amount   = $items["current_balance"];

                if ($pay > 0 && $pay <= $MYwallet_amount) {

                    $stmt_checkExist = $db->query("SELECT drug_sn FROM patient_ap_services WHERE sn='$sale_sn' and paystatus=0 and pay>0");
                    if ($stmt_checkExist->rowCount() > 0) {
                        $row__ = $stmt_checkExist->fetch(PDO::FETCH_ASSOC);
                        $drug_sn = $row__['drug_sn'];

                        // Handle discount and charge logic
                        if ($dsc_chr_set == 1 && $charge > 0) {
                            $discount_pay = $pay - $charge;
                            $CREDIT_ENTRY_2 = billing(
                                $db,
                                $app_no,
                                $hosp_no,
                                $insurance_no,
                                $ref_value,
                                $sale_sn,
                                $item_services,
                                'CREDIT',
                                0,
                                $charge,
                                0,
                                $value_date,
                                $auth_code,
                                null,
                                $credit_gl_account_charge,
                                $lg_ref_no,
                                0
                            );
                            if ($CREDIT_ENTRY_2 !== 'success') {
                                throw new Exception('Error CREDIT CHARGE ');
                            }
                            $DEBIT_ENTRY_2 = 'success';
                        } elseif ($discount == 0) {
                            $CREDIT_ENTRY_2 = 'success';
                            $discount_pay = $pay;
                        }

                        // Handle discount entries
                        if ($dsc_chr_set == 1 && $discount > 0) {
                            $discount_pay = $pay + $discount;
                            $dr_amt = $discount;
                            $DEBIT_ENTRY_2 = billing(
                                $db,
                                $emr,
                                $emr,
                                $discount_insurance_no,
                                $ref_value,
                                $sale_sn,
                                $item_services,
                                'DEBIT',
                                $discount,
                                0,
                                0,
                                $value_date,
                                $auth_code,
                                null,
                                $debit_gl_account_discount,
                                $lg_ref_no,
                                0
                            );

                            if ($DEBIT_ENTRY_2 !== 'success') {
                                throw new Exception('Error saving bill: ');
                            }

                            $CREDIT_ENTRY_2 = 'success';
                        } elseif ($charge == 0) {
                            $DEBIT_ENTRY_2 = 'success';
                            $discount_pay = $pay;
                        }

                        // Final credit entry for the discount payment
                        $dr_amt = 0;
                        $cr_amt = $discount_pay;
                        $CREDIT_ENTRY = billing(
                            $db,
                            $app_no,
                            $hosp_no,
                            $insurance_no,
                            $ref_value,
                            $sale_sn,
                            $item_services,
                            'CREDIT',
                            0,
                            $cr_amt,
                            0,
                            $value_date,
                            $auth_code,
                            null,
                            $credit_gl_account,
                            $lg_ref_no,
                            0
                        );

                        if ($CREDIT_ENTRY !== 'success') {
                            throw new Exception('Error saving bill: ');
                        }

                        // Debit entry for the payment
                        $dr_amt = $pay;
                        $DEBIT_ENTRY = billing(
                            $db,
                            $app_no,
                            $hosp_no,
                            $insurance_no,
                            $ref_value,
                            $sale_sn,
                            $item_services,
                            'DEBIT',
                            $dr_amt,
                            0,
                            0,
                            $value_date,
                            $auth_code,
                            $bank_name,
                            $wallet_account,
                            $lg_ref_no,
                            0
                        );

                        if ($DEBIT_ENTRY !== 'success') {
                            throw new Exception('Error saving bill: ');
                        }

                        // Check if all entries were successful
                        if (
                            $CREDIT_ENTRY == 'success' && $DEBIT_ENTRY == 'success' &&
                            $DEBIT_ENTRY_2 == 'success' && $CREDIT_ENTRY_2 == 'success' && $insurance_no != ''
                        ) {
                            // Update patient_ap_services
                            $wallet_debt_bill_to_acct = 'RECEP'; // Means receipt of payment
                            $paystatus = '1';
                            $cr = '0';
                            $pay_mode = 'cash';
                            date_default_timezone_set('Africa/Lagos');
                            $setdate = $value_date . ' ' . date('H:i');
                            $updateSQL = "UPDATE patient_ap_services 
                                    SET paystatus = :paystatus,
                                        pay_mode = :pay_mode,
                                        cr = :cr,
                                        transact_date = :transact_date,
                                        invoice_by = :invoice_by,
                                        pay = :pay,
                                        discount = :discount,
                                        add_charge = :add_charge,
                                        payment_remarks = :payment_remarks,
                                        ledger_TX = :ledger_TX,
                                        wallet_debt_bill_to_acct = :wallet_debt_bill_to_acct,
                                        who_process_paystatus = :who_process_paystatus
                                    WHERE hospital_no = :hospital_no 
                                    AND sn = :sn 
                                    AND paystatus = '0'";

                            $stmt = $db->prepare($updateSQL);
                            $stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_INT);
                            $stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_INT);
                            $stmt->bindParam(':cr', $cr, PDO::PARAM_INT);
                            $stmt->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
                            $stmt->bindParam(':invoice_by', $_SESSION['fullname'], PDO::PARAM_STR);
                            $stmt->bindParam(':pay', $pay, PDO::PARAM_STR);
                            $stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
                            $stmt->bindParam(':add_charge', $charge, PDO::PARAM_STR);
                            $stmt->bindParam(':payment_remarks', $payment_remarks, PDO::PARAM_STR);
                            $stmt->bindParam(':ledger_TX', $lg_ref_no, PDO::PARAM_STR);
                            $stmt->bindParam(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct, PDO::PARAM_STR);
                            $stmt->bindParam(':who_process_paystatus', $_SESSION['EmployeeCode'], PDO::PARAM_STR);
                            $stmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
                            $stmt->bindParam(':sn', $sale_sn, PDO::PARAM_STR);
                            $stmt->execute();

                            if ($updateSQL) {
                                $insertSQL = "INSERT INTO saleprint(hos_no,sale_no) VALUES ('$emr','$sale_sn')";
                                $db->exec($insertSQL);

                                // Handle discounts and charges
                                if ($dsc_chr_set == 1) {
                                    if ($dura == 'Once') {
                                        $status = 'Finish';
                                        $status2 = '1';
                                        $count_bal = '';
                                    } elseif ($dura == 'Limited') {
                                        $count_bal = $count_bal - 1;
                                        if ($count_bal <= 0) {
                                            $status = 'Finish';
                                            $status2 = '1';
                                        } else {
                                            $status = 'On-going';
                                            $status2 = '0';
                                        }
                                    } elseif ($dura == 'Always') {
                                        $status = 'On-going';
                                        $status2 = '0';
                                        $count_bal = '';
                                    }


                                    if ($insurance_type == 'EX' || $insurance_no == 'EX0001') {



                                        $individual_group_no = $referral_sn;




                                        if ($service_type == 'SPECIFY') {
                                            $updateSQL = "UPDATE patient_discount_services SET status='$status2', count_bal='$count_bal' WHERE sn='$sn_service' AND individual_group_no IN ('$individual_group_no','$hosp_no')";
                                            $db->exec($updateSQL);
                                        } else {
                                            $updateSQL = "UPDATE patient_discount SET status='$status', count_bal='$count_bal' WHERE individual_group_no IN ('$individual_group_no','$hosp_no')";
                                            $db->exec($updateSQL);
                                        }
                                    } else if ($insurance_no == '1000') {
                                        $individual_group_no = $hosp_no;

                                        if ($service_type == 'SPECIFY') {
                                            $updateSQL = "UPDATE patient_discount_services SET status='$status2', count_bal='$count_bal' WHERE sn='$sn_service' AND individual_group_no='$individual_group_no'";
                                            $db->exec($updateSQL);
                                        } else {
                                            $updateSQL = "UPDATE patient_discount SET status='$status', count_bal='$count_bal' WHERE individual_group_no='$individual_group_no'";
                                            $db->exec($updateSQL);
                                        }
                                    } else {
                                        $individual_group_no = $insurance_no;



                                        if ($service_type == 'SPECIFY') {
                                            $updateSQL = "UPDATE patient_discount_services SET status='$status2', count_bal='$count_bal' WHERE sn='$sn_service' AND individual_group_no='$individual_group_no'";
                                            $db->exec($updateSQL);
                                        } else {
                                            $updateSQL = "UPDATE patient_discount SET status='$status', count_bal='$count_bal' WHERE individual_group_no='$individual_group_no'";
                                            $db->exec($updateSQL);
                                        }
                                    }
                                }
                            }

                            if ($service_group == 'Laboratory' or $service_group == 'Radiology') {
                                $updateSQL3 = "UPDATE lab_manage SET result_on_credit = 0,date_time_pay = :date_time_pay WHERE patient = :hospital_no AND labrequest_no = :labrequest_no";
                                $stmt = $db->prepare($updateSQL3);
                                $stmt->bindParam(':date_time_pay', $setdate, PDO::PARAM_STR);
                                $stmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
                                $stmt->bindParam(':labrequest_no', $drug_sn, PDO::PARAM_STR);
                                $stmt->execute();
                            }
                        }

                        if ($pay <= $new_amt_payment && $pay > 0) {
                            $bal = $new_amt_payment - $pay;
                            $new_amt_payment = $bal;
                            $print_items[] = $sale_sn;
                        }
                    }
                }
            }
        }

        if ($initial_current_balance < 0 && $acct_recievabl_bal > 0 && $pay_mode == null) {
            $sql = "SELECT sn 
							FROM chart_ledger 
							WHERE hospital_no = :emr 
							AND cr_amt > 0
							$condition
							ORDER BY sn DESC 
							LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
            $stmt->execute();

            $sn = ($row = $stmt->fetch(PDO::FETCH_ASSOC)) ? $row['sn'] : null;
            $response_main['message'] = 'Unable to Process Item(s) Due to Existing DEBT!';
            $response_main['status'] = '3';
            $response_main['url'] = $sn;
        } else {

            $response_main['message'] = 'Item(s) Has been Posted Successfully!';
            $response_main['status'] = '2';
        }



        // Commit the transaction if everything is successful
        $db->commit();

        echo json_encode($response_main);
        exit;
    } else {
        // Handle the case where the initial conditions are not met
        throw new Exception('Transaction Error! Something Went Wrong With Transaction Ledger');
    }
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    $db->rollBack();
    $response_main['message'] = 'An Error Has Occurred: ' . $e->getMessage();
    $response_main['status'] = '1'; // Error status

    echo json_encode($response_main);
    exit;
}
