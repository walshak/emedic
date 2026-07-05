<?php
if (isset($_GET['delete'])) {
    if (isset($_GET['data'])) {
        $id = intval(cleanInput($_GET['data']));

        $stmt = $db->prepare("UPDATE medical_report_task SET  status = '0'  WHERE id = ? AND action !='Printed'  ");
        $save =  $stmt->execute(array($id));
        $error_status = 2;
        $error_msg = 'Success: Deleted';
    }
}

// include('../mailer/index.php');

function sendMail($recipientEmail, $mailBody, $mailSubject, $recipientName)
{
    $senderEmail = 'info@ngscha.ni.gov.ng';
    $senderName = 'Webmedic';

    // Create email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $senderName . " <" . $senderEmail . ">" . "\r\n";

    // Send email
    if (mail($recipientEmail, $mailSubject, $mailBody, $headers)) {
        echo 'Message has been sent';
    } else {
        echo 'Message could not be sent.';
    }
}



if (isset($_POST['update_med_report_btn'])) {
    $med_report_notes = $_POST['med_report_notes'];

    $hospital_no = $_POST['hospital_no'];
    $action = $_POST['med_report_notes_action'];
    $report_type = $_POST['med_report_type'];

    $error_status = 1;
    $error_msg = 'Error: Notes not saved...';
    $data = null;
    $id = intval(cleanInput($_POST['med_report_id']));

    $stmt = $db->prepare("UPDATE medical_report_task SET  notes = ?,report_type=?, action = ?, created_by_name = ? WHERE id = ?  ");
    $save =  $stmt->execute(array($med_report_notes, $report_type, $action, $_SESSION['fullname'],  $id));

    if ($save) {
        $error_status = 2;
        if ($action == 'email') {
            sendMail('abdulkarimabdullahi365@gmail.com', $med_report_notes, $report_type, 'Abdulkarim A.');
            $error_msg = 'Success: Notes sent to mail';
        } else {
            $error_msg = 'Success: Notes is saved...';
        }
    }

    $current_tab = 'medical_report';
}


if (isset($_POST['saveNewMedicalReport'])) {
    $med_report_notes = $_POST['med_report_notes'];

    $hospital_no = $_POST['hospital_no'];
    $action = $_POST['med_report_notes_action'];
    $report_type = $_POST['med_report_type'];

    $error_status = 1;
    $error_msg = 'Error: Notes not saved...';
    $data = null;

    $stmt = $db->prepare("SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1' AND created_by = ? AND token = ? ");
    $stmt->execute(array($hospital_no, $_SESSION['id'], $_POST['token']));
    if ($stmt->rowCount()  == 0) {
        $stmt = $db->prepare("INSERT INTO medical_report_task  (hospital_no, notes, created_by,action, created_by_name, status, token, report_type) VALUES (?, ?, ?, ?, ?, '1', ?, ?) ");
        $save = $stmt->execute(array($hospital_no, $med_report_notes, $_SESSION['id'], $action, $_SESSION['fullname'], $_POST['token'], $report_type));

        if ($save) {
            $error_status = 2;
            if ($action == 'email') {
                sendMail('abdulkarimabdullahi365@gmail.com', $med_report_notes, $report_type, 'Abdulkarim A.');
                $error_msg = 'Success: Notes sent to mail';
            } else {
                $error_msg = 'Success: Notes is saved...';
            }
        }
    }

    $current_tab = 'medical_report';
}


$canSeeMedicalAssesment = false;
// logic to check this right

$med_report_sql_string = '';
$canSeeMedicalAssesment = $_SESSION['rights'] === 'MD' ? true : false;
if ($canSeeMedicalAssesment === false) {
    $med_report_sql_string .= " AND report_type != 'Medical Assessment' ";
}

$sql = "SELECT 
    SUM(CASE WHEN report_type = 'Medical Report' THEN 1 ELSE 0 END ) AS medical_report_total,
      SUM(CASE WHEN report_type = 'Medical Assessment' THEN 1 ELSE 0 END ) AS medical_assessment_total,
        SUM(CASE WHEN report_type = 'Referral' THEN 1 ELSE 0 END ) AS referral_total
      FROM medical_report_task WHERE hospital_no = ? AND status = '1' ";
$report_cout_stmt = $db->prepare($sql);
$report_cout_stmt->execute(array($hospital_no));
$report_cout_row = $report_cout_stmt->fetch();

$med_report_draft_stmt = $db->prepare("SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1'  AND action = 'draft'  ORDER BY id DESC");
$med_report_draft_stmt->execute(array($hospital_no));

$med_report_sent_stmt = $db->prepare("SELECT * FROM medical_report_task WHERE hospital_no = ? AND status = '1'  AND action = 'save'  $med_report_sql_string ORDER BY id DESC");
$med_report_sent_stmt->execute(array($hospital_no));

?>
<div class="panel-body">
    <div class="row">
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                <div class="ibox-content mailbox-content">
                    <div class="file-manager">
                        <a class="btn btn-block btn-success compose-mail " href="med_report.php?hosp_no=<?= $hosp_no; ?>" arial-data="compose-med-report"><i class="fa fa-edit"></i> Compose Medical Report</a>
                        <div class="space-25"></div>
                        <h5>Main</h5>
                        <ul class="folder-list m-b-md" style="padding: 0">
                            <li class="" arial-data="sent-med-report">
                                <a href="med_report.php?hosp_no=<?= $hosp_no; ?>&med-report" style="font-size: 14px;"> <i class="fa fa-inbox "></i> Medical Reports<span class="label label-warning pull-right" id="sent_counter"><?php echo $report_cout_row['medical_report_total']; ?></span> </a>
                            </li>
                            <li class="" arial-data="draft-med-report"><a href="med_report.php?hosp_no=<?= $hosp_no; ?>&assessment" style="font-size: 14px;">
                                    <i class="fa fa-file-text-o"></i> Medical Assessment <span class="label label-danger pull-right" id="draft_counter"><?php echo $report_cout_row['medical_assessment_total']; ?></span></a></li>
                            <li class="" arial-data="draft-med-report"><a href="med_report.php?hosp_no=<?= $hosp_no; ?>&referral" style="font-size: 14px;">
                                    <i class="fa fa-file-text-o"></i> Referral Notes <span class="label label-danger pull-right" id="draft_counter"><?php echo $report_cout_row['referral_total']; ?></span></a></li>

                        </ul>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9 animated fadeInRight">
            <div class="mail-box-header"></div>
            <div class="mail-box">
                <?php if (isset($_GET['data']) && isset($_GET['edit'])) :
                    $id = intval(cleanInput($_GET['data']));
                    $stmt = $db->prepare("SELECT * FROM medical_report_task WHERE id=? AND hospital_no = ? ");
                    $stmt->execute(array($id, $hosp_no));
                    if ($stmt->rowCount() >  0) :
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $notes = $row['notes'];


                ?>
                        <div>
                            <!-- <div class="text-right">
                                <button class="btn btn-card" id="copy-from-med-hx-btn"><i class="fa fa-copy"></i> Copy from Medical History</button>
                                <button class="btn  btn-danger" id="close-copy-from-med-hx-btn" style="display:none"><i class="fa fa-close"></i> Close Medical History</button>
                                <button class="btn  btn-success" id="open-copy-from-med-hx-btn" style="display:none"><i class="fa fa-open"></i> Open Medical History</button>
                            </div> -->
                            <div id="copy-from-med-hx-wrap">

                            </div>
                            <form action="<?php echo $editFormAction; ?>" method="post" id="med_report_notes_form">
                                <div class="mb-2">
                                    <label for="">Medical Report Type [<span class="text-danger">*</span>]</label>
                                    <select name="med_report_type" id="med_report_type" class="form-control" required>
                                        <option value="">Select Report Type</option>
                                        <option value="Medical Report" <?= $row['report_type'] == 'Medical Report' ? 'selected' : ''; ?>> Medical Report</option>
                                        <option value="Medical Assessment" <?= $row['report_type'] == 'Medical Assessment' ? 'selected' : ''; ?>> Medical Assessment Note</option>
                                        <option value="Referral" <?= $row['report_type'] == 'Referral' ? 'selected' : ''; ?>> Referral Note</option>
                                    </select>
                                </div>
                                <br>
                                <div id="med_report_notes_wrap"> <textarea name="med_report_notes" id="med_report_notes" cols="30" rows="10" class="trumbowygEditor"><?= $row['notes']; ?></textarea></div>

                                <div>
                                    <input type="hidden" name="hospital_no" id="med_report_hospital_no" value="<?php echo $hospital_no; ?>">
                                    <input type="hidden" name="med_report_id" id="med_report_id" value="<?php echo $row['id']; ?>">
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="hidden" name="med_report_notes_action" id="med_report_notes_action" required>
                                        <select name="med_report_notes_action" id="med_report_notes_action" class="form-control" required>
                                            <option value="" selected>Select Action</option>
                                            <option value="draft" <?= ($row['action'] == 'draft' ? 'selected' : ''); ?>> Draft</option>
                                            <option value="save" <?= ($row['action'] == 'save' ? 'selected' : ''); ?>> Send for Printing</option>
                                            <option value="email" <?= ($row['action'] == 'email' ? 'selected' : ''); ?>> Send to email</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-right">
                                            <button class="btn btn-success" name="update_med_report_btn"><i class="fa fa-save"></i> Submit</button>
                                            <a href="med_report.php?hosp_no=<?= $hosp_no; ?>" class="btn btn-danger" id="reset-med-report-btn"> <i class="fa fa-refresh"></i> Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>


                        </div>
                    <?php endif; ?>
                <?php elseif (isset($_GET['med-report']) || isset($_GET['assessment']) || isset($_GET['referral'])) : ?>
                    <div id="sent-med-report-status"></div>
                    <div id="sent-med-report-content"><?php include_once('_patient_med_report_sent.php'); ?></div>
                <?php else : ?>
                    <div id="compose-med-report" class="med_report_div_wrap">
                        <div>
                            <!-- <div class="text-right">
                                <button class="btn btn-card" id="copy-from-med-hx-btn"><i class="fa fa-copy"></i> Copy from Medical History</button>
                                <button class="btn  btn-danger" id="close-copy-from-med-hx-btn" style="display:none"><i class="fa fa-close"></i> Close Medical History</button>
                                <button class="btn  btn-success" id="open-copy-from-med-hx-btn" style="display:none"><i class="fa fa-open"></i> Open Medical History</button>
                            </div> -->
                            <div id="copy-from-med-hx-wrap">

                            </div>
                            <form action="med_report.php?hosp_no=<?= $hosp_no; ?>" method="post" id="med_report_notes_form">
                                <div class="mb-2">
                                    <label for="">Medical Report Type [<span class="text-danger">*</span>]</label>
                                    <select name="med_report_type" id="med_report_type" class="form-control" required>
                                        <option value="">Select Report Type</option>
                                        <option value="Medical Report"> Medical Report</option>
                                        <option value="Medical Assessment"> Medical Assessment Note</option>
                                        <option value="Referral"> Referral Note</option>
                                    </select>
                                </div>
                                <br>


                                <div id="med_report_notes_wrap"> <textarea name="med_report_notes" id="med_report_notes" cols="30" rows="10" class="trumbowygEditor"></textarea></div>

                                <div><input type="hidden" name="hospital_no" id="med_report_hospital_no" value="<?php echo $hospital_no; ?>"></div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-8">
                                        <label for="">Select Save Action [<span class="text-danger">*</span>]</label>
                                        <input type="hidden" name="med_report_notes_action" id="med_report_notes_action_" required>
                                        <select name="med_report_notes_action" id="med_report_notes_action" class="form-control" required>

                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-right">
                                            <input type="hidden" name="token" id="token" value="<?= uniqid(); ?>">
                                            <button class="btn btn-success" name="saveNewMedicalReport"><i class="fa fa-save"></i> Submit</button>
                                            <a href="med_report.php?hosp_no=<?= $hosp_no; ?>" class="btn btn-danger" id="reset-med-report-btn"> <i class="fa fa-refresh"></i> Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>



                            </form>
                        </div>
                        <br>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Listen for changes in the med_report_notes_action select
        $(document).on('change', '#med_report_type', function() {
            // Get the selected value
            var selectedType = $(this).val();
            // Check if the selected value is 'ma' (Medical Assessment Note)
            if (selectedType == 'Medical Assessment') {
                // Set the save action to 'save' (Send for Printing)
                $('#med_report_notes_action').html(
                    ` <option value="save"> Forward for Printing</option>   <option value="email"> Send to email</option>`
                );
            } else {
                $('#med_report_notes_action').html(
                    `  <option value="">Select  Action</option>
                        <option value="draft"> Draft</option>
                        <option value="save"> Send for Printing</option>
                        <option value="email"> Send to email</option>
                        `
                );
            }
        });
    });
</script>