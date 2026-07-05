<?php

if (isset($_GET["dl"])) {

    $dl = $_GET['dl'];
    $deleteSQL = $db->prepare("DELETE FROM eval_period WHERE id='$dl'");
    $deleteSQL->execute();

    header("location:index.php?evalPeriod");
}

if (isset($_GET["evalPeriod"])) {
    $evalPeriod = $_GET["evalPeriod"];
    if ($evalPeriod != '') {
        $edit_mode = 1;
        $stmt = $db->query("SELECT * FROM eval_period  WHERE id = '$evalPeriod'");
        $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $edit_mode = 0;
    }
}


if (isset($_POST["evalPeriod"])) {
    if ($_POST["edit_mode"] == 0) {
        $stmt = $db->prepare('INSERT INTO eval_period (name) VALUES (:name)');
        $stmt->bindParam(':name', $_POST["name"]);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("location:index.php?evalPeriod");
        }
    } else {
        $stmt = $db->prepare('UPDATE eval_period SET name = :name WHERE id = :id');
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':id', $_POST['sn']);
        $stmt->execute();
    }
    header("location:index.php?evalPeriod");
}
?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Set Evaluation Periods</h5>
            </div>
            <div class="ibox-content">

                <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

                    <div class="form_sep">
                        <label for="" class="req">Name/Label of period</label>
                        <input type="text" id="name" name="name" class="form-control" data-required="true" placeholder="e.g First quater" value="<?php echo $rowx['name']; ?>" required>
                    </div>

                    <div class="form_sep">
                        <div class="pull-left">
                            <button class="btn btn-success" type="submit" name="evalPeriod" id="evalPeriod">Save</button>
                        </div>

                        <div class="pull-right">
                            <a href="index.php?evalPeriod" class="btn btn-warning">Cancel</a>
                        </div>
                    </div>
                    <input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
                    <input type="hidden" name="sn" value="<?php echo $evalPeriod; ?>" />
                </form>

                <hr>

                <?php



                $stmt = $db->query("SELECT * FROM eval_period order by id");
                if ($stmt->rowCount() > 0) { ?>

                    <table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9">
                        <thead>
                            <tr>
                                <th data-toggle="true">S/N</th>
                                <th data-toggle="true">Name</th>
                                <th data-toggle="true">Manage</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $n = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                if ($colordecide % 2 == 0) {
                                    $bgcolor = "#F4F4F4";
                                } else {
                                    $bgcolor = "#FFFFFF";
                                }
                            ?>
                                <tr bgcolor="<?php echo $bgcolor; ?>">
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><a href="index.php?<?php echo 'evalPeriod=' . $row['id']; ?>"> [ Edit ] </a>
                                        &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                                        <a href="index.php?evalPeriod&<?php echo 'dl=' . $row['id']; ?>"> [ Delete ] </a>


                                    </td>
                                </tr>
                            <?php
                                $colordecide++;
                                $n++;
                            } ?>

                        </tbody>

                    </table>
                <?php } else {
                    echo 'No Records Found';
                }

                ?>

            </div>
        </div>
    </div>
</div>