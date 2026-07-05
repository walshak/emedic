<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');
include_once('inc/functions.php');

// Get chart classes for account hierarchy
$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');
$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

// Get parameters
$active_fy = get_active_year();
$default_start = $active_fy ? $active_fy['begin'] : date('Y-01-01');
$default_end = $active_fy ? $active_fy['end'] : date('Y-12-31');
$default_fy_id = $active_fy ? $active_fy['id'] : null;

$fiscal_year_id = isset($_GET['fiscal_year']) && $_GET['fiscal_year'] != '' ? $_GET['fiscal_year'] : $default_fy_id;
$start_date = isset($_GET['start']) ? $_GET['start'] : (isset($_GET['start_date']) ? $_GET['start_date'] : $default_start);
$end_date = isset($_GET['end']) ? $_GET['end'] : (isset($_GET['end_date']) ? $_GET['end_date'] : $default_end);
$account_filter = isset($_GET['account_filter']) ? $_GET['account_filter'] : '';
$reconcile_type = isset($_GET['reconcile_type']) ? $_GET['reconcile_type'] : 'summary';
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
								<h5>Account Reconciliation</h5>
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
                                                        <label for="reconcile_type" class="control-label"><strong>Type</strong></label>
                                                        <select name="reconcile_type" id="reconcile_type" class="form-control">
                                                            <option value="summary" <?php echo ($reconcile_type == 'summary') ? 'selected' : ''; ?>>Summary</option>
                                                            <option value="detailed" <?php echo ($reconcile_type == 'detailed') ? 'selected' : ''; ?>>Detailed</option>
                                                            <option value="balance_analysis" <?php echo ($reconcile_type == 'balance_analysis') ? 'selected' : ''; ?>>Balance Analysis</option>
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
                                                        <i class="fa fa-calculator"></i> <strong>RECONCILE</strong>
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
											echo "<h3>Account Reconciliation Report</h3>";
											echo "<p><strong>Date Range:</strong> $start_date to $end_date</p>";
											if ($account_filter) {
												// Get account name for display
												$account_info = $db->prepare("SELECT account_name FROM chart_accounts WHERE account_code = ?");
												$account_info->execute([$account_filter]);
												$account_data = $account_info->fetch(PDO::FETCH_ASSOC);
												$account_name = $account_data ? $account_data['account_name'] : 'Unknown Account';
												echo "<p><strong>Account Filter:</strong> [$account_filter] $account_name</p>";
											}

											// Build account filter condition
											$account_condition = "";
											$account_params = ['start' => $start_date, 'end' => $end_date];
											if ($account_filter) {
												$account_condition = "AND c.account_no = :account_filter";
												$account_params['account_filter'] = $account_filter;
											}

											if ($reconcile_type == 'summary') {
												// Summary reconciliation
												echo "<div class='panel panel-primary'>";
												echo "<div class='panel-heading'><h4><i class='fa fa-list'></i> Account Summary Reconciliation</h4></div>";
												echo "<div class='panel-body'>";

												$summary = $db->prepare("
													SELECT 
														c.account_no,
														COALESCE(a.account_name, 'Unknown Account') AS account_name,
														COALESCE(cl.class_name, 'Unknown') AS account_type,
														SUM(c.dr_amt) AS total_debit,
														SUM(c.cr_amt) AS total_credit,
														SUM(c.dr_amt) - SUM(c.cr_amt) AS net_balance,
														COUNT(*) AS transaction_count,
														MIN(c.date_entry2) AS first_transaction,
														MAX(c.date_entry2) AS last_transaction
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													LEFT JOIN chart_groups g ON g.id = a.account_group
													LEFT JOIN chart_class cl ON cl.cid = g.class_id
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end
													$account_condition
													GROUP BY c.account_no, a.account_name, cl.class_name
													ORDER BY ABS(SUM(c.dr_amt) - SUM(c.cr_amt)) DESC, c.account_no
												");
												$summary->execute($account_params);
												$summary_results = $summary->fetchAll(PDO::FETCH_ASSOC);

												if (!$summary_results) {
													echo "<div class='alert alert-info'>No accounts found for the specified criteria.</div>";
												} else {
													echo "<div class='table-responsive'>";
													echo "<table class='table table-striped table-bordered table-hover table-condensed'>";
													echo "<thead>";
													echo "<tr>";
													echo "<th>Account</th>";
													echo "<th>Type</th>";
													echo "<th>Total Debit</th>";
													echo "<th>Total Credit</th>";
													echo "<th>Net Balance</th>";
													echo "<th>Transactions</th>";
													echo "<th>Period</th>";
													echo "</tr>";
													echo "</thead>";
													echo "<tbody>";

													$grand_debit = 0;
													$grand_credit = 0;
													$grand_balance = 0;
													$total_transactions = 0;

													foreach ($summary_results as $account) {
														$grand_debit += $account['total_debit'];
														$grand_credit += $account['total_credit'];
														$grand_balance += $account['net_balance'];
														$total_transactions += $account['transaction_count'];

														$balance_class = '';
														if ($account['net_balance'] > 0) {
															$balance_class = 'text-success';
														} elseif ($account['net_balance'] < 0) {
															$balance_class = 'text-danger';
														}

														echo "<tr>";
														echo "<td><strong>[{$account['account_no']}] {$account['account_name']}</strong></td>";
														echo "<td><span class='label label-info'>{$account['account_type']}</span></td>";
														echo "<td>" . number_format($account['total_debit'], 2) . "</td>";
														echo "<td>" . number_format($account['total_credit'], 2) . "</td>";
														echo "<td class='$balance_class'><strong>" . number_format($account['net_balance'], 2) . "</strong></td>";
														echo "<td>{$account['transaction_count']}</td>";
														echo "<td><small>{$account['first_transaction']} to {$account['last_transaction']}</small></td>";
														echo "</tr>";
													}

													// Grand totals
													echo "<tr class='info'>";
													echo "<td colspan='3'><strong>GRAND TOTALS</strong></td>";
													echo "<td><strong>" . number_format($grand_debit, 2) . "</strong></td>";
													echo "<td><strong>" . number_format($grand_credit, 2) . "</strong></td>";
													echo "<td><strong>" . number_format($grand_balance, 2) . "</strong></td>";
													echo "<td><strong>$total_transactions</strong></td>";
													echo "<td></td>";
													echo "</tr>";

													echo "</tbody>";
													echo "</table>";
													echo "</div>"; // Close table-responsive

													// Balance check
													if (abs($grand_balance) <= 0.01) {
														echo "<div class='alert alert-success'><i class='fa fa-check'></i> <strong>All accounts are reconciled!</strong> Total balance difference: " . number_format($grand_balance, 2) . "</div>";
													} else {
														echo "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i> <strong>Reconciliation discrepancy found!</strong> Total balance difference: " . number_format($grand_balance, 2) . "</div>";
													}
												}

												echo "</div></div>";
											} elseif ($reconcile_type == 'detailed') {
												// Detailed reconciliation
												echo "<div class='panel panel-info'>";
												echo "<div class='panel-heading'><h4><i class='fa fa-list-alt'></i> Detailed Account Reconciliation</h4></div>";
												echo "<div class='panel-body'>";

												$detailed = $db->prepare("
													SELECT 
														c.sn,
														c.account_no,
														COALESCE(a.account_name, 'Unknown Account') AS account_name,
														c.transc_type,
														c.dr_amt,
														c.cr_amt,
														c.date_entry2,
														c.lg_ref_no,
														c.invoice_no,
														c.prepared_by,
														c.item_services
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end
													$account_condition
													ORDER BY c.account_no, c.date_entry2, c.sn
													LIMIT 500
												");
												$detailed->execute($account_params);
												$detailed_results = $detailed->fetchAll(PDO::FETCH_ASSOC);

												if (!$detailed_results) {
													echo "<div class='alert alert-info'>No detailed transactions found for the specified criteria.</div>";
												} else {
													echo "<div class='alert alert-info'>Showing first 500 transactions. Use account filter for more specific results.</div>";

													echo "<div class='table-responsive'>";
													echo "<table class='table table-striped table-bordered table-hover table-condensed'>";
													echo "<thead>";
													echo "<tr>";
													echo "<th>SN</th>";
													echo "<th>Account</th>";
													echo "<th>Type</th>";
													echo "<th>Debit</th>";
													echo "<th>Credit</th>";
													echo "<th>Date</th>";
													echo "<th>Reference</th>";
													echo "<th>Invoice</th>";
													echo "<th>Prepared By</th>";
													echo "<th>Description</th>";
													echo "</tr>";
													echo "</thead>";
													echo "<tbody>";

													$running_balance = 0;
													$current_account = '';

													foreach ($detailed_results as $detail) {
														if ($current_account != $detail['account_no']) {
															if ($current_account != '') {
																echo "<tr class='warning'>";
																echo "<td colspan='10'><strong>Account {$current_account} Running Balance: " . number_format($running_balance, 2) . "</strong></td>";
																echo "</tr>";
															}
															$current_account = $detail['account_no'];
															$running_balance = 0;
														}

														$running_balance += ($detail['dr_amt'] - $detail['cr_amt']);

														echo "<tr>";
														echo "<td>{$detail['sn']}</td>";
														echo "<td><strong>[{$detail['account_no']}] {$detail['account_name']}</strong></td>";
														echo "<td><span class='label label-default'>{$detail['transc_type']}</span></td>";
														echo "<td>" . ($detail['dr_amt'] > 0 ? number_format($detail['dr_amt'], 2) : '-') . "</td>";
														echo "<td>" . ($detail['cr_amt'] > 0 ? number_format($detail['cr_amt'], 2) : '-') . "</td>";
														echo "<td>{$detail['date_entry2']}</td>";
														echo "<td>{$detail['lg_ref_no']}</td>";
														echo "<td>{$detail['invoice_no']}</td>";
														echo "<td>{$detail['prepared_by']}</td>";
														echo "<td><small>{$detail['item_services']}</small></td>";
														echo "</tr>";
													}

													// Final balance for last account
													if ($current_account != '') {
														echo "<tr class='warning'>";
														echo "<td colspan='10'><strong>Account {$current_account} Final Running Balance: " . number_format($running_balance, 2) . "</strong></td>";
														echo "</tr>";
													}

													echo "</tbody>";
													echo "</table>";
													echo "</div>"; // Close table-responsive
												}

												echo "</div></div>";
											} elseif ($reconcile_type == 'balance_analysis') {
												// Balance analysis
												echo "<div class='panel panel-warning'>";
												echo "<div class='panel-heading'><h4><i class='fa fa-chart-line'></i> Balance Analysis</h4></div>";
												echo "<div class='panel-body'>";

												// Account type analysis
												$type_analysis = $db->prepare("
													SELECT 
														COALESCE(cl.class_name, 'Unknown') AS account_type,
														COUNT(DISTINCT c.account_no) AS account_count,
														SUM(c.dr_amt) AS total_debit,
														SUM(c.cr_amt) AS total_credit,
														SUM(c.dr_amt) - SUM(c.cr_amt) AS net_balance
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													LEFT JOIN chart_groups g ON g.id = a.account_group
													LEFT JOIN chart_class cl ON cl.cid = g.class_id
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end
													$account_condition
													GROUP BY cl.class_name
													ORDER BY ABS(SUM(c.dr_amt) - SUM(c.cr_amt)) DESC
												");
												$type_analysis->execute($account_params);
												$type_results = $type_analysis->fetchAll(PDO::FETCH_ASSOC);

												echo "<h5>Analysis by Account Type</h5>";
												echo "<div class='table-responsive'>";
												echo "<table class='table table-striped table-bordered table-hover'>";
												echo "<thead>";
												echo "<tr>";
												echo "<th>Account Type</th>";
												echo "<th>Accounts</th>";
												echo "<th>Total Debit</th>";
												echo "<th>Total Credit</th>";
												echo "<th>Net Balance</th>";
												echo "<th>Balance %</th>";
												echo "</tr>";
												echo "</thead>";
												echo "<tbody>";

												$total_net_balance = array_sum(array_column($type_results, 'net_balance'));

												foreach ($type_results as $type) {
													$balance_percentage = $total_net_balance != 0 ? ($type['net_balance'] / $total_net_balance) * 100 : 0;

													$balance_class = '';
													if ($type['net_balance'] > 0) {
														$balance_class = 'text-success';
													} elseif ($type['net_balance'] < 0) {
														$balance_class = 'text-danger';
													}

													echo "<tr>";
													echo "<td><strong>{$type['account_type']}</strong></td>";
													echo "<td>{$type['account_count']}</td>";
													echo "<td>" . number_format($type['total_debit'], 2) . "</td>";
													echo "<td>" . number_format($type['total_credit'], 2) . "</td>";
													echo "<td class='$balance_class'><strong>" . number_format($type['net_balance'], 2) . "</strong></td>";
													echo "<td>" . number_format($balance_percentage, 1) . "%</td>";
													echo "</tr>";
												}

												echo "</tbody>";
												echo "</table>";
												echo "</div>";

												// Top unbalanced accounts
												echo "<h5>Top Unbalanced Accounts</h5>";
												$unbalanced = $db->prepare("
													SELECT 
														c.account_no,
														COALESCE(a.account_name, 'Unknown Account') AS account_name,
														SUM(c.dr_amt) - SUM(c.cr_amt) AS net_balance,
														COUNT(*) AS transaction_count
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end
													$account_condition
													GROUP BY c.account_no, a.account_name
													HAVING ABS(SUM(c.dr_amt) - SUM(c.cr_amt)) > 0.01
													ORDER BY ABS(SUM(c.dr_amt) - SUM(c.cr_amt)) DESC
													LIMIT 20
												");
												$unbalanced->execute($account_params);
												$unbalanced_results = $unbalanced->fetchAll(PDO::FETCH_ASSOC);

												if (!$unbalanced_results) {
													echo "<div class='alert alert-success'>All accounts are balanced!</div>";
												} else {
													echo "<div class='table-responsive'>";
													echo "<table class='table table-striped table-bordered table-hover'>";
													echo "<thead>";
													echo "<tr>";
													echo "<th>Account Code</th>";
													echo "<th>Account Name</th>";
													echo "<th>Net Balance</th>";
													echo "<th>Transactions</th>";
													echo "</tr>";
													echo "</thead>";
													echo "<tbody>";

													foreach ($unbalanced_results as $unbal) {
														$balance_class = $unbal['net_balance'] > 0 ? 'text-success' : 'text-danger';

														echo "<tr>";
														echo "<td><strong>{$unbal['account_no']}</strong></td>";
														echo "<td>{$unbal['account_name']}</td>";
														echo "<td class='$balance_class'><strong>" . number_format($unbal['net_balance'], 2) . "</strong></td>";
														echo "<td>{$unbal['transaction_count']}</td>";
														echo "</tr>";
													}

													echo "</tbody>";
													echo "</table>";
													echo "</div>";
												}

												echo "</div></div>";
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