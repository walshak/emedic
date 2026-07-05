<?php
session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");
?>
<!DOCTYPE html>
<html>

<?php

if (isset($_POST["remove_amount_deposit"])) {

    $emr = $_POST["hospital_no_delete_post"];
    $dr_amt_delete = $_POST["dr_amt_delete"];
    $convert_transaction_id = $_POST["convert_transaction_id"];
    $lg_ref_no = $_POST["lg_ref_no"];
    $paymethod_ = $_POST["paymethod_"];
    $desc = "Trnx to Deposit: " . $dr_amt_delete;

    $general_credit_limit =    $_SESSION['credit_limit_status'];
    $items = call_current_balance($db, $emr, $general_credit_limit);

    $current_balance =  $items["current_balance"];
    $patient_name      = $items['patient_name'];
    $names             = $items['names'];
    $referral_name     = $items['referral_name'];
    $discount_set      = $items['discount_set'];
    $insurance_type    = $items['insurance_type'];
    $insurance_no      = $items['insurance_no'];
    $save_insurance_no = $items['save_insurance_no'];
    $wallet_amount     = $items['wallet_amount'];

    try {
        $db->beginTransaction();
        $stmt_del_status = null;
        $stmt = $db->query("SELECT lg_ref_no FROM chart_ledger WHERE lg_ref_no ='$lg_ref_no'");

        if ($stmt->rowCount() > 0 && $current_balance >= $dr_amt_delete) {
            $stmt_del = $db->prepare("DELETE FROM chart_ledger WHERE lg_ref_no=:lg_ref_no AND ref_value=:paymethod_");
            $stmt_del->bindParam(':lg_ref_no', $lg_ref_no, PDO::PARAM_STR);
            $stmt_del->bindParam(':paymethod_', $paymethod_, PDO::PARAM_STR);
            $stmt_del->execute();

            if ($stmt_del->rowCount() > 0) {
                $stmt_del_status = "Transaction to Deposit successful!";
            } else {
                $stmt_del_status = "Delete failed.";
            }

            $action_to_take = 'Trnx to Deposit Deleted';
            $setdatetime = date("Y-m-d H:i:s");
            $sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,action,date_and_time) 
                VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:action,:date_and_time)");
            $sql->bindParam(':item_sn', $lg_ref_no, PDO::PARAM_STR);
            $sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
            $sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
            $sql->bindParam(':patient_id', $emr, PDO::PARAM_STR);
            $sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
            $sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
            $sql->execute();
        } else {

            $stmt_del_status = "Insufficient balance or Multiple transactions posted on the deposit.";
        }

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        echo 'Error: ' . $e->getMessage();
    }
}



if (isset($_POST["delete_wrong_posting_deposit"])) {

    $emr = $_POST["hospital_no_delete_post"];
    $dr_amt_delete = $_POST["dr_amt_delete"];
    $convert_transaction_id = $_POST["convert_transaction_id"];
    $lg_ref_no = $_POST["lg_ref_no"];

    $general_credit_limit =    $_SESSION['credit_limit_status'];
    $items = call_current_balance($db, $emr, $general_credit_limit);

    $current_balance =  $items["current_balance"];
    $patient_name      = $items['patient_name'];
    $names             = $items['names'];
    $referral_name     = $items['referral_name'];
    $discount_set      = $items['discount_set'];
    $insurance_type    = $items['insurance_type'];
    $insurance_no      = $items['insurance_no'];
    $save_insurance_no = $items['save_insurance_no'];
    $wallet_amount     = $items['wallet_amount'];


    $desc = "Delete Deposit: " . $dr_amt_delete;
    try {
        $db->beginTransaction();
        $stmt_del_status = null;
        $stmt = $db->query("SELECT lg_ref_no FROM chart_ledger WHERE lg_ref_no ='$lg_ref_no'");

        if ($stmt->rowCount() == 2 && $current_balance >= $dr_amt_delete) {
            $stmt_del = $db->prepare("DELETE FROM chart_ledger WHERE lg_ref_no=:lg_ref_no");
            $stmt_del->bindParam(':lg_ref_no', $lg_ref_no, PDO::PARAM_STR);
            $stmt_del->execute();

            if ($stmt_del->rowCount() > 0) {
                $stmt_del_status = "Delete successful!";
            } else {
                $stmt_del_status = "Delete failed.";
            }

            $action_to_take = 'Deposit Deleted';
            $setdatetime = date("Y-m-d H:i:s");
            $sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,action,date_and_time) 
                VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:action,:date_and_time)");
            $sql->bindParam(':item_sn', $lg_ref_no, PDO::PARAM_STR);
            $sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
            $sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
            $sql->bindParam(':patient_id', $emr, PDO::PARAM_STR);
            $sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
            $sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
            $sql->execute();
        } else {

            $stmt_del_status = "Insufficient balance or Multiple transactions posted on the deposit.";
        }

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        echo 'Error: ' . $e->getMessage();
    }

    //    echo 'sdsssssssssssssssss';
}


if (isset($_POST["save_transaction"])) {
    $bank_name = $_POST["bank_name"];
    $paymethod = $_POST["paymethod"];


    if ($paymethod != 'cash' and $bank_name == '') {
        $go_status = 1;
        header('location:transc.php?err_bank');
    } elseif ($paymethod == 'cash') {
        $go_status = 0;
        $bank_name = '';
    }
    if ($go_status == 0) {
        $convert_transaction_id = $_POST["convert_transaction_id"];

        if ($paymethod == 'cash') {
            $stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='Main Cash' and account_group ='5'");
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $debit_gl_account = $row['account_code'];
            } else {
                $debit_gl_account = '';
            }
        } else {
            $stmt = $db->query("SELECT account_code FROM chart_accounts 
						WHERE account_name='$bank_name'  and account_group ='5'");  /// get bank name
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $debit_gl_account = $row['account_code'];
            } else {
                $debit_gl_account = '';
            }
        }
        //echo $debit_gl_account;
        //exit;


        if ($debit_gl_account != '') {
            $stmt2 = $db->prepare("UPDATE chart_ledger 
                                   SET ref_value = :paymethod, 
                                       bank_name = :bank_name, 
                                       account_no = :debit_gl_account 
                                   WHERE sn = :convert_transaction_id");

            $stmt2->bindValue(':paymethod', $paymethod, PDO::PARAM_STR);
            $stmt2->bindValue(':bank_name', $bank_name, PDO::PARAM_STR);
            $stmt2->bindValue(':debit_gl_account', $debit_gl_account, PDO::PARAM_STR);
            $stmt2->bindValue(':convert_transaction_id', $convert_transaction_id, PDO::PARAM_STR);

            $stmt2->execute();

            header('location: transc.php?tr_updated');
        } else {
            header('location: transc.php?err_bank');
        }
    }

    //

}


if (isset($_GET['pos'])) {
    $pos = $_GET['pos'];
    $desc = 'Amount Paid/Convert to POS';

    $stmt2 = $db->prepare("UPDATE chart_ledger 
                           SET ref_value = :ref_value, 
                               item_services = :item_services 
                           WHERE sn = :pos");

    $stmt2->bindValue(':ref_value', 'POS', PDO::PARAM_STR);
    $stmt2->bindValue(':item_services', $desc, PDO::PARAM_STR);
    $stmt2->bindValue(':pos', $pos, PDO::PARAM_INT); // Assuming $pos is an integer

    $stmt2->execute();
}


?>


<?php include("../inc/header.php"); ?>
<?php
$payment_domain = "(p.serv_group='Laboratory' or p.serv_group='Radiology')";
$setdate = date("Y-m-d");

$responsible = $_SESSION['fullname'];
$setdate = date("Y-m-d");
$year = date("Y");
$patient_acct_status = 1;
?>

</head>

<body class="fixed-navigation">

    <div id="wrapper">
        <?php include("../inc/nav_side_bill.php"); ?>

        <div id="page-wrapper" class="gray-bg sidebar-content">
            <?php include("../inc/nav_header.php"); ?>

            <?php include("../inc/billing_side_bar.php"); ?>

            <div class="wrapper wrapper-content">



                <div class="row">
                    <div class="col-md-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right">Today</span>
                                <h5>Patient(s) Seen</h5>
                            </div>
                            <div class="ibox-content">
                                <h1 class="no-margins">

                                    <?php
                                    $stmt = $db->query("SELECT distinct(hospital_no) FROM chart_ledger 
where prepared_by='$responsible' and date(date_entry)='$setdate'");
                                    echo $stmt->rowCount();
                                    ?>
                                </h1>
                                <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                                <small>Total Patient(s)</small>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-5">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="pull-right"><a href="transc.php">See Details </a></span>
                                <h5>Money Recieved & Sales</h5>
                            </div>
                            <div class="ibox-content">

                                <div class="row">
                                    <div class="col-md-6">
                                        <h1 class="no-margins">
                                            <?php
                                            $stmt2 = $db->query("SELECT sum(a.dr_amt) as dr FROM chart_ledger as a 
	inner join chart_accounts as aa on aa.account_code = a.account_no
	inner join chart_groups as g on g.id = aa.account_group
    where a.prepared_by='$responsible' and date(a.date_entry)='$setdate' and g.class_id = '1'");
                                            $row_dr = $stmt2->fetch(PDO::FETCH_ASSOC);
                                            echo number_format($row_dr['dr']);


                                            ?>
                                        </h1>
                                        <small>Total Transactions by you</small>

                                    </div>

                                    <div class="col-md-6">
                                        <h1 class="no-margins">
                                            <?php
                                            try {
                                                $stmt2 = $db->query("SELECT sum(a.cr_amt) as cr FROM chart_ledger as a 
	inner join chart_accounts as aa on aa.account_code = a.account_no
	inner join chart_groups as g on g.id = aa.account_group
    where a.prepared_by='$responsible' and date(a.date_entry)='$setdate' and g.class_id = '4'");

                                                if ($stmt2 !== false) {
                                                    $row_cr = $stmt2->fetch(PDO::FETCH_ASSOC);
                                                    if ($row_cr !== false) {
                                                        echo number_format($row_cr['cr']);
                                                    } else {
                                                        echo "No data found.";
                                                    }
                                                } else {
                                                    echo "Error executing query.";
                                                }
                                            } catch (PDOException $e) {
                                                echo "Database Error: " . $e->getMessage();
                                            }
                                            ?>
                                        </h1>
                                        <small>Total Sales</small>

                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right">As At Today</span>
                                <h5>Discount/Charges</h5>
                            </div>
                            <div class="ibox-content">

                                <div class="row">
                                    <div class="col-md-6">
                                        <h1 class="no-margins">
                                            <?php
                                            $stmt5 = $db->query("SELECT sum(p.discount) as discount FROM patient_ap_services as p 
inner join chart_ledger as b on b.sale_sn=p.sn where p.discount>0 and b.prepared_by='$responsible' and date(p.transact_date)='$setdate'");
                                            $dsc_rwx = $stmt5->fetch(PDO::FETCH_ASSOC);
                                            if ($stmt5->rowCount() > 0) {
                                                echo number_format($dsc_rwx['discount']);
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </h1>
                                        <small>Total Discount</small>

                                    </div>

                                    <div class="col-md-6">
                                        <h1 class="no-margins">
                                            <?php
                                            $stmt6 = $db->query("SELECT sum(p.add_charge) as add_charge FROM patient_ap_services as p 
inner join chart_ledger as b on b.sale_sn=p.sn where p.add_charge>0 and b.prepared_by='$responsible' and date(p.transact_date)='$setdate'");
                                            $dsc_rwx = $stmt6->fetch(PDO::FETCH_ASSOC);
                                            if ($stmt6->rowCount() > 0) {
                                                echo number_format($dsc_rwx['add_charge']);
                                            } else {
                                                echo '0';
                                            }
                                            ?>

                                        </h1>
                                        <small>Total Extra-Charge</small>

                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Specify Report Setting</h5>
                            </div>

                            <div class="ibox-content">
                                <div class="row">

                                    <?php if (isset($_GET['err_bank'])): ?>
                                        <div class="alert alert-danger"><strong>Invalid Bank Name</strong></div>
                                    <?php endif; ?>

                                    <?php if (isset($_GET['tr_updated'])): ?>
                                        <div class="alert alert-success"><strong>Transaction Updated</strong></div>
                                    <?php endif; ?>

                                    <?php if (isset($_POST['delete_wrong_posting_deposit']) || isset($_POST['remove_amount_deposit'])): ?>
                                        <div class="alert alert-success"><strong><?= isset($stmt_del_status) ? $stmt_del_status : ''; ?></strong></div>
                                    <?php endif; ?>

                                    <form action="transc.php" method="post">
                                        <!-- Report Type -->
                                        <div class="col-md-4">
                                            <div class="form_sep">
                                                <label class="req">Report Type</label>
                                                <select name="rpt_type" id="rpt_type" class="form-control" required>
                                                    <option value="">Select...</option>
                                                    <?php
                                                    $types = [
                                                        'cash_pos' => 'Cash, POS, Transfer & Refunds',
                                                        'transc' => 'All Transactions',
                                                        'bill_to_acct' => 'Billed to Account',
                                                        'inv_g' => 'Invoice Generated',
                                                        'logs' => 'Logs',
                                                        'discount_charge' => 'Discount / Extra-Charges Reports',
                                                        'p_c' => 'Patients Booked for Appointment'
                                                    ];
                                                    foreach ($types as $key => $label) {
                                                        $selected = (isset($_POST['rpt_type']) && $_POST['rpt_type'] == $key) ? 'selected' : '';
                                                        echo "<option value='$key' $selected>$label</option>";
                                                    }
                                                    ?>
                                                </select>

                                                <label>Payment Method</label>
                                                <select name="payment_method" id="payment_method" class="form-control">
                                                    <option value="">Select...</option>
                                                    <option value="cash" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                                                    <option value="POS" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'POS') ? 'selected' : ''; ?>>POS</option>
                                                    <option value="Pay_by_transfer" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Pay_by_transfer') ? 'selected' : ''; ?>>Transfer</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Bank + Date Range + Time Range -->
                                        <div class="col-md-4">
                                            <div class="form_sep">
                                                <label>Receiving Bank Name</label>
                                                <select name="bank_name" id="bank_name" class="form-control">
                                                    <option value="">Select...</option>
                                                    <?php
                                                    $stmt_bnk = $db->query("SELECT DISTINCT bank_name FROM chart_ledger WHERE bank_name != '' ORDER BY bank_name");
                                                    while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) {
                                                        $selected = (isset($_POST['bank_name']) && $_POST['bank_name'] == $row_rstbank['bank_name']) ? 'selected' : '';
                                                        echo "<option value='{$row_rstbank['bank_name']}' $selected>{$row_rstbank['bank_name']}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <!-- Date + Time Filter -->
                                            <div class="form-group">
                                                <label class="req">Date & Time Range</label>
                                                <div class="input-daterange input-group">
                                                    <input type="date" class="input-sm form-control" name="start"
                                                        value="<?= isset($_POST['start']) ? $_POST['start'] : date('Y-m-d'); ?>" />
                                                    <span class="input-group-addon">to</span>
                                                    <input type="date" class="input-sm form-control" name="end"
                                                        value="<?= isset($_POST['end']) ? $_POST['end'] : date('Y-m-d'); ?>" />
                                                </div>

                                                <br>

                                                <label>Time Range</label>
                                                <select name="time_range" id="time_range" class="form-control" onchange="toggleCustomTime(this.value)">
                                                    <option value="">All Day</option>
                                                    <option value="morning" <?= (isset($_POST['time_range']) && $_POST['time_range'] == 'morning') ? 'selected' : ''; ?>>Morning (6:00 AM - 11:59 AM)</option>
                                                    <option value="afternoon" <?= (isset($_POST['time_range']) && $_POST['time_range'] == 'afternoon') ? 'selected' : ''; ?>>Afternoon (12:00 PM - 5:59 PM)</option>
                                                    <option value="night" <?= (isset($_POST['time_range']) && $_POST['time_range'] == 'night') ? 'selected' : ''; ?>>Night (6:00 PM - 5:59 AM)</option>
                                                    <option value="custom" <?= (isset($_POST['time_range']) && $_POST['time_range'] == 'custom') ? 'selected' : ''; ?>>Custom Time Range</option>
                                                </select>

                                                <div id="custom_time_div" style="margin-top:8px; display:<?= (isset($_POST['time_range']) && $_POST['time_range'] == 'custom') ? 'block' : 'none'; ?>">
                                                    <div class="input-group">
                                                        <input type="time" name="time_start" class="form-control input-sm"
                                                            value="<?= isset($_POST['time_start']) ? $_POST['time_start'] : ''; ?>">
                                                        <span class="input-group-addon">to</span>
                                                        <input type="time" name="time_end" class="form-control input-sm"
                                                            value="<?= isset($_POST['time_end']) ? $_POST['time_end'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="col-md-4">
                                            <div class="form_sep">
                                                <label>&nbsp;</label><br>
                                                <button class="btn btn-success btn-sm" type="submit" name="apply_task2">Apply</button>
                                                <a href="transc.php" class="btn btn-primary btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
                                                <a href="index.php" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Close</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleCustomTime(value) {
                            document.getElementById('custom_time_div').style.display = (value === 'custom') ? 'block' : 'none';
                        }
                    </script>



                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">

                                <h5>Data Display</h5>
                            </div>
                            <div class="ibox-content">

                                <?php
                                $cash_amt = 0;
                                $ref_value = null;
                                $bank_name = null;


                                if (isset($_POST["apply_task2"]) || isset($_POST["save_transaction"])) {

                                    if (isset($_POST["save_transaction"])) {
                                        $start = $end = date('Y-m-d');
                                        $rpt_type = 'cash_pos';
                                    } else {
                                        $start = trim($_POST['start']);
                                        $end = trim($_POST['end']);
                                        $rpt_type = $_POST['rpt_type'];
                                    }

                                    // Default full-day time
                                    $time_range = isset($_POST['time_range']) ? $_POST['time_range'] : '';
                                    $time_start = "00:00:00";
                                    $time_end = "23:59:59";

                                    // Adjust based on time range selection
                                    if ($time_range == 'morning') {
                                        $time_start = "06:00:00";
                                        $time_end = "11:59:59";
                                    } elseif ($time_range == 'afternoon') {
                                        $time_start = "12:00:00";
                                        $time_end = "17:59:59";
                                    } elseif ($time_range == 'night') {
                                        $time_start = "18:00:00";
                                        $time_end = "05:59:59"; // handle overnight logic below
                                    } elseif ($time_range == 'custom') {
                                        $time_start = !empty($_POST['time_start']) ? $_POST['time_start'] . ":00" : "00:00:00";
                                        $time_end = !empty($_POST['time_end']) ? $_POST['time_end'] . ":00" : "23:59:59";
                                    }

                                    // Handle night shift spanning 2 dates (6 PM - 5:59 AM next day)
                                    if ($time_range == 'night') {
                                        $setdates = "( (date_entry BETWEEN '$start 18:00:00' AND '$end 23:59:59') 
                    OR (date_entry BETWEEN DATE_ADD('$start', INTERVAL 1 DAY) AND '$end 05:59:59') )";
                                    } else {
                                        $setdates = "date_entry BETWEEN '$start $time_start' AND '$end $time_end'";
                                    }

                                    // Also define alternative date columns if needed
                                    $setdates2 = str_replace("date_entry2", "date_entry", $setdates);
                                    $setdates3 = str_replace("date_entry2", "date_ap", $setdates);

                                    $period = date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));

                                    // Optional filters
                                    $ref_value_search = (!empty($_POST["payment_method"])) ? " AND ref_value = '" . $_POST["payment_method"] . "'" : '';
                                    $bank_name_search = (!empty($_POST["bank_name"])) ? " AND bank_name = '" . $_POST["bank_name"] . "'" : '';
                                }



                                if ($rpt_type == 'transc' || $rpt_type == 'cash_pos') { ?>

                                    <?php if ($rpt_type == 'cash_pos') {

                                        //////AND (b.insurance_no IS NULL OR b.insurance_no = '') 
                                    ?>
                                        <div class="alert alert-info"><strong>Cash Transaction(s) Collected By: <?php echo $_SESSION['fullname'] . ' // Period: ' . $period . ' //Bank ' . $bank_name . ' //<strong>Type: </strong> ' . $ref_value;  ?></strong></div>
                                    <?php
                                        $prepared_by = $_SESSION['fullname'];


                                        $cash = 'cash';
                                        $POS = 'POS';
                                        $Refund = 'Refund';
                                        $Transfer = 'Transfer';
                                        $Pay_by_transfer = 'Pay_by_transfer';
                                        // Prepare the SQL query
                                        $sql = "SELECT b.*, 
               COALESCE(e.surname, '') AS surname,
               COALESCE(e.oname, '') AS oname,
               COALESCE(e.fname, '') AS fname
        FROM chart_ledger AS b
        LEFT JOIN enrollee AS e ON e.hospital_no = b.hospital_no
        WHERE b.hospital_no IS NOT NULL 
          AND (ref_value IN (:ref_value1, :ref_value2, :ref_value3, :ref_value4, :ref_value5))
          AND prepared_by = :prepared_by 
          AND transc_type = 'DEBIT' 
          AND $setdates $ref_value_search $bank_name_search 
        ORDER BY date_entry";
                                        // Prepare the statement
                                        $stmt = $db->prepare($sql);

                                        // Bind parameters
                                        $stmt->bindValue(':ref_value1', $cash, PDO::PARAM_STR);
                                        $stmt->bindValue(':ref_value2', $POS, PDO::PARAM_STR);
                                        $stmt->bindValue(':ref_value3', $Refund, PDO::PARAM_STR);
                                        $stmt->bindValue(':ref_value4', $Transfer, PDO::PARAM_STR);
                                        $stmt->bindValue(':ref_value5', $Pay_by_transfer, PDO::PARAM_STR);
                                        $stmt->bindValue(':prepared_by', $prepared_by, PDO::PARAM_STR);
                                        $stmt->execute();
                                    }

                                    if ($rpt_type == 'transc') {
                                        /// echo $rpt_type;

                                    ?>
                                        <div class="alert alert-info"><strong>Report of Transaction(s) By: <?php echo $_SESSION['fullname'] . ' // Period: ' . $period . ' //Bank ' . $bank_name . ' //<strong>Type: </strong> ' . $ref_value; ?></strong></div>
                                    <?php
                                        $prepared_by = $_SESSION['fullname'];
                                        $sql = "SELECT b.*, 
               COALESCE(e.surname, '') AS surname,
               COALESCE(e.oname, '') AS oname,
               COALESCE(e.fname, '') AS fname
        FROM chart_ledger AS b
        LEFT JOIN enrollee AS e ON e.hospital_no = b.hospital_no
        WHERE prepared_by = :prepared_by 
          AND $setdates $ref_value_search $bank_name_search 
        ORDER BY date_entry";

                                        $stmt = $db->prepare($sql);
                                        $stmt->bindValue(':prepared_by', $prepared_by, PDO::PARAM_STR);
                                        $stmt->execute();
                                    }

                                    if ($stmt->rowCount() > 0) { ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Hospital #</th>
                                                        <th>Name</th>
                                                        <th>Description</th>
                                                        <th>Method</th>
                                                        <th><!--DR-->CREDIT(DEPOSIT)</th>
                                                        <th><!--CR-->DEBIT(DEDUCT)</th>
                                                        <th>Value Date</th>
                                                        <th>.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php $n = 1;
                                                    $dr_amt = 0;
                                                    $Refund = 0;
                                                    $pos = 0;
                                                    $transfer = 0;
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $payment = '';
                                                        if (strtoupper($row['ref_value']) == 'CASH') {
                                                            $cash_amt = $cash_amt + $row['dr_amt'];
                                                            // echo 'Cash';
                                                        }
                                                        if (strtoupper($row['ref_value']) == 'POS') {
                                                            $pos = $pos + $row['dr_amt'];
                                                            // echo 'POS';
                                                        }
                                                        if (strtoupper($row['ref_value']) == 'TRANSFER' or   strtoupper($row['ref_value']) == 'PAY_BY_TRANSFER') {
                                                            $transfer = $transfer + $row['dr_amt'];
                                                            // echo 'Transfer';
                                                        }
                                                        if (strtoupper($row['ref_value']) == 'REFUND') {
                                                            $Refund = $Refund + $row['dr_amt'];
                                                        }
                                                    ?>
                                                        <tr class="record">
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $row['hospital_no']; ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $row['fname'] . ' ' . $row['oname'] . ' ' . $row['surname']; ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?>
                                                            </td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php

                                                                                                        if (strtoupper($row['ref_value']) == 'CASH') {
                                                                                                            $payment = '<strong>CASH</strong>';
                                                                                                        } elseif (strtoupper($row['ref_value']) == 'TRANSFER') {
                                                                                                            $payment = '<strong>Transfer: </strong>' . $row['bank_name'];
                                                                                                        } elseif (strtoupper($row['ref_value']) == 'POS') {
                                                                                                            $payment = '<strong>POS: </strong>' . $row['bank_name'];
                                                                                                        }

                                                                                                        if ($row['dr_amt'] > 0) {
                                                                                                            echo $payment;
                                                                                                        }
                                                                                                        ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['dr_amt'], 2, '.', ','); ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['cr_amt'], 2, '.', ','); ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;"><?php echo date('d, M Y h:i:m a', strtotime($row['date_entry'])); ?></td>
                                                            <td style="border-bottom: 1px solid #ddd;">

                                                                <?php


                                                                date_default_timezone_set('Africa/Lagos');
                                                                $CurDateHR = date('Y-m-d');
                                                                $date1 = new DateTime($CurDateHR);
                                                                $date2 = new DateTime($row['date_entry']);
                                                                $diff = $date2->diff($date1);
                                                                $d = $diff->format('%a');
                                                                if ($d <= 7) {
                                                                    if ($rpt_type == 'cash_pos' and ($row['ref_value'] == 'cash' or $row['ref_value'] == 'POS' or $row['ref_value'] == 'Transfer' or $row['ref_value'] == 'Pay_by_transfer')) { ?>
                                                                        <input type="button" name="edit_price" value="Convert/Delete Pay" data-target="#modal" id="<?php echo $row['sn']; ?>"
                                                                            class="btn btn-danger btn-xs convert_entry" />
                                                                    <?php } ?>
                                                                <?php } ?>

                                                            </td>
                                                        </tr>

                                                    <?php
                                                        $n++;
                                                    }
                                                    ?>
                                                </tbody>
                                                <tfoot class="hide-if-no-paging">
                                                    <tr>
                                                        <td colspan="6" class="text-center">
                                                            <ul class="pagination pagination-sm"></ul>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <div><b><i><?php echo 'Total row(s) found: ' . $stmt->rowCount(); ?></i></b></div>
                                            <div align="right"><b><i><?php echo 'Period: ' . $period; ?></i></b></div>

                                            <div align="right"><b><i><?php echo 'Total Transactions: ' ?></i></b></div>
                                            <br>


                                            <table width="100%">
                                                <tr>
                                                    <td>
                                                        <div align="right"><b><i><?php echo 'Cash:'; ?></i></b></div>
                                                        <div align="right" style="font-size:18px"><strong><?php echo number_format($cash_amt, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                    </td>
                                                    <td>
                                                        <div align="right"><b><i><?php echo 'POS:'; ?></i></b></div>
                                                        <div align="right" style="font-size:18px"><strong><?php echo number_format($pos, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                    </td>

                                                    <td>
                                                        <div align="right"><b><i><?php echo 'Transfered:'; ?></i></b></div>
                                                        <div align="right" style="font-size:18px"><strong><?php echo number_format($transfer, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                    </td>
                                                    <td>
                                                        <div align="right"><b><i><?php echo 'Refunds:'; ?></i></b></div>
                                                        <div align="right" style="font-size:18px"><strong><?php echo number_format($Refund, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <hr>

                                            <?php $amount_transc = ($cash_amt + $pos + $transfer) - $Refund;    ?>
                                            <div align="right"><b><i><?php echo 'Actual Cash/POS:'; ?></i></b></div>
                                            <div align="right" style="font-size:18px"><strong><?php echo number_format($amount_transc, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>




                                        </div>

                                        <?php

                                        $prepared_by = $_SESSION['fullname'];

                                        if ($transfer > 0 or $pos > 0) {

                                            $bank_total = 0;

                                            $stmt_master = $db->query("SELECT distinct bank_name FROM chart_ledger 
		  		where prepared_by ='$prepared_by' and $setdates and  (ref_value='Transfer' or ref_value='Pay_by_transfer' or ref_value='POS')  and bank_name!=''");
                                            if ($stmt_master->rowCount() > 0) { ?>
                                                <h2>Bank Transfered/POS Report</h2>
                                                <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;">
                                                    <tr>
                                                        <th><strong>#</strong></th>
                                                        <th><strong>Bank</strong></th>
                                                        <th><strong>Total Amount</strong></th>
                                                    </tr>
                                                    <?php
                                                    $n = 1;
                                                    while ($rww = $stmt_master->fetch(PDO::FETCH_ASSOC)) {
                                                        $bank_name = $rww['bank_name'];

                                                    ?>

                                                        <tr>
                                                            <td><?= $n; ?></td>
                                                            <td><strong><?= $bank_name; ?></strong></td>
                                                            <td>
                                                                <h4> <?php
                                                                        $stmt_masterx = $db->query("SELECT sum(dr_amt) as g_total FROM chart_ledger 
		  		where prepared_by ='$prepared_by' and $setdates and bank_name='$bank_name' and (ref_value='Transfer' or ref_value='Pay_by_transfer' or ref_value='POS') and prepared_by ='$prepared_by'");
                                                                        $rww_2 = $stmt_masterx->fetch(PDO::FETCH_ASSOC);
                                                                        echo number_format($rww_2['g_total']);

                                                                        $bank_total = $bank_total +  $rww_2['g_total'];

                                                                        ?></h4>
                                                            </td>

                                                        </tr>
                                                    <?php $n++;
                                                    } ?>

                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <h2>Total Amount:</h2>
                                                        </td>
                                                        <td>
                                                            <h2><?php echo number_format($bank_total); ?></h2>
                                                        </td>
                                                    </tr>
                                                </table>
                                        <?php }
                                        }

                                        ?>

                                <?php } else {
                                        echo '<strong>No Record Found</strong>';
                                    }
                                }

                                ?>





                                <?php if ($rpt_type == 'inv_g' or $rpt_type == 'discount_charge') {

                                    $fullname = $_SESSION['fullname'];
                                    if ($rpt_type == 'inv_g') {
                                        $search = "invoice_status='1' and paystatus='0' and  prepared_by='$fullname' and ";
                                        $setdates2 = "date(date_entry) between '$start' and '$end'";
                                    }
                                    if ($rpt_type == 'discount_charge') {
                                        $search = "(discount>0 or add_charge>0) and ";
                                        $setdates2 = "transact_date between '$start' and '$end'";
                                    }


                                ?>
                                    <div class="alert alert-info"><strong>Invoice Generated By: <?php echo $_SESSION['fullname']; ?></strong></div>
                                    <?php
                                    $stmt = $db->query("SELECT a.*,e.surname,e.oname,e.fname FROM patient_ap_services as a 
	inner join enrollee as e on e.hospital_no = a.hospital_no where $search $setdates2 order by date_entry");
                                    if ($stmt->rowCount() > 0) { ?>

                                        <table class="table table-striped table-bordered table-hover dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Hospital #</th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th>Claim</th>
                                                    <th>Pay</th>
                                                    <th>DSC/CHR</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php $n = 1;
                                                $claim_amt = 0;
                                                $Refund = 0;
                                                $pos = 0;
                                                $T_discount = 0;
                                                $T_add_charge = 0;
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $claim_amt = $claim_amt + $row['claim_amt'];
                                                    $pay = $pay + $row['pay'];
                                                    $T_discount = $T_discount + $row['discount'];
                                                    $T_add_charge = $T_add_charge + $row['add_charge'];
                                                ?>
                                                    <tr class="record">
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['hospital_no']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['fname'] . ' ' . $row['oname'] . ' ' . $row['surname']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['claim_amt']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['pay']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php
                                                                                                    if ($row['discount'] > 0) {
                                                                                                        echo 'DSC: ' . $row['discount'];
                                                                                                    }
                                                                                                    if ($row['add_charge'] > 0) {
                                                                                                        echo 'CHR: ' . $row['add_charge'];
                                                                                                    }
                                                                                                    ?></td>

                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo date('d, M Y h:i:m a', strtotime($row['date_entry'])); ?></td>
                                                    </tr>

                                                <?php
                                                    $n++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>

                                        <div><b><i><?php echo 'Total row(s) found: ' . $stmt->rowCount(); ?></i></b></div>
                                        <div align="right"><b><i><?php echo 'Period: ' . $period; ?></i></b></div>
                                        <div align="right"><b><i><?php echo 'Total Transactions: ' ?></i></b></div>
                                        <br>


                                        <table width="100%">
                                            <tr>
                                                <td>
                                                    <div align="right"><b><i><?php echo 'Claim Amount:'; ?></i></b></div>
                                                    <div align="right" style="font-size:18px"><strong><?php echo number_format($claim_amt, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                </td>
                                                <td>
                                                    <div align="right"><b><i><?php echo 'Payment:'; ?></i></b></div>
                                                    <div align="right" style="font-size:18px"><strong><?php echo number_format($pay, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                </td>

                                                <td>
                                                    <div align="right"><b><i><?php echo 'Total Discount:'; ?></i></b></div>
                                                    <div align="right" style="font-size:18px"><strong><?php echo number_format($T_discount, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                </td>
                                                <td>
                                                    <div align="right"><b><i><?php echo 'Total Charges:'; ?></i></b></div>
                                                    <div align="right" style="font-size:18px"><strong><?php echo number_format($T_add_charge, 2, '.', ',') . ' ' . '<small>only</small>'; ?> </strong></div>
                                                </td>

                                            </tr>
                                        </table>


                                <?php } else {
                                        echo '<strong>No Record Found</strong>';
                                    }
                                }
                                ?>


                                <?php if ($rpt_type == 'bill_to_acct') { ?>

                                    <?php
                                    ///$searchTag="WHERE $searchTag_part AND cr='2' AND transact_date BETWEEN '$start' AND '$end' order by hospital_no";
                                    $fullname = $_SESSION['fullname'];
                                    $stmt = $db->query("SELECT a.*,e.surname,e.oname,e.fname FROM patient_ap_services as a
	inner join enrollee as e 
	on a.hospital_no = e.hospital_no
		where a.cr='2' and a.created_by='$fullname' and $setdates2 order by date_entry");
                                    if ($stmt->rowCount() > 0) { ?>

                                        <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th data-toggle="true">Entered Date</th>
                                                    <th data-toggle="true">Service/Item</th>
                                                    <th data-toggle="true">Hospital #</th>
                                                    <th data-toggle="true">Name</th>
                                                    <th data-toggle="true">By</th>
                                                    <th data-toggle="true">Amount</th>
                                                    <th data-toggle="true">Claim</th>
                                                    <th data-toggle="true">Entered By</th>
                                                    <th data-toggle="true">Entered Date</th>
                                                    <th data-toggle="true">.</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $n = 1;
                                                $Pending = 0;
                                                $Approved = 0;
                                                $tcredit = 0;
                                                $queue = 0;
                                                $specimen = 0;
                                                $result = 0;
                                                $approve = 0;
                                                $reject = 0;
                                                $cancel = 0;
                                                $tcredit = 0;
                                                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php echo date('d,M y', strtotime($roww['date_entry'])); ?></td>
                                                        <td><?php echo $roww['item_services']; ?></td>
                                                        <td><?php echo $roww['hospital_no']; ?></td>
                                                        <td><?php echo $roww['fname'] . ' ' . $roww['oname'] . ' ' . $roww['surname']; ?></td>
                                                        <td><?php echo $roww['prepared_by']; ?></td>
                                                        <td><?php echo $roww['pay']; ?></td>
                                                        <td><?php echo $roww['claim_amt']; ?></td>
                                                        <td><?php echo $roww['created_by']; ?></td>
                                                        <td><?php if ($roww['paystatus'] == '1') {
                                                                echo date('d,M y H:i:s a', strtotime($roww['transact_date']));
                                                            } else {
                                                                echo '';
                                                            } ?></td>
                                                        <td><?php
                                                            if ($roww['acct_billed_ack'] == '1') {

                                                                echo 'Approved';
                                                                $Approved = $Approved + $roww['pay'];
                                                            } else {

                                                                $Pending = $Pending + $roww['pay'];
                                                                echo 'Pending';
                                                            }
                                                            ?>

                                                        </td>
                                                    </tr>
                                                <?php $n++;
                                                } ?>
                                            </tbody>
                                        </table>

                                        <h4>Summary</h4>
                                        <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;">
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td><strong>Amount (Approved)<br> </strong></td>
                                                    <td><strong>Amount (Pending)<br> </strong></td>
                                                    <td><strong></strong></td>
                                                </tr>

                                                <tr>
                                                    <td>#</td>
                                                    <td><?php echo number_format($Approved, 2, '.', ','); ?></td>
                                                    <td><?php echo number_format($Pending, 2, '.', ','); ?></td>
                                                    <td></td>
                                                </tr>

                                            </tbody>
                                        </table>
                                <?php }
                                }
                                ?>






                                <?php if ($rpt_type == 'p_c') { ?>
                                    <div class="alert alert-info"><strong>Patients Booked for appointment</strong></div>
                                    <?php
                                    $stmt = $db->prepare("SELECT * FROM apptm 
    WHERE checkin_by = :checkin_by 
      AND $setdates3 
    ORDER BY date_ap");

                                    $stmt->bindValue(':checkin_by', $_SESSION['fullname'], PDO::PARAM_STR);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) { ?>

                                        <table class="table table-striped table-bordered table-hover dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Hospital #</th>
                                                    <th>Patient Name</th>
                                                    <th>Doctor</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php $n = 1;
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <tr class="record">
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['hospital_no']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['patient_name']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['referal_doc']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo date('d, M Y', strtotime($row['date_ap'])); ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['ap_time']; ?></td>

                                                    </tr>

                                                <?php
                                                    $n++;
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot class="hide-if-no-paging">
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        <ul class="pagination pagination-sm"></ul>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>

                                <?php } else {
                                        echo '<strong>No Record Found</strong>';
                                    }
                                } ?>




                            </div>
                        </div>

                    </div>

                    <div class="modal inmodal fade" id="convert_pay_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h4 class="modal-title" id="">Convert Transactions</h4>
                                </div>
                                <div class="modal-body" id="convert_pay_body">
                                </div>
                            </div>
                        </div>
                    </div>




                </div>
                <?php include("../inc/footer.php"); ?>

            </div>
        </div>



        <?php //include("../inc/footer_scripts.php"); 
        ?>

        <!-- Data Tables -->

        <script src="../js/jquery-3.1.1.min.js"></script>
        <script src="../js/popper.min.js"></script>
        <script src="../js/bootstrap.js"></script>
        <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
        <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        <script src="../js/plugins/dataTables/datatables.min.js"></script>
        <script src="../js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>

        <!-- Custom and plugin javascript -->
        <script src="../js/inspinia.js"></script>
        <script src="../js/plugins/pace/pace.min.js"></script>

        <!-- Page-Level Scripts -->
        <script>
            $(document).ready(function() {
                $('.dataTables-example').DataTable({
                    pageLength: 25,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [{
                            extend: 'copy'
                        },
                        {
                            extend: 'csv'
                        },
                        {
                            extend: 'excel',
                            title: 'Data'
                        },
                        {
                            extend: 'pdf',
                            title: 'Data'
                        },

                        {
                            extend: 'print',
                            customize: function(win) {
                                $(win.document.body).addClass('white-bg');
                                $(win.document.body).css('font-size', '10px');

                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                            }
                        }
                    ]

                });

            });



            $(document).on('click', '.convert_entry', function() {
                var convert_transaction_id = $(this).attr("id");

                if (convert_transaction_id != '') {
                    $.ajax({
                        url: "fetch_set.php",
                        method: "POST",
                        // data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
                        data: {
                            convert_transaction_id: convert_transaction_id
                        },

                        success: function(data) {

                            $('.modal-title').text('Convert Transaction');

                            $('#convert_pay_body').html(data);
                            $('#convert_pay_modal').modal('show');
                        }
                    });
                }
            });




            <?php if (isset($_GET['pos'])) { ?>
                toastr.success('<?php echo 'Amount Paid converted to POS Payment'; ?>', 'Attention', {
                    timeOut: 5000
                })
            <?php } ?>



            <?php if (isset($_GET['Nex'])) { ?>
                toastr.error('<?php echo 'Patient number does not exist. Please try again '; ?>', 'Error', {
                    timeOut: 5000
                })
            <?php } ?>

            <?php if (isset($_GET['sx'])) { ?>
                toastr.success('<?php echo 'Payment was successful'; ?>', 'Successfully', {
                    timeOut: 5000
                })
            <?php } ?>
        </script>
</body>

</html>