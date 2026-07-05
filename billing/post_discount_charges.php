<?php

if (!empty($_REQUEST['item'])) {

    $lg_ref_no = time() . $emr;
    $hosp_no = $emr;

    $list = $_POST["item"];
    $batch_name_array = explode(",", $list);
    $result_count = count($batch_name_array);
    $sv = 0;

    try {

        // Start transaction
        $db->beginTransaction();

        for ($i = 0; $i < $result_count; $i++) {
            $inv_id = $batch_name_array[$i];
            $break = explode("__", $inv_id);
            $sale_sn = $break[0];
            $sn = $break[0];
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
							LIMIT 1	");
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

            $ref_value = 'Discount';
            $item_services = $patient_name .  ' (' . $emr . ') Discount:' . $item_services;
            $bal = null;
            $bank_name = null;


            /// DEBIT DISCOUNT ////
            $stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='DISCOUNT TO PATIENTS'");
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $debit_gl_account_discount = $row['account_code'];
            } else {

                $response_main['message'] = 'Transaction Error! This Ledger Does Not Exist:  DISCOUNT TO PATIENTS ';
                $response_main['status'] = '1';
                echo  json_encode($response_main);
                exit;
            }

            if ($dsc_chr_set == 1 and $discount > 0) {

                $dr_amt = $discount;
                $DEBIT_ENTRY = billing(
                    $db,
                    $emr,
                    $emr,
                    $discount_insurance_no,
                    $ref_value,
                    $sale_sn,
                    $item_services,
                    'DEBIT',
                    $dr_amt,
                    0,
                    $bal,
                    $value_date,
                    $auth_code,
                    $bank_name,
                    $debit_gl_account_discount,
                    $lg_ref_no,
                    0
                );

                if ($DEBIT_ENTRY !== 'success') {
                    throw new Exception('Error saving bill: ');
                }

                $cr_amt = $discount;
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
                    $cr_amt,
                    $bal,
                    $value_date,
                    $auth_code,
                    $bank_name,
                    $credit_gl_account,
                    $lg_ref_no,
                    0
                );

                if ($CREDIT_ENTRY !== 'success') {
                    throw new Exception('Error saving bill: ');
                }
            }


            //// CLEAR PATIENT SERVICES TABLE

            if ($insurance_no != '') {

                $stmt_checkExist = $db->query("SELECT drug_sn FROM patient_ap_services WHERE sn='$sale_sn' and paystatus=0 and pay>0");
                if ($stmt_checkExist->rowCount() > 0) {
                    $row__ = $stmt_checkExist->fetch(PDO::FETCH_ASSOC);
                    $drug_sn = $row__['drug_sn'];


                    $wallet_debt_bill_to_acct = 'DISC'; ///// MEANS RECIEPT OF PAYMENT /////
                    $paystatus = '1';
                    $cr = '0';
                    //$setdate = date('Y-m-d H:i');
                    $setdate = $value_date . ' ' . date('H:i');
                    $updateSQL = "UPDATE patient_ap_services 
                                    SET paystatus = :paystatus,
                                        cr = :cr,
                                        transact_date = :transact_date,
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
                    $stmt->bindParam(':cr', $cr, PDO::PARAM_INT);
                    $stmt->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
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

                        if ($service_group == 'Laboratory' or $service_group == 'Radiology') {
                            $updateSQL3 = "UPDATE lab_manage SET result_on_credit = 0,date_time_pay = :date_time_pay WHERE patient = :hospital_no                                AND labrequest_no = :labrequest_no";
                            $stmt = $db->prepare($updateSQL3);
                            $stmt->bindParam(':date_time_pay', $setdate, PDO::PARAM_STR);
                            $stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
                            $stmt->bindParam(':labrequest_no', $drug_sn, PDO::PARAM_STR);
                            $stmt->execute();
                        }

                        /// DISCOUNT & CHARGES
                        //---------------------------------	
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

                            if ($service_type == 'SPECIFY') {
                                $updateSQL = "UPDATE patient_discount_services SET status='$status', count_bal='$count_bal' WHERE sn='$sn_service'";
                                $db->exec($updateSQL);
                            } else {
                                $updateSQL = "UPDATE patient_discount SET status='$status', count_bal='$count_bal' WHERE individual_group_no='$hosp_no'";
                                $db->exec($updateSQL);
                            }
                        }
                        /////===================================== DISCOUNT & CHARGES ==========================================	
                    }
                }
            } else {
                throw new Exception('Error: Insurance Number Invalid! ');
            }
        }

        // Commit the transaction if all operations are successful
        $db->commit();

        $response_main['message'] = 'Item(s) Has been Posted Successfully!';
        $response_main['status'] = '0';
        echo json_encode($response_main);
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $db->rollBack();
        error_log($e->getMessage());

        // Return error response
        $response_main['message'] = 'Transaction Error! Something Went Wrong: ' . $e->getMessage();
        $response_main['status'] = '1';
        echo json_encode($response_main);
        exit;
    }
}
