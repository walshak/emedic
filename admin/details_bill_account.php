      			<div class="alert alert-success">
           <h4><?php echo $report_title; ?></h4>
            </div>     
            <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;" >                 
                <thead>
                <tr>
                    <th></th>
                    <th data-toggle="true">Entered Date</th>
                    <th data-toggle="true">A/No</th>
                    <th data-toggle="true">Service/Item</th>
                    <th data-toggle="true">Hospital #</th>
                    <th data-toggle="true">By</th>
                    <th data-toggle="true">Amount</th>
                    <th data-toggle="true">Claim</th>
                    <th data-toggle="true">Entered By</th>
                    <th data-toggle="true">Entered Date</th>
                    <th data-toggle="true">.</th>
                </tr>
                </thead>
                <tbody>

                    <?php 
                        $n=1;$Pending=0;$Approved=0;$tcredit=0;
						$queue=0;$specimen=0;$result=0;$approve=0;$reject=0;$cancel=0;$tcredit=0;
               while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
			   ?>
                    <tr>
                    <td><?php echo $n;?></td>  
                    <td><?php echo date('d,M y', strtotime($roww['date_entry']));?></td>
                    <td><?php echo $roww['app_no']; ?></td>
                    <td><?php echo $roww['item_services']; ?></td>
                    <td><?php echo $roww['hospital_no']; ?></td>
                    <td><?php echo $roww['prepared_by']; ?></td>
                    <td><?php echo $roww['pay']; ?></td>
                    <td><?php echo $roww['claim_amt']; ?></td>
                    <td><?php echo $roww['created_by']; ?></td>
                    <td><?php if($roww['paystatus']=='1'){echo date('d,M y', strtotime($roww['transact_date']));}else{echo '';} ?></td>
                    <td><?php 
					if($roww['acct_billed_ack']=='1'){
						
						echo 'Approved';
						$Approved=$Approved+$roww['pay'];	
					}else{
						
						$Pending=$Pending+$roww['pay'];
						echo 'Pending';}
					?>

                    </td>
                    </tr>
                    <?php $n++; }?>
                    </tbody>
                    </table>
                    
  <h4>Summary</h4>
  <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;" >                 
					<tbody>
                    <tr>  
                    <td></td>
                    <td><strong>Amount (Approved)<br> </strong></td>
                    <td><strong>Amount (Pending)<br> </strong></td>
                    <td><strong></strong></td>
                    </tr>
  
                      <tr> 
                      <td>#</td> 
                    <td><?php echo number_format($Approved, 2, '.', ','); ?></td>
                    <td><?php echo number_format($Pending, 2, '.', ','); ?></td>
                    <td></td>
                    </tr>
                                      
                </tbody>
                </table>
                
<br><br>

<a href="transc.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh & Search again</a></div>
