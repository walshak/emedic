<?php
session_start();
 include("../Connections/Conn.php");
 include("../admin/admin_previleges.php");

 ?>

<?php


if (isset($_POST["apply_set"])) {
		
// Prepare and execute the select statement
$stmt = $db->prepare("SELECT username FROM med_users_rights WHERE username = :username");
$stmt->bindValue(':username', $_POST["username"], PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // Prepare the update statement
    $stmt = $db->prepare("UPDATE med_users_rights SET 
        consl = :consl,
        drug = :drug,
        invest = :invest
        WHERE username = :username");

    // Bind values to the parameters
    $stmt->bindValue(':consl', $_POST["consl"], PDO::PARAM_STR);
    $stmt->bindValue(':drug', $_POST["drug"], PDO::PARAM_STR);
    $stmt->bindValue(':invest', $_POST["invest"], PDO::PARAM_STR);
    $stmt->bindValue(':username', $_POST["username"], PDO::PARAM_STR);
    // Execute the update statement
    $stmt->execute();
?>

                	<div class="alert alert-success">Saved Successfully
    </div>
    <?php
	
		}else{

// Prepare the insert statement
$stmt = $db->prepare("INSERT INTO med_users_rights (
    username, consl, drug,invest
) VALUES (
    :username,:consl,:drug,:invset
)");

// Bind values to the parameters
$stmt->bindValue(':username', $_POST["username"], PDO::PARAM_STR);
$stmt->bindValue(':consl', $_POST["consl"], PDO::PARAM_STR);
$stmt->bindValue(':drug', $_POST["drug"], PDO::PARAM_STR);
$stmt->bindValue(':invest', $_POST["invest"], PDO::PARAM_STR);
// Execute the insert statement
$stmt->execute(); ?>
                    	<div class="alert alert-success">Saved Successfully
    </div>

    <?php
	
		}
				  
}	
	
?>



 

    <!DOCTYPE html>
    <html>
    
        <?php include("../inc/header.php"); 
       /// $dept_name=$_SESSION['dept_name'];
        $dept_id=$_SESSION['dept_id'];
        //echo $unit_head;
       // echo '<br>';
        //echo $dept_id;
     
        ///$rights = $_SESSION['rights'];
       ///echo $voucher_unit = $_SESSION['voucher_unit'];

?>

<body>
    
    <div id="wrapper">
    
    <nav class="navbar-default navbar-static-side" role="navigation">
                <div class="sidebar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="nav-header">
                            <div class="dropdown profile-element"> <span>
                            <img alt="image" class="img-circle" src="<?php if(file_exists(staff_p . 'port_'.$uname . '.'. 'jpg')){ echo staff_p . 'port_'.$uname . '.'. 'jpg'; } else{echo '../img/user_avatar_lg.png'; }?>" height="50" width="50">
                               
                                 </span>
                                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                             <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname ?></strong>
                               </span> <span class="text-muted text-xs block"><?php echo $_SESSION['Designation'] ?> <b class="caret"></b></span> </span> </a>
                                <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                    <li><a href="index.php?profile=<?php echo $_SESSION['username'] ?>">Profile</a></li>
                                    <li><a href="mailbox.php">Mailbox</a></li>
                                    <li class="divider"></li>
                                    <li><a href="../index.php">Logout</a></li>
                                </ul>
                            </div>
                            <div class="logo-element">
                                IN+
                            </div>
                        </li>
                        
                        <li>
                            <a href="../<?php echo $_SESSION['navigate']; ?>/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
                        </li>
                        
                        <?php ///include("../inc/nav_side_profile.php"); ?> 
                                            
                    </ul>
    
                </div>
            </nav>
    
    
    <div id="page-wrapper" class="gray-bg">
       <?php include("../inc/nav_header.php"); ?>    


<div class="row">
<div class="col-lg-12">
    <div class="ibox float-e-margins">
    <div class="ibox-title"><h5>Medical Services USERS Privileges</h5></div>

<div class="ibox-content">
          <form action="med_rights.php" method="POST">

          <div class="row">
          <div class="col-md-8">     
				 <div class="form_sep">
						<label for="reg_input_no" class="req">Select a User & Setup Rights</label>
						<select name="staff_id"  class="input-sm chosen-select" style="width:350px;" >
						 <option selected="selected" value="">Search and Select Staff</option>
						<?php 
						
						$stmt = $db->query("SELECT u.id, h.FirstName, h.MiddleName, h.LastName, h.Cadre 
						FROM admin_users as u inner join hremp as h on h.EmployeeCode=u.EmployeeCode WHERE u.status='1' and u.rights='$rights' order by FirstName");
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
			<option value="<?php echo $row["id"]; ?>"><?php echo $row["FirstName"] .' ' . $row["LastName"]. ' ' . $row["MiddleName"]. ' (' . $row["Cadre"] .')'; ?></option>
						<?php } ?>
						</select>

					</div>
				 <div class="form_sep">
					  <button type="submit" class="btn btn-success btn btn-sm" autofocus name="apply_">Apply Search</button>
					</div>
            </div>
            
            <div class="col-md-4"> 
            <label for="reg_input_no" class="">.</label><br>
            <a rel="" href="index.php?rit" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>
            </div>
            </div> 
                 </form>

            
<hr>
				<?php if(isset($_POST['apply_'])){

                    echo $_POST['staff_id'];

$stmt = $db->prepare("SELECT fullname, username FROM admin_users WHERE id = :staff_id");
$stmt->bindValue(':staff_id', $_POST['staff_id'], PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
					
					?>
                
                
 <form action="med_rights.php" method="POST">

			    <div class="alert alert-success">
                <label for="" class="">Current User : <?php echo $row['fullname']; ?></label>
                <input type="hidden" name="username" value="<?php echo $row['username']; ?>" >
                    </div>
                    

                               <table class="table table-striped table-bordered table-hover" >                 
                                                <thead>
                                                <tr>
                                                  <th width="35%" data-toggle="true"></th>
                                                    <th width="35%" data-toggle="true"></th>
                                                    <th width="35%" data-toggle="true"></th>
                                                  </tr>                                                
                                                </thead>
                                                <tbody>
<tr>
  <td>
    
  <div class="form_sep">
						<label for="reg_input_no" class="req">Select Medical History/Consultation Notes Rights </label>
						<select name="consl"  class="input-sm chosen-select" >
						 <option selected="selected" value="">Select ---</option>
						 <option value="0">Disable Access</option>
						 <option value="1">Patient On Admission</option>
						 <option value="2">Patient On Appointments</option>
						 <option value="3">Patient On my Department</option>
						 <option value="4">All Patients</option>
						</select>
    </div>
  </td>
  <td>

  <div class="form_sep">
						<label for="reg_input_no" class="req">Drugs Information</label><br>
						<select name="drug"  class="input-sm chosen-select" >
						 <option selected="selected" value="">Select ---</option>
						 <option value="0">Disable Access</option>
						 <option value="1">Patient On Admission</option>
						 <option value="2">Patient On Appointments</option>
						 <option value="3">Patient On my Department</option>
						 <option value="4">All Patients</option>
						</select>
    </div>


  </td>
  <td>
  <div class="form_sep">
						<label for="reg_input_no" class="req">Investigations Information</label><br>
						<select name="invest"  class="input-sm chosen-select" >
						 <option selected="selected" value="">Select ---</option>
                         <option value="0">Disable Access</option>
						 <option value="1">Patient On Admission</option>
						 <option value="2">Patient On Appointments</option>
						 <option value="3">Patient On my Department</option>
						 <option value="4">All Patients</option>
						</select>
    </div>
</td>
</tr>







												</tbody>
                                                </table>

                      
                                                <div class="form_sep">
                                                <button class="btn btn-primary" type="submit" name="apply_set" >Apply</button>
                                                </div>
                  
 </form>
 
                 <?php }else{ ?>
                 
                 	<div class="alert alert-warning">
    <p>Select User from List above to Display Here</p>
    </div>


				 <?php } ?>
                            
                        </div>
                                        
</div>
</div>



</div>
</div>
</div>


				 </body>
    
