<?php
include("../Connections/Conn.php");
session_start();

if (isset($_POST['disable_manage'])) {
    $disable_manage = $_POST['disable_manage'];
    $update = $db->prepare("UPDATE manage_patients_vip_staff SET status = 0 WHERE sn = :disable_manage");
    $update->bindParam(':disable_manage', $disable_manage, PDO::PARAM_INT); // Assuming sn is an integer
    $update->execute();

    // Check if the update was successful
    if ($update->rowCount() > 0) {
        echo "Update successful: Close and Open to see updates.";
    } else {
        echo "No records updated. Please check if the sn exists or if it was already disabled.";
    }
    exit;
}

if (isset($_POST['able_manage'])) {
    $able_manage = $_POST['able_manage'];
    $update = $db->prepare("UPDATE manage_patients_vip_staff SET status = 1 WHERE sn = :able_manage");
    $update->bindParam(':able_manage', $able_manage, PDO::PARAM_INT); // Assuming sn is an integer
    $update->execute();

    // Check if the update was successful
    if ($update->rowCount() > 0) {
        echo "Update successful: Close and Open to see updates.";
    } else {
        echo "No records updated. Please check if the sn exists or if it was already disabled.";
    }
    exit;
}


if (isset($_POST['make_vip'])) {


    $vip = $_POST["vip"];
    $hospital_no_manage = $_POST["hospital_no_manage"];
    $doctor_id = $_SESSION["id"];
    $manage_status = $_POST["manage_status"];
    $patient_manage_hx = $_POST["patient_manage_hx"];
    $make_vip = $_POST["make_vip"];
    $manage_hx = $_POST["manage_hx"];
    $patient_manage_by = $_POST["patient_manage_by"];
?>


    <form action="patient.php?hosp_no=<?= $hospital_no_manage; ?>&mgt" method="post">

        <h4 class="text-center">Patient Management</h4>
        <?php $make_vip = 0;
        if ($_SESSION['rights'] != 'MD' && ($manage_status == '2' or $manage_status == '1')) {

            if ($patient_manage_by == $doctor_id) { ?>
                <h2>Do you want to stop patient management?</h2>
                <input type="hidden" name="manage_as_type" id="" value="Cancel" />
            <?php } else { ?>
                <h2>Are you sure you want to Change this Patient Management?</h2>
            <?php }   ?>



        <?php } elseif ($_SESSION['rights'] == 'MD') {
            $make_vip = 1; ?>
            <h3> <?php if ($vip == 1) {
                        echo '<b>VIP Patient</b><br>';
                    } ?> : Select how you want to manage this patient?</h3>

            <div class="form-group">
                <label>Manage Patient As</label>
                <select name="manage_as_type" class="form-control" style="font-size:15px" id="serviceTemplate" required>
                    <option value=""> -- Select -- </option>
                    <?php if ($manage_status > 0) { ?> <option value="Cancel">Cancel</option> <?php } ?>
                    <option value="Manage" <?php if ($vip == 0 && $manage_status == 2) {
                                                echo 'selected';
                                            } ?>>Manage As Other Doctor</option>
                    <option value="VIP">VIP</option>
                </select>
            </div>

            <div class="form_sep" id="staffSelection" style="display: none;">
                <label for="reg_input_no" class="">Want to add staff to see this patient's medical history?</label>
                <div style="width: 350px; height: 150px; overflow-y: auto; border: 1px solid #ccc; padding: 5px;">
                    <?php
                    $stmt = $db->query("SELECT id, fullname, rights FROM admin_users WHERE status=1");
                    // Assuming $stmt is already defined and contains the staff data
                    while ($row_s = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <div>
                            <input type="checkbox" name="staff_list[]" value="<?= $row_s['id'] . '__' . $row_s['rights']; ?>" id="staff_<?= $row_s['id']; ?>">
                            <label for="staff_<?= $row_s['id']; ?>"><?= $row_s['fullname']; ?></label>
                        </div>
                    <?php } ?>
                </div>
            </div>



        <?php } else { ?>
            <h2>Are you sure you want to Manage this Patient?</h2>
        <?php } ?>
        <hr>
        <input type="hidden" name="vip" id="" value="<?php echo $make_vip; ?>" />
        <input type="hidden" name="hospital_no_manage" id="" value="<?php echo $hospital_no_manage; ?>" />
        <input type="hidden" name="doctor_id" id="" value="<?php echo $doctor_id; ?>" />
        <input type="hidden" name="manage_status" id="" value="<?php echo $manage_status; ?>" />
        <input type="hidden" name="patient_manage_hx" id="" value="<?php echo $patient_manage_hx; ?>" />


        <?php if ($manage_hx != '') {
            echo "<strong>Patient History</strong>";
            echo '<br>' . $manage_hx;
            echo '<hr>';
        } ?>

        <?php
        if ($vip == 1) { ?>
            <hr>

            <table class="table table-striped table-bordered table-hover dataTables-example">
                <thead>
                    <tr>
                        <th><strong>#</strong></th>
                        <th><strong>Staff</strong></th>
                        <th><strong>Date</strong></th>
                        <th><strong>Current Status</strong></th>
                        <th><strong>Action</strong></th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $n = 1;
                    $stmt = $db->prepare("SELECT f.fullname, m.* FROM manage_patients_vip_staff AS m 
                                                        INNER JOIN admin_users AS f ON m.user_id = f.id WHERE who_created= :who_created");
                    $stmt->bindParam(':who_created', $_SESSION['id']);
                    $stmt->execute();
                    while ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                        <tr>
                            <td><?php echo $n++; ?></td>
                            <td><?php echo $rw['fullname']; ?></td>
                            <td>
                                <?= date('d M, Y h:i a', strtotime("" . $rw['date_time'])); ?>
                            </td>
                            <td><?php if ($rw['status'] == 1) {
                                    echo '<b style="color:blue;">Active</b>';
                                } else {
                                    echo '<b style="color:red;">Disabled</b>';
                                } ?></td>
                            <td>
                                <?php if ($rw['status'] == 1) { ?>
                                    <button type="button" class="btn btn-warning btn-xs dropdown-toggle" id="" onClick="disable_manage('<?php echo $rw['sn']; ?>')">Disable</button>
                                <?php } else { ?>
                                    <button type="button" class="btn btn-success btn-xs dropdown-toggle" id="" onClick="able_manage('<?php echo $rw['sn']; ?>')">Activate</button>

                                <?php } ?>
                            </td>
                            <td>
                            </td>
                        </tr>

                    <?php
                    }
                    ?>

                </tbody>
            </table>


        <?php } ?>


        <input type="submit" name="hospital_manage_save" class="btn btn-sm btn-primary"> &nbsp;&nbsp; :
        <button class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
    </form>

<?php

}


?>
<script>
    document.getElementById('serviceTemplate').addEventListener('change', function() {
        var staffSelectionDiv = document.getElementById('staffSelection');
        if (this.value === 'VIP') {
            staffSelectionDiv.style.display = 'block'; // Show staff selection
        } else {
            staffSelectionDiv.style.display = 'none'; // Hide staff selection
        }
    });
</script>