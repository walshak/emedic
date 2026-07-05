<?php include("../Connections/Conn.php"); ?>
<?php
session_start();

include('objects.php');
include('helpers.php');

$response_main = array(
    'claim_amt' => '',
    'amount_paying' => '',
    'pay_mode' => '',
    'ccop_int_charge' => '',
    'hosp_price' => '',
    'status' => ''
);

if (isset($_POST["change_procedure"])) {

    $procedure_sn = $_POST["change_procedure"];
    $interest = $_POST["interest"];
    $insurance_type = $_POST["insurance_type"];
    $add_minus = $_POST["add_minus"];
    $payment_mode = $_POST["payment_mode"];
    $services_access = $_POST["services_access"];
    $app_no = $_POST["app_no"];

    $stmt = $db->query("SELECT * FROM prices_table WHERE sn=$procedure_sn");
    if ($stmt->rowCount() > 0) {
        $row_serv = $stmt->fetch(PDO::FETCH_ASSOC);
        $item_sn = $row_serv['sn'];
        $item_services = $row_serv['item_service'];
        $coverage = $row_serv['coverage'];
        $price_table = $row_serv['price_table'];
        $serv_group = $row_serv['price_table'];
        $cat_type = $row_serv['category'];
        $dept_id = $row_serv['dept'];
        $hosp_price = $row_serv['hosp_price'];
        $ext_price = $row_serv['ext_price'];
        $nhis_price = $row_serv['nhis_price'];
        $nhis_price = $nhis_price == 0 ? $hosp_price : $nhis_price;
        $cash_price = $hosp_price;

        $procedure_item_services = $item_services;
        $hos_no = $hospital_no;

        $target_sn = $item_sn;
        $NHIS_DRUG_CONSUMBL_STATE = 0;
        $_tariff_table = "hmo_medical_tariff";
        $amount_invoice = new_service_amount_cal(
            $db,
            $hospital_no,
            $app_no,
            $interest,
            $insurance_type,
            $insurance_no,
            $hosp_price,
            $ext_price,
            $nhis_price,
            $services_access,
            $add_minus,
            $payment_mode,
            $_tariff_table,
            $target_sn,
            $NHIS_DRUG_CONSUMBL_STATE
        );

        $claim_amt = $amount_invoice["claim_amt"];
        $amount_paying = $amount_invoice["amount_paying"];
        $pay_mode = $amount_invoice["pay_mode"];
        $ccop_int_charge = $amount_invoice["ccop_int_charge"];
        $hosp_price = $amount_invoice["item_amt"];


        if ($item_amt > 0) {
            $procedure_amount = $item_amt;
        } else {
            $procedure_amount = $claim_amt;
        }

        $response_main['claim_amt'] = $claim_amt;
        $response_main['amount_paying'] = $amount_paying;
        $response_main['pay_mode'] = $pay_mode;
        $response_main['ccop_int_charge'] = $ccop_int_charge;
        $response_main['hosp_price'] = $hosp_price;
        echo json_encode($response_main);
        exit;
    }
}


if (isset($_POST["procedure_sn"])) {

    $approve_request_id = $_POST["procedure_sn"];
    $stmt = $db->prepare("SELECT p.*,s.paystatus FROM procedures p INNER JOIN patient_ap_services s ON s.sn=p.sale_no WHERE p.sn= '$approve_request_id'");
    $stmt->execute();
    $rw = $stmt->fetch(PDO::FETCH_ASSOC);
    $hospital_no = $rw['hospital_no'];
    $paystatus = $rw['paystatus'];
    $procedures = $rw['procedures'];
    $sale_no = $rw['sale_no'];

    $patient_info = $Patient->getByHospitalNo($hospital_no);

    if (!empty($patient_info)) {
        $dob = $patient_info->dob;
        $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
        $patient_insurance = $patient_info->insurance_type;
        $patient_access_type = $patient_info->services_access;
        $nhis_no_ext = $patient_info->nhis_no_ext;
        $nhis_no = $patient_info->nhis_no;
        $insurance_name = $patient_info->insurance_name;
        $interest = $patient_info->interest;
        $insurance_type = $patient_info->insurance_type;
        $insurance = $patient_info->insurance_type;
        $services_access = $patient_info->services_access;
        $insurance_no = $patient_info->insurance_no;
        $add_minus = $patient_info->add_minus;
        $payment_mode = $patient_info->payment_mode;
    }

    $patientOnQueuestmt = $db->prepare("SELECT * FROM apptm  
    WHERE hospital_no = '$hospital_no' AND status != 'cancelled' ORDER BY sn LIMIT 1 ");
    $patientOnQueuestmt->execute();
    if ($patientOnQueuestmt->rowCount() > 0) {
        $patientAppoint = $patientOnQueuestmt->fetch();
        $app_no = $patientAppoint['appt_no'];
    } else {
        $app_no = $hospital_no;
    }
?>
    <?php if ($paystatus == 0) { ?>
        <h3 style="color: red;;">Payment Status: Unpaid</h3>
    <?php } else { ?>
        <h3 style="color: blue;;">
            Payment Status: Paid <br>
            <b>Note:</b>If you change the procedure, the previously paid amount will be reverted to the patient’s account to be used for the new procedure. Click <b>Apply</b> to proceed.
        </h3>
    <?php } ?>

    <form method="post" id="subject" action="<?php echo $editFormAction; ?>" onsubmit="return confirmChange();">

        <input type="hidden" value="<?= $interest; ?>" id="interest" name="interest">
        <input type="hidden" value="<?= $insurance_type; ?>" id="insurance_type" name="insurance_type">
        <input type="hidden" value="<?= $insurance_no; ?>" id="insurance_no" name="insurance_no">
        <input type="hidden" value="<?= $add_minus; ?>" id="add_minus" name="add_minus">
        <input type="hidden" value="<?= $payment_mode; ?>" id="payment_mode" name="payment_mode">
        <input type="hidden" value="<?= $services_access; ?>" id="services_access" name="services_access">
        <input type="hidden" value="<?= $hospital_no; ?>" id="hospital_no" name="hospital_no">
        <input type="hidden" value="<?= $app_no; ?>" id="app_no" name="app_no">

        <div class="form_sep">
            <label class="req">Choose the Procedure to change to:</label>
            <select class="form-control" name="change_procedure" id="change_procedure" onchange="change_procedure_list()" required>
                <option value="">Select</option>
                <?php
                $stmt = $db->prepare("
                    SELECT sn, item_service, hosp_price, ext_price 
                    FROM prices_table 
                    WHERE price_table = 'Medical Services'
                    AND hosp_price > 0
                    AND ext_price > 0
                    ORDER BY item_service ASC");
                $stmt->execute();
                $is_not_360 = !in_array(strtolower($_SESSION['h_code']), array('360', 'mluth'));
                while ($procedure = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $sn           = htmlspecialchars($procedure['sn']);
                    $item_service = htmlspecialchars($procedure['item_service']);

                    if ($is_not_360) {
                        $price = ' - N' . number_format($procedure['ext_price']);
                    } else {
                        $price = '';
                    }
                ?>
                    <option value="<?php echo $sn; ?>">
                        <?php echo $item_service . $price; ?>
                    </option>
                <?php } ?>
            </select>
        </div>


        <div class="form_sep">
            <label class="req">Enter Reason for Change</label>
            <input type="text" class="form-control" id="enter_reason" name="enter_reason">
        </div>



        <div class="form_sep">
            <button class="btn btn-primary btn-sm" type="submit" name="change_procedure_button">Apply</button>
        </div>

        <input type="hidden" value="" id="claim_amt" name="claim_amt">
        <input type="hidden" value="" id="amount_paying" name="amount_paying">
        <input type="hidden" value="" id="pay_mode" name="pay_mode">
        <input type="hidden" value="" id="ccop_int_charge" name="ccop_int_charge">
        <input type="hidden" value="" id="hosp_price" name="hosp_price">
        <input type="hidden" value="<?= $approve_request_id; ?>" id="approve_request_id" name="approve_request_id">
        <input type="hidden" value="<?= $paystatus; ?>" id="paystatus" name="paystatus">
        <input type="hidden" value="<?= $sale_no; ?>" name="sale_no">
        <input type="hidden" value="<?= $procedures; ?>" name="old_procedures">

    </form>
<?php

}
?>