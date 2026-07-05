<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Generate Payslip Option Here ... </h5>
			</div>

			<form action="index.php?gpay" method="POST">

				<div class="ibox-content">
					<div class="row">
						<div class="col-sm-3">
							<label for="reg_select" class="">Generate by Designation</label>
							<select name="Designation" class="form-control">
								<option selected="selected" value="">... select ...</option>
								<?php
								$stmt = $db->query("SELECT distinct designation FROM designation ORDER BY designation ASC");
								while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $row2['designation']; ?>"><?php echo $row2['designation']; ?></option>
								<?php } ?>
							</select>

						</div>
						<div class="col-sm-3">
							<label for="reg_select" class="">Generate by Deparment</label>
							<select name="Department" class="form-control">
								<option selected="selected" value="">... select ...</option>
								<?php
								$stmt = $db->query("SELECT distinct Department FROM hremp ORDER BY Department ASC");

								while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $row2['Department']; ?>"><?php echo $row2['Department']; ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-sm-3">
							<label for="reg_select" class="">Sort by Cadre</label>
							<select name="Cadre" class="form-control">
								<option selected="selected" value="">... select ...</option>
								<?php
								$stmt = $db->query("SELECT distinct Cadre FROM hremp ORDER BY Cadre ASC");

								while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $row2['Cadre']; ?>"><?php echo $row2['Cadre']; ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-sm-3">
							<label for="reg_select" class="">Check to Generate for All Staff</label>
							<div class="checkbox i-checks"><label> <input type="checkbox" value="all" name="all_staff">
									&nbsp; All Employees </label></div>
						</div>

					</div>
				</div>

				<div class="ibox-content">

					<div class="row">
						<div class="col-sm-4">

							<table>
								<tr>
									<td style="padding-right:5px;">
										<div class="form_sep">
											<label for="reg_select" class="req">Month</label>
											<select name="month" id="month" class="form-control" onchange="compute_dates()" required>
												<option selected="selected" value="">Select...</option>
												<option value="January">January</option>
												<option value="February">February</option>
												<option value="March">March</option>
												<option value="April">April</option>
												<option value="May">May</option>
												<option value="June">June</option>
												<option value="July">July</option>
												<option value="August">August</option>
												<option value="September">September</option>
												<option value="October">October</option>
												<option value="November">November</option>
												<option value="December">December</option>
											</select>
										</div>
									</td>
									<td>
										<div class="form_sep">
											<label for="reg_select" class="req">Year</label>
											<input type="text" id="year" name="year" class="form-control" value="<?php echo date("Y"); ?>" maxlength="4" required>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<div class="form_sep"></div>
										<div class="form_sep">
											<label for="reg_input_no" class="req">Set Dates Range</label><br>
											<div class="form_sep" id="">
												<div class="input-daterange input-group" id="">
													<input type="date" class="form-control" onchange="compute_dates()" name="start" id="date_start" value="" required />
													<span class="input-group-addon">to</span>
													<input type="date" class="form-control" onchange="compute_dates()" name="end" id="date_end" value="" required />
												</div>

											</div>
										</div>

									</td>
								</tr>
							</table>

						</div>


						<div class="col-sm-4">

							<br><strong>Choose one of the option below:</strong>
							<div class="radio i-checks"><label> <input type="radio" value="full" onclick="compute_dates()" name="sal_type" id="sal_type1" required> &nbsp;Pay Full Month </label></div>
							<div class="radio i-checks"><label> <input type="radio" value="days" onclick="compute_dates()" name="sal_type" id="sal_type2" required> &nbsp;Pay Days Worked </label></div>



						</div>
						<div class="col-sm-4">

							<div class="form_sep">
								<label for="reg_select" class="">Enter Total Days Worked</label>
								<input type="number" id="days" name="days" class="form-control" min="1">
							</div>

							<div class="form_sep">
								<button class="btn btn-info btn btn-sm" type="submit" name="generate_my_payslip" id="generate_my_payslip">Generate Payslip</button>

								&nbsp;&nbsp; | &nbsp;&nbsp;
								<a href="index.php?gpay" class="btn btn-white btn btn-sm"><i class="fa fa-refresh"></i> &nbsp;Refresh</a>

							</div>
						</div>


					</div>

			</form>
			<!-- new code -->
			<script>
				function compute_dates() {
					console.log('hello');
					days = $('#days');
					sal_type1 = $('#sal_type1');
					sal_type2 = $('#sal_type2');
					date_start = $('#date_start');
					date_end = $('#date_end');
					if (date_start.val() != '' && date_end.val() != '') {
						date_start = new Date(date_start.val());
						date_end = new Date(date_end.val());
						date_diff = ((date_end.getTime() - date_start.getTime()) / (1000 * 60 * 60 * 24)) + 1;
						days.val(date_diff);

					}

				}
			</script>
			<!-- end new code -->
		</div>


	</div>
</div>


<div class="col-lg-12">
	<div class="ibox float-e-margins">
		<div class="ibox-title">
			<h5>Payslip Reports</h5>
		</div>

		<div class="ibox-content">

			<form action="index.php?gpay" method="POST">

				<?php

				if (isset($_POST['send_pay'])) {



					if (!empty($_REQUEST['pay_email'])) {
						///echo Ecode=$Ecode/$month/$year/$date_range"	
						$item_list = $_REQUEST['pay_email'];

						for ($i = 0; $i < count($item_list); $i++) {
							$values = $item_list[$i];
							$break = explode("/", $values);
							$Ecode = $break[0];
							$month = $break[1];
							$year = $break[2];
							$date_range = $break[3];
							////////

							include("payslip_send_body.php");
						}
					}

					if ($done == 1) {
						echo '<strong style="color:#00F">Payslips sent successfully.</strong>';
					}
				}




				if (isset($_POST['generate_my_payslip']) or isset($_POST['save_payslip'])) {
					$Grand_Basic = 0;
					$Grand_Earning = 0;
					$Grand_Inc_Earning = 0;
					$Grand_Deduction = 0;
					$Grand_Net = 0;
					if (isset($_POST['generate_my_payslip'])) {

						$month = $_POST['month'];
						$year = $_POST['year'];
						$start = $_POST['start'];
						$end = $_POST['end'];
						$sal_type = $_POST['sal_type'];
						$days = $_POST['days'];
						$date_range = $start . ' - ' . $end;

						if (isset($_POST['Designation']) and $_POST['Designation'] != '') {
							$Designation = $_POST['Designation'];
							$query_part = "e.Designation='$Designation' and ";
						} elseif (isset($_POST['Department']) and $_POST['Department'] != '') {
							$Department = $_POST['Department'];
							$query_part = "e.Department='$Department' and ";
						} elseif (isset($_POST['Cadre']) and $_POST['Cadre'] != '') {
							$Cadre = $_POST['Cadre'];
							$query_part = "e.Cadre='$Cadre' and ";
						} elseif (isset($_POST['all_staff']) and $_POST['all_staff'] == 'all') {
							//$Cadre=$_POST['Cadre'];
							$query_part = " ";
						}
						$save_mode = 'no';
					} elseif (isset($_POST['save_payslip'])) {


						$month = $_POST['month2'];
						$year = $_POST['year2'];
						$start = $_POST['start2'];
						$end = $_POST['end2'];
						$sal_type = $_POST['sal_type2'];
						$days = $_POST['days2'];
						$query_part = $_POST['part2'];
						$date_range = $start . ' - ' . $end;
						$save_mode = 'yes';
					}



					if ($sal_type == 'full') {
						$ts = strtotime("$month $year");
						$days = date('t', $ts);
					} elseif ($days != '') {
						// calculate days worked
						$days = $days;
					} else {
						/// part	


						date_default_timezone_set('Africa/Lagos');
						$date1 = date_create($start);
						$date2 = date_create($end);
						$diff = date_diff($date1, $date2);
						$days = $diff->format("%a");
					}

					$stmt_eCODE = $db->query("SELECT u.fullname,e.EmployeeCode FROM admin_users as u inner join hremp as e on u.EmployeeCode=e.EmployeeCode WHERE $query_part e.status='0' order by e.sn");
					if ($stmt_eCODE->rowCount() > 0) {

				?>

						<table class="table table-striped table-bordered table-hover dataTables-example">
							<thead>
								<tr>

									<th>Staff No</th>
									<th>Name</th>
									<th>B/Salary</th>
									<th>Earning</th>
									<th>Income Earning</th>
									<th>Deduction</th>
									<th>Net Pay</th>
									<th>Confirm</th>
									<th>.</th>
									<th>.</th>
								</tr>
							</thead>
							<tbody>

								<?php
								$n = 1;
								while ($rowEc = $stmt_eCODE->fetch(PDO::FETCH_ASSOC)) {
									$Ecode = $rowEc['EmployeeCode'];
									$emplname = $rowEc['fullname'];

									//// BASIC SALARY  ////
									$stmt = $db->query("SELECT flat_percent_value FROM hred_details_sal WHERE employee_no='$Ecode' and EorD='B' and flat_percent_value>0");
									if ($stmt->rowCount() > 0) {
										$row = $stmt->fetch(PDO::FETCH_ASSOC);
										$BASIC_SALARY = 0;

										if ($sal_type == 'days') {
											$BASIC_SALARY = $row['flat_percent_value'];
											$ts = strtotime("$month $year");
											$no_days_mnths = date('t', $ts);
											$pay_per_day = round($BASIC_SALARY / $no_days_mnths);
											$BASIC_SALARY = $pay_per_day * $days;
										} else {
											$BASIC_SALARY = $row['flat_percent_value'];
										}


										$amount = $BASIC_SALARY;
										if ($save_mode == 'yes' and $BASIC_SALARY > 0) {
											$descriptn = 'Basic Salary';
											$pay_head = 'B';
											$duration = '';
											$bal = '';
											$row_sn = '';
											$pay_head_type = '';
											$sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
										}
									} else {
										$BASIC_SALARY = 0;
									}

									$Grand_Basic = $Grand_Basic + $BASIC_SALARY;
									////  END OF BASIC 	


									//// EARNING 

									$stmt = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$Ecode' and EorD='E' and flat_percent_value>0 and (bal='always' or duration>=1)");

									if ($stmt->rowCount() > 0) {
										$total_earnings = 0;
										while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
											$p = $rwx['flat_percent_value'];
											$duration = $rwx['duration'];
											$bal = $rwx['bal'];
											$descriptn = $rwx['descriptn'];
											$pay_head = 'E';
											$pay_head_type = 'Earnings';
											$row_sn = $rwx['sn'];

											if ($rwx['flat_percent'] == 'Percent') {
												$percent = $p / 100;
												$earning = $percent * $BASIC_SALARY;
												$total_earnings = $total_earnings + $earning;
											}
											if ($rwx['flat_percent'] == 'Flat') {
												$total_earnings = $total_earnings + $p;
												$earning = $p;
											}
											$amount = $earning;
											$Grand_Earning = $Grand_Earning + $earning;

											if ($save_mode == 'yes') {
												$sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
											}
										}
									} else {
										$total_earnings = 0;
									}
									/// END OF EARNING ---------------------------------

									//// SERVICE GENERATED EARNINGD


									$stmt = $db->query("SELECT * FROM hred_details WHERE employee_no='$Ecode' and flat_percent_value>0 and service_type_earn='All Hospital Services' and (bal='always' or duration>=1)");

									if ($stmt->rowCount() > 0) {

										$row = $stmt->fetch(PDO::FETCH_ASSOC);
										///ALL SERVICES HERE
										$fp = $row['flat_percent'];
										$fp_value = $row['flat_percent_value'];
										$duration = $row['duration'];
										$pay_head_type = 'Earnings_Income';
										$bal = $row['bal'];
										$row_sn = $row['sn'];
										$part = '';
										$pay_head = 'E';
										$gen_pay = '1';
										$earning_serv = 0;
										include_once("serv_part.php");
									} else {

										//// SPECIFY AND CATGORY ////				
										$earning_serv = 0;
								?>

									<?php $n = 1;
										$stmt_cat = $db->query("SELECT * FROM hred_income_services WHERE employee_no='$Ecode' and flat_percent_value>0 and type='Category' and (bal='always' or duration>=1)");

										if ($stmt_cat->rowCount() > 0) {
											while ($row = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
												/// loop thru category
												$fp = $row['flat_percent'];
												$fp_value = $row['flat_percent_value'];
												$descriptn = $row['descriptn'];
												$duration = $row['duration'];
												$pay_head_type = 'Earnings_Income';
												$pay_head = 'E';
												$bal = $row['bal'];
												$row_sn = $row['sn'];

												if ($descriptn == 'Nursing Services') {
													echo $part = "serv_group='Nursing Services' and ";
												} elseif ($descriptn == 'Investigation') {
													$part = "(serv_group='Laboratory' or serv_group='Radiology') and ";
												} elseif ($descriptn == 'Pharmacy') {
													$part = "(serv_group='Pharmacy' or serv_group='Nursing Consumable') and ";
												} elseif ($descriptn == 'Medical Services') {
													$part = "serv_group='Medical Services'";
												} elseif ($descriptn == 'Other Services') {
													$part = "(serv_group='Consultation' or serv_group='Other Services') and ";
												} else {
													$part = "";
												}
												$gen_pay = '1';
												include("serv_part.php");
											}
										}

										/////--------------------------- Specify				
										$stmt_item = $db->query("SELECT * FROM hred_income_services WHERE employee_no='$Ecode' and flat_percent_value>0 and type='Specify' and (bal='always' or duration>=1)");

										if ($stmt_item->rowCount() > 0) {
											while ($row = $stmt_item->fetch(PDO::FETCH_ASSOC)) {
												$fp = $row['flat_percent'];
												$fp_value = $row['flat_percent_value'];
												$descriptn = $row['descriptn'];
												$duration = $row['duration'];
												$pay_head_type = 'Earnings_Income';
												$pay_head = 'E';
												$bal = $row['bal'];
												$row_sn = $row['sn'];

												$gen_pay = '1';
												$part = "item_services='$descriptn' and ";
												include("serv_part.php");
											}
										} else {
											$no_income = 1;
										}
									}

									///// END SERVICE GENERATED EARNINGD 


									//// DEDUCTIONS 		
									$deduct = 0;
									$stmt = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$Ecode' and EorD='D' and flat_percent_value>0 and (bal='always' or duration>=1)");
									if ($stmt->rowCount() > 0) {

										$total_deduction = 0;
										while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {

											$p = $rwx['flat_percent_value'];
											$duration = $rwx['duration'];
											$bal = $rwx['bal'];
											$descriptn = $rwx['descriptn'];
											$pay_head = 'D';
											$pay_head_type = 'deductions';
											$row_sn = $rwx['sn'];

											if ($rwx['flat_percent'] == 'Percent') {
												$percent = $p / 100;
												$deduct = $percent * $BASIC_SALARY;
												$total_deduction = $total_deduction + $deduct;
											}
											if ($rwx['flat_percent'] == 'Flat') {
												$total_deduction = $total_deduction + $p;
												$deduct = $p;
											}

											$amount = $deduct;
											$Grand_Deduction = $Grand_Deduction + $deduct;

											if ($save_mode == 'yes') {
												$sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
											}
										}
									} else {
										$total_deduction = 0;
									}
									?>

									<tr>

										<td><?php echo $Ecode; ?></td>
										<td><?php echo $emplname; ?></td>
										<td><?php echo number_format($BASIC_SALARY); ?></td>
										<td><?php echo number_format($total_earnings); ?></td>
										<td><?php echo number_format($earning_serv); ?></td>
										<td><?php echo number_format($total_deduction); ?></td>
										<td><?php $net = 0;
											$net = ($BASIC_SALARY + $total_earnings + $earning_serv) - $total_deduction;
											echo number_format($net); ?></td>
										<td><a data-toggle="modal" data-target="#myModal5" class="confirm_payslip" id="<?php echo $Ecode . '__' . $start . '__' . $end . '__' . $sal_type . '__' . $emplname . '__' . $days . '__' . $month . '__' . $year; ?>">Confirm</a>
										</td>
										<?php
										$chk = $db->query("SELECT ECode FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range'");
										if ($chk->rowCount() > 0) { ?>
											<td><label> <input type="checkbox" class="checkbox i-checks" value="<?php echo "$Ecode/$month/$year/$date_range"; ?>" name="pay_email[]" checked></label></td>
											<td>
												<a href="index.php?<?php echo "Ecode=$Ecode/$month/$year/$date_range&lip"; ?>" target="_blank">Print</a>
												&nbsp; | &nbsp;

												<a href="index.php?<?php echo "Ecode=$Ecode/$month/$year/$date_range&lip&del"; ?>" target="_blank">Delete</a>
											</td>
										<?php } else { ?>
											<td></td>
											<td></td>
										<?php } ?>

									</tr>

								<?php

									///// =================== looping	
								} ?>

							</tbody>
						</table>


						<strong>Summary</strong>
						<hr>
						<strong style="color:#F06">Salary Dates Range:<br>
							<?php echo date("d M,Y", strtotime($start)) . ' - ' . date("d M,Y", strtotime($end)); ?></strong> &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
						<strong style="color:#F06">Pay Advice for <?php echo $month . ', ' . $year; ?></strong>
						&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
						<?php $ts = strtotime("$month $year");
						$days = date('t', $ts);
						echo '<strong>Total Days: ' . $days . '</strong>' ?>
						<hr>

						<strong>Total Employees: &nbsp; </strong> <strong style="font-size:16px"><?php echo $stmt_eCODE->rowCount(); ?></strong>
						<br>
						<strong>Total Basic Salary: &nbsp; </strong> <strong style="font-size:16px"><?php echo number_format($Grand_Basic); ?></strong>
						&nbsp; | &nbsp;
						<strong>Total Earning: &nbsp; </strong> <strong style="font-size:16px"><?php echo number_format($Grand_Earning); ?></strong>
						&nbsp; | &nbsp;
						<strong>Total Earning (Income): &nbsp; </strong> <strong style="font-size:16px"><?php echo number_format($Grand_Inc_Earning); ?></strong>
						&nbsp; | &nbsp;
						<strong>Total Deductions: &nbsp; </strong> <strong style="font-size:16px"><?php echo number_format($Grand_Deduction); ?></strong>
						<hr>


						<table>
							<tr>
								<td>
									<strong>Total NET PAY: &nbsp; </strong> <strong style="font-size:16px"><?php

																											$Grand_Net = ($Grand_Basic + $Grand_Earning + $Grand_Inc_Earning) - $Grand_Deduction;
																											echo number_format($Grand_Net); ?></strong>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>

									<?php if (isset($_POST['save_payslip'])) { ?>

										<button class="btn btn-success btn btn-sm" type="submit" name="send_pay" id="send_pay">Send Payslip to staff emails</button>
										&nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp;
										<a href="index.php?sal=<?php echo $Ecode ?>" class="btn btn-warning btn btn-sm">Close</a>


									<?php } else { ?>

										<button class="btn btn-info btn btn-sm" type="submit" name="save_payslip" id="save_payslip">Save Payslip</button>
										&nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp;
										<a href="index.php?sal=<?php echo $Ecode ?>" class="btn btn-warning btn btn-sm">Cancel</a>

									<?php } ?>



									<input type="hidden" name="month2" value="<?php echo $month; ?>">
									<input type="hidden" name="year2" value="<?php echo $year; ?>">
									<input type="hidden" name="start2" value="<?php echo $start; ?>">
									<input type="hidden" name="end2" value="<?php echo $end; ?>">
									<input type="hidden" name="sal_type2" value="<?php echo $sal_type; ?>">
									<input type="hidden" name="part2" value="<?php echo $query_part; ?>">


								</td>
							</tr>
						</table>

				<?php } else {
						echo '<strong>No Records Found </strong>';
					}
				}
				?>
			</form>
		</div>
	</div>
</div>

</div>

<?php

function add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type)
{

	include("../Connections/Conn.php");

	$chk = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and descriptn='$descriptn' and pay_head='$pay_head' and month='$month' and year='$year' and date_range='$date_range'");
	if ($chk->rowCount() == 0) {

		$stmt = $db->prepare("INSERT INTO hrpayslip(ECode, descriptn, pay_head, amount, month, year, date_range, days, generate_by, generate_date) 
						VALUES (:Ecode, :descriptn, :pay_head, :amount, :month, :year, :date_range, :days, :generate_by, :generate_date)");

		$stmt->bindParam(':Ecode', $Ecode);
		$stmt->bindParam(':descriptn', $descriptn);
		$stmt->bindParam(':pay_head', $pay_head);
		$stmt->bindParam(':amount', $amount);
		$stmt->bindParam(':month', $month);
		$stmt->bindParam(':year', $year);
		$stmt->bindParam(':date_range', $date_range);
		$stmt->bindParam(':days', $days);
		$stmt->bindParam(':generate_by', $_SESSION['fullname']);
		$stmt->bindParam(':generate_date', $setdatetime);

		$stmt->execute();

		///-----------------------------------------------------------		
		if ($bal != 'always' and ($pay_head_type == 'Earnings' or $pay_head_type = 'deductions')) {
			$duration = $duration - 1;
			$stmt = "UPDATE hred_details_sal SET duration='$duration' WHERE sn='$row_sn'";
			$db->exec($stmt);
		}
		////////////----------------------------------------------------

		if ($bal != 'always' and $pay_head_type == 'Earnings_Income') {
			$duration = $duration - 1;
			$stmt = "UPDATE hred_income_services SET duration='$duration' WHERE sn='$row_sn'";
			$db->exec($stmt);
		}
	}
}


?>


<div class="modal inmodal fade" id="confirm_payslip_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Confirm Payslip</h4>
			</div>
			<div class="modal-body" id="confirm_payslip_body">
			</div>

		</div>
	</div>