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
    <a href="../admin/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span> </a>
</li>

<li>
 <a href="index.php?ptm=all"><i class="fa fa-users"></i> <span class="nav-label">All Patients</span> </a> 
</li> 

<li>
 <a href="index.php?ptm=ppt"><i class="fa fa-th"></i> <span class="nav-label">Private Patients</span> </a> 
</li> 

<li>
 <a href="index.php?ptm=cpt"><i class="fa fa-building-o"></i> <span class="nav-label">Coporate Patients</span> </a> 
</li> 

<li>
 <a href="index.php?ptm=fld"><i class="fa fa-folder-open"></i> <span class="nav-label">Family Folders</span> </a> 
</li>

<li>
 <a href="index.php?ptm=nhs"><i class="fa fa-slideshare"></i> <span class="nav-label">NHIS Patients</span> </a> 
</li>

<li>
 <a href="index.php?ptm=phs"><i class="fa fa-file-o"></i> <span class="nav-label">PHIS (HMOs)</span> </a> 
</li>

<li>
 <a href="index.php?ptm"><i class="fa fa-user"></i> <span class="nav-label">Search for Patient</span> </a> 
</li>

            </div>
        </nav>
        
