<?php

if (isset($_GET['services'])) {

	$sn = $_GET['services'];
	$remarks = '';
	$tag = 'auto';
	$updateSQL = "UPDATE patient_ap_services SET tag=:tag, remarks=:remarks WHERE sn=:sn";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':tag', $tag, PDO::PARAM_STR);
	$sql->bindParam(':remarks', $remarks, PDO::PARAM_STR);
	$sql->bindParam(':sn', $sn, PDO::PARAM_STR);
	//$sql->execute();
	if ($sql->execute()) {
		$error_status = 2;
		$error_msg = 'Success : Auto Service Added Successfully!';
	} else {
		$error_status = 1;
		$error_msg = 'Error : Save Not Successfully';
	}
}

if (isset($_GET['auto_cancel'])) {

	$sn = $_GET['auto_cancel'];
	$remarks = '';
	$tag = '';
	$updateSQL = "UPDATE patient_ap_services SET tag=:tag, remarks=:remarks WHERE sn=:sn";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':tag', $tag, PDO::PARAM_STR);
	$sql->bindParam(':remarks', $remarks, PDO::PARAM_STR);
	$sql->bindParam(':sn', $sn, PDO::PARAM_STR);
	//$sql->execute();
	if ($sql->execute()) {
		$error_status = 2;
		$error_msg = 'Success : Auto Service Added Successfully!';
	} else {
		$error_status = 1;
		$error_msg = 'Error : Save Not Successfully';
	}
}



if (isset($_GET['del_service'])) {
	$sn = $_GET['del_service'];
	$updateSQL = "DELETE FROM patient_ap_services WHERE sn=:sn";
	$stmt_22 = $db->prepare($updateSQL);
	$stmt_22->bindParam(':sn', $sn, PDO::PARAM_STR);
	$stmt_22->execute();
}

if (isset($_GET['del_cr'])) {
	$sn = $_GET['del_cr'];
	$cr = '0';
	$updateSQL = "UPDATE patient_ap_services SET cr=:cr WHERE sn=:sn";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':cr', $cr, PDO::PARAM_STR);
	$sql->bindParam(':sn', $sn, PDO::PARAM_STR);
	$sql->execute();
}


if (isset($_POST["add_services"])) {

	// drug substitute
	$interest = ($_POST['interest']);
	$add_minus = ($_POST['add_minus']);
	$payment_mode = ($_POST['payment_mode']);
	$insurance_no = ($_POST['insurance_no']);
	$insurance_type = ($_POST['insurance_type']);
	$insurance = ($_POST['insurance_type']);
	$service_name = ($_POST['service_name']);
	$hospital_no = ($_POST['hos_no']);
	$hos_no = ($_POST['hos_no']);
	$new_qty = ($_POST['qty']);
	$setdate = date("Y-m-d H:i:s");
	$appt_no = $_POST['app_no'];
	$dept_id = $_SESSION['dept_id'];

	$sp_remarks = '';
	$stmt2 = $db->prepare("SELECT * FROM prices_table WHERE sn='$service_name'");
	$stmt2->execute();
	if ($stmt2->rowCount() > 0) {
		$row_serv = $stmt2->fetch(PDO::FETCH_ASSOC);
		$item_sn = $row_serv['sn'];
		$item_service = $row_serv['item_service'];
		$coverage = $row_serv['coverage'];
		$insurance_type = $row_serv['insurance_type'];
		$price_table = $row_serv['price_table'];
		$category = 'Nursing Services'; ///$row_serv['category'];		
		$hosp_price = $row_serv['hosp_price'];
		$nhis_price = $row_serv['nhis_price'];
		$ext_price = $row_serv['ext_price'];

		$part = explode("/", $coverage);
		$private = $part[0];
		$nhis = $part[1];

		$target_sn = $item_sn;
		$_tariff_table = "hmo_medical_tariff";
		include("../inc/price_calc.php");

		$amt_paying = $amt_paying * $new_qty;
		$claim_amt = $claim_amt * $new_qty;

		include("../inc/utilities.php");
		$invoice_no = INV();

		$qty = $new_qty;
		$remarks = 'Nil';
		$invoice_status = '1';
		$invoicedate = date('Y-m-d H:i:s');
		$invoice_by = $_SESSION['fullname'];
		$paystatus = '0';
		$cr = '1';
		//$hosp_price=$hosp_price_ap;
		$drug_status = '0';
		$dsp_by = $_SESSION['fullname'];
		$process_claim = 0;

		include("../inc/patient_ap_services.php");

		if ($showid > 0) {
			$error_status = 2;
			$error_msg = 'Success : Service Added Successfully!';
		} else {
			$error_status = 1;
			$error_msg = 'Error : Save Not Successfully';
		}
	}
}


if (isset($_POST["service_apply"])) {

	$past_rec = ($_POST['select_rpt_type']);
	$hos_no = ($_POST['hos_no']);
	$from_date = ($_POST['from_date']);
	$to_date = ($_POST['to_date']);


	header("location:patient_bill.php?hosp_no=$hos_no&Services&dd=$from_date/$to_date/$past_rec");
}

if (isset($_GET['dd'])) {

	///echo $_GET['dd'];

	$part = explode("/", $_GET['dd']);
	$from_date = $part[0];
	$to_date = $part[1];
	$past_rec = $part[2];
} else {
	$from_date = date("Y-m-d");
	$to_date = date("Y-m-d");
}

?>



<div class="row">
	<div class="col-sm-6 b-r">
		<form action="patient_bill.php?hosp_no=<?php echo $hospital_no; ?>&Services" method="POST">

			<table width="100%">
				<tr>
					<td width="60%" style="padding-right:10px; ">
						<div class="form_sep">
							<label>Nursing Service</label>
							<select name="service_name" class="input-sm chosen-select" style="width:350px;" required>
								<option selected="selected" value="">Search and Select Nursing Services</option>
								<?php
								$dept_id = $_SESSION['dept_id'];
								$stmt_22 = $db->prepare("SELECT sn,item_service FROM prices_table 
	WHERE hosp_price>0 and (price_table='Nursing Services' or dept = '$dept_id') order by item_service");
								///WHERE hosp_price>0 and (price_table='Nursing Services' or price_table='Medical Services') order by item_service");
								$stmt_22->execute();
								while ($row = $stmt_22->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $row["sn"]; ?>"><?php echo $row["item_service"]; ?></option>
								<?php } ?>
							</select>
						</div>


					</td>
					<td width="10%" style="padding-right:10px; ">
						<div class="form_sep">
							<label>Qty</label>
							<input type="number" id="qty" name="qty" class="form-control" data-required="true" min="1" value="1" required>
						</div>
					</td>
					<td>
						<label>.</label><br>
						<button class="btn btn-success" type="submit" name="add_services" id="">Add & Delivered</button>
					</td>
				</tr>
			</table>

			<input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
			<input type="hidden" name="hos_no" value="<?php echo $hospital_no; ?>" />
			<input type="hidden" name="insurance" value="<?php echo $patient_insurance; ?>" />
			<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />
			<input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
			<input type="hidden" name="interest" value="<?php echo $interest; ?>" />
			<input type="hidden" name="add_minus" value="<?php echo $add_minus; ?>" />
			<input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>" />

		</form>

	</div>


	<div class="col-sm-6">
		<h4>Search for Past Services</h4>
		<form action="patient_bill.php?hosp_no=<?php echo $hospital_no; ?>&Services" method="POST">
			<table>
				<tr>
					<td>
						<div class="form_sep" id="">
							<div class="input-daterange input-group" id="">
								<input type="date" class="input-sm form-control" name="from_date" value="<?php echo $from_date; ?>" />
								<span class="input-group-addon">to</span>
								<input type="date" class="input-sm form-control" name="to_date" value="<?php echo $to_date; ?>" />
							</div>
						</div>
					</td>
					<td>
						<div class="form_sep" id="">
							<select name="select_rpt_type" class="form-control" required>
								<option selected="selected" value="">Select type of Report</option>
								<option value="0">Invoiced/Payment Pending Only</option>
								<option value="1">Paid Services Only</option>
							</select>
						</div>
					</td>
					<td>
						&nbsp;&nbsp;<button class="btn btn-success btn btn-sm" type="submit" name="service_apply">Apply</button>
					</td>
				</tr>
			</table>

			<input type="hidden" name="hos_no" value="<?php echo $hospital_no; ?>" />
		</form>
	</div>
</div>




<hr>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox ">



			<?php

			$dept_id = $_SESSION['dept_id'];
			///$past_rec=null;

			if (isset($_GET['dd'])) {
				//// past payment

				if ($past_rec == 1) {
					$search_critera = "and paystatus='1'";
				} else {
					$search_critera = "and paystatus='0'";
				}

				/////echo $search_critera;

				$stmt_22 = $db->prepare("SELECT * FROM patient_ap_services 
WHERE hospital_no=:hospital_no and remarks!='auto_deduct' and remarks!='auto_deduct2' $search_critera and 
(remarks='' or remarks='nil' or remarks is NULL or tag='del') and date(date_entry) between '$from_date' and '$to_date'");
				//$stmt_22->bindParam(':app_no', $app_no, PDO::PARAM_STR);
				$stmt_22->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmt_22->execute();
			} else {

				///echo($dept_id);	
				$paystatus = '0';
				$stmt_22 = $db->prepare("SELECT * FROM patient_ap_services WHERE hospital_no=:hospital_no and paystatus=:paystatus and (cat_type='Nursing Services' or cat_type='Medical Services') and (remarks='' or remarks='nil' or remarks is null or tag='del')");
				$stmt_22->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmt_22->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
				$stmt_22->execute();
			}
			if ($stmt_22->rowCount() > 0) { ?>
				<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="15">
					<thead>
						<tr>
							<th data-toggle="true" width="10%">Invoice #</th>
							<th data-toggle="true" width="25%">Service Name</th>
							<th data-hide="phone,tablet" width="8%">Hosp/Price</th>
							<th data-hide="phone,tablet" width="8%">Pay/Claim</th>
							<th data-hide="phone,tablet" width="6%">Qty</th>
							<th data-hide="phone,tablet" width="18%">Date</th>
							<th data-hide="phone,tablet" width="10%">Entered By</th>
							<th data-hide="phone,tablet" width="55%"></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$bgcolor = "#F4F4F4";
						$colordecide = 1;
						$paying = 0;
						$claiming = 0;

						while ($row = $stmt_22->fetch(PDO::FETCH_ASSOC)) {
							$sn = $row['sn'];


							if ($colordecide % 2 == 0) {
								$bgcolor = "#F4F4F4";
							} else {
								$bgcolor = "#FFFFFF";
							}
						?>

							<tr bgcolor="<?php echo $bgcolor; ?>">
								<td><?php echo $row['invoice_no']; ?></td>
								<td><?php if ($patient_insurance == 'NHIS' and $row['access'] == 3) {
										echo '<strong style="color:#F00"> Private Service(100% payable)</strong><br>';
									}
									echo $row['item_services']; ?>



									<?php if ($row['tag'] == 'del') {
										echo '<strong style="color:#F00"><br>[Daily Post]</strong><br>';
									} ?>
									<?php
									if ($row['drug_status'] == 1) { ?>&nbsp; <i class="fa fa-check" style="color: blue;"></i> <?php } ?>

								<br>
								<?php if ($row['tag'] == 'auto') { ?>
									<a href="patient_bill.php?hosp_no=<?php echo $hospital_no; ?>&auto_cancel=<?php echo $sn; ?>&Services"
										onclick="return confirm('Are you sure you want to Cancel Auto Bill Daily ?')">
										<small style="color: red;">[ Cancel Auto ]</small></a>
								<?php } else { ?>

									<a href="patient_bill.php?hosp_no=<?php echo $hospital_no; ?>&services=<?php echo $sn; ?>&Services"
										onclick="return confirm('Are you sure you want to Set this Item to Auto Bill Daily ?')">
										<small style="color: blue;">[ Auto Bill Daily ]</small></a>
								<?php } ?>

								</td>
								<td><?php echo number_format($row['hosp_price'], 2, '.', ','); ?></td>
								<td><?php if ($row['pay'] > 0) {
										echo number_format($row['pay'], 2, '.', ',');
									}
									if ($row['claim_amt'] > 0) {
										echo number_format($row['claim_amt'], 2, '.', ',');
									}
									?></td>
								<td><?php echo $row['qty']; ?></td>
								<td><?php echo date('d M,Y', strtotime($row['date_entry'])) . '-' . date('h:i:s a', strtotime($row['date_entry'])); ?></td>
								<td><?php echo $row['prepared_by'];  ?></td>

								<td bgcolor="#CCFFFF">
									<div align="center">
										<?php if ($row['paystatus'] == 1 and $row['pay'] > 0) { ?>
											Paid
										<?php } elseif ($row['paystatus'] == 1 and $row['pay'] == 0 and $row['claim'] > 0) { ?>
											Posted
										<?php } elseif ($row['paystatus'] == 0) { ?>

											<?php if ($row['pay'] == 0 and $row['claim'] > 0) { ?>[ Post ]

										<?php } else { ?>
											[ Un-paid ]

											<?php if ($row['cr'] == 1) {
													echo '<strong style="color:red;">CR</strong>'; ?>

												<?php if ($_SESSION['unit_head'] == 1) { ?>
													<a href="patient_bill.php?hosp_no=<?php echo $hospital_no . '&del_cr=' . $row['sn']; ?>&Services" onclick="return(YNconfirm17())">[Del. CR]</a>
												<?php } ?>

												<?php }
											} ?>&nbsp;
												<?php if (($row['prepared_by'] == $_SESSION['fullname'] and $row['drug_status'] == 0)
													or $_SESSION['unit_head'] == 1
												) { ?>
													<a href="patient_bill.php?hosp_no=<?php echo $hospital_no . '&del_service=' . $row['sn']; ?>&Services" onclick="return(YNconfirm18())">[Delete]</a>
												<?php } ?>
											<?php } ?>
								</td>
							</tr>
						<?php $colordecide++;
							if ($row['paystatus'] == 0) {
								$claiming = $claiming + $row['claim_amt'];
								$paying = $paying + $row['pay'];
							}
						}

						?>

					</tbody>
				</table>
				<hr>

				<table width="100%">
					<tr>
						<td>
							<div align="right">
								<table style="background-color:#CCF;">
									<tr>
										<td>
											<div align="right"><strong>Insured Billed:</strong></div>
										</td>
										<td>&nbsp;&nbsp;</td>
										<td>
											<div align="right" style="font-size:20px; padding-right:10px; padding-top:10px;"><strong><?php echo  number_format($claiming, 2, '.', ','); ?></strong></div>
										</td>
									</tr>
									<tr>
										<td>
											<div align="right"><strong>Amount Paying:</strong></div>
										</td>
										<td>&nbsp;&nbsp;</td>
										<td>
											<div align="right" style="font-size:20px;  padding-right:10px;"><strong><?php echo  number_format($paying, 2, '.', ','); ?></strong></div>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>

			<?php

			} else { ?>
				<div style="font-size:13px;" align="center"><b>No Pending Nursing Services</b></div>
			<?php } ?>


		</div>
	</div>
</div>


<script>
	function YNconfirm17() {
		if (window.confirm('Do you want to remove Credit?')) {
			return true;
		} else
			return false;
	}

	function YNconfirm18() {
		if (window.confirm('Are you sure you want to Delete this item?')) {
			return true;
		} else
			return false;
	}
</script>