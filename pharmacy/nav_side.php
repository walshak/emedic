<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                        <?php
                        $staff_p = "../staff_photos/";
                        $uname = isset($_SESSION['username']) ? $_SESSION['username'] : '';
                        $fullname = isset($fullname) ? $fullname : $uname;
                        $photo_path = $staff_p . 'port_' . $uname . '.jpg';
                        $photo = file_exists($photo_path) ? $photo_path : '../img/user_avatar_lg.png';

                        if (isset($_SESSION['unit_head']) && $_SESSION['unit_head'] == 1) {
                            $unit = " <i>(Unit Head)</i>";
                        } else {
                            $unit = '';
                        }
                        ?>
                        <img alt="image" class="img-circle" src="<?php echo htmlspecialchars($photo); ?>" height="50" width="50">
                    </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold"><?php echo ($fullname . $unit); ?></strong>
                            </span>
                            <span class="text-muted text-xs block">
                                <?php
                                if (!empty($_SESSION['Designation'])) {
                                    echo htmlspecialchars($_SESSION['Designation']);
                                } else {
                                    echo htmlspecialchars($_SESSION['speciality']);
                                }
                                ?>
                                <b class="caret"></b>
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="../profile/index.php?profile=<?php echo urlencode($_SESSION['username']); ?>">Profile</a></li>
                        <li><a href="../profile/mailbox.php">Mailbox</a></li>
                        <li class="divider"></li>
                        <li><a href="../index.php">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">IN+</div>
            </li>

            <?php if (isset($_SESSION['navigate']) && $_SESSION['navigate'] == 'admin') { ?>
                <li>
                    <a href="../admin/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span></a>
                </li>
            <?php } ?>

            <?php if (isset($rights) && $rights == 'PH') { ?>
                <li>
                    <a href="../pharmacy/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a>
                </li>
            <?php } elseif (isset($rights) && $rights == 'NS') { ?>
                <li>
                    <a href="../nursing/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a>
                </li>
            <?php } elseif (isset($rights) && $rights == 'LB') { ?>
                <li>
                    <a href="../investigations/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard <?php echo htmlspecialchars($rights); ?></span></a>
                </li>
            <?php } else { ?>
                <li>
                    <a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a>
                </li>
            <?php } ?>

            <?php if (isset($_SESSION['external_sale']) && $_SESSION['external_sale'] == 1) { ?>
                <li>
                    <a href="../admin/index.php?sale"><i class="fa fa-shopping-cart"></i> <span class="nav-label">External Sales</span></a>
                </li>
            <?php } ?>

            <?php include("../inc/nav_side_profile.php"); ?>
        </ul>
    </div>
</nav>