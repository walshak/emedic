<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');
if (isset($_POST['save-new-note'])) {
    $hospital_no =  $_POST['hospital_no'];
    if (isset($_POST['pro_note'])) {
        $notes =  $_POST['pro_note'];
    } else if (isset($_POST['pro_note_' . $hospital_no])) {
        $notes =  $_POST['pro_note_' . $hospital_no];
    }

    $appointment_number =  $_POST['appointment_number'];

    $status = 401;
    $data = null;
    $message = "";
    $last_inserted_id = null;

    if (!empty($notes)) {
        $save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, 'DR', 'note',  $_SESSION['fullname'], $specialty = null, $created_by = $_SESSION["id"], $save_direct = true);
        if ($save_note) {
            $status = 200;
            $message = "Note is saved successfully...";

            $last_id_stmt = $db->prepare("SELECT * FROM notes where notes_type = 'note' AND hospital_no = ? ORDER BY sn DESC LIMIT 1 ");
            $last_id_stmt->execute(array($hospital_no));
            $row = $last_id_stmt->fetch(PDO::FETCH_ASSOC);
            $last_inserted_id = $row["sn"];


            $data = '
                    <div class="ibox">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>  Progress Note | <span style="color: #ccc">' . date("d, M Y") . '</span></h5>
                            </div>
                            <div class="col-md-4">
                                <h5 class="text-right">
                                
                                <button class="btn btn-sm btn-success btn-secondary" style="float:right" data-toggle="modal" data-target="#editProgressNoteModal_' . $last_inserted_id . '"> <i class="fa fa-edit"></i> Edit Note </button>

                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content" id="progress_note_content_' . $last_inserted_id . '">
                        ' . $notes . '
                    </div>
                    </div>

                    <div class="modal inmodal fade" id="editProgressNoteModal_' . $last_inserted_id . '" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
                    <div class="modal-dialog modal-lg" style="width: 65%;">
                        <div class="modal-content">
                        <form action="#" method="post" onsubmit="return false" id="update_progress_note_form_' . $last_inserted_id . '">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title" id="">Edit Note</h4>
                            </div>
                            <input type="hidden" name="update-note" value="' . $hospital_no . '">
                            <input type="hidden" name="id" value="' . $last_inserted_id . '">
                            <div class="modal-body" id="editProgressNoteModal_' . $last_inserted_id . '_wrap">
                                <textarea name="pro_note" id="pro_note_edit_' . $last_inserted_id . '" cols="45" rows="5" maxlength="160" class="summernote" placeholder="Type Your Message Here"> ' . $notes . ' </textarea>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-full btn-primary" onclick="updateProgressNote(' . $last_inserted_id . ')">Save Note</button>
                            </div>
                            </form>
                        </div>
                    </div>
                    </div>
                    ';
        }
    } else {
        $message = "Note is empty";
    }

    echo json_encode(["status" => $status, "data" => $data, "message" => $message, "id" => $last_inserted_id]);
    exit;
}
if (isset($_POST['update-note'])) {

    $status = 401;
    $message = null;
    $data = null;
    if (isset($_POST['pro_note'])) {
        $notes =  $_POST['pro_note'];
        $id =  $_POST['id'];

        if (!empty($notes)) {
            $update_stmt = $db->prepare("UPDATE notes SET notes = ? ,status = '1' WHERE sn = ? ");
            $update =  $update_stmt->execute(array($notes, $id));
            if ($update == true) {
                $message = "Notes saved successfully...";
                $status = 200;
                $data = $notes;
            }
        }
    }

    echo json_encode(["status" => $status, "data" => $data, "message" => $message, "id" => null]);
    exit;
}
