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
                   <p> select/check the items you wish to pay or post claim and then click invoice & pay or post claims button. </p>

                   <?php

                    $search_datee = "and date(date_entry) between '$from_date' and '$to_date'";

                    if (isset($_POST['apply_approve'])) {
                        $inv_status = " AND paystatus=0";
                    } elseif (isset($_POST['apply_processed_inv'])) {
                        $inv_status = " and invoice_status=1 AND paystatus=1 AND pay=0";
                    } else {
                        $inv_status = " and (invoice_status=1 or invoice_status=0) AND paystatus=0";
                    }



                    $n = 1;
                    $s = 1;
                    $claim_set = 0;
                    $total_claim = 0;
                    $invoice_set = 0;
                    $total_dsc = 0;
                    $total_chr = 0;

                    $table_format = "SELECT sn,item_services,pay,claim_amt,dsp_by,invoice_no,invoice_status,invoice_date,
                date_entry,prepared_by,invoice_by,cat_type,serv_group,access,paystatus,qty,cr,drug_sn,med_dosage_unit,process_claim,drug_status,claim_valid_by 
                FROM patient_ap_services WHERE hospital_no='$emr' $search_datee $inv_status";



                    //////////////// LAB / RADIOLOGY 

                    //if($_SESSION['lab']=='1'){ 

                    $stmt = $db->query("$table_format and (serv_group='Laboratory' or serv_group='Radiology') order by invoice_status,paystatus,date_entry,cat_type");

                    ?>

                   <?php if ($stmt->rowCount() > 0) {
                        //include_once("../inc/utilities.php");
                        //$dsc_chr_set = 0;

                        //  patient_discount($db, $emr, $patient_type, $discount_set, $referral_name, $insurance_no, $pf, $pf_value, $dsc_chr, $dura, $service_type, $dsc_chr_set, $post_type, $count_bal, $dsc_chr_set, $mySearch, $grp_idv, $grp_idv_no);

                    ?>

                       <table class="table table-striped" width="100%">
                           <tr>
                               <th width="3%">.</th>
                               <th width="26%">Investigations</th>
                               <th width="2%">Qty</th>
                               <th width="10%">Amount</th>
                               <th width="6%">Claims</th>
                               <th width="6%">Status</th>
                               <th width="16%">Date Entered</th>
                               <th width="16%">Entered By</th>
                               <th width="15%">.</th>
                           </tr>

                           <?php
                            // Initialize variables
                            $lab = 0;
                            $total_claim = 0;

                            if (!isset($n)) {
                                $n = 1;
                            }
                            if (!isset($s)) {
                                $s = 1;
                            }

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                $sn        = $row['sn'];
                                $pay       = $row['pay'];
                                $claim_amt = $row['claim_amt'];

                                // Replace ?? with safe checks
                                $charge       = isset($row['charge']) ? $row['charge'] : 0;
                                $discount     = isset($row['discount']) ? $row['discount'] : 0;
                                $total_chr    = isset($row['total_chr']) ? $row['total_chr'] : 0;
                                $total_dsc    = isset($row['total_dsc']) ? $row['total_dsc'] : 0;
                                $dura         = isset($row['dura']) ? $row['dura'] : '';
                                $post_type    = isset($row['post_type']) ? $row['post_type'] : '';
                                $count_bal    = isset($row['count_bal']) ? $row['count_bal'] : '';
                                $dsc_chr_set  = isset($row['dsc_chr_set']) ? $row['dsc_chr_set'] : '';
                                $grp_idv_no   = isset($row['grp_idv_no']) ? $row['grp_idv_no'] : '';

                                $lab += $pay;
                                $total_claim += $claim_amt;

                                if ($pay == 0 && $claim_amt > 0) {
                                    $claim_set = 1;
                                }
                                if ($pay > 0) {
                                    $invoice_set = 1;
                                }

                                // Checkbox value builder
                                $valueParts = [
                                    $sn,
                                    $pay,
                                    $row['item_services'],
                                    $row['qty'],
                                    $row['cr'],
                                    $discount,
                                    $charge,
                                    '',
                                    $total_chr,
                                    $total_dsc,
                                    $dura,
                                    $post_type,
                                    $count_bal,
                                    $dsc_chr_set,
                                    $grp_idv_no,
                                    $row['cat_type'],
                                    $row['serv_group']
                                ];

                                $checkboxValue = implode('__', $valueParts);
                            ?>

                               <tr>
                                   <td width="3%">
                                       <input
                                           type="checkbox"
                                           value="<?= htmlspecialchars($checkboxValue, ENT_QUOTES); ?>"
                                           name="inv[]"
                                           id="add_m_<?= $n; ?>"
                                           onclick="UpdateCost()"
                                           style="height: 15px; width: 15px;" />
                                   </td>

                                   <td width="26%">
                                       <?= $s . ' - ' . htmlspecialchars($row['item_services']); ?>
                                   </td>

                                   <td width="2%"><?= $row['qty']; ?></td>

                                   <td width="10%">
                                       <?= number_format($pay); ?><br>
                                       <?php if ($charge > 0): ?>
                                           CHR: <?= number_format($charge); ?><br>
                                       <?php endif; ?>
                                       <?php if ($discount > 0): ?>
                                           DSC: <?= number_format($discount); ?>
                                       <?php endif; ?>
                                   </td>

                                   <td width="6%"><?= number_format($claim_amt); ?></td>

                                   <td width="6%">
                                       <?php if ($row['cr'] == 1): ?>
                                           <strong style="color:#F00">Credit</strong>
                                       <?php else: ?>
                                           Pending
                                       <?php endif; ?>
                                   </td>

                                   <td width="16%">
                                       <?= date('d M, y h:i a', strtotime($row['date_entry'])); ?>
                                   </td>

                                   <td width="16%">
                                       <?= htmlspecialchars($row['prepared_by']); ?>
                                   </td>

                                   <td width="15%">
                                       <?php if (!empty($_SESSION['edit_price_at_point'])): ?>
                                           <input
                                               type="button"
                                               name="edit_price"
                                               value="Edit"
                                               data-target="#modal"
                                               id="<?= $sn . '__inv'; ?>"
                                               class="btn btn-danger btn-xs edit_price_entry" />
                                       <?php endif; ?>

                                       <?php if ($row['paystatus'] == 1): ?>
                                           &nbsp;:&nbsp;
                                           <input
                                               type="button"
                                               id="cancel_invoiced_<?= $sn; ?>"
                                               value="Cancel"
                                               class="btn btn-warning btn-xs"
                                               onclick="cancel_invoiced('<?= $sn; ?>')" />
                                       <?php endif; ?>
                                   </td>
                               </tr>

                           <?php
                                $n++;
                                $s++;
                            } // end while
                            ?>
                       </table>

                   <?php  } else {
                        echo '<div class="alert alert-danger">No <b>Investigation</b> Records For Date Selected. Adjust Dates Range & Try Again.</div>';
                    } ?>

                   <?php // } 
                    ?>

                   <?php // } 
                    ?>

                   <div class="row">
                       <div class="col-md-2">
                           <h3 class="no-margins">Totals:</h3>
                           <small> </small>
                       </div>
                       <?php //if ($_SESSION['pharm'] == '1') { 
                        ?>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?php echo number_format($pharm); ?></h3>
                           <small>Pharmacy</small>
                       </div>
                       <?php  //} 
                        ?>
                       <?php //if ($_SESSION['nursing'] == '1') { 
                        ?>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?php echo number_format($nursing); ?></h3>
                           <small>Nursing</small>
                       </div>
                       <?php // } 
                        ?>
                       <?php //if($_SESSION['lab']=='1'){ 
                        ?>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?php echo number_format($lab); ?></h3>
                           <small>Investigation</small>
                       </div>
                       <?php // } 
                        ?>
                       <?php //if ($_SESSION['other_bill'] == '1') { 
                        ?>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?php echo number_format($others); ?></h3>
                           <small>Others</small>
                       </div>
                       <?php // } 
                        ?>
                       <div class="col-md-2">
                           <h3 class="no-margins"><?php $total = $pharm + $nursing + $lab + $others;
                                                    echo number_format($total); ?></h3>
                           <small>Total Invoice</small>
                       </div>
                   </div>


                   <?php if (isset($_GET['sel'])) { ?>
                       <br>
                       <strong style="color:#F00">WARNING // Select item(s) you wish to prepare invoice ... </strong>
                   <?php } ?>

                   <hr>
                   <table width="100%">
                       <tr>
                           <?php if ($invoice_set == 1) { ?>
                               <td width="16%" align="right">
                                   <div align="right">
                                       <strong>Discount / <?php echo number_format($total_dsc); ?> </strong>
                                   </div>
                               </td>


                               <td width="16%" align="right">
                                   <div align="right">
                                       <strong>Extra-Charge / <?php echo number_format($total_chr); ?> </strong>
                                   </div>
                               </td>

                               <td width="16%" align="right">
                                   <div align="right">
                                       <strong>Total Amount Paying: </strong>
                                   </div>
                               </td>
                               <td width="16%" align="left">
                                   <div align="left">
                                       <input type="text" name="totalcost" id="totalcost" class=" input-sm form-control" readonly="readonly" style="font-size:26px; outline:none; background:none; background-color:transparent; border: 0px solid;">
                                   </div>
                               </td>
                               <td align="left" width="16%">
                                   <div align="left">
                                       <input type="hidden" value="<?php echo $patient_name; ?>" name="patient_name">
                                       <div class="checkbox i-checks"><label> <input type="checkbox" value="daily_inv" name="daily_inv">Generate Patient Admitted Daily Invoice</label></div>
                                   </div>
                               </td>
                               <td align="left" width="16%">

                                   <button class="btn btn-success btn-sm" type="submit" name="process_inv" id="process_inv">Invoice & Pay</button>

                               </td>
                           <?php } ?>

                           <?php if ($claim_set == 1) { ?>

                               <td width="16%" align="right">
                                   <div align="right">
                                       <strong>Total Claim: </strong> &nbsp;&nbsp;
                                   </div>
                               </td>
                               <td width="16%" align="left">
                                   <div align="left">
                                       <span style="font-size:20px"> <?php echo number_format($total_claim); ?></span>
                                   </div>
                               </td>
                               <td width="16%">
                                   <button class="btn btn-info btn-sm" type="submit" name="process_claim" id="process_claim" onclick="return confirm('Are you sure you want to post this claim?')">&nbsp; &nbsp;&nbsp;&nbsp; Post Claim &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;</button>

                                   <hr>

                                   <button class="btn btn-danger btn-sm" type="submit" name="convert_pay" id="convert_pay" onclick="return confirm('WARNING! Are you sure you want to convert selected items to self pay.')">Convert to Self Pay</button>


                               </td>
                           <?php } ?>

                       </tr>
                   </table>
                   <input type="hidden" value="inv" name="i_come_from">
                   <input type="hidden" value="<?php echo $emr; ?>" name="emr">
               </form>

               <?php if ($adm_count > 0) { ?>
                   <input type="button" name="" value="Admission Invoice" data-target="#modal" id="12x" class="btn btn-success btn-sm adm_invoice" />
               <?php } ?>


           </div>

           <?php $inv_count = $n; ?>