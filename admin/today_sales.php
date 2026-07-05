<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Today's Transactions & Write-Off</h5>
			</div>

			<div class="ibox-content">


				<div class="row">

					<div class="col-sm-3 b-r">
						<h3 class="m-t-none m-b">New File</h3>
						<p>New Patient(s) Registered Today</p>

						<?php
						$pay = 0;
						$claim_amt = 0;
						$writeoff = 0;
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where (item_services='New File' or item_services='New Card') and cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
						if ($stmt->rowCount() > 0) {
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($rwx['pay_mode'] == 'cash') {
									$pay = $pay + $rwx['pay'];
								} elseif ($rwx['pay_mode'] == 'claim') {
									$claim_amt = $claim_amt + $rwx['claim_amt'];
								} else {
									$writeoff = $writeoff + $rwx['pay'];
								}
							}
						}
						?>

						<strong style="font-size:24px; "><?php echo $stmt->rowCount(); ?></strong><small> / Patients</small>
						<hr>
						<table width="100%">
							<tr>
								<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Paid: </small>
								</td>
								<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Claim: </small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong><br><small style="color:red;">Write-off: </small>
								</td>
							</tr>
						</table>

						<hr>
						<div style="background-color:#FCF; padding:10px; ">
							<strong>External Sales (Income)</strong>

							<?php
							$pay = 0;
							$claim_amt = 0;
							$writeoff = 0;
							$transact_date = date("Y-m-d");
							$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where serv_group='EX' and cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
							if ($stmt->rowCount() > 0) {
								$pay = 0;
								while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
									if ($rwx['pay_mode'] == 'cash') {
										$pay = $pay + $rwx['pay'];
									} elseif ($rwx['pay_mode'] == 'claim') {
										$claim_amt = $claim_amt + $rwx['claim_amt'];
									} else {
										$writeoff = $writeoff + $rwx['pay'];
									}
								}
							}
							?>
							<br>
							<strong style="font-size:24px"><?php echo number_format($pay); ?></strong> <small>/ Amount/Paid: </small> <br>
							<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong> <small>/ Amount/Write-Off: </small>
						</div>

					</div>

					<div class="col-sm-3 b-r">
						<h3>Consultation/Appointments (Income/Writeoff)</h3>

						<?php
						$pay = 0;
						$claim_amt = 0;
						$writeoff = 0;
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where serv_group='Consultation' and cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
						if ($stmt->rowCount() > 0) {
							$pay = 0;
							$claim_amt = 0;
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($rwx['pay_mode'] == 'cash') {
									$pay = $pay + $rwx['pay'];
								} elseif ($rwx['pay_mode'] == 'claim') {
									$claim_amt = $claim_amt + $rwx['claim_amt'];
								} else {
									$writeoff = $writeoff + $rwx['pay'];
								}
							}
						}
						?>

						<br>
						<strong style="font-size:24px; "><?php echo $stmt->rowCount(); ?></strong><small> / Appointments</small>
						<hr>
						<table width="100%">
							<tr>
								<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Amount/Paid: </small>
								</td>
								<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Amount/Claim: </small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong><br><small style="color:red;">Write-off: </small>
								</td>
							</tr>
						</table>

						<hr>

						<?php
						$date_ap = date("Y-m-d");
						$stmt = $db->query("SELECT hospital_no FROM apptm where status='cancelled' and date(date_ap)='$date_ap'");
						?>

						<strong style="font-size:24px"><?php echo $stmt->rowCount(); ?></strong> <small>/ Appointment Cancelled </small>

					</div>

					<div class="col-sm-3 b-r">
						<h3>Pharmacy/Investigations (Income)</h3><br>

						<h4>Pharmacy Income</h4>

						<?php
						$pay = 0;
						$claim_amt = 0;
						$writeoff = 0;
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where serv_group='Pharmacy' and cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
						if ($stmt->rowCount() > 0) {
							$pay = 0;
							$claim_amt = 0;
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($rwx['pay_mode'] == 'cash') {
									$pay = $pay + $rwx['pay'];
								} elseif ($rwx['pay_mode'] == 'claim') {
									$claim_amt = $claim_amt + $rwx['claim_amt'];
								} else {
									$writeoff = $writeoff + $rwx['pay'];
								}
							}
						}
						?>

						<strong style="font-size:24px; "><?php echo $stmt->rowCount(); ?></strong><small> / Total Medications</small> <br>
						<table width="100%">
							<tr>
								<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Amount/Paid: </small>
								</td>
								<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Amount/Claim: </small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong><br><small style="color:red;">Write-off: </small>
								</td>
							</tr>
						</table>

						<hr>

						<h4>Investigations Income</h4>

						<?php
						$pay = 0;
						$claim_amt = 0;
						$writeoff = 0;
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where (serv_group='Laboratory' or serv_group='Radiology') and cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
						if ($stmt->rowCount() > 0) {
							$pay = 0;
							$claim_amt = 0;
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($rwx['pay_mode'] == 'cash') {
									$pay = $pay + $rwx['pay'];
								} elseif ($rwx['pay_mode'] == 'claim') {
									$claim_amt = $claim_amt + $rwx['claim_amt'];
								} else {
									$writeoff = $writeoff + $rwx['pay'];
								}
							}
						}
						?>

						<strong style="font-size:24px; "><?php echo $stmt->rowCount(); ?></strong><small> / Investigations</small> <br>
						<table width="100%">
							<tr>
								<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Amount/Paid: </small>
								</td>
								<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Amount/Claim: </small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong><br><small style="color:red;">Write-off: </small>
								</td>
							</tr>
						</table>


					</div>

					<div class="col-sm-3">
						<h3>Nursing Station/Other Services</h3><br>

						<h4>Services/Consumables</h4>

						<?php
						$pay = 0;
						$claim_amt = 0;
						$writeoff = 0;
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where (serv_group='Medical Services' or serv_group='Nursing Services' or serv_group='Nursing Consumable') and cr=0 and acct_billed_ack=0 
	and	date(transact_date)='$transact_date' and paystatus='1'");
						if ($stmt->rowCount() > 0) {
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($rwx['pay_mode'] == 'cash') {
									$pay = $pay + $rwx['pay'];
								} elseif ($rwx['pay_mode'] == 'claim') {
									$claim_amt = $claim_amt + $rwx['claim_amt'];
								} else {
									$writeoff = $writeoff + $rwx['pay'];
								}
							}
						}
						?>

						<table width="100%">
							<tr>
								<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Amount/Paid: </small>
								</td>
								<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Amount/Claim: </small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong><br><small style="color:red;">Write-off: </small>
								</td>
							</tr>
						</table>

						<hr>

						<h4>Other Services</h4>

						<?php
						$pay = 0;
						$claim_amt = 0;
						$writeoff = 0;
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services where serv_group='Other Services' and cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
						if ($stmt->rowCount() > 0) {

							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($rwx['pay_mode'] == 'cash') {
									$pay = $pay + $rwx['pay'];
								} elseif ($rwx['pay_mode'] == 'claim') {
									$claim_amt = $claim_amt + $rwx['claim_amt'];
								} else {
									$writeoff = $writeoff + $rwx['pay'];
								}
							}
						}
						?>

						<table width="100%">
							<tr>
								<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Amount/Paid: </small>
								</td>
								<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Amount/Claim: </small>
								</td>
							</tr>
						</table>


					</div>

				</div>

				<hr>
				<br>

				<div class="row">

					<div class="col-sm-6 b-r">
						<h3 class="m-t-none m-b">Cashier/Account on Duty</h3>

						<?php
						$transact_date = date("Y-m-d");

						$stmt = $db->query("SELECT distinct prepared_by FROM chart_ledger where date(date_entry)='$transact_date'");
						if ($stmt->rowCount() > 0) {
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$prepared_by = $rwx['prepared_by'];

								$stmt2 = $db->query("SELECT username FROM admin_users where fullname='$prepared_by'");
								$rwxx = $stmt2->fetch(PDO::FETCH_ASSOC);
								$username = $rwxx['username'];

								$stmt3 = $db->query("SELECT * FROM admin_users_logs where username='$username' and date(Log_in)='$transact_date' order by sn limit 1");
								$rwxxx = $stmt3->fetch(PDO::FETCH_ASSOC);
								$first_login = $rwxxx['Log_in'];

								$stmt4 = $db->query("SELECT * FROM admin_users_logs where username='$username' and date(Log_in)='$transact_date' order by sn desc limit 1");
								$rwxxx = $stmt4->fetch(PDO::FETCH_ASSOC);
								$last_login = $rwxxx['Log_in'];
								$last_logout = $rwxxx['Log_out'];


								$stmt3 = $db->query("SELECT dr_amt,ref_value FROM chart_ledger 
		where (ref_value='cash' or ref_value='Cash' or ref_value='POS' or ref_value='Pay_by_transfer') 
		and transc_type='DEBIT' and prepared_by='$prepared_by' 
		and date(date_entry2)='$transact_date'");
								$cash = 0;
								$POS = 0;
								$transfer = 0;
								while ($roww = $stmt3->fetch(PDO::FETCH_ASSOC)) {

									if ($roww['ref_value'] == 'Cash' or $roww['ref_value'] == 'cash') {
										$dr_amt = $dr_amt + $roww['dr_amt'];
										$cash = $cash + $roww['dr_amt'];
									}
									if ($roww['ref_value'] == 'POS') {
										$POS = $POS + $roww['dr_amt'];
									}
									//ref_value='Transfer' or ref_value='Pay_by_transfer
									if ($roww['ref_value'] == 'Transfer' or $roww['ref_value'] == 'Pay_by_transfer') {
										$transfer = $transfer + $roww['dr_amt'];
									}
								}
						?>

								<h4><?php echo $prepared_by; ?></h4>

								<strong>Login at:</strong><?php echo date("H:i:s a", strtotime($first_login)) ?> <br>
								<strong>Last Login: </strong> <?php echo date("H:i:s a ", strtotime($last_login)) ?> / <strong>Logout: </strong> <?php if ($last_logout == '') {
																																						echo '-';
																																					} else {
																																						echo date("H:i:s a", strtotime($last_logout));
																																					} ?>

								<table width="100%">
									<tr>
										<td><strong style="font-size:24px"><?php echo number_format($cash); ?></strong><br><small>Amount/CASH: </small>
										</td>
										<td><strong style="font-size:24px"><?php echo number_format($POS); ?></strong><br><small>Amount/POS: </small>
										</td>
										<td><strong style="font-size:24px"><?php echo number_format($transfer); ?></strong><br><small>Amount/Transfer: </small>
										</td>

									</tr>
								</table>

								<hr>
						<?php

							}
						}
						?>



					</div>

					<div class="col-sm-6 b-r">
						<h3 class="m-t-none m-b">Doctors on Duty</h3>

						<?php
						$transact_date = date("Y-m-d");
						$stmt = $db->query("SELECT distinct fullname,username FROM admin_users_logs where date(Log_in)='$transact_date' and (rights='AD' or rights='MD' or rights='DR')");
						if ($stmt->rowCount() > 0) {
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$fullname = $rwx['fullname'];
								$username = $rwx['username'];

								$stmt44 = $db->query("SELECT hospital_no FROM apptm where referal_doc='$username' and queue_lock='1' and date(date_ap)='$transact_date'");
								$no_seen = $stmt44->rowCount();

								$pay = 0;
								$claim_amt = 0;
								$writeoff = 0;
								$stmt55 = $db->query("SELECT pay,claim_amt,pay_mode FROM patient_ap_services 
	where (dsp_by='$fullname' or prepared_by='$fullname') and  cr=0 and acct_billed_ack=0 and date(transact_date)='$transact_date' and paystatus='1'");
								if ($stmt55->rowCount() > 0) {
									$pay = 0;
									$claim_amt = 0;
									while ($rwx = $stmt55->fetch(PDO::FETCH_ASSOC)) {
										if ($rwx['pay_mode'] == 'cash') {
											$pay = $pay + $rwx['pay'];
										} elseif ($rwx['pay_mode'] == 'claim') {
											$claim_amt = $claim_amt + $rwx['claim_amt'];
										} else {
											$writeoff = $writeoff + $rwx['pay'];
										}
									}
								}


								$stmt3 = $db->query("SELECT * FROM admin_users_logs where username='$username' and date(Log_in)='$transact_date' order by sn limit 1");
								$rwxxx = $stmt3->fetch(PDO::FETCH_ASSOC);
								$first_login = $rwxxx['Log_in'];

								$stmt4 = $db->query("SELECT * FROM admin_users_logs where username='$username' and date(Log_in)='$transact_date' order by sn desc limit 1");
								$rwxxx = $stmt4->fetch(PDO::FETCH_ASSOC);
								$last_login = $rwxxx['Log_in'];
								$last_logout = $rwxxx['Log_out'];
						?>


								<h4><?php echo $fullname; ?></h4>

								<div class="row">
									<div class="col-sm-6 b-r">
										<strong>Login at:</strong><?php echo date("H:i:s a", strtotime($first_login)) ?> <br>
										<strong>Last Login: </strong> <?php echo date("H:i:s a ", strtotime($last_login)) ?> / <br><strong>Logout: </strong> <?php if ($last_logout == '') {
																																									echo '-';
																																								} else {
																																									echo date("H:i:s a", strtotime($last_logout));
																																								} ?>
									</div>

									<div class="col-sm-6">

										<strong style="font-size:24px; "><?php echo $no_seen; ?></strong><small> / Total Patient Seen</small>

										<table width="100%">
											<tr>
												<td><strong style="font-size:24px"><?php echo number_format($pay); ?></strong><br><small>Amount/Paid: </small>
												</td>
												<td><strong style="font-size:24px"><?php echo number_format($claim_amt); ?></strong><br><small>Amount/Claim: </small>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<strong style="font-size:24px"><?php echo number_format($writeoff); ?></strong><br><small style="color:red;">Amount/Write Off: </small>
												</td>
											</tr>
										</table>
									</div>

								</div>



								<hr>




						<?php

							}
						}

						?>


					</div>

				</div>

			</div>

		</div>
	</div>






</div>