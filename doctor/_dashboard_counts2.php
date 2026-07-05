<?php
if(isset($_POST['get_dashboard_counts'])){
    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');
    header( 'Content-Type: application/json' );
    $date_today = date('Y-m-d');
       

    //////////////// DRUGS
        $stmt = $db->prepare("SELECT COUNT(sn) total  FROM patient_ap_services  WHERE created_by = ? AND cat_type = 'Pharmacy' AND date_entry = ?  ");
        $stmt->execute(array($_SESSION['id'], $date_today));
        $total_drugs_requested_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_drugs_requested = $total_drugs_requested_row['total'];

        $stmt = $db->prepare("SELECT COUNT(sn) total  FROM patient_ap_services   WHERE created_by = ? AND serv_group = 'Laboratory' AND date_entry = ?  ");
        $stmt->execute(array($_SESSION['id'], $date_today));
        $total_labs_requested_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_labs_requested = $total_labs_requested_row['total'];

        $stmt = $db->prepare("SELECT COUNT(sn) total  FROM patient_ap_services   WHERE created_by = ? AND serv_group = 'Radiology' AND date_entry = ?  ");
        $stmt->execute(array($_SESSION['id'], $date_today));
        $total_scans_requested_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_scans_requested = $total_scans_requested_row['total'];

        echo json_encode(['status' => 200,  
            'total_drugs_requested' => $total_drugs_requested, 
            'total_labs_requested' => $total_labs_requested, 
            'total_scans_requested' => $total_scans_requested
        ]);

    exit;

}



    ?>