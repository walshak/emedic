<?php

if ($sal_type == 'full') {
    $ts = strtotime("$month $year");
    $days = date('t', $ts);
} elseif ($days != '') {
    // calculate days worked
    $days = $_POST['days'];
} else {
    // / part

    date_default_timezone_set('Africa/Lagos');
    $date1 = date_create($start);
    $date2 = date_create($end);
    $diff = date_diff($date1, $date2);
    $days = $diff->format('%a');
}

// / get the name
$stmt = $db->query("SELECT u.fullname,e.JoiningDate,e.Department,e.BankName,e.AccountNo,e.username FROM admin_users as u inner join hremp as e on u.EmployeeCode=e.EmployeeCode WHERE u.EmployeeCode='$Ecode'");
$rwxx = $stmt->fetch(PDO::FETCH_ASSOC);
$emplname = $rwxx['fullname'];
$username = $rwxx['username'];
// /===============================================================================================

$setdate = date('Y-m-d H:i:s');
$total_earnings = 0;
$total_deduction = 0;

// // BASIC SALARY
$stmt = $db->query("SELECT flat_percent_value, flat_percent_value_gross FROM hred_details_sal WHERE employee_no='$Ecode' and EorD='B' and flat_percent_value>0");
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $basic_sal = 0;

    $gross_sal = $row['flat_percent_value_gross'];

    if ($sal_type == 'days') {
        $basic_sal = $row['flat_percent_value'];
        $ts = strtotime("$month $year");
        $no_days_mnths = date('t', $ts);
        $pay_per_day = round($basic_sal / $no_days_mnths);
        $basic_sal = $pay_per_day * $days;
    } else {
        $basic_sal = $row['flat_percent_value'];
    }

    $amount = $basic_sal;
    if ($save_mode == 'yes' and $basic_sal > 0) {
        $descriptn = 'Basic Salary';
        $pay_head = 'B';
        $duration = '';
        $bal = '';
        $row_sn = '';
        $pay_head_type = '';
        $sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
    }
}
// //================= END BASIC SALARY
if ($basic_sal > 0) {
    echo '<strong style="font-size:16px">Basic Salary: </strong><strong  style="font-size:18px">'.number_format($basic_sal).'</strong><hr>';
} else {
    $basic_sal = 0;
}

// // EARNINGS
$stmt = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$Ecode' and EorD='E' and flat_percent_value>0 and (bal='always' or duration>=1)");
if ($stmt->rowCount() > 0) { ?>

	<div style="width:400px; ">
		<h3>EARNINGS / ALLOWANCES</h3>
		<table class="table table-bordered" width="50%">
			<thead>
				<tr>
					<th width="40%">Description</th>
					<th width="10%">Earnings</th>
				</tr>
			</thead>
			<tbody>
				<?php
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
            $earning = $percent * $basic_sal;
            $total_earnings = $total_earnings + $earning;
        }

        if ($rwx['flat_percent'] == 'Percent_Of_Gross') {
            $percent = $p / 100;
            $earning = $percent * $gross_sal;
            $total_earnings = $total_earnings + $earning;
        }

        if ($rwx['flat_percent'] == 'Flat') {
            $total_earnings = $total_earnings + $p;
            $earning = $p;
        }
        $amount = $earning;
        if ($save_mode == 'yes') {
            $sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
        }
        ?>
					<tr>
						<td><?php echo $rwx['descriptn']; ?></td>
						<td align="right"><?php echo number_format($earning); ?></td>
					</tr>
				<?php	} ?>

				<tr>
					<td align="right"><strong>Total Earnings: </strong></td>
					<td align="right"><strong><?php echo $total_earnings; ?></strong> </td>
				</tr>
			</tbody>
		</table>
	</div>
<?php	} ?>

<form action="index.php?sal=<?php echo $Ecode; ?>" method="post">

	<?php

    // // INCOMES EARNINGS

    $stmt = $db->query("SELECT * FROM hred_details WHERE employee_no='$Ecode' and flat_percent_value>0 and service_type_earn='All Hospital Services' and (bal='always' or duration>=1)");

if ($stmt->rowCount() > 0) { ?>

		<h3>EARNING GENERATED SERVICES</h3>
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th width="5%"></th>
					<th width="40%">Description</th>
					<th width="5%">Qty</th>
					<th width="5%">H/P</th>
					<th width="5%">Amt</th>
					<th width="5%">Earn</th>
					<th width="12%">Date</th>
				</tr>
			</thead>
			<tbody>

			<?php
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // /ALL SERVICES HERE
    $fp = $row['flat_percent'];
    $fp_value = $row['flat_percent_value'];
    $duration = $row['duration'];
    $bal = $row['bal'];
    $row_sn = $row['sn'];
    $part = '';
    include_once 'serv_part.php';
} else {
    // // SPECIFY AND CATGORY ////
    $earning_serv = 0;
    ?>

				<h3>EARNING FROM INCOME GENERATED</h3><strong style="color:#F33">Un-check to remove item below.</strong>
				<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th width="5%"></th>
							<th width="40%">Description</th>
							<th width="5%">Qty</th>
							<th width="5%">H/P</th>
							<th width="5%">Amt</th>
							<th width="5%">Earn</th>
							<th width="12%">Date</th>
						</tr>
					</thead>
					<tbody>

						<?php $n = 1;
    $stmt_cat = $db->query("SELECT * FROM hred_income_services WHERE employee_no='$Ecode' and flat_percent_value>0 and type='Category' and (bal='always' or duration>=1)");

    if ($stmt_cat->rowCount() > 0) {
        while ($row = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
            // / loop thru category
            $fp = $row['flat_percent'];
            $fp_value = $row['flat_percent_value'];
            $descriptn = $row['descriptn'];
            $duration = $row['duration'];
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
                $part = '';
            }

            include 'serv_part.php';
        }
    }

    // ///--------------------------- Specify
    $stmt_item = $db->query("SELECT * FROM hred_income_services WHERE employee_no='$Ecode' and flat_percent_value>0 and type='Specify' and (bal='always' or duration>=1)");
    // echo $stmt_item->rowCount();

    if ($stmt_item->rowCount() > 0) {
        while ($row = $stmt_item->fetch(PDO::FETCH_ASSOC)) {
            $fp = $row['flat_percent'];
            $fp_value = $row['flat_percent_value'];
            $descriptn = $row['descriptn'];
            $duration = $row['duration'];
            $bal = $row['bal'];
            $row_sn = $row['sn'];

            $part = "item_services='$descriptn' and ";
            include 'serv_part.php';
        }
    } else {
        $no_income = 1;
    }
    $inv_count = $n;

    ?>

						<tr>
							<td></td>
							<td align="right"><strong>Total Earnings (Income)</strong></td>
							<td colspan="4" align="right">

								<?php if ($confirm_mode == '1') { ?>
									<strong><?php echo number_format($earning_serv); ?></strong>
								<?php } ?>

								<input type="text" name="earning_income" id="earning_income" align="right"
									readonly="readonly" style="font-size:14px; outline:none; background:none; background-color:transparent; border: 0px solid;">
							</td>
							<td></td>
							<td></td>
						</tr>
					</tbody>
				</table>
			<?php
}

// // DEDUCTIONS
$stmt = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$Ecode' and EorD='D' and flat_percent_value>0 and (bal='always' or duration>=1)");
if ($stmt->rowCount() > 0) { ?>

				<div style="width:400px; ">
					<h3>DEDUCTIONS</h3>
					<table class="table table-bordered" width="50%">
						<thead>
							<tr>
								<th width="40%">Description</th>
								<th width="10%">Deductions</th>
							</tr>
						</thead>
						<tbody>
							<?php
                    $total_deduction = 0;
    $duration = 0;
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
            $deduct = $percent * $basic_sal;
            $total_deduction = $total_deduction + $deduct;
        }

        if ($rwx['flat_percent'] == 'Percent_Of_Gross') {
            $percent = $p / 100;
            $deduct = $percent * $gross_sal;
            $total_deduction = $total_deduction + $deduct;
        }
        if ($rwx['flat_percent'] == 'Flat') {
            $total_deduction = $total_deduction + $p;
            $deduct = $p;
        }

        $amount = $deduct;
        if ($save_mode == 'yes') {
            $sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
        }

        ?>
								<tr>
									<td><?php echo $rwx['descriptn']; ?></td>
									<td align="right"><?php echo number_format($deduct); ?></td>
								</tr>
							<?php	} ?>
							<tr>
								<td align="right"><strong>Total Deductions: </strong></td>
								<td align="right"><strong><?php echo $total_deduction; ?></strong> </td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php	} ?>




			</div>

			<?php

            if (isset($_POST['save_payslip'])) {
                header("location:index.php?Ecode=$Ecode/$month/$year/$date_range&lip");
            }
?>


			<?php if ($no_income == 0) { ?>

				<div class="col-lg-4">

					<strong style="font-size:14px">Pay Advice for <?php echo $month.', '.$year; ?></strong><br>
					<small>Dates: <?php echo date('d M, y', strtotime($start)).' to '.date('d M, y', strtotime($end)); ?></small> &nbsp; &nbsp; | &nbsp; &nbsp;
					<strong><strong>Days</strong> / <?php echo $days; ?></strong><br><br>
					<strong><strong>Salary</strong>: <?php echo $sal_type; ?></strong>
					<hr>
					<table class="table table-bordered" width="100%" style="font-size:14px">
						<tr>
							<td colspan="2"><strong><?php echo $emplname; ?></strong></td>
						</tr>
						<tr>
							<td><strong>Emp No:</strong></td>
							<td width="60%"><?php echo $Ecode; ?></td>
						</tr>

						<tr>
							<td colspan="2"><strong>Department:</strong><BR><?php echo $rwxx['Department']; ?></td>
						</tr>

						<tr>
							<td><strong>Date Engaged</strong>:</td>
							<td width="60%"><?php if ($rwxx['JoiningDate'] == '') {
							    echo '';
							} else {
							    echo date('d M, Y', strtotime($rwxx['JoiningDate']));
							} ?></td>
						</tr>
						<tr>
							<td colspan="2"><strong>Pay Point:</strong><br><?php echo $rwxx['BankName'].' [ '.$rwxx['AccountNo'].' ]'; ?></td>
						</tr>
					</table>

				</div>

			<?php } ?>

			</div>
			<hr>
			<?php $Total_total_earnings = $earning_serv + $total_earnings + $basic_sal; ?>
			<table width="100%">
				<tr>
					<td><strong>Earnings:</strong> &nbsp;<strong style="font-size:18px;"><?php echo number_format($Total_total_earnings); ?></strong></td>
					<td><strong>Deductions:</strong> &nbsp; <strong style="font-size:18px"><?php echo number_format($total_deduction); ?></strong></td>
					<td><strong>Net Pay:</strong> &nbsp; <?php $net = $Total_total_earnings - $total_deduction;
echo '<strong style="font-size:18px">'.number_format($net).'</strong>'; ?></td>

					<?php if ($confirm_mode != '1') { ?>
						<td align="right"><strong>Net Pay (After Adjustment):</strong></td>
						<td>
							<input type="text" name="totalcost" id="totalcost" class=" input-sm form-control"
								readonly="readonly" style="font-size:18px; font-weight:bold; outline:none; background:none; background-color:transparent; border: 0px solid;">
						</td>
					<?php } ?>

				</tr>
			</table>