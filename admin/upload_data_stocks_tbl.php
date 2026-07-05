<?php
session_start();
require_once('../Connections/Conn.php'); // $db must be PDO

/* ---------------- HELPER FUNCTIONS ---------------- */

function percentage_markup_cal($purchase_cost, $units, $percentage_markup)
{
    $purchase_cost = is_numeric($purchase_cost) ? $purchase_cost : 0;
    $units = is_numeric($units) ? $units : 0;
    $percentage_markup = is_numeric($percentage_markup) ? $percentage_markup : 0;

    if ($purchase_cost <= 0 || $units <= 0) {
        return array('hosp_price' => 0);
    }

    $unit_cost = $purchase_cost / $units;
    $markup = ($unit_cost * $percentage_markup) / 100;

    return array('hosp_price' => round($unit_cost + $markup, 2));
}

function fixNumber($val, &$warnings, $row, $field)
{
    $clean = preg_replace('/[^0-9.]/', '', $val);

    if ($clean === '' || !is_numeric($clean)) {
        if ($row && $field) {
            $warnings[] = "Row $row: $field invalid → set to 0";
        }
        return 0;
    }

    if ($clean != $val && $row && $field) {
        $warnings[] = "Row $row: $field auto-fixed ($val → $clean)";
    }

    return $clean;
}

function addError(&$errors, $row, $msg)
{
    $errors[] = "Row <b>$row</b>: $msg";
}

/**
 * Record opening / reset stock
 */
function recordOpeningStock($db, $stock_sn, $qty, $dept_id)
{
    if ($qty <= 0) return;

    // $dept_id  = isset($_SESSION['dept_id']) ? $_SESSION['dept_id'] : 0;
    $enter_by = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';

    if (!$dept_id || $enter_by == '') return;

    $captured_date = date('Y-m-d');
    $inven_desc = 'Opening Stock';
    $cust_patient_type = 'IN';

    $stmt = $db->prepare("
        SELECT sn FROM stock_table_inven
        WHERE stock_sn = :stock_sn
          AND cust_patient_id = :dept_id
          AND DATE(captured_date) = :captured_date
          AND inven_desc = :inven_desc
        LIMIT 1
    ");

    $stmt->execute(array(
        ':stock_sn' => $stock_sn,
        ':dept_id' => $dept_id,
        ':captured_date' => $captured_date,
        ':inven_desc' => $inven_desc
    ));

    if ($stmt->rowCount() > 0) return;

    $insert = $db->prepare("
        INSERT INTO stock_table_inven
        (stock_sn, inven_desc, qtyIN, qtyOUT, bal, buy, sale,
         cust_patient_id, cust_patient_type, enter_by, captured_date)
        VALUES
        (:stock_sn, :inven_desc, :qtyIN, 0, :bal, 0, 0,
         :dept_id, :cust_patient_type, :enter_by, :captured_date)
    ");

    $insert->execute(array(
        ':stock_sn' => $stock_sn,
        ':inven_desc' => $inven_desc,
        ':qtyIN' => $qty,
        ':bal' => $qty,
        ':dept_id' => $dept_id,
        ':cust_patient_type' => $cust_patient_type,
        ':enter_by' => $enter_by,
        ':captured_date' => $captured_date
    ));
}

/* ---------------- INIT ---------------- */

if (!isset($_POST['upload_type']) || empty($_FILES['the_upload']['tmp_name'])) {
    die("No file uploaded or invalid request");
}

$upload_type = $_POST['upload_type'];
$file = $_FILES['the_upload']['tmp_name'];

$errors = array();
$warnings = array();
$duplicates = array();
$success = 0;
$rowNum = 1;

/* ---------------- FULL STOCK UPLOAD ---------------- */

if ($upload_type == 'full') {

    $rollbackOnError = isset($_POST['rollback']);
    $reset_qty = isset($_POST['reset_qty']);

    $expected_headers = array(
        'sn',
        'product_name',
        'generic_name',
        'buying_cost',
        'category',
        'navigation',
        'stock_table',
        'nhis_price',
        'hosp_price',
        'cash_price',
        'stock_total_unit',
        'qty',
        'status',
        'price_markup'
    );

    if (($handle = fopen($file, "r")) === false) die("Unable to open CSV");

    $headers = fgetcsv($handle);
    if ($headers !== $expected_headers) die("Invalid CSV headers");

    $seen = array();
    $db->beginTransaction();

    while (($row = fgetcsv($handle)) !== false) {
        $rowNum++;

        $sn               = trim(isset($row[0]) ? $row[0] : '');
        $product_name     = trim(isset($row[1]) ? $row[1] : '');
        $buying_cost      = fixNumber(isset($row[3]) ? $row[3] : 0, $warnings, $rowNum, 'buying_cost');
        $category     = trim(isset($row[5]) ? $row[4] : '');
        $navigation     = trim(isset($row[5]) ? $row[5] : '');
        $stock_table     = trim(isset($row[5]) ? $row[6] : '');
        $nhis_price       = fixNumber(isset($row[7]) ? $row[7] : 0, $warnings, $rowNum, 'nhis_price');
        $hosp_price       = fixNumber(isset($row[8]) ? $row[8] : 0, $warnings, $rowNum, 'hosp_price');
        $cash_price       = fixNumber(isset($row[9]) ? $row[9] : 0, $warnings, $rowNum, 'cash_price');
        $stock_total_unit = fixNumber(isset($row[10]) ? $row[10] : 0, $warnings, $rowNum, 'stock_total_unit');
        $qty              = fixNumber(isset($row[11]) ? $row[11] : 0, $warnings, $rowNum, 'qty');
        $status           = strtolower(trim(isset($row[12]) ? $row[12] : 'active'));
        $price_markup     = fixNumber(isset($row[13]) ? $row[13] : 0, $warnings, $rowNum, 'price_markup');

        if ($product_name == '') {
            addError($errors, $rowNum, "product_name is required");
            if ($rollbackOnError) break;
            continue;
        }

        // Apply markup
        if ($price_markup > 0 && $buying_cost > 0 && $stock_total_unit > 0) {
            $calc = percentage_markup_cal($buying_cost, $stock_total_unit, $price_markup);
            $hosp_price = $cash_price = $calc['hosp_price'];
        }

        // CSV duplicate
        $key = strtolower($product_name);
        if (isset($seen[$key])) {
            $duplicates[] = "Row $rowNum: duplicate in CSV (row {$seen[$key]})";
            if ($rollbackOnError) break;
            continue;
        }
        $seen[$key] = $rowNum;

        // DB duplicate
        if ($sn == '') {
            $chk = $db->prepare("SELECT sn FROM stock_table WHERE product_name=?");
            $chk->execute(array($product_name));
            if ($chk->rowCount() > 0) {
                $duplicates[] = "Row $rowNum: duplicate in DB";
                if ($rollbackOnError) break;
                continue;
            }
        }

        // INSERT / UPDATE
        if ($sn != '') {
            $sql = "UPDATE stock_table
                    SET product_name=?, buying_cost=?,category=?,navigation=?,stock_table=?, nhis_price=?, hosp_price=?,
                        cash_price=?, stock_total_unit=?, qty=?, status=?, price_markup=?
                    WHERE sn=?";
            $params = array(
                $product_name,
                $buying_cost,
                $category,
                $navigation,
                $stock_table,
                $nhis_price,
                $hosp_price,
                $cash_price,
                $stock_total_unit,
                $qty,
                $status,
                $price_markup,
                $sn
            );
        } else {
            $sql = "INSERT INTO stock_table
                    (product_name,category,navigation,stock_table,buying_cost,nhis_price,hosp_price,
                     cash_price,stock_total_unit,qty,status,price_markup)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
            $params = array(
                $product_name,
                $category,
                $navigation,
                $stock_table,
                $buying_cost,
                $nhis_price,
                $hosp_price,
                $cash_price,
                $stock_total_unit,
                $qty,
                $status,
                $price_markup
            );
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $stock_sn = ($sn != '') ? (int)$sn : (int)$db->lastInsertId();

        if ($reset_qty) {
            recordOpeningStock($db, $stock_sn, $qty, $dept_id);
        }

        $success++;
    }

    if ($rollbackOnError && (!empty($errors) || !empty($duplicates))) {
        $db->rollBack();
        echo "<h3 style='color:red'>Transaction rolled back</h3>";
    } else {
        $db->commit();
        echo "<h3 style='color:green'>Upload completed</h3>";
    }

    fclose($handle);
}

/* ---------------- REPORT ---------------- */

echo "<p>Total rows processed: <b>$rowNum</b></p>";
echo "<p>Successful: <b style='color:green'>$success</b></p>";
echo "<p>Warnings: <b>" . count($warnings) . "</b></p>";
echo "<p>Duplicates: <b>" . count($duplicates) . "</b></p>";
echo "<p>Errors: <b>" . count($errors) . "</b></p>";

$allLists = array($warnings, $duplicates, $errors);
foreach ($allLists as $list) {
    if (!empty($list)) {
        echo "<ul>";
        foreach ($list as $msg) echo "<li>$msg</li>";
        echo "</ul>";
    }
}

echo "<a href='index.php?updown' class='btn btn-danger btn-xs'>[ Close ]</a>";
