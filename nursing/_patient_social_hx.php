<?php

$show_edit_social_hx_modal = false;
if ( isset( $_POST[ 'save_social_hx_btn' ] ) ) {
    $social_hx_notes = $_POST[ 'social_hx_notes' ];
    $hospital_no = $_POST[ 'hospital_no' ];
    $app_no = $_POST[ 'app_no' ];

    $error_status = 1;
    $error_msg = 'Oops! something went wrong...';

    $save_allergy_stmt = $db->prepare( 'INSERT INTO c_d_remarks (complain, app_no, hospital_no, cat_type, prepared_by, created_by)  VALUES (?, ?, ?, ?, ?, ?) ' );
    $save = $save_allergy_stmt->execute( array( $social_hx_notes, $app_no, $hospital_no, 'SH', $_SESSION[ 'fullname' ], $_SESSION[ 'id' ] ) );

    if ( $save == true ) {
        $error_status = 2;
        $error_msg = 'Success: Social Histor saved';
        $show_edit_social_hx_modal = true;
    } else {
        $show_edit_social_hx_modal = true;
    }
}

if ( isset( $_POST[ 'update_social_hx_btn' ] ) ) {

    $social_hx_notes = $_POST[ 'social_hx_notes' ];
    // $hospital_no = $_POST[ 'hospital_no' ];
    $app_no = $_POST[ 'app_no' ];
    $social_hx_sn = $_POST[ 'sn' ];

    $error_status = 1;
    $error_msg = 'Oops! something went wrong...';

    $update_allergy_stmt = $db->prepare( 'UPDATE c_d_remarks SET complain = ?, updated_at = now(), updated_by = ? WHERE sn=?' );
    $update = $update_allergy_stmt->execute( array( $social_hx_notes, $_SESSION[ 'id' ], $social_hx_sn ) );

    if ( $update == true ) {
        $error_status = 2;
        $error_msg = 'Success: Social History saved';
        $show_edit_social_hx_modal = true;
    } else {
        $show_edit_social_hx_modal = true;
    }
}

$stmt = $db->prepare( "SELECT * FROM c_d_remarks WHERE hospital_no=? and cat_type='SH' AND status = '1' ORDER BY sn DESC LIMIT 1" );
$stmt->execute( array( $hospital_no ) );
$social_hx_num_rows = $stmt->rowCount();

?>

<h2>Social History</h2>

<?php if ( $social_hx_num_rows > 0 ) {
    ?>
    <a href = '#' id="edit-social-hx-btn">[ Edit Social Hx ]</a> |
    <a href = '#' class = 'show-social-hx-btn'> [ Show Social Hx ]</a>
    <a href = '#' class = 'hide-social-hx-btn' style = 'display:none'> [ Hide Social Hx ] </a>
    <?php
    $social_hx_row = $stmt->fetch( PDO::FETCH_ASSOC );

} else {
   // if ( $asPaidForAppointment || $adm_status == 3 ) {
        if($_SESSION['rights'] == 'DR'){
        ?>
        <a href = '#' id="add-social-hx-btn">[ Add Social Life ]</a> 
        <?php
        }
   /// }
}
?>

<hr>

<?php

if ( $social_hx_num_rows > 0 ) {
    $n = 1;
    echo '<div id="social-hx-wrap" style="display:none">'.$social_hx_row[ 'complain' ] . '<br>Captured by: ' . $social_hx_row[ 'prepared_by' ].'</div>';
    ?>

    <hr>
    <div class = 'modal inmodal fade' id = 'EditPatientSocialHxModal' tabindex = '-1' role = 'dialog' aria-hidden = 'true' data-keyboard = 'false' >
    <div class = 'modal-dialog modal-lg' style = 'width: 900px;'>
    <div class = 'modal-content'>
    <div class = 'modal-header'>
    <button type = 'button' class = 'close' data-dismiss = 'modal' aria-hidden = 'true'>×</button>
    <h4 class = 'modal-title' id = ''>Edit  Patient Social History</h4>
    </div>
    <div class = 'modal-body' style = 'min-height: 300px'>
    <form action = "<?php echo $editFormAction; ?>" method = 'POST'>
    <h3>Describe all Social Life here</h3>
    <textarea cols = '30' rows = '10' class = 'form-control summernote' name = 'social_hx_notes'><?php echo $social_hx_row[ 'complain' ];
    ?></textarea>
    <hr>
    <div class = 'text-center'>
    <input type = 'hidden' name = 'hospital_no' value = "<?php echo $social_hx_row[ 'hospital_no' ]; ?>">
    <input type = 'hidden' name = 'app_no' value = "<?php echo $social_hx_row[ 'app_no' ]; ?>">
    <input type = 'hidden' name = 'sn' value = "<?php echo $social_hx_row[ 'sn' ]; ?>">
    <button class = 'btn btn-primary btn-full' name = 'update_social_hx_btn'>Save </button>
    </div>
    </form>
    </div>
    </div>
    </div>
    </div>

    <?php
}

?>



<div class = 'modal  fade' id = 'newPatientSocialHxModal' tabindex = '-1' role = 'dialog' aria-hidden = 'true' data-keyboard = 'false' >
<div class = 'modal-dialog modal-lg' style = 'width: 900px;'>
<div class = 'modal-content'>
<form action = "<?php echo $editFormAction; ?>" method = 'POST'>
    <div class = 'modal-header'>
        <button type = 'button' class = 'close' data-dismiss = 'modal' aria-hidden = 'true'>×</button>
        <h4 class = 'modal-title' id = ''>Add Patient Social History</h4>
    </div>
    <div class = 'modal-body' style='max-height:300px;overflow:auto'>
            <div >
            <h3>Describe all social life here</h3>
            <textarea  class = 'form-control summernote' name = 'social_hx_notes'><?php include_once( '_patient_social_hx_template.php' );
            ?></textarea>
          
            <input type = 'hidden' name = 'hospital_no' value = "<?php echo $hospital_no; ?>">
            <input type = 'hidden' name = 'app_no' value = "<?php echo $appointment_number; ?>">
            </div>
            <div class="text-right">
                <button class = 'btn btn-primary ' name = 'save_social_hx_btn'>Save </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
        </div>
        
        </form>
    </div>
</div>
</div>

<script>
    $(document).on('click', '#add-social-hx-btn', function(){ 
     $( '#newPatientSocialHxModal' ).modal( 'show' );
});

$(document).on('click', '#edit-social-hx-btn', function(){ 
      $( '#EditPatientSocialHxModal' ).modal( 'show' );
});

$(document).on('click', '.show-social-hx-btn', function() {
                $('#social-hx-wrap').slideToggle('slow');
                $('.hide-social-hx-btn').fadeToggle('fast');
                $('.show-social-hx-btn').fadeToggle('fast');
            });

            $(document).on('click', '.hide-social-hx-btn', function() {
                $('#social-hx-wrap').slideToggle('slow');
                $('.hide-social-hx-btn').fadeToggle('fast');
                $('.show-social-hx-btn').fadeToggle('fast');
            });
</script>
