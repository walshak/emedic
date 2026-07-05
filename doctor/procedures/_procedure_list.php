<?php
if (isset($_POST['delete_procedure_btn'])) {
    $procedure_sn = cleanInput($_POST['procedure_sn']);
    $payment_sn = cleanInput($_POST['payment_sn']);

    try {
        // Begin transaction
        $db->beginTransaction();

        // Delete from patient_ap_services first
        $stmt = $db->prepare("DELETE FROM patient_ap_services WHERE sn = ? AND paystatus = 0 AND cr = 0");
        $stmt->execute(array($payment_sn));

        if ($stmt->rowCount() > 0) {
            // Delete from procedures table if first deletion succeeds
            $stmt2 = $db->prepare("DELETE FROM procedures WHERE sale_no = ?");
            $stmt2->execute(array($payment_sn));

            // Commit both deletions
            $db->commit();

            $error_status = 2;
            $error_msg = 'Success: Both records deleted.';
        } else {
            // Nothing to delete — rollback
            $db->rollBack();

            $error_status = 1;
            $error_msg = 'Error: No records found to delete in patient_ap_services.';
        }
    } catch (Exception $e) {
        // Rollback on any exception
        $db->rollBack();

        $error_status = 0;
        $error_msg = 'Transaction failed: ' . $e->getMessage();
    }
}

?>

<?php
$sn = 1;
$is_doctor = false;
$is_specialist = false;
$year_month = date('Y-m');

/*
if ($_SESSION['rights'] == 'DR') {
    $is_doctor = true;

    if (isset($_POST['show_pending'])) {

        $add_on = $_POST['Pending_Documentation'];
        $stmt = $db->prepare("SELECT * FROM procedures WHERE consultant_id = ? $add_on ORDER BY sn DESC ");
        $stmt->execute([$_SESSION['id']]);
    } else {

        $stmt = $db->prepare("SELECT * FROM procedures WHERE (created_by = ? OR consultant_id = ? ) and post_opt_notes_id is NUll ORDER BY sn DESC ");
        $stmt->execute([$_SESSION['id'], $_SESSION['id']]);
    }
} else {

    */

///}

if ($_SESSION['dispensory'] == 1) {
    $dept_id = $_SESSION['dept_id'];
    $_sort_by_dept_id = "dept_id='$dept_id' AND ";
} else {
    $_sort_by_dept_id = '';
}

if (isset($_POST['show_pending'])) {

    $add_on = $_POST['Pending_Documentation'];
    $stmt = $db->prepare("SELECT * FROM procedures WHERE $_sort_by_dept_id consultant_id = ? $add_on ORDER BY sn DESC ");
    $stmt->execute([$_SESSION['id']]);
} elseif (isset($_POST['get_query'])) {

    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $selected_value = $_POST['dropdown'];

    $sql = "SELECT * FROM procedures WHERE $_sort_by_dept_id date(date_entry) BETWEEN :start_date AND :end_date";
    $params = [
        ':start_date' => $start_date,
        ':end_date' => $end_date,
    ];

    // If a dropdown value is selected, add it to the query
    if (!empty($selected_value)) {
        $sql .= " AND (procedures = :selected_value 
        OR consultant_name = :selected_value_name 
        OR theater = :selected_value_theater 
        OR procedure_time = :selected_value_time)";
        $params[':selected_value'] = $selected_value;
        $params[':selected_value_name'] = $selected_value;
        $params[':selected_value_theater'] = $selected_value;
        $params[':selected_value_time'] = $selected_value;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
} elseif (isset($_GET['patient'])) {
    $hospital_no = cleanInput($_GET['patient']);
    $sql = "SELECT  * FROM procedures WHERE $_sort_by_dept_id hospital_no = ? ORDER BY sn DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$hospital_no]);
?>
    <table width="100%">
        <tbody>
            <tr>
                <td align="center">
                    <h3 style="color:darkblue">THIS PATIENT'S ALL PROCEDURE(s)</h3>
                </td>
            </tr>
        </tbody>
    </table>


<?php } else {
    $date_limt = date('Y-m-d', strtotime('-2 weeks'));
    $stmt = $db->prepare("SELECT * FROM procedures WHERE $_sort_by_dept_id DATE(date_entry) >= '$date_limt' and post_opt_notes_id is NUll ORDER BY sn DESC LIMIT 20");
    $stmt->execute();

?>
    <table width="100%">
        <tbody>
            <tr>
                <td align="center">
                    <h3 style="color:black"> RECENT PROCEDURE(s) REQUEST</h3>
                </td>
            </tr>
        </tbody>
    </table>
<?php }

?>


<h3 style="color: brown;;">Filter Procedures Data Below: </h3>

<!DOCTYPE html>
<html lang="en">



<body>

    <form method="POST" action="" style="background-color: skyblue; padding-left: 10px;">
        &nbsp;
        <table style="padding: 2px;">
            <tr>




                <td>
                    <label for="start_date">From Date:</label>
                    <input class="form-control" type="date" id="start_date" name="start_date"
                        value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>" required>
                </td>

                <td>
                    <label for="end_date">To Date:</label>
                    <input class="form-control" type="date" id="end_date" name="end_date"
                        value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>" required>
                </td>

                <td>
                    <label for="dropdown">Select Procedure, Consultant, Theater, or Time:</label>
                    <select class="form-control" id="dropdown" name="dropdown">
                        <option value="">-- Select --</option>
                        <?php
                        try {
                            $query = "SELECT DISTINCT procedures AS value FROM procedures UNION 
                            SELECT DISTINCT consultant_name FROM procedures UNION 
                            SELECT DISTINCT theater FROM procedures UNION 
                            SELECT DISTINCT procedure_time FROM procedures";

                            $stmt3 = $db->query($query);

                            // Check if we have results and populate the dropdown
                            if ($stmt3->rowCount() > 0) {
                                while ($row = $stmt3->fetch()) {
                                    if ($row['value'] != '') {
                                        echo "<option value='" . htmlspecialchars($row['value']) . "'>" . htmlspecialchars($row['value']) . "</option>";
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            // Handle connection error
                            die("Connection failed: " . $e->getMessage());
                        }

                        // Close the connection (optional, as it will close automatically when the script ends)
                        $conn = null;
                        ?>

                    </select>


                </td>
                <td width="15%"><label for="end_date">:</label><br>
                    <input type="submit" name="get_query" value="Search" class="btn btn-danger btn-sm">
                </td>

                <td width="15%"><label for="end_date">:</label><br>
                    <a href="index.php?procedure" class="btn btn-success btn-sm"> <i class="fa fa-home"></i>&nbsp;Refresh </a>
                </td>
            </tr>
        </table>
        <br>
    </form>

</body>

</html>



<br>
<table class="table table-striped dataTables-example" border="2">
    <thead>
        <tr>
            <th>#</th>
            <?php if (!isset($_GET['patient'])) { ?>
                <th>Name</th>
                <th>Hospital No</th>
                <th>Insurance</th>
            <?php } ?>
            <th>Procedure Type</th>
            <th>Consultant</th>
            <th>Details</th>
            <th>Payment/Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php

        $Pending_Documentation = '';
        $pending_doc_count = 0;
        $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($procedures as $key => $procedure) {
            $app_no = $procedure['app_no'];
            $hospital_no = $procedure['hospital_no'];
            $procedure_sn = $procedure['sn'];
            $item_services = $procedure['procedures'];
            $date_entry = $procedure['date_entry'];
            $service_id = $procedure['service_id'];
            $post_opt_notes_id = $procedure['post_opt_notes_id'];
            $cost = $procedure['cost'];
            $sale_no = $procedure['sale_no'];

            $stmt = $db->prepare("SELECT sn,paystatus,pay_mode FROM patient_ap_services WHERE sn = ?");
            $stmt->execute(array($sale_no));
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                $payment_sn = $row['sn'];
                $pay_mode = $row['pay_mode'];

                if ($row['paystatus'] == 1 and $post_opt_notes_id == '') {
                    $pay_status = 'Paid <br> <strong>[Not Performed]</strong>';

                    if ($procedure['consultant_id'] == $_SESSION['id']) {
                        /// echo $payment_sn;
                        $Pending_Documentation = $Pending_Documentation . "sn='"  . $procedure_sn . "'" . ' or ';
                        $pending_doc_count = $pending_doc_count + 1;
                    }
                } elseif ($row['paystatus'] == 1 and $post_opt_notes_id != '') {
                    $pay_status = 'Paid <br> <strong>[Performed]</strong>';
                } else {
                    $pay_status = 'Pending';
                }
        ?>
                <tr>
                    <td><?= $sn++; ?></td>

                    <?php if (!isset($_GET['patient'])) { ?>
                        <td><?= $procedure["name"]; ?></td>
                        <td><?= $procedure["hospital_no"]; ?></td>
                        <td><?= $procedure["insurance_type"]; ?></td>
                    <?php } ?>

                    <td><?= $procedure["procedures"]; ?>
                        <?php
                        if ($pay_status == 'Pending' && ($procedure['consultant_id'] == $_SESSION['id'] || $_SESSION['fullname'] == $procedure['prepared_by'])) {

                        ?>
                            <form action="<?= $editFormAction; ?>" method="post" onsubmit="return confirm('Let\'s get your confirmation to delete this request. Proceed?')">
                                <input type="hidden" name="procedure_sn" value="<?= $procedure_sn; ?>">
                                <input type="hidden" name="payment_sn" value="<?= $payment_sn; ?>">
                                <button type="submit" class="btn btn-xs btn-danger" name="delete_procedure_btn">Delete</button>
                            </form>
                        <?php
                        }
                        ?>

                        <?php if (!empty($procedure['old_procedure_id']) && !empty($procedure['old_procedure_name'])): ?>
                            <hr>
                            <p>
                                <b> This is a follow-up from a previous procedure :</b> <br><br>
                                <span><?php echo $procedure['old_procedure_name'] ?> &nbsp; <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["old_procedure_id"])); ?>" target="_blank">[View This Prev. Procedure]</a></span>
                            </p>
                        <?php endif ?>
                        <?php
                        $fu = $db->prepare("SELECT * FROM procedures where old_procedure_id = ? ");
                        $fu->execute([$procedure['sn']]);
                        $fu = $fu->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (count($fu) > 0): ?>
                            <hr>

                            <h4>This Procedure has the following follow-up procedures :</h4>
                            <?php foreach ($fu as $f): ?>
                                <span><?php echo $f['procedures'] ?> &nbsp; <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($f["sn"])); ?>" target="_blank">[View This Follow-up Procedure]</a></span>

                            <?php endforeach ?>

                        <?php endif ?>
                    </td>
                    <td><?= $procedure["consultant_name"]; ?></td>
                    <td>
                        <b>Booked By:</b> <?= $procedure["prepared_by"]; ?>
                        <br><b>Date Time:</b> <?= date('d M, Y H:i A', strtotime('' . $procedure["date_entry"] . '')); ?>
                        <br><b>Required Theater Use:</b> <strong><i>[ <?= $procedure["require_theater"]; ?> ]</i></strong>
                    </td>
                    <td><?= $pay_status; ?></td>
                    <td class="text-center">
                        <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-<?= (empty($post_opt_notes_id) ? 'success' : 'primary'); ?>">&nbsp;View&nbsp;</a>
                    </td>
                </tr>
            <?php
            } else { ?>










                <tr>
                    <td><?= $sn++; ?></td>

                    <?php if (!isset($_GET['patient'])) { ?>
                        <td><?= $procedure["name"]; ?></td>
                        <td><?= $procedure["hospital_no"]; ?></td>
                        <td><?= $procedure["insurance_type"]; ?></td>
                    <?php } ?>

                    <td><?= $procedure["procedures"]; ?>

                        <?php if (!empty($procedure['old_procedure_id']) && !empty($procedure['old_procedure_name'])): ?>
                            <hr>
                            <p>
                                <b> This is a follow-up from a previous procedure :</b> <br><br>
                                <span><?php echo $procedure['old_procedure_name'] ?> &nbsp; <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["old_procedure_id"])); ?>" target="_blank">[View This Prev. Procedure]</a></span>
                            </p>
                        <?php endif ?>
                        <?php
                        $fu = $db->prepare("SELECT * FROM procedures where old_procedure_id = ? ");
                        $fu->execute([$procedure['sn']]);
                        $fu = $fu->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (count($fu) > 0): ?>
                            <hr>

                            <h4>This Procedure has the following follow-up procedures :</h4>
                            <?php foreach ($fu as $f): ?>
                                <span><?php echo $f['procedures'] ?> &nbsp; <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($f["sn"])); ?>" target="_blank">[View This Follow-up Procedure]</a></span>

                            <?php endforeach ?>

                        <?php endif ?>
                    </td>
                    <td><?= $procedure["consultant_name"]; ?></td>
                    <td><?= number_format($cost); ?></td>
                    <td>
                        <b>Booked By:</b> <?= $procedure["prepared_by"]; ?>
                        <br><b>Date Time:</b> <?= date('d M, Y H:i A', strtotime('' . $procedure["date_entry"] . '')); ?>
                        <br><b>Required Theater Use:</b> <strong><i>[ <?= $procedure["require_theater"]; ?> ]</i></strong>
                    </td>
                    <td><?= $pay_status; ?></td>
                    <td class="text-center">
                        <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-<?= (empty($post_opt_notes_id) ? 'success' : 'primary'); ?>">&nbsp;View&nbsp;</a>
                    </td>
                </tr>

















        <?php    }
        }
        ?>
    </tbody>
</table>

<?php
if ($pending_doc_count > 0) {
    $query = substr($Pending_Documentation, 0, -3);
?>
    <h3 style="color: brown; ">You have <?= $pending_doc_count; ?> pending Documentation(s) to complete</h3>
    <form action="<?= $editFormAction; ?>" method="post">
        <input type="hidden" name="Pending_Documentation" value="<?php echo  ' and (' . $query . ')'; ?>">
        <button type="submit" class="btn btn-xs btn-danger" name="show_pending">Show Me</button>
        &nbsp;&nbsp;<a href="" class="btn btn-xs btn-default">Refresh</a>
    </form>
<?php } ?>

<script></script>