<div class="modal  fade" id="editDialysisModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 40%; margin: 0px auto; ">

        <!-- Modal content-->
        <div class="modal-content">


            <form action="<?= $actual_link . "&saving=1010"; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                <div class="modal-body" style="min-height: 400px">
                    <h2> Edit Dialysis Data</h2>
                    <div>
                        <div class="col-md-4" style="display: none;">
                            <input type="text" name="token" id="token" value="<?= $dialysis_data_info->id; ?>" class="form-control" maxlength="10">
                        </div>
                        <div class="row">

                            <div class="col-md-3">
                                <label for="reg_select" class="">UF</label>
                                <input type="text" name="uf" id="uf" value="<?= $dialysis_data_info->uf; ?>" step="any" class="form-control" maxlength="10">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Blood Flow</label>
                                <input type="text" name="blood_flow" id="blood_flow" step="any" value="<?= $dialysis_data_info->blood_flow; ?>" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Pre Weight</label>
                                <input type="text" name="vp_pre_weight" id="vp_pre_weight" value="<?= $dialysis_data_info->vp_pre_weight; ?>" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Post Weight</label>
                                <input type="text" maxlength="" name="ap_post_weight" id="ap_post_weight" step="any" value="<?= $dialysis_data_info->ap_post_weight; ?>" class="form-control">
                            </div>
                        </div>
                        <br>

                        <div class="row">


                            <div class="col-md-3">
                                <label for="reg_select" class="">UFR (m/s/hr)</label>
                                <input type="text" name="ufr" id="ufr" value="<?= $dialysis_data_info->ufr; ?>" step="any" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label for="reg_select" class="">Date</label>
                                <input type="date" name="performed_date" id="performed_date" value="<?= $dialysis_data_info->performed_date; ?>" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Time</label>
                                <input type="time" name="performed_time" id="performed_time" value="<?= $dialysis_data_info->performed_time; ?>" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">HEP</label>
                                <input type="text" name="hep" id="hep" class="form-control" value="<?= $dialysis_data_info->hep; ?>">
                            </div>

                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="reg_select" class="">BP (mmHg)</label>
                                <input type="text" maxlength="10" name="bp" id="bp" class="form-control" value="<?= $dialysis_data_info->bp; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Pulse (b/min)</label>
                                <input type="text" maxlength="10" name="pulse" id="pulse" class="form-control" value="<?= $dialysis_data_info->pulse; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Diagnosis</label>
                                <input type="text" maxlength="100" name="diagnosis" id="diagnosis" class="form-control" value="<?= $dialysis_data_info->Diagnosis; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Access</label>
                                <input type="text" maxlength="50" name="access" id="access" class="form-control" value="<?= $dialysis_data_info->Access; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="reg_select" class="">Dialyzer</label>
                                <input type="text" maxlength="50" name="dialyzer" id="dialyzer" class="form-control" value="<?= $dialysis_data_info->Dialyzer; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">PCV</label>
                                <input type="number" maxlength="50" name="pcv" id="pcv" class="form-control" value="<?= $dialysis_data_info->PCV; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">Duration</label>
                                <input type="text" maxlength="100" name="duration" id="duration" class="form-control" value="<?= $dialysis_data_info->Duration; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reg_select" class="">SPO2</label>
                                <input type="text" maxlength="100" name="spo" id="spo" class="form-control" value="<?= $dialysis_data_info->SPO2; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form_sep">
                                    <label for="intradialysis_complication">Intradialysis Complication:</label>
                                    <select id="intradialysis_complication" name="intradialysis_complication" class="form-control">
                                        <option value="">-- Select Complication --</option>
                                        <option value="Hypertension" <?= ($dialysis_data_info->complication == 'Hypertension') ? 'selected' : '' ?>>Hypertension</option>
                                        <option value="Hypotension" <?= ($dialysis_data_info->complication == 'Hypotension') ? 'selected' : '' ?>>Hypotension</option>
                                        <option value="Hypoglycemia" <?= ($dialysis_data_info->complication == 'Hypoglycemia') ? 'selected' : '' ?>>Hypoglycemia</option>
                                        <option value="Hyperglycemia" <?= ($dialysis_data_info->complication == 'Hyperglycemia') ? 'selected' : '' ?>>Hyperglycemia</option>
                                        <option value="Fever" <?= ($dialysis_data_info->complication == 'Fever') ? 'selected' : '' ?>>Fever</option>
                                        <option value="Hypothermia" <?= ($dialysis_data_info->complication == 'Hypothermia') ? 'selected' : '' ?>>Hypothermia</option>
                                        <option value="Cramps" <?= ($dialysis_data_info->complication == 'Cramps') ? 'selected' : '' ?>>Cramps</option>
                                        <option value="Itching" <?= ($dialysis_data_info->complication == 'Itching') ? 'selected' : '' ?>>Itching</option>
                                        <option value="Headache" <?= ($dialysis_data_info->complication == 'Headache') ? 'selected' : '' ?>>Headache</option>
                                        <option value="Seizure" <?= ($dialysis_data_info->complication == 'Seizure') ? 'selected' : '' ?>>Seizure</option>
                                        <option value="Cough" <?= ($dialysis_data_info->complication == 'Cough') ? 'selected' : '' ?>>Cough</option>
                                        <option value="Diarrhoea" <?= ($dialysis_data_info->complication == 'Diarrhoea') ? 'selected' : '' ?>>Diarrhoea</option>
                                        <option value="Vomiting" <?= ($dialysis_data_info->complication == 'Vomiting') ? 'selected' : '' ?>>Vomiting</option>
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
                                        while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                            <option
                                                value="<?= $rwx['fullname']; ?>"
                                                <?= $rwx['fullname'] == $dialysis_data_info->Name_Nurse ? 'selected' : ''; ?>>
                                                <?= $rwx['fullname']; ?>
                                            </option>
                                    <?php endwhile;
                                    }

                                    ?>

                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form_sep">
                                    <label>Duty Shift</label>
                                    <select name="duty_shift" class="input-sm chosen-select" style="width:350px;">
                                        <option selected="selected" value="">--Select--</option>
                                        <option value="Morning" <?= $dialysis_data_info->Duty_Shift == 'Morning' ? 'selected' : ''; ?>>Morning</option>
                                        <option value="Night" <?= $dialysis_data_info->Duty_Shift == 'Night' ? 'selected' : ''; ?>>Night</option>

                                    </select>
                                </div>

                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <input type="hidden" name="fluid_loss" id="fluid_loss" class="form-control" value="<?php $dialysis_data_info->fluid_loss; ?>">
                            <div class="col-md-12">
                                <label for="reg_select" class="req">Dialysis Note</label>
                                <textarea name="dialysis_note" id="dialysis_note" cols="30" rows="5" class="form-control"><?= $dialysis_data_info->dialysis_note; ?> </textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="app_no" value="<?= $dialysis_data_info->app_no; ?>">
                    <input type="hidden" name="hospital_no" value="<?= $dialysis_data_info->hospital_no; ?>">
                    <input type="hidden" name="dialysis_token" id="dialysis_token" value="<?= $token; ?>" class="form-control" maxlength="10" required>
                    <input type="hidden" name="performed_by" id="performed_by" value="<?= $_SESSION['id']; ?>">
                    <?php if ($dialysisInfo->completed != 'yes'): ?>
                        <a href="#" style="float: left;">
                            <input type="checkbox" style="height: 17px; width: 17px; " name="completed" id="completed" value="yes"
                                <?= $dialysis_info->completed == 'yes' ? 'checked' : ''; ?>> <strong>
                                Mark as Complete</strong> </a>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" name="saveDialysisNote"> Save Data </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-danger" onclick="closeModal('editDialysisModal', 'data')">Close</button>
							-->



                </div>

            </form>
        </div>
    </div>
</div>