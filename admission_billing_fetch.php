<?php
include('Connections/Conn.php');
session_start();

if (!isset($_POST['admission_sn'])) exit('Invalid request');

$adm_sn = intval($_POST['admission_sn']);
$serv_group_filter = trim($_POST['serv_group']);

$stmt = $db->prepare("SELECT hospital_no, date_admit,
    IF(date_discharge IS NULL OR date_discharge='' OR date_discharge='0000-00-00 00:00:00', NOW(), date_discharge) AS date_discharge
    FROM admission WHERE sn=? LIMIT 1");
$stmt->execute(array($adm_sn));
$adm = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adm) exit("Admission not found.");

$hospital_no = $adm['hospital_no'];
$date_start  = date('Y-m-d', strtotime($adm['date_admit']));
$date_end    = date('Y-m-d', strtotime($adm['date_discharge']));

echo "<h4>Billing Details for <b>$hospital_no</b> ($date_start → $date_end)</h4>";

$total_grand_price = 0;
$total_grand_discount = 0;
$total_grand_pay = 0;

$current = $date_start;
while (strtotime($current) <= strtotime($date_end)) {

    echo "<div class='day-header'>DAY: " . date('d,M,Y', strtotime($current)) . "</div>";

    // Select service groups or categories for this day
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
        echo "<em>No billing for this date.</em><br>";
    }

    foreach ($groups as $g) {

        if ($serv_group_filter == 'Accommodation_Nursing_care') {   /// Nursing Services
            $cat_type = "Accommodation/Nursing Care";
            echo "<div class='category'>CATEGORY: $cat_type</div>";
            $q = $db->prepare("SELECT * FROM patient_ap_services 
                               WHERE hospital_no=? AND DATE(date_entry)=? 
                               AND serv_group='Nursing Services' 
                               AND cat_type != 'Bed Space/Accommodation' 
                               ORDER BY item_services");
            $q->execute(array($hospital_no, $current));
        } elseif ($serv_group_filter == 'Accommodation') {
            $cat_type = "Bed Space/Accommodation";
            echo "<div class='category'>CATEGORY: $cat_type</div>";
            $q = $db->prepare("SELECT * FROM patient_ap_services 
                               WHERE hospital_no=? AND DATE(date_entry)=? AND cat_type=? ORDER BY item_services");
            $q->execute(array($hospital_no, $current, $cat_type));
        } else {
            $serv_group = isset($g['serv_group']) ? $g['serv_group'] : $g['cat_type'];
            echo "<div class='category'>CATEGORY: $serv_group</div>";
            $q = $db->prepare("SELECT * FROM patient_ap_services 
                               WHERE hospital_no=? AND DATE(date_entry)=? AND serv_group=? ORDER BY item_services");
            $q->execute(array($hospital_no, $current, $serv_group));
        }

        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) continue;

        echo "<table>
              <tr>
                <th>Date Entry</th><th>Service</th><th>Hosp Price</th><th>Claim Amt</th>
                <th>Interest</th><th>Qty</th><th>Status</th><th>Invoice Date</th>
                <th>Invoice By</th><th>Prepared By</th><th>Dispensed By</th>
                <th>Transact Date</th><th>Actual Pay</th><th>.</th>
                <th>PayStatus</th><th>CR</th><th>Discount</th><th>Claim Valid By</th>
              </tr>";

        $subtotal_price = 0;
        $subtotal_discount = 0;  ////15000
        $subtotal_pay = 0;

        foreach ($rows as $r) {
            $price = floatval($r['hosp_price']) * floatval($r['qty']);
            $discount = floatval($r['discount']);
            $pay = ($r['paystatus'] == 1) ? floatval($r['pay']) : 0;

            $subtotal_price += $price;
            $subtotal_discount += $discount;
            $subtotal_pay += $pay;

            // Translations
            // Determine drug status display
            if ($r['paystatus'] == 0 && $r['med_dosage_unit'] == 1) {
                $drug_status = "<b style='color:red'>Inv. Canceled</b>";
            } else {
                $drug_status = ($r['drug_status'] == 1) ? "<b style='color:blue'>Delivered</b>" : "<b style='color:purple'>Pending</p>";
            }

            $paystatus = ($r['paystatus'] == 1) ? "<b style='color:blue'>Paid</b>" : (($r['paystatus'] == 3) ? "Cancelled" : "<b style='color:red'>Unpaid</b>");
            $cr_status = ($r['cr'] == 1) ? "<b style='color:brown;'>Credit</b>" : (($r['cr'] == 2) ? "Billed to Acct" : "");

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
    <td></td>
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

        echo "</table>";

        $total_grand_price += $subtotal_price;
        $total_grand_discount += $subtotal_discount;
        $total_grand_pay += $subtotal_pay;
    }

    $current = date('Y-m-d', strtotime($current . ' +1 day'));
}

// Grand Totals
echo "<h4 style='margin-top:15px;color:#900;'>GRAND TOTALS</h4>";
echo "<table style='width:60%;border:1px solid #ccc;'>
        <tr><th>Description</th><th align='right'>Amount (₦)</th></tr>
        <tr><td>Total Hospital Price</td><td align='right'><b>" . number_format($total_grand_price, 2) . "</b></td></tr>
        <tr><td>Total Actual Paid</td><td align='right'><b>" . number_format($total_grand_pay, 2) . "</b></td></tr>
        <tr><td>Total Discount</td><td align='right'><b>" . number_format($total_grand_discount, 2) . "</b></td></tr>
      </table>";
