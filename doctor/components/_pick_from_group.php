<div class="panel-group " id="accordion">
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
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $index; ?>">
                        [<?= $lab_group->name; ?>]

                        <span style="float:right">
                            <?php
                            if ($category == 'lab' || $category == 'rad') {
                            ?>
                                <span class="text-primary btn btn-primary btn-sm" onclick="pickLabFromGroup(<?= $lab_group->id; ?>, '<?= $category; ?>')"><i class="fa fa-plus" style="color:#FFF"> Pick Group</i></span>
                            <?php
                            } else {
                            ?>
                                <span class="text-primary btn btn-primary btn-sm" onclick="pickDrugFromGroup(<?= $lab_group->id; ?>, '<?= $category; ?>')"><i class="fa fa-plus" style="color:#FFF"> Pick Group</i></span>
                            <?php
                            }
                            ?>

                        </span>
                    </a>
                </h4>
            </div>
            <div id="collapse<?= $index; ?>" class="panel-collapse <?= $index == 1 ? '' : 'collapse'; ?>">

            </div>

        </div>
    <?php
        $index++;
    }
    ?>
</div>