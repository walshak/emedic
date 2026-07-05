<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');
?>

<body class="fixed-navigation">
	<div id="wrapper">

		<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>
			<div class="">

				<div class="row">
					<div class="col-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Accounting Diagnostics</h5>
							</div>
							<div class="ibox-content" align="center">

								<h2>DIAGNOSTIC TOOLS</h2>
								<p>Use these tools to diagnose and fix accounting discrepancies in the system.</p>

								<div class="row">
									<div class="col-md-6">
										<div class="ibox">
											<div class="ibox-title">
												<h3>Unbalanced Transactions</h3>
											</div>
											<div class="ibox-content">
												<p>Detect and fix unbalanced ledger transactions by reference number.</p>
												<a href="diagnostics_unbalanced.php" class="btn btn-primary btn-block">
													<i class="fa fa-balance-scale"></i> Check Unbalanced Transactions
												</a>
											</div>
										</div>
									</div>

									<div class="col-md-6">
										<div class="ibox">
											<div class="ibox-title">
												<h3>Trial Balance Analysis</h3>
											</div>
											<div class="ibox-content">
												<p>Generate detailed trial balance reports with flexible date ranges.</p>
												<a href="diagnostics_trial_balance.php" class="btn btn-success btn-block">
													<i class="fa fa-chart-bar"></i> Generate Trial Balance
												</a>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="ibox">
											<div class="ibox-title">
												<h3>Ledger Integrity Check</h3>
											</div>
											<div class="ibox-content">
												<p>Comprehensive check of ledger integrity and transaction consistency.</p>
												<a href="diagnostics_integrity.php" class="btn btn-warning btn-block">
													<i class="fa fa-check-circle"></i> Check Ledger Integrity
												</a>
											</div>
										</div>
									</div>

									<div class="col-md-6">
										<div class="ibox">
											<div class="ibox-title">
												<h3>Account Reconciliation</h3>
											</div>
											<div class="ibox-content">
												<p>Reconcile accounts and identify discrepancies in financial records.</p>
												<a href="diagnostics_reconciliation.php" class="btn btn-info btn-block">
													<i class="fa fa-calculator"></i> Account Reconciliation
												</a>
											</div>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<?php include '../../inc/footer.php'; ?>

			</div>
		</div>

		<?php include('../modal_lock.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>

		<script>
			<?php
			if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
				timeOut: 5000
			})
			<?php } ?>
		</script>

		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
		<script src="../js/idle.js"></script>
</body>

</html>
