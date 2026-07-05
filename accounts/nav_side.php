<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <?php
            $staff_p = "../staff_photos/";
            $photo_path = $staff_p . 'port_' . $uname . '.jpg';
            $photo = file_exists($photo_path) ? $photo_path : '../img/user_avatar_lg.png';
            $unit = ($_SESSION['unit_head'] == 1) ? "Unit Head" : "";
            ?>

            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                        <img alt="image" class="img-circle" src="<?php echo $photo; ?>" height="50" width="50">
                    </span>

                    <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0)">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold"><?php echo $fullname; ?></strong>
                            </span>
                            <span class="text-muted text-xs block">
                                <?php
                                if (!empty($_SESSION['Designation'])) {
                                    echo $_SESSION['Designation'] . ' - ' . $unit;
                                } else {
                                    echo $_SESSION['speciality'] . ' - ' . $unit;
                                }
                                ?>
                                <b class="caret"></b></span>
                        </span>
                    </a>

                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="../profile/index.php?profile=<?php echo $_SESSION['username']; ?>">Profile</a></li>
                        <li><a href="../profile/mailbox.php">Mailbox</a></li>
                        <li class="divider"></li>
                        <li><a href="../index.php">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">IN+</div>
            </li>

            <li>
                <a href="../<?php echo $_SESSION['navigate']; ?>/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
            </li>

            <li>
                <a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Accounts Dashboard</span> </a>
            </li>
            <?php // include("../inc/nav_side_profile.php"); 
            ?>
        </ul>
    </div>
</nav>