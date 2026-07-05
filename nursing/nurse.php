<?php

$setdate = date("Y-m-d");
$yr = date("Y");
$mth = date("m");
$seen_today = $db->query("SELECT distinct hospital_no FROM apptm WHERE vital_lock='0' AND status = 'checkin' and date_ap='$setdate'");
?>

<div class="row">
	<div class="col-lg-3">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<span class="label label-success pull-right">Today</span>
				<h5>Vital Queue</h5>
			</div>
			<div class="ibox-content">
				<h1 class="no-margins"><?php echo $seen_today->rowCount(); ?></h1>
				<div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
				<small>Total Patients <a onClick="queue_list()"> <strong>[ See List ]</strong> </a>




				</small>
			</div>
		</div>
	</div>
	<?php
	$dsp_today = $db->query("SELECT hospital_no FROM patient_ap_services WHERE serv_group='consumable' and drug_status='1' and date(date_entry)='$setdate'");
	?>
	<div class="col-lg-3">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<span class="label label-primary pull-right">Today</span>
				<h5>Consumable(s) Delivered</h5>
			</div>
			<div class="ibox-content">
				<h1 class="no-margins"><?php echo $dsp_today->rowCount(); ?></h1>
				<div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
				<small>Total Delivered</small>
			</div>
		</div>
	</div>

	<?php
	/// $exp_today=$db->query("SELECT sn FROM stock_table_inven WHERE expire_date<='$setdate' and stock_table='Nursing Consumable'");						
	$total_stock = $db->query("SELECT distinct stock_sn FROM stock_table_inven WHERE cust_patient_id='$dept_id'");

	?>

	<div class="col-lg-3">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Stocks</h5>
			</div>
			<div class="ibox-content">
				<h1 class="no-margins"><?php echo $total_stock->rowCount(); ?></h1>
				<small>Total Stocks</small>
			</div>
		</div>
	</div>
	<?php
	$cr_today = $db->query("SELECT sum(pay) as amt FROM patient_ap_services WHERE cat_type='Nursing Consumable' and paystatus='0' and  cr='1' and drug_status='1'");
	$rowx = $cr_today->fetch(PDO::FETCH_ASSOC);

	?>

	<div class="col-lg-3">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Stocks Delivered/On-Credits</h5>
			</div>
			<div class="ibox-content">
				<h1 class="no-margins"><?php echo 'N' . number_format($rowx['amt']); ?></h1>
				<div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
				<small>Cash</small>
			</div>
		</div>
	</div>
</div>


<div class="row">


	<div class="col-lg-12">
		<!---------------- ROW RIGHT ---------------->

		<?php

		$setdate = date("Y-m-d");
		$yr = date("Y");
		$mth = date("m");
		$profile = $_SESSION['username'];


		$setdate = date("Y-m-d");
		$cur_date_time = date('Y-m-d H:i:s');

		$pending_adm_request = $db->query("SELECT  e.surname, e.fname, adm.hospital_no, adm.room_bed, adm.room_bed_sn, adm.doc_incharge, adm.date_admit FROM admission as adm inner join enrollee as e on e.hospital_no=adm.hospital_no WHERE adm.adm_status='0' order by adm.hospital_no DESC");
		$no_pending_adm_request = $pending_adm_request->rowCount();

		$pending_adm_discharge_request = $db->query("SELECT * FROM discharge_fellowup order by date_ap DESC");
		$no_pending_adm_discharge_request = $pending_adm_discharge_request->rowCount();

		if (isset($_GET['dischgr'])) {
			$current_tab = 'dischgr';
		} elseif (isset($_GET['adm'])) {
			$current_tab = 'adm';
		} else {
			$current_tab = 'main';
		}
		?>

		<div class="ibox float-e-margins">
			<div class="ibox-content">

				<div class="tabs-container">
					<ul class="nav nav-tabs">


						<li class="<?= ($current_tab == 'main' ? "active" : ""); ?>"><a data-toggle="tab" href="#tab-1" style="font-size: 15px; color: black;"><i class="fa fa-dashboard"></i>Main</a></li>

						<li class="<?= ($current_tab == 'adm' ? "active" : ""); ?>"><a data-toggle="tab" href="#med-hx-tab" style="font-size: 15px; color: black;"><i class="fa fa-bitbucket-square"></i>Patients On-Admission</a></li>

						<li class="<?= ($current_tab == 'admrq' ? "active" : ""); ?>"><a data-toggle="tab" href="#pend-req-tab" style="font-size: 15px; color: black;"><i class="fa fa-bitbucket"></i>Admission Request
								<?php if ($no_pending_adm_request > 0) { ?><span class="badge badge-danger"><?= $no_pending_adm_request; ?></span> <?php } ?></a>
						</li>

						<li class="<?= ($current_tab == 'dischgr' ? "active" : ""); ?>"><a data-toggle="tab" href="#pend-discharge_req-tab" style="font-size: 15px; color: black;"><i class="fa fa-check-square-o"></i>Discharge Request & History
								<?php if ($no_pending_adm_discharge_request > 0) { ?><span class="badge badge-danger"><?= $no_pending_adm_discharge_request; ?></span> <?php } ?></a>
						</li>

						<li class="<?= ($current_tab == 'icu' ? "active" : ""); ?>"><a data-toggle="tab" href="#pend-icu-tab" style="font-size: 15px; color: black;" onClick="patient_on_icu_hdu('icu')">
								<i class="fa fa-check-square-o"></i>ICU Patient(s)</a>
						</li>

						<li class="<?= ($current_tab == 'hdu' ? "active" : ""); ?>"><a data-toggle="tab" href="#pend-hdu-tab" style="font-size: 15px; color: black;" onClick="patient_on_icu_hdu('hdu')">
								<i class="fa fa-check-square-o"></i>HDU Patient(s)</a>
						</li>

					</ul>
					<div class="tab-content">


						<div id="tab-1" class="tab-pane <?= ($current_tab == 'main' ? "active" : ""); ?>">
							<br>
							<br>
							<div class="panel-body">

								<div class="row">
									<div class="col-lg-4 b-r">
										<?php include('../search_patient_code.php'); ?>
									</div>

									<div class="col-lg-8">
										<?php

										$unit_head = $_SESSION['unit_head']; ///.$unit_head;

										if ($unit_head == '1') { ?>
											<a href="index.php?rpt" class="btn btn-app"><i class="fa fa-archive"></i>Report</a>
											<a href="../admin/index.php?stock" class="btn btn-app"><i class="fa fa-folder-open-o"></i>Stock Manager</a>
										<?php } ?>
										<a href="../admin/index.php?sale" class="btn btn-app"><i class="fa fa-shopping-cart"></i>External Sales</a>
									</div>
								</div>


								<hr>
								<div class="row">
									<div class="col-lg-12">
										<div id="_patient_on_queue_table">
											<?php if ($seen_today->rowCount() > 0) { ?>
												<a onClick="queue_list()"> <strong>[ View Queue List ]</strong> </a>
											<?php } ?>
										</div>
									</div>
								</div>

							</div>
						</div>




						<div id="med-hx-tab" class="tab-pane <?= ($current_tab == 'adm' ? "active" : ""); ?>">
							<div class="panel-body">
								<?php
								include_once('_patients_on_admission_list.php');
								?>

							</div>
						</div>


						<div id="pend-req-tab" class="tab-pane <?= ($current_tab == 'admrq' ? "active" : ""); ?>">
							<br>
							<?php
							if ($no_pending_adm_request > 0) {
							?>
								<table class='table table-striped table-bordered table-hover dataTables-example' style="font-size:16px;">
									<thead>
										<tr>
											<th>#</th>
											<th>Hospital No</th>
											<th>Name</th>
											<th>Requested By </th>
											<th>Requested Date </th>
											<th width="20%">.</th>
										</tr>
									</thead>
									<tbody>

										<?php
										$n = 1;
										while ($roww = $pending_adm_request->fetch(PDO::FETCH_ASSOC)) {
										?>
											<tr>
												<td><?php echo $n; ?></td>
												<td><?php echo $roww['hospital_no']; ?></td>
												<td><?php echo $roww['fname'] . ', ' . $roww['surname']; ?></td>
												<td><?php echo $roww['doc_incharge']; ?></td>
												<td>
													<?php
													$added_time = $roww['date_admit'];
													echo date('d M, y h:i:s a', strtotime($added_time)) . ' ';
													$Current_date = date('Y-m-d H:i:s');
													date_default_timezone_set('Africa/Lagos');
													$date1 = new DateTime($Current_date);
													$date2 = new DateTime($added_time);
													$diff = $date2->diff($date1);
													$ddd =	$diff->format('%a');
													///echo '(' . $diff->format('<strong>%a</strong> day/<strong> %h</strong> hr') .')'; 
													?>

												</td>
												<?php if ($ddd >= 1) {
													$hospital_no = $roww['hospital_no'];
													$insertSQL = 'DELETE FROM admission WHERE hospital_no = ?  AND  adm_status = 0 ';
													$sql = $db->prepare($insertSQL);
													$save = $sql->execute(array($hospital_no));
													echo '<td></td>';
												} else { ?>
													<td class="text-center"><a href="patient.php?hosp_no=<?php echo $roww['hospital_no']; ?>&adm_req" class="btn btn-primary">View Patient</a></td>
												<?php }	?>

											</tr>
										<?php
											$n++;
										} ?>

									</tbody>
								</table>
							<?php
							}
							?>
						</div>



						<div id="pend-discharge_req-tab" class="tab-pane <?= ($current_tab == 'dischgr' ? "active" : ""); ?> ">

							<?php
							function h($str)
							{
								return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
							}

							// Fetch distinct values for dropdown filters from admission table
							function getDistinctValues(PDO $db, $column)
							{
								$stmt = $db->prepare("SELECT DISTINCT `$column` FROM admission WHERE `$column` IS NOT NULL AND `$column` != '' ORDER BY `$column` ASC");
								$stmt->execute();
								return $stmt->fetchAll(PDO::FETCH_COLUMN);
							}

							// Fetch distinct departments joined with admission table
							function getDistinctDepartmentsWithAdmissions(PDO $db)
							{
								$stmt = $db->prepare("
        SELECT DISTINCT d.department 
        FROM department d
        INNER JOIN admission a ON d.sn = a.dept_id
        WHERE d.department IS NOT NULL AND d.department != ''
        ORDER BY d.department ASC
    ");
								$stmt->execute();
								return $stmt->fetchAll(PDO::FETCH_COLUMN);
							}

							$docs = getDistinctValues($db, 'doc_incharge');
							$discharge_names = getDistinctValues($db, 'discharge_name');
							$room_beds = getDistinctValues($db, 'room_bed');
							$admit_types = getDistinctValues($db, 'admit_type');
							$discharge_statuses = getDistinctValues($db, 'discharge_status');
							$departments = getDistinctDepartmentsWithAdmissions($db);

							// Initialize variables from form or default values
							$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
							$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';
							$date_filter = isset($_POST['date_filter']) ? $_POST['date_filter'] : ''; // 'date_admit' or 'date_discharge'
							$doc_incharge_filter = isset($_POST['doc_incharge']) ? $_POST['doc_incharge'] : '';
							$discharge_name_filter = isset($_POST['discharge_name']) ? $_POST['discharge_name'] : '';
							$room_bed_filter = isset($_POST['room_bed']) ? $_POST['room_bed'] : '';
							$admit_type_filter = isset($_POST['admit_type']) ? $_POST['admit_type'] : '';
							$discharge_status_filter = isset($_POST['discharge_status']) ? $_POST['discharge_status'] : '';
							$department_filter = isset($_POST['department']) ? $_POST['department'] : '';

							// Validate date_filter input
							$allowed_date_filters = ['date_admit', 'date_discharge'];
							if (!in_array($date_filter, $allowed_date_filters)) {
								$date_filter = '';
							}

							?>
							<form method="post" action="index.php?dischgr">
								<hr>
								<h2 style="color:brown; ">Filter Past Discharge Patient Records</h2>
								<table class="filter-table">
									<tr>
										<td>
											<label for="date_from">Date From:</label><br />
											<input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo h($date_from); ?>" />
										</td>
										<td>
											<label for="date_to">Date To:</label><br />
											<input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo h($date_to); ?>" />
										</td>
										<td>
											<label for="date_filter">Filter By:</label><br />
											<select id="date_filter" name="date_filter" class="form-control">
												<option value="" <?php echo $date_filter === '' ? 'selected' : ''; ?>>-- Select date column --</option>
												<option value="date_admit" <?php echo $date_filter === 'date_admit' ? 'selected' : ''; ?>>Date Admitted</option>
												<option value="date_discharge" <?php echo $date_filter === 'date_discharge' ? 'selected' : ''; ?>>Date Discharged</option>
											</select>
										</td>
										<td>
											<label for="doc_incharge">Doctor In Charge:</label><br />
											<select id="doc_incharge" name="doc_incharge" class="form-control">
												<option value="">-- All --</option>
												<?php foreach ($docs as $doc): ?>
													<option value="<?php echo h($doc); ?>" <?php echo $doc_incharge_filter === $doc ? 'selected' : ''; ?>><?php echo h($doc); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<label for="discharge_name">Discharge Name:</label><br />
											<select id="discharge_name" name="discharge_name" class="form-control">
												<option value="">-- All --</option>
												<?php foreach ($discharge_names as $dname): ?>
													<option value="<?php echo h($dname); ?>" <?php echo $discharge_name_filter === $dname ? 'selected' : ''; ?>><?php echo h($dname); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
									</tr>
									<tr>
										<td>
											<label for="room_bed">Room/Bed:</label><br />
											<select id="room_bed" name="room_bed" class="form-control">
												<option value="">-- All --</option>
												<?php foreach ($room_beds as $room): ?>
													<option value="<?php echo h($room); ?>" <?php echo $room_bed_filter === $room ? 'selected' : ''; ?>><?php echo h($room); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<label for="admit_type">Admit Type:</label><br />
											<select id="admit_type" name="admit_type" class="form-control">
												<option value="">-- All --</option>
												<?php foreach ($admit_types as $atype): ?>
													<option value="<?php echo h($atype); ?>" <?php echo $admit_type_filter === $atype ? 'selected' : ''; ?>>


														<?php

														if ($atype == 'admit_o') {
															echo 'Observation';
														} else {
															echo 'Admitted';
														};

														///echo h($atype); 
														?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<label for="discharge_status">Discharge Status:</label><br />
											<select id="discharge_status" name="discharge_status" class="form-control">
												<option value="">-- All --</option>
												<?php foreach ($discharge_statuses as $status): ?>
													<option value="<?php echo h($status); ?>" <?php echo $discharge_status_filter === $status ? 'selected' : ''; ?>><?php echo h($status); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<label for="department">Department:</label><br />
											<select id="department" name="department" class="form-control">
												<option value="">-- All --</option>
												<?php foreach ($departments as $dept): ?>
													<option value="<?php echo h($dept); ?>" <?php echo $department_filter === $dept ? 'selected' : ''; ?>><?php echo h($dept); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td class="button-cell" style="vertical-align: bottom;">
											<button type="submit" name="filter" class="btn btn-primary">Filter</button>
											&nbsp;
											<a href="index.php?dischgr" class="btn btn-default">Refresh/Close</a>
										</td>
									</tr>
								</table>
							</form>

							<hr>

							<?php if (isset($_POST['filter'])) {

								$sql = "
SELECT 
    a.hospital_no,
    e.surname,
    e.fname,
    e.oname,
    a.discharge_name,
    a.room_bed,
    a.dept_id,
    a.doc_incharge,
    a.date_admit,
    a.date_discharge,
    a.discharge_by_nurse,
    a.discharge_status,
    a.admit_type,
    a.nurse_adm_by,
    d.department,
    nu.fullname AS nurse_fullname,
    (SELECT COUNT(*) FROM admission a2 WHERE a2.hospital_no = a.hospital_no) AS admission_count
FROM 
    admission a
LEFT JOIN 
    enrollee e ON a.hospital_no = e.hospital_no
LEFT JOIN 
    department d ON a.dept_id = d.sn
LEFT JOIN 
    admin_users nu ON a.nurse_adm_by = nu.id
WHERE 
    a.adm_status = 4
";

								// Prepare parameters array for PDO
								$params = [];

								// Append dynamic filters if selected
								if ($date_filter && $date_from && $date_to) {
									$sql .= " AND (a.$date_filter BETWEEN :date_from AND :date_to)";
									$params[':date_from'] = $date_from;
									$params[':date_to'] = $date_to;
								}
								if ($doc_incharge_filter !== '') {
									$sql .= " AND a.doc_incharge = :doc_incharge";
									$params[':doc_incharge'] = $doc_incharge_filter;
								}
								if ($discharge_name_filter !== '') {
									$sql .= " AND a.discharge_name = :discharge_name";
									$params[':discharge_name'] = $discharge_name_filter;
								}
								if ($room_bed_filter !== '') {
									$sql .= " AND a.room_bed = :room_bed";
									$params[':room_bed'] = $room_bed_filter;
								}
								if ($admit_type_filter !== '') {
									$sql .= " AND a.admit_type = :admit_type";
									$params[':admit_type'] = $admit_type_filter;
								}
								if ($discharge_status_filter !== '') {
									$sql .= " AND a.discharge_status = :discharge_status";
									$params[':discharge_status'] = $discharge_status_filter;
								}
								if ($department_filter !== '') {
									$sql .= " AND d.department = :department";
									$params[':department'] = $department_filter;
								}

								$sql .= " ORDER BY a.date_discharge DESC, a.hospital_no ASC";

								$stmt = $db->prepare($sql);
								foreach ($params as $param => $val) {
									$stmt->bindValue($param, $val);
								}
								$stmt->execute();
								$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

								// Initialize summary arrays
								$total_admitted = [];
								$doc_incharge_count = [];
								$nurse_count = [];
								$discharge_status_count = [];
								$admit_type_count = [];

							?>

								<table class='table table-striped table-bordered table-hover dataTables-example' style="font-size:16px;">
									<thead>
										<tr>
											<th>Hospital No</th>
											<th>Full Name</th>
											<th>Room/Bed</th>
											<th>Department</th>
											<th>Doctor In Charge</th>
											<th>Date Admitted</th>
											<th>Date Discharged</th>
											<th>Discharge By Nurse</th>
											<th>Discharge Status</th>
											<th>Admit Type</th>
											<th>Admission Count</th>
											<th>View</th>
										</tr>
									</thead>
									<tbody>
										<?php if (count($results) > 0): ?>
											<?php foreach ($results as $row): ?>
												<tr>
													<td><?php echo h($row['hospital_no']); ?></td>
													<td><?php echo h(trim($row['surname'] . ' ' . $row['fname'] . ' ' . $row['oname'])); ?></td>
													<td><?php echo h($row['room_bed']); ?></td>
													<td><?php echo h($row['department']); ?></td>
													<td><?php echo h($row['doc_incharge']); ?></td>
													<td>
														<?php
														if (!empty($row['date_admit'])) {
															$timestampAdmit = strtotime($row['date_admit']);
															echo $timestampAdmit ? date('d M Y h:i A', $timestampAdmit) : h($row['date_admit']);
														} else {
															echo '';
														}
														?>
													</td>
													<td>
														<?php
														if (!empty($row['date_discharge'])) {
															$timestampDischarge = strtotime($row['date_discharge']);
															echo $timestampDischarge ? date('d M Y h:i A', $timestampDischarge) : h($row['date_discharge']);
														} else {
															echo '';
														}
														?>
													</td>
													<td><?php echo h($row['discharge_by_nurse']); ?></td>
													<td><?php echo h($row['discharge_status']); ?></td>
													<td><?php if ($row['admit_type'] == 'admit_o') {
															echo 'Observation';
														} else {
															echo 'Admitted';
														}; ?></td>
													<td style="text-align:center;"><?php echo (int)$row['admission_count']; ?></td>
													<td style="text-align:center;"><a href="patient.php?hosp_no=<?php echo h($row['hospital_no']) ?>" class="btn btn-primary">View Patient</a></td>
												</tr>
												<?php
												// Build summary data
												$total_admitted[$row['hospital_no']] = true;
												if (!empty($row['doc_incharge'])) {
													if (isset($doc_incharge_count[$row['doc_incharge']])) {
														$doc_incharge_count[$row['doc_incharge']]++;
													} else {
														$doc_incharge_count[$row['doc_incharge']] = 1;
													}
												}
												if (!empty($row['nurse_fullname'])) {
													if (isset($nurse_count[$row['nurse_fullname']])) {
														$nurse_count[$row['nurse_fullname']]++;
													} else {
														$nurse_count[$row['nurse_fullname']] = 1;
													}
												}
												if (!empty($row['discharge_status'])) {
													if (isset($discharge_status_count[$row['discharge_status']])) {
														$discharge_status_count[$row['discharge_status']]++;
													} else {
														$discharge_status_count[$row['discharge_status']] = 1;
													}
												}
												if (!empty($row['admit_type'])) {
													if (isset($admit_type_count[$row['admit_type']])) {
														$admit_type_count[$row['admit_type']]++;
													} else {
														$admit_type_count[$row['admit_type']] = 1;
													}
												}
												?>
											<?php endforeach; ?>
										<?php else: ?>
											<tr>
												<td colspan="11" style="text-align:center;">No discharge patient records found for the specified criteria.</td>
											</tr>
										<?php endif; ?>
									</tbody>
								</table>

								<h2>Summary</h2>

								<p><strong>Total Unique Patients Admitted:</strong> <?php echo count($total_admitted); ?></p>

								<h3>Doctor In Charge Counts:</h3>
								<ul>
									<?php foreach ($doc_incharge_count as $doc => $count): ?>
										<li><?php echo h($doc); ?>: <?php echo $count; ?></li>
									<?php endforeach; ?>
								</ul>

								<h3>Nurse Admission Counts:</h3>
								<ul>
									<?php foreach ($nurse_count as $nurse => $count): ?>
										<li><?php echo h($nurse); ?>: <?php echo $count; ?></li>
									<?php endforeach; ?>
								</ul>

								<h3>Discharge Status Counts:</h3>
								<ul>
									<?php foreach ($discharge_status_count as $status => $count): ?>
										<li><?php echo h($status); ?>: <?php echo $count; ?></li>
									<?php endforeach; ?>
								</ul>

								<h3>Admit Type Counts:</h3>
								<ul>
									<?php foreach ($admit_type_count as $type => $count): ?>
										<li><?php

											if ($type == 'admit_o') {
												echo 'Observation';
											} else {
												echo 'Admitted';
											};


											///echo h($type); 
											?>: <?php echo $count; ?></li>
									<?php endforeach; ?>
								</ul>

							<?php } elseif ($no_pending_adm_discharge_request > 0) {
							?>
								<br>
								<h2>Discharge Request</h2>
								<hr>
								<table class='table table-striped table-bordered table-hover dataTables-example' style="font-size:16px;">
									<thead>
										<tr>
											<th>#</th>
											<th>Hospital</th>
											<th>Name</th>
											<th>Discharge by</th>
											<th>Pending</th>
											<th>.</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$n = 1;
										while ($roww = $pending_adm_discharge_request->fetch(PDO::FETCH_ASSOC)) {

											$hospital_no = $roww['hospital_no'];
											$appt_no = $roww['appt_no'];
										?>
											<tr>
												<td><?php echo $n; ?></td>
												<td><?php echo $hospital_no; ?></td>
												<td><?php echo $roww['patient_name']; ?></td>
												<td><?php echo $roww['referal_doc'];
													?></td>
												<td><?php
													$added_time = $roww['date_ap'] . ' ' . $roww['ap_time'];
													$Current_date = date('Y-m-d');
													date_default_timezone_set('Africa/Lagos');
													$date1 = new DateTime($Current_date);
													$date2 = new DateTime($added_time);
													$diff = $date2->diff($date1);
													$days = $diff->format('%a');
													echo $diff->format('<strong>%a</strong> day/<strong> %h</strong> hr'); ?></td>
												<td>

													<?php if ($days > 2) {
														$stmt_ = $db->prepare("
    SELECT hospital_no 
    FROM admission 
    WHERE hospital_no = :hospital_no  
    AND adm_status = 4 
    AND admit_type = 'admit_p' 
    AND date_discharge IS NOT NULL
");
														$stmt_->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
														$stmt_->execute();

														if ($stmt_->rowCount() > 0) {

															// Only delete if admission condition is satisfied
															$deleteSQL = "DELETE FROM discharge_fellowup WHERE hospital_no = :hospital_no";
															$sql = $db->prepare($deleteSQL);
															$sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
															$sql->execute();

															/* 	if ($sql->rowCount() > 0) {
																echo "✅ Record deleted successfully.";
															} else {
																echo "⚠️ No follow-up record found to delete.";
															} */
														} else {
															///echo " Admission condition not met. Deletion not allowed.";
														}
													} else { ?>

														<a href="patient.php?hosp_no=<?php echo $roww['hospital_no']; ?>&adm" class="btn btn-primary">View Patient</a>

													<?php } ?>


												</td>
											</tr>

										<?php
											$n++;
										} ?>

									</tbody>
								</table><?php
									}
										?>
						</div>
						<div id="pend-icu-tab" class="tab-pane <?= ($current_tab == 'icu' ? "active" : ""); ?> ">
							<div id="patient_on_icu_hdu_"></div>
						</div>

						<div id="pend-hdu-tab" class="tab-pane <?= ($current_tab == 'hdu' ? "active" : ""); ?> ">
							<div id="patient_on_icu_hdu_2"></div>
						</div>


					</div>
				</div>
			</div>
		</div>

	</div>
</div>