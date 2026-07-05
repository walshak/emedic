<?php
/////////////////////////// TAB AND CONSULTATION NOTE SUBMISSION//////////////////////
$mgt_notes = null;
$mode_typpe = null;


$mgt_template = null;
$template_id = null;
if (isset($_POST["load-mgt-template"])) {
    $current_tab = 'mgt';

    if ($_POST['template_select'] == 'blank') {
        $mgt_template = ' -- Blank --';
    } else {
        $template_id = intVal(cleanInput($_POST['template_select']));
        $stmt = $db->prepare("SELECT template FROM  services_templates  WHERE id = ? ");
        $stmt->execute(array($template_id));
        if ($stmt->rowCount() > 0) {
            $mgt_template_arr = $stmt->fetch(PDO::FETCH_ASSOC);
            $mgt_template = $mgt_template_arr['template'];
        }
    }
}

//// nursing module   progress_edit_mode
if (isset($_POST['mode_typpe']) and $_POST['mode_typpe'] != '') {
    $mode_typpe = $_POST['mode_typpe'];
}


if (isset($_POST['save_progress_note_button']) or $mode_typpe == 'save_progress_note_button') {
    $error_status = 1;
    $error_msg = 'Error : Progress Note Not Saved';

    $hospital_no = $_POST['hospital_no'];
    $notes = $_POST['mgt_notes'];
    $appointment_number = $_POST['appointment_number'];

    if ($mode_typpe == 'save_progress_note_button') {
        $tag = 'NS';
        $current_tab = 'progress';
    } else {
        $tag = 'DR';
        $current_tab = 'adm';
    }

    $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, $tag, 'note',  $_SESSION['fullname'], null,  $_SESSION["id"]);
    if ($save_note) {
        $error_status = 2;
        $error_msg = 'Success : Progress Notes Saved Successfully!';
    }
    lockApppointment($db, $appointment_number);
}

////////////// Delete note action
if (isset($_POST['delete-note-btn'])) {
    $error_status = 1;
    $error_msg = 'Error : Something went wrong';
    $sn = intval($_POST['id']);
    $service_id = intval($_POST['service_id']);
    $notes_type = $_POST['notes_type'];
    $app_no = $_POST['app_no'];


    $delete = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
    $deleted = $delete->execute(array($sn));

    if ($deleted) {
        if ($notes_type == 'serv_review') {
            /// delete payment
            $delete = $db->prepare("DELETE FROM  patient_ap_services  WHERE app_no = ? AND drug_sn = ?  AND serv_group = 'Review' ");
            $deleted = $delete->execute(array($app_no, $service_id));
        }
        $error_status = 2;
        $error_msg = 'Success : Note Deleted';
    }
}


///////////////////// Update note action
if (isset($_POST['save_edit_note_btn']) or $mode_typpe == 'progress_edit_mode') {
    $error_status = 1;
    $error_msg = 'Error : Note Not Saved!!!!';
    $sn = intval($_POST['id']);

    if ($mode_typpe == 'progress_edit_mode') {
        $notes = $_POST['mgt_notes'];
        $current_tab = 'progress';
    } else {
        $notes = $_POST['edit_note_editor'];
    }

    if (!empty($notes)) {

        $check = $db->prepare("SELECT * FROM notes WHERE sn = ? ");
        $check->execute(array($sn));
        if ($check->rowCount() > 0) {
            $row = $check->fetch(PDO::FETCH_ASSOC);
            $prev_note = $row['notes'];

            if ($notes != $prev_note) {
                $update = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
                $update->execute(array($sn));

                $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, service_id)  VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?)');
                $updated = $stmt->execute(
                    array(
                        $row['app_no'],
                        $row['hospital_no'],
                        $notes,
                        $row['tag'],
                        $row['notes_type'],
                        $_SESSION['fullname'],
                        $row['specialty'],
                        $_SESSION['id'],
                        $row['service_id']
                    )
                );

                if ($updated) {
                    $error_status = 2;
                    $error_msg = 'Success : Note Saved Successfully!';
                }
            } else {
                $error_msg = 'Error : Note same as Previous Note!';
            }
        } else {
            $error_msg = 'Error : Note not found!';
        }
    }
}



///////////////////// SEE SPECIALIST REQUEST //////////////////
if (isset($_POST['see_a_specialist_btn'])) {
    $error_status = 1;
    $error_msg = 'Error : Request note not sent';

    $hospital_no = cleanInput($_POST['hosp_no']);
    $specialist = cleanInput($_POST['specialist']);

    $notes = cleanInput($_POST['request_note_description']);
    if ($specialist != '') {
        $notes = $notes . '<br>' . $specialist;
    }
    $appointment_number = cleanInput($_POST['app_no']);
    $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, 'DR', 'REQ_REMINDER',  $_SESSION['fullname'], null,  $_SESSION["id"]);
    if ($save_note) {
        $error_status = 2;
        $error_msg = 'Success : Request sent';

        lockApppointment($db, $appointment_number);
    }
}






if (isset($_GET['dlv'])) {
    $dlv = $_GET['dlv'];

    $insertSQL = 'DELETE FROM  v_bp WHERE sn = ? ';
    $sql = $db->prepare($insertSQL);
    $save = $sql->execute(array($dlv));

    $error_status = 1;
    $error_msg = 'Error : Unable to Delete';

    if ($save == true) {
        $error_status = 2;
        $error_msg = 'Success : Deleted';
        $adm_status = 5;
    }
}
