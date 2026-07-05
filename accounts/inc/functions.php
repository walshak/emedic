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
    if ($start != '' && $end != '') {
        global $db;
        $debit_total = $db->prepare('SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as dr_total FROM chart_ledger
        LEFT JOIN chart_accounts ON chart_accounts.account_code = chart_ledger.account_no
        LEFT JOIN chart_groups ON chart_groups.id = chart_accounts.account_group
        WHERE chart_groups.id = ? AND chart_ledger.transc_type = ? AND chart_ledger.patient_stt_status!=1 AND DATE(chart_ledger.date_entry2) BETWEEN ? AND ?');

        $debit_total->execute([$id, 'DEBIT', $start, $end]);

        $debit_total = $debit_total->fetch()['dr_total'];

        $credit_total = $db->prepare('SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as cr_total FROM chart_ledger
        LEFT JOIN chart_accounts ON chart_accounts.account_code = chart_ledger.account_no
        LEFT JOIN chart_groups ON chart_groups.id = chart_accounts.account_group
        WHERE chart_groups.id = ? AND chart_ledger.transc_type = ? AND chart_ledger.patient_stt_status!=1 AND DATE(chart_ledger.date_entry2) BETWEEN ? AND ?');

        $credit_total->execute([$id, 'CREDIT', $start, $end]);

        $credit_total = $credit_total->fetch()['cr_total'];
    } else {
        global $db;
        $debit_total = $db->query("SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as dr_total FROM chart_ledger
        LEFT JOIN chart_accounts ON chart_accounts.account_code = chart_ledger.account_no
        LEFT JOIN chart_groups ON chart_groups.id = chart_accounts.account_group
        WHERE chart_groups.id = '$id' AND chart_ledger.transc_type = 'DEBIT' AND chart_ledger.patient_stt_status!=1");

        $debit_total = $debit_total->fetch()['dr_total'];

        $credit_total = $db->query("SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as cr_total FROM chart_ledger
        LEFT JOIN chart_accounts ON chart_accounts.account_code = chart_ledger.account_no
        LEFT JOIN chart_groups ON chart_groups.id = chart_accounts.account_group
        WHERE chart_groups.id = '$id' AND chart_ledger.patient_stt_status!=1 AND  chart_ledger.transc_type = 'CREDIT'");

        $credit_total = $credit_total->fetch()['cr_total'];
    }

    return ['DR' => $debit_total, 'CR' => $credit_total, 'bal' => ($debit_total - $credit_total)];
}

function getAccountTotal($code, $start = '', $end = '')
{
    if ($start != '' && $end != '') {
        global $db;
        $debit_total = $db->prepare('SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as dr_total FROM chart_ledger
        WHERE chart_ledger.account_no = ? AND chart_ledger.transc_type = ? AND chart_ledger.patient_stt_status!=1 AND DATE(chart_ledger.date_entry2) BETWEEN ? AND ?');

        $debit_total->execute([$code, 'DEBIT', $start, $end]);
        $debit_total = $debit_total->fetch()['dr_total'];

        $credit_total = $db->prepare('SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as cr_total FROM chart_ledger
        WHERE chart_ledger.account_no = ? AND chart_ledger.transc_type = ? AND chart_ledger.patient_stt_status!=1 AND DATE(chart_ledger.date_entry2) BETWEEN ? AND ?');

        $credit_total->execute([$code, 'CREDIT', $start, $end]);
        $credit_total = $credit_total->fetch()['cr_total'];
    } else {
        global $db;
        $debit_total = $db->prepare('SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as dr_total FROM chart_ledger
        WHERE chart_ledger.account_no = ? AND chart_ledger.patient_stt_status!=1 AND chart_ledger.transc_type = ?');

        $debit_total->execute([$code, 'DEBIT']);
        $debit_total = $debit_total->fetch()['dr_total'];

        $credit_total = $db->prepare('SELECT (SUM(`dr_amt`) + SUM(`cr_amt`)) as cr_total FROM chart_ledger
        WHERE chart_ledger.account_no = ? AND chart_ledger.patient_stt_status!=1 AND chart_ledger.transc_type = ?');

        $credit_total->execute([$code, 'CREDIT']);
        $credit_total = $credit_total->fetch()['cr_total'];
    }

    return ['DR' => $debit_total, 'CR' => $credit_total, 'bal' => ($debit_total - $credit_total)];
}

function getClassTotal($id, $start = '', $end = '')
{
    if ($start == '' || $end == '') {
        global $db;

        $debit_total = 0;
        $credit_total = 0;

        $groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ?');
        $groups->execute([$id]);
        $groups = $groups->fetchAll(PDO::FETCH_ASSOC);

        for ($y = 0; $y < count($groups); ++$y) {
            $gid = $groups[$y]['id'];

            $debit_total += getGroupTotal($gid)['DR'];

            $credit_total += getGroupTotal($gid)['CR'];
        }
    } else {
        global $db;

        $debit_total = 0;
        $credit_total = 0;

        $groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ?');
        $groups->execute([$id]);
        $groups = $groups->fetchAll(PDO::FETCH_ASSOC);

        for ($y = 0; $y < count($groups); ++$y) {
            $gid = $groups[$y]['id'];

            $debit_total += getGroupTotal($gid, $start, $end)['DR'];

            $credit_total += getGroupTotal($gid, $start, $end)['CR'];
        }
    }

    return ['DR' => $debit_total, 'CR' => $credit_total, 'bal' => ($debit_total - $credit_total)];
}

function getClassesAggr($classes, $start = '', $end = '')
{
    $aggregate = ['DR' => 0, 'CR' => 0, 'bal' => 0];
    foreach ($classes as $class) {
        if ($start == '' || $end == '') {
            $r = getClassTotal($class);
        } else {
            $r = getClassTotal($class, $start, $start);
        }
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

    // Base SQL query without date filtering
    $sql = 'SELECT SUM(dr_amt) AS total_debit, SUM(cr_amt) AS total_credit FROM chart_ledger';

    // Add date range filtering if both start and end dates are provided
    if ($start_date != '' && $end_date != '') {
        $sql .= ' WHERE chart_ledger.patient_stt_status!=1 AND DATE(chart_ledger.date_entry2) BETWEEN :start_date AND :end_date';
    }

    // Prepare the query
    $stmt = $db->prepare($sql);

    // Bind parameters if dates are provided
    if ($start_date != '' && $end_date != '') {
        $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
    } else {
        $stmt->execute();
    }

    // Fetch the results
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the totals in an associative array, defaulting to 0 if no results
    return [
        'total_debit' => $result['total_debit'] ? $result['total_debit'] : 0,
        'total_credit' => $result['total_credit'] ? $result['total_credit'] : 0,
    ];
}
