<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">


            <?php
            $staff_p = "../staff_photos/";
            $uname = isset($_SESSION['username']) ? $_SESSION['username'] : '';
            $photo_path = $staff_p . 'port_' . $uname . '.jpg';
            $photo = file_exists($photo_path) ? $photo_path : '../img/user_avatar_lg.png';
            $fullname = isset($fullname) ? $fullname : $uname;
            $unit = (!empty($_SESSION['unit_head']) && $_SESSION['unit_head'] == 1) ? "Unit Head" : "";
            $unit_display = $unit ? " - $unit" : "";
            $navigate = isset($_SESSION['navigate']) ? $_SESSION['navigate'] : 'index';
            ?>


            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                        <img alt="image" class="img-circle" src="<?php echo htmlspecialchars($photo); ?>" height="50" width="50">
                    </span>

                    <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0)">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold"><?php echo htmlspecialchars($fullname); ?></strong>
                            </span>
                            <span class="text-muted text-xs block">
                                <?php
                                if (!empty($_SESSION['Designation'])) {
                                    echo htmlspecialchars($_SESSION['Designation'] . $unit_display);
                                } else {
                                    echo htmlspecialchars($_SESSION['speciality'] . $unit_display);
                                }
                                ?>
                                <b class="caret"></b>
                            </span>
                        </span>
                    </a>

                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="../profile/index.php?profile=<?php echo urlencode($uname); ?>">Profile</a></li>
                        <li><a href="../profile/mailbox.php">Mailbox</a></li>
                        <li class="divider"></li>
                        <li><a href="../index.php">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">IN+</div>
            </li>

            <li>

                <?php if ($_SESSION['rights'] == 'CA') { ?>
                    <a href="../billing/index.php?main"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
                <?php } else { ?>
                    <a href="../<?php echo $_SESSION['navigate']; ?>/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span> </a>
                <?php } ?>


            </li>


            <?php ///include("nav_side_profile.php"); 
            ?>

            <li>
                <a href="#"><i class="fa fa-dropbox"></i> <span class="nav-label">my Transactions</span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="../investigations/xsale.php">Ex-Patients</a></li>
                    <li><a href="transc.php">Report</a></li>
                </ul>
            </li>


        </ul>

    </div>
</nav>