<?php
session_start();
include("../Connections/Conn.php");


$username = $_SESSION['username'];
$stmt2 = $db->query("SELECT voucher_unit FROM admin_users_rights WHERE username='$username'");
if ($stmt2->rowCount() > 0) {
    $row_invst = $stmt2->fetch(PDO::FETCH_ASSOC);
    $_SESSION['voucher_unit'] = $row_invst['voucher_unit'];
}
?>


<!DOCTYPE html>
<html>

<?php include("../inc/header.php");
/// $dept_name=$_SESSION['dept_name'];
$dept_id = $_SESSION['dept_id'];

function containsKeywords($string)
{
    // Convert null to empty string to avoid deprecation warning
    $string = $string ?? '';

    $keywords = array('LB', 'RD', 'PH', 'NS', 'CA', 'RE', 'nursing');
    $pattern = '/' . implode('|', array_map('preg_quote', $keywords)) . '/i';

    return preg_match($pattern, $string) === 1;
}

/* if (containsKeywords($rights)) {
    ///echo "The string contains one of the keywords.";
    $query_my_unit = " and v_center='$dept_id' ";
    if ($_SESSION['voucher_unit'] == 0) {
        header("Location:../logout.php");
    }
} else {
    $query_my_unit = " ";
    if ($_SESSION['voucher'] == 0) {
        header("Location:../logout.php");
    }
} */
?>


<body>

    <div id="wrapper">
<?php
// Ensure session is active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set variables safely
$uname     = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$fullname  = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
$designation = isset($_SESSION['Designation']) ? $_SESSION['Designation'] : '';
$navigate  = isset($_SESSION['navigate']) ? $_SESSION['navigate'] : 'dashboard';

// Staff photo folder
$staff_p = "../staff_photos/"; // Adjust to your real path

// Photo path
$photoFile = $staff_p . 'port_' . $uname . '.jpg';
$photo = file_exists($photoFile) ? $photoFile : '../img/user_avatar_lg.png';
?>

<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">

            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                        <img alt="image" class="img-circle" src="<?php echo $photo; ?>" height="50" width="50">
                    </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear"> 
                            <span class="block m-t-xs"> 
                                <strong class="font-bold"><?php echo $fullname; ?></strong>
                            </span>
                            <span class="text-muted text-xs block">
                                <?php echo $designation; ?> <b class="caret"></b>
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="index.php?profile=<?php echo $uname; ?>">Profile</a></li>
                        <li><a href="mailbox.php">Mailbox</a></li>
                        <li class="divider"></li>
                        <li><a href="../index.php">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">IN+</div>
            </li>

            <li>
                <a href="../<?php echo $navigate; ?>/index.php">
                    <i class="fa fa-dashboard"></i> 
                    <span class="nav-label">Main Dashboard</span>
                </a>
            </li>

            <?php include(__DIR__ . "/../inc/nav_side_profile.php"); ?>

        </ul>
    </div>
</nav>



        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>

            <?php


            if (isset($_POST["gene_btn"])) {

                /// confirmation message here
                $setdate = date("Y-m-d");
                $qty = $_POST['qty'];
                $expire_date = $_POST['expire_date'];

                if ($expire_date >= $setdate) {

                    /// loop ing start here
                    $batch_code = mt_rand(100, 999);

                    $insertSQL = "INSERT INTO vouchers(batch_code, batch_type, qty, used, amount, created_by, description, date_gen, date_expire,v_center) 
              VALUES (:batch_code, :batch_type, :qty, '0', :amt, :created_by, :description, :date_gen, :expire_date,:v_center)";
                    $stmt = $db->prepare($insertSQL);
                    $stmt->bindParam(':batch_code', $batch_code, PDO::PARAM_STR);
                    $stmt->bindParam(':batch_type', $_POST['vtype'], PDO::PARAM_STR);
                    $stmt->bindParam(':qty', $_POST['qty'], PDO::PARAM_INT);
                    $stmt->bindParam(':amt', $_POST['amt'], PDO::PARAM_INT);
                    $stmt->bindParam(':created_by', $_SESSION['fullname'], PDO::PARAM_STR);
                    $stmt->bindParam(':description', $_POST['Description'], PDO::PARAM_STR);
                    $stmt->bindParam(':date_gen', $setdate, PDO::PARAM_STR);
                    $stmt->bindParam(':expire_date', $_POST['expire_date'], PDO::PARAM_STR);
                    $stmt->bindParam(':v_center', $_POST['dept_id'], PDO::PARAM_STR);
                    $stmt->execute();


                    /// generate inventory
                    $qty = $_POST['qty'];
                    $i = 1;
                    while ($i <= $qty) {
                        //for($i=1;$i<count(3);$i++){
                        $rd = rand();
                        $vcode = $rd . $batch_code . $i;
                        $insertSQL = "INSERT INTO vouchers_inventory(voucher_code, batch_code, hospital_no, patient_name, amount, issued_by, issued_date, used_status) 
            VALUES (:voucher_code, :batch_code, '', '', :amt, '', '', '0')";
                        $stmt = $db->prepare($insertSQL);
                        $stmt->bindParam(':voucher_code', $vcode, PDO::PARAM_STR);
                        $stmt->bindParam(':batch_code', $batch_code, PDO::PARAM_STR);
                        $stmt->bindParam(':amt', $_POST['amt'], PDO::PARAM_INT);
                        $stmt->execute();


                        $i++;
                    }
                    //
                    header("location:vchr.php?vchr");
                } else {
                    $err = 1;
                }
            }


if (isset($_GET['dlv'])) {
    $stmt = $db->prepare("SELECT batch_code FROM vouchers_inventory WHERE batch_code=:batch_code AND used_status=1");
    $stmt->bindParam(':batch_code', $_GET['dlv'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $deleteInventory = "DELETE FROM vouchers_inventory WHERE batch_code=:batch_code AND used_status='0'";
        $stmtDeleteInventory = $db->prepare($deleteInventory);
        $stmtDeleteInventory->bindParam(':batch_code', $_GET['dlv'], PDO::PARAM_STR);
        $stmtDeleteInventory->execute();

        $deleteVouchers = "DELETE FROM vouchers WHERE batch_code=:batch_code";
        $stmtDeleteVouchers = $db->prepare($deleteVouchers);
        $stmtDeleteVouchers->bindParam(':batch_code', $_GET['dlv'], PDO::PARAM_STR);
        $stmtDeleteVouchers->execute();
    }
}



?>


            <div class="row">
                <div class="col-lg-4">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Write off Vouchers/Tickets Settings</h5>
                        </div>

                        <div class="ibox-content">

                            <strong style="color:#F00">What to do here ... </strong>
                            <p>Generate vouchers for Write off and authorization codes for Billing Unit</p>
                            <br>

                            <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject">

                                <div class="form_sep">
                                    <label for="reg_input_name" class="req">Quantity <small>(Min:1 & Max:10 per Batch)</small></label>
                                    <input type="number" id="qty" name="qty" class="form-control" data-required="true" min="1" max="10" maxlength="2" required>
                                </div>
                                <!--
                <div class="form_sep">
                <label for="reg_input_name" class="req">Amount/Value (Enter Zero Amount for Write Off)</label>
                <input type="number" id="amt" name="amt" class="form-control" data-required="true" max="1000000" required>
                </div>
-->

                                <input type="hidden" value="0" name="amt">


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

                                <input type="hidden" value="writeoff" name="vtype">


                                <input type="hidden" name="dept_id" id="dept_id" value="<?= $dept_id; ?>">

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

                    $stmt = $db->prepare("SELECT i.*, v.batch_type 
        FROM vouchers_inventory AS i 
        INNER JOIN vouchers AS v ON i.batch_code = v.batch_code 
        WHERE i.batch_code = :batch_code  
        ORDER BY i.sn");
                    $stmt->bindParam(':batch_code', $batch_code, PDO::PARAM_STR);
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
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $amt_ = $amt_ + $row['amount'];

                                    ?>
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
                                    <b>TOTAL AMOUNT: <?= number_format($amt_); ?></b>
                                    <HR>


                                    <a rel="" href="vchr.php?vchr&print=<?php echo $batch_code; ?>" class="btn btn-info btn btn-sm"><i class="fa fa-print"></i>&nbsp;Preview & Print</a> &nbsp; &nbsp; | &nbsp; &nbsp; <a rel="" href="vchr.php?vchr" class="btn btn-deafault btn btn-sm">Cancel</a>

                                <?php } else { ?>
                                    <strong><i>No result(s) found</i></strong>
                                <?php }
                                } elseif (isset($_GET['print'])) {
                                    $voucher = $_GET['print'];
                                    include("../inc/print.php");
                                } else { ?>



                                <form action="vchr.php" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">

                                    <div class="col-md-5">
                                        <div class="ibox float-e-margins">
                                            <label for="reg_input_no" class="req">Set Dates </label><br>
                                            <div class="form_sep" id="">
                                                <div class="input-daterange input-group" id="">
                                                    <input type="date" class="input-sm form-control" name="start" required value="<?php
                                                                                                                                        if (isset($_POST['start']) and $_POST['start'] != '') {
                                                                                                                                            echo $_POST['start'];
                                                                                                                                        } else {
                                                                                                                                            echo '';
                                                                                                                                        } ?> " />
                                                    <span class="input-group-addon">to</span>
                                                    <input type="date" class="input-sm form-control" name="end" required value="<?php
                                                                                                                                if (isset($_POST['end']) and $_POST['end'] != '') {
                                                                                                                                    echo $_POST['end'];
                                                                                                                                } else {
                                                                                                                                    echo '';
                                                                                                                                } ?>" />
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" value="writeoff" name="vchrType">

                                    <div class="col-md-4">
                                        <div class="ibox float-e-margins">
                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">.</label><br>
                                                <button class="btn btn-primary btn btn-sm" type="submit" name="apply_vchr"> <i class="fa fa-check"></i> Apply</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form_sep"></div>
                                </form>


                                <?php

                                $todaydate = date("Y-m-d");
                                    if (isset($_POST['apply_vchr'])) {

                                        //// echo $_POST['apply_vchr'];

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
                                            $search = " date(date_gen)>='$todaydate'";
                                        } else {
                                            $search = " batch_type='$vchrType' and date(date_gen) between '$start' and '$end'";
                                        }

                                        //// echo $search . ' ' .  $query_my_unit;

                                    } else {
                                        $search = " date(date_gen)>='$todaydate'";
                                    }
                                    ?>



                                <?php


                                    $stmt = $db->query("SELECT * FROM vouchers WHERE $search $query_my_unit order by date_gen");
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
                                                $amt_ = 0;
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                            ?>
                                                <tr>
                                                    <td><?php
                                                        echo '<strong>Batch/Code</strong>: ' . $row['batch_code'] . '<br>' .
                                                            '<strong>Batch Type</strong>: ' . $row['batch_type'] . '<br>' .
                                                            '<strong>Quantity</strong>: ' . $row['qty'] . '<br>' .
                                                            '<strong>Qty Used</strong>: ' . $row['used'] .  '<br>' .
                                                            '<strong>Amount</strong>: ' . $row['amount'] .  '<br>' .
                                                            '<strong>Created by</strong>: ' . $row['created_by']; ?>
                                                        <br>
                                                        <a rel="" href="vchr.php?vchr&<?php echo 'dlv=' . $row['batch_code']; ?>">[ Delete ]</a>
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
                                                        <a rel="" href="vchr.php?vchr&b=<?php echo $row['batch_code']; ?>"><?php echo '[View List]' ?></a>
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



            <?php include('../modal_lock.php'); ?>
            <?php include("../inc/footer_scripts.php"); ?>