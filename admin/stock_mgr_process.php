<?php

$active = "active";
$one = 1;
$zero = 0;
$error_status = null;
$error_msg = '';

$stmt = $db->prepare("DELETE FROM stock_table_procurment WHERE batch_no is null or batch_no =''");
$stmt->execute();

$twoWeeksAgo = date('Y-m-d', strtotime('-2 weeks'));
$status = 'no';

$sql = "DELETE FROM stock_table_procurment WHERE order_date < :twoWeeksAgo and status=:status";
$stmt = $db->prepare($sql);
$stmt->bindParam(':twoWeeksAgo', $twoWeeksAgo, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->execute();



if ($_SESSION['navigate'] == 'pharmacy' or $_SESSION['navigate'] == 'Pharmacy') {
    $navigation = 'pharmacy';
    $stock_table = 'pharmacy';
    $stock = 'p';
} elseif ($_SESSION['navigate'] == 'nursing') {
    $navigation = 'nursing';
    $stock_table = "Store";
    $stock = "o";
} elseif ($_SESSION['navigate'] == 'investigations') {
    $navigation = 'investigation';
    $stock_table = "Store";
    $stock = "o";
} else {
    $navigation = 'Store';
    $stock_table = "Store";
    $stock = "o";
}

//	investigations


if (isset($_GET['del_ordered'], $_GET['stock'])) {
    $del_ordered = $_GET['del_ordered'];
    $stock       = $_GET['stock'];

    // Split del_ordered into parts
    $part = explode("/", $del_ordered);
    if (count($part) < 3) {
        // Invalid format
        header("Location: index.php?stock=" . urlencode($stock) . "&error=invalid_request");
        exit;
    }

    $batch_no   = $part[0];
    $order_date = $part[1];
    $order_by   = $part[2];

    try {
        $stmt = $db->prepare("DELETE FROM stock_table_procurment WHERE batch_no = :batch_no");
        $stmt->execute([':batch_no' => $batch_no]);

        // Check if a row was deleted
        $status = ($stmt->rowCount() > 0) ? 'deleted' : 'not_found';
        header("Location: index.php?stock=" . urlencode($stock) . "&status=" . $status . "&pdr");
        exit;
    } catch (Exception $e) {
        // Log error if needed
        error_log("Error deleting procurement: " . $e->getMessage());
        header("Location: index.php?stock=" . urlencode($stock) . "&status=error&pdr");
        exit;
    }
}

if (isset($_GET['dl'], $_GET['stock'])) {
    $stock_sn = $_GET['dl'];
    $stock    = $_GET['stock'];
    $delete   = 'delete';

    // Check if stock_sn exists in inventory
    $stmt = $db->prepare("SELECT 1 FROM stock_table_inven WHERE stock_sn = :stock_sn LIMIT 1");
    $stmt->execute([':stock_sn' => $stock_sn]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {

        // No inventory records → delete from stock_table
        $stmt = $db->prepare("DELETE FROM stock_table WHERE sn = :sn");
        $stmt->execute([':sn' => $stock_sn]);
    } else {

        // Inventory exists → check if quantity is zero
        $stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :sn LIMIT 1");
        $stmt->execute([':sn' => $stock_sn]);
        $qty = $stmt->fetchColumn();

        if ($qty == 0) {
            // Qty is zero → allow marking as deleted
            $stmt = $db->prepare("UPDATE stock_table SET status = :status WHERE sn = :sn");
            $stmt->execute([
                ':status' => $delete,
                ':sn'     => $stock_sn
            ]);
        } else {
            // Qty > 0 → do not delete
            header("Location: index.php?stock=" . urlencode($stock) . "&error=qty_not_zero");
            exit;
        }
    }

    // Redirect after action
    header("Location: index.php?stock=" . urlencode($stock) . "&deleted");
    exit;
}


if (isset($_GET["pay_del"])) {
    $pay_del = $_GET["pay_del"];
    $stock = $_GET["stock"];
    $stmt = $db->prepare("DELETE FROM stock_table_procure_pay WHERE sn='$pay_del'");
    $stmt->execute();
    header("location:index.php?stock=$stock&pdr&deleted");
}

if (isset($_POST["close_Transaction"])) {
    $order_by = $_POST["order_by"];
    $order_date = $_POST["order_date"];
    $supplier_id = $_POST["supplier_id"];
    $stock = $_POST["stock"];
    $pro_inv = $_REQUEST['inv'];

    if (!empty($pro_inv)) {
        $upd_stmt = $db->prepare('UPDATE stock_table_procurment SET order_qty = :order_qty, purchase_price = :purchase_price, total_cost = :total_cost, pay_status = :pay_status WHERE sn = :sn');

        for ($i = 0; $i < count($pro_inv); $i++) {
            $inv_id = $pro_inv[$i];
            $upd_stmt->bindParam(':order_qty', $_POST["order_qty_$inv_id"]);
            $upd_stmt->bindParam(':purchase_price', $_POST["buying_cost_$inv_id"]);
            $upd_stmt->bindParam(':total_cost', $_POST["total2_$inv_id"]);
            $upd_stmt->bindParam(':pay_status', $one);
            $upd_stmt->bindParam(':sn', $inv_id);
            $upd_stmt->execute();
        }
    } else {
        header("location:index.php?stock=$stock&pdr&err_sel");
    }
}


if (isset($_POST["p_reverse_rq"])) {
    $p_reverse_rq = $_POST["p_reverse_rq"];

    $stmt = $db->query("SELECT r.*, s.qty FROM stock_table_request r 
    INNER JOIN stock_table s on s.sn=r.stock_sn WHERE r.sn='$p_reverse_rq'");
    if ($stmt->rowCount() > 0) {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        $apr_qty = $roww['approve_qty'];
        $stock_sn = $roww['stock_sn'];
        $order_dept = $roww['order_dept'];
        $Collectedby = $_SESSION['fullname'];
        $Collectedby = str_replace("'", "", trim($Collectedby));
        $batch_no = $roww['batch_no'];
        $C_qty = $roww['qty'];
        $sn = $roww['sn'];
        $total_unit = '';
        $stock_post = 'RV_';
        $add_or_remove = 'remove';
        $procure_sn = '';
        $reverse = '1';

        $query_remove = save_invent_bal($db, $total_unit, $apr_qty, $stock_sn, $Collectedby, $add_or_remove, $batch_no, $C_qty, $order_dept, $reverse, $stock_post, $procure_sn);
        /// get dept id for store room or pharmacy

        if ($query_remove == 'success') {
            ///// ADD APPROVE QTY TO TABLE   /////
            $approval_dept = $_SESSION['dept_id'];
            $add_or_remove = 'add';
            $stock_post = 'RV';
            $query_add = save_invent_bal(
                $db,
                $total_unit,
                $apr_qty,
                $stock_sn,
                $Collectedby,
                $add_or_remove,
                $batch_no,
                $C_qty,
                $approval_dept,
                $reverse,
                $stock_post,
                $procure_sn
            );
            $addUpdate = "UPDATE stock_table_request 
	SET status='reverse' 
	WHERE sn='$p_reverse_rq'";
            $db->exec($addUpdate);
            header("location:index.php?stock=rpt&sv");
        }
    } else {
        header("location:index.php?stock=rpt");
    }
}

if (isset($_POST["p_reverse"])) {
    $p_reverse = $_POST["p_reverse"];
    $supplied_qqty = $_POST["supplied_qqty"];

    $stmt = $db->query("SELECT r.*, s.qty FROM stock_table_inven r inner join stock_table s on s.sn=r.stock_sn WHERE (reserve_status=0  or reserve_status='') and procure_id='$p_reverse'");
    if ($stmt->rowCount() > 0) {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        $apr_qty = $supplied_qqty; ///$roww['qtyIN'];
        $stock_sn = $roww['stock_sn'];
        $enter_by = $roww['enter_by'];
        $order_dept = $roww['cust_patient_id'];
        $Collectedby = $_SESSION['fullname'];
        $Collectedby = str_replace("'", "", trim($Collectedby));
        $batch_no = $roww['batch'] . '/' . $supplied_qqty;
        $C_qty = $roww['qty'];
        $sn = $roww['sn'];
        $total_unit = '';
        $stock_post = 'RV';

        $add_or_remove = 'remove';
        $procure_sn = $p_reverse;
        $reverse = '1';
        $query = save_invent_bal($db, $total_unit, $apr_qty, $stock_sn, $Collectedby, $add_or_remove, $batch_no, $C_qty, $order_dept, $reverse, $stock_post, $procure_sn);

        if ($query == 'success') {
            $addUpdate = "UPDATE stock_table_inven SET reserve_status='1' WHERE sn='$sn'";
            $db->exec($addUpdate);
            $addUpdate = "UPDATE stock_table_procurment SET pay_status='1',status='reverse' WHERE sn='$p_reverse'";
            $db->exec($addUpdate);
        }
        header("location:index.php?stock=rpt&sv");
    } else {
        ///echo '<strong>Not </strong>';
    }
}

if (isset($_GET["rej"])) {
    $rej = $_GET["rej"];
    $stock = $_GET["stock"];
    $stmt = "UPDATE stock_table_request SET status='reject', seen=1 WHERE sn='$rej'";
    $db->exec($stmt);
    header("location:index.php?stock=$stock&rrq");
}

if (isset($_GET["del"])) {
    $del = $_GET["del"];
    $stmt = $db->prepare("DELETE FROM stock_table_request WHERE (status='reject' or status='pending') and sn='$del'");
    $stmt->execute();
}

//echo $batch_no . '/' . $supplier_id . '/' . $stock; 

if (isset($_GET["proc"])) {
    $del = $_GET["proc"];
    $stock = $_GET["stock"];
    /// $batch_no.'/'.$supplier_id.'/'.$stock;
    $partss = explode("/", $stock);
    $batch_no = $partss[0];

    $stmt = $db->query("SELECT total_cost FROM stock_table_procurment WHERE sn='$del'");
    if ($stmt->rowCount() > 0) {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_cost_dele = $roww['total_cost'];
    }

    $stmt = $db->query("SELECT SUM(total_cost) AS total_cost 
    FROM stock_table_procurment WHERE batch_no='$batch_no'");
    if ($stmt->rowCount() > 0) {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        echo  $total_cost = $roww['total_cost'] - $total_cost_dele;
    } else {
        $total_cost = 0;
    }

    $stmt = $db->query("SELECT SUM(amount) AS amount FROM stock_table_procure_pay WHERE batch_no='$batch_no'");
    if ($stmt->rowCount() > 0) {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        $amount = $roww['amount'];
    } else {
        $amount = 0;
    }

    if ($amount > $total_cost) {
        header("location:index.php?stock=$stock&pdr&Error_payment");
    } else {
        $stmt = $db->prepare("DELETE FROM stock_table_procurment WHERE status='no' and sn='$del'");
        $stmt->execute();
        header("location:index.php?stock=$stock&pdr&sv");
    }
}

if (isset($_POST['del_upload'])) {
    $id = $_POST['del_upload'];
    $updateSQL = "DELETE FROM stock_combo WHERE sn=:id";
    $stmt = $db->prepare($updateSQL);
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
    $msg = "Deleted";
    $msg_type = "success";
}


if (isset($_POST["save_purchase_order"]) or isset($_POST["reject_purchase_order"])) {
    $setdate = date('Y-m-d H:i:00');
    $pro_inv = $_REQUEST['inv'];
    $stock = $_POST['stock'];
    $status_link = $_POST['status_link'];
    $batch_no = $_POST['batch_no_'];
    $invoice_number = $_POST['invoice_number'];  //// continue here tmrw inshaallah
    if (!empty($invoice_number)) {
        $batch_no = $batch_no . '(' . $invoice_number . ')';
    }


    $approval_stages = $_POST['approval_stages'];

    if (!empty($pro_inv)) {

        for ($i = 0; $i < count($pro_inv); $i++) {
            $inv_id = $pro_inv[$i];

            if ($status_link == 'reverse') {
                // Prepare the SQL statement
                $upd_stmt = "UPDATE stock_table_procurment SET approve_by = '', status = 'no', approve_date = '', pay_status = '0' WHERE sn = :sn";
                $stmt = $db->prepare($upd_stmt);
                $stmt->bindParam(':sn', $inv_id, PDO::PARAM_STR);
                $stmt->execute();
            } elseif ($status_link == 'approve') {
                // Prepare the SQL statement
                $upd_stmt = "UPDATE stock_table_procurment SET approve_by = :approve_by, status = 'yes', approve_date = :approve_date, pay_status = '0' WHERE sn = :sn";
                $stmt = $db->prepare($upd_stmt);
                $stmt->bindParam(':approve_by', $_SESSION['fullname'], PDO::PARAM_STR);
                $stmt->bindParam(':approve_date', $setdate, PDO::PARAM_STR);
                $stmt->bindParam(':sn', $inv_id, PDO::PARAM_STR);
                $stmt->execute();
            } elseif ($status_link == 'edit') {

                if (isset($_POST["reject_purchase_order"])) {
                    $reject_reason = $_POST["reject_reason"];
                    $who_is_rejecting = $_POST["who_is_rejecting"];
                    $Procurement_Officer_msg = $_POST["Procurement_Officer_msg"] . '<br>' . $reject_reason;

                    if ($who_is_rejecting == 'ack') {
                        $approval_stages = 0;
                        $view_status = 0;
                    }
                } else {
                    $view_status = 0;
                    $Procurement_Officer_msg = $_POST["Procurement_Officer_msg"];
                }

                /*                 $upd_stmt = "UPDATE stock_table_procurment SET order_qty = :order_qty, purchase_price = :purchase_price, total_cost = :total_cost, old_qty=:old_qty,  approval_stages = :approval_stages, Procurement_Officer_msg=:Procurement_Officer_msg, view_status=:view_status	 WHERE sn = :sn";
                $stmt = $db->prepare($upd_stmt);
                $stmt->bindParam(':order_qty', $_POST["order_qty_$inv_id"], PDO::PARAM_STR);
                $stmt->bindParam(':purchase_price', $_POST["buying_cost_$inv_id"], PDO::PARAM_STR);
                $stmt->bindParam(':total_cost', $_POST["total2_$inv_id"], PDO::PARAM_STR);
                $stmt->bindParam(':old_qty', $_POST["old_qty_$inv_id"], PDO::PARAM_STR);
                $stmt->bindParam(':approval_stages', $approval_stages, PDO::PARAM_STR);
                $stmt->bindParam(':Procurement_Officer_msg', $Procurement_Officer_msg, PDO::PARAM_STR);
                $stmt->bindParam(':view_status', $view_status, PDO::PARAM_STR);
                $stmt->bindParam(':sn', $inv_id, PDO::PARAM_STR);
                $stmt->execute(); */


                try {
                    // Make sure PDO throws exceptions
                    /// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $upd_stmt = "
        UPDATE stock_table_procurment 
        SET 
            order_qty = :order_qty, 
            purchase_price = :purchase_price, 
            total_cost = :total_cost, 
            old_qty = :old_qty,  
            approval_stages = :approval_stages, 
            Procurement_Officer_msg = :Procurement_Officer_msg, 
            batch_no = :batch_no,
            view_status = :view_status
        WHERE sn = :sn
    ";

                    $stmt = $db->prepare($upd_stmt);

                    $stmt->bindParam(':order_qty', $_POST["order_qty_$inv_id"], PDO::PARAM_STR);
                    $stmt->bindParam(':purchase_price', $_POST["buying_cost_$inv_id"], PDO::PARAM_STR);
                    $stmt->bindParam(':total_cost', $_POST["total2_$inv_id"], PDO::PARAM_STR);
                    $stmt->bindParam(':old_qty', $_POST["old_qty_$inv_id"], PDO::PARAM_STR);
                    $stmt->bindParam(':approval_stages', $approval_stages, PDO::PARAM_STR);
                    $stmt->bindParam(':Procurement_Officer_msg', $Procurement_Officer_msg, PDO::PARAM_STR);
                    $stmt->bindParam(':batch_no', $batch_no, PDO::PARAM_STR);
                    $stmt->bindParam(':view_status', $view_status, PDO::PARAM_STR);
                    $stmt->bindParam(':sn', $inv_id, PDO::PARAM_STR);

                    $stmt->execute();

                    // Check if any row was updated
                    if ($stmt->rowCount() > 0) {
                        echo "Update successful.";
                    } else {
                        echo "No changes made or record not found.";
                        exit;
                    }
                } catch (PDOException $e) {
                    echo "Update failed: " . $e->getMessage();
                    exit;
                }
            }
        }
        /// stock=p&pdr
        header("location:index.php?stock=$stock&pdr&sv");
    } else {
        header("location:index.php?stock=$stock&pdr&err_sel");
    }
}



if (isset($_POST['update_hmo_prices_btn'])) {

    try {
        // Begin transaction
        $db->beginTransaction();

        $no_of_hmos_to_update = $_POST['all_'] - 1;

        $stock_total_unit_2 = (float)$_POST['stock_total_unit_2'];
        $purchase_cost_2    = (float)$_POST['purchase_cost_2'];
        $hosp_no_mark_up    = (float)$_POST['hosp_no_mark_up'];

        $hosp_price_2       = (float)$_POST['hosp_price_2'];
        $hosp_price_2_former = (float)$_POST['hosp_price_2_former'];

        $product_name_former = trim($_POST['product_name_former']);

        $cash_price_2        = (float)$_POST['cash_price_2'];
        $cash_price_2_former = (float)$_POST['cash_price_2_former'];

        $NHIS_price_2        = (float)$_POST['NHIS_price_2'];
        $NHIS_price_2_former = (float)$_POST['NHIS_price_2_former'];

        $stock_sn_main       = (int)$_POST['stock_sn_main'];

        // Compute prices if markup is used
        if ($hosp_no_mark_up > 0) {
            $result = percentage_markup_cal($purchase_cost_2, $stock_total_unit_2, $hosp_no_mark_up);
            $hosp_price = $result['hosp_price'];
            $cash_price = $result['cash_price'];
        } else {
            $hosp_price = $hosp_price_2;
            $cash_price = $cash_price_2;
        }

        // Update main stock_table if prices have changed
        if ($hosp_price_2_former != $hosp_price || $cash_price_2_former != $cash_price || $NHIS_price_2_former != $NHIS_price_2) {

            $active = 'active';
            $stmt = $db->prepare('UPDATE stock_table SET 
                buying_cost = :buying_cost,
                nhis_price = :nhis_price,
                hosp_price = :hosp_price,
                cash_price = :cash_price,
                status = :status,
                price_markup = :price_markup
                WHERE sn = :sn
            ');

            $stmt->execute([
                ':buying_cost'   => $purchase_cost_2,
                ':nhis_price'    => $NHIS_price_2,
                ':hosp_price'    => $hosp_price,
                ':cash_price'    => $cash_price,
                ':status'        => $active,
                ':price_markup'  => $hosp_no_mark_up,
                ':sn'            => $stock_sn_main
            ]);

            // Log the update
            $setdatetime = date("Y-m-d H:i:s");
            $action = "Stock Add/Update";
            $desc = $product_name_former . ' Former Price: ' . $hosp_price_2_former .
                ' / New: ' . $hosp_price_2 . ' price_markup: ' . $hosp_no_mark_up;

            $sql = $db->prepare("INSERT INTO patient_staff_logs 
                (descriptions, staff_name, action, date_and_time)
                VALUES (:descriptions, :staff_name, :action, :date_and_time)");
            $sql->execute([
                ':descriptions' => $desc,
                ':staff_name'   => $_SESSION['fullname'],
                ':action'       => $action,
                ':date_and_time' => $setdatetime
            ]);
        }

        // Prepare HMO tariff update statement
        $stmt = $db->prepare('UPDATE hmo_stocks_tariff 
            SET price = :price, price_markup = :price_markup 
            WHERE stock_sn = :stock_sn AND hmo = :hmo');

        for ($i = 0; $i <= $no_of_hmos_to_update; $i++) {

            $stock_sn     = (int)$_POST['stock_sn'][$i];
            $price_input  = (float)$_POST['prices'][$i];
            $price_markup = isset($_POST['price_markup'][$i]) ? (float)$_POST['price_markup'][$i] : 0;
            $hmo          = $_POST['hmos'][$i];

            // Default price is manual entry
            $price = $price_input;

            // Recalculate if markup is set
            if ($price_markup > 0) {
                $stmtx = $db->prepare("SELECT stock_total_unit, hosp_price, buying_cost 
                    FROM stock_table WHERE sn = :sn LIMIT 1");
                $stmtx->execute([':sn' => $stock_sn]);

                if ($rwxxx = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                    $resultx = percentage_markup_cal(
                        $rwxxx['buying_cost'],
                        $rwxxx['stock_total_unit'],
                        $price_markup
                    );
                    $price = $resultx['hosp_price'];
                }
            }

            // Execute HMO update
            $stmt->execute([
                ':price'        => $price,
                ':price_markup' => $price_markup,
                ':stock_sn'     => $stock_sn,
                ':hmo'          => $hmo
            ]);
        }

        // Commit transaction
        $db->commit();
        //$sv = 1;
        echo "<div class='alert alert-success'>
            <strong>Success!</strong> HMO Prices Updated Successfully.
        </div>";
    } catch (Exception $e) {
        // Roll back on error
        $db->rollBack();
        echo "<h3 style='color:red;'>Error updating prices: " . htmlspecialchars($e->getMessage()) . "</h3>";
    }
}



if (isset($_POST["add_stock"])) {

    // ROUTINE SANITIZATION$_POST['category']
    $stock_id       = $_POST['stock_id'];
    $stock_table    = $_POST['stock_table'];
    $navigation    = $_POST['stock_table'];
    $priveleges     = $_POST['priveleges'];
    $category     = $_POST['category'];

    $price_markup   = floatval($_POST['percentage_markup']);
    $stockname      = trim($_POST['stockname']);
    $expire_date    = !empty($_POST['expired']) ? $_POST['expired'] : null;


    $categoryUpper = strtoupper($category);
    if (
        $_SESSION['rights'] === 'NS' ||
        in_array($categoryUpper, ['NURSING CONSUMABLES', 'NURSING CONSUMABLE'], true)
    ) {
        $navigation = 'nursing';
    } elseif (
        $_SESSION['rights'] === 'LB' ||
        in_array($categoryUpper, ['INVESTIGATION CONSUMABLES', 'INVESTIGATION CONSUMABLE'], true)
    ) {
        $navigation = 'investigation';
    }

    // ACCESS LOGIC
    if (
        $_SESSION['pharm_request_from_store'] == 0 &&
        ($_SESSION['stock_mgr'] == '1' || $_SESSION['stock_mgr_pharm'] == '1')
    ) {
        $qty = $_POST['qty'];
    } elseif (
        $_SESSION['pharm_request_from_store'] == 1 &&
        $_SESSION['stock_mgr'] == '1'
    ) {
        $qty = $_POST['qty'];
    } else {
        $qty = 0;
    }

    // REMOVE UNWANTED CHARACTERS
    $charactersToRemove = '*=";,:\'';
    $pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
    $stockname = preg_replace($pattern, '', $stockname);

    // COVERAGE LOGIC
    if (!empty($_POST["NHIS_price"]) && $_POST["NHIS_price"] > 0) {
        $coverage       = "PRIVATE/NHIS";
        $NHIS_price     = $_POST["NHIS_price"];
        $insurance_type = $_POST['insurance_type'] ?: '1';
    } else {
        $coverage       = "PRIVATE";
        $insurance_type = null;
        $NHIS_price     = null;
    }

    // PRICE LOGIC
    if (!empty($_POST['cash_price'])) {
        $cash_price = $_POST['cash_price'];
    } elseif (!empty($_POST['hosp_price'])) {
        $cash_price = $_POST['hosp_price'];
    } else {
        $cash_price = 0;
    }

    $purchase_cost = floatval($_POST['purchase_cost']);
    $units = intval(isset($_POST['units']) ? $_POST['units'] : 1);

    // APPLY MARKUP
    if ($price_markup > 0 && function_exists("percentage_markup_cal")) {
        $result      = percentage_markup_cal($purchase_cost, $units, $price_markup);
        $hosp_price  = $result['hosp_price'];
        $cash_price  = $result['cash_price'];
    } else {
        $hosp_price = isset($_POST['hosp_price']) ? $_POST['hosp_price'] : 0;
    }

    // IF NEW STOCK
    if (empty($stock_id)) {

        $capture_date = date("Y-m-d");

        try {
            $stmt = $db->prepare('SELECT * FROM stock_table WHERE product_name = :product_name');
            $stmt->execute([':product_name' => ucwords($stockname)]);

            if ($stmt->rowCount() == 0) {

                $stmt = $db->prepare(
                    'INSERT INTO stock_table 
                    (product_name, category, navigation, stock_table, coverage, insurance_type, generic_name, 
                     presentation, buying_cost, nhis_price, hosp_price, cash_price, package_type, 
                     stock_total_unit, qty, reorder_level, expire_date, date_captured, entry_mode, price_markup) 
                    VALUES 
                    (:product_name, :category, :navigation, :stock_table, :coverage, :insurance_type, 
                     :generic_name, :presentation, :buying_cost, :nhis_price, :hosp_price, :cash_price, 
                     :package_type, :stock_total_unit, :qty, :reorder_level, :expire_date, :date_captured, 
                     :entry_mode, :price_markup)'
                );

                $stmt->execute([
                    ':product_name'     => ucwords($stockname),
                    ':category'         => $_POST['category'],
                    ':navigation'       => $navigation,
                    ':stock_table'      => $stock_table,
                    ':coverage'         => $coverage,
                    ':insurance_type'   => $insurance_type,
                    ':generic_name'     => $_POST['GenericName'],
                    ':presentation'     => $_POST['presentation'],
                    ':buying_cost'      => $purchase_cost,
                    ':nhis_price'       => $NHIS_price,
                    ':hosp_price'       => $hosp_price,
                    ':cash_price'       => $cash_price,
                    ':package_type'     => $_POST['packagetype'],
                    ':stock_total_unit' => $units,
                    ':qty'              => $qty,
                    ':reorder_level'    => $_POST['reorder'],
                    ':expire_date'      => $expire_date,
                    ':date_captured'    => $capture_date,
                    ':entry_mode'       => $_POST['stock_request'],
                    ':price_markup'     => $price_markup
                ]);
            }
        } catch (PDOException $e) {
            echo "Insert failed: " . $e->getMessage();
        }
    }

    // IF UPDATING STOCK
    else {

        try {
            $active = 'active';

            if ($priveleges == 'yes') {
                $sql = 'UPDATE stock_table SET 
                    product_name=:product_name,
                    category=:category,
                    coverage=:coverage,
                    insurance_type=:insurance_type,
                    generic_name=:generic_name,
                    presentation=:presentation,
                    buying_cost=:buying_cost,
                    nhis_price=:nhis_price,
                    hosp_price=:hosp_price,
                    cash_price=:cash_price,
                    package_type=:package_type,
                    stock_total_unit=:stock_total_unit,
                    reorder_level=:reorder_level,
                    expire_date=:expire_date,
                    status=:status,
                    entry_mode=:entry_mode,
                    price_markup=:price_markup
                    WHERE sn=:sn';

                $params = [
                    ':product_name'     => ucwords($stockname),
                    ':category'         => $_POST['category'],
                    ':coverage'         => $coverage,
                    ':insurance_type'   => $insurance_type,
                    ':generic_name'     => $_POST['GenericName'],
                    ':presentation'     => $_POST['presentation'],
                    ':buying_cost'      => $purchase_cost,
                    ':nhis_price'       => $NHIS_price,
                    ':hosp_price'       => $hosp_price,
                    ':cash_price'       => $cash_price,
                    ':package_type'     => $_POST['packagetype'],
                    ':stock_total_unit' => $units,
                    ':reorder_level'    => $_POST['reorder'],
                    ':expire_date'      => $expire_date,
                    ':status'           => $active,
                    ':entry_mode'       => $_POST['stock_request'],
                    ':price_markup'     => $price_markup,
                    ':sn'               => $stock_id
                ];
            } else {

                $sql = 'UPDATE stock_table SET 
                    product_name=:product_name, 
                    category=:category, 
                    coverage=:coverage, 
                    insurance_type=:insurance_type, 
                    generic_name=:generic_name,  
                    presentation=:presentation, 
                    package_type=:package_type, 
                    stock_total_unit=:stock_total_unit, 
                    reorder_level=:reorder_level, 
                    expire_date=:expire_date, 
                    status=:status, 
                    entry_mode=:entry_mode,
                    price_markup=:price_markup 
                    WHERE sn=:sn';

                $params = [
                    ':product_name'     => ucwords($stockname),
                    ':category'         => $_POST['category'],
                    ':coverage'         => $coverage,
                    ':insurance_type'   => $insurance_type,
                    ':generic_name'     => $_POST['GenericName'],
                    ':presentation'     => $_POST['presentation'],
                    ':package_type'     => $_POST['packagetype'],
                    ':stock_total_unit' => $units,
                    ':reorder_level'    => $_POST['reorder'],
                    ':expire_date'      => $expire_date,
                    ':status'           => $active,
                    ':entry_mode'       => $_POST['stock_request'],
                    ':price_markup'     => $price_markup,
                    ':sn'               => $stock_id
                ];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            echo "Update failed: " . $e->getMessage();
        }
    }



    /// WE SOME TABLE TO CONFIRM 
    $sql = "UPDATE special_package SET 
                service_title = :service_title 
            WHERE service_id = :service_id 
            AND service_type = 'pharmacy'";

    $stmt = $db->prepare($sql);

    $params = [
        ':service_title' => ucwords($stockname),
        ':service_id'    => $stock_id
    ];

    $stmt->execute($params);
    // LOG ACTIVITY
    try {
        $desc = $stockname . ' Prices: ' . $hosp_price . ' / ' . $cash_price .
            ' qty ' . $qty . ' price_markup: ' . $price_markup;

        $stmt = $db->prepare("INSERT INTO patient_staff_logs (descriptions,staff_name,action,date_and_time) 
                              VALUES (:descriptions,:staff_name,:action,:date_and_time)");

        $stmt->execute([
            ':descriptions' => $desc,
            ':staff_name'   => $_SESSION['fullname'],
            ':action'       => "Stock Add/Update",
            ':date_and_time' => date("Y-m-d H:i:s")
        ]);
    } catch (PDOException $e) {
        echo "Log insert failed: " . $e->getMessage();
    }
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

    return array('hosp_price' => $hosp_price, 'cash_price' => $cash_price);
}


if (isset($_POST["add_cat"])) {
    if (isset($_POST["sn"])) {
        $stock_table = $_POST['stock_table'];

        $stmt = $db->prepare('UPDATE stock_cat_table SET name = :name WHERE sn = :sn');
        $stmt->bindParam(':name', ucwords($_POST['Category']));
        $stmt->bindParam(':sn', $_POST['sn']);
        $stmt->execute();

        $stmt = $db->prepare('UPDATE stock_table SET category = :category WHERE category = :old_category AND navigation = :navigation');
        $stmt->bindParam(':category', ucwords($_POST['Category']));
        $stmt->bindParam(':old_category', $_POST['old_category']);
        $stmt->bindParam(':navigation', $navigation);
        $stmt->execute();
        $sv = 1;
    } else {
        $stmt = $db->prepare('SELECT * FROM stock_cat_table WHERE name = :name AND navigation = :navigation');
        $stmt->bindParam(':name', $_POST['Category']);
        $stmt->bindParam(':navigation', $navigation);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = $db->prepare('INSERT INTO stock_cat_table (name, navigation) VALUES (:name, :navigation)');
            $stmt->bindParam(':name', ucwords($_POST['Category']));
            $stmt->bindParam(':navigation', $navigation);
            $stmt->execute();
            $sv = 1;
        }
    }
}

if (isset($_POST["add_company"])) {

    if (isset($_POST["sn"])) {   ////    

        $stmt = $db->prepare('UPDATE stock_company SET name = :name, address = :address, phone = :phone, email = :email, bank_name = :bank_name, account_no = :account_no, account_name = :account_name, payment_method = :payment_method WHERE sn = :sn');
        $stmt->bindParam(':name', ucwords($_POST['company']));
        $stmt->bindParam(':address', $_POST['address']);
        $stmt->bindParam(':phone', $_POST['phone']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':bank_name', $_POST['bankname']);
        $stmt->bindParam(':account_no', $_POST['acct_no']);
        $stmt->bindParam(':account_name', $_POST['acct_name']);
        $stmt->bindParam(':payment_method', $_POST['payment_method']);
        $stmt->bindParam(':sn', $_POST['sn']);
        $stmt->execute();
        $sv = 1;
    } else {

        $stmt = $db->prepare('SELECT * FROM stock_company WHERE name = :name');
        $stmt->bindParam(':name', $_POST['company']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = $db->prepare('INSERT INTO stock_company (name, address, phone, email, bank_name, account_no, account_name, payment_method) VALUES (:name, :address, :phone, :email, :bank_name, :account_no, :account_name, :payment_method)');
            $stmt->bindParam(':name', ucwords($_POST['company']));
            $stmt->bindParam(':address', $_POST['address']);
            $stmt->bindParam(':phone', $_POST['phone']);
            $stmt->bindParam(':email', $_POST['email']);
            $stmt->bindParam(':bank_name', $_POST['bankname']);
            $stmt->bindParam(':account_no', $_POST['acct_no']);
            $stmt->bindParam(':account_name', $_POST['acct_name']);
            $stmt->bindParam(':payment_method', $_POST['payment_method']);
            $stmt->execute();

            $sv = 1;
        }
    }
}

if (isset($_GET["dl"])) {
    $delete = "delete";
    $stock_sn = $_GET["dl"];
    $stmt = $db->prepare('SELECT * FROM stock_table_inven WHERE stock_sn = :stock_sn');
    $stmt->bindParam(':stock_sn', $stock_sn);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $stmt_del = $db->prepare('DELETE FROM stock_table WHERE sn = :sn');
        $stmt_del->bindParam(':sn', $_POST["dl"]);
        $stmt_del->execute();
    } else {

        $stmt_comb = $db->prepare('SELECT product_name FROM stock_table WHERE sn = :stock_sn');
        $stmt_comb->bindParam(':stock_sn', $stock_sn);
        $roww3 = $stmt_comb->fetch(PDO::FETCH_ASSOC);
        $product_name = $roww3['product_name'] . '(deleted)';

        $stmt = $db->prepare('UPDATE stock_table SET status = :status,product_name=:product_name  WHERE sn = :sn');
        $stmt->bindParam(':status', $delete);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':sn', $_GET["dl"]);
        $stmt->execute();
    }
}

if (isset($_GET["ac"])) {
    $active = "active";
    $stmt = $db->prepare('UPDATE stock_table SET status = :status WHERE sn = :sn');
    $stmt->bindParam(':status', $active);
    $stmt->bindParam(':sn', $_GET["ac"]);
    $stmt->execute();
}

if (isset($_POST["approve_order"])) {

    $total_unit = $_POST["stock_total_unit"];
    $apr_qty = $_POST["apr_qty"];
    $order_no = $_POST["order_no"];
    $label = $_POST["label"];
    $order_dept = $_POST["order_dept"];
    $stock_sn = $_POST["stock_sn"];
    $Collectedby = $_POST["Collectedby"];
    $Collectedby = str_replace("'", "", trim($Collectedby));
    $C_qty = $_POST["C_qty"];
    $combo = $_POST["combo"];
    $stock_name = $_POST["stock_name"];
    $get_post = $_POST["get_post"];
    $stock_direction = $_POST["stock_direction"];
    $batch_no = $order_no . '/' . $label;
    $all_product = '';
    $error_m = 0;

    $input_array = [];

    $stmt = $db->prepare("SELECT * FROM stock_table_request WHERE sn = :sn");
    $stmt->execute([':sn' => $order_no]);
    if ($stmt->rowCount() > 0) {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        $request_sn = $inv_id;
        $order_dept = $roww['order_dept'];
        $order_date = $roww['order_date'];
        $order_qty = $roww['order_qty'];
        $order_no = $roww['sn'];
        $stock_table = $roww['stock_table'];
        $combo = $roww['combo'];


        if ($combo == '1') {
            $stock_name = $roww['stock_name'];
            $stmt_comb = $db->query("SELECT c.*, s.sn as stock_sn FROM stock_combo c inner join stock_table s on s.sn=c.items WHERE combo_name='$stock_name'");
            if ($stmt_comb->rowCount() > 0) {
                while ($roww3 = $stmt_comb->fetch(PDO::FETCH_ASSOC)) {
                    $stock_sn = $roww3['stock_sn'];
                    $apr_qty = $roww3['qty'] * $apr_qty;              /// request_sn							
                    array_push($input_array, array("stock_sn" => "$stock_sn", "apr_qty" => "$apr_qty", "request_sn" => "$request_sn"));
                }
            } else {
                echo 'Error Found';
            }
        } else {
            $stock_sn = $roww['stock_sn'];
            array_push($input_array, array("stock_sn" => "$stock_sn", "apr_qty" => "$apr_qty", "request_sn" => "$request_sn"));
        }
    }


    foreach ($input_array as $elements) {
        $stock_sn = $elements['stock_sn'];
        $found_key = array_search($stock_sn, array_column($input_array, 'stock_sn'));
        // grap the return arr
        $the_arr = $input_array[$found_key];
        $stock_sn = $the_arr['stock_sn'];
        $apr_qty = $the_arr['apr_qty'];
        $request_sn = $the_arr['request_sn'];
        //// --------------------------------------------

        $stmtxc = $db->query("SELECT stock_total_unit,entry_mode,qty,product_name FROM stock_table WHERE sn='$stock_sn'");
        if ($stmtxc->rowCount() > 0) {
            while ($rrwx = $stmtxc->fetch(PDO::FETCH_ASSOC)) {
                $stock_total_unit = $rrwx['stock_total_unit'];
                $C_qty = $rrwx['qty'];
                $product_name = $rrwx['product_name'];
            }
        }



        ////============================== DEDUCTION START HERE =======================================================	

        if ($apr_qty > 0 and $Collectedby != '' and $C_qty >= $apr_qty) {

            //// REMOVE FROM PHARM/MAIN STORE
            $add_or_remove = 'remove';
            $reverse = '';
            $procure_sn = '';
            $approval_dept = $_SESSION['dept_id']; /// get dept id for store room or pharmacy
            $stock_post = 'RQ';
            $query_remove = save_invent_bal($db, $total_unit, $apr_qty, $stock_sn, $Collectedby, $add_or_remove, $batch_no, $C_qty, $approval_dept, $reverse, $stock_post, $procure_sn);

            if ($query_remove == 'success') {
                ///// ADD APPROVE QTY TO TABLE   /////
                $add_or_remove = 'add';
                $query_add = save_invent_bal($db, $total_unit, $apr_qty, $stock_sn, $Collectedby, $add_or_remove, $batch_no, $C_qty, $order_dept, $reverse, $stock_post, $procure_sn);
                if ($query_add == 'success') {
                    //// check if it was successful ////

                    $addUpdate = $db->prepare("UPDATE stock_table_request SET status = :status, approve_by = :approve_by, approve_date = :approve_date, approve_qty = :approve_qty, bal = :bal, collect_by = :collect_by, collect_date = :collect_date, batch_no = :batch_no WHERE stock_sn = :stock_sn");
                    $addUpdate->execute([':status' => $status, ':approve_by' => $_SESSION['fullname'], ':approve_date' => $setdate, ':approve_qty' => $apr_qty, ':bal' => $apr_qty, ':collect_by' => $Collectedby, ':collect_date' => $setdate, ':batch_no' => $batch_no, ':stock_sn' => $stock_sn]);
                }
            }
        } else {
            $error_m = 1;
            $all_product = $all_product . $product_name . ' : ';
        }
    }
    if ($error_m == 1) {
        header("location:index.php?stock=$get_post&sv_1=$all_product&rrq");
    } else {
        header("location:index.php?stock=$get_post&sv&rrq");
    }
}



function save_invent_bal($db, $total_unit, $apr_qty, $stock_sn, $Collectedby, $add_or_remove, $batch_no, $C_qty, $order_dept, $reverse, $stock_post, $procure_sn)
{
    try {
        //TODO:merge with mr Habu

        // Error Checking
        if (empty($total_unit) || $total_unit == '0') {
            $total_unit = 1;
        }

        if ($add_or_remove == 'remove') {
            $inven_desc = "$stock_post/Ded.- APR/RCV " . $_SESSION["fullname"] . "/$Collectedby";
        } else {
            $inven_desc = "$stock_post/Add- APR/RCV " . $_SESSION["fullname"] . "/$Collectedby";
        }

        if ($reverse == '1') {
            $inven_desc = "REVERSED: " . $_SESSION["fullname"];
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


        // Get the last stock balance
        $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :order_dept ORDER BY sn DESC LIMIT 1");
        $stmt_chk->execute([':stock_sn' => $stock_sn, ':order_dept' => $order_dept]);
        $rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
        $old_qty = ($rowwc['bal']) ? $rowwc['bal'] : ($stock_post == 'RQ' && $add_or_remove == 'add' ? 0 : ($stock_post == 'RV' && $add_or_remove == 'remove' ? 0 : $C_qty));

        // Compute new quantity
        if ($add_or_remove == 'add' && $apr_qty > 0) {
            $c_qty = $old_qty + $apr_qty;
            $qtyIN = $apr_qty;
            $qtyOUT = 0;
        } elseif ($add_or_remove == 'remove' && $apr_qty > 0 && $old_qty >= $apr_qty) {
            $c_qty = $old_qty - $apr_qty;
            $qtyIN = 0;
            $qtyOUT = $apr_qty;
        } else {
            header("location:index.php?stock=rpt&error=$stock_sn");
            exit;
        }

        $captured_date = date("Y-m-d");
        $enter_by = $_SESSION['fullname'];
        $cust_patient_type = 'IN';
        $inven_desc = str_replace("'", "", trim($inven_desc));

        $db->beginTransaction();

        // Check for duplicate entry
        $stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND inven_desc = :inven_desc AND qtyIN = :qtyIN AND qtyOUT = :qtyOUT AND enter_by = :enter_by AND captured_date = :captured_date AND batch = :batch_no");
        $stmt_chk->execute([
            ':stock_sn' => $stock_sn,
            ':inven_desc' => $inven_desc,
            ':qtyIN' => $qtyIN,
            ':qtyOUT' => $qtyOUT,
            ':enter_by' => $enter_by,
            ':captured_date' => $captured_date,
            ':batch_no' => $batch_no
        ]);

        if ($stmt_chk->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO stock_table_inven (stock_sn, inven_desc, batch, qtyIN, qtyOUT, bal,buy,sale, cust_patient_id, cust_patient_type, enter_by, captured_date, procure_id) 
            VALUES (:stock_sn, :inven_desc, :batch_no, :qtyIN, :qtyOUT, :c_qty, :buy,:sale,:order_dept, :cust_patient_type, :enter_by, :captured_date, :procure_sn)");
            $stmt->execute([
                ':stock_sn' => $stock_sn,
                ':inven_desc' => $inven_desc,
                ':batch_no' => $batch_no,
                ':qtyIN' => $qtyIN,
                ':qtyOUT' => $qtyOUT,
                ':c_qty' => $c_qty,
                ':buy' => $buy,
                ':sale' => $sale,
                ':order_dept' => $order_dept,
                ':cust_patient_type' => $cust_patient_type,
                ':enter_by' => $enter_by,
                ':captured_date' => $captured_date,
                ':procure_sn' => $procure_sn
            ]);

            if ($stmt->rowCount() > 0) {
                if (!($stock_post == 'RQ' && $add_or_remove == 'add') && !($stock_post == 'RV_' && $add_or_remove == 'remove')) {
                    $stmt = $db->prepare("UPDATE stock_table SET status = 'active', date_last_update = :captured_date WHERE sn = :stock_sn");
                    $stmt->execute([
                        ':captured_date' => $captured_date,
                        ':stock_sn' => $stock_sn
                    ]);
                }
                $db->commit();
                return 'success';
            } else {
                throw new Exception("Insert operation failed.");
            }
        } else {
            throw new Exception("Duplicate stock entry detected.");
        }
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Transaction failed: " . $e->getMessage() . " on line " . $e->getLine());
        error_log("Trace: " . print_r($e->getTrace(), true));
        die("Error: " . $e->getMessage());
    }
}

if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $error_status = 2; // success
            $error_msg = 'Record deleted successfully.';
            break;
        case 'not_found':
            $error_status = 1; // error
            $error_msg = 'No matching record found.';
            break;
        case 'error':
            $error_status = 1; // error
            $error_msg = 'An error occurred while processing your request.';
            break;
    }
} elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_request') {
    $error_status = 1; // error
    $error_msg = 'Invalid request.';
}
