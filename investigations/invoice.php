<?php include("../Connections/Conn.php");?>

<?php
session_start();


if (isset($_GET["g"])) {
	$search=$_GET["g"];	
	
	$part=explode("/", $search);
  		$search=$part[0];
	$view=$part[1];
}

if (isset($_POST["print_inv"])) {
	
	$search=$_POST["emr"];
	
if(!empty($_REQUEST['inv'])) {	
		
  $stmt = $db->prepare("DELETE FROM invsti_invoice WHERE emr = :search");
  $stmt->bindParam(':search', $search, PDO::PARAM_STR);
  $stmt->execute();
   

// ADD TO TABLE
$pro_inv = $_REQUEST['inv'];
	for($i=0;$i<count($pro_inv);$i++){
			
						$inv_sn = $pro_inv[$i];
            $stmt = $db->prepare("SELECT item_services, claim_amt, qty, invoice_no, pay, date_entry, cr FROM patient_ap_services WHERE sn = :inv_sn");
            $stmt->bindParam(':inv_sn', $inv_sn, PDO::PARAM_STR);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					
						if($row['invoice_no']==''){
					$invoice_no= $_POST["emr"] . ''. mt_rand(100,999);
						}else{
						$invoice_no= $row['invoice_no'];
							}
							
							
							if ($row['claim_amt']>0 and $row['pay']==0 and $row['cr']==0 ){
									$paystatus=1;
							}else{
									$paystatus=0;
								}
		/// UPDATE			
    $stmt4 = $db->prepare("UPDATE patient_ap_services SET invoice_no = :invoice_no, invoice_status = :invoice_status, paystatus = :paystatus WHERE sn = :inv_sn AND invoice_status = :invoice_status_zero");
    $stmt4->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
    $stmt4->bindParam(':invoice_status', $invoice_status, PDO::PARAM_INT); // Assuming invoice_status is an integer
    $stmt4->bindParam(':paystatus', $paystatus, PDO::PARAM_INT); // Assuming paystatus is an integer
    $stmt4->bindParam(':inv_sn', $inv_sn, PDO::PARAM_STR);
    $stmt4->bindValue(':invoice_status_zero', 0, PDO::PARAM_INT); // Assuming invoice_status = 0
    
    $stmt4->execute();
    
    $stmt3 = $db->prepare("INSERT INTO invsti_invoice (item_services, claim_amt, qty, invoice_no, pay, entry_date, emr) 
    VALUES (:item_services, :claim_amt, :qty, :invoice_no, :pay, :entry_date, :search)");

    $stmt3->bindParam(':item_services', $row['item_services'], PDO::PARAM_STR);
    $stmt3->bindParam(':claim_amt', $row['claim_amt'], PDO::PARAM_STR);
    $stmt3->bindParam(':qty', $row['qty'], PDO::PARAM_INT); // Assuming qty is an integer
    $stmt3->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
    $stmt3->bindParam(':pay', $row['pay'], PDO::PARAM_STR);
    $stmt3->bindParam(':entry_date', $row['date_entry'], PDO::PARAM_STR); // Assuming date_entry is a string
    $stmt3->bindParam(':search', $search, PDO::PARAM_STR);

    $stmt3->execute();

				
			}
	}
	
	/// load preview
header("location:invoice.php?g=$search/print");
	}else{
	
		header("location:invoice.php?g=$search/error");
}
	}	
	
?>

<!DOCTYPE html>
<html>

	<?php include("../inc/header.php"); ?>


<body>

<div id="wrapper">

   <?php include("../inc/nav_side.php"); ?>


<div id="page-wrapper" class="gray-bg">
   <?php include("../inc/nav_header.php"); ?>
   

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Invoices & Payments Status</h2>
        <ol class="breadcrumb">
                <a href="index.html">Home</a>
            <li>
            </li>
                        <li>
                <a href="mgt.php">Investigation</a>
            </li>
            <li class="active">
                <strong>Invoice</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

        <div class="wrapper wrapper-content  animated fadeInRight">
            <div class="row">
                <div class="col-lg-4">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Select Search Filter</h5>
                        </div>
                        <div class="ibox-content">
 <form action="invoice.php" method="POST" id="subject" name="subject" enctype="multipart/form-data" >
                                
<div class="form_sep">
                            <label for="reg_input_no" class="req">Search by Group ID/Hospital #</label>
<input type="text" class="form-control"  name="search_item" placeholder="Enter Search ..." value="<?php
 if(isset($_POST['search_item'])){echo $_POST['search_item']; }?>">
          </div>                
                
                
                <div class="form_sep">
                                                
                                    <strong>Select Dates Range</strong><br>
                                    <div class="form-group" id="data_5">
                                        <div class="input-daterange input-group" id="datepicker">
                                        <input type="text" class="input-sm form-control" name="start"
                                   value="<?php if(isset($_POST['start'])){echo $_POST['start']; }else{echo date("Y-m-d");} ?>"/>
                                        <span class="input-group-addon">to</span>
                                        <input type="text" class="input-sm form-control" name="end" value="<?php if(isset($_POST['end'])){echo $_POST['end']; }else{echo date("Y-m-d");} ?>" />
                                        </div>
                                    </div>
                                    
                                    </div>
                                    <div class="form_sep">
                                    <button class="btn btn-primary" type="submit" name="apply_date" >Apply</button>
                                    </div>
</form>


                    
                            
                            
                        </div>
                    </div>


                </div>
                <div class="col-lg-8">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Data Display</h5>
                        </div>
                        <div class="ibox-content">

		<?php if($view=="print"){
			 // Assuming $db is your PDO database connection object

      $stmt4 = $db->prepare("SELECT patient, patient_name FROM lab_manage WHERE group_id = :search");
      $stmt4->bindParam(':search', $search, PDO::PARAM_STR);
      $stmt4->execute();
      $row4 = $stmt4->fetch(PDO::FETCH_ASSOC);

        ?>
        
        
       <div class="form_sep">
       
               <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="fa fa-print"></i>&nbsp;Print Invoice</button></a>    
               
      &nbsp;&nbsp;&nbsp | &nbsp;&nbsp;&nbsp
        <button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='invoice.php?g=<?php echo $search . '/'; ?>'" >Cancel</button>
        </div>
        
        
        <div id="content">
        
        <table width="278" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td height="34"></td>
            <td height="34">
            </td>
          </tr>
          <tr>
            <td height="55" colspan="2"><div align="center"><b>
              <h3><?php echo $_SESSION['section']; ?></h3>
            </b></div>
            <div align="center">INVOICE</div>
              <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
              <div align="center">(Hospital Copy)</div></td>
          </tr>
          <tr>
            <td width="106" height="31"><b>Hospital No:</b></td>
            <td width="149"><?php echo $row4['patient']; ?></td>
          </tr>
          <tr>
            <td height="27"><b>Names:</b></td>
            <td height="27"><?php echo $row4['patient_name']; ?></td>
          </tr>
          <tr>
            <td height="136" colspan="2"><table width="270" border="0">
              <tr>
                <td height="23"><b>S/N</b></td>
                <td><b>Test Name</b></td>
                <td><b>Qty</b></td>
                <td><b>Amount</b></td>
              </tr>
              <tr>
              <?php
            $cnt = 0;
            $TotalTrans=0;
          // Assuming $db is your PDO database connection object

$stmt = $db->prepare("SELECT * FROM invsti_invoice WHERE emr = :search ORDER BY sn");
$stmt->bindParam(':search', $search, PDO::PARAM_STR);
$stmt->execute();

                          
                     if($query_rstSelect->rowCount()>0){
                        while($row=$query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
                        
                        <?php $cnt++;
                        if($row['pay']>0){$amt=$row['pay'];}else{$amt=$row['claim_amt'];}
                        $TotalTrans=$TotalTrans+$amt;
                        ?>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $row['item_services']; ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo $amt; ?></td>
                <td><?php 
                ?>
                
                </td>
              </tr>
              <?php
                }
                     }
                ?>
        
              <tr>
                <td height="13">&nbsp;</td>
                <td><b>Total:</b></td>
                <td>&nbsp;</td>
                <td>
                <hr  style="border:1px dotted;"/>
                <?php 
                echo "<strong>".number_format($TotalTrans)."</strong>";
                  ?>
                <hr  style="border:1px dotted;"/></td>
              </tr>
            </table>
            <hr  style="border:1px dotted;"/></td>
          </tr>
          <tr>
            <td height="27"><b>Date &amp; Time:</b></td>
            <td height="27"><?php echo date("d-m-Y H:i:s"); ?></td>
          </tr>
          <tr>
            <td height="27"><b> Generated By:</b></td>
            <td height="27"><?php echo $_SESSION['fullname']; ?></td>
          </tr>
        </table>
        <hr>
        <table width="278" height="317" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td height="55" colspan="2"><div align="center"><b>
              <h3><?php echo $_SESSION['section']; ?></h3>
            </b></div>
              <div align="center">INVOICE</div>
              <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
              <div align="center">(Customer's Copy)</div></td>
          </tr>
          <tr>
            <td width="106" height="31"><b>Hospital No:</b></td>
            <td width="149"><?php echo $row4['patient']; ?></td>
          </tr>
          <tr>
            <td height="27"><b>Names:</b></td>
            <td height="27"><?php echo $row4['patient_name']; ?></td>
          </tr>
          <tr>
            <td height="136" colspan="2"><table width="270" border="0">
              <tr>
                <td height="23"><b>S/N</b></td>
                <td><b>Test Name</b></td>
                <td><b>Qty</b></td>
                <td><b>Amount</b></td>
              </tr>
              <tr>
              <?php
            $cnt = 0;
            $TotalTrans=0;
         // Assuming $db is your PDO database connection object

$stmt = $db->prepare("SELECT * FROM invsti_invoice WHERE emr = :search ORDER BY sn");
$stmt->bindParam(':search', $search, PDO::PARAM_STR);
$stmt->execute();

                          
                     if($query_rstSelect->rowCount()>0){
                        while($row=$query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
                        
                        <?php $cnt++;
                        if($row['pay']>0){$amt=$row['pay'];}else{$amt=$row['claim_amt'];}
                        $TotalTrans=$TotalTrans+$amt;
                        ?>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $row['item_services']; ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo $amt; ?></td>
                <td><?php 
                ?>
                
                </td>
              </tr>
              <?php
                }
                     }
                ?>
                
                </td>
        
              <tr>
                <td height="13">&nbsp;</td>
                <td><b>Total:</b></td>
                <td>&nbsp;</td>
                <td><hr  style="border:1px dotted;"/>
                <?php 
                echo "<strong>".number_format($TotalTrans)."</strong>";
                  ?>
                  <hr  style="border:1px dotted;"/></td>
              </tr>
            </table>
              <hr  style="border:1px dotted;"/>
        <table>
          <tr>
            <td height="27"><b>Date &amp; Time:</b></td>
            <td height="27"><?php echo date("d-m-Y H:i:s"); ?></td>
          </tr>
          <tr>
            <td height="27"><b> Generated By:</b></td>
            <td height="27"><?php echo $_SESSION['fullname']; ?></td>
          </tr>
        </table>
       
       </div>
       
       

        
        
        
        
      <?php }else{ ?>
                                
         <form action="invoice.php" method="POST" id="subject" name="subject" enctype="multipart/form-data" >
        

                        <?php
                        $total_p=0; $total_c=0; $total_cr=0; $total_amt_paid=0; $total_amt_claim_posted=0;
						
						 //     start end  inv_search
             if (isset($_POST["apply_date"])) {
              $stmt = $db->prepare("SELECT l.*, p.pay, p.claim_amt, p.invoice_status, p.paystatus, p.hospital_no, p.cr, p.sn AS inv_code 
                                   FROM lab_manage AS l 
                                   INNER JOIN patient_ap_services AS p ON l.labrequest_no = p.drug_sn 
                                   WHERE l.section = :section 
                                   AND (l.group_id = :search_item OR l.patient = :search_item) 
                                   AND l.request_date BETWEEN :start AND :end");
          
              $stmt->bindParam(':section', $_SESSION['section'], PDO::PARAM_STR);
              $stmt->bindParam(':search_item', $_POST['search_item'], PDO::PARAM_STR);
              $stmt->bindParam(':start', $_POST['start'], PDO::PARAM_STR);
              $stmt->bindParam(':end', $_POST['end'], PDO::PARAM_STR);
              $stmt->execute();
          } else {
              $stmt = $db->prepare("SELECT l.*, p.pay, p.claim_amt, p.invoice_status, p.paystatus, p.hospital_no, p.cr, p.sn AS inv_code 
                                   FROM lab_manage AS l 
                                   INNER JOIN patient_ap_services AS p ON l.labrequest_no = p.drug_sn 
                                   WHERE l.section = :section 
                                   AND l.group_id = :search_item");
          
              $stmt->bindParam(':section', $_SESSION['section'], PDO::PARAM_STR);
              $stmt->bindParam(':search_item', $_POST['search_item'], PDO::PARAM_STR);
              $stmt->execute();
          }
          
						
		if($stmt->rowCount()>0){ ?>
        
        <?php if ($view=="error"){ ?><strong style="color:#F00;">Tick/Check Items You Want To Print</strong><?php } ?>
        
        <?php  if(isset($_POST["start"]) and isset($_POST["end"])){ ?> <strong> Reports Dates Between <?php echo date("d M Y",strtotime($_POST["start"])) . ' - ' . date("d M Y",strtotime($_POST["end"])); ?></strong> <?php } ?>
        <br>
        <?php

$stmt = $db->prepare("SELECT patient, patient_name FROM lab_manage WHERE group_id = :search");
$stmt->bindParam(':search', $search, PDO::PARAM_STR);
$stmt->execute();
if ($stmt->rowCount() > 0) {
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $patient = $row['patient'];
					  
  $stmt_en = $db->prepare("SELECT i.insurance_name, i.interest, i.insurance_type 
  FROM enrollee AS e 
  INNER JOIN insurance_tbl AS i ON e.hmo_no = i.insurance_no 
  WHERE e.hospital_no = :patient AND e.status = :status");

$stmt_en->bindParam(':patient', $patient, PDO::PARAM_STR);
$status = 'active'; // Assuming 'active' is a constant or predefined value
$stmt_en->bindParam(':status', $status, PDO::PARAM_STR);

$stmt_en->execute();

if ($stmt_en->rowCount() > 0) {
  $row_ap = $stmt_en->fetch(PDO::FETCH_ASSOC);
  $insurance_name = $row_ap['insurance_name'];
  $interest = $row_ap['interest'];
  $insurance = $row_ap['insurance_type'];
  // Process retrieved data as needed
} else {
  // Handle case where no rows are found, if needed
}

				?>
	<div class="alert alert-success"> Insurance Status: <?php echo $insurance_name . ' [' . $insurance . ']'; ?> </div>			
		<?php	}else{ ?>
	<div class="alert alert-success"> Insurance Status: Private </div>			
		<?php }			  
		
		?>
        
  <table class="table table-striped table-bordered table-hover" >                 
                                    <thead>
                                    <tr>
                                           <th data-toggle="true">Tick</th>
                                           <th data-toggle="true">Lab #</th>
                                           <th data-toggle="true">Test Name</th>
                                           <th data-toggle="true">Requested By</th>
                                           <th data-toggle="true">Date</th>
                                           <th data-toggle="true">Amount</th>
                                           <th data-toggle="true">.</th>
                                      </tr>                                                
                                    </thead>
                                    <tbody>
        
        <?php
						
                     while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                          $total_p=$total_p+$row['pay'];
						  $total_c=$total_c+$row['claim_amt'];
						  
                    if($row['cr']==1 and $row['paystatus']==0){$total_cr=$total_cr+$row['pay'];}
                if($row['paystatus']==1 and $row['pay']>0 ){$total_amt_paid=$total_amt_paid+$row['pay'];}
		 if($row['paystatus']==1 and $row['claim_amt']>0 ){$total_amt_claim_posted=$total_amt_claim_posted+$row['claim_amt'];}
                                $emr=$row['hospital_no'];
										$search=$row['group_id'];
                          ?>
                      <tr>
                             <td>
                             <input type="checkbox" name="inv[]" value="<?php echo $row['inv_code'];?>" ></td>
                                      <td><?php echo $row['labrequest_no']; ?></td>
                                      <td><?php echo $row['test_name']; ?></td>
                                 <td><?php echo $row['request_by']; ?></td>
                                <td><?php echo date("d M Y", strtotime($row['request_date'])); ?></td>
                                 <td><?php if($row['claim_amt']>0){echo $row['claim_amt'];}else{echo $row['pay'];} ?></td> 
                                   <td><?php if ($row['paystatus']==0){
									   echo 'Pending';}else{echo 'Paid/Posted';} ?></td> 
                                   <td><?php if ($row['invoice_status']==0){echo 'Pending';}else{echo 'Invoiced';} ?></td> 
                        </tr>
                <?php }
                        ?>
                    
                                                  <tr>
                             <td>&nbsp;</td>
                                      <td></td>
                                      <td></td>
                                 <td> </td>
                                <td></td>
                                 <td></td> 
                                   <td></td> 
                                   <td></td> 
                        </tr>    
                                      <tr>
                              <td></td> <td></td>
                                      <td>Amount Paying</td>
                                      <td>Credits</td>
                                 <td>Amount Paid</td>
                                <td>Total (Claim)</td>
                                 <td>Claim Posted</td> 
                                   <td></td> 
                                  
                        </tr>
                        
                                                    <tr>
                              <td></td> <td>Summary</td>
                                      <td><?php echo number_format($total_p, 2, '.', ','); ?></td>
                                      <td><?php echo number_format($total_cr, 2, '.', ','); ?></td>
                                 <td><?php  echo number_format($total_amt_paid, 2, '.', ',');?></td>
                                <td><?php echo number_format($total_c, 2, '.', ','); ?></td>
                                 <td> <?php echo  number_format($total_amt_claim_posted, 2, '.', ',') ?></td> 
                                   <td></td> 
                                  
                        </tr>
                        </tbody>
                        </table>
                        
                                                   <div class="pull-left">
     <button class="btn btn-success btn-sm" type="submit" name="print_inv" ><i class="fa fa-print"></i>&nbsp;Claim / Invoice</button>
                                            </div>
                                            
        <div class="pull-right">
     <a href="mgt.php" class="btn btn-danger btn-sm" ><i class="fa fa-times"></i>&nbsp;Close</a>
                                            </div>
                                            
                        <input type="hidden" name="emr" value="<?php echo $search; ?>" >
           </form>
               
		<?php 
					}else{
						echo '<strong>No Record(s) to Show</strong>';
						
						}
		
	  } ?>                        
                     
                    </div>


                </div>
            </div>

        </div>
           
                    </div>
        </div>

<?php include("../inc/footer_scripts.php"); ?>


    
        <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
   
   <script>
   
   
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


        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            /* Init DataTables */
            var oTable = $('#editable').dataTable();

            /* Apply the jEditable handlers to the table */
            oTable.$('td').editable( '../example_ajax.php', {
                "callback": function( sValue, y ) {
                    var aPos = oTable.fnGetPosition( this );
                    oTable.fnUpdate( sValue, aPos[0], aPos[1] );
                },
                "submitdata": function ( value, settings ) {
                    return {
                        "row_id": this.parentNode.getAttribute('id'),
                        "column": oTable.fnGetPosition( this )[2]
                    };
                },

                "width": "90%",
                "height": "100%"
            } );


        });

        function fnClickAddRow() {
            $('#editable').dataTable().fnAddData( [
                "Custom row",
                "New row",
                "New row",
                "New row",
                "New row" ] );

        }
		
				            $('#data_5 .input-daterange').datepicker({
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true
            });
    </script>
     
</body>

</html>
