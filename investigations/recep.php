      			<div class="alert alert-success">
           <h4><?php echo $report_title; ?></h4>
            </div>     
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                <thead>
                <tr>
                    <th></th>
                    <th data-toggle="true">Transaction Date</th>
                    <th data-toggle="true">Hospital No</th>
                    <th data-toggle="true">Transaction Type</th>
                    <th data-toggle="true">Description</th>
                    <th data-toggle="true">Debit</th>
                    <th data-toggle="true">Credit</th>
                     <th data-toggle="true">Patient Bal</th>
                    <th data-toggle="true">.</th>
                </tr>
                </thead>
                <tbody>

                    <?php 
                        $n=1;$cash=0;$pos=0;$tcredit=0;
						$queue=0;$specimen=0;$result=0;$approve=0;$reject=0;$cancel=0;$tcredit=0;
               while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
			   ?>
                    <tr>
                    <td><?php echo $n;?></td>  
                    <td><?php echo date('d,M y', strtotime($roww['date_entry2']));?></td>
                    <td><?php echo $roww['hospital_no']; ?></td>
                    <td><?php echo $roww['ref_value']; ?></td>
                    <td><?php echo $roww['item_services']; ?></td>
                    <td><?php echo $roww['dr_amt']; ?></td>
                     <td><?php echo $roww['cr_amt']; ?></td>
                      <td><?php echo $roww['bal']; ?></td>
                    <td><?php 
					if($roww['ref_value']=='Cash' or $roww['ref_value']=='cash'){$cash=$cash+$roww['dr_amt'];}
					if($roww['ref_value']=='POS'){$pos=$pos+$roww['dr_amt'];}
		
					?>

                    </td>
                    </tr>
                    <?php $n++; }?>
                    </tbody>
                    </table>
                    
  <h4>Summary</h4>
  <table class="table table-striped table-bordered table-hover dataTables-example" >                 
					<tbody>
                    <tr>  
                    <td></td>
                    <td><strong>Cash Recieved</strong></td>
                    <td><strong>POS Recieved</strong></td>
                    <td><strong>Total Transaction (Money)</strong></td>
                    </tr>
  
                      <tr> 
                      <td>#</td> 
                    <td><?php echo number_format($cash, 2, '.', ','); ?></td>
                    <td><?php echo number_format($pos, 2, '.', ','); ?></td>
                    <td><?php $total=$cash+$pos; echo number_format($total, 2, '.', ','); ?></td>
                    </tr>
                                      
                </tbody>
                </table>
                
