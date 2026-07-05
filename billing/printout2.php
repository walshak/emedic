<?php include("../Connections/Conn.php");
session_start();
$patient_name=$_GET['name'];
 if (isset($_GET['recepinv'])){
		$emr=$_GET['recepinv'];
 	}elseif(isset($_GET['ext_sale'])){
	 		$emr=$_GET['ext_sale'];
			$patient_name=$_GET['ex_patient_name'];
 	}elseif(isset($_GET['deposit'])){
	 		$emr=$_GET['deposit'];			
 	}elseif(isset($_GET['refund'])){
	 		$emr=$_GET['refund'];			
 		}		
?>
<div class="form_sep" align="center" style="width:278px;">
      
      <?php if(isset($_GET['print']) and $voucher!=""){ ?>
        <button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='index.php'" >Cancel</button> 
             
      <?php }elseif(isset($_GET['ext_sale'])){ ?>
      <a href="index.php?sale=<?php echo $emr; ?>" class="btn btn-default btn-sm" >Close</a>
      <?php }else{ ?>
      <a href="pacct.php?emr=<?php echo $emr; ?>" class="btn btn-default btn-sm" >Close</a>
      <?php } ?>

&nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp

<input type="button" onClick="window.print()"  value="PRINT"/>
<!--               <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="fa fa-print"></i>&nbsp;Print</button></a>  
-->               </div>
               
                 
               
<div id="content" align="center" style="width:278px;">
    <body oncopy="return false" oncut="return false" onpaste="return false"> 

        <?php if (isset($_GET['dep']) or isset($_GET['rfd'])){
				
			if (isset($_GET['dep'])){
				$sn=$_GET['dep'];
                    $stmtP=$db->query("SELECT * FROM chart_ledger WHERE sn='$sn'");
                	  $row=$stmtP->fetch(PDO::FETCH_ASSOC); 
				  		$LABEL='DEPOSIT';
						$by='Recieved';
				}elseif(isset($_GET['rfd'])){
						 $sn=$_GET['rfd'];
                    		$stmtP=$db->query("SELECT * FROM chart_ledger WHERE sn='$sn'");
                  				$row=$stmtP->fetch(PDO::FETCH_ASSOC); 
								$LABEL='REFUND';
								$by='Disbursed';
												
				  }
			?>
        
        <table width="278" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td height="34"></td>
            <td height="34"></td>
          </tr>
          <tr>
            <td height="55" colspan="2"><div align="center"><b>
              <h3> <?php echo $LABEL; ?> RECIEPT</h3>
            </b></div>
              <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
            </td>
          </tr>
          <tr>
            <td width="106" height="31"><b>Hospital No:</b></td>
            <td width="149"><?php echo $emr; ?></td>
          </tr>
          <tr>
            <td height="27"><b>Names:</b></td>
            <td height="27"><?php echo $patient_name; ?></td>
          </tr>
          <tr><td colspan="2"><strong>Description</strong></td></tr>
          <tr><td colspan="2"><?php echo $row['item_services']; ?></td></tr>
          
          <tr><td colspan="2" align="right"><strong>Amount Recived:</strong></td></tr>
          <tr><td colspan="2" align="right">=N= <?php if($LABEL=='REFUND'){echo number_format($row['cr_amt']);}else{echo number_format($row['dr_amt']);} ?></td></tr> 
          
          <tr><td colspan="2" align="right"><strong>Current Balance:</strong></td></tr>
          <tr><td colspan="2" align="right">=N= <?php echo number_format($row['bal']); ?></td></tr>  
 <tr>
   <td colspan="2">---------------------------------------------</td></tr> 
               <tr>
                <td><b><?php echo $by ?> By</b></td>
                <td><?php echo $row['prepared_by']; ?></td>
              </tr>
              <tr>
                <td><b>Date Paid:</b></td>
                <td><?php echo date("d M Y",strtotime($row['date_entry'])); ?></td>
              </tr>
              </table>  
              
              <hr  style="border:1px dotted;"/>
              
              <table width="278" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td height="34"></td>
            <td height="34"></td>
          </tr>
          <tr>
            <td height="55" colspan="2"><div align="center"><b>
              <h3> <?php echo $LABEL; ?> RECIEPT</h3>
             <strong>(Hospital Copy)</strong>
            </b></div>
              <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
            </td>
          </tr>
          <tr>
            <td width="106" height="31"><b>Hospital No:</b></td>
            <td width="149"><?php echo $emr; ?></td>
          </tr>
          <tr>
            <td height="27"><b>Names:</b></td>
            <td height="27"><?php echo $patient_name; ?></td>
          </tr>
          <tr><td colspan="2"><strong>Description</strong></td></tr>
          <tr><td colspan="2"><?php echo $row['item_services']; ?></td></tr>
          
          <tr><td colspan="2" align="right"><strong>Amount Recived:</strong></td></tr>
          <tr><td colspan="2" align="right">=N= <?php echo number_format($row['dr_amt']); ?></td></tr> 
          
          <tr><td colspan="2" align="right"><strong>Current Balance:</strong></td></tr>
          <tr><td colspan="2" align="right">=N= <?php echo number_format($row['bal']); ?></td></tr>  
 <tr>
   <td colspan="2">---------------------------------------------</td></tr> 
               <tr>
                <td><b><?php echo $by ?> By</b></td>
                <td><?php echo $row['prepared_by']; ?></td>
              </tr>
              <tr>
                <td><b>Date Paid:</b></td>
                <td><?php echo date("d M Y",strtotime($row['date_entry'])); ?></td>
              </tr>
              </table>
              
         <?php }elseif(isset($_GET['print']) and $voucher!=""){
			 
       $sql = "SELECT i.*, v.batch_type, v.date_expire, v.amount as amt, v.created_by 
       FROM vouchers_inventory as i 
       INNER JOIN vouchers as v on i.batch_code = v.batch_code 
       WHERE i.batch_code = :batch_code 
       ORDER BY i.sn";
$stmt = $db->prepare($sql);
$stmt->bindValue(':batch_code', $voucher, PDO::PARAM_STR); // Assuming $voucher is a string
$stmt->execute();
if ($stmt->rowCount() > 0) {
   while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?> 
			
         <table width="278" height="160" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td ></td>
            <td ></td>
          </tr>
          <tr>
            <td height="55" colspan="2"><div align="center"><b>
              <h3><?php echo $row['batch_type'] . ' Voucher'?></h3>
            </b></div>
              <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
            </td>
          </tr>
          <tr>
            <td ><strong>Voucher Code</strong>: </td>
            <td ><strong style="font-size:18px"><?php echo $row['voucher_code'];?></strong></td>
          </tr>
          <tr>
            <td ><strong>Authorized Amount/Percentage</strong>: </td>
            <td ><strong style="font-size:12px"><?php echo number_format($row['amt']);?></strong></td>
          </tr>
                   <tr>
            <td ><strong>Expired</strong> : <?php echo date("d M,y", strtotime($row['date_expire']));?></td>
            <td ><strong>Generated</strong>: <?php echo $row['created_by'];?></td>
          </tr> 
          </table>         
		----------------------------------------
		<?php }
		
		}
		?>
                            
                      
        <?php }elseif(isset($_GET['recepinv'])){
					$emr=$_GET['recepinv'];
						
			?>
<table width="278" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
    <tr>
    <td height="55" colspan="2"><div align="center"><b>
      <h3>
<?php if(isset($_GET['r'])){echo "PAYMENT RECEIPT";} else{echo "INVOICE";} ?>	  
      </h3>
    </b></div>
      <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
      <div align="center">(Hospital Copy)</div></td>
  </tr>
  <tr>
    <td width="106" height="31"><b>Hospital No:</b></td>
    <td width="149"><?php echo $emr; ?></td>
  </tr>
  <tr>
    <td height="27"><b>Names:</b></td>
    <td height="27"><?php echo $patient_name; ?></td>
  </tr>
  <tr>
    <td height="136" colspan="2"><table width="270" border="0">
      <tr>
        <td height="23"><b>S/N</b></td>
        <td><b>Description</b></td>
        <td><b>Qty</b></td>
        <td><b>Amount</b></td>
      </tr>
     
                    <?php
			$cnt=0;
			$TotalTrans=0;
	 $stmtt=$db->query("SELECT * FROM saleprint WHERE hos_no='$emr' order by sn");
		while($roww=$stmtt->fetch(PDO::FETCH_ASSOC)) {
				$sale_no = $roww['sale_no'];
								
        $stmtP=$db->query("SELECT item_services,claim_amt,qty,invoice_no,pay,date_entry,cr,invoice_status FROM patient_ap_services WHERE sn='$sale_no'");
              
                  $row=$stmtP->fetch(PDO::FETCH_ASSOC); 
						
						 $claim_amt=$row['claim_amt']; 
							$invoice_no=$row['invoice_no'];
							$invoice_status=$row['invoice_status'];
						$pay=$row['pay'];
						$cr=$row['cr'];
					
					
						?>
                        
                        <?php $cnt++;
                        if($row['pay']>0){$amt=$row['pay'];}else{$amt=$row['claim_amt'];}
                        $TotalTrans=$TotalTrans+$amt;
                        ?>
               <tr>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $row['item_services']; ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo number_format($amt); ?></td>
              </tr>
              <?php
              //  }
                     }
                ?>
      <tr>
        <td height="13">&nbsp;</td>
        <td><b>Total:</b></td>
        <td>&nbsp;</td>
        <td>
		<hr  style="border:1px dotted;"/>
		<?php 
		echo "<strong>".number_format($TotalTrans)."</strong>";
		  ?>
		<hr  style="border:1px dotted;"/></td>
      </tr>
    </table>
    <hr  style="border:1px dotted;"/></td>
  </tr>
  <tr>
    <td height="27"><b>Date &amp; Time:</b></td>
    <td height="27"><?php echo date("d-m-Y H:i:s a"); ?></td>
  </tr>
  <tr>
    <td height="27"><b> Generated By:</b></td>
    <td height="27"><?php echo $_SESSION['fullname']; ?></td>
  </tr>
</table>
	<br>

<table width="278" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
    <tr>
    <td height="55" colspan="2"><div align="center"><b>
      <h3>
<?php if(isset($_GET['r'])){echo "PAYMENT RECEIPT";} else{echo "INVOICE";} ?>	  
      </h3>
    </b></div>
      <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
      <div align="center">(Customer Copy)</div></td>
  </tr>
  <tr>
    <td width="106" height="31"><b>Hospital No:</b></td>
    <td width="149"><?php echo $emr; ?></td>
  </tr>
  <tr>
    <td height="27"><b>Names:</b></td>
    <td height="27"><?php echo $patient_name; ?></td>
  </tr>
  <tr>
    <td height="136" colspan="2"><table width="270" border="0">
      <tr>
        <td height="23"><b>S/N</b></td>
        <td><b>Description</b></td>
        <td><b>Qty</b></td>
        <td><b>Amount</b></td>
      </tr>
     
                    <?php
			$cnt=0;
			$TotalTrans=0;
	 $stmtt=$db->query("SELECT * FROM saleprint WHERE hos_no='$emr' order by sn");
		while($roww=$stmtt->fetch(PDO::FETCH_ASSOC)) {
				$sale_no = $roww['sale_no'];
								
                    $stmtP=$db->query("SELECT item_services,claim_amt,qty,invoice_no,pay,date_entry,cr,invoice_status FROM patient_ap_services WHERE sn='$sale_no'");
              
                  $row=$stmtP->fetch(PDO::FETCH_ASSOC); 
						
						$claim_amt=$row['claim_amt']; 
							$invoice_no=$row['invoice_no'];
							$invoice_status=$row['invoice_status'];
						$pay=$row['pay'];
						$cr=$row['cr'];
						?>
                        
                        <?php $cnt++;
                        if($row['pay']>0){$amt=$row['pay'];}else{$amt=$row['claim_amt'];}
                        $TotalTrans=$TotalTrans+$amt;
                        ?>
               <tr>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $row['item_services']; ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo number_format($amt); ?></td>
              </tr>
              <?php
              //  }
                     }
                ?>
      <tr>
        <td height="13">&nbsp;</td>
        <td><b>Total:</b></td>
        <td>&nbsp;</td>
        <td>
		<hr  style="border:1px dotted;"/>
		<?php 
		echo "<strong>".number_format($TotalTrans)."</strong>";
		  ?>
		<hr  style="border:1px dotted;"/></td>
      </tr>
    </table>
    <hr  style="border:1px dotted;"/></td>
  </tr>
  <tr>
    <td height="27"><b>Date &amp; Time:</b></td>
    <td height="27"><?php echo date("d-m-Y H:i:s a"); ?></td>
  </tr>
  <tr>
    <td height="27"><b> Generated By:</b></td>
    <td height="27"><?php echo $_SESSION['fullname']; ?></td>
  </tr>
</table>


        
<?php } ?>

</body>
       </div>
       
       <script>
	   
	   
   function Clickheretoprint()
{ 
  var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,"; 
      disp_setting+="scrollbars=yes,width=800, height=400, left=100, top=25"; 
  var content_vlue = document.getElementById("content").innerHTML; 
  
  var docprint=window.open("","",disp_setting); 
   docprint.document.open(); 
   docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');          
   docprint.document.write(content_vlue); 
   docprint.document.close(); 
   docprint.focus(); 
}


	   </script>