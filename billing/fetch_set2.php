 <?php include("../Connections/Conn.php"); ?>

 <?php
  session_start();


  if (isset($_POST["part_payment_entry_id"])) {
    $sn = $_POST["part_payment_entry_id"];
    $stmt = $db->prepare("
        SELECT hospital_no, app_no, item_services, pay, claim_amt, qty, 
               pay_mode, claim_interest, drug_sn, cat_type, remarks 
        FROM patient_ap_services 
        WHERE sn = :sn
    ");
    $stmt->bindParam(':sn', $sn, PDO::PARAM_STR);
    $stmt->execute();

    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row_d) {
      $emr = $row_d['hospital_no'];
      $app_no = $row_d['app_no'];
      $item_services = $row_d['item_services'];
      $pay = $row_d['pay'];
      $claim_amt = $row_d['claim_amt'];
      $qty = $row_d['qty'];
      $pay_mode = $row_d['pay_mode'];
      $claim_interest = $row_d['claim_interest'];
      $drug_sn = $row_d['drug_sn'];
      $cat_type = $row_d['cat_type'];
      $remarks = $row_d['remarks'];
    } else {
      echo "<div class='alert alert-warning'>No records found.</div>";
    }

    if ($remarks == 'pay') {
      $remarks = $sn;

      /// the essence of this code is form part payment of the package under remarks
      $stmt = $db->query("SELECT app_no FROM patient_ap_services WHERE remarks='$sn' and cr=0 and serv_group='Medical Services'");
      if ($stmt->rowCount() == 0) {
        $inv = "UPDATE patient_ap_services SET remarks='$remarks' WHERE sn='$sn'";
        $db->exec($inv);
        $remarks = $sn; ///['sn'];
      }
    }

  ?>
   <form method="POST" action="pacct.php?emr=<?php echo $emr . '&' . $view; ?>">



     <div class="form_sep">
       <label for="reg_input_no" class="req">Amount Payable</label>
       <input type="number" id="pay" name="pay" class="form-control" max="<?php echo $pay; ?>" min="1" value="<?php echo $pay; ?>" required>
     </div>


     <div class="form_sep" id="bill_account"><br>
       <label for="reg_input_no" class="req"><strong style="color: #F00">Select Part Payment Options</strong></label>
       <select name="payment_option" id="payment_option" class="form-control" required>
         <option value=''>Select...</option>
         <option value='1'>Deliver service and pay the remaining balance later</option>
         <option value='0'>Don't Deliver service until payment is completed</option>
       </select>
     </div>



     <div class="form_sep">
       <button class="btn btn-primary btn-sm" type="submit" name="amount_paying_now" id="">Save</button>
     </div>
     <input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
     <input type="hidden" name="item_services" value="<?php echo $item_services; ?>" />
     <input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
     <input type="hidden" name="amount_to_paid" id="amount_to_paid" value="<?php echo $pay; ?>" />
     <input type="hidden" name="last_sale_sn" id="sn" value="<?php echo $sn; ?>" />
     <input type="hidden" name="drug_sn" id="drug_sn" value="<?php echo $drug_sn; ?>" />
     <input type="hidden" name="cat_type" id="cat_type" value="<?php echo $cat_type; ?>" />
     <input type="hidden" name="remarks" id="" value="<?php echo $remarks; ?>" />
   </form>

 <?php


  }

  if (isset($_POST["editprice_id"])) {


    // Get the posted editprice_id or set it as empty string
    $editprice_id = isset($_POST["editprice_id"]) ? $_POST["editprice_id"] : '';

    // Split the value using double underscore
    $parts = explode("__", $editprice_id);

    // Extract the parts safely
    $sn = isset($parts[0]) ? $parts[0] : null;
    $view = isset($parts[1]) ? $parts[1] : null;


    // Proceed only if sn is not null
    if ($sn !== null) {




      // Prepare and execute the SQL statement securely
      $stmt = $db->prepare("
            SELECT hospital_no, item_services, pay, claim_amt, qty, pay_mode, 
                   interest, drug_sn, cat_type, serv_group 
            FROM patient_ap_services 
            WHERE sn = :sn
        ");

      $stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
      $stmt->execute();

      // Fetch the record
      $row_d = $stmt->fetch(PDO::FETCH_ASSOC);


      if ($row_d) {
        // Assign values from the result
        $emr           = $row_d['hospital_no'];
        $item_services = $row_d['item_services'];
        $pay           = $row_d['pay'];
        $claim_amt     = $row_d['claim_amt'];
        $qty           = $row_d['qty'];
        $pay_mode      = $row_d['pay_mode'];
        $claim_interest = !empty($row_d['interest']) ? $row_d['interest'] : '0';
        $drug_sn       = $row_d['drug_sn'];
        $cat_type      = $row_d['cat_type'];
        $serv_group    = $row_d['serv_group'];

        // You can echo or use these variables below as needed
      } else {
        echo "<div class='alert alert-warning'>No record found for sn = " . htmlspecialchars($sn) . "</div>";
      }
    }



  ?>

   <form method="POST" action="pacct.php?emr=<?php echo $emr . '&' . $view; ?>">

     <div class="form_sep">
       <label for="reg_input_no" class="req">Service Name/Item</label>
       <input type="text" id="item_services" name="item_services" value="<?php echo $item_services; ?>" class="form-control" required>
     </div>


     <div class="form_sep">
       <label for="reg_input_no" class="req">Amount Payable</label>
       <input type="text" id="pay" name="pay" class="form-control" value="<?php echo $pay; ?>" required>
     </div>

     <div class="form_sep">
       <label for="reg_input_no" class="req">Claim</label>
       <input type="text" id="claim" name="claim" class="form-control" value="<?php echo $claim_amt; ?>" required>
     </div>

     <div class="form_sep">
       <label for="reg_input_no" class="req">Claim Interest</label>
       <input type="text" id="claim_interest" name="claim_interest" class="form-control" value="<?php echo $claim_interest; ?>" required>
     </div>

     <div class="form_sep">
       <label for="reg_input_no" class="req">Quantity</label>
       <input type="number" id="qty" name="qty" class="form-control" maxlength="3" value="<?php echo $qty; ?>" required>
     </div>


     <div class="form_sep">
       <label for="reg_input_no" class="req">Type</label>
       <select name="mode" id="mode" class="form-control" required>
         <option value="claim" <?php if ($pay_mode == 'claim') { ?> selected="selected" <?php } ?>>claim</option>
         <option value="cash" <?php if ($pay_mode == 'cash') { ?> selected="selected" <?php } ?>>cash payment</option>
       </select>
     </div>

     <div class="form_sep">
       <button class="btn btn-primary btn-sm" type="submit" name="edit_update_price" id="edit_update_price">Save Changes</button>
     </div>
     <input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
     <input type="hidden" name="sn" id="sn" value="<?php echo $sn; ?>" />
     <input type="hidden" name="drug_sn" id="drug_sn" value="<?php echo $drug_sn; ?>" />
     <input type="hidden" name="cat_type" id="cat_type" value="<?php echo $cat_type; ?>" />
     <input type="hidden" name="serv_group" id="serv_group" value="<?php echo $serv_group; ?>" />
   </form>

   <?php
  }



  if (isset($_POST["search_detials"])) {

    $search_detials = trim($_POST["search_detials"]);
    $target = trim($_POST["target"]);

    $search_detials = trim($_POST["search_detials"]);
    $search_detials2 = trim($_POST["search_detials"]);
    $search_detials_split = $search_detials;
    $search_detials = "%$search_detials%";
    $length_search = strlen($search_detials2);

    $word_count = str_word_count($search_detials);
    $partt = explode(" ", $search_detials_split);
    if (count($partt) == 1) {


      if (preg_match("/[a-z]/i", $search_detials)) {

        $query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
            FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
              WHERE surname like :surname or fname like :fname or oname like :oname");
        $query->bindParam(':surname', $search_detials);
        $query->bindParam(':fname', $search_detials);
        $query->bindParam(':oname', $search_detials);
      } elseif ($length_search >= 3  && $length_search <= 6) {
        $query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname,e.phone , e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE e.hospital_no like :hospital_no or e.old_hospital_no like :old_hospital_no");
        $query->bindParam(':hospital_no', $search_detials);
        $query->bindParam(':old_hospital_no', $search_detials);
      } elseif ($length_search >= 10) {
        $query = $db->prepare("SELECT e.hospital_no, e.surname,e.fname,e.phone , e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no WHERE e.phone like :phone");
        $query->bindParam(':phone', $search_detials);
      } else {
        echo 'Search Text Specified Not Available!';
        exit;
      }

      $query->execute();
    } else if (count($partt) == 2) {

      $partt = explode(" ", $search_detials_split);
      $name1 = $partt[0];
      $name2 = $partt[1];
      $name1 =  "$name1%";
      $name2 =  "$name2%";

      $query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
          FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
          WHERE 
            (e.surname LIKE :name1 AND e.fname LIKE :name2)  OR
            (e.surname LIKE :name3 AND e.fname LIKE :name4)  OR
            (e.oname LIKE :name5 AND e.surname LIKE :name6) OR
            (e.oname LIKE :name7 AND e.surname LIKE :name8) OR
            (e.fname LIKE :name9 AND e.oname LIKE :name10) OR 
            (e.fname LIKE :name11 AND e.oname LIKE :name12) OR
            (e.fname LIKE :name13 AND e.oname LIKE :name14) 
            
            ");

      $query->bindParam(':name1', $name1);
      $query->bindParam(':name2', $name2);
      $query->bindParam(':name3', $name2);
      $query->bindParam(':name4', $name1);
      $query->bindParam(':name5', $name1);
      $query->bindParam(':name6', $name2);
      $query->bindParam(':name7', $name2);
      $query->bindParam(':name8', $name1);
      $query->bindParam(':name9', $name1);
      $query->bindParam(':name10', $name2);
      $query->bindParam(':name11', $name2);
      $query->bindParam(':name12', $name1);
      $query->bindParam(':name13', $search_detials_split);
      $query->bindParam(':name14', $search_detials_split);
      $query->execute();
    } else if (count($partt) == 3) {

      $partt = explode(" ", $search_detials_split);
      $name1 = $partt[0];
      $name2 = $partt[1];
      $name3 = $partt[2];



      $name1 =  "$name1%";
      $name2 =  "$name2%";
      $name3 =  "$name3%";

      $query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
          FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
          WHERE 
            (e.fname LIKE :name2 AND e.surname LIKE :name1 AND e.oname LIKE :name3)  OR
            (e.fname LIKE :name4 AND e.surname LIKE :name5 AND e.oname LIKE :name6)  OR
            (e.fname LIKE :name7 AND e.surname LIKE :name8 AND e.oname LIKE :name9)  OR
            (e.fname LIKE :name10 AND e.surname LIKE :name11 AND e.oname LIKE :name12)  OR
            (e.fname LIKE :name13 AND e.surname LIKE :name14 AND e.oname LIKE :name15)  OR
            (e.fname LIKE :name16 AND e.surname LIKE :name17 AND e.oname LIKE :name18)  OR

            (e.surname LIKE :name22 AND e.oname LIKE :name23 ) OR
            (e.surname LIKE :name24 AND e.fname LIKE :name25 ) OR
            (e.fname LIKE :name26 AND e.surname LIKE :name27 ) OR
            (e.fname LIKE :name28 AND e.oname LIKE :name29 ) OR
            (e.oname LIKE :name30 AND e.fname LIKE :name31 ) OR
            (e.oname LIKE :name32 AND e.surname LIKE :name33 ) 
            
            ");

      // surname fname oname : 1 2 3
      $query->bindParam(':name1', $name2);
      $query->bindParam(':name2', $name1);
      $query->bindParam(':name3', $name3);

      // fname surname oname 
      $query->bindParam(':name4', $name1);
      $query->bindParam(':name5', $name2);
      $query->bindParam(':name6', $name3);
      //
      //  oname fname surname
      $query->bindParam(':name7', $name2);
      $query->bindParam(':name8', $name3);
      $query->bindParam(':name9', $name1);

      //  fname  oname surname
      $query->bindParam(':name10', $name1);
      $query->bindParam(':name11', $name3);
      $query->bindParam(':name12', $name2);

      // surname oname fname 
      $query->bindParam(':name13', $name3);
      $query->bindParam(':name14', $name1);
      $query->bindParam(':name15', $name2);

      //  oname  surname fname
      $query->bindParam(':name16', $name3);
      $query->bindParam(':name17', $name2);
      $query->bindParam(':name18', $name1);

      $name22 = $name2 . ' ' . $name1;
      $name23 = $name3;
      $name24 = $name2 . ' ' . $name3;
      $name25 = $name1;
      $name26 = $name1 . ' ' . $name2;
      $name27 = $name3;
      $name28 = $name1 . ' ' . $name3;
      $name29 = $name2;
      $name30 = $name3 . ' ' . $name2;
      $name31 = $name2;
      $name32 = $name3 . ' ' . $name1;
      $name33 = $name1;

      $query->bindParam(':name22', $name21); // sf :21
      $query->bindParam(':name23', $name22); // o : 3
      $query->bindParam(':name24', $name23); // so :23
      $query->bindParam(':name25', $name24); // f : 1
      $query->bindParam(':name26', $name25); // fs :12
      $query->bindParam(':name27', $name26); // o  : 3
      $query->bindParam(':name28', $name27); // fo 13
      $query->bindParam(':name29', $name28); // s : 2
      $query->bindParam(':name30', $name29); // os : 32
      $query->bindParam(':name31', $name30); // f : 1
      $query->bindParam(':name32', $name31); // os :32
      $query->bindParam(':name33', $name32); // o  : 1
      $query->execute();
    } else {

      $partt = explode(" ", $search_detials_split);
      $query = $db->prepare("SELECT e.hospital_no, e.surname,fname, e.phone , e.oname, i.insurance_name  
        FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
          WHERE e.surname like :surname and e.fname like :fname");
      $query->bindParam(':surname', $partt[0]);
      $query->bindParam(':fname', $partt[1]);
      $query->bindParam(':fname', $partt[1]);
      $query->execute();
    }





    if ($query->rowCount() > 0) {

      if ($query->rowCount() == 1) {
        header('Content-Type: application/json');

        $roww = $query->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["status" => 200, "redirect" => true, "hosp_no" => $roww['hospital_no']]);
        exit;
      }

    ?>


     <!-- 				<input type="text" id="search_input" placeholder="Narrow Search" class="form-control">
-->
     <table class="table table-striped" id="search_table">
       <thead>
         <tr>
           <th data-toggle="true">Hospital No</th>
           <th data-toggle="true">Surname</th>
           <th data-toggle="true">First Name</th>
           <th data-toggle="true">Other Name</th>
           <th data-toggle="true">Phone</th>
           <th data-toggle="true">Insurance</th>
           <th data-toggle="true">Action</th>
         </tr>
       </thead>
       <tbody>
         <?php
          $n = 1;
          while ($roww = $query->fetch(PDO::FETCH_ASSOC)) {
          ?>
           <tr>
             <td><?php echo $hospital_no = $roww['hospital_no']; ?></td>
             <td><?php echo $roww['surname']; ?></td>
             <td><?php echo $roww['fname']; ?></td>
             <td><?php echo $roww['oname']; ?></td>
             <td><?php echo $roww['phone']; ?></td>
             <td><?php echo $roww['insurance_name']; ?></td>
             <td>
               <a href="pacct.php?emr=<?php echo $hospital_no; ?>" class="btn btn-primary btn-xs"><i class="fa fa-search-plus"></i> &nbsp;Go</a>
             </td>

           </tr>
         <?php } ?>
       </tbody>
     </table>

 <?php   } else {

      echo 'Not Found';
      exit;
    }
    exit;
  }

  ?>