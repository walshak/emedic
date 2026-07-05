<?php ?>
[<?php
$Vvnbcefqmr3a = "-1";
if(array_key_exists( 'id',$_REQUEST)) {
	$Vvnbcefqmr3a=$_REQUEST['id'];
}
$Vn5kucfwqap2 = "10";
if(array_key_exists( 'count',$_REQUEST)) {
	$Vn5kucfwqap2=$_REQUEST['count'];
}
if ($Vvnbcefqmr3a==null || $Vvnbcefqmr3a=="") $Vvnbcefqmr3a = "0";
if ($Vn5kucfwqap2==null || $Vn5kucfwqap2=="") $Vn5kucfwqap2 = "10";

$Vlwrouzupmb3 = (int)$Vn5kucfwqap2;
for ($V4jxpnh1o213=1; $V4jxpnh1o213<=$Vlwrouzupmb3; $V4jxpnh1o213++) {
	$V002moyrkidp = $Vvnbcefqmr3a."_".$V4jxpnh1o213;
	$Vqzjmlp1n1ag = "tree".$V002moyrkidp;
	echo "{ id:'".$V002moyrkidp."',	name:'".$Vqzjmlp1n1ag."'}";
	if ($V4jxpnh1o213<$Vlwrouzupmb3) {
		echo ",";
	}
	
}
?>]