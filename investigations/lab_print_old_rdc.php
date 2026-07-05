<?php include("../Connections/Conn.php");?>

<?php

if(isset($_GET['emr'])){
				
				$emr=$_GET['emr'];
		$stmt=$db->query("SELECT d.* FROM rdc_patient_tbl as d WHERE d.patient_no='$emr'");
				$getNames=$stmt->fetch(PDO::FETCH_ASSOC);
	
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
                    <h2>Laboratory Report</h2>
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
                        <a href="archive_rdc.php?emr=<?php echo $emr; ?>" class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Close & Return to Previous Page"><i class="fa fa-times"></i> Close</a>
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
                            
                            <div align="center"><h2>Laboratory Report</h2></div>
	  
					  
                                <div class="col-sm-12">
                                
                                 <table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                    <tr>
                              <td width="15%">Patient's Name:</td><td width="40%" ><?php echo $getNames['patient_names']; ?></td>
                                    <td width="15%">Sex/Age:</td><td width="30%"><?php echo $getNames['patient_gender']; ?> / <?php echo $getNames['patient_age']; ?> Years</td>
                                    </tr>
                                    <tr>
                                      <td>Patient No:</td>
                                      <td><?php echo $getNames['patient_no']; ?></td>
                                      <td>Date of Birth:</td>
                                      <td><?php echo $getNames['patient_dob']; ?></td>
                                    </tr>
                                    <tr>
                                      <td>Address</td>
                                      <td><?php echo $getNames['patient_address']; ?></td>
                                      <td>Patient Phone No.:</td>
                                      <td><?php echo $getNames['patient_phone']; ?></td>
                                    </tr>
                                    <tr>
                                    <td>Specimen</td><td><?php echo $getNames3['patient_speciment']; ?></td>
                                   	<td>Specimen(s) Collected Date &amp; Time</td><td><?php echo $getNames3['date_captured']; ?></td>
                                    </tr>
                                     <tr>
                                    <td>Date Printed</td><td colspan="3"><?php echo date("d/m/Y"); ?></td>
                                    </tr>
                                 </table>
                                </div>

                                
                            </div>

                      <div class="table-responsive m-t">
                
                <?php
if(isset($_GET['oldr'])){ 
		 $oldr=$_GET['oldr'];
			$parts=explode("/", $oldr);
				 $oldr=$parts['0'];
				 $result_id=$parts['1'];
	
		include("cp_rdc.php");

}

?>
                               
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
