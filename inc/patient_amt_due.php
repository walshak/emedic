<?php

$stmtt=$db->query("SELECT * FROM patient_ap_services WHERE hospital_no='$hos_no' and invoice_status!='3' and paystatus='0'");
				if($stmtt->rowCount()>0){
	
	$amt_due=0;$amt_hmo=0;$n=1;$amt_paid=0;$hmo_amt_due=0;
				while($row=$stmtt->fetch(PDO::FETCH_ASSOC)) {
				$duration=0;
			if($row['remarks']=='auto_deduct'){
					date_default_timezone_set('Africa/Lagos');
					$Current_date=date('Y-m-d H:i:s');
					$date1 = new DateTime($Current_date);
					$date2 = new DateTime($row['date_entry']);
					
					$diff = $date2->diff($date1);	
					$hr= $diff->format('%h');
					$day=$diff->format('%a');
									
										if ($day==0){$duration=1;}
									if ($day>0){$duration=$day;}
							if ($hr>12){$duration=$duration+1;}
				
								$claim_amt=$row['claim_amt']*$duration;
								$pay=$row['pay']*$duration;	
										}else{
								$claim_amt=$row['claim_amt'];
								$pay=$row['pay'];
							}

if($row['paystatus']==0 and $row['pay']>0 and $row['cr']==1){
		$amt_due=$amt_due+ $pay;
		$hmo_amt_due=$hmo_amt_due+ $claim_amt;
}

if($row['pay']==0 and $row['claim_amt']>0){					
$post_hmo_amt=$post_hmo_amt+ $claim_amt;
}
									}
}

?>