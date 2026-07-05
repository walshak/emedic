<?php

if ($_SESSION['section'] == 'Laboratory') {
	$section = "section='Laboratory' and ";
} elseif ($_SESSION['section'] == 'Laboratory') {
	$section = "section='Laboratory' and ";
} else {
	$section = '';
}


$setdate = date("Y-m-d");
$yr = date("Y");
$mth = date("m");
$queue = $db->query("SELECT l.labrequest_no 
	FROM lab_manage l 
	INNER JOIN patient_ap_services p 
	ON p.drug_sn=l.labrequest_no WHERE $section data_capture_status='queue' and p.paystatus=1");
?>




<div class="row">
	<div class="col-lg-3">


		<div class="row">

			<div class="col-lg-12">

				<div class="ibox float-e-margins">
					<div class="ibox-title">
						<span class="label label-info pull-right"></span>
						<h5>Write Off Bill: Current Month: <?= date('M');  ?></h5>
					</div>
					<div class="ibox-content">
						<h1 class="no-margins"><?php


												$current_month = date('m'); // Extract current month
												$current_year = date('Y');  // Extract current year

												$stmtx = $db->prepare("
									   SELECT 
										   SUM(CASE WHEN DATE_FORMAT(i.issued_date, '%m') = :current_month THEN i.amount ELSE 0 END) as Total_amt_month,
										   SUM(i.amount) as Total_amt_year
									   FROM vouchers_inventory AS i 
									   INNER JOIN vouchers AS v ON i.batch_code = v.batch_code 
									   WHERE v.created_by = :created_by 
										 AND DATE_FORMAT(i.issued_date, '%Y') = :current_year
								   ");


												$stmtx->bindParam(':created_by', $_SESSION['fullname'], PDO::PARAM_STR);
												$stmtx->bindParam(':current_month', $current_month, PDO::PARAM_STR);
												$stmtx->bindParam(':current_year', $current_year, PDO::PARAM_STR);
												$stmtx->execute();

												if ($stmtx->rowCount() > 0) {
													$result = $stmtx->fetch(PDO::FETCH_ASSOC);
													echo 'N' . number_format($result['Total_amt_month']) . "<br>";
												} else {
													echo "No records found.";
												}


												?></h1>
						<H3><?php echo "Total current year: " . number_format($result['Total_amt_year']);
							?></H3>
						<?php if ($stmtx->rowCount() > 0) {
						?>
							<a href="../profile/vchr.php" style="font-size: 14px;">Click for More Information!</a>
						<?php }
						?>
					</div>
				</div>

				<?php
				$fullname = $_SESSION['fullname'];
				$section = $_SESSION['section'];
				$setdate = date('Y-m-d');
				$mth = date('m');

				$section_filter = '';
				$bind_section = false;

				if ($section == 'Laboratory' || $section == 'Radiology') {
					$section_filter = "section = :section AND ";
					$bind_section = true;
				}

				// Count: Approved Today
				$stmt1 = $db->prepare("SELECT COUNT(*) FROM lab_manage 
    WHERE {$section_filter} data_capture_status = 'approve' 
    AND DATE(result_date) = :setdate");
				if ($bind_section) $stmt1->bindParam(':section', $section, PDO::PARAM_STR);
				$stmt1->bindParam(':setdate', $setdate, PDO::PARAM_STR);
				$stmt1->execute();
				$approve_today = $stmt1->fetchColumn();

				// Count: Awaiting Approval (pending statuses)
				$stmt2 = $db->prepare("SELECT COUNT(*) FROM lab_manage 
    WHERE {$section_filter} data_capture_status IN ('specimen','capture','result')");
				if ($bind_section) $stmt2->bindParam(':section', $section, PDO::PARAM_STR);
				$stmt2->execute();
				$awaiting_approval = $stmt2->fetchColumn();

				// Count: My Approvals this Month
				$stmt3 = $db->prepare("SELECT COUNT(*) FROM lab_manage 
    WHERE section = :section AND data_capture_status = 'approve' 
    AND MONTH(result_date) = :mth AND approved_by = :fullname");
				$stmt3->bindParam(':section', $section, PDO::PARAM_STR);
				$stmt3->bindParam(':mth', $mth, PDO::PARAM_INT);
				$stmt3->bindParam(':fullname', $fullname, PDO::PARAM_STR);
				$stmt3->execute();
				$myappr = $stmt3->fetchColumn();

				// Count: My Investigations this Month
				$stmt4 = $db->prepare("SELECT COUNT(*) FROM lab_result 
    WHERE MONTH(result_date) = :mth AND lab_sci_name = :fullname");
				$stmt4->bindParam(':mth', $mth, PDO::PARAM_INT);
				$stmt4->bindParam(':fullname', $fullname, PDO::PARAM_STR);
				$stmt4->execute();
				$myInvsti = $stmt4->fetchColumn();
				?>

				<!-- DASHBOARD DISPLAY -->
				<div class="ibox float-e-margins">
					<div class="ibox-title">
						<h5>Awaits Approval</h5>
					</div>
					<div class="ibox-content">
						<h1 class="no-margins"><?php echo $awaiting_approval; ?></h1>
						<small>Pending Requests</small>
					</div>
				</div>

				<div class="ibox float-e-margins">
					<div class="ibox-title">
						<h5>Results Approved</h5>
					</div>
					<div class="ibox-content">
						<h1 class="no-margins"><?php echo $approve_today; ?></h1>
						<small>Total Today</small>
					</div>
				</div>

				<div class="ibox float-e-margins">
					<div class="ibox-title">
						<h5>My Approval(s)</h5>
					</div>
					<div class="ibox-content">
						<h1 class="no-margins"><?php echo $myappr; ?></h1>
						<small><?php echo date("M, y"); ?></small>
					</div>
				</div>

				<div class="ibox float-e-margins">
					<div class="ibox-title">
						<h5>Completed Requests</h5>
					</div>
					<div class="ibox-content">
						<h1 class="no-margins"><?php echo $myInvsti; ?></h1>
						<small><?php echo date("M, y"); ?></small>
					</div>
				</div>

				<?php //} 
				?>








			</div>
		</div>
	</div>




	<div class="col-lg-9">


		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<span class="label label-info pull-right"></span>
				<h5>Navigation</h5>
			</div>

			<div class="ibox-content">
				<div class="row" align="center">
					<?php
					if (in_array(strtoupper($_SESSION['rights']), ['RE', 'MD', 'GM', 'US', 'AC'])) {
					?>
						<a class="btn btn-app add_new_sale" data-toggle="modal" data-target="#myModal5"><i class="fa fa-plus-square"></i>Add New Patient</a>
						<a href="mgt.php" class="btn btn-app"><i class="fa fa-ticket" style="color:brown; "></i>Investigation</a>
						<a href="xsale.php" class="btn btn-app"><i class="fa fa-database"></i>Add Request</a>
						<a href="referral.php" class="btn btn-app"><i class="fa fa-recycle"></i>Add Referrals</a>

						<?php if ($_SESSION['bill'] == '1') { ?>
							<a href="../billing/index.php?invest" class="btn btn-app"><i class="fa fa-money"></i>>Biller</a>
						<?php } ?>

					<?php } else { ?>
						<a href="mgt.php" class="btn btn-app"><i class="fa fa-user"></i>Investigations</a>
						<a href="mgt_rpt.php" class="btn btn-app"><i class="fa fa-archive"></i>Report</a>
						<a href="../profile/invsti_rq.php" class="btn btn-app"><i class="fa fa-database"></i>Requisition</a>
					<?php } ?>


				</div>
			</div>
		</div>








		<div class="row">






			<?php
			if ($_SESSION['section'] == "") {
				$lab_mgt_where = "(section='Radiology' or section='Laboratory') ";
			} else {
				$category = $_SESSION['section'];
				$lab_mgt_where = "section='$category'";
			}
			?>



			<div class="col-lg-12">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Investigation enquiry</h5>
								<div class="ibox-tools">
									<a href="index.php" class="btn btn-white btn-xs"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Refresh to Current List</a>

									<a class="collapse-link">
										<i class="fa fa-chevron-up"></i>
									</a>
									<a class="close-link">
										<i class="fa fa-times"></i>
									</a>
								</div>
							</div>
							<div class="ibox-content">

								<div class="row">
									<div class="col-lg-12">
										<form action="index.php" method="post">
											<table>
												<tr>
													<td>
														<label for="reg_input_no">Search By Patient Name or Hospital No.</label>
														<select name="patient" class="chosen-select" style="width:350px;">
															<option selected value="">Search and Select Patient</option>
															<?php
															$setdate = date("Y-m-d");
															$category = $_SESSION['section'];
															$lab_mgt_where = ($category == "") ? "(section='Radiology' or section='Laboratory')" : "section='$category'";

															// Limit patient options for speed
															$stmt = $db->query("SELECT patient, patient_name FROM lab_manage WHERE $lab_mgt_where GROUP BY patient ORDER BY sn DESC LIMIT 1000");
															foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
																echo '<option value="' . $row["patient"] . '">' . $row["patient"] . ' ' . $row["patient_name"] . '</option>';
															}
															?>
														</select>
													</td>
													<td>
														<label>.</label><br>
														<button class="btn btn-primary btn-sm" type="submit" name="apply_requests">Apply</button>
													</td>
												</tr>
											</table>
										</form>
										<br>

										<?php
										if (isset($_POST['apply_requests']) && !empty($_POST['patient'])) {
											$hosp_no = $_POST['patient'];
											$add_on = " AND patient = '$hosp_no'";
										} else {
											$add_on = " AND DATE(request_date) = '$setdate'";
										}

										// Pull request records
										$stmt = $db->query("SELECT data_capture_status, request_date, labrequest_no, test_name, patient_name, patient, request_by 
		FROM lab_manage WHERE $lab_mgt_where $add_on ORDER BY sn DESC LIMIT 50");

										if ($stmt->rowCount() > 0) {
											$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
										?>
											<table class="table table-striped table-bordered table-hover dataTables-example">
												<thead>
													<tr>
														<th>Status</th>
														<th>Date RQ.</th>
														<th>Investigation</th>
														<th>Patient #</th>
														<th>Name</th>
														<th>Requester</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($data as $row) {
														switch ($row['data_capture_status']) {
															case 'specimen':
																$status = 5;
																break;
															case 'capture':
																$status = 6;
																break;
															case 'result':
																$status = 7;
																break;
															case 'approve':
																$status = 2;
																break;
															case 'cancel':
																$status = 3;
																break;
															case 'reject':
															case 'delete':
																$status = 4;
																break;
															default:
																$status = 1;
																break;
														}
													?>
														<tr>
															<td>
																<?php
																if ($status == 4) echo '<span class="label label-warning">Canceled</span>';
																elseif ($status == 3) echo '<span class="label label-warning">Reject</span>';
																elseif ($status == 2) echo '<span class="label label-primary">Completed</span>';
																elseif ($status == 5) echo '<span class="label label-primary">Specimen Taken</span>';
																elseif ($status == 6) echo '<span class="label label-primary">Capture Taken</span>';
																elseif ($status == 7) echo '<span class="label label-primary">Result Taken</span>';
																else echo '<small>Pending...</small>';
																?>
															</td>
															<td><i class="fa fa-clock-o"></i>&nbsp; <?php echo date('d M y', strtotime($row['request_date'])); ?></td>
															<td><?php echo htmlspecialchars($row['test_name']); ?></td>
															<td><?php echo htmlspecialchars($row['patient']); ?></td>
															<td><?php
																$part = explode(" ", $row['patient_name']);
																echo htmlspecialchars($part[0] . ' ' . (isset($part[1]) ? substr($part[1], 0, 1) . '.' : ''));
																?>
															</td>
															<td><?php
																if (!empty($row['request_by'])) {
																	$req = explode(" ", $row['request_by']);
																	echo htmlspecialchars($req[0] . ' ' . (isset($req[1]) ? substr($req[1], 0, 1) . '.' : ''));
																}
																?>
															</td>
														</tr>
													<?php } ?>
												</tbody>
											</table>
										<?php } else {
											echo '<br>No Requests Available!';
										} ?>
									</div>


								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>





















<?php

//// chech maintenance status

//$todays_date=date("Y-m-d");
//$stmt_chk=$db->query("SELECT task_name FROM invsti_dailyCheck WHERE date(date_check)='$todays_date' and task_name='maintenance'");						 				
//if($stmt_chk->rowCount()==0){

/*

$stmt = $db->query("SELECT * FROM invsti_machine WHERE due_status='0'");
if ($stmt->rowCount() > 0) {
	while ($rowx = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$s_type = $rowx['service_every_type'];
		$next_due_date = $rowx['next_due_date'];
		$investi_count = $rowx['investigation_count'];
		$s_count = $rowx['service_every_count'];
		$device_no = $rowx['sn'];

		if ($s_type == 'Days' and $next_due_date < date("Y-m-d")) {
			$update = "UPDATE invsti_machine SET due_status='1' WHERE sn='$device_no'";
			$db->exec($update);
		}

		if ($s_type == 'Investigation') {
			//---------------------------------------------------------------------------------------------
			$Cur_date = date("Y-m-d");
			$stmt = $db->query("SELECT * FROM invsti_machine_settings WHERE device_no='$device_no'");
			if ($stmt->rowCount() > 0) {
				$total = 0;
				$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($data as $key => $row) {

					//while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
					$investigation_name = $row['investigation_name'];

					/// get total lab manage table for approve, result, rejected
					$stmt3 = $db->query("SELECT sn FROM lab_manage WHERE maintenance_lock='0' and test_name='$investigation_name' and date(request_date)='$Cur_date' and (data_capture_status='result' or data_capture_status='reject' or data_capture_status='approve')");
					if ($stmt3->rowCount() > 0) {
						$total = $total + $stmt3->rowCount();
						while ($rowxx = $stmt3->fetch(PDO::FETCH_ASSOC)) {
							$sn = $rowxx['sn'];
							$update = "UPDATE lab_manage SET maintenance_lock='1' WHERE sn='$sn'";
							$db->exec($update);
						}
					}
				}
				/// end of looopiiiingggggggggggggggggggggggggggg
				// calculate and decide here
				$TOTAL_invsti = $investi_count + $total;
				if ($TOTAL_invsti >= $s_count) {
					$update = "UPDATE invsti_machine SET due_status='1', investigation_count='$TOTAL_invsti' WHERE sn='$device_no'";
					$db->exec($update);
				} else {
					$update = "UPDATE invsti_machine SET investigation_count='$TOTAL_invsti' WHERE sn='$device_no'";
					$db->exec($update);
				}
			}
			///============================================================================================															

		}
	}
}

*/

//	$update="UPDATE invsti_dailyCheck SET date_check='$todays_date'";
//	$db->exec($update);

//}



//============================================================


?>

<?php include("investigations/search_modal.php") ?>