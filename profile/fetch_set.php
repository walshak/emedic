<?php
session_start();
include("../Connections/Conn.php");

$response_main = array(
	'batch_no' => '0',
	'message' => ''
);

/// stock_name:stock_name,order_qty_:order_qty_, stock_sn_:stock_sn_,navigation:navigation},

if (isset($_POST["stock_name"])) {
	$dept_id = $_SESSION["dept_id"];

	$stock_name = $_POST["stock_name"];
	$new_qty = $_POST["order_qty_"];
	$stock_sn = $_POST["stock_sn_"];
	$pcs_pack_ = $_POST["pcs_pack_"];
	$stock_table_dept = $_POST["stock_table_dept"];
	$setdate = date('Y-m-d H:i:s');
	$combo = 0;
	$remarks = '';


	$stmtv = $db->prepare("SELECT navigation, stock_table,stock_total_unit FROM stock_table WHERE sn=:stock_sn");
	$stmtv->bindParam(':stock_sn', $stock_sn);
	$stmtv->execute();

	$pending = 'pending';
	// ALTER TABLE `stock_table_request` ADD `dept_incharge_stock` VARCHAR(10) NULL DEFAULT NULL AFTER `stock_table`;

	if ($stmtv->rowCount() > 0) {
		$rwxx = $stmtv->fetch(PDO::FETCH_ASSOC);
		$navigation = $rwxx['navigation'];
		$stock_table = $rwxx['stock_table'];
		$stock_total_unit = $rwxx['stock_total_unit'];

		if ($pcs_pack_ == 'pck') {
			$new_qty = $stock_total_unit  * $new_qty;
		}

		$stmt = $db->prepare("SELECT * FROM stock_table_request 
			WHERE stock_sn=:stock_sn and order_dept=:dept_id and status=:status 
			and order_by=:order_by and stock_name=:stock_name and DATE(order_date)=:order_date 
			and dept_incharge_stock=:dept_incharge_stock");
		$stmt->bindParam(':stock_sn', $stock_sn);
		$stmt->bindParam(':dept_id', $dept_id);
		$stmt->bindParam(':status', $pending);
		$stmt->bindParam(':order_by', $_SESSION['fullname']);
		$stmt->bindParam(':stock_name', $stock_name);
		$stmt->bindParam(':order_date', $order_date);
		$stmt->bindParam(':dept_incharge_stock', $stock_table_dept);
		$stmt->execute();

		if ($stmt->rowCount() == 0) {
			$stmt = $db->prepare("INSERT INTO stock_table_request(stock_sn, order_by, order_by_id, order_dept, order_date, order_qty,pck_pcs, unit_pck,stock_name, stock_table,dept_incharge_stock, navigation, remarks, combo) 
				VALUES(:stock_sn, :order_by, :order_by_id, :order_dept, :order_date, :order_qty,:pck_pcs, :unit_pck,:stock_name, :stock_table,:dept_incharge_stock, :navigation, :remarks, :combo)");
			$stmt->bindParam(':stock_sn', $stock_sn);
			$stmt->bindParam(':order_by', $_SESSION['fullname']);
			$stmt->bindParam(':order_by_id', $_SESSION['id']);
			$stmt->bindParam(':order_dept', $dept_id);
			$stmt->bindParam(':order_date', $setdate);
			$stmt->bindParam(':order_qty', $new_qty);
			$stmt->bindParam(':pck_pcs', $pcs_pack_);
			$stmt->bindParam(':unit_pck', $stock_total_unit);
			$stmt->bindParam(':stock_name', $stock_name);
			$stmt->bindParam(':dept_incharge_stock', $stock_table_dept);
			$stmt->bindParam(':stock_table', $stock_table);
			$stmt->bindParam(':navigation', $navigation);
			$stmt->bindParam(':remarks', $remarks);
			$stmt->bindParam(':combo', $combo);
			$stmt->execute();

			$response_main['batch_no'] = 0;
			$response_main['message'] = 'Added Successfully!';
			echo json_encode($response_main);
			exit;
		} else {
			$response_main['message'] = 'Item Order Already Exist!';
			$response_main['batch_no'] = 1;
			echo json_encode($response_main);
			exit;
		}
	}
}


if (isset($_POST["request_list_id"])) {
	$dept_id = $_SESSION['dept_id'];
	$fullname = $_SESSION['fullname'];
	$dispensory = $_SESSION['dispensory'];
	$start2 = $_POST['start2'];
	$end2 = $_POST['end2'];

	if ($start2 != '' and $end2 != '') {
		$order_date = " and date(order_date) between '$start2' and '$end2'";
	} else {
		$today = date('Y-m-d');
		$order_date = "and remove_status=0 and date(order_date) between '$today' and '$today'";
	}

	$rights = $_SESSION['rights'];
	$unit_head = $_SESSION['unit_head'];
	$departments = [];

	if ($unit_head == 1 && in_array($rights, ['LB', 'PH', 'NS'])) {
		$dept_map = [
			'LB' => "department_type='Radiology' OR department_type='Laboratory'",
			'PH' => "department_type='Pharmacy' OR department_type='Main Store'",
			'NS' => "department_type='Nursing' OR department_type='medical services'"
		];

		$where_ = $dept_map[$rights];
		$stmt_check = $db->prepare("SELECT sn FROM department WHERE $where_ ORDER BY department");
		$stmt_check->execute();

		if ($stmt_check->rowCount()) {
			$departments = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
		}
	} elseif ($unit_head == 1) {
		$stmt_check = $db->prepare("SELECT h.Department FROM admin_users a 
		INNER JOIN hremp h ON h.EmployeeCode=a.EmployeeCode WHERE rights = :rights");
		$stmt_check->bindParam(':rights', $rights);
		$stmt_check->execute();
		if ($stmt_check->rowCount() > 0) {
			while ($roww = $stmt_check->fetch(PDO::FETCH_ASSOC)) {
				$departments[] = $roww['Department'];
			}
		}
	} else {
		$departments = $dept_id;
	}

	$dept_id_list = is_array($departments) ? implode("','", $departments) : $departments;
	$stmt = $db->query("SELECT s.*, d.department as order_dept, s.order_dept as dept_id FROM stock_table_request as s 
			inner join department as d on d.sn=s.order_dept 
			where (s.order_dept IN ('$dept_id_list') OR order_by='$fullname') $order_date order by order_date desc");
	$set = 1;

	if ($stmt->rowCount() > 0 and $set == 1) { ?>
		<form action="invsti_rq.php" method="post">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover dataTables-example">
					<thead>
						<tr>
							<th data-toggle="true">#</th>
							<th data-toggle="true">Ordered By</th>
							<th data-toggle="true">ODR/Date</th>
							<th data-toggle="true">Name</th>
							<th data-toggle="true">Approve/By</th>
							<th data-toggle="true">A/Qty</th>
							<th data-toggle="true">A/Status</th>
							<th data-toggle="true">.</th>
						</tr>
					</thead>
					<tbody>

						<?php
						$n = 1;
						$button_visible = 0;
						while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

							//echo $roww['seen'];
							$send_status = null;
							if ($roww['seen'] == 2) {
								$disabled = "disabled";
								$send_status = '<strong style="color:blue;">[Seen]</strong>';
							} elseif ($roww['seen'] == 0) {
								$button_visible = 1;
								$disabled = "";
								$send_status = '<strong style="color:red;">[Not Send]</strong>';
							} else {
								$disabled = "disabled";
								$send_status = '<strong style="color:blue;">[Send]</strong>';
							}
						?>
							<tr>
								<td>
									<input type="checkbox" <?= $disabled ?> name="inv_all[]" value="<?php echo $roww['sn']; ?>" />
								</td>
								<td><?php echo $sn . '-' . $roww['order_by'] . '<br>' . '<strong>' . $roww['order_dept'] . '</strong>'; ?></td>
								<td><?php echo date("d-M,y h:i a", strtotime($roww['order_date'])); ?></td>
								<td>
									<?php
									echo $roww['stock_name'] . '<br><strong>Qty: </strong>' .
										($roww['pck_pcs'] == 'pck'
											? $roww['order_qty'] / $roww['unit_pck']
											: $roww['order_qty']
										) . ' (' . $roww['pck_pcs'] . ')';
									?>
								</td>
								<td><?php echo $roww['approve_by']; ?></td>
								<td><?php echo $roww['approve_qty']; ?></td>
								<td><?php echo $roww['status'] . '<br>' .  $send_status; ?></td>

								<td><?php if (($roww['status'] == 'pending' or $roww['status'] == 'reject') and $roww['order_by'] == $_SESSION['fullname']) { ?>
										<a href="invsti_rq.php?del=<?php echo $roww['sn']; ?>" onclick="return confirm('Are you sure you want to delete?')"> <strong>Del.</strong></a>
									<?php } elseif ($roww['status'] == 'Approved') { ?>
										<?php if ($roww['remove_status'] == '0') {

											if ($dispensory == 1) {

												$stock_sn = $roww['stock_sn'];

												$stmt_check = $db->prepare("SELECT 1 FROM stock_table_dispensory WHERE stock_table_id = :stock_table_id and dept_id = :dept_id");
												$stmt_check->bindParam(':stock_table_id', $stock_sn);
												$stmt_check->bindParam(':dept_id', $dept_id);
												$stmt_check->execute();
												if ($stmt_check->rowCount() == 0) {

													$stmt22 = $db->prepare("SELECT * FROM stock_table WHERE sn=:stock_sn");
													$stmt22->bindParam(':stock_sn', $stock_sn);
													$stmt22->execute();
													$rowwx = $stmt22->fetch(PDO::FETCH_ASSOC);

													$nhis_price = $rowwx['nhis_price']; // Replace with the actual value
													$hosp_price = $rowwx['hosp_price']; // Replace with the actual value
													$expire_date = $rowwx['expire_date']; // Replace with the actual value
													$price_markup = $rowwx['price_markup']; // Replace with the actual value
													// Insert into stock_table_dispensory
													$stmt_add = $db->prepare("INSERT INTO stock_table_dispensory (
										
											stock_table_id, 
											dept_id, 
											nhis_price, 
											hosp_price, 
											expire_date, 
											price_markup
										) VALUES (
											
											:stock_table_id, 
											:dept_id, 
											:nhis_price, 
											:hosp_price, 
											:expire_date, 
											:price_markup
										)");

													$stmt_add->bindParam(':stock_table_id', $stock_sn);
													$stmt_add->bindParam(':dept_id', $dept_id);
													$stmt_add->bindParam(':nhis_price', $nhis_price);
													$stmt_add->bindParam(':hosp_price', $hosp_price);
													$stmt_add->bindParam(':expire_date', $expire_date);
													$stmt_add->bindParam(':price_markup', $price_markup);
													$stmt_add->execute();
												}
											}






										?>
											<a href="invsti_rq.php?unlist=<?php echo $roww['sn']; ?>" class="btn btn-warning btn-xs" onclick="return confirm('Are you sure you want to hide this request?')">Hide</a>
									<?php }
									} ?>
								</td>

							</tr>
						<?php
							$n++;
						} ?>

					</tbody>
				</table>
			</div>


			<?php if ($button_visible == 1) { ?>


				<div class="form_sep" id="">
					<label class="req"><strong>Select DESTINATION</strong></label>
					<select name="destination" id="destination" class="form-control" required>
						<?php

						$stmt2 = $db->query("SELECT * FROM department where sn IN ('$dept_id_list') order by department");
						echo "echo <option value=''>-- Select --</option>";
						while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?php echo $row2["sn"]; ?>" <?php if ($dept_id == $row2["sn"]) { ?>selected <?php } ?>><?php echo $row2["department"]; ?></option>
						<?php }	?>
					</select>
				</div>


				<?php



				$disabled = "disabled";
				if ($_SESSION['b4_approve_requisition_setup'] == 1) {
					if ($_SESSION['unit_head'] == 1) {
						$disabled = "";
					}
				} elseif ($_SESSION['b4_approve_requisition_setup'] == 0) {
					$disabled = '';
				}
				?>

				<div class="form_sep" id="">
					<button class="btn btn-danger btn" type="submit" name="process_select_rq" onclick="return confirm('Are you sure you want to Process Selected Items?')" <?= $disabled; ?>><i class="fa fa-arrow"></i>&nbsp;Process Selected Items(s)</button>
				</div>
		</form>

	<?php } ?>

<?php }
}



if (isset($_POST["editprice_id"])) {

	$editprice_id = $_POST["editprice_id"];
	$stmt = $db->prepare("SELECT d.*, s.product_name,s.stock_table FROM stock_table_dispensory d 
	INNER JOIN stock_table s ON s.sn=d.stock_table_id WHERE id=:id");
	$stmt->bindParam(':id', $editprice_id);
	$stmt->execute();

	$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

?>



<form method="POST" action="invsti_rq.php">

	<h2><?= $row_d['product_name']; ?></h2>

	<div class="form_sep">
		<label for="reg_input_no" class="req">NHIS Price</label>
		<input type="number" step="any" id="" name="nhis_price" class="form-control" value="<?= $row_d['nhis_price']; ?>" required>
	</div>

	<div class="form_sep">
		<label for="reg_input_no" class="req">Hospital Price</label>
		<input type="number" step="any" id="" name="hosp_price" class="form-control" value="<?= $row_d['hosp_price']; ?>" required>
	</div>

	<div class="form_sep">
		<label for="reg_input_no" class="req">Cash price</label>
		<input type="number" step="any" id="" name="cash_price" class="form-control" value="<?= $row_d['cash_price']; ?>" required>
	</div>



	<div class="form_sep">
		<button class="btn btn-primary btn-sm" type="submit" name="edit_update_price" id="edit_update_price">Save Changes</button>
	</div>

	<input type="hidden" name="sn" id="sn" value="<?php echo $editprice_id; ?>" />

</form>

<?php
}
?>