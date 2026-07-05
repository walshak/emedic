<?php
$links = '';
$templates = [];
$template_stmt = $db->prepare("SELECT id, template_name, template FROM services_templates WHERE status = '1'  ");
$template_stmt->execute();
if ($template_stmt->rowCount() > 0) {
    $templates = $template_stmt->fetchAll(PDO::FETCH_ASSOC);
}



?>

<div class="modal inmodal fade" id="newProgressNoteModal_<?= $hospital_no; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 65%;">
        <div class="modal-content">
            <form action="#" method="post" onsubmit="return false" id="new_progress_note_form_<?= $hospital_no; ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">New Note [<?= $hospital_no; ?>]</h4>
                </div>
                <div class="modal-body">
                    <div class="well well-sm">
                        <label for="select_template">Select Note Template</label>
                        <select name="template_<?= $hospital_no; ?>" id="template_<?= $hospital_no; ?>" class="input-sm chosen-select select-template-list" style="width:350px;" arial-data="<?= $hospital_no; ?>" required>
                            <option selected="selected" value="">Search & Select Template</option>
                            <option value="blank"> Blank Note </option>
                            <?php
                            foreach ($templates as $key => $template) {
                            ?>
                                <option value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div id="pro_note_<?= $hospital_no; ?>_wrap">
                        <!-- <textarea name="pro_note" id="pro_note_" cols="45" rows="5" maxlength="160" class="ckeditor" placeholder="Type Your Message Here"></textarea> -->
                        <textarea name="pro_note" id="pro_note_<?= $hospital_no; ?>" cols="45" rows="5" maxlength="160" placeholder="Type Your Message Here">
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
                <div class="modal-footer">

                    <button class="btn  btn-primary" onclick="save_progress_note('<?= $hospital_no; ?>')">Save Note <i class="fa fa-save"></i></button>
                    <button type="button" class="btn btn-danger " data-dismiss="modal">Minimise (-)</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var templates = <?php echo json_encode($templates); ?>;

    $(document).on('change', '.select-template-list', function() {


        let hosp_no = $(this).attr('arial-data');
        let template_id = $(this).val();

        if (template_id == "blank") {
            $('#pro_note_' + hosp_no + '_wrap').html('');
            $('#pro_note_' + hosp_no + '_wrap').html('<textarea name="pro_note_' + hosp_no + '" id="pro_note_' + hosp_no + '" cols="45" rows="5" maxlength="160" class="summernote" placeholder="Type Your Message Here"></textarea>');
            $('#pro_note_' + hosp_no).summernote();
        } else {
            templates.forEach((elem, index) => {

                if (elem.id == template_id) {
                    $('#pro_note_' + hosp_no + '_wrap').html('');
                    $('#pro_note_' + hosp_no + '_wrap').html('<textarea name="pro_note_' + hosp_no + '" id="pro_note_' + hosp_no + '" cols="45" rows="5" maxlength="160" class="summernote" placeholder="Type Your Message Here">' + elem.template + '</textarea>');
                    $('#pro_note_' + hosp_no).summernote();
                    console.log(elem.template);
                }
            })
        }


    });
</script>