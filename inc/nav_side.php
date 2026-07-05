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

            <?php
            $dashboards = array(
                'PH' => '../pharmacy/index.php',
                'NS' => '../nursing/index.php',
                'LB' => '../investigations/index.php'
            );
            $link = isset($dashboards[$rights]) ? $dashboards[$rights] : 'index.php';
            ?>

            <li>
                <a href="<?php echo $link; ?>"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a>
            </li>

            <?php if ($_SESSION['create1'] == 1 or $_SESSION['users'] == 1 or $_SESSION['equipmnt'] == 1) { ?>
                <li>
                    <a href="javascript:void(0)"><i class="fa fa-cogs"></i>
                        <span class="nav-label">Settings</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level">
                        <li><a href="setup.php">Investigation Setup</a></li>
                        <li><a href="invst_tmplate.php">Investigation Template</a></li>
                        <li><a href="setup.php?combos">Lab Combinations</a></li>
                        <?php if ($_SESSION['equipmnt'] == 1) { ?>
                            <li><a href="machine.php">Equipment Maintenance Logsheet</a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php
            $section = $_SESSION['section'];
            $speciality = $_SESSION['speciality'];
            $unit_head = $_SESSION['unit_head'];
            $rights = $_SESSION['rights'];
            $bill = $_SESSION['bill'];

            $investSections = ['Laboratory', 'Radiology'];
            $canViewFullInvestigation = in_array($section, $investSections) || $speciality === 'Administrator' || $unit_head === 1;
            ?>

            <!-- Investigation Menu -->
            <?php if ($canViewFullInvestigation) : ?>
                <li>
                    <a href="javascript:void(0)">
                        <i class="fa fa-flask"></i>
                        <span class="nav-label">Investigation</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level">
                        <li><a href="mgt.php"><i class="fa fa-flask"></i> Management</a></li>
                        <li><a href="mgt_rpt.php"><i class="fa fa-info-circle"></i> Management Report</a></li>

                        <?php if ($unit_head == 1) : ?>
                            <li><a href="../dbase/patient_dbase/test.php"><i class="fa fa-info-circle"></i> Advance Report</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php else : ?>
                <li>
                    <a href="mgt.php"><i class="fa fa-flask"></i> <span class="nav-label">Investigation</span></a>
                </li>
            <?php endif; ?>

            <!-- Billing / Patients Menu -->
            <?php if ($bill == 1) : ?>
                <li><a href="xsale.php"><i class="fa fa-users"></i> Patients</a></li>
                <li><a href="referral.php"><i class="fa fa-send"></i> Add Referrals</a></li>
            <?php endif; ?>

            <!-- Stock Manager -->
            <?php if ($unit_head == 1 && $rights == 'LB') : ?>
                <li>
                    <a href="../admin/index.php?stock"><i class="fa fa-tasks"></i> Stock Manager</a>
                </li>
            <?php endif; ?>

            <?php include('../inc/nav_side_profile.php'); ?>

            <?php if ($_SESSION['rights'] != 'LB') { ?>
                <li class="special_link">
                    <a href="../admin/index.php"><i class="fa fa-times"></i> <span class="nav-label">Main Dashboard</span></a>
                </li>
            <?php } ?>

        </ul>
    </div>
</nav>