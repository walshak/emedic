<?php
  
 class Database
 { 
 
 var $Veq53rkksn4a;  		
 var $Vnpbxoqoj0am; 	
 var $Vw33yblrezvp; 		
 var $Vmdlovqqirph; 	
 var $Vj2hix5jympu;
 var $V1jwyoxtrpq0;
 var $Vjpggjp1rjjz;
 var $Vhrbx2jdje5h;
 
 function Database()
 { 
 
 
 
 
 
 
 
  $this->host = "localhost";                  
  $this->password = "";           
  $this->user = "root";                   
  $this->database = "web-school";           
  $this->rows = 0;
 
 
 
 
 
  
 } 
 
 function OpenLink()
 { 
  $this->link = @mysql_connect($this->host,$this->user,$this->password) or die (print "Class Database: Error while connecting to DB (link)");
 } 
 
 function SelectDB()
 { 
 
 @mysql_select_db($this->database,$this->link) or die (print "Class Database: Error while selecting DB");
  
 } 
 
 function CloseDB()
 { 
 mysql_close();
 } 
 
 function Query($V1jwyoxtrpq0)
 { 
 $this->OpenLink();
 $this->SelectDB();
 $this->query = $V1jwyoxtrpq0;
 $this->result = mysql_query($V1jwyoxtrpq0,$this->link) or die (print "Class Database: Error while executing Query"."<br>".$V1jwyoxtrpq0."<br>".mysql_error());
 



 
 $this->CloseDB();
 } 
  
 } 
 
?>