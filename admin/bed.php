<?php

if (isset($_POST['update_hmo_prices_btn'])) {

    $no_of_hmos_to_update = $_POST['all_'] - 1;
    for ($i = 0; $i <= $no_of_hmos_to_update; $i++) {

        ///echo $_POST['prices'][$i];

        $updateSQL = "UPDATE hmo_bed_tariff SET price = :price WHERE stock_sn = :stock_sn AND hmo = :hmo";
        $stmt = $db->prepare($updateSQL);

        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
        $stmt->bindParam(':hmo', $hmo, PDO::PARAM_STR);

        $length = count($_POST['prices']);

        for ($i = 0; $i < $length; $i++) {
            $price = $_POST['prices'][$i];
            $stock_sn = $_POST['stock_sn'][$i];
            $hmo = $_POST['hmos'][$i];

            $stmt->execute();
        }

        $sv = 1;
    }
}


if (isset($_POST["writeoff_dischgr"])) {

    $hos_no = $_POST['hos_no'];
    $app_no = $_POST['app_no'];
    $room_bed = $_POST['room_bed'];
    $room_bed_sn = $_POST['room_bed_sn'];
    $payment_remarks = $_POST['payment_remarks'];
    $all_items = [];

    try {
        // Begin transaction
        $db->beginTransaction();

        ///////////// WRITE OFF UNPAID CREDIT SERVICES /////////////
        $stmtt = $db->query("
            SELECT sn,item_services,pay FROM patient_ap_services 
            WHERE hospital_no = '$hos_no' AND invoice_status != '3' AND paystatus = '0' AND cr = '1'
        ");

        if ($stmtt->rowCount() > 0) {
            while ($row = $stmtt->fetch(PDO::FETCH_ASSOC)) {
                $sn = $row['sn'];
                $item_services = $row['item_services'];
                $pay = $row['pay'];
                $setdate = date("Y-m-d");
                $paystatus = '1';
                $transact_date = $setdate;
                $pay_mode = 'writeoff';


                if ($item_services !== '' && $pay !== '') {
                    $all_items[] = "{$item_services}({$pay})";
                }

                $updateSQL = "UPDATE patient_ap_services 
                              SET paystatus = :paystatus, transact_date = :transact_date, pay_mode = :pay_mode 
                              WHERE hospital_no = :hospital_no AND sn = :sn";
                $stmt = $db->prepare($updateSQL);
                $stmt->bindParam(':paystatus', $paystatus);
                $stmt->bindParam(':transact_date', $transact_date);
                $stmt->bindParam(':pay_mode', $pay_mode);
                $stmt->bindParam(':hospital_no', $hos_no);
                $stmt->bindParam(':sn', $sn);
                $stmt->execute();
            }
        }

        ///////////// UPDATE BED STATUS /////////////
        $db->exec("UPDATE bed_mgt SET status = '0' WHERE sn = '$room_bed_sn'");

        ///////////// UPDATE ADMISSION STATUS /////////////
        $date_discharge = date("Y-m-d H:i:s");
        $adm_status = '4';
        $old_adm_status = '3';

        $updateSQL2 = "UPDATE admission 
                       SET adm_status = :adm_status, date_discharge = :date_discharge 
                       WHERE hospital_no = :hospital_no AND room_bed_sn = :room_bed_sn AND adm_status = :old_adm_status";
        $stmt = $db->prepare($updateSQL2);
        $stmt->bindParam(':adm_status', $adm_status);
        $stmt->bindParam(':date_discharge', $date_discharge);
        $stmt->bindParam(':hospital_no', $hos_no);
        $stmt->bindParam(':room_bed_sn', $room_bed_sn);
        $stmt->bindParam(':old_adm_status', $old_adm_status);
        $stmt->execute();

        ///////////// CHECK & UPDATE FELLOW-UP /////////////
        $stmt = $db->prepare("SELECT * FROM discharge_fellowup WHERE hospital_no = :hospital_no");
        $stmt->bindParam(':hospital_no', $hos_no);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row_check = $stmt->fetch(PDO::FETCH_ASSOC);
            $status = ($row_check['status'] == 'pending') ? 'discharge' : $row_check['status'];
            $ap_direction = 1;

            // Update apptm
            $updateSQL3 = "UPDATE apptm 
                           SET status = :status, ap_direction = :ap_direction 
                           WHERE appt_no = :appt_no AND hospital_no = :hospital_no";
            $stmt_update = $db->prepare($updateSQL3);
            $stmt_update->bindParam(':status', $status);
            $stmt_update->bindValue(':ap_direction', $ap_direction);
            $stmt_update->bindParam(':appt_no', $app_no);
            $stmt_update->bindParam(':hospital_no', $hos_no);
            $stmt_update->execute();

            // Delete follow-up
            $deleteSQL = "DELETE FROM discharge_fellowup WHERE hospital_no = :hospital_no";
            $stmt_delete = $db->prepare($deleteSQL);
            $stmt_delete->bindParam(':hospital_no', $hos_no);
            $stmt_delete->execute();
        }

        $all_items_here = implode(", ", $all_items);
        $total_amt = 0;
        $stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no,service_list,total_amount, remark, staff_name) 
        VALUES (:hospital_no,:list_desc,:cash, :remark, :staff_name)");

        $stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
        $stmt->bindParam(':list_desc', $all_items_here, PDO::PARAM_STR);
        $stmt->bindParam(':cash', $total_amt, PDO::PARAM_STR);
        $stmt->bindParam(':remark', $payment_remarks, PDO::PARAM_STR);
        $stmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
        $stmt->execute();

        $sv = 1;

        // All operations succeeded — commit the transaction
        $db->commit();
    } catch (Exception $e) {
        // Roll back transaction on error
        $db->rollBack();
        echo "<div class='alert alert-danger'>Transaction failed: " . $e->getMessage() . "</div>";
    }
}



if ((isset($_POST["add_room"])) && ($_POST["MM_update"] == "pdetail")) {

    // Check if the room already exists in the bed table
    $stmt = $db->prepare("SELECT * FROM bed WHERE rooms = :rooms");
    $stmt->bindParam(':rooms', $_POST['create_room'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        if (isset($_POST['room_id_']) && $_POST['room_id_'] != '') {
            //intent is to update exixting room
            try {
                // Start the transaction
                $db->beginTransaction();

                //obtain the current room name, sothat we can change the name of all the beds in that room to the new name
                $stmt = $db->prepare("SELECT rooms FROM bed WHERE sn = ?");
                $stmt->execute([$_POST['room_id_']]);
                $currentRoom = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$currentRoom) {
                    throw new Exception('Room not found.');
                }

                $currentRoomName = $currentRoom['rooms'];

                // Update all beds that have the current room name with the new room name
                $stmt = $db->prepare("UPDATE bed_mgt SET room_name = ? WHERE room_name = ?");
                $stmt->execute([$_POST['create_room'], $currentRoomName]);

                // Update the room itself
                $stmt = $db->prepare("UPDATE bed SET rooms = ?, dept_id = ? WHERE sn = ?");
                $stmt->execute([$_POST['create_room'], $_POST['Department'], $_POST['room_id_']]);

                // Commit the transaction
                $db->commit();
                $sv = 1;
            } catch (Exception $e) {
                // Rollback the transaction if something goes wrong
                $db->rollBack();
                error_log("Failed to update room and beds: " . $e->getMessage());
                $error = "<script>alert('Failed to update room!')</script>";
                echo $error;
                $sv = 0;
            }
        } else {
            // Room does not exist an intent is to insert new record, so insert new record
            $insertSQL = "INSERT INTO bed(rooms, dept_id) VALUES (:rooms, :dept_id)";
            $stmt_insert = $db->prepare($insertSQL);
            $stmt_insert->bindParam(':rooms', $_POST['create_room'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':dept_id', $_POST['Department'], PDO::PARAM_STR);
            $stmt_insert->execute();
            $sv = 1; // Set success flag to 1
        }
    } else {
        // Room already exists
        $error = "<script>alert('Room Name Already Exist!');</script>";
        echo $error;
        $sv = 0;
    }
}



if (isset($_POST["delete_bed"])) {
    $delete_bed_id = $_POST['delete_bed_id'];

    $stmt = $db->prepare("DELETE FROM bed_mgt WHERE sn = :sn");
    $stmt->bindParam(':sn', $delete_bed_id, PDO::PARAM_INT);
    $stmt->execute();
    $dl = '1';
}


if ((isset($_POST["add_bed"])) && ($_POST["MM_update"] == "pdetail")) {

    $room = $_POST['room'];
    $stmt = $db->query("SELECT dept_id FROM bed WHERE rooms='$room'");
    $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
    $dept_id =  $row2['dept_id'];


    if (isset($_POST["sn"]) and $_POST["sn"] != '') {

        if ($_POST['nhis_price'] > 0) {
            $access = 1;
        } else {
            $access = 3;
        }

        $updateSQL = "UPDATE bed_mgt SET room_name=:room_name, tips=:tips, nhis_price=:nhis_price, hosp_price=:hosp_price, ext_price=:ext_price, access=:access, dept_id=:dept_id WHERE sn=:sn";
        $stmt = $db->prepare($updateSQL);
        $stmt->bindParam(':room_name', $_POST['room'], PDO::PARAM_STR);
        $stmt->bindParam(':tips', $_POST['title'], PDO::PARAM_STR);
        $stmt->bindParam(':nhis_price', $_POST['nhis_price'], PDO::PARAM_STR);
        $stmt->bindParam(':hosp_price', $_POST['hosp_price'], PDO::PARAM_STR);
        $stmt->bindParam(':ext_price', $_POST['exit_pricee'], PDO::PARAM_STR);
        $stmt->bindParam(':access', $access, PDO::PARAM_STR); // assuming $access is defined elsewhere
        $stmt->bindParam(':dept_id', $_POST['Department'], PDO::PARAM_STR); // assuming $dept_id is defined elsewhere
        $stmt->bindParam(':sn', $_POST['sn'], PDO::PARAM_INT);
        $stmt->execute();
        $sv = 1;
    } else {


        $qry = $db->prepare("SELECT * FROM bed_mgt WHERE room_name=:room_name AND bed_no=:bed_no");
        $qry->bindParam(':room_name', $_POST['room'], PDO::PARAM_STR);
        $qry->bindParam(':bed_no', $_POST['bed_number'], PDO::PARAM_STR);
        $qry->execute();

        if ($qry->rowCount() == 0) {
            if ($_POST['nhis_price'] > 0) {
                $access = 1;
            } else {
                $access = 3;
            }

            $insertSQL = "INSERT INTO bed_mgt(room_name, bed_no, tips, nhis_price, hosp_price, ext_price, access, status, dept_id) 
							  VALUES (:room_name, :bed_no, :tips, :nhis_price, :hosp_price, :ext_price, :access, :status, :dept_id)";

            $stmt = $db->prepare($insertSQL);
            $stmt->bindParam(':room_name', $_POST['room'], PDO::PARAM_STR);
            $stmt->bindParam(':bed_no', $_POST['bed_number'], PDO::PARAM_STR);
            $stmt->bindParam(':tips', $_POST['title'], PDO::PARAM_STR);
            $stmt->bindParam(':nhis_price', $_POST['nhis_price'], PDO::PARAM_STR);
            $stmt->bindParam(':hosp_price', $_POST['hosp_price'], PDO::PARAM_STR);
            $stmt->bindParam(':ext_price', $_POST['exit_pricee'], PDO::PARAM_STR);
            $stmt->bindParam(':access', $access, PDO::PARAM_INT);
            $stmt->bindValue(':status', '0', PDO::PARAM_INT);
            $stmt->bindParam(':dept_id', $_POST['Department'], PDO::PARAM_STR); // assuming $dept_id is defined elsewhere

            if ($stmt->execute()) {
                $sv = 1;
            } else {
                $error = "Failed to insert bed details.";
            }
        } else {
            $error = "Bed Name Already Exist!";
            echo $error;
        }
    }
}


if (isset($_GET['sn'])) {
    $sn = $_GET['sn'];

    $stmt = $db->prepare("SELECT * FROM bed_mgt WHERE sn = :sn");
    $stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
    $stmt->execute();
    $row_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET['free'])) {
    $free = $_GET['free'];

    $stmt = $db->prepare("UPDATE bed_mgt SET status = '0' WHERE sn = :free");
    $stmt->bindParam(':free', $free, PDO::PARAM_INT);
    $stmt->execute();
}

if (isset($_GET['lock'])) {
    $lock = $_GET['lock'];

    $stmt = $db->prepare("UPDATE bed_mgt SET status = '1' WHERE sn = :lock");
    $stmt->bindParam(':lock', $lock, PDO::PARAM_INT);
    $stmt->execute();
}

if (isset($_GET['dl'])) {
    $cat_name = $_GET['dl'];

    $stmt = $db->prepare("DELETE FROM bed WHERE rooms = :cat_name");
    $stmt->bindParam(':cat_name', $cat_name, PDO::PARAM_STR);
    $stmt->execute();

    $stmt = $db->prepare("DELETE FROM bed_mgt WHERE room_name = :cat_name");
    $stmt->bindParam(':cat_name', $cat_name, PDO::PARAM_STR);
    $stmt->execute();

    $del = 1;
}

?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Bed Reports</h5>
                <div class="ibox-tools">

                    <input type="button" name="edit" value="Add/Edit Room/Ward or Category" data-target="#myModal5" id="" class="btn btn-primary btn-xs add_new_bed_cat" />
                    &nbsp;&nbsp; | &nbsp;&nbsp;
                    <input type="button" name="add_bed" value="Add New Bed" data-target="#myModal5" id="" class="btn btn-primary btn-xs add_new_bed" />

                </div>
            </div>


            <div class="ibox-content">

                <div class="row">

                    <form action="index.php?bed" method="POST">
                        <div class="col-sm-2">
                            <label>Sort by Floor</label>
                            <select name="floor" class="form-control">
                                <option value="" <?php echo (empty($_POST['floor']) ? 'selected' : ''); ?>>... select ...</option>
                                <?php
                                $stmt = $db->query("SELECT DISTINCT tips FROM bed_mgt WHERE tips != '' ORDER BY tips ASC");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = (isset($_POST['floor']) && $_POST['floor'] == $row['tips']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['tips']) . '" ' . $selected . '>' . htmlspecialchars($row['tips']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-sm-3">
                            <label>Sort by Category</label>
                            <select name="category" class="form-control">
                                <option value="" <?php echo (empty($_POST['category']) ? 'selected' : ''); ?>>... select ...</option>
                                <?php
                                $stmt = $db->query("SELECT DISTINCT rooms FROM bed ORDER BY rooms ASC");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = (isset($_POST['category']) && $_POST['category'] == $row['rooms']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['rooms']) . '" ' . $selected . '>' . htmlspecialchars($row['rooms']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-sm-2">
                            <label>Sort by Department</label>
                            <select name="dept_id" class="form-control">
                                <option value="" <?php echo (empty($_POST['dept_id']) ? 'selected' : ''); ?>>... select ...</option>
                                <?php
                                $stmt = $db->query("
            SELECT DISTINCT bm.dept_id, d.department
            FROM bed_mgt AS bm
            INNER JOIN department AS d ON bm.dept_id = d.sn
            WHERE bm.dept_id IS NOT NULL
            ORDER BY d.department ASC
        ");
                                while ($dept = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = (isset($_POST['dept_id']) && $_POST['dept_id'] == $dept['dept_id']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($dept['dept_id']) . '" ' . $selected . '>' . htmlspecialchars($dept['department']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-sm-2">
                            <label>Sort by Vacant/Occupied</label>
                            <select name="vc" class="form-control">
                                <option value="" <?php echo (empty($_POST['vc']) ? 'selected' : ''); ?>>... select ...</option>
                                <option value="v" <?php echo (isset($_POST['vc']) && $_POST['vc'] == 'v') ? 'selected' : ''; ?>>Vacant</option>
                                <option value="o" <?php echo (isset($_POST['vc']) && $_POST['vc'] == 'o') ? 'selected' : ''; ?>>Occupied</option>
                            </select>
                        </div>


                        <div class="col-sm-3">
                            <label class="invisible">Action</label><br>
                            <button type="submit" name="apply" id="apply" class="btn btn-info btn-sm">Apply</button>
                            &nbsp;&nbsp; | &nbsp;&nbsp;
                            <a href="index.php?bed" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i>&nbsp;Refresh</a>
                        </div>
                    </form>


                </div>
                <hr>
                <br>

                <strong style="color:#F00"><?php echo $error; ?></strong>
                <?php
                $locked = $unlocked = $occupy = $vacant = 0;

                $whereClause = '';
                $params = [];

                if (isset($_POST['apply'])) {
                    if (!empty($_POST['floor'])) {
                        $whereClause = "WHERE b.tips = :filter";
                        $params[':filter'] = $_POST['floor'];
                    } elseif (!empty($_POST['category'])) {
                        $whereClause = "WHERE b.room_name = :filter";
                        $params[':filter'] = $_POST['category'];
                    } elseif (!empty($_POST['dept_id'])) {
                        $whereClause = "WHERE b.dept_id = :filter";
                        $params[':filter'] = $_POST['dept_id'];
                    } elseif (!empty($_POST['vc'])) {
                        $status = ($_POST['vc'] == 'o') ? 1 : 0;
                        $whereClause = "WHERE b.status = :filter";
                        $params[':filter'] = $status;
                    }
                }

                $query = "SELECT b.*, d.department,d.require_amount_b4_adm,d.min_amount_adm,d.min_amount_condition 
          FROM bed_mgt b 
          INNER JOIN department d ON d.sn = b.dept_id 
          $whereClause 
          ORDER BY b.room_name, b.bed_no";

                $stmt = $db->prepare($query);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0): ?>
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Room Name</th>
                                <th>Bed #</th>
                                <th>Department</th>
                                <th>Coverage</th>
                                <th>NHIS Price</th>
                                <th>HMO Price</th>
                                <th>Private Price</th>
                                <th>Status</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $n = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):

                                $require_amount_b4_adm = $row['require_amount_b4_adm'];
                                $min_amount_adm = $row['min_amount_adm']; //. '</h3>';
                                $min_amount_condition = $row['min_amount_condition'];

                                $roomDisplay = $row['room_name'] . (!empty($row['tips']) ? " ({$row['tips']})" : '');
                                $coverage = ($row['access'] == 3) ? 'Private' : (($row['access'] == 1) ? 'Private/NHIS' : 'Unspecified');
                            ?>
                                <tr>
                                    <td><?= $n++; ?></td>
                                    <td><?= htmlspecialchars($roomDisplay); ?></td>
                                    <td><?= 'Bed ' . $row['bed_no']; ?></td>
                                    <td><?= htmlspecialchars($row['department']); ?></td>
                                    <td><?= $coverage; ?></td>
                                    <td><?= $row['nhis_price']; ?></td>
                                    <td><?= $row['hosp_price']; ?></td>
                                    <td><?= $row['ext_price']; ?></td>

                                    <?php if ($row['status'] == 0): $vacant++; ?>
                                        <td><span style="color:#00F;"><strong>Vacant</strong></span></td>
                                        <td>
                                            <button class="btn btn-warning btn-xs edit_bed" id="<?= $row['sn']; ?>" <?= ($_SESSION['accom_price'] == 0) ? 'disabled' : ''; ?>>Edit</button>
                                            &nbsp;|&nbsp;
                                            <button class="btn btn-warning btn-xs edit_bed_tariff" id="<?= $row['sn']; ?>">Tariff</button>
                                            &nbsp;|&nbsp;
                                            <button class="btn btn-danger btn-xs confirm_delete_bed" id="<?= $row['sn']; ?>">Delete Bed</button>
                                            &nbsp;|&nbsp;
                                            <a href="index.php?bed&lock=<?= $row['sn']; ?>" class="btn btn-success btn-xs">Lock Bed</a>
                                        </td>
                                    <?php else:
                                        $adm_status = '3';
                                        $stmt2 = $db->prepare("SELECT hospital_no FROM admission WHERE room_bed_sn = :sn AND adm_status = :adm_status");
                                        $stmt2->execute([':sn' => $row['sn'], ':adm_status' => $adm_status]);
                                        $row_s = $stmt2->fetch(PDO::FETCH_ASSOC);
                                        $hosp_no = isset($row_s['hospital_no']) ? $row_s['hospital_no'] : 'Error';
                                        if ($hosp_no !== 'Error') {
                                            $occupy++;
                                            $statusText = 'Occupied';
                                        } else {
                                            $locked++;
                                            $statusText = 'Locked';
                                        }
                                    ?>
                                        <td><span style="color:#F00;"><strong><?= $statusText; ?></strong></span></td>
                                        <td>
                                            <button class="btn btn-warning btn-xs edit_bed" id="<?= $row['sn']; ?>">Price</button>
                                            &nbsp;|&nbsp;
                                            <button class="btn btn-warning btn-xs edit_bed_tariff" id="<?= $row['sn']; ?>">Tariff</button>
                                            &nbsp;|&nbsp;
                                            <?php if ($hosp_no !== 'Error'): ?>
                                                <button class="btn btn-primary btn-xs see_occupant" id="<?= $hosp_no; ?>">See Occupant: <?= $hosp_no; ?></button>
                                            <?php else: ?>
                                                <a href="index.php?bed&free=<?= $row['sn']; ?>" class="btn btn-success btn-xs">Unlock</a>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php
                    if (!empty($_POST['dept_id'])) {
                        if ($require_amount_b4_adm == 1) {
                            echo '<h3>No Required Amount to Admit Patient</h3>';
                        } else {
                            echo '<h3>Required % from Minimum Admission Deposit: ' . $require_amount_b4_adm . '%</h3>';
                        }
                        echo '<h3>Minimum Deposit: ' . number_format($min_amount_adm, 2) . '</h3>';
                        echo '<h3>Percent of Credit Limit: ' . $min_amount_condition . '%</h3>';
                    }


                    ?>
                    <hr>

                    <table width="100%" align="center">
                        <tr>
                            <td align="center"><strong style="font-size:16px;">Total Beds: <?= $stmt->rowCount(); ?></strong></td>
                            <td align="center"><strong style="font-size:16px;">Vacant Beds: <?= $vacant; ?></strong></td>
                            <td align="center"><strong style="font-size:16px;">Locked Beds: <?= $locked; ?></strong></td>
                            <td align="center"><strong style="font-size:16px;">Occupied Beds: <?= $occupy; ?></strong></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p>No Records Found</p>
                <?php endif; ?>


            </div>

        </div>
    </div>

</div>

<div class="modal inmodal fade" id="see_occupant_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Occupant Information</h4>
            </div>
            <div class="modal-body" id="see_occupant_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="delete_bed_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Confirm Delete</h4>
            </div>
            <div class="modal-body" id="delete_bed_body">
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="add_new_bed_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <!--				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
-->
                <h4 class="modal-title" id="">Bed Manager</h4>
            </div>
            <div class="modal-body" id="add_new_bed_body">






                <form action="index.php?bed" method="POST">

                    <div class="form_sep">

                        <label for="reg_select" class="">Select Room/Ward</label>
                        <select name="room" id="room" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <?php
                            $stmt = $db->query("SELECT * FROM bed ORDER BY sn ASC");
                            while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo  $row2['rooms']; ?>"><?php echo $row2['rooms']; ?></option>
                            <?php } ?>
                        </select>

                    </div>


                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Bed Number</label>
                        <input type="number" id="bed_number" name="bed_number" class="form-control" min="1" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="">Floor</label>

                        <select name="title" id="title" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="">No Floor</option>
                            <option value="1st Floor">1st Floor</option>
                            <option value="2nd Floor">2nd Floor</option>
                            <option value="3rd Floor">3rd Floor</option>
                            <option value="4th Floor">4th Floor</option>
                            <option value="5th Floor">5th Floor</option>
                            <option value="Ground Floor">Ground Floor</option>
                        </select>

                    </div>


                    <div class="form_sep">
                        <label for="reg_input_no" class="req">NHIS Price<br><small style="color:#F00">Enter Zero(0) if not covered under insurance scheme</small></label>
                        <input type="number" step="any" id="nhis_price" name="nhis_price" class="form-control" min="0" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">HMO Based Price</label>
                        <input type="number" step="any" id="hosp_price" name="hosp_price" class="form-control" min="0" required>
                    </div>


                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Private Patient Price</label>
                        <input type="number" step="any" id="exit_pricee" name="exit_pricee" class="form-control" min="0" required>
                    </div>


                    <div class="form_sep">

                        <label for="reg_select" class="">Department</label>
                        <select name="Department" id="Department" class="form-control" required>
                            <option value="">Select Department...</option>
                            <?php

                            $stt = $db->query("SELECT * FROM department order by department");
                            while ($row_rstdepartment = $stt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option <?php if ($dp == $row_rstdepartment["sn"]) { ?>selected<?php } ?> value="<?php echo $row_rstdepartment["sn"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep"></div>

                    <div class="pull-left">
                        <button class="btn btn-success btn-sm" type="submit" name="add_bed" id="add_bed">Save Bed</button>
                    </div>

                    <div class="pull-right">
                        <a href="index.php?bed" class="btn btn-warning btn-sm"><i class="fa fa-times"></i> &nbsp; Close</a>
                    </div>

                    <input type="hidden" name="MM_update" value="pdetail" />
                    <input type="hidden" name="sn" id="sn" />

                </form>



            </div>
        </div>
    </div>
</div>





<div class="modal inmodal fade" id="add_new_cat_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Bed Manager</h4>
            </div>
            <div class="modal-body" id="add_new_cat_body">

                <form action="index.php?bed" method="POST">

                    <strong>Enter Room/Ward Name</strong><br>

                    <div class="form_sep">
                        <input type="text" id="create_room" name="create_room" class="form-control" data-required="true">
                    </div>

                    <div class="form_sep">
                        <?php $dp = $row_rstSelect['Department']; ?>
                        <label for="reg_select" class="">Department</label>
                        <select name="Department" id="Department" class="form-control">
                            <option value="">Select Department...</option>
                            <?php

                            $stt = $db->query("SELECT * FROM department order by department");
                            while ($row_rstdepartment = $stt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row_rstdepartment["sn"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep">

                        <button class="btn btn-success btn-sm" type="submit" name="add_room" id="add_room">Add</button>
                    </div>

                    <input type="hidden" name="room_id_" id="room_id_">
                    <input type="hidden" name="MM_update" value="pdetail" />
                </form>

                <hr>

                <?php
                $stmt = $db->query("SELECT b.*, d.department FROM bed as b inner join department as d on d.sn=dept_id order by rooms");
                if ($stmt->rowCount() > 0) { ?>
                    <strong>Existing Records List</strong>
                    <hr>
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Category/Name</th>
                                <th>Department</th>
                                <th>.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $n = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php echo $n; ?></td>
                                    <td><?php echo $row['rooms']; ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td><a href="#" class="edit_room_btn scroll-to-top-of-modal" data-room_name="<?php echo $row['rooms']; ?>">Edit</a></td>
                                    <td><a href="index.php?price=b&dl=<?php echo $row['rooms']; ?>" class="text-danger">Delete</a></td>
                                </tr>
                            <?php $n++;
                            } ?>
                            </tr>
                        </tbody>
                    </table>
                <?php } else {
                    echo 'No Records Found';
                } ?>






            </div>
        </div>
    </div>
</div>