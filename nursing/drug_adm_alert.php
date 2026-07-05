<?php
include("../Connections/Conn.php");

if (isset($_POST['ackwl_drugs'])) {
    echo $hospital_no = $_POST['ackwl_drugs'];

    if ($hospital_no == 'all') {
        $updateSQL = "UPDATE patient_ap_services SET tag='' WHERE tag='drug'";
        $db->exec($updateSQL);
    } else {
        $updateSQL = "UPDATE patient_ap_services SET tag='' WHERE hospital_no='$hospital_no' and tag='drug'";
        $db->exec($updateSQL);
    }
}



$stmt = $db->prepare("SELECT e.surname, e.fname, a.hospital_no 
			FROM admission as a 
			inner join enrollee as e on e.hospital_no = a.hospital_no
			WHERE adm_status='3' order by date_admit DESC");
$stmt->execute();
?>

<div align="center">
    <button type="submit" class="btn btn-danger" name="patient_trs_display"
        onClick="acknown_drugAlert('all')">Click to Acknowledge All</button>
</div>
<hr>

<table class="table table-striped table-bordered table-hover dataTables-example" id="adm_list_tbl">

    <thead>
        <tr>
            <th width="2%">No</th>
            <th>Hospital Number</th>
            <th>Patient Name</th>
            <th style="color: red;">New Drugs</th>
            <th></th>

        </tr>
    </thead>
    <tbody>


        <?php

        $n = 1;

        while ($roww = $stmt->fetch()) {

            $date_entry = date('Y-m-d');
            $hospital_no = $roww['hospital_no'];
            $patient_stmt = $db->prepare("SELECT item_services FROM patient_ap_services 
					where hospital_no = ? and serv_group='Pharmacy' and tag='drug' and date(date_entry)='$date_entry'");
            $patient_stmt->execute(array($roww['hospital_no']));
            if ($patient_stmt->rowCount() > 0) {
                $drug = '';
                while ($rowwx = $patient_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $drug .= $rowwx['item_services'] . '<br>';
                }

        ?>
                <tr>
                    <td><?php echo $n; ?></td>
                    <td><?php echo $roww['hospital_no']; ?></td>
                    <td><?php echo $roww['surname'] . ' ' . $roww['fname'] . ' '; ?></td>
                    <td><?php echo $drug; ?></td>
                    <td><button type="submit" class="btn btn-primary btn-sm" name="patient_trs_display" onClick="acknown_drugAlert('<?= $hospital_no ?>')">Acknowledge</button></td>
                </tr>

        <?php
            }
            $n++;
        } ?>


    </tbody>
</table>