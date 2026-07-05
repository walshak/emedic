<div class="modal  fade" id="dialysisAddNoteModal<?= $dialysis_info->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 40%; margin: 0px auto; ">

        <!-- Modal content-->
        <div class="modal-content">

            <form action="<?= $actual_link . "&saving=1010"; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                <div class="modal-body" style="min-height: 400px">
                    <?php
                    if ($check_count > 0) { ?>
                        <h3 style="color: red; ">There is an uncompleted session(s) you need to complete ... </h3>
                    <?php } ?>

                    <h2> Dialysis Note</h2>

                    <h5>
                        <b> Patient Name: </b><?= $dialysis_info->patient_name; ?> <br>
                        <b> Request Date: </b><?= $dialysis_info->request_date; ?> <br>
                        <b> Request By: </b><?= $dialysis_info->request_by; ?> <br>
                    </h5>
                    <div>
                        <div class="col-md-4" style="display: none;">
                            <input type="text" name="dialysis_id" id="dialysis_id" value="<?= $dialysis_info->id; ?>" class="form-control" maxlength="10" required>
                            <input type="text" name="token" id="token" value="<?= $token; ?>" class="form-control" maxlength="10" required>
                        </div>

                        <div class="row">

                            <div class="col-md-3">
                                <label for="reg_select" class="">UF</label>
                                <input type="text" name="uf" id="uf" value="" step="any" class="form-control" maxlength="10">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Blood Flow</label>
                                <input type="text" name="blood_flow" id="blood_flow" step="any" value="" class="form-control" maxlength="10">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Pre Weight</label>
                                <input type="text" name="vp_pre_weight" id="vp_pre_weight" value="" class="form-control" maxlength="10">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Post Weight</label>
                                <input type="text" name="ap_post_weight" id="ap_post_weight" class="form-control" maxlength="10">
                            </div>


                        </div>
                        <br>

                        <div class="row">


                            <div class="col-md-3">
                                <label for="reg_select" class="">UFR (m/s/hr)</label>
                                <input type="number" maxlength="10" name="ufr" id="ufr" value="" step="any" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Date</label>
                                <input type="date" maxlength="10" name="performed_date" id="performed_date" value="" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Time</label>
                                <input type="time" maxlength="10" name="performed_time" id="performed_time" value="" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">HEP</label>
                                <input type="text" maxlength="10" name="hep" id="hep" class="form-control" value="">
                            </div>



                        </div>
                        <br>

                        <div class="row">

                            <div class="col-md-3">
                                <label for="reg_select" class="">BP (mmHg)</label>
                                <input type="text" maxlength="10" name="bp" id="bp" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Pulse (b/min)</label>
                                <input type="text" maxlength="10" name="pulse" id="pulse" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Diagnosis</label>
                                <input type="text" maxlength="100" name="diagnosis" id="diagnosis" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Access</label>
                                <input type="text" maxlength="50" name="access" id="access" class="form-control" value="">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-3">
                                <label for="reg_select" class="">Dialyzer</label>
                                <input type="text" maxlength="50" name="dialyzer" id="dialyzer" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">PCV</label>
                                <input type="text" maxlength="50" name="pcv" id="pcv" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Duration</label>
                                <input type="text" maxlength="100" name="duration" id="duration" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">SPO2</label>
                                <input type="text" maxlength="100" name="spo" id="spo" class="form-control" value="">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form_sep">
                                    <label for="intradialysis_complication">Intradialysis Complication:</label>
                                    <select id="intradialysis_complication" name="intradialysis_complication" class="form-control">
                                        <option value="">-- Select Complication --</option>
                                        <option value="Hypertension">Hypertension</option>
                                        <option value="Hypotension">Hypotension</option>
                                        <option value="Hypoglycemia">Hypoglycemia</option>
                                        <option value="Hyperglycemia">Hyperglycemia</option>
                                        <option value="Fever">Fever</option>
                                        <option value="Hypothermia">Hypothermia</option>
                                        <option value="Cramps">Cramps</option>
                                        <option value="Itching">Itching</option>
                                        <option value="Headache">Headache</option>
                                        <option value="Seizure">Seizure</option>
                                        <option value="Cough">Cough</option>
                                        <option value="Diarrhoea">Diarrhoea</option>
                                        <option value="Vomiting">Vomiting</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="reg_select" class="">Name of Nurse</label>
                                <select name="name_nurse" class="input-sm chosen-select" style="width:350px;">
                                    <option selected="selected" value="">--Select--</option>
                                    <?php
                                    $stmt = $db->prepare("SELECT fullname FROM admin_users WHERE rights='NS' and status='1'");
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option <?php if ($fullname == $rwx['fullname']) { ?> selected <?php } ?> value="<?php echo $rwx['fullname'] ?>"><?php echo $rwx['fullname'] ?></option>

                                    <?php    }
                                    }

                                    ?>

                                </select>
                            </div>
                            <div class="col-md-6">


                                <div class="form_sep">
                                    <label>Duty Shift</label>
                                    <select name="duty_shift" class="input-sm chosen-select" style="width:350px;">
                                        <option selected="selected" value="">--Select--</option>
                                        <option value="Morning">Morning</option>
                                        <option value="Night">Night</option>

                                    </select>
                                </div>

                            </div>



                        </div>




                        <br>

                        <div class="row">
                            <!-- <div class="col-md-6"> -->
                            <!-- <label for="reg_select" class="req">Fluid Loss</label> -->
                            <input type="hidden" name="fluid_loss" id="fluid_loss" class="form-control" value="<?php //$dialysis_info->fluid_loss; 
                                                                                                                ?>">
                            <!-- </div> -->

                            <div class="col-md-12">
                                <label for="reg_select" class="req">Dialysis Note</label>
                                <textarea name="dialysis_note" id="dialysis_note" cols="30" rows="5" class="form-control"> </textarea>
                            </div>
                        </div>





                        <input type="hidden" name="id" value="<?= $dialysis_info->id; ?>">
                        <input type="hidden" name="app_no" value="<?= $dialysis_info->app_no; ?>">
                        <input type="hidden" name="hospital_no" value="<?= $dialysis_info->hospital_no; ?>">
                        <input type="hidden" name="uf_goal" value="<?= $dialysis_info->uf; ?>">
                        <input type="hidden" name="performed_by" id="performed_by" value="<?= $_SESSION['id']; ?>">




                    </div>

                </div>
                <div class="modal-footer">

                    <?php



                    if ($check_count > 0  and $uf == '') {
                        $status_lock = 1;
                    } elseif ($check_count > 0 and $uf != '') {
                        $status_lock = 0;
                    } else {
                        $status_lock = 0;
                    }

                    $status_lock = 0;


                    ?>

                    <a href="#" style="float: left;"><input type="checkbox" style="height: 17px; width: 17px; " name="completed" id="completed" value="yes" <?= $dialysis_info->completed == 'yes' ? 'checked' : ''; ?>> <strong>Mark as Completed</strong> </a>
                    <button type="submit" class="btn btn-primary" name="" <?php if ($status_lock == 1) { ?>disabled<?php } ?>> Save Data </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>

            </form>
        </div>
    </div>
</div>





<div class="modal  fade" id="changeMachineModal<?= $dialysis_info->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 40%; margin: 0px auto; ">

        <!-- Modal content-->
        <div class="modal-content">

            <form action="<?= $details_page_url; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                <div class="modal-body" style="min-height: 400px">



                    <h2> Change Machine</h2>
                    <h3>
                        <b> Patient Name: </b><?= $dialysis_info->patient_name; ?> <br>
                        <b> Request Date: </b><?= $dialysis_info->request_date; ?> <br>
                        <b> Request By: </b><?= $dialysis_info->request_by; ?> <br>
                    </h3>


                    <hr>


                    <div>
                        <div class="col-md-4" style="display: none;">
                            <input type="hidden" name="dialysis_id" id="dialysis_id" value="<?= $dialysis_info->id; ?>" class="form-control" maxlength="10" required>
                            <input type="text" name="token" id="token" value="<?= $token; ?>" class="form-control" maxlength="10" required>
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form_sep">
                                    <label class="req">Request Type:</label>
                                    <select name="request_type" id="request_type" class="form-control" style="font-size: 14px;" required>
                                        <option value="">Select Request Type</option>
                                        <?php
                                        $get_services = $Dialysis->get_services();
                                        foreach ($get_services as $key => $service_) {

                                            if ($dialysisInfo->price_table_id != $service_->sn) {
                                                echo ' <option value="' . $service_->sn . '">' . $service_->item_service . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <label class="">.</label><br>
                                <button type="submit" class="btn btn-primary" name="change_machine">Change Machine</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>


                            </div>

                        </div>


                        <input type="hidden" name="id" value="<?= $dialysis_info->id; ?>">

                        <input type="hidden" name="insurance_type" value="<?= $insurance_type; ?>">
                        <input type="hidden" name="interest" value="<?= $interest; ?>">
                        <input type="hidden" name="add_minus" value="<?= $add_minus; ?>">
                        <input type="hidden" name="payment_mode" value="<?= $payment_mode; ?>">



                        <input type="hidden" name="price_table_id" value="<?= $dialysisInfo->price_table_id; ?>">
                        <input type="hidden" name="app_no" value="<?= $dialysis_info->app_no; ?>">
                        <input type="hidden" name="hospital_no" value="<?= $dialysis_info->hospital_no; ?>">
                        <input type="hidden" name="uf_goal" value="<?= $dialysis_info->uf; ?>">
                        <input type="hidden" name="performed_by" id="performed_by" value="<?= $_SESSION['id']; ?>">
                        <input type="hidden" name="redirect" id="redirect" value="<?php echo 'p=' . $_GET['p'] . '&d=' . $_GET['d']; ?>">

                    </div>

                </div>


            </form>
        </div>
    </div>
</div>