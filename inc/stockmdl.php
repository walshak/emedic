<div class="modal inmodal fade" id="add_stock_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Adding New Stock</h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="add_stock_form">
                     
             <div class="form_sep">
                  <label for="reg_input_no" class="req">Stock Name</label>
                <input type="text" id="stockname" name="stockname" class="form-control" required  maxlength="100">
            </div>     

            <div class="form_sep">
            <label for="reg_input_no" class="req">Category</label>
              <select name="category" id="category" class="form-control" required style="font-size:14px">
              <option value="">-- select--</option>
              
                <?php  $stmt=$db->query("SELECT * FROM lab_stocks_cat"); if ($stmt->rowCount()>0){?> 
                	<?php while($row=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
            <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
            		<?php }
				}?>
            </select>
            </div>  
                    
            <div class="form_sep"> 
            <label for="reg_input_no" class="req">Purchase Cost</label>
            <input type="number" id="purchase_cost" name="purchase_cost" class="form-control" onkeyup="sum();" min="0" > 
            </div>        
               
            <div class="form_sep"> 
            <label for="reg_input_no" class="">Batch Number</label>
            <input type="text" id="batchno" name="batchno" class="form-control"  maxlength="10"> 
            </div>   
            
<div class="form_sep" id="data_1">
<label for="reg_input_no" class="req">Expired Date</label>
<div class="input-group date">
<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
<input type="text" name="expired" id="expired" class="form-control" required>
</div>
</div>             
                                         
<div class="form_sep">                       
<label for="reg_input_no" class="req">Re-order level</label>
<input type="number" id="reorder" name="reorder" class="form-control" onkeyup="sum();" min="0" required>
</div>
                    
<div class="form_sep">                       
<label for="reg_input_no" class="req">New Quantity</label>
<input type="number" id="qty" name="qty" class="form-control" onkeyup="sum();" min="0" required>
</div>

<div class="form_sep">                       
<label for="reg_input_no" class="req">Total Units/Piece in each Stock<br><small>(Any Number Specify Would Be Used To Compute Whole Stocks)</small></label>
<input type="number" id="units" name="units" class="form-control" onkeyup="sum();" min="1" required>
</div>

<div class="form_sep">                       
<label for="reg_input_no" class="">Unit of Measurement&nbsp; <small>e.g mills/qty</small></label>
<input type="text" id="measurement" name="measurement" class="form-control" maxlength="5">
</div>

                    <div class="form_sep">
                    <button class="btn btn-primary btn-xs" type="submit" name="add_btn" >Save</button>
                    </div>
                    <br>
                                  <input type="hidden" name="MM_update" value="adding_stocks" />
                                  <input type="hidden" name="stock_id" id="stock_id" />
                                  
                                  <input type="text" name="stock_table" id="stock_table" value="<?php echo $stock_table; ?>" />
                                  
                                </form>
                </div>  
           </div>  
      </div>  
 </div>
 
 
 
 <div class="modal inmodal fade" id="add_category_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Adding New Stock Category</h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="add_category_form">

<div class="form_sep">                       
<label for="reg_input_no" class="req">Category Name</label>
<input type="text" id="cat" name="cat" class="form-control" maxlength="100">
</div>

                    <div class="form_sep">
                    <button class="btn btn-primary btn-xs" type="submit" name="add_cat" >Add</button>
                    </div>
                    <br>
                                  <input type="hidden" name="MM_update" value="adding_cat" />
                                 
                                </form>
                </div>  
           </div>  
      </div>  
 </div>
 
 
 <div class="modal inmodal fade" id="inventory_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Stocks Inventory</h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="inventory_form">
              
                <div class="form_sep"> 
                <label for="reg_input_no" class="req">Current Quantity</label>
                <input type="number" id="mgt_old_qty" name="mgt_old_qty" class="form-control" readonly>             
                </div>

                <div class="form_sep">
                <label for="reg_select" class="req">Select Entry Mode</label>
                <select name="qty_type" id="qty_type" class="form-control" required>
                        <option selected="selected" value="">Select...</option>
                     <option value="1">Add Quantity</option>
                     <option value="2">Remove Quantity</option>
                </select>
                </div>
                
                <div class="form_sep"> 
                <label for="reg_input_no" class="req">Enter Quantity</label>
                <input type="number" id="new_qty" name="new_qty" class="form-control" onkeyup="sum();" min="1" required>             
                </div>
                              
                <div class="form_sep">
                <label for="reg_select" class="req">Description Task</label>
                <select name="desc" id="desc" class="form-control" required>
                        <option selected="selected" value="">Select...</option>
                     <option value="Add:">Add to Stock</option>
                     <option value="Expired:">Expired Items</option>
                     <option value="Damage:">Damage Items</option>
                     <option value="Missing:">Missing Items</option>
                     <option value="Return:">Return Items</option>
                     <option value="Others:">Others [Describe below]</option>
                </select>
                </div>
                
                 
                
                
                <div class="form_sep">
                <label for="reg_input_no" class="req">Describe Here</label>
              <input type="text" id="desc2" name="desc2" class="form-control"  required placeholder="" maxlength="30">                 </div>
                            
            <div class="form_sep" id="data_1">
            <label for="reg_input_no">New Expiry Date</label>
            <div class="input-group date">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" name="mgt_expired" id="mgt_expired" class="form-control">
            </div>
            </div> 
                
                               <div class="form_sep">
                    <button class="btn btn-primary btn-xs" type="submit" name="add_inven" >Save</button>
     
                      </div>        
                    <br>
                                  <input type="hidden" name="MM_update" value="stock_inven" />
                                 <input type="hidden" name="mgt_stock_id" id="mgt_stock_id" />
                                 <input type="hidden" name="mgt_unit" id="mgt_unit" />
                                </form>
                </div>  
           </div>  
      </div>  
 </div>