<?php
include '../inc/constants.php';
$dept_id = $_SESSION['dept_id'];
$drug_reversal_period = $_SESSION['drug_reversal_period'];
$Current_date_ = date('Y-m-d H:i:s');
$date1_ = new DateTime($Current_date);


$Pharmacy = 'Pharmacy';
if ($_SESSION['dispensory'] == 0) {
	$patch_Dispens_query = "";
} else {
	$patch_Dispens_query = " AND dept_dispensory_id = :dept_dispensory_id"; // Use a placeholder for binding
}

$drug_status = '0';
$zero = 0;
$setdate = date('Y-m-d');




function percentage_markup_cal($purchase_cost, $units, $percentage_markup)
{

	$amt = $purchase_cost / $units;
	$markup_amount = ($amt * $percentage_markup) / 100;
	$selling_price = $amt + round($markup_amount, 3);

	if ($selling_price > 0) {
		$hosp_price = $selling_price;
		$cash_price = $selling_price;
	}

	return array('hosp_price' => $hosp_price, 'cash_price' => $cash_price);
}

if (isset($_POST['save_change'])) {
	// drug substitute
	$interest = $_POST['interest'];
	$insurance = $_POST['insurance_type'];
	$insurance_type = $_POST['insurance_type'];
	$payment_mode = $_POST['payment_mode'];
	$old_sn = $_POST['old_sn'];
	$insurance_no = $_POST['insurance_no'];
	$app_no = $_POST['app_no'];
	$new_precription = $_POST['new_precription'];

	$new_drug_name = $_POST['searchdrug'];
	$pp = explode('__', $new_drug_name);
	$new_drug_name = $pp[1];
	$stock_sn = $pp[0];
	$hosp_no = $_POST['hosp_no'];
	$new_qty = $_POST['qty'];
	$setdate = date('Y-m-d H:i:s');
	$invoice_status = 1;

	// / check for old drug details
	$qury_services = 'SELECT item_services, qty, remarks, process_claim,invoice_status,claim_amt,pay_mode FROM patient_ap_services WHERE sn = :sn';
	$stmt = $db->prepare($qury_services);
	$stmt->bindParam(':sn', $old_sn);
	$stmt->execute();
	$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
	$old_drug_name = $rwx['item_services'];

	$process_claim = $rwx['process_claim'];
	//$invoice_status = $rwx['invoice_status'];
	$claim_amt = $rwx['claim_amt'];
	$pay_mode = $rwx['pay_mode'];

	///if ($process_claim == 1 && $pay_mode == 'claim' && $claim_amt > 0) {
	//if ($pay_mode == 'claim' && $claim_amt > 0 && $_SESSION['col5'] == 1) {
	/// ZMKC has a special case where claim processing is not done for drug substitution, so we check for that before deciding to reset the claim processing status and invoice status. This is because in ZMKC, drug substitutions are handled differently and may not require reprocessing of claims, hence we want to preserve the original claim processing status and invoice status for ZMKC.
	//if (strtoupper($_SESSION['h_code']) == 'ZMKC') {

	//if($_SESSION['col5'] == 1) {  

	///}
	//$process_claim = 0;
	//$invoice_status = 1;
	//} else {
	//	$process_claim = 0;
	//	$invoice_status = 0;
	//}
	///}

	$remarks = '<strong>OLD Drug: </strong>' . $old_drug_name . ': ' . $rwx['remarks'] . '<br><strong>NEW: </strong> ' . $new_precription . ' by: ' . $_SESSION['fullname'];

	$stmt_dispensary = $db->prepare("SELECT bal FROM stock_table_inven 
	WHERE stock_sn = :stock_sn 
	AND cust_patient_id = :cust_patient_id 
	ORDER BY sn DESC LIMIT 1");
	$stmt_dispensary->execute([
		':stock_sn' => $stock_sn,
		':cust_patient_id' => $dept_id
	]);
	$rw_disp = $stmt_dispensary->fetch(PDO::FETCH_ASSOC);
	$bal = $rw_disp ? $rw_disp['bal'] : 0;

	if ($_SESSION['pharm_request_from_store'] == 1 && $bal <= 0) {
		$bal = 0;
	}

	$qry_drug = 'SELECT qty,hosp_price,cash_price FROM stock_table WHERE sn = :sn';
	$stmt = $db->prepare($qry_drug);
	$stmt->bindParam(':sn', $stock_sn);
	$stmt->execute();
	$row_drug = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($bal == 0 && $row_drug['qty'] > 0 && $_SESSION['pharm_request_from_store'] == 0) {
		$bal = $row_drug['qty'];
	}

	if ($row_drug['hosp_price'] > 0 &&  $row_drug['cash_price'] > 0) {
		if ($bal >= $new_qty && $new_qty > 0) {
			$qry_drug = 'SELECT * FROM stock_table WHERE sn = :sn AND qty >= :qty AND stock_table = :stock_table';
			$stmt = $db->prepare($qry_drug);
			$stmt->bindParam(':sn', $stock_sn);
			$stmt->bindParam(':qty', $new_qty);
			$stmt->bindValue(':stock_table', $Pharmacy);
			$stmt->execute();

			$row_drug = $stmt->fetch(PDO::FETCH_ASSOC);
			$drug_name = $row_drug['product_name'];
			$price_markup = $row_drug['price_markup'];
			$total_unit = $row_drug['stock_total_unit'];
			$buying_cost = $row_drug['buying_cost'];

			$nhis_price = $row_drug['nhis_price'];
			$hosp_price = $row_drug['hosp_price'];
			$ext_price = $row_drug['cash_price'];

			if ($price_markup > 0 && $buying_cost > 0) {
				$result = percentage_markup_cal($buying_cost, $total_unit, $price_markup);
				$hosp_price = $result['hosp_price'];
				$ext_price = $result['cash_price'];
			}

			$drug_sn = $row_drug['sn'];
			$stock_table = $row_drug['stock_table'];
			$coverage = $row_drug['coverage'];
			$part = explode('/', $coverage);
			$private = $part[0]; // This will always be set
			if (isset($part[1])) {
				$nhis = $part[1]; // This is set if the second part exists
			} else {
				$nhis = ''; // Default value if the second part is not present
			}
			$nhis = strtoupper($nhis);
			// /$insurance_type=$row_drug['insurance_type'];


			if ($_SESSION['dispensory'] == 1) {
				$qry_drug = 'SELECT hosp_price,nhis_price,cash_price FROM stock_table_dispensory WHERE stock_table_id = :sn';
				$stmt = $db->prepare($qry_drug);
				$stmt->bindParam(':sn', $stock_sn);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$row_drug = $stmt->fetch(PDO::FETCH_ASSOC);
					$hosp_price = $row_drug['hosp_price']; /// $new_qty;
					$cash_price = $row_drug['cash_price'];
					$nhis_price = $row_drug['nhis_price'];
					$ext_price  = $row_drug['cash_price'];
				}
			}

			$insurance = $insurance_type;
			$target_sn = $stock_sn;
			$NHIS_DRUG_CONSUMBL_STATE = 1;
			$_tariff_table = "hmo_stocks_tariff";

			include_once '../inc/price_calc.php';

			$amt_paying = $amt_paying * $new_qty;
			$claim_amt = $claim_amt * $new_qty;

			if ($amt_paying > 0 || $claim_amt > 0) {
				include_once '../inc/utilities.php';
				$invoice_no = INV();
				$updateSQL = 'UPDATE patient_ap_services 
							  SET access = :access,
								  drug_sn = :drug_sn,
								  item_services = :item_services,
								  hosp_price = :hosp_price,
								  claim_amt = :claim_amt,
								  qty = :qty,
								  invoice_status = :invoice_status,
								  invoice_no = :invoice_no,
								  process_claim = :process_claim,
								  invoice_date = :invoice_date,
								  invoice_by = :invoice_by,
								  pay = :pay,
								  date_entry = :date_entry,
								  pay_mode = :pay_mode,
								  remarks = :remarks,
								  tag = :tag
							  WHERE sn = :sn';

				$stmt = $db->prepare($updateSQL);
				$stmt->bindParam(':access', $insurance_type);
				$stmt->bindParam(':drug_sn', $drug_sn);
				$stmt->bindParam(':item_services', $drug_name);
				$stmt->bindParam(':hosp_price', $row_drug['hosp_price']);
				$stmt->bindParam(':claim_amt', $claim_amt);
				$stmt->bindParam(':qty', $new_qty);
				$stmt->bindValue(':invoice_status', $invoice_status);
				$stmt->bindParam(':invoice_no', $invoice_no);
				$stmt->bindParam(':process_claim', $process_claim);
				$stmt->bindParam(':invoice_date', $setdate);
				$stmt->bindParam(':invoice_by', $_SESSION['fullname']);
				$stmt->bindParam(':pay', $amt_paying);
				$stmt->bindParam(':date_entry', $setdate);
				$stmt->bindParam(':pay_mode', $pay_mode);
				$stmt->bindParam(':remarks', $remarks);
				$stmt->bindValue(':tag', $one);
				$stmt->bindParam(':sn', $old_sn);

				$stmt->execute();

				$error_status = 2;
				$error_msg = 'Saved and Added to Pharmacy Managment Section Below ... ';
			} else {
				$error_status = 1;
				$error_msg = 'Invalid Amount Invoiced for Selected Drug!';
			}
		} else {
			$error_status = 1;
			$error_msg = 'Invalid Stock Quantity';
		}
	} else {
		$error_status = 1;
		$error_msg = 'Invalid! No Price Assign to Selected Drug.';
	}
}

if (isset($_POST['add_all_drugs'])) {
	$hosp_no = $_POST['hospital_no'];
	$pro_inv = $_REQUEST['inv_all'];

	if (!empty($pro_inv)) {
		for ($i = 0; $i < count($pro_inv); ++$i) {
			$inv_id = $pro_inv[$i];

			$updated_price = 0;
			$break = explode('__', $inv_id);
			$drug_sn = $break[0];
			$sale_sn = $break[5];
			$qty = $_POST['qty_' . $sale_sn];
			$edit_price = $_POST['edit_price_' . $sale_sn];
			$pay = str_replace(',', '', $break[2]);
			$claim_amt = $break[3];

			$hmo = $break[6];
			$hmo_type = $break[7];
			$interest = $break[8];
			$insurance_type = $break[10];
			$dept_dispensory_id = $break[11];
			$stock_total_unit = $break[12];
			$price_markup = str_replace(',', '', $break[13]);
			$hosp_price = str_replace(',', '', $break[14]);
			$buying_cost = str_replace(',', '', $break[15]);
			$edit_price = str_replace(',', '', $edit_price);

			if ($price_markup > 0 && $buying_cost > 0 && $claim_amt == 0) {
				$result = percentage_markup_cal($buying_cost, $stock_total_unit, $price_markup);
				$pay = $result['cash_price'];
			}


			if ($edit_price > 0 && $pay != $edit_price && $pay > 0 && $_SESSION['edit_price_point_sale'] == 1) {
				$pay = $edit_price;
				$updated_price = $edit_price;
			}

			if ($edit_price > 0 and $pay != $edit_price and $claim_amt > 0 && $_SESSION['edit_price_point_sale'] == 1) {
				$claim_amt = $edit_price;
			}


			$qury_price = 'SELECT price FROM hmo_stocks_tariff WHERE stock_sn = :drug_sn AND price > 0 AND (hmo = :hmo OR hmo = :hmo_type)';
			$stmt = $db->prepare($qury_price);
			$stmt->bindParam(':drug_sn', $drug_sn);
			$stmt->bindParam(':hmo', $hmo);
			$stmt->bindParam(':hmo_type', $hmo_type);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$row_dx = $stmt->fetch(PDO::FETCH_ASSOC);
				$claim_amt = $row_dx['price'];
			}
			$new_pay = $pay * $qty;
			$new_claim_amt = $claim_amt * $qty;
			$new_interest = $interest * $qty;

			if ($qty > 0 && ($pay > 0 or $new_claim_amt > 0)) {
				include_once '../inc/utilities.php';
				$invoice_no = INV();
				$setdate = date('Y-m-d H:i:s');
				$updateSQL = 'UPDATE patient_ap_services 
				  SET claim_amt = :claim_amt,
					  interest = :interest,
					  qty = :qty,
					  invoice_status = :invoice_status,
					  invoice_no = :invoice_no,
					  invoice_date = :invoice_date,
					  invoice_by = :invoice_by,
					  pay = :pay
				  WHERE sn = :sn AND hospital_no = :hospital_no';

				$stmt = $db->prepare($updateSQL);
				$stmt->bindParam(':claim_amt', $new_claim_amt);
				$stmt->bindParam(':interest', $new_interest);
				$stmt->bindParam(':qty', $qty);
				$stmt->bindValue(':invoice_status', $one);
				$stmt->bindParam(':invoice_no', $invoice_no);
				$stmt->bindParam(':invoice_date', $setdate);
				$stmt->bindParam(':invoice_by', $_SESSION['fullname']);
				$stmt->bindParam(':pay', $new_pay);
				$stmt->bindParam(':sn', $sale_sn);
				$stmt->bindParam(':hospital_no', $hosp_no);
				$stmt->execute();


				if ($updated_price > 0 && $_SESSION['dispensory'] == 0 && $_SESSION['edit_price_point_sale'] == 1) {
					$updateSQL = "UPDATE stock_table SET hosp_price = :hosp_price, cash_price = :edit_price, ml_1sheet = '1' WHERE sn = :drug_sn";
					$stmt = $db->prepare($updateSQL);
					$stmt->bindParam(':hosp_price', $updated_price);
					$stmt->bindParam(':edit_price', $updated_price);
					$stmt->bindParam(':drug_sn', $drug_sn);
					$stmt->execute();
				} elseif ($edit_price > 0 && $_SESSION['dispensory'] == 1  && $_SESSION['edit_price_point_sale'] == 1) {
					$updateSQL = "UPDATE stock_table_dispensory SET hosp_price = :hosp_price, cash_price = :edit_price WHERE stock_table_id = :stock_table_id";
					$stmt = $db->prepare($updateSQL);
					$stmt->bindParam(':hosp_price', $updated_price);
					$stmt->bindParam(':edit_price', $updated_price);
					$stmt->bindParam(':stock_table_id', $drug_sn);
					$stmt->execute();
				}
			} else {
				$error_status = 1;
				$error_msg = 'Invalid Amount Invoiced.';
			}
		}
	}
	$error_status = 2;
	$error_msg = 'Selected Drug(s) Processed .. Check Pharmacy Managment Section Below!';
}

if (isset($_POST['ph_add_drug'])) {
	// drug substitute

	$interest = $_POST['interest'];
	$insurance = $_POST['insurance_type'];
	$new_precription = isset($_POST['new_precription']) ? trim($_POST['new_precription']) : '';
	$remarks = !empty($new_precription) ? $new_precription : 'pharmacist';
	$insurance_no = $_POST['insurance_no'];
	$insurance_type = $_POST['insurance_type'];
	$payment_mode = $_POST['payment_mode'];
	$new_drug_name = $_POST['searchdrug'];
	$pp = explode('__', $new_drug_name);
	$new_drug_name = $pp[1];
	$stock_sn = $pp[0];
	$hospital_no = $_POST['hosp_no'];
	$new_qty = $_POST['qty'];
	$setdate = date('Y-m-d H:i:s');
	$appt_no = $_POST['app_no'];


	$stmt_dispensary = $db->prepare("SELECT bal FROM stock_table_inven 
	WHERE stock_sn = :stock_sn 
	AND cust_patient_id = :cust_patient_id 
	ORDER BY sn DESC LIMIT 1");
	$stmt_dispensary->execute([
		':stock_sn' => $stock_sn,
		':cust_patient_id' => $dept_id
	]);
	$rw_disp = $stmt_dispensary->fetch(PDO::FETCH_ASSOC);
	$bal = $rw_disp ? $rw_disp['bal'] : 0;

	if ($_SESSION['pharm_request_from_store'] == 1 && $bal <= 0) {
		$bal = 0;
	}

	$qry_drug = 'SELECT * FROM stock_table WHERE sn = :sn';
	$stmt = $db->prepare($qry_drug);
	$stmt->bindParam(':sn', $stock_sn);
	$stmt->execute();
	$row_drug = $stmt->fetch(PDO::FETCH_ASSOC);
	$price_markup = $row_drug['price_markup'];
	$total_unit = $row_drug['stock_total_unit'];
	$buying_cost = $row_drug['buying_cost'];


	if ($bal == 0 && $row_drug['qty'] > 0 && $_SESSION['pharm_request_from_store'] == 0) {
		$bal = $row_drug['qty'];
	}

	if ($row_drug['hosp_price'] > 0 && $row_drug['cash_price'] > 0 && $new_qty > 0) {
		if ($bal >= $new_qty) {
			// Assigning values to variables

			$item_service = $row_drug['product_name'];
			$nhis_price = $row_drug['nhis_price'];
			$hosp_price = $row_drug['hosp_price'];
			$cash_price = $row_drug['cash_price'];
			$ext_price = $row_drug['cash_price'];

			if ($price_markup > 0 && $buying_cost > 0) {
				$result = percentage_markup_cal($buying_cost, $total_unit, $price_markup);
				$hosp_price = $result['hosp_price'];
				$cash_price = $result['cash_price'];
				$ext_price = $result['cash_price'];
			}

			$nhis_price = $nhis_price;
			$hosp_price = $hosp_price;
			$cash_price = $cash_price;
			$ext_price = $ext_price;
			$item_sn = $row_drug['sn'];
			$price_table = $row_drug['stock_table'];
			$coverage = $row_drug['coverage'];
			$part = explode('/', $coverage);
			$private = $part[0]; // This will always be set

			if (isset($part[1])) {
				$nhis = $part[1]; // This is set if the second part exists
			} else {
				$nhis = ''; // Default value if the second part is not present
			}


			if ($_SESSION['dispensory'] == 1) {
				$qry_drug = 'SELECT hosp_price, nhis_price, cash_price 
							 FROM stock_table_dispensory 
							 WHERE stock_table_id = :sn';

				$stmt = $db->prepare($qry_drug);
				$stmt->bindParam(':sn', $stock_sn, PDO::PARAM_STR);
				$stmt->execute();

				$row_drug = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($row_drug) {
					$nhis_price  = $row_drug['nhis_price'];
					$hosp_price  = $row_drug['hosp_price'];
					$cash_price  = $row_drug['cash_price'];
					$ext_price  = $row_drug['cash_price'];
				}
			}

			$hosp_price_ap = $row_drug['hosp_price'];
			$category = 'Pharmacy';


			$insurance = $insurance_type;
			$target_sn = $stock_sn;
			$NHIS_DRUG_CONSUMBL_STATE = 1;
			$_tariff_table = "hmo_stocks_tariff";

			include_once '../inc/price_calc.php';

			include '../inc/utilities.php';
			$invoice_no = INV();

			$qty = $new_qty;
			$invoice_status = '1';
			$invoicedate = date('Y-m-d H:i:s');
			$invoice_by = $_SESSION['fullname'];
			$paystatus = '0';
			$cr = '0';
			$hosp_price = $hosp_price_ap;

			$amt_paying = $amt_paying * $new_qty;
			$claim_amt = $claim_amt * $new_qty;

			$sp_remarks = null;
			include '../inc/patient_ap_services.php';

			///==========================================
			header("location:index.php?presc&hos_no=$hospital_no");
		} else {
			$error_status = 1;
			$error_msg = 'Invalid Stock Quantity';
		}
	} else {
		$error_status = 1;
		$error_msg = 'Invalid! No Amount Invoiced for Selected Drug.';
	}
}

if (isset($_GET['hos_no']) or isset($_POST['in_patient']) or isset($_GET['chg'])) {
	if (isset($_GET['hos_no'])) {
		$hos_no = $_GET['hos_no'];
	} elseif (isset($_GET['chg'])) {
		list($sale_sn, $hos_no) = explode('/', $_GET['chg']);
	} else {
		$hos_no = $_POST['in_patient'];
	}

	$emr = $hos_no;
	require_once 'visit_status.php';
	$status = getPatientStatusInfo($db, $hos_no, $_SESSION);

	// Display status
	$patient_type = $status['patient_type'];
	// Access other values if needed:
	$back_date = $status['back_date'];
	$app_no = $status['app_no'];
	$location_dept = $status['location_dept'];


	$general_credit_limit =	$_SESSION['credit_limit_status'];
	$items = call_current_balance($db, $emr, $general_credit_limit);

	$current_balance =  $items["current_balance"];
	$patient_name      = $items['patient_name'];
	$nhis_no           = $items['nhis_no'];
	$names             = $items['names'];
	$surname           = $items['surname'];
	$fname             = $items['fname'];
	$oname             = $items['oname'];
	$age             = $items['age'];
	$gender            = $items['gender'];
	$address           = $items['address'];
	$referral_name     = $items['referral_name'];
	$discount_set      = $items['discount_set'];
	$phone             = $items['phone'];
	$email             = $items['email'];
	$insurance_type = $items['insurance_type'];
	$insurance_no      = $items['insurance_no'];
	$save_insurance_no = $items['save_insurance_no'];
	$wallet_amount     = $items['wallet_amount'];
	$wallet_account          = $items['wallet_account'];
	$bal_credit_limit  = $items['bal_credit_limit'];
	$credit_limit2  = $items['credit_limit'];
	$credit_limit  = 	$items['bal_credit_limit'];
	$add_minus         = $items['add_minus'];
	$interest          = $items['interest'];
	///$patient_type      = $items['patient_type'];
	$addr              = $items['addr'];
	$payment_mode      = $items['payment_mode'];
	$insur_title       = $items['insur_title'];
	$insurance_name       = $items['insurance_name'];
	$entitled_to       = $items['entitled_to'];
	$Total_total_credits       = $items['Total_total_credits'];
	$vip               = $items['vip'];
	$group               = $items['group'];
	$label             = $items['credit_type']; // Note: original key was 'credit_type', mapped to $label



} else {
	header("location:index.php");
}


if ($vip == 1) {
	$vip_status = 0;
	// Allow if user is MD or found in VIP staff table
	if ($_SESSION['rights'] === 'MD') {
		$vip = 0;
		$vip_status = 1;
	} else {
		$stmt = $db->prepare("SELECT 1 FROM manage_patients_vip_staff WHERE hospital_no = ? AND user_id = ? AND status = 1 LIMIT 1");
		$stmt->execute([$hos_no, $_SESSION['id']]);

		if ($stmt->fetchColumn()) {
			$vip = 0;
			$vip_status = 1;
		}
	}
}

if (isset($_GET['dl'])) {
	$sn = $_GET['dl'];
	$qry_drug = $db->prepare('DELETE FROM patient_ap_services WHERE sn = :sn AND paystatus = 0');
	$qry_drug->bindParam(':sn', $sn);
	$qry_drug->execute();
}

if (isset($_GET['cl'])) {
	$sn = $_GET['cl'];

	// Fetch current values from the database
	$stmt = $db->prepare('SELECT qty, drug_sn, claim_amt, pay, invoice_status FROM patient_ap_services WHERE sn = :sn');
	$stmt->bindParam(':sn', $sn);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		$row_me = $stmt->fetch(PDO::FETCH_ASSOC);

		// Only proceed if invoice_status is not already 0 (update not yet done)
		if ($row_me['invoice_status'] != '0') {

			// Update the record just once
			$updateSQL = $db->prepare('UPDATE patient_ap_services 
                SET invoice_status = :invoice_status WHERE sn = :sn');
			$invoice_status = '0';
			$updateSQL->bindParam(':invoice_status', $invoice_status);
			$updateSQL->bindParam(':sn', $sn);
			$updateSQL->execute();
		}
	}
	header("location:index.php?presc&hos_no=$hos_no");
}


if (isset($_POST['apply'])) {
	$hos_no = $_POST['hos_no'];
	$view_list = $_POST['view_list'];

	$from_date = $_POST['from_date'];
	$to_date = $_POST['to_date'];
	header("location:index.php?presc&hos_no=$hos_no&dd=$from_date/$to_date&$view_list");
}

if (isset($_GET['dd'])) {
	$part = explode('/', $_GET['dd']);
	$from_date = $part[0];
	$to_date = $part[1];
} else {
	///$from_date = $back_date;
	$from_date = date('Y-m-d');
	$to_date = date('Y-m-d');
}




// drug reminder submitted
if (isset($_POST['drug_sn_rem'])) {
	$drug_sn = $_POST['drug_sn_rem'];
	$hos_no = $_POST['hos_no_rem'];
	$reminder_date = $_POST['reminder_date'];
	$days_before_reminder = $_POST['days_before_reminder'];

	$stmt = $db->prepare("INSERT INTO pharm_drug_reminder (drug_sn, hos_no, reminder_date, days_before_reminder, status) VALUES (:drug_sn, :hos_no, :reminder_date, :days_before_reminder, 'enabled')");
	$stmt->bindParam(':drug_sn', $drug_sn);
	$stmt->bindParam(':hos_no', $hos_no);
	$stmt->bindParam(':reminder_date', $reminder_date);
	$stmt->bindParam(':days_before_reminder', $days_before_reminder);
	$stmt->execute();

	header("Location: index.php?presc&hos_no=$hos_no&sv=1");
}


?>

<div class="row">

	<div class="col-lg-7">
		<div class="ibox float-e-margins">

			<table>
				<tr>
					<td>
						<form method="post" action="index.php?<?php echo 'presc&hos_no=' . $hos_no; ?>">
							<button class="btn btn-success btn-xs" type="submit" name="skip_popup_2" id="skip_popup_2"
								value="<?php echo $hos_no; ?>" style="font-size: 15px; color:white;">Auto.</button>
						</form>
					</td>
					<td>

						&nbsp;<a href="index.php?presc&hos_no=<?php echo $hos_no . '&dsp'; ?>" class="btn btn-success btn-xs" style="font-size: 15px; color:white; ">Dispensed</a> &nbsp;
						<input type="button" name="" value="Biodata" data-target="#modal" id="<?php echo $hos_no; ?>" class="btn btn-primary btn-xs bio_data_link" style="font-size: 15px; color:white; " /> &nbsp;
						<?php if ($vip == 0) { ?>
							<input type="button" name="on_cr" value="Plan" data-target="#modal" id="<?php echo $hos_no; ?>" class="btn btn-info btn-xs plan" style="font-size: 15px; color:white; " /> &nbsp;
						<?php } ?>
						<?php if ($_SESSION['view_consult_notes'] == 1 && $vip == 0) { ?>
							<input type="button" name="on_cr" value="Consult Notes &nbsp;" data-target="#modal" id="<?php echo $hos_no; ?>" class="btn btn-success btn-xs encounters_form" style="font-size: 15px; color:white; " />&nbsp;
							<input type="button" name="on_lab" value="Test Results &nbsp;" data-target="#modal" id="<?php echo $hos_no; ?>" class="btn btn-warning btn-xs lab_res_modal_form" style="font-size: 15px; color:white; " />&nbsp;
						<?php } ?>
						<a href="index.php?presc&hos_no=<?php echo $hos_no . '&hx'; ?>" class="btn btn-primary btn-xs" style="font-size: 15px; color:white; ">Drug Hx&nbsp;</a> &nbsp;
						<input type="button" name="edit_users" value="Rx Notes" data-target="#modal" id="<?php echo $hos_no; ?>"
							class="btn btn-warning btn-xs document_note" style="font-size: 15px; color:white; " />&nbsp;
						<a href="#" type="submit" class="btn btn-primary btn-xs" style="font-size: 15px; color:white; " data-toggle="modal" data-target="#pharm_clinical_modal" style="color: white; font-size: 14px; ">Clinical</a>

					</td>
				</tr>
			</table>





			<div class="ibox-content">

				<table width="100%">
					<tr>
						<td>
							<div style="font-size:14px;">
								|&nbsp; <?php echo '<strong># ' . $hos_no . '</strong>'; ?>
								|&nbsp; <?php echo '<strong>' . $names . '</strong>';
										if ($vip_status == 1) {
											echo '<b style="color:blue;"> [ VIP PATIENT ]</b>';
										} ?><br>
								|&nbsp; <?php echo '<strong>DOB: ' . $dob . ' [ Age: ' . $age . ' ] </strong>'; ?>
								|&nbsp; <?php echo '<strong>B/G: ' . $blood_g . 'G/Type: ' . $geno_type . '</strong>'; ?><br>
								|&nbsp;<strong style="color: saddlebrown;">Insurance's status: </strong><?php echo ' <i><b>' . $insurance_name . '[' . $insurance_type . ']</b></i>'; ?>
							</div>
						</td>
						<td>
							<div style="font-size:14px;">
								<?php
								$stmt = $db->prepare("SELECT complain FROM c_d_remarks WHERE hospital_no = :hos_no AND cat_type = 'DH' ORDER BY app_no, sn DESC");
								$stmt->bindParam(':hos_no', $hos_no);
								$stmt->execute();

								$complaints = $stmt->fetchAll(PDO::FETCH_COLUMN);

								if (!empty($complaints)) {
									$allergies = htmlspecialchars(implode(' : ', $complaints));
									echo '<strong style="color: red;">Allergies:</strong><br>' . $allergies;
								} else {
									echo '<strong>No Allergies</strong>';
								}
								?>
							</div>
						</td>
						<td>
					</tr>
				</table>

			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="ibox float-e-margins">
			&nbsp;
			<a href="../admission_billing.php?emr=<?= $hos_no; ?>" class="btn btn-info btn-xs" style="font-size: 15px; color:white; " style="color: white; font-size: 14px; ">Daily Invoice</a>
			&nbsp;
			<a href="#" onclick="get_ex_patients()" class="btn btn-success btn-xs" style="font-size: 15px; color:white;">Search EX</a>
			&nbsp;
			<a href="#" onclick="myFunction_inPatientBtn()" class="btn btn-primary btn-xs" style="font-size: 15px; color:white;">In-Patients</a>
			&nbsp;
			<a href="index.php?presc&hos_no=<?php echo $hos_no; ?>" class="btn btn-default btn-xs" style="font-size: 15px; color:black; ">Refresh Page</a>


			&nbsp;
			<a href="index.php" class="btn btn-danger btn-xs" style="font-size: 15px; color:white; ">Close</a>
			<div class="ibox-content">

				<form action="index.php" method="POST">
					<div class="">
						<table width="100%">
							<tr>

								<td>
									<label for="" class="" style="color: red; ">QUICK SEARCH DRUG REQUEST(S):</label>
									<select class="chosen-select" class="form-control" name="requesters_drugs" id="requesters_drugs" required>
										<option value="">-- Select --</option>
									</select>
								</td>
								<td>&nbsp;</td>
								<td>
									<label for="" class="">.</label><br>
									<button class="btn btn-success btn btn-sm" type="submit" name="apply_requester">Apply</button>
								</td>
							</tr>

						</table>
					</div>
				</form>

			</div>
		</div>
	</div>

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<div>
					<table width="100%" style="font-size: 16px;">
						<tr>
							<td align="left" width="50%"><b>Doctor/Nurse Request, Prescriptions & Plans</b></td>
							<td align="right" width="50%"><b><?= $patient_type; ?></b></td>
						</tr>
					</table>
				</div>
			</div>

			<div class="ibox-content">
				<?php

				$allowed = ['dsp', 'dcr', 'hx', 'bio'];
				$details = '';

				foreach ($allowed as $key) {
					if (isset($_GET[$key])) {
						$details = $key;
						break;
					}
				}


				?>

				<form action="presc.php" method="POST">
					<table>
						<tr>
							<td style="padding-left:5px; padding-bottom:12px;   "><b>Show Detail By Dates:</b></td>
							<td>


								<div class="form-group" id="">
									<div class="input-daterange input-group" id="datepicker">
										<input type="date" class="form-control" name="from_date" value="<?php echo $from_date; ?>" />
										<span class="input-group-addon">to</span>
										<input type="date" class="form-control" name="to_date" value="<?php echo $to_date; ?>" />
									</div>
								</div>
							</td>
							<td style="padding-left:5px; padding-bottom:12px;   ">
								<button class="btn btn-success btn btn-sm" type="submit" name="apply" id="apply">Apply</button>
								<input type="hidden" name="MM_update" value="pdetail" />
								<input type="hidden" name="hos_no" value="<?php echo $hos_no; ?>" />
								<input type="hidden" name="view_list" value="<?php echo $details; ?>" />
							</td>
						</tr>
					</table>
				</form>

				<?php
				if (isset($_GET['RQ'])) {
					$prepared_by = base64_decode($_GET['RQ']);
					$Search_prepared_by = " prepared_by = '$prepared_by' and ";

					$day = 1;
					$from_date = date('Y-m-d', strtotime("-$day days"));
					$to_date = date('Y-m-d');
				} else {
					$Search_prepared_by = '';
				}

				if (!isset($_GET['dsp']) && !isset($_GET['hx']) && !isset($_GET['paid']) && !isset($_GET['unpaid'])) {

					$from_date = $from_date . ' 00:00:00'; // Start of the day
					$to_date = $to_date . ' 23:59:59'; // End of the day

					$stmt = $db->prepare('SELECT 
                        p.serv_group,
                        p.remarks,
                        p.pay,
                        p.claim_amt,
                        p.date_entry,
                        p.tag,
                        p.interest,
                        p.dept_dispensory_id,
    					p.dispensory_status_at_phamcy,
                        p.pay_mode,
                        p.process_claim,
                        p.prescription,
                        p.med_duration_unit,
                        p.prepared_by,
                        p.created_by,
                        p.qty as p_qty,
                        stock_table.expire_date,
                        stock_table.qty,
                        stock_table.product_name,
                        stock_table.dosage,
                        stock_table.strength,
                        stock_table.sn,
                        stock_table.hosp_price,
                        stock_table.price_markup,
                        stock_table.stock_total_unit,
                        stock_table.buying_cost,
                        p.sn as sale_sn 
                    FROM 
                        patient_ap_services as p 
                    INNER JOIN 
                        stock_table ON p.drug_sn = stock_table.sn 
                    WHERE 
                        hospital_no = :hos_no 
                        AND invoice_status = :invoice_status 
                        ' . $patch_Dispens_query . '
                        AND drug_status = :drug_status 
                        AND serv_group = :serv_group 
                        AND date_entry BETWEEN :from_date AND :to_date 
                    ORDER BY 
                        date_entry DESC');

					// Bind parameters
					$stmt->bindParam(':hos_no', $hos_no);
					$stmt->bindParam(':invoice_status', $zero);
					if ($_SESSION['dispensory'] != 0) {
						$stmt->bindParam(':dept_dispensory_id', $dept_id); // Bind only if dispensary is not 0
					}
					$stmt->bindParam(':drug_status', $drug_status);
					$stmt->bindParam(':serv_group', $Pharmacy);
					$stmt->bindParam(':from_date', $from_date);
					$stmt->bindParam(':to_date', $to_date);
					$stmt->execute();

					if ($stmt->rowCount() > 0) { ?>

						<form action="index.php?presc&hos_no=<?= $hos_no; ?>#drug-section" method="POST">

							<div style=" max-height:500px; overflow:auto">
								<?php ob_start(); ?>
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th width="2%">#</th>
											<th width="10%">Refill Reminder</th>
											<th width="17%">Drug</th>
											<th width="30%">Prescription</th>
											<?php if ($_SESSION['edit_price_point_sale'] == 1 and $insurance_type != 'NHIS') { ?>
												<th width="10%" style="color:red;">Edit Price (1 Unit)</th>
											<?php } ?>
											<th>Pay</th>
											<th>Claim</th>
											<th width="7%" style="color: red;">Enter Qty</th>
											<th width="10%">Sub Total</th>

											<th width="10%">.</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$nn = 1;


										while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

											$disabled = null;
											$Dispensory = null;
											$val = null;
											if ($row['dispensory_status_at_phamcy'] == 1) {
												$departmentsArray = getAllDepartments($db); // Fetch all departments
												foreach ($departmentsArray as $department) {
													if ($department['sn'] == $row['dept_dispensory_id']) {
														$Dispensory = '<br><span style="color:blue;">RQ Unit: <br></span>' . $department['department'];
														break;
													}
												}
												///$Dispensory = '<br><span style="color:blue;">RQ Dispense</span>';  /// RQ REQUISITION DISPENSE
											} elseif ($row['dept_dispensory_id'] != $dept_id) {

												$departmentsArray = getAllDepartments($db); // Fetch all departments
												foreach ($departmentsArray as $department) {
													if ($department['sn'] == $row['dept_dispensory_id']) {
														$Dispensory = '<br><span style="color:blue;"> Dispensary: </span>' . $department['department'];
														break;
													}
												}
											} else {
												$Dispensory = null;
											}
											$drug_sn = $row['sn'];
											$prescribe_qty = $row['p_qty'];
											$stock_qty = $row['qty'];

											// Backdate to 2 days before the current date
											$current_date = new DateTime();
											$backdated_date = new DateTime();
											$backdated_date->modify('-2 days');

											$current_date_str = $current_date->format('Y-m-d') . ' 00:00:00';;
											$backdated_date_str = $backdated_date->format('Y-m-d') . ' 23:59:59';

											$stmtcc = $db->prepare('SELECT SUM(qty) as total_qty_invoiced FROM patient_ap_services 
												WHERE invoice_status = 1
												AND drug_status = 0 
												AND paystatus = 0 
												' . $patch_Dispens_query . '
												AND drug_sn = :drug_sn 
												AND dept_dispensory_id = :dept_dispensory_id 
												AND hospital_no != :hos_no 
												AND date_entry BETWEEN :backdated_date AND :current_date');

											$stmtcc->bindParam(':drug_sn', $drug_sn, PDO::PARAM_STR);
											$stmtcc->bindParam(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
											$stmtcc->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
											$stmtcc->bindParam(':backdated_date', $backdated_date_str, PDO::PARAM_STR);
											$stmtcc->bindParam(':current_date', $current_date_str, PDO::PARAM_STR);

											$stmtcc->execute();

											$rowcc = $stmtcc->fetch(PDO::FETCH_ASSOC);
											$total_qty_invoiced = $rowcc ? $rowcc['total_qty_invoiced'] : 0;
											$invoiced_qty = ($total_qty_invoiced > 0) ? '<br>Existing Invoiced Qty: ' . $total_qty_invoiced : null;

											$stmt_dispensary = $db->prepare("SELECT bal FROM stock_table_inven 
											WHERE stock_sn = :stock_sn 
											AND cust_patient_id = :cust_patient_id 
											ORDER BY sn DESC LIMIT 1");
											$stmt_dispensary->execute([
												':stock_sn' => $drug_sn,
												':cust_patient_id' => $dept_id
											]);
											$rw_disp = $stmt_dispensary->fetch(PDO::FETCH_ASSOC);
											$qty = $rw_disp ? $rw_disp['bal'] : 0;

											if ($qty == 0 && $stock_qty > 0 && $row['dept_dispensory_id'] == $dept_id) {
												$qty = $stock_qty;
											}

											$pay = $row['pay'];
											///echo '==' . $_SESSION['col5'] . '==';
											$val_mgs = null;
											$new_request = null;
											if ($total_qty_invoiced > 0 and $total_qty_invoiced >= $qty) {
												$val = 1;
												$disabled = 'disabled';
												$new_request = '<b>[Running low: drug invoiced for already for others, pending payment.]</b>';
											} elseif ($qty <= 0) {
												$val = 1;
												$disabled = '';
												$new_request = '<b>[INSUFFICIENT QTY]</b>';
											}

											if ($total_qty_invoiced < $qty) {
												$qty = $qty - $total_qty_invoiced;
											} else {
											}

											if ($row['pay_mode'] == 'claim' and $row['process_claim'] == '0' and $row['pay'] == '0' && $_SESSION['col5'] == 0) {
												$val = 1;
												$disabled = 'disabled';
												$val_mgs = '<b>[Validation Pending]</b>';
											}										?>
											<tr>

												<td>
													<?php
													$main_pay = $row['pay'];
													$main_claim_amt = $row['claim_amt'];

													if ($prescribe_qty > 1) {
														$main_pay = $main_pay / $prescribe_qty;
														$main_claim_amt = $main_claim_amt / $prescribe_qty;
													}

													// / echo $row['sn'] . '__' . $row['product_name']. '__' . $row['pay']. '__' . $row['claim_amt']. '__' . $row['qty']. '__'. $row['sale_sn'].'__'. $insurance_no .'__'. $insurance. '__' . $row['interest'];
													?>
													<input type="checkbox" <?php echo $disabled; ?> onClick="UpdateCost()"
														value="<?php echo $row['sn'] . '__' . $row['product_name'] . '__' . $main_pay . '__' . $main_claim_amt . '__' . $row['qty'] . '__' . $row['sale_sn'] . '__' . $insurance_no . '__' . $insurance . '__' . $row['interest'] . '__' . $row['remarks'] . '__' . $insurance_type . '__' . $row['dept_dispensory_id'] . '__' . $row['stock_total_unit'] . '__' . $row['price_markup'] . '__' . $row['hosp_price'] . '__' . $row['buying_cost'] . '__' . $row['prepared_by']; ?>" name="inv_all[]" id="add_m_<?php echo $nn; ?>" />
												</td>

												<td>
													<button type="button" class="btn btn-warning btn-xs"
														onclick="setReminderModalOpen('<?php echo $row['sn']; ?>', '<?php echo $hos_no; ?>')">
														Set Reminder
													</button>
												</td>


												<td><?php echo $nn . '-' . $row['product_name'];
													displayRemainingDays($row['expire_date'], $row['sn']); ?>
													<?php


													// Current date and time
													if ($new_request == null && $val_mgs == null) {
														$setdate = date('Y-m-d H:i:s');
														$currentTime = strtotime($setdate);
														$entryTime   = strtotime($row['date_entry']);

														// Get the difference in seconds
														$diffInSeconds = abs($currentTime - $entryTime);

														// If difference is 5 minutes (300 seconds) or less
														if ($diffInSeconds <= 300) {
															$new_request = '<b>[New Request]</b>';
														}
													}

													echo '<small style="color: red;">' . $new_request . $val_mgs . $invoiced_qty . '</small>';
													echo $Dispensory;
													?>
												</td>
												<td><?php
													if ($row['remarks'] != '') {
														echo $row['remarks'] . ' <strong>Remarks: </strong>' . $row['prescription'];
													}
													echo '<br><strong>Requested by/Date: </strong>' . $row['prepared_by'] . ' / ' . date('d M,y h:ia', strtotime($row['date_entry']));
													?></td>
												<?php if ($_SESSION['edit_price_point_sale'] == 1 and $insurance_type != 'NHIS') { ?>
													<td>

														<input type="number"
															id="edit_price_<?php echo $nn; ?>"
															name="edit_price_<?php echo $row['sale_sn']; ?>"
															onkeyup="UpdateCost()"
															class="form-control"
															min="1"
															step="0.01"
															value="<?php echo round($main_pay, 2); ?>" />

													</td>
												<?php } else { ?>
													<input type="hidden" id="edit_price_<?php echo $nn; ?>" name="edit_price_<?php echo $row['sale_sn']; ?>" value="<?php echo number_format($main_pay, 2); ?>" />
												<?php } ?>
												<td><?php echo number_format($row['pay'], 2); ?></td>
												<td><?php echo number_format($row['claim_amt'], 2); ?></td>
												<td>
													<?php
													$prescribe_qty = ($prescribe_qty > 1) ? $prescribe_qty : 1;
													$qty = ($qty == 0) ? 1 : $qty;
													?>
													<input type="number"
														id="qty_<?php echo $nn; ?>"
														name="qty_<?php echo $row['sale_sn']; ?>"
														class="form-control"
														onkeyup="UpdateCost()"
														min="1"
														value="<?php echo $prescribe_qty; ?>"
														max="<?php echo $qty; ?>" />

												</td>
												<td>

													<input type="text" class="input-sm form-control" id="total_<?php echo $nn; ?>" name="total_<?php echo $snn; ?>"
														value="" readonly style=" width:90%;" />

												</td>

												<td>

													<input type="button" name="notes" value="Chat" data-target="#modal" id="<?php echo $row['sale_sn'] . '__' . $row['product_name']; ?>" class="btn btn-success btn-xs view_notes" />
													&nbsp;|&nbsp;
													<?php if ($row['created_by'] == $_SESSION['id']) { ?>
														<a href="index.php?presc&<?php echo 'dl=' . $row['sale_sn'] . '&hos_no=' . $hos_no; ?>" onclick="return confirm('Are you sure you want to delete this drug request?')" class="btn btn-danger btn-xs">Delete</a>
													<?php } else { ?>
														<?php /*?><input type="button" name="adddrug" value="Add" data-target="#modal" id="<?php echo $row["sale_sn"] .'__'. $row['product_name'].'__'. $insurance_no .'__'. $insurance; ?>" class="btn btn-primary btn-xs add_drug" />
                                                    &nbsp;|&nbsp; <?php */ ?><a href="index.php?presc&chg=<?php echo $row['sale_sn'] . '/' . $hos_no; ?>&drug_name=<?= $row['product_name']; ?>#target" onclick="return confirm('Are you sure you want to Adapt this drug?')" class="btn btn-warning btn-xs" <?php if ($row['tag'] == '1' || $disabled == 'disabled') { ?>disabled<?php } ?>>Adapt</a>
													<?php } ?>
												</td>


											</tr>
										<?php ++$nn;
										} ?>

										<tr>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td><strong>Grand Total:</strong></td>

											<td colspan="2">
												<input type="text" class="input-sm form-control" id="grand_total" value="" readonly style=" width:90%;" />
											</td>
										</tr>


									</tbody>
								</table>
								<?php
								// Capture the output into a variable
								$tableOutput = ob_get_contents();
								// Clean (erase) the output buffer and turn off output buffering
								ob_end_clean();
								// Now you can output the captured content
								echo $tableOutput;
								?>
							</div>
							<br>
							<button class="btn btn-danger btn" type="submit" name="add_all_drugs" id="add_all_drugs" onclick="return confirm('Are you sure you want to Process Selected Drugs?')"><i class="fa fa-arrow"></i>&nbsp;Process Selected Drug(s)</button>
							&nbsp;:&nbsp;

							<button class="btn btn-success btn" type="submit" name="print_presciptn" id="print_presciptn"><i class="fa fa-arrow"></i>&nbsp;Print Prescription / Send SMS to Patient</button>
							<input type="hidden" name="hospital_no" value="<?php echo $hos_no; ?>">
							<input type="hidden" name="patient_name" value="<?php echo $names; ?>">
							<input type="hidden" name="patient_age" value="<?php echo $age; ?>">

							<input type="hidden" name="p_list" id="p_list" value="<?php echo $nn - 1; ?>">

						</form>

					<?php } else { ?>
						<div><strong style="color:#F00; font-size:18px; ">
								There are currently no pending medications for the selected date range.
								<br>
								To view previous records, adjust the date range to an earlier period and click the Apply button.</strong></div>
					<?php } ?>


					<?php

					$types = ['plan', 'treatment'];
					$placeholders = implode(',', array_fill(0, count($types), '?'));

					$stmt = $db->prepare("
    SELECT date_entry2, date_entry, prepared_by, no_updates, notes 
    FROM notes 
    WHERE hospital_no = ? 
    AND notes_type IN ($placeholders)
    AND status = '1' 
    AND date_entry BETWEEN ? AND ? 
    ORDER BY sn DESC
");

					$params = array_merge([$hos_no], $types, [$from_date, $to_date]);
					$stmt->execute($params);

					if ($stmt->rowCount() > 0 && $vip == 0) { ?>

						<div style="background-color:#CCFF99" align="center"><strong style="color: red;">Doctor's Plans: <?php echo $stmt->rowCount() . ' <u>Found</u>'; ?></strong></div>

						<div style=" max-height:500px; overflow:auto">

							<table class="table table-striped table-bordered">
								<thead>
									<tr bgcolor="#CCFF99">
										<th data-toggle="true" width="2%">#</th>
										<th data-toggle="true" width="60%">Plans</th>
										<th data-toggle="true"></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$bgcolor = '#F4F4F4';
									$colordecide = 1;
									$n = 1;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {



										if ($row['date_entry2'] != '') {
											$date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($row['date_entry2'])) . ' & ';
											$date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($row['date_entry'])) . '<br>';
											$date_ .= "<b>Number of Edits </b>" .  $row['no_updates'];
										} else {
											$date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($row['date_entry'])) . '<br>';
										}
										$date_ = "<div style='font-size:14px;'>$date_</div>";


										if ($colordecide % 2 == 0) {
											$bgcolor = '#F4F4F4';
										} else {
											$bgcolor = '#FFFFFF';
										}
									?>
										<tr bgcolor="<?php echo $bgcolor; ?>">
											<td><?php echo $n; ?></td>
											<td><?php

												if ($setdate == date('Y-m-d', strtotime($row['date_entry']))) { ?>
													<strong style="color: red;">[NEW] >> </strong>
												<?php } ?>
												<?php echo $row['notes']; ?>
											</td>
											<td><?php echo $date_; ///date('d M,Y h:i:s a', strtotime($row['date_entry']));
												echo ' |  <i>Entered by: </i><b>' . $row['prepared_by'] . '</b></div>'; ?></td>


										</tr>
									<?php ++$colordecide;
										++$n;
									}

									?>

								</tbody>
							</table>
						</div>
					<?php } ?>
				<?php } ?>




			</div>

		</div>
	</div>

	<!-- Reminder Modal -->
	<div class="modal fade" id="reminderModal" tabindex="-1" role="dialog" aria-labelledby="reminderModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="reminderModalLabel">Set Refill Reminder</h4>
				</div>
				<div class="modal-body">
					<form action="" method="POST">
						<input type="hidden" name="drug_sn_rem" id="drug_sn_rem">
						<input type="hidden" name="hos_no_rem" id="hos_no_rem">
						<div class="form-group">
							<label for="reminder_date">Reminder Date</label>
							<input type="date" class="form-control" name="reminder_date" id="reminder_date" required>
						</div>
						<div class="form-group">
							<label for="reminder_date">Remind me this days before due</label>
							<select name="days_before_reminder" id="days_before_reminder" class="form-control">
								<option value="1">1 Day</option>
								<option value="2">2 Days</option>
								<option value="5">5 Days</option>
								<option value="7">One Week</option>
								<option value="14">2 Weeks</option>
								<option value="30">1 Month</option>
								<option value="60">2 Months</option>
							</select>
						</div>
						<button type="submit" class="btn btn-primary">Set Reminder</button>
					</form>
					<hr>
					<h4>Existing Reminders(Active & Disabled)</h4>
					<div id="existing_drug_reminder_list">

					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Pharmacy Management</h5>
			</div>

			<div class="ibox-content">

				<?php if (!isset($_GET['dsp']) && !isset($_GET['hx'])) { ?>

					<div class="alert alert-info"><?php if (isset($_GET['chg'])) { ?>
							<strong style="color:#F00; font-size:16px">Select the New Drug you want to Adapt: </strong><b style="color:#00F; font-size:16px; "><?= $_GET['drug_name']; ?></b>
							<?php } else { ?><?php echo '<strong>Add New Drug Here >></strong>';
													} ?>
							<form action="index.php?presc&hos_no=<?= $hos_no; ?>#drug-section" method="POST">

								<table width="100%" cellpadding="5px;">
									<tr>
										<td width="12%">
											<strong>Type Drug Name:</strong>&nbsp;
										</td>
										<td style="padding-right:10px; ">

											<div class="form_sep">
												<select name="searchdrug" id="medications" class="input-sm select2" style="width:100%; height:45px;" required>
													<option selected="selected" value="">Search and Select medication</option>
													<?php
													$items = [];
													if ($_SESSION['dispensory'] == 1) {
														$stmt = $db->query("
													SELECT 
														m.sn,
														m.product_name,
														COALESCE(d.qty, '0') AS qty,
														d.expire_date,
														CONCAT(
															m.product_name,
															' Qty: [', COALESCE(d.qty, '0'), ']',
															CASE
																WHEN d.expire_date IS NULL 
																	OR d.expire_date = '' 
																	OR d.expire_date = '0000-00-00' 
																	OR DATE(d.expire_date) > DATE_ADD(CURRENT_DATE, INTERVAL 6 MONTH)
																THEN ''
																ELSE CONCAT(' **exp in ', DATEDIFF(d.expire_date, CURRENT_DATE), ' days**')
															END
														) AS name
													FROM stock_table m 
													INNER JOIN stock_table_dispensory d ON d.stock_table_id = m.sn
													WHERE m.status = 'active'
													ORDER BY m.product_name;
												");
													} else {
														$stmt = $db->query("
													SELECT 
														sn,
														product_name,
														COALESCE(qty, '0') AS qty,
														expire_date,
														CONCAT(
															product_name,
															' Qty: [', COALESCE(qty, '0'), ']',
															CASE
																WHEN expire_date IS NULL 
																	OR expire_date = '' 
																	OR expire_date = '0000-00-00' 
																	OR DATE(expire_date) > DATE_ADD(CURRENT_DATE, INTERVAL 6 MONTH)
																THEN ''
																ELSE CONCAT(' **exp in ', DATEDIFF(expire_date, CURRENT_DATE), ' days**')
															END
														) AS name
													FROM stock_table
													WHERE status = 'active' 
													AND hosp_price > 0 
													AND stock_table = 'Pharmacy'
													ORDER BY product_name;
												");
													}

													while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
														$items[] = [
															'id' => htmlspecialchars($row['sn'] . '__' . $row['product_name']),
															'text' => htmlspecialchars($row['name']),
															'is_expiring' => (!empty($row['expire_date']) && $row['expire_date'] !== '0000-00-00' && $row['expire_date'] <= date('Y-m-d', strtotime('+6 months')))
														];
													}
													?>
												</select>
											</div>
										</td>
										<td width="2%" style="padding-right:10px; ">
											Qty:
										</td>
										<td width="8%" style="padding-right:10px; ">
											<div class="form_sep">
												<input type="number" id="qty" name="qty" class="form-control input-sm" data-required="true" min="1" required>
											</div>
										</td>
										<td style="padding-top:5px; ">
											<?php if (isset($_GET['chg'])) {
												$chg = $_GET['chg'];
												$part = explode('/', $chg);
												$old_sn = $part[0];
											?>
												<div class="form_sep">
													<a id="target"></a>
													<button class="btn btn-warning btn-sm" type="submit" name="save_change" id="save_change">Save (Adapt)</button>
													<input type="hidden" name="old_sn" value="<?php echo $old_sn; ?>" />
													&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?presc&hos_no=<?php echo $hos_no; ?>" class="btn btn-danger btn-sm"><b>Cancel</b></a>
												</div>



											<?php } else { ?>
												<div class="form_sep">
													<button class="btn btn-success btn btn-sm" type="submit" name="ph_add_drug" id="ph_add_drug">Add Drug & Invoice</button>
												</div>
											<?php } ?>

										</td>
										<td>
											<a href="index.php?presc&hos_no=<?php echo $hos_no; ?>" class="btn btn-default btn btn-sm"> <i class=" fa fa-refresh"></i>&nbsp;Refresh</a>
										</td>
									</tr>

									<tr>
										<td></td>
										<td>
											<?php
											$chg = isset($_GET['chg']); // true if ?chg= exists
											$labelClass = $chg ? 'req' : '';
											$optionalText = $chg ? '' : '(Optional)';
											?>
											<label class="<?= $labelClass ?>">
												<i>Enter Drug Prescription<?= $optionalText ?>:</i>
											</label>
											<input type="text" name="new_precription" class="form-control" <?= $chg ? 'required' : '' ?>>
										</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								</table>

								<input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
								<input type="hidden" name="hosp_no" value="<?php echo $hos_no; ?>" />
								<input type="hidden" name="drug_name" value="<?php echo $drug_name; ?>" />
								<input type="hidden" name="nhis_price" value="<?php echo $nhis_price; ?>" />
								<input type="hidden" name="hosp_price" value="<?php echo $hosp_price; ?>" />
								<input type="hidden" name="access" value="<?php echo $access; ?>" />
								<input type="hidden" name="insurance" value="<?php echo $insurance; ?>" />
								<input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
								<input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>" />
								<input type="hidden" name="interest" value="<?php echo $interest; ?>" />
								<input type="hidden" name="ap_type" value="<?php echo $ap_type; ?>" />
								<input type="hidden" name="drug_sn" value="<?php echo $drug_sn; ?>" />
								<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />
								<input type="hidden" name="MM_update" value="pdetail" />
							</form>

					</div>

				<?php } ?>

				<?php

				if (!isset($_GET['dsp']) and !isset($_GET['hx'])) {

					if (isset($_GET['dispense'])) {
						$status_filter = " AND drug_status = 0  AND paystatus = 1";
					} elseif (isset($_GET['unpaid'])) {
						$status_filter = " AND paystatus = 0 AND invoice_status = 1 AND drug_status = 0";
					} elseif (isset($_GET['paid'])) {
						///$status_filter = " AND paystatus = 1 AND (invoice_status = 1 OR drug_status = 0)";
						$status_filter = " AND paystatus = 1 AND invoice_status = 1 AND drug_status = 0";
					} else {
						$status_filter = " AND invoice_status = 1 AND drug_status = 0";
					}

					$stmt = $db->prepare('SELECT p.serv_group,p.remarks,p.pay,p.claim_amt,p.date_entry,p.transact_date,p.prepared_by,p.pay_mode,p.paystatus,p.qty,p.dsp_by,p.invoice_by,p.invoice_status,p.drug_status,p.item_services,p.sn,p.access,p.cr,p.drug_sn,p.wallet_debt_bill_to_acct,p.dept_dispensory_id,p.dept_id,p.dispensory_status_at_phamcy,p.prescription,s.product_name,s.dosage,s.qty as stock_qty,s.strength FROM patient_ap_services AS p 
					INNER JOIN stock_table AS s ON p.drug_sn = s.sn 
					WHERE hospital_no = :hos_no ' . $status_filter . ' AND serv_group = :serv_group   ' . $patch_Dispens_query . ' ORDER BY invoice_date DESC');
					$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
					$stmt->bindValue(':serv_group', $Pharmacy, PDO::PARAM_STR);
					if ($_SESSION['dispensory'] != 0) {
						$stmt->bindValue(':dept_dispensory_id', $dept_id); // Bind only if dispensary is not 0
					}
					$stmt->execute();
					if ($stmt->rowCount() > 0) { ?>

						<span style="color:brown; font-size: 14px;"> <b>Filter: Show by </b> </span>
						<a href="index.php?presc&hos_no=<?= $hos_no; ?>&paid#drug-section" class="btn btn-success btn-xs">Paid List</a>
						: <a href="index.php?presc&hos_no=<?= $hos_no; ?>&unpaid#drug-section" class="btn btn-danger btn-xs">Un-Paid List</a>
						: <a href="index.php?presc&hos_no=<?= $hos_no; ?>&dispense#drug-section" class="btn btn-warning btn-xs">All Pending</a>
						: <a href="index.php?presc&hos_no=<?= $hos_no; ?>#drug-section" class="btn btn-default btn-xs">Refresh</a>

						<div id="drug-section">
							<form action="index.php?invoice" method="POST">
								<?php ob_start(); ?>
								<table id="myTable" class="table table-striped table-bordered table-hover dataTables-example">

									<thead>
										<tr>
											<th data-toggle="true" width="1%">Check</th>
											<th data-toggle="true" width="20%">Drug</th>
											<th data-hide="phone,tablet" width="25%">Prescription</th>
											<th data-hide="phone,tablet" width="10%">Amount</th>
											<th data-hide="phone,tablet" width="15%">Date</th>
											<th data-hide="phone,tablet" width="15%"></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$paying = 0;
										$claiming = 0;
										$invoice_set = 0;
										$inv_print = 0;
										$dispense = 0;
										$dispense_cr = 0;
										while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

											$Dispensory = null;
											if ($row['dispensory_status_at_phamcy'] == 1) {
												$departmentsArray = getAllDepartments($db); // Fetch all departments
												foreach ($departmentsArray as $department) {
													if ($department['sn'] == $row['dept_dispensory_id']) {
														$Dispensory = '<br><span style="color:blue;">RQ Unit: <br></span>' . $department['department'];
														//break;
													}
												}
											} elseif ($row['dept_dispensory_id'] != $dept_id) {
												$departmentsArray = getAllDepartments($db); // Fetch all departments
												foreach ($departmentsArray as $department) {
													if ($department['sn'] == $row['dept_dispensory_id']) {
														$Dispensory = '<br><span style="color:blue;">Dispensory Unit: <br></span>' . $department['department'];
														break;
													}
												}
											} else {
												$Dispensory = null;
											}

											if ($Dispensory == null) {

												$disabled_chk = '';
												$disabled_msg = '';

												if ($_SESSION['dsp_oncredit'] == 0 && $row['paystatus'] == 0 && $row['pay'] > 0 && $credit_limit > 0) {

													if ($credit_limit > 0 && $row['pay'] <= $credit_limit) {
														$credit_limit = $credit_limit - $row['pay'];
													} else {
														$check = 0;
														$disabled_chk = 'disabled';
													}
												} elseif ($row['claim_amt'] > 0 && $row['paystatus'] == 0) {
													$check = 0;
													$disabled_chk = 'disabled';
													$disabled_msg = '<strong style="color:red;">Apply Post Pending</strong>';
												} elseif ($row['drug_status'] == 1) {
													$check = 0;
												} elseif (($row['paystatus'] == 1 or $row['cr'] == 1) and $row['drug_status'] == 0) {
													$check = 1;
												} else {
													$check = 0;
												}
												if ($row['paystatus'] == 1 and $row['drug_status'] == 1) {
													$inv_print = 1;
												} else {
													$inv_print = 0;	?>

													<tr>
														<td align="center"><br>
															<input type="checkbox"
																value="<?php echo $row['sn']; ?>" name="inv[]" <?php if ($check == '1') { ?>checked<?php } ?> <?php echo $disabled_chk; ?>
																style="display:block; height:19px; width:19px;" />
														</td>

														<td><?php if ($insurance_status == 1 and $row['access'] == 3) {
																echo '<strong style="color:#F00"> Private Drug(100% payable)</strong><br>';
															}
															echo $row['item_services'];
															echo $Dispensory
															?>
															<br>
															<?php if ($_SESSION['dispensory'] == 0) { ?>
																<input type="button" name="Refill" value="+ Refill Drug" data-target="#modal" id="<?php echo $row['sn'] . '/' . $hos_no . '/' . $app_no . '/' . $names . '/IN/P/' . $adm_status . '/' . $row['paystatus']; ?>" class="btn btn-primary btn-xs refill_drug" />
																&nbsp;|&nbsp;
															<?php } ?>
															<input type="button" name="notes" value="Notes" data-target="#modal" id="<?php echo $row['sn'] . '__' . $row['item_services']; ?>" class="btn btn-success btn-xs view_notes" />
															<?php
															if (
																($row['created_by'] == $_SESSION['id'] || $_SESSION['unit_head'] == 1)
																&& $row['paystatus'] == 0
																&& $row['drug_status'] == 0
																&& $row['dept_id'] == $_SESSION['dept_id']
															) {	?>
																&nbsp;|&nbsp;
																<a href="index.php?presc<?php echo '&dl=' . $row['sn'] . '&hos_no=' . $hos_no; ?>" onclick="return confirm('Are you sure you want to delete this drug?')" class="btn btn-danger btn-xs">Delete</a> &nbsp;|&nbsp;
															<?php } elseif ($row['paystatus'] == 0 and $row['drug_status'] == 0) { ?>

																<a href="index.php?presc<?php echo '&cl=' . $row['sn'] . '&hos_no=' . $hos_no; ?>" class="btn btn-warning btn-xs">Cancel</a>
															<?php } ?>

															<?php if ($disabled_msg != '') { ?><?php echo '<br>' . $disabled_msg;
																							} ?>

														<td>
															<?php
															if ($row['remarks'] != '') {
																echo $row['remarks'] . ' <strong>Remarks: </strong>' . $row['prescription'];
															}
															echo '<br><strong>Requested by/Date: </strong><br>' . $row['prepared_by'] . ' / ' . date('d M,y h:ia', strtotime($row['date_entry']));
															?>


														</td>

														<td><?php echo '<b>Amount: </b>' . number_format($row['pay'], 2);
															echo '<br><b>Claim: </b>' . number_format($row['claim_amt'], 2) . '<br><b>Quantity: </b>' . $row['qty']; ?></td>
														<td><?php echo date('d M,Y', strtotime($row['date_entry'])) . ' / ' . date('h:i:s a', strtotime($row['date_entry']));
															echo '<br><b>';
															if ($row['invoice_status'] == '0') {
																echo 'Invoice Pending';
															} else {
																echo 'Invoiced by ' . $row['invoice_by'];
															} ?> <?php echo '</b>'; ?></td>

														<td bgcolor="#CCFFFF">
															<div align="">
																<?php if ($row['paystatus'] == 1 and $row['pay'] > 0) { ?>
																	<strong>PAID</strong><br>
																	<?php
																	if ($row['drug_status'] == 1) {
																		echo '<br>[ Dispensed] by: ' . $row['dsp_by'];
																	} ?>

																<?php } elseif ($row['paystatus'] == 1 and $row['pay'] == 0 and $row['claim'] > 0) { ?>
																	<strong>POSTED</strong><br>
																	<?php
																	if ($row['drug_status'] == 1) {
																		echo '<br>[ Dispensed] by: ' . $row['dsp_by'];
																	} ?>
																<?php } ?>

																<?php



																if ($row['paystatus'] == 1 and $row['drug_status'] == 0) {
																	$dispense = +1; ?>
																	<strong style="color: red;">DISPENSE PENDING</strong>
																	<?php
																	$date2 = new DateTime($row['transact_date']);
																	$diff = $date1_->diff($date2); // Calculate the difference here
																	$day = $diff->format('%a');
																	$days_valid = $day;
																	if ($_SESSION['reverse_drug'] == 1 && $row['pay_mode'] == 'cash' && $row['pay'] > 0 && $days_valid <= $drug_reversal_period) {
																	?>
																		<button type="button" class="btn btn-danger btn-xs dropdown-toggle" id="reverse_pay1" onClick="reverse_pay('<?php echo $row['sn']; ?>')">Reverse Pay</button>
																	<?php
																	}
																} elseif ($row['drug_status'] == 0 and $row['pay'] == 0 and $row['paystatus'] == 1) { ?>
																	<strong>Posted</strong><br>
																<?php }
																if ($row['paystatus'] == 0 and $row['drug_status'] == 0 and ($row['invoice_status'] == 0 or $row['invoice_status'] == 1)) {
																	if ($row['stock_qty'] <= 0) {
																		$stmtd = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :dept_id AND bal > 0 ORDER BY sn DESC LIMIT 1");
																		$stmtd->bindParam(':stock_sn', $row['drug_sn'], PDO::PARAM_STR);
																		$stmtd->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
																		$stmtd->execute();

																		if ($stmtd->rowCount() == 0) {
																			$Insufficent_qty = '<br><b>(Insufficient Qty)</b>';
																		}
																	} else {
																		$Insufficent_qty = null;
																	}
																?>

																	<strong style="color:#F00">UNPAID<?php echo $Insufficent_qty; ?></strong><br>
																	<?php
																	if ($current_balance > 0 and $_SESSION['payfrom_status'] == 1) { ?>
																		<button type="button" class="btn btn-warning btn-xs dropdown-toggle" id="pay_now" onClick="payNow('<?php echo $row['sn']; ?>','pharmacy','<?php echo $hos_no; ?>')">Pay from Wallet</button>

																	<?php } ?>

																	<?php
																	if ($_SESSION['dsp_oncredit'] == 1 or $credit_limit > 0) {
																		$dispense_cr = +1; ?>
																	<?php } ?>
																<?php } elseif ($row['paystatus'] == 0 and $row['drug_status'] == 1 and ($row['invoice_status'] == 0 or $row['invoice_status'] == 1)) { ?>

																	<strong style="color: #F00">Dispensed On/Credit by:</strong> <br> <?php echo $row['dsp_by'] . '<br>'; ?>
																	<input type="button" name="on_cr" value=" - Reverse Drug &nbsp;" data-target="#modal" id="<?php echo $row['sn'] . '/' . $hos_no . '/' . $app_no . '/' . $names . '/IN/P/' . $adm_status . '/' . $row['paystatus']; ?>" class="btn btn-info btn-xs reverse" />
																<?php } ?>

														</td>

													</tr>

												<?php
													if ($row['paystatus'] == 0) {
														$claiming = $claiming + $row['claim_amt'];
														$paying = $paying + $row['pay'];
													}
												}
											} else {

												///// =================================== DISPENSORY //// REQUISITION FROM STORE ===================

												?>

												<tr>
													<td align="center"></td>
													<td><?php echo $row['item_services'] . $Dispensory; ?>
														<?php if ($row['paystatus'] == 0 and $row['drug_status'] == 0) { ?>
															<a href="index.php?presc<?php echo '&cl=' . $row['sn'] . '&hos_no=' . $hos_no; ?>" class="btn btn-warning btn-xs">Cancel</a>
														<?php } elseif ($row['paystatus'] == 1 and $row['drug_status'] == 0) { ?><br>

															<?php if ($row['dispensory_status_at_phamcy'] == 1) {
																$QTY_STATUS = null;
																$dept_id_status = $row['dept_id'];
																$drug_sn = $row['drug_sn'];

																$departmentsArray = getAllDepartments($db); // Fetch all departments
																foreach ($departmentsArray as $department) {
																	if ($department['sn'] == $dept_id_status) {
																		$department_ = $department['department'];
																		break;
																	}
																}

																$stmt_dispensary = $db->prepare("SELECT bal FROM stock_table_inven 
																WHERE stock_sn = :stock_sn 
																AND cust_patient_id = :cust_patient_id 
																ORDER BY sn DESC LIMIT 1");
																$stmt_dispensary->execute([
																	':stock_sn' => $drug_sn,
																	':cust_patient_id' => $dept_id_status
																]);
																$rw_disp = $stmt_dispensary->fetch(PDO::FETCH_ASSOC);
																$bal = $rw_disp ? $rw_disp['bal'] : 0;

																if ($bal && $bal > 0) {
																	$Dispens_title = "Dispense (RQ Store) Bal: " . $bal;
																	$danger = "success";
																} else {
																	$QTY_STATUS = '<b>RQ Insufficient Qty At ' . $department_ . '</b><br>';
																}


																echo $QTY_STATUS;
															?>
																<button type="button" class="btn btn-<?= $danger; ?> btn btn-xs" id="dispense_btn_RQ"
																	onclick="dispense_RQ_Dispen('<?php echo 'dispense_RQ'; ?>','<?php echo $hos_no; ?>','<?php echo $row['sn']; ?>')"><?= $Dispens_title; ?></button>
															<?php } elseif ($row['dept_dispensory_id'] != $dept_id) { ?>
																<button type="button" class="btn btn-danger btn btn-xs" id="dispense_btn_RQ"
																	onclick="dispense_RQ_Dispen('<?php echo 'dispense_Main'; ?>','<?php echo $hos_no; ?>','<?php echo $row['sn']; ?>')">Dispense (Main Store)</button>
															<?php } ?>

														<?php } ?>
													</td>
													<td><?php echo $row['remarks']; ?>
													</td>
													<td><?php echo '<b>Amount: </b>' . number_format($row['pay'], 2);
														echo '<br><b>Claim: </b>' . number_format($row['claim_amt'], 2) . '<br><b>Quantity: </b>' . $row['qty']; ?></td>
													<td><?php echo date('d M,Y', strtotime($row['date_entry'])) . '<br>' . date('h:i:s a', strtotime($row['date_entry']));
														echo '<br><b>';
														if ($row['invoice_status'] == '0') {
															echo 'Invoice Pending';
														} else {
															echo 'Invoiced';
														} ?> <?php echo '</b>'; ?></td>

													<td bgcolor="#CCFFFF">
														<div align="">
															<?php if ($row['paystatus'] == 1 and $row['pay'] > 0) { ?>
																<strong>PAID</strong><br>
																<?php
																if ($row['drug_status'] == 1) {
																	echo '<br>[ Dispensed] by: ' . $row['dsp_by'];
																} ?>

															<?php } elseif ($row['paystatus'] == 1 and $row['pay'] == 0 and $row['claim'] > 0) { ?>
																<strong>POSTED</strong><br>
																<?php
																if ($row['drug_status'] == 1) {
																	echo '<br>[ Dispensed] by: ' . $row['dsp_by'];
																} ?>
															<?php } ?>

															<?php
															if ($row['paystatus'] == 1 and $row['drug_status'] == 0) { ?>
																<strong style="color: red;">DISPENSE PENDING</strong>
																<?php if ($row['paystatus'] == 1 and $row['pay_mode'] == 'cash' and $row['drug_status'] == 0 and $row['pay'] > 0) { ?>
																	<button type="button" class="btn btn-danger btn-xs dropdown-toggle" id="reverse_pay1" onClick="reverse_pay('<?php echo $row['sn']; ?>')">Reverse Pay</button>
																<?php } ?>

															<?php } elseif ($row['drug_status'] == 0 and $row['pay'] == 0 and $row['paystatus'] == 1) { ?>
																<strong>Posted</strong><br>
															<?php }
															if ($row['paystatus'] == 0 and $row['drug_status'] == 0 and ($row['invoice_status'] == 0 or $row['invoice_status'] == 1)) {
															?>

																<strong style="color:#F00">UNPAID<?php echo $Insufficent_qty; ?></strong><br>
																<?php if ($current_balance > 0 and $_SESSION['payfrom_status'] == 1) { ?>
																	<button type="button" class="btn btn-warning btn-xs dropdown-toggle" id="pay_now" onClick="payNow('<?php echo $row['sn']; ?>','pharmacy','<?php echo $hos_no; ?>')">Pay from Wallet</button>
																<?php } ?>

																<?php
																if ($_SESSION['dsp_oncredit'] == 1 || $credit_limit > 0) {
																	$dispense_cr = +1; ?>
																<?php } ?>
															<?php } elseif ($row['paystatus'] == 0 && $row['drug_status'] == 1 && ($row['invoice_status'] == 0 || $row['invoice_status'] == 1)) { ?>

																<strong style="color: #F00">Dispensed On/Credit by:</strong> <br> <?php echo $row['dsp_by'] . '<br>'; ?>
																<input type="button" name="on_cr" value=" - Reverse Drug &nbsp;" data-target="#modal" id="<?php echo $row['sn'] . '/' . $hos_no . '/' . $app_no . '/' . $names . '/IN/P/' . $adm_status . '/' . $row['paystatus']; ?>" class="btn btn-info btn-xs reverse" />
															<?php } ?>
													</td>
												</tr>
										<?php
											}
										} // / end og the loooping ////
										?>

									</tbody>
								</table>

								<?php
								// Capture the output into a variable
								$tableOutput = ob_get_contents();
								// Clean (erase) the output buffer and turn off output buffering
								ob_end_clean();
								// Now you can output the captured content
								echo $tableOutput;
								?>
								<span style="color:brown; font-size: 14px;"> <b>Filter: Show by </b> </span>
								<a href="index.php?presc&hos_no=<?= $hos_no; ?>&paid#drug-section" class="btn btn-success btn-xs">Paid List</a>
								: <a href="index.php?presc&hos_no=<?= $hos_no; ?>&unpaid#drug-section" class="btn btn-danger btn-xs">Un-Paid List</a>
								: <a href="index.php?presc&hos_no=<?= $hos_no; ?>&dispense#drug-section" class="btn btn-warning btn-xs">All Pending</a>
								: <a href="index.php?presc&hos_no=<?= $hos_no; ?>#drug-section" class="btn btn-default btn-xs">Refresh</a>

								<table width="100%" align="right">
									<tr>
										<td align="right">
											<?php if ($dispense > 0) { ?>
												<button type="button" class="btn btn-primary btn btn-sm" id="dispense_btn"
													onclick="dispense_process('<?php echo 'dispense_all'; ?>','<?php echo $hos_no; ?>')">Dispense All</button>


											<?php
												// $credit_limit > 0 && $pay <= $credit_limit &&


											} elseif ($dispense_cr > 0 && $dispense == 0 && $paying > 0) {	?>
												<button type="button" class="btn btn-danger btn btn-sm" id="dispense_btn" <?= $credit_limit <= 0 ? 'disabled' : '' ?>

													onclick="dispense_process('<?php echo 'dispense_all_cr'; ?>','<?php echo $hos_no; ?>')">Dispense On Credit </button>
											<?php } ?>

										</td>
									</tr>
								</table>


								<table width="100%">
									<tr>
										<td>
											<div class="form_sep">
												<button class="btn btn-success btn btn-sm" type="submit" name="print_invoice" id="print_invoice"><i class="fa fa-print"></i>&nbsp;Print Selected Invoices</button>

												<input type="button" name="writeoff" value="Validate Package" data-target="#myModal5" id="<?php echo $hos_no; ?>" class="btn btn-info btn-sm validate_package" /><br>
												<!--            <button class="btn btn-success btn btn-sm" type="submit" name="pay_for_deposit" id="pay_for_deposit"><i class="fa fa-print"></i>&nbsp;Pay from Deposit </button>-->


											</div>
											<?php // }
											?>
											<input type="hidden" name="hosp_no" id="hosp_no" value="<?php echo $hos_no; ?>" />
											<input type="hidden" name="re_direct" value="<?php echo 'p'; ?>" />
										</td>

							</form>
						</div>


						<td>
							<div align="right">
								<?php if (isset($_GET['pay'])) { ?><strong style="color:#00F; font-size:14px">Payment/Post was successfully!<br>Dispense medications now </strong><?php } ?>
								<?php if (isset($_GET['perr'])) { ?><strong style="color:#F00; font-size:14px">ERROR! No enough money to continue payment</strong><?php } ?>

								<?php
								if ($paying > 0) {
									echo '<h3 style="color:red; ">Credit Balance: ' . $credit_limit;
									if ($credit_limit <= 0) {
										echo '<br>Patient has exhuasted credit limit With About: ' . number_format($Total_total_credits, 2, '.', ',');
									}
									echo  '</h3>';
								}
								?>

								<table style="background-color:#CCF;">
									<tr>
										<td>
											<div align="right"><strong>Insured Billed:</strong></div>
										</td>
										<td>&nbsp;&nbsp;</td>
										<td>
											<div align="right" style="font-size:20px; padding-right:10px; padding-top:10px;"><strong><?php echo number_format($claiming, 2, '.', ','); ?></strong></div>
										</td>
									</tr>
									<tr>
										<td>
											<div align="right"><strong>Amount Paying:</strong></div>
										</td>
										<td>&nbsp;&nbsp;</td>
										<td>
											<div align="right" style="font-size:20px;  padding-right:10px;"><strong><?php echo number_format($paying, 2, '.', ','); ?></strong></div>
										</td>
									</tr>
									<?php if (($current_balance > 0  && $_SESSION['payfrom_status'] == 1) || $claiming > 0) { ?>
										<tr>
											<td>
												<div align="right" style="color: #006; font-size:14px; padding-left:10px;"><strong>Balance(Current Deposit):</strong></div>
											</td>
											<td>&nbsp;&nbsp;</td>
											<td>
												<div align="right" style="font-size:20px;  padding-right:10px;"><strong><?php echo number_format($current_balance, 2, '.', ','); ?></strong></div>
											</td>
										</tr>
										<tr>
											<td>
												<div align="right" style="color: #006; font-size:14px"><strong></strong></div>
											</td>
											<td>&nbsp;&nbsp;</td>
											<td>
												<div align="right" style="font-size:20px; padding-right:10px; padding-bottom:10px;">
													<form action="apply_claim.php?#drug-section" method="POST">
														<?php if ($claiming > 0) {

															$stmt = $db->prepare("SELECT invoice_status FROM patient_ap_services WHERE hospital_no = :hos_no AND invoice_status = '1' AND paystatus=0 AND serv_group = 'Pharmacy'");
															$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
															//$stmt->bindParam(':from_date', $back_date, PDO::PARAM_STR);
															//$stmt->bindParam(':to_date', $to_date, PDO::PARAM_STR);
															$stmt->execute();
															if ($stmt->rowCount() > 0) { ?>
																<h3 style="color: #F00">Apply/Post the <U><i>TOTAL SERVICES CLAIM ABOVE</i></U> Before Dispensing Drug(s)!</h2>
																	<button class="btn btn-warning" type="submit" name="apply_claim" id="apply_claim" onclick="return confirm('Are you sure you want to post claim?')">Apply Claim</button>
																<?php } ?>

																<input type="hidden" name="transc_type" value="<?php echo 'claim'; ?>" />
															<?php } ?>

															<input type="hidden" name="current_balance" value="<?php echo $current_balance; ?>" />
															<input type="hidden" name="hosp_no" id="hosp_no" value="<?php echo $hos_no; ?>" />
															<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />
															<input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
															<input type="hidden" name="save_insurance_no" value="<?php echo $save_insurance_no; ?>" />
															<input type="hidden" name="active_bal" value="<?php echo $active_bal; ?>" />
													</form>
												</div>
											</td>
										</tr>
									<?php } ?>
								</table>
							</div>
						</td>
						</tr>
						</table>

					<?php

					} else { ?>
						<div style="font-size:13px;" align="center"><b>No Pending Drugs for Payment/Dispense</b></div>
					<?php } ?>


					<?php } elseif (isset($_GET['dsp'])) {

					$to_date = $to_date . ' 23:59:59';
					$target = 'dsp';
					$back_date_v = date('Y-m-d', strtotime("-$drug_reversal_period days")) . ' 00:00:00';

					$stmt = $db->prepare('SELECT p.serv_group,
                            p.remarks,
                            p.pay,
                            p.tag,
                            p.claim_amt,
                            p.date_entry,
                            p.prepared_by,
                            p.created_by,
                            p.paystatus,
                            p.qty,
                            p.drug_status,
                            p.wallet_debt_bill_to_acct,
                            p.invoice_no,
                            p.invoice_status,
                            p.dsp_by,
                            p.item_services,
                            p.dept_id,
                            p.dept_dispensory_id,
                            p.sn,
                            i.insertion_date_time,
                            p.cr
                     FROM patient_ap_services as p
                     INNER JOIN stock_table_inven AS i ON p.sn = i.sale_sn
                     WHERE hospital_no = :hos_no
                     AND serv_group = :serv_group
                     AND return_status = 0
                     AND drug_status = :drug_status
                     AND qtyOUT >0
                     ' . $patch_Dispens_query . '
                     AND insertion_date_time BETWEEN :back_date_v AND :to_date');

					$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
					$stmt->bindParam(':serv_group', $Pharmacy, PDO::PARAM_STR);
					$stmt->bindParam(':drug_status', $one, PDO::PARAM_STR);
					if ($_SESSION['dispensory'] != 0) {
						$stmt->bindValue(':dept_dispensory_id', $dept_id); // Bind only if dispensary is not 0
					}
					$stmt->bindParam(':back_date_v', $back_date_v, PDO::PARAM_STR);
					$stmt->bindParam(':to_date', $to_date, PDO::PARAM_STR);
					$stmt->execute();
					if ($stmt->rowCount() > 0) { ?>

						<?php if (isset($_GET['dsp'])) {
							$target = 'dsp';
						?>
							<div class="alert alert-success">
								<h3>PATIENT'S DISPENSED LIST </h3>
								<i><b style="color:red;"> The reversal period is <?= $drug_reversal_period; ?> days from today's date! (<?= date('d M, Y', strtotime($back_date_v)) . ' to ' . date('d M, Y', strtotime($to_date)); ?>) </b></i>
							</div>
							<table width="100%">
								<tr>
									<td>
										<div style="color:#F00"><strong><?php echo '' . $stmt->rowCount() . ', '; ?>Dispense List</strong></div>
									</td>
									<td>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									</td>
								</tr>
							</table>

							<hr>
						<?php } ?>
						<form action="print_cep.php" name="delete_cform" id="delete_cform" method="POST">
							<div class="sideScrollStyle3">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th data-toggle="true" width="1%">#</th>
											<th data-toggle="true" width="20%">Drug</th>
											<th data-hide="phone,tablet" width="25%">Prescription</th>
											<th data-hide="phone,tablet" width="10%">Amount</th>
											<th data-hide="phone,tablet" width="15%">Dispensed Date/By</th>
											<th data-hide="phone,tablet" width="15%"></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$bgcolor = '#F4F4F4';
										$colordecide = 1;
										$paying = 0;
										$claiming = 0;
										$inv_status = 0;
										$h = 1;
										while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										?>

											<tr>
												<td><?php echo $h++; /// '<input type="checkbox" value="' . $row['sn'] . '__' . $row['item_services'] . '__' . $row['pay'] . '__' . $row['claim_amt'] . '__' . $row['qty'] . '__' . $row['cr'] . '__' . $row['wallet_debt_bill_to_acct'] . '" name="inv[]" checked="checked" /> ' . $row['invoice_no'];
													?></td>
												<td><?php echo $row['item_services']; ?>
													<?php if ($row['paystatus'] == 0 and $row['drug_status'] == 1 and ($row['invoice_status'] == 0 or $row['invoice_status'] == 1)) { ?><br>
														<strong style="color: #F00">Dispensed On/Credit by:</strong> <br> <?php echo $row['dsp_by'];
																														} ?>
												</td>
												<td><?php if ($row['remarks'] != '') {
														echo $row['remarks'];
													} else {
														echo '';
													} ?></td>
												<td><?php if ($row['paystatus'] == '1') {
														echo '<b>Paid: </b>';
													} else {
														echo '<b>Paying: </b>';
													}
													echo number_format($row['pay'], 2, '.', ',');
													echo '<br><b>Claim: </b>' . number_format($row['claim_amt'], 2, '.', ',') . '<br><b>Quantity: </b>' . $row['qty']; ?></td>
												<td><?php echo date('d M,Y', strtotime($row['insertion_date_time'])) . '<br>' . date('h:i:s a', strtotime($row['insertion_date_time']));
													echo '<br><b>' . $row['dsp_by'] . '</b>'; ?></td>

												<td bgcolor="#CCFFFF">
													<div align="center">
														<?php
														if ($_SESSION['reverse_drug'] == 0) {
															echo '<b>Insufficient Privileges</b>';
														} elseif ($_SESSION['dept_id'] != $row['dept_dispensory_id']) {
															$departmentsArray = getAllDepartments($db); // Fetch all departments
															foreach ($departmentsArray as $department) {
																if ($department['sn'] == $row['dept_dispensory_id']) {
																	echo $department['department'] . '<br><b style="color:red;">Insufficient Privileges</b>'; // Return the found department
																	break;
																}
															}
														} elseif ($row['drug_status'] == '1' && $row['tag'] != 'notRvse' && $_SESSION['reverse_drug'] == 1) { ?>
															<input type="button" name="on_cr" value="Reverse Drug &nbsp;" data-target="#modal" id="<?php echo $row['sn'] . '/' . $hos_no . '/' . $app_no . '/' . $names . '/IN/P/' . $adm_status . '/' . $row['paystatus']; ?>" class="btn btn-info btn-xs reverse" />
														<?php } ?>
												</td>
											</tr>
										<?php } ?>

									</tbody>
								</table>
							</div>
							<div align="right"><?php echo '<i><b>Total Result(s): ' . $stmt->rowCount() . '</b></i>'; ?></div>
							<br><br>
							<div class="form_sep">


								<?php if ($h > 20) { ?><a href="index.php?presc&hos_no=<?= $hos_no; ?>&dsp" class="btn btn-success btn-sm">Refresh/Go Up</a> <?php } ?>

								<!--<button class="btn btn-info btn-sm" type="submit" name="print_reciept" id="print_reciept">Print Reciept</button> -->
								<input type="hidden" name="hosp_no" value="<?php echo $hos_no; ?>" />

						</form>

					<?php } else { ?>
						<div><strong style="color:#F00">No dispensed medications are allowed during the grace period between <br>(<?= date('d M, Y', strtotime($back_date_v)) . ' to ' . date('d M, Y', strtotime($to_date)); ?>)</strong></div>
					<?php } ?>

				<?php } ?>


				<?php if (isset($_GET['hx'])) {
					$target = 'hx'; ?>
					<div class="alert alert-success">
						<h3>PATIENT'S MEDICATIONS HISTORY</h3>
						<a href="statistics.php?hospital_no=<?= $hos_no; ?>" style="font-size: 14px; ;">Click for More Report</a>
					</div>
					<table class="table table-striped table-bordered table-hover dataTables-example">
						<thead>
							<tr>
								<th width="4%">#</th>
								<th width="10%">Date</th>
								<th width="25%">Drug</th>
								<th width="5%">Amount</th>
								<th width="5%">Claim</th>
								<th width="15%">Prescription</th>
								<th width="15%">Doctor/Responsible</th>
								<th width="15%">Pharmacist</th>
								<th width="10%">Status</th>
							</tr>
						</thead>

						<tbody>

							<?php

							$stmt = $db->prepare("SELECT item_services,date_entry,remarks,prepared_by,created_by,dsp_by,drug_status,claim_amt,pay
					   FROM patient_ap_services 
					   WHERE hospital_no = :hos_no 
					   AND serv_group = 'Pharmacy' 
					   ORDER BY date_entry DESC");

							$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
							$stmt->execute();
							if ($stmt->rowCount() > 0) {
								$n = 1;
								while ($row_visit = $stmt->fetch(PDO::FETCH_ASSOC)) {
							?>
									<tr>
										<td><?php echo $n; ?></td>
										<td width="10%"><?php echo date('d M,Y', strtotime($row_visit['date_entry'])) . ' ' . date('h:i a', strtotime($row_visit['date_entry'])); ?></td>

										<td><?php echo $row_visit['item_services']; ?></td>
										<td><?php echo number_format($row_visit['pay'], 2); ?></td>
										<td><?php echo number_format($row_visit['claim_amt'], 2); ?></td>
										<td><?php echo $row_visit['remarks']; ?></td>
										<td><?php echo $row_visit['prepared_by']; ?></td>
										<td><?php echo $row_visit['dsp_by']; ?></td>
										<td><?php if ($row_visit['drug_status'] == 1) {
												echo "<b style='color:blue;'>Delivered</b>";
											} else {
												echo "<b style='color:red;'>Not Delivered</b>";
											} ?></td>
									</tr>
							<?php
									$n++;
								}
							} ?>

						</tbody>
					</table>



				<?php } ?>







			</div>
		</div>

	</div>

	<?php

	$departmentsCache = [];

	// Function to get all departments
	function getAllDepartments($db)
	{
		global $departmentsCache; // Use the global variable
		if (empty($departmentsCache)) { // Check if the cache is empty
			$stmt = $db->query("SELECT sn,department FROM department where requistn_disp_ByMainPharm_or_depensory=1 or dispensory=1");
			$departmentsCache = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch and cache departments
		}
		return $departmentsCache; // Return the cached departments
	}

	function getDepartmentBySn($db, $sn)
	{
		///$sn = '8';
		$departmentsArray = getAllDepartments($db); // Fetch all departments
		foreach ($departmentsArray as $department) {
			if ($department['sn'] == $sn) {
				return  $department['department']; // Return the found department
			}
		}
		///return $sn; // Return null if not found
	}

	function displayRemainingDays($expireDate, $sn_)
	{

		try {
			// Create DateTime objects
			$currentDate = new DateTime();
			$expirationDate = new DateTime($expireDate);
			// Calculate remaining days
			$remainingDays = $currentDate->diff($expirationDate)->days;

			// Determine the color based on remaining days
			if ($expireDate == "0000-00-00") {
				echo "<sup style='color: red; font-size:12px;'>Invalid Exp. Date</sup>";
				echo "<button type='button' class='btn btn-danger btn-xs' onclick='handleExpired($sn_)'>Edit Date</button>"; // Example button
			} elseif ($remainingDays <= 0) {
				// Expired
				echo "<sup style='color: red; font-size:12px;'>Expired</sup>";
				echo "<button type='button' class='btn btn-danger btn-xs' onclick='handleExpired($sn_)'>Edit Date</button>"; // Example button
			} elseif ($remainingDays < 30) { // Between 1 and 3 months
				echo "<sup style='color: red; font-size:12px;'>Exp. $remainingDays days</sup>";
				echo "<button type='button' class='btn btn-danger btn-xs' onclick='handleExpired($sn_)'>Edit Date</button>"; // Example button
			}
		} catch (Exception $e) {
			// Handle invalid date
			echo "<span style='color: orange;'>Invalid expiration date</span>";
			echo "<button onclick='handleExpired()'>Take Action</button>"; // Example button
		}
	}



	// Example JavaScript functions to handle button clicks

	?>
	<div class="modal inmodal fade" id="add_drug_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Add Drug</h4>
				</div>
				<div class="modal-body" id="add_drug_body">
				</div>
			</div>
		</div>
	</div>

	<div class="modal inmodal fade" id="view_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Notes</h4>
				</div>
				<div class="modal-body" id="view_notes_body">
				</div>
			</div>
		</div>
	</div>

	<div class="modal inmodal fade" id="refill_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Refill Drug</h4>
				</div>



				<div class="modal-body" id="refill_body">
				</div>
			</div>
		</div>
	</div>

	<div class="modal inmodal fade" id="dsp_oncredit_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Dispense On/Credit</h4>
				</div>
				<div class="modal-body" id="dsp_oncredit_body">
				</div>

			</div>
		</div>
	</div>

	<div class="modal inmodal fade" id="reverse_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>-->
					<h4 class="modal-title" id="">Reverse Drug</h4>

				</div>
				<div class="modal-body" id="reverse_body">


				</div>


			</div>

		</div>

	</div>

	<div class="modal inmodal fade" id="past_medication_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Past Medication</h4>
				</div>
				<div class="modal-body" id="past_medication_body">
				</div>
			</div>
		</div>
	</div>

	<div class="modal inmodal fade" id="plan_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Plan</h4>
				</div>
				<div class="modal-body" id="plan_body">
				</div>
			</div>
		</div>
	</div>








	<style>
		/* Make modal 90% of screen width */
		#encounters_modal .modal-dialog.modal-xl {
			width: 90%;
			max-width: 90%;
			margin: 30px auto;
		}

		/* Ensure table fits inside modal and wraps content */
		#encounters_body table {
			width: 100%;
			table-layout: fixed;
		}

		#encounters_body table td,
		#encounters_body table th {
			word-wrap: break-word;
			overflow-wrap: break-word;
		}

		/* Add horizontal scroll for very small screens if needed */
		#encounters_body {
			overflow-x: auto;
		}
	</style>

	<div class="modal inmodal fade" id="encounters_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Plan</h4>
				</div>
				<div class="modal-body">
					<div class="" id="encounters_body_form"></div>
					<hr>
					<div class="" id="encounters_body"></div>
				</div>
			</div>
		</div>
	</div>


	<div class="modal inmodal fade" id="bio_data_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Patient Biodata</h4>
				</div>
				<div class="modal-body" id="bio_data_body">
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


	<div class="modal inmodal fade" id="edit_expiry_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Edit Expiry Date</h4>
				</div>
				<div class="modal-body" id="edit_expiry_body">
				</div>
			</div>
		</div>
	</div>


	<div class="modal inmodal fade" id="print_prescription" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Print Prescription</h4>
				</div>
				<div class="modal-body" id="bio_data_body">

					<?php if (isset($_POST['print_presciptn'])) {
						$hosp_no = $_POST['hospital_no'];
						$patient_name = $_POST['patient_name'];
						$patient_age = $_POST['patient_age'];

						if (!empty($_REQUEST['inv_all'])) {
							$pro_inv = $_REQUEST['inv_all'];
					?>

							<button onclick="print_patient_prescriptn('print_presc')" class="btn btn-sm btn-success">Print Prescription</button>

							<div class="print_presc" style="width:100%; font-family:Arial, sans-serif;">

								<?php
								// Get Doctor Name from first prescription item
								$doctor_name = '';

								if (!empty($pro_inv)) {
									$first_item = explode('__', $pro_inv[0]);

									// index 16 contains created_by
									$doctor_name = $first_item[16];
								}
								?>

								<table width="100%" border="0" cellpadding="2" cellspacing="0"
									style="font-size:14px; line-height:1.2;"
									bgcolor="#FFFFFF">

									<!-- Header -->
									<tr>
										<td colspan="2" align="center" style="padding-bottom:3px;">


											<img src="../img/logo.png" width="70" height="40" />

											<div style="font-size:13px; margin-top:2px;">
												<strong>RC: 7093868</strong>
											</div>

											<h3 style="margin:0;">DRUG PRESCRIPTION</h3>
										</td>
									</tr>

									<tr>
										<td colspan="2">
											<hr style="margin:3px 0;">
										</td>
									</tr>

									<!-- Patient Details -->
									<tr>
										<td width="35%">
											<strong>Hospital No:</strong>
										</td>
										<td width="65%">
											<?php echo $hosp_no; ?>
										</td>
									</tr>

									<tr>
										<td>
											<strong>Patient Name:</strong>
										</td>
										<td>
											<?php echo $patient_name; ?>
										</td>
									</tr>

									<tr>
										<td>
											<strong>Age:</strong>
										</td>
										<td>
											<?php
											preg_match('/\d+/', $age, $matches);
											echo $matches[0];
											?>
										</td>
									</tr>

									<tr>
										<td>
											<strong>Doctor:</strong>
										</td>
										<td>
											<?php echo $doctor_name; ?>
										</td>
									</tr>

									<!-- Prescription Table -->
									<tr>
										<td colspan="2" style="padding-top:5px;">

											<table width="100%" border="1" cellspacing="0" cellpadding="3"
												style="border-collapse:collapse; font-size:13px;">

												<tr bgcolor="#f2f2f2">
													<th width="8%">#</th>
													<th width="37%">Drug</th>
													<th width="55%">Prescription</th>
												</tr>

												<?php
												$cnt = 0;

												for ($i = 0; $i < count($pro_inv); ++$i) {

													$inv_id = $pro_inv[$i];

													$break = explode('__', $inv_id);

													$drug_sn       = $break[0];
													$item_services = $break[1];
													$remarks       = $break[9];

													$cnt++;
												?>

													<tr>
														<td>
															<?php echo $cnt; ?>
														</td>

														<td>
															<?php echo $item_services; ?>
														</td>

														<td>
															<?php echo $remarks; ?>
														</td>
													</tr>

												<?php } ?>

											</table>

										</td>
									</tr>

									<!-- Date -->
									<!-- Generated By -->
									<tr>
										<td colspan="2" style="padding-top:5px; font-size:13px;">
											<strong>Date:</strong>
											<?php echo date('d-m-Y h:i A'); ?>

											&nbsp;&nbsp;&nbsp;

											<strong>Generated By:</strong>
											<?php echo $_SESSION['fullname']; ?>
										</td>
									</tr>

								</table>

								<hr style="margin:5px 0;">

								<!-- Footer -->
								<div align="center" style="font-size:12px; line-height:1.2;">

									<strong>
										<?= $_SESSION['h_address'] ?>
										<br>

										<?php
										echo $_SESSION['h_phone'];

										if ($_SESSION['h_code'] == 'mluth') {
											echo ' | Email: pharmacy@mluth.com';
										}
										?>
									</strong>

								</div>

							</div>


							<hr>

							<div class="alert alert-info">
								<h3 style="color:blue;">Sending Drug Availability Information to Patients</h3>

								<div class="form_sep">
									<select name="searchdrug" class="form-control" required>
										<option selected="selected" value="">Select mode of Sending</option>
										<option value="Email">Email</option>
										<option value="SMS">SMS</option>
									</select>

									<hr>

									<button type="button" class="btn btn-sm btn-success" onclick="send_msg()"><i class="fa fa-arrow"></i>&nbsp;Send Now</button>
								</div>

							</div>

						<?php
						} else { ?>
							<h2 style="color:red; ">Invalid Selection!</h2>
					<?php }
					}

					?>



				</div>
			</div>
		</div>
	</div>


	<div class="modal inmodal fade" id="reverse_pay_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Reverse Payment</h4>
				</div>



				<input type='hidden' value="" name="" id="reverse_sale_id">

				<div class="modal-body" id="reverse_pay_body">

					<hr>
					<h1 style="color:red;">

						ARE YOU SURE YOU WANT TO REVERSE MONEY TO PATIENT ACCOUNT?


					</h1>
					<hr>


					<button type="button" class="btn btn-danger btn-xs dropdown-toggle" id="reverse_pay_final" onClick="reverse_pay_final()">Reverse Payment Now</button>

				</div>
			</div>
		</div>
	</div>


	<div class="modal inmodal fade" id="med_pop_up" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Medication History</h4>
				</div>
				<div class="modal-body" id="med_pop_upbody">



					<div class="sideScrollStyle3">
						<?php

						$stmt = $db->prepare('SELECT p.serv_group, p.remarks, p.pay, p.claim_amt, p.date_entry, p.prepared_by,p.created_by, p.paystatus, p.qty, p.item_services, p.dsp_by, stock_table.product_name, stock_table.dosage, stock_table.strength, p.sn, p.cr 
						FROM patient_ap_services AS p 
						INNER JOIN stock_table ON p.drug_sn = stock_table.sn 
						WHERE hospital_no = :hos_no 
						AND invoice_status = :invoice_status 
						AND serv_group = :serv_group 
						AND drug_status = :drug_status 
						ORDER BY transact_date DESC 
						LIMIT 200');

						$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
						$stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR); // Assuming '1' is the value for invoice_status
						$stmt->bindParam(':serv_group', $serv_group, PDO::PARAM_STR);         // Assuming 'Pharmacy' is the value for serv_group
						$stmt->bindParam(':drug_status', $drug_status, PDO::PARAM_STR);       // Assuming '1' is the value for drug_status

						$invoice_status = '1';     // Set the value for invoice_status
						$serv_group = 'Pharmacy';  // Set the value for serv_group
						$drug_status = '1';        // Set the value for drug_status

						$stmt->execute();

						if ($stmt->rowCount() > 0) { ?>
							<table class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
									<tr>
										<th data-toggle="true" width="20%">Drug</th>
										<th data-hide="phone,tablet">Prescription</th>
										<th data-hide="phone,tablet" width="5%">Qty</th>
										<th data-hide="phone,tablet" width="7%">Pay</th>
										<th data-hide="phone,tablet" width="7%">Claim</th>
										<th data-hide="phone,tablet" width="15%">Date/Entered By</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$paying = 0;
									$claiming = 0;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
									?>
										<tr>
											<td><?php echo $row['product_name']; ?></td>
											<td><?php if ($row['remarks'] != '') {
													echo $row['remarks'];
												} else {
													echo '';
												} ?></td>
											<td><?php echo $row['qty']; ?></td>
											<td><?php echo number_format($row['pay'], 2, '.', ','); ?></td>
											<td><?php echo number_format($row['claim_amt'], 2, '.', ','); ?></td>
											<td><?php echo date('d M,Y', strtotime($row['date_entry'])) . '<br>' . date('h:i:s a', strtotime($row['date_entry']));
												echo '<br><b>' . $row['dsp_by'] . '</b>'; ?></td>
										</tr>
									<?php } ?>

								</tbody>
							</table>

					</div>


				<?php } else { ?>
					<strong>No Medication Records to Display!</strong>
				<?php } ?>
				<form method="post" action="index.php?<?php echo 'presc&hos_no=' . $hos_no; ?>">
					<button class="btn btn-primary btn-xs" type="submit" name="skip_popup" id="skip_popup" value="<?php echo $hos_no; ?>">Don't Show again </button>
				</form>

				</div>
			</div>
		</div>
	</div>
</div>

<?php

$stmt = $db->prepare('SELECT reason_adm, sn,app_no FROM admission WHERE (adm_status=3 OR adm_status=0) AND hospital_no = ? LIMIT 1');
$stmt->execute([$hos_no]);
list($reason_adm, $sn, $app_no) = $stmt->fetch() ?: [null, null, null];

$stmt = $db->prepare("SELECT notes FROM notes WHERE notes_type='plan' AND hospital_no = ? AND app_no = ? LIMIT 1");
$stmt->execute([$hos_no, $app_no]);
list($notes) = $stmt->fetch() ?: [null];

?>




<div class="modal inmodal fade" id="pharm_clinical_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="display: flex; flex-direction: column; height: 100vh; width: 90%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""> Clinical Pharmacy Unit</h4>
			</div>
			<div class="modal-body">
				<div>
					<div>
						<label for=""> Select Form:</label>
						<select name='pharmacy_form' id='pharmacy_form' class="form-control" required>
							<option selected='selected' value=''>Search & Select</option>
							<option value='Patient_discharge_councelling_form'>Patient_discharge_councelling_form</option>
							<option value='MEDICATION_INTERVENTION_FORM'>MEDICATION INTERVENTION FORM</option>
							<option value='MEDICATION_RECONCILE_FORM'>MEDICATION RECONCILAITION FORM</option>


						</select>
					</div>
					<br>
					<?php include 'clinical_forms_markup.php'; ?>

					<input type="hidden" name="admission_sn_clinical_form" value="<?php echo $row['sn']; ?>" />
					<input type="hidden" name="hospital_no_clinical_form" value="<?php echo $hos_no; ?>" />
					<input type="hidden" name="appt_no_clinical_form" value="<?php echo $app_no; ?>" />
					<input type="hidden" name="patient_name_clinical_form" value="<?php echo $patient_name; ?>" />
					<p class="text-center">
						<button class='btn btn-success btn-sm' type='submit' name='send_clinical_form_btn' id='send_clinical_form_btn' onclick="clinical_form_submit()">Save for Print</button>
					</p>
				</div>
				<hr>
				<h3>Forms history</h3>
				<?php
				try {
					// Fetch only the specified forms('Patient_discharge_councelling_form', 'MEDICATION_INTERVENTION_FORM', 'MEDICATION_RECONCILE_FORM') from the database
					$stmt = $db->prepare("
						SELECT id, hospital_no, app_no, service, created_at, created_by
						FROM notes_services
						WHERE hospital_no = :hospital_no
						AND service IN ('Patient_discharge_councelling_form', 'MEDICATION_INTERVENTION_FORM', 'MEDICATION_RECONCILE_FORM')
					");
					$stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
					$stmt->execute();
					$clinical_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$error_message = 'Error: ' . $e->getMessage();
					error_log($error_message);
					echo "Error while fetching clinical forms history";
				}
				?>
				<?php if (!empty($clinical_forms)): ?>
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Hospital No</th>
								<th>Appointment No</th>
								<th>Service</th>
								<th>Created At</th>
								<th>Created By</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($clinical_forms as $form): ?>
								<tr>
									<td><?= htmlspecialchars($form['hospital_no']); ?></td>
									<td><?= htmlspecialchars($form['app_no']); ?></td>
									<td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $form['service']))); ?></td>
									<td><?= htmlspecialchars($form['created_at']); ?></td>
									<td><?= htmlspecialchars($form['created_by']); ?></td>
									<td>
										<button class="btn btn-primary btn-xs print-btn" data-id="<?= $form['id']; ?>" onclick="print_clinical_form(this)">Print</button>

										<button class="delete-btn btn btn-danger btn-xs print-btn" data-id="<?= $form['id']; ?>" onclick="delete_clinical_form(this)">Delete</button>

									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<p class="alert alert-warning">No forms found for this patient.</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="in_patient_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Admitted Patients</h4>
			</div>
			<div class="modal-body">

				<div id="adm-table-container"></div>
			</div>
		</div>
	</div>
</div>



<script>
	function UpdateCost() {
		var p_list = document.getElementById("p_list").value;
		var grand_total = 0;
		var check;

		for (let i = 1; i <= p_list; i++) {
			var chk = document.getElementById("add_m_" + i).value;
			var edit_price_ = document.getElementById("edit_price_" + i).value;
			var qty = document.getElementById("qty_" + i).value;

			check = $("#add_m_" + i).is(":checked");

			if (check) {
				// Remove commas and parse the values
				var edit_price = parseFloat(edit_price_.replace(/,/g, ''));
				var quantity = parseFloat(qty.replace(/,/g, ''));

				// Calculate total
				var total = quantity * edit_price;

				// Format total
				var formattedTotal = new Intl.NumberFormat().format(total);
				document.getElementById("total_" + i).value = formattedTotal;

				// Update grand total
				grand_total += total;

				// Format grand total
				var formattedGrandTotal = new Intl.NumberFormat().format(grand_total);
				document.getElementById("grand_total").value = formattedGrandTotal;

			} else {
				document.getElementById("total_" + i).value = 0;
				document.getElementById("grand_total").value = 0;
			}
		}

		// If you want to truncate the grand total to two digits
		//  var truncatedGrandTotal = Math.floor(grand_total / 1000); // Truncate to thousands
		//document.getElementById("grand_total").value = truncatedGrandTotal;
	}


	const options = document.querySelectorAll('#medications option');
	options.forEach(option => {
		if (option.text.includes('exp in')) {
			option.style.color = 'red'; // Change color to red for expired items
		}
	});


	function clinical_form_submit() {



		// Get the selected pharmacy form value
		var pharmacyForm = $('#pharmacy_form').val();

		if (!pharmacyForm) {
			alert('Please select a form.');
			return;
		}

		// Get the corresponding div content
		var formBody = $('#refferal_notes_' + pharmacyForm).val();

		// Create an object to hold the form data
		var formData = {
			pharmacy_form: pharmacyForm,
			pharm_form_body: formBody,
			admission_sn: $('[name="admission_sn_clinical_form"]').val(),
			hosp_no: $('[name="hosp_no_clinical_form"]').val(),
			hospital_no: $('[name="hospital_no_clinical_form"]').val(),
			appt_no: $('[name="appt_no_clinical_form"]').val(),
			patient_name: $('[name="patient_name_clinical_form"]').val()
		};

		// Make the AJAX request
		$.ajax({
			url: 'fetch_set.php',
			type: 'POST',
			data: formData,
			success: function(response) {

				toastr.success('Saved Succesfully, now printing', 'Attention', {
					timeOut: 5000
				})

				// Open a new print window with the contents of the corresponding div
				var printWindow = window.open('', '', 'height=600,width=800');
				printWindow.document.write('<html><head><title>Print</title></head><body>');
				printWindow.document.write(response);
				printWindow.document.write('</body></html>');
				printWindow.document.close();
				setTimeout(function() {
					printWindow.print();
					printWindow.close();
				}, 500);

				$('#pharmacy_form').val('');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error('Error:', textStatus, errorThrown);
				toastr.success('Error While saving Form', 'Attention', {
					timeOut: 5000
				})
			}
		});
	}

	function delete_clinical_form(obj) {

		// Get the ID from the data-id attribute
		var id = $(obj).data('id');
		var row = $(obj).closest('tr');

		if (confirm('Are you sure you want to delete this record?')) {
			// Make the AJAX request
			$.ajax({
				url: 'fetch_set.php',
				type: 'POST',
				data: {
					clinical_form_del_id: id
				},
				success: function(response) {
					row.remove();
					toastr.success("Form deleted successfully", 'Attention', {
						timeOut: 5000
					})
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error('Error:', textStatus, errorThrown);
					toastr.success("Error While deleting Form", 'Attention', {
						timeOut: 5000
					})
				}
			});
		}

	}

	function print_clinical_form(button) {
		// Get the ID from the data-id attribute
		var id = $(button).data('id');

		// Make the AJAX request to fetch the clinical form notes
		$.ajax({
			url: 'fetch_set.php',
			type: 'POST',
			data: {
				clinical_form_print_id: id
			},
			success: function(response) {
				//response contains the HTML of the clinical form
				if (response) {
					// Create a new window for printing
					var printWindow = window.open('', '_blank');

					// Write the content to the new window
					printWindow.document.write(`
                    <html>
                    <head>
                        <title>Print Clinical Form</title>
                        <link rel="stylesheet" href="path/to/your/styles.css">
                    </head>
                    <body>
                        ${response}
                    </body>
                    </html>
                `);

					// Close the document to ensure all content is loaded
					printWindow.document.close();

					// Print the content after a slight delay to ensure everything is rendered
					setTimeout(function() {
						printWindow.print();
						printWindow.close();
					}, 500);
				} else {
					alert('No data found for this clinical form.');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error('Error:', textStatus, errorThrown);
				toastr.error("Error while printing", 'Attention', {
					timeOut: 5000
				})
			}
		});
	}




	function reverse_drug_now(mode, tr) {

		var app_no = $('#app_no').val();
		var hosp_no = $('#hosp_no').val();
		var sale_sn = $('#sale_sn').val();
		var drug_sn = $('#drug_sn').val();
		var EX_or_IN = $('#EX_or_IN').val();
		var where = 'pharmacy';
		var names = $('#names').val();
		var specify_qtyy = $('#specify_qtyy').val();
		var all_qtyy = $('#all_qtyy').val();

		if (mode == 'specify_qty' && specify_qtyy > all_qtyy) {

			toastr.error('Invalid Quantity!', 'Error', {
				timeOut: 3000
			})
			exit;

		} else {

			$.ajax({
				url: "../inc/inv_pro.php",
				method: "POST",
				data: {
					reverse_drug: mode,
					hosp_no: hosp_no,
					app_no: app_no,
					sale_sn: sale_sn,
					drug_sn: drug_sn,
					EX_or_IN: EX_or_IN,
					where: where,
					specify_qtyy: specify_qtyy,
					all_qtyy: all_qtyy
				},
				success: function(data) {

					document.getElementById("reverse_btn").disabled = true;

					var jsonn = JSON.parse(data);
					if (jsonn["status"] == 1) {
						document.getElementById("reverse_btn").disabled = true;
						toastr.error(jsonn["message"], 'Attention', {
							timeOut: 5000
						})
					} else {
						toastr.success(jsonn["message"], 'Attention', {
							timeOut: 5000
						})
						document.getElementById("reverse_btn2").disabled = true;
						document.getElementById("reverse_btn").disabled = true;
						///window.location.href = 'index.php?presc&hos_no=' + hosp_no;
					}
				}
			});
		}
	}



	function refill_drug_now(hosp_no, tr) {

		var app_no = $('#app_no').val();
		var hosp_no = $('#hosp_no').val();
		var sale_sn = $('#sale_sn').val();
		var drug_sn = $('#drug_sn').val();
		var EX_or_IN = $('#EX_or_IN').val();
		var where = $('#where').val();
		var old_qty = $('#old_qty').val();
		var refill_qty = $('#refill_qty').val();
		var claim_amt = $('#claim_amt').val();
		var pay = $('#pay').val();

		//alert(refill_qty);

		$.ajax({
			url: "../inc/inv_pro.php",
			method: "POST",
			data: {
				refill: true,
				hosp_no: hosp_no,
				app_no: app_no,
				sale_sn: sale_sn,
				drug_sn: drug_sn,
				EX_or_IN: EX_or_IN,
				where: where,
				old_qty: old_qty,
				refill_qty: refill_qty,
				claim_amt: claim_amt,
				pay: pay
			},
			success: function(data) {

				document.getElementById("refill_btn").disabled = true;

				var jsonn = JSON.parse(data);
				if (jsonn["status"] == 1) {
					document.getElementById("refill_btn").disabled = false;
					toastr.error(jsonn["message"], 'Attention', {
						timeOut: 5000
					})
				} else {
					toastr.success(jsonn["message"], 'Attention', {
						timeOut: 5000
					})
				}

			}
		});


	}



	function dispense_RQ_Dispen(dispense_RQ_Dispen_TYPE, hosp_no, sn) {

		document.getElementById("dispense_btn_RQ").disabled = true;

		$.ajax({
			url: "../inc/inv_pro.php",
			method: "POST",
			data: {
				hosp_no: hosp_no,
				list: sn,
				dispense: dispense_RQ_Dispen_TYPE
			},
			success: function(data) {

				var jsonn = JSON.parse(data);

				if (jsonn["status"] == 1) {
					document.getElementById("dispense_btn").disabled = false;
					toastr.error(jsonn["message"], 'Attention', {
						timeOut: 5000
					})
				} else {
					toastr.success(jsonn["message"], 'Attention', {
						timeOut: 5000
					})
				}
			}
		});
	}


	function dispense_process(dispense_all_cr, hosp_no) {
		/// confirm

		if (dispense_all_cr == 'dispense_all_cr') {
			var rr = confirm("Confirm dispensing of selected medication(s) on credit or to account billing?");
		} else {
			rr = true;
		}


		if (rr === true) {

			var favorite = [];
			$.each($("input[name='inv[]']:checked"), function() {
				if ($(this).val() != '') {
					favorite.push($(this).val());
				}
			});
			var v = favorite.join(",");

			document.getElementById("dispense_btn").disabled = true;

			$.ajax({
				url: "../inc/inv_pro.php",
				method: "POST",
				data: {
					hosp_no: hosp_no,
					list: v,
					dispense: dispense_all_cr
				},
				success: function(data) {
					var jsonn = JSON.parse(data);

					if (jsonn["status"] == 1) {
						document.getElementById("dispense_btn").disabled = false;
						toastr.error(jsonn["message"], 'Attention', {
							timeOut: 5000
						})
					} else {
						toastr.success(jsonn["message"], 'Attention', {
							timeOut: 5000
						})

						setTimeout(function() {
							window.location.href = "index.php?presc&hos_no=" + hosp_no; // ← put your URL here
						}, 3000);



						/* 			// Refresh after 2 seconds
									setTimeout(function() {
										location.reload();
									}, 2000); */
					}
				}
			});

		}

	}


	function handleExpired(sn) {
		$.ajax({
			url: "edit_expiry_date.php",
			method: "POST",
			data: {
				exp_date: sn
			},
			success: function(data) {

				$('#edit_expiry_modal').modal('show');
				$('#edit_expiry_body').html(data);
			}
		});
	}


	function save_edit_product_stock() {

		var expired = document.getElementById('expired_edit_2').value;
		var stock_snn = document.getElementById('stock_snn').value;
		$.ajax({
			url: "edit_expiry_date.php",
			method: "POST",
			data: {
				expired: expired,
				stock_snn: stock_snn
			},
			success: function(data) {
				alert(data);

				///document.getElementById('pharm_doctor_chat_displayed').innerHTML = data;

			}
		});
	}

	function reverse_pay(sale_sn) {

		document.getElementById("reverse_pay1").disabled = true;
		document.getElementById("reverse_sale_id").value = sale_sn;
		$('#reverse_pay_modal').modal('show');
		$('#reverse_pay_body').html(data);

	}

	function reverse_pay_final() {

		var reverse_sale_id = document.getElementById("reverse_sale_id").value

		///alert(reverse_sale_id);

		$.ajax({
			url: "../inc/inv_pro.php",
			method: "POST",
			data: {
				reverse_sale_id: reverse_sale_id
			},
			success: function(data) {

				var jsonn = JSON.parse(data);


				if (jsonn["status"] == 1) {
					/// make this modal reverse_pay_modal disappear
					document.getElementById('reverse_pay_final').disabled = false;
					toastr.error(jsonn["message"], 'Attention', {
						timeOut: 5000
					})
				} else {
					document.getElementById('reverse_pay_final').disabled = true;
					document.getElementById('dispense_btn').disabled = true;
					document.getElementById("reverse_pay_final").innerHTML = 'Done';
					toastr.success('Successful', 'Success', {
						timeOut: 5000
					})
				}

			}
		});

	}

	function payNow(sale_sn, target, hospital_no) {


		if (target == 'investigation') {
			var rr = confirm("Are you sure you want to Pay from Patient's Deposit?");
		} else {
			rr = true;
		}

		if (rr === true) {

			document.getElementById('pay_now').innerHTML = "Wait ...";
			document.getElementById('pay_now').disabled = true;

			$.ajax({
				url: "../payfrom_wallet.php",
				method: "POST",
				data: {
					sale_sn: sale_sn
				},
				success: function(data) {

					var jsonn = JSON.parse(data);
					if (jsonn["status"] == 1) {

						document.getElementById("pay_now").innerHTML = 'Pay from Wallet';
						document.getElementById('pay_now').disabled = false;
						toastr.error(jsonn["message"], 'Attention', {
							timeOut: 5000
						})
					} else {

						document.getElementById("pay_now").innerHTML = 'Done';
						toastr.success('Successful', 'Success', {
							timeOut: 5000
						})


						if (target == 'pharmacy') {
							window.location = 'index.php?presc&hos_no=' + hospital_no;
						}
					}

				}
			});
		}
	}
</script>
<!-- drug reminder modal js -->
<script>
	function setReminderModalOpen(drugSn, hosNo) {
		document.getElementById('drug_sn_rem').value = drugSn;
		document.getElementById('hos_no_rem').value = hosNo;

		var xhr = new XMLHttpRequest();
		xhr.open('GET', `fetch_set.php?drug_sn_rem_hist=${drugSn}&hos_no_rem_hist=${hosNo}`, true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				// Inject the response (table with reminders) into the modal
				document.getElementById('existing_drug_reminder_list').innerHTML = xhr.responseText;

				// Show the modal using Bootstrap's modal functionality
				var reminderModal = $('#reminderModal');
				reminderModal.modal('show');
			}
		};
		xhr.send();
	}
</script>