<?php

if ($sp_remarks != '') {
	$remarks = 'auto_deduct';
	$tag = 'del';
} else {
	$sp_remarks = $remarks;
	$tag = '';
}

$process_claim = 0;
$empty = '';

///$pay_mode=$pp[0];$claim_amt=$pp[1];$access=$pp[2];$amt_paying=$pp[3];$appt_no=$pp[4];
$setdate = date('Y-m-d H:00:00');
$setdate2 = date('Y-m-d');
// Prepare the SQL statement with placeholders
$sql = "SELECT * FROM patient_ap_services 
        WHERE hospital_no = :hospital_no 
        AND app_no = :app_no 
        AND cat_type = :cat_type 
        AND drug_sn = :drug_sn 
        AND qty = :qty 
        AND remarks = :remarks 
        AND prepared_by = :prepared_by 
        AND paystatus = '0' 
        AND date_entry = :setdate";

// Prepare the statement
$stmt = $db->prepare($sql);

// Bind parameters
$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmt->bindParam(':app_no', $appt_no, PDO::PARAM_STR);
$stmt->bindParam(':cat_type', $price_table, PDO::PARAM_STR);
$stmt->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
$stmt->bindParam(':qty', $qty, PDO::PARAM_STR);
$stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
$stmt->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR); // Assuming $setdate is properly formatted as a string

// Execute the query
$stmt->execute();

// Check if any rows were returned
if ($stmt->rowCount() == 0) {


	$sql = "INSERT INTO patient_ap_services 
									(app_no, hospital_no, access, serv_group, cat_type, dept_id,dept_dispensory_id, drug_sn, item_services, tag, hosp_price, claim_amt, interest, qty, remarks, drug_status, invoice_status, invoice_no, invoice_date, invoice_by, prepared_by,created_by, date_entry, transact_date, pay, pay_mode, paystatus, process_claim, cr) 
									VALUES 
									(:app_no, :hospital_no, :access, :serv_group, :cat_type, :dept_id,:dept_dispensory_id, :drug_sn, :item_services, :tag, :hosp_price, :claim_amt, :interest, :qty, :remarks, :drug_status, :invoice_status, :invoice_no, :invoice_date, :invoice_by, :prepared_by,:created_by, :date_entry, :transact_date, :pay, :pay_mode, :paystatus, :process_claim, :cr)";

	$stmt = $db->prepare($sql);

	$stmt->bindParam(':app_no', $appt_no, PDO::PARAM_STR);
	$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
	$stmt->bindParam(':access', $access, PDO::PARAM_STR);
	$stmt->bindParam(':serv_group', $category, PDO::PARAM_STR);
	$stmt->bindParam(':cat_type', $price_table, PDO::PARAM_STR);
	$stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
	$stmt->bindParam(':dept_dispensory_id', $dept_dispensory_id, PDO::PARAM_STR);
	$stmt->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
	$stmt->bindParam(':item_services', $item_service, PDO::PARAM_STR);
	$stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
	$stmt->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR); // Assuming hosp_price is a string, adjust if it's numeric
	$stmt->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR); // Assuming claim_amt is a string, adjust if it's numeric
	$stmt->bindParam(':interest', $ccop_int_charge, PDO::PARAM_STR); // Assuming ccop_int_charge is a string, adjust if it's numeric
	$stmt->bindParam(':qty', $qty, PDO::PARAM_STR); // Assuming qty is a string, adjust if it's numeric
	$stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
	$stmt->bindParam(':drug_status', $drug_status, PDO::PARAM_STR);
	$stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_STR);
	$stmt->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
	$stmt->bindParam(':invoice_date', $invoicedate, PDO::PARAM_STR);
	$stmt->bindParam(':invoice_by', $invoice_by, PDO::PARAM_STR);
	$stmt->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
	$stmt->bindParam(':created_by', $_SESSION['id'], PDO::PARAM_STR);
	$stmt->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
	$stmt->bindValue(':transact_date', $empty, PDO::PARAM_STR); // Assuming ' ' is a placeholder for transact_date
	$stmt->bindParam(':pay', $amt_paying, PDO::PARAM_STR); // Assuming amt_paying is a string, adjust if it's numeric
	$stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
	$stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
	$stmt->bindValue(':process_claim', $process_claim, PDO::PARAM_STR); // Assuming '0' is a placeholder for process_claim
	$stmt->bindParam(':cr', $cr, PDO::PARAM_STR);

	$stmt->execute();

	$showid = $db->lastInsertId();
}
