<?php

if (isset($_POST["apply_set"])) {

	$mgt_stock = isset($_POST["mgt_stock"]) ? 1 : 0;
	$print_invoice = isset($_POST["print_invoice"]) ? 1 : 0;
	$manage_price = isset($_POST["manage_price"]) ? 1 : 0;
	$external_sale = isset($_POST["external_sale"]) ? 1 : 0;
	$ext_sale_recieve_cash = isset($_POST["ext_sale_recieve_cash"]) ? 1 : 0;
	$dsp_oncredit = isset($_POST["dsp_oncredit"]) ? 1 : 0;
	$view_consult_notes = isset($_POST["view_consult_notes"]) ? 1 : 0;
	$edit_price_point_sale = isset($_POST["edit_price_point_sale"]) ? 1 : 0;
	$reverse_drug = isset($_POST["reverse_drug"]) ? 1 : 0;

	$stmt = $db->prepare("SELECT username FROM pharm_users WHERE username=:username");
	$stmt->bindParam(':username', $_POST["username"]);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {

		$username = $_POST["username"];

		// Step 1: Get current record
		$currentStmt = $db->prepare("SELECT * FROM pharm_users WHERE username = :username");
		$currentStmt->execute([':username' => $username]);
		$currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);

		if (!$currentData) {
			die("User not found");
		}

		// Step 2: Fields to compare
		$fields = [
			"speciality",
			"mgt_stock",
			"print_invoice",
			"manage_price",
			"external_sale",
			"ext_sale_recieve_cash",
			"dsp_oncredit",
			"view_consult_notes",
			"edit_price_point_sale",
			"reverse_drug"
		];

		// Step 3: Compare and collect changes
		$changedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
		$changedColumns = [];
		$oldValues = [];
		$newValues = [];

		foreach ($fields as $field) {
			$new = isset($_POST[$field]) ? $_POST[$field] : 0;
			$old = $currentData[$field];

			if ((string)$old !== (string)$new) {
				$changedColumns[] = $field;
				$oldValues[$field] = $old;
				$newValues[$field] = $new;
			}
		}

		// Step 4: Insert one log entry if changes exist
		$table_name = "PHARMACY";
		if (!empty($changedColumns)) {
			$logStmt = $db->prepare("
				INSERT INTO admin_users_rights_log
				(username, changed_field, old_value, new_value, changed_by, table_name, changed_at)
				VALUES (:username, :changed_fields, :old_values, :new_values, :changed_by,:table_name, NOW())
			");

			$logStmt->execute([
				':username'       => $username,
				':changed_fields' => implode(',', $changedColumns),
				':old_values'     => json_encode($oldValues),
				':new_values'     => json_encode($newValues),
				':changed_by'     => $changedBy,
				':table_name'     => $table_name
			]);
		}



		$stmt = $db->prepare("UPDATE pharm_users SET 
										speciality=:speciality, 
										mgt_stock=:mgt_stock, 
										print_invoice=:print_invoice, 
										manage_price=:manage_price, 
										external_sale=:external_sale, 
										ext_sale_recieve_cash=:ext_sale_recieve_cash, 
										dsp_oncredit=:dsp_oncredit, 
										view_consult_notes=:view_consult_notes, 
										edit_price_point_sale=:edit_price_point_sale, 
										reverse_drug=:reverse_drug 
										WHERE username=:username");

		$stmt->bindParam(':speciality', $_POST["speciality"]);
		$stmt->bindParam(':mgt_stock', $mgt_stock);
		$stmt->bindParam(':print_invoice', $print_invoice);
		$stmt->bindParam(':manage_price', $manage_price);
		$stmt->bindParam(':external_sale', $external_sale);
		$stmt->bindParam(':ext_sale_recieve_cash', $ext_sale_recieve_cash);
		$stmt->bindParam(':dsp_oncredit', $dsp_oncredit);
		$stmt->bindParam(':view_consult_notes', $view_consult_notes);
		$stmt->bindParam(':edit_price_point_sale', $edit_price_point_sale);
		$stmt->bindParam(':reverse_drug', $reverse_drug);
		$stmt->bindParam(':username', $_POST["username"]);
		$stmt->execute();

?>
		<div class="alert alert-success">Saved Successfully</div>
	<?php
	} else {
		$stmt = $db->prepare("INSERT INTO pharm_users(username, speciality, mgt_stock, print_invoice, manage_price, external_sale, ext_sale_recieve_cash, dsp_oncredit, view_consult_notes, edit_price_point_sale,reverse_drug) 
										VALUES (:username, :speciality, :mgt_stock, :print_invoice, :manage_price, :external_sale, :ext_sale_recieve_cash, :dsp_oncredit, :view_consult_notes, :edit_price_point_sale,:reverse_drug)");

		$stmt->bindParam(':username', $_POST["username"]);
		$stmt->bindParam(':speciality', $_POST["speciality"]);
		$stmt->bindParam(':mgt_stock', $mgt_stock);
		$stmt->bindParam(':print_invoice', $print_invoice);
		$stmt->bindParam(':manage_price', $manage_price);
		$stmt->bindParam(':external_sale', $external_sale);
		$stmt->bindParam(':ext_sale_recieve_cash', $ext_sale_recieve_cash);
		$stmt->bindParam(':dsp_oncredit', $dsp_oncredit);
		$stmt->bindParam(':view_consult_notes', $view_consult_notes);
		$stmt->bindParam(':edit_price_point_sale', $edit_price_point_sale);
		$stmt->bindParam(':reverse_drug', $reverse_drug);
		$stmt->execute();
	?>
		<div class="alert alert-success">Saved Successfully</div>
<?php
	}
}

?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>PHARMACY USERS Privileges</h5>
			</div>

			<div class="ibox-content">

				<form action="index.php?phm" method="POST">

					<div class="row">
						<div class="col-md-8">
							<div class="form_sep">
								<label for="reg_input_no" class="req">Select a User & Setup Rights</label>
								<select name="staff_id" class="input-sm chosen-select" style="width:350px;">
									<option selected="selected" value="">Search and Select Staff</option>
									<?php $stmt = $db->query("SELECT u.id, h.FirstName, h.MiddleName, h.LastName, h.Cadre 
						FROM admin_users as u inner join hremp as h on h.EmployeeCode=u.EmployeeCode WHERE u.status='1' and
						(rights='MD' or rights='GM' or rights='AC' or rights='PH' or rights='AD' or rights='DR')
						order by FirstName");
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
										<option value="<?php echo $row["id"]; ?>"><?php echo $row["FirstName"] . ' ' . $row["LastName"] . ' ' . $row["MiddleName"] . ' (' . $row["Cadre"] . ')'; ?></option>
									<?php } ?>
								</select>

							</div>
							<div class="form_sep">
								<button type="submit" class="btn btn-success btn btn-sm" autofocus name="apply_">Apply Search</button>
							</div>
						</div>

						<div class="col-md-4">
							<label for="reg_input_no" class="">.</label><br>
							<a rel="" href="index.php?rit" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>
						</div>
					</div>
				</form>


				<hr>
				<?php

				if (isset($_POST['apply_'])) {
					$stmt = $db->prepare("SELECT fullname, username FROM admin_users WHERE id=:staff_id");
					$stmt->bindParam(':staff_id', $_POST['staff_id']);
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);

				?>


					<form action="index.php?phm" method="POST">

						<div class="alert alert-success">
							<label for="" class="">Current User : <?php echo $row['fullname']; ?></label>
							<input type="hidden" name="username" value="<?php echo $row['username']; ?>">
						</div>


						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th width="5%" data-toggle="true">&nbsp;</th>
									<th width="46%" data-toggle="true">Rights</th>
									<th width="5%" data-toggle="true">&nbsp;</th>
									<th width="44%" data-toggle="true">Rights</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$stmt = $db->prepare("SELECT * FROM pharm_users WHERE username=:username");
								$stmt->bindParam(':username', $row['username']);
								$stmt->execute();
								$row2 = $stmt->fetch(PDO::FETCH_ASSOC);
								?>
								<tr>
									<td><input type="checkbox" name="mgt_stock" value="1" <?php if ($row2['mgt_stock'] == 1) { ?> checked <?php } ?>></td>
									<td>Manage Stock</td>
									<td><input type="checkbox" name="print_invoice" value="1" <?php if ($row2['print_invoice'] == 1) { ?> checked <?php } ?>></td>
									<td>Print Invoice</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="manage_price" value="1" <?php if ($row2['manage_price'] == 1) { ?> checked <?php } ?>></td>
									<td>Manage Price <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td><input type="checkbox" name="external_sale" value="1" <?php if ($row2['external_sale'] == 1) { ?> checked <?php } ?>></td>
									<td>External Sales</td>
								</tr>

								<tr>
									<td>
										<input type="checkbox" name="ext_sale_recieve_cash" value="1" <?php if ($row2['ext_sale_recieve_cash'] == 1) { ?> checked <?php } ?>>
									</td>
									<td>Post/Recieve Cash (External Sales Only)</td>
									<td>
										<input type="checkbox" name="dsp_oncredit" value="1" <?php if ($row2['dsp_oncredit'] == 1) { ?> checked <?php } ?>>
									</td>
									<td>Dispense On-Credit <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
								</tr>
								<tr>
									<td>
										<input type="checkbox" name="view_consult_notes" value="1" <?php if ($row2['view_consult_notes'] == 1) { ?> checked <?php } ?>>
									</td>
									<td>View Consultation Notes <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td>
										<input type="checkbox" name="edit_price_point_sale" value="1" <?php if ($row2['edit_price_point_sale'] == 1) { ?> checked <?php } ?>>
									</td>
									<td>
										Edit At Point of Sale Rights <i class="fa fa-exclamation-triangle" style="color: red;"></i>

									</td>
								</tr>
								<tr>
									<td>
										<input type="checkbox" name="reverse_drug" value="1" <?php if ($row2['reverse_drug'] == 1) { ?> checked <?php } ?>>
									</td>
									<td>Reverse Drug <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td>

									</td>
									<td>
									</td>
								</tr>



							</tbody>
						</table>
						<div class="form_sep">
							<label for="reg_input_no" class="">Select User Type</label>
							<select name="speciality" id="speciality" class="form-control" required>

								<?php if ($row2['speciality'] == '') { ?>
									<option selected="selected" value="">Select ...</option>
								<?php } else { ?>
									<option selected="selected" value="<?php echo $row2['speciality'] ?>"><?php echo $row2['speciality'] ?></option>
								<?php } ?>

								<option value="User">User</option>
								<option value="Pharmacist">Pharmacist</option>
							</select>
						</div>

						<div class="form_sep">
							<button class="btn btn-primary" type="submit" name="apply_set">Apply</button>
						</div>

					</form>

				<?php } else { ?>

					<div class="alert alert-warning">
						<p>Select User from List above to Display Here</p>
					</div>


				<?php } ?>

			</div>

		</div>
	</div>



</div>