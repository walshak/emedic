 <?php include("../Connections/Conn.php"); ?>

 <?php
	session_start();

	if (isset($_POST["edit_patient_id_ext"])) {

		$transc_code = $_POST["edit_patient_id_ext"];

		$stmt_hr = $db->prepare('SELECT * FROM pharm_ext WHERE transc_code = :transc_code');
		$stmt_hr->bindParam(':transc_code', $transc_code);
		$stmt_hr->execute();
		$rwx = $stmt_hr->fetch(PDO::FETCH_ASSOC);
		if ($rwx) {
			$fullname = $rwx['cust_name'];
		}

	?>

 	<form method="POST" id="ext_form" action="index.php?sale">

 		<div class="form_sep">
 			<label for="reg_input_name" class="req">Patient Fullname (Surname, Others):</label>
 			<input type="text" id="name" name="name" class="form-control" value="<?= $fullname; ?>" required>
 		</div>

 		<div class="form_sep">
 			<label for="reg_select" class="req">Gender</label>
 			<select name="gender" id="gender" class="form-control" required>
 				<option selected="selected" value="">Select...</option>
 				<option value="Male" <?php if ($rwx['gender'] == 'Male') echo 'selected'; ?>>Male</option>
 				<option value="Female" <?php if ($rwx['gender'] == 'Female') echo 'selected'; ?>>Female</option>
 				<option value="Others" <?php if ($rwx['gender'] == 'Others') echo 'selected'; ?>>Others</option>
 			</select>
 		</div>


 		<div class="form_sep">
 			<table width="100%" cellpadding="5">
 				<tr>
 					<td>
 						<div class="form_sep" id="">
 							<label class="font-noraml">Date of Birth or Age</label>
 							<div class="input-group">
 								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
 								<input type="date" class="form-control" name="dob" id="dob" value="<?= $rwx['dob']; ?>">
 							</div>
 						</div>
 					</td>
 				</tr>
 			</table>
 		</div>

 		<div class="form_sep">
 			<label for="reg_input_name" class="">Phone:</label>
 			<input type="text" id="phone" name="phone" value="<?= $rwx['phone']; ?>" class="form-control">
 		</div>

 		<div class="form_sep">
 			<label for="reg_input_name" class="">Address:</label>
 			<input type="text" id="addr" name="addr" value="<?= $rwx['address']; ?>" class="form-control">
 		</div>


 		<div class="form_sep">
 			<label for="reg_input_name" class="req">Email Address:</label>
 			<input type="email" id="email_address" name="email_address" value="<?= $rwx['email_address']; ?>" class="form-control" required>
 		</div>


 		<div class="form_sep">
 			<label for="reg_select" class="">Patient is Referred from::</label>
 			<select name="referral" id="referral" class="form-control">
 				<option selected="selected" value="">Select...</option>
 				<?php
					$stmt = $db->query("SELECT name,sn FROM referrals");
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
 					<option value="<?php echo htmlspecialchars($row['sn']); ?>" <?php echo ($rwx['referral'] == $row['sn']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
 				<?php } ?>
 			</select>
 		</div>


 		<div class="form_sep">
 			<button class="btn btn-success btn-xs" type="submit" name="save_update" id="save">Update</button>
 		</div>

 		<input type="hidden" name="transc_code" value="<?= $transc_code; ?>">
 	</form>


 	<?php }




	if (isset($_POST["po_reorder_id"])) {
		$stock_table = $_POST["po_reorder_id"];



		if ($_SESSION['navigate'] == 'pharmacy' or $_SESSION['navigate'] == 'Pharmacy') {
			$category = "";
		} else {
			if ($_SESSION['navigate'] == 'investigations') {
				$category = " and (category='Investigations' or category='Investigation Consumables' or category='Laboratory' or category='Radiology')";
			} elseif ($_SESSION['navigate'] == 'nursing') {
				$category = " and (category='Nursing Consumables')";
			} else {
				$category = "";
			}
		}




		$stmt = $db->query("SELECT * FROM stock_table where status='active' and qty<=reorder_level and stock_table='$stock_table' $category");
		if ($stmt->rowCount() > 0) { ?>

 		<form action="index.php?stock=<?= $stock; ?>&pdr" method="post">
 			<div class="form_sep" id="">
 				<label for="reg_input_no" class="req">Supplier</label>
 				<select name="supplier_id" id="supplier_id" class="form-control" style="font-size:14px" required>
 					<option value="">-- select--</option>

 					<?php $stmtxx = $db->query("SELECT * FROM stock_company order by name");
						if ($stmt->rowCount() > 0) { ?>
 						<?php while ($row = $stmtxx->fetch(PDO::FETCH_ASSOC)) { ?>
 							<option <?php if ($row['sn'] == $supplier_id) { ?> selected <?php } ?> value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
 					<?php }
						} ?>
 				</select>
 			</div>

 			<hr>



 			<table class="table table-striped table-bordered table-hover dataTables-example">
 				<thead>
 					<tr>
 						<th width="5%">#</th>
 						<th>Stock/Item</th>
 						<th width="15%">Qty</th>
 						<th width="15%">Unit Cost</th>
 						<th width="20%">Total</th>

 					</tr>
 				</thead>
 				<tbody>

 					<?php
						$n = 1;
						$ttotal = 0;
						$g_ttotal = 0;
						$approve_amt = 0;
						while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$purchase_price = $roww['buying_cost'];


						?>
 						<tr>
 							<td>
 								<input type="checkbox" value="<?php echo $sn = $roww['sn']; ?>" name="inv[]" class="checkbox i-checks" />
 							</td>
 							<td><?php echo $roww['product_name']; ?>

 								<input type="hidden" class="input-sm form-control" id="stockName_<?php echo $n; ?>" name="stockName_<?php echo $sn; ?>" required value="<?php echo $roww['product_name']; ?>" />
 							</td>

 							<td>

 								<div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
 									<input type="text" class="input-sm form-control" id="order_qty_<?php echo $n; ?>" name="order_qty_<?php echo $sn; ?>" required value="1" onkeyup="UpdateCost()" min="1" style="width:90%;" />
 								</div>
 							</td>
 							<td>
 								<div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
 									<input type="text" class="input-sm form-control" id="buying_cost_<?php echo $n; ?>" name="buying_cost_<?php echo $sn; ?>" required value="<?php echo $purchase_price; ?>" onkeyup="UpdateCost()" style=" width:90%;" />
 								</div>

 							</td>
 							<td>
 								<input type="text" class="input-sm form-control" id="total_<?php echo $n; ?>" name="total_<?php echo $sn; ?>" value="<?php $ttotal = $purchase_price * 1;
																																						echo number_format($ttotal); ?>" readonly style=" width:90%;" />
 								<input type="hidden" id="total2_<?php echo $n; ?>" name="total2_<?php echo $sn; ?>" value="<?php echo $ttotal = $purchase_price * 1;  ?>" readonly style=" width:90%;" />

 							</td>


 						</tr>

 					<?php
							$g_ttotal = $g_ttotal + $ttotal;
							$n++;
						} ?>

 				</tbody>
 			</table>

 			<input type="hidden" name="p_list" id="p_list" value="<?php echo $n - 1; ?>">



 			<div class="form_sep" align="right">
 				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
 				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 				<button class="btn btn-primary btn-sm" type="submit" name="save_pre_order_list">Save</button>
 			</div>


 			<input type="hidden" name="status_link" id="status_link" value="edit">

 			<input type="hidden" name="stock" id="stock" value="<?php echo $stock_table; ?>">
 		</form>

 	<?php }
	}



	if (isset($_POST["webmedic_users_id"])) {

		$webmedic_users_id = $_POST["webmedic_users_id"];
		// Check if user exists in admin_users
		$stmt_users = $db->prepare("SELECT * FROM admin_users WHERE EmployeeCode = :EmployeeCode");
		$stmt_users->bindValue(':EmployeeCode', $webmedic_users_id, PDO::PARAM_STR);
		$stmt_users->execute();

		if ($stmt_users->rowCount() == 0) {
			$data_mode = 0;
		} else {
			$row_rstSelect = $stmt_users->fetch(PDO::FETCH_ASSOC);
			$data_mode = 1;
		}

		// Get name from hremp table
		$stmt_hr = $db->prepare("SELECT FirstName, LastName FROM hremp WHERE EmployeeCode = :EmployeeCode");
		$stmt_hr->bindValue(':EmployeeCode', $webmedic_users_id, PDO::PARAM_STR);
		$stmt_hr->execute();

		$rwx = $stmt_hr->fetch(PDO::FETCH_ASSOC);
		$fullname = $rwx['FirstName'] . ' ' . $rwx['LastName'] . ' ' . $rwx['MiddleName'];


		?>

 	<div class="alert alert-info" style="font-family:Arial, Helvetica, sans-serif"><strong><?php if ($data_mode == 1) {
																								echo 'Edit Privileges';
																							} else {
																								echo 'Add New Privileges';
																							} ?></strong></div>


 	<form action="index.php?rit" method="POST" id="subject" name="subject" enctype="multipart/form-data">

 		<div class="form_sep">
 			<label for="reg_input_no" class="req">Username</label>
 			<input type="text" id="Username" name="Username" class="form-control" required value="<?php echo $row_rstSelect['username'] ?>">
 		</div>

 		<div class="form_sep">
 			<label for="reg_input_no" class="req">Rights</label>
 			<select name="rights" id="rights" class="form-control" required>
 				<?php
					$rights_options = array(
						'MD' => 'Medical Director',
						'AO' => 'Admin Officer/Resource Manager',
						'AD' => 'Admin Officer and Consulting Doctor',
						'GM' => 'General Manager',
						'CA' => 'Cashier/Billing',
						'AC' => 'Accountant',
						'DR' => 'Doctor',
						'PY' => 'Physiotherapist',
						'NS' => 'Nurse/MidWifery',
						'PH' => 'Pharmacy',
						'RE' => 'Receptionist/FrontDesk',
						'LB' => 'Lab/Rad Officer',
						'ST' => 'Stores',
						'US' => 'User'
					);

					$selected_right = isset($row_rstSelect['rights']) ? $row_rstSelect['rights'] : '';

					echo '<option value="">Select...</option>';
					foreach ($rights_options as $value => $label) {
						$selected = ($selected_right == $value) ? 'selected="selected"' : '';
						echo '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
					}
					?>
 			</select>
 		</div>


 		<div class="form_sep">
 			<label for="reg_input_no" class="req">Unit Head</label>
 			<select name="UnitHead" id="UnitHead" class="form-control" required>

 				<option selected="selected" value="">Select...</option>
 				<option value="1" <?php if ($row_rstSelect['unit_head'] == 1) { ?> selected <?php } ?>>Yes</option>
 				<option value="0" <?php if ($row_rstSelect['unit_head'] == 0) { ?> selected <?php } ?>>No</option>
 			</select>
 		</div>

 		<div class="form_sep">

 			<label for="reg_input_no" class="req">User's Lock Status</label>
 			<select name="LockStatus" id="LockStatus" class="form-control" required>
 				<option value="" selected>Select...</option>
 				<option value="0" <?php if ($row_rstSelect['status'] == 0) { ?> selected <?php } ?>>Locked</option>
 				<option value="1" <?php if ($row_rstSelect['status'] == 1) { ?> selected <?php } ?>>Unlocked</option>
 			</select>
 		</div>

 		<div id="lab_image_div" class="form_sep">

 			<div class="form_sep">
 				<label for="reg_input_no" class="">Lab/Radiology Role</label>
 				<select name="speciality" id="speciality" class="form-control" data-required="true">
 					<option selected="selected" value="">Select ...</option>
 					<option value="Chemical Pathologist">Chemical Pathologist</option>
 					<option value="Medical Microbiologist">Medical Microbiologist</option>
 					<option value="Radiologist">Radiologist</option>
 					<option value="Haematogist">Haematogist</option>
 					<option value="X-ray Imaging Technician">X-ray Imaging Technician</option>
 					<option value="Radiographer">Radiographer</option>
 					<option value="Laboratory Technician">Laboratory Technician</option>
 					<option value="Medical Microbiologist">Medical Microbiologist</option>
 					<option value="Laboratory Scientist">Laboratory Scientist</option>
 					<option value="Data Operator">Data Entry Staff</option>
 					<option value="Receptionist">Receptionist</option>
 					<option value="Administrator">Administrator</option>
 				</select>
 			</div>

 			<div class="form_sep" id="investigation">
 				<label for="reg_input_no" class="">Investigation Unit</label>
 				<select name="section" id="section" class="form-control">
 					<option value="Laboratory">Laboratory</option>
 					<option value="Radiology">Radiology</option>
 				</select>
 			</div>
 		</div>

 		<?php
			if ($row_rstSelect['specialist'] != '') {
				$specialist = $row_rstSelect['specialist'];
			}
			?>
 		<div class="form_sep">
 			<label for="reg_input_no">Only Specialists/Consultants</label><br>
 			<label style="color:red;"><i>Note: Not for Medical Officers (MOs), Nurses, or other staff</i></label>

 			<select name="Specialist" id="Specialist" class="form-control">

 				<option selected="selected" value="">Select Specialty...</option>
 				<?php
					$stmt = $db->query("SELECT * FROM specialists ORDER BY name");
					while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
 					<option value="<?php echo  $row2['id']; ?>" <?php if ($specialist == $row2['id']) { ?>selected<?php } ?>><?php echo $row2['name']; ?></option>
 				<?php } ?>

 			</select>
 		</div>


 		<div class="form_sep">
 			<div class="pull-left">
 				<button class="btn btn-success" type="submit" name="save_users" id="save_users">Save</button>
 			</div>

 			<div class="pull-right">
 				<a href="index.php?rit" class="btn btn-warning">Close</a>

 				<?php if ($data_mode == 1) { ?>
 					&nbsp;&nbsp; | &nbsp;&nbsp;


 					<a href="index.php?rit&ECode=<?php echo $row_rstSelect['EmployeeCode'] . '&reset'; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to Reset Password?');">[ Reset Password ]</a>
 				<?php } ?>

 			</div>
 			<input type="hidden" name="EmployeeCode" value="<?php echo $webmedic_users_id; ?>" />
 			<input type="hidden" name="data_mode" value="<?php echo $data_mode; ?>" />
 			<input type="hidden" name="fullname" value="<?php echo $fullname; ?>" />

 		</div>

 	</form>





 	<?php }



	if (isset($_POST["edit_order_id"])) {

		$edit_order_id = $_POST["edit_order_id"];
		$parts = explode("/", $edit_order_id);
		$batch_no = $parts[0];
		$order_by = $parts[1];
		$stock_table = $parts[2];
		$stock = $parts[3];
		$status_link = $parts[4];
		$supplier_name_desc = $parts[5];
		$approval_stages = $parts[6];
		/// check the payment 

		$rmk = $stock_table . ' : ' . $supplier_name_desc;


		$stmt = $db->query("SELECT p.*,s.buying_cost,c.name,s.stock_total_unit FROM stock_table_procurment as p 
inner join stock_table as s on s.sn=p.stock_sn 
inner join stock_company as c on p.supplier_id=c.sn 
where p.stock_table='$stock_table' and p.order_by='$order_by' and p.batch_no='$batch_no'");
		if ($stmt->rowCount() > 0) { ?>

 		<form action="index.php?stock=<?= $stock; ?>&pdr" method="post" onsubmit="return validateForm()">

 			<?php if ($approval_stages == 0 and $_SESSION['Procurement_Officer_ack'] == 0) { ?>
 				<h3 style="color: red;;">YOU CAN EDIT QUANTITY & COST BEFORE SUBMISSION!</h3><br>
 			<?php } ?>

 			<table class="table table-striped table-bordered table-hover dataTables-example">
 				<thead>
 					<tr>
 						<th width="5%">#</th>
 						<th width="10%">Date</th>
 						<th>Stock/Item</th>
 						<th width="15%">Qty</th>
 						<th width="15%">Unit Cost</th>
 						<th width="20%">Total</th>
 						<th width="10%">.</th>
 						<?php if ($approval_stages >= 0) { ?>
 							<th width="10%"><b style="color:red;">Initial Qty</b></th>
 						<?php } ?>

 					</tr>
 				</thead>
 				<tbody>

 					<?php
						$n = 1;
						$ttotal = 0;
						$g_ttotal = 0;
						$approve_amt = 0;
						while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$purchase_price = $roww['purchase_price'];
							$supplier_id = $roww['supplier_id'];
							$pay_status = $roww['pay_status'];
							$Procurement_Officer_msg = $roww['Procurement_Officer_msg'];
							$order_by = $roww['order_by'];
							if ($purchase_price <= 0) {
								$purchase_price = $roww['buying_cost'];
							}


						?>
 						<tr>
 							<td>
 								<input type="checkbox" value="<?php echo $sn = $roww['sn']; ?>" name="inv[]"
 									<?php



										if (($status_link == 'approve' or $status_link == 'payment')) {

											if ($roww['status'] == 'yes' and $roww['pay_status'] == 0) {
												$approve_amt = $approve_amt + $roww['total_cost'];
											} elseif ($roww['status'] == 'yes' and $roww['pay_status'] == 1) {
												$posted_amt = $posted_amt + $roww['total_cost'];
											}

										?>
 									checked
 									<?php } elseif ($status_link == 'reverse' and $roww['status'] == 'reverse' and $roww['pay_status'] == 1) { ?>
 									checked
 									<?php } elseif ($status_link == 'approve' and $roww['status'] == 'no' and ($roww['pay_status'] == 1 or $roww['pay_status'] == 0)) { ?>
 									checked
 									<?php } elseif ($roww['status'] == 'no' and $roww['pay_status'] == 0) { ?>
 									checked<?php } else { ?> disabled<?php } ?> class="checkbox i-checks" />
 							</td>
 							<td><?php echo date("d-M-y", strtotime($roww['order_date']));; ?></td>
 							<td><?php echo $roww['stock_name']; ?>
 								<?php if ($approval_stages == 0 and $_SESSION['Procurement_Officer_ack'] == 0) { ?>
 									<?php if ($roww['status'] == 'no' and $roww['pay_status'] == 0) { ?> <br>
 										<a href="index.php?proc=<?php echo $roww['sn']; ?>&stock=<?php echo $batch_no . '/' . $supplier_id . '/' . $stock;  ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to Delete ?')">Delete</a>

 								<?php }
									} ?>
 							</td>

 							<td>

 								<div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
 									<input type="text" class="input-sm form-control" id="order_qty_<?php echo $n; ?>" name="order_qty_<?php echo $sn; ?>" required value="<?php echo $roww['order_qty']; ?>" onkeyup="UpdateCost()" min="1" style="width:90%;" />


 								</div>
 								<input type="hidden" class="input-sm form-control" id="old_qty_<?php echo $n; ?>" name="old_qty_<?php echo $sn; ?>" required value="<?php echo $roww['order_qty']; ?>" />

 							</td>
 							<td>
 								<div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
 									<input type="text" class="input-sm form-control" id="buying_cost_<?php echo $n; ?>" name="buying_cost_<?php echo $sn; ?>" required value="<?php echo $purchase_price; ?>" onkeyup="UpdateCost()" style=" width:90%;" />
 								</div>

 							</td>
 							<td>
 								<input type="text" class="input-sm form-control" id="total_<?php echo $n; ?>" name="total_<?php echo $sn; ?>" value="<?php $ttotal = $roww['order_qty'] * $purchase_price;
																																						echo number_format($ttotal); ?>" readonly style=" width:90%;" />
 								<input type="hidden" id="total2_<?php echo $n; ?>" name="total2_<?php echo $sn; ?>" value="<?php echo $ttotal = $roww['order_qty'] * $purchase_price;  ?>" readonly style=" width:90%;" />

 							</td>
 							<td>
 								<?php if ($roww['status'] == 'no') {
										echo '<strong>PDG</strong>';
									} else {
										echo '<strong>APV</strong>';
									} ?>
 							</td>
 							<?php if ($approval_stages >= 0) { ?>
 								<td><b><?php echo $roww['old_qty']; ?></b></td>
 							<?php } ?>

 						</tr>

 					<?php
							$g_ttotal = $g_ttotal + $ttotal;
							$n++;
						} ?>

 				</tbody>
 			</table>

 			<?php

				if (preg_match('/[()]/', $batch_no)) {
					$invoice_number = $batch_no;
				} else {
					$invoice_number = null;
				}

				?>




 			<div align="center">
 				<h2><?php echo $supplier_name_desc; ?></h2>
 			</div>

 			<input type="hidden" name="p_list" id="p_list" value="<?php echo $n - 1; ?>">

 			<?php if ($status_link == 'edit') { ?>

 				<?php if ($approval_stages == 0) { ?>
 					<b>ENTER VENDOR INVOICE NO. (Optional):</b> <input type="text" name="invoice_number" value="<?php echo isset($invoice_number) ? $invoice_number : ''; ?>" />
 					<br><br>
 				<?php } else {
					?>
 					<b>VENDOR INVOICE NO.: </b> <?php echo isset($invoice_number) ? $invoice_number : 'N/A';  ?>
 					<br><br>
 				<?php } ?>

 				<input type="hidden" name="batch_no_" id="batch_no_" value="<?php echo $batch_no; ?>">


 				<?php if ($_SESSION['Procurement_Officer_ack'] == 0 or $_SESSION['Procurement_Officer_ack'] == 1) { ?>
 					<b style="color: red;"><?= $Procurement_Officer_msg; ?></b> <br>
 				<?php } ?>

 				<?php


					if ($approval_stages == 0 and $_SESSION['Procurement_Officer_ack'] == 1 and $order_by == $_SESSION['fullname']) { ?>

 					<div class="pull-left">
 						<b>Submit to Purchasing/Procurement Officer for Acknowledgement!</b><br>
 						<button class="btn btn-primary" type="submit" name="save_purchase_order" onclick="return confirm('Are you sure you want to update and submit?')">Update and Submit</button>
 					</div>
 					<input type="hidden" name="approval_stages" id="approval_stages" value="1">
 				<?php } else { ?>

 					<?php if ($approval_stages == 0 and $_SESSION['Procurement_Officer_ack'] == 0) { ?>



 						<div class="pull-left">
 							<b>Submit to Purchasing/Procurement Officer for Acknowledgement!</b><br>
 							<button class="btn btn-primary" type="submit" name="save_purchase_order" onclick="return confirm('Are you sure you want to update and submit?')">Update and Submit</button>
 						</div>
 						<input type="hidden" name="approval_stages" id="approval_stages" value="1">
 					<?php } elseif ($approval_stages == 0 and $_SESSION['Procurement_Officer_ack'] == 1) { ?>
 						<div align='center' style="color:red; ">Request Has Not Been Submit to Procurement Officer for Acknowledgement!</div>
 					<?php }

						if ($approval_stages == 1 and $_SESSION['Procurement_Officer_ack'] == 1) {

							$delete = $db->prepare("UPDATE stock_table_procurment SET view_status = 'ack' WHERE batch_no = ? ");
							$deleted = $delete->execute(array($batch_no));

						?>
 						<div class="pull-left">
 							<b>Submit for Final Approval</b><br>
 							<button class="btn btn-primary" type="submit" name="save_purchase_order" onclick="return confirm('Are you sure you want to update and submit?')">Update and Submit</button>
 							<hr>

 							<div class="form_sep">
 								<label>Enter Reason for Rejection</label>
 								<input type="text" class="input-sm form-control" style="height: 35px; width: 500px; " name="reject_reason" id="reject_reason" value="" />
 								<button class="btn btn-warning" type="submit" name="reject_purchase_order" onclick="return confirm('Are you sure you want to Reject?')">Reject</button>
 								<input type="hidden" value="ack" name="who_is_rejecting">

 							</div>

 						</div>
 						<input type="hidden" name="approval_stages" id="approval_stages" value="2">
 					<?php } elseif ($approval_stages == 1 and $_SESSION['Procurement_Officer_ack'] == 0) { ?>
 						<div align='center' style="color:red; ">Request Has Been Submit to Procurement Officer for Acknowledgement!</div>
 					<?php } ?>
 				<?php } ?>

 				<input type="hidden" value="<?= $Procurement_Officer_msg; ?>" name="who_is_rejecting_msg">


 				<div class="pull-right">
 					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
 				</div>
 				<input type="hidden" name="status_link" id="status_link" value="edit">

 			<?php } ?>


 			<?php if ($status_link == 'reverse') { ?>
 				<h4>Approve Supplies and Deliver Stock(s) to Store Room</h4>
 				<h2>Amount Approving: <?= number_format($approve_amt) ?> </h2>


 				<div class="pull-left">
 					<button class="btn btn-danger" type="submit" name="save_purchase_order" onclick="return confirm('Are you sure you want to do this ?')">Reverse Authorization</button>
 				</div>

 				<div class="pull-right">
 					<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
 				</div>

 				<input type="hidden" name="status_link" id="status_link" value="reverse">

 			<?php } ?>

 			<?php if ($status_link == 'approve') { ?>
 				<h4>Approve Supplies and Deliver Stock(s) to Store Room</h4>
 				<h2>Amount Approving: <?= number_format($approve_amt) ?> </h2>

 				<div class="pull-left">
 					<button class="btn btn-primary" type="submit" <?php if ($approve_amt <= 0) { ?><?php } ?> name="save_purchase_order" onclick="return confirm('Are you sure you want to do this ?')">Approve</button>
 				</div>

 				<div class="pull-right">
 					<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
 				</div>

 				<input type="hidden" name="status_link" id="status_link" value="approve">

 			<?php } ?>

 			<input type="hidden" name="stock" id="stock" value="<?php echo $stock; ?>">

 			<?php if ($status_link == 'payment') { ?>


 				<h2 id="grand_total">Purchase Order <u>Approved</u>: <?= number_format($approve_amt); ?></h2>
 				<div class="form_sep">
 					<input type="hidden" step="any" id="amt_pay" name="amt_pay" class="form-control" value="<?php echo $approve_amt; ?>" required>
 				</div>

 				<div class="form_sep">

 					<table width="100%">
 						<tr>

 							<td width="50%">
 								<div class="form_sep">
 									<label for="account_debit" class="">DEBIT</label>
 									<!-- <select name="account_debit" id="account_debit" class="form-control">

 										<option selected="selected" value="">Select...</option>
 										<?php
											$stmt = $db->query("SELECT a.* FROM chart_accounts as a
		INNER JOIN chart_groups as g ON g.id=a.account_group
		INNER JOIN chart_class as c ON c.cid=g.class_id
		WHERE a.account_group IN (2,3) 
		ORDER BY class_id,account_group");
											while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
 											<option value="<?php echo  $row2['account_code']; ?>"><?php echo $row2['account_name']; ?></option>
 										<?php } ?>

 									</select> -->

 									<select class="chosen-select form-control" id="account_debit" name="account_debit">
 										<option value="">-- select Account--</option>
 										<?php
											$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');

											$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

											?>
 										<?php foreach ($classes as $class) { ?>
 											<optgroup label="<?php echo $class['class_name']; ?>">
 												<?php
													$cid = $class['cid'];
													$groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ? AND inactive = ?');
													$groups->execute([$cid, 0]);
													$groups = $groups->fetchAll(PDO::FETCH_ASSOC);
													?>
 												<?php foreach ($groups as $group) { ?>
 											<optgroup label="<?php echo $group['name']; ?>">
 												<?php
														$group_id = $group['id'];

														$accounts = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ? AND inactive = ?');
														$accounts->execute([$group_id, 0]);
														$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
													?>
 												<?php foreach ($accounts as $account) { ?>
 													<option value="<?php echo $account["account_code"] ?>">[<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?></option>
 												<?php } ?>
 											</optgroup>
 										<?php } ?>
 										</optgroup>
 									<?php } ?>
 									</select>
 								</div>
 							</td>


 							<td width="50%">
 								<input type="hidden" name="account_credit" id="account_credit" value="Account Payable">
 								<br>
 								<p style="font-size: 18px">&nbsp; &nbsp; <strong style="color: red; ">CREDIT</strong>: <?= $supplier_name_desc ?></p>

 								<?php /*?>	<div class="form_sep">
	<label for="reg_input_no" class="">CREDIT</label>
	<select name="account_credit" id="account_credit" class="form-control">

		<option selected="selected" value="">Select...</option>
		<?php
		$stmt = $db->query("SELECT a.* FROM chart_accounts as a
		INNER JOIN chart_groups as g ON g.id=a.account_group
		INNER JOIN chart_class as c ON c.cid=g.class_id
		WHERE g.id IN (2) 
		ORDER BY class_id,account_group");
		while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
		<option value="<?php echo  $row2['account_code']; ?>" ><?php echo $row2['account_name']; ?></option>
		<?php } ?>

	</select>
	</div>	<?php */ ?>

 							</td>
 						</tr>
 						<tr>
 							<td colspan="2">
 								<div class="form_sep">
 									<label for="reg_input_no" class="">Remarks</label>
 									<input type="text" id="remarks" name="remarks" class="form-control" value="<?= $rmk; ?>" required>

 								</div>

 							</td>

 						</tr>
 					</table>
 					<hr>

 					<div class="pull-left">

 						<button type="button" class="btn btn-primary" id="post_to_ledger" onClick="save_purchase_payment()" <?php if ($approve_amt == 0) { ?>disabled<?php } ?>>Post to Ledger</button>

 					</div>

 					<div class="pull-right">
 						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
 					</div>

 				</div>


 				<hr>

 				<input type="hidden" name="stock" id="stock" value="<?php echo $stock; ?>">
 				<input type="hidden" name="bal_amt" id="bal_amt" value="<?php echo $bal; ?>">
 				<input type="hidden" name="order_by" id="order_by" value="<?php echo $order_by; ?>">
 				<input type="hidden" name="order_date" id="order_date" value="<?php echo $order_date; ?>">
 				<input type="hidden" name="supplier_id" id="supplier_id" value="<?php echo $supplier_id; ?>">
 				<input type="text" name="batch_no" id="batch_no" value="<?php echo $batch_no; ?>">



 				<br>
 				<h3 id="grand_total">Purchase Order <u>POSTED</u>: <?= number_format($posted_amt); ?></h3>


 			<?php } ?>


 		</form>

 		<input type="hidden" name="supplier_id2" id="supplier_id2" value="<?php echo $supplier_id; ?>">

 	<?php } ?>

 	<?php }


	if (isset($_POST["print_order_id"])) {

		/// $batch_no.'/'.$order_by.'/'.$stock_table.'/'.$stock;

		$print_order_id = $_POST["print_order_id"];
		$parts = explode("/", $print_order_id);
		$batch_no = $parts[0];
		//	$order_date=$parts[1];
		$order_by = $parts[1];
		$stock_table = $parts[2];
		$stock = $parts[3];


		$stmt = $db->query("SELECT p.*,s.buying_cost,c.name,s.stock_total_unit FROM stock_table_procurment as p 
inner join stock_table as s on s.sn=p.stock_sn 
inner join stock_company as c on p.supplier_id=c.sn 
where p.stock_table='$stock_table' and p.order_by='$order_by' and p.batch_no='$batch_no'");
		if ($stmt->rowCount() > 0) { ?>

 		<div id="content">
 			<table cellpadding="5" cellspacing="5" border="0" align="center">
 				<tr>
 					<td width="50%" align="center"><img alt="image" src="../img/logo.png" height="100" width="100"></td>
 				</tr>
 				<tr>
 					<td width="50%" align="center"><strong><?php echo $_SESSION['h_name']; ?></strong></td>
 				</tr>
 				<tr>
 					<td width="50%" align="center"><?php echo $_SESSION['h_address']; ?></td>
 				</tr>
 				<tr>
 					<td width="50%" align="center"><?php echo $_SESSION['h_phone']; ?></td>
 				</tr>
 				<tr>
 					<td width="50%" align="center">
 						<h3>[PURCHASE ORDER]</h3>
 					</td>
 				</tr>
 			</table>
 			<table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
 				<thead>
 					<tr>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">#</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Name</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Unit Cost</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Qty</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Total cost</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Last/ODR Date</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Last/Qty</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Last/Price</th>
 						<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;text-align: left;">Last/Cost</th>
 					</tr>
 				</thead>
 				<tbody>

 					<?php
						$n = 1;
						$grand_t = 0;
						while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$grand_t = $grand_t + $roww['total_cost'];
							$supplier_id = $roww['supplier_id'];

							if ($roww['status'] == 'yes' or $roww['pay_status'] == 1) { /// check for approve status
								$pay_status = 1;
							}
						?>
 						<tr>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $n; ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['stock_name']; ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($roww['purchase_price']); ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['order_qty']; ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($roww['total_cost']); ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['last_order_date']; ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['last_order_qty']; ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($roww['last_purchase_price']); ?></td>
 							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($roww['last_order_total_cost']); ?></td>

 						</tr>
 					<?php
							$supplier_name = $roww['name'];
							$order_date = $roww['order_date'];
							$order_by = $roww['order_by'];
							$approve_by = $roww['approve_by'];
							$approve_date = date("d-M-Y", strtotime($roww['approve_date']));
							$n++;
						} ?>
 				</tbody>
 			</table>
 			<br>

 			<?php
				$stmt = $db->prepare('SELECT * FROM stock_company WHERE sn = :sn');
				$stmt->bindParam(':sn', $supplier_id);
				$stmt->execute();
				$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($row_d) {
					$bank_name = $row_d['bank_name'];
					$name_ = $row_d['name'];
					$account_no = $row_d['account_no'];
					$account_name = $row_d['account_name'];
					$phone = $row_d['phone'];
					$address = $row_d['address'];
					$payment_method = $row_d['payment_method'];
				}
				?>
 			<table width="100%">
 				<tr>
 					<td width="60%">
 						<table width="30%">
 							<tr>
 								<td><strong>Grand Total:</strong></td>
 								<td><strong><?php echo number_format($grand_t); ?></strong></td>
 							</tr>
 							<tr>
 								<td><strong>Ordered Date:</strong></td>
 								<td><strong><?php echo date("d-M-Y", strtotime($order_date)); ?></strong></td>
 							</tr>
 							<tr>
 								<td><strong>Ordered By:</strong></td>
 								<td><strong><?php echo $order_by; ?></strong></td>
 							</tr>
 							<tr>
 								<td>&nbsp;</td>
 								<td>&nbsp;</td>
 							</tr>
 							<tr>
 								<td colspan="2">Sign:_________________________________________</td>
 							</tr>
 							<tr>
 								<td colspan="2"><strong>Procurement Manager</strong></td>
 							</tr>
 						</table>
 					</td>
 					<td>&nbsp;</td>
 					<td width="30%">
 						<?php if ($payment_method == 'Cheque') { ?>
 							<strong>Payment Method: CHEQUE</strong>
 						<?php } else { ?>
 							<table width="100%">
 								<tr>
 									<td><strong>Account Detials: <br><?php echo $name_ . '/' . $account_name; ?></strong></td>
 								</tr>
 								<tr>
 									<td><strong>[ <?php echo $account_no; ?> ] </strong></td>
 								</tr>
 								<tr>
 									<td><strong>Bank Name: <?php echo $bank_name; ?></strong></td>
 								</tr>
 								<tr>
 									<td><strong>Phone: <?php echo $phone; ?></strong></td>
 								</tr>
 							</table>
 						<?php } ?>

 					</td>
 				</tr>
 			</table>


 			<?php if ($pay_status == '1') { ?>

 				<hr>
 				<table width="30%">
 					<tr>
 						<td><strong>Approved By:</strong></td>
 						<td><strong><?php echo $approve_by; ?></strong></td>
 					</tr>
 					<tr>
 						<td><strong>Approved Date:</strong></td>
 						<td><strong><?php echo $approve_date; ?></strong></td>
 					</tr>
 				</table>
 				<hr>

 				<?php

					$total_amount = 0;
					$stmt = $db->query("SELECT * FROM stock_table_procure_pay where ordered_by='$order_by' and ordered_date='$order_date' and supplier_id='$supplier_id'");
					if ($stmt->rowCount() > 0) { ?>
 					<strong>Payment History</strong>
 					<table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
 						<thead>
 							<tr>
 								<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;">#</th>
 								<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;">Date</th>
 								<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;">Amount</th>
 								<th style="border-bottom: 1px solid #000; border-top: 1px solid #000;">Entered By</th>
 							</tr>
 						</thead>
 						<tbody>
 							<?php
								$i = 1;
								while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
									$total_amount = $total_amount + $roww['amount']; ?>
 								<tr>
 									<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $i; ?></td>
 									<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo date("d-M-y", strtotime($roww['date_captured']));; ?></td>
 									<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($roww['amount']); ?></td>
 									<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['captured_by']; ?></td>
 								</tr>
 							<?php $i++;
								} ?>
 					</table>
 					<strong>Amount Paid: <?php echo number_format($total_amount); ?></strong>
 			<?php  }
				}

				?>

 			<br>
 			<br>
 			<div style="font-size: 14px; " align="center"><strong>Please Note:</strong> Purchase order is been Expected to be completed within 3 day of your acknowledgment, the order will be invalid when exceeded.</div>
 			<br><br>
 			<hr>
 			<div align="center"><strong>MEDICAL DIRECTOR APPROVAL STAMP</strong></div>


 		</div>
 		<button type="button" class="btn btn-default btn-xs" data-dismiss="modal">Close</button>
 		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 		<input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report" />


 	<?php } else { ?>
 		<div class="alert alert-warning">No Records to show </div>

 	<?php }
	}

	if (isset($_POST["editprice_id"])) {


		$editprice_id = $_POST["editprice_id"];
		$parts = explode("__", $editprice_id);
		$sn = $parts['0'];
		$view = $parts['1'];

		$stmt = $db->prepare('SELECT * FROM patient_ap_services WHERE sn = :sn');
		$stmt->bindParam(':sn', $sn);
		$stmt->execute();
		$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($row_d) {
			$emr = $row_d['hospital_no'];
			$item_services = $row_d['item_services'];
			$pay = $row_d['pay'];
			$claim_amt = $row_d['claim_amt'];
			$qty = $row_d['qty'];
			$pay_mode = $row_d['pay_mode'];
			$claim_interest = $row_d['claim_interest'];
			$paystatus = $row_d['paystatus'];
		}

		?>

 	<form method="POST" action="pacct.php?emr=<?php echo $emr . '&' . $view; ?>">

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
 			<label for="reg_input_no" class="req">Claim Interest</label>
 			<input type="text" id="claim_interest" name="claim_interest" class="form-control" value="<?php echo $claim_interest; ?>" required>
 		</div>

 		<div class="form_sep">
 			<label for="reg_input_no" class="req">Quantity</label>
 			<input type="number" id="qty" name="qty" class="form-control" maxlength="3" value="<?php echo $qty; ?>" required>
 		</div>


 		<div class="form_sep">
 			<label for="reg_input_no" class="req">Type</label>
 			<select name="mode" id="mode" class="form-control" required>
 				<option value="claim" <?php if ($pay_mode == 'claim') { ?> selected="selected" <?php } ?>>claim</option>
 				<option value="cash" <?php if ($pay_mode == 'cash') { ?> selected="selected" <?php } ?>>cash payment</option>
 			</select>
 		</div>

 		<div class="form_sep">
 			<button class="btn btn-primary btn-sm" type="submit" name="edit_update_price" id="edit_update_price">Save Changes</button>
 		</div>
 		<input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
 		<input type="hidden" name="sn" id="sn" value="<?php echo $sn; ?>" />
 		<input type="hidden" name="paystatus_status" id="sn" value="<?php echo $paystatus; ?>" />
 	</form>

 	<?php
	}

	if (isset($_POST["search_detials"])) {

		$target = $_POST["target"];

		if ($target == 'frontdesk') {
			$link = 'index.php?ptm=all/';
		}
		if ($target == 'sales') {
			$link = 'index.php?sale=';
		}
		if ($target == 'Ex_sales') {
			$link = 'index.php?sale=';
		}
		if ($target == 'Billing_Ex_sales') {
			$link = 'pacct.php?emr=';
			$target = 'Ex_sales';
		}
		if ($target == 'billing') {
			$link = 'pacct.php?emr=';
			$target = '';
		}



		////billing/pacct.php?emr=EX13

		$search_detials = trim($_POST["search_detials"]);
		$search_detials2 = trim($_POST["search_detials"]);
		$search_detials_split = $search_detials;
		$search_detials = "%$search_detials%";
		$length_search = strlen($search_detials2);

		if (isset($_POST["link"])) {
			$link = $_POST["link"];
		}

		$word_count = str_word_count($search_detials);
		$partt = explode(" ", $search_detials_split);


		if ($target == 'Ex_sales') {

			$query = $db->prepare("SELECT * FROM pharm_ext	WHERE transc_code like :transc_code or cust_name like :cust_name or phone like :phone");
			$query->bindParam(':transc_code', $search_detials);
			$query->bindParam(':cust_name', $search_detials);
			$query->bindParam(':phone', $search_detials);
			$query->execute();
			//echo $query->rowCount();
			///exit;


		} elseif (count($partt) == 1) {

			if (preg_match("/[a-z]/i", $search_detials)) {

				$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
							FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
								WHERE surname like :surname or fname like :fname or oname like :oname");
				$query->bindParam(':surname', $search_detials);
				$query->bindParam(':fname', $search_detials);
				$query->bindParam(':oname', $search_detials);
			} elseif ($length_search >= 3  && $length_search <= 6) {
				$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname,e.phone , e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE e.hospital_no like :hospital_no or e.old_hospital_no like :old_hospital_no");
				$query->bindParam(':hospital_no', $search_detials);
				$query->bindParam(':old_hospital_no', $search_detials);
			} elseif ($length_search >= 10) {
				$query = $db->prepare("SELECT e.hospital_no, e.surname,e.fname,e.phone , e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no WHERE e.phone like :phone");
				$query->bindParam(':phone', $search_detials);
			} else {
				echo 'Search Text Specified Not Available!';
				exit;
			}

			$query->execute();
		} else if (count($partt) == 2) {

			$partt = explode(" ", $search_detials_split);
			$name1 = $partt[0];
			$name2 = $partt[1];
			$name1 =  "$name1%";
			$name2 =  "$name2%";

			$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE 
							(e.surname LIKE :name1 AND e.fname LIKE :name2)  OR
							(e.surname LIKE :name3 AND e.fname LIKE :name4)  OR
							(e.oname LIKE :name5 AND e.surname LIKE :name6) OR
							(e.oname LIKE :name7 AND e.surname LIKE :name8) OR
							(e.fname LIKE :name9 AND e.oname LIKE :name10) OR 
							(e.fname LIKE :name11 AND e.oname LIKE :name12) OR
							(e.fname LIKE :name13 AND e.oname LIKE :name14) 
							
							");

			$query->bindParam(':name1', $name1);
			$query->bindParam(':name2', $name2);
			$query->bindParam(':name3', $name2);
			$query->bindParam(':name4', $name1);
			$query->bindParam(':name5', $name1);
			$query->bindParam(':name6', $name2);
			$query->bindParam(':name7', $name2);
			$query->bindParam(':name8', $name1);
			$query->bindParam(':name9', $name1);
			$query->bindParam(':name10', $name2);
			$query->bindParam(':name11', $name2);
			$query->bindParam(':name12', $name1);
			$query->bindParam(':name13', $search_detials_split);
			$query->bindParam(':name14', $search_detials_split);
			$query->execute();
		} else if (count($partt) == 3) {

			$partt = explode(" ", $search_detials_split);
			$name1 = $partt[0];
			$name2 = $partt[1];
			$name3 = $partt[2];



			$name1 =  "$name1%";
			$name2 =  "$name2%";
			$name3 =  "$name3%";

			$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE 
							(e.fname LIKE :name2 AND e.surname LIKE :name1 AND e.oname LIKE :name3)  OR
							(e.fname LIKE :name4 AND e.surname LIKE :name5 AND e.oname LIKE :name6)  OR
							(e.fname LIKE :name7 AND e.surname LIKE :name8 AND e.oname LIKE :name9)  OR
							(e.fname LIKE :name10 AND e.surname LIKE :name11 AND e.oname LIKE :name12)  OR
							(e.fname LIKE :name13 AND e.surname LIKE :name14 AND e.oname LIKE :name15)  OR
							(e.fname LIKE :name16 AND e.surname LIKE :name17 AND e.oname LIKE :name18)  OR

							(e.surname LIKE :name22 AND e.oname LIKE :name23 ) OR
							(e.surname LIKE :name24 AND e.fname LIKE :name25 ) OR
							(e.fname LIKE :name26 AND e.surname LIKE :name27 ) OR
							(e.fname LIKE :name28 AND e.oname LIKE :name29 ) OR
							(e.oname LIKE :name30 AND e.fname LIKE :name31 ) OR
							(e.oname LIKE :name32 AND e.surname LIKE :name33 ) 
							
							");

			// surname fname oname : 1 2 3
			$query->bindParam(':name1', $name2);
			$query->bindParam(':name2', $name1);
			$query->bindParam(':name3', $name3);

			// fname surname oname 
			$query->bindParam(':name4', $name1);
			$query->bindParam(':name5', $name2);
			$query->bindParam(':name6', $name3);
			//
			//  oname fname surname
			$query->bindParam(':name7', $name2);
			$query->bindParam(':name8', $name3);
			$query->bindParam(':name9', $name1);

			//  fname  oname surname
			$query->bindParam(':name10', $name1);
			$query->bindParam(':name11', $name3);
			$query->bindParam(':name12', $name2);

			// surname oname fname 
			$query->bindParam(':name13', $name3);
			$query->bindParam(':name14', $name1);
			$query->bindParam(':name15', $name2);

			//  oname  surname fname
			$query->bindParam(':name16', $name3);
			$query->bindParam(':name17', $name2);
			$query->bindParam(':name18', $name1);

			$name22 = $name2 . ' ' . $name1;
			$name23 = $name3;
			$name24 = $name2 . ' ' . $name3;
			$name25 = $name1;
			$name26 = $name1 . ' ' . $name2;
			$name27 = $name3;
			$name28 = $name1 . ' ' . $name3;
			$name29 = $name2;
			$name30 = $name3 . ' ' . $name2;
			$name31 = $name2;
			$name32 = $name3 . ' ' . $name1;
			$name33 = $name1;

			$query->bindParam(':name22', $name21); // sf :21
			$query->bindParam(':name23', $name22); // o : 3
			$query->bindParam(':name24', $name23); // so :23
			$query->bindParam(':name25', $name24); // f : 1
			$query->bindParam(':name26', $name25); // fs :12
			$query->bindParam(':name27', $name26); // o  : 3
			$query->bindParam(':name28', $name27); // fo 13
			$query->bindParam(':name29', $name28); // s : 2
			$query->bindParam(':name30', $name29); // os : 32
			$query->bindParam(':name31', $name30); // f : 1
			$query->bindParam(':name32', $name31); // os :32
			$query->bindParam(':name33', $name32); // o  : 1
			$query->execute();
		} else {

			$partt = explode(" ", $search_detials_split);
			$query = $db->prepare("SELECT e.hospital_no, e.surname,fname, e.phone , e.oname, i.insurance_name  
					FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE e.surname like :surname and e.fname like :fname");
			$query->bindParam(':surname', $partt[0]);
			$query->bindParam(':fname', $partt[1]);
			$query->bindParam(':fname', $partt[1]);
			$query->execute();
		}

		if ($query->rowCount() > 0) {

			if ($query->rowCount() == 1) {
				header('Content-Type: application/json');

				$roww = $query->fetch(PDO::FETCH_ASSOC);
				if ($target == 'Ex_sales') {
					$hospital_no = $roww['transc_code'];
					///	exit;

				} else {
					$hospital_no = $roww['hospital_no'];
				}


				echo json_encode(["status" => 200, "redirect" => true, "hosp_no" => $hospital_no]);
				exit;
			}

		?>


 		<!-- 				<input type="text" id="search_input" placeholder="Narrow Search" class="form-control">
-->
 		<table class="table table-striped" id="search_table">
 			<thead>
 				<tr>
 					<th data-toggle="true">Hospital No</th>
 					<?php if ($target == 'Ex_sales') { ?>
 						<th data-toggle="true">Name</th>
 						<th data-toggle="true"></th>
 						<th data-toggle="true"></th>
 						<th data-toggle="true">Phone</th>
 						<th data-toggle="true"></th>

 					<?php } else { ?>

 						<th data-toggle="true">Surname</th>
 						<th data-toggle="true">First Name</th>
 						<th data-toggle="true">Other Name</th>
 						<th data-toggle="true">Phone</th>
 						<th data-toggle="true">Insurance</th>
 					<?php } ?>

 					<th data-toggle="true">Action</th>
 				</tr>
 			</thead>
 			<tbody>
 				<?php
					$n = 1;

					if ($target == 'Ex_sales') {
						while ($roww = $query->fetch(PDO::FETCH_ASSOC)) {  ?>
 						<tr>
 							<td><?php echo $hospital_no = $roww['transc_code']; ?></td>
 							<td><?php echo $roww['cust_name']; ?></td>
 							<td>-</td>
 							<td>-</td>
 							<td><?php echo $roww['phone']; ?></td>
 							<td>-</td>
 							<td>
 								<a href="<?php echo $link . $hospital_no; ?>" class="btn btn-primary btn-xs"><i class="fa fa-search-plus"></i> &nbsp;Go</a>
 							</td>
 						</tr>
 					<?php } ?>

 					<?php } else {


						while ($roww = $query->fetch(PDO::FETCH_ASSOC)) {
						?>
 						<tr>
 							<td><?php echo $hospital_no = $roww['hospital_no']; ?></td>
 							<td><?php echo $roww['surname']; ?></td>
 							<td><?php echo $roww['fname']; ?></td>
 							<td><?php echo $roww['oname']; ?></td>
 							<td><?php echo $roww['phone']; ?></td>
 							<td><?php echo $roww['insurance_name']; ?></td>
 							<td>
 								<a href="<?php echo $link . $hospital_no; ?>" class="btn btn-primary btn-xs"><i class="fa fa-search-plus"></i> &nbsp;Go</a>
 							</td>

 						</tr>
 				<?php }
					}
					?>
 			</tbody>
 		</table>

 <?php   } else {

			echo '<div class="alert alert-danger">
    <h3>PATIENT NOT FOUND. PRESS THE ESC KEY OR CLICK ANYWHERE TO CLOSE THE SCREEN AND TRY AGAIN.</h3>
</div>';
			exit;
		}
		exit;
	}

	?>

 <script>
 	function save_purchase_payment() {

 		var amt_pay = document.getElementById("amt_pay").value;
 		var stock = document.getElementById("stock").value;
 		var bal_amt = document.getElementById("bal_amt").value;
 		var order_by = document.getElementById("order_by").value;
 		var order_date = document.getElementById("order_date").value;
 		var supplier_id = document.getElementById("supplier_id2").value;
 		var batch_no = document.getElementById("batch_no").value;
 		var account_credit = document.getElementById("account_credit").value;
 		var account_debit = document.getElementById("account_debit").value;
 		var remarks = document.getElementById("remarks").value;
 		var save_purchase_payment = true;


 		if (account_credit == '') {
 			alert('Invalid Account Credit');
 			exit;
 		}
 		if (account_debit == '') {
 			alert('Invalid Account Debit');
 			exit;
 		}
 		if (remarks == '') {
 			alert('Invalid Remarks');
 			exit;
 		}

 		document.getElementById('post_to_ledger').innerHTML = "Please wait ...";
 		document.getElementById('post_to_ledger').disabled = true;


 		$.ajax({
 			url: "post_ledger.php",
 			method: "POST",
 			data: {
 				stock: stock,
 				bal_amt: bal_amt,
 				order_by: order_by,
 				supplier_id: supplier_id,
 				batch_no: batch_no,
 				account_credit: account_credit,
 				account_debit: account_debit,
 				save_purchase_payment: save_purchase_payment,
 				amt_pay: amt_pay,
 				remarks: remarks
 			},
 			success: function(data) {

 				var json = JSON.parse(data);

 				if (json["status"] == 1) {

 					toastr.success(json["message"], 'Attention', {
 						timeOut: 5000
 					}); //var rrr=json["rrr"];
 					document.getElementById('post_to_ledger').innerHTML = "Done";
 					///document.getElementById('post_to_ledger').disabled = true; 


 				} else {

 					toastr.error(json["message"], 'Attention', {
 						timeOut: 5000
 					}); //var rrr=json["rrr"];
 					document.getElementById('post_to_ledger').innerHTML = "Post to Ledger";
 					document.getElementById('post_to_ledger').disabled = false;

 				}

 				/// 




 			}
 		});

 	}


 	$(document).ready(function() {

 		$("#lab_image_div").hide();


 		$('#rights').on('change', function() {


 			if (this.value == 'LB') {
 				$("#lab_image_div").show();

 			}

 			if (this.value != 'LB') {
 				$("#lab_image_div").hide();

 			}

 		});
 	});
 </script>


 <script>
 	function validateForm() {
 		const rejectReason = document.getElementById('reject_reason').value.trim();
 		const rejectButtonClicked = document.activeElement.name === 'reject_purchase_order';

 		if (rejectButtonClicked && rejectReason === '') {
 			alert('Please enter a reason for rejection.');
 			return false;
 		}
 		/// return confirm('Are you sure you want to submit?');
 	}
 </script>