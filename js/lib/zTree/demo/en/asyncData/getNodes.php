<?php ?>
[<?php
$Vvnbcefqmr3a = "0";
$Viaoxy4ha3je = "";
$Vrblbuurk4ga = "";
$Vbyp1k0ptwyl = "";
if(array_key_exists( 'id',$_REQUEST)) {
	$Vvnbcefqmr3a=$_REQUEST['id'];
}
if(array_key_exists( 'lv',$_REQUEST)) {
	$Vrblbuurk4ga=$_REQUEST['lv'];
}
if(array_key_exists('n',$_REQUEST)) {
	$Viaoxy4ha3je=$_REQUEST['n'];
}
if(array_key_exists('chk',$_REQUEST)) {
	$Vbyp1k0ptwyl=$_REQUEST['chk'];
}
if ($Vvnbcefqmr3a==null || $Vvnbcefqmr3a=="") $Vvnbcefqmr3a = "0";
if ($Vrblbuurk4ga==null || $Vrblbuurk4ga=="") $Vrblbuurk4ga = "0";
if ($Viaoxy4ha3je==null) $Viaoxy4ha3je = "";
else $Viaoxy4ha3je = $Viaoxy4ha3je.".";







for ($V4jxpnh1o213=1; $V4jxpnh1o213<5; $V4jxpnh1o213++) {
	$V002moyrkidp = $Vvnbcefqmr3a.$V4jxpnh1o213;
	$Vqzjmlp1n1ag = $Viaoxy4ha3je."n".$V4jxpnh1o213;
	echo "{ id:'".$V002moyrkidp."',	name:'".$Vqzjmlp1n1ag."',	isParent:".(( $Vrblbuurk4ga < "2" && ($V4jxpnh1o213%2)!=0)?"true":"false").($Vbyp1k0ptwyl==""?"":((($Vrblbuurk4ga < "2" && ($V4jxpnh1o213%2)!=0)?", halfCheck:true":"").($V4jxpnh1o213==3?", checked:true":"")))."}";
	if ($V4jxpnh1o213<4) {
		echo ",";
	}
}
?>]
