 <?php
	
	$supplier_id=$_POST["supplier_id"];
	$stock_table=$_POST["stock_table"];
	$list_stock=$_POST["list_stock"];

	if($stock_table==''){$earch_cateria2="";}else{$earch_cateria2=" and stock_table='$stock_table'";	}
	if($supplier_id=='all' or $supplier_id==''){$earch_cateria="";}else{$earch_cateria=" and supplier_id='$supplier_id'";}
	if($list_stock==''){$list_stock="";}else{$list_stock=" and stock_sn='$list_stock'";}
	
	$request_status=$_POST["request_status"];
	$start2=$_POST["start2"];
	$end2=$_POST["end2"];
	
 	$stmt=$db->query("SELECT p.*,s.name FROM stock_table_procurment p 
 	inner join stock_company s on s.sn=p.supplier_id 
	WHERE status='$request_status' and date(order_date) between '$start2' and '$end2' $earch_cateria $earch_cateria2 $list_stock $search_plus");

	if ($stmt->rowCount()>0){?>

<div id="content">
<table cellpadding="5" cellspacing="5" border="0" align="center">
<tr><td width="50%" align="center"><img alt="image" src="../img/hopital_logo.jpg" height="100" width="100"></td></tr>
<tr><td width="50%" align="center"><strong><?php echo $_SESSION['h_name']; ?></strong></td></tr>
<tr><td width="50%" align="center"><?php echo $_SESSION['h_address']; ?></td></tr>
<tr><td width="50%" align="center"><?php echo $_SESSION['h_phone']; ?></td></tr>
</table>   
		<table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">                                                <thead>
		<tr>
			<th >#</th>
			<th >Name</th>
			<th >Unit</th>
			<th >Price</th>
			<th >Qty</th>
			<th >Qty/Supl</th>
			<th >T/Cost</th>
			<th >ODR/Date</th>
			<th >ODR/By</th>
			<?php if($_POST["request_status"]=='yes' or $_POST["request_status"]=='reverse'){ ?>
			<th >APR/By</th>
			<th >APR/Date</th>
			<!--<th >Reverse</th>-->
			<?php }else{ ?>
			<th >APR/By</th>
			<th >APR/Date</th>
			<!--<th >Reverse</th>-->
			<?php } ?>
			<?php if($supplier_id=='all'){?>
			<th >Supplier</th>
			
			<?php } ?>
			<th >EXP.Date</th>			
			<th ></th>			
		</tr>
		</thead>
		<tbody>

			<?php 
				$n=1;
				$grand_t=0;
				while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) {
					$grand_t=$grand_t+$roww['total_cost'];
				?>
			   <tr>  
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $n; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['stock_name']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['price_per_unit']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['purchase_price']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['order_qty']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['supplied_qty']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($roww['total_cost']); ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo date("d-m-y", strtotime($roww['order_date'])); ?></td>
				   	<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['order_by']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['approve_by']; ?></td>
					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo date("d-m-y", strtotime($roww['approve_date'])); ?></td>
<?php ?>					<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
					<?php 				
						$date1=date_create(date("d-m-Y"));
						$date2=date_create($roww['approve_date']);
						$diff=date_diff($date1,$date2);
						$day=$diff->format("%a");
if($_SESSION['procure']=='1' and $day<=2 and $roww['status']=='yes' and $roww['pay_status']=='2'){?>
	<form method="post" action="index.php?stock=rpt">				
<button type="submit" class="btn btn-danger btn-xs" name="p_reverse" onclick="return confirm('Are you sure you want to REVERSE this ITEM')" value="<?php echo $roww['sn']; ?>">Reverse</button>
			<input type="hidden" value="<?php echo $roww['supplied_qty']; ?>" name="supplied_qqty">
			</form>				
				   		<?php }else{echo'<strong></strong>';} ?>
				   </td>
					  
					<?php if($supplier_id=='all'){?>
							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['name']; ?></td>                                                       
					<?php } ?>	
							<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php 
					if($roww['expiry_date']!=''){
					echo date("d-m-y", strtotime($roww['expiry_date']));
					}else{ echo '-';
					}?></td>                                                       				   
			</tr>
			<?php 
				if($supplier_id!='all'){$supplier_name=$roww['name'];}
			   $n++;	
			}?>
		</tbody>
		</table>
			<?php if($supplier_id!='all'){?>
			<h2>Supplier: <?php echo $supplier_name; ?></h2>
			<?php } ?>
			<h2>Grand Total: <?php echo $grand_t; ?></h2>
			<strong>Report Date: <?php echo date("d-m-y", strtotime($start2)) . ' - ' . date("d-m-y", strtotime($end2)); ?></strong>
	
</div>

          <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report"/>                    
<a href="index.php?stock=rpt" class="btn btn-danger btn-xs">Close</a>


										<?php }else{ ?>
                                           <div class="alert alert-warning">No Records to show </div>    
                                             <a href="index.php?stock=rpt" class="btn btn-danger btn-xs">Close</a>
   
                                                <?php }
	
	
	
	
	
