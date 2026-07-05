<?php
include("../Connections/Conn.php");
// new code
if (isset($_POST['request_id'])) {
    //sleep(10);
    $request_id = $_POST['request_id'];
    $stmt = $db->prepare("SELECT * FROM hrlvapply WHERE sn = ?");
    $stmt->execute([$request_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    //echo $request_id;
    echo json_encode($data);
}
if(isset($_POST['ECode']) && isset($_POST['leave_type'])){
    $year = $_POST['year'];
    $leave_type = $_POST['leave_type'];
    $ECode = $_POST['ECode'];
    $stmt = $db->prepare("SELECT * from hrlvapply inner join hrlv on hrlvapply.type_leave = hrlv.leave_type 
        where hrlvapply.year=? and hrlvapply.type_leave=? and (hrlvapply.status='finish' or hrlvapply.status = 'Approve') and hrlvapply.ECode=?");
    $stmt->execute([$year, $leave_type, $ECode]);
    $res = $stmt->fetchAll();
    echo json_encode($res);
    
}
// new code
