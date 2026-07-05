<?php


function xancel() {}
function approve($approve_all, $error_sel) {}
function queue() {}



function labs()
{
    include("../Connections/Conn.php");



    if ($_SESSION['col4'] == "1") {
        $dept_id = $_SESSION['dept_id'];
        $sort_by_dept_ = " AND lab.dept='$dept_id'";
        $sort_by_dept_2 = " WHERE lab.dept='$dept_id'";
    } elseif ($_SESSION['section'] == 'Laboratory' or $_SESSION['section'] == 'Radiology') {
        $category = $_SESSION['section'];
        $sort_by_dept_ = " AND lab.category='$category'";
        $sort_by_dept_2 = " WHERE lab.category='$category'";
    } else {
        $sort_by_dept_ = "";
        $sort_by_dept_2 = "";
    }


    if (isset($_GET['did'])) {
        $dept_id = (int)$_GET['did'];
        $sql = "SELECT lab.*, d.department 
                FROM lab_scan AS lab 
                INNER JOIN department AS d ON d.sn = lab.dept 
                WHERE d.sn = :dept_id $sort_by_dept_ 
                ORDER BY lab.sn";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $sql = "SELECT lab.*, d.department 
                FROM lab_scan AS lab 
                INNER JOIN department AS d ON d.sn = lab.dept 
                $sort_by_dept_2 
                ORDER BY lab.sn";
        $stmt = $db->query($sql);
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($data) > 0): ?>

        <div id="refresh">
            <table class="table table-striped table-bordered table-hover dataTables-example">
                <thead>
                    <tr>
                        <th>No</th>
                        <th width="20%">Investigation</th>
                        <th></th>
                        <th>Department</th>
                        <th>Category</th>
                        <th>INS</th>
                        <th>Hospital<br>Price</th>
                        <th>External<br>Price</th>
                        <th>NHIS<br>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1;
                    foreach ($data as $row): ?>
                        <tr>
                            <td><?= $n ?></td>
                            <td><?= htmlspecialchars($row['test']) ?></td>
                            <td>
                                <?php if ($row['category'] === 'Laboratory'): ?>
                                    <input type="button" name="view_lab_tbl" value="View"
                                        data-toggle="modal" data-target="#myModal5"
                                        id="<?= $row['sn'] . '__' . $row['test'] ?>"
                                        class="btn btn-info btn-xs view_lab" />
                                <?php elseif ($row['category'] === 'Radiology'): ?>
                                    <a href="setup.php?template=<?= $row['sn'] ?>" class="btn btn-primary btn-xs">Template</a>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['sub_category']) ?></td>
                            <td>
                                <?php
                                echo ($row['insurance_type'] == 1) ? 'PRI' : (($row['insurance_type'] == 2) ? 'SEC' : '-');
                                ?>
                            </td>
                            <td><?= number_format($row['hosp_price'], 2) ?></td>
                            <td><?= number_format($row['ext_price'], 2) ?></td>
                            <td><?= number_format($row['nhis_price'], 2) ?></td>
                            <td>
                                <a href="setup.php?delete=<?= $row['sn'] ?>"
                                    class="btn btn-danger btn-xs"
                                    onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
                                &nbsp;|&nbsp;
                                <input type="button" name="edit_lab" value="Edit"
                                    data-toggle="modal" data-target="#myModal5"
                                    id="<?= $row['sn'] . '__' . $row['test'] ?>"
                                    class="btn btn-warning btn-xs edit_lab" />
                                &nbsp;|&nbsp;
                                <input type="button" name="Manage_price" value="Manage"
                                    data-toggle="modal" data-target="#myModal5"
                                    id="<?= $row['sn'] . '__' . $row['test'] ?>"
                                    class="btn btn-info btn-xs manage_price"
                                    <?= ($_SESSION['price'] == 0) ? 'disabled' : '' ?> />
                            </td>
                        </tr>
                    <?php $n++;
                    endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <br>No Records Found
    <?php endif;
}
function combos($sort_by_dept_3)
{
    include("../Connections/Conn.php");

    $query = "SELECT * FROM lab_scan WHERE combo_test = 1 $sort_by_dept_3 ORDER BY sn";
    $stmt = $db->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
    ?>
        <div id="refresh">
            <table class="table table-striped table-bordered table-hover dataTables-example">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Lab Combination Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $n = 1;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $comboId = htmlspecialchars($row["sn"] . '__' . $row["test"]);
                    ?>
                        <tr>
                            <td><?php echo $n++; ?></td>
                            <td><?php echo htmlspecialchars($row["test"]); ?></td>
                            <td>
                                <input type="button"
                                    name="view_combos_list"
                                    value="View / Add Lab Test"
                                    data-target="#myModal5"
                                    id="<?php echo $comboId; ?>"
                                    class="btn btn-info btn-xs view_combos" />
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php
    } else {
        echo '<br>No Combos Available!';
    }
}



function reagent()
{
    include("../Connections/Conn.php");

    $stmt = $db->query("select * from lab_reagent");
    if ($stmt->rowCount() > 0) { ?>
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th data-toggle="true">#</th>
                    <th data-toggle="true">Re-agent</th>
                    <th data-toggle="true">Qty Test Covered</th>
                    <th data-toggle="true">.</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $n = 1;
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($data as $key => $roww) {
                    // while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                        <td><?php echo $roww['sn']; ?></td>
                        <td><?php echo $roww['reagent']; ?></td>
                        <td><?php echo $roww['qty_test']; ?></td>
                        <td><a href="cnsumbl.php?rdel=<?php echo $roww['sn']; ?>">Delete</a></td>
                    </tr>
                <?php
                    $n++;
                } ?>

            </tbody>
        </table>
    <?php }
}




function lab_scan_fields($test_id, $test_id, $test_name, $field)
{

    include("../Connections/Conn.php");

    $rstSelect = $db->query("SELECT * FROM lab_scan_fields WHERE test_no='$test_id' order by sn");
    if ($rstSelect->rowCount() > 0) {
    ?>

        <div id="test_fields">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th data-toggle="true">No</th>
                        <th data-toggle="true">Field</th>
                        <th data-toggle="true">Input Type</th>
                        <th data-toggle="true">Reference</th>
                        <th data-toggle="true">Manage</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $n = 1;
                    while ($roww = $rstSelect->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <tr>
                            <td><?php echo $roww['sn']; ?></td>
                            <td><?php echo $roww['field']; ?></td>
                            <td><?php echo $roww['field_type']; ?></td>
                            <td><?php echo $roww['reference']; ?></td>
                            <td>
                                <?php if ($roww['field_type'] == 'options') { ?>

                                    <input type="button" name="edit" value="View & Add Option" data-toggle="modal" data-target="#myModal5" id="<?php echo $test_id; ?>"
                                        class="btn btn-info btn-xs view_lab_option" />
                                    &nbsp;

                                <?php } elseif ($roww['field_type'] == 'values') { ?>

                                    <input type="button" name="edit" value="View & Add Values Input box" data-toggle="modal" data-target="#myModal5" id="<?php echo $roww["sn"]; ?>"
                                        class="btn btn-info btn-xs view_lab_values" />
                                    &nbsp;

                                <?php } elseif ($roww['field_type'] == 'report') { ?>

                                    <a href="setup.php?template=<?php echo $test_id; ?>" class="btn btn-success btn-xs"> Set Report Template</a>
                                    &nbsp;
                                <?php } else {
                                    echo 'Value';
                                } ?>
                            </td>
                            <td>

                                <input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"] . '__' . $test_id . '__' . $test_name; ?>"
                                    class="btn btn-danger btn-xs lab_scan_fields_del" data-target="#myModal5" />
                            </td>
                        </tr>
                    <?php
                        $n++;
                    } ?>

                </tbody>
            </table>
        <?php } else { ?>
            <br><br><strong>No Item(s) Added To The Lab Test. Click Add Button to Add Items.</strong><br><br>
            <input type="button" name="Add" value="Add" data-target=".slacker-modal" id="<?php echo $field; ?>" class="btn btn-success btn-xs add_fields_items" /> |

    <?php }
}
    ?>