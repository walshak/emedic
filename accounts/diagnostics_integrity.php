<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');
include_once('inc/functions.php');

// Get chart classes for account hierarchy
$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');
$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

// Get date filters
$active_fy = get_active_year();
$default_start = $active_fy ? $active_fy['begin'] : date('Y-01-01');
$default_end = $active_fy ? $active_fy['end'] : date('Y-12-31');
$default_fy_id = $active_fy ? $active_fy['id'] : null;

$fiscal_year_id = isset($_GET['fiscal_year']) && $_GET['fiscal_year'] != '' ? $_GET['fiscal_year'] : $default_fy_id;
$start_date = isset($_GET['start']) ? $_GET['start'] : (isset($_GET['start_date']) ? $_GET['start_date'] : $default_start);
$end_date = isset($_GET['end']) ? $_GET['end'] : (isset($_GET['end_date']) ? $_GET['end_date'] : $default_end);
$check_type = isset($_GET['check_type']) ? $_GET['check_type'] : 'all';
$account_filter = isset($_GET['account_filter']) ? $_GET['account_filter'] : '';

// Helper function to build account filter condition
function buildAccountFilter($account_filter, $base_params)
{
    $condition = "";
    $params = $base_params;
    if ($account_filter) {
        $condition = "AND account_no = :account_filter";
        $params['account_filter'] = $account_filter;
    }
    return ['condition' => $condition, 'params' => $params];
}
?>

<body class="fixed-navigation">
    <div id="wrapper">

        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg sidebar-content">

            <?php include '../../inc/nav_header.php'; ?>
            <div class="">

                <div class="row">
                    <div class="col-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Ledger Integrity Check</h5>
                                <div class="ibox-tools">
                                    <a href="diagnostics.php" class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i> Back to Diagnostics</a>
                                </div>
                            </div>
                            <div class="ibox-content">

                                <!-- Filter Form -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <?php echo render_fiscal_year_filter($fiscal_year_id); ?>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="start" class="control-label"><strong>Start Date</strong></label>
                                                        <input type="date" id="start" name="start" class="form-control" value="<?php echo $start_date; ?>" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="end" class="control-label"><strong>End Date</strong></label>
                                                        <input type="date" id="end" name="end" class="form-control" value="<?php echo $end_date; ?>" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="check_type" class="control-label"><strong>Check Type</strong></label>
                                                        <select name="check_type" id="check_type" class="form-control">
                                                            <option value="all" <?php echo ($check_type == 'all') ? 'selected' : ''; ?>>All Checks</option>
                                                            <option value="duplicates" <?php echo ($check_type == 'duplicates') ? 'selected' : ''; ?>>Duplicate Entries</option>
                                                            <option value="orphaned" <?php echo ($check_type == 'orphaned') ? 'selected' : ''; ?>>Orphaned Records</option>
                                                            <option value="invalid_accounts" <?php echo ($check_type == 'invalid_accounts') ? 'selected' : ''; ?>>Invalid Accounts</option>
                                                            <option value="missing_refs" <?php echo ($check_type == 'missing_refs') ? 'selected' : ''; ?>>Missing References</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="account_filter" class="control-label"><strong>Account</strong></label>
                                                        <select name="account_filter" id="account_filter" class="form-control select2">
                                                            <option value="">-- All Accounts --</option>
                                                            <?php foreach ($classes as $class) { ?>
                                                                <optgroup label="<?php echo $class['class_name']; ?>">
                                                                    <?php
                                                                    $cid = $class['cid'];
                                                                    $groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ? AND inactive = ?');
                                                                    $groups->execute([$cid, 0]);
                                                                    $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <?php foreach ($groups as $group) { ?>
                                                                <optgroup label="<?php echo $group['name']; ?>">
                                                                    <?php
                                                                        $group_id = $group['id'];
                                                                        $accounts = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ? AND inactive = ?');
                                                                        $accounts->execute([$group_id, 0]);
                                                                        $accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <?php foreach ($accounts as $account) { ?>
                                                                        <option value="<?php echo $account['account_code']; ?>" <?php echo ($account_filter == $account['account_code']) ? 'selected' : ''; ?>>
                                                                            [<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </optgroup>
                                                            <?php } ?>
                                                            </optgroup>
                                                        <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-check"></i> <strong>RUN CHECK</strong>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Results Section -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                        try {
                                            echo "<h3>Ledger Integrity Check Results</h3>";
                                            echo "<p><strong>Date Range:</strong> $start_date to $end_date | <strong>Check Type:</strong> " . ucfirst(str_replace('_', ' ', $check_type)) . "</p>";
                                            if ($account_filter) {
                                                // Get account name for display
                                                $account_info = $db->prepare("SELECT account_name FROM chart_accounts WHERE account_code = ?");
                                                $account_info->execute([$account_filter]);
                                                $account_data = $account_info->fetch(PDO::FETCH_ASSOC);
                                                $account_name = $account_data ? $account_data['account_name'] : 'Unknown Account';
                                                echo "<p><strong>Account Filter:</strong> [$account_filter] $account_name</p>";
                                            }

                                            $issues_found = false;

                                            // Check for duplicate entries
                                            if ($check_type == 'all' || $check_type == 'duplicates') {
                                                echo "<div class='panel panel-warning'>";
                                                echo "<div class='panel-heading'><h4><i class='fa fa-copy'></i> Duplicate Entry Check</h4></div>";
                                                echo "<div class='panel-body'>";

                                                $filter = buildAccountFilter($account_filter, ['start' => $start_date, 'end' => $end_date]);

                                                $duplicates = $db->prepare("
													SELECT 
														c.account_no, c.transc_type, c.dr_amt, c.cr_amt, c.date_entry2, c.lg_ref_no, c.invoice_no,
														COALESCE(a.account_name, 'Unknown Account') AS account_name,
														COUNT(*) as duplicate_count
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end {$filter['condition']}
													GROUP BY c.account_no, c.transc_type, c.dr_amt, c.cr_amt, c.date_entry2, c.lg_ref_no, c.invoice_no, a.account_name
													HAVING COUNT(*) > 1
													ORDER BY duplicate_count DESC
												");
                                                $duplicates->execute($filter['params']);
                                                $duplicate_results = $duplicates->fetchAll(PDO::FETCH_ASSOC);

                                                if (!$duplicate_results) {
                                                    echo "<div class='alert alert-success'><i class='fa fa-check'></i> No duplicate entries found.</div>";
                                                } else {
                                                    $issues_found = true;
                                                    echo "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i> Found " . count($duplicate_results) . " potential duplicate entry group(s).</div>";

                                                    echo "<div class='table-responsive'>";
                                                    echo "<table class='table table-striped table-bordered table-hover table-condensed'>";
                                                    echo "<thead><tr><th>Account</th><th>Type</th><th>Debit</th><th>Credit</th><th>Date</th><th>Reference</th><th>Invoice</th><th>Count</th></tr></thead>";
                                                    echo "<tbody>";
                                                    foreach ($duplicate_results as $dup) {
                                                        echo "<tr>";
                                                        echo "<td>[{$dup['account_no']}] {$dup['account_name']}</td>";
                                                        echo "<td>{$dup['transc_type']}</td>";
                                                        echo "<td>" . number_format($dup['dr_amt'], 2) . "</td>";
                                                        echo "<td>" . number_format($dup['cr_amt'], 2) . "</td>";
                                                        echo "<td>{$dup['date_entry2']}</td>";
                                                        echo "<td>{$dup['lg_ref_no']}</td>";
                                                        echo "<td>{$dup['invoice_no']}</td>";
                                                        echo "<td class='text-danger'><strong>{$dup['duplicate_count']}</strong></td>";
                                                        echo "</tr>";
                                                    }
                                                    echo "</tbody></table>";
                                                    echo "</div>"; // Close table-responsive
                                                }
                                                echo "</div></div>";
                                            }

                                            // Check for invalid account codes
                                            if ($check_type == 'all' || $check_type == 'invalid_accounts') {
                                                echo "<div class='panel panel-danger'>";
                                                echo "<div class='panel-heading'><h4><i class='fa fa-exclamation-circle'></i> Invalid Account Check</h4></div>";
                                                echo "<div class='panel-body'>";

                                                $filter = buildAccountFilter($account_filter, ['start' => $start_date, 'end' => $end_date]);

                                                $invalid_accounts = $db->prepare("
													SELECT 
														c.account_no,
														COUNT(*) as usage_count,
														SUM(c.dr_amt) as total_debit,
														SUM(c.cr_amt) as total_credit
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end
													AND a.account_code IS NULL
													AND c.account_no IS NOT NULL
													AND c.account_no != ''
													{$filter['condition']}
													GROUP BY c.account_no
													ORDER BY usage_count DESC
												");
                                                $invalid_accounts->execute($filter['params']);
                                                $invalid_results = $invalid_accounts->fetchAll(PDO::FETCH_ASSOC);

                                                if (!$invalid_results) {
                                                    echo "<div class='alert alert-success'><i class='fa fa-check'></i> All account codes are valid.</div>";
                                                } else {
                                                    $issues_found = true;
                                                    echo "<div class='alert alert-danger'><i class='fa fa-exclamation-circle'></i> Found " . count($invalid_results) . " invalid account code(s).</div>";

                                                    echo "<div class='table-responsive'>";
                                                    echo "<table class='table table-striped table-bordered table-hover table-condensed'>";
                                                    echo "<thead><tr><th>Invalid Account Code</th><th>Usage Count</th><th>Total Debit</th><th>Total Credit</th></tr></thead>";
                                                    echo "<tbody>";
                                                    foreach ($invalid_results as $inv) {
                                                        echo "<tr>";
                                                        echo "<td class='text-danger'><strong>{$inv['account_no']}</strong></td>";
                                                        echo "<td>{$inv['usage_count']}</td>";
                                                        echo "<td>" . number_format($inv['total_debit'], 2) . "</td>";
                                                        echo "<td>" . number_format($inv['total_credit'], 2) . "</td>";
                                                        echo "</tr>";
                                                    }
                                                    echo "</tbody></table>";
                                                    echo "</div>"; // Close table-responsive
                                                }
                                                echo "</div></div>";
                                            }

                                            // Check for missing reference numbers
                                            if ($check_type == 'all' || $check_type == 'missing_refs') {
                                                echo "<div class='panel panel-info'>";
                                                echo "<div class='panel-heading'><h4><i class='fa fa-question-circle'></i> Missing Reference Check</h4></div>";
                                                echo "<div class='panel-body'>";

                                                $filter = buildAccountFilter($account_filter, ['start' => $start_date, 'end' => $end_date]);

                                                $missing_refs = $db->prepare("
													SELECT 
														c.sn, c.account_no, c.transc_type, c.dr_amt, c.cr_amt, c.date_entry2, c.invoice_no, c.prepared_by,
														COALESCE(a.account_name, 'Unknown Account') AS account_name
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end
													AND (c.lg_ref_no IS NULL OR c.lg_ref_no = '' OR c.lg_ref_no = '0')
													{$filter['condition']}
													ORDER BY c.date_entry2 DESC
													LIMIT 100
												");
                                                $missing_refs->execute($filter['params']);
                                                $missing_results = $missing_refs->fetchAll(PDO::FETCH_ASSOC);

                                                if (!$missing_results) {
                                                    echo "<div class='alert alert-success'><i class='fa fa-check'></i> All entries have reference numbers.</div>";
                                                } else {
                                                    $issues_found = true;
                                                    echo "<div class='alert alert-info'><i class='fa fa-info-circle'></i> Found " . count($missing_results) . " entries without reference numbers (showing first 100).</div>";

                                                    echo "<div class='table-responsive'>";
                                                    echo "<table class='table table-striped table-bordered table-hover table-condensed'>";
                                                    echo "<thead><tr><th>SN</th><th>Account</th><th>Type</th><th>Debit</th><th>Credit</th><th>Date</th><th>Invoice</th><th>Prepared By</th></tr></thead>";
                                                    echo "<tbody>";
                                                    foreach ($missing_results as $miss) {
                                                        echo "<tr>";
                                                        echo "<td>{$miss['sn']}</td>";
                                                        echo "<td>[{$miss['account_no']}] {$miss['account_name']}</td>";
                                                        echo "<td>{$miss['transc_type']}</td>";
                                                        echo "<td>" . number_format($miss['dr_amt'], 2) . "</td>";
                                                        echo "<td>" . number_format($miss['cr_amt'], 2) . "</td>";
                                                        echo "<td>{$miss['date_entry2']}</td>";
                                                        echo "<td>{$miss['invoice_no']}</td>";
                                                        echo "<td>{$miss['prepared_by']}</td>";
                                                        echo "</tr>";
                                                    }
                                                    echo "</tbody></table>";
                                                    echo "</div>"; // Close table-responsive
                                                }
                                                echo "</div></div>";
                                            }

                                            // Check for orphaned records (single entries in a reference group)
                                            if ($check_type == 'all' || $check_type == 'orphaned') {
                                                echo "<div class='panel panel-warning'>";
                                                echo "<div class='panel-heading'><h4><i class='fa fa-unlink'></i> Orphaned Records Check</h4></div>";
                                                echo "<div class='panel-body'>";

                                                $filter = buildAccountFilter($account_filter, ['start' => $start_date, 'end' => $end_date]);

                                                $orphaned = $db->prepare("
													SELECT 
														lg_ref_no,
														COUNT(*) as entry_count,
														SUM(dr_amt) as total_debit,
														SUM(cr_amt) as total_credit,
														MAX(date_entry2) as latest_date
													FROM chart_ledger
													WHERE patient_stt_status!=1 AND date_entry2 BETWEEN :start AND :end
													AND lg_ref_no IS NOT NULL 
													AND lg_ref_no != ''
													AND lg_ref_no != '0'
													{$filter['condition']}
													GROUP BY lg_ref_no
													HAVING COUNT(*) = 1
													ORDER BY latest_date DESC
													LIMIT 50
												");
                                                $orphaned->execute($filter['params']);
                                                $orphaned_results = $orphaned->fetchAll(PDO::FETCH_ASSOC);

                                                if (!$orphaned_results) {
                                                    echo "<div class='alert alert-success'><i class='fa fa-check'></i> No orphaned records found.</div>";
                                                } else {
                                                    $issues_found = true;
                                                    echo "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i> Found " . count($orphaned_results) . " orphaned reference(s) (single entries) - showing first 50.</div>";

                                                    echo "<div class='table-responsive'>";
                                                    echo "<table class='table table-striped table-bordered table-hover table-condensed'>";
                                                    echo "<thead><tr><th>Reference</th><th>Entries</th><th>Total Debit</th><th>Total Credit</th><th>Latest Date</th></tr></thead>";
                                                    echo "<tbody>";
                                                    foreach ($orphaned_results as $orph) {
                                                        echo "<tr>";
                                                        echo "<td>{$orph['lg_ref_no']}</td>";
                                                        echo "<td class='text-warning'><strong>{$orph['entry_count']}</strong></td>";
                                                        echo "<td>" . number_format($orph['total_debit'], 2) . "</td>";
                                                        echo "<td>" . number_format($orph['total_credit'], 2) . "</td>";
                                                        echo "<td>{$orph['latest_date']}</td>";
                                                        echo "</tr>";
                                                    }
                                                    echo "</tbody></table>";
                                                    echo "</div>"; // Close table-responsive
                                                }
                                                echo "</div></div>";
                                            }

                                            // Overall Summary
                                            echo "<div class='panel panel-primary'>";
                                            echo "<div class='panel-heading'><h4><i class='fa fa-summary'></i> Integrity Check Summary</h4></div>";
                                            echo "<div class='panel-body'>";

                                            if (!$issues_found) {
                                                echo "<div class='alert alert-success'>";
                                                echo "<h4><i class='fa fa-check-circle'></i> Ledger Integrity Check Passed!</h4>";
                                                echo "<p>No integrity issues were found in the specified date range.</p>";
                                                echo "</div>";
                                            } else {
                                                echo "<div class='alert alert-warning'>";
                                                echo "<h4><i class='fa fa-exclamation-triangle'></i> Integrity Issues Found</h4>";
                                                echo "<p>Please review the issues identified above and take appropriate corrective action.</p>";
                                                echo "</div>";
                                            }

                                            // Quick stats
                                            $filter = buildAccountFilter($account_filter, ['start' => $start_date, 'end' => $end_date]);

                                            $stats = $db->prepare("
												SELECT 
													COUNT(*) as total_entries,
													COUNT(DISTINCT lg_ref_no) as unique_refs,
													COUNT(DISTINCT account_no) as unique_accounts,
													SUM(dr_amt) as total_debits,
													SUM(cr_amt) as total_credits,
													MIN(date_entry2) as earliest_date,
													MAX(date_entry2) as latest_date
												FROM chart_ledger
												WHERE patient_stt_status!=1 AND date_entry2 BETWEEN :start AND :end {$filter['condition']}
											");
                                            $stats->execute($filter['params']);
                                            $stats_data = $stats->fetch(PDO::FETCH_ASSOC);

                                            echo "<div class='row'>";
                                            echo "<div class='col-md-3'><strong>Total Entries:</strong><br>" . number_format($stats_data['total_entries']) . "</div>";
                                            echo "<div class='col-md-3'><strong>Unique References:</strong><br>" . number_format($stats_data['unique_refs']) . "</div>";
                                            echo "<div class='col-md-3'><strong>Unique Accounts:</strong><br>" . number_format($stats_data['unique_accounts']) . "</div>";
                                            echo "<div class='col-md-3'><strong>Date Range:</strong><br>{$stats_data['earliest_date']} to {$stats_data['latest_date']}</div>";
                                            echo "</div>";

                                            echo "</div></div>";
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'><i class='fa fa-exclamation-circle'></i> Database Error: " . $e->getMessage() . "</div>";
                                        }
                                        ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <?php include '../../inc/footer.php'; ?>

            </div>
        </div>

        <?php include('../modal_lock.php'); ?>
        <?php include("../inc/footer_scripts.php"); ?>

        <script>
            $(document).ready(function() {
                // Initialize Select2 for account dropdown
                $('.select2').select2({
                    placeholder: "-- All Accounts --",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>

</body>

</html>