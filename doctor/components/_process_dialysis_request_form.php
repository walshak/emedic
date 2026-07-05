<?php

$number_of_session = intval($_POST["number_of_session"]);


$data['hospital_no'] = $_POST['hospital_no'];
$data['token'] = $_POST['token'];
$data['price_table_id'] = $_POST['service_id'];
$data['patient_name'] = $_POST['patient_name'];
$data['request_type'] = $_POST['service_name'];
$data['number_of_session'] = $_POST['number_of_session'];
$data['amount'] = $_POST['amount'];
$data['claim_amt'] = $_POST['claim_amt'];
$data['insurance_no'] = $_POST['insurance_no'];
$data['insurance_type'] = $_POST['insurance_type'];
$data['interest'] = $_POST['interest'];
$data['request_note'] = $_POST['request_note'];
$data['request_by'] = $_SESSION['fullname'];
$data['created_by'] = $_SESSION['id'];

if ($data['amount'] > 0 or $data['claim_amt'] > 0) {

    for ($i = 1; $i <= $number_of_session; $i++) {
        $date_time = preg_split('/T/', $_POST['schedule_date_' . $i]);
        $data['schedule_date'] = $date_time[0];
        $data['schedule_time'] = $date_time[1];
        $date_timee = preg_replace('/T/', ' ', $_POST['schedule_date_' . $i]);


        $appointment_number = $Appointment->genAppNo();
        $data['appointment_number'] = $appointment_number;
        $dept_id = $_SESSION['dept_id'];

        $status = 'booked';
        if ($i == 1) {
            $status = 'checkin';
        }

        $duration = 1;
        $date_timee = date('Y-m-d H:i:s');
        $app_expiration_date = date("Y-m-d h:i:s", strtotime($date_timee . "+$duration days", time()));

        if ($_POST['pay_mode'] == 'claim') {
            $data['amount'] = 0;
            $invoice_status = 0;
            $invoice_no = '';
        } else {
            $invoice_no = $_POST['invoice_no'];
        }



        $paystatus = '0';
        $isFreeDialysis = false;
        $stmt = $db->prepare("SELECT id, app_no, price_table_id, dialysis_slot FROM transplants WHERE hospital_no = ? AND dialysis_slot > 0 ORDER BY id DESC ");
        $stmt->execute([$data['hospital_no']]);
        if ($stmt->rowCount() > 0) {
            $transplantInfo = $stmt->fetch();
            $dialysis_slot  = $transplantInfo['dialysis_slot'];
            $price_table_id  = $transplantInfo['price_table_id'];
            $app_no  = $transplantInfo['app_no'];


            $PatientApService_info = $PatientApService->get(['app_no' => $app_no, 'hospital_no' => $data['hospital_no'], 'drug_sn' => $price_table_id]);

            if (!empty($PatientApService_info)) {
                if ($PatientApService_info->paystatus == '1') {
                    $paystatus = '1';
                    $isFreeDialysis = true;
                    $data['request_type'] =  $data['request_type'] . ' (Free service ' . ((3 - $dialysis_slot) + 1) . ')';
                }
            }
        }


        $date_entry = date('Y-m-d');
        $stmtCheck = $db->prepare("SELECT sn FROM patient_ap_services 
		where date_entry LIKE '$date_entry%' AND hospital_no = ? AND drug_sn = ? AND cat_type='Dialysis' AND paystatus = 0");
        $stmtCheck->execute([$data['hospital_no'], $data['price_table_id']]);
        if ($stmtCheck->rowCount() >= 1) {
            echo '<h4 class="alert alert-sm alert-danger">Notice: Sorry you have pending dialysis</h4>';
            $isPending = true;
        } else {
            $insertSQL = $db->prepare("INSERT INTO apptm(appt_no,hospital_no,tagno,patient_name,app_by,referal_doc,app_state,dept,service_id,services_name,insurance,insurance_type,interest,ap_type,auth_code, auth_expire_date,date_ap,ap_time,ap_date_time,status,queue_lock,checkin_by,doctor_id,app_duration,app_expiration_date) VALUES (?,?,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,?,?,?,? )");
            $createAppointment = $insertSQL->execute(array($appointment_number, $data['hospital_no'], '0', $data['patient_name'], 'anydoctor', 'Any Doctor', '', $dept_id, $data['price_table_id'], $data['request_type'], $data['insurance_no'], $data['insurance_type'], $data['interest'], '', '0', '', $data['schedule_date'], $data['schedule_time'], $date_timee, $status, '0', $_SESSION['fullname'], null, $duration, $app_expiration_date));

            if ($createAppointment) {
                $save_dialysis = $Dialysis->save($data);
                if ($save_dialysis) {
                    $save_patient_service = save_patient_ap_service(
                        $db,
                        $appointment_number,
                        $data['hospital_no'],
                        null,
                        'Medical Services',
                        'Dialysis',
                        $_POST['dept_price_id'],
                        $data['price_table_id'],
                        $data['request_type'],
                        $_POST['hosp_price'],
                        $_POST['claim_amt'],
                        $_POST["ccop_int_charge"],
                        0,
                        $invoice_no,
                        $_SESSION['fullname'],
                        $data['amount'],
                        $_POST['pay_mode'],
                        null,
                        null,
                        null,
                        null,
                        null,
                        '',
                        false,
                        0,
                        null,
                        $paystatus,
                        0
                    );

                    if ($save_patient_service) {
                        if ($isFreeDialysis == true) {
                            $dialysis_slot = $dialysis_slot - 1;
                            $updateStmt = $db->prepare("UPDATE transplants SET dialysis_slot = ? WHERE id = ? ");
                            $updateStmt->execute([$dialysis_slot, $transplantInfo['id']]);
                        }
                        echo '<div class="alert alert-success"> Success: Dialysis Request completed.</div>';
                    } else {
                        echo '<div class="alert alert-danger">Error[03]: Could not complete the request....</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">ErrorCode[02]: Could not complete the request....</div>';
                }
            } else {
                echo '<div class="alert alert-danger">ErrorCode[01]: Could not complete the request....</div>';
            }
        }
    }
} else {
    echo '<div class="alert alert-danger">ErrorCode[000]: Cannaot determine the cost....</div>';
}


?>


<script>
    $(document).ready(function() {
        $("#dialysis-request-modal").modal('show');
    });
</script>