         
         <h3><?php echo $insurance_type; ?></h3>
         
           <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                                    <thead>
                                    <tr>
                                        <th>EMR/Name</th>
                                        <th>Status</th>
                                       <th>Doctor</th>
                                        <th>Date</th>
                                        <th>.</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php  
	
										$c=1;
								$setdate=date("Y-m-d");
		$stmt_chk=$db->query("SELECT * FROM apptm where MONTH(date_ap)='$last_month' and YEAR(date_ap)='$last_yr' and insurance='$insurance_type'");		
			
										while($row=$stmt_chk->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                <td>
								
								<?php echo $row['hospital_no']; ?>
                            <?php if($row['ap_type']=='1' and $row['status']=='checkin'){ ?>
                                <small style="color:#F00">[Primary/Care]</small>
                                <?php }elseif($row['ap_type']=='2'){ ?>
                                <br><small style="color:#F00">[Secondary/Care]</small>
                                <?php } ?>
								<?php echo '/<br>';?>
	<a href="index.php?ptm=all/<?php echo $row['hospital_no']; ?>"><?php echo $row['patient_name']; ?></a>							
                                </td>
            <td><?php 
			////// NON - ADMITTED STATU SHOW
					$admitted='no';
					
			date_default_timezone_set('Africa/Lagos');
			$Current_date=date('Y-m-d H:i:s');
			$date1 = new DateTime($Current_date);
			$date2 = new DateTime($row['ap_date_time']);
			$diff = $date2->diff($date1);	
			$hr= $diff->format('%h');
			$day=$diff->format('%a');			
					if($Current_date>$date2){$app_status='past';}else{$app_status='notyet';}

			$chkpay=$db->query("SELECT paystatus FROM patient_ap_services WHERE hospital_no='$hos_no' and app_no='$appt_no' and paystatus='1'"); 
if($chkpay->rowCount()>0){
		$pay='yes'; }else{ $pay='no';}
				
		if ($pay=='no' and  $row['ap_date_time']>$Current_date){
		echo 'Appointment <br><strong style="color:#F00">[ Pay Pending ]</strong><br> ' . $day . ' days/'. $hr . 'hrs '. '<br><strong style="color:#00F"> [ Remaining ] </strong>';
			
			}elseif($row['queue_lock']=='0' and $row['status']=='checkin' and $row['ap_date_time']<=$Current_date){
				echo 'Waiting: <br> ' . $day . ' days/'. $hr . 'hrs ago' ; if( $row['app_state']=='em'){echo '<br><strong style="color:#F00">[ Emergency ]</strong>';}

			}elseif($row['queue_lock']=='0' and $row['status']=='checkin' and $row['ap_date_time']>$Current_date){
	echo 'Appointment [ Paid ]: <br> ' . $day . ' days/'. $hr . 'hrs '. '<br><strong style="color:#00F"> [ Remaining ] </strong>';	
			}elseif($row['queue_lock']=='1' and $row['status']=='checkin'){
	echo 'Seen Doctor:<br> ' . $day . ' days/'. $hr . 'hrs ago' ;
			
			}elseif($row['queue_lock']=='1' and $row['status']=='discharge'){
		echo 'Discharged seen:<br> ' . $day . ' days/'. $hr . 'hrs ago' ;
			
			}elseif($row['status']=='cancelled'){
							echo 'Cancelled';
								}
					?>
</td>


                               
                                <td><?php echo $row['referal_doc']; ?></td>
                                <td><?php echo date("d M y",strtotime($row['ap_date_time'])) .'/<br>'. date("h:ia",strtotime($row['ap_date_time'])) ; ?></td>
                                <td>
           <?php if(($row['insurance']=='NHIS' or $row['insurance']=='PHIS') and $row['ap_type']=='1' and ($row['status']=='checkin' or $row['status']=='discharge')){ ?>
<input type="button"  name="Change ppt" value="Auth. Code" data-target="#myModal5" id="<?php echo $hos_no.'/'.$row['appt_no'].'/cptclaim/'.$auth; ?>" class="btn btn-primary btn-sm auth_code" />  

<?php }elseif(($row['insurance']=='NHIS' or $row['insurance']=='PHIS') and $row['ap_type']=='2' and strlen($row['auth_code'])<=6){ ?>
<strong style="color:#F00">No Auth/Code</strong> &nbsp;
<input type="button"  name="Change ppt" value="Auth. Code" data-target="#myModal5" id="<?php echo $hos_no.'/'.$row['appt_no'].'/cptclaim/'.$auth; ?>" class="btn btn-primary btn-sm auth_code" />  
                                                                                                              
<?php }elseif($row['auth_code']!='' and $row['auth_code']!='0000'){?>
<strong style="color:#009">Auth. Code:<br><?php echo $row['auth_code']; ?></strong>
<?php }?>                                                           
                                </td>
                                
                                    </tr>
                                    <?php 	}?>
                                    
                                    
                                    

                                    </tbody>
                                    </table>
