<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

if ($_SESSION['navigate'] == 'pharmacy' or $_SESSION['navigate'] == 'Pharmacy') {
	$navigation = 'pharmacy';
	$stock_table = 'pharmacy';
	$stock = 'p';
} else {
	$navigation = 'nursing';
	$stock_table = "others";
	$stock = "o";
}


if (isset($_POST["edit_stockname"])) {


	$stockname = $_POST["edit_stockname"];
	// Remove unwanted characters from stock name
	$charactersToRemove = '*=";,:\''; // Space character is omitted
	$pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
	$stockname = preg_replace($pattern, '', $stockname);

	// Prepare the SQL statement
	$stmt = $db->prepare('UPDATE stock_table SET product_name = :product_name, 
	package_type = :package_type, 
	reorder_level = :reorder_level, 
	expire_date = :expire_date WHERE sn = :sn');

	// Execute the statement
	$success = $stmt->execute([
		':product_name' => ucwords($stockname),
		':package_type' => $_POST['packagetype'],
		':reorder_level' => $_POST['reorder'],
		':expire_date' => $_POST['expired'],
		':sn' => $_POST['stock_snn']
	]);

	// Check if the update was successful
	if ($success) {
		echo "Record updated successfully!. You can Refresh if you wish to see changes!";
	} else {
		echo "Failed to update the record. Please try again.";
	}

	exit;
}

if (isset($_POST["mgt_expire_date_id"])) {

	$mgt_expire_date_id = $_POST["mgt_expire_date_id"];
	$part = explode("__", $mgt_expire_date_id);
	$stockSn = $part[0]; // Assuming $part[0] contains the stock serial number

	echo '<h3>Batch/Procurements Expiring Dates Below: </h3>';

	$stmt = $db->prepare("SELECT sn, stock_sn, expiry_date FROM stock_table_procurment WHERE stock_sn = :stock_sn AND finish_status = 0 AND status = 'yes'");
	$stmt->bindParam(':stock_sn', $stockSn, PDO::PARAM_STR);
	$stmt->execute();



	// Check if any rows were returned
	if ($stmt->rowCount() > 0) { ?> <div align="right"><small style="color: red;">Click Finish button if product is Sold Out! </small></div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th data-toggle="true">Date Expired</th>
					<th data-toggle="true">Batch No</th>
					<th data-toggle="true">Edit Exp. Date </th>
					<th data-toggle="true">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row_d = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
					<tr>
						<td><?= date("d-M-y", strtotime($row_d['expiry_date'])); ?></td>
						<td><?= $row_d['batch_no']; ?></td>
						<td><input type="date" id="mgt_date_<?php echo $row_d['sn']; ?>" name="mgt_date_<?php echo $row_d['sn']; ?>" value="<?php echo $row_d['expiry_date']; ?>" class="form-control"></td>
						<td>
							<a href="#" class="btn btn-primary btn-xs" onclick="update_expire_date('<?php echo $row_d['sn']; ?>','<?php echo $part[0]; ?>')">Update Date</a>
							&nbsp; : &nbsp;
							<a href="#" class="btn btn-primary btn-xs" onclick="finish_drug('<?php echo $row_d['sn']; ?>')">Finish</a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		<strong>Expiring Batch Records to Displayed!</strong>
	<?php } ?>



	<br>
	<hr>

	<?php

	$stmt = $db->prepare("SELECT product_name,expire_date,reorder_level,package_type FROM stock_table WHERE sn = :stock_sn");
	$stmt->bindParam(':stock_sn', $stockSn, PDO::PARAM_STR);
	$stmt->execute();
	$row_d = $stmt->fetch(PDO::FETCH_ASSOC);
	?>



	<div class="alert alert-info">

		<h3 style="color: red;;">EDIT: Expiring Date & Optional Edit details. Click Save Button.</h3>
		<input type="hidden" name="stock_snn" id="stock_snn" value="<?= $stockSn; ?>">

		<div class="form_sep">
			<label for="reg_input_no" class="req"> Product/Stock Name</label>
			<input type="text" id="stockname_edit_2" name="stockname" class="form-control" required maxlength="100" value="<?= $row_d['product_name']; ?>">
		</div>

		<div class="form_sep" id="">
			<label for="reg_input_no">Expirying Date</label>
			<div class="input-group date">
				<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
				<input type="date" name="expired_edit_2" id="expired_edit_2" class="form-control" value="<?= $row_d['expire_date']; ?>">
			</div>
		</div>

		<div class="form_sep">
			<label for="reg_input_no" class="req">Re-order level</label>
			<input type="number" id="reorder_2" name="reorder" class="form-control" min="0" value="1" required value="<?= $row_d['reorder_level']; ?>">
		</div>
		<div class="form_sep">
			<label for="reg_input_no" class="req">Package Type</label>
			<select name="packagetype" id="packagetype_2" class="form-control" style="font-size:14px" required>
				<option value="">-- select--</option>

				<?php $stmtt = $db->query("SELECT * FROM stock_table_package");
				if ($stmtt->rowCount() > 0) { ?>
					<?php while ($roww = $stmtt->fetch(PDO::FETCH_ASSOC)) { ?>
						<option value="<?php echo $roww['name']; ?>" <?php if ($row_d['package_type'] == $roww['name']) { ?>selected<?php } ?>><?php echo $roww['name']; ?></option>
				<?php }
				} ?>
			</select>
		</div>
		<div class="form_sep">
			<div class="pull-left">
				<a href="#" class="btn btn-primary" onclick="save_edit_product_stock('<?php echo $row_d['sn']; ?>')">Save</a>

			</div>

			<div class="pull-right">
				<button class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>


<?php }

if (isset($_POST["mgt_stock_id_report_form"])) {

	echo '<h2>Inventory Report</h2>';

	$mgt_stock_id_report_form = $_POST["mgt_stock_id_report_form"];
	$part = explode("/", $mgt_stock_id_report_form);
	$sn_stock = $part[0];
	$dept = $part[1];

	$stmt = $db->prepare("SELECT product_name,package_type,stock_total_unit FROM stock_table WHERE sn = :sn");
	$stmt->bindParam(":sn", $sn_stock, PDO::PARAM_STR);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$product_name = $row['product_name'];
	$stock_total_unit = $row['stock_total_unit'];
	$package_type = $row['package_type'];

	$stmt = $db->prepare("SELECT department FROM department WHERE sn = :sn");
	$stmt->bindParam(":sn", $dept, PDO::PARAM_STR);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$department = $row['department']; ?>


	<div class="alert alert-info">

		<h2 style="color: blue;;"><?= $product_name;
									echo "<h3>Package Type: $stock_total_unit Unit(s) = $package_type </h3>"; ?>
		</h2>
		<h3 style="color: black;;">Department: <?= $department;  ?></h3>
		<input type="hidden" name="" id="inventory_stock_snn" value="<?= $sn_stock; ?>">
		<input type="hidden" name="" id="inventory_dept" value="<?= $dept; ?>">
		<input type="hidden" name="" id="inventory_stock_total_unit" value="<?= $stock_total_unit; ?>">

		<table>
			<tr>
				<td>
					<div class="form_sep flex-fill">
						<label for="inventory_start">From Date</label>
						<div class="input-group date">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="date" name="inventory_start" id="inventory_start" class="form-control"
								value="<?php echo date('Y-m-d', strtotime('first day of this month')); ?>">
						</div>
					</div>
				</td>
				<td>
					<div class="form_sep flex-fill">
						<label for="inventory_end">To Date</label>
						<div class="input-group date">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="date" name="inventory_end" id="inventory_end" class="form-control"
								value="<?php echo date('Y-m-d'); ?>">
						</div>
					</div>
				</td>
				<td width="20%">
					<!-- Bootstrap-styled larger checkbox -->
					<div class="form-check"><br>
						<input class="form-check-input" type="checkbox" name="inventroy_pack_status" id="inventroy_pack_status" value="1" title="stock_total_unit">
						<label class="form-check-label" for="inventroy_pack_status" style="font-size: 1.2rem; ">
							View As Pack
						</label>
					</div>


				</td>
				<td>
					<div class="pull-left">
						<label for="inventory_end">.</label><br>
						<a href="#" class="btn btn-primary" onclick="fetch_inventory_report()">Fetch Report</a>
					</div>
				</td>
				<td>&nbsp;&nbsp;
				</td>
				<td>
					<div class="form_sep">
						<div class="pull-right"><label for="inventory_end">.</label><br>
							<button class="btn btn-danger" data-dismiss="modal">Close</button>
						</div>
					</div>
				</td>
			</tr>
		</table>
		<div id="inventory_body_report"></div>
	</div>


<?php



}





if (isset($_POST["mgt_stock_id"])) {

	$mgt_stock_id = $_POST["mgt_stock_id"];
	$part = explode("__", $mgt_stock_id);
	$stock_table_to_post = $_POST['stock_table_to_post'];

	// Fetch from stock_table
	$stmt = $db->prepare("SELECT package_type,stock_table,supplier_id,buying_cost,stock_total_unit FROM stock_table WHERE sn = :sn ORDER BY sn");
	$stmt->bindParam(":sn", $part[0], PDO::PARAM_STR);
	$stmt->execute();

	if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

		$stock_table = $row['stock_table'];
		$supplier_id = $row['supplier_id'];
		$buying_cost = $row['buying_cost'];
		$stock_total_unit = $row['stock_total_unit'];
		$package_type = $row['package_type'];

		if (!empty($supplier_id)) {
			$stmt = $db->prepare("
				SELECT sc.name, stp.supplier_id
				FROM stock_table_procurment stp
				LEFT JOIN stock_company sc ON sc.sn = stp.supplier_id
				WHERE stp.stock_sn = :stock_sn
				ORDER BY stp.sn DESC
				LIMIT 1");
			$stmt->bindParam(":stock_sn", $part[0], PDO::PARAM_STR);
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$supplier_id = $row['supplier_id'];
				$name = $row['name'];
			}
		}





		$stmt = $db->prepare("SELECT bal FROM stock_table_inven 
		WHERE stock_sn = :drug_sn AND cust_patient_id = :dept_id 
		ORDER BY sn DESC LIMIT 1");
		$stmt->bindParam(':drug_sn', $part[0], PDO::PARAM_STR);
		$stmt->bindParam(':dept_id', $_SESSION['dept_id'], PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$rw = $stmt->fetch(PDO::FETCH_ASSOC);
			$qty = $rw['bal'];
		} else {
			$qty = 0;
		}
	}

	$pack_status = null;
	if ($package_type !== '' && $stock_total_unit > 1) {
		$pack = floor($qty / $stock_total_unit); // whole packages
		$pcs  = $qty % $stock_total_unit;        // remainder pieces
		$desc = $pack . ' ' . $package_type . ' & ' . $pcs . ' pcs Remaining';
		$pack_status = 1;
	} else {
		$desc = $qty;
		$pack_status = 0;
	} ?>

	<!--<form action="index.php?stock=<?php //echo $stock; 
										?>" method="POST"> -->
	<form id="stockForm" method="POST">
		<h4><?php echo $name; ?></h4>
		<h2 style="color:blue;"><?php echo $part[1]; ?></h2>
		<h3><span id="OldQty">Current Quantity:&nbsp;</span><?php echo $desc; ?></h3>

		<h3 style="color:red; "><span id="currentQty"></span></h3>
		<div id="resultBox"></div>

		<div class="form_sep">
			<input type="hidden" value="<?php echo $row['qty']; ?>" name="mgt_old_qty" class="form-control" readonly>
		</div>

		<div class="form_sep">
			<label for="reg_select" class="req">Select Entry Mode</label>
			<select name="qty_type" id="qty_type" class="form-control" required>
				<option selected="selected" value="">Select...</option>

				<?php if ($_SESSION['stock_mgr'] == '1' or $_SESSION['stock_mgr_pharm'] == '1') { ?>
					<option value="1">Re-Stock from Purchase Order(Express PO)</option>
				<?php } ?>

				<!--<option value="3">Re-Stock from Main Store</option>-->

				<?php if ($qty > 0 and $_SESSION['remove_stock_qty'] == '1') { ?>
					<option value="4">(-) Remove Quantity</option>
				<?php } ?>

				<?php if ($_SESSION['stock_mgr'] == '1' or $_SESSION['stock_mgr_pharm'] == '1') { ?>
					<option value="6">(+) Add Quantity </option>
				<?php 	} ?>
			</select>
		</div>


		<?php if ($pack_status == 1) { ?>
			<div class="form_sep">
				<label for="reg_select" class="req">Enter Qty As</label>
				<select name="as_pack" id="as_pack" class="form-control" required>
					<option selected="selected" value="">Select...</option>
					<option value="pack">Pack</option>
					<option value="Pcs">Pcs</option>
				</select>
			</div>
		<?php } else { ?>
			<input type="hidden" name="as_pack" value="Pcs">
		<?php  } ?>

		<div class="form_sep">
			<label for="reg_input_no" class="req">Enter Quantity</label>
			<input type="number" id="new_qty" name="new_qty" class="form-control" onkeyup="sum();" min="1" required>
		</div>



		<div class="form_sep">
			<label for="reg_select" class="req">Description</label>
			<select name="desc" id="desc" class="form-control" required>
				<option selected="selected" value="">Select...</option>
				<option value="Add:">Add to Stock</option>
				<option value="Expired:">Expired Items</option>
				<option value="Damage:">Damage Items</option>
				<option value="Missing:">Missing Items</option>
				<option value="Return:">Return Items</option>
				<option value="Adjusted Qty:">Adjusted Qty</option>
				<option value="Corrected:">Corrected</option>
				<option value="Others:">Others [Describe below]</option>
			</select>
		</div>




		<div class="form_sep">
			<label for="reg_input_no" class="">Describe Here</label>
			<input type="text" id="desc2" name="desc2" class="form-control" placeholder="" maxlength="30">
		</div>
		<div class="form_sep" id="supplier">
			<i class="text-danger">Note that express POs bypass accounts and do not directly impact expenses as they should. <br>
				Only use this apporoach if it aligns with your company policy. <br>
				Otherwise, use traditional POs to restock items for proper expense tracking.
			</i>
			<div class="form_sep" id="">
				<label for="reg_input_no" class="req">Supplier</label>
				<select name="supplier_id" id="supplier_id" class="form-control" style="font-size:14px">
					<option value="">-- select--</option>

					<?php $stmt = $db->query("SELECT * FROM stock_company order by name");
					if ($stmt->rowCount() > 0) { ?>
						<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
							<option <?php if ($row['sn'] == $supplier_id) { ?> selected <?php } ?> value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
					<?php }
					} ?>
				</select>
			</div>

			<div class="form_sep">
				<label for="reg_input_no" class="req">Purchasing Cost</label>
				<input type="number" id="buying_cost" name="buying_cost" step="any" class="form-control" value="<?php echo $buying_cost; ?>">
			</div>

			<div class="form_sep">
				<label for="reg_input_no" class="">Batch Number</label>
				<input type="text" id="" name="batch_no" class="form-control">
			</div>

			<div class="form_sep">
				<label for="pur_date_ip" class="">Purchase Date</label>
				<input type="date" id="pur_date_ip" name="pur_date_ip" class="form-control" value="<?php echo date('Y-m-d'); ?>">
			</div>

			<div class="form_sep">
				<label for="mfg_date_ip" class="">Mfg. date</label>
				<input type="date" id="mfg_date_ip" name="mfg_date_ip" class="form-control">
			</div>

			<div class="form_sep">
				<label for="exp_date_ip" class="">Exp. date</label>
				<input type="date" id="exp_date_ip" name="exp_date_ip" class="form-control">
			</div>

		</div>




		<div class="form_sep">
			<?php if ($stock_total_unit == '') { ?>
				<strong style="color: red; ">Invalid Stock Unit. Goto Edit to Set Unit</strong>
				<div class="pull-right">
					<button class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>

			<?php } else { ?>


				<div class="pull-left">

					<button class="btn btn-primary" type="submit" id="submitBtn">
						Save
					</button>
					<!--<button class="btn btn-primary" type="submit" name="add_inven"
						onclick="return confirm('Are you sure you wish to alter the quantity of the selected Item? this action is irreversible!')">Save</button>-->
				</div>

				<div class="pull-right">
					<button class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>

			<?php } ?>
		</div>
		<br>

		<input type="hidden" name="mgt_stock_id" id="mgt_stock_id" value="<?php echo $mgt_stock_id; ?>" />
		<input type="hidden" name="stockName" id="stockName" value="<?php echo $part[1]; ?>" />
		<input type="hidden" name="running_qty" id="running_qty" value="<?php echo $qty; ?>" />
		<input type="hidden" name="mgt_unit" id="mgt_unit" />
		<input type="hidden" name="stock" id="stock" value="<?php echo $stock_table_to_post; ?>" />
		<input type="hidden" name="stock_total_unit" id="stock_total_unit" value="<?php echo $stock_total_unit; ?>" />
		<input type="hidden" name="package_type" id="package_type" value="<?php echo $package_type; ?>" />

	</form>
<?php

}
?>


<?php
if (isset($_POST["mgt_stock_id_report"])) {

	$stock_id = $_POST["mgt_stock_id_report"];
	$start = $_POST['inventory_start'];
	$end = $_POST['inventory_end'];
	$dept_rq = $_POST['inventory_dept'];
	$units = $_POST['inventory_stock_total_unit'];
	$_pack_status = 0;
	if (isset($_POST['inventroy_pack_status']) && $_POST['inventroy_pack_status'] == '1') {
		$_pack_status = 1;
	}


	function formatQuantity($qty, $units)
	{
		if ($units > 1) {
			$pack = floor($qty / $units);
			$pcs  = $qty % $units;

			$parts = [];
			if ($pack > 0) $parts[] = "$pack (pck)";
			if ($pcs > 0)  $parts[] = "$pcs (pcs)";

			return implode(' & ', $parts);
		} else {
			return $qty . ' (pcs)';
		}
	}

	//$qty = 17;
	//$units = 5;
	///echo formatQuantity($qty, $units); // Output: "3 (pck) & 2 (pcs)"


?>
	<hr>
	<?php
	$q_dept = " and cust_patient_id=:cust_patient_id";

	$stmt = $db->prepare('SELECT t.* FROM (SELECT * FROM stock_table_inven 
                                                WHERE stock_sn = :stock_sn AND date(captured_date) BETWEEN :start AND :end ' . $q_dept . ' ORDER BY sn ASC) t ORDER BY t.sn ASC');
	$stmt->bindParam(':stock_sn', $stock_id);
	$stmt->bindParam(':start', $start);
	$stmt->bindParam(':end', $end);

	if ($dept_rq != '') {
		$stmt->bindParam(':cust_patient_id', $dept_rq);
	}

	$stmt->execute();

	if ($stmt->rowCount() > 0) {
	?>
		<!-- Buttons -->
		<div class="mb-2">
			<button id="downloadCSV" class="btn btn-success btn-sm">Download Excel</button>
			<button id="printReport" class="btn btn-primary btn-sm">Print</button>
		</div>

		<!-- Inventory Table with Unique ID -->
		<table id="inventoryReportTable" class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th width="20%">Description</th>
					<th>IN</th>
					<th>OUT</th>
					<th>Bal</th>
					<th>Buy</th>
					<th>Sale</th>
					<th>Total Amt(Buy)</th>
					<th>Total Amt(Sale)</th>
					<th>Date</th>
					<th>Captured By</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$n = 1;
				$qtyIN = 0;
				$qtyOUT = 0;
				$total_buy_sofar = 0;
				$total_sale_sofar = 0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				?>
					<tr>
						<td><?php echo $n; ?></td>
						<td><?php echo $row['inven_desc']; ?></td>
						<td>
							<?php
							echo ($_pack_status == 1 && $units > 1)
								? formatQuantity($row['qtyIN'], $units)
								: $row['qtyIN'];

							if ($row['qtyIN'] > 0 && $row['buy'] > 0) {
								$total_buy_sofar += ($row['buy'] * $row['qtyIN']);
							}
							$qtyIN += $row['qtyIN'];
							?>
						</td>
						<td>
							<?php
							echo ($_pack_status == 1 && $units > 1)
								? formatQuantity($row['qtyOUT'], $units)
								: $row['qtyOUT'];

							if ($row['qtyOUT'] > 0 && $row['sale'] > 0) {
								$total_sale_sofar += ($row['sale'] * $row['qtyOUT']);
							}
							$qtyOUT += $row['qtyOUT'];
							?>
						</td>
						<td>
							<?php
							echo ($_pack_status == 1 && $units > 1)
								? formatQuantity($row['bal'], $units)
								: $row['bal'];
							?>
						</td>
						<td><?php echo number_format($row['buy']); ?></td>
						<td><?php echo number_format($row['sale']); ?></td>

						<td><?php echo ($row['buy'] > 0) ? number_format($row['bal'] * $row['buy']) : ''; ?></td>
						<td><?php echo ($row['sale'] > 0) ? number_format($row['bal'] * $row['sale']) : ''; ?></td>

						<td>
							<?php
							echo date('d M,Y', strtotime($row['insertion_date_time'])) . '<br>';
							echo date('h:i:s a', strtotime($row['insertion_date_time']));
							?>
						</td>
						<td><?php echo $row['enter_by']; ?></td>
					</tr>

				<?php
					$n++;
				} ?>
				<tr>
					<td colspan="2" style="text-align:right;"><strong>Total:</strong></td>
					<td><?php

						echo ($_pack_status == 1 && $units > 1)
							? formatQuantity($qtyIN, $units)
							: $qtyIN;

						?></td>
					<td><?php
						echo ($_pack_status == 1 && $units > 1)
							? formatQuantity($qtyOUT, $units)
							: $qtyOUT;; ?></td>
					<td colspan="6"></td>
				</tr>
			</tbody>
		</table>

		<!-- JavaScript for CSV Download & Print -->
		<script>
			// CSV Download
			document.getElementById("downloadCSV").addEventListener("click", function() {
				let table = document.getElementById("inventoryReportTable");
				let rows = table.querySelectorAll("tr");
				let csv = [];

				rows.forEach(row => {
					let cols = row.querySelectorAll("td, th");
					let rowData = [];
					cols.forEach(col => {
						let data = col.innerText.replace(/"/g, '""'); // Escape quotes
						rowData.push('"' + data + '"');
					});
					csv.push(rowData.join(","));
				});

				let csvString = csv.join("\n");
				let link = document.createElement("a");
				link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString);
				link.download = "inventory_report.csv";
				link.click();
			});

			// Print Table
			document.getElementById("printReport").addEventListener("click", function() {
				let table = document.getElementById("inventoryReportTable");

				if (!table) {
					alert("No table found to print!");
					return;
				}

				let printWin = window.open("", "_blank", "width=900,height=700");
				printWin.document.write(`
                    <html>
                    <head>
                        <title>Inventory Report</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                            table, th, td { border: 1px solid #000; }
                            th, td { padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                        </style>
                    </head>
                    <body>
                        <h2>Inventory Report</h2>
                        ${table.outerHTML}
                    </body>
                    </html>
                `);
				printWin.document.close();
				printWin.focus();
				printWin.print();
			});
		</script>

<?php
	} else {
		echo 'No Inventory Records Found';
	}
}
?>


<script>
	$("#stockForm").on("submit", function(e) {
		e.preventDefault(); // stop normal form submit

		if (!confirm("Are you sure you wish to alter the quantity of the selected item? This action is irreversible!")) {
			return false;
		}

		let formData = $(this).serialize();
		$("#submitBtn").prop("disabled", true).text("Processing...");

		$.ajax({
			url: "add_inventory.php",
			type: "POST",
			data: formData,
			dataType: "json", // expect JSON
			success: function(response) {
				$("#submitBtn").prop("disabled", false).text("Save");
				$("#resultBox").html(response.message);

				if (response.status === "success" && response.new_qty !== undefined) {
					$("#currentQty").text(response.new_qty);
					$("#OldQty").text("Previous Qty: ");
				}

				// scroll modal body to top
				$("#inventory_body2").animate({
					scrollTop: 0
				}, 400);
			},
			error: function(xhr, status, error) {
				$("#submitBtn").prop("disabled", false).text("Save");
				$("#resultBox").html("<div class='alert alert-danger'>Error: " + error + "</div>");

				// scroll modal body to top
				$("#inventory_body2").animate({
					scrollTop: 0
				}, 400);
			}

		});
	});




	$(document).ready(function() {

		$("#supplier").hide();

		// Show/hide supplier section based on selection
		$('#qty_type').on('change', function() {
			if (this.value == '1') {
				$("#supplier").show();
				// Make supplier fields required when this option is selected
				$("#supplier_id, #buying_cost").prop('required', true);
			} else {
				$("#supplier").hide();
				// Remove required attribute when not selected
				$("#supplier_id, #buying_cost").prop('required', false);
			}
		});
	});


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