<?php

$setdate = date("Y-m-d H:i:s");

if (isset($_GET["del"])) {
    $del = $_GET['del'];
    
    // Prepare the DELETE statement
    $stmt = $db->prepare("DELETE FROM hrlvapply WHERE sn = :sn");
    
    // Bind the parameter
    $stmt->bindParam(':sn', $del, PDO::PARAM_STR);
    
    // Execute the statement
    $stmt->execute();
    
    $sv = 1; // Set a success variable if needed
}


if (isset($_POST["add_leave"])) {

	$leave_type = $_POST['leave_type'];
	$part = explode("__", $leave_type);

	$req_days = $part[1];
	$leave_type = $part[0];
	$apply_type = $part[2];

	$EmployeeCode = $_POST['EmployeeCode'];
	// new code
	$part = explode("__", $EmployeeCode);
	$EmployeeCode = $part[0];
	// end new code
	$t_days = $_POST['t_days'];

	/// check if 
	$error = 0;
	if ($t_days <= $req_days) {

		if ($apply_type == 'Yealy') {
			$yr = date("Y");
			$stmt = $db->query("Select * from hrlvapply where year='$yr' and type_leave='$leave_type' and status='finish' and ECode='$EmployeeCode'");
			// new code
			$stmt2 = $db->query("Select * from hrlvapply where year='$yr' and type_leave='$leave_type' and status='pending' and ECode='$EmployeeCode'");
			// end new code
			if ($stmt->rowCount() == 0) {
				$error = '0';
			} else {
				$error = '1';
			}
			// new code
			if ($stmt2->rowCount() == 0) {
				$error = 0;
			} else {
				$error = 2;
			}
			// end new code
		} elseif ($apply_type == 'Monthly') {
			$month = date("m");

			$stmt = $db->query("Select * from hrlvapply where month='$month' and type_leave='$leave_type' and status='finish' and ECode='$EmployeeCode'");
			// new code
			$stmt2 = $db->query("Select * from hrlvapply where month='$month' and type_leave='$leave_type' and status='pending' and ECode='$EmployeeCode'");
			// end new code
			if ($stmt->rowCount() == 0) {
				$error = '0';
			} else {
				$error = '1';
			}
			// new code
			if ($stmt2->rowCount() == 0) {
				$error = 0;
			} else {
				$error = 2;
			}
			// end new code
		} elseif ($apply_type == 'Any Time') {
			$error = '0';
		}


		//// TOTAL DAYS REQUIRED
	} else {
		$err_title = '<strong>Leave Days specified is more than required days</strong>';
	}

	if ($error == 1) {
		$err_title = 'Duplicate Leave Request Found... This request has been approved or request before.';
	} elseif ($error == 2) { //new code
		$err_title = 'Pending Leave Request Exist!';
		// end new code
	} else {
		Add_leave($leave_type);
	}
}


function Add_leave($leave_type)
{
	include("../Connections/Conn.php");

	$setdate = date("Y-m-d H:i:s");
	$m = date("m");
	$yr = date("Y");
	$Ecode_name = $_POST["EmployeeCode"];
	$part = explode("__", $Ecode_name);
	$Ecode = $part[0];
	$name = $part[1];

	$stmt = $db->prepare("INSERT INTO hrlvapply (ECode, Name, date_apply, starting_date, month, year, reason, type_leave, days, status)
                      VALUES (:ECode, :Name, :date_apply, :starting_date, :month, :year, :reason, :type_leave, :days, :status)");

$stmt->bindParam(':ECode', $Ecode, PDO::PARAM_STR);
$stmt->bindParam(':Name', $name, PDO::PARAM_STR);
$stmt->bindParam(':date_apply', $setdate, PDO::PARAM_STR);
$stmt->bindParam(':starting_date', $_POST["start"], PDO::PARAM_STR);
$stmt->bindParam(':month', $m, PDO::PARAM_STR);
$stmt->bindParam(':year', $yr, PDO::PARAM_STR);
$stmt->bindParam(':reason', $_POST["reason"], PDO::PARAM_STR);
$stmt->bindParam(':type_leave', $leave_type, PDO::PARAM_STR);
$stmt->bindParam(':days', $_POST["t_days"], PDO::PARAM_STR);
$stmt->bindValue(':status', 'pending', PDO::PARAM_STR);

$stmt->execute();



	global $sv;
	return $sv = 1;
	// end new code
}
?>


<div class="row">
	<div class="col-lg-4">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Apply Here</h5>
			</div>

			<div class="ibox-content">

				<?php if ($err_title != '') { ?><strong style="color:#F00"><?php echo $err_title;  ?></strong>
					<hr><?php } ?>

				<form action="index.php?yLV" method="POST">

					<div class="form_sep">

						<label for="reg_input_no" class="req">Search Employee</label>
						<select name="EmployeeCode" class="input-sm chosen-select" style="width:350px;" id="employ_code" onchange="do_leave_calculation(this.value)" required>
							<option selected="selected" value="">Search and Select Staff</option>
							<?php $stmt = $db->query("SELECT EmployeeCode, FirstName, LastName FROM hremp WHERE status=0 order by FirstName");


							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $row["EmployeeCode"] . '__' . $row["FirstName"]  . ', ' . $row["LastName"]; ?>"><?php echo $row["EmployeeCode"] . ' ' . $row["FirstName"]  . ', ' . $row["LastName"]; ?></option>
							<?php } ?>
						</select>

					</div>

					<div class="form_sep">
						<label for="reg_select" class="req">Leave Type</label>
						<select name="leave_type" id="leave_type" class="form-control" required style="display: none;" onchange="do_leave_calculation()">
							<option selected="selected" value="">Select ...</option>

							<?php
							$stmt_lv = $db->query("SELECT * FROM hrlv");
							while ($rwx = $stmt_lv->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $rwx["leave_type"] . '__' . $rwx["days"] . '__' . $rwx["apply_type"]; ?>"><?php echo $rwx["leave_type"] . '/ Days: ' . $rwx["days"]; ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form_sep">
						<label for="reg_textarea_message" class="req">Reason for Application</label>
						<textarea name="reason" id="reason" cols="30" rows="14" class="form-control" required>
</textarea>
					</div>


					<div class="form_sep" id="data_1">
						<label for="reg_input_no" class="req"> Starting Date</label>
						<div class="input-group date">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" class="form-control" name="start" id="start_date" onchange="do_leave_calculation()" required>
						</div>
					</div>


					<div class="form_sep">
						<label for="reg_input_no" class="req">Total Days</label>&nbsp; &nbsp;<span id="days-left"></span>
						<input type="number" id="t_days" name="t_days" min="1" class="form-control" required style="display: none;">
					</div>

					<div class="form_sep">
						<button class="btn btn-info btn btn-sm" type="submit" name="add_leave" id="add_leave" disabled>Submit Request</button>
					</div>
				</form>
				<script>
					function do_leave_calculation(ECode = null) {
						ECode = $('#employ_code').val();
						ECode = ECode.split('__');
						ECode = ECode[0];
						leave_type = $('#leave_type').val();
						console.log(leave_type);
						leave_type = leave_type.split('__');
						leave_type_days = leave_type[1];
						leave_type = leave_type[0];
						console.log(leave_type_days);
						if (ECode != '') {
							// console.log(ECode);
							$('#leave_type').show();
						} else {
							$('#leave_type').hide();
						}
						if (leave_type != '') {
							start_date = $('#start_date').val();
							if (start_date != '') {
								d = new Date(start_date);
								year = d.getFullYear();
								//console.log(year);
								$.ajax({
									url: "fetch_leave_remarks.php",
									method: "POST",
									data: {
										ECode: ECode,
										year: year,
										leave_type: leave_type
									},
									success: function(data) {
										data = JSON.parse(data);
										if (data.length) {
											console.log(data);
											$('#t_days').show();
											$('#add_leave').attr('disabled', false);
											$('#t_days').attr('max', (data[0].days - data[0][20]));
											$('#days-left').text(data[0].days - data[0][20] + " Days available");
										} else {
											$('#t_days').show();
											$('#add_leave').attr('disabled', false);
											$('#t_days').attr('max', leave_type_days);
											$('#days-left').text(leave_type_days + " Days available");
										}
									}
								});
							}

						} else {
							$('#t_days').hide();
							$('#add_leave').attr('disabled', true);
						}
					}
				</script>
			</div>

		</div>
	</div>


	<div class="col-lg-8">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Leave Table</h5>
			</div>

			<div class="ibox-content">

				<?php

				$stmt = $db->query("SELECT * FROM hrlvapply where ECode='$ECode_logged' order by sn");
				if ($stmt->rowCount() > 0) { ?>

					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>Dates</th>
								<th>Details</th>
								<th>Resume Date</th>
								<th>Approvers:</th>
								<th>Remarks</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>

							<?php
							$n = 1;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

								<tr>

									<td><?php echo '<strong>Apply Date: </strong><br>' . date("d M,y", strtotime($row['date_apply'])); ?><br>
										<?php echo '<strong>Start Date: </strong><br>' .  date("d M,y", strtotime($row['starting_date'])); ?></td>
									<td><?php echo '<strong>Reason:</strong><br>' . $row['reason'] . '<br> <strong>Leave Type:</strong><br>' . $row['type_leave'] . '/ days:' . $row['days']; ?></td>
									<td><?php $d = $row['days'];
										echo date("d,M Y", strtotime($row['starting_date'] . " +$d day")); ?></td>
									<td><?php if ($row['approver1'] != '') {
											echo '<strong>Approver I: </strong><br>' . $row['approver1'];
										} ?>
										<?php if ($row['approver2'] != '') {
											echo '<strong>Approver II: </strong><br>' . $row['approver2'];
										} ?></td>
									<td>
										<!-- new code -->
										<?php
										echo $row['remarks'];
										echo "<hr>";
										echo $row['remarks2'];
										?>

									</td>
									<td><?php echo $row['status']; ?> <?php if ($row['status'] == 'pending') { ?>
											<a href="index.php?yLV&del=<?php echo $row['sn']; ?>"> [Delete]</a>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>

						</tbody>
					</table>
				<?php } else {
					echo '<strong>No Leave Records Found </strong>';
				}

				?>

			</div>
		</div>
	</div>

</div>