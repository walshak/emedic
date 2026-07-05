<nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element"> <span>
                        <img alt="image" class="img-circle" src="<?php if(file_exists(staff_p . $uname . '.'. 'jpg')){ echo staff_p . $uname . '.'. 'jpg'; } else{echo '../img/user_avatar_lg.png'; }?>" height="50" width="50">
                           
                             </span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                         <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname ?></strong>
                           </span> <span class="text-muted text-xs block"><?php echo $_SESSION['Designation'] ?> <b class="caret"></b></span> </span> </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="../profile/index.php?profile=<?php echo $_SESSION['username']; ?>">Profile</a></li>
                                <li><a href="../profile/mailbox.php">Mailbox</a></li>
                                <li class="divider"></li>
                                <li><a href="../index.php">Logout</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            IN+
                        </div>
                    </li>
                    
<li>
    <a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span> </a>
</li>

<li>
 <a href="index.php?rdc"><i class="fa fa-users"></i> <span class="nav-label">Rad/Lab Specific Rights</span> </a> 
</li> 

<li>
 <a href="index.php?bill"><i class="fa fa-th"></i> <span class="nav-label">Biller Specific Rights</span> </a> 
</li> 

<li>
 <a href="index.php?phm"><i class="fa fa-tag"></i> <span class="nav-label">Pharmacy Specific Rights</span> </a> 
</li> 

<li>
 <a href="index.php?min"><i class="fa fa-lock"></i> <span class="nav-label">General Rights</span> </a> 
</li> 



            </div>
        </nav>
        
