<?php

if (isset($_POST["updateTransplantBtn"])) {
    $error_status = 1;
    $hospital_no = $_POST['hospital_no'];
    $transplant_id = $_POST['transplant_id'];
    $done_transplant_before = $_POST['done_transplant_before'];
    $number_of_transplant = $_POST['number_of_transplant'];
    // $target_dir = dirname(__FILE__) . "/documents/transplant/";
    $target_dir = "../documents/transplant/";

    $isHlaResultUploaded = false;
    $isDsaResultUploaded = false;

    $hla_target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', $title . basename($_FILES["hla_result_file"]["name"]));
    $dsa_target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', $title . basename($_FILES["dsa_result_file"]["name"]));

    $hla_imageFileType = strtolower(pathinfo($hla_target_file, PATHINFO_EXTENSION));
    $dsa_imageFileType = strtolower(pathinfo($dsa_target_file, PATHINFO_EXTENSION));

    if ($hla_imageFileType == 'pdf' || $hla_imageFileType == 'jpg' || $hla_imageFileType == 'png' || $hla_imageFileType == 'jpeg') {
        $hla_tmp_name = $_FILES['hla_result_file']['tmp_name'];

        if (move_uploaded_file($hla_tmp_name, $hla_target_file)) {
            $save_hla_result = $Document->save($hospital_no, 'HLA Result', $hla_target_file, $hla_imageFileType, 'Transplant',  'transplants', $transplant_id, $_SESSION["id"]);
            $isHlaResultUploaded = true;
        }
    }

    if ($dsa_imageFileType == 'pdf' || $dsa_imageFileType == 'jpg' || $dsa_imageFileType == 'png' || $dsa_imageFileType == 'jpeg') {
        $dsa_tmp_name = $_FILES['dsa_result_file']['tmp_name'];

        if (move_uploaded_file($dsa_tmp_name, $dsa_target_file)) {
            $save_dsa_result = $Document->save($hospital_no, 'DSA Result', $dsa_target_file, $dsa_imageFileType, 'Transplant',  'transplants', $transplant_id, $_SESSION["id"]);
            $isDsaResultUploaded = true;
        }
    }

    if ($isDsaResultUploaded == true && $isHlaResultUploaded == true) {
        $stmt = $db->prepare("UPDATE transplants SET hla_result_link = ?, dsa_result_link = ?, number_of_transplant = ?, done_transplant_before = ?  WHERE id = ? ");
        $update = $stmt->execute(array(
            $hla_target_file,
            $dsa_target_file,
            $number_of_transplant,
            $done_transplant_before,
            $transplant_id
        ));

        if ($update) {
            $error_status = 2;
            $error_msg = "  Updated successfully...";
        } else {
            $error_msg = "  Update failed...";
        }
    } else {
        if ($isHlaResultUploaded == false) {
            $error_msg = "  HLA Result not uploaded ...";
        }

        if ($isDsaResultUploaded == false) {
            $error_msg .= "  DSA Result not uploaded...";
        }
    }
}



if (isset($_POST['transplant_patient_update_profile_btn'])) {
    $hospital_no = $_POST['patient_for_transplant'];
    $blood_group = $_POST['blood_group'];
    $genotype = $_POST['genotype'];

    $error_msg = "Oops! Couldn't update";
    $error_status = 1;

    $update = $db->prepare("UPDATE enrollee SET blood_g = ?, geno_type = ? WHERE hospital_no = ? ");
    $update = $update->execute(array(
        $blood_group,
        $genotype,
        $hospital_no
    ));

    if ($update) {
        $error_msg = "Wow! Record updated...";
        $error_status = 2;
        $patient_info->blood_g = $blood_group;
        $patient_info->geno_type = $genotype;
    }
}

if (isset($_POST['addHLAResultBtn'])) {
    $donor_id = $_POST['donor_id'];
    $transplant_id = $_POST['transplant_id'];
    $target_dir = "../documents/transplant/";
    echo   $target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', basename($_FILES["hla_result_file"]["name"]));
    $uploadOk = 1;

    $error_status = 1;
    $error_msg = "Oops! Something went wrong";

    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if ($imageFileType == 'pdf') {
        $tmp_name = $_FILES['hla_result_file']['tmp_name'];

        if (move_uploaded_file($tmp_name, $target_file)) {
            $stmt = $db->prepare("UPDATE transplants_donors SET hla_result_link = ? WHERE id = ? ");
            $update = $stmt->execute(array(
                $target_file,
                $donor_id
            ));
            $error_status = 2;
            $error_msg = 'Result is uploaded successfully...';
        } else {
            $error_msg = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error_msg = "File type not allow... ";
    }
}




if (isset($_POST["addCrossMatchResultBtn"])) {
    $donor_id = $_POST['donor_id'];
    $transplant_id = $_POST['transplant_id'];
    $percentage = $_POST['percentage'];
    $comment = $_POST['comment'];

    $error_status = 1;
    $error_msg = 'Oops! Something went wrong..';

    $stmt = $db->prepare("UPDATE transplants_donors SET percentage = ?, comment = ?  WHERE id = ? ");
    $update = $stmt->execute(array(
        $percentage,
        $comment,
        $donor_id
    ));

    if ($update) {
        $error_status = 2;
        $error_msg = 'Result is uploaded successfully...';
    }
}

/////////////// Remove Donor
if (isset($_POST['removeDonorBtn'])) {
    $donor_id = $_POST['donor_id'];
    $error_status = 1;
    $error_msg = "Oops! Something went wrong";
    $stmt = $db->prepare("DELETE FROM transplants_donors WHERE  id = ? ");
    $delete = $stmt->execute(array($donor_id));
    if ($delete) {
        $error_msg = "Donor has been removed";
        $error_status = 2;
    }
}


/////////////Remove Investigation

if (isset($_POST['removeInvestigationBtn'])) {
    $id = $_POST['id'];
    $error_status = 1;
    $error_msg = "Oops! Something went wrong";
    $stmt = $db->prepare("DELETE FROM transplants_investigations WHERE  id = ? ");
    $delete = $stmt->execute(array($id));
    if ($delete) {
        $error_msg = "Investigation has been removed";
        $error_status = 2;
    }
}


///////////// add Lab Investigation to Trasnplant ///////////////////////////////////
if (isset($_REQUEST['add_lab_investigations_btn'])) {
    $hospital_no = $hospital_no;
    $transplant_id = $patient_transplant_info->id;
    $lab_investigations = $_POST['lab_investigations'];
    $error_status = 1;
    $error_msg = "Oops! Something went wrong";

    foreach ($lab_investigations as $key => $lab_investigation) {
        $lab_manage_id = $lab_investigation;
        $stmt = $db->prepare("SELECT *  FROM lab_manage WHERE  sn = ? ");
        $stmt->execute(array($lab_manage_id));

        $investigation_info = json_decode(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));

        if (!empty($investigation_info)) {
            $test_name = $investigation_info->test_name;
            $request_date = $investigation_info->request_date;
            $result_note = $investigation_info->result_note;
            $test_id = $investigation_info->test_id;

            $stmt = $db->prepare("SELECT *  FROM transplants_investigations WHERE  transplant_id = ? AND test_id = ?");
            $stmt->execute(array($transplant_id, $test_id));
            if ($stmt->rowCount() == 0) {
                $stmt = $db->prepare("INSERT INTO  transplants_investigations (transplant_id, test_id, test_name, result_note, date_conducted, lab_manage_id, created_by, category) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?) ");
                $save =  $stmt->execute(array($transplant_id, $test_id, $test_name, $result_note, $request_date, $lab_manage_id, $_SESSION["id"], 'Lab'));
                if ($save) {
                    $error_msg = "Good! Investigation is linked...";
                    $error_status = 2;
                }
            } else {
                $error_msg = "Oops! Investigation Linked before";
            }
        }
    }
}


///////////// add Rad Investigation to Trasnplant ///////////////////////////////////
if (isset($_REQUEST['add_rad_investigations_btn'])) {
    $hospital_no = $hospital_no;
    $transplant_id = $patient_transplant_info->id;
    $rad_investigations = $_POST['rad_investigations'];
    $error_status = 1;
    $error_msg = "Oops! Something went wrong";

    foreach ($rad_investigations as $key => $rad_investigation) {
        $lab_manage_id = $rad_investigation;
        $stmt = $db->prepare("SELECT *  FROM lab_manage WHERE  sn = ? ");
        $stmt->execute(array($lab_manage_id));

        $investigation_info = json_decode(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));

        if (!empty($investigation_info)) {
            $test_name = $investigation_info->test_name;
            $request_date = $investigation_info->request_date;
            $result_note = $investigation_info->result_note;
            $test_id = $investigation_info->test_id;

            $stmt = $db->prepare("SELECT *  FROM transplants_investigations WHERE  transplant_id = ? AND test_id = ?");
            $stmt->execute(array($transplant_id, $test_id));
            if ($stmt->rowCount() == 0) {
                $stmt = $db->prepare("INSERT INTO  transplants_investigations (transplant_id, test_id, test_name, result_note, date_conducted, lab_manage_id, created_by, category) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?) ");
                $save =  $stmt->execute(array($transplant_id, $test_id, $test_name, $result_note, $request_date, $lab_manage_id, $_SESSION["id"], 'Rad'));
                if ($save) {
                    $error_msg = "Good! Investigation is linked...";
                    $error_status = 2;
                }
            } else {
                $error_msg = "Oops! Investigation Linked before";
            }
        }
    }
}


$age = 0;
$age_full = null;
if (!empty($patient_info->dob)) {
    $components = preg_split("/-/", $patient_info->dob);
    $year = $components[0];
    $age = date('Y') - $year;
    $age_full = $age . ' yrs';

    if ($age == 0) {
        $month = abs(date('m') - $components[1]);
        $age_full = $month . ' Months';
    }
}
?>
<h4> <a href="patient.php?hosp_no=<?= $hospital_no; ?>"> Home </a> | <u>Patient Information </u></h4>
<table>
    <tr>
        <td style=" padding-right:10px;">
            <img src="<?php if (file_exists(enrollee_p . $hospital_no . '.' . 'jpg')) {
                            echo enrollee_p . $hospital_no . '.' . 'jpg';
                        } else {
                            echo '../img/no_photo.jpg';
                        } ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar">
        </td>
        <td>
            <span style="font-size:18px"><?php echo $hospital_no; ?></span>
            <span style="font-size:25px"><?php echo ' / ' . $patient_name; ?>
            </span>

            <br>

            <table class="table border">
                <tr>
                    <td><strong>Gender:</strong>&nbsp; <?= $patient_info->gender; ?></td>
                    <td><strong>Age:&nbsp; <?= $age_full; ?></strong></td>
                    <td><strong>Blood/Group:&nbsp; <?= $patient_info->blood_g; ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Genotype:</strong> <?= $patient_info->geno_type; ?><br>
                    </td>

                    <td><strong>Marital Status:</strong><br>
                    </td>

                    <td><strong>Insurance Type:</strong> <?= $patient_info->insurance; ?><br>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<?php
if (empty($patient_info->blood_g)) {
?>
    <!---############################################### UPDATE RECORD MODAL -----###############################-->
    <div class="modal inmodal fade" id="updatePatientRecordForTransplant" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Update Patient Record</h4>
                </div>
                <div class="modal-body" style="min-height: 300px;">
                    <form action="<?= $editFormAction; ?>" method="post">
                        <div class="form_sep">
                            <h3><?= $hospital_no . '/' . $patient_name; ?></h3>
                            <p><input type="hidden" name="patient_for_transplant" id="patient_for_transplant" value="<?= $hospital_no; ?>" required></p>
                        </div>
                        <div class="form_sep">
                            <label for="reg_input_no" class="req">Blood Group </label>
                            <select class="form-control" name="blood_group" required>
                                <option value=""> </option>
                                <option value="A+"> A+ </option>
                                <option value="O+"> O+</option>
                                <option value="B+"> B+</option>
                                <option value="AB+"> AB+</option>
                                <option value="A-"> A-</option>
                                <option value="O-"> O-</option>
                                <option value="B-"> B-</option>
                                <option value="AB-"> AB-</option>
                            </select>
                        </div>
                        <br>
                        <div class="form_sep">
                            <label for="reg_input_no" class="req">Genotype </label>
                            <select class="form-control" name="genotype" required>
                                <option value=""> </option>
                                <option value="AA"> AA </option>
                                <option value="AS"> AS </option>
                                <option value="SS"> SS </option>
                                <option value="AC"> AC </option>
                            </select>
                        </div>
                        <br>
                        <br>
                        <div class="form_sep">
                            <div class="pull-left">
                                <button type="submit" class="btn btn-success btn btn-sm" name="transplant_patient_update_profile_btn" id="transplant_request_btn">Update</button>
                            </div>
                            <div class="pull-right">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel / Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!---############################################### END UPDATE RECORD MODAL -----###############################-->


    <script>
        $(document).ready(function() {
            $("#updatePatientRecordForTransplant").modal('show');
        });
    </script>

<?php
}
?>


<h4> <u>Transplant Information &nbsp;&nbsp;
        <a href="#" data-toggle="modal" data-target="#updateTransplantModal" class="btn btn-sm btn-xs btn-success"><i class="fa fa-edit"></i> Edit</a></u></h4>


<div class="modal inmodal fade" id="updateTransplantModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Transplant Request</h4>
            </div>

            <div class="modal-body" style="min-height: 300px;">

                <form action="<?= $editFormAction; ?>" method="post" id="transplant_request_form" enctype="multipart/form-data">

                    <br>
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Have you done transplant before ? </label>
                        <select data-placeholder="Choose Procedures" class="input-sm form-control chosen-select" name="done_transplant_before" id="done_transplant_before" style="width:350px;" tabindex="4" required>
                            <option value="">Select</option>
                            <option value="Yes" <?= $patient_transplant_info->done_transplant_before == "Yes" ? "selected" : ""; ?>>Yes</option>
                            <option value="No" <?= $patient_transplant_info->done_transplant_before == "No" ? "selected" : ""; ?>>No</option>
                        </select>
                    </div>
                    <br>
                    <div class="form_sep" id="number_of_transplant_wrap" style="display:<?= $patient_transplant_info->done_transplant_before == "No" ? "block" : "block"; ?>">
                        <label for="reg_input_no" class="req">Number of Transplant: </label>
                        <input type="number" name="number_of_transplant" id="number_of_transplant" class="form-control" value="<?= $patient_transplant_info->number_of_transplant; ?>">
                    </div>
                    <br>
                    <div>
                        <label for="reg_input_no" class="req">HLA Result File [jpg, png, pdf] </label>
                        <input type="file" name="hla_result_file" id="hla_result_file" class="form-control">
                    </div>
                    <br>
                    <div>
                        <label for="reg_input_no" class="req">DSA Result File [jpg, png, pdf] </label>
                        <input type="file" name="dsa_result_file" id="dsa_result_file" class="form-control">
                    </div>
                    <br>


                    <br>
                    <br>
                    <div class="form_sep">

                        <div class="pull-left">
                            <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                            <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
                            <button type="submit" class="btn btn-success btn btn-sm" name="updateTransplantBtn" id="updateTransplantBtn">Update </button>
                        </div>

                        <div class="pull-right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel / Close</button>

                        </div>
                    </div>

                </form>
            </div>


        </div>
    </div>
</div>



<table class="table" border="2">
    <tr>
        <td><b>Transplant Type:</b></td>
        <td><?= $patient_transplant_info->transplant_type; ?></td>
        <td> <b>Request Date:</b></td>
        <td><?= dateFormat_($patient_transplant_info->created_at); ?></td>

    </tr>
    <tr>
        <td><b>Transplant Amount:</b></td>
        <td><b>&#8358;<?= number_format($patient_transplant_info->amount, 2); ?></b></td>

        <td> <b>Have done transplant ? :</b></td>
        <td><?= $patient_transplant_info->done_transplant_before; ?></td>
        <td> <b> Number of tranplant :</b></td>
        <td><?= $patient_transplant_info->number_of_transplant; ?></td>

    </tr>
    <tr>

        <td> <b>HLA Result:</b></td>
        <td colspan="4"><?php
                        if (!empty($patient_transplant_info->hla_result_link)) {
                        ?>
                <a href="<?= $patient_transplant_info->hla_result_link; ?>" target="_BLANK">View Result </a>
            <?php
                        } ?>
        </td>
    </tr>
    <tr>

        <td> <b>DSA Result:</b></td>
        <td colspan="4"><?php
                        if (!empty($patient_transplant_info->dsa_result_link)) {
                        ?>
                <a href="<?= $patient_transplant_info->dsa_result_link; ?>" target="_BLANK">View Result </a>
            <?php
                        } ?>
        </td>
    </tr>

</table>
<a href="#" class="btn btn-info btn-xs " data-toggle="modal" data-target="#addLabInvestigationsModal"> Add Lab Investigations</a> |
<a href="#" class="btn btn-success btn-xs " data-toggle="modal" data-target="#addRadInvestigationsModal"> Add Radiology Investigations</a> |
<a href="#" class="btn btn-success btn-xs " data-toggle="modal" data-target="#investigationsModal"> View Investigations</a> |
<a href="#" class="btn btn-primary btn-xs " data-toggle="modal" data-target="#uploadDocumentModal"> Upload Documents </a> |
<a href="#" class="btn btn-success btn-xs " data-toggle="modal" data-target="#viewTransplantDocumentsModal"> View Documents </a> |
<a href="#" class="btn btn-info btn-xs " data-toggle="modal" data-target="#viewSpecialistNotesModal"> Specialist Notes </a> |
<a href="#" class="btn btn-info btn-xs " data-toggle="modal" data-target="#addPostOperationalNotesModal"> Post Operation Notes </a>


<!---############################################### LAB INVESTIGATION MODAL -----###############################-->
<div class="modal inmodal fade" id="addLabInvestigationsModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Link Lab Investigation to Transplant</h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <form action="<?= $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                    <div class="alert alert-info"> Link <?= $patient_name . "'s Lab Investigation to Transplant"; ?> </div>
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Select Lab Investigations </label>
                        <select name="lab_investigations[]" multiple class="input-sm chosen-select" required>
                            <option value="">Select Lab Investigations</option>
                            <?php
                            $select_lab_investigations = $Investigation->getPatientLabs($hospital_no, true, 5);

                            foreach ($select_lab_investigations as $key => $select_lab_investigation) {
                            ?>
                                <option value="<?= $select_lab_investigation->sn; ?>"> <?= $select_lab_investigation->test_name  . ' - On the ' . date("d/m/Y", strtotime($select_lab_investigation->request_date)); ?> </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <br>
                    <br>
                    <div class="form_sep">

                        <div class="pull-left">
                            <button type="submit" class="btn btn-primary btn btn-sm" name="add_lab_investigations_btn" id="add_lab_investigations_btn">Link Selected Investigation</button>
                        </div>

                        <div class="pull-right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!---############################################### END LAB INVESTIGATION MODAL -----###############################-->


<!---############################################### RAD INVESTIGATION MODAL -----###############################-->
<div class="modal inmodal fade" id="addRadInvestigationsModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Llink Radiology to Transplant</h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
                    <div class="alert alert-info"> Link <?= $patient_name . "'s Lab Investigation to Transplant"; ?> </div>
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Select Radiology Investigations </label>
                        <select name="rad_investigations[]" multiple class="input-sm chosen-select" required>
                            <option value="">Select Radiology Investigations</option>
                            <?php
                            $select_rad_investigations = $Investigation->getPatientRads($hospital_no, true, 5);

                            foreach ($select_rad_investigations as $key => $select_rad_investigation) {
                            ?>
                                <option value="<?= $select_rad_investigation->sn; ?>"> <?= $select_rad_investigation->test_name  . ' - On the ' . date("d/m/Y", strtotime($select_rad_investigation->request_date)); ?> </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <br>
                    <br>
                    <div class="form_sep">

                        <div class="pull-left">
                            <button type="submit" class="btn btn-primary btn btn-sm" name="add_rad_investigations_btn" id="add_rad_investigations_btn">Link Selected Investigation</button>
                        </div>

                        <div class="pull-right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!---############################################### END RAD INVESTIGATION MODAL -----###############################-->


<div class="modal inmodal fade" id="investigationsModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Investigations </h4>
            </div>
            <div class="modal-body">

                <?php
                $stmt = $db->prepare("SELECT *  FROM transplants_investigations WHERE  transplant_id = ?");
                $stmt->execute(array($transplant_id));
                if ($stmt->rowCount() > 0) {  ?>
                    <hr>
                    <table class="table" border="2">
                        <thead>
                            <tr>
                                <td>SN</td>
                                <td>INVESTIGATION</td>
                                <td>TYPE</td>
                                <td>DATE</td>
                                <td>RESULT</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $sn = 1;
                            $transplant_investigations = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
                            foreach ($transplant_investigations as $key => $transplant_investigation) {
                                $result_note = null;

                                $stmt = $db->prepare("SELECT *  FROM lab_manage WHERE  sn = ? ");
                                $stmt->execute(array($transplant_investigation->lab_manage_id));
                                $investigation_info = json_decode(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));

                                if (!empty($investigation_info)) {
                                    if (!empty($investigation_info->result_note)) {
                                        $result_note = $investigation_info->result_note;
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td><?= $transplant_investigation->test_name; ?></td>
                                    <td><?= $transplant_investigation->category; ?></td>
                                    <td><?= date(' d M, Y', strtotime($transplant_investigation->date_conducted)); ?></td>
                                    <td><?= $result_note; ?></td>
                                    <td>
                                        <form action="<?= $editFormAction; ?>" method="post" onsubmit="return confirm('Are you sure ?')">
                                            <input type="hidden" name="id" value="<?= $transplant_investigation->id; ?>">
                                            <button type="submit" class="btn btn-xs btn-danger" name="removeInvestigationBtn"><i class="fa fa-trash"></i> Remove </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                <?php }  ?>
            </div>
        </div>
    </div>
</div>


<hr>

<div class="row">
    <div class="col-md-8">
        <div id="donor_table_wrap">
            <h3> <u>Donors </u></h3>
            <?php

            $donor_list = $Transplant->getDonor(['transplant_id' => $transplant_id], true);

            foreach ($donor_list as $key => $donor) {
            ?>
                <div class="row" style="border: 2px solid #888">

                    <div class="col-md-6">
                        <span style="font-size:18px">Name: </span>
                        <span style="font-size:20px"><?php echo '  ' . $donor->name; ?>
                        </span>
                        <br>
                        <table class="table border">
                            <tr>
                                <td><strong>Gender:</strong>&nbsp; <?= $donor->gender; ?></td>
                                <td><strong>Blood/Group::&nbsp; <?= $donor->blood_group; ?></strong></td>
                                <td><strong>Phone:&nbsp; <?= $donor->phone_number; ?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Address:</strong> <?= $donor->address; ?><br>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="col-md-6">
                        <h3 class="text-right"> Crossmatch </h6>
                            <br>
                            <h4 class="text-right">
                                <span class="text-<?= ($donor->comment >= "Positive" ? 'info' : 'danger'); ?>"><?= $donor->comment; ?></span>

                                <?php
                                if (!empty($donor->percentage)) {
                                ?>
                                    / <?php /*?><span class="text-<?= ($donor->percentage >= 50 ? 'success' : 'danger'); ?>"><?= '0 - ' . $donor->percentage; ?>%</span><?php */?>
								<strong><?= $donor->percentage; ?></strong>
                                <?php
                                }
                                ?>

                            </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="text-center">
                            <a href="#<?= $donor->id; ?>" class="btn btn-default btn-xs " data-toggle="modal" data-target="#addHLAResultModal<?= $donor->id; ?>"> Add HLA Result</a> |
                            <?php
                            if (!empty($donor->hla_result_link)) {
                            ?>
                                <a href="<?= $donor->hla_result_link; ?>" target="_BLANK" class="btn btn-default btn-xs "> View HLA Result</a> |
                            <?php
                            }
                            ?>
                            <a href="#<?= $donor->id; ?>" class=" btn btn-default btn-xs " data-toggle="modal" data-target="#addCrossMatchResultModal<?= $donor->id; ?>"> Add Crossmatch Result</a> |
                            <form action="<?= $editFormAction; ?>" method="post" onsubmit="return confirm('Are you sure?') " style="display: inline;">
                                <input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
                                <button type="submit" class=" btn btn-default btn-xs " name="removeDonorBtn"> Remove Donor</button>
                            </form>
                        </h2>
                    </div>
                </div>

                <div class="modal inmodal fade" id="addHLAResultModal<?= $donor->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title" id="">HLA Result </h4>
                            </div>
                            <div class="modal-body" style="min-height: 300px;">
                                <form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
                                    <div>
                                        <label for="reg_input_no" class="req">HLA Result File [jpg, png, pdf] </label>
                                        <input type="file" name="hla_result_file" id="hla_result_file" class="form-control">
                                    </div>
                                    <br>

                                    <div>
                                        <input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
                                        <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
                                        <button type="submit" class="btn btn-primary" name="addHLAResultBtn" style="display: block; width:100%;">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal inmodal fade" id="addCrossMatchResultModal<?= $donor->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title" id="">Crossmatch Result </h4>
                            </div>
                            <div class="modal-body" style="min-height: 300px;">
                                <form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
                                    <div>
                                        <label for="reg_input_no" class="req">Result (%)</label>
                                        <input type="text" maxlength="5" name="percentage" id="percentage" value="<?= $donor->percentage; ?>" class="form-control">
                                    </div>
                                    <br>
                                    <div>
                                        <label for="reg_input_no" class="req">Comment</label>
                                        <select name="comment" id="comment" class="form-control">
                                            <option value=""></option>
                                            <option value="Negative" <?= $donor->comment == "Negative" ? "selected" : ""; ?>>Negative</option>
                                            <option value="Positive" <?= $donor->comment == "Positive" ? "selected" : ""; ?>>Positive</option>
                                        </select>
                                    </div>
                                    <br>
                                    <div>
                                        <input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
                                        <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
                                        <button type="submit" class="btn btn-primary" name="addCrossMatchResultBtn" style="display: block; width:100%;">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            <?php
            }


            ?>
        </div>
    </div>
    <div class="col-md-4">

        <div>

            <div class="form_sep">
                <label for="reg_input_no" class="req">Select Donor </label>
                <select name="donor" id="donor_hospital_no" class="input-sm chosen-select" required>
                    <option value="">Select Donor</option>
                    <?php
                    $patients = $Patient->getLight([], true);
                    foreach ($patients as $key => $patient) {
                        if ($patient->hospital_no != $hospital_no) {
                    ?>
                            <option value="<?= $patient->hospital_no; ?>"> <?= $patient->hospital_no . ' - ' . $patient->surname . '  ' . $patient->fname . '  ' . $patient->oname; ?> </option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </div>

            <br>
            <div>
                <input type="hidden" name="transplant_id" id="transplant_id_donor_form" value="<?= $transplant_id; ?>">
                <button class="btn btn-sm btn-success" id="load_donor_record_btn"> Open Donor Form</button>
            </div>


        </div>
    </div>
</div>



<div class="modal inmodal fade" id="donorFormModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Donor Form </h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <form action="<?= $editFormAction; ?>" method="POST" name="subject" id="donor_form_wrap" enctype="multipart/form-data">

                </form>
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="viewTransplantDocumentsModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Documents </h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <h2> Transplant Documents </h2>
                <?php
                $related_table_id = $transplant_id;
                $related_table = "Transplant";
                $module = "Transplant";
                include_once('_uploadDocumentModal.php');
                ?>

                <div class="well well-sm">
                    <?php
                    $patient_docs = $Document->get(['hospital_no' => $hospital_no, 'module' => 'Transplant', 'related_table_id' => $transplant_id], true);

                    if (count($patient_docs) > 0) {
                    ?>
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Title</th>
                                    <th>Date Uploaded</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sn = 1;
                                foreach ($patient_docs as $key => $patient_doc) {
                                ?>
                                    <tr>
                                        <th><?= $sn++; ?></th>
                                        <th><?= $patient_doc->title; ?></th>
                                        <th><?= date('d M, Y', strtotime($patient_doc->created_at)); ?></th>
                                        <th><a href="<?= $patient_doc->link; ?>" target="_BLANK"> View Document</a></th>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                        echo '<h3> Documents: No document is uploaded yet </h3>';
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="viewSpecialistNotesModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Specialist Notes </h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <?php

                $stmt = $db->prepare("SELECT sn, notes, date_entry FROM notes WHERE  hospital_no = ? AND notes_type = 'CONS'  AND status = '1' order by sn desc  LIMIT 10 ");
                $stmt->execute(array($hospital_no));
                if ($stmt->rowCount() > 0) {
                ?>
                    <h2 class="text-center"><u>------- Notes------- </u></h2>
                    <?php
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $key => $row) {

                    ?>
                        <u><b>Date: <?= dateFormat_($row['date_entry']); ?></b></u>
                        <div><?= $row["notes"]; ?></div>
                        <hr>
                <?php
                    }
                }
                ?>

            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="addPostOperationalNotesModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Post Operation Note </h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">


            </div>
        </div>
    </div>
</div>