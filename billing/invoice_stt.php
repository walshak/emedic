<div class="ibox-title">
    <h5 style="color:#00F">List of Items Invoiced</h5>
</div>
<div class="ibox-content">
    <div class="row">

        <form action="stt_print_invoice.php" method="POST">


            <div class="col-md-12">



                <?php
                if (!isset($_GET['vsbl'])) { ?>
                    <table class="table table-striped" width="100%">
                        <thead>
                            <tr>
                                <th width="4%">#</th>
                                <th width="4%">Sel</th>
                                <th width="30%">Item</th>
                                <th width="6%" class="text-center">Qty</th>
                                <th width="10%" class="text-right">Amount</th>
                                <th width="8%" class="text-right">Discount</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="18%">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $emr = $_GET['emr'];
                            $cnt = 0;
                            $TotalTrans = 0;

                            $stmt = $db->query("SELECT * FROM invoice_temp2 WHERE hosp='$emr'");
                            if ($stmt->rowCount() > 0) {

                                $stmtt = $db->query("SELECT * FROM invoice_temp2 WHERE hosp='$emr' ORDER BY sn");
                                while ($roww = $stmtt->fetch(PDO::FETCH_ASSOC)) {

                                    $sale_no = $roww['sale_no'];
                                    $dsc = $roww['amt'];

                                    $stmtP = $db->query("SELECT sn, item_services, claim_amt, qty, invoice_no, pay, date_entry, cr, invoice_status, cat_type, serv_group 
                                     FROM patient_ap_services 
                                     WHERE sn='$sale_no' 
                                     ORDER BY cat_type");

                                    $row = $stmtP->fetch(PDO::FETCH_ASSOC);
                                    if (!$row) continue;

                                    $cnt++;
                                    $amt = ($row['pay'] > 0) ? $row['pay'] : $row['claim_amt'];
                                    $TotalTrans += $amt;
                                    $date_entry = date('d M, Y h:ia', strtotime($row['date_entry']));
                            ?>
                                    <tr>
                                        <td><?php echo $cnt; ?></td>
                                        <td>
                                            <input type="checkbox" checked
                                                value="<?php
                                                        echo $row['sn'] . '__' .
                                                            $row['pay'] . '__' .
                                                            $row['item_services'] . '__' .
                                                            $row['qty'] . '__' .
                                                            $date_entry . '__' .
                                                            $row['cr'] . '__' .
                                                            $row['cat_type'] . '__' .
                                                            $row['serv_group'] . '__' .
                                                            $dsc; ?>"
                                                name="inv[]" />
                                        </td>
                                        <td><?php echo htmlspecialchars($row['item_services']); ?></td>
                                        <td class="text-center"><?php echo $row['qty']; ?></td>
                                        <td class="text-right"><?php echo number_format($amt); ?></td>
                                        <td class="text-right"><?php if ($dsc > 0) echo number_format($dsc); ?></td>
                                        <td class="text-center">
                                            <?php if ($row['cr'] == 1) echo '<strong style="color:red;">CREDIT</strong>'; ?>
                                        </td>
                                        <td><?php echo $date_entry; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td class="text-right"><strong><?php echo number_format($TotalTrans); ?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-2">
                        <a href="pacct.php?emr=<?= $emr; ?>&pay" class="btn btn-danger btn-sm">
                            <i class="fa fa-times"></i> Close
                        </a>
                        &nbsp;
                        <button class="btn btn-info btn-sm" type="submit" name="print_invoice_stt">
                            <i class="fa fa-search"></i> Preview
                        </button>

                        <input type="hidden" name="type_of_print" value="simple">
                        <input type="hidden" name="emr" value="<?= $emr; ?>">
                    </div>

                <?php
                            } else {
                                echo '<div class="alert alert-warning">No Records to show</div>';
                            }
                        } else {

                            if (isset($_GET['search_datee']) && $_GET['search_datee'] != '') {
                                $search_datee = trim($_GET['search_datee']);

                                if (preg_match("/^AND DATE\(date_entry\) BETWEEN '(\d{4}-\d{2}-\d{2})' AND '(\d{4}-\d{2}-\d{2})'$/", $search_datee, $matches)) {
                                    $from_date = $matches[1];
                                    $to_date   = $matches[2];

                                    // Optional: extra check for valid calendar dates
                                    if (
                                        checkdate(substr($from_date, 5, 2), substr($from_date, 8, 2), substr($from_date, 0, 4)) &&
                                        checkdate(substr($to_date, 5, 2), substr($to_date, 8, 2), substr($to_date, 0, 4))
                                    ) {

                                        //   echo "Valid: From $from_date to $to_date";
                                    } else {
                                        echo "Invalid calendar date(s).";
                                    }
                                } else {
                                    echo "Invalid format.";
                                }
                            }




                ?>

                <table class="table table-striped" width="100%">
                    <tr>
                        <th width="4%">#</th>
                        <th width="4%">Sel</th>
                        <th width="30%">Item</th>
                        <th width="6%" class="text-center">Qty</th>
                        <th width="10%" class="text-right">Amount</th>
                        <th width="8%" class="text-right">Discount</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="18%">Date</th>
                    </tr>

                    <?php
                            $cnt = 0;
                            $TotalTrans = 0;

                            $stmtP = $db->query("SELECT sn, item_services, claim_amt, qty, invoice_no, pay, date_entry, cr, invoice_status, cat_type, serv_group 
                         FROM patient_ap_services 
                         WHERE hospital_no='$emr' AND paystatus=0 AND med_dosage_unit!=1 $search_datee 
                         ORDER BY date_entry");
                            while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {

                                $cnt++;
                                $amt = ($row['pay'] > 0) ? $row['pay'] : $row['claim_amt'];
                                $TotalTrans += $amt;
                                $date_entry = date('d-m-Y H:i:s a', strtotime($row['date_entry']));
                    ?>
                        <tr>
                            <td><?php echo $cnt; ?></td>
                            <td>
                                <input type="checkbox" checked
                                    value="<?php echo $row['sn'] . '__' . $row['pay'] . '__' . $row['item_services'] . '__' . $row['qty'] . '__' . $date_entry . '__' . $row['cr'] . '__' . $row['cat_type'] . '__' . $row['serv_group'] . '__' . $dsc; ?>"
                                    name="inv[]" />
                            </td>
                            <td><?php echo htmlspecialchars($row['item_services']); ?></td>
                            <td class="text-center"><?php echo $row['qty']; ?></td>
                            <td class="text-right"><?php echo number_format($amt); ?></td>
                            <td class="text-right"><?php if ($dsc > 0) echo number_format($dsc); ?></td>
                            <td class="text-center">
                                <?php if ($row['cr'] == 1) echo '<strong style="color:red;">CREDIT</strong>'; ?>
                            </td>
                            <td><?php echo date('d M, Y h:ia', strtotime($row['date_entry'])); ?></td>
                        </tr>
                    <?php } ?>
                </table>


                <button class="btn btn-info btn-sm" type="submit" name="print_invoice_stt" id=""><i class="fa fa-search"></i>&nbsp; Preview</button>
                &nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp
                <a href="pacct.php?emr=<?php echo $emr . '&pay'; ?>"> Reset</a>


                <input type="hidden" value="<?php echo $emr; ?>" name="emr">
                <input type="hidden" value="adv" name="type_of_print">
                <input type="hidden" value="<?php echo $start . '__' . $end; ?>" name="dates">

            <?php } ?>
        </form>

    </div>


    <?php if (isset($_POST['print_ap'])) {
        include("stt_print.php");
    } ?>