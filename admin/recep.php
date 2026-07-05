      			<div class="alert alert-success">
           <h4><?php echo $report_title .' // ' . $bank_name . ' // ' . $ref_value ; ?></h4>
            </div>     
            <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">                 
                <thead>
                <tr>
                    <th></th>
                    <th>Transaction Date</th>
                    <th>EMR No</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>.</th>
                    <th>Entered By</th>
                </tr>
                </thead>
                <tbody>

                    <?php 
                        $n=1;$cash=0;$pos=0;$transfer=0;$tcredit=0;
						$queue=0;$specimen=0;$result=0;$approve=0;$reject=0;$cancel=0;$tcredit=0;$Refund=0;
               while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
				   		$hospital_no = $roww['hospital_no'];
					   	$stmt_data=$db->query("SELECT surname,oname,fname FROM enrollee where hospital_no='$hospital_no'");	
				   		$row_data=$stmt_data->fetch(PDO::FETCH_ASSOC);
					   	$patient_name = $row_data['fname'] . ' ' . $row_data['oname']. ' ' . $row_data['surname']; 
			   ?>
                    <tr>
						<td><?php echo $n;?></td>  
						<td><?php echo date('d,M y H:i:s a', strtotime($roww['date_entry']));?></td>
						<td><?php echo $roww['hospital_no']; ?></td>
						<td><?php echo $patient_name; ?></td>
						<td><?php echo $roww['item_services']; ?></td>
						<td><?php echo number_format($roww['dr_amt']); ?></td>
                    <td><?php 
				   
if(strtoupper($roww['ref_value'])=='CASH'){
	$cash=$cash+$roww['dr_amt']; 
	echo 'Cash';}
if(strtoupper($roww['ref_value'])=='POS'){
	$pos=$pos+$roww['dr_amt']; 
	echo 'POS'; }
if(strtoupper($roww['ref_value'])=='TRANSFER' or   strtoupper($roww['ref_value'])=='PAY_BY_TRANSFER'){
	$transfer=$transfer+$roww['dr_amt']; 
	echo 'Transfer';}
if(strtoupper($roww['ref_value'])=='REFUND'){
	$Refund=$Refund+$roww['dr_amt'];
}	
?>
                    </td>
                    <td><?php echo $roww['prepared_by']; ?></td>
                    </tr>
                    <?php $n++; }?>
                    </tbody>
				                <tfoot>
                <tr>
					<th></th>
					<th>Transaction Date</th>
					<th>EMR No</th>
					<th>Name</th>
					<th>Description</th>
					<th>Amount</th>
					<th>.</th>
                </tr>
                </tfoot>
                    </table>
  <hr>                  
  <h2>Summary</h2>
  <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;" >                 
                    <thead>
                    <tr>  
                    <th>.</th>
                    <th><strong>Cash Recieved</strong></th>
                    <th><strong>POS Recieved</strong></th>
                    <th><strong>Transfered</strong></th>
                    <th><strong>Cash Refund</strong></th>
                    <th><strong>Total Transaction (Money)</strong></th>
                    <th><strong>Actual(Total)</strong></th>
                    </tr>
                    </thead>
	  <tbody>
  
                      <tr> 
                      <td>#</td> 
                    <td><?php echo number_format($cash, 2, '.', ','); ?></td>
                    <td><?php echo number_format($pos, 2, '.', ','); ?></td>
                    <td><?php echo number_format($transfer, 2, '.', ','); ?></td>
                    <td><?php echo number_format($Refund, 2, '.', ','); ?></td>
                    <td><?php $total=$cash+$pos+$transfer; echo number_format($total, 2, '.', ','); ?></td>
                    <td>
					 <?php $amount_transc=($cash+$pos+$transfer)-$Refund;
					  echo number_format($amount_transc,2,'.',',');
					   ?></td>
                    </tr>
                                      
                </tbody>
	                     <tfoot>
                    <tr>
                    <th>.</th>
                    <th><strong>Cash Recieved</strong></th>
                    <th><strong>POS Recieved</strong></th>
                    <th><strong>Transfered</strong></th>
                    <th><strong>Cash Refund</strong></th>
                    <th><strong>Total Transaction (Money)</strong></th>
                    <th><strong>Actual(Total) </strong></th>
                    </tr>
                    </tfoot>
                </table>

<?php
if($transfer>0 or $pos>0){
	
	$bank_total = 0 ;
	
	      $stmt_master=$db->query("SELECT distinct bank_name FROM chart_ledger 
		  		where $searchTag2 and  (ref_value='Transfer' or ref_value='Pay_by_transfer' or ref_value='POS') and transc_type='DEBIT'  and bank_name!=''");
								if($stmt_master->rowCount()>0){ ?>
		<h2>Bank Transfered/POS Report</h2>	
	  <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;" >                 
             <tr>  
                    <th><strong>#</strong></th>
                    <th><strong>Bank</strong></th>
                    <th><strong>Total Amount</strong></th>
		  </tr>
<?php
						$n=1;				
						while ($rww=$stmt_master->fetch(PDO::FETCH_ASSOC)){ 
								 $bank_name = $rww['bank_name'];
								
						?>
						
							<tr>
								<td><?= $n; ?></td>
								<td><strong><?= $bank_name; ?></strong></td>
								<td>
<h4>									<?php
			$stmt_masterx=$db->query("SELECT sum(dr_amt) as g_total FROM chart_ledger 
		  		where $searchTag2 and bank_name='$bank_name' and transc_type='DEBIT' and (ref_value='Transfer' or ref_value='Pay_by_transfer' or ref_value='POS')");
				$rww_2=$stmt_masterx->fetch(PDO::FETCH_ASSOC);
				echo number_format($rww_2['g_total']);	
					
							$bank_total = $bank_total +  $rww_2['g_total'];
							
									?></h4>
								</td>
								
							</tr>	
<?php $n++;
	} ?>
		  
<tr>
			<td></td>	  
			<td><h2>Total Amount:</h2> </td>	  
			<td><h2><?php echo number_format($bank_total); ?></h2></td>	  
</tr>		  
</table>		  
<?php }
	
}

?>
  
<br><br>
<a href="transc.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh & Search again</a>


