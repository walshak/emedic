<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

// Fetch hospital details from the database
$hospital_details_query = 'SELECT * FROM hospital_details LIMIT 1';
$hospital_details_stmt = $db->prepare($hospital_details_query);
$hospital_details_stmt->execute();
$hospital_details = $hospital_details_stmt->fetch(PDO::FETCH_ASSOC);

// Construct the hospital table HTML
$hospital_table = '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">';
$hospital_table .= '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
$hospital_table .= '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
$hospital_table .= '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
$hospital_table .= '</table><br>';

// Get chart classes for account hierarchy
$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');
$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

// Get parameters
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$period = isset($_GET['period']) ? $_GET['period'] : 'monthly';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-01-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-12-31');
$account_filter = isset($_GET['account_filter']) ? $_GET['account_filter'] : '';
?>

<style>
	.trial-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 1.5rem;
	}

	.trial-table th,
	.trial-table td {
		padding: 0.35rem 0.6rem;
		font-size: 0.97em;
		border: none !important;
		background: none !important;
		position: relative;
	}

	.trial-class-row {
		font-weight: 700;
		color: #007bff;
		font-size: 1.18em;
		background: #f8f9fa;
	}

	.trial-group-row {
		font-weight: 600;
		color: #17a2b8;
		font-size: 1.07em;
		background: #f8f9fa;
	}

	.trial-account-row {
		font-weight: 500;
		color: #6c757d;
		font-size: 1em;
	}

	.trial-class-row td {
		padding-left: 0.2em;
	}

	.trial-group-row td {
		padding-left: 2.5em;
	}

	.trial-account-row td {
		padding-left: 4.5em;
	}

	.amount-cell,
	.balance-cell {
		text-align: right;
	}

	.toggle-link {
		cursor: pointer;
		color: #007bff;
		font-size: 1.1em;
		margin-right: 0.5em;
	}

	.print-icon {
		float: right;
		margin-left: 10px;
		cursor: pointer;
		font-size: 0.8em;
		color: #999;
	}

	.print-icon:hover {
		color: #666;
	}

	/* Period button styling with high specificity */
	.period-buttons-container {
		display: inline-block;
	}

	.period-buttons-container .period-btn {
		margin-right: 5px !important;
		margin-bottom: 5px !important;
		transition: all 0.3s ease !important;
		border: 1px solid #ccc !important;
		position: relative !important;
	}

	.period-buttons-container .period-btn.btn-primary,
	.period-btn.btn-primary {
		background-color: #337ab7 !important;
		border-color: #2e6da4 !important;
		color: #fff !important;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
	}

	.period-buttons-container .period-btn.btn-default,
	.period-btn.btn-default {
		background-color: #fff !important;
		border-color: #ccc !important;
		color: #333 !important;
	}

	.period-buttons-container .period-btn:hover,
	.period-btn:hover {
		background-color: #e6e6e6 !important;
		border-color: #adadad !important;
		transform: translateY(-1px) !important;
	}

	.period-buttons-container .period-btn.btn-primary:hover,
	.period-btn.btn-primary:hover {
		background-color: #286090 !important;
		border-color: #204d74 !important;
	}

	.period-buttons-container .period-btn:focus,
	.period-btn:focus {
		outline: none !important;
		box-shadow: 0 0 0 2px rgba(51, 122, 183, 0.5) !important;
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
</style>

<script>
	function toggleTrialSection(id) {
		var toggleIcon = document.getElementById(id + "_toggle");
		var isExpanded = toggleIcon && toggleIcon.innerHTML === "&#9660;";
		if (isExpanded) {
			// Collapse: hide all descendants and set all icons to collapsed
			toggleChildren(id, false, true);
			toggleIcon.innerHTML = "&#9654;";
		} else {
			// Expand: show all descendants and set all icons to expanded
			toggleChildren(id, true, true);
			toggleIcon.innerHTML = "&#9660;";
		}
	}

	// show: true to show, false to hide
	// recursive: true to set all descendants, false for direct children only
	function toggleChildren(parentId, show, recursive) {
		var rows = document.querySelectorAll('[data-parent="' + parentId + '"]');
		for (var i = 0; i < rows.length; i++) {
			rows[i].style.display = show ? "" : "none";
			var childId = rows[i].id;
			var toggleIcon = document.getElementById(childId + "_toggle");
			if (toggleIcon) {
				toggleIcon.innerHTML = show ? "&#9660;" : "&#9654;";
			}
			if (recursive) {
				// Recursively show/hide all descendants
				toggleChildren(childId, show, true);
			}
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
							<h5>Accounting Dashboard</h5>
						</div>
						<div class="ibox-content">

							<!-- Filter Form -->
							<div class="row">
								<div class="col-md-12">
									<form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">

										<!-- Date & Period Selection Row -->
										<div class="row mb-3">
											<div class="col-md-2">
												<div class="form-group">
													<label for="year" class="control-label"><strong>Year</strong></label>
													<select name="year" id="year" class="form-control">
														<?php for ($y = 2020; $y <= date('Y') + 1; $y++) : ?>
															<option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>><?php echo $y; ?></option>
														<?php endfor; ?>
													</select>
												</div>
											</div>

											<div class="col-md-3">
												<div class="form-group">
													<label for="start_date" class="control-label"><strong>Start Date</strong></label>
													<input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
												</div>
											</div>

											<div class="col-md-3">
												<div class="form-group">
													<label for="end_date" class="control-label"><strong>End Date</strong></label>
													<input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
												</div>
											</div>

											<div class="col-md-4">
												<div class="form-group">
													<label for="account_filter" class="control-label" title="Filter data by specific account - leave blank to show all accounts"><strong><i class="fa fa-filter"></i> Account Filter</strong></label>
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

										<!-- Period Selection & Options Row -->
										<div class="row mb-3">
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label"><strong>Period Grouping</strong></label>
													<div style="margin-top: 5px;">
														<!-- Hidden input to store selected period -->
														<input type="hidden" name="period" id="period" value="<?php echo $period; ?>">

														<div class="period-buttons-container">
															<button type="button" class="btn btn-sm period-btn <?php echo ($period == 'daily') ? 'btn-primary' : 'btn-default'; ?>" data-period="daily" onclick="return toggle_p_group(this)" title="Group data by daily periods">
																<i class="fa fa-calendar-day"></i> Daily
															</button>
															<button type="button" class="btn btn-sm period-btn <?php echo ($period == 'weekly') ? 'btn-primary' : 'btn-default'; ?>" data-period="weekly" onclick="return toggle_p_group(this)" title="Group data by weekly periods">
																<i class="fa fa-calendar-week"></i> Weekly
															</button>
															<button type="button" class="btn btn-sm period-btn <?php echo ($period == 'monthly') ? 'btn-primary' : 'btn-default'; ?>" data-period="monthly" onclick="return toggle_p_group(this)" title="Group data by monthly periods">
																<i class="fa fa-calendar"></i> Monthly
															</button>
															<button type="button" class="btn btn-sm period-btn <?php echo ($period == 'quarterly') ? 'btn-primary' : 'btn-default'; ?>" data-period="quarterly" onclick="return toggle_p_group(this)" title="Group data by quarterly periods">
																<i class="fa fa-calendar-alt"></i> Quarterly
															</button>
															<button type="button" class="btn btn-sm period-btn <?php echo ($period == 'yearly') ? 'btn-primary' : 'btn-default'; ?>" data-period="yearly" onclick="return toggle_p_group(this)" title="Group data by yearly periods">
																<i class="fa fa-calendar-check"></i> Yearly
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-3">
												<div class="form-group">
													<label class="control-label"><strong>Display Options</strong></label>
													<div style="margin-top: 8px;">
														<div class="checkbox">
															<label title="Toggle to show separate Credit and Debit columns instead of combined Net amount">
																<input type="checkbox" name="show_crdr" id="show_crdr" <?php echo isset($_GET['show_crdr']) ? 'checked' : '' ?>>
																<i class="fa fa-columns"></i> <strong>Show Credit/Debit Columns</strong>
															</label>
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-3">
												<div class="form-group">
													<label class="control-label"><strong>Actions</strong></label>
													<div style="margin-top: 8px;">
														<button type="submit" class="btn btn-primary btn-lg" style="margin-right: 15px; font-weight: bold;" title="Click to fetch and display trial balance data with your selected filters">
															<i class="fa fa-search"></i> <strong>FETCH DATA</strong>
														</button>
														<br><small class="text-muted" style="margin-bottom: 10px; display: block;">↑ Click above to load data</small>
														<button type="button" class="btn btn-success btn-sm" onclick="exportDiagnosticTrialBalanceToCSV()" title="Export current data to CSV file">
															<i class="fa fa-download"></i> Export CSV
														</button>
													</div>
												</div>
											</div>
										</div>

										<!-- Quick Date Shortcuts -->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label class="control-label"><strong>Quick Date Shortcuts</strong></label>
													<div style="margin-top: 5px;">
														<button type="button" class="btn btn-xs btn-info" onclick="setCurrentMonth()">
															<i class="fa fa-calendar"></i> Current Month
														</button>
														<button type="button" class="btn btn-xs btn-info" onclick="setCurrentQuarter()">
															<i class="fa fa-calendar-alt"></i> Current Quarter
														</button>
														<button type="button" class="btn btn-xs btn-info" onclick="setCurrentYear()">
															<i class="fa fa-calendar-year"></i> Current Year
														</button>
														<button type="button" class="btn btn-xs btn-warning" onclick="setLastMonth()">
															<i class="fa fa-backward"></i> Last Month
														</button>
														<button type="button" class="btn btn-xs btn-warning" onclick="setLastQuarter()">
															<i class="fa fa-step-backward"></i> Last Quarter
														</button>
													</div>
												</div>
											</div>
										</div>

									</form>
								</div>
							</div>

							<!-- Results Section -->
							<div class="row">
								<div class="col-md-12">
									<div id="diagnostics-trial-balance">
										<?php
										try {
											// Create unified header with all trial balance information
											$period_display = ucfirst($period);
											$title = "Trial Balance Report ($period_display) - $year";
											echo "<div class='text-center mb-4'>";
											echo "<h3>$title</h3>";
											echo "<p><strong>Period:</strong> $period_display | <strong>Date Range:</strong> $start_date to $end_date</p>";
											if ($account_filter) {
												// Get account name for display
												$account_info = $db->prepare("SELECT account_name FROM chart_accounts WHERE account_code = ?");
												$account_info->execute([$account_filter]);
												$account_data = $account_info->fetch(PDO::FETCH_ASSOC);
												$account_name = $account_data ? $account_data['account_name'] : 'Unknown Account';
												echo "<p><strong>Account Filter:</strong> [$account_filter] $account_name</p>";
											}
											echo "</div>";

											// Function to render trial balance
											function renderTrialBalance($db, $start, $end, $label, $groupSql, $account_filter = '')
											{

												// Build account filter condition
												$account_condition = "";
												$query_params = ['start' => $start, 'end' => $end];
												if ($account_filter) {
													$account_condition = "AND c.account_no = :account_filter";
													$query_params['account_filter'] = $account_filter;
												}

												// Modified query to include class and group information for hierarchy
												$stmt = $db->prepare("
													SELECT 
														DATE_FORMAT(c.date_entry2, $groupSql) AS period,
														c.account_no,
														COALESCE(a.account_name, 'Unknown Account') AS account_name,
														COALESCE(cl.class_name, 'Unknown Class') AS class_name,
														COALESCE(cl.cid, 999) AS class_id,
														COALESCE(cg.name, 'Unknown Group') AS group_name,
														COALESCE(cg.id, 999) AS group_id,
														SUM(c.dr_amt) AS total_debit,
														SUM(c.cr_amt) AS total_credit,
														SUM(c.dr_amt) - SUM(c.cr_amt) AS balance
													FROM chart_ledger c
													LEFT JOIN chart_accounts a ON c.account_no = a.account_code
													LEFT JOIN chart_groups cg ON a.account_group = cg.id
													LEFT JOIN chart_class cl ON cg.class_id = cl.cid
													WHERE c.patient_stt_status!=1 AND c.date_entry2 BETWEEN :start AND :end $account_condition
													GROUP BY period, c.account_no, a.account_name, cl.class_name, cl.cid, cg.name, cg.id
													ORDER BY period, cl.cid, cg.id, c.account_no
												");
												$stmt->execute($query_params);
												$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

												if (!$results) {
													echo "<div class='alert alert-info'>No data found for this period.</div>";
												} else {
													// Group by period
													$periods = [];
													foreach ($results as $row) {
														$periods[$row['period']][] = $row;
													}

													foreach ($periods as $period => $accounts) {
														echo "<div class='period-header' style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>";
														echo "<h5 style='margin: 0;'>Period: $period</h5>";
														echo "<i class='fa fa-print text-muted' style='cursor:pointer; font-size:1.2em;' onclick='printPeriodSection(\"period_" . str_replace(['-', ' ', ':'], '_', $period) . "\")' title='Print Period'></i>";
														echo "</div>";
														echo "<div class='table-responsive' id='period_" . str_replace(['-', ' ', ':'], '_', $period) . "'>";
														echo "<table class='table table-striped table-bordered table-hover table-condensed trial-table'>";
														echo "<thead>";
														echo "<tr>";
														echo "<th>Account</th>";
														if (isset($_GET['show_crdr'])) {
															echo "<th class='amount-cell'>Total Debit</th>";
															echo "<th class='amount-cell'>Total Credit</th>";
														}
														echo "<th class='balance-cell'>Balance</th>";
														echo "</tr>";
														echo "</thead>";
														echo "<tbody>";

														$period_total_debit = 0;
														$period_total_credit = 0;
														$period_balance = 0;

														// Organize accounts by class and group for hierarchical display
														$display_structure = [];
														foreach ($accounts as $account) {
															$class_id = $account['class_id'];
															$group_id = $account['group_id'];

															if (!isset($display_structure[$class_id])) {
																$display_structure[$class_id] = [
																	'class_name' => $account['class_name'],
																	'groups' => []
																];
															}

															if (!isset($display_structure[$class_id]['groups'][$group_id])) {
																$display_structure[$class_id]['groups'][$group_id] = [
																	'group_name' => $account['group_name'],
																	'accounts' => []
																];
															}

															$display_structure[$class_id]['groups'][$group_id]['accounts'][] = $account;
														}

														// Display hierarchical structure
														$class_idx = 0;
														foreach ($display_structure as $class_id => $class_info) {
															$class_idx++;

															// Class row
															echo "<tr class='trial-class-row' id='trial_class_{$period}_{$class_idx}'>";
															echo "<td>";
															echo "<span id='trial_class_{$period}_{$class_idx}_toggle' class='toggle-link' onclick='toggleTrialSection(\"trial_class_{$period}_{$class_idx}\"); event.stopPropagation();'>&#9660;</span>";
															echo "<a href='ledger_entries.php?level=class&id={$class_id}&start=" . urlencode($start) . "&end=" . urlencode($end) . "' style='color:#007bff; text-decoration:underline;' title='View Ledger Entries for Class' target='_blank'>";
															echo htmlspecialchars($class_info['class_name']);
															echo "</a>";
															echo "<span class='print-icon' onclick='printClassSection(\"trial_class_{$period}_{$class_idx}\")' title='Print Class'>🖨️</span>";
															echo "</td>";
															if (isset($_GET['show_crdr'])) {
																echo "<td class='amount-cell'></td>";
																echo "<td class='amount-cell'></td>";
															}
															echo "<td class='balance-cell'></td>";
															echo "</tr>";

															$group_idx = 0;
															foreach ($class_info['groups'] as $group_id => $group_info) {
																$group_idx++;

																// Group row
																echo "<tr class='trial-group-row' id='trial_group_{$period}_{$class_idx}_{$group_idx}' data-parent='trial_class_{$period}_{$class_idx}'>";
																echo "<td>";
																echo "<span id='trial_group_{$period}_{$class_idx}_{$group_idx}_toggle' class='toggle-link' onclick='toggleTrialSection(\"trial_group_{$period}_{$class_idx}_{$group_idx}\"); event.stopPropagation();'>&#9660;</span>";
																echo "<a href='ledger_entries.php?level=group&id={$group_id}&start=" . urlencode($start) . "&end=" . urlencode($end) . "' style='color:#17a2b8; text-decoration:underline;' title='View Ledger Entries for Group' target='_blank'>";
																echo htmlspecialchars($group_info['group_name']);
																echo "</a>";
																echo "<span class='print-icon' onclick='printGroupSection(\"trial_group_{$period}_{$class_idx}_{$group_idx}\")' title='Print Group'>🖨️</span>";
																echo "</td>";
																if (isset($_GET['show_crdr'])) {
																	echo "<td class='amount-cell'></td>";
																	echo "<td class='amount-cell'></td>";
																}
																echo "<td class='balance-cell'></td>";
																echo "</tr>";

																$acc_idx = 0;
																foreach ($group_info['accounts'] as $account) {
																	$acc_idx++;
																	$period_total_debit += $account['total_debit'];
																	$period_total_credit += $account['total_credit'];
																	$period_balance += $account['balance'];

																	$balance_class = '';
																	if ($account['balance'] > 0) {
																		$balance_class = 'text-success';
																	} elseif ($account['balance'] < 0) {
																		$balance_class = 'text-danger';
																	}

																	// Account row
																	echo "<tr class='trial-account-row' id='trial_account_{$period}_{$class_idx}_{$group_idx}_{$acc_idx}' data-parent='trial_group_{$period}_{$class_idx}_{$group_idx}'>";
																	echo "<td>";
																	echo "<a href='ledger_entries.php?level=account&id={$account['account_no']}&start=" . urlencode($start) . "&end=" . urlencode($end) . "' style='color:#6c757d; text-decoration:underline;' title='View Ledger Entries for Account' target='_blank'>";
																	echo "[{$account['account_no']}] {$account['account_name']}";
																	echo "</a>";
																	echo "<span class='print-icon' onclick='printAccountSection(\"trial_account_{$period}_{$class_idx}_{$group_idx}_{$acc_idx}\")' title='Print Account'>🖨️</span>";
																	echo "</td>";
																	if (isset($_GET['show_crdr'])) {
																		echo "<td class='amount-cell'>" . number_format($account['total_debit'], 2) . "</td>";
																		echo "<td class='amount-cell'>" . number_format($account['total_credit'], 2) . "</td>";
																	}
																	echo "<td class='balance-cell $balance_class'>" . number_format($account['balance'], 2) . "</td>";
																	echo "</tr>";
																}
															}
														}

														// Period totals
														echo "<tr class='info'>";
														echo "<td><strong>Period Totals</strong></td>";
														if (isset($_GET['show_crdr'])) {
															echo "<td class='amount-cell'><strong>" . number_format($period_total_debit, 2) . "</strong></td>";
															echo "<td class='amount-cell'><strong>" . number_format($period_total_credit, 2) . "</strong></td>";
														}
														echo "<td class='balance-cell'><strong>" . number_format($period_balance, 2) . "</strong></td>";
														echo "</tr>";

														// Balance check
														$colspan = isset($_GET['show_crdr']) ? '4' : '2';
														if (abs($period_balance) > 0.01) {
															echo "<tr class='danger'>";
															echo "<td colspan='$colspan'><i class='fa fa-exclamation-triangle'></i> <strong>Warning:</strong> Period is not balanced! Difference: " . number_format($period_balance, 2) . "</td>";
															echo "</tr>";
														} else {
															echo "<tr class='success'>";
															echo "<td colspan='$colspan'><i class='fa fa-check'></i> <strong>Period is balanced</strong></td>";
															echo "</tr>";
														}

														echo "</tbody>";
														echo "</table>";
														echo "</div>"; // Close table-responsive
														echo "<br>";
													}
												}
											}

											// Determine date ranges and grouping based on period
											if ($period == 'daily') {
												renderTrialBalance($db, $start_date, $end_date, "Daily Trial Balance", "'%Y-%m-%d'", $account_filter);
											} elseif ($period == 'weekly') {
												renderTrialBalance($db, $start_date, $end_date, "Weekly Trial Balance", "CONCAT('%Y-Week ', LPAD(WEEK(date_entry2, 1), 2, '0'))", $account_filter);
											} elseif ($period == 'monthly') {
												renderTrialBalance($db, $start_date, $end_date, "Monthly Trial Balance", "CONCAT('%Y-', MONTHNAME(date_entry2))", $account_filter);
											} elseif ($period == 'quarterly') {
												renderTrialBalance($db, $start_date, $end_date, "Quarterly Trial Balance", "CONCAT('%Y-Quarter ', QUARTER(date_entry2))", $account_filter);
											} elseif ($period == 'yearly') {
												renderTrialBalance($db, $start_date, $end_date, "Yearly Trial Balance", "'%Y'", $account_filter);
											}

											// Overall summary
											echo "<div class='panel panel-primary'>";
											echo "<div class='panel-heading'><h4>Overall Summary</h4></div>";
											echo "<div class='panel-body'>";

											// Build account filter for summary
											$summary_account_condition = "";
											$summary_params = ['start' => $start_date, 'end' => $end_date];
											if ($account_filter) {
												$summary_account_condition = "AND account_no = :account_filter";
												$summary_params['account_filter'] = $account_filter;
											}

											$summary = $db->prepare("
												SELECT 
													SUM(dr_amt) AS grand_total_debit,
													SUM(cr_amt) AS grand_total_credit,
													SUM(dr_amt) - SUM(cr_amt) AS grand_balance,
													COUNT(DISTINCT lg_ref_no) AS total_transactions,
													COUNT(*) AS total_entries
												FROM chart_ledger
												WHERE patient_stt_status!=1 AND date_entry2 BETWEEN :start AND :end $summary_account_condition
											");
											$summary->execute($summary_params);
											$summary_data = $summary->fetch(PDO::FETCH_ASSOC);

											echo "<div class='row'>";
											echo "<div class='col-md-3'>";
											echo "<h5>Grand Total Debit</h5>";
											echo "<h4>" . number_format($summary_data['grand_total_debit'], 2) . "</h4>";
											echo "</div>";
											echo "<div class='col-md-3'>";
											echo "<h5>Grand Total Credit</h5>";
											echo "<h4>" . number_format($summary_data['grand_total_credit'], 2) . "</h4>";
											echo "</div>";
											echo "<div class='col-md-3'>";
											echo "<h5>Grand Balance</h5>";
											$balance_class = abs($summary_data['grand_balance']) <= 0.01 ? 'text-success' : 'text-danger';
											echo "<h4 class='$balance_class'>" . number_format($summary_data['grand_balance'], 2) . "</h4>";
											echo "</div>";
											echo "<div class='col-md-3'>";
											echo "<h5>Total Transactions</h5>";
											echo "<h4>{$summary_data['total_transactions']}</h4>";
											echo "<p>({$summary_data['total_entries']} entries)</p>";
											echo "</div>";
											echo "</div>";

											if (abs($summary_data['grand_balance']) <= 0.01) {
												echo "<div class='alert alert-success'><i class='fa fa-check'></i> <strong>Books are balanced!</strong></div>";
											} else {
												echo "<div class='alert alert-danger'><i class='fa fa-exclamation-triangle'></i> <strong>Books are not balanced!</strong> Difference: " . number_format($summary_data['grand_balance'], 2) . "</div>";
											}

											echo "</div>";
											echo "</div>";
										} catch (PDOException $e) {
											echo "<div class='alert alert-danger'><i class='fa fa-exclamation-circle'></i> Database Error: " . $e->getMessage() . "</div>";
										}
										?>
									</div> <!-- End diagnostics-trial-balance -->
								</div>
							</div>

							<div style="margin-top: 20px;" id="print-buttons-section">
								<button class="btn btn-success" onclick="printTrialDiv('diagnostics-trial-balance')"><i class="fa fa-print">&nbsp; Print Trial Balance</i></button>
								&nbsp;&nbsp;
								<button class="btn btn-info" onclick="exportDiagnosticTrialBalanceToCSV()"><i class="fa fa-file-text-o">&nbsp; Export to CSV</i></button>
							</div>

						</div>
					</div>
				</div>
			</div>
			<?php include '../../inc/footer.php'; ?>

		</div>

		<?php include('../modal_lock.php'); ?>
		<?php include '/inc/footer_scripts.php'; ?>

		<script>
			// Hospital header HTML for printouts
			var hospitalHeader = `<?php echo addslashes($hospital_table); ?>`;

			// Auto-update date range when year changes
			document.getElementById('year').addEventListener('change', function() {
				const year = this.value;
				document.getElementById('start_date').value = `${year}-01-01`;
				document.getElementById('end_date').value = `${year}-12-31`;
			});

			// Quick date shortcut functions
			function setCurrentMonth() {
				const now = new Date();
				const year = now.getFullYear();
				const month = String(now.getMonth() + 1).padStart(2, '0');
				document.getElementById('start_date').value = `${year}-${month}-01`;
				const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
				document.getElementById('end_date').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year').value = year;
			}

			function setCurrentQuarter() {
				const now = new Date();
				const year = now.getFullYear();
				const quarter = Math.floor(now.getMonth() / 3);
				const startMonth = String(quarter * 3 + 1).padStart(2, '0');
				const endMonth = String(quarter * 3 + 3).padStart(2, '0');
				document.getElementById('start_date').value = `${year}-${startMonth}-01`;
				const lastDay = new Date(year, quarter * 3 + 3, 0).getDate();
				document.getElementById('end_date').value = `${year}-${endMonth}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year').value = year;
			}

			function setCurrentYear() {
				const year = new Date().getFullYear();
				document.getElementById('start_date').value = `${year}-01-01`;
				document.getElementById('end_date').value = `${year}-12-31`;
				document.getElementById('year').value = year;
			}

			function setLastMonth() {
				const now = new Date();
				const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
				const year = lastMonth.getFullYear();
				const month = String(lastMonth.getMonth() + 1).padStart(2, '0');
				document.getElementById('start_date').value = `${year}-${month}-01`;
				const lastDay = new Date(year, lastMonth.getMonth() + 1, 0).getDate();
				document.getElementById('end_date').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year').value = year;
			}

			function setLastQuarter() {
				const now = new Date();
				const currentQuarter = Math.floor(now.getMonth() / 3);
				const lastQuarter = currentQuarter === 0 ? 3 : currentQuarter - 1;
				const year = currentQuarter === 0 ? now.getFullYear() - 1 : now.getFullYear();
				const startMonth = String(lastQuarter * 3 + 1).padStart(2, '0');
				const endMonth = String(lastQuarter * 3 + 3).padStart(2, '0');
				document.getElementById('start_date').value = `${year}-${startMonth}-01`;
				const lastDay = new Date(year, lastQuarter * 3 + 3, 0).getDate();
				document.getElementById('end_date').value = `${year}-${endMonth}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year').value = year;
			}

			// Initialize Select2 for account dropdown
			$(document).ready(function() {
				$('.select2').select2({
					placeholder: "-- All Accounts --",
					allowClear: true,
					width: '100%'
				});
			});

			// Simple function to handle period group toggle
			function toggle_p_group(button) {
				var selectedPeriod = button.getAttribute('data-period');

				// Remove active class from all period buttons
				var allButtons = document.querySelectorAll('.period-btn');
				for (var i = 0; i < allButtons.length; i++) {
					allButtons[i].className = allButtons[i].className.replace('btn-primary', 'btn-default');
					if (!allButtons[i].className.includes('btn-default')) {
						allButtons[i].className += ' btn-default';
					}
				}

				// Add active class to clicked button
				button.className = button.className.replace('btn-default', 'btn-primary');
				if (!button.className.includes('btn-primary')) {
					button.className += ' btn-primary';
				}

				// Update hidden input
				document.getElementById('period').value = selectedPeriod;

				return false;
			}

			// Export diagnostic trial balance to CSV
			function exportDiagnosticTrialBalanceToCSV() {
				// Get form values directly from the form inputs
				var startDate = document.querySelector('input[name="start_date"]').value;
				var endDate = document.querySelector('input[name="end_date"]').value;
				var showCrDr = document.querySelector('input[name="show_crdr"]').checked ? '1' : '0';
				var accountFilter = document.querySelector('select[name="account_filter"]').value;

				// Get current period from PHP variable or default to monthly
				var period = '<?php echo $period; ?>';

				// Validate dates
				if (!startDate || !endDate) {
					alert('Please select start and end dates first.');
					return;
				}

				// Create download URL with parameters
				var exportUrl = 'trial_balance_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate) +
					'&show_crdr=' + showCrDr +
					'&period=' + encodeURIComponent(period) +
					'&diagnostic=1';

				if (accountFilter) {
					exportUrl += '&account_filter=' + encodeURIComponent(accountFilter);
				}

				// Create temporary download link
				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'Diagnostic_Trial_Balance_' + startDate + '_to_' + endDate + '_' + period + '.csv';
				link.style.display = 'none';

				// Trigger download
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}

			// Print trial balance function
			function printTrialDiv(divId) {
				// Get current period and year for title
				var year = document.querySelector('select[name="year"]').value;
				var period = document.querySelector('button.btn-primary').textContent;
				var title = 'Trial Balance Report (' + period + ') - ' + year;

				var content = document.getElementById(divId).innerHTML;
				var popupWindow = window.open('', '_blank', 'width=900,height=900');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>' + title + '</title>');
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body {
                            font-size: 12px;
                            color: #222;
                            background: #fff;
                        }
                        .trial-table {
                            width: 100%;
                            border-collapse: collapse !important;
                        }
                        .trial-table th, .trial-table td {
                            border: 1px solid #bbb !important;
                            background: none !important;
                            padding: 0.35rem 0.6rem;
                        }
                        .trial-class-row { font-weight: 700; color: #007bff; font-size: 1.18em; background: #f8f9fa; }
                        .trial-group-row { font-weight: 600; color: #17a2b8; font-size: 1.07em; background: #f8f9fa; }
                        .trial-account-row { font-weight: 500; color: #6c757d; font-size: 1em; }
                        .trial-class-row td { padding-left: 0.2em; }
                        .trial-group-row td { padding-left: 2.5em; }
                        .trial-account-row td { padding-left: 4.5em; }
                        .amount-cell, .balance-cell { text-align: right; }
                        .toggle-link { display: none !important; }
                        .fa-print, .print-icon { display: none !important; }
                        a { text-decoration: none !important; color: inherit !important; }
                        a:hover { text-decoration: none !important; color: inherit !important; }
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
                        .btn, .fa-print, .print-icon, a[onclick*="printTrialDiv"], a[onclick*="printDiv"] {
                            display: none !important;
                        }
                        @media print {
                            a[href]:after { content: none !important; }
                            a { color: inherit !important; text-decoration: none !important; }
                        }
                    </style>
                `);
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(hospitalHeader);
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				popupWindow.print();
			}

			// Print specific class section
			function printClassSection(classId) {
				// Get the class row and all its children (groups and accounts)
				var classRow = document.getElementById(classId);
				var content = '<table class="trial-table table table-striped table-bordered">';

				// Add table headers
				content += '<thead><tr>';
				content += '<th>Account</th>';
				// Check if CR/DR columns are visible
				var showCrDr = document.querySelector('input[name="show_crdr"]').checked;
				if (showCrDr) {
					content += '<th class="amount-cell">Total Debit</th>';
					content += '<th class="amount-cell">Total Credit</th>';
				}
				content += '<th class="balance-cell">Balance</th>';
				content += '</tr></thead><tbody>';

				content += classRow.outerHTML;

				// Get all direct child groups of this class
				var directGroups = document.querySelectorAll('[data-parent="' + classId + '"]');

				directGroups.forEach(function(groupRow) {
					content += groupRow.outerHTML;

					// Get all accounts under this group
					var groupId = groupRow.id;
					var accounts = document.querySelectorAll('[data-parent="' + groupId + '"]');
					accounts.forEach(function(accountRow) {
						content += accountRow.outerHTML;
					});
				});

				content += '</tbody></table>';

				printContent(content, 'Class Report');
			}

			// Print specific group section
			function printGroupSection(groupId) {
				var groupRow = document.getElementById(groupId);
				var content = '<table class="trial-table table table-striped table-bordered">';

				// Add table headers
				content += '<thead><tr>';
				content += '<th>Account</th>';
				// Check if CR/DR columns are visible
				var showCrDr = document.querySelector('input[name="show_crdr"]').checked;
				if (showCrDr) {
					content += '<th class="amount-cell">Total Debit</th>';
					content += '<th class="amount-cell">Total Credit</th>';
				}
				content += '<th class="balance-cell">Balance</th>';
				content += '</tr></thead><tbody>';

				content += groupRow.outerHTML;

				// Get all accounts under this group
				var accounts = document.querySelectorAll('[data-parent="' + groupId + '"]');
				accounts.forEach(function(accountRow) {
					content += accountRow.outerHTML;
				});

				content += '</tbody></table>';

				printContent(content, 'Group Report');
			}

			// Print specific account section
			function printAccountSection(accountId) {
				var accountRow = document.getElementById(accountId);

				var content = '<table class="trial-table table table-striped table-bordered">';

				// Add table headers
				content += '<thead><tr>';
				content += '<th>Account</th>';
				// Check if CR/DR columns are visible
				var showCrDr = document.querySelector('input[name="show_crdr"]').checked;
				if (showCrDr) {
					content += '<th class="amount-cell">Total Debit</th>';
					content += '<th class="amount-cell">Total Credit</th>';
				}
				content += '<th class="balance-cell">Balance</th>';
				content += '</tr></thead><tbody>';

				content += accountRow.outerHTML;
				content += '</tbody></table>';

				printContent(content, 'Account Report');
			}

			// Print specific period section
			function printPeriodSection(periodId) {
				var periodDiv = document.getElementById(periodId);

				var content = '<div>';
				content += periodDiv.innerHTML;
				content += '</div>';

				printContent(content, 'Period Report - ' + periodId.replace(/[_-]/g, ' '));
			}

			// Helper function for printing content
			function printContent(content, title) {
				var popupWindow = window.open('', '_blank', 'width=800,height=600');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>' + title + '</title>');
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body { font-size: 12px; color: #222; background: #fff; }
                        .trial-table { width: 100%; border-collapse: collapse !important; }
                        .trial-table th, .trial-table td { border: 1px solid #bbb !important; padding: 0.35rem 0.6rem; }
                        .trial-table th { background-color: #f8f9fa !important; font-weight: bold; text-align: center; }
                        .trial-class-row { font-weight: 700; color: #007bff; font-size: 1.18em; }
                        .trial-group-row { font-weight: 600; color: #17a2b8; font-size: 1.07em; }
                        .trial-account-row { font-weight: 500; color: #6c757d; font-size: 1em; }
                        .trial-class-row td { padding-left: 0.2em; }
                        .trial-group-row td { padding-left: 2.5em; }
                        .trial-account-row td { padding-left: 4.5em; }
                        .amount-cell, .balance-cell { text-align: right; }
                        .toggle-link, .fa-print, .print-icon { display: none !important; }
                        a { text-decoration: none !important; color: inherit !important; }
                        a:hover { text-decoration: none !important; color: inherit !important; }
                        @media print {
                            a[href]:after { content: none !important; }
                            a { color: inherit !important; text-decoration: none !important; }
                        }
                    </style>
                `);
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(hospitalHeader);
				popupWindow.document.write('<h3>' + title + '</h3>');
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				popupWindow.print();
			}
		</script>

		<script>
			<?php
			if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
				timeOut: 5000
			})
			<?php } ?>
		</script>

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
		<script src="../js/idle.js"></script>
</body>

</html>