<?php


function save_invent($drug_sn, $sale_sn, $inven_desc, $batch, $qtyIN, $qtyOUT, $c_qty, $cust_patient_id, $enter_by, $captured_date, $transact_date, $invoice_status, $drug_status, $paystatus, $cr, $Sale_drug_qty, $claim_amt, $pay, $EX_or_IN, $where)
{
	include('../Connections/Conn.php');

	$updateSQL = $db->prepare("UPDATE patient_ap_services SET qty=:qty, claim_amt=:claim_amt, pay=:pay, dsp_by=:dsp_by, cr=:cr, invoice_status=:invoice_status, drug_status=:drug_status, paystatus=:paystatus WHERE sn=:sn");
	$updateSQL->bindParam(':qty', $Sale_drug_qty);
	$updateSQL->bindParam(':claim_amt', $claim_amt);
	$updateSQL->bindParam(':pay', $pay);
	$updateSQL->bindParam(':dsp_by', $_SESSION['fullname']);
	$updateSQL->bindParam(':cr', $cr);
	$updateSQL->bindParam(':invoice_status', $invoice_status);
	$updateSQL->bindParam(':drug_status', $drug_status);
	$updateSQL->bindParam(':paystatus', $paystatus);
	$updateSQL->bindParam(':sn', $sale_sn);
	$updateSQL->execute();

	$C_date = date("Y-m-d");

	$stmt2 = $db->prepare("SELECT * FROM stock_table_inven WHERE stock_sn=:stock_sn and sale_sn=:sale_sn and inven_desc=:inven_desc and qtyOUT=:qtyOUT and bal=:bal and cust_patient_id=:cust_patient_id and enter_by=:enter_by and date(captured_date)=:captured_date");
	$stmt2->bindParam(':stock_sn', $drug_sn);
	$stmt2->bindParam(':sale_sn', $sale_sn);
	$stmt2->bindParam(':inven_desc', $inven_desc);
	$stmt2->bindParam(':qtyOUT', $qtyOUT);
	$stmt2->bindParam(':bal', $c_qty);
	$stmt2->bindParam(':cust_patient_id', $cust_patient_id);
	$stmt2->bindParam(':enter_by', $enter_by);
	$stmt2->bindParam(':captured_date', $C_date);
	$stmt2->execute();

	if ($stmt2->rowCount() == 0) {
		$insertSQL = $db->prepare("INSERT INTO stock_table_inven(stock_sn, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, cust_patient_id, cust_patient_type, enter_by, captured_date) VALUES (:stock_sn, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :cust_patient_id, :cust_patient_type, :enter_by, :captured_date)");
		$insertSQL->bindParam(':stock_sn', $drug_sn);
		$insertSQL->bindParam(':sale_sn', $sale_sn);
		$insertSQL->bindParam(':inven_desc', $inven_desc);
		$insertSQL->bindParam(':batch', $batch);
		$insertSQL->bindParam(':qtyIN', $qtyIN);
		$insertSQL->bindParam(':qtyOUT', $qtyOUT);
		$insertSQL->bindParam(':bal', $c_qty);
		$insertSQL->bindParam(':cust_patient_id', $cust_patient_id);
		$insertSQL->bindParam(':cust_patient_type', $EX_or_IN);
		$insertSQL->bindParam(':enter_by', $_SESSION['fullname']);
		$insertSQL->bindParam(':captured_date', $captured_date);
		$insertSQL->execute();

		// update record

		/*
		$updateSQL = $db->prepare("UPDATE stock_table SET qty=:qty WHERE sn=:sn");
		$updateSQL->bindParam(':qty', $c_qty);
		$updateSQL->bindParam(':sn', $drug_sn);
		$updateSQL->execute();

		*/
	}
}
