<div class="row">

    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Report</h5>
            </div>
            <div class="ibox-content" id="content">

                <?php

                $stmt = $db->query("SELECT sn FROM department WHERE department='Pharmacy'");
                $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
                $dept_id = $row_rstSelect['sn']; ?>

                <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                    <tr>
                        <td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
                        <td width="50%" align="right">
                            <div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong><?php echo $_SESSION['h_name']; ?></strong></div> <br>
                            <div style="font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
                        </td>
                    </tr>
                </table>
                <hr>


                <div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
                    <?php

                    $Pharmacy = 'Pharmacy';
                    $one = 1;
                    $zero = 0;
                    $qty = 0;
                    $pay_credit = null;
                    $colordecide = null;
                    $staff2 = null;
                    $t_qty = null;
                    $dsp_by = null;


                    if (isset($_POST['next1'])) {
                        header("location:pharm_rpt.php");
                    }

                    $desccc = !empty($_POST['desccc']) ? $_POST['desccc'] : null;
                    $rpt_type = !empty($_POST['rpt_type']) ? $_POST['rpt_type'] : null;
                    $searchdrug_inv = !empty($_POST['searchdrug_inv']) ? $_POST['searchdrug_inv'] : null;



                    if (isset($_POST['staff']) and $_POST['staff'] != '') {

                        if ($_POST['staff'] == 'All Staff') {
                            $staff_name = $_SESSION['fullname'] . ' (All Staff)';
                            $staff = "";
                        } else {
                            $fullname = $_POST['staff'];

                            if ($rpt_type == 'at' or $rpt_type == 'ap') {
                                $staff = " and inv.enter_by='$fullname'";
                            }
                            if ($rpt_type == 'ps' or $rpt_type == 'dcr' or $rpt_type == 'd_r') {
                                $staff = " and dsp_by='$fullname'";
                            }
                            if ($rpt_type == 'inv') {
                                $staff = " and dsp_by='$fullname'";
                                $staff2 = " and enter_by='$fullname'";
                            }
                            $staff_name = $fullname;
                        }
                        //echo $fullname  . 'ggg';
                    } else {
                        $fullname = $_SESSION['fullname'];
                        $staff_name = $_SESSION['fullname'];
                    }

                    //


                    if (isset($_GET['all'])) { ?>
                        All Drugs In-stocks
                    <?php    } elseif ($desccc == 'Expired') { ?>
                        All Expired Drugs
                    <?php    } elseif ($desccc == 'rorder') { ?>
                        Drugs Re-order list

                    <?php } elseif ($rpt_type == 'at') { ?>
                        Today's Sales Report (Summary) (<?php
                                                        $start = $_POST['from_date'];
                                                        $to = $_POST['to_date'];
                                                        if ($start != '' and $to != '') {
                                                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                                                            $ddset = 1;
                                                        } else {
                                                            $setdate = date('Y-m-d');
                                                            echo date('d M,Y', strtotime($setdate));
                                                            $ddset = 0;
                                                        } ?>)
                    <?php } elseif ($desccc == 'Expiring') {
                        $days = ($_POST['days']);
                        echo 'List of drugs expiring in ' . $days . ' day(s)';
                    } elseif ($rpt_type == 'ap') { ?>
                        Today's Sales Report (Summary by Drug Name) (<?php
                                                                        $start = $_POST['from_date'];
                                                                        $to = $_POST['to_date'];
                                                                        if ($start != '' and $to != '') {
                                                                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                                                                            $ddset = 1;
                                                                        } else {
                                                                            $setdate = date('Y-m-d');
                                                                            echo date('d M,Y', strtotime($setdate));
                                                                            $ddset = 0;
                                                                        } ?>)
                    <?php } elseif ($desccc == 'Expiring') {
                        $days = ($_POST['days']);
                        echo 'List of drugs expiring in ' . $days . ' day(s)';
                    } elseif ($rpt_type == 'doc_pres') {
                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];
                        if ($start != '' and $to != '') {
                            echo 'Report of Prescription Dispense/Not Dispense Status for Patients<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Prescription Dispense/Not Dispense Status for Patients';
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'ps') {

                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];
                        if ($start != '' and $to != '') {
                            echo 'Report of Patients Seen and Drugs Details<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Patients Seen and Drugs Details';
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'dcr') {
                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];
                        if ($start != '' and $to != '') {
                            echo 'Report of Dispensed on Credit<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Patients Dispensed on Credit';
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'inv') {

                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];
                        if ($start != '' and $to != '') {
                            echo 'Inventory Report<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Inventory Report';
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'd_r') {

                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];
                        if ($start != '' and $to != '') {
                            echo 'Drugs Returned<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Drugs Returned';
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'inv_drug') {

                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];
                        if ($start != '' and $to != '') {
                            echo 'Stock Inventory<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Stock Inventory: ' . $searchdrug_inv;
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'rhmo') {

                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];

                        $hmo_nhis = isset($_POST['hmo_nhis']) && !empty($_POST['hmo_nhis']) ? $_POST['hmo_nhis'] : '';
                        $hmo_type = isset($_POST['hmo_type']) && !empty($_POST['hmo_type']) ? $_POST['hmo_type'] : '';


                        if ($start != '' and $to != '') {
                            echo 'HMO/Coperate Reports: ' . $hmo_type . '<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>HMO/Coperate Reports: ' . $hmo_type;
                            $ddset = 0;
                        }
                    } elseif ($rpt_type == 'visit') {

                        $start = $_POST['from_date'];
                        $to = $_POST['to_date'];

                        if ($start != '' and $to != '') {
                            echo 'Patient Visit Reports<br>';
                            echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
                            $ddset = 1;
                        } else {
                            $setdate = date('Y-m-d');
                            echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Patient visit reports';
                            $ddset = 0;
                        }
                    }


                    ?>

                </div>

                <hr>


                <?php


                $setdate = date('Y-m-d');
                $desccc = !empty($_POST['desccc']) ? $_POST['desccc'] : null;
                if (isset($_GET['all']) or $desccc == 'Expired' or $desccc == 'rorder' or $desccc == 'Expiring') {

                    if (isset($_GET['all'])) {
                        $stmt = $db->prepare("SELECT * FROM stock_table WHERE stock_table = :stock_table ORDER BY product_name");
                        $stmt->bindValue(':stock_table', $Pharmacy, PDO::PARAM_STR);
                    } elseif ($desccc == 'Expired') {
                        $stmt = $db->prepare("SELECT s.* FROM stock_table s 
							  INNER JOIN stock_table_procurment as p ON s.sn = p.stock_sn 
							  WHERE date(p.expiry_date) <= :setdate 
							  AND finish_status = '0' 
							  AND p.status = 'yes' 
							  AND s.stock_table = 'Pharmacy' 
							  ORDER BY s.product_name");
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                    } elseif ($desccc == 'Expiring') {
                        $setdate = date('Y-m-d');
                        $days = !empty($_POST['days']) ? $_POST['days'] : 1;
                        $date = date('Y-m-d', strtotime($setdate . " + $days days"));

                        $stmt = $db->prepare("SELECT s.* FROM stock_table s 
							  INNER JOIN stock_table_procurment as p ON s.sn = p.stock_sn 
							  WHERE p.expiry_date <= :date 
							  AND finish_status = '0' 
							  AND p.status = 'yes' 
							  AND s.stock_table = 'Pharmacy' 
							  ORDER BY s.product_name");
                        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
                    } elseif ($desccc == 'rorder') {
                        $stmt = $db->prepare("SELECT * FROM stock_table WHERE stock_table = :stock_table 
							  AND qty = reorder_level 
							  ORDER BY product_name, dosage");
                        $stmt->bindValue(':stock_table', $Pharmacy, PDO::PARAM_STR);
                    }

                    $stmt->execute();

                    if (($desccc == 'Expired' or $desccc == 'Expiring') && $stmt->rowCount() == 0) {

                        if ($desccc == 'Expired') {
                            $stmt = $db->prepare("SELECT * FROM stock_table WHERE p.expiry_date <= :date 		
						AND status = 'active' 
						AND stock_table = 'Pharmacy' 
						ORDER BY product_name");
                            $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                        } else {
                            $setdate = date('Y-m-d');
                            $days = !empty($_POST['days']) ? $_POST['days'] : 1;
                            $date = date('Y-m-d', strtotime($setdate . " + $days days"));
                            $stmt = $db->prepare("SELECT * FROM stock_table 
							WHERE date(expire_date) BETWEEN '$setdate' AND '$date' AND status = 'active' AND stock_table = 'Pharmacy' ORDER BY product_name");
                        }
                        $stmt->execute();
                    }


                    if ($stmt->rowCount() > 0) { ?>

                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th width="2%">#</th>
                                    <th width="20%">Name </th>
                                    <th width="10%">Formulation </th>
                                    <th width="7%">Buying cost </th>
                                    <th width="7%">NHIS</th>
                                    <th width="7%">Hosp. Price</th>
                                    <th width="7%">Qty</th>
                                    <th width="10%">Expired<br>Date</th>

                                </tr>
                            </thead>
                            <tbody>


                                <?php $n = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr class="record">
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['product_name']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['dosage']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['buying_cost']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['nhis_price']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['hosp_price']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['qty']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['expire_date']; ?></td>
                                    </tr>
                                <?php
                                    $n++;
                                }
                                ?>
                            </tbody>
                        </table>
                        <div><b><i><?php echo 'Total row(s) found: ' . $stmt->rowCount(); ?></i></b></div>
                    <?php }
                } elseif ($rpt_type == 'at') {
                    if ($ddset == 1) {

                        $stmt = $db->prepare("SELECT DISTINCT p.drug_sn 
									  FROM patient_ap_services AS p 
									  INNER JOIN stock_table_inven AS inv ON inv.stock_sn = p.drug_sn 
									  WHERE p.paystatus = :paystatus 
									  AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id) $staff
									  AND date(transact_date) BETWEEN :start AND :to");
                        $stmt->bindValue(':paystatus', $one, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT p.drug_sn 
									  FROM patient_ap_services AS p 
									  INNER JOIN stock_table_inven AS inv ON inv.stock_sn = p.drug_sn 
									  WHERE date(transact_date) = :setdate 
									  AND p.paystatus = :paystatus 
									  AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id) $staff");
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                        $stmt->bindValue(':paystatus', $one, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    if ($stmt->rowCount() > 0) { ?>
                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th width="20%">Name </th>
                                    <th width="7%">Quantity<br>Dispensed </th>
                                    <th width="7%">Quantity<br>in-stock</th>
                                    <th width="40%">
                                        <table width="100%">
                                            <tr>
                                                <td colspan="4">
                                                    <div align="center">&nbsp;</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="25%">
                                                    <div align="right"><?php echo 'POS <br>(External Sales)'; ?></div>
                                                </td>
                                                <td width="25%">
                                                    <div align="right"><?php echo 'Private<br>(Patients)'; ?></div>
                                                </td>
                                                <td width="25%">
                                                    <div align="right"><?php echo 'NHIS<br>(10% Payments)'; ?></div>
                                                </td>
                                                <td width="25%">
                                                    <div align="right"><?php echo 'CLAIM<br>(Zero Payable)'; ?></div>
                                                </td>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $t_pos = 0;
                                $t_nhis = 0;
                                $t_pvt = 0;
                                $t_claim = 0;
                                $t_nhis_90 = 0;
                                $qty = 0;
                                $pos = 0;
                                $nhis = 0;
                                $pvt = 0;
                                $claim = 0;
                                $nhis_90 = 0;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


                                    $drug_sn = $row['drug_sn'];  /// target ////
                                    if ($ddset == 1) {
                                        $stmtx = $db->prepare("SELECT 
												   p.qty AS p_qty,
												   p.serv_group,
												   p.claim_amt,
												   p.pay,
												   d.product_name,
												   d.qty AS d_qty 
												   FROM patient_ap_services AS p 
												   INNER JOIN stock_table AS d ON d.sn = p.drug_sn 
												   INNER JOIN stock_table_inven AS inv ON p.sn = inv.sale_sn 
												   WHERE p.paystatus = :paystatus 
												   AND p.drug_sn = :drug_sn 
												   AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)
												   $staff 
												   AND date(p.transact_date) BETWEEN :start AND :to");
                                        $stmtx->bindValue(':paystatus', $one, PDO::PARAM_STR);
                                        $stmtx->bindValue(':drug_sn', $drug_sn, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':start', $start, PDO::PARAM_STR);
                                        $stmtx->bindValue(':to', $to, PDO::PARAM_STR);
                                    } else {
                                        $stmtx = $db->prepare("SELECT 
												   p.qty AS p_qty,
												   p.serv_group,
												   p.claim_amt,
												   p.pay,
												   d.product_name,
												   d.qty AS d_qty 
												   FROM patient_ap_services AS p 
												   INNER JOIN stock_table AS d ON d.sn = p.drug_sn 
												   INNER JOIN stock_table_inven AS inv ON p.sn = inv.sale_sn 
												   WHERE date(p.transact_date) = :setdate 
												   AND p.paystatus = :paystatus 
												   AND p.drug_sn = :drug_sn 
												   AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)
												   $staff");
                                        $stmtx->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                                        $stmtx->bindValue(':paystatus', $one, PDO::PARAM_STR);
                                        $stmtx->bindValue(':drug_sn', $drug_sn, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                    }

                                    $stmtx->execute();

                                    if ($stmtx->rowCount() > 0) {
                                        //if(mysql_num_rows($rstSelect2)>0){ 
                                ?>
                                    <?php  // $pos=0;  $nhis=0;      
                                        while ($roww = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                                            ///while($roww=mysql_fetch_array($rstSelect2)) {
                                            $qty = $qty + $roww['p_qty'];
                                            if ($roww['serv_group'] == 'EX') {
                                                $pos = $pos + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] > 0) {
                                                $nhis = $nhis + $roww['pay'];
                                                $nhis_90 = $nhis_90 + $roww['claim_amt'];
                                            }
                                            if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX') {
                                                $pvt = $pvt + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
                                                $claim = $claim + $roww['claim_amt'];
                                            }
                                            $drug_name = $roww['product_name'];
                                            $d_qty = $roww['d_qty'];
                                        }
                                    } ?>
                                    <tr class="record">
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $drug_name; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $qty;
                                                                                    $qty = 0; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $d_qty; ?></td>
                                        <td width="40%" style="border-bottom: 1px solid #ddd;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="25%">
                                                        <div align="right"><?php echo number_format($pos, 2, '.', ','); ?></div>
                                                    </td>
                                                    <td width="25%">
                                                        <div align="right"><?php echo number_format($pvt, 2, '.', ','); ?></div>
                                                    </td>
                                                    <td width="25%">
                                                        <div align="right"><?php echo number_format($nhis, 2, '.', ','); ?></div>
                                                    </td>
                                                    <td width="25%">
                                                        <div align="right"><?php echo number_format($claim, 2, '.', '.'); ?></div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                <?php
                                    $t_pos = $t_pos + $pos;
                                    $t_nhis = $t_nhis + $nhis;
                                    $t_nhis_90 = $t_nhis_90 + $nhis_90;
                                    $t_pvt = $t_pvt + $pvt;
                                    $t_claim = $t_claim + $claim;
                                    $pos = 0;
                                    $nhis = 0;
                                    $pvt = 0;
                                    $pvt = 0;
                                    $claim = 0;
                                } ?>

                                <tr class="record">
                                    <td style="border-bottom: 1px solid #ddd;"><b>Summary(Total):</b></td>
                                    <td style="border-bottom: 1px solid #ddd;"></td>
                                    <td style="border-bottom: 1px solid #ddd;"></td>
                                    <td width="40%" style="border-bottom: 1px solid #ddd;">
                                        <table width="100%">
                                            <tr>
                                                <td width="25%">
                                                    <div align="right"><strong><?php echo number_format($t_pos, 2, '.', ','); ?></strong></div>
                                                </td>
                                                <td width="25%">
                                                    <div align="right"><strong><?php echo number_format($t_pvt, 2, '.', ','); ?></strong></div>
                                                </td>
                                                <td width="25%">
                                                    <div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ',') . '(10%)' . '<br>' . number_format($t_nhis, 2, '.', ',') . '(90%)'; ?></strong></div>
                                                </td>
                                                <td width="25%">
                                                    <div align="right"><strong><?php echo number_format($t_claim, 2, '.', ','); ?></strong></div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                            </tbody>
                        </table>
                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

                    <?php } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'ap') {

                    if ($ddset == 1) {
                        $stmt = $db->prepare("SELECT DISTINCT p.drug_sn 
							  FROM patient_ap_services AS p 
							  INNER JOIN stock_table_inven AS inv ON inv.stock_sn = p.drug_sn 
							  WHERE p.paystatus = :paystatus 
							  AND p.drug_status = :drug_status 
							  AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id) 
							  $staff 
							  AND DATE(transact_date) BETWEEN :start AND :to");
                        $stmt->bindValue(':paystatus', $one, PDO::PARAM_INT);
                        $stmt->bindValue(':drug_status', $one, PDO::PARAM_INT);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT p.drug_sn 
							  FROM patient_ap_services AS p 
							  INNER JOIN stock_table_inven AS inv ON inv.stock_sn = p.drug_sn 
							  WHERE DATE(p.transact_date) = :setdate 
							  AND p.paystatus = :paystatus 
							  AND p.drug_status = :drug_status 
							  AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)
							  $staff");
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                        $stmt->bindValue(':paystatus', $one, PDO::PARAM_INT);
                        $stmt->bindValue(':drug_status', $one, PDO::PARAM_INT);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                    }

                    $stmt->execute();

                    if ($stmt->rowCount() > 0) { ?>

                        <?php $t_pos = 0;
                        $t_nhis = 0;
                        $t_pvt = 0;
                        $t_claim = 0;
                        $t_nhis_90 = 0;
                        $pos = 0;
                        $nhis = 0;
                        $pvt = 0;
                        $claim = 0;
                        $nhis_90 = 0;
                        $qty = 0;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                            <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                <thead>
                                    <tr bgcolor="#CCCCCC">
                                        <th width="7%">Invoice # </th>
                                        <th width="7%">Hospital # </th>
                                        <th width="7%">Qty </th>
                                        <th width="7%">Claim </th>
                                        <th width="7%">Amt Paid </th>
                                        <th width="7%">Date </th>
                                        <th width="7%">Entered/Disp. by </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php

                                    $drug_sn = $row['drug_sn'];    ///// target 
                                    if ($ddset == 1) {
                                        $stmtx = $db->prepare("SELECT 
								p.item_services,
								p.date_entry,
								p.prepared_by,
								p.invoice_no,
								p.dsp_by,
								p.hospital_no,
								p.qty AS p_qty,
								p.serv_group,
								p.claim_amt,
								p.pay 
							  FROM patient_ap_services AS p 
							  INNER JOIN stock_table_inven AS inv ON p.sn = inv.sale_sn 
							  WHERE p.paystatus = :paystatus 
							  AND p.drug_status = :drug_status 
							  AND p.drug_sn = :drug_sn 
							  AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)
							  $staff 
							  AND DATE(transact_date) BETWEEN :start AND :to 
							  ORDER BY hospital_no");
                                        $stmtx->bindValue(':paystatus', $one, PDO::PARAM_INT);
                                        $stmtx->bindValue(':drug_status', $one, PDO::PARAM_INT);
                                        $stmtx->bindValue(':drug_sn', $drug_sn, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':start', $start, PDO::PARAM_STR);
                                        $stmtx->bindValue(':to', $to, PDO::PARAM_STR);
                                    } else {
                                        $stmtx = $db->prepare("SELECT 
								p.item_services,
								p.date_entry,
								p.prepared_by,
								p.invoice_no,
								p.dsp_by,
								p.hospital_no,
								p.qty AS p_qty,
								p.serv_group,
								p.claim_amt,
								p.pay 
							  FROM patient_ap_services AS p 
							  INNER JOIN stock_table_inven AS inv ON p.sn = inv.sale_sn 
							  WHERE DATE(p.transact_date) = :setdate 
							  AND p.drug_status = :drug_status 
							  AND p.paystatus = :paystatus 
							  AND p.drug_sn = :drug_sn 
							  AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id) 
							  $staff 
							  ORDER BY hospital_no");
                                        $stmtx->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                                        $stmtx->bindValue(':drug_status', $one, PDO::PARAM_INT);
                                        $stmtx->bindValue(':paystatus', $one, PDO::PARAM_INT);
                                        $stmtx->bindValue(':drug_sn', $drug_sn, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                    }

                                    $stmtx->execute();
                                    if ($stmtx->rowCount() > 0) {
                                        //if(mysql_num_rows($rstSelect2)>0){ 
                                    ?>
                                        <?php  // $pos=0;  $nhis=0;      
                                        while ($roww = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                                            $qty = $qty + $roww['p_qty'];
                                            if ($roww['serv_group'] == 'EX') {
                                                $pos = $pos + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] > 0) {
                                                $nhis = $nhis + $roww['pay'];
                                                $nhis_90 = $nhis_90 + $roww['claim_amt'];
                                            }
                                            if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX') {
                                                $pvt = $pvt + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
                                                $claim = $claim + $roww['claim_amt'];
                                            }
                                            $drug_name = $roww['item_services'];
                                            //$d_qty=$roww['d_qty'];
                                        ?>
                                            <tr class="record">
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['invoice_no']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['hospital_no']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['p_qty']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo date('d M,Y', strtotime($roww['date_entry'])); ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
                                            </tr>

                                    <?php     }
                                    } ?>

                                    <tr class="record">
                                        <td style="border-bottom: 1px solid #ddd;"></td>
                                        <td colspan="2" style="border-bottom: 1px solid #ddd;"><?php
                                                                                                echo '<table width="100%"><tr><td colspan="2"><b>' . $drug_name . '</b></td></tr>';
                                                                                                echo '<tr><td colspan="2">' . $qty . ' - Qty Dispensed' . '</td></tr></table>';
                                                                                                ?>
                                        </td>
                                        <td colspan="2" style="border-bottom: 1px solid #ddd;"><?php
                                                                                                echo '<table width="100%"><tr><td> <div align="right"><b>' . 'Sales: ' . '</b></div></td><td>' . ' N' . number_format($pos, 2, '.', ',') . '</td></tr>';
                                                                                                echo '<tr><td> <div align="right"><b>' . 'Claim: ' . '</b></div></td><td>' . ' N' . number_format($claim, 2, '.', ',') . '</td></tr></table>';
                                                                                                ?>
                                        </td>
                                        <td colspan="2" style="border-bottom: 1px solid #ddd;"><?php
                                                                                                echo '<table width="100%"><tr><td> <div align="right"><b>' . 'NHIS(10%) Paid: ' . '</b></div></td><td>' . ' N' . number_format($nhis, 2, '.', ',') . '</td></tr>';
                                                                                                echo '<tr><td> <div align="right"><b>' . 'NHIS(90%) Billed: ' . '</b></div></td><td>' . ' N' . number_format($nhis_90, 2, '.', ',') . '</td></tr>';
                                                                                                echo '<tr><td> <div align="right"><b>' . 'Private Paid: ' . '</b></div></td><td>' . ' N' . number_format($pvt, 2, '.', ',') . '</td></tr></table>';
                                                                                                ?>
                                        </td>

                                    </tr>

                                    <?php
                                    $t_pos = $t_pos + $pos;
                                    $t_nhis = $t_nhis + $nhis;
                                    $t_nhis_90 = $t_nhis_90 + $nhis_90;
                                    $t_pvt = $t_pvt + $pvt;
                                    $t_claim = $t_claim + $claim;
                                    $t_qty = $t_qty + $qty;
                                    $pos = 0;
                                    $nhis = 0;
                                    $pvt = 0;
                                    $claim = 0;
                                    $nhis_90 = 0;
                                    $qty = 0;
                                    ?>

                                </tbody>
                            </table>
                        <?php } ?>
                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

                    <?php
                    } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'doc_pres') {

                    /// PATIENT SEEN
                    $setdate = date('Y-m-d');

                    if ($ddset == 1) {
                        //echo 'here';
                        $stmt = $db->prepare("SELECT DISTINCT p.hospital_no 
									FROM patient_ap_services AS p 
									WHERE (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id) AND DATE(p.date_entry) BETWEEN :start AND :to");
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT p.hospital_no 
									FROM patient_ap_services AS p 
									WHERE (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)
									AND DATE(p.date_entry) = :setdate");
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    if ($stmt->rowCount() > 0) { ?>

                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th>Hospital #</th>
                                    <th>Dispense Drugs</th>
                                    <th>Not Dispense Drugs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $dispense_drugs = '';
                                $Non_dispense_drugs = '';
                                $dispensed_count = [];
                                $non_dispensed_count = [];


                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $hospital_no = $row['hospital_no'];   ///// target 

                                    if ($ddset == 1) {
                                        $stmtx = $db->prepare("SELECT * FROM patient_ap_services 
								   WHERE hospital_no = :hospital_no 
								   AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
								   AND DATE(date_entry) BETWEEN :start AND :to");
                                        $stmtx->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':start', $start, PDO::PARAM_STR);
                                        $stmtx->bindValue(':to', $to, PDO::PARAM_STR);
                                    } else {
                                        $stmtx = $db->prepare("SELECT * FROM patient_ap_services 
								   WHERE hospital_no = :hospital_no 
								   AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
								   AND DATE(date_entry) = :setdate");
                                        $stmtx->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                                    }

                                    $stmtx->execute();
                                    if ($stmtx->rowCount() > 0) { ?>
                                        <?php


                                        while ($roww = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                                            if ($roww['drug_status'] == 1) {
                                                // Append to the dispense drugs string
                                                $dispense_drugs .= $roww['item_services'] . ' by <b>' . $roww['dsp_by'] . '</b><br>';

                                                // Count the dispensed items
                                                if (!isset($dispensed_count[$roww['item_services']])) {
                                                    $dispensed_count[$roww['item_services']] = 0;
                                                }
                                                $dispensed_count[$roww['item_services']]++;
                                            } else {
                                                // Append to the non-dispense drugs string
                                                $Non_dispense_drugs .= $roww['item_services'] . '<br>';

                                                // Count the non-dispensed items
                                                if (!isset($non_dispensed_count[$roww['item_services']])) {
                                                    $non_dispensed_count[$roww['item_services']] = 0;
                                                }
                                                $non_dispensed_count[$roww['item_services']]++;
                                            }
                                        }

                                        // Summary of dispensed items
                                        // Prepare the summary table
                                        $summary_table = '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
                                        $summary_table .= '<tr><th>No.</th><th>Name of Drug</th><th>Total Dispense</th><th>Total Not Dispense</th></tr>';

                                        // Combine the counts into a single array for display
                                        $all_items = array_unique(array_merge(array_keys($dispensed_count), array_keys($non_dispensed_count)));
                                        $sn = 1;
                                        foreach ($all_items as $item) {
                                            $dispensed_total = isset($dispensed_count[$item]) ? $dispensed_count[$item] : 0;
                                            $non_dispensed_total = isset($non_dispensed_count[$item]) ? $non_dispensed_count[$item] : 0;

                                            $summary_table .= '<tr>';
                                            $summary_table .= '<td>' . $sn++ . '</td>';
                                            $summary_table .= '<td>' . htmlspecialchars($item) . '</td>';
                                            $summary_table .= '<td>' . $dispensed_total . '</td>';
                                            $summary_table .= '<td>' . $non_dispensed_total . '</td>';
                                            $summary_table .= '</tr>';
                                        }

                                        $summary_table .= '</table>';

                                        ?>
                                        <tr class="record">
                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $dispense_drugs; ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php echo $Non_dispense_drugs; ?></td>
                                        </tr>
                                <?php
                                        $dispense_drugs = '';
                                        $Non_dispense_drugs = '';
                                    }
                                }





                                ?>

                            </tbody>
                        </table>

                    <?php
                        echo '<hr><h3>Summary:</h3>';
                        echo $summary_table . '<br>';
                    } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'ps') {


                    /// PATIENT SEEN
                    $setdate = date('Y-m-d');

                    if ($ddset == 1) {
                        $stmt = $db->prepare("SELECT DISTINCT p.hospital_no 
									FROM patient_ap_services AS p 
									WHERE p.drug_status = :drug_status 
									AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)  
									$staff 
									AND DATE(p.date_entry) BETWEEN :start AND :to");
                        $stmt->bindValue(':drug_status', $one, PDO::PARAM_INT);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT p.hospital_no 
									FROM patient_ap_services AS p 
									WHERE p.drug_status = :drug_status 
									AND (p.dept_id = :dept_id or p.dept_dispensory_id = :dept_dispensory_id)
									AND DATE(p.date_entry) = :setdate 
									$staff");
                        $stmt->bindValue(':drug_status', $one, PDO::PARAM_INT);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    if ($stmt->rowCount() > 0) { ?>

                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th>Hospital #</th>
                                    <th>Drug Name</th>
                                    <th>Qty</th>
                                    <th>Claim</th>
                                    <th>Amount</th>
                                    <th>Entered/Disp.By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $t_pos = 0;
                                $t_nhis = 0;
                                $t_pvt = 0;
                                $t_claim = 0;
                                $t_nhis_90 = 0;
                                $t_pay_credit = 0;
                                $pos = 0;
                                $nhis = 0;
                                $pvt = 0;
                                $claim = 0;
                                $nhis_90 = 0;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $hospital_no = $row['hospital_no'];   ///// target 
                                    if ($ddset == 1) {
                                        $stmtx = $db->prepare("SELECT * FROM patient_ap_services 
								   WHERE drug_status = :drug_status 
								   AND hospital_no = :hospital_no 
								   AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
								   $staff 
								   AND DATE(date_entry) BETWEEN :start AND :to");
                                        $stmtx->bindValue(':drug_status', $one, PDO::PARAM_INT);
                                        $stmtx->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':start', $start, PDO::PARAM_STR);
                                        $stmtx->bindValue(':to', $to, PDO::PARAM_STR);
                                    } else {
                                        $stmtx = $db->prepare("SELECT * FROM patient_ap_services 
								   WHERE drug_status = :drug_status 
								   AND hospital_no = :hospital_no 
								   AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
								   AND DATE(date_entry) = :setdate 
								   $staff");
                                        $stmtx->bindValue(':drug_status', $one, PDO::PARAM_INT);
                                        $stmtx->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                                    }

                                    $stmtx->execute();
                                    if ($stmtx->rowCount() > 0) {
                                        //if(mysql_num_rows($rstSelect2)>0){ 
                                ?>
                                        <?php  // $pos=0;  $nhis=0;      
                                        while ($roww = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                                            $qty = $qty + $roww['qty'];

                                            if ($roww['serv_group'] == 'EX' and $roww['paystatus'] == 1) {
                                                $pos = $pos + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] > 0 and $roww['paystatus'] == 1) {
                                                $nhis = $nhis + $roww['pay'];
                                                $nhis_90 = $nhis_90 + $roww['claim_amt'];
                                            }
                                            if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX' and $roww['paystatus'] == 1) {
                                                $pvt = $pvt + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] == 0 and $roww['paystatus'] == 1) {
                                                $claim = $claim + $roww['claim_amt'];
                                            }
                                            if ($roww['drug_status'] == 1 and $roww['paystatus'] == 0) {
                                                $pay_credit = $pay_credit + $roww['pay'];
                                            }
                                        ?>
                                            <tr class="record">
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>

                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
                                            </tr>
                                        <?php }
                                        // end of looping create summary
                                        ?>
                                        <tr class="record">
                                            <td style="border-bottom: 1px solid #ddd;"></td>
                                            <td style="border-bottom: 1px solid #ddd;"><strong>Patient Sub Total:</strong></td>
                                            <td style="border-bottom: 1px solid #ddd;"></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($pay_credit > 0) {
                                                                                            echo '<b>Dispense on Credit</b><br>' . 'N ' . number_format($pay_credit, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($pos > 0) {
                                                                                            echo '<b>POS (External Sales)</b><br>' . 'N ' . number_format($pos, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($nhis > 0) {
                                                                                            echo '<b>NHIS (10% Payable)</b><br>' . 'N ' . number_format($nhis, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($pvt > 0) {
                                                                                            echo '<b>Private Sales</b><br>' . 'N ' . number_format($pvt, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($claim > 0) {
                                                                                            echo '<b>Claim(100% Insurance Bills)</b><br>' . 'N ' . number_format($claim, 2, '.', ',');
                                                                                        } ?></td>
                                        </tr>
                                    <?php } ?>

                                <?php
                                    $t_pos = $t_pos + $pos;
                                    $t_nhis = $t_nhis + $nhis;
                                    $t_nhis_90 = $t_nhis_90 + $nhis_90;
                                    $t_pvt = $t_pvt + $pvt;
                                    $t_claim = $t_claim + $claim;
                                    $t_pay_credit = $t_pay_credit + $pay_credit;
                                    $pos = 0;
                                    $nhis = 0;
                                    $pvt = 0;
                                    $pvt = 0;
                                    $claim = 0;
                                    $pay_credit = 0;
                                } ?>

                            </tbody>
                        </table>
                        <div align="right">
                            <div style="font-size:18px"><b>Summary:</b></div>
                            <table width="" cellpadding="3">
                                <tr>
                                    <td width="">Dispensed On Credit</td>
                                    <td width="">
                                        <div align="right"><strong><?php echo number_format($t_pay_credit, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                                <td width="">External Sale (POS)</td>
                                <td width="">
                                    <div align="right"><strong><?php echo number_format($t_pos, 2, '.', ','); ?></strong></div>
                                </td>
                                </tr>
                                <tr>
                                    <td width="">Private Patient Sales:</td>
                                    <td width="">
                                        <div align="right"><strong><?php echo number_format($t_pvt, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="">NHIS (10% Payable/Recieved)</td>
                                    <td width="25%">
                                        <div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="">NHIS (90% Claim)</td>
                                    <td width="25%">
                                        <div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="">Total Claim/Insurance Bills</td>
                                    <td width="25%">
                                        <div align="right"><strong><?php echo number_format($t_claim, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

                    <?php } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'dcr') {

                    /// PATIENT SEEN
                    $setdate = date('Y-m-d');

                    if ($ddset == 1) {
                        $stmt = $db->prepare("SELECT DISTINCT hospital_no 
							  FROM patient_ap_services 
							  WHERE cr = :cr 
							  AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
							  $staff 
							  AND paystatus = :paystatus 
							  AND DATE(date_entry) BETWEEN :start AND :to");
                        $stmt->bindValue(':cr', '1', PDO::PARAM_STR);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':paystatus', '0', PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT hospital_no 
							  FROM patient_ap_services 
							  WHERE cr = :cr 
							  AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
							  $staff 
							  AND paystatus = :paystatus 
							  AND DATE(date_entry) = :setdate");
                        $stmt->bindValue(':cr', '1', PDO::PARAM_STR);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':paystatus', '0', PDO::PARAM_STR);
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                    }

                    $stmt->execute();

                    if ($stmt->rowCount() > 0) { ?>
                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th>A/P#</th>
                                    <th>Hospital #</th>
                                    <th>Dept</th>
                                    <th>Drug Name</th>
                                    <th>Qty</th>
                                    <th>Claim</th>
                                    <th>Amount</th>
                                    <th>Entered/Disp.by</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $t_pos = 0;
                                $t_nhis = 0;
                                $t_pvt = 0;
                                $t_claim = 0;
                                $t_nhis_90 = 0;
                                $t_pay_credit = 0;
                                $pay_credit = 0;
                                $pos = 0;
                                $nhis = 0;
                                $pvt = 0;
                                $claim = 0;
                                $nhis_90 = 0;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $hospital_no = $row['hospital_no'];

                                    if ($ddset == 1) {
                                        $stmtx = $db->prepare("SELECT * FROM patient_ap_services 
													   WHERE cr = :cr 
													   AND paystatus = :paystatus 
													   AND hospital_no = :hospital_no 
													   AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id)
													   $staff 
													   AND DATE(date_entry) BETWEEN :start AND :to");
                                        $stmtx->bindValue(':cr', '1', PDO::PARAM_STR);
                                        $stmtx->bindValue(':paystatus', '0', PDO::PARAM_STR);
                                        $stmtx->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':start', $start, PDO::PARAM_STR);
                                        $stmtx->bindValue(':to', $to, PDO::PARAM_STR);
                                    } else {
                                        $stmtx = $db->prepare("SELECT * FROM patient_ap_services 
													   WHERE cr = :cr 
													   AND paystatus = :paystatus 
													   AND hospital_no = :hospital_no 
													   AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
													   $staff 
													   AND DATE(date_entry) = :setdate");
                                        $stmtx->bindValue(':cr', '1', PDO::PARAM_STR);
                                        $stmtx->bindValue(':paystatus', '0', PDO::PARAM_STR);
                                        $stmtx->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                        $stmtx->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                                    }

                                    $stmtx->execute();
                                    if ($stmtx->rowCount() > 0) {

                                        while ($roww = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                                            $qty = $qty + $roww['qty'];
                                            if ($roww['serv_group'] == 'EX') {
                                                $pos = $pos + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] > 0) {
                                                $nhis = $nhis + $roww['pay'];
                                                $nhis_90 = $nhis_90 + $roww['claim_amt'];
                                            }
                                            if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX') {
                                                $pvt = $pvt + $roww['pay'];
                                            }
                                            if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
                                                $claim = $claim + $roww['claim_amt'];
                                            }
                                ?>
                                            <tr class="record">
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['serv_group']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
                                            </tr>
                                        <?php }
                                        // end of looping create summary
                                        ?>
                                        <tr class="record">
                                            <td style="border-bottom: 1px solid #ddd;"></td>
                                            <td style="border-bottom: 1px solid #ddd;"></td>
                                            <td style="border-bottom: 1px solid #ddd;"></td>
                                            <td style="border-bottom: 1px solid #ddd;"><strong>Patient Sub Total:</strong></td>
                                            <td style="border-bottom: 1px solid #ddd;"></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($pay_credit > 0) {
                                                                                            echo '<b>Dispense on Credit</b><br>' . 'N ' . number_format($pay_credit, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($pos > 0) {
                                                                                            echo '<b>POS (External Credit)</b><br>' . 'N ' . number_format($pos, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($nhis > 0) {
                                                                                            echo '<b>NHIS (10% Payable)</b><br>' . 'N ' . number_format($nhis, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($pvt > 0) {
                                                                                            echo '<b>Private Credit </b><br>' . 'N ' . number_format($pvt, 2, '.', ',');
                                                                                        } ?></td>
                                            <td style="border-bottom: 1px solid #ddd;"><?php if ($claim > 0) {
                                                                                            echo '<b>Claim(100% Insurance Bills)</b><br>' . 'N ' . number_format($claim, 2, '.', ',');
                                                                                        } ?></td>
                                        </tr>
                                    <?php } ?>

                                <?php
                                    $t_pos = $t_pos + $pos;
                                    $t_nhis = $t_nhis + $nhis;
                                    $t_nhis_90 = $t_nhis_90 + $nhis_90;
                                    $t_pvt = $t_pvt + $pvt;
                                    $t_claim = $t_claim + $claim;
                                    $t_pay_credit = $t_pay_credit + $pay_credit;
                                    $pos = 0;
                                    $nhis = 0;
                                    $pvt = 0;
                                    $pvt = 0;
                                    $claim = 0;
                                    $pay_credit = 0;
                                } ?>

                            </tbody>
                        </table>
                        <div align="right">
                            <div style="font-size:18px"><b>Summary:</b></div>
                            <table width="" cellpadding="3">
                                <tr>
                                    <td width="">Dispensed On Credit</td>
                                    <td width="">
                                        <div align="right"><strong><?php echo number_format($t_pay_credit, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                                <td width="">External Sale (POS)</td>
                                <td width="">
                                    <div align="right"><strong><?php echo number_format($t_pos, 2, '.', ','); ?></strong></div>
                                </td>
                                </tr>
                                <tr>
                                    <td width="">Private Patients :</td>
                                    <td width="">
                                        <div align="right"><strong><?php echo number_format($t_pvt, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="">NHIS (10% Payable/Recieved)</td>
                                    <td width="25%">
                                        <div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="">NHIS (90% Claim)</td>
                                    <td width="25%">
                                        <div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="">Total Claim/Insurance Bills</td>
                                    <td width="25%">
                                        <div align="right"><strong><?php echo number_format($t_claim, 2, '.', ','); ?></strong></div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

                        <?php } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'inv') {


                    /// PATIENT SEEN
                    $setdate = date('Y-m-d');

                    if ($ddset == 1) {
                        $stmt = $db->prepare("SELECT DISTINCT drug_sn, item_services 
									  FROM patient_ap_services 
									  WHERE serv_group = :serv_group 
									  AND drug_status = :drug_status 
									  $staff 
									  AND DATE(date_entry) BETWEEN :start AND :to");
                        $stmt->bindValue(':serv_group', 'Pharmacy', PDO::PARAM_STR);
                        $stmt->bindValue(':drug_status', '1', PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT drug_sn, item_services 
									  FROM patient_ap_services 
									  WHERE DATE(date_entry) = :setdate 
									  AND serv_group = :serv_group 
									  AND drug_status = :drug_status 
									  $staff");
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                        $stmt->bindValue(':serv_group', 'Pharmacy', PDO::PARAM_STR);
                        $stmt->bindValue(':drug_status', '1', PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $dn = 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                            $product_sn = $row['drug_sn'];
                            $item_services = $row['item_services'];

                            if ($ddset == 1) {
                                $stmtx = $db->prepare("SELECT * FROM stock_table_inven 
												  WHERE stock_sn = :product_sn 
												  $staff2 
												  AND DATE(captured_date) BETWEEN :start AND :to 
												  ORDER BY sn");
                                $stmtx->bindValue(':product_sn', $product_sn, PDO::PARAM_STR);
                                $stmtx->bindValue(':start', $start, PDO::PARAM_STR);
                                $stmtx->bindValue(':to', $to, PDO::PARAM_STR);
                            } else {
                                // Assuming $setdate is defined earlier
                                $stmtx = $db->prepare("SELECT * FROM stock_table_inven 
												  WHERE stock_sn = :product_sn 
												  $staff2 
												  AND DATE(captured_date) = :setdate 
												  ORDER BY sn");
                                $stmtx->bindValue(':product_sn', $product_sn, PDO::PARAM_STR);
                                $stmtx->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                            }

                            $stmtx->execute();

                            if ($stmtx->rowCount() > 0) { ?>
                                <?php
                                //   $roww=mysql_fetch_array($rstSelect2);
                                echo  '<strong style="font-size:16px">' . $dn . '. ' . $item_services . '</strong>';
                                ?>

                                <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                    <thead>
                                        <tr bgcolor="#CCCCCC">
                                            <th>#</th>
                                            <th>Description</th>
                                            <th>Batch No</th>
                                            <th>IN</th>
                                            <th>OUT</th>
                                            <th>Balance</th>
                                            <th>Date</th>
                                            <th>Dispensed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $n = 1;
                                        while ($roww = $stmtx->fetch(PDO::FETCH_ASSOC)) {

                                            if ($colordecide % 2 == 0) {
                                                $bgcolor = "#F4F4F4";
                                            } else {
                                                $bgcolor = "#FFFFFF";
                                            } ?>
                                            <tr class="record">
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $n;; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['inven_desc']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['batch']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qtyIN']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qtyOUT']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['bal']; ?></td>

                                                <td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['captured_date'])); ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['enter_by']; ?></td>
                                            </tr>
                                        <?php $colordecide++;
                                            $n++;
                                        }
                                        ?>
                                    </tbody>
                                </table><br>
                        <?php

                            }
                            $dn++;
                        }
                        ?>

                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

                    <?php

                    } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'd_r') {

                    /// PATIENT SEEN
                    $setdate = date('Y-m-d');

                    if ($ddset == 1) {
                        $stmt = $db->prepare("SELECT * FROM patient_ap_services 
							 WHERE drug_status = :drug_status 
							 AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id)
							 $staff 
							 AND DATE(date_entry) BETWEEN :start AND :to 
							 ORDER BY item_services");
                        $stmt->bindValue(':drug_status', '1', PDO::PARAM_STR);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                        $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                    } else {
                        // Assuming $setdate is defined earlier
                        $stmt = $db->prepare("SELECT * FROM patient_ap_services 
							 WHERE drug_status = :drug_status 
							 AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
							 $staff 
							 AND DATE(date_entry) = :setdate 
							 ORDER BY item_services");
                        $stmt->bindValue(':drug_status', '1', PDO::PARAM_STR);
                        $stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    if ($stmt->rowCount() > 0) { ?>

                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th>A/P#</th>
                                    <th>Hospital #</th>
                                    <th>Dept</th>
                                    <th>Drug Name</th>
                                    <th>Qty</th>
                                    <th>Claim</th>
                                    <th>Amount</th>
                                    <th>Entered/Disp by</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                    <tr class="record">
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['hospital_no']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['serv_group']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
                                    </tr>
                                <?php }  ?>
                            </tbody>
                        </table>

                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>
                        <?php
                    } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'inv_drug') {

                    /// PATIENT SEEN
                    $setdate = date('Y-m-d');
                    $stmt = $db->prepare("SELECT sn, product_name, qty, reorder_level, expire_date FROM stock_table WHERE sn = :searchdrug_inv");
                    $stmt->bindValue(':searchdrug_inv', $searchdrug_inv, PDO::PARAM_STR);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $product_sn = $row['sn'];
                            $item_services = $row['product_name'];


                            if ($ddset == 1) {
                                $stmt = $db->prepare("SELECT * FROM stock_table_inven 
													  WHERE stock_sn = :product_sn 
													  AND DATE(captured_date) BETWEEN :start AND :to 
													  ORDER BY sn");
                                $stmt->bindValue(':product_sn', $product_sn, PDO::PARAM_STR);
                                $stmt->bindValue(':start', $start, PDO::PARAM_STR);
                                $stmt->bindValue(':to', $to, PDO::PARAM_STR);
                            } else {
                                $stmt = $db->prepare("SELECT * FROM stock_table_inven 
													  WHERE stock_sn = :product_sn 
													  AND DATE(captured_date) = :setdate 
													  ORDER BY sn");
                                $stmt->bindValue(':product_sn', $product_sn, PDO::PARAM_STR);
                                $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
                            }

                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {  ?>
                                <?php
                                echo  '<strong style="font-size:16px">' . $dn . '. ' . $item_services . '</strong>';
                                ?>

                                <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                    <thead>
                                        <tr bgcolor="#CCCCCC">
                                            <th>#</th>
                                            <th>Description</th>
                                            <th>Batch No</th>
                                            <th>IN</th>
                                            <th>OUT</th>
                                            <th>Balance</th>
                                            <th>Date</th>
                                            <th>Dispensed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $n = 1;
                                        while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            if ($colordecide % 2 == 0) {
                                                $bgcolor = "#F4F4F4";
                                            } else {
                                                $bgcolor = "#FFFFFF";
                                            } ?>
                                            <tr class="record">
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $n;; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['inven_desc']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['batch']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qtyIN']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qtyOUT']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $bal = $roww['bal']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['captured_date'])); ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['enter_by']; ?></td>
                                            </tr>
                                        <?php $colordecide++;
                                            $n++;
                                        }
                                        ?>
                                    </tbody>
                                </table><br>
                                <table cellpadding="5" cellspacing="5" border="0" width="100%">

                                    <tr class="record">
                                        <td style="border-bottom: 1px solid #ddd; font-size:18px"> Quantity: <?php echo $bal; ?></td>
                                        <td style="border-bottom: 1px solid #ddd; font-size:18px">Re-order Level: <?php echo $row['reorder_level']; ?></td>
                                        <td style="border-bottom: 1px solid #ddd; font-size:18px">Expiring Date: <?php //echo date('d,M Y', strtotime($row['expire_date'])); 
                                                                                                                    ?></td>

                                    </tr>
                                </table>

                        <?php


                            }
                        }
                        ?>


                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $_SESSION['fullname']; ?></strong>

                        <?php

                    } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                } elseif ($rpt_type == 'rhmo') {


                    if (isset($_POST["hmo_nhis"]) and $_POST["hmo_nhis"] != '') {
                        $Tclaim_hmo = 0;
                        $Tpay_hmo = 0;

                        foreach ($_POST["hmo_nhis"] as $hmo_nhis_no) {
                            $parts = explode("__", $hmo_nhis_no);
                            $hmo_nhis_no = $parts[0];
                            $hmo_name = $parts[1];

                            if ($ddset == 1) {
                                $search_date = " and date(ap.date_entry) between '$start' AND '$to'";
                            } else {
                                $search_date = " and date(ap.date_entry)='$setdate'";
                            }

                            $stt = $db->prepare("SELECT 
	enl.hospital_no, ap.* 
	FROM enrollee AS enl 
	INNER JOIN patient_ap_services AS ap ON ap.hospital_no=enl.hospital_no 
	WHERE enl.hmo_no=:hmo_no and enl.insurance=:insur_type and (ap.dept_id = :dept_id or ap.dept_dispensory_id = :dept_dispensory_id) and ap.drug_status='1' $search_date");
                            $stt->bindValue(':hmo_no', $hmo_nhis_no, PDO::PARAM_STR);
                            $stt->bindValue(':insur_type', $hmo_type, PDO::PARAM_STR);
                            $stt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                            $stt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                            $stt->execute();
                            if ($stt->rowCount() > 0) {

                        ?>

                                <h3><?php echo $hmo_name; ?></h3>
                                <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                    <thead>
                                        <tr bgcolor="#CCCCCC">
                                            <th>A/P#</th>
                                            <th>Hospital #</th>
                                            <th>Drug Name</th>
                                            <th>Qty</th>
                                            <th>Claim</th>
                                            <th>Amount</th>
                                            <th>Entered/Disp by</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        while ($roww = $stt->fetch(PDO::FETCH_ASSOC)) {

                                            $claim_hmo = $claim_hmo + $roww['claim_amt'];
                                            $Tclaim_hmo = $Tclaim_hmo + $roww['claim_amt'];

                                            $pay_hmo = $pay_hmo + $roww['pay'];
                                            $Tpay_hmo = $Tpay_hmo + $roww['pay'];

                                        ?>
                                            <tr class="record">
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['hospital_no']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
                                                <td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
                                            </tr>
                                        <?php
                                            //$claim_hmo=0;
                                            //$pay_hmo=0;	
                                        }  ?>
                                    </tbody>
                                </table>

                                <h3><strong>Total Claim:</strong> <?php echo number_format($claim_hmo, 2); ?> &nbsp;|&nbsp; <strong>Cash Recieved: </strong><?php echo number_format($pay_hmo, 2); ?> </h3>
                                <br>

                            <?php

                            }
                            $claim_hmo = 0;
                            $pay_hmo = 0;
                        }

                        if ($Tclaim_hmo > 0) { ?>
                            <hr>
                            <h2>Grand Total (Claim): <?php echo number_format($Tclaim_hmo, 2); ?></h2>
                            <h2>Grand Total (Paid Amount): <?php echo number_format($Tpay_hmo, 2); ?></h2>
                        <?php } ?>

                    <?php } else {
                        echo 'Invalid selection';
                    }
                } elseif ($rpt_type == 'visit') {

                    $setdate = date('Y-m-d');

                    if ($ddset == 1) {
                    } else {
                        $start = $setdate;
                        $to = $setdate;
                    }
                    $drug_status = '1';
                    $TotalP_claim = 0;
                    $Tp_pay = 0;
                    $Tvisit = 0;
                    ///	$dept_id='Pharmacy';
                    //	echo '------'. $dept_id;

                    $stt = $db->prepare("SELECT DISTINCT hospital_no FROM patient_ap_services WHERE drug_status=:drug_status and (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) $staff and date(date_entry) between '$start' AND '$to' order by item_services");
                    $stt->bindValue(':drug_status', $drug_status, PDO::PARAM_STR);
                    $stt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                    $stt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                    $stt->execute();
                    if ($stt->rowCount() > 0) {    ?>



                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th>Hospital #</th>
                                    <th>Total Visit</th>
                                    <th>Claim</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                while ($roww = $stt->fetch(PDO::FETCH_ASSOC)) {
                                    $hospital_no = $roww['hospital_no'];
                                    $stt2 = $db->prepare("SELECT 
	sum(claim_amt) as P_claim, 
	sum(pay) as p_pay 
	FROM patient_ap_services 
	WHERE drug_status=:drug_status and (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) and hospital_no=:hospital_no $staff and date(date_entry) between '$start' AND '$to' order by item_services");
                                    $stt2->bindValue(':drug_status', $drug_status, PDO::PARAM_STR);
                                    $stt2->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                    $stt2->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                    $stt2->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                    $stt2->execute();
                                    $rowwx = $stt2->fetch(PDO::FETCH_ASSOC);


                                    ////// total visit

                                    $stt_visit = $db->prepare("SELECT DISTINCT app_no FROM patient_ap_services 
		WHERE hospital_no = :hospital_no 
		AND drug_status = :drug_status 
		AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id)
		$staff 
		AND date(date_entry) BETWEEN :start AND :to 
		ORDER BY item_services");

                                    $stt_visit->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                    $stt_visit->bindValue(':drug_status', $drug_status, PDO::PARAM_STR);
                                    $stt_visit->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                    $stt_visit->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
                                    $stt_visit->bindValue(':start', $start, PDO::PARAM_STR); // Assuming $start is already formatted as 'Y-m-d'
                                    $stt_visit->bindValue(':to', $to, PDO::PARAM_STR); // Assuming $to is already formatted as 'Y-m-d'
                                    $stt_visit->execute();



                                    $TotalP_claim = $TotalP_claim + $rowwx['P_claim'];
                                    $Tp_pay = $Tp_pay + $rowwx['p_pay'];
                                    $Tvisit = $Tvisit + $stt_visit->rowCount();

                                ?>

                                    <tr class="record">
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $stt_visit->rowCount(); ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($rowwx['P_claim'], 2); ?></td>
                                        <td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($rowwx['p_pay'], 2); ?></td>
                                    </tr>
                                <?php }  ?>

                                <tr class="record">
                                    <td style="border-bottom: 1px solid #ddd;">&nbsp;</td>
                                    <td style="border-bottom: 1px solid #ddd;">&nbsp;</td>
                                    <td style="border-bottom: 1px solid #ddd;">&nbsp; </td>
                                </tr>
                                <tr class="record">
                                    <td style="border-bottom: 1px solid #ddd;"><strong>Total: <?php echo $stt->rowCount(); ?></strong></td>
                                    <td style="border-bottom: 1px solid #ddd;"><strong><?php echo $Tvisit; ?></strong></td>
                                    <td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($TotalP_claim, 2); ?></strong></td>
                                    <td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($Tp_pay, 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <br><br>
                        <div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
                        <strong style="font-size:14px;"><?php echo $staff_name; ?></strong>
                <?php
                    } else {
                        echo '<strong style="font-size:14px;">No Data Found</strong>';
                    }
                }


                ?>


                <div class="form_sep">
                    <div class="pull-left" style="margin-right:100px;">
                        <a href="mgt_rpt.php" style="font-size:20px;"><button class="btn btn-danger btn-large">Close</button></a>
                    </div>

                    <div class="pull-right" style="margin-right:100px;">
                        <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                    </div>
                </div>




            </div>

        </div>
    </div>

</div>