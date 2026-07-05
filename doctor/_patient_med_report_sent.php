<?php
if (isset($_POST['load_med_report_sent'])) {
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');
}

?>
<table class="table table-hover table-bordered table-hover table-active" with="100%">
    <thead>
        <tr>
            <td></td>
            <td>Notes</td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        <?php

        $med_report_sql_string = '';
        $canSeeMedicalAssesment = $_SESSION['rights'] === 'MD' ? true : false;
        if ($canSeeMedicalAssesment === false) {
            $med_report_sql_string .= " AND report_type != 'Medical Assessment' ";
        }
        $hospital_no = isset($_POST['hospital_no']) ? $_POST['hospital_no'] :  $hosp_no;
        $med_report_sent_stmt = $db->prepare("SELECT * FROM medical_report_task WHERE hospital_no = '$hosp_no' AND status = '1'    $med_report_sql_string  ORDER BY id DESC");
        $med_report_sent_stmt->execute();
        if ($med_report_sent_stmt->rowCount()  > 0) {
            $med_report_tasks  = $med_report_sent_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($med_report_tasks as $key => $med_report_task) {

        ?>
                <tr class="unread">
                    <td class="check-mail">
                        <input type="checkbox" class="i-checks" checked>
                    </td>
                    <td class="mail-subject"><a href="#"><?php echo substr($med_report_task['notes'], 0, 100); ?></a></td>
                    <td><a href="#"><?php echo $med_report_task['action'] == 'Printed' ? '<span class="badge badge-primary">Printed.</span>' : '<span class="badge badge-pf">Pending...</span>'; ?></a></td>
                    <td class=""><?php echo dateFormat_($med_report_task['created_at']); ?></td>
                    <td><a href="#"><?php echo $med_report_task['created_by_name']; ?></a></td>

                    <td class="text-right mail-date">
                        <?php if ($med_report_task['action'] != 'Printed'): ?>
                            <a class="btn btn-success edit-med-report-button" href="med_report.php?hosp_no=<?= $hosp_no . '&data=' . $med_report_task['id']; ?>&edit"><i class="fa fa-edit"></i></a>
                            <a href="preview_med_report.php?token=<?= base64_encode("medical_report_id-" . $med_report_task['id']); ?>" class="btn  btn-primary" target="_BLANK"><i class="fa fa-print"></i> Preview & Print</a>
                            <a class="btn btn-danger delete-med-report-button" href="med_report.php?hosp_no=<?= $hosp_no . '&data=' . $med_report_task['id']; ?>&delete"><i class="fa fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
        <?php
            }
        }
        ?>


    </tbody>
</table>