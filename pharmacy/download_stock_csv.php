<?php
session_start();
include("../Connections/Conn.php");

// ----------------------
// Setup
// ----------------------
$setdate  = date('Y-m-d');
$dept_id  = isset($_SESSION['dept_id']) ? $_SESSION['dept_id'] : 0;
$ddset    = isset($_POST['ddset']) ? intval($_POST['ddset']) : 0;
$start    = isset($_POST['start']) ? $_POST['start'] : null;
$to       = isset($_POST['to']) ? $_POST['to'] : null;

// ----------------------
// Base query: latest row per stock_sn + totals
// ----------------------
$drugQuery = "
    SELECT 
        st.product_name AS item_services,
        st.stock_total_unit,
        st.buying_cost,
        st.hosp_price,
        st.price_markup,
        st.sn,
        inv.inven_desc,
        inv.buy,
        inv.sale,
        inv.qtyIN,
        inv.qtyOUT,
        inv.bal,
        inv.insertion_date_time,
        inv.enter_by,
        totals.total_in,
        totals.total_out
    FROM stock_table st
    INNER JOIN stock_table_inven inv 
        ON st.sn = inv.stock_sn
    INNER JOIN (
        SELECT stock_sn, MAX(sn) AS last_sn
        FROM stock_table_inven
        GROUP BY stock_sn
    ) latest 
        ON inv.stock_sn = latest.stock_sn 
       AND inv.sn = latest.last_sn
    INNER JOIN (
        SELECT stock_sn, 
               SUM(qtyIN) AS total_in, 
               SUM(qtyOUT) AS total_out
        FROM stock_table_inven
        WHERE cust_patient_id = :dept_id_sub
";

if ($ddset == 1 && !empty($start) && !empty($to)) {
    $drugQuery .= " AND DATE(captured_date) BETWEEN :start_sub AND :to_sub";
} else {
    $drugQuery .= " AND DATE(captured_date) = :setdate_sub";
}

$drugQuery .= " GROUP BY stock_sn
    ) totals 
    ON inv.stock_sn = totals.stock_sn
    WHERE inv.cust_patient_id = :dept_id_main";

if ($ddset == 1 && !empty($start) && !empty($to)) {
    $drugQuery .= " AND DATE(inv.captured_date) BETWEEN :start_main AND :to_main";
} else {
    $drugQuery .= " AND DATE(inv.captured_date) = :setdate_main";
}

$drugQuery .= " ORDER BY st.product_name";

// ----------------------
// Prepare + Bind
// ----------------------
$drugStmt = $db->prepare($drugQuery);

$drugStmt->bindValue(':dept_id_sub', $dept_id, PDO::PARAM_INT);
$drugStmt->bindValue(':dept_id_main', $dept_id, PDO::PARAM_INT);

if ($ddset == 1 && !empty($start) && !empty($to)) {
    $drugStmt->bindValue(':start_sub', $start, PDO::PARAM_STR);
    $drugStmt->bindValue(':to_sub', $to, PDO::PARAM_STR);

    $drugStmt->bindValue(':start_main', $start, PDO::PARAM_STR);
    $drugStmt->bindValue(':to_main', $to, PDO::PARAM_STR);
} else {
    $drugStmt->bindValue(':setdate_sub', $setdate, PDO::PARAM_STR);
    $drugStmt->bindValue(':setdate_main', $setdate, PDO::PARAM_STR);
}

$drugStmt->execute();

// ----------------------
// Helper functions
// ----------------------
function percentage_markup_cal($purchase_cost, $units, $percentage_markup)
{
    $amt = $units > 0 ? ($purchase_cost / $units) : 0;
    $markup_amount = ($amt * $percentage_markup) / 100;
    $selling_price = $amt + $markup_amount;
    return array('hosp_price' => $selling_price > 0 ? $selling_price : 0);
}

function formatQuantity($qty, $unit_size)
{
    $packs = floor($qty / $unit_size);
    $pieces = $qty % $unit_size;
    return $packs . " pack(s)" . ($pieces > 0 ? " + $pieces pcs" : "");
}

// ----------------------
// CSV Output
// ----------------------
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="stock_report.csv"');

$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, [
    'SN',
    'Item Services',
    'Description',
    'Buy',
    'Sell',
    'IN',
    'OUT',
    'Balance (Pcs)',
    'Balance (Pack)',
    'Total Buying Cost Accrued',
    'Total Selling Cost Accrued',
    'Date',
    'Dispensed By',
    'Total IN',
    'Total OUT'
]);

$sn = 1;
$grand_total_buying = 0;
$grand_total_selling = 0;

// Rows
while ($row = $drugStmt->fetch(PDO::FETCH_ASSOC)) {
    // ----------------------
    // BUYING COST
    // ----------------------
    $buy_price = (!empty($row['buy']) && $row['buy'] > 0)
        ? $row['buy']
        : ($row['stock_total_unit'] > 0 ? $row['buying_cost'] / $row['stock_total_unit'] : 0);

    $buy_source = (!empty($row['buy']) && $row['buy'] > 0) ? "IBP" : "SBC";
    $total_buying = $row['bal'] * $buy_price;
    $grand_total_buying += $total_buying;

    // ----------------------
    // SELLING COST
    // ----------------------
    $sell_price = 0;
    $sell_source = "";

    if (!empty($row['sale']) && $row['sale'] > 0) {
        $sell_price = $row['sale'];
        $sell_source = "ISP";
    } else {
        $priceStmt = $db->prepare("
            SELECT hosp_price 
            FROM patient_ap_services 
            WHERE serv_group='Pharmacy' 
              AND drug_sn = :sn
            ORDER BY sn DESC LIMIT 1
        ");
        $priceStmt->bindValue(':sn', $row['sn'], PDO::PARAM_INT);
        $priceStmt->execute();
        $last_price = $priceStmt->fetchColumn();

        if (!empty($last_price) && $last_price > 0) {
            $sell_price = $last_price;
            $sell_source = "LPSP";
        } else {
            if (!empty($row['price_markup']) && $row['price_markup'] > 0) {
                $calc = percentage_markup_cal($row['buying_cost'], $row['stock_total_unit'], $row['price_markup']);
                $sell_price = $calc['hosp_price'];
                $sell_source = "CM";
            } else {
                $sell_price = $row['hosp_price'];
                $sell_source = "DHP";
            }
        }
    }

    $total_selling = $row['bal'] * $sell_price;
    $grand_total_selling += $total_selling;

    // Format balance pack
    $bal_pack = ($row['bal'] > 1 && $row['stock_total_unit'] > 1)
        ? formatQuantity($row['bal'], $row['stock_total_unit'])
        : 0;

    // Format totals IN/OUT
    $total_in  = ($row['total_in'] > 0)
        ? (($row['total_in'] > 1 && $row['stock_total_unit'] > 1)
            ? formatQuantity($row['total_in'], $row['stock_total_unit'])
            : $row['total_in'] . ' (pcs)')
        : 0;

    $total_out = ($row['total_out'] > 0)
        ? (($row['total_out'] > 1 && $row['stock_total_unit'] > 1)
            ? formatQuantity($row['total_out'], $row['stock_total_unit'])
            : $row['total_out'] . ' (pcs)')
        : 0;

    // Write row
    fputcsv($output, [
        $sn++,
        $row['item_services'],
        $row['inven_desc'],
        $row['buy'] . " ($buy_source)",
        $row['sale'] . " ($sell_source)",
        $row['qtyIN'],
        $row['qtyOUT'],
        $row['bal'],
        $bal_pack,
        number_format($total_buying, 2),
        number_format($total_selling, 2),
        date('d,M Y', strtotime($row['insertion_date_time'])),
        $row['enter_by'],
        $total_in,
        $total_out
    ]);
}

// ----------------------
// GRAND TOTAL
// ----------------------
fputcsv($output, []);
fputcsv($output, [
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    'GRAND TOTAL',
    '',
    number_format($grand_total_buying, 2),
    number_format($grand_total_selling, 2),
    '',
    '',
    '',
    ''
]);

// ----------------------
// LEGEND
// ----------------------
fputcsv($output, []);
fputcsv($output, ['LEGEND:']);
fputcsv($output, ['IBP = Inventory Buy Price']);
fputcsv($output, ['SBC = Stock Buying Cost']);
fputcsv($output, ['ISP = Inventory Sale Price']);
fputcsv($output, ['DHP = Default Hospital Price']);
fputcsv($output, ['CM = Calculated Markup']);
fputcsv($output, ['LPSP = Last Patient Service Price']);

fclose($output);
exit;
