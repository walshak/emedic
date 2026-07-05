<?php
include('Connections/Conn.php');
session_start();

if (!isset($_GET['admission_sn'])) exit('Invalid request');

$adm_sn = intval($_GET['admission_sn']);
$serv_group_filter = isset($_GET['serv_group']) ? trim($_GET['serv_group']) : '';

$stmt = $db->prepare("SELECT hospital_no, date_admit,
    IF(date_discharge IS NULL OR date_discharge='' OR date_discharge='0000-00-00 00:00:00', NOW(), date_discharge) AS date_discharge
    FROM admission WHERE sn=? LIMIT 1");
$stmt->execute(array($adm_sn));
$adm = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adm) exit("Admission not found.");

$hospital_no = $adm['hospital_no'];
$date_start = date('Y-m-d', strtotime($adm['date_admit']));
$date_end   = date('Y-m-d', strtotime($adm['date_discharge']));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=billing_{$hospital_no}_{$date_start}_to_{$date_end}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1' cellspacing='0' cellpadding='4'>";
echo "<tr><th colspan='18' style='background:#ccc;'>Billing Summary for $hospital_no ($date_start → $date_end)</th></tr>";

$total_grand_price = 0;
$total_grand_discount = 0;
$total_grand_pay = 0;

$current = $date_start;
while (strtotime($current) <= strtotime($date_end)) {
    echo "<tr style='background:#eaeaea;'><td colspan='18'><b>DAY: " . date('d,M,Y', strtotime($current)) . "</b></td></tr>";

    // Define filter
    if ($serv_group_filter == 'Accommodation_Nursing_care') {
        $grp_stmt = $db->prepare("SELECT DISTINCT cat_type FROM patient_ap_services 
                                  WHERE hospital_no=? AND DATE(date_entry)=? AND cat_type!='Bed Space/Accommodation' AND serv_group='Nursing Services'");
        $grp_stmt->execute(array($hospital_no, $current));
    } elseif ($serv_group_filter == 'Accommodation') {
        $grp_stmt = $db->prepare("SELECT DISTINCT cat_type FROM patient_ap_services 
                                  WHERE hospital_no=? AND DATE(date_entry)=? AND cat_type='Bed Space/Accommodation'");
        $grp_stmt->execute(array($hospital_no, $current));
    } elseif ($serv_group_filter != '') {
        $grp_stmt = $db->prepare("SELECT DISTINCT serv_group FROM patient_ap_services 
                                  WHERE hospital_no=? AND DATE(date_entry)=? AND serv_group=?");
        $grp_stmt->execute(array($hospital_no, $current, $serv_group_filter));
    } else {
        $grp_stmt = $db->prepare("SELECT DISTINCT serv_group FROM patient_ap_services 
                                  WHERE hospital_no=? AND DATE(date_entry)=? ORDER BY serv_group");
        $grp_stmt->execute(array($hospital_no, $current));
    }

    $groups = $grp_stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$groups) {
        echo "<tr><td colspan='18'><em>No billing for this date.</em></td></tr>";
    }

    foreach ($groups as $g) {
        if ($serv_group_filter == 'Accommodation_Nursing_care') {
            $cat_type = "Accommodation/Nursing Care";
            echo "<tr style='background:#f4f4f4;'><td colspan='18'><b>CATEGORY: $cat_type</b></td></tr>";
            $q = $db->prepare("SELECT * FROM patient_ap_services 
                               WHERE hospital_no=? AND DATE(date_entry)=? 
                               AND serv_group='Nursing Services' 
                               AND cat_type != 'Bed Space/Accommodation' 
                               ORDER BY item_services");
            $q->execute(array($hospital_no, $current));
        } elseif ($serv_group_filter == 'Accommodation') {
            $cat_type = "Bed Space/Accommodation";
            echo "<tr style='background:#f4f4f4;'><td colspan='18'><b>CATEGORY: $cat_type</b></td></tr>";
            $q = $db->prepare("SELECT * FROM patient_ap_services 
                               WHERE hospital_no=? AND DATE(date_entry)=? AND cat_type=? ORDER BY item_services");
            $q->execute(array($hospital_no, $current, $cat_type));
        } else {
            $serv_group = isset($g['serv_group']) ? $g['serv_group'] : $g['cat_type'];
            echo "<tr style='background:#f4f4f4;'><td colspan='18'><b>CATEGORY: $serv_group</b></td></tr>";
            $q = $db->prepare("SELECT * FROM patient_ap_services 
                               WHERE hospital_no=? AND DATE(date_entry)=? AND serv_group=? ORDER BY item_services");
            $q->execute(array($hospital_no, $current, $serv_group));
        }

        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) continue;

        echo "<tr style='background:#d9edf7;font-weight:bold;'>
                <td>Date Entry</td><td>Service</td><td>Hosp Price</td><td>Claim Amt</td>
                <td>Interest</td><td>Qty</td><td>Status</td><td>Invoice Date</td>
                <td>Invoice By</td><td>Prepared By</td><td>Dispensed By</td>
                <td>Transact Date</td><td>Actual Pay</td><td>Pay Mode</td>
                <td>PayStatus</td><td>CR</td><td>Discount</td><td>Claim Valid By</td>
              </tr>";

        $subtotal_price = 0;
        $subtotal_discount = 0;
        $subtotal_pay = 0;

        foreach ($rows as $r) {
            $price = floatval($r['hosp_price']) * floatval($r['qty']);
            $discount = floatval($r['discount']);
            $pay = ($r['paystatus'] == 1) ? floatval($r['pay']) : 0;

            $subtotal_price += $price;
            $subtotal_discount += $discount;
            $subtotal_pay += $pay;

            if ($r['paystatus'] == 0 && $r['med_dosage_unit'] == 1) {
                $drug_status = "<b style='color:red'>Inv. Canceled</b>";
            } else {
                $drug_status = ($r['drug_status'] == 1) ? "Delivered" : "Pending";
            }

            $paystatus = ($r['paystatus'] == 1) ? "Paid" : (($r['paystatus'] == 3) ? "Cancelled" : "Unpaid");
            $cr_status = ($r['cr'] == 1) ? "Credit" : (($r['cr'] == 2) ? "Billed to Acct" : "");


            echo "<tr>
    <td>" . (!empty($r['date_entry']) && strtotime($r['date_entry']) && $r['date_entry'] != '0000-00-00'
                ? date('d,M,Y', strtotime($r['date_entry']))
                : '') . "</td>
    <td>{$r['item_services']}</td>
    <td align='right'>" . number_format($r['hosp_price'], 2) . "</td>
    <td align='right'>" . number_format($r['claim_amt'], 2) . "</td>
    <td align='right'>" . number_format($r['interest'], 2) . "</td>
    <td align='center'>{$r['qty']}</td>
    <td>{$drug_status}</td>
    <td>" . (!empty($r['invoice_date']) && strtotime($r['invoice_date']) && $r['invoice_date'] != '0000-00-00 00:00:00'
                ? date('d/m/Y h:i A', strtotime($r['invoice_date']))
                : '') . "</td>
    <td>{$r['invoice_by']}</td>
    <td>{$r['prepared_by']}</td>
    <td>{$r['dsp_by']}</td>
    <td>" . (!empty($r['transact_date']) && strtotime($r['transact_date']) && $r['transact_date'] != '0000-00-00 00:00:00'
                ? date('d/m/Y h:i A', strtotime($r['transact_date']))
                : '') . "</td>
    <td align='right'>" . number_format($pay, 2) . "</td>
    <td>{$r['pay_mode']}</td>
    <td>{$paystatus}</td>
    <td>{$cr_status}</td>
    <td align='right'>" . number_format($discount, 2) . "</td>
    <td>{$r['claim_valid_by']}</td>
</tr>";
        }

        echo "<tr style='background:#e8f7ff;font-weight:bold;'>
                <td colspan='2'>Subtotal</td>
                <td align='right'>" . number_format($subtotal_price, 2) . "</td>
                <td colspan='9'></td>
                <td align='right'>" . number_format($subtotal_pay, 2) . "</td>
                <td colspan='3'></td>
                <td align='right'>" . number_format($subtotal_discount, 2) . "</td>
                <td></td>
              </tr>";

        $total_grand_price += $subtotal_price;
        $total_grand_discount += $subtotal_discount;
        $total_grand_pay += $subtotal_pay;
    }

    $current = date('Y-m-d', strtotime($current . ' +1 day'));
}

// Grand Totals
echo "<tr style='background:#ccc;'><th colspan='18'>GRAND TOTALS</th></tr>";
echo "<tr><td colspan='2'>Total Hospital Price</td><td align='right'><b>" . number_format($total_grand_price, 2) . "</b></td><td colspan='15'></td></tr>";
echo "<tr><td colspan='2'>Total Actual Paid</td><td align='right'><b>" . number_format($total_grand_pay, 2) . "</b></td><td colspan='15'></td></tr>";
echo "<tr><td colspan='2'>Total Discount</td><td align='right'><b>" . number_format($total_grand_discount, 2) . "</b></td><td colspan='15'></td></tr>";
echo "</table>";
