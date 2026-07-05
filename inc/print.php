<?php $print_status="r" ?>

<div class="form_sep" align="center" style="width:278px;">
               <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="fa fa-print"></i>&nbsp;Print</button></a>    
               
      &nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp
      <?php if(isset($_GET['print']) and $voucher!=""){ ?>
        <button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='vchr.php'" >Cancel</button>      
      <?php }elseif($ext_inv==1){ ?>
              <button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='index.php?sale=<?php echo $hosp_no; ?>'" >Cancel</button>      

      <?php }else{ ?>
        <button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='pacct.php?emr=<?php echo $emr; ?>'" >Cancel</button>
        <?php } ?>
        </div>
        
<div id="content" align="center" style="width:278px;">

        <?php if (isset($_GET['dep'])){
						$sn=$_GET['dep'];
				header("location:../inc/printout2.php?deposit=$emr&name=$patient_name&dep=$sn");
			?>
        	 <?php }elseif (isset($_GET['rfd'])){
						$sn=$_GET['rfd'];
				header("location:../inc/printout2.php?refund=$emr&name=$patient_name&rfd=$sn");
			?>       
              
         <?php }elseif(isset($_GET['print']) and $voucher!=""){
			 
$stmt =$db->query("SELECT i.*,v.batch_type,v.date_expire,v.amount as amt,v.created_by FROM vouchers_inventory as i 
INNER JOIN vouchers as v on i.batch_code=v.batch_code WHERE i.batch_code='$voucher' order by i.sn"); 
		if($stmt->rowCount()>0){
			while($row=$stmt->fetch(PDO::FETCH_ASSOC)){ ?> 
			
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
            <td ><strong>Authorized Amount</strong>: </td>
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
                            
                      
        <?php }else{
			
$update = "DELETE FROM saleprint WHERE hos_no='$emr'";
$db->exec($update);
			
			if($print_afterpay=='1'){
				$SelectedItems = $print_items;
				}else{
					$SelectedItems = $_REQUEST['inv'];
			}
		
    for($i=0;$i<count($SelectedItems);$i++){
			if ($print_status=='i'){
                    $inv_id = $SelectedItems[$i];
                   				 $break=explode("__",$inv_id); 
								 echo $sale_no=$break[0]; 
						}elseif($print_status=='r'){
							$inv_id = $SelectedItems[$i];
							$break=explode("__",$inv_id); 
						 $sale_no=$break[0]; 
						// $sale_no = $SelectedItems[$i];
						}else{
							 $sale_no = $SelectedItems[$i];
						}
							
				$insertSQL = "INSERT INTO saleprint(hos_no,sale_no) VALUES ('$emr','$sale_no')";
				$db->exec($insertSQL);		
		}
							if($ext_inv==1){
								header("location:../inc/printout2.php?ext_sale=$emr&name=$ex_patient_name&i");
									}else{
							header("location:../inc/printout2.php?recepinv=$emr&name=$patient_name&r");
						}
		
   }
  ?>


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