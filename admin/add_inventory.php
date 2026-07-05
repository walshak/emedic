<?php
session_start();

if (empty($_SESSION['fullname']) && empty($_SESSION['dept_id'])) {
    // Stop execution if not set
    die("<div class='alert alert-danger'>Error: User session not properly set. Please log in again.</div>");
}

include('../Connections/Conn.php');

//if (isset($_POST["add_inven"])) {
$setdate = date('Y-m-d H:i:s');
$mgt_stock_id = $_POST["mgt_stock_id"];
$part = explode("__", $mgt_stock_id);
$mgt_stock_id = $part[0];
$buying_cost = 0;

$stmt_call = $db->prepare("SELECT buying_cost,cash_price 
                           FROM stock_table 
                           WHERE sn = :stock_sn 
                           LIMIT 1");
$stmt_call->execute([':stock_sn' => $mgt_stock_id]);
$rwx = $stmt_call->fetch(PDO::FETCH_ASSOC);

if ($rwx) {
    $sale  = $rwx['cash_price'];
    $buy = $rwx['buying_cost'];
}

$qty_type = $_POST["qty_type"];
$as_pack = $_POST["as_pack"];
$old_qty = $_POST["mgt_old_qty"];
$running_qty = $_POST["running_qty"];
$new_qty = $_POST["new_qty"];
$stock_total_unit = $_POST['stock_total_unit'];
$package_type = $_POST['package_type'];

if ($as_pack == 'pack') {
    $new_qty = $new_qty * $stock_total_unit;
}

if (isset($_POST['buying_cost']) && $_POST['buying_cost'] > 0) {
    $buying_cost = $_POST['buying_cost'];
    $buy = $buying_cost / $stock_total_unit;
}

$stock         = $_POST['stock'];
$batch_no      = isset($_POST['batch_no']) ? $_POST['batch_no'] : '';
$pur_date      = isset($_POST['pur_date_ip']) ? $_POST['pur_date_ip'] : date('Y-m-d');
$mfg_date      = isset($_POST['mfg_date_ip']) ? $_POST['mfg_date_ip'] : null;
$exp_date      = isset($_POST['exp_date_ip']) ? $_POST['exp_date_ip'] : null;
$supplier_id   = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : null;

$inven_desc = $_POST["desc"] . '' . $_POST["desc2"];
$inven_desc = str_replace("'", "", trim($inven_desc));

$enter_by   = $_SESSION['fullname'];
$dept_id    = $_SESSION['dept_id'];
$cust_patient_type = 'IN';
try {
    $db->beginTransaction();

    // === get latest balance ===
    $stmt = $db->prepare("SELECT bal FROM stock_table_inven 
                              WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id 
                              ORDER BY sn DESC LIMIT 1");
    $stmt->execute([':stock_sn' => $mgt_stock_id, ':cust_patient_id' => $dept_id]);

    if ($stmt->rowCount() == 0) {

        $stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :stock_sn");
        $stmt->bindParam(':stock_sn', $mgt_stock_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0 && $_SESSION['pharm_request_from_store'] == 0) {
            $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
            $stock_qty = $rwx['qty'];

            if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {

                if ($_SESSION['pharm_request_from_store'] == 0  && $stock_qty > 0) {
                    $inven_desc_openinig = "Opening Stock (Patient Dispensed)";
                    $qtyOUT = 0;

                    $addRow = "INSERT INTO stock_table_inven(stock_sn,inven_desc,qtyIN,qtyOUT,bal,buy,sale,cust_patient_id,cust_patient_type,enter_by,captured_date) 
						VALUES (:stock_sn,:inven_desc,:qtyIN,:qtyOUT,:c_qty,:buy,:sale, :cust_patient_id,:cust_patient_type,:enter_by,:captured_date)";
                    $stmt = $db->prepare($addRow);
                    $stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
                    $stmt->bindParam(':inven_desc', $inven_desc_openinig, PDO::PARAM_STR);
                    $stmt->bindParam(':qtyIN', $stock_qty, PDO::PARAM_INT);
                    $stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
                    $stmt->bindParam(':c_qty', $stock_qty, PDO::PARAM_INT);
                    $stmt->bindParam(':buy', $buy, PDO::PARAM_INT);
                    $stmt->bindParam(':sale', $sale, PDO::PARAM_INT);
                    $stmt->bindParam(':cust_patient_id', $dept_id, PDO::PARAM_STR);
                    $stmt->bindParam(':cust_patient_type', $cust_patient_type, PDO::PARAM_STR);
                    $stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
                    $stmt->bindParam(':captured_date', $setdate, PDO::PARAM_STR);
                    $stmt->execute();
                } else {
                    //  $response_main['message'] = '-Insufficient Stock Quantity <br> Confirm from Notes button/Store <br> Cancel/Delete add New Request' . $stock_qty;
                    // $response_main['status'] = '1';
                    // echo  json_encode($response_main);
                    //  exit;
                }
            }

            $runing_bal = $stock_qty;
        } else {
            $stock_qty = 0;
        }
    } else {
        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
        $runing_bal = ($row_rstSelect['bal'] <= 0) ? 0 : $row_rstSelect['bal'];
    }


    // === determine qtyIN / qtyOUT based on mode ===
    if ($qty_type == '1') { // PO Restock
        $c_qty   = $new_qty + $runing_bal;
        $qtyIN   = $new_qty;
        $qtyOUT  = 0;
        $cust_patient_type = 'IN';
    } elseif ($qty_type == '6' && $new_qty > 0) { // Manual Add
        $c_qty   = $new_qty + $runing_bal;
        $qtyIN   = $new_qty;
        $qtyOUT  = 0;
        $cust_patient_type = 'IN';
    } elseif ($qty_type == '4' && $runing_bal > 0) { // Remove
        if ($new_qty > $runing_bal) {
            $new_qty = $runing_bal;
            $c_qty   = 0;
            $qtyIN   = 0;
            $qtyOUT  = $runing_bal;
        } else {
            $c_qty   = $runing_bal - $new_qty;
            $qtyIN   = 0;
            $qtyOUT  = $new_qty;
        }
        $cust_patient_type = 'IN';
    } else {
        $db->rollBack();
        echo json_encode([
            "status"  => "warning",
            "message" => "<div class='alert alert-danger'>Invalid operation.</div>"
        ]);
        exit;
    }

    // === 4-hour duplicate protection ===
    $check_stmt = $db->prepare("
            SELECT sn FROM stock_table_inven
            WHERE stock_sn = :stock_sn
              AND inven_desc = :inven_desc
              AND qtyIN = :qtyIN
              AND qtyOUT = :qtyOUT
              AND cust_patient_id = :dept_id
              AND captured_date >= (NOW() - INTERVAL 4 HOUR)
            LIMIT 1
        ");
    $check_stmt->execute([
        ':stock_sn'   => $mgt_stock_id,
        ':inven_desc' => $inven_desc,
        ':qtyIN'      => $qtyIN,
        ':qtyOUT'     => $qtyOUT,
        ':dept_id'    => $dept_id
    ]);

    if ($check_stmt->rowCount() > 0) {
        $db->rollBack();
        echo json_encode([
            "status"  => "warning",
            "message" => "<div class='alert alert-warning'><h2>Duplicate request ignored (same request within 4 hours).</h2></div>"
        ]);
        exit;
    }

    // === insert into stock_table_inven ===
    $add_stmt = $db->prepare("INSERT INTO stock_table_inven 
            (stock_sn, inven_desc, batch, qtyIN, qtyOUT, bal,buy,sale, cust_patient_id, 
             cust_patient_type, return_status, enter_by, captured_date) 
            VALUES 
            (:stock_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :buy,:sale, :cust_patient_id, 
             :cust_patient_type, 0, :enter_by, :captured_date)");

    $add_stmt->execute([
        ':stock_sn'          => $mgt_stock_id,
        ':inven_desc'        => $inven_desc,
        ':batch'             => $batch_no,
        ':qtyIN'             => $qtyIN,
        ':qtyOUT'            => $qtyOUT,
        ':bal'               => $c_qty,
        ':buy'               => $buy,
        ':sale'               => $sale,
        ':cust_patient_id'   => $dept_id,
        ':cust_patient_type' => $cust_patient_type,
        ':enter_by'          => $enter_by,
        ':captured_date'     => $setdate
    ]);

    // === update stock_table if pharmacy/store ===
    if (in_array(strtoupper($_SESSION['dept_group_name']), ['STORE', 'PHARMACY'])) {
        $active = 'active';
        if ($qty_type == '1') {
            $upd_stmt = $db->prepare("UPDATE stock_table 
                    SET expire_date = :expire_date, qty = :qty, status = :status 
                    WHERE sn = :sn");
            $upd_stmt->execute([
                ':expire_date' => $exp_date,
                ':qty'         => $c_qty,
                ':status'      => $active,
                ':sn'          => $mgt_stock_id
            ]);
        } else {

            $supplier_id = $supplier_id ?: null;

            $upd_stmt = $db->prepare("UPDATE stock_table 
                    SET qty = :qty, supplier_id = :supplier_id, status = :status 
                    WHERE sn = :sn");
            $upd_stmt->execute([
                ':qty'   => $c_qty,
                ':supplier_id'   => $supplier_id,
                ':status' => $active,
                ':sn'    => $mgt_stock_id
            ]);
        }
    }

    // === If PO Restocking, insert into procurement table ===
    if ($qty_type == '1') {
        $total_cost    = $buying_cost * $new_qty;
        $stock_name    = $_POST['stockName'];
        $PO_dept_name  = $_SESSION['dept_name'];
        $approve_by    = $_SESSION['fullname'];
        $approve_date  = date('Y-m-d');
        $batch_no_unique = $pur_date . '-' . $supplier_id . '-' . time();
        $status        = 'yes';
        $approval_stages = 2;
        $remarks       = 'Procurement/PO/Express';
        $finish_status = 0;
        $pay_status    = 2;
        $navigation    = ($_POST['stock'] == 'o') ? 'Store' : 'Pharmacy';
        $price_per_unit = ($new_qty > 0) ? floor($buying_cost / $new_qty) : 0;

        $proc_stmt = $db->prepare("INSERT INTO stock_table_procurment 
                (stock_sn, order_by, order_date, order_qty, purchase_price, price_per_unit, total_cost, 
                 mfg_date, expiry_date, supplier_id, supplied_qty, dept, PO_dept_name, approve_by, approve_date, 
                 batch_no, status, approval_stages, remarks, finish_status, pay_status, 
                 navigation, old_qty, stock_name, stock_table) 
                VALUES 
                (:stock_sn, :order_by, :order_date, :order_qty, :purchase_price, :price_per_unit, :total_cost, 
                 :mfg_date, :expiry_date, :supplier_id, :supplied_qty, :dept, :PO_dept_name, :approve_by, :approve_date, 
                 :batch_no, :status, :approval_stages, :remarks, :finish_status, :pay_status, 
                 :navigation, :old_qty, :stock_name, :stock_table)");

        $proc_stmt->execute([
            ':stock_sn'        => $mgt_stock_id,
            ':order_by'        => $enter_by,
            ':order_date'      => $pur_date,
            ':order_qty'       => $new_qty,
            ':purchase_price'  => $buying_cost,
            ':price_per_unit'  => $price_per_unit,
            ':total_cost'      => $total_cost,
            ':mfg_date'        => $mfg_date,
            ':expiry_date'     => $exp_date,
            ':supplier_id'     => $supplier_id,
            ':supplied_qty'    => $new_qty,
            ':dept'            => $dept_id,
            ':PO_dept_name'    => $PO_dept_name,
            ':approve_by'      => $approve_by,
            ':approve_date'    => $approve_date,
            ':batch_no'        => $batch_no_unique,
            ':status'          => $status,
            ':approval_stages' => $approval_stages,
            ':remarks'         => $remarks,
            ':finish_status'   => $finish_status,
            ':pay_status'      => $pay_status,
            ':navigation'      => $navigation,
            ':old_qty'         => $running_qty,
            ':stock_name'      => $stock_name,
            ':stock_table'     => $navigation
        ]);
    }


    $db->commit();

    $qty_stmt = $db->prepare("
    SELECT bal 
    FROM stock_table_inven 
    WHERE stock_sn = :stock_sn 
      AND cust_patient_id = :dept_id
    ORDER BY sn DESC 
    LIMIT 1");
    $qty_stmt->execute([
        ':stock_sn' => $mgt_stock_id,
        ':dept_id'  => $dept_id
    ]);
    $new_bal = ($qty_stmt->rowCount() > 0) ? $qty_stmt->fetch(PDO::FETCH_ASSOC)['bal'] : 0;


    if ($package_type != '' && $stock_total_unit > 1) {
        $pack = $new_bal / $stock_total_unit;
        $ppp = explode('.', $pack);
        $pack = $ppp[0];
        $bb = $new_bal % $stock_total_unit;
        if ($stock_total_unit > 1) {
            $pack = $pack;
        } else {
            $pack = 0;
        }

        if ($stock_total_unit > 1) {
            $pcs = $bb;
        } else {
            $pcs = $new_bal;
        }

        $desc =  $pack . ' ' . $package_type . ' & ' . $pcs . ' pcs Remaining';
    } else {
        $desc = $new_bal;
    }

    echo json_encode([
        "status"   => "success",
        "message"  => "<div class='alert alert-success'><h2>Inventory Executed Successfully.</h2></div>",
        "new_qty"  => 'Current Quantity: ' . $desc
    ]);
    exit;
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        "status"  => "error",
        "message" => "<div class='alert alert-danger'><h2>Error occurred: " . $e->getMessage() . '=' . $supplier_id . "</h2></div>"
    ]);
    exit;
}
