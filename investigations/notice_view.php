<?php 

include("../Connections/Conn.php");

session_start();

$setdate = date("Y-m-d");

$qury_notice = "SELECT * FROM notices WHERE expired >= :setdate AND (notice_to = :fullname OR notice_to = :rights)";
$stmt = $db->prepare($qury_notice);
$stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR);
$stmt->bindParam(':fullname', $_SESSION['fullname'], PDO::PARAM_STR);
$stmt->bindParam(':rights', $_SESSION['rights'], PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sn = $row['sn'];

        $qury_notice_usage = "SELECT * FROM notices_usage WHERE notice_sn = :sn AND staffname = :fullname";
        $stmt_usage = $db->prepare($qury_notice_usage);
        $stmt_usage->bindParam(':sn', $sn, PDO::PARAM_STR);
        $stmt_usage->bindParam(':fullname', $_SESSION['fullname'], PDO::PARAM_STR);
        $stmt_usage->execute();

        if ($stmt_usage->rowCount() == 0) {
            $insertSQL = "INSERT INTO notices_usage (notice_sn, staffname, status, date_captured) VALUES (:sn, :fullname, :status, :setdate)";
            $stmt_insert = $db->prepare($insertSQL);
            $stmt_insert->bindParam(':sn', $sn, PDO::PARAM_STR);
            $stmt_insert->bindParam(':fullname', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':status', $status = '1', PDO::PARAM_STR);
            $stmt_insert->bindParam(':setdate', $setdate, PDO::PARAM_STR);
            $stmt_insert->execute();
        } else {
            $updateSQL = "UPDATE notices_usage SET date_captured = :setdate WHERE notice_sn = :sn AND staffname = :fullname";
            $stmt_update = $db->prepare($updateSQL);
            $stmt_update->bindParam(':setdate', $setdate, PDO::PARAM_STR);
            $stmt_update->bindParam(':sn', $sn, PDO::PARAM_STR);
            $stmt_update->bindParam(':fullname', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt_update->execute();
        }
    }
}
	

header("location:index.php")


?>