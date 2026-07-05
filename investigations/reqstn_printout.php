<?php include("../Connections/Conn.php");?>

<!DOCTYPE html>
<html>
<style>

.td_s {
	padding-right:60px; padding-bottom:10px;
}

</style>
	<?php include("../inc/header.php"); ?>


<body>

<div id="wrapper">

   <?php include("../inc/nav_side.php"); ?>


<div id="page-wrapper" class="gray-bg">
   <?php include("../inc/nav_header.php"); ?>

        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-8">
                    <h2>Requisition Request Report</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li class="active">
                            <strong>Report</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-4">
                    <div class="title-action">
                        
                        <a href="javascript:Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-print"></i> Print Report </a>
                        <a href="inven_rpt.php" class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Close & Return to Previous Page"><i class="fa fa-times"></i> Close</a>
                    </div>
                </div>
            </div>
  
                
<div class="row" id="content">
            <div class="col-lg-12">
                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="ibox-content p-xl" >
                            <div class="row">
                            <div align="center"><h2>REQUISITION REQUEST</h2></div>
                            </div>

 
<?php if (isset($_POST["auto_gen"]) or isset($_POST["show_report"])) { 
                        
                        if(isset($_POST["auto_gen"])) {
                            
                                       $stmt=$db->query("SELECT * FROM lab_stocks WHERE qty<=reorder_level and status='active'");
                                            if($stmt->rowCount()>0){
                                                    $n=0;
                                                while($row=$stmt->fetch(PDO::FETCH_ASSOC)) { 
														$stock_id[$n]=$row['stock_sn'];
														$stock_name[$n]=$row['stock_name'];
														$qty[$n]=$row['qty'];
														$cat[$n]=$row['cat'];
														$buying_cost[$n]=$row['buying_cost'];
                                                        $n++;
                                                }
                                                }else{
                                                    $n=0;
                                            }
                        
                        }elseif(isset($_POST["show_report"])) {  
                                        $values = $_POST['stock_name_list'];
                                        $n=0;
                                    foreach ($values as $a){
                                        $part=explode("/", $a);
										$stock_id[$n]=$part[0];
										$stock_name[$n]=$part[1];
										$qty[$n]=$part[2];
										$cat[$n]=$part[3];
										$buying_cost[$n]=$part[4];
											
                                        $n++;
                                    } 
                                
                        } 
                            
                        
                        if($n>0){
                                                
                         ?>
                         
                  
                                                                <?php 
                                                                
                                                        $error=0;
                                                        for($x = 0; $x < $n; $x++) {
                                                                $stockIDD=$stock_id[$x];
																
                                                       $searchTerm='Add';
													   
	if(isset($_POST["auto_gen"]) or $_POST['choose_date']=='reorder_date') {
						
						///// AUTOMATICE BY REORDER LEVEL
										
  $stmt_rpt=$db->query("SELECT * FROM lab_stocks_inven WHERE stock_sn='$stockIDD' and (inven_desc like '%".$searchTerm."%' or inven_desc='Opening Stock') ORDER BY sn desc LIMIT 1");
  						 if($stmt_rpt->rowCount()==0){
							 //////////// CHECK STOCK TABLE
							  $stmt_rpt2=$db->query("SELECT * FROM lab_stocks WHERE stock_sn='$stockIDD'");
 									        $rowwx=$stmt_rpt2->fetch(PDO::FETCH_ASSOC);
                                       			$start_date=date("Y-m-d", strtotime($rowwx['date_captured']));
												$initial_qty=$rowwx['qty'];
												$no_intiat_post='yes';
												
										 }elseif($stmt_rpt->rowCount()>0){
                                                  $roww=$stmt_rpt->fetch(PDO::FETCH_ASSOC);
                                                $start_date=date("Y-m-d", strtotime($roww['captured_date']));
												$initial_qty=$roww['qtyIN'];
												$no_intiat_post='no';
                                            }
					//////////////// AUTO END
											
    }elseif(isset($_POST["show_report"]) and $_POST['choose_date']=='specify_date' and $_POST['prefer_date']<=date("Y-m-d")){
								 /// USER SPECIFY THE DATES
								 
                                            $start_date = $_POST['prefer_date'];
											
  $stmt_rpt=$db->query("SELECT * FROM lab_stocks_inven WHERE stock_sn='$stockIDD' and inven_desc like '%".$searchTerm."%' AND date(captured_date) BETWEEN '$start_date' AND '$end_date' ORDER BY sn asc LIMIT 1");
  
  						 if($stmt_rpt->rowCount()==0){
							 //////////// CHECK STOCK TABLE
							  $stmt_rpt2=$db->query("SELECT * FROM lab_stocks WHERE stock_sn='$stockIDD'");
 									        $rowwx=$stmt_rpt2->fetch(PDO::FETCH_ASSOC);
                                       			$start_date=date("Y-m-d", strtotime($rowwx['date_captured']));
												$initial_qty=$rowwx['qty'];
												$no_intiat_post='yes';
												
										 }elseif($stmt_rpt->rowCount()>0){
                                                  $roww=$stmt_rpt->fetch(PDO::FETCH_ASSOC);
                                                $start_date=date("Y-m-d", strtotime($roww['captured_date']));
												$initial_qty=$roww['qtyIN'];
												$no_intiat_post='no';
                                            }											
											
	}else{
             $error=1;
    }
									
								
            $end_date=date("Y-m-d");
                                
									
			/// qty at stock level						
  $stmt_qty=$db->query("SELECT bal FROM lab_stocks_inven WHERE stock_sn='$stockIDD' ORDER BY sn desc LIMIT 1");
		 if($stmt_qty->rowCount()>0){$stk_row=$stmt_qty->fetch(PDO::FETCH_ASSOC);$inv_qty = $stk_row['bal'];}else{$inv_qty=0;}
		
					/// qty at stock requisition level						
  $stmt_qty=$db->query("SELECT bal FROM lab_stocks_inven_rq WHERE stock_sn='$stockIDD' ORDER BY sn desc LIMIT 1");
	if($stmt_qty->rowCount()>0){$stk_row=$stmt_qty->fetch(PDO::FETCH_ASSOC);$inv_rq_qty = $stk_row['bal'];}else{$inv_rq_qty=0;}

					/// total test run at requistion table
					
  $stmt_total_test=$db->query("SELECT return_status FROM lab_stocks_inven_rq WHERE stock_sn='$stockIDD' and return_status='1'");
			if($stmt_total_test->rowCount()>0){$invest_Count=$stmt_total_test->rowCount();}else{$invest_Count='Zero';}
					
										if($inv_qty>$qty[$x]){
								//syncronizes the balance
								$stmt_upQTY="UPDATE lab_stocks SET qty='$inv_qty' WHERE stock_sn='$stockIDD'";
										$db->exec($stmt_upQTY);
									}
									
			        /// LOOOP ING 
					
					
                                if($error==0 and $no_intiat_post=='no'){						
																		                        
                            $stmt_final=$db->query("SELECT * FROM lab_stocks_inven WHERE stock_sn='$stockIDD' AND date(captured_date) BETWEEN '$start_date' AND '$end_date'");
                                    if($stmt_final->rowCount()>0){
                            
                            $qtyIN=0; $qtyOUT=0; $qtyConsumed=0; $unknownQty=0;
                                    while($row_F=$stmt_final->fetch(PDO::FETCH_ASSOC)){
                                        
                                            if($row_F['cust_patient_type']=='DEPT_REQ'){
                                           		$qtyConsumed=$qtyConsumed+$row['qtyOUT'];
                                            }
											
											if($row_F['qtyOUT']>0 and $row_F['cust_patient_type']=='OUT'){
												$unknownQty=$unknownQty+$row['qtyOUT'];
											}
												$qtyIN=$qtyIN+$row_F['qtyIN'];
												$qtyOUT=$qtyOUT+$row_F['qtyOUT'];
                                        
                                    }
  ?>						
                            
<strong>Report Dates:&nbsp; <?php echo date("d M Y", strtotime($start_date)) .  '-'. date("d M Y");?> </strong>&nbsp; // <strong>Stock Name:</strong> &nbsp; <?php echo $stock_name[$x]; ?> // &nbsp; <strong>Buying Cost:</strong> &nbsp; <?php echo $buying_cost[$x]; ?> // &nbsp; <strong>Total Investigations Done:</strong> &nbsp; <?php echo $invest_Count; ?> &nbsp; // Requisition Status:&nbsp; <?php $total=$inv_qty+$inv_rq_qty; 
if ($total==0){$s='YES';}else{$s='NO';}?><strong><?php echo $s; ?></strong>
                    
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">

               <tr>
<td style="border-bottom: 1px solid #000; text-align:left" width="14%">Last Qty Purchased: &nbsp; <?php echo $initial_qty;?>
<td style="border-bottom: 1px solid #000; text-align:left" width="10%">T/Qty IN &nbsp; <?php echo $qtyIN; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="10%">T/Qty OUT &nbsp;<?php echo $qtyOUT; ?></td>
</td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Qty Approved to Dept &nbsp; <?php echo $qtyConsumed; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Stock Level &nbsp; <?php echo $inv_qty; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Dept Stock Level &nbsp; <?php echo $inv_rq_qty; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Current Qty &nbsp;<?php echo $inv_qty . ' plus ' . $inv_rq_qty; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="10%">Unknown/Stocks:&nbsp; <?php echo $unknownQty; ?></td>

               </tr>
<tr>
<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%">Enter Requesting Qty</td>
<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%"></td>

<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%">Authorization Signature</td>
<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%"></td>
</tr>
               </table>       

                            
              <?php
                                                }	
												
			}elseif($error==0 and $no_intiat_post=='yes'){ ?>

<strong>Report Dates:&nbsp; <?php echo date("d M Y", strtotime($start_date)) .  '-'. date("d M Y");?> </strong>&nbsp; // <strong>Stock Name:</strong> &nbsp; <?php echo $stock_name[$x]; ?> // &nbsp; <strong>Buying Cost:</strong> &nbsp; <?php echo $buying_cost[$x]; ?> // &nbsp; <strong>Total Investigations Done:</strong> &nbsp; <?php echo $invest_Count; ?> &nbsp; // Requisition Status:&nbsp; <?php $total=$inv_qty+$inv_rq_qty; 
if ($total==0){$s='YES';}else{$s='NO';}?><strong><?php echo $s; ?></strong>
                    
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">

               <tr>
<td style="border-bottom: 1px solid #000; text-align:left" width="14%">Last Qty Purchased: &nbsp; <?php echo $initial_qty;?>
<td style="border-bottom: 1px solid #000; text-align:left" width="10%">T/Qty IN &nbsp; <?php echo $qtyIN; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="10%">T/Qty OUT &nbsp;<?php echo $qtyOUT; ?></td>
</td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Qty Approved to Dept &nbsp; <?php echo $qtyConsumed; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Stock Level &nbsp; <?php echo $inv_qty; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Dept Stock Level &nbsp; <?php echo $inv_rq_qty; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="16%">Current Qty &nbsp;<?php echo $inv_qty . ' plus ' . $inv_rq_qty; ?></td>
<td style="border-bottom: 1px solid #000; text-align:left" width="10%">Unknown/Stocks:&nbsp; <?php echo $unknownQty; ?></td>

               </tr>
<tr>
<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%">Enter Requesting Qty</td>
<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%"></td>

<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%">Authorization Signature</td>
<td colspan="2" style="border-bottom: 1px solid #000; text-align:left" width="25%"></td>
</tr>
               </table>       


            <?php
            }else{
				   		echo "<strong>Invalid Search Settings </strong>";	
			 }
                 
				 
				 
				 
				                                                
             }  ////////////////////// LOOOOPPPPPPPPPPIIIIIIIINNNNNNNNNNNNG
             
			 ?>									
                                            
 <strong>Generated By:&nbsp; </strong> <?php echo $_SESSION['fullname']; ?> <br>
<strong>Date:&nbsp; </strong><?php echo date("d-m-Y")?>                       	
                                             
             
			 <?php }else{echo 'No Inventory Records Found';} ?>
         		
                                                          
             <?php } ?>
                                                   

                        </div>
                </div>
            </div>
        </div>            
            
           
                    </div>
        </div>

<?php include("../inc/footer_scripts.php"); ?>


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
    </script>   
    
        <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
   
   <script>
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
    </script>
     
</body>

</html>
