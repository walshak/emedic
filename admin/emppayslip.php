<?php if (isset($_POST['delete_confirm_payslip'])) {

	$stmt = $db->prepare(sprintf(
		"DELETE FROM hrpayslip WHERE date_range=%s and  ECode=%s ",
		GetSQLValueString($_POST["date_range"], "text"),
		GetSQLValueString($_POST["Ecode"], "text")
	));
	$stmt->execute();
} ?>

<div class="row">
	<div class="col-lg-8">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Employee Salary Payslip</h5>
			</div>

			<div class="ibox-content">


				<?php
				if (isset($_GET['slp']) and $_GET['slp'] != '') {
					$ECode = $_GET['slp'];
				}

				$n = 1;
				$stmt = $db->query("SELECT distinct month, year FROM hrpayslip where ECode='$ECode' ORDER BY sn");
				if ($stmt->rowCount() > 0) { ?>

					<table class="table table-striped table-bordered table-hover dataTables-example">
						<thead>
							<tr>
								<th>No</th>
								<th>B/Salary</th>
								<th>Earning</th>
								<th>Deduction</th>
								<th>Net Pay</th>
								<th></th>
							</tr>
						</thead>
						<tbody>


							<?php
							$view = 'slp';
							while ($rwxx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$month = $rwxx['month'];
								$year = $rwxx['year'];

								/////////////// computer
								$stmtt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$ECode' and month='$month' and year='$year' order by sn");
								if ($stmtt->rowCount() > 0) {

									$total_earning = 0;
									$total_deduct = 0;
									$basic_sal = 0;
									while ($rww = $stmtt->fetch(PDO::FETCH_ASSOC)) {
										$date_range = $rww['date_range'];
										if ($rww['pay_head'] == 'E') {
											$total_earning = $total_earning + $rww['amount'];
											$Gtotal_earning = $Gtotal_earning + $rww['amount'];
										} elseif ($rww['pay_head'] == 'D') {
											$total_deduct = $total_deduct + $rww['amount'];
											$Gtotal_deduct = $Gtotal_deduct + $rww['amount'];
										} elseif ($rww['pay_head'] == 'B') {
											$basic_sal = $basic_sal + $rww['amount'];
											$Gbasic_sal = $Gbasic_sal + $rww['amount'];
										}
									} ?>


									<tr>
										<td><?php echo $n; ?></td>
										<td><?php echo $basic_sal; ?></td>
										<td><?php echo $total_earning; ?></td>
										<td><?php echo $total_deduct; ?></td>
										<td><?php $net = ($basic_sal + $total_earning) - $total_deduct;
											echo $net; ?></td>
										<td><a href="index.php?Ecode=<?php echo $ECode . '/' . $month . '/' . $year . '/' . $date_range . '&lip&one'; ?>">View Payslip</a>
											&nbsp;&nbsp; | &nbsp;&nbsp;
											<a data-toggle="modal" data-target="#myModal5" class="confirm_payslip_delete" id="<?php echo $ECode . '/' . $date_range . '/' . $month . '/' . $year . '/' . $staff_pay . '/' . $view . '/'; ?>">Delete</a>

										</td>
									</tr>
							<?php $n++;
								}
							} ?>
						</tbody>
					</table>




				<?php } else { ?>

					<strong>No Records to Display</strong>
				<?php } ?>

			</div>



		</div>
	</div>


	<div class="col-lg-4">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Employee Payroll Grand Totals </h5>
			</div>

			<div class="ibox-content">

				<?php

				if (isset($_GET['slp']) and $_GET['slp'] != '') {
					$ECode = $_GET['slp'];
					$stmt = $db->query("SELECT EmployeeCode, FirstName, LastName FROM hremp WHERE EmployeeCode='$ECode'");
					$row = $stmt->fetch(PDO::FETCH_ASSOC);


				?>
					<div class="form_sep">
						<strong><?php echo $ECode . ' / ' . $row['FirstName'] . ', ' . $row['LastName'];  ?></strong>
					</div>

					<div class="form_sep">

						<?php

						$stmtt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$ECode'");
						if ($stmtt->rowCount() > 0) {

							$Gtotal_earning = 0;
							$Gtotal_deduct = 0;
							$Gbasic_sal = 0;
							while ($rww = $stmtt->fetch(PDO::FETCH_ASSOC)) {
								$date_range = $rww['date_range'];
								if ($rww['pay_head'] == 'E') {

									$Gtotal_earning = $Gtotal_earning + $rww['amount'];
								} elseif ($rww['pay_head'] == 'D') {

									$Gtotal_deduct = $Gtotal_deduct + $rww['amount'];
								} elseif ($rww['pay_head'] == 'B') {

									$Gbasic_sal = $Gbasic_sal + $rww['amount'];
								}
							}
						}


						?>



						<table width="100%">
							<tr>
								<td>
									<strong style="font-size:16px;">Grand Total Basic Salary:</strong>
								</td>
								<td align="right">
									<strong style="font-size:16px;"><?php echo number_format($Gbasic_sal); ?></strong>
								</td>

							</tr>
							<tr>
								<td>
									<strong style="font-size:16px;">Grand Total Earnings:</strong>
								</td>
								<td align="right">
									<strong style="font-size:16px;"><?php echo number_format($Gtotal_earning); ?></strong>
								</td>
							</tr>
							<tr>
								<td>
									<strong style="font-size:16px;">Grand Total Deductions:</strong>
								</td>
								<td align="right">
									<strong style="font-size:16px;"><?php echo number_format($Gtotal_deduct); ?></strong>
								</td>
							</tr>
						</table>


					</div>

					<div align="right" class="form_sep"><strong style="font-size:20px;">Grand Total Net Pay: </strong><strong style="font-size:25px;">N
							<?php $net = ($Gbasic_sal + $Gtotal_earning) - $Gtotal_deduct;
							echo number_format($net) ?></strong></div>


				<?php
				}
				?>

			</div>
		</div>
	</div>

</div>

</div>
<div class="modal inmodal fade" id="confirm_del_payslip_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Confirm Delete Payslip</h4>
			</div>
			<div class="modal-body" id="confirm_del_payslip_body">
			</div>

		</div>
	</div>
</div>