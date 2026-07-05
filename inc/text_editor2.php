<?php session_start();
require_once('../Connections/Conn.php');

if (isset($_POST["load_template"])) {

    $stmtx = $db->prepare("SELECT template,id FROM services_templates WHERE id=:doc_template");
    $stmtx->bindValue(':doc_template', $_POST["load_template"], PDO::PARAM_STR);
    $stmtx->execute();
    $stmt_logCOUNT = $stmtx->rowCount();
    if ($stmt_logCOUNT > 0) {
        $rowx = $stmtx->fetch(PDO::FETCH_ASSOC);
        echo $sn = $rowx['template'];
    } else {
        echo 'Type Notes';
    }
}


if (isset($_POST["template_id"])) {

    $stmtx = $db->prepare("SELECT template FROM services_templates WHERE id=:doc_template");
    $stmtx->bindValue(':doc_template', $_POST["template_id"], PDO::PARAM_STR);
    $stmtx->execute();
    $stmt_logCOUNT = $stmtx->rowCount();
    if ($stmt_logCOUNT > 0) {
        $rowx = $stmtx->fetch(PDO::FETCH_ASSOC);
        echo $rowx['template'];
    } else {
        echo 'Type Notes';
    }
}


if (isset($_POST['save_pre_operation'])) {

    $procedure_sn = $_POST['sn'];
    $hospital_no = $_POST['hospital_no'];
    $pre_opt_notes = $_POST['pre_opt_notes'];

    $patient_procedure_stmt = $db->prepare("SELECT * from procedures WHERE sn=? ORDER BY sn DESC LIMIT 1");
    $patient_procedure_stmt->execute(array($procedure_sn));
    if ($patient_procedure_stmt->rowCount() > 0) {
        $procedure = $patient_procedure_stmt->fetch(PDO::FETCH_ASSOC);
        $app_no = $procedure['app_no'];
        $pre_opt_notes_id = $procedure['pre_opt_notes_id'];

        if (empty($pre_opt_notes_id)) {
            $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by,dept_id)  VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?)');
            $save_note = $stmt->execute(
                array(
                    $app_no,
                    $hospital_no,
                    $pre_opt_notes,
                    'DR',
                    'pre_opt_notes',
                    $_SESSION['fullname'],
                    '',
                    $_SESSION["id"],
                    $_SESSION["dept_id"]
                )
            );
            if ($save_note == true) {
                $id = $db->lastInsertId();
                $stmt = $db->prepare("UPDATE procedures SET pre_opt_notes_id=? WHERE sn=? ");
                $stmt->execute(array($id, $procedure_sn));
            }
        } else {
            $stmt = $db->prepare("UPDATE notes SET notes=? WHERE sn=? ");
            $save_note =  $stmt->execute(array($pre_opt_notes, $pre_opt_notes_id));
        }

        if ($save_note == true) {
            //  $error_status = 2;
            echo $error_msg = 'Notes Saved Successfully';
        } else {

            $error_status = 1;
            $error_msg = 'Something Went Wrong ...';
        }
    }
    exit;
}


if (isset($_POST['save_post_operation'])) {
    $procedure_sn = $_POST['sn'];
    $hospital_no = $_POST['hospital_no'];
    $post_opt_notes  = trim($_POST['post_opt_notes']);
    $post_op_results = $_POST['post_op_results'];
    $service_id = $_POST['service_id'];
    $cr = $_POST['cr'];
    $sale_no = $_POST['sale_no'];
    $post_opt_notes_id = $_POST['post_opt_notes_id'];
    $post_opt_notes_old = trim($_POST['post_opt_notes_old']);

    $date_time = preg_split('/T/', $_POST['performed_date']);
    $ap_date = $date_time[0];
    $ap_time = $date_time[1];
    $performed_date = $ap_date . ' ' . $ap_time;


    if (strlen($post_opt_notes) > 20) {

        $patient_procedure_stmt = $db->prepare("SELECT * from procedures WHERE sn=? ORDER BY sn DESC LIMIT 1");
        $patient_procedure_stmt->execute(array($procedure_sn));
        if ($patient_procedure_stmt->rowCount() > 0) {
            $procedure = $patient_procedure_stmt->fetch(PDO::FETCH_ASSOC);

            $app_no = $procedure['app_no'];
            $post_opt_notes_id = $procedure['post_opt_notes_id'];

            if (empty($post_opt_notes_id)) {
                $stmt = $db->prepare('INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by,dept_id)  VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?)');
                $save_note = $stmt->execute(
                    array(
                        $app_no,
                        $hospital_no,
                        $post_opt_notes,
                        'DR',
                        'post_opt_notes',
                        $_SESSION['fullname'],
                        '',
                        $_SESSION["dept_id"],
                        $_SESSION["id"]
                    )
                );
                if ($save_note == true) {
                    $id = $db->lastInsertId();
                    $stmt = $db->prepare("UPDATE procedures SET post_opt_notes_id=?, performed_date=? WHERe sn=? ");
                    $stmt->execute(array($id, $performed_date, $procedure_sn));
                }
            } else {
                $stmt = $db->prepare("UPDATE notes SET notes=? WHERe sn=? ");
                $save_note =  $stmt->execute(array($post_opt_notes, $post_opt_notes_id));

                $stmt = $db->prepare("UPDATE procedures SET post_op_results=?, performed_date = ? WHERe sn=? ");
                $stmt->execute(array($post_op_results, $performed_date, $procedure_sn));

                if (trim($post_opt_notes_old) != trim($post_opt_notes)) {
                    $stmt = $db->prepare('INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by,dept_id,status)  VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?, ?)');
                    $save_note = $stmt->execute(
                        array(
                            $app_no,
                            $hospital_no,
                            $post_opt_notes_old,
                            'DR',
                            'post_opt_notes',
                            $_SESSION['fullname'],
                            '',
                            $_SESSION["dept_id"],
                            $_SESSION["id"],
                            '0'
                        )
                    );
                }
            }

            if ($save_note == true) {
                $one = "1";
                $fullname = $_SESSION['fullname'];
                $fullname = str_replace("'", "", $fullname); // Consider using htmlspecialchars() instead for better security
                $stmt = $db->prepare("UPDATE patient_ap_services SET drug_status = ?, dsp_by = ?, cr = ? WHERE sn = ?");
                $stmt->execute(array($one, $fullname, $cr, $sale_no));

                $error_status = 2;
                $error_msg = 'Notes Saved Successfully';
                echo json_encode(["status" => $error_status, "message" => $error_msg]);
                exit;
            } else {
                $error_status = 1;
                echo $error_msg = 'Error : Something Went Wrong ...';
                echo json_encode(["status" => $error_status, "message" => $error_msg]);
                exit;
            }
        }
    } else {
        $error_status = 2;
        $error_msg = 'Error : Type More Text.';
        echo json_encode(["status" => $error_status, "message" => $error_msg]);
        exit;
    }
}
