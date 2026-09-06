<div class="modal inmodal" id="discharge_patient_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Discharge Patient</h4>
            </div>
            <div class="modal-body" id="discharge_patient_body_">

            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="admit_patient_modal_nurse" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Initiate Admission / Observation</h4>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>">
                    <input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>">
                    <input type="hidden" name="created_by_name" value="<?php echo $_SESSION['fullname']; ?>">
                    <input type="hidden" name="created_by" value="<?php echo $_SESSION['id']; ?>">
                    
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Admission Type</label>
                            <select name="admit_type" class="form-control" required>
                                <option value="">- Select Type -</option>
                                <option value="admit_p">Standard Inpatient Admission</option>
                                <option value="admit_o">Admit to Observation (24 Hrs)</option>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Reason for Admission</label>
                            <input type="text" name="reason_adm" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                    <button type="submit" name="add_admit_request" class="btn btn-primary">Submit Admission Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
