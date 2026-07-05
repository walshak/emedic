<?php include("../Connections/Conn.php");
session_start();

$response_main = array(
    'message' => '',
    'status' => ''
);

$captured_date = date("Y-m-d");
$enter_by = $_SESSION['fullname'];
$cust_patient_type = 'IN';

$setdate = date('Y-m-d H:i:00');
$pro_inv = $_REQUEST['inv'];
$stock = $_POST['stock'];
$invalid_exp = 0;

if (!empty($pro_inv)) {

    for ($i = 0; $i < count($pro_inv); $i++) {
        $inv_id = $pro_inv[$i];
        $exp_date = $_POST["exp_$inv_id"];

        /// if (!empty($exp_date)) {

        $timestamp = strtotime($exp_date); // Convert the string to a Unix timestamp

        //if ($timestamp === false) {
        //    $invalid_exp++;
        // } else {
        $current_timestamp = time(); // Get the current Unix timestamp
        /*
        if ($timestamp <= $current_timestamp) {
            $invalid_exp++; // Expiration date is not greater than current date
        } else {

            */
        $stmt = $db->prepare("SELECT entry_mode, qty, stock_total_unit, price_markup, hosp_price, cash_price 
        FROM stock_table WHERE sn = :sn");
        $stmt->execute([':sn' => $_POST["stock_sn_$inv_id"]]);

        if ($stmt->rowCount() > 0) {

            $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_unit = $rwx['stock_total_unit'];
            $price_markup = $rwx['price_markup'];
            $hosp_price = $rwx['hosp_price'];
            $cash_price = $rwx['cash_price'];  /// price_markup,,
            $apr_qty = $_POST["order_qty_$inv_id"];
            $apr_qty_actual = $_POST["order_qty_$inv_id"];
            $supplied_qty = $_POST["supply_qty_$inv_id"];
            $stock_sn = $_POST["stock_sn_$inv_id"];
            $order_dept = $_POST["order_dept_$inv_id"];
            $batch_no_ = $_POST["batch_no_$inv_id"];
            $main_ordered_qty = $_POST["main_ordered_qty_$inv_id"];
            $purchase_price = $_POST["purchase_price_$inv_id"];
            $Collectedby = $_SESSION['fullname'];
            $Collectedby = str_replace("'", "", trim($Collectedby));
            $C_qty = $rwx['qty'];


            if ($price_markup > 0) {
                $result = percentage_markup_cal($purchase_price, $total_unit, $price_markup);
                $hosp_price = $result['hosp_price'];
                $cash_price = $result['cash_price'];
                $buying_price = $result['buying_price'];
            }

            if ($batch_no_ == '') {
                $batch_no = $_POST["order_date_$inv_id"] . '/' . $apr_qty . '/' . $supplied_qty;
            }
            $procure_sn = $inv_id;

            $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn='$stock_sn' and cust_patient_id='$order_dept' ORDER BY sn DESC LIMIT 1");
            $stmt_chk->execute();
            if ($stmt_chk->rowCount() > 0) {
                $rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
                $old_qty = $rowwc['bal'];
            } else {
                $inven_desc = "Opening Stock (Procurment Data Entry)";
                $qtyOUT = 0;
                $old_qty = $C_qty;
                $addRow = "INSERT INTO stock_table_inven(stock_sn,inven_desc,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date) 
                    VALUES (:stock_sn,:inven_desc,:qtyIN,:qtyOUT,:c_qty,:buy,:sale,:cust_patient_id,:cust_patient_type,:enter_by,:captured_date)";
                $stmt = $db->prepare($addRow);
                $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                $stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                $stmt->bindParam(':qtyIN', $old_qty, PDO::PARAM_INT);
                $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                $stmt->bindParam(':c_qty', $old_qty, PDO::PARAM_INT);
                $stmt->bindParam(':buy', $buying_price, PDO::PARAM_INT);
                $stmt->bindParam(':sale', $cash_price, PDO::PARAM_INT);
                $stmt->bindParam(':cust_patient_id', $order_dept, PDO::PARAM_STR);
                $stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                $stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                $stmt->execute();
            }
            $apr_qty = $apr_qty * $total_unit;
            $c_qty = $old_qty + $apr_qty;
            $qtyIN = $apr_qty;
            $qtyOUT = 0;

            $invenDesc = "<b>PROCUREMENT RECIEVED BY: </b>" . $_SESSION["fullname"];
            $inven_desc = preg_replace("/'/", "", trim($invenDesc));

            $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn=:stock_sn and inven_desc=:inven_desc and qtyIN=:qtyIN and     qtyOUT=:qtyOUT and enter_by=:enter_by and captured_date=:captured_date and batch=:batch_no");

            $stmt_chk->bindParam(':stock_sn', $stock_sn);
            $stmt_chk->bindParam(':inven_desc', $inven_desc);
            $stmt_chk->bindParam(':qtyIN', $qtyIN);
            $stmt_chk->bindParam(':qtyOUT', $qtyOUT);
            $stmt_chk->bindParam(':enter_by', $enter_by);
            $stmt_chk->bindParam(':captured_date', $captured_date);
            $stmt_chk->bindParam(':batch_no', $batch_no);
            $stmt_chk->execute();

            if ($stmt_chk->rowCount() == 0) {

                $db->beginTransaction();
                try {


                    // Query 1
                    $query = "INSERT INTO stock_table_inven(stock_sn,inven_desc,batch,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date,procure_id) 
                        VALUES (:stock_sn,:inven_desc,:batch_no,:qtyIN,:qtyOUT,:c_qty,:buy,:sale,:order_dept,:cust_patient_type,:enter_by,:captured_date,:procure_sn)";
                    $stmt1 = $db->prepare($query);

                    $stmt1->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
                    $stmt1->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
                    $stmt1->bindParam(':batch_no', $batch_no, PDO::PARAM_STR);
                    $stmt1->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
                    $stmt1->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                    $stmt1->bindParam(':c_qty', $c_qty, PDO::PARAM_INT);
                    $stmt1->bindParam(':buy', $buying_price, PDO::PARAM_INT);
                    $stmt1->bindParam(':sale', $cash_price, PDO::PARAM_INT);
                    $stmt1->bindParam(':order_dept', $order_dept, PDO::PARAM_STR);
                    $stmt1->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                    $stmt1->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                    $stmt1->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                    $stmt1->bindParam(':procure_sn', $procure_sn, PDO::PARAM_STR);
                    $stmt1->execute();

                    $supplied_qty = $supplied_qty + $apr_qty_actual;

                    if ($supplied_qty == $main_ordered_qty) {
                        $pay_status = 2;
                    } else {
                        $pay_status = 1;
                    }

                    // Query 3
                    $upd_stmt = "UPDATE stock_table_procurment SET mfg_date = :mfg_date, expiry_date = :expiry_date, pay_status = :pay_status, supplied_qty = :supplied_qty, product_batch_no = :product_batch_no WHERE sn = :sn";
                    $stmt3 = $db->prepare($upd_stmt);
                    $stmt3->bindParam(':mfg_date', $_POST["mgf_$inv_id"], PDO::PARAM_STR);
                    $stmt3->bindParam(':expiry_date', $_POST["exp_$inv_id"], PDO::PARAM_STR);
                    $stmt3->bindParam(':pay_status', $pay_status, PDO::PARAM_STR);
                    $stmt3->bindParam(':supplied_qty', $supplied_qty, PDO::PARAM_STR);
                    $stmt3->bindParam(':product_batch_no', $_POST["batch_no_$inv_id"], PDO::PARAM_STR);
                    $stmt3->bindParam(':sn', $inv_id, PDO::PARAM_STR);
                    $stmt3->execute();



                    $upd_stmt = "UPDATE stock_table SET mfg_date = :mfg_date, expire_date = :expire_date, hosp_price=:hosp_price,cash_price=:cash_price, buying_cost=:buying_cost,date_last_update=:captured_date,status='active' WHERE sn = :sn";
                    $stmt4 = $db->prepare($upd_stmt);
                    $stmt4->bindParam(':mfg_date', $_POST["mgf_$inv_id"], PDO::PARAM_STR);
                    $stmt4->bindParam(':expire_date', $_POST["exp_$inv_id"], PDO::PARAM_STR);
                    $stmt4->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR);
                    $stmt4->bindParam(':cash_price', $cash_price, PDO::PARAM_STR);
                    $stmt4->bindParam(':buying_cost', $buying_price, PDO::PARAM_STR);
                    $stmt4->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
                    $stmt4->bindParam(':sn', $_POST["stock_sn_$inv_id"], PDO::PARAM_STR);
                    $stmt4->execute();
                    $db->commit();

                    $commit_count++;
                } catch (Exception $e) {
                    $db->rollBack();
                    $response_main['message'] = 'Error Occurred: ' . $e->getMessage(); // Include the actual error message
                    $response_main['status'] = '0';
                    echo json_encode($response_main);
                    exit;
                }
            }
        }
        ///}
        // }
        //  }
    } //// looopiing

    if ($invalid_exp > 0) {
        $response_main['message'] = 'Invalid Expiring Dates Found!!!';
        $response_main['status'] = '2';
        echo  json_encode($response_main);
        exit;
    }

    if ($commit_count > 0) {
        $response_main['message'] = 'Sucessful!';
        $response_main['status'] = '1';
        echo  json_encode($response_main);
        exit;
    } else {
        $response_main['message'] = 'Unsucessful!';
        $response_main['status'] = '0';
        echo  json_encode($response_main);
        exit;
    }
} else {
    $response_main['message'] = 'Invalid Selection!!!';
    $response_main['status'] = '0';
    echo  json_encode($response_main);
    exit;
}


function percentage_markup_cal($purchase_cost, $units, $percentage_markup)
{
    $amt = $purchase_cost / $units;
    $markup_amount = ($amt * $percentage_markup) / 100;
    $selling_price = $amt + $markup_amount;

    if ($selling_price > 0) {
        $hosp_price = $selling_price;
        $cash_price = $selling_price;
    }

    return array('hosp_price' => $hosp_price, 'cash_price' => $cash_price, 'buying_price' => $amt);
}
