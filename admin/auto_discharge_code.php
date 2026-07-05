<?php

///if (isset($_POST['system_discharge'])) {

include("../Connections/Conn.php");

$CurDateHR = date("Y-m-d H:i:s");

// Prepare the SQL query
$query = "SELECT 
        ap_date_time,
        appt_no,
        hospital_no,
        queue_lock,
        status,
        app_duration,
        app_expiration_date 
    FROM apptm 
    WHERE (status='future' OR status='checkin') AND ap_date_time <= :curDateHR";
$apptm = $db->prepare($query);
$apptm->bindParam(':curDateHR', $CurDateHR);
$apptm->execute();

if ($apptm->rowCount() > 0) {
    while ($row = $apptm->fetch(PDO::FETCH_ASSOC)) {

        $chk_date = $row['ap_date_time'];
        $appt_no = $row['appt_no'];
        $hospital_no = $row['hospital_no'];
        $queue_lock = $row['queue_lock'];
        $status = $row['status'];
        $app_duration = $row['app_duration'];
        if ($app_duration == '') {
            $app_duration = 1;
        }
        $app_expiration_date = $row['app_expiration_date'];

        //// TIME DIFFERENCE STATUS ///

        date_default_timezone_set('Africa/Lagos');
        $date1 = new DateTime($CurDateHR);
        $date2 = new DateTime($chk_date);
        $diff = $date2->diff($date1);
        $d = $diff->format('%a');
        ///     echo '<br>' . $app_duration . '===' . $d . '===' . $status . '===' . $appt_no . '===' . $queue_lock;

        if ($status == 'checkin' && ($CurDateHR > $app_expiration_date  or $d >= $app_duration)) {
            $status = 'cancelled';
            $discharge_remarks = '';
            $ap_direction = '0';
            $queue_lock = '1';
            $stmt_serv = $db->query("SELECT paystatus FROM patient_ap_services WHERE app_no='$appt_no' AND serv_group='Consultation'");
            if ($stmt_serv->rowCount() > 0) {
                $rowX = $stmt_serv->fetch(PDO::FETCH_ASSOC);
                $paystatus = $rowX['paystatus'];
                if ($paystatus == '1') {
                    $status = 'discharge';
                    $discharge_remarks = 'System Discharge';
                }
            }

            $updateSQL = "UPDATE apptm 	SET queue_lock='$queue_lock',ap_direction='$ap_direction', status='$status', discharge_remarks='$discharge_remarks' 	WHERE appt_no='$appt_no'";
            $db->exec($updateSQL);
        } elseif ($status == 'future' and $CurDateHR > $app_expiration_date) {

            $queue_lock = '2';
            $ap_direction = '0';
            $status = 'cancelled';
            $discharge_remarks = '';

            $updateSQL = "UPDATE apptm 	SET queue_lock='$queue_lock',ap_direction='$ap_direction', status='$status', discharge_remarks='$discharge_remarks' 	WHERE appt_no='$appt_no'";
            $db->exec($updateSQL);
        }
    }
}
///}
