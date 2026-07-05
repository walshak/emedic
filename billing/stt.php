<div class="ibox-title">
	<h5 style="color:#00F">View Reciepts and General Statement of Accounts ... </h5>
</div>
<div class="ibox-content">
	<div class="row">

		<form action="pacct.php" method="POST">

			<?php if (!isset($_POST['print_ap'])) { ?>

				<div class="col-md-4">
					<div class="ibox float-e-margins">
						<label class="">Set Dates Range & Click Apply button</label><br>

						<div class="form_sep">
							<div class="input-daterange input-group">
								<?php
								$start_date = isset($_POST['start']) && $_POST['start'] != '' ? $_POST['start'] : date("Y-m-d");
								$end_date   = isset($_POST['end']) && $_POST['end'] != '' ? $_POST['end'] : date("Y-m-d");
								?>
								<input type="date" class="form-control" name="start" value="<?php echo $start_date; ?>">
								<span class="input-group-addon">to</span>
								<input type="date" class="form-control" name="end" value="<?php echo $end_date; ?>">
							</div>
						</div>
					</div>
				</div>


				<div class="col-md-3">
					<div class="ibox float-e-margins">
						<div class="form_sep">
							<label class="req">[Optional] Additional Reports</label>
							<select name="paymethod" class="input-sm form-control">
								<option value="">Select...</option>
								<?php
								// Determine default paymethod based on insurance type
								$default_paymethod = ($insurance_type == 'Family') ? 'patient_acct_family' : 'patient_acct';

								// Use posted value if available, otherwise default
								$selected_paymethod = isset($_POST['paymethod']) ? $_POST['paymethod'] : $default_paymethod;
								?>

								<?php if ($insurance_type == 'Family') { ?>
									<option value="patient_acct_family" <?php if ($selected_paymethod == 'patient_acct_family') echo 'selected'; ?>>
										Family Folder Statement/Transaction
									</option>
									<option value="patient_acct" <?php if ($selected_paymethod == 'patient_acct') echo 'selected'; ?>>
										Deposit Statement/Transactions
									</option>
								<?php } else { ?>
									<option value="patient_acct" <?php if ($selected_paymethod == 'patient_acct') echo 'selected'; ?>>
										Deposit Statement/Transactions
									</option>
								<?php } ?>


								<option value="billing_table" <?php if (@$_POST['paymethod'] == 'billing_table') echo 'selected'; ?>>
									Old Statement
								</option>

								<option value="claim" <?php if (@$_POST['paymethod'] == 'claim') echo 'selected'; ?>>
									Claims
								</option>

								<option value="discount" <?php if (@$_POST['paymethod'] == 'discount') echo 'selected'; ?>>
									Discounts
								</option>

								<option value="writeoff" <?php if (@$_POST['paymethod'] == 'writeoff') echo 'selected'; ?>>
									Write Off
								</option>

								<option value="charge" <?php if (@$_POST['paymethod'] == 'charge') echo 'selected'; ?>>
									Extra Charges
								</option>

								<option value="reverse" <?php if (@$_POST['paymethod'] == 'reverse') echo 'selected'; ?>>
									Reversed Transaction Reports
								</option>

								<option value="account_billed" <?php if (@$_POST['paymethod'] == 'account_billed') echo 'selected'; ?>>
									Account Billed
								</option>

								<option value="delivered_credit" <?php if (@$_POST['paymethod'] == 'delivered_credit') echo 'selected'; ?>>
									Delivered As Credit/Account Receivable
								</option>
							</select>

							<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>">
						</div>
					</div>
				</div>


				<div class="col-md-5">
					<div class="ibox float-e-margins">
						<div class="form_sep">
							<label for="reg_input_no" class="">.</label><br>
							<button class="btn btn-primary btn btn-sm" type="submit" name="statement"> <i class="fa fa-check"></i> Statement</button>
							&nbsp;|&nbsp;
							<button class="btn btn-primary btn btn-sm" type="submit" name="reciept"> <i class="fa fa-check"></i> Re-print Reciept</button>
							&nbsp;|&nbsp;
							<a class="btn btn-success btn btn-sm" href="../admission_billing.php?emr=<?php echo $emr; ?>">Adm. Billing Report</a>
							<a class="btn btn-warning btn btn-sm" href="pacct.php?emr=<?php echo $emr; ?>"><i class="fa fa-times"></i>&nbsp;Close</a>
						</div>
					</div>
				</div>
				<hr>

			<?php } ?>

			<div class="col-md-12">

				<?php
				if (isset($_POST['reciept'])) {

					$emr = $_POST['emr'];

					if (isset($_POST['start']) and $_POST['start'] == '') {
						$start = date("Y-m-d");
					} else {
						$start = $_POST['start'];
					}
					if (isset($_POST['end']) and $_POST['end'] == '') {
						$end = date("Y-m-d");
					} else {
						$end = $_POST['end'];
					}


					$stmt = $db->query("SELECT * FROM patient_ap_services WHERE hospital_no='$emr' and paystatus=1 and pay_mode='cash' and date(transact_date) between '$start' and '$end' order by invoice_status,paystatus,date_entry,cat_type");
					if ($stmt->rowCount() > 0) { ?>

						<table class="table table-striped" width="100%">
							<tr>
								<th width="3%">.</th>
								<th width="28%">Item</th>
								<th width="6%" align="right">Hosp/Price</th>
								<th width="6%" align="right">Cash Price</th>
								<th width="2%">Qty</th>
								<th width="6%" align="right">Amount</th>
								<th width="12%">DSC/CHR</th>
								<th width="10%">.</th>
								<th width="12%">Tranc. Date</th>
							</tr>
							<?php
							$s = 1;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$pay = $row['pay'];
								$pay_ = $row['pay'] / $row['qty'];
								$total_inv = $total_inv + $pay;

								$err = '';
								$disbled = '';
								$serv_group = strtoupper(trim($row['serv_group']));
								$cat_type   = strtoupper(trim($row['cat_type']));

								if ($serv_group === 'INVESTIGATION') {
									$labrequest_no = $row['drug_sn'];
									$stmt2 = $db->prepare("SELECT sn FROM lab_manage WHERE labrequest_no = ?");
									$stmt2->execute([$labrequest_no]);

									if ($stmt2->rowCount() === 0) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
								} elseif ($cat_type === 'DIALYSIS') {
									$created_by  = $row['created_by'];
									$hosp_price  = $row['hosp_price'];
									$date_entry  = date("Y-m-d", strtotime($row["date_entry"]));

									$stmt2 = $db->prepare("
													SELECT id 
													FROM dialysis 
													WHERE amount = ? AND created_by = ? AND DATE(request_date) = ?
												");
									$stmt2->execute([$hosp_price, $created_by, $date_entry]);

									if ($stmt2->rowCount() === 0) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
								} elseif ($serv_group === 'MEDICAL SERVICES') {
									// Keep variables if you'll use them later
									$drug_sn    = $row['drug_sn'];
									$created_by = $row['created_by'];
									$date_entry = date("Y-m-d", strtotime($row["date_entry"]));
								}
							?>

								<tr>
									<td width="2%">
										<input type="checkbox" checked value="<?php
																				echo $row['sn'] . '__' . $pay . '__' . $row['item_services'] . '__' . $row['qty'] . '__' . $row['cr'] . '__' . $discount . '__' . $charge . '__' . $total_chr . '__' . $total_dsc . '__' . $dura . '__' . $post_type . '__' . $count_bal . '__' . $dsc_chr_set; ?>" name="inv[]" id="add_m_<?php echo $n ?>" onclick="UpdateCost()" />
									</td>
									<td width="28%"><?php echo $s . ' - ' . $row['item_services']; ?>

										<?php if ($err != '' and $row['serv_group'] == 'Investigation') { ?>
											<a href="pacct.php?emr=<?= $emr; ?>&pay&clear=<?= $labrequest_no; ?>">Clear Error</a>
										<?php }  ?>

									</td>
									<td width="6%" align=""><?php echo number_format($row['hosp_price'], 2); ?></td>
									<td width="6%" align=""><?php echo number_format($pay_, 2); ?></td>
									<td width="2%"><?php echo $row['qty']; ?></td>
									<td width="6%" align=""><?php echo number_format($pay, 2); ?></td>
									<td width="12%"><?php
													if ($row['add_charge'] > 0) {
														echo 'CHR: ' . number_format($row['add_charge'], 2);
													}
													if ($row['discount'] > 0) {
														echo 'DSC: ' . number_format($row['discount'], 2);
													}
													?></td>
									<td width="10%"><?php
													if ($row['cr'] == 1 and $row['paystatus'] == '0') { ?>
											<strong style="color:#F00">Credit</strong>
										<?php } elseif ($row['invoice_status'] == '0') {
														echo 'Invoice';
													} elseif ($row['paystatus'] == '1') {
														echo 'Paid';
													}
										?>
									</td>
									<td width="15%"><?php echo  date('d M,y h:i a', strtotime($row['transact_date'])); ?></td>
								</tr>
							<?php $n += 1;
								$s += 1;
							} ?>
						</table>

						<button class="btn btn-info btn-sm" type="submit" name="print_recep" id="print_recep"><i class="fa fa-search"></i>&nbsp; Preview</button>

					<?php  } else { ?>
						<div class="alert alert-warning"> No Records to show </div>
						<?php }
				}

				if (isset($_POST['statement'])) {

					$emr = $_POST['emr'];

					$start = (!empty($_POST['start'])) ? $_POST['start'] : date("Y-m-d");
					$end   = (!empty($_POST['end']))   ? $_POST['end']   : date("Y-m-d");

					if (isset($_POST['paymethod']) && $_POST['paymethod'] != '') {
						$transc_type = $_POST['paymethod'];
						$bal_visible = 0;

						// Validate and format dates
						$start = isset($_POST['start']) ? $_POST['start'] : date('Y-m-01');
						$end = isset($_POST['end']) ? $_POST['end'] : date('Y-m-d');
						$insurance_no = isset($_POST['insurance_no']) ? trim($_POST['insurance_no']) : '';
						$emr = isset($_POST['emr']) ? trim($_POST['emr']) : '';
						$prepared_by = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';

						$report_title = date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
						$params = [':start' => $start, ':end' => $end]; // Common parameters
						$Report_head = null;

						switch ($transc_type) {
							case 'patient_acct':
								$title = "Payments Reports showing $report_title";
								$table_name = "chart_ledger";
								$search_part = "AND account_no = '2121' AND patient_stt_status != '2' AND date_entry2 BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								$bal_visible = 1;
								$Report_head = 'patient_acct';
								break;

							case 'patient_acct_family':
								$title = "Payments Reports showing $report_title";
								$table_name = "chart_ledger";
								//$search_part = "AND account_no = '2121' AND insurance_no = :insurance_no AND DATE(date_entry2) BETWEEN :start AND :end";
								$search_part = "AND account_no = '2121' AND patient_stt_status != '2' AND insurance_no = :insurance_no AND DATE(date_entry2) BETWEEN :start AND :end";
								$params[':insurance_no'] = $insurance_no;
								$bal_visible = 1;

								$hospital_nos = [];
								$stmt = $db->prepare("SELECT hospital_no FROM enrollee WHERE hmo_no = :insurance_no");
								$stmt->execute([':insurance_no' => $insurance_no]);
								$hospital_nos = $stmt->fetchAll(PDO::FETCH_COLUMN);

								// Update insurance_no in chart_ledger
								if (!empty($hospital_nos)) {
									$placeholders = implode(',', array_fill(0, count($hospital_nos), '?'));
									$sql_update = "UPDATE chart_ledger SET insurance_no = ? WHERE hospital_no IN ($placeholders)";
									$stmt_update = $db->prepare($sql_update);
									$stmt_update->execute(array_merge([$insurance_no], $hospital_nos));
								}
								$Report_head = 'patient_acct_family';
								break;

							case 'payment':
							case 'all':
								$title = "Payments Reports showing $report_title";
								$table_name = "chart_ledger";
								$search_part = "AND account_no != '2121' AND date_entry2 BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								break;

							case 'claim':
								$title = "Claims Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND claim_amt > 0 AND paystatus = '1' AND DATE(transact_date) BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								break;

							case 'discount':
								$title = "Discount Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND discount > 0 AND paystatus = '1' AND pay_mode = 'cash' AND DATE(transact_date) BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								break;

							case 'charge':
								$title = "Additional Charges Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND add_charge > 0 AND paystatus = '1' AND pay_mode = 'cash' AND DATE(transact_date) BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								break;

							case 'writeoff':
								$title = "Writeoff Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND paystatus = '1' AND pay_mode = 'writeoff' AND DATE(transact_date) BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								$Report_head = 'WRITE-OFF';
								break;

							case 'delivered_credit':
								$title = "Delivered As Credit Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND paystatus = '1' AND pay_mode = 'cash' AND wallet_debt_bill_to_acct='CREDIT' AND DATE(transact_date) BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								$Report_head = 'DELIVERED AS CREDIT';
								break;

							case 'reverse':
								$title = "Reverse Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND (pay_mode = 'reverse' OR paystatus = '3') AND DATE(date_entry) BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								break;

							case 'account_billed':
								$title = "Account Billed Reports showing $report_title";
								$table_name = "patient_ap_services";
								$search_part = "AND cr = 2 AND paystatus = '1' AND created_by = :prepared_by AND DATE(transact_date) BETWEEN :start AND :end";
								$params[':prepared_by'] = $prepared_by;
								$params[':emr'] = $emr;
								break;

							case 'billing_table':
								$title = "Payments Reports showing $report_title";
								$table_name = "patient_billing";
								$search_part = "AND date_entry2 BETWEEN :start AND :end";
								$params[':emr'] = $emr;
								break;

							default:
								$title = "Custom Reports showing $report_title";
								$table_name = "chart_ledger";
								$search_part = "AND ref_value LIKE :ref_value";
								$params[':ref_value'] = "%$transc_type%";
								$params[':emr'] = $emr;
								break;
						}

						// Final query
						if ($transc_type == 'patient_acct_family') {
							$sql = "SELECT * FROM $table_name WHERE hospital_no != '' $search_part ORDER BY sn";
							$stmt = $db->prepare($sql);
							$stmt->execute($params);
						} else {
							$sql = "SELECT * FROM $table_name WHERE hospital_no = :emr $search_part ORDER BY sn";
							$stmt = $db->prepare($sql);
							$stmt->execute($params);
						}

						// Now $stmt contains your result set
						///$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					}



					$fiscal_year_stmt = $db->query("
							SELECT begin, end 
							FROM chart_fiscal_year 
							WHERE closed = '0' 
							ORDER BY begin DESC 
							LIMIT 1");

					$row = $fiscal_year_stmt->fetch(PDO::FETCH_ASSOC);
					$begin = $row['begin'];
					$end_acct_date  = $row['end'];

					$begin_ts      = strtotime($begin);
					$end_ts        = strtotime($end_acct_date);
					$start_ts      = strtotime($start);
					$date_ts       = strtotime($end);

					$start_ts = strtotime($start);
					$date_ts  = strtotime($end);

					// Check if date difference exceeds 1 year (365 days)
					$diff_days = ($date_ts - $start_ts) / (60 * 60 * 24);

					if ($diff_days > 365) {
						echo "<div class='alert alert-danger'>Date range cannot exceed 1 year.</div>";
						exit;
					}

					$papaCondition = '';
					$params = [
						':emr'   => $emr,
						':start' => date('Y-01-01', strtotime($begin)),
						':end'   => date('Y-m-d', strtotime('-1 day', strtotime($start)))
					];

					if ($insurance_type === 'Family') {
						//$papaCondition = ' AND insurance_no = :insurance_no';
						$papaCondition = ' AND insurance_no = :insurance_no';
						$params[':insurance_no'] = $insurance_no;
					}

					if ($stmt->rowCount() > 0) {
						if ($table_name == "patient_ap_services") { ?>

							<div class="alert alert-success"><?php echo $title; ?></div>
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Item/Description</th>
										<th>Hospital Price </th>
										<th>Quantity</th>
										<th>Amount</th>
										<?php if ($transc_type == 'writeoff') { ?>
											<th>Description</th>
										<?php } else { ?>
											<th>Discount</th>
											<th>Add. Charge</th>
										<?php } ?>


										<th>T/Date</th>
										<th>E/Date</th>
										<th>Entered By</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$n = 1;
									$discount = 0;
									$charge = 0;
									$amount = 0;
									$claim_amt = 0;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										if ($transc_type == 'claim') {
											$pay = $row['claim_amt'];
											$amount = $amount + $row['claim_amt'];
										} else {
											$amount = $amount + $row['pay'];
											$pay = $row['pay'];
										}
										$charge = $charge + $row['add_charge'];
										$discount = $discount + $row['discount'];
									?>
										<tr>
											<td><input type="checkbox" class="inv_ckeckbox_" value="<?php
																									echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['hosp_price'] . '__' .
																										$row['qty'] . '__' . $row['transact_date'] . '__' . $pay . '__' . $row['discount'] . '__' .
																										$row['add_charge'] . '__' . $row['payment_remarks'] . '__' . $row['date_entry'] . '__' . $row['hospital_no']; ?>" name="inv_x[]" checked /> &nbsp;&nbsp;
												<?php echo  $n . ' - ' . $row['item_services']; ?></td>
											<td><?php echo  $row['hosp_price']; ?></td>
											<td><?php echo  $row['qty']; ?></td>
											<td><?php echo  $pay; ?></td>
											<?php if ($transc_type == 'writeoff') { ?>
												<td><?php echo  $row['payment_remarks']; ?></td>
											<?php } else { ?>
												<td><?php echo  $row['discount']; ?></td>
												<td><?php echo  $row['add_charge']; ?></td>
											<?php } ?>
											<td><?php echo  date("d,M Y h:i:s a", strtotime($row['date_entry'])); ?></td>
											<td><?php echo  date("d,M Y h:i:s a", strtotime($row['transact_date'])); ?></td>
											<td><?php echo  $row['prepared_by']; ?></td>
										</tr>
									<?php $n += 1;
									} ?>
								</tbody>
							</table>
							<hr />

							<div class="row">
								<div class="col-md-2">
									<h3 class="no-margins"><?php echo number_format($amount, 2); ?></h3>
									<small>Total Amount</small>
								</div>

								<div class="col-md-2">
									<h3 class="no-margins"><?php echo number_format($charge, 2); ?></h3>
									<small>Total Additional Charge</small>
								</div>

								<div class="col-md-2">
									<h3 class="no-margins"><?php echo number_format($discount, 2); ?></h3>
									<small>Total Discount</small>
								</div>
							</div>
							<br>

							<button class="btn btn-info btn-sm" type="submit" name="print_ap" id="print_ap"><i class="fa fa-search"></i>&nbsp; Preview</button>
							<input type="hidden" value="<?= $Report_head; ?>" name="report_type">

						<?php }

						if ($table_name == "chart_ledger") {

							$papaCondition = '';
							$whereParts = [];
							$params = [
								':start' => date('Y-01-01', strtotime($begin)),
								':end'   => date('Y-m-d', strtotime('-1 day', strtotime($start)))
							];

							// Build conditions
							if ($insurance_type === 'Family') {
								$whereParts[] = 'insurance_no = :insurance_no';
								$params[':insurance_no'] = $insurance_no;
							} else {
								$whereParts[] = 'hospital_no = :emr';
								$params[':emr'] = $emr;
							}

							// Common filters
							$whereParts[] = "account_no = '2121'";
							$whereParts[] = "patient_stt_status != 2";
							$whereParts[] = "DATE(date_entry2) BETWEEN :start AND :end";

							// Combine WHERE conditions safely
							$whereClause = implode(' AND ', $whereParts);

							$sql = "
									SELECT 
										COALESCE(SUM(dr_amt), 0) AS TOTAL_DEBITS, 
										COALESCE(SUM(cr_amt), 0) AS TOTAL_CREDITS
									FROM chart_ledger 
									WHERE $whereClause
								";

							$stmtb = $db->prepare($sql);
							$stmtb->execute($params);
							$result = $stmtb->fetch(PDO::FETCH_ASSOC);

							// Balance forward calculation
							$totalDebits  = $result['TOTAL_DEBITS'];
							$totalCredits = $result['TOTAL_CREDITS'];
							$bal_B_F      = $totalCredits - $totalDebits;
							$set          = ($totalDebits != 0 || $totalCredits != 0) ? 1 : null;

						?>


							<div class="alert alert-success"><?php echo $title; ?></div>
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Item/Description</th>
										<th>Bank</th>
										<?php if ($bal_visible == 1) { ?>
											<th>Deposit</th>
											<th>Withdrawn</th>
											<th>Balance</th>
										<?php } else { ?>
											<th>Recieved</th>
											<th>Service</th>
										<?php } ?>

										<th>Date</th>
										<th>Entered By</th>
									</tr>
								</thead>
								<tbody>
									<?php

									if ($set == 1) { ?>

										<tr>
											<td>Opening Balance</td>
											<td></td>

											<?php if ($bal_visible == 1) { ?>
												<td>0.00</td>
												<td>0.00</td>
												<td><?= number_format($bal_B_F, 2); ?></td>
											<?php } else { ?>
												<td></td>
												<td></td>
											<?php } ?>

											<td><?php ///echo  date("d,M Y", strtotime(date($end))); 
												?></td>
											<td></td>
										</tr>

									<?php }

									$n = 1;
									$dr_amt = 0;
									$cr_amt = 0;
									$cr_refund_amt = 0;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

										$sale_sn = $row['sale_sn'];
										$app_no = $row['app_no'];
										$item_services = $row['item_services'];
										$amt = $row['cr_amt'];
										if (strtoupper($row['ref_value']) != 'REFUND') {
											$cr_amt = $cr_amt + $row['cr_amt'];
										} else {
											$cr_refund_amt = $cr_refund_amt + $row['cr_amt'];
										}

										$dr_amt = $dr_amt + $row['dr_amt'];

										if ($n == 1) {
											$bal = $row['cr_amt'] + $bal_B_F - $row['dr_amt'];
										} else {
											$bal += $row['cr_amt'] - $row['dr_amt'];
										} ?>
										<tr>
											<td>
												<input type="checkbox" class="inv_checkbox_" value="<?php echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['transc_type'] . '__' . $row['dr_amt'] . '__' . $row['cr_amt'] . '__' . $bal . '__' . $row['date_entry2'] . '__' . $row['prepared_by']; ?>" name="inv_x[]" checked onclick="checkme()" /> &nbsp;&nbsp;
												<?php echo  $n . ' - ' . $row['item_services']; ?>
												<?php
												$item_services = $row['item_services'];
												if (strpos($item_services, "Returned/") !== false) {
													$Returned = $Returned + $row['cr_amt'];
												}

												if (strpos($item_services, 'Deposit') !== false || strpos($item_services, 'AR DEBT PAID') !== false) {
												?>
													&nbsp; <a href="pacct.php?emr=<?php echo $emr; ?>&dep=<?php echo $row['sn']; ?>">[ Print ]</a>
												<?php
												}
												?>

											</td>
											<td>
												<?php

												echo $row['bank_name'];

												?></td>

											<?php if ($bal_visible == 1) { ?>
												<td><?php echo  number_format($row['cr_amt'], 2); ?></td>
												<td><?php echo  number_format($row['dr_amt'], 2); ?></td>
												<td><?php echo  number_format($bal, 2); ?></td>
											<?php } else { ?>
												<td><?php


													$Deposit = 'Deposit';
													if (($row['item_services'] == 'Money Recieved' or
														strpos($row['item_services'], $Deposit) === 0) and $row['dr_amt'] > 0) {
														echo  number_format($row['dr_amt'], 2);
													} else {
														echo  '-';
													}
													?>
												</td>
												<td><?php


													echo  number_format($row['cr_amt'], 2);

													?>
												</td>


											<?php } ?>


											<td><?php echo  date("d,M Y h:i:s a", strtotime($row['date_entry'])); ?></td>
											<td><?php echo  $row['prepared_by']; ?></td>
										</tr>
									<?php $n += 1;
									} ?>
								</tbody>
							</table>
							<hr />
							<?php if ($bal_visible == 1) { ?>
								<div class="row">
									<div class="col-md-2">
										<h3 class="no-margins"><?php echo number_format($cr_amt - $Returned, 2); ?></h3>
										<small>Total Deposits</small>
									</div>

									<div class="col-md-2">
										<h3 class="no-margins"><?php echo number_format($Returned, 2); ?></h3>
										<small>Total Reversed</small>
									</div>

									<div class="col-md-2">
										<h3 class="no-margins"><?php echo number_format($dr_amt - $Returned, 2); ?></h3>
										<small>Total Deductions</small>
									</div>


								</div>
							<?php } ?>


							<br>



							<button class="btn btn-info btn-sm" type="submit" name="print_ap" id="print_ap"><i class="fa fa-search"></i>&nbsp; Preview</button>
							<input type="checkbox" id="agree" name="agree" value="yes"> Custom Selection
							<input type="hidden" value="<?= $Report_head; ?>" name="report_type">
							<input type="hidden" value="<?= $transc_type; ?>" name="transc_type">
							<input type="hidden" value="<?= $begin; ?>" name="begin_financial">
						<?php }

						if ($table_name == "patient_billing") { ?>

							<div class="alert alert-success"><?php echo $title; ?></div>

							<table class="table table-striped">
								<thead>
									<tr>
										<th>Item/Description</th>
										<th>Transaction Type</th>
										<th>Deposit/CR</th>
										<th>Deduct/DR</th>
										<th>Balance</th>
										<th>Date</th>
										<th>Entered By</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$n = 1;
									$dr_amt = 0;
									$cr_amt = 0;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


										$sale_sn = $row['sale_sn'];
										$app_no = $row['app_no'];
										$item_services = $row['item_services'];
										$amt = $row['cr_amt'];



										$dr_amt = $dr_amt + $row['dr_amt'];
										$cr_amt = $cr_amt + $row['cr_amt'];
									?>
										<tr>
											<td><input type="checkbox" value="<?php
																				echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['transc_type'] . '__' . $row['dr_amt'] . '__' . $row['cr_amt'] . '__' . $row['bal'] . '__' . $row['date_entry2'] . '__' . $row['prepared_by'] . '__' . $row['hospital_no']; ?>" name="inv_x[]" checked /> &nbsp;&nbsp;
												<?php echo  $n . ' - ' . $row['item_services']; ?>
												<?php
												$item_services = $row['item_services'];

												$item_services2 = substr($item_services, 0, 7);
												if ($item_services2 == 'Deposit') { ?>
													&nbsp; <a href="pacct.php?emr=<?php echo $emr; ?>&dep=<?php echo $row['sn']; ?>">[ Print ]</a><?php } ?></td>
											<td><?php
												if ($row['transc_type'] == 'Debit') {
													echo 'Deposit/Paid';
												} elseif ($row['transc_type'] == 'Credit') {
													echo '<strong>Used</strong>';
												} else {
													echo $row['transc_type'];
												}
												?></td>
											<td><?php echo  number_format($row['dr_amt'], 2); ?></td>
											<td><?php echo  number_format($row['cr_amt'], 2); ?></td>
											<td><?php echo  number_format($row['bal'], 2); ?></td>
											<td><?php echo  date("d,M Y h:i:s a", strtotime($row['date_entry'])); ?></td>
											<td><?php echo  $row['prepared_by']; ?></td>
										</tr>
									<?php $n += 1;
									} ?>
								</tbody>
							</table>
							<hr />

							<div class="row">
								<div class="col-md-2">
									<h3 class="no-margins"><?php echo number_format($dr_amt, 2); ?></h3>
									<small>Total Deposits</small>
								</div>

								<div class="col-md-2">
									<h3 class="no-margins"><?php echo number_format($cr_amt, 2); ?></h3>
									<small>Total Deductions</small>
								</div>

							</div>
							<br>

							<button class="btn btn-info btn-sm" type="submit" name="print_ap" id="print_ap"><i class="fa fa-search"></i>&nbsp; Preview</button>
							<input type="hidden" value="billing" name="report_type">
						<?php } ?>





					<?php } else { ?>
						<div class="alert alert-danger">No Record(s) to display between <?php echo 'Dates period: ' . $datetitle; ?></div>
				<?php }
				}
				?>

				<input type="hidden" value="<?php echo $emr; ?>" name="emr">
				<input type="hidden" value="<?php echo $start . '__' . $end; ?>" name="dates">
			</div>
		</form>

		<?php

		if (isset($_POST['print_ap'])) {
			if (isset($_POST['agree'])) {
				include("stt_print_custom.php");
			} else {
				include("stt_print.php");
			}
		}



		?>


		<script>
			function checkme() {
				// Get all invoice checkboxes
				var boxes = document.querySelectorAll('.inv_checkbox_');
				var custom = document.getElementById('agree');

				// Check if any invoice checkbox is unchecked
				var anyUnchecked = Array.prototype.some.call(boxes, function(box) {
					return !box.checked;
				});

				// If any unchecked, check "agree"; else uncheck it
				if (anyUnchecked) {
					custom.checked = true;
				} else {
					custom.checked = false; // Remove this line if you want it to stay checked once triggered
				}
			}
		</script>