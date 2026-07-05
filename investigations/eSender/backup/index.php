<?php
session_start();
include("../Connections/Conn.php");?>
<!DOCTYPE html>
<html>
<?php
include("../inc/header.php"); 
include("invst_users.php"); 
?>
    

 <title>WebMedic | <?php if($_SESSION['Designation']!=""){echo $_SESSION['Designation']; }else{echo $_SESSION['speciality'];}?></title>
<!--  
 <style>
 .btn.btn-app {
    position: relative;
	padding-top:15px;
    margin: 0 0 5px 5px;
	width:130px;
    height: 45px;
    box-shadow: none;
    border-radius: 0;
    text-align: center;
    color: #666;
    border: 1px solid #ddd;
    background-color: #e3fcf7;
    font-size: 13px
}
.btn.btn-app>.fa,
.btn.btn-app>.glyphicon,
.btn.btn-app>.ion {
    font-size: 10px;
    display: block;
}
.btn.btn-app:hover {
    background:#1ABB9C;
	color: #FFF;
    border-color: #aaa
}
.btn.btn-app:active,
.btn.btn-app:focus {
    box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125)
}
.btn.btn-app>.badge {
    position: absolute;
    top: -3px;
    right: -10px;
    font-size: 10px;
    font-weight: 400
}
</style> -->
 
<body>
    <div id="wrapper">
    
   
   <?php include("../inc/nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">
        
   <?php include("nav_header.php"); 
   
   if (isset($_GET['manage'])){
 		include("../inc/breadcrumb.php");	
   }
   
   include_once("../inc/alert_msg.php");

   ?>
        
            <div class="wrapper wrapper-content">
            
   <?php 
   
				if(strtoupper($_SESSION['password'])=='STAFF123'){
		header("location:../profile/index.php?profile=$uname&changepassword");
		}
				
  	include("../inc/dashb_lab.php");

   ?>  


     
           </div>
                
                
   <?php include("../inc/footer.php"); ?>       
        </div>
    </div>

<?php  include("../inc/lab_mdl.php") ?>
<?php  include("search_modal.php") ?>
<?php   include('../modal_lock.php'); ?>


   <?php include("../inc/footer_scripts.php"); ?> 
     
     	<?php if(isset($_GET['Nex'])){ ?>
    <script>toastr.error('This patient Number Does Not Exist. Please try again!', 'Error', {timeOut: 5000})</script>
    <?php } ?>
     
    <script> 
 	<?php  if($display_status==1){?>
		 $(document).ready(function(){$("#discharge_booking_modal").modal('show');});
	<?php  } ?>
	</script>
    

	
	<?php
    if($display_status==1){ ?>
     				<script type="text/javascript">
                $(document).ready(function(){
                    $("#myModalx").modal('show');
                });
            </script>                

    <?php  } ?>
            
            


<script src="../js/idle.js"></script>

</body>
</html>
