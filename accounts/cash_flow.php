<?php 
include('inc/header.php');
?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php
        include("../../inc/nav_accounts_side_bar.php");
		$shw_side = 'yes';
        

?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>
			<div class="wrapper wrapper-content">
					<div class="row">
						<div class="col-lg-12">
							<div class="ibox float-e-margins">
								<div class="ibox-title">

									<h5>Accounts Dashboard <?php echo date('h:i a'); ?></h5>
								</div>
								<div class="ibox-content">
									<div class="row">
                                        cash flow
									</div>
								</div>
							</div>
						</div>
					</div>


				<?php include '../../inc/footer.php'; ?>

			</div>
		</div>


		<?php include '/inc/footer_scripts.php'; ?>


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

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
</body>

</html>