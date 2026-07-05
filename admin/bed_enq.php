<?php


?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Bed List Enq.</h5>
            </div>


            <div class="ibox-content">

                <div class="row">

                    <form action="index.php?bed_enq" method="POST">
                        <div class="col-sm-2">
                            <label for="floor_select">Sort by Floor</label>
                            <select name="floor" id="floor_select" class="form-control">
                                <option value="">... select ...</option>
                                <?php
                                $floors = $db->query("
                                    SELECT DISTINCT tips 
                                    FROM bed_mgt 
                                    WHERE tips != '' 
                                    ORDER BY tips ASC
                                ")->fetchAll(PDO::FETCH_COLUMN);

                                foreach ($floors as $floor) {
                                    echo '<option value="' . htmlspecialchars($floor) . '">' . htmlspecialchars($floor) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-sm-2">
                            <label for="category_select">Sort by Category</label>
                            <select name="category" id="category_select" class="form-control">
                                <option value="">... select ...</option>
                                <?php
                                $categories = $db->query("
                                    SELECT DISTINCT rooms 
                                    FROM bed 
                                    WHERE rooms != '' 
                                    ORDER BY sn ASC
                                ")->fetchAll(PDO::FETCH_COLUMN);

                                foreach ($categories as $category) {
                                    echo '<option value="' . htmlspecialchars($category) . '">' . htmlspecialchars($category) . '</option>';
                                }
                                ?>
                            </select>
                        </div>


                        <div class="col-sm-2">
                            <label for="floor_select">Sort by Department</label>
                            <select name="dept_id" id="floor_select" class="form-control">
                                <option value="">... select ...</option>
                                <?php
                                $stmt = $db->query("
            SELECT DISTINCT bm.dept_id, d.department
            FROM bed_mgt AS bm
            INNER JOIN department AS d ON bm.dept_id = d.sn
            WHERE bm.dept_id IS NOT NULL
            ORDER BY d.department ASC
        ");

                                $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($departments as $dept) {
                                    echo '<option value="' . htmlspecialchars($dept['dept_id']) . '">' . htmlspecialchars($dept['department']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>


                        <div class="col-sm-2">
                            <label for="reg_select" class="">Sort by qqqq/Occupied</label>
                            <select name="vc" class="form-control">
                                <option selected="selected" value="">... select ...</option>
                                <option value="v">Vacant</option>
                                <option value="o">Occupied</option>
                            </select>
                        </div>



                        <div class="col-sm-2">

                            <div class="form_sep">
                                <label for="reg_select" class="">.</label><br>
                                <button class="btn btn-info btn btn-sm" type="submit" name="apply" id="apply">Apply</button>
                                &nbsp;&nbsp; | &nbsp;&nbsp;

                                <a href="index.php?bed_enq" class="btn btn-white btn btn-sm"><i class="fa fa-refresh"></i> &nbsp; Refresh</a>
                            </div>

                        </div>
                    </form>

                </div>
                <hr>
                <br>

                <strong style="color:#F00"><?php echo $error; ?></strong>
                <?php
                if (isset($_POST['apply'])) {
                    if (isset($_POST['floor']) and $_POST['floor'] != '') {
                        $floor = $_POST['floor'];
                        $stmt = $db->query("SELECT * FROM bed_mgt WHERE tips='$floor' order by room_name, bed_no");
                    } elseif (isset($_POST['category']) and $_POST['category'] != '') {
                        $category = $_POST['category'];
                        $stmt = $db->query("SELECT * FROM bed_mgt WHERE room_name='$category' order by room_name, bed_no");
                    } elseif (isset($_POST['vc']) and $_POST['vc'] != '') {
                        $vc = $_POST['vc'];
                        if ($vc == 'o') {
                            $status = 1;
                        } else {
                            $status = 0;
                        }
                        $stmt = $db->query("SELECT * FROM bed_mgt WHERE status='$status' order by room_name, bed_no");
                    }
                } else {
                    $locked = 0;
                    $unlocked = 0;
                    $occupy = 0;
                    $vacant = 0;
                    $stmt = $db->query("SELECT * FROM bed_mgt order by room_name, bed_no");
                }
                if ($stmt->rowCount() > 0) { ?>

                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Room Name</th>
                                <th>Bed #</th>
                                <th>Coverage</th>
                                <th>NHIS Price</th>
                                <th>HMO Price</th>
                                <th>Private Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $n = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php echo $n; ?></td>
                                    <td><?php echo $row['room_name'];
                                        if ($row['tips'] != '') {
                                            echo ' (' . $row['tips'] . ')';
                                        } ?></td>
                                    <td><?php echo 'bed ' . $row['bed_no']; ?></td>
                                    <td><?php if ($row['access'] == 3) {
                                            echo 'Private';
                                        } elseif ($row['access'] == 1) {
                                            echo 'Private/NHIS';
                                        } else {
                                            echo 'Unspecified';
                                        } ?></td>
                                    <td><?php echo $row['nhis_price']; ?></td>
                                    <td><?php echo $row['hosp_price']; ?></td>
                                    <td><?php echo $row['ext_price']; ?></td>

                                    <?php if ($row['status'] == 0) {
                                        $vacant = $vacant + 1; ?>
                                        <td>
                                            <div style="color:#00F; font-size:14px"><strong>Vacant</strong></div>
                                        </td>
                                    <?php } else {
                                        $adm_status = '3';
                                        $stmt2 = $db->prepare("SELECT hospital_no FROM admission WHERE room_bed_sn = :room_bed_sn AND adm_status = :adm_status");
                                        $stmt2->bindParam(':room_bed_sn', $row['sn'], PDO::PARAM_INT);
                                        $stmt2->bindParam(':adm_status', $adm_status, PDO::PARAM_STR);
                                        $stmt2->execute();

                                        if ($stmt2->rowCount() > 0) {
                                            $row_s = $stmt2->fetch(PDO::FETCH_ASSOC);
                                            $hosp_no = $row_s['hospital_no'];
                                        } else {
                                            $hosp_no = 'Error';
                                        }

                                    ?>
                                        <td>
                                            <div style="color:#F00; font-size:14px"><strong>
                                                    <?php if ($hosp_no != 'Error') {
                                                        echo 'Occupied';
                                                        $occupy = $occupy + 1;
                                                    } else {
                                                        echo 'Locked';
                                                        $locked = $locked + 1;
                                                    } ?></strong></div>
                                        </td>
                                <?php }
                                    $n++;
                                }
                                ?>
                                </tr>
                        </tbody>
                    </table>


                    <hr>

                    <table width="100%" align="center">
                        <tr>
                            <td align="center">
                                <strong style="font-size:16px;">Total Bed: <?php echo $stmt->rowCount(); ?></strong>
                            </td>
                            <td align="center">
                                <strong style="font-size:16px">Vacant Bed: <?php echo $vacant; ?></strong>
                            </td>
                            <td align="center">
                                <strong style="font-size:16px;">Locked Bed: <?php echo $locked; ?></strong>
                            </td>
                            <td align="center">
                                <strong style="font-size:16px;">Occupied Bed: <?php echo $occupy; ?></strong>
                            </td>
                        </tr>
                    </table>
                <?php } else {
                    echo 'No Records Found';
                } ?>

            </div>

        </div>
    </div>

</div>