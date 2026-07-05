<?php


include_once("class.database.php");





class batch
{ 






var $V5v1rmmzxlwq;   

var $Vd04cwbjne25;   
var $V3mr5kpowmf5;   
var $Vekfnjqt3yae;   
var $Ve1lmyvvbxk0;   
var $Vmhxkb3gl5tx;   

var $Vmdlovqqirph; 






function batch()
{

$this->database = new Database();

}







function getid()
{
return $this->id;
}

function getcourse_id()
{
return $this->course_id;
}

function getbatch()
{
return $this->batch;
}

function getstart_date()
{
return $this->start_date;
}

function getend_date()
{
return $this->end_date;
}

function getorg_id()
{
return $this->org_id;
}






function setid($Voatwj4ahvts)
{
$this->id =  $Voatwj4ahvts;
}

function setcourse_id($Voatwj4ahvts)
{
$this->course_id =  $Voatwj4ahvts;
}

function setbatch($Voatwj4ahvts)
{
$this->batch =  $Voatwj4ahvts;
}

function setstart_date($Voatwj4ahvts)
{
$this->start_date =  $Voatwj4ahvts;
}

function setend_date($Voatwj4ahvts)
{
$this->end_date =  $Voatwj4ahvts;
}

function setorg_id($Voatwj4ahvts)
{
$this->org_id =  $Voatwj4ahvts;
}





function select($V5v1rmmzxlwq)
{
$Vmhxkb3gl5tx=$_SESSION['org_id'];
$Vhw31yp4bica =  "SELECT * FROM batch WHERE id = $V5v1rmmzxlwq AND  org_id = $Vmhxkb3gl5tx";
$Vjpggjp1rjjz =  $this->database->query($Vhw31yp4bica);
$Vjpggjp1rjjz = $this->database->result;
$Vjnupbcucmqt = mysql_fetch_object($Vjpggjp1rjjz);


$this->id = $Vjnupbcucmqt->id;

$this->course_id = $Vjnupbcucmqt->course_id;

$this->batch = $Vjnupbcucmqt->batch;

$this->start_date = $Vjnupbcucmqt->start_date;

$this->end_date = $Vjnupbcucmqt->end_date;

$this->org_id = $Vjnupbcucmqt->org_id;

}









function delete($V5v1rmmzxlwq)
{
$Vmhxkb3gl5tx=$_SESSION['org_id'];
$Vhw31yp4bica = "DELETE FROM batch WHERE id = $V5v1rmmzxlwq AND org_id = $Vmhxkb3gl5tx;";
$Vjpggjp1rjjz = $this->database->query($Vhw31yp4bica);

}





function insert()
{
$this->id = ""; 

$Vhw31yp4bica = "INSERT INTO batch ( course_id,batch,start_date,end_date,org_id ) VALUES ( '$this->course_id','$this->batch','$this->start_date','$this->end_date','$this->org_id' )";
$Vjpggjp1rjjz = $this->database->query($Vhw31yp4bica);
$this->id = mysql_insert_id($this->database->link);

}





function update($V5v1rmmzxlwq)
{


$Vmhxkb3gl5tx=$_SESSION['org_id'];
$Vhw31yp4bica = " UPDATE batch SET  course_id = '$this->course_id',batch = '$this->batch',start_date = '$this->start_date',end_date = '$this->end_date',org_id = '$this->org_id' WHERE id = $V5v1rmmzxlwq AND  org_id = $Vmhxkb3gl5tx";

$Vjpggjp1rjjz = $this->database->query($Vhw31yp4bica);



}



function selectall($Vxpccuhkkgbo='',$Vi3sqnmerhus='',$Vcpigwrn12e2)
{
	$Vhw31yp4bica =  "SELECT * FROM batch ";
	if($Vxpccuhkkgbo!='')
		$Vhw31yp4bica .= " WHERE $Vxpccuhkkgbo ";
	
	if($Vcpigwrn12e2!='')
		$Vhw31yp4bica .= " ORDER BY $Vcpigwrn12e2 ";
		
	if($Vi3sqnmerhus!='')
		$Vhw31yp4bica .= " LIMIT $Vi3sqnmerhus ";
	
	$Vhw31yp4bica .=";";
	
	$Vjpggjp1rjjz =  $this->database->query($Vhw31yp4bica);
	$Vjpggjp1rjjz = $this->database->result;
	$this->rows=array();
	while($Vjnupbcucmqt=mysql_fetch_object($Vjpggjp1rjjz))
	{
	$this->rows[] = $Vjnupbcucmqt;
	}
	return $this->rows;
}





function selectorgall()
{
	$Vmhxkb3gl5tx=$_SESSION['org_id'];
	$Vhw31yp4bica =  "SELECT * FROM batch WHERE  org_id='$Vmhxkb3gl5tx' ";
	
	$Vjpggjp1rjjz =  $this->database->query($Vhw31yp4bica);
	$Vjpggjp1rjjz = $this->database->result;
	$this->rows=array();
	while($Vjnupbcucmqt=mysql_fetch_object($Vjpggjp1rjjz))
	{
	$this->rows[] = $Vjnupbcucmqt;
	}
	return $this->rows;
}


function selectbatch($Vw5odans422l)
{
	$Vmhxkb3gl5tx=$_SESSION['org_id'];
	$Vhw31yp4bica =  "SELECT * FROM batch WHERE course_id='$Vw5odans422l' AND org_id='$Vmhxkb3gl5tx' ";
	
	$Vjpggjp1rjjz =  $this->database->query($Vhw31yp4bica);
	$Vjpggjp1rjjz = $this->database->result;
	$this->rows=array();
	while($Vjnupbcucmqt=mysql_fetch_object($Vjpggjp1rjjz))
	{
	$this->rows[] = $Vjnupbcucmqt;
	}
	return $this->rows;
}



 function check_batch($V5v1rmmzxlwq)
	{
		$Vmhxkb3gl5tx=$_SESSION['org_id'];
		$Vhw31yp4bica =  "SELECT * FROM batch WHERE id = '$V5v1rmmzxlwq' AND org_id='$Vmhxkb3gl5tx';";
		
		$Vjpggjp1rjjz =  $this->database->query($Vhw31yp4bica);
		$Vjpggjp1rjjz = $this->database->result;
		if($Vjnupbcucmqt=mysql_fetch_object($Vjpggjp1rjjz))
		{
			return $Vjnupbcucmqt->id;
		}
		else 
		{
			return -1;
		}
	}
	
	
	function delete_batch($Vnkeuqsnaayo)
{
$Vmhxkb3gl5tx=$_SESSION['org_id'];
$Vhw31yp4bica = "DELETE FROM batch WHERE course_id = $Vnkeuqsnaayo AND org_id = $Vmhxkb3gl5tx;";
$Vjpggjp1rjjz = $this->database->query($Vhw31yp4bica);

}

	
	
} 

?>