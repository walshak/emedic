<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('declared.php');
include("../inc/credit_current_balance.php");

?>
<!DOCTYPE html>
<html>

<?php include("../inc/header.php"); ?>
<?php

if (isset($_POST['save_credit_limit'])) {
	include("../process_credit_limit.php");
}

if (isset($_GET["can_y_credit"])) {
	include("../process_credit_limit.php");
}


$bal = 0;

$sql = "SELECT sn, department FROM department";
$stmt = $db->prepare($sql);
$stmt->execute();
$departments = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$departments[$row['sn']] = $row['department']; // Use ID as key and name as value
}

if (isset($_POST['print_invoice_adv'])) {
	$emr = $_POST['emr'];
	$patient_name = $_POST['patient_name2'];
	$search_datee = $_POST['search_datee'];

	header("location:pacct.php?emr=$emr&invoice_stt&vsbl&name=$patient_name&search_datee=$search_datee");
	exit;
}


if (isset($_POST['Save'])) {

	$setdate = date("Y-m-d");
	if (isset($_POST['service_type']) and $_POST['service_type'] != '') {
		$service_type = $_POST['service_type'];
	} elseif (isset($_POST['service_type3']) and $_POST['service_type3'] != '') {
		$service_type = $_POST['service_type3'];
	}

	$input_map = array(
		'investigation'     => 'Investigation',
		'pharmacy'          => 'pharmacy',
		'nursing'           => 'nursing',
		'medical_services'  => 'Medical',
		'others'            => 'others'
	);

	if (isset($input_map[$service_type]) && isset($_POST[$input_map[$service_type]])) {
		$input_value = $_POST[$input_map[$service_type]];
		$parts = explode("__", $input_value);
		$service_sn = isset($parts[0]) ? $parts[0] : '';
		$service_item = isset($parts[1]) ? $parts[1] : '';
	} else {
		$service_sn = '';
		$service_item = '';
	}

	$specify_count1 = (isset($_POST['how_long1'], $_POST['specify_count1']) &&
		$_POST['how_long1'] === 'Specify' &&
		($_POST['specify_count1'] === '' || $_POST['specify_count1'] === '0'))
		? 1
		: $_POST['specify_count1'];


	try {
		$db->beginTransaction();

		$stmt = $db->prepare("SELECT * FROM patient_discount_services where service_sn=:service_sn and service_item=:service_item and individual_group_no=:individual_group_no");
		$stmt->bindParam(':service_sn', $service_sn);
		$stmt->bindParam(':service_item', $service_item);
		$stmt->bindParam(':individual_group_no', $_POST['emr']);
		$stmt->execute();

		if ($stmt->rowCount() == 0) {

			if ($service_sn != '' and $service_item != '') {

				$insertSQL = "INSERT INTO patient_discount_services (
                    patient_discount_sn, individual_group_no,service_center, service_sn, service_item, 
                    discount_charge, percentage_flat, percentage_flat_value, duration, 
                    specify_count, count_bal, setby, status_date
                  ) VALUES (
                    :patient_discount_sn, :individual_group_no, :service_center,:service_sn, :service_item, 
                    :discount_charge, :percentage_flat, :percentage_flat_value, :duration, 
                    :specify_count, :count_bal, :setby, :status_date
                  )";

				$stmt = $db->prepare($insertSQL);
				$stmt->bindParam(':patient_discount_sn', $_POST['id']);
				$stmt->bindParam(':individual_group_no', $_POST['emr']);
				$stmt->bindParam(':service_center', $service_type, PDO::PARAM_STR);
				$stmt->bindParam(':service_sn', $service_sn);
				$stmt->bindParam(':service_item', $service_item);
				$stmt->bindParam(':discount_charge', $_POST['discount_charge']);
				$stmt->bindParam(':percentage_flat', $_POST['mode1']);
				$stmt->bindParam(':percentage_flat_value', $_POST['mode_value1']);
				$stmt->bindParam(':duration', $_POST['how_long1']);
				$stmt->bindParam(':specify_count', $specify_count1);
				$stmt->bindParam(':count_bal', $specify_count1);
				$stmt->bindParam(':setby', $_SESSION['fullname']);
				$stmt->bindParam(':status_date', $setdate);
				$stmt->execute();

				$status = "On-going";
				$stmt2 = $db->prepare("UPDATE patient_discount SET status=:status WHERE individual_group_no=:individual_group_no");
				$stmt2->bindParam(':status', $status);
				$stmt2->bindParam(':individual_group_no', $_POST['emr']);
				$stmt2->execute();

				$db->commit();
				if (isset($_POST['service_type']) and $_POST['service_type'] != '') {
					$header = $_POST['emr'];
					header("location:pacct.php?emr=$header&sv");
				} elseif (isset($_POST['service_type3']) and $_POST['service_type3'] != '') {
					$service_type = $_POST['service_type3'];
					header("location:index.php?discount&sv");
				}
			} else {


				if (isset($_POST['service_type']) and $_POST['service_type'] != '') {
					$header = $_POST['emr'];
					header("location:pacct.php?emr=$header&err_selection");
				} elseif (isset($_POST['service_type3']) and $_POST['service_type3'] != '') {
					$service_type = $_POST['service_type3'];
					header("location:index.php?discount&err_selection");
				}
			}
		} else {

			if (isset($_POST['service_type']) and $_POST['service_type'] != '') {
				$header = $_POST['emr'];
				header("location:pacct.php?emr=$header&err_selection");
			} elseif (isset($_POST['service_type3']) and $_POST['service_type3'] != '') {
				$service_type = $_POST['service_type3'];
				header("location:index.php?discount&err_selection");
			}
		}
	} catch (PDOException $e) {
		$db->rollBack();
		echo "Error: " . $e->getMessage();
	}
	exit;
}


if (isset($_POST['amount_paying_now'])) {

	$pay_mode = 'cash';
	$setdate = date('Y-m-d H:i:s');
	$amount_paying = $_POST['pay'];
	$cat_type = $_POST['cat_type'];
	$amount_to_paid = $_POST['amount_to_paid'];
	$item_services = $_POST['item_services'];
	$balance = $amount_to_paid - $amount_paying;
	$last_sale_sn = $_POST['last_sale_sn'];
	$payment_option = $_POST['payment_option'];

	///include_once("../inc/utilities.php");
	///$invoice_no=INV() . $_POST['emr'];

	// Default invoice status
	$invoice_status = 1;

	// Initialize payment values safely
	$new_pay = 0;
	$old_pay = 0;

	// Decide payment handling based on option selected
	if ($payment_option == 0) {
		// Part payment: pay what is available now
		$new_pay = $amount_paying;
		$old_pay = $balance;

		// Indicate partial payment in service description
		$item_services .= ' (Part)';
	} else {
		// Full or remaining balance payment
		$new_pay = $balance;
		$old_pay = $amount_paying;

		// No label modification needed here
	}


	if ($balance > 0) {


		/// last_sale_sn
		// Check if the special package entry already exists
		$remarks = 'part';
		$serv_group = 'Medical Services';
		$one = '1';
		$stmt = $db->prepare("SELECT dept_id,created_by,invoice_no FROM patient_ap_services WHERE sn = :last_sale_sn");
		$stmt->bindParam(':last_sale_sn', $last_sale_sn, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$dept_id = $row['dept_id'];
			$created_by = $row['created_by'];
			$invoice_no = $row['invoice_no'];
			try {
				// Begin transaction
				$db->beginTransaction();

				// Insert new entry into patient_ap_services  /// created_by
				$insertSQL = "INSERT INTO patient_ap_services (app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, remarks, invoice_status, invoice_no, invoice_by, invoice_date, qty, prepared_by, date_entry, pay, pay_mode, created_by) 
							VALUES (:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :remarks, :invoice_status, :invoice_no, :invoice_by, :invoice_date, :qty, :prepared_by, :date_entry, :pay, :pay_mode, :created_by)";

				$stmt_insert = $db->prepare($insertSQL);
				$stmt_insert->bindParam(':app_no', $_POST['app_no'], PDO::PARAM_STR);
				$stmt_insert->bindParam(':hospital_no', $_POST['emr'], PDO::PARAM_STR);
				$stmt_insert->bindParam(':serv_group', $serv_group, PDO::PARAM_STR);
				$stmt_insert->bindParam(':cat_type', $cat_type, PDO::PARAM_STR);
				$stmt_insert->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
				$stmt_insert->bindParam(':drug_sn', $_POST['last_sale_sn'], PDO::PARAM_STR);
				$stmt_insert->bindParam(':item_services', $item_services, PDO::PARAM_STR);
				$stmt_insert->bindParam(':hosp_price', $balance, PDO::PARAM_STR);
				$stmt_insert->bindParam(':remarks', $remarks, PDO::PARAM_STR);
				$stmt_insert->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
				$stmt_insert->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
				$stmt_insert->bindParam(':invoice_by', $_SESSION['fullname'], PDO::PARAM_STR);
				$stmt_insert->bindParam(':invoice_date', $setdate, PDO::PARAM_STR);
				$stmt_insert->bindParam(':qty', $one, PDO::PARAM_STR);
				$stmt_insert->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
				$stmt_insert->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
				$stmt_insert->bindParam(':pay', $new_pay, PDO::PARAM_STR);
				$stmt_insert->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
				$stmt_insert->bindParam(':created_by', $created_by, PDO::PARAM_STR);
				$stmt_insert->execute();

				// Check if the insert operation was successful
				if ($stmt_insert->rowCount() > 0) {
					$cr = 1;
					// Update pay in patient_ap_services for the previous entry
					$updateSQL = "UPDATE patient_ap_services SET pay = :amount_paying,invoice_status=1,remarks='part',tag='target' WHERE sn = :last_sale_sn";
					$stmt_update = $db->prepare($updateSQL);
					$stmt_update->bindParam(':amount_paying', $old_pay, PDO::PARAM_STR);
					$stmt_update->bindParam(':last_sale_sn', $last_sale_sn, PDO::PARAM_STR);

					$stmt_update->execute();

					// Commit transaction
					$db->commit();

					$emr = $_POST['emr'];
					header("location:pacct.php?emr=$emr&pay");
					exit; // Stop further execution
				} else {
					$db->rollBack();
					echo "Insertion failed";
				}
			} catch (PDOException $e) {
				$db->rollBack();
				echo "Database error: " . $e->getMessage();
			} catch (Exception $e) {
				$db->rollBack();
				echo "An unexpected error occurred: " . $e->getMessage();
			}
		}
	}
}


$payment_domain = "(p.serv_group='Laboratory' or p.serv_group='Radiology')";
$setdate = date("Y-m-d H:i:s");
$range_define = 0;
?>


<body class="fixed-navigation">

	<?php

	if (
		isset($_POST['go_patients']) ||
		isset($_POST['go_search']) ||
		isset($_POST['cancel_2']) ||
		isset($_GET['emr']) ||
		isset($_POST['emr']) ||
		isset($_POST['apply_search'])
	) {
		if (isset($_POST['apply_search'])) {
			$emr = trim($_POST['search2']);
			echo "<script>window.location.href = 'pacct.php?emr=" . urlencode($emr) . "&pay';</script>";
			exit;
		}

		if (isset($_POST['go_search'])) {
			$emr = trim($_POST['go_search']);
		}

		if (isset($_GET['emr'])) {
			$emr = trim($_GET['emr']);
		} elseif (isset($_POST['emr'])) {
			$emr = trim($_POST['emr']);
		}





		// Redirect for go_patients or cancel_2
		if (isset($_POST['go_patients']) || isset($_POST['cancel_2'])) {
			echo "<script>window.location.href = 'pacct.php?emr=" . urlencode($emr) . "&pay';</script>";
			exit;
		}

		// Proceed with patient account processing
		//
		$noExist = '0';
		///$Total_total_debit  = '0';
		$general_credit_limit =	$_SESSION['credit_limit_status'];
		$items = call_current_balance($db, $emr, $general_credit_limit);

		$current_balance =  $items["current_balance"];
		$current_balance_RMS =  $items["current_balance"];
		$Total_total_credits =  $items["Total_total_credits"];
		$patient_name      = $items['patient_name'];
		$nhis_no           = $items['nhis_no'];
		$names             = $items['names'];
		$surname           = $items['surname'];
		$fname             = $items['fname'];
		$oname             = $items['oname'];
		$gender            = $items['gender'];
		$address           = $items['address'];
		$referral_name     = $items['referral_name'];
		$referral_sn       = $items['referral_sn'];
		$discount_set      = $items['discount_set'];
		$phone             = $items['phone'];
		$email             = $items['email'];
		$insurance_type    = $items['insurance_type'];
		$insurance_no      = $items['insurance_no'];
		$save_insurance_no = $items['save_insurance_no'];
		$wallet_amount     = $items['current_balance'];
		$bal_credit_limit  = $items['bal_credit_limit'];
		$credit_limit  	   = $items['bal_credit_limit'];
		$add_minus         = $items['add_minus'];
		$interest          = $items['interest'];
		$patient_type      = $items['patient_type'];
		$addr              = $items['addr'];
		$insur_title       = $items['insur_title'];
		$wallet_account    = $items['wallet_account'];
		$main_credit_limit = $items['credit_limit'];
		$Total_total_debit = $items['Patient_Bill_Receivable'];
		$current_balance_actual = $items['current_balance_actual'];
		$vip               = $items['vip'];
		$label             = $items['credit_type']; // Note: original key was 'credit_type', mapped to $label

		/////======================================= RMS

		if ($_SESSION['h_code'] == 'RMS') {
			include_once("RMS.php");
		}


		/// adjustment
		/* 		$Total_total_debit     = max(0, $Total_total_debit);
		$current_balance_actual = ($current_balance > 0)
			? $current_balance
			: max(0, $current_balance_actual); */
	} else {
		header("Location: index.php");
		exit;
	}

	if (isset($_GET['clear'])) {
		include_once('../inc/clear_lab_request_error.php');
	}


	// Stop Discount
	if (isset($_GET['dsc'])) {
		$stmt = $db->prepare("
			UPDATE patient_discount 
			SET status = 'Stop' 
			WHERE individual_group_no = :emr 
			  AND discount_charge = 'Discount'
		");
		$stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
		$stmt->execute();
		$done = 'msg';
	}

	// Stop Charge
	if (isset($_GET['chr'])) {
		$stmt = $db->prepare("
			UPDATE patient_discount 
			SET status = 'Stop' 
			WHERE individual_group_no = :emr 
			  AND discount_charge = 'Charge'
		");
		$stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
		$stmt->execute();
		$done = 'msg';
	}

	// Cancel Unpaid Service Line
	if (isset($_GET['cnl'])) {
		$cnl = trim($_GET['cnl']);
		$stmt = $db->prepare("
			UPDATE patient_ap_services 
			SET invoice_status = 0, 
				med_dosage_unit = 1 
			WHERE sn = :cnl 
			  AND paystatus = 0
		");
		$stmt->bindParam(':cnl', $cnl, PDO::PARAM_STR);

		if ($stmt->execute()) {
			$done = 'msg'; // Success
		} else {
			$done = 'error'; // Failure
		}
	}

	if (isset($_GET['uncl'])) {
		$uncl = trim($_GET['uncl']);
		$stmt = $db->prepare("
			UPDATE patient_ap_services 
			SET invoice_status = 1, 
				med_dosage_unit = 0
			WHERE sn = :uncl 
			  AND paystatus = 0  AND cr = 0
		");
		$stmt->bindParam(':uncl', $uncl, PDO::PARAM_STR);

		if ($stmt->execute()) {
			$done = 'msg'; // Success
		} else {
			$done = 'error'; // Failure
		}
	}


	if (isset($_GET['cnlcr'])) {
		$cnlcr = trim($_GET['cnlcr']);
		$cnlcr = base64_decode(base64_decode(base64_decode($cnlcr)));
		// Reset invoice & credit flags for unpaid service
		$stmt = $db->prepare("
			UPDATE patient_ap_services 
			SET med_dosage_unit = 1 
			WHERE sn = :cnl 
			  AND paystatus = 0
		");
		$stmt->bindParam(':cnl', $cnlcr, PDO::PARAM_STR);

		if ($stmt->execute()) {
			// Fetch service details for logging
			$stmt = $db->prepare("
				SELECT item_services, pay, qty, hospital_no 
				FROM patient_ap_services 
				WHERE sn = :cnlcr
			");
			$stmt->bindParam(':cnlcr', $cnlcr, PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$rmks = 'EMR' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];
			} else {
				$rmks = 'Record not found for cancellation.';
			}

			// Log action
			$desc   = $rmks;
			$staff  = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Unknown Staff';
			$pid    = $cnlcr;
			$pname  = ''; // can be set if available
			$action = 'Credit Inv.Cancelled';

			include_once("../logs.php");
		}
	}


	if (isset($_POST['print_recep_X'])) {
		$emr = trim($_POST['emr']);

		// Delete old invoice print selections for this EMR
		$stmt_del = $db->prepare("DELETE FROM invsti_invoice WHERE emr = :emr");
		$stmt_del->bindParam(':emr', $emr, PDO::PARAM_STR);
		$stmt_del->execute();

		// Insert selected invoice items for reprinting
		if (!empty($_POST['inv_x']) && is_array($_POST['inv_x'])) {
			$insertSQL = "INSERT INTO invsti_invoice (sale_no, emr) VALUES (:sale_no, :emr)";
			$stmt_insert = $db->prepare($insertSQL);

			foreach ($_POST['inv_x'] as $sale_no) {
				$sale_no = trim($sale_no);
				if ($sale_no !== '') {
					$stmt_insert->bindParam(':sale_no', $sale_no, PDO::PARAM_STR);
					$stmt_insert->bindParam(':emr', $emr, PDO::PARAM_STR);
					$stmt_insert->execute();
				}
			}
		}

		// Redirect to print page
		header("Location: ../printout.php?emr=" . urlencode($emr));
		exit;
	}


	if (isset($_POST['xcancel'])) {
		$search_again = $_POST['search_again_cancel'];
		$emr = $_POST['emr'];
		$total_credit = $_POST['total_credit'];
		$insurance_no = $_POST['insurance_no'];
	}

	if (isset($_POST['rof_all'])) {

		if (!empty($_REQUEST['inv'])) {
			$item_list = $_REQUEST['inv'];

			for ($i = 0; $i < count($item_list); $i++) {
				$values = $item_list[$i];
				$break = explode("__", $values);
				$sn = $break[0];

				$stmt = $db->query("SELECT item_services,pay,qty,hospital_no FROM patient_ap_services WHERE sn='$sn'");
				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$rmks = 'Hosp/No.' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];
				}

				$staff = $_SESSION['fullname'];
				$pid = $row['hospital_no'];
				$item_sn = $sn;
				$pname = '';
				$desc = $rmks;
				$action = 'WriteOff';
				include("../logs.php");


				$inv = "UPDATE patient_ap_services SET pay_mode='cash',paystatus='0',transact_date='0000-00-00' WHERE sn='$sn'";
				$db->exec($inv);
			}
			header("location:pacct.php?emr=$emr&rof");
		} else {
		}
	}

	if (isset($_POST['process_riteoff'])) {

		$paystatus = '1'; // Assuming '1' is the value for paystatus
		$pay_mode = 'writeoff'; // Assuming 'writeoff' is the value for pay_mode
		$emr = $_POST['emr'];

		if (!empty($_POST['inv'])) {
			$item_list = $_POST['inv'];

			foreach ($item_list as $values) {
				$break = explode("__", $values);
				$sn = $break[0];

				$updateSQL = "UPDATE patient_ap_services SET paystatus = :paystatus, transact_date = :transact_date, pay_mode = :pay_mode WHERE hospital_no = :hospital_no AND sn = :sn AND paystatus = '0'";
				$stmt = $db->prepare($updateSQL);
				$stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
				$stmt->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
				$stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
				$stmt->bindParam(':hospital_no', $_POST['emr'], PDO::PARAM_STR);
				$stmt->bindParam(':sn', $sn, PDO::PARAM_STR);



				$stmt->execute();
			}

			header("location: pacct.php?emr=$emr&sv");
		} else {
			header("location: pacct.php?emr=$emr&rof&sel");
			// Redirect to error page for selection error
		}
	}

	if (isset($_POST['paynow'])) {

		if (empty($_REQUEST['inv'])) {
			$error = "Check list of the items you wish to pay before you continue ...";
			$search_again = $_POST['dates'];
			$total_credit = $_POST['total_credit'];
			$amt_paying = $_POST['totalcost'];
			$insurance_no = $_POST['save_insurance_no'];
		} else {
			// confirmation page
			$confirm_set = 1;
			$emr = $_POST['emr'];
			$search_again_cancel = $_POST['dates'];
			$total_credit = $_POST['total_credit'];
			$amt_paying = $_POST['totalcost'];
			$insurance_no = $_POST['save_insurance_no'];
		}
	}


	if (isset($_POST["savebill"])) {


		$hos_no    = isset($_POST['hos_no']) ? $_POST['hos_no'] : null;
		$insurance = isset($_POST['insurance_type']) ? $_POST['insurance_type'] : null;
		$interest  = isset($_POST['interest']) ? (float)$_POST['interest'] : 0;
		$amount    = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

		$percent          = $interest / 100;
		$ccop_int_charge  = $amount * $percent;

		$stmt = $db->prepare("
			SELECT appt_no 
			FROM apptm 
			WHERE hospital_no = :hos_no 
			AND ap_direction = '1' 
			ORDER BY sn DESC 
			LIMIT 1");
		$stmt->execute([':hos_no' => $hos_no]);
		$rwx = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($rwx && isset($rwx['appt_no'])) {
			$appt_no = $rwx['appt_no'];
		} else {
			$appt_no = $hos_no;
		}

		if (isset($_POST['paymode']) && $_POST['paymode'] === 'c') {
			$amt_paying     = $amount;
			$claim_amt      = 0;
			$pay_mode       = 'cash';
			$service_access = 3;
		} else {
			if (strtoupper($insurance) === 'NHIS') {
				$claim_amt      = $amount;
				$amt_paying     = 0;
				$service_access = 1;
			} else {
				$claim_amt      = $amount + $ccop_int_charge;
				$amt_paying     = 0;
				$service_access = 3;
			}
			$pay_mode = 'claim';
		}

		include_once("../inc/utilities.php");
		$zero = 0;
		$dash = '-';
		$one = '1';
		$empty = ' ';
		$invoice_no = INV(); // Assuming INV() function generates the invoice number
		$insertSQL = "INSERT INTO patient_ap_services 
							(app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, remarks, drug_status, invoice_status, invoice_no, prepared_by, date_entry, transact_date, pay, pay_mode, paystatus, process_claim)
							VALUES 
							(:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, :interest, :qty, :remarks, :drug_status, :invoice_status, :invoice_no, :prepared_by, :date_entry, :transact_date, :pay, :pay_mode, :paystatus, :process_claim)";

		try {
			$stmt = $db->prepare($insertSQL);

			// Bind parameters
			$stmt->bindParam(':app_no', $appt_no, PDO::PARAM_STR);
			$stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
			$stmt->bindParam(':access', $service_access, PDO::PARAM_STR);
			$stmt->bindParam(':serv_group', 'Misc. Service', PDO::PARAM_STR);
			$stmt->bindParam(':cat_type', 'Other Services', PDO::PARAM_STR);
			$stmt->bindParam(':dept_id', $dash, PDO::PARAM_STR); // Adjust if necessary
			$stmt->bindParam(':drug_sn', $empty, PDO::PARAM_STR); // Adjust if necessary
			$stmt->bindParam(':item_services', $_POST['desc'], PDO::PARAM_STR);
			$stmt->bindParam(':hosp_price', $_POST['amount'], PDO::PARAM_STR);
			$stmt->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
			$stmt->bindParam(':interest', $ccop_int_charge, PDO::PARAM_STR);
			$stmt->bindParam(':qty', '1', PDO::PARAM_INT); // Assuming qty is integer, adjust if necessary
			$stmt->bindParam(':remarks', 'Misc. Service', PDO::PARAM_STR);
			$stmt->bindParam(':drug_status', '0', PDO::PARAM_INT); // Assuming drug_status is integer, adjust if necessary
			$stmt->bindParam(':invoice_status', '0', PDO::PARAM_INT); // Assuming invoice_status is integer, adjust if necessary
			$stmt->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
			$stmt->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
			$stmt->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
			$stmt->bindParam(':transact_date', NULL, PDO::PARAM_STR); // Adjust if necessary
			$stmt->bindParam(':pay', $amt_paying, PDO::PARAM_STR);
			$stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
			$stmt->bindParam(':paystatus', '0', PDO::PARAM_INT); // Assuming paystatus is integer, adjust if necessary
			$stmt->bindParam(':process_claim', '0', PDO::PARAM_INT); // Assuming process_claim is integer, adjust if necessary
			$stmt->execute();

			// Redirect to another page after successful insertion
			header("location: pacct.php?emr=$hos_no&inv");
			exit; // Ensure script stops execution after redirection
		} catch (PDOException $e) {
			// Handle PDO exception (e.g., log or display error message)
			echo "Error: " . $e->getMessage();
		}
	}


	if (isset($_POST['process_claim'])) {
		//header("location:pacct3.php?emr=$emr&$stt&seloooo");
		///exit;

		if (!empty($_REQUEST['inv'])) {
			$SystemSelected = $_REQUEST['inv'];
			$updateSuccessful = false; // Flag to track if any update was successful

			for ($i = 0; $i < count($SystemSelected); $i++) {
				$inv_id = $SystemSelected[$i];
				$break = explode("__", $inv_id);
				$sn = $break[0];

				$invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
				$paystatus = 1; // Assuming paystatus is integer, adjust if necessary
				$cr = 0; // Assuming cr is integer, adjust if necessary
				$invoice_status = 1; // Assuming invoice_status is integer, adjust if necessary
				$updateSQL = "UPDATE patient_ap_services 
						SET paystatus = :paystatus,
							transact_date = :transact_date,
							cr = :cr,
							invoice_status = :invoice_status,
							invoice_date = :invoice_date,
							invoice_by = :invoice_by,
							invoice_no = :invoice_no
						WHERE hospital_no = :hospital_no
						  AND sn = :sn";
				$stmt = $db->prepare($updateSQL);

				// Bind parameters
				$stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_INT);
				$stmt->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
				$stmt->bindParam(':cr', $cr, PDO::PARAM_INT); // Adjust type if necessary
				$stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_INT);
				$stmt->bindParam(':invoice_date', $setdate, PDO::PARAM_STR);
				$stmt->bindParam(':invoice_by', $_SESSION['fullname'], PDO::PARAM_STR);
				$stmt->bindParam(':invoice_no', $invoice_no, PDO::PARAM_INT);
				$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
				$stmt->bindParam(':sn', $sn, PDO::PARAM_STR);
				$stmt->execute();

				// Check if the update was successful
				if ($stmt->rowCount() > 0) {
					$updateSuccessful = true; // Set flag to true if an update was successful

					// Proceed with logging
					$stmt = $db->query("SELECT item_services,pay,qty,hospital_no FROM patient_ap_services WHERE sn='$sn'");
					if ($stmt->rowCount() > 0) {
						$row = $stmt->fetch(PDO::FETCH_ASSOC);
						$rmks = 'Hosp/No.' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];

						$staff = $_SESSION['fullname'];
						$pid = $row['hospital_no'];
						$item_sn = $sn;
						$pname = '';
						$desc = $rmks;
						$action = 'Process Claim';
						include("../logs.php");
					}
				}
			}

			// After the loop, check if any updates were successful
			if ($updateSuccessful) {
				$error_status = 2;
				$error_msg = 'Updated Successfully.';
			} else {
				$error_status = 1;
				$error_msg = 'No records were updated';
			}
		} else {
			header("location:pacct.php?emr=$emr&inv&sel");
		}
	}


	if (isset($_POST['convert_pay'])) {
		// header("location:pacct3.php?emr=$emr&$stt&sel");
		if (!empty($_REQUEST['inv'])) {
			$SystemSelected = $_REQUEST['inv'];
			$updateSuccessful = false; // Flag to track if any update was successful

			for ($i = 0; $i < count($SystemSelected); $i++) {
				$inv_id = $SystemSelected[$i];
				$break = explode("__", $inv_id);
				$sn = $break[0];

				include_once("../inc/utilities.php");
				$invoice_no = INV();

				$stmt = $db->query("SELECT hosp_price, qty FROM patient_ap_services WHERE sn='$sn'");
				if ($stmt->rowCount() > 0) {
					$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
					$hosp_price = $rwx['hosp_price'];
					$qty = $rwx['qty'];
					$pay = $hosp_price * $qty;
					$claim_amt = '0'; // Assuming claim_amt is string, adjust if necessary
					$pay_mode = 'cash'; // Assuming pay_mode is string, adjust if necessary
					$invoice_status = 1; // Assuming invoice_status is integer, adjust if necessary

					$updateSQL = "UPDATE patient_ap_services 
						SET claim_amt = :claim_amt,
							pay = :pay,
							pay_mode = :pay_mode,
							invoice_status = :invoice_status,
							invoice_no = :invoice_no
						WHERE hospital_no = :hospital_no
						  AND sn = :sn";

					$stmt = $db->prepare($updateSQL);

					// Bind parameters
					$stmt->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR); // Adjust type if necessary
					$stmt->bindParam(':pay', $pay, PDO::PARAM_STR); // Adjust type if necessary
					$stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR); // Adjust type if necessary
					$stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_INT); // Adjust type if necessary
					$stmt->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR); // Adjust type if necessary
					$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR); // Adjust type if necessary
					$stmt->bindParam(':sn', $sn, PDO::PARAM_STR); // Adjust type if necessary
					$stmt->execute();

					// Check if the update was successful
					if ($stmt->rowCount() > 0) {
						$updateSuccessful = true; // Set flag to true if an update was successful

						// Proceed with logging
						$stmt = $db->query("SELECT item_services, pay, qty, hospital_no FROM patient_ap_services WHERE sn='$sn'");
						if ($stmt->rowCount() > 0) {
							$row = $stmt->fetch(PDO::FETCH_ASSOC);
							$rmks = 'Hosp/No.' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];

							$staff = $_SESSION['fullname'];
							$pid = $row['hospital_no'];
							$item_sn = $sn;
							$pname = '';
							$desc = $rmks;
							$action = 'Convert_claim_pay';
							include("../logs.php");
						}
					}
				}
			}

			if ($updateSuccessful) {
				$error_status = 2;
				$error_msg = 'Converted & Updated Successfully.';
			} else {
				$error_status = 1;
				$error_msg = 'No records were updated';
			}
		} else {
			header("location:pacct.php?emr=$emr&inv&sel");
		}
	}

	if (isset($_POST['edit_update_price'])) {

		$drug_sn = $_POST['drug_sn'];
		$cat_type = $_POST['cat_type'];
		$serv_group = $_POST['serv_group'];

		if ($_POST['pay'] > 0 or $_POST['claim'] > 0) {

			// Step 1: Get old price details BEFORE updating
			$getOldSQL = "SELECT pay, claim_amt FROM patient_ap_services WHERE hospital_no = :hospital_no AND sn = :sn";
			$getStmt = $db->prepare($getOldSQL);
			$getStmt->bindParam(':hospital_no', $_POST['emr'], PDO::PARAM_STR);
			$getStmt->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);
			$getStmt->execute();
			$oldData = $getStmt->fetch(PDO::FETCH_ASSOC);

			// Store old values
			$oldPay = $oldData['pay'];
			$oldClaim = $oldData['claim_amt'];

			// Step 2: Update the record
			$updateSQL = "UPDATE patient_ap_services 
                  SET item_services = :item_services,
                      pay = :pay,
                      claim_amt = :claim_amt,
                      qty = :qty,
                      pay_mode = :pay_mode
                  WHERE hospital_no = :hospital_no
                  AND sn = :sn";

			$stmt = $db->prepare($updateSQL);
			$stmt->bindParam(':item_services', $_POST['item_services'], PDO::PARAM_STR);
			$stmt->bindParam(':pay', $_POST['pay'], PDO::PARAM_STR);
			$stmt->bindParam(':claim_amt', $_POST['claim'], PDO::PARAM_STR);
			$stmt->bindParam(':qty', $_POST['qty'], PDO::PARAM_INT);
			$stmt->bindParam(':pay_mode', $_POST['mode'], PDO::PARAM_STR);
			$stmt->bindParam(':hospital_no', $_POST['emr'], PDO::PARAM_STR);
			$stmt->bindParam(':sn', $_POST['sn'], PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$error_status = 2;
				$error_msg = 'Updated Successfully.';

				// Step 3: Log the change
				$sn = $_POST['sn'];
				$hospital_no = $_POST['emr'];

				// Create description for log
				$desc = "Price changed from Pay: {$oldPay} to {$_POST['pay']}, 
                 Claim: {$oldClaim} to {$_POST['claim']}";

				$action_to_take = "Price Update";
				$setdatetime = date('Y-m-d H:i:s');

				$sql = $db->prepare("INSERT INTO patient_staff_logs 
                (item_sn, descriptions, staff_name, patient_id, action, date_and_time) 
                VALUES (:item_sn, :descriptions, :staff_name, :patient_id, :action, :date_and_time)");
				$sql->bindParam(':item_sn', $sn, PDO::PARAM_STR);
				$sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
				$sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
				$sql->bindParam(':patient_id', $hospital_no, PDO::PARAM_STR);
				$sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
				$sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
				$sql->execute();
			} else {
				$error_status = 1;
				$error_msg = 'No records were updated';
			}
		}
	}

	if (isset($_POST['un_cancel_invoice_only'])) {

		$SystemSelected = isset($_REQUEST['inv']) ? $_REQUEST['inv'] : array();
		if (!is_array($SystemSelected)) $SystemSelected = array($SystemSelected);

		if (count($SystemSelected) > 0) {
			$setdatetime = date('Y-m-d H:i:s');
			$snList = array();

			foreach ($SystemSelected as $inv_id) {
				$break = explode("__", $inv_id);
				if (!empty($break[0])) {
					$snList[] = (int)trim($break[0]); // cast to integer
				}
			}

			if (count($snList) > 0) {
				$snIn = implode(",", $snList);

				$cancelSQL = "
                UPDATE patient_ap_services
                SET med_dosage_unit = 0
                WHERE sn IN ($snIn)
                  AND paystatus = 0";

				$affected = $db->exec($cancelSQL);

				if ($affected > 0) {
					echo "<div class='alert alert-success'>$affected invoice(s) un-cancelled successfully.</div>";
				} else {
					echo "<div class='alert alert-warning'>No invoices were updated.</div>";
				}
			}
		}
	}

	if (isset($_POST['cancel_invoice_only'])) {

		$SystemSelected = isset($_REQUEST['inv']) ? $_REQUEST['inv'] : array();
		if (!is_array($SystemSelected)) $SystemSelected = array($SystemSelected);

		if (count($SystemSelected) > 0) {

			$setdatetime = date('Y-m-d H:i:s');
			$snList = array();

			// Extract all sn values safely
			foreach ($SystemSelected as $inv_id) {
				$break = explode("__", $inv_id);
				if (!empty($break[0])) {
					$snList[] = $db->quote(trim($break[0]));
				}
			}

			if (count($snList) > 0) {

				$snIn = implode(",", $snList);

				// --- Bulk cancel all invoices at once ---
				$cancelSQL = "
                UPDATE patient_ap_services
                SET                  
                    med_dosage_unit = 1
                WHERE sn IN ($snIn)
                  AND paystatus = 0";
				$db->exec($cancelSQL);

				// --- Fetch all details for log ---
				$fetchSQL = "
                SELECT sn, item_services, pay, qty, hospital_no, cr
                FROM patient_ap_services
                WHERE sn IN ($snIn)";
				$rows = $db->query($fetchSQL)->fetchAll(PDO::FETCH_ASSOC);

				$combinedRemarks = array();

				foreach ($rows as $row) {
					// Only log those with cr == 1 and item_services != 1
					if ($row['cr'] == 1 && $row['item_services'] != 1) {
						$rmk = 'EMR' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];
						$combinedRemarks[] = $rmk;
					}
				}

				// --- Insert one combined log entry ---
				if (count($combinedRemarks) > 0) {
					$desc   = implode(" | ", $combinedRemarks);
					$staff  = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Unknown Staff';
					$pid    = 'Batch';
					$pname  = '';
					$action = 'Credit Inv.Cancelled (Batch)';
					$item_sn = 'Batch-' . time();

					// Prevent duplicate log entry
					$chk = $db->prepare("SELECT COUNT(*) FROM patient_staff_logs WHERE descriptions = :desc AND date_and_time = :dt");
					$chk->bindParam(':desc', $desc, PDO::PARAM_STR);
					$chk->bindParam(':dt', $setdatetime, PDO::PARAM_STR);
					$chk->execute();

					if ($chk->fetchColumn() == 0) {
						$sql = $db->prepare("
                        INSERT INTO patient_staff_logs 
                        (item_sn, descriptions, staff_name, patient_id, patient_name, action, date_and_time)
                        VALUES (:item_sn, :descriptions, :staff_name, :patient_id, :patient_name, :action, :date_and_time)
                    ");
						$sql->bindParam(':item_sn', $item_sn, PDO::PARAM_STR);
						$sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
						$sql->bindParam(':staff_name', $staff, PDO::PARAM_STR);
						$sql->bindParam(':patient_id', $pid, PDO::PARAM_STR);
						$sql->bindParam(':patient_name', $pname, PDO::PARAM_STR);
						$sql->bindParam(':action', $action, PDO::PARAM_STR);
						$sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
						$sql->execute();
					}
				}
			}
		}

		header("location:pacct.php?emr=$emr&pay");
	}
	?>


	<div id="wrapper">
		<?php include("../inc/nav_side_bill.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">
			<?php include("../inc/nav_header.php"); ?>

			<?php include("../inc/billing_side_bar.php"); ?>

			<div class="wrapper wrapper-content">

				<?php if (!isset($_POST['process_inv']) and $final_pay_err != 1) { ?>
					<div class="row">
						<div class="col-md-6">
							<div class="ibox float-e-margins">
								<div class="ibox-title">
									<span class="label label-primary pull-left">Insurance's Status:</span> &nbsp;
									<strong><?php if ($noExist == '0') {
												echo " "; ?><?php echo $insur_title . ' ';
														} ?></strong>
								</div>
								<div class="ibox-content">

									<div class="row">
										<div class="col-md-12">
											<table>
												<tr>
													<td>
														<h1 class="no-margins"><?php echo $emr . ' / '; ?></h1>
													</td>
													<td>
														<h2 class="no-margins"><?php echo $patient_name ?></h2>
													</td>
												</tr>
											</table>

											<small>Hospital Number / Patient Name</small>
										</div>
									</div>


								</div>
							</div>
						</div>

						<div class="col-md-3">
							<div class="ibox float-e-margins">
								<div class="ibox-title">
									<span class="label label-success pull-right">As at Today</span>
									<h5><?php
										echo $bal2;

										if ($noExist == '0') {
											echo 'Account Balance';
										} else {
											echo 'Visits';
										} ?></h5>
								</div>
								<div class="ibox-content">
									<h1 class="no-margins"><?php if ($noExist == '0') {
																if ($current_balance == 0) {
																	echo 'Zero Naira';
																} else {
																	echo number_format($current_balance, 2);
																}
															} else {
																echo 'dash';
															} ?></h1>
									<div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
									<small><?php if ($noExist == '0') {
												$balanceColor = '';
												if ($current_balance_actual > 0) {
													$balanceColor = 'color:blue;';
												} elseif ($current_balance_actual < 0) {
													$balanceColor = 'color:red;';
												}
												echo '<b><span style="' . $balanceColor . '">Actual Balance:'
													. number_format($current_balance_actual, 2) .
													'</span></b>';
											} else {
												echo 'dash';
											} ?></small>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="ibox float-e-margins">
								<div class="ibox-title">
									<span class="label label-info pull-right">Debt Profile</span>
									<h5>DEBT</h5>
								</div>
								<div class="ibox-content">
									<h1 class="no-margins"><?= number_format($Total_total_debit); ?></h1>
									<small style="color: red; ">Credit Limit Services: <?= number_format($Total_total_credits); ?></small>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>

				<div class="row">

					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<?php

							if (isset($_POST['print_invoice_only'])) {

								$emr = $_POST['emr'];
								$patient_name = $_POST['patient_name2'];

								$update = "DELETE FROM invoice_temp2 WHERE hosp='$emr'";
								$db->exec($update);



								$SystemSelected = $_REQUEST['inv'];
								for ($i = 0; $i < count($SystemSelected); $i++) {

									$inv_id = $SystemSelected[$i];
									$break = explode("__", $inv_id);
									$sn = $break[0];
									$dsc = $break[5];

									$insertSQL = "INSERT INTO invoice_temp2 (hosp, sale_no, amt) VALUES (:hosp, :sale_no, :amt)";
									$stmt = $db->prepare($insertSQL);

									// Bind parameters
									$stmt->bindParam(':hosp', $emr, PDO::PARAM_STR); // Adjust type if necessary
									$stmt->bindParam(':sale_no', $sn, PDO::PARAM_STR); // Adjust type if necessary
									$stmt->bindParam(':amt', $dsc, PDO::PARAM_STR); // Adjust type if necessary

									// Execute the statement
									$stmt->execute();
								}
								header("location:pacct.php?emr=$emr&invoice_stt&name=$patient_name");
							} elseif (isset($_POST['daily_inv']) and $_POST['daily_inv'] == 'daily_inv') {
								$emr = $_POST['emr'];
								$patient_name = $_POST['patient_name'];

								$update = "DELETE FROM invoice_temp2 WHERE hosp='$emr'";
								$db->exec($update);



								$SystemSelected = $_REQUEST['inv'];
								for ($i = 0; $i < count($SystemSelected); $i++) {

									$inv_id = $SystemSelected[$i];
									$break = explode("__", $inv_id);
									$sn = $break[0];

									$insertSQL = "INSERT INTO invoice_temp2 (hosp, sale_no) VALUES (:hosp, :sale_no)";

									try {
										$stmt = $db->prepare($insertSQL);
										$stmt->bindParam(':hosp', $emr, PDO::PARAM_STR);
										$stmt->bindParam(':sale_no', $sn, PDO::PARAM_STR);
										$stmt->execute();
									} catch (PDOException $e) {
										echo "Error: " . $e->getMessage();
									}
								}
								/// 	include("../inc/invoice.php"); 
								header("location:../inc/printout2.php?daily=$emr&name=$patient_name");
							} elseif (isset($_POST['process_inv']) or $final_pay_err == 1) {

								if (!empty($_REQUEST['SystemSelected']) and $final_pay_err == 1) {
									$SystemSelected = $_REQUEST['SystemSelected'];
								} elseif (!empty($_REQUEST['inv']) and isset($_POST['process_inv'])) {
									$SystemSelected = $_REQUEST['inv'];
								} else {
									$stt = $_POST['i_come_from'];
									header("location:pacct.php?emr=$emr&$stt&sel");
								} ?>

								<div class="ibox-title" align="center">
									<strong style="color:#F00; font-size:21px">Payment Confirmation? </strong>
								</div>
								<div class="ibox-content">
									<form action="pacct.php" method="post">

										<div class="row">
											<div class="col-md-8">
												<div class="ibox float-e-margins">

													<div class="pull-right">

														<!--    <input type="submit" name="xcancel" id="xcancel" value="Cancel" class="btn btn-warning btn-sm" />
-->
													</div>


													<?php

													///$debt_post=0;
													if (isset($_POST['debt_post']) and $_POST['debt_post'] != '') {
														$debt_post = 1;
													}
													$discount_set = $_POST['discount_set'];
													$discount_insurance_no = $_POST['discount_insurance_no'];
													$dsc_chr_type = $_POST['dsc_chr_type'];

													if (isset($_POST['discount_vourcher']) and $_POST['discount_vourcher'] != '') {

														$auth_code = $_POST['discount_vourcher'];
														$set_date = date("Y-m-d");
														$query = "SELECT vi.voucher_code, v.amount, v.created_by, v.flat_cent, v.batch_type, v.v_center FROM vouchers_inventory as vi INNER JOIN vouchers as v ON vi.batch_code = v.batch_code WHERE vi.voucher_code = :auth_code AND vi.used_status = 0 AND v.date_expire >= :set_date AND (v.batch_type = 'Discount' OR v.batch_type = 'writeoff')";
														$stmt = $db->prepare($query);
														$stmt->bindValue(':auth_code', $auth_code, PDO::PARAM_STR);
														$stmt->bindValue(':set_date', $set_date, PDO::PARAM_STR);
														$stmt->execute();

														// Check if any voucher is available
														if ($stmt->rowCount() == 0) {
															echo '<strong style="color:#F00; font-size:25px;">No Voucher Available!</strong>';
														} else {
															$flat_cent = 'Flat';
															$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
															$voucher_type = $rowx['batch_type'];
															$voucher_center = $rowx['v_center'];
															$created_by = $rowx['created_by'];
															$v_discount = $rowx['amount'];
													?>
															<table class="table table-bordered" style="font-size:14px;">
																<tr>
																	<td>Discount Voucher:</td>
																	<td><strong><?php echo $flat_cent; ?></strong> / <?php echo $v_discount; ?></td>
																	<td><strong>Auth. By</strong> / <?php echo $created_by; ?></td>
																</tr>
															</table>
														<?php
														}
														?>

													<?php } else {
														$v_discount = 0;
													} ?>

													<table class="table table-bordered" style="font-size:14px;">
														<tr>
															<td></td>
															<td><strong>Item/Service</strong></td>
															<td><strong>Qty</strong></td>
															<td><strong>Amount</strong></td>
															<td><strong>DSC/CHR</strong></td>
															<td><strong>.</strong></td>
														</tr>
														<?php
														///$SystemSelected = $_REQUEST['inv'];

														$amt_sysSelected = 0;
														$Total_dsc = 0;
														$totalcr = 0;
														$credit_bal = $total_credit;
														$insufficient = 0;
														$snn = 0;

														for ($i = 0; $i < count($SystemSelected); $i++) {

															$inv_id = $SystemSelected[$i];
															$break = explode("__", $inv_id);
															$sn = $break[0];
															$pay = $break[1];
															$item = $break[2];
															$qty = $break[3];
															$cr = $break[4];
															$discount = $break[5];
															$charge = $break[6];
															$total_chr = $break[7];
															$total_dsc = $break[8];
															$dura = $break[9];
															$post_type = $break[10];
															$count_bal = $break[11];
															$dsc_chr_set = $break[12];
															$grp_idv_no = $break[13];
															$cat_type_ = $break[14];
															$serv_group_ = $break[15];
															$sn_service = $break[17];
															$service_type = $break[18];

															if ($cat_type_ == '' and $serv_group_ == 'Laboratory') {
																$cat_type_ = 'Laboratory Test';
															} elseif ($cat_type_ == '' and $serv_group_ == 'Radiology') {
																$cat_type_ = 'SCAN/IMAGING';
															}

															$drug_sn = $break[16];
															if ($cr == 1) {
																$totalcr = $totalcr + $pay;
															}

															if ($serv_group_ == 'Laboratory' or $serv_group_ == 'Radiology') {
																$labrequest_no = $drug_sn;
																$stmt2 = $db->query("SELECT sn FROM lab_manage WHERE labrequest_no='$labrequest_no'");
																if ($stmt2->rowCount() == 0) {
																	$skip = 'yes';
																} else {
																	$skip = 'no';
																}
															} else {
																$skip = 'no';
															}

															include_once("../inc/utilities.php");
															$invoice_no = INV();
															$inv = "UPDATE patient_ap_services SET invoice_status='1',invoice_no='$invoice_no', invoice_date='$setdate',invoice_by='$fullname',med_dosage_unit='' WHERE sn='$sn' and invoice_status='0'";
															$db->exec($inv);

															if ($skip == 'no') {

																$stmt = $db->query("SELECT sn,dept_id FROM patient_ap_services where sn='$sn' and paystatus='0'");
																if ($stmt->rowCount() > 0) {
																	$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
																	$dept_id_id = $rowx['dept_id'];

																	if (strtoupper($serv_group_) == 'PHARMACY') {
																		$departmentName = 'Pharmacy';
																	} elseif (array_key_exists($dept_id_id, $departments)) {
																		$departmentName = $departments[$dept_id_id];
																		///echo "Department ID '$dept_id_id' corresponds to the name: '$departmentName'";
																	} else {
																		$departmentName = null;
																	}

																	if ($voucher_type == 'writeoff' and $dept_id_id == $voucher_center) {

																		$amt_sysSelected = $amt_sysSelected + $pay;
																		$snn = $snn + 1;							?>
																		<tr>
																			<td>
																				<input type="checkbox" value="<?php echo $sn . '__' . $pay . '__' . $item . '__' . $qty . '__' . $discount . '__' . $charge
																													. '__' . $dura . '__' . $post_type . '__' . $count_bal . '__' . $dsc_chr_set . '__' . $cat_type_ . '__' . $cr . '__' . $serv_group_ . '__' . $sn_service . '__' . $service_type . '__' . $departmentName; ?>" name="item[]" checked style="display: none;" />
																				<?php echo $snn; ?>
																			</td>
																			<td><?php echo $item; ?></td>
																			<td><?php echo $qty; ?></td>
																			<td><?php echo number_format($pay, 2); ?></td>
																			<td><?php if ($charge > 0) {
																					echo 'CHR: ' . number_format((float)$charge, 2);
																				}
																				if ($discount > 0) {
																					$Total_dsc += (float)$discount;
																					echo 'DSC: ' . number_format((float)$discount, 2);
																				}
																				?></td>
																			<td><?php if ($cr == 1) {
																					echo 'Credit';
																				} ?></td>
																		</tr>
																	<?php }

																	if ($voucher_type != 'writeoff') {

																		$amt_sysSelected = $amt_sysSelected + $pay;
																		$snn = $snn + 1;
																	?>
																		<tr>
																			<td>
																				<input type="checkbox" value="<?php echo $sn . '__' . $pay . '__' . $item . '__' . $qty . '__' . $discount . '__' . $charge
																													. '__' . $dura . '__' . $post_type . '__' . $count_bal . '__' . $dsc_chr_set . '__' . $cat_type_ . '__' . $cr . '__' . $serv_group_ . '__' . $departmentName . '__' . $service_type . '__' . $sn_service; ?>" name="item[]" checked style="display: none;" />
																				<?php echo $snn; ?>
																			</td>
																			<td><?php echo $item; ?></td>
																			<td><?php echo $qty; ?></td>
																			<td><?php echo number_format($pay, 2); ?></td>
																			<td>
																				<?php
																				if ($charge > 0) {
																					echo 'CHR: ' . number_format((float)$charge, 2);
																				}
																				if ($discount > 0) {
																					$Total_dsc += (float)$discount;
																					echo 'DSC: ' . number_format((float)$discount, 2);
																				}
																				?>
																			</td>
																			<td><?php if ($cr == 1) {
																					echo 'Credit';
																				} ?></td>
																		</tr>

																	<?php } ?>

														<?php
																}
															}
														}  ?>
														</tr>
													</table>
													<?php


													if ($flat_cent == 'Flat') {
														$current_balance = $outstanding_balance + $v_discount;
													} else {
														$percent = $v_discount / 100;
														$v_discount = $amt_sysSelected * $percent;
														$current_balance = $outstanding_balance + $v_discount;
													}

													if ($amt_sysSelected > $current_balance) {
														$cash = $amt_sysSelected - $current_balance;
														$new_current_bal = 0;
													} else {
														$new_current_bal = $current_balance - $amt_sysSelected;
														$cash = 0;
													}


													///echo '===============' . $cash . '======' . $Total_dsc;

													if (isset($_POST['paymethod'])) {
														$paymethod = $_POST['paymethod'];
													} ?>

													<table width="100%">
														<tr>
															<td width="40%" style="padding-right:10px;">
																<label class="req">Payment Method</label>

																<select name="paymethod" id="paymethod" class="form-control" style=" font-size: 16px; " required>

																	<?php ///if (($cash == 0 && $Total_dsc > 0) or ($cash == $Total_dsc && $Total_dsc > 0)) { 
																	?>
																	<?php if ($cash == 0 && $Total_dsc > 0) { ?>
																		<option value="post_discount" <?php if ($paymethod == 'post_discount') { ?>selected<?php } ?>>Post Discount</option>
																	<?php } else { ?>

																		<?php if ($voucher_type == 'writeoff') { ?>
																			<option value="writeoff" <?php if ($voucher_type == 'writeoff') { ?>selected<?php } ?>>Write Off</option>
																		<?php } else { ?>
																			<option value=''>Select...</option>
																			<?php if ($v_discount  == 0) { ?>
																				<option value="cash" <?php if ($paymethod == 'cash' || empty($paymethod)) { ?>selected<?php } ?>>Cash Payment</option>
																				<option value="POS" <?php if ($paymethod == 'POS') { ?>selected<?php } ?>>POS Payment</option>
																				<option value="Transfer" <?php if ($paymethod == 'Transfer') { ?>selected<?php } ?>>Transfer Payment</option>
																			<?php } ?>
																			<?php

																			if ($wallet_amount > 0 or $v_discount  > 0) {
																				if ($wallet_amount > 0 && $v_discount  == 0) {
																					$t_title_ = "Pay from Patient's Deposit";
																				} elseif ($wallet_amount == 0 && $v_discount > 0) {
																					$t_title_ = "Add Voucher to Wallet & Clear";
																				}

																			?>
																				<option value="Wallet" <?php if ($paymethod == 'Wallet') { ?>selected<?php } ?>><?= $t_title_; ?></option>
																			<?php } ?>

																			<?php if ($_SESSION['transfer'] == 1 and $debt_post == '' and  $v_discount  == 0) { ?>
																				<option value="pay_from_patient_wallet" <?php if ($paymethod == 'pay_from_patient_wallet') { ?>selected<?php } ?>>Pay from Another Patient's Wallet</option>
																			<?php } ?>
																			<?php if ($total_credit > 0) { ?>
																				<option value="Reconcile" <?php if ($paymethod == 'Reconcile') { ?>selected<?php } ?>>Reconcile Outstanding</option>
																			<?php } ?>
																			<?php if ($v_discount  == 0) { ?>
																				<option value="CASHPOS" <?php if ($paymethod == 'CASHPOS') { ?>selected<?php } ?>>Cash & POS Payment Split</option>
																				<option value="CASHTransfer" <?php if ($paymethod == 'CASHTransfer') { ?>selected<?php } ?>>Cash & Transfer Payment Split</option>
																			<?php } ?>
																			<?php if ($debt_post == '' and  $v_discount  == 0) { ?>
																				<option value="PostCredit" <?php if ($paymethod == 'PostCredit') { ?>selected<?php } ?>>Post As Account Recievable(AR)</option>
																				<option value="Bill_to" <?php if ($paymethod == 'Bill_to') { ?>selected<?php } ?>>Bill to Account</option>
																			<?php } ?>

																		<?php } ?>


																	<?php } ?>

																</select>
															</td>
															<td>
																<div class="form_sep">
																	<label for="reg_input_no" class="">Account or Ref/No.</label>
																	<input type="text" id="ref_no" name="ref_no" class="form-control" maxlength="50" placeholder="Enter Ref/No">
																</div>
															</td>
															<td style="padding-left:10px;">
																<div class="form_sep">
																	<label for="bank_name" class="<?= ($final_pay_err == 1) ? 'text-danger font-weight-bold' : '' ?>">
																		Receiving Bank Name
																	</label>
																	<select name="bank_name" id="bank_name" class="form-control" style="font-size: 16px;">
																		<option value=''>Select...</option>
																		<?php
																		$stmt_bnk = $db->query("SELECT bank FROM bank WHERE biller = 1 ORDER BY bank");
																		while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)):
																			$bank = htmlspecialchars($row_rstbank['bank']);
																		?>
																			<option value="<?= $bank ?>"><?= $bank ?></option>
																		<?php endwhile; ?>
																	</select>
																</div>

															</td>
														</tr>
														<tr>
															<td width="40%" style="padding-right:10px;">
																<div class="form_sep" id="cashpos"><br>
																	<label for="reg_input_no" class=""><strong style="color: #F00">Enter Cash Amount only (POS/Transfer & Cash)</strong></label>
																	<input type="number" id="cash_split" name="cash_split" class="form-control" max="<?php echo $cash; ?>">
																</div>



																<div class="form_sep" id="bill_account"><br>
																	<label for="auth_staff" class="text-danger font-weight-bold">
																		Select the account name to be billed:
																	</label>
																	<select name="auth_staff" id="auth_staff" class="form-control">
																		<option value=''>Select...</option>
																		<?php
																		$stmt_bnk = $db->query("SELECT EmployeeCode, fullname FROM admin_users WHERE bill_account_status = 1 ORDER BY fullname");
																		while ($row = $stmt_bnk->fetch(PDO::FETCH_ASSOC)):
																			$employeeCode = htmlspecialchars($row['EmployeeCode']);
																			$fullname = htmlspecialchars($row['fullname']);
																		?>
																			<option value="<?= $employeeCode ?>"><?= $fullname ?></option>
																		<?php endwhile; ?>
																	</select>
																</div>





															</td>
															<td></td>
															<td></td>
														</tr>
													</table>

													<hr>
													<div>
														<label>Payment Remarks</label>
														<textarea class=" input-sm form-control" cols="5" rows="2" name="payment_remarks" id="payment_remarks" maxlength="100"></textarea>
													</div>

													<div>
														<br>
														<label for="value_date" style="color:red;">Value Date</label>
														<input type="date" id="value_date" value="<?= date('Y-m-d'); ?>" class="input-sm form-control">
													</div>




													<br>
													<div class="pull-left">

														<button type="button" class="btn btn-success" id="paynow_final_pay_BUTTON"
															onclick="paynow_final_pay()"><?php if ($voucher_type == 'writeoff') { ?>WRITE OFF<?php } else { ?> Post Now<?php } ?> </button>


													</div>


													<div class="pull-right">
														<a href="pacct.php?emr=<?php echo $emr; ?>&pay" class="btn btn-danger">Cancel</a>

													</div>

												</div>
											</div>


											<div class="col-md-4">
												<div class="ibox float-e-margins">
													<table class="table table-bordered" style="font-family:Tahoma, Geneva, sans-serif; font-size:14px;">
														<tr>
															<td colspan="2"><strong>See Payment Details</strong></td>
														</tr>
														<tr>
															<td>Patient's Deposit Amount</td>
															<td><?php echo number_format($wallet_amount, 2); ?></td>
														</tr>
														<tr>
															<td>Total Amount Accrued</td>
															<td><?php echo number_format($amt_sysSelected, 2); ?></td>
														</tr>
														<?php if ($total_dsc > 0) { ?>
															<tr>
																<td>Discount:</td>
																<td><?php echo number_format($total_dsc, 2); ?></td>
															</tr>
														<?php } ?>
														<?php if ($v_discount > 0) { ?>
															<tr>
																<td>Voucher Discount:</td>
																<td><?php echo number_format($v_discount, 2); ?></td>
															</tr>
														<?php } ?>
														<?php if ($total_chr > 0) { ?>
															<tr>
																<td>Additional Charge:</td>
																<td><?php echo number_format($total_chr, 2); ?></td>
															</tr>
														<?php } ?>

														<tr>
															<td style="background-color:greenyellow; ">
																<h2>PAYING</h2>
															</td>
															<td style="background-color:greenyellow; ">
																<h2><strong><?php
																			if ($voucher_type == 'writeoff') {
																				$new_current_bal = 0;
																				$credit_bal = 0;
																				echo 'WriteOff';
																			} else {
																				echo number_format($cash, 2);
																			} ?></strong></h3>
															</td>
														</tr>
														<tr>
															<td>Outstanding/Balance</td>
															<td><?php if ($new_current_bal == 0) {
																	echo 'Zero Naira';
																} else {
																	echo number_format($current_balance, 2);
																} ?></td>
														</tr>
														<tr>
															<td>Total Credit</td>
															<td><?php if ($total_credit == 0) {
																	echo 'Zero Naira';
																} else {
																	echo number_format($total_credit, 2);
																} ?></td>
														</tr>
														<tr>
															<td>Credit Balance</td>
															<td><?php if ($credit_bal == 0) {
																	echo 'Zero Naira';
																} else {
																	echo number_format($credit_bal, 2);
																} ?></td>
														</tr>

													</table>

												</div>
											</div>
										</div>


										<input type="hidden" value="<?php echo $emr; ?>" name="emr" id="emr">
										<input type="hidden" value="<?php echo $insurance_type; ?>" name="insurance_type" id="insurance_type">
										<input type="hidden" value="<?php echo $wallet_account; ?>" name="wallet_account" id="wallet_account">
										<input type="hidden" value="<?php echo $search_again_cancel; ?>" name="search_again_cancel" id="search_again_cancel">
										<input type="hidden" value="<?php echo $total_credit; ?>" name="total_credit" id="total_credit">
										<input type="hidden" value="<?php echo $current_balance; ?>" name="current_balance" id="current_balance">
										<input type="hidden" value="<?php echo $insurance_no; ?>" name="insurance_no" id="insurance_no">
										<input type="hidden" value="<?php echo $paying_outstanding; ?>" name="new_amt_payment" id="new_amt_payment">
										<input type="hidden" value="<?php echo $amt_paying; ?>" name="amt_paying" id="amt_paying">
										<input type="hidden" value="<?php echo $cash; ?>" name="cash" id="cash">
										<input type="hidden" value="<?php echo $v_discount; ?>" name="v_discount" id="v_discount">
										<input type="hidden" value="<?php echo $voucher_type; ?>" name="voucher_type" id="voucher_type">
										<input type="hidden" value="<?php echo $voucher_center; ?>" name="voucher_center" id="voucher_center">

										<input type="hidden" value="<?php echo $voucher_center; ?>" name="voucher_created_by" id="voucher_created_by">
										<input type="hidden" value="<?php echo $auth_code; ?>" name="auth_code" id="auth_code">
										<input type="hidden" value="<?php echo $save_insurance_no; ?>" name="save_insurance_no" id="save_insurance_no">
										<input type="hidden" value="<?php echo $patient_name; ?>" name="patient_name" id="patient_name">
										<input type="hidden" value="<?php echo $grp_idv_no; ?>" name="grp_idv_no" id="grp_idv_no">
										<input type="hidden" value="" name="wallet_payeee" id="wallet_payeee">
										<input type="hidden" value="<?php echo $wallet_amount; ?>" name="wallet_amount" id="wallet_amount">

										<?php
										$rnd = rand(1000, 99999);
										$concat = $rnd . date('dmyH') . $emr;
										?>
										<input type="hidden" value="<?php echo $concat; ?>" name="transaction_code" id="transaction_code">
										<input type="hidden" value="<?php echo $debt_post; ?>" name="debt_post" id="debt_post">
										<input type="hidden" value="<?php echo $discount_set; ?>" name="discount_set" id="discount_set">
										<input type="hidden" value="<?php echo $discount_insurance_no; ?>" name="discount_insurance_no" id="discount_insurance_no">
										<input type="hidden" value="<?php echo $dsc_chr_type; ?>" name="dsc_chr_type" id="dsc_chr_type">

										<?php
										for ($i = 0; $i < count($SystemSelected); $i++) {
											$inv_id = $SystemSelected[$i];
										?>
											<input type="hidden" value="<?php echo $inv_id; ?>" name="SystemSelected[]" />

										<?php } ?>


									</form>
								</div>

								<?php

								?>



							<?php


							} elseif (isset($_GET['invoice_stt'])) {
								include("invoice_stt.php");
							} elseif (isset($_GET['inv'])) {
								$target = "inv";
								include("../inc/invoice.php");
							} elseif (
								isset($_GET['pay']) ||
								isset($_POST['apply_pay']) ||
								(isset($final_pay_err) && $final_pay_err == 1)
							) {
								$target = "pay";
								include("../inc/pay.php");
							} elseif (
								isset($_POST['print_recep']) ||
								(isset($print_afterpay) && $print_afterpay == 1) ||
								isset($_GET['dep']) ||
								isset($_GET['rfd'])
							) {
								include("../inc/print.php");
							} elseif (
								isset($_GET['stt']) ||
								isset($_POST['statement']) ||
								isset($_POST['reciept']) ||
								isset($_POST['print_ap'])
							) {
								include("stt.php");
							} elseif (isset($_GET['rof']) || isset($_POST['apply_rof'])) {
								include("riteoff.php");
							} else {
								$final_pay_err = 0;

							?>
								<div class="ibox-title">
									<h5>Welcome to <u><?php echo $patient_name . "</u> Account!";
														if ($referral_name != '') {
															echo "<b style='color:blue;'> (Referral: $referral_name) .</b>";
														} ?></h5>
								</div>
								<div class="ibox-content">

									<div class="row">
										<strong>&nbsp;&nbsp;&nbsp;Phone Number: <?php echo $phone; ?></strong>
										<hr>
										<strong></strong>

										<div class="col-lg-6">

											<?php //if($_SESSION['deposit']==1){
											?>
											<h4 style="color:blue; ">For money deposits, patient refunds, and inter-patient transfers, <br>click the <b><u>Deposit & Refund</u></b> button below.</h4>
											<a class="btn btn-app money" data-toggle="modal" data-target="#myModal5"><i class="fa fa-bank money"></i> Deposit & Refund</a>
											<?php /// } 
											?>
											<?= $hos_no; ?>

											<a href="pacct.php?emr=<?php echo $emr; ?>&inv" class="btn btn-app"><i class="fa fa-check-square-o"></i> Invoice / Claims</a>
											<a href="pacct.php?emr=<?php echo $emr; ?>&pay" class="btn btn-app"><i class="fa fa-paypal"></i>Pay Now</a>
											<a href="pacct.php?emr=<?php echo $emr; ?>&stt" class="btn btn-app"><i class="fa fa-ticket"></i>Reciepts / Statement</a>
											<a href="../admin/index.php?sale=<?php echo $emr; ?>" class="btn btn-app"><i class="fa fa-shopping-cart"></i><span>Add Services & Sales</span></a>
											<a class="btn btn-app validate_package" data-toggle="modal" data-target="#myModal5"><i class="fa fa-check-square-o validate_package" id="<?= $emr; ?>"></i>Validate Package</a>



											<hr>
											<a href="pacct.php?emr=<?php echo $emr; ?>" class="btn btn-app"><i class="fa fa-recycle"></i><strong>Refresh</strong></a>
											<a href="index.php" class="btn btn-app"><i class="fa fa-times"></i><span style="color:#F00">Close</span></a>


										</div>
										<div class="col-lg-6">


											<h2 <?php if ($Total_total_credits > 0) { ?>style="color: red;" <?php } ?>>ACCOUNT RECIEVABLE: N<?= number_format($Total_total_debit); ?></h2>
											<?php if ($Total_total_credits > 0) { ?>
												<hr>
												<p style="font-size: 16px; "><b>TOTAL SERVICE DELIVERED ON-CREDIT: N<?= number_format($Total_total_credits); ?></b><br>
													<span style="color: red; ">Pending To Be Post To Patient's Debt Profile</span>
												</p>
											<?php } ?>

											<hr>

											<?php //if($_SESSION['writeoff']==1){
											?>
											<a href="pacct.php?emr=<?php echo $emr; ?>&rof" class="btn btn-app"><i class="fa fa-check"></i><span style="color:#F00">Accounts</span></a>
											<?php ///} 
											?>


											<?php if ($_SESSION['discount'] == 1) { ?>
												<a class="btn btn-app add_credit_limit" data-toggle="modal" data-target="#myModal5" id="<?= $emr; ?>"><i class="fa fa-smile-o add_credit_limit" style="color:red;"></i>Set/Edit Credit Limit</a>
											<?php } ?>



											<br>


											<?php if ($_SESSION['discount'] == 1) { ?>
												&nbsp;<h4>&nbsp; - Setup Discounts & Charges </h4>
												<a class="btn btn-app discount_modal" data-toggle="modal" data-target="#myModal5"><i class="fa fa-smile-o discount_modal"></i>Discount / Charges</a>
											<?php } ?>



											<a href="index.php" class="btn btn-app"><i class="fa fa-times"></i><span style="color:#F00">Close</span></a>

											<!--
<hr>
<a href="oldemr_trans.php?hospital_no=" class="btn btn-danger">Open OLD Billing</a>
-->

										</div>
									</div>
								<?php } ?>


								</div>
						</div>

					</div>

				</div>
				<?php include("../inc/footer.php"); ?>

			</div>
		</div>


		<div class="modal inmodal fade" id="add_credit_limit_modal" tabindex="-1" role="dialog" aria-hidden="true">
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





		<!-- Payment Confirmation Modal -->
		<div class="modal inmodal fade" id="paynow_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Confirm Payment Processing</h4>
					</div>
					<div class="modal-body" id="paynow_confirm_modal_body">
						<p style="font-size: 15px; font-weight: bold; color: #333;">Are you sure you want to process this payment transaction?</p>
						<div id="paynow_confirm_details" style="background: #f8f9fa; padding: 12px; border: 1px solid #e7eaec; border-radius: 4px; font-size: 14px; margin-top: 10px;">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary" id="confirm_paynow_process_btn" onclick="execute_paynow_final_pay()">Confirm & Process</button>
					</div>
				</div>
			</div>
		</div>

		<?php include("mdl.php"); ?>


		<div class="modal inmodal fade" id="adm_transactn_" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Admission Invoice</h4>
					</div>
					<div class="modal-body">
						<form method="post" action="../adm_transc.php">

							<div class="ibox float-e-margins">
								<label for="reg_input_no" class="">Set Dates Range & Click Apply button </label><br>
								<div class="form_sep" id="">
									<div class="input-daterange input-group" id="">
										<input type="date" class="form-control" name="start" value="<?php echo $from_date; ?>" />
										<span class="input-group-addon">to</span>
										<input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
									</div>
								</div>
								<div class="form_sep">
									<label for="reg_input_name" class="">Enter Discount Voucher (Optional)</label>
									<input type="text" id="discount_vourcher" name="discount_vourcher" class="form-control">
								</div>

								<div class="form_sep">
									<div class="pull-left">
										<input type="hidden" value="<?php echo $emr; ?>" name="emr" id="emr">
										<input type="hidden" value="<?php echo "billing/pacct.php?emr=$emr&$target"; ?>" name="target">
										<button type="submit" class="btn btn-success btn btn-sm" name="submit_">Apply</button>

									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include('../modal_lock.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>

		<script>
			$(document).on('click', '.add_credit_limit', function() {
				var add_credit_limit_id = $(this).attr("id");
				var interface_type = 'billing'; // renamed

				$.ajax({
					url: "../add_credit_limit.php",
					method: "POST",
					cache: false,
					data: {
						add_credit_limit_id: add_credit_limit_id,
						interface_name: interface_type
					},
					success: function(data) {
						$('#add_credit_limit_modal .modal-title').text('Add and Delete Credit Limit');
						$('#add_credit_limit_modal #add_credit_limit_body').html(data);

						$('#add_credit_limit_modal').modal('show');
					}
				});
			});

			$(document).ready(function() {
				// Toggle all checkboxes when the toggle_all_checkboxes is clicked
				$("#toggle_all_checkboxes").click(function() {
					// Get the current state of the toggle checkbox
					var isChecked = $(this).prop('checked');

					// Apply that state to all checkboxes with class inv_checkbox_
					$(".inv_checkbox_").prop('checked', isChecked);
				});

				// Update the toggle_all checkbox state when individual checkboxes change
				$(".inv_checkbox_").click(function() {
					// If all checkboxes are checked, check the toggle_all
					if ($(".inv_checkbox_:checked").length === $(".inv_checkbox_").length) {
						$("#toggle_all_checkboxes").prop('checked', true);
					} else {
						$("#toggle_all_checkboxes").prop('checked', false);
					}
				});
			});
		</script>

		<script>
			function UpdateCost() {
				var sum = 0;
				var gn, elem;
				var maxLoop = <?php echo isset($inv_count) ? intval($inv_count) : (isset($n) ? intval($n) + 1 : 1000); ?>;
				for (var i = 1; i < maxLoop; i++) {
					gn = 'add_m_' + i;
					elem = document.getElementById(gn);

					if (elem && elem.checked) {
						var mystr = elem.value;
						var myarr = mystr.split("__");

						// Use parseFloat to handle decimal numbers
						sum += parseFloat(myarr[1]) || 0; // Fallback to 0 if parsing fails
					}
				}

				// Format the sum to two decimal places
				var value = sum.toFixed(2);
				var formattedValue = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

				var totalCostElem = document.getElementById('totalcost');
				if (totalCostElem) {
					totalCostElem.value = formattedValue;
				}
			}

			window.onload = UpdateCost;
		</script>

		<?php //include("../inc/footer_scripts.php"); 
		?>
		<script>
			<?php if ($error != "") { ?>
				toastr.error('<?php echo $error ?>', 'Error', {
					timeOut: 5000
				})
			<?php } ?>

			<?php if ($sv == "1") { ?>
				toastr.success('Saved Successfully', 'Saved', {
					timeOut: 5000
				})
			<?php } ?>

			<?php if (isset($_GET['dsc']) and $done == 'msg') { ?>
				toastr.success('Discount Stopped Successfully', 'Stopped', {
					timeOut: 5000
				})
			<?php } ?>

			<?php if (isset($_GET['as'])) { ?>
				toastr.success('Click Discount/Charges again to add selected services', 'Add Services', {
					timeOut: 5000
				})
			<?php } ?>

			<?php if (isset($_GET['sv'])) { ?>
				toastr.success('Successfully', 'Success', {
					timeOut: 5000
				})
			<?php }
			if ($error_wallet == 1) {
			?>
				toastr.error('Patient Deposit Not Available for Deposit! Contact Hospital Accountant.', 'Error', {
					timeOut: 5000
				})
			<?php }
			if (isset($_GET['dep_error'])) {
				$dep_error = $_GET['dep_error'];
			?>
				toastr.error('<?php echo $dep_error ?>', 'Error', {
					timeOut: 5000
				})
			<?php } ?>





			<?php if (isset($_GET['chr']) and $done == 'msg') { ?>
				toastr.success('Charges Stopped Successfully', 'Stopped', {
					timeOut: 5000
				})
			<?php } ?>

			<?php if (isset($_GET['err_selection'])) { ?>
				toastr.error('Invalid Selection', 'Error', {
					timeOut: 5000
				})
			<?php } ?>

			<?php if ($final_pay_err == 1) { ?>
				toastr.error('Select Bank Name', 'Error', {
					timeOut: 5000
				})
			<?php } ?>




			<?php if (isset($_GET['error'])) { ?>
				toastr.error('You can not set All Services to discount and charges at the same time ... ', 'Error', {
					timeOut: 5000
				})
			<?php } ?>


			/*
		$(document).ready(function(){
			$("#cashpos").hide();
			//("#Private").hide();
			
    $('#paymethod').on('change', function() {
      if ( this.value == 'CASHPOS')
      {
		$("#cashpos").show();
		}

      if ( this.value != 'CASHPOS')
      {
		$("#cashpos").hide();
		}

});  
*/


			function pick_deposit() {
				///alert();
				document.getElementById('paymethod').value = 'Wallet';
			}

			$(document).ready(function() {
				function updatePayMethodUI() {
					const payMethod = $('#paymethod').val() || '';
					const walletAmount = parseFloat($('#wallet_amount').val()) || 0;
					const cash = parseFloat($('#cash').val()) || 0;

					// Reset visibility
					$("#cashpos, #bill_account").hide();

					let buttonLabel = 'Post Now';

					switch (payMethod) {
						case 'Wallet':
							$('#wallet_modal').modal('show');
							buttonLabel = 'PATIENT DEPOSIT PAYMENT';
							break;

						case 'cash':
							if (walletAmount >= cash && walletAmount > 0) {
								$('#sufficient_bal_modal').modal('show');
							}
							buttonLabel = 'CASH PAY NOW';
							break;

						case 'pay_from_patient_wallet':
							$('#pay_from_patient_wallet_modal').modal('show');
							buttonLabel = 'PAY FROM ANOTHER PATIENT DEPOSIT';
							break;

						case 'Reconcile':
							$('#Reconcile_modal').modal('show');
							buttonLabel = 'RECONCILATION';
							break;

						case 'PostCredit':
							$('#PostCredit_modal').modal('show');
							buttonLabel = 'DELIVER SERVICE AS CREDIT/AR';
							break;

						case 'CASHPOS':
						case 'CASHTransfer':
							if (walletAmount >= cash && walletAmount > 0) {
								$('#sufficient_bal_modal').modal('show');
							}
							$("#cashpos").show();
							buttonLabel = 'SPLIT PAYMENT';
							break;

						case 'Bill_to':
							if (walletAmount >= cash && walletAmount > 0) {
								$('#sufficient_bal_modal').modal('show');
							}
							$("#bill_account").show();
							buttonLabel = 'BILL TO ACCOUNT';
							break;

						case 'POS':
							if (walletAmount >= cash && walletAmount > 0) {
								$('#sufficient_bal_modal').modal('show');
							}
							buttonLabel = 'POS PAYMENT';
							break;

						case 'Transfer':
							if (walletAmount >= cash && walletAmount > 0) {
								$('#sufficient_bal_modal').modal('show');
							}
							buttonLabel = 'BANK TRANSFER';
							break;

						case 'writeoff':
							if (walletAmount >= cash && walletAmount > 0) {
								$('#sufficient_bal_modal').modal('show');
							}
							buttonLabel = 'WRITE OFF';
							break;

						case '':
						default:
							buttonLabel = 'Select Payment Method';
							break;
					}

					$('#paynow_final_pay_BUTTON').html(buttonLabel);
				}

				$('#paymethod').on('change', updatePayMethodUI);
				updatePayMethodUI();
			});


			$('#data_5 .input-daterange').datepicker({
				keyboardNavigation: false,
				forceParse: false,
				autoclose: true
			});

			$(document).on('click', '.misc', function() {
				$('.modal-title').text('Add Miscellaneous Bill/Charge');
				$('#misc_modal').modal('show');
			});

			$(document).on('click', '.money', function() {
				//   var lab_request_no2 = $(this).attr("id"); 
				//  	 var res = lab_request_no2.split("__");

				$('.modal-title').text('Money Transaction');
				$('#money_modal').modal('show');
				$('#money_form').html(data);

			});


			$(document).on('click', '.discount_modal', function() {
				//   var lab_request_no2 = $(this).attr("id"); 
				//  	 var res = lab_request_no2.split("__");

				$('.modal-title').text('Patient Discount and Charge setup');
				$('#discount_modal').modal('show');
				$('#discount_form').html(data);

			});

			$(document).on('click', '.add_charge', function() {
				//  	 var res = lab_request_no2.split("__");

				$('.modal-title').text('Patient Discount and Charge setup');
				$('#discount_charge_modal_2').modal('show');
				$('#discount_charge_form_2').html(data);

			});


			$(document).on('click', '.add_services', function() {
				// $('#view_lab_option_modal').modal('hide'); 
				var add_services_id = $(this).attr("id");




				if (add_services_id != '') {
					$.ajax({
						url: "fetch_set.php",
						method: "POST",
						data: {
							add_services_id: add_services_id
						},
						success: function(data) {

							$('.modal-title').text('Add Services/Items');

							$('#add_services_modal').modal('show');
							$('#add_services_body').html(data);
						}
					});
				}
			});


			$(document).on('click', '.adm_invoice', function() {
				$('#adm_transactn_').modal('show');
				//$('#add_services_body').html(data); 
			});

			$(document).on('click', '.view_notes', function() {
				var note_inv_id = $(this).attr("id");
				var res = note_inv_id.split("__");

				// echo $row["sn"] .'__'. $row['item_services'].'__'. $patient_type.'__'. $referral_name .'__'. $insurance_no

				if (note_inv_id != '') {
					$.ajax({
						url: "fetch_set.php",
						method: "POST",
						data: {
							note_inv_id: res[0] + '__' + res[2] + '__' + res[3] + '__' + res[4] + '__' + res[5]
						},
						success: function(data) {

							$('.modal-title').text('Invoice Notes: ' + res[1]);

							$('#view_invoice_body').html(data);
							$('#view_invoice_modal').modal('show');
						}
					});
				}
			});


			$(document).on('click', '.part_payment_entry', function() {
				var part_payment_entry_id = $(this).attr("id");
				if (part_payment_entry_id != '') {
					$.ajax({
						url: "fetch_set2.php",
						method: "POST",
						// data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
						data: {
							part_payment_entry_id: part_payment_entry_id
						},

						success: function(data) {

							$('.modal-title').text('Part Payment Entry');

							$('#edit_price_body').html(data);
							$('#edit_price_modal').modal('show');
						}
					});
				}
			});


			$(document).on('click', '.edit_price_entry', function() {

				var editprice_id = $(this).attr("id");
				if (editprice_id != '') {

					$.ajax({
						url: "fetch_set2.php",
						method: "POST",
						// data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
						data: {
							editprice_id: editprice_id
						},

						success: function(data) {


							$('.modal-title').text('Edit Price');

							$('#edit_price_body').html(data);
							$('#edit_price_modal').modal('show');
						}
					});
				}
			});




			$('#money_form').on("submit", function(event) {
				event.preventDefault();
				if ($('#transaction_type').val() == "Deposit" && $('#mode_pay').val() == "") {
					swal("Mode of Payment is required for Deposit Transaction");
				} else if ($('#transaction_type').val() == "Refund" && $('#mode_pay').val() == "") {
					swal("Mode of Payment is required for Refund Transaction");
				} else if ($('#transaction_type').val() == "") {
					swal("Transaction Type is required");
				} else if ($('#amount').val() == "") {
					swal("Amount is required");
				} else {
					$.ajax({
						url: "insert.php",
						method: "POST",
						data: $('#money_form').serialize(),
						beforeSend: function() {
							$('#save').val("Saving");
						},
						success: function(data) {

							var search_emr = $("#emr").val();

							$('#money_modal').modal('hide');

							$.ajax({
								url: "fetch_set.php",
								method: "POST",
								data: {
									search_emr: search_emr
								},
								success: function(data) {

									$('.modal-title').text('Money Transaction Confirmation');

									$('#add_money_confirm_modal').modal('show');
									$('#add_money_confirm_body').html(data);
								}
							});

						},
						complete: function() {

						},
						error: function(data) {

							alert("Oops...", "Something went wrong :(", "error");
							swal({
								title: 'Oops...!',
								text: 'Something went wrong ',
								type: 'error',
								timer: 500
							})
						}
					});

				}
			});


			$('#discount_add_form').on("submit", function(event) {

				event.preventDefault();
				$.ajax({
					url: "insert.php",
					method: "POST",
					data: $('#discount_add_form').serialize(),
					beforeSend: function() {
						$('#save').val("Saving");
					},
					success: function(data) {

						var service_type = $("#service_type").val();

						if (service_type == 'All Services') {
							location.href = "pacct.php?emr=<?php echo $emr ?>&sv"
						}

						if (service_type == 'specify') {
							location.href = "pacct.php?emr=<?php echo $emr ?>&as"
						}
					},
					complete: function() {
						$('#save').val("Saved");
					},
					error: function(data) {

						alert("Oops...", "Something went wrong :(", "error");
						//swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					}
				});
			});


			$('#discount_edit_body').on("submit", function(event) {
				event.preventDefault();

				// $('#discount_edit_modal').modal('hide');

				$.ajax({
					url: "insert.php",
					method: "POST",
					data: $('#discount_edit_body').serialize(),
					beforeSend: function() {
						$('#save').val("Updating");
					},
					success: function(data) {

						var service_type = $("#service_type1").val();

						location.href = "pacct.php?emr=<?php echo $emr ?>&sv"


					},
					complete: function() {
						$('#save').val("Updated");
					},
					error: function(data) {

						alert("Oops...", "Something went wrong :(", "error");
						//swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					}
				});
			});



			$(document).on('click', '.add_services_item_del', function() {
				var service_item_no = $(this).attr("id");

				var res = service_item_no.split("__");

				if (service_item_no != '') {
					$.ajax({
						url: "delete.php",
						method: "POST",
						data: {
							service_item_no: res[0]
						},
						success: function(data) {

							var service_item_no = res[1] + '__' + res[2] + '__' + res[3];

							$.ajax({
								url: "fetch_set.php",
								method: "POST",
								data: {
									add_services_id: service_item_no
								},
								success: function(data) {

									$('#add_services_modal').modal('show');
									$('#add_services_body').html(data);
								}
							});

							// $('#view_lab_modal').modal('show');  
							//$('#view_lab_body').html(data); 
						}
					});
				}
			});


			$(document).on('click', '.view_history', function() {
				var history_id = $(this).attr("id");

				var res = history_id.split("__");

				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						history_id: res[0]
					},
					success: function(data) {

						$('.modal-title').text('History of Entries');
						$('#view_history_modal').modal('show');
						$('#view_history_body').html(data);
					}
				});
			});


			$(document).on('click', '.confirm', function() {
				$('#view_lab_option_modal').modal('hide');
				var search_emr = $(this).attr("id");
				if (search_emr != '') {
					$.ajax({
						url: "fetch_set.php",
						method: "POST",
						data: {
							search_emr: search_emr
						},
						success: function(data) {

							$('.modal-title').text('Money Transaction Confirmation');

							$('#add_money_confirm_modal').modal('show');
							$('#add_money_confirm_body').html(data);
						}
					});
				}
			});


			$(document).on('click', '.delete_confirm', function() {
				var delete_id = $(this).attr("id");

				var res = delete_id.split("__");
				var delete_id = res[0] + '__' + res[2] + '__' + res[3];

				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						delete_id: delete_id
					},
					success: function(data) {

						$('.modal-title').text('Delete Confirmation: ' + res[1]);
						$('#delete_confirmation_modal').modal('show');
						$('#delete_confirmation_body').html(data);
					}
				});
			});

			function getVal(id) {
				var el = document.getElementById(id);
				return el ? el.value : '';
			}

			function paynow_final_pay() {
				console.log("paynow_final_pay called");

				var emr = getVal('emr');
				var value_date = getVal('value_date');
				var auth_staff = getVal('auth_staff');
				var cash_split = getVal('cash_split');
				var insurance_no = getVal('insurance_no');
				var cash = getVal('cash');
				var v_discount = getVal('v_discount');
				var auth_code = getVal('auth_code');
				var save_insurance_no = getVal('save_insurance_no');
				var patient_name = getVal('patient_name');
				var grp_idv_no = getVal('grp_idv_no');
				var transaction_code = getVal('transaction_code');
				var wallet_amount = getVal('wallet_amount');
				var paymethod = getVal('paymethod');
				var payment_remarks = getVal('payment_remarks');
				var ref_no = getVal('ref_no');
				var bank_name = getVal('bank_name');
				var voucher_type = getVal('voucher_type');

				var characterLength = payment_remarks ? payment_remarks.length : 0;

				console.log("paynow_final_pay state:", {
					emr: emr,
					paymethod: paymethod,
					bank_name: bank_name,
					auth_staff: auth_staff,
					value_date: value_date,
					voucher_type: voucher_type,
					characterLength: characterLength
				});

				function showMsg(msg, isError) {
					console.log("showMsg:", msg, isError ? "ERROR" : "SUCCESS");
					if (typeof toastr !== 'undefined') {
						if (isError) toastr.error(msg, 'Error', { timeOut: 5000 });
						else toastr.success(msg, 'Success', { timeOut: 5000 });
					} else {
						alert(msg);
					}
				}

				// Check if payment method is selected
				if (!paymethod || paymethod === '') {
					console.warn("Validation failed: paymethod is empty");
					showMsg('Select Payment Method Before you Continue', true);
					return false;
				}

				// Check for Bill_to with missing staff authorization
				if (paymethod === 'Bill_to' && !auth_staff) {
					console.warn("Validation failed: auth_staff is empty for Bill_to");
					showMsg('Select Account Name To Bill To', true);
					return false;
				}

				// Check for required remarks in certain payment methods or writeoff
				const needsLongRemark = ['pay_from_patient_wallet', 'Bill_to'].includes(paymethod) || voucher_type === 'writeoff';
				if (needsLongRemark && characterLength < 50) {
					console.warn("Validation failed: remarks < 50 chars");
					showMsg('Enter at least 50 characters or more for Remarks', true);
					return false;
				}

				const bankRequiredMethods = ['POS', 'CASHPOS', 'Transfer', 'CASHTransfer'];
				if (bankRequiredMethods.includes(paymethod) && !bank_name) {
					console.warn("Validation failed: bank_name is empty");
					showMsg('Select Bank Name !', true);
					return false;
				}

				if (!value_date) {
					console.warn("Validation failed: value_date is empty");
					showMsg('Invalid Date', true);
					return false;
				}

				var favorite = [];
				$.each($("input[name='item[]']"), function() {
					var val = $(this).val();
					if (val && val !== '' && !favorite.includes(val)) {
						favorite.push(val);
					}
				});
				if (favorite.length === 0) {
					$.each($("input[name='SystemSelected[]']"), function() {
						var val = $(this).val();
						if (val && val !== '' && !favorite.includes(val)) {
							favorite.push(val);
						}
					});
				}

				var detailsHtml = `
					<div style="font-size: 14px; line-height: 1.8;">
						<p style="margin-bottom: 4px;"><strong>Patient:</strong> ${patient_name || emr}</p>
						<p style="margin-bottom: 4px;"><strong>Payment Method:</strong> ${paymethod}</p>
						${bank_name ? `<p style="margin-bottom: 4px;"><strong>Receiving Bank:</strong> ${bank_name}</p>` : ''}
						${ref_no ? `<p style="margin-bottom: 4px;"><strong>Reference No:</strong> ${ref_no}</p>` : ''}
						<p style="margin-bottom: 4px;"><strong>Value Date:</strong> ${value_date}</p>
						<p style="margin-bottom: 0;"><strong>Selected Items:</strong> ${favorite.length} item(s)</p>
					</div>
				`;

				$('#paynow_confirm_details').html(detailsHtml);
				$('#paynow_confirm_modal').modal('show');
			}

			function execute_paynow_final_pay() {
				console.log("execute_paynow_final_pay called");
				$('#paynow_confirm_modal').modal('hide');

				var emr = getVal('emr');
				var value_date = getVal('value_date');
				var auth_staff = getVal('auth_staff');
				var cash_split = getVal('cash_split');
				var insurance_no = getVal('insurance_no');
				var cash = getVal('cash');
				var v_discount = getVal('v_discount');
				var auth_code = getVal('auth_code');
				var save_insurance_no = getVal('save_insurance_no');
				var patient_name = getVal('patient_name');
				var grp_idv_no = getVal('grp_idv_no');
				var transaction_code = getVal('transaction_code');
				var wallet_amount = getVal('wallet_amount');
				var paymethod = getVal('paymethod');
				var payment_remarks = getVal('payment_remarks');
				var ref_no = getVal('ref_no');
				var bank_name = getVal('bank_name');
				var wallet_payeee = getVal('wallet_payeee');
				var wallet_account = getVal('wallet_account');
				var insurance_type = getVal('insurance_type');
				var voucher_center = getVal('voucher_center');
				var voucher_type = getVal('voucher_type');
				var voucher_created_by = getVal('voucher_created_by');
				var debt_post = getVal('debt_post');
				var discount_set = getVal('discount_set');
				var discount_insurance_no = getVal('discount_insurance_no');
				var dsc_chr_type = getVal('dsc_chr_type');

				function showMsg(msg, isError) {
					if (typeof toastr !== 'undefined') {
						if (isError) toastr.error(msg, 'Error', { timeOut: 5000 });
						else toastr.success(msg, 'Success', { timeOut: 5000 });
					} else {
						alert(msg);
					}
				}

				var favorite = [];
				$.each($("input[name='item[]']"), function() {
					var val = $(this).val();
					if (val && val !== '' && !favorite.includes(val)) {
						favorite.push(val);
					}
				});
				if (favorite.length === 0) {
					$.each($("input[name='SystemSelected[]']"), function() {
						var val = $(this).val();
						if (val && val !== '' && !favorite.includes(val)) {
							favorite.push(val);
						}
					});
				}
				var v = favorite.join(",");

				var btnElem = document.getElementById("paynow_final_pay_BUTTON");
				if (btnElem) btnElem.disabled = true;
				var button_titel = btnElem ? btnElem.innerHTML : 'CASH PAY NOW';
				if (btnElem) btnElem.innerHTML = 'Wait ...';

				var paynow_final = true;

				console.log("Sending AJAX to pacct_process.php...");
				$.ajax({
					url: "pacct_process.php",
					method: "POST",
					data: {
						emr: emr,
						auth_staff: auth_staff,
						item: v,
						paynow_final: paynow_final,
						insurance_no: insurance_no,
						cash: cash,
						v_discount: v_discount,
						auth_code: auth_code,
						save_insurance_no: save_insurance_no,
						patient_name: patient_name,
						grp_idv_no: grp_idv_no,
						transaction_code: transaction_code,
						wallet_amount: wallet_amount,
						paymethod: paymethod,
						payment_remarks: payment_remarks,
						ref_no: ref_no,
						bank_name: bank_name,
						cash_split: cash_split,
						wallet_payeee: wallet_payeee,
						wallet_account: wallet_account,
						insurance_type: insurance_type,
						value_date: value_date,
						voucher_created_by: voucher_created_by,
						debt_post: debt_post,
						discount_set: discount_set,
						discount_insurance_no: discount_insurance_no,
						dsc_chr_type: dsc_chr_type
					},
					success: function(data) {
						console.log("AJAX success response raw:", data);
						var jsonn = {};
						try {
							jsonn = JSON.parse(data);
						} catch(e) {
							console.error("JSON parse error:", e, data);
							if (btnElem) {
								btnElem.disabled = false;
								btnElem.innerHTML = button_titel;
							}
							showMsg("Server Error: " + data, true);
							return;
						}

						if (jsonn["status"] == 3) {
							window.location = '../inc/printout2.php?deposit=' + emr + '&name=' + patient_name + '&dep=' + jsonn["url"];

						} else if (jsonn["status"] == 1) {
							if (btnElem) {
								btnElem.disabled = false;
								btnElem.innerHTML = button_titel;
							}
							showMsg(jsonn["message"], true);

						} else if (jsonn["status"] == 22) {
							if (btnElem) {
								btnElem.disabled = true;
								btnElem.innerHTML = button_titel;
							}
							showMsg(jsonn["message"], false);

						} else {
							showMsg(jsonn["message"] || 'Success', false);
							window.location = '../inc/printout2.php?recepinv=' + emr + '&name=' + patient_name + '&r';
						}
					},
					error: function(xhr, status, err) {
						console.error("AJAX Error:", status, err);
						if (btnElem) {
							btnElem.disabled = false;
							btnElem.innerHTML = button_titel;
						}
						showMsg("Network error: " + err, true);
					}
				});
			}

			process_accomodation_invoice();
			process_accomodation_invoice2();
			process_accomodation_invoice3();

			function process_accomodation_invoice() {
				$.ajax({
					url: "../auth_drop_adm_invoice_bed_only.php",
					method: "POST",
					data: {
						generate_bed_invoice: true,
						hospital_no: "<?php echo $emr; ?>"
					},
					success: function(data) {
						///alert(data);
					}
				});
			}

			function process_accomodation_invoice2() {

				$.ajax({
					url: "../auth_drop_adm_invoice_others_only.php",
					method: "POST",
					data: {
						generate_bed_invoice: true,
						hospital_no: "<?php echo $emr; ?>"
					},
					success: function(data) {
						///alert(data);
					}
				});
			}

			function process_accomodation_invoice3() {
				$.ajax({
					url: "../auth_drop_adm_invoice_tag_only.php",
					method: "POST",
					data: {
						generate_bed_invoice: true,
						hospital_no: "<?php echo $emr; ?>"
					},
					success: function(data) {
						///alert(data);
					}
				});
			}


			$(document).ready(function() {


				$.ajax({
					url: "billing.php",
					method: "POST",
					data: {
						hospital_no: "<?php echo $emr; ?>"
					},
					success: function(response) {



						if (response.status == 200) {
							//  $('#Total_total_debit').text(response.Total_total_debit);
							///  $('#Total_total_credits_').text(response.Total_total_credits);
							///document.getElementById("Total_total_credits_").text(response.Total_total_credits);
							/// $('#patient_seen_thismonth_count').text(response.patient_seen_thismonth_count);

						}
					},
					error: function(err) {
						console.log(err)
					}
				});
			})


			/////================================ BEGINING VALIDATION

			$(document).on('click', '.validate_package', function() {
				var validate_package_id = $(this).attr("id");
				if (validate_package_id != '') {
					$.ajax({
						url: "../validate_package.php",
						method: "POST",

						data: {
							validate_package_id: validate_package_id
						},

						success: function(data) {

							$('.modal-title').text('Validate Package');

							$('#validate_package_body').html(data);
							$('#validate_package_modal').modal('show');
						}
					});
				}
			});

			function un_validate_pckage_item(tag) {
				if (confirm("Are you sure you want to UNDO?")) {
					$.ajax({
						url: "../validate_package.php",
						method: "POST",
						data: {
							un_validate_: tag
						},
						success: function(data) {

							document.getElementById("undo_" + tag).disabled = true;
							document.getElementById("undo_" + tag).innerHTML = 'Done';
						},
						error: function(jqXHR, textStatus, errorThrown) {
							console.error("Error:", textStatus, errorThrown);
						}
					});
				}
			}

			function validate_pckage_item(tag) {
				if (confirm("Are you sure you want to proceed with this action?")) {
					$.ajax({
						url: "../validate_package.php",
						method: "POST",
						data: {
							validate_: tag
						},
						success: function(data) {
							document.getElementById("undo_" + tag).disabled = true;
							document.getElementById("undo_" + tag).innerHTML = 'Done';
						}
					});
				}
			}


			////========================================= VALIDATIONS




			function confirm_wallet() {

				var patient_emr = document.getElementById('patient_emr').value;
				var cash = document.getElementById('cash').value;
				var emr = document.getElementById('emr').value;

				$.ajax({
					url: "billing.php",
					method: "POST",
					data: {
						patient_emr: patient_emr,
						cash: cash,
						emr: emr
					},
					success: function(data) {

						///alert(data);

						var jsonn = JSON.parse(data);
						document.getElementById("name_wallet_benefact").innerHTML = jsonn["name"];
						document.getElementById("wallet_amount_").innerHTML = jsonn["wallet"];
						document.getElementById("wallet_amount_message").innerHTML = jsonn["message"];

						if (jsonn["status"] == 2) {
							///document.getElementById("confirm_wallet_continue").enabled = false;
							var button = document.getElementById("confirm_wallet_continue");
							button.disabled = true;
						} else if (jsonn["status"] == 1) {




							///document.getElementById("payee").value= patient_emr;
							document.getElementById("wallet_payeee").value = patient_emr;
							////

							/// 


						} else {
							toastr.error(jsonn["message"], 'Error', {
								timeOut: 5000
							})
						}



					}
				});

			}

			function cancel_invoiced(sn) {

				document.getElementById("cancel_invoiced_" + sn).value = "Wait..";

				$.ajax({
					url: "cancel_invoiced.php",
					method: "POST",
					data: {
						cancl_sn: sn
					},
					success: function(data) {

						var jsonn = JSON.parse(data);
						if (jsonn["status"] == 1) {

							toastr.success(jsonn["message"], 'Attention', {
								timeOut: 5000
							})
							// Assuming 'sn' is defined and holds the correct serial number
							var button = document.getElementById("cancel_invoiced_" + sn);
							button.value = "Cancelled";
							button.disabled = true;
						} else {
							toastr.error(jsonn["message"], 'Error', {
								timeOut: 5000

							})

							var button = document.getElementById("cancel_invoiced_" + sn);
							button.value = "Cancel";
							///button.disabled = true;
						}
					}
				});
			}

			function confirm_wallet_continue() {

				var confm = 'true';

				if (confm == 'true') {
					var rr = confirm("Pay From Another Patient Deposit Is NOT Reversable..... Are you sure you want to PROCESS?  ");
				} else {
					rr = true;
				}

				if (rr === true) {
					$('#pay_from_patient_wallet_modal').modal('hide');


				} else {


				}



			}
		</script>


		<script>
			<?php

			if ($error_status == 1) { ?>
				toastr.error('<?php echo $error_msg ?>', 'Error', {
					timeOut: 5000
				})

			<?php } else if ($error_status == 2) {
			?>
				toastr.success(' <?php echo $error_msg ?> ', 'Success', {
					timeOut: 5000
				})
			<?php

			}

			?>
		</script>

		<script src="../js/idle.js"></script>
		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

		<script>
			$('.dataTables-example').dataTable({
				responsive: true,
				"dom": 'T<"clear">lfrtip',
				"tableTools": {
					"sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
				}

			});
			$('#data_5 .input-daterange').datepicker({
				keyboardNavigation: false,
				forceParse: false,
				autoclose: true
			});
		</script>
</body>

</html>