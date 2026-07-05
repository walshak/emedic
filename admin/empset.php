<?php



if (isset($_POST["AddCadre"])) {

	$edit_mode = $_POST["edit_mode"];
	$interface = $_POST["interface"];

	if ($edit_mode == 0) {
		$sql = $db->prepare("INSERT INTO cadre (cadre) VALUES (:cadre)");
		$sql->bindParam(':cadre', $_POST["Cadre"], PDO::PARAM_STR);
		$sql->execute();
		$msg = "saved";
	} else {
		$updateSQL = "UPDATE cadre SET cadre=:cadre WHERE sn=:sn";
		$sql = $db->prepare($updateSQL);
		$sql->bindParam(':cadre', $_POST['Cadre'], PDO::PARAM_STR);
		$sql->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);
		$sql->execute();
		$msg = "updated";
	}


	echo "<script>
	alert('" . addslashes($msg) . "');
	window.location.href = 'index.php?" . addslashes($interface) . "';
</script>";

	exit;
}

if (isset($_POST["AddDesignation"])) {

	$edit_mode = $_POST["edit_mode"];
	$interface = $_POST["interface"];

	if ($edit_mode == 0) {
		$sql = $db->prepare("INSERT INTO designation (designation) VALUES (:designation)");
		$sql->bindParam(':designation', $_POST["Designation"], PDO::PARAM_STR);
		$sql->execute();
		$msg = "saved";
	} else {
		$updateSQL = "UPDATE designation SET designation=:designation WHERE sn=:sn";
		$sql = $db->prepare($updateSQL);
		$sql->bindParam(':designation', $_POST['Designation'], PDO::PARAM_STR);
		$sql->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);
		$sql->execute();
		$msg = "updated";
	}


	echo "<script>
	alert('" . addslashes($msg) . "');
	window.location.href = 'index.php?" . addslashes($interface) . "';
</script>";

	exit;
}

if (isset($_POST["AddBank"])) {

	$edit_mode = $_POST["edit_mode"];
	$interface = $_POST["interface"];

	if ($edit_mode == 0) {
		$sql = $db->prepare("INSERT INTO bank (bank) VALUES (:bank)");
		$sql->bindParam(':bank', $_POST["Bank"], PDO::PARAM_STR);
		$sql->execute();
		$msg = "Saved successfully";
	} else {
		$updateSQL = "UPDATE bank SET bank=:bank WHERE sn=:sn";
		$sql = $db->prepare($updateSQL);
		$sql->bindParam(':bank', $_POST['Bank'], PDO::PARAM_STR);
		$sql->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);
		$sql->execute();
		$msg = "Updated successfully";
	}

	if (isset($_POST['sn']) && $_POST['sn'] != '') {
		$interface .= '=' . $_POST['sn'];
	}

	// Show alert and redirect
	echo "<script>
		alert('" . addslashes($msg) . "');
		window.location.href = 'index.php?" . addslashes($interface) . "';
	</script>";
}


if (isset($_GET["chk"])) {
	$updateSQL = "UPDATE bank SET biller=1 WHERE sn=:sn";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':sn', $_GET['chk'], PDO::PARAM_STR);
	$sql->execute();
}

if (isset($_POST["AddDepartment"])) {
	try {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->beginTransaction();

		$edit_mode = $_POST["edit_mode"];
		$interface = $_POST["interface"];

		if ($edit_mode == 0) {
			$sql = $db->prepare("INSERT INTO department 
            (department, department_type, dispensory, requistn_disp_ByMainPharm_or_depensory, view_patient_on_adm, min_amount_adm, min_amount_condition, require_amount_b4_adm) 
            VALUES 
            (:department, :category_dept, :dispensory, :requistn_disp_ByMainPharm_or_depensory, :view_patient_on_adm, :min_amount_adm, :min_amount_condition, :require_amount_b4_adm)");

			$sql->bindParam(':department', $_POST["Department"], PDO::PARAM_STR);
			$sql->bindParam(':category_dept', $_POST["category_dept"], PDO::PARAM_STR);
			// Notice here: use 'dispensory' to match your DB column!
			$sql->bindParam(':dispensory', $_POST["dispensory"], PDO::PARAM_STR);
			$sql->bindParam(':requistn_disp_ByMainPharm_or_depensory', $_POST["requistn_disp_ByMainPharm_or_depensory"], PDO::PARAM_STR);
			$sql->bindParam(':view_patient_on_adm', $_POST["view_patient_on_adm"], PDO::PARAM_STR);
			$sql->bindParam(':min_amount_adm', $_POST["min_amount_adm"], PDO::PARAM_STR);
			$sql->bindParam(':min_amount_condition', $_POST["min_amount_condition"], PDO::PARAM_STR);
			$sql->bindParam(':require_amount_b4_adm', $_POST["require_amount_b4_adm"], PDO::PARAM_STR);

			$sql->execute();
			$msg = "Saved successfully";
		} else {
			// Fetch old data before update
			$oldDataStmt = $db->prepare("SELECT * FROM department WHERE sn = :sn");
			$oldDataStmt->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);
			$oldDataStmt->execute();
			$oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

			$updateSQL = "UPDATE department SET 
            department = :department, 
            department_type = :category_dept, 
            dispensory = :dispensory, 
            requistn_disp_ByMainPharm_or_depensory = :requistn_disp_ByMainPharm_or_depensory, 
            view_patient_on_adm = :view_patient_on_adm, 
            min_amount_adm = :min_amount_adm, 
            min_amount_condition = :min_amount_condition, 
            require_amount_b4_adm = :require_amount_b4_adm 
            WHERE sn = :sn";

			$sql = $db->prepare($updateSQL);
			$sql->bindParam(':department', $_POST['Department'], PDO::PARAM_STR);
			$sql->bindParam(':category_dept', $_POST['category_dept'], PDO::PARAM_STR);
			$sql->bindParam(':dispensory', $_POST['dispensory'], PDO::PARAM_STR);
			$sql->bindParam(':requistn_disp_ByMainPharm_or_depensory', $_POST["requistn_disp_ByMainPharm_or_depensory"], PDO::PARAM_STR);
			$sql->bindParam(':view_patient_on_adm', $_POST["view_patient_on_adm"], PDO::PARAM_STR);
			$sql->bindParam(':min_amount_adm', $_POST["min_amount_adm"], PDO::PARAM_STR);
			$sql->bindParam(':min_amount_condition', $_POST["min_amount_condition"], PDO::PARAM_STR);
			$sql->bindParam(':require_amount_b4_adm', $_POST["require_amount_b4_adm"], PDO::PARAM_STR);
			$sql->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);

			$sql->execute();
			$msg = "Updated successfully";
			// Log changes
			$changes = [];
			$fields = ['department', 'department_type', 'dispensory', 'requistn_disp_ByMainPharm_or_depensory', 'view_patient_on_adm', 'min_amount_adm', 'min_amount_condition', 'require_amount_b4_adm'];

			foreach ($fields as $field) {
				$oldVal = isset($oldData[$field]) ? $oldData[$field] : '';
				// Map POST keys when different
				$postKey = $field === 'department_type' ? 'category_dept' : ($field === 'dispensory' ? 'dispensory' : $field);
				$newVal = isset($_POST[$postKey]) ? $_POST[$postKey] : '';

				if ($oldVal != $newVal) {
					$changes[] = "$field changed from '$oldVal' to '$newVal'";
				}
			}

			if (!empty($changes)) {
				$desc = "Updated department SN " . $_POST['sn'] . ": " . implode("; ", $changes);
				$setdatetime = date("Y-m-d H:i:s");
				$action = "UPDATE";

				$chk = $db->prepare("SELECT * FROM patient_staff_logs WHERE descriptions = :desc AND date_and_time = :datetime");
				$chk->bindParam(':desc', $desc);
				$chk->bindParam(':datetime', $setdatetime);
				$chk->execute();

				if ($chk->rowCount() == 0 && $desc != '') {
					$log = $db->prepare("INSERT INTO patient_staff_logs (descriptions, staff_name, action, date_and_time)
                                     VALUES (:descriptions, :staff_name, :action, :date_and_time)");
					$log->bindParam(':descriptions', $desc);
					$log->bindParam(':staff_name', $_SESSION['fullname']);
					$log->bindParam(':action', $action);
					$log->bindParam(':date_and_time', $setdatetime);
					$log->execute();
				}
			}
		}

		$db->commit();
		if (isset($_POST['sn']) and $_POST['sn'] != '') {
			$interface = $interface . '=' . $_POST['sn'];
		}

		echo "<script>
		alert('" . addslashes($msg) . "');
		window.location.href = 'index.php?" . addslashes($interface) . "';
	</script>";

		exit();
	} catch (Exception $e) {
		$db->rollBack();
		echo "Failed: " . $e->getMessage();
	}
}

/* Delete  */
if (isset($_POST['delsubmit'])) {
	$fullname = $_SESSION['fullname'];
	$dat = explode("-", $_POST['del_id']);
	$task_sn = $dat[1];
	$app_no = $dat[0];

	$deleteSQL = "DELETE FROM clinical_task WHERE sn='$task_sn' and created_by='$fullname' ";
	$sql = $db->prepare($deleteSQL);

	$deleteSQL = "DELETE FROM clinical_task_routine WHERE task_sn='$task_sn' and app_no='$app_no' ";
	$sql = $db->prepare($deleteSQL);
}



if (isset($_GET['Cadre'])) {
	$sn = $_GET['Cadre'];
	$interface = 'Cadre';
	$button = 'AddCadre';
	$table = 'cadre';
	$title = 'Add Cadre/User Type';
	$order_by = " order by cadre";
} elseif (isset($_GET['Designation'])) {
	$sn = $_GET['Designation'];
	$interface = 'Designation';
	$button = 'AddDesignation';
	$table = 'designation';
	$title = 'Add Designation';
	$order_by = " order by designation";
} elseif (isset($_GET['Department'])) {
	$sn = $_GET['Department'];
	$interface = 'Department';
	$button = 'AddDepartment';
	$table = 'department';
	$title = 'Add Department';
	$order_by = " order by department";
} elseif (isset($_GET['Bank'])) {
	$sn = $_GET['Bank'];
	$interface = 'Bank';
	$button = 'AddBank';
	$table = 'bank';
	$title = 'Add Bank';
	$order_by = " order by bank";
}


///echo $order_by;


if (isset($_GET["dl"])) {
	$dl = $_GET['dl'];

	if ($table == 'department') {
		$stmt = $db->prepare("SELECT dept FROM apptm where dept='$dl'");
		$stmt->execute();
		$apptm = $stmt->rowCount();
		$stmt = $db->prepare("SELECT dept_id FROM patient_ap_services where dept_id='$dl'");
		$stmt->execute();
		$patient_ap_services = $stmt->rowCount();
		$stmt = $db->prepare("SELECT dept_id FROM notes where dept_id='$dl'");
		$stmt->execute();
		$notes = $stmt->rowCount();
		$stmt = $db->prepare("SELECT Department FROM hremp where Department='$dl'");
		$stmt->execute();
		$Department = $stmt->rowCount();

		if ($apptm == 0 && $patient_ap_services == 0 && $notes == 0 && $Department == 0) {
			$deleteSQL = $db->prepare("DELETE FROM $table WHERE sn='$dl' $order_by");
			$deleteSQL->execute();
		} else {
			echo '<b style="color:red;">You cannot delete a department that is already active.</b>';
		}
	} else {

		$deleteSQL = $db->prepare("DELETE FROM $table WHERE sn='$dl' $order_by");
		$deleteSQL->execute();
	}


	///header("location:index.php?$interface");
}

?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5><?php echo $title; ?></h5>
			</div>
			<div class="ibox-content">

				<?php

				if ($sn != '') {
					/// there is someting there ////
					$edit_mode = 1;

					$stmt = $db->prepare("SELECT * FROM $table where sn='$sn' $order_by");
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$row = $stmt->fetch(PDO::FETCH_ASSOC);

						if ($interface == 'Cadre') {
							$display = $row['cadre'];
						} elseif ($interface == 'Designation') {
							$display = $row['designation'];
						} elseif ($interface == 'Department') {
							$display = $row['department'];
						} elseif ($interface == 'Specialist') {
							$display = $row['Specialist'];
						} elseif ($interface == 'Bank') {
							$display = $row['bank'];
						}
					}
				}



				if (!empty($_GET['status'])) {
					if ($_GET['status'] == 'saved') {
						echo "<div style='color: green;'>Record saved successfully</div>";
					} elseif ($_GET['status'] == 'updated') {
						echo "<div style='color: green;'>Record updated successfully</div>";
					}
				}


				?>




				<form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

					<div class="form_sep">
						<label for="reg_input_no" class="req"><?php echo $title; ?></label>
						<input type="text" id="<?php echo $interface; ?>" name="<?php echo $interface; ?>" class="form-control" data-required="true" placeholder="" maxlength="50" value="<?php echo $display; ?>" required>
					</div>

					<?php if (isset($_GET['Department'])) { ?>
						<div class="form_sep">
							<label for="reg_input_no" class="req">Category of Department</label>
							<select name="category_dept" id="category_dept" class="form-control" required>
								<option selected="selected" value="">Select...</option>
								<option <?php if ($row['department_type'] == 'medical services') { ?>selected <?php } ?> value="medical services">Medical Services</option>
								<option <?php if ($row['department_type'] == 'O&G') { ?>selected <?php } ?> value="O&G">Obstetric & Gynaecology</option>
								<option <?php if ($row['department_type'] == 'A&E') { ?>selected <?php } ?> value="A&E">Accident and Emergency</option>
								<option <?php if ($row['department_type'] == 'Physiotherapy') { ?>selected <?php } ?> value="Physiotherapy">Physiotherapy</option>
								<option <?php if ($row['department_type'] == 'Laboratory') { ?>selected <?php } ?> value="Laboratory">Laboratory</option>
								<option <?php if ($row['department_type'] == 'Radiology') { ?>selected <?php } ?> value="Radiology">Radiology</option>
								<option <?php if ($row['department_type'] == 'Nursing') { ?>selected <?php } ?> value="Nursing">Nursing</option>
								<option <?php if ($row['department_type'] == 'Pharmacy') { ?>selected <?php } ?> value="Pharmacy">Pharmacy</option>
								<option <?php if ($row['department_type'] == 'admin') { ?>selected <?php } ?> value="admin">Admin</option>
								<option <?php if ($row['department_type'] == 'Store') { ?>selected <?php } ?> value="Store">Store</option>
								<option <?php if ($row['department_type'] == 'Main Store') { ?>selected <?php } ?> value="Main Store">Main/Major Store</option>
							</select>
						</div>


						<div class="form_sep">
							<label for="reg_input_no" class="req">Set this to “Yes” if the department has its own mini pharmacy dispensory.</label>
							<select name="dispensory" id="dispensory" class="form-control" required>
								<option selected="selected" value="0">Select...</option>
								<option <?php if ($row['dispensory'] == '0') { ?>selected <?php } ?> value="0">no</option>
								<option <?php if ($row['dispensory'] == '1') { ?>selected <?php } ?> value="1">yes</option>

							</select>
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="req">Set to “Yes” if the Pharmacy Unit will be responsible for dispensing medication or stock requested by this department through a stock requisition order.</label>
							<select name="requistn_disp_ByMainPharm_or_depensory" id="requistn_disp_ByMainPharm_or_depensory" class="form-control" required>
								<option selected="selected" value="0">Select...</option>
								<option <?php if ($row['requistn_disp_ByMainPharm_or_depensory'] == '0') { ?>selected <?php } ?> value="0">no</option>
								<option <?php if ($row['requistn_disp_ByMainPharm_or_depensory'] == '1') { ?>selected <?php } ?> value="1">yes</option>

							</select>
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="req">Allow this department’s staff to view admitted patients in other departments?</label>
							<select name="view_patient_on_adm" id="view_patient_on_adm" class="form-control" required>
								<option selected="selected" value="0">Select...</option>
								<option <?php if ($row['view_patient_on_adm'] == '0') { ?>selected <?php } ?> value="0">yes</option>
								<option <?php if ($row['view_patient_on_adm'] == '1') { ?>selected <?php } ?> value="1">no</option>

							</select>
						</div>
						<div class="form_sep">
							<label for="reg_input_no" class="req">Set Minimum Admission Deposit for Admitted Patients into this Department</label>
							<input type="number" id="min_amount_adm" name="min_amount_adm" class="form-control" data-required="true" placeholder="" value="<?php echo $min_amount_adm = $row['min_amount_adm']; ?>">
						</div>

						<div class="form_sep">
							<?php
							$percent_credit_limit = $row['min_amount_condition'];
							$admission_limit = ($percent_credit_limit / 100) * $min_amount_adm;
							echo '<b>CREDIT LIMIT: </b>' . number_format($admission_limit, 2);
							?>
							<label for="reg_input_no" class="req">Enter the percentage of the minimum deposit to be used as the credit limit. Entering 1 will block all credit-based services, including drug dispensing, test result entry, and others.</label>
							<input type="number" id="min_amount_condition" name="min_amount_condition" class="form-control" data-required="true" max="100" min="0" placeholder="" value="<?php echo $row['min_amount_condition']; ?>">
						</div>

						<div class="form_sep">

							<?php
							$require_amount_b4_adm = $row['require_amount_b4_adm'];
							$require_amount_b4_adm = ($require_amount_b4_adm / 100) * $min_amount_adm;
							echo '<b>REQUIRED AMOUNT: </b>' . number_format($require_amount_b4_adm, 2);
							?>
							<label for="reg_input_no" class="req">Indicate whether the patient must have at least a certain percentage of the minimum deposit before being admitted to this department. Enter 1 to skip this condition.</label>
							<input type="number" id="require_amount_b4_adm" name="require_amount_b4_adm" class="form-control" data-required="true" placeholder="" min="1" max="100" value="<?php echo $row['require_amount_b4_adm']; ?>">
						</div>

					<?php } ?>


					<div class="form_sep">
						<button class="btn btn-success" type="submit" name="<?php echo $button; ?>" id="<?php echo $button; ?>"><?php if ($edit_mode == 1) { ?> Save <?php } else { ?>Create <?php $edit_mode = 0;
																																															} ?></button>
					</div>
					<input type="hidden" name="sn" value="<?php echo $row['sn']; ?>" />
					<input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
					<input type="hidden" name="interface" value="<?php echo $interface; ?>" />
					<input type="hidden" name="table" value="<?php echo $table; ?>" />
				</form>

				<hr>

				<?php



				/////echo '=======' . ;

				$stmt = $db->prepare("SELECT * FROM $table $order_by");
				$stmt->execute();
				if ($stmt->rowCount() > 0) { ?>

					<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9">
						<thead>
							<tr>
								<th data-toggle="true">SI.No</th>
								<th data-toggle="true"><?php echo $interface; ?></th>
								<?php if (isset($_GET['Department'])) { ?>
									<th data-toggle="true">Category</th>
									<th data-toggle="true">dispensory</th>
									<th data-toggle="true">Pharmacy Requisition Status</th>
									<th data-toggle="true">Admission Status</th>
								<?php } ?>
								<th data-toggle="true">Manage</th>
							</tr>
						</thead>
						<tbody>

							<?php
							$n = 1;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($colordecide % 2 == 0) {
									$bgcolor = "#F4F4F4";
								} else {
									$bgcolor = "#FFFFFF";
								}
							?>
								<tr bgcolor="<?php echo $bgcolor; ?>">
									<td><?php echo $n; ?></td>
									<td><?php if ($interface == 'Cadre') {
											echo $row['cadre'];
										} elseif ($interface == 'Designation') {
											echo $row['designation'];
										} elseif ($interface == 'Department') {
											echo $row['department'];
											echo ($row['min_amount_adm'] != '') ? ' (<b>Min Deposit: </b>' . $row['min_amount_adm'] . ')' : '';
										} elseif ($interface == 'Bank') {
											echo $row['bank'];
										} ?></td>

									<?php if (isset($_GET['Department'])) { ?>
										<td><?= $row['department_type']; ?></td>
										<td><?php echo $row['dispensory'] == 0 ? '<b style="color:blue">No</b>' : '<b style="color:red">Yes</b>'; ?></td>
										<td><?php echo $row['requistn_disp_ByMainPharm_or_depensory'] == 0 ? '<b style="color:blue">No</b>' : '<b style="color:red">Yes</b>'; ?></td>
										<td><?php echo $row['view_patient_on_adm'] == 0 ? '<b style="color:blue">Yes</b>' : '<b style="color:red">No</b>'; ?></td>
									<?php } ?>
									<td>
										<a href="index.php?<?php echo $interface . '=' . $row['sn']; ?>"> [ Edit ] </a>
										&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
										<a href="index.php?<?php echo $interface . '&dl=' . $row['sn']; ?>"> [ Delete ] </a>

										<?php if ($table == 'bank' and $row['biller'] == 0) { ?>
											&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
											<a href="index.php?<?php echo $interface . '&chk=' . $row['sn']; ?>">Check for Biller</a>
										<?php } elseif ($table == 'bank' and $row['biller'] == 1) { ?>
											<strong style="color: red;">Checked</strong>
										<?php } ?>
									</td>
								</tr>
							<?php
								$colordecide++;
								$n++;
							} ?>

						</tbody>
						<tfoot class="hide-if-no-paging">
							<tr>
								<td colspan="8" class="text-center">
									<ul class="pagination pagination-sm"></ul>
								</td>
							</tr>
						</tfoot>
					</table>
				<?php } else {
					echo 'No Records Found';
				}

				?>

			</div>
		</div>
	</div>
</div>