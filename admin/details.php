      			<div class="alert alert-success">
      				<h4><?php echo $report_title; ?></h4>
      			</div>
      			<table class="table table-striped table-bordered table-hover dataTables-example">
      				<thead>
      					<tr>
      						<th></th>
      						<th data-toggle="true">Date</th>
      						<th data-toggle="true">Service/Item</th>
      						<th data-toggle="true">Hospital #</th>
      						<th data-toggle="true">Name</th>
      						<th data-toggle="true">By</th>
      						<th data-toggle="true">Amount</th>
      						<th data-toggle="true">Claim</th>
      						<th data-toggle="true">Invoiced By</th>
      						<th data-toggle="true">Date</th>
      						<th data-toggle="true">.</th>
      					</tr>
      				</thead>
      				<tbody>

      					<?php
							$n = 1;
							$tclaim = 0;
							$tpay = 0;
							$tcredit = 0;
							$queue = 0;
							$specimen = 0;
							$result = 0;
							$approve = 0;
							$reject = 0;
							$cancel = 0;
							$tcredit = 0;
							$writeoff = 0;
							$reverse = 0;

							while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

								$hospital_no = $roww['hospital_no'];
								$stmt_data = $db->query("SELECT surname,oname,fname FROM enrollee where hospital_no='$hospital_no'");
								$row_data = $stmt_data->fetch(PDO::FETCH_ASSOC);
								$patient_name = $row_data['fname'] . ' ' . $row_data['oname'] . ' ' . $row_data['surname'];
							?>
      						<tr>
      							<td><?php echo $n; ?></td>
      							<td><?php echo date('d,M y H:i:s a', strtotime($roww['date_entry'])); ?></td>
      							<td><?php echo $roww['item_services'];
										if ($_POST['Referred_select'] == 'Writeoff') {
											echo '<br>' .  $roww['payment_remarks'];
										}

										?></td>
      							<td><?php echo $roww['hospital_no']; ?></td>
      							<td><?php echo $patient_name; ///['hospital_no']; 
										?></td>
      							<td><?php echo $roww['prepared_by']; ?></td>
      							<td><?php echo $roww['pay']; ?></td>
      							<td><?php echo $roww['claim_amt']; ?></td>
      							<td><?php echo $roww['invoice_by']; ?></td>
      							<td><?php if ($roww['paystatus'] == '1') {
											echo date('d,M y H:i:s a', strtotime($roww['transact_date']));
										} else {
											echo '';
										} ?></td>
      							<td><?php

										if ($roww['paystatus'] == '1' and $roww['claim_amt'] > 0) {
											echo 'Posted';
											$tclaim = $tclaim + $roww['claim_amt'];
										} elseif ($roww['paystatus'] == '1' and $roww['pay'] > 0 and $roww['pay_mode'] == "cash" and $roww['cr'] == "0") {
											echo 'Paid';
											$tpay = $tpay + $roww['pay'];
										} elseif ($roww['paystatus'] == '1' and ($roww['pay_mode'] == "writeoff" or $roww['wallet_debt_bill_to_acct'] == "WRF")) {
											$writeoff = $writeoff + $roww['pay'];
											echo 'WriteOff';
										} elseif ($roww['paystatus'] == '3' or ($roww['pay_mode'] == "reverse")) {
											$reverse = $reverse + $roww['pay'];
											echo 'Reverse';
										} elseif ($roww['paystatus'] == '0' and $roww['cr'] == "1") {
											$tcredit = $tcredit + $roww['pay'];
										} else {
											echo 'Pending';
										}
										?>

      							</td>
      						</tr>
      					<?php $n++;
							} ?>
      				</tbody>
      			</table>

      			<h4>Summary</h4>
      			<table class="table table-striped table-bordered table-hover dataTables-example">
      				<tbody>
      					<tr>
      						<td></td>
      						<td><strong>Amount Insured<br> (Claims)</strong></td>
      						<td><strong>Amount Paid<br> (Cash Recieved)</strong></td>
      						<td><strong>Total Credits</strong></td>
      						<td><strong>Total Writeoff</strong></td>
      						<td><strong>Total Reverse</strong></td>
      					</tr>

      					<tr>
      						<td>#</td>
      						<td><?php echo number_format($tclaim, 2, '.', ','); ?></td>
      						<td><?php echo number_format($tpay, 2, '.', ','); ?></td>
      						<td><?php echo number_format($tcredit, 2, '.', ','); ?></td>
      						<td><?php echo number_format($writeoff, 2, '.', ','); ?></td>
      						<td><?php echo number_format($reverse, 2, '.', ','); ?></td>
      					</tr>

      				</tbody>
      			</table>

      			<br><br>

      			<a href="transc.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh & Search again</a>