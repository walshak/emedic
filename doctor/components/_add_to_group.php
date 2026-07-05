<div class="panel-group" id="accordion">
    <?php
    if (isset($_POST['category'])) {
        $category = $_POST['category'];
        session_start();
        include("../../Connections/Conn.php");
        include('../objects.php');
    }
    $index = 1;
    $lab_groups = $CustomGroup->get(['created_by' => $_SESSION['id'], 'category' => $category], true);
    foreach ($lab_groups as $key => $lab_group) {

    ?>
        <div class="panel panel-default" style="padding: 5px;">
            <div class="panel-heading">
                <h4 class="panel-title" style="padding:10px">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $category . $index; ?>">
                        [<?= $lab_group->name; ?>]

                        <span style="float:right">

                            <?php
                            if ($category == 'lab') {
                            ?>
                                <span class="text-primary btn btn-primary btn-sm" onclick="openAdd_to_group_modal(<?= $lab_group->id; ?>, 'add_lab_to_group_modal')"><i class="fa fa-plus" style="color:#FFF"> Add to group</i></span>
                            <?php
                            }

                            if ($category == 'rad') {
                            ?>
                                <span class="text-primary btn btn-primary btn-sm" onclick="openAdd_to_group_modal(<?= $lab_group->id; ?>, 'add_rad_to_group_modal')"><i class="fa fa-plus" style="color:#FFF">> Add to group</i></span>
                            <?php
                            }

                            if ($category == 'drug') {
                            ?>
                                <span class="text-primary btn btn-primary btn-sm" onclick="openAdd_medication_to_group_modal(<?= $lab_group->id; ?>, 'add_drug_to_group_modal')"><i class="fa fa-plus" style="color:#FFF"> Add to group</i></span>
                            <?php
                            }
                            ?>


                        </span>
                    </a>
                </h4>
            </div>
            <div id="collapse<?= $category . $index; ?>" class="panel-collapse <?= $index == 1 ? 'collapse' : 'collapse'; ?>">
                <br>
                <div id="">
                    <?php
                    if ($category == 'lab' || $category == 'rad') {
                        $labs_in_the_group = $LabGroup->get(['group_id' => $lab_group->id, 'created_by' => $_SESSION['id']], true);

                        if (count($labs_in_the_group) > 0) {

                    ?>
                            <table class="table table-active" border="1">
                                <thead>
                                    <tr>
                                        <td>SN</td>
                                        <td>TEST</td>
                                        <td>SPECIMEN</td>
                                        <td>NOTE</td>
                                        <td>ACTION</td>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $sn_lab = 1;
                                    foreach ($labs_in_the_group as $key => $lab_in_the_group) {
                                        $lab_info = $Investigation->find($lab_in_the_group->lab_id);
                                    ?>
                                        <tr>
                                            <td><?= $sn_lab++; ?></td>
                                            <td><?= $lab_info->test; ?></td>
                                            <td><?= $lab_in_the_group->specimen; ?></td>
                                            <td><?= $lab_in_the_group->request_note; ?></td>
                                            <td>
                                                <button class="btn btn-danger" onclick="removeFromGroup(<?= $lab_in_the_group->id; ?>, '<?= $category; ?>')"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>

                                </tbody>
                            </table>
                        <?php

                        } else {
                            echo '<p class="text-center">No Lab in the Group</p>';
                        }
                    } else {

                        $medications_in_the_group = $DrugGroup->get(['group_id' => $lab_group->id, 'created_by' => $_SESSION['id']], true);

                        if (count($medications_in_the_group) > 0) {

                        ?>
                            <table class="table table-active" border="1">
                                <thead>
                                    <tr>
                                        <td>SN</td>
                                        <td>NAME</td>
                                        <td>ACTION</td>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $sn_drug = 1;
                                    foreach ($medications_in_the_group as $key => $medication_in_the_group) {
                                        $drug_info = $DrugStock->find($medication_in_the_group->drug_id);
                                    ?>
                                        <tr>
                                            <td><?= $sn_drug++; ?></td>
                                            <td><?= $drug_info->product_name; ?></td>
                                            <td>
                                                <button class="btn btn-danger" onclick="removeFromGroup(<?= $medication_in_the_group->id; ?>, '<?= $category; ?>')"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>

                                </tbody>
                            </table>
                    <?php

                        } else {
                            echo ' <p class="text-center">No Medication in the Group</p> ';
                        }
                    }

                    ?>
                </div>
            </div>

        </div>
    <?php
        $index++;
    }
    ?>

</div>