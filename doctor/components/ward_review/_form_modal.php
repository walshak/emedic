<?php
if (isset($_POST['save_service_review_btn'])) {
    $review_service = cleanInput($_POST['review_servie_id']);
    $review_note = cleanInput($_POST['review_note']);
    $hospital_no = cleanInput($_POST['hospital_no']);
    $appointment_number = cleanInput($_POST['appointment_number']);
    $interest = cleanInput($_POST['interest']);
    $insurance = cleanInput($_POST['insurance']);
    $ap_type = cleanInput($_POST['ap_type']);

    $review_service_arr = explode("||", $review_service);
    $service_id = $review_service_arr[0];
    $hosp_price = $review_service_arr[1];
    $dept_id = $review_service_arr[2];
    $item_service = $review_service_arr[3];
    $nhis_price = $nhis_price[4];
    $cash_price = $hosp_price;
    // $nhis_price = $hosp_price;
    $error_status = 1;
    $error_msg = 'Error : something went wrong ';

    $serv_group = 'Review';
    $cat_type = 'Nursing Services';

    $patient_info = $Patient->getByHospitalNo($hospital_no);
    $ap_type = 0;
    if (!empty($patient_info)) {
        $insurance_name = $patient_info->insurance_name;
        $interest = $patient_info->interest;
        $insurance_type = $patient_info->insurance_type;
        $services_access = $patient_info->services_access;
        $insurance_no = $patient_info->insurance_no;
        $payment_mode = $patient_info->payment_mode;
        $add_minus = $patient_info->add_minus;


        $target_sn = $service_id;
        $NHIS_DRUG_CONSUMBL_STATE = 0;
        $_tariff_table = "hmo_medical_tariff";
        $amount_invoice = new_service_amount_cal(
            $db,
            $hospital_no,
            $appointment_number,
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

        $save = save_patient_ap_service(
            $db,
            $appointment_number,
            $hospital_no,
            $services_access,
            $serv_group,
            $cat_type,
            $dept_id,
            $service_id,
            $item_service,
            $hosp_price,
            $amount_invoice["claim_amt"],
            $amount_invoice["ccop_int_charge"],
            "",
            $amount_invoice["invoice_no"],
            $_SESSION['fullname'],
            $amount_invoice["amount_paying"],
            $amount_invoice["pay_mode"],
            null,
            null,
            null,
            null,
            null,
            '',
            false,
            1
        );


        if ($save == true) {
            $save_note = saveToNotes($db, $appointment_number, $hospital_no, $review_note, 'DR', 'serv_review',  $_SESSION['fullname'], $item_service,  $_SESSION["id"], false, $service_id);
            $error_status = 2;
            $error_msg = 'Success : Saved';
        }
    }
}
?>
<!------  New Note MODAL ---------------->
<div class="modal inmodal fade" id="serviceReviewModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 50%;">
        <div class="modal-content">
            <form action="<?php echo $editFormAction; ?>" method="post" id="" onsubmit="return confirm('Patient will be bill for this documentation. Do you want to proceed? ')">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Service Review and Documentation</h4>
                </div>
                <div class="modal-body">
                    <div class="well well-sm">
                        <label for="select_template">Select Service</label>
                        <select name="review_servie_id" id="review_servie_id" class="input-sm chosen-select" arial-data="<?= $hospital_no; ?>" required>
                            <option value=""> Select Service </option>
                            <?php
                            $stmt = $db->prepare("SELECT sn, item_service,hosp_price,dept,nhis_price FROM prices_table WHERE price_table = 'Nursing Services' AND (item_service LIKE '%review%' or item_service LIKE '%ward%' or item_service LIKE '%session%') order by item_service ");
                            // $stmt = $db->prepare("SELECT sn, item_service,hosp_price,dept FROM prices_table WHERE price_table = 'Nursing Services' AND item_service LIKE '%review%'");
                            $stmt->execute();
                            if ($stmt->execute() > 0) {
                                while ($service = $stmt->fetch()) {
                            ?>
                                    <option value="<?= $service["sn"] . '||' . $service["hosp_price"] . '||' . $service["dept"] . '||' . $service["item_service"] . '||' . $service["nhis_price"]; ?>"><?= $service["item_service"] . ' ( ' . number_format($service["hosp_price"]) . ' ) '; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div id="review_note_wrap">
                        <label>Enter Documentation Review Below:</label>
                        <textarea name="review_note" id="review_note" class="form-control" cols="45" rows="5" maxlength="160" placeholder=""></textarea>
                    </div>

                    <div>

                        <?php
                        $sn = 1;
                        $total_srvices = 0;
                        $created_by_id = $_SESSION['id'];
                        $stmt = $db->prepare("SELECT sn, paystatus, pay, item_services, drug_sn, app_no,pay_mode,claim_amt   FROM patient_ap_services WHERE serv_group = 'Review' AND paystatus = 0 AND created_by = $created_by_id ");
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            echo '
                                    <div id="patient_services_table_wrap">
                                        <table class="table table-bordered table-striped table-hover table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Service</th>
                                                    <th>PRICE</th>
                                                    <th>STATUS</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                    ';
                            while ($pending_service = $stmt->fetch()) {

                                $amount = $pending_service["pay_mode"] == 'claim' ? $pending_service["claim_amt"] : $pending_service["pay"];
                                $total_srvices += $amount;

                                echo '
                                            <tr>
                                                <td>' . $sn++ . '</td>
                                                <td>' . $pending_service["item_services"] . '</td>
                                                <td>&#8358; ' . number_format($amount) . '</td>
                                                <td>' . ($pending_service["paystatus"] == '1' ? "Paid" : "Not Paid") . '</td>
                                                <td>
                                                <a href="#" class="btn btn-xs btn-danger delete-ars-btn" arial-app-no="' . $pending_service["app_no"] . '" arial-sn="' . $pending_service["sn"] . '"><i class="fa fa-trash"></i> Cancel</a>
                                                <a href="#" class="btn btn-xs btn-success edit-ars-btn" 
                                                    arial-app-no="' . $pending_service["app_no"] . '" 
                                                    arial-sn="' . $pending_service["drug_sn"] . '"
                                                    id="edit-ars-btn-' . $pending_service["sn"] . '"><i class="fa fa-edit"></i> Edit</a>
                                                </td>
                                            </tr>
                                            
                                        ';
                            }
                            echo '
                                    <tr>
                                    <td></td>
                                    <td><b>Total</b></td>
                                    <td></td>
                                    <td><b>&#8358; ' . number_format($total_srvices) . '</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                        </tbody>
                                    </table>
                                    </div>
                                ';
                        } else {
                            // echo 'No services ';
                        }


                        ?>
                    </div>
                </div>
                <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                <input type="hidden" name="appointment_number" value="<?= $appointment_number; ?>">
                <input type="hidden" name="insurance" value="<?= $patient_insurance; ?>">
                <input type="hidden" name="ap_type" value="<?= $patient_access_type; ?>">
                <input type="hidden" name="interest" value="<?= $appointment_interest; ?>">
                <div class="modal-footer">
                    <button class="btn  btn-primary" name="save_service_review_btn">Save and Bill Patient</button>
                    <button type="button" class="btn btn-danger " data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!------  New Note MODAL ---------------->
<div class="modal inmodal fade" id="editserviceReviewModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 50%;">
        <div class="modal-content">
            <!-- <form action=""  method="post"  id="" onsubmit="return confirm('Patient will be bill for this document. Do you want to proceed? ')">  -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Service Review and Documentation</h4>
            </div>
            <div class="modal-body">

                <div id="review_note_wrap">
                    <label>Make Documentation Below:</label>
                    <textarea name="edit_review_note" id="edit_review_note" class="form-control" cols="45" rows="5" maxlength="160" placeholder="Type Your Message Here"></textarea>
                </div>
            </div>
            <input type="hidden" id="review_service_note_id" value="">
            <input type="hidden" id="review_service_app_no" value="">
            <input type="hidden" id="review_service_id" value="">
            <div class="modal-footer">
                <button class="btn  btn-primary update_service_review_btn" name="update_service_review_btn">Save</button>
                <button type="button" class="btn btn-danger close-edit-ars-btn ">Close</button>
            </div>
            <!-- </form> -->
        </div>
    </div>
</div>