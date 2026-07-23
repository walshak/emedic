<?php

if (isset($_POST["save_users"])) {

	$data_mode = $_POST['data_mode'];
	$Username = $_POST['Username'];
	$EmployeeCode = $_POST['EmployeeCode'];



	if (isset($_POST['Specialist']) and $_POST['Specialist'] != '') {
		$Specialist = $_POST['Specialist'];
	} else {
		$Specialist = '';
	}

	if ($_POST['rights'] == 'LB' and $_POST["speciality"] == '' and $_POST["section"] == '') {
		header("location:index.php?rit&er");
		exit;
	}
	$stmt_usr = $db->prepare("SELECT * FROM admin_users WHERE username = :username");
	$stmt_usr->bindParam(':username', $Username, PDO::PARAM_STR);
	$stmt_usr->execute();

	if ($stmt_usr->rowCount() == 0 and $data_mode == 0) {


		$setdate = date("Y-m-d H:i:s");
		$temp = md5('staff123');
		$insertSQL = "INSERT INTO admin_users (EmployeeCode, username, fullname, password, date_updated, unit_head, rights, specialist, status) 
		VALUES (:EmployeeCode, :Username, :fullname, :password, :date_updated, :UnitHead, :rights, :Specialist, :LockStatus)";

		$stmt = $db->prepare($insertSQL);

		$stmt->bindParam(':EmployeeCode', $_POST['EmployeeCode'], PDO::PARAM_STR);
		$stmt->bindParam(':Username', $_POST['Username'], PDO::PARAM_STR);
		$stmt->bindParam(':fullname', $_POST['fullname'], PDO::PARAM_STR);
		$stmt->bindParam(':password', $temp, PDO::PARAM_STR); // Assuming $temp is the password hash or encrypted value
		$stmt->bindParam(':date_updated', $setdate, PDO::PARAM_STR);
		$stmt->bindParam(':UnitHead', $_POST['UnitHead'], PDO::PARAM_STR);
		$stmt->bindParam(':rights', $_POST['rights'], PDO::PARAM_STR);
		$stmt->bindParam(':Specialist', $Specialist, PDO::PARAM_STR); // Assuming $Specialist is defined elsewhere
		$stmt->bindParam(':LockStatus', $_POST['LockStatus'], PDO::PARAM_STR);

		$stmt->execute();

		$staff = $_SESSION['fullname'];
		$pid = null;
		$item_sn = null;
		$pname = $_POST['Username'];
		$desc = 'ADDING & CREATING PREVILEGES ' . $_POST['fullname'] . ' (' . $_POST['rights'] . ')';
		$action = 'NEW USER';
		include("../logs.php");


		/// PHARMACY
		$mgt_stock = '0';
		$print_invoice = '0';
		$manage_price = '0';
		$external_sale = '0';
		$ext_sale_recieve_cash = '0';
		$dsp_oncredit = '0';
		$view_consult_notes = '0';

		/// CASHIER
		$invoice = '0';
		$reciept = '0';
		$deposit = '0';
		$lab = '0';
		$pharm = '0';
		$nursing = '0';
		$other_bill = '0';
		$reprint = '0';
		$transfer = '0';
		$refund = '0';
		$discount = '0';
		$claims = '0';
		$vouchers = '0';
		$reversal = '0';
		$writeoff = '0';

		//// LAB PRIVE
		$create = '0';
		$price = '0';
		$request = '1';
		$specimen = '0';
		$enter = '0';
		$approve = '0';
		$stock = '0';
		$inventory = '0';
		$report = '0';
		$users = '0';
		$oncredit = '0';
		$oncredit_adm = '0';
		$sms = '0';
		$equipmnt = '0';
		$inv = '0';
		$bill = '0';
		$edit_appr = '0';


		//// rights and privileges	/// ADMIN PRIVILEGES
		$patient_master = '0';
		$create_edit_insur = '0';
		$add_new_patient = '0';
		$convert_patient_insur = '0';
		$admitted_patient_alert = '0';
		$ext_sales = '0';
		$biller = '0';
		$rdc = '0';
		$enquiry = '0';
		$gen_claim_rpt = '0';
		$hr_payroll = '0';
		$doc_income = '0';
		$report_mgr = '0';
		$price_mgr = '0';
		$bed_mgr = '0';
		$accom_price = '0';
		$writeoff_discharge = '0';
		$see_statistic_rpt = '0';
		$see_med_rpt = '0';
		$see_patient_log = '0';
		$see_patient_tranc_writeoff = '0';
		$discount = '0';
		$voucher = '0';
		$webmedic_admin_right = '0';
		$stock_mgr = '0';
		$procure = '0';
		$stock_mgr_pharm = '0';
		$purchase_approval = '0';
		$rq_approval = '0';
		$b4_rq_approval = '0';
		$pharmacy = '0';
		$nursing = '0';
		$doctor = '0';
		$remove_stock_qty = '0';
		$procedure_module = '0';
		$dialysis = '0';
		$transplant = '0';
		$PO_payment = '0';


		if ($_POST['rights'] == 'RE' or $_POST['rights'] == 'AC') {

			$patient_master = '1';
			$add_new_patient = '1';
			$ext_sales = '1';
			$enquiry = '1';
			$rdc = '1';
			$create_edit_insur = '1';
			$convert_patient_insur = '1';
			$gen_claim_rpt = '1';
			$admitted_patient_alert = '1';

			if ($_POST['rights'] == 'AC') {
				$speciality = 'Administrator';
			} else {
				$speciality = 'Receptionist';
			}
		}


		if ($_POST['rights'] == 'CA' or $_POST['rights'] == 'AC') {
			$biller = '1';
			$ext_sales = '1';
			$enquiry = '1';
			$speciality = 'User';
		}



		if ($_POST['rights'] == 'AO' or $_POST['rights'] == 'AC' or $_POST['rights'] == 'AD') {
			$hr_payroll = '1';
			$enquiry = '1';
			$speciality = 'Administrator';
		}


		if ($_POST['rights'] == 'PH') {

			$stock_mgr_pharm = '1';
			$mgt_stock = '1';
			$print_invoice = '1';

			if ($_POST['UnitHead'] == '1') {
				$manage_price = '1';
				$view_consult_notes = '1';
				//$dsp_oncredit='0';
			}
			$external_sale = '1';
			$ext_sale_recieve_cash = '0';

			$speciality = 'User';
		}


		if ($_POST['rights'] == 'ST') {
			$stock_mgr = '1';
			$procure = '1';
			$b4_rq_approval = '0';
			$rq_approval = '1';
			$speciality = 'User';
		}


		if ($_POST['rights'] == 'AC') {
			$doc_income = '1';
			$report_mgr = '1';
			$price_mgr = '1';
			$bed_mgr = '1';
			$accom_price = '1';
			$discount = '1';
			$voucher = '1';
			$purchase_approval = '1';
			$stock_mgr = '1';
			$stock_mgr_pharm = '1';
			$speciality = 'Administrator';
		}

		if ($_POST['rights'] == 'MD'  or $_POST['rights'] == 'GM') {

			$patient_master = '1';
			$add_new_patient = '1';
			$ext_sales = '1';
			$enquiry = '1';
			$rdc = '1';

			/// hmo 
			$create_edit_insur = '1';
			$convert_patient_insur = '1';
			$gen_claim_rpt = '1';
			$admitted_patient_alert = '1';

			$biller = '1';

			$hr_payroll = '1';
			$doc_income = '1';
			$report_mgr = '1';
			$price_mgr = '1';
			$bed_mgr = '1';
			$accom_price = '1';
			$writeoff_discharge = '1';
			$see_statistic_rpt = '1';

			$see_med_rpt = '1';
			$see_patient_log = '1';
			$see_patient_tranc_writeoff = '1';
			$discount = '1';
			$voucher = '1';
			$webmedic_admin_right = '1';

			$stock_mgr = '1';
			$procure = '1';
			$stock_mgr_pharm = '1';
			$purchase_approval = '1';
			$rq_approval = '1';
			$b4_rq_approval = '1';

			$pharmacy = '1';
			$nursing = '1';
			$doctor = '1';
			$remove_stock_qty = '1';
			$procedure_module = '1';
			$dialysis = '1';
			$transplant = '1';
			$PO_payment = '1';

			$speciality = 'Administrator';
		}

		if ($_POST['rights'] == 'LB') {

			if ($_POST['UnitHead'] == '1') {
				$create = '1';
				$price = '1';
				$stock = '1';
				$users = '1';
				$oncredit_adm = '1';
				$edit_appr = '1';
				$inventory = '1';
				$equipmnt = '1';
				$report = '1';
			}
			$request = '1';
			$specimen = '1';
			$enter = '1';
			$approve = '1';
			$sms = '1';
			$inv = '1';
		}

		//// CASHIER CODE LINES ////

		if ($_POST['rights'] == 'CA' or $_POST['rights'] == 'AC') {

			$invoice = '1';
			$reciept = '1';
			$deposit = '1';
			$lab = '1';
			$pharm = '1';
			$nursing = '1';
			$other_bill = '1';
			$reprint = '1';
			$claims = '1';
			$request = '1';


			if ($_POST['rights'] == 'AC' or $_POST['rights'] == 'GM') {
				$transfer = '1';
				$refund = '1';
				$discount = '1';
				$vouchers = '1';
				$reversal = '1';
				$writeoff = '1';
			}
		}


		if ($_POST['rights'] != 'LB') {

			$speciality = 'Administrator';
			$stmt = $db->prepare("INSERT INTO admin_users_rights (
			username,
			speciality,
			hr_payroll,
			doc_income,
			rdc,
			report_mgr,
			price_mgr,
			bed_mgr,
			accom_price,
			writeoff_discharge,
			see_statistic_rpt,
			patient_master,
			create_edit_insur,
			add_new_patient,
			convert_patient_insur,
			see_med_rpt,
			see_patient_log,
			see_patient_tranc_writeoff,
			admitted_patient_alert,
			ext_sales,
			biller,
			discount,
			voucher,
			webmedic_admin_right,
			enquiry,
			stock_mgr,
			stock_mgr_pharm,
			purchase_approval,
			procure,
			rq_approval,
			b4_rq_approval,
			gen_claim_rpt,
			writeoff,
			pharmacy,
			nursing,
			doctor,
			remove_stock_qty,
			transplant,
			dialysis,
			PO_payment,
			procedure_module
	) VALUES (
		:Username,
		:speciality,
		:hr_payroll,
		:doc_income,
		:rdc,
		:report_mgr,
		:price_mgr,
		:bed_mgr,
		:accom_price,
		:writeoff_discharge,
		:see_statistic_rpt,
		:patient_master,
		:create_edit_insur,
		:add_new_patient,
		:convert_patient_insur,
		:see_med_rpt,
		:see_patient_log,
		:see_patient_tranc_writeoff,
		:admitted_patient_alert,
		:ext_sales,
		:biller,
		:discount,
		:voucher,
		:webmedic_admin_right,
		:enquiry,
		:stock_mgr,
		:stock_mgr_pharm,
		:purchase_approval,
		:procure,
		:rq_approval,
		:b4_rq_approval,
		:gen_claim_rpt,
		:writeoff,
		:pharmacy,
		:nursing,
		:doctor,
		:remove_stock_qty,
		:transplant,
		:dialysis,
		:PO_payment,
		:procedure_module
	)");

			$stmt->bindParam(':Username', $_POST['Username'], PDO::PARAM_STR);
			$stmt->bindParam(':speciality', $speciality, PDO::PARAM_STR);
			$stmt->bindParam(':hr_payroll', $hr_payroll, PDO::PARAM_STR);
			$stmt->bindParam(':doc_income', $doc_income, PDO::PARAM_STR);
			$stmt->bindParam(':rdc', $rdc, PDO::PARAM_STR);
			$stmt->bindParam(':report_mgr', $report_mgr, PDO::PARAM_STR);
			$stmt->bindParam(':price_mgr', $price_mgr, PDO::PARAM_STR);
			$stmt->bindParam(':bed_mgr', $bed_mgr, PDO::PARAM_STR);
			$stmt->bindParam(':accom_price', $accom_price, PDO::PARAM_STR);
			$stmt->bindParam(':writeoff_discharge', $writeoff_discharge, PDO::PARAM_STR);
			$stmt->bindParam(':see_statistic_rpt', $see_statistic_rpt, PDO::PARAM_STR);
			$stmt->bindParam(':patient_master', $patient_master, PDO::PARAM_STR);
			$stmt->bindParam(':create_edit_insur', $create_edit_insur, PDO::PARAM_STR);
			$stmt->bindParam(':add_new_patient', $add_new_patient, PDO::PARAM_STR);
			$stmt->bindParam(':convert_patient_insur', $convert_patient_insur, PDO::PARAM_STR);
			$stmt->bindParam(':see_med_rpt', $see_med_rpt, PDO::PARAM_STR);
			$stmt->bindParam(':see_patient_log', $see_patient_log, PDO::PARAM_STR);
			$stmt->bindParam(':see_patient_tranc_writeoff', $see_patient_tranc_writeoff, PDO::PARAM_STR);
			$stmt->bindParam(':admitted_patient_alert', $admitted_patient_alert, PDO::PARAM_STR);
			$stmt->bindParam(':ext_sales', $ext_sales, PDO::PARAM_STR);
			$stmt->bindParam(':biller', $biller, PDO::PARAM_STR);
			$stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
			$stmt->bindParam(':voucher', $voucher, PDO::PARAM_STR);
			$stmt->bindParam(':webmedic_admin_right', $webmedic_admin_right, PDO::PARAM_STR);
			$stmt->bindParam(':enquiry', $enquiry, PDO::PARAM_STR);
			$stmt->bindParam(':stock_mgr', $stock_mgr, PDO::PARAM_STR);
			$stmt->bindParam(':stock_mgr_pharm', $stock_mgr_pharm, PDO::PARAM_STR);
			$stmt->bindParam(':purchase_approval', $purchase_approval, PDO::PARAM_STR);
			$stmt->bindParam(':procure', $procure, PDO::PARAM_STR);
			$stmt->bindParam(':rq_approval', $rq_approval, PDO::PARAM_STR);
			$stmt->bindParam(':b4_rq_approval', $b4_rq_approval, PDO::PARAM_STR);
			$stmt->bindParam(':gen_claim_rpt', $gen_claim_rpt, PDO::PARAM_STR);
			$stmt->bindParam(':writeoff', $writeoff, PDO::PARAM_STR);
			$stmt->bindParam(':pharmacy', $pharmacy, PDO::PARAM_STR);
			$stmt->bindParam(':nursing', $nursing, PDO::PARAM_STR);
			$stmt->bindParam(':doctor', $doctor, PDO::PARAM_STR);
			$stmt->bindParam(':remove_stock_qty', $remove_stock_qty, PDO::PARAM_STR);
			$stmt->bindParam(':transplant', $transplant, PDO::PARAM_STR);
			$stmt->bindParam(':dialysis', $dialysis, PDO::PARAM_STR);
			$stmt->bindParam(':PO_payment', $PO_payment, PDO::PARAM_STR);
			$stmt->bindParam(':procedure_module', $procedure_module, PDO::PARAM_STR);

			$stmt->execute();
		}

		//// LAB ENTRY



		if ($_POST['rights'] == 'LB' or $_POST['rights'] == 'MD'  or $_POST['rights'] == 'GM' or $_POST['rights'] == 'RE' or $_POST['rights'] == 'AC') {

			$stmt = $db->prepare("SELECT username FROM invsti_users WHERE username = :username");
			$stmt->bindParam(':username', $_POST["Username"], PDO::PARAM_STR);
			$stmt->execute();
			//	$speciality=$_POST["speciality"];
			if ($speciality == "Administrator" or $speciality == "Receptionist" or $speciality == "User") {
				$section = "";
			} else {
				$section = $_POST["section"];
			}

			if ($stmt->rowCount() == 0) {

				$stmt = $db->prepare("INSERT INTO invsti_users (
					username,
					speciality,
					section,
					create1,
					price,
					request,
					specimen,
					enter,
					approve,
					stock,
					inventory,
					report,
					users,
					oncredit,
					oncredit_adm,
					sms,
					equipmnt,
					inv,
					bill,
					edit_appr
				) VALUES (
					:Username,
					:speciality,
					:section,
					:create1,
					:price,
					:request,
					:specimen,
					:enter,
					:approve,
					:stock,
					:inventory,
					:report,
					:users,
					:oncredit,
					:oncredit_adm,
					:sms,
					:equipmnt,
					:inv,
					:bill,
					:edit_appr
				)");

				$stmt->bindParam(':Username', $_POST["Username"], PDO::PARAM_STR);
				$stmt->bindParam(':speciality', $speciality, PDO::PARAM_STR);
				$stmt->bindParam(':section', $section, PDO::PARAM_STR);
				$stmt->bindParam(':create1', $create, PDO::PARAM_STR);
				$stmt->bindParam(':price', $price, PDO::PARAM_STR);
				$stmt->bindParam(':request', $request, PDO::PARAM_STR);
				$stmt->bindParam(':specimen', $specimen, PDO::PARAM_STR);
				$stmt->bindParam(':enter', $enter, PDO::PARAM_STR);
				$stmt->bindParam(':approve', $approve, PDO::PARAM_STR);
				$stmt->bindParam(':stock', $stock, PDO::PARAM_STR);
				$stmt->bindParam(':inventory', $inventory, PDO::PARAM_STR);
				$stmt->bindParam(':report', $report, PDO::PARAM_STR);
				$stmt->bindParam(':users', $users, PDO::PARAM_STR);
				$stmt->bindParam(':oncredit', $oncredit, PDO::PARAM_STR);
				$stmt->bindParam(':oncredit_adm', $oncredit_adm, PDO::PARAM_STR);
				$stmt->bindParam(':sms', $sms, PDO::PARAM_STR);
				$stmt->bindParam(':equipmnt', $equipmnt, PDO::PARAM_STR);
				$stmt->bindParam(':inv', $inv, PDO::PARAM_STR);
				$stmt->bindParam(':bill', $bill, PDO::PARAM_STR);
				$stmt->bindParam(':edit_appr', $edit_appr, PDO::PARAM_STR);

				// Assuming variables $speciality, $section, $create, $price, etc. are properly initialized with appropriate values

				$stmt->execute();
			}
		}


		if ($_POST['rights'] == 'PH') {

			$Pharmacist = 'Pharmacist';

			$stmt = $db->prepare("SELECT username FROM pharm_users WHERE username = :username");
			$stmt->bindParam(':username', $_POST["Username"], PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() == 0) {
				$stmt_insert = $db->prepare("INSERT INTO pharm_users (
        username,
        speciality,
        mgt_stock,
        print_invoice,
        manage_price,
        external_sale,
        ext_sale_recieve_cash,
        dsp_oncredit,
        view_consult_notes
    ) VALUES (
        :Username,
        :speciality,
        :mgt_stock,
        :print_invoice,
        :manage_price,
        :external_sale,
        :ext_sale_recieve_cash,
        :dsp_oncredit,
        :view_consult_notes
    )");

				$stmt_insert->bindParam(':Username', $_POST["Username"], PDO::PARAM_STR);
				$stmt_insert->bindParam(':speciality', $Pharmacist, PDO::PARAM_STR);
				$stmt_insert->bindParam(':mgt_stock', $mgt_stock, PDO::PARAM_STR);
				$stmt_insert->bindParam(':print_invoice', $print_invoice, PDO::PARAM_STR);
				$stmt_insert->bindParam(':manage_price', $manage_price, PDO::PARAM_STR);
				$stmt_insert->bindParam(':external_sale', $external_sale, PDO::PARAM_STR);
				$stmt_insert->bindParam(':ext_sale_recieve_cash', $ext_sale_recieve_cash, PDO::PARAM_STR);
				$stmt_insert->bindParam(':dsp_oncredit', $dsp_oncredit, PDO::PARAM_STR);
				$stmt_insert->bindParam(':view_consult_notes', $view_consult_notes, PDO::PARAM_STR);

				$stmt_insert->execute();
			}
		}


		if ($_POST['rights'] == 'CA' or $_POST['rights'] == 'AC' or $_POST['rights'] == 'MD' or $_POST['rights'] == 'GM') {

			$stmt = $db->prepare("SELECT username FROM biller_users WHERE username = :username");
			$stmt->bindParam(':username', $_POST["Username"], PDO::PARAM_STR);
			$stmt->execute();
			if ($_POST['rights'] == 'CA') {
				$speciality = 'Receptionist';
			} else {
				$speciality = 'Administrator';
			}

			if ($stmt->rowCount() == 0) {
				$stmt = $db->prepare("INSERT INTO biller_users (
			username,
			speciality,
			invoice,
			reciept,
			deposit,
			lab,
			pharm,
			nursing,
			other_bill,
			reprint,
			transfer,
			refund,
			discount,
			claims,
			vouchers,
			reversal,
			writeoff
		) VALUES (
			:Username,
			:speciality,
			:invoice,
			:reciept,
			:deposit,
			:lab,
			:pharm,
			:nursing,
			:other_bill,
			:reprint,
			:transfer,
			:refund,
			:discount,
			:claims,
			:vouchers,
			:reversal,
			:writeoff
		)");

				$stmt->bindParam(':Username', $_POST["Username"], PDO::PARAM_STR);
				$stmt->bindParam(':speciality', $speciality, PDO::PARAM_STR);
				$stmt->bindParam(':invoice', $invoice, PDO::PARAM_STR);
				$stmt->bindParam(':reciept', $reciept, PDO::PARAM_STR);
				$stmt->bindParam(':deposit', $deposit, PDO::PARAM_STR);
				$stmt->bindParam(':lab', $lab, PDO::PARAM_STR);
				$stmt->bindParam(':pharm', $pharm, PDO::PARAM_STR);
				$stmt->bindParam(':nursing', $nursing, PDO::PARAM_STR);
				$stmt->bindParam(':other_bill', $other_bill, PDO::PARAM_STR);
				$stmt->bindParam(':reprint', $reprint, PDO::PARAM_STR);
				$stmt->bindParam(':transfer', $transfer, PDO::PARAM_STR);
				$stmt->bindParam(':refund', $refund, PDO::PARAM_STR);
				$stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
				$stmt->bindParam(':claims', $claims, PDO::PARAM_STR);
				$stmt->bindParam(':vouchers', $vouchers, PDO::PARAM_STR);
				$stmt->bindParam(':reversal', $reversal, PDO::PARAM_STR);
				$stmt->bindParam(':writeoff', $writeoff, PDO::PARAM_STR);

				$stmt->execute();
			}
		}



		header("location:index.php?rit&sv");
	} elseif ($stmt_usr->rowCount() > 0 and $data_mode == 0) {
		header("location:index.php?rit&er");
	} else {

		$roww = $stmt_usr->fetch(PDO::FETCH_ASSOC);
		$desc = "OLD: " .  $roww['fullname'] . " RIGHT: " .  $roww['rights'] . " HEAD: " .  $roww['unit_head'] . " SP: " .  $roww['specialist'];

		$empCode = $_POST['EmployeeCode'];

		// 1. Get old data before update
		$getOldSQL = "SELECT * FROM admin_users WHERE EmployeeCode = :empCode";
		$getStmt = $db->prepare($getOldSQL);
		$getStmt->bindParam(':empCode', $empCode, PDO::PARAM_STR);
		$getStmt->execute();
		$oldData = $getStmt->fetch(PDO::FETCH_ASSOC);

		// 2. Perform the update
		$updateSQL = "UPDATE admin_users 
    SET fullname = :fullname,
        rights = :rights,
        unit_head = :unit_head,
        specialist = :specialist,
        status = :status 
    WHERE EmployeeCode = :employeeCode";

		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':fullname', $_POST['fullname'], PDO::PARAM_STR);
		$stmt->bindParam(':rights', $_POST['rights'], PDO::PARAM_STR);
		$stmt->bindParam(':unit_head', $_POST['UnitHead'], PDO::PARAM_STR);
		$stmt->bindParam(':specialist', $_POST['Specialist'], PDO::PARAM_STR);
		$stmt->bindParam(':status', $_POST['LockStatus'], PDO::PARAM_STR);
		$stmt->bindParam(':employeeCode', $empCode, PDO::PARAM_STR);
		$stmt->execute();

		// 3. Compare old vs. new and record changes
		$changes = [];

		$map = [
			'fullname'   => 'fullname',
			'rights'     => 'rights',
			'unit_head'  => 'UnitHead',
			'specialist' => 'Specialist',
			'status'     => 'LockStatus'
		];

		foreach ($map as $dbField => $postKey) {
			$oldValue = $oldData[$dbField];
			$newValue = isset($_POST[$postKey]) ? $_POST[$postKey] : null; // no ?? null
			if ($oldValue != $newValue) {
				$changes[] = "$dbField changed from '{$oldValue}' to '{$newValue}'";
			}
		}


		// 4. Insert log entry if any changes
		if (!empty($changes)) {
			$description = implode("; ", $changes);
			$logSQL = "INSERT INTO patient_staff_logs 
               (patient_id, descriptions, staff_name, action, date_and_time) 
               VALUES (:patient_id, :descriptions, :staff_name, :action, :date_and_time)";
			$logStmt = $db->prepare($logSQL);
			$logStmt->bindParam(':patient_id', $empCode);
			$logStmt->bindParam(':descriptions', $description);
			$logStmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
			$logStmt->bindValue(':action', 'Admin user update', PDO::PARAM_STR);
			$logStmt->bindValue(':date_and_time', date('Y-m-d H:i:s'), PDO::PARAM_STR);
			$logStmt->execute();
		}


		if ($roww['rights'] != $_POST['rights']) {
			header("location:index.php?rit&sv_adjust_rights");
		} else {
			header("location:index.php?rit&sv");
		}
	}
}


if (isset($_GET["reset"])) {
	$ECode = $_GET["ECode"];
	$temp = md5('staff123');
	$updateSQL = "UPDATE admin_users SET password = :password WHERE EmployeeCode = :employeeCode";

	$stmt = $db->prepare($updateSQL);

	$stmt->bindParam(':password', $temp, PDO::PARAM_STR);
	$stmt->bindParam(':employeeCode', $ECode, PDO::PARAM_STR);

	$stmt->execute();

	header("location:index.php?rit&sv");
}



// new code
if (isset($_POST['do_block'])) {
	$action = $_POST['action'];
	$staff_no = $_POST['ECode'];

	if ($action == 'block') {
		$stmt = $db->prepare("UPDATE admin_users SET status = 0 WHERE EmployeeCode = ? ");
		if ($stmt->execute([$staff_no])) {
			$sv = 1;
		}
	} elseif ($action == 'unblock') {
		$date_updated = date("Y-m-d");
		$stmt = $db->prepare("UPDATE admin_users SET status = 1, date_updated='$date_updated' WHERE EmployeeCode = ? ");
		if ($stmt->execute([$staff_no])) {
			$sv = 1;
		}
	}
}
// end new code



?>




<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Users Rights Manager</h5>
			</div>

			<div class="ibox-content">


				<div align="center">
					<h2>PRIVILEGES & RIGHTS</h2>
					<a href="index.php?min" class="btn btn-app"><i class="fa fa-lock"></i>General Rights</a>
					<a href="index.php?rdc" class="btn btn-app"><i class="fa fa-users"></i>Rad/Lab Rights</a>
					<a href="index.php?bill" class="btn btn-app"><i class="fa fa-th"></i>Biller/Cashier Rights</a>
					<a href="index.php?phm" class="btn btn-app"><i class="fa fa-tag"></i>Pharmacy Rights</a>

					<?php if (isset($_SESSION['rights']) == 'MD') { ?>
						<a href="index.php?right_report" class="btn btn-app"><i class="fa fa-plus"></i>Rights Report</a>
					<?php } ?>
				</div>


				<hr>
				<br>


				<div align="center">
					<h2>ASSIGN STAFF AND LOGIN DETAILS</h2>
				</div>

				<?php
				$stmt_list = $db->query("SELECT * FROM hremp order by sn");
				if ($stmt_list->rowCount() > 0) { ?>


					<table class="table table-striped table-bordered table-hover dataTables-example">
						<thead>
							<tr>
								<th></th>
								<th>Emp.#</th>
								<th>Name</th>
								<th>Cadre</th>
								<th>Designation</th>
								<th>Status</th>
								<th width="12%">Manage</th>
								<th width="3%">.</th>
								<th>Username</th>

							</tr>
						</thead>
						<tbody>
							<?php
							$c = 1;
							while ($row = $stmt_list->fetch(PDO::FETCH_ASSOC)) { ?>
								<tr>
									<td><?php echo $c; ?></td>
									<td><?php echo $row['EmployeeCode']; ?></td>
									<td><?php echo $row['FirstName'] . ', ' . $row['LastName'] . ' ' . $row['MiddleName']; ?></td>
									<td><?php echo $row['Cadre']; ?></td>
									<td><?php echo $row['Designation']; ?></td>
									<td>
										<?php
										if ($row['status'] == 0) {
											echo 'Engaged';
										} elseif ($row['status'] == 2) {
											echo 'Suspended';
										} else {
											echo 'Disengaged';
										}
										?>
									</td>
									<td>

										<input type="button" name="users" value="Assign to WebMedic" data-target="#modal" id="<?php echo $row["EmployeeCode"]; ?>" class="btn btn-success btn-xs webmedic_users" />
									</td>
									<td>
										<?php
										$ECode = $row['EmployeeCode'];
										$stmt = $db->prepare("SELECT status,username FROM admin_users WHERE EmployeeCode = ? LIMIT 1");
										if ($stmt->execute([$ECode])) {
											$d = $stmt->fetch(PDO::FETCH_ASSOC);
											if ($stmt->rowCount() == 1) {
												if ($d['status'] == 1) { ?>
													<form action="" method="post">
														<input type="hidden" value="<?= $ECode ?>" name="ECode">
														<input type="hidden" value="block" name="action">
														<input type="hidden" name="do_block">
														<button type="submit" onclick="return confirm('Are you sure you wish to block this user?');"><span class="fa fa-lock text-danger"></span></button>
													</form>
												<?php } else { ?>
													<form action="" method="post">
														<input type="hidden" value="<?= $ECode ?>" name="ECode">
														<input type="hidden" value="unblock" name="action">
														<input type="hidden" name="do_block">
														<button type="submit" onclick="return confirm('Are you sure you wish to unblock this user?');"><span class="fa fa-unlock text-success"></span></button>
													</form>
										<?php }
											}
										}
										?>
									</td>
									<td>
										<?php echo $d['username']; ?>
									</td>
								</tr>
							<?php

								$c++;
							} ?>

						</tbody>
					</table>

				<?php } else { ?>
					<br>
					<strong style="font-size:14px">No Data to display. Click Here .. </strong> <a href="index.php?hr"><i class="fa fa-refresh"></i>&nbsp;Refresh</a>
					<br>
				<?php }

				?>

			</div>

		</div>
	</div>
</div>


<div class="modal inmodal fade" id="webmedic_user_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Webmedic App Users</h4>
			</div>
			<div class="modal-body" id="webmedic_users_body">



			</div>
		</div>
	</div>
</div>