<?php

if (isset($_POST['hos_no'])) {
	$hos_no = $_POST['hos_no'];
	$getvalue = $_POST['getvalue'];
	$auth_code_post = $_POST['auth_code'];
	$cpt = $_POST['cpt'];
	$special_package = $_POST['special_package'];

	// Prepare statement for enrollee query
	$stmt = $db->prepare("
        SELECT e.*, i.insurance_name, i.interest, i.payment_mode, i.services_access, i.insurance_type 
        FROM enrollee AS e 
        INNER JOIN insurance_tbl AS i ON e.hmo_no = i.insurance_no 
        WHERE hospital_no = :hos_no AND status = 'active'
    ");
	$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() == 0) {
		// No enrollee found, handle this case appropriately
		// e.g., redirect or show a message
		// header("Location: dashboard.php");
		// exit;
	} else {
		$row_rstSelect_d = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	// Prepare statement for appointment query
	$stmt2 = $db->prepare("SELECT * FROM apptm WHERE hospital_no = :hos_no ORDER BY sn DESC LIMIT 1");
	$stmt2->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
	$stmt2->execute();

	if ($stmt2->rowCount() > 0) {
		$row_rstSelect = $stmt2->fetch(PDO::FETCH_ASSOC);
		$app_no = $row_rstSelect['appt_no'];
		$date_ap = $row_rstSelect['date_ap'];
		$ap_time = $row_rstSelect['ap_time'];
		$ap_type = $row_rstSelect['ap_type'];
		$auth_code = $row_rstSelect['auth_code'];
	} else {
		$date_ap = '';
		// Set other variables if needed, or leave undefined
	}
}

?>


<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Fee for service claim / Special package</h5>
			</div>

			<div class="ibox-content">

				<div class="deposit_reciept_customer">

					<div align="center">
						<div style="font:bold 18px 'Arial';"><?php echo $row_rstSelect_d['insurance_name']; ?></div>

						<?php echo $row_rstSelect_d['addr']; ?> <br><br>
						<div style="font:bold 14px 'Arial';"><?php echo 'FEE FOR SERVICE CLAIMS REPORT / SPECIAL PACKAGE'; ?></div>
						<div style="font:bold 18px 'Arial'; width:200px; position: absolute;right: 0px;"><?php
																											echo 'INV:' . $app_no; ?></div>
						<br></br>
					</div>
					<div align="left" style="font:bold 14px 'Arial';">
					</div>
					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr>
							<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
							<td width="50%" align="right"><img src="<?php if (file_exists(enrollee_p . $hos_no . '.' . 'jpg')) {
																		echo enrollee_p . $hos_no . '.' . 'jpg';
																	} else {
																		echo '../img/user_avatar_lg.png';
																	} ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar"></td>
						</tr>

					</table><br>
					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr bgcolor="#FFCC66" style="font-weight:100">
							<td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
							<td style="font:bold 14px 'Arial';" width="30%">Insurance Coverage</td>
							<td style="font:bold 14px 'Arial';" width="30%">Patient Name: </td>
						</tr>
						<tr>
							<td><?php echo '<i><strong>Hospital Number:</strong></i>' . ' ' . $row_rstSelect_d['hospital_no']; ?></td>
							<td><?php echo $row_rstSelect_d['nhis_no'] . ' (' . $row_rstSelect_d['insurance_type'] . ')'; ?></td>
							<td><?php echo $row_rstSelect_d['surname'] . ', ' . $row_rstSelect_d['fname'] . ' ' . $row_rstSelect_d['oname']; ?></td>
						</tr>
						<tr bgcolor="#FFCC66">
							<td style="font:bold 14px 'Arial';">Age (Yrs/Mnts): </td>
							<td style="font:bold 14px 'Arial';">Sex: </td>
							<td style="font:bold 14px 'Arial';">Report Dates: </td>
						</tr>
						<tr>
							<td><?php echo $row_rstSelect_d['age']; ?></td>
							<td><?php echo ucfirst($row_rstSelect_d['gender']); ?></td>
							<td><?php
								$from = $_POST['from'];
								$to = $_POST['to'];
								echo date('d M, Y', strtotime($from)) . ' - ' . date('d M, Y', strtotime($to));
								?></td>
						</tr>
					</table>

					<br></br>

					<?php

					if (!empty($_REQUEST['inv'])) {


						// ADD TO TABLE
						$pro_inv = $_REQUEST['inv'];
						$inv_id = $pro_inv[1];
						$break = explode("__", $inv_id);
						$sn = $break[0];


						$qury = $db->query("SELECT serv_group FROM patient_ap_services WHERE sn='$sn'");
						$row_s = $qury->fetch(PDO::FETCH_ASSOC);
						$serv_group = strtoupper($row_s['serv_group']);
					}

					?>

					<?php if ($special_package == 'special_package') { ?>
						<div align="center" style="font:bold 18px 'Arial';">SPECIAL PACKAGE SERVICE</div>
					<?php } else { ?>
						<div align="center" style="font:bold 18px 'Arial';"><?php echo  $serv_group; ?></div>
					<?php } ?>

					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<thead>
							<tr bgcolor="#CCCCCC">
								<th width="12%">Date</th>
								<th> Service Name </th>
								<th width="17%"> Category </th>
								<th width="10%"> Price </th>
								<th width="6%"> Qty </th>
								<th width="10%"> Amount(=N=) </th>
							</tr>
						</thead>
						<tbody>

							<?php


							if (!empty($_REQUEST['inv'])) {


								// ADD TO TABLE
								$pro_inv = $_REQUEST['inv'];
								for ($i = 0; $i < count($pro_inv); $i++) {

									$inv_id = $pro_inv[$i];
									$break = explode("__", $inv_id);
									$sn = $break[0];


									$qury = $db->query("SELECT * FROM patient_ap_services WHERE sn='$sn'");
									$row_s = $qury->fetch(PDO::FETCH_ASSOC);
							?>

									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo date('d, M Y', strtotime($row_s['date_entry'])); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row_s['item_services'];
																					if ($row_s['serv_group'] == 'Pharmacy' and $row_s['remarks']) {
																						echo '<br> [' . $row_s['remarks'] . ']';
																					}

																					?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row_s['cat_type']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php
																					if ($special_package == 'special_package') {
																						echo number_format($row_s['hosp_price'], 2, '.', ',');
																					} else {
																						echo number_format($row_s['claim_amt'], 2, '.', ',');
																					}
																					?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row_s['qty']; ?></td>
										<td style="border-bottom: 1px solid #ddd; text-align:right" bgcolor="#999999">
											<?php

											if ($special_package == 'special_package') {
												echo '<strong>' . number_format($row_s['pay'], 2, '.', ',') . '</strong>';
												$total_hmo_amt = $total_hmo_amt + $row_s['pay'];
											} else {
												echo '<strong>' . number_format($row_s['claim_amt'], 2, '.', ',') . '</strong>';
												$total_hmo_amt = $total_hmo_amt + $row_s['claim_amt'];
											}
											?>
										</td>
									</tr>
								<?php } ?>



								<tr>
									<td style="border-bottom: 1px solid #ddd; text-align:left" colspan="2"><strong style="font-size: 11px; color: #222222;">
											<?php echo 'Presentation Dates: ' . date('d M,Y', strtotime($from)) . ' - ' . date('d M, Y', strtotime($to)); ?>


										</strong></td>
									<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2"></td>
									<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2"><strong style="font-size: 15px; color: #222222;">
											&#8358;<?php echo  number_format($total_hmo_amt, 2, '.', ','); ?>
										</strong></td>
								</tr>

						</tbody>
					</table>

					<br>

				<?php

							} else {

								///echo 'sdsdsdsds';
							}
				?>



				</div>





				<div class="form_sep" align="right">
					<div class="pull-right" style="margin-right:100px;">
						<!-- <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary" value="Print"/>-->
						<button onclick="printdeposit('deposit_reciept_customer')">Print</button>

					</div>
				</div>



				<a href="index.php?claims=<?php echo $hos_no . '&A=' . '/' . $from . '/' . $to . '/' . '' . '/' . $cpt . '&' . $special_package; ?>" style="font-size:20px;"><button class="btn btn-danger btn-sm"><i class="icon-print"></i> Close</button></a>




			</div>

		</div>
	</div>

</div>




<script>
	function printdeposit(deposit_reciept) {
		var printWindow = window.open('', 'PRINTOUT', 'height=400,width=600');
		printWindow.document.write('<html><head><title>PRINTOUT</title></head><body>');
		printWindow.document.write(document.getElementsByClassName(deposit_reciept)[0].innerHTML);
		printWindow.document.write('</body></html>');
		printWindow.print();
		printWindow.close();
	}
</script>