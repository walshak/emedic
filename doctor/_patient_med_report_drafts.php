<?php
if(isset($_POST['load_med_report_drafts'])){
    include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');

$hospital_no = $_POST['hospital_no'];
  $med_report_draft_stmt = $db->prepare( "SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1'  AND action = 'save'  ORDER BY id DESC" );
            $med_report_draft_stmt->execute( array( $hosp_no ) );
}
?>
 <table class="table table-hover table-mail">
     <thead>
         <tr>
             <td></td>
             <td>Notes</td>
             <td>Date</td>
             <td>Doctor</td>
             <td></td>
         </tr>
     </thead>
    <tbody>
        <?php

        
            if($med_report_draft_stmt->rowCount()  > 0){
                $med_report_tasks  = $med_report_draft_stmt->fetchAll( PDO::FETCH_ASSOC );
                foreach ( $med_report_tasks as $key => $med_report_task ) {
                    
            ?>
            <tr class="unread">
                <td class="check-mail">
                    <input type="checkbox" class="i-checks" checked>
                </td>
                <td class="mail-subject"><a href="#"><?php echo substr($med_report_task['notes'], 0, 100);?></a></td>
                           <td class=""><?php echo dateFormat_($med_report_task['created_at']);?></td>
                <td ><a href="#"><?php echo $med_report_task['created_by_name'];?></a></td>
     
                <td class="text-right mail-date">
                    <a class="btn btn-success edit-med-report-button" href="med_report.php?hosp_no=<?= $hosp_no.'&data='.$med_report_task['id']; ?>&edit"><i class="fa fa-edit"></i></a>
                    <a class="btn btn-danger delete-med-report-button" href="med_report.php?hosp_no=<?= $hosp_no.'&data='.$med_report_task['id']; ?>&delete"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            <?php
                }
            }
        ?>


</tbody>
</table>