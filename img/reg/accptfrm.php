<?php require_once('Connections/ePConn.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
	Global $conn;
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }
//$theValue="lk";
  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn, $theValue) : mysqli_escape_string($conn, $theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}


require('fpdf/fpdf.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo
    $this->Image('futlog.jpg',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',16);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(40,10,'FEDERAL UNIVERSITY OF TECHNOLOGY MINNA',0,0,'C');
	
	$this->Ln(8);
	 $this->Cell(80);
	  $this->SetFont('Arial','',12);
	 $this->Cell(30,10,'OFFICE OF THE DEAN, POSTGRADUATE SCHOOL',0,0,'C');
	 
    // Line break
	$this->Ln(10);
	 $this->Cell(80);
	  $this->SetFont('Arial','U',14);
	 $this->Cell(30,10,'ACCEPTANCE FORM',0,0,'C');
	 
    // Line break
    $this->Ln(15);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

if (isset($_GET['TK'])){
$TKurl=$_GET['TK'];
//decode url
include_once('encodecourl.php');
$TK2url=decodeurl("$TKurl","$mykeyurl");
$TKexplode=explode(";", "$TK2url");
$RegNumb=$TKexplode_RegNumb[1]=explode("=", "$TKexplode[4]");
$RegNumb=$RegNumb[1];
$CandName=$TKexplode_CandName[1]=explode("=", "$TKexplode[1]");	
$CandName1=$CandName[1];	
$stdid=$TKexplode_stdid[1]=explode("=", "$TKexplode[2]");
$stdid=$stdid[1];
$dept=$TKexplode_dept[1]=explode("=", "$TKexplode[5]");
$dept=$dept[1];
$sess=$TKexplode_sess[1]=explode("=", "$TKexplode[6]");
$sess=$sess[1];
$matno=$TKexplode_matno[1]=explode("=", "$TKexplode[7]");
$matno=$matno[1];
}
$CandNam=explode(' ',strip_tags($CandName1));


$query_rscheckco = sprintf("SELECT * FROM pgapplication WHERE applicant_id = %s", GetSQLValueString($RegNumb, "text"));
$rscheckco =  mysqli_query($conn,$query_rscheckco) or die(mysqli_connect_errno());
$row_rscheckco = mysqli_fetch_array($rscheckco);
$totalRows_rscheckco = mysqli_num_rows($rscheckco);

$surname=$row_rscheckco['surname'];
$firstname=$row_rscheckco['firstname'];
$p_address=$row_rscheckco['p_address'];

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
//Student's profile

 $pdf->SetY(50);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'Application Form No:');
	 $pdf->SetX(54);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,$RegNumb,0,1);
	
	 $pdf->SetXY(29,58);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'Surname:');
	 $pdf->SetX(54);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,$surname,0,1);	
	
  $pdf->SetXY(27,66);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'First Name:');
	 $pdf->SetX(54);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,$firstname,0,1);		
	
$pdf->SetXY(19,75);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'Contact Address:');
	 $pdf->SetX(54);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,$p_address,0,0);	
	
	//Candidate's Letter
$pdf->SetY(95);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'The Secretary,');		
	
$pdf->SetY(102);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Postgraduate School,');		

$pdf->SetY(109);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Federal University of Technology Minna.');		
	
$pdf->SetY(129);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'OFFER OF ADMISSION TO POSTGRADUATE SCHOOL',0,0,'C');	
	
$pdf->SetY(135);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'2016/2017 ACADEMIC SESSSION',0,0,'C');			
	
$pdf->SetY(150);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'I wish to inform you that accept/reject the offer of admission made to me for_______________________',0,0,'');		
	
$pdf->SetY(160);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'in Degree Programme in the Department of ___________________________________.',0,0,'');	
	
$pdf->SetY(180);
$pdf->SetFont('Times','B',13);
    $pdf->Cell(0,10,'DECLARATION',0,0,'');	
	
	
$pdf->SetY(195);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'I have read the Regulation for Students and I do solemnly declare that i shall abide by them.',0,0,'');
	
$pdf->SetY(211);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Date ___________________________',0,0,'');		
	
$pdf->SetX(97);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Signature _________________________',0,0,'');	
	
	
$pdf->SetY(225);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'*Delete whichever is not applicable',0,0,'');						 
	 
$pdf->Output();
?>
