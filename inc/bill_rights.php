<?php

if (isset($_SESSION['username'])){

			$uname=$_SESSION['username'];
			
				$stmt2 = $db->query("SELECT * FROM biller_users WHERE username='$uname'");
				if($stmt2->rowCount()>0){
					$row_invst = $stmt2->fetch(PDO::FETCH_ASSOC);
						$_SESSION['invoice']=$row_invst['invoice'];
						$_SESSION['reciept']=$row_invst['reciept'];
						$_SESSION['deposit']=$row_invst['deposit'];
						$_SESSION['lab']=$row_invst['lab'];
						$_SESSION['pharm']=$row_invst['pharm'];
						$_SESSION['nursing']=$row_invst['nursing'];
						$_SESSION['other_bill']=$row_invst['other_bill'];
						$_SESSION['reprint']=$row_invst['reprint'];
						$_SESSION['transfer']=$row_invst['transfer'];
						$_SESSION['refund']=$row_invst['refund'];
						$_SESSION['discount']=$row_invst['discount'];
						$_SESSION['claims']=$row_invst['claims'];
						$_SESSION['vouchers']=$row_invst['vouchers'];
						$_SESSION['reversal']=$row_invst['reversal'];
						$_SESSION['writeoff']=$row_invst['writeoff'];
						$_SESSION['edit_price_at_point']=$row_invst['edit_price_at_point'];
				}



} else{
	$time=time();
	error_reporting(0);
	ob_start();ob_clean();
	
	if(!$_SESSION['username']=='')
	{ 
		if(($time-$_SESSION['last_action'])>60000) 
		{
			unset($_SESSION['username']);
			unset($_SESSION['rights']);
			unset($_SESSION['primary_rights']);
			unset($_SESSION['fullname']);
			unset($_SESSION['last_action']);
			unset($_SESSION['specialist']);
			unset($_SESSION['unit_head']);
			session_destroy();
			header('location:../index.php');
			
			}
		else
		{
			//die('aaaa'.$_SESSION['last_action']);	
			$_SESSION['last_action']=time();
			return true;
		}
	}
	else
	{
		header("location: ../index.php");
		
	}	
}


















?>