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
		$dates = $_POST['dates'];
		$transc_type = $_POST['transc_type'];
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
			Insurance Status: <b><i><?= $insurance_type . ' ( ' . $insurance_name . ' )';  ?></i></b><br>
		<?php } ?>
		Hospital Statement For <?php echo  $patient_name . ' ( ' . $emr . ' )'  . '<br>' . 'Address / Phone: ' .  $address . ' / ' . $phone;
								?>

	</div>

	<hr>



	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
		<thead>
			<tr bgcolor="#CCCCCC">
				<th width="20%">Date </th>
				<th width="35%">Description </th>
				<th width="35%">Hospital Price </th>
				<th width="10%">Qty </th>
				<th width="15%">Amount </th>
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
				$t_charge = $t_charge + $add_charge;
				$t_discount = $t_discount + $discount;
				$t_pay = $t_pay + $pay;

			?>
				<tr class="record">
					<td style="border-bottom: 1px solid #ddd;"><?php echo date('d M, Y', strtotime($transact_date)); ?> </td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo $item_services; ?></td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($hosp_price, 2); ?></td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo $qty; ?></td>
					<td style="border-bottom: 1px solid #ddd;"><?php echo $pay; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>




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