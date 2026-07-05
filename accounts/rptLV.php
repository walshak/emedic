<?php

$setdate = date("Y-m-d H:i:s");

// new code
if (isset($_POST['approve_leave_request']) or isset($_POST['reject_leave_request'])) {

	var_dump($_POST);
	die();

	if (isset($_POST['approve_leave_request'])) {
		$sn = $_POST['request_id'];
		$status = 'approve';
	}

	if (isset($_POST['reject_leave_request'])) {
		$sn = $_POST['request_id'];
		$status = 'reject';
	}
	if (isset($_POST['remarks'])) {
		$remarks = $_POST['remarks'];
	} else {
		$remarks = '';
	}

	$stmt = $db->query("Select approver1,approver2,type_leave,status,dept_id from hrlvapply where sn='$sn'");
	$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
	$type_leave = $rowx['type_leave'];
	$save_status = $rowx['status'];
	$approver1_save = $rowx['approver1'];
	$emp_dept_id = $rowx['dept_id'];

	/*	$stt = $db->query("SELECT approver1,approver2 FROM hrlv where leave_type='$type_leave'");
	if ($stt->rowCount() > 0) {
		$rowxx = $stt->fetch(PDO::FETCH_ASSOC);
		$approver1_sign = $rowxx['approver1'];
		$approver2_sign = $rowxx['approver2'];
	}
*/

	$Designation = $_SESSION['Designation'];
	if ($_SESSION['unit_head'] == '1') {
		$is_HOD = 'HOD';
	} else {
		$is_HOD = $_SESSION['Designation'];
	}

	$stmt22 = $db->query("SELECT * FROM hrlv where leave_type='$type_leave'");
	$row22 = $stmt22->fetch(PDO::FETCH_ASSOC); /// approver one
	$approver1_head = $row22['approver1'];
	$approver2_head = $row22['approver2'];


	if ($approver1_save == '') {
		$HiddenProducts = explode(',', $approver1_head);

		if (in_array($is_HOD, $HiddenProducts)) {
			if ($is_HOD == 'HOD' and $_SESSION['dept_id'] == $emp_dept_id) {
				$app_1_lock = 0;
				$app_2_lock = 1; ////  echo "Available";
			} else {
				$app_1_lock = 0;
				$app_2_lock = 1; ////  echo "Available";
			}
		} else {
			$app_1_lock = 1;
			$app_2_lock = 1;
		}
	} else {

		$HiddenProducts = explode(',', $approver2_head);
		if (in_array($is_HOD, $HiddenProducts)) {
			if ($approver1_save != '') {
				$app_1_lock = 1;
				$app_2_lock = 0;
			} else {
				$app_1_lock = 1;
				$app_2_lock = 1;
			}
		} else {
			$app_1_lock = 1;
			$app_2_lock = 1;
		}
	}

	echo $sign_me = $app_1_lock . $app_2_lock;
	exit;



	if ($sign_me == '01') {

		$approver1 = $_SESSION['fullname'];

		if (isset($_POST['reject_leave_request'])) {
			$status = 'reject';
			$approver1 = $approver1 . '<br><strong style="color:#F00">Rejected</strong>';
		} else {
			$status = 'pending';
			$approver1 = $approver1 . '<br>Approved';
		}
		$stmt2 = sprintf("UPDATE hrlvapply SET status='$status', approver1='$approver1',approver1_date='$setdate',remarks='$remarks' WHERE sn='$sn'");
		$db->exec($stmt2);
		$sv = 1;
		//} elseif ($_SESSION['Designation'] == $approver2_sign and $approver1_save == '') {
		//	$err_title = 'Unable to perform task ... first approver must sign before you continue';
	} elseif ($sign_me == '10') {

		$approver2 = $_SESSION['fullname'];

		if (isset($_POST['reject_leave_request'])) {
			$status = 'reject';
			$approver2 = $approver2 . '<br><strong style="color:#F00">Rejected</strong>';
		} else {
			$status = 'Approve';
			$approver2 = $approver2 . '<br>Approved';
		}
		// new code
		$stmt2 = sprintf("UPDATE hrlvapply SET status='$status', approver2='$approver2',approver2_date='$setdate', remarks2 = '$remarks' WHERE sn='$sn'");
		$db->exec($stmt2);
		$sv = 1;
	} else {
		$err_title = 'Unable to perform task or first approver must sign before you continue';
		//$err_title = 'Unable to perform task';
	}
}


if (isset($_POST["add_leave"])) {

	$leave_type = $_POST['leave_type'];
	$part = explode("__", $leave_type);

	$req_days = $part[1];
	$leave_type = $part[0];
	$apply_type = $part[2];

	$EmployeeCode = $_POST['EmployeeCode'];
	$t_days = $_POST['t_days'];

	/// check if 
	$error = 0;
	if ($t_days <= $req_days) {

		if ($apply_type == 'Yealy') {
			$yr = date("Y");
			$stmt = $db->query("Select * from hrlvapply where year='$yr' and type_leave='$leave_type' and status='finish' and ECode='$EmployeeCode'");
			if ($stmt->rowCount() == 0) {
				$error = '0';
			} else {
				$error = '1';
			}
		} elseif ($apply_type == 'Monthly') {
			$month = date("m");

			$stmt = $db->query("Select * from hrlvapply where month='$month' and type_leave='$leave_type' and status='finish' and ECode='$EmployeeCode'");
			if ($stmt->rowCount() == 0) {
				$error = '0';
			} else {
				$error = '1';
			}
		} elseif ($apply_type == 'Any Time') {
			//$sub=Add_leave($leave_type);
			$error = '0';
		}


		//// TOTAL DAYS REQUIRED
	} else {
		$err_title = '<strong>Leave Days specified is more than required days</strong>';
	}

	if ($error == 1) {
		$err_title = 'Duplicate Leave Request Found... This request has been approved or request before.';
	} else {
		$stmt = $db->query("Select * from hrlvapply where status='pending' and ECode='$EmployeeCode'");
		if ($stmt->rowCount() == 0) {
			$sub = Add_leave($leave_type);
		} else {
			$err_title = 'Pending Leave Request Exist!';
		}
	}
}


function Add_leave($leave_type)
{
	include("../Connections/Conn.php");

	$setdate = date("Y-m-d H:i:s");
	$m = date("m");
	$yr = date("Y");

	$update = sprintf(
		"INSERT INTO hrlvapply(ECode,date_apply,starting_date,month,year,reason,type_leave,days,status) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
		GetSQLValueString($_POST["EmployeeCode"], "text"),
		GetSQLValueString($setdate, "text"),
		GetSQLValueString($_POST["start"], "text"),
		GetSQLValueString($m, "text"),
		GetSQLValueString($yr, "text"),
		GetSQLValueString($_POST["reason"], "text"),
		GetSQLValueString($leave_type, "text"),
		GetSQLValueString($_POST["t_days"], "text"),
		GetSQLValueString('pending', "text")
	);
	$db->exec($update);

	//header("location:index.php?yLV");	


}
?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Staff Leave Requests and Approval List</h5>
			</div>

			<div class="ibox-content">

				<?php if ($err_title != '') { ?><strong style="color:#F00"><?php echo $err_title;  ?></strong>
					<hr><?php } ?>
				<!-- new code -->
				<form action="index.php?rLV" method="POST">

					<div class="row">

						<div class="col-lg-4">
							<div class="form_sep">
								<label for="reg_select" class="">Select Task</label>
								<select name="leave_type" id="leave_type" class="form-control">
									<option selected="selected" value="">Select ...</option>

									<option value="pending">Requested</option>
									<option value="approve">Approved</option>
									<option value="reject">Rejected</option>
									<option value="finished">Finished</option>
								</select>
							</div>
						</div>


						<div class="col-lg-4">

							<div id="form_sep">
								<label for="reg_input_no" class="">Search Employee</label>
								<select name="EmpCode" class="input-sm chosen-select" style="width:350px;">
									<option selected="selected" value="">Search and Select Staff</option>
									<?php $stmt = $db->query("SELECT emp.EmployeeCode,emp.FirstName,emp.LastName FROM hremp as emp inner join hrlvapply as app on emp.EmployeeCode=app.ECode WHERE emp.status=0 order by emp.FirstName");
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
										<option value="<?php echo $row["EmployeeCode"]; ?>"><?php echo $row["EmployeeCode"] . ' ' . $row["FirstName"]  . ', ' . $row["LastName"]; ?></option>
									<?php } ?>
								</select>

							</div>


						</div>



						<div class="col-lg-4">
							<div class="form_sep">
								<label for="reg_input_no" class="">.</label><br>
								<button class="btn btn-info btn btn-sm" type="submit" name="apply_apply" id="apply_apply">Apply</button>
							</div>
						</div>
					</div>






				</form>
			</div>

		</div>
	</div>


	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Leave/Approval Table</h5>
			</div>

			<div class="ibox-content">

				<?php
				$Designation = $_SESSION['Designation'];

				if (isset($_POST['apply_apply'])) {
					if (isset($_POST['leave_type']) and $_POST['leave_type'] != '') {
						$leave_type = $_POST['leave_type'];
						$stmt = $db->query("SELECT * FROM hrlvapply where status='$leave_type' order by sn desc");
					} elseif (isset($_POST['EmpCode']) and $_POST['EmpCode'] != '') {
						$EmpCode = $_POST['EmpCode'];
						$stmt = $db->query("SELECT * FROM hrlvapply where ECode='$EmpCode' order by sn desc");
					}
				} else {
					$stmt = $db->query("SELECT * FROM hrlvapply where status='pending' order by sn desc");
				}

				if ($stmt->rowCount() > 0) { ?>

					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>

								<th>Name</th>
								<th>Date/Applied</th>
								<th>Date/Starts</th>
								<th>Leave Type</th>
								<th>Reason</th>
								<th>Resumption Date</th>
								<th>Approver I:</th>
								<th>Approver II:</th>
								<th>Status</th>
								<th></th>
							</tr>
						</thead>
						<tbody>

							<?php
							$n = 1;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

								///echo $row['sn']; 
							?>

								<tr>
									<td><?php echo $row['ECode'] . ':<br> ' . $row['sn']; ?></td>
									<td><?php echo date('d M Y', strtotime($row['date_apply'])); ?></td>
									<td><?php echo date('d M Y', strtotime($row['starting_date'])); ?></td>
									<td><?php echo $row['type_leave']; ?></td>
									<td><?php echo $row['reason']; ?></td>
									<td><?php

										$d = $row['days'];
										$ddate = $row['starting_date'];
										echo $resumpdate2 = date('d M Y', strtotime($ddate . '+' . $d . ' days'));
										$ddate = strtotime($row['starting_date']);


										if ($row['status'] == 'Approve') {
											///print_r("hello");
											date_default_timezone_set('Africa/Lagos');
											$today = date("U");
											if ($ddate > $today) {
												$main_date = $ddate;
											} else {
												$main_date = $today;
											}
											$date1 = date_create($main_date);
											$date2 = date_create($resumpdate2);
											$diff = date_diff($date1, $date2);
											echo '<br>Days Remaining: ' . $days = $diff->format("%a");

											if ($days <= 0) {
												$sn = $row['sn'];
												$stmt2 = sprintf("UPDATE hrlvapply SET status='finish' WHERE sn='$sn'");
												$db->exec($stmt2);
											}
										}

										?>
									</td>
									<td><?php if ($row['approver1'] != '') {
											echo $row['approver1'] . '<br>' . date('d M Y', strtotime($row['approver1_date']));
											echo "<hr>";
											echo $row['remarks'];
										}
										?>
									</td>
									<td><?php if ($row['approver2'] != '') {
											echo $row['approver2'] . '<br>' . date('d M Y', strtotime($row['approver2_date']));
											echo "<hr>";
											echo $row['remarks2'];
										}
										?>
									</td>

									<td>
										<?php
										if ($row['status'] == 'Approve') {
											echo "Approved";
										} elseif ($row['status'] == 'reject') {
											echo "Rejected";
										} else {
										}
										// echo $row['status']; 
										?>
									</td>
									<td>
										<?php if ($row['status'] == 'finish') { ?>
											<strong>Finished</strong>
										<?php } else { ?>

											<div class="btn-group">
												<button data-toggle="dropdown" class="btn btn-danger btn-xs dropdown-toggle">Action <span class="caret"></span></button>
												<ul class="dropdown-menu">
													<?php if ($row['status'] == 'pending' or $row['status'] == 'reject') { ?>
														<!-- new code -->
														<li> <a onclick="show_approve_leave_modal(<?php echo $row['sn']; ?>)">Approve</a> </li>
														<!-- end new code -->

													<?php } ?>
													<!-- new code  -->
													<li> <a onclick="show_reject_leave_modal(<?php echo $row['sn']; ?>)">Reject</a> </li>
													<!-- end new code -->


												</ul>
											</div>

										<?php } ?>
									</td>
								</tr>
							<?php } ?>

						</tbody>
					</table>
				<?php } else {
					echo '<strong>No Records Found </strong>';
				}

				?>

			</div>
		</div>
	</div>
	<!-- new code -->
	<div class="modal inmodal fade" id="reject_leave_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Reject leave request</h4>
				</div>

				<div class="modal-body">
					<p id="reject_request_loader">Loading...</p>
					<form action="" method="POST" id="reject_leave_form" style="display: none;">
						<div class="form_sep">
							<label for="">
								Enter remarks
							</label>
							<textarea name="remarks" id="" cols="25" rows="5" class="form-control" required></textarea>
						</div>
						<div class="form_sep">
							<label for="">First approver remarks</label>
							<p id="reject_remarks1">N/A</p>
						</div>
						<div class="form_sep">
							<input type="submit" class="btn btn-danger" value="Reject" name="reject_leave_request">
						</div>
						<input type="hidden" id="the_reject_request_id" name="request_id">
						<input type="hidden" id="the_reject_remark_no" name="remark_no" value="1">
					</form>

				</div>
			</div>
		</div>
	</div>

	<div class="modal inmodal fade" id="approve_leave_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Approve leave request</h4>
				</div>

				<div class="modal-body">
					<p id="approve_request_loader">Loading...</p>
					<form action="" method="POST" id="approve_leave_form" style="display: none;">
						<div class="form_sep">
							<label for="">
								Enter remarks
							</label>
							<textarea name="remarks" id="" cols="25" rows="5" class="form-control" required></textarea>
						</div>
						<div class="form_sep">
							<label for="">First approver remarks</label>
							<p id="approve_remarks1">N/A</p>
						</div>
						<div class="form_sep">

							<input type="submit" class="btn btn-success" value="Approve" name="approve_leave_request">
						</div>
						<input type="hidden" id="the_approve_request_id" name="request_id">
						<input type="hidden" id="the_approve_remark_no" name="remark_no" value="1">
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- /end new code -->
</div>