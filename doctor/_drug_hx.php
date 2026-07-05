<?php

if (!isset($appointment_number)) {
    $appointment_number = null;
}

if (
    isset($_POST['save_medication_plan']) ||
    isset($_POST['update_medication_plan']) ||
    isset($_POST['loadDrugHx']) ||
    isset($_POST['delete_medication_plan'])
) {
    session_start();
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');
    include("_session.php");



    if (
        isset($_POST['save_medication_plan']) ||
        isset($_POST['update_medication_plan']) ||
        isset($_POST['delete_medication_plan'])
    ) {
        header('Content-Type: application/json');
    }


    if (isset($_POST['loadDrugHx'])) {
        $nn = 1;
        $hospital_no = $_POST['hospital_no'];
        $appointment_number = $_POST['appointment_number'];
        $drug_hx_stmt = $db->prepare("SELECT DISTINCT item_services, sn,app_no, remarks, prepared_by,claim_amt, pay, date_entry,drug_status,paystatus   FROM  patient_ap_services 
            WHERE hospital_no = ? AND serv_group = 'Pharmacy' AND paystatus = 1 AND app_no !='$appointment_number' ORDER BY date_entry, drug_status DESC LIMIT 20");
        $drug_hx_stmt->execute(array($hospital_no));
        $drug_hx  = $drug_hx_stmt->fetchAll(PDO::FETCH_ASSOC);

        $tr = '';
        foreach ($drug_hx as $key => $drug_) {
            $amount = $drug_['pay'] != 0 ? $drug_['pay'] : $drug_['claim_amt'];
            $id     = $drug_['sn'];
            $rp     = '';

            if ($appointment_number != "" && $_SESSION['rights'] != 'NS' && $drug_['paystatus'] == 1) {
                $rp  = "<input type='checkbox' class='represcribe-checkbox' value='" . $id . "' style='transform: scale(1.5); margin-right:5px;'>";
            }


            // build status label
            $status = ($drug_['paystatus'] == 1)
                ? "<span style='color:blue;'>Paid</span>"
                : "<span style='color:red;'>Not Paid</span>";

            $tr .= "<tr>
                <td>" . $rp . $drug_['item_services'] . " " . $status . "</td>
                <td>" . $drug_['remarks'] . "</td>
                <td>" . $drug_['prepared_by'] . "</td>
                <td>" . date('d M, Y h:iA', strtotime($drug_['date_entry'])) . "</td>
            </tr>";
        }

        echo $tr;

        exit;
    }

    exit;
}



if (!isset($drug_plan_hx_url)) {
    $drug_plan_hx_url = '_drug_hx.php';
}



?>
<div id="medication_plan_note_wrap<?php $appointment_number; ?>"></div>

<?php

$drug_hx_stmt = $db->prepare("SELECT sn FROM  patient_ap_services 
            WHERE hospital_no = ? AND serv_group = 'Pharmacy' AND paystatus=1 LIMIT 1");
$drug_hx_stmt->execute(array($hospital_no));
$hx_count = $drug_hx_stmt->rowCount();
?>

<div class="ibox-content">

    <h2><I>Drug History or Re-Prescribe Previously Prescribed Drugs</I></h2>

    <table>
        <tr>
            <td>
                <input type="button" name="edit_users" value="Click Here to See Drug History " data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-lg btn-info view_more_medication_hx" />

            </td>
            <td>&nbsp;
            </td>
            <td>
                <buttton class="btn btn-lg btn-warning" id="represcribe-btn" style="display:none" onclick="represcribe()">Submit Drug Re-Prescribe</buttton>

            </td>
        </tr>
    </table>
    <?php if ($hx_count > 0) { ?>

        <b style="color:blue; font-size:14px; ">Please select each medication below and scroll to the top to submit your responses.</b>
        <table class="table table-striped" id="drug__hx__table" style="font-size: 16px;;">

            <thead>
                <tr>
                    <th><strong style="color:crimson;"><b><u>TICK BELOW</u></b></strong> </th>
                    <th>Prescription</th>
                    <th>Given By</th>
                    <th>Date Given</th>
                </tr>
            </thead>
            <tbody id="drug_hx_tbl"></tbody>
        </table>

    <?php  } ?>


</div>



<div class="modal inmodal" id="med_hx_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Medication History </h4>
            </div>
            <div class="modal-body" id="med_hx_body">
                <h4 class="text-center text-danger">Loading, please wait...</h4>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>




<script>
    $(document).on('click', '.view_more_medication_hx', function() {

        var hosp_number = $(this).attr("id");
        $.ajax({
            url: "../inc/plan_add_view.php",
            method: "POST",
            data: {
                medication_hx_hosp: hosp_number
            },
            success: function(data) {

                $('.modal-title').text("Medications History Administered to Patient");
                $('#med_hx_mdl').modal('show');
                $('#med_hx_body').html(data);
            }
        });
    });
</script>