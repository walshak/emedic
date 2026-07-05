<?php
$r_title = null;

function queue($db, $sort_by_dept, $user_type, $old_hospital_no, $patient_fullname, $notes_status, $vip_status, $hospital_number, $url, $_count_stmtss, $hmo_no)
{

	list($startDate, $endDate, $hospitalNo, $filter, $stage) = explode('/', $url);

	// Set pagination
	$interval_status = 0;
	$limit = 20; // Records per page
	$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
	$offset = ($page - 1) * $limit;

	// Add LIMIT clause to SQL
	$orde_by = " ORDER BY request_date2 DESC LIMIT $offset, $limit";


	$searchCatera = '';
	$searchCatera4 = '';
	$setDates = '';
	$filter_pay = null;
	$r_title = null;

	$section = $_SESSION['section'] ?: 'Radiology,Laboratory';
	$labMgtWhere = "section IN (:section) and ";
	$labMgtWhereNoAnd = "section IN (:section)";

	if (isset($_POST['apply_narrow_search']) or (isset($_GET['page']) && $stage == 1)) {

		$rTitle = 'Request(s)';
		$searchCatera4 = '';

		if (!empty($_POST["start_"]) && !empty($_POST["end_"])) {
			$startDate = $_POST["start_"];
			$endDate = $_POST["end_"];
		}
		$start = new DateTime($startDate);
		$end = new DateTime($endDate);
		$interval = $start->diff($end)->days;

		if ($interval > 30) {
			// Handle error if date difference exceeds 30 days
			echo "<div class='alert alert-danger'>Date range should not exceed 30 days.</div>";
			$startDate = '';
			$endDate = '';
			$interval_status = 1;
		} else {
			$searchCatera4 = " and request_date2 between :startDate AND :endDate";
			$rTitle = sprintf(
				'Request(s) between dates: %s and %s',
				date('d, M Y', strtotime($startDate)),
				date('d, M Y', strtotime($endDate))
			);
		}


		$path_url = "$startDate/$endDate/hosp/filter/1";
		$inner_sort_by = "order by sn DESC";
	} elseif (
		isset($_POST['apply_narrow_search_patient']) || isset($_POST['uploadDocumentBtn']) ||
		(isset($_GET['page']) && ($stage == 2 or $stage == 3)) ||
		(isset($_GET['hosp_no']) && $_GET['hosp_no'] != '')
	) {

		if (($hospitalNo != '' && $stage == 2) or (isset($_GET['hosp_no']) && $_GET['hosp_no'] != '')) {
			if ($hospitalNo == '') {
				$hospitalNo = $_GET['hosp_no'];
			}
			$searchCatera = " and patient=:hospitalNo";
			$rTitle = 'Record(s)';
			$path_url = "//$hospitalNo/filter/2";
			$inner_sort_by = "order by sn DESC";

			if (isset($_GET['recent'])) {
				$startDate = date("Y-m-d", strtotime("-5 days"));
				$endDate = date("Y-m-d");
				$date_field = ($hmo_no == 1000) ? 'date_time_pay' : 'request_date';
				$filter_pay = " AND DATE($date_field) BETWEEN :startDate AND :endDate AND data_capture_status='queue'";
				$filter_pay_second = " AND DATE($date_field) BETWEEN '$startDate' AND '$endDate' AND data_capture_status='queue'";
				$orde_by = " ORDER BY $date_field DESC LIMIT $offset, $limit";
				$inner_sort_by = " ORDER BY $date_field DESC";

				$rTitle = 'Record(s) Between date range: ' . date("d M, Y", strtotime($startDate)) . ' - ' . date("d M, Y", strtotime($endDate));
				$path_url = "$startDate/$endDate/$hospitalNo/filter/2";
			}
		} elseif (($hospitalNo != '' && $stage == 3) or (isset($_POST['hospital_no']) && $_POST['hospital_no'] != '')) {

			// Set hospital number if not already set
			if (empty($hospitalNo) && isset($_POST['hospital_no']) && $_POST['hospital_no'] != '') {
				$hospitalNo = $_POST['hospital_no'];
				$startDate = null;
				$endDate = null;
			}

			// Initialize variables
			$start_2 = '';
			$end_2 = '';
			$advance_filter = '';

			// Handle filtering and pagination
			if (isset($_GET['page'])) {
				// Preserve filter on pagination
				if (!empty($filter)) {
					$advance_filter = $filter;
				}

				if (!empty($startDate) && !empty($endDate)) {
					$start_2 = $startDate;
					$end_2 = $endDate;
				}
			} elseif (!empty($_POST['advance_filter'])) {
				// Handle fresh filter from POST
				$advance_filter = $_POST['advance_filter'];

				if (!empty($_POST['start_2'])) {
					$start_2 = $_POST['start_2'];
				}

				if (!empty($_POST['end_2'])) {
					$end_2 = $_POST['end_2'];
				}
			}

			$searchCatera = " and patient=:hospitalNo";

			if ($advance_filter != '') {

				if ($advance_filter == 'queue') {
					$filter_pay = " and request_date2 between :startDate AND :endDate AND data_capture_status='queue' AND date_time_pay IS NULL";
					$filter_pay_second = " and request_date2 between '$start_2' AND '$end_2' AND data_capture_status='queue' AND date_time_pay IS NULL";
					$inner_sort_by = "order by request_date2 DESC";
				} elseif ($advance_filter == 'payment') {
					// Determine which field to use based on HMO number
					$date_field = ($hmo_no == 1000) ? 'date_time_pay' : 'request_date';
					$filter_pay = " AND DATE($date_field) BETWEEN :startDate AND :endDate AND data_capture_status='queue'";
					$filter_pay_second = " AND DATE($date_field) BETWEEN '$start_2' AND '$end_2' AND data_capture_status='queue'";
					$orde_by = " ORDER BY $date_field DESC LIMIT $offset, $limit";
					$inner_sort_by = " ORDER BY $date_field DESC";
				} else {
					$filter_pay = " and DATE(result_date) between :startDate AND :endDate AND data_capture_status='$advance_filter'";
					$filter_pay_second = " and DATE(result_date) between '$start_2' AND '$end_2' AND data_capture_status='$advance_filter'";
					$inner_sort_by = "order by result_date DESC";
				}

				$rTitle = 'Record(s) Between date range: ' . date("d M, Y", strtotime($start_2)) . ' - ' . date("d M, Y", strtotime($end_2));
			} else {
				$rTitle = 'Record(s)';
				$inner_sort_by = "order by request_date2 DESC";
			}

			$path_url = "$start_2/$end_2/$hospitalNo/$advance_filter/3";
		} else {
			$rTitle = '';
			$inner_sort_by = "order by request_date2 DESC";
		}
	} else {

		$inner_sort_by = "order by sn DESC";
		//$startDate = date("Y-m-d");
		//$endDate = date("Y-m-d");
		//$setDates = " and date(request_date) between :startDate and :endDate";
		//$rTitle = sprintf('Request(s) between dates: %s - %s', date("d M Y", strtotime($startDate)), date("d M Y", strtotime($endDate)));
		//$path_url = "$startDate/$endDate/hosp/filter";
	}



	// Count total matching records
	$count_sql = "SELECT COUNT(DISTINCT app_no) as total FROM lab_manage WHERE 1=1 $searchCatera $searchCatera4 $filter_pay $setDates $sort_by_dept";
	$count_stmt = $db->prepare($count_sql);

	// Bind parameters if necessary
	if ($searchCatera != '') $count_stmt->bindParam(':hospitalNo', $hospitalNo);
	if (!empty($startDate) && !empty($endDate) && $advance_filter == '') {
		$count_stmt->bindParam(':startDate', $startDate);
		$count_stmt->bindParam(':endDate', $endDate);
	}
	if (!empty($start_2) && !empty($end_2) && $advance_filter != '') {
		$count_stmt->bindParam(':startDate', $start_2);
		$count_stmt->bindParam(':endDate', $end_2);
	}

	$count_stmt->execute();
	$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
	$total_pages = ceil($total_records / $limit);


	if ($rTitle != '' && $interval_status == 0) {
		///echo '<br>';
		///echo $searchCatera . $searchCatera4 . $filter_pay . $setDates . $sort_by_dept;

		// Prepare the base SQL query
		$sql = "SELECT DISTINCT app_no, request_date2, patient, patient_name, business_service_center FROM lab_manage as lab WHERE 1=1";

		// Conditionally add the section filter based on user type
		if ($user_type == 'lab_img_user') {
			$sql .= " AND section = '$section'";
		}
		// Add other search criteria
		$sql .= " $searchCatera $searchCatera4 $filter_pay $setDates $sort_by_dept $orde_by";

		// Prepare the statement
		$stmt = $db->prepare($sql, [
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		]);

		// Bind other parameters
		if ($searchCatera != '') {
			$stmt->bindParam(':hospitalNo', $hospitalNo);
		}
		if ($startDate != '' && $endDate != '' && isset($_GET['recent'])) {

			$stmt->bindParam(':startDate', $startDate);
			$stmt->bindParam(':endDate', $endDate);
		}
		if ($startDate != '' && $endDate != '' && $advance_filter == '') {
			$stmt->bindParam(':startDate', $startDate);
			$stmt->bindParam(':endDate', $endDate);
		}

		if ($start_2 != '' && $end_2 != '' && $advance_filter != '') {
			$stmt->bindParam(':startDate', $start_2);
			$stmt->bindParam(':endDate', $end_2);
		}
		$stmt->execute();
		if ($stmt->rowCount() > 0) { ?>
			<hr>



			<div class="alert alert-info">
				<?php echo '<strong>' . $stmt->rowCount() . ' - ' . $rTitle . '</strong>'; ?>
			</div>

			<?php
			$category = $_SESSION['section'];
			$notes_type = $category == 'Laboratory' ? 'Lab' : 'Scan';
			if ($hospital_number != '') { ?>
				<table>
					<tr>
						<td>
							<input type="button" name="on_cr" value="View Patient Biodata" data-target="#modal" id="<?php echo $hospital_number; ?>" class="btn btn-primary  bio_data_link" style="font-size: 15px; color:white; " /> &nbsp;
						</td>
						<td>
							<input type="button" name="" value="Upload & View Documents" data-target="#modal" id="<?php echo $hospital_number; ?>" class="btn btn-success doc_" />
						</td>
						<td>
							&nbsp;&nbsp;<a href="mgt.php?hosp_no=<?= $hospital_number; ?>" class="btn btn-info">Refresh Patient List</a>
						</td>
						<td>
							<?php


							if ($old_hospital_no != '') {
								echo ' <hr/><button class="btn btn-danger" id="vista_notes_modal_btn" data-notes-type="' . $notes_type . '" data-hospital-no="' . $old_hospital_no . '">OLD EMR LAB</button>';
							}

							?>
						</td>
					</tr>
				</table>
			<?php } ?>


			<br>
			<br>
			<b style="color:brown; display:block; text-align:right;"><i>(R) means result entered, awaiting approval.</i></b>
			<table class="table table-striped table-bordered table-hover" style="font-size: 14px;">
				<thead>
					<tr>
						<th>#</th>
						<th>Patient Details</th>
						<th width="27%"><b style="color: firebrick; ">PENDING REQUESTS</b></th>
						<th width="27%"><b style="color: blue; ">COMPLETED REQUESTS </b></th>
						<th>Department</th>
						<th>View</th>
					</tr>
				</thead>
				<tbody>

					<?php
					// Session fallbacks
					$current_user = isset($_SESSION['id']) ? $_SESSION['id'] : '';
					$current_rights = isset($_SESSION['rights']) ? $_SESSION['rights'] : '';
					$current_section = isset($_SESSION['section']) ? $_SESSION['section'] : '';


					// Prefetch VIP statuses
					$vip_map = array();
					$vip_stmt = $db->query("SELECT hospital_no, vip FROM enrollee");
					while ($row = $vip_stmt->fetch(PDO::FETCH_ASSOC)) {
						$vip_map[$row['hospital_no']] = $row['vip'];
					}

					// Prefetch VIP staff access
					$vip_staff_patients = array();
					$vip_stmt2 = $db->prepare("SELECT hospital_no FROM manage_patients_vip_staff WHERE user_id = ? AND status = 1");
					$vip_stmt2->execute(array($current_user));
					while ($row = $vip_stmt2->fetch(PDO::FETCH_ASSOC)) {
						$vip_staff_patients[$row['hospital_no']] = true;
					}

					// Prefetch department map
					$department_map = array();
					$dept_stmt = $db->query("SELECT a.hospital_no, d.department FROM apptm a INNER JOIN department d ON d.sn = a.dept");
					while ($row = $dept_stmt->fetch(PDO::FETCH_ASSOC)) {
						$department_map[$row['hospital_no']] = $row['department'];
					}
					$nx = 1;
					while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

						$hosp_no = $roww['patient'];
						$app_no = $roww['app_no'];
						$patient_name = '';
						$list_tests_pending = '';
						$list_tests_done = '';
						$list_tests = '';
						$list_tests_sn = '';
						$list_test_done_image = '';
						$list_test_done_lab = '';
						$rlst = $r_status = '';
						$pending_c = 1;
						$t_amount = 0;
						$done_c = 1;
						$n = 1;
						$request_date2 = null;
						$detail = null;


						// VIP logic
						$vip_status = isset($vip_map[$hosp_no]) ? $vip_map[$hosp_no] : 0;
						if ($vip_status == 1) {
							$is_authorized = isset($vip_staff_patients[$hosp_no]) || $current_rights === 'MD';
							if ($is_authorized) {
								$patient_name = $roww['patient_name'];
								$vip_status = 0;
							} else {
								$patient_name = '';
								$vip_status = 1;
							}
						} else {
							$patient_name = $roww['patient_name'];
						}
					?>


						<tr>
							<td><?php echo $nx++; ?></td>
							<?php $patient = $roww['patient']; ?>
							<td><?php
								if ($vip_status == 1) {
									//echo base64_encode($patient);
									echo ($patient);
								} else {
									echo $patient . ' / ' . $roww['app_no'] . '<br>' . $patient_name;

									if ($hospital_number == '') {
										echo "<br><a href='mgt.php?hosp_no=$patient&recent' class='btn btn-primary btn-xs'>See this Patient List Only</a>";
									}
								}
								?></td>
							<td><?php

								$app_no = ($roww['app_no'] == '') ? ' ' : $roww['app_no'];

								$appt_no_ = " and app_no='$app_no'";
								$data_capture_status = 'queue';
								$preferred_specimen = null;
								$type_patient = $roww['business_service_center'];
								$request_date2 = $roww['request_date2'];
								$request_date_filter = " and request_date2='$request_date2'";

								// Department
								$department = isset($department_map[$hosp_no]) ? $department_map[$hosp_no] : '';

								// $searchCatera $searchCatera4 $filter_pay $setDates $sort_by_dept
								///echo $filter_pay_second;

								$stmtx = $db->query("SELECT sn,test_name,request_by,requesting_physician,
							business_service_center,test_id,section,request_date,request_date2,labrequest_no,
							request_note,requesting_physician,lab_combos,preferred_specimen,collected_specimen, 
							result_date,entered_by,data_capture_status,group_id,lab_combo_request_no,approved_by,
							bill,amount,attachment,date_time_pay,sms_status,collected_notes,collected_by,abnormal_results FROM lab_manage 
							WHERE patient='$hosp_no' $appt_no_ $request_date_filter $filter_pay_second $sort_by_dept $inner_sort_by"); ///,  

								while ($roww_ = $stmtx->fetch(PDO::FETCH_ASSOC)) {
									if ($roww_['data_capture_status']  == 'approve' && $vip_status == 1) {
									} else {
										$Current_date = date("Y-m-d");
										$date1 = new DateTime($Current_date);
										$date2 = new DateTime($roww_['result_date']);
										$diff = $date2->diff($date1);
										$day = $diff->format('%a');

										$request_note = str_replace(",", " ", $roww_['request_note']);
										$test_name = str_replace(",", " ", $roww_['test_name']);
										$request_by = str_replace(",", " ", $roww_['request_by']);
										$requesting_physician = str_replace(",", "", $roww_['requesting_physician']);
										$entered_by = str_replace(",", "", $roww_['entered_by']);
										$preferred_specimen = str_replace(",", "+", trim($roww_['preferred_specimen']));

										///9027----4D obstetrics scan----IN----57----Radiology----
										///2025-07-01 10:49:10----LB01542998990230131972----
										///Super Admin----
										//----0----//----//----//----queue----0----//----//----002804----
										///LB0154299899023013197----//----//----//----

										// result_date, request_date, approved_by, request_by entered_by
										$detailParts = [
											$roww_['sn'],
											$test_name,
											$roww_['business_service_center'],
											$roww_['test_id'],
											$roww_['section'],
											$roww_['request_date'],
											$roww_['labrequest_no'],
											$request_by,
											$requesting_physician,
											$roww_['lab_combos'],
											$preferred_specimen,
											$roww_['result_date'],
											$entered_by,
											$roww_['data_capture_status'],
											$day,
											$request_note,
											$roww_['collected_specimen'],
											$roww_['group_id'],
											$roww_['lab_combo_request_no'],
											$roww_['approved_by'],
											$roww_['bill'],
											$roww_['amount'],
											$roww_['attachment'],
											$roww_['date_time_pay'],
											$roww_['sms_status'],
											$roww_['collected_notes'],
											$roww_['collected_by']
										];

										$detail = implode('----', $detailParts);

										$t_amount = $t_amount + $roww_['amount'];
										if (!in_array($roww_['abnormal_results'], ['0', '', 'Normal']) && $_SESSION['rights'] === 'LB') {
											$rlst = "<small style='color:brown;'><i>{$roww_['abnormal_results']}</i></small>";
										}

										$r_status = ($roww_['data_capture_status'] == 'result') ? " <b style='color:brown;'><i>(R)</i></b>" : '';
										$status = $roww_['data_capture_status'];
										if (in_array($status, ['queue', 'specimen', 'capture']) && !empty($roww_['date_time_pay'])) {
											$pay_date_raw = $roww_['date_time_pay'];
											$pay_date = strtotime($pay_date_raw);
											if ($pay_date !== false) {
												$formatted_pay_date = '<i>Paid: ' . date('d/m/y', $pay_date) . '</i>';
											} else {
												$formatted_pay_date = ''; // or handle invalid date here
											}
										} else {
											$formatted_pay_date = '';
										}

										$bill_display = ($roww_['bill'] != '') ? '(LB' . $roww_['bill'] . ')' : '';
										$test_display = $roww_['test_name'] . '<b>' . $bill_display . ($formatted_pay_date ? ' ' . $formatted_pay_date : '') . '</b>';

										if ($_SESSION['section'] == 'Laboratory' or $user_type == 'user') {

											if ($roww_['section'] == 'Radiology') {
												$list_test_done_image .= $roww_['sn'] . ',';
											}
											if ($roww_['section'] === 'Laboratory') {
												if (in_array($status, ['queue', 'specimen', 'capture'])) {
													$list_tests_pending .= $pending_c . '). ' . $test_display . '<br>';
													$pending_c++;
												} elseif (in_array($status, ['result', 'approve'])) {
													$list_tests_done .= $done_c . '). ' . $test_display . $rlst . $r_status . '<br>';
													$done_c++;
												}
												$list_tests_sn .= $detail . ',';
											}
										}

										if ($_SESSION['section'] == 'Radiology' or $user_type == 'user') {

											if ($roww_['section'] === 'Laboratory') {
												$list_test_done_lab .= $roww_['sn'] . ',';
											}

											if ($roww_['section'] === 'Radiology') {
												if (in_array($status, ['queue', 'specimen', 'capture'])) {
													$list_tests_pending .= $pending_c . '). ' . $test_display . '<br>';
													$pending_c++;
												} elseif (in_array($status, ['result', 'approve'])) {
													$list_tests_done .= $done_c . '). ' . $test_display . $rlst . $r_status . '<br>';
													$done_c++;
												}

												$list_tests_sn .= $detail . ',';
											}
										}
									}
									$rlst = $r_status = null;
								}

								$detail = null;

								$RQ = (!empty($requesting_physician)) ? $requesting_physician : $request_by;

								$image_list = $list_test_done_image;
								$image_package = ($image_list == '') ? 'null' : $app_no . '____' . $hosp_no . '____' . $image_list;
								$image_list = null;

								$lab_list = $list_test_done_lab;
								$lab_package = ($lab_list == '') ? 'null' : $app_no . '____' . $hosp_no . '____' . $lab_list;
								$lab_list = null;

								$date_rq = $request_date2; // [8]
								$f = $list_tests_pending;  // [5]

								if ($f != '') {
									echo $list_tests_pending;
									$list_tests_pending = null;

									if ($RQ != '') {
										$part = explode(' ', trim($RQ));
										$first = $part[0];
										$initial = isset($part[1]) ? substr($part[1], 0, 1) . '.' : '';

										echo '<br><b>N' . number_format($t_amount, 2) . '</b>';
										echo '<br><strong>Requested by: </strong>' . $first . ' ' . $initial;
										echo '<br>' . date('d, M y', strtotime($date_rq));
									}
								} else {
									echo 'No Investigation(s)';
								}
								$list_tests = rtrim($list_tests, ','); ?>
							</td>
							<td><?php echo $list_tests_done; //[4]; 
								?></td>
							<td><?php echo $department; ?></td>
							<td>
								<?php
								if ($notes_status == "empty" && $_count_stmtss == 0) {
									echo "Search patient to view notes.";
								} elseif ($user_type == 'lab_img_user' && $f != '' && $vip_status == 0) { ?>
									<input type="button" name="" value="Notes & Vitals" data-target="#modal" id="<?php echo $app_no . '____' . $hosp_no; ?>" class="btn btn-sm btn-info consultations_vitals" />
								<?php } ?>

								<?php if ($_SESSION['section'] == 'Laboratory' or $user_type == 'user') { ?>
									|
									<input type="button" name="" value="Image" data-target="#modal" id="<?php echo $image_package; ?>" class="btn btn-sm btn-primary view_results_list" />
									|

									<?php if ($f != '' or $list_tests_done != '') { ?>
										<input type="button" name="" value="Lab." data-target="#modal"
											id="<?php echo $app_no . '____' . $hosp_no . '____' . $list_tests_sn . '____' .
													$list_tests . '____' . $patient_name . '____new____' . $type_patient; ?>"
											class="btn btn-sm btn-success enter_results" />
									<?php
										$list_tests_sn = null;
										$list_tests = null;
									} ?>


								<?php } elseif ($_SESSION['section'] == 'Radiology' or $user_type == 'user') { ?>
									|
									<input type="button" name="" value="Lab" data-target="#modal" id="<?php echo $lab_package; ?>" class="btn btn-sm btn-primary view_results_list" />
									|

									<?php if ($f != '' or $list_tests_done != '') { ?>
										<input type="button" name="" value="Image" data-target="#modal"
											id="<?php echo $app_no . '____' . $hosp_no . '____' . $list_tests_sn . '____' .
													$list_tests . '____' . $patient_name . '____new____' . $type_patient; ?>"
											class="btn btn-sm btn-success enter_results" />
									<?php
										$list_tests_sn = null;
										$list_tests = null;
									} ?>

								<?php }
								$list_tests_done = null;
								?>
							</td>
						</tr>
					<?php
						$n++;
					} ?>

				</tbody>
			</table>


			<?php

			if ($total_pages > 1) {
				echo '<nav><ul class="pagination">';

				// Previous button
				if ($page > 1) {
					echo '<li class="page-item"><a class="page-link" href="?url=' . $path_url . '&page=' . ($page - 1) . '">&laquo; Prev</a></li>';
				}

				// Page numbers
				for ($i = 1; $i <= $total_pages; $i++) {
					$active = ($i == $page) ? 'active' : '';
					echo "<li class='page-item $active'><a class='page-link' href='?url=$path_url&page=$i'>$i</a></li>";
				}

				// Next button
				if ($page < $total_pages) {
					echo '<li class="page-item"><a class="page-link" href="?url=' . $path_url . '&page=' . ($page + 1) . '">Next &raquo;</a></li>';
				}

				echo '</ul></nav>';
			}
			?>
		<?php } else { ?>
			<br>
			<div class="alert alert-danger">No Record(s) Available! <br></div>
<?php }
	} else {
		echo '<br><div class="alert alert-danger">No Option or Search Cateria Selected.<br></div>';
	}
}



?>

<div class="modal inmodal fade" id="vista_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Patient Investigation Reports</h4>
			</div>
			<div class="modal-body" id="vista_notes_modal_body">

			</div>


		</div>
	</div>
</div>



<div class="modal inmodal fade" id="vista_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Patient Investigation Reports</h4>
			</div>
			<div class="modal-body" id="vista_notes_modal_body">

			</div>

		</div>
	</div>
</div>