<?php include("../Connections/Conn.php");
session_start();

if (isset($_POST["save"])) {
    // Check if referral name already exists
    $stmt = $db->prepare("SELECT * FROM referrals WHERE name = :ref_name");
    $stmt->bindParam(':ref_name', $_POST["ref_name"], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Insert new referral if not exists
        $insertStmt = $db->prepare("INSERT INTO referrals(name, referral_type, designation, phone, addr) VALUES(:name_referral, :referal_type, :designation, :phone, :addr)");
        $insertStmt->bindParam(':name_referral', $_POST['name_referral'], PDO::PARAM_STR);
        $insertStmt->bindParam(':referal_type', $_POST['referal_type'], PDO::PARAM_STR);
        $insertStmt->bindParam(':designation', $_POST['designation'], PDO::PARAM_STR);
        $insertStmt->bindParam(':phone', $_POST['phone'], PDO::PARAM_STR);
        $insertStmt->bindParam(':addr', $_POST['addr'], PDO::PARAM_STR);
        $insertStmt->execute();

        header("location:referral.php");
        exit;
    } else {
        // Update existing references
        $updateLabStmt = $db->prepare("UPDATE lab_manage SET referral = :name_referral WHERE referral = :ref_name");
        $updateLabStmt->bindParam(':name_referral', $_POST['name_referral'], PDO::PARAM_STR);
        $updateLabStmt->bindParam(':ref_name', $_POST['ref_name'], PDO::PARAM_STR);
        $updateLabStmt->execute();

        $updatePharmStmt = $db->prepare("UPDATE pharm_ext SET referral = :name_referral WHERE referral = :ref_name");
        $updatePharmStmt->bindParam(':name_referral', $_POST['name_referral'], PDO::PARAM_STR);
        $updatePharmStmt->bindParam(':ref_name', $_POST['ref_name'], PDO::PARAM_STR);
        $updatePharmStmt->execute();

        $updateReferralStmt = $db->prepare("UPDATE referrals SET name = :name_referral, referral_type = :referal_type, designation = :designation, phone = :phone, addr = :addr WHERE name = :ref_name");
        $updateReferralStmt->bindParam(':name_referral', $_POST['name_referral'], PDO::PARAM_STR);
        $updateReferralStmt->bindParam(':referal_type', $_POST['referal_type'], PDO::PARAM_STR);
        $updateReferralStmt->bindParam(':designation', $_POST['designation'], PDO::PARAM_STR);
        $updateReferralStmt->bindParam(':phone', $_POST['phone'], PDO::PARAM_STR);
        $updateReferralStmt->bindParam(':addr', $_POST['addr'], PDO::PARAM_STR);
        $updateReferralStmt->bindParam(':ref_name', $_POST['ref_name'], PDO::PARAM_STR);
        $updateReferralStmt->execute();

        header("location:referral.php");
        exit;
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
        <h2>Referrals</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.html">Home</a>
            </li>
            <li class="active">
                <strong>Referrals</strong>
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
                            <h5>Add Referral</h5>
                        </div>
                        <div class="ibox-content">
  
  <?php
							
							
  	$sn=$_GET["edit"];
	
	$stmt=$db->query("SELECT * FROM referrals WHERE sn='$sn'");
	if($stmt->rowCount()>0){
		$roww=$stmt->fetch(PDO::FETCH_ASSOC);
	}
	?>                      
                            <form action="referral.php" method="POST" id="subject" name="subject" >

                            <div class="form_sep">
                            <label for="reg_input_name" class="req">Name of Referral:</label>
                            <input type="text" id="name_referral" name="name_referral" class="form-control" required value="<?php echo $roww['name']; ?>">
                            </div>
                                                        
                            <div class="form_sep">
                            <label for="reg_select" class="req">Type</label>
                            <select name="referal_type" id="referal_type" class="form-control"  required>
                            			<?php if($roww['referral_type']!=''){?>
<option selected="selected" value="<?php echo $roww['referral_type']; ?> "><?php echo $roww['referral_type']; ?> </option>
                                        <?php }else{ ?>
                                        <option selected="selected" value="">Select...</option>
                                        <?php } ?>
                                                           
                                                           
                                    <option value="Organisation">Organisation</option>
                                    <option value="Individual">Individual</option>
                            </select>
                             </div>	
                             
                            <div class="form_sep">
                            <label for="reg_input_name" class="">Designation:</label>
                            <input type="text" id="designation" name="designation" class="form-control" value="<?php echo $roww['designation']; ?>">
                            </div>
                                                         
                             
                            <div class="form_sep">
                            <label for="reg_input_name" class="">Phone:</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $roww['phone']; ?>">
                            </div>
                            
                            <div class="form_sep">
                            <label for="reg_input_name" class="">Address:</label>
                            <input type="text" id="addr" name="addr" class="form-control" value="<?php echo $roww['addr']; ?>">
                            </div>
                                                
                                <div class="form_sep">
                                <button class="btn btn-success" type="submit" name="save">Save</button>
                            <input type="hidden" name="ref_name" value="<?php echo $roww['name']; ?>">
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
<?php
	$stmt=$db->query("SELECT * FROM referrals order by sn");
	if($stmt->rowCount()>0){?>
    
                                            <div class="alert alert-info">
                                       <?php echo '<strong>' . $stmt->rowCount() .' Results found</strong>'; ?>
                                        </div>
  
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                                                <thead>
                                                <tr>
                                                    <th data-toggle="true">#</th>
                                                    <th data-toggle="true">Name</th>
                                                    <th data-toggle="true">Type</th>
                                                    <th data-toggle="true">Designation</th>
                                                    <th data-toggle="true">Phone</th>
                                                    <th data-toggle="true">Address</th>
                                                    <th data-toggle="true">.</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                    while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) {  ?>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php echo $roww['name']; ?></td>
                                                        <td><?php echo $roww['referral_type']; ?></td>
                                                        <td><?php echo $roww['designation']; ?></td>
                                                        <td><?php echo $roww['phone']; ?></td>
                                                        <td><?php echo $roww['addr']; ?></td>
                                                        <td> <a href="referral.php?edit=<?php echo $roww['sn']; ?>">Edit</a></td>
</tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                </tbody>
                                                </table>
                                        <?php }else{echo '<br>No Data Available!';}  
										
										?>                   

                            
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
