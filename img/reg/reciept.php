
<?php include('inc/header.php');  
include('Connections/Conn.php');

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
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
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_GET['sn']) and $_GET['sn']!=''){
	$sn=sanitize($_GET['sn']);
}else{
	header("location:dashboard.php");
	}

if(isset($_GET['hos_no'])){
	
	$hos_no=sanitize($_GET['hos_no']);
	
			mysql_select_db($database_conn);
		$query_rstSelect = sprintf("SELECT * FROM enrollee WHERE hospital_no=%s", GetSQLValueString($hos_no, "text"));
		$rstSelect_details =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
			if(mysqli_num_rows($rstSelect_details)==0){
					header("location:dashboard.php");
				}

					$row_rstSelect_d = mysqli_fetch_array($rstSelect_details);
					$employer_no=$row_rstSelect_d['employer_no'];
					$hmo_no=$row_rstSelect_d['hmo_no'];		

		$query = sprintf("SELECT * FROM apptm WHERE hospital_no=%s and status='checkin'", 
					GetSQLValueString($hos_no, "text"));
		$rst =  mysqli_query($conn,$query) or die(mysqli_connect_errno());
if(mysqli_num_rows($rst)>0){
		$row_rs = mysqli_fetch_array($rst);	
		$app_no=$row_rs['appt_no'];
		$ap=$row_rs['appt_no'];
		$ap_type=$row_rs['ap_type'];
		
				}else{ // no current visit
					// query the last visit
$query_rstS = sprintf("SELECT * FROM apptm WHERE hospital_no=%s order by appt_no desc", 
GetSQLValueString($hos_no, "text"));$rst =  mysqli_query($conn,$query_rstS) or die(mysqli_connect_errno());					
if(mysqli_num_rows($rst)>0){
		$row_rs = mysqli_fetch_array($rst);	
		$app_no=$row_rs['appt_no'];
		$ap=$row_rs['appt_no'];
		$ap_type=$row_rs['ap_type'];}						
					}	
		

			
			$query_rstSelect = sprintf("SELECT * FROM hmo WHERE hmo_no=%s", GetSQLValueString($hmo_no, "text"));
		$rst_hmo =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
			$row_hmo = mysqli_fetch_array($rst_hmo);
					$hmo_name=$row_hmo['hmo_name'];
					$addr=$row_hmo['addr'];
					
}else{
	header("location:dashboard.php");
	}

function createRandomPassword() {
	$chars = "003232303232023232023456789";
	srand((double)microtime()*1000000);
	$i = 0;
	$pass = '' ;
	while ($i <= 7) {

		$num = rand() % 33;

		$tmp = substr($chars, $num, 1);

		$pass = $pass . $tmp;

		$i++;

	}
	return $pass;
}
?>

<!DOCTYPE html>
<html>
<head>

<script language="javascript">
function Clickheretoprint()
{ 
  var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,"; 
      disp_setting+="scrollbars=yes,width=800, height=400, left=100, top=25"; 
  var content_vlue = document.getElementById("content").innerHTML; 
  
  var docprint=window.open("","",disp_setting); 
   docprint.document.open(); 
   docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');          
   docprint.document.write(content_vlue); 
   docprint.document.close(); 
   docprint.focus(); 
}
</script>

</head>
<!-- mobile navigation -->
    <nav id="mobile_navigation"></nav>

    <section id="breadcrumbs">
            <div class="container">
                    <ul>
                            <li><a href="dashboard.php">WebMEDIC</a></li>
                                <li><a href="<?php echo "pos_main2.php?hos_no=$hos_no"?>">Patient Home</a></li>
                            <li><span>RECIEPT</span></li>						
                    </ul>
            </div>
    </section>
    <section class="container clearfix main_section">
            <div id="main_content_outer" class="clearfix">
                    <div id="main_content">
                            <div class="alert alert-info"><strong>RECIEPT</strong></div>

                            <!-- main content -->
<form action="" method="POST" id="hmo_no" name="">
</form>
<div class="row">
<div class="col-sm-12" id="content">
        <!--<div class="panel-heading">
                <h4 class="panel-title">Simple Validation</h4>
        </div>-->

<div style="font:bold 18px 'Arial'; width:200px; position: absolute;right: 0px; top: 0px; color:#C00"><?php 
echo 'RECIEPT' . '<br>' . $finalcode='RC/'.createRandomPassword();?></div>
<div align="center">
        <div style="font:bold 18px 'Arial';"></div>
        
        <br><br>
        <div style="font:bold 14px 'Arial';"></div>
        <br></br>
</div>
    <div align="left" style="font:bold 14px 'Arial';">
      </div>
<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
<tr>
<td width="50%" align="left"><img src="img/logo.png" width="196" height="111"></td>
<td width="50%" align="right" ><?php  echo 'To:' . '<br>' . $row_rstSelect_d['addr']; ?></td>
</tr>

</table>   
<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
        <tr bgcolor="#FFCC66" style="font-weight:100">
            <td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
            <td style="font:bold 14px 'Arial';"width="30%">NHIS No: </td>
            <td style="font:bold 14px 'Arial';"width="30%">Patient Name: </td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect_d['hospital_no']; ?></td> 
            <td><?php echo $row_rstSelect_d['nhis_no']; ?></td> 
			<td><?php echo $row_rstSelect_d['surname'] . ', ' . $row_rstSelect_d['fname'] . ' '. $row_rstSelect_d['oname']; ?></td>
		</tr>
        <tr bgcolor="#FFCC66">
            <td style="font:bold 14px 'Arial';">Age: </td>
            <td style="font:bold 14px 'Arial';">Sex: </td>
            <td style="font:bold 14px 'Arial';">Date: </td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect_d['age']; ?></td> 
            <td><?php echo $row_rstSelect_d['gender']; ?></td> 
			<td><?php echo date("Y-m-d"); ?></td>
		</tr>

        <tr bgcolor="#FFCC66">
            <td style="font:bold 14px 'Arial';">OPD/In patient: </td>
            <td style="font:bold 14px 'Arial';">Date of Addmission/Discharge: </td>
            <td style="font:bold 14px 'Arial';">Referral Code/Authorization Code: </td>
        </tr>
        <tr>
            <td><?php echo $app_no; 
						$query_rstSelect = sprintf("SELECT date_admit,date_discharge FROM admission WHERE app_no=%s", GetSQLValueString($app_no, "text"));
		$rst_adm =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
				if(mysqli_num_rows($rst_adm)>0){$row_rst_adm = mysqli_fetch_array($rst_adm);$d=$row_rst_adm['date_admit'] . '/' . $row_rst_adm['date_discharge']; }
			else{$d='-';
				if($ap_type=='s_care'){
					$query_rstCode = sprintf("SELECT auth_code FROM apptm WHERE appt_no=%s", GetSQLValueString($app_no, "text"));
						$rst_code =  mysqli_query($conn,$query_rstCode) or die(mysqli_connect_errno());
						$row_rst_code = mysqli_fetch_array($rst_code);$ccode=$row_rst_code['auth_code'];
					}else{$ccode='-';}
				
			}?></td> 
            <td><?php echo $d; ?></td> 
			<td><?php if($ccode==0){echo '-';}else{echo $code;} ?></td>
		</tr>
	</table>
	


    
                         <?php  
                           	//mysql_select_db($database_conn);
							if(isset($_GET['sn'])){
								
		$query_rstSelect = sprintf("SELECT * FROM patient_ap_services WHERE sn=%s order by cat_type",
		 GetSQLValueString($sn, "text"));
		$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());	
							}else{
								
				$query_rstSelect = sprintf("SELECT * FROM patient_ap_services WHERE paystatus=%s and app_no=%s order by cat_type",
		 GetSQLValueString('1', "text"),GetSQLValueString($app_no, "text"));
		$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());	
								}
							if(mysqli_num_rows($rstSelect)>0)
							//if(count($courselist)>0)
                                                                {?>
                                                                
    <br></br>
    <div align="center" style="font:bold 14px 'Arial';"><?php  echo 'DETAIL OF SERVICES'; ?></div>

	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
		<thead>
			<tr bgcolor="#CCCCCC">
				<th width="90">Date Entry </th>
				<th> Service Name </th>
				<th> Price </th>
				<th> Qty </th>
				<th> Duration </th>
				<th> Amount </th>
				<th> </th>                
			</tr>
		</thead>
		<tbody>
			

			 <?php  
				 $total_due=0;
			 	$total_pay=0;
			 while($row=mysql_fetch_array($rstSelect)) {?>
             
				<tr class="record">
				<td style="border-bottom: 1px solid #ddd;"><?php echo $row['transact_date']; ?></td>
				<td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
				<td style="border-bottom: 1px solid #ddd;"><?php echo $row['hosp_price']; ?></td>
               <td style="border-bottom: 1px solid #ddd;"><?php if ($row['cat_type']=='Medication'){echo $row['qty'];}else{echo '-';} ?></td>
				<td style="border-bottom: 1px solid #ddd;"><?php if ($row['cat_type']=='Medication'){echo $row['drug_days'];}else{echo '-';} ?></td>
				<td style="border-bottom: 1px solid #ddd;" bgcolor="#999999">
				<?php
				echo number_format($row['pay'], 2, '.', ',');
				
				?>
				</td>
              	<td style="border-bottom: 1px solid #ddd;">
				<?php
						///$total_pay=$total_pay+$row['pay'];
						$total_due=$total_due+$row['pay'];
						
						if($row['paystatus']==0){
						echo '<a href="pay_cal.php?' .'hos_no=' . $row['hospital_no'] . '&ap=' .  $row['app_no'].  '&sn=' .  $row['sn']. '">' .  'unpaid'. '</a>';
								$total_due=$total_due+$row['pay'];
						}else{
							echo 'paid';
								$total_pay=$total_pay+$row['pay'];
							}
				?>
				</td>
                
				</tr>
				<?php
					}
				?>
                <tr>
					<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="5"><strong style="font-size: 10px; color: #222222;">          
					Due Amount:
					</strong></td>
					<td style="border-bottom: 1px solid #ddd; text-align:left" colspan="3"><strong style="font-size: 15px; color: #222222;">
					<?php echo 'N'.  number_format($total_due, 2, '.', ',');?>
					</strong></td>
				</tr>
 				<tr>
					<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="5"><strong style="font-size: 10px; color: #222222;">
                    Total Amount Paid:
					</strong></td>
					<td style="border-bottom: 1px solid #ddd; text-align:left" colspan="3"><strong style="font-size: 15px; color: #222222;">
					<?php echo 'N'.  number_format($total_pay, 2, '.', ',');?>
					</strong></td>
				</tr>
 				<tr>
					<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="5"><strong style="font-size: 10px; color: #222222;">
                    Balance:
					</strong></td>
					<td style="border-bottom: 1px solid #ddd; text-align:left" colspan="3"><strong style="font-size: 15px; color: #222222;">
					<?php 
					
					$bal=$total_due-$total_pay;
					if($bal<0){echo $bal=0;}else{
					echo 'N'.  number_format($total_due-$total_pay, 2, '.', ',');}?>
					</strong></td>
				</tr>			</tbody>
	</table>
    
     
    <br><br>
    <table style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
			<tr>
				<td align=""width="50%">___________________________ </td>
            </tr> 
            
            <tr>   
            	<td align=""width="50%">SIGN. OF CASHIER </td>
            </tr>
	</table>
    
	<?php } ?>



</div>
</div>
</div>
</div>



    <div class="form_sep" align="right">
        <div class="pull-right" style="margin-right:100px;">
        <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
     
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<button class="btn btn-success" type="submit" name="add_new_enrollee" id="add_new_enrollee" onClick="window.location='<?php echo "pos_main2.php?hos_no=$hos_no"?>'">Cancel</button>
</div>
    </div>

    </section>
    <div id="footer_space"></div>

<?php  include('inc/footer.php'); ?>