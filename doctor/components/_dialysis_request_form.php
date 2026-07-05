<?php
$setdate = date("Y-m-d");
$all_patients = [];

$isPending = false;
if (isset($_POST['dialysisFirstPhaseBtn'])) {

    $hospital_no = $_POST["hospital_no"];

    $request_type = $_POST["request_type"];
    $number_of_session = $_POST["number_of_session"];

    $duration = $_POST["duration"];
    $uf_goal = $_POST["uf_goal"];
    $heparin_dose = $_POST["heparin_dose"];
    $Dialysate_flow_rate = $_POST["Dialysate_flow_rate"];
    $Anticoagulation = $_POST["Anticoagulation"];
    $blood_flow_rate = $_POST["blood_flow_rate"];
    $note = $_POST["request_note"];
    $add_minus = $_POST["add_minus"];

    $request_note = '<strong>Duration: </strong>' . $duration . ' | ' .  '<strong>UF Goal: </strong>' . $uf_goal . ' | ' .
        '<strong>Heparin Dose: </strong>' . $heparin_dose . ' | ' . '<strong>Dialysate Flow Rate: </strong>' . $Dialysate_flow_rate . ' | ' .  '<strong>Anticoagulation: </strong>' . $Anticoagulation . ' | ' . '<strong>Blood Flow Rate: </strong>' . $blood_flow_rate . ' | ' . $note;

    $patient_info = $Patient->getLight(['hospital_no' => $hospital_no]);


    if (!empty($patient_info)) {

        $patient_name =  $patient_info->surname . ' ' . $patient_info->fname . ' ' . $patient_info->other_name;
        $token = time() . $hospital_no;


        $date_entry = date('Y-m-d');
        $stmtCheck = $db->prepare("SELECT sn FROM patient_ap_services 
		where date_entry LIKE '$date_entry%' AND hospital_no = ? AND drug_sn = ? AND cat_type='Dialysis' AND paystatus = 0");
        $stmtCheck->execute([$hospital_no, $request_type]);

        if ($stmtCheck->rowCount() >= 1) {
            if (isset($_GET['p'])) {
                $p = $_GET['p'];
            } else {
                $p = null;
            } ?>
            <h4 class="alert alert-sm alert-danger">Pending Request(s) Found. Click to <a href="dialysis.php?p=<?= $p; ?>&patient=<?= $hospital_no; ?>&cancel_request" onclick="return confirm('Are you sure you want to Cancel All Pending Requests?')">Cancel Pending Request(s)</a></h4>
            <?php $isPending = true;

            ?>



    <?php } else {

            //// Get information about the service like the price and the name
            $request_type_info = $Dialysis->get_services(['sn' => $request_type]);

            if (!empty($request_type_info)) {

                $appointment_number = $hospital_no;
                $insurance_info = $Insurance->get(['insurance_no' => $patient_info->hmo_no]);
                $services_access = 1;

                if (!empty($insurance_info)) {

                    $target_sn = $request_type_info->sn;
                    $NHIS_DRUG_CONSUMBL_STATE = 0;
                    $_tariff_table = "hmo_medical_tariff";

                    $insurance_no = $patient_info->hmo_no;
                    $insurance_type = $insurance_info->insurance_type;
                    $add_minus = $insurance_info->add_minus;
                    $payment_mode = $insurance_info->payment_mode;
                    $interest = $insurance_info->interest;

                    $amount_invoice = new_service_amount_cal(
                        $db,
                        $hospital_no,
                        $appointment_number,
                        $interest,
                        $insurance_type,
                        $insurance_no,
                        $request_type_info->hosp_price,
                        $request_type_info->ext_price,
                        $request_type_info->nhis_price,
                        $services_access,
                        $add_minus,
                        $payment_mode,
                        $_tariff_table,
                        $target_sn,
                        $NHIS_DRUG_CONSUMBL_STATE,
                        true
                    );

                    $claim_amt = $amount_invoice["claim_amt"];
                    $amount_paying = $amount_invoice["amount_paying"];
                    $pay_mode = $amount_invoice["pay_mode"];
                    $ccop_int_charge = $amount_invoice["ccop_int_charge"];
                    $item_amt = $amount_invoice["item_amt"];
                    $dept_price_id =   $request_type_info->dept;

                    $amount = $amount_paying * $number_of_session;
                    $claim_amt = $claim_amt * $number_of_session;

                    if ($amount_paying > 0) {
                        $dialysis_amount = $amount_paying;
                    } else {
                        $dialysis_amount = $claim_amt;
                    }

                    include_once('components/_dialysis_request_form2.php');
                } else {
                    /// Insurance info not found!
                    echo '<div class="alert alert-danger">ErrorCode[02]: Insurance Details Not Found [' . $patient_info->hmo_no . ']!....</div>';
                }
            } else {
                //// info about service not found!
                echo '<div class="alert alert-danger">ErrorCode[01]: Details About Service Not Found!!....</div>';
            }
        }
    } else {
        /// Invalid hospital no.
    }
} else if (isset($_POST['submitSessionDateTimeBtn'])) {
    include_once('components/_process_dialysis_request_form.php');
} else {
    $all_patients = $Patient->getLight([], true);
    include_once('components/_dialysis_request_form1.php');
}


if ($isPending === true) {
    ?>
    <script>
        // Reduce the height of a Bootstrap modal using JavaScript
        $(document).ready(function() {
            // Target the modal element
            var modal = $('#dialysis-request-modal');

            // Set the desired height
            var newHeight = '150px';

            // Update the modal's height
            modal.find('.modal-content').css('height', newHeight);
        });
    </script>
<?php
}

?>