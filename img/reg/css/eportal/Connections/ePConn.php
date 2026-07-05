<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
/*$hostname_cnn = "localhost";
$database_cnn = "futmini";
$username_cnn = "root";
$password_cnn = "";
$cnn = mysql_pconnect($hostname_cnn, $username_cnn, $password_cnn) or trigger_error(mysql_error(),E_USER_ERROR); 

*/
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
//$hostname_conn = "localhost";
//$database_conn = "futmini";

//DB LINK
$conn = mysql_connect('localhost', 'root', '$kan@e-portalgk'); 
if (!$conn)  
{ 
    die('Could not connect database: ' . mysql_error()); 
}
$database_ePConn=mysql_select_db ("eportal");
if (!$database_ePConn)  
{ 
die ('Could not select database: ' . mysql_error());
}

?>

