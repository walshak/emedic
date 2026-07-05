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
                    
<li>
    <a href="../admin/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
</li>

<li>
    <a href="index.php?price"><i class="fa fa-mail-reply-all"></i> <span class="nav-label">Prices Dashboard</span> </a>
</li>

<li>
 <a href="index.php?price=c"><i class="fa fa-user-md"></i> <span class="nav-label">Consultation</span> </a> 
</li> 

<li>
 <a href="index.php?price=m"><i class="fa fa-hospital-o"></i> <span class="nav-label">Medical Services</span> </a> 
</li> 

<li>
 <a href="index.php?price=n"><i class="fa fa-bitbucket"></i> <span class="nav-label">Nursing Services</span> </a> 
</li> 

<li>
 <a href="index.php?price=o"><i class="fa fa-tasks"></i> <span class="nav-label">Others</span> </a> 
</li>

<li>
 <a href="index.php?price=iv"><i class="fa fa-flask"></i> <span class="nav-label">Investigations</span> </a> 
</li>


            </div>
        </nav>