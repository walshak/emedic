<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element"> <span>
                        <img alt="image" class="img-circle" src="<?php if (file_exists(staff_p . 'port_' . $uname . '.' . 'jpg')) {
                                                                        echo staff_p . 'port_' . $uname . '.' . 'jpg';
                                                                    } else {
                                                                        echo '../img/user_avatar_lg.png';
                                                                    } ?>" height="50" width="50">

                    </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname ?></strong>
                            </span> <span class="text-muted text-xs block"><?php if ($_SESSION['Designation'] != "") {
                                                                                echo $_SESSION['Designation'];
                                                                            } else {
                                                                                echo $_SESSION['specialist'];
                                                                            } ?> <b class="caret"></b></span> </span> </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="../profile/index.php?profile=<?php echo $_SESSION['username'] ?>">Profile</a></li>
                        <li><a href="../profile/mailbox.php">Mailbox</a></li>
                        <li class="divider"></li>
                        <li><a href="../index.php">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">
                    IN+
                </div>
            </li>

            <?php
					
				///echo $_SESSION['navigate'];
					

	
	
					if($_SESSION['navigate']=='admin'){ ?>
                    <li>
                       <a href="../admin/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
                    </li>
                    <?php } ?>
                    					
					<?php if($rights=='PH'){?>
                    <li>
                        <a href="../pharmacy/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span> </a>
                    </li>
					<?php }elseif($rights=='NS'){?>
                    <li>
                        <a href="../nursing/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span> </a>
                    </li>
					
					<?php }elseif($rights=='LB'){?>
                    <li>
                        <a href="../investigations/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard <?php echo $rights; ?></span> </a>
                    </li>					
					
					<?php }elseif($rights=='DR'){ ?>
                    <li>
                        <a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span> </a>
                    </li>
                    <?php } ?>
            
	
					<?php if($_SESSION['doctor']=='1' or $rights=='NS' or $rights=='DR'){?>
			
<li> <a href="index.php?search"><i class="fa fa-search"></i> <span class="nav-label">Patient Search </span></a> </li>
<li> <a href="index.php?adm"><i class="fa fa-bed"></i> <span class="nav-label">Patients On-Admission </span></a> </li>
<li><a href="../doctor/procedure.php?procedure"><i class="fa fa-eraser"></i> <span class="nav-label">Procedures </span></a></li>
			<?php //if($_SESSION['h_code']=='zmkc'){?>
<li><a href="../doctor/dialysis.php"><i class="fa fa-signal"></i> <span class="nav-label">Dialysis</span> </a></li>
<li><a href="index.php?transplant"><i class="fa fa-cut"></i> <span class="nav-label">Transplant</span> </a> </li>
			<?php // } ?>
<li>
                     
                    </li>

					<?php } ?>
	
	<?php include('../inc/nav_side_profile.php') ;?>                    

        </ul>

    </div>
</nav>