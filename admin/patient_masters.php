<?php
if (isset($_POST['apply_search'])) {

	$in_patient = $_POST['in_patient'];
	header("location:index.php?ptm=all/$in_patient");
}

if (isset($_GET['ptm'])) {
	$ptm = $_GET['ptm'];
	$get_prev = $_GET['ptm'];

	$part = explode("/", $ptm);

	$ptm = $part[0];
	if ($ptm == 'all') {
		$hosp_no = $part[1];
		//$ptm_2='1000';
	} else {
		$ptm_2 = $part[1];
		$hosp_no = $part[2];
	}
}

if (isset($_GET['dx'])) {

	$desc = 'De-activate Insurance Status /' . $ptm_2;
	$staff = $_SESSION['fullname'];
	$pid = '';
	$pname = '';
	$action = 'Insurance/De/Active';
	include_once("logs.php");

	$updateSQL = "UPDATE insurance_tbl SET status='deactive' WHERE insurance_no='$ptm_2'";
	$db->exec($updateSQL);
	header("location:index.php?ptm=$ptm/$ptm_2");
}

if (isset($_GET['ax'])) {

	$desc = 'Activate Insurance Status /' . $ptm_2;
	$staff = $_SESSION['fullname'];
	$pid = '';
	$pname = '';
	$action = 'Insurance/Active';
	include_once("logs.php");

	$updateSQL = "UPDATE insurance_tbl SET status='active' WHERE insurance_no='$ptm_2'";
	$db->exec($updateSQL);
	header("location:index.php?ptm=$ptm/$ptm_2");
}

if (isset($_GET['Idl'])) {

	$staff = $_SESSION['fullname'];
	$pid = '';
	$pname = '';
	$action = 'Deleted HMO:' . $ptm_2;
	include_once("logs.php");


	$stmt = $db->prepare("SELECT sn FROM enrollee WHERE hmo_no=:hmo_no");
	$stmt->bindParam(':hmo_no', $ptm_2, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() == 0) {
		$delete_del = "DELETE FROM insurance_tbl WHERE insurance_no=:insurance_no";
		$sql = $db->prepare($delete_del);
		$sql->bindParam(':insurance_no', $ptm_2, PDO::PARAM_STR);
		$sql->execute();
		header("location:index.php?ptm=$ptm&dl");
		exit; // Don't forget to exit after redirecting
	}
}

if (isset($_POST["Save_patient"])) {

	$birthDate = $_POST['dob'];
	$setdate = date("Y-m-d H:i:s");
	$hosp_no = $_POST['hosp_no'];
	$surname = trim($_POST['surname']);
	$fname = trim($_POST['fname']);
	$oname = trim($_POST['oname']);

	date_default_timezone_set('Africa/Lagos');
	$Current_date = date('Y-m-d');
	$date1 = new DateTime($Current_date);
	$date2 = new DateTime($birthDate);
	$diff = $date2->diff($date1);
	$yrs = $diff->format('%y');
	$mnth = $diff->format('%m');
	$days = $diff->format('%d'); // Get the difference in days

	// Format the age output
	if ($yrs == 0 && $mnth == 0) {
		$age = $days . ' days';
	} elseif ($yrs == 0) {
		$age = $mnth . ' mths ' . $days . ' days';
	} else {
		$age = $yrs . 'yrs ' . $mnth . 'mths ' . $days . 'days';
	}

	if ($_POST['state_lga'] == 'others' and $_POST['other_state'] != '') {
		$state = $_POST['other_state'];
	} elseif ($_POST['state_lga'] == 'others' and $_POST['other_state'] == '') {
		$state = "Unknown";
	} else {
		$state = $_POST['state_lga'];
	}



	if ($hosp_no == '') {

		$stmt = $db->query("SELECT hospital_no FROM enrollee ORDER BY hospital_no DESC LIMIT 1");
		if ($stmt->rowCount() == 0) {
			$hospital_no = '000001';
		} else {
			$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
			$hospital_no = $rwx['hospital_no'] + 1;
			$hospital_no = sprintf('%006d', $hospital_no);
		}


		$insurance = $_POST['insurance'];
		$hmo = $_POST['patient_type'];


		$surname = str_replace("'", "", $surname);
		$surname = str_replace('"', '', $surname);


		$fname = str_replace("'", "", $fname);
		$fname = str_replace('"', '', $fname);


		$oname = str_replace("'", "", $oname);
		$oname = str_replace('"', '', $oname);

		$stmt = $db->query("SELECT * FROM insurance_tbl WHERE insurance_no='$hmo' and status='active'");
		if ($stmt->rowCount() > 0) {
			$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
			$interest = $row_rstSelect['interest'];

			$token_ = md5($hospital_no);

			// Check if hospital already exists
			$checkHospitalSQL = "SELECT COUNT(*) FROM enrollee WHERE hospital_no = :hospital_no";
			$stmt = $db->prepare($checkHospitalSQL);
			$stmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmt->execute();
			$hospitalExists = $stmt->fetchColumn();

			// Check if hospital exists
			if ($hospitalExists > 0) {

				$recep_msg = "Hospital number already exists.";
				$msg = 'Error';
				$title_header = 'File Error';
			} else {
				// Ensure that surname and fname are not empty
				$surname = strtoupper($_POST['surname']);
				$fname = strtoupper($_POST['fname']);

				if (empty($surname) || empty($fname)) {

					$recep_msg = "Surname and First Name cannot be empty.";
					$msg = 'Error';
					$title_header = 'File Error';
				} else {
					// Proceed with inserting the enrollee
					$insertSQL = "INSERT INTO enrollee (
                        nhis_no, nhis_no_ext, hospital_no, primary_provider, hmo_no, insurance, employer_no, member, nhis_registration_date, expiry_date, surname, fname, oname, occupation, gender, marital_status, blood_g, geno_type, dob, age, phone, email, state_lga, tribe, nationality, addr, religion, date_capture, patient_review, rank, command_formation, visit_status, token
                      ) VALUES (
                        :nhis_no, :nhis_no_ext, :hospital_no, :primary_provider, :hmo_no, :insurance, :employer_no, :member, :nhis_registration_date, :expiry_date, :surname, :fname, :oname, :occupation, :gender, :marital_status, :blood_g, :geno_type, :dob, :age, :phone, :email, :state_lga, :tribe, :nationality, :addr, :religion, :date_capture, :patient_review, :rank, :command_formation, :visit_status, :token
                      )";

					$stmt = $db->prepare($insertSQL);
					$stmt->bindValue(':nhis_no', $_POST['nhis_membership_no'], PDO::PARAM_STR);
					$stmt->bindValue(':nhis_no_ext', $_POST['dependant'], PDO::PARAM_STR);
					$stmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
					$stmt->bindValue(':primary_provider', $_POST['primary_provider'], PDO::PARAM_STR);
					$stmt->bindValue(':hmo_no', $_POST['patient_type'], PDO::PARAM_STR);
					$stmt->bindValue(':insurance', $_POST['insurance'], PDO::PARAM_STR);
					$stmt->bindValue(':employer_no', '', PDO::PARAM_STR);
					$stmt->bindValue(':member', $_POST['member'], PDO::PARAM_STR);
					$stmt->bindValue(':nhis_registration_date', $_POST['datenhis'], PDO::PARAM_STR);
					$stmt->bindValue(':expiry_date', $_POST['expiry_date'], PDO::PARAM_STR);
					$stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
					$stmt->bindValue(':fname', $fname, PDO::PARAM_STR);
					$stmt->bindValue(':oname', strtoupper($oname), PDO::PARAM_STR);
					$stmt->bindValue(':occupation', $_POST['occup'], PDO::PARAM_STR);
					$stmt->bindValue(':gender', $_POST['genderr'], PDO::PARAM_STR);
					$stmt->bindValue(':marital_status', $_POST['marital'], PDO::PARAM_STR);
					$stmt->bindValue(':blood_g', $_POST['blood_group'], PDO::PARAM_STR);
					$stmt->bindValue(':geno_type', $_POST['gtype'], PDO::PARAM_STR);
					$stmt->bindValue(':dob', $_POST['dob'], PDO::PARAM_STR);
					$stmt->bindValue(':age', $age, PDO::PARAM_STR);
					$stmt->bindValue(':phone', $_POST['phone'], PDO::PARAM_STR);
					$stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
					$stmt->bindValue(':state_lga', $_POST['state_lga'], PDO::PARAM_STR);
					$stmt->bindValue(':tribe', $_POST['tribe'], PDO::PARAM_STR);
					$stmt->bindValue(':nationality', $_POST['nationality'], PDO::PARAM_STR);
					$stmt->bindValue(':addr', $_POST['addr'], PDO::PARAM_STR);
					$stmt->bindValue(':religion', $_POST['religion'], PDO::PARAM_STR);
					$stmt->bindValue(':date_capture', $setdate, PDO::PARAM_STR);
					$stmt->bindValue(':patient_review', $_POST['reviewer'], PDO::PARAM_STR);
					$stmt->bindValue(':rank', $_POST['police_ranks'], PDO::PARAM_STR);
					$stmt->bindValue(':command_formation', $_POST['command_formation'], PDO::PARAM_STR);
					$stmt->bindValue(':visit_status', 'new', PDO::PARAM_STR);
					$stmt->bindValue(':token', $token_, PDO::PARAM_STR);
					$stmt->execute();



					$recep_msg = "New file created successfully.";
					$msg = 'success';
					$title_header = 'File Created';

					$desc = 'New Patient Added /' . $hospital_no;
					$staff = $_SESSION['fullname'];
					$pid = $hospital_no;
					$pname = '';
					$action = 'New Patient/Added';
					include_once("logs.php");

					$sv = 1;
				}
			}
		} else {
			$recep_msg = "HMO/Insurance entity is currently not active.";
			$msg = 'danger';
			$title_header = 'Error';
		}

		//		}

	} else {
		try {
			// Enable PDO exceptions for easier debugging
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Begin transaction
			$db->beginTransaction();

			if (empty($surname) || empty($fname)) {
				$recep_msg = "Surname and First Name cannot be empty.";
				$msg = 'Error';
				$title_header = 'File Error';
			} else {

				// Sanitize inputs
				$surname = trim(str_replace(['"', "'"], '', $_POST['surname']));
				$fname   = trim(str_replace(['"', "'"], '', $_POST['fname']));
				$oname   = trim(str_replace(['"', "'"], '', $_POST['oname']));
				$validation_status = 1;

				// 1️⃣ Fetch old data before update
				$getOldSQL = "SELECT * FROM enrollee WHERE hospital_no = :hospital_no";
				$getStmt = $db->prepare($getOldSQL);
				$getStmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
				$getStmt->execute();
				$oldData = $getStmt->fetch(PDO::FETCH_ASSOC);

				// 2️⃣ Perform the update
				$updateSQL = "UPDATE enrollee SET
			nhis_no = :nhis_no,
			surname = :surname,
			fname = :fname,
			oname = :oname,
			dob = :dob,
			age = :age,
			gender = :gender,
			phone = :phone,
			occupation = :occupation,
			marital_status = :marital_status,
			blood_g = :blood_g,
			geno_type = :geno_type,
			email = :email,
			religion = :religion,
			state_lga = :state_lga,
			tribe = :tribe,
			nationality = :nationality,
			addr = :addr,
			validation_status = :validation_status,
			member = :member,
			primary_provider = :primary_provider,
			nhis_registration_date = :nhis_registration_date,
			expiry_date = :expiry_date,
			patient_review = :patient_review,
			rank = :rank,
			command_formation = :command_formation
		WHERE hospital_no = :hospital_no";

				$stmt = $db->prepare($updateSQL);
				$stmt->bindValue(':nhis_no', $_POST['nhis_membership_no'], PDO::PARAM_STR);
				$stmt->bindValue(':surname', strtoupper($surname), PDO::PARAM_STR);
				$stmt->bindValue(':fname', strtoupper($fname), PDO::PARAM_STR);
				$stmt->bindValue(':oname', strtoupper($oname), PDO::PARAM_STR);
				$stmt->bindValue(':dob', $_POST['dob'], PDO::PARAM_STR);
				$stmt->bindValue(':age', $age, PDO::PARAM_STR);
				$stmt->bindValue(':gender', $_POST['genderr'], PDO::PARAM_STR);
				$stmt->bindValue(':phone', $_POST['phone'], PDO::PARAM_STR);
				$stmt->bindValue(':occupation', $_POST['occup'], PDO::PARAM_STR);
				$stmt->bindValue(':marital_status', $_POST['marital'], PDO::PARAM_STR);
				$stmt->bindValue(':blood_g', $_POST['blood_group'], PDO::PARAM_STR);
				$stmt->bindValue(':geno_type', $_POST['gtype'], PDO::PARAM_STR);
				$stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
				$stmt->bindValue(':religion', $_POST['religion'], PDO::PARAM_STR);
				$stmt->bindValue(':state_lga', $_POST['state_lga'], PDO::PARAM_STR);
				$stmt->bindValue(':tribe', $_POST['tribe'], PDO::PARAM_STR);
				$stmt->bindValue(':nationality', $_POST['nationality'], PDO::PARAM_STR);
				$stmt->bindValue(':addr', $_POST['addr'], PDO::PARAM_STR);
				$stmt->bindValue(':validation_status', $validation_status, PDO::PARAM_STR);
				$stmt->bindValue(':member', $_POST['member'], PDO::PARAM_STR);
				$stmt->bindValue(':primary_provider', $_POST['primary_provider'], PDO::PARAM_STR);
				$stmt->bindValue(':nhis_registration_date', NULL, PDO::PARAM_STR);
				$stmt->bindValue(':expiry_date', $_POST['expiry_date'], PDO::PARAM_STR);
				$stmt->bindValue(':patient_review', $_POST['reviewer'], PDO::PARAM_STR);
				$stmt->bindValue(':rank', $_POST['police_ranks'], PDO::PARAM_STR);
				$stmt->bindValue(':command_formation', $_POST['command_formation'], PDO::PARAM_STR);
				$stmt->bindValue(':hospital_no', $hosp_no, PDO::PARAM_STR);
				$stmt->execute();

				// 3️⃣ Compare and find changes
				$changes = [];
				if ($oldData) {
					$fieldMap = [
						'nhis_no' => 'nhis_membership_no',
						'surname' => 'surname',
						'fname' => 'fname',
						'oname' => 'oname',
						'dob' => 'dob',
						'age' => 'age',
						'gender' => 'genderr',
						'phone' => 'phone',
						'occupation' => 'occup',
						'marital_status' => 'marital',
						'blood_g' => 'blood_group',
						'geno_type' => 'gtype',
						'email' => 'email',
						'religion' => 'religion',
						'state_lga' => 'state_lga',
						'tribe' => 'tribe',
						'nationality' => 'nationality',
						'addr' => 'addr',
						'member' => 'member',
						'primary_provider' => 'primary_provider',
						'nhis_registration_date' => 'datenhis',
						'expiry_date' => 'expiry_date',
						'patient_review' => 'reviewer',
						'rank' => 'police_ranks',
						'command_formation' => 'command_formation'
					];

					foreach ($fieldMap as $dbField => $postKey) {
						if (isset($_POST[$postKey])) {
							$newValue = $_POST[$postKey];
							$oldValue = isset($oldData[$dbField]) ? $oldData[$dbField] : '';
							if ($oldValue != $newValue) {
								$changes[] = "$dbField changed from '{$oldValue}' to '{$newValue}'";
							}
						}
					}
				}

				// 4️⃣ Log the changes if any
				if (!empty($changes)) {
					$description = implode("; ", $changes);
					$logSQL = "INSERT INTO patient_staff_logs 
				(patient_id, descriptions, staff_name, action, date_and_time)
				VALUES (:patient_id, :descriptions, :staff_name, :action, :date_and_time)";
					$logStmt = $db->prepare($logSQL);
					$logStmt->bindParam(':patient_id', $hosp_no);
					$logStmt->bindParam(':descriptions', $description);
					$logStmt->bindParam(':staff_name', $_SESSION['fullname']);
					$logStmt->bindValue(':action', 'Enrollee record update');
					$logStmt->bindValue(':date_and_time', date('Y-m-d H:i:s'));
					$logStmt->execute();
				}

				// 5️⃣ Update dependent tables
				$patient_name = trim($surname . ' ' . $fname . ' ' . $oname);

				$tables = [
					['lab_manage', 'patient', 'patient_name'],
					['apptm', 'hospital_no', 'patient_name'],
					['dialysis', 'hospital_no', 'patient_name'],
					['procedures', 'hospital_no', 'name'],
					['transplants', 'hospital_no', 'patient_name']
				];

				foreach ($tables as $tbl) {
					list($table, $col, $nameCol) = $tbl;
					$updateSQL = "UPDATE {$table} SET {$nameCol} = :patient_name WHERE {$col} = :hospital_no";
					$stmt = $db->prepare($updateSQL);
					$stmt->bindValue(':patient_name', $patient_name, PDO::PARAM_STR);
					$stmt->bindValue(':hospital_no', $hosp_no, PDO::PARAM_STR);
					$stmt->execute();
				}

				$sv = 1;
			}

			// ✅ Commit transaction
			$db->commit();
		} catch (Exception $e) {
			// ❌ Rollback on error
			if ($db->inTransaction()) {
				$db->rollBack();
			}

			// Log or display the error safely
			error_log("Patient update failed: " . $e->getMessage());
			$recep_msg = "An error occurred while updating the record: " . $e->getMessage();
			$msg = 'Error';
			$title_header = 'Transaction Failed';
			$sv = 0;
		}
	}
}

if (isset($_POST["assign_submit"])) {
	try {
		// Start database transaction
		$db->beginTransaction();

		// === Validate required fields ===
		$required_fields = ['nhis_membership_no', 'dependant', 'hmo_no', 'Insurance_Type', 'hosp_no'];
		$missing = [];
		foreach ($required_fields as $field) {
			$value = isset($_POST[$field]) ? trim($_POST[$field]) : '';
			if ($value === '') $missing[] = $field;
		}
		if ($missing) {
			echo "Please fill in all required fields";
			echo ' <a href="index.php?ptm=ppt/'
				. urlencode($_POST['hmo_no']) . '/'
				. urlencode($_POST['hosp_no']) . '">Go Back</a>';
			exit;
		}

		// === Safe variables ===
		$nhis_membership_no = trim($_POST['nhis_membership_no']);
		$dependant          = trim($_POST['dependant']);
		$hmo_no_new         = trim($_POST['hmo_no']);
		$insurance_type     = trim($_POST['Insurance_Type']);
		$hospital_no        = trim($_POST['hosp_no']);
		$payment_remarks    = isset($_POST['payment_remarks']) ? trim($_POST['payment_remarks']) : '';
		$staff_name         = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'System';

		// === Fetch existing enrollee details ===
		$stmt = $db->prepare("
			SELECT e.nhis_no, e.insurance, e.hmo_no, i.insurance_name 
			FROM enrollee e 
			INNER JOIN insurance_tbl i ON e.hmo_no = i.insurance_no 
			WHERE hospital_no = :hosp_no 
			LIMIT 1
		");
		$stmt->bindParam(':hosp_no', $hospital_no, PDO::PARAM_STR);
		$stmt->execute();
		$existing = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$existing) {
			throw new Exception("Patient record not found for hospital_no: $hospital_no");
		}

		// === Update enrollee ===
		$updateSQL = "
			UPDATE enrollee SET
				nhis_no     = :nhis_no,
				nhis_no_ext = :nhis_no_ext,
				hmo_no      = :hmo_no,
				insurance   = :insurance
			WHERE hospital_no = :hospital_no
		";
		$stmt = $db->prepare($updateSQL);
		$stmt->execute([
			':nhis_no'      => $nhis_membership_no,
			':nhis_no_ext'  => $dependant,
			':hmo_no'       => $hmo_no_new,
			':insurance'    => $insurance_type,
			':hospital_no'  => $hospital_no
		]);

		// === Update chart_ledger ===
		$stmt = $db->prepare("UPDATE chart_ledger SET insurance_no = :insurance_no WHERE hospital_no = :hospital_no");
		$stmt->execute([
			':insurance_no' => $hmo_no_new,
			':hospital_no'  => $hospital_no
		]);

		// === Check patient_discount ===
		$stmt = $db->prepare("
			SELECT sn 
			FROM patient_discount 
			WHERE individual_group_no = :hmo_no 
			  AND status = 'On-going' 
			LIMIT 1
		");
		$stmt->bindParam(':hmo_no', $hmo_no_new, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$stmt = $db->prepare("UPDATE enrollee SET discount_set = 1 WHERE hospital_no = :hospital_no");
			$stmt->execute([':hospital_no' => $hospital_no]);
		}

		// === Insert remarks if HMO or Insurance changed ===
		if ($existing['hmo_no'] != $hmo_no_new || $existing['insurance'] != $insurance_type || !empty($payment_remarks)) {
			$desc = '<b>Updated:</b> ' . htmlspecialchars($existing['hmo_no']) . '/' . htmlspecialchars($existing['insurance_name']) .
				' (' . htmlspecialchars($existing['insurance']) . ') → ' .
				htmlspecialchars($hmo_no_new) . ' (' . htmlspecialchars($insurance_type) . ')';

			$full_remark = trim($payment_remarks . ' ' . $desc);

			$stmt = $db->prepare("
				INSERT INTO patients_remarks_tbl (hospital_no, remark, staff_name)
				VALUES (:hospital_no, :remark, :staff_name)
			");
			$stmt->execute([
				':hospital_no' => $hospital_no,
				':remark'      => $full_remark,
				':staff_name'  => $staff_name
			]);
		}


		// 6️⃣ Handle “New File” Claim Auto-Creation
		$stmt = $db->prepare("SELECT hmo_no, insurance, visit_status FROM enrollee WHERE hospital_no = :hosp_no LIMIT 1");
		$stmt->execute([':hosp_no' => $hospital_no]);
		$rwx2 = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($rwx2 && $rwx2['visit_status'] === 'new') {

			// Check if "New File" was previously paid in cash
			$ckCash = $db->prepare("
                SELECT sn FROM patient_ap_services 
                WHERE item_services = 'New File' 
                  AND hospital_no = :hosp_no 
                  AND pay_mode = 'cash' 
                  AND pay > 0
                LIMIT 1");
			$ckCash->execute([':hosp_no' => $hospital_no]);

			if ($ckCash->fetch(PDO::FETCH_ASSOC)) {

				// === Fetch 'New file' service details ===
				$stmt = $db->prepare("SELECT sn, item_service, hosp_price, nhis_price, dept, category FROM prices_table WHERE item_service = 'New File' LIMIT 1");
				$stmt->execute();
				$priceRow = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!$priceRow) {
					throw new Exception("'New File' service not found in prices_table.");
				}

				$item_sn      = $priceRow['sn'];
				$item_service = $priceRow['item_service'];
				$hosp_price   = $priceRow['hosp_price'];
				$nhis_price   = $priceRow['nhis_price'];
				$dept_id      = $priceRow['dept_id'];
				$cat_type     = $priceRow['category'];

				// === Determine claim amount ===
				$claim_amt = ($nhis_price > 0) ? $nhis_price : $hosp_price;

				$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'System';
				$dept_id = isset($_SESSION['dept_id']) ? $_SESSION['dept_id'] : 0;
				$current_datetime = date('Y-m-d H:i:s');
				$invoice_no = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);

				// === Avoid duplicates ===
				$stmt = $db->prepare("
                    SELECT 1 FROM patient_ap_services 
                    WHERE hospital_no = :hospital_no AND item_services = :item_service AND pay_mode = 'claim' LIMIT 1
                ");
				$stmt->execute([':hospital_no' => $hospital_no, ':item_service' => $item_service]);

				if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
					// Insert new claim record
					$insertSQL = "
						INSERT INTO patient_ap_services
						(app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, 
						invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, date_entry, pay, pay_mode, 
						paystatus, process_claim, claim_valid_by)
						VALUES
						(:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, 0, 1,
						1, :invoice_no, :invoice_date, :invoice_by, :prepared_by, :date_entry, 0, 'claim', 0, 0, :claim_valid_by)";

					$stmtInsert = $db->prepare($insertSQL);
					$stmtInsert->execute([
						':app_no'         => $hospital_no,
						':hospital_no'    => $hospital_no,
						':serv_group'     => 'Registration',
						':cat_type'       => $cat_type,
						':dept_id'        => $dept_id,
						':drug_sn'        => $item_sn,
						':item_services'  => $item_service,
						':hosp_price'     => $hosp_price,
						':claim_amt'      => $claim_amt,
						':invoice_no'     => $invoice_no,
						':invoice_date'   => $current_datetime,
						':invoice_by'     => $fullname,
						':prepared_by'    => $fullname,
						':date_entry'     => $current_datetime,
						':claim_valid_by' => $claim_valid_by
					]);

					// Remove old 'cash' entry
					$del = $db->prepare("
                        DELETE FROM patient_ap_services 
                        WHERE hospital_no = :hospital_no AND item_services = 'New File' AND pay_mode = 'cash'");
					$del->execute([':hospital_no' => $hospital_no]);

					// Step 1: Select first matching consultation record
					$sel = $db->prepare("
							SELECT app_no FROM patient_ap_services 
							WHERE hospital_no = :hospital_no 
							AND serv_group = 'Consultation' 
							AND pay_mode = 'cash' 
							AND paystatus = 0
							LIMIT 1	");
					$sel->execute([':hospital_no' => $hospital_no]);
					$row = $sel->fetch(PDO::FETCH_ASSOC);

					// Step 2: If found, delete from both tables
					if ($row) {
						$app_no = $row['app_no'];

						// Delete from patient_ap_services
						$del1 = $db->prepare("
								DELETE FROM patient_ap_services 
								WHERE hospital_no = :hospital_no 
								AND serv_group = 'Consultation' 
								AND pay_mode = 'cash' 
								AND paystatus = 0 
								AND app_no = :app_no
							");
						$del1->execute([
							':hospital_no' => $hospital_no,
							':app_no' => $app_no
						]);

						// Delete from apptm
						$del2 = $db->prepare("
							DELETE FROM apptm 
							WHERE hospital_no = :hospital_no 
							AND status = 'checkin'  
							AND queue_lock = 0 
							AND appt_no = :app_no
						");
						$del2->execute([
							':hospital_no' => $hospital_no,
							':app_no' => $app_no
						]);
					}
				}
			}
		}

		// === Commit all changes ===
		$db->commit();

		// === Redirect ===
		$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
		$current_url .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
		<script type="text/javascript">
			window.location.href = "<?php echo $current_url; ?>&sv";
		</script>
	<?php

	} catch (Exception $e) {
		// Rollback if failed
		if ($db->inTransaction()) {
			$db->rollBack();
		}

		// Log detailed error
		error_log("Transaction Error (assign_submit): " . $e->getMessage());
		echo "<div class='error'>An unexpected error occurred: " . htmlspecialchars($e->getMessage()) . "</div>";
		exit;
	}
}

if (isset($_POST["assign_submit_cpt_phs"])) {

	try {
		// --- Input sanitization ---
		$payment_remarks = ($_POST['payment_remarks']);
		$hmo_no          = ($_POST['hmo_no']);
		$insurance_type  = ($_POST['Insurance_Type']);
		$hospital_no     = ($_POST['hosp_no']);
		$mem_no          = ($_POST['mem_no']);
		$g_relation      = ($_POST['g_relation']);
		$fullname        = ($_SESSION['fullname']);
		$current_datetime = date('Y-m-d H:i:s');
		$claim_valid_by = null;
		$current_datetime = null;

		if ($hmo_no === '' || $insurance_type === '' || $hospital_no === '') {
			echo "Please fill in all required fields.<br>";
			echo '<a href="index.php?ptm=ppt/' . htmlspecialchars($hmo_no) . '/' . htmlspecialchars($hospital_no) . '">Go Back</a>';
			exit;
		}

		// --- Begin transaction ---
		$db->beginTransaction();

		// 1️⃣ Update enrollee info
		$updateSQL = "UPDATE enrollee 
                      SET nhis_no = :nhis_no,
                          member = :member,
                          hmo_no = :hmo_no,
                          insurance = :insurance
                      WHERE hospital_no = :hospital_no";
		$stmt = $db->prepare($updateSQL);
		$stmt->execute([
			':nhis_no'     => $mem_no,
			':member'      => $g_relation,
			':hmo_no'      => $hmo_no,
			':insurance'   => $insurance_type,
			':hospital_no' => $hospital_no
		]);

		// 2️⃣ Fetch current insurance details
		$stmt = $db->prepare("
            SELECT e.nhis_no, e.insurance, e.hmo_no, i.insurance_name 
            FROM enrollee e 
            INNER JOIN insurance_tbl i ON e.hmo_no = i.insurance_no 
            WHERE e.hospital_no = :hosp_no 
            LIMIT 1");
		$stmt->execute([':hosp_no' => $hospital_no]);
		$rwx = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$rwx) {
			throw new Exception("Enrollee record not found for hospital_no: " . $hospital_no);
		}

		$insurance_existing = $rwx['insurance'];
		$hmo_no_existing    = $rwx['hmo_no'];
		$old_insurance_name = $rwx['insurance_name'];

		// 3️⃣ Update chart ledger insurance
		$stmt = $db->prepare("UPDATE chart_ledger SET insurance_no = :insurance_no WHERE hospital_no = :hospital_no");
		$stmt->execute([':insurance_no' => $hmo_no, ':hospital_no' => $hospital_no]);

		// 4️⃣ Apply discount flag if applicable
		$stmt = $db->prepare("SELECT sn FROM patient_discount WHERE individual_group_no = :hmo_no AND status = 'On-going' LIMIT 1");
		$stmt->execute([':hmo_no' => $hmo_no]);
		if ($stmt->fetch(PDO::FETCH_ASSOC)) {
			$stmt2 = $db->prepare("UPDATE enrollee SET discount_set = 1 WHERE hospital_no = :hospital_no");
			$stmt2->execute([':hospital_no' => $hospital_no]);
		}

		// 5️⃣ Add remark if insurance changed or note added
		if ($payment_remarks !== '' || $hmo_no_existing != $hmo_no || $insurance_existing != $insurance_type) {
			$desc = '<b>Updated:</b> ' . htmlspecialchars($hmo_no_existing) . '/' . htmlspecialchars($old_insurance_name)
				. '(' . htmlspecialchars($insurance_existing) . ') → '
				. htmlspecialchars($hmo_no) . '(' . htmlspecialchars($insurance_type) . ')';
			$full_remark = trim($payment_remarks . ' ' . $desc);

			$stmt = $db->prepare("
                INSERT INTO patients_remarks_tbl (hospital_no, remark, staff_name) 
                VALUES (:hospital_no, :remark, :staff_name)
            ");
			$stmt->execute([
				':hospital_no' => $hospital_no,
				':remark'      => $full_remark,
				':staff_name'  => $fullname
			]);
		}

		// 6️⃣ Handle “New File” Claim Auto-Creation
		$stmt = $db->prepare("SELECT hmo_no, insurance, visit_status FROM enrollee WHERE hospital_no = :hosp_no LIMIT 1");
		$stmt->execute([':hosp_no' => $hospital_no]);
		$rwx2 = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($rwx2 && $rwx2['visit_status'] === 'new') {

			// Check if "New File" was previously paid in cash
			$ckCash = $db->prepare("
                SELECT sn FROM patient_ap_services 
                WHERE item_services = 'New File' 
                  AND hospital_no = :hosp_no 
                  AND pay_mode = 'cash' 
                  AND pay > 0
                LIMIT 1");
			$ckCash->execute([':hosp_no' => $hospital_no]);

			if ($ckCash->fetch(PDO::FETCH_ASSOC)) {

				// === Fetch 'New file' service details ===
				$stmt = $db->prepare("SELECT sn, item_service, hosp_price, nhis_price, dept, category FROM prices_table WHERE item_service = 'New File' LIMIT 1");
				$stmt->execute();
				$priceRow = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!$priceRow) {
					throw new Exception("'New File' service not found in prices_table.");
				}

				$item_sn      = $priceRow['sn'];
				$item_service = $priceRow['item_service'];
				$hosp_price   = $priceRow['hosp_price'];
				$nhis_price   = $priceRow['nhis_price'];
				$dept_id      = $priceRow['dept_id'];
				$cat_type     = $priceRow['category'];

				$stmt = $db->prepare("
                        SELECT price 
                        FROM hmo_medical_tariff 
                        WHERE stock_sn = :item_sn AND price > 0 AND hmo = :hmo_no 
                        LIMIT 1");
				$stmt->execute([':item_sn' => $item_sn, ':hmo_no' => $hmo_no]);
				$tariff = $stmt->fetch(PDO::FETCH_ASSOC);
				$claim_amt = ($tariff) ? $tariff['price'] : $hosp_price;


				$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'System';
				$dept_id = isset($_SESSION['dept_id']) ? $_SESSION['dept_id'] : 0;
				$current_datetime = date('Y-m-d H:i:s');
				$invoice_no = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);

				// === Avoid duplicates ===
				$stmt = $db->prepare("
                    SELECT 1 FROM patient_ap_services 
                    WHERE hospital_no = :hospital_no AND item_services = :item_service AND pay_mode = 'claim' LIMIT 1
                ");
				$stmt->execute([':hospital_no' => $hospital_no, ':item_service' => $item_service]);

				if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
					// Insert new claim record
					$insertSQL = "
						INSERT INTO patient_ap_services
						(app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, 
						invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, date_entry, pay, pay_mode, 
						paystatus, process_claim, claim_valid_by)
						VALUES
						(:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, 0, 1,
						1, :invoice_no, :invoice_date, :invoice_by, :prepared_by, :date_entry, 0, 'claim', 0, 0, :claim_valid_by)";

					$stmtInsert = $db->prepare($insertSQL);
					$stmtInsert->execute([
						':app_no'         => $hospital_no,
						':hospital_no'    => $hospital_no,
						':serv_group'     => 'Registration',
						':cat_type'       => $cat_type,
						':dept_id'        => $dept_id,
						':drug_sn'        => $item_sn,
						':item_services'  => $item_service,
						':hosp_price'     => $hosp_price,
						':claim_amt'      => $claim_amt,
						':invoice_no'     => $invoice_no,
						':invoice_date'   => $current_datetime,
						':invoice_by'     => $fullname,
						':prepared_by'    => $fullname,
						':date_entry'     => $current_datetime,
						':claim_valid_by' => $claim_valid_by
					]);

					// Remove old 'cash' entry
					$del = $db->prepare("
                        DELETE FROM patient_ap_services 
                        WHERE hospital_no = :hospital_no AND item_services = 'New File' AND pay_mode = 'cash'");
					$del->execute([':hospital_no' => $hospital_no]);

					// Step 1: Select first matching consultation record
					$sel = $db->prepare("
							SELECT app_no FROM patient_ap_services 
							WHERE hospital_no = :hospital_no 
							AND serv_group = 'Consultation' 
							AND pay_mode = 'cash' 
							AND paystatus = 0
							LIMIT 1
						");
					$sel->execute([':hospital_no' => $hospital_no]);
					$row = $sel->fetch(PDO::FETCH_ASSOC);

					// Step 2: If found, delete from both tables
					if ($row) {
						$app_no = $row['app_no'];

						// Delete from patient_ap_services
						$del1 = $db->prepare("
								DELETE FROM patient_ap_services 
								WHERE hospital_no = :hospital_no 
								AND serv_group = 'Consultation' 
								AND pay_mode = 'cash' 
								AND paystatus = 0 
								AND app_no = :app_no
							");
						$del1->execute([
							':hospital_no' => $hospital_no,
							':app_no' => $app_no
						]);

						// Delete from apptm
						$del2 = $db->prepare("
							DELETE FROM apptm 
							WHERE hospital_no = :hospital_no 
							AND status = 'checkin'  
							AND queue_lock = 0 
							AND appt_no = :app_no
						");
						$del2->execute([
							':hospital_no' => $hospital_no,
							':app_no' => $app_no
						]);
					}
				}
			}
		}

		// ✅ Commit transaction
		$db->commit();

		// Redirect after success
		$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
		$current_url .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	?>
		<script type="text/javascript">
			window.location.href = "<?php echo htmlspecialchars($current_url . '&sv'); ?>";
		</script>
<?php
	} catch (Exception $e) {
		if ($db->inTransaction()) $db->rollBack();
		echo "<div class='error'><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</div>";
	}
}


if (isset($_POST["assign_submit_family"])) {

	try {
		$db->beginTransaction();

		$hmo_no = isset($_POST['hmo_no']) ? trim($_POST['hmo_no']) : '';
		$hosp_no = isset($_POST['hosp_no']) ? trim($_POST['hosp_no']) : '';
		$member = isset($_POST['member']) ? trim($_POST['member']) : '';
		$insurance_type = isset($_POST['Insurance_Type']) ? trim($_POST['Insurance_Type']) : '';
		$payment_remarks = isset($_POST['payment_remarks']) ? trim($_POST['payment_remarks']) : '';

		$total_family_member_allow = isset($_SESSION['total_family_member_allow']) ? intval($_SESSION['total_family_member_allow']) : 0;

		if ($hmo_no === '' || $hosp_no === '') {
			throw new Exception("Please fill in all fields");
		}

		// Fetch enrollee details once
		$stmt = $db->prepare("SELECT e.nhis_no,e.insurance,e.hmo_no,i.insurance_name 
                          FROM enrollee e 
                          INNER JOIN insurance_tbl i ON e.hmo_no=i.insurance_no 
                          WHERE hospital_no = :hosp_no LIMIT 1");
		$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
		$stmt->execute();
		$rwx = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$rwx) {
			throw new Exception("Enrollee not found for hospital number: " . htmlspecialchars($hosp_no));
		}

		$insurance_existing = $rwx['insurance'];
		$hmo_no_existing = $rwx['hmo_no'];
		$old_insurance_name = $rwx['insurance_name'];

		// Confirm Family Folder payment
		$stmt = $db->prepare("SELECT sn FROM patient_ap_services 
                          WHERE item_services = 'Family Folder' 
                          AND hospital_no = :hosp_no AND paystatus = 1");
		$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
			$payment_sn = $rwx['sn'];

			$stmt3 = $db->prepare("SELECT insurance_name FROM insurance_tbl WHERE family_folder_pay_id = :payment_sn");
			$stmt3->bindParam(':payment_sn', $payment_sn, PDO::PARAM_STR);
			$stmt3->execute();

			if ($stmt3->rowCount() == 0) {
				$updateSQL = "UPDATE insurance_tbl SET family_folder_pay_id = :payment_sn WHERE insurance_no = :hmo_no";
				$stmt2 = $db->prepare($updateSQL);
				$stmt2->bindParam(':payment_sn', $payment_sn, PDO::PARAM_STR);
				$stmt2->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
				$stmt2->execute();

				$updateSQL = "UPDATE patient_ap_services SET paystatus = '1', pay = 0 
                          WHERE hospital_no = :hosp_no AND item_services = 'New File'";
				$stmt2 = $db->prepare($updateSQL);
				$stmt2->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
				$stmt2->execute();
			} else {
				$rwxcc = $stmt3->fetch(PDO::FETCH_ASSOC);
				$insurance_name = $rwxcc['insurance_name'];
				throw new Exception("Family Payment Detail Already Exists And Assigned to Another Family Folder: " . htmlspecialchars($insurance_name));
			}
		} else {
			if ($total_family_member_allow > 0) {
				$stmt3 = $db->prepare("SELECT sn FROM enrollee WHERE hmo_no = :hmo_no AND insurance = 'Family'");
				$stmt3->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
				$stmt3->execute();

				$no_registered = $stmt3->rowCount();

				if ($no_registered >= $total_family_member_allow) {
					throw new Exception("Total members allowed exceeded! This patient must pay for a new file.");
				}

				$stmt4 = $db->prepare("SELECT insurance_no FROM insurance_tbl 
                                   WHERE insurance_no = :hmo_no AND family_folder_pay_id IS NOT NULL");
				$stmt4->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
				$stmt4->execute();

				if ($stmt4->rowCount() > 0) {
					$updateSQL = "UPDATE patient_ap_services SET paystatus = '1', pay = 0 
                              WHERE hospital_no = :hosp_no AND item_services = 'New File'";
					$stmt5 = $db->prepare($updateSQL);
					$stmt5->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
					$stmt5->execute();
				}
			}
		}

		// Update enrollee record
		$updateSQL = "UPDATE enrollee 
                  SET member = :member, hmo_no = :hmo_no, insurance = :insurance 
                  WHERE hospital_no = :hosp_no";
		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':member', $member, PDO::PARAM_STR);
		$stmt->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
		$stmt->bindParam(':insurance', $insurance_type, PDO::PARAM_STR);
		$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
		$stmt->execute();

		// Update chart ledger
		$updateSQL = "UPDATE chart_ledger SET insurance_no = :insurance_no WHERE hospital_no = :hosp_no";
		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':insurance_no', $hmo_no, PDO::PARAM_STR);
		$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
		$stmt->execute();

		// Apply discount if patient has an ongoing discount
		$stmt = $db->prepare("SELECT sn FROM patient_discount WHERE individual_group_no = :hmo_no AND status = 'On-going'");
		$stmt->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$discount_set = 1;
			$updateSQL = "UPDATE enrollee SET discount_set = :discount_set WHERE hospital_no = :hosp_no";
			$stmt2 = $db->prepare($updateSQL);
			$stmt2->bindParam(':discount_set', $discount_set, PDO::PARAM_INT);
			$stmt2->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
			$stmt2->execute();
		}

		// Log remarks if any
		$desc = '<b>Updated:</b> ' . $hmo_no_existing . '/' . $old_insurance_name . '(' . $insurance_existing  . ') to ' . $hmo_no . '(' . $insurance_type . ')';
		$full_remark = trim($payment_remarks . ' ' . $desc);

		$stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no, remark, staff_name) 
                          VALUES (:hosp_no, :remark, :staff_name)");
		$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
		$stmt->bindParam(':remark', $full_remark, PDO::PARAM_STR);
		$stmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
		$stmt->execute();

		$db->commit();
		$sv = 1;

		//echo "<h3 style='color:green;'>Update successful</h3>";
		//echo '<a href="index.php?ptm=ppt/' . htmlspecialchars($hmo_no) . '/' . htmlspecialchars($hosp_no) . '">Go Back</a>';
	} catch (Exception $e) {
		$db->rollBack();
		echo "<h3 style='color:red;'>Transaction Failed: " . $e->getMessage() . "</h3>";
		echo '<a href="index.php?ptm=ppt/' . htmlspecialchars($hmo_no) . '/' . htmlspecialchars($hosp_no) . '">Go Back</a>';
	}
}

if (isset($_POST["assign_submit_ppt"])) {
	try {
		// Begin Transaction
		$db->beginTransaction();

		$hosp_no = $_POST['hosp_no'];
		$payment_remarks = trim($_POST['payment_remarks']);
		$insurance = "Private(Self)";
		$hmo_no = "1000";

		// Fetch enrollee details once
		$stmt = $db->prepare("SELECT e.nhis_no,e.insurance,e.hmo_no,i.insurance_name 
        FROM enrollee e 
        INNER JOIN insurance_tbl i ON e.hmo_no=i.insurance_no 
        WHERE hospital_no = :hosp_no LIMIT 1");
		$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
		$stmt->execute();
		$rwx = $stmt->fetch(PDO::FETCH_ASSOC);

		$nhis_no = $rwx['nhis_no'];
		$insurance_existing = $rwx['insurance'];
		$hmo_no_existing = $rwx['hmo_no'];
		$old_insurance_name = $rwx['insurance_name'];

		// Set condition based on insurance type
		if ($insurance_existing === 'NHIS' && $nhis_no !== '') {
			$whereField = 'nhis_no';
			$whereValue = $nhis_no;
		} else {
			$whereField = 'hospital_no';
			$whereValue = $hosp_no;
		}

		// Update enrollee
		$updateSQL = "UPDATE enrollee 
                  SET hmo_no = :hmo_no, insurance = :insurance, nhis_no = null, discount_set = null 
                  WHERE {$whereField} = :where_value";
		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR);
		$stmt->bindParam(':insurance', $insurance, PDO::PARAM_STR);
		$stmt->bindParam(':where_value', $whereValue, PDO::PARAM_STR);
		$stmt->execute();

		// Update chart_ledger
		$stmt = $db->prepare("UPDATE chart_ledger SET insurance_no = null WHERE hospital_no = :hospital_no");
		$stmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
		$stmt->execute();

		// Handle “New File” Claim Auto-Creation
		$stmt = $db->prepare("SELECT hmo_no, insurance, visit_status FROM enrollee WHERE hospital_no = :hosp_no LIMIT 1");
		$stmt->execute([':hosp_no' => $hosp_no]);
		$rwx2 = $stmt->fetch(PDO::FETCH_ASSOC);





		if ($rwx2 && $rwx2['visit_status'] === 'new') {

			// Check if "New File" was previously paid in cash
			$ckCash = $db->prepare("
            SELECT sn FROM patient_ap_services 
            WHERE item_services = 'New File' 
              AND hospital_no = :hosp_no 
              AND pay_mode = 'claim' 
              AND pay = 0 LIMIT 1");
			$ckCash->execute([':hosp_no' => $hosp_no]);

			if ($ckCash->fetch(PDO::FETCH_ASSOC)) {

				// Fetch 'New file' service details
				$stmt = $db->prepare("SELECT sn, item_service, hosp_price, nhis_price, dept, category 
                                  FROM prices_table WHERE item_service = 'New File' LIMIT 1");
				$stmt->execute();
				$priceRow = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!$priceRow) {
					throw new Exception("'New File' service not found in prices_table.");
				}

				$item_sn      = $priceRow['sn'];
				$item_service = $priceRow['item_service'];
				$hosp_price   = $priceRow['hosp_price'];
				$nhis_price   = $priceRow['nhis_price'];
				$dept_id      = $priceRow['dept'];
				$cat_type     = $priceRow['category'];

				$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'System';
				$dept_id = isset($_SESSION['dept_id']) ? $_SESSION['dept_id'] : 0;
				$current_datetime = date('Y-m-d H:i:s');
				$invoice_no = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
				$claim_amt = 0;
				$claim_valid_by = $fullname;

				// Avoid duplicates
				$stmt = $db->prepare("
                SELECT 1 FROM patient_ap_services 
                WHERE hospital_no = :hospital_no AND item_services = :item_service AND pay_mode = 'cash' LIMIT 1 ");
				$stmt->execute([':hospital_no' => $hosp_no, ':item_service' => $item_service]);




				if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
					// Insert new claim record
					$insertSQL = "
                    INSERT INTO patient_ap_services
                    (app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, 
                    invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, date_entry, pay, pay_mode, 
                    paystatus, process_claim, claim_valid_by)
                    VALUES
                    (:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, 0, 1,
                    1, :invoice_no, :invoice_date, :invoice_by, :prepared_by, :date_entry, :pay, 'cash', 0, 0, :claim_valid_by)";
					$stmtInsert = $db->prepare($insertSQL);
					$stmtInsert->execute([
						':app_no'         => $hosp_no,
						':hospital_no'    => $hosp_no,
						':serv_group'     => 'Registration',
						':cat_type'       => $cat_type,
						':dept_id'        => $dept_id,
						':drug_sn'        => $item_sn,
						':item_services'  => $item_service,
						':hosp_price'     => $hosp_price,
						':claim_amt'      => $claim_amt,
						':invoice_no'     => $invoice_no,
						':invoice_date'   => $current_datetime,
						':invoice_by'     => $fullname,
						':prepared_by'    => $fullname,
						':date_entry'     => $current_datetime,
						':pay'            => $hosp_price,
						':claim_valid_by' => $claim_valid_by
					]);

					// Remove old 'claim' entry
					$del = $db->prepare("
                    DELETE FROM patient_ap_services 
                    WHERE hospital_no = :hospital_no AND item_services = 'New File' AND pay_mode = 'claim'");
					$del->execute([':hospital_no' => $hosp_no]);

					// Delete pending consultation claim + appointment
					$sel = $db->prepare("
                    SELECT app_no FROM patient_ap_services 
                    WHERE hospital_no = :hospital_no 
                    AND serv_group = 'Consultation' 
                    AND pay_mode = 'claim' 
                    AND paystatus = 0
                    LIMIT 1
                ");
					$sel->execute([':hospital_no' => $hosp_no]);
					$row = $sel->fetch(PDO::FETCH_ASSOC);

					if ($row) {
						$app_no = $row['app_no'];

						$del1 = $db->prepare("
                        DELETE FROM patient_ap_services 
                        WHERE hospital_no = :hospital_no 
                        AND serv_group = 'Consultation' 
                        AND pay_mode = 'claim' 
                        AND paystatus = 0 
                        AND app_no = :app_no
                    ");
						$del1->execute([
							':hospital_no' => $hosp_no,
							':app_no' => $app_no
						]);

						$del2 = $db->prepare("
                        DELETE FROM apptm 
                        WHERE hospital_no = :hospital_no 
                        AND status = 'checkin'  
                        AND queue_lock = 0 
                        AND appt_no = :app_no
                    ");
						$del2->execute([
							':hospital_no' => $hosp_no,
							':app_no' => $app_no
						]);
					}
				}
			}
		}

		// Insert remarks
		$desc = '<b>Updated:</b> ' . $hmo_no_existing . '/' . $old_insurance_name . '(' . $insurance_existing  . ') to Private(Self)';
		$full_remark = trim($payment_remarks . ' ' . $desc);
		$stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no, remark, staff_name) 
                          VALUES (:hospital_no, :remark, :staff_name)");
		$stmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
		$stmt->bindParam(':remark', $full_remark, PDO::PARAM_STR);
		$stmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
		$stmt->execute();

		// Commit Transaction
		$db->commit();
		$sv = 1;
	} catch (Exception $e) {
		// Rollback Transaction
		$db->rollBack();
		$sv = 0;
		error_log("Transaction failed: " . $e->getMessage());
	}
}


$username = $_SESSION['username'];
$stmt = $db->prepare("
    SELECT edit_patient_biodata_merge 
    FROM admin_users_rights 
    WHERE username = :username 
    LIMIT 1");
$stmt->execute([':username' => $username]);
$editValue = $stmt->fetchColumn();
$edit_patient_biodata_merge = $editValue !== false ? $editValue : null;


?>

<?php if ($ptm == '' and $hosp_no == '') {



?>


	<div class="row">
		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Patients Manager</h5>
				</div>
				<div class="ibox-content">

					<a href="index.php?ptm=all/" class="btn btn-app"><i class="fa fa-users"></i>All Patients</a>
					<a href="index.php?ptm=ppt/1000" class="btn btn-app"><i class="fa fa-th"></i>Private Patients</a>
					<a href="index.php?ptm=cpt/" class="btn btn-app"><i class="fa fa-building-o"></i>Corporate Patients</a>
					<?php if ($edit_patient_biodata_merge == 1) { ?>
						<a class="btn btn-app" data-toggle="modal" data-target="#myModal5">
							<i class="fa fa-user merge_patient_number"></i> <strong>Merge Hospital No.</strong></a>
					<?php } ?>
					<a href="index.php?ptm=missing/" class="btn btn-app"><i class="fa fa-building-o"></i>Missing Records</a>

					<hr>

					<table width="100%">
						<tr>
							<td>
								<a href="index.php?ptm=fld/" class="btn btn-app"><i class="fa fa-folder-open"></i>Family Folders</a>
								<a href="index.php?ptm=nhs" class="btn btn-app"><i class="fa fa-slideshare"></i>NHIS Patients</a>
								<a href="index.php?ptm=phs" class="btn btn-app"><i class="fa fa-file-o"></i>PHIS (HMOs)</a>
							</td>
							<td>

								<form action="index.php?ptm" method="POST">

									<h2>General Patient Search</h2><strong></strong>




									<div class="form_sep">
										<label for="reg_input_no" class="">Name or Phone or Hospital Number <small style="color: red;">[Press Enter key Enabled]</small> </label>
										<input type="text" id="search" name="search" class="form-control" style="border-color: black;" required>
									</div>
									<br>
									<div class="form_sep">


										<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action" id="apply_action" onclick="search_patient('frontdesk')"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
									</div>
								</form>



				</div>

				</td>
				</tr>
				</table>


				<hr>
				<?php if ($_SESSION['gen_claim_rpt'] == 1) { ?>
					<a href="index.php?cptclaim" class="btn btn-app"><i class="fa fa-building-o"></i>
						<p style="color:#F00">All Claims Reports</p>
					</a>

				<?php } ?>

				<a href="index.php?Specialist" class="btn btn-app"><i class="fa fa-plus"></i>
					<p style="color:darkmagenta"><strong>Specialist Services</strong></p>
				</a>


				<?php if ($_SESSION['see_med_rpt'] == 1) { ?>

					<a href="index.php?datab" class="btn btn-app"><i class="fa fa-database"></i>
						<p>Statistics & Data</p>
					</a>
					<br />
				<?php }
				?>

			</div>

		</div>
	</div>

	</div>

<?php } elseif (in_array($ptm, ['all', 'ppt', 'cpt', 'fld', 'nhs', 'phs', 'ext', 'missing']) && $hosp_no == '') {

	$show_tbl = 'n';
	$action = '';
	$search_part = '';
	$url = '';

	switch ($ptm) {
		case 'missing':
			$search_part = "WHERE hmo_no = '' OR insurance = '' OR hmo_no IS NULL OR insurance IS NULL";
			$show_tbl = 'y';
			$action = 'p';
			$url = 'all';
			break;

		case 'all':
			$search_part = "WHERE hmo_no != '' AND insurance != ''";
			$show_tbl = 'y';
			$action = 'p';
			$url = 'all';
			break;

		case 'ppt':
			$search_part = "WHERE hmo_no = '1000'";
			$show_tbl = 'y';
			$action = 'p';
			$url = 'ppt/1000';
			break;

		case 'cpt':
		case 'fld':
		case 'nhs':
		case 'phs':
			$insurance_map = [
				'cpt' => 'Corporate',
				'fld' => 'Family',
				'nhs' => 'NHIS',
				'phs' => 'PHIS'
			];

			if (empty($ptm_2)) {
				$search_part = "WHERE insurance_type = '{$insurance_map[$ptm]}'";
				$action = 'grp';
				$url = $ptm;
			} else {
				$search_part = "WHERE hmo_no = '$ptm_2'";
				$show_tbl = 'y';
				$action = 'p';
				$url = "$ptm/$ptm_2";
			}
			break;

		default:
			$show_tbl = 'n';
	}
?>

	<div class="row">

		<div class="col-lg-9">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Patient Masters</h5>
				</div>

				<div class="ibox-content">

					<?php
					if ($show_tbl === 'y' && $action === 'p') {
						$n = 1;
						$total_patients = 0;
						$no_female = 0;
						$no_male = 0;

						// It's better to SELECT only required columns rather than *
						$sql = "SELECT hospital_no, surname, fname, occupation, gender, credit_limit, date_capture, captured_by 	        FROM enrollee $search_part ORDER BY sn";
						$stmt = $db->query($sql);

						if ($stmt->rowCount() > 0) {
							$total_patients = $stmt->rowCount(); ?>

							<a href="index.php?ptm=<?php echo htmlspecialchars($ptm); ?>/">Previous Page</a>
							<hr>

							<table class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
									<tr>
										<th>No</th>
										<th>#</th>
										<th>Surname</th>
										<th>First Name</th>
										<th>Occupation</th>
										<th>Gender</th>
										<th>Credit Limit</th>
										<th>Date</th>
										<th>Captured By</th>
									</tr>
								</thead>
								<tbody>
									<?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$gender = strtolower(trim($rwx['gender']));
										if (in_array($gender, ['female', 'f'])) {
											$no_female++;
										} elseif (in_array($gender, ['male', 'm'])) {
											$no_male++;
										}
									?>
										<tr>
											<td><?php echo $n++; ?></td>
											<td>
												<a href="index.php?ptm=<?php echo htmlspecialchars($url . '/' . $rwx['hospital_no']); ?>">
													<?php echo htmlspecialchars($rwx['hospital_no']); ?>
												</a>
											</td>
											<td><?php echo htmlspecialchars($rwx['surname']); ?></td>
											<td><?php echo htmlspecialchars($rwx['fname']); ?></td>
											<td><?php echo htmlspecialchars($rwx['occupation']); ?></td>
											<td><?php echo ucfirst($gender); ?></td>
											<td><?php echo number_format($rwx['credit_limit']); ?></td>
											<td><?php
												// Check if the date_capture field is not empty
												if (!empty($rwx['date_capture'])) {
													// Format and display the date
													echo date("d, M y", strtotime($rwx['date_capture']));
												} else {
													// Optionally, you can echo a message or leave it blank
													echo '<strong>N/A</strong>'; // or simply leave this line out
												}
												?></td>
											<td><?php echo htmlspecialchars($rwx['captured_by']); ?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>

						<?php } else {
							$total_patients = 0;
						?>

							<strong>No Record Found!</strong><br><br>

							<a href="index.php?ptm=<?php echo htmlspecialchars($ptm); ?>/" class="btn btn-primary btn-xs">Goto Previous Page</a>
							&nbsp; | &nbsp;

							<input type="button" disabled
								name="book_app"
								value="&nbsp;&nbsp;&nbsp;&nbsp;Add New Patient&nbsp;&nbsp;&nbsp;&nbsp;"
								data-target="#modal"
								id="<?php echo htmlspecialchars($patient_type); ?>"
								class="btn btn-success btn-xs add_patient"
								<?php if ($_SESSION['add_new_patient'] == 0) echo 'disabled'; ?> />

							&nbsp; | &nbsp;

							<a href="index.php?ptm" class="btn btn-danger btn-xs">
								<i class="fa fa-times"></i> &nbsp; Close
							</a>

						<?php }
					}



					if ($action == 'grp') {
						$n = 1;
						$total_insurance = 0;
						$stmt = $db->query("SELECT * FROM insurance_tbl $search_part ORDER BY insurance_name");
						if ($stmt->rowCount() > 0) {
							$total_insurance = $stmt->rowCount();
						?>

							<table class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
									<tr>
										<th>#</th>
										<th>Name</th>
										<th>Interest</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>

									<?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
										<tr>
											<td><?php echo $rwx['insurance_no']; ?></td>
											<td><a href="index.php?ptm=<?php echo $url . '/' . $rwx['insurance_no']; ?>"><?php echo $rwx['insurance_name']; ?></a>
											</td>
											<td><?php echo $rwx['interest'] . ' (' . $rwx['add_minus'] . ')'; ?></td>

											<td>
												<input type="button" name="book_app" <?php if ($_SESSION['create_edit_insur'] == 0) { ?> disabled <?php } ?> value="Edit" data-target="#modal" id="<?php echo 'edit_' . $rwx['insurance_no'] . '_' . $ptm; ?>" class="btn btn-warning btn-xs add_insurance" />
												&nbsp; | &nbsp;
												<a href="index.php?ptm=<?php echo $url . '/' . $rwx['insurance_no']; ?>" class="btn btn-success btn-xs">View</a>
												&nbsp; | &nbsp;
												<?php
												$insur = $rwx['insurance_no'];
												$stmt_chk = $db->query("SELECT * FROM enrollee where hmo_no='$insur'");
												if ($stmt_chk->rowCount() > 0) {
													echo $stmt_chk->rowCount() . ' / <small>Patients</small>';
												} else { ?>

													<a href="index.php?ptm=<?php echo $ptm . '/' . $rwx['insurance_no'] . '&Idl'; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>

												<?php }
												?>
											</td>


										</tr>
								<?php $n++;
									}
								} ?>
								</tbody>
							</table>


						<?php } ?>


				</div>
			</div>
		</div>




		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Summary Panel </h5>
				</div>

				<div class="ibox-content">

					<?php
					if ($ptm == 'all' or $ptm == 'ppt') { ?>
						<div align="center">
							<input type="button" name="book_app" disabled <?php if ($_SESSION['add_new_patient'] == 0) { ?> disabled <?php } ?> value="&nbsp;&nbsp;&nbsp;&nbsp;Add New Patient&nbsp;&nbsp;&nbsp;&nbsp;" data-target="#modal" id="<?php echo $patient_type; ?>" class="btn btn-success btn-sm add_patient" />
						</div>
						<div class="form_sep"></div>
						<div class="form_sep"></div>
					<?php } else { ?>



						<?php
						if ($ptm_2 != '' and $hosp_no == '') {
							$stmt = $db->query("SELECT * FROM insurance_tbl where insurance_no='$ptm_2'");
							if ($stmt->rowCount() > 0) {
								$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
								$options = [
									'P' => 'Interest',
									'N' => 'Discount'
								];
								$I = isset($options[$rwx['add_minus']]) ? $options[$rwx['add_minus']] : '';

						?>

								<?php
								if ($ptm == 'all') {
									$patient_type = '1000';
								} elseif ($ptm == 'ppt') {
									$patient_type = '1000';
								} else {
									$patient_type = $rwx['insurance_no'];
								} ?>

								<div align="center">
									<input type="button" name="book_app" disabled <?php if ($_SESSION['add_new_patient'] == 0) { ?> disabled <?php } ?> value="&nbsp;&nbsp;&nbsp;&nbsp;Add New Patient&nbsp;&nbsp;&nbsp;&nbsp;" data-target="#modal" id="<?php echo $patient_type; ?>" class="btn btn-success btn-sm add_patient" disabled />
								</div>
								<div class="form_sep"></div>
								<div class="form_sep"></div>

								<strong style="font-size:12px">Insurance Name : </strong><br><strong><?php echo $rwx['insurance_no'] . ' / ' . $rwx['insurance_name']; ?></strong>
								<hr>
								<strong style="font-size:12px">Phone/Address: </strong><br><strong><?php echo $rwx['phone'] . ' /<br> ' . $rwx['addr']; ?></strong>
								<hr>
								<strong style="font-size:12px">Credit Limit Setup: </strong><br><strong><?php echo $rwx['credit_setup']; ?></strong>
								<hr>
								<strong style="font-size:12px">Maximum Members Allowed: </strong><br><strong><?php echo $_SESSION['total_family_member_allow']; ?></strong>
								<hr>
								<strong style="font-size:12px">Interest: <?php echo $rwx['interest'] . ' (' . $I . ')'; ?> / Status: <strong
										<?php
										if ($rwx['status'] == 'active') { ?> style="color:#009" <?php } else { ?>style="color:#F00" <?php } ?>><?php echo strtoupper($rwx['status']); ?></strong></strong><br>

								<?php if ($_SESSION['create_edit_insur'] == 1) { ?>

									<?php if ($rwx['status'] == 'active') { ?>
										<a href="index.php?ptm=<?php echo $ptm . '/' . $rwx['insurance_no'] . '&dx'; ?>"><strong style="color:#F00">[ De/Activate ]</strong></a>
									<?php } else { ?>
										<a href="index.php?ptm=<?php echo $ptm . '/' . $rwx['insurance_no'] . '&ax'; ?>"><strong style="color:#009">[ Activate Here ] </strong></a>

									<?php } ?>

								<?php } ?>

								&nbsp; | &nbsp;
								<input type="button" name="edit_insur" value="Edit" <?php if ($_SESSION['create_edit_insur'] == 0) { ?> disabled <?php } ?> data-target="#modal" id="<?php echo 'edit_' . $rwx['insurance_no'] . '_' . $ptm; ?>" class="btn btn-warning btn-xs add_insurance" />
								<?php if ($total_patients == 0) { ?>
									&nbsp; | &nbsp;
									<a href="index.php?ptm=<?php echo $ptm . '/' . $rwx['insurance_no'] . '&Idl'; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
								<?php } ?>


								<hr>



							<?php
							}
						} elseif ($ptm_2 == '' and $hosp_no == '' and $ptm != '') {
							if ($ptm == 'cpt' or $ptm == 'fld' or $ptm == 'nhs' or $ptm == 'phs') {
								$insur = 1;
								if ($ptm == 'cpt') {
									$p_title = 'Corporate Accounts';
									$insurance = 'Corporate';
								}
								if ($ptm == 'fld') {
									$p_title = 'Family Folders';
									$insurance = 'Family';
								}
								if ($ptm == 'nhs') {
									$p_title = 'NHIS Accounts';
									$insurance = 'NHIS';
								}
								if ($ptm == 'phs') {
									$p_title = 'Private HMOs';
									$insurance = 'PHIS';
								}
							?>


								<strong style="font-size:18px; "><?php echo $total_insurance; ?></strong> <strong style="font-size:12px">/ Total <?php echo $p_title ?></strong>

								<?php
								$stmt_Count = $db->query("SELECT hospital_no FROM enrollee where insurance='$insurance'");
								?>
								<div class="form_sep"></div>
								<div class="form_sep"></div>

								<strong style="font-size:18px; "><?php echo $stmt_Count->rowCount(); ?></strong> <strong style="font-size:12px">/ Total Patients</strong>

								<div class="form_sep"></div>
								<div class="form_sep"></div>

								<input type="button" name="add_insur" <?php if ($_SESSION['create_edit_insur'] == 0) { ?> disabled <?php } ?> value="Add New <?php echo $p_title; ?>" data-target="#modal" id="<?php echo 'new_' . $ptm . '_ '; ?>" class="btn btn-primary btn-xs add_insurance" />

						<?php
							}
						}

						if ($ptm == 'cpt' or $ptm == 'fld' or $ptm == 'nhs' or $ptm == 'phs') {
							$insur = 1;
							if ($ptm == 'cpt') {
								$p_title = 'Corporate Accounts';
								$insurance = 'Corporate';
							}
							if ($ptm == 'fld') {
								$p_title = 'Family Folders';
								$insurance = 'Family';
							}
							if ($ptm == 'nhs') {
								$p_title = 'NHIS Accounts';
								$insurance = 'NHIS';
							}
							if ($ptm == 'phs') {
								$p_title = 'Private HMOs';
								$insurance = 'PHIS';
							}
						}
					}

					if ($show_tbl == 'y') { ?>
						<strong style="font-size:18px; "><?php echo $total_patients; ?></strong> <strong style="font-size:12px">/ Total Patients</strong>
						<div class="form_sep"></div>
						<div class="form_sep"></div>

						<strong>Gender: <br> Male: &nbsp; <?php echo $no_male; ?> / Female: &nbsp; <?php echo $no_female; ?> </strong>
						<div class="form_sep"></div>
						<div class="form_sep"></div>




					<?php } ?>

				</div>

			</div>
		</div>

	</div>

<?php } elseif ($hosp_no > 0) { ?>

	<div class="row">

		<div class="col-lg-8">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Patient Data </h5>
					<div class="ibox-tools">


						<a href="index.php?ptm=<?php $url = "$ptm/";
												echo $url; ?>" class="btn btn-white btn-xs" style="font-size: 14px; color:black;">Back</a> &nbsp;

						<a href="index.php?ptm=<?php $url = "$ptm/$ptm_2";
												echo $url; ?>/" class="btn btn-white btn-xs" style="font-size: 14px; color:black;">Goto Patients List</a>


					</div>
				</div>


				<div class="ibox-content">

					<?php
					include_once("p_info.php");
					?>

				</div>

			</div>
		</div>


		<div class="col-lg-4">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Other Data </h5>
				</div>

				<div class="ibox-content">

					<?php if ($complain != '') { ?>
						<strong style="color:#F00"><?php echo $complain; ?></strong>
					<?php } ?>

					<table cellpadding="10%">
						<tr>
							<td>
								<img src="<?php if (file_exists(enrollee_p . $hosp_no . '.' . 'jpg')) {
												echo enrollee_p . $hosp_no . '.' . 'jpg';
											} else {
												echo '../img/no_photo.jpg';
											} ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar"> <br><br>
							</td>
							<td>
								<input type="button" name="Change ppt" value="Upload Passport" data-target="#myModal5" id="<?php echo $profile; ?>" class="btn btn-primary btn-xs change_ppt" />
							</td>
						</tr>
					</table>


					<table align="">
						<tr>
							<td style="padding:10px;">
								<input type="button" name="emr_idfront" value="EMR ID /Front" data-target="#myModal5" id="<?php echo $hosp_no . '/front'; ?>" class="btn btn-success btn-xs emr_id_front" />
							</td>
							<td>
								<input type="button" name="emr_idback" value="EMR ID /Back" data-target="#myModal5" id="<?php echo $hosp_no . '/back'; ?>" class="btn btn-success btn-xs emr_id_back" />
							</td>
						</tr>
					</table>

					<table class="table table-striped table-bordered">
						<?php
						// Count visits with status 'discharge'
						$stmtVisits = $db->query("SELECT COUNT(*) as visit_count FROM apptm WHERE hospital_no = '$hosp_no' AND status = 'discharge'");
						$visits = $stmtVisits->fetch(PDO::FETCH_ASSOC)['visit_count'];

						// Count admissions with adm_status '4'
						$stmtAdmissions = $db->query("SELECT COUNT(*) as adm_count FROM admission WHERE hospital_no = '$hosp_no' AND adm_status = '4'");
						$admissions = $stmtAdmissions->fetch(PDO::FETCH_ASSOC)['adm_count'];
						?>

						<tr>
							<td><strong>No of Visits:</strong></td>
							<td>&nbsp;<strong><?= $visits ?></strong></td>
						</tr>
						<tr>
							<td><strong>No of Admission:</strong></td>
							<td>&nbsp;<strong><?= $admissions ?></strong></td>
						</tr>

						<tr>
							<td><strong>Last Time Visited:</strong></td>
							<td><?php

								// Get the last visit
								$stmt1 = $db->prepare("SELECT ap_date_time 
                       FROM apptm 
                       WHERE hospital_no = :hosp_no 
                       ORDER BY ap_date_time DESC 
                       LIMIT 1");

								$stmt1->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
								$stmt1->execute();

								$lastVisit = $stmt1->fetch(PDO::FETCH_ASSOC);

								if ($lastVisit) {

									$lastDate = $lastVisit['ap_date_time'];
									$lastDate2 = date("Y-m-d", strtotime($lastDate));

									echo "<strong>Last Visit:</strong> ";
									echo date("d M, Y H:i:s", strtotime($lastDate));
									echo "<br>";

									// Get the visit before the last (must be earlier than last visit)
									$stmt2 = $db->prepare("SELECT ap_date_time 
                           FROM apptm 
                           WHERE hospital_no = :hosp_no 
                           AND DATE(ap_date_time) < :last_date
                           ORDER BY ap_date_time DESC 
                           LIMIT 1");

									$stmt2->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
									$stmt2->bindParam(':last_date', $lastDate2, PDO::PARAM_STR);
									$stmt2->execute();

									$prevVisit = $stmt2->fetch(PDO::FETCH_ASSOC);

									if ($prevVisit) {
										echo "<strong>Day Before Last Visit:</strong> ";
										echo date("d M, Y H:i:s", strtotime($prevVisit['ap_date_time']));
									} else {
										echo "<strong>Day Before Last Visit:</strong> No Visit Found";
									}
								} else {
									echo "<strong>No Previous Visit</strong>";
								}

								?>

							</td>
						</tr>
					</table>

					<hr>


					<a href="index.php?claims=<?php echo $hosp_no; ?>" class="btn btn-success block full-width m-b">View Patient Claims/Private Service Reports</a>

					<!--<a href="index.php?claims=&special_package" class="btn btn-info block full-width m-b">Special Package Validation</a> -->


					<input type="button" name="" value="Validate Package" data-target="#myModal5" id="<?= $hosp_no; ?>" class="btn btn-info block full-width m-b validate_package" /><br>

					<?php if ($_SESSION['billing_discount'] == 1) { ?>
						<input type="button" name="" value="Set/Edit Credit Limit for this Patient" data-target="#myModal5" id="<?= $hosp_no; ?>" class="btn btn-danger block full-width m-b add_credit_limit" /><br>
					<?php } ?>
					<?php if ($_SESSION['print_med_report'] == 1) { ?>

						<a href="#" class="btn btn-primary block full-width m-b" id="print-medical-report-btn" name="med_reports"> Print Patient's Medical Report </a>
					<?php } else { ?>
						<b>Print Patient's Medical Report Disabled</b>
					<?php } ?>
					<button type="button" class="btn btn-warning block full-width m-b" data-toggle="modal" data-target="#proc_medserv_modal" onclick="loadProcMedServData()"><i class="fa fa-list-alt"></i> Procedures & Medical Services</button>
					<hr>


					<form action="index.php?med=<?php echo $hosp_no; ?>" method="post">

						<div style="background-color: #FFC; padding:10px; ">
							<div class="form_sep">
								<label for="reg_input_no" class="req">Set Dates Range</label><br>
								<div class="form_sep" id="">
									<div class="input-daterange input-group" id="">
										<input type="date" class="form-control" name="start" value="" required />
										<span class="input-group-addon">to</span>
										<input type="date" class="form-control" name="end" value="" required />
									</div>

								</div>
							</div>


							<div class="form_sep">
								<button class="btn btn-success btn btn-sm" type="submit" <?php if ($_SESSION['see_med_rpt'] == 0) { ?> disabled <?php } ?> name="med_reports_" id="med_reports_">Show Medical Reports</button>
							</div>

							<div class="form_sep">
								<button class="btn btn-info btn btn-sm" type="submit" <?php if ($_SESSION['see_patient_log'] == 0) { ?> disabled <?php } ?> name="show_logs" id="show_logs">&nbsp;Patient Logs Reports &nbsp;</button>
							</div>

						</div>
						<input type="hidden" name="get_prev" value="<?php echo $get_prev ?>" />

					</form>

					<?php /*?><hr>
						<div style="padding:10px; ">

						<div class="form_sep">
						
						<input type="button" name="financial_rpt" <?php if($_SESSION['see_patient_tranc_writeoff']==0){ ?> disabled <?php }?> value="&nbsp; Transactions Reports&nbsp;" data-target="#modal" id="" class="btn btn-danger btn-sm financial_rpt" />
						</div>
						</div><?php */ ?>




				</div>

			</div>
		</div>

	</div>

<?php  } ?>



<div class="modal inmodal fade" id="insurance_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Insurance and Family Folder</h4>
			</div>

			<div class="modal-body" id="insurance_body">


			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="edit_patient_data_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">

				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

			</div>
			<div class="modal-body" id="edit_patient_data_body">

				<form action="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" method="POST">

					<?php

					$stmt = $db->query("SELECT nhis_no, insurance, hmo_no, nhis_no_ext FROM enrollee WHERE hospital_no='$hosp_no'");
					if ($stmt->rowCount() > 0) {
						$rw = $stmt->fetch(PDO::FETCH_ASSOC);
						$nhis_no = $rw['nhis_no'];
						$insurance = $rw['insurance'];
						$patient_type = $rw['hmo_no'];
						$nhis_no_ext = $rw['nhis_no_ext'];

						switch ($nhis_no_ext) {
							case '0':
								$nhs_ext = 'Principal';
								break;
							case '1':
								$nhs_ext = 'Spouse';
								break;
							case '2':
								$nhs_ext = 'Dependant 2';
								break;
							case '3':
								$nhs_ext = 'Dependant 3';
								break;
							case '4':
								$nhs_ext = 'Dependant 4';
								break;
							default:
								$nhs_ext = 'Extra Dependant';
								break;
						}

						$nhis_status = 1;
					} else {
						$nhis_status = 0;
						switch ($ptm) {
							case 'cpt':
								$insurance = 'Corporate';
								break;
							case 'fld':
								$insurance = 'Family';
								break;
							case 'nhs':
								$insurance = 'NHIS';
								break;
							case 'phs':
								$insurance = 'PHIS';
								break;
							default:
								$insurance = '';
								break;
						}
					}


					if ($insurance == 'NHIS') { ?>

						<div class="form_sep">
							<label for="reg_input_no" class="req">NHIS No.</label>
							<input type="text" id="nhis_membership_no" name="nhis_membership_no" class="form-control" <?php if ($nhis_no != '') { ?> readonly <?php } ?> value="<?php echo $nhis_no; ?>" required>
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="">Primary Provider (if change place)</label>
							<input type="text" id="primary_provider" name="primary_provider" class="form-control">
						</div>

						<?php if ($nhis_status == 1) { ?>

							<div class="form_sep">
								<label for="reg_select" class="req"><i>Select Available Vacant</i></label>
								<select name="dependant" id="dependant" class="form-control" required>
									<option selected="selected" value="">Select...</option>
									<option value="<?php echo htmlspecialchars($nhis_no_ext); ?>"><?php echo htmlspecialchars($nhs_ext); ?></option>

									<?php
									// Fetch all occupied nhis_no_ext values for the given nhis_no and insurance='NHIS'
									$stmt = $db->prepare("SELECT nhis_no_ext FROM enrollee WHERE nhis_no = :nhis_no AND insurance = 'NHIS'");
									$stmt->execute([':nhis_no' => $nhis_no]);
									$occupied = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

									// Define all possible roles for nhis_no_ext values
									$roles = [
										0 => 'Principal',
										1 => 'Spouse',
										2 => 'Dependant 2',
										3 => 'Dependant 3',
										4 => 'Dependant 4',
										5 => 'Extra Dependant'
									];

									foreach ($roles as $ext => $label) {
										if (!in_array($ext, $occupied)) {
											echo '<option value="' . $ext . '">' . htmlspecialchars($label) . '</option>';
										}
									}
									?>
								</select>
							</div>

						<?php } else { ?>

							<div class="form_sep">
								<label for="reg_select" class="req"><i>Select Available Vacant</i></label>
								<select name="dependant" id="dependant" class="form-control" required>
									<option selected="selected" value="">Select ...</option>
									<option value="0">Principal</option>
									<option value="1">Spouse</option>
									<option value="2">Dependant 2</option>
									<option value="3">Dependant 3</option>
									<option value="4">Dependant 4</option>
									<option value="5">Extra Dependant</option>
								</select>
							</div>

						<?php } ?>
						<div class="form_sep">
							<label for="reg_select" class="">Relationship</label>
							<select name="member" id="member" class="form-control">
								<option selected="selected" value="">Select ...</option>
								<option value="father">father</option>
								<option value="son">son</option>
								<option value="husband">husband</option>
								<option value="brother">brother</option>
								<option value="grandfather">grandfather</option>
								<option value="grandson">grandson</option>
								<option value="uncle">uncle</option>
								<option value="nephew">nephew</option>
								<option value="cousin">cousin</option>
								<option value="mother">mother</option>
								<option value="daughter">daughter</option>
								<option value="wife">wife</option>
								<option value="sister">sister</option>
								<option value="grandmother">grandmother</option>
								<option value="granddaughter">granddaughter</option>
								<option value="aunt">aunt</option>
								<option value="niece">niece</option>
								<option value="parent">parent</option>
								<option value="child">child</option>
								<option value="spouse">spouse</option>
								<option value="sibling">sibling</option>
								<option value="grandparents">grandparents</option>
								<option value="grandchild">grandchild</option>
								<option value="friend">friend</option>
								<option value="Others">Others</option>
							</select>
						</div>

						<div class="form_sep">
							<div class="form_sep" id="data_1">
								<label for="reg_input_no" class="">Date of NHIS Registration</label>
								<div class="input-group date">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="text" class="form-control" name="datenhis" id="datenhis">
								</div>
							</div>
						</div>

						<div class="form_sep">
							<div class="form_sep" id="data_1">
								<label for="reg_input_no" class="">NHIS Expiration Date</label>
								<div class="input-group date">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="text" class="form-control" name="expiry_date" id="expiry_date">
								</div>
							</div>
						</div>


						<input type="hidden" id="patient_type" name="patient_type" value="<?php echo $patient_type; ?>">
						<input type="hidden" id="insurance" name="insurance" value="<?php echo $insurance; ?>">

					<?php } elseif ($insurance == 'Corporate' or $insurance == 'PHIS') { ?>

						<div class="form_sep">
							<label for="reg_input_name" class="">Corporate/Membership Number:</label>
							<input type="text" id="nhis_membership_no" name="nhis_membership_no" class="form-control" value="<?php echo $nhis_no; ?>">
						</div>

						<div class="form_sep">
							<label for="reg_select" class="">Relationship</label>
							<select name="member" id="member" class="form-control">
								<option selected="selected" value="">Select ...</option>
								<option value="father">father</option>
								<option value="son">son</option>
								<option value="husband">husband</option>
								<option value="brother">brother</option>
								<option value="grandfather">grandfather</option>
								<option value="grandson">grandson</option>
								<option value="uncle">uncle</option>
								<option value="nephew">nephew</option>
								<option value="cousin">cousin</option>
								<option value="mother">mother</option>
								<option value="daughter">daughter</option>
								<option value="wife">wife</option>
								<option value="sister">sister</option>
								<option value="grandmother">grandmother</option>
								<option value="granddaughter">granddaughter</option>
								<option value="aunt">aunt</option>
								<option value="niece">niece</option>
								<option value="parent">parent</option>
								<option value="child">child</option>
								<option value="spouse">spouse</option>
								<option value="sibling">sibling</option>
								<option value="grandparents">grandparents</option>
								<option value="grandchild">grandchild</option>
								<option value="friend">friend</option>
								<option value="Others">Others</option>
							</select>
						</div>

						<input type="hidden" id="patient_type" name="patient_type" value="<?php echo $patient_type; ?>">
						<input type="hidden" id="insurance" name="insurance" value="<?php echo $insurance; ?>">

					<?php } elseif ($insurance == 'Family') { ?>
						<input type="hidden" id="patient_type" name="patient_type" value="<?php echo $ptm_2; ?>">
						<input type="hidden" id="insurance" name="insurance" value="<?php echo $insurance; ?>">

					<?php } else { ?>
						<input type="hidden" id="patient_type" name="patient_type" value="1000">
						<input type="hidden" id="insurance" name="insurance" value="Private(Self)">
					<?php } ?>

					<div class="form_sep"></div>



					<div class="form_sep">
						<label for="surname" class="req">Surname</label>
						<input type="text" id="surname" name="surname" class="form-control" required <?php if ($edit_patient_biodata_merge == 0) { ?> readonly<?php } ?>>
					</div>

					<div class="form_sep">
						<label for="fname" class="req">First Name</label>
						<input type="text" id="fname" name="fname" class="form-control" required <?php if ($edit_patient_biodata_merge == 0) { ?> readonly<?php } ?>>
					</div>

					<div class="form_sep">
						<label for="oname">Other Name</label>
						<input type="text" id="oname" name="oname" class="form-control" <?php if ($edit_patient_biodata_merge == 0) { ?> readonly<?php } ?>>
					</div>




					<div class="form_sep">
						<label for="reg_select" class="req">Gender</label>
						<select name="genderr" id="genderr" class="form-control" required>
							<option selected="selected" value="">Select...</option>
							<option value="Male">Male</option>
							<option value="Female">Female</option>
						</select>
					</div>

					<div class="form_sep">
						<label for="reg_input_name" class="req">Phone</label>
						<input type="text" id="phone" name="phone" class="form-control" required>
					</div>

					<?php if ($edit_patient_biodata_merge == 1) { ?>

						<div class="form_sep">
							<label for="reg_input_no" class="req">Date of Birth</label>
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="date" class="form-control" name="dob" id="dob" required>
							</div>
						</div>

					<?php } else { ?>
						<input type="hidden" id="dob" name="dob" value="">
					<?php } ?>

					<div class="form_sep">
						<label for="reg_input_name">Age</label>
						<input type="text" id="age" name="age" class="form-control" readonly>
					</div>

					<div class="form_sep">
						<label for="reg_input_name" class="req"> Occupation</label>
						<input type="text" id="occup" name="occup" class="form-control" required>
					</div>

					<div class="form_sep">
						<label for="reg_select" class="">Marital Status</label>
						<select name="marital" id="marital" class="form-control">

							<option selected="selected" value="">Select Marital...</option>


							<option value="Single ">Single </option>
							<option value="Married ">Married </option>
							<option value="Widowed ">Widowed </option>
							<option value="Divorced ">Divorced </option>
							<option value="Separated ">Separated </option>
						</select>
					</div>

					<div class="form_sep">
						<label for="reg_select">Blood Group</label>
						<select name="blood_group" id="blood_group" class="form-control">


							<option selected="selected" value="">Select Blood Group...</option>
							<option>-- select --</option>

							<option value="O-">O-</option>
							<option value="O+">O+</option>
							<option value="A+">A+</option>
							<option value="A-">A-</option>
							<option value="B-">B-</option>
							<option value="B+">B+</option>
							<option value="AB-">AB-</option>
							<option value="AB+">AB+</option>
						</select>
					</div>


					<div class="form_sep">
						<label for="reg_input_Geno_type"> Genotype</label>
						<input type="text" id="gtype" name="gtype" class="form-control">
					</div>


					<div class="form_sep">
						<label for="reg_input_name"> Email Address:</label>
						<input type="email" id="email" name="email" class="form-control" data-type="email">
					</div>

					<div class="form_sep">
						<label for="reg_select">Religion</label>
						<select name="religion" id="religion" class="form-control">

							<option selected="selected" value="">Select Religion...</option>
							<option value="Christianity">Christianity</option>
							<option value="Islam">Islam</option>
							<option value="Hindu">Hindu</option>
							<option value="Judaism">Judaism</option>
							<option value="Buddhism">Buddhism</option>
							<option value="Other">Others</option>
						</select>
					</div>

					<div class="form_sep">
						<label for="reg_select" class="">State/LGA</label>
						<select name="state_lga" id="state_lga" class="form-control">

							<option selected="selected" value="">Select...</option>
							<option value="N/A">N/A</option>
							<?php include("state_lga.php"); ?>
						</select>
					</div>

					<div class="form_sep">
						<label for="reg_input_name"> Specify Other State :</label>
						<input type="text" id="other_state" name="other_state" class="form-control">
					</div>

					<div class="form_sep">
						<label for="reg_input_name"> Tribe:</label>
						<input type="text" id="tribe" name="tribe" class="form-control">
					</div>

					<div class="form_sep">
						<label for="reg_input_name" class=""> Nationality:</label>
						<input type="text" id="nationality" name="nationality" class="form-control">
					</div>

					<div class="form_sep">
						<label for="reg_textarea_message" class="req">Address</label>
						<textarea name="addr" id="addr" cols="30" rows="4" class="form-control" data-minlength="15" required></textarea>
					</div>

					<?php if ($_SESSION['unit_head'] == 1 && $edit_patient_biodata_merge == 1) { ?>
						<div class="form_sep">
							<label for="reg_textarea_message" class="">In an emergency, share my medical details with: [Name / Patient Reviewer]</label>
							<textarea name="reviewer" id="reviewer" cols="30" rows="4" class="form-control" data-minlength="15"></textarea>
						</div>
					<?php } else { ?>
						<input type="hidden" id="reviewer" name="reviewer" value="">
					<?php } ?>

					<?php if ($_SESSION['h_code'] == 'police') { ?>


						<div class="form_sep">
							<label for="reg_select" class="">Rank/Civilian</label>
							<select name="police_ranks" id="police_ranks" class="form-control">
								<option value="Civilian">Civilian</option>
								<option value="Inspector-General">Inspector-General</option>
								<option value="Deputy Inspector-General">Deputy Inspector-General</option>
								<option value="Asst. Inspector-General">Asst. Inspector-General</option>
								<option value="Commissioner">Commissioner</option>
								<option value="Deputy Commissioner">Deputy Commissioner</option>
								<option value="Asst. Commissioner">Asst. Commissioner</option>
								<option value="Chief Superintendent">Chief Superintendent</option>
								<option value="Superintendent">Superintendent</option>
								<option value="Deputy Superintendent">Deputy Superintendent</option>
								<option value="Asst. Superintendent">Asst. Superintendent</option>
								<option value="Inspector">Inspector</option>
								<option value="Sergeant Major">Sergeant Major</option>
								<option value="Sergeant">Sergeant</option>
								<option value="Corporal">Corporal</option>
								<option value="Constable">Constable</option>
							</select>
						</div>


						<div class="form_sep">
							<label for="reg_input_name" class="">Command/Formation:</label>
							<input type="text" id="command_formation" name="command_formation" class="form-control">
						</div>

					<?php } else { ?>
						<input type="hidden" value="" name="police_rank">
						<input type="hidden" value="" name="command_formation">

					<?php } ?>





					<div class="form_sep">
						<div class="pull-left">
							<button type="submit" class="btn btn-success btn btn-sm" name="Save_patient" id="Save_patient">Save</button>
							<input type="hidden" id="hosp_no" name="hosp_no">

						</div>

						<div class="pull-right">
							<a href="index.php?ptm=<?php if ($ptm_2 == '') {
														echo $ptm . '/' . $hosp_no;
													} else {
														echo $ptm . '/' . $ptm_2 . '/' . $hosp_no;
													} ?>" class="btn btn-danger btn btn-sm">Close</a>

						</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="financial_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">View Patient Financial Report</h4>
			</div>

			<div class="modal-body" id="financial_body">

				<form action="index.php?tRpt=<?php echo $hosp_no; ?>" method="post">


					<div class="form_sep">
						<label for="reg_input_no" class="req">Set Dates Range</label><br>
						<div class="form_sep" id="">
							<div class="input-daterange input-group" id="">
								<input type="date" class="form-control" name="start" value="" required />
								<span class="input-group-addon">to</span>
								<input type="date" class="form-control" name="end" value="" required />
							</div>
						</div>
					</div>


					<div class="form_sep">
						<label for="reg_select" class="req">Transaction Report</label>
						<select name="Transaction_Report" id="Transaction_Report" class="form-control" required>

							<option selected="selected" value="">Select ...</option>
							<option value="Credits">Credits Items</option>
							<option value="Pending Invoice">Pending Invoices</option>
							<option value="Paid">Paid Items</option>
							<option value="Writeoff">WriteOff List</option>
							<option value="Cancel">Canceled and Others Items</option>
						</select>
					</div>

					<div class="form_sep">
						<button type="submit" class="btn btn-success btn btn-sm" name="Save_patient" id="Save_patient">Show Records</button>
						<input type="hidden" name="getvalue" value="<?php echo 'all/' . $hosp_no; ?>" </div>
				</form>


			</div>
		</div>
	</div>
</div>






<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""><?php echo $title_header; ?></h4>
			</div>

			<div class="modal-body" id="claims_body">

				<div class="alert alert-<?php echo $msg;  ?>"><?php echo $recep_msg; ?></div>
				<hr>
				<a href="" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>

			</div>
		</div>
	</div>
</div>


<div class="modal inmodal fade" id="emr_front_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""></h4>
			</div>

			<div class="modal-body" id="emr_id_body">


				<div id="content_front">
					<?php
					$stmtt = $db->query("SELECT fname,surname,oname,phone,date_capture,gender from enrollee WHERE hospital_no='$hosp_no'");
					if ($stmtt->rowCount() > 0) {
						$row_rstSelect = $stmtt->fetch(PDO::FETCH_ASSOC);

					?>
						<table width="323" border="0">
							<tr>
								<td width="317" height="167">
									<table width="320" border="0" align="left" height="120" cellpadding="0" cellspacing="0" id="searchBorder3" bgcolor="#FFFFFF">
										<tr>
											<td width="160" height="20" colspan="2"><img src="../img/logo.png" width="160" height="81"></td>
											<td width="160" height="20" colspan="2">
												<div align="right"><strong style="font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif; font-size:12px">Insurance Status:</strong><br />
													<strong style="font-family:'Trebuchet MS', Arial, Helvetica, sans-serif; font-size:16px"><?php if ($ptm_2 != "") {
																																					echo $ptm_2 . '/';
																																				} ?><?php echo $insurance; ?></strong>
												</div>
											</td>
										</tr>
										<tr>
											<td height="20">&nbsp;</td>
											<td colspan="3">&nbsp;</td>
										</tr>
										<tr>
											<td height="20" colspan="4"><strong style="font-family:Verdana, Geneva, sans-serif; font-size:14px">ID # </strong><strong style="font-family:Verdana, Geneva, sans-serif; font-size:20px"><?php echo $hosp_no; ?></strong></td>
										</tr>
										<tr>
											<td height="20" colspan="4"><strong style="font-family:Verdana, Geneva, sans-serif; font-size:16px"><?php echo $row_rstSelect['surname']; ?>,</strong> <strong style="font-family:Verdana, Geneva, sans-serif; font-size:20px"><?php echo $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname']; ?></strong></td>
										</tr>
										<tr>
											<td height="20" colspan="4"><strong style="font-family:Arial, Helvetica, sans-serif; font-size:12px">Gender: <?php echo strtoupper($row_rstSelect['gender']) ?> / Phone # <?php echo $row_rstSelect['phone'] ?> </strong></td>
										</tr>
										<tr>
											<td height="20">&nbsp;</td>
											<td colspan="3">
												<div align="right"><strong style="font-family:Arial, Helvetica, sans-serif; font-size:10px">Issued Date:<?php echo date('d M,Y', strtotime($row_rstSelect['date_capture'])) ?></strong></div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					<?php } ?>

				</div>

				<div class="form_sep" align="right" style="font-family:Arial, Helvetica, sans-serif">
					<div class="pull-right" style="margin-right:100px;">
						<a href="javascript:Clickheretoprint_front()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="patient_search_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Patient Search Modal</h4>
			</div>
			<div class="modal-body" id="patient_search_body">

			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="emr_back_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""></h4>
			</div>

			<div class="modal-body" id="emr_id_body">

				<div id="content_back">

					<table width="277" border="0" align="left" height="105" cellpadding="0" cellspacing="0" id="searchBorder" bgcolor="#FFFFFF" style="font-family:Arial, Helvetica, sans-serif">
						<tr>
							<td width="277" height="105" valign="top">
								<h5 align="center">This Card is the Property of<br />
									<b><?php echo $_SESSION['h_name']; ?></b><br />
									<?php echo $_SESSION['h_address']; ?><br />
									email:@gmail.com<br />
									Tel: <?php echo $_SESSION['h_phone']; ?>
									<<br />
									<i>If found, please return to the above address or to the nearest Police Station.</i><br />
									<br /><img src="../img/sign.jpg" alt="" width="101" height="25" /><span style="font-size:10px;"><br />
										Medical Director's Signature</span>
								</h5>
							</td>
						</tr>
					</table>



				</div>

				<div class="form_sep" align="right" style="font-family:Arial, Helvetica, sans-serif">
					<div class="pull-right" style="margin-right:100px;">
						<a href="javascript:Clickheretoprint_back()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>


<div class="modal inmodal fade" id="validate_package_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Validate Package</h4>
			</div>
			<div class="modal-body" id="validate_package_body">
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="add_credit_limit_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Credit Limit</h4>
			</div>
			<div class="modal-body" id="add_credit_limit_body">
			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function() {


		var input = document.getElementById("search");
		input.addEventListener("keyup", function(event) {
			if (event.keyCode === 13) {
				event.preventDefault();
				document.getElementById("apply_action").click();
			}
		});
	});





	function search_patient() {

		document.getElementById('apply_action').innerHTML = 'Wait ..';
		document.getElementById("apply_action").disabled = true;

		///var search_detials = document.getElementById("search").value;
		var text = document.getElementById("search").value;
		let search_detials = text.replace(/^\s+|\s+$/gm, '');

		let length = search_detials.length;

		if (length < 4) {
			toastr.error('Search must be more than 4 characters', 'Invalid Data ', {
				timeOut: 5000
			});
			document.getElementById('apply_action').innerHTML = 'Search';
			document.getElementById("apply_action").disabled = false;
			///exit;
			return 0;

		}
		$.ajax({
			url: "fetch_set2.php",
			method: "POST",
			data: {
				search_detials: search_detials
			},
			success: function(data) {

				setTimeout(function() {
					$("#overlay").fadeOut();
				}, 500);
				//toastr.info(data, 'Attention', {timeOut: 5000})//
				console.log(data)
				if (data.redirect != undefined) {
					/// index.php?presc&hos_no=000002 index.php?ptm=all/$in_patient
					window.location = 'index.php?ptm=all/' + data.hosp_no;
				} else {

					document.getElementById('apply_action').innerHTML = 'Search';
					document.getElementById("apply_action").disabled = false;

					if (data.trim() == 'NotFound') {
						toastr.error('Not match found', 'Error', {
							timeOut: 5000
						})
					} else {
						$('.modal-title').text('Search Patient');
						$('#patient_search_body').html(data);
						$('#patient_search_modal').modal('show');
					}
				}
			}

		});


	}
</script>
<script language="javascript">
	function Clickheretoprint_front() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
		var content_vlue = document.getElementById("content_front").innerHTML;

		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();

	}


	function Clickheretoprint_back() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
		var content_vlue = document.getElementById("content_back").innerHTML;

		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();
	}
</script>



<?php include_once('../doctor/_medical_report_modal.php'); ?>
<?php include_once('_proc_medserv_modal.php'); ?>