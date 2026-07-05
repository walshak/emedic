<?php
// Ensure database connection exists
if (!isset($db)) {
	die("Database connection not established.");
}

// Validate and sanitize hospital number
$hos_no = isset($_POST['hosp_no']) ? trim($_POST['hosp_no']) : null;
if (empty($hos_no)) {
	die("<strong>Invalid Hospital Number</strong>");
}

// Always clear existing records for this hospital number
$deleteStmt = $db->prepare("DELETE FROM saleprint WHERE hos_no = :hos_no");
$deleteStmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
$deleteStmt->execute();

// Retrieve selected invoices
$pro_inv = isset($_REQUEST['inv']) ? $_REQUEST['inv'] : [];

if (!empty($pro_inv) && isset($_POST['print_invoice'])) {

	// Prepare reusable statements (faster)
	$fetchStmt = $db->prepare("SELECT item_services, pay, claim_amt, qty FROM patient_ap_services WHERE sn = ? LIMIT 1");
	$insertStmt = $db->prepare("
        INSERT INTO saleprint (hos_no, sale_no, item, pay, claim, qty)
        VALUES (:hos_no, :sn, :item, :pay, :claim, :qty)
    ");

	foreach ($pro_inv as $inv_id) {
		// Extract serial number
		$parts = explode("__", $inv_id);
		$sn = isset($parts[0]) ? trim($parts[0]) : null;
		if (empty($sn)) continue;

		// Fetch service data
		$fetchStmt->execute([$sn]);
		$row = $fetchStmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) continue; // Skip if not found

		// Insert record
		$insertStmt->execute([
			':hos_no' => $hos_no,
			':sn'     => $sn,
			':item'   => $row['item_services'],
			':pay'    => $row['pay'],
			':claim'  => $row['claim_amt'],
			':qty'    => $row['qty']
		]);
	}

	// Redirect to invoice page
	header("Location: invoice.php?hosp_no=" . urlencode($hos_no));
	exit;
} else {
	// If no items selected
	echo '<strong>No Item Selected</strong><hr>';
	echo '<a href="index.php?presc&hos_no=' . htmlspecialchars($hos_no, ENT_QUOTES) . '" class="btn btn-danger btn-sm"><strong>Close</strong></a>';
}
