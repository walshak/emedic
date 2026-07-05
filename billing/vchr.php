<?php
if (isset($_POST["gene_btn"])) {

    /// confirmation message here 
    $setdate = date("Y-m-d");
    $qty = $_POST['qty'];
    $expire_date = $_POST['expire_date'];

    if ($expire_date >= $setdate) {

        /// loop ing start here
        $batch_code = mt_rand(100, 999);
        $zero = '0';

        $insertSQL = "INSERT INTO vouchers(batch_code, batch_type, qty, used, amount, created_by, description, date_gen, date_expire) 
              VALUES (:batch_code, :batch_type, :qty, :used, :amount, :created_by, :description, :date_gen, :date_expire)";

        $stmt = $db->prepare($insertSQL);

        $stmt->bindValue(':batch_code', $batch_code, PDO::PARAM_STR);
        $stmt->bindValue(':batch_type', $_POST['vtype'], PDO::PARAM_STR);
        $stmt->bindValue(':qty', $_POST['qty'], PDO::PARAM_INT); // Assuming qty is an integer
        $stmt->bindValue(':used', $zero, PDO::PARAM_INT); // Assuming used is an integer
        $stmt->bindValue(':amount', $_POST['amt'], PDO::PARAM_STR);
        $stmt->bindValue(':created_by', $_SESSION['fullname'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $_POST['Description'], PDO::PARAM_STR);
        $stmt->bindValue(':date_gen', $setdate, PDO::PARAM_STR); // Assuming $setdate is a string in YYYY-MM-DD format
        $stmt->bindValue(':date_expire', $_POST['expire_date'], PDO::PARAM_STR); // Assuming expire_date is a string in YYYY-MM-DD format

        $stmt->execute();


        /// generate inventory 
        $qty = $_POST['qty'];
        $i = 1;
        while ($i <= $qty) {
            //for($i=1;$i<count(3);$i++){
            $rd = rand();
            $empty = '';
            $zero = 0;
            $vcode = $rd . $batch_code . $i;
            $insertSQL = "INSERT INTO vouchers_inventory(voucher_code, batch_code, hospital_no, patient_name, amount, issued_by, issued_date, used_status) 
            VALUES (:voucher_code, :batch_code, :hospital_no, :patient_name, :amount, :issued_by, :issued_date, :used_status)";

            $stmt = $db->prepare($insertSQL);
            $stmt->bindValue(':voucher_code', $vcode, PDO::PARAM_STR);
            $stmt->bindValue(':batch_code', $batch_code, PDO::PARAM_STR);
            $stmt->bindValue(':hospital_no', $empty, PDO::PARAM_STR); // Assuming ' ' is the default value
            $stmt->bindValue(':patient_name', $empty, PDO::PARAM_STR); // Assuming ' ' is the default value
            $stmt->bindValue(':amount', $_POST['amt'], PDO::PARAM_STR);
            $stmt->bindValue(':issued_by', $empty, PDO::PARAM_STR); // Assuming ' ' is the default value
            $stmt->bindValue(':issued_date', $empty, PDO::PARAM_STR); // Assuming ' ' is the default value
            $stmt->bindValue(':used_status', $zero, PDO::PARAM_INT); // Assuming used_status is an integer

            $stmt->execute();


            $i++;
        }
        //		
        header("location:index.php?vchr");
    } else {
        $err = 1;
    }
}


if (isset($_GET['dlv'])) {

    // Check if there are any used vouchers with the given batch_code
    $stmt = $db->prepare("SELECT batch_code FROM vouchers_inventory WHERE batch_code = :batch_code AND used_status = 1");
    $stmt->bindValue(':batch_code', $_GET["dlv"], PDO::PARAM_STR);
    $stmt->execute();

    // If no used vouchers are found, proceed with deletion
    if ($stmt->rowCount() == 0) {
        // Delete unused vouchers from vouchers_inventory
        $stmt_delete_inventory = $db->prepare("DELETE FROM vouchers_inventory WHERE batch_code = :batch_code AND used_status = '0'");
        $stmt_delete_inventory->bindValue(':batch_code', $_GET["dlv"], PDO::PARAM_STR);
        $stmt_delete_inventory->execute();

        // Delete vouchers from vouchers table
        $stmt_delete_vouchers = $db->prepare("DELETE FROM vouchers WHERE batch_code = :batch_code");
        $stmt_delete_vouchers->bindValue(':batch_code', $_GET["dlv"], PDO::PARAM_STR);
        $stmt_delete_vouchers->execute();
    }
}


?>


<div class="row">
    <div class="col-lg-4">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Discount/Transfer/Refund Settings</h5>
            </div>

            <div class="ibox-content">

                <strong style="color:#F00">What to do here ... </strong>
                <p>Generate vouchers for discount and authorization codes for money transfer between patients and refunds ... </p>
                <br>

                <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject">

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Quantity <small>(Min:1 & Max:10 per Batch)</small></label>
                        <input type="number" id="qty" name="qty" class="form-control" data-required="true" min="1" max="10" maxlength="2" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Amount/Value</label>
                        <input type="number" id="amt" name="amt" class="form-control" data-required="true" max="1000000" required>
                    </div>

                    <div class="form_sep" id="">
                        <label class="font-noraml">Expiration Date</label>
                        <div class="input-group date">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date" class="form-control" name="expire_date" id="expire_date" required>
                        </div>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Description</label>
                        <input type="text" id="Description" name="Description" class="form-control" data-required="true" maxlength="60" required>
                    </div>


                    <div class="form_sep">
                        <label for="reg_select" class="req">Voucher Type</label>
                        <select name="vtype" id="vtype" class="form-control" required>
                            <option selected="selected" value="">Select...</option>
                            <option value="Discount">Discount</option>
                            <option value="Refund">Refund</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>



                    <br /><br />
                    <div class="form_sep">
                        <table>
                            <tr>
                                <td><button type="submit" class="btn btn-success" name="gene_btn" id="gene_btn">Generate</button></td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td></td>
                            </tr>
                        </table>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <div class="col-lg-8">
        <div class="ibox float-e-margins">
            <div class="ibox-title">

                <h5>Voucher Reports</h5>
            </div>
            <div class="ibox-content">


                <?php

                if (isset($_GET['b'])) {

                    $batch_code = $_GET['b'];

                    // Prepare the SQL query using PDO prepared statement
                    $stmt = $db->prepare("SELECT i.*, v.batch_type FROM vouchers_inventory AS i INNER JOIN vouchers AS v ON i.batch_code = v.batch_code WHERE i.batch_code = :batch_code ORDER BY i.sn");
                    $stmt->bindValue(':batch_code', $batch_code, PDO::PARAM_STR); // Bind the parameter
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) { ?>

                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th data-toggle="true">Detail</th>
                                    <th data-toggle="true">Hospital No</th>
                                    <th data-toggle="true">Name</th>
                                    <th data-hide="phone,tablet">Amount</th>
                                    <th data-hide="phone,tablet">.</th>
                                    <th data-hide="phone,tablet">.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //$amt_due=0;$amt_hmo=0;$n=1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td><?php
                                            echo '<strong>Voucher/Code:</strong> ' . $row['voucher_code'] . '<br>';
                                            echo '<strong>Batch/Type:</strong> ' . $row['batch_code'] . ' [ ' . $row['batch_type'] . ' ]';
                                            ?></td>
                                        <td><?php echo $row['hospital_no']; ?></td>
                                        <td><?php echo $row['patient_name']; ?></td>
                                        <td><?php echo $row['amount']; ?></td>
                                        <td><?php
                                            echo '<strong>Issued by:</strong> ' . $row['issued_by'] . '<br>';

                                            if ($row['issued_date'] == '0000-00-00') {
                                                echo '';
                                            } else {
                                                echo '<strong>Issued/Date:</strong> ' . date('d M,Y', strtotime($row['issued_date']));
                                            }
                                            ?></td>
                                        <td><?php if ($row['used_status'] == '1') {
                                                echo 'USED';
                                            } ?></td>
                                    </tr>
                                <?php $n += 1;
                                } ?>
                            </tbody>

                        </table>

                        <a rel="" href="index.php?vchr&print=<?php echo $batch_code; ?>" class="btn btn-info btn-sm">
                            <i class="fa fa-print"></i>&nbsp;Preview & Print
                        </a> &nbsp; &nbsp; | &nbsp; &nbsp;
                        <a rel="" href="index.php?vchr&b=<?php echo $batch_code; ?>" class="btn btn-default btn-sm">Cancel</a>


                    <?php } else { ?>
                        <strong><i>No result(s) found</i></strong>
                    <?php }
                } elseif (isset($_GET['print'])) {
                    $voucher = $_GET['print'];
                    include("../inc/print.php");
                } else { ?>



                    <form action="index.php" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">

                        <div class="col-md-5">
                            <div class="ibox float-e-margins">
                                <label for="reg_input_no" class="">Set Dates </label><br>
                                <div class="form_sep" id="">
                                    <div class="input-daterange input-group" id="">
                                        <input type="date" class="input-sm form-control" name="start" value="<?php
                                                                                                                if (isset($_POST['start']) and $_POST['start'] != '') {
                                                                                                                    echo $_POST['start'];
                                                                                                                } else {
                                                                                                                    echo date("Y-m-d");
                                                                                                                } ?>" />
                                        <span class="input-group-addon">to</span>
                                        <input type="date" class="input-sm form-control" name="end" value="<?php
                                                                                                            if (isset($_POST['end']) and $_POST['end'] != '') {
                                                                                                                echo $_POST['end'];
                                                                                                            } else {
                                                                                                                echo date("Y-m-d");
                                                                                                            } ?>" />
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="ibox float-e-margins">
                                <div class="form_sep">
                                    <label class="req">Vouchers</label>
                                    <select name="vchrType" id="vchrType" class="input-sm form-control">
                                        <option selected="selected" value="">Select...</option>
                                        <option value="Discount">Discount</option>
                                        <option value="Refund">Refund</option>
                                        <option value="Write-off">Write-off</option>
                                        <option value="Transfer">Transfer</option>
                                    </select>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="ibox float-e-margins">
                                <div class="form_sep">
                                    <label for="reg_input_no" class="">.</label><br>
                                    <button class="btn btn-primary btn btn-sm" type="submit" name="apply_vchr"> <i class="fa fa-check"></i> Apply</button>

                                    &nbsp;&nbsp; | &nbsp;&nbsp;

                                    <a href="../admin/index.php" class="btn btn-danger btn btn-sm"> <i class="fa fa-times"></i> &nbsp;Close</a>

                                </div>
                            </div>
                        </div>

                        <div class="form_sep"></div>

                        <?php

                        $todaydate = date("Y-m-d");
                        if (isset($_POST['apply_vchr'])) {

                            if (isset($_POST['vchrType']) and $_POST['vchrType'] == '') {
                                $vchrType = 'all';
                            } else {
                                $vchrType = $_POST['vchrType'];
                            }

                            if (isset($_POST['start']) and $_POST['start'] == '') {
                                $start = date("Y-m-d");
                            } else {
                                $start = $_POST['start'];
                            }

                            if (isset($_POST['end']) and $_POST['end'] == '') {
                                $end = date("Y-m-d");
                            } else {
                                $end = $_POST['end'];
                            }

                            if ($vchrType == 'all') {
                                $search = " date_expire>'$todaydate'";
                            } else {
                                $search = " batch_type='$vchrType' and date_gen between '$start' and '$end'";
                            }
                        } else {
                            $search = " date_expire>'$todaydate'";
                        }
                        ?>



                        <?php
                        $stmt = $db->query("SELECT * FROM vouchers WHERE $search order by date_gen");
                        if ($stmt->rowCount() > 0) { ?>

                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th data-toggle="true">Details</th>
                                        <th data-hide="phone,tablet">Date</th>
                                        <th data-hide="phone,tablet">Description/Center</th>
                                        <th data-hide="phone,tablet">.</th>
                                        <th data-hide="phone,tablet">.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    //$amt_due=0;$amt_hmo=0;$n=1;
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php
                                                echo '<strong>Batch/Code</strong>: ' . $row['batch_code'] . '<br>' .
                                                    '<strong>Batch Type</strong>: ' . $row['batch_type'] . '<br>' .
                                                    '<strong>Quantity</strong>: ' . $row['qty'] . '<br>' .
                                                    '<strong>Qty Used</strong>: ' . $row['used'] .  '<br>' .
                                                    '<strong>Amount</strong>: ' . $row['amount'] .  '<br>' .
                                                    '<strong>Created by</strong>: ' . $row['created_by']; ?>
                                                <br>
                                                <a rel="" href="index.php?vchr&<?php echo 'dlv=' . $row['batch_code']; ?>">[ Delete ]</a>
                                            </td>
                                            <td><?php echo 'Created Date: <br>' . date('d M,Y', strtotime($row['date_gen']));
                                                echo '<br>Expire Date: <br>' . date('d M,Y', strtotime($row['date_expire'])); ?></td>
                                            <td><?php echo $row['description'] . '<br>' . '<strong>Center</strong>:' . $row['v_center']; ?></td>
                                            <td>
                                                <?php
                                                $setdate = date("Y-m-d");
                                                $expire_date = $row['date_expire'];
                                                if ($setdate <= $expire_date) { ?>
                                                    <div style="background:#06F; color:#FFF"><strong>Valid</strong></div>


                                                <?php } else { ?>
                                                    <div style="background:#F00; color:#FFF"><strong>Expired</strong></div>
                                                <?php }
                                                ?>
                                            </td>
                                            <td>
                                                <a rel="" href="index.php?vchr&b=<?php echo $row['batch_code']; ?>"><?php echo '[View List]' ?></a>
                                            </td>
                                        </tr>
                                    <?php $n += 1;
                                    } ?>
                                </tbody>
                            </table>
                            <strong><i><?php echo $stmt->rowCount(); ?> result(s) found</i></strong>
                        <?php  } else { ?>

                            <strong>No Records to show</strong>
                        <?php } ?>


                    <?php } ?>
            </div>
        </div>
    </div>
</div>