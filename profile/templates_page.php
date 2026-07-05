<?php
$temp_list = [];
if (isset($_POST["saveTemplateBtn"])) {
    $template_name = $_POST["template_name"];
    $category = $_POST["service_cat"];
    $department_id = $_POST["department_id"];
    $template = $_POST["template"];
    $now_setdate = date('Y-m-d H:i:s');

    $error_status = 1;
    $error_msg = 'Error : Template is not saved';

    $stmt = $db->prepare("SELECT t.*, s.cat_type FROM services_templates t 
    INNER JOIN serv_cat s ON t.category = s.cat_type WHERE t.template_name = ? AND t.status = '1'");
    $stmt->execute(array($template_name));

    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO services_templates (template_name, template,category, created_by, status, created_at, department_id) VALUES (?, ?,?, ?, '1',?, ?) ");
        $create_template = $stmt->execute(array($template_name, $template, $category, $_SESSION["id"], $now_setdate, $department_id));
        if ($create_template) {
            $error_status = 2;
            $error_msg = 'Success : Template is saved';
        }
    } else {
        $error_msg = 'Error : Template name already exist';
    }
}

if (isset($_POST["updateTemplateBtn"])) {
    $error_status = 1;
    $error_msg = 'Error : Template is not saved';
    $template_name = $_POST["template_name"];
    $category = $_POST["service_cat"];
    $department_id = $_POST["department_id"];


    $template = $_POST["template"];
    $id = $_POST["id"];

    $stmt = $db->prepare("UPDATE services_templates SET template_name = ? , template = ?, category = ?, modified_at = now(), department_id=? WHERE id = ?");
    $update = $stmt->execute(array($template_name, $template, $category, $department_id, $id));
    if ($update) {
        $error_status = 2;
        $error_msg = 'Success : Template is updated';
    }
}

if (isset($_POST["deleteTempBtn"])) {
    $error_status = 1;
    $error_msg = 'Error : Template is not deleted';
    $id = $_POST["id"];

    $stmt = $db->prepare("UPDATE services_templates SET status = '0', modified_at = now() WHERE id = ? AND status = '1' ");
    $update = $stmt->execute(array($id));
    if ($update) {
        $error_status = 2;
        $error_msg = 'Success : Template is deleted';
    }
}
?>

<div class="ibox float-e-margins">
    <div class="ibox-title">
        <span class="btn btn-success btn-xs pull-right" data-toggle="modal" data-target="#newTemplateModal" style="font-size: 15px;">&nbsp;New Template&nbsp;</span>
        <h5>Manage Templates</h5>
    </div>
    <div class="ibox-content">


        <form action="./templates.php" method="POST" name="subject" enctype="multipart/form-data">
            <!-- Service Category Dropdown -->
            <div class="form-sep">
                <label for="service_cat" class="req" style="font-size:14px">Select Department Category</label>
                <select name="service_cat" id="service_cat" class="form-control" style="font-size: 15px;" required>
                    <option value="">Select Service Category</option>
                    <?php
                    $stmt = $db->prepare("SELECT cat_type FROM serv_cat ORDER BY cat_type");
                    $stmt->execute();
                    $service_cat_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($service_cat_list as $service_cat):
                        $selected = (isset($_POST['service_cat']) && $_POST['service_cat'] == $service_cat['cat_type']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($service_cat['cat_type']); ?>" <?= $selected ?>>
                            <?= htmlspecialchars($service_cat['cat_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <hr>

            <!-- Department Dropdown -->
            <?php if ($isAdmin): ?>
                <div class="form-sep">
                    <label for="reg_select" class="req" style="font-size:14px">Select Department</label>
                    <select name="department_pick" id="reg_select" class="form-control" style="font-size: 15px;">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $department):
                            $selected = (isset($_POST['department_pick']) && $_POST['department_pick'] == $department['sn']) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($department['sn']); ?>" <?= $selected ?>>
                                <?= htmlspecialchars($department['department']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr>
            <?php else: ?>
                <input type="hidden" name="department_pick" value="<?= htmlspecialchars($_SESSION['dept_id']); ?>">
            <?php endif; ?>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-6 text-right">
                    <button type="submit" class="btn btn-primary" name="show_templates">Show</button>
                </div>
            </div>
        </form>




        <?php if (isset($_POST["updateTemplateBtn"])  or isset($_POST["deleteTempBtn"]) or isset($_POST["saveTemplateBtn"])) { ?>
            <?php if ($error_status == 2) { ?>
                <h1 style="color: blue;"><?php echo $error_msg; ?></h1>
            <?php } else { ?>
                <h1 style="color: red;"><?php echo $error_msg; ?></h1>
        <?php }
        }
        ?>


        <?php if (isset($_POST['show_templates'])) {
            $service_cat = $_POST['service_cat'];

            if (isset($_POST['department_pick']) && $_POST['department_pick'] != '') {
                $department_pick = $_POST['department_pick'];
                $query_contraints2 = " AND t.department_id = $department_pick ";
            }

            // echo '========' . $isAdmin;
            //echo 'd========' . $user_dept;

            // echo $dept_id = $_SESSION['dept_id'];
            // $query_contraints = $isAdmin ? "" : " AND t.department_id = $user_dept ";

            // echo $query_contraints;
        ?>
            <br>
            <table class='table table-striped table-bordered table-hover dataTables-example'>
                <thead>
                    <tr>
                        <th width='2%'>No</th>
                        <th>Template Name</th>
                        <th>Service Category</th>

                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $sn = 1;
                    $stmt = $db->prepare("SELECT t.template_name, t.created_at, t.id, t.category 
                    FROM services_templates t WHERE t.status = '1' and t.category='$service_cat'
                     $query_contraints2 ORDER BY template_name, category");
                    $stmt->execute();

                    while ($template = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <tr>
                            <th width='2%'><?= $sn++; ?></th>
                            <th><?= $template["template_name"]; ?></th>
                            <th><?= $template["category"]; ?></th>
                            <th class="text-center">
                                <a href="./templates.php?template=<?= $template['id'] . '&view'; ?>" class="btn btn-sm btn-primary">View </a>
                                <a href="./templates.php?template=<?= $template['id'] . '&edit'; ?>" class="btn btn-sm btn-info">Edit </a>

                                <form action="./templates.php" method="post" onsubmit="return confirm('Please confirm to proceed...') ? true: false" style="display:inline"> <input type="hidden" name="id" value="<?= $template["id"]; ?>"> <button type="submit" class="btn btn-sm btn-danger" name="deleteTempBtn">Delete </button></form>

                            </th>
                        </tr>
                    <?php
                    }
                    ?>

                </tbody>
            </table>

        <?php } ?>
    </div>
</div>


<div class="modal inmodal fade" id="newTemplateModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">New Template </h4>
            </div>
            <div class="modal-body" style="min-height: 300px;">
                <form action="./templates.php" method="POST" name="subject" enctype="multipart/form-data">
                    <div class="form-sep">
                        <label for="reg_input_no" class="req">Template Name: </label>
                        <input type="text" maxlength="100" name="template_name" id="template_name" class="form-control" placeholder="Template Name" style="font-size: 15px;" required>
                    </div>
                    <br>
                    <div class="form-sep">
                        <label for="reg_input_no" class="req">Category: </label>
                        <select name="service_cat" id="service_cat" class="form-control" style="font-size: 15px;" required>
                            <option value="">Select Service Category</option>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM serv_cat order by cat_type");
                            $stmt->execute();
                            $service_cat_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($service_cat_list as $key => $service_cat) {
                            ?> <option value="<?= $service_cat["cat_type"]; ?>"><?= $service_cat["cat_type"]; ?></option> <?php
                                                                                                                        }
                                                                                                                            ?>
                        </select>
                    </div>

                    <br>
                    <div class="form-sep">
                        <label for="reg_input_no" class="req">Department: </label>
                        <select name="department_id" id="department_id" class="form-control" style="font-size: 15px;" required>
                            <option value="">Select Department</option>
                            <?php
                            foreach ($departments as $key => $department) {
                            ?> <option value="<?= $department["sn"]; ?>"><?= $department["department"]; ?></option> <?php
                                                                                                                }
                                                                                                                    ?>
                        </select>
                    </div>

                    <br>
                    <div class="form-sep">

                        <label for="reg_input_no" class="req">Template [formated text, table, html code] </label>
                        <textarea class="form-control trumbowygEditor" name="template" id="template" cols="30" rows="25"></textarea>
                    </div>
                    <br>

                    <div>

                        <div class="row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary" name="saveTemplateBtn">Save</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--------------- TEMPLATE EDIT AND VIEW MODALS ------------------------>
<?php
if (isset($_GET['template'])) {
    $template_id = intval($_GET['template']);
    $stmt = $db->prepare("SELECT t.* FROM services_templates t WHERE  t.id=?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    $action_type =   isset($_GET['view']) ? 'view' : null;
    $action_type =   isset($_GET['edit']) ? 'edit' : $action_type;

?>
    <!---- EDIT MODAL -->
    <div class="modal inmodal fade" id="editTemplateModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Update Template </h4>
                </div>
                <div class="modal-body" style="min-height: 300px;">
                    <form action="./templates.php" method="POST" name="subject" enctype="multipart/form-data">
                        <div class="form-sep">
                            <label for="reg_input_no" class="req">Template Name: </label>
                            <input type="text" maxlength="100" name="template_name" id="template_name" value="<?= $template["template_name"]; ?>" class="form-control" placeholder="Template Name" required style="font-size: 15px;">
                        </div>
                        <br>
                        <div class="form-sep">
                            <label for="reg_input_no" class="req">Service Category: </label>
                            <select name="service_cat" id="service_cat" class="form-control" style="font-size: 15px;" required>
                                <option value="">Select Service Category</option>
                                <?php

                                foreach ($service_cat_list as $key => $service_cat) {
                                ?> <option value="<?= $service_cat["cat_type"]; ?>" <?= ($template["category"] == $service_cat["cat_type"] ? "selected" : ""); ?>><?= $service_cat["cat_type"]; ?></option> <?php
                                                                                                                                                                                                        }
                                                                                                                                                                                                            ?>
                            </select>
                        </div>

                        <br>
                        <div class="form-sep">
                            <label for="reg_input_no" class="req">Department: </label>
                            <select name="department_id" id="department_id" class="form-control" style="font-size: 15px;" required>
                                <option value="">Select Department</option>
                                <?php
                                foreach ($departments as $key => $department) {
                                ?> <option value="<?= $department["sn"]; ?>" <?= ($template["department_id"] == $department["sn"] ? 'selected' : ''); ?>><?= $department["department"]; ?></option> <?php
                                                                                                                                                                                                }
                                                                                                                                                                                                    ?>
                            </select>
                        </div>

                        <br>
                        <div class="form-sep">
                            <div style="max-height:400px;overflow:auto">
                                <label for="reg_input_no" class="req">Template [formated text, table, html code] </label>
                                <textarea class="form-control trumbowygEditor" name="template" id="template" cols="30" rows="10" required><?= $template["template"]; ?></textarea>
                            </div>
                            <br>

                            <div>
                                <div>

                                    <div class="row">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6 text-right">
                                            <input type="hidden" name="id" value="<?= $template["id"]; ?>">
                                            <button type="submit" class="btn btn-primary" name="updateTemplateBtn">Save</button>
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-------- View Modal--------->

    <div class="modal inmodal fade" id="viewTemplateModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">New Template </h4>
                </div>
                <div class="modal-body" style="min-height: 300px;">
                    <div><?= $template["template"]; ?></div>
                </div>
                <div class="modal-footer">


                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
    <script src="../js/jquery-3.1.1.min.js"></script>

    <script src="../js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {

            setTimeout(() => {
                $('#<?= $action_type; ?>TemplateModal').modal('show');
            }, 300);
        })
    </script>
<?php
}
?>