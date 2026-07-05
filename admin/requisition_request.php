<h2>Requisition - Requests List</h2>
<small>What to do here ... </small>

<div class="row">
	<div class="col-lg-6">
		<h3>View Request from Department/Unit/Staff</h3>
		<h3>Approve or Reject Request</h3>
		<hr>

	</div>

	<div class="col-lg-6">
		<h3>Transfer Approved Requisitions (Inventory) between Departments</h3>
		<input type="button" name="cat" value="Move Inventory" data-target="#myModal5" id="Move_Inventory_button" class="btn btn-success stock_transfer" />
		<br>
		<br>

	</div>
</div>

<?php

if ($_SESSION['b4_approve_requisition_setup'] == 1) {
	$tag_search = "";
	$tag_search2 = "";
} else {
	$tag_search = "and s.stock_table='$stock_table'";
	$tag_search2 = "and stock_table='$stock_table'";
}

///echo $tag_search2 . $search_plus;
?>

<form action="index.php?stock=<?php echo $stock; ?>&rrq" method="POST">
	<div class="row">
		<!-- ORDER BY NAME -->
		<div class="col-lg-5">
			<div class="form_sep">
				<h3>Select <u><strong style="color: red;">COLLECTOR/ORDER</strong></u> by Name</h3>
				<select name="order_by_name" class="form-control">
					<option value="">Select ...</option>
					<?php
					$selected_name = isset($_POST['order_by_name']) ? $_POST['order_by_name'] : "";

					$sql = "SELECT DISTINCT order_by_id, order_by
                            FROM stock_table_request
                            WHERE status='pending' AND (seen=1 OR seen=2)
                            $tag_search2 $search_plus";

					foreach ($db->query($sql, PDO::FETCH_ASSOC) as $row) {
						$isSelected = ($row['order_by_id'] == $selected_name) ? "selected" : "";
						echo "<option value='{$row['order_by_id']}' $isSelected>" . htmlspecialchars($row['order_by']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>

		<!-- ORDER BY DEPARTMENT -->
		<div class="col-lg-4">
			<div class="form_sep">
				<h3>Select <u>REQUEST</u> by <strong style="color: red;">DEPARTMENT</strong></h3>
				<select name="order_by_dept" class="form-control">
					<option value="">Select ...</option>
					<?php
					$selected_dept = isset($_POST['order_by_dept']) ? $_POST['order_by_dept'] : "";

					$sql = "SELECT DISTINCT d.sn, d.department
                            FROM stock_table_request s
                            INNER JOIN department d ON d.sn = s.order_dept
                            WHERE s.status='pending' AND (s.seen=1 OR s.seen=2)
                            $tag_search $search_plus";

					foreach ($db->query($sql, PDO::FETCH_ASSOC) as $row) {
						$isSelected = ($row['sn'] == $selected_dept) ? "selected" : "";
						echo "<option value='{$row['sn']}' $isSelected>" . htmlspecialchars($row['department']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>

		<!-- BUTTONS -->
		<div class="col-lg-3">
			<div class="form_sep">
				<p>Click Button</p>
				<button class="btn btn-primary btn-sm" type="submit" name="show_requisition_filter">
					<strong>Show List(s)</strong>
				</button>
				&nbsp;:&nbsp;
				<a href="index.php?stock=<?php echo $stock; ?>&rrq" class="btn btn-default btn-sm">Refresh Display</a>
			</div>
		</div>
	</div>
</form>






<?php




if (isset($_POST['order_by_dept']) and $_POST['order_by_dept'] != '') {
	$order_by_dept = $_POST['order_by_dept'];
	$order_by_dept = " and order_dept='$order_by_dept'";
} else {
	$order_by_dept = '';
}

///echo $order_by_dept;

if (isset($_GET['change_id']) and $_GET['change_id'] != '') {
	$change_id_raw = $_GET['change_id'];
	$change_id = " and s.sn='$change_id_raw'";

	$updateSQL = "UPDATE stock_table_request 
              SET pck_pcs = 'pcs' 
              WHERE sn = :request_sn";

	$stmt = $db->prepare($updateSQL);
	$stmt->bindParam(':request_sn', $change_id_raw, PDO::PARAM_STR);

	if ($stmt->execute()) {
		// Check the number of affected rows
		if ($stmt->rowCount() > 0) {
			echo "<h3>Update successful: " . $stmt->rowCount() . " row(s) updated.</h3>";
		} else {
			echo "<h3>No rows updated. The requested record may not exist or the data may be the same.</h3>";
		}
	} else {
		echo "<h3>Update failed: " . implode(", ", $stmt->errorInfo()) . '</h3>';
	}
} elseif (isset($_POST['change_order_dept']) and $_POST['change_order_dept'] != '') {
	$order_by_dept = $_POST['change_order_dept'];
	$order_by_dept = " and order_dept='$order_by_dept'";
} elseif (isset($_POST['order_by_name']) and $_POST['order_by_name'] != '') {
	$order_by_name = $_POST['order_by_name'];
	$order_by_name = " and order_by_id='$order_by_name'";
} else {
	$order_by_name = '';
}

if ($_SESSION['navigate'] == 'admin' and ($_SESSION['stock_mgr'] == '1' or $_SESSION['stock_mgr_pharm'] == '1')) {
	$admin = '1';
} else {
	$admin = '0';
}

if (in_array($_SESSION['dept_group_name'], ['Store', 'Pharmacy', 'Main Store'])) {
	$dept_id = $_SESSION['dept_id'];
	$short_dept_charge = " and dept_incharge_stock='$dept_id'";
} elseif ($_SESSION['b4_rq_approval'] == 1) {
	$short_dept_charge = null;
}
///echo '===========' . $att_query . $order_by_name . $order_by_dept . $tag_search . $search_plus . $change_id . $short_dept_charge;

if (isset($_POST['show_requisition_filter']) && $order_by_name == '' && $order_by_dept == '' && $change_id == '') {
	echo "<HR><h3 style='color: red;'>INVALID SELECTIONS - COLLECTOR OR DEPARTMENT FIELDS!</h3>";
	exit;
} elseif (
	isset($_POST['show_requisition_filter']) ||
	isset($_POST['change_order_dept']) ||
	(isset($_GET['change_id']) && ($order_by_name !== '' || $order_by_dept !== '' || $change_id !== ''))
) {
	$stmt = $db->query("SELECT s.*, d.department as order_dept,s.order_dept as dept_id 
	FROM stock_table_request as s 
	inner join department as d on d.sn=s.order_dept 
	where s.status='pending' $change_id $att_query $order_by_name $order_by_dept $tag_search $search_plus $short_dept_charge");
	$set = 1;

	if ($stmt->rowCount() > 0) { ?>
		<hr>
		<?php if ($all_product != '') { ?>
			<strong style="color: red;">Invalid Quantity:</strong><strong><?php echo $all_product; ?> </strong>
		<?php } ?>

		<form id="myForm" action="index.php?stock=<?php echo $stock; ?>&rrq" method="POST">
			<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px; ">
				<thead>
					<tr>
						<th data-toggle="true">#</th>
						<th data-toggle="true">ORD/APR By/Dept</th>
						<th data-toggle="true">Order Item(s)</th>
						<th data-toggle="true">Ordered Date</th>
						<th data-toggle="true"><strong style="color: red; ">ORD Qty</strong></th>
						<th data-toggle="true"><strong style="color: blue; ">APR Qty</strong></th>

						<th width="10%"><strong style="color: red;">Edit Qty Below</strong></th>
						<th width="10%">Current Qty</th>
						<?php if ($_SESSION['rq_approval'] == '1' or $_SESSION['b4_rq_approval'] == '1') { ?>
							<th data-toggle="true">.</th>
						<?php } else { ?>
							<th data-toggle="true">.</th>
						<?php } ?>


					</tr>
				</thead>
				<tbody>

					<?php
					$n = 1;
					$count_pending_appr = 0;
					while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$stock_sn = $roww['stock_sn'];
						if ($_SESSION['b4_approve_requisition_setup'] == 1) {

							$app_tille = null;

							if ($_SESSION['b4_rq_approval'] == 1) {
								if ($roww['b4_approve_by'] == '' or $roww['b4_approve_by'] == null) {
									$enable_status = "";
									$count_pending_appr++;
									$app_tille = "<br><small style='color:red'>Approval Pending</small>";
								} else {
									if ($roww['b4_approve_by'] == '') {
										$enable_status = "disabled";
									} else {
										$enable_status = "";
									}
								}
							} else {
								if ($roww['b4_approve_by'] == '' or $roww['b4_approve_by'] == null) {
									$enable_status = "disabled";
									$count_pending_appr++;
									$app_tille = "<br><small style='color:red'>Approval Pending</small>";
								} else {
									$enable_status = "";
								}
							}
						} else {
							$enable_status = "";
						}
					?>
						<tr>
							<td><input type="checkbox" value="<?= $roww['sn']; ?>" name="inv_all[]" style="height: 18px; width: 18px;" onClick="select_edit('<?= $roww['sn']; ?>')" <?= $enable_status; ?> /></td>
							<td><?php

								if ($_SESSION['b4_approve_requisition_setup'] == 1) {
									$b4_approve_by = null;
									$b4_approve_date = null;
									if ($roww['b4_approve_by'] != '') {
										$mx_qty = $roww['b4_approve_qty'];
										$b4_approve_by = '<i>' . $roww['b4_approve_by'] . '</i>';
										$b4_approve_date = date("d-M-y h:i a", strtotime($roww['b4_approve_date']));
									} else {
										$mx_qty = $roww['order_qty'];
									}
								}

								echo $roww['order_by'] . '/<br>' . $b4_approve_by . ' <strong>[' . $roww['order_dept'] . ']</strong>';
								echo $app_tille;
								$dept_id = $roww['dept_id'];
								$order_by = $roww['order_by'] ?></td>
							<td><?php echo $roww['stock_name']; ?>
								<br><input type="button" name="change_item" value="Change Item" data-target="#myModal5" id="<?= $roww['sn']; ?>" class="btn btn-warning btn-xs change_item" <?= $enable_status; ?> />
							</td>
							<td><?php echo date("d-M-y h:i a", strtotime($roww['order_date'])) . '<br>' . $b4_approve_date; ?></td>
							<td>
								<?php
								if ($roww['pck_pcs'] == 'pck') {
									echo $roww['order_qty'] / $roww['unit_pck'];
									echo '(' . $roww['pck_pcs'] . ')';
								?>
									<br>
									<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&change_id=<?= $roww['sn']; ?>"
										class="btn btn-primary btn-xs"
										<?= $enable_status; ?>
										onclick="return confirm('Are you sure you want to change this item to Pcs?');">
										Change to Pcs
									</a>
								<?php
								} else {
									echo $roww['order_qty'] . '(pcs)';
								} ?>
							</td>
							<td><?php
								if ($roww['b4_approve_qty'] > 0) {
									echo ($roww['pck_pcs'] === 'pck')
										? $roww['b4_approve_qty'] / $roww['unit_pck']
										: $roww['b4_approve_qty'];
									echo '(' . $roww['pck_pcs'] . ')';
								}

								$order_qty = $roww['order_qty'];
								$approved_qty = ($roww['b4_approve_qty'] > 0 && $roww['b4_approve_qty'] != $roww['order_qty'])
									? $roww['b4_approve_qty']
									: $roww['order_qty'];

								if ($roww['pck_pcs'] === 'pck' && $approved_qty > 0) {
									$approved_qty /= $roww['unit_pck']; // divide directly
								} ?></td>

							<td width="10%">


								<?php
								if ($roww['combo'] == '0') {

									if ($_SESSION['b4_rq_approval'] == 1) {
										/// APPROVAL WANT TO SEE QTY BEFORE APPROVING

										$stmtvx = $db->query("SELECT qty FROM stock_table WHERE sn='$stock_sn'");
										if ($stmtvx->rowCount() > 0) {
											$rowxw = $stmtvx->fetch(PDO::FETCH_ASSOC);
											$main_qty = $rowxw['qty'];
										}
									} else {
										$dept_id_ = $_SESSION['dept_id'];
										$stmt_dispensary = $db->prepare("SELECT bal FROM stock_table_inven 
													WHERE stock_sn=:stock_sn AND cust_patient_id=:cust_patient_id ORDER BY sn DESC LIMIT 1");
										$stmt_dispensary->bindParam(':stock_sn', $stock_sn);
										$stmt_dispensary->bindParam(':cust_patient_id', $dept_id_);
										$stmt_dispensary->execute();
										if ($stmt_dispensary->rowCount() > 0) {
											$rw_disp = $stmt_dispensary->fetch(PDO::FETCH_ASSOC);
											$main_qty = $rw_disp['bal'];
										} else {
											/*
														$stmtvx = $db->query("SELECT qty FROM stock_table WHERE sn='$stock_sn'");
														if ($stmtvx->rowCount() > 0) {
															$rowxw = $stmtvx->fetch(PDO::FETCH_ASSOC);
															$main_qty = $rowxw['qty'];
														}
														*/
										}
									}

									if ($main_qty > 0) {
										if ($_SESSION['b4_approve_requisition_setup'] == 1 && $roww['b4_approve_by'] != '') {
											///$main_qty = $approved_qty;
										}

										$main_qty_pck_pcs = ($roww['pck_pcs'] === 'pck')
											? $main_qty / $roww['unit_pck']
											: $main_qty;
										$mx_qty_pcs = ($roww['pck_pcs'] === 'pck')
											? $mx_qty / $roww['unit_pck']
											: $mx_qty;
								?>
										<input type="number" id="qty_<?php echo $roww['sn']; ?>" name="qty_<?php echo $roww['sn']; ?>"
											class="form-control" onkeyup="sum();" min="1" value="<?php echo $approved_qty; ?>" max="<?php echo $main_qty_pck_pcs; ?>" disabled />
									<?php }
								} else { ?>
									<input type="number" id="qty_<?php echo $roww['sn']; ?>" name="qty_<?php echo $roww['sn']; ?>"
										class="form-control" onkeyup="sum();" min="1" value="<?php echo $approved_qty; ?>" max="<?php echo $mx_qty_pcs; ?>" disabled />

								<?php } ?>
								<input type="hidden" id="pck_pcs_<?php echo $roww['sn']; ?>" name="pck_pcs_<?php echo $roww['sn']; ?>" value="<?php echo $roww['pck_pcs']; ?>">
								<input type="hidden" id="unit_pck_<?php echo $roww['sn']; ?>" name="unit_pck_<?php echo $roww['sn']; ?>" value="<?php echo $roww['unit_pck']; ?>">

							</td>
							<td <?php if ($main_qty <= 0) { ?>style="background-color: coral" <?php } ?>><strong><?php if ($main_qty <= 0) {
																														echo '<strong>Zero Stock</strong>';
																													} else {

																														if ($roww['unit_pck'] > 1) {
																															$pack = floor($main_qty / $roww['unit_pck']); // whole packages
																															$pcs  = $main_qty % $roww['unit_pck'];        // remainder pieces

																															$parts = [];
																															if ($pack > 0) $parts[] = "$pack (pck)";
																															if ($pcs  > 0) $parts[] = "$pcs (pcs)";

																															$desc = implode(' & ', $parts);
																														} else {
																															$desc = $main_qty . ' (pcs)';
																														}

																														echo $desc;
																													} ?>


									<?php echo '<br>Dept Bal: ' . $roww['dept_last_bal'];  ?>
								</strong>


							</td>

							<?php if ($_SESSION['rq_approval'] == '1' or $_SESSION['b4_rq_approval'] == '1') { ?>

								<td>
									<?php if ($roww['status'] == 'pending') { ?>
										<?php /*?>	<input type="button" name="" value="Approve" data-target="#myModal5" id="<?php echo $roww["sn"] .'__'. $stock; ?>" 
								class="btn btn-success btn-xs approve_request" /> &nbsp; | &nbsp;<?php */ ?>
										<a href="index.php?rej=<?php echo $roww['sn']; ?>&stock=<?php echo $stock;  ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to REJECT ?')" <?= $enable_status; ?>>Reject</a>
									<?php } ?>
								</td>
							<?php } else { ?>


								<td><?php if (($roww['status'] == 'pending' or $roww['status'] == 'reject') and $roww['order_by'] == $_SESSION['fullname']) { ?>
										<a href="index.php?del=<?php echo $roww['sn']; ?>&stock=<?php echo $stock;  ?>" onclick="return confirm('Are you sure you want to delete?')"> Delete</a>
									<?php } ?>
								</td>

							<?php } ?>

						</tr>
					<?php
						$dept_incharge_stock = $roww['dept_incharge_stock'];
						$n++;
					} ?>

				</tbody>
			</table>

			<?php if (
				$_SESSION['b4_rq_approval'] == 1 &&
				$_SESSION['rq_approval'] == 1 && $_SESSION['b4_approve_requisition_setup'] == 1
			) {
				$show_apprv = 0;
				$show_issue = 1;
				$rq_value = 1;
			} elseif ($_SESSION['b4_approve_requisition_setup'] == 0 or $_SESSION['rq_approval'] == 1) {
				$show_apprv = 0;
				$show_issue = 1;
				$rq_value = 1;
			} elseif ($_SESSION['b4_approve_requisition_setup'] == 1 and $_SESSION['b4_rq_approval'] == 1) {
				$show_apprv = 1;
				$show_issue = 0;
				$rq_value = 1;
			} elseif ($_SESSION['b4_approve_requisition_setup'] == 1 and $_SESSION['rq_approval'] == 1) {
				$show_apprv = 0;
				$show_issue = 1;
				$rq_value = 0;
			}
			?>


			<?php if ($_SESSION['b4_rq_approval'] == '1' or $_SESSION['rq_approval'] == '1') { ?>

				<?php
				////echo '========' . $order_by_dept;
				if ($order_by_dept != '' && $show_issue == 1) { ?>


					<div class="form_sep" width="60%">
						<H3 for="reg_input_no" class="req">SELECT <U>WHO</U> IS COLLECTING THE ORDER:</H3>
						<select name="Collectedby" class="form-control" style="font-size: 18px; " required>
							<option selected="selected" value="">Select ...</option>
							<?php
							echo $dept_id;
							$stmt = $db->query("SELECT a.* FROM hremp as h 
			inner join admin_users as a 
			on a.EmployeeCode=h.EmployeeCode 
			where h.status='0' and h.Department='$dept_id' order by fullname");

							if ($stmt->rowCount() > 0) {
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $row["fullname"]; ?>"><?php echo $row["fullname"]; ?></option>
							<?php }
							}
							?>
						</select>
					</div>

				<?php } elseif ($order_by_name != '') { ?>
					<input type="hidden" name="Collectedby" value="<?php echo $order_by; ?>">
				<?php }	?>




				<div class="form_sep" width="60%">

					<?php

					///echo $roww['dept_incharge_stock'];
					///if (in_array($_SESSION['dept_group_name'], ['Store', 'Pharmacy', 'Main Store'])) { 
					?>

					<?php if ($show_issue == 1) { ?>
						<button class="btn btn-primary btn btn-lg" type="submit" name="approve_all_issue" id="approve_all_issue" data-clicked="false" onclick="return confirm('Are you sure you want to Issue and Quantity ?')"><i class="fa fa-arrow"></i>&nbsp; <?php if ($dept_incharge_stock != $dept_id) {
																																																																		echo 'Approved Selected Item(s)';
																																																																	} else {
																																																																		echo 'Issue Selected Item(s)';
																																																																	}  ?></button>
					<?php } ?>

					<?php if ($show_apprv == 1 && $count_pending_appr > 0) { ?>
						<button class="btn btn-primary btn btn-lg" type="submit" name="approve_all_request" id="approve_all_request" data-clicked="false" onclick="return confirm('Are you sure you want to Approve and Quantity ?')"><i class="fa fa-arrow"></i>&nbsp;Approve Selected Item(s)</button>
					<?php } ?>
				</div>

			<?php } ?>
			<input type="hidden" value="<?= $rq_value; ?>" name="b4_approve_requisition_setup" id="b4_approve_requisition_setup">
			<input type="hidden" value="<?php echo $stock; ?>" name="stock_code">
		</form>

	<?php } else { ?>
		<HR>
		<div class="alert alert-warning"><strong>⚠ No Records to Show for the Selections Above.</strong></div>

<?php }
}


if (isset($_POST['change_order'])) {

	$stock_name_old = $_POST['stock_name_old'];
	$request_sn = $_POST['request_sn'];
	$changed_item = $_POST['changed_item'];

	$stmt_chk = $db->prepare("SELECT product_name FROM stock_table WHERE sn = :sn");
	$stmt_chk->execute([':sn' => $changed_item]);
	$rowwc = $stmt_chk->fetch(PDO::FETCH_ASSOC);
	$Changed = 'Changed: ' . $stock_name_old . '/' . $rowwc['product_name'];
	$updateSQL = "UPDATE stock_table_request SET stock_name=:Changed, stock_sn=:stock_sn WHERE sn=:request_sn";
	$stmt = $db->prepare($updateSQL);
	$stmt->bindParam(':Changed', $Changed, PDO::PARAM_STR);
	$stmt->bindParam(':stock_sn', $changed_item, PDO::PARAM_STR);
	$stmt->bindParam(':request_sn', $request_sn, PDO::PARAM_STR);

	if ($stmt->execute()) {
		// Check the number of affected rows
		if ($stmt->rowCount() > 0) {
			echo '<br><div class="alert alert-success"><strong>✔ Update successful. Click On Show List(s) Button Above to See Changes!</strong></div>';
		} else {
			echo '<div class="alert alert-warning"><strong>⚠ No rows updated.</strong> The requested record may not exist or the data may be the same.</div>';
		}
	} else {
		echo '<div class="alert alert-danger"><strong>✖ Update failed:</strong> ' . htmlspecialchars(implode(", ", $stmt->errorInfo())) . '</div>';
	}
}

?>


<div class="modal inmodal fade" id="stock_transfer_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Stock Transfer Between Departments/Units</h4>
			</div>
			<div class="modal-body" id="stock_transfer_body">


				<div class="form_sep">
					<label for="reg_input_no" class="">Search for the status of all items from the selected department below: </label>
					<select name="department" class="form-control" id="dept_id_stock" onchange="load_stock('allitems')">
						<option selected="selected" value="">Select </option>
						<?php
						$stmt = $db->query("SELECT * FROM department ORDER BY department ASC");
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
						<?php } ?>
					</select>
				</div>


				<div class="form_sep">
					<label for="reg_input_no" class="">Search for the Status of Items Across All Departments:</label>
					<select name="department" class="form-control" id="stock__item" onchange="load_stock('departments')">
						<option selected="selected" value="">Select </option>

						<?php
						$stmt = $db->query("SELECT distinct s.sn, s.product_name FROM stock_table_inven as i 
				INNER JOIN stock_table as s ON s.sn = i.stock_sn order by stock_sn");
						if ($stmt->rowCount() > 0) {
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $row["sn"]; ?>"><?php echo $row["product_name"]; ?></option>
						<?php }
						}
						?>
					</select>
				</div>

				<hr>
				<div id="dept_stock_items"></div>

			</div>
		</div>
	</div>
</div>



<div class="modal inmodal fade" id="change_item_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""></h4>
			</div>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
				<div class="modal-body" id="change_item_body" style="height:400px;">
				</div>
			</form>

		</div>
	</div>
</div>