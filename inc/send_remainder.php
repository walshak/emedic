<?php

session_start();
include('../Connections/Conn.php');


if (isset($_POST["deletete"])) {

	$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
	$sn       = isset($_POST['deletete']) ? trim($_POST['deletete']) : '';

	$response = array(
		'status'  => 0,
		'message' => ''
	);

	try {

		// Enable PDO Exceptions
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if ($sn == '') {
			throw new Exception('Invalid request parameter.');
		}

		$db->beginTransaction();

		/*
        |--------------------------------------------------------------------------
        | Get Main Request
        |--------------------------------------------------------------------------
        */
		$stmt = $db->prepare("
            SELECT sn, labrequest_no, lab_combos
            FROM lab_manage
            WHERE sn = :sn
            AND request_by = :fullname
            LIMIT 1
        ");

		$stmt->execute(array(
			':sn'       => $sn,
			':fullname' => $fullname
		));

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row) {
			throw new Exception('Unable to delete. Only requester can delete this investigation.');
		}

		$labrequest_no = $row['labrequest_no'];
		$lab_combos    = $row['lab_combos'];

		/*
        |--------------------------------------------------------------------------
        | Delete Combo Billings
        |--------------------------------------------------------------------------
        */
		if ($lab_combos == 1) {

			$stmtCombo = $db->prepare("
                SELECT lab_combo_request_no
                FROM lab_manage
                WHERE labrequest_no = :labrequest_no
            ");

			$stmtCombo->execute(array(
				':labrequest_no' => $labrequest_no
			));

			while ($combo = $stmtCombo->fetch(PDO::FETCH_ASSOC)) {

				$combo_no = $combo['lab_combo_request_no'];

				if ($combo_no != '') {

					$delComboBill = $db->prepare("
                        DELETE FROM patient_ap_services
                        WHERE drug_sn = :drug_sn
                        AND paystatus = 0
                    ");

					$delComboBill->execute(array(
						':drug_sn' => $combo_no
					));
				}
			}
		}

		/*
        |--------------------------------------------------------------------------
        | Delete Main Billing
        |--------------------------------------------------------------------------
        */
		$delMainBill = $db->prepare("
            DELETE FROM patient_ap_services
            WHERE drug_sn = :drug_sn
            AND paystatus = 0
        ");

		$delMainBill->execute(array(
			':drug_sn' => $labrequest_no
		));

		/*
        |--------------------------------------------------------------------------
        | Delete Lab Requests
        |--------------------------------------------------------------------------
        */
		$delRequest = $db->prepare("
            DELETE FROM lab_manage
            WHERE labrequest_no = :labrequest_no
            OR lab_combo_request_no = :lab_combo_request_no
        ");

		$delRequest->execute(array(
			':labrequest_no'       => $labrequest_no,
			':lab_combo_request_no' => $labrequest_no
		));

		if ($delRequest->rowCount() > 0) {

			$db->commit();

			$response['status']  = 1;
			$response['message'] = 'Investigation Deleted Successfully! Click Close to Refresh List.';
		} else {

			$db->rollBack();

			$response['status']  = 0;
			$response['message'] = 'Delete failed.';
		}
	} catch (PDOException $e) {

		if ($db->inTransaction()) {
			$db->rollBack();
		}

		$response['status']  = 0;
		$response['message'] = 'Database Error: ' . $e->getMessage();
	} catch (Exception $e) {

		if ($db->inTransaction()) {
			$db->rollBack();
		}

		$response['status']  = 0;
		$response['message'] = $e->getMessage();
	}

	echo json_encode($response);
}

if (isset($_POST["doctor_result_status"])) {


	$response = array(
		'status' => 0,
		'last_id' => '',
		'hospital_no' => '',
		'message' => ''
	);

	$sn = $_POST["doctor_result_status"];

	$stmt_get = $db->query("SELECT labrequest_no FROM lab_manage WHERE sn='$sn'");
	if ($stmt_get->rowCount() > 0) {
		$rowx = $stmt_get->fetch(PDO::FETCH_ASSOC);
		$labrequest_no = $rowx['labrequest_no'];

		/// check if payment has been made

		$stmt_get = $db->query("SELECT sn,drug_sn,hospital_no,item_services,app_no FROM patient_ap_services WHERE drug_sn='$labrequest_no'");
		if ($stmt_get->rowCount() > 0) {

			//// get ap services from for insertion ====////////////////////////////////////		
			$rowx = $stmt_get->fetch(PDO::FETCH_ASSOC);
			$app_service_tbl_id = $rowx['sn'];
			$app_service_id = $rowx['drug_sn'];
			$hospital_no = $rowx['hospital_no'];
			$item_services = $rowx['item_services'];
			$app_no = $rowx['app_no'];
			///$drug_sn = $rowx['drug_sn'];
			$updated_by_name = 'doctor_result_status';

			$created_at = date('Y-m-d H:i:s');

			$stmt_get = $db->query("SELECT * FROM notes_services WHERE app_service_tbl_id='$app_service_tbl_id'");
			if ($stmt_get->rowCount() == 0) {

				$sql = $db->prepare("INSERT INTO notes_services (
	hospital_no,app_no,app_service_tbl_id,app_service_id,service,created_at,created_by,prepared_by,updated_by_name) 
		VALUES (:hospital_no,:app_no,:app_service_tbl_id,:app_service_id,:service,:created_at,:created_by,:prepared_by,:updated_by_name)");
				$sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$sql->bindParam(':app_no', $app_no, PDO::PARAM_STR);
				$sql->bindParam(':app_service_tbl_id', $app_service_tbl_id, PDO::PARAM_STR);
				$sql->bindParam(':app_service_id', $app_service_id, PDO::PARAM_STR);
				$sql->bindParam(':service', $item_services, PDO::PARAM_STR);
				$sql->bindParam(':created_at', $created_at, PDO::PARAM_STR);
				$sql->bindParam(':created_by', $_SESSION['id'], PDO::PARAM_STR);
				$sql->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
				$sql->bindParam(':updated_by_name', $updated_by_name, PDO::PARAM_STR);

				$sql->execute();
				if ($sql->rowCount() > 0) {

					$last_id = base64_encode($db->lastInsertId());
					$response['message'] = 'An investigation has been converted to a medical service for documentation. Please click the Medical Service tab to add a note.';
					$response['status'] = '1';
					$response['last_id'] = $last_id;
					$response['hospital_no'] = $hospital_no;
				} else {
					$response['message'] = 'Unable to Convert Service';
					$response['status'] = '0';
				}
			} else {

				$response['message'] = 'Converted Already! Click the Medical Service Tab to Add Note';
				$response['status'] = '0';
			}

			echo  json_encode($response);
		}
	}
}

if (isset($_POST["send_reminder"])) {
	$sn = $_POST["send_reminder"];

	$stmt_get = $db->prepare("SELECT patient_name, test_name, section, request_date, patient FROM lab_manage WHERE sn = ?");
	$stmt_get->execute([$sn]);

	if ($stmt_get->rowCount() > 0) {
		$rowx = $stmt_get->fetch(PDO::FETCH_ASSOC);

		$patient_name = $rowx['patient_name'];
		$test_name = $rowx['test_name'];
		$section = $rowx['section'];
		$request_date = $rowx['request_date'];
		$patient = $rowx['patient'];

		// Calculate time difference
		$reqDate = new DateTime($request_date);
		$now = new DateTime();
		$interval = $reqDate->diff($now);

		// Nicely formatted time difference
		$date_f = $interval->format('%d days, %h hrs, %i mins ago');

		if (strtolower($section) == "laboratory") {
			$code = "LB";
		} else {
			$code = "RD";
		}
		$message_ = 'Result Reminder for patient <b>' . htmlspecialchars($patient_name) . '</b> (' . htmlspecialchars($test_name) . ')<br>' .
			'Requested since ' . $date_f . '<br>' .
			'<a href="mgt.php?hosp_no=' . urlencode($patient) . '">View Patient</a>';

		// Send notification
		global_notify_($db, 'notifyurgent', $code, '<b>URGENT REMINDER</b>', $message_);
	}
}

if (isset($_POST["send_reminder2_all"])) {
	$sms_status = '0';
	$updateSQL = "UPDATE lab_manage SET sms_status=:sms_status WHERE section=:section and sms_status=1";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':sms_status', $sms_status, PDO::PARAM_STR);
	$sql->bindParam(':section', $_POST["send_reminder2_all"], PDO::PARAM_STR);
	$sql->execute();
}


if (isset($_POST["send_reminder2"])) {
	$sms_status = '0';
	$updateSQL = "UPDATE lab_manage SET sms_status=:sms_status WHERE sn=:sn";
	$sql = $db->prepare($updateSQL);
	$sql->bindParam(':sms_status', $sms_status, PDO::PARAM_STR);
	$sql->bindParam(':sn', $_POST["send_reminder2"], PDO::PARAM_STR);
	$sql->execute();
}
