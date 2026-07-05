<?php
include("../Connections/Conn.php");
session_start();
include('../inc/header.php');
include 'inc/functions.php';

// Get date rang// Helper to get totals for an account
function getAccountTotals($db, $account_code, $start, $end)
{
    global $totals_cache;
    $cache_key = $account_code . '_' . $start . '_' . $end;

    if (isset($totals_cache['account'][$cache_key])) {
        return $totals_cache['account'][$cache_key];
    }

    $stmt = $db->prepare('
        SELECT 
            SUM(CASE WHEN transc_type="DEBIT" THEN dr_amt ELSE 0 END) as total_dr,
            SUM(CASE WHEN transc_type="CREDIT" THEN cr_amt ELSE 0 END) as total_cr
        FROM chart_ledger 
        WHERE account_no = ? AND patient_stt_status!=1 AND DATE(date_entry2) BETWEEN ? AND ?
    ');
    $stmt->execute([$account_code, $start, $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $net = ($row['total_dr'] ?: 0) - ($row['total_cr'] ?: 0);

    $result = [
        'dr' => $row['total_dr'] ?: 0,
        'cr' => $row['total_cr'] ?: 0,
        'net' => $net
    ];

    $totals_cache['account'][$cache_key] = $result;
    return $result;
}

// Default to today
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Fetch all classes
$classes = $db->query('SELECT * FROM chart_class')->fetchAll(PDO::FETCH_ASSOC);

// PERFORMANCE OPTIMIZATION: Cache for totals to avoid redundant queries
$totals_cache = [
    'class' => [],
    'group' => [],
    'account' => []
];
$entries_cache = [];

function getGroups($db, $class_id)
{
    $stmt = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ?');
    $stmt->execute([$class_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAccounts($db, $group_id)
{
    $stmt = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ?');
    $stmt->execute([$group_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLedgerEntries($db, $account_code, $start, $end)
{
    global $entries_cache;
    $cache_key = $account_code . '_' . $start . '_' . $end;

    if (isset($entries_cache[$cache_key])) {
        return $entries_cache[$cache_key];
    }

    $stmt = $db->prepare('
        SELECT cl.*, ca.account_name, ca.account_code
        FROM chart_ledger cl
        JOIN chart_accounts ca ON cl.account_no = ca.account_code
        WHERE cl.account_no = ? AND cl.patient_stt_status!=1 AND DATE(cl.date_entry2) BETWEEN ? AND ?
        ORDER BY cl.date_entry2 ASC, cl.sn ASC
    ');
    $stmt->execute([$account_code, $start, $end]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $entries_cache[$cache_key] = $result;
    return $result;
}

// Helper to get all totals for the whole period
function getAllTotals($db, $start, $end)
{
    $stmt = $db->prepare('
        SELECT 
            SUM(CASE WHEN cl.transc_type="DEBIT" THEN cl.dr_amt ELSE 0 END) as total_dr,
            SUM(CASE WHEN cl.transc_type="CREDIT" THEN cl.cr_amt ELSE 0 END) as total_cr
        FROM chart_ledger cl
        WHERE cl.patient_stt_status!=1 AND DATE(cl.date_entry2) BETWEEN ? AND ?
    ');
    $stmt->execute([$start, $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // If no entries, set to 0
    $row['total_dr'] = $row['total_dr'] !== null ? $row['total_dr'] : 0;
    $row['total_cr'] = $row['total_cr'] !== null ? $row['total_cr'] : 0;
    $net = $row['total_dr'] - $row['total_cr'];
    return [
        'dr' => $row['total_dr'],
        'cr' => $row['total_cr'],
        'net' => $net
    ];
}

// Helper to get totals for a class
function getClassTotals($db, $class_id, $start, $end)
{
    global $totals_cache;
    $cache_key = $class_id . '_' . $start . '_' . $end;

    if (isset($totals_cache['class'][$cache_key])) {
        return $totals_cache['class'][$cache_key];
    }

    $stmt = $db->prepare('
        SELECT 
            SUM(CASE WHEN cl.transc_type="DEBIT" THEN cl.dr_amt ELSE 0 END) as total_dr,
            SUM(CASE WHEN cl.transc_type="CREDIT" THEN cl.cr_amt ELSE 0 END) as total_cr
        FROM chart_ledger cl
        JOIN chart_accounts ca ON cl.account_no = ca.account_code
        JOIN chart_groups cg ON ca.account_group = cg.id
        WHERE cg.class_id = ? AND cl.patient_stt_status!=1 AND DATE(cl.date_entry2) BETWEEN ? AND ?
    ');
    $stmt->execute([$class_id, $start, $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $net = ($row['total_dr'] ?: 0) - ($row['total_cr'] ?: 0);

    $result = [
        'dr' => $row['total_dr'] ?: 0,
        'cr' => $row['total_cr'] ?: 0,
        'net' => $net
    ];

    $totals_cache['class'][$cache_key] = $result;
    return $result;
}

// Helper to get totals for a group
function getGroupTotals($db, $group_id, $start, $end)
{
    global $totals_cache;
    $cache_key = $group_id . '_' . $start . '_' . $end;

    if (isset($totals_cache['group'][$cache_key])) {
        return $totals_cache['group'][$cache_key];
    }

    $stmt = $db->prepare('
        SELECT 
            SUM(CASE WHEN cl.transc_type="DEBIT" THEN cl.dr_amt ELSE 0 END) as total_dr,
            SUM(CASE WHEN cl.transc_type="CREDIT" THEN cl.cr_amt ELSE 0 END) as total_cr
        FROM chart_ledger cl
        JOIN chart_accounts ca ON cl.account_no = ca.account_code
        WHERE ca.account_group = ? AND cl.patient_stt_status!=1 AND DATE(cl.date_entry2) BETWEEN ? AND ?
    ');
    $stmt->execute([$group_id, $start, $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $net = ($row['total_dr'] ?: 0) - ($row['total_cr'] ?: 0);

    $result = [
        'dr' => $row['total_dr'] ?: 0,
        'cr' => $row['total_cr'] ?: 0,
        'net' => $net
    ];

    $totals_cache['group'][$cache_key] = $result;
    return $result;
}

?>
<style>
    /* Flat, compact, hierarchical, detail-rich UI */
    .ledger-section,
    .group-section,
    .account-section {
        border-left: 3px solid #e0e0e0;
        margin-bottom: 0.5rem;
        padding-left: 0.7rem;
        /* Add horizontal border for L-shape */
        border-bottom: none;
        border-radius: 0 0 0 0;
        background: none;
    }

    .ledger-section {
        border-color: #007bff;
        margin-bottom: 2.5rem;
        border-bottom: 4px solid #007bff;
        padding-bottom: 1.5rem;
        background: none;
    }

    .group-section {
        border-color: #17a2b8;
        margin-bottom: 1.5rem;
        border-bottom: 3px solid #17a2b8;
        padding-bottom: 1rem;
        background: none;
    }

    .account-section {
        border-color: #6c757d;
        margin-bottom: 1rem;
        border-bottom: 2px solid #6c757d;
        padding-bottom: 0.7rem;
        background: none;
    }

    .section-header {
        font-weight: 600;
        font-size: 1.1rem;
        background: #f8f9fa;
        margin-bottom: 0.5em;
        padding: 0.3rem 0.5rem;
        display: flex;
        align-items: center;
        cursor: pointer;
        gap: 1.5em;
    }

    .section-header .toggle-link {
        font-size: 1.1em;
        margin-right: 0.5em;
        color: #007bff;
        cursor: pointer;
        text-decoration: none;
    }

    .section-header .section-title {
        display: flex;
        align-items: center;
        gap: 0.5em;
    }

    .section-header .section-stats {
        margin-left: 1.5em;
        color: #444;
        font-size: 0.97em;
        font-weight: 400;
        opacity: 0.95;
        display: flex;
        gap: 1em;
        align-items: center;
    }

    .ledger-section .section-header span.section-title {
        font-size: 1.5em;
        font-weight: 700;
        color: #007bff;
    }

    .group-section .section-header span.section-title {
        font-size: 1.25em;
        font-weight: 600;
        color: #17a2b8;
    }

    .account-section .section-header span.section-title {
        font-size: 1em;
        font-weight: 500;
        color: #6c757d;
    }

    .totals-row {
        background: #f1f3f4;
        font-weight: bold;
        font-size: 0.98em;
    }

    .compact-table th,
    .compact-table td {
        padding: 0.25rem 0.4rem !important;
        font-size: 0.93em;
    }

    .compact-table {
        margin-bottom: 0.3rem;
    }

    .subtotals {
        font-size: 0.97em;
        color: #333;
        margin: 0.2rem 0 0.5rem 0.5rem;
    }

    .summary-top,
    .section-summary {
        background: #e9ecef;
        border-radius: 4px;
        padding: 0.5em 1em;
        margin-bottom: 0.7em;
        font-size: 1em;
        display: flex;
        flex-wrap: wrap;
        gap: 2em;
        align-items: center;
    }

    .summary-top strong,
    .section-summary strong {
        color: #007bff;
    }

    .section-summary {
        background: #f6f8fa;
        margin-bottom: 0.3em;
        font-size: 0.97em;
    }

    /* Add vertical spacing above each section for clarity */
    .ledger-section {
        margin-top: 2.5rem;
    }

    .group-section {
        margin-top: 1.5rem;
    }

    .account-section {
        margin-top: 1rem;
    }
</style>
<script>
    function toggleSection(id) {
        var el = document.getElementById(id);
        if (el.style.display === "none") {
            el.style.display = "";
            document.getElementById(id + "_toggle").innerHTML = "&#9660;";
        } else {
            el.style.display = "none";
            document.getElementById(id + "_toggle").innerHTML = "&#9654;";
        }
    }
</script>

<body class="fixed-navigation">
    <div id="wrapper">
        <?php include("nav_side.php"); ?>
        <div id="page-wrapper" class="gray-bg sidebar-content">
            <?php include '../../inc/nav_header.php'; ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>General Ledger Sheet</h5>
                        </div>
                        <div class="ibox-content">
                            <form method="get" class="form-inline mb-2">
                                <label>Start Date</label>
                                <input type="date" name="start" class="form-control input-sm" value="<?php echo htmlspecialchars($start_date); ?>" required>
                                &nbsp;
                                <label>End Date</label>
                                <input type="date" name="end" class="form-control input-sm" value="<?php echo htmlspecialchars($end_date); ?>" required>
                                &nbsp;
                                <button type="submit" class="btn btn-primary btn-sm">Display</button>
                                <button type="button" class="btn btn-success btn-sm" onclick="exportGeneralLedgerDirectly()" title="Export to CSV without loading data">
                                    <i class="fa fa-file-excel-o"></i> Export CSV
                                </button>
                            </form>
                            <?php
                            // OPTIMIZED: Calculate main totals with a single query instead of nested loops
                            $main_totals_query = $db->prepare('
                                SELECT 
                                    SUM(CASE WHEN cl.transc_type="DEBIT" THEN cl.dr_amt ELSE 0 END) as total_dr,
                                    SUM(CASE WHEN cl.transc_type="CREDIT" THEN cl.cr_amt ELSE 0 END) as total_cr
                                FROM chart_ledger cl
                                WHERE cl.patient_stt_status!=1 AND DATE(cl.date_entry2) BETWEEN ? AND ?
                            ');
                            $main_totals_query->execute([$start_date, $end_date]);
                            $main_totals_result = $main_totals_query->fetch(PDO::FETCH_ASSOC);
                            $main_total_dr = $main_totals_result['total_dr'] ?: 0;
                            $main_total_cr = $main_totals_result['total_cr'] ?: 0;
                            $main_net = $main_total_dr - $main_total_cr;
                            ?>
                            <div class="summary-top">
                                <strong>Period:</strong> <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
                                <strong>Total Debits:</strong> <?php echo format_accounting($main_total_dr); ?>
                                <strong>Total Credits:</strong> <?php echo format_accounting($main_total_cr); ?>
                                <strong>Net:</strong>
                                <?php
                                echo format_accounting(abs($main_net)) . ($main_net >= 0 ? ' DR' : ' CR');
                                ?>
                            </div>
                            <div id="general_ledger_sheet">
                                <?php $class_idx = 0;
                                foreach ($classes as $class): $class_idx++; ?>
                                    <?php
                                    $class_totals = getClassTotals($db, $class['cid'], $start_date, $end_date);
                                    ?>
                                    <div class="ledger-section">
                                        <div class="section-header" onclick="toggleSection('class_<?php echo $class_idx; ?>')">
                                            <span id="class_<?php echo $class_idx; ?>_toggle" class="toggle-link">&#9660;</span>
                                            <span class="section-title"><?php echo htmlspecialchars($class['class_name']); ?></span>
                                            <span class="section-stats">
                                                | <span><strong>Debits:</strong> <?php echo format_accounting($class_totals['dr']); ?></span>
                                                <span><strong>Credits:</strong> <?php echo format_accounting($class_totals['cr']); ?></span>
                                                <span><strong>Net:</strong>
                                                    <?php echo format_accounting(abs($class_totals['net'])) . ($class_totals['net'] >= 0 ? ' DR' : ' CR'); ?>
                                                </span>
                                                | <span><strong>Period:</strong> <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></span>
                                            </span>
                                            <span style="margin-left:auto;">
                                                <a href="#" onclick="event.stopPropagation();printSection('class_<?php echo $class_idx; ?>');return false;" title="Print Class"><i class="fa fa-print"></i></a>
                                                <a href="#" onclick="event.stopPropagation();exportClassToCSV('<?php echo $class['cid']; ?>', '<?php echo addslashes($class['class_name']); ?>');return false;" title="Export Class to CSV" style="margin-left:5px;"><i class="fa fa-file-excel-o"></i></a>
                                            </span>
                                        </div>
                                        <?php
                                        $class_total_dr = 0;
                                        $class_total_cr = 0;
                                        $class_groups = getGroups($db, $class['cid']);
                                        ?>
                                        <div id="class_<?php echo $class_idx; ?>">
                                            <?php $group_idx = 0;
                                            foreach ($class_groups as $group): $group_idx++; ?>
                                                <?php
                                                $group_totals = getGroupTotals($db, $group['id'], $start_date, $end_date);
                                                ?>
                                                <div class="group-section">
                                                    <div class="section-header" onclick="toggleSection('group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>')">
                                                        <span id="group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_toggle" class="toggle-link">&#9660;</span>
                                                        <span class="section-title"><?php echo htmlspecialchars($group['name']); ?></span>
                                                        <span class="section-stats">
                                                            | <span><strong>Debits:</strong> <?php echo format_accounting($group_totals['dr']); ?></span>
                                                            <span><strong>Credits:</strong> <?php echo format_accounting($group_totals['cr']); ?></span>
                                                            <span><strong>Net:</strong>
                                                                <?php echo format_accounting(abs($group_totals['net'])) . ($group_totals['net'] >= 0 ? ' DR' : ' CR'); ?>
                                                            </span>
                                                            | <span><strong>Period:</strong> <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></span>
                                                        </span>
                                                        <span style="margin-left:auto;">
                                                            <a href="#" onclick="event.stopPropagation();printSection('group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>');return false;" title="Print Group"><i class="fa fa-print"></i></a>
                                                            <a href="#" onclick="event.stopPropagation();exportGroupToCSV('<?php echo $group['id']; ?>', '<?php echo addslashes($group['group_name']); ?>');return false;" title="Export Group to CSV" style="margin-left:5px;"><i class="fa fa-file-excel-o"></i></a>
                                                        </span>
                                                    </div>
                                                    <?php
                                                    $group_total_dr = 0;
                                                    $group_total_cr = 0;
                                                    $group_accounts = getAccounts($db, $group['id']);
                                                    ?>
                                                    <div id="group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>">
                                                        <?php $acc_idx = 0;
                                                        foreach ($group_accounts as $account): $acc_idx++; ?>
                                                            <?php
                                                            $entries = getLedgerEntries($db, $account['account_code'], $start_date, $end_date);
                                                            if (empty($entries)) continue;
                                                            $running_balance = 0;
                                                            $acc_dr = 0;
                                                            $acc_cr = 0;
                                                            $acc_totals = getAccountTotals($db, $account['account_code'], $start_date, $end_date);
                                                            ?>
                                                            <div class="account-section">
                                                                <div class="section-header" onclick="toggleSection('acc_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_<?php echo $acc_idx; ?>')">
                                                                    <span id="acc_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_<?php echo $acc_idx; ?>_toggle" class="toggle-link">&#9660;</span>
                                                                    <span class="section-title">
                                                                        [<?php echo $account['account_code']; ?>] <?php echo htmlspecialchars($account['account_name']); ?>
                                                                    </span>
                                                                    <span class="section-stats">
                                                                        | <span><strong>Debits:</strong> <?php echo format_accounting($acc_totals['dr']); ?></span>
                                                                        <span><strong>Credits:</strong> <?php echo format_accounting($acc_totals['cr']); ?></span>
                                                                        <span><strong>Net:</strong>
                                                                            <?php echo format_accounting(abs($acc_totals['net'])) . ($acc_totals['net'] >= 0 ? ' DR' : ' CR'); ?>
                                                                        </span>
                                                                        | <span><strong>Period:</strong> <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></span>
                                                                    </span>
                                                                    <span style="margin-left:auto;">
                                                                        <a href="#" onclick="event.stopPropagation();printSection('acc_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_<?php echo $acc_idx; ?>');return false;" title="Print Account"><i class="fa fa-print"></i></a>
                                                                        <a href="#" onclick="event.stopPropagation();exportAccountToCSV('<?php echo $account['account_code']; ?>', '<?php echo addslashes($account['account_name']); ?>');return false;" title="Export Account to CSV" style="margin-left:5px;"><i class="fa fa-file-excel-o"></i></a>
                                                                    </span>
                                                                </div>
                                                                <div id="acc_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_<?php echo $acc_idx; ?>">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered table-hover table-sm compact-table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="width:7%;">Date</th>
                                                                                    <th style="width:10%;">Ref</th>
                                                                                    <th style="width:19%;">Description</th>
                                                                                    <th class="text-right" style="width:9%;">Debit</th>
                                                                                    <th class="text-right" style="width:9%;">Credit</th>
                                                                                    <th class="text-right" style="width:10%;">Bal</th>
                                                                                    <th style="width:10%;">By</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($entries as $entry):
                                                                                    if ($entry['transc_type'] == 'DEBIT') {
                                                                                        $running_balance += $entry['dr_amt'];
                                                                                        $acc_dr += $entry['dr_amt'];
                                                                                    } else {
                                                                                        $running_balance -= $entry['cr_amt'];
                                                                                        $acc_cr += $entry['cr_amt'];
                                                                                    }
                                                                                ?>
                                                                                    <tr>
                                                                                        <td><?php echo date('d-M-y', strtotime($entry['date_entry2'])); ?></td>
                                                                                        <td><?php echo htmlspecialchars($entry['lg_ref_no']); ?></td>
                                                                                        <td>
                                                                                            <?php if ($entry['hospital_no'] || $entry['insurance_no']): ?>
                                                                                                <span style="font-size:0.93em;">
                                                                                                    <?php
                                                                                                    echo $entry['hospital_no'] ? 'Pt: <b>' . htmlspecialchars($entry['hospital_no']) . '</b> ' : '';
                                                                                                    echo $entry['insurance_no'] ? 'Ins/Sup: <b>' . htmlspecialchars($entry['insurance_no']) . '</b>' : '';
                                                                                                    ?>
                                                                                                </span><br>
                                                                                            <?php endif ?>
                                                                                            <span style="font-size:0.93em;">
                                                                                                <b>Narr:</b> <?php echo htmlspecialchars($entry['ref_value']); ?>
                                                                                                <?php if ($entry['item_services']) echo '<br><small>' . htmlspecialchars($entry['item_services']) . '</small>'; ?>
                                                                                            </span>
                                                                                        </td>
                                                                                        <td class="text-right"><?php echo $entry['dr_amt'] > 0 ? format_accounting($entry['dr_amt']) : ''; ?></td>
                                                                                        <td class="text-right"><?php echo $entry['cr_amt'] > 0 ? format_accounting($entry['cr_amt']) : ''; ?></td>
                                                                                        <td class="text-right">
                                                                                            <?php
                                                                                            echo format_accounting(abs($running_balance)) .
                                                                                                ($running_balance >= 0 ? ' DR' : ' CR');
                                                                                            ?>
                                                                                        </td>
                                                                                        <td><?php echo htmlspecialchars($entry['prepared_by']); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                            <tfoot>
                                                                                <tr class="totals-row">
                                                                                    <td colspan="3" class="text-right">Account Total</td>
                                                                                    <td class="text-right"><?php echo format_accounting($acc_dr); ?></td>
                                                                                    <td class="text-right"><?php echo format_accounting($acc_cr); ?></td>
                                                                                    <td class="text-right">
                                                                                        <?php
                                                                                        echo format_accounting(abs($running_balance)) .
                                                                                            ($running_balance >= 0 ? ' DR' : ' CR');
                                                                                        ?>
                                                                                    </td>
                                                                                    <td></td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                    <!-- Account total summary below the table -->
                                                                    <div class="subtotals" style="margin-left:0.5rem;">
                                                                        <span style="color:#6c757d;">
                                                                            <b>Account Total for [<?php echo $account['account_code']; ?>] <?php echo htmlspecialchars($account['account_name']); ?> (<?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>):</b>
                                                                        </span>
                                                                        <span class="ml-2">Debit: <b><?php echo format_accounting($acc_dr); ?></b></span>
                                                                        <span class="ml-2">Credit: <b><?php echo format_accounting($acc_cr); ?></b></span>
                                                                        <span class="ml-2">
                                                                            Net: <b><?php
                                                                                    $net = $acc_dr - $acc_cr;
                                                                                    echo format_accounting(abs($net)) . ($net >= 0 ? ' DR' : ' CR');
                                                                                    ?></b>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                            $group_total_dr += $acc_dr;
                                                            $group_total_cr += $acc_cr;
                                                            ?>
                                                        <?php endforeach; ?>
                                                        <!-- Group subtotal -->
                                                        <div class="subtotals">
                                                            <span style="color:#17a2b8;">
                                                                <b>Group Subtotal for <?php echo htmlspecialchars($group['name']); ?> (<?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>):</b>
                                                            </span>
                                                            <span class="ml-2">Debit: <b><?php echo format_accounting($group_total_dr); ?></b></span>
                                                            <span class="ml-2">Credit: <b><?php echo format_accounting($group_total_cr); ?></b></span>
                                                            <span class="ml-2">
                                                                Net: <b><?php
                                                                        $net = $group_total_dr - $group_total_cr;
                                                                        echo format_accounting(abs($net)) . ($net >= 0 ? ' DR' : ' CR');
                                                                        ?></b>
                                                            </span>
                                                        </div>
                                                        <?php
                                                        $class_total_dr += $group_total_dr;
                                                        $class_total_cr += $group_total_cr;
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <!-- Class subtotal -->
                                            <div class="subtotals" style="margin-left:1.2rem;">
                                                <span style="color:#007bff;">
                                                    <b>Class Subtotal for <?php echo htmlspecialchars($class['class_name']); ?> (<?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>):</b>
                                                </span>
                                                <span class="ml-2">Debit: <b><?php echo format_accounting($class_total_dr); ?></b></span>
                                                <span class="ml-2">Credit: <b><?php echo format_accounting($class_total_cr); ?></b></span>
                                                <span class="ml-2">
                                                    Net: <b><?php
                                                            $net = $class_total_dr - $class_total_cr;
                                                            echo format_accounting(abs($net)) . ($net >= 0 ? ' DR' : ' CR');
                                                            ?></b>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="summary-top" style="margin-top:2em;">
                                <strong>Period:</strong> <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
                                <strong>Total Debits:</strong> <?php echo format_accounting($main_total_dr); ?>
                                <strong>Total Credits:</strong> <?php echo format_accounting($main_total_cr); ?>
                                <strong>Net:</strong>
                                <?php
                                echo format_accounting(abs($main_net)) . ($main_net >= 0 ? ' DR' : ' CR');
                                ?>
                            </div>
                            <button class="btn btn-success btn-sm m-1" onclick="printDiv('general_ledger_sheet')">
                                <i class="fa fa-print"></i>&nbsp; Print General Ledger
                            </button>
                            <button class="btn btn-info btn-sm m-1" onclick="exportGeneralLedgerToCSV()">
                                <i class="fa fa-file-text-o"></i>&nbsp; Export to CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../../inc/footer.php'; ?>
        </div>
        <?php include('../modal_lock.php'); ?>
        <?php include '/inc/footer_scripts.php'; ?>
        <script>
            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var popupWindow = window.open('', '_blank', 'width=900,height=900');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>' + document.title + '</title>');
                // Add compact print styles for consistency and compactness
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
                popupWindow.document.write(`
                    <style>
                        body {
                            font-size: 12px;
                            color: #222;
                            background: #fff;
                        }
                        .ledger-section, .group-section, .account-section {
                            border-left: 3px solid #e0e0e0 !important;
                            border-bottom: none !important;
                            padding-left: 0.7rem !important;
                            margin-bottom: 0.5rem !important;
                            background: none !important;
                        }
                        .ledger-section { border-color: #007bff !important; border-bottom: 4px solid #007bff !important; margin-bottom: 2.5rem !important; padding-bottom: 1.5rem !important; }
                        .group-section { border-color: #17a2b8 !important; border-bottom: 3px solid #17a2b8 !important; margin-bottom: 1.5rem !important; padding-bottom: 1rem !important; }
                        .account-section { border-color: #6c757d !important; border-bottom: 2px solid #6c757d !important; margin-bottom: 1rem !important; padding-bottom: 0.7rem !important; }
                        .section-header {
                            font-weight: 600;
                            font-size: 1.1rem;
                            background: #f8f9fa !important;
                            margin-bottom: 0.5em !important;
                            padding: 0.3rem 0.5rem !important;
                            display: flex !important;
                            align-items: center;
                            gap: 1.5em;
                            border-radius: 0 !important;
                        }
                        .section-header .toggle-link { display: none !important; }
                        .section-header .section-title { font-size: inherit !important; font-weight: inherit !important; }
                        .section-header .section-stats { font-size: 0.97em !important; color: #444 !important; opacity: 0.95; }
                        .ledger-section .section-header span.section-title { font-size: 1.25em !important; font-weight: 700 !important; color: #007bff !important; }
                        .group-section .section-header span.section-title { font-size: 1.12em !important; font-weight: 600 !important; color: #17a2b8 !important; }
                        .account-section .section-header span.section-title { font-size: 1em !important; font-weight: 500 !important; color: #6c757d !important; }
                        .summary-top, .section-summary {
                            background: #e9ecef !important;
                            border-radius: 4px !important;
                            padding: 0.5em 1em !important;
                            margin-bottom: 0.7em !important;
                            font-size: 1em !important;
                            display: flex !important;
                            flex-wrap: wrap !important;
                            gap: 2em !important;
                            align-items: center !important;
                        }
                        .summary-top strong, .section-summary strong { color: #007bff !important; }
                        .subtotals {
                            font-size: 0.97em !important;
                            color: #333 !important;
                            margin: 0.2rem 0 0.5rem 0.5rem !important;
                        }
                        .compact-table th, .compact-table td {
                            padding: 0.18rem 0.3rem !important;
                            font-size: 0.93em !important;
                        }
                        .compact-table {
                            margin-bottom: 0.3rem !important;
                        }
                        .totals-row {
                            background: #f1f3f4 !important;
                            font-weight: bold !important;
                            font-size: 0.98em !important;
                        }
                        .btn, .fa-print, a[onclick*="printSection"], a[onclick*="printDiv"] {
                            display: none !important;
                        }
                        @media print {
                            a[href]:after { content: none !important; }
                        }
                    </style>
                `);
                popupWindow.document.write('</head><body>');
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                popupWindow.print();
            }

            function printSection(sectionId) {
                var section = document.getElementById(sectionId);
                // Find the section header (the row with the print button)
                var header = section.previousElementSibling;
                // If the previous sibling is not the header (e.g. due to whitespace), search upwards
                while (header && !header.classList.contains('section-header')) {
                    header = header.previousElementSibling;
                }
                // Compose dynamic context heading
                var contextHeading = '';
                if (header) {
                    var title = header.querySelector('.section-title') ? header.querySelector('.section-title').innerText : '';
                    var stats = header.querySelector('.section-stats') ? header.querySelector('.section-stats').innerText : '';
                    contextHeading = '<div style="font-size:1.2em;font-weight:bold;margin-bottom:0.7em;">' +
                        'General Ledger Section: ' + title +
                        (stats ? '<span style="font-size:0.95em;font-weight:normal;"> &nbsp; ' + stats + '</span>' : '') +
                        '</div>';
                }
                var content = '';
                if (header) {
                    content += '<div class="' + header.className + '">' + header.innerHTML + '</div>';
                }
                content += section.outerHTML;

                var popupWindow = window.open('', '_blank', 'width=900,height=900');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>' + document.title + '</title>');
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
                popupWindow.document.write(`
                    <style>
                        body {
                            font-size: 12px;
                            color: #222;
                            background: #fff;
                        }
                        .ledger-section, .group-section, .account-section {
                            border-left: 3px solid #e0e0e0 !important;
                            border-bottom: none !important;
                            padding-left: 0.7rem !important;
                            margin-bottom: 0.5rem !important;
                            background: none !important;
                        }
                        .ledger-section { border-color: #007bff !important; border-bottom: 4px solid #007bff !important; margin-bottom: 2.5rem !important; padding-bottom: 1.5rem !important; }
                        .group-section { border-color: #17a2b8 !important; border-bottom: 3px solid #17a2b8 !important; margin-bottom: 1.5rem !important; padding-bottom: 1rem !important; }
                        .account-section { border-color: #6c757d !important; border-bottom: 2px solid #6c757d !important; margin-bottom: 1rem !important; padding-bottom: 0.7rem !important; }
                        .section-header {
                            font-weight: 600;
                            font-size: 1.1rem;
                            background: #f8f9fa !important;
                            margin-bottom: 0.5em !important;
                            padding: 0.3rem 0.5rem !important;
                            display: flex !important;
                            align-items: center;
                            gap: 1.5em;
                            border-radius: 0 !important;
                        }
                        .section-header .toggle-link { display: none !important; }
                        .section-header .section-title { font-size: inherit !important; font-weight: inherit !important; }
                        .section-header .section-stats { font-size: 0.97em !important; color: #444 !important; opacity: 0.95; }
                        .ledger-section .section-header span.section-title { font-size: 1.25em !important; font-weight: 700 !important; color: #007bff !important; }
                        .group-section .section-header span.section-title { font-size: 1.12em !important; font-weight: 600 !important; color: #17a2b8 !important; }
                        .account-section .section-header span.section-title { font-size: 1em !important; font-weight: 500 !important; color: #6c757d !important; }
                        .summary-top, .section-summary {
                            background: #e9ecef !important;
                            border-radius: 4px !important;
                            padding: 0.5em 1em !important;
                            margin-bottom: 0.7em !important;
                            font-size: 1em !important;
                            display: flex !important;
                            flex-wrap: wrap !important;
                            gap: 2em !important;
                            align-items: center !important;
                        }
                        .summary-top strong, .section-summary strong { color: #007bff !important; }
                        .subtotals {
                            font-size: 0.97em !important;
                            color: #333 !important;
                            margin: 0.2rem 0 0.5rem 0.5rem !important;
                        }
                        .compact-table th, .compact-table td {
                            padding: 0.18rem 0.3rem !important;
                            font-size: 0.93em !important;
                        }
                        .compact-table {
                            margin-bottom: 0.3rem !important;
                        }
                        .totals-row {
                            background: #f1f3f4 !important;
                            font-weight: bold !important;
                            font-size: 0.98em !important;
                        }
                        .btn, .fa-print, a[onclick*="printSection"], a[onclick*="printDiv"] {
                            display: none !important;
                        }
                        @media print {
                            a[href]:after { content: none !important; }
                        }
                    </style>
                `);
                popupWindow.document.write('</head><body>');
                popupWindow.document.write(contextHeading);
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                popupWindow.print();
            }

            function exportGeneralLedgerToCSV() {
                // Get URL parameters for direct database query
                var startDate = "<?php echo $start_date; ?>";
                var endDate = "<?php echo $end_date; ?>";

                // Validate dates
                if (!startDate || !endDate) {
                    alert('Please select a date range first.');
                    return;
                }

                // Create download URL with parameters
                var exportUrl = 'general_ledger_export_csv.php?start=' + encodeURIComponent(startDate) +
                    '&end=' + encodeURIComponent(endDate);

                // Create temporary download link
                var link = document.createElement('a');
                link.href = exportUrl;
                link.download = 'General_Ledger_' + startDate + '_to_' + endDate + '.csv';
                link.style.display = 'none';

                // Trigger download
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportClassToCSV(classId, className) {
                var startDate = "<?php echo $start_date; ?>";
                var endDate = "<?php echo $end_date; ?>";

                if (!startDate || !endDate) {
                    alert('Please select a date range first.');
                    return;
                }

                var exportUrl = 'general_ledger_export_csv.php?start=' + encodeURIComponent(startDate) +
                    '&end=' + encodeURIComponent(endDate) + '&class_id=' + encodeURIComponent(classId);

                var link = document.createElement('a');
                link.href = exportUrl;
                link.download = 'Class_' + className + '_' + startDate + '_to_' + endDate + '.csv';
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportGroupToCSV(groupId, groupName) {
                var startDate = "<?php echo $start_date; ?>";
                var endDate = "<?php echo $end_date; ?>";

                if (!startDate || !endDate) {
                    alert('Please select a date range first.');
                    return;
                }

                var exportUrl = 'general_ledger_export_csv.php?start=' + encodeURIComponent(startDate) +
                    '&end=' + encodeURIComponent(endDate) + '&group_id=' + encodeURIComponent(groupId);

                var link = document.createElement('a');
                link.href = exportUrl;
                link.download = 'Group_' + groupName + '_' + startDate + '_to_' + endDate + '.csv';
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportAccountToCSV(accountCode, accountName) {
                var startDate = "<?php echo $start_date; ?>";
                var endDate = "<?php echo $end_date; ?>";

                if (!startDate || !endDate) {
                    alert('Please select a date range first.');
                    return;
                }

                var exportUrl = 'general_ledger_export_csv.php?start=' + encodeURIComponent(startDate) +
                    '&end=' + encodeURIComponent(endDate) + '&account_code=' + encodeURIComponent(accountCode);

                var link = document.createElement('a');
                link.href = exportUrl;
                link.download = 'Account_' + accountName + '_' + startDate + '_to_' + endDate + '.csv';
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportGeneralLedgerDirectly() {
                // Get form values directly from the form inputs
                var startDate = document.querySelector('input[name="start"]').value;
                var endDate = document.querySelector('input[name="end"]').value;

                // Validate dates
                if (!startDate || !endDate) {
                    alert('Please select start and end dates first.');
                    return;
                }

                // Create download URL with parameters
                var exportUrl = 'general_ledger_export_csv.php?start=' + encodeURIComponent(startDate) +
                    '&end=' + encodeURIComponent(endDate);

                // Create temporary download link
                var link = document.createElement('a');
                link.href = exportUrl;
                link.download = 'General_Ledger_' + startDate + '_to_' + endDate + '.csv';
                link.style.display = 'none';

                // Trigger download
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
        <script src="../js/idle.js"></script>
</body>

</html>