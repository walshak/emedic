<?php

if (isset($_POST['search_patient_donor'])) {
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');

    $message = 'Error: Something went wrong..';
    $status = 401;
    $data = null;

    $hospital_no = $_POST['hospital_no'];
    $transplant_id = $_POST['transplant_id'];
    $patient_transplant_info = $Transplant->get(['id' => $transplant_id]);

    if ($hospital_no == $patient_transplant_info->hospital_no) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "You cannot add receipient as donor", "status" => $status, "data" => $data]);
        exit;
    }

    $check = $db->prepare("SELECT id FROM transplants_donors WHERE hospital_no = ? AND transplant_id = ? ");
    $check->execute(array(
        $hospital_no,
        $transplant_id
    ));

    if ($check->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(["message" => "[$hospital_no] is already a donor", "status" => $status, "data" => $data]);
        exit;
    }

    $patient_info = $Patient->get(['hospital_no' => $hospital_no]);
    if (!empty($patient_info)) {
        $name = $patient_info->surname . ' ' . $patient_info->fname . ' ' . $patient_info->oname;
?>


        <div>
            <label for="reg_input_no" class="req">Name:</label>
            <input type="text" name="name" id="name" value="<?= $name; ?>" placeholder="Full name" maxlength="100" class="form-control" readonly>
        </div>
        <div>
            <label for="reg_input_no" class="req">Blood Group </label>
            <select class="form-control" name="blood_group" required>
                <option value=""> </option>
                <option value="A+" <?= $patient_info->blood_g == "A+" ? "selected" : "" ?>> A+ </option>
                <option value="O+" <?= $patient_info->blood_g == "O+" ? "selected" : "" ?>> O+</option>
                <option value="B+" <?= $patient_info->blood_g == "B+" ? "selected" : "" ?>> B+</option>
                <option value="AB+" <?= $patient_info->blood_g == "AB+" ? "selected" : "" ?>> AB+</option>
                <option value="A-" <?= $patient_info->blood_g == "A-" ? "selected" : "" ?>> A-</option>
                <option value="O-" <?= $patient_info->blood_g == "O-" ? "selected" : "" ?>> O-</option>
                <option value="B-" <?= $patient_info->blood_g == "B" ? "selected" : "" ?>> B-</option>
                <option value="AB-" <?= $patient_info->blood_g == "AB" ? "selected" : "" ?>> AB-</option>
            </select>
        </div>

        <br>

        <div>
            <label for="reg_input_no" class="req">Genotype </label>
            <select class="form-control" name="genotype" required>
                <option value=""> </option>
                <option value="AA" <?= $patient_info->geno_type == "AA" ? "selected" : "" ?>> AA </option>
                <option value="AS" <?= $patient_info->geno_type == "AS" ? "selected" : "" ?>> AS </option>
                <option value="SS" <?= $patient_info->geno_type == "SS" ? "selected" : "" ?>> SS </option>
                <option value="AC" <?= $patient_info->geno_type == "AC" ? "selected" : "" ?>> AC </option>
            </select>
        </div>
        <div>
            <label for="reg_input_no" class="req">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" value="<?= $patient_info->phone; ?>" maxlength="14" placeholder="Phone Number" class="form-control" required>
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="req">Address </label>
            <input type="text" name="address" id="address" value="<?= $patient_info->addr; ?>" placeholder="" class="form-control" required>
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="">NOK Name:</label>
            <input type="text" name="nok_name" id="nok_name" placeholder="" class="form-control">
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="">NOK Phone number</label>
            <input type="text" name="nok_phone_number" id="nok_phone_number" maxlength="14" placeholder="" class="form-control">
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="">NOK Address</label>
            <input type="text" name="nok_address" id="nok_address" placeholder="" class="form-control">
        </div>
        <br>
        <p id="addDonorStatusArea"></p>
        <p class="text-right">
            <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>" placeholder="" class="form-control" required>
            <input type="hidden" name="addDonor" value="true" placeholder="" class="form-control" required>
            <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>" placeholder="" class="form-control" required>
            <button type="submit" class="btn btn-primary" name="addDonorBtn">Save</button>
            <button class="btn btn-danger" id="reset-patient-donor-form">Reset</button>
            <button class="btn btn-danger" data-dismiss="modal">Close</button>
        </p>


    <?php
        exit;
    } else {
        header('Content-Type: application/json');
        $message = 'Error: Search not found..';
    }



    echo json_encode(["message" => $message, "status" => $status, "data" => $data]);
    exit;
}


if (isset($_POST['selected_donor_btn'])) {
    $donor_id = $_POST['donor_id'];
    $is_matched = '1';

    $stmt = $db->prepare("UPDATE transplants_donors SET is_matched = ? WHERE id = ? ");
    $update = $stmt->execute(array(
        $is_matched,
        $donor_id
    ));

    if ($update) {
        $error_status = 2;
        $error_msg = "  Updated successfully...";
    } else {
        $error_msg = "  Update failed...";
    }
}



if (isset($_POST['fetch_edit_trans_note'])) {
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');

    $trans_note_id = $_POST['trans_note_id'];


    $trans_note_stmt = $db->prepare("SELECT n.*, tn.id tn_id,tn.template_id, tn.transplant_id  FROM transplant_notes tn INNER JOIN notes n ON tn.notes_id = n.sn WHERE tn.id = ? and tn.status = '1' ");
    $trans_note_stmt->execute(array($trans_note_id));

    if ($trans_note_stmt->rowCount() > 0) {
        $transplant_notes_row = $trans_note_stmt->fetch(PDO::FETCH_ASSOC);
        $notes = $transplant_notes_row['notes'];
        $sn = $transplant_notes_row['sn'];
        $template_id = $transplant_notes_row['template_id'];
        $transplant_id = $transplant_notes_row['transplant_id'];


    ?>


        <div>
            <label for="select_template">Select Note Template</label>
            <select class="input-sm chosen-select "
                onchange="load_templates(this.value, 'edit_consulation_notes')"
                style="width:350px;"
                name="template_id">
                <option value="blank"> Blank Note </option>
                <?php


                foreach ($templates as $key => $template) {
                ?>
                    <option value="<?= $template["id"]; ?>" <?php echo ($template_id == $template["id"] ? 'selected' : ''); ?>><?= $template["template_name"]; ?></option>
                <?php
                }
                ?>
            </select>
            <div id="pre-opt-temp-load-status"></div>
        </div>
        <div>
            <br>
            <label for="select_template"> Enter Note Below: </label>
            <div id="edit_consulation_notes_wrap"><textarea name="edit_consulation_notes" id="edit_consulation_notes" cols="30" rows="10" class="summernote" style="margin-top:0px"><?php echo $notes; ?> </textarea></div>
        </div>
        <input type="hidden" name="transplant_id" value="<?php echo $transplant_id; ?>">
        <input type="hidden" name="sn" value="<?php echo $sn; ?>">
        <input type="hidden" name="trans_note_id" value="<?php echo $trans_note_id; ?>">



<?php
    }


    exit;
}


$appointment_number = null;
if (isset($_POST['addDSAResultBtn'])) {

    $hospital_no = $_POST['hospital_no'];
    $transplant_id = $_POST['transplant_id'];
    $target_dir = "../documents/transplant/";

    $error_status = 1;
    $error_msg = "Error: Something went wrong...";

    $isDsaResultUploaded = false;
    $dsa_target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', $title . basename($_FILES["dsa_result_file"]["name"]));
    $dsa_imageFileType = strtolower(pathinfo($dsa_target_file, PATHINFO_EXTENSION));



    if ($dsa_imageFileType == 'pdf' || $dsa_imageFileType == 'jpg' || $dsa_imageFileType == 'png' || $dsa_imageFileType == 'jpeg') {
        $dsa_tmp_name = $_FILES['dsa_result_file']['tmp_name'];

        if (move_uploaded_file($dsa_tmp_name, $dsa_target_file)) {
            $save_dsa_result = $Document->save($hospital_no, 'DSA Result', $dsa_target_file, $dsa_imageFileType, 'Transplant',  'transplants', $transplant_id, $_SESSION["id"]);
            $isDsaResultUploaded = true;
        }
    }

    if ($isDsaResultUploaded == true) {
        $stmt = $db->prepare("UPDATE transplants SET dsa_result_link = ? WHERE id = ? ");
        $update = $stmt->execute(array(
            $dsa_target_file,
            $transplant_id
        ));

        if ($update) {
            $error_status = 2;
            $error_msg = "  Updated successfully...";
        } else {
            $error_msg = "  Update failed...";
        }
    } else {


        if ($isDsaResultUploaded == false) {
            $error_msg = "  DSA Result not uploaded...";
        }
    }
}

if (isset($_POST['addHLAResultBtn'])) {
    $hospital_no = $_POST['hospital_no'];
    $transplant_id = $_POST['transplant_id'];
    $result_type = $_POST['result_type'];
    $target_dir = "../documents/transplant/";

    $error_status = 1;
    $error_msg = "Error: Something went wrong...";
    $isHlaResultUploaded = false;

    $hla_target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', $title . basename($_FILES["result_file"]["name"]));

    $hla_imageFileType = strtolower(pathinfo($hla_target_file, PATHINFO_EXTENSION));

    if ($hla_imageFileType == 'pdf' || $hla_imageFileType == 'jpg' || $hla_imageFileType == 'png' || $hla_imageFileType == 'jpeg') {
        $hla_tmp_name = $_FILES['result_file']['tmp_name'];

        if (move_uploaded_file($hla_tmp_name, $hla_target_file)) {
            $save_hla_result = $Document->save($hospital_no, $result_type, $hla_target_file, $hla_imageFileType, 'Transplant',  'transplants', $transplant_id, $_SESSION["id"]);
            $isHlaResultUploaded = true;
        }
    }

    if ($isHlaResultUploaded == true) {
        $stmt = $db->prepare("INSERT INTO transplant_results (transplant_id, result_link, result_type, result_notes, created_by) VALUES (?, ?, ?, ?, ?) ");
        $save = $stmt->execute(array(
            $transplant_id,
            $hla_target_file,
            $_POST['result_type'],
            $_POST['result_notes'],
            $_SESSION['id']
        ));

        if ($save) {
            $error_status = 2;
            $error_msg = "  Saved successfully...";
        } else {
            $error_msg = "  Saving failed...";
        }
    } else {
        if ($isHlaResultUploaded == false) {
            $error_msg = "   Result not uploaded ...";
        }
    }
}



if (isset($_POST['removeHLAResultBtn'])) {
    $id = $_POST['result_id'];
    $hla_target_file = null;
    $stmt = $db->prepare("UPDATE transplant_results SET status = '0' WHERE id = ? ");
    $update = $stmt->execute(array($id));
}

if (isset($_POST['removeDSAResultBtn'])) {
    $transplant_id = $_POST['transplant_id'];
    $dsa_result_link = null;
    $stmt = $db->prepare("UPDATE transplants SET dsa_result_link = ? WHERE id = ? ");
    $update = $stmt->execute(array(
        $dsa_result_link,
        $transplant_id
    ));
}


if (isset($_POST['addDonorHLAResultBtn'])) {
    $donor_id = $_POST['donor_id'];
    $transplant_id = $_POST['transplant_id'];
    $target_dir = "../documents/transplant/";
    $target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', basename($_FILES["hla_result_file"]["name"]));
    $uploadOk = 1;

    $error_status = 1;
    $error_msg = "Oops! Something went wrong";

    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


    if ($imageFileType == 'pdf' || $imageFileType == 'jpg' || $imageFileType == 'png' || $imageFileType == 'jpeg') {
        $tmp_name = $_FILES['hla_result_file']['tmp_name'];

        if (move_uploaded_file($tmp_name, $target_file)) {
            $stmt = $db->prepare("UPDATE transplants_donors SET hla_result_link = ?, result_notes=? WHERE id = ? ");
            $update = $stmt->execute(array(
                $target_file,
                $_POST['result_notes'],
                $donor_id
            ));
            $error_status = 2;
            $error_msg = 'Result uploaded successfully...';
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

if (isset($_GET['trs'])) {

    $transplant_id = base64_decode(base64_decode($_GET['trs']));
    $patient_transplant_info = $Transplant->get(['id' => $transplant_id]);

    // print_r($patient_transplant_info);
    $admin_user = $AdminUser->find($patient_transplant_info->created_by);
    if (!empty($patient_transplant_info)) {





        $hosp_no = $patient_transplant_info->hospital_no;
        $hospital_no = $patient_transplant_info->hospital_no;
        $appointment_number = $patient_transplant_info->app_no;

        //  $patient_info = $Patient->getLight(['hospital_no' => $hosp_no]);
        $patient_info = $Patient->getByHospitalNo($hosp_no);

        if (!empty($patient_info)) {
            $patient_name = $patient_info->fname . ' ' . $patient_info->surname;
            ///echo $blood_g= $patient_info->blood_g;
        } else {
            echo 'Patient info not found!';
            exit;
        }
    } else {
        exit;
    }
} else {
    echo 'Transplant token not found!';
    exit;
}





?>

<div class="row">
    <div class="col-lg-12">
        <div class="wrapper wrapper-content animated fadeInUp">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="m-b-md">
                                <a href="index.php?transplant" class="btn btn-success pull-left"> <i class="fa fa-home"></i> Transplant List </a>

                                <?php if ($_SESSION['rights'] == 'NS') { ?>
                                    <a href="../nursing/patient.php?hosp_no=<?php echo $hospital_no; ?>" class="btn btn-info pull-right"> <i class="fa fa-user"></i> Patient Dashboard </a>
                                <?php } elseif ($_SESSION['rights'] == 'DR') { ?>
                                    <a href="patient.php?hosp_no=<?php echo $hospital_no; ?>" class="btn btn-info pull-right"> <i class="fa fa-user"></i> Patient Dashboard </a>
                                <?php } ?>

                                <a href="#" class=" btn-xs pull-right"> | </a>
                                <a href="#" class="btn btn-primary pull-right open-modal-btn" arial-modal="donor-form-modal"> <i class="fa fa-users"></i> Add Donor </a>
                                <a href="#" class=" btn-xs pull-right"> | </a>
                                <a href="#" class="btn btn-default pull-right" onclick="ClickheretoprintDiv('printable-area')"> <i class="fa fa-print"></i> Print </a>




                            </div>
                            <br>

                        </div>
                    </div>


                    <div id="printable-area" style="border: 2px solid #000;padding: 20px">
                        <div>
                            <h4 class="text-center">
                                <p><img src="../img/logo.png" alt="logo" width="100px"></p>
                                <?php echo $_SESSION['h_name']; ?>
                            </h4>
                            <h5 class="text-center"><?php echo $_SESSION['h_address']; ?> <br> <?php echo $_SESSION['h_phone']; ?></h5>
                        </div>


                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:14px; font-family:Arial, Helvetica, sans-serif;">
                                    <tr>
                                        <td colspan="6" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left">
                                            <h2 align="center"><?= $patient_transplant_info->transplant_type; ?> </h2>
                                            <h3 align="center">Amount: N <?php echo number_format($patient_transplant_info->amount, 2);


                                                                            $stmt = $db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE hospital_no='$hospital_no' and account_no=2121");
                                                                            if ($stmt->rowCount() > 0) {
                                                                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                                $TOTAL_CREDITS = $row['TOTAL_CREDITS'];
                                                                                $TOTAL_DEBITS = $row['TOTAL_DEBITS'];
                                                                            }
                                                                            $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;

                                                                            ?>
                                                &nbsp; | &nbsp; Amount Deposit: <?php echo number_format($current_balance, 2); ?> </h3>

                                            <?php $performed_date = $patient_transplant_info->performed_date; ?>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="border-bottom: 1px solid #000; text-align:left">
                                            <b>Hospital Number:</b> <?php echo $hospital_no; ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <b>Name:</b> <?php echo $patient_transplant_info->patient_name; ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <b>Blood Group:</b> <?php echo $patient_info->blood_g; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Requested by:</b>&nbsp;<?php echo $admin_user->fullname; ?></td>
                                        <td><b>Requested Date:</b>&nbsp;<?php echo date('d M, Y h:i:s A', strtotime('' . $patient_transplant_info->created_at . '')); ?></td>
                                        <td><b>Date of Operation:</b>&nbsp;<?php echo ($patient_transplant_info->performed_date != null ? date('d M, Y', strtotime('' . $patient_transplant_info->performed_date . '')) : ''); ?></td>
                                        <td><b>Surgeon/Consultant:</b>&nbsp;<?php echo $patient_transplant_info->main_surgeon; ?></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h4>Transplant Note(s) </h4>
                                            <div id="operation_notes">
                                                <!-- Js shall load content area from _transplant_consultation_notes -->
                                            </div>
                                        </td>
                                    </tr>




                                    <tr>
                                        <td colspan="6">
                                            <div>

                                                <a href="#" class="open-modal-btn" arial-modal="addHLAResultModal"><i class="fa fa-paperclip "></i> Click to Attach HLA/DSA Result & Legal Docs.</a>
                                                <br>
                                            </div>
                                            <?php
                                            $stmt = $db->prepare("SELECT * FROM transplant_results  WHERE transplant_id = ? AND status = '1' ");
                                            $stmt->execute(array($transplant_id));

                                            if ($stmt->rowCount()  > 0) {
                                            ?>
                                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:14px; font-family:Arial, Helvetica, sans-serif;">
                                                    <thead>
                                                        <tr>
                                                            <th style="border-bottom: 1px solid #000; text-align:left">Result Type</th>
                                                            <th width="60%" style="border-bottom: 1px solid #000; text-align:left">Notes</th>
                                                            <th style="border-bottom: 1px solid #000; text-align:left">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($results as $key => $result_) {
                                                        ?>
                                                            <tr>
                                                                <td style="border-bottom: 1px solid #000; text-align:left">
                                                                    <?php echo $result_['result_type']; ?>
                                                                </td>
                                                                <td style="border-bottom: 1px solid #000; text-align:left">
                                                                    <?php echo $result_['result_notes']; ?>
                                                                </td>
                                                                <td style="border-bottom: 1px solid #000; text-align:left">

                                                                    <form action="<?php echo $editFormAction; ?>" method="post" onsubmit="return confirm('Do you want to remove <?php echo $result_['result_type']; ?> Result?')" style="display:inline">
                                                                        <button class="btn-white" type="submit" name="removeHLAResultBtn" <?php if ($performed_date != '') { ?> disabled <?php } ?>><b>[Remove Result]</b></button>
                                                                        <input type="hidden" name="result_id" value="<?= $result_['id']; ?>">
                                                                    </form>

                                                                    <a href="<?php echo $result_['result_link']; ?>" target="_BLANK" class="text-success "> | <b>[View Result]</b></a>
                                                                </td>
                                                            </tr>

                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php
                                            }
                                            ?>



                                        </td>

                                    </tr>

                                    <tr>
                                        <td colspan="6">
                                            <br>
                                            <h3 class="text-left"> Donors: </h4>
                                                <div>
                                                    <?php

                                                    $donor_list = $Transplant->getDonor(['transplant_id' => $transplant_id], true);
                                                    if (count($donor_list) > 0) {
                                                    ?>
                                                        <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:14px; font-family:Arial, Helvetica, sans-serif;">
                                                            <tr>
                                                                <th style="border: 1px solid #000; text-align:left">#</th>
                                                                <th style="border: 1px solid #000; text-align:left">Hosp. #</th>
                                                                <th style="border: 1px solid #000; text-align:left">Name</th>
                                                                <th style="border: 1px solid #000; text-align:left">Gender</th>
                                                                <th style="border: 1px solid #000; text-align:left">Blood Group</th>
                                                                <th style="border: 1px solid #000; text-align:left">Geno Type</th>
                                                                <th style="border: 1px solid #000; text-align:left">Phone Number</th>
                                                                <th style="border: 1px solid #000; text-align:left">Cross Result</th>
                                                                <th style="border: 1px solid #000; text-align:left">Comment</th>
                                                                <th style="border: 1px solid #000; text-align:left">Status</th>
                                                            </tr>
                                                            <?php
                                                            $sn = 1;
                                                            $selected_yes = '';
                                                            foreach ($donor_list as $key => $donor) {
                                                                $selected = $donor->is_matched;

                                                            ?>
                                                                <tr <?php if ($selected == '1') { ?>style="background-color: aquamarine" <?php } ?>>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= $sn++; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?php echo $donor->hospital_no; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?php echo $donor->name; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= $donor->gender; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= $donor->blood_group; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= $donor->genotype; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= $donor->phone_number; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= ($donor->percentage != null ? '0 - ' . $donor->percentage . '%' : ''); ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?= $donor->comment; ?></td>
                                                                    <td style="border: 1px solid #000; text-align:left"><?php
                                                                                                                        if ($selected == '1') {
                                                                                                                            $selected_yes = 'yes';
                                                                                                                        ?>
                                                                            <strong style="color: red; "><i>SELECTED DONOR</i></strong>;
                                                                        <?php }

                                                                        ?>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </table>
                                                    <?php
                                                    }


                                                    ?>
                                                </div>

                                        </td>
                                    </tr>


                                </table>
                            </div>
                        </div>











                    </div>
                    <div class="row m-t-sm">
                        <div class="col-lg-12">
                            <div class="panel blank-panel">
                                <div class="panel-heading">
                                    <div class="panel-options">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a href="#tab-1" data-toggle="tab" style="font-size: 14px; color: black;">Donors</a></li>
                                            <li class=""><a href="#tab-cons-note" data-toggle="tab" style="font-size: 14px; color: black;"> Notes and Post-Operation</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="panel-body">

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab-1">
                                            <div class="well well-sm">
                                                <div class="light-card">
                                                    <?php include_once('_transplant_donors.php'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="tab-cons-note">
                                            <div>
                                                <div class="text-right">
                                                    <button class="btn btn-success open-modal-btn" arial-modal="consultation_notes_modal" <?php if ($_SESSION['rights'] != 'DR') { ?>disabled><?php } ?><i class="fa fa-edit"></i> Enter New Note</button>
                                                </div>
                                            </div>
                                            <?php include_once('_transplant_consultation_notes.php'); ?>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>


<div class="modal inmodal fade" id="addHLAResultModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id=""> ResultS </h4>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="result_type">Result Type</label>
                        <select name="result_type" id="result_type" class="input-sm hosen-select form-control" required>
                            <option value=""> Select result type</option>
                            <option value="DSA"> DSA Result</option>
                            <option value="HLA"> HLA Result</option>
                            <option value="Cross Match"> Cross Match</option>
                            <option value="Legal Documents"> Legal Documents</option>
                        </select>
                    </div>
                    <br>
                    <div>
                        <label for="result_file" class="req"> Result File [jpg, png, pdf] </label>
                        <input type="file" name="result_file" id="result_file" class="form-control">
                    </div>
                    <br>
                    <div>
                        <label for="result_notes" class=""> Notes: </label>
                        <textarea name="result_notes" id="result_notes" class="form-control" cols="30" rows="10"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                    <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
                    <button type="submit" class="btn btn-primary" name="addHLAResultBtn">Save</button>
                    <button class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="addDSAResultModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">DSA Result </h4>
                </div>
                <div class="modal-body">

                    <div>
                        <label for="reg_input_no" class="req">DSA Result File [jpg, png, pdf] </label>
                        <input type="file" name="dsa_result_file" id="dsa_result_file" class="form-control">
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                    <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
                    <button type="submit" class="btn btn-primary" name="addDSAResultBtn">Save</button>
                    <button class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="donor-form-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="min-height:500px">
        <div class="modal-content">
            <form action="<?php echo $editFormAction; ?>" method="post">
                <div class="modal-body">
                    <div id="search-patient-donor-wrap">
                        <br>
                        <br>
                        <div>
                            <label for="patient-id-input">Search For Patient:</label>
                            <input type="text" class="form-control" name="patient-id-input" id="patient-id-input" placeholder="Patient EMR ID/NO.">
                            <input type="hidden" class="form-control" name="transplant_id" id="transplant_id" value="<?php echo $transplant_id; ?>">
                            <br>
                            <span class="search-patient-donor-status"></span>
                            <p class="text-right"><button class="btn btn-primary" id="search-patient-donor-btn">Search</button>
                                <button class="btn btn-danger" data-dismiss="modal">Close</button>
                            </p>
                        </div>
                        <br>
                        <br>
                    </div>
                    <div id="search-patient-donor-form-wrap">
                        <br>
                        <form action="<?php echo $editFormAction; ?>" method="post">
                            <div></div>
                        </form>
                    </div>
                </div>

            </form>
        </div>
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

<script>
    document.getElementById('search-patient-donor-form-wrap').style.display = 'none';
    var transplant_id = <?php echo $transplant_id; ?>;
</script>