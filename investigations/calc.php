<?php


				$percent=$interest/100;
		$int_charge=$nhis_price*$percent;
$ccop_int_charge=$hosp_price*$percent; 

if($insurance=='NHIS' and $nhis_price>0){
		$claim_amt=$nhis_price;
		$amt_paying=0;
		$pay_mode='claim';
			$item_amt=$nhis_price;
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