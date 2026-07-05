<?php include("../Connections/Conn.php");?>

<?php
	if (isset($_POST['search'])){
		$emr=$_POST['search'];
	}
$section=$_POST['section'];


			if(isset($_POST['ex_patient']) and $_POST['ex_patient']!=''){
					$ex_patient=$_POST['ex_patient'];
					
									
			$stmt_en=$db->query("SELECT * FROM rdc_patient_tbl WHERE patient_no='$ex_patient'");
							if($stmt_en->rowCount()>0){
								
							//	header("location:index23.php?$ex_patient");
								
										if($section=='Laboratory'){
											///header("location:index23.php?$ex_patient");
											
												header("location:archive_rdc.php?emr=$ex_patient");
								}elseif($section=='Radiology'){
										header("location:scan_report.php?archive=$ex_patient");
								}else{
									header("location:index.php?Nex");
								}	
								
						}else{		
		header("location:index.php?Nex");	
			//$noExist='1';	
		}		
	
	
	}
				
				
			
			
			if(isset($_POST['in_patient']) and $_POST['in_patient']!=''){
						$in_patient=$_POST['in_patient'];
						
								$stmt_en=$db->query("SELECT hospital_no FROM enrollee WHERE hospital_no='$in_patient'"); 
				if($stmt_en->rowCount()>0){
							if($section=='Laboratory'){
									header("location:archive.php?emr=$in_patient");
								}elseif($section=='Radiology'){
										header("location:scan_report.php?archive=$in_patient");
							}else{
									header("location:index.php?Nex");
							}
				
			}else{		
		header("location:index.php?Nex");	
			//$noExist='1';	
		}
}

?>	
