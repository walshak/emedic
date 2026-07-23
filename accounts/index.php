<?php include(__DIR__ . "/../Connections/Conn.php"); ?>

<?php
session_start();
include(__DIR__ . '/../inc/header.php');
?>

<body class="fixed-navigation">
	<div id="wrapper">

		<?php include(__DIR__ . "/nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include __DIR__ . '/../inc/nav_header.php'; ?>
			<div class="">






				<div class="row">
					<div class="col-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Accounts Dashboard</h5>
							</div>
							<div class="ibox-content" align="center">





								<BR><BR>




								<h2>ACCOUNT SETTINGS</h2>
								<a href="fiscal_years.php" class="btn btn-app"><i class="fa fa-cog"></i>Setting</a>
								<a href="report_gl_settings.php" class="btn btn-app"><i class="fa fa-cog"></i>Gl Bindings</a>
								<a href="gl_classes.php" class="btn btn-app"><i class="fa fa-book"></i>GL Classes</a>
								<a href="gl_groups.php" class="btn btn-app"><i class="fa fa-list"></i>GL Groups</a>

								<hr><BR><BR>
								<H2>POSTING/DATA ENTRY</H2>
								<a href="gl_accounts.php" class="btn btn-app"><i class="fa fa-book"></i>GL Accounts</a>
								<a href="journal_entry.php" class="btn btn-app"><i class="fa fa-edit"></i>Journal Entry</a>
								<a href="all_journal.php" class="btn btn-app"><i class="fa fa-book"></i>All Journals</a>
								<a href="../admin/index.php?stock=o&pdr" class="btn btn-app"><i class="fa fa-list"></i>Purchase Order</a>

								<hr><BR><BR>

								<H2>ACCOUNT REPORTS</H2>
								<a href="balance_sheet.php" class="btn btn-app"><i class="fa fa-line-chart"></i>Balance Sheet</a>
								<a href="trial_balance.php" class="btn btn-app"><i class="fa fa-bar-chart"></i>Trial Balance</a>
								<a href="income.php" class="btn btn-app"><i class="fa fa-money"></i>Income Statement</a>
								<a href="invoice_reports.php" class="btn btn-app"><i class="fa fa-list"></i>Invoice Statement</a>
								<a href="ar_reports.php" class="btn btn-app"><i class="fa fa-list"></i>AR Report</a>
								<a href="ap_reports.php" class="btn btn-app"><i class="fa fa-list"></i>AP Report</a>
								<a href="cash_flow.php" class="btn btn-app"><i class="fa fa-question-circle"></i>Cash Flow Statement</a>
								<a href="general_ledger_sheet.php" class="btn btn-app"><i class="fa fa-book"></i>General Ledger Sheet</a>

								<hr><BR><BR>

								<H2>DIAGNOSTICS & TOOLS</H2>
								<a href="diagnostics.php" class="btn btn-app" style="background-color: #f39c12; color: white;"><i class="fa fa-stethoscope"></i>Diagnostics</a>

							</div>
						</div>
					</div>
				</div>


				<?php include __DIR__ . '/../inc/footer.php'; ?>

			</div>
		</div>


		<?php include(__DIR__ . '/../modal_lock.php'); ?>
		<?php include(__DIR__ . "/../inc/footer_scripts.php"); ?>


		<script>
			<?php
			if (isset($error_status) && $error_status == 1) { ?>toastr.error('<?php echo isset($error_msg) ? $error_msg : ''; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } elseif (isset($error_status) && $error_status == 2) { ?>toastr.success(' <?php echo isset($error_msg) ? $error_msg : ''; ?> ', 'Success', {
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