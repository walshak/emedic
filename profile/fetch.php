<?php

session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");
$setdate = date('Y-m-d H:i:s');

$response = array(
    'total_qty_test' => '',
    'main_msg' => '',
    'total_consmbl_used' => '',
    'qty_deduct_status' => '0',
    'consumable_status' => ''
);

$response2 = array(
    'detail' => '',
    'detail2' => '',
    'msg' => ''
);

if (isset($_POST["confirm_patient"])) {
    $hosp_number = $_POST["confirm_patient"];
    $stock_sn_sn = $_POST["stock_sn_sn"];
    $new_qty = $_POST["new_qty"];

    $stmt = $db->query("select insurance from enrollee where hospital_no='$hosp_number'");
    if ($stmt->rowCount() > 0) {
        $emr = $hosp_number;
        $general_credit_limit =	$_SESSION['credit_limit_status'];
        $items = call_current_balance($db, $emr, $general_credit_limit);
        $current_balance =  $items["current_balance"];
        $patient_name      = $items['patient_name'];
        $nhis_no           = $items['nhis_no'];
        $names             = $items['names'];
        $surname           = $items['surname'];
        $fname             = $items['fname'];
        $oname             = $items['oname'];
        $referral_name     = $items['referral_name'];
        $discount_set      = $items['discount_set'];
        $insurance_type    = $items['insurance_type'];
        $insurance_no      = $items['insurance_no'];
        $save_insurance_no = $items['save_insurance_no'];
        $wallet_amount     = $items['wallet_amount'];
        $add_minus         = $items['add_minus'];
        $interest          = $items['interest'];

        $stmt_stock = $db->query("select 
coverage,insurance_type,cash_price,hosp_price,nhis_price,stock_table,product_name 
from stock_table where sn='$stock_sn_sn'");
        if ($stmt_stock->rowCount() > 0) {
            $rww = $stmt_stock->fetch(PDO::FETCH_ASSOC);

            $nhis_price = $rww['nhis_price'] * $new_qty;
            $hosp_price = $rww['hosp_price'] * $new_qty;
            $cash_price = $rww['cash_price'] * $new_qty;
            $price_table = $rww['stock_table'];
            $hosp_price_2 = $rww['hosp_price'];
            $item_service = $rww['product_name'];
            $coverage = $rww['coverage'];
            $part = explode("/", $coverage);
            $private = $part[0];
            $nhis = $part[1];
            $nhis = strtoupper($nhis);
            $insurance_type = $rww['insurance_type'];
            $hos_no = $hosp_number;

            $insurance = $insurance_type;
            $target_sn = $stock_sn_sn;
            $NHIS_DRUG_CONSUMBL_STATE = 1;
            $_tariff_table = "hmo_stocks_tariff";

            include_once '../inc/price_calc.php';

            //echo $amt_paying;

            $stmt = $db->query("SELECT appt_no FROM apptm WHERE hospital_no='$hos_no' ORDER BY sn DESC LIMIT 1");
            $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
            $appt_no = $rwx['appt_no'];
            ///===============================================

            $details = $pay_mode . '__' . $claim_amt . '__' . $access . '__' . $amt_paying . '__' . $appt_no . '__' . $price_table . '__' . $hosp_price_2 . '__' . $item_service;
            $response2['detail'] = $details;
            $response2['detail2'] = "<hr><table width='100%'><tr><td><strong>Name:</strong> $patient_name</td><td><strong>Amount Paying: </strong>$amt_paying</td><td><strong>Claim: </strong> $claim_amt</td></tr></table>";
            $response2['msg'] = '';

            echo  json_encode($response2);
        }
    } else {


        $response2['msg'] = 'Record Not Available!';
        echo  json_encode($response2);
    }
}

if (isset($_POST["check_for_consumble"])) {

    $check_for_consumble = $_POST["check_for_consumble"];
    $stock_sn_sn = $_POST["stock_sn_sn"];
    $dept_id = $_SESSION["dept_id"];
    $yet_to_clear = 0;
    $over_all_qty_tests = 0;

    /// get unit of measuremenr
    $stmt = $db->query("select ml_1sheet from stock_table where sn='$stock_sn_sn'");
    $rww = $stmt->fetch(PDO::FETCH_ASSOC);
    $ml_1sheet = $rww['ml_1sheet'];
    /// get stock balance

    $stmtx = $db->query("SELECT bal FROM stock_table_inven 
where cust_patient_id='$dept_id' and stock_sn='$stock_sn_sn' ORDER BY sn DESC LIMIT 1");
    if ($stmtx->rowCount() > 0) {
        $rocw = $stmtx->fetch(PDO::FETCH_ASSOC);
        $bal = $rocw['bal'];
    } else {
        $bal = 0;
    }

    $tt_bal_mill_sheet = $ml_1sheet * $bal;

    $stmt = $db->query("select * from lab_test_consumble where consumable_no='$stock_sn_sn'");
    if ($stmt->rowCount() > 0) {
        while ($rww = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lab_test_no = $rww['lab_test_no'];
            $qty_test = $rww['qty_test'];


            $drip_per = $ml_1sheet / $qty_test;
            $qty_reached = $drip_per * $qty_test;

            ////$over_all_qty_tests=$over_all_qty_tests + $qty_test;

            /// loop through the test conducted using this particular stock ////
            $stmt_list_test = $db->query("select test_id from lab_manage where test_id='$lab_test_no' and data_capture_status='approve' and consumble_lock='0'");
            if ($stmt_list_test->rowCount() > 0) {
                /// get to total test yet to be accounted for consumble

                $test_conducted = $stmt_list_test->rowCount();
                $consmb_used = $drip_per * $test_conducted;
                $total_drip = $total_drip + $consmb_used;

                ///$yet_to_clear = $yet_to_clear + $stmt_list_test->rowCount();

            }
        }

        $diff_used = $tt_bal_mill_sheet - $total_drip;
        if ($diff_used == 0) {
            $display_qty_balance = 'Qty Remaining: 0';
        } elseif ($diff_used > 0) {
            $back_to_qty = $diff_used / $ml_1sheet;

            if (fmod($back_to_qty, 1) !== 0.00) {
                // your code if its decimals has a value
                $pp = explode(".", $back_to_qty);
                $qty_bal = $pp[0];
                $Qty_used = $bal - $qty_bal;
                ////
                $ml_sheet_bal = $Qty_used * $ml_1sheet;
                $ml_sheet_bal = $ml_sheet_bal - $total_drip;
                $display_qty_balance = 'Qty Remaining: ' . $qty_bal . '/' . $ml_sheet_bal;
            } else {
                // your code if the decimals are .00, or is an integer
                $display_qty_balance = 'Qty Remaining: ' . $back_to_qty;
            }
        } else {
            $diff_used = $total_drip - $tt_bal_mill_sheet;
            $display_qty_balance = 'Qty Remaining: ' . $diff_used;
        }


        /// used quantity ///
        if ($total_drip < $ml_1sheet) {
            $display_qty_used = 'Qty Used: ' . $total_drip;
        } else {
            ///
            $back_to_qty_used = $total_drip / $ml_1sheet;
            if (fmod($back_to_qty_used, 1) !== 0.00) {
                $pp2 = explode(".", $back_to_qty_used);
                $qty_balx = $pp2[0];
                $mil_used = $qty_balx * $ml_1sheet;
                $mil_used = $mil_used - $total_drip;
            } else {
                $qty_balx = $back_to_qty_used;
                $mil_used = '';
            }
            $display_qty_used = 'Qty Used: ' . $qty_balx . '/' . $mil_used;
        }

        $main_msg = $display_qty_used . '<br>' . $display_qty_balance;

        if ($qty_reached >= $total_drip) {
            $response['qty_deduct_status'] = '0';
        } else {
            $response['qty_deduct_status'] = '1';
        }

        $response['consumable_status'] = '1';
        $response['total_test_conduct'] = $test_conducted;
        //$response['total_test_conduct']=$qty_reached . '-' . $total_drip;
        $response['main_msg'] = $main_msg;
    } else {
        //// no consumble set here ////
        $response['consumable_status'] = '0';
        $response['total_test_conduct'] = '0';
        $response['main_msg'] = '';
    }

    echo  json_encode($response);
}



if (isset($_POST["final_submission"])) {

    $stock_sn = $_POST["final_submission"];
    $dept_id = $_POST["dept_id"];
    $new_qty = $_POST["new_qty"];
    $qty = $_POST["new_qty"];
    $fullname = $_POST["fullname"];
    $billable_detail = $_POST["billable_detail"];
    $deduct_consumable = $_POST["deduct_consumable"];

    if ($billable_detail != '') {
        $remarks = 'Deducted: ' . $_POST["hospital_num"];
        $hospital_no = $_POST["hospital_num"];
    } else {
        $remarks = $_POST["remark"];
    }



    if ($deduct_consumable == 'yes') {

        $stmt = $db->query("select ml_1sheet from stock_table where sn='$stock_sn_sn'");
        $rww = $stmt->fetch(PDO::FETCH_ASSOC);
        $ml_1sheet = $rww['ml_1sheet'];

        $stmt = $db->query("select * from lab_test_consumble where consumable_no='$stock_sn_sn'");
        if ($stmt->rowCount() > 0) {
            while ($rww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lab_test_no = $rww['lab_test_no'];
                $qty_test = $rww['qty_test'];

                $drip_per = $ml_1sheet / $qty_test;
            }
        }
    }


    $stmt_call = $db->prepare("SELECT buying_cost, cash_price 
                           FROM stock_table 
                           WHERE sn = :stock_sn 
                           LIMIT 1");
    $stmt_call->execute([':stock_sn' => $stock_sn_sn]);
    $rwx = $stmt_call->fetch(PDO::FETCH_ASSOC);

    if ($rwx) {
        $sale  = $rwx['cash_price'];
        $buy = $rwx['buying_cost'];
    }



    $sql = "SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id ORDER BY sn DESC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
    $stmt->bindParam(':cust_patient_id', $dept_id, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $rw = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rw['bal'] >= $new_qty) {
            $c_qty = $rw['bal'] - $new_qty;
            $qtyIN = 0;
            $qtyOUT = $new_qty;
            $cust_patient_type = 'IN';
            $captured_date = date('Y-m-d H:i:00');
            $enter_by = $fullname; ///['fullname'];
            $cust_patient_id = $dept_id;
            $sale_sn = '';
            $return_status = 0;
            $batch = '';

            $selectSQL = "SELECT stock_sn FROM stock_table_inven 
									WHERE stock_sn = :stock_sn 
									AND inven_desc = :inven_desc 
									AND qtyIN = :qtyIN 
									AND qtyOUT = :qtyOUT 
									AND enter_by = :enter_by 
									AND captured_date = :captured_date 
									AND cust_patient_id = :cust_patient_id";
            $selectStmt = $db->prepare($selectSQL);

            // Bind parameters
            $selectStmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
            $selectStmt->bindParam(':inven_desc', $remarks, PDO::PARAM_STR);
            $selectStmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_STR);
            $selectStmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_STR);
            $selectStmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
            $selectStmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
            $selectStmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);

            // Execute the SELECT statement
            $selectStmt->execute();

            // Check if any rows were returned
            if ($selectStmt->rowCount() == 0) {


                try {
                    // Begin a transaction
                    $db->beginTransaction();

                    // First INSERT operation
                    $insertSQL1 = "INSERT INTO stock_table_inven(
											stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, buy,sale,
											cust_patient_id, cust_patient_type, return_status, enter_by, captured_date
										  ) VALUES (
											:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :buy,:sale,
											:cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date
										  )";
                    $insertStmt1 = $db->prepare($insertSQL1);

                    // Bind parameters for the first INSERT
                    $insertStmt1->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':inven_desc', $remarks, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':batch', $batch, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':qtyIN', $qtyIN, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':bal', $c_qty, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':buy', $buy, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':sale', $sale, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':return_status', $return_status, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                    $insertStmt1->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);

                    // Execute the first INSERT statement
                    $insertStmt1->execute();

                    if ($billable_detail != '') {
                        $pp = explode("__", $billable_detail);
                        $pay_mode = $pp[0];
                        $claim_amt = $pp[1];
                        $access = $pp[2];
                        $amt_paying = $pp[3];
                        $appt_no = $pp[4];
                        $category = $pp[5];
                        $price_table = $pp[5];
                        $hosp_price = $pp[6];
                        $item_service = $pp[7];
                        $item_sn = $stock_sn;
                        $invoice_status = '1';
                        $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
                        $invoicedate = date('Y-m-d');
                        $invoice_by = $_SESSION['fullname'];
                        $paystatus = '0';
                        $cr = '1';
                        $setdatetime = date("Y-m-d H:i:s");
                        $one = 1;
                        $blank = '';
                        $tag = 'notRvse';
                        $zero = 0; // Added zero to bind the :process_claim parameter

                        // Second INSERT operation
                        $insertSQL2 = "INSERT INTO patient_ap_services(
												app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, 
												item_services, hosp_price, claim_amt, qty, remarks, drug_status, 
												invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, 
												date_entry, transact_date, pay, pay_mode, paystatus, process_claim, cr,tag
											  ) VALUES (
												:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, 
												:item_services, :hosp_price, :claim_amt, :qty, :remarks, :drug_status, 
												:invoice_status, :invoice_no, :invoice_date, :invoice_by, :prepared_by, 
												:date_entry, :transact_date, :pay, :pay_mode, :paystatus, :process_claim, :cr,:tag
											  )";
                        $insertStmt2 = $db->prepare($insertSQL2);

                        // Bind parameters for the second INSERT
                        $insertStmt2->bindParam(':app_no', $appt_no, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':access', $access, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':serv_group', $category, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':cat_type', $price_table, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':item_services', $item_service, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':qty', $qty, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':remarks', $remarks, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':drug_status', $one, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':invoice_date', $invoicedate, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':invoice_by', $invoice_by, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
                        $insertStmt2->bindParam(':date_entry', $setdatetime, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':transact_date', null, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':pay', $amt_paying, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':process_claim', $zero, PDO::PARAM_STR); // Changed to $zero
                        $insertStmt2->bindParam(':cr', $cr, PDO::PARAM_STR);
                        $insertStmt2->bindParam(':tag', $tag, PDO::PARAM_STR);

                        // Execute the second INSERT statement
                        $insertStmt2->execute();
                    }

                    // Commit the transaction
                    $db->commit();

                    echo 'Record Saved Successfully';
                } catch (Exception $e) {
                    // Roll back the transaction if something failed
                    $db->rollBack();
                    echo 'Failed to save record: ' . $e->getMessage();
                }
            } else {
                echo 'Already Exist!';
            }
        } else {
            echo 'Invalid Quantity Available';
        }
    } else {
        echo 'No Stock Available';
    }
}
