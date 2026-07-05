<?php

include("../Connections/Conn.php");

session_start();
include('../inc/header.php');

// get income and expense classes
$classes = $db->query('SELECT * FROM chart_class WHERE 1');

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);
include 'inc/functions.php';

// Function to get accounts based on different hierarchy levels
function getAccountsByLevel($db, $identifier, $level)
{
    switch ($level) {
        case 'account':
            $query = $db->prepare('SELECT * FROM chart_accounts WHERE account_code = ?');
            $query->execute([$identifier]);
            return $query->fetchAll(PDO::FETCH_ASSOC);

        case 'group':
            $query = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ?');
            $query->execute([$identifier]);
            return $query->fetchAll(PDO::FETCH_ASSOC);

        case 'class':
            $query = $db->prepare('
                SELECT ca.* 
                FROM chart_accounts ca
                JOIN chart_groups cg ON ca.account_group = cg.id
                WHERE cg.class_id = ?
            ');
            $query->execute([$identifier]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Function to get name of the selected level
function getLevelName($db, $identifier, $level)
{
    switch ($level) {
        case 'account':
            $query = $db->prepare('SELECT account_name as name FROM chart_accounts WHERE account_code = ?');
            break;
        case 'group':
            $query = $db->prepare('SELECT name FROM chart_groups WHERE id = ?');
            break;
        case 'class':
            $query = $db->prepare('SELECT class_name as name FROM chart_class WHERE cid = ?');
            break;
    }
    $query->execute([$identifier]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result['name'];
}

// Get parameters from URL
$identifier = ($_GET['id'])  ? $_GET['id'] : null;
$level = ($_GET['level']) ? $_GET['level'] : 'account'; // account, group, or class
$start_date = ($_GET['start']) ? $_GET['start'] : null;
$end_date = ($_GET['end']) ? $_GET['end'] : null;

if ($identifier && $start_date && $end_date) {
    // Get all relevant accounts
    $accounts = getAccountsByLevel($db, $identifier, $level);
    $level_name = getLevelName($db, $identifier, $level);

    // Prepare ledger query once
    $entries_query = $db->prepare('
        SELECT 
            cl.*,
            ca.account_name,
            ca.account_code
        FROM chart_ledger cl
        JOIN chart_accounts ca ON cl.account_no = ca.account_code
        WHERE cl.account_no = ? 
        AND cl.patient_stt_status!=1
        AND DATE(cl.date_entry2) BETWEEN ? AND ?
        ORDER BY cl.date_entry2 ASC, cl.sn ASC
    ');

    // Initialize summary arrays
    $total_debits = 0;
    $total_credits = 0;
    $account_balances = [];


    $the_stetment = 1;
}
?>

<body class="fixed-navigation">
    <div id="wrapper">
        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg sidebar-content">

            <?php include '../../inc/nav_header.php'; ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">

                            <h5>Accounting Dashboard</h5>


                        </div>
                        <div class="ibox-content">

                            <?php if (isset($the_stetment)) { ?>
                                <div id="ledger_entries">
                                    <h1>Ledger Details - <?php echo ucfirst($level); ?></h1>
                                    <h3><?php echo ucfirst($level); ?> - <?php echo htmlspecialchars($level_name); ?></h3>
                                    <h4>For the period <?php echo date('d M Y', strtotime($start_date)); ?> to
                                        <?php echo date('d M Y', strtotime($end_date)); ?></h4>

                                    <?php foreach ($accounts as $account) {
                                        // Get entries for this account
                                        $entries_query->execute([$account['account_code'], $start_date, $end_date]);
                                        $entries = $entries_query->fetchAll(PDO::FETCH_ASSOC);

                                        if (empty($entries)) continue; // Skip accounts with no entries

                                        $running_balance = 0;
                                        $account_debits = 0;
                                        $account_credits = 0;
                                    ?>

                                        <div class="account-section mb-4">
                                            <h5>[<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?></h5>
                                            <style>
                                                .table-responsive {
                                                    width: 100%;
                                                    overflow-x: auto;
                                                }

                                                .table {
                                                    width: 100%;
                                                    table-layout: fixed;
                                                }

                                                .table th,
                                                .table td {
                                                    word-wrap: break-word;
                                                    white-space: normal;
                                                }

                                                .text-right {
                                                    text-align: right;
                                                }
                                            </style>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 8%;">Date</th>
                                                            <th style="width: 12%;">Reference ID</th>
                                                            <th style="width: 20%;">Description</th>
                                                            <th class="text-right" style="width: 12%;">Debit</th>
                                                            <th class="text-right" style="width: 12%;">Credit</th>
                                                            <th class="text-right" style="width: 12%;">Running Balance</th>
                                                            <th style="width: 13%;">Prepared By</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($entries as $entry) {
                                                            if ($entry['transc_type'] == 'DEBIT') {
                                                                $running_balance += $entry['dr_amt'];
                                                                $account_debits += $entry['dr_amt'];
                                                            } else {
                                                                $running_balance -= $entry['cr_amt'];
                                                                $account_credits += $entry['cr_amt'];
                                                            }
                                                        ?>
                                                            <tr>
                                                                <td><?php echo date('d M Y', strtotime($entry['date_entry2'])); ?></td>
                                                                <td><?php echo htmlspecialchars($entry['lg_ref_no']); ?></td>
                                                                <td>
                                                                    <?php if ($entry['hospital_no'] || $entry['insurance_no']): ?>
                                                                        <b>Party ID</b>
                                                                        <br>
                                                                        <?php
                                                                        echo $entry['hospital_no'] ? 'Patient: ' . htmlspecialchars($entry['hospital_no']) . '<br>' : '';
                                                                        echo $entry['insurance_no'] ? 'Insurance Or Supplier ID: ' . htmlspecialchars($entry['insurance_no']) : '';
                                                                        ?>
                                                                        <br>
                                                                        <br>
                                                                    <?php endif ?>
                                                                    <b>Narration:</b>
                                                                    <?php
                                                                    echo htmlspecialchars($entry['ref_value']);
                                                                    if ($entry['item_services']) {
                                                                        echo '<br><small>' . htmlspecialchars($entry['item_services']) . '</small>';
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td class="text-right">
                                                                    <?php if ($entry['dr_amt'] > 0) {
                                                                        echo format_accounting($entry['dr_amt']);
                                                                    } else {
                                                                        echo 0;
                                                                    } ?>
                                                                </td>
                                                                <td class="text-right">
                                                                    <?php if ($entry['cr_amt'] > 0) {
                                                                        echo format_accounting($entry['cr_amt']);
                                                                    } else {
                                                                        echo 0;
                                                                    } ?>
                                                                </td>
                                                                <td class="text-right">
                                                                    <?php
                                                                    echo format_accounting(abs($running_balance)) .
                                                                        ($running_balance >= 0 ? ' DR' : ' CR');
                                                                    ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($entry['prepared_by']); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3" class="text-right">Account Totals:</th>
                                                            <th class="text-right"><?php echo format_accounting($account_debits); ?></th>
                                                            <th class="text-right"><?php echo format_accounting($account_credits); ?></th>
                                                            <th class="text-right">
                                                                <?php
                                                                echo format_accounting(abs($running_balance)) .
                                                                    ($running_balance >= 0 ? ' DR' : ' CR');
                                                                ?>
                                                            </th>
                                                            <th></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                        </div>
                                    <?php
                                        $total_debits += $account_debits;
                                        $total_credits += $account_credits;
                                        $account_balances[$account['account_code']] = $running_balance;
                                    } ?>

                                    <!-- Overall Summary -->
                                    <div class="summary-section mt-4">
                                        <h4>Summary for <?php echo ucfirst($level); ?> - <?php echo htmlspecialchars($level_name); ?></h4>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Total Debits</th>
                                                    <th>Total Credits</th>
                                                    <th>Net Position</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-right"><?php echo format_accounting($total_debits); ?></td>
                                                    <td class="text-right"><?php echo format_accounting($total_credits); ?></td>
                                                    <td class="text-right">
                                                        <?php
                                                        $net_position = $total_debits - $total_credits;
                                                        echo format_accounting(abs($net_position)) .
                                                            ($net_position >= 0 ? ' DR' : ' CR');
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <button class="btn btn-success m-1" onclick="printDiv('ledger_entries')">
                                        <i class="fa fa-print"></i>&nbsp; Print Entries
                                    </button>
                                    &nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <a href="trial_balance.php?start=<?php echo urlencode($_GET['start']); ?>&end=<?php echo urlencode($_GET['end']); ?>" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i>&nbsp; Back to Trial Balance
                                    </a>
                                </div>

                                <!-- <button class="btn btn-success" onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Entries</i></button> -->
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>


            <?php include '../../inc/footer.php'; ?>
        </div>


        <?php include('../modal_lock.php'); ?>
        <?php include '/inc/footer_scripts.php'; ?>


        <script>
            <?php
            if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
                timeOut: 5000
            })
            <?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
                timeOut: 5000
            })
            <?php } ?>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>

        <script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
        <script>
            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var popupWindow = window.open('', '_blank', 'width=600,height=600');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>' + document.title + '</title>');
                // Reference the external stylesheet from the main page
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
                popupWindow.document.write('</head><body>');
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                popupWindow.print();
            }
        </script>

        <script src="../js/idle.js"></script>
</body>

</html>