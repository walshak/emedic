<?php

if($insurance=='NHIS'){

$stmt=$db->query("SELECT ap_type FROM apptm WHERE hospital_no='$hos_no' and ap_direction='1' ORDER BY sn DESC LIMIT 1");
	if($stmt->rowCount()>0){
			$rwx =$stmt->fetch(PDO::FETCH_ASSOC);	
			$patient_nhis_access=$rwx['ap_type'];
	}else{
		// default set primary care one/1
		$patient_nhis_access=1;
	}
if($service_access<=$patient_nhis_access){
		$claim_amt=$nhis_price;
			$amt_paying=0;
				$pay_mode='claim';
			$item_amt=$nhis_price;
			
				}else{
			///  NHIS Coverage Undecided
				// Payable
						$claim_amt=0;	
						$amt_paying=$hosp_price;
						$pay_mode='cash';
						$item_amt=$hosp_price;

		}

}elseif($insurance=='PHIS'){
		$claim_amt=$hosp_price+$ccop_int_charge;
		$amt_paying=0;
		$pay_mode='claim';
			$item_amt=$hosp_price;
}elseif($insurance=='coperate' or $insurance=='Coperate' or  $insurance=='Cooperate'){
		$claim_amt=$hosp_price+$ccop_int_charge;
		$amt_paying=0;
		$pay_mode='claim';
		$item_amt=$hosp_price;
}else{
		$claim_amt=0;	
		$amt_paying=$hosp_price;
		$pay_mode='cash';
			$item_amt=$hosp_price;
}
?>