<?php if($viewer=='invest' and $query_type=='all'){?>  

      			<div class="alert alert-success">
           <h4><?php echo "Investigation Reports - Between " .  date("d M Y",strtotime("$start")) . 
			" - " . date("d M Y",strtotime("$end"));	 ?></h4>
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
$stmt_rpt=$db->query("SELECT * FROM invsti_count_rpt WHERE operator='$operator' order by total_invest DESC");
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


<?php }else{ ?>         
               
<table class="table table-striped table-bordered table-hover" >

 <thead>
<tr>
    <th data-toggle="true" width="15%">Summary <br> <?php echo $summary; ?></th>
    <th data-toggle="true" width="9%">Amount Insured<br> (Claims)</th>
    <th data-toggle="true" width="9%">Amount Paid<br> (Cash Recieved)</th>
    <th data-toggle="true" width="9%">Credits<br> (Amount Pending)</th>
    <th data-toggle="true" width="9%">Requests<br>On-Queue (pending)</th>
    <th data-toggle="true" width="9%">Specimen <br> Taken (Pending)</th>
    <th data-toggle="true" width="9%">Results<br>(Approval Pending)</th>
    <th data-toggle="true" width="9%">Approved<br>Successful (Ready)</th>
    <th data-toggle="true" width="9%">Result Rejected</th>
    <th data-toggle="true" width="9%">Request Cancelled</th>
    <th data-toggle="true" width="9%">Total Test</th>
</tr>
</thead>
<tbody>

  <?php
 $stmt_sub=$db->query("SELECT * FROM invsti_transc_rpt WHERE rpt_type='$rpt_type' and date_create='$setdate' and operator='$operator'");
				$stmt_sub->execute();
						$TOTALclaim=0;$TOTALpay=0;
				$Tqueue=0;$Tspecimen=0;$Tresult=0;$Tapprove=0;$Treject=0;$Tcancel=0;$Total_test=0; $TOTALcredit=0;
								
						while ($roww=$stmt_sub->fetch(PDO::FETCH_ASSOC)){
							$TOTALclaim=$TOTALclaim+$roww['claim'];
							$TOTALpay=$TOTALpay+$roww['paid'];
							$TOTALcredit=$TOTALcredit+$roww['credit'];
							$Tqueue=$Tqueue+$roww['Queue'];
							$Tspecimen=$Tspecimen+$roww['Specimen'];
							$Tresult=$Tresult+$roww['Results'];
							$Tapprove=$Tapprove+$roww['Approved'];
							$Treject=$Treject+$roww['Rejected'];
							$Tcancel=$Tcancel+$roww['Cancelled'];
							$Total_test=$Total_test+$roww['total_test'];
														 ?>
  
                      <tr> 
      <td width="15%"><strong style="font-size:13px"><?php echo $roww['summary_title']; ?></strong></td>                
    <td><strong style="font-size:13px"><?php   echo number_format($roww['claim'], 2, '.', ','); ?></strong></td>
    <td><strong style="font-size:13px"><?php echo number_format($roww['paid'], 2, '.', ','); ?></strong></td>
    <td><strong style="font-size:13px"><?php echo number_format($roww['credit'], 2, '.', ','); ?></strong></td>
    <td><strong style="font-size:13px"><?php echo $roww['Queue']; ?></strong></td>
    <td><strong style="font-size:13px"><?php echo $roww['Specimen']; ?></strong></td>
    <td><strong style="font-size:13px"><?php echo $roww['Results']; ?></strong></td>
    <td><strong style="font-size:13px"><?php echo $roww['Approved']; ?></strong></td>
    <td><strong style="font-size:13px"><?php echo $roww['Rejected']; ?></strong></td>
    <td><strong style="font-size:13px"><?php echo $roww['Cancelled']; ?></strong></td>
     <td><strong style="font-size:13px"><?php echo $roww['total_test']; ?></strong></td>
                    </tr>
                         
                         <?php } ?>             

</tbody>
</table>

 <table class="table table-striped table-bordered table-hover " >
                      <tr> 
      <td align="right" width="15%"><strong style="font-size:13px; text-align:right">Total</strong></td>                
    <td width="9%"><strong style="font-size:13px"><?php   echo number_format($TOTALclaim, 2, '.', ','); ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo number_format($TOTALpay, 2, '.', ','); ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo number_format($TOTALcredit, 2, '.', ','); ?></strong></td>    
    <td width="9%"><strong style="font-size:13px"><?php echo $Tqueue; ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo $Tspecimen; ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo $Tresult; ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo $Tapprove; ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo $Treject; ?></strong></td>
    <td width="9%"><strong style="font-size:13px"><?php echo $Tcancel; ?></strong></td>
     <td width="9%"><strong style="font-size:13px"><?php echo $Total_test; ?></strong></td>
</tr>

</tbody>
</table>

<?php } ?>