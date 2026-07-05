<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element"> <span>

                        <?php
                        if ($_SESSION['unit_head'] == 1) {
                            $unit = " <i>(Unit Head)</i>";
                        } else {
                            $unit = '';
                        }
                        ?>
                    </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname . $unit; ?></strong>
                            </span> <span class="text-muted text-xs block"><?php if ($_SESSION['Designation'] != "") {
                                                                                echo $_SESSION['Designation'];
                                                                            } else {
                                                                                echo $_SESSION['speciality'];
                                                                            } ?><b class="caret"></b></span> </span> </a>
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
                <?php if ($_SESSION['rights'] == 'CA') { ?>
                    <a href="../billing/index.php?main"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
                <?php } else { ?>
                    <a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
                <?php } ?>
            </li>

            <?php include("nav_side_profile.php"); ?>

        </ul>

    </div>
</nav>