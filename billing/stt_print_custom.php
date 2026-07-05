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


		?>
		<?php if ($insurance_type == 'Family') { ?>
			Insurance Status: <b><i><?= $insurance_name;  ?></i></b><br>
		<?php }  ?>

		<?php
		if ($report_type == 'WRITE-OFF') { ?>
			WRITE-OFF
		<?php } else { ?>
			Hospital Statement For <?php echo  $patient_name . ' ( ' . $emr . ' )'  . '<br>' . 'Address / Phone: ' .  $address . ' / ' . $phone;
								} ?>
		<?php echo '<br>PERIOD: [' . date('d M, Y', strtotime($from)) . ' - ' . date('d M, Y', strtotime($to)) . ']'; ?>
	</div>
	<hr>

	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
		<thead>
			<tr bgcolor="#CCCCCC">
				<th width="15%">Date </th>
				<th width="30%">Description </th>
				<th width="15%">Deposit </th>
				<th width="15%">Withdrawal </th>
				<?php ///if($bal_visible==1){
				?>
				<th width="15%">Balance</th>
				<?php if ($insurance_type == 'Family') { ?>
					<th width="15%">EMR #</th>
				<?php } ?>
				<?php /// } 
				?>
			</tr>
		</thead>
		<tbody>

			<?php
			$SelectedItems = $_REQUEST['inv_x'];
			$total_dr_amt = 0;
			$total_cr_amt = 0;

			for ($i = 0; $i < count($SelectedItems); $i++) {
				$inv_id = $SelectedItems[$i];
				$break = explode("__", $inv_id);

				$item_services = $break[1];
				$dr_amt = $break[3];
				$cr_amt = $break[4];
				$bal = $break[5];
				$transact_date = $break[6];
				$hospital_no = $break[8];

				$total_dr_amt = $total_dr_amt + $dr_amt;
				$total_cr_amt = $total_cr_amt + $cr_amt;




			?>
				<tr class="record">
					<td style="border-bottom: 1px solid #ddd;"><?php echo date('d M, Y', strtotime($transact_date)); ?> </td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo $item_services; ?></td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($cr_amt, 2); ?></td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($dr_amt, 2); ?></td>
					<?php //if($bal_visible==1){
					?>

					<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($bal, 2); ?></td>

					<?php if ($insurance_type == 'Family') { ?>
						<td style="border-bottom: 1px solid #ddd;"><?= $hospital_no; ?></td>
				<?php }
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
			<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_cr_amt, 2, '.', ','); ?></strong></td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #ddd;">Total Withdrawal:</td>
			<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_dr_amt, 2, '.', ','); ?></strong></td>
		</tr>

		<tr>
			<td style="border-bottom: 1px solid #ddd;">Current Balance:</td>
			<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($total_cr_amt - $total_dr_amt, 2, '.', ','); ?></strong></td>
		</tr>
	</table>


	<?php
	//if ($_SESSION['h_code'] == 'mluth') { 
	?>
	Account Details:
	Bank Account <No class=":">1234567890</No>
	Bank Name <No class=":">First Bank</No>
	<?php
	///}
	?>


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
			&nbsp;Bank Name <No class=":">zenith Bank</No> <br>
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