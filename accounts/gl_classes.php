<?php include("../Connections/Conn.php");?>

<?php
session_start();
include('../inc/header.php');

include('inc/functions.php');

$classes = $db->query('SELECT * FROM chart_class');

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['class_name'])) {
	if ($_POST['class_name'] != '') {
		try {
			$db->beginTransaction();
			$class_name = $_POST['class_name'];
			$class_id = $_POST['class_id'];

			$update = $db->prepare("UPDATE chart_class SET class_name = ? WHERE cid = ?");
			$update = $update->execute([$class_name, $class_id]);
			$db->commit();
			set_flash_message('Class updated successfully', 'success');
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Faled to update class', 'danger');
			// header('Location:?');
		}
	}
}
?>

<body class="fixed-navigation">
	<div id="wrapper">
<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">

								<h5>Accounts Dashboard - Chart Classes</h5> 
								
								
								
							</div>
							<div class="ibox-content">
								<?php
								echo show_flash_msg();
								?>
									<?php if (isset($_GET['id'])) : ?>
										<?php
										$class_id = $_GET['id'];
										$class = $db->prepare('SELECT * FROM chart_class WHERE cid = ?');
										$class->execute([$class_id]);
										$class = $class->fetch();
										?>
										<hr>
										<h3>Editing class '<?= $class['class_name'] ?>'</h3>
										<form action="" method="post">
											<div class="form-group">
												<label for="name">Class Name</label>
												<input type="hidden" name="class_id" value="<?= $class_id ?>" required>
												<input type="text" name="class_name" class="form-control" id="name" value="<?= $class['class_name'] ?>" required>
											</div>
											<button type="submit" class="btn btn-success">Update Class</button>
										</form>
										<hr>
									<?php endif ?>

									<div class="table-responsive" id="chart_groups">
										<table class="table">
											<thead>
												<th>S/N</th>
												<th>Class Name</th>
												<th>Action</th>
											</thead>
											<tbody>
												<?php $i=1; foreach ($classes as $class) { ?>
													<tr>
														<td><?= $i++; ?></td>
														<td><?php echo $class['class_name']; ?></td>
														<td><a href="?id=<?php echo $class['cid']; ?>" class="btn btn-primary"><i class="fa fa-edit"></i></a></td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
									
								<br>
								<br>
								
<button class="btn btn-primary" onclick="printDiv('chart_groups')"><i class="fa fa-print"></i>&nbsp; Print Report</button>
								
								
								
								
							</div>
						</div>
					</div>
				</div>


				<?php include '../../inc/footer.php'; ?>

			</div>
		</div>


		<?php 		 include('../modal_lock.php'); ?>
		<?php include 'inc/footer_scripts.php'; ?>


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
		<script>
			function printDiv(divId) {
				var content = document.getElementById(divId).innerHTML;
				var popupWindow = window.open('', '_blank', 'width=600,height=600');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>' + document.title + '</title>');
				// Reference the external stylesheet from the main page
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				popupWindow.print();
			}
		</script>

<script src="../js/idle.js"></script>
</body>

</html>