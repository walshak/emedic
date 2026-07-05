<?php
if (isset($_POST['gen_med_services_hx'])) {
    session_start();
    include("Connections/Conn.php");
    ///require_once('cam_check_list.php');
    include('doctor/objects.php');
    include('doctor/helpers.php');
    include("inc/credit_current_balance.php");
    $hospital_no = $_POST['hospital_no'];
}


?>


<h3><u>Documentation History:</u></h3>
<table class="table active">
    <thead>
        <tr>
            <th>#</th>
            <th>Service</th>
            <th>Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        $notes = [];

        if (isset($hospital_no)) {
            // Fetch notes services for hospital_no
            $stmt = $db->prepare("SELECT * FROM notes_services WHERE status = '1' AND hospital_no = ? ORDER BY id DESC LIMIT 10");
            $stmt->execute([$hospital_no]);
            $notes = $stmt->fetchAll();
        } else {
            $stmt = $db->prepare("SELECT * FROM notes_services WHERE status = '1' ORDER BY id DESC LIMIT 10");
            $stmt->execute();
            $notes = $stmt->fetchAll();
        }

        // If hospital_no set, fetch balance once
        if (isset($hospital_no)) {
            $emr = $hospital_no;
            $general_credit_limit = $_SESSION['credit_limit_status'];
            $items = call_current_balance($db, $emr, $general_credit_limit);
            $current_balance =  $items["current_balance"];
            $credit_limit  = $items['bal_credit_limit'];
        }

        foreach ($notes as $note) {
            $paystatus = 0;
            $pay = 0;
            $pay_mode = '';
            $drug_status = 0;

            if (!empty($note['app_service_tbl_id'])) {
                $hospital_no = $note['hospital_no'];
                $stmt = $db->prepare("SELECT paystatus,item_services,pay_mode, pay, hospital_no, drug_status 
                FROM patient_ap_services WHERE sn = ? AND hospital_no =? ");
                $stmt->execute([$note['app_service_id'], $hospital_no]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($payment) {
                    $paystatus = intval($payment['paystatus']);
                    $pay_mode = $payment['pay_mode'];
                    $hosp_no = $payment['hospital_no'];
                    $pay = $payment['pay'];
                    $drug_status = $payment['drug_status'];
                }
            }

            echo "<tr>";
            echo "<td>{$sn}</td>";
            echo "<td><strong>{$note['item_services']}</strong><br>";

            echo $note['isCompleted']
                ? '<b class="text-success"><i>COMPLETED</i></b>'
                : '<b class="text-danger">NOT COMPLETED</b>';

            echo " /<br>";
            echo $paystatus
                ? '<strong class="text-success">Paid</strong>'
                : '<strong class="text-danger">Not Paid</strong>';

            // Wallet payment condition
            if (isset($hospital_no) && $current_balance > 0 && $_SESSION['payfrom_status'] == 1 && $pay_mode == 'cash' && $paystatus == 0) {
                echo "<br><button type='button' class='btn btn-warning btn-xs' id='pay_now{$note['app_service_tbl_id']}' onClick=\"payNow('{$note['app_service_tbl_id']}', 'medical_services', '{$hosp_no}')\">Pay from Wallet</button>";
            }

            echo "<br><strong>Requested By:</strong> {$note['prepared_by']}";
            echo "<br><strong>Captured By:</strong> {$note['consultant_name']}</td>";

            echo "<td>" . date('d M, Y', strtotime($note['created_at'])) . "</td>";

            echo "<td>";
            $cr = ($paystatus == 0 && $note['is_free'] == '') ? 1 : 0;

            if ($paystatus == 1 || $credit_limit >= $pay || $note['is_free'] == 'free') {
                echo "<a href='patient.php?hosp_no={$note['hospital_no']}&editMedService=" . base64_encode($note['id']) . "&xccrxcccx={$cr}' class='btn btn-primary'><i class='fa fa-edit'></i></a> ";
                echo "<a href='patient.php?hosp_no={$note['hospital_no']}&printMedService=" . base64_encode($note['id']) . "' class='btn btn-success'><i class='fa fa-print'></i></a> ";
            }

            if ($paystatus == 0 && $drug_status == 0) {
                echo "<a href='patient.php?hosp_no={$note['hospital_no']}&DeleteMedService=" . base64_encode($note['id']) . "' class='btn btn-danger' onclick=\"return confirm('Are you sure you want to delete this medical service?');\"><i class='fa fa-trash'></i></a>";
            }

            echo "</td></tr>";

            $sn++;
        }
        ?>
    </tbody>
</table>