<?php

if (isset($_POST["apply_set"])) {

	$invoice = isset($_POST["invoice"]) ? 1 : 0;
	$reciept = isset($_POST["reciept"]) ? 1 : 0;
	$deposit = isset($_POST["deposit"]) ? 1 : 0;
	$lab = isset($_POST["lab"]) ? 1 : 0;
	$pharm = isset($_POST["pharm"]) ? 1 : 0;
	$nursing = isset($_POST["nursing"]) ? 1 : 0;
	$other_bill = isset($_POST["other_bill"]) ? 1 : 0;
	$reprint = isset($_POST["reprint"]) ? 1 : 0;
	$transfer = isset($_POST["transfer"]) ? 1 : 0;
	$refund = isset($_POST["refund"]) ? 1 : 0;
	$discount = isset($_POST["discount"]) ? 1 : 0;
	$claims = isset($_POST["claims"]) ? 1 : 0;
	$vouchers = isset($_POST["vouchers"]) ? 1 : 0;
	$reversal = isset($_POST["reversal"]) ? 1 : 0;
	$writeoff = isset($_POST["writeoff"]) ? 1 : 0;
	$edit_price_at_point = isset($_POST["edit_price_at_point"]) ? 1 : 0;


	$stmt = $db->prepare('SELECT username FROM biller_users WHERE username = :username');
	$stmt->execute([
		':username' => $_POST["username"]
	]);
	if ($stmt->rowCount() > 0) {

		try {
			// Begin transaction
			$db->beginTransaction();

			// Step 1: Get current values from DB
			$currentStmt = $db->prepare("SELECT * FROM biller_users WHERE username = :username");
			$currentStmt->execute([':username' => $_POST["username"]]);
			$currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);

			if (!$currentData) {
				throw new Exception("User not found");
			}

			// Step 2: Fields to compare
			$fields = [
				"speciality",
				"invoice",
				"reciept",
				"deposit",
				"lab",
				"pharm",
				"nursing",
				"other_bill",
				"reprint",
				"transfer",
				"refund",
				"discount",
				"claims",
				"vouchers",
				"reversal",
				"writeoff",
				"edit_price_at_point"
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

			// Step 4: Log changes (only if any)
			$table_name = "BILLING";
			$username = $_POST["username"];

			if (!empty($changedColumns)) {
				$logStmt = $db->prepare("
			INSERT INTO admin_users_rights_log
			(username, changed_field, old_value, new_value, changed_by, table_name, changed_at)
			VALUES (:username, :changed_fields, :old_values, :new_values, :changed_by, :table_name, NOW())");

				$logStmt->execute([
					':username'       => $username,
					':changed_fields' => implode(',', $changedColumns),
					':old_values'     => json_encode($oldValues),
					':new_values'     => json_encode($newValues),
					':changed_by'     => $changedBy,
					':table_name'     => $table_name
				]);
			}

			// Step 5: Perform update
			$stmt = $db->prepare('UPDATE biller_users 
		SET speciality = :speciality, 
			invoice = :invoice, 
			reciept = :reciept, 
			deposit = :deposit, 
			lab = :lab, 
			pharm = :pharm, 
			nursing = :nursing, 
			other_bill = :other_bill, 
			reprint = :reprint, 
			transfer = :transfer, 
			refund = :refund, 
			discount = :discount, 
			claims = :claims, 
			vouchers = :vouchers, 
			reversal = :reversal, 
			writeoff = :writeoff, 
			edit_price_at_point = :edit_price_at_point 
		WHERE username = :username');

			$stmt->execute([
				':speciality' => $_POST["speciality"],
				':invoice' => isset($_POST["invoice"]) ? 1 : 0,
				':reciept' => isset($_POST["reciept"]) ? 1 : 0,
				':deposit' => isset($_POST["deposit"]) ? 1 : 0,
				':lab' => isset($_POST["lab"]) ? 1 : 0,
				':pharm' => isset($_POST["pharm"]) ? 1 : 0,
				':nursing' => isset($_POST["nursing"]) ? 1 : 0,
				':other_bill' => isset($_POST["other_bill"]) ? 1 : 0,
				':reprint' => isset($_POST["reprint"]) ? 1 : 0,
				':transfer' => isset($_POST["transfer"]) ? 1 : 0,
				':refund' => isset($_POST["refund"]) ? 1 : 0,
				':discount' => isset($_POST["discount"]) ? 1 : 0,
				':claims' => isset($_POST["claims"]) ? 1 : 0,
				':vouchers' => isset($_POST["vouchers"]) ? 1 : 0,
				':reversal' => isset($_POST["reversal"]) ? 1 : 0,
				':writeoff' => isset($_POST["writeoff"]) ? 1 : 0,
				':edit_price_at_point' => isset($_POST["edit_price_at_point"]) ? 1 : 0,
				':username' => $_POST["username"]
			]);

			// Step 6: Commit transaction
			$db->commit();
?>
			<div class="alert alert-success">Saved successfully. The user must log in again for the changes to take effect</div>
		<?php

		} catch (Exception $e) {
			// Rollback if anything fails
			$db->rollBack();
			echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
		}
	} else {
		try {
			// Begin transaction
			$db->beginTransaction();

			$username = $_POST["username"];
			$speciality = $_POST["speciality"];
			$changedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
			$table_name = "BILLING";

			// Step 1: Prepare insert
			$stmt = $db->prepare('INSERT INTO biller_users(
		username, speciality, invoice, reciept, deposit, lab, pharm, nursing, other_bill, reprint, transfer, 
		refund, discount, claims, vouchers, reversal, writeoff, edit_price_at_point
	) VALUES (
		:username, :speciality, :invoice, :reciept, :deposit, :lab, :pharm, :nursing, :other_bill, :reprint, :transfer,
		:refund, :discount, :claims, :vouchers, :reversal, :writeoff, :edit_price_at_point
	)');

			// Convert checkbox-style inputs (unchecked → 0)
			$params = [
				':username' => $username,
				':speciality' => $speciality,
				':invoice' => isset($_POST["invoice"]) ? 1 : 0,
				':reciept' => isset($_POST["reciept"]) ? 1 : 0,
				':deposit' => isset($_POST["deposit"]) ? 1 : 0,
				':lab' => isset($_POST["lab"]) ? 1 : 0,
				':pharm' => isset($_POST["pharm"]) ? 1 : 0,
				':nursing' => isset($_POST["nursing"]) ? 1 : 0,
				':other_bill' => isset($_POST["other_bill"]) ? 1 : 0,
				':reprint' => isset($_POST["reprint"]) ? 1 : 0,
				':transfer' => isset($_POST["transfer"]) ? 1 : 0,
				':refund' => isset($_POST["refund"]) ? 1 : 0,
				':discount' => isset($_POST["discount"]) ? 1 : 0,
				':claims' => isset($_POST["claims"]) ? 1 : 0,
				':vouchers' => isset($_POST["vouchers"]) ? 1 : 0,
				':reversal' => isset($_POST["reversal"]) ? 1 : 0,
				':writeoff' => isset($_POST["writeoff"]) ? 1 : 0,
				':edit_price_at_point' => isset($_POST["edit_price_at_point"]) ? 1 : 0
			];

			// Execute insert
			$stmt->execute($params);

			// Step 2: Log creation
			$logStmt = $db->prepare("
		INSERT INTO admin_users_rights_log
		(username, changed_field, old_value, new_value, changed_by, table_name, changed_at)
		VALUES (:username, :changed_fields, :old_values, :new_values, :changed_by, :table_name, NOW())
	");

			$logStmt->execute([
				':username'       => $username,
				':changed_fields' => 'ALL',
				':old_values'     => json_encode([]),
				':new_values'     => json_encode($params),
				':changed_by'     => $changedBy,
				':table_name'     => $table_name
			]);

			// Step 3: Commit transaction
			$db->commit();
		?>
			<div class="alert alert-success">Saved successfully. The user must log in again for the changes to take effect</div>
<?php

		} catch (Exception $e) {
			// Rollback on failure
			$db->rollBack();
			echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
		}
	}
}

?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>BILLER/CASHIER User Privileges</h5>
			</div>

			<div class="ibox-content">

				<?php
				$stmt = $db->prepare('SELECT * FROM admin_users WHERE status = :status ORDER BY fullname');
				$stmt->execute([':status' => '1']);
				?>
				<form action="index.php?bill" method="POST">

					<div class="row">

						<div class="col-md-8">


							<div class="form_sep">
								<label for="reg_input_no" class="req">Select a User & Setup Rights</label>
								<select name="staff_id" class="input-sm chosen-select" style="width:350px;">
									<option selected="selected" value="">Search and Select Staff</option>
									<?php $stmt = $db->query("SELECT u.id, h.FirstName, h.MiddleName, h.LastName, h.Cadre 
						FROM admin_users as u inner join hremp as h on h.EmployeeCode=u.EmployeeCode 
						WHERE u.status='1' order by FirstName");
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
				<?php if (isset($_POST['apply_'])) {
					$stmt = $db->prepare('SELECT fullname, username FROM admin_users WHERE id = :staff_id');
					$stmt->bindParam(':staff_id', $_POST['staff_id']);
					$stmt->execute();

					$row = $stmt->fetch(PDO::FETCH_ASSOC);

				?>

					<form action="index.php?bill" method="POST">

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
								$stmt = $db->prepare('SELECT * FROM biller_users WHERE username = :username');
								$stmt->bindParam(':username', $row['username']);
								$stmt->execute();
								$row2 = $stmt->fetch(PDO::FETCH_ASSOC);
								?>
								<tr>
									<td><input type="checkbox" name="invoice" value="1" <?php if ($row2['invoice'] == 1) { ?> checked <?php } ?>></td>
									<td>Print Invoice</td>
									<td><input type="checkbox" name="reciept" value="1" <?php if ($row2['reciept'] == 1) { ?> checked <?php } ?>></td>
									<td>Print Reciept</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="deposit" value="1" <?php if ($row2['deposit'] == 1) { ?> checked <?php } ?>></td>
									<td>Accept Deposit</td>
									<td><input type="checkbox" name="lab" value="1" <?php if ($row2['lab'] == 1) { ?> checked <?php } ?>></td>
									<td>Post Laboratory bills only</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="pharm" value="1" <?php if ($row2['pharm'] == 1) { ?> checked <?php } ?>></td>
									<td>Post Pharmacy Bills</td>
									<td><input type="checkbox" name="nursing" value="1" <?php if ($row2['nursing'] == 1) { ?> checked <?php } ?>></td>
									<td>Nursing Station Bills</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="other_bill" value="1" <?php if ($row2['other_bill'] == 1) { ?> checked <?php } ?>></td>
									<td>Post Others Bills (Consultation, etc)</td>
									<td><input type="checkbox" name="reprint" value="1" <?php if ($row2['reprint'] == 1) { ?> checked <?php } ?>></td>
									<td>Reprint Reciepts</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="transfer" value="1" <?php if ($row2['transfer'] == 1) { ?> checked <?php } ?>></td>
									<td>Transfer Money/From Another Patient Wallet <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td><input type="checkbox" name="refund" value="1" <?php if ($row2['refund'] == 1) { ?> checked <?php } ?>></td>
									<td>
										<p style="color:red;">Accountant Rights</p> (Refund Patient Deposit In Cash/Bank)<i class="fa fa-exclamation-triangle" style="color: red;"></i>
									</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="discount" value="1" <?php if ($row2['discount'] == 1) { ?> checked <?php } ?>></td>
									<td>Give Discount/Charges/Credit Limit <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td><input type="checkbox" name="claims" value="1" <?php if ($row2['claims'] == 1) { ?> checked <?php } ?>></td>
									<td>Add Claims</td>
								</tr>

								<tr>
									<td><input type="checkbox" name="vouchers" value="1" <?php if ($row2['vouchers'] == 1) { ?> checked <?php } ?>></td>
									<td>Vouchers <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td><input type="checkbox" name="reversal" value="1" <?php if ($row2['reversal'] == 1) { ?> checked <?php } ?>></td>
									<td>Reverse Paid/Unpaid services <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

								</tr>

								<tr>
									<td><input type="checkbox" name="writeoff" value="1" <?php if ($row2['writeoff'] == 1) { ?> checked <?php } ?>></td>
									<td>Write off Credits <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
									<td>
										<input type="checkbox" name="edit_price_at_point" value="1" <?php if ($row2['edit_price_at_point'] == 1) { ?> checked <?php } ?>>
									</td>
									<td>
										Edit Price At Point of Sale <i class="fa fa-exclamation-triangle" style="color: red;"></i>
									</td>

								</tr>


							</tbody>
						</table>
						<div class="form_sep">
							<label for="reg_input_no" class="req">Speciality Or User</label>
							<select name="speciality" id="speciality" class="form-control" required>

								<?php if ($row2['speciality'] == '') { ?>
									<option selected="selected" value="">Select ...</option>
								<?php } else { ?>
									<option selected="selected" value="<?php echo $row2['speciality'] ?>"><?php echo $row2['speciality'] ?></option>
								<?php } ?>

								<option value="Receptionist">Receptionist</option>
								<option value="Administrator">Administrator</option>
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