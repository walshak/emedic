<?php
//include_once("Connections/Conn.php");
/**
 * Take a number in format 1000000 or -1000000
 * and a currency symbol and format it
 * in the format 1,000,000 or (1,000,000) for negative values.
 *
 * @param float|int|string $value
 * @param string           $currency_symbol
 *
 * @return string
 */
function format_accounting($value, $currency_symbol = '')
{
    // Convert the value to a numeric format
    $numeric_value = floatval($value);

    // Determine if the value is negative
    $is_negative = $numeric_value < 0;

    // Format the value with commas for thousands and decimals
    $formatted_value = number_format(abs($numeric_value), 2);

    // Add parentheses for negative values
    if ($is_negative) {
        $formatted_value = "($formatted_value)";
    }

    // Add the currency symbol
    $formatted_value .= ' ' . $currency_symbol;

    return $formatted_value;
}

function getGroupTotal($id, $start = '', $end = '')
{
    global $db;
    
    // Base conditions
    $conditions = "chart_groups.id = ? AND chart_ledger.transc_type = ? AND chart_ledger.patient_stt_status != 1";
    $params_dr = [$id, 'DEBIT'];
    $params_cr = [$id, 'CREDIT'];
    
    // Date filtering
    if ($start != '' && $end != '') {
        $conditions .= " AND DATE(chart_ledger.date_entry2) BETWEEN ? AND ?";
        $params_dr[] = $start;
        $params_dr[] = $end;
        $params_cr[] = $start;
        $params_cr[] = $end;
    }
    
    // Exclude closing entries if this is an income/expense group
    // We check if the group belongs to class 4 or 5
    $class_stmt = $db->prepare('SELECT class_id FROM chart_groups WHERE id = ?');
    $class_stmt->execute([$id]);
    $class_id = $class_stmt->fetch()['class_id'];
    if ($class_id == 4 || $class_id == 5) {
        $conditions .= " AND (chart_ledger.app_no NOT IN ('YEAREND', 'YEARSTART') OR chart_ledger.app_no IS NULL)";
    }

    $query = "SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as total FROM chart_ledger
              LEFT JOIN chart_accounts ON chart_accounts.account_code = chart_ledger.account_no
              LEFT JOIN chart_groups ON chart_groups.id = chart_accounts.account_group
              WHERE " . $conditions;

    $stmt_dr = $db->prepare($query);
    $stmt_dr->execute($params_dr);
    $debit_total = $stmt_dr->fetch()['total'] ?: 0;

    $stmt_cr = $db->prepare($query);
    $stmt_cr->execute($params_cr);
    $credit_total = $stmt_cr->fetch()['total'] ?: 0;

    return ['DR' => $debit_total, 'CR' => $credit_total, 'bal' => ($debit_total - $credit_total)];
}

function getAccountTotal($code, $start = '', $end = '')
{
    global $db;
    
    $conditions = "chart_ledger.account_no = ? AND chart_ledger.transc_type = ? AND chart_ledger.patient_stt_status != 1";
    $params_dr = [$code, 'DEBIT'];
    $params_cr = [$code, 'CREDIT'];
    
    // Date filtering
    if ($start != '' && $end != '') {
        $conditions .= " AND DATE(chart_ledger.date_entry2) BETWEEN ? AND ?";
        $params_dr[] = $start;
        $params_dr[] = $end;
        $params_cr[] = $start;
        $params_cr[] = $end;
    }
    
    // Exclude closing entries if this is an income/expense account
    $class_stmt = $db->prepare('SELECT g.class_id FROM chart_accounts a JOIN chart_groups g ON a.account_group = g.id WHERE a.account_code = ?');
    $class_stmt->execute([$code]);
    $class_res = $class_stmt->fetch();
    if ($class_res && ($class_res['class_id'] == 4 || $class_res['class_id'] == 5)) {
        $conditions .= " AND (chart_ledger.app_no NOT IN ('YEAREND', 'YEARSTART') OR chart_ledger.app_no IS NULL)";
    }

    $query = "SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as total FROM chart_ledger WHERE " . $conditions;

    $stmt_dr = $db->prepare($query);
    $stmt_dr->execute($params_dr);
    $debit_total = $stmt_dr->fetch()['total'] ?: 0;

    $stmt_cr = $db->prepare($query);
    $stmt_cr->execute($params_cr);
    $credit_total = $stmt_cr->fetch()['total'] ?: 0;

    return ['DR' => $debit_total, 'CR' => $credit_total, 'bal' => ($debit_total - $credit_total)];
}

function getClassTotal($id, $start = '', $end = '')
{
    global $db;
    $debit_total = 0;
    $credit_total = 0;

    $groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ?');
    $groups->execute([$id]);
    $groups = $groups->fetchAll(PDO::FETCH_ASSOC);

    foreach ($groups as $group) {
        $gid = $group['id'];
        $totals = getGroupTotal($gid, $start, $end);
        $debit_total += $totals['DR'];
        $credit_total += $totals['CR'];
    }

    return ['DR' => $debit_total, 'CR' => $credit_total, 'bal' => ($debit_total - $credit_total)];
}

function getClassesAggr($classes, $start = '', $end = '')
{
    $aggregate = ['DR' => 0, 'CR' => 0, 'bal' => 0];
    foreach ($classes as $class) {
        $r = getClassTotal($class, $start, $end);
        $aggregate['DR'] += $r['DR'];
        $aggregate['CR'] += $r['CR'];
        $aggregate['bal'] += $r['bal'];
    }

    return $aggregate;
}

function getAccountName($code)
{
    global $db;

    $name = $db->prepare('SELECT account_name FROM chart_accounts WHERE account_code = ?');

    $name->execute([$code]);

    return $name->fetch()['account_name'];
}

function getInsuranceName($insurance_no)
{
    global $db;
    $stmt_bnk = $db->query("SELECT name FROM stock_company where sn='$insurance_no'");
    $row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC);
    return $row_rstbank['name'];
}

function makePositive($val)
{
    if ($val < 1) {
        $r = $val * (-1);
    } else {
        $r = $val;
    }

    return $r;
}

function set_flash_message($msg, $type = 'success')
{
    $_SESSION['flash_msg'] = $msg;
    $_SESSION['flash_msg_type'] = $type;

    return true;
}

function show_flash_msg()
{
    if (isset($_SESSION['flash_msg']) && isset($_SESSION['flash_msg_type']) && $_SESSION['flash_msg'] != '' && $_SESSION['flash_msg_type'] != '') {
        $msg = $_SESSION['flash_msg'];
        $type = $_SESSION['flash_msg_type'];
        $str = "<div class='alert alert-" . $type . "'>" . $msg . '</div>';

        unset($_SESSION['flash_msg']);
        unset($_SESSION['flash_msg_type']);

        return $str;
    }
}

function get_active_year()
{
    global $db;
    $fical_year = $db->query("SELECT * FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
    $fical_year = $fical_year->fetch();
    return $fical_year;
}

function redirect_to_active_year()
{
    if (!isset($_GET['start']) && !isset($_GET['end'])) {
        $active_year = get_active_year();
        header('Location:?start=' . $active_year['begin'] . '&end=' . $active_year['end']);
    }
}

function redirect_to_active_day()
{
    if (!isset($_GET['start']) && !isset($_GET['end'])) {
        $today = date('Y-m-d');
        header('Location:?start=' . $today . '&end=' . $today);
    }
}

function getTotalDebitAndCredit($start_date = '', $end_date = '')
{
    global $db;

    // Base SQL query
    $sql = 'SELECT SUM(dr_amt) AS total_debit, SUM(cr_amt) AS total_credit FROM chart_ledger WHERE patient_stt_status != 1';
    $params = [];

    // Add date range filtering
    if ($start_date != '' && $end_date != '') {
        $sql .= ' AND DATE(date_entry2) BETWEEN :start_date AND :end_date';
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    // Prepare the query
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Fetch the results
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'total_debit' => $result['total_debit'] ? $result['total_debit'] : 0,
        'total_credit' => $result['total_credit'] ? $result['total_credit'] : 0,
    ];
}


function get_fiscal_years() {
    global $db;
    $stmt = $db->query("SELECT * FROM chart_fiscal_year ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function render_fiscal_year_filter($selected_year_id = null) {
    $years = get_fiscal_years();
    $active_year = get_active_year();
    if ($selected_year_id === null && $active_year) {
        $selected_year_id = $active_year['id'];
    }
    
    $html = '<div class="form-group">';
    $html .= '<label for="fiscal_year_filter">Fiscal Year</label>';
    $html .= '<select id="fiscal_year_filter" name="fiscal_year" class="form-control" onchange="updateFiscalYearDates(this)">';
    $html .= '<option value="">-- All Time --</option>';
    
    foreach ($years as $year) {
        $selected = ($year['id'] == $selected_year_id) ? 'selected' : '';
        $status = ($year['closed'] == 1) ? 'Closed' : 'Active';
        $disabled = ($year['closed'] == 1) ? 'disabled' : '';
        $label = date('M Y', strtotime($year['begin'])) . ' - ' . date('M Y', strtotime($year['end'])) . " ($status)";
        
        // Encode bounds to use in Javascript
        $data_attr = 'data-start="' . $year['begin'] . '" data-end="' . $year['end'] . '"';
        
        $html .= "<option value='{$year['id']}' {$data_attr} {$selected} {$disabled}>{$label}</option>";
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    // Add JS to auto-fill start and end dates
    $html .= '
    <script>
    function updateFiscalYearDates(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        if (selectedOption.value) {
            var start = selectedOption.getAttribute("data-start");
            var end = selectedOption.getAttribute("data-end");
            
            // Look for common start/end date inputs
            var startInput = document.getElementById("start_date") || document.getElementById("start");
            var endInput = document.getElementById("end_date") || document.getElementById("end");
            
            if (startInput) startInput.value = start;
            if (endInput) endInput.value = end;
        }
    }
    </script>
    ';
    
    return $html;
}
