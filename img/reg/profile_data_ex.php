<?php

include('connect_line.php'); 

// clear variable against sql injection

                   	   $Personal_Address=$_POST['Personal_Address'];
                       $phone=$_POST['phone'];
                       $marital=$_POST['marital'];
					   $religion= $_POST['religion'];
                   	   $Next_of_Kin_Name=$_POST['Next_of_Kin_Name'];
					   $next_Kin_relation=$_POST['next_Kin_relation'];
                       $next_addr=$_POST['next_addr'];
					   $next_phone=$_POST['next_phone'];   
					   $blood_group=$_POST['blood_group'];
					   $stdid=$_POST['stdid'];
					   					   
$updt2="UPDATE e_data_profile SET paddr='$Personal_Address', phone='$phone', marital='$marital', religion='$religion', next_name='$Next_of_Kin_Name', next_rel='$next_Kin_relation', next_addr='$next_addr', next_phone='$next_phone', blood_group='$blood_group', session='2014/2015' WHERE stdid='$stdid'";
$result = mysqli_query($dbc, $updt2);

include_once('encodeco.php');
$urlstdid=encode("$stdid","$mykey");
header ("Location: main.php?stdid=$urlstdid&view=adm");


?>
