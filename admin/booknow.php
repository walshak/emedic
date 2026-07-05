<?php
include('../Connections/Conn.php');
session_start();

if (isset($_POST['finalbook_now'])) {

	try {

		$stmt = $db->query("SELECT MAX(sn) AS last_sn FROM apptm");
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$last_sn = isset($row['last_sn']) ? (int)$row['last_sn'] : 0;
		$appt_no = sprintf('%06d', $last_sn + 1);

		$hos_no = $_POST['hosp_no'];
		$item_sn = $_POST['item_sn'];
		$duration = $_POST['duration'];
		$date_timee = $_POST['date_timee'];
		$hosp_price_ = $_POST['hosp_price'];

		if ($duration > 0 and $item_sn != '') {

			if ((int)$hosp_price_ === 100) {
				$free_consultation = '1';
				if ((int)$duration === 100) {
					$duration = 1;
				}
			} else {
				$free_consultation = '0';
			}

			$app_expiration_date = date("Y-m-d h:i:s", strtotime($date_timee . "+$duration days", time()));
			$one = "1";
			$one = "0";
			$empty = "";
			$stmt = $db->prepare("
						SELECT 1 
						FROM apptm 
						WHERE hospital_no = ? 
						AND service_id = ? 
						AND (status = 'checkin' OR status = 'future')
						LIMIT 1");
			$stmt->execute(array($hos_no, $item_sn));
			if (!$stmt->fetch()) {

				$stmt = $db->query("SELECT appt_no FROM apptm WHERE appt_no='$appt_no'");
				if ($stmt->rowCount() == 0) {

					// Prepare the SQL statement with placeholders
					$insertSQL = "INSERT INTO apptm(
								appt_no, hospital_no, tagno, patient_name, app_by, referal_doc, app_state, dept, service_id, 
								services_name, insurance, insurance_type, interest, ap_type, auth_code, 
								date_ap, ap_date_time, status, queue_lock, checkin_by, doctor_id, app_duration, app_expiration_date,queue_center,cr
							) VALUES (
								:appt_no, :hospital_no, :tagno, :patient_name, :app_by, :referal_doc, :app_state, :dept, :service_id, 
								:services_name, :insurance, :insurance_type, :interest, :ap_type, :auth_code, 
								:date_ap, :ap_date_time, :status, :queue_lock, :checkin_by, :doctor_id, :app_duration, :app_expiration_date,:queue_center,:cr
							)";

					// Prepare the statement
					$stmt = $db->prepare($insertSQL);
					$stmt->bindValue(':appt_no', $appt_no, PDO::PARAM_STR);
					$stmt->bindValue(':hospital_no', $_POST['hosp_no'], PDO::PARAM_STR);
					$stmt->bindValue(':tagno', $one, PDO::PARAM_STR);
					$stmt->bindValue(':patient_name', $_POST['patient_name'], PDO::PARAM_STR);
					$stmt->bindValue(':app_by', $_POST['appl_by'], PDO::PARAM_STR);
					$stmt->bindValue(':referal_doc', $_POST['referral_doctor'], PDO::PARAM_STR);
					$stmt->bindValue(':app_state', $empty, PDO::PARAM_STR);
					$stmt->bindValue(':dept', $_POST['dept_id'], PDO::PARAM_STR);
					$stmt->bindValue(':service_id', $_POST['item_sn'], PDO::PARAM_STR);
					$stmt->bindValue(':services_name', $_POST['item_service'], PDO::PARAM_STR);
					$stmt->bindValue(':insurance', $_POST['insurance_no'], PDO::PARAM_STR);
					$stmt->bindValue(':insurance_type', $_POST['insurance_type'], PDO::PARAM_STR);
					$stmt->bindValue(':interest', 0, PDO::PARAM_STR);
					$stmt->bindValue(':ap_type', $_POST['service_access'], PDO::PARAM_STR);
					$stmt->bindValue(':auth_code', $one, PDO::PARAM_STR);
					$stmt->bindValue(':date_ap', $_POST['ap_date'], PDO::PARAM_STR);
					$stmt->bindValue(':ap_date_time', $_POST['date_timee'], PDO::PARAM_STR);
					$stmt->bindValue(':status', $_POST['how_contact'], PDO::PARAM_STR);
					$stmt->bindValue(':queue_lock', $_POST['queue_lock'], PDO::PARAM_STR);
					$stmt->bindValue(':checkin_by', $_POST['fullname'], PDO::PARAM_STR);
					$stmt->bindValue(':doctor_id', empty($_POST['doctor_id']) ? 0 : (int)$_POST['doctor_id'], PDO::PARAM_INT);
					$stmt->bindValue(':app_duration', $_POST['duration'], PDO::PARAM_STR);
					$stmt->bindValue(':app_expiration_date', $app_expiration_date, PDO::PARAM_STR);
					$stmt->bindValue(':queue_center', $_POST['queue_center'], PDO::PARAM_STR);
					$stmt->bindValue(':cr', $_POST['cr'], PDO::PARAM_STR);

					if ($stmt->execute()) {
						$lastInsertId = $db->lastInsertId();
					} else {
						header("location:index.php?bk_err");
						exit;
					}

					$invoice_no = date('m') . sprintf('%06d', mt_rand(0, 999999));
					$setdate    = date('Y-m-d H:i:s');

					// Sanitize POST inputs
					$pay_mode         = isset($_POST['pay_mode']) ? trim($_POST['pay_mode']) : '';
					$self_pay         = isset($_POST['self_pay']) ? trim($_POST['self_pay']) : '';
					$pay_from_deposit = isset($_POST['pay_from_deposit']) ? trim($_POST['pay_from_deposit']) : '';
					$visit_status     = isset($_POST['visit_status']) ? trim($_POST['visit_status']) : '';
					$insurance        = isset($_POST['insurance']) ? trim($_POST['insurance']) : '';
					$file_amt_actual  = isset($_POST['file_amt_actual']) ? trim($_POST['file_amt_actual']) : '';
					$ccop_int_charge  = isset($_POST['ccop_int_charge']) ? trim($_POST['ccop_int_charge']) : 0;
					$item_sn          = isset($_POST['item_sn']) ? trim($_POST['item_sn']) : '';
					$item_service     = isset($_POST['item_service']) ? trim($_POST['item_service']) : '';
					$hosp_price       = isset($_POST['hosp_price']) ? floatval($_POST['hosp_price']) : 0;
					$free_consultation = isset($free_consultation) ? $free_consultation : '0';

					// Fetch item details (once only)
					$stmt = $db->prepare("SELECT hosp_price, category, ext_price FROM prices_table WHERE sn = ?");
					$stmt->execute(array($item_sn));
					$item_row = $stmt->fetch(PDO::FETCH_ASSOC);

					// Default values
					$amt_paying  = 0;
					$claim_amt   = 0;
					$transc_date = date('Y-m-d H:i:s');
					$paystatus   = '0';
					$cat_type    = !empty($item_row['category']) ? $item_row['category'] : 'Consultation';
					$ext_price   = !empty($item_row['ext_price']) ? $item_row['ext_price'] : 0;

					// Determine payment mode and amounts
					if ($self_pay === 'pay' && $pay_mode === 'claim') {
						if ($item_row) {
							// Self-pay override: treat as cash
							$amt_paying  = $item_row['hosp_price'];
							$pay_mode    = 'cash';
							$claim_amt   = 0;
							$transc_date = null;
						} else {
							// No item found: process claim
							$pay_mode  = 'claim';
							$claim_amt = isset($_POST['claim_amt']) ? floatval($_POST['claim_amt']) : 0;
						}
					} elseif ($pay_mode === 'claim' && empty($self_pay)) {
						// Claim without self-pay
						$pay_mode  = 'claim';
						$claim_amt = isset($_POST['claim_amt']) ? floatval($_POST['claim_amt']) : 0;
					} else {
						// Default: normal cash payment
						$pay_mode    = 'cash';
						$amt_paying  = isset($_POST['ext_price']) ? floatval($_POST['ext_price']) : $hosp_price;
					}

					// Free consultation override
					if ($free_consultation == '1') {
						$paystatus  = 1;
						$amt_paying = 0;
						$claim_amt  = 0;
					}

					// === Call main transaction function ===
					$response = patient_ap_services(
						$db,
						$appt_no,
						$cat_type,
						$item_service,
						$item_sn,
						$claim_amt,
						$ccop_int_charge,
						$invoice_no,
						$amt_paying,
						$hosp_price,
						$pay_mode,
						$paystatus
					);

					// Handle failed response
					if ($response != '1') {
						$deleteStmt1 = $db->prepare("DELETE FROM apptm WHERE sn = :lastInsertId");
						$deleteStmt1->bindParam(':lastInsertId', $lastInsertId, PDO::PARAM_STR);
						$deleteStmt1->execute();

						echo $response;
						echo '<a href="index.php?bk_err">Click here to go to Main Page!!</a>';
						exit;
					}

					// === Update patient age ===
					$hosp_no = isset($_POST['hosp_no']) ? trim($_POST['hosp_no']) : '';
					if (!empty($hosp_no)) {
						$stmt = $db->prepare("SELECT dob FROM enrollee WHERE hospital_no = :hosp_no");
						$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
						$stmt->execute();

						if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$birthDate = trim($row['dob']);
							date_default_timezone_set('Africa/Lagos');

							if ($birthDate && strtotime($birthDate)) {
								$today = new DateTime(date('Y-m-d'));
								$dob   = new DateTime($birthDate);
								$diff  = $dob->diff($today);

								$yrs  = (int)$diff->format('%y');
								$mnth = (int)$diff->format('%m');
								$days = (int)$diff->format('%d');

								if ($yrs === 0 && $mnth === 0) {
									$age = "{$days} days";
								} elseif ($yrs === 0) {
									$age = "{$mnth} mths {$days} days";
								} else {
									$age = "{$yrs} yrs {$mnth} mths {$days} days";
								}

								// Prevent unrealistic ages
								$dob_sql = ($yrs > 150) ? ", dob=''" : '';
								$updateSQL = "UPDATE enrollee SET age = :age $dob_sql WHERE hospital_no = :hosp_no";
								$updateStmt = $db->prepare($updateSQL);
								$updateStmt->bindParam(':age', $age, PDO::PARAM_STR);
								$updateStmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
								$updateStmt->execute();
							}
						}
					}

					// === Fetch latest sn from patient_ap_services ===
					$stmt = $db->prepare("
							SELECT sn 
							FROM patient_ap_services 
							WHERE app_no = :appt_no 
							ORDER BY sn DESC 
							LIMIT 1
						");
					$stmt->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
					$stmt->execute();

					$sn = ($row = $stmt->fetch(PDO::FETCH_ASSOC)) ? $row['sn'] : null;

					if ($pay_from_deposit === 'pay_from_deposit' && !empty($sn)) {
						$redirect = "index.php?equiry&sv&pay_from_deposit={$sn}";
					} elseif ($pay_mode === 'cash' && isset($_SESSION['biller']) && $_SESSION['biller'] == '1') {
						$redirect = "../billing/pacct.php?emr={$hosp_no}&pay";
					} else {
						$redirect = "index.php?equiry&sv";
					}
					// Perform redirect
					header("Location: {$redirect}");
					exit;
				} else {
					header("location:index.php?bk_err&appointment_already_exist");
				}
			}
		} else {
			header("location:index.php?bk_err");
		}
	} catch (Exception $e) {

		$deleteStmt1 = $db->prepare("DELETE FROM apptm WHERE sn = :lastInsertId");
		$deleteStmt1->bindParam(':lastInsertId', $lastInsertId, PDO::PARAM_STR);
		$deleteStmt1->execute();

		echo '<div style="color:red;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
		echo '<a href="index.php?bk_err">Click here to go to Main Page!</a>';
		exit;
	}
}



function patient_ap_services(
	$db,
	$appt_no,
	$cat_type,
	$item_service,
	$item_sn,
	$claim_amt,
	$ccop_int_charge,
	$invoice_no,
	$amt_paying,
	$hosp_price,
	$pay_mode,
	$paystatus
) {

	$one = "1";
	// Set the service group based on item_service
	if ($item_service == 'New File') {
		$serv_group = 'Registration';
	} else {
		$serv_group = 'Consultation';
	}

	// Set the current date and time
	$setdate = date('Y-m-d H:i:s');

	// Prepare the SQL statement with placeholders
	$insertSQL = "INSERT INTO patient_ap_services(
        app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, 
        hosp_price, claim_amt, interest, qty, invoice_status, invoice_no, invoice_date, 
        invoice_by, prepared_by, date_entry, transact_date, pay, pay_mode, paystatus) 
        VALUES (
        :app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, 
        :hosp_price, :claim_amt, :interest, :qty, :invoice_status, :invoice_no, :invoice_date, 
        :invoice_by, :prepared_by, :date_entry, :transact_date, :pay, :pay_mode, :paystatus)";

	// Prepare the statement
	$stmt = $db->prepare($insertSQL);

	// Bind the parameters
	$stmt->bindValue(':app_no', $appt_no, PDO::PARAM_STR);
	$stmt->bindValue(':hospital_no', $_POST['hosp_no'], PDO::PARAM_STR);
	$stmt->bindValue(':access', $_POST['service_access'], PDO::PARAM_STR);
	$stmt->bindValue(':serv_group', $serv_group, PDO::PARAM_STR);
	$stmt->bindValue(':cat_type', $cat_type, PDO::PARAM_STR);
	$stmt->bindValue(':dept_id', $_POST['dept_id'], PDO::PARAM_STR);
	$stmt->bindValue(':drug_sn', $item_sn, PDO::PARAM_STR);
	$stmt->bindValue(':item_services', $item_service, PDO::PARAM_STR);
	$stmt->bindValue(':hosp_price', $hosp_price, PDO::PARAM_STR);
	$stmt->bindValue(':claim_amt', $claim_amt, PDO::PARAM_STR);
	$stmt->bindValue(':interest', $ccop_int_charge, PDO::PARAM_STR);
	$stmt->bindValue(':qty', $one, PDO::PARAM_INT);
	$stmt->bindValue(':invoice_status', $one, PDO::PARAM_INT);
	$stmt->bindValue(':invoice_no', $invoice_no, PDO::PARAM_STR);
	$stmt->bindValue(':invoice_date', $setdate, PDO::PARAM_STR);
	$stmt->bindValue(':invoice_by', $_POST['fullname'], PDO::PARAM_STR);
	$stmt->bindValue(':prepared_by', $_POST['fullname'], PDO::PARAM_STR);
	$stmt->bindValue(':date_entry', $setdate, PDO::PARAM_STR);
	$stmt->bindValue(':transact_date', NULL, PDO::PARAM_STR);
	$stmt->bindValue(':pay', $amt_paying, PDO::PARAM_STR);
	$stmt->bindValue(':pay_mode', $pay_mode, PDO::PARAM_STR);
	$stmt->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
	// Execute the statement
	if ($stmt->execute()) {
		return "1";
	} else {
		return "Error inserting record: " . implode(":", $stmt->errorInfo());
	}
}
