<div class="col-sm-12" id="content">
	<div align="left" style="font:bold 14px 'Arial';">
	</div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
		<tr>
			<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
			<td width="50%" align="right">
				<div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>
						<?php echo $_SESSION['h_name']; ?></strong></div> <br>
				<div style="font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
			</td>
		</tr>
	</table>
	<hr>

	<div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
		<?php
		$report_type = $_POST['report_type'];
		$search_part = $_POST['search_part'];
		$begin_financial = $_POST['begin_financial'];
		$transc_type = $_POST['transc_type'];
		$dates = $_POST['dates'];
		$emr = $_POST['emr'];
		$parts = explode("__", $dates);
		$from = $parts[0];
		$to = $parts[1];


		$stmt_en = $db->query("SELECT 
		i.insurance_name,
		i.interest,
		i.insurance_type,
		i.insurance_no,
		i.add_minus,
		e.surname,
		e.fname,
		e.oname,
		e.gender,
		e.addr,
		e.discount_set, 
		e.phone, 
		e.email 
		FROM enrollee as e 
		INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no 
		WHERE hospital_no='$emr' and status='active'");
		if ($stmt_en->rowCount() > 0) {

			$row = $stmt_en->fetch(PDO::FETCH_ASSOC);
			$insurance_name = $row['insurance_name'];
			$insurance = $row['insurance_name'];
			$interest = $row['interest'];
			$insurance_type = $row['insurance_type'];
			$insurance_no = $row['insurance_no'];
			$add_minus = $row['add_minus'];
			$patient_name = $row['surname'] . ', ' . $row['fname'] . ' ' . $row['oname'];
			$gender = $row['gender'];
			$address = $row['addr'];
			$discount_set = $row['discount_set'];
			$phone = $row['phone'];
			$email = $row['email'];
		}

		if ($insurance_type == 'Family') { ?>
			Insurance Status: <b><i><?= $insurance_name;  ?></i></b><br>
		<?php }
		if (in_array($report_type, ['WRITE-OFF', 'DELIVERED AS CREDIT'])) {
			echo $report_type . '<br>';
		} ?>
		Hospital Statement For <?php echo  $patient_name . ' ( ' . $emr . ' )'  . '<br>' . 'Address / Phone: ' .  $address . ' / ' . $phone;

								echo '<br>PERIOD: [' . date('d M, Y', strtotime($from)) . ' - ' . date('d M, Y', strtotime($to)) . ']'; ?>
	</div>
	<hr>
	<?php
	if ($transc_type == 'payment') { ?>

		<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
			<thead>
				<tr bgcolor="#CCCCCC">
					<th width="15%">Date </th>
					<th width="30%">Description </th>
					<th width="15%">Bank</th>
					<th width="15%">Amount Recieved</th>
					<th width="15%">Service Recieved</th>
					<th width="15%"></th>
				</tr>
			</thead>
			<tbody>

				<?php

				$search_part = "and account_no !='2121'  and date_entry2 BETWEEN '$from' and '$to'";
				$stmt = $db->query("SELECT * FROM chart_ledger WHERE hospital_no='$emr' $search_part order by sn");
				$n = 1;
				$dr_amt = 0;
				$cr_amt = 0;
				$Returned = 0;

				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {



					$cr_amt = $cr_amt + $row['cr_amt'];
					$dr_amt = $dr_amt + $row['dr_amt'];

					if ($n == 1) {
						$bal = $row['cr_amt'];
					} else {

						if ($row['cr_amt'] > 0) {
							$bal = $bal + $row['cr_amt'];
						} else {
							$bal = $bal - $row['dr_amt'];
						}
					}



				?>
					<tr class="record">
						<td style="border-bottom: 1px solid #ddd;"><?php echo date('d M, Y', strtotime($row['date_entry2'])); ?> </td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo $row['bank_name']; ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php

																	$Deposit = 'Deposit';
																	if (($row['item_services'] == 'Money Recieved' or
																		strpos($row['item_services'], $Deposit) === 0) and $row['dr_amt'] > 0) {
																		echo  number_format($row['dr_amt'], 2);
																		$total_dr_amt = $total_dr_amt + $row['dr_amt'];
																	} else {
																		echo  '-';
																	}
																	?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php
																	$total_cr_amt = $total_cr_amt + $row['cr_amt'];
																	echo  number_format($row['cr_amt'], 2); ?></td>



						<td style="border-bottom: 1px solid #ddd;">
							<?php
							///$total_cr_amt = $total_cr_amt + $row['cr_amt'];
							///echo  number_format($bal); 
							?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		<br>
		<table align="right" cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:right;width : 100%;">
			<tr class="">
				<td style="border-bottom: 1px solid #ddd;">Total Amount Recieved</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_cr_amt, 2, '.', ','); ?></strong></td>
			</tr>
			<tr>
				<td style="border-bottom: 1px solid #ddd;">Total Service Billed:</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_dr_amt, 2, '.', ','); ?></strong></td>
			</tr>
		</table>

	<?php } elseif (in_array($report_type, ['ap_services', 'WRITE-OFF', 'DELIVERED AS CREDIT'])) { ?>

		<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
			<thead>
				<tr bgcolor="#CCCCCC">
					<th width="15%">Delivered Date</th>
					<th width="27%">Description</th>
					<th width="27%">Hospital Price</th>
					<th width="8%">Qty</th>
					<th width="12%">Amount</th>
					<th width="12%">Entered</th>
				</tr>

			</thead>
			<tbody>

				<?php
				$SelectedItems = $_REQUEST['inv_x'];
				$t_discount = 0;
				$t_charge = 0;
				$t_pay = 0;
				for ($i = 0; $i < count($SelectedItems); $i++) {
					$inv_id = $SelectedItems[$i];
					$break = explode("__", $inv_id);
					$item_services = $break[1];
					$hosp_price = $break[2];
					$qty = $break[3];
					$transact_date = $break[4];
					$pay = $break[5];
					$discount = $break[6];
					$add_charge = $break[7];
					$payment_remarks = $break[8];
					$date_entry = $break[9];
					$t_charge = $t_charge + $add_charge;
					$t_discount = $t_discount + $discount;
					$t_pay = $t_pay + $pay;

				?>
					<tr class="record">
						<td style="border-bottom: 1px solid #ddd;"><?php echo date('d M, Y', strtotime($transact_date)); ?> </td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo $item_services;
																	if ($report_type == 'WRITE-OFF') {
																		echo '<br>' . $payment_remarks;
																	}

																	?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($hosp_price, 2); ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo $qty; ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($pay); ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo date('d M, Y', strtotime($date_entry)); ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>


		<br>


		<?php if ($current_balance < 0 && $t_pay > $current_bal) {
			$bbal = $t_pay - abs($current_balance);
		} ?>
		<table align="right" cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:right;width : 100%;">
			<tr class="">
				<td style="border-bottom: 1px solid #ddd;">Total Amount</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($t_pay, 2, '.', ','); ?></strong></td>
			</tr>
			<tr class="">
				<td style="border-bottom: 1px solid #ddd;">Current Amount</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($current_balance, 2, '.', ','); ?></strong></td>
			</tr>
			<tr class="">
				<td style="border-bottom: 1px solid #ddd;">Credit Difference</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($bbal, 2, '.', ','); ?></strong></td>
			</tr>



		</table>

	<?php } elseif (in_array($report_type, ['patient_acct', 'patient_acct_family'])) {
		if ($insurance_type == 'Family') {

			$stmt_en = $db->prepare("SELECT surname, fname, oname, hospital_no FROM enrollee WHERE hmo_no = :insurance_no");
			$stmt_en->execute(['insurance_no' => $insurance_no]);
			$patients = [];
			while ($row = $stmt_en->fetch(PDO::FETCH_ASSOC)) {
				$patients[$row['hospital_no']] = $row['surname'] . ', ' . $row['fname'] . ' ' . $row['oname'];
			}
		}

		$params = [':start' => $from, ':end' => $to];
		switch ($transc_type) {
			case 'patient_acct':
				$search_part = "AND account_no = '2121' AND patient_stt_status != '2' AND date_entry2 BETWEEN :start AND :end";
				$params[':emr'] = $emr;
				$bal_visible = 1;
				break;

			case 'patient_acct_family':
				$search_part = "AND account_no = '2121' AND patient_stt_status != '2' AND insurance_no = :insurance_no AND date_entry2 BETWEEN :start AND :end";
				$params[':insurance_no'] = $insurance_no;
				$bal_visible = 1;

				$hospital_nos = [];
				$stmt = $db->prepare("SELECT hospital_no FROM enrollee WHERE hmo_no = :insurance_no");
				$stmt->execute([':insurance_no' => $insurance_no]);
				$hospital_nos = $stmt->fetchAll(PDO::FETCH_COLUMN);
				break;
			default:
				$search_part = "AND ref_value LIKE :ref_value";
				$params[':ref_value'] = "%$transc_type%";
				$params[':emr'] = $emr;
				break;
		}

		// Final query
		if ($transc_type == 'patient_acct_family') {
			$sql = "SELECT * FROM chart_ledger WHERE hospital_no != '' $search_part ORDER BY sn";
			$stmt = $db->prepare($sql);
			$stmt->execute($params);
		} else {
			$sql = "SELECT * FROM chart_ledger WHERE hospital_no = :emr $search_part ORDER BY sn";
			$stmt = $db->prepare($sql);
			$stmt->execute($params);
		}

		$papaCondition = '';
		$params = [
			':start' => date('Y-01-01', strtotime($begin_financial)),
			':end'   => date('Y-m-d', strtotime('-1 day', strtotime($from)))
		];

		if ($insurance_type === 'Family') {
			// For Family insurance, use insurance_no only
			$whereClause = 'insurance_no = :insurance_no';
			$params[':insurance_no'] = $insurance_no;
		} else {
			// For all other insurance types, use hospital_no
			$whereClause = 'hospital_no = :emr';
			$params[':emr'] = $emr;
		}

		$sql = "
    SELECT 
        COALESCE(SUM(dr_amt), 0) AS TOTAL_DEBITS, 
        COALESCE(SUM(cr_amt), 0) AS TOTAL_CREDITS
    FROM chart_ledger 
    WHERE $whereClause
      AND account_no = '2121'
      AND patient_stt_status != '2'
      AND date_entry2 BETWEEN :start AND :end";

		$stmtb = $db->prepare($sql);
		$stmtb->execute($params);
		$result = $stmtb->fetch(PDO::FETCH_ASSOC);

		// Balance forward calculation
		$totalDebits  = $result['TOTAL_DEBITS'];
		$totalCredits = $result['TOTAL_CREDITS'];
		$bal_B_F      = $totalCredits - $totalDebits;
		$set          = ($totalDebits != 0 || $totalCredits != 0) ? 1 : null;


	?>

		<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
			<thead>
				<tr bgcolor="#CCCCCC">
					<?php if ($insurance_type == 'Family') { ?>
						<th width="8%">Date</th>
						<th width="13%">Bank</th>
						<th width="32%">Description</th>
						<th width="9%">Deposit</th>
						<th width="9%">Withdrawal</th>
						<th width="9%">Balance</th>
						<th width="20%">Name</th>
					<?php } else { ?>
						<th width="8%">Date</th>
						<th width="14%">Bank</th>
						<th width="47%">Description</th>
						<th width="10%">Deposit</th>
						<th width="10%">Withdrawal</th>
						<th width="10%">Balance</th>


					<?php } ?>
				</tr>

			</thead>
			<tbody>
				<?php if ($set == 1) { ?>
					<tr>
						<td style="border-bottom: 1px solid #ddd;"><?= date("d-m-Y", strtotime($from));; ?></td>
						<td style="border-bottom: 1px solid #ddd;"></td>
						<td style="border-bottom: 1px solid #ddd;">Opening Balance</td>
						<td style="border-bottom: 1px solid #ddd;">0.00</td>
						<td style="border-bottom: 1px solid #ddd;">0.00</td>
						<td style="border-bottom: 1px solid #ddd;"><?= number_format($bal_B_F, 2); ?></td>
						<?php if ($insurance_type == 'Family') { ?><td></td><?php } ?>
					</tr>

				<?php } ?>
				<?php
				$total_dr_amt = 0;
				$total_cr_amt = $bal_B_F;
				$n = 1;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

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
					}

					if (strpos($item_services, "Returned/") !== false) {
						$Returned = $Returned + $row['cr_amt'];
					}


					$total_dr_amt = $total_dr_amt + $row['dr_amt'];
					$total_cr_amt = $total_cr_amt + $row['cr_amt'];
					$hospital_no = $row['hospital_no'];

				?>



					<tr class="record">
						<td style="border-bottom: 1px solid #ddd;"><?php echo  date("d-m-Y", strtotime($row['date_entry'])); ?> </td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo  $row['bank_name']; ?> </td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo $item_services; ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['cr_amt'], 2); ?></td>
						<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['dr_amt'], 2); ?></td>
						<?php //if($bal_visible==1){
						?>

						<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($bal, 2); ?></td>

						<?php if ($insurance_type == 'Family') { ?>
							<td style="border-bottom: 1px solid #ddd;"><?php echo $patients[$hospital_no]; ?></td>
					<?php }

						$n += 1;
					} ?>
					</tr>
					<?php ///} 
					?>
			</tbody>
		</table>

		<br>
		<table align="right" cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:right;width : 100%;">
			<tr class="">
				<td style="border-bottom: 1px solid #ddd;">Total Deposit</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php $total_cr_amt2 = $total_cr_amt - $Returned;
																	echo number_format($total_cr_amt2, 2, '.', ','); ?></strong></td>
			</tr>
			<tr>
				<td style="border-bottom: 1px solid #ddd;">Total Withdrawal:</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_dr_amt - $Returned, 2, '.', ','); ?></strong></td>
			</tr>

			<tr>
				<td style="border-bottom: 1px solid #ddd;">Current Balance:</td>
				<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_cr_amt - $total_dr_amt, 2, '.', ','); ?></strong></td>
			</tr>
		</table>






	<?php } ?>


	<?php

	// Assuming $db is your PDO database connection object

	if ($insurance_type == 'Family' || $insurance_type == 'Corporate') {
		// Prepare SQL query for Family or Corporate insurance types
		$sql = "SELECT bal FROM chart_ledger WHERE insurance_no = :insurance_no ORDER BY sn DESC LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':insurance_no', $insurance_no, PDO::PARAM_STR); // Assuming $insurance_no is a string
	} else {
		// Prepare SQL query for other cases
		$sql = "SELECT bal FROM chart_ledger WHERE hospital_no = :hospital_no ORDER BY sn DESC LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':hospital_no', $emr, PDO::PARAM_STR); // Assuming $emr is a string
	}

	// Execute the query
	$stmt->execute();

	// Check if there are rows returned
	if ($stmt->rowCount() > 0) {
		// Fetch the result
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$current_bal = $row['bal'];
	} else {
		// Handle case where no rows are returned
		$current_bal = 0; // Or set to a default value as per your application's logic
	}



	?>

	<?php
	if ($_SESSION['h_code'] == 'mluth') {
	?>
		<h4>
			Account Details:
			&nbsp;Bank Account <No class=":">1310751048</No>
			&nbsp;Bank Name <No class=":">zenith Bank</No><br>
			&nbsp;Bank Account <No class=":">0007455996</No>
			&nbsp;Bank Name <No class=":">Tajabank Bank</No>
		</h4>
	<?php
	}
	?>

</div>




<div class="col-sm-12">
	<br><br>
	<a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="fa fa-print"></i>&nbsp;Print</button></a>

	&nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp
	<button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='pacct.php?emr=<?php echo $emr . '&stt'; ?>'">Reset</button>

</div>



<script>
	function Clickheretoprint() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
		var content_vlue = document.getElementById("content").innerHTML;

		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();
	}
</script>