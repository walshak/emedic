<?php ///include("../Connections/Conn.php");
?>

<?php
session_start();
$drug_reversal_period = $_SESSION['drug_reversal_period'];


/////////////////////////====================================================================================		
///refill_drug_id

if (isset($_POST["refill_drug_id"])) {
	$part_part = $_POST["refill_drug_id"];
} elseif (isset($_POST["dsp_oncredit_id"])) {
	$part_part = $_POST["dsp_oncredit_id"];
} elseif (isset($_POST["reverse_id"])) {
	$part_part = $_POST["reverse_id"];
}

///echo $row['sn'] . '/' . $hos_no. '/' . $app_no .'/'. $names .'/IN/P/'. $adm_status .'/'. $row['paystatus']

$part = explode("/", $part_part);
$sale_sn = $part[0];
$hos_no = $part[1];
$app_no = $part[2];
$names = $part[3];
$EX_or_IN = $part[4];
$where = $part[5];
$admstatus = $part[6];
$paystatus = $part[7];

?>
<div align="center"><a href="index.php?presc&hos_no=<?= $hos_no; ?>" class="btn btn-danger btn btn-xs">Close</a></div>
<?php


$stmt = $db->prepare("SELECT pay, claim_amt, item_services, qty, drug_sn, remarks 
							  FROM patient_ap_services 
							  WHERE sn = :sale_sn AND hospital_no = :hos_no");
$stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
	$row_drug = $stmt->fetch(PDO::FETCH_ASSOC);
	$pay = $row_drug['pay'];
	$claim_amt = $row_drug['claim_amt'];
	if ($claim_amt > 0) {
		$amt = $claim_amt;
	} else {
		$amt = $pay;
	}
	$item_services = $row_drug['item_services'];
	$qty = $row_drug['qty'];
	$old_qty = $row_drug['qty'];
	$drug_sn = $row_drug['drug_sn'];
	$remarks = $row_drug['remarks'];

	$dept_id = $_SESSION['dept_id'];
	$stmt = $db->prepare("SELECT bal FROM stock_table_inven 
								  WHERE stock_sn = :drug_sn AND cust_patient_id = :dept_id 
								  ORDER BY sn DESC LIMIT 1");
	$stmt->bindParam(':drug_sn', $drug_sn, PDO::PARAM_STR);
	$stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		$rw = $stmt->fetch(PDO::FETCH_ASSOC);
		$current_qty = $rw['bal'];
	} else {
		$current_qty = 0;
		/*
		$stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :drug_sn");
		$stmt->bindParam(':drug_sn', $drug_sn, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$rw = $stmt->fetch(PDO::FETCH_ASSOC);
			$current_qty = $rw['qty'];
		} else {
			$current_qty = 0;
		}

		*/
	}
}
?>

<table width="100%">
	<tr>
		<td>
			<strong>Current Qty: <?php echo $current_qty; ?></strong><br>
			<h3><?php echo $item_services; ?></h3>
		</td>
		<td>
			Patient Quantity/&nbsp; <strong><?php echo $qty; ?></strong>
		</td>
		<td>
			Amount/&nbsp; <strong><?php echo $amt; ?></strong>
	</tr>
</table>

<hr>

<?php

if (isset($_POST["refill_drug_id"])) { ?>

	<form action="index.php?<?php echo $target . $hos_no; ?>" method="">

		<div class="form_sep">
			<label for="reg_input_no" class="req">Enter Refill Quantity</label>
			<input type="number" id="refill_qty" name="refill_qty" class="form-control" onkeyup="sum();" min="1" max="<?php echo $current_qty; ?>" required>
		</div>

		<div class="form_sep">


			<button type="button" class="btn btn-success btn btn-sm" id="refill_btn"
				onclick="refill_drug_now('<?php echo 'dispense_all_cr'; ?>','<?php echo $hos_no; ?>')">Add Now </button>




		</div>


	<?php } ?>



	<?php if (isset($_POST["dsp_oncredit_id"])) { ?>



		<?php if ($current_qty >= $qty) { ?>
			<div class="form_sep">

				<button type="button" class="btn btn-danger btn btn-sm" id="dispense_oncredit_xternal"
					onclick="dispense_oncredit_xternal()">Dispense On/Credit</button>
			</div>
		<?php } else { ?>
			<strong style="font-size:14px; color:#F00">Insufficient quantity to dispense</strong>
		<?php } ?>

	<?php } ?>

	<?php if (isset($_POST["reverse_id"])) {
		$Err = 0;
		$status = '';
		////echo 'ldkdkdkdkdk';
		//// date entry if less than 24hrs 				
		$stmt22 = $db->prepare("SELECT transact_date, app_no, paystatus, pay, cr, hospital_no 
									   FROM patient_ap_services WHERE sn = :sale_sn");
		$stmt22->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
		$stmt22->execute();

		if ($stmt22->rowCount() > 0) {
			$rowx = $stmt22->fetch(PDO::FETCH_ASSOC);
			$transact_date = $rowx['transact_date'];
			$app_no = $rowx['app_no'];
			$paystatus = $rowx['paystatus'];
			$pay = $rowx['pay'];
			$cr = $rowx['cr'];
			$hospital_no = $rowx['hospital_no'];

			date_default_timezone_set('Africa/Lagos');
			$current_date = date('Y-m-d');
			$date1 = new DateTime($current_date);
			$date2 = new DateTime($transact_date);
			$diff = $date2->diff($date1);
			$hr = $diff->format('%h');
			$dayy = $diff->format('%a');
		} else {
			$Err = 1;
		}

		$stmt22 = $db->prepare("SELECT * FROM apptm WHERE appt_no = :app_no AND status = 'checkin'");
		$stmt22->bindParam(':app_no', $app_no, PDO::PARAM_STR);
		$stmt22->execute();

		if ($stmt22->rowCount() > 0) {
			$status = 'checkin';
		}

		if ($drug_reversal_period == '' or $drug_reversal_period == 0) {
			$grace = 1;
		} else {
			$grace = $drug_reversal_period;
		}

		if ($paystatus == '0' or $dayy <= $grace or $status == 'checkin' or $pay == 0) { ?>


			<input type="hidden" id="all_qtyy" name="all_qtyy" value="<?php echo $qty; ?>"> </div>

			<div class="form_sep">
				<label for="reg_input_no" class="req">Select One Option Below:</label>
			</div>
			<table width="100%">
				<tr>
					<td> <input id="drug_replace" type="radio" name="selection_status" value="drug_replace" /> Quantity<strong>(All)</strong> </td>
					<td><input id="drug_new" type="radio" name="selection_status" value="drug_new" /> Quantity<strong>(Specify)</strong> </td>
				</tr>
			</table>
			<br />

			<div id="div1">

				<strong>Are you returning all the quantity delivered?</strong><br><br>
				<div class="form_sep">
					<button type="button" class="btn btn-success btn btn-sm" id="reverse_btn"
						onclick="reverse_drug_now('<?php echo 'return_all'; ?>','<?php echo $hos_no; ?>')">Return All </button>

				</div>

			</div>

			<div id="div2">

				<div class="form_sep">
					<label for="reg_input_no" class="req">Enter Return Quantity</label>
					<input type="number" id="specify_qtyy" name="specify_qtyy" class="form-control" onkeyup="sum();" min="1" max="<?php echo $qty; ?>" data-required="true">
				</div>

				<div class="form_sep">
					<button type="button" class="btn btn-success btn btn-sm" id="reverse_btn2"
						onclick="reverse_drug_now('<?php echo 'specify_qty'; ?>','<?php echo $hos_no; ?>')">Return Now </button>



				</div>
			</div>

		<?php } else { ?>

			<div class="alert alert-danger"><strong style="font-size:16px">Error: This drug has exceeded reversal period ... </strong></div>

		<?php } ?>

	<?php } ?>


	<input type="hidden" name="app_no" id="app_no" value="<?php echo $app_no; ?>" />
	<input type="hidden" name="hosp_no" id="hosp_no" value="<?php echo $hos_no; ?>" />
	<input type="hidden" name="hosp_no_nursing" id="hosp_no_nursing" value="<?php echo $hospital_no; ?>" />
	<input type="hidden" name="drug_sn" id="drug_sn" value="<?php echo $drug_sn; ?>" />
	<input type="hidden" name="sale_sn" id="sale_sn" value="<?php echo $sale_sn; ?>" />
	<input type="hidden" name="names" id="names" value="<?php echo $names; ?>" />
	<input type="hidden" name="claim_amt" id="claim_amt" value="<?php echo $claim_amt; ?>" />
	<input type="hidden" name="pay" id="pay" value="<?php echo $pay; ?>" />
	<input type="hidden" name="old_qty" id="old_qty" value="<?php echo $old_qty; ?>" />
	<input type="hidden" name="remarks" id="remarks" value="<?php echo $remarks; ?>" />
	<input type="hidden" name="EX_or_IN" id="EX_or_IN" value="<?php echo $EX_or_IN; ?>" />
	<input type="hidden" name="where" id="where" value="<?php echo $where; ?>" />
	<input type="hidden" name="admstatus" id="admstatus" value="<?php echo $admstatus; ?>" />
	<input type="hidden" name="paystatus" id="paystatus" value="<?php echo $paystatus; ?>" />
	<input type="hidden" name="refill_drug_id" id="refill_drug_id" value="<?php echo $part_part; ?>" />
	<input type="hidden" name="fullname" id="fullname" value="<?php echo $_SESSION['fullname']; ?>" />