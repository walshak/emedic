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
	 $this->Cell(30,10,'GUARANTOR\'S FORM',0,0,'C');
	 
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
$level=$TKexplode_level[1]=explode("=", "$TKexplode[4]");
$level=$level[1];
$dept=$TKexplode_dept[1]=explode("=", "$TKexplode[5]");
$dept=$dept[1];
$sess=$TKexplode_sess[1]=explode("=", "$TKexplode[6]");
$sess=$sess[1];
$matno=$TKexplode_matno[1]=explode("=", "$TKexplode[7]");
$matno=$matno[1];
}


$query_rscheckco = sprintf("SELECT * FROM pgapplication WHERE applicant_id = %s", GetSQLValueString($RegNumb, "text"));
$rscheckco =  mysqli_query($conn,$query_rscheckco) or die(mysqli_connect_errno());
$row_rscheckco = mysqli_fetch_array($rscheckco);
$totalRows_rscheckco = mysqli_num_rows($rscheckco);

$display_fullname=$row_rscheckco['display_fullname'];

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
//Student's profile

 $pdf->SetY(50);
$pdf->SetFont('Arial','U',14);
    $pdf->Cell(0,10,'SECTION A - STUDENT\'S PARTICULARS:');
	
	
	 $pdf->SetY(62);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'1.   Name of Student');
	 $pdf->SetX(54);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,$display_fullname,0,1);	
	
  $pdf->SetY(70);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'2.   Programme ___________________________________________________________');
		
		
  $pdf->SetY(78);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'3.   Department ___________________________________________________________');
	
	
	 $pdf->SetY(86);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'4.   School _______________________________________________________________');
	
	
$pdf->SetY(94);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'5.   Signature_______________________');
	
$pdf->SetX(90);
	$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Date_________________________________');
	// $pdf->SetX(23);

	
	//Candidate's Letter

	
$pdf->SetY(110);
$pdf->SetFont('Times','U',13);
    $pdf->Cell(0,10,'SECTION B - GUARANTOR\'S SECTION',0,0,'');	
	
$pdf->SetY(120);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'6.   Name ________________________________________________________________');
	
	
	 $pdf->SetY(128);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'7.   Occupation ____________________________________________________________');
	
	
$pdf->SetY(136);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'8.   Business/Office Address _________________________________________________');
	
	
	 $pdf->SetY(144);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'9.   Postal Address(if different from 8) _________________________________________');	
	
$pdf->SetY(152);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'________________________________________________________________________');	
	
$pdf->SetY(160);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'10.   Relation to Student ____________________________________________________');
		
	
$pdf->SetY(168);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'11.   Parent/Guardian___________________');
	
$pdf->SetX(94);
	$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Employer___________________________');	
	
$pdf->SetY(176);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   Support(Amount) _______________________________________________________');
		
$pdf->SetY(184);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   Others ________________________________________________________________');
					
	
 $pdf->SetY(192);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'12.   I/We _________________________________________');	
	
$pdf->SetY(200);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'  Solemnly declare to meet the above obligations in the academic interest of the above ');	
	
$pdf->SetY(206);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   mentioned student__________________________________________________ of the');	
	
$pdf->SetY(212);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   Department__________________________________________________ of the');		
	

$pdf->SetY(218);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   Federal University of Technology Minna.');	
	
$pdf->SetY(226);	
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'	 Signed __________________',0,0,'');	

$pdf->SetX(67);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'this day of________',0,0,'');		
	
$pdf->SetX(105);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Month ____________',0,0,'');			
$pdf->SetX(147);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'Year _______',0,0,'');			
		
	

$pdf->SetY(234);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   in the presence of _________________________________________');	
	
$pdf->SetY(242);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'	Witness:');			

$pdf->SetY(250);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   Name _________________________________________');	
	
$pdf->SetY(258);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'   Address _________________________________________');		

$pdf->SetY(266);

$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'    Signature _________________________',0,0,'');	

$pdf->SetX(97);
$pdf->SetFont('Times','',13);
    $pdf->Cell(0,10,'	Date ____________________________',0,0,'');		
	

	
					 
	 
$pdf->Output();
?>
