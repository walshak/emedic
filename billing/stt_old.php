<div class="ibox-title">
	<h5 style="color:#00F">View Reciepts and General Statement of Accounts ... </h5>
</div>
<div class="ibox-content">
	<div class="row">

		<form action="pacct.php" method="POST">

			<?php if (!isset($_POST['print_ap']) and !isset($_POST['reconcile'])) { ?>

				<div class="col-md-4">
					<div class="ibox float-e-margins">
						<label for="reg_input_no" class="">Set Dates Range & Click Apply button </label><br>
						<div class="form_sep" id="">
							<div class="input-daterange input-group" id="">
								<input type="date" class="form-control" name="start" value="<?php
																							if (isset($_POST['start']) and $_POST['start'] != '') {
																								echo $_POST['start'];
																							} else {
																								echo date("Y-m-d");
																							} ?>" />
								<span class="input-group-addon">to</span>
								<input type="date" class="form-control" name="end" value="<?php
																							if (isset($_POST['end']) and $_POST['end'] != '') {
																								echo $_POST['end'];
																							} else {
																								echo date("Y-m-d");
																							} ?>" />
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-3">
					<div class="ibox float-e-margins">
						<div class="form_sep">
							<label class="req">[Optional] Additional Reports </label>
							<select name="paymethod" id="paymethod" class="input-sm form-control">
								<option value="patient_acct" selected>Select...</option>
								<option value="patient_acct" <?php if ($_POST['paymethod'] == 'patient_acct') {
																	echo 'selected';
																} ?>>Deposit Statement/Transactions</option>
								<?php if ($insurance_type == 'Family') { ?>
									<option value="patient_acct_family" <?php if ($_POST['paymethod'] == 'patient_acct_family') {
																			echo 'selected';
																		} ?>>Family Folder Statement/Transaction</option>
								<?php } ?>
								<option value="billing_table" <?php if ($_POST['paymethod'] == 'billing_table') {
																	echo 'selected';
																} ?>>Old Statement </option>
								<!--<option value="payment"<?php ///if($_POST['paymethod']=='payment'){echo 'selected';}
															?>>All Transactions</option> -->
								<option value="claim" <?php if ($_POST['paymethod'] == 'claim') {
															echo 'selected';
														} ?>>Claims</option>
								<option value="discount" <?php if ($_POST['paymethod'] == 'discount') {
																echo 'selected';
															} ?>>Discounts</option>

								<option value="writeoff" <?php if ($_POST['paymethod'] == 'writeoff') {
																echo 'selected';
															} ?>>Write Off</option>
								<option value="charge" <?php if ($_POST['paymethod'] == 'charge') {
															echo 'selected';
														} ?>>Extra Charges</option>

								<option value="reverse" <?php if ($_POST['paymethod'] == 'reverse') {
															echo 'selected';
														} ?>>Reversed Transaction Reports</option>

								<option value="account_billed" <?php if ($_POST['paymethod'] == 'account_billed') {
																	echo 'selected';
																} ?>>Account Billed</option>
							</select>
						</div>
					</div>
				</div>


				<div class="col-md-5">
					<div class="ibox float-e-margins">
						<div class="form_sep">
							<label for="reg_input_no" class="">.</label><br>
							<button class="btn btn-primary btn btn-sm" type="submit" name="statement"> <i class="fa fa-check"></i> Statement</button>
							&nbsp;&nbsp;|&nbsp;&nbsp;
							<button class="btn btn-primary btn btn-sm" type="submit" name="reciept"> <i class="fa fa-check"></i> Re-print Reciept</button>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
					if ($stmt->rowCount() > 0) {
				?>

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
								if (strtoupper($row['serv_group']) == 'INVESTIGATION') {
									$labrequest_no = $row['drug_sn'];
									$stmt2 = $db->query("SELECT sn FROM lab_manage WHERE labrequest_no='$labrequest_no'");
									if ($stmt2->rowCount() == 0) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
								} elseif (strtoupper($row['cat_type']) == 'DIALYSIS') {
									$created_by = $row['created_by'];
									$hosp_price = $row['hosp_price'];
									$date_entry = date("Y-m-d", strtotime($row["date_entry"]));
									$stmt2 = $db->query("SELECT id FROM dialysis 
			 		WHERE amount='$hosp_price' and created_by='$created_by' and date(request_date)='$date_entry'");
									if ($stmt2->rowCount() == 0) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
								} elseif (strtoupper($row['serv_group']) == 'MEDICAL SERVICES') {
									$drug_sn = $row['drug_sn'];
									$created_by = $row['created_by'];
									$date_entry = date("Y-m-d", strtotime($row["date_entry"]));
								} else {
									$err = '';
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

					if (isset($_POST['paymethod']) and $_POST['paymethod'] != '') {
						$transc_type = $_POST['paymethod'];
						$bal_visible = 0;

						if ($transc_type == 'patient_acct') {
							$title = "Payments Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "chart_ledger";
							$search_part = "and account_no ='2121' and date_entry2 BETWEEN '$start' and '$end'";
							$bal_visible = 1;
						} elseif ($transc_type == 'patient_acct_family') {
							$title = "Payments Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "chart_ledger";
							$search_part = "and account_no ='2121' and insurance_no ='$insurance_no' and date_entry2 BETWEEN '$start' and '$end'";
							$bal_visible = 1;
						} elseif ($transc_type == 'payment' or $transc_type == 'all') {
							$title = "Payments Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "chart_ledger";
							$search_part = "and account_no !='2121'  and date_entry2 BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'claim') {
							$title = "Claims Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "patient_ap_services";
							$search_part = " and claim_amt>0 and paystatus='1' and date(transact_date) BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'discount') {
							$title = "Discount Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "patient_ap_services";
							$search_part = " and discount>0 and paystatus='1' and pay_mode='cash' and date(transact_date) BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'charge') {
							$title = "Additional Charges Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "patient_ap_services";
							$search_part = " and add_charge>0 and paystatus='1' and pay_mode='cash' and date(transact_date) BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'writeoff') {
							$title = "Writeoff Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "patient_ap_services";
							$search_part = " and paystatus='1' and pay_mode='writeoff' and date(transact_date) BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'reverse') {
							$title = "Reverse Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "patient_ap_services";
							$search_part = " and (pay_mode='reverse' or paystatus='3') and date(date_entry) BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'account_billed') {
							$prepared_by = $_SESSION['fullname'];
							$table_name = "patient_ap_services";
							$search_part = " and cr=2 and paystatus='1' and created_by='$prepared_by' and date(transact_date) BETWEEN '$start' and '$end'";
						} elseif ($transc_type == 'billing_table') {
							$title = "Payments Reports showing " . date("d M, Y", strtotime($start)) . ' to ' . date("d M, Y", strtotime($end));
							$table_name = "patient_billing";
							$search_part = "and date_entry2 BETWEEN '$start' and '$end'";
						} else {

							$table_name = "chart_ledger";
							$search_part = "and ref_value LIKE '%$transc_type%'";
						}
						if ($transc_type == 'patient_acct_family') {
							$stmt = $db->query("SELECT * FROM $table_name WHERE hospital_no !='' $search_part order by sn");
						} else {
							$stmt = $db->query("SELECT * FROM $table_name WHERE hospital_no='$emr' $search_part order by sn");
						}
					}

					if ($stmt->rowCount() > 0) { ?>

						<?php if ($table_name == "patient_ap_services") { ?>

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


										<th>Date</th>
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
											<td><input type="checkbox" value="<?php
																				echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['hosp_price'] . '__' . $row['qty'] . '__' . $row['transact_date'] . '__' . $pay . '__' . $row['discount'] . '__' . $row['add_charge'] . '__' . $row['payment_remarks']; ?>" name="inv_x[]" checked /> &nbsp;&nbsp;
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
							<input type="hidden" value="WRITE-OFF" name="report_type">

						<?php } ?>

						<?php

						if ($table_name == "chart_ledger") {
							///echo $insurance_no;

							if ($insurance_type == 'Family') {
								$papa = "insurance_no='$insurance_no'";
							} else {
								$papa = "hospital_no ='$emr'";
							}

							$year = date('Y', strtotime($end));
							$start_2 = date('Y-01-01', strtotime($year));
							//echo '<br>';
							$end = date('Y-m-d', strtotime('-1 day', strtotime($start)));
							//echo '<br>';

							$stmtb = $db->prepare("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger 
				WHERE  $papa and account_no='2121'
				AND date_entry2 BETWEEN :start AND :end");

							//$stmtb->bindParam(':emr', $emr);
							$stmtb->bindParam(':start', $start_2);
							$stmtb->bindParam(':end', $end);
							$stmtb->execute();
							$result = $stmtb->fetch();

							if (!is_null($result['TOTAL_DEBITS']) || !is_null($result['TOTAL_CREDITS'])) {
								// Row exists!
								$totalDebits = $result['TOTAL_DEBITS'];
								echo '<br>';
								$totalCredits = $result['TOTAL_CREDITS'];
								echo '<br>';
								$bal_B_F = $totalCredits - $totalDebits;
								$set = 1;
							} else {
								///echo 'No row found!';/// No row found!
								$set = null;
							}
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
										$hospital_no = $row['hospital_no'];
										$app_no = $row['app_no'];
										$item_services = $row['item_services'];
										$amt = $row['cr_amt'];
										if (strtoupper($row['ref_value']) != 'REFUND') {
											$cr_amt = $cr_amt + $row['cr_amt'];
										} else {
											$cr_refund_amt = $cr_refund_amt + $row['cr_amt'];
										}

										$dr_amt = $dr_amt + $row['dr_amt'];

										///echo '===' . $dr_amt . '<br>';

										if ($n == 1) {
											///echo 'here';
											$bal = 0;
											//$bal = $row['cr_amt'] + $bal_B_F;
											if ($row['cr_amt'] > 0) {
												$bal = $bal + $row['cr_amt'];
											} else {
												$bal = $bal - $row['dr_amt'];
											}
										} else {

											if ($row['cr_amt'] > 0) {
												$bal = $bal + $row['cr_amt'];
											} else {
												$bal = $bal - $row['dr_amt'];
											}
										}

									?>
										<tr>
											<td><input type="checkbox" value="<?php
																				echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['transc_type'] . '__' . $row['dr_amt'] . '__' . $row['cr_amt'] . '__' . $bal . '__' . $row['date_entry2'] . '__' . $row['prepared_by'] . '__' . $hospital_no; ?>" name="inv_x[]" checked /> &nbsp;&nbsp;
												<?php echo  $n . ' - ' . $row['item_services']; ?>
												<?php
												$item_services = $row['item_services'];
												$item_services2 = substr($item_services, 0, 7);
												if ($item_services2 == 'Deposit') { ?>
													&nbsp; <a href="pacct.php?emr=<?php echo $emr; ?>&dep=<?php echo $row['sn']; ?>">[ Print ]</a><?php } ?></td>
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
										<h3 class="no-margins"><?php echo number_format($cr_amt, 2); ?></h3>
										<small>Total Deposits</small>
									</div>

									<div class="col-md-2">
										<h3 class="no-margins"><?php echo number_format($cr_refund_amt, 2); ?></h3>
										<small>Total Reversed</small>
									</div>

									<div class="col-md-2">
										<h3 class="no-margins"><?php echo number_format($dr_amt, 2); ?></h3>
										<small>Total Deductions</small>
									</div>


								</div>
							<?php } ?>


							<br>

							<button class="btn btn-info btn-sm" type="submit" name="print_ap" id="print_ap"><i class="fa fa-search"></i>&nbsp; Preview</button>
							<input type="hidden" value="billing" name="report_type">
							<input type="hidden" value="<?= $transc_type; ?>" name="transc_type">
							&nbsp; : &nbsp;
							<button class="btn btn-danger btn-sm" type="submit" name="reconcile" id="reconcile"><i class="fa fa-search"></i>&nbsp; Reconcile Reciept Error</button>
						<?php } ?>





						<?php

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
																				echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['transc_type'] . '__' . $row['dr_amt'] . '__' . $row['cr_amt'] . '__' . $row['bal'] . '__' . $row['date_entry2'] . '__' . $row['prepared_by']; ?>" name="inv_x[]" checked /> &nbsp;&nbsp;
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
							&nbsp; : &nbsp;
							<button class="btn btn-danger btn-sm" type="submit" name="reconcile" id="reconcile"><i class="fa fa-search"></i>&nbsp; Reconcile Reciept Error</button>
						<?php } ?>





					<?php } else { ?>
						<div class="alert alert-danger">No Record(s) to display between <?php echo 'Dates period: ' . $datetitle; ?></div>
				<?php }
				}
				?>

				<input type="hidden" value="<?php echo $emr; ?>" name="emr">
				<input type="hidden" value="<?php echo $emr; ?>" name="emr">
				<input type="hidden" value="<?php echo $start . '__' . $end; ?>" name="dates">
		</form>

	</div>


	<?php if (isset($_POST['print_ap'])) {
		include("stt_print.php");
	} ?>