<?php

$result_comment = null;
$result_type = null;
$form = null;
$specimen_collected = null;
$Specimen = null;
$data_capture_status = null;
$labrequest_no_main  = null;
$test_name = null;
$all_data = '';


session_start();
require_once('../Connections/Conn.php');

///include('../doctor/objects.php');
include('../doctor/helpers.php');

$oncredit = $_SESSION['oncredit'];
$oncredit_adm = $_SESSION['oncredit_adm'];
$investigation_edit_period = $_SESSION['investigation_edit_period'];

if ($investigation_edit_period == '' or $investigation_edit_period == 0) {
	$grace = 1;
} else {
	$grace = $investigation_edit_period;
}

if (isset($_POST['show_result_only'])) {

	$enter_results_id = $_POST["show_result_only"];
	$close_me = $_POST["close_me"];

	$pp = explode("__", $enter_results_id);
	$labrequest_no = $pp[0];
	$hospital_no = $pp[1];
	$test_id = $pp[2];
	$test_name = $pp[3];

	echo '<h2>TEST NAME: ' . $test_name . '</h2>';

	$investigation_hx_stmt = $db->prepare("SELECT data_capture_status,labrequest_no,entered_by, request_by,  request_date, 
	result_note,result_date, entered_by, approved_by, lab_combos FROM lab_manage 
		  WHERE patient = ? AND test_id = ? AND data_capture_status = 'approve' ORDER BY request_date DESC LIMIT 30");
	$investigation_hx_stmt->execute(array($hospital_no, $test_id));
	$investigation_  = $investigation_hx_stmt->fetch(PDO::FETCH_ASSOC);



	if ($investigation_['lab_combos'] == 1) {
		$cb_no = $test_id;
		$stmt2 = $db->query("SELECT c.test_id, l.test FROM lab_combos_items as c inner join lab_scan as l on c.test_id=l.sn WHERE combos_id='$cb_no'");
		while ($row_test = $stmt2->fetch(PDO::FETCH_ASSOC)) {
			$test_name = $row_test['test'];
			$test_id = $row_test['test_id'];
			include('_investigaton_results_subset.php');
		}
	} else {
		include('_investigaton_results_subset.php');
	}


?>

	<?php if ($close_me == 'view_result_list') { ?>
		<input type="button" name="" value="Close" onClick="close_dashboard_2()" data-target="#modal" class="btn btn-danger" />
	<?php } else { ?>
		<input type="button" name="" value="Close" onClick="close_dashboard()" data-target="#modal" class="btn btn-danger" />
	<?php } ?>

	<?php

	exit;
}

if (isset($_POST['load_table_items'])) {

	$show_print_button = '';
	// $app_no .'____' . $hosp_no .'____' .$list_tests_sn.'____' .$list_tests.'____' .$patient_name.'____new____' .$type_patient; 
	$enter_results_id = $_POST["load_table_items"];
	$pp = explode("____", $enter_results_id);
	$app_no = $pp[0];
	$hosp_no = $pp[1];
	$list_tests_sn = $pp[2];
	$list_tests = $pp[3];
	$patient_name = $pp[4];
	$interface = $pp[5];
	$type_patient = $pp[6];
	$list_tests_sn = substr_replace($list_tests_sn, "", -1);
	$th_arrays = explode(",", $list_tests_sn);
	$result = count($th_arrays);


	if ($result >= 1) {

		/// check if patient on-admission
		if ($oncredit == 1) {
			$enable_cr_post = 1;
		} else {
			$stmt = $db->prepare("SELECT hospital_no FROM admission WHERE hospital_no = :hosp_no AND adm_status = '3'");
			$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() > 0 && $oncredit_adm == 1) {
				$enable_cr_post = 1;
			} else {
				$enable_cr_post = 0;
			}
		}



		$n = 1;
		$result_status = '';


		for ($x = 0; $x < $result; $x++) {

			$detail = $th_arrays[$x];
			///	echo '<br>';

			$roww = explode("----", $detail);

			$test_name = $roww[1];
			$business_service_center = $roww[2];
			$test_id = $roww[3];
			$section = $roww[4];
			$requested_date = $roww[5];
			$lab_combo = $roww[9];
			$labrequest_no = $roww[6];
			$requester = $roww[7];
			$preferred_specimen = $roww[10];
			$entered_by = $roww[12];
			$request_note = $roww[15];
			$data_capture_status = $roww[13];
			$collected_specimen = $roww[16];
			$group_id = $roww[17];
			$lab_combo_request_no = $roww[18];

			if ($lab_combo == 1) {
				$cb_no = $test_id;
				$stmt2 = $db->prepare("SELECT c.test_id, l.test, l.category, l.dept 
	FROM lab_combos_items AS c 
	INNER JOIN lab_scan AS l ON c.test_id = l.sn 
	WHERE combos_id = :cb_no");

				$stmt2->bindParam(':cb_no', $cb_no, PDO::PARAM_STR);
				$stmt2->execute();

				$nn = 1;
				while ($row_test = $stmt2->fetch(PDO::FETCH_ASSOC)) {
					$test_name = $row_test['test'];
					$test_sn = $row_test['test_id'];
					$section = $row_test['category'];
					$dept_id = $row_test['dept'];

					$stmt_lck = $db->prepare("SELECT labrequest_no FROM lab_manage WHERE patient = :hosp_no AND test_id = :test_sn AND lab_combo_request_no = :labrequest_no");
					$stmt_lck->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
					$stmt_lck->bindParam(':test_sn', $test_sn, PDO::PARAM_STR);
					$stmt_lck->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
					$stmt_lck->execute();

					if ($stmt_lck->rowCount() == 0) {

						$empty = '';
						$emty_zero = '0';
						$labrequest_no_2 = $labrequest_no . $nn;
						$sql = $db->prepare("INSERT INTO lab_manage (app_no,labrequest_no,patient,patient_name,test_id,test_name,lab_cat,
			section,group_id,business_service_center,referral,preferred_specimen,request_note,request_date,request_by,lab_combos,
			requesting_physician,lab_combo_request_no) 
VALUES (:app_no,:labrequest_no,:patient,:patient_name,:test_id,:test_name,:lab_cat,
			:section,:group_id,:business_service_center,:referral,:preferred_specimen,:request_note,:request_date,:request_by,:lab_combos,
			:requesting_physician,:lab_combo_request_no)");
						$sql->bindParam(':app_no', $app_no, PDO::PARAM_STR);
						$sql->bindParam(':labrequest_no', $labrequest_no_2, PDO::PARAM_STR);
						$sql->bindParam(':patient', $hosp_no, PDO::PARAM_STR);
						$sql->bindParam(':patient_name', $patient_name, PDO::PARAM_STR);
						$sql->bindParam(':test_id', $test_sn, PDO::PARAM_STR);
						$sql->bindParam(':test_name', $test_name, PDO::PARAM_STR);
						$sql->bindParam(':lab_cat', $dept_id, PDO::PARAM_STR);
						$sql->bindParam(':section', $section, PDO::PARAM_STR);
						$sql->bindParam(':group_id', $group_id, PDO::PARAM_STR);
						$sql->bindParam(':business_service_center', $business_service_center, PDO::PARAM_STR);
						$sql->bindParam(':referral', $emty, PDO::PARAM_STR);
						$sql->bindParam(':preferred_specimen', $collected_specimen, PDO::PARAM_STR);
						$sql->bindParam(':request_note', $request_note, PDO::PARAM_STR);
						$sql->bindParam(':request_date', $requested_date, PDO::PARAM_STR);
						$sql->bindParam(':request_by', $requester, PDO::PARAM_STR);
						$sql->bindParam(':lab_combos', $emty_zero, PDO::PARAM_STR);
						$sql->bindParam(':requesting_physician', $entered_by, PDO::PARAM_STR);
						$sql->bindParam(':lab_combo_request_no', $labrequest_no, PDO::PARAM_STR);
						$sql->execute();


						$data = $test_sn . '__' . $test_name . '__' . $labrequest_no_2 . '__' . $preferred_specimen . '__' . $entered_by . '__' . $lab_combo . '__' . $request_note . '__' . $data_capture_status . '__' . $collected_specimen . '__' . $section . '__' . $labrequest_no;
						$all_data .= $data . ',';
					} else {
						$row_test_ = $stmt_lck->fetch(PDO::FETCH_ASSOC);
						$labrequest_no_2 = $row_test_['labrequest_no'];

						$data = $test_sn . '__' . $test_name . '__' . $labrequest_no_2 . '__' . $preferred_specimen . '__' . $entered_by . '__' . $lab_combo . '__' . $request_note . '__' . $data_capture_status . '__' . $collected_specimen . '__' . $section . '__' . $labrequest_no;
						$all_data .= $data . ',';
					}


					$nn++;
				}
			} elseif ($lab_combo_request_no == '') {
				$data = $test_id . '__' . $test_name . '__' . $labrequest_no . '__' . $preferred_specimen . '__' . $entered_by . '__' . $lab_combo . '__' . $request_note .
					'__' . $data_capture_status . '__' . $collected_specimen . '__' . $section . '__' . $labrequest_no;
				$all_data .= $data . ',';
			}
		}

		//}



	?>
		<form method="post" action="printlab.php" id="" name="">

			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>#</th>
						<th>Dates</th>
						<th>Investigation</th>
						<th>Requester/Approver</th>
						<th>.</th>
						<th>Status</th>
						<th></th>
						<th></th>
					</tr>

				</thead>
				<tbody>

					<?php
					$n = 1;
					$result_status = '';

					//echo $all_data;


					$all_data = rtrim($all_data, ", ");
					$myArray = explode(',', $all_data);

					foreach ($myArray as $value) {
						///echo "$value <br>";
						$roww = explode("__", $value);

						$test_id = $roww[0];
						$test_name = $roww[1];
						$labrequest_no = $roww[2];
						$preferred_specimen = $roww[3];
						$entered_by = $roww[4];
						$lab_combo = $roww[5];
						$request_note = ''; ////= $roww[6]; Disabled bcos of Remarks causing pop -up 
						$data_capture_status = $roww[7];
						$collected_specimen = $roww[8];
						$section = $roww[9];
						$lab_combo_request_no = $roww[10];

						$test_no = $test_id;

						$detail = $th_arrays[$x];
						$roww = explode("----", $detail);
						///$_detail=$roww[3].'__'.$roww[1].'__'.$roww[6].'__'.$roww[10].'__'.$roww[12].'__'.$roww[9].'__'.$roww[15].'__'.$roww[13].'__'.$roww[16];
						$_detail = $test_id . '__' . $test_name . '__' . $labrequest_no . '__' . $preferred_specimen . '__' .
							$entered_by . '__' . $lab_combo . '__' . $request_note . '__' . $data_capture_status . '__' . $collected_specimen;
						///	echo '<br>';

						///$lab_combos = $roww[9];

						$stmt_list = $db->prepare("SELECT data_capture_status, result_date, request_date, approved_by, request_by 
	FROM lab_manage 
	WHERE labrequest_no = :labrequest_no AND app_no = :app_no");

						$stmt_list->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
						$stmt_list->bindParam(':app_no', $app_no, PDO::PARAM_STR);
						$stmt_list->execute();

						if ($stmt_list->rowCount() > 0) {
							$row = $stmt_list->fetch(PDO::FETCH_ASSOC);
							$data_capture_status = $row['data_capture_status'];
							$result_date = $row['result_date'];
							$request_date = $row['request_date'];
							$approved_by = $row['approved_by'];
							$request_by = $row['request_by'];

							/*	if($lab_combo==1){
		$stmt_yz=$db->query("SELECT * FROM lab_result WHERE lab_no='$labrequest_no' and test_no='$test_no'");
			if($stmt_yz->rowCount()>0){
					if($data_capture_status=='approve'){
						$data_capture_status = 'approve';
					}else{
						$data_capture_status = 'result';
					}
			
			
		}
	}*/

							$Current_date = date("Y-m-d");
							$date1 = new DateTime($Current_date);
							$date2 = new DateTime($result_date);
							$diff = $date2->diff($date1);
							$day = $diff->format('%a');


							if ($result_date != '' and $data_capture_status != 'queue') {
								$rslt_date = date('d,M y h:i a', strtotime($result_date));
							} else {
								$rslt_date = '';
							}

							$rq_date = date('d,M y h:i a', strtotime($request_date));

							if ($data_capture_status == 'result' and $_SESSION['approve'] == '1') {
								$result_status = 'yes';
							}
						} else {
							$data_capture_status = '';
							$result_date = '';
							$request_date = '';
							$approved_by = '';
						}





					?>
						<tr>
							<td><?php echo $n; ?></td>
							<td><?php echo '<B>REQ: </B>' . $rq_date . '<br>' . '<B>APR: </B>' . $rslt_date; ?></td>
							<td><?php if ($lab_combo == 1) {
									echo '<strong>C: </strong>';
								}
								echo $test_name;
								echo '<br>';

								if ($data_capture_status == 'queue') {
									echo '<strong>[ Queue ]</strong>';
									$button_tilte = 'New Result';
									$button_color = 'success';
									$test_status = '0';
								} elseif ($data_capture_status == 'specimen') {
									echo '<strong>[ Specimen Taken ]</strong>';
									$button_tilte = 'New Result';
									$button_color = 'success';
									$test_status = '0';
								} elseif ($data_capture_status == 'result') {
									echo '<strong>[ Approve Pending ]</strong>';
									$button_tilte = 'Edit';
									$button_color = 'warning';
									$test_status = '1';
								} else {
									echo '<strong>[ Approved ]</strong>';
									$button_tilte = 'Edit';
									$button_color = 'warning';
									$test_status = '1';
								}

								?></td>
							<td><?php echo $request_by . '<br>' . $approved_by; ?></td>
							<td>
								<input type="button" name="edit" value="Notes " data-target="#modal" id="<?php echo $labrequest_no; ?>" class="btn btn-success btn-xs view_notes" <?php if ($_SESSION['speciality'] == 'Administrator' or $_SESSION['speciality'] == 'Receptionist') { ?> disabled <?php } ?> />
							</td>
							<td>




								<?php
								/// check if 


								$stmt_list = $db->prepare("SELECT paystatus, pay_mode, cr, sn 
		FROM patient_ap_services 
		WHERE drug_sn = :lab_combo_request_no");

								$stmt_list->bindParam(':lab_combo_request_no', $lab_combo_request_no, PDO::PARAM_STR);
								$stmt_list->execute();

								if ($stmt_list->rowCount() > 0) {
									$row = $stmt_list->fetch(PDO::FETCH_ASSOC);
									$paystatus = $row['paystatus'];
									$pay_mode = $row['pay_mode'];
									$cr = $row['cr'];
									$sn_ = $row['sn'];
								} else {
									$pay_lock = 0;
								}

								$stmt = $db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE hospital_no='$hosp_no' and account_no='2121'");
								if ($stmt->rowCount() > 0) {
									$row = $stmt->fetch(PDO::FETCH_ASSOC);
									$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
									$TOTAL_DEBITS = $row['TOTAL_DEBITS'];
									$current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
								}

								$pay_lock = 0;
								if ($paystatus == 1 and $pay_mode == 'claim') { ?>
									<strong style="color:#009">Posted</strong>
								<?php } elseif ($paystatus == 1) { ?>
									<strong style="color:#009">Paid</strong>
								<?php } else {

									if ($enable_cr_post == 1) {

										if ($data_capture_status == 'approve' or $data_capture_status == 'result') {
											$b_title = 'Posted On-Credit';
											$button_tilte = 'Edit';
											$button_color = 'warning';
										} else {
											$b_title = 'Not Paid/Bill(Cr)';
											$button_tilte = 'Result On-Credit';
											$button_color = 'danger';
										}

										$pay_lock = 0;
									} elseif ($data_capture_status == 'queue') {
										$pay_lock = 1;
										$b_title = 'Not Paid';
									} else {
										$pay_lock = 0;
										$b_title = 'Not Paid';
									}

								?>

									<?php if ($current_balance > 0 and $_SESSION['payfrom_status'] == 1) { ?>
										<button type="button" class="btn btn-warning btn-xs dropdown-toggle" id="pay_now<?php echo $sn_; ?>" onClick="payNow('<?php echo $sn_; ?>','investigation','<?= $hosp_no; ?>')">Pay from Wallet</button>
									<?php } ?>

									<strong style="color: #F00"><?= $b_title; ?></strong>
								<?php } ?>

								<?php
								$view_status = '';
								if ($data_capture_status == 'approve' and $day > $grace) {
									$pay_lock = 1;
									$view_status = 'yes';
									echo $edit_status = " <br>[Exceed:" . $grace . " days]";
								} else {
									$edit_status = '';
								}

								if ($interface == 'print' and $data_capture_status == 'result') {
									$pay_lock = 1;
									echo $edit_status = ' [Approve Pending]';
								} else {
									$edit_status = 'yes';
								}

								?>




							</td>

							<td>
								<?php if ($section == $_SESSION['section']) {


								?>
									<input type="button" name="edit" value="<?= $button_tilte; ?>" <?php if ($pay_lock == 1) { ?>disabled <?php } ?> onClick=" show_result_sheet('<?php echo $_detail . '__' . $paystatus . '__' . $cr . '__' . $test_status . '__' . $lab_combo_request_no; ?>')" class="btn btn-<?= $button_color; ?> btn-xs" />
								<?php } ?>


								<?php if (($section == 'Radiology' or $_SESSION['speciality'] == 'Administrator' or $_SESSION['speciality'] == 'Receptionist')
									and $data_capture_status == 'approve'
								) { ?>
									<!-- check if it is an external patient -->
									<?php if (strpos($labrequest_no, 'EX')) : ?>
										&nbsp;|&nbsp; <a href="printscan.php?e=<?php echo $labrequest_no; ?>" class="btn btn-primary btn-xs">Print/Email</a>
									<?php else : ?>
										&nbsp;|&nbsp; <a href="printscan.php?i=<?php echo $labrequest_no; ?>" class="btn btn-primary btn-xs">Print/Email</a>
									<?php endif ?>

								<?php } ?>

								<?php if ($view_status == 'yes') { ?>
									<input type="button" name="edit" value="View" onClick=" view_result_only('<?php echo $labrequest_no . '__' . $hosp_no . '__' . $test_no . '__' . $test_name; ?>')" class="btn btn-success btn-xs" />
								<?php } ?>
							</td>
							<td>


								<?php if ($test_status == 1 and $section == 'Laboratory' and ($data_capture_status == 'approve' or $data_capture_status == 'result')) {
									$show_print_button = 'yes';
								?>
									<input type="checkbox" value="<?php echo $_detail . '__' . $paystatus . '__' . $cr; ?>" name="inv[]" style="display:block; height:18px; width:18px;">
								<?php } ?>
							</td>



						</tr>
					<?php $n++;
					} ?>

				</tbody>
			</table>


		<?php } else { ?>


		<?php } ?>





		<?php if (
			$show_print_button == 'yes' and
			($_SESSION['speciality'] == 'Administrator' or $_SESSION['speciality'] == 'Receptionist' or $_SESSION['section'] == 'Laboratory')
		) { ?>

			<button type="submit" class="btn btn-success" name="display_result_print">Print / Email Result</button>
		<?php } ?>
		<input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>">
		<input type="hidden" name="type_patient" value="<?php echo $type_patient; ?>">
		<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>">
		<input type="hidden" name="hospital_no" value="<?php echo $hosp_no; ?>">
		<input type="hidden" name="app_no" value="<?php echo $app_no; ?>">
		</form>

	<?php exit;
}



if (isset($_POST['speciment_taken'])) {

	/// speciment_taken:speciment_taken,labrequest_no:labrequest_no, test_id:test_id

	$speciment_taken = $_POST['speciment_taken'];
	$test_id = $_POST['test_id'];
	$labrequest_no = $_POST['labrequest_no'];
	$data_capture_status = 'specimen';
	$collected_date = date('Y-m-d');

	$updateSQL = "UPDATE lab_manage SET data_capture_status=:data_capture_status,
collected_specimen=:collected_specimen,
collected_by=:collected_by,collected_date=:collected_date
WHERE labrequest_no=:labrequest_no";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':data_capture_status', $data_capture_status, PDO::PARAM_STR);
	$sql->bindParam(':collected_specimen', $speciment_taken, PDO::PARAM_STR);
	$sql->bindParam(':collected_by', $_SESSION['fullname'], PDO::PARAM_STR);
	$sql->bindParam(':collected_date', $collected_date, PDO::PARAM_STR);
	$sql->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	$sql->execute();
}



if (isset($_POST['mgt_notes'])) {


	if ($_SESSION['speciality'] == "Data Operator") {
		$entered_by = $_SESSION['fullname'];
		$lab_sci_name = '';
		$lab_sci_speciality = '';
	} else {
		$entered_by = $_SESSION['fullname'];
		$lab_sci_name = $_SESSION['fullname'];
		$lab_sci_speciality = $_SESSION['speciality'];
	}
	$result_date = date("Y-m-d H:i:s");
	$RQ_type = 'sl';
	$comment = $_POST['comment'];
	$notes = '';


	$labrequest_no = $_POST['labrequest_no'];
	$labrequest_no_main = $_POST['labrequest_no_main'];
	$test_no = $_POST['test_id'];
	$paystatus = $_POST['paystatus'];
	$speciment_taken = $_POST['speciment_taken'];
	$cr = $_POST['cr'];



	$approve_status = $_POST["approve_result"];
	if ($approve_status == 1) {
		$data_capture_status = 'approve';
		$approved_by = $_SESSION['fullname'];
	} else {
		$data_capture_status = 'result';
		$approved_by = '';
	}


	$test_id = $_POST['test_id'];
	$stmt = $db->query("SELECT * FROM lab_scan_fields WHERE test_no='$test_id'");
	if ($stmt->rowCount() == 0) {
		$field_type = 'report';
		$sql = $db->prepare("INSERT INTO lab_scan_fields (test_no,field,field_type) VALUES (:test_no,:field,:field_type)");
		$sql->bindParam(':test_no', $_POST['test_id'], PDO::PARAM_STR);
		$sql->bindParam(':field', $_POST['test_name'], PDO::PARAM_STR);
		$sql->bindParam(':field_type', $field_type, PDO::PARAM_STR);
		$sql->execute();
	}

	$stmt_chk = $db->query("SELECT * FROM lab_result WHERE lab_no='$labrequest_no' and test_no='$test_no'");
	if ($stmt_chk->rowCount() == 0) {

		$sql = $db->prepare("INSERT INTO lab_result (field_value,lab_no,test_no,test_name,specimen_collected,comment,notes,RQ_type,result_date,lab_sci_name,lab_sci_speciality,
				entered_by) VALUES (:field_value,:lab_no,:test_no,:test_name,:specimen_collected,:comment,:notes,:RQ_type,:result_date,:lab_sci_name,:lab_sci_speciality,
				:entered_by)");

		$sql->bindParam(':field_value', $_POST['mgt_notes'], PDO::PARAM_STR);
		$sql->bindParam(':lab_no', $_POST['labrequest_no'], PDO::PARAM_STR);
		$sql->bindParam(':test_no', $_POST['test_id'], PDO::PARAM_STR);
		$sql->bindParam(':test_name', $_POST['test_name'], PDO::PARAM_STR);
		$sql->bindParam(':specimen_collected', $_POST['speciment_taken'], PDO::PARAM_STR);
		$sql->bindParam(':comment', $comment, PDO::PARAM_STR);
		$sql->bindParam(':notes', $notes, PDO::PARAM_STR);
		$sql->bindParam(':RQ_type', $RQ_type, PDO::PARAM_STR);
		$sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
		$sql->bindParam(':lab_sci_name', $lab_sci_name, PDO::PARAM_STR);
		$sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
		$sql->bindParam(':entered_by', $entered_by, PDO::PARAM_STR);
		$sql->execute();
	} else {

		$updateSQL = "UPDATE lab_result SET field_value=:field_value,lab_sci_name=:lab_sci_name,
	lab_sci_speciality=:lab_sci_speciality,entered_by=:entered_by,result_date=:result_date,comment=:comment WHERE lab_no=:lab_no and test_no=:test_no";
		$sql = $db->prepare($updateSQL);
		$sql->bindParam(':field_value', $_POST['mgt_notes'], PDO::PARAM_STR);
		$sql->bindParam(':lab_sci_name', $lab_sci_name, PDO::PARAM_STR);
		$sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
		$sql->bindParam(':entered_by', $entered_by, PDO::PARAM_STR);
		$sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
		$sql->bindParam(':comment', $comment, PDO::PARAM_STR);
		$sql->bindParam(':lab_no', $labrequest_no, PDO::PARAM_STR);
		$sql->bindParam(':test_no', $test_no, PDO::PARAM_STR);

		$sql->execute();
		////
		$sql = $db->prepare("INSERT INTO lab_result_old (field_value,lab_no,test_no,test_name,comment,RQ_type,result_date,lab_sci_name,lab_sci_speciality,entered_by) 
	VALUES (:field_value,:lab_no,:test_no,:test_name,:comment,:RQ_type,:result_date,:lab_sci_name,:lab_sci_speciality,:entered_by)");

		$sql->bindParam(':field_value', $_POST['mgt_notes'], PDO::PARAM_STR);
		$sql->bindParam(':lab_no', $_POST['labrequest_no'], PDO::PARAM_STR);
		$sql->bindParam(':test_no', $_POST['test_id'], PDO::PARAM_STR);
		$sql->bindParam(':test_name', $_POST['test_name'], PDO::PARAM_STR);
		$sql->bindParam(':comment', $comment, PDO::PARAM_STR);
		$sql->bindParam(':RQ_type', $RQ_type, PDO::PARAM_STR);
		$sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
		$sql->bindParam(':lab_sci_name', $lab_sci_name, PDO::PARAM_STR);
		$sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
		$sql->bindParam(':entered_by', $entered_by, PDO::PARAM_STR);
		$sql->execute();
	}
	$sms_ = 0;
	$updateSQL = "UPDATE lab_manage SET result_note=:result_note,lab_sci_name=:lab_sci_name,lab_sci_speciality=:lab_sci_speciality,
	entered_by=:entered_by,result_date=:result_date,data_capture_status=:data_capture_status,approved_by=:approved_by ,sms_status=:sms_status 
	WHERE labrequest_no=:labrequest_no";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':result_note', $_POST['mgt_notes'], PDO::PARAM_STR);
	$sql->bindParam(':lab_sci_name', $lab_sci_name, PDO::PARAM_STR);
	$sql->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
	$sql->bindParam(':entered_by', $entered_by, PDO::PARAM_STR);
	$sql->bindParam(':result_date', $result_date, PDO::PARAM_STR);
	$sql->bindParam(':data_capture_status', $data_capture_status, PDO::PARAM_STR);
	$sql->bindParam(':approved_by', $approved_by, PDO::PARAM_STR);
	$sql->bindParam(':sms_status', $sms_, PDO::PARAM_STR);
	$sql->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	$sql->execute();


	if ($paystatus == 0) {
		$credit_status = '1';
	} else {
		$credit_status = '0';
	}
	$one = '1';

	$invoice_no = mt_rand(1000000, 9999999);
	$updateSQL = "UPDATE patient_ap_services 
			SET cr=:cr,
			invoice_no=:invoice_no,
			invoice_by=:invoice_by,
			drug_status=:drug_status,
			invoice_status=:invoice_status
					WHERE drug_sn=:labrequest_no";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':cr', $credit_status, PDO::PARAM_STR);
	$sql->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
	$sql->bindParam(':invoice_by', $_SESSION["fullname"], PDO::PARAM_STR);
	$sql->bindParam(':drug_status', $one, PDO::PARAM_STR);
	$sql->bindParam(':invoice_status', $one, PDO::PARAM_STR);
	$sql->bindParam(':labrequest_no', $labrequest_no_main, PDO::PARAM_STR);
	$sql->execute();
}


if (isset($_POST['details'])) {

	$response_main = array(
		'test_id' => '',
		'test_name' => '',
		'specimen_collected' => '',
		'labrequest_no' => '',
		'labrequest_no_main' => '',
		'cr' => '',
		'paystatus' => '',
		'test_status' => '',
		'data_capture_status' => '',
		'result_note' => '',
		'section' => '',
		'form' => '',
		'result_type' => '',
		'result_comment' => '',
		'Specimen' => ''
	);


	////	1060__ANTI PLATELET ANTIBODY*__LB120000210000000112__Not Specified____1____queue____0__1__0__LB12000021000000011
	/// $_detail.'__'.$paystatus.'__'.$cr.'__'.$test_status.'__'.$lab_combo_request_no;


	///$labrequest_no = 'LB120000210000000112';

	$main_data = $_POST['details'];
	$row_test = explode("__", $main_data);
	$test_id = $row_test[0];
	$test_name = $row_test[1];
	$labrequest_no = $row_test[2];
	$Specimen = $row_test[3];
	$nameof_patient = $row_test[4];
	$lab_combo = $row_test[5];
	$request_note = $row_test[6];
	$data_capture_status = $row_test[7];
	$empty = $row_test[8];
	$paystatus = $row_test[9];
	$cr = $row_test[10];
	$test_status_investigate = $row_test[11];
	$labrequest_no_main = $row_test[12];

	$stmt_list3 = $db->query("SELECT * FROM lab_scan_fields WHERE test_no='$test_id'");
	$row_fields = $stmt_list3->fetch(PDO::FETCH_ASSOC);
	$field_no = $row_fields['sn'];
	$field_type = $row_fields['field_type'];
	$field = $row_fields['field'];


	if ($test_status_investigate == 1) {
		$field_value = '';
		$stmt_list = $db->query("SELECT * FROM lab_result WHERE test_no='$test_id' and lab_no='$labrequest_no'");
		if ($stmt_list->rowCount() > 0) {
			$row = $stmt_list->fetch(PDO::FETCH_ASSOC);

			$rslt_spm = $row['specimen_collected'];
			$field_value = $row['field_value'];
			$result_comment = $row['comment'];
			$result_note = $row['notes'];
			$result_date = $row['result_date'];
			$response_main['test_status'] = 1;
			$response_main['result_note'] = $field_value;
			$edit = 1;
		} else {
			$field_value = '';
			$edit = 0;
		}
	}
}

if ($row_fields['reference'] != '') {
	$Reference = 'Reference' . '( ' . $row_fields['reference'] . ' )';
} else {
	$Reference = '';
}

$title_head = '';

$title_head .= '<table width="100%"><tr><td><label for="reg_input_no" class="">' . $field;
$title_head .= '</label></td><td align="right"><div align="right"><label for="reg_input_no" style="text-align:right">' . $Reference;
$title_head .= '</label></div></td></tr></table>';

if ($row_fields['field_type'] == 'options') {

	$opt = $title_head;
	$opt .= '<select name="option_input" id="option_input" class="form-control" data-required="true">';

	$stmt_opt = $db->query("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no='$test_id'");
	if ($stmt_opt->rowCount() > 0) {
		if ($edit == 1) {
			$opt .= '<option selected="selected" value="' . $field_value . '">' . $field_value . '</option> ';
		} else {
			$opt .= '<option selected="selected" value="">Select ...</option>';
		}

		while ($row_opt = $stmt_opt->fetch(PDO::FETCH_ASSOC)) {
			$options = $row_opt['options'];
			$opt .= ' <option value="' . $options . '">' . $options . '</option>';
		}

		$opt .= '</select>';
	}
	$form = $opt;
	$result_type = 'options';
} elseif ($row_fields['field_type'] == 'value') {

	$form = $title_head;
	//if($edit==1 and $field_type=='value'){=$field_value;} 
	$form .= '<input type="text" name="single_input" id="single_input" class="form-control" value="' . $field_value . '" placeholder="Enter result for' . $field . '"/>';
	$result_type = 'single';
} elseif ($row_fields['field_type'] == 'report') {

	$stmt_list = $db->query("SELECT template_2 FROM invsti_template WHERE sn='$test_id'");
	if ($stmt_list->rowCount() > 0) {
		$row = $stmt_list->fetch(PDO::FETCH_ASSOC);

		if ($edit == 1) {
			$form = $field_value; ///['template_2'];
		} else {
			$form = $row['template_2'];
		}

		$result_type = 'single_report';
	} else {
		$result_type = 'report';
	}
} else {

	//// REPORT TEMPLATE ///	


	$result_type = 'report';
}


$stmt_list = $db->prepare("SELECT collected_specimen 
                          FROM lab_manage 
                          WHERE test_id = :test_id 
                          AND labrequest_no = :labrequest_no");

$stmt_list->bindParam(':test_id', $test_id, PDO::PARAM_STR);
$stmt_list->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
$stmt_list->execute();

if ($stmt_list->rowCount() > 0) {
	$row = $stmt_list->fetch(PDO::FETCH_ASSOC);
	$specimen_collected = $row['collected_specimen'];
}
$response_main['test_id'] = $test_id;
$response_main['test_name'] = $test_name;
$response_main['labrequest_no'] = $labrequest_no;
$response_main['labrequest_no_main'] = $labrequest_no_main;
$response_main['data_capture_status'] = $data_capture_status;
$response_main['cr'] = $cr;
$response_main['Specimen'] = $Specimen;
$response_main['paystatus'] = $paystatus;
$response_main['specimen_collected'] = $specimen_collected;
$response_main['section'] = $_SESSION['section'];
$response_main['result_comment'] = $result_comment; ///['section'];
$response_main['result_type'] = $result_type; ///['section'];
$response_main['form'] = $form; ///['section'];
echo  json_encode($response_main);


	?>