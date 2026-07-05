<?php

/////////////// Save Dialysis Note  /////////////////////


	$price_table_id = $old_procedure_id;
	$appointment_number = $app_no;
	$id = $sn_procedure_sn;
	$request_type = $procedure_id;
	$CREDIT_ENTRY = null;
	$DEBIT_ENTRY = null;
	$lg_ref_no  = '';

	 $sql = "SELECT * FROM patient_ap_services 
		WHERE app_no='$appointment_number' and hospital_no='$hospital_no'
		and drug_sn='$old_procedure_id' and paystatus = 1 ";
	$stmt = $db->query($sql);
	if ($stmt->rowCount() > 0) {
		$rw = $stmt->fetch(PDO::FETCH_ASSOC);

		$reverse_pay = $rw['pay'];
		$wallet_payee = $rw['wallet_payee'];
		$claim_amt = $rw['claim_amt'];
		$pay_mode = $rw['pay_mode'];
		$sale_sn = $rw['sn'];
		$rmks = 'Reversed: ' . $rw['item_services'];

		$emr = $hospital_no;
		$hos_no = $hospital_no;

		if ($reverse_pay > 0 and $pay_mode == 'cash') {
			

			// if ($wallet_payee == '') {
			
			// 	$stm = $db->query("SELECT sn,sale_sn,item_services,hospital_no,cr_amt,account_no FROM chart_ledger 
	        //             WHERE sale_sn='$sale_sn' and cr_amt > 0");
			// 	if ($stm->rowCount() == 1) {
			// 		$row_return = $stm->fetch(PDO::FETCH_ASSOC);
					
			// 		$sale_sn = $row_return['sale_sn'];
			// 		 $debit_gl_account = $row_return['account_no'];
			// 		if ($row_return['cr_amt'] > 0) {
			// 			$rmks = 'Reversed: '  . $row_return['item_services'] . '/Amt: ' . $row_return['cr_amt'];
			// 			$reverse_pay = $row_return['cr_amt'];
			// 		}
			// 	}
				include("../admin/reverse.php");
			// } else {
			// 	 $error_msg = 'Unable to Reserve Transaction! Wallet Transfer Payment Not Reversable!';
			// 	$error_status = 1;
			// }
		} elseif ($claim_amt > 0 and $pay_mode == 'claim') {
			$CREDIT_ENTRY = 'success';
			$DEBIT_ENTRY = 'success';
		} else {
			header("location:index.php?$redirect");
			exit;
		}


		if ($CREDIT_ENTRY == 'success' and $DEBIT_ENTRY == 'success') {

			//// save ap services 
			$stmt2 = $db->prepare("SELECT * FROM prices_table WHERE sn='$request_type'");
			$stmt2->execute();
			if ($stmt2->rowCount() > 0) {
				$row_serv = $stmt2->fetch(PDO::FETCH_ASSOC);
				$item_sn = $row_serv['sn'];
				$item_service = $row_serv['item_service'];
				$coverage = $row_serv['coverage'];
				$insurance_type = $row_serv['insurance_type'];
				$price_table = $row_serv['price_table'];
				$category = $row_serv['category'];		
				$cat_type = $row_serv['category'];		
				$hosp_price = $row_serv['hosp_price'];
				$nhis_price = $row_serv['nhis_price'];
				$ext_price = $row_serv['ext_price'];
				$dept_id = $row_serv['dept'];

				$part = explode("/", $coverage);
				$private = $part[0];
				$nhis = $part[1];

				$stmt = $db->prepare("UPDATE patient_ap_services SET paystatus=3, invoice_status=3 WHERE sn='$sale_sn'");
				$stmt->execute();

				$hos_no = $hospital_no;
				include_once("../inc/price_calc.php");

				/// check for special traff 
				//===============================
				if ($insurance != 'Private(Self)') {
					$stmt = $db->query("SELECT price FROM hmo_medical_tariff WHERE stock_sn='$request_type'and price>0 and hmo='$insurance_no'");
					if ($stmt->rowCount() > 0) {
						$row_dx = $stmt->fetch(PDO::FETCH_ASSOC);
						$claim_amt = $row_dx['price'];
						$ccop_int_charge = 0;
					}
				}
			}


			$invoice_no = rand() . $appointment_number;
			$paystatus = 0;

			$save_patient_service = save_patient_ap_service(
				$db,
				$appointment_number,
				$hospital_no,
				null,
				'Medical Services',
				$cat_type,
				$dept_id,
				$item_sn, ///['price_table_id'],
				$item_service, //;///['request_type'],
				$hosp_price, ///'],
				$claim_amt, ///['claim_amt'],
				$ccop_int_charge, ///"],
				0,
				$invoice_no,
				$_SESSION['fullname'],
				$amt_paying, ///['amount'],
				$pay_mode, ///['pay_mode'],
				null,
				null,
				null,
				null,
				null,
				'',
				false,
				0,
				null,
				$paystatus
			);


			if ($save_patient_service) {

				if ($amt_paying > 0) {
					$amount = $amt_paying;
				} else {
					$amount = $claim_amt;
				}


				$stmt = $db->prepare("UPDATE procedures 
					SET procedures = '$item_service', 
					    service_id = '$request_type', 
						cost = '$amount',
						old_procedure_id = '$old_procedure_id',
						old_procedure_name = '$old_procedure_name',
						WHERE id=$id ");
				$stmt->execute();
			}
			header("location:index.php?$redirect");
		} else {

			if ($reverse_pay > 0) {
				$update = "DELETE FROM chart_ledger WHERE lg_ref_no='$lg_ref_no'";
				$db->exec($update);
			}

			$error_status = 1;
			echo $error_msg = 'Error : Unable to Change Procedure!';
		}
	}else{

		$stmt = $db->prepare("UPDATE patient_ap_services SET paystatus=3, invoice_status=3 
			WHERE app_no='$appointment_number' and hospital_no='$hospital_no'
		and drug_sn='$old_procedure_id'  ");
				$stmt->execute();


		$stmt = $db->prepare("UPDATE procedures 
					SET procedures = '$item_service', 
					    service_id = '$request_type', 
						cost = '$amount',
						old_procedure_id = '$old_procedure_id',
						old_procedure_name = '$old_procedure_name',
						WHERE id=$id ");
				$stmt->execute();
				
	}

