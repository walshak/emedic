<?php
if (isset($_POST['get_dashboard_counts'])) {
    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');
    header('Content-Type: application/json');

    $date_today = date('Y-m-d');
    $date_month = date('Y-m');
    // $stmt = $db->prepare("SELECT COUNT(sn) total FROM apptm  WHERE (referal_doc = ? OR doctor_id = ? ) AND date_ap = '$date_today'   ORDER BY sn DESC ");
    // $stmt->execute(array($_SESSION['username'], $_SESSION['id']));
    // $seeTodayRow = $stmt->fetch(PDO::FETCH_ASSOC);
    // $seen_today_count = $seeTodayRow['total'];

    $stmt = $db->prepare("SELECT DISTINCT app_no FROM notes  WHERE created_by = ? AND date_entry LIKE '$date_today%'");
    $stmt->execute(array($_SESSION['id']));
    $seen_today_count = $stmt->rowCount();

    $stmt = $db->prepare("SELECT DISTINCT app_no FROM notes  WHERE created_by = ? AND date_entry LIKE '$date_month%'");
    $stmt->execute(array($_SESSION['id']));
    // $seeMonthRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $patient_seen_thismonth_count = $stmt->rowCount();


    $stmt = $db->prepare("SELECT COUNT(sn) total  FROM admission  WHERE hospital_no IS NOT NULL AND doc_incharge = ? AND date_admit LIKE '$date_today%' AND adm_status = 3 ");
    $stmt->execute(array($_SESSION['id']));
    $admittedTodayRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $admitted_today_count = $admittedTodayRow['total'];


    $sql = "SELECT COUNT(sn) total  FROM admission  WHERE hospital_no IS NOT NULL AND (doc_incharge = ? OR doc_incharge = ?) AND adm_status = 3 ";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($_SESSION['id'], $_SESSION['username']));
    $admittedRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $no_on_admission_count = $admittedRow['total'];

    $sql = "SELECT COUNT(sn) total  FROM admission  WHERE   hospital_no IS NOT NULL AND (doc_incharge = ? OR doc_incharge = ?) AND adm_status = 4 ";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($_SESSION['id'], $_SESSION['username']));
    $dischargedRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $discharged_count = $dischargedRow['total'];

    //////////////// PROCEDURES
    $stmt = $db->prepare("SELECT COUNT(sn) total  FROM procedures  WHERE created_by = ? OR consultant_id = ?  ");
    $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
    $total_procedure_request_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_procedure_request = $total_procedure_request_row['total'];

    $stmt = $db->prepare("SELECT COUNT(sn) total  FROM procedures  WHERE (created_by = ? OR consultant_id = ?) AND post_op_results IS NOT NULL  ");
    $stmt->execute(array($_SESSION['id'], $_SESSION['id']));
    $total_procedure_performed_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_procedure_performed = $total_procedure_performed_row['total'];


    if ($_SESSION['rights'] == 'MD') {
        $rights = "";
    } else {
        $ID = $_SESSION['id'];
        $rights = "(p.created_by = '$ID' OR p.consultant_id = '$ID') AND ";
    }
    $stmt = $db->prepare("SELECT COUNT(a.sn) total FROM procedures as p 
    inner join patient_ap_services as a on a.sn=p.sale_no  WHERE $rights post_op_results IS NULL AND paystatus=1");
    $stmt->execute();
    $total_procedure_pending_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_procedure_pending = $total_procedure_pending_row['total'];


    echo json_encode([
        'status' => 200,
        'seen_today_count' => $seen_today_count,
        'patient_seen_thismonth_count' => $patient_seen_thismonth_count,
        'admitted_today_count' => $admitted_today_count,
        'no_on_admission_count' => $no_on_admission_count,
        'discharged_today_count' => $discharged_count,
        'total_procedure_performed' => $total_procedure_performed,
        'total_procedure_request' => $total_procedure_request,
        'total_procedure_pending' => $total_procedure_pending
    ]);
    exit;
}
