<?php
include('../Connections/Conn.php');

$selectDrugSn = "SELECT app_no,hospital_no FROM dialysis";
$drugSnRows = $db->query($selectDrugSn)->fetchAll(PDO::FETCH_ASSOC);
if (!empty($drugSnRows)) {
    foreach ($drugSnRows as $row) {
        $app_no = $row['app_no'];
        $hospital_no = $row['hospital_no'];
        $deleted1 = "SELECT sn FROM patient_ap_services WHERE app_no = :app_no AND hospital_no = :hospital_no AND cat_type = 'Dialysis'";
        $stmt1 = $db->prepare($deleted1);
        $stmt1->bindParam(':app_no', $app_no);
        $stmt1->bindParam(':hospital_no', $hospital_no);
        $stmt1->execute();
        $deletedCount1 = $stmt1->rowCount();

        if ($deletedCount1 == 0) {
            // Delete from dialysis
            $deleted2 = "DELETE FROM dialysis WHERE app_no = :app_no AND hospital_no = :hospital_no";
            $stmt2 = $db->prepare($deleted2);
            $stmt2->bindParam(':app_no', $app_no);
            $stmt2->bindParam(':hospital_no', $hospital_no);
            $stmt2->execute();

            // Delete from apptm
            $deleted3 = "DELETE FROM apptm WHERE appt_no = :app_no AND hospital_no = :hospital_no";
            $stmt3 = $db->prepare($deleted3);
            $stmt3->bindParam(':app_no', $app_no);
            $stmt3->bindParam(':hospital_no', $hospital_no);
            $stmt3->execute();
        }
    }
}
