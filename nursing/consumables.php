<?php
$dept_id = $_SESSION['dept_id'];
$fullname = $_SESSION['fullname'];


if (isset($_POST["add_consumble"])) {


	try {
		// ================= START TRANSACTION =================
		$db->beginTransaction();

		// ================= INPUTS =================
		$interest        = $_POST['interest'];
		$add_minus       = $_POST['add_minus'];
		$payment_mode    = $_POST['payment_mode'];
		$insurance_no    = $_POST['insurance_no'];
		$insurance_type  = $_POST['insurance_type'];
		$insurance       = $_POST['insurance_type'];
		$stock_sn        = $_POST['consumable_sn'];
		$hospital_no     = $_POST['hos_no'];
		$hos_no          = $_POST['hos_no'];
		$new_qty         = (int)$_POST['qty'];
		$appt_no         = $_POST['app_no'];

		$dept_id         = $_SESSION['dept_id'];
		$fullname        = $_SESSION['fullname'];
		$user_id         = $_SESSION['id'];

		$setdate         = date("Y-m-d H:i:s");
		$captured_date   = date("Y-m-d H:i:00");

		// ================= CHECK STOCK BALANCE =================
		$stmt = $db->prepare("
        SELECT bal 
        FROM stock_table_inven 
        WHERE stock_sn = :stock_sn 
          AND cust_patient_id = :dept_id 
        ORDER BY sn DESC 
        LIMIT 1
    ");
		$stmt->execute([
			':stock_sn' => $stock_sn,
			':dept_id'  => $dept_id
		]);

		if ($stmt->rowCount() === 0) {
			throw new Exception('Error : No Stock Available');
		}

		$rw = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($rw['bal'] < $new_qty) {
			throw new Exception('Error : Invalid Quantity Available');
		}

		// ================= CALCULATIONS =================
		$c_qty            = $rw['bal'] - $new_qty;
		$qtyIN            = 0;
		$qtyOUT           = $new_qty;
		$cust_patient_type = 'IN';
		$return_status    = 0;
		$batch            = 'dsp';
		$remarks          = 'Delivered to patient: ' . $hos_no;

		// ================= CHECK DUPLICATE INVENTORY ENTRY =================
		$stmt = $db->prepare("
        SELECT sn 
        FROM stock_table_inven
        WHERE stock_sn = :stock_sn
          AND inven_desc = :remarks
          AND qtyOUT = :qtyOUT
          AND enter_by = :enter_by
          AND captured_date = :captured_date
          AND cust_patient_id = :cust_patient_id
    ");
		$stmt->execute([
			':stock_sn'        => $stock_sn,
			':remarks'         => $remarks,
			':qtyOUT'          => $qtyOUT,
			':enter_by'        => $fullname,
			':captured_date'   => $captured_date,
			':cust_patient_id' => $dept_id
		]);

		if ($stmt->rowCount() > 0) {
			throw new Exception('Error : Already Captured!');
		}

		// ================= GET PRODUCT DETAILS =================
		$stmt = $db->prepare("
        SELECT product_name, nhis_price, hosp_price, cash_price,
               sn, stock_table, coverage, buying_cost
        FROM stock_table
        WHERE sn = :stock_sn
        LIMIT 1 ");
		$stmt->execute([':stock_sn' => $stock_sn]);
		$drug = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$drug) {
			throw new Exception('Error : Product Not Found');
		}

		$item_service = $drug['product_name'];
		$hosp_price = $drug['hosp_price'];
		$hosp_price_ap = $drug['hosp_price'];
		$item_sn      = $drug['sn'];
		$price_table  = 'Consumable';
		$category     = 'Nursing Services';

		$sale         = $drug['cash_price'];
		$cash_price         = $drug['cash_price'];
		$ext_price         = $drug['cash_price'];
		$nhis_price         = $drug['nhis_price'];
		$buy          = $drug['buying_cost'];
		$buying_cost          = $drug['buying_cost'];
		$price_markup          = $drug['price_markup'];


		if ($price_markup > 0 && $buying_cost > 0) {
			$amt = $buying_cost / $units;
			$markup_amount = ($amt * $price_markup) / 100;
			$selling_price = $amt + round($markup_amount, 3);

			if ($selling_price > 0) {
				$ext_price = $selling_price;
				$hosp_price = $selling_price;
			}
		}

		$target_sn = $stock_sn;
		$_tariff_table = "hmo_stocks_tariff	";

		// ================= PRICE CALC =================
		include("../inc/price_calc.php");

		$amt_paying *= $new_qty;
		$claim_amt  *= $new_qty;

		// ================= INVOICE =================
		$invoice_no     = 'INV' . rand(10000, 99999);
		$invoice_status = 1;
		$paystatus      = 0;
		$cr             = 1;
		$drug_status    = 1;

		// ================= INSERT PATIENT SERVICE =================
		$stmt = $db->prepare("
        INSERT INTO patient_ap_services
        (app_no, hospital_no, access, serv_group, cat_type, dept_id,
         drug_sn, item_services, hosp_price, claim_amt, interest,
         qty, remarks, drug_status, invoice_status, invoice_no,
         invoice_date, invoice_by, prepared_by, created_by,dsp_by,
         date_entry, pay, pay_mode, paystatus, process_claim, cr,med_duration_unit)
        VALUES
        (:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id,
         :drug_sn, :item_services, :hosp_price, :claim_amt, :interest,
         :qty, :remarks, :drug_status, :invoice_status, :invoice_no,
         :invoice_date, :invoice_by, :prepared_by, :created_by,:dsp_by,
         :date_entry, :pay, :pay_mode, :paystatus, 0, :cr, :med_duration_unit)
    ");

		$stmt->execute([
			':app_no'        => $appt_no,
			':hospital_no'   => $hospital_no,
			':access'        => $access,
			':serv_group'    => $category,
			':cat_type'      => $price_table,
			':dept_id'       => $dept_id,
			':drug_sn'       => $item_sn,
			':item_services' => $item_service,
			':hosp_price'    => $hosp_price_ap,
			':claim_amt'     => $claim_amt,
			':interest'      => $interest,
			':qty'           => $new_qty,
			':remarks'       => $remarks,
			':drug_status'   => $drug_status,
			':invoice_status' => $invoice_status,
			':invoice_no'    => $invoice_no,
			':invoice_date'  => $setdate,
			':invoice_by'    => $fullname,
			':prepared_by'   => $fullname,
			':created_by'    => $user_id,
			':dsp_by'    => $fullname,
			':date_entry'    => $setdate,
			':pay'           => $amt_paying,
			':pay_mode'      => $pay_mode,
			':paystatus'     => $paystatus,
			':cr'            => $cr,
			':med_duration_unit' => $c_qty
		]);

		$sale_sn = $db->lastInsertId();

		// ================= INSERT INVENTORY MOVEMENT =================
		$stmt = $db->prepare("
        INSERT INTO stock_table_inven
        (stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal,
         buy, sale, cust_patient_id, cust_patient_type,
         return_status, enter_by, captured_date)
        VALUES
        (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal,
         :buy, :sale, :cust_patient_id, :cust_patient_type,
         :return_status, :enter_by, :captured_date)
    ");

		$stmt->execute([
			':stock_sn'          => $stock_sn,
			':sale_sn'           => $sale_sn,
			':inven_desc'        => $remarks,
			':batch'             => $batch,
			':qtyIN'             => $qtyIN,
			':qtyOUT'            => $qtyOUT,
			':bal'               => $c_qty,
			':buy'               => $buy,
			':sale'              => $sale,
			':cust_patient_id'   => $dept_id,
			':cust_patient_type' => $cust_patient_type,
			':return_status'     => $return_status,
			':enter_by'          => $fullname,
			':captured_date'     => $captured_date
		]);

		// ================= COMMIT =================
		$db->commit();
		$success_msg = "Success : Added Successfully";
	} catch (Exception $e) {
		$db->rollBack();
		$error_msg = $e->getMessage();
	}
}


if (isset($_POST["service_apply_consumable"])) {
	$from_date_cn = ($_POST['from_date_cn']);
	$to_date_cn = ($_POST['to_date_cn']);
} else {
	///	$from_date=$back_date;
	$from_date_cn = date("Y-m-d");
	$to_date_cn = date("Y-m-d");
}

?>


<?php if (!empty($success_msg)) { ?>
	<div class="alert alert-success" role="">
		<?php echo htmlspecialchars($success_msg); ?>
	</div>
<?php } ?>

<?php if (!empty($error_msg)) { ?>
	<div class="alert alert-danger" role="">
		<?php echo htmlspecialchars($error_msg); ?>
	</div>
<?php } ?>


<br>
<div class="row">
	<div class="col-sm-6 b-r">
		<form action="patient_bill.php?hosp_no=<?= htmlspecialchars($hospital_no) ?>&Consumable" method="POST">
			<table width="100%">
				<tr>
					<td width="60%" style="padding-right:10px;">
						<?php
						$sql = "
						SELECT DISTINCT s.product_name, s.sn, c.bal 
						FROM stock_table s
						INNER JOIN stock_table_inven c ON s.sn = c.stock_sn
						WHERE c.cust_patient_id = :dept_id
						  AND c.bal > 0
						  AND s.cash_price > 0
						ORDER BY s.product_name
					";
						$stmt = $db->prepare($sql);
						$stmt->execute([':dept_id' => $dept_id]);
						$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
						?>

						<div class="form_sep">
							<label>Select Consumables</label>
							<input
								type="text"
								class="form-control"
								id="stock_consumbl_list"
								name="stock_consumbl_list"
								placeholder="Enter Consumable Here"
								autocomplete="off">

							<input type="hidden" id="consumable_sn" name="consumable_sn">
						</div>
					</td>

					<td width="10%" style="padding-right:10px;">
						<div class="form_sep">
							<label>Qty</label>
							<input type="number" id="qty" name="qty" class="form-control" value="1" min="1" required>
						</div>
					</td>

					<td>
						<label>&nbsp;</label><br>
						<button class="btn btn-success" type="submit" name="add_consumble">Add & Delivered</button>
					</td>
				</tr>
			</table>

			<!-- Hidden inputs -->
			<input type="hidden" name="app_no" value="<?= htmlspecialchars($appointment_number) ?>" />
			<input type="hidden" name="hos_no" value="<?= htmlspecialchars($hospital_no) ?>" />
			<input type="hidden" name="insurance" value="<?= htmlspecialchars($patient_insurance) ?>" />
			<input type="hidden" name="payment_mode" value="<?= htmlspecialchars($payment_mode) ?>" />
			<input type="hidden" name="insurance_no" value="<?= htmlspecialchars($insurance_no) ?>" />
			<input type="hidden" name="insurance_type" value="<?= htmlspecialchars($insurance_type) ?>" />
			<input type="hidden" name="interest" value="<?= htmlspecialchars($interest) ?>" />
			<input type="hidden" name="add_minus" value="<?= htmlspecialchars($add_minus) ?>" />
		</form>
	</div>



	<div class="col-sm-6">
		<h4>Search for Past records</h4>
		<form action="patient_bill.php?hosp_no=<?php echo $hospital_no; ?>" method="POST">
			<table>
				<tr>
					<td>
						<div class="form_sep" id="">
							<div class="input-daterange input-group" id="">
								<input type="date" class="input-sm form-control" name="from_date_cn" value="<?php echo $from_date_cn; ?>" />
								<span class="input-group-addon">to</span>
								<input type="date" class="input-sm form-control" name="to_date_cn" value="<?php echo $to_date_cn; ?>" />
							</div>
						</div>
					</td>
					<td>
						<div class="form_sep" id="">
							<select name="select_rpt_type" class="form-control">
								<option selected="selected" value="">Select type of Report</option>
								<option value="0">Invoiced/Payment Pending Only</option>
								<option value="1">Paid Services Only</option>
							</select>
						</div>
					</td>
					<td>
						&nbsp;&nbsp;<button class="btn btn-success btn btn-sm" type="submit" name="service_apply_consumable">Apply</button>
					</td>
				</tr>
			</table>

			<input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>" />
		</form>
	</div>
</div>




<hr>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox ">

			<?php

			if (isset($_POST['service_apply_consumable'])) {
				//// past payment
				$hospital_no = $_POST['hospital_no'];
				$from_date_cn = $_POST['from_date_cn'];
				$to_date_cn = $_POST['to_date_cn'];

				if ($_POST['select_rpt_type'] == 1) {
					$search_critera = "and paystatus='1' and date(date_entry) between '$from_date_cn' and '$to_date_cn'";
				} else {
					$search_critera = "and paystatus='0' and date(date_entry) between '$from_date_cn' and '$to_date_cn'";
				}
			} else {

				if (isset($_GET['hosp_no']) && !empty($_GET['hosp_no'])) {
					$hospital_no = $_GET['hosp_no'];
				} else {
					$hospital_no = $_POST['hospital_no'];
				}
				$search_critera = "and paystatus='0'";
			}

			$stmt = $db->query("SELECT p.serv_group,p.remarks,p.pay,p.claim_amt,p.date_entry,p.prepared_by,p.med_duration_unit,p.paystatus,p.qty,p.dsp_by,p.invoice_status,p.drug_status,p.item_services,stock_table.product_name,stock_table.dosage,stock_table.strength,p.sn,p.access 
	FROM patient_ap_services as p 
	INNER JOIN stock_table ON p.drug_sn=stock_table.sn 
	WHERE hospital_no='$hospital_no' and (invoice_status='0' or invoice_status='1') $search_critera and cat_type='consumable' and dept_id='$dept_id'");

			if ($stmt->rowCount() > 0) { ?>

				<form action="patient_bill.php" method="POST">

					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th data-toggle="true" width="1%"></th>
								<th data-toggle="true">Consumable</th>
								<th data-hide="phone,tablet" width="5%">Cash</th>
								<th data-hide="phone,tablet" width="5%">Claim</th>
								<th data-hide="phone,tablet" width="5%">Qty</th>
								<th data-hide="phone,tablet" width="8%" style="background-color: greenyellow;">Bal. Qty</th>
								<th data-hide="phone,tablet" width="10%">Date</th>
								<th data-hide="phone,tablet" width="15%">Pay Status</th>
								<th data-hide="phone,tablet" width="15%">Action</th>
								<th data-hide="phone,tablet" width="12%">Entered By</th>
							</tr>
						</thead>

						<tbody>
							<?php
							$paying = 0;
							$claiming = 0;
							$invoice_set = 0;
							$inv_print = 0;

							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$inv_print = 1;   ?>

								<tr>
									<td><?php echo '<input type="checkbox" value="' . $row['sn'] . '__' . $row['item_services'] . '__' . $row['pay'] . '__' . $row['claim_amt'] . '__' . $row['qty'] . '" name="inv[]" checked="checked" />' . ' ' . $row['invoice_no']; ?></td>
									<td><?php if ($insurance_status == 1 and $row['access'] == 3) {
											echo '<strong style="color:#F00"> Private Drug(100% payable)</strong><br>';
										}
										echo $row['item_services']; ?>
									</td>

									<td><?php echo number_format($row['pay']); ?></td>
									<td><?php echo number_format($row['claim_amt']); ?></td>
									<td><?php echo $row['qty']; ?></td>
									<td style="background-color: greenyellow;"><?php echo $row['med_duration_unit']; ?></td>
									<td><?php echo date('d M', strtotime($row['date_entry'])) . ' ' . date('h:i a', strtotime($row['date_entry'])); ?></td>
									<td>

										<?php if ($row['paystatus'] == 1 and $row['pay'] > 0) { ?>
											<strong>PAID</strong>
										<?php
										} elseif ($row['paystatus'] == 0 and $row['cr'] == 1) { ?>
											<strong style="color:#F00;">CREDIT</strong>
										<?php
										} elseif ($row['paystatus'] == 0 && $row['invoice_status'] == 1 && $row['drug_status'] == 1) { ?>
											<strong style="color:#F00;">INVOICED & DISPENSED</strong>
										<?php } elseif ($row['paystatus'] == 0 and $row['invoice_status'] == 0) { ?>
											<strong style="color:#F00;">PENDING</strong>
										<?php
										} elseif ($row['paystatus'] == 1 and $row['pay'] == 0 and $row['claim'] > 0) { ?>
											<strong>POSTED</strong><br>
										<?php } ?>

									</td>
									<td bgcolor="#CCFFFF">

										<input type="button" name="notes" value="Notes" data-target="#modal" id="<?php echo $row["sn"] . '__' . $row['item_services']; ?>" class="btn btn-warning btn-xs view_notes" />
										&nbsp;|&nbsp;

										<?php
										$l = 0;
										if ($unit_head == '1' or $row['prepared_by'] == $fullname) {
											$l = 0;
										} else {
											$l = 1;
										}



										if ($row['drug_status'] == 1) { ?>

											<input type="button" name="on_cr" value=" - Reverse &nbsp;" data-target="#modal" id="<?php echo $row['sn'] . '/' . $hospital_no . '/' . $app_no . '/' . $names . '/IN/P/' . $adm_status . '/' . $row['paystatus']; ?>" class="btn btn-danger btn-xs reverse" />
										<?php } elseif ($row['invoice_status'] == 0 && $row['paystatus'] == 0 && $row['drug_status'] == 0) { ?>
											<a href="patient_bill.php?<?php echo 'dl=' . $row['sn'] . '&hosp_no=' . $hospital_no; ?>&Consumable" onclick="return confirm('Are you sure you want to delete this Item?')" class="btn btn-danger btn-xs" <?php if ($l == 1) { ?>disabled<?php } ?>>Delete</a>



										<?php } ?>



									</td>
									<td><?php echo $row['prepared_by']; ?> </td>
								</tr>


							<?php
								if ($row['paystatus'] == 0) {
									$claiming = $claiming + $row['claim_amt'];
									$paying = $paying + $row['pay'];
								}
							}
							//	}

							?>
						</tbody>
					</table>

				</form>
				<hr>




			<?php

			} else { ?>
				<br>
				<div style="font-size:13px;" align="center"><b>No Existing Consumables</b></div>
			<?php } ?>




		</div>
	</div>
</div>

<div class="modal inmodal fade" id="add_drug_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Drug</h4>
			</div>
			<div class="modal-body" id="add_drug_body">
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="view_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Notes</h4>
			</div>
			<div class="modal-body" id="view_notes_body">
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="refill_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Consumable</h4>
			</div>
			<div class="modal-body" id="refill_body">
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="dsp_oncredit_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Dispense On/Credit</h4>
			</div>
			<div class="modal-body" id="dsp_oncredit_body">
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="reverse_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Reverse Consumable</h4>
			</div>
			<div class="modal-body" id="reverse_body">
			</div>
		</div>
	</div>
</div>