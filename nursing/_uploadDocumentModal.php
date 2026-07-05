<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);



if (isset($_POST['uploadDocumentBtn'])) {

    $title = trim($_POST['title']);
    $related_table_id = $_POST['related_table_id'];
    $related_table = $_POST['related_table'];
    $hospital_no = $_POST['hospital_no'];
    $module = $_POST['module'];
    $target_dir = rtrim($_POST['target_dir'], '/') . '/'; // ensure trailing slash

    try {
        if (empty($hospital_no)) {
            throw new Exception("Oops! Patient ID not recognized...");
        }

        if (!isset($_FILES["document_file"]["tmp_name"]) || empty($_FILES["document_file"]["tmp_name"])) {
            throw new Exception("No file selected or upload error code: " . $_FILES["document_file"]["error"]);
        }

        // Prepare safe file name
        $file_name = preg_replace('/\s+/', '_', basename($_FILES["document_file"]["name"]));
        $unique_name = uniqid() . getToken(10) . '_' . $file_name;
        $target_file = $target_dir . $unique_name;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed types
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $allowed_types)) {
            throw new Exception("Bad Request! Invalid file type (“$imageFileType”). Only PDF, JPG, JPEG, PNG allowed.");
        }

        $tmp_name = $_FILES['document_file']['tmp_name'];
        if (!move_uploaded_file($tmp_name, $target_file)) {
            throw new Exception("Sorry, there was an error moving your file. Check folder permissions for: $target_dir");
        }

        // Insert into DB
        $stmt = $db->prepare("
            INSERT INTO patients_documents 
            (hospital_no, title, link, file_type, module, related_table, related_table_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt->execute(array(
            $hospital_no,
            $title,
            $target_file,
            $imageFileType,
            $module,
            $related_table,
            $related_table_id,
            $_SESSION["id"]
        ))) {
            $db_error = implode(' | ', $stmt->errorInfo());
            throw new Exception("Database Error: $db_error");
        }

        // ✅ SUCCESS MESSAGE
        echo "<div class='alert alert-success'>Document uploaded successfully.</div>";
    } catch (Exception $e) {
        // ❌ ERROR MESSAGE
        echo "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    }
}



if (isset($related_table_id)) {
    $related_table_id = $related_table_id;
} else {
    $related_table_id = null;
}

if (isset($related_table)) {
    $related_table = $related_table;
} else {
    $related_table = null;
}

if (isset($hospital_no)) {
    $hospital_no = $hospital_no;
} else {
    $hospital_no = null;
}

if (isset($module)) {
    $module = $module;
} else {
    $module = null;
}

if (isset($target_dir)) {
    $target_dir = $target_dir;
} else {
    $target_dir = "../documents/patients/";
}





?>
<div class="modal inmodal fade" id="uploadDocumentModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Upload Document </h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
                    <div>
                        <label for="reg_input_no" class="req">Title: </label>
                        <input type="text" maxlength="100" name="title" id="title" class="form-control" placeholder="Document Title" required>
                    </div>
                    <br>
                    <div>
                        <label for="reg_input_no" class="req">Document [jpg, png, pdf] </label>
                        <input type="file" name="document_file" id="document_file" class="form-control" required>
                    </div>
                    <br>

                    <div>
                        <input type="hidden" name="related_table_id" value="<?= $related_table_id; ?>">
                        <input type="hidden" name="target_dir" value="<?= $target_dir; ?>">
                        <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                        <input type="hidden" name="related_table" value="<?= $related_table; ?>">
                        <input type="hidden" name="module" value="<?= $module; ?>">
                        <button type="submit" class="btn btn-primary" name="uploadDocumentBtn" style="display: block; width:100%;">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>