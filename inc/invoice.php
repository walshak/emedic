   <?php

    if (isset($_POST['apply_approve']) || isset($_POST['apply_processed_inv'])) {
        $from_date = $_POST['start'];
        $to_date = $_POST['end'];
    } else {
        // Default to today's date
        $today = date("Y-m-d");
        $from_date = $to_date = $today;

        // Check if patient is still admitted or was discharged today
        $stmt = $db->prepare("
        SELECT date_admit 
        FROM admission 
        WHERE hospital_no = :emr 
        AND (adm_status = '3' OR DATE(date_discharge) = :today)
        LIMIT 1");
        $stmt->bindParam(':emr', $emr);
        $stmt->bindParam(':today', $today);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $from_date = date('Y-m-d', strtotime($row['date_admit']));
            // to_date remains today's date
        }
    }
    echo "<script>document.title = 'Invoice/Claims - " . htmlspecialchars($patient_name) . "';</script>";

    ?>

   <div class="ibox-title">
       <h5>INVOICE/CLAIMS: </h5>
   </div>
   <div class="ibox-content">
       <div class="row">


           <div class="col-md-12">

               <form action="pacct.php?emr=<?= $emr; ?>&inv" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">

                   <div class="col-md-4">
                       <div class="ibox float-e-margins">
                           <label for="reg_input_no" class="">Set Dates Range & Click Apply button </label><br>
                           <div class="form_sep" id="">
                               <div class="input-daterange input-group" id="">
                                   <input type="date" class="input-sm form-control" name="start" value="<?php echo $from_date; ?>" />
                                   <span class="input-group-addon">to</span>
                                   <input type="date" class="input-sm form-control" name="end" value="<?php echo $to_date; ?>" />
                               </div>
                           </div>
                       </div>
                   </div>


                   <div class="col-md-2">
                       <div class="ibox float-e-margins">
                           <div class="form_sep">
                               <label for="reg_input_no" class="">.</label><br>
                               <button class="btn btn-primary btn btn-sm" type="submit" name="apply_approve">Show Pending Invoice</button>
                           </div>
                       </div>
                   </div>

                   <div class="col-md-2">
                       <div class="ibox float-e-margins">
                           <div class="form_sep">
                               <label for="reg_input_no" class="">.</label><br>
                               <button class="btn btn-success btn btn-sm" type="submit" name="apply_processed_inv">Show Claim Invoiced</button>
                           </div>
                       </div>
                   </div>

                   <div class="col-md-2">
                       <div class="ibox float-e-margins">
                           <div class="form_sep">
                               <label for="reg_input_no" class="">.</label><br>
                               <a href="pacct.php?emr=<?php echo $emr; ?>&inv" class="btn btn-success btn btn-sm"> <i class="fa fa-refresh"></i> &nbsp;Refresh</a>
                           </div>
                       </div>
                   </div>


                   <div class="col-md-2">
                       <div class="ibox float-e-margins">
                           <div class="form_sep">
                               <label for="reg_input_no" class="">.</label><br>
                               <a href="pacct.php?emr=<?php echo $emr; ?>" class="btn btn-danger btn btn-sm"> <i class="fa fa-times"></i> &nbsp;Close</a>
                           </div>
                       </div>
                   </div>

                   <strong style="color:#F00">What to do here ...</strong><br>
                   <p>Select/check the items you wish to pay or post claim, then click "Invoice & Pay" or "Post Claims" button.</p>

                   <?php
                    // ------------------- CONFIG & INITIALIZATION -------------------


                    // Input dates and patient info (ensure these are sanitized/validated)
                    $fromDT = $from_date . ' 00:00:00';
                    $toDT   = $to_date . ' 23:59:59';

                    if (isset($_POST['apply_approve'])) {
                        $inv_status = " AND paystatus=0";
                    } elseif (isset($_POST['apply_processed_inv'])) {
                        $inv_status = " AND invoice_status=1 AND paystatus=1 AND pay=0";
                    } else {
                        $inv_status = " AND invoice_status=0 AND paystatus=0";
                    }

                    $n = 1; // checkbox counter
                    $s = 1; // serial counter

                    $totals = [
                        'pharm'   => 0,
                        'nursing' => 0,
                        'lab'     => 0,
                        'others'  => 0,
                        'claim'   => 0,
                        'invoice' => 0,
                        'dsc'     => 0,
                        'chr'     => 0
                    ];

                    // ------------------- FUNCTIONS -------------------

                    function canSelectForClaim($row)
                    {
                        return (
                            ($row['pay'] > 0 && $row['dsp_by'] != '') ||
                            ($row['pay'] > 0 && $row['invoice_status'] == 1) ||
                            ($row['pay'] == 0 && $row['claim_amt'] > 0 && $row['invoice_status'] == 1) ||
                            ($row['med_dosage_unit'] == 1)
                        );
                    }

                    function buildCheckboxValue($row)
                    {
                        return htmlspecialchars(implode('__', [
                            $row['sn'],
                            $row['pay'],
                            $row['item_services'],
                            $row['qty'],
                            $row['cr'],
                            $row['cat_type'],
                            $row['serv_group']
                        ]), ENT_QUOTES);
                    }

                    function renderServiceTable($title, $rows, &$totals, &$n, &$s)
                    {
                        if (empty($rows)) {
                            echo "<div class='alert alert-danger'>No <b>$title</b> records found.</div>";
                            return;
                        }

                        echo "<table class='table table-striped'>
    <tr>
        <th>.</th>
        <th>$title</th>
        <th>Qty</th>
        <th>Amount</th>
        <th>Claims</th>
        <th>Status</th>
        <th>Date</th>
        <th>By</th>
        <th>Action</th>
    </tr>";

                        foreach ($rows as $row) {
                            $canSelect = canSelectForClaim($row);

                            // Totals
                            $totals['claim']   += $row['claim_amt'];
                            $totals['invoice'] += $row['pay'];

                            switch ($row['serv_group']) {
                                case 'Pharmacy':
                                    $totals['pharm'] += $row['pay'];
                                    break;
                                case 'Nursing Services':
                                case 'Nursing Consumable':
                                    $totals['nursing'] += $row['pay'];
                                    break;
                                case 'Laboratory':
                                case 'Radiology':
                                    $totals['lab'] += $row['pay'];
                                    break;
                                default:
                                    $totals['others'] += $row['pay'];
                            }

                            echo "<tr>
        <td><input type='checkbox' name='inv[]' value='" . buildCheckboxValue($row) . "' id='add_m_$n' onclick='UpdateCost()' " . (!$canSelect ? 'disabled' : '') . " /></td>
        <td>{$s} - {$row['item_services']}</td>
        <td>{$row['qty']}</td>
        <td>" . number_format($row['pay']) . "</td>
        <td>{$row['claim_amt']}</td>
        <td>" . ($row['cr'] ? "<span style='color:red'>Credit</span>" : "Pending") . "</td>
        <td>" . date('d M,y h:i a', strtotime($row['date_entry'])) . "</td>
        <td>{$row['prepared_by']}</td>
        <td>";

                    ?>
                           <button class="btn btn-success btn-xs view_notes" id="<?php echo $row['sn'] . '__' . $row['item_services']; ?>">Notes</button>

                           <?php if ($_SESSION['edit_price_at_point'] == 1): ?>
                               <button class="btn btn-danger btn-xs edit_price_entry" id="<?php echo $row['sn']; ?>__inv">Edit</button>
                           <?php endif; ?>

                           <?php if ($row['paystatus'] == 1 && $row['drug_status'] == 0): ?>
                               <button class="btn btn-warning btn-xs" onclick="cancel_invoiced('<?php echo $row['sn']; ?>')">Cancel</button>
                           <?php endif; ?>

                   <?php

                            echo "</td></tr>";

                            $n++;
                            $s++;
                        }

                        echo "</table>";
                    }

                    // ------------------- FETCH DATA -------------------

                    $sql = "
SELECT sn, item_services, pay, claim_amt, dsp_by, invoice_no, invoice_status,
       invoice_date, date_entry, prepared_by, invoice_by, cat_type,
       serv_group, access, paystatus, qty, cr, med_dosage_unit, drug_status
FROM patient_ap_services
WHERE hospital_no = :emr
AND date_entry BETWEEN :from AND :to
$inv_status
ORDER BY date_entry ASC
LIMIT 2000
";

                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':emr' => $emr,
                        ':from' => $fromDT,
                        ':to'   => $toDT
                    ]);

                    $allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // ------------------- GROUP DATA -------------------

                    $grouped = [
                        'Pharmacy'       => [],
                        'Nursing'        => [],
                        'Investigations' => [],
                        'Other Services' => []
                    ];

                    foreach ($allRows as $row) {
                        if ($row['serv_group'] == 'Pharmacy') {
                            $grouped['Pharmacy'][] = $row;
                        } elseif (
                            $row['serv_group'] == 'Nursing Services' ||
                            $row['serv_group'] == 'Nursing Consumable' ||
                            strpos($row['cat_type'], 'Nursing') === 0
                        ) {
                            $grouped['Nursing'][] = $row;
                        } elseif (in_array($row['serv_group'], ['Laboratory', 'Radiology'])) {
                            $grouped['Investigations'][] = $row;
                        } else {
                            $grouped['Other Services'][] = $row;
                        }
                    }

                    // ------------------- RENDER TABLES -------------------

                    renderServiceTable('Pharmacy', $grouped['Pharmacy'], $totals, $n, $s);
                    renderServiceTable('Nursing', $grouped['Nursing'], $totals, $n, $s);
                    renderServiceTable('Investigations', $grouped['Investigations'], $totals, $n, $s);
                    renderServiceTable('Other Services', $grouped['Other Services'], $totals, $n, $s);

                    // ------------------- TOTALS -------------------

                    $totalInvoice = $totals['pharm'] + $totals['nursing'] + $totals['lab'] + $totals['others'];
                    ?>

                   <div class="row">
                       <div class="col-md-2">
                           <h3 class="no-margins">Totals:</h3>
                       </div>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?= number_format($totals['pharm']); ?></h3><small>Pharmacy</small>
                       </div>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?= number_format($totals['nursing']); ?></h3><small>Nursing</small>
                       </div>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?= number_format($totals['lab']); ?></h3><small>Investigation</small>
                       </div>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?= number_format($totals['others']); ?></h3><small>Others</small>
                       </div>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?= number_format($totalInvoice); ?></h3><small>Total Invoice</small>
                       </div>
                   </div>

                   <hr>

                   <table width="100%">
                       <tr>
                           <?php if ($totals['invoice'] > 0): ?>
                               <td width="16%" align="right"><strong>Discount / <?= number_format($totals['dsc']); ?></strong></td>
                               <td width="16%" align="right"><strong>Extra-Charge / <?= number_format($totals['chr']); ?></strong></td>
                               <td width="16%" align="right"><strong>Total Amount Paying:</strong></td>
                               <td width="16%" align="left">
                                   <input type="text" name="totalcost" id="totalcost" class="input-sm form-control" readonly style="font-size:26px; background:none; border:0;">
                               </td>
                               <td width="16%" align="left">
                                   <input type="hidden" value="<?= $patient_name; ?>" name="patient_name">
                                   <div class="checkbox i-checks">
                                       <label><input type="checkbox" value="daily_inv" name="daily_inv">Generate Patient Admitted Daily Invoice</label>
                                   </div>
                               </td>
                               <td width="16%" align="left">
                                   <button class="btn btn-success btn-sm" type="submit" name="process_inv" id="process_inv">Invoice & Pay</button>
                               </td>
                           <?php endif; ?>

                           <?php if ($totals['claim'] > 0): ?>
                               <td width="16%" align="right"><strong>Total Claim:</strong></td>
                               <td width="16%" align="left"><span style="font-size:20px"><?= number_format($totals['claim']); ?></span></td>
                               <td width="16%">
                                   <button class="btn btn-info btn-sm" type="submit" name="process_claim" id="process_claim" onclick="return confirm('Are you sure you want to post this claim?')">Post Claim</button>
                                   <hr>
                                   <button class="btn btn-danger btn-sm" type="submit" name="convert_pay" id="convert_pay" onclick="return confirm('WARNING! Are you sure you want to convert selected items to self pay.')">Convert to Self Pay</button>
                               </td>
                           <?php endif; ?>
                       </tr>
                   </table>

                   <input type="hidden" value="inv" name="i_come_from">
                   <input type="hidden" value="<?= $emr; ?>" name="emr">

                   <?php if ($adm_count > 0): ?>
                       <input type="button" value="Admission Invoice" data-target="#modal" id="12x" class="btn btn-success btn-sm adm_invoice">
                   <?php endif; ?>



               </form>
           </div>
       </div>
   </div>