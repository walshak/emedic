<?php

session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");

$response_main = array(
    'message' => '',
    'status' => ''
);

function save_invent_deduct($db, $drug_sn, $sale_sn, $inven_desc, $batch, $qtyIN, $qtyOUT, $new_qty, $bal, $cust_patient_id, $EX_or_IN, $inventory)
{
    $inven_desc = str_replace('\'', '', $inven_desc);
    $EX_or_IN = 'IN';
    $C_date = date("Y-m-d");

    $stock_sn = $drug_sn;

    $stmt_call = $db->prepare("SELECT buying_cost, cash_price 
                           FROM stock_table 
                           WHERE sn = :stock_sn 
                           LIMIT 1");
    $stmt_call->execute([':stock_sn' => $stock_sn]);
    $rwx = $stmt_call->fetch(PDO::FETCH_ASSOC);

    if ($rwx) {
        $sale  = $rwx['cash_price'];
        $buy = $rwx['buying_cost'];
    }

    $stmt = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn=:stock_sn AND cust_patient_id=:cust_patient_id ORDER BY sn DESC LIMIT 1");
    $stmt->bindParam(':stock_sn', $stock_sn);
    $stmt->bindParam(':cust_patient_id', $cust_patient_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $rw = $stmt->fetch(PDO::FETCH_ASSOC);
        $bal = $rw['bal'];
    } else {

        if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {

            if ($_SESSION['pharm_request_from_store'] == 0) {
                $stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn=:stock_sn");
                $stmt->bindParam(':stock_sn', $stock_sn);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
                    $bal = $rwx['qty'];
                } else {
                    $bal = 0;
                }
            } else {
                $bal = 0;
            }
        }


        $bal = 0;
    }

    if ($new_qty > $bal && $inventory == 'deduct') {
        return 0;
    } else {
        if ($inventory == 'deduct') {
            $c_qty = $bal - $new_qty;
            $bal = $bal - $new_qty;
            $qtyIN = 0;
            $qtyOUT = $new_qty;
        } else {
            $c_qty = $bal + $new_qty;
            $bal = $bal + $new_qty;
            $qtyIN = $new_qty;
            $qtyOUT = 0;
        }

        $cust_patient_type = 'IN';
        $captured_date = date('Y-m-d H:i:0');
        $enter_by = $_SESSION['fullname'];
        $return_status = 0;
        $batch = '';

        $stmt = $db->prepare("SELECT stock_sn FROM stock_table_inven WHERE stock_sn=:stock_sn AND sale_sn=:sale_sn AND inven_desc=:inven_desc AND qtyIN=:qtyIN AND qtyOUT=:qtyOUT AND enter_by=:enter_by AND captured_date=:captured_date AND cust_patient_id=:cust_patient_id AND batch=:batch");
        $stmt->bindParam(':stock_sn', $stock_sn);
        $stmt->bindParam(':sale_sn', $sale_sn);
        $stmt->bindParam(':inven_desc', $inven_desc);
        $stmt->bindParam(':qtyIN', $qtyIN);
        $stmt->bindParam(':qtyOUT', $qtyOUT);
        $stmt->bindParam(':enter_by', $enter_by);
        $stmt->bindParam(':captured_date', $captured_date);
        $stmt->bindParam(':cust_patient_id', $cust_patient_id);
        $stmt->bindParam(':batch', $batch);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $add_stmt = $db->prepare("INSERT INTO stock_table_inven (stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal,buy,sale, cust_patient_id, cust_patient_type, return_status, enter_by, captured_date) VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal,:buy,:sale, :cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date)");
            $add_stmt->bindParam(':stock_sn', $stock_sn);
            $add_stmt->bindParam(':sale_sn', $sale_sn);
            $add_stmt->bindParam(':inven_desc', $inven_desc);
            $add_stmt->bindParam(':batch', $batch);
            $add_stmt->bindParam(':qtyIN', $qtyIN);
            $add_stmt->bindParam(':qtyOUT', $qtyOUT);
            $add_stmt->bindParam(':bal', $bal);
            $add_stmt->bindParam(':buy', $buy);
            $add_stmt->bindParam(':sale', $sale);
            $add_stmt->bindParam(':cust_patient_id', $cust_patient_id);
            $add_stmt->bindParam(':cust_patient_type', $cust_patient_type);
            $add_stmt->bindParam(':return_status', $return_status);
            $add_stmt->bindParam(':enter_by', $enter_by);
            $add_stmt->bindParam(':captured_date', $captured_date);
            $add_stmt->execute();

            if ($add_stmt) {

                if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {
                    $upd_stmt = $db->prepare('UPDATE stock_table SET qty = :qty WHERE sn = :sn');
                    $upd_stmt->bindParam(':qty', $bal);
                    $upd_stmt->bindParam(':sn', $stock_sn);
                    $upd_stmt->execute();
                }

                return 1;
            }
        }
    }
}

if (isset($_POST['dispense'])) {

    $hos_no = $_POST['hosp_no'];
    $dispense = $_POST['dispense'];
    $dept_id = $_SESSION['dept_id'];
    $enter_by = $_SESSION['fullname'];
    $captured_date = date('Y-m-d');
    $cust_patient_type = 'IN';

    if ($dept_id != '' && $enter_by != '') {
        if (!empty($_POST["list"])) {

            $list = $_POST["list"];
            $batch_name_array = explode(",", $list);
            $result_count = count($batch_name_array);
            $sv = 0;
            $insufficient_drugs = [];

            for ($i = 0; $i < $result_count; $i++) {
                $inv_id = $batch_name_array[$i];
                $break = explode("__", $inv_id);
                $sale_sn = $break[0];

                $stmt_ckh = $db->query("SELECT paystatus,item_services,pay,claim_amt,qty,drug_sn,drug_status,wallet_debt_bill_to_acct,dept_id,dept_dispensory_id 
					FROM patient_ap_services WHERE sn='$sale_sn' and drug_status=0");
                if ($stmt_ckh->rowCount() > 0) {
                    $rwxcv = $stmt_ckh->fetch(PDO::FETCH_ASSOC);
                    $main_paystatus = $rwxcv['paystatus'];
                    $item = $rwxcv['item_services'];
                    $pay = $rwxcv['pay'];
                    $claim = $rwxcv['claim_amt'];
                    $new_qty = $rwxcv['qty'];
                    $stock_sn = $rwxcv['drug_sn'];
                    $drug_status = $rwxcv['drug_status'];
                    $wallet_debt_bill_to_acct = $rwxcv['wallet_debt_bill_to_acct'];
                    $dept_dispensory_id = $rwxcv['dept_dispensory_id'];
                    $dept_id_status = $rwxcv['dept_id'];

                    if ($dispense == 'dispense_all_cr') {
                        $paystatus = 0;
                        $cr = 1;
                        $invoice_status = 1;
                        $inven_desc = 'Credit Dispensed/' . $hos_no;
                        if ($main_paystatus == 1) {
                            $paystatus = 1;
                            $cr = 0;
                            $invoice_status = 1;
                            $inven_desc = 'Dispensed/' . $hos_no;
                        } else {
                            $paystatus = 0;
                            $cr = 1;
                            $invoice_status = 1;
                            $inven_desc = 'Credit Dispensed/' . $hos_no;
                        }
                    } else {

                        /// check if this drug has been paid for ///
                        if ($main_paystatus == 1) {
                            $paystatus = 1;
                            $cr = 0;
                            $invoice_status = 1;
                            $inven_desc = 'Dispensed/' . $hos_no;
                        } else {
                            $paystatus = 0;
                            $cr = 1;
                            $invoice_status = 1;
                            $inven_desc = 'Credit Dispensed/' . $hos_no;
                        }
                    }

                    $stmt_call = $db->prepare("SELECT buying_cost, cash_price 
                           FROM stock_table 
                           WHERE sn = :stock_sn 
                           LIMIT 1");
                    $stmt_call->execute([':stock_sn' => $stock_sn]);
                    $rwx = $stmt_call->fetch(PDO::FETCH_ASSOC);

                    if ($rwx) {
                        $sale  = $rwx['cash_price'];
                        $buy = $rwx['buying_cost'];
                    }


                    $convert_main = 0;
                    if ($dispense == 'dispense_Main') {
                        $dept_id = $_SESSION['dept_id'];
                    } elseif ($dispense == 'dispense_RQ') {
                        $stmt = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :dept_id ORDER BY sn DESC LIMIT 1");
                        $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                        $stmt->bindParam(':dept_id', $dept_id_status, PDO::PARAM_STR);
                        $stmt->execute();
                        if ($stmt->rowCount() == 0) {
                            $convert_main = 1;
                            $dept_id = $_SESSION['dept_id'];
                        } else {
                            $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
                            $rQ_qty = $rwx['bal'];
                            if ($rQ_qty >= $new_qty) {
                                $dept_id = $dept_id_status;
                            } else {
                                $convert_main = 1;
                                $dept_id = $_SESSION['dept_id'];
                            }
                        }
                    }

                    //// main sttock quantity here ////
                    $stmt = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :dept_id ORDER BY sn DESC LIMIT 1");
                    $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                    $stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                    $stmt->execute();

                    if ($stmt->rowCount() == 0) {
                        //$stock_qty = 0;
                        if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {

                            $stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :stock_sn");
                            $stmt->bindParam(':stock_sn', $stock_sn);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0 && $_SESSION['pharm_request_from_store'] == 0) {
                                $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
                                $stock_qty = $rwx['qty'];
                            } else {
                                $stock_qty = 0;
                            }

                            if ($_SESSION['pharm_request_from_store'] == 0  && $stock_qty > 0) {
                                $inven_desc_openinig = "Opening Stock (Patient Dispensed)";
                                $qtyOUT = 0;
                                $null = 'IN';

                                $addRow = "INSERT INTO stock_table_inven(stock_sn,inven_desc,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date) 
						VALUES (:stock_sn,:inven_desc,:qtyIN,:qtyOUT,:c_qty,:buy,:sale, :cust_patient_id,:cust_patient_type,:enter_by,:captured_date)";
                                $stmt = $db->prepare($addRow);
                                $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                $stmt->bindParam(':inven_desc', $inven_desc_openinig, PDO::PARAM_STR);
                                $stmt->bindParam(':qtyIN', $stock_qty, PDO::PARAM_INT);
                                $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                                $stmt->bindParam(':c_qty', $stock_qty, PDO::PARAM_INT);
                                $stmt->bindParam(':buy', $buy, PDO::PARAM_INT);
                                $stmt->bindParam(':sale', $sale, PDO::PARAM_INT);
                                $stmt->bindParam(':cust_patient_id', $dept_id, PDO::PARAM_STR);
                                $stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                                $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                                $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                $stmt->execute();
                            } else {
                                //$response_main['message'] = 'Insufficient Stock Quantity <br> Confirm from Notes button/Store <br> Cancel/Delete add New Request';
                                // $response_main['status'] = '1';
                                // echo  json_encode($response_main);
                                //exit;
                            }
                        }
                    } else {
                        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
                        $stock_qty = $row_rstSelect['bal'];
                    }

                    if ($stock_qty >= $new_qty) {
                        $bal = $stock_qty - $new_qty;
                        $qtyIN = 0;
                        $qtyOUT = $new_qty;
                        $return_status = 0;

                        $stmt = $db->prepare("SELECT stock_sn FROM stock_table_inven 
										  WHERE stock_sn = :stock_sn 
										  AND sale_sn = :sale_sn 
										  AND inven_desc = :inven_desc 
										  AND qtyOUT = :qtyOUT 
                                          AND enter_by = :enter_by 
										  AND DATE(captured_date) = :captured_date 
										  AND cust_patient_id = :cust_patient_id");
                        $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                        $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                        $stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                        $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                        $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                        $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                        $stmt->bindParam(':cust_patient_id', $dept_id, PDO::PARAM_STR);
                        $stmt->execute();

                        if ($stmt->rowCount() == 0) {

                            try {
                                $db->beginTransaction();
                                $add_stmt = $db->prepare("INSERT INTO stock_table_inven 
													  (stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal,buy,sale, cust_patient_id, cust_patient_type, return_status, enter_by, captured_date) 
													  VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal,:buy,:sale, :cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date)");
                                $add_stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                $add_stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                                $add_stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                                $add_stmt->bindParam(':batch', $batch, PDO::PARAM_STR);
                                $add_stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
                                $add_stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                                $add_stmt->bindParam(':bal', $bal, PDO::PARAM_INT);
                                $add_stmt->bindParam(':buy', $buy, PDO::PARAM_INT);
                                $add_stmt->bindParam(':sale', $sale, PDO::PARAM_INT);
                                $add_stmt->bindParam(':cust_patient_id', $dept_id, PDO::PARAM_STR);
                                $add_stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                                $add_stmt->bindParam(':return_status', $return_status, PDO::PARAM_INT);
                                $add_stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                                $add_stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                $add_stmt->execute();

                                if ($add_stmt) {
                                    $drug_status = 1;
                                    // Update app services
                                    if ($wallet_debt_bill_to_acct == 'BILL' or $wallet_debt_bill_to_acct == 'WRF') {
                                        $cr = 2;
                                    }
                                    $updateSQL = $db->prepare("UPDATE patient_ap_services 
														   SET dept_dispensory_id = :dept_dispensory_id,dsp_by = :fullname, cr = :cr, invoice_status = :invoice_status, 
														   drug_status = :drug_status, paystatus = :paystatus 
														   WHERE sn = :sale_sn");
                                    $updateSQL->bindParam(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                    $updateSQL->bindParam(':fullname', $_SESSION['fullname'], PDO::PARAM_STR);
                                    $updateSQL->bindParam(':cr', $cr, PDO::PARAM_STR);
                                    $updateSQL->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
                                    $updateSQL->bindParam(':drug_status', $drug_status, PDO::PARAM_STR);
                                    $updateSQL->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
                                    $updateSQL->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                                    $updateSQL->execute();

                                    if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {
                                        $upd_stmt = $db->prepare('UPDATE stock_table SET qty = :qty WHERE sn = :sn');
                                        $upd_stmt->bindParam(':qty', $bal);
                                        $upd_stmt->bindParam(':sn', $stock_sn);
                                        $upd_stmt->execute();
                                    }

                                    $db->commit();

                                    $sv++;
                                } else {
                                    $db->rollBack();
                                    $response_main['message'] = 'Not Saved Successfully!';
                                    $response_main['status'] = '1';
                                    echo json_encode($response_main);
                                    exit;
                                }
                            } catch (PDOException $e) {
                                $db->rollBack();
                                throw $e;
                            }
                        } else {

                            $response_main['message'] = 'Not Saved Successfully!!!';
                            $response_main['status'] = '1';
                            echo json_encode($response_main);
                            exit;
                        }
                    } else {
                        // $response_main['message'] = $stock_sn . ' dept = ' . $dept_id . ' bal = ' . $stock_qty . ' new = ' . $new_qty . ' = ' . $_SESSION['dept_group_name'] . ' = ' . $_SESSION['dept_id']; ///'Insufficient Stock Quantity <br> Confirm from Notes button/Store <br> Cancel/Delete add New Request';
                        /*         $response_main['message'] = 'Insufficient Stock Quantity for this Drug  ' . $item . '<br> Current Stock = ' . $stock_qty . ' <br> Requested Quantity = ' . $new_qty . ' <br> Please Confirm from Notes button/Store <br> Or Cancel/Delete and Add New Request';
                        $response_main['status'] = '1';
                        echo  json_encode($response_main);
                        exit; */

                        $insufficient_drugs[] = [
                            'drug' => $item,
                            'current_stock' => $stock_qty,
                            'requested_qty' => $new_qty
                        ];

                        // Skip this drug and continue with others
                        continue;
                    }
                }
            }


            $response = [];

            if ($sv > 0) {
                $response['status'] = '0';
                $response['message'] = 'Some drugs were dispensed successfully.';
            } else {
                $response['status'] = '1';
                $response['message'] = 'No drugs were dispensed.';
            }

            if (!empty($insufficient_drugs)) {
                $msg = '<br><br><b>Drugs with insufficient stock:</b><br>';

                foreach ($insufficient_drugs as $d) {
                    $msg .= '- ' . $d['drug'] .
                        ' (Stock: ' . $d['current_stock'] .
                        ', Requested: ' . $d['requested_qty'] . ')<br>';
                }

                $response['message'] .= $msg;
            }

            echo json_encode($response);
            exit;








            /* 




            if ($sv > 0) {
                $response_main['message'] = 'Saved Successful!';
                $response_main['status'] = '0';
                echo  json_encode($response_main);
                exit;
            } else {

                $response_main['message'] = 'Unable to Dispense any Drug <br> Or Invalid Selection';
                $response_main['status'] = '1';
                echo  json_encode($response_main);
                exit;
            } */
        } else {

            $response_main['message'] = 'No Item Selected';
            $response_main['status'] = '1';
            echo  json_encode($response_main);
            exit;
        }
    } else {

        $response_main['message'] = 'No Department Identification';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
    }
}

if (isset($_POST["refill"])) {

    $hos_no = ($_POST['hosp_no']);
    $sale_sn = ($_POST['sale_sn']);
    $drug_sn = ($_POST['drug_sn']);
    $EX_or_IN = ($_POST['EX_or_IN']);
    $where = ($_POST['where']);
    $old_qty = ($_POST['old_qty']);
    $refill_qty = ($_POST['refill_qty']);
    $claim_amt = ($_POST['claim_amt']);
    $pay = ($_POST['pay']);

    /// price breakdown
    $unit_claim = $claim_amt / $old_qty;
    $unit_pay = $pay / $old_qty;
    $current_qty = $old_qty + $refill_qty;

    //// new claim and pay amount
    $TOTAL_CLAIM = $unit_claim * $current_qty;
    $TOTAL_PAY = $unit_pay * $current_qty;

    //// CURRENT STOCK LEVEL
    $stmt = $db->prepare("SELECT p.*, stock_table.qty as d_qty 
							FROM patient_ap_services as p 
							INNER JOIN stock_table ON p.drug_sn = stock_table.sn 
							WHERE p.sn = :sale_sn");
    $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
    $stmt->execute();

    $row_me = $stmt->fetch(PDO::FETCH_ASSOC);
    $qtyOUT = $refill_qty;
    $qtyIN = 0;
    //$stock_qty = $row_me['d_qty'];
    $paystatus = $row_me['paystatus'];
    $drug_status = $row_me['drug_status'];
    $cr = $row_me['cr'];
    $inven_desc = 'Refilled/' . $hos_no . '/' . $names;
    $batch = 'fil';
    $dept_id = $_SESSION['dept_id'];

    // Second query
    $stmt = $db->prepare("SELECT bal FROM stock_table_inven 
							WHERE stock_sn = :drug_sn AND cust_patient_id = :dept_id 
							ORDER BY sn DESC LIMIT 1");
    $stmt->bindParam(':drug_sn', $drug_sn, PDO::PARAM_STR);
    $stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $rw = $stmt->fetch(PDO::FETCH_ASSOC);
        $bal = $rw['bal'];
    } else {
        // Third query

        if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {

            if ($_SESSION['pharm_request_from_store'] == 0) {
                $stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :drug_sn");
                $stmt->bindParam(':drug_sn', $drug_sn, PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $rw = $stmt->fetch(PDO::FETCH_ASSOC);
                    $bal = $rw['qty'];
                } else {
                    $bal = 0;
                }
            } else {
                $bal = 0;
            }
        }
    }
    if ($bal <= 0) {

        $response_main['message'] = 'Invalid Quantity';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
    }

    if ($cr == "1" && $drug_status == '1' && $paystatus == '0') {
        /// deduct and dispense drug
        $new_qty = $refill_qty;
        $cust_patient_id = $dept_id;
        $inventory = 'deduct';
        $query = save_invent_deduct($db, $drug_sn, $sale_sn, $inven_desc, $batch, $qtyIN, $qtyOUT, $new_qty, $bal, $cust_patient_id, $EX_or_IN, $inventory);
        if ($query > 0) {
            $save_status = 1;
        }
    } elseif ($cr == "0" and $drug_status == '0' and $paystatus == '0') {
        /// cal new price/ claim  and DONT dispense drung
        if ($current_qty > $bal) {
            $response_main['message'] = 'Invalid Quantity';
            $response_main['status'] = '1';
            echo  json_encode($response_main);
            exit;
        } else {
            $save_status = 1;
        }
    } else {
        $response_main['message'] = 'Invalid Quantity';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
    }

    if ($save_status == 1 && $paystatus == 0) {
        $updateSQL = $db->prepare("UPDATE patient_ap_services 
				SET qty = :qty, claim_amt = :claim_amt, pay = :pay 
				WHERE sn = :sn");

        $updateSQL->bindParam(':qty', $current_qty, PDO::PARAM_STR);
        $updateSQL->bindParam(':claim_amt', $TOTAL_CLAIM, PDO::PARAM_STR);
        $updateSQL->bindParam(':pay', $TOTAL_PAY, PDO::PARAM_STR);
        $updateSQL->bindParam(':sn', $sale_sn, PDO::PARAM_STR);

        $rslt = $updateSQL->execute();

        $response_main['message'] = 'Refilled Successful & Refresh';
        $response_main['status'] = '0';
        echo json_encode($response_main);
        exit;
    }
}

if (isset($_POST["reverse_drug"])) {

    $hos_no = ($_POST['hosp_no']);
    $emr = $hos_no;
    $hospital_no = ($_POST['hosp_no']);
    $sale_sn = ($_POST['sale_sn']);
    $drug_sn = ($_POST['drug_sn']);
    $names = ($_POST['names']);
    $EX_or_IN = ($_POST['EX_or_IN']);
    $where = ($_POST['where']);
    $reverse_drug = ($_POST['reverse_drug']);
    $save_done = null;

    if ($reverse_drug == 'return_all' and $specify_qtyy == '') {
        $return_qty = $_POST["all_qtyy"];
        $return_status = 'all';
    } else {

        $return_qty = $_POST["specify_qtyy"];
        $return_status = 'part';
    }

    // Fetch sale row + stock balance
    $stmt = $db->prepare("
    SELECT 
        p.qty AS p_qty,
        p.drug_sn,
        p.item_services,
        p.pay_mode,
        p.pay,
        p.claim_amt,
        p.invoice_status,
        p.dept_dispensory_id,
        p.paystatus,
        p.cr,
        p.ledger_TX,
        p.wallet_payee,
        p.dsp_by,
        st.qty AS d_qty
    FROM patient_ap_services p
    INNER JOIN stock_table st ON p.drug_sn = st.sn
    WHERE p.sn = :sale_sn");

    $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
    $stmt->execute();
    $row_me = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row_me) {
        die("Error: Invalid sale record");
    }

    // Input → return vars
    $qtyIN       = $return_qty;
    $new_qty     = $return_qty;
    $qtyOUT      = 0;
    $batch       = 'rvs';
    $inven_desc  = 'Returned/' . $hos_no . '/' . $names;

    $cust_patient_id = !empty($row_me['dept_dispensory_id'])
        ? $row_me['dept_dispensory_id']
        : $_SESSION['dept_id'];

    $captured_date = date("Y-m-d H:i:s");
    $transact_date = date("Y-m-d");
    $enter_by      = $_SESSION['fullname'];

    // Extract DB values
    $p_qty        = $row_me['p_qty'];
    $drug_sn      = $row_me['drug_sn'];
    $ledger_TX    = $row_me['ledger_TX'];
    $claim_amt    = $row_me['claim_amt'];
    $pay          = $row_me['pay'];
    $paystatus_db = $row_me['paystatus'];
    $wallet_payee = $row_me['wallet_payee'];
    $cr           = $row_me['cr'];
    $pay_mode     = $row_me['pay_mode'];
    $dsp_by       = $row_me['dsp_by']; // default

    // Per-unit values
    $nhis_price = $p_qty > 0 ? ($claim_amt / $p_qty) : 0;
    $hosp_price = $p_qty > 0 ? ($pay / $p_qty) : 0;

    // New quantities after reverse
    $Sale_drug_qty = $p_qty - $return_qty;
    $reverse_pay   = $hosp_price * $return_qty;
    $reverse_claim = $nhis_price * $return_qty;

    // Full reversal or last return
    if ($return_status == 'all' || $Sale_drug_qty <= 0) {

        $invoice_status = 0;
        $drug_status    = 0;
        $paystatus      = 0;
        $cr             = 0;

        // To avoid divide-by-zero or zero rows
        $Sale_drug_qty  = 1;

        // Recalculate remaining to 1 unit only
        $claim_amt      = $nhis_price;
        $pay            = $hosp_price;

        $dsp_by         = '';
    } else {

        $invoice_status = $row_me['invoice_status'];
        $paystatus = $row_me['paystatus'];
        $drug_status    = 1;

        $claim_amt      = $nhis_price * $Sale_drug_qty;
        $pay            = $hosp_price * $Sale_drug_qty;
    }


    // if ($wallet_payee == '') {
    try {
        // Begin transaction
        $db->beginTransaction();

        // Reverse ledger only if cash transaction that was credited & paid
        if ($pay_mode == 'cash' && $cr == 2 && $paystatus_db == 1) {

            $sql = "DELETE FROM chart_ledger 
                WHERE sale_sn = :sn 
                  AND hospital_no = :hospital_no";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':sn', $sale_sn, PDO::PARAM_INT);
            $stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_INT);

            $stmt->execute();
            $affectedRows = $stmt->rowCount(); // ✅ correct row count

            $save_done = ($affectedRows > 0) ? 1 : 0;
            $who = 13;
        } elseif ($paystatus_db == 1 && $cr != 2 && $pay > 0) {

            if ($wallet_payee != '') {
                $emr = $wallet_payee;
            }
            // Get debit GL + insurance account for this sale
            $stm = $db->prepare("
                        SELECT account_no, insurance_no 
                        FROM chart_ledger 
                        WHERE sale_sn = :sale_sn
                        AND transc_type = 'CREDIT'
                        LIMIT 1");
            $stm->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
            $stm->execute();

            $row_return = $stm->fetch(PDO::FETCH_ASSOC);
            $debit_gl_account = !empty($row_return['account_no'])
                ? $row_return['account_no'] : '';
            $insurance_no = !empty($row_return['insurance_no'])
                ? $row_return['insurance_no'] : null;

            $lg_ref_no = time() . $emr;
            $auth_code = null;

            if ($debit_gl_account == '') {
                throw new Exception('Invalid Ledger / Account Does Not Exist!' . $debit_gl_account);
            }

            if ($debit_gl_account != '' && $insurance_no != '') {
                $sale_sn_cancelled = $sale_sn . 'XC' . rand(1000, 9999);
                ////throw new Exception('Error Crediting!==== pay sale_sn = ' . $sale_sn . '  debit_gl_account = ' . $debit_gl_account . ' insurance_no = ' . $insurance_no . ' emr:= ' . $emr);
                $CREDIT_ENTRY = billing(
                    $db,
                    $emr,
                    $emr,
                    $insurance_no,
                    null,
                    $sale_sn_cancelled,
                    $inven_desc,
                    'CREDIT',
                    0,
                    $reverse_pay,
                    0,
                    date('Y-m-d H:i:s'),
                    $auth_code,
                    null,
                    '2121',
                    $lg_ref_no,
                    0
                );

                if ($CREDIT_ENTRY !== 'success') {
                    throw new Exception('Error Crediting!');
                }

                $DEBIT_ENTRY = billing(
                    $db,
                    $emr,
                    $emr,
                    $insurance_no,
                    null,
                    $sale_sn_cancelled,
                    $inven_desc,
                    'DEBIT',
                    $reverse_pay,
                    0,
                    0,
                    date('Y-m-d H:i:s'),
                    $auth_code,
                    null,
                    $debit_gl_account,
                    $lg_ref_no,
                    0
                );

                if ($DEBIT_ENTRY !== 'success') {
                    throw new Exception('Error Debiting!');
                }
                $save_done = 1;
                $who = 2;
            } else {
                throw new Exception('Invalid Ledger / Account Does Not Exist!');
            }
        } else {
            // Payment was not made, drug was dispensed on credit
            $save_done = 1;
            $who = 3;
        }

        if ($save_done == 1) {
            $bal = 0;
            $inventory = 'add';
            $query = save_invent_deduct($db, $drug_sn, $sale_sn, $inven_desc, $batch, $qtyIN, $qtyOUT, $new_qty, $bal, $cust_patient_id, $EX_or_IN, $inventory);

            if ($query > 0) {

                $updateSQL = $db->prepare("UPDATE patient_ap_services 
                    SET qty = :qty, claim_amt = :claim_amt, pay = :pay, cr = :cr, drug_status = :drug_status,
                        invoice_status = :invoice_status, paystatus = :paystatus, dsp_by = :dsp_by 
                    WHERE sn = :sn");

                $updateSQL->bindParam(':qty', $Sale_drug_qty, PDO::PARAM_STR);
                $updateSQL->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                $updateSQL->bindParam(':pay', $pay, PDO::PARAM_STR);
                $updateSQL->bindParam(':cr', $cr, PDO::PARAM_STR);
                $updateSQL->bindParam(':drug_status', $drug_status, PDO::PARAM_STR);
                $updateSQL->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
                $updateSQL->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
                $updateSQL->bindParam(':dsp_by', $dsp_by, PDO::PARAM_STR);
                $updateSQL->bindParam(':sn', $sale_sn, PDO::PARAM_STR);
                $updateSQL->execute();

                // Update chart_ledger sale_sn
                /*                 $sale_sn_cancelled = $sale_sn . 'XC' . rand(1000, 9999);
                    $updateLedger = $db->prepare("
                            UPDATE chart_ledger 
                            SET sale_sn = :sale_sn_cancelled 
                            WHERE sale_sn = :sale_sn");
                    $updateLedger->bindParam(':sale_sn_cancelled', $sale_sn_cancelled, PDO::PARAM_STR);
                    $updateLedger->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                    $updateLedger->execute(); */

                if ($paystatus == 0) {
                    $updateInv = $db->prepare("
                            UPDATE stock_table_inven 
                            SET return_status = 1
                            WHERE sale_sn = :sale_sn
                        ");
                    $updateInv->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                    $updateInv->execute();
                } else {
                    $updateInv = $db->prepare("
                            UPDATE stock_table_inven 
                            SET return_status = 1
                            WHERE sn = (
                                SELECT sn FROM stock_table_inven 
                                WHERE sale_sn = :sale_sn
                                ORDER BY sn DESC
                                LIMIT 1
                            )
                        ");
                    $updateInv->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
                    $updateInv->execute();
                }

                $db->commit(); // ✅ Commit transaction
                $response_main['message'] = 'Reversed Successfully!';
                $response_main['status'] = '0';
                echo json_encode($response_main);
                exit;
            } else {
                throw new Exception('Unable to Reverse Drug!');
            }
        }
    } catch (Exception $e) {
        $db->rollBack(); // ✅ Rollback transaction on error
        $response_main['message'] = $e->getMessage();
        $response_main['status'] = '1';
        echo json_encode($response_main);
        exit;
    }
    // } else {

    //    $response_main['message'] = 'Unable to Reserve Transaction! Wallet/Patient Deposit Transfer Payment Not Reversable!';
    //     $response_main['status'] = '1';
    //    echo  json_encode($response_main);
    //    exit;
    //}
}

if (isset($_POST["reverse_sale_id"])) {
    $sale_sn = $_POST['reverse_sale_id'];

    try {
        $db->beginTransaction(); // ✅ Begin transaction

        // Fetch sale and stock details
        $stmt = $db->prepare("
            SELECT p.qty as p_qty, p.drug_sn, p.item_services, p.pay_mode, 
                   p.hospital_no, p.pay, p.claim_amt, p.invoice_status, 
                   p.paystatus, p.cr, p.ledger_TX, p.wallet_payee, p.dsp_by, 
                   stock_table.qty as d_qty 
            FROM patient_ap_services AS p 
            INNER JOIN stock_table ON p.drug_sn = stock_table.sn 
            WHERE p.sn = :sale_sn");
        $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
        $stmt->execute();
        $row_me = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row_me) {
            throw new Exception("Sale not found!");
        }

        $hos_no = $row_me['hospital_no'];
        $emr = $hos_no;
        $p_qty = $row_me['p_qty'];
        $ledger_TX = $row_me['ledger_TX'];
        $claim_amt = $row_me['claim_amt'];
        $reverse_pay = $row_me['pay'];
        $paystatus = $row_me['paystatus'];
        $wallet_payee = $row_me['wallet_payee'];
        $drug_sn = $row_me['drug_sn'];
        $cr = $row_me['cr'];
        $pay_mode = $row_me['pay_mode'];

        $inven_desc = 'Returned/' . $hos_no . '/' . $patient_name;
        // Get debit GL + insurance account
        $stm = $db->prepare("
            SELECT account_no, insurance_no 
            FROM chart_ledger 
            WHERE sale_sn = :sale_sn
            AND transc_type = 'CREDIT'
            LIMIT 1");
        $stm->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
        $stm->execute();
        $row_return = $stm->fetch(PDO::FETCH_ASSOC);

        $debit_gl_account = $row_return['account_no'];
        $insurance_no = $row_return['insurance_no'];

        $lg_ref_no = time() . $hos_no;

        if ($debit_gl_account && $insurance_no && $reverse_pay > 0) {
            // CREDIT ENTRY

            $CREDIT_ENTRY = billing(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $inven_desc,       // ref value
                $sale_sn,
                $inven_desc,
                'CREDIT',
                0,
                $reverse_pay,
                0,
                date('Y-m-d H:i:s'),
                $auth_code,
                '',               // safe bank name
                '2121',           // account
                $lg_ref_no,
                0                 // patient status
            );


            if ($CREDIT_ENTRY !== 'success') {
                throw new Exception('Error crediting!');
            }

            // DEBIT ENTRY
            $DEBIT_ENTRY = billing(
                $db,
                $emr,
                $emr,
                $insurance_no,
                $inven_desc,
                $sale_sn,
                $inven_desc,
                'DEBIT',
                $reverse_pay,
                0,
                0,
                $setdate,
                $auth_code,
                null,
                $debit_gl_account,
                $lg_ref_no,
                0
            );
            if ($DEBIT_ENTRY !== 'success') {
                throw new Exception('Error debiting!');
            }

            // Update patient_ap_services
            $updateSQL = "
                UPDATE patient_ap_services 
                SET invoice_status = 0, 
                    paystatus = 0, 
                    drug_status = 0, 
                    transact_date = NULL 
                WHERE sn = :sale_sn
            ";
            $stmt = $db->prepare($updateSQL);
            $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
            $stmt->execute();

            // Update chart_ledger sale_sn
            $sale_sn_cancelled = $sale_sn . 'XC' . rand(1000, 9999);
            $updateLedger = $db->prepare("
                UPDATE chart_ledger 
                SET sale_sn = :sale_sn_cancelled 
                WHERE sale_sn = :sale_sn
            ");
            $updateLedger->bindParam(':sale_sn_cancelled', $sale_sn_cancelled, PDO::PARAM_STR);
            $updateLedger->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
            $updateLedger->execute();

            $db->commit(); // ✅ Commit transaction

            echo json_encode(['message' => 'Reversed Successfully!', 'status' => '0']);
            exit;
        } else {
            throw new Exception('Invalid Ledger / Account Does Not Exist or No Payment!');
        }
    } catch (Exception $e) {
        $db->rollBack(); // ✅ Rollback transaction on error
        echo json_encode(['message' => $e->getMessage(), 'status' => '1']);
        exit;
    }
}

if (isset($_POST["dispense_oncredit_external_sales"])) {

    $sale_sn = ($_POST['sale_sn']);
    $names = ($_POST['names']);
    /// transaction
    $transaction = ($_POST['dispense_oncredit_external_sales']);

    $stmt = $db->prepare("SELECT p.qty as p_qty, p.drug_sn, p.item_services, p.pay, p.hospital_no, p.claim_amt, stock_table.qty as d_qty 
							  FROM patient_ap_services as p 
							  INNER JOIN stock_table ON p.drug_sn = stock_table.sn 
							  WHERE p.sn = :sale_sn");
    $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
    $stmt->execute();

    $row_me = $stmt->fetch(PDO::FETCH_ASSOC);

    $qtyOUT = $row_me['p_qty'];
    $new_qty = $row_me['p_qty'];
    $stock_qty = $row_me['d_qty'];
    $hospital_no = $row_me['hospital_no'];
    $c_qty = $stock_qty - $qtyOUT;

    $qtyIN = 0;
    $batch = ' ';
    $drug_sn = $row_me['drug_sn'];
    $item_services = $row_me['item_services'];
    $inven_desc = 'Credit Dispensed/' . $hospital_no . '/' . $names;
    $dept_id = $_SESSION['dept_id'];
    $cust_patient_id = $dept_id;
    $captured_date = date("Y-m-d H:i:s");
    $transact_date = date("Y-m-d");
    $enter_by = $_SESSION['fullname'];
    $claim_amt = $row_me['claim_amt'];
    $pay = $row_me['pay'];


    if ($transaction == 'credit') {
        $invoice_status = 1;
        $drug_status = 1;
        $paystatus = 0;
        $cr = 1;
    } else {

        $inven_desc = 'Dispense to patient: ' . $hospital_no;
        $cr = 0;
        $invoice_status = 1;
        $drug_status = 1;
        $paystatus = 1;
        $bal = 0;
    }



    $Sale_drug_qty = $qtyOUT;
    $inventory = 'deduct';
    $bal = 0;

    $query = save_invent_deduct($db, $drug_sn, $sale_sn, $inven_desc, $batch, $qtyIN, $qtyOUT, $new_qty, $bal, $cust_patient_id, $EX_or_IN, $inventory);

    if ($query > 0) {

        $updateSQL = $db->prepare("UPDATE patient_ap_services SET qty = :qty, claim_amt = :claim_amt, pay = :pay, dsp_by = :dsp_by, cr = :cr, invoice_status = :invoice_status, drug_status = :drug_status, paystatus = :paystatus WHERE sn = :sn");
        $updateSQL->bindParam(':qty', $Sale_drug_qty);
        $updateSQL->bindParam(':claim_amt', $claim_amt);
        $updateSQL->bindParam(':pay', $pay);
        $updateSQL->bindParam(':dsp_by', $_POST['fullname']);
        $updateSQL->bindParam(':cr', $cr);
        $updateSQL->bindParam(':invoice_status', $invoice_status);
        $updateSQL->bindParam(':drug_status', $drug_status);
        $updateSQL->bindParam(':paystatus', $paystatus);
        $updateSQL->bindParam(':sn', $sale_sn);

        $updateSQL->execute();

        $C_date = date("Y-m-d");



        $response_main['message'] = 'Dispensed Successfully!';
        $response_main['status'] = '0';
        echo  json_encode($response_main);
        exit;
    } else {
        $response_main['message'] = 'Unable to Dispense Drug!';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
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

    // Always use system time
    $setdate  = date('Y-m-d H:i:00');
    $setdate2 = date('Y-m-d');
    $prepared_by = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'SYSTEM';

    // Null safety
    if ($ref_value === null) {
        $ref_value = '';
    }
    if ($bank_name === null) {
        $bank_name = '';
    }
    if ($patient_stt_status === null) {
        $patient_stt_status = 0;
    }

    try {

        // Prevent duplicate ledger entry (compare by date only, not exact seconds)
        $stmt = $db->prepare("
			SELECT sn FROM chart_ledger 
			WHERE app_no = :app_no 
				AND hospital_no = :hospital_no 
				AND sale_sn = :sale_sn 
				AND item_services = :item_services 
				AND transc_type = :transc_type 
				AND dr_amt = :dr_amt 
				AND cr_amt = :cr_amt 
				AND account_no = :account_no 
				AND DATE(date_entry) = :date_entry2
			LIMIT 1
		");

        $stmt->bindParam(':app_no', $app_no);
        $stmt->bindParam(':hospital_no', $hosp_no);
        $stmt->bindParam(':sale_sn', $sale_sn);
        $stmt->bindParam(':item_services', $item_services);
        $stmt->bindParam(':transc_type', $transc_type);
        $stmt->bindParam(':dr_amt', $dr_amt);
        $stmt->bindParam(':cr_amt', $cr_amt);
        $stmt->bindParam(':account_no', $account_no);
        $stmt->bindParam(':date_entry2', $setdate2);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return "exists" . $transc_type . ' ' . $dr_amt . ' ' . $item_services; // avoid double posting
        }

        // Current fiscal year
        $fical_year = $db->query("SELECT id FROM chart_fiscal_year WHERE closed = '0' LIMIT 1")->fetchColumn();
        if (!$fical_year) {
            $fical_year = 0;
        }

        // Insert entry
        $sql = $db->prepare("
			INSERT INTO chart_ledger 
			(app_no, hospital_no, insurance_no, ref_value, sale_sn, item_services, bank_name,
			transc_type, dr_amt, cr_amt, bal, prepared_by, date_entry, date_entry2, auth_code, 
			account_no, lg_ref_no, fiscal_year, patient_stt_status)
			VALUES
			(:app_no, :hospital_no, :insurance_no, :ref_value, :sale_sn, :item_services, :bank_name,
			:transc_type, :dr_amt, :cr_amt, :bal, :prepared_by, :date_entry, :date_entry2, :auth_code,
			:account_no, :lg_ref_no, :fiscal_year, :patient_stt_status)
		");

        $sql->bindParam(':app_no', $app_no);
        $sql->bindParam(':hospital_no', $hosp_no);
        $sql->bindParam(':insurance_no', $hmo_no);
        $sql->bindParam(':ref_value', $ref_value);
        $sql->bindParam(':sale_sn', $sale_sn);
        $sql->bindParam(':item_services', $item_services);
        $sql->bindParam(':bank_name', $bank_name);
        $sql->bindParam(':transc_type', $transc_type);
        $sql->bindParam(':dr_amt', $dr_amt);
        $sql->bindParam(':cr_amt', $cr_amt);
        $sql->bindParam(':bal', $bal);
        $sql->bindParam(':prepared_by', $prepared_by);
        $sql->bindParam(':date_entry', $setdate);
        $sql->bindParam(':date_entry2', $setdate2);
        $sql->bindParam(':auth_code', $auth_code);
        $sql->bindParam(':account_no', $account_no);
        $sql->bindParam(':lg_ref_no', $lg_ref_no);
        $sql->bindParam(':fiscal_year', $fical_year);
        $sql->bindParam(':patient_stt_status', $patient_stt_status);
        $sql->execute();

        return ($sql->rowCount() > 0) ? "success" : "error";
    } catch (PDOException $e) {
        return "error: " . $e->getMessage();
    }
}
