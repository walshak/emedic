<?php include("../Connections/Conn.php");?>

<?php
session_start();
include('../inc/header.php');


if (isset($_POST['save_pay_mode_gl_settings_btn'])) {
	$modes = $_POST['pay_method'];
	$gls = $_POST['acc_to_dr'];

	$err_count = 0;
	$db->beginTransaction();
	for ($i = 0; $i < count($modes); $i++) {
		$check_cur_set = $db->prepare("SELECT id FROM chart_payment_method_gl_settings WHERE payment_method = ?");
		$check_cur_set->execute([$modes[$i]]);
		$gl = $gls[$i];
		$mode = $modes[$i];
		if ($check_cur_set->rowCount() == 0) {
			$set_val = $db->prepare("INSERT INTO chart_payment_method_gl_settings(payment_method, gl_account) VALUES(?,?)");
			$set_val = $set_val->execute([$mode, $gl]);
			if ($set_val) {
				continue;
			} else {
				$err_count++;
				continue;
			}
		} else {
			$update_val = $db->prepare("UPDATE chart_payment_method_gl_settings SET gl_account = ? WHERE payment_method = ?");
			$update_val = $update_val->execute([$gl, $mode]);
			if ($update_val) {
				//success message
				continue;
			} else {
				$err_count++;
				continue;
			}
		}
	}
	if ($err_count == 0) {
		try {
			$db->commit();
			echo "Success";
		} catch (\Exception $th) {
			echo $th->getMessage();
		}
	} else {
		$db->rollBack();
		echo "some errors occured, no changes were made";
	}
}

if (isset($_POST['save_hmo_gl_settings_btn'])) {
	$hmos = $_POST['hmo'];
	$gls = $_POST['acc_to_dr'];

	$err_count = 0;
	$db->beginTransaction();
	for ($i = 0; $i < count($hmos); $i++) {
		$check_cur_set = $db->prepare("SELECT id FROM hmo_gl_settings WHERE hmo = ?");
		$check_cur_set->execute([$hmos[$i]]);
		$gl = $gls[$i];
		$hmo = $hmos[$i];
		if ($check_cur_set->rowCount() == 0) {
			$set_val = $db->prepare("INSERT INTO hmo_gl_settings(hmo, gl_account) VALUES(?,?)");
			$set_val = $set_val->execute([$hmo, $gl]);
			if ($set_val) {
				continue;
			} else {
				$err_count++;
				continue;
			}
		} else {
			$update_val = $db->prepare("UPDATE hmo_gl_settings SET gl_account = ? WHERE hmo = ?");
			$update_val = $update_val->execute([$gl, $hmo]);
			if ($update_val) {
				//success message
				continue;
			} else {
				$err_count++;
				continue;
			}
		}
	}
	if ($err_count == 0) {
		try {
			$db->commit();
			echo "Success";
		} catch (\Exception $th) {
			echo $th->getMessage();
		}
	} else {
		$db->rollBack();
		echo "some errors occured, no changes were made";
	}
}

if (isset($_POST['save_gl_settings_btn'])) {
	$cats = $_POST['trans_category'];
	$gls = $_POST['acc_to_dr'];

	$err_count = 0;
	$db->beginTransaction();
	for ($i = 0; $i < count($cats); $i++) {
		$check_cur_set = $db->prepare("SELECT id FROM chart_service_cat_gl_settings WHERE service_cat = ?");
		$check_cur_set->execute([$cats[$i]]);
		$gl = $gls[$i];
		$cat = $cats[$i];
		if ($check_cur_set->rowCount() == 0) {
			$set_val = $db->prepare("INSERT INTO chart_service_cat_gl_settings(service_cat, gl_account) VALUES(?,?)");
			$set_val = $set_val->execute([$cat, $gl]);
			if ($set_val) {
				continue;
			} else {
				$err_count++;
				continue;
			}
		} else {
			$update_val = $db->prepare("UPDATE chart_service_cat_gl_settings SET gl_account = ? WHERE service_cat = ?");
			$update_val = $update_val->execute([$gl, $cat]);
			if ($update_val) {
				//success message
				continue;
			} else {
				$err_count++;
				continue;
			}
		}
	}
	if ($err_count == 0) {
		try {
			$db->commit();
			echo "Success";
		} catch (\Exception $th) {
			echo $th->getMessage();
		}
	} else {
		$db->rollBack();
		echo "some errors occured, no changes were made";
	}
}
?>

<body class="fixed-navigation">
	<div id="wrapper">
<?php include("nav_side.php"); ?>


		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>

			<div class="row">
				<div class="col-lg-12">
					<div class="ibox float-e-margins">
						<div class="ibox-title">

							<h2>Accounting Dashboard</h2>
						</div>
						<div class="ibox-content">
							<div class="row">
								<div class="col-lg-12">
									
									
									<form method="POST" action="report_gl_settings.php">
												<h5>Report GL Settings | <small>Here you can associate each service category to a GL account.</small></h5>


											<div class="ibox-content">
												<div class="row">
													<div class="col-sm-12">
												
		<?php
		$query_rstSelect = $db->query("SELECT DISTINCT cat_type FROM patient_ap_services_category 
			where cat_type!='' order by cat_type");
		$query_rstSelect->execute();
		$all_cat = $query_rstSelect->fetchAll(PDO::FETCH_ASSOC);



		$chart_items = $db->query("SELECT chart_accounts.*, chart_groups.name, chart_class.class_name FROM chart_accounts 
			LEFT JOIN chart_groups ON chart_groups.id = chart_accounts.account_group
			LEFT JOIN chart_class ON chart_class.cid = chart_groups.class_id
			WHERE chart_accounts.inactive = 0 ORDER BY chart_class.cid DESC");

		$chart_items = $chart_items->fetchAll(PDO::FETCH_BOTH);

		?>
														<table class="table">
															<thead>
																<th>Service Category</th>
																<th>Associated Gl Account</th>
															</thead>
															<?php for ($k = 0; $k < count($all_cat); $k++) : ?>
																<tr>
																	<th>
																		<?= $all_cat[$k]['cat_type'] ?>
																		<input type="hidden" name="trans_category[]" value="<?= $all_cat[$k]['cat_type'] ?>">
																		<?php
																		$cur_set = $db->prepare("SELECT gl_account FROM chart_service_cat_gl_settings where service_cat = ?");
																		$cur_set->execute([$all_cat[$k]['cat_type']]);
																		$cur_set = $cur_set->fetch(PDO::FETCH_NUM)[0];
																		?>
																	</th>
																	<td>
																		<select name="acc_to_dr[]" id="" class="form-control select2">
																			<option value="">--select GL--</option>
																			<?php foreach ($chart_items as $chart_item) : ?>
																				<option value="<?= $chart_item['account_code'] ?>" <?= (($cur_set && $cur_set == $chart_item['account_code']) ? 'selected' : '') ?>>
																					<b><?= $chart_item['class_name'] ?></b> =>
																					<?= $chart_item['name'] ?><br> =>
																					<?= $chart_item['account_name'] ?> [<?= $chart_item['account_code'] ?>]
																				</option>
																			<?php endforeach ?>
																		</select>
																	</td>
																</tr>
															<?php endfor ?>
														</table>
													</div>
												</div>

												<div class="form_sep">
													<button class="btn btn-primary btn" type="submit" name="save_gl_settings_btn">Apply</button>
													<div class="pull-right">
														<a href="report_gl_settings.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh </a>
													</div>
												</div>

											</div>

									</form>
								</div>

								<hr>
								<div class="row">
									<div class="col-lg-12">
										<form method="POST" action="report_gl_settings.php">
											<div class="ibox float-e-margins">
												<div class="ibox-title">
													<h5>Payment method GL Settings | <small>Here you can associate each payment method to a GL account.</small></h5>

												</div>

												<div class="ibox-content">
													<div class="row">
														<div class="col-sm-12">
															<h3 class="m-t-none m-b"></h3>
															<?php
															$query_rstSelect = $db->query("SELECT pay_mode
																FROM billing_payment_mode WHERE 1");
															$query_rstSelect->execute();
															$all_modes = $query_rstSelect->fetchAll(PDO::FETCH_ASSOC);
															?>
															<table class="table">
																<thead>
																	<th>Payment Method</th>
																	<th>Associated Gl Account</th>
																</thead>
																<?php for ($o = 0; $o < count($all_modes); $o++) : ?>
																	<tr>
																		<th>
																			<?= $all_modes[$o]['pay_mode'] ?>
																			<input type="hidden" name="pay_method[]" value="<?= $all_modes[$o]['pay_mode'] ?>">
																			<?php
																			$cur_set = $db->prepare("SELECT gl_account FROM chart_payment_method_gl_settings where payment_method = ?");
																			$cur_set->execute([$all_modes[$o]['pay_mode']]);
																			$cur_set = $cur_set->fetch(PDO::FETCH_NUM)[0];
																			?>
																		</th>
																		<td>
																			<select name="acc_to_dr[]" id="" class="form-control select2">
																				<option value="">--select GL--</option>
																				<?php foreach ($chart_items as $chart_item) : ?>
																					<option value="<?= $chart_item['account_code'] ?>" <?= (($cur_set && $cur_set == $chart_item['account_code']) ? 'selected' : '') ?>>
																						<b><?= $chart_item['class_name'] ?></b> =>
																						<?= $chart_item['name'] ?><br> =>
																						<?= $chart_item['account_name'] ?> [<?= $chart_item['account_code'] ?>]
																					</option>
																				<?php endforeach ?>
																			</select>
																		</td>
																	</tr>
																<?php endfor ?>
															</table>
														</div>
													</div>

													<div class="form_sep">
														<button class="btn btn-primary btn" type="submit" name="save_pay_mode_gl_settings_btn">Apply</button>
														<div class="pull-right">
															<a href="report_gl_settings.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh </a>
														</div>
													</div>

												</div>

											</div>
										</form>
									</div>
								</div>
								<hr>
								<div class="row">
									<div class="col-lg-12">
										<form method="POST" action="report_gl_settings.php">
											<div class="ibox float-e-margins">
												<div class="ibox-title">
													<h5>HMO GL Settings | <small>Here you can associate each HMO to a GL account.</small></h5>

												</div>

												<div class="ibox-content">
													<div class="row">
														<div class="col-sm-12">
															<h3 class="m-t-none m-b"></h3>
															<?php
															$query_rstSelect = $db->prepare("SELECT 
																CONCAT(insurance_tbl.insurance_type, '_', insurance_tbl.insurance_name) AS hmo_
																FROM 
																	patient_ap_services 
																LEFT JOIN 
																	enrollee ON patient_ap_services.hospital_no = enrollee.hospital_no
																LEFT JOIN 
																	insurance_tbl ON enrollee.hmo_no = insurance_tbl.insurance_no
																WHERE 
																	insurance_tbl.status = 'active'
																GROUP BY 
																	insurance_tbl.insurance_type, insurance_tbl.insurance_name;
															");

															// $query_rstSelect = $db->query("SELECT DISTINCT CONCAT(insurance_type,'_',insurance_name) as hmo_
															// FROM insurance_tbl WHERE status = 'active'");
															$query_rstSelect->execute();
															$all_hmos = $query_rstSelect->fetchAll(PDO::FETCH_ASSOC);

															?>
															<table class="table">
																<thead>
																	<th>HMO</th>
																	<th>Associated Gl Account</th>
																</thead>
																<?php for ($o = 0; $o < count($all_hmos); $o++) : ?>
																	<tr>
																		<th>
																			<?= $all_hmos[$o]['hmo_'] ?>
																			<input type="hidden" name="hmo[]" value="<?= $all_hmos[$o]['hmo_'] ?>">
																			<?php
																			$cur_set = $db->prepare("SELECT gl_account FROM chart_hmo_gl_settings where hmo = ?");
																			$cur_set->execute([$all_hmos[$o]['hmo_']]);
																			$cur_set = $cur_set->fetch(PDO::FETCH_NUM)[0];
																			?>
																		</th>
																		<td>
																			<select name="acc_to_dr[]" id="" class="form-control select2">
																				<option value="">--select GL--</option>
																				<?php foreach ($chart_items as $chart_item) : ?>
																					<option value="<?= $chart_item['account_code'] ?>" <?= (($cur_set && $cur_set == $chart_item['account_code']) ? 'selected' : '') ?>>
																						<b><?= $chart_item['class_name'] ?></b> =>
																						<?= $chart_item['name'] ?><br> =>
																						<?= $chart_item['account_name'] ?> [<?= $chart_item['account_code'] ?>]
																					</option>
																				<?php endforeach ?>
																			</select>
																		</td>
																	</tr>
																<?php endfor ?>
															</table>
														</div>
													</div>

													<div class="form_sep">
														<button class="btn btn-primary btn" type="submit" name="save_hmo_gl_settings_btn">Apply</button>
														<div class="pull-right">
															<a href="report_gl_settings.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh </a>
														</div>
													</div>

												</div>

											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include '../../inc/footer.php'; ?>

		</div>


		<?php 		 include('../modal_lock.php'); ?>
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
		<script>
			$(document).ready(function() {
				$('.select2').select2();
			});
		</script>

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

<script src="../js/idle.js"></script>
</body>

</html>