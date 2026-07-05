<?php

$table = "";
$CurDateHR = date("Y-m-d H:i:s");


$staff_log = $_SESSION['username'];

$stmt = $db->query("SELECT * FROM check_list WHERE task='auto_deduct' and staff_log='$staff_log'");
if ($stmt->rowCount() == 0) {

	$task = 'auto_deduct';
	$curDate = $CurDateHR;
	$status = 'active';
	$count = 0;
	$staffLog = $_SESSION['username'];

	$insertSQL = "INSERT INTO check_list (task, cur_date, next_update_date, status, count, staff_log) 
              VALUES (:task, :cur_date, :next_update_date, :status, :count, :staff_log)";

	$stmt = $db->prepare($insertSQL);
	$stmt->bindParam(':task', $task, PDO::PARAM_STR);
	$stmt->bindParam(':cur_date', $curDate, PDO::PARAM_STR);
	$stmt->bindParam(':next_update_date', $curDate, PDO::PARAM_STR);
	$stmt->bindParam(':status', $status, PDO::PARAM_STR);
	$stmt->bindParam(':count', $count, PDO::PARAM_INT);
	$stmt->bindParam(':staff_log', $staffLog, PDO::PARAM_STR);

	$stmt->execute();
}

$stmt = $db->query("SELECT * FROM check_list WHERE task='auto_deduct' and next_update_date<='$CurDateHR' and staff_log='$staff_log'");
if ($stmt->rowCount() > 0) {
	$stmt_main = $db->query("SELECT distinct hospital_no FROM admission WHERE adm_status='3'");
	if ($stmt_main->rowCount() > 0) {
		while ($row_p = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
			$hos_no = $row_p['hospital_no'];

			//// GENERATE DAILY INVOICE  /////

			$stmt_COM = $db->query("SELECT * FROM patient_ap_services WHERE hospital_no='$hos_no' and remarks='auto_deduct'");
			if ($stmt_COM->rowCount() > 0) {

				while ($row_rs = $stmt_COM->fetch(PDO::FETCH_ASSOC)) {
					$duration = 0;
					date_default_timezone_set('Africa/Lagos');
					$Current_date = date('Y-m-d H:i:s');
					$date1 = new DateTime($Current_date);
					$date2 = new DateTime($row_rs['date_entry']);
					$time = $date2->format('H:i:s');
					$next_due_date = date('Y-m-d') . ' ' . $time;

					$diff = $date2->diff($date1);
					$day = $diff->format('%a');
					if ($day > 0) {

						//	echo $hos_no . '=' .  $day;
						//	echo '<br>';
						//// drop the invoice ////

						$sale_sn = $row_rs['sn'];
						//	echo $item_services=$row_rs['item_services'];
						$duration = $day;
						$claim_amt = $row_rs['claim_amt'] / $row_rs['qty'];
						$Tclaim_amt = $claim_amt * $duration;

						$claim_interest = $row_rs['interest'] / $row_rs['qty'];
						$Tclaim_interest = $claim_interest * $duration;

						$amt_paying = $row_rs['pay'] / $row_rs['qty'];
						$Tamt_paying = $amt_paying * $duration;

						$invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));


						$app_no = $row_rs['app_no'];
						$hospital_no = $row_rs['hospital_no'];
						$drug_sn = $row_rs['drug_sn'];

						$empty = '';
						$zero = 0;
						$one = 1;
						$auto_deduct = 'auto_deduct';

						$stmtffff = $db->query("SELECT app_no FROM patient_ap_services WHERE app_no='$app_no' and hospital_no='$hospital_no' and drug_sn='$drug_sn' and date_entry='$next_due_date' and remarks='auto_deduct'");
						if ($stmtffff->rowCount() == 0) {

							// Assuming $db is your PDO database connection object

							$insertSQL = "INSERT INTO patient_ap_services (app_no, hospital_no, access, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, remarks, drug_status, invoice_status, invoice_no, prepared_by, date_entry, transact_date, pay, pay_mode, paystatus, process_claim, cr) 
              VALUES (:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, :interest, :qty, :remarks, :drug_status, :invoice_status, :invoice_no, :prepared_by, :date_entry, :transact_date, :pay, :pay_mode, :paystatus, :process_claim, :cr)";

							$stmt = $db->prepare($insertSQL);

							// Bind parameters
							$stmt->bindParam(':app_no', $row_rs['app_no'], PDO::PARAM_STR);
							$stmt->bindParam(':hospital_no', $row_rs['hospital_no'], PDO::PARAM_STR);
							$stmt->bindParam(':access', $row_rs['accesss'], PDO::PARAM_STR);
							$stmt->bindParam(':serv_group', $row_rs['serv_group'], PDO::PARAM_STR);
							$stmt->bindParam(':cat_type', $row_rs['cat_type'], PDO::PARAM_STR);
							$stmt->bindParam(':dept_id', $row_rs['dept_id'], PDO::PARAM_STR);
							$stmt->bindParam(':drug_sn', $row_rs['drug_sn'], PDO::PARAM_STR);
							$stmt->bindParam(':item_services', $row_rs['item_services'], PDO::PARAM_STR);
							$stmt->bindParam(':hosp_price', $row_rs['hosp_price'], PDO::PARAM_STR);
							$stmt->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
							$stmt->bindParam(':interest', $Tclaim_interest, PDO::PARAM_STR);
							$stmt->bindValue(':qty', $one, PDO::PARAM_STR);
							$stmt->bindValue(':remarks', $auto_deduct, PDO::PARAM_STR);
							$stmt->bindValue(':drug_status', $zero, PDO::PARAM_STR);
							$stmt->bindValue(':invoice_status', $zero, PDO::PARAM_STR);
							$stmt->bindValue(':invoice_no', $empty, PDO::PARAM_STR);
							$stmt->bindParam(':prepared_by', $row_rs['prepared_by'], PDO::PARAM_STR);
							$stmt->bindParam(':date_entry', $next_due_date, PDO::PARAM_STR);
							$stmt->bindValue(':transact_date', NULL, PDO::PARAM_STR);
							$stmt->bindParam(':pay', $amt_paying, PDO::PARAM_STR);
							$stmt->bindParam(':pay_mode', $row_rs['pay_mode'], PDO::PARAM_STR);
							$stmt->bindValue(':paystatus', $zero, PDO::PARAM_STR);
							$stmt->bindValue(':process_claim', $zero, PDO::PARAM_STR);
							$stmt->bindValue(':cr', $one, PDO::PARAM_STR);

							$stmt->execute();

							//$db->exec($insertSQL);

							if ($db->exec($insertSQL) == TRUE) {

								// Assuming $db is your PDO database connection object

								$updateSQL = "UPDATE patient_ap_services 
              SET paystatus = :paystatus, 
                  invoice_status = :invoice_status, 
                  invoice_no = :invoice_no, 
                  remarks = :remarks, 
                  claim_amt = :claim_amt, 
                  pay = :pay, 
                  qty = :qty 
              WHERE hospital_no = :hospital_no 
              AND sn = :sn";

								$stmt = $db->prepare($updateSQL);

								// Bind parameters
								$stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_STR); // Assuming $paystatus is defined elsewhere
								$stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR); // Assuming $invoice_status is defined elsewhere
								$stmt->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR); // Assuming $invoice_no is defined elsewhere
								$stmt->bindValue(':remarks', 'auto_deduct2', PDO::PARAM_STR); // Static value 'auto_deduct2'
								$stmt->bindParam(':claim_amt', $Tclaim_amt, PDO::PARAM_STR); // Assuming $Tclaim_amt is defined elsewhere
								$stmt->bindParam(':pay', $Tamt_paying, PDO::PARAM_STR); // Assuming $Tamt_paying is defined elsewhere
								$stmt->bindParam(':qty', $duration, PDO::PARAM_STR); // Assuming $duration is defined elsewhere
								$stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR); // Assuming $hos_no is defined elsewhere
								$stmt->bindParam(':sn', $sale_sn, PDO::PARAM_STR); // Assuming $sale_sn is defined elsewhere

								$stmt->execute();
							}
						}
					}
				}
			}
		}
	}




	/// add hour 
	$nxt_due_date = date("Y-m-d H:i:s", strtotime($CurDateHR . " +15 minutes"));
	$updateSQL2 = "UPDATE check_list SET next_update_date='$nxt_due_date',count=count+1 WHERE task='auto_deduct' and staff_log='$staff_log'";
	$db->query($updateSQL2);
}
