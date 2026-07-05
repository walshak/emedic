<?php

if (isset($_POST['read_msg'])) {


	$setdate = date("Y-m-d");
	$fullname = $_SESSION['fullname'];
	$rights = $_SESSION['rights'];

	$stmtt = $db->query("SELECT * FROM notices WHERE expired>='$setdate' and (notice_to='$fullname' or notice_to='$rights')");
	if ($stmtt->rowCount() > 0) {
		while ($row = $stmtt->fetch(PDO::FETCH_ASSOC)) {
			$sn = $row['sn'];
			/////
			$stmt2 = $db->query("SELECT * FROM notices_usage WHERE notice_sn='$sn' and staffname='$fullname'");
			if ($stmt2->rowCount() == 0) {

				$insertSQL = "INSERT INTO notices_usage(notice_sn,staffname,status,date_captured) VALUES ('$sn','$fullname','1','$setdate')";
				$db->exec($insertSQL);
			} else {
				$updateSQL = "UPDATE notices_usage SET date_captured='$setdate' WHERE notice_sn='$sn' and staffname='$fullname'";
				$db->exec($updateSQL);
			}

			///looop end
		}
	}
}



$display_status = 0;
$setdate = date("Y-m-d");
$stmt = $db->query("SELECT * FROM notices WHERE expired>='$setdate'");
if ($stmt->rowCount() > 0) {
	///echo '8888';
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$notice_to = $row['notice_to'];
		$note = $row['note'] . '<br>' . '<strong>' . $row['from_who'] . '</strong>';
		$sn = $row['sn'];

		if ($row['from_who'] != $_SESSION['fullname']) {
			$_SESSION['rights'];
			if ($notice_to == $_SESSION['rights'] or $notice_to == $_SESSION['fullname']) {
				$fullname = $_SESSION['fullname'];
				$qury_notice2 = $db->query("SELECT * FROM notices_usage WHERE date_captured='$setdate' and notice_sn='$sn' and staffname='$fullname'");
				if ($qury_notice2->rowCount() == 0) {
					$my_note = $my_note . $note . '<hr>';
					$display_status = 1;
				}
			}
		}
		///looop end
	}
}
