<?php
if(isset($_POST['save_med_report']) || isset($_POST['copy_from_med_hx']) || isset($_POST['get_med_report']) || isset($_POST['get_med_report_counter'])){
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
}


if(isset($_POST['delete_med_report'])){
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
          header('Content-Type: application/json');
         $id = $_POST['id'];

         $status = 401;
        $message = 'Error: something went wrong.';
        $data = null;

         $stmt = $db->prepare("UPDATE medical_report_task SET  status = '0' WHERE id = ?  " );
        $save =  $stmt->execute( array(  $id) );

          if($save){
            $status = 200;
            $message = 'Success: Saved...';
            $data = null;
            }
          echo json_encode(["message" => $message, "status" => $status, "data" => $data]);
    exit;

}
if(isset($_POST['get_med_report_counter'])){
    header('Content-Type: application/json');
 $hospital_no = $_POST['hospital_no'];
    $med_report_draft_stmt = $db->prepare( "SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1'  AND action = 'draft'  ORDER BY id DESC" );
    $med_report_draft_stmt->execute( array( $hospital_no ) );

    $med_report_sent_stmt = $db->prepare( "SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1'  AND action = 'save'  ORDER BY id DESC" );
    $med_report_sent_stmt->execute( array( $hospital_no ) );

    echo json_encode(['sent' => $med_report_sent_stmt->rowCount(), 'draft' => $med_report_draft_stmt->rowCount()]);
    exit;

}


if(isset($_POST['get_med_report'])){
        $id = $_POST['id'];
    $stmt = $db->prepare( "SELECT * FROM medical_report_task WHERE id=?" );
    $stmt->execute( array( $id ) );
    if($stmt->rowCount() >  0){
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
         echo $row['notes'];
         exit;
    }
exit;
}


if(isset($_POST['save_med_report'])){
    header('Content-Type: application/json');
    $med_report_notes = $_POST['med_report_notes'];
    $hospital_no = $_POST['hospital_no'];
    $action = $_POST['action'];

    $status = 401;
    $message = 'Error: something went wrong.';
    $data = null;
    
    $stmt = $db->prepare( "SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1' AND created_by = ? AND action != 'printed' " );
    $stmt->execute( array( $hospital_no, $_SESSION['id'] ) );
    if($stmt->rowCount()  == 0){
            $stmt = $db->prepare("INSERT INTO medical_report_task  (hospital_no, notes, created_by,action, created_by_name, status) VALUES (?, ?, ?, ?, ?, '1') " );
            $save = $stmt->execute( array( $hospital_no, $med_report_notes, $_SESSION['id'], $action, $_SESSION['fullname']) );
    }else{
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $db->prepare("UPDATE medical_report_task SET  notes = ?, action = ?, created_by_name = ? WHERE id = ?  " );
        $save =  $stmt->execute( array(  $med_report_notes, $action, $_SESSION['fullname'],  $row['id'] ) );
    }

    if($save){
    $status = 200;
    $message = 'Success: Saved...';
    $data = null;
    }
echo json_encode(["message" => $message, "status" => $status, "data" => $data]);
exit;
}


if(isset($_POST['copy_from_med_hx'])){
$hospital_no = $_POST['hospital_no'];

$medication_hx_stmt = $db->prepare( "SELECT * FROM notes WHERE hospital_no = ? and status = '1' ORDER BY date_entry DESC LIMIT 250" );
$medication_hx_stmt->execute( array( $hospital_no ) );

$med_copy_sn = 1;
if ( $medication_hx_stmt->rowCount() > 0 ) {
    ?>
    <div class="light-card full-height-scroll" style="overflow: hidden; width: auto; height: 100%;" style="padding: 20px" >
    <?php
   

    while ( $mdication_ = $medication_hx_stmt->fetch( PDO::FETCH_ASSOC ) ) {
        ?>
        <div class = 'ibox light-card' id="med-to-copy-wrap-<?php echo $med_copy_sn;?>">
        
        <div class = 'ibox-content ' >
        <div id="med-to-copy-content-<?php echo $med_copy_sn;?>">
         <?php
        if ( $mdication_[ 'notes_type' ] == 'C' ) {
            echo 'Complains';
        } else  if ( $mdication_[ 'notes_type' ] == 'D' ) {
            echo 'Diagnosis/Findings';
        } else  if ( $mdication_[ 'notes_type' ] == 'note' ) {
            echo 'Progress Note';
        } else  if ( $mdication_[ 'notes_type' ] == 'plan' ) {
            echo 'Med. Plan';
        } else  if ( $mdication_[ 'notes_type' ] == 'PH' ) {
            echo 'Physical Examinations';
        } else  if ( $mdication_[ 'notes_type' ] == 'CONS' ) {
            echo 'Consultation Notes';
        }  else  if ( $mdication_[ 'notes_type' ] == 'mgt' ) {
            echo 'Management';
        }
        else {
            echo 'Uncategorised';
        }

        echo ' | <span style="color: #ccc">' . formatDateTime_( $mdication_[ 'date_entry' ] ) . '</span>';

        ?>
        <?php echo $mdication_[ 'notes' ]; ?></div>
         <button class="btn btn-sm btn-success copy-med-hx-button" style="float:right" arial-data="<?php echo $med_copy_sn;?>"><i class="fa fa-copy"></i> Click to Copy</button>
        </div>
        <div>
            
        </div>
        </div>
        <?php
        $med_copy_sn++;
    }

    ?>
    </div>
    <?php
}


    exit;
}

?>