<?php
require_once(__DIR__ . '/../Connections/Conn.php');

if (isset($_GET["dl"])) {
    $dl = intval($_GET['dl']);
    $deleteSQL = $db->prepare("DELETE FROM admission_discharge_checklists WHERE id = ?");
    $deleteSQL->execute([$dl]);

    header("location:index.php?admission_checklists");
    exit;
}

$edit_mode = 0;
$rowx = [
    'id' => '',
    'title' => '',
    'description' => '',
    'type' => 'admission',
    'status' => 1
];

if (isset($_GET["edit_id"])) {
    $edit_id = intval($_GET["edit_id"]);
    if ($edit_id > 0) {
        $stmt = $db->prepare("SELECT * FROM admission_discharge_checklists WHERE id = ?");
        $stmt->execute([$edit_id]);
        if ($stmt->rowCount() > 0) {
            $edit_mode = 1;
            $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

if (isset($_POST["save_checklist"])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
    $post_edit_mode = intval($_POST['edit_mode']);
    $post_id = intval($_POST['sn']);

    if ($post_edit_mode == 0) {
        $stmt = $db->prepare("INSERT INTO admission_discharge_checklists (title, description, type, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $type, $status]);
    } else {
        $stmt = $db->prepare("UPDATE admission_discharge_checklists SET title = ?, description = ?, type = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $description, $type, $status, $post_id]);
    }

    header("location:index.php?admission_checklists");
    exit;
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Admission & Discharge Checklists Setting</h5>
            </div>
            <div class="ibox-content">

                <form action="index.php?admission_checklists" method="POST" id="checklist_form" name="checklist_form">

                    <div class="form_sep">
                        <label for="title" class="req">Checklist Item Title</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Verify Patient Vitals & Identification Band" value="<?php echo htmlspecialchars($rowx['title']); ?>" required>
                    </div>

                    <div class="form_sep m-t-sm">
                        <label for="type" class="req">Checklist Category / Stage</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="admission" <?php echo ($rowx['type'] == 'admission') ? 'selected' : ''; ?>>Admission Checklist</option>
                            <option value="discharge" <?php echo ($rowx['type'] == 'discharge') ? 'selected' : ''; ?>>Discharge Checklist</option>
                        </select>
                    </div>

                    <div class="form_sep m-t-sm">
                        <label for="description">Item Description / Instructions (Optional)</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Provide any detailed instruction for the nurse..."><?php echo htmlspecialchars($rowx['description']); ?></textarea>
                    </div>

                    <div class="form_sep m-t-sm">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1" <?php echo ($rowx['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo ($rowx['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form_sep m-t-md">
                        <div class="pull-left">
                            <button class="btn btn-success" type="submit" name="save_checklist" id="save_checklist">Save Checklist Item</button>
                        </div>
                        <div class="pull-right">
                            <a href="index.php?admission_checklists" class="btn btn-warning">Cancel</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
                    <input type="hidden" name="sn" value="<?php echo $rowx['id']; ?>" />
                </form>

                <hr>

                <?php
                $stmt = $db->query("SELECT * FROM admission_discharge_checklists ORDER BY type ASC, id DESC");
                if ($stmt->rowCount() > 0) { ?>

                    <table id="resp_table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>SI.No</th>
                                <th>Checklist Title</th>
                                <th>Category / Stage</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $n = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <tr>
                                    <td><?php echo $n; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                    <td>
                                        <span class="label <?php echo ($row['type'] == 'admission') ? 'label-info' : 'label-primary'; ?>">
                                            <?php echo ucfirst($row['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td>
                                        <?php if ($row['status'] == 1) { ?>
                                            <span class="badge badge-primary">Active</span>
                                        <?php } else { ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="index.php?admission_checklists&edit_id=<?php echo $row['id']; ?>" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        &nbsp;|&nbsp;
                                        <a href="index.php?admission_checklists&dl=<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this checklist item?')"><i class="fa fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php
                                $n++;
                            } ?>

                        </tbody>
                    </table>
                <?php } else {
                    echo '<div class="alert alert-info">No Checklist Items Found. Create your first checklist item above.</div>';
                } ?>

            </div>
        </div>
    </div>
</div>
