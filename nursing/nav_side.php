<nav class="navbar-default navbar-static-side" role="navigation">
	<div class="sidebar-collapse">
		<ul class="nav" id="side-menu">
			<li class="nav-header">
				<?php
				$staff_p = "../staff_photos/";
				$uname = $_SESSION['username'];
				$photo_path = $staff_p . 'port_' . $uname . '.jpg';
				$photo = file_exists($photo_path) ? $photo_path : '../img/user_avatar_lg.png';
				$unit = ($_SESSION['unit_head'] == 1) ? "Unit Head" : "";
				$unit_display = !empty($unit) ? " - $unit" : "";
				?>

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
						<li><a href="../profile/index.php?profile=<?php echo urlencode($_SESSION['username']); ?>">Profile</a></li>
						<li><a href="../profile/mailbox.php">Mailbox</a></li>
						<li class="divider"></li>
						<li><a href="../index.php">Logout</a></li>
					</ul>
				</div>
				<div class="logo-element">IN+</div>
			</li>

			<?php if ($_SESSION['navigate'] == 'admin'): ?>
				<li>
					<a href="../admin/index.php">
						<i class="fa fa-dashboard"></i>
						<span class="nav-label">Main Dashboard</span>
					</a>
				</li>
			<?php endif; ?>

			<?php if ($rights == 'NS'): ?>
				<li><a href="../nursing/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a></li>
				<li><a href="../nursing/index.php?adm"><i class="fa fa-bed"></i> <span class="nav-label">Admission</span></a></li>
				<li><a href="../doctor/procedure.php?procedure"><i class="fa fa-user-md"></i> <span class="nav-label">Procedures</span></a></li>

				<?php if ($_SESSION['dialysis_visible'] == 1) { ?>
					<li><a href="../doctor/dialysis.php"><i class="fa fa-signal"></i> <span class="nav-label">Dialysis</span></a></li>
					<li><a href="../doctor/index.php?transplant"><i class="fa fa-cut"></i> <span class="nav-label">Transplant</span></a></li>
				<?php } ?>


			<?php else: ?>
				<li><a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a></li>
			<?php endif; ?>

			<?php include("../inc/nav_side_profile.php"); ?>
		</ul>
	</div>
</nav>