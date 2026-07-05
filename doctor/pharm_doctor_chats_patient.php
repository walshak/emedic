<?php
session_start();

include_once('../Connections/Conn.php');
$response = array(
    'status' => 0,
    'message0' => '',
    'message1' => ''
);

$view_status_date = date('Y-m-d');
$view_status_date_param = $view_status_date . '%';

// Query for view_status = 0
$pharDocChatstmt0 = $db->prepare("SELECT * FROM `pharm_doctor_notes` 
                                    WHERE reciever = :reciever 
                                    AND view_status = 0 
                                    AND date_time LIKE :view_status_date
                                    ");
$pharDocChatstmt0->bindParam(':reciever', $_SESSION['fullname']);
$pharDocChatstmt0->bindParam(':view_status_date', $view_status_date_param);
$pharDocChatstmt0->execute();
$totalMessages0 = $pharDocChatstmt0->rowCount();

// Query for view_status = 1
$pharDocChatstmt1 = $db->prepare("SELECT * FROM `pharm_doctor_notes` 
                                    WHERE reciever = :reciever 
                                    AND view_status = 1 
                                    AND date_time LIKE :view_status_date
                                    ");
$pharDocChatstmt1->bindParam(':reciever', $_SESSION['fullname']);
$pharDocChatstmt1->bindParam(':view_status_date', $view_status_date_param);
$pharDocChatstmt1->execute();
$totalMessages1 = $pharDocChatstmt1->rowCount();

$response['message0'] = $totalMessages0;
$response['message1'] = $totalMessages1;
$response['status'] = '1';

echo json_encode($response);
exit;
