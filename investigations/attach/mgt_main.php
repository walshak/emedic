<?php
$user_type = null;
$r_title = null;

function queue($db)
{
	$searchCatera = '';
	$searchCatera4 = '';
	$setDates = '';
	$rTitle = '';

	$user_type = $_SESSION['speciality'] == 'Administrator' || $_SESSION['speciality'] == 'Receptionist' ? 'user' : 'lab_img_user';
	$section = $_SESSION['section'] ?: 'Radiology,Laboratory';
	$labMgtWhere = "section IN (:section) and ";
	$labMgtWhereNoAnd = "section IN (:section)";

	if (isset($_POST['hospital_no']) || isset($_POST['apply_narrow_search_patient']) || isset($_POST['uploadDocumentBtn']) || (isset($_GET['hosp_no']) && $_GET['hosp_no'] != '')) {
		echo $_POST['hospital_no'];
		exit;

		if (isset($_GET['hosp_no']) && $_GET['hosp_no'] != '') {
			$hospitalNo = $_GET['hosp_no'];
			$searchCatera = " and patient=:hospitalNo";
			$rTitle = 'Record(s)';
		} elseif (isset($_POST['hospital_no']) && $_POST['hospital_no'] != '') {
			$hospitalNo = $_POST['hospital_no'];
			$searchCatera = " and patient=:hospitalNo";

			if (isset($_POST['today']) && $_POST['today'] == 'today') {
				$startDate = date('Y-m-d');
				$endDate = date('Y-m-d');
				$searchCatera4 = " and date(request_date) between :startDate AND :endDate";
			} else {
				$searchCatera4 = '';
			}

			$rTitle = 'Record(s)';
		}
	} elseif (isset($_POST['apply_narrow_search'])) {
		if ($_POST["start_"] != '' && $_POST["end_"] != '') {
			$startDate = $_POST["start_"];
			$endDate = $_POST["end_"];
			$searchCatera4 = " and date(request_date) between :startDate AND :endDate";
			$rTitle = sprintf('Request(s) between dates: %s and %s', date('d, M Y', strtotime($startDate)), date('d, M Y', strtotime($endDate)));
		} else {
			$rTitle = 'Request(s)';
		}
	} else {

		$startDate = date("Y-m-d");
		$endDate = date("Y-m-d");
		$setDates = " and date(request_date) between :startDate and :endDate";
		$rTitle = sprintf('Request(s) between dates: %s - %s', date("d M Y", strtotime($startDate)), date("d M Y", strtotime($endDate)));
	}

	$stmt = $db->prepare("SELECT distinct app_no,request_date2,patient,patient_name,business_service_center FROM lab_manage as lab WHERE $labMgtWhereNoAnd $searchCatera $searchCatera4 $setDates ORDER BY request_date DESC LIMIT 100", [
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	]); // Limit the result set to 100 rows

	$stmt->bindParam(':section', $section);
	if ($searchCatera != '') {
		$stmt->bindParam(':hospitalNo', $hospitalNo);
	}
	if ($startDate != '' and $endDate != '') {
		$stmt->bindParam(':startDate', $startDate);
		$stmt->bindParam(':endDate', $endDate);
	}
	$stmt->execute();

	if ($stmt->rowCount() > 0) { ?>
		<hr>

		<?php
		if ((isset($_POST['hospital_no']) and $_POST['hospital_no'] != '') or (isset($_GET['hosp_no']) and $_GET['hosp_no'] != '')) {

			if ($_POST['hospital_no'] != '') {
				$hospital_number = $_POST['hospital_no'];
			} elseif (isset($_GET['hosp_no'])) {
				$hospital_number = $_GET['hosp_no'];
			} else {
				$hospital_number = $_POST['hospital_no'];
			}
		}

		?>

		<div class="alert alert-info">
			<?php echo '<strong>' . $stmt->rowCount() . ' - ' . $rTitle . '</strong>'; ?>
		</div>

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
					$stmtss = $db->prepare("SELECT old_hospital_no, vip FROM enrollee WHERE hospital_no = :hospital_no");
					$stmtss->bindParam(':hospital_no', $hospital_number);
					$stmtss->execute();

					if ($rowx = $stmtss->fetch(PDO::FETCH_ASSOC)) {
						$old_hospital_no = $rowx['old_hospital_no'];
						$vip_status = $rowx['vip']; // Retrieve the vip column
						$category = $_SESSION['section'];
						$notes_type = $category == 'Laboratory' ? 'Lab' : 'Scan';
						if ($old_hospital_no != '') {
							echo ' <hr/><button class="btn btn-danger" id="vista_notes_modal_btn" data-notes-type="' . $notes_type . '" data-hospital-no="' . $old_hospital_no . '">OLD EMR LAB</button>';
						}
					} else {
						//echo 'empty';
					}
					?>
				</td>
			</tr>
		</table>



		<br>
		<br>
		<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;">
			<thead>
				<tr>
					<th>#</th>
					<th>Patient Details</th>
					<th width="20%" style="color: firebrick; ">PENDING REQUESTS</th>
					<th width="20%" style="color: blue; ">COMPLETED REQUESTS</th>
					<th>Department</th>
					<th>View</th>
				</tr>
			</thead>
			<tbody>

				<?php
				$n = 1;
				$request_date2 = null;
				$list_tests = '';
				$list_tests_pending = '';
				$list_tests_done = '';
				$list_tests_sn = '';
				$list_test_done_image = '';
				$list_test_done_lab = '';
				$detail = null;

				while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

					$hosp_no = $roww['patient'];
					$stmtss = $db->prepare("SELECT vip FROM enrollee WHERE hospital_no = :hospital_no LIMIT 1");
					$stmtss->bindParam(':hospital_no', $hosp_no);
					$stmtss->execute();
					$rowx = $stmtss->fetch(PDO::FETCH_ASSOC);
					$vip_status = $rowx['vip']; // Retrieve the vip column
					if ($vip_status == 1) {
						$stmtcv = $db->prepare("SELECT 1 FROM manage_patients_vip_staff WHERE hospital_no = ? AND user_id = ? AND status=1");
						$stmtcv->execute([$hosp_no, $_SESSION['id']]);
						if ($stmtcv->rowCount() > 0 || $_SESSION['rights'] === 'MD') {
							$patient_name = $roww['patient_name'];
							$vip_status = 0;
						} else {
							$vip_status = 1;
							$patient_name = '';
						}
					} else {
						$patient_name = $roww['patient_name'];
					}				?>


					<tr>
						<td><?php echo $n; ?></td>
						<?php $patient = $roww['patient']; ?>
						<td><?php
							if ($vip_status == 1) {
								//echo base64_encode($patient);
								echo ($patient);
							} else {
								echo $patient . ' / ' . $roww['app_no'] . '<br>' . $patient_name;
							}

							?></td>
						<td><?php

							$app_no = $roww['app_no'];
							if ($app_no == '') {
								$app_no = ' ';
							}
							$appt_no_ = " and app_no='$app_no'";
							$data_capture_status = 'queue';
							$preferred_specimen = $roww['preferred_specimen'];
							$type_patient = $roww['business_service_center'];
							$request_date2 = $roww['request_date2'];
							$request_date_filter = " and request_date2='$request_date2'";

							$stmtss = $db->prepare("SELECT d.department FROM apptm a inner join department d on d.sn=a.dept WHERE hospital_no=:hosp_no");
							$stmtss->bindParam(':hosp_no', $hosp_no);
							$stmtss->execute();
							if ($rowx = $stmtss->fetchColumn()) {
								$department = $rowx;
							} else {
								$department = '';
							}

							$stmtx = $db->query("SELECT sn,test_name,request_by,requesting_physician,
							business_service_center,test_id,section,request_date,request_date2,labrequest_no,
							request_note,requesting_physician,lab_combos,preferred_specimen,collected_specimen, 
							result_date,entered_by,data_capture_status,group_id,lab_combo_request_no,approved_by FROM lab_manage 
							WHERE patient='$hosp_no' $appt_no_ $setdates $request_date_filter order by sn DESC"); ///,  

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


									// result_date, request_date, approved_by, request_by entered_by
									$detail = $roww_['sn'] . '----' . $test_name . '----' . $roww_['business_service_center'] . '----' .
										$roww_['test_id'] . '----' . $roww_['section'] . '----' . $roww_['request_date'] . '----' .
										$roww_['labrequest_no'] . '----' .	$request_by . '----' . $requesting_physician . '----' .
										$roww_['lab_combos'] . '----' . $preferred_specimen . '----' .
										$roww_['result_date'] . '----' . $entered_by . '----' . $roww_['data_capture_status'] . '----' .
										$day . '----' . $request_note . '----' .
										$roww_['collected_specimen'] . '----' . $roww_['group_id'] . '----' . $roww_['lab_combo_request_no'] . '----' .
										$roww_['approved_by'];


									if ($_SESSION['section'] == 'Laboratory' or $user_type == 'user') {

										if ($roww_['section'] == 'Radiology') {
											$list_test_done_image .= $roww_['sn'] . ',';
										}

										if ($roww_['section'] == 'Laboratory') {

											if ($roww_['data_capture_status'] == 'queue' or $roww_['data_capture_status'] == 'specimen' or $roww_['data_capture_status'] == 'capture') {
												$list_tests_pending .= $roww_['test_name'] . ',';
											} elseif ($roww_['data_capture_status'] == 'result' or $roww_['data_capture_status'] == 'approve') {
												$list_tests_done .= $roww_['test_name'] . ',';
											}
											$list_tests_sn .= $detail . ',';
										}
									} elseif ($_SESSION['section'] == 'Radiology' or $user_type == 'user') {

										if ($roww_['section'] == 'Laboratory') {
											$list_test_done_lab .= $roww_['sn'] . ',';
										}

										if ($roww_['section'] == 'Radiology') {

											if ($roww_['data_capture_status'] == 'queue' or $roww_['data_capture_status'] == 'specimen' or $roww_['data_capture_status'] == 'capture') {
												$list_tests_pending .= $roww_['test_name'] . ',';
											} elseif ($roww_['data_capture_status'] == 'result' or $roww_['data_capture_status'] == 'approve') {
												$list_tests_done .= $roww_['test_name'] . ',';
											}
											$list_tests_sn .= $detail . ',';
										}
									}
								}
							}

							$detail = null;

							if ($requesting_physician != '') {
								$RQ = $requesting_physician;
							} else {
								$RQ = $request_by;
							}

							$image_list = $list_test_done_image; //[6]; //// image

							if ($image_list == '') {
								$image_package = 'null';
							} else {
								$image_package =  $app_no . '____' . $hosp_no . '____' . $image_list;
								$image_list = null;
							}

							$lab_list = $list_test_done_lab; ///[7]; //// lab								  
							if ($lab_list == '') {
								$lab_package = 'null';
							} else {
								$lab_package = $app_no . '____' . $hosp_no . '____' . $lab_list;
								$lab_list = null;
							}

							$date_rq = $request_date2; //[8];
							$f = $list_tests_pending; ///[5];
							if ($f != '') {
								////echo $f = $list_tests_pending; ///[5];
								$list_tests_pending = null;


								if ($RQ != '') {
									$part = explode(" ", $RQ);
									echo '<br><strong>Requested by: </strong>' . $part[0] . ' ' . substr($part[1], 0, 1) . '. ';
									echo '<br>' . $date_rq;
								}
							} else {
								echo 'No Investigation(s)';
							}

							$list_tests = substr_replace($list_tests, "", -1);
							?></td>
						<td><?php echo $list_tests_done; //[4]; 
							?></td>
						<td><?php echo $department; ?></td>
						<td>
							<?php
							if ($user_type == 'lab_img_user' and $f != '' && $vip_status == 0 && $stmtss->rowCount() > 0) { ?>
								<input type="button" name="" value="Medical Notes & Vitals" data-target="#modal" id="<?php echo $app_no . '____' . $hosp_no; ?>" class="btn btn-info consultations_vitals" />
							<?php } ?>


							<?php if ($_SESSION['section'] == 'Laboratory') { ?>
								&nbsp;|&nbsp;
								<input type="button" name="" value="Image" data-target="#modal" id="<?php echo $image_package; ?>" class="btn btn-primary view_results_list" />
								&nbsp;|&nbsp;

								<?php if ($f != '' or $list_tests_done != '') { ?>
									<input type="button" name="" value="Lab." data-target="#modal"
										id="<?php echo $app_no . '____' . $hosp_no . '____' . $list_tests_sn . '____' .
												$list_tests . '____' . $patient_name . '____new____' . $type_patient; ?>"
										class="btn btn-success enter_results" <?php if ($user_type == 'user') { ?> disabled <?php } ?> />
								<?php
									$list_tests_sn = null;
									$list_tests = null;
								} ?>


							<?php } elseif ($_SESSION['section'] == 'Radiology') { ?>
								&nbsp;|&nbsp;
								<input type="button" name="" value="Lab" data-target="#modal" id="<?php echo $lab_package; ?>" class="btn btn-primary view_results_list" />
								&nbsp;|&nbsp;

								<?php if ($f != '' or $list_tests_done != '') { ?>
									<input type="button" name="" value="Image" data-target="#modal"
										id="<?php echo $app_no . '____' . $hosp_no . '____' . $list_tests_sn . '____' .
												$list_tests . '____' . $patient_name . '____new____' . $type_patient; ?>"
										class="btn btn-success enter_results" <?php if ($user_type == 'user') { ?> disabled <?php } ?> />
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
	<?php } else { ?>
		<br>
		<div class="alert alert-danger">No Record(s) Available! <br><?php ///echo $r_title; 
																	?>: </div>
<?php }
}



?>

<div class="modal inmodal fade" id="vista_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Patient Investiagtion Reports</h4>
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
				<h4 class="modal-title" id="">Patient Investiagtion Reports</h4>
			</div>
			<div class="modal-body" id="vista_notes_modal_body">

			</div>

		</div>
	</div>
</div>