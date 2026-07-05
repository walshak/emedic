<?php include("../Connections/Conn.php");?>

<?php
session_start();
include('../inc/header.php');

include('inc/functions.php');


if(isset($_GET['delete'])){
	
	$del_account = $_GET['delete'];
	$stmt =$db->prepare("SELECT * FROM chart_ledger WHERE account_no=:account_no");
		$stmt->bindValue(':account_no', $del_account, PDO::PARAM_STR);
		$stmt->execute();	
	if($stmt->rowCount()==0){
			 
		$updateSQL = "DELETE FROM chart_accounts WHERE account_code=:account_code";
		$stmt_22 = $db->prepare($updateSQL);
		$stmt_22->bindParam(':account_code', $del_account, PDO::PARAM_STR);
		$stmt_22->execute();
			$msg ="<h2 style='color:blue;'>Deleted</h2>";	
	}else{
		$msg ="<h2 style='color:red;'>Unable to Delete</h2>";
	}	
}

$classes = $db->query('SELECT * FROM chart_class');

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['account_name'])){
	if($_POST['account_name'] != '' && $_POST['account_group'] != ''){
		try {
			$db->beginTransaction();
			$account_name = $_POST['account_name'];
			$account_id = $_POST['account_id'];
            $group_id = $_POST['account_group'];

			$update = $db->prepare("UPDATE chart_accounts SET account_name = ? , account_group = ? WHERE account_code = ?");
			$update = $update->execute([$account_name, $group_id, $account_id]);
			$db->commit();
			set_flash_message('Account updated successfully', 'success');
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Faled to update account','danger');
			// header('Location:?');
		}

	}
}

if(isset($_POST['account_name_add'])){
	if(($_POST['account_name_add'] != '') && ($_POST['account_group_add'] != '') && ($_POST['account_code_add'] != '')){
		try {
			$db->beginTransaction();
			$account_name = $_POST['account_name_add'];
            $account_code = $_POST['account_code_add'];
            $group_id = $_POST['account_group_add'];

            $check_code = $db->prepare("SELECT account_code FROM chart_accounts WHERE account_code = ?");
            $check_code->execute([$account_code]);
            $check_code = $check_code->fetchAll();
            // print_r($check_code);
            // die();

            if(count($check_code) == 0){
                $add = $db->prepare("INSERT INTO chart_accounts(account_name , account_group, account_code) VALUES(?, ?, ?)");
                $add = $add->execute([$account_name, $group_id, $account_code]);
                $db->commit();
                set_flash_message('Account added successfully', 'success');
            }else{
                set_flash_message('Failed to update account, Specified account code already exists','danger');
            }
			// header('Location:?');
		} catch (\Exception $e) {
			error_log($e->getMessage());
			$db->rollBack();
			set_flash_message('Failed to update account','danger');
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
                                <h5>Accounts Dashboard | Chart Accounts</h5> 
                            </div>
                            <div class="ibox-content">
								
								<a href="?add" class="btn btn-warning"><i class="fa fa-plus"></i>&nbsp;ADD ACCOUNT</a>	&nbsp; : &nbsp;
								<a href="gl_accounts.php" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;REFRESH</a>&nbsp; : &nbsp;
								<a href="index.php" class="btn btn-danger" style="color: black; "><i class="fa fa-close"></i>&nbsp;CLOSE</a>
								<br>
								<br>
								
								<?= $msg; ?>
								
                                <?php 
                                    echo show_flash_msg();
                                        // $_SESSION['flash_msg'] = null;
                                        // $_SESSION['flash_msg_type'] = null;
								?>
                                    <?php if (isset($_GET['id'])) { ?>
										<?php
                                        $account_id = $_GET['id'];
                                        $groups = $db->prepare('SELECT * FROM chart_groups');
                                        $groups->execute();
                                        $groups = $groups->fetchAll();

                                        $account = $db->prepare('SELECT * FROM chart_accounts WHERE account_code = ?');
                                        $account->execute([$account_id]);
                                        $account = $account->fetch();
                                        ?>
								
										<h3>Editing Account '<?php echo $account['account_name']; ?>'</h3>
										<form action="" method="post">
                                            <div class="form-group">
												<label for="name">Account Group</label>
												
												<select name="account_group" class="form-control">
                                                    <?php foreach ($groups as $group) { ?>
                                                        <option value="<?php echo $group['id']; ?>" <?php echo ($group['id'] == $account['account_group']) ? 'selected' : ''; ?>><?php echo $group['name']; ?></option>
                                                    <?php } ?>
                                                </select>
											</div>
											<div class="form-group">
												<label for="name">Account Name</label>
												<input type="hidden" name="account_id"  value="<?php echo $account_id; ?>" required>
												<input type="text" name="account_name" class="form-control" id="name" value="<?php echo $account['account_name']; ?>" required>
											</div>
											<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you wish to update this GL account?')">Update Account</button>
										</form>
										<hr>
									<?php } ?>

								
								
                                    <?php if (isset($_GET['add'])) { ?>
                                        <?php 
                                            $groups = $db->prepare('SELECT * FROM chart_groups');
                                            $groups->execute();
                                            $groups = $groups->fetchAll();
                                        ?>
								
								
										<h3>Add Account</h3>
										<form action="" method="post">
                                            <div class="form-group">
												<label for="name">Account Group</label>
												
												<select name="account_group_add" class="form-control">
                                                    <?php foreach ($groups as $group) { ?>
                                                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                                                    <?php } ?>
                                                </select>
											</div>
                                            <div class="form-group">
												<label for="code">Account Code</label>
												<input type="text" name="account_code_add" class="form-control" id="code" required>
											</div>
											<div class="form-group">
												<label for="name">Account Name</label>
												<input type="text" name="account_name_add" class="form-control" id="name" required>
											</div>
											<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you wish add this GL account?')">Create Account</button>
										</form>
										<hr>
									<?php } ?>
								
								
								
								
                                    <div class="table-responsive" id="chart_groups">
                                        <table class="table" style="font-size: 18px; ">
                                            <thead>
                                                <th>S/N</th>
                                                <th>Account Code</th>
                                                <th>Account Name</th>
                                                <th>Action</th>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($classes as $class) { ?>
                                                    <tr>
                                                        <th colspan="4"><h2><strong><i><?php echo strtoupper($class['class_name']); ?></i></strong></h2></th>
                                                        <?php
                                                        $cid = $class['cid'];
                                                    $groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ?');
                                                    $groups->execute([$cid]);
                                                    $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                        <?php $i=1; foreach ($groups as $group) { ?>
                                                    <tr>
                                                        <td colspan="4"><strong><?php echo $group['name']; ?></strong></td>
                                                        <?php
                                                        $group_id = $group['id'];

                                                            $accounts = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ?');
                                                            $accounts->execute([$group_id]);
                                                            $accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
                                                            ?>
                                                        <?php foreach ($accounts as $acc) { ?>
                                                            <tr>
                                                                <td><?= $i++; ?></td>
                                                                <td><?php echo $acc['account_code']; ?></td>
                                                                <td><?php echo $acc['account_name']; ?></td>
                                                                <td>
													<?php if($acc['account_code2'] != '2'){?>				
																	<a href="?id=<?php echo $acc['account_code']; ?>" class="btn btn-primary"><i class="fa fa-edit"></i></a>
													<?php }?>
													<?php if($acc['account_code2'] != '1' and $acc['account_code2'] != '2'){?>				
																<a href="?delete=<?php echo $acc['account_code']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you wish DELETE this GL account?')"><i class="fa fa-trash"></i></a>
															<?php }?>		
																</td>
                                                            </tr>
                                                        <?php } ?>
                                                </tr>
                                            <?php } ?>
                                            </tr>
                                        <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
					
<button class="btn btn-primary" onclick="printDiv('chart_groups')"><i class="fa fa-print"></i>&nbsp;Print Report</button>					
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
                popupWindow.print();
            }
        </script>
<script src="../js/idle.js"></script>
</body>

</html>