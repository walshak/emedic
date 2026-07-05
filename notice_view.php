<?php 
include('inc/top_header.php');
include('inc/header.php');  
include('Connections/Conn.php');

$setdate = date("Y-m-d");

try {
    $qury_notice = "SELECT * FROM notices WHERE expired >= :setdate AND (notice_to = :fullname OR notice_to = :rights)";
    $stmt_notice = $db->prepare($qury_notice);
    $stmt_notice->execute([
        ':setdate' => $setdate,
        ':fullname' => $_SESSION['fullname'],
        ':rights' => $_SESSION['rights']
    ]);

    if ($stmt_notice->rowCount() > 0) {
        while ($row = $stmt_notice->fetch()) {
            $sn = $row['sn'];

            $qury_notice_usage = "SELECT * FROM notices_usage WHERE notice_sn = :sn AND staffname = :fullname";
            $stmt_notice_usage = $db->prepare($qury_notice_usage);
            $stmt_notice_usage->execute([
                ':sn' => $sn,
                ':fullname' => $_SESSION['fullname']
            ]);

            if ($stmt_notice_usage->rowCount() == 0) {
                $insertSQL = "INSERT INTO notices_usage (notice_sn, staffname, status, date_captured) VALUES (:sn, :fullname, :status, :setdate)";
                $stmt_insert = $db->prepare($insertSQL);
                $stmt_insert->execute([
                    ':sn' => $sn,
                    ':fullname' => $_SESSION['fullname'],
                    ':status' => '1',
                    ':setdate' => $setdate
                ]);
            } else {
                $updateSQL = "UPDATE notices_usage SET date_captured = :setdate WHERE notice_sn = :sn AND staffname = :fullname";
                $stmt_update = $db->prepare($updateSQL);
                $stmt_update->execute([
                    ':setdate' => $setdate,
                    ':sn' => $sn,
                    ':fullname' => $_SESSION['fullname']
                ]);
            }
        }
    }

header("location:dashboard.php")

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}




?>