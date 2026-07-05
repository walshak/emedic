      			<div class="alert alert-success">
           <h4><?php echo $report_title; ?></h4>
            </div>     
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                <thead>
                <tr>
                    <th></th>
                    <th data-toggle="true">Requested Date</th>
                    <th data-toggle="true">Lab No</th>
                    <th data-toggle="true">Lab Test</th>
                    <th data-toggle="true">Hospital #</th>
                    <th data-toggle="true">Patient</th>
                    <th data-toggle="true">By</th>
                    <th data-toggle="true">Amount</th>
                    <th data-toggle="true">Claim</th>
                    <th data-toggle="true">Test Status</th>
                    <th data-toggle="true">Paid Date</th>
                    <th data-toggle="true">.</th>
                </tr>
                </thead>
                <tbody>

                    <?php 
                        $n=1;$tclaim=0;$tpay=0;$tcredit=0;
						$queue=0;$specimen=0;$result=0;$approve=0;$reject=0;$cancel=0;$tcredit=0;
               while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
			   ?>
                    <tr>
                    <td><?php echo $n;?></td>  
                    <td><?php echo date('d,M y', strtotime($roww['request_date']));?></td>
                    <td><?php echo $roww['labrequest_no']; ?></td>
                    <td><?php echo $roww['test_name']; ?></td>
                    <td><?php echo $roww['hospital_no']; ?></td>
                    <td><?php echo $roww['patient_name']; ?></td>
                    <td><?php echo $roww['request_by']; ?></td>
                    <td><?php echo $roww['pay']; ?></td>
                    <td><?php echo $roww['claim_amt']; ?></td>
                    <td><?php echo $roww['data_capture_status']; ?></td>
                    <td><?php if($roww['paystatus']=='1'){echo date('d,M y', strtotime($roww['transact_date']));}else{echo '';} ?></td>
                    <td><?php 
					if($roww['paystatus']=='1' and $roww['claim_amt']>0){echo 'Posted';
						$tclaim=$tclaim+$roww['claim_amt'];
						}elseif($roww['paystatus']=='1' and $roww['pay']>0 and $roww['cr']=="0"){echo 'Paid';
							$tpay=$tpay+$roww['pay'];
						}elseif($roww['paystatus']=='0' and $roww['cr']=="1"){
							$tcredit=$tcredit+$roww['pay'];
						}else{echo 'Pending';
						}
							 if($roww['data_capture_status']=='queue'){$queue=$queue+1;}
							 if($roww['data_capture_status']=='specimen'){$specimen=$specimen+1;}
							 if($roww['data_capture_status']=='result'){$result=$result+1;}
							 if($roww['data_capture_status']=='approve'){$approve=$approve+1;}
							 if($roww['data_capture_status']=='reject'){$reject=$reject+1;}
							 if($roww['data_capture_status']=='cancel'){$cancel=$cancel+1;}
					 
                   // if($roww['paystatus']=="0" and $roww['cr']=="1"){
					//	$tcredit=$tcredit+$roww['pay'];
					//	echo ' | Credit';
					//}
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
                    <td><strong>Amount Insured<br> (Claims)</strong></td>
                    <td><strong>Amount Paid<br> (Cash Recieved)</strong></td>
                    <td><strong>Total Credits</strong></td>
                    <td><strong>Lab Requested<br>On-Queue (pending)</strong></td>
                    <td><strong>Specimen <br> Taken (Pending)</strong></td>
                    <td><strong>Results Captured <br>(Approval Pending)</strong></td>
                    <td><strong>Approved<br>Successful</strong></td>
                    <td><strong>Result Rejected</strong></td>
                    <td><strong>Request Cancelled</strong></td>
                    </tr>
  
                      <tr> 
                      <td>#</td> 
                    <td><?php echo number_format($tclaim, 2, '.', ','); ?></td>
                    <td><?php echo number_format($tpay, 2, '.', ','); ?></td>
                    <td><?php echo number_format($tcredit, 2, '.', ','); ?></td>
                    <td><?php echo $queue; ?></td>
                    <td><?php echo $specimen; ?></td>
                    <td><?php echo $result; ?></td>
                    <td><?php echo $approve; ?></td>
                    <td><?php echo $reject; ?></td>
                    <td><?php echo $cancel; ?></td>
                    </tr>
                                      
                </tbody>
                </table>
                
