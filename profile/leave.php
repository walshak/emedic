<?php

if (isset($_GET["dl"])) {

	$dl = $_GET['dl'];
	$deleteSQL = $db->prepare("DELETE FROM hrlv WHERE sn='$dl'");
	$deleteSQL->execute();

	header("location:index.php?LV");
}

if (isset($_GET["LV"])) {
	$LV = $_GET["LV"];
	if ($LV != '') {
		$edit_mode = 1;
		$stmt = $db->query("Select * from hrlv where sn='$LV'");
		$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
	} else {
		$edit_mode = 0;
	}
}


if (isset($_POST["LV"])) {

	$approver_all = '';
	foreach ($_POST["approver1"] as $approver1) {
		$approver_all = $approver_all . $approver1 . ',';
	}
	$approver_all = rtrim($approver_all, ',');


	$approver_all_2 = '';
	foreach ($_POST["approver2"] as $approver1) {
		$approver_all_2 = $approver_all_2 . $approver1 . ',';
	}
	$approver_all_2 = rtrim($approver_all_2, ',');




	if ($_POST["edit_mode"] == 0) {
		if ($approver_all != '' and $approver_all_2 != '') {
			$leave_type = $_POST['leave_type'];
			$stmt = $db->query("Select * from hrlv where leave_type='$leave_type'");
			if ($stmt->rowCount() == 0) {
				$insert_stmt = $db->prepare("INSERT INTO hrlv(leave_type, days, approver1, approver2, apply_type) 
												   VALUES (:leave_type, :total_days, :approver1, :approver2, :apply_type)");

				$insert_stmt->bindParam(':leave_type', $_POST["leave_type"], PDO::PARAM_STR);
				$insert_stmt->bindParam(':total_days', $_POST["total_days"], PDO::PARAM_STR);
				$insert_stmt->bindParam(':approver1', $approver_all, PDO::PARAM_STR);
				$insert_stmt->bindParam(':approver2', $approver_all_2, PDO::PARAM_STR);
				$insert_stmt->bindParam(':apply_type', $_POST["apply_type"], PDO::PARAM_STR);

				$insert_stmt->execute();
			}
		} else {
			header("location:index.php?LV&Null_Approver");
		}
	} else {
		if ($approver_all == '') {
			$approver_all = $_POST['approver1_h'];
		}
		if ($approver_all_2 == '') {
			$approver_all_2 = $_POST['approver2_h'];
		}

		$stmt = $db->prepare("UPDATE hrlv 
                    SET leave_type = :leave_type, 
                        days = :total_days, 
                        approver1 = :approver1, 
                        approver2 = :approver2, 
                        apply_type = :apply_type
                    WHERE sn = :sn");

		$stmt->bindParam(':leave_type', $_POST['leave_type'], PDO::PARAM_STR);
		$stmt->bindParam(':total_days', $_POST['total_days'], PDO::PARAM_STR);
		$stmt->bindParam(':approver1', $approver_all, PDO::PARAM_STR);
		$stmt->bindParam(':approver2', $approver_all_2, PDO::PARAM_STR);
		$stmt->bindParam(':apply_type', $_POST['apply_type'], PDO::PARAM_STR);
		$stmt->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);

		$stmt->execute();
	}

	header("location:index.php?LV");
}
?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Set Leave Category</h5>
			</div>
			<div class="ibox-content">


				<form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

					<div class="form_sep">
						<label for="reg_input_no" class="req">Enter Leave Type</label>
						<input type="text" id="leave_type" name="leave_type" class="form-control" data-required="true" placeholder="" maxlength="50" value="<?php echo $rowx['leave_type']; ?>" required>
					</div>


					<div class="form_sep">
						<label for="reg_input_no" class="req">Enter Total Days</label>
						<input type="number" id="total_days" name="total_days" class="form-control" data-required="true" min="1" value="<?php echo $rowx['days']; ?>" required>
					</div>

					<div class="form_sep">
						<label for="reg_select" class="req">Select Leave Multiple Approver(s) I </label>
						<!--<select name="approver1" id="approver1" class="form-control" required>
-->
						<select name="approver1[]" data-placeholder=" --Select Approvers --" class="chosen-select" multiple style="width:350px;" tabindex="4">
							<option value="HOD">HOD</option>

							<?php
							$stmt_apr = $db->query("SELECT DISTINCT Designation FROM hremp");
							while ($rwx = $stmt_apr->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $rwx["Designation"]; ?>"><?php echo $rwx["Designation"]; ?></option>
							<?php } ?>
						</select>
						<input type="hidden" name="approver1_h" value="<?php echo $rowx['approver1'] ?>">
					</div>


					<div class="form_sep">
						<label for="reg_select" class="req">Select Leave Multiple Approver(s) II</label>
						<select name="approver2[]" data-placeholder=" --Select Approvers --" class="chosen-select" multiple style="width:350px;" tabindex="4">

							<?php
							$stmt_apr = $db->query("SELECT DISTINCT Designation FROM hremp");
							while ($rwx = $stmt_apr->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $rwx["Designation"]; ?>"><?php echo $rwx["Designation"]; ?></option>
							<?php } ?>
						</select>
						<input type="hidden" name="approver2_h" value="<?php echo $rowx['approver2'] ?>">

					</div>

					<div class="form_sep">
						<label for="reg_select" class="req">Select Application Type </label>
						<select name="apply_type" id="apply_type" class="form-control" required>
							<?php if ($rowx['apply_type'] != '') { ?>
								<option value="<?php echo $rowx['apply_type']; ?>"><?php echo $rowx['apply_type']; ?></option>
							<?php } else { ?>
								<option selected="selected" value="">Select ...</option>
							<?php } ?>
							<option value="Yearly">Yearly</option>
							<option value="Monthly">Monthly</option>
							<option value="Any Time">Any Time</option>
						</select>
					</div>

					<div class="form_sep">
						<div class="pull-left">
							<button class="btn btn-success" type="submit" name="LV" id="LV">Save</button>
						</div>

						<div class="pull-right">
							<a href="index.php?LV" class="btn btn-warning">Cancel</a>
						</div>
					</div>
					<input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
					<input type="hidden" name="sn" value="<?php echo $LV; ?>" />
				</form>

				<hr>

				<?php



				$stmt = $db->query("SELECT * FROM hrlv order by sn");
				if ($stmt->rowCount() > 0) { ?>

					<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9">
						<thead>
							<tr>
								<th data-toggle="true">SI.No</th>
								<th data-toggle="true">Leave Category</th>
								<th data-toggle="true">Days</th>
								<th data-toggle="true">Approver I</th>
								<th data-toggle="true">Approver II</th>
								<th data-toggle="true">Apply Type</th>
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
									<td><?php echo $row['leave_type']; ?></td>
									<td><?php echo $row['days']; ?></td>
									<td><?php echo $row['approver1']; ?></td>
									<td><?php echo $row['approver2']; ?></td>
									<td><?php echo $row['apply_type']; ?></td>
									<td><a href="index.php?<?php echo 'LV=' . $row['sn']; ?>"> [ Edit ] </a>
										&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
										<a href="index.php?LV&<?php echo 'dl=' . $row['sn']; ?>"> [ Delete ] </a>


									</td>
								</tr>
							<?php
								$colordecide++;
								$n++;
							} ?>

						</tbody>

					</table>
				<?php } else {
					echo 'No Records Found';
				}

				?>

			</div>
		</div>
	</div>
</div>