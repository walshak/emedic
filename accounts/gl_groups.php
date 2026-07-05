<?php include("../Connections/Conn.php");?>

<?php
session_start();
include('../inc/header.php');

include('inc/functions.php');

$classes = $db->query("SELECT * FROM chart_class");

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['group_name'])){
	if($_POST['group_name'] != '' && $_POST['group_class'] != ''){
		try {
			$db->beginTransaction();
			$group_name = $_POST['group_name'];
			$class_id = $_POST['group_class'];
            $group_id = $_POST['group_id'];

			$update = $db->prepare("UPDATE chart_groups SET name = ? , class_id = ? WHERE id = ?");
			$update = $update->execute([$group_name, $class_id, $group_id]);
			$db->commit();
			set_flash_message('Group updated successfully', 'success');
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Faled to update group','danger');
			// header('Location:?');
		}

	}
}

if(isset($_POST['group_name_add'])){
	if($_POST['group_name_add'] != '' && $_POST['group_class_add'] != ''){
		try {
			$db->beginTransaction();
			$group_name = $_POST['group_name_add'];
			$class_id = $_POST['group_class_add'];

			$add = $db->prepare("INSERT INTO chart_groups(name, class_id) VALUES(?, ?)");
			$add = $add->execute([$group_name, $class_id]);
			$db->commit();
			set_flash_message('Group added successfully', 'success');
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Faled to create group','danger');
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

                                <h5>Accounts Dashboard - Chart Groups</h5>
								
                            </div>
                            <div class="ibox-content">
								<a href="?add" class="btn btn-warning"><i class="fa fa-plus"></i>&nbsp; ADD A NEW CHART GROUP</a> &nbsp; : &nbsp;
								<a href="gl_groups.php" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;REFRESH</a>&nbsp; : &nbsp;
								<a href="index.php" class="btn btn-danger" style="color: black; "><i class="fa fa-close"></i>&nbsp;CLOSE</a>
								
								<hr>
								
                                <?php 
                                    echo show_flash_msg();
                                    // $_SESSION['flash_msg'] = null;
									// $_SESSION['flash_msg_type'] = null;
								?>
                                    <?php if (isset($_GET['id'])) : ?>
										<?php
										$group_id = $_GET['id'];
										$group = $db->prepare('SELECT * FROM chart_groups WHERE id = ? ');
										$group->execute([$group_id]);
										$group = $group->fetch();
                                        $classes = $db->prepare('SELECT * FROM chart_class');
                                        $classes->execute();
                                        $classes = $classes->fetchAll();
										?>

								
										<h3>Editing group '<?=$group['name']?>'</h3>
										<form action="" method="post">
                                            <div class="form-group">
												<label for="name">Group Class</label>
												
												<select name="group_class" class="form-control">
                                                    <?php foreach($classes as $class):?>
                                                        <option value="<?=$class['cid']?>" <?=(($class['cid'] == $group['class_id'])? 'selected' : '')?>><?=$class['class_name']?></option>
                                                    <?php endforeach ?>
                                                </select>
											</div>
											<div class="form-group">
												<label for="name">Group Name</label>
												<input type="hidden" name="group_id"  value="<?=$group_id?>" required>
												<input type="text" name="group_name" class="form-control" id="name" value="<?=$group['name']?>" required>
											</div>
											<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you wish to update this GL group?')">Update Group</button>
										</form>
										<hr>
									<?php endif ?>

								
								
								
								
                                    <?php if (isset($_GET['add'])) : ?>
										<hr>
										<h3>New Group </h3>
										<form action="" method="post">
                                            <div class="form-group">
												<label for="name">Group Class</label>
												
												<select name="group_class_add" class="form-control">
                                                    <?php foreach($classes as $class):?>
                                                        <option value="<?=$class['cid']?>"><?=$class['class_name']?></option>
                                                    <?php endforeach ?>
                                                </select>
											</div>
											<div class="form-group">
												<label for="name">Group Name</label>
												<input type="text" name="group_name_add" class="form-control" id="name_add" value="<?=$group['name']?>" required>
											</div>
											<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you wish to add this GL group?')">Create Group</button>
										</form>
										<hr>
									<?php endif ?>
                                    <div class="table-responsive" id="chart_groups">
                                        <table class="table">
                                            <thead>
                                                <th>S/N</th>
                                                <th>Group Name</th>
                                                <th>Action</th>
                                            </thead>
                                            <tbody>
                                                <?php $i=1; foreach ($classes as $class) : ?>
                                                    <tr>
                                                        <th colspan="3"><?= $class['class_name'] ?></th>
                                                        <?php
                                                        $cid = $class['cid'];
                                                        $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
                                                        $groups->execute([$cid]);
                                                        $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                        ?>
                                                        <?php foreach ($groups as $group) : ?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $group['name'] ?></td>
                                                        <td><a href="?id=<?= $group['id'] ?>" class="btn btn-primary"><i class="fa fa-edit"></i></a></td>
                                                    </tr>
                                                <?php endforeach ?>
                                                </tr>
                                            <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
						
						
						
<button class="btn btn-primary" onclick="printDiv('chart_groups')"><i class="fa fa-print"></i>&nbsp; Print Report</button>						
                            </div>
                        </div>
                    </div>
                </div>


                <?php include '../../inc/footer.php'; ?>

            </div>
        </div>


        <?php  include('../modal_lock.php'); ?>
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
                setTimeout(function() {
                    popupWindow.focus();
                    popupWindow.print();
                }, 1000);
            }
        </script>

<script src="../js/idle.js"></script>
</body>

</html>