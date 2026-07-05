<?php include("../Connections/Conn.php");?>

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
        <h2>Laboratory User Privileges</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.php">Home</a>
            </li>
            <li class="active">
                <strong>Privileges</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

        <div class="wrapper wrapper-content  animated fadeInRight">
            <div class="row">
                                <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>User Privileges</h5>
                        </div>
                        <div class="ibox-content">
                        
<?php 
							
							///$rights='rights.php';
							$file_name="rights.php";
							include("../inc/rdc_rigthts.php")?>
                            
                        </div>
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
   
      $(document).ready(function(){
		
		$("#investigation").hide();
		
    $('#speciality').on('change', function() {
	
      if ( this.value == 'Receptionist' || this.value == 'Administrator' )
      {
		$("#investigation").hide();
      }
	  else
      {
		$("#investigation").show();
      }	  
	  
    });
});
   
   
   
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
