<?php

if (($insurance=='NHIS' and $nhis=='') or $payst==1){ 
						
//// PRIVATE SERVICES
$amt_paying=$hosp_price;
$claim_amt=0;
$pay_mode='cash';
$access='3';
	$item_amt=$hosp_price;
if($payst==0){
	$coverageError=3;
}else{
	$coverageError=0;
}
											
}elseif($insurance=='NHIS' and $nhis=='NHIS'){
	/// // check if primary or secondary
		if($insurance_type=='2' and ($ap_type==1 or $ap_type=="")){ 
					$amt_paying=$hosp_price;
					$claim_amt=0;
					$pay_mode='cash';
					$access=$ap_type;
						$coverageError=2;
							$item_amt=$hosp_price;
						
			 }else{
			/// nhis section
					$amt_paying=0;
					$claim_amt=$nhis_price;
					$pay_mode='claim';
					$access=$ap_type;
					$coverageError='0';
					$item_amt=$nhis_price;
			}	
}else{
							//// PRIVATE SERVICES / COPORATE / PHIS

								$percent=$interest/100;
								$int_charge=$hosp_price*$percent;
								$ccop_int_charge=$hosp_price*$percent; 
								$item_amt=$hosp_price	; 
								$setdate=date("Y-m-d H:i:s");
							
								
								if($insurance=='PHIS'){
									$claim_amt=$hosp_price+$ccop_int_charge;
									$amt_paying=0;
									$pay_mode='claim';
									$item_amt=$hosp_price	;
								}elseif($insurance=='coperate' or $insurance=='Coperate' or  $insurance=='Cooperate'){
										$claim_amt=$hosp_price+$ccop_int_charge;
									$amt_paying=0;
									$pay_mode='claim';
									$item_amt=$hosp_price	;
								}else{
									$claim_amt=0;	
									$amt_paying=$ext_price;
									$pay_mode='cash';
									$item_amt=$ext_price	;
								}
								
								$access=3;
								
    					 } 


?>