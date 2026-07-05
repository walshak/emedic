<?php
$templates = [];
 $template_stmt = $db->prepare("SELECT t.* FROM services_templates t INNER JOIN serv_cat c ON t.service_cat = c.serv_cat_id WHERE t.status = '1' and (t.category = 'Medical Services' or c.serv_cat_name = 'Transplant')");
        $template_stmt->execute();

 if ($template_stmt->rowCount() > 0) {
            $templates = $template_stmt->fetchAll(PDO::FETCH_ASSOC);
        }


    if(isset($_POST['save-transplant-consultation-note-btn']))  {
        $consulation_notes = $_POST["consulation_notes"];
        $template_id = $_POST["template_id"];
        $transplant_id = $_POST["transplant_id"];
        $main_surgeon = $_POST["consultant_name"];
        $transplant_outcome = $_POST["transplant_outcome"];
         $performed_date = $_POST["performed_date"];
     
        
              $error_status = 1;
             $error_msg = 'Error : Something went wrong...';
        

        $patient_transplant_info = $Transplant->get(['id' => $transplant_id]);
        $app_no = $patient_transplant_info->app_no;
        $hospital_no = $patient_transplant_info->hospital_no;

        $stmt = $db->prepare("UPDATE transplants SET performed_date=?, transplant_outcome=?, main_surgeon=? WHERe id=? ");
        $save_transplant=  $stmt->execute(array( $performed_date, $transplant_outcome, $main_surgeon, $transplant_id));

        $trans_note_stmt = $db->prepare("SELECT * from  transplant_notes WHERE template_id=? AND created_by = ? AND transplant_id = ? AND status = '1' ");
        $trans_note_stmt->execute(array( $template_id, $_SESSION["id"], $transplant_id));
        if ($trans_note_stmt->rowCount() == 0) {
             $stmt = $db->prepare( 'INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by)  VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?)' );
                $save_note = $stmt->execute(
                    array(
                        $app_no,
                        $hospital_no,
                        $consulation_notes,
                        'DR',
                        'CONS',
                        $_SESSION['fullname'],
                        '',
                        $_SESSION["id"]
                    )
                );
            if($save_note == true){
                $id = $db->lastInsertId();
                $stmt = $db->prepare("INSERT INTO transplant_notes (template_id, notes_id, transplant_id, created_by, created_by_name) VALUES (?, ?, ?, ?, ?) ");
                $stmt->execute(array($template_id, $id, $transplant_id, $_SESSION['id'], $_SESSION['fullname']));
            }
        }else{
              $transplant_notes_row = $trans_note_stmt->fetch(PDO::FETCH_ASSOC);
              $notes_id = $transplant_notes_row['notes_id'];

                 $stmt = $db->prepare("UPDATE notes SET notes=? WHERe sn=? ");
                $save_note=  $stmt->execute(array( $consulation_notes, $notes_id));
        }

          if($save_note == true){
              $error_status = 2;
             $error_msg = 'Success : Notes is saved...';
        }
    }

     if(isset($_POST['update-transplant-consultation-note-btn']))  {
        $edit_consulation_notes = $_POST["edit_consulation_notes"];
        
        $transplant_id = $_POST["transplant_id"];
        $main_surgeon = $_POST["consultant_name"];
        $transplant_outcome = $_POST["transplant_outcome"];
         $performed_date = $_POST["performed_date"];
        $stmt = $db->prepare("UPDATE transplants SET performed_date=?, transplant_outcome=?, main_surgeon=? WHERe id=? ");
        $save_transplant=  $stmt->execute(array( $performed_date, $transplant_outcome, $main_surgeon, $transplant_id));
     

        $sn = $_POST["sn"];

          $stmt = $db->prepare("UPDATE notes SET notes=? WHERe sn=? ");
                $save_note=  $stmt->execute(array( $edit_consulation_notes, $sn));
                 if($save_note == true){
              $error_status = 2;
             $error_msg = 'Success : Notes is saved...';
        }

     }

?>
<div class="modal inmodal fade" id="consultation_notes_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" style="min-height: 500px;">
        <div class="modal-content">
                <form action="<?= $editFormAction; ?>" method="post" id="pre_opt_note_form">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Transplant Notes</h4>
			</div>
                <div class="modal-body" >  
                
                        <div>
                             <label for="select_template">Select Note Template</label>
                         <select class="input-sm chosen-select " 
                         onchange="load_templates(this.value, 'consulation_notes')"
                         style="width:350px;" 
                         name="template_id"
                          >
                             <option  value="blank"> Blank Note </option>
                             <?php
                            
       
                                foreach ($templates as $key => $template) {
                                ?>
                                 <option  value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                             <?php
                                }
                                ?>
                         </select>
                         <div id="pre-opt-temp-load-status"></div>
                        </div>
                        <div>
                            <br>
                             <label for="select_template"> Enter Note Below: </label>
                            <div id="consulation_notes_wrap"><textarea name="consulation_notes" id="consulation_notes" cols="30" rows="10" class="summernote" style="margin-top:0px"> </textarea></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label for="select_template"> Transplant Outcome: </label>
                                <select name="transplant_outcome" id="transplant_outcome" class="form-control" style="font-size: 14px;">
                                    <option value="">Select Transplant Outcome</option>
                                    <option value="Successfull">Successfull </option>
                                    <option value="Not Successfull">Not Successfull </option>
                                    <option value="Not Applicable">Not Applicable </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                            <div>
                                                <label for="reg_select" class="req">Main Surgeon/Doctor/Consultant</label>
                                                <input type="text"  class="typeahead form-control  " data-provide="typeahead" id="typeahead_search_specialist" placeholder="Search for Doctor/Specialist" autocomplete="off">
                                                <input type="hidden" name="consultant_id" id="typeahead_search_specialist_id">
                                                <input type="hidden" name="consultant_name" id="typeahead_search_specialist_name">
                                                </div>
                            </div>
                            <div class="col-md-4">
                            <label for="select_template"> Performed Date:</label>
                            <input type="date" name="performed_date" id="performed_date" class="form-control">
                            </div>
                        </div>
                        

                        
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hospital_no" value="<?php echo $hospital_no;?>">
                    <input type="hidden" name="transplant_id" value="<?php echo $transplant_id;?>">
                    <button class="btn btn-success" type="submit" name="save-transplant-consultation-note-btn" >Save </button>
                    <button class="btn btn-danger" data-dismiss="modal"  >Close </button>
                </div>
                </form>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="edit_consultation_notes_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" style="min-height: 500px;">
        <div class="modal-content" >
                <form action="<?= $editFormAction; ?>" method="post" >
                <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""> Transplant Note  </h4>
			</div>
                <div class="modal-body" >  
                
                <div id="edit_consultation_notes_modal_content">
                                <?php
                                  $patient_transplant_info = $Transplant->get(['id' => $transplant_id]);
                                ?>
                </div>
                
                <div>
                   <div class="row">
                            <div class="col-md-4">
                                <label for="select_template"> Transplant Outcome: </label>
                                <select name="transplant_outcome" id="transplant_outcome" class="form-control" style="font-size: 14px;">
                                    <option value="">Select Transplant Outcome</option>
                                    <option value="Successful" <?= $patient_transplant_info->transplant_outcome == 'Successful' ? 'selected':'';?>  >Successful </option>
                                    <option value="Not Successful" <?= $patient_transplant_info->transplant_outcome == 'Not Successful' ? 'selected':'';?>>Not Successful </option>
									<option value="Not Successful" <?= $patient_transplant_info->transplant_outcome == 'Not Applicable' ? 'selected':'';?>>Not Applicable </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                            <div>
                                                <label for="reg_select" class="req">Consultant</label>
                                                <input type="text"  class="typeahead form-control  " value="<?= $patient_transplant_info->main_surgeon;?>" data-provide="typeahead" id="typeahead_search_specialist_edit" placeholder="Search for Doctor/Specialist" autocomplete="off">
                                                <input type="hidden" name="consultant_id" id="typeahead_search_specialist_id_edit">
                                                <input type="hidden" name="consultant_name" value="<?= $patient_transplant_info->main_surgeon;?>" id="typeahead_search_specialist_name_edit">
                                                </div>
                            </div>
                            <div class="col-md-4">
                            <label for="select_template"> Performed Date: </label>
                            <input type="date" name="performed_date" value="<?= date('Y-m-d', strtotime(''.$patient_transplant_info->performed_date));?>" id="performed_date" class="form-control">
                            </div>
                        </div>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        
                   </div>
                         
                    
                </div>
                <div class="modal-footer">
                
                    <button class="btn btn-success" type="submit" name="update-transplant-consultation-note-btn" >Save </button>
                    <button class="btn btn-danger" data-dismiss="modal"  >Close </button>
                </div>
               
                 
                </form>
        </div>
    </div>
</div>


<br>

<div >


<?php
	
$medication_hx_stmt = $db->prepare( "
SELECT n.*, tn.id tn_id  
FROM transplant_notes tn 
INNER JOIN notes n ON tn.notes_id = n.sn 
WHERE tn.transplant_id = ? and tn.status = '1' and n.hospital_no='$hospital_no'" );
$medication_hx_stmt->execute( array( $transplant_id ) );

if ( $medication_hx_stmt->rowCount() > 0 ) {
    $mdication_hx  = $medication_hx_stmt->fetchAll( PDO::FETCH_ASSOC );

    foreach ( $mdication_hx as $key => $mdication_ ) {
        ?>
        <div class = 'ibox med-hx-ibox-content med-hx-ibox-content-<?php echo $mdication_[ 'notes_type' ];?>'>
        <div class = 'ibox-title'>
            <?php
                if($_SESSION['id'] == $mdication_[ 'created_by' ] ){
                    ?>
                        <h5> 
            
            Consultation Notes <?php  echo ' | <span style="color: #ccc">' . formatDateTime_( $mdication_[ 'date_entry' ] ) . '</span>';?></h5>
            <button class="btn btn-success btn-xs  open-edit-trans-cons-note-btn" style="margin-left: 20px" arial-modal="edit_consultation_notes_modal" arial-id="<?php echo $mdication_['tn_id'];?>"  <?php if($performed_date!=''){?> disabled <?php } ?>><i class="fa fa-edit"></i>&nbsp;Edit</button>
                    <?php
                }else{
  ?>
                        <h5> 
           
            Consultation Notes <?php  echo ' | <span style="color: #ccc">' . formatDateTime_( $mdication_[ 'date_entry' ] ) . '</span>';?></h5>
                    <?php
                }
            ?>
        
        </div>
        <div class = 'ibox-content med-ibox-content-area' id="operation_note_content_area">
        <?php echo $mdication_[ 'notes' ];
        ?>
        <div style = 'margin-top:10px;border-top: 1px solid #f1f1f1'>
        <br>
        <small><b>Captured by:  <?php echo $mdication_[ 'prepared_by' ];
        ?></b></small>
        </div>
        </div>
        </div>
        <?php
    }
}

?>
</div>
<script>
    $(document).ready(function(){

        $('#operation_notes').html($('#operation_note_content_area').html())
        var search_specialist_result = [];
    $('#typeahead_search_specialist').typeahead({
        source: function(query, query_response) {
						
            $.ajax({
                url: "controllers/specialist.php",
                method: "POST",
                data: {
                    search_specialist: true,
                    action: 'search_specialist',
                    input_text: $('#typeahead_search_specialist').val()
                },
                dataType: "json",
                success: function(data) {   
                   
                    search_specialist_result = data;
                    query_response($.map(data, function(item) {
                       
                        return item.name;

                    }));
                }

            })
        },
        updater: function(item) {
            search_specialist_result.forEach(element => {
                if(element.name == item){
                    $('#typeahead_search_specialist_id').val(element.id)
                    $('#typeahead_search_specialist_name').val(element.name)
                    return item;
                }
            });
            return item
        }
    });



    $('#typeahead_search_specialist_edit').typeahead({
        source: function(query, query_response) {
						
            $.ajax({
                url: "controllers/specialist.php",
                method: "POST",
                data: {
                    search_specialist: true,
                    action: 'search_specialist',
                    input_text: $('#typeahead_search_specialist_edit').val()
                },
                dataType: "json",
                success: function(data) {   
                   
                    search_specialist_result = data;
                    query_response($.map(data, function(item) {
                       
                        return item.name;

                    }));
                }

            })
        },
        updater: function(item) {
            search_specialist_result.forEach(element => {
                if(element.name == item){
                    $('#typeahead_search_specialist_id_edit').val(element.id)
                    $('#typeahead_search_specialist_name_edit').val(element.name)
                    return item;
                }
            });
            return item
        }
    });
    });
	
</script>