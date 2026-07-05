<table class="table table-striped table-bordered table-hover" >

 <thead>
<tr>
    <th data-toggle="true" width="15%">Summary <br> <?php echo $summary; ?></th>
    <th data-toggle="true" width="9%">Amount Insured<br> (Claims)</th>
    <th data-toggle="true" width="9%">Amount Paid<br> (Cash Recieved)</th>
    <th data-toggle="true" width="9%">Credits<br> (Amount Pending)</th>
</tr>
</thead>
<tbody>

  <?php
 $stmt_sub=$db->query("SELECT * FROM transc_rpt WHERE rpt_type='$rpt_type' and date_create='$setdate' and operator='$operator'");
				$stmt_sub->execute();
						$TOTALclaim=0;$TOTALpay=0;
				$Tqueue=0;$Tspecimen=0;$Tresult=0;$Tapprove=0;$Treject=0;$Tcancel=0;$Total_test=0; $TOTALcredit=0;
								
						while ($roww=$stmt_sub->fetch(PDO::FETCH_ASSOC)){
							$TOTALclaim=$TOTALclaim+$roww['claim'];
							$TOTALpay=$TOTALpay+$roww['paid'];
							$TOTALcredit=$TOTALcredit+$roww['credit'];

														 ?>
  
                      <tr> 
      <td width="15%"><strong style="font-size:13px"><?php echo $roww['summary_title']; ?></strong></td>                
    <td><strong style="font-size:13px"><?php   echo number_format($roww['claim'], 2, '.', ','); ?></strong></td>
    <td><strong style="font-size:13px"><?php echo number_format($roww['paid'], 2, '.', ','); ?></strong></td>
    <td><strong style="font-size:13px"><?php echo number_format($roww['credit'], 2, '.', ','); ?></strong></td>

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

</tr>

</tbody>
</table>

<br><br>

<a href="transc.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh & Search again</a></div>
