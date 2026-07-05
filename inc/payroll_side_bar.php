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
                        $photo_path = $staff_p . $uname . '.jpg';
                        $photo = file_exists($photo_path) ? $photo_path : '../img/user_avatar_lg.png';
                        ?>
                        <img alt="image" class="img-circle" src="<?php echo htmlspecialchars($photo); ?>" height="50" width="50">
                    </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold"><?php echo htmlspecialchars($fullname); ?></strong>
                            </span>
                            <span class="text-muted text-xs block">
                                <?php echo htmlspecialchars($_SESSION['Designation']); ?>
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

            <li>
                <a href="../admin/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a>
            </li>

            <li>
                <a href="#"><i class="fa fa-gears"></i> <span class="nav-label">HR Settings</span></a>
                <ul class="nav nav-second-level">
                    <li><a href="index.php?Cadre">Cadre/Staff Category</a></li>
                    <li><a href="index.php?Designation">Add Designation</a></li>
                    <li><a href="index.php?Department">Department</a></li>
                    <li><a href="index.php?Specialist">Specialist</a></li>
                    <li><a href="index.php?Bank">Bank</a></li>
                </ul>
            </li>

            <li>
                <a href="#"><i class="fa fa-user"></i> <span class="nav-label">Employee</span></a>
                <ul class="nav nav-second-level">
                    <li><a href="index.php?Add">Add Employee</a></li>
                    <li><a href="index.php?hr">Employee List</a></li>
                </ul>
            </li>

            <li>
                <a href="#"><i class="fa fa-briefcase"></i> <span class="nav-label">Payroll/Salaries</span></a>
                <ul class="nav nav-second-level">
                    <li><a href="index.php?ED">Pay Heads</a></li>
                    <li><a href="index.php?sal">Salary Settings</a></li>
                    <li><a href="index.php?gpay">Generate Payslip</a></li>
                    <li><a href="index.php?srp">Reports</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>