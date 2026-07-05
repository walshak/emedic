<?php include("../Connections/Conn.php");
session_start();

$response_main = array(
    'message' => '',
    'status' => ''
);

if ($_POST['action'] == 'approve_all_request') {
    $pro_inv = $_REQUEST['inv_all'];
    if (!empty($pro_inv)) {
        try {
            // Begin the transaction
            $db->beginTransaction();

            for ($i = 0; $i < count($pro_inv); $i++) {
                $inv_id = $pro_inv[$i];
                $apr_qty = $_POST["qty_" . $inv_id];
                $pck_pcs_ = $_POST["pck_pcs_" . $inv_id];
                $unit_pck_ = $_POST["unit_pck_" . $inv_id];

                if ($pck_pcs_ == 'pck') {
                    $apr_qty = $unit_pck_ * $apr_qty;
                }

                $setdate = date('Y-m-d H:i:00');
                $addUpdate = "UPDATE stock_table_request SET 
                    b4_approve_by = :approve_by, 
                    b4_approve_date = :approve_date, 
                    b4_approve_qty = :approve_qty
                    WHERE sn = :sn";
                $stmt = $db->prepare($addUpdate);
                $stmt->bindParam(':approve_by', $_SESSION['fullname'], PDO::PARAM_STR);
                $stmt->bindParam(':approve_date', $setdate, PDO::PARAM_STR);
                $stmt->bindParam(':approve_qty', $apr_qty, PDO::PARAM_STR);
                $stmt->bindParam(':sn', $inv_id, PDO::PARAM_STR);
                $stmt->execute();
            }

            // Commit the transaction
            $db->commit();

            $response_main['message'] = 'Approved Successful!';
            $response_main['status'] = '1';
        } catch (Exception $e) {
            // Rollback the transaction if something failed
            $db->rollBack();
            $response_main['message'] = 'Failed: ' . $e->getMessage();
            $response_main['status'] = '0';
        }
    } else {
        $response_main['message'] = 'No items to approve.';
        $response_main['status'] = '0';
    }
    echo json_encode($response_main);
    exit;
}



if ($_POST['action'] == 'approve_all_issue') {

    $pro_inv = $_REQUEST['inv_all'];
    $Collectedby = $_POST["Collectedby"];

    $Collectedby = str_replace("'", "", trim($Collectedby));
    $stock_code = $_POST["stock_code"];
    $input_array = [];
    $all_product = '';
    $error_m = 0;
    $captured_date = date("Y-m-d");
    $enter_by = $_SESSION['fullname'];
    $cust_patient_type = 'IN';
    $approval_dept = $_SESSION['dept_id']; /// get dept id for store room or pharmacy

    if (!empty($pro_inv)) {

        for ($i = 0; $i < count($pro_inv); $i++) {
            $inv_id = $pro_inv[$i];
            /// qty ///
            $apr_qty = $_POST["qty_" . $inv_id];
            $pck_pcs_ = $_POST["pck_pcs_" . $inv_id];
            $unit_pck_ = $_POST["unit_pck_" . $inv_id];

            if ($pck_pcs_ == 'pck') {
                $apr_qty = $unit_pck_ * $apr_qty;
            }

            /// COLLECTOR ////
            $stmt = $db->prepare("SELECT * FROM stock_table_request WHERE sn = :sn AND status = 'pending'");
            $stmt->execute([':sn' => $inv_id]);

            if ($stmt->rowCount() > 0) {
                $roww = $stmt->fetch(PDO::FETCH_ASSOC);

                $request_sn = $inv_id;
                $order_dept = $roww['order_dept'];
                $order_date = $roww['order_date'];
                $order_qty = $roww['order_qty'];
                $order_no = $roww['sn'];
                $stock_table = $roww['stock_table'];
                $dept_incharge_stock = $roww['dept_incharge_stock'];
                $combo = $roww['combo'];
                if ($combo == '1') {
                    $stock_name = $roww['stock_name'];
                    $stmt_comb = $db->query("SELECT c.*, s.sn as stock_sn FROM stock_combo c inner join stock_table s on s.sn=c.items WHERE combo_name='$stock_name'");
                    if ($stmt_comb->rowCount() > 0) {
                        while ($roww3 = $stmt_comb->fetch(PDO::FETCH_ASSOC)) {
                            $stock_sn = $roww3['stock_sn'];
                            $apr_qty = $roww3['qty'] * $apr_qty;              /// request_sn							
                            array_push($input_array, array("stock_sn" => "$stock_sn", "apr_qty" => "$apr_qty", "request_sn" => "$request_sn", "dept_incharge_stock" => "$dept_incharge_stock"));
                        }
                    } else {
                        echo 'Error Found';
                    }
                } else {
                    $stock_sn = $roww['stock_sn'];
                    array_push($input_array, array("stock_sn" => "$stock_sn", "apr_qty" => "$apr_qty", "request_sn" => "$request_sn", "dept_incharge_stock" => "$dept_incharge_stock"));
                }
            }
        }


        try {

            $insert_count = 0;
            $update_count = 0;
            $debug_log = [];

            $db->beginTransaction();

            foreach ($input_array as $elements) {

                $stock_sn = $elements['stock_sn'];
                $found_key = array_search($stock_sn, array_column($input_array, 'stock_sn'));
                // grap the return arr
                $the_arr = $input_array[$found_key];
                $stock_sn = $the_arr['stock_sn'];
                $apr_qty = $the_arr['apr_qty'];
                $request_sn = $the_arr['request_sn'];
                $dept_incharge_stock = $the_arr['dept_incharge_stock'];
                //// --------------------------------------------

                $stmt = $db->prepare("SELECT qty,buying_cost,cash_price FROM stock_table WHERE sn=:sn");
                $stmt->execute([':sn' => $stock_sn]);
                $rwx = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$rwx) {
                    $debug_log[] = "STOCK NOT FOUND: {$stock_sn}";
                    continue;
                }

                if ($rwx) {
                    $sale  = $rwx['cash_price'];
                    $buy = $rwx['buying_cost'];
                    $C_qty = $rwx['qty'];
                }

                if ($dept_incharge_stock == $approval_dept) {  //// confirm dept incharge is same as approval dept ////




                    if ($apr_qty > 0 and $Collectedby != '') {






                        //// REMOVE STOCK FROMMMMMMMMMMMMMMMMMM DEPT//////////////////////////////

                        $procure_sn = '';
                        $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn=:stock_sn and cust_patient_id=:approval_dept ORDER BY sn DESC LIMIT 1");
                        $stmt_chk->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                        $stmt_chk->bindParam(':approval_dept', $approval_dept, PDO::PARAM_STR);
                        $stmt_chk->execute();
                        if ($stmt_chk->rowCount() > 0) {
                            $rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
                            $old_qty = $rowwc['bal'];
                        } else {

                            ///$old_qty = 0;
                            /// intial qunatity 

                            if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {

                                $inven_desc = "Opening Stock (Requisition Approval)";
                                $qtyOUT = 0;
                                $old_qty = $C_qty;
                                $addRow = "INSERT INTO stock_table_inven(stock_sn,inven_desc,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date)                    VALUES (:stock_sn,:inven_desc,:qtyIN,:qtyOUT,:c_qty,:buy,:sale,:cust_patient_id,:cust_patient_type,:enter_by,:captured_date)";
                                $stmt = $db->prepare($addRow);
                                $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                $stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                                $stmt->bindParam(':qtyIN', $old_qty, PDO::PARAM_INT);
                                $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                                $stmt->bindParam(':c_qty', $old_qty, PDO::PARAM_INT);
                                $stmt->bindParam(':buy', $buy, PDO::PARAM_INT);
                                $stmt->bindParam(':sale', $sale, PDO::PARAM_INT);
                                $stmt->bindParam(':cust_patient_id', $approval_dept, PDO::PARAM_STR);
                                $stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                                $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                                $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                $stmt->execute();
                                $insert_count += $stmt->rowCount();

                                if ($stmt->rowCount() == 0) {
                                    $debug_log[] = "FAILED REMOVE INSERT: {$stock_sn}";
                                }
                            }
                        }

                        if ($apr_qty > 0 && $old_qty >= $apr_qty && $approval_dept != '') {

                            $c_qty = $old_qty - $apr_qty;
                            $qtyIN = 0;
                            $qtyOUT = $apr_qty;

                            $invenDesc = "RMV BY: " . $_SESSION["fullname"] . " APR TO: " . $Collectedby;
                            $inven_desc = preg_replace("/'/", "", trim($invenDesc));

                            $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven 
                              WHERE stock_sn=:stock_sn and inven_desc=:inven_desc and qtyIN=:qtyIN and 
                              qtyOUT=:qtyOUT and enter_by=:enter_by and captured_date=:captured_date");
                            $stmt_chk->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                            $stmt_chk->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                            $stmt_chk->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
                            $stmt_chk->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                            $stmt_chk->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                            $stmt_chk->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                            $stmt_chk->execute();

                            if ($stmt_chk->rowCount() == 0) {
                                $addRow = "INSERT INTO stock_table_inven(stock_sn,inven_desc,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date,procure_id)                    VALUES (:stock_sn,:inven_desc,:qtyIN,:qtyOUT,:c_qty,:buy,:sale,:order_dept,:cust_patient_type,:enter_by,:captured_date,:procure_sn)";
                                $stmt = $db->prepare($addRow);
                                $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                $stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                                $stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
                                $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                                $stmt->bindParam(':c_qty', $c_qty, PDO::PARAM_INT);
                                $stmt->bindParam(':buy', $buy, PDO::PARAM_INT);
                                $stmt->bindParam(':sale', $sale, PDO::PARAM_INT);
                                $stmt->bindParam(':order_dept', $approval_dept, PDO::PARAM_STR);
                                $stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                                $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                                $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                $stmt->bindParam(':procure_sn', $procure_sn, PDO::PARAM_STR);
                                $stmt->execute();
                                $insert_count += $stmt->rowCount();

                                if ($stmt->rowCount() == 0) {
                                    $debug_log[] = "FAILED REMOVE INSERT: {$stock_sn}";
                                }


                                if ($stmt->rowCount() > 0) {
                                    if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {
                                        $updateSQL = "UPDATE stock_table SET qty = :qty,status='active',date_last_update=:captured_date WHERE sn=:stock_sn";
                                        $stmt = $db->prepare($updateSQL);
                                        $stmt->bindParam(':qty', $c_qty, PDO::PARAM_INT);
                                        $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                        $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                        $stmt->execute();

                                        $update_count += $stmt->rowCount();
                                    }
                                }
                            } else {
                                $debug_log[] = "DUPLICATE REMOVE ENTRY: stock_sn={$stock_sn}, qtyOUT={$qtyOUT}, enter_by={$enter_by}, captured_date={$captured_date}";
                            }

                            //// END OF REMOVE STOCK FROMMMMMMMMMMMMMMMMMM DEPT//////////////////////////////

                            if ($order_dept != $approval_dept) {

                                ////============== ADDD TO QTY TO ORDER DEPARTMENT  ==================================

                                $stmtChk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn=:stock_sn AND cust_patient_id=:order_dept                    ORDER BY sn DESC LIMIT 1");
                                $stmtChk->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                $stmtChk->bindParam(':order_dept', $order_dept, PDO::PARAM_STR);
                                $stmtChk->execute();

                                if ($stmtChk->rowCount() > 0) {
                                    $rowWc = $stmtChk->fetch(PDO::FETCH_ASSOC);
                                    $old_qty = $rowWc['bal'];
                                } else {
                                    $old_qty = 0;
                                }

                                $c_qty = $old_qty + $apr_qty;
                                $qtyIN = $apr_qty;
                                $qtyOUT = 0;

                                $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven 
                                          WHERE stock_sn=:stock_sn and inven_desc=:inven_desc and qtyIN=:qtyIN and 
                                          qtyOUT=:qtyOUT and enter_by=:enter_by and captured_date=:captured_date");
                                $stmt_chk->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                $stmt_chk->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                                $stmt_chk->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
                                $stmt_chk->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                                $stmt_chk->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                                $stmt_chk->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                $stmt_chk->execute();

                                if ($stmt_chk->rowCount() == 0) {
                                    $addRow = "INSERT INTO stock_table_inven(stock_sn,inven_desc,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date,procure_id) 
                               VALUES (:stock_sn,:inven_desc,:qtyIN,:qtyOUT,:c_qty,:buy,:sale,:order_dept,:cust_patient_type,:enter_by,:captured_date,:procure_sn)";
                                    $stmt = $db->prepare($addRow);
                                    $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                                    $stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                                    $stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
                                    $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                                    $stmt->bindParam(':c_qty', $c_qty, PDO::PARAM_INT);
                                    $stmt->bindParam(':buy', $buy, PDO::PARAM_INT);
                                    $stmt->bindParam(':sale', $sale, PDO::PARAM_INT);
                                    $stmt->bindParam(':order_dept', $order_dept, PDO::PARAM_STR);
                                    $stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                                    $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                                    $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                                    $stmt->bindParam(':procure_sn', $procure_sn, PDO::PARAM_STR);
                                    $stmt->execute();
                                    $insert_count += $stmt->rowCount();
                                }


                                //// END OF ADDD STOCK FROMMMMMMMMMMMMMMMMMM DEPT//////////////////////////////
                            } else {
                                $debug_log[] = "SAME DEPT TRANSFER SKIPPED: stock_sn={$stock_sn}, dept={$approval_dept}";
                            }

                            $setdate = date('Y-m-d H:i:00');
                            $status = 'Approved';
                            $addUpdate = "UPDATE stock_table_request SET 
                            status = :status, 
                            approve_by = :approve_by, 
                            approve_date = :approve_date, 
                            approve_qty = :approve_qty, 
                            bal = :bal, 
                            collect_by = :collect_by, 
                            collect_date = :collect_date
                            WHERE sn = :sn";
                            $stmt = $db->prepare($addUpdate);
                            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                            $stmt->bindParam(':approve_by', $_SESSION['fullname'], PDO::PARAM_STR);
                            $stmt->bindParam(':approve_date', $setdate, PDO::PARAM_STR);
                            $stmt->bindParam(':approve_qty', $apr_qty, PDO::PARAM_STR);
                            $stmt->bindParam(':bal', $apr_qty, PDO::PARAM_STR);
                            $stmt->bindParam(':collect_by', $Collectedby, PDO::PARAM_STR);
                            $stmt->bindParam(':collect_date', $setdate, PDO::PARAM_STR);
                            $stmt->bindParam(':sn', $request_sn, PDO::PARAM_STR);
                            $stmt->execute();
                            $update_count += $stmt->rowCount();
                        } else {
                            $debug_log[] = "INSUFFICIENT STOCK: stock_sn={$stock_sn}, bal={$old_qty}, req={$apr_qty}";
                        }
                    } else {
                        $debug_log[] = "INVALID QTY / COLLECTOR: stock_sn={$stock_sn}";
                    }
                } else {


                    /// just 

                    if ($_SESSION['b4_approve_requisition_setup'] == 1 && $_SESSION['b4_rq_approval'] == 1) {
                        $setdate = date('Y-m-d H:i:00');
                        $addUpdate = "UPDATE stock_table_request SET 
                    b4_approve_by = :approve_by, 
                    b4_approve_date = :approve_date, 
                    b4_approve_qty = :approve_qty
                    WHERE sn = :sn";
                        $stmt = $db->prepare($addUpdate);
                        $stmt->bindParam(':approve_by', $_SESSION['fullname'], PDO::PARAM_STR);
                        $stmt->bindParam(':approve_date', $setdate, PDO::PARAM_STR);
                        $stmt->bindParam(':approve_qty', $apr_qty, PDO::PARAM_STR);
                        $stmt->bindParam(':sn', $request_sn, PDO::PARAM_STR);
                        $stmt->execute();
                        $update_count += $stmt->rowCount();
                    }
                }
            }  /// LOOOP ING

            $db->commit();
            if (($insert_count + $update_count) > 0) {

                echo json_encode([
                    'status' => 1,
                    'message' => 'Issued Successfully',
                    'affected_rows' => ($insert_count + $update_count),
                    'warnings' => $debug_log
                ]);
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'No insert or update occurred',
                    'debug' => $debug_log
                ]);
            }

            exit;

            echo  json_encode($response_main);
            exit;
        } catch (Exception $e) {

            $db->rollBack();

            echo json_encode([
                'status' => 0,
                'message' => 'Transaction Failed',
                'error' => $e->getMessage(),
                'debug' => $debug_log
            ]);
            exit;
        }
        /*         } catch (Exception $e) {
            $db->rollBack();
            $response_main['message'] = 'Error Occurred: ' . $e->getMessage(); // Include the actual error message
            $response_main['status'] = '0';
            echo json_encode($response_main);
            exit;
        } */
    } else {
        $response_main['message'] = 'Invalid Selection!!!';
        $response_main['status'] = '0';
        echo  json_encode($response_main);
        exit;
    }


    // Code for approve_all_issue
} elseif ($_POST['action'] == 'approve_all_request') {
    // Code for approve_all_request
}
