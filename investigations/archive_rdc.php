<?php include("../Connections/Conn.php");?>

<?php

	if (isset($_GET['emr'])){
		$emr=$_GET['emr'];
	}

	//if (isset($_POST['search'])){
		//$emr=$_POST['search'];
	//}

		//$stmt_en=$db->query("SELECT hospital_no FROM enrollee WHERE hospital_no='$emr'"); 
			//	if($stmt_en->rowCount()>0){
						
				//	}else{

	//$stmt=$db->query("SELECT * FROM pharm_ext WHERE transc_code='$emr'");
		//		if($stmt_en->rowCount()>0){
		//					
		//			}else{		
	//	header("location:index.php?Nex");	
			//$noExist='1';	
		//			}
//}

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
        <h2>Past Results</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.html">Home</a>
            </li>
            <li class="active">
                <strong>Reports</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

        <div class="wrapper wrapper-content  animated fadeInRight">
            <div class="row">
                <div class="col-lg-3">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Select Investigation Type</h5>
                        </div>
                        <div class="ibox-content">
                        
    <div class="form_sep">
            
			<ul>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=bc'; ?>">rdc_blood_culture</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=cp'; ?>">rdc_chemical_pathology</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=csf'; ?>">rdc_csf_microscopy</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=ha'; ?>">rdc_haemotology</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=lip'; ?>">rdc_lipid</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=mi'; ?>">rdc_microbiology</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=omb'; ?>">rdc_other_microbiology</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=omi'; ?>">rdc_other_microscopy</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=sem'; ?>">rdc_seminal</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=ser'; ?>">rdc_serology</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=sto'; ?>">rdc_stool_microscopy</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=uri'; ?>">rdc_Urinalysis</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=urm'; ?>">rdc_urine_microscopy</a></li>
                <li><a href="<?php echo 'archive_rdc.php?emr=' . $emr .'&old=ray'; ?>">X-ray</a></li>
			</ul>            

    </div>
                            
                        </div>
                    </div>


                </div>
                <div class="col-lg-9">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Result Lists & Report Display</h5>
                        </div>
                        <div class="ibox-content">


<?php

if(isset($_GET['oldr'])){ 
		 $oldr=$_GET['oldr'];
			$parts=explode("/", $oldr);
				 $oldr=$parts['0'];
				 $result_id=$parts['1'];
	
		include("cp_rdc.php");

}

if(isset($_GET['oldr_print'])){ 
		 $oldr=$_GET['oldr_print'];
			$parts=explode("/", $oldr);
				 $oldr=$parts['0'];
				 $result_id=$parts['1'];
	
		include("cp_rdc.php");

}

if(isset($_GET['old'])){ 
$old=$_GET['old'];

if($old=='bc'){ 
	$table="rdc_blood_culture";
}elseif($old=='cp'){
	$table="rdc_chemical_pathology";
}elseif($old=='csf'){
	$table="rdc_csf_microscopy";
}elseif($old=='ha'){
	$table="rdc_haemotology";
}elseif($old=='lip'){
	$table="rdc_lipid";
}elseif($old=='mi'){
	$table="rdc_microbiology";
}elseif($old=='omb'){
	$table="rdc_other_microbiology";
}elseif($old=='omi'){
	$table="rdc_other_microscopy";
}elseif($old=='sem'){
	$table="rdc_seminal";
}elseif($old=='ser'){
	$table="rdc_serology";
}elseif($old=='sto'){
	$table="rdc_stool_microscopy";
}elseif($old=='uri'){
	$table="rdc_Urinalysis";
}elseif($old=='urm'){
	$table="rdc_urine_microscopy";
}elseif($old=='ray'){
	$table="rdc_xray";
}






$stmt=$db->query("SELECT * FROM $table WHERE patient_no='$emr' order by date_captured desc");

                if($stmt->rowCount()>0){?>
                
                					<hr>
                               <div class="alert alert-success">
                               
                               <?php echo $stmt->rowCount() . ' requests found ' . $table; ?>
                                </div>      
                               <table class="table table-striped table-bordered table-hover" >                 
                                                <thead>
                                                <tr>
                                                     <th data-toggle="true">#</th>
                                                     <th data-toggle="true">Result Date</th>
                                                    <th data-toggle="true">Patient Name</th>
                                                    <th data-toggle="true">Lab Scientist</th>
                                                    <th data-toggle="true">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                        while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) {                                                        ?>
                                                       <tr> 
                                                       <td><?php echo $n; ?></td> 
                                     		<td><?php echo date('d,M y h:i a', strtotime($roww['date_captured']));?></td>
                                          <td><?php echo $roww['patient_firstName'] . ', ' . $roww['patient_surname'] ; ?></td>
                                                        <td><?php echo $roww['lab_tech']; ?></td>
                                              <td><a href="lab_print_old_rdc.php?emr=<?php echo $emr .'&oldr='. $old .'/'.$roww['result_id'] ; ?>">[ See Result ]</a></td>
                                                        </tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                        
                                                </tbody>
                                                </table>
                                   <?php }else{ ?>
                                  		 <div class="alert alert-warning">No Result found</div>
                                   <?php } 
								   
} ?>

                            
                        </div>
                    </div>
                    
<div class="pull-right">
<a href="index.php" class="btn btn-danger" ><i class="fa fa-timees"></i>Close</a>
</div>

                </div>
            </div>

        </div>
           
                    </div>
        </div>
        
<?php include("search_modal.php") ?>        
        
<?php include("../inc/footer_scripts.php"); ?>


    
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
