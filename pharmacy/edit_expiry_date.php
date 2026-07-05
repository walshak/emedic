<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
if (isset($_POST["stock_snn"])) {
    $stmt = $db->prepare('UPDATE stock_table SET expire_date = :expire_date WHERE sn = :sn');

    // Execute the statement
    $success = $stmt->execute([
        ':expire_date' => $_POST['expired'],
        ':sn' => $_POST['stock_snn']
    ]);

    // Check if the update was successful
    if ($success) {
        echo "Record updated successfully!. You can Refresh if you wish to see changes!";
    } else {
        echo "Failed to update the record. Please try again.";
    }

    exit;
} elseif (isset($_POST["exp_date"])) {
    $exp_date = $_POST["exp_date"];


    $stmt = $db->prepare("SELECT product_name,expire_date FROM stock_table WHERE sn = :stock_sn");
    $stmt->bindParam(':stock_sn', $exp_date, PDO::PARAM_STR);
    $stmt->execute();
    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="alert alert-info">

    <h3 style="color: red;;">EDIT: Expiring Date</h3>
    <h3 style="color: black;;"><?= $row_d['product_name']; ?></h3>


    <div class="form_sep" id="">
        <label for="reg_input_no">Expirying Date</label>
        <div class="input-group date">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="date" name="expired_edit_2" id="expired_edit_2" class="form-control" value="<?= $row_d['expire_date']; ?>">
        </div>
    </div>

    <input type="hidden" name="stock_snn" id="stock_snn" value="<?= $exp_date; ?>">

    <div class="form_sep">
        <div class="pull-left">
            <a href="#" class="btn btn-primary" onclick="save_edit_product_stock('<?php echo $exp_date; ?>')">Save</a>

        </div>

        <div class="pull-right">
            <button class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>

</div>