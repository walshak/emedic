 <?php
  session_start();
  include("../Connections/Conn.php");
  include("../inc/credit_current_balance.php");

  if (isset($_POST["add_services_id"]) or isset($_POST["add_services_id_grp"])) {
    if (isset($_POST["add_services_id"])) {

      $add_services_id = $_POST["add_services_id"];
      $parts = explode("__", $add_services_id);
      $id = $parts['0'];
      $emr = $parts['1'];
      $service_type = 'service_type';
    }

    if (isset($_POST["add_services_id_grp"])) {
      $add_services_id = $_POST["add_services_id_grp"];

      $parts = explode("__", $add_services_id);
      $id = $parts['0'];
      $emr = $parts['1'];

      $service_type = 'service_type3';
    }
    //	$discount_charge=$parts['2'];


    $stmt = $db->prepare("SELECT sn, service_item, discount_charge, percentage_flat, percentage_flat_value, duration, specify_count, count_bal, setby, status_date  FROM patient_discount_services WHERE individual_group_no = :individual_group_no ORDER BY sn");
    $stmt->bindParam(':individual_group_no', $emr, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0): ?>

     <div id="test_fields">
       <table class="table table-striped">
         <thead>
           <tr>
             <th>No</th>
             <th>Service Name</th>
             <th>Posting Type</th>
             <th>Percentage Flat</th>
             <th>Duration</th>
             <th>Count/Balance</th>
             <th>Action</th>
           </tr>
         </thead>
         <tbody>
           <?php $n = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
             <tr>
               <td><?= $n ?></td>
               <td><?= htmlspecialchars($row['service_item']) ?></td>
               <td><?= htmlspecialchars($row['discount_charge']) ?></td>
               <td><?= htmlspecialchars($row['percentage_flat']) . ' (' . htmlspecialchars($row['percentage_flat_value']) . ')' ?></td>
               <td><?= htmlspecialchars($row['duration']) ?></td>
               <td><?= htmlspecialchars($row['specify_count']) . '/' . htmlspecialchars($row['count_bal']) ?></td>
               <td>
                 <input
                   type="button"
                   name="Delete"
                   value="Delete"
                   id="<?= $row["sn"] . '__' . $id . '__' . $emr . '__' . $row['discount_charge'] ?>"
                   class="btn btn-danger btn-xs add_services_item_del"
                   data-target="#myModal5" />
               </td>
             </tr>
             <tr>
               <td colspan="7">
                 <strong>Entered By / Date:</strong>
                 <?= htmlspecialchars($row['setby']) ?> / <?= date("d, M Y", strtotime($row['status_date'])) ?>
               </td>
             </tr>
           <?php $n++;
            endwhile; ?>
         </tbody>
       </table>
     </div>

   <?php else: ?>
     <br><br>
     <strong>No Service/Item Added. Search & Select Item to add.</strong>
     <br><br>
   <?php endif; ?>


   <form method="POST" action="pacct.php">

     <table width="100%">
       <tr>
         <td>
           <div class="">
             <label for="reg_input_no" class="req">Discount/Charge</label>
             <select name="discount_charge" id="discount_charge" class="form-control" required>
               <option selected="selected" value="">Select ...</option>
               <option value="Discount">Discount</option>
               <option value="Charge">Charge</option>
             </select>
           </div>
         </td>

         <td>
           <div class="">
             <label for="reg_input_no" class="req">Type</label>
             <select name="mode1" id="mode1" class="form-control" required>
               <option selected="selected" value="">Select ...</option>
               <option value="Percentage">Percentage</option>
               <option value="Flat">Flat</option>
             </select>
           </div>
         </td>
         <td>
           <div class="">
             <label for="reg_input_no" class="req">Flat/Percent Value</label>
             <input type="text" id="mode_value1" name="mode_value1" class="form-control" maxlength="10" required>
           </div>
         </td>
       </tr>

       <tr>
         <td colspan="3">&nbsp; </td>
       </tr>
       <tr>
         <td>

           <div class="">
             <label for="reg_input_no" class="req">How long (Duration)</label>
             <select name="how_long1" id="how_long1" class="form-control" required>
               <option selected="selected" value="">Select ...</option>
               <option value="Once">Once</option>
               <option value="Limited">Limited</option>
               <option value="Always">Always</option>
             </select>
           </div>
         </td>
         <td colspan="2">
           <div class="">
             <label for="reg_input_no" class="">If Choose Limited. Enter Limit</label>
             <input type="number" id="specify_count1" name="specify_count1" class="form-control">
           </div>
         </td>
       </tr>
       <tr>
         <td colspan="3">&nbsp; </td>
       </tr>
       <tr>
         <td>

           <div class="">
             <label for="reg_input_no" class="req">Select Service Center</label>
             <select name="<?php echo $service_type; ?>" id="<?php echo $service_type; ?>" class="form-control" required>
               <option selected="selected" value="">Select ...</option>
               <option value="investigation">Investigations (Lab & Radiology)</option>
               <option value="pharmacy">Pharmacy and Nursing Consumables</option>
               <option value="nursing">Nursing Services</option>
               <option value="medical_services">Medical Services</option>
               <option value="others">Consultation/Others Services</option>
             </select>
           </div>
         </td>
         <td colspan="3">

           <div id="invest">
             <?php
              // Fetch only needed columns instead of SELECT *
              $stmt2 = $db->query("
                SELECT sn, test 
                FROM lab_scan 
                WHERE hosp_price > 0 AND combo_test = '0' 
                ORDER BY test ASC
              ");
              ?>

             <label for="investigation" class="req">Investigation</label>
             <select name="Investigation" id="investigation" data-placeholder="Search..." class="form-control">
               <option value="" selected>-- select --</option>
               <option value="0__all_investigation">All Investigation</option>

               <?php while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) : ?>
                 <option value="<?php echo htmlspecialchars($row['sn'] . '__' . $row['test']); ?>">
                   <?php echo htmlspecialchars($row['test']); ?>
                 </option>
               <?php endwhile; ?>
             </select>
           </div>


           <!-- Medical Services -->
           <div id="med">
             <?php
              $stmt = $db->query("
                SELECT sn, item_service 
                FROM prices_table 
                WHERE hosp_price > 0 AND price_table = 'Medical Services' 
                ORDER BY item_service
              ");
              ?>
             <label class="req">Medical Services</label>
             <select name="Medical" class="form-control" data-placeholder="Search...">
               <option selected value="">-- select --</option>
               <option value="0__all_Medical Services">All Medical Services</option>
               <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                 <option value="<?= htmlspecialchars($row['sn'] . '__' . $row['item_service']) ?>">
                   <?= htmlspecialchars($row['item_service']) ?>
                 </option>
               <?php endwhile; ?>
             </select>
           </div>

           <!-- Drugs and Consumables -->
           <div id="pharm">
             <?php
              $stmt = $db->query("
                SELECT sn, product_name, stock_table 
                FROM stock_table 
                WHERE hosp_price > 0 
                  AND stock_table IN ('Nursing Consumable', 'Pharmacy') 
                ORDER BY stock_table, product_name
              ");
              ?>
             <label class="req">Drugs and Consumables</label>
             <select name="pharmacy" class="form-control" data-placeholder="Search...">
               <option selected value="">-- select --</option>
               <option value="0__all_Pharmacy">All Pharmacy Drugs</option>
               <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                 <option value="<?= htmlspecialchars($row['sn'] . '__' . $row['product_name']) ?>">
                   <?= htmlspecialchars($row['product_name'] . ' (' . $row['stock_table'] . ')') ?>
                 </option>
               <?php endwhile; ?>
             </select>
           </div>

           <!-- Nursing Services -->
           <div id="nurs">
             <?php
              $stmt = $db->query("
                SELECT sn, item_service 
                FROM prices_table 
                WHERE hosp_price > 0 AND price_table = 'Nursing Services' 
                ORDER BY item_service
              ");
              ?>
             <label class="req">Nursing Services</label>
             <select name="nursing" class="form-control" data-placeholder="Search...">
               <option selected value="">-- select --</option>
               <option value="0__all_Nursing Services">All Nursing Services</option>
               <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                 <option value="<?= htmlspecialchars($row['sn'] . '__' . $row['item_service']) ?>">
                   <?= htmlspecialchars($row['item_service']) ?>
                 </option>
               <?php endwhile; ?>
             </select>
           </div>

           <!-- Other Services -->
           <div id="other_serv">
             <?php
              $stmt = $db->query("
                SELECT sn, item_service 
                FROM prices_table 
                WHERE hosp_price > 0 AND price_table IN ('Other Services', 'Consultation') 
                ORDER BY item_service
              ");
              ?>
             <label class="req">Others</label>
             <select name="others" class="form-control" data-placeholder="Search...">
               <option selected value="">-- select --</option>
               <option value="0__all_others">All Others</option>
               <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                 <option value="<?= htmlspecialchars($row['sn'] . '__' . $row['item_service']) ?>">
                   <?= htmlspecialchars($row['item_service']) ?>
                 </option>
               <?php endwhile; ?>
             </select>
           </div>

         </td>
       </tr>
     </table>

     <hr>
     <div class="pull-left">
       <button class="btn btn-primary btn-sm" type="submit" name="Save" id="Save">Add Service</button>
     </div>
     <br><br> <br><br>
     <input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
     <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
     <input type="hidden" name="MM_update" value="add_services_list" />
   </form>


   <?php  }



  if (isset($_POST["history_id"])) {
    $history_id = $_POST["history_id"];
    $stmt = $db->prepare("SELECT history FROM patient_discount WHERE sn = :sn");
    $stmt->bindParam(':sn', $history_id, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
      // Fetch and process the re
    ?>

     <table class="table table-striped">
       <thead>
         <tr>
           <th data-toggle="true">History</th>
         </tr>
       </thead>
       <tbody>
         <?php
          $n = 1;
          while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
          ?>
           <tr>
             <td><?php echo $roww['history']; ?></td>
           </tr>
         <?php } ?>
       </tbody>
     </table>

 <?php   }
  }
  ?>


 <?php

  if (isset($_POST["note_inv_id"])) {
    $note_inv_id = $_POST["note_inv_id"];
    $part = explode("__", $note_inv_id);
    $sn = $part[0];
    $patient_type = $part[1];
    $referral_name = $part[2];
    $insurance_no = $part[3];
    $discount_set = $part[4];

    $note_inv_id = $_POST["note_inv_id"];
    $stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = :sn");
    $stmt->bindParam(':sn', $note_inv_id, PDO::PARAM_STR);
    $stmt->execute();

    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row_d) {
      $emr = $row_d['hospital_no'];
      $item_services = $row_d['item_services'];
      $pay = $row_d['pay'];
    } else {
      echo "No records found.";
    }

    include("../inc/utilities.php");
    $dsc_chr_set = 0;
    if ($discount_set == '') {
      $discount_set = '0';
    }
    patient_discount($db, $emr, $patient_type, $discount_set, $referral_name, $insurance_no, $pf, $pf_value, $dsc_chr, $dura, $service_type, $dsc_chr_set, $post_type, $count_bal, $dsc_chr_set, $mySearch, $grp_idv, $grp_idv_no);

    if ($service_type == 'specify' and $dsc_chr_set == 1) {
      include_once("../inc/utilities.php");
      selected_items($db, $emr, $item_services, $mySearch, $pf, $pf_value, $dsc_chr, $dura, $post_type, $count_bal, $dsc_chr_set);
      //echo $pf;
    }

    include_once("../inc/utilities.php");
    dsc_chr_calc($pf, $dsc_chr_set, $dsc_chr, $dsc_chr_set, $pay, $pf_value, $discount, $charge, $total_chr, $total_dsc, $post_type, $count_bal);

  ?>

   <table class="table table-bordered">
     <tbody>

       <tr>
         <td>Item Services: </td>
         <td><?php echo $row_d['item_services']; ?></td>
       </tr>
       <tr>
         <td>Hospital Price: </td>
         <td><?php echo $row_d['hosp_price']; ?></td>
       </tr>
       <tr>
         <td>Claim Price: </td>
         <td><?php echo $row_d['claim_amt']; ?></td>
       </tr>
       <tr>
         <td>Quantity: </td>
         <td><?php echo $row_d['qty']; ?></td>
       </tr>
       <tr>
         <td>Invoice Number: </td>
         <td><?php echo $row_d['invoice_no']; ?></td>
       </tr>
       <tr>
         <td>Invoice Date: </td>
         <td><?php if ($row_d['invoice_date'] == '' or $row_d['invoice_date'] == '0000-00-00') {
                echo '';
              } else {
                echo date("d M Y H:i:s a ", strtotime($row_d['invoice_date']));
              } ?></td>
       </tr>
       <tr>
         <td>Invoice Status: </td>
         <td><?php if ($row_d['invoice_status'] == 0) {
                echo 'Invoice Pending';
              } else {
                echo 'Invoice Ready';
              } ?></td>
       </tr>
       <tr>
         <td>Invoice By: </td>
         <td><?php echo $row_d['invoice_by']; ?></td>
       </tr>
       <tr>
         <td>Discount: </td>
         <td><?php echo $discount; ?></td>
       </tr>
       <tr>
         <td>Additional Charge: </td>
         <td><?php echo $charge; ?></td>
       </tr>
       <tr>
         <td>Amount: </td>
         <td><?php echo $pay; ?></td>
       </tr>

       <tr>
         <td>Credit Status: </td>
         <td><?php if ($row_d['cr'] == 1) {
                echo 'Credit';
              } else {
                echo 'No';
              } ?></td>
       </tr>
       <tr>
         <td>Dispense Status: </td>
         <td><?php if ($row_d['dsp_by'] == 1) {
                echo 'Delivered';
              } else {
                echo 'No';
              } ?></td>
       </tr>
       <tr>
         <td>Paid Status: </td>
         <td><?php if ($row_d['paystatus'] == 1) {
                echo 'Amount Paid';
              } else {
                echo 'Pending';
              } ?></td>
       </tr>

       </tr>

     </tbody>
   </table>

 <?php }


  if (isset($_POST["convert_transaction_id"])) {
    $convert_transaction_id = $_POST["convert_transaction_id"];
    $stmt = $db->prepare("
          SELECT ref_value, dr_amt, date_entry, bank_name, hospital_no, lg_ref_no 
          FROM chart_ledger 
          WHERE sn = :sn
      ");
    $stmt->bindValue(':sn', $convert_transaction_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($row_d = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $paymethod    = $row_d['ref_value'];
      $dr_amt       = $row_d['dr_amt'];
      $date_entry   = $row_d['date_entry'];
      $bank         = $row_d['bank_name'];
      $hospital_no  = $row_d['hospital_no'];
      $lg_ref_no    = $row_d['lg_ref_no'];
    } else {
      echo "<div class='alert alert-warning'>Transaction not found.</div>";
    }


  ?>
   <form method="POST" action="transc.php">
     <div style="font-size: 18px">AMOUNT: <?php echo number_format($dr_amt, 2); ?></div>
     <div style="font-size: 18px"><?php echo date('d-m-Y h:i:s a', strtotime($date_entry)) ?></div>


     <div>
       <h4><B style="color: #F00;">Deposit Wrong Amount:</B><br>If the deposit amount is incorrect, please delete it here.</h4>
       <button class="btn btn-danger btn-sm" type="submit" name="delete_wrong_posting_deposit" id="" onclick="return confirm('Are you sure you want to Delete?');">Delete Deposit</button>
     </div>
     <hr>
     <?php if (strtoupper($paymethod) == 'CASH' or strtoupper($paymethod) == 'POS') { ?>

       <h4><B style="color: #F00;">Error Posting:</B>&nbsp;Instead of removing this amount <?php echo number_format($dr_amt, 2); ?> from the patient deposit and you posted a direct transaction use the button below</h4>
       <div class="pull-left">
         <button class="btn btn-success btn-sm" type="submit" name="remove_amount_deposit" id="" onclick="return confirm('Are you sure you want to Remove Amount from Existing Deposit?');">Remove from Deposit</button>
       </div>

     <?php } ?>
     <br>
     <hr>

     <input type="hidden" name="hospital_no_delete_post" value="<?= $hospital_no; ?>">
     <input type="hidden" name="dr_amt_delete" value="<?= $dr_amt; ?>">
     <input type="hidden" name="lg_ref_no" value="<?= $lg_ref_no; ?>">
     <input type="hidden" name="paymethod_" value="<?= $paymethod; ?>">



     <h3>PAYMENT CONVERTION BELOW</h3>
     <table width="100%">
       <tr>
         <td width="40%" style="padding-right:10px; ">
           <label class="req">Payment Method</label>
           <select name="paymethod" id="paymethod" class="form-control" required>
             <option value=''>Select...</option>
             <option value="cash" <?php if ($paymethod == 'cash' or $paymethod == 'Cash') { ?>selected<?php } ?>>Cash Payment</option>
             <option value="POS" <?php if ($paymethod == 'POS') { ?>selected<?php } ?>>POS Payment</option>
             <option value="Transfer" <?php if ($paymethod == 'Transfer' or $paymethod == 'Pay_by_transfer') { ?>selected<?php } ?>>Transfer Payment</option>

           </select>
         </td>
         <td style="padding-left:10px;">
           <div class="form_sep">
             <label for="reg_input_no" class="">Recieving Bank Name</label>
             <select name="bank_name" id="bank_name" class="form-control">
               <option value=''>Select...</option>
               <?php
                $stmt_bnk = $db->query("SELECT * FROM bank where biller=1 order by bank");
                while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) { ?>
                 <option value="<?php echo $row_rstbank["bank"]; ?>" <?php if ($bank == $row_rstbank["bank"]) { ?> selected <?php } ?>><?php echo $row_rstbank["bank"]; ?></option>
               <?php } ?>
             </select>
           </div>
           </div>
         </td>
       </tr>
     </table>



     <br>
     <hr>

     <div class="form_sep">
       <div class="pull-left">
         <button class="btn btn-success btn-sm" type="submit" name="save_transaction" id="" onclick="return confirm('Are you sure you want to save?');">Convert Amount</button>
       </div>
       <div class="pull-right">
         <button type="button" data-dismiss="modal" aria-hidden="true">Close</button>
       </div>
     </div>
     <input type="hidden" name="convert_transaction_id" value="<?= $convert_transaction_id; ?>">
   </form>

 <?php }

  if (isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];
    $part = explode("__", $delete_id);
  ?>

   <h3>Are you sure you want to delete the item selected?</h3>
   <form method="POST" action="<?php if ($part[1] == 'dsc') { ?>index.php?discount<?php } else { ?>pacct.php<?php } ?>">



     <div class="pull-left">
       <button class="btn btn-danger btn-sm" type="submit" name="delete_confirm" id="delete_confirm">Delete</button>
     </div>
     <div class="pull-right">
       <a href="" class="btn btn-success btn-sm">Cancel</a>
     </div>

     <input type="hidden" value="<?php echo $part[0]; ?>" name="item_id" />
     <input type="hidden" value="<?php echo $part[1]; ?>" name="table_id" />
     <input type="hidden" value="<?php echo $part[2]; ?>" name="emr" />
     <!--  <input type="hidden" name="MM_update" value="delete_confirm" />  
-->
   </form>

   <?php
  }



  if (isset($_POST["search_emr"])) {
    $error = 0;
    $auth = 0;
    $fullname = 'Not Available';
    $created_by = '';
    $exist = 0;
    $search_emr = $_POST["search_emr"];
    $stmt = $db->prepare("SELECT * FROM billing_dep_confirm WHERE hospitla_no = :hospitla_no ORDER BY sn DESC LIMIT 1");
    $stmt->bindParam(':hospitla_no', $search_emr, PDO::PARAM_STR);
    $stmt->execute();

    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row_d) {
      $hosp_no_beneficiary = $row_d['hosp_no'];
      $current_balance = $row_d['current_balance'];
      $auth_code = $row_d['auth_code'];
      $auth_amount = $row_d['amount'];
      $authorized = ''; // This variable is not set in the original code snippet
      $transc_type = $row_d['transc_type'];
      $mode_pay = $row_d['mode_pay'];
    } else {
      echo "No records found.";
    }

    $stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='Patient Deposit'");
    if ($stmt->rowCount() > 0) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $credit_gl_account = $row['account_code'];
    } else {
      $credit_gl_account = '';
      $error == 1; ?>
     <h2>
       No Patient Deposit Ledger/Account
       Contact the Accountant for Generation!
     </h2>
   <?php
    }




    if ($transc_type == 'Transfer_to_patient') {
      $error_title = 0;
      $error_title2 = 0;
      $error_title3 = 0;
      /// search for beneficiary
      $stmt = $db->query("SELECT surname,fname,oname,hmo_no FROM enrollee WHERE hospital_no='$hosp_no_beneficiary'");
      if ($stmt->rowCount() > 0) {
        $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
        $fullname = $rowx['surname'] . ', ' . $rowx['fname'] . ' ' . $rowx['oname'];
        $hmo_no = $rowx['hmo_no'];

        if ($hmo_no != '1000') {
          $exist = 1;
        }
      } else {
        /// second chance
        $stmt = $db->query("SELECT cust_name FROM pharm_ext WHERE transc_code='$hosp_no_beneficiary'");
        if ($stmt->rowCount() > 0) {
          $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
          $fullname = $rowx['cust_name'];
        } else {
          //// not found
          $exist = 1;
          echo $error_title = "Hospital Number: $hosp_no_beneficiary you wish to transfer money to does not exist<br>";
        }
      }
      //////////////////// end of beneficiary 
      /// check giver account number
    }


    //------------------------------------------------------------------------------------	
    if (($transc_type == 'Transfer_to_patient' and $_SESSION['transfer'] == 0) or ($transc_type == 'Refund' and $_SESSION['refund'] == '0')) {
      $set_date = date("Y-m-d");
      $stmt = $db->query("SELECT vi.voucher_code,v.amount,v.created_by FROM vouchers_inventory as vi INNER JOIN vouchers as v ON vi.batch_code = v.batch_code WHERE vi.voucher_code='$auth_code' and vi.used_status='0' and v.date_expire>='$set_date' and v.batch_type='$transc_type'");
      if ($stmt->rowCount() == 0) {
        $auth = 1;
        $created_by = '';
      } else {
        $rowx = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth_amount = $rowx['amount'];
        $created_by = $rowx['created_by'];
        $authorized = 'Authorized ';
        $auth = 0;
      }
    }

    ?>
   <form method="post" id="" action="pacct.php">

     <strong><?php echo $authorized; ?>Amount: &nbsp;</strong><strong style="font-size:24px"><?php echo number_format($auth_amount, 2); ?></strong>
     <small>/only</small>
     <?php
      if ($auth_amount > $current_balance and ($transc_type == 'Transfer_to_patient')) {
        $error = 1; ?>
       &nbsp;<strong style="color:#F00">Insufficient Amount</strong>
     <?php  }

      if ($transc_type == 'Deposit' and ($mode_pay == 'cash' or $mode_pay == 'POS' or $mode_pay == 'Transfer')) {
        echo '<h3>Are you sure you want to <u>deposit</u> <br> the amount with the details attached.</h3>';

        $emr = $hosp_no_beneficiary;
      } elseif ($transc_type == 'Refund' and $error == 0 and $auth == 0) {
        echo '<h3>Are you sure you want to <u>Refund</u> <br> the amount with the details attached.</h3>';
      } elseif ($transc_type == 'Transfer_to_patient' and $error == 0) {

        $emr = $hosp_no_beneficiary;

        $general_credit_limit =  $_SESSION['credit_limit_status'];
        $items = call_current_balance($db, $emr, $general_credit_limit);
        $current_balance =  $items["current_balance"];
        $insurance_type =  $items["insurance_type"];
        $dr_amt = $auth_amount;
        $bal_beneficiary = $auth_amount + $current_balance;
      } ?>

     <table class="table table-bordered">
       <tbody>

         <?php if ($row_d['transc_type'] == 'Transfer_to_patient') { ?>

           <tr>
             <td>Hospital No/ <?php echo $hosp_no_beneficiary; ?></td>
             <td><strong>Name/ <?php echo $fullname; ?></strong></td>
           </tr>

         <?php } ?>

         <tr>
           <td><?php if ($row_d['mode_pay'] == 'cash' and $transc_type == 'Deposit') {
                  echo 'Cash Payment<br>';
                } elseif ($row_d['mode_pay'] == 'POS' and $transc_type == 'Deposit') {
                  echo 'POS Payment<br>';
                } elseif ($row_d['mode_pay'] == 'Transfer' and $transc_type == 'Deposit') {
                  echo 'Payment by Bank Transfer<br>';
                } elseif ($transc_type == 'Transfer_to_patient') {
                  echo 'Fund Transfer';
                } elseif ($transc_type == 'Refund') {
                  //	echo 'Refund Money<br>';	
                }

                ?>
             <?php
              if ($auth == 1 and $_SESSION['refund'] == '0') { ?>
               <strong style="color: #F00"> Invalid Authorization Code!</strong>

             <?php } elseif ($auth == 1 and $_SESSION['transfer'] == '0') { ?>
               <strong style="color: #F00"> Invalid Authorization Code!</strong>

             <?php } elseif ($transc_type == 'Deposit' and $mode_pay == 'Transfer') {
                ///$error=1;
              ?>
               <!-- <strong style="color: #F00"> Invalid Deposit Payment Mode (Choose Cash or POS)</strong> -->

             <?php } elseif ($transc_type != 'Deposit' and $error == 0 and $auth == 0) { ?>

               <strong>Authorization Code:</strong> &nbsp; <?php echo $row_d['auth_code']; ?>
               <?php if ($created_by != '') { ?><br><strong>Authorized by:</strong> &nbsp; <?php echo $created_by;
                                                                                          } ?>

             <?php } ?>

             <?php if ($exist == 1 and $transc_type == 'Transfer_to_patient' && $hmo_no != '1000') { ?>
               <br><strong style="color:#F00"> -Beneficiary Must Be Private Patient!</strong>
             <?php } elseif ($exist == 1 and $transc_type == 'Transfer_to_patient') { ?>
               <br><strong style="color:#F00"> -Beneficiary Hospital Number Does Not Exist!</strong>
             <?php } ?>

           </td>
           <td> <strong>Description:</strong> <br><?php echo $row_d['descrip']; ?></td>
         </tr>
       </tbody>
     </table>

     <?php

      ///if(($mode_pay=='POS' or $mode_pay=='Transfer') and $transc_type=='Deposit'){
      if ($mode_pay == 'POS' or $mode_pay == 'Transfer') {



      ?>
       <div class="form_sep">
         <strong>POS/Transfer Additional Details</strong>
       </div>

       <div class="form_sep">
         <label for="reg_input_no" class="">POS Ref/No or Account No</label>
         <input type="text" id="ref_no" name="ref_no" class="form-control">
       </div>

       <?php
        $query_rstSelect = $db->query("SELECT bank FROM bank where biller=1 order by bank");
        $query_rstSelect->execute();
        ?>
       <div class="form_sep">
         <label for="reg_input_no" class="" style="color: darksalmon; ">Bank Name</label>
         <select name="bank_name" id="bank_name" class="form-control" required>
           <option selected="selected" value="">Select ...</option>
           <?php while ($rwcep = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
             <option value="<?php echo $rwcep['bank']; ?>"><?php echo $rwcep["bank"]; ?></option>
           <?php } ?>
         </select>
       </div>

     <?php } ?>



     <div class="form_sep">
       <div class="pull-left">

         <button type="button" class="btn btn-success btn btn-xs" id="final_save_money_"
           onclick="final_save_money_BTN()" <?php
                                            if ($auth == 1 or $error == 1 or $exist == 1) { ?>disabled <?php } ?>>Save</button>


       </div>

       <div class="pull-right">

         <a href="pacct.php?emr=<?php echo $search_emr = $_POST["search_emr"]; ?>" class="btn btn-danger btn btn-xs">Cancel</a>
       </div>

     </div>
     <input type="hidden" name="amount" id="amount" value="<?php echo $auth_amount; ?>" />
     <input type="hidden" name="insurance_no" id="insurance_no" value="<?php echo $insurance_no; ?>" />
     <input type="hidden" name="credit_gl_account" id="credit_gl_account" value="<?php echo $credit_gl_account; ?>" />
     <input type="hidden" name="transc_type" id="transc_type" value="<?php echo $transc_type; ?>" />
     <input type="hidden" name="mode_pay" id="mode_pay" value="<?php echo $mode_pay; ?>" />

     <input type="hidden" name="hosp_no_bene" id="hosp_no_bene" value="<?php echo $hosp_no_beneficiary; ?>" />
     <input type="hidden" name="hosp_no_giver" id="hosp_no_giver" value="<?php echo $_POST["search_emr"]; ?>" />

     <input type="hidden" name="auth_amount" id="auth_amount" value="<?php echo $dr_amt; ?>" />
     <input type="hidden" name="bal_beneficiary" id="bal_beneficiary" value="<?php echo $bal_beneficiary; ?>" />
     <input type="hidden" name="wallet_account" id="wallet_account" value="<?php echo $wallet_account; ?>" />
   </form>

 <?php } ?>

 <?php

  if (isset($_POST["search_detials"])) {
    $search_detials = trim($_POST["search_detials"]);
    $search_detials_split = $search_detials;
    $search_detials = "%$search_detials%";
    $length_search = strlen($search_detials);

    if (preg_match("/[a-z]/i", $search_detials)) {

      $word_count = str_word_count($search_detials);
      if ($word_count == 1) {
        $query = $db->prepare("SELECT hospital_no,surname,fname,phone FROM enrollee WHERE surname like :surname or fname like :fname");
        $query->bindParam(':surname', $search_detials);
        $query->bindParam(':fname', $search_detials);
      } else {

        $partt = explode(" ", $search_detials_split);
        $query = $db->prepare("SELECT hospital_no,surname,fname,phone FROM enrollee 
				WHERE surname like :surname and fname like :fname");
        $query->bindParam(':surname', $partt[0]);
        $query->bindParam(':fname', $partt[1]);
      }
    } elseif ($length_search <= 8) {

      $query = $db->prepare("SELECT hospital_no,surname,fname,phone FROM enrollee 
WHERE hospital_no like :hospital_no or old_hospital_no like :old_hospital_no");
      $query->bindParam(':hospital_no', $search_detials);
      $query->bindParam(':old_hospital_no', $search_detials);
    } elseif ($length_search > 10) {
      $query = $db->prepare("SELECT hospital_no,surname,fname,phone FROM enrollee 
WHERE phone like :phone");
      $query->bindParam(':phone', $search_detials);
    }

    $query->execute();
    if ($query->rowCount() > 0) {

      if ($query->rowCount() == 1) {
        header('Content-Type: application/json');

        $roww = $query->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["status" => 200, "redirect" => true, "hosp_no" => $roww['hospital_no']]);
        exit;
      }

  ?>

     <table class="table table-striped">
       <thead>
         <tr>
           <th data-toggle="true">History</th>
           <th data-toggle="true">Surname</th>
           <th data-toggle="true">First Name</th>
           <th data-toggle="true">Phone</th>
           <th data-toggle="true">Action</th>
         </tr>
       </thead>
       <tbody>
         <?php
          $n = 1;
          while ($roww = $query->fetch(PDO::FETCH_ASSOC)) {
          ?>
           <tr>
             <td><?php echo $roww['hospital_no']; ?></td>
             <td><?php echo $roww['surname']; ?></td>
             <td><?php echo $roww['fname']; ?></td>
             <td><?php echo $roww['phone']; ?></td>
             <td>
               <a href="pacct.php?emr=<?php echo $roww['hospital_no']; ?>"><i class="fa fa-search-plus"></i> &nbsp;Go </button></a>
             </td>

           </tr>
         <?php } ?>
       </tbody>
     </table>

 <?php   } else {
      echo 'NotFound';
      exit;
    }
    exit;
  }
  ?>


 <script>
   $('#add_services_form_form').on("submit", function(event) {


     event.preventDefault();

     $.ajax({
       url: "insert.php",
       method: "POST",
       data: $('#add_services_form_form').serialize(),
       beforeSend: function() {
         $('#Save').val("Updating");
       },
       success: function(data) {
         $('#add_services_modal').modal('hide');

         //$('#test_fields').html(data);  
       },
       complete: function() {
         $('#add_services_form_form').val("Insert");
       },
       error: function(data) {

         alert("Oops...", "Something went wrong :(", "error");
         //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
       }
     });
   });



   $('#confirm_delete_form').on("submit", function(event) {
     event.preventDefault();

     $.ajax({
       url: "delete.php",
       method: "POST",
       data: $('#confirm_delete_form').serialize(),
       beforeSend: function() {
         $('#Delete').val("Deleting");
       },
       success: function(data) {
         $('#confirm_delete_modal').modal('hide');

         //$('#test_fields').html(data);  
       },
       complete: function() {
         $('#confirm_delete_form').val("Deleted");
       },
       error: function(data) {

         alert("Oops...", "Something went wrong :(", "error");
         //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
       }
     });
   });



   $(document).ready(function() {
     $("#invest").hide();
     $("#med").hide();
     $("#pharm").hide();
     $("#other_serv").hide();
     $("#nurs").hide();

     $('#<?php echo $service_type; ?>').on('change', function() {

       if (this.value == 'investigation') {
         $("#invest").show();
         $("#med").hide();
         $("#pharm").hide();
         $("#other_serv").hide();
         $("#nurs").hide();
       }

       if (this.value == 'medical_services') {
         $("#med").show();
         $("#invest").hide();
         $("#pharm").hide();
         $("#other_serv").hide();
         $("#nurs").hide();
       }

       if (this.value == 'pharmacy') {
         $("#med").hide();
         $("#invest").hide();
         $("#pharm").show();
         $("#other_serv").hide();
         $("#nurs").hide();
         $("#nurs").hide();
       }

       if (this.value == 'nursing') {
         $("#med").hide();
         $("#invest").hide();
         $("#pharm").hide();
         $("#other_serv").hide();
         $("#nurs").show();
       }

       if (this.value == 'others') {
         $("#med").hide();
         $("#invest").hide();
         $("#pharm").hide();
         $("#other_serv").show();
         $("#nurs").hide();
       }

     });
   });


   function final_save_money_BTN() {

     var amount = document.getElementById('amount').value;
     var credit_gl_account = document.getElementById('credit_gl_account').value;
     var transc_type = document.getElementById('transc_type').value;
     var hosp_no_bene = document.getElementById('hosp_no_bene').value;
     var hosp_no_giver = document.getElementById('hosp_no_giver').value;
     var auth_amount = document.getElementById('auth_amount').value;
     var bal_beneficiary = document.getElementById('bal_beneficiary').value;
     var mode_pay = document.getElementById('mode_pay').value;
     var wallet_account = document.getElementById('wallet_account').value;
     var final_save_money = true;


     if (transc_type == 'Transfer_to_patient') {

       var bank_name = '';
       var ref_no = '';

     } else {

       if (mode_pay == 'POS' || mode_pay == 'Transfer') {
         var bank_name = document.getElementById('bank_name').value;

         if (bank_name == '') {
           toastr.error('Invalid Bank Name', 'Error', {
             timeOut: 5000
           })
           exit;
         }

       }


       if (mode_pay != 'cash') {
         var bank_name = document.getElementById('bank_name').value;
         var ref_no = document.getElementById('ref_no').value;
       } else {
         var bank_name = '';
         var ref_no = '';
       }

     }

     document.getElementById("final_save_money_").disabled = false;
     document.getElementById("final_save_money_").innerHTML = 'Wait ...';

     $.ajax({
       url: "pacct_process.php",
       method: "POST",
       data: {
         final_save_money: final_save_money,
         amount: amount,
         credit_gl_account: credit_gl_account,
         transc_type: transc_type,
         hosp_no_bene: hosp_no_bene,
         hosp_no_giver: hosp_no_giver,
         auth_amount: auth_amount,
         bal_beneficiary: bal_beneficiary,
         ref_no: ref_no,
         bank_name: bank_name,
         wallet_account: wallet_account
       },
       success: function(data) {

         ///  alert(data);


         var jsonn = JSON.parse(data);

         if (jsonn["status"] == 2) {
           toastr.success(jsonn["message"], 'Success', {
             timeOut: 5000
           })
           window.location = 'pacct.php?emr=' + hosp_no_giver + '&dep=' + jsonn["url"];

         } else if (jsonn["status"] == 3) {
           toastr.success(jsonn["message"], 'Success', {
             timeOut: 5000
           })
           window.location = 'pacct.php?emr=' + hosp_no_giver + '&rfd=' + jsonn["url"];

         } else if (jsonn["status"] == 1) {
           document.getElementById("final_save_money_").disabled = false;
           document.getElementById("final_save_money_").innerHTML = 'Save';
           toastr.error(jsonn["message"], 'Attention', {
             timeOut: 5000
           })
         } else {
           toastr.success(jsonn["message"], 'Success', {
             timeOut: 5000
           })
           window.location = 'pacct.php?emr=' + hosp_no_giver + '&pay';
         }
       }
     });


   }
 </script>