<?php
if ( $asPaidForAppointment || $adm_status == 3 ) {
    include_once( '_see_specialist_modal.php' );
}

if ( isset( $_POST[ 'saveConsultationBtn' ] ) ) {
    $error_status = 1;
            $error_msg = 'Error! Consultation note is not saved...';
    if(!empty(trim($_POST[ 'consultation_note' ]))){
        $specialty = $_POST[ 'service_and_id' ];
        // $consultation_note = '<h6>'.strtoupper($specialty).' CONSULTATION NOTES </h4>'. $_POST[ 'consultation_note' ];
        $consultation_note =  $_POST[ 'consultation_note' ];
    
        $error_status = 1;
        $error_msg = 'Oops! something went wrong...';
    
        $saveConsultations = saveToNotes( $db, $appointment_number, $hospital_no, $consultation_note, 'DR', 'CONS',  $_SESSION[ 'fullname' ], $specialty, $_SESSION[ 'id' ] );
        if ( $saveConsultations ) {
            $error_status = 2;
            $error_msg = 'Success! Consultation note is saved...';
        }
    }
   
}

$specialistRequests = $PatientApService->get(
    [ 'serv_group' => 'Consultation', 'cat_type' => 'Consultation', 'hospital_no' => $hospital_no, 'app_no' => $appointment_number ],
    true
);

?>


<div class = 'modal inmodal fade' id = 'consultationNoteModal' tabindex = '-1' role = 'dialog' aria-hidden = 'true' data-keyboard = 'false' data-backdrop = 'static'>
<div class = 'modal-dialog modal-lg' style = 'width: 50%;'>
<div class = 'modal-content'>
<div class = 'modal-header'>
<button type = 'button' class = 'close' data-dismiss = 'modal' aria-hidden = 'true'>×</button>
<h4 class = 'modal-title' id = ''>  Consultation Notes </h4>
</div>
<div class = 'modal-body' style = 'min-height: 300px'>

<div style = 'max-height: 280px;overflow-y:auto'>
<?php

$note = ' ';
$stmt = $db->prepare( "SELECT sn, notes FROM notes WHERE app_no = ? AND hospital_no = ? AND notes_type = 'CONS'  and created_by = ? order by sn desc " );
$stmt->execute( array( $appointment_number, $hospital_no, $_SESSION[ 'id' ] ) );
if ( $stmt->rowCount() > 0 ) {
    ?>
    <table class = 'table table-bordered'>
    <thead>
    <tr>
    <td>SN</td>
    <td>Notes</td>
    </tr>
    </thead>
    <tbody>
    <?php
    $sn = 0;
    $note = null;
    $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    foreach ( $rows as $key => $row ) {
        if ( $sn == 0 ) {
            $note =  $row[ 'notes' ];
            $sn++;
        } else {
            ?>
            <tr>
            <td><?php echo $sn++;
            ?></td>
            <td><?php echo $row[ 'notes' ];
            ?></td>
            </tr>
            <?php
        }
    }

    ?>
    </tbody>
    </table>
    <?php
}

// }



$cons_template = null;
$cons_template_id = null;
if(isset($_POST["load-cons-template"])){
   
    

    $cons_template_id = $_POST['template_id'];

    $stmt = $db->prepare("SELECT template FROM  services_templates  WHERE id = ? ");
    $stmt->execute(array($cons_template_id));
    if($stmt->rowCount() > 0){
        $cons_template_arr = $stmt->fetch(PDO::FETCH_ASSOC);
        $cons_template = $cons_template_arr['template'];
        $note = $cons_template;
    }
   
    
}

?>
</div>
<div class = 'well well-sm'>
    <div style="padding:0 5px"> 
<form action="<?php echo $editFormAction; ?>" method="post">
         <div class="row">
             <div class="col-md-10">
                      <b>Select Template:</b>
                <select name="template_id" id="template_id" class="input-sm chosen-select select-template-list" style="width:350px;" arial-data="<?= $hospital_no; ?>" >
                    <option selected="selected" value="">Search & Select Template</option>
                    <?php
                    $stmt = $db->prepare("SELECT st.id, st.service_cat,st.template_name FROM  services_templates st 
                    INNER JOIN serv_cat sc ON st.service_cat = sc.serv_cat_id  WHERE sc.serv_group = 'doctor' ");
                    $stmt->execute();
                    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($templates as $key => $template) {

                        if($cons_template != null){
                            ?>
                            <option value="<?= $template["id"]; ?>" <?= ($cons_template_id == $template["id"] ? 'selected': ''); ?> ><?= $template["template_name"]; ?></option>
                        <?php
                        }else{
                            ?>
                            <option value="<?= $template["id"]; ?>" ><?= $template["template_name"]; ?></option>
                        <?php
                        }
                    
                    }
                    ?>
                </select>
             </div>
             <div class="col-md-2 text-left">
                <br>
                <button class="btn btn-success btn-sm" name="load-cons-template">Display </button>
            </div>
         </div>
        </form>
                </div>
                <br>
<form action = "<?= $editFormAction; ?>" method = 'post'>

<div >
    <div class="row">
        <div class="col-md-12">
        <b>Select Service:</b>
<select class = 'form-control' name = 'service_and_id' id = 'service_and_id' required>
<?php
foreach ( $specialistRequests as $key => $specialistRequest ) {
    // echo '<option value = "'.$specialistRequest->sn.': '.$specialistRequest->item_services.'"> '.$specialistRequest->item_services.'</option>';
    echo '<option value = "'.$specialistRequest->item_services.'"> '.$specialistRequest->item_services.'</option>';
}
?>
</select>

<br>
<label for = 'consultation_note' class="text-left">Enter  Notes [below]:</label>
<textarea  id = 'pro_note' cols = '45' rows = '20' maxlength = '560' name = 'consultation_note' class = 'summernote' placeholder = 'Type Your Message Here' >
<?php echo (empty($note) ? ' &nbsp;&nbsp;  ': $note);
?>
</textarea>
<!-- <textarea class = 'form-control' name = 'consultation_note' id = 'consultation_note' cols = '30' rows = '10' required></textarea> -->
</div>

<br>

</div>
</div>
</div>
</div>
<div class="modal-footer">
        <button type = 'submit' class = 'btn btn-primary btn btn-sm' name = 'saveConsultationBtn' id = 'saveConsultationBtn' >Save</button>
            <button type="button" class="btn btn-danger " data-dismiss="modal">Minimise (-)</button>
      

</div>
</form>


<?php
if(isset($_POST["load-cons-template"])){
?>
<script>
        $(document).ready(()=>{
            $('#consultationNoteModal').modal('show');
        })
</script>
<?php

}

?>

<!-- AI Clinical Toolkit Widget -->
<script>
    window.currentHospitalNo = '<?= $hospital_no ?>';
    window.currentPatientId = '<?= $hospital_no ?>';
</script>

