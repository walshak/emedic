<?php


if (isset($_GET['third_party_c'])) {
    $third_party_c = base64_decode($_GET['third_party_c']);

    $update = $db->prepare("UPDATE patients_documents SET status = '0' WHERE id = ?");
    $updated = $update->execute(array($third_party_c));

    if ($updated && $update->rowCount() > 0) {
        echo "<script>alert('Status updated successfully.');</script>";
    } else {
        echo "<script>alert('No update made. Either ID not found or status already set to 0.');</script>";
    }
}


if (isset($_POST['add_procedure_doc'])) {
    // Sanitize inputs
    $target_dir = "../documents/patients/";
    $hospital_no = isset($_POST['hospital_no']) ? $_POST['hospital_no'] : '';
    $transplant_id = isset($_POST['transplant_id']) ? $_POST['transplant_id'] : '';
    $result_type = isset($_POST['result_type']) ? $_POST['result_type'] : '';

    // Check if a file was uploaded
    if (isset($_FILES["result_file"]) && $_FILES["result_file"]["error"] === UPLOAD_ERR_OK) {
        // Get the original file name and extension
        $original_file_name = basename($_FILES["result_file"]["name"]);
        $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];

        if (in_array($file_extension, $allowed_extensions)) {
            // Create a unique file name
            $new_file_name = $transplant_id . "_" . uniqid() . "_" . $hospital_no . '_' . $original_file_name;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["result_file"]["tmp_name"], $target_file)) {
                $save_hla_result = $Document->save($hospital_no, $target_file, $_SESSION["id"], $result_type, $file_extension, 'Procedures', 'Procedures', $transplant_id);
                $isHlaResultUploaded = true;
                $error_status = 2;
                $error_msg = "  Saved successfully...";
            } else {
                $error_msg = "Error: Failed to move uploaded file.";
            }
        } else {
            $error_msg = "Error: Invalid file type. Allowed types are: " . implode(", ", $allowed_extensions);
        }
    } else {
        // Handle file upload errors
        $error_msg =  "Error: File upload error code: " . $_FILES["result_file"]["error"];
    }
}
?>

<?php
$appointment_number = null;

//////////////////  ADD RESOURCE PERSON
if (isset($_POST['update_book_procedure'])) {

    $pp = $_POST['update_doctor'];
    $indication = $_POST['remarks'];
    $start_date = $_POST['start_date'];
    $sn_procedure_sn = $_POST['sn_procedure_sn'];
    $require_theater = $_POST['require_theater'];
    $theater_select = $_POST['theater_select'];
    $procedure_time = $_POST['procedure_time'];
    $reason_procedure = $_POST['reason_procedure'];

    $pp = $_POST['update_doctor'];
    $m_p = explode("__", $pp);
    $consultant_id = $m_p[0];
    $consultant_name = $m_p[1];

    if ($indication != '') {
        $indication .= '<br>';
    }

    if (trim($consultant_name) != trim($_POST['old_consultant_name'])) {
        $indication .= ' Changed Consultant: ' . $_POST['old_consultant_name'];
    }

    $new_date = date('Y-m-d', strtotime($start_date));
    $old_date = date('Y-m-d', strtotime($_POST['old_date']));

    if (trim($new_date) != trim($old_date)) {
        $indication .= ' Changed Dates: ' . $old_date;
    }

    $hospital_no = $_POST['hospital_no'];
    $stmt = $db->prepare("UPDATE procedures SET indication=?,consultant_name=?,primary_diag=?, consultant_id = ?, sDate = ?, require_theater=?, theater=?, procedure_time=? WHERe sn=? ");
    $stmt->execute(array($indication, $consultant_name, $reason_procedure, $consultant_id, $start_date, $require_theater, $theater_select, $procedure_time, $sn_procedure_sn));
    $error_status = 2;
    $error_msg = 'Success : Saved';
}


if (isset($_POST['add_resource_person'])) {
    $resource_code = $_POST['resource_role'];
    $id = $_POST['resource_sn'];
    $procedure_sn = $_POST['procedure_sn'];
    $hospital_no = $_POST['hospital_no'];

    $resources = [
        ['resource_code' => 'rss', 'role' => 'Surgeon'],
        ['resource_code' => 'ras', 'role' => 'Assistant Surgeon'],
        ['resource_code' => 'ran', 'role' => 'Anaesthetist'],
        ['resource_code' => 'rsn', 'role' => 'Nurse']
    ];

    $save = false;
    $role = null;
    $name = null;
    foreach ($resources as $key => $resource) {
        if ($resource["resource_code"] == $resource_code) {
            $role = $resource["role"];
        }
    }


    $stmt = $db->prepare("SELECT * FROM admin_users where id= ?  and status='1' ");
    $stmt->execute(array($id));
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = $row["fullname"];
    }

    $check_stmt = $db->prepare("SELECT * FROM procedure_resources where prdure_sn= ?  and resource_sn= ? AND resource_code = ?");
    $check_stmt->execute(array($procedure_sn, $id, $resource_code));
    if ($check_stmt->rowCount() == 0) {

        $stmt = $db->prepare("INSERT INTO  procedure_resources (prdure_sn, hosp_no, resource_sn, name, resource_code,dateadd, role) VALUES (?, ?, ?, ?, ?, ?, ?) ");
        $save = $stmt->execute(array($procedure_sn, $hospital_no, $id, $name, $resource_code, date('Y-m-d h:i:s'), $role));
        $error_status = 2;
        $error_msg = 'Success : Added successfully...';
    }
}

//////////////////  REMOVE RESOURCE PERSON
if (isset($_REQUEST['remove-resource-person-btn'])) {
    $rsn = $_REQUEST['rsn'];

    $check_stmt = $db->prepare("SELECT * FROM procedure_resources where sn= ? ");
    $check_stmt->execute(array($rsn));
    if ($check_stmt->rowCount() > 0) {
        $remove_stmt = $db->prepare("DELETE FROM procedure_resources where sn= ? ");
        $remove = $remove_stmt->execute(array($rsn));
        if ($remove) {
            $error_status = 2;
            $error_msg = 'Success : Removed successfully...';
        }
    }
}


if ($_SESSION['dispensory'] == 1) {
    $dept_id = $_SESSION['dept_id'];
    $_sort_by_dept_id = "dept_id='$dept_id' AND ";
} else {
    $_sort_by_dept_id = '';
}


$procedure_sn = base64_decode(base64_decode($_GET['pr']));
$patient_procedure_stmt = $db->prepare("SELECT * from procedures WHERE $_sort_by_dept_id sn=? ORDER BY sn DESC LIMIT 1");
$patient_procedure_stmt->execute(array($procedure_sn));
if ($patient_procedure_stmt->rowCount() > 0) {
    $procedure = $patient_procedure_stmt->fetch(PDO::FETCH_ASSOC);
    $hospital_no = $procedure['hospital_no'];

    $consultant_id = $procedure['consultant_id'];
    $post_opt_notes_id = $procedure['post_opt_notes_id'];
    $performed_date = $procedure['performed_date'];
    $sale_no = $procedure['sale_no'];

    /// check who should document
    $disable = '';
    $disable2 = '';
    $disable_edit_button = null;

    if ($_SESSION["id"] != $consultant_id) {
        //$disable='disabled';
    }

    if ($post_opt_notes_id != '') {
        $disable_edit_button = 'disabled';
    }


    $patient_info = $Patient->get(['hospital_no' => $hospital_no]);
    if (!empty($patient_info)) {
        $hospital_no = $patient_info->hospital_no;
        $vip = $patient_info->vip;

        if ($vip == 1 && $_SESSION['rights'] != 'MD') {
            header("location:index.php?vip");
        }
    }

    $hosp_no = $hospital_no;
    $p = '';
    $patient_past_procedures_stmt = $db->prepare("SELECT hospital_no,procedures from procedures WHERE hospital_no=? AND sn != ? AND post_op_results IS NOT NULL ORDER BY sn DESC ");
    $patient_past_procedures_stmt->execute(array($hospital_no, $procedure_sn));
    if ($patient_past_procedures_stmt->rowCount() > 0) {
        while ($pp = $patient_past_procedures_stmt->fetch(PDO::FETCH_ASSOC)) {
            $p = $p . $pp['procedures'] . ', ';
        }
        $past_procedures = substr($p, 0, -2);
    } else {
        $past_procedures = 'None';
    }

    $procedure_sn = $procedure["sn"];
    $appointment_number = $procedure["app_no"];
    $pre_opt_notes_id = $procedure["pre_opt_notes_id"];
    $anas_opt_notes_id = $procedure["anas_opt_notes_id"];
    $post_opt_notes_id = $procedure["post_opt_notes_id"];
    $post_op_results = $procedure["post_op_results"];
    $p_app_no = $procedure["app_no"];
    $service_id = $procedure["service_id"];

    $sn = 1;
    $amount = 0;
    $d1 = strtotime($procedure["sDate"]);
    $d2 = strtotime(date('Y-m-d h:i:s'));
    $totalSecondsDiff = $d1 - $d2;
    $day =  ceil($totalSecondsDiff / (3600 * 24));
    $week =  floor($totalSecondsDiff / (3600 * 24 * 7));
    $paystatus = 0;

    $stmt = $db->prepare("
    SELECT pay, paystatus, pay_mode 
    FROM patient_ap_services 
    WHERE hospital_no = :hospital_no 
      AND sn = :sale_no
    LIMIT 1");

    $stmt->execute([
        ':hospital_no' => $hospital_no,
        ':sale_no'     => $sale_no
    ]);

    $procedure_service = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($procedure_service) {
        $amount    = $procedure_service['pay'];
        $paystatus = $procedure_service['paystatus'];
        $pay_mode  = $procedure_service['pay_mode'];
    }


    //////// NOTES
    $post_opt_notes = null;
    $pre_opt_notes = null;
    $anas_opt_notes = null;

    $stmt = $db->prepare("SELECT * FROM notes WHERE sn=? AND status='1'");
    $stmt->execute(array($post_opt_notes_id));
    if ($stmt->rowCount() > 0) {
        $notes = $stmt->fetch(PDO::FETCH_ASSOC);
        $post_opt_notes = $notes['notes'];
        $created_by_id = $notes['created_by'];

        if ($post_opt_notes_id != '') {
            if (date_diff_day($performed_date) > 1) {
                $disable2 = 'disabled';
            } else {
                if (($_SESSION["id"] == $consultant_id or $created_by_id == $_SESSION["id"])) {
                    $disable2 = '';
                } else {
                    $disable2 = 'disabled';
                }
            }
        }

        $post_opt_notes_entered_by  = '<b><i>Entered By: </i></b>' . $notes['prepared_by'];

        if (!empty($notes['date_entry'])) {
            $post_opt_notes_entered_by .= '<br> Date/Time: ' . formatDateTime_($notes['date_entry']);
        }
    }

    $stmt = $db->prepare("SELECT * FROM notes WHERE sn=? AND status='1'");
    $stmt->execute(array($pre_opt_notes_id));
    if ($stmt->rowCount() > 0) {
        $notes = $stmt->fetch(PDO::FETCH_ASSOC);
        $pre_opt_notes = $notes['notes'];
        $pre_opt_notes_entered_by = '<b><i>Entered By: </i></b>' . $notes['prepared_by'];

        if (!empty($notes['date_entry'])) {
            $pre_opt_notes_entered_by .= '<br> Date/Time: ' . formatDateTime_($notes['date_entry']);
        }
    }

    $stmt = $db->prepare("SELECT * FROM notes WHERE sn=? AND status='1'");
    $stmt->execute(array($anas_opt_notes_id));
    if ($stmt->rowCount() > 0) {
        $notes = $stmt->fetch(PDO::FETCH_ASSOC);
        $anas_opt_notes = $notes['notes'];
        $anas_opt_notes_entered_by = '<b><i>Entered By: </i></b>' . $notes['prepared_by'];

        if (!empty($notes['date_entry'])) {
            $anas_opt_notes_entered_by .= '<br> Date/Time: ' . formatDateTime_($notes['date_entry']);
        }
    }

    $paystatus_ = '<span class="label label-danger">Not Paid</span>';
    if ($paystatus == 1) {
        $paystatus_ = '<span class="label label-primary">Paid</span>';
    }

    $emr = $hosp_no;
    $general_credit_limit =    $_SESSION['credit_limit_status'];
    $items = call_current_balance($db, $emr, $general_credit_limit);
    $current_balance =  $items["current_balance"];
    $credit_limit  = $items['bal_credit_limit'];
} else {
    exit;
}


$dept_id = $_SESSION['dept_id'];
$templates = [];
$template_stmt = $db->prepare("SELECT t.* FROM services_templates t INNER JOIN serv_cat c ON t.service_cat = c.serv_cat_id WHERE t.status = '1' and t.department_id = '$dept_id'");
$template_stmt->execute();

if ($template_stmt->rowCount() > 0) {
    $templates = $template_stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>




<div class="row">
    <div class="col-lg-12">
        <div class="wrapper wrapper-content animated fadeInUp">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-12">

                            <a href="procedure.php?procedure" class="btn btn-success pull-left">
                                <i class="fa fa-list"></i> &nbsp;Goto All Procedures</a>


                            <div class="m-b-md  pull-right">

                                <?php if ($rights == 'NS') {
                                    $href = "nursing";
                                } else {
                                    $href = "doctor";
                                } ?>
                                <a href="index.php?procedure&patient=<?php echo $hospital_no; ?>" class="btn btn-primary">
                                    <i class="fa fa-user"></i>&nbsp;Return to Patient List</a>

                                &nbsp; | &nbsp;

                                <a href="procedure.php?procedure&pr=<?= base64_encode(base64_encode($procedure_sn)); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow"></i>&nbsp;Refresh Page</a>

                            </div>
                            <br>

                        </div>
                    </div>

                    <div id="printable-area" style="border: 2px solid #000;padding: 20px">
                        <div>
                            <h3 class="text-center">
                                <p><img src="../img/logo.png" alt="logo" width="100px"></p>
                                <?php echo $_SESSION['h_name']; ?>
                            </h3>
                            <h5 class="text-center"><?php echo $_SESSION['h_address']; ?> <br> <?php echo $_SESSION['h_phone']; ?></h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:14px; font-family:Arial, Helvetica, sans-serif;">
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left">
                                            <h3 align="center"><I>PROCEDURE: </I>: <?= $procedure["procedures"]; ?>
                                                <?php if ($post_opt_notes_id == '' && ($procedure['consultant_id'] == $_SESSION['id'] || $_SESSION['fullname'] == $procedure['prepared_by'])) {

                                                ?>
                                                    <input type="button" name="change_item" value="Change Procedure" data-target="#myModal5" id="<?= $procedure_sn; ?>" class="btn btn-danger btn-xs change_item" />
                                                <?php } ?>
                                            </h3>

                                            <?php if (!in_array(strtolower($_SESSION['h_code']), array('360', 'mluth'))) { ?>
                                                <h3 align="center"><i>AMOUNT:</i> N <?= number_format($procedure["cost"]); ?>
                                                <?php } ?>
                                                <?php
                                                if ($paystatus == 0 && $pay_mode == 'cash') {
                                                    if ($current_balance > 0 and $_SESSION['payfrom_status'] == 1) {
                                                ?> <button type="button" class="btn btn-warning btn-xs dropdown-toggle" id="pay_now<?php echo $sale_no; ?>" onClick="payNow('<?php echo $sale_no; ?>','procedure','<?= $hosp_no; ?>')">Pay from Wallet</button>
                                                <?php }
                                                }
                                                ?>
                                                </h3>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right"><b>Patient Details:</b></td>
                                        <td style="border-bottom: 1px solid #000; text-align:left" width="70%">
                                            <?php echo $procedure["hospital_no"] . ' | ' . $procedure["name"]; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right">
                                            <b>Requested by | Consultant:</b>
                                        </td>
                                        <td style="border-bottom: 1px solid #000; text-align:left">
                                            <?php echo $procedure["prepared_by"] . ' | ' . $procedure["consultant_name"]; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right">
                                            <b>Requested Date:</b> <br>
                                            <b>Procedure Date:</b><br>
                                            <b>Procedure Duration:</b><br>
                                            <b>Performed Date:</b><br>
                                            <b>Require Theater Use:</b><br>
                                            <b>Theater:</b>
                                        </td>
                                        <td style="border-bottom: 1px solid #000; text-align:left">

                                            <?php echo date('d M, Y h:i:s A', strtotime('' . $procedure["date_entry"] . '')); ?><br />
                                            <?php echo date('d M, Y h:i:s A', strtotime('' . $procedure["sDate"] . ''));

                                            $sDate = $procedure["sDate"]; // e.g., '2024-11-07 01:24:00'
                                            $currentDateTime = new DateTime();
                                            $procedureDateTime = new DateTime($sDate);
                                            $interval = $currentDateTime->diff($procedureDateTime);
                                            $remainingDays = $interval->d;
                                            $remainingHours = $interval->h;
                                            echo '&nbsp';
                                            if ($procedureDateTime > $currentDateTime) {
                                                echo " <b style='color:red;'>Remaining time: " . $remainingDays . " days and " . $remainingHours . " hours.</b>";
                                            } else {
                                                echo "<b>The date has already passed.</b>";
                                            }
                                            ?><br />
                                            <?= $procedure["procedure_time"]; ?><br>
                                            <?= !empty($procedure["performed_date"]) ? date('d M, Y', strtotime('' . $procedure["performed_date"] . '')) : ''; ?><br />
                                            <strong><i>[ <?php echo $procedure["require_theater"]; ?> ]</i></strong><br>
                                            <?= $procedure["theater"]; ?>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left">
                                            <h3>Reason for Procedure, Previous Procedure(s) & Other Notes:</h3>
                                            <?= $procedure["primary_diag"]; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left">
                                            <h3 align="center"><u>PREVIOUS PROCEDURES IN THIS THREAD</u></h3>
                                            <div>
                                                <?php if (!empty($procedure['old_procedure_id']) && !empty($procedure['old_procedure_name'])): ?>
                                                    <p>
                                                        <span><?php echo $procedure['old_procedure_name'] ?> &nbsp; <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["old_procedure_id"])); ?>" target="_blank">[View This Prev. Procedure]</a></span>
                                                    </p>
                                                <?php endif ?>
                                            </div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right"><b>Updated Notes:</b></td>
                                        <td style="border-bottom: 1px solid #000; text-align:left"><?php echo $procedure["indication"]; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right"><b>Date of Operation:</b></td>
                                        <td style="border-bottom: 1px solid #000; text-align:left"><?php echo ($procedure["performed_date"] != null ? date('d M, Y h:i:s A', strtotime('' . $procedure["performed_date"] . '')) : ''); ?></td>
                                    </tr>

                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right"><b>Past Procedure(s):</b></td>
                                        <td style="border-bottom: 1px solid #000; text-align:left"><?php echo $past_procedures; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right"><b>Current Operation Remark:</b></td>
                                        <td style="border-bottom: 1px solid #000; text-align:left"><?php echo $post_op_results; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #000; text-align:right">
                                            <h4><u>Resource Person(s):</u></h4>
                                        </td>
                                        <td style="border-bottom: 1px solid #000; text-align:left">
                                            <ol>
                                                <?php
                                                $check_stmt = $db->prepare("SELECT * FROM procedure_resources where prdure_sn= ? ORDER BY role ");
                                                $check_stmt->execute(array($procedure_sn));
                                                if ($check_stmt->rowCount() > 0) {
                                                    $sn = 1;
                                                    $procedure_resources = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($procedure_resources as $key => $procedure_resource) {
                                                        $rsn = $procedure_resource["sn"];
                                                        $prdure_sn = $procedure_resource["prdure_sn"];
                                                        $name = $procedure_resource["name"];
                                                        $role = $procedure_resource["role"];

                                                ?>
                                                        <li><?php echo $name . ' <b>[' . $role . ']</b>'; ?></li>
                                                <?php

                                                    }
                                                }
                                                ?>
                                            </ol>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left">
                                            <h3 align="center"><u>PRE-OP NOTES</u></h3>
                                            <div>
                                                <?php
                                                echo $pre_opt_notes . '<hr>';
                                                echo $pre_opt_notes_entered_by;
                                                ?>
                                            </div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left">
                                            <h3 align="center"><u>ANESTHESIA NOTES</u></h3>
                                            <div>
                                                <?php
                                                echo $anas_opt_notes . '<hr>';
                                                echo $anas_opt_notes_entered_by;
                                                ?>
                                            </div>
                                        </td>

                                    </tr>
                                    <?php //if ($paystatus == 1) {
                                    ?>
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left">
                                            <h3 align="center"><u>POST-OPERATION NOTES</u></h3>
                                            <div>
                                                <?php
                                                echo $post_opt_notes . '<hr>';
                                                echo $post_opt_notes_entered_by; ?>
                                            </div>
                                        </td>
                                        <?php /// }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left">
                                            <h3 align="center"><u>FOLLOW UP PROCEDURES</u></h3>
                                            <div>
                                                <?php
                                                $fu = $db->prepare("SELECT * FROM procedures where old_procedure_id = ? ");
                                                $fu->execute([$procedure['sn']]);
                                                $fu = $fu->fetchAll(PDO::FETCH_ASSOC);
                                                ?>

                                                <?php if (count($fu) > 0): ?>
                                                    <h4>This Procedure has the following follow-up procedures :</h4>
                                                    <?php foreach ($fu as $f): ?>
                                                        <span><?php echo $f['procedures'] ?> &nbsp; <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($f["sn"])); ?>" target="_blank">[View This Follow-up Procedure]</a></span>

                                                    <?php endforeach ?>

                                                <?php endif ?>
                                            </div>
                                        </td>

                                    </tr>
                                </table>
                            </div>
                        </div>

                        <br>
                        <br>
                        <!-- <p class="text-center">
                                            <b>
                                            ______________________________ <br>
                                              Consultant/Head of Clinicals
                                            </b>
                            </p> -->

                    </div>
                    <hr>
                    <div class="row">

                        <div class="col-lg-12">
                            <?php if ($paystatus != 1) { ?><h3>Not Paid <h3><?php }

                                                                        $cr = 0;
                                                                        if ($paystatus == 0 && $cost <= $credit_limit) {
                                                                            $cr = 1;
                                                                            if ($credit_limit > 0) { ?>
                                            <h3 style="color:red;">Patient Credit Limit: <?= number_format($credit_limit); ?></h3>
                                        <?php } ?>
                                    <?php } ?>

                                    <a href="procedure.php?procedure&pr=<?= base64_encode(base64_encode($procedure_sn)); ?>" class="btn btn-default">
                                        <i class="fa fa-arrow"></i>&nbsp;Refresh Page</a>
                                    &nbsp; | &nbsp;
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#updatebookProcedureModal" <?= $disable_edit_button; ?>>Edit Request</button>
                                    &nbsp; | &nbsp;

                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#resource_persons_modal" <?= $disable2; ?>>
                                        Resource Persons</button>
                                    &nbsp; | &nbsp;
                                    <?php

                                    $cost = $procedure["cost"];

                                    if ($paystatus == 1 or $cost <= $credit_limit) { ?>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pre_opt_modal" <?= $disable; ?>>
                                            Pre-Operation Note</button>
                                        &nbsp; | &nbsp;
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#anas_opt_modal" <?= $disable; ?>>
                                            Anesthesia Note</button>
                                        &nbsp; | &nbsp;
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#post_opt_modal" <?= $disable2; ?>>
                                            Post-Operation Note</button>
                                        &nbsp; | &nbsp;
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#bookFolloUpProcedureModal" data-_follow_up_patient_id="<?php echo $hospital_no ?>" data-_old_procedure_name="<?php echo $procedure['procedures'] ?>" data-_old_procedure_id="<?php echo $procedure['sn'] ?>"> + Follow Up Procedure </button> &nbsp; | &nbsp;
                                        <a href="#" class="btn btn-white" onclick="ClickheretoprintDiv('printable-area')"> <i class="fa fa-print"></i> Print </a>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pre_opt_modal" <?= $disable; ?>>
                                            Pre-Operation Note
                                        </button>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#anas_opt_modal" <?= $disable; ?>>
                                            Anesthesia Note
                                        </button>
                                    <?php } ?>
                                    &nbsp; | &nbsp;
                                    <a href="#<?= $procedure_sn; ?>" class="btn btn-white " data-toggle="modal" data-target="#addHLAResultModal<?= $procedure_sn; ?>"><b>Upload Documents</b></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>



<?php include_once('resource_people_modal.php'); ?>

<div class="modal inmodal fade" id="change_item_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>
            <div class="modal-body" id="change_item_body"></div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="pre_opt_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" style="min-height: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""> PRE-OPERATION NOTE</h4>
            </div>
            <div class="modal-body">
                <div class="form_sep">
                    <h3>Select Note Template</h3>
                    <select class="input-sm chosen-select " onChange="load_template()" id="template_id1" style="width:350px;">
                        <option value="blank"> Blank Note </option>
                        <?php foreach ($templates as $key => $template) { ?>
                            <option value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form_sep">
                    <h3> Enter Note Below: </h3>
                    <div name="pre_opt_notes" id="pre_opt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px;">
                        <?php echo $pre_opt_notes; ?>
                    </div>
                </div>


                <div class="modal-footer">
                    <input type="hidden" name="hospital_no" id="hospital_no" value="<?php echo $hospital_no; ?>">
                    <input type="hidden" name="sn" id="sn" value="<?php echo $procedure_sn; ?>">
                    <input type="hidden" name="sn_code" id="sn_code" value="<?php echo base64_encode(base64_encode($procedure_sn)); ?>">
                    <button class="btn btn-primary" id="pre_operation_btn" onClick="pre_operation_save()">Save Documentation</button>
                    <button class="btn btn-danger" data-dismiss="modal">Close </button>
                </div>


            </div>
        </div>
    </div>

    <div class="modal inmodal fade" id="anas_opt_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-xl" style="min-height: 500px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Anesthesia Note</h4>
                </div>
                <div class="modal-body">
                    <div class="form_sep">
                        <h3>Select Note Template</h3>
                        <select class="input-sm chosen-select " onChange="load_template()" id="template_id1" style="width:350px;">
                            <option value="blank"> Blank Note </option>
                            <?php foreach ($templates as $key => $template) { ?>
                                <option value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep">
                        <h3> Enter Note Below: </h3>
                        <div name="anas_opt_notes" id="anas_opt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px;">
                            <?php echo $anas_opt_notes; ?>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <input type="hidden" name="hospital_no" id="hospital_no" value="<?php echo $hospital_no; ?>">
                        <input type="hidden" name="sn" id="sn" value="<?php echo $procedure_sn; ?>">
                        <input type="hidden" name="sn_code" id="sn_code" value="<?php echo base64_encode(base64_encode($procedure_sn)); ?>">
                        <button class="btn btn-primary" id="anas_operation_btn" onClick="anas_operation_save()">Save Documentation</button>
                        <button class="btn btn-danger" data-dismiss="modal">Close </button>
                    </div>


                </div>
            </div>
        </div>



        <div class="modal inmodal fade" id="post_opt_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-xl" style="min-height: 500px; margin: 0px auto;">
                <div class="modal-content">
                    <form action="<?= $editFormAction; ?>" method="post" id="pre_opt_note_form">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">POST-OPERATION NOTE</h4>
                        </div>
                        <div class="" style="padding: 20px;">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form_sep">
                                        <h3> Operation Date/Time </h3>
                                        <p><input type="datetime-local" id="performed_date" min="<?= date('Y-m') . '-01'; ?>T08:30" max="<?= date('Y') + (1); ?>-01-30T16:30" name="performed_date" value="<?= date('Y-m-d') . 'T' . date('H:i'); ?>" class="form-control"></p>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form_sep">
                                        <h3> Post - Operation Outcome/Result: </h3>
                                        <select class="input-sm form-control " name="post_op_results" id="post_op_results" style="width:350px; font-size: 15px;" required>
                                            <option value="" selected> Select Outcome </option>
                                            <option value="Successful" <?php echo ($post_op_results == 'Successful' ? 'selected' : ''); ?>>Successful </option>
                                            <option value="Unsuccessful" <?php echo ($post_op_results == 'Unsuccessful' ? 'selected' : ''); ?>>Unsuccessful </option>
                                            <option value="Death" <?php echo ($post_op_results == 'Death' ? 'selected' : ''); ?>>Death </option>
                                            <option value="Cancelled" <?php echo ($post_op_results == 'Cancelled' ? 'selected' : ''); ?>>Cancelled </option>
                                            <option value="Not Applicable" <?php echo ($post_op_results == 'Not Applicable' ? 'selected' : ''); ?>>Not Applicable </option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="form_sep">
                                <h3>Select Note Template</h3>
                                <select class="input-sm chosen-select " onChange="load_template2()" id="template_id2" style="width:350px;">
                                    <option value="blank"> Blank Note </option>
                                    <?php foreach ($templates as $key => $template) { ?>
                                        <option value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form_sep">
                                <h3> Enter Note Below: </h3>
                                <div name="post_opt_notes" id="post_opt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px;">
                                    <?php echo $post_opt_notes; ?>
                                </div>
                            </div>


                            <br>
                            <div class="text-right" style="padding: 20px;">
                                <input type="hidden" id="post_opt_notes_id" value="<?php echo $post_opt_notes_id; ?>">
                                <input type="hidden" id="post_opt_notes_old" value="<?php echo $post_opt_notes; ?>">
                                <input type="hidden" name="hospital_no" id="hospital_no" value="<?php echo $hospital_no; ?>">
                                <input type="hidden" name="sn" id="sn" value="<?php echo $procedure_sn; ?>">
                                <input type="hidden" id="service_id" value="<?php echo $service_id; ?>">
                                <input type="hidden" id="cr" value="<?php echo $cr; ?>">
                                <input type="hidden" id="sale_no" value="<?php echo $sale_no; ?>">
                                <input type="hidden" name="sn_code" id="sn_code" value="<?php echo base64_encode(base64_encode($procedure_sn)); ?>">
                                <button class="btn btn-primary" id="post_operation_btn" onClick="post_operation_save()">Save Documentation</button>
                                <button class="btn btn-danger" data-dismiss="modal">Close </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="modal inmodal fade" id="updatebookProcedureModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="">Update Procedure Booking</h4>
                    </div>

                    <div class="modal-body">

                        <form method="post" id="subject" action="<?php echo $editFormAction; ?>">

                            <div class="form_sep">
                                <label> Describe Reason for Procedure, Previous Procedure(s) & Other Notes</label>
                                <textarea name="reason_procedure" rows="4" cols="50" class="form-control" placeholder="Enter your comments here..."><?= $procedure['primary_diag']; ?></textarea>
                                <input type="hidden" name="old_reason" value="<?= $procedure['primary_diag']; ?>">
                            </div>

                            <div class="form_sep">
                                <label> Procedure Date/Time </label>
                                <?php
                                $sDate = $procedure['sDate'];
                                $dateValue = date('Y-m-d\TH:i', strtotime($sDate));
                                ?>
                                <input type="datetime-local" id="start_date" min="<?= date('Y-m') . '-01T08:30'; ?>" max="<?= date('Y') + 1; ?>-01-30T16:30" name="start_date" value="<?= $dateValue; ?>" class="form-control">
                                <input type="hidden" name="old_date" value="<?= $procedure['sDate']; ?>">
                            </div>
                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Doctor/Consultant</label>
                                <select name="update_doctor" class="input-sm chosen-select" style="width:350px;" required>
                                    <option selected="selected" value="">Search </option>
                                    <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'AD' OR rights = 'DR' OR rights = 'MD') AND  status='1' order by fullname");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                        <option value="<?php echo $row["id"] . '__' . $row["fullname"]; ?>" <?= ($row["fullname"] == $procedure['consultant_name'] ? 'selected' : ''); ?>><?php echo $row["fullname"]; ?></option>
                                    <?php endwhile ?>
                                </select>
                                <input type="hidden" name="old_consultant_name" value="<?= $procedure['consultant_name']; ?>">
                            </div>

                            <div class="form_sep">
                                <label class="req"> Require Theater Use: </label>
                                <select class="form-control" name="require_theater">
                                    <option value="No" selected>Select</option>
                                    <option value="Yes" <?= $procedure['require_theater'] == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                    <option value="No" <?= $procedure['require_theater'] == 'No' ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>

                            <div class="form_sep" id="theater_section">
                                <label for="theater_select" class="req">Select Theater:</label>
                                <select class="form-control" id="theater_select" name="theater_select">
                                    <option value="">Select Theater</option>
                                    <option value="Other">Other (Please specify)</option>
                                    <?php
                                    $stmt = $db->prepare("SELECT distinct theater FROM procedures WHERE theater!='' ORDER BY theater ASC");
                                    $stmt->execute();
                                    if ($stmt->execute() > 0) {
                                        while ($service = $stmt->fetch()) { ?>
                                            <option value="<?= $service["theater"]; ?>" <?= $procedure['theater'] == $service["theater"] ? 'selected' : ''; ?>> <?= $service["theater"]; ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form_sep">
                                <label class="req"> Estimate time for Procedure: </label>
                                <select class="form-control" name="procedure_time" id="procedure_time" required>
                                    <option value="" <?= empty($procedure['procedure_time']) ? 'selected' : ''; ?>>Select</option>
                                    <option value="Not Certain" <?= $procedure['procedure_time'] == 'Not Certain' ? 'selected' : ''; ?>>Not Certain</option>
                                    <option value="Less Than 1hr" <?= $procedure['procedure_time'] == 'Less Than 1hr' ? 'selected' : ''; ?>>Less Than 1hr</option>
                                    <option value="1hr" <?= $procedure['procedure_time'] == '1hr' ? 'selected' : ''; ?>>1hr</option>
                                    <option value="1 to 2hrs" <?= $procedure['procedure_time'] == '1 to 2hrs' ? 'selected' : ''; ?>>1 to 2hrs</option>
                                    <option value="2 to 3hrs" <?= $procedure['procedure_time'] == '2 to 3hrs' ? 'selected' : ''; ?>>2 to 3hrs</option>
                                    <option value="3 to 4hrs" <?= $procedure['procedure_time'] == '3 to 4hrs' ? 'selected' : ''; ?>>3 to 4hrs</option>
                                    <option value="4 to 5hrs" <?= $procedure['procedure_time'] == '4 to 5hrs' ? 'selected' : ''; ?>>4 to 5hrs</option>
                                    <option value="5 to 6hrs" <?= $procedure['procedure_time'] == '5 to 6hrs' ? 'selected' : ''; ?>>5 to 6hrs</option>
                                    <option value="6 to 7hrs" <?= $procedure['procedure_time'] == '6 to 7hrs' ? 'selected' : ''; ?>>6 to 7hrs</option>
                                    <option value="7 to 8hrs" <?= $procedure['procedure_time'] == '7 to 8hrs' ? 'selected' : ''; ?>>7 to 8hrs</option>
                                    <option value="8 to 9hrs" <?= $procedure['procedure_time'] == '8 to 9hrs' ? 'selected' : ''; ?>>8 to 9hrs</option>
                                    <option value="9 to 10hrs" <?= $procedure['procedure_time'] == '9 to 10hrs' ? 'selected' : ''; ?>>9 to 10hrs</option>
                                    <option value="More than 10hrs" <?= $procedure['procedure_time'] == 'More than 10hrs' ? 'selected' : ''; ?>>More than 10hrs</option>
                                </select>
                            </div>


                            <div class="form_sep">

                                <div class="pull-left">
                                    <button type="submit" class="btn btn-success btn btn-sm" name="update_book_procedure" id="">Update Request</button>
                                </div>

                                <div class="pull-right">
                                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>

                                </div>
                            </div>

                    </div>

                    <input type="hidden" name="remarks" value="<?php echo $service["indication"]; ?>">
                    <input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>">
                    <input type="hidden" name="sn_procedure_sn" value="<?php echo $procedure_sn; ?>">
                    <input type="hidden" name="redirect" value="procedure&pr=<?= $_GET['pr']; ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal inmodal fade" id="addHLAResultModal<?= $procedure_sn; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">UPLOAD DOCUMENTS</h4>
                </div>
                <div class="modal-body" style="min-height: 300px;">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="hospital_no" value="<?= $hosp_no; ?>" required><br>
                        <input type="hidden" name="transplant_id" value="<?= $procedure_sn; ?>" required><br>
                        <label for="result_type">Title of Document:</label>
                        <input class="form-control" type="text" name="result_type" required><br>

                        <label for="result_file">Upload Result File:</label>
                        <input class="form-control" type="file" name="result_file" accept=".pdf, .jpg, .jpeg, .png" required><br>

                        <button type="submit" class="btn btn-primary" name="add_procedure_doc" style="display: block; width:100%;">Submit</button>
                    </form>

                    <hr>
                    <?php
                    $patient_docs = $Document->get(['hospital_no' => $hosp_no], true);
                    if (count($patient_docs) > 0) { ?>

                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Title</th>
                                    <th>Date Uploaded</th>
                                    <th>Uploaded By</th>
                                    <th>Document</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sn = 1;
                                foreach ($patient_docs as $key => $patient_doc) {
                                    $uploaded_by = null;
                                    $user = $AdminUser->find($patient_doc->created_by);

                                    if (!empty($user)) {
                                        $uploaded_by = $user->fullname;
                                    }
                                ?>
                                    <tr>
                                        <th><?= $sn++; ?></th>
                                        <th><?= $patient_doc->title; ?></th>
                                        <th><?= date('d M, Y', strtotime($patient_doc->created_at)); ?></th>
                                        <th><?= $uploaded_by; ?></th>
                                        <th><a href="<?= $patient_doc->link; ?>" target="_BLANK"> View Document</a></th>

                                        <th>
                                            <?php
                                            $created_time = strtotime($patient_doc->created_at);
                                            $now = time();
                                            $within_60days = ($now - $created_time) < (30 * 24 * 60 * 60); // 5,184,000 seconds = 60 days
                                            if (
                                                (trim($_SESSION['fullname']) == trim($user->fullname) && $within_60days)
                                                || $_SESSION['delete_doc'] == 1
                                            ) {
                                            ?>
                                                <a href="procedure.php?procedure&pr=<?= $_GET['pr'] ?>&third_party_c=<?= base64_encode($patient_doc->id); ?>"
                                                    onclick="return confirm('Are you sure you want to delete this document?');">
                                                    Delete
                                                </a>
                                            <?php
                                            }
                                            ?>


                                        </th>

                                    </tr>
                            <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>



    <script>
        var appointment_number = '<?php echo $appointment_number; ?>';
    </script>