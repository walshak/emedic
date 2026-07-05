<?php
require_once('Connections/Conn.php');
include("inc/credit_current_balance.php");

$start = $_POST['start'];
$end = $_POST['end'];
$emr = $_POST['emr'];
$target = $_POST['target'];

$staff_discount = null;
$todate = date("Y-m-d");

$general_credit_limit =    $_SESSION['credit_limit_status'];
$items = call_current_balance($db, $emr, $general_credit_limit);
$current_balance =  $items["current_balance"];
$patient_name      = $items['patient_name'];
$nhis_no           = $items['nhis_no'];
$names             = $items['names'];
$discount_set      = $items['discount_set'];
$insurance_type    = $items['insurance_type'];
$insurance_no      = $items['insurance_no'];
$save_insurance_no = $items['save_insurance_no'];
$wallet_amount     = $items['wallet_amount'];
$bal_credit_limit  = $items['bal_credit_limit'];
$credit_limit  = $items['bal_credit_limit'];
$add_minus         = $items['add_minus'];
$interest          = $items['interest'];
$patient_type      = $items['patient_type'];
$insur_title       = $items['insur_title'];
$patient_type  = $items['insurance_type'];
$insurance_name  = $items['insurance_name'];
$insurance_type  = $items['insurance_type'];

$patient_type =  $insurance_type;

if ($discount_set == 1 && $patient_type == "Private(Self)" or $patient_type == "Family") {
    $stmtcv = $db->query("SELECT 1 FROM hremp WHERE EmployeeCode='$nhis_no' LIMIT 1");
    $staff_discount = $stmtcv->rowCount() > 0 ? 1 : null;
}

$check_adm = $db->prepare("SELECT date_admit FROM admission WHERE hospital_no = :emr AND (adm_status = '3' or date(date_discharge) = :todate)");
$check_adm->bindParam(':emr', $emr);
$check_adm->bindParam(':todate', $todate);
$check_adm->execute();

if ($check_adm->rowCount() > 0) {
    $rowx = $check_adm->fetch(PDO::FETCH_ASSOC);
    $from_date = date('Y-m-d', strtotime($rowx['date_admit']));
    $todate = $to_date = date("Y-m-d");

    if ($from_date != $start && $todate != $end) {
        $from_date = $start;
        $todate = $end;
    }
}

if ($insurance_type == 'Family') {
    $papa = " and insurance_no='$insurance_no' ";
} else {
    $papa = "";
}


$fiscal_year_stmt = $db->query("
							SELECT begin, end 
							FROM chart_fiscal_year 
							WHERE closed = '0' 
							ORDER BY begin DESC 
							LIMIT 1");

$row = $fiscal_year_stmt->fetch(PDO::FETCH_ASSOC);
$begin = $row['begin'];
$end_acct_date  = $row['end'];

$begin_ts      = strtotime($begin);
$end_ts        = strtotime($end_acct_date);
$start_ts      = strtotime($from_date);
$date_ts       = strtotime($todate);

if ($start_ts < $begin_ts || $start_ts > $end_ts) {
    echo "<div class='alert alert-danger'>Financial Account Starting Date is out of range: ($begin to $end_acct_date) </div>";
    exit;
}
if ($date_ts < $begin_ts || $date_ts > $end_ts) {
    echo "<div class='alert alert-danger'>Financial Account Ending is out of range: ($begin to $end_acct_date) </div>";
    exit;
}

$papaCondition = '';
$params = [
    ':emr'   => $emr,
    ':start' => date('Y-01-01', strtotime($begin)),
    ':end'   => date('Y-m-d', strtotime('-1 day', strtotime($start)))
];

if ($insurance_type === 'Family') {
    $papaCondition = ' AND insurance_no = :insurance_no';
    $params[':insurance_no'] = $insurance_no;
}

$sql = "
								SELECT 
									COALESCE(SUM(dr_amt), 0) AS TOTAL_DEBITS, 
									COALESCE(SUM(cr_amt), 0) AS TOTAL_CREDITS
								FROM chart_ledger 
								WHERE hospital_no = :emr
								$papaCondition
								AND account_no = '2121'
								AND date_entry2 BETWEEN :start AND :end
							";

$stmtb = $db->prepare($sql);
$stmtb->execute($params);
$result = $stmtb->fetch(PDO::FETCH_ASSOC);

// Balance forward calculation
$totalDebits  = $result['TOTAL_DEBITS'];
$totalCredits = $result['TOTAL_CREDITS'];
$bal_B_F      = $totalCredits - $totalDebits;
$set          = ($totalDebits != 0 || $totalCredits != 0) ? 1 : null;
?>
<div id="Main_content">
    <div align="left" style="font:bold 14px 'Arial';">
    </div>
    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
        <tr>
            <td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
            <td width="50%" align="right">
                <div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>
                        <?php echo $_SESSION['h_name']; ?></strong></div> <br>
                <div style="font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
            </td>
        </tr>
    </table>
    <hr>

    <?php if ($insurance_type == 'Family') { ?>
        Insurance Status: <b><i><?= $insurance_type . ' ( ' . $insurance_name . ' )';  ?></i></b><br>
    <?php } ?>
    Hospital Statement For <?php echo  $patient_name . ' ( ' . $emr . ' )'  . '<br>' . 'Address / Phone: ' .  $address . ' / ' . $phone; ?>
    <?php

    if (isset($_POST['discount_vourcher']) and $_POST['discount_vourcher'] != '') {

        $auth_code = $_POST['discount_vourcher'];
        $set_date = date("Y-m-d");
        $query = "SELECT vi.voucher_code, v.amount, v.created_by, v.flat_cent, v.batch_type, v.v_center 
   FROM vouchers_inventory as vi 
   INNER JOIN vouchers as v ON vi.batch_code = v.batch_code 
   WHERE vi.voucher_code = :auth_code 
   AND vi.used_status = 0 
   AND v.date_expire >= :set_date 
   AND (v.batch_type = 'Discount' OR v.batch_type = 'writeoff')";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':auth_code', $auth_code, PDO::PARAM_STR);
        $stmt->bindValue(':set_date', $set_date, PDO::PARAM_STR);
        $stmt->execute();

        // Check if any voucher is available
        if ($stmt->rowCount() == 0) {
            echo '<strong style="color:#F00; font-size:25px;">No Voucher Available!</strong>';
        } else {
            $flat_cent = 'Flat';
            $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
            $voucher_type = $rowx['batch_type'];
            $voucher_center = $rowx['v_center'];
            $created_by = $rowx['created_by'];
            $v_discount = $rowx['amount'];
    ?>
            <table class="table table-bordered" style="font-size:14px;">
                <tr>
                    <td>Discount Voucher:</td>
                    <td><strong><?php echo $flat_cent; ?></strong> / <?php echo $v_discount; ?></td>
                    <td><strong>Auth. By</strong> / <?php echo $created_by; ?></td>
                </tr>
            </table>
        <?php
        }
        ?>

    <?php } else {
        $v_discount = 0;
    } ?>

    <h2>PAYMENT HISTORY</h2>

    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
        <thead>
            <tr bgcolor="#CCCCCC">
                <th width="30%">Item/Description</th>
                <th width="15%">Bank</th>
                <th width="10%">Deposit</th>
                <th width="10%">Withdrawn</th>
                <th width="10%">Balance</th>
                <th width="15%">Date</th>
                <th width="15%">Entered By</th>
            </tr>
        </thead>
        <tbody>
            <?php

            // if ($set == 1) { 
            ?>
            <tr class="record">
                <td style="border-bottom: 1px solid #ddd;">Opening Balance</td>
                <td style="border-bottom: 1px solid #ddd;"></td>

                <?php if ($bal_visible == 1) { ?>
                    <td style="border-bottom: 1px solid #ddd;">0.00</td>
                    <td style="border-bottom: 1px solid #ddd;">0.00</td>
                    <td style="border-bottom: 1px solid #ddd;"><?= number_format($bal_B_F, 2); ?></td>
                <?php } else { ?>
                    <td style="border-bottom: 1px solid #ddd;"></td>
                    <td style="border-bottom: 1px solid #ddd;"></td>
                <?php } ?>

                <td style="border-bottom: 1px solid #ddd;"><?php ///echo  date("d,M Y", strtotime(date("Y-m-d"))); 
                                                            ?></td>
                <td style="border-bottom: 1px solid #ddd;"></td>
            </tr>

            <?php //}

            $n = 1;
            $dr_amt = 0;
            $cr_amt = 0;
            $cr_refund_amt = 0;

            $table_name = "chart_ledger";
            $bal_visible = 1;
            $params = [':start' => $from_date, ':end' => $todate]; // Common parameters
            if ($insurance_type === 'Family') {

                $search_part = "AND account_no = '2121' AND insurance_no = :insurance_no AND DATE(date_entry2) BETWEEN :start AND :end";
                $params[':insurance_no'] = $insurance_no;
            } else {
                $search_part = "AND account_no = '2121' AND DATE(date_entry2) BETWEEN :start AND :end";
                $params[':emr'] = $emr;
            }


            if ($insurance_type == 'Family') {
                $sql = "SELECT * FROM $table_name WHERE hospital_no != '' $search_part ORDER BY sn";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            } else {
                $sql = "SELECT * FROM $table_name WHERE hospital_no = :emr $search_part ORDER BY sn";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            }


            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $sale_sn = $row['sale_sn'];
                $app_no = $row['app_no'];
                $item_services = $row['item_services'];
                $amt = $row['cr_amt'];
                if (strtoupper($row['ref_value']) != 'REFUND') {
                    $cr_amt = $cr_amt + $row['cr_amt'];
                } else {
                    $cr_refund_amt = $cr_refund_amt + $row['cr_amt'];
                }

                $dr_amt = $dr_amt + $row['dr_amt'];

                $cr_amt = $cr_amt + $row['cr_amt'];

                if ($n == 1) {
                    $bal = $row['cr_amt'] + $bal_B_F - $row['dr_amt'];
                } else {
                    $bal += $row['cr_amt'] - $row['dr_amt'];
                }


            ?>
                <tr>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo  $n . ' - ' . $row['item_services']; ?>
                        <?php
                        $item_services = $row['item_services'];
                        $item_services2 = substr($item_services, 0, 7);

                        if (strpos($item_services, "Returned/") !== false) {
                            $Returned = $Returned + $row['cr_amt'];
                        } ?></td>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo $row['bank_name']; ?></td>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($row['cr_amt'], 2); ?></td>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($row['dr_amt'], 2); ?></td>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($bal, 2); ?></td>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo  date("d,M Y h:i:s a", strtotime($row['date_entry'])); ?></td>
                    <td style="border-bottom: 1px solid #ddd;"><?php echo  $row['prepared_by']; ?></td>
                </tr>
            <?php $n += 1;
            } ?>

            <tr class="record">
                <td style="border-bottom: 1px solid #ddd;"></td>
                <td style="border-bottom: 1px solid #ddd;"><b>Total:</b></td>
                <td style="border-bottom: 1px solid #ddd;"><b><?php echo number_format($cr_amt, 2, '.', ','); ?></b></td>
                <td style="border-bottom: 1px solid #ddd;"><b><?php echo number_format($dr_amt, 2, '.', ','); ?></b></td>
                <td style="border-bottom: 1px solid #ddd;"><b><?php echo number_format($cr_amt - $dr_amt, 2, '.', ','); ?></b></td>
                <td style="border-bottom: 1px solid #ddd;"></td>
                <td style="border-bottom: 1px solid #ddd;"></td>
            </tr>
        </tbody>
    </table>

    <div id="sub_content">
        <table width="100%">
            <tr>
                <td width="70%">
                    <hr>
                    <h2>INVOICED FOR PAYMENT</h2>
                    <?php
                    $stmt = $db->prepare("SELECT * FROM patient_ap_services 
WHERE hospital_no=:emr and paystatus=0 and pay>0  and invoice_status=1 and date(date_entry) between :from_date and :to_date order by serv_group,date_entry");
                    $stmt->bindParam(':emr', $emr);
                    $stmt->bindParam(':from_date', $from_date);
                    $stmt->bindParam(':to_date', $to_date);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        include("inc/utilities.php");


                        patient_discount(
                            $db,
                            $staff_discount,
                            $emr,
                            $patient_type,
                            $discount_set,
                            $referral_name,
                            $insurance_no,
                            $pf,
                            $pf_value,
                            $dsc_chr,
                            $dura,
                            $service_type,
                            $dsc_chr_set,
                            $post_type,
                            $count_bal,
                            $dsc_chr_set,
                            $mySearch,
                            $grp_idv,
                            $grp_idv_no
                        );



                        $amt_sysSelected = 0;
                        $sX = 0;
                        $total_inv = 0;
                        $total_cr = 0;
                        $total_dsc = 0;
                        $total_chr = 0;

                    ?>
                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>

                                <tr bgcolor="#CCCCCC">
                                    <th width="1%"></th>
                                    <th width="35%">Item/Service</th>
                                    <th width="5%">Qty</th>
                                    <th width="20%">Amount</th>
                                    <th width="10%">DSC/CHR</th>
                                    <th width="15%">.</th>
                                </tr>
                            </thead>


                            <?php
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                $pay = $row['pay'];
                                $snn = $row['sn'];
                                $cat_type = $row['cat_type'];
                                $dept_id_id = $row['dept_id'];
                                $amt_sysSelected = $amt_sysSelected + $pay;

                                if ($row['cr'] == 1) {
                                    $total_cr = $total_cr + $row['pay'];
                                }

                                $discount = null;
                                $charge = null;

                                if ($discount_set == 1) {

                                    /// echo '=======' . $service_type;

                                    if (strtoupper($service_type) == 'SPECIFY') {

                                        $item_services = $row['item_services'];

                                        if ($patient_type == 'Family' or $patient_type == 'Corperate') {
                                            $mySearch = "individual_group_no='$insurance_no'";
                                        } else {

                                            if ($staff_discount == 1) {
                                                $emr2 = "staff";
                                                $mySearch = "individual_group_no='$emr2'";
                                            } else {
                                                $mySearch = "individual_group_no='$emr'";
                                            }
                                        }

                                        if (strtoupper($row['serv_group']) == 'PHARMACY') {
                                            $item_services_2 = "all_Pharmacy";
                                        } elseif (strtoupper($row['serv_group']) == 'NURSING SERVICES') {
                                            $item_services_2 = "all_Nursing Services";
                                        } elseif ($row['serv_group'] == 'Radiology' or $row['serv_group'] == 'Laboratory') {
                                            $item_services_2 = "all_investigation";
                                        } elseif (strtoupper($row['serv_group']) == 'MEDICAL SERVICES') {
                                            $item_services_2 = "all_Medical Services";
                                        } elseif (
                                            strtoupper($row['serv_group']) != 'MEDICAL SERVICES' && strtoupper($row['serv_group']) != 'PHARMACY'  &&
                                            strtoupper($row['serv_group']) != 'NURSING SERVICES' && $row['serv_group'] != 'Radiology' && $row['serv_group'] != 'Laboratory'
                                        ) {
                                            $item_services_2 = "all_others";
                                        } else {
                                            $item_services_2 = $item_services;
                                        }

                                        $stmt_dsc2 = $db->query("SELECT * FROM patient_discount_services WHERE $mySearch and service_item='$item_services' and status='0'");
                                        if ($stmt_dsc2->rowCount() > 0) {
                                            $item_services_2 = $item_services;
                                        }

                                        include_once("inc/utilities.php");
                                        selected_items($db, $emr, $item_services_2, $mySearch, $pf, $pf_value, $dsc_chr, $dura, $post_type, $count_bal, $dsc_chr_set, $sn_service);
                                    }

                                    include_once("inc/utilities.php");    /// 
                                    //	if($cat_type == $service_type or $service_type == 'All Services'){
                                    dsc_chr_calc($pf, $dsc_chr_set, $dsc_chr, $cat_type, $service_type, $pay, $pf_value, $discount, $charge, $total_chr, $total_dsc, $post_type, $count_bal);
                                    ///}

                                }

                                if ($voucher_type == 'writeoff' and $dept_id_id == $voucher_center) {


                                    $sX = $sX + 1;
                            ?>
                                    <tr>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $sX; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['qty']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($pay); ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php if ($charge > 0) {
                                                                                        echo 'CHR: ' . number_format($charge);
                                                                                    }
                                                                                    if ($discount > 0) {
                                                                                        echo 'DSC: ' . number_format($discount);
                                                                                    }
                                                                                    ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php if ($row['cr'] == 1) {
                                                                                        echo "<b style='color:red; '>Credit</b>";
                                                                                    } ?></td>
                                    </tr>
                                <?php }

                                if ($voucher_type != 'writeoff') {

                                    $sX = $sX + 1;
                                ?>
                                    <tr>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $sX; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['qty']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($pay); ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php if ($charge > 0) {
                                                                                        echo 'CHR: ' . number_format($charge);
                                                                                    }
                                                                                    if ($discount > 0) {
                                                                                        echo 'DSC: ' . number_format($discount);
                                                                                    }
                                                                                    ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php if ($row['cr'] == 1) {
                                                                                        echo "<b style='color:red; '>Credit</b>";
                                                                                    } ?></td>
                                    </tr>

                                <?php } ?>

                            <?php


                            }

                            ?>
                            <tr>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"><b><?php echo number_format($amt_sysSelected); ?></b></td>
                                <td style="border-bottom: 1px solid #ddd;"><b><?php
                                                                                if ($total_dsc > 0) {
                                                                                    echo  number_format($total_dsc);
                                                                                }
                                                                                ?></b></td>
                                <td style="border-bottom: 1px solid #ddd;"><b>
                                        <?php if ($total_dsc > 0) {
                                            echo  number_format($total_cr);
                                        } ?>
                                    </b></td>
                            </tr>

                            <tr>
                                <td colspan="6">
                                    <div align="center"><b>END OF ITEM(S) INVOICED FOR PAYMENT</b></div>
                                </td>
                            </tr>

                        </table>

                    <?php }
                    ?>



                    <hr>
                    <h2>INVOICE <u>PENDING</u> FOR PAYMENT</h2>
                    <?php
                    $stmt = $db->prepare("SELECT * FROM patient_ap_services 
WHERE hospital_no=:emr and paystatus=0 and pay>0  and invoice_status=0 and date(date_entry) between :from_date and :to_date order by serv_group,date_entry");
                    $stmt->bindParam(':emr', $emr);
                    $stmt->bindParam(':from_date', $from_date);
                    $stmt->bindParam(':to_date', $to_date);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $un_invoiced_item = 0;
                    ?>
                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th width="1%"></th>
                                    <th width="35%">Item/Service</th>
                                    <th width="5%">Qty</th>
                                    <th width="20%">Amount</th>
                                    <th width="10%">DSC/CHR</th>
                                    <th width="15%">.</th>
                                </tr>
                            </thead>

                            <?php
                            $total_cr_INV = 0;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                $pay = $row['pay'];
                                $snn = $row['sn'];
                                $cat_type = $row['cat_type'];
                                $dept_id_id = $row['dept_id'];
                                $un_invoiced_item = $un_invoiced_item + $pay;

                                if ($row['cr'] == 1) {
                                    $total_cr = $total_cr + $row['pay'];
                                    $amt_sysSelected = $amt_sysSelected + $row['pay'];
                                    $total_cr_INV = $total_cr_INV + $row['pay'];
                                }

                                $sX = $sX + 1;
                            ?>
                                <tr>
                                    <td style="border-bottom: 1px solid #ddd;"><?php echo $sX; ?></td>
                                    <td style="border-bottom: 1px solid #ddd;"><?php echo  $row['item_services']; ?></td>
                                    <td style="border-bottom: 1px solid #ddd;"><?php echo $row['qty']; ?></td>
                                    <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($pay); ?></td>
                                    <td style="border-bottom: 1px solid #ddd;"></td>
                                    <td style="border-bottom: 1px solid #ddd;"><b><?php if ($row['cr'] == 1) {
                                                                                        echo "<b style='color:red; '>Credit</b>";
                                                                                    } ?></b></td>
                                </tr>
                            <?php


                            }

                            ?>

                            <tr>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"><b><?php echo number_format($un_invoiced_item); ?></b></td>
                                <td style="border-bottom: 1px solid #ddd;"></td>
                                <td style="border-bottom: 1px solid #ddd;"><b><?php
                                                                                echo  number_format($total_cr_INV);
                                                                                ?></b></td>
                            </tr>

                        </table>

                    <?php } ?>




                </td>
                <td>

                    <?php

                    $current_balance = $wallet_amount;
                    $amt_sysSelected_actual = $amt_sysSelected;
                    $amt_sysSelected = $amt_sysSelected - $total_dsc;

                    if ($flat_cent == 'Flat') {
                        $current_balance = $outstanding_balance + $v_discount;
                    } else {
                        $percent = $v_discount / 100;
                        $v_discount = $amt_sysSelected * $percent;
                        $current_balance = $outstanding_balance + $v_discount;
                    }

                    if ($amt_sysSelected > $current_balance) {
                        $cash = $amt_sysSelected - $current_balance;
                        $new_current_bal = 0;
                    } else {
                        $new_current_bal = $current_balance - $amt_sysSelected_actual;
                        $cash = 0;
                    }
                    ?>

                    <hr>
                    <h2>TRANSACTION SUMMARY</h2>

                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 18px;text-align:left;width : 100%;">
                        <tr>
                            <td>Patient's Deposit Amount</td>
                            <td><?php echo number_format($current_balance); ?></td>
                        </tr>
                        <tr>
                            <td>Total Amount Accrued</td>
                            <td><?php echo number_format($amt_sysSelected_actual); ?></td>
                        </tr>
                        <?php if ($total_dsc > 0) { ?>
                            <tr>
                                <td>Discount:</td>
                                <td><?php echo number_format($total_dsc); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($v_discount > 0) { ?>
                            <tr>
                                <td>Voucher Discount:</td>
                                <td><?php echo number_format($v_discount); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($total_chr > 0) { ?>
                            <tr>
                                <td>Additional Charge:</td>
                                <td><?php echo number_format($total_chr); ?></td>
                            </tr>
                        <?php } ?>

                        <tr>
                            <td style="background-color:greenyellow; ">
                                <h2>PAYING</h2>
                            </td>
                            <td style="background-color:greenyellow; ">
                                <h2><strong><?php
                                            if ($voucher_type == 'writeoff') {
                                                $new_current_bal = 0;
                                                $credit_bal = 0;
                                                echo 'WriteOff';
                                            } else {
                                                echo number_format($cash);
                                            } ?></strong></h3>
                            </td>
                        </tr>
                        <tr>
                            <td>Outstanding/Balance</td>
                            <td><?php if ($new_current_bal == 0) {
                                    echo 'Zero Naira';
                                } else {
                                    echo number_format($new_current_bal, 2);
                                } ?></td>
                        </tr>
                        <tr>
                            <td>Total Credit</td>
                            <td><?php if ($total_cr == 0) {
                                    echo 'Zero Naira';
                                } else {
                                    echo number_format($total_cr);
                                } ?></td>
                        </tr>
                    </table>

                </td>

            </tr>

        </table>
    </div>
</div>


<hr>
<button onclick="printdeposit('Main_content')" class="btn btn-success btn btn-sm">Print Full Copy</button>
&nbsp; : &nbsp;
<button onclick="printdeposit('sub_content')" class="btn btn-success btn btn-sm">Print Invoice Copy</button>

&nbsp;&nbsp; : &nbsp;&nbsp;

<a href="<?= $target; ?>" class="btn btn-danger btn btn-sm">Close</a>
<hr>
<br>


<script>
    function printdeposit(deposit_reciept) {
        var printWindow = window.open('', 'TRANSACTIONS / INVOICE PRINOUT', 'height=400,width=600');
        printWindow.document.write('<html><head><title>TRANSACTIONS / INVOICE PRINOUT</title></head><body>');
        printWindow.document.write(document.getElementById(deposit_reciept).innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.print();
        printWindow.close();
    }
</script>