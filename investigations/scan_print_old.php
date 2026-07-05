<?php include("../Connections/Conn.php");?>

<?php
	
if(isset($_GET['emr'])){
	$emr=$_GET['emr'];
	$id=$_GET['id'];
	
}
 
 	     $stmt=$db->query(sprintf("SELECT * FROM enrollee WHERE hospital_no='$emr'")); 
			if($stmt->rowCount()>0){
					$roww=$stmt->fetch(PDO::FETCH_ASSOC);
					$nat=$roww['nationality'];
					$gender=$roww['gender'];
					$addr=$roww['addr'];
					$insurance=$roww['insurance'];	
					$phone=$roww['phone'];
					$birthDate=$roww['dob'];
					$patient_name =$roww['surname'] .' '. $roww['fname'];
					$gender=$roww['gender'];
					$birthDate=$roww['dob'];			
					}else{



if(isset($_GET['emr'])){
				
				$emr=$_GET['emr'];
		$stmt=$db->query("SELECT d.* FROM rdc_patient_tbl as d WHERE d.patient_no='$emr'");
				$roww=$stmt->fetch(PDO::FETCH_ASSOC);
					$gender=$roww['patient_gender'];
					$addr=$roww['patient_address'];
					$phone=$roww['patient_phone'];
					$birthDate=$roww['patient_dob'];
					$patient_name =$roww['patient_names'];
					
	
}
 
				
					}
					
					
 
 
 

								date_default_timezone_set('Africa/Lagos');
										$Current_date=date('Y-m-d');
											$date1 = new DateTime($Current_date);
											$date2 = new DateTime($birthDate);
											$diff = $date2->diff($date1);	
													$age= $diff->format('%y');
?>


<?php

$stmt_rpt=$db->query("SELECT * FROM rdc_xray WHERE patient_id='$id'");
	 			if($stmt_rpt->rowCount()>0){

			$row_rpt=$stmt_rpt->fetch(PDO::FETCH_ASSOC);
			$radiology_report=$row_rpt['rayReport'];
			$rayConclusion=$row_rpt['rayConclusion'];
			$date_captured=$row_rpt['date_captured'];
			$lab_tech=$row_rpt['lab_tech'];
			
				}
			


?>

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
                    <h2>Radialogy Report</h2>
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
                        <a href="scan_report.php?archive=<?php echo $emr; ?>" class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Close & Return to Previous Page"><i class="fa fa-times"></i> Close</a>
                    </div>
                </div>
            </div>
  
                
<div class="row" id="content">
            <div class="col-lg-12">
                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="ibox-content p-xl" >
                            <div class="row">
                                 <table width="100%">
                                    <tr>
                                    <td>
  <img alt="image" src="../img/logo.png" height="111" width="196">

                                    </td>
                                    <td>
                                    <div align="right" class="pull-right">
                                   <?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
                 
                                    </td>
                                    </tr>
                                 </table>     
                            
                            <div align="center"><h2>Radiology Report</h2></div>
	  
					  
                                <div class="col-sm-12">

                              <table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                    <tr>
                                    <td width="15%">Patient's Name:</td><td width="40%" ><?php echo $patient_name; ?></td>
                                    <td width="15%">Sex/Age:</td><td width="30%"><?php echo $gender. ' / ' . $age;?></td>
                                    </tr>
                                    <tr>
                                    <td>Patient No:</td><td><?php echo $emr;?></td>
                                   	<td>Patient Phone No.:</td><td><?php echo $phone;?></td>
                                    </tr>
                                     <tr>
                                    <td>Address:</td><td colspan="3"><?php echo $addr;?></td>
                                    </tr>
                                      <tr>
                                    <td><span><strong>Requested Date:</strong></span></td>
                                    <td><?php echo date('d-m-Y',strtotime($row_rpt['date_captured']));?></td>
                                    <td><span><strong>Approved By:</strong></span></td>
                                    <td><?php echo $row_rpt['doctor_name'];?></td>
                                    </tr>                                                                       
                                 </table>
                                </div>
                            </div>

                      <div class="table-responsive m-t">
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">
                                    <tbody>
        <tr><td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
		<div align="left"><?php echo $radiology_report; ?></div> </td>
                </tr>
                
                      <tr><td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
		<div align="left">Conclusion</td>
                </tr>
                      <tr><td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
		<div align="left"><?php echo $rayConclusion; ?></div> </td>
                </tr>
                
                </tbody>
                </table>
                <br>
              <table align="right"  style="font-size:12px; font-family:Arial, Helvetica, sans-serif;">
                <tr>
                    <td width="40%"><strong><?php echo '<strong>'. $lab_tech.'</strong>';?></strong> <BR>X-ray Imaging Technician
                    </td>
                    <td width="15%">&nbsp;</td>
    
                    <td width="40%">Dr.&nbsp;Salaam Abdul<br><strong>Radiologist</strong></td>
                    <td width="5%">&nbsp;</td>
                </tr>
                <tr>
                    <td width="">
                    	
                    </td>
                    <td width="">&nbsp;</td>
    
                    <td width="">
         
 							<img src="images/dr sallam.png" alt="" />                               
	  
                    </td>
                    <td width="">&nbsp;</td>
                </tr>                            
</tbody>
                                </table>
                                <br><br>
                            </div><!-- /table-responsive -->
                        </div>
                </div>
            </div>
        </div>            
            
           
                    </div>
        </div>

<?php include("search_modal.php") ?>

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
