<?php

if (isset($_SESSION['username'])){
$uname=$_SESSION['username'];
$section_=$_SESSION['section_'];
	
		$stmt2 = $db->query("SELECT * FROM invsti_users WHERE username='$uname'");
				if($stmt2->rowCount()>0){
					$row_invst = $stmt2->fetch(PDO::FETCH_ASSOC);
					
						
							if($row_invst['section'] == 'Radiology'){
								$_SESSION['section']=$row_invst['section'];
								$_SESSION['speciality']=$row_invst['speciality'];
							}elseif($row_invst['section'] == 'Laboratory'){
								$_SESSION['section']=$row_invst['section'];
								$_SESSION['speciality']=$row_invst['speciality'];
							}elseif($section_ == 'RAD'){
								$_SESSION['section']='Radiology';
								$_SESSION['speciality']='Radiologist';							
							}elseif($section_ == 'LB'){
								$_SESSION['section']='Laboratory';
								$_SESSION['speciality']='Laboratory Scientist';
							}else{
								$_SESSION['section']=$row_invst['section'];
								$_SESSION['speciality']=$row_invst['speciality'];
							}
				
					//echo $uname;
					///echo $_SESSION['section'];
					
					
						$_SESSION['create1']=$row_invst['create1'];
						$_SESSION['price']=$row_invst['price'];
						$_SESSION['request']=$row_invst['request'];
						$_SESSION['specimen']=$row_invst['specimen'];
						$_SESSION['enter']=$row_invst['enter'];
						$_SESSION['approve']=$row_invst['approve'];
						$_SESSION['stock']=$row_invst['stock'];
						$_SESSION['inventory']=$row_invst['inventory'];
						$_SESSION['report']=$row_invst['report'];
						$_SESSION['users']=$row_invst['users'];
						$_SESSION['oncredit']=$row_invst['oncredit'];
						$_SESSION['oncredit_adm']=$row_invst['oncredit_adm'];
						$_SESSION['sms']=$row_invst['sms'];
						$_SESSION['equipmnt']=$row_invst['equipmnt'];
						$_SESSION['inv']=$row_invst['inv'];
						$_SESSION['bill']=$row_invst['bill'];
						$_SESSION['edit_appr']=$row_invst['edit_appr'];
	
				$stmt22 = $db->query("SELECT sn FROM admin_users_logs WHERE username='$uname' order by sn desc limit 1");
				if($stmt22->rowCount()>0){
					$rowx = $stmt22->fetch(PDO::FETCH_ASSOC);
							$_SESSION['last_login_id']=$rowx['sn'];
							}
				
			}else{			
				header("location:../index.php?access");
					}	
		}

	?>