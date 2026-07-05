<?php
session_start();
require_once('../Connections/Conn.php');
include("../inc/credit_current_balance.php");

$result_comment = null;
$result_type = null;
$form = null;
$specimen_collected = null;
$Specimen = null;
$data_capture_status = null;
$labrequest_no_main  = null;
$test_name = null;
$all_data = '';
$oncredit = $_SESSION['oncredit'];
$oncredit_adm = $_SESSION['oncredit_adm'];
$investigation_edit_period = $_SESSION['investigation_edit_period'];
$vrlst = $_SESSION['view_lab_result'];
$rights = $_SESSION['rights'];

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

	$investigation_hx_stmt = $db->prepare("SELECT data_capture_status,labrequest_no,entered_by, request_by, result_comment, request_date, 
	result_note,result_date, entered_by, approved_by, lab_combos FROM lab_manage 
		  WHERE patient = ? AND test_id = ? AND data_capture_status = 'approve' and labrequest_no=?  ORDER BY request_date DESC LIMIT 30");
	$investigation_hx_stmt->execute(array($hospital_no, $test_id, $labrequest_no));
	$investigation_  = $investigation_hx_stmt->fetch(PDO::FETCH_ASSOC); ?>
	<div class="row">
		<div class="col-md-7 text-left">
			<?php
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
			} ?>
		</div>
		<div class="col-md-5 text-right">
			<div class="form_sep">
				<label for="reg_input_no" class="">ADD COMMENT</label>
				<textarea name="comment" id="more_comment" cols="15" rows="10" class="form-control" data-minlength="10" placeholder="Enter result for COMMENT"></textarea>
			</div>
		</div>


		<input type="hidden" value="<?= $labrequest_no ?>" id="more_labrequest_no" name="labrequest_no">
		<input type="hidden" value="<?= $_SESSION['fullname'] ?>" id="who_is_add" name="who_is_add">
		<button type="button" class="btn btn-success btn-xs" onClick="add_more_commment()">Add Comment</button>
	</div>



	<?php if ($close_me == 'view_result_list') { ?>
		<input type="button" name="" value="Close" onClick="close_dashboard_2()" data-target="#modal" class="btn btn-danger" />
	<?php } else { ?>
		<input type="button" name="" value="Close" onClick="close_dashboard()" data-target="#modal" class="btn btn-danger" />
	<?php } ?>

	<?php

	exit;
}

if (isset($_POST['load_table_items'])) {

	/// CHECK HERE FOR 
	$Combo_status = null;
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

		// Check if patient is on general credit
		$enable_cr_post = ($oncredit_adm == 1) ? 1 : 0;

		// Get current balance and credit limit
		$emr = $hosp_no;
		$general_credit_limit = $_SESSION['credit_limit_status'];
		$items = call_current_balance($db, $emr, $general_credit_limit);
		$current_balance =  $items["current_balance"];
		$credit_limit  = $items['bal_credit_limit'];
		$credit_limit_bal  = $items['bal_credit_limit'];
		$Total_total_credits  = $items['Total_total_credits'];
		$admitted_dept  = $items['admitted_dept'];

		// Display credit balance if applicable
		if ($admitted_dept != null) {
			echo '<h4 style="color:blue;">On-Admission</h4>'; // Use htmlspecialchars for security
		}
		if ($credit_limit <= 0) {
			echo '<h3 style="color:red;">Credit Balance: ' . htmlspecialchars($credit_limit) . '</h3>'; // Use htmlspecialchars for security
		}



		if (!empty($hosp_no)) {

			$sql = "SELECT * 
            FROM tbl_patient_alerts
            WHERE hospital_no = :hospital_no
            AND alert != '' AND status='1'";

			$stmt = $db->prepare($sql);
			$stmt->execute(array(':hospital_no' => $hosp_no));

			$patient_alerts = $stmt->fetchAll(PDO::FETCH_OBJ);

			if (!empty($patient_alerts)) {

				echo "<div class='alert alert-danger shadow-sm' style='border-left:6px solid red;'>";

				echo "<h4 style='margin-bottom:15px; font-weight:bold;'>
                ⚠️ Patient Alerts ⚠️
              </h4>";

				foreach ($patient_alerts as $alert) {

					echo "<div class='mb-3 p-3 bg-light rounded' style='font-size:20px;color:black;'>";

					echo htmlentities($alert->alert);

					echo "</div>";

					if (!empty($alert->created_at) && strtotime($alert->created_at)) {

						$formattedDate = date('d M Y, h:i a', strtotime($alert->created_at));

						echo "<small class='text-muted d-block mb-3'>
                        {$formattedDate} by " . htmlentities($alert->created_by) . "
                      </small>";
					} else {

						echo "<small class='text-muted d-block mb-3'>
                        by " . htmlentities($alert->created_by) . "
                      </small>";
					}
				}

				echo "</div>";
			}
		}


















		$n = 1;
		$result_status = '';
		$all_data = '';

		// Prepare statements once outside the loop
		$stmtLabCombos = $db->prepare("
			SELECT c.test_id, l.test, l.category, l.dept 
			FROM lab_combos_items AS c
			INNER JOIN lab_scan AS l ON c.test_id = l.sn 
			WHERE combos_id = :cb_no
		");

		$stmtLabManageCheck = $db->prepare("
			SELECT labrequest_no, bill 
			FROM lab_manage 
			WHERE patient = :hosp_no AND test_id = :test_sn AND lab_combo_request_no = :labrequest_no
		");


		$insertLabManage = $db->prepare("
			INSERT INTO lab_manage (
				app_no, labrequest_no, patient, patient_name, test_id, test_name, lab_cat, section, group_id, 
				business_service_center, referral, preferred_specimen, request_note, request_date,request_date2, request_by, 
				lab_combos, requesting_physician, lab_combo_request_no,date_time_pay,data_capture_status,result_date,approved_by
			) VALUES (
				:app_no, :labrequest_no, :patient, :patient_name, :test_id, :test_name, :lab_cat, :section, :group_id, 
				:business_service_center, :referral, :preferred_specimen, :request_note, :request_date,:request_date2, :request_by, 
				:lab_combos, :requesting_physician, :lab_combo_request_no, :date_time_pay, :data_capture_status, :result_date, :approved_by
			)
		");

		$updateLabManage = $db->prepare("UPDATE lab_manage SET lab_combos = 0 WHERE test_id = :test_id AND patient = :hosp_no AND labrequest_no = :labrequest_no");
		$updateLabCombo_status = $db->prepare("UPDATE lab_manage SET sms_status = 1 WHERE test_id = :test_id AND patient = :hosp_no AND labrequest_no = :labrequest_no AND lab_combos=1"); /// EXTRACTED 


		for ($x = 0; $x < $result; $x++) {
			$detail = $th_arrays[$x];
			$roww = explode("----", $detail);

			///9027----4D obstetrics scan----IN----57----Radiology----
			///2025-07-01 10:49:10----LB01542998990230131972----
			///Super Admin----
			//--8--0----//----//----//----queue----0----//----//----002804----
			///LB0154299899023013197----//----//----//----
			/*
			$roww_['sn'] 0 . '----' . $test_name 1 . '----' . $roww_['business_service_center'] 2 . '----' .
			$roww_['test_id'] 3 . '----' . $roww_['section'] 4 . '----' . $roww_['request_date'] 5 . '----' .
			$roww_['labrequest_no'] 6 . '----' .	$request_by 7 . '----' . $requesting_physician 8 . '----' .
			$roww_['lab_combos'] 9 . '----' . $preferred_specimen 10 . '----' .
			$roww_['result_date'] 11 . '----' . $entered_by 12 . '----' . $roww_['data_capture_status'] 13 . '----' .
			$day 14 . '----' . $request_note 15 . '----' .
			$roww_['collected_specimen'] 16 . '----' . $roww_['group_id'] 17 . '----' . $roww_['lab_combo_request_no'] 18 . '----' .
			$roww_['approved_by'] . '----' . $roww_['bill'] . '----' . $roww_['amount'] . '----' . $roww_['attachment'];
			*/

			// Extract variables once
			$test_name = $roww[1];
			$business_service_center = $roww[2];
			$test_id = $roww[3];
			$section = $roww[4];
			$request_date = $roww[5];
			$requested_date = $roww[5];
			$labrequest_no = $roww[6];
			$requester = $roww[7];
			$request_by = $roww[7];
			$lab_combo = $roww[9];
			$preferred_specimen = $roww[10];
			$result_date = $roww[11];
			$entered_by = $roww[12];
			$data_capture_status = $roww[13];
			$request_note = $roww[15];
			$collected_specimen = $roww[16];
			$group_id = $roww[17];
			$lab_combo_request_no = $roww[18];
			$approved_by = $roww[19];
			$bill = $roww[20];
			$amount = $roww[21];
			$attachment = $roww[22];
			$date_time_pay = $roww[23];
			$combo_Extract_status = $roww[24]; /// SMS_STATUS  /// CONVERTED CHECK COMBO TEST EXTRACTION
			$disable_rslt_entry = $roww[24]; /// SMS_STATUS  /// CONVERTED CHECK COMBO TEST EXTRACTION
			$collected_notes = $roww[25];
			$collected_by_save = $roww[26];

			///echo '===' . $lab_combo . '===' . $combo_Extract_status .  '===' . $lab_combo_request_no . '<br>';

			if ($lab_combo == 1 && $combo_Extract_status == 0 && ($data_capture_status == 'approve' || $data_capture_status == 'result')) {

				try {

					// Start transaction
					$db->beginTransaction();

					$Combo_status = "refresh";
					$cb_no = $test_id;
					$stmtLabCombos->bindParam(':cb_no', $cb_no, PDO::PARAM_STR);
					$stmtLabCombos->execute();

					if ($stmtLabCombos->rowCount() > 0) {
						$nn = 1;
						while ($row_test = $stmtLabCombos->fetch(PDO::FETCH_ASSOC)) {

							$test_name = $row_test['test'];
							$test_sn   = $row_test['test_id'];
							$section   = $row_test['category'];
							$dept_id   = $row_test['dept'];

							$stmtLabManageCheck->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
							$stmtLabManageCheck->bindParam(':test_sn', $test_sn, PDO::PARAM_STR);
							$stmtLabManageCheck->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
							$stmtLabManageCheck->execute();

							if ($stmtLabManageCheck->rowCount() == 0) {

								$format = 'Y-m-d H:i:s';
								$d = DateTime::createFromFormat($format, $date_time_pay);
								if (!($d && $d->format($format) === $date_time_pay)) {
									$date_time_pay = null;
								}

								$labrequest_no_2 = 'MX' . $labrequest_no . $nn;
								$empty = '';
								$empty_zero = '0';

								//// UPDATE THE RESULTS ==============================

								$sql = $db->prepare("UPDATE lab_result SET lab_no = :lab_no	WHERE lab_no = :labrequest_no AND test_no = :test_no");
								$sql->execute([
									':lab_no' => $labrequest_no_2,
									':labrequest_no' => $labrequest_no,
									':test_no' => $test_sn
								]);

								// Bind + Execute insert
								$approve = 'approve';
								$insertLabManage->bindParam(':app_no', $app_no, PDO::PARAM_STR);
								$insertLabManage->bindParam(':labrequest_no', $labrequest_no_2, PDO::PARAM_STR);
								$insertLabManage->bindParam(':patient', $hosp_no, PDO::PARAM_STR);
								$insertLabManage->bindParam(':patient_name', $patient_name, PDO::PARAM_STR);
								$insertLabManage->bindParam(':test_id', $test_sn, PDO::PARAM_STR);
								$insertLabManage->bindParam(':test_name', $test_name, PDO::PARAM_STR);
								$insertLabManage->bindParam(':lab_cat', $dept_id, PDO::PARAM_STR);
								$insertLabManage->bindParam(':section', $section, PDO::PARAM_STR);
								$insertLabManage->bindParam(':group_id', $group_id, PDO::PARAM_STR);
								$insertLabManage->bindParam(':business_service_center', $business_service_center, PDO::PARAM_STR);
								$insertLabManage->bindParam(':referral', $empty, PDO::PARAM_STR);
								$insertLabManage->bindParam(':preferred_specimen', $collected_specimen, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_note', $request_note, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_date', $requested_date, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_date2', $requested_date, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_by', $requester, PDO::PARAM_STR);
								$insertLabManage->bindParam(':lab_combos', $empty_zero, PDO::PARAM_STR);
								$insertLabManage->bindParam(':requesting_physician', $entered_by, PDO::PARAM_STR);
								$insertLabManage->bindParam(':lab_combo_request_no', $labrequest_no, PDO::PARAM_STR);
								$insertLabManage->bindParam(':date_time_pay', $date_time_pay, PDO::PARAM_STR);
								$insertLabManage->bindParam(':data_capture_status', $approve, PDO::PARAM_STR);
								$insertLabManage->bindParam(':result_date', $result_date, PDO::PARAM_STR);
								$insertLabManage->bindParam(':approved_by', $approved_by, PDO::PARAM_STR);

								$insertLabManage->execute();
							}

							$nn++;
						}
						$updateLabCombo_status->bindParam(':test_id', $cb_no, PDO::PARAM_STR);
						$updateLabCombo_status->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
						$updateLabCombo_status->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
						$updateLabCombo_status->execute();
					} else {
						$updateLabManage->bindParam(':test_id', $test_id, PDO::PARAM_STR);
						$updateLabManage->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
						$updateLabManage->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
						$updateLabManage->execute();
					}

					$db->commit();
				} catch (Exception $e) {
					// ❌ Something failed — rollback
					$db->rollback();
					echo "Transaction failed: " . $e->getMessage();
					exit;
				}
			} elseif ($lab_combo == 1 && $combo_Extract_status == 0 && $data_capture_status == 'queue') {

				try {

					// Start transaction
					$db->beginTransaction();

					$Combo_status = "refresh";
					$cb_no = $test_id;
					$stmtLabCombos->bindParam(':cb_no', $cb_no, PDO::PARAM_STR);
					$stmtLabCombos->execute();

					if ($stmtLabCombos->rowCount() > 0) {


						$nn = 1;
						while ($row_test = $stmtLabCombos->fetch(PDO::FETCH_ASSOC)) {

							$test_name = $row_test['test'];
							$test_sn   = $row_test['test_id'];
							$section   = $row_test['category'];
							$dept_id   = $row_test['dept'];

							$stmtLabManageCheck->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
							$stmtLabManageCheck->bindParam(':test_sn', $test_sn, PDO::PARAM_STR);
							$stmtLabManageCheck->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
							$stmtLabManageCheck->execute();

							if ($stmtLabManageCheck->rowCount() == 0) {

								$format = 'Y-m-d H:i:s';
								$d = DateTime::createFromFormat($format, $date_time_pay);
								if (!($d && $d->format($format) === $date_time_pay)) {
									$date_time_pay = null;
								}

								$labrequest_no_2 = $labrequest_no . $nn;
								$empty = '';
								$empty_zero = '0';
								$data_capture_status = 'queue';
								$result_date = null;

								// Bind + Execute insert
								$insertLabManage->bindParam(':app_no', $app_no, PDO::PARAM_STR);
								$insertLabManage->bindParam(':labrequest_no', $labrequest_no_2, PDO::PARAM_STR);
								$insertLabManage->bindParam(':patient', $hosp_no, PDO::PARAM_STR);
								$insertLabManage->bindParam(':patient_name', $patient_name, PDO::PARAM_STR);
								$insertLabManage->bindParam(':test_id', $test_sn, PDO::PARAM_STR);
								$insertLabManage->bindParam(':test_name', $test_name, PDO::PARAM_STR);
								$insertLabManage->bindParam(':lab_cat', $dept_id, PDO::PARAM_STR);
								$insertLabManage->bindParam(':section', $section, PDO::PARAM_STR);
								$insertLabManage->bindParam(':group_id', $group_id, PDO::PARAM_STR);
								$insertLabManage->bindParam(':business_service_center', $business_service_center, PDO::PARAM_STR);
								$insertLabManage->bindParam(':referral', $empty, PDO::PARAM_STR);
								$insertLabManage->bindParam(':preferred_specimen', $collected_specimen, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_note', $request_note, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_date', $requested_date, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_date2', $requested_date, PDO::PARAM_STR);
								$insertLabManage->bindParam(':request_by', $requester, PDO::PARAM_STR);
								$insertLabManage->bindParam(':lab_combos', $empty_zero, PDO::PARAM_STR);
								$insertLabManage->bindParam(':requesting_physician', $entered_by, PDO::PARAM_STR);
								$insertLabManage->bindParam(':lab_combo_request_no', $labrequest_no, PDO::PARAM_STR);
								$insertLabManage->bindParam(':date_time_pay', $date_time_pay, PDO::PARAM_STR);

								$insertLabManage->bindParam(':data_capture_status', $data_capture_status, PDO::PARAM_STR);
								$insertLabManage->bindParam(':result_date', $result_date, PDO::PARAM_STR);
								$insertLabManage->bindParam(':approved_by', $empty, PDO::PARAM_STR);
								$insertLabManage->execute();

								$data = implode('__', [
									$test_sn,
									$test_name,
									$labrequest_no_2,
									$preferred_specimen,
									$entered_by,
									$lab_combo,
									$request_note,
									$data_capture_status,
									$collected_specimen,
									$section,
									$labrequest_no,
									$requested_date,
									$approved_by,
									$result_date,
									$request_by,
									$bill,
									$amount,
									$attachment,
									$collected_notes,
									$collected_by_save,
									$disable_rslt_entry,
								]);
								if (strtoupper($_SESSION['section']) == strtoupper($section)) {
									$all_data .= $data . ',';
								}
							} else {
							}
							$nn++;
						}

						$updateLabCombo_status->bindParam(':test_id', $cb_no, PDO::PARAM_STR);
						$updateLabCombo_status->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
						$updateLabCombo_status->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
						$updateLabCombo_status->execute();
					} else {
						$updateLabManage->bindParam(':test_id', $test_id, PDO::PARAM_STR);
						$updateLabManage->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
						$updateLabManage->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
						$updateLabManage->execute();
					}

					// ✅ All good — commit
					$db->commit();
				} catch (Exception $e) {
					// ❌ Something failed — rollback
					$db->rollback();
					echo "Transaction failed: " . $e->getMessage();
					exit;
				}
			} elseif ($lab_combo_request_no != '' && $lab_combo == 0 && $disable_rslt_entry == 0) {

				$data = implode('__', [
					$test_id,
					$test_name,
					$labrequest_no,
					$preferred_specimen,
					$entered_by,
					$lab_combo,
					$request_note,
					$data_capture_status,
					$collected_specimen,
					$section,
					$lab_combo_request_no,
					$requested_date,
					$approved_by,
					$result_date,
					$request_by,
					$bill,
					$amount,
					$attachment,
					$collected_notes,
					$collected_by_save,
					$disable_rslt_entry,
				]);
				if (strtoupper($_SESSION['section']) == strtoupper($section)) {
					if ($lab_combo == 0 && $combo_Extract_status == 0) {
						$all_data .= $data . ',';
					}
				}
			} elseif ($lab_combo_request_no == '') {
				$data = implode('__', [
					$test_id,
					$test_name,
					$labrequest_no,
					$preferred_specimen,
					$entered_by,
					$lab_combo,
					$request_note,
					$data_capture_status,
					$collected_specimen,
					$section,
					$labrequest_no,
					$requested_date,
					$approved_by,
					$result_date,
					$request_by,
					$bill,
					$amount,
					$attachment,
					$collected_notes,
					$collected_by_save,
					$disable_rslt_entry,
				]);
				if ($lab_combo != 1 && $combo_Extract_status != 1) {
					$all_data .= $data . ',';
				}
			}
		}
		////print_r($all_data)

		$g_total = 0;
		if ($Combo_status == 'refresh') {
			echo '<div class="alert alert-danger">
					<h3>A lab combo investigation request has been detected. Please refresh the page before proceeding. </h3>
				</div>';
			echo "<a href='mgt.php?hosp_no={$hosp_no}' class='btn btn-primary'>REFRESH</a> ";
			exit;
		}
	?>

		<form method="post" action="printlab.php" id="" name="">
			<div align="center">
				<h3 style="color:blue; ">INVESTIGATION(S)</h3>
			</div>
			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>#</th>
						<th>.</th>
						<th width="14%">Req./Appv Dates</th>
						<th>LB#</th>
						<th>Investigation</th>
						<th>Requester/Approver</th>
						<th>.</th>
						<th>Status</th>
						<th></th>
						<th>
							<input
								type="checkbox"
								id="select_all_inv"
								style="display:block; height:18px; width:18px;"
								onchange="toggleAllInv(this)">


						</th>
					</tr>

				</thead>
				<tbody>

					<?php
					$n = 1;
					$total_amt = 0;
					$result_status = '';

					$all_data = rtrim($all_data, ", ");
					$myArray = explode(',', $all_data);


					// Pre-process labrequest_nos and lab_combo_request_nos from input array
					$labrequest_nos = [];
					$lab_combo_rnos = [];

					foreach ($myArray as $val) {
						$parts = explode('__', $val);
						if (isset($parts[2], $parts[10])) {
							$labrequest_nos[] = $parts[2];
							$lab_combo_rnos[] = $parts[10];
						}
					}

					// Fetch lab_manage data
					$lab_manage_map = [];
					if (!empty($labrequest_nos)) {
						$placeholders = implode(',', array_fill(0, count($labrequest_nos), '?'));
						$stmt = $db->prepare("SELECT labrequest_no, data_capture_status FROM lab_manage WHERE labrequest_no IN ($placeholders)");
						$stmt->execute($labrequest_nos);
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$lab_manage_map[$row['labrequest_no']] = $row['data_capture_status'];
						}
					}

					// Fetch patient_ap_services data
					$ap_services_map = [];
					if (!empty($lab_combo_rnos)) {
						$placeholders = implode(',', array_fill(0, count($lab_combo_rnos), '?'));
						$stmt2 = $db->prepare("SELECT drug_sn, paystatus, pay_mode, cr, sn, pay FROM patient_ap_services WHERE drug_sn IN ($placeholders)");
						$stmt2->execute($lab_combo_rnos);
						while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
							$ap_services_map[$row['drug_sn']] = $row;
						}
					}

					$currentDate = new DateTime();
					$n = 1;
					$total_amt = 0;


					try {
						// your table row HTML
						foreach ($myArray as $x => $value):
							$roww = explode('__', $value);

							// Assign variables from exploded array
							list(
								$test_id,
								$test_name,
								$labrequest_no,
								$preferred_specimen,
								$entered_by,
								$lab_combo,,
								$data_capture_status_raw,
								$collected_specimen,
								$section,
								$lab_combo_request_no,
								$request_date,
								$approved_by,
								$result_date,
								$request_by,
								$bill,
								$amount,
								$attachment,
								$collected_notes,
								$collected_by_save,
								$disable_rslt_entry
							) = array_pad($roww, 18, '');


							///echo $data_capture_status_raw;


							// Lookup actual capture status
							$data_capture_status = isset($lab_manage_map[$labrequest_no]) ? $lab_manage_map[$labrequest_no] : $data_capture_status_raw;

							// Lookup payment info
							$paystatus = $pay_mode = $pay = $cr = $sn_ = null;
							if (isset($ap_services_map[$lab_combo_request_no])) {
								$pay_row = $ap_services_map[$lab_combo_request_no];
								$paystatus = $pay_row['paystatus'];
								$pay_mode = $pay_row['pay_mode'];
								$pay = $pay_row['pay'];
								$cr = $pay_row['cr'];
								$sn_ = $pay_row['sn'];
							}



							$rq_date = $request_date ? date('d/m/y h:i a', strtotime($request_date)) : '';
							$rslt_date = $result_date ? date('d/m/y h:i a', strtotime($result_date)) : '';
							$day = 0;
							$result_status = null;

							if (!empty($approved_by) && $result_date && $data_capture_status != 'queue') {
								$resultDate = new DateTime($result_date);
								$diff = $currentDate->diff($resultDate);
								$day = $diff->days;
							}

							if ($data_capture_status == 'result' && isset($_SESSION['approve']) && $_SESSION['approve'] == '1') {
								$result_status = 'yes';
							}

							$test_status = '0';
							$button_title = 'New Result';
							$button_color = 'success';


							if (!in_array($data_capture_status, ['queue', 'specimen'])) {
								$button_title = 'Edit';
								$button_color = 'warning';
								$test_status = '1';
							}

							$_detail = implode('__', [
								$test_id,
								$test_name,
								$labrequest_no,
								$preferred_specimen,
								$entered_by,
								$lab_combo,
								'',
								$data_capture_status,
								$collected_specimen
							]);




							// Determine pay lock
							$pay_lock = 0;
							$b_title = 'Not Paid';
							$edit_status = 'yes';
							$view_status = '';

							if ($paystatus == 1 && $pay_mode == 'claim') {
								$b_title = 'Posted';
								$pay_lock = 0;
							} elseif ($paystatus == 1) {
								$b_title = 'Paid';
								$pay_lock = 0;
							} else {


								$g_total += $pay;
								///if ($enable_cr_post == 1 || $credit_limit > 0) {
								if ($credit_limit > 0) {
									if ($pay <= $credit_limit && $data_capture_status == 'queue') {
										$pay_lock = 0;
										$credit_limit -= $pay;
									} elseif ($data_capture_status == 'queue') {
										$pay_lock = 1;
									} else {
										$pay_lock = 1;
									}

									$pay_lock = 0;   /// to be remove later

									if (in_array($data_capture_status, ['approve', 'result'])) {
										$b_title = 'Posted On-Credit';
										$pay_lock = 0;
									} else {
										$b_title = 'Not Paid';
										$button_title = 'Result On-Credit';
										$button_color = 'danger';
									}
								} else {
									$pay_lock = 1;
								}
							}

							if ($data_capture_status == 'approve' && $day > $grace) {
								$pay_lock = 1;
								$view_status = 'yes';
								$edit_status = "<br>[Exceed: " . (int)$grace . " days]";
							} elseif ($interface == 'print' && $data_capture_status == 'result') {
								$pay_lock = 1;
								$edit_status = ' [Approve Pending]';
							}

							if (!empty($bill)) {
								$total_amt += $amount;
							}					?>
							<tr>
								<td><?php echo $n; ?></td>
								<td>
									<?php if (empty($bill)): ?>

										<input type="checkbox" value="<?php echo htmlspecialchars($labrequest_no); ?>" name="inv_bill_2[]" onchange="toggle_check()">
									<?php endif; ?>
								</td>
								<td><?php echo $rq_date . '<br>' . $rslt_date; ?></td>
								<td><b>LB<?php echo htmlspecialchars($bill); ?></b></td>
								<td>
									<?php
									if ($lab_combo == 1) echo '<strong>C: </strong>';
									$amount_value = (isset($amount) && is_numeric($amount)) ? $amount : 0;

									echo htmlspecialchars($test_name) .
										'<br><b>(N' . number_format($amount_value) . ')</b><br>';

									echo '<strong>[ ' . ($data_capture_status == 'result' ? 'Approve Pending' : ucfirst($data_capture_status)) . ' ]</strong>';
									?>
								</td>
								<td><?php echo htmlspecialchars($request_by) . '<br>' . htmlspecialchars($approved_by); ?></td>
								<td>

									<input type="button" name="edit" value="Notes" data-target="#modal"
										id="<?= htmlspecialchars($labrequest_no); ?>" class="btn btn-success btn-xs view_notes"
										<?= ($rights != 'LB') ? 'disabled' : '' ?>>
									<?php if ($collected_specimen === '') { ?>
										<button type="button"
											data-target="#modal"
											id="<?= htmlspecialchars($labrequest_no); ?>"
											class="btn btn-warning btn-xs capture_take_spm"
											<?= $sampl_ = ($section == 'Radiology') ? 'Capture?' : 'Take Specimen' ?>>
											<?= $sampl_; ?>
										</button>
									<?php } ?>

								</td>


								<td>
									<?php if ($paystatus == 1 && $pay_mode == 'claim'): ?>
										<strong style="color:#009">Posted</strong>
									<?php elseif ($paystatus == 1): ?>
										<strong style="color:#009">Paid</strong>
									<?php else: ?>
										<?php if ($current_balance > 0 && $_SESSION['payfrom_status'] == 1 && $pay <= $current_balance): ?>
											<button type="button" class="btn btn-warning btn-xs" id="pay_now<?php echo $sn_; ?>"
												onClick="payNow('<?php echo $sn_; ?>','investigation','<?= $hosp_no; ?>')">Pay from Wallet</button>
										<?php endif; ?>
										<strong style="color:#F00"><?= $b_title; ?></strong>
									<?php endif; ?>
								</td>
								<td>
									<?php

									if ($section == $_SESSION['section']): ?>
										<input type="button" id="button_<?= $labrequest_no; ?>" value="<?= $button_title; ?>"
											<?php if ($pay_lock == 1) echo 'disabled';
											?>
											onClick="show_result_sheet('<?php echo $_detail . '__' . $paystatus . '__' . $cr . '__' . $test_status . '__' . $lab_combo_request_no . '__' . $bill . '__' . $attachment . '__' . $collected_notes; ?>','<?= $labrequest_no; ?>')"
											class="btn btn-<?= $button_color; ?> btn-xs" />
									<?php endif; ?>

									<?php
									if (($section == 'Radiology' || $vrlst == 1) && $data_capture_status == 'approve'): ?>
										&nbsp;|&nbsp;
										<a href="printscan.php?<?= (strpos($labrequest_no, 'EX') !== false ? 'e=' : 'i=') . htmlspecialchars($labrequest_no); ?>"
											class="btn btn-primary btn-xs">Print/Email</a>
									<?php endif; ?>

									<?php if ($view_status == 'yes' && ($rights == 'LB' || $vrlst == 1)): ?>
										<input type="button" value="View"
											onClick="view_result_only('<?php echo $labrequest_no . '__' . $hosp_no . '__' . $test_id . '__' . $test_name; ?>')"
											class="btn btn-success btn-xs" />
									<?php endif; ?>
								</td>
								<td>
									<?php if ($section == 'Laboratory' && in_array($data_capture_status, ['approve', 'result'])):
										$show_print_button = 'yes';
									?>




										<input
											type="checkbox"
											class="inv-checkbox"
											value="<?php echo $_detail . '__' . $paystatus . '__' . $cr; ?>"
											name="inv[]"
											onchange="updateSelectAll()"
											style="display:block; height:18px; width:18px;">

									<?php endif; ?>
								</td>
							</tr>
					<?php
							$n++;
						endforeach;
					} catch (Throwable $e) {
						error_log("Error: " . $e->getMessage());
					}
					?>


				</tbody>
			</table>
			<div style="display: flex; justify-content: space-between; align-items: center;">
				<?php if ($total_amt > 0) { ?>
					<div class="alert alert-danger" style="font-size: 18px; margin: 0;">
						<?= number_format($total_amt, 2); ?>
					</div>
				<?php } ?>

				<?php if ($total_amt == 0) { ?>
					<div></div>
				<?php } ?>

				<div class="alert alert-success" style="font-size: 18px; margin: 0;">
					<?= '<b>Total Amount: </b>' . number_format($g_total, 2) . '<b style="color:red"> (Existing Credit: ' . number_format($Total_total_credits, 2) . ')</b>'; ?><br>
					<?php
					if ($g_total > 0 && $credit_limit_bal < $g_total) {
						echo "<small style='color:red;'>Credit limit or deposit amount is insufficient. Available balance: " .  number_format($credit_limit_bal, 2) . '</small>';
					}
					?>


				</div>
			</div>

		<?php } else { ?>
		<?php } ?>
		<div class="row">
			<div class="col-md-6 text-left">
				<b>Select the boxes on the left above.</b><br>
				<button type="submit" class="btn btn-success btn-sm" name="generate_bill" disabled>
					Generate Invoice/Billing Numbering (LB)
				</button>
			</div>

			<?php if (
				$show_print_button == 'yes' && ($vrlst == 1 || $_SESSION['section'] == 'Laboratory')
			) { ?>
				<div class="col-md-2 text-right">
					&nbsp;&nbsp; : &nbsp;&nbsp;
					<b>.</b><br>
					<input type="button" id="closeBtn" value="Close All" onClick="close_dashboard_all()" class="btn btn-danger btn-sm" />
				</div>
				<div class="col-md-4 text-right">
					<div class="d-flex align-items-center justify-content-end" style="gap: 10px;">
						<small><b>Check the box above right to print selected test(s).</b></small>
						<?php if ($_SESSION['unit_head'] == 1) { ?>
							<br>
							<input type="checkbox" name="prev_result" value="prev" style="height:18px; width:18px;">Old Results
						<?php } ?>
						<button type="submit" class="btn btn-success btn-sm" name="display_result_print" disabled>
							Print / Email Result
						</button>
					</div>

				</div>

			<?php } ?>
		</div>


		<input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>">
		<input type="hidden" name="type_patient" value="<?php echo $type_patient; ?>">
		<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>">
		<input type="hidden" name="hospital_no" value="<?php echo $hosp_no; ?>">
		<input type="hidden" name="app_no" value="<?php echo $app_no; ?>">
		</form>

		<!--<button id="submitBtn" onclick="submitCheckboxes()" class="btn btn-success">Invoice/Bill</button> -->
	<?php exit;
}

if (isset($_POST['speciment_taken_'])) {

	// Get variables from POST

	$specimen_taken = isset($_POST['speciment_taken_']) ? $_POST['speciment_taken_'] : '';
	$labrequest_no_ = isset($_POST['labrequest_no_']) ? $_POST['labrequest_no_'] : '';
	$collected_date = isset($_POST['datetime']) ? $_POST['datetime'] : '';
	$collected_notes = isset($_POST['collected_notes']) ? $_POST['collected_notes'] : '';
	$data_capture_status_ = isset($_POST['data_capture_status_']) ? $_POST['data_capture_status_'] : '';

	// Validate datetime format (from input like "Y-m-d\TH:i") and convert to "Y-m-d H:i:s"
	$dt = DateTime::createFromFormat('Y-m-d\TH:i', $collected_date);
	if ($dt && $dt->format('Y-m-d\TH:i') === $collected_date) {
		$collected_date = $dt->format('Y-m-d H:i:s');
	} else {
		// If not valid, use current datetime
		$collected_date = date('Y-m-d H:i:s');
	}

	// Default values
	$collected_by = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'unknown';
	// Prepare and execute update
	$sql = $db->prepare("
	UPDATE lab_manage 
	SET 
		data_capture_status = :data_capture_status,
		collected_specimen = :collected_specimen,
		collected_by = :collected_by,
		collected_notes = :collected_notes,
		collected_date = :collected_date
	WHERE 
		labrequest_no = :labrequest_no
		AND (data_capture_status NOT IN ('approve', 'result'))");

	$updated = $sql->execute(array(
		':data_capture_status' => $data_capture_status_,
		':collected_specimen' => $specimen_taken,
		':collected_by' => $collected_by,
		':collected_notes' => $collected_notes,
		':collected_date' => $collected_date,
		':labrequest_no' => $labrequest_no_
	));

	if ($updated && $sql->rowCount() > 0) {
		echo "Specimen collection info updated successfully.";
	} else {
		echo "No update made. Either lab request not found, data already up to date, or status is 'approve'/'result'.";
	}
}

if (isset($_POST['mgt_notes'])) {

	try {
		$db->beginTransaction();

		// Default values
		$entered_by = $_SESSION['fullname'];
		$lab_sci_name = '';
		$lab_sci_speciality = '';
		$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
		$notes = '';
		$RQ_type = 'sl';
		$result_date = date("Y-m-d H:i:s");

		// Lab info
		$labrequest_no = $_POST['labrequest_no'];
		$labrequest_no_main = $_POST['labrequest_no_main'];
		$test_id = $_POST['test_id'];
		$test_name = $_POST['test_name'];
		$mgt_notes = $_POST['mgt_notes'];
		$paystatus = $_POST['paystatus'];
		$result_status = $_POST['result_status'];
		$cr = $_POST['cr'];
		$username = trim($_POST['fullname_approver']);

		if ($_SESSION['section'] == 'Laboratory') {
			$speciment_taken = $_POST['speciment_taken'];
		} else {
			$speciment_taken = 'capture';
		}

		// Step 1: Get approver speciality, fullname, and EmployeeCode
		$sql = "SELECT i.speciality, u.fullname, u.EmployeeCode 
        FROM invsti_users i 
        INNER JOIN admin_users u ON u.username = i.username 
        WHERE u.username = :username";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':username', $username);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$speciality_approver = $row['speciality'];
		$fullname_approver   = $row['fullname'];
		$EmployeeCode        = $row['EmployeeCode'];

		// Step 2: Fallback if speciality is empty – fetch from hremp using EmployeeCode
		if (trim($speciality_approver) === '' && $EmployeeCode) {
			$sql = "SELECT Designation FROM hremp WHERE EmployeeCode = :EmployeeCode";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':EmployeeCode', $EmployeeCode);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$speciality_approver = $row['Designation'];
		}

		$speciality_approver = $speciality_approver ? htmlspecialchars($speciality_approver) : null;
		$fullname_approver   = $fullname_approver ? htmlspecialchars($fullname_approver) : null;



		// Set lab scientist info if not data operator
		if ($_SESSION['speciality'] !== "Data Operator") {
			$lab_sci_name = $_SESSION['fullname'];
			$lab_sci_speciality = $_SESSION['speciality'];
		}

		$approve_status = (int)$_POST['approve_result'];
		$data_capture_status = $approve_status === 1 ? 'approve' : 'result';
		$approved_by = $approve_status === 1 ? $fullname_approver : '';

		// Ensure field exists
		$stmt = $db->prepare("SELECT 1 FROM lab_scan_fields WHERE test_no = ?");
		$stmt->execute([$test_id]);
		if ($stmt->rowCount() === 0) {
			$sql = $db->prepare("INSERT INTO lab_scan_fields (test_no, field, field_type) VALUES (?, ?, ?)");
			$sql->execute([$test_id, $test_name, 'report']);
		}

		// Check if result exists
		$stmt_chk = $db->prepare("SELECT 1 FROM lab_result WHERE lab_no = ? AND test_no = ?");
		$stmt_chk->execute([$labrequest_no, $test_id]);

		if ($stmt_chk->rowCount() === 0) {
			// INSERT new result
			$sql = $db->prepare("INSERT INTO lab_result (field_value, lab_no, test_no, test_name, specimen_collected, comment, notes, RQ_type, result_date, lab_sci_name, lab_sci_speciality, entered_by)
								 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$sql->execute([
				$mgt_notes,
				$labrequest_no,
				$test_id,
				$test_name,
				$speciment_taken,
				$comment,
				$notes,
				$RQ_type,
				$result_date,
				$lab_sci_name,
				$lab_sci_speciality,
				$entered_by
			]);
		} else {
			// UPDATE result
			$sql = $db->prepare("UPDATE lab_result 
								 SET field_value = ?, lab_sci_name = ?, lab_sci_speciality = ?, entered_by = ?, result_date = ?, comment = ? 
								 WHERE lab_no = ? AND test_no = ?");
			$sql->execute([
				$mgt_notes,
				$lab_sci_name,
				$lab_sci_speciality,
				$entered_by,
				$result_date,
				$comment,
				$labrequest_no,
				$test_id
			]);

			// BACKUP old result
			$sql = $db->prepare("INSERT INTO lab_result_old (field_value, lab_no, test_no, test_name, comment, RQ_type, result_date, lab_sci_name, lab_sci_speciality, entered_by) 
								 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$sql->execute([
				$mgt_notes,
				$labrequest_no,
				$test_id,
				$test_name,
				$comment,
				$RQ_type,
				$result_date,
				$lab_sci_name,
				$speciality_approver,
				$approved_by
			]);
		}

		// Update lab_manage
		$check = $db->prepare("SELECT collected_by FROM lab_manage WHERE labrequest_no = ?");
		$check->execute([$labrequest_no]);
		$current = $check->fetch(PDO::FETCH_ASSOC);

		$credit_status = ($paystatus == 0) ? '1' : '0';
		$collected_date = date('Y-m-d H:i:s');
		$collected_by = $_SESSION['fullname'];

		if (empty($current['collected_by'])) {
			$sql = $db->prepare("UPDATE lab_manage 
								 SET collected_by = :collected_by,
									 collected_specimen = :collected_specimen,
									 collected_date = :collected_date,
									 result_note = :result_note,
									 lab_sci_name = :lab_sci_name,
									 lab_sci_speciality = :lab_sci_speciality,
									 entered_by = :entered_by,
									 result_date = :result_date,
									 data_capture_status = :data_capture_status,
									 approved_by = :approved_by,
									 sms_status = 0,
									 result_on_credit = :result_on_credit,
									 abnormal_results = :result_status
								 WHERE labrequest_no = :labrequest_no");
			$sql->execute([
				':collected_by' => $collected_by,
				':collected_specimen' => $speciment_taken,
				':collected_date' => $collected_date,
				':result_note' => $mgt_notes,
				':lab_sci_name' => $fullname_approver,
				':lab_sci_speciality' => $speciality_approver,
				':entered_by' => $entered_by,
				':result_date' => $result_date,
				':data_capture_status' => $data_capture_status,
				':approved_by' => $approved_by,
				':result_on_credit' => $credit_status,
				':result_status' => $result_status,
				':labrequest_no' => $labrequest_no
			]);
		} else {
			$sql = $db->prepare("UPDATE lab_manage 
								 SET result_note = ?, lab_sci_name = ?, lab_sci_speciality = ?, 
									 entered_by = ?, result_date = ?, data_capture_status = ?, 
									 approved_by = ?, sms_status = 0, result_on_credit = ?, abnormal_results = ? 
								 WHERE labrequest_no = ?");
			$sql->execute([
				$mgt_notes,
				$fullname_approver,
				$speciality_approver,
				$entered_by,
				$result_date,
				$data_capture_status,
				$approved_by,
				$credit_status,
				$result_status,
				$labrequest_no
			]);
		}

		// Invoice update
		$invoice_no = mt_rand(1000000, 9999999);
		$sql = $db->prepare("UPDATE patient_ap_services 
							 SET cr = ?, invoice_no = ?, invoice_by = ?, 
								 drug_status = 1, invoice_status = 1 
							 WHERE drug_sn = ?");
		$sql->execute([
			$credit_status,
			$invoice_no,
			$_SESSION['fullname'],
			$labrequest_no_main
		]);

		// Finalize
		$db->commit();
		echo "Lab result saved successfully.";
	} catch (Exception $e) {
		$db->rollBack();
		///error_log("Lab Result Save Error: " . $e->getMessage());
		echo "Error saving lab result: " . $e->getMessage();
	}
}

if (isset($_POST['details'])) {

	// Default response structure
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
		'approved_by' => '',
		'collected_notes' => '',
		'hosp_no' => '',
		'collected_by' => '',
		'username' => '',
		'attachment' => '',
		'result_status' => '',
		'Specimen' => ''
	);


	// Extract data
	$main_data = $_POST['details'];
	$row_test = explode("__", $main_data);

	// Assign with fallback
	$test_id                = isset($row_test[0])  ? $row_test[0]  : '';
	$test_name              = isset($row_test[1])  ? $row_test[1]  : '';
	$labrequest_no          = isset($row_test[2])  ? $row_test[2]  : '';
	$Specimen               = isset($row_test[3])  ? $row_test[3]  : '';
	$lab_combo              = isset($row_test[5])  ? $row_test[5]  : '';
	$request_note           = isset($row_test[6])  ? $row_test[6]  : '';
	$data_capture_status    = isset($row_test[7])  ? $row_test[7]  : '';
	$empty                  = isset($row_test[8])  ? $row_test[8]  : '';
	$paystatus              = isset($row_test[9])  ? $row_test[9]  : '';
	$cr                     = isset($row_test[10]) ? $row_test[10] : '';
	$test_status_investigate = isset($row_test[11]) ? $row_test[11] : '';
	$labrequest_no_main     = isset($row_test[12]) ? $row_test[12] : '';
	$collected_notes     = isset($row_test[15]) ? $row_test[15] : '';
	$collected_by_save     = isset($row_test[16]) ? $row_test[16] : '';

	// Fetch test field metadata
	$stmt_fields = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = ?");
	$stmt_fields->execute(array($test_id));
	$row_fields = $stmt_fields->fetch(PDO::FETCH_ASSOC);

	$field_no    = isset($row_fields['sn']) ? $row_fields['sn'] : '';
	$field_type  = isset($row_fields['field_type']) ? $row_fields['field_type'] : '';
	$field       = isset($row_fields['field']) ? $row_fields['field'] : '';
	$reference   = !empty($row_fields['reference']) ? 'Reference ( ' . $row_fields['reference'] . ' )' : '';

	// Initialize result variables
	$field_value = '';
	$result_comment = '';
	$result_note = '';
	$result_date = '';
	$edit = 0;

	// Fetch lab result if exists
	$stmt_result = $db->prepare("SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ?");
	$stmt_result->execute(array($test_id, $labrequest_no));
	if ($stmt_result->rowCount() > 0) {
		$row = $stmt_result->fetch(PDO::FETCH_ASSOC);
		$field_value     = $row['field_value'];
		$result_comment  = $row['comment'];
		$result_note     = $row['notes'];
		$result_date     = $row['result_date'];
		$edit = 1;

		$response_main['test_status'] = 1;
		$response_main['result_note'] = $field_value;
	}

	// Generate header
	$title_head = '<table width="100%"><tr><td><label>' . htmlspecialchars($field) . '</label></td>';
	$title_head .= '<td align="right"><label style="text-align:right">' . htmlspecialchars($reference) . '</label></td></tr></table>';

	// Generate input form based on field type
	$form = '';
	$result_type = '';

	if ($field_type === 'options') {
		$form .= $title_head . '<select name="option_input" id="option_input" class="form-control" data-required="true">';

		$stmt_opt = $db->prepare("SELECT options FROM lab_scan_rlts_opt WHERE field_id_no = ?");
		$stmt_opt->execute(array($field_no));

		if ($edit) {
			$form .= '<option selected value="' . htmlspecialchars($field_value) . '">' . htmlspecialchars($field_value) . '</option>';
		} else {
			$form .= '<option selected value="">Select ...</option>';
		}

		while ($opt_row = $stmt_opt->fetch(PDO::FETCH_ASSOC)) {
			$option = htmlspecialchars($opt_row['options']);
			$form .= '<option value="' . $option . '">' . $option . '</option>';
		}

		$form .= '</select>';
		$result_type = 'options';
	} elseif ($field_type === 'value') {
		$form = $title_head;
		$form .= '<input type="text" name="single_input" id="single_input" class="form-control" value="' . htmlspecialchars($field_value) . '" placeholder="Enter result for ' . htmlspecialchars($field) . '"/>';
		$result_type = 'single';
	} elseif ($field_type === 'values') {

		$form = $title_head;

		$stmt_vals = $db->prepare("SELECT options,reference FROM lab_scan_rlts_values WHERE field_id_no = ?");
		$stmt_vals->execute(array($field_no));

		while ($val_row = $stmt_vals->fetch(PDO::FETCH_ASSOC)) {
			$field = htmlspecialchars($val_row['options']);
			$ref = !empty($val_row['reference']) ? ' ( ' . htmlspecialchars($val_row['reference']) . ' )' : '';


			$form .= '<input type="text" name="single_input" id="single_input" class="form-control" value="' . htmlspecialchars($field_value) . '" placeholder="Enter result for ' . htmlspecialchars($field) . '"/>';
		}

		$result_type = 'multi';
	} elseif ($field_type === 'single') {
		$form = $title_head;
		///ORDER BY sn

		$form .= '<input type="text" name="single_input" id="single_input" class="form-control" value="' . htmlspecialchars($field_value) . '" placeholder="Enter result for ' . htmlspecialchars($field) . '"/>';
		$result_type = 'single';
	} elseif ($field_type === 'report') {
		$stmt_tpl = $db->prepare("SELECT template_2 FROM invsti_template WHERE sn = ?");
		$stmt_tpl->execute([$test_id]);
		$row = $stmt_tpl->fetch(PDO::FETCH_ASSOC);

		$form = ($edit == 1)
			? $field_value
			: (isset($row['template_2']) ? $row['template_2'] : '');

		$result_type = 'single_report';
	} else {
		$result_type = 'report'; // fallback
	}

	// Get specimen collection status
	$specimen_collected = '';
	$data_capture_status_row = '';

	$stmt_manage = $db->prepare("SELECT collected_specimen, data_capture_status,bill,approved_by,attachment,patient,abnormal_results FROM lab_manage WHERE test_id = ? AND labrequest_no = ?");
	$stmt_manage->execute(array($test_id, $labrequest_no));

	if ($row = $stmt_manage->fetch(PDO::FETCH_ASSOC)) {
		$specimen_collected = $row['collected_specimen'];
		$data_capture_status_row = $row['data_capture_status'];
		$approved_by = $row['approved_by'];
		$attachment = $row['attachment'];
		$abnormal_results = $row['abnormal_results'];
		$hosp_no = $row['patient'];
		$bill = ($row['bill'] == '') ? '' : $row['bill'];

		$sql = "SELECT username 
        FROM admin_users 
        WHERE fullname = :approved_by";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':approved_by', $approved_by);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$username = $row['username'];
	}

	// Final response values
	$response_main['test_id'] = $test_id;
	$response_main['test_name'] = $test_name;
	$response_main['labrequest_no'] = $labrequest_no;
	$response_main['hosp_no'] = $hosp_no;
	$response_main['labrequest_no_main'] = $labrequest_no_main;
	$response_main['approved_by'] = $approved_by;
	$response_main['data_capture_status'] = $data_capture_status_row;
	$response_main['cr'] = $cr;
	$response_main['Specimen'] = $Specimen;
	$response_main['paystatus'] = $paystatus;
	$response_main['specimen_collected'] = $specimen_collected;
	$response_main['section'] = isset($_SESSION['section']) ? $_SESSION['section'] : '';
	$response_main['result_comment'] = $result_comment;
	$response_main['result_type'] = $result_type;
	$response_main['lb'] = $bill;
	$response_main['collected_notes'] = $collected_notes;
	$response_main['collected_by'] = $collected_by_save;
	$response_main['username'] = $username;
	$response_main['attachment'] = $attachment;
	$response_main['result_status'] = $abnormal_results;
	$response_main['form'] =  $form;

	// Output JSON response
	echo json_encode($response_main);
} ?>