<?php

function queue($db)
{

	if ($_SESSION['speciality'] == 'Administrator' or $_SESSION['speciality'] == 'Receptionist') {
		$user_type = 'user';
	} else {
		$user_type = 'lab_img_user';
	}
	if ($_SESSION['section'] == "") {
		$lab_mgt_where = "(section='Radiology' or section='Laboratory') and ";
		$lab_mgt_where_no_and = "(section='Radiology' or section='Laboratory')";
	} else {
		$category = $_SESSION['section'];
		$lab_mgt_where = "section='$category' and ";
		$lab_mgt_where_no_and = "section='$category'";
	}

	$search_catera = '';
	$search_catera3 = '';
	$search_catera4 = '';

	if (
		isset($_POST['apply_narrow_search_patient']) or isset($_POST['uploadDocumentBtn'])
		or (isset($_GET['hosp_no']) and $_GET['hosp_no'] != '')
	) {

		if (isset($_GET['hosp_no']) and $_GET['hosp_no'] != '') {
			$hospital_no = $_GET['hosp_no'];
			$search_catera = " and patient='$hospital_no'";
			$search_catera4 = '';
			$setdates = '';
			$r_title = 'Record(s)';
		}

		if (isset($_POST['hospital_no']) and $_POST['hospital_no'] != '') {
			$hospital_no = $_POST['hospital_no'];
			$search_catera = " and patient='$hospital_no'";

			if (isset($_POST['today']) and $_POST['today'] == 'today') {
				$start_date = date('Y-m-d');
				$end_date = date('Y-m-d');
				///$end_date=$_POST["end_"];
				$search_catera4 = "and date(request_date) between '$start_date' AND '$end_date'";
			} else {

				$search_catera4 = '';
			}


			$setdates = '';
			$r_title = 'Record(s)';
		}
		///echo '=====================================';

	} elseif (isset($_POST['apply_narrow_search'])) {

		if ($_POST["start_"] != '' and $_POST["end_"] != '') {
			$start_date = $_POST["start_"];
			$end_date = $_POST["end_"];
			$search_catera4 = "and date(request_date) between '$start_date' AND '$end_date'";

			$r_title = 'Request(s) between dates: ' . date('d, M Y', strtotime($start_date)) .  ' and ' . date('d, M Y', strtotime($end_date));
		} else {
			$r_title = 'Request(s)';
		}
	} else {


		$setdate = date("Y-m-d");
		$end = date("Y-m-d");
		$start = date("Y-m-d");
		$setdates = " and date(request_date) between '$start' and '$end'";
		$r_title = ' Request(s) between dates:  ' . date("d M Y", strtotime($start)) . ' - ' . date("d M Y", strtotime($end)) . '</strong>';
	}


	$stmt = $db->query(sprintf("SELECT distinct app_no,request_date2,patient,patient_name,business_service_center
	FROM lab_manage as lab  WHERE $lab_mgt_where_no_and $search_catera $search_catera3 $search_catera4 $setdates order by request_date DESC"));
	if ($stmt->rowCount() > 0) { ?>
		<hr>



		<?php

		if ((isset($_POST['hospital_no']) and $_POST['hospital_no'] != '') or (isset($_GET['hosp_no']) and $_GET['hosp_no'] != '')) {

			if (isset($_GET['hosp_no'])) {
				$hospital_number = $_GET['hosp_no'];
			} else {
				$hospital_number = $_POST['hospital_no'];
			}
			$stmt12 = $db->query("SELECT e.*,i.insurance_name,i.interest,i.insurance_type,g.guardian_Name,g.guardian_phone 
		FROM enrollee as e 
		INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no
		INNER JOIN guardian_tbl as g ON g.patient_id = e.hospital_no
		WHERE e.hospital_no='$hospital_number' and status='active'");
			if ($stmt12->rowCount() > 0) {
				$rwxv = $stmt12->fetch(PDO::FETCH_ASSOC);

		?>


				<table class="table table-striped table-bordered" style="font-size: 15px;">
					<tr style="">
						<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Patient Detail: </td>
						<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Insurance: </td>
						<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Phone/Email: </td>
						<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Next of Kin Name: </td>
						<td width="20%" rowspan="4" class='text-right'>
							<br>
							Patient Document(s)
							<input type="button" name="" value="Upload & View Documents" data-target="#modal" id="<?php echo $rwxv['hospital_no']; ?>" class="btn btn-success doc_" />
							<?php
							if (isset($rwxv['old_hospital_no'])) {
								if ($rwxv['old_hospital_no'] != '' && $rwxv['old_hospital_no'] != null && $rwxv['old_hospital_no'] != 'NULL') {

									$category = $_SESSION['section'];
									$notes_type = $category == 'Laboratory' ? 'Lab' : 'Scan';


									echo '
							<hr/>
							<buttton class="btn btn btn-danger" id="vista_notes_modal_btn" 
								arial-data = "' . $notes_type . '"
								arial-hospitalnno = "' . $rwxv['old_hospital_no'] . '"
								>OLD EMR LAB</buttton>
						';
								} else {
									//echo 'empty';
								}
							} else {
								//echo 'not found';
							}


							?>
						</td>

					</tr>
					<tr>

						<td><?php echo $rwxv['hospital_no']; ?> / <?php echo $rwxv['surname'] . ', ' . $rwxv['fname'] . ' ' . $rwxv['oname'];
																	$name = trim($rwxv['surname'] . ' ' . $rwxv['fname'] . ' ' . $rwxv['oname']); ?></td>
						<td><?php echo $rwxv['insurance_name']; ?> / <?php echo $rwxv['insurance_type']; ?></td>
						<td><?php echo $rwxv['phone']; ?> / <?php echo $rwxv['email']; ?></td>
						<td><?php echo $rwxv['guardian_Name']; ?></td>
						<td></td>

					</tr>
					<tr style="background: #666; color: #FFF;">
						<td style="font:bold 14px 'Arial';">Age: </td>
						<td style="font:bold 14px 'Arial';">Gender: </td>
						<td style="font:bold 14px 'Arial';">Blood Group: </td>
						<td style="font:bold 14px 'Arial';">Next of Kin Phone No: </td>
					</tr>
					<tr>
						<td><?php echo $rwxv['age']; ?></td>
						<td><?php echo $rwxv['gender']; ?></td>
						<td><?php echo $rwxv['blood_g']; ?></td>
						<td><?php echo $rwxv['guardian_phone']; ?></td>

					</tr>
				</table>

		<?php
			}
		}
		?>

		<div class="alert alert-info">
			<?php echo '<strong>' . $stmt->rowCount() . ' - ' . $r_title . '</strong>'; ?>
		</div>

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
				$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($data as $key => $roww) { ?>
					<tr>
						<td><?php echo $n; ?></td>
						<?php $patient = $roww['patient']; ?>
						<td><?php $patient_name = $roww['patient_name'];
							echo $patient . ' / ' . $roww['app_no'] . '<br>' . $patient_name;
							?></td>
						<td><?php

							$app_no = $roww['app_no'];
							if ($app_no == '') {
								$app_no = ' ';
							}
							$appt_no_ = " and app_no='$app_no'";

							$hosp_no = $roww['patient'];
							$data_capture_status = 'queue';
							$preferred_specimen = $roww['preferred_specimen'];
							$type_patient = $roww['business_service_center'];
							$request_date2 = $roww['request_date2'];
							$request_date_filter = " and request_date2='$request_date2'";

							$stmtss = $db->query("SELECT d.department FROM apptm a inner join department d on d.sn=a.dept WHERE hospital_no='$hosp_no'");
							if ($stmtss->rowCount() > 0) {
								$rowx = $stmtss->fetch(PDO::FETCH_ASSOC);
								$department = $rowx['department'];
							} else {
								$department = '';
							}

							$query = list_test($db, $app_no, $hosp_no, $lab_mgt_where, $setdates, $data_capture_status, $appt_no_, $user_type, $request_date_filter);




							$requesting_physician = $query[1];
							$request_by = $query[2];
							$list_tests_sn = $query[3];
							if ($requesting_physician != '') {
								$RQ = $requesting_physician;
							} else {
								$RQ = $request_by;
							}

							$image_list = $query[6]; //// image

							if ($image_list == '') {
								$image_package = 'null';
							} else {
								$image_package =  $app_no . '____' . $hosp_no . '____' . $image_list;
							}

							$lab_list = $query[7]; //// lab								  
							if ($lab_list == '') {
								$lab_package = 'null';
							} else {
								$lab_package = $app_no . '____' . $hosp_no . '____' . $lab_list;
							}

							/// echo $lab_package;

							$date_rq = $query[8];
							///echo 'sddddddddddddd';

							$f = $query[5];
							if ($f != '') {
								echo $f = $query[5];


								if ($RQ != '') {
									$part = explode(" ", $RQ);
									echo '<br><strong>Requested by: </strong>' . $part[0] . ' ' . substr($part[1], 0, 1) . '. ';
									echo '<br>' . $date_rq;
								}
							} else {
								echo 'No Investigation(s)';
							}

							///  echo '====' . $_SESSION['section'];

							$list_tests = substr_replace($list_tests, "", -1);
							?></td>
						<td><?php echo $query[4]; ?></td>
						<td><?php echo $department; ?></td>
						<td>
							<?php if ($user_type == 'lab_img_user' and $f != '') { ?>
								<input type="button" name="" value="Medical Notes & Vitals" data-target="#modal" id="<?php echo $app_no . '____' . $hosp_no; ?>" class="btn btn-info consultations_vitals" />
							<?php } ?>


							<?php if ($_SESSION['section'] == 'Laboratory') { ?>
								&nbsp;|&nbsp;
								<input type="button" name="" value="Image" data-target="#modal" id="<?php echo $image_package; ?>" class="btn btn-primary view_results_list" />
								&nbsp;|&nbsp;

								<?php if ($f != '' or $query[4] != '') { ?>
									<input type="button" name="" value="Lab." data-target="#modal" id="<?php
																										echo $app_no . '____' . $hosp_no . '____' . $list_tests_sn . '____' . $list_tests . '____' . $patient_name . '____new____' . $type_patient; ?>" class="btn btn-success enter_results" <?php if ($user_type == 'user') { ?> disabled <?php } ?> />
								<?php } ?>



							<?php } elseif ($_SESSION['section'] == 'Radiology') { ?>
								&nbsp;|&nbsp;
								<input type="button" name="" value="Lab" data-target="#modal" id="<?php echo $lab_package; ?>" class="btn btn-primary view_results_list" />
								&nbsp;|&nbsp;

								<?php if ($f != '' or $query[4] != '') { ?>
									<input type="button" name="" value="Image" data-target="#modal" id="<?php
																										echo $app_no . '____' . $hosp_no . '____' . $list_tests_sn . '____' . $list_tests . '____' . $patient_name . '____new____' . $type_patient; ?>" class="btn btn-success enter_results" <?php if ($user_type == 'user') { ?> disabled <?php } ?> />
								<?php } ?>

							<?php }  ?>



						</td>
					</tr>
				<?php
					$n++;
				} ?>

			</tbody>
		</table>
	<?php } else { ?>
		<br>
		<div class="alert alert-danger">No Record(s) Available! <br><?php echo $r_title; ?>: </div>
<?php }
}



function list_test($db, $app_no, $hosp_no, $lab_mgt_where, $setdates, $data_capture_status, $appt_no_, $user_type, $request_date_filter)
{
	$list_tests = '';
	$list_tests_pending = '';
	$list_tests_done = '';
	$list_tests_sn = '';
	$list_test_done_image = '';
	$list_test_done_lab = '';
	$stmtx = $db->query("SELECT sn,test_name,request_by,requesting_physician,
		 business_service_center,test_id,section,request_date,request_date2,labrequest_no,request_by,
		 request_note,requesting_physician,lab_combos,preferred_specimen,collected_specimen, 
		 result_date,entered_by,data_capture_status,group_id,lab_combo_request_no FROM lab_manage 
		 WHERE patient='$hosp_no' $appt_no_ $setdates $request_date_filter order by sn DESC"); ///,  

	while ($roww_ = $stmtx->fetch(PDO::FETCH_ASSOC)) {

		//foreach ($data_ as $key_ => $roww_) {	

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



		$detail = $roww_['sn'] . '----' . $test_name . '----' . $roww_['business_service_center'] . '----' .
			$roww_['test_id'] . '----' . $roww_['section'] . '----' . $roww_['request_date'] . '----' . $roww_['labrequest_no'] . '----' .
			$request_by . '----' . $requesting_physician . '----' . $roww_['lab_combos'] . '----' . $roww_['preferred_specimen'] . '----' .
			$roww_['result_date'] . '----' . $entered_by . '----' . $roww_['data_capture_status'] . '----' . $day . '----' . $request_note . '----' .
			$roww_['collected_specimen'] . '----' . $roww_['group_id'] . '----' . $roww_['lab_combo_request_no'];

		///or section='Laboratory') and ";						
		///if($_SESSION['speciality']=='Administrator' or $_SESSION['speciality']=='Receptionist'){	


		//list_test_done_image
		//	

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



		///// ZENTH ABUJA DONT WANT TO SEE THIS BELOW //////////////////////			

		/*			$requesting_physician=$roww_['requesting_physician'];
							$request_date2='<strong>Date: </strong>' . '<strong style="color:red;">' . date( "d M Y", strtotime($roww_["request_date2"] )). '</strong>';
							$request_by=$roww_['request_by'];*/
	}
	return array(
		$list_tests, $requesting_physician, $request_by, $list_tests_sn, $list_tests_done,
		$list_tests_pending, $list_test_done_image, $list_test_done_lab, $request_date2
	);
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