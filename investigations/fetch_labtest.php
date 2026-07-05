<?php include("../Connections/Conn.php"); ?>

<?php

if (isset($_POST["set_volume_sheet_id"])) {
    $stock_id = $_POST["set_volume_sheet_id"];
?>

    <form method="POST" action="cnsumbl.php">
        <h3>Set Total Volume of Re-agent / No of Sheet/Film </h3>
        <strong>Example: 100ml / 1 sheet </strong>
        <hr>

        <div class="form_sep">
            <label for="reg_input_no" class="req"> Total ml/ Sheet/Film Per Test) </label>
            <input type="number" id="ml_sheet" name="ml_sheet" class="form-control" required>
        </div>

        <div class="form_sep">
            <div class="pull-left">
                <button class="btn btn-success" type="submit" name="save_ml" id="save_ml">Save</button>
            </div>

            <div class="pull-right">
                <a href="cnsumbl.php" class="btn btn-warning">Cancel</a>
            </div>
        </div>
        <input type="hidden" value="<?php echo $stock_id; ?>" name="stock_id">
        <input type="hidden" value="SNI" name="consumble">

    </form>

<?php
}

if (isset($_POST["manage_price_id"])) {
    $stmt = $db->prepare("SELECT * FROM lab_scan WHERE sn = :sn");
    $stmt->bindParam(':sn', $_POST["manage_price_id"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["edit_id"])) {
    $stmt = $db->prepare("SELECT * FROM patient_discount WHERE sn = :sn");
    $stmt->bindParam(':sn', $_POST["edit_id"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["mgt_stock_id"])) {
    $stmt = $db->prepare("SELECT * FROM lab_stocks WHERE stock_sn = :stock_sn ORDER BY stock_sn");
    $stmt->bindParam(':stock_sn', $_POST["mgt_stock_id"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["stock_id"])) {
    $stmt = $db->prepare("SELECT * FROM lab_stocks WHERE stock_sn = :stock_sn ORDER BY stock_sn");
    $stmt->bindParam(':stock_sn', $_POST["stock_id"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["lab_test_no_"])) {
    $stmt = $db->prepare("SELECT * FROM lab_scan WHERE sn = :sn ORDER BY sn");
    $stmt->bindParam(':sn', $_POST["lab_test_no_"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["edit_lab_id"])) {
    $stmt = $db->prepare("SELECT * FROM lab_scan WHERE sn = :sn");
    $stmt->bindParam(':sn', $_POST["edit_lab_id"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["edit_ext_patient"])) {
    $stmt = $db->prepare("SELECT * FROM pharm_ext WHERE transc_code = :transc_code");
    $stmt->bindParam(':transc_code', $_POST["edit_ext_patient"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["consumable_id"])) {
    $stmt = $db->prepare("SELECT * FROM lab_scan WHERE sn = :sn");
    $stmt->bindParam(':sn', $_POST["consumable_id"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}



if (isset($_POST["lab_request_reorder"])) {

    $lab_request_reorder = $_POST["lab_request_reorder"];
    $part = explode("__", $lab_request_reorder);

?>

    <form method="POST" id="reject_result_form">
        <strong>Are you sure want to reorder this investigation?</strong>
        <hr>
        <strong>Investigation Request #: &nbsp; <?php echo $part[0]; ?></strong><br>
        <strong>Investigation Type: &nbsp;<?php echo $part[1]; ?></strong><br><br />

        <input type="button" name="reject" value="Re-order Request" data-target=".slacker-modal" id="<?php echo $part[0]; ?>" class="btn btn-danger btn-xs reorder_request_post" />

        &nbsp;
        <input type="button" name="cancel" value="Cancel" data-target=".slacker-modal" id="" class="btn btn-default btn-xs cancel_reorder" />
    </form>


<?php }


if (!empty($_GET['q'])) {
    $query = "SELECT c.patient, c.patient_name AS cname FROM lab_manage c WHERE c.patient_name LIKE :search OR c.patient LIKE :search";
    $stmt = $conn->prepare($query);
    $search = '%' . $_GET['q'] . '%';
    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    $stmt->execute();

    $numCustomer = $stmt->rowCount();

    if ($numCustomer != 0) {
        $answer = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $answer[] = array("id" => $row['patient'], "text" => $row['cname']);
        }
    } else {
        $answer[] = array("id" => "0", "text" => "No Results Found...");
    }
    echo json_encode($answer);
}


if (isset($_POST['add_more_commment'])) {
    $lab_no = $_POST['add_more_commment'];
    $fullname = $_POST['who_is_add'];
    $more_comment = $_POST['more_comment'];
    $setdate = date('d-m-Y H:i:s');

    // Prepare the formatted comment
    $formatted_comment = $more_comment . '<br><b>Name:</b> ' . $fullname . ' / <b>Date: </b>' . $setdate;

    // Step 1: Get existing comment
    $stmt = $db->prepare("SELECT result_comment FROM lab_manage WHERE labrequest_no = :lab_no");
    $stmt->bindParam(':lab_no', $lab_no, PDO::PARAM_STR);
    $stmt->execute();
    $existing = $stmt->fetchColumn();

    // Step 2: Merge comments
    if (!empty($existing)) {
        $formatted_comment = $existing . '<br><br>' . $formatted_comment;
    }

    // Step 3: Update with new merged comment
    $stmt = $db->prepare("UPDATE lab_manage SET result_comment = :comment WHERE labrequest_no = :lab_no");
    $stmt->bindParam(':comment', $formatted_comment, PDO::PARAM_STR);
    $stmt->bindParam(':lab_no', $lab_no, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Comment added successfully. Refresh to see changes.";
    } else {
        echo "Failed to add comment.";
    }
}


if (isset($_POST['lab_req_number'])) {
    $Request_ID = $_POST['lab_req_number'];
    $fullname = $_POST['fullname'];
    $speciment_taken = $_POST['speciment_taken'];
    $setdate = date('Y-m-d H:i:s');

    $stmt = $db->prepare("UPDATE lab_manage SET collected_by = :fullname, collected_date = :setdate, data_capture_status = 'specimen', collected_specimen = :speciment_taken WHERE labrequest_no = :Request_ID");

    $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
    $stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':speciment_taken', $speciment_taken, PDO::PARAM_STR);
    $stmt->bindParam(':Request_ID', $Request_ID, PDO::PARAM_STR);

    $stmt->execute();
}
