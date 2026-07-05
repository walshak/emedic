<?php if ($_SESSION['procure'] == '1' or ($_SESSION['navigate'] == 'investigations' and $_SESSION['unit_head'] == '1')) { ?>

    <h3><strong style="color: RED;">STEP 4:</strong> FINAL STAGE</h3>
    <h2>Procurement/Purchase Ordered Approval Data Entry List</h2>
    <small>What to do here ... Re-stock Approved Purchase ordered to Shelves Store Room</small>
    <hr>

    <form action="index.php?stock=<?php echo $stock; ?>&pcr" method="POST">

        <div class="form_sep">
            <label class="form_sep" class="req">Query Procurement by Company</label>
            <select name="company_name_procure" id="" class="form-control" required style="font-size:15px;">

                <?php
                $stmt = $db->query("SELECT distinct c.* FROM stock_table_procurment p 
inner join stock_company as c on p.supplier_id=c.sn where p.status='yes' and pay_status=1 $search_plus"); ?>
                <option selected="selected" value="">Select ...</option>
                <?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $rxw["sn"]; ?>"><?php echo $rxw["name"]; ?></option>
                <?php  } ?>
            </select>
        </div>

        <div class="form_sep">
            <button class="btn btn-success btn-sm" type="submit" name="query_procurement" id="">Display</button>
            <a href="index.php?stock=<?php echo $stock; ?>&pcr" class="btn btn-default btn-sm">Refresh</a>
        </div>
    </form>

    <?php
    if (isset($_POST['query_procurement'])) {
        $company_name_procure = $_POST['company_name_procure'];
        $add_searc = " and p.supplier_id='$company_name_procure'";
    } else {
        $add_searc = '';
    }


    ///echo $stock_table . '==' .$add_searc . '='. $procur_search;
    $stmt = $db->query("SELECT p.*,s.buying_cost,s.hosp_price,s.cash_price,c.name,
    s.stock_total_unit,s.expire_date,p.mfg_date,s.package_type 
FROM stock_table_procurment as p 
inner join stock_table as s on s.sn=p.stock_sn 
inner join stock_company as c on p.supplier_id=c.sn 
where p.pay_status=1 and p.status='yes' and p.stock_table='$stock_table' $add_searc $procur_search");
    if ($stmt->rowCount() > 0) { ?>

        <hr>
        <form id="myForm_procurement_entry" method="POST" action="index.php?stock=<?php echo $stock; ?>&pcr">

            <table class="table table-striped table-bordered table-hover dataTables-example">
                <thead>
                    <tr>
                        <th data-toggle="true">#</th>
                        <th width="30%">Order/By & Date</th>
                        <th width="25%">Stock</th>
                        <th width="25%">Supply/Qty</th>
                        <th width="10%">Batch No/PO Dept</th>
                        <th width="20%">MFG/EXP Date</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $n = 1;
                    $ttotal = 0;
                    $g_ttotal = 0;
                    while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

                        $bal_qty = $roww['order_qty'] - $roww['supplied_qty'];

                    ?>
                        <tr>
                            <td>

                                <input type="checkbox" value="<?php echo $sn = $roww['sn']; ?>" name="inv[]" class="checkbox i-checks" />
                            </td>
                            <td><?php echo $roww['order_by'] . '<br>' . date("d-m-y", strtotime($roww['order_date']));; ?></td>
                            <td><?php echo $roww['stock_name'] . '<br> <b>' . $roww['stock_total_unit'] . ' Pcs in ' . $roww['package_type'] . '</b><br>(' . $roww['name'] . ')'; ?></td>
                            <td>
                                <strong>Approved Qty: <?php echo $roww['order_qty']; ?></strong><br><strong style="color: red; ">Enter Qty Supply: </strong>
                                <input type="number" class="input-sm form-control" max="<?php echo $bal_qty; ?>" min='1' name="order_qty_<?php echo $sn; ?>" id="order_qty_<?php echo $sn; ?>" value="<?php echo $bal_qty; ?>">

                                <?php /*?><div style="display:flex; flex-direction: row;">
	<label for="order_qty">A/Qty:</label><?php echo $bal_qty;?>
	<label for="price_per_unit">&nbsp;&nbsp;&nbsp;Unit:</label><?php echo $roww['stock_total_unit'];?><br>
	
</div><?php */ ?>
                                <br><strong style="font-size: 14px;">Total Units: <?php echo $bal_qty * $roww['stock_total_unit']; ?></strong>

                            </td>
                            <td>


                                <input type="text" class="input-sm form-control" name="batch_no_<?php echo $sn; ?>" value="">
                                <h3><b style="color:blue;">TARGET DEPT:</b><br> <?= $roww['PO_dept_name']; ?></h3>
                                <?php if ($roww['PO_dept_id'] != '') {
                                    $PO_dept_id = $roww['PO_dept_id'];
                                } else {
                                    $PO_dept_id = $roww['dept'];
                                } ?>

                            </td>
                            <td>

                                <div class="form_sep" id="">
                                    <div class="input-group date">
                                        <span class="input-group-addon" style="color: blue;"><i class="fa fa-calendar"></i></span>
                                        <input type="date" name="mgf_<?php echo $sn; ?>" id="" value="<?php echo $roww['mfg_date']; ?>" class="form-control">
                                    </div>
                                </div>

                                <div class="form_sep" id="">
                                    <div class="input-group date">
                                        <span class="input-group-addon" style="color: red;"><i class="fa fa-calendar"></i></span>
                                        <input type="date" name="exp_<?php echo $sn; ?>" id="" value="<?php echo $roww['expire_date']; ?>" class="form-control">
                                    </div>
                                </div>
                                <input type="hidden" name="stock_sn_<?php echo $sn; ?>" id="stock_sn_<?php echo $sn; ?>" value="<?php echo $roww['stock_sn']; ?>">
                                <input type="hidden" name="procure_sn_<?php echo $sn; ?>" id="procure_sn_<?php echo $sn; ?>" value="<?php echo $roww['sn']; ?>">
                                <input type="hidden" name="supply_qty_<?php echo $sn; ?>" id="supply_qty_<?php echo $sn; ?>" value="<?php echo $roww['supplied_qty']; ?>">
                                <input type="hidden" name="main_ordered_qty_<?php echo $sn; ?>" id="main_ordered_qty_<?php echo $sn; ?>" value="<?php echo $roww['order_qty']; ?>">
                                <input type="hidden" name="order_date_<?php echo $sn; ?>" id="order_date_<?php echo $sn; ?>" value="<?php echo $roww['order_date']; ?>">
                                <input type="hidden" name="order_dept_<?php echo $sn; ?>" id="order_dept_<?php echo $sn; ?>" value="<?php echo $PO_dept_id; ?>">
                                <input type="hidden" name="purchase_price_<?php echo $sn; ?>" id="purchase_price_<?php echo $sn; ?>" value="<?php echo $roww['purchase_price']; ?>">
                            </td>
                        </tr>

                    <?php
                        ///$g_ttotal=$g_ttotal+$ttotal;
                        $n++;
                    } ?>

                </tbody>
            </table>
            <div class="form_sep">
                <button class="btn btn-primary" type="submit" name="approve_procurement" id="approve_procurement" onclick="return confirm('Are you sure you want to Re-Stock Selected Item(s)?')">PROCESS & RE-STOCK SELECTED ITEM</button>
            </div>
            <input type="hidden" name="stock" id="stock" value="<?php echo $stock; ?>">

            <input type="hidden" name="p_list" id="p_list" value="<?php echo $n - 1; ?>">

        </form>
<?php }
}
?>