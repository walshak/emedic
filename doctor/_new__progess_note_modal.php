<?php

?>


<div class="modal inmodal fade" id="newProgressNoteModal_<?= $hospital_no; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
                                <div class="modal-dialog modal-lg" style="width: 65%;">
                                    <div class="modal-content">
                                        <form action="<?php echo $editFormAction;?>"  method="post"  id="new_progress_note_form_<?= $hospital_no; ?>">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h4 class="modal-title" id="">New Note [<?= $hospital_no; ?>]</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="well well-sm">
                                                    <label for="select_template">Select Note Template</label>
                                                    <select name="template_<?= $hospital_no; ?>" id="template_<?= $hospital_no; ?>" class="form-control select-template-list" arial-data="<?= $hospital_no; ?>" required>
                                                    
                                                        <option  value="blank"> Blank Note </option>
                                                        
                                                    </select>
                                                </div>
                                                <div id="pro_note_<?= $hospital_no; ?>_wrap">
                                                    
                                                    <textarea name="pro_note_<?= $hospital_no; ?>" class="summernote" id="pro_note_<?= $hospital_no; ?>" cols="45" rows="5" maxlength="160" placeholder="Type Your Message Here">
                                                            <div>
                                                                <h4>Focus:</h4>
                                                                <h4>Notes:</h4>
                                                                
                                                            </div>
                                                    </textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                                            <input type="hidden" name="appointment_number" value="<?= $admission_app_no; ?>">
                                            <input type="hidden" name="save-new-note" value="<?= $hospital_no; ?>">
                                            <input type="hidden" name="loadPatientWardRoundBtn" value="loadPatientWardRoundBtn">
                                            <div class="modal-footer">
                                                <button class="btn  btn-primary" name="save_progress_note_button">Save Note <i class="fa fa-save"></i></button>
                                                <button type="button" class="btn btn-danger " data-dismiss="modal">Minimise (-)</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

<!-- AI Clinical Toolkit Widget -->
<script>
    if (!window.currentHospitalNo) window.currentHospitalNo = '<?= $hospital_no ?>';
    if (!window.currentPatientId) window.currentPatientId = '<?= $hospital_no ?>';
</script>
