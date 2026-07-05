<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Admitted Patients Credit/Deposit Status</h5>
			</div>



			<div class="ibox-content">

				<a href="index.php?cr&dschr" class="btn btn-danger btn-xs">Discharge Patient with Owing Amount</a>
				&nbsp; : &nbsp;
				<a href="index.php?cr" class="btn btn-warning btn-xs">Patient On-Admission Owing</a>


				<?php
				$All_patient_cr = 0;
				$all_patient_cr_count = 0;
				$todate = date("Y-m-d");

				if (isset($_GET['dschr'])) {
					$status = 4;
				} else {
					$status = 3;
				}

				// STEP 1: Get admitted patients
				$stmt_main = $db->prepare("
    SELECT hospital_no, adm_status, DATE(date_admit) AS date_admit
    FROM admission
    WHERE adm_status = :status
    ORDER BY date_admit DESC");
				$stmt_main->execute([':status' => $status]);
				$admittedPatients = $stmt_main->fetchAll(PDO::FETCH_ASSOC);

				if (count($admittedPatients) > 0) {
					$hosNos = array_column($admittedPatients, 'hospital_no');
					$hosNosList = "'" . implode("','", $hosNos) . "'";

					// STEP 2: Enrollee data
					$enrollees = [];
					$res = $db->query("SELECT hospital_no, surname, fname, insurance FROM enrollee WHERE hospital_no IN ($hosNosList)");
					while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
						$enrollees[$row['hospital_no']] = $row;
					}

					// STEP 3: Deposit balances
					$deposits = [];
					$res = $db->query("
        SELECT hospital_no,
               COALESCE(SUM(cr_amt), 0) - COALESCE(SUM(dr_amt), 0) AS balance
        FROM chart_ledger
        WHERE hospital_no IN ($hosNosList) AND account_no = 2121
        GROUP BY hospital_no
    ");
					while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
						$deposits[$row['hospital_no']] = (float)$row['balance'];
					}

					// STEP 4: Accommodation credits
					$accommodation = [];
					$res = $db->query("
        SELECT hospital_no, claim_amt, pay, date_entry, invoice_status
        FROM patient_ap_services
        WHERE hospital_no IN ($hosNosList)
          AND cat_type = 'Bed Space/Accommodation'
          AND cr = '1'
          AND remarks = 'auto_deduct'
    ");
					$now = new DateTime();
					while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
						$hos_no = $row['hospital_no'];
						if (!isset($accommodation[$hos_no])) {
							$accommodation[$hos_no] = ['pay' => 0, 'claim' => 0, 'duration' => 0];
						}
						$dateEntry = new DateTime($row['date_entry']);
						$diff = $dateEntry->diff($now);
						$days = (int)$diff->format('%a');
						$hours = (int)$diff->format('%h');
						$duration = $days + ($hours > 12 ? 1 : 0);
						if ($duration < 1) $duration = 1;

						if ((int)$row['invoice_status'] === 1) {
							$claimAmt = $row['claim_amt'];
							$payAmt = $row['pay'];
						} else {
							$claimAmt = $row['claim_amt'] * $duration;
							$payAmt = $row['pay'] * $duration;
						}

						$accommodation[$hos_no]['pay'] += $payAmt;
						$accommodation[$hos_no]['claim'] += $claimAmt;
						$accommodation[$hos_no]['duration'] = $duration;
					}

					// STEP 5: Render table
				?>
					<table class="table table-striped table-bordered table-hover dataTables-example">
						<tbody>
							<?php
							foreach ($admittedPatients as $pat) {
								$hos_no = $pat['hospital_no'];
								$back_date = date("Y-m-d", strtotime($pat['date_admit'] . " -2 days"));

								// Get unpaid services for this patient (filtered by date range)
								$stmt = $db->prepare("
            SELECT *
            FROM patient_ap_services
            WHERE hospital_no = :hos_no
              AND paystatus = 0
              AND DATE(date_entry) BETWEEN :back_date AND :todate
        ");
								$stmt->execute([
									':hos_no' => $hos_no,
									':back_date' => $back_date,
									':todate' => $todate
								]);

								$svc = [
									'invst_cr' => 0,
									'invst_claim' => 0,
									'invst_inv' => 0,
									'med_cr' => 0,
									'med_claim' => 0,
									'med_inv' => 0,
									'nur_cr' => 0,
									'nur_claim' => 0,
									'nur_inv' => 0,
									'other_cr' => 0,
									'other_claim' => 0,
									'other_inv' => 0
								];

								while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
									$cr = $rwx['cr'];
									$inv_status = $rwx['invoice_status'];
									$pay = (float)$rwx['pay'];
									$claim = (float)$rwx['claim_amt'];

									// Investigations
									if (in_array($rwx['serv_group'], ['Laboratory', 'Radiology'])) {
										if ($cr == '1') {
											$svc['invst_cr'] += $pay;
											$svc['invst_claim'] += $claim;
										} elseif ($cr == '0' && in_array($inv_status, ['0', '1'])) {
											$svc['invst_inv'] += $pay;
										}
									}

									// Pharmacy
									if ($rwx['serv_group'] == 'Pharmacy') {
										if ($cr == '1') {
											$svc['med_cr'] += $pay;
											if ($claim > 0) $svc['med_claim'] += $claim;
										} elseif ($cr == '0' && in_array($inv_status, ['0', '1'])) {
											$svc['med_inv'] += $pay;
											if ($claim > 0) $svc['med_claim'] += $claim;
										}
									}

									// Nursing
									if (in_array($rwx['cat_type'], ['Nursing Services', 'Nursing Consumable']) && $rwx['remarks'] !== 'auto_deduct') {
										if ($cr == '1') {
											$svc['nur_cr'] += $pay;
											if ($claim > 0) $svc['nur_claim'] += $claim;
										} elseif ($cr == '0' && in_array($inv_status, ['0', '1'])) {
											$svc['nur_inv'] += $pay;
											if ($claim > 0) $svc['nur_claim'] += $claim;
										}
									}

									// Other
									if (in_array($rwx['cat_type'], ['Medical Services', 'Other Services']) || $rwx['serv_group'] == 'Consultation') {
										if ($cr == '1') {
											$svc['other_cr'] += $pay;
											if ($claim > 0) $svc['other_claim'] += $claim;
										} elseif ($cr == '0' && in_array($inv_status, ['0', '1'])) {
											$svc['other_inv'] += $pay;
											if ($claim > 0) $svc['other_claim'] += $claim;
										}
									}
								}

								$acc = isset($accommodation[$hos_no]) ? $accommodation[$hos_no] : ['pay' => 0, 'claim' => 0, 'duration' => 0];
								$deposit = isset($deposits[$hos_no]) ? $deposits[$hos_no] : 0;
								$enr = isset($enrollees[$hos_no]) ? $enrollees[$hos_no] : ['fname' => '', 'surname' => '', 'insurance' => ''];
								$name = $enr['fname'] . ', ' . $enr['surname'];

								$total_credit = $acc['pay'] + $svc['invst_cr'] + $svc['med_cr'] + $svc['nur_cr'] + $svc['other_cr'];
								$total_claim_credits = $acc['claim'] + $svc['invst_claim'] + $svc['med_claim'] + $svc['nur_claim'] + $svc['other_claim'];
								$total_inv = $svc['invst_inv'] + $svc['med_inv'] + $svc['nur_inv'] + $svc['other_inv'];
								$bal = $deposit - $total_credit;

								if ($bal < 0) {
									$all_patient_cr_count++;
									$All_patient_cr += ($total_credit - $deposit);
								}
							?>
								<tr>
									<td colspan="2"><strong><?php echo $hos_no . ' / ' . $name . ' / ' . $enr['insurance']; ?></strong></td>
									<td colspan="2"><strong>Current Deposit: <?php echo $deposit > 0 ? number_format($deposit) : number_format($deposit); ?></strong></td>
									<td></td>
								</tr>
								<tr>
									<td>
										<strong><u>INVESTIGATIONS</u></strong><br>
										<table width="100%">
											<tr>
												<td><strong>CREDIT: </strong></td>
												<td><?php echo $svc['invst_cr']; ?></td>
											</tr>
											<tr>
												<td><strong>CREDIT (Claim): </strong></td>
												<td><?php echo $svc['invst_claim']; ?></td>
											</tr>
											<tr>
												<td><strong>INVOICE: </strong></td>
												<td><?php echo $svc['invst_inv']; ?></td>
											</tr>
										</table>
									</td>
									<td>
										<strong><u>PHARMACY</u></strong><br>
										<table width="100%">
											<tr>
												<td><strong>CREDIT: </strong></td>
												<td><?php echo $svc['med_cr']; ?></td>
											</tr>
											<tr>
												<td><strong>CREDIT (Claim): </strong></td>
												<td><?php echo $svc['med_claim']; ?></td>
											</tr>
											<tr>
												<td><strong>INVOICE: </strong></td>
												<td><?php echo $svc['med_inv']; ?></td>
											</tr>
										</table>
									</td>
									<td>
										<strong><u>NURSING SERVICES/CONS.</u></strong><br>
										<table width="100%">
											<tr>
												<td><strong>CREDIT: </strong></td>
												<td><?php echo $svc['nur_cr']; ?></td>
											</tr>
											<tr>
												<td><strong>CREDIT (Claim): </strong></td>
												<td><?php echo $svc['nur_claim']; ?></td>
											</tr>
											<tr>
												<td><strong>INVOICE: </strong></td>
												<td><?php echo $svc['nur_inv']; ?></td>
											</tr>
										</table>
									</td>
									<td>
										<strong><u>ACCOMMODATION</u></strong><br>
										<table width="100%">
											<tr>
												<td><strong>CREDIT: </strong></td>
												<td><?php echo number_format($acc['pay']); ?></td>
											</tr>
											<tr>
												<td><strong>CREDIT (Claim): </strong></td>
												<td><?php echo number_format($acc['claim']); ?></td>
											</tr>
											<tr>
												<td><strong>DURATION: </strong></td>
												<td><?php echo $acc['duration'] . ' Day(s)'; ?></td>
											</tr>
										</table>
									</td>
									<td>
										<strong><u>OTHER CREDITS</u></strong><br>
										<table width="100%">
											<tr>
												<td><strong>CREDIT: </strong></td>
												<td><?php echo number_format($svc['other_cr']); ?></td>
											</tr>
											<tr>
												<td><strong>CREDIT (Claim): </strong></td>
												<td><?php echo number_format($svc['other_claim']); ?></td>
											</tr>
											<tr>
												<td><strong>INVOICE: </strong></td>
												<td><?php echo number_format($svc['other_inv']); ?></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<strong>GRAND TOTAL</strong> |
										<strong>CREDITS: </strong><?php echo number_format($total_credit); ?> /
										<strong>CLAIM: </strong><?php echo number_format($total_claim_credits); ?> /
										<strong>INVOICE: </strong><?php echo number_format($total_inv); ?> |
										<?php if ($bal >= 0) { ?>
											<strong style="color:#009">CURRENT BAL. AS @ NOW:</strong> <strong><?php echo number_format($bal); ?></strong> |
											<strong>STATUS:</strong> <span style="color:#009; font-size:12px">NORMAL</span>
										<?php } else { ?>
											<strong style="color:#F00">CREDIT BAL.:</strong> <strong>(<?php echo number_format($bal); ?>)</strong> |
											<strong>STATUS:</strong> <span style="color:#F00; font-size:12px">DEPOSIT REQUIRED</span>
										<?php } ?>
									</td>
								</tr>
							<?php
							} // end foreach
							?>
						</tbody>
					</table>
					<hr>
					<h2>Summary:</h2>
					<strong style="font-size: 15px;">Total Admitted Patient(s) Credit: (<?php echo $all_patient_cr_count; ?> Patients Required Deposit)</strong> /
					<strong style="font-size: 25px;"><?php echo 'N' . number_format($All_patient_cr); ?></strong>
				<?php
				} else {
					echo '<br>No Record Found';
				}
				?>


			</div>
		</div>
	</div>

</div>