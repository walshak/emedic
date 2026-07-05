<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

// Get chart classes for account hierarchy
$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');
$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

// Get date filters from form submission
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$selected_month = isset($_GET['selected_month']) ? $_GET['selected_month'] : date('Y-m');
$account_filter = isset($_GET['account_filter']) ? $_GET['account_filter'] : '';
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
                                <h5>Unbalanced Ledger Transactions Diagnostic</h5>
                                <div class="ibox-tools">
                                    <a href="diagnostics.php" class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i> Back to Diagnostics</a>
                                </div>
                            </div>
                            <div class="ibox-content">

                                <!-- Date Filter Form -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <form method="GET" class="form-inline" style="margin-bottom: 20px;">
                                            <div class="form-group">
                                                <label for="start_date">Start Date:</label>
                                                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                                            </div>
                                            <div class="form-group" style="margin-left: 10px;">
                                                <label for="end_date">End Date:</label>
                                                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                                            </div>
                                            <div class="form-group" style="margin-left: 10px;">
                                                <label for="selected_month">Quick Month:</label>
                                                <input type="month" id="selected_month" name="selected_month" class="form-control" value="<?php echo $selected_month; ?>">
                                            </div>
                                            <div class="form-group" style="margin-left: 10px;">
                                                <label for="account_filter">Account:</label>
                                                <select name="account_filter" id="account_filter" class="form-control select2" style="width: 300px;">
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
                                            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">
                                                <i class="fa fa-search"></i> Filter
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Results Section -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                        try {
                                            echo "<h3>Unbalanced Ledger Transactions</h3>";
                                            echo "<p><strong>Date Range:</strong> $start_date to $end_date</p>";

                                            // Build date condition based on user input
                                            if (!empty($selected_month)) {
                                                $date_condition = "DATE_FORMAT(date_entry2, '%Y-%m') = :month";
                                                $date_params = ['month' => $selected_month];
                                            } else {
                                                $date_condition = "date_entry2 BETWEEN :start_date AND :end_date";
                                                $date_params = ['start_date' => $start_date, 'end_date' => $end_date];
                                            }

                                            // Add account filter
                                            $account_condition = "";
                                            if ($account_filter) {
                                                $account_condition = "AND account_no = :account_filter";
                                                $date_params['account_filter'] = $account_filter;
                                            }

                                            // Query for unbalanced transactions
                                            $stmt = $db->prepare("
												SELECT 
													lg_ref_no,
													SUM(dr_amt) AS total_debit,
													SUM(cr_amt) AS total_credit,
													SUM(dr_amt) - SUM(cr_amt) AS difference,
													MAX(date_entry2) AS latest_date,
													COUNT(*) AS entry_count
												FROM chart_ledger
												WHERE $date_condition $account_condition
                                                AND patient_stt_status!=1 
												GROUP BY lg_ref_no
												HAVING ABS(SUM(dr_amt) - SUM(cr_amt)) > 0.01
												ORDER BY ABS(SUM(dr_amt) - SUM(cr_amt)) DESC
											");
                                            $stmt->execute($date_params);
                                            $bad_refs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // Display filter information
                                            echo "<h3>Unbalanced Transactions Report</h3>";
                                            if (!empty($selected_month)) {
                                                echo "<p><strong>Filter:</strong> Month $selected_month</p>";
                                            } else {
                                                echo "<p><strong>Date Range:</strong> $start_date to $end_date</p>";
                                            }
                                            if ($account_filter) {
                                                // Get account name for display
                                                $account_info = $db->prepare("SELECT account_name FROM chart_accounts WHERE account_code = ?");
                                                $account_info->execute([$account_filter]);
                                                $account_data = $account_info->fetch(PDO::FETCH_ASSOC);
                                                $account_name = $account_data ? $account_data['account_name'] : 'Unknown Account';
                                                echo "<p><strong>Account Filter:</strong> [$account_filter] $account_name</p>";
                                            }

                                            if (!$bad_refs) {
                                                echo "<div class='alert alert-success'><i class='fa fa-check'></i> All transactions are balanced for this period ✅</div>";
                                            } else {
                                                echo "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i> Found " . count($bad_refs) . " unbalanced transaction reference(s)</div>";

                                                foreach ($bad_refs as $ref) {
                                                    echo "<div class='panel panel-danger'>";
                                                    echo "<div class='panel-heading'>";
                                                    echo "<h4>Transaction Reference: {$ref['lg_ref_no']}</h4>";
                                                    echo "<p>Entries: {$ref['entry_count']} | Latest Date: {$ref['latest_date']}</p>";
                                                    echo "</div>";
                                                    echo "<div class='panel-body'>";

                                                    echo "<div class='row'>";
                                                    echo "<div class='col-md-4'>";
                                                    echo "<strong>Total Debit:</strong> " . number_format($ref['total_debit'], 2);
                                                    echo "</div>";
                                                    echo "<div class='col-md-4'>";
                                                    echo "<strong>Total Credit:</strong> " . number_format($ref['total_credit'], 2);
                                                    echo "</div>";
                                                    echo "<div class='col-md-4'>";
                                                    echo "<strong>Difference:</strong> <span style='color:red; font-weight:bold;'>" . number_format($ref['difference'], 2) . "</span>";
                                                    echo "</div>";
                                                    echo "</div>";

                                                    // Show ledger entries for this reference
                                                    $details = $db->prepare("
														SELECT 
															c.sn, c.account_no, c.transc_type, c.dr_amt, c.cr_amt, c.prepared_by,
															c.item_services, c.date_entry2, c.invoice_no,
															COALESCE(a.account_name, 'Unknown Account') AS account_name
														FROM chart_ledger c
														LEFT JOIN chart_accounts a ON c.account_no = a.account_code
														WHERE c.patient_stt_status!=1 AND c.lg_ref_no = :ref
														ORDER BY c.sn
													");
                                                    $details->execute(['ref' => $ref['lg_ref_no']]);
                                                    $rows = $details->fetchAll(PDO::FETCH_ASSOC);

                                                    echo "<div class='table-responsive'>";
                                                    echo "<table class='table table-striped table-bordered table-hover'>";
                                                    echo "<thead>";
                                                    echo "<tr>";
                                                    echo "<th>SN</th>";
                                                    echo "<th>Account</th>";
                                                    echo "<th>Type</th>";
                                                    echo "<th>Debit</th>";
                                                    echo "<th>Credit</th>";
                                                    echo "<th>Date</th>";
                                                    echo "<th>Invoice</th>";
                                                    echo "<th>Prepared By</th>";
                                                    echo "<th>Description</th>";
                                                    echo "</tr>";
                                                    echo "</thead>";
                                                    echo "<tbody>";
                                                    foreach ($rows as $r) {
                                                        echo "<tr>";
                                                        echo "<td>{$r['sn']}</td>";
                                                        echo "<td>[{$r['account_no']}] {$r['account_name']}</td>";
                                                        echo "<td>{$r['transc_type']}</td>";
                                                        echo "<td>" . number_format($r['dr_amt'], 2) . "</td>";
                                                        echo "<td>" . number_format($r['cr_amt'], 2) . "</td>";
                                                        echo "<td>{$r['date_entry2']}</td>";
                                                        echo "<td>{$r['invoice_no']}</td>";
                                                        echo "<td>{$r['prepared_by']}</td>";
                                                        echo "<td>{$r['item_services']}</td>";
                                                        echo "</tr>";
                                                    }
                                                    echo "</tbody>";
                                                    echo "</table>";
                                                    echo "</div>"; // Close table-responsive

                                                    // Suggest adjustment
                                                    $adjustment = $ref['difference'];
                                                    $latestDate = $ref['latest_date'];
                                                    $amount = number_format(abs($adjustment), 2, '.', '');

                                                    echo "<div class='alert alert-info'>";
                                                    echo "<h5><i class='fa fa-lightbulb-o'></i> Suggested Fix:</h5>";
                                                    if ($adjustment > 0) {
                                                        echo "<p>Add <strong>Credit {$amount}</strong> to balance this transaction</p>";
                                                        $sql = "INSERT INTO chart_ledger (hospital_no, insurance_no, account_no, transc_type, dr_amt, cr_amt, date_entry, date_entry2, prepared_by, lg_ref_no, fiscal_year, item_services, invoice_no, can_delete)
																VALUES (NULL, NULL, 'ADJUST_ACC', 'CREDIT', 0, {$amount}, NOW(), '{$latestDate}', 'System Adjustment', '{$ref['lg_ref_no']}', 2, 'Adjustment to balance entry', 0, 0);";
                                                    } else {
                                                        echo "<p>Add <strong>Debit {$amount}</strong> to balance this transaction</p>";
                                                        $sql = "INSERT INTO chart_ledger (hospital_no, insurance_no, account_no, transc_type, dr_amt, cr_amt, date_entry, date_entry2, prepared_by, lg_ref_no, fiscal_year, item_services, invoice_no, can_delete)
																VALUES (NULL, NULL, 'ADJUST_ACC', 'DEBIT', {$amount}, 0, NOW(), '{$latestDate}', 'System Adjustment', '{$ref['lg_ref_no']}', 2, 'Adjustment to balance entry', 0, 0);";
                                                    }
                                                    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>$sql</pre>";
                                                    echo "</div>";

                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                            }
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
            // Auto-populate start and end dates when month is selected
            document.getElementById('selected_month').addEventListener('change', function() {
                const monthValue = this.value;
                if (monthValue) {
                    const year = monthValue.split('-')[0];
                    const month = monthValue.split('-')[1];
                    const startDate = `${year}-${month}-01`;
                    const endDate = new Date(year, month, 0).toISOString().split('T')[0];

                    document.getElementById('start_date').value = startDate;
                    document.getElementById('end_date').value = endDate;
                }
            });

            // Initialize Select2 for account dropdown
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "-- All Accounts --",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>

</body>

</html>