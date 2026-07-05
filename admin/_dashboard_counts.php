<?php
$acc_pay = null;
include("../Connections/Conn.php");

if (isset($_POST['bill_account_status'])) {

	$ECode_logged = $_POST['bill_account_status'];
	$t_pay = 0;

	try {
		// Prepare and execute the first query
		$stmt_d = $db->prepare("SELECT DISTINCT hospital_no FROM patient_ap_services WHERE acct_billed_staff = :ecode AND cr = '2' AND acct_billed_ack = 0");
		$stmt_d->bindParam(':ecode', $ECode_logged, PDO::PARAM_STR);
		$stmt_d->execute();

		if ($stmt_d->rowCount() > 0) {
			while ($rwxx = $stmt_d->fetch(PDO::FETCH_ASSOC)) {
				$hospital_no = $rwxx['hospital_no'];

				// Prepare and execute the second query
				$stmt = $db->prepare("SELECT pay FROM patient_ap_services WHERE acct_billed_staff = :ecode AND hospital_no = :hospital_no AND cr = '2' AND acct_billed_ack = 0");
				$stmt->bindParam(':ecode', $ECode_logged, PDO::PARAM_STR);
				$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$t_pay += $rwx['pay'];
					}
				}
			}
		}

		// Return the result in JSON format
		echo json_encode([
			'status' => 200,
			'bill_account_amount' => '<strong style="color:#F00; font-size:30px;">' . '&#8358;' . number_format($t_pay) . '</strong>'
		]);
	} catch (PDOException $e) {
		// Handle any errors
		echo json_encode([
			'status' => 500,
			'message' => 'Database error: ' . $e->getMessage()
		]);
	}
} else {
	// Handle the case where bill_account_status is not set
	echo json_encode([
		'status' => 400,
		'message' => 'bill_account_status not set'
	]);
}
