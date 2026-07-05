<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);



if (isset($_POST['uploadDocumentBtn'])) {
    $title = $_POST['title'];
    $related_table_id = $_POST['related_table_id'];
    $related_table = $_POST['related_table'];
    $hospital_no = $_POST['hospital_no'];
    $module = $_POST['module'];
    $target_dir = $_POST['target_dir'];

    $error_status = 1;
    $error_msg = "Oops! Something went wrong";


    if (!empty($hospital_no)) {
        // $target_dir = dirname(__FILE__) . "/documents/patients/";
        // $target_dir = "../documents/transplant/";
        $target_file = $target_dir . uniqid() . "_" . $hospital_no . '_'  . preg_replace('/ /i', '', $title . basename($_FILES["document_file"]["name"]));
        $uploadOk = 1;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($imageFileType == 'pdf' || $imageFileType == 'jpg' || $imageFileType == 'png' || $imageFileType == 'jpeg') {
            $tmp_name = $_FILES['document_file']['tmp_name'];

            if (move_uploaded_file($tmp_name, $target_file)) {


                ///$hospital_no, $title, $link, $file_type, $module, $related_table, $related_table_id, $created_by))) {               
                $save = $Document->save($hospital_no, $target_file, $_SESSION["id"], $title, $imageFileType, $module, $related_table, $related_table_id);

                if ($save) {
                    $error_status = 2;
                    $error_msg = 'Document is uploaded successfully...';
                } else {
                    $error_msg = 'Operation Failed / Already uploaded...';
                }
            } else {
                $error_msg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error_msg = "Bad Request! Invalid file... ";
        }
    } else {
        $error_msg = "Oops! Patient ID not recognized...";
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