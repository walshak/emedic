<?php 

include("../Connections/Conn.php");

session_start();
	$setdate= date("Y-m-d");
	
	$qury_notice = "SELECT * FROM notices WHERE expired >= :setdate AND (notice_to = :fullname OR notice_to = :rights)";
	$stmtt_notice = $db->prepare($qury_notice);
	$stmtt_notice->bindParam(':setdate', $setdate);
	$stmtt_notice->bindParam(':fullname', $_SESSION['fullname']);
	$stmtt_notice->bindParam(':rights', $_SESSION['rights']);
	$stmtt_notice->execute();
	
	if ($stmtt_notice->rowCount() > 0) {
		while ($row = $stmtt_notice->fetch(PDO::FETCH_ASSOC)) {
			$sn = $row['sn'];
	
			$qury_notice_usage = "SELECT * FROM notices_usage WHERE notice_sn = :sn AND staffname = :fullname";
			$stmtt_notice_usage = $db->prepare($qury_notice_usage);
			$stmtt_notice_usage->bindParam(':sn', $sn);
			$stmtt_notice_usage->bindParam(':fullname', $_SESSION['fullname']);
			$stmtt_notice_usage->execute();
	
			if ($stmtt_notice_usage->rowCount() == 0) {
				$insertSQL = "INSERT INTO notices_usage (notice_sn, staffname, status, date_captured) VALUES (:sn, :fullname, :status, :setdate)";
				$stmt_insert = $db->prepare($insertSQL);
				$stmt_insert->bindParam(':sn', $sn);
				$stmt_insert->bindParam(':fullname', $_SESSION['fullname']);
				$stmt_insert->bindParam(':status', $status);
				$stmt_insert->bindParam(':setdate', $setdate);
				$status = '1';
				$stmt_insert->execute();
			} else {
				$updateSQL = "UPDATE notices_usage SET date_captured = :setdate WHERE notice_sn = :sn AND staffname = :fullname";
				$stmt_update = $db->prepare($updateSQL);
				$stmt_update->bindParam(':setdate', $setdate);
				$stmt_update->bindParam(':sn', $sn);
				$stmt_update->bindParam(':fullname', $_SESSION['fullname']);
				$stmt_update->execute();
			}
		}
	}
	

header("location:index.php")


?>