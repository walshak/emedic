<?php
session_start();
include("../Connections/Conn.php");
///include("_session.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');



if (isset($_POST['delete_notes'])) {

    $response = array(
        'status' => 0,
        'message' => ''
    );

    $id = $_POST['delete_notes'];
    $sale_sn = $_POST['sale_sn'];

    $check = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = ? and paystatus = '0' ");
    $check->execute(array($sale_sn));
    if ($check->rowCount() > 0) {
        /// delete not paid yet
        $row = $check->fetch(PDO::FETCH_ASSOC);
        $app_no = $row['app_no'];
        $hospital_no = $row['hospital_no'];
        $service_id = $row['drug_sn'];

        $delete = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
        $deleted = $delete->execute(array($id));

        if ($deleted) {
            // if($notes_type == 'serv_review'){
            /// delete payment
            $delete = $db->prepare("DELETE FROM  patient_ap_services  WHERE hospital_no = ? AND app_no = ? AND drug_sn = ?  AND serv_group = 'Review' ");
            $deleted = $delete->execute(array($hospital_no, $app_no, $service_id));
            //  }
            $response['message'] = 'Successful!';
            $response['status'] = '0';
            echo  json_encode($response);
            exit;
        }

        $response['message'] = 'Not Successful!';
        $response['status'] = '1';
        echo  json_encode($response);
        exit;
    } else {
        $response['message'] = 'Not Successful!';
        $response['status'] = '1';
        echo  json_encode($response);
    }
    exit;
}


if (isset($_POST['notify_requester'])) {

    try {
        $response = array(
            'status' => 0,
            'message' => ''
        );
        // Define the date_time variable
        $date_time = date('Y-m-d'); // Assuming you want to use the current date and time

        // Search for an existing record
        $searchSQL = "SELECT * FROM pharm_doctor_notes 
        WHERE drug_name = :drug_name AND date(date_time) = :date_time AND sender =:sender";
        $searchStmt = $db->prepare($searchSQL);
        $searchStmt->bindParam(':drug_name', $_POST['drug_name_'], PDO::PARAM_STR);
        $searchStmt->bindParam(':date_time', date('Y-m-d', strtotime($date_time)), PDO::PARAM_STR);
        $searchStmt->bindParam(':sender', $_SESSION['fullname'], PDO::PARAM_STR);
        $searchStmt->execute();
        $existingRecord = $searchStmt->fetch();

        if ($existingRecord) {

            $noteee = "<br>" . '<i>' . $_SESSION['fullname'] . ': </i>' . $_POST['drug_note'];
            // Record exists, concatenate new drug_note and update
            $updateSQL = "UPDATE pharm_doctor_notes SET notes = CONCAT(notes, :new_notes), view_status=0
            WHERE hospital_no = :hospital_no AND drug_name = :drug_name AND date(date_time) = :date_time";
            $updateStmt = $db->prepare($updateSQL);
            $updateStmt->bindParam(':new_notes', $noteee, PDO::PARAM_STR); // Concatenate with a newline character
            $updateStmt->bindParam(':hospital_no', $_POST['notify_requester'], PDO::PARAM_STR);
            $updateStmt->bindParam(':drug_name', $_POST['drug_name_'], PDO::PARAM_STR);
            $updateStmt->bindParam(':date_time', $date_time, PDO::PARAM_STR);
            $updateStmt->execute();
        } else {
            // Record does not exist, insert new row
            $insertSQL = "INSERT INTO pharm_doctor_notes (hospital_no, patient_name, drug_name, sender, reciever, notes, date_time, view_status) 
                  VALUES (:hospital_no, :patient_name,:drug_name, :sender, :reciever, :notes, :date_time, 0)";
            $stmt = $db->prepare($insertSQL);
            $stmt->bindParam(':hospital_no', $_POST['notify_requester'], PDO::PARAM_STR);
            $stmt->bindParam(':patient_name', $_POST['patient_name'], PDO::PARAM_STR);
            $stmt->bindParam(':drug_name', $_POST['drug_name_'], PDO::PARAM_STR);
            $stmt->bindParam(':sender', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt->bindParam(':reciever', $_POST['reciever'], PDO::PARAM_STR);
            $stmt->bindParam(':notes', $_POST['drug_note'], PDO::PARAM_STR);
            $stmt->bindParam(':date_time', $date_time, PDO::PARAM_STR);
            $stmt->execute();
        }

        // If execution is successful
        $response['message'] = 'Successful!';
        $response['status'] = 0; // Usually, 1 indicates success
    } catch (PDOException $e) {
        // If an error occurs
        $response['message'] = 'Error: ' . $e->getMessage();
        $response['status'] = 1;
    }

    // Return the response as JSON
    echo json_encode($response);
    exit;
}


if (isset($_POST['edit_sn'])) {

    $response = array(
        'notes' => '',
        'date_entry2' => '',
        'date_entry' => '',
        'notes_sn' => ''
    );

    $edit_sn = $_POST['edit_sn'];
    $stmt_getd = $db->prepare("SELECT notes,date_entry2,date_entry2 FROM notes WHERE sn = '$edit_sn'");
    $stmt_getd->execute();
    $rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC);
    $response['notes'] = $rwxx['notes'];
    $response['date_entry2'] = $rwxx['date_entry2'];
    $response['date_entry2'] = $rwxx['date_entry2'];
    $response['notes_sn'] = $edit_sn;
    echo  json_encode($response);
    exit;
}


if (isset($_POST['pharm_doctor_chat'])) {


    /// pharm_doctor_chat:patient_hosp_no,drug_name_:drug_name_

    $hospital_no = $_POST['pharm_doctor_chat'];
    $drug_name_ = $_POST['drug_name_'];
    $sn = 1;
?>


    <div style=" max-height:300px; overflow:auto">
        <table class="table table-bordered table-striped table-hover table-responsive">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Details</th>
                    <th>.</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $stmt_getd = $db->prepare("SELECT * FROM pharm_doctor_notes WHERE hospital_no = '$hospital_no' AND drug_name='$drug_name_' order by sn desc");
                $stmt_getd->execute();
                while ($rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC)) {
                    $rwxx_sn = $rwxx["sn"];



                ?>
                    <tr>
                        <td><?= $sn++; ?></td>
                        <td><?php echo $rwxx["notes"] . '<br><b><u>' . $rwxx["reciever"] . '</u> / ' .
                                date("d-m-Y H:i", strtotime($rwxx["date_time"])) . '</b>'; ?></td>
                        <td><?php if (date_diff_day($rwxx['date_time']) <= 1 && $_SESSION['fullname'] == $rwxx['sender'] && $rwxx['is_delete'] == 0) { ?>
                                <button type="button" class="btn btn-xs btn-danger" id="add" onclick="delete_notes_chat('<?= $rwxx_sn; ?>')"><i class="fa fa-plus"></i>&nbsp;Delete</button>

                                <?php echo $rwxx['view_status'] == 1 ? 'Seen' : 'Pending'; ?>

                            <?php  }    ?>





                        </td>
                    </tr>
                <?php    } ?>
            </tbody>
        </table>
    </div>
<?php }


if (isset($_POST['patient_review_doc'])) {

    $hospital_no = $_POST['patient_review_doc'];
    $sn = 1;
?>


    <div style=" max-height:300px; overflow:auto">
        <table class="table table-bordered table-striped table-hover table-responsive">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $stmt_getd = $db->prepare("SELECT * FROM notes WHERE hospital_no = '$hospital_no' AND status='1' and tag='PH' order by sn desc");
                $stmt_getd->execute();
                while ($rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                        <td><?= $sn++; ?></td>
                        <td><?php
                            echo $rwxx["notes"]; ?>
                            <br>
                            <small><b><i>Entered by: <?php echo $rwxx['prepared_by']; ?>
                                        Date: <?php echo formatDateTime_($rwxx['date_entry']); ?></i></b></small>
                            <br>

                            <?php if (date_diff_day($rwxx['date_entry']) < 1 && $_SESSION['fullname'] == $rwxx['prepared_by']) { ?>
                                <button type="button" class="btn btn-xs btn-warning" id="edit" onclick="edit_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-edit"></i>&nbsp;Edit</button>
                            <?php  }    ?>

                            <?php if (date_diff_day($rwxx['date_entry']) < 1 && $_SESSION['fullname'] == $rwxx['prepared_by']) { ?>
                                <button type="button" class="btn btn-xs btn-danger" id="add" onclick="delete_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-plus"></i>&nbsp;Delete</button>
                            <?php  }    ?>





                        </td>
                    </tr>
                <?php    } ?>
            </tbody>
        </table>
    </div>
<?php    } ?>

<?php

if (isset($_POST['patient_review'])) {

    $sn = 1;
    $total_srvices = 0;
    $hospital_no = $_POST['patient_review'];



    $stmt = $db->prepare("SELECT 
	sn, paystatus, pay, item_services, drug_sn, app_no,pay_mode,claim_amt,created_by,date_entry, prepared_by
	FROM patient_ap_services 
	WHERE serv_group = 'Review' AND paystatus = 0 AND hospital_no = $hospital_no order by sn desc limit 15 ");
    $stmt->execute();
    if ($stmt->rowCount() > 0) { ?>


        <div style=" max-height:300px; overflow:auto">
            <table class="table table-bordered table-striped table-hover table-responsive">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($pending_service = $stmt->fetch()) {

                        $app_no = $pending_service["app_no"];
                        $sale_sn = $pending_service["sn"];
                        $created_by = $pending_service["created_by"];
                        $prepared_by = $pending_service["prepared_by"];
                        $drug_sn = $pending_service["drug_sn"];
                        $date_entry = $pending_service["date_entry"];
                        $date_entry = substr($date_entry, 0, -9);


                        $stmt_getd = $db->prepare("SELECT * FROM notes WHERE app_no = '$app_no'  AND created_by = '$created_by' AND hospital_no = '$hospital_no' AND service_id = '$drug_sn' AND date(date_entry) = '$date_entry' AND status='1' order by sn desc");
                        $stmt_getd->execute();
                        while ($rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC)) {



                            if ($rwxx['date_entry2'] != '') {
                                $date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($rwxx['date_entry2'])) . ' & ';
                                $date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($rwxx['date_entry'])) . '<br>';
                                $date_ .= "<b>Number of Edits </b>" .  $rwxx['no_updates'];
                            } else {
                                $date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($rwxx['date_entry'])) . '<br>';
                            }
                            $date_ = "<div style='font-size:14px;'>$date_</div>";


                            $amount = $pending_service["pay_mode"] == 'claim' ? $pending_service["claim_amt"] : $pending_service["pay"];
                            $total_srvices += $amount;
                    ?>
                            <tr>
                                <td><?= $sn++; ?></td>
                                <td><?php
                                    echo '<strong>' . $pending_service["item_services"] . '</strong><br>';
                                    echo $rwxx["notes"]; ?>
                                    <br>
                                    <?php echo '<b>[ Amount:' . number_format($amount) . ' /' . ($pending_service["paystatus"] == '1' ? "Paid" : "Not Paid") . ']</b>';    ?><br>
                                    <small><b><i>Entered by: <?php echo $rwxx['prepared_by']; ?>
                                                <?php echo $date_;  //formatDateTime_($rwxx['date_entry']); 
                                                ?></i></b></small>
                                    <br>

                                    <?php if (date_diff_day($date_entry) < 1 && $_SESSION['fullname'] == $prepared_by) { ?>
                                        <button type="button" class="btn btn-xs btn-warning" id="edit" onclick="edit_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-edit"></i>&nbsp;Edit</button>
                                    <?php  }    ?>

                                    <?php if (date_diff_day($date_entry) < 1) { ?>
                                        <button type="button" class="btn btn-xs btn-success" id="add" onclick="add_notes('<?= $rwxx["sn"] ?>')"><i class="fa fa-plus"></i>&nbsp;Add More Notes</button>
                                    <?php  }    ?>





                                </td>
                            </tr>
                    <?php    }
                    } ?>
                </tbody>
            </table>
        </div>
        <h3>Total Amount: =N= <?= number_format($total_srvices) ?></h3>
<?php } else {
        // echo 'No services ';
    }

    exit;
}




if (isset($_POST['edit_notes_update'])) {
    ///review_note:review_note,edit_notes_update:notes_sn
    $response = array(
        'status' => 0,
        'message' => ''
    );

    $sn = cleanInput($_POST['edit_notes_update']);
    $review_note = ($_POST['review_note']);

    $save_note = editNotes($db, $sn, $review_note);
    if ($save_note == true) {
        $response['message'] = 'Successful!';
        $response['status'] = '0';
        echo  json_encode($response);
    } else {
        $response['message'] = 'Not Successful!';
        $response['status'] = '1';
        echo  json_encode($response);
    }
    exit;
}









?>