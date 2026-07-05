<?php

if (isset($_POST['delete_ivf_form_btn'])) {
    $procedure_sn = cleanInput($_POST['procedure_sn']);

    $stmt = $db->prepare("DELETE FROM ivf_form WHERE id = ? ");
    $stmt->execute(array($procedure_sn));

    $error_status = 2;
    $error_msg = 'Success : Form Deleted ';
}


if (isset($_GET['patient'])) {
    $hospital_no = cleanInput($_GET['patient']);
    $sql = "SELECT  * FROM ivf_form WHERE ivf_hosp_no = ? ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$hospital_no]);
} else {
    $sql = "SELECT  * FROM ivf_form ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
}

?>
<table width="100%">
    <tbody>
        <tr>
            <td align="center">
                <h3 style="color:#888">PATIENT'S IVF FORM(s)</h3>
                <?php if (isset($_GET['patient'])) : ?>
                    <p>
                        Patient Name: <?= $patient_info->surname . ' ' . $patient_info->fname . ' ' . (($patient_info->oname) ? $patient_info->oname : "") ?> |
                        Insurance: <?= $patient_info->insurance ?> |
                        Hospital No.: <?= $patient_info->hospital_no ?>
                    </p>
                <?php endif ?>
            </td>
        </tr>
    </tbody>
</table>

<br>
<table class="table table-striped dataTables-example" border="2">
    <thead>
        <?php if (isset($_GET['patient'])) : ?>
            <tr>
                <th>#</th>
                <th>Wife Name</th>
                <th>Husband Name</th>
                <th>Date</th>
                <th>Treatment Program</th>
                <th></th>
            </tr>
        <?php else : ?>
            <tr>
                <th>#</th>
                <th>Wife Name</th>
                <th>Insurance</th>
                <th>Husband Name</th>
             
                <th>Date</th>
                <th>Treatment Program</th>
                <th></th>
            </tr>
        <?php endif ?>
    </thead>
    <tbody>
        <?php
        $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sn = 1;
        if (isset($_GET['patient'])) {
            foreach ($forms as $key => $ivf_form) { ?>
                <tr>
                    <td>
                        <?= $sn ?>
                    </td>
                    <td>
                        <?= $ivf_form['ivf_wife_name'] ?>
                    </td>
                    <td>
                        <?= $ivf_form['ivf_husband_name'] ?>
                    </td>
                    <td>
                        <?= date('d-M-Y', strtotime($ivf_form['ivf_date'])) ?>
                    </td>
                    <td>
                        <?= $ivf_form['ivf_treat_plan'] ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?ivf_form&pr=<?= base64_encode(base64_encode($ivf_form["id"])); ?>" class="btn btn-xs btn-success'); ?>">&nbsp;View&nbsp;</a>
                    </td>
                </tr>
            <?php
            }
        } else {
            foreach ($forms as $key => $ivf_form) { 
                $p_i = $Patient->getByHospitalNo($ivf_form['ivf_hosp_no']);
                ?>
                <tr>
                    <td>
                        <?= $sn ?>
                    </td>
                    <td>
                        <?= $ivf_form['ivf_wife_name'].' ('.$ivf_form['ivf_hosp_no'].')' ?>
                    </td>
                   <td> <?= $p_i->insurance_type ?></td>
                    <td>
                        <?= $ivf_form['ivf_husband_name'] ?>
                    </td>
                    <td>
                        <?= date('d-M-Y', strtotime($ivf_form['ivf_date'])) ?>
                    </td>
                    <td>
                        <?= $ivf_form['ivf_treat_plan'] ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?ivf_form&pr=<?= base64_encode(base64_encode($ivf_form["id"])); ?>" class="btn btn-xs btn-primary">&nbsp;View&nbsp;</a>
                    </td>
                </tr>
        <?php
            }
        }
        ?>

    </tbody>
</table>
<script></script>