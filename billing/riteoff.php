<?php
$reversal = 0;
$writeoff = 0;
$stmt = $db->prepare('SELECT reversal,writeoff FROM biller_users WHERE username = :username');
$stmt->execute([
	':username' => $_SESSION["username"]
]);
if ($stmt->rowCount() > 0) {
	$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
	$reversal = $rowx['reversal'];
	$writeoff = $rowx['writeoff'];
}



if (isset($_GET['rrof']) and $_SESSION['fullname'] != '' && $reversal === 1) {
	$parts = $_GET['rrof'];
	$pp = explode("/", $parts);

	$sn_encoded = $pp[0];
	$sn = base64_decode(base64_decode($sn_encoded));
	$start = $pp[2];
	$end = $pp[3];
	$Transaction_Report = $pp[1];
	$cr = 0;

	$stmt = $db->prepare("SELECT item_services, pay, qty, hospital_no, drug_status FROM patient_ap_services WHERE sn = :sn AND drug_status = 0");
	$stmt->execute(['sn' => $sn]);

	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$rmks = 'Hosp/No.' . $row['hospital_no'] . '/ ' . $row['item_services'] . '/Amt: ' . $row['pay'] . '/Qty: ' . $row['qty'];

		$staff = $_SESSION['fullname'];
		$pid = $row['hospital_no'];
		$item_sn = $sn;
		$pname = '';
		$desc = $rmks;
		$action = 'WriteOff Reversed';
		include_once("../admin/logs.php");

		if ($row['drug_status'] == 1) {
			$cr = 1;
		}

		$inv = "UPDATE patient_ap_services 
            SET pay_mode = 'cash', paystatus = '0', transact_date = '0000-00-00', cr = :cr 
            WHERE sn = :sn";
		$updateStmt = $db->prepare($inv);
		$updateStmt->execute(['cr' => $cr, 'sn' => $sn]);
	} else {
		echo "<div class='alert alert-danger'>
        <p> Oops! Invalid Re-Write off. Service has been delivered. </p>
    </div>";
	}
} elseif (isset($_GET['delete']) and $_SESSION['fullname'] != '') {
	$parts = $_GET['delete'];
	$sn = base64_decode(base64_decode($parts));
	$cr = 0;
	try {
		// Begin transaction
		$db->beginTransaction();

		// Prepare and execute query with placeholders
		$stmt2 = $db->prepare("SELECT ledger_TX, serv_group,drug_status,hospital_no,item_services,pay 
		FROM patient_ap_services WHERE sn = :sn AND paystatus = 1");
		$stmt2->execute([':sn' => $sn]);

		if ($stmt2->rowCount() > 0) {
			$rowx = $stmt2->fetch(PDO::FETCH_ASSOC);
			$ledger_TX = $rowx['ledger_TX'];
			$serv_group = $rowx['serv_group'];
			$drug_status = $rowx['drug_status'];
			$hospital_no = $rowx['hospital_no'];
			$pay = $rowx['pay'];
			$item_services = $rowx['item_services'];
			$action_to_take = 'Deleted Transaction';

			$setdatetime = date("Y-m-d H:i:s");
			$desc = 'Deleted Transaction: By' . $_SESSION['fullname'] . ' / ' . $item_services . '(' . $pay . ') / ' . $setdatetime;
			if ($drug_status == 1) {
				$cr = 1;
			}

			// Execute delete operation with placeholders
			$deleteStmt = $db->prepare("DELETE FROM chart_ledger WHERE lg_ref_no = :lg_ref_no AND sale_sn = :sn");
			$deleteStmt->execute([':lg_ref_no' => $ledger_TX, ':sn' => $sn]);

			// Execute update operation with placeholders
			$updateStmt = $db->prepare("UPDATE patient_ap_services SET paystatus = 0, cr = '$cr', transact_date = '' WHERE ledger_TX = :ledger_TX AND sn = :sn");
			$updateStmt->execute([':ledger_TX' => $ledger_TX, ':sn' => $sn]);


			$sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,action,date_and_time) 
				VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:action,:date_and_time)");
			$sql->bindParam(':item_sn', $sn, PDO::PARAM_STR);
			$sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
			$sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
			$sql->bindParam(':patient_id', $hospital_no, PDO::PARAM_STR);
			$sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
			$sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
			$sql->execute();

			$error_msg = 'Post Has Been Deleted';
			$error_status = 2;
			//	}
		} else {
			$error_msg = 'No records found';
			$error_status = 1;
		}

		// Commit transaction
		$db->commit();
	} catch (PDOException $e) {
		// Rollback transaction in case of PDO error
		$db->rollBack();
		$error_msg = 'Database error occurred: ' . $e->getMessage();
		$error_status = 1;
	} catch (Exception $e) {
		// Rollback transaction in case of general error
		$db->rollBack();
		$error_msg = 'An unexpected error occurred: ' . $e->getMessage();
		$error_status = 1;
	}

	// Output error message for debugging (for development purposes only)
	if ($error_status == 1) {
		echo $error_msg;
	}
} elseif (isset($_GET['reverse']) && !empty($_SESSION['fullname']) && $reversal === 1) {

	$parts = explode("/", filter_var($_GET['reverse'], FILTER_SANITIZE_STRING));
	$sn = isset($parts[0]) ? $parts[0] : '';
	$sn = $sale_sn = base64_decode(base64_decode($sn));
	$Transaction_Report = isset($parts[1]) ? $parts[1] : '';
	$start = isset($parts[2]) ? $parts[2] : '';
	$end = isset($parts[3]) ? $parts[3] : '';

	if ($sn == '') {
		$error_msg = 'Invalid service number!';
		$error_status = 1;
	} else {
		$stmt2 = $db->prepare("SELECT sn, serv_group, wallet_payee, cr, pay_mode, drug_status, 
			hospital_no,wallet_debt_bill_to_acct,drug_sn,ledger_TX FROM patient_ap_services WHERE sn = ?");
		$stmt2->execute([$sn]);

		if ($stmt2->rowCount() > 0) {
			$rowx = $stmt2->fetch(PDO::FETCH_ASSOC);

			$serv_group   = $rowx['serv_group'];
			$drug_sn   = $rowx['drug_sn'];
			$wallet_payee = $rowx['wallet_payee'];
			$pay_mode     = $rowx['pay_mode'];
			$drug_status  = $rowx['drug_status'];
			$lg_ref_no  = $rowx['ledger_TX'];
			$cr           = $rowx['cr'];
			$bill_to_acct = $rowx['wallet_debt_bill_to_acct'];
			// 

			if ($bill_to_acct == 'PWALET') {
				$error_msg = 'You Can Not Reverse Service That Has Been Paid From Another Wallet!';
				$error_status = 1;
			}
			// Restriction checks
			elseif ($drug_status == 1) {
				$error_msg = 'You Can Not Reverse Service That Has Been Delivered!';
				$error_status = 1;
			} elseif (in_array($serv_group, ['Pharmacy', 'Consultation', 'Registration'])) {
				$error_msg = 'You Can Not Reverse Pharmacy or Consultation Services Here';
				$error_status = 1;
			} else {
				// Check if chart_ledger has valid entry
				$stm = $db->prepare("SELECT sn, sale_sn, item_services, hospital_no, cr_amt, account_no, lg_ref_no 
				FROM chart_ledger WHERE sale_sn = ? AND cr_amt > 0");
				$stm->execute([$sn]);

				try {
					$db->beginTransaction();

					if ($pay_mode == 'spkage') {
						$updateSQL = "UPDATE patient_ap_services SET invoice_status = 1, paystatus = 0, transact_date =null, claim_valid_by =null, med_frequency = null WHERE sn = ?";
						$db->prepare($updateSQL)->execute([$sn]);
					} elseif ($pay_mode == 'cash' && ($cr == 2 || $bill_to_acct == 'CREDIT' || $bill_to_acct == 'BILL')) {

						$updateSQL = "UPDATE patient_ap_services  SET invoice_status = 1, paystatus = 0, cr = 0 WHERE sn = ?";
						$success1 = $db->prepare($updateSQL)->execute([$sn]);

						if ($success1) {
							$deleteLedger = "DELETE FROM chart_ledger WHERE sale_sn = ?";
							$db->prepare($deleteLedger)->execute([$sn]);

							$error_status = 2;
							$error_msg = 'Reversed Successful';
						} else {
							throw new Exception('Failed to update patient service');
						}
					} else {
						// Wallet / non-cash reversal logic
						include_once("../admin/reverse.php");
						// ✅ Only proceed if reversal entries were inserted
						///if ($save_done) {
						if ($status == "success") {   // all reversed
							$setdatetime = date("Y-m-d H:i:s");
							$updateSQL = "UPDATE patient_ap_services SET invoice_status = 3, paystatus = 3, transact_date = ?, pay_mode = 'reverse' WHERE sn = ?";
							$db->prepare($updateSQL)->execute([$setdatetime, $sn]);

							// Optional: clear lab/radiology credit flags
							if ($serv_group == 'Laboratory' || $serv_group == 'Radiology') {
								$updateSQL3 = "UPDATE lab_manage 
                               SET result_on_credit = 0, date_time_pay = null
                               WHERE labrequest_no = :labrequest_no";
								$stmt = $db->prepare($updateSQL3);
								$stmt->bindParam(':labrequest_no', $drug_sn, PDO::PARAM_STR);
								$stmt->execute();
							}

							$error_status = 2;
							$error_msg = $wallet_payee !== ''
								? 'Reversed successful to owner/s Account!'
								: 'Reversed Successful';
						} else {
							$error_status = 1;
							$error_msg = 'Unable To Reverse Successfully';
						}
					}

					$db->commit();
				} catch (PDOException $e) {
					$db->rollBack();
					$error_status = 1;
					$error_msg = 'Transaction Failed: ' . $e->getMessage();
				} catch (Exception $e) {
					$db->rollBack();
					$error_status = 1;
					$error_msg = 'Error: ' . $e->getMessage();
				}
			}
		} else {
			$error_msg = 'Service Not Found!';
			$error_status = 1;
		}
	}
} else {
	// Default POST fallback
	$start               = isset($_POST["start"]) ? $_POST["start"] : '';
	$end                 = isset($_POST["end"]) ? $_POST["end"] : '';
	$Transaction_Report  = isset($_POST["Transaction_Report"]) ? $_POST["Transaction_Report"] : '';
	$get_prev            = isset($_POST["getvalue"]) ? $_POST["getvalue"] : '';
}


if (isset($_POST['execute_action'])) {
	if ($writeoff == "1") {
		if (!empty($_REQUEST['inv'])) {
			try {
				$db->beginTransaction();

				$action_to_take = $_POST['action_to_take'];
				$payment_remarks = $_POST['payment_remarks'];
				$emr = $_POST['emr'];
				$item_list_raw = $_REQUEST['inv'];

				$batch_name_array = is_array($item_list_raw)
					? $item_list_raw
					: array_map('trim', explode(",", $item_list_raw));

				$all_items = [];

				foreach ($batch_name_array as $inv_id) {
					$break = explode("__", $inv_id);

					if (isset($break[2], $break[1])) {
						$item_services = trim($break[2]);
						$pay = trim($break[1]);

						if ($item_services !== '' && $pay !== '') {
							$all_items[] = "{$item_services}({$pay})";
						}
					}
				}

				$all_items_here = implode(", ", $all_items);


				$total_amt = 0;
				$old_payment_rmk = null;

				if (strlen(trim($payment_remarks)) >= 50) {
					for ($i = 0; $i < count($item_list_raw); $i++) {
						$values = $item_list_raw[$i];
						$break = explode("__", $values);
						$sn = $break[0];
						$pay = $break[1];
						$item_services = $break[2];
						$total_amt = $total_amt + $pay;

						if ($action_to_take == 'writeoff') {
							$EmployeeCode = $_SESSION['EmployeeCode'];
							$staff = $_SESSION['fullname'];
							$transact_date = date("Y-m-d H:i:s");

							// Fetch existing record with paystatus = 0 to avoid unnecessary updates
							$stmt2 = $db->prepare("SELECT item_services, pay, transact_date, payment_remarks FROM patient_ap_services WHERE sn = :sn AND paystatus = 0");
							$stmt2->execute(['sn' => $sn]);

							if ($stmt2->rowCount() > 0) {
								$rowx = $stmt2->fetch(PDO::FETCH_ASSOC);
								$desc = '<b style="color:red;">WRITEOFF BILLED: </b>';

								$updateSQL = "UPDATE patient_ap_services 
											  SET paystatus = 1, 
												  transact_date = :transact_date, 
												  pay_mode = 'writeoff', 
												  payment_remarks = :payment_remarks, 
												  who_process_paystatus = :EmployeeCode 
											  WHERE sn = :sn AND paystatus = 0";
								$stmtUpdate = $db->prepare($updateSQL);
								$stmtUpdate->execute([
									'transact_date' => $transact_date,
									'payment_remarks' => $payment_remarks,
									'EmployeeCode' => $EmployeeCode,
									'sn' => $sn
								]);

								$sv = 1;
							}
						} elseif ($action_to_take == 'cr_to_invoice') {
							$updateSQL = "UPDATE patient_ap_services 
										  SET cr = 0, payment_remarks = :payment_remarks 
										  WHERE sn = :sn 
											AND cr = 1 
											AND drug_status = 0 
											AND paystatus = 0 
											AND remarks != 'auto_deduct'";
							$stmt = $db->prepare($updateSQL);
							$stmt->execute([
								'payment_remarks' => $payment_remarks,
								'sn' => $sn
							]);
							$sv = 1;
							$desc = '<b>CREDIT TO INVOICE: </b>';
						} elseif ($action_to_take == 'invoice_to_credit') {
							$updateSQL = "UPDATE patient_ap_services 
										  SET cr = 1, payment_remarks = :payment_remarks 
										  WHERE sn = :sn 
											AND cr = 0 
											AND paystatus = 0";
							$stmt = $db->prepare($updateSQL);
							$stmt->execute([
								'payment_remarks' => $payment_remarks,
								'sn' => $sn
							]);
							$sv = 1;
							$desc = '<b>INVOICE TO CREDIT: </b><br>';
						}
					}

					$payment_remarks = $desc . $payment_remarks;

					$stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no,service_list,total_amount, remark, staff_name) 
				VALUES (:hospital_no,:list_desc,:cash, :remark, :staff_name)");

					$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
					$stmt->bindParam(':list_desc', $all_items_here, PDO::PARAM_STR);
					$stmt->bindParam(':cash', $total_amt, PDO::PARAM_STR);
					$stmt->bindParam(':remark', $payment_remarks, PDO::PARAM_STR);
					$stmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
					///$stmt->bindParam(':bill_to_who', $_POST['auth_staff'], PDO::PARAM_STR);
					$stmt->execute();

					$db->commit();
				} else { ?>
					<div class="alert alert-danger">
						<h2> Oops! Invalid remarks/comments. Please enter a remark for the action you want to perform. Remarks must be at least 50 characters. </h2>
					</div>
<?php }
			} catch (PDOException $e) {
				$db->rollBack();
				echo "Error: " . $e->getMessage();
			}
		} else {
			echo "<div class='alert alert-danger'>
			<p> Oops ! Invalid Selection </p>
		</div>";
		}
	} else {
		echo "<div class='alert alert-danger'>
				<p> Oops ! Invalid Write off rights </p>
			</div>";
	}
}

?>

<div class="ibox-title">
	<h5>Welcome to <u><?php echo $patient_name; ?></u> Account!</h5>
</div>
<div class="ibox-content">
	<div class="row">


		<?php
		// Option A: require both rights to be > 0

		// Option B: require reversal right
		if ($reversal === 0) {
			echo "<div class='alert alert-danger'>You do not have permission to perform reversals.</div>";
		}

		// Option C: require write-off right
		if ($writeoff === 0) {
			echo "<div class='alert alert-danger'>You do not have permission to write off invoices.</div>";
		}

		?>


		<form action="pacct.php?emr=<?= $emr; ?>&rof" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">
			<div class="col-md-4">


				<div class="ibox float-e-margins">
					<label for="reg_input_no" class="">Set Dates Range & Click Apply button </label><br>
					<div class="form_sep" id="">
						<?php
						$start = isset($_POST['start']) && $_POST['start'] != '' ? $_POST['start'] : ($start != '' ? $start : date("Y-m-d"));
						$end = isset($_POST['end']) && $_POST['end'] != '' ? $_POST['end'] : ($end != '' ? $end : date("Y-m-d"));
						?>

						<div class="input-daterange input-group">
							<input type="date" class="form-control" name="start" value="<?php echo htmlspecialchars($start); ?>" />
							<span class="input-group-addon">to</span>
							<input type="date" class="form-control" name="end" value="<?php echo htmlspecialchars($end); ?>" />
						</div>


					</div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="ibox float-e-margins">
					<?php
					$selectedPaymethod = isset($_POST['paymethod']) ? $_POST['paymethod'] : (isset($Transaction_Report) ? $Transaction_Report : '');
					?>

					<div class="form_sep">
						<label class="req">Select One Option</label>
						<select name="paymethod" id="" class="input-sm form-control" required>
							<option value='' <?php echo ($selectedPaymethod == '') ? 'selected' : ''; ?>>Select...</option>
							<option value="credit" <?php echo ($selectedPaymethod == 'credit') ? 'selected' : ''; ?>>Credits</option>
							<option value="writeoff" <?php echo ($selectedPaymethod == 'writeoff') ? 'selected' : ''; ?>>Items written off</option>
							<option value="Gen_inv" <?php echo ($selectedPaymethod == 'Gen_inv') ? 'selected' : ''; ?>>Invoice prepared for payment</option>
							<option value="Invoice Pending" <?php echo ($selectedPaymethod == 'Invoice Pending') ? 'selected' : ''; ?>>Items Not Invoiced</option>
							<option value="Paid" <?php echo ($selectedPaymethod == 'Paid') ? 'selected' : ''; ?>>Paid Items</option>
						</select>
					</div>


				</div>
			</div>

			<div class="col-md-1">
				<div class="ibox float-e-margins">
					<div class="form_sep">
						<label for="reg_input_no" class="">.</label><br>
						<button class="btn btn-primary btn btn-sm" type="submit" name="apply_rof"> <i class="fa fa-check"></i> Apply</button>
					</div>
				</div>
			</div>

			<div class="col-md-3">
				<div class="ibox float-e-margins">
					<div class="form_sep">
						<label for="reg_input_no" class="">.</label><br>
						<a href="pacct.php?emr=<?php echo $emr; ?>&rof" class="btn btn-default btn btn-sm"> <i class="fa fa-times"></i> &nbsp;Refresh</a>&nbsp;:&nbsp;
						<a href="pacct.php?emr=<?php echo $emr; ?>" class="btn btn-danger btn btn-sm"> <i class="fa fa-times"></i> &nbsp;Close</a>
					</div>
				</div>
			</div>

			<div class="col-md-12">
				<?php
				$n = 1;
				$s = 1;
				$claim_set = 0;
				$total_claim = 0;
				$invoice_set = 0;

				if (isset($_POST['apply_rof']) or isset($_POST['execute_action']) or $selectedPaymethod != '') {

					$paymethod = $selectedPaymethod;



					if ($paymethod == 'credit') {
						$search = " hospital_no='$emr' and paystatus=0 and cr=1 and date(date_entry) between '$start' and '$end' ";
						$title = "<strong>Credits Transactions between " . date("d M, Y", strtotime($start)) . ' - ' . date("d M, Y", strtotime($end)) . '</strong><br><br>';
					} elseif ($paymethod == 'Gen_inv') {
						$search = " hospital_no='$emr' and paystatus=0 and invoice_status=1 and date(date_entry) between '$start' and '$end' ";
						$title = "<strong>Generated Invoices between " . date("d M, Y", strtotime($start)) . ' - ' . date("d M, Y", strtotime($end)) . '</strong><br><br>';
					} elseif ($paymethod == 'Invoice Pending') {
						$search = " hospital_no='$emr' and paystatus=0 and invoice_status=0 and date(date_entry) between '$start' and '$end' ";
						$title = "<strong>Pending Invoices between " . date("d M, Y", strtotime($start)) . ' - ' . date("d M, Y", strtotime($end)) . '</strong><br><br>';
					} elseif ($paymethod == 'Paid') {
						$search = " hospital_no='$emr' and paystatus=1 and pay>0 and pay_mode='cash' and date(transact_date) between '$start' and '$end' ";
						$title = "<strong>Pending Invoices between " . date("d M, Y", strtotime($start)) . ' - ' . date("d M, Y", strtotime($end)) . '</strong><br><br>';
					} elseif ($paymethod == 'writeoff') {
						$title = 'Undo Write-Off';
						$search = " hospital_no='$emr' and paystatus=1 and pay_mode='writeoff' ";
					}

				?>

					<?php
					$stmt = $db->query("SELECT * FROM patient_ap_services WHERE $search order by invoice_status,paystatus,date_entry,cat_type");
					?>

					<?php if ($stmt->rowCount() > 0) { ?>
						<?php if (in_array($_POST['paymethod'], ['Gen_inv', 'Invoice Pending'])): ?>
							<h3 style="color:red;">Write Off Billing or Change Invoice Status: Check the Item(s), Select Option, and Click the Execute Button to Proceed</h3> <?php endif; ?>
						<p><?php echo $title; ?></p>
						<table class="table table-striped" width="100%">
							<tr>
								<th width="3%">.</th>
								<th width="28%">Item</th>
								<th width="6%">Price</th>
								<th width="2%">Qty</th>
								<th width="6%">Amount</th>
								<th width="10%">.</th>
								<th width="12%">Captured/TranscDate</th>
								<th width="21%">.</th>
								<th width="21%">.</th>
							</tr>
							<?php
							$total_inv = 0;
							$total_cr = 0;
							$total_dsc = 0;
							$total_chr = 0;
							$total_riteoff = 0;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$pay = $row['pay'];
								$encode_sn = base64_encode(base64_encode($row['sn']));
							?>

								<tr>
									<td width="3%">
										<input type="checkbox"
											value="<?php echo $row['sn'] . '__' . $pay . '__' . $row['item_services']; ?>"
											name="inv[]"
											id="add_m_<?php echo (int)$n; ?>"
											<?php
											if (isset($_POST['inv']) && in_array($row['sn'] . '__' . $pay . '__' . $row['item_services'], $_POST['inv'])) {
												echo 'checked';
											}
											?>
											onclick="UpdateCost()" />
									</td>
									<td width="28%"><?php echo $s . ' - ' . $row['item_services']; // . '===' . $row['sn']; 
													?></td>
									<td width="6%" align=""><?php echo number_format($row['hosp_price']); ?></td>
									<td width="2%"><?php echo $row['qty']; ?></td>
									<td width="6%" align=""><?php echo number_format($pay); ?></td>

									<td width="10%"><?php
													if ($row['cr'] == 1 and $row['paystatus'] == '0') { ?>
											<strong style="color:#F00">Credit</strong>
										<?php
													} elseif ($row['pay_mode'] == 'cash' && $row['invoice_status'] == 0) {
														echo 'Invoice';
													} elseif ($row['wallet_debt_bill_to_acct'] == 'BILL' && $row['paystatus'] == 1) {
														echo '<b>Bill to MD</b>';
													} elseif ($row['pay_mode'] == 'cash' && $row['paystatus'] == 1) {
														echo '<b>Paid</b>';
													} else {
														echo $row['pay_mode'];
													}
										?>
									</td>
									<td width="15%">
										<?php echo  date('d M,Y', strtotime($row['date_entry'])) . '<br>' . date('d M,Y', strtotime($row['transact_date'])); ?>
									</td>
									<td width="18%">
										<input type="button" name="edit" value="Notes" data-target="#modal" id="<?php echo $row["sn"] . '__' . $row['item_services']; ?>" class="btn btn-success btn-xs view_notes" />
									<td>


										<?php if (!in_array($_POST['paymethod'], ['Gen_inv', 'Invoice Pending'])): ?>

											<?php if ($paymethod == 'Paid' && $row['wallet_debt_bill_to_acct'] == 'BILL') {
											?>
												<a href="pacct.php?emr=<?php echo $emr . '&rof&reverse=' . $encode_sn . '/Paid/' . $start . '/' . $end; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to REVERSE? If you Reversed, PAYMENT IS NOT POSSIBLE for this ITEM AGAIN!');">Reverse Amount Billed to MD </a>

											<?php } elseif ($paymethod == 'Paid') { ?>
												<div class="btn-group">
													<button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle">
														Action <span class="caret"></span>
													</button>
													<ul class="dropdown-menu">
														<li><a href="pacct.php?emr=<?php echo $emr . '&rof&reverse=' . $encode_sn . '/Paid/' . $start . '/' . $end; ?>" onclick="return confirm('Are you sure you want to REVERSE? If you Reversed, PAYMENT IS NOT POSSIBLE for this ITEM AGAIN!');">Reverse Amount Paid to Patient Account </a></li>
														<li><a href="pacct.php?emr=<?php echo $emr . '&rof&delete=' . $encode_sn; ?>" onclick="return confirm('Are you sure you want to REVERSE and DELETE Transaction from the Ledger Table?');">Delete Wrong Post</a></li>
													</ul>
												</div>
											<?php } elseif ($paymethod == 'writeoff') {		?>
												<?php if ($writeoff == 1) {	?>
													<a href="pacct.php?emr=<?php echo $emr . '&rof&rrof=' . $encode_sn . '/' . $paymethod . '/' . $start . '/' . $end; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to REVERSE Write Off?')">Reverse Writeoff</a>
												<?php }
												?>
											<?php } ?>

										<?php endif; ?>
									</td>
								</tr>
							<?php $n += 1;
								$s += 1;
							} ?>
						</table>
					<?php  } else { ?>
						<div class="alert alert-warning"> No Records to show </div>
					<?php } ?>


					<?php if (isset($_GET['sel'])) { ?>
						<br><strong style="color:#F00">Select item(s) you wish to write off ... </strong>
					<?php } ?>

					<hr>
					<table width="50%">
						<tr>
							<td width="20%" align="right">
								<div align="right">
									<strong>Total Amount: </strong>
								</div>
							</td>
							<td width="10%" align="right">
								<div align="right">
									<input type="text" name="totalcost" id="totalcost" class=" input-sm form-control"
										readonly="readonly" style="font-size:20px; outline:none; background:none; background-color:transparent; border: 0px solid;">
								</div>
							</td>
							<td align="left" width="16%">
								<div align="left">
							</td>
						</tr>
					</table>
					<?php if ($stmt->rowCount() > 0) { ?>
						<?php if ($paymethod != 'writeoff' && $paymethod != 'Paid') { ?>
							<label class="req">Select One Option & Click Execute</label>
							<select name="action_to_take" id="action_to_take" class="input-sm form-control">
								<option value='' <?php if (isset($_POST['action_to_take']) && $_POST['action_to_take'] == '') echo 'selected'; ?>>Select...</option>
								<option value='writeoff' <?php if (isset($_POST['action_to_take']) && $_POST['action_to_take'] == 'writeoff') echo 'selected'; ?>>WriteOff</option>
								<option value='cr_to_invoice' <?php if (isset($_POST['action_to_take']) && $_POST['action_to_take'] == 'cr_to_invoice') echo 'selected'; ?>>Change Credit to Invoice</option>
								<option value='invoice_to_credit' <?php if (isset($_POST['action_to_take']) && $_POST['action_to_take'] == 'invoice_to_credit') echo 'selected'; ?>>Change Invoice to Credit</option>
							</select>

							<label>Remarks (Required) <b style="color: red;">Remarks must be at least 50 characters.</b></label>
							<textarea class="input-sm form-control" cols="5" rows="2" name="payment_remarks" id="payment_remarks" maxlength="100"><?php echo isset($_POST['payment_remarks']) ? htmlspecialchars($_POST['payment_remarks']) : ''; ?></textarea>
			</div>

			<button class="btn btn-danger btn-lg" type="submit" name="execute_action" id="execute_action">Execute</button>
		<?php } ?>

	</div>
	<input type="hidden" value="pay" name="pay">
	<input type="hidden" value="<?php echo $emr; ?>" name="emr">
	</form>

<?php } ?>
<?php } else {

					///$hospital_no = isset($_POST['hospital_no']) ? trim($_POST['hospital_no']) : '';
					$t = 0;
					if ($emr != '') {
						$stmt = $db->prepare("SELECT p.hospital_no, p.service_list, p.total_amount, p.remark, p.date_time, p.staff_name, a.fullname 
											  FROM patients_remarks_tbl AS p 
											  LEFT JOIN admin_users AS a ON a.EmployeeCode = p.bill_to_who
											  WHERE p.hospital_no = :hospital_no
											  ORDER BY p.date_time DESC");
						$stmt->execute([':hospital_no' => $emr]);
					}
?>

	<?php if (!empty($stmt) && $stmt->rowCount() > 0) { ?>

		<h2 style="color:brown; ">Patient Transactions log</h2>
		<table class="table table-striped table-bordered table-hover dataTables-example">
			<thead>
				<tr>
					<th>#</th>
					<th>Service List</th>
					<th>Total Amount</th>
					<th>Remark</th>
					<th>Staff Name</th>
					<th>Bill To (If Any)</th>
					<th>Date/Time</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$t = $t + $row['total_amount']; ?>
					<tr>
						<td><?php echo $n++; ?></td>
						<td><?php echo ($row['service_list']); ?></td>
						<td><?php echo htmlspecialchars(number_format($row['total_amount'], 2)); ?></td>
						<td><?php echo $row['remark']; ?></td>
						<td><?php echo htmlspecialchars($row['staff_name']); ?></td>
						<td><?php echo $row['fullname'] != '' ? htmlspecialchars($row['fullname']) : '<span style="color: gray;">N/A</span>'; ?></td>
						<td><?php echo date('d M Y h:i A', strtotime($row['date_time'])); ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<h3>Total Amount: <?= number_format($t, 2); ?></h3>
	<?php } else { ?>
		<div class="alert alert-warning">No records found for Hospital No: <strong><?php echo htmlspecialchars($hospital_no); ?></strong></div>
<?php }
				} ?>
</div>

<?php

$inv_count = $n; ?>