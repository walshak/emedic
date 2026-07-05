<?php
include("../Connections/Conn.php");
session_start();


if (isset($_POST['delete_booking_sn'])) {
    $delete_booking_sn = $_POST['delete_booking_sn'];

    $stmt = $db->prepare("DELETE FROM apptm_fellowup WHERE  sn = ? ");
    $delete = $stmt->execute(array($delete_booking_sn));
    if ($delete) {
        echo "Deleted Successful";
    }
}
?>

<form action="<?php echo $editFormAction; ?>" method="POST" id="subject1" name="subject" enctype="multipart/form-data">

    <div class="form_sep">
        <label for="reg_select" class="req">Appointment Type</label>
        <select name="appointment_type" id="appointment_type" class="form-control" required onchange="toggleSpecialistDropdown()">
            <option value=""> -- Select Type --</option>
            <option value="see_specialist">See Specialist</option>
            <option value="fellowup">Follow-Up Appointment</option>
            <option value="comment">Remarks</option>
        </select>
    </div>

    <!-- Specialist dropdown (hidden by default) -->
    <div class="form_sep" id="specialist_dropdown" style="display:none;">
        <label for="specialist" class="req">Select Specialist</label>
        <select name="specialist" class="form-control">
            <option value="">-- Choose Specialist --</option>
            <?php
            try {
                $sql = "
                    SELECT s.id, s.name, a.fullname
                    FROM specialists AS s
                    INNER JOIN admin_users AS a ON s.id = a.specialist
                    WHERE a.status = 1
                    ORDER BY s.name
                ";


                $stmt = $db->prepare($sql);
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($row['fullname']) . " (" . htmlspecialchars($row['name']) . ")" . "'>"
                        . htmlspecialchars($row['fullname']) . " (" . htmlspecialchars($row['name']) . ")</option>";
                }
            } catch (PDOException $e) {
                echo "<option disabled>Error loading specialists</option>";
            }
            ?>
        </select>

    </div>

    <div class="form_sep">
        <label for="request_note_description" class="req">Describe Request Here: </label>
        <textarea class="form-control" rows="5" name="request_note_description" required></textarea>
    </div>

    <?php
    date_default_timezone_set('Africa/Lagos');
    $defaultDateTime = date('Y-m-d\TH:i'); // Format: YYYY-MM-DDTHH:MM
    ?>

    <div class="form_sep">
        <label for="appointment_date" class="req">Date:</label>
        <input type="datetime-local" name="appointment_date" class="form-control" value="<?php echo $defaultDateTime; ?>" required>
    </div>

    <br>
    <table width="100%">
        <tr>
            <td>
                <button class="btn btn-success btn-sm" type="submit" name="see_a_specialist_btn" id="see_a_specialist_btn" onclick="return confirm('Are you sure you want to send request ?')">Send Request Now</button>
            </td>
            <td>
                <div align="right">
                    <button class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                </div>
            </td>
        </tr>
    </table>

    <hr>
    <?php

    $hospital_no = $_POST['___hospital_no'];
    $stmt = $db->prepare("SELECT * FROM apptm_fellowup WHERE hospital_no='$hospital_no' AND date_time_stamp BETWEEN NOW() - INTERVAL 48 HOUR AND NOW()  order by sn desc");
    $interfc = 2;
    $stmt->execute();
    $n = 1;
    if ($stmt->rowCount() > 0) { ?>

        <h2>Doctor's Requests</h2>
        <table class="table table-bordered" style="font-size: 13px;">
            <thead>
                <tr>
                    <th><strong>#</strong></th>
                    <th width="">Date</th>
                    <?php if ($interfc == 1 && $interfaccc == "front-desk") { ?><th>Name</th><?php } ?>
                    <th width="">Patient Name</th>
                    <th width="">Notes</th>
                    <th width="">Noted By</th>
                    <th width=""></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sn = $row['sn'];
                ?>
                    <tr>
                        <td><?= $n++; ?></td>
                        <td><?php echo date('d M,Y', strtotime($row['date_time'])) . '<br>' . date('h:i a', strtotime($row['date_time'])); ?></td>
                        <?php if ($interfc == 1) { ?><td><?php echo $row['fname'] . ' ' . $row['surname']; ?></td><?php } ?>
                        <td><?php echo '<b>' . strtoupper($row['service_type']) . '</b>' . '<br>' . $row['request_note_description']; ?> </td>
                        <td><?php echo $row['doctor_name']; ?></td>

                        <td><?php if ($row['status'] == 1) {
                                echo '<b>Acknowledged</b>';
                            } elseif ($row['status'] == 0 && $interfc == 2) {
                            ?>
                                <input type="button" name="Delete" value="Delete" onclick="delete_doc_booking('<?= $sn; ?>')"
                                    class="btn btn-danger btn-xs" />
                            <?php } elseif ($interfaccc == "front-desk") { ?>
                                <input type="button" name="" value="Acknowledge" onclick="Accknl_booking('<?= $sn; ?>')"
                                    class="btn btn-warning btn-xs" />
                            <?php } ?>
                        </td>
                    </tr>
                <?php     } ?>

            </tbody>
        </table>

    <?php }

    ?>
    <input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
    <input type="hidden" name="hosp_no" id="___hospital_no" value="<?php echo $hospital_no; ?>" />

</form>