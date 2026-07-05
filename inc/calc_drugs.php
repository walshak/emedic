<?php
$percent=$interest/100;
$int_charge=$hosp_price*$percent;
$ccop_int_charge=$hosp_price*$percent; 
if($cash_price==0){
	$cash_price	= $hosp_price;
	}


if ($insurance=='NHIS' and $nhis==''){ 
			
			//// PRIVATE SERVICES
		$amt_paying=$cash_price;
		$claim_amt=0;
		$pay_mode='cash';
		$access='3';	
		
}elseif($insurance=='NHIS' and $nhis=='NHIS'){
	
	$stmt=$db->query("SELECT ap_type,appt_no FROM apptm WHERE hospital_no='$hos_no' ORDER BY sn DESC LIMIT 1");
	if($stmt->rowCount()>0){
			$rwx =$stmt->fetch(PDO::FETCH_ASSOC);	
			$patient_nhis_access=$rwx['ap_type'];
			$appt_no=$rwx['appt_no'];
	}else{
		// default set primary care one/1
		$patient_nhis_access=1;
	}
	
		/// // check if primary or secondary
			if($insurance_type=='2' and ($patient_nhis_access==1 or $patient_nhis_access=="")){ 
						$amt_paying=$cash_price;
						$claim_amt=0;
						$pay_mode='cash';
						$access=$patient_nhis_access;
				 }else{
				/// nhis section
				$nhis_int_charge=$nhis_price*$percent;
						$hosp_price=$hosp_price*2;
							$int_charge=$hosp_price*$percent;
								$claim_amt=$nhis_price-$nhis_int_charge;
							$amt_paying=$int_charge;
						$pay_mode='cent';
						$access=$patient_nhis_access;
				}
				
}else{
							//// PRIVATE SERVICES / COPORATE / PHIS
								if($insurance=='PHIS'){
									$claim_amt=$hosp_price+$ccop_int_charge;
									$amt_paying=0;
									$pay_mode='claim';
								}elseif($insurance=='coperate' or $insurance=='Coperate' or  $insurance=='Cooperate'){
										$claim_amt=$hosp_price+$ccop_int_charge;
									$amt_paying=0;
									$pay_mode='claim';
								}else{
									$claim_amt=0;	
									$amt_paying=$cash_price;
									$pay_mode='cash';
								}
								
								$access=3;
								
    					 } 
						 
?>