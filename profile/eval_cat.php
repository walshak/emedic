<?php

if (isset($_GET["dl"])) {

    $dl = $_GET['dl'];
    $deleteSQL = $db->prepare("DELETE FROM eval_template WHERE id='$dl'");
    $deleteSQL->execute();

    header("location:index.php?evalCat");
}

if (isset($_GET["evalCat"])) {
    $evalCat = $_GET["evalCat"];
    if ($evalCat != '') {
        $edit_mode = 1;
        $stmt = $db->query("SELECT * FROM eval_template INNER JOIN department ON eval_template.dept_id = department.sn WHERE eval_template.id = '$evalCat'");
        $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $edit_mode = 0;
    }
}


if (isset($_POST["evalCat"])) {
    if ($_POST["edit_mode"] == 0) {
        // Insert new evaluation template
        $stmt = $db->prepare("
            INSERT INTO eval_template (dept_id, name, template) 
            VALUES (:dept_id, :name, :template)
        ");
        $result = $stmt->execute([
            ':dept_id' => $_POST["dept_id"],
            ':name' => $_POST["name"],
            ':template' => $_POST["template"]
        ]);

        if ($result) {
            header("Location: index.php?evalCat");
            exit();
        }
    } else {
        // Update existing evaluation template
        $stmt = $db->prepare("
            UPDATE eval_template 
            SET dept_id = :dept_id, name = :name, template = :template 
            WHERE id = :id
        ");
        $stmt->execute([
            ':dept_id' => $_POST["dept_id"],
            ':name' => $_POST["name"],
            ':template' => $_POST["template"],
            ':id' => $_POST["sn"]
        ]);
        header("Location: index.php?evalCat");
        exit();
    }
}
?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Set Evaluation Category/Template</h5>
            </div>
            <div class="ibox-content">


                <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Name/Label of Template</label>
                        <input type="text" id="name" name="name" class="form-control" data-required="true" placeholder="e.g Nurses COD" value="<?php echo $rowx['name']; ?>" required>
                    </div>


                    <div class="form_sep">
                        <label class="req">Type/Design Template</label>
                        <textarea name="template" cols="30" rows="10" class="form-control trumbowygEditor" required><?php echo $rowx['template']; ?></textarea>
                    </div>

                    <div class="form_sep">
                        <label for="reg_select" class="req">Select Department </label>
                        <select name="dept_id" id="" class="form-control" required>
                            <?php if ($rowx['dept_id'] != '') { ?>
                                <option value="<?php echo $rowx['dept_id']; ?>"><?php echo $rowx['department']; ?></option>
                            <?php } else { ?>
                                <option selected="selected" value="">Select ...</option>
                            <?php } ?>

                            <?php
                            $stmt_apr = $db->query("SELECT * FROM department");
                            while ($rwx = $stmt_apr->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $rwx["sn"]; ?>"><?php echo $rwx["department"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep">
                        <div class="pull-left">
                            <button class="btn btn-success" type="submit" name="evalCat" id="evalCat">Save</button>
                        </div>

                        <div class="pull-right">
                            <a href="index.php?evalCat" class="btn btn-warning">Cancel</a>
                        </div>
                    </div>
                    <input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
                    <input type="hidden" name="sn" value="<?php echo $evalCat; ?>" />
                </form>

                <hr>

                <?php



                $stmt = $db->query("SELECT * FROM eval_template order by id");
                if ($stmt->rowCount() > 0) { ?>

                    <table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9">
                        <thead>
                            <tr>
                                <th data-toggle="true">S/N</th>
                                <th data-toggle="true">Name</th>
                                <th data-toggle="true">Deptartment</th>
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
                                    <td>
                                        <?php
                                        $dept_id = $row['dept_id'];
                                        $stmt2 = $db->query("SELECT department FROM department WHERE sn = '$dept_id'");
                                        $dept = $stmt2->fetch(PDO::FETCH_ASSOC);
                                        echo ($dept['department']);
                                        ?>
                                    </td>
                                    <td><a href="index.php?<?php echo 'evalCat=' . $row['id']; ?>"> [ Edit ] </a>
                                        &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                                        <a href="index.php?evalCat&<?php echo 'dl=' . $row['id']; ?>" onclick="return confirm('Are you sure you want to delete?');"> [ Delete ] </a>


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