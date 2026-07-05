<?php

if (strtoupper($_SESSION['h_code']) != 'ZMKC') {
    $stmt = $db->prepare("SELECT * FROM diagnosis WHERE item = 'Birth injury to femur (ICD10: P132)' AND description = '1'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $admission_ = $stmt->rowCount();
        /// disable admission ///
    } else {
        $admission_ = 0;
    }
} else {
    $admission_ = 0;
}

?>


<div class="modal inmodal fade" id="money_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Money Transaction</h4>
            </div>

            <div class="modal-body">

                <form method="POST" id="money_form">
                    <strong>Current Balance: &nbsp;</strong><strong style="font-size:24px"><?php echo number_format($current_balance, 2); ?></strong>
                    <small>/Naira only</small>
                    <div class="form_sep"></div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Select transaction type</label>
                        <select name="transaction_type" id="transaction_type" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Deposit">Deposit Money</option>
                            <?php if ($current_balance > 0 && $_SESSION['refund'] == 1) { ?>
                                <option value="Refund">Refund Wallet/Deposit Money</option>
                            <?php } ?>
                            <?php if ($current_balance > 0 && $_SESSION['transfer'] == 1) { ?>
                                <option value="Transfer_to_patient">Transfer Deposit Money to another patient</option>
                            <?php } ?>
                        </select>
                    </div>

                    <!--   <input type="hidden" name="transaction_type" id="transaction_type" value="Deposit">
 -->
                    <div class="form_sep" id="bene">
                        <label for="reg_input_no">Enter Beneficiary Hospital Number (Private Patients Only)</label>
                        <input type="text" id="patient_no_transfer" name="patient_no_transfer" class="form-control">
                    </div>

                    <div class="form_sep">
                        <label for="amount" class="req">Enter Amount</label>
                        <input type="text" id="amount" name="amount" class="form-control" required>
                    </div>

                    <script>
                        const amountInput = document.getElementById('amount');

                        amountInput.addEventListener('input', function(e) {
                            // Remove all commas
                            let value = this.value.replace(/,/g, '');

                            // Allow only digits and one decimal point
                            if (!/^\d*\.?\d*$/.test(value)) {
                                // Remove last invalid character
                                value = value.slice(0, -1);
                            }

                            // Split integer and decimal parts
                            let parts = value.split('.');
                            // Format integer part with commas
                            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                            // Join integer and decimal parts
                            this.value = parts.join('.');
                        });
                    </script>



                    <div class="form_sep" id="mode_payment_status">
                        <label for="reg_input_no" class="req">Select Mode Payment</label>
                        <select name="mode_pay" id="mode_pay" class="form-control">
                            <option selected="selected" value="">Select ...</option>
                            <option value="cash">Cash</option>
                            <option value="POS">POS</option>
                            <option value="Transfer">Pay by Bank Transfer</option>
                            <option value="cash">Patient Deposit</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Description</label>
                        <input type="text" id="desc" name="desc" class="form-control" required>
                    </div>

                    <?php if ($_SESSION['transfer'] == 0) { ?>
                        <div class="form_sep">
                            <label for="reg_input_no">Authorization Code: (Refund/Transfer)</label>
                            <input type="text" id="auto_code" name="auto_code" class="form-control">
                        </div>
                    <?php } ?>

                    <?php if ($admission_ == 0) { ?>
                        <hr>
                        <div class="pull-left">
                            <button class="btn btn-primary btn-sm" type="submit" name="save">Save</button>
                        </div>

                    <?php } else { ?>
                        <hr>
                        <h4 style="color:brown;">Deposit Rights Disabled</h4>
                    <?php } ?>

                    <input type="hidden" name="requester" value="<?php echo $_SESSION['fullname']; ?>" />
                    <input type="hidden" name="emr" value="<?php echo $emr; ?>" />
                    <input type="hidden" name="insurance_no" id="insurance_no" value="<?php echo $insurance_no; ?>" />
                    <input type="hidden" name="current_balance" value="<?php echo $current_balance; ?>" />
                    <input type="hidden" name="MM_update" value="add_money_confirm" />

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="discount_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Discount / Additional Charges</h4>
            </div>
            <div class="modal-body">

                <?php
                $subject = null;
                $status_disabled = 0;
                $stmtr = $db->prepare("SELECT hmo_no FROM enrollee WHERE hospital_no = :emr LIMIT 1");
                $stmtr->bindParam(':emr', $emr, PDO::PARAM_STR);
                $stmtr->execute();
                $rowx = $stmtr->fetch(PDO::FETCH_ASSOC);
                if ($rowx) {
                    $subject = $rowx['hmo_no'];
                    if ($subject != 1000) {
                        echo "<h2>This person is not a private patient, so the setup cannot be done here. 
                        Please click the link below to set up for Family, Corporate, or Referrals.</h2>";
                        echo '>> <a href="index.php?discount">Discount/Charge Setup</a>';
                        $status_disabled = 1;
                    }
                } else {
                    // Check pharm_ext if not found
                    $stmtr = $db->prepare("SELECT referral FROM pharm_ext WHERE transc_code = :emr");
                    $stmtr->bindParam(':emr', $emr, PDO::PARAM_STR);
                    $stmtr->execute();
                    $rowx = $stmtr->fetch(PDO::FETCH_ASSOC);
                    if ($rowx) {
                        $subject = $rowx['referral'];
                        $subject = !empty($subject) ? $subject : "EX";
                    }
                }

                $stmtd = $db->query("SELECT * FROM patient_discount WHERE individual_group_no='$subject'");
                if ($stmtd->rowCount() > 0) {
                    $row = $stmtd->fetch(PDO::FETCH_ASSOC);

                    echo "<h2>The task is already set up</h2><hr>";
                    echo '<H3>TYPE: ' . $row['discount_charge'] . '</H3>';
                    echo '<H3>NAME: ' . $row['individual_group_name'] . '</H3>';
                    echo '<H3>PERCENT/FLAT: ' . $row['percentage_flat'] . '(' . $row['percentage_flat_value'] . ')</H3>';
                    echo '<H3>COVERAGE: ' . $row['apply_to_services'] . '</H3><br>';
                    echo '>> <a href="index.php?discount">See More Details</a>';
                    $status_disabled = 1;
                }


                $stmt = $db->query("SELECT * FROM patient_discount WHERE individual_group_no='$emr'");
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $discount_charge = ($row['discount_charge'] === 'Charge') ? 'chr' : 'dsc';

                ?>
                    <h4 style="color:#00F"><?php echo $row['discount_charge']; ?> Settings </h4><strong>Date:</strong>
                    <?php echo date("d, M,y", strtotime($row['status_date'])); ?>&nbsp;|&nbsp;<strong>Status:</strong>
                    <?php if ($row['status'] == 'Pending') {
                        echo 'Pending: Add Services and '; ?>
                        <a href="pacct.php?emr=<?php echo $emr; ?>">Refresh</a>
                    <?php } else {
                        echo $row['status'];
                    } ?> <br>
                    <?php if ($row['status'] == 'On-going') {

                        // Single query approach that updates only if the condition is met
                        $stmt = $db->prepare("UPDATE enrollee SET discount_set = 1 WHERE hospital_no = :emr AND discount_set = 0");
                        $stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
                        $stmt->execute();

                        $stmt = $db->prepare("UPDATE pharm_ext SET discount_set = 1 WHERE transc_code = :emr AND discount_set = 0");
                        $stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
                        $stmt->execute();

                    ?>
                        <a href="pacct.php?emr=<?php echo $emr . '&' . $discount_charge; ?>" class="btn btn-danger btn-xs">[ Cancel / Stop ] </a> &nbsp;
                    <?php } ?>
                    <input type="button" name="edit" value="Edit / Enable" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-warning btn-xs edit_discount" />
                    <br><br>
                    <table class="table table-striped table-bordered table-hover dataTables-example" width="100%">
                        <tr>
                            <td><strong>Percentage or Flat:</strong> </td>
                            <td><?php echo $row['percentage_flat'] . '(' . $row['percentage_flat_value'] . ')'; ?></td>
                            <td><strong>Duration:</strong></td>
                            <td><?php echo $row['duration']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Count/Remaining Counts:</strong></td>
                            <td><?php echo $row['specify_count'] . '/' . $row['count_bal']; ?></td>
                            <td><strong>Service Type:</strong></td>
                            <td>
                                <?php
                                if ($row['apply_to_services'] == 'All Services') {
                                    echo 'All Hospital Services';
                                } else { ?>
                                    <input type="button" name="edit" value="Add Services" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $emr; ?>" class="btn btn-success btn-xs add_services" />

                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="1"><strong>Setup by:</strong></td>
                            <td colspan="3"><?php echo $row['setby']; ?></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <input type="button" name="edit" value="See Updates History" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $emr; ?>" class="btn btn-success btn-xs view_history" />
                            </td>
                        </tr>
                    </table>

                    <?php
                } else {
                    if ($subject != '' &&   $status_disabled == 0) {
                    ?>

                        <form method="POST" id="discount_add_form">

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Select Discount or Charge</label>
                                <select name="discount_charge" id="discount_charge" class="form-control" required>
                                    <option selected="selected" value="">Select ...</option>
                                    <option value="Discount">Discount</option>
                                    <option value="Charge">Charge</option>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Mode of Discount/Charge</label>
                                <select name="mode" id="mode" class="form-control" required>
                                    <option selected="selected" value="">Select ...</option>
                                    <option value="Percentage">Percentage</option>
                                    <option value="Flat">Flat</option>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Enter Flat or Percentage Value</label>
                                <input type="text" id="mode_value" name="mode_value" class="form-control" maxlength="12" required>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">How long (Duration)</label>
                                <select name="how_long" id="how_long" class="form-control" required>
                                    <option selected="selected" value="">Select ...</option>
                                    <option value="Once">Once</option>
                                    <option value="Limited">Limited</option>
                                    <option value="Always">Always</option>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">If Limited Selected Above. Enter Count Here</label>
                                <input type="number" id="specify_count" name="specify_count" class="form-control">
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">..Apply Discount/Charge Hospital Services</label>
                                <select name="service_type" id="service_type" class="form-control" required>
                                    <option selected="selected" value="">Select ...</option>
                                    <option value="All Services">All Service in the hospital</option>
                                    <option value="specify">Selected Service in the hospital (Specify Later)</option>
                                </select>
                            </div>

                            <hr>
                            <div class="pull-left">
                                <button class="btn btn-primary btn-sm" type="submit" name="save">Save</button>
                            </div>
                            <input type="hidden" name="emr" value="<?php echo $emr; ?>" />
                            <input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>" />
                            <input type="hidden" name="MM_update" value="add_discount_insert" />

                        </form>


                <?php
                    } else {
                        ///echo '<b>Discount Not Available for this Person</b>';
                    }
                }

                ?>
            </div>

        </div>
    </div>
</div>


<div class="modal inmodal fade" id="edit_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Edit Price</h4>
            </div>
            <div class="modal-body" id="edit_price_body">
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="part_payment_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Part Payment Entry</h4>
            </div>
            <div class="modal-body" id="part_payment_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="view_invoice_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Notes</h4>
            </div>
            <div class="modal-body" id="view_invoice_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="edit_discount_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Edit</h4>
            </div>
            <div class="modal-body" id="edit_discount_body">

            </div>

        </div>
    </div>
</div>


<div class="modal inmodal fade" id="discount_edit_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Discount and Charges</h4>
            </div>
            <div class="modal-body">
                <!--  <form method="POST" action="pacct.php" >-->
                <form method="POST" id="discount_edit_body">

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Select Discount or Charge</label>
                        <select name="discount_charge1" id="discount_charge1" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Discount">Discount</option>
                            <option value="Charge">Charge</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Mode of Discount/Charge</label>
                        <select name="mode1" id="mode1" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Percentage">Percentage</option>
                            <option value="Flat">Flat</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Enter Flat or Percentage Value</label>
                        <input type="text" id="mode_value1" name="mode_value1" class="form-control" maxlength="12" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">How long (Duration)</label>
                        <select name="how_long1" id="how_long1" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Once">Once</option>
                            <option value="Limited">Limited</option>
                            <option value="Always">Always</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="">If Limited Selected Above. Enter Count here</label>
                        <input type="number" id="specify_count1" name="specify_count1" class="form-control">
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Apply Discount/Charge Hospital Services</label>
                        <select name="service_type1" id="service_type1" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="All Services">All Service in the hospital</option>
                            <option value="specify">Selected Service in the hospital (Specify Later)</option>
                        </select>
                    </div>



                    <hr>
                    <div class="pull-left">
                        <button class="btn btn-primary btn-sm" type="submit" name="save">Update Changes</button>
                    </div>
                    <input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
                    <input type="hidden" name="sn" id="sn" />
                    <input type="hidden" name="history" id="history" />
                    <input type="hidden" name="MM_update" value="update_discount" />


                </form>
            </div>

        </div>
    </div>
</div>


<div class="modal inmodal fade" id="view_history_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Data Entries History</h4>
            </div>
            <div class="modal-body" id="view_history_body">
            </div>

        </div>
    </div>
</div>


<div class="modal inmodal fade" id="add_services_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Add Services</h4>
            </div>
            <div class="modal-body" id="add_services_body">
            </div>

        </div>
    </div>
</div>

<div class="modal inmodal fade" id="add_money_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Edit</h4>
            </div>
            <div class="modal-body" id="add_money_confirm_body">

            </div>

        </div>
    </div>
</div>


<div class="modal inmodal fade" id="delete_confirmation_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Delete Confirmation</h4>
            </div>
            <div class="modal-body" id="delete_confirmation_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="misc_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Add Miscellaneous Bill/Charge</h4>
            </div>
            <div class="modal-body" id="misc_body">

                <form method="POST" action="pacct.php?emr=<?php echo $emr; ?>">

                    <div class="form_sep">
                        <label for="reg_textarea_message" class="req">Description</label>
                        <input type="text" name="desc" id="desc" class="form-control" required maxlength="30">
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Amount</label>
                        <input type="number" id="amount" name="amount" class="form-control" required>
                    </div>

                    <?php if ($insurance_type == 'NHIS' or $insurance_type == 'PHIS' or $insurance_type == 'Corporate') { ?>
                        <div class="form_sep">
                            <label for="reg_select" class="req">Payment Mode</label>
                            <select name="paymode" id="paymode" class="form-control" required>

                                <option selected="selected" value="">Select...</option>

                                <option value="c">Cash (Paying Now)</option>
                                <option value="b">Send Bill to Insurance</option>
                            </select>
                        </div>
                    <?php } ?>

                    <div class="form_sep">
                        <button type="submit" class="btn btn-success" name="savebill" id="savebill">Save</button>
                    </div>

                    <input type="hidden" name="hos_no" value="<?php echo $emr; ?>" />
                    <input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />
                    <input type="hidden" name="interest" value="<?php echo $interest; ?>" />

                </form>

            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="wallet_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Patient Deposit</h4>
            </div>
            <div class="modal-body" id="">

                <h2>Are you sure you want to pay services selected from <strong style="color: red;">Patient Deposit</strong>?</h2>
                <h2>Patient Deposit Amount: <?php echo number_format($wallet_amount); ?></h2>

                <hr>

                <button type="button" class="btn btn-success" data-dismiss="modal" aria-hidden="true">Okay</button>&nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="Reconcile_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">RECONCILATION</h4>
            </div>
            <div class="modal-body" id="">

                <h2>Are you sure you want to Reconcile this Amount!</h2>
                <h2>Reconcile Amount: <?php echo number_format($total_credit); ?></h2>

                <hr>

                <button type="button" class="btn btn-success" data-dismiss="modal" aria-hidden="true">Okay</button>&nbsp;&nbsp;
                <a href="pacct.php?emr=<?= $emr; ?>&pay" class="btn btn-danger">Cancel</a>
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="PostCredit_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">POST PATIENT BILL AS ACCOUNT RECIEVABLE (AR)</h4>
            </div>
            <div class="modal-body" id="" align="center">

                <h3 style="color:brown">The current credit type in use:<?= $label . ' of  (₦)' .
                                                                            number_format($main_credit_limit, 2); ?></h3>
                <?php

                if ($bal_credit_limit < 0) { ?>
                    <div class="alert alert-warning" align="center">
                        <h3>The patient has exceeded the credit limit balance. Do you still want to continue?</h3>
                    </div>
                <?php  } else { ?>

                    <h3 style="color: red;">Do you want to post or deliver the selected items as
                        Debt/AR to the patient and post them to the Patient Receivable Ledger</h3>


                <?php } ?>

                <hr>

                <button type="button" class="btn btn-success" data-dismiss="modal" aria-hidden="true">YES</button>&nbsp;&nbsp;
                <a href="pacct.php?emr=<?= $emr; ?>&pay" class="btn btn-danger">CANCEL</a>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="pay_from_patient_wallet_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">PAY FROM ANOTHER PATIENT DEPOSIT</h4>

            </div>
            <div class="modal-body" id="">


                <div class="form_sep">
                    <label for="reg_input_name" class="req">Enter the EMR Number of the patient you wish to make a payment from their deposit account.</label>
                    <input type="text" id="patient_emr" name="patient_emr" class="form-control" required>
                </div>

                <div class="form_sep">
                    <input type="button" name="eidt_gd" value="Confirm Patient's Status" class="btn btn-success" onClick="confirm_wallet()" />


                </div>

                <hr>

                <input type="hidden" name="payee" id="payee">

                <div style="color: chocolate; font-size: 18px;" id="wallet_amount_message"></div>
                <h1 id="name_wallet_benefact"></h1>
                <h3 id="wallet_amount_"></h3>
                <hr>
                <h3 style="color: red;"><u>WARNING:</u><br> PAY FROM ANOTHER PATIENT DEPOSIT IS <u>NOT</u> REVERSABLE!</h3>
                <div class="form_sep">
                    <a href="pacct.php?emr=<?= $emr; ?>&pay" class="btn btn-danger">Close</a> &nbsp;:&nbsp;
                    <input type="button" id="confirm_wallet_continue" value="Continue" class="btn btn-warning" onClick="confirm_wallet_continue()" />

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="validate_package_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Validate Package</h4>
            </div>
            <div class="modal-body" id="validate_package_body">
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="sufficient_bal_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">ATTENTION</h4>
            </div>
            <div class="modal-body" id="" align="center">

                <h1 style="color: red;">This patient has a sufficient balance in their deposit account. Would you like to skip this and collect a fresh payment for the selected service?</h1>
                <h1 style="color: red;">Click <B><U>CANCEL</U></B> To Pay From Patient's Deposit</h1>


                <hr>

                <button type="button" class="btn btn-success" data-dismiss="modal" aria-hidden="true">YES TO CONTINUE</button>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true" onclick="pick_deposit()">CANCEL (PAY FROM DEPOSIT)</button>
            </div>
        </div>
    </div>
</div>