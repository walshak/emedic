<?php
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
                                        <td><input type="date" class="form-control" name="ivf_date" value="<?= $form['ivf_date'] ?>" readonly></td>
                                        <td>Hospital No</td>
                                        <td><input type="text" class="form-control" name="ivf_hosp_no" value="<?= $form['ivf_hosp_no'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Husband Name</td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_husband_name" class="form-control" value="<?= $form['ivf_husband_name'] ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Wife Name</td>
                                        <td colspan="3">
                                            <?php $name_wife = $patient_info->surname . ' ' . $patient_info->fname . ' ' . (($patient_info->oname) ? $patient_info->oname : ""); ?>
                                            <input type="text" name="ivf_wife_name" value="<?php echo $name_wife; ?>" class="form-control" readonly>
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
                                        <td><input type="text" class="form-control" name="ivf_age" value="<?= $wife_age ?>" <?= ($wife_age == "") ? "" : 'readonly'; ?>></td>
                                        <td>Tel</td>
                                        <td><input type="text" class="form-control" name="ivf_tel" value="<?= $patient_info->phone ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Treatment Plan
                                        </td>
                                        <td colspan="3">
                                            <input type="text" class="form-control" value="<?= $form['ivf_treat_plan'] ?>" readonly>
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
                                            <input type="text" class="form-control" value="<?= $form['ivf_treat_plan'] ?>" readonly>
                                        </td>
                                        <td>Start date</td>
                                        <td><input type="date" name="start_date" class="form-control" value="<?= $form['start_date'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>GnRH-a</td>
                                        <td colspan="3"><input type="text" name="ivf_gnrha" class="form-control" value="<?= $form['ivf_gnrha'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Gonadotrophin</td>
                                        <td colspan="3">
                                            <input type="text" class="form-control" value="<?= $form['ivf_treat_plan'] ?>" readonly>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Days of Stimulation</td>
                                        <td colspan="3"><input type="text" name="ivf_days_of_stimulation" class="form-control" value="<?= $form['ivf_days_of_stimulation'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>hGG</td>
                                        <td><input type="text" class="form-control" name="ivf_hcg" value="<?= $form['ivf_hcg'] ?>" readonly></td>
                                        <td>Dose</td>
                                        <td><input type="text" class="form-control" name="ivf_dose" value="<?= $form['ivf_dose'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Date Administered</td>
                                        <td colspan="3"><input type="text" name="ivf_date_administered" class="form-control" value="<?= $form['ivf_date_administered'] ?>" readonly></td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Semen Preparation Details</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Sample Type
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" value="<?= $form['ivf_treat_plan'] ?>" readonly>
                                        </td>
                                        <td>Date of Analysis</td>
                                        <td><input type="date" name="ivf_date_of_analysis" class="form-control" value="<?= $form['ivf_date_of_analysis'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Volume</td>
                                        <td><input type="text" name="ivf_volume" class="form-control" value="<?= $form['ivf_volume'] ?>" readonly></td>
                                        <td>Viscosity</td>
                                        <td><input type="text" name="ivf_vicosity" class="form-control" value="<?= $form['ivf_vicosity'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Conc. Count</td>
                                        <td><input type="text" class="form-control" name="ivf_conc_count" value="<?= $form['ivf_conc_count'] ?>" readonly></td>
                                        <td>Motile Count</td>
                                        <td><input type="text" class="form-control" name="ivf_mortile_count" value="<?= $form['ivf_mortile_count'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Morphology</td>
                                        <td colspan="3"><input type="text" name="ivf_morphology" class="form-control" value="<?= $form['ivf_morphology'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Remarks </td>
                                        <td colspan="3"><input type="text" name="ivf_remarks" class="form-control" value="<?= $form['ivf_remarks'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Egg retrieval and Fertilization</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            No. of Follicles
                                        </td>
                                        <td><input type="date" name="ivf_no_of_folicles" class="form-control" value="<?= $form['ivf_no_of_folicles'] ?>" readonly></td>
                                        <td>Retrieval Date</td>
                                        <td><input type="date" name="ivf_retrival_date" class="form-control" value="<?= $form['ivf_retrival_date'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>No. of Eggs</td>
                                        <td><input type="text" name="ivf_no_of_eggs" class="form-control" value="<?= $form['ivf_no_of_eggs'] ?>" readonly></td>
                                        <td>No. Fertilized</td>
                                        <td><input type="text" name="ivf_no_fertilized" class="form-control" value="<?= $form['ivf_no_fertilized'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Fertilization Method</td>
                                        <td colspan="3">
                                            <input type="text" class="form-control" value="<?= $form['ivf_fertiliztion_method'] ?>" readonly>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>No Cleaved</td>
                                        <td><input type="text" name="ivf_no_cleaved" class="form-control" value="<?= $form['ivf_no_cleaved'] ?>" readonly></td>
                                        <td>No. Frozen</td>
                                        <td><input type="text" name="ivf_no_frozen" class="form-control" value="<?= $form['ivf_no_frozen'] ?>" readonly></td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Embryo Transfer</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            No. Transfered
                                        </td>
                                        <td><input type="text" name="ivf_no_transferd" class="form-control" value="<?= $form['ivf_no_transferd'] ?>" readonly></td>
                                        <td>Transfer Date</td>
                                        <td><input type="date" name="ivf_transfer_date" class="form-control" value="<?= $form['ivf_transfer_date'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Cleaving/Embryo Grade</td>
                                        <td colspan="3"><input type="text" name="ivf_embrayo_grade" class="form-control" value="<?= $form['ivf_embrayo_grade'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Blastocyst</td>
                                        <td><input type="text" name="ivf_blastocyst" class="form-control" value="<?= $form['ivf_blastocyst'] ?>" readonly></td>
                                        <td>No. Frozen</td>
                                        <td><input type="text" name="ivf_blastocyst_no_frozen" class="form-control" value="<?= $form['ivf_blastocyst_no_frozen'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Comment</td>
                                        <td colspan="3"><input type="text" name="ivf_no_cleaved" class="form-control" value="<?= $form['ivf_no_cleaved'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="background-color: gray; color:white">Conclusion</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            After your embryo transfer, the pregnancy test should be done on
                                        </td>
                                        <td>
                                            <input type="date" name="ivf_pregnancy_test_date" class="form-control" value="<?= $form['ivf_pregnancy_test_date'] ?>" readonly>
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
                                            <input type="text" name="ivf_support_drugs" class="form-control" value="<?= $form['ivf_support_drugs'] ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Embryologist
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_embryologist" class="form-control" value="<?= $form['ivf_embryologist'] ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Fertility Specialist
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_fertility_specialist" class="form-control" value="<?= $form['ivf_fertility_specialist'] ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            IVF Nurse
                                        </td>
                                        <td colspan="3">
                                            <input type="text" name="ivf_IVF_nurse" class="form-control" value="<?= $form['ivf_IVF_nurse'] ?>" readonly>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="#" class="btn btn-white" onclick="ClickheretoprintDiv('printable-area')"> <i class="fa fa-print"></i> Print </a>
                        </div>
                    </div>
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
                            <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'DR' OR rights = 'AD' OR rights = 'NS') AND  status='1' order by count desc");
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