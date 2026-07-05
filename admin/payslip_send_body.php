<?php

$stmt = $db->query("SELECT username,fullname FROM admin_users WHERE EmployeeCode='$Ecode'");
if ($stmt->rowCount() > 0) {
	$rwc = $stmt->fetch(PDO::FETCH_ASSOC);
	$on_contact_username = $rwc['username'];
	$on_contact_name = $rwc['fullname'];

	$month = $_POST['month'];
	$year = $_POST['year'];
	$date_range = $_POST['date_range'];

	$basic_salary_table = $earning_table = $deduct_table = $netpay_table = "";

	$T_D = $T_E = 0;




	/// PREPARING PAYSLIP

	/////////////////// BASIC SALARY ///////////////////////
	$descriptn = '';
	$amount = '';
	$total_amount = '';
	$table_row = '';
	$stmt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range' and pay_head='B' and amount>0");
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$B = $row['amount'];

		$stmt = $db->query("SELECT sum(amount) as basic_sal FROM hrpayslip WHERE ECode='$Ecode' and pay_head='B'");
		$rwc = $stmt->fetch(PDO::FETCH_ASSOC);

		$descriptn = '<td style="border-bottom: 1px solid #ddd; padding:5px;">Basic Salary</td>';
		$amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($row['amount']) . '</td>';
		$total_amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($rwc['basic_sal']) . '</td>';
		$table_row .= '<tr>' . $descriptn . $amount . $total_amount . '</tr>';

		$basic_salary_table =
			'<table width="100%" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">
			<tr>
			<th width="60%">DESCRIPTION</th>
			<th width="20%">AMOUNT</th>
			<th width="20%">AMOUNT (TODATE)</th>
			</tr>'
			. $table_row .
			'</table><br>';
	}
	//////////////////// -----------------------------------------


	///// EARNINGS ---------------------------------

	$stmt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range' and pay_head='E' and amount>0");
	if ($stmt->rowCount() > 0) {

		$descriptn = '';
		$amount = '';
		$total_amount = '';
		$table_row = '';
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$T_E = $T_E + $row['amount'];
			$desc = $row['descriptn'];

			$stmtt = $db->query("SELECT sum(amount) as earn FROM hrpayslip WHERE ECode='$Ecode' and pay_head='E' and descriptn='$desc'");
			$rwc = $stmtt->fetch(PDO::FETCH_ASSOC);

			$descriptn = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . $row['descriptn'] . '</td>';
			$amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($row['amount']) . '</td>';
			$total_amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($rwc['earn']) . '</td>';
			$table_row .= '<tr>' . $descriptn . $amount . $total_amount . '</tr>';
		}

		//// FINISHED LOOPING /////
		$descriptn = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . '<strong>Total: </strong>' . '</td>';
		$amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($T_E) . '</td>';
		$total_amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;"></td>';
		$table_row .= '<tr>' . $descriptn . $amount . $total_amount . '</tr>';

		$earning_table =
			'<table width="100%" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">
			<tr>
			<th width="60%">DESCRIPTION</th>
			<th width="20%">AMOUNT</th>
			<th width="20%">AMOUNT (TODATE)</th>
			</tr>'
			. $table_row .
			'</table><br>';
	}

	/////////////////////// END OF EARNINGS 

	///////////////--------------- DEDUCTIONS

	$stmt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range' and pay_head='D' and amount>0");
	if ($stmt->rowCount() > 0) { ?>

			 <?php
				$descriptn = '';
				$amount = '';
				$total_amount = '';
				$table_row = '';
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$T_D = $T_D + $row['amount'];
					$desc = $row['descriptn'];

					$stmtt = $db->query("SELECT sum(amount) as deduct FROM hrpayslip WHERE ECode='$Ecode' and pay_head='D' and descriptn='$desc'");
					$rwc = $stmtt->fetch(PDO::FETCH_ASSOC);

					$descriptn = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . $row['descriptn'] . '</td>';
					$amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($row['amount']) . '</td>';
					$total_amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($rwc['deduct']) . '</td>';
					$table_row .= '<tr>' . $descriptn . $amount . $total_amount . '</tr>';
				}
			}

			////////////// END OF LOOOPPPPPPPINGGGGGGGGGGGG

			$descriptn = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . '<strong>Total: </strong>' . '</td>';
			$amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($T_D) . '</td>';
			$total_amount = '<td style="border-bottom: 1px solid #ddd; padding:5px;"></td>';
			$table_row .= '<tr>' . $descriptn . $amount . $total_amount . '</tr>';

			$deduct_table =
				'<table width="100%" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">
			<tr>
			<th width="60%">DESCRIPTION</th>
			<th width="20%">AMOUNT</th>
			<th width="20%">AMOUNT (TODATE)</th>
			</tr>'
				. $table_row .
				'</table><br>';

			////////// END OF DEDUCTIONS
			$descriptn = '';
			$amount = '';
			$total_amount = '';
			$table_row = '';
			$netpay = ($B + $T_E) - $T_D;

			$netpay_table =
				'<table width="100%" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">
			<tr>
			<td width="60%" style="border-bottom: 1px solid #ddd; padding:5px;"><strong>NET PAY</strong></td>
			<td width="20%" style="border-bottom: 1px solid #ddd; padding:5px;">' . number_format($netpay) . '</td>
			<td width="20%" style="border-bottom: 1px solid #ddd; padding:5px;"></td>
			</tr></table>';

			//// SEND TO MY MAIL


			$stmt_sn = $db->query("SELECT sn FROM mails ORDER BY sn desc limit 1");
			if ($stmt_sn->rowCount() > 0) {
				$row_sn = $stmt_sn->fetch(PDO::FETCH_ASSOC);
				$sn = 1 + $row_sn['sn'];
			} else {
				$sn = 1;
			}


			//$comb=$basic_salary_table . $earning_table . $deduct_table . $netpay_table;
			$comb = $basic_salary_table . $earning_table . $deduct_table . $netpay_table;

			$pay_advice = "Pay Advice for " . $month . ', ' . $year;
			//$setdate=date("Y-m-d");
			$setdatetime = date("Y-m-d H:i:s");
			$send = "send";
			$stmt2 = $db->prepare("INSERT INTO mails(sn, on_contact_name, on_contact_username, from_name, from_username, msg, subject, mail_status, mail_date) 
				VALUES (:sn, :on_contact_name, :on_contact_username, :from_name, :from_username, :msg, :subject, :mail_status, :mail_date)");

			$stmt2->bindParam(':sn', $sn);
			$stmt2->bindParam(':on_contact_name', $on_contact_name);
			$stmt2->bindParam(':on_contact_username', $on_contact_username);
			$stmt2->bindParam(':from_name', $_SESSION['fullname']);
			$stmt2->bindParam(':from_username', $_SESSION['username']);
			$stmt2->bindParam(':msg', $comb);
			$stmt2->bindParam(':subject', $pay_advice);
			$stmt2->bindParam(':mail_status', $send);
			$stmt2->bindParam(':mail_date', $setdatetime);

			$stmt2->execute();
			$done = 1;
		} else {
			$title = "Error: You can not send payslip to user because loging credentails ";
		}
