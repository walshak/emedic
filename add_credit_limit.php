 <?php
    session_start();
    include("Connections/Conn.php");
    include("inc/credit_current_balance.php");

    if (isset($_POST["validate_"])) {

        $validate_ = $_POST["validate_"];
        $PARTT = explode("__", $validate_);

        $sn = $PARTT[0];
        $service_table_id = $PARTT[1];

        $paystatus = 1;
        $invoice_status = 1;
        $pay_mode = 'spkage';
        $claim_valid_by = $_SESSION['fullname'] . '/<br>' . date('d-m-y h:i:s a');
        $transc_date = date("Y-m-d H:i:s");

        // Prepare the SQL statement
        $updateSQL = "UPDATE patient_ap_services SET 
        paystatus = :paystatus,
        invoice_status = :invoice_status,
        transact_date = :transact_date,
        claim_valid_by = :claim_valid_by,
        pay_mode = :pay_mode,
        med_frequency = :med_frequency
        WHERE sn = :sn AND paystatus = 0";

        // Prepare the statement
        $stmt = $db->prepare($updateSQL);

        // Bind the parameters
        $stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_INT);
        $stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_INT);
        $stmt->bindParam(':transact_date', $transc_date, PDO::PARAM_STR);
        $stmt->bindParam(':claim_valid_by', $claim_valid_by, PDO::PARAM_STR);
        $stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
        $stmt->bindParam(':med_frequency', $service_table_id, PDO::PARAM_STR);
        $stmt->bindParam(':sn', $sn, PDO::PARAM_STR);

        // Execute the statement
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Validated was successful.";
        } else {
            echo "Already Validated.";
        }

        exit;
    }



    if (isset($_POST["add_credit_limit_id"])) {

        if (isset($_POST["interface_name"])) {
            $inter_url = "&emr=";
        } else {
            $inter_url = "&ptm=all/";
        }

        $emr = $_POST["add_credit_limit_id"];
        $general_credit_limit =    $_SESSION['credit_limit_status'];
        $items = call_current_balance($db, $emr, $general_credit_limit);
        $current_balance =  $items["current_balance"];
        $credit_limit  = $items['bal_credit_limit'];
        $main_credit_limit  = $items['credit_limit'];
        $label = $items['credit_type'];
        $Total_total_credits = $items['Total_total_credits'];
        $insurance_cr_limit = $items['insurance_cr_limit'];
        $appointment_credit_limit = $items['appointment_credit_limit'];
        $admission_limit = $items['admission_limit'];
        $personal_adm_credit_limit = $items['personal_adm_credit_limit'];
        $personal_cr_limit = $items['personal_cr_limit'];
        $patient_name = $items['patient_name'];
        $entitled_to = $items['entitled_to'];

    ?>



     <h2><b style="color:blue">NAME: </b><?= $patient_name; ?></h2>


     <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 18px;;">

         <tr>
             <td><b style="color:brown">The current credit type in use:</b> <b><?= $label . ' of  (₦)' . number_format($main_credit_limit, 2); ?></b></td>
             <td align="right"></td>
         </tr>
         <tr>
             <td>Current Deposit</td>
             <td align="right" style="<?= ($current_balance < 0) ? 'color:red;' : ''; ?>">
                 <?= number_format($current_balance, 2); ?>
             </td>
         </tr>
         <tr>
             <td>Current Credit Limit</td>
             <td align="right" style="<?= ($credit_limit < 0) ? 'color:red;' : ''; ?>">
                 <?= number_format($credit_limit, 2); ?>
             </td>
         </tr>


         <tr>
             <td>Current Credit Limit Plus Total Deposit</td>

             <td align="right" style="<?= ($entitled_to < 0) ? 'color:red;' : ''; ?>">
                 <?= number_format($entitled_to, 2); ?>
             </td>
         </tr>

     </table>



     <h3>Credits currently posted but not yet reflected in the ledger: <?= number_format($Total_total_credits, 2); ?></h3>


     <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 18px;;">
         <tr>
             <th align="left">Credit Type (Others)</th>
             <th align="right">Limit (₦)</th>
         </tr>
         <tr>
             <td>1. Personal Credit Limit</td>
             <td align="right"><?= number_format($personal_cr_limit, 2);
                                if ($personal_cr_limit > 0) {
                                    $urlencode = $emr . '____personal____' . $personal_cr_limit;
                                    $urlencode = base64_encode(base64_encode(base64_encode($urlencode)));
                                ?><br>
                     <a href="?can_y_credit=<?php echo urlencode($urlencode) . $inter_url . $emr ?>" onclick="return confirm('Are you sure you want to Cancel?');" class="btn btn-danger btn-xs">Cancel</a>

                 <?php }
                    ?>
             </td>
         </tr>
         <tr>
             <td>2. Appointment Credit Limit</td>
             <td align="right"><?= number_format($appointment_credit_limit, 2);
                                if ($appointment_credit_limit > 0) {
                                    $urlencode = $emr . '____apptm____' . $appointment_credit_limit;
                                    $urlencode = base64_encode(base64_encode(base64_encode($urlencode)));
                                ?><br>
                     <a href="?can_y_credit=<?php echo urlencode($urlencode) . $inter_url . $emr ?>" onclick="return confirm('Are you sure you want to Cancel?');" class="btn btn-danger btn-xs">Cancel</a>

                 <?php }
                    ?>
             </td>
         </tr>
         <tr>
             <td>3. Personal Current Admission Credit Limit</td>
             <td align="right"><?= number_format($personal_adm_credit_limit, 2);
                                if ($personal_adm_credit_limit > 0) {
                                    $urlencode = $emr . '____personal_adm____' . $personal_adm_credit_limit;
                                    $urlencode = base64_encode(base64_encode(base64_encode($urlencode)));
                                ?><br>
                     <a href="?can_y_credit=<?php echo urlencode($urlencode) . $inter_url . $emr ?>" onclick="return confirm('Are you sure you want to Cancel?');" class="btn btn-danger btn-xs">Cancel</a>

                 <?php }
                    ?>
             </td>
         </tr>
         <tr>
             <td>4. Department Admission Credit Limit</td>
             <td align="right"><?= number_format($admission_limit, 2); ?></td>
         </tr>
         <tr>
             <td>5. Family's Folder Credit Limit</td>
             <td align="right">
                 <?php if ($_SESSION['unit_head'] == 1) { ?>
                     <?= number_format($insurance_cr_limit, 2);
                        if ($insurance_cr_limit > 0) {
                            $urlencode = $emr . '____family____' . $insurance_cr_limit;
                            $urlencode = base64_encode(base64_encode(base64_encode($urlencode)));
                        ?><br>
                         <a href="?can_y_credit=<?php echo urlencode($urlencode) . $inter_url . $emr ?>" onclick="return confirm('Are you sure you want to Cancel?');" class="btn btn-danger btn-xs">Cancel</a>

                 <?php }
                    }
                    ?>
             </td>
         </tr>
         <tr>
             <td>6. General Credit for All Patient Limit</td>
             <td align="right"><?= number_format($general_credit_limit, 2); ?></td>
         </tr>
     </table>


     <hr>

     <?php if (isset($_POST["interface_name"])) { ?>
         <form method="POST" action="pacct.php?emr=<?php echo $emr; ?>">

         <?php } else { ?>
             <form method="POST" action="index.php?ptm=all/<?php echo $emr; ?>">

             <?php } ?>

             <div class="form_sep">
                 <label for="reg_input_no" class="req">Enter Credit Amount</label>
                 <input type="number" id="" name="credit_limit" class="form-control" style="font-size: 18px;" min='0' required>
             </div>

             <div class="form_sep">
                 <label for="reg_input_no" class="">Select Credit Option</label>
                 <select name="credit_option" class="form-control" required style="font-size: 18px;">
                     <option value="">Select</option>
                     <option value="1">Set as Current Appointment Credit Limit</option>
                     <option value="2">Set as Current Admission Credit Limit</option>
                     <option value="3">Set as Personal Credit Limit for Current and All Future Visits</option>
                     <?php
                        if ($_SESSION['unit_head'] == 1) { ?>
                         <option value="4">Set Department Credit Limit for All Patients, Including This One</option>
                         <option value="5">Set Family Folder Credit Limit for This Patient and Their Family Members</option>
                         <option value="6">Set General Credit Limit for All Patients, Including This One</option>
                     <?php } ?>
                 </select>

             </div>

             <div class="form_sep"></div>

             <div class="pull-left">
                 <button class="btn btn-primary" type="submit" name="save_credit_limit">Save</button>
                 <input type="hidden" name="hospital_no" value="<?php echo $emr; ?>" />
             </div>


             <div class="pull-right">
                 <button class="btn btn-danger" data-dismiss="modal">Close</button>
             </div>
             </form>


         <?php

        }
            ?>