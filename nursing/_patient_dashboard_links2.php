<table width="85%">
<tr>
<td>
    <a href="patient_bill.php?hosp_no=<?= $hospital_no; ?>&Services" class="btn btn-sm btn-w-m btn-success" 
    style="font-size: 14px; color: white;"><i class="fa fa-repeat"></i>&nbsp;&nbsp;Services</a>

    <a href="patient_bill.php?hosp_no=<?= $hospital_no; ?>&Invoice" class="btn btn-sm btn-w-m btn-primary" 
    style="font-size: 14px; color: white;"><i class="fa fa-repeat"></i>&nbsp;&nbsp;Invoice</a>


    <a href="patient_bill.php?hosp_no=<?= $hospital_no; ?>&Consumable" class="btn btn-sm btn-w-m btn-warning" 
    style="font-size: 14px; color: white;"><i class="fa fa-repeat"></i>&nbsp;&nbsp;Consumable</a>


    <a href="patient.php?hosp_no=<?= $hospital_no; ?>" class="btn btn-sm btn-w-m btn-danger" 
    style="font-size: 14px; color: white;"><i class="fa fa-reply-all"></i>&nbsp;&nbsp;Return</a>

    <a href="patient_bill.php?hosp_no=<?= $hospital_no; ?>" class="btn btn-sm btn-w-m btn-default" 
    style="font-size: 14px; color: white;"><i class="fa fa-reply-all"></i>&nbsp;&nbsp;Refresh</a>
</td>
</tr>
</table>


<div id="msg"></div>
<?php
$complain=null;
$stmt = $db->prepare( "SELECT complain,prepared_by FROM c_d_remarks 
	WHERE hospital_no=? and cat_type='DH' and complain!='' and
	(complain not like '%none%' or complain not like '%NULL%' or complain not like '%nil%' 
	or complain not like '%nil%') ORDER BY sn DESC LIMIT 1" );
    $stmt->execute( array( $hospital_no ) );
if ($stmt->rowCount() > 0) {   
$allergies_row = $stmt->fetch( PDO::FETCH_ASSOC );	
$complain=$allergies_row['complain'];
$count_me = strlen($complain);
if($count_me>0){	
?>	

<span class="blink"><strong style="color: chocolate; ">Allergies:</strong> </span>
<?php echo '<strong>'.$allergies_row['complain'].'</strong>' .'<br><small><i>Entered by</i>: ' . $allergies_row['prepared_by']. '</small>'; ?> 
<?php } }?>