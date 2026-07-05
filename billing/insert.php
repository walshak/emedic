<?php include("../Connections/Conn.php"); ?>

<?php
session_start();


if ($_POST["MM_update"] == 'add_money_confirm') {


	$amount_clean = str_replace(',', '', $_POST['amount']);

	try {
		// Prepare the DELETE statement
		$deleteSQL = "DELETE FROM billing_dep_confirm WHERE hospitla_no = :hospitla_no";
		$deleteStmt = $db->prepare($deleteSQL);
		$deleteStmt->bindParam(':hospitla_no', $_POST['emr'], PDO::PARAM_STR);
		$deleteStmt->execute();

		// Prepare the INSERT statement
		$insertSQL = "INSERT INTO billing_dep_confirm(hospitla_no, transc_type, hosp_no, amount, current_balance, mode_pay, descrip,insurance_no) 
					  VALUES (:hospitla_no, :transc_type, :hosp_no, :amount, :current_balance, :mode_pay, :descrip, :insurance_no)";
		$insertStmt = $db->prepare($insertSQL);

		// Bind parameters
		$insertStmt->bindParam(':hospitla_no', $_POST['emr'], PDO::PARAM_STR);
		$insertStmt->bindParam(':transc_type', $_POST['transaction_type'], PDO::PARAM_STR);
		$insertStmt->bindParam(':hosp_no', $_POST['patient_no_transfer'], PDO::PARAM_STR);
		///$insertStmt->bindParam(':auth_code', $_POST['auto_code'], PDO::PARAM_STR);
		$insertStmt->bindParam(':amount', $amount_clean, PDO::PARAM_STR);
		$insertStmt->bindParam(':current_balance', $_POST['current_balance'], PDO::PARAM_STR);
		$insertStmt->bindParam(':mode_pay', $_POST['mode_pay'], PDO::PARAM_STR);
		$insertStmt->bindParam(':descrip', $_POST['desc'], PDO::PARAM_STR);
		$insertStmt->bindParam(':insurance_no', $_POST['insurance_no'], PDO::PARAM_STR);

		// Execute the INSERT statement
		$insertStmt->execute();
	} catch (PDOException $e) {
		// Handle errors here
		echo "Error: " . $e->getMessage();
	}
}

if ($_POST["MM_update"] == 'add_discount_insert') {

	$setdate = date("Y-m-d");
	if ($_POST['service_type'] == 'All Services') {
		$status = 'On-going';
	} else {
		$status = 'Pending';
	}



	if ($_POST['how_long'] == 'Specify' and ($_POST['specify_count'] == '' or $_POST['specify_count'] == '0')) {
		$specify_count = 1;
	} else {
		$specify_count = $_POST['specify_count'];
	}

	$history = ' - ' . $_POST['discount_charge1'] . ': ' . $_POST['mode'] . ':' . $_POST['mode_value'] . '; Duration: ' . $_POST['how_long'] . '; Count: ' . $specify_count . '; Service Type: ' . $_POST['service_type'] . ';  Setup By: ' . $_SESSION['fullname'] . '; Dated: ' . $setdate;

	$emr = $_POST['emr'];
	$stmt = $db->prepare("SELECT * FROM patient_discount WHERE individual_group_no = :emr");
	$stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
	$stmt->execute();

	$individual_group = "individual";
	$empty = "";

	if ($stmt->rowCount() == 0) {
		$add_data = "INSERT INTO patient_discount (individual_group, individual_group_no, individual_group_name, discount_charge, percentage_flat, percentage_flat_value, duration, specify_count, apply_to_services, services_items, count_bal, setby, history, status_date, status) 
					  VALUES (:individual_group, :individual_group_no, :individual_group_name, :discount_charge, :percentage_flat, :percentage_flat_value, :duration, :specify_count, :apply_to_services, :services_items, :count_bal, :setby, :history, :status_date, :status)";

		$stmt_insert = $db->prepare($add_data);
		$stmt_insert->bindParam(':individual_group', $individual_group, PDO::PARAM_STR);
		$stmt_insert->bindParam(':individual_group_no', $_POST['emr'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':individual_group_name', $_POST['patient_name'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':discount_charge', $_POST['discount_charge'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':percentage_flat', $_POST['mode'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':percentage_flat_value', $_POST['mode_value'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':duration', $_POST['how_long'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':specify_count', $_POST['specify_count'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':apply_to_services', $_POST['service_type'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':services_items', $empty, PDO::PARAM_STR);
		$stmt_insert->bindParam(':count_bal', $specify_count, PDO::PARAM_STR);
		$stmt_insert->bindParam(':setby', $_SESSION['fullname'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':history', $history, PDO::PARAM_STR);
		$stmt_insert->bindParam(':status_date', $setdate, PDO::PARAM_STR);
		$stmt_insert->bindParam(':status', $status, PDO::PARAM_STR);

		$stmt_insert->execute();

		// Update discount_set in enrollee table
		$stmt_update = $db->prepare("UPDATE enrollee SET discount_set = 1 WHERE hospital_no = :hospital_no");
		$stmt_update->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
		$stmt_update->execute();
	}
}



if ($_POST["MM_update"] == 'update_discount') {

	$setdate = date("Y-m-d");

	if ($_POST['service_type1'] == 'All Services') {
		$status = 'On-going';
	} else {
		$status = 'Pending';
	}

	if ($_POST['how_long1'] == 'Specify' and ($_POST['specify_count1'] == '' or $_POST['specify_count1'] == '0')) {
		$specify_count1 = 1;
	} else {
		$specify_count1 = $_POST['specify_count1'];
	}

	$history = ' - ' . $_POST['discount_charge1'] . ': ' . $_POST['mode1'] . ':' . $_POST['mode_value1'] . '; Duration: ' . $_POST['how_long1'] . '; Count: ' . $specify_count1 . '; Service Type: ' . $_POST['service_type1'] . ';  Setup By: ' . $_SESSION['fullname'] . '; Dated: ' . $setdate;

	$emr = $_POST['emr'];

	$serv_type = $_POST['service_type1'];
	$stmt44 = $db->query("SELECT * FROM patient_discount where individual_group_no='$emr' and apply_to_services='specify'");

	if ($stmt44->rowCount() > 0 and $serv_type == 'All Services') {
		$update = "DELETE FROM patient_discount_services WHERE individual_group_no='$emr'";
		$db->exec($update);
	}


	$stmt = $db->query("SELECT * FROM patient_discount where individual_group_no='$emr'");
	if ($stmt->rowCount() > 0) {
		$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
		$history_old = $rowx['history'];

		if ($history != $history_old) {
			$save_history = $history_old . '<br>' . $history;
		} else {
			$save_history = $history;
		}
	}

	try {
		// Prepare the UPDATE statement
		$updateSQL = "UPDATE patient_discount SET 
                    discount_charge = :discount_charge, 
                    percentage_flat = :percentage_flat, 
                    percentage_flat_value = :percentage_flat_value, 
                    duration = :duration, 
                    apply_to_services = :apply_to_services, 
                    specify_count = :specify_count, 
                    count_bal = :count_bal, 
                    setby = :setby, 
                    history = :history, 
                    status = :status 
                  WHERE sn = :sn";
		$updateStmt = $db->prepare($updateSQL);

		// Bind parameters
		$updateStmt->bindParam(':discount_charge', $_POST['discount_charge1'], PDO::PARAM_STR);
		$updateStmt->bindParam(':percentage_flat', $_POST['mode1'], PDO::PARAM_STR);
		$updateStmt->bindParam(':percentage_flat_value', $_POST['mode_value1'], PDO::PARAM_STR);
		$updateStmt->bindParam(':duration', $_POST['how_long1'], PDO::PARAM_STR);
		$updateStmt->bindParam(':apply_to_services', $_POST['service_type1'], PDO::PARAM_STR);
		$updateStmt->bindParam(':specify_count', $specify_count1, PDO::PARAM_STR);
		$updateStmt->bindParam(':count_bal', $specify_count1, PDO::PARAM_STR);
		$updateStmt->bindParam(':setby', $_SESSION['fullname'], PDO::PARAM_STR);
		$updateStmt->bindParam(':history', $save_history, PDO::PARAM_STR);
		$updateStmt->bindParam(':status', $status, PDO::PARAM_STR);
		$updateStmt->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);

		// Execute the UPDATE statement
		$updateStmt->execute();
	} catch (PDOException $e) {
		// Handle errors here
		echo "Error: " . $e->getMessage();
	}
}

if ($_POST["MM_update"] == 'add_services_list') {

	$setdate = date("Y-m-d");
	if (isset($_GET['service_type']) and $_GET['service_type'] != '') {
		$service_type = $_POST['service_type'];
	} elseif (isset($_GET['service_type3']) and $_GET['service_type3'] != '') {
		$service_type = $_POST['service_type3'];
	}

	if ($service_type == 'investigation') {
		$Investigation = $_POST['Investigation'];
		$parts = explode("__", $Investigation);
		$service_sn = $parts['0'];
		$service_item = $parts['1'];
	} elseif ($service_type == 'pharmacy') {
		$pharmacy = $_POST['pharmacy'];
		$parts = explode("__", $pharmacy);
		$service_sn = $parts['0'];
		$service_item = $parts['1'];
	} elseif ($service_type == 'nursing') {
		$nursing = $_POST['nursing'];
		$parts = explode("__", $nursing);
		$service_sn = $parts['0'];
		$service_item = $parts['1'];
	} elseif ($service_type == 'med') {
		$Medical = $_POST['Medical'];
		$parts = explode("__", $Medical);
		$service_sn = $parts['0'];
		$service_item = $parts['1'];
	} elseif ($service_type == 'others') {
		$others = $_POST['others'];
		$parts = explode("__", $others);
		$service_sn = $parts['0'];
		$service_item = $parts['1'];
	}


	if ($_POST['how_long1'] == 'Specify' and ($_POST['specify_count1'] == '' or $_POST['specify_count1'] == '0')) {
		$specify_count1 = 1;
	} else {
		$specify_count1 = $_POST['specify_count1'];
	}

	$stmt = $db->prepare("SELECT * FROM patient_discount_services WHERE service_sn = :service_sn AND service_item = :service_item");
	$stmt->bindParam(':service_sn', $service_sn, PDO::PARAM_STR);
	$stmt->bindParam(':service_item', $service_item, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() == 0) {
		if ($service_sn != '' && $service_item != '' && $service_type != '') {
			$insertSQL = "INSERT INTO patient_discount_services (
									patient_discount_sn, individual_group_no,service_center, service_sn, service_item, 
									discount_charge, percentage_flat, percentage_flat_value, duration, 
									specify_count, count_bal, setby, status_date
								  ) VALUES (
									:patient_discount_sn, :individual_group_no, :service_center,:service_sn, :service_item, 
									:discount_charge, :percentage_flat, :percentage_flat_value, :duration, 
									:specify_count, :count_bal, :setby, :status_date
								  )";

			$stmtInsert = $db->prepare($insertSQL);
			$stmtInsert->bindParam(':patient_discount_sn', $_POST['id'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':individual_group_no', $_POST['emr'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':service_center', $service_type, PDO::PARAM_STR);
			$stmtInsert->bindParam(':service_sn', $service_sn, PDO::PARAM_STR);
			$stmtInsert->bindParam(':service_item', $service_item, PDO::PARAM_STR);
			$stmtInsert->bindParam(':discount_charge', $_POST['discount_charge'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':percentage_flat', $_POST['mode1'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':percentage_flat_value', $_POST['mode_value1'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':duration', $_POST['how_long1'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':specify_count', $specify_count1, PDO::PARAM_STR);
			$stmtInsert->bindParam(':count_bal', $specify_count1, PDO::PARAM_STR);
			$stmtInsert->bindParam(':setby', $_SESSION['fullname'], PDO::PARAM_STR);
			$stmtInsert->bindParam(':status_date', $setdate, PDO::PARAM_STR);
			$stmtInsert->execute();

			$updateSQL = "UPDATE patient_discount SET status = :status WHERE individual_group_no = :individual_group_no";
			$stmtUpdate = $db->prepare($updateSQL);
			$stmtUpdate->bindParam(':status', $status = 'On-going', PDO::PARAM_STR);
			$stmtUpdate->bindParam(':individual_group_no', $_POST['emr'], PDO::PARAM_STR);
			$stmtUpdate->execute();
		}
	}
}
