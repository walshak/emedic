<?php
session_start();
include("../Connections/Conn.php");

if (isset($_POST['add_documentation'])) {
    echo $add_documentation = $_POST['add_documentation'];
?>
    <div id="loader" class="loader"></div>
    <form action="" id="remita_form2" name="remita_form2" method="POST">


        <div class="form-group">
            <h4>Enter Notes/Document below:</h4>
            <div id="edit__mode" style="color: red;"></div>
            <textarea name="selected_review_note" class="form-control" cols="45" rows="5" placeholder="" style="font-size:18px" id="selected_review_note" required></textarea>
        </div>

        <input type="hidden" name="hospital_no" id="hospital_no" value="<?= $add_documentation; ?>">
        <input type="hidden" name="appointment_number" id="appointment_number" value="<?= $add_documentation; ?>">

        <input type="hidden" name="mode" id="mode" value="new">
        <input type="hidden" name="notes_sn" id="notes_sn" value="">
        <input type="hidden" name="dept_id" id="dept_id" value="<?= $_SESSION['dept_id']; ?>">

        <button type="button" class="btn btn-sm btn-success" id="pay_now" name="pay_now" onclick="service_bill()">Add Notes</button>
        <hr>


        <div id="data_displayed"><strong style="color: red;">Loading ... Please Wait!</strong></div>

        <div class="modal-footer">

            <button type="button" class="btn btn-sm btn-default " data-dismiss="modal">Close</button>
        </div>
    </form>
<?php
    exit;
}


if (isset($_POST['review_note'])) {

    $response = array(
        'status' => 0,
        'message' => ''
    );

    // Clean the input data
    function cleanInput($data)
    {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    $review_note = cleanInput($_POST['review_note']);
    $hospital_no = cleanInput($_POST['hospital_no']);
    $dept_id = cleanInput($_POST['dept_id']);
    $tag = 'PH';
    $notes_type = 'Pharm';
    $setdate = date('Y-m-d H:i:s');

    try {
        // Prepare the SQL statement with placeholders
        $insertSQL = "INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, created_by, date_entry) 
                          VALUES (:app_no, :hospital_no, :notes, :tag, :notes_type,:created_by, :prepared_by,  :date_entry)";
        $stmt = $db->prepare($insertSQL);

        // Bind the parameters
        $stmt->bindParam(':app_no', $hospital_no, PDO::PARAM_STR); // Assuming app_no is the same as hospital_no
        $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $review_note, PDO::PARAM_STR);
        $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
        $stmt->bindParam(':notes_type', $notes_type, PDO::PARAM_STR);
        $stmt->bindParam(':created_by', $_SESSION['fullname'], PDO::PARAM_STR);
        $stmt->bindParam(':prepared_by', $_SESSION['EmployeeCode'], PDO::PARAM_STR);
        $stmt->bindParam(':date_entry', $setdate, PDO::PARAM_STR);

        // Execute the statement
        $stmt->execute();

        // If execution is successful
        $response['message'] = 'Successful!';
        $response['status'] = 1; // Usually, 1 indicates success
    } catch (PDOException $e) {
        // If an error occurs
        $response['message'] = 'Error: ' . $e->getMessage();
        $response['status'] = 0;
    }

    // Return the response as JSON
    echo json_encode($response);
    exit;
}


if (isset($_POST['review_note_edit'])) {
    $response = array(
        'status' => 0,
        'message' => ''
    );

    function cleanInput($data)
    {
        // Use htmlspecialchars and trim in a more secure manner
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Assuming $db is your database connection object

    // Clean and assign variables
    $sn = cleanInput($_POST['edit_notes_update']);
    $review_note = cleanInput($_POST['review_note_edit']); // Assuming this is the correct field name

    // Prepare and execute the SQL statement
    $stmt = $db->prepare("UPDATE notes SET notes = ? WHERE sn = ?");
    $save = $stmt->execute(array($review_note, $sn));

    if ($save) {
        $response['message'] = 'Successful!';
        $response['status'] = 0;
        echo json_encode($response);
    } else {
        $response['message'] = 'Not Successful!';
        $response['status'] = 1;
        echo json_encode($response);
    }
    exit; // Ensure no further output interferes with JSON response

}




if (isset($_POST['delete_notes'])) {

    $response = array(
        'status' => 0,
        'message' => ''
    );

    $id = $_POST['delete_notes'];


    $delete = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
    $deleted = $delete->execute(array($id));

    if ($deleted) {

        $response['message'] = 'Successful!';
        $response['status'] = '0';
        echo  json_encode($response);
        exit;
    } else {
        $response['message'] = 'Not Successful!';
        $response['status'] = '1';
        echo  json_encode($response);
    }
    exit;
}

if (isset($_POST['delete_notes_chat'])) {

    $response = array(
        'status' => 0,
        'message' => ''
    );

    $id = $_POST['delete_notes_chat'];

    try {
        $delete = $db->prepare("DELETE FROM pharm_doctor_notes WHERE sn = ? and is_delete =0");
        $deleted = $delete->execute(array($id));

        if ($deleted) {
            $response['message'] = 'Successful!';
            $response['status'] = 0;
        } else {
            $response['message'] = 'Not Successful!';
            $response['status'] = 1;
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        $response['status'] = 1;
    }

    echo json_encode($response);
    exit;
}
?>




?>