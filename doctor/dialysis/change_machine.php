<?php
if (isset($_POST['change_machine'])) {

	try {
		$db->beginTransaction();

		$price_table_id = $_POST['price_table_id'];
		$appointment_number = $_POST['app_no'];
		$id = $_POST['id'];
		$hospital_no = $_POST['hospital_no'];
		$emr = $hospital_no;
		$hos_no = $hospital_no;
		$request_type = $_POST['request_type'];
		$redirect = $_POST['redirect'];
		$insurance = $_POST['insurance_type'];
		$interest = $_POST['interest'];
		$add_minus = $_POST['add_minus'];
		$payment_mode = $_POST['payment_mode'];

		// Fetch service record
		$stmt = $db->prepare("
			SELECT 
				pay,
				wallet_payee,
				claim_amt,
				pay_mode,
				sn,
				cr,
				paystatus,
				drug_status,
				drug_sn,
				wallet_debt_bill_to_acct,
				ledger_TX
			FROM patient_ap_services
			WHERE app_no = :app_no
			  AND hospital_no = :hospital_no
			  AND (cat_type = 'Dialysis' OR cat_type = 'Other Services')
			  AND drug_sn = :price_table_id
			LIMIT 1
		");
		$stmt->execute([
			':app_no'         => $appointment_number,
			':hospital_no'    => $hospital_no,
			':price_table_id' => $price_table_id
		]);

		if ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$reverse_pay  = $rw['pay'];
			$wallet_payee = $rw['wallet_payee'];
			$claim_amt    = $rw['claim_amt'];
			$pay_mode     = $rw['pay_mode'];
			$sale_sn      = $rw['sn'];
			$cr           = $rw['cr'];
			$paystatus    = $rw['paystatus'];
			$drug_status  = $rw['drug_status'];
			$drug_sn      = $rw['drug_sn'];
			$bill_to_acct = $rw['wallet_debt_bill_to_acct'];
			$lg_ref_no    = $rw['ledger_TX'];

			if ($paystatus == 1) {

				if ($pay_mode == 'spkage') {
					$updateSQL = "UPDATE patient_ap_services SET invoice_status = 1, paystatus = 0, transact_date = NULL, claim_valid_by = NULL, med_frequency = NULL WHERE sn = ?";
					$db->prepare($updateSQL)->execute([$sale_sn]);
				} elseif ($pay_mode == 'cash' && ($cr == 2 || $bill_to_acct == 'CREDIT' || $bill_to_acct == 'BILL')) {

					$updateSQL = "UPDATE patient_ap_services SET invoice_status = 1, paystatus = 0, cr = 0 WHERE sn = ?";
					$success1 = $db->prepare($updateSQL)->execute([$sale_sn]);

					if ($success1) {
						$deleteLedger = "DELETE FROM chart_ledger WHERE sale_sn = ?";
						$db->prepare($deleteLedger)->execute([$sale_sn]);
					} else {
						throw new Exception('Failed to update patient service for cash reversal.');
					}
				} elseif ($pay_mode == 'cash') {

					if ($wallet_payee == '') {
						include("../admin/reverse.php");
						///if ($save_done) {
						if ($status == "success") {
							$CREDIT_ENTRY = 'success';
							$DEBIT_ENTRY  = 'success';
						}
					} else {
						throw new Exception('Unable to Reverse Transaction! Wallet Transfer Payment Not Reversible!');
					}
				} elseif ($claim_amt > 0 && $pay_mode == 'claim') {
					$CREDIT_ENTRY = 'success';
					$DEBIT_ENTRY  = 'success';
				} else {
					echo "<script>window.location.href = 'dialysis.php?$redirect';</script>";
					exit;
				}
			} else {
				$CREDIT_ENTRY = 'success';
				$DEBIT_ENTRY  = 'success';
			}

			if ($CREDIT_ENTRY == 'success' && $DEBIT_ENTRY == 'success') {

				// Fetch service details
				$stmt2 = $db->prepare("SELECT * FROM prices_table WHERE sn = :request_type LIMIT 1");
				$stmt2->execute([':request_type' => $request_type]);

				if ($row_serv = $stmt2->fetch(PDO::FETCH_ASSOC)) {

					$item_sn        = $row_serv['sn'];
					$item_service   = $row_serv['item_service'];
					$coverage       = $row_serv['coverage'];
					$insurance_type = $row_serv['insurance_type'];
					$price_table    = $row_serv['price_table'];
					$category       = !empty($row_serv['category']) ? $row_serv['category'] : 'Dialysis';
					$hosp_price     = $row_serv['hosp_price'];
					$nhis_price     = $row_serv['nhis_price'];
					$ext_price      = $row_serv['ext_price'];
					$dept_id        = $row_serv['dept'];

					// Split coverage safely
					$part    = explode("/", $coverage);
					$private = isset($part[0]) ? $part[0] : 0;
					$nhis    = isset($part[1]) ? $part[1] : 0;

					// Harmonized update for paystatus
					if ($paystatus === 0 || $paystatus === 1) {
						$stmt = $db->prepare("
							UPDATE patient_ap_services 
							SET paystatus = 3, invoice_status = 3 
							WHERE sn = :sale_sn AND paystatus = :paystatus
						");
						$stmt->execute([
							':sale_sn'   => $sale_sn,
							':paystatus' => $paystatus
						]);
					}

					// Price recalculation
					$hos_no        = $hospital_no;
					$target_sn     = $request_type;
					$_tariff_table = "hmo_medical_tariff";
					include("../inc/price_calc.php");
				}

				$invoice_no = rand() . $appointment_number;
				$paystatus = 0;

				$save_patient_service = save_patient_ap_service(
					$db,
					$appointment_number,
					$hospital_no,
					null,
					'Medical Services',
					'Dialysis',
					$dept_id,
					$item_sn,
					$item_service,
					$hosp_price,
					$claim_amt,
					$ccop_int_charge,
					0,
					$invoice_no,
					$_SESSION['fullname'],
					$amt_paying,
					$pay_mode,
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
					$amount = ($amt_paying > 0) ? $amt_paying : $claim_amt;
					$stmt = $db->prepare("
						UPDATE dialysis 
						SET request_type = :item_service, 
							price_table_id = :request_type, 
							amount = :amount 
						WHERE id = :id
					");
					$stmt->bindParam(':item_service', $item_service, PDO::PARAM_STR);
					$stmt->bindParam(':request_type', $request_type, PDO::PARAM_INT);
					$stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
				} else {
					throw new Exception('Error: Unable to Complete Process!');
				}

				$db->commit(); // ✅ Commit all if successful
				echo "<script>window.location.href = 'dialysis.php?$redirect';</script>";
				exit;
			} else {

				if ($reverse_pay > 0) {
					$update = "DELETE FROM chart_ledger WHERE lg_ref_no = ?";
					$db->prepare($update)->execute([$lg_ref_no]);
				}

				throw new Exception('Error: Unable to Change Machine!');
			}
		}
	} catch (Exception $e) {
		$db->rollBack(); // ❌ Roll back on any failure
		$error_status = 1;
		$error_msg = 'Transaction failed: ' . $e->getMessage();
	}
}
