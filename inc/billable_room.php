<?php
session_start();

include("../Connections/Conn.php");
include('../doctor/objects.php');

$hosp_no  = $_POST['hosp_no'];
$room_bed_sn  = $_POST['room_bed_sn'];
$admission_sn  = $_POST['admission_sn'];
$appointment_number  = $_POST['appointment_number'];


$dept_id = $_SESSION['dept_id'];

$patient_info = $Patient->getByHospitalNo($hosp_no);

$ap_type = 0;
if (!empty($patient_info)) {
    $dob = $patient_info->dob;
    $visit_status = $patient_info->visit_status;
    $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
    $sex = $patient_info->gender;
    $insurance_type = $patient_info->insurance_type;
    $patient_insurance = $patient_info->insurance_type;
    $insurancen_no = $patient_info->hmo_no;
    $interest = $patient_info->interest;
    $token = $patient_info->token;
    $add_minus = $patient_info->add_minus;
    $payment_mode = $patient_info->payment_mode;
    $services_access = $patient_info->services_access;
    $manage_hx = $patient_info->patient_manage_hx;
    $patient_manage_by = $patient_info->patient_manage_by;
    $vip = $patient_info->vip;
}
?>
<form action="patient.php?hosp_no=<?= $hosp_no; ?>&adm_req" method="POST" id="subject" name="subject">

    <h2 style="color: red; ">This Patient Is Not Currently Bill for Accommodation!</h2>
    <HR>
    <h3>Do you want to set Daily Auto bill for this Patient?</h3>

    <hr>
    <table width="100%">
        <tr>
            <td>
                <div class="form_sep">
                    <label for="reg_input_no" class="req">Effective Date</label>

                    <div class="form-group" id="data_1">
                        <input type="date" name="admit_date" id="admit_date" class="form-control" maxlength="10" value="<?php echo date("Y-m-d"); ?>" required>
                    </div>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <label for="reg_select" class="req">Effective Time (24hrs Format)</label>
                    <input type="time" name="admit_time" id="admit_time" class="form-control" value="<?php echo date('H:i') ?>" maxlength="5" required>
                </div>

            </td>
        </tr>
    </table>

    <div class="form_sep">
        <label for="reg_select" class="req" style="font-size:14px">Choose Nursing Services To Be Billed Authomatically!</label><br>
        <?php






        $price_table = 'Nursing Services';
        $stmtx = $db->prepare("SELECT * FROM prices_table 
		WHERE price_table=:price_table and ext_price > 0 and hosp_price > 0 and nusing_setauth=1");
        $stmtx->bindValue(':price_table', $price_table, PDO::PARAM_STR);
        $stmtx->execute();
        ?>



        <?php while ($row_emp = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
            <input type="checkbox" name="services_name[]" value="<?php echo $row_emp['sn']; ?>" style="height: 15px; width: 15px;">
            &nbsp;<?php echo $row_emp['item_service']; ?><br>


        <?php } ?>





    </div>
    <hr>
    <button class="btn btn-success" type="submit" name="start_billing" id="start_billing" onclick="return confirm('Are you sure you want to Start Billing this Patient ?')">Start Billing</button>


    <input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
    <input type="hidden" name="dept_id" value="<?php echo $_SESSION['dept_id']; ?>" />
    <input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
    <input type="hidden" name="ap_type" value="<?php echo $ap_type; ?>" />
    <input type="hidden" name="interest" value="<?php echo $interest; ?>" />
    <input type="hidden" name="insurance_type" value="<?php echo $patient_insurance; ?>" />
    <input type="hidden" name="admission_sn" value="<?php echo $admission_sn; ?>" />
    <input type="hidden" name="add_minus" value="<?php echo $add_minus; ?>" />
    <input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>" />
    <input type="hidden" name="insurance_no" value="<?php echo $insurance_no; ?>" />
    <input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
    <input type="hidden" name="room_bed_sn_" value="<?php echo $room_bed_sn; ?>" />

</form>