<?php

session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");

// CATEGORY NORMALIZER
function normalize_category($category)
{
    if ($category == 'Consultation' || $category == 'Medical Services') {
        return 'consult_med';
    }
    return $category;
}


// MAIN RENDER FUNCTION
function load_categories($db, $category, $post_id, $edit_id = 0)
{

    $category = normalize_category($category);

    // FETCH DATA
    $stmt = $db->prepare("SELECT * FROM prices_table_category WHERE cat_type = :cat ORDER BY sn");
    $stmt->execute(array(':cat' => $category));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // EDIT DATA
    $edit_name = '';
    if ($edit_id > 0) {
        $stmt2 = $db->prepare("SELECT * FROM prices_table_category WHERE sn = :id");
        $stmt2->execute(array(':id' => $edit_id));
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($row2) $edit_name = $row2['Name'];
    }
?>

    <!-- TABLE -->
    <?php if (count($rows) > 0) { ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $n = 1;
                foreach ($rows as $row) { ?>
                    <tr>
                        <td><?php echo $n++; ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td>
                            <a href="#" class="edit_category"
                                data-id="<?php echo $row['sn']; ?>"
                                data-type="<?php echo $category; ?>"
                                data-post="<?php echo $post_id; ?>">Edit</a> |

                            <a href="#" class="delete_category"
                                data-id="<?php echo $row['sn']; ?>"
                                data-type="<?php echo $category; ?>"
                                data-post="<?php echo $post_id; ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <strong>No Grouping Found</strong><br><br>
    <?php } ?>


    <!-- FORM -->
    <form id="category_form">

        <input type="text" name="category_type" value="<?php echo $category; ?>">
        <input type="text" name="post_id" value="<?php echo $post_id; ?>">
        <input type="text" name="edit_id" value="<?php echo $edit_id; ?>">

        <div class="form-group">
            <label><?php echo ucfirst($category); ?> Group</label>
            <input type="text" name="catetory_name" class="form-control"
                value="<?php echo htmlspecialchars($edit_name); ?>" required>
        </div>

        <button type="submit" name="save_category" class="btn btn-primary">
            <?php echo ($edit_id > 0) ? 'Update' : 'Save'; ?>
        </button>

    </form>

<?php
}


// ================= ACTION HANDLERS =================

// LOAD
if (isset($_POST['add_new_category_id'])) {

    $part = explode("__", $_POST['add_new_category_id']);
    load_categories($db, $part[0], $part[1]);
}


// SAVE (ADD / UPDATE)
if (isset($_POST['save_category'])) {

    $name = trim($_POST['catetory_name']);
    $type = $_POST['category_type'];
    $post_id = $_POST['post_id'];
    $edit_id = intval($_POST['edit_id']);

    if ($edit_id > 0) {
        $stmt = $db->prepare("UPDATE prices_table_category SET Name = :name WHERE sn = :id");
        $stmt->execute(array(':name' => $name, ':id' => $edit_id));
    } else {
        $stmt = $db->prepare("INSERT INTO prices_table_category (Name, cat_type) VALUES (:name, :type)");
        $stmt->execute(array(':name' => $name, ':type' => $type));
    }

    load_categories($db, $type, $post_id);
}


// DELETE
if (isset($_POST['delete_id'])) {

    $id = intval($_POST['delete_id']);
    $type = $_POST['category_type'];
    $post_id = $_POST['post_id'];

    $stmt = $db->prepare("DELETE FROM prices_table_category WHERE sn = :id");
    $stmt->execute(array(':id' => $id));

    load_categories($db, $type, $post_id);
}


// EDIT
if (isset($_POST['edit_id']) && !isset($_POST['save_category'])) {

    load_categories(
        $db,
        $_POST['category_type'],
        $_POST['post_id'],
        intval($_POST['edit_id'])
    );
}
?>