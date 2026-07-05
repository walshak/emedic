<?php

echo $username;
echo '<br>';
	echo $emplname;
	$stmt_pro= $db->query("SELECT pay,hospital_no,claim_amt,item_services,qty,hosp_price,transact_date,prepared_by,dsp_by FROM patient_ap_services WHERE $part (prepared_by='$username' or dsp_by='$username' or prepared_by='$emplname' or dsp_by='$emplname') and paystatus='1' and transact_date between '$start' and '$end' order by cat_type,sn");
			//	echo $stmt_pro->rowCount();
				
			while($rwx=$stmt_pro->fetch(PDO::FETCH_ASSOC)){		
				$claim_amt=$rwx['claim_amt'];
				$pay=$rwx['pay'];			
						if($pay>0){
							$amount=$pay;
							$mode='Cash';
					}elseif($claim_amt>0){
							$amount=$claim_amt;	
							$mode='Insured';
					}
					
					if($fp=='Percent'){
							$percent=$fp_value/100;
							$earning=$percent * $amount;
					}else{
							$earning=$fp_value;
					}
					
					$earning_serv=$earning_serv+$earning;
						$amount=$earning;
					$Grand_Inc_Earning=$Grand_Inc_Earning+$earning;
					
		if(isset($_GET['gpay']) and $save_mode=='yes'){
					$descriptn=$rwx['item_services'];
			$sub=add_payslip($Ecode,$descriptn,$pay_head,$amount,$month,$year,$date_range,$days,$setdatetime,$duration,$bal,$row_sn,$pay_head_type);

		}
						
				?>
                    
                 <?php if($gen_pay!='1'){?>   
                      <tr>
                 <td>
                          <input type="checkbox"  checked value="<?php echo $rwx['sn'] .'__'. $rwx['item_services'] .'__'. $duration.'__'.$bal.'__'.$amount.'__'.$earning.'__'.$row_sn; ?>" name="inv[]" id="add_m_<?php echo $n ?>" onclick="UpdateCost()"  />
    </td>
                <td><?php echo $mode. '<br>'. $rwx['item_services']; ?></td>
                <td><?php echo $rwx['qty'];?></td>
                <td><?php echo $rwx['hosp_price'];?></td>
                <td><?php echo number_format($amount); ?></td>
                <td align="right"><?php echo number_format($earning); ?></td>
                <td><?php echo date("d M",strtotime($rwx['transact_date'])); ?></td>
                    </tr>               
                    <?php
					$n+=1;	
				 }
			} ?>
