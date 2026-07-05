<?php
if (isset($_POST['ajaxPosting'])) {
    session_start();
    header('Content-Type: application/json');
    include("../../Connections/Conn.php");
    include('../objects.php');
    include('../helpers.php');

    if (isset($_POST['cancelReminder'])) {

        $updateStmt = $db->prepare("UPDATE clinical_task SET status = 'close' WHERE sn = ?");
        $isClosed = $updateStmt->execute(array($_POST['sn']));
        if ($isClosed)
            echo json_encode(["status" => 200, "message" => "Reminder has been closed/Fulfiled"]);
        else
            echo json_encode(["status" => 401, "message" => "Oops! Something went wrong"]);
    }

    exit;
}


if (isset($_POST['saveAlert'])) {
    session_start();
    header('Content-Type: application/json');
    include("../../Connections/Conn.php");
    include('../objects.php');
    include('../helpers.php');

    if (isset($_POST['alert_notes'])) {
        $now_setdate = date('Y-m-d H:i:s');

        $alert_notes = $_POST['alert_notes'];
        $hospital_no = $_POST['hospital_no'];
        if ($alert_notes != '') {
            $save_alert_stmt = $db->prepare('INSERT INTO tbl_patient_alerts (alert,hospital_no, created_by, created_at) VALUES (?, ?, ?,?) ');
            $save = $save_alert_stmt->execute(array($alert_notes, $hospital_no,  $_SESSION['fullname'], $now_setdate));
            echo 'Success: Alert saved';
        }
    }

    exit;
}
$Current_date_date = date("Y-m-d H:i:s");
$date_only = date('Y-m-d');

$stmt = $db->prepare("SELECT * FROM dialysis WHERE is_reminder_set = 0 AND schedule_date >= :today");
$stmt->execute([':today' => $date_only]);

if ($stmt->rowCount() > 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $schedule_date_time = $row['schedule_date'] . ' ' . $row['schedule_time'];
        $hospital_no = $row['hospital_no'];
        $task_desc = 'Auto Reminder of ' . $row['request_type'] . ' booked for ' . $row['patient_name'];

        $sql = $db->prepare("INSERT INTO clinical_task (
            hospital_no, task_desc, every, intervall, task_count, status, sDate, nDate, created_by, link_where
        ) VALUES (
            :hospital_no, :task_desc, :every, :intervall, :task_count, :status, :sDate, :nDate, :created_by, :link_where
        )");

        $sql->execute([
            ':hospital_no' => $hospital_no,
            ':task_desc' => $task_desc,
            ':every' => 2,
            ':intervall' => 'hours',
            ':task_count' => 0,
            ':status' => 'open',
            ':sDate' => $schedule_date_time,
            ':nDate' => $schedule_date_time,
            ':created_by' => $_SESSION['id'],
            ':link_where' => 'Dialysis'
        ]);

        if ($sql->rowCount() > 0) {
            $update = $db->prepare("UPDATE dialysis SET is_reminder_set = 1 WHERE id = ?");
            $update->execute([$row['id']]);
        }
    }
}

if (isset($_POST['setTaskReminderBtn'])) {

    $error_status = 1;
    $error_msg = 'Oops! Something went wrong...';

    $hospital_no = $_POST['hospital_no'];
    $plus = (int)$_POST['every'];
    $created_by = $_SESSION["id"];
    $interval = $_POST['Interval_t'];
    $task_desc = $_POST['task_desc'];
    $task_count = $_POST['task_count'];

    // Combine date and time input
    $C_date = preg_replace('/T/', ' ', $_POST['sDate']); // sDate is in HTML5 datetime-local format

    // Calculate next date based on interval
    switch (strtolower($interval)) {
        case 'mins':
            $CN_date = date("Y-m-d H:i:s", strtotime("$C_date +$plus minutes"));
            break;
        case 'hourly':
            $CN_date = date("Y-m-d H:i:s", strtotime("$C_date +$plus hours"));
            break;
        case 'days':
            $CN_date = date("Y-m-d H:i:s", strtotime("$C_date +$plus days"));
            break;
        case 'weekly':
            $CN_date = date("Y-m-d H:i:s", strtotime("$C_date +$plus weeks"));
            break;
        default:
            $CN_date = date("Y-m-d H:i:s", strtotime("$C_date +$plus months"));
            break;
    }

    // Check if a similar open dialysis task already exists
    $stmt = $db->prepare("SELECT * FROM clinical_task 
        WHERE status = 'open' 
        AND hospital_no = ? 
        AND DATE(sDate) <= ? 
        AND link_where = 'Dialysis'");
    $stmt->execute([$hospital_no, $C_date]);

    if ($stmt->rowCount() == 0) {
        $sql = $db->prepare("INSERT INTO clinical_task (
            hospital_no, task_desc, every, intervall, task_count, status, sDate, nDate, created_by, link_where
        ) VALUES (
            :hospital_no, :task_desc, :every, :intervall, :task_count, :status, :sDate, :nDate, :created_by, :link_where
        )");

        $save = $sql->execute([
            ':hospital_no' => $hospital_no,
            ':task_desc' => $task_desc,
            ':every' => $plus,
            ':intervall' => $interval,
            ':task_count' => $task_count,
            ':status' => 'open',
            ':sDate' => $C_date,
            ':nDate' => $CN_date,
            ':created_by' => $created_by,
            ':link_where' => 'Dialysis'
        ]);

        if ($save) {
            $error_status = 2;
            $error_msg = '✅ Success! Task reminder has been set.';
        }
    } else {
        $error_status = 3;
        $error_msg = '⚠️ A similar task already exists for this patient.';
    }

    // Optional: echo as JSON if used via AJAX
    // echo json_encode(['status' => $error_status, 'message' => $error_msg]);
}


$clinical_count = 0;

////// Should only display message when no post request is made to the page ////////////////
if (!$_POST) {
    if ($page == 'Open_dialysis') {
        ///// upon opening dialysis request to view details
        $pendingTasksstmt = $db->prepare(" SELECT * FROM clinical_task WHERE status = 'open' AND hospital_no = ? AND CAST(nDate AS Datetime) < '$Current_date_date' AND link_where = 'Dialysis'");
        $pendingTasksstmt->execute(array($dialysis_info->hospital_no));
        $clinical_count = $pendingTasksstmt->rowCount();
    } else {
        $pendingTasksstmt = $db->prepare(" SELECT * FROM clinical_task WHERE status = 'open' AND CAST(nDate AS Datetime) < '$Current_date_date' AND link_where = 'Dialysis'");
        $pendingTasksstmt->execute();
        $clinical_count = $pendingTasksstmt->rowCount();
    }

    if ($clinical_count > 0) {
        $pending_task_list = json_decode(json_encode($pendingTasksstmt->fetchAll(PDO::FETCH_ASSOC)));
        foreach ($pending_task_list as $key => $pending_task) {
            $plus = $pending_task->every;
            // $C_date = $pending_task->nDate;
            $C_date = $Current_date_date;
            $sn = $pending_task->sn;
            $new_task_count = $pending_task->task_count - 1;

            if ($pending_task->intervall == 'mins') {
                $new_reminder_dateTime = date("Y-m-d H:i:s", strtotime($C_date . " +$plus minutes"));
            } elseif ($pending_task->intervall == 'hourly') {
                $new_reminder_dateTime = date("Y-m-d H:i:s", strtotime($C_date . " +$plus hours"));
            } elseif ($pending_task->intervall == 'Days') {
                $new_reminder_dateTime = date("Y-m-d H:i:s", strtotime($C_date . " +$plus day"));
            } elseif ($pending_task->intervall == 'Weekly') {
                $new_reminder_dateTime = date("Y-m-d H:i:s", strtotime($C_date . " +$plus week"));
            } else {
                $new_reminder_dateTime = date("Y-m-d H:i:s", strtotime($C_date . " +$plus month"));
            }

            $new_task_count = $new_task_count < 0 ? 0 : $new_task_count;
            $updateStmt = $db->prepare("UPDATE clinical_task SET nDate = ?, task_count = ? WHERE sn = ?");
            $updateStmt->execute(array($new_reminder_dateTime, $new_task_count, $sn));
        }
    }
}


if (isset($_REQUEST['updateAlertNote'])):
    $alert_notes = $_POST['alert_notes'];
    $hospital_no = $_POST['hospital_no'];
    $note_sn = $_POST['note_sn'];

    $now_setdate = date('Y-m-d H:i:s');

    $update_allergy_stmt = $db->prepare('UPDATE tbl_patient_alerts SET alert = ?, updated_at = ? 
      WHERE id=? AND hospital_no = ?');
    $update = $update_allergy_stmt->execute(array($alert_notes, $now_setdate, $note_sn, $hospital_no));

    $error_status = 2;
    $error_msg = 'Success: Alert saved';
endif;


$complains = [];
$stmt = $db->prepare("SELECT * FROM tbl_patient_alerts WHERE hospital_no=?  ORDER BY id DESC ");
$stmt->execute(array($hospital_no));
$clinical_count = $stmt->rowCount();
if ($clinical_count > 0) {
    $complains = $stmt->fetchAll();
}



if (isset($_GET['editAlert']) && !empty($_GET['editAlert'])):
    $clinical_count = 0;

endif;


?>


<div class="widget <?php if ($clinical_count > 0) { ?>red<?php } else { ?>blue<?php } ?>-bg text-center notification-widget">
    <div title="Click to view tasks" data-toggle="modal" data-target="#pending_task_reminder_modal">
        <i class="fa fa-bell fa-4x"></i>
        <h1 class="m-xs notification-widget-count"><?php echo $clinical_count; ?></h1>
        <h5 class="font-bold no-margins">
            Alert(s)
        </h5>
    </div>
    <hr>
    <p class="text-center"><a href="#" type="button" name="fill_result" data-toggle="modal" data-target="#clinical_task_modal" class="btn btn-warning btn-sm clinical_tasks">Set New Alert </a></p>
</div>
<!-- <p class="text-center"><input type="button" name="fill_result_2" value="Fulfill Reminder " data-toggle="modal" data-target="#pending_task_reminder_modal" class="btn btn-default btn-xs view_clinical_task" /></p> -->
<hr>
<!-- <a href="index.php?clinical_rpt=<?php echo $dialysis_info->hospital_no; ?>" class="btn btn-white btn-xs">View Past Report</a> -->




<div class="modal inmodal fade" id="clinical_task_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- <form action="<?= $editFormAction; ?>" method="post"> -->

            <div class="modal-header">
                <a href="<?= $editFormAction; ?>" type="button" class="close" aria-hidden="true">×</a>
                <h4 class="modal-title" id=""> Patient Alert</h4>
            </div>
            <div class="modal-body" id="clinical_task">
                <div id="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-content no-padding">

                                    <table class="table table-hover">
                                        <?php
                                        if ($page == 'Open_dialysis') {
                                        ?>
                                            <tr>
                                                <td colspan="3">
                                                    <div class="form_sep">
                                                        <label for="reg_input_no" class="req"><?= $dialysis_info->patient_name . ' / ' . $dialysis_info->hospital_no; ?> </label>
                                                        <input type="hidden" name="hospital_no" value="<?php echo $dialysis_info->hospital_no; ?>" />
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        } else {
                                        ?>
                                            <tr>
                                                <td colspan="3">
                                                    <div class="form_sep">
                                                        <label for="reg_input_no" class="req">Select Patient</label>
                                                        <select name="hospital_no" class="input-sm chosen-select" style="width:350px;" required>
                                                            <option selected="selected" value="">Search</option>
                                                            <?php
                                                            $all_patients = $Dialysis->get(['completed' => 'no'], true);
                                                            foreach ($all_patients as $key => $patient) {
                                                            ?>
                                                                <option value="<?php echo $patient->hospital_no; ?>"><?= $patient->hospital_no . ' - ' . $patient->patient_name; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>

                                        <tr>
                                            <td colspan="3">
                                                <div class="form_sep">
                                                    <label for="reg_input_no" class="req">Note:</label>
                                                    <textarea name="allergies_notes" id="allergies_notes" cols="30" rows="10" class="form-control"></textarea>
                                                </div>
                                            </td>
                                        </tr>


                                    </table>





                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn btn-sm" type="submit" name="setTaskReminderBtn" id="save_notes" onclick="save_allergies()">Save</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
            <!-- </form> -->
        </div>
    </div>
</div>


<?php

if (isset($_GET['editAlert']) && !empty($_GET['editAlert'])):
    $sn = base64_decode(base64_decode($_GET['editAlert']));
    $stmt = $db->prepare("SELECT * FROM tbl_patient_alerts WHERE hospital_no=? and id=? ORDER BY id DESC ");
    $stmt->execute(array($hospital_no, $sn));
    $clinical_count = $stmt->rowCount();
    if ($clinical_count > 0):
        $complain = $stmt->fetch();

?>
        <div class="modal inmodal fade" id="editAlertModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="<?= $details_page_url; ?>" method="post">

                        <div class="modal-header">
                            <a href="<?= $details_page_url; ?>" type="button" class="close" aria-hidden="true">×</a>
                            <h4 class="modal-title" id=""> Patient Alert</h4>
                        </div>
                        <div class="modal-body" id="editAlertModalBody">
                            <div id="wrapper">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="ibox float-e-margins">
                                            <div class="ibox-content no-padding">

                                                <table class="table table-hover">
                                                    <?php
                                                    if ($page == 'Open_dialysis') {
                                                    ?>
                                                        <tr>
                                                            <td colspan="3">
                                                                <div class="form_sep">
                                                                    <label for="reg_input_no" class="req"><?= $dialysis_info->patient_name . ' / ' . $dialysis_info->hospital_no; ?> </label>
                                                                    <input type="hidden" name="hospital_no" value="<?php echo $dialysis_info->hospital_no; ?>" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <tr>
                                                            <td colspan="3">
                                                                <div class="form_sep">
                                                                    <label for="reg_input_no" class="req">Select Patient</label>
                                                                    <select name="hospital_no" class="input-sm chosen-select" style="width:350px;" required>
                                                                        <option selected="selected" value="">Search</option>
                                                                        <?php
                                                                        $all_patients = $Dialysis->get(['completed' => 'no'], true);
                                                                        foreach ($all_patients as $key => $patient) {
                                                                        ?>
                                                                            <option value="<?php echo $patient->hospital_no; ?>"><?= $patient->hospital_no . ' - ' . $patient->patient_name; ?></option>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>

                                                    <tr>
                                                        <td colspan="3">
                                                            <div class="form_sep">
                                                                <label for="reg_input_no" class="req">Note:</label>
                                                                <textarea name="alert_notes" id="alert_notes" cols="30" rows="10" class="form-control"><?= $complain['alert']; ?></textarea>
                                                            </div>
                                                        </td>
                                                    </tr>


                                                </table>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="note_sn" value="<?= $complain['id']; ?> ">
                            <input type="hidden" name="hospital_no" value="<?= $complain['hospital_no']; ?> ">
                            <button class="btn btn-success btn btn-sm" type="submit" name="updateAlertNote">Update</button>
                            <a href="<?= $details_page_url; ?>" type="button" class="btn btn-danger">Close</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#editAlertModal').modal('show');
            });
        </script>
        <script>
            function update_allergies() {

                var note_sn = $('#note_sn').val();
                var hospital_no = $('#note_hospital_no').val();
                var allergies_notes = $('#allergies_notes2').val();
                var app_no = "<?= $dialysisInfo->app_no; ?>";
                $.ajax({
                    url: "_patient_allergies.php",
                    method: "POST",
                    data: {
                        hospital_no: hospital_no,
                        app_no: app_no,
                        allergies_notes: allergies_notes,
                        update_allergies_btn: true,
                        sn: note_sn
                    },
                    success: function(data) {
                        alert(data);
                        window.location.reload(true);
                    }
                });

            }
        </script>
    <?php
    endif;
endif;



if (isset($_GET['editAlert']) && !empty($_GET['editAlert'])):
    $clinical_count = 0;
    ?>
    <script>
        $('#pending_task_reminder_modal').modal('hide');
    </script>
<?php
endif;
?>