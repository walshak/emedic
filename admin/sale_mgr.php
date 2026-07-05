<?php

if (isset($_POST['save_update'])) {

	$setdate = date('Y-m-d');
	$transc_code = $_POST['transc_code'];

	if ($_POST['age'] > 0) {
		$age = $_POST['age'];
		$C_date = date("Y-m-d");
		$datebate = date("Y-m-d H:i:s", strtotime($C_date . " -$age year"));
	} else {
		$datebate = $_POST['dob'];
	}



	$discount_set = 0;
	$referral = $_POST['referral'];
	$stmt = $db->query("SELECT sn FROM patient_discount where individual_group_no='$referral' and status='On-going'");
	if ($stmt->rowCount() > 0) {
		$discount_set = 1;
	}


	// 
	// Prepare the UPDATE statement
	$stmt = $db->prepare(
		"UPDATE pharm_ext 
		SET cust_name = :cust_name, gender = :gender, dob = :dob, phone = :phone, address = :address, email_address = :email_address, referral = :referral, date_ap = :date_ap, captured_by = :captured_by, discount_set = :discount_set 
		WHERE transc_code = :transc_code"
	);

	// Bind the parameters
	$stmt->bindParam(':cust_name', strtoupper($_POST['name']));
	$stmt->bindParam(':gender', $_POST['gender']);
	$stmt->bindParam(':dob', $datebate);
	$stmt->bindParam(':phone', $_POST['phone']);
	$stmt->bindParam(':address', $_POST['addr']);
	$stmt->bindParam(':email_address', $_POST['email_address']);
	$stmt->bindParam(':referral', $_POST['referral']);
	$stmt->bindParam(':date_ap', $setdate);
	$stmt->bindParam(':captured_by', $_SESSION['fullname']);
	$stmt->bindParam(':discount_set', $discount_set);
	$stmt->bindParam(':transc_code', $transc_code);
	$stmt->execute();

	$stmt = $db->prepare(
		"UPDATE lab_manage 
		SET patient_name = :patient_name WHERE patient = :patient"
	);

	// Bind the parameters
	$stmt->bindParam(':patient_name', strtoupper($_POST['name']));
	$stmt->bindParam(':patient', $transc_code);
	$stmt->execute();

	header("location:index.php?sale=$transc_code&success");
	/// index.php?sale=EX2

}



if (isset($_GET['dl'])) {

	$sn = $_GET['dl'];
	$paystatus = 0;


	$deleteSQL = "DELETE FROM patient_ap_services WHERE (sn = :sn or remarks = :remarks) and paystatus = :paystatus";
	$stmt = $db->prepare($deleteSQL);
	$stmt->bindValue(':sn', $sn, PDO::PARAM_INT);
	$stmt->bindValue(':remarks', $sn, PDO::PARAM_INT);
	$stmt->bindValue(':paystatus', $paystatus, PDO::PARAM_INT);
	$stmt->execute();

	// Check if the row was deleted
	if ($stmt->rowCount() > 0) {
		$deleteSQL = "DELETE FROM special_package_booking WHERE ap_service_id = :ap_service_id";
		$stmt = $db->prepare($deleteSQL);
		$stmt->bindValue(':ap_service_id', $sn, PDO::PARAM_INT);
		$stmt->execute();
	}
}



if (isset($_POST['pay_now'])) {
}

if (isset($_POST["add_service"]) or isset($_POST["add_service_other"])) {

	$interest = $_POST['interest'];
	$add_minus = $_POST['add_minus'];
	$payment_mode = $_POST['payment_mode'];
	$insurance_no = $_POST['insurance_no'];
	$insurance = $_POST['insurance_type'];
	$EX_IN = $_POST['EX_IN'];
	$patient_nhis_access = $_POST['patient_nhis_access'];
	$dept_id = $_SESSION['service_dept'];
	$hosp_no = $_POST['hosp_no'];
	$appt_no = $_POST['appt_no'];
	$show_billing = $_POST['show_billing'];

	$setdate = date("Y-m-d H:i:s");

	$item_service = '';
	if (!empty($_POST['item_service'])) {
		$item_service = $_POST['item_service'];
	} elseif (!empty($_POST['item_consumables'])) {
		$item_service = $_POST['item_consumables'];
	}


	if (
		isset($_POST["add_service_other"]) && $_POST["service_dept"] != '' && $_POST["amount"] != ''
		and $show_billing == 'checked' && $_SESSION['rights'] != 'PH'
	) {

		$new_qty = $_POST['qty_other'];
		$cat_type = $_POST['service_dept'];
		$serv_group = $_POST['service_dept'];
		$hosp_price = $_POST['amount'];
		$ext_price = $_POST['amount'];
		$item_service = $_POST["special_service"];
		$nhis_price = '0';
		///$cat_type = 'External Services';
		///$serv_group = 'External Services';
		$sn = '00';
		$coverage = '';
		$service_access = '';

		$new_qty = (!empty($_POST['qty_other']) && $_POST['qty_other'] != '0')
			? $_POST['qty_other']
			: 1;

		$error_status = '0';
	} elseif (
		(!empty($_POST['item_service'])) || (!empty($_POST['item_consumables']))
	) {

		$new_qty = $_POST['qty'];
		if (empty($new_qty) || $new_qty == '0') {
			$new_qty = 1; // Default to 1 if invalid
		}

		$parts = explode("__", $item_service);
		$sn = $parts[0];
		$item_service = $parts[1];
		$coverage = $parts[2];
		$service_access = $parts[3];
		$ext_price = $parts[4];
		$nhis_price = $parts[5];
		$dept_id = $parts[6];
		$hosp_price = $parts[7];
		$serv_group = $parts[8];  /// price table	
		$cat_type = $parts[9];
		$duration = $parts[10];


		///exit;


		if ($cat_type == '') {
			$cat_type = $serv_group;
		}

		$error_status = '0';
	} else {

		$error_status = '1';
		$error_msg = 'Error occurred: Invalid transaction field. Please make sure all required fields are not empty';
	}


	if ($error_status == '0') {

		if ($_SESSION['rights'] == 'PH' || !empty($_POST['item_consumables'])) {
			$type_price = 'Pharm';

			if ($_SESSION['rights'] == 'PH') {
				$cat_type = 'Pharmacy';
				$serv_group = 'Pharmacy';
			} else {

				$cat_type = 'Consumable';
				$serv_group = 'Nursing Services';
			}

			$dept_id = $_SESSION['dept_id'];
			//// CHECK IF QTY IS ENOUGH

			$stmt = $db->prepare("SELECT bal
			FROM stock_table_inven
			WHERE stock_sn = :sn
			  AND cust_patient_id = :dept_id
			ORDER BY sn DESC
			LIMIT 1");

			$stmt->execute([
				':sn'       => $sn,
				':dept_id'  => $dept_id
			]);

			$bal_stock = ($row = $stmt->fetch(PDO::FETCH_ASSOC)) ? $row['bal'] : 0;

			if ($bal_stock == 0) {
				////	

				$stmt = $db->prepare("SELECT qty,price_markup,stock_total_unit,buying_cost FROM stock_table WHERE sn = :sn");
				$stmt->bindValue(':sn', $sn, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
					$bal_stock = $rwx['qty'];
					$price_markup = $rwx['price_markup'];
					$units = $rwx['stock_total_unit'];
					$buying_cost = $rwx['buying_cost'];

					if ($price_markup > 0 && $buying_cost > 0) {
						$amt = $buying_cost / $units;
						$markup_amount = ($amt * $price_markup) / 100;
						$selling_price = $amt + round($markup_amount, 3);

						if ($selling_price > 0) {
							$ext_price = $selling_price;
							$hosp_price = $selling_price;
						}
					}
				} else {
					$bal_stock = 0;
				}
			}
			$error = 0;

			if ($new_qty > $bal_stock) {
				$error = 1;
				$err_msg = 'Invalid Quantity';
			}
		} else {
			$type_price = 'Prices_table';
			$error = 0;
		}

		if ($EX_IN == 'IN') {
			$target_sn = $sn;
			$_tariff_table = "hmo_stocks_tariff";
			include("../inc/price_calc.php");
		} else {
			/// EXTERNAL PATIENTS  ext_price
			$ccop_int_charge = 0;
			$claim_amt = 0;
			$amt_paying = ($ext_price > 0) ? $ext_price : $hosp_price;
			$pay_mode = 'cash';
		}

		include_once("../inc/utilities.php");
		$invoice_no = INV();

		if ($error == 0) {
			$amt_paying = $amt_paying * $new_qty;
			$claim_amt = $claim_amt * $new_qty;
			$query = save_transc(
				$hosp_no,
				$appt_no,
				$patient_nhis_access,
				$serv_group,
				$dept_id,
				$sn,
				$cat_type,
				$item_service,
				$claim_amt,
				$ccop_int_charge,
				$hosp_price,
				$new_qty,
				$invoice_no,
				$setdate,
				$amt_paying,
				$pay_insured,
				$pay_mode,
				$duration,
				$db
			);
			$error_status = '2';
			$error_msg = 'Successfully!';
		} else {
			$error_status = '1';
			$error_msg = 'Error Occured: Invalid Quantity!';
		}
	} else {
		$error_status = '1';
		$error_msg = 'Error Occured: Invalid Transactions!';
	}
}

function save_transc($hosp_no, $appt_no, $patient_nhis_access, $serv_group, $dept_id, $sn, $cat_type, $item_service, $claim_amt, $ccop_int_charge, $hosp_price, $new_qty, $invoice_no, $setdate, $amt_paying, $pay_insured, $pay_mode, $duration, $db)
{
	session_start();
	if (!isset($_SESSION['fullname']) || !isset($_SESSION['dept_id'])) {
		return; // Prevent inserting without session context
	}

	$tag = 'sales';
	$setdate = date('Y-m-d H:i:s');
	$setdate2 = date('Y-m-d');

	$drug_status = '0';
	$invoice_status = '1';
	$process_claim = '0';
	$cr = '0';
	$paystatus = '0';
	if (empty($ccop_int_charge)) {
		$ccop_int_charge = 0;
	}
	try {
		// Check for duplicate entry first
		$stmt = $db->prepare("SELECT 1 FROM patient_ap_services 
            WHERE hospital_no = :hospital_no 
            AND app_no = :app_no 
            AND cat_type = :cat_type 
            AND drug_sn = :drug_sn 
            AND item_services = :item_services 
            AND qty = :qty 
            AND prepared_by = :prepared_by 
            AND DATE(date_entry) = :date_entry 
            AND paystatus = :paystatus 
            LIMIT 1");

		$stmt->execute([
			':hospital_no' => $hosp_no,
			':app_no' => $appt_no,
			':cat_type' => $cat_type,
			':drug_sn' => $sn,
			':item_services' => $item_service,
			':qty' => $new_qty,
			':prepared_by' => $_SESSION['fullname'],
			':date_entry' => $setdate2,
			':paystatus' => $paystatus
		]);

		if ($stmt->rowCount() == 0 && $_SESSION['dept_id'] != '') {
			$insertSQL = $db->prepare("INSERT INTO patient_ap_services 
                (app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, tag, hosp_price, claim_amt, interest, qty, remarks, drug_status, invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, dsp_by, date_entry, transact_date, pay, pay_mode, paystatus, process_claim, cr) 
                VALUES 
                (:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :tag, :hosp_price, :claim_amt, :interest, :qty, :remarks, :drug_status, :invoice_status, :invoice_no, :invoice_date, :invoice_by, :prepared_by, :dsp_by, :date_entry, :transact_date, :pay, :pay_mode, :paystatus, :process_claim, :cr)");

			$insertSQL->execute([
				':app_no' => $appt_no,
				':hospital_no' => $hosp_no,
				':access' => $patient_nhis_access,
				':serv_group' => $serv_group,
				':cat_type' => $cat_type,
				':dept_id' => $dept_id,
				':drug_sn' => $sn,
				':item_services' => $item_service,
				':tag' => $tag,
				':hosp_price' => $hosp_price,
				':claim_amt' => $claim_amt,
				':interest' => $ccop_int_charge,
				':qty' => $new_qty,
				':remarks' => $pay_insured,
				':drug_status' => $drug_status,
				':invoice_status' => $invoice_status,
				':invoice_no' => $invoice_no,
				':invoice_date' => $setdate,
				':invoice_by' => $_SESSION['fullname'],
				':prepared_by' => $_SESSION['fullname'],
				':dsp_by' => $_SESSION['fullname'],
				':date_entry' => $setdate,
				':transact_date' => NULL,
				':pay' => $amt_paying,
				':pay_mode' => $pay_mode,
				':paystatus' => $paystatus,
				':process_claim' => $process_claim,
				':cr' => $cr
			]);

			$lastInsertedID = $db->lastInsertId();

			if (strtoupper($cat_type) == 'SPECIAL PACKAGE') {
				if (isset($_POST['patient_name'])) {
					$patient_name = $_POST['patient_name'];
					$from_date = date('Y-m-d');
					$to_date = date('Y-m-d', strtotime($from_date . " +$duration days"));

					$stmt = $db->prepare("INSERT INTO special_package_booking 
                        (hospital_no, patient_name, service_id, service_title, service_amount, created_by, date_open, date_expire, ap_service_id) 
                        VALUES 
                        (:hospital_no, :patient_name, :service_id, :service_title, :service_amount, :created_by, :date_open, :date_expire, :ap_service_id)");

					$stmt->execute([
						':hospital_no' => $hosp_no,
						':patient_name' => $patient_name,
						':service_id' => $sn,
						':service_title' => $item_service,
						':service_amount' => $amt_paying,
						':created_by' => $_SESSION['fullname'],
						':date_open' => $from_date,
						':date_expire' => $to_date,
						':ap_service_id' => $lastInsertedID
					]);
				}
			}
		}
	} catch (PDOException $e) {
		// Handle/log your error properly
		error_log("Database error in save_transc: " . $e->getMessage());
		// Optionally display a user-friendly message or redirect
	}
}


if (isset($_POST["save"])) {

	$setdate = date('Y-m-d');
	$stmt = $db->query("SELECT sn FROM pharm_ext ORDER BY sn DESC LIMIT 1");
	if ($stmt->rowCount() > 0) {
		$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
		$SN = 1 + $row_rstSelect['sn'];
		$transc_code = 'EX' . $SN;
	} else {
		$SN = 1;
		$transc_code = 'EX' . $SN;
	}

	$sn = substr($transc_code, 2);

	if ($_POST['age'] > 0) {
		$age = $_POST['age'];
		$C_date = date("Y-m-d");
		$datebate = date("Y-m-d H:i:s", strtotime($C_date . " -$age year"));
	} else {

		$datebate = $_POST['dob'];
	}


	// Prepare the INSERT statement
	// Check if transc_code already exists
	$checkSQL = "SELECT COUNT(*) FROM pharm_ext WHERE transc_code = :transc_code";
	$checkStmt = $db->prepare($checkSQL);
	$checkStmt->bindParam(':transc_code', $transc_code);
	$checkStmt->execute();
	$exists = $checkStmt->fetchColumn();

	if ($exists == 0) {
		// Prepare the insert statement
		$stmt = $db->prepare(
			"INSERT INTO pharm_ext(sn, transc_code, cust_name, description, gender, dob, phone, address, email_address, referral, date_ap, captured_by) 
        VALUES (:sn, :transc_code, :cust_name, :description, :gender, :dob, :phone, :address, :email_address, :referral, :date_ap, :captured_by)"
		);

		// Bind the parameters
		$stmt->bindParam(':sn', $sn);
		$stmt->bindParam(':transc_code', $transc_code);
		$stmt->bindParam(':cust_name', strtoupper($_POST['name']));
		$stmt->bindParam(':description', $description);
		$stmt->bindParam(':gender', $_POST['gender']);
		$stmt->bindParam(':dob', $datebate);
		$stmt->bindParam(':phone', $_POST['phone']);
		$stmt->bindParam(':address', $_POST['addr']);
		$stmt->bindParam(':email_address', $_POST['email_address']);
		$stmt->bindParam(':referral', $_POST['referral']);
		$stmt->bindParam(':date_ap', $setdate);
		$stmt->bindParam(':captured_by', $_SESSION['fullname']);
		$description = 'Reception';
		$stmt->execute();


		$referral = $_POST['referral'];
		$stmt = $db->query("SELECT sn FROM patient_discount where individual_group_no='$referral' and status='On-going'");
		if ($stmt->rowCount() > 0) {

			$updateSQL = "UPDATE pharm_ext 
		SET discount_set = :discount_set 
		WHERE transc_code = :transc_code";
			$discount_set = 1;
			$stmt = $db->prepare($updateSQL);
			$stmt->bindParam(':discount_set', $discount_set, PDO::PARAM_STR);
			$stmt->bindParam(':transc_code', $transc_code, PDO::PARAM_STR);
			$stmt->execute();
		}



		echo "Record inserted successfully.";
	} else {
		echo "The transaction code already exists.";
	}

	$hosp_no = $transc_code;
	$patient_name = $_POST['name'];
	$EX_IN = 'EX';
}


function save_invent($drug_sn, $sale_sn, $inven_desc, $batch, $qtyIN, $qtyOUT, $c_qty, $cust_patient_id, $enter_by, $captured_date, $transact_date, $invoice_status, $drug_status, $paystatus, $cr, $Sale_drug_qty, $claim_amt, $pay, $EX_or_IN, $where)
{
	include('../Connections/Conn.php');

	$updateSQL = "UPDATE patient_ap_services 
				  SET qty = :qty, claim_amt = :claim_amt, pay = :pay, dsp_by = :dsp_by, cr = :cr, invoice_status = :invoice_status, drug_status = :drug_status, paystatus = :paystatus 
				  WHERE sn = :sn";

	$stmt = $db->prepare($updateSQL);
	$stmt->bindParam(':qty', $Sale_drug_qty, PDO::PARAM_STR);
	$stmt->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
	$stmt->bindParam(':pay', $pay, PDO::PARAM_STR);
	$stmt->bindParam(':dsp_by', $_SESSION['fullname'], PDO::PARAM_STR);
	$stmt->bindParam(':cr', $cr, PDO::PARAM_STR);
	$stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
	$stmt->bindParam(':drug_status', $drug_status, PDO::PARAM_STR);
	$stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
	$stmt->bindParam(':sn', $sale_sn, PDO::PARAM_STR);
	$stmt->execute();

	$C_date = date("Y-m-d");

	$selectSQL = "SELECT * FROM stock_table_inven 
				  WHERE stock_sn = :stock_sn AND sale_sn = :sale_sn AND inven_desc = :inven_desc AND qtyOUT = :qtyOUT AND bal = :bal AND cust_patient_id = :cust_patient_id AND enter_by = :enter_by AND DATE(captured_date) = :captured_date";

	$stmt2 = $db->prepare($selectSQL);
	$stmt2->bindParam(':stock_sn', $drug_sn, PDO::PARAM_STR);
	$stmt2->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
	$stmt2->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
	$stmt2->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_STR);
	$stmt2->bindParam(':bal', $c_qty, PDO::PARAM_STR);
	$stmt2->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
	$stmt2->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
	$stmt2->bindParam(':captured_date', $C_date, PDO::PARAM_STR);
	$stmt2->execute();

	if ($stmt2->rowCount() == 0) {
		$insertSQL = "INSERT INTO stock_table_inven (stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, cust_patient_id, cust_patient_type, enter_by, captured_date) 
					  VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :cust_patient_id, :cust_patient_type, :enter_by, :captured_date)";

		$stmt = $db->prepare($insertSQL);
		$stmt->bindParam(':stock_sn', $drug_sn, PDO::PARAM_STR);
		$stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
		$stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
		$stmt->bindParam(':batch', $batch, PDO::PARAM_STR);
		$stmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_STR);
		$stmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_STR);
		$stmt->bindParam(':bal', $c_qty, PDO::PARAM_STR);
		$stmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
		$stmt->bindParam(':cust_patient_type', $EX_or_IN, PDO::PARAM_STR);
		$stmt->bindParam(':enter_by', $_SESSION['fullname'], PDO::PARAM_STR);
		$stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
		$stmt->execute();

		/*
		$updateStockSQL = "UPDATE stock_table SET qty = :qty WHERE sn = :sn";
		$stmt = $db->prepare($updateStockSQL);
		$stmt->bindParam(':qty', $c_qty, PDO::PARAM_STR);
		$stmt->bindParam(':sn', $drug_sn, PDO::PARAM_STR);
		$stmt->execute();

		*/
	}
}

?>


<div class="row">

	<div class="col-lg-4">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>External Services Manager</h5>
			</div>

			<div class="ibox-content">







				<h4>
					<p>New Account. Click the button.</p>
				</h4>

				<div class="form_sep">
					<input type="button" name="users" value="Add New External" data-target="#modal" id="" class="btn btn-danger btn-sm ext_modal_link" />
				</div>


				<hr>

				<h4>
					<p>Search for patient/Account you wish to add hospital services to.</p>
				</h4>

				<form action="index.php" method="POST">


					<div class="form_sep">
						<p style="color:red; font-size: 24px; ">[ Search for Patient ] </p><strong>(Name or Phone or Hospital No.)</strong>

						<input type="text" id="search" name="search" class="form-control" style="border-color:black;" required>
					</div>
					<br>
					<div class="form_sep">
						<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action" id="apply_action" onclick="search_patient('sales')"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
					</div>
				</form>

				<hr>

				<form method="POST" id="ext_form" action="index.php?sale">



					<div style="background-color:#FCF; padding:10px">

						<div class="row form_sep">

							<div class="col-md-12">
								<div>
									<label for="reg_input_no" class="req">Search External Patients</label>
									<input type="text" id="search_2" name="search_2" class="form-control" style="border-color:black;" required>
									<br>
									<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action_2" id="apply_action_2" onclick="search_patient('Ex_sales')"><i class="fa fa-search"></i>&nbsp;Apply Search</button>

								</div>
							</div>
						</div>
						<!-- walshak 27/6/2023 -->
						<div class="form_sep">

							<div class="form_sep">

							</div>




						</div>
					</div>
				</form>

				<hr>

				<div class="form_sep">
					<a href="index.php?sale"><strong>See Today's Sales</strong></a>
				</div>
				<div class="form_sep">
				</div>

				<form method="POST" id="ext_form" action="index.php?sale">

					<div class="form_sep">
						<label for="reg_input_no" class="req">Set Dates Range</label><br>
						<div class="form_sep" id="">
							<div class="input-daterange input-group" id="">
								<input type="date" class="form-control" name="start2" value="" required />
								<span class="input-group-addon">to</span>
								<input type="date" class="form-control" name="end2" value="" required />
							</div>

						</div>
					</div>

					<div class="form_sep">
						<button class="btn btn-success btn-sm" type="submit" name="apply_past_dates">Apply</button>
					</div>
				</form>

			</div>

		</div>
	</div>




	<div class="col-lg-8">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Transaction </h5>
			</div>

			<div class="ibox-content">

				<?php





				if (isset($_GET['sale'])) {
					$hosp_no = $_GET['sale'];
					if ($hosp_no != '') {


						$stmt = $db->query("SELECT p.cust_name,r.name as referral_name  FROM pharm_ext p LEFT JOIN referrals r ON p.referral = r.sn where transc_code ='$hosp_no'");
						if ($stmt->rowCount() > 0) {
							$row = $stmt->fetch(PDO::FETCH_ASSOC);
							$appt_no = $hosp_no;
							$EX_IN = 'EX';
							$patient_name = $row['cust_name'];
							$referral_name = $row['referral_name'];
						} else {

							$stmt = $db->query("SELECT surname, fname FROM enrollee WHERE hospital_no='$hosp_no'");
							$row = $stmt->fetch(PDO::FETCH_ASSOC);
							$EX_IN = 'IN';
							$patient_name = $row['fname'] . ' ' . $row['surname'];
						}
					}
				}


				if (isset($_POST['add_service']) or isset($_POST['add_service_other'])) {
					$hosp_no = $_POST['hosp_no'];
					$appt_no = $_POST['appt_no'];
					$EX_IN = $_POST['EX_IN'];
					$patient_name = $_POST['patient_name'];
				}

				if (isset($_POST['report_date'])) {
					$hosp_no = $_POST['hosp_no2'];
					$appt_no = $_POST['appt_no2'];
					$EX_IN = $_POST['EX_IN2'];
					$patient_name = $_POST['patient_name2'];
				}


				?>

				<?php if (isset($_POST['apply_items'])) {

					$ex_patient = $_POST['ex_patient'];
					$in_patient = $_POST['in_patient'];


					if ($in_patient == '' and $ex_patient == '') { ?>
						<div class="alert alert-warning">
							<p>Select existing patient before you continue ... </p>
						</div>
					<?php } elseif ($in_patient != '' and $ex_patient != '') { ?>
						<div class="alert alert-warning">
							<p>Select either internal or external patient before you continue ... </p>
						</div>

				<?php } else {
						if ($ex_patient != '') {
							$patient = $ex_patient;

							header("location:index.php?sale=$patient");
						} else {
							$patient = $in_patient;
						}
						$part = explode("__", $patient);
						$hosp_no = $part[0];
						$patient_name = $part[1];
						$EX_IN = $part[2];
					}
				}
				?>

				<?php if (($hosp_no != '' and $patient_name != '') or (isset($_POST["save"]) and $transc_code != '' and $patient_name != '')) {
					if (isset($_POST["save"])) {
						$hosp_no = $transc_code;
					}
					if ($EX_IN == 'IN') {
						$emr = $hosp_no;
						$general_credit_limit =	$_SESSION['credit_limit_status'];
						$items = call_current_balance($db, $emr, $general_credit_limit);

						$current_balance =  $items["current_balance"];
						$patient_name      = $items['patient_name'];
						$nhis_no           = $items['nhis_no'];
						$names             = $items['names'];
						$surname           = $items['surname'];
						$fname             = $items['fname'];
						$oname             = $items['oname'];
						$gender            = $items['gender'];
						$address           = $items['address'];
						$referral_name     = $items['referral_name'];
						$referral_sn     = $items['referral_sn'];
						$discount_set      = $items['discount_set'];
						$phone             = $items['phone'];
						$email             = $items['email'];
						$insurance_type    = $items['insurance_type'];
						$insurance_no      = $items['insurance_no'];
						$save_insurance_no = $items['save_insurance_no'];
						$wallet_amount     = $items['wallet_amount'];
						$bal_credit_limit  = $items['bal_credit_limit'];
						$credit_limit  = $items['bal_credit_limit'];
						$add_minus         = $items['add_minus'];
						$interest          = $items['interest'];
						$patient_type      = $items['patient_type'];
						$insur_title       = $items['insur_title'];
						$payment_mode       = $items['payment_mode'];
					}

					$stmt = $db->query("SELECT ap_type,appt_no FROM apptm WHERE hospital_no='$hosp_no' and ap_direction='1' ORDER BY sn DESC LIMIT 1");
					if ($stmt->rowCount() > 0) {
						$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
						$patient_nhis_access = $rwx['ap_type'];
						$appt_no = $rwx['appt_no'];
					} else {
						$patient_nhis_access = 1;
						$appt_no = $hosp_no;
					}


				?>
					<strong>Patient Number:</strong><strong style="font-size:18px">&nbsp;<?php echo $hosp_no; ?></strong>&nbsp;/&nbsp;
					<strong style="font-size:14px">Name:</strong> &nbsp; <strong style="font-size:18px"><?php echo $patient_name; ?></strong>

					<?php
					if ($referral_name != '') {
						echo "<br><h3 style='color:blue;'> (Referral: $referral_name) .</h3>";
					}
					?><input type="button" name="eidt_gd" value="Edit Data" data-target="#modal" id="<?php echo $hosp_no; ?>" class="btn btn-warning btn-xs  edit_patient_ext" style="font-size: 14px; " />



					<br>
					<a href="index.php?sale=<?= $hosp_no; ?>" class="btn btn-success btn-sm">Refresh Page</a>
					<hr>
					<?php if ($_SESSION['rights'] != 'PH') { ?>

						<form method="POST" id="ext_form" action="index.php?sale">

							<input type="button" name="" value="Enter Note" data-target="#modal" id="<?php echo $hosp_no; ?>" class="btn btn-success btn-xs document_note" style="font-size: 14px; " />



							<div id="list_services">

								<table width="100%" cellpadding="4">
									<tr>
										<td width="50%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Enter [ <strong style="color:#F00">Service</strong> ] Here</label>
											</div>
										</td>
										<td width="50%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Enter Quantity</label>
											</div>
										</td>
									</tr>
									<tr>
										<td width="70%" style="padding-right:10px; ">
											<div class="form_sep">
												<select name="item_service" class="input-sm chosen-select" style="width:350px;">
													<option selected="selected" value="">Search and Select Services</option>
													<?php
													if ($_SESSION['rights'] != 'PH') {
														$stmt = $db->query("SELECT * FROM prices_table WHERE (price_table='Consultation' or price_table='Other Services' or price_table='Nursing Services') and status='0' and ext_price>0 and hosp_price >0 order by item_service");
														while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row["sn"] . '__' . $row["item_service"] . '__' . $row["coverage"] . '__' . $row["insurance_type"] . '__' . $row["ext_price"] . '__' . $row["nhis_price"] . '__' . $row["dept"] . '__' . $row["hosp_price"] . '__' . $row["price_table"] . '__' . $row["category"] . '__' . $row["duration"]; ?>"><?php echo $row["item_service"]; ?></option>
													<?php }
													}
													?>
												</select>
											</div>




											<div class="form_sep">
												<select name="item_consumables" class="input-sm chosen-select" style="width:350px;">
													<option selected="selected" value="">Search and Select Consumables</option>

													<?php

													if ($_SESSION['rights'] == 'NS') {
														$cur_date = date("Y-m-d");
														if ($_SESSION['dispensory'] == 1) {
															$stmt = $db->query("SELECT m.* FROM stock_table m 
														INNER JOIN stock_table_dispensory d on d.stock_table_id = m.sn
														WHERE status = 'active' ORDER BY product_name");
														} else {
															$stmt = $db->query("SELECT * FROM stock_table WHERE stock_table='Store'  and qty>0  and cash_price > 0 order by product_name");
														}
														while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row["sn"] . '__' . $row["product_name"] . '__' . $row["coverage"] . '__' . $row["insurance_type"] . '__' . $row["cash_price"] . '__' . $row["nhis_price"] . '__' . 'dept' . '__' . $row['hosp_price'] . '__' . 'Pharmacy' . '__' . 'Pharmacy' . '__' . '0'; ?>"><?php echo $row["product_name"] . '__' . $row["cash_price"]; ?></option>
													<?php }
													}
													?>
												</select>
											</div>


										</td>

										<td width="10%" style="padding-right:10px; ">
											<input type="number" id="qty" name="qty" class="form-control" onkeyup="sum();" min="1" data-required="true">
										</td>
										<td width="20%" style="padding-right:10px; ">
											<button class="btn btn-success" type="submit" name="add_service" id="add_service">Add</button>
										</td>
									</tr>
								</table>
							</div>



							<label for="chkPassport">
								<table>
									<tr>
										<td><input type="checkbox" id="show_billing" name="show_billing" value="checked" style="height: 20px; width: 20px;" /></td>
										<td>&nbsp;<strong style="color: crimson; ">Check to Enter Special Services and Amount.</strong></td>
									</tr>
								</table>


							</label>




							<div id="custom_billing" style="display: none">

								<hr />

								<table width="100%" cellpadding="4">
									<tr>

										<td width="50%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Describe Service Type</label>
											</div>
										</td>


										<td width="20%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Service Type</label>
											</div>
										</td>
									</tr>



									<tr>
										<td width="50%" style="padding-right:10px; ">
											<div class="form_sep">
												<input type="text" name="special_service" maxlength="100" class="form-control">
											</div>
										</td>

										<td width="20%" style="padding-right:10px; ">
											<div class="form_sep">
												<select name="service_dept" class="form-control">
													<option selected="selected" value="">Search and Select Items</option>

													<?php

													$stmt = $db->query("SELECT * FROM prices_table_category");
													while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row["Name"]; ?>"><?php echo $row["Name"]; ?></option>


													<?php }  ?>
												</select>
											</div>
										</td>

									</tr>

									<tr>

										<td width="20%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Amount</label>
											</div>
										</td>

										<td width="10%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Qty</label>
											</div>
										</td>
									</tr>
									<tr>
										<td width="20%" style="padding-right:10px; ">
											<input type="number" step="any" id="amount" name="amount" class="form-control" min="1" data-required="true">
										</td>


										<td width="10%" style="padding-right:10px; ">
											<input type="number" id="" name="qty_other" class="form-control" onkeyup="sum();" min="1" data-required="true">
										</td>



										<td width="20%" style="padding-right:10px; ">
											<button class="btn btn-success" type="submit" name="add_service_other" id="add_service_other">Add</button>

										</td>
									</tr>
								</table>


							</div>

							<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
							<input type="hidden" name="appt_no" value="<?php echo $appt_no; ?>" />
							<input type="hidden" name="patient_nhis_access" value="<?php echo $patient_nhis_access; ?>" />
							<input type="hidden" name="interest" value="<?php echo $interest; ?>" />
							<input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
							<input type="hidden" name="EX_IN" value="<?php echo $EX_IN; ?>" />
							<input type="hidden" name="c" value="<?php echo $patient_name; ?>" />
							<input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>" />
							<input type="hidden" name="add_minus" value="<?php echo $add_minus; ?>" />
							<input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>" />
							<input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />


						</form>

					<?php } ?>

					<?php if ($EX_IN == 'EX') { ?>
						<?php if ($_SESSION['rights'] == 'PH') { ?>
							<a href="../pharmacy/index.php?presc&hos_no=<?php echo $hosp_no; ?>" class="btn btn-primary btn-lg" style="font-size: 14px; ">Click Go to Pharmacy Managment To Add Drug </a><br>
					<?php }
					}
					?>

					<?php


					if ($_SESSION['rights'] == 'PH_cancelled') {
						$searchPart = "serv_group='Pharmacy' AND invoice_status!=3";
					} else {
						$searchPart = "(serv_group='Other Services' or serv_group='EX' or tag='sales') AND invoice_status!=3";
					}

					if (isset($_POST['report_date']) or isset($_GET['d'])) {
						if (isset($_POST['report_date'])) {
							$start = $_POST['start'];
							$end = $_POST['end'];
						} else {
							$dates = $_GET['d'];
							$parts = explode("/", $dates);
							$start = $parts[0];
							$end = $parts[1];
						}


						$stmt = $db->query("SELECT * FROM patient_ap_services WHERE $searchPart and hospital_no='$hosp_no' and date(date_entry) between '$start' and '$end'");
					} elseif (isset($_GET['dt'])) {
						$start = $_GET['dt'];
						$end = date("Y-m-d");
						$stmt = $db->query("SELECT * FROM patient_ap_services WHERE $searchPart and hospital_no='$hosp_no' and drug_status='0' and date(date_entry) between '$start' and '$end'");
					} elseif (isset($_GET['invoiced'])) {
						$start = $_GET['invoiced'];
						$end = date("Y-m-d");
						$stmt = $db->query("SELECT * FROM patient_ap_services WHERE $searchPart and hospital_no='$hosp_no' and paystatus='0' and invoice_status='1' and date(date_entry) between '$start' and '$end'");
					} else {
						$setdate = date("Y-m-d");
						$stmt = $db->query("SELECT * FROM patient_ap_services WHERE $searchPart and hospital_no='$hosp_no' and date(date_entry)='$setdate'");
					}

					if ($stmt->rowCount() > 0) { ?>
						<hr>
						<form method="POST" id="ext_form" action="index.php?sale">

							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th data-toggle="true" width="20%">Description</th>
										<th data-toggle="true" width="10%">Price</th>
										<th data-toggle="true" width="7%">Qty</th>
										<th data-toggle="true" width="7%">Amt</th>
										<th data-toggle="true" width="20%">Date/Entered By</th>
										<th data-toggle="true" width="15%"></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$bgcolor = "#F4F4F4";
									$colordecide = 1;
									$paying = 0;
									$paid = 0;
									$sn = 1;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										if ($row['paystatus'] == 1) {
											$paid = $paid + $row['pay'];
										} elseif ($row['paystatus'] == 0) {
											$paying = $paying + $row['pay'];
										}
										if ($colordecide % 2 == 0) {
											$bgcolor = "#F4F4F4";
										} else {
											$bgcolor = "#FFFFFF";
										}
									?>
										<tr bgcolor="<?php echo $bgcolor; ?>">

											<td><?php echo '<input type="checkbox" value="' . $row['sn'] . '__' . $row['item_services'] . '__' . $row['pay'] . '__' . $row['qty'] . '__' . $row['paystatus'] . '__' . $row['transact_date'] . '__' . $row['remarks'] . '" name="inv[]" checked="checked" />' . ' ' . $row['item_services'];
												if ($row['drug_status'] == 1) {
													echo '<br><strong style="color:#F00">[Delivered]</strong> ';
												} elseif ($row['cat_type'] == 'Consumable' && $row['drug_status'] == 0 && $row['paystatus'] == 0) {
													echo '<br><strong style="color:#F00">[Paid & Dispense Pending]</strong> ';
												} ?>

											</td>
											<td><?php echo $row['hosp_price']; ?></td>
											<td><?php echo $row['qty']; ?></td>
											<td><strong><?php echo number_format($row['pay'], 2, '.', ','); ?></strong></td>
											<td><?php echo date('d M,Y', strtotime($row['date_entry']));
												echo '<br><b>' . $row['prepared_by'] . '</b>';  ?></td>
											<td>
												<?php if ($row['invoice_status'] == 1 and $row['paystatus'] == 1) { ?>
													<b>Paid</b>
												<?php } elseif ($row['invoice_status'] == 1 and $row['paystatus'] == 1 and $row['drug_status'] == 1) {
													echo 'Delivered';
												} elseif (($row['invoice_status'] == 0 or $row['invoice_status'] == 1) and $row['paystatus'] == 0) {
													echo 'Payment Pending';
												} ?>

												<?php
												if ($rights != 'PH') {
													if ($row['paystatus'] == 0 and $row['drug_status'] == 0 and $row['prepared_by'] == $_SESSION['fullname']) { ?>
														<br><a href="index.php?sale=<?php echo $hosp_no . '&dl=' . $row['sn']; ?>" onclick="return confirm('Are you sure you want to delete this item?')" class="btn btn-danger btn-xs">Delete</a><br>
												<?php }
												}
												?>

												<?php
												if ($rights == 'PH' || $row['cat_type'] == 'Consumable') {
													if ($row['invoice_status'] == 1 and $row['paystatus'] == 1 and $row['drug_status'] == 0) { ?>

														<button type="button" id="dispense_external" onclick="dispense_xternal(<?php echo $row['sn']; ?>)" class="btn btn-warning btn-xs">Dispense Item </button>

			</div>
		<?php } elseif ($row['invoice_status'] == 1 and $row['paystatus'] == 0 and $row['drug_status'] == 0 and $row['cr'] == 0) { ?>
			<a href="index.php?sale=<?php echo $hosp_no . '&dl=' . $row['sn']; ?>" onclick="return confirm('Are you sure you want to delete this item?')" class="btn btn-danger btn-xs">Delete</a>

			<?php if ($_SESSION['dsp_oncredit'] == 1) { ?>
				<input type="button" name="on_cr" value="Dispense On/Credit" data-target="#modal" id="<?php echo $row['sn'] . '/' . $hosp_no . '/' . $hosp_no . '/' . $patient_name . '/EX/P/' . $adm_status . '/' . $row['paystatus']; ?>" class="btn btn-primary btn-xs dsp_oncredit" />
			<?php } ?>

	<?php }
												}
	?>


	</td>

	</tr>


<?php
										$sn++;
									} ?>

<tr>
	<td><strong>Total:</strong></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><strong><?php echo number_format($paying, 2, '.', ','); ?></strong></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</tbody>
</table>

<?php if ($rights == 'PH' and $_SESSION['print_invoice'] == 1) { ?>
	<div align="left" style="font-size:12px"><strong>
			<button class="btn btn-info" type="submit" name="print_now" id="print_now">Print Invoice</button>
			&nbsp;&nbsp; | &nbsp;&nbsp;
			<a href="index.php?sale=<?php echo $hosp_no; ?>" class="btn btn-white">Refresh</a>
		</strong></div>
	<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
	<input type="hidden" name="ex_patient_name" value="<?php echo $patient_name; ?>" />
<?php } ?>

<hr>

<?php if ($EX_IN == 'EX') {
							if ($_SESSION['recve_cash'] == 1 or $_SESSION['rights'] != "PH") { ?>

		<table cellpadding="10" width="100%">
			<tr>
				<td>

					<div align="right" style="font-size:12px"><?php if ($paying > 0) { ?><strong style="color:#F00"> <?php } else { ?><strong> <?php } ?>Total Amount Paying:</strong> &nbsp;
								<strong style="font-size:20px"><?php echo number_format($paying, 2, '.', ',') ?></strong></div>
					<input type="hidden" name="paying" value="<?php echo $paying; ?>" />
				</td>
				<td style="padding-right:12px;">
					<div align="right" style="font-size:12px"><strong>Amount Paid:</strong> &nbsp;<strong style="font-size:20px"><?php echo number_format($paid, 2, '.', ',') ?></strong></div>
				</td>
			</tr>
		</table><br>

		<?php if ($paying > 0) { ?>
			<?php /*?>							<table width="100%"><tr>                      
                                    <td>
      <label for="reg_input_no" class="req">Mode of Payment:</label>                  
            <select name="paymethod" id="paymethod" required class="form-control" >
                <option value="">Select...</option>
                <option value="Cash">CASH</option>
                 <option value="POS">POS MACHINE</option>
                </select>                        
                                    </td> 
                                   <td>
                                   <div class="form_sep">
      <label for="reg_input_no" class=""><?php if(isset($_GET['err'])){?><strong style="color:#F00" >POS Ref/No(Max: 9 digits)</strong> <?php }else{?>POS Ref/No. (Max. 9 digits)<?php }?></label>
                        <input type="text" id="ref_no" name="ref_no" class="form-control" maxlength="50" placeholder="Enter POS Ref/No">
                        </div>
                         
                                   </td>
                                    <td>
                <div class="form_sep">
      <label for="reg_input_no" class=""><?php if(isset($_GET['err'])){?><strong style="color:#F00" >Payer Bank Name</strong> <?php }else{?>Payer Bank Name<?php }?></label>                
                <select name="bank_name" id="bank_name" class="form-control" >
                           <option value=''>Select...</option>                   
                    <?php 
                    $stmt_bnk = $db->query("SELECT * FROM bank");
                    while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)){ ?>
                <option value="<?php echo $row_rstbank["bank"]; ?>"><?php echo $row_rstbank["bank"]; ?></option>
                          <?php } ?>
                </select>
                </div>                                     
                                    </td>
                   					</tr>
                                    </table><?php */ ?>
			<?php if ($_SESSION['biller'] == '1') { ?>
				<div align="right" style="font-size:12px"><strong>________</strong></div>
				<a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>&pay" class="btn btn-success btn-sm">Goto BILLER</a>
			<?php } ?>

			<!--   <div align="right" style="font-size:12px"><strong><button class="btn btn-success" type="submit" name="pay_now" id="pay_now" >Pay Now</button></strong></div>-->



		<?php } ?>

		<?php if ($paid > 0) { ?>
			<td>
				<div align="right" style="font-size:12px"><strong>________</strong></div>
				<div align="right" style="font-size:12px"><strong><button class="btn btn-info" type="submit" name="print_recep" id="close_now">Print</button></strong></div>
			</td>
		<?php } ?>

		<?php if ($paying == 0) { ?>
			<td>
				<div align="right" style="font-size:12px"><strong>__________</strong></div>
				<div align="right" style="font-size:12px"><strong><button class="btn btn-danger" type="submit" name="close_now" id="close_now">Close</button></strong></div>
			</td>
		<?php } ?>






		<hr>
		<table align="right">
			<tr>
				<td align="right" style="padding-right:12px; ">Search for past reciept and print </td>
				<td align="right">
					<div class="form_sep" align="right">
						<input type="button" name="users" value="Search" data-target="#modal" id="" class="btn btn-info btn-sm report_dates" />
					</div>
				</td>
			</tr>
		</table>
	<?php } ?>

<?php } else { ?>
	<?php if ($_SESSION['biller'] == '1') { ?>

		<a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>&pay" class="btn btn-success btn-sm">Goto BILLER</a>

	<?php } ?>
<?php } ?>

<input type="hidden" name="ex_patient_name" value="<?php echo $patient_name; ?>" />

<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
</form>
<br>


<?php } else { ?>
	<hr>
	<div align="center"><strong>No Item Found in the table</strong></div>
<?php }
?>

<?php } elseif (isset($_POST['print_recep'])) {

					$emr = $_POST['hosp_no'];
					$ex_patient_name = $_POST['ex_patient_name'];
					$ext_inv = 2;
					include_once("../inc/print.php");

					//	$hosp_no=$_POST['hosp_no'];

					///	if(!empty($_REQUEST['inv'])) {	
					//	include_once("../inc/reciept_body.php");	

				} elseif (isset($_POST['print_now'])) {
					$emr = $_POST['hosp_no'];
					$ex_patient_name = $_POST['ex_patient_name'];
					$ext_inv = 1;
					include_once("../inc/print.php");
				} else {

					$start_date = date("Y-m-d");
					$from_date = $start_date;
					$to_date   = $start_date;

					// Determine service group based on user rights
					if (isset($_SESSION['rights']) && $_SESSION['rights'] == 'PH') {
						$searchPart = "serv_group='Pharmacy'";
					} else {
						$searchPart = "(serv_group='Other Services' OR serv_group='EX')";
					}

					// Check if user applied past date filters
					if (isset($_POST['apply_past_dates'])) {
						$from_date = trim($_POST['start2']);
						$to_date   = trim($_POST['end2']);

						if ($from_date != '' && $to_date != '') {
							$date_filter = "DATE(date_entry) BETWEEN '$from_date' AND '$to_date'";
							$dates = "$from_date/$to_date";
						} else {
							$date_filter = "DATE(date_entry) = '$start_date'";
							$dates = "$start_date/$start_date";
						}

						// Use dynamic service group for flexibility
						$stmt = $db->query("
								SELECT DISTINCT hospital_no
								FROM patient_ap_services
								WHERE $date_filter
								AND $searchPart
								AND hospital_no LIKE 'EX%'
							");
					} else {
						// Default view: today's data for hospitals starting with EX
						$stmt = $db->query("
								SELECT DISTINCT hospital_no
								FROM patient_ap_services
								WHERE DATE(date_entry) = '$start_date'
								AND hospital_no LIKE 'EX%'
							");
						$dates = "$start_date/$start_date";
						$from_date = $start_date;
						$to_date   = $start_date;
					}

					// --- Display Section ---
					if ($stmt->rowCount() > 0) {
?>
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th width="5%">#</th>
				<th width="30%">Hospital No</th>
				<th width="30%">Total (₦)</th>
				<th width="30%">Action</th>
			</tr>
		</thead>
		<tbody>
			<?php
						$amt = 0;
						$pending_amt = 0;
						$n = 1;

						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$hospital_no = $row['hospital_no'];

							// Sum all payments for the selected date(s)
							$sqlSum = $db->query("
                    SELECT SUM(pay) AS TotalTrans, paystatus
                    FROM patient_ap_services
                    WHERE hospital_no = '$hospital_no'
                      AND invoice_status = '1'
                      AND DATE(date_entry) BETWEEN '$from_date' AND '$to_date'
                ");
							$rows = $sqlSum->fetch(PDO::FETCH_ASSOC);
							$TotalTrans = isset($rows['TotalTrans']) ? (float)$rows['TotalTrans'] : 0;

							if ($rows['paystatus'] == '1') {
								$amt += $TotalTrans;
							} else {
								$pending_amt += $TotalTrans;
							}
			?>
				<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo htmlspecialchars($hospital_no); ?></td>
					<td align="right"><?php echo number_format($TotalTrans, 2, '.', ','); ?></td>
					<td>
						<a href="index.php?sale=<?php echo urlencode($hospital_no); ?>&d=<?php echo urlencode($dates); ?>">
							[View Sales / Goto PayNow]
						</a>
					</td>
				</tr>
			<?php

						}
			?>
		</tbody>
	</table>

	<hr>
	<div align="left">
		<table width="100%" cellpadding="10">
			<tr>

				<td><strong>Transactions:&nbsp;&nbsp;</strong></td>
				<td>
					<strong style="font-size:20px; color:blue;">
						₦<?php echo number_format($amt, 2, '.', ','); ?>
					</strong>
				</td>
			</tr>
		</table>
	</div>

<?php
					} else {
?>
	<div align="center" style="font-size:16px;">
		<strong>No Sale Recorded Yet</strong>
	</div>
<?php
					}
				} ?>



		</div>

	</div>
</div>

</div>


<div class="modal inmodal fade" id="ext_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">New External Patient</h4>
			</div>

			<div class="modal-body">
				<form method="POST" id="ext_form" action="index.php?sale">

					<div class="form_sep">
						<label for="reg_input_name" class="req">Patient Fullname (Surname, Others):</label>
						<input type="text" id="name" name="name" class="form-control" required>
					</div>

					<div class="form_sep">
						<label for="reg_select" class="req">Gender</label>
						<select name="gender" id="gender" class="form-control" required>
							<option selected="selected" value="">Select...</option>
							<option value="Male">Male</option>
							<option value="Female">Female</option>
							<option value="Others">Others</option>
						</select>
					</div>

					<div class="form_sep">
						<table width="100%" cellpadding="5">
							<tr>
								<td>
									<div class="form_sep" id="">
										<label class="font-noraml">Date of Birth or Age</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
											<input type="date" class="form-control" name="dob" id="dob">
										</div>
									</div>
								</td>
								<td>
									<div class="form_sep">
										<label for="reg_select" class="req">Age</label>
										<input type="number" id="age" name="age" max="100" min="0" maxlength="3" value="0" class="form-control">
									</div>
								</td>
							</tr>
						</table>
					</div>

					<div class="form_sep">
						<label for="reg_input_name" class="">Phone:</label>
						<input type="text" id="phone" name="phone" class="form-control">
					</div>

					<div class="form_sep">
						<label for="reg_input_name" class="">Address:</label>
						<input type="text" id="addr" name="addr" class="form-control">
					</div>

					<div class="form_sep">
						<label for="reg_input_name" class="req">Email Address:</label>
						<input type="email" id="email_address" name="email_address" class="form-control" required>
					</div>


					<div class="form_sep">
						<label for="reg_select" class="">Patient is Referred from::</label>
						<select name="referral" id="referral" class="form-control">
							<option selected="selected" value="">Select...</option>
							<?php $stmt = $db->query("SELECT name,sn FROM referrals");
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form_sep">
						<button class="btn btn-success btn-xs" type="submit" name="save" id="save">Save</button>
					</div>
				</form>


			</div>
		</div>
	</div>
</div>






<div class="modal inmodal fade" id="report_dates_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Enter Report Dates</h4>
			</div>

			<div class="modal-body">
				<form method="POST" id="ext_form" action="index.php?sale">



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
						<button class="btn btn-success btn-xs" type="submit" name="report_date" id="report_date">Apply</button>
					</div>
					<input type="hidden" value="<?php echo $hosp_no ?>" name="hosp_no2">
					<input type="hidden" value="<?php echo $appt_no ?>" name="appt_no2">
					<input type="hidden" value="<?php echo $EX_IN ?>" name="EX_IN2">
					<input type="hidden" value="<?php echo $patient_name ?>" name="patient_name2">

				</form>


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


<div class="modal inmodal fade" id="edit_patient_data_ext_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Edit External Patient</h4>
			</div>
			<div class="modal-body" id="edit_pat_ext_body">
			</div>
		</div>
	</div>
</div>



<div class="modal inmodal" id="document_note_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content animated bounceInRight">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

				<h4 class="modal-title">Patient's Documentation</h4>
			</div>

			<div class="modal-body" id="document_note_body">


			</div>
		</div>
	</div>
</div>


<script>
	function Clickheretoprint() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
		var content_vlue = document.getElementById("content").innerHTML;

		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();
	}



	function service_bill() {

		var review_note = document.getElementById('selected_review_note').value;
		var hospital_no = document.getElementById('hospital_no').value;
		var dept_id = document.getElementById('dept_id').value;
		var mode = document.getElementById('mode').value;

		if (review_note != '' && mode == 'new') {

			var rr = confirm("Are you sure you want to Save?");
			if (rr === true) {

				$.ajax({
					url: "../inc/documentation.php",
					method: "POST",
					data: {
						review_note: review_note,
						hospital_no: hospital_no,
						dept_id: dept_id
					},
					success: function(data) {

						var json = JSON.parse(data);
						//alert(json["status"]);
						if (json["status"] == 1) {

							document.getElementById("pay_now").disabled = true;
							document.getElementById("selected_review_note").value = '';
							patient_review();
							toastr.info(json["message"], 'Saved', {
								timeOut: 5000
							});
						} else {
							toastr.error(json["message"], 'Error', {
								timeOut: 5000
							});

						}
					}
				});

			} else {
				setTimeout(function() {
					$("#overlay").fadeOut();
				}, 500);
				exit;
			}

		} else if (review_note != '' && mode == 'edit') {

			var notes_sn = document.getElementById('notes_sn').value;
			var review_note = document.getElementById('selected_review_note').value;

			///alert(notes_sn);
			///alert(review_note);

			$.ajax({
				url: "../inc/documentation.php",
				method: "POST",
				data: {
					review_note_edit: review_note,
					edit_notes_update: notes_sn
				},
				success: function(data) {
					var json = JSON.parse(data);

					if (json["status"] == 0) {

						document.getElementById("pay_now").disabled = true;
						document.getElementById("selected_review_note").value = '';
						patient_review();
						refresh_form();
						toastr.success(json["message"], 'Updated', {
							timeOut: 5000
						});
					} else {
						toastr.error(json["message"], 'Error', {
							timeOut: 5000
						});

					}
				}
			});

		} else if (review_note != '' && mode == 'add') {

			var notes_sn = document.getElementById('notes_sn').value;
			var review_note = document.getElementById('selected_review_note').value;

			$.ajax({
				url: "../doctor/_ward_review.php",
				method: "POST",
				data: {
					review_note: review_note,
					add_new_notes: notes_sn
				},
				success: function(data) {
					var json = JSON.parse(data);
					//alert(json["status"]);
					if (json["status"] == 0) {

						document.getElementById("pay_now").disabled = true;
						document.getElementById("selected_review_note").value = '';
						patient_review();
						refresh_form();
						toastr.success(json["message"], 'Note Added', {
							timeOut: 5000
						});
					} else {
						toastr.error(json["message"], 'Error', {
							timeOut: 5000
						});

					}
				}
			});


		} else {
			toastr.error('Empty Notes', 'Error', {
				timeOut: 5000
			});
		}
	}



	function patient_review() {

		/// $('#loader').show();

		var hospital_no = document.getElementById('hospital_no').value;

		$.ajax({
			url: "../inc/_ward_review_fetch.php",
			method: "POST",
			data: {
				patient_review_doc: hospital_no
			},
			success: function(data) {

				document.getElementById('data_displayed').innerHTML = data;

			}
		});
	}



	function edit_notes(sn) {

		$.ajax({
			url: "../inc/_ward_review_fetch.php",
			method: "POST",
			data: {
				edit_sn: sn
			},
			success: function(data) {

				var json = JSON.parse(data);
				document.getElementById('selected_review_note').innerHTML = '';
				document.getElementById('selected_review_note').value = json["notes"];
				document.getElementById('mode').value = 'edit';
				document.getElementById('notes_sn').value = json["notes_sn"];
				document.getElementById('edit__mode').innerHTML = '<strong>[ Edit Note Below ]</strong>';
				document.getElementById('pay_now').innerHTML = 'Save Edited Notes';
				document.getElementById("pay_now").disabled = false;
				//document.getElementById('selected_review_servie_id').value='';
			}
		});

	}
</script>