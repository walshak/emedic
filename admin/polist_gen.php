 <?php
    if (isset($_POST['save_pre_order_list2'])) {
        // Loop through the submitted values
        foreach ($_POST['save_pre_order_list2'] as $index => $value) {
            // Retrieve the specific values for each row using the index
            $stockName = $_POST['stockName_' . $index];
            $orderQty = $_POST['order_qty_' . $index];
            $buyingCost = $_POST['buying_cost_' . $index];
            $total = $_POST['total_' . $index];


            ///echo $total;

            // Process or store the values as needed
            // ...
        }
    }

    if (isset($_POST["save_pre_order_list"])) {
    }


    if (isset($_GET['reorder'])) {
        $supress_re_order = " and qty<=reorder_level";
    } else {
        $supress_re_order = "";
    }


    ////echo $supress_re_order . $search . $search_plus;

    $stmt = $db->query("SELECT * FROM stock_table where status='active' $supress_re_order $search $search_plus");
    if ($stmt->rowCount() > 0) { ?>
     <h3>Start INVENTORY PROCESS here ... // OR <a href="index.php?stock=<?= $stock; ?>&reorder&polist_gen"> CLICK HERE </a> TO FILTER <u><strong> RE-ORDER LEVEL ITEMS ONLY </strong></u> </h3>
     <hr>
     <div id="selected_PO_LIST"></div>
     <strong style="color: red;">Follow the step below: </strong>

     <?php if (isset($_POST['supplier_id'])) {
            $supplier_id = $_POST['supplier_id'];
        }
        ?>

     <?php /*?><form action="index.php?stock=<?= $stock; ?>&sv&polist_gen" method="post" >
<?php */ ?>


     <div class="form_sep" id="">
         <h3 for="reg_input_no" class="req"><strong style="color: red;">STEP 1:</strong> Select <I>VENDOR/SUPPLIER</I></h3>
         <select name="supplier_id" id="supplier_id" class="form-control" style="font-size:16px" onChange="selected_batch()" required>
             <option value="">-- select--</option>

             <?php $stmtxx = $db->query("SELECT * FROM stock_company order by name");
                if ($stmt->rowCount() > 0) { ?>
                 <?php while ($row = $stmtxx->fetch(PDO::FETCH_ASSOC)) { ?>
                     <option <?php if ($row['sn'] == $supplier_id) { ?> selected <?php } ?> value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
             <?php }
                } ?>
         </select>
     </div>

     <div class="form_sep" id="">
         <h3 for="reg_input_no" class="req"><strong style="color: red;">STEP 2:</strong> Select <I>NEW PO / EXISTING BATCH</I></h3>
         <div class="form_sep" id="doctorname_div">
             <div class="form-group">
                 <label><strong>Existing/PO/New</strong></label>
                 <select class="form-control" name=generated_po id="generated_po" onChange="selected_PO_LIST('empty')" style="font-size: 15px; ">
                     <option value="">-- Not Applicable --</option>
                 </select>
             </div>
         </div>
     </div>

     <br>

     <div class="form_sep" id="">
         <h3 for="reg_input_no" class="req"><strong style="color: red;">STEP 3:</strong> Select <I>PURCHASING ORDER (PO) DEPARTMENT</I></h3>

         <div class="form_sep">
             <select name="p_order_dept" id="p_order_dept" class="form-control">
                 <option selected="selected" value="">Search and Select Items</option>

                 <?php
                    $stmtxx = $db->query("SELECT * FROM department order by department");
                    while ($row = $stmtxx->fetch(PDO::FETCH_ASSOC)) { ?>
                     <option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
                 <?php }  ?>
             </select>
         </div>



     </div>

     <br>





     <input type="hidden" id="batch_number" value="">

     <h3><strong style="color: red;">STEP 4 </strong>: &nbsp;</h3>
     <h3><I>SELECTION</I>, enter <I>QTY</I>, <i>BUYING COST</i>, and click the <i>PLUS SIGN</i> button to ADD.</h3>
     <table class="table table-striped table-bordered table-hover dataTables-example">
         <thead>
             <tr>
                 <th><i class="fa fa-tick"></i></th>
                 <th>Stock/Item</th>
                 <th width="15%">Qty</th>
                 <th width="15%">Unit Cost</th>
                 <th width="20%">Total</th>
                 <th></th>

             </tr>
         </thead>
         <tbody>

             <?php
                $n = 1;
                $ttotal = 0;
                $g_ttotal = 0;
                $approve_amt = 0;
                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $purchase_price = $roww['buying_cost'];
                    if ($purchase_price == '') {
                        $purchase_price = 0;
                    }

                ?>
                 <tr>
                     <td><?= $n; ?></td>
                     <td><?php echo $roww['product_name']; ?>

                         <input type="hidden" class="input-sm form-control" id="stockName_<?php echo $n; ?>" name="stockName_<?php echo $n; ?>" required value="<?php echo $roww['product_name']; ?>" />
                         <input type="hidden" class="input-sm form-control" id="stock_sn_<?php echo $n; ?>" name="stock_sn_<?php echo $n; ?>" required value="<?php echo $roww['sn']; ?>" />
                         <input type="hidden" class="input-sm form-control" id="stock_total_unit_<?php echo $n; ?>" name="stock_total_unit_<?php echo $n; ?>" required value="<?php echo $roww['stock_total_unit']; ?>" />
                     </td>

                     <td>

                         <div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
                             <input type="text" class="input-sm form-control" id="order_qty_<?php echo $n; ?>" name="order_qty_<?php echo $n; ?>" required value="1" onkeyup="UpdateCost()" min="1" style="width:90%;" />
                         </div>
                     </td>
                     <td>
                         <div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
                             <input type="text" class="input-sm form-control" id="buying_cost_<?php echo $n; ?>" name="buying_cost_<?php echo $n; ?>" required value="<?php echo $purchase_price; ?>" onkeyup="UpdateCost()" style=" width:90%;" />
                         </div>

                     </td>
                     <td>
                         <input type="text" class="input-sm form-control" id="total_<?php echo $n; ?>" name="total_<?php echo $sn; ?>" value="<?php $ttotal = $purchase_price * 1;
                                                                                                                                                echo number_format($ttotal); ?>" readonly style=" width:90%;" />
                         <input type="hidden" id="total2_<?php echo $n; ?>" name="total2_<?php echo $n; ?>" value="<?php echo $ttotal = $purchase_price * 1;  ?>" readonly style=" width:90%;" />

                     </td>
                     <td>
                         <button class="btn btn-primary" name="" onClick="add_order('<?= $n; ?>')">+</button>

                     </td>


                 </tr>

             <?php
                    $g_ttotal = $g_ttotal + $ttotal;
                    $n++;
                } ?>

         </tbody>
     </table>

     <input type="hidden" name="p_list" id="p_list" value="<?php echo $n - 1; ?>">

     <!--
		

					
					<div class="form_sep" >
<button class="btn btn-primary" type="submit" name="save_pre_order_list">Process Selected Item(s)</button>
</div>-->



     <input type="hidden" name="status_link" id="status_link" value="edit">

     <input type="hidden" name="stock" id="stock" value="<?php echo $stock_table; ?>">
     <input type="hidden" name="navigation" id="navigation" value="<?php echo $navigation; ?>">
     <input type="hidden" name="batch_no" id="batch_no" value="<?php echo date('Y-m-d'); ?>">
     <!--</form>
-->
 <?php } else { ?>
     <h2>No Records to Display</h2>
 <?php }
