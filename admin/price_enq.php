<div class="row">

    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Select Price Station</h5>
            </div>

            <div class="ibox-content">

                <div class="form_sep">
                    <label for="reg_select" class="">Category</label>
                    <select name="cat" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="cat" class="form-control" data-required="true">
                        <option selected="selected" value="<?php echo $cat; ?>"><?php echo $category_name; ?></option>
                        <option value="<?php echo 'index.php?enq=Medical Services'; ?>">Medical Services</option>
                        <option value="<?php echo 'index.php?enq=Consultation'; ?>">Consultation</option>
                        <option value="<?php echo 'index.php?enq=Nursing Services'; ?>">Nursing Services</option>
                        <option value="<?php echo 'index.php?enq=Other Services'; ?>">Other Services</option>
                        <option value="<?php echo 'index.php?enq=Investigation'; ?>">Investigation</option>

                    </select>
                </div>
            </div>

        </div>
    </div>




    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Report Panel </h5>
            </div>

            <div class="ibox-content">
                <?php

                if (isset($_GET['enq'])) {
                    $search = $_GET['enq'];
                }

                ?>

                <?php if ($search == 'Investigation') { ?>
                    <strong>Investigations</strong>
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Item Name</th>
                                <th>Private Price</th>
                                <th>HMO Price</th>
                                <th>NHIS Price</th>
                                <th>Coverage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $c = 1;
                            $stmt = $db->query("SELECT * FROM lab_scan order by test,dept ASC");
                            if ($stmt->rowCount() > 0) {

                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                    <tr>
                                        <td><?php echo $c; ?></td>
                                        <td>
                                            <?php echo $row['test'] ?>
                                            <!-- Add button to trigger modal for combo tests -->
                                            <?php if ($row['combo_test'] == 1) : ?>
                                                <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#labComboModal<?php echo $row['sn']; ?>">
                                                    View Tests
                                                </button>

                                                <!-- Modal for this specific lab combo -->
                                                <div class="modal fade" id="labComboModal<?php echo $row['sn']; ?>" tabindex="-1" role="dialog" aria-labelledby="labComboModalLabel<?php echo $row['sn']; ?>">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                <h4 class="modal-title" id="labComboModalLabel<?php echo $row['sn']; ?>">Test Details for <?php echo $row['test']; ?></h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php
                                                                $com_id = $row['sn']; // Assuming 'sn' is the combos_id referenced in the query
                                                                $rstSelect = $db->query("SELECT lab_scan.test, lab_combos_items.sn FROM lab_combos_items 
                                                inner join lab_scan on lab_scan.sn=lab_combos_items.test_id WHERE combos_id='$com_id' order by sn");
                                                                ?>
                                                                <table class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>No</th>
                                                                            <th>Lab Test Name</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        $n = 1;
                                                                        while ($combo_test = $rstSelect->fetch(PDO::FETCH_ASSOC)) {
                                                                        ?>
                                                                            <tr>
                                                                                <td><?php echo $n; ?></td>
                                                                                <td><?php echo $combo_test['test']; ?></td>
                                                                            </tr>
                                                                        <?php
                                                                            $n++;
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif ?>
                                        </td>
                                        <td><?php echo number_format($row['ext_price'], 2, '.', ','); ?></td>
                                        <td><?php echo number_format($row['hosp_price'], 2, '.', ','); ?></td>
                                        <td><?php echo number_format($row['nhis_price'], 2, '.', ','); ?></td>
                                        <td><?php echo $row['coverage']; ?></td>
                                    </tr>
                            <?php
                                    $colordecide++;
                                    $c++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Item Name</th>
                                <th>Private Price</th>
                                <th>HMO Price</th>
                                <th>NHIS Price</th>
                                <th>Coverage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $c = 1;
                            $query_rstSelect2 = $db->query("SELECT * FROM prices_table WHERE price_table='$search' GROUP BY item_service ASC");
                            if ($query_rstSelect2->rowCount() > 0) {

                                while ($row = $query_rstSelect2->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                    <tr>
                                        <td><?php echo $c; ?></td>
                                        <td>
                                            <?php echo $row['item_service'] ?>
                                            <!-- add button to trigger modal for special service list -->
                                            <?php if ($row['special_package'] == 1): ?>
                                                <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#specialServiceModal<?php echo $row['sn']; ?>">
                                                    View Details
                                                </button>

                                                <!-- Modal for this specific service -->
                                                <div class="modal fade" id="specialServiceModal<?php echo $row['sn']; ?>" tabindex="-1" role="dialog" aria-labelledby="specialServiceModalLabel<?php echo $row['sn']; ?>">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                <h4 class="modal-title" id="specialServiceModalLabel<?php echo $row['sn']; ?>">Special Services for <?php echo $row['item_service']; ?></h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php
                                                                $stmtt = $db->prepare("SELECT * FROM special_package WHERE service_table_id=:service_table_id");
                                                                $stmtt->bindParam(':service_table_id', $row['sn'], PDO::PARAM_STR);
                                                                $stmtt->execute();
                                                                ?>
                                                                <table id="resp_table" class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Sn</th>
                                                                            <th>ITEM</th>
                                                                            <th>TIMES(X)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        $n = 1;
                                                                        while ($special_row = $stmtt->fetch(PDO::FETCH_ASSOC)) {
                                                                        ?>
                                                                            <tr>
                                                                                <td><?php echo $n++; ?></td>
                                                                                <td><?php echo $special_row['service_title']; ?></td>
                                                                                <td><?php echo $special_row['duration']; ?></td>
                                                                            </tr>
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($row['ext_price'], 2, '.', ','); ?></td>
                                        <td><?php echo number_format($row['hosp_price'], 2, '.', ','); ?></td>
                                        <td><?php echo number_format($row['nhis_price'], 2, '.', ','); ?></td>
                                        <td><?php echo $row['coverage']; ?></td>
                                    </tr>
                            <?php
                                    $colordecide++;
                                    $c++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>


                <?php
                    //	echo 'Total products: '. mysql_num_rows($rstSelect);
                } ?>



            </div>

        </div>
    </div>

</div>