                    <?php 
                        $n=1;$cash=0;$pos=0;$tcredit=0;
						$queue=0;$specimen=0;$result=0;$approve=0;$reject=0;$cancel=0;$tcredit=0;
		   				//	$total_test_requested = $roww->rowCount();
			     while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
			   			
						if($roww['paystatus']=='1' and $roww['claim_amt']>0 and $roww['cr']=="0"){echo '';
							$tclaim=$tclaim+$roww['claim_amt'];
						}elseif($roww['paystatus']=='1' and $roww['pay']>0 and $roww['cr']=="0"){echo '';
							$tpay=$tpay+$roww['pay'];
						}elseif($roww['paystatus']=='0' and $roww['pay']>0 and $roww['cr']=="1"){echo '';
							$tcredit=$tcredit+$roww['pay'];
						}

							 if($roww['data_capture_status']=='queue'){$queue=$queue+1;}
							 if($roww['data_capture_status']=='specimen'){$specimen=$specimen+1;}
							 if($roww['data_capture_status']=='result'){$result=$result+1;}
							 if($roww['data_capture_status']=='approve'){$approve=$approve+1;}
							 if($roww['data_capture_status']=='reject'){$reject=$reject+1;}
							 if($roww['data_capture_status']=='cancel'){$cancel=$cancel+1;}
			   }
	
										   
               $stmt_add = $db->prepare("INSERT IGNORE INTO invsti_count_rpt (investigation, queue, specimen, results, approve, reject, cancel, total_invest, total_claim, total_pay, t_credits, operator) VALUES (:investigation, :queue, :specimen, :results, :approve, :reject, :cancel, :total_invest, :total_claim, :total_pay, :t_credits, :operator)");

               $stmt_add->bindParam(':investigation', $report_title_part, PDO::PARAM_STR);
               $stmt_add->bindParam(':queue', $queue, PDO::PARAM_STR);
               $stmt_add->bindParam(':specimen', $specimen, PDO::PARAM_STR);
               $stmt_add->bindParam(':results', $result, PDO::PARAM_STR);
               $stmt_add->bindParam(':approve', $approve, PDO::PARAM_STR);
               $stmt_add->bindParam(':reject', $reject, PDO::PARAM_STR);
               $stmt_add->bindParam(':cancel', $cancel, PDO::PARAM_STR);
               $stmt_add->bindParam(':total_invest', $total_test_requested, PDO::PARAM_STR);
               $stmt_add->bindParam(':total_claim', $tclaim, PDO::PARAM_STR);
               $stmt_add->bindParam(':total_pay', $tpay, PDO::PARAM_STR);
               $stmt_add->bindParam(':t_credits', $tcredit, PDO::PARAM_STR);
               $stmt_add->bindParam(':operator', $_SESSION["fullname"], PDO::PARAM_STR);
               
               $stmt_add->execute();
                   
?>

<?php if($query_type=='one'){?>
      			<div class="alert alert-success">
           <h4><?php echo $report_title; ?></h4>
            </div>  
            
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                <thead>
                <tr>
                            <th data-toggle="true">Investigation</th>
                    <th data-toggle="true">T/Queue</th>
                    <th data-toggle="true">T/Specimen</th>
                    <th data-toggle="true">T/Results</th>
                    <th data-toggle="true">T/Approval</th>
                    <th data-toggle="true">T/Rejected</th>
                    <th data-toggle="true">T/Canceled</th>
                    <th data-toggle="true">T/Investigation</th>
                    <th data-toggle="true">T/Amount (Claims)</th>
                    <th data-toggle="true">T/Amount (Paid)</th>
                    <th data-toggle="true">T/Credits</th>
                </tr>
                </thead>
                <tbody>
                
                <?php

$operator=$_SESSION["fullname"];
$stmt_rpt=$db->query("SELECT * FROM invsti_count_rpt WHERE operator='$operator' order by total_invest desc");
		while ($roww=$stmt_rpt->fetch(PDO::FETCH_ASSOC)){ ?>
         <tr>           
        <td><?php echo $roww['investigation'];?></td>
        <td><?php echo $roww['queue'];?></td>
        <td><?php echo $roww['specimen'];?></td>
        <td><?php echo $roww['results'];?></td>
        <td><?php echo $roww['approve'];?></td>
        <td><?php echo $roww['reject'];?></td>
        <td><?php echo $roww['cancel'];?></td>
        <td><?php echo $roww['total_invest'];?></td>
        <td><?php echo number_format($roww['total_claim']);?></td>
        <td><?php echo number_format($roww['total_pay']);?></td>
         <td><?php echo number_format($roww['t_credits']);?></td>
         </tr>
        <?php } ?>

          </tbody>
          </table>      
<?php } ?>