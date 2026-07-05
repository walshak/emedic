<?php

if (isset($_REQUEST['update-changes'])) {
    // Clean each input
    // Iterate through the $_POST array
    foreach ($_POST as $key => $value) {
        // Replace the value with the cleaned input
        $_POST[$key] = cleanInput($value);
    }

    // Prepare the SQL statement
    $sql = "UPDATE ivf_form SET 
                ivf_date = ?,
                ivf_hosp_no = ?,
                ivf_husband_name = ?,
                ivf_wife_name = ?,
                ivf_age = ?,
                ivf_tel = ?,
                ivf_treat_plan = ?,
                ivf_protocol = ?,
                start_date = ?,
                ivf_gnrha = ?,
                ivf_gonadotrophin = ?,
                ivf_days_of_stimulation = ?,
                ivf_hcg = ?,
                ivf_dose = ?,
                ivf_date_administered = ?,
                ivf_sample_type = ?,
                ivf_date_of_analysis = ?,
                ivf_volume = ?,
                ivf_vicosity = ?,
                ivf_conc_count = ?,
                ivf_mortile_count = ?,
                ivf_morphology = ?,
                ivf_remarks = ?,
                ivf_no_of_folicles = ?,
                ivf_retrival_date = ?,
                ivf_no_of_eggs = ?,
                ivf_no_fertilized = ?,
                ivf_fertiliztion_method = ?,
                ivf_no_cleaved = ?,
                ivf_no_frozen = ?,
                ivf_no_transferd = ?,
                ivf_transfer_date = ?,
                ivf_embrayo_grade = ?,
                ivf_blastocyst = ?,
                ivf_blastocyst_no_frozen = ?,
                ivf_pregnancy_test_date = ?,
                ivf_support_drugs = ?,
                ivf_embryologist = ?,
                ivf_fertility_specialist = ?,
                ivf_IVF_nurse = ? WHERE id = ? ";

    // Prepare the statement
    $stmt = $db->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(1, $_POST['ivf_date']);
    $stmt->bindParam(2, $_POST['ivf_hosp_no']);
    $stmt->bindParam(3, $_POST['ivf_husband_name']);
    $stmt->bindParam(4, $_POST['ivf_wife_name']);
    $stmt->bindParam(5, $_POST['ivf_age']);
    $stmt->bindParam(6, $_POST['ivf_tel']);
    $stmt->bindParam(7, $_POST['ivf_treat_plan']);
    $stmt->bindParam(8, $_POST['ivf_protocol']);
    $stmt->bindParam(9, $_POST['start_date']);
    $stmt->bindParam(10, $_POST['ivf_gnrha']);
    $stmt->bindParam(11, $_POST['ivf_gonadotrophin']);
    $stmt->bindParam(12, $_POST['ivf_days_of_stimulation']);
    $stmt->bindParam(13, $_POST['ivf_hcg']);
    $stmt->bindParam(14, $_POST['ivf_dose']);
    $stmt->bindParam(15, $_POST['ivf_date_administered']);
    $stmt->bindParam(16, $_POST['ivf_sample_type']);
    $stmt->bindParam(17, $_POST['ivf_date_of_analysis']);
    $stmt->bindParam(18, $_POST['ivf_volume']);
    $stmt->bindParam(19, $_POST['ivf_vicosity']);
    $stmt->bindParam(20, $_POST['ivf_conc_count']);
    $stmt->bindParam(21, $_POST['ivf_mortile_count']);
    $stmt->bindParam(22, $_POST['ivf_morphology']);
    $stmt->bindParam(23, $_POST['ivf_remarks']);
    $stmt->bindParam(24, $_POST['ivf_no_of_folicles']);
    $stmt->bindParam(25, $_POST['ivf_retrival_date']);
    $stmt->bindParam(26, $_POST['ivf_no_of_eggs']);
    $stmt->bindParam(27, $_POST['ivf_no_fertilized']);
    $stmt->bindParam(28, $_POST['ivf_fertiliztion_method']);
    $stmt->bindParam(29, $_POST['ivf_no_cleaved']);
    $stmt->bindParam(30, $_POST['ivf_no_frozen']);
    $stmt->bindParam(31, $_POST['ivf_no_transferd']);
    $stmt->bindParam(32, $_POST['ivf_transfer_date']);
    $stmt->bindParam(33, $_POST['ivf_embrayo_grade']);
    $stmt->bindParam(34, $_POST['ivf_blastocyst']);
    $stmt->bindParam(35, $_POST['ivf_blastocyst_no_frozen']);
    $stmt->bindParam(36, $_POST['ivf_pregnancy_test_date']);
    $stmt->bindParam(37, $_POST['ivf_support_drugs']);
    $stmt->bindParam(38, $_POST['ivf_embryologist']);
    $stmt->bindParam(39, $_POST['ivf_fertility_specialist']);
    $stmt->bindParam(40, $_POST['ivf_IVF_nurse']);
    $stmt->bindParam(41, $_POST['token']);

    // Execute the statement
    $stmt->execute();

    // Check if the insertion was successful
    if ($stmt->rowCount() > 0) {
        $error_status = 2;
        $error_msg = 'Success : Form Saved Successfully!';
    } else {
        $error_status = 1;
        $error_msg = 'Failiure : Form not Saved!';
    }
}




$form_sn = base64_decode(base64_decode($_GET['pr']));
$patient_procedure_stmt = $db->prepare("SELECT * from ivf_form WHERE id=? ORDER BY id DESC LIMIT 1");
$patient_procedure_stmt->execute(array($form_sn));
if ($patient_procedure_stmt->rowCount() > 0) {
    $form = $patient_procedure_stmt->fetch(PDO::FETCH_ASSOC);
    $hospital_no = $form['ivf_hosp_no'];
    $patient_info = $Patient->get(['hospital_no' => $hospital_no]);
    if (!empty($patient_info)) {
        $hospital_no = $patient_info->hospital_no;
        $sn = 1;
    }
} else {
    exit;
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="wrapper wrapper-content animated fadeInUp">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <form action="index.php?ivf_form&pr=<?= $_GET['pr']; ?>" method="post">
                            <input type="hidden" value="<?= $form['id']; ?>" name="token">
                            <div class="col-lg-12">

                                <a href="index.php?ivf_form" class="btn btn-success pull-left">
                                    <i class="fa fa-list"></i> &nbsp;Goto All Forms</a>
                                <div class="m-b-md  pull-right">
                                    <?php if ($rights == 'NS') {
                                        $href = "nursing";
                                    } else {
                                        $href = "doctor";
                                    } ?>
                                    <a href="index.php?ivf_form&pr=<?= base64_encode(base64_encode($form_sn)); ?>" class="btn btn-default">
                                        <i class="fa fa-arrow"></i>&nbsp;Refresh Page</a>

                                </div>
                                <br>

                            </div>
                    </div>

                    <div id="printable-area" style="border: 2px solid #000;padding: 20px">
                        <div>
                            <h3 class="text-center">
                                <p><img src="../img/logo.png" alt="logo" width="100px"></p>
                                <?php echo $_SESSION['h_name']; ?>
                            </h3>
                            <h5 class="text-center"><?php echo $_SESSION['h_address']; ?> <br> <?php echo $_SESSION['h_phone']; ?></h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table">
                                    <tr>
                                        <td>Date</td>
                                        <td><input type="date" class="form-control" name="ivf_date" value="<?= $form['ivf_date'] ?>"></td>
                                        <td>Hospital No</td>
                                        <td><input type="text" class="form-control" name="ivf_hosp_no" value="<?= $form['ivf_hosp_no'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Husband Name</td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_husband_name" class="form-control" value="<?= $form['ivf_husband_name'] ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Wife Name</td>
                                        <td colspan="3">
                                            <?php $name_wife = $patient_info->surname . ' ' . $patient_info->fname . ' ' . (($patient_info->oname) ? $patient_info->oname : ""); ?>
                                            <input type="text" name="ivf_wife_name" value="<?php echo $name_wife; ?>" class="form-control">
                                        </td>

                                    </tr>
                                    <tr>
                                        <?php
                                        if ($patient_info->dob) {
                                            $ddd = date_diff(date_create($patient_info->dob), date_create('now'));
                                            $wife_age = $ddd->y;
                                        } else {
                                            $wife_age = "";
                                        }
                                        ?>
                                        <td>Age</td>
                                        <td><input type="text" class="form-control" name="ivf_age" value="<?= $wife_age ?>" <?= ($wife_age == "") ? "" : ''; ?>></td>
                                        <td>Tel</td>
                                        <td><input type="text" class="form-control" name="ivf_tel" value="<?= $patient_info->phone ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Treatment Plan
                                        </td>
                                        <td colspan="3">
                                            <select name="ivf_treat_plan" class="form-control">
                                                <option value="">-- Select plan --</option>
                                                <option value="IVF" <?= ($form['ivf_treat_plan'] == 'IVF') ? 'selected' : '' ?>>IVF</option>
                                                <option value="ICSI" <?= ($form['ivf_treat_plan'] == 'ICSI') ? 'selected' : '' ?>>ICSI</option>
                                                <option value="IVF/ICSI" <?= ($form['ivf_treat_plan'] == 'IVF/ICSI') ? 'selected' : '' ?>>IVF/ICSI</option>
                                                <option value="ER" <?= ($form['ivf_treat_plan'] == 'ER') ? 'selected' : '' ?>>ER</option>
                                                <option value="IVF/TESA" <?= ($form['ivf_treat_plan'] == 'IVF/TESA') ? 'selected' : '' ?>>IVF/TESA</option>
                                                <option value="IVF/PESA" <?= ($form['ivf_treat_plan'] == 'IVF/PESA') ? 'selected' : '' ?>>IVF/PESA</option>
                                                <option value="TESE" <?= ($form['ivf_treat_plan'] == 'TESE') ? 'selected' : '' ?>>TESE</option>
                                                <option value="ES" <?= ($form['ivf_treat_plan'] == 'ES') ? 'selected' : '' ?>>ES</option>
                                                <option value="FET" <?= ($form['ivf_treat_plan'] == 'FET') ? 'selected' : '' ?>>FET</option>
                                                <option value="FOT" <?= ($form['ivf_treat_plan'] == 'FOT') ? 'selected' : '' ?>>FOT</option>
                                                <option value="IVF/PGD/XY" <?= ($form['ivf_treat_plan'] == 'IVF/PGD/XY') ? 'selected' : '' ?>>IVF/PGD/XY</option>
                                                <option value="IVF/PGD/XY/HBSS" <?= ($form['ivf_treat_plan'] == 'IVF/PGD/XY/HBSS') ? 'selected' : '' ?>>IVF/PGD/XY/HBSS</option>
                                                <option value="IVF/PGD/HBSS" <?= ($form['ivf_treat_plan'] == 'IVF/PGD/HBSS') ? 'selected' : '' ?>>IVF/PGD/HBSS</option>
                                                <option value="IVF/TESA/PESA" <?= ($form['ivf_treat_plan'] == 'IVF/TESA/PESA') ? 'selected' : '' ?>>IVF/TESA/PESA</option>
                                                <option value="IVF/SD" <?= ($form['ivf_treat_plan'] == 'IVF/SD') ? 'selected' : '' ?>>IVF/SD</option>
                                                <option value="ER/SD" <?= ($form['ivf_treat_plan'] == 'ER/SD') ? 'selected' : '' ?>>ER/SD</option>
                                            </select>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Treatment Details</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Protocol
                                        </td>
                                        <td>
                                            <select name="ivf_protocol" class="form-control">
                                                <option value="">-- Select plan --</option>
                                                <option value="LONG" <?= ($form['ivf_protocol'] == 'LONG') ? 'selected' : '' ?>>LONG</option>
                                                <option value="SHORT AGONIST" <?= ($form['ivf_protocol'] == 'SHORT AGONIST') ? 'selected' : '' ?>>SHORT AGONIST</option>
                                                <option value="SHORT ANTAGONIS" <?= ($form['ivf_protocol'] == 'SHORT ANTAGONIS') ? 'selected' : '' ?>>SHORT ANTAGONIS</option>
                                                <option value="OTHERS" <?= ($form['ivf_protocol'] == 'OTHERS') ? 'selected' : '' ?>>OTHERS</option>


                                            </select>

                                        </td>
                                        <td>Start date</td>
                                        <td><input type="date" name="start_date" class="form-control" value="<?= $form['start_date'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>GnRH-a</td>
                                        <td colspan="3"><input type="text" name="ivf_gnrha" class="form-control" value="<?= $form['ivf_gnrha'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Gonadotrophin</td>
                                        <td colspan="3">
                                            <select name="ivf_gonadotrophin" class="form-control">
                                                <option value="">-- Select plan --</option>
                                                <option value="Recombinant FSH" <?= ($form['ivf_gonadotrophin'] == 'Recombinant FSH') ? 'selected' : '' ?>>Recombinant FSH</option>
                                                <option value="Pure FSH" <?= ($form['ivf_gonadotrophin'] == 'Pure FSH') ? 'selected' : '' ?>>Pure FSH</option>
                                                <option value="hMG" <?= ($form['ivf_gonadotrophin'] == 'hMG') ? 'selected' : '' ?>>hMG</option>

                                            </select>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Days of Stimulation</td>
                                        <td colspan="3"><input type="text" name="ivf_days_of_stimulation" class="form-control" value="<?= $form['ivf_days_of_stimulation'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>hGG</td>
                                        <td><input type="text" class="form-control" name="ivf_hcg" value="<?= $form['ivf_hcg'] ?>"></td>
                                        <td>Dose</td>
                                        <td><input type="text" class="form-control" name="ivf_dose" value="<?= $form['ivf_dose'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Date Administered</td>
                                        <td colspan="3"><input type="text" name="ivf_date_administered" class="form-control" value="<?= $form['ivf_date_administered'] ?>"></td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Semen Preparation Details</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Sample Type
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" value="<?= $form['ivf_treat_plan'] ?>">
                                            <select name="ivf_sample_type" class="form-control">
                                                <option value="">-- Select plan --</option>
                                                <option value="Fresh" <?= ($form['ivf_sample_type'] == 'Fresh') ? 'selected' : '' ?>>Fresh</option>
                                                <option value="Frozen" <?= ($form['ivf_sample_type'] == 'Frozen') ? 'selected' : '' ?>>Frozen</option>

                                            </select>
                                        </td>
                                        <td>Date of Analysis</td>
                                        <td><input type="date" name="ivf_date_of_analysis" class="form-control" value="<?= $form['ivf_date_of_analysis'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Volume</td>
                                        <td><input type="text" name="ivf_volume" class="form-control" value="<?= $form['ivf_volume'] ?>"></td>
                                        <td>Viscosity</td>
                                        <td><input type="text" name="ivf_vicosity" class="form-control" value="<?= $form['ivf_vicosity'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Conc. Count</td>
                                        <td><input type="text" class="form-control" name="ivf_conc_count" value="<?= $form['ivf_conc_count'] ?>"></td>
                                        <td>Motile Count</td>
                                        <td><input type="text" class="form-control" name="ivf_mortile_count" value="<?= $form['ivf_mortile_count'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Morphology</td>
                                        <td colspan="3"><input type="text" name="ivf_morphology" class="form-control" value="<?= $form['ivf_morphology'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Remarks </td>
                                        <td colspan="3"><input type="text" name="ivf_remarks" class="form-control" value="<?= $form['ivf_remarks'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Egg retrieval and Fertilization</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            No. of Follicles
                                        </td>
                                        <td><input type="date" name="ivf_no_of_folicles" class="form-control" value="<?= $form['ivf_no_of_folicles'] ?>"></td>
                                        <td>Retrieval Date</td>
                                        <td><input type="date" name="ivf_retrival_date" class="form-control" value="<?= $form['ivf_retrival_date'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>No. of Eggs</td>
                                        <td><input type="text" name="ivf_no_of_eggs" class="form-control" value="<?= $form['ivf_no_of_eggs'] ?>"></td>
                                        <td>No. Fertilized</td>
                                        <td><input type="text" name="ivf_no_fertilized" class="form-control" value="<?= $form['ivf_no_fertilized'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Fertilization Method</td>
                                        <td colspan="3">
                                            <select name="ivf_fertiliztion_method" class="form-control">
                                                <option value="">-- Select plan --</option>
                                                <option value="IVF" <?= ($form['ivf_fertiliztion_method'] == 'Fresh') ? 'selected' : '' ?>>IVF</option>
                                                <option value="ICSI" <?= ($form['ivf_fertiliztion_method'] == 'ICSI') ? 'selected' : '' ?>>ICSI</option>
                                                <option value="IVF/ICSI" <?= ($form['ivf_fertiliztion_method'] == 'IVF/ICSI') ? 'selected' : '' ?>>IVF/ICSI</option>

                                            </select>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>No Cleaved</td>
                                        <td><input type="text" name="ivf_no_cleaved" class="form-control" value="<?= $form['ivf_no_cleaved'] ?>"></td>
                                        <td>No. Frozen</td>
                                        <td><input type="text" name="ivf_no_frozen" class="form-control" value="<?= $form['ivf_no_frozen'] ?>"></td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Embryo Transfer</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            No. Transfered
                                        </td>
                                        <td><input type="text" name="ivf_no_transferd" class="form-control" value="<?= $form['ivf_no_transferd'] ?>"></td>
                                        <td>Transfer Date</td>
                                        <td><input type="date" name="ivf_transfer_date" class="form-control" value="<?= $form['ivf_transfer_date'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Cleaving/Embryo Grade</td>
                                        <td colspan="3"><input type="text" name="ivf_embrayo_grade" class="form-control" value="<?= $form['ivf_embrayo_grade'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Blastocyst</td>
                                        <td><input type="text" name="ivf_blastocyst" class="form-control" value="<?= $form['ivf_blastocyst'] ?>"></td>
                                        <td>No. Frozen</td>
                                        <td><input type="text" name="ivf_blastocyst_no_frozen" class="form-control" value="<?= $form['ivf_blastocyst_no_frozen'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Comment</td>
                                        <td colspan="3"><input type="text" name="ivf_no_cleaved" class="form-control" value="<?= $form['ivf_no_cleaved'] ?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Conclusion</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            After your embryo transfer, the pregnancy test should be done on
                                        </td>
                                        <td>
                                            <input type="date" name="ivf_pregnancy_test_date" class="form-control" value="<?= $form['ivf_pregnancy_test_date'] ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            Please contact us on <?php echo $_SESSION['h_phone']; ?> if you have any difficulties or complications
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            While awaiting pregnancy test, kindly continue taking the drugs for luteal support. These are
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_support_drugs" class="form-control" value="<?= $form['ivf_support_drugs'] ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Embryologist
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_embryologist" class="form-control" value="<?= $form['ivf_embryologist'] ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Fertility Specialist
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_fertility_specialist" class="form-control" value="<?= $form['ivf_fertility_specialist'] ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            IVF Nurse
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_IVF_nurse" class="form-control" value="<?= $form['ivf_IVF_nurse'] ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-success" name="update-changes"> <i class="fa fa-save"></i> Update Changes </button>
                            <a href="#" class="btn btn-white" onclick="ClickheretoprintDiv('printable-area')"> <i class="fa fa-print"></i> Print </a>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>



<div class="modal inmodal fade" id="resource_persons_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="min-height: 500px;">
        <div class="modal-content">
            <form action="<?= $editFormAction; ?>" method="post" id="pre_opt_note_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id=""> Resource Persons </h4>
                </div>
                <div class="" style="padding: 20px">




                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Role </label>
                        <select name="resource_role" class="input-sm chosen-select" style="width:350px;" required>
                            <option selected="selected" value="">Search</option>
                            <option value="rss">Surgeon</option>
                            <option value="ras">Assistant Surgeon</option>
                            <option value="ran">Anaesthetist</option>
                            <option value="rsn">Nurse</option>
                        </select>


                    </div>
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Resource Persons </label>
                        <select name="resource_sn" class="input-sm chosen-select" style="width:350px;" required>
                            <option selected="selected" value="">Search </option>
                            <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'AD' OR rights = 'DR' OR rights = 'NS') AND  status='1' order by count desc");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row["id"]; ?>"><?php echo $row["fullname"]; ?></option>
                            <?php } ?>
                        </select>


                    </div>



                    <br>

                </div>
                <div class="text-right" style="padding: 20px">
                    <input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>">
                    <input type="hidden" name="procedure_sn" value="<?php echo $form_sn; ?>">
                    <button class="btn btn-success" type="submit" name="add_resource_person">Save </button>
                    <button class="btn btn-danger" data-dismiss="modal">Close </button>
                </div>
                <br>
            </form>

            <br>
            <?php
            $check_stmt = $db->prepare("SELECT * FROM procedure_resources where prdure_sn= ? ");
            $check_stmt->execute(array($form_sn));

            if ($check_stmt->rowCount() > 0) {
            ?>
                <div style="padding: 20px;">
                    <table class="table table-bordered" width="100%">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                        <?php
                        // $check_stmt = $db->prepare("SELECT * FROM procedure_resources where prdure_sn= ? ");
                        // $check_stmt->execute(array($form_sn));
                        // if ($check_stmt->rowCount() > 0) {
                        $sn = 1;
                        // $form_resources = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($form_resources as $key => $form_resource) {
                            $rsn = $form_resource["sn"];
                            $prdure_sn = $form_resource["prdure_sn"];
                            $name = $form_resource["name"];
                            $role = $form_resource["role"];

                        ?>
                            <tr>
                                <td><?= $sn++; ?></td>
                                <td><?= $name; ?></td>
                                <td><?= $role; ?> </td>
                                <td>

                                    <form action="<?php echo $editFormAction; ?>" method="POST" onsubmit="return confirm('Please confirm your action to remove <?= $name; ?> as a resource person')">
                                        <input type="hidden" name="hosp" value="<?= $hospital_no; ?>">
                                        <input type="hidden" name="pr" value="<?= base64_encode($prdure_sn); ?>">
                                        <input type="hidden" name="rsn" value="<?= $rsn; ?>">
                                        <input type="submit" name="remove-resource-person-btn" class="btn btn-xs btn-danger " value="Remove">
                                    </form>


                                </td>
                            </tr>
                        <?php
                        }
                        // }

                        ?>
                    </table>
                </div>
            <?php
            }
            ?>

        </div>
    </div>
</div>



<div class="modal inmodal fade" id="pre_opt_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" style="min-height: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""> PRE-OPERATION NOTE</h4>
            </div>
            <div class="modal-body">
                <div class="form_sep">
                    <h3>Select Note Template</h3>
                    <select class="input-sm chosen-select " onChange="load_template()" id="template_id1" style="width:350px;">
                        <option value="blank"> Blank Note </option>
                        <?php foreach ($templates as $key => $template) { ?>
                            <option value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form_sep">
                    <h3> Enter Note Below: </h3>
                    <div name="pre_opt_notes" id="pre_opt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px;">
                        <?php echo $pre_opt_notes; ?>
                    </div>
                </div>


                <div class="modal-footer">
                    <input type="hidden" name="hospital_no" id="hospital_no" value="<?php echo $hospital_no; ?>">
                    <input type="hidden" name="sn" id="sn" value="<?php echo $form_sn; ?>">
                    <input type="hidden" name="sn_code" id="sn_code" value="<?php echo base64_encode(base64_encode($form_sn)); ?>">
                    <button class="btn btn-primary" id="pre_operation_btn" onClick="pre_operation_save()">Save Documentation</button>
                    <button class="btn btn-danger" data-dismiss="modal">Close </button>
                </div>


            </div>
        </div>
    </div>



    <div class="modal inmodal fade" id="post_opt_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-xl" style="min-height: 500px; margin: 0px auto;">
            <div class="modal-content">
                <form action="<?= $editFormAction; ?>" method="post" id="pre_opt_note_form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="">POST-OPERATION NOTE</h4>
                    </div>
                    <div class="" style="padding: 20px;">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form_sep">
                                    <h3> Operation Date/Time </h3>
                                    <p><input type="datetime-local" id="performed_date" min="<?= date('Y-m') . '-01'; ?>T08:30" max="<?= date('Y') + (1); ?>-01-30T16:30" name="performed_date" value="<?= date('Y-m-d') . 'T' . date('H:i'); ?>" class="form-control"></p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form_sep">
                                    <h3> Post - Operation Outcome/Result: </h3>
                                    <select class="input-sm form-control " name="post_op_results" id="post_op_results" style="width:350px; font-size: 15px;" required>
                                        <option value="" selected> Select Outcome </option>
                                        <option value="Successful" <?php echo ($post_op_results == 'Successful' ? 'selected' : ''); ?>>Successful </option>
                                        <option value="Unsuccessful" <?php echo ($post_op_results == 'Unsuccessful' ? 'selected' : ''); ?>>Unsuccessful </option>
                                        <option value="Death" <?php echo ($post_op_results == 'Death' ? 'selected' : ''); ?>>Death </option>
                                        <option value="Cancelled" <?php echo ($post_op_results == 'Cancelled' ? 'selected' : ''); ?>>Cancelled </option>
                                        <option value="Cancelled" <?php echo ($post_op_results == 'Not Applicable' ? 'selected' : ''); ?>>Not Applicable </option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="form_sep">
                            <h3>Select Note Template</h3>
                            <select class="input-sm chosen-select " onChange="load_template2()" id="template_id2" style="width:350px;">
                                <option value="blank"> Blank Note </option>
                                <?php foreach ($templates as $key => $template) { ?>
                                    <option value="<?= $template["id"]; ?>"><?= $template["template_name"]; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form_sep">
                            <h3> Enter Note Below: </h3>
                            <div name="post_opt_notes" id="post_opt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px;">
                                <?php echo $post_opt_notes; ?>
                            </div>
                        </div>


                        <br>
                        <div class="text-right" style="padding: 20px;">
                            <input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>">
                            <input type="hidden" name="sn" value="<?php echo $form_sn; ?>">
                            <input type="hidden" name="sn_code" id="sn_code" value="<?php echo base64_encode(base64_encode($form_sn)); ?>">
                            <button class="btn btn-primary" id="post_operation_btn" onClick="post_operation_save()">Save Documentation</button>
                            <button class="btn btn-danger" data-dismiss="modal">Close </button>
                        </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal inmodal fade" id="updatebookProcedureModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Update Procedure Booking</h4>
                </div>

                <div class="modal-body">

                    <form method="post" id="subject" action="<?php echo $editFormAction; ?>">

                        <br>

                        <div class="form_sep">
                            <label> New Book Date/Time </label>
                            <input type="datetime-local" id="start_date" min="<?= date('Y-m') . '-01'; ?>T08:30" max="<?= date('Y') + (1); ?>-01-30T16:30" name="start_date" value="<?= date('Y-m-d') . 'T' . date('h:i'); ?>" class="form-control">

                        </div>

                        <div class="form_sep">
                            <label for="reg_input_no" class="req">Doctor/Consultant</label>
                            <select name="update_doctor" class="input-sm chosen-select" style="width:350px;" required>
                                <option selected="selected" value="">Search </option>
                                <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'DR') AND  status='1' order by fullname");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["id"] . '__' . $row["fullname"]; ?>"><?php echo $row["fullname"]; ?></option>
                                <?php } ?>
                            </select>
                            <br>
                            <br>
                            <br>
                            <br>

                        </div>


                        <div class="form_sep">

                            <div class="pull-left">
                                <button type="submit" class="btn btn-success btn btn-sm" name="update_book_procedure" id="">Update Request</button>
                            </div>

                            <div class="pull-right">
                                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>

                            </div>
                        </div>

                        <input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>">
                        <input type="hidden" name="sn_procedure_sn" value="<?php echo $form_sn; ?>">
                    </form>

                    <hr>


                </div>
            </div>
        </div>
    </div>







    <script>
        var appointment_number = '<?php echo $appointment_number; ?>';
    </script>