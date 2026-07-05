<?php session_start();
require_once('../Connections/Conn.php');

if (isset($_POST["delete_drug_chart"])) {

	$drug_chart_id = $_POST["delete_drug_chart"];

	try {
		// Start a transaction
		$db->beginTransaction();

		// Check if the drug chart exists
		$stmt1 = $db->prepare("SELECT sale_sn FROM drug_charts WHERE sn=:sn");
		$stmt1->bindParam(':sn', $drug_chart_id, PDO::PARAM_STR);
		$stmt1->execute();
		$count = $stmt1->rowCount();
		$result = $stmt1->fetch();

		if ($count > 0) {
			// Record found
			$sale_sn = $result['sale_sn'];

			// Check if the drug chart has any inventory
			$stmt2 = $db->prepare("SELECT sale_sn FROM drug_charts_inven WHERE drug_chart=:drug_chart");
			$stmt2->bindParam(':drug_chart', $drug_chart_id, PDO::PARAM_STR);
			$stmt2->execute();
			$count2 = $stmt2->rowCount();

			if ($count2 == 0) {
				// Delete the drug chart inventory
				$delete1 = $db->prepare("DELETE FROM drug_charts_inven WHERE drug_chart = :sale_sn");
				$delete1->bindParam(':sale_sn', $drug_chart_id, PDO::PARAM_STR);
				$delete1->execute();

				// Delete the drug chart
				$delete2 = $db->prepare("DELETE FROM drug_charts WHERE sn = :sale_sn");
				$delete2->bindParam(':sale_sn', $drug_chart_id, PDO::PARAM_STR);
				$delete2->execute();

				// Delete the drug chart timing
				$delete3 = $db->prepare("DELETE FROM drug_chart_timing WHERE drug_chart_id = :drug_chart_id");
				$delete3->bindParam(':drug_chart_id', $drug_chart_id, PDO::PARAM_STR);
				$delete3->execute();

				// Commit the transaction
				$db->commit();

				// Return a success message
				echo 'Drug chart deleted successfully.';
			} else {
				// Rollback the transaction
				$db->rollBack();

				// Return an error message
				echo 'Unable to delete. Drug chart has inventory.';
			}
		} else {
			// Rollback the transaction
			$db->rollBack();

			// Return an error message
			echo 'Drug chart not found.';
		}
	} catch (PDOException $e) {
		$db->rollBack();

		echo 'Error deleting drug chart: ' . $e->getMessage();
	}
}
