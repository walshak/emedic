<?php
session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");

if (isset($_POST["approve_request_id"])) {

	$approve_request_id = $_POST["approve_request_id"];
	$stmt = $db->query("SELECT * FROM stock_table_request WHERE sn='$approve_request_id'");
	if ($stmt->rowCount() > 0) {
		$roww = $stmt->fetch(PDO::FETCH_ASSOC);
		$order_dept = $roww['order_dept'];
		$order_date = $roww['order_date'];
		$order_qty = $roww['order_qty'];
		$stock_name = $roww['stock_name'];
		$stock_sn = $roww['stock_sn'];
		$order_no = $roww['sn'];
		$stock_table = $roww['stock_table'];
		$combo = $roww['combo'];
		//	$order_by_id=$roww['order_dept'];
	}

	if ($combo == '1') {

		$C_qty = 0;
		$C_main_qty = 0;
		$max = $order_qty;
	} else {
		$stmt = $db->prepare('SELECT qty, main_qty, stock_total_unit FROM stock_table WHERE sn = :sn');
		$stmt->bindParam(':sn', $stock_sn);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {

			$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
			$C_qty = $rwx['qty'];
			$C_main_qty = $rwx['main_qty'];
			$stock_total_unit = $rwx['stock_total_unit'];
		} else {
			$C_qty = 0;
			$C_main_qty = 0;
		}
		$max = $C_main_qty;
	}

	if ($stock_table == 'Nursing Consumable' or $stock_table == 'c') { /// c = nusrsing consumable ///
		//// get nursing from admin user table ////
		$rights = 'NS';
		$get_post = "c";
		$search_rights = " where rights='$rights'";
	} elseif ($stock_table == 'Pharmacy' or $stock_table == 'p') {
		$rights = 'PH';
		$get_post = "p";
		$search_rights = " where rights='$rights'";
	} elseif ($stock_table == 'Investigation' or $stock_table == 'l') {
		$rights = 'LB';
		$get_post = "l";
		$search_rights = " where rights='$rights'";
	} else {
		$search_rights = "";
		$get_post = "o";
	} ?>


	<form action="index.php?stock=<?php echo $get_post; ?>" method="post">
		<div class="form_sep"><strong>Qty Remaining <?php echo  ':' . $C_qty; ?></strong></div>
		<div class="form_sep"><strong>Stock Name <?php echo $stock_name; ?> / Quantity Order <?php echo $order_qty; ?> / Order Date <?php echo date("d M Y", strtotime($order_date)); ?></strong></div>

		<?php

		//// Here determine Main Bal or Runing Balance ////
		$stock_direction = '';
		if ($C_main_qty == 0 and $C_qty > 0 and $combo == '1') {
			$stock_direction = 'runing';
			$max = $C_qty;
		} elseif ($C_main_qty > 0 and $combo == '1') {
			$stock_direction = 'main';
			$max = $C_main_qty;
		} else {
			$stock_direction = 'main';
			$max = $order_qty;   //// to allow it pass the condition below ////
		}

		?>
		<div class="form_sep">
			<label for="reg_input_no" class="">Batch Number/Item Label</label>
			<input type="text" name="label" class="form-control">
		</div>


		<div class="form_sep">
			<label for="reg_input_no" class="req">Approve Quantity</label>
			<input type="number" name="apr_qty" min="1" max="<?php echo $max; ?>" class="form-control" value="<?php echo $order_qty; ?>">
		</div>

		<div class="form_sep">
			<label for="reg_input_no" class="req">Collected By</label>
			<select name="Collectedby" class="form-control" required>
				<option selected="selected" value="">Select ...</option>

				<?php
				$stmt = $db->query("SELECT FirstName,LastName,MiddleName FROM hremp where Department='$order_dept' order by FirstName");
				if ($stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
						<option value="<?php echo $row["FirstName"] . ' ' . $row["LastName"] . ' ' . $row["MiddleName"]; ?>"><?php echo $row["FirstName"] . ' ' . $row["LastName"] . ' ' . $row["MiddleName"]; ?></option>
				<?php }
				}
				?>
			</select>
		</div>

		<div class="form_sep">
			<button class="btn btn-primary btn-sm" type="submit" name="approve_order">Approve Order</button>
		</div>
		<input type="hidden" name="sn" value="<?php echo $_GET['app']; ?>">
		<input type="hidden" name="order_no" value="<?php echo $order_no; ?>">
		<input type="hidden" name="order_dept" value="<?php echo $order_dept; ?>">
		<input type="hidden" name="order_date" value="<?php echo $order_date; ?>">
		<input type="hidden" name="stock_sn" value="<?php echo $stock_sn; ?>">
		<input type="hidden" name="stock_total_unit" value="<?php echo $stock_total_unit; ?>">
		<input type="hidden" name="stock_name" value="<?php echo $stock_name; ?>">
		<input type="hidden" name="C_qty" value="<?php echo $C_qty; ?>">
		<input type="hidden" name="C_main_qty" value="<?php echo $C_main_qty; ?>">
		<input type="hidden" name="combo" value="<?php echo $combo; ?>">
		<input type="hidden" name="get_post" value="<?php echo $get_post; ?>">
		<input type="hidden" name="stock_direction" value="<?php echo $stock_direction; ?>">
	</form>

<?php


}

if (isset($_POST["change_requisition_item"])) {
	$sn = $_POST["change_requisition_item"];
	$stmt_chk = $db->prepare("SELECT * FROM stock_table_request WHERE sn = :sn");
	$stmt_chk->execute([':sn' => $sn]);

	if ($stmt_chk->rowCount() > 0) {
		$rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
		echo '<h2>' . $rowwc['stock_name'] . '</h2>';
		$stock_table = $rowwc['stock_table'];
		$order_dept = $rowwc['order_dept'];
	} ?>

	<input type="hidden" value="<?= $rowwc['stock_name']; ?>" name="stock_name_old">
	<input type="hidden" value="<?= $sn; ?>" name="request_sn">

	<div class="form_sep">
		<label for="reg_input_no" class="">Search for Item</label>
		<select class="chosen-select" class="form-control" name="changed_item" id="" required>
			<option selected="selected" value="">Select </option>

			<?php
			$stmt = $db->query("SELECT sn,product_name,qty FROM stock_table where stock_table='$stock_table' order by product_name ");
			if ($stmt->rowCount() > 0) {
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
					<option value="<?php echo $row["sn"]; ?>"><?php echo $row["product_name"] . ' Qty: ' . $row["qty"]; ?></option>
			<?php }
			}
			?>
		</select>
	</div>

	<input type="hidden" value="<?= $order_dept ?>" name="change_order_dept">

	<div class="form_sep">
		<button class="btn btn-primary btn-sm" type="submit" name="change_order">Change Order</button>
	</div>

	<?php }



if (isset($_POST["dept_to_trnxfer_"])) {

	$response_main = array(
		'message' => '',
		'status' => ''
	);

	$captured_date = date("Y-m-d");
	$enter_by = $_SESSION['fullname'];
	$cust_patient_type = 'IN';

	$qty_ = $_POST["qty_"];
	$stock_sn_ = $_POST["stock_sn_"]; // Ensure this is set
	$Dept_from_ = $_POST["Dept_from_"]; // Ensure this is set
	$dept_to_trnxfer_ = $_POST["dept_to_trnxfer_"]; // Ensure this is set


	function getDepartmentInfo($db, $sn_field)
	{
		if (isset($_POST[$sn_field]) && !empty($_POST[$sn_field])) {
			$dept_sn = $_POST[$sn_field];
			$stmt = $db->prepare("SELECT department, department_type FROM department WHERE sn = :sn LIMIT 1");
			$stmt->bindParam(':sn', $dept_sn, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		return false;
	}

	// Usage:
	if ($deptRow = getDepartmentInfo($db, 'dept_to_trnxfer_')) {
		$trnf_dept = $deptRow['department'];
		$trnf_dept_type = $deptRow['department_type'];
	}

	if ($deptRow = getDepartmentInfo($db, 'Dept_from_')) {
		$from_dept = $deptRow['department'];
		$from_dept_type = $deptRow['department_type'];
	}


	// Check current balance
	$stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id ORDER BY sn DESC LIMIT 1");
	$stmt_chk->execute([':stock_sn' => $stock_sn_, ':cust_patient_id' => $Dept_from_]);

	if ($stmt_chk->rowCount() > 0) {
		$rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
		$old_qty = $rowwc['bal'];
	} else {
		$response_main['message'] = 'Source department not found!';
		$response_main['status'] = '1';
		echo json_encode($response_main);
		exit;
	}

	// Validate quantity
	if ($qty_ > $old_qty) {
		$response_main['message'] = 'Invalid Quantity!';
		$response_main['status'] = '1';
		echo json_encode($response_main);
		exit;
	}

	$inven_desc = "Qty:" . $qty_ . ' RMV: ' . $from_dept . ' TRX: ' . $trnf_dept;

	// Check destination balance
	$stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id ORDER BY sn DESC LIMIT 1");
	$stmt_chk->execute([':stock_sn' => $stock_sn_, ':cust_patient_id' => $dept_to_trnxfer_]);

	if ($stmt_chk->rowCount() > 0) {
		$rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
		$old_qty_trnxf_to = $rowwc['bal'];
	} else {
		$old_qty_trnxf_to = 0; // Assuming starting balance is 0 if not found
	}


	$stmt_call = $db->prepare("SELECT buying_cost, cash_price 
                           FROM stock_table 
                           WHERE sn = :stock_sn 
                           LIMIT 1");
	$stmt_call->execute([':stock_sn' => $stock_sn_]);
	$rwx = $stmt_call->fetch(PDO::FETCH_ASSOC);

	if ($rwx) {
		$sale  = $rwx['cash_price'];
		$buy = $rwx['buying_cost'];
	}


	try {
		$db->beginTransaction();

		// Update stock for source department
		$qtyIN = 0;
		$qtyOUT = $qty_;
		$c_qty = $old_qty - $qty_;

		// Insert record for source department
		$stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND inven_desc = :inven_desc AND qtyIN = :qtyIN AND qtyOUT = :qtyOUT AND enter_by = :enter_by AND captured_date = :captured_date");
		$stmt_chk->execute([
			':stock_sn' => $stock_sn_,
			':inven_desc' => $inven_desc,
			':qtyIN' => $qtyIN,
			':qtyOUT' => $qtyOUT,
			':enter_by' => $enter_by,
			':captured_date' => $captured_date
		]);

		if ($stmt_chk->rowCount() == 0) {
			$addRow = "INSERT INTO stock_table_inven(stock_sn, inven_desc, qtyIN, qtyOUT, bal,buy,sale, cust_patient_id, cust_patient_type, enter_by, captured_date) 
							   VALUES (:stock_sn, :inven_desc, :qtyIN, :qtyOUT, :c_qty,:buy,:sale, :order_dept, :cust_patient_type, :enter_by, :captured_date)";
			$stmt = $db->prepare($addRow);
			$stmt->execute([
				':stock_sn' => $stock_sn_,
				':inven_desc' => $inven_desc,
				':qtyIN' => $qtyIN,
				':qtyOUT' => $qtyOUT,
				':c_qty' => $c_qty,
				':buy' => $buy,
				':sale' => $sale,
				':order_dept' => $Dept_from_,
				':cust_patient_type' => $cust_patient_type,
				':enter_by' => $enter_by,
				':captured_date' => $captured_date
			]);

			if (in_array(strtoupper($from_dept_type), ['STORE', 'PHARMACY'])) {
				$upd_stmt = $db->prepare('UPDATE stock_table SET qty = :qty WHERE sn = :sn');
				$upd_stmt->bindParam(':qty', $c_qty);
				$upd_stmt->bindParam(':sn', $stock_sn_);
				$upd_stmt->execute();
			}
		}

		// Update stock for destination department
		$qtyOUT = 0;
		$qtyIN = $qty_;
		$c_qty = $old_qty_trnxf_to + $qty_;

		// Insert record for destination department
		$stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND inven_desc = :inven_desc AND qtyIN = :qtyIN AND qtyOUT = :qtyOUT AND enter_by = :enter_by AND captured_date = :captured_date");
		$stmt_chk->execute([
			':stock_sn' => $stock_sn_,
			':inven_desc' => $inven_desc,
			':qtyIN' => $qtyIN,
			':qtyOUT' => $qtyOUT,
			':enter_by' => $enter_by,
			':captured_date' => $captured_date
		]);

		if ($stmt_chk->rowCount() == 0) {
			$addRow = "INSERT INTO stock_table_inven(stock_sn, inven_desc, qtyIN, qtyOUT, bal,buy,sale,cust_patient_id, cust_patient_type, enter_by, captured_date) 
							   VALUES (:stock_sn, :inven_desc, :qtyIN, :qtyOUT, :c_qty,:buy,:sale, :order_dept, :cust_patient_type, :enter_by, :captured_date)";
			$stmt2 = $db->prepare($addRow);
			$stmt2->execute([
				':stock_sn' => $stock_sn_,
				':inven_desc' => $inven_desc,
				':qtyIN' => $qtyIN,
				':qtyOUT' => $qtyOUT,
				':c_qty' => $c_qty,
				':buy' => $buy,
				':sale' => $sale,
				':order_dept' => $dept_to_trnxfer_,
				':cust_patient_type' => $cust_patient_type,
				':enter_by' => $enter_by,
				':captured_date' => $captured_date
			]);

			if (in_array(strtoupper($trnf_dept), ['STORE', 'PHARMACY'])) {
				$upd_stmt = $db->prepare('UPDATE stock_table SET qty = :qty WHERE sn = :sn');
				$upd_stmt->bindParam(':qty', $c_qty);
				$upd_stmt->bindParam(':sn', $stock_sn_);
				$upd_stmt->execute();
			}
		}

		$db->commit();
		$response_main['message'] = 'Successfully transferred stock!';
		$response_main['status'] = '0';
		echo json_encode($response_main);
		exit;
	} catch (Exception $e) {
		$db->rollBack();
		$response_main['message'] = "Transaction failed: " . $e->getMessage();
		$response_main['status'] = '1';
		echo json_encode($response_main);
		exit;
	}
}


if (isset($_POST["departmentId_stock_"])) {

	$departmentId_stock_ = $_POST['departmentId_stock_'];
	$stock__item = $_POST['stock__item'];
	$target = $_POST['target'];


	// Fetch department data once and store it in an array
	$stmt_dept = $db->query("SELECT * FROM department ORDER BY department ASC");
	$departments = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);

	if ($target == 'departments') {
		$stmt = $db->query("SELECT distinct d.sn, d.department FROM department as d	INNER JOIN stock_table_inven as s ON s.cust_patient_id = d.sn WHERE stock_sn='$stock__item' order by d.department");

		if ($stmt->rowCount() > 0) { ?>
			<div id="test_fields">
				<table class="table table-striped">
					<thead>
						<tr>
							<th data-toggle="true">No</th>
							<th data-toggle="true">Department</th>
							<th data-toggle="true">Current Qty</th>
							<th data-toggle="true">Enter Qty</th>
						</tr>
					</thead>
					<tbody>

						<?php
						$n = 1;
						while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

							$departmentId_stock_ = $roww['sn'];
							$department = $roww['department'];

							$stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn='$stock__item' and cust_patient_id='$departmentId_stock_' ORDER BY sn DESC LIMIT 1");
							$stmt_chk->execute();
							if ($stmt_chk->rowCount() > 0) {
								$rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
								$old_qty = $rowwc['bal'];
							}
						?>
							<input type="hidden" id="stock_sn_<?php echo $n; ?>" value="<?php echo $stock__item; ?>" />
							<input type="hidden" id="which_one_task" value="list_departments" />
							<tr>
								<td><?php echo $n; ?></td>
								<td><?php echo $department; ?>
									<input type="hidden" id="Dept_from_<?php echo $n; ?>" value="<?php echo $departmentId_stock_; ?>" />
								</td>
								<td><?= $old_qty; ?></td>
								<td><input type="number" id="qty_<?php echo $n; ?>" name="qty_<?php echo $n; ?>" class="form-control" onkeyup="sum();" min="1" value="" max="<?php echo $old_qty; ?>" /></td>
								<td>
									<select name="department" class="form-control" id="dept_to_trnxfer_<?php echo $n; ?>">
										<option selected="selected" value="">Select </option>
										<?php foreach ($departments as $row) { ?>
											<option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
										<?php } ?>
									</select>
								</td>
								<td>
									<button class="btn btn-primary" name="" onClick="add_order_trxnf('<?= $n; ?>')">+</button>
								</td>
							</tr>
						<?php
							$n++;
						} ?>

					</tbody>
				</table>
			<?php } else { ?>
				<br><br><strong>No Record Found</strong><br><br>

			<?php }
	} else {
		//TODO:merge with mr Habu
		$stmt = $db->query("SELECT distinct s.sn, s.product_name  FROM stock_table_inven as i INNER JOIN stock_table as s ON s.sn = i.stock_sn WHERE cust_patient_id='$departmentId_stock_' order by s.product_name");

		if ($stmt->rowCount() > 0) { ?>
				<div id="test_fields">
					<table class="table table-striped">
						<thead>
							<tr>
								<th data-toggle="true">No</th>
								<th data-toggle="true">Name</th>
								<th data-toggle="true">Qty</th>
								<th data-toggle="true">Qty</th>
							</tr>
						</thead>
						<tbody>
							<input type="hidden" id="which_one_task" value="main_department" />
							<?php
							$n = 1;
							while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

								$stock_sn2 = $roww['sn'];

								$stmt_chk = $db->prepare("SELECT bal FROM stock_table_inven 
						WHERE stock_sn='$stock_sn2' and cust_patient_id='$departmentId_stock_' ORDER BY sn DESC LIMIT 1");
								$stmt_chk->execute();
								if ($stmt_chk->rowCount() > 0) {
									$rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
									$old_qty = $rowwc['bal'];
								}
							?>
								<tr>
									<td><?php echo $n; ?></td>
									<td><?php echo $roww['product_name']; ?>
										<input type="hidden" id="stock_sn2_<?php echo $n; ?>" value="<?php echo $stock_sn2; ?>" />
									</td>
									<td><?= $old_qty; ?></td>
									<td><input type="number" id="qty2_<?php echo $n; ?>" class="form-control" onkeyup="sum();" min="1" value="" max="<?php echo $old_qty; ?>" /></td>
									<td>
										<select name="department" class="form-control" id="dept_to_trnxfer2_<?php echo $n; ?>">
											<option selected="selected" value="">Select </option>
											<?php foreach ($departments as $row) { ?>
												<option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
											<?php } ?>
										</select>
									</td>
									<td>
										<button class="btn btn-primary" name="" onClick="add_order_trxnf('<?= $n; ?>')">+</button>
									</td>


								</tr>
							<?php
								$n++;
							} ?>

						</tbody>
					</table>
				<?php } else { ?>
					<br><br><strong>No Record Found</strong><br><br>

				<?php }
		}
		exit;
	}

	if (isset($_POST["save_record"])) {

		$stmt = $db->query("SELECT insurance_no FROM insurance_tbl ORDER BY insurance_no DESC LIMIT 1");
		$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
		$insurance_no = 1 + $rwx['insurance_no'];


		///   interest  phone addr

		$insurance_name = ucwords($_POST['insurance_name']);
		$mode_payment = $_POST['mode_payment'];
		$Insurance_Type = $_POST['Insurance_Type'];
		$interest = $_POST['interest'];
		$ptm = $_POST['ptm'];
		$add_minus = $_POST['add_minus'];
		$payment_mode = $_POST['payment_mode'];
		$credit_setup = $_POST['credit_setup'];
		//if ($credit_setup == '') {
		//	$credit_setup = 0;
		//}
		////exit;
		//echo $credit_setup;
		///exit;

		if ($interest > 0 && ($add_minus === '' || $add_minus === 'nil')) {
			echo '<strong>Error Occured Please Specify Interest Addition or Discount Minus</strong>';
				?>
				<a href="index.php?ptm=<?= $ptm; ?>">Return</a>
				<?php exit; ?>
			<?php
			header("location:index.php?ptm=$ptm&er");
			exit;
		} elseif ($interest == 0 && $add_minus == 'nil') {
			$add_minus = '-';
			$interest = 0;
		} else {
		}



		if ($Insurance_Type == 'NHIS') {
			$ptm = 'nhs';
		} elseif ($Insurance_Type == 'PHIS') {
			$ptm = 'phs';
		} elseif ($Insurance_Type == 'Corporate') {
			$ptm = 'cpt';
		} elseif ($Insurance_Type == 'Family') {
			$ptm = 'fld';
		}


		$setdate = date("Y-m-d");
		$mode = $_POST['mode'];




		if ($mode == 'edit') {


			// Debug output (make sure to remove or comment out in production)
			$insur_no = $_POST['insurance_no'];
			///echo $interest; // Assuming $interest is set somewhere before this point


			try {
				$updateSQL = "UPDATE insurance_tbl 
						  SET insurance_name = :insurance_name, 
							  insurance_type = :insurance_type, 
							  interest = :interest, 
							  add_minus = :add_minus, 
							  payment_mode = :payment_mode, 
							  services_access = :services_access, 
							  phone = :phone, 
							  addr = :addr, 
							  credit_setup = :credit_setup
						  WHERE insurance_no = :insurance_no";

				$stmt = $db->prepare($updateSQL);

				$stmt->bindParam(':insurance_name', $_POST['insurance_name'], PDO::PARAM_STR);
				$stmt->bindParam(':insurance_type', $_POST['Insurance_Type'], PDO::PARAM_STR);
				$stmt->bindParam(':interest', $interest, PDO::PARAM_STR);
				$stmt->bindParam(':add_minus', $add_minus, PDO::PARAM_STR);
				$stmt->bindParam(':payment_mode', $_POST['mode_payment'], PDO::PARAM_STR);
				$stmt->bindParam(':services_access', $_POST['access_to'], PDO::PARAM_STR);
				$stmt->bindParam(':phone', $_POST['phone'], PDO::PARAM_STR);
				$stmt->bindParam(':addr', $_POST['addr'], PDO::PARAM_STR);
				$stmt->bindParam(':credit_setup', $credit_setup, PDO::PARAM_STR);
				$stmt->bindParam(':insurance_no', $_POST['insurance_no'], PDO::PARAM_STR);

				$stmt->execute();
				//?ptm=nhs/1093


				header("Location: index.php?ptm=" . urlencode($ptm) . "/" . urlencode($insur_no) . "&sv");
				exit; // Make sure to exit after header redirection
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
			}
		} else {

			$stmt = $db->query("SELECT insurance_no FROM insurance_tbl ORDER BY insurance_no DESC LIMIT 1");
			$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
			$insurance_no = 1 + $rwx['insurance_no'];
			///   interest  phone addr

			$stmt = $db->query("SELECT * FROM insurance_tbl WHERE insurance_type='$Insurance_Type' and insurance_name LIKE '%$insurance_name%'");
			if ($stmt->rowCount() == 0) {
				$active = "active";
				$insertSQL = $db->prepare('INSERT INTO insurance_tbl (insurance_no, insurance_name, insurance_type, interest, add_minus, payment_mode, services_access, status, phone, addr,credit_setup,date_captured) VALUES (:insurance_no, :insurance_name, :insurance_type, :interest, :add_minus, :payment_mode, :services_access, :status, :phone, :addr, :credit_setup, :date_captured)');
				$insertSQL->bindParam(':insurance_no', $insurance_no);
				$insertSQL->bindParam(':insurance_name', $insurance_name);
				$insertSQL->bindParam(':insurance_type', $Insurance_Type);
				$insertSQL->bindParam(':interest', $interest);
				$insertSQL->bindParam(':add_minus', $add_minus);
				$insertSQL->bindParam(':payment_mode', $_POST['mode_payment']);
				$insertSQL->bindParam(':services_access', $_POST['access_to']);
				$insertSQL->bindParam(':status', $active);
				$insertSQL->bindParam(':phone', $_POST['phone']);
				$insertSQL->bindParam(':addr', $_POST['addr']);
				$insertSQL->bindParam(':credit_setup', $credit_setup);
				$insertSQL->bindParam(':date_captured', $setdate);
				$insertSQL->execute();
				header("location:index.php?ptm=$ptm&sv");
			} else {
				header("location:index.php?ptm=$ptm&er");
			}
		}
	}




	if (isset($_POST["confirm_payslip_id"])) {

		$confirm_payslip_id = $_POST["confirm_payslip_id"];
		$parts = explode("__", $confirm_payslip_id);

		$Ecode = $parts['0'];
		$start = $parts['1'];
		$end = $parts['2'];
		$sal_type = $parts['3'];
		$emplname = $parts['4'];
		$days = $parts['5'];
		$month = $parts['6'];
		$year = $parts['7'];
		$Grand_Inc_Earning = 0;
		$gen_pay = 0;
		$no_income = 1;
		$confirm_mode = 1;
		$save_mode = 'no';
		include("payslip_generate_body.php");
	}



	if (isset($_POST["add_new_category_id"])) {

		$add_new_category_id = $_POST["add_new_category_id"];

		$part = explode("__", $add_new_category_id);
		$category = $part[0];
		$post_id = $part[1];

		if ($category == 'Consultation' || $category == 'Medical Services') {
			$category = "consult_med";
		}

		$stmt = $db->query("SELECT * FROM prices_table_category WHERE cat_type='$category' ORDER BY sn");

		if ($stmt->rowCount() > 0) {
			?>
				<div id="test_fields">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No</th>
								<th>Name</th>
								<th colspan="2">Action</th>
							</tr>
						</thead>
						<tbody>

							<?php
							$n = 1;
							while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
							?>
								<tr>
									<td><?php echo $n; ?></td>
									<td><?php echo $roww['Name']; ?></td>

									<!-- DELETE -->
									<td>
										<a href="index.php?price=<?php echo $post_id; ?>&cat=<?php echo $roww['sn']; ?>"
											onclick="return confirm('Are you sure you want to delete ?');">
											Delete
										</a>
									</td>

									<!-- EDIT (AJAX trigger) -->
									<td>
										<a href="javascript:void(0);"
											class="edit-btn"
											data-id="<?php echo $roww['sn']; ?>"
											data-name="<?php echo htmlspecialchars($roww['Name']); ?>">
											Edit
										</a>
									</td>
								</tr>
							<?php
								$n++;
							}
							?>

						</tbody>
					</table>
				</div>
			<?php
		} else {
			echo "<br><br><strong>No Grouping Found</strong><br><br>";
		}
			?>

			<!-- FORM -->
			<form method="POST" action="index.php?price=<?php echo $post_id; ?>">

				<div class="form_sep">
					<label class="req"><?php echo $category; ?> Group</label>
					<input type="text" id="catetory_name" name="catetory_name"
						class="form-control" maxlength="100" required>
				</div>

				<!-- HIDDEN EDIT FIELD -->
				<input type="hidden" id="edit_id" name="edit_id" value="">

				<div class="form_sep"></div>

				<div class="pull-left">
					<button class="btn btn-primary" type="submit" name="add_category" id="saveBtn">
						Save
					</button>
					<input type="hidden" name="category_type" value="<?php echo $category; ?>" />
				</div>

				<div class="pull-right">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>

			</form>

			<!-- JQUERY -->
			<script>
				$(document).ready(function() {

					// EDIT CLICK
					$(".edit-btn").click(function() {

						var id = $(this).data("id");
						var name = $(this).data("name");

						// Fill textbox
						$("#catetory_name").val(name);

						// Set edit ID
						$("#edit_id").val(id);

						// Change button text
						$("#saveBtn").text("Update");
					});

					// CANCEL EDIT
					$("#cancelEdit").click(function() {
						$("#catetory_name").val('');
						$("#edit_id").val('');
						$("#saveBtn").text("Save");
					});

				});
			</script>

		<?php
	}



	if (isset($_POST["add_new_item_id"])) {
		$add_new_item_id = $_POST["add_new_item_id"];

		$part = explode("__", $add_new_item_id);
		$table_name = $part[0];
		$post_id = $part[1];
		$sn = $part[2];
		$mode = $part[3];

		if ($mode == 'edit') {
			$stmt_edit = $db->query("SELECT * FROM prices_table WHERE sn='$sn'");
			$rwx = $stmt_edit->fetch(PDO::FETCH_ASSOC);
			$item_service = $rwx['item_service'];
			$category = $rwx['category'];
			$dept = $rwx['dept'];
			$price_table = $rwx['price_table'];
			$file_amt = $rwx['file_amt'];
			$duration = $rwx['duration'];
			$special_package = $rwx['special_package'];
			$is_sugical_procedure = $rwx['is_sugical_procedure'];
		} else {
			$item_service = '';
			$category = '';
			$dept = '';
			$price_table = 0;
			$file_amt = 0;
			$duration = 0;
		}


		?>

			<form method="POST" action="index.php?price=<?php echo $post_id; ?>">

				<div class="form_sep">
					<label for="reg_input_no" class="req"> <?php echo $table_name; ?> Name</label>
					<input type="text" id="item_name" name="item_name" class="form-control" placeholder="" maxlength="100" value="<?php echo $item_service; ?>" required>
				</div>

				<div class="form_sep">
					<label for="reg_input_no" class="">Category Name </label>
					<select name="category_item" id="category_item" class="form-control">
						<option selected="selected" value="">Select Category ...</option>

						<?php if ($category != '') { ?>
							<option selected="selected" value="<?php echo $category; ?>"><?php echo $category; ?></option>
						<?php } ?>


						<?php

						if ($table_name == 'Consultation' or $table_name == 'Medical Services') {
							$category = "consult_med";
						} else {
							$category = $table_name;
						}
						$stmtt = $db->query("SELECT * FROM prices_table_category WHERE cat_type='$category' order by Name");
						while ($row_rstdepartment = $stmtt->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?php echo $row_rstdepartment['Name']; ?>"><?php echo $row_rstdepartment["Name"]; ?></option>
						<?php } ?>
					</select>
				</div>


				<div class="form_sep">
					<label for="reg_input_no" class="req">Department</label>
					<select name="item_dept" id="item_dept" class="form-control" required>
						<option selected="selected" value="">Select Department...</option>

						<?php if ($dept != '') { ?>
							<option selected="selected" value="<?php
																echo $dept; ?>">
								<?php
								$dept = $rwx['dept'];
								$stmttt = $db->query("SELECT department FROM department WHERE sn='$dept'");
								$rwx_dept = $stmttt->fetch(PDO::FETCH_ASSOC);
								echo $rwx_dept['department'];
								?></option>
						<?php } ?>

						<?php
						$stmtx = $db->query("SELECT * FROM department order by department");
						while ($row_rstdepartment = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?php echo $row_rstdepartment['sn']; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
						<?php } ?>
					</select>
				</div>



				<?php if ($post_id == 'c') { ?>


					<div class="form_sep">

						<label for="reg_input_no" class="req">

							<?php
							$stmt = $db->prepare("SELECT nhis_price, hosp_price, ext_price FROM prices_table WHERE item_service = ?");
							$stmt->execute(['New File']);
							if ($row_dx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$nhis_price = $row_dx['nhis_price'];
								$hosp_price = (float)$row_dx['hosp_price'];
								$ext_price  = (float)$row_dx['ext_price'];
								$final_price = max($hosp_price, $ext_price);
							}
							?>

							General File Fee: <?= ($final_price); ?><br>
							NHIS File Fee: <?= ($nhis_price); ?><br>

							Registration / File Fee (Consultation)<br>
							<small style="color: red;">
								To charge a custom amount different from the general fee above, enter the specific amount.
							</small><br>
							<small style="color: red;">
								To make New File Amount free of charge, type One in figure<b> "1" </b>
							</small>

						</label>

						<input type="text" id="file_amount" name="file_amount" class="form-control" placeholder="" maxlength="100" value="<?= $final_price; ?>" required>
					</div>


					<div class="form_sep">
						<label for="consultation_dura" class="req">
							Consultation validity period (in days) — allows the patient to return to the doctor after the first appointment.
							<br>
							<small style="color: red;">
								Note: Enter the code <b><u>100</u></b> for a free consultation.
							</small>
						</label>
						<input type="number" id="consultation_dura" name="consultation_dura" class="form-control" min="1" value="<?php echo $duration; ?>" required>
					</div>


					<?php $specialist_id = $rwx['specialist_id']; ?>
					<div class="form_sep">
						<label for="reg_input_no" class="">Specialist (Consultation Only)</label>
						<select name="specialty" id="specialty" class="form-control">
							<option selected="selected" value="">Select Specialty...</option>

							<?php
							$stmtx = $db->query("SELECT * FROM specialists order by name");
							while ($rw = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $rw['id']; ?>" <?php if ($specialist_id == $rw['id']) { ?>selected<?php } ?>><?php echo $rw["name"]; ?></option>
							<?php } ?>
						</select>
					</div>

				<?php } ?>


				<div class="form_sep">
					<label for="special_package" class="req">
						Special Package — if set to <b>Yes</b>, you can include additional services such as drugs, investigations, etc. (e.g., ANC).
					</label>
					<select name="special_package" id="special_package" class="form-control" required>
						<option value="">Select...</option>
						<option value="1" <?php if ($special_package == '1') {
												echo 'selected';
											} ?>>Yes</option>
						<option value="0" <?php if ($special_package == '0') {
												echo 'selected';
											} ?>>No</option>
					</select>
				</div>


				<?php if ($post_id == 'm') : ?>
					<div class="form_sep">
						<label for="is_sugical_procedure" class="req">Is Surgical Procedure?</label>
						<select name="is_sugical_procedure" id="is_sugical_procedure" class="form-control" required>
							<option value="">Select ...</option>
							<option value="1" <?php if ($is_sugical_procedure == '1') { ?>selected<?php } ?>>yes</option>
							<option value="0" <?php if ($is_sugical_procedure == '0') { ?>selected<?php } ?>>no</option>
						</select>
					</div>
				<?php endif ?>

				<?php if ($mode == 'edit') { ?>

					<div class="form_sep">
						<label for="reg_input_no" class="req">Price Table</label>
						<select name="price_table" id="price_table" class="form-control" required>
							<option selected="selected" value="">Select Price Table...</option>
							<?php if ($price_table != '') { ?>
								<option selected="selected" value="<?php echo $price_table; ?>"><?php echo $price_table; ?></option>
							<?php } ?>
							<option value="Consultation">Consultation</option>
							<option value="Medical Services">Medical Services</option>
							<option value="Nursing Services">Nursing Services</option>
							<option value="Other Services">Other Services</option>
						</select>
					</div>

				<?php } ?>



				<div class="form_sep"></div>

				<div class="pull-left">
					<button class="btn btn-primary" type="submit" name="save_item">Save</button>
					<input type="hidden" name="category_type" value="<?php echo $category; ?>" />
				</div>


				<div class="pull-right">
					<button class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>

				<input type="hidden" name="table_name" value="<?php echo $table_name; ?>" />
				<input type="hidden" name="sn" value="<?php echo $sn; ?>" />
				<input type="hidden" name="mode" value="<?php echo $mode; ?>" />
			</form>
		<?php } ?>


		<?php if (isset($_POST["insur_convert_id"])) {

			$insur_convert_id = $_POST["insur_convert_id"];
			$parts = explode("__", $insur_convert_id);
			$Insurance_Type = $parts[0];
			$hmo_no = $parts[1];
			$hosp_no = $parts[2];
			$nhis_no = $parts[3];

			if ($Insurance_Type == 'NHIS') {
				if ($nhis_no != '') {
					$stmt = $db->query("SELECT nhis_no,insurance,hmo_no FROM enrollee where nhis_no='$nhis_no'");
					if ($stmt->rowCount() > 0) {
						$rw = $stmt->fetch(PDO::FETCH_ASSOC);
						$insurance = $rw['insurance'];
						$hmo_no = $rw['hmo_no'];
						$nhis_status = 1;
					} else {
						$nhis_status = 0;
					}
		?>

					<form action="index.php?ptm=<?php echo 'nhs/' . $hmo_no . '/' . $hosp_no; ?>" method="POST">

						<?php if ($nhis_status == 0) { ?>

							<div class="form_sep">
								<table width="100%">
									<tr>
										<td align="right">
											<strong>This NHIS number has not been register before. <br>Do you want to register it now?</strong>
										</td>
										<td align="right">
											<strong style=" font-size:18px">NHIS Number:<br> <?php echo $nhis_no; ?></strong>
										</td>
									</tr>
								</table>
							</div>

							<div class="form_sep">
								<label for="reg_select" class="req"><i>Select Available Vacant</i></label>
								<select name="dependant" id="dependant" class="form-control" required>
									<option selected="selected" value="">Select ...</option>
									<option value="0">Principal</option>
									<option value="1">Spouse</option>
									<option value="2">Dependant 2</option>
									<option value="3">Dependant 3</option>
									<option value="4">Dependant 4</option>
									<option value="5">Extra Dependant</option>
								</select>
							</div>

							<?php } else {

							$query_ext = $db->query("SELECT * FROM enrollee WHERE nhis_no='$nhis_no' and insurance='NHIS' order by nhis_no_ext");
							if ($query_ext->rowCount() > 0) { ?>

								<h3 class="heading_a">Membership</h3>
								<table class="table table-striped table-bordered table-hover dataTables-example">
									<thead>
										<tr>
											<th width="60%">Member Details:</th>
											<th width="10%">Relationship:</th>
										</tr>
									</thead>
									<tbody>

										<?php
										while ($row = $query_ext->fetch(PDO::FETCH_ASSOC)) { ?>
											<tr class="record">
												<td>
													<li><?php echo 'NHIS No.:' . $row['nhis_no'] . '-' . $row['nhis_no_ext']; ?></li>
													<li><?php echo 'Hospital No.:' . $row['hospital_no']; ?></li>
													<li><?php echo 'Name: ' . $row['surname'] . ' ' . $row['fname'] . ' ' . $row['oname']; ?></li>
												</td>
												<td><?php if ($row['nhis_no_ext'] == '0') {
														echo 'Principal';
													} elseif ($row['nhis_no_ext'] == '1') {
														echo 'Spouse';
													} elseif ($row['nhis_no_ext'] == '2') {
														echo 'Dependant 2';
													} elseif ($row['nhis_no_ext'] == '3') {
														echo 'Dependant 3';
													} elseif ($row['nhis_no_ext'] == '4') {
														echo 'Dependant 4';
													} else {
														echo 'Extra Dependant';
													}
													?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							<?php } ?>
							<hr>

							<div class="form_sep">
								<label for="reg_select" class="req"><i>Select Available Vacant</i></label>
								<select name="dependant" id="dependant" class="form-control" required>
									<option selected="selected" value="">Select...</option>
									<?php
									$stmt = $db->query("SELECT nhis_no_ext FROM enrollee where nhis_no='$nhis_no' AND insurance='NHIS' and nhis_no_ext=0");
									if ($stmt->rowCount() == 0) {
										echo '<option value="0">Principal</option>';
									}

									$stmt = $db->query("SELECT nhis_no_ext FROM enrollee where nhis_no='$nhis_no' AND insurance='NHIS' and nhis_no_ext=1");
									if ($stmt->rowCount() == 0) {
										echo '<option value="1">Spouse</option>';
									}

									$stmt = $db->query("SELECT nhis_no_ext FROM enrollee where nhis_no='$nhis_no' AND insurance='NHIS' and nhis_no_ext=2");
									if ($stmt->rowCount() == 0) {
										echo '<option value="2">Dependant 2</option>';
									}

									$stmt = $db->query("SELECT nhis_no_ext FROM enrollee where nhis_no='$nhis_no' AND insurance='NHIS' and nhis_no_ext=3");
									if ($stmt->rowCount() == 0) {
										echo '<option value="3">Dependant 3</option>';
									}

									$stmt = $db->query("SELECT nhis_no_ext FROM enrollee where nhis_no='$nhis_no' AND insurance='NHIS' and nhis_no_ext=4");
									if ($stmt->rowCount() == 0) {
										echo '<option value="4">Dependant 4</option>';
									}

									$stmt = $db->query("SELECT nhis_no_ext FROM enrollee where nhis_no='$nhis_no' AND insurance='NHIS' and nhis_no_ext=5");
									if ($stmt->rowCount() == 0) {
										echo '<option value="5">Extra Dependant </option>';
									}
									?>
								</select>
							</div>
						<?php } ?>

						<div class="form_sep">
							<button class="btn btn-success btn-sm" type="submit" name="assign_submit" id="assign_submit">Assign & Save</button>
						</div>

						<input type="hidden" value="<?php echo $hmo_no; ?>" name="hmo_no" />
						<input type="hidden" id="nhis_membership_no" name="nhis_membership_no" value="<?php echo $nhis_no; ?>">
						<input type="hidden" id="Insurance_Type" name="Insurance_Type" value="<?php echo $Insurance_Type; ?>">
						<input type="hidden" id="hosp_no" name="hosp_no" value="<?php echo $hosp_no; ?>">

					<?php } else { ?>
						<strong>Invalid NHIS Number. Enter NHIS Number</strong>
						<hr>
						<a href="index.php?ptm" class="btn btn-danger btn-sm">Close</a>

					<?php } ?>
					</form>


					<?php } elseif ($Insurance_Type == 'PHIS' or $Insurance_Type == 'Corporate') {
					if ($Insurance_Type == 'PHIS') {
						$ptm = 'phs';
					} else {
						$ptm = 'cpt';
					}
					if ($hmo_no != '') {
					?>

						<form action="index.php?ptm=<?php echo $ptm . '/' . $hmo_no . '/' . $hosp_no; ?>" method="POST">


							<div class="form_sep">
								<label for="reg_input_name" class="">Enter Membership Number:</label>
								<input type="text" id="mem_no" name="mem_no" class="form-control" data-required="true">
							</div>

							<div class="form_sep">
								<label for="reg_select" class="">Relationship</label>
								<select name="g_relation" id="g_relation" class="form-control">

									<option selected="selected" value="">Select Relationship...</option>
									<option value="father">father</option>
									<option value="son">son</option>
									<option value="husband">husband</option>
									<option value="brother">brother</option>
									<option value="grandfather">grandfather</option>
									<option value="grandson">grandson</option>
									<option value="uncle">uncle</option>
									<option value="nephew">nephew</option>
									<option value="cousin">cousin</option>
									<option value="mother">mother</option>
									<option value="daughter">daughter</option>
									<option value="wife">wife</option>
									<option value="sister">sister</option>
									<option value="grandmother">grandmother</option>
									<option value="granddaughter">granddaughter</option>
									<option value="aunt">aunt</option>
									<option value="niece">niece</option>
									<option value="parent">parent</option>
									<option value="child">child</option>
									<option value="spouse">spouse</option>
									<option value="sibling">sibling</option>
									<option value="grandparents">grandparents</option>
									<option value="grandchild">grandchild</option>
									<option value="friend">friend</option>
									<option value="Others">Others</option>
								</select>
							</div>

							<div class="form_sep">
								<button class="btn btn-success btn-sm" type="submit" name="assign_submit_cpt_phs" id="assign_submit_cpt_phs">Assign & Save</button>
							</div>

							<input type="hidden" value="<?php echo $hmo_no; ?>" name="hmo_no" />
							<input type="hidden" id="Insurance_Type" name="Insurance_Type" value="<?php echo $Insurance_Type; ?>">
							<input type="hidden" id="hosp_no" name="hosp_no" value="<?php echo $hosp_no; ?>">

						</form>
					<?php } else { ?>
						<strong>Invalid Insurance entity. Choose Insurance before you continue ...</strong>
						<hr>
						<a href="index.php?ptm" class="btn btn-danger btn-sm">Close</a>

					<?php } ?>

					<?php

					$query_ext = $db->query("SELECT * FROM enrollee WHERE nhis_no='$nhis_no' and insurance='$Insurance_Type' and nhis_no!='' order by hospital_no");
					if ($query_ext->rowCount() > 0) { ?>

						<h3 class="heading_a">Membership</h3>
						<table class="table table-striped table-bordered table-hover dataTables-example">
							<thead>
								<tr>
									<th width="60%">Member Details:</th>
									<th width="10%">Relationship:</th>
								</tr>
							</thead>
							<tbody>

								<?php
								while ($row = $query_ext->fetch(PDO::FETCH_ASSOC)) { ?>
									<tr class="record">
										<td>
											<li><?php echo 'Member No.:' . $row['nhis_no']; ?></li>
											<li><?php echo 'Hospital No.:' . $row['hospital_no']; ?></li>
											<li><?php echo 'Name: ' . $row['surname'] . ' ' . $row['fname'] . ' ' . $row['oname']; ?></li>
										</td>
										<td><?php echo $row['member']; ?></td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>

					<?php } ?>

					<?php } elseif ($Insurance_Type == 'Family') {
					if ($hmo_no != '') {

						$part = explode(":", $hmo_no);
						$family_no = $part[0];
						$family_name = $part[1];


					?>

						<form action="index.php?ptm=<?php echo 'fld/' . $family_no . '/' . $hosp_no; ?>" method="POST">

							<div class="form_sep">
								<table width="100%">
									<tr>
										<td align="right">
											<strong>Do you want to assign <br>patient to this family number</strong>
										</td>
										<td align="right">
											<strong style=" font-size:18px">Family:<br> <?php echo $family_name; ?></strong>
										</td>
									</tr>
								</table>
							</div>


							<div class="form_sep">
								<button class="btn btn-success btn-sm" type="submit" name="assign_submit_family" id="assign_submit_family">Assign & Save</button>
							</div>

							<input type="hidden" value="<?php echo $nhis_no; ?>" name="member" />
							<input type="hidden" value="<?php echo $family_no; ?>" name="hmo_no" />
							<input type="hidden" id="Insurance_Type" name="Insurance_Type" value="<?php echo $Insurance_Type; ?>">
							<input type="hidden" id="hosp_no" name="hosp_no" value="<?php echo $hosp_no; ?>">

						</form>
					<?php } else { ?>
						<strong>Invalid Insurance entity. Choose Insurance before you continue ...</strong>
						<hr>
						<a href="index.php?ptm" class="btn btn-danger btn-sm">Close</a>

					<?php } ?>


				<?php } elseif ($Insurance_Type == 'Private') {	?>

					<div class="form_sep">
						<table width="100%">
							<tr>
								<td align="right">
									<strong>Do you want to convert this<br> patient to Private(Self) Status?</strong>
								</td>
								<td align="right">
									<strong style=" font-size:18px">Hospital No:<br> <?php echo $hosp_no; ?></strong>
								</td>
							</tr>
						</table>
					</div>

					<form action="index.php?ptm=ppt" method="POST">

						<div class="form_sep">
							<button class="btn btn-success btn-sm" type="submit" name="assign_submit_ppt" id="assign_submit_ppt">Assign & Save</button>
						</div>
						<input type="hidden" id="hosp_no" name="hosp_no" value="<?php echo $hosp_no; ?>">



					<?php } ?>




				<?php  } ?>





				<?php if (isset($_POST["payslip_delete_id"])) {

					$delete_id = $_POST["payslip_delete_id"];
					$part = explode("/", $delete_id);

				?>

					<h3>Are you sure you want to delete the Payslip for Staff <?php echo $part[0]; ?></h3>
					<form method="POST" action="index.php?<?php if ($part[5] == 'slp') {
																echo $part[5] . '=' . $part[0];
															} else {
																echo $part[5];
															} ?>">

						<div class="pull-left">
							<button class="btn btn-danger btn-sm" type="submit" name="delete_confirm_payslip" id="delete_confirm_payslip">Delete</button>
						</div>
						<div class="pull-right">
							<a href="" class="btn btn-success btn-sm">Cancel</a>
						</div>

						<input type="hidden" value="<?php echo $part[0]; ?>" name="Ecode" />
						<input type="hidden" value="<?php echo $part[1]; ?>" name="date_range" />
						<input type="hidden" value="<?php echo $part[2]; ?>" name="month" />
						<input type="hidden" value="<?php echo $part[3]; ?>" name="year" />
						<input type="hidden" value="<?php echo $part[4]; ?>" name="staff_pay" />

					</form>

				<?php
				}



				if (isset($_POST["write_off_id"])) {
					$write_off_id = $_POST["write_off_id"];
					$part = explode("__", $write_off_id);
					$target = $part[4];
				?>
					<?php if ($target == 'zero') { ?>
						<h3>Are you sure you want to Unlock?</h3>
					<?php } else { ?>
						<h3>Are you sure you want to write-off all the credits?</h3>
					<?php } ?>
					<form method="POST" action="index.php?bed">

						<div class="pull-left">
							<button class="btn btn-danger btn-sm" type="submit" name="writeoff_dischgr" id="writeoff_dischgr">Yes Write-off</button>
						</div>
						<div class="pull-right">
							<a href="" class="btn btn-success btn-sm">Cancel</a>
						</div>

						<input type="hidden" value="<?php echo $part[0]; ?>" name="hos_no" />
						<input type="hidden" value="<?php echo $part[1]; ?>" name="app_no" />
						<input type="hidden" value="<?php echo $part[2]; ?>" name="room_bed" />
						<input type="hidden" value="<?php echo $part[3]; ?>" name="room_bed_sn" />

					</form>

					<?php
				}

				if (isset($_POST["add_services_id_income"])) {

					$add_services_id_income = $_POST["add_services_id_income"];
					$parts = explode("__", $add_services_id_income);
					$id = $parts['0'];
					$type = $parts['1'];
					$ECode = $parts['2'];



					$stmt = $db->query("SELECT * FROM hred_income_services WHERE employee_no='$ECode' order by sn");
					if ($stmt->rowCount() > 0) { ?>
						<div id="test_fields">
							<table class="table table-striped">
								<thead>
									<tr>
										<th data-toggle="true">No</th>
										<th data-toggle="true">Service Name</th>
										<th data-toggle="true">Set by</th>
										<th data-toggle="true">Date Captured</th>
									</tr>
								</thead>
								<tbody>

									<?php
									$n = 1;
									while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
										if ($roww['flat_percent'] == 'Percent') {
											$mode = '%';
										} else {
											$mode = 'flat';
										}
									?>
										<tr>
											<td><?php echo $n; ?></td>
											<td><?php echo $roww['descriptn'] . '(' . $roww['flat_percent_value'] . $mode . ')'; ?></td>
											<td><?php echo $roww['setby']; ?></td>
											<td><?php echo $roww['set_date']; ?></td>
											<td>

												<input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"] . '__' . '' . '__' . $type . '__' . $ECode; ?>" class="btn btn-danger btn-xs add_services_income_del" data-target="#myModal5" />

											</td>
										</tr>
									<?php
										$n++;
									} ?>

								</tbody>
							</table>
						<?php } else { ?>
							<br><br><strong>No Service/Item Added. Search & Select Item to add .</strong><br><br>
						<?php } ?>
						<br>
						<form method="POST" id="add_services_income_form_form">

							<div class="form_sep">
								<label for="reg_select" class="req">Flat/Percentage</label>
								<select name="flat_percent2" id="flat_percent2" class="form-control" required>
									<option selected="selected" value="">Select...</option>
									<option value="Flat">Flat Amount</option>
									<option value="Percent">Percentage of Service Paid</option>
								</select>
							</div>

							<div class="form_sep">
								<label for="reg_input_name" class="req">Enter Percentage or Flat Rate:</label>
								<input type="number" step="any" id="flat_percent_amt2" name="flat_percent_amt2" class="form-control" maxlength="12" required>
							</div>

							<div class="form_sep">
								<label for="reg_input_name" class="req">Duration: &nbsp; <small>Enter Zero(0) if duration is unlimited</small></label>
								<input type="number" id="duration2" name="duration2" class="form-control" required>
							</div>



							<div class="form_sep">
								<label for="reg_input_no" class="req">Select Service Center</label>
								<select name="service_type" id="service_type" class="form-control" required>
									<option selected="selected" value="">Select ...</option>
									<option value="Investigation">Investigations (Lab & Radiology)</option>
									<option value="Pharmacy">Pharmacy and Nursing Consumables</option>
									<option value="Nursing Services">Nursing Services</option>
									<option value="Medical Services">Medical Services</option>
									<option value="Other Services">Consultation/Others Services</option>
								</select>
							</div>
							<br>

							<?php if ($type == 'Specify') { ?>

								<div class="form_sep" id="invest3">
									<?php $stmt2 = $db->query("SELECT * FROM lab_scan WHERE hosp_price>0 and combo_test='0' order by test"); ?>

									<label for="reg_input_no" class="req">Investigation</label>
									<select name="Investigation" data-placeholder="Search..." class="form-control">
										<option selected value="">-- select --</option>
										<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $roww["sn"] . '__' . $roww["test"]; ?>"><?php echo $roww["test"]; ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="form_sep" id="med3">
									<?php $stmt2 = $db->query("SELECT * FROM prices_table WHERE hosp_price>0 and price_table='Medical Services' order by item_service"); ?>

									<label for="reg_input_no" class="req">Medical Services</label>
									<select name="Medical" data-placeholder="Search..." class="form-control">
										<option selected value="">-- select --</option>
										<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $roww["sn"] . '__' . $roww["item_service"]; ?>"><?php echo $roww["item_service"]; ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="form_sep" id="pharm3">
									<?php $stmt2 = $db->query("SELECT * FROM stock_table WHERE hosp_price>0 order by stock_table, product_name"); ?>

									<label for="reg_input_no" class="req">Drugs and Consumables</label>
									<select name="pharmacy" data-placeholder="Search..." class="form-control">
										<option selected value="">-- select --</option>
										<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $roww["sn"] . '__' . $roww["product_name"]; ?>"><?php echo $roww["product_name"] . '(' . $roww["stock_table"] . ')'; ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="form_sep" id="nurs3">
									<?php $stmt2 = $db->query("SELECT * FROM prices_table WHERE hosp_price>0 and price_table='Nursing Services' order by item_service"); ?>

									<label for="reg_input_no" class="req">Nursing Services</label>
									<select name="nursing" data-placeholder="Search..." class="form-control">
										<option selected value="">-- select --</option>
										<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $roww["sn"] . '__' . $roww["item_service"]; ?>"><?php echo $roww["item_service"]; ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="form_sep" id="other_serv3">
									<?php $stmt2 = $db->query("SELECT * FROM prices_table WHERE hosp_price>0 and (price_table='Other Services' or price_table='Consultation') order by item_service"); ?>

									<label for="reg_input_no" class="req">Others</label>
									<select name="others" data-placeholder="Search..." class="form-control">
										<option selected value="">-- select --</option>
										<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $roww["sn"] . '__' . $roww["item_service"]; ?>"><?php echo $roww["item_service"]; ?></option>
										<?php } ?>
									</select>
								</div>

							<?php } ?>


							<hr>
							<div class="pull-left">
								<button class="btn btn-primary btn-sm" type="submit" name="Save" id="Save">Add Service</button>
							</div>
							<br><br> <br><br>
							<input type="hidden" name="ECode" id="ECode" value="<?php echo $ECode; ?>" />
							<input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
							<input type="hidden" name="type" id="type" value="<?php echo $type; ?>" />
							<input type="hidden" name="MM_update" value="add_income_services" />
						</form>


					<?php


				} ?>

					<?php

					if (isset($_POST["see_occupant_id"])) {

						$hos_no = $_POST["see_occupant_id"];
						$emr = $_POST["see_occupant_id"];

						$stmt = $db->query("SELECT * FROM apptm WHERE hospital_no='$hos_no' ORDER BY sn DESC LIMIT 1");
						if ($stmt->rowCount() > 0) {
							$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
							$app_no = $row_rstSelect['appt_no'];
							$ap_type = $row_rstSelect['ap_type'];
							$status = $row_rstSelect['status'];
							$auth_code = $row_rstSelect['auth_code'];
							$auth_status = $row_rstSelect['status'];
							$patient_name = $row_rstSelect['patient_name'];
							$services_name = $row_rstSelect['services_name'];
						}


						$general_credit_limit =	$_SESSION['credit_limit_status'];
						$items = call_current_balance($db, $emr, $general_credit_limit);

						$current_balance =  $items["current_balance"];
						$patient_name      = $items['patient_name'];
						$nhis_no           = $items['nhis_no'];
						$referral_name     = $items['referral_name'];
						$discount_set      = $items['discount_set'];
						$insurance_type    = $items['insurance_type'];
						$insurance_no      = $items['insurance_no'];
						$save_insurance_no = $items['save_insurance_no'];
						$wallet_amount     = $items['wallet_amount'];
						$wallet_account          = $items['wallet_account'];
						$bal_credit_limit  = $items['bal_credit_limit'];
						$credit_limit  = $items['bal_credit_limit'];
						$add_minus         = $items['add_minus'];
						$payment_mode         = $items['payment_mode'];
						$interest          = $items['interest'];
						$patient_type      = $items['patient_type'];

						if ($insurance_type == 'NHIS') {
							$type = 'nhis';
						} else {
							$type = 'others';
							$ap_type = 3;
						}


						$stmt = $db->query("SELECT * FROM admission WHERE hospital_no='$hos_no' order by sn desc limit 1");
						if ($stmt->rowCount() == 0) {
							$adm_status = 5;
						} else {
							$row_adm = $stmt->fetch(PDO::FETCH_ASSOC);
							$adm_status = $row_adm['adm_status'];
							$date_admit = $row_adm['date_admit'];
							$room_bed = $row_adm['room_bed'];
							$room_bed_sn = $row_adm['room_bed_sn'];
						}


						$stmt = $db->query("SELECT * FROM discharge_fellowup WHERE hospital_no='$hos_no'");
						if ($stmt->rowCount() == 0) {
							$adm_request = 0;
						} else {
							$adm_request = 1;
						}

						/// check payment table for only autoset transactions
						$bed_nos = 0;
						$stmt = $db->query("SELECT hospital_no FROM patient_ap_services WHERE hospital_no='$hos_no' and cat_type='Bed Space/Accommodation' and paystatus='0'");
						if ($stmt->rowCount() >= 2 and $adm_status == 3) {
							$bed_nos = 1;
						}
					?>
						<div class="row">
							<div class="col-sm-6">
								<strong><?php echo $hos_no . '/ ' . $patient_name . '<br>' . 'Consultation: ' . $services_name; ?></strong>
								<hr>
								<div style="font-size:18px; color:#093">
									<strong>Days <br>On Admission</strong>
								</div>
								<?php

								$setdate = date('Y-m-d H:i:s');
								$date1 = new DateTime($setdate);
								$date2 = new DateTime($date_admit);
								$diff = $date2->diff($date1);
								?>
								<table>
									<tr>
										<td colspan="3"><?php
														echo '' . $diff->format('<strong style="font-size:24px">%a</strong> Day(s)<strong style="font-size:24px"> %h</strong> hr(s)') . '<br><br><strong style="font-size:14px; color:#C60">Date Admitted:</strong><br><strong style="font-size:14px;">' . date('d M,Y h:i:s a', strtotime($date_admit)) . '</strong>'; ?>
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
								</table>
							</div>

							<div class="col-sm-6">

								<?php include_once("../inc/patient_amt_due.php"); ?>
								<strong style="color:#F00; font-size:14px; ">Total Amount Due(Credits)</strong>
								<br>
								<strong style="font-size:28px">
									<?php echo 'N ' . number_format($amt_due); ?>
								</strong>

								<?php if ($hmo_amt_due > 0) { ?>
									<strong>Total HMO(Insured)</strong>
									<br>
									<strong style="font-size:28px">
										<?php echo 'N ' . number_format($hmo_amt_due); ?>
									</strong>

								<?php } ?>

								<HR>

								<?php if ($amt_due == 0) { ?>

									<input type="button" name="writeoff" value="Unlock / Discharge" data-target="#myModal5" id="<?php echo $hos_no . '__' . $app_no . '__' . $room_bed . '__' . $room_bed_sn . '__zero'; ?>" class="btn btn-danger btn-sm write_off_confirm" />

									<input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
									<input type="hidden" name="room_bed_sn" value="<?php echo $room_bed_sn; ?>" />
									<input type="hidden" name="room_bed" value="<?php echo $room_bed; ?>" />

								<?php } elseif ($amt_due > 0) { ?>
									<div class="form_sep">
										<input type="button" name="writeoff" value="Write Off / Discharge" <?php if ($_SESSION['writeoff_discharge'] == 0) { ?> disabled <?php } ?> data-target="#myModal5" id="<?php echo $hos_no . '__' . $app_no . '__' . $room_bed . '__' . $room_bed_sn . '__more'; ?>" class="btn btn-danger btn-sm write_off_confirm" />
									</div>

									<input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
									<input type="hidden" name="room_bed_sn" value="<?php echo $room_bed_sn; ?>" />
									<input type="hidden" name="room_bed" value="<?php echo $room_bed; ?>" />
					</form>


				<?php } ?>
				</div>
			</div>

		<?php  } ?>

		<?php

		if (isset($_POST["delete_bed_id"])) {

			$delete_bed_id = $_POST["delete_bed_id"];

		?>

			<h3>Are you sure you want to delete the Bed Number</h3>

			<form method="POST" action="index.php?bed">

				<div class="pull-left">
					<button class="btn btn-danger btn-sm" type="submit" name="delete_bed" id="delete_bed">Delete</button>
				</div>
				<div class="pull-right">
					<a href="" class="btn btn-success btn-sm">Cancel</a>
				</div>

				<input type="hidden" value="<?php echo $delete_bed_id; ?>" name="delete_bed_id" />
			</form>

		<?php
		}

		if (isset($_POST["confirm_item_del_id"])) {

			$confirm_item_del_id = $_POST["confirm_item_del_id"];
			$part = explode("__", $confirm_item_del_id);
			$item_sn = $part[0];
			$point = $part[1];
		?>

			<h3>Are you sure you want to delete Item</h3>

			<form method="POST" action="index.php?price=<?php echo $point; ?>">

				<div class="pull-left">
					<button class="btn btn-danger btn-sm" type="submit" name="del_item_submit" id="del_item_submit">Delete</button>
				</div>
				<div class="pull-right">
					<a href="" class="btn btn-success btn-sm">Cancel</a>
				</div>

				<input type="hidden" value="<?php echo $item_sn; ?>" name="item_sn" />
			</form>

		<?php
		}

		if (isset($_POST["delete_gd_id"])) {

			$delete_gd_id = $_POST["delete_gd_id"];
			$part = explode("__", $delete_gd_id);
			$ptm = $part[0];
			$delete_gd_id = $part[1];
			$part2 = explode("/", $ptm);
			$ptm = $part2[0] . '/' . $part2[2];
		?>

			<h3>Are you sure you want to delete Item</h3>

			<form method="POST" action="index.php?ptm=<?php echo $ptm; ?>">

				<div class="pull-left">
					<button class="btn btn-danger btn-sm" type="submit" name="del_guardian_submit" id="del_guardian_submit">Delete</button>
				</div>
				<div class="pull-right">
					<a href="" class="btn btn-success btn-sm">Cancel</a>
				</div>

				<input type="hidden" value="<?php echo $delete_gd_id; ?>" name="delete_gd_id" />
				<input type="hidden" value="<?php echo $ptm; ?>" name="ptm_gurdian" />
			</form>

		<?php
		}

		if (isset($_POST["add_insurance_id"])) {
			$ptm = $_POST["add_insurance_id"];
			$part = explode("_", $ptm);
			$mode = $part[0];
			$ptm = $part[1];
			$ptm_2 = $part[2];

			if ($ptm == 'cpt') {
				$p_title = 'Corporate';
			}
			if ($ptm == 'fld') {
				$p_title = 'Family';
			}
			if ($ptm == 'nhs') {
				$p_title = 'NHIS';
			}
			if ($ptm == 'phs') {
				$p_title = 'PHIS';
			}



			$stmt = $db->query("SELECT * FROM insurance_tbl WHERE insurance_no='$ptm'");
			$rwx = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($mode == 'edit') {
				$p_title = $rwx['insurance_type'];
			}
		?>


			<form action="fetch_set.php" method="POST" id="subject" name="subject">


				<div class="form_sep">
					<label for="reg_textarea_message" class="req">Name</label>
					<input type="text" name="insurance_name" id="insurance_name" class="form-control" value="<?php
																												echo $rwx['insurance_name']; ?>" required>
				</div>

				<div class="form_sep">
					<label for="Insurance_Type" class="req">Insurance Type</label>
					<select name="Insurance_Type" id="Insurance_Type" class="form-control" required>
						<option value="" <?php echo empty($p_title) ? 'selected' : ''; ?>>
							Select...
						</option>
						<option value="NHIS" <?php echo ($p_title == 'NHIS') ? 'selected' : ''; ?>>NHIS</option>
						<option value="PHIS" <?php echo ($p_title == 'PHIS') ? 'selected' : ''; ?>>PHIS</option>
						<option value="Corporate" <?php echo ($p_title == 'Corporate' || $p_title == 'Corporate') ? 'selected' : ''; ?>>Corporate</option>
						<option value="Family" <?php echo ($p_title == 'Family') ? 'selected' : ''; ?>>Family</option>
					</select>
				</div>


				<div class="form_sep">
					<label for="mode_payment" class="req">Mode of Payment</label>
					<select name="mode_payment" id="mode_payment" class="form-control" required>
						<option value="" <?php echo empty($rwx['payment_mode']) ? 'selected' : ''; ?>>
							Select...
						</option>
						<option value="0" <?php echo ($rwx['payment_mode'] == '0') ? 'selected' : ''; ?>>
							Pay before service or use delivered service as credit (Applicable: Family Folder)
						</option>
						<option value="1" <?php echo ($rwx['payment_mode'] == '1') ? 'selected' : ''; ?>>
							Payment is claimed after service is delivered (Applicable: NHIS, PHIS, Corporate)
						</option>


					</select>
				</div>


				<div class="form_sep">
					<label for="reg_textarea_message" class="req">Apply percentage (%) to hospital price: Extra Charges or Deduction (Enter 0 if not applicable)</label>
					<input type="number" onchange="setTwoNumberDecimal" min="0" step="0.5" name="interest" id="interest" class="form-control" value="<?php echo $rwx['interest']; ?>" />
				</div>

				<?php if ($p_title == 'Family') { ?>

					<div class="form_sep">
						<label for="" class="">Credit Limit (Family Folder Only)</label>
						<input type="number" min="0" name="credit_setup" id="" class="form-control" value="<?php echo $rwx['credit_setup']; ?>" />
					</div>

				<?php } else { ?>
					<input type="hidden" name="credit_setup" id="" value="0" />
				<?php } ?>

				<div class="form_sep">
					<label for="add_minus">
						Choose whether to apply an Extra Charge (P) or a Discount (N) based on the specified percentage of the hospital price:
					</label>
					<select name="add_minus" id="add_minus" class="form-control">
						<option value="nil" <?php echo ($rwx['add_minus'] == '' || $rwx['add_minus'] == 'nil') ? 'selected' : ''; ?>>
							Select...
						</option>
						<option value="P" <?php echo ($rwx['add_minus'] == 'P') ? 'selected' : ''; ?>>P</option>
						<option value="N" <?php echo ($rwx['add_minus'] == 'N') ? 'selected' : ''; ?>>N</option>
					</select>
				</div>



				<div class="form_sep">
					<label for="access_to" class="req">Access Hospital Services</label>
					<select name="access_to" id="access_to" class="form-control" required>
						<option value="" <?php echo empty($rwx['services_access']) ? 'selected' : ''; ?>>
							Select...
						</option>
						<option value="0" <?php echo ($rwx['services_access'] === '0') ? 'selected' : ''; ?>>
							Limited
						</option>
						<option value="1" <?php echo ($rwx['services_access'] === '1') ? 'selected' : ''; ?>>
							All
						</option>
					</select>
				</div>





				<div class="form_sep">
					<label for="reg_textarea_message" class="">Phone</label>
					<input type="text" name="phone" id="phone" value="<?php
																		echo $rwx['phone']; ?>" class="form-control">
				</div>

				<div class="form_sep">
					<label for="reg_textarea_message" class="">Address</label>
					<input type="text" name="addr" id="addr" value="<?php
																	echo $rwx['addr']; ?>" class="form-control">
				</div>

				<div class="form_sep">
					<button class="btn btn-success btn-xs" type="submit" name="save_record" id="save_record">Save</button>
					<input type="hidden" name="ptm" value="<?php echo $ptm;  ?>" />
				</div>
				<input type="hidden" name="insurance_no" id="insurance_no" value="<?php echo $ptm; ?>"> <input type="hidden" name="mode" id="mode" value="<?php echo $mode; ?>">
				<input type="hidden" name="ptm" id="ptm" value="<?php echo $ptm_2; ?>">
			</form>


			<?php
		}

		if (isset($_POST["claim_income_id"])) {

			$claim_income_id = $_POST["claim_income_id"];
			$part = explode("/", $claim_income_id);

			$hmo_no = $part[0];
			$last_month = $part[1];
			$last_yr = $part[2];
			$insured_claim = $part[3];
			$enrollees = $part[4];

			$stmt = $db->query("SELECT * FROM  insurance_claim_rpt WHERE insurance_no='$hmo_no' order by sn");
			if ($stmt->rowCount() > 0) { ?>
				<div id="test_fields">
					<table class="table table-striped">
						<thead>
							<tr>
								<th data-toggle="true">No.</th>
								<th data-toggle="true">Month/Year</th>
								<th data-toggle="true">Settlement</th>
								<th data-toggle="true">Date Posted</th>
								<th data-toggle="true">By</th>
							</tr>
						</thead>
						<tbody>

							<?php
							$n = 1;
							while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
							?>
								<tr>
									<td><?php echo $n; ?></td>
									<td><?php echo $roww['month'] . '/' . $roww['year']; ?></td>
									<td><?php echo $roww['settlement']; ?></td>
									<td><?php echo $roww['date_post']; ?></td>
									<td><?php echo $roww['post_by']; ?></td>
								</tr>
							<?php
								$n++;
							} ?>

						</tbody>
					</table>
				<?php } else { ?>
					<br><br><strong>No Income Found</strong><br>
					<hr>

				<?php } ?>

				<form method="POST" action="index.php?cptclaim=<?php $hmo_no . '/' . $last_month . '/' . $last_yr; ?>">
					<table cellpadding="20">
						<tr>
							<td align="right"><strong>Month/Year</strong></td>
							<td>&nbsp;&nbsp;<strong style="font-size:14px"><?php echo $last_month . ' / ' . $last_yr ?></strong></td>
						</tr>
						<tr>
							<td align="right"><strong>Total Claim / Enrollees: </strong></td>
							<td> &nbsp;&nbsp;<strong style="font-size:14px"><?php echo $insured_claim . ' / ' . $enrollees . 'patients'; ?></strong></td>
						</tr>
					</table>
					<hr>
					<div class="form_sep">
						<label for="reg_input_no" class="req">Enter Income (Recovered Claims) for month: <?php echo $last_month . ' / ' . $last_yr; ?></label>
						<input type="text" id="recovered_claims" name="recovered_claims" class="form-control" placeholder="" maxlength="20" value="" required>
					</div>


					<div class="form_sep">
						<button class="btn btn-primary btn-xs" type="submit" name="save_income">Save Income</button>
						<input type="hidden" name="last_month" value="<?php echo $last_month; ?>" />
						<input type="hidden" name="last_yr" value="<?php echo $last_yr; ?>" />
						<input type="hidden" name="hmo_no" value="<?php echo trim($hmo_no); ?>" />

					</div>
				</form>

			<?php

		}



		if (isset($_POST["appt_patient_id"])) {
			///	10/NHIS/005000/Veronica David Munkai/Paediatric
			////$book_path=$interest.'/'.$insurance .'/'.$hosp_no.'/'.$name.'/'
			//	30/PHIS/001390/Lumhan Nander Salmwang/old/1018/N/gopd

			$appt_patient_id = $_POST["appt_patient_id"];

			$part = explode("/", $appt_patient_id);
			$interest = $part[0];
			$insurance = $part[1];
			$hosp_no = $part[2];
			$name = $part[3];
			$visit_status = $part[4];
			$insurance_no = $part[5];
			$add_minus = $part[6];
			$payment_mode = $part[7];
			$dept = $part[8];
			$vaccine_status = 0;

			if ($dept == 'physio') {   /// this is for special modules physio and antenatal
				$queue_center = 'PY';
			} elseif ($dept == 'Antenatal') {
				$queue_center = 'MF';	/// stands for midwify
			} else {
				$queue_center = 'DR';
			}

			if (strtoupper($dept) == 'VACCINATION' or strtoupper($dept) == 'IMMUNIZATION' or strtoupper($dept) == 'VACCINE') {
				$search_part = "item_service like '%$dept%' and ";
				$vaccine_status = 1;
			} elseif ($dept == 'emergency') {
				$search_part = "item_service like '%emerg%'  and ";
				$where_string_1 = " ";
				$where_string_2 = " ";
				$dept_id = 'more';
			} elseif ($dept == 'More_services') {
				$search_part = "item_service !='vaccination' and ";
				$where_string_1 = " ";
				$where_string_2 = " ";
				$dept_id = 'more';
			} else {
				$stmt = $db->query("SELECT * FROM department WHERE department like '$dept%'");
				if ($stmt->rowCount() > 0) {
					$roww = $stmt->fetch(PDO::FETCH_ASSOC);
					$dept_id = $roww['sn'];
				} else {
					$dept_id = '';
				}
				$search_part = " (dept='$dept_id' or item_service like '%$dept%') and item_service !='vaccination' and ";
				$where_string_1 = " h.Department='$dept_id' AND ";
				$where_string_2 = " WHERE dept='$dept_id' ";
			}

			///echo $search_part;
			?>

				<form method="POST" id="booking_form">
					<div align="right">
						Booking appointment for :<br><strong style="font-size:18px"><?php echo $name; ?></strong>
						<div class="form_sep"> </div>
					</div>

					<div class="form_sep">
						<table width="100%">
							<tr>
								<td colspan="2">


									<label for="reg_input_no" class="req">Select Consultation Services</label>
									<select name="consultation_services" data-placeholder="Search..." id="consultation_services" class="form-control" style="font-size: 15px; " required>
										<option selected value="">-- select --</option>

										<?php

										$coverage = ($insurance === 'NHIS') ? "AND coverage='PRIVATE/NHIS'" : "";

										$stmt2 = $db->query("SELECT * FROM prices_table WHERE $search_part status='0' and hosp_price>0 and ext_price>0 and duration>0 and price_table='Consultation' $coverage order by item_service");
										if ($stmt2->rowCount() > 0) { ?>
											<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $roww["sn"] . '__' . $roww["item_service"] . '__' . $roww["hosp_price"] . '__' . $roww["nhis_price"] . '__' . $roww["insurance_type"] . '__' . $roww["dept"] . '__' . $roww["category"] . '__' . $roww["price_table"] . '__' . $roww["duration"] . '__' . $roww["file_amt"] . '__' . $roww["specialist_id"] . '__' . $roww["ext_price"] . '__' . $queue_center;  ?>"><?php echo $roww["item_service"]; ?></option>
										<?php }
										} ?>
									</select>



									<br>
								</td>


							</tr>





							<tr>
								<td style=" padding-right:12px; " width="30%" align="right"><br><strong>Set Appointment by</strong></td>
								<td width="70%"> <br>
									<select name="ap_type" id="ap_type" class="form-control" style="font-size: 15px; ">
										<option selected="selected" value="">-- select --</option>
										<option value="doctorname">Doctor Name</option>
										<option value="specialist">Specialist</option>
									</select>
								</td>
							</tr>

							<tr>
								<td></td>
								<td>
									<div class="form_sep"></div>



									<div class="form_sep" id="doctorname_div">
										<div class="form-group">
											<label><strong>Book by doctor name (Optional)</strong></label>
											<select class="form-control" name="doctor_name" id="doctor_name" style="font-size: 15px; ">
												<option value="">-- Not Applicable --</option>
											</select>
										</div>
									</div>

									<div class="form_sep" id="specialist_div">
										<div class="form-group">
											<label><strong>Book by specialist (Optional)</strong></label>
											<select class="form-control" name="Specialist" id="Specialist" style="font-size: 15px; ">
												<option value="">-- Not Applicable --</option>
											</select>
										</div>
									</div>


								</td>
							</tr>


							<tr>
								<td style=" padding-right:12px; " width="30%" align="right"><br><strong>
										<label for="reg_input_no" class="req">Select Contact:</label></strong></td>
								<td width="70%"> <br>


									<select name="how_contact" id="how_contact" class="form-control" style="font-size: 15px; " required>
										<option selected="selected" value="">-- select --</option>
										<?php if ($queue_center == 'DR') { ?>
											<option value="checkin">Seeing Doctor Now</option>
											<option value="future">Future Appointment</option>
										<?php } else { ?>
											<option value="checkin">Checking Now</option>
										<?php } ?>

									</select>
								</td>
							</tr>
							<?php ///}
							?>

							<tr>
								<td></td>
								<td>
									<div id="id_later">
										<table>
											<tr>
												<td>
													<label for="reg_select" class="">Time</label>
													<select name="ap_time" id="ap_time" class="form-control" style="font-size: 15px; ">
														<option selected="selected" value=""> </option>

														<option value="7:00 AM">7:00 AM</option>
														<option value="7:30 AM">7:30 AM</option>
														<option value="8:00 AM">8:00 AM</option>
														<option value="8:30 AM">8:30 AM</option>
														<option value="9:00 AM">9:00 AM</option>
														<option value="9:30 AM">9:30 AM</option>
														<option value="10:00 AM">10:00 AM</option>
														<option value="10:30 AM">10:30 AM</option>
														<option value="11:00 AM">11:00 AM</option>
														<option value="11:30 AM">11:30 AM</option>
														<option value="12:00 PM">12:00 PM</option>
														<option value="12:30 PM">12:30 PM</option>
														<option value="1:00 PM">1:00 PM</option>
														<option value="1:30 PM">1:30 PM</option>
														<option value="2:00 PM">2:00 PM</option>
														<option value="2:30 PM">2:30 PM</option>
														<option value="3:00 PM">3:00 PM</option>
														<option value="3:30 PM">3:30 PM</option>
														<option value="4:00 PM">4:00 PM</option>
														<option value="4:30 PM">4:30 PM</option>
														<option value="5:00 PM">5:00 PM</option>
														<option value="5:30 PM">5:30 PM</option>
														<option value="6:00 PM">6:00 PM</option>
														<option value="6:30 PM">6:30 PM</option>
														<option value="7:00 PM">7:00 PM</option>
														<option value="7:30 PM">7:30 PM</option>
														<option value="8:00 PM">8:00 PM</option>
														<option value="8:30 PM">8:30 PM</option>
														<option value="9:00 PM">9:00 PM</option>
														<option value="9:30 PM">9:30 PM</option>
														<option value="10:00 PM">10:00 PM</option>
														<option value="10:30 PM">10:30 PM</option>
														<option value="11:00 PM">11:00 PM</option>
														<option value="11:30 PM">11:30 PM</option>
														<option value="12:00 AM">12:00 AM</option>
														<option value="12:30 AM">12:30 AM</option>
														<option value="1:00 AM">1:00 AM</option>
														<option value="1:30 AM">1:30 AM</option>
														<option value="2:00 AM">2:00 AM</option>
														<option value="2:30 AM">2:30 AM</option>
														<option value="3:00 AM">3:00 AM</option>
														<option value="3:30 AM">3:30 AM</option>
														<option value="4:00 AM">4:00 AM</option>
														<option value="4:30 AM">4:30 AM</option>
														<option value="5:00 AM">5:00 AM</option>
														<option value="5:30 AM">5:30 AM</option>
														<option value="6:00 AM">6:00 AM</option>
														<option value="6:30 AM">6:30 AM</option>
													</select>
												</td>
												<td>
													<div id="data_1">
														<label class="font-noraml">Date</label>
														<div class="input-group">
															<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
															<input type="date" class="form-control" id="ap_date" name="ap_date" value="<?php echo date("Y-m-d"); ?>">
														</div>
													</div>
												</td>
											</tr>
										</table>




									</div>
								</td>
							</tr>
						</table>
					</div>





					<div class="form_sep">
						<div class="pull-left">
							<button class="btn btn-success btn-sm" type="submit" name="book_now" id="book_now"><i class="fa fa-check"></i>&nbsp;Book Now</button>
						</div>
						<div class="pull-right">

							<a href="index.php?ptm=all/<?= $hosp_no; ?>" class="btn btn-danger btn-sm"><i class="fa fa-times"></i>&nbsp;Cancel</a>
						</div>

					</div>
					<input type="hidden" name="interest" id="interest" value="<?php echo $interest; ?>" />
					<input type="hidden" name="add_minus" id="add_minus" value="<?php echo $add_minus; ?>" />
					<input type="hidden" name="payment_mode" id="payment_mode" value="<?php echo $payment_mode; ?>" />
					<input type="hidden" name="insurance" id="insurance" value="<?php echo $insurance; ?>" />
					<input type="hidden" name="insurance_no" id="insurance_no" value="<?php echo $insurance_no; ?>" />
					<input type="hidden" name="hosp_no" id="hosp_no" value="<?php echo $hosp_no; ?>" />
					<input type="hidden" name="patient_name" id="patient_name" value="<?php echo $name; ?>" />
					<input type="hidden" name="dept_id" id="dept_id" value="<?php echo $dept_id; ?>" />
					<input type="hidden" name="visit_status" id="visit_status" value="<?php echo $visit_status; ?>" />

				</form>

				<?php
			}

			if (isset($_POST["consultation_services"])) {

				///echo $roww["sn"] . '__' . $roww["item_service"] . '__' . $roww["hosp_price"] . '__' . $roww["nhis_price"] . '__' . $roww["insurance_type"] . '__' . $roww["dept"] . '__' . $roww["category"] . '__' . $roww["price_table"] . '__' . $roww["duration"] . '__' . $roww["file_amt"] . '__' . $roww["specialist_id"] . '__' . $roww["ext_price"];

				$error_tracker = '';
				$consultation_services = $_POST["consultation_services"];
				$parts = explode("__", $consultation_services);
				$sn = $parts[0];
				$sn_1 = $parts[0];
				$item_service = $parts[1];
				$hosp_price_main = $hosp_price = $parts[2];
				$Consultation_price = $parts[2];
				$nhis_price = $parts[3];
				$service_access = $parts[4];
				$dept = $parts[5];
				$category = $parts[6];
				$price_table = $parts[7];
				$duration = $parts[8];
				$file_amt = $parts[9];
				$default_specialist = $parts[10];
				$ext_price = $parts[11];
				$ext_Consultation_price = $parts[11];
				$queue_center = $parts[12];

				$doctor_name = $_POST["doctor_name"];
				$Specialist = $_POST["Specialist"];
				$ap_time = $_POST["ap_time"];
				$ap_date = $_POST["ap_date"];
				$interest = $_POST["interest"];
				$add_minus = $_POST["add_minus"];
				$payment_mode = $_POST["payment_mode"];
				$hos_no = $_POST["hosp_no"];
				$patient_name = $_POST["patient_name"];
				$dept_id = $dept; ///$_POST["dept_id"];
				$insurance = $_POST["insurance"];
				$insurance_no = $_POST["insurance_no"];
				$ap_type = $_POST["ap_type"];
				$visit_status = $_POST["visit_status"];


				if ($dept_id == 'more') {
					// Get department ID from prices_table based on item_service
					$stmt = $db->prepare("SELECT dept FROM prices_table WHERE item_service = ?");
					$stmt->execute([$item_service]);
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($row) {
						$dept_id = $row['dept'];
					}
				}
				// Fetch department name if dept_id is valid
				$department = '';
				if (!empty($dept_id)) {
					$stmt = $db->prepare("SELECT department FROM department WHERE sn = ?");
					$stmt->execute([$dept_id]);
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($row) {
						$department = $row['department'];
					}
				}


				//////////////////////// CHECKING EXISTING APPOINTMENT
				// Use a prepared statement to avoid SQL injection and improve efficiency
				$stmt = $db->prepare("
						SELECT appt_no, ap_date_time, date_ap, ap_time, status
						FROM apptm
						WHERE hospital_no = ? 
						AND service_id = ? 
						AND status IN ('checkin', 'future')
						LIMIT 1");
				$stmt->execute([$hos_no, $sn_1]);

				if ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
					// Appointment exists
					$appt_no      = $rwx['appt_no'];
					$ap_date_time = $rwx['ap_date_time'];
					$date_ap      = $rwx['date_ap'];
					$ap_time      = $rwx['ap_time'];
					$status       = ($rwx['status'] === 'checkin') ? '' : $rwx['status'];


					date_default_timezone_set('Africa/Lagos');
					$setdate = new DateTime();
					$appt_date = new DateTime($ap_date_time);
					$diff = $appt_date->diff($setdate);
					$date_time_remain = (int)$diff->format('%a');
					$app_status = ($setdate > $appt_date) ? 'past' : 'notyet';

					////-------------------------------
					$stmt = $db->prepare("
								SELECT paystatus, item_services, pay 
								FROM patient_ap_services 
								WHERE hospital_no = ? 
								AND drug_sn = ?
								LIMIT 1");
					$stmt->execute([$hos_no, $sn_1]);

					if ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
						// Appointment exists
						$item_services_2 = $rwx['item_services'];
						$amt_pay = (int)$rwx['pay'];
						$paystatus = (int)$rwx['paystatus'];

						if ($paystatus === 1 && $amt_pay == 0) {
							$title_ = "This patient already has a " .  htmlspecialchars($item_services_2)  .
								" appointment that has been fully processed.";
							$cancel = "ap/approve/reverse";
						} else if ($paystatus === 1 && $amt_pay > 0) {
							$title_ = "This patient has an existing " . htmlspecialchars($status) .
								" appointment that has already been <br> paid/validated for <u>" .
								htmlspecialchars($item_services_2) . "</u>.";
							$cancel = "ap/approve/reverse";
						} else {
							$title_ = "This patient has an existing appointment awaiting payment/validation!";
							$cancel = "ap/approve/paypending";
						}
					} else {
						// No record found — clean up old appointment entry safely
						$deleteStmt1 = $db->prepare("
								DELETE FROM apptm 
								WHERE service_id = :service_id 
								AND appt_no = :appt_no");
						$deleteStmt1->bindParam(':service_id', $sn_1, PDO::PARAM_STR);
						$deleteStmt1->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
						$deleteStmt1->execute();

						echo '
							<div class="alert alert-danger" style="font-weight:bold;">
								<i class="fa fa-exclamation-triangle"></i>
								Oops! An error occurred previously. Please close this window and try again.
							</div>';
					} ?>

					<div align="right">
						<span style="color:#F00"> <?php echo $title_; ?></span><br><strong style="font-size:18px"><?php echo $patient_name; ?></strong>
						<div class="form_sep"> </div>
					</div>
					<hr>

					<b style="color:#F00;">Click the Action button below to take an action.</b>
					<table class="table table-striped">
						<thead>
							<tr>
								<th data-toggle="true">A/#</th>
								<th data-toggle="true">Status</th>
								<th data-toggle="true">A/Date</th>
								<th data-toggle="true">Consultation</th>
								<th data-toggle="true">By</th>
							</tr>
						</thead>
						<tbody>

							<?php

							$n = 1;
							// Safely prepare query with bound parameters
							$sql = "SELECT sn, appt_no, status, queue_lock,service_id,ap_date_time,services_name,checkin_by,referal_doc,doctor_id,app_by
									FROM apptm
									WHERE hospital_no = :hos_no
									AND service_id  = :sn_1
									AND (status = 'checkin' OR status = 'future')
									ORDER BY sn DESC";
							$stmt = $db->prepare($sql);
							$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
							$stmt->bindParam(':sn_1', $sn_1, PDO::PARAM_STR);
							$stmt->execute();
							// Fetch rows safely

							while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
							?>
								<tr>
									<td><?php echo $roww['appt_no']; ?></td>
									<td>
										<?php
										$appt_no = $roww['appt_no'];
										$queue_lock = $roww['queue_lock'];
										$service_id = $roww['service_id'];

										$stmt77 = $db->prepare("
													SELECT paystatus, sn, pay_mode, transact_date
													FROM patient_ap_services
													WHERE hospital_no = ?
													AND app_no = ?
													AND paystatus = '1'
													AND serv_group = 'Consultation'
													LIMIT 1");

										$stmt77->execute([$hos_no, $appt_no]);
										$st = 'pendin'; // default status
										if ($rwxx = $stmt77->fetch(PDO::FETCH_ASSOC)) {
											$sn             = $rwxx['sn'];
											$pay_mode       = $rwxx['pay_mode'];
											$transact_date  = date('Y-m-d', strtotime($rwxx['transact_date']));
											$today_date     = date('Y-m-d');

											if ($pay_mode === 'claim') {
												$title  = 'Posted';
												$title2 = 'Post Pending';
											} else {
												$title  = 'Paid';
												$title2 = 'Payment Pending';
											}
											echo $title;
											$st = 'paid';
										} else {
											echo 'Payment Pending';
										}
										?>
										<br>

										<div class="btn-group">
											<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown">
												Action <span class="caret"></span>
											</button>
											<ul class="dropdown-menu" role="menu">
												<?php
												// Safety defaults
												$st              = isset($st) ? $st : '';
												$queue_lock      = isset($queue_lock) ? $queue_lock : 0;
												$transact_date   = isset($transact_date) ? $transact_date : '';
												$setdate_date    = isset($setdate_date) ? $setdate_date : '';
												$app_status      = isset($app_status) ? $app_status : '';
												$service_id      = isset($service_id) ? $service_id : '';
												$hos_no          = isset($hos_no) ? $hos_no : '';
												$pay_mode        = isset($pay_mode) ? $pay_mode : '';
												$roww['appt_no'] = isset($roww['appt_no']) ? $roww['appt_no'] : '';
												$sn              = isset($sn) ? $sn : '';

												$doctor_id = trim($roww['doctor_id']);
												$app_by = trim($roww['app_by']);
												if (!empty($doctor_id) && $doctor_id != "0") {
													$stmt = $db->prepare("SELECT fullname FROM admin_users WHERE id = :id LIMIT 1");
													$stmt->execute([':id' => $doctor_id]);
													$rxw = $stmt->fetch(PDO::FETCH_ASSOC);
													$fullname_ = $rxw ? '<b>(' . trim($rxw['fullname']) . ')</b>' : '';
												} else if (!empty($app_by) && $app_by != "anydoctor") {
													$stmt = $db->prepare("SELECT fullname FROM admin_users WHERE username = :app_by LIMIT 1");
													$stmt->execute([':app_by' => $app_by]);
													$rxw = $stmt->fetch(PDO::FETCH_ASSOC);
													$fullname_ = $rxw ? '<b>(' . trim($rxw['fullname']) . ')</b>' : '';
												} else {
													$fullname_ = $roww['referal_doc'];
												}


												// ------------------------------------
												// CHECK IF NOTE EXISTS FOR THIS APPOINTMENT
												// --------------------------------------

												$note_sql = "SELECT sn FROM notes 
													WHERE hospital_no = :hos_no 
													AND app_no = :appt_no
													LIMIT 1";
												$note_stmt = $db->prepare($note_sql);
												$note_stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
												$note_stmt->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
												$note_stmt->execute();
												$note_exists = ($note_stmt->rowCount() > 0) ? true : false;

												// CASE 1: Paid, unlocked, and same-day transaction → allow deletion/reversal
												if ($st == 'paid' && $queue_lock == 0 && $transact_date == $setdate_date && $note_exists == false) { ?>
													<li>
														<a href="index.php?ptm=all/<?php echo $hos_no . '&drv=' . $hos_no . '/' . $roww['appt_no'] . '/' . $sn . '/' . $pay_mode; ?>"
															onclick="return confirm('Are you sure you want to delete appointment ?')">
															Delete Appointment & Reverse Payment
														</a>
													</li>

												<?php
													// CASE 2: Paid, unlocked, and past transaction date → allow adding to queue
												} elseif ($st == 'paid' && $queue_lock == 0 && $transact_date != $setdate_date) { ?>
													<li>
														<a href="index.php?equiry&see_doctor_now=<?php echo $roww['appt_no'] . '&s_id=' . $service_id; ?>">
															<b>Add Patient to Doctor Queue List</b>
														</a>
													</li>

												<?php
													// CASE 3: Paid and already in queue → allow requeue and reopen
												} elseif ($st == 'paid' && $queue_lock == 1) { ?>
													<li>
														<a href="index.php?equiry&see_doctor_now=<?php echo $roww['appt_no'] . '&s_id=' . $service_id; ?>">
															<b>Add Patient to Doctor Queue List</b>
														</a>
													</li>


												<?php
													// CASE 4: Pending appointments → allow simple deletion
												} elseif ($st == 'pendin' && $note_exists == false) { ?>
													<li>
														<a href="index.php?ptm=all/<?php echo $hos_no . '&dlp=' . $hos_no . '/' . $roww['appt_no']; ?>">
															Delete Appointment
														</a>
													</li>
												<?php }
												if ($st == 'paid' && ($note_exists == true || $fullname_ != 'Any Doctor')) {
												?>
													<li>
														<a href="javascript:void(0);" id="show_refer">
															Add Patient to Queue And Refer to Another Doctor
														</a>
													</li>


													<li>
														<a href="index.php?ptm=all/<?php echo $hos_no . '&dgr=' . $hos_no . '/' . $roww['appt_no'] . '/' . $sn . '/' . $pay_mode; ?>"
															onclick="return confirm('Are you sure you want to Close Existing appointment ?')">
															Close Appointment & Open New
														</a>
													</li>

												<?php  }

												// CASE 5: Past or future appointments — correct comparison operators used
												if ((in_array($app_status, array('past', 'notyet'))) && $st == 'paid' && isset($roww['status']) && $roww['status'] == 'future') { ?>
													<li>
														<a href="index.php?ptm=all/<?php echo $hos_no . '&drn=' . $roww['appt_no']; ?>"
															onclick="return confirm('Are you sure you want the patient to see doctor ?')">
															See Doctor Now
														</a>
													</li>
												<?php } ?>
											</ul>
										</div>

									</td>
									<td><?php echo date("d M Y", strtotime($roww['ap_date_time'])); ?><br>
										<?php echo date("h:i:s a", strtotime($roww['ap_date_time'])); ?><br>
										<?php if ($roww['status'] == 'checkin') {
											echo '<strong>On-Queue</strong>';
										} else {
											echo '<strong>Future</strong>';
										} ?>
									</td>
									<td><?php



										echo $roww['services_name']; ?><br><?php echo $fullname_; ?></td>
									<td><?php echo $roww['checkin_by']; ?></td>
								</tr>
							<?php
								$n++;
							} ?>

						</tbody>
					</table>

					<div id="refer_appoint">
						<?php if ($st == 'paid' && $note_exists == true) { ?>
							<form action="index.php?equiry" method="POST">

								<div class="form_sep">
									<label class="form_sep" class="req">Select Doctor Name you wish to Refer Patient</label>
									<select name="dr_name" id="dr_name" class="form-control" required style="font-size:15px;">

										<?php
										$stmt = $db->query("SELECT h.FirstName,u.fullname, h.LastName,h.Designation,u.username FROM hremp as h inner join admin_users as u on h.EmployeeCode=u.EmployeeCode WHERE (u.rights = 'DR' or u.rights = 'MD' or h.Designation = 'Radiologist') AND h.status = '0'  order by h.FirstName");
										?>
										<option selected="selected" value="">Select ...</option>
										<?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $rxw["username"]; ?>"><?php echo $rxw["fullname"]; ?></option>
										<?php  } ?>
									</select>
								</div>

								<div class="form_sep">
									<button class="btn btn-success btn-sm" type="submit" name="save_refer" id="save_refer">Refer Now</button>
								</div>

								<input type="hidden" name="app_no" value="<?php echo $appt_no; ?>" />
								<input type="hidden" name="hosp_no" value="<?php echo $hos_no; ?>" />
							</form>
						<?php  } ?>
					</div>

					<br>
					<br>


					<div class="pull-right">
						<a href="" class="btn btn-danger btn-sm"><i class="fa fa-times"></i>&nbsp;Cancel</a>
					</div>

				<?php

				} else {

					///// APPOINTMENT DOES NOT EXIST

					if (!empty($_POST['how_contact'])) {
						$how_contact = trim($_POST['how_contact']);
						$queue_lock = '0';
						$date_timee = date('Y-m-d H:i:s'); // default

						if ($how_contact === 'future') {
							$ap_date = !empty($_POST['ap_date']) ? trim($_POST['ap_date']) : '';
							$ap_time = !empty($_POST['ap_time']) ? date("H:i", strtotime($_POST['ap_time'])) : '00:00';
							$date_timee = date('Y-m-d H:i:s', strtotime("$ap_date $ap_time"));

							if ($date_timee <= date('Y-m-d H:i:s')) {
								$error_tracker .= "Future appointment date must be greater than current date/time. ";
							}
						}
					}

					///specialist
					// Default values
					$default_appl_by = $default_specialist ?: 'anydoctor';
					$referral_doctor = 'Any Doctor';

					if ($ap_type == '') {
						$appl_by = $default_appl_by;
					} elseif ($ap_type == 'doctorname') {
						$appl_by = $doctor_name ?: 'anydoctor';
						$referral_doctor = $doctor_name ?: 'Any Doctor';
					} elseif ($ap_type == 'specialist') {
						$appl_by = $Specialist ?: $default_specialist ?: 'anydoctor';
						$referral_doctor = $Specialist ?: $default_specialist ?: 'Any Doctor';
					} else {
						$appl_by = $default_appl_by;
					}


					$stmt_price = $db->prepare("
						SELECT nhis_price,hosp_price 
						FROM prices_table 
						WHERE item_service = 'New File' 
						LIMIT 1	");
					$stmt_price->execute();
					if ($row_price = $stmt_price->fetch(PDO::FETCH_ASSOC)) {
						$tbl_nhis_price = (float) $row_price['nhis_price'];
						$tbl_hosp_price = (float) $row_price['hosp_price'];
						if ($file_amt == 0) {
							$file_amt = $tbl_hosp_price;
						}
					}

					$target_sn = $sn_1;
					$_tariff_table = "hmo_medical_tariff";
					$file_amt_actual = $file_amt;

					if ($visit_status == 'new' and $insurance == 'NHIS') {
						$file_amt_actual = $tbl_nhis_price;
					} elseif ($visit_status == 'new' and $insurance != 'Private(Self)') {
						$hosp_price = $file_amt;
						$ext_price = $file_amt;
						$file_amt = $claim_amt;
						$file_amt_actual = $claim_amt;
					}

					$hosp_price = $Consultation_price;
					$ext_price = $ext_Consultation_price;
					$NHIS_DRUG_CONSUMBL_STATE = null;
					include("../inc/price_calc.php");
					$doctor_id = null;					?>


					<form method="POST" action="booknow.php">
						<table width="100%">
							<tr>
								<td>
									<div align="left">

										<?php
										$show_status_free = null;
										if ($amt_paying == 100 and $claim_amt = 100) {
											$show_status_free = 1; ?>
											<strong>FREE APPOINTMENT</strong>
										<?php } else { ?>
											<strong><?php if ($amt_paying > 0) {
														echo 'Cash Paying: ';
													} else {
														echo 'Claim: ';
													} ?></strong>
											<strong style="font-size:20px">
												<?php if ($amt_paying > 0) {
													echo number_format($amt_paying);
												} else {
													echo number_format($claim_amt);
												} ?>
											</strong>/only<br />

										<?php } ?>

										<p style="font-size:15px; "><?php echo $item_service; ?></p>
										<?php
										if ($visit_status == 'new') {
											// Check if the selected service ($sn) has 'file_amt' marked as '1'
											$stmt = $db->prepare("SELECT sn FROM prices_table WHERE sn = ? AND file_amt = '1'");
											$stmt->execute(array($sn));

											if ($stmt->rowCount() == 0) {
												// No free file amount found → Check patient's existing 'New File' charge status
												$stmt = $db->prepare("
														SELECT sn, paystatus 
														FROM patient_ap_services 
														WHERE item_services = 'New File' 
														AND hospital_no = ? 
														ORDER BY sn DESC 
														LIMIT 1");
												$stmt->execute(array($hos_no));
												if ($row_dx = $stmt->fetch(PDO::FETCH_ASSOC)) {
													$paystatus = (int) $row_dx['paystatus'];
													// Display file amount only if not yet paid
													if ($paystatus === 0) {
														echo '<strong>File Amount:</strong> ';
														echo '<strong style="font-size:20px;">' . number_format($file_amt_actual) . '</strong>';
													}
												}
											} else {
												// File is free
												echo '<strong>File Amount: </strong><strong style="font-size:20px;">FREE</strong>';
												// Remove any unpaid 'New File' record for this patient
												$delete = $db->prepare("
														DELETE FROM patient_ap_services 
														WHERE item_services = 'New File' 
														AND hospital_no = ? 
														AND paystatus = 0
													");
												$delete->execute(array($hos_no));
											}
										}

										if ($amt_paying == 0 && $show_status_free == null) { ?>
											<div class="checkbox i-checks">
												<label>
													<input type="checkbox" value="pay" name="self_pay">
													<strong style="color:#00C">Choose if Self Pay Consultation</strong>
												</label>
											</div>
										<?php }

										$emr = $hos_no;
										$general_credit_limit =	$_SESSION['credit_limit_status'];
										$items = call_current_balance($db, $emr, $general_credit_limit);
										$current_balance =  $items["current_balance"];
										$patient_name      = $items['patient_name'];
										$names             = $items['names'];
										$gender            = $items['gender'];
										$address           = $items['address'];
										$referral_name     = $items['referral_name'];
										$discount_set      = $items['discount_set'];
										$insurance_type    = $items['insurance_type'];
										$insurance_no      = $items['insurance_no'];
										$save_insurance_no = $items['save_insurance_no'];
										$wallet_amount     = $items['wallet_amount'];
										$wallet_account          = $items['wallet_account'];
										$credit_limit  = $items['bal_credit_limit'];

										if ($current_balance > $amt_paying && $show_status_free == null) { ?>
											<h3 style="color: red; ">Patient Has Sufficient Balance</h3>
											<div class="checkbox i-checks">
												<label>
													<input type="checkbox" value="pay_from_deposit" name="pay_from_deposit">
													<strong style="color:#00C">Choose to Pay from Patient Wallet</strong>
												</label>
											</div>
										<?php }

										?>
								</td>

								<td>
									<div align="right">
										Appointment Confirmation for :<br><strong style="font-size:18px"><?php echo $patient_name; ?></strong>
										<div class="form_sep"> </div>
									</div>
								</td>
							</tr>
						</table>
						<hr>
						<table style="padding:10px; ">
							<?php if ($doctor_name != '') { ?>
								<tr>
									<td align="right"><strong style=" font-size:14px">Doctor: &nbsp; </strong></td>
									<td>
										<?php
										$fullname = '';
										$doctor_id = null;
										$stmt = $db->prepare("SELECT id, fullname FROM admin_users WHERE username = ?");
										$stmt->execute(array($doctor_name));
										if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
											$fullname = $row['fullname'];
											echo $fullname;
											$doctor_id = $row['id'];
										}

										?>
									</td>
								</tr>
							<?php } ?>

							<tr>
								<td align="right"><strong style=" font-size:14px">Department: &nbsp; </strong></td>
								<td>
									<?php echo $department; ?>
								</td>
							</tr>

							<tr>
								<td align="right"><strong style=" font-size:14px">Contact: &nbsp; </strong></td>

								<td>
									<?php if ($how_contact == 'checkin') {
										echo ($queue_center == 'DR') ? 'Seeing Doctor Now' : 'Checked In';
									} else {
										echo 'Future Appointment /<br>';
										echo date('d M Y H:i:s', strtotime($date_timee));;
									} ?>
								</td>
							</tr>
							<tr>
								<td align="right"><strong style=" font-size:14px">Duration: &nbsp; </strong></td>
								<td align="left"> <?php
													echo ($amt_paying == 100 and $claim_amt = 100) ? '1' : $duration; ?>/Day(s)
								</td>
							</tr>

						</table>
						<hr>

						<div class="form_sep">
							<div class="pull-left">
								<button type="submit" class="btn btn-success btn btn-sm" name="finalbook_now" id="finalbook_now">Finish Booking</button>
							</div>
							<div class="pull-right">
								<a href="" class="btn btn-danger btn-sm"><i class="fa fa-times"></i>&nbsp;Cancel</a>
							</div>
						</div>

						<input type="hidden" name="service_access" id="service_access"
							value="<?php echo ($insurance === 'NHIS' || $insurance === 'PHIS') ? '1' : '3'; ?>" />

						<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>" />
						<input type="hidden" name="item_sn" value="<?php echo $sn; ?>" />
						<input type="hidden" name="appl_by" value="<?php echo $appl_by ?>" />
						<input type="hidden" name="doctor_id" value="<?php echo $doctor_id ?>" />
						<input type="hidden" name="referral_doctor" value="<?php echo $referral_doctor ?>" />
						<input type="hidden" name="ap_time" value="<?php echo $ap_time ?>" />
						<input type="hidden" name="ap_date" value="<?php echo $ap_date ?>" />
						<input type="hidden" name="date_timee" value="<?php echo $date_timee ?>" />
						<input type="hidden" name="interest" value="<?php echo $interest ?>" />
						<input type="hidden" name="add_minus" value="<?php echo $add_minus ?>" />
						<input type="hidden" name="payment_mode" value="<?php echo $payment_mode ?>" />
						<input type="hidden" name="hosp_no" value="<?php echo $hos_no ?>" />
						<input type="hidden" name="patient_name" value="<?php echo $patient_name ?>" />
						<input type="hidden" name="insurance_type" value="<?php echo $insurance ?>" />
						<input type="hidden" name="insurance_no" value="<?php echo $insurance_no ?>" />
						<input type="hidden" name="item_service" value="<?php echo $item_service ?>" />
						<input type="hidden" name="how_contact" value="<?php echo $how_contact ?>" />
						<input type="hidden" name="queue_lock" value="<?php echo $queue_lock ?>" />
						<input type="hidden" name="price_table" value="<?php echo $price_table ?>" />
						<input type="hidden" name="category" value="<?php echo $category ?>" />
						<input type="hidden" name="claim_amt" value="<?php echo $claim_amt ?>" />
						<input type="hidden" name="amt_paying" value="<?php echo $amt_paying ?>" />
						<input type="hidden" name="hosp_price" value="<?php echo $hosp_price_main ?>" />
						<input type="hidden" name="ext_price" value="<?php echo $ext_price ?>" />
						<input type="hidden" name="pay_mode" value="<?php echo $pay_mode ?>" />
						<input type="hidden" name="fullname" value="<?php echo $_SESSION['fullname'] ?>" />
						<input type="hidden" name="visit_status" value="<?php echo $visit_status ?>" />
						<input type="hidden" name="duration" value="<?php echo $duration; ?>" />
						<input type="hidden" name="file_amt_actual" value="<?php echo $file_amt_actual; ?>" />
						<input type="hidden" name="ccop_int_charge" value="<?php echo $ccop_int_charge; ?>" />
						<input type="hidden" name="queue_center" value="<?php echo $queue_center; ?>" />
						<input type="hidden" name="cr" value="<?php echo $credit_limit; ?>" />
					</form>


				<?php
				}
			}



			if (isset($_POST["fields_new_id"])) {

				$fields_new_id = $_POST["fields_new_id"];
				$msg = trim($_POST["msg"]);
				$part = explode("__", $fields_new_id);
				$surname = $part[0];
				$fname = $part[1];
				$oname = $part[2];
				$gender = $part[3];
				$dob = $part[4];
				$phoneno = $part[5];
				?>

				<table width="100%">
					<tr>

						<td>
							<div align="right">
								<?php if ($msg == "PatientExist") {
									$hospital_no = ''; ?>
									<strong style="color: red">Patient Detail Already Exist in the Database!</strong><br>
								<?php } else { ?>
									New File Has Created Successfully :<br>
								<?php $hospital_no = $msg;
								} ?>

								<strong style="font-size:18px"><?php echo $surname . ' ' . $fname . ' ' . $oname; ?></strong>
								<div class="form_sep"> </div>
							</div>
						</td>
					<tr>
						<td colspan="2">
							<table>
								<tr>
									<td align="right">Hospital/EMR Number: &nbsp;</td>
									<td align="left"> <strong style="font-size:18; "><?php if ($hospital_no == '') {
																							echo 'Pending';
																						} else {
																							echo $hospital_no;
																						} ?></strong></td>
								</tr>
								<tr>
									<td align="right">Current Insurance Status: &nbsp;</td>
									<td align="left"><?php if ($msg == 'PatientExist') {
															echo 'Pending';
														} else { ?><strong style="font-size:18; color:#F00 ">Private(Self)</strong><?php } ?></td>
								</tr>
								<tr>
									<td colspan="2"></td>
								</tr>


								<tr>
									<td colspan="2">
										DOB: <?php echo date("d M Y", strtotime($dob)) ?> &nbsp;/&nbsp; Gender: <?php echo $gender; ?>
										&nbsp;/&nbsp; Phone #: <?php echo $phoneno; ?>
									</td>
								</tr>
							</table>


							<?php if ($msg == 'PatientExist') { ?>

								<hr>
								<form action="insert.php" method="post">
									<input type="hidden" name="surname" value="<?= $surname; ?>">
									<input type="hidden" name="fname" value="<?= $fname; ?>">
									<input type="hidden" name="oname" value="<?= $oname; ?>">
									<input type="hidden" name="dob" value="<?= $dob; ?>">
									<input type="hidden" name="gender" value="<?= $gender; ?>">
									<input type="hidden" name="phoneno" value="<?= $phoneno; ?>">

									<div class="pull-left">
										<button type="submit" class="btn btn-success btn btn-sm" name="Save_patient_save" id="Save_patient_save">Save & Continue ... </button>
										<input type="hidden" name="MM_update" value="add_new_patient_start" />
										<input type="hidden" name="PatientExist" value="PatientExist" />
									</div>
									<div class="pull-right">
										<button type="button" class="btn btn-danger btn btn-sm" data-dismiss="modal" aria-hidden="true">Cancel</button>
									</div>

								</form>
								<br>
								<hr>
								<div align="left" style="color:#F00; font-size:16px; ">Patient(s) with similary entries.</div><br>
								<?php
								$stmt = $db->query("SELECT * FROM enrollee WHERE surname LIKE '%$surname%' and fname LIKE '%$fname%' and gender LIKE '%$gender%' and dob LIKE '%$dob%'");
								if ($stmt->rowCount() > 0) { ?>

									<table class="table table-striped">
										<thead>
											<tr>
												<th data-toggle="true">Hospital # / Name</th>
												<th data-toggle="true">DOB</th>
												<th data-toggle="true">Phone Number / Address</th>
											</tr>
										</thead>
										<tbody>
											<?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
												<tr>
													<td>
														<?php
														echo $rwx['hospital_no'] . '<br>' . $rwx['surname'] . ', ' . $rwx['fname'] . ' ' . $rwx['oname'] . '<br>' .  '<strong>Insurance</strong>: ' . $rwx['insurance']; ?></td>
													<td><?php echo date("d M,Y", strtotime($rwx['dob'])); ?></td>
													<td><?php echo $rwx['phone'] . '<br>' . $rwx['addr']; ?></td>
												</tr>

											<?php } ?>
										</tbody>
									</table>
								<?php
								}
							} else { ?>

								<hr>
								<table width="100%">
									<tr>
										<td><a href="index.php?ptm=all/<?php echo $hospital_no ?>&more_data" class="btn btn-warning btn btn-sm">Add More Data</a></td>
										<td><a href="index.php?ptm=all/<?php echo $hospital_no ?>&app2" class="btn btn-success btn btn-sm">Book Appointment</a></td>
										<td><a href="index.php?ptm=all/<?php echo $hospital_no ?>&assign_patient_insur" class="btn btn-danger btn btn-sm">Assign Patient to Insurance</a></td>
										<td><a href="index.php" class="btn btn-warning btn btn-sm">Close</a></td>
									</tr>
								</table>

							<?php }

							?>
						<?php }



					if (isset($_POST['patient_refer_id'])) {
						$patient_refer_id = $_POST['patient_refer_id'];

						$part = explode("/", $patient_refer_id);
						///$hos_no.'/'.$row['appt_no']
						$hos_no = $part[0];
						$appt_no = $part[1];
						?>


							<form action="index.php?equiry" method="POST">

								<div class="form_sep">
									<label class="form_sep" class="req">Select Doctor Name you wish to Refer Patient</label>
									<select name="dr_name" id="dr_name" class="form-control" required style="font-size:15px;">

										<?php
										$stmt = $db->query("SELECT h.FirstName,u.fullname, h.LastName,h.Designation,u.username FROM hremp as h inner join admin_users as u on h.EmployeeCode=u.EmployeeCode WHERE (u.rights = 'DR' or u.rights = 'MD' or h.Designation = 'Radiologist') AND h.status = '0'  order by h.FirstName");
										?>
										<option selected="selected" value="">Select ...</option>
										<?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo $rxw["username"]; ?>"><?php echo $rxw["fullname"]; ?></option>
										<?php  } ?>
									</select>
								</div>

								<div class="form_sep">
									<button class="btn btn-success btn-sm" type="submit" name="save_refer" id="save_refer">Refer Now</button>
								</div>

								<input type="hidden" name="app_no" value="<?php echo $appt_no; ?>" />
								<input type="hidden" name="hosp_no" value="<?php echo $hos_no; ?>" />
							</form>

						<?php }	?>

						<div class="modal inmodal fade" id="write_off_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
							<div class="modal-dialog modal-lg">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
										<h4 class="modal-title" id="">Write Off</h4>
									</div>
									<div class="modal-body" id="write_off_body">
									</div>

								</div>
							</div>
						</div>


						<?php


						if (isset($_POST["auth_code_id"])) {
							$url = null;
							$auth_code_id = $_POST["auth_code_id"];
							$part = explode("/", $auth_code_id);
							///$hos_no.'/'.$row['appt_no']
							$hos_no = $part[0];
							$appt_no = $part[1];
							$view = $part[2];
							$url = $part[3];
							$part2 = explode("__", $url);
							$url = $part2[0] . '/' . $part2[1] . '/' . $part2[2];

							$stmt = $db->query("SELECT auth_code FROM apptm WHERE appt_no='$appt_no'");
							$rxw = $stmt->fetch(PDO::FETCH_ASSOC);

						?>

							<strong>Enter Authorization Code(s) for Secondary Care:</strong><br>
							<small style="color:#F63;">Separate using commas for more than one codes</small>

							<form action="index.php?<?php echo $view . '&url=' . $url; ?>" method="POST">

								<table width="100%">
									<tr>
										<td>
											<!--<input name="auth_code" id="auth_code" type="checkbox"  value="later">&nbsp;Enter Authorization Code Later</label>
-->
										</td>
									</tr>
									<tr>
										<td>

											<label for="reg_input_no" class=""></label>
											<input type="text" id="a_code" name="a_code" class="form-control" required maxlength="100" style="width:100%;" value="<?php if ($rxw["auth_code"] != '') {
																																										echo $rxw["auth_code"];
																																									} else {
																																										echo '';
																																									} ?>">
										</td>
									</tr>
								</table>

								<br />
								<button type="submit" class="btn btn-success" name="tranfer_sub_code" id="tranfer_sub_code">Save</button>
								<input type="hidden" name="ap" value="<?php echo $appt_no; ?>" />
								<input type="hidden" name="hos_no" value="<?php echo $hos_no; ?>" />


							</form>
							<script>
								document.getElementById('auth_code').onchange = function() {
									document.getElementById('a_code').disabled = !this.unchecked;
								};
							</script>

							<?php
						}



						if (isset($_POST["edit_category_id"])) {
							$edit_category_id = $_POST["edit_category_id"];
							$stmt = $db->query("SELECT * FROM stock_cat_table where sn='$edit_category_id'");

							if ($stmt->rowCount() > 0) {
								$rxw = $stmt->fetch(PDO::FETCH_ASSOC);
								$stock_cat_id = $rxw['stock_table'];

								if ($stock_cat_id == 'Pharmacy') {
									$stock = "p";
								} elseif ($stock_cat_id == 'Nursing Consumable') {
									$stock = "c";
								} elseif ($stock_cat_id == 'Investigation') {
									$stock = "l";
								} elseif ($stock_cat_id == 'Others') {
									$stock = "o";
								}

							?>
								<form method="POST" action="index.php?stock=<?php echo $stock; ?>">
									<div class="form_sep">
										<label for="reg_input_no" class="req">Category Name</label>
										<input type="text" id="Category" name="Category" class="form-control" maxlength="100" value="<?php echo $rxw['name']; ?>">
									</div>

									<div class="form_sep">
										<button class="btn btn-primary btn-xs" type="submit" name="add_cat">Update</button>
									</div>
									<br>
									<input type="hidden" name="MM_update" value="adding_cat" />
									<input type="hidden" name="sn" id="sn" value="<?php echo $rxw['sn']; ?>" />
									<input type="hidden" name="stock_table" id="stock_table" value="<?php echo $stock_cat_id; ?>" />
									<input type="hidden" name="old_category" id="old_category" value="<?php echo $rxw['name'] ?>" />


								</form>
							<?php } ?>

						<?php } ?>




						<?php
						if (isset($_POST["stock_cat_id"])) {
							$navigation = $_POST["stock_cat_id"];

							if ($navigation == 'pharmacy') {
								$stock = "p";
							} else {
								$stock = "o";
							}

							$stmt = $db->query("SELECT * FROM stock_cat_table WHERE navigation='$navigation'");

							if ($stmt->rowCount() > 0) { ?>


								<div id="test_fields">
									<table class="table table-striped">
										<thead>
											<tr>
												<th data-toggle="true">No</th>
												<th data-toggle="true">Name</th>
												<th data-toggle="true">Navigation</th>
											</tr>
										</thead>
										<tbody>

											<?php
											$n = 1;
											while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
											?>
												<tr>
													<td><?php echo $n; ?></td>
													<td><?php echo $roww['name']; ?></td>
													<td><?php echo $roww['navigation']; ?></td>
													<td>

														<input type="button" name="Edit" value="Edit" id="<?php echo $roww["sn"]; ?>" class="btn btn-warning btn-xs edit_category" data-target="#myModal5" />

													</td>
												</tr>
											<?php
												$n++;
											} ?>

										</tbody>
									</table>
								<?php } else { ?>
									<br><br><strong>No Category Found!</strong><br><br>
								<?php } ?>
								<br>
								<form method="POST" action="index.php?stock=<?php echo $stock; ?>">
									<div class="form_sep">
										<label for="reg_input_no" class="req">Group Name</label>
										<input type="text" id="Category" name="Category" class="form-control" maxlength="100">
									</div>

									<div class="form_sep">
										<button class="btn btn-primary btn-xs" type="submit" name="add_cat">Add</button>
									</div>
									<br>
									<input type="hidden" name="MM_update" value="adding_cat" />
									<input type="hidden" name="stock_table" id="stock_table" value="<?php echo $stock_cat_id; ?>" />

								</form>


							<?php } ?>


							<?php

							if (isset($_POST["edit_stock_id"])) {
								$edit_stock_id = $_POST["edit_stock_id"];
								$stmt = $db->query("SELECT * FROM stock_table WHERE sn='$edit_stock_id'");

								if ($stmt->rowCount() > 0) {
									$roww = $stmt->fetch(PDO::FETCH_ASSOC);
							?>

									<div class="pull-left">
										<input type="button" name="edit" value="Edit Stock" data-target="#myModal5" id="<?php echo $roww["sn"]; ?>" class="btn btn-warning btn-xs edit_stock" /> &nbsp;|&nbsp;
										<input type="button" name="edit" value="Edit HMO/Corporate Tariff/Prices" data-target="#myModal5" id="<?php echo $roww["sn"]; ?>" class="btn btn-primary btn-xs edit_hmo_price" /> &nbsp;|&nbsp;

										<a href="" class="btn btn-danger btn-xs" class="fa fa-times"></i>&nbsp;Close</a>
									</div>
									<br><br>

									<table class="table table-striped">
										<tr>
											<td>S/N: </td>
											<td><?php echo $roww['sn']; ?></td>
										</tr>
										<tr>
											<td>Stock Name: </td>
											<td><?php echo $roww['product_name']; ?></td>
										</tr>
										<tr>
											<td>Category: </td>
											<td><?php echo $roww['category']; ?></td>
										</tr>
										<tr>
											<td>Coverage: </td>
											<td><?php echo $roww['coverage']; ?></td>
										</tr>
										<tr>
											<td>Insurance/Health Care: </td>
											<td><?php if ($roww['insurance_type'] == 1) {
													echo 'Primary Care';
												} else {
													echo 'Secondary Care';
												}; ?></td>
										</tr>

										<?php if ($roww['stock_table'] == 'Pharmacy') { ?>
											<tr>
												<td>Generic Name: </td>
												<td><?php echo $roww['generic_name']; ?></td>
											</tr>
											<tr>
												<td>Presentation: </td>
												<td><?php echo $roww['presentation']; ?></td>
											</tr>

											<tr>
												<td>Percent Markup: </td>
												<td><?php echo $roww['price_markup']; ?></td>
											</tr>
										<?php } ?>

										<tr>
											<td>Buying Cost: </td>
											<td><?php echo $roww['buying_cost']; ?></td>
										</tr>
										<tr>
											<td>NHIS Price: </td>
											<td><?php echo $roww['nhis_price']; ?></td>
										</tr>
										<tr>
											<td>Hospital Price: </td>
											<td><?php echo $roww['hosp_price']; ?></td>
										</tr>
										<tr>
											<td>Cash Price: </td>
											<td><?php echo $roww['cash_price']; ?></td>
										</tr>

										<tr>
											<td>Package Type: </td>
											<td><?php echo $roww['package_type']; ?></td>
										</tr>
										<tr>
											<td>Total in a Package: </td>
											<td><?php echo $roww['stock_total_unit']; ?></td>
										</tr>
										<tr>
											<td>Current Quantity: </td>
											<td><?php echo $roww['qty']; ?></td>
										</tr>
										<tr>
											<td>Re/Order Level: </td>
											<td><?php echo $roww['reorder_level']; ?></td>
										</tr>
										<tr>
											<td>Expired Date: </td>
											<td><?php if ($roww['expire_date'] == '') {
													echo '';
												} elseif ($roww['expire_date'] == '0000-00-00') {
													echo '';
												} else {
													echo date("d,M Y", strtotime($roww['expire_date']));
												} ?></td>
										</tr>
										<tr>
											<td>Date Captured: </td>
											<td><?php if ($roww['date_captured'] == '') {
													echo '';
												} elseif ($roww['date_captured'] == '0000-00-00') {
													echo '';
												} else {
													echo date("d,M Y", strtotime($roww['date_captured']));
												} ?></td>
										</tr>
									</table>


							<?php }
							}

							?>
							<?php



							if (
								isset($_POST['edit_hmo_stock_id']) or isset($_POST['edit_bed_tariff_id']) or
								isset($_POST['edit_price_tariff_id']) or isset($_POST['edit_investigation_tariff_id'])
							) {

								// Default value
								$manage_accesss = 0;

								// Check user rights first
								if (isset($_SESSION['rights']) && $_SESSION['rights'] === 'PH') {
									$username = $_SESSION['username'];
									$stmtt = $db->prepare("SELECT manage_price FROM pharm_users WHERE username = :username LIMIT 1");
									$stmtt->execute([':username' => $username]);
									$roww = $stmtt->fetch(PDO::FETCH_ASSOC);
									$manage_accesss = $roww ? (int)$roww['manage_price'] : 0;
								} else {
									$manage_accesss    = isset($_SESSION['stock_mgr']);
								}

								if ($manage_accesss != 1) {
									echo '<h2>ACCESS DENIED</h2>';
									exit;
								}

								if (isset($_POST['edit_price_tariff_id'])) {
									$table_tariff_name = "hmo_medical_tariff";
									$stock_sn = $_POST['edit_price_tariff_id'];
									$sql = "SELECT distinct insurance_no,insurance_name FROM insurance_tbl INNER JOIN hmo_medical_tariff 
                                        ON insurance_tbl.insurance_no=hmo_medical_tariff.hmo";
								} elseif (isset($_POST['edit_investigation_tariff_id'])) {
									$table_tariff_name = "hmo_investigation_tariff";
									$stock_sn = $_POST['edit_investigation_tariff_id'];
									$sql = "SELECT distinct insurance_no,insurance_name FROM insurance_tbl INNER JOIN hmo_investigation_tariff 
                                        ON insurance_tbl.insurance_no=hmo_investigation_tariff.hmo";
								} elseif (isset($_POST['edit_hmo_stock_id'])) {
									$table_tariff_name = "hmo_stocks_tariff";
									$stock_sn = $_POST['edit_hmo_stock_id'];
									$sql = "SELECT distinct insurance_no,insurance_name FROM insurance_tbl INNER JOIN hmo_stocks_tariff 
                                        ON insurance_tbl.insurance_no=hmo_stocks_tariff.hmo";
								} else {
									$table_tariff_name = "hmo_bed_tariff";
									$stock_sn = $_POST['edit_bed_tariff_id'];
									$sql = "SELECT distinct insurance_no,insurance_name FROM insurance_tbl INNER JOIN hmo_bed_tariff 
                                        ON insurance_tbl.insurance_no=hmo_bed_tariff.hmo";
								}

								$stmt = $db->query($sql);
								if ($stmt->rowCount() > 0) {
									while ($rrwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$hmo_ = $rrwx['insurance_no'];
										$insurance_name_ = $rrwx['insurance_name'];
										if (isset($_POST['edit_hmo_stock_id'])) {


											$sqlx = "SELECT hmo FROM hmo_stocks_tariff where hmo='$hmo_' and stock_sn='$stock_sn'";
											$stmtxx = $db->query($sqlx);
											if ($stmtxx->rowCount() == 0) {

												$query = "
													SELECT price_markup, hosp_price
													FROM stock_table 
													WHERE sn = :stock_sn 
													AND price_markup > 0 
													LIMIT 1";
												$stmtChk = $db->prepare($query);
												$stmtChk->execute([':stock_sn' => $stock_sn]);

												if ($rw_chk = $stmtChk->fetch(PDO::FETCH_ASSOC)) {
													$price_markup = $rw_chk['price_markup'];
													$empty = $rw_chk['hosp_price'];
												} else {
													$price_markup = 0;
													$empty = 0;
												}

												$stmtte = $db->prepare("INSERT INTO hmo_stocks_tariff (hmo, stock_sn, price,price_markup) VALUES (:hmo, :stock_sn, :price, :price_markup)");
												$stmtte->bindParam(':hmo', $hmo_);
												$stmtte->bindParam(':stock_sn', $stock_sn);
												$stmtte->bindParam(':price', $empty);
												$stmtte->bindParam(':price_markup', $price_markup);
												$stmtte->execute();
											}
										} else {

											$sqlx = "SELECT hmo FROM $table_tariff_name where hmo='$hmo_' and stock_sn='$stock_sn'";
											$stmtxx = $db->query($sqlx);
											if ($stmtxx->rowCount() == 0) {
												$empty = '0';
												$stmtte = $db->prepare("INSERT INTO $table_tariff_name (hmo, stock_sn, price) VALUES (:hmo, :stock_sn, :price)");
												$stmtte->bindParam(':hmo', $hmo_);
												$stmtte->bindParam(':stock_sn', $stock_sn);
												$stmtte->bindParam(':price', $empty);
												$stmtte->execute();
											}
										}
									}


									if (isset($_POST['edit_hmo_stock_id'])) {
										$stock_sn = $_POST['edit_hmo_stock_id']; // assign variable safely
										$sql = "SELECT hmo_stocks_tariff.*, insurance_tbl.insurance_name,stock_table.product_name
												FROM hmo_stocks_tariff 
												INNER JOIN insurance_tbl 
													ON insurance_tbl.insurance_no = hmo_stocks_tariff.hmo
												INNER JOIN stock_table 
													ON hmo_stocks_tariff.stock_sn = stock_table.sn
												WHERE hmo_stocks_tariff.stock_sn = ?";
										$stmt = $db->prepare($sql);
										$stmt->execute([$stock_sn]);
										$hmo__prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
									}

									if (isset($_POST['edit_bed_tariff_id'])) {
										$stock_sn = $_POST['edit_bed_tariff_id'];

										$sql = "SELECT hmo_bed_tariff.*, insurance_tbl.insurance_name ,bed_mgt.room_name
												FROM hmo_bed_tariff 
												INNER JOIN insurance_tbl 
													ON insurance_tbl.insurance_no = hmo_bed_tariff.hmo
												INNER JOIN bed_mgt 
													ON hmo_bed_tariff.stock_sn = bed_mgt.sn
												WHERE hmo_bed_tariff.stock_sn = ?";
										$stmt = $db->prepare($sql);
										$stmt->execute([$stock_sn]);
										$hmo__prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
									}

									if (isset($_POST['edit_price_tariff_id'])) {
										$stock_sn = $_POST['edit_price_tariff_id'];

										$sql = "SELECT hmo_medical_tariff.*, insurance_tbl.insurance_name ,prices_table.item_service
											FROM hmo_medical_tariff 
											INNER JOIN insurance_tbl 
												ON insurance_tbl.insurance_no = hmo_medical_tariff.hmo
											INNER JOIN prices_table 
												ON hmo_medical_tariff.stock_sn = prices_table.sn
											WHERE hmo_medical_tariff.stock_sn = ?";
										$stmt = $db->prepare($sql);
										$stmt->execute([$stock_sn]);
										$hmo__prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
										/// item_service
									}

									if (isset($_POST['edit_investigation_tariff_id'])) {
										$stock_sn = $_POST['edit_investigation_tariff_id'];

										$sql = "SELECT hmo_investigation_tariff.*, insurance_tbl.insurance_name, lab_scan.test 
											FROM hmo_investigation_tariff 
											INNER JOIN insurance_tbl 
												ON insurance_tbl.insurance_no = hmo_investigation_tariff.hmo
											INNER JOIN lab_scan 
												ON hmo_investigation_tariff.stock_sn = lab_scan.sn
											WHERE hmo_investigation_tariff.stock_sn = ?";
										$stmt = $db->prepare($sql);
										$stmt->execute([$stock_sn]);
										$hmo__prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
									}


									if ($stmt->rowCount() > 0) {

							?>

										<div class="pull-left">
											<button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">
												<i class="fa fa-times"></i>&nbsp;Close
											</button>

										</div>
										<br><br>

										<form method="post" id="update_hmo_prices_form">

											<?php if ($table_tariff_name == "hmo_stocks_tariff") {


												$stmt2 = $db->prepare('SELECT * FROM stock_table WHERE sn = :sn');
												$stmt2->bindParam(':sn', $stock_sn);
												$stmt2->execute();
												$rows = $stmt2->fetch(PDO::FETCH_ASSOC); ?>
												<table>
													<tr>
														<td><label for="reg_input_no" class="" style="color:#F63;"><i>PURCHASE COST: </i> (=N= <?= $rows['buying_cost']; ?>)</label>&nbsp;</td>
														<td><input type="number" step="any" id="purchase_cost_2" name="purchase_cost_2" value="<?= $rows['buying_cost']; ?>" class="form-control" min="0"></td>
													</tr>
													<tr>
														<td> <label for="reg_input_no" class="" style="color: #F00;"><i>PERCENTAGE MARK-UP: </i>(<?= $rows['price_markup']; ?>)%</label>&nbsp;
															<br><small style="color:red;">Enter 0 if not applicable. Otherwise, enter prices manually below.</small>
														</td>
														<td><input type="number" step="any" id="hosp_no_mark_up" value="<?= $rows['price_markup']; ?>" name="hosp_no_mark_up" class="form-control" onkeyup="percentage_markup_cal_2();" min="0"></td>
													</tr>
													<tr>
														<td></td>
														<td>&nbsp;</td>
													</tr>
													<tr>
														<td>
															<label for="reg_input_no" class="req">1. HMO/General Hospital Price: (<?= $rows['hosp_price']; ?>)</label>
														</td>
														<td>
															<input type="number" step="any" id="hosp_price_2" name="hosp_price_2" value="<?= $rows['hosp_price']; ?>" class="form-control" required>
															<input type="hidden" value="<?php echo $rows['hosp_price']; ?>" name="hosp_price_2_former">
															<input type="hidden" value="<?php echo $rows['product_name']; ?>" name="product_name_former">
															<input type="hidden" value="<?php echo $stock_sn; ?>" name="stock_sn_main">

														</td>
													</tr>
													<tr>
														<td>
															<label for="reg_input_no" class="req">2. Cash Price (External Patients) (<?= $rows['cash_price']; ?>)</label>
														</td>
														<td>
															<input type="number" step="any" id="cash_price_2" name="cash_price_2" value="<?= $rows['cash_price']; ?>" class="form-control" required>
															<input type="hidden" value="<?php echo $rows['cash_price']; ?>" name="cash_price_2_former">
														</td>
													</tr>
													<tr>
														<td>
															<label for="reg_input_no" class="">3. NHIS Price (Enter Price if Covered by NHIS or Skip it) (<?= $rows['nhis_price']; ?>)</label>
														</td>
														<td>
															<input type="number" step="any" id="NHIS_price_2" name="NHIS_price_2" value="<?= $rows['nhis_price']; ?>" class="form-control">
															<input type="hidden" value="<?php echo $rows['nhis_price']; ?>" name="NHIS_price_2_former">
															<input type="hidden" id="stock_total_unit_2" name="stock_total_unit_2" value="<?= $rows['stock_total_unit']; ?>" class="form-control">
														</td>
													</tr>
												</table>
												<hr>


											<?php } ?>

											<h4 style="color:blue;">Edit the HMO specific prices: <br>
												<?php
												if (isset($_POST['edit_investigation_tariff_id'])) {
													echo $hmo__prices[0]['test']; // color it blue
												} elseif (isset($_POST['edit_price_tariff_id'])) {
													echo $hmo__prices[0]['item_service'];  // color echo
												} elseif (isset($_POST['edit_bed_tariff_id'])) {
													echo $hmo__prices[0]['room_name'];
												} else {
													echo $hmo__prices[0]['product_name'];
												}
												?></h4>
											<?php foreach ($hmo__prices as $hmo_price) { ?>
												<div class="form-sep">
													<label for=""><?php echo $hmo_price['insurance_name'] ?> </label>

													<table>
														<tr>
															<td>
																<b>Price (<?php echo $hmo_price['price'] ?>)</b>
																<input type="text" name="prices[]" class="form-control hmo_price"
																	value="<?php echo $hmo_price['price'] ?>" required>

																<input type="hidden" name="prices_2[]" value="<?php echo $hmo_price['price']; ?>">
															</td>
															<td>
																<?php if (isset($_POST['edit_hmo_stock_id'])) { ?>
																	<b>Price Markup (Optional) (<?php echo $hmo_price['price_markup'] ?>)</b>
																	<input type="text" name="price_markup[]" class="form-control hmo_markup"
																		value="<?php echo $hmo_price['price_markup'] ?>"
																		onkeyup="percentage_markup_cal_4_each(this);">
																<?php } ?>
															</td>
														</tr>
													</table>




												</div>
												<br>
												<input type="hidden" name="hmos[]" value="<?php echo $hmo_price['hmo'] ?>" required>
												<input type="hidden" name="stock_sn[]" value="<?php echo $hmo_price['stock_sn'] ?>" required>


											<?php } ?>
											<br><input type="submit" name="update_hmo_prices_btn" id="update_hmo_prices_btn" class="btn btn-primary btn-sm" value="Update prices">
											<input type="hidden" value="<?php echo $stmt->rowCount(); ?>" name="all_">
											<input type="hidden" value="<?php echo $table_tariff_name; ?>" name="table_tariff_name">

											<br>
											<h3><b style="color:#F63;">Note:</b> If you can't see other HMO/corporate names above, go to the Download/Upload page to download HMO-specific records and upload one or more records successfully. Then, return to here Edit All Medication/Prices. </h3>

										</form>
							<?php
										//print_r($hmo_stock_prices);
									}
								} else {

									echo '<h2>No Records Available. go to the Download/Upload page to download HMO-specific records and upload one or more records successfully. Then, return here to Edit All Medication/Prices. </h2>';
								}
							}
							?>


							<script>
								function percentage_markup_cal_2() {
									var purchase_cost = parseFloat(document.getElementById('purchase_cost_2').value);

									// Check if purchase_cost is a valid number
									if (isNaN(purchase_cost) || purchase_cost <= 0) {
										alert('Invalid Purchase Price');
										return; // Use return instead of exit
									}

									var units = parseFloat(document.getElementById('stock_total_unit_2').value);
									var percentage_markup = parseFloat(document.getElementById('hosp_no_mark_up').value);

									// Check if units or percentage_markup are valid numbers
									if (isNaN(units) || units <= 0) {
										alert('Invalid Units');
										return;
									}

									if (isNaN(percentage_markup) || percentage_markup < 0) {
										alert('Invalid Percentage Markup');
										return;
									}

									var amt = purchase_cost / units;
									var markup_amount = (amt * percentage_markup) / 100;
									var selling_price = amt + markup_amount;

									if (selling_price > 0) {
										document.getElementById('hosp_price_2').value = selling_price.toFixed(2);
										document.getElementById('cash_price_2').value = selling_price.toFixed(2);
									}

									// Optionally alert the selling price
									// alert("Selling Price: " + selling_price.toFixed(2));
								}
							</script>


							<script>
								function calculateSellingPrice(purchase_cost, units, percentage_markup) {
									if (isNaN(purchase_cost) || purchase_cost <= 0) return null;
									if (isNaN(units) || units <= 0) return null;
									if (isNaN(percentage_markup) || percentage_markup < 0) return null;

									var amt = purchase_cost / units;
									var markup_amount = (amt * percentage_markup) / 100;
									return amt + markup_amount;
								}

								function percentage_markup_cal_4_each(input) {
									var purchase_cost = parseFloat(document.getElementById('purchase_cost_2').value);
									var units = parseFloat(document.getElementById('stock_total_unit_2').value);
									var percentage_markup = parseFloat(input.value);

									var selling_price = calculateSellingPrice(purchase_cost, units, percentage_markup);
									if (selling_price !== null) {
										var row = input.closest('tr');
										var priceInput = row.querySelector('.hmo_price');
										if (priceInput) {
											priceInput.value = selling_price.toFixed(2);
										}
									}
								}

								// Recalculate all HMO rows when purchase_cost_2 or stock_total_unit_2 changes
								function recalc_all_hmo_prices() {
									var purchase_cost = parseFloat(document.getElementById('purchase_cost_2').value);
									var units = parseFloat(document.getElementById('stock_total_unit_2').value);

									if (isNaN(purchase_cost) || purchase_cost <= 0) return;
									if (isNaN(units) || units <= 0) return;

									// Loop through all markup inputs
									var markups = document.querySelectorAll('.hmo_markup');
									for (var i = 0; i < markups.length; i++) {
										var markup = parseFloat(markups[i].value);
										var row = markups[i].closest('tr');
										var priceInput = row.querySelector('.hmo_price');
										var selling_price = calculateSellingPrice(purchase_cost, units, markup);
										if (selling_price !== null && priceInput) {
											priceInput.value = selling_price.toFixed(2);
										}
									}
								}

								// Bind live recalculation when purchase cost or total units change
								document.getElementById('purchase_cost_2').addEventListener('keyup', recalc_all_hmo_prices);
								document.getElementById('stock_total_unit_2').addEventListener('keyup', recalc_all_hmo_prices);
							</script>

							<?php

							if (isset($_POST["dsp_oncredit_id"])) {
								include("../inc/dsp_rvs.php");
							} ?>

							<?php

							if (isset($_POST["company_id"])) {
								$company_id = $_POST["company_id"];
								$stmt = $db->query("SELECT * FROM stock_company");

							?>

								<br>
								<form method="POST" action="index.php?stock">
									<div class="form_sep">
										<label for="reg_input_no" class="req">Company Name</label>
										<input type="text" id="company" name="company" class="form-control" maxlength="100" required>
									</div>
									<div class="form_sep">
										<label for="reg_input_no" class="">Address</label>
										<input type="text" id="address" name="address" class="form-control" maxlength="100">
									</div>
									<div class="form_sep">
										<label for="reg_input_no" class="req">Phone Number</label>
										<input type="text" id="phone" name="phone" class="form-control" maxlength="100" required>
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="req">Payment Method</label>
										<select name="payment_method" id="payment_method" class="form-control" style="font-size:14px" required>
											<option value="">-- select--</option>
											<option value="Transfer">Transfer</option>
											<option value="Cheque">Cheque</option>

										</select>
									</div>


									<div class="form_sep">
										<label for="reg_input_no" class="">Account Name</label>
										<input type="text" id="acct_name" name="acct_name" class="form-control" maxlength="100">
									</div>
									<div class="form_sep">
										<label for="reg_input_no" class="">Account Number</label>
										<input type="text" id="acct_no" name="acct_no" class="form-control" maxlength="100">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="">Bank Name</label>
										<input type="text" id="bankname" name="bankname" class="form-control" maxlength="100">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="">Email</label>
										<input type="text" id="email" name="email" class="form-control" maxlength="100">
									</div>
									<div class="form_sep">
										<button class="btn btn-primary btn-xs" type="submit" name="add_company">Add Company</button>
									</div>
									<br>
									<input type="hidden" name="MM_update" value="adding_cat" />
									<input type="hidden" name="company_id" id="company_id" value="<?php echo $company_id; ?>" />

								</form>


								<?php
								if ($stmt->rowCount() > 0) { ?>


									<div id="test_fields">
										<table class="table table-striped">
											<thead>
												<tr>
													<th data-toggle="true">No</th>
													<th data-toggle="true">Name</th>
													<th data-toggle="true">Address</th>
													<th data-toggle="true">Bank Details</th>
												</tr>
											</thead>
											<tbody>

												<?php
												$n = 1;
												while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
												?>
													<tr>
														<td><?php echo $n; ?></td>
														<td><?php echo $roww['name']; ?></td>
														<td><?php echo $roww['address'] . '<br>' . $roww['email'] . '<br>' . $roww['phone']; ?></td>
														<td><?php echo $roww['payment_method'] . '<br>' . $roww['bank_name'] . '<br>' . $roww['account_no'] . '<br>' . $roww['account_name']; ?></td>
														<td>

															<input type="button" name="Edit" value="Edit" id="<?php echo $roww["sn"]; ?>" class="btn btn-warning btn-xs edit_company" data-target="#myModal5" />

														</td>
													</tr>
												<?php
													$n++;
												} ?>

											</tbody>
										</table>
									<?php } else { ?>
										<br><br><strong>No Company Found .</strong><br><br>
									<?php } ?>


								<?php }

							if (isset($_POST["edit_company_id"])) {
								$edit_company_id = $_POST["edit_company_id"];
								$stmt = $db->query("SELECT * FROM stock_company where sn='$edit_company_id'");

								$rxw = $stmt->fetch(PDO::FETCH_ASSOC);

								?>
									<form method="POST" action="index.php?stock">
										<div class="form_sep">
											<label for="reg_input_no" class="req">Company Name</label>
											<input type="text" id="" name="company" class="form-control" maxlength="100" value="<?php echo $rxw['name']; ?>" required>
										</div>
										<div class="form_sep">
											<label for="reg_input_no" class="">Address</label>
											<input type="text" id="" name="address" class="form-control" maxlength="100" value="<?php echo $rxw['address']; ?>">
										</div>
										<div class="form_sep">
											<label for="reg_input_no" class="req">Phone</label>
											<input type="text" id="" name="phone" class="form-control" maxlength="50" value="<?php echo $rxw['phone']; ?>" required>
										</div>
										<div class="form_sep">
											<label for="reg_input_no" class="">Email</label>
											<input type="text" id="" name="email" class="form-control" maxlength="50" value="<?php echo $rxw['email']; ?>">
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="req">Payment Method</label>
											<select name="payment_method" id="payment_method" class="form-control" style="font-size:14px" required>
												<option value="">-- select--</option>
												<option value="Transfer" <?php if ($rxw['payment_method'] == 'Transfer') { ?>selected <?php } ?>>Transfer</option>
												<option value="Cheque" <?php if ($rxw['payment_method'] == 'Cheque') { ?>selected <?php } ?>>Cheque</option>

											</select>
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="">Account Name</label>
											<input type="text" id="acct_name" name="acct_name" class="form-control" maxlength="100" value="<?php echo $rxw['account_name']; ?>">
										</div>
										<div class="form_sep">
											<label for="reg_input_no" class="">Account Number</label>
											<input type="text" id="acct_no" name="acct_no" class="form-control" maxlength="100" value="<?php echo $rxw['account_no']; ?>">
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="">Bank Name</label>
											<input type="text" id="bankname" name="bankname" class="form-control" maxlength="100" value="<?php echo $rxw['bank_name']; ?>">
										</div>


										<div class="form_sep">
											<button class="btn btn-primary btn-xs" type="submit" name="add_company">Update</button>
										</div>
										<br>
										<input type="hidden" name="sn" id="sn" value="<?php echo $rxw['sn']; ?>" />


									</form>

								<?php } ?>

								<?php

								if (isset($_POST["create_stock_combo_id"])) { 	?>

									<br>
									<form method="POST" action="index.php?stock">
										<div class="form_sep">
											<label for="reg_input_no" class="req">Combo Name</label>
											<input type="text" id="combo_name" name="combo_name" class="form-control" maxlength="100">
										</div>
										<!--		<div class="form_sep">
		<label for="reg_input_no" class="req">Category </label>

		<select name="stock_table_table" id="stock_table_table" class="form-control"  style="font-size:14px">
		<option value="">-- select--</option>
		<option value="Nursing Consumables">Nursing Consumables</option>
		<option value="Pharmacy">Pharmacy</option>
		<option value="Investigation Consumables">Investigation Consumables</option>
		<option value="Others">Other Stocks</option>
		</select>
		</div>-->

										<input type="hidden" name="stock_table_table" value="">

										<div class="form_sep">
											<label class="req"><strong>Select the Stock you wish to order</strong></label>
											<select class="form-control" name="stock_items" id="stock_items">
												<option value="">-- empty list --</option>
											</select>
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="req">Order Quantity</label>
											<input type="number" name="qty_combo" id="qty_combo" min="1" class="form-control">
										</div>



										<div class="form_sep" id="">
											<button class="btn btn-primary btn-sm" type="button" name="create_comboo" id="create_comboo" onclick="create_combo_stocks()">Add Item</button>
										</div>

										<hr>
										<div class="form_sep">
											<label class="form_sep" class="req">View Existing combo list </label>
											<select name="exist_combo" id="exist_combo" class="form-control" required style="font-size:15px;" onChange="display_combo_uploads('0')">

												<?php
												$stmt = $db->query("SELECT distinct combo_name FROM stock_combo order by combo_name"); ?>
												<option selected="selected" value="">Select ...</option>
												<?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $rxw["combo_name"]; ?>"><?php echo $rxw["combo_name"]; ?></option>
												<?php  } ?>
											</select>
										</div>

										<div id="Success_message"></div>



									</form>
								<?php	}





								if (isset($_POST["approve_procurement_id"])) {
									$approve_procurement_id = $_POST["approve_procurement_id"];
									$stmt = $db->prepare('SELECT p.*, s.buying_cost, c.name FROM stock_table_procurment as p INNER JOIN stock_company as c ON p.supplier_id = c.sn WHERE stock_sn = :stock_sn');
									$stmt->bindParam(':stock_sn', $approve_procurement_id);
									$stmt->execute();
									$row_d = $stmt->fetch(PDO::FETCH_ASSOC);
								?>
									<table class="table table-striped">
										<thead>
											<tr>
												<th data-toggle="true">Company Name</th>
												<th data-toggle="true">Product Name </th>
												<th data-toggle="true">Order By</th>
											</tr>
										</thead>
										<tbody>

											<tr>
												<th><?php $row_d['name']; ?></th>
												<th><?php $row_d['stock_name']; ?></th>
												<th><?php $row_d['order_by']; ?></th>
											</tr>
										</tbody>
									</table>


									<form method="POST" action="index.php?claims=<?php echo $emr . '&dates=' . $dates; ?>">



										<div class="form_sep">
											<label for="reg_input_no" class="req">Amount Payable</label>
											<input type="text" id="pay" name="pay" class="form-control" value="<?php ?>" required>
										</div>



										<div class="form_sep">
											<button class="btn btn-primary btn-sm" type="submit" name="edit_update_price" id="edit_update_price">Save Changes</button>
										</div>

									</form>

								<?php }


								if (isset($_POST["editprice_id"])) {

									$editprice_id = $_POST["editprice_id"];

									$note_inv_id = $_POST["editprice_id"];
									$part = explode("__", $note_inv_id);
									$sn = $part[0];
									$dates = $part[1];



									$stmt = $db->query("SELECT * FROM patient_ap_services WHERE sn='$sn'");
									$row_d = $stmt->fetch(PDO::FETCH_ASSOC);
									$emr = $row_d['hospital_no'];
									$item_services = $row_d['item_services'];
									$pay = $row_d['pay'];
									$claim_amt = $row_d['claim_amt'];
									$qty = $row_d['qty'];
									$pay_mode = $row_d['pay_mode'];
								?>

									<form method="POST" action="index.php?claims=<?php echo $emr . '&dates=' . $dates; ?>">

										<div class="form_sep">
											<label for="reg_input_no" class="req">Service Name/Item</label>
											<input type="text" id="item_services" name="item_services" value="<?php echo $item_services; ?>" class="form-control" required>
										</div>


										<div class="form_sep">
											<label for="reg_input_no" class="req">Amount Payable</label>
											<input type="number" step="any" id="pay" name="pay" class="form-control" value="<?php echo $pay; ?>" required>
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="req">Claim</label>
											<input type="number" step="any" id="claim" name="claim" class="form-control" value="<?php echo $claim_amt; ?>" required>
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="req">Quantity</label>
											<input type="number" id="qty" name="qty" class="form-control" maxlength="3" value="<?php echo $qty; ?>" required>
										</div>


										<div class="form_sep">
											<button class="btn btn-primary btn-sm" type="submit" name="edit_update_price" id="edit_update_price">Save Changes</button>
										</div>
										<input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
										<input type="hidden" name="sn" id="sn" value="<?php echo $sn; ?>" />
										<input type="hidden" name="dates" id="dates" value="<?php echo $dates; ?>" />
										<input type="hidden" name="pay_mode" id="pay_mode" value="<?php echo $pay_mode; ?>" />
									</form>

								<?php
								}
								?>

								<?php
								if (isset($_POST["edit_claim_date_id"])) {

									$edit_claim_date_id = $_POST["edit_claim_date_id"];
									$part = explode("__", $edit_claim_date_id);
									$sn = $part[0];
									$dates = $part[1];



									$stmt = $db->query("SELECT * FROM patient_ap_services WHERE sn='$sn'");
									$row_d = $stmt->fetch(PDO::FETCH_ASSOC);
									$emr = $row_d['hospital_no'];
									$date_entry =	date('d/m/Y', strtotime($row_d['date_entry']));

								?>

									<form method="POST" action="index.php?claims=<?php echo $emr . '&dates=' . $dates; ?>">
										<div class="form_sep">
											<label for="reg_input_no" class="req">Date</label>
											<input type="date" value="<?php echo $date_entry; ?>" name="date_entry" class="form-control" required>
										</div>


										<div class="form_sep">
											<button class="btn btn-primary btn-sm" type="submit" name="edit_update_date" id="edit_update_date">Save Changes</button>
										</div>
										<input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
										<input type="hidden" name="sn" id="sn" value="<?php echo $sn; ?>" />
										<input type="hidden" name="dates" id="dates" value="<?php echo $dates; ?>" />
									</form>

								<?php
								}








								if (isset($_POST["add_service_id_package_id"])) { ?>

									<div class="form_sep">
										<label class="form_sep" class="req">MEDICAL SERVICES / CONSULTATIONS/ NURSING SERVICES</label>

										<select name="medical_services" id="medical_services" class="input-sm chosen-select" style="width:350px;">
											<?php
											$stmt = $db->query("SELECT * FROM prices_table where item_service!='' and special_package=0 order by item_service"); ?>
											<option selected="selected" value="">Select ...</option>
											<?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $rxw["sn"] . '___' . $rxw["item_service"]; ?>"><?php echo $rxw["item_service"]; ?></option>
											<?php  } ?>
										</select>

										<label for="reg_input_no" class="req">How many times</label>
										<input type="number" min="1" name="how_many" id="how_many" class="form-control" required>

										<button class="btn btn-info btn-sm" type="submit" name="edit_update_date" onClick="Add_service('medical_services')">Add Medical Services</button>
									</div>


									<hr>



									<div class="form_sep">
										<label class="form_sep" class="req">RADIOLOGY AND LAB</label>
										<select name="" id="investigations" class="input-sm chosen-select" style="width:350px;">

											<?php
											$stmt = $db->query("SELECT * FROM lab_scan where test!='' order by test"); ?>
											<option selected="selected" value="">Select ...</option>
											<?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $rxw["sn"] . '___' . $rxw["test"]; ?>"><?php echo $rxw["test"]; ?></option>
											<?php  } ?>
										</select>

										<div class="form_sep">
											<label for="reg_input_no" class="req">How many times</label>
											<input type="number" min="1" name="how_many2" id="how_many2" class="form-control" required>
										</div>

										<button class="btn btn-primary btn-sm" type="submit" name="edit_update_date" onClick="Add_service('investigations')">Add Investigations</button>

									</div>



									<div class="form_sep">
										<label class="form_sep" class="req">PHARMACY</label>
										<select name="" id="pharmacy" class="input-sm chosen-select" style="width:350px;">

											<?php
											$stmt = $db->query("SELECT * FROM stock_table where product_name!='' and stock_table='Pharmacy' order by product_name"); ?>
											<option selected="selected" value="">Select ...</option>
											<?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $rxw["sn"] . '___' . $rxw["product_name"]; ?>"><?php echo $rxw["product_name"]; ?></option>
											<?php  } ?>
										</select>

										<div class="form_sep">
											<label for="reg_input_no" class="req">How many times</label>
											<input type="number" min="1" name="how_many3" id="how_many3" class="form-control" required>
										</div>

										<button class="btn btn-success btn-sm" type="submit" name="edit_update_date" onClick="Add_service('pharmacy')">Add Pharmacy Item</button>
									</div>


									<input type="hidden" id="add_service_id_package_id" value="<?= $_POST["add_service_id_package_id"]; ?>" class="form-control" required>


									<hr>
									<div id="target_table"></div>
								<?php

								}



								?>




								<script>
									/// 
									function view_service() {
										var add_service_id_package_id = document.getElementById("add_service_id_package_id").value;
										$.ajax({
											//alert(category_id);
											url: "../inc/special_package_settings.php",
											data: {
												target_table: add_service_id_package_id
											},
											type: 'POST',
											success: function(response) {
												///alert(response);
												var resp = $.trim(response);
												$("#target_table").html(resp);

											}
										});

									}




									function Add_service(target) {

										var medical_services = document.getElementById("medical_services").value;
										var investigations = document.getElementById("investigations").value;
										var pharmacy = document.getElementById("pharmacy").value;

										var how_many = document.getElementById("how_many").value;
										var how_many2 = document.getElementById("how_many2").value;
										var how_many3 = document.getElementById("how_many3").value;

										var add_service_id_package_id = document.getElementById("add_service_id_package_id").value;

										// Validate that at least one of the services is provided
										if (!medical_services && !investigations && !pharmacy) {
											alert("Please provide at least one of the following: Medical Services, Investigations, or Pharmacy.");
											return; // Stop the function if validation fails
										}

										$.ajax({
											url: "../inc/special_package_settings.php",
											data: {
												target: target,
												medical_services: medical_services,
												investigations: investigations,
												pharmacy: pharmacy,
												how_many: how_many,
												how_many2: how_many2,
												how_many3: how_many3,
												add_service_id_package_id: add_service_id_package_id
											},
											type: 'POST',
											success: function(response) {
												view_service();
											}
										});
									}




									$(document).ready(function() {

										$("#stock_table_table").change(function() {

											var category_id = $(this).val();
											if (category_id != "") {
												$.ajax({
													//alert(category_id);
													url: "../inc/get_stock_table.php",
													data: {
														category_id: category_id
													},
													type: 'POST',
													success: function(response) {
														////alert(response);
														var resp = $.trim(response);
														$("#stock_items").html(resp);

													}
												});
											} else {
												$("#stock_items").html("<option value=''>------- Select --------</option>");
											}


										});

									});

									function create_combo_stocks() {
										var combo_name = document.getElementById("combo_name").value;
										var stock_items = document.getElementById("stock_items").value;
										var qty_combo = document.getElementById("qty_combo").value;
										//$(document).ready(function(){$("#wateeeeeee").modal('show');});
										//$('#ibox2').children('.ibox-content').toggleClass('sk-loading');
										if (combo_name == "") {
											alert("Please provide your Combo Name!");
											exit;
										}

										if (stock_items == "") {
											alert("Please provide your stock_items!");
											exit;
										}

										if (qty_combo == "") {
											alert("Please set required Qty!");
											exit;
										}
										$("#overlay").fadeIn();
										$.ajax({
											url: "insert.php",
											method: "POST",
											data: {
												combo_name: combo_name,
												stock_items: stock_items,
												qty_combo: qty_combo
											},
											success: function(data) {

												///	alert(data);
												document.getElementById("qty_combo").value = '';
												document.getElementById("stock_items").value = '';
												//	setTimeout(function(){$("#overlay").fadeOut();},500);
												// document.getElementById('exist_combo').value=combo_name;
												display_combo_uploads(combo_name);
											}
										});
									}

									function display_combo_uploads(combo_name) {
										//$("#overlay").fadeIn(); 
										if (combo_name == '0') {
											var upload_combo = document.getElementById('exist_combo').value;
											document.getElementById('combo_name').value = upload_combo;
										} else if (combo_name != '0') {
											var upload_combo = combo_name;
										} else {
											var upload_combo = document.getElementById('combo_name').value;
										}


										$.ajax({
											url: "insert.php",
											method: "POST",
											data: {
												fetch_data: upload_combo
											},
											success: function(data) {
												//	alert(data);
												var json = JSON.parse(data);
												$("#Success_message").html(json["message"]);
											}
										});

									}


									function setTwoNumberDecimal(event) {
										this.value = parseFloat(this.value).toFixed(2);
									}

									$(".chosen-select").chosen({
										allow_single_deselect: true,
										enable_search_threshold: 10,
										no_results_text: 'Oops, nothing found!',
										width: "100%"
									});
									$('.chosen-drop').css({
										"width": "100%",
										"white-space": "nowrap"
									})


									$('#add_services_income_form_form').on("submit", function(event) {
										event.preventDefault();

										$.ajax({
											url: "insert.php",
											method: "POST",
											data: $('#add_services_income_form_form').serialize(),
											beforeSend: function() {
												$('#Save').val("Saving");
											},
											success: function(data) {
												$('#add_income_services_modal').modal('hide');

												//$('#test_fields').html(data);  
											},
											complete: function() {
												$('#add_services_form_form').val("Saving");
											},
											error: function(data) {

												alert("Oops...", "Something went wrong :(", "error");
												//swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
											}
										});
									});


									$(document).ready(function() {
										$("#invest3").hide();
										$("#med3").hide();
										$("#pharm3").hide();
										$("#other_serv3").hide();
										$("#nurs3").hide();

										$('#service_type').on('change', function() {

											if (this.value == 'Investigation') {
												$("#invest3").show();
												$("#med3").hide();
												$("#pharm3").hide();
												$("#other_serv3").hide();
												$("#nurs3").hide();
											}

											if (this.value == 'Medical Services') {
												$("#med3").show();
												$("#invest3").hide();
												$("#pharm3").hide();
												$("#other_serv3").hide();
												$("#nurs3").hide();
											}

											if (this.value == 'Pharmacy') {
												$("#med3").hide();
												$("#invest3").hide();
												$("#pharm3").show();
												$("#other_serv3").hide();
												$("#nurs3").hide();
												$("#nurs3").hide();
											}

											if (this.value == 'Nursing Services') {
												$("#med3").hide();
												$("#invest3").hide();
												$("#pharm3").hide();
												$("#other_serv3").hide();
												$("#nurs3").show();
											}

											if (this.value == 'Other Services') {
												$("#med3").hide();
												$("#invest3").hide();
												$("#pharm3").hide();
												$("#other_serv3").show();
												$("#nurs3").hide();
											}

										});
									});

									$(document).ready(function() {
										$('#div3').hide('fast');
										$('#div2').hide('fast');
										$('#div1').hide('fast');

										$('#admit').click(function() {
											$('#div3').hide('fast');
											$('#div2').hide('fast');
											$('#div1').show('fast');
										});
										$('#accomo').click(function() {
											$('#div1').hide('fast');
											$('#div2').show('fast');
											$('#div3').hide('fast');
										});

										$('#writeoff').click(function() {
											$('#div1').hide('fast');
											$('#div2').hide('fast');
											$('#div3').show('fast');
										});

									});

									$('#data_1 .input-group.date').datepicker({
										todayBtn: "linked",
										keyboardNavigation: false,
										forceParse: false,
										calendarWeeks: true,
										autoclose: true
									});




									$(document).on('click', '.write_off_confirm', function() {
										///  $('#see_occupant_modal').modal('hide'); 

										var write_off_id = $(this).attr("id");
										if (write_off_id != '') {
											$.ajax({
												url: "fetch_set.php",
												method: "POST",
												data: {
													write_off_id: write_off_id
												},
												success: function(data) {

													$('.modal-title').text('Write off Confirmation');

													$('#write_off_modal').modal('show');
													$('#write_off_body').html(data);
												}
											});
										}
									});


									$('#booking_form').on("submit", function(event) {
										$('#appointment_page2_modal').modal('hide');


										event.preventDefault();

										var consultation_services = $("#consultation_services").val();
										var doctor_name = $("#doctor_name").val();
										var Specialist = $("#Specialist").val();
										var ap_time = $("#ap_time").val();
										var ap_date = $("#ap_date").val();
										var interest = $("#interest").val();
										var insurance = $("#insurance").val();
										var hosp_no = $("#hosp_no").val();
										var patient_name = $("#patient_name").val();
										var dept_id = $("#dept_id").val();
										var how_contact = $("#how_contact").val();
										var ap_type = $("#ap_type").val();
										var visit_status = $("#visit_status").val();
										var add_minus = $("#add_minus").val();
										var payment_mode = $("#payment_mode").val();
										var insurance_no = $("#insurance_no").val();

										///alert(dept_id);


										$.ajax({
											url: "fetch_set.php",
											method: "POST",
											data: {
												consultation_services: consultation_services,
												doctor_name: doctor_name,
												Specialist: Specialist,
												ap_time: ap_time,
												ap_date: ap_date,
												interest: interest,
												hosp_no: hosp_no,
												patient_name: patient_name,
												dept_id: dept_id,
												insurance: insurance,
												how_contact: how_contact,
												ap_type: ap_type,
												visit_status: visit_status,
												add_minus: add_minus,
												payment_mode: payment_mode,
												insurance_no: insurance_no
											},
											success: function(data) {
												///alert(data);
												//  $('.modal-title').text('Field Names for ' + res[1]);
												$('#booking_confirm_modal').modal('show');
												$('#booking_confirm_body').html(data);
											}
										});
									});


									$(document).ready(function() {
										$('#id_later').hide('fast');

										$('#admit').click(function() {
											$('#div3').hide('fast');
											$('#div2').hide('fast');
										});

									});

									$(document).ready(function() {
										$("#id_later").hide();

										$('#how_contact').on('change', function() {

											if (this.value == 'future') {
												$("#id_later").show();
											}

											if (this.value != 'future') {
												$("#id_later").hide();
											}


										});
									});


									$(document).ready(function() {
										$("#specialist_div").hide();
										$("#doctorname_div").hide();

										$('#ap_type').on('change', function() {

											if (this.value == 'specialist') {
												$("#specialist_div").show();
												$("#doctorname_div").hide();
											}

											if (this.value == 'doctorname') {
												$("#doctorname_div").show();
												$("#specialist_div").hide();
											}

											if (this.value == '') {
												$("#doctorname_div").hide();
												$("#specialist_div").hide();
											}

										});
									});



									$("#consultation_services").change(function() {
										//alert('helloo');


										var dept_id = document.getElementById("dept_id").value;

										////alert(dept_id);

										var consultation_services_id = $(this).val();
										if (consultation_services_id != "") {


											///	alert(consultation_services_id);

											$.ajax({
												//alert(category_id);
												url: "get_services.php",
												data: {
													cat_id: consultation_services_id,
													dept_id: dept_id
												},
												type: 'POST',
												success: function(response) {
													//	alert(response);
													var resp = $.trim(response);
													$("#doctor_name").html(resp);

												}
											});

											$.ajax({

												url: "get_services.php",
												data: {
													cat_id2: consultation_services_id
												},
												type: 'POST',
												success: function(response) {
													//	alert(response);
													var resp = $.trim(response);
													$("#Specialist").html(resp);

												}
											});




										} else {
											$("#doctor_name").html("<option value=''>------- Select --------</option>");
											$("#Specialist").html("<option value=''>------- Select --------</option>");
										}


									});


									view_service();
								</script>



								<script>
									//document.addEventListener("DOMContentLoaded", function() {
									var el = document.getElementById("refer_appoint");
									if (el) el.style.display = "none";
								</script>

								<script>
									document.getElementById("show_refer").addEventListener("click", function() {
										document.getElementById("refer_appoint").style.display = "block";
									});
								</script>