<?php
session_start();
include("../Connections/Conn.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $setdate = date("Y-m-d");
  $three_months_from_now = date("Y-m-d", strtotime("+3 months"));
  $six_months_from_now = date("Y-m-d", strtotime("+6 months"));

  // 1. Patients Seen Today
  $seen_today_count = $db->query("
        SELECT COUNT(DISTINCT hospital_no) AS cnt 
        FROM patient_ap_services 
        WHERE serv_group = 'Pharmacy' 
          AND paystatus = '1' 
          AND drug_status = '1' 
          AND DATE(transact_date) = '$setdate'
    ")->fetch(PDO::FETCH_ASSOC)['cnt'];

  // 2. Drugs Dispensed Today (same condition, just total rows)
  $dsp_today_count = $db->query("
        SELECT COUNT(*) AS cnt 
        FROM patient_ap_services 
        WHERE serv_group = 'Pharmacy' 
          AND paystatus = '1' 
          AND drug_status = '1' 
          AND DATE(transact_date) = '$setdate'
    ")->fetch(PDO::FETCH_ASSOC)['cnt'];

  // 3. Expired Stocks (Check both tables)
  $exp_today_count = $db->query("
        SELECT COUNT(*) AS cnt 
        FROM stock_table 
        WHERE expire_date <= '$setdate' 
          AND status = 'yes' 
          AND stock_table = 'Pharmacy'
    ")->fetch(PDO::FETCH_ASSOC)['cnt'];

  if ($exp_today_count == 0) {
    $exp_today_count = $db->query("
            SELECT COUNT(*) AS cnt 
            FROM stock_table 
            WHERE expire_date <= '$setdate' 
              AND status = 'active' 
              AND stock_table = 'Pharmacy'
        ")->fetch(PDO::FETCH_ASSOC)['cnt'];
  }

  // 4. Reorder Level
  $odr_today_count = $db->query("
        SELECT COUNT(*) AS cnt 
        FROM stock_table 
        WHERE qty = reorder_level 
          AND stock_table = 'Pharmacy'
    ")->fetch(PDO::FETCH_ASSOC)['cnt'];

  // Get the current year
  $current_year = date('Y');

  $rowx = $db->query("
    SELECT 
        IFNULL(SUM(claim_amt), 0) AS clm, 
        IFNULL(SUM(pay), 0) AS amt 
    FROM patient_ap_services 
    WHERE serv_group = 'Pharmacy' 
      AND paystatus = '0' 
      AND cr = '1' 
      AND drug_status = '1'
      AND YEAR(date_entry) = $current_year
")->fetch(PDO::FETCH_ASSOC);

  $cr_today_count = 'N' . number_format($rowx['amt'], 2) . ' / N' . number_format($rowx['clm'], 2);

  // 6. Expiring in 3 Months
  $exp_three_months_count = $db->query("
        SELECT COUNT(*) AS cnt 
        FROM stock_table 
        WHERE DATE(expire_date) BETWEEN '$setdate' AND '$three_months_from_now' 
          AND expire_date != '0000-00-00' 
          AND status = 'active' 
          AND stock_table = 'Pharmacy'
    ")->fetch(PDO::FETCH_ASSOC)['cnt'];

  // 7. Expiring in 3–6 Months
  $exp_six_months_count = $db->query("
        SELECT COUNT(*) AS cnt 
        FROM stock_table 
        WHERE DATE(expire_date) BETWEEN '$three_months_from_now' AND '$six_months_from_now' 
          AND expire_date != '0000-00-00' 
          AND status = 'active' 
          AND stock_table = 'Pharmacy'
    ")->fetch(PDO::FETCH_ASSOC)['cnt'];

  // ✅ Return as JSON
  echo json_encode([
    'seen_today' => (int)$seen_today_count,
    'dsp_today' => (int)$dsp_today_count,
    'exp_today' => (int)$exp_today_count,
    'odr_today' => (int)$odr_today_count,
    'exp_three_months_count' => (int)$exp_three_months_count,
    'exp_six_months_count' => (int)$exp_six_months_count,
    'cr_today' => $cr_today_count
  ]);
  exit;
}
