
 <?php

    $medication_hx_stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no = ?  AND notes_type = 'note' AND status = '1' ORDER BY date_entry  DESC LIMIT 250");
    $medication_hx_stmt->execute(array($hospital_no));

    if ($medication_hx_stmt->rowCount() > 0) {
        $mdication_hx  = $medication_hx_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($mdication_hx as $key => $mdication_) {
    ?>
         <div class="ibox">
             <div class="ibox-title">
                 <div class="row">
                     <div class="col-md-8">
                         <h5> <?php echo ' Progress Note | <span style="color: #ccc">' . formatDateTime_($mdication_["date_entry"]) . '</span>'; ?> </h5>
                     </div>
                     <div class="col-md-4">
                         <h5 class="text-right">
                             <?php

                                if ($admission_app_no == $mdication_["app_no"]) {
                                    $editBtn = '<button class="btn btn-sm btn-success btn-secondary" style="float:right" data-toggle="modal" data-target="#editProgressNoteModal_' . $mdication_["sn"] . '"> <i class="fa fa-edit"></i> Edit Note </button>';
                                    // $newNoteBtn .= '<button class="btn btn-sm btn-primary"> <i class="fa fa-file"></i> New Note </button>';
                                    echo ' ' . $editBtn . '  ';
                                }
                                ?>

                         </h5>
                     </div>
                 </div>
             </div>
             <div class="ibox-content" id="progress_note_content_<?= $mdication_["sn"]; ?>">
                 <div><?= $mdication_["notes"]; ?></div>
                 <p><small><b>Captured by: <?php echo $mdication_['prepared_by'];
                                                ?></b></small></p>
                        
             </div>
         </div>

         <!------  EDIT MODAL ---------------->
         <div class="modal inmodal fade" id="editProgressNoteModal_<?= $mdication_["sn"]; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
             <div class="modal-dialog modal-lg" style="width: 65%;">
                 <div class="modal-content">
                     <form action="<?php echo $editFormAction;?>" method="post" >
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                             <h4 class="modal-title" id="">Edit Note</h4>
                         </div>
                         <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                         <input type="hidden" name="update-note" value="<?= $hospital_no; ?>">
                         <input type="hidden" name="id" value="<?= $mdication_["sn"]; ?>">
                         <div class="modal-body" id="editProgressNoteModal_<?= $mdication_["sn"]; ?>_wrap">
                             <textarea name="pro_note" id="pro_note_edit_<?= $mdication_["sn"]; ?>" cols="45" rows="5" maxlength="160" class="summernote" placeholder="Type Your Message Here"><?= $mdication_["notes"]; ?> </textarea>
                         </div>
                         <div class="modal-footer">
                         
                             <button type="submit" class="btn  btn-primary" name="update_progress_note_button">Save Note <i class="fa fa-save"></i></button>
                             <!-- <button class="btn  btn-primary" onclick="updateProgressNote(<?= $mdication_['sn']; ?>)">Save Note <i class="fa fa-save"></i></button> -->
                             <button type="button" class="btn btn-danger " data-dismiss="modal">Close (x)</button>
                         </div>
                     </form>
                 </div>
             </div>
         </div>


 <?php
        }
    }else{
        echo '<h6 class="text-center"> No progress note history found </h6>';
    }
