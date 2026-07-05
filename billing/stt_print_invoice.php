<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('declared.php');
include("../inc/credit_current_balance.php");
?>


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

		///if ($_POST['type_of_print'] == "simple") {

		$SelectedItems = $_REQUEST['inv'];

		$consultation = '';
		$pharmacy = '';
		$nursing = '';
		$investigation = '';
		$medical = '';
		$others = '';
		$total_amt_dsc = 0;

		for ($i = 0; $i < count($SelectedItems); $i++) {

			$inv_id = $SelectedItems[$i];
			$break = explode("__", $inv_id);

			$item_services = $break[2];
			$amt = $break[1];
			$qty = $break[3];
			$date_entry = $break[4];
			$cr = $break[5];
			$cat_type = $break[6];
			$serv_group = $break[7];
			$dsc = $break[8];

			if ($dsc > 0) {
				$total_amt_dsc = $total_amt_dsc + $dsc;
				$amt = $amt - $dsc;
				$_dsc = "DSC:" . number_format($dsc);
			} else {
				$_dsc = null;
			}
			$total_amt = $total_amt + $amt;



			$BODY = '<tr class="record" style="font-size: 13px;"><td style="border-bottom: 1px solid #ddd;">' . $date_entry . '</td>
<td style="border-bottom: 1px solid #ddd;">' . $item_services . '</td><td style="border-bottom: 1px solid #ddd;">' . $qty . '</td>
<td style="border-bottom: 1px solid #ddd;">' . number_format($amt, 2, '.', ',') . '</td>
<td style="border-bottom: 1px solid #ddd;">' . $_dsc . '</td></tr>';



			if ($serv_group == 'Consultation' or $serv_group == 'Registration') {
				$consultation .= $BODY;
			} elseif ($serv_group == 'Pharmacy') {
				$pharmacy .= $BODY;
			} elseif ($serv_group == 'Medical Services') {
				$medical .= $BODY;
			} elseif ($serv_group == 'Nursing Services') {
				$nursing .= $BODY;
			} elseif ($serv_group == 'Laboratory' or $serv_group == 'Radiology') {
				$investigation .= $BODY;
			} else {
				$others .= $BODY;
			}
		}
		///}


		$report_type = $_POST['report_type'];
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
		Hospital Statement For <?php echo  $patient_name . ' ( ' . $emr . ' )'  . '<br>' . 'Address / Phone: ' .  $address . ' / ' . $phone;


								?><BR>
		<strong>[ INVOICE SERVICES ]</strong>
	</div>

	<hr>


	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 18px;text-align:left;width : 100%;">
		<thead>
			<tr bgcolor="#CCCCCC">
				<th width="30%">Date </th>
				<th width="40%">Description </th>
				<th width="10%">Quantity </th>
				<th width="20%">Amount </th>
				<th width=""></th>

			</tr>
		</thead>
		<tbody>


			<?php



			if ($consultation != '') {
				echo '<tr><td colspan="4"><H3>CONSULTATION</H3></td></tr>' . $consultation;
			}
			if ($pharmacy != '') {
				echo '<tr><td colspan="4"><H3>PHARMACY</H3></td></tr>' . $pharmacy;
			}

			if ($investigation != '') {
				echo '<tr><td colspan="4"><H3>INVESTIGATION</H3></td></tr>' . $investigation;
			}

			if ($medical != '') {
				echo '<tr><td colspan="4"><H3>MEDICAL SERVICES</H3></td></tr>' . $medical;
			}

			if ($nursing != '') {
				echo '<tr><td colspan="4"><H3>NURSING SERVICES</H3></td></tr>' . $nursing;
			}
			if ($others != '') {
				echo '<tr><td colspan="4"><H3>OTHER SERVICES</H3></td></tr>' . $others;
			}

			?>

			<?php /*?>			 <?php 
			
			/// $row['sn'] .'__' .$pay.'__'. $row['item_services'] .'__'. $row['qty'].'__'. $date_entry .'__'. $cr;
			 	$SelectedItems = $_REQUEST['inv'];
						
								
   				for($i=0;$i<count($SelectedItems);$i++){ 
						$inv_id = $SelectedItems[$i];
					 	$break=explode("__",$inv_id); 
						
									$item_services=$break[2]; 
									$amt=$break[1];
									$qty=$break[3];
									$date_entry=$break[4];
									$cr=$break[5];
									$cat_type=$break[6];

							$total_amt=$total_amt+$amt;
							///$total_cr_amt=$total_cr_amt+$cr_amt;
							
				?>
			  <tr class="record" style="font-size: 13px;">
				<td style="border-bottom: 1px solid #ddd;"><?php echo $date_entry;?> </td>
				<td style="border-bottom: 1px solid #ddd;"><?php echo $item_services; ?></td>
				<td style="border-bottom: 1px solid #ddd;"><?php echo $qty; ?></td>
                <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($amt, 2, '.', ','); ?></td>
                <td style="border-bottom: 1px solid #ddd;"></td>
			  </tr>
				<?php } ?><?php */ ?>


			<tr class="record" style="font-size: 18px;">
				<td style="border-bottom: 1px solid #ddd;"></td>
				<td style="border-bottom: 1px solid #ddd;"></td>
				<td style="border-bottom: 1px solid #ddd;">Total:</td>
				<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($total_amt, 2);
															if ($total_amt_dsc > 0) {
																echo "<BR>DISCOUNT: " . number_format($total_amt_dsc);
															} ?></td>
			</tr>

		</tbody>
	</table>

	<div style="font-size: 16px;">
		<?php echo $_SESSION['billing_remarks']; ?>
	</div>

</div>



<div class="col-sm-12">
	<br><br>
	<a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="fa fa-print"></i>&nbsp;Full Print</button></a>

	<?php if ($_POST['type_of_print'] == "simple") { ?>
		&nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp
		<a href="../inc/printout2.php?invoice=<?php echo $emr; ?>&name=<?php echo $patient_name; ?>" class="btn btn-success btn-sm">Print </a>
	<?php } ?>

	&nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp
	<button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='pacct.php?emr=<?php echo $emr . '&pay'; ?>'">Reset</button>

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