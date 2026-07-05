<?php

function formatQuantity($qty, $units)
{
	if ($units > 1) {
		$pack = floor($qty / $units);
		$pcs  = $qty % $units;

		$parts = [];
		if ($pack > 0) $parts[] = "$pack (pck)";
		if ($pcs > 0)  $parts[] = "$pcs (pcs)";

		return implode(' & ', $parts);
	} else {
		return $qty . ' (pcs)';
	}
}

include('stock_mgr_process.php');

if ($_SESSION['navigate'] == 'nursing' or $_SESSION['navigate'] == 'Nursing') {

	$search = "and stock_table='Store'";
	$search_part = " stock_table='Store'";
	$procur_search = " and p.navigation='nursing'";
	$stock = 'o';
	$stock_ = 'o';
	$search_plus = " and (navigation='nursing' or category='Nursing Consumables')";
	$search_plus_where = " WHERE (navigation='nursing' or category='Nursing Consumables')";
	$stock_table = "Store";
} elseif ($_SESSION['navigate'] == 'pharmacy' or $_SESSION['navigate'] == 'Pharmacy') {
	$search = "and stock_table='Pharmacy'";
	$search_part = " stock_table='Pharmacy'";
	$procur_search = " and p.navigation='pharmacy'";
	$stock = 'p';
	$stock_ = 'p';
	$search_plus = "";
	$search_plus_where = "";
	$stock_table = "Pharmacy";
} else {
	if ($_SESSION['navigate'] == 'investigations') {
		$search_plus = " and (navigation='investigation' OR navigation='store')";
		$search_plus_where = " WHERE (navigation='investigation' OR navigation='store')";
		$procur_search = " and (p.navigation='investigation' OR p.navigation='store')";
	} elseif ($_SESSION['navigate'] == 'nursing') {
		$search_plus = " and navigation='nursing'";
		$search_plus_where = " WHERE navigation='nursing'";
		$procur_search = " and p.navigation='nursing'";
	} else {
		$search_plus = "";
		$search_plus_where = "";
		$procur_search = " and p.navigation='Store'";
	}

	$stock = $_GET['stock'];

	if ($stock == 'p' and $_SESSION['stock_mgr_pharm'] == 1) {
		$search = " and stock_table='Pharmacy'";
		$search_part = " stock_table='Pharmacy'";
		$stock = 'p';
		$stock_ = 'p';
		$stock_table = "Pharmacy";
	} elseif ($stock == 'o') {
		$search = " and stock_table='Store'";
		$search_part = " stock_table='Store'";
		$stock = 'o';
		$stock_ = 'o';
		$stock_table = "Store";
	} elseif ($stock == '' && $_SESSION['stock_mgr'] == 1) {
		$search = " and stock_table='Store'";
		$search_part = " stock_table='Store'";
		$stock = 'o';
		$stock_ = 'o';
		$stock_table = "Store";
	}
}

$navigate = strtolower($_SESSION['navigate']);

if (isset($_GET['stock'])) {
	$stock = $_GET['stock'];
	if ($stock == 'p') {
		$stock = 'p';
		$stock_ = 'p';
	} elseif ($stock == 'o') {
		$stock = 'o';
		$stock_ = 'o';
	} elseif ($stock == 'rpt') {
		if ($navigate == 'admin') {
			$stock_ = 'o';
		} else {
			$stock_ = 'p';
		}
	} elseif ($stock == '') {
		if ($navigate == 'admin') {
			$stock_ = 'o';
		} elseif ($navigate == 'pharmacy') {
			$stock_ = 'p';
		}
	}
}

if (isset($_POST['show_dept_store'])) {
	$dept_id_inv =	$_POST['store_department'];
} else {
	$dept_id_inv =	$_SESSION['dept_id'];
}

$dept_incharge_stock = null;
if ($_SESSION['stock_mgr_pharm'] == 1 && $_SESSION['stock_mgr'] == 0) {
	$dept_id = $_SESSION['dept_id'];
	$dept_incharge_stock = " and dept_incharge_stock='$dept_id'";
	$stock_table = "pharmacy";
}

if ($stock == '') { ?>

	<div class="row">
		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Stock Manager Dashboard</h5>
				</div>


				<?php
				$setdate = date("Y-m-d");
				$back_yr = date('Y-m-d', strtotime('-1 year'));
				$back_6m = date('Y-m-d', strtotime('+6 months'));
				$back_3m = date('Y-m-d', strtotime('+3 months'));

				$exp_today = $db->query("SELECT sn FROM stock_table WHERE date(expire_date) <='$setdate' and date(expire_date) != '0000-00-00' and status='active' $search $search_plus"); ?>
				<?php $exp_3m = $db->query("SELECT sn FROM stock_table WHERE DATE(expire_date) BETWEEN '$setdate' AND '$back_3m' and date(expire_date) != '0000-00-00' and status='active' $search $search_plus"); ?>
				<?php $exp_6m = $db->query("SELECT sn FROM stock_table WHERE date(expire_date) BETWEEN '$back_3m' AND '$back_6m' and date(expire_date) != '0000-00-00' and status='active' $search $search_plus"); ?>
				<?php $exp_invalid = $db->query("SELECT sn FROM stock_table WHERE (expire_date = '0000-00-00' OR expire_date IS NULL) and status='active' $search $search_plus"); ?>
				<?php $odr_today = $db->query("SELECT sn FROM stock_table WHERE status='active' and qty<=reorder_level $search $search_plus"); ?>
				<?php $rq_request = $db->query("SELECT sn FROM stock_table_request WHERE status='pending' and seen=1 $search $search_plus $dept_incharge_stock"); ?>
				<?php $total_stock = $db->query("SELECT sn FROM stock_table WHERE status='active' $search $search_plus"); ?>
				<?php $runing_stock = $db->query("SELECT sum(qty) as runing_stock FROM stock_table WHERE status='active' $search $search_plus");
				$rw = $runing_stock->fetch(PDO::FETCH_ASSOC);
				$runing_stock = $rw['runing_stock'];	?>

				<?php $Deleted_stock = $db->query("SELECT sn FROM stock_table WHERE status!='active' $search $search_plus"); ?>
				<?php $dormant = $db->query("SELECT sn FROM stock_table WHERE status='active' and date_last_update<='$back_yr' $search $search_plus"); ?>
				<?php
				if ($_SESSION['navigate'] != 'nursing') {
					$approval_procure = $db->query("SELECT sn FROM stock_table_procurment where pay_status=1 and status='yes' $search $search_plus");
				}
				?>

				<div class="ibox-content" align="center">
					<input type="button" name="cat" value="Add / Edit Suppliers / Companies " data-target="#myModal5" id="<?php echo '1'; ?>" class="btn btn-primary add_company" />
					&nbsp; : &nbsp;
					<input type="button" name="cat" value="Create Stock Combo" data-target="#myModal5" id="<?php echo '1'; ?>" class="btn btn-success create_stock_combo" />
					<hr>

					<H2>INVENTORY/COUNTER</H2>
					<?php if ($odr_today->rowCount() > 0) {
						$color = 'red';
					} else {
						$color = 'black';
					} ?>

					<a href="index.php?stock=<?= $stock_; ?>&total" class="btn btn-app" style="background-color: aquamarine;"><i class=""></i><strong style="font-size:25px;"><?php echo $total_stock->rowCount(); ?></strong><br>Total Stocks</a>
					<a href="index.php?stock=<?= $stock_; ?>&dormant" class="btn btn-app"><i class=""></i><strong style="font-size:25px;"><?php echo $dormant->rowCount(); ?></strong><br>Dormant Stocks</a>
					<a href="index.php?stock=<?= $stock_; ?>&delete" class="btn btn-app" style="background-color:antiquewhite;"><i class=""></i><strong style="font-size:25px;"><?php echo $Deleted_stock->rowCount(); ?></strong><br>Deleted Stocks</a>
					<a href="" class="btn btn-app"><i class=""></i><strong style="font-size:25px;"><?php echo $runing_stock; ?></strong><br>Total Stocks Qty</a>
					<a href="index.php?stock=<?= $stock_; ?>&reorder&polist_gen" class="btn btn-app"><i class=""></i><strong style="font-size:25px; color:<?= $color; ?>; "><?php echo $odr_today->rowCount(); ?></strong><br>Re/Order Stock(s)</a>
					<a href="index.php?stock=<?= $stock_; ?>&exp_now" class="btn btn-app"><i class=""></i><strong style="font-size:25px;"><?php echo $exp_today->rowCount(); ?></strong><br><?php if ($exp_today->rowCount() > 0) { ?><strong style="color:#F00">Expired Stocks</strong><?php } else { ?>Expired Stocks<?php } ?></a>
					<a href="index.php?stock=<?= $stock_; ?>&exp_invalid" class="btn btn-app"><i class=""></i><strong style="font-size:25px;"><?php echo $exp_invalid->rowCount(); ?></strong><br><?php if ($exp_invalid->rowCount() > 0) { ?><strong style="color:#F00">Invalid Exp. Date</strong><?php } else { ?>Invalid Exp. Date<?php } ?></a>
					<a href="index.php?stock=<?= $stock_; ?>&exp_3m" class="btn btn-app"><i class=""></i><strong style="font-size:25px;"><?php echo $exp_3m->rowCount(); ?></strong><br><?php if ($exp_3m->rowCount() > 0) { ?><strong style="color:#F00">Expires in 3 months</strong><?php } else { ?>Expires in 3 months<?php } ?></a>
					<a href="index.php?stock=<?= $stock_; ?>&exp_6m" class="btn btn-app"><i class=""></i><strong style="font-size:25px;"><?php echo $exp_6m->rowCount(); ?></strong><br><?php if ($exp_6m->rowCount() > 0) { ?><strong style="color:#F00">Expires in 6 months</strong><?php } else { ?>Expires in 6 months<?php } ?></a>

				</div>


				<div class="ibox-content" align="center">

					<H2>OPERATIONS</H2>
					<?php

					if ($_SESSION['stock_mgr_pharm'] == '1') { ?>
						<a href="index.php?stock=p" class="btn btn-app"><i class="fa fa-eraser"></i>Pharmacy</a>
					<?php } ?>

					<?php if ($_SESSION['stock_mgr'] == '1') {
					?>
						<a href="index.php?stock=o" class="btn btn-app"><i class="fa fa-tasks"></i>General Store</a>
					<?php } ?>

					<?php if ($_SESSION['stock_mgr_pharm'] == '1' or $_SESSION['stock_mgr'] == '1') { ?>
						<a href="index.php?stock=<?= $stock_; ?>&pdr" class="btn btn-app"><i class="fa fa-tasks"></i>Purchase Order List</a>
					<?php } ?>

					<?php if ($_SESSION['procure'] == '1' or ($_SESSION['navigate'] == 'investigations' and $_SESSION['unit_head'] == '1')) { ?>
						<a href="index.php?stock=<?= $stock_; ?>&pcr" class="btn btn-app"><i class="fa fa-tasks"></i>Approval Entry (<?php echo $approval_procure->rowCount(); ?>)</a>
					<?php } ?>


					<?php if ($_SESSION['rq_approval'] == '1' or $_SESSION['b4_rq_approval'] == '1') { ?>
						<a href="index.php?stock=<?= $stock_; ?>&rrq" class="btn btn-app"><i class="fa fa-exchange"></i>Requisition RQ (<strong><?php echo $rq_request->rowCount(); ?></strong>)</a>
					<?php }



					if ($_SESSION['unit_head'] == '1') {
					?>

						<hr>


						<strong>View Report: Purchase Orders : Department Requests : Inventory Reports : Reverse Stocks</strong><br>
						<a href="index.php?stock=rpt" class="btn btn-app"><i class="fa fa-envelope"></i>All Reports</a>

						<?php if ($_SESSION['stock_mgr'] == '1' or $_SESSION['stock_mgr_pharm'] == '1') { ?>
							<a href="index.php?updown" class="btn btn-app"><i class="fa fa-tasks"></i><strong style="color: darkblue">Download & Upload</strong> </a>
					<?php }
					} ?>
				</div>
			</div>
		</div>
	</div>



<?php
} else {
?>


	<div class="row">
		<div class="col-lg-12">

			<div class="ibox ">

				<?php if ($_SESSION['stock_mgr'] == '1' ||  $_SESSION['stock_mgr_pharm'] == '1' ||  $_SESSION['rights'] == 'NS') { ?>

					<div class="ibox-title">
						<div class="ibox-tools">


							<div class="pull-left">
								<a href="index.php?stock" class="btn btn-default btn-xs" style="color:white; font-size: 14px; "> Return to Stock Dashboard </a>


								<?php if (!isset($_GET['rrq']) and !isset($_GET['pcr']) and !isset($_GET['pdr']) and $_GET['stock'] != 'rpt') {  ?>
									&nbsp; | &nbsp;
									<input type="button" name="cat" value=" Add Category " data-target="#myModal5" id="<?php echo $navigation; ?>" class="btn btn-success btn-xs add_category" style="color: white; font-size: 14px; " />
									&nbsp; | &nbsp;
									<input type="button" name="edit" value=" Add New Item " data-target="#myModal5" id="<?php echo $roww["sn"]; ?>"
										class="btn btn-primary btn-xs add_stock" style="color: white; font-size: 14px; " />
								<?php } ?>

							</div>
							<div class="pull-right">
							</div>



						</div>
					</div>
				<?php } ?>

				<div class="ibox-title">
					<div class="ibox-tools">

						<?php
						// Get the current URL
						$current_url = $_SERVER['REQUESTstock=o_URI']; ?>

						<?php
						if (isset($_GET['stock']) && in_array($_GET['stock'], ['o', 'p']) && !isset($_GET['pdr']) && !isset($_GET['polist_gen'])) {
						?>
							<a href="<?php echo htmlspecialchars($current_url); ?>"
								class="btn btn-warning btn-xs">
								<strong style="color: red; font-size: 14px;">
									REFRESH TO SEE STORE/PHARM. CURRENT QTY
								</strong>
							</a>&nbsp; | &nbsp;
						<?php
						}
						?>

						<?php if ($_SESSION['rights'] == 'AC') { ?>
							<A href="../accounts/index.php" class="btn btn-danger btn-xs">RETURN TO ACCOUNTS</A>&nbsp; | &nbsp;
						<?php } ?>


						<?php
						if ($_SESSION['stock_mgr'] == '1' or $_SESSION['stock_mgr_pharm'] == '1') { ?>
							<a href="index.php?stock=<?php echo $stock_; ?>&polist_gen" class="btn btn-success btn-xs" style="color: white; font-size: 14px;">Generate New/Edit PO (Step 1 & 2)</a>
							&nbsp; | &nbsp;
							<a href="index.php?stock=<?php echo $stock_; ?>&pdr" class="btn btn-info btn-xs" style="color: white; font-size: 14px;">View Generated PO List (Step 3)</a>
						<?php } ?>

						<?php if ($_SESSION['procure'] == '1' or ($_SESSION['navigate'] == 'investigations' and $_SESSION['unit_head'] == '1')) { ?>
							&nbsp; | &nbsp;
							<a href="index.php?stock=<?php echo $stock_; ?>&pcr" class="btn btn-primary btn-xs" style="color: white; font-size: 14px;">Procurement Entry (Step 4)</a>
						<?php } ?>

					</div>
				</div>

				<div class="ibox-content">

					<?php

					if (isset($_POST['PO_Report'])) {
						include("PO_reports.php");
					} elseif (
						$stock == 'rpt' and !isset($_POST['apply_range']) and !isset($_POST['show_requisition'])
						and !isset($_GET['pdr']) and !isset($_GET['polist_gen'])
					) { ?>

						<div class="row">
							<div class="col-lg-6">

								<form action="index.php?stock=<?php echo $stock_; ?>" method="POST">
									<h2>Departmental/Store Inventory Report</h2>
									<HR>

									<div class="form_sep">
										<label for="reg_input_no" class="">Query by department (Dept. Requisition/Request report only)</label>
										<select name="dept_rq" class="input-sm chosen-select" style="width:350px;">
											<option selected="selected" value="">Search and Select</option>

											<?php
											$stmt = $db->query("SELECT distinct d.* FROM department d INNER JOIN stock_table_inven r on r.cust_patient_id=d.sn order by department ");
											while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $row['sn'] . '__' . $row['department']; ?>"><?php echo $row['department']; ?></option>
											<?php } ?>
										</select>

									</div>



									<div class="form_sep">
										<label for="reg_input_no" class="req">Search for stock here</label>
										<select name="stock_name" class="input-sm chosen-select" style="width:350px;">
											<option selected="selected" value="">Search and Select</option>

											<?php

											if ($_SESSION['rights'] == 'PH') {
												$stmt = $db->query("SELECT distinct s.* FROM stock_table s INNER JOIN stock_table_inven p on p.stock_sn=s.sn WHERE s.stock_table='Pharmacy' order by stock_table,product_name ");
											} elseif ($_SESSION['navigate'] == 'nursing') {
												$stmt = $db->query("SELECT distinct s.* FROM stock_table s INNER JOIN stock_table_inven p on p.stock_sn=s.sn where s.navigation='nursing' order by stock_table,product_name ");
											} elseif ($_SESSION['navigate'] == 'investigations') {
												$stmt = $db->query("SELECT distinct s.* FROM stock_table s INNER JOIN stock_table_inven p on p.stock_sn=s.sn where s.navigation='investigation' order by stock_table,product_name ");
											} else {
												$stmt = $db->query("SELECT distinct s.* FROM stock_table s INNER JOIN stock_table_inven p on p.stock_sn=s.sn order by stock_table,product_name ");
											}

											while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $row['sn']; ?>"><?php echo $row['product_name']; ?></option>
											<?php } ?>
										</select>

									</div>



									<div class="form_sep" id="">
										<label class="font-noraml">Select Dates Report </label>
										<div class="input-daterange input-group" id="">
											<input type="date" class="form-control" name="start" value="<?php if (isset($_POST['start'])) {
																											echo $_POST['start'];
																										} else {
																											echo date("Y-m-d");
																										} ?>" />
											<span class="input-group-addon">to</span>
											<input type="date" class="form-control" name="end" value="<?php if (isset($_POST['end'])) {
																											echo $_POST['end'];
																										} else {
																											echo date("Y-m-d");
																										} ?>" />
										</div>
									</div>

									<div class="form_sep">
										<button class="btn btn-primary btn-sm" type="submit" name="apply_range">Apply</button>
									</div>
									<br>
									<input type="hidden" name="MM_update" value="adding_cat" />

								</form>
							</div>
							<div class="col-lg-6">

								<?php if ($_SESSION['procure'] == '1') { ?>

									<h2>PO & Departmental Request Summary Reports</h2>
									<HR>
									<form action="index.php?stock=rpt" method="POST">

										<div class="form_sep">
											<label for="reg_input_no" class="req">Choose of type Report</label>
											<select name="type_of_report" id="type_of_report" class="form-control" required style="font-size:14px">
												<option value="">-- select--</option>
												<option value="pro">Purchase Order</option>
												<option value="req">Deparment Requests</option>
											</select>
										</div>

										<div class="form_sep" id="req">
											<label for="reg_input_no" class="req">Ordered by Department</label>
											<select name="department" id="department" class="form-control" style="font-size:14px">
												<option value="">-- select--</option>
												<option value="all">All Departments</option>
												<?php $stmt = $db->query("SELECT distinct d.* FROM department d left join stock_table_request r on r.order_dept=d.sn $search_plus_where order by department ");
												if ($stmt->rowCount() > 0) { ?>
													<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row['sn']; ?>"><?php echo $row['department']; ?></option>
												<?php }
												} ?>
											</select>
										</div>


										<div class="form_sep" id="pro">
											<label for="reg_input_no" class="req">Supplier</label>
											<select name="supplier_id" id="supplier_id" class="form-control" style="font-size:14px">
												<option value="">-- select--</option>
												<option value="all">All Suppliers</option>
												<?php $stmt = $db->query("SELECT distinct c.* FROM stock_company c 				inner join stock_table_procurment p on p.supplier_id=c.sn $search_plus_where order by c.name");
												if ($stmt->rowCount() > 0) { ?>
													<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
												<?php }
												} ?>
											</select>
										</div>

										<div class="form_sep">
											<label for="reg_input_no" class="req">Category</label>
											<select name="stock_table" id="stock_table_rpt" class="form-control" style="font-size:14px" required>
												<option value="">-- select--</option>

												<?php if ($_SESSION['stock_mgr_pharm'] == 1) { ?>
													<option value="Pharmacy">Pharmacy</option>
												<?php } ?>

												<?php if ($_SESSION['stock_mgr'] == 1) { ?>
													<option value="Store">General Store</option>
												<?php } ?>

											</select>
										</div>

										<div class="form_sep">
											<label><strong>Select Stock/Item(Optional)</strong></label>
											<select class="form-control" name="list_stock" id="list_stock">
												<option value="">-- Not Applicable --</option>
											</select>
										</div>


										<div class="form_sep" id="">
											<label for="reg_input_no" class="">Sort by Collector/Requisition (Optional)</label>
											<select name="Collector" id="Collector" class="form-control" style="font-size:14px">
												<option value="">-- select--</option>
												<?php $stmt = $db->query("SELECT distinct order_by,order_by_id FROM stock_table_request $search_plus_where");
												if ($stmt->rowCount() > 0) { ?>
													<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row['order_by_id']; ?>"><?php echo $row['order_by']; ?></option>
												<?php }
												} ?>
											</select>
										</div>



										<div class="form_sep">
											<label for="reg_input_no" class="req">Request Status</label>
											<select name="request_status" id="request_status" class="form-control" style="font-size:14px" required>
												<!-- <option value="all">All Statuses</option> -->
												<option value="no">Pending</option>
												<option value="yes">Approved</option>
												<option value="reverse">Reversed</option>
												<option value="reject">Rejected</option>
												<!-- <option value="issue_pending">Issue Pending</option> -->
											</select>
										</div>

										<div class="form_sep" id="">
											<label class="font-noraml">Select Dates </label>
											<div class="input-daterange input-group" id="">
												<input type="date" class="form-control" name="start2" id="start2" value="<?php if (isset($_POST['start2'])) {
																																echo $_POST['start2'];
																															} else {
																																echo date("Y-m-d");
																															} ?>" />
												<span class="input-group-addon">to</span>
												<input type="date" class="form-control" name="end2" id="end2" value="<?php if (isset($_POST['end2'])) {
																															echo $_POST['end2'];
																														} else {
																															echo date("Y-m-d");
																														} ?>" />
											</div>
										</div>

										<div class="form_sep" id="pro_b">
											<button class="btn btn-primary btn-sm" type="submit" name="PO_Report">Apply PO</button>
										</div>

										<div class="form_sep" id="req_b">
											<button class="btn btn-primary btn-sm" type="submit" name="show_requisition">Apply Request</button>
										</div>

										<div class="">

											<?php
											$current_url = $_SERVER['REQUEST_URI'];
											?>

											<div class="pull-right">
												<a href="<?php echo htmlspecialchars($current_url); ?>" class="btn btn-danger btn-sm">Refresh</a>
											</div>
										</div>
									</form>

								<?php } ?>

							</div>
						</div>
						<?php
						// 
					} elseif (isset($_POST['show_requisition'])) {
						$department_id = $_POST["department"];
						$stock_table = $_POST["stock_table"];
						$start2 = $_POST["start2"];
						$Collector = $_POST["Collector"];
						$end2 = $_POST["end2"];
						$list_stock = $_POST["list_stock"];
						$request_status = $_POST["request_status"];

						//TODO:merge with mr Habu
						// Get department name early if specific department
						$department_name = '';
						if ($department_id != 'all' && $department_id != '') {
							$dept_stmt = $db->query("SELECT department FROM department WHERE sn = '$department_id' LIMIT 1");
							if ($dept_row = $dept_stmt->fetch(PDO::FETCH_ASSOC)) {
								$department_name = $dept_row['department'];
							}
						}

						// status handling
						if ($request_status == 'no') {
							$request_status = "'pending'";
							$date_query = "and date(order_date) between '$start2' and '$end2'";
						} elseif ($request_status == 'yes') {
							$request_status = "'Approved'";
							$date_query = "and date(approve_date) between '$start2' and '$end2'";
						} elseif ($request_status == 'reject') {
							$request_status = "'reject'";
							$date_query = "and date(order_date) between '$start2' and '$end2'";
						} elseif ($request_status == 'issue_pending') {
							$request_status = "'issue_pending'";
							$date_query = "and date(approve_date) between '$start2' and '$end2'";
						} elseif ($request_status == 'all') {
							$request_status = "'pending', 'Approved', 'reverse', 'reject', 'issue_pending'";
							$date_query = "and (
								(status in ('pending', 'reject') and date(order_date) between '$start2' and '$end2') or
								(status in ('Approved', 'reverse', 'issue_pending') and date(approve_date) between '$start2' and '$end2')
							)";
						} else {
							$request_status = "'reverse'";
							$date_query = "and date(approve_date) between '$start2' and '$end2'";
						}

						$empty = '';
						$update = "DELETE FROM temp_claim2";
						$db->exec($update);

						if ($stock_table == '') {
							$search_cateria2 = "";
						} else {
							$search_cateria2 = " and stock_table='$stock_table'";
						}
						if ($list_stock == '') {
							$list_stock = "";
						} else {
							$list_stock = " and stock_sn='$list_stock'";
						}

						if ($Collector == '') {
							$Collector = "";
						} else {
							$Collector = " and order_by_id='$Collector'";
						}

						if ($department_id == 'all' or $department_id == '') {
							$search_cateria = "";
						} else {
							$search_cateria = " and order_dept='$department_id'";
						}

						$stmt_item = $db->query("
						SELECT 
							distinct stock_name
						FROM stock_table_request
						WHERE status in ($request_status) $date_query $search_cateria2 $Collector $search_cateria $list_stock $search_plus");


						if ($stmt_item->rowCount() > 0) { ?>
							<div id="content">
								<table cellpadding="5" cellspacing="5" border="0" align="center">
									<tr>
										<td width="50%" align="center"><img alt="image" src="../img/logo.png" width="200"></td>
									</tr>
									<tr>
										<td width="50%" align="center"><strong><?php echo $_SESSION['h_name']; ?></strong></td>
									</tr>
									<tr>
										<td width="50%" align="center"><?php echo $_SESSION['h_address']; ?></td>
									</tr>
									<tr>
										<td width="50%" align="center"><?php echo $_SESSION['h_phone']; ?></td>
									</tr>
								</table>

								<?php if ($department_id != 'all') { ?>
									<h2>Department: <?php echo $department_name; ?></h2>
								<?php } ?>
								<strong>Report Date: <?php echo date("d-m-Y", strtotime($start2)) . ' - ' . date("d-m-Y", strtotime($end2)); ?></strong>
								<hr>

								<h4>REPORT TITLE: <?= ($request_status == "'pending', 'Approved', 'reverse', 'reject', 'issue_pending'" ? "ALL REQUESTS" : strtoupper(trim($request_status, "'"))) ?> /// DATES: <?= date("d-m-Y", strtotime($start2)) . ' - ' . date("d-m-Y", strtotime($end2)); ?></h4>


								<?php
								//TODO:merge with mr Habu
								$ddd =  'DATES: ' . date("d-m-Y", strtotime($start2)) . ' - ' . date("d-m-Y", strtotime($end2));
								$sql = $db->prepare("INSERT INTO temp_claim2(a2,a3) VALUES (:a2,:a3)");
								$sql->bindParam(':a2', $request_status, PDO::PARAM_STR);
								$sql->bindParam(':a3', $ddd, PDO::PARAM_STR);
								$sql->execute();

								$nn = 1;
								$pck_pcs = 0;
								$stockData = array();
								while ($rowwx = $stmt_item->fetch(PDO::FETCH_ASSOC)) {
									$stock_name = $rowwx['stock_name'];
									$stmt = $db->query("
									SELECT 
										p.*,
										s.department 
									FROM stock_table_request p 
									inner join department s on s.sn=p.order_dept 
									WHERE status in ($request_status) 
									and stock_name ='$stock_name' 
									$date_query $search_cateria2 $Collector $search_cateria $search_plus
									ORDER BY p.order_date DESC");
									//TODO:merge with mr Habu

									// Initialize counters for each status
									$total_pending = 0;
									$total_approved = 0;
									$total_reverse = 0;
									$total_reject = 0;
									$total_issue_pending = 0;

									// Store results in array for multiple passes
									$results = [];
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$results[] = $row;
										// Update totals based on status
										switch ($row['status']) {
											case 'pending':
												$total_pending += $row['order_qty'];
												break;
											case 'Approved':
												$total_approved += $row['approve_qty'];
												break;
											case 'reverse':
												$total_reverse += $row['approve_qty'];
												break;
											case 'reject':
												$total_reject += $row['order_qty'];
												break;
											case 'issue_pending':
												$total_issue_pending += $row['approve_qty'];
												break;
										}
									}
									/// add name of the stock
									$sql = $db->prepare("INSERT INTO temp_claim2(a2) VALUES (:a2)");
									$sql->bindParam(':a2', $stock_name, PDO::PARAM_STR);
									$sql->execute();
									$sql = $db->prepare("INSERT INTO temp_claim2(a2) VALUES (:a2)");
									$sql->bindParam(':a2', $empty, PDO::PARAM_STR);
									$sql->execute();
								?>


									<h3><?php echo $nn . '-' . $stock_name;
										//TODO:merge with mr Habu




										?></h3>
									<table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
										<thead>
											<tr>
												<?php if ($department_id == 'all') {
												?>
													<th>Department</th>
												<?php } ?>
												<th>Status</th>
												<th>ODR/Qty</th>
												<th>APV/Qty</th>
												<th>ISSUE/Qty</th>
												<th>ODR By</th>
												<th>APV By</th>
												<th>ISSUE By</th>
												<th>ODR Date</th>
												<th>APV Date</th>
												<th>ISSUE Date</th>
												<th>Collected By</th>
												<th>Collected Date</th>
												<?php if ($_SESSION['unit_head'] == '1' and $roww['status'] == 'Approved') { ?><th>.</th><?php } ?>
											</tr>
										</thead>
										<tbody>

											<?php
											$a1 = 'DEPARTMENT';
											$a2 = 'ORDER QTY';
											$a3 = 'APPROVE QTY';
											$a4 = 'ORDER BY';
											$a5 = 'APPROVE BY';
											$a6 = 'ORDER DATE';
											$a7 = 'APPROVE DATE';
											$a8 = 'COLLECTED BY';
											$a9 = 'COLLECTED DATE';
											$sql = $db->prepare("INSERT INTO temp_claim2(a1,a2,a3,a4,a5,a6,a7,a8,a9) VALUES (:a1,:a2,:a3,:a4,:a5,:a6,:a7,:a8,:a9)");
											$sql->bindParam(':a1', $a1, PDO::PARAM_STR);
											$sql->bindParam(':a2', $a2, PDO::PARAM_STR);
											$sql->bindParam(':a3', $a3, PDO::PARAM_STR);
											$sql->bindParam(':a4', $a4, PDO::PARAM_STR);
											$sql->bindParam(':a5', $a5, PDO::PARAM_STR);
											$sql->bindParam(':a6', $a6, PDO::PARAM_STR);
											$sql->bindParam(':a7', $a7, PDO::PARAM_STR);
											$sql->bindParam(':a8', $a8, PDO::PARAM_STR);
											$sql->bindParam(':a9', $a9, PDO::PARAM_STR);
											$sql->execute();

											//$nn=1;
											$grand_t = 0;
											$approve_qty = 0;
											foreach ($results as $roww) {

												$Current_date = date('Y-m-d');
												$date1 = new DateTime($Current_date);
												$date2 = new DateTime($roww['approve_date']);
												$diff = $date2->diff($date1);
												$day = $diff->format('%a');


												$approve_qty = $approve_qty + $roww['approve_qty'];
											?>
												<tr>
													<?php if ($department_id == 'all') { ?>
														<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['department']; ?></td>
													<?php } ?>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['status']; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
														<?php
														echo $order_qty = ($roww['pck_pcs'] == 'pck')
															? ($roww['order_qty'] / $roww['unit_pck']) . '(pck)'
															: $roww['order_qty'] . '(pcs)';;
														?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
														<?php echo ($roww['pck_pcs'] == 'pck')
															? ($roww['b4_approve_qty'] / $roww['unit_pck']) . '(pck)'
															: $roww['b4_approve_qty'] . '(pcs)'; ?>
													</td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
														<?php echo $approve_qty = ($roww['pck_pcs'] == 'pck')
															? ($roww['approve_qty'] / $roww['unit_pck']) . '(pck)'
															: $roww['approve_qty'] . '(pcs)'; ?>
													</td>

													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['order_by']; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['b4_approve_by']; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['approve_by']; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo date("d-m-Y", strtotime($roww['order_date'])); ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['b4_approve_date'] ? date("d-m-Y", strtotime($roww['b4_approve_date'])) : ''; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['approve_date'] ? date("d-m-Y", strtotime($roww['approve_date'])) : ''; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['collect_by']; ?></td>
													<td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $roww['collect_date'] ? date("d-m-Y h:i a", strtotime($roww['collect_date'])) : ''; ?></td>


													<td>
														<?php if ($roww['status'] == 'Approved' and $day <= 200) { /// days is les  than 
														?>
															<form method="post" action="index.php?stock=rpt">
																<button type="submit" class="btn btn-danger btn-xs" name="p_reverse_rq" onclick="return confirm('Are you sure you want to REVERSE this ITEM')" value="<?php echo $roww['sn']; ?>">Reverse</button>
															</form>
														<?php } ?>
													</td>
												</tr>
											<?php

												if ($roww['pck_pcs'] == 'pck') {
													$stockData[$stock_name] = array(
														'pck_pcs'  => $roww['pck_pcs'],
														'unit_pck' => $roww['unit_pck']
													);
												}


												if ($department_id != 'all') {
													$department_name = $roww['department'];
												}
												$n++;

												$sql = $db->prepare("INSERT INTO temp_claim2(a1,a2,a3,a4,a5,a6,a7,a8,a9) VALUES (:a1,:a2,:a3,:a4,:a5,:a6,:a7,:a8,:a9)");
												$sql->bindParam(':a1', $roww['department'], PDO::PARAM_STR);
												$sql->bindParam(':a2', $order_qty, PDO::PARAM_STR);
												$sql->bindParam(':a3', $approve_qty, PDO::PARAM_STR);
												$sql->bindParam(':a4', $roww['order_by'], PDO::PARAM_STR);
												$sql->bindParam(':a5', $roww['approve_by'], PDO::PARAM_STR);
												$sql->bindParam(':a6', $roww['order_date'], PDO::PARAM_STR);
												$sql->bindParam(':a7', $roww['approve_date'], PDO::PARAM_STR);
												$sql->bindParam(':a8', $roww['collect_by'], PDO::PARAM_STR);
												$sql->bindParam(':a9', $roww['collect_date'], PDO::PARAM_STR);
												$sql->execute();
											} ?>

										</tbody>
									</table>

									<div style="margin: 5px 0; padding: 5px; background-color: #f9f9f9; border: 1px solid #ddd;">
										<strong>Totals for <?php echo $stock_name;


															///print_r($stockData);
															$searchStock = $stock_name; ///"Paracetamol"; // example stock_name

															if (isset($stockData[$searchStock])) {
																$pck_pcs  = $stockData[$searchStock]['pck_pcs'];
																$unit_pck = $stockData[$searchStock]['unit_pck'];
															} else {
																$unit_pck = 1;
																$pck_pcs = 'pcs';
															}


															?>:</strong>
										<table class="table table-bordered" style="margin-top: 2px; font-size:12px;">
											<tr>






												<?php if ($total_pending > 0) { ?>
													<td><strong>Pending:</strong>
														<?php
														echo ($pck_pcs == 'pck' && $unit_pck > 1)
															? formatQuantity($total_pending, $unit_pck)
															: $total_pending;
														?></td>
												<?php } ?>
												<?php if ($total_approved > 0) {
												?>
													<td><strong>Approved:</strong>
														<?php
														echo ($pck_pcs == 'pck' && $unit_pck > 1)
															? formatQuantity($total_approved, $unit_pck)
															: $total_approved;; ?></td>
												<?php } ?>
												<?php if ($total_reverse > 0) { ?>
													<td><strong>Reversed:</strong>
														<?php

														echo ($pck_pcs == 'pck' && $unit_pck > 1)
															? formatQuantity($total_reverse, $unit_pck)
															: $total_reverse;
														?></td>
												<?php } ?>
												<?php if ($total_reject > 0) { ?>
													<td><strong>Rejected:</strong>
														<?php
														echo ($pck_pcs == 'pck' && $unit_pck > 1)
															? formatQuantity($total_reject, $unit_pck)
															: $total_reject; ?></td>
												<?php } ?>
												<?php if ($total_issue_pending > 0) { ?>
													<td><strong>Issue Pending:</strong>
														<?php

														echo ($pck_pcs == 'pck' && $unit_pck > 1)
															? formatQuantity($total_issue_pending, $unit_pck)
															: $total_issue_pending;
														?></td>
												<?php } ?>
												<td><strong>Total Requests:</strong> <?php echo count($results); ?></td>
											</tr>
										</table>
									</div>
									<hr>
								<?php $nn++;
								}

								?>
							</div>

							<table>
								<tr>

									<td>
										<input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report" />
										<a href="index.php?stock=rpt" class="btn btn-danger btn-xs">Close</a> &nbsp;&nbsp;
									</td>

									<td>



										<form action="download_code.php" method="POST" id="subject" name="subject">

											<button class="btn btn-default btn-xs" type="submit" name="apply_rpt2">Download</button>


										</form>

									</td>

								</tr>

							</table>







						<?php } else { ?>
							<div class="alert alert-warning">No Records to show </div>
							<a href="index.php?stock=rpt" class="btn btn-danger btn-xs">Close</a>
						<?php }
					} elseif (isset($_GET['rrq'])) {
						include("requisition_request.php");
					} elseif (isset($_GET['pcr'])) {
						include("procure_data_entry.php");
					} elseif (isset($_GET['polist_gen']) or isset($_POST['save_pre_order_list'])) {
						include("polist_gen.php");
					} elseif (isset($_GET['pdr'])) { ?>

						<?php if ($_SESSION['Procurement_Officer_ack'] == 1) {
							$stmt_inbox = $db->query("SELECT distinct batch_no FROM stock_table_procurment WHERE approval_stages=1 and navigation='Store'");
							if ($stmt_inbox->rowCount() > 0) { ?>
								<h4 style="color: red;"><strong>Total P.O Request(s) for Approval: <?= $stmt_inbox->rowCount(); ?> </strong></h4>
								<a href="index.php?stock=o&pdr&procurement_manager_ack">View Store & Acknowledge</a>
							<?php }
							$stmt_inbox = $db->query("SELECT distinct batch_no FROM stock_table_procurment WHERE approval_stages=1 and navigation='Pharmacy'");
							if ($stmt_inbox->rowCount() > 0) { ?>
								<h4 style="color: red;"><strong>Total P.O Request(s) for Approval: <?= $stmt_inbox->rowCount(); ?> </strong></h4>
								<a href="index.php?stock=p&pdr&procurement_manager_ack">View Pharmacy & Acknowledge</a>
							<?php } ?>
						<?php } ?>

						<?php if ($_SESSION['PO_payment'] == 1 or $_SESSION['rights'] == 'AC') { ?>
							<h4>
								<span class="blink"><strong>Click the Button View Approve Request(s)</strong></span>
							</h4>
							<a href="index.php?stock=p&pdr&account_pharm">[ Pharmacy Request(s) ]</a> &nbsp; : &nbsp;
							<a href="index.php?stock=o&pdr&account_store">[ Store Request(s) ]</a>
						<?php } ?>

						<?php if ($_SESSION['purchase_approval'] == 1) {
							$stmt_inbox = $db->query("SELECT distinct batch_no FROM stock_table_procurment WHERE approval_stages=2 and status='no' and navigation='Store'");
							if ($stmt_inbox->rowCount() > 0) { ?>
								<h4>
									<span class="blink"><strong>Total P.O Store Request(s) : <?= $stmt_inbox->rowCount(); ?></strong></span>
								</h4>
								<a href="index.php?stock=o&pdr&approve_for_accountant">[ Click to Approve for Accountant ]</a>
							<?php }

							$stmt_inbox = $db->query("SELECT distinct batch_no FROM stock_table_procurment WHERE approval_stages=2 and  status='no' and navigation='Pharmacy'");
							if ($stmt_inbox->rowCount() > 0) { ?>
								<h4 style="color: red;">
									<span class="blink"><strong>Total P.O Pharmacy Request(s) : <?= $stmt_inbox->rowCount(); ?> </strong></span>
								</h4>
								<a href="index.php?stock=p&pdr&approve_for_accountant">[ Click to Approve for Accountant ]</a>
							<?php } ?>
						<?php } ?>

						<hr>
						<h3>QUERY PURCHASE ORDER REQUEST GENERATED:</h3>
						<form action="index.php?stock=<?= $stock_; ?>&pdr" method="post">
							<table width="100%">
								<tr>
									<td width="20%">
										<div class="form_sep">
											<label for="reg_input_no" class="req">VENDOR/SUPPLIER</label>
											<select name="supplier_id" id="supplier_id" class="form-control" style="font-size:14px">
												<option value="">-- select--</option>
												<option value="all">All Suppliers</option>
												<?php $stmt = $db->query("SELECT distinct c.* FROM stock_company c 
					inner join stock_table_procurment p on p.supplier_id=c.sn $search_plus_where order by c.name");
												if ($stmt->rowCount() > 0) { ?>
													<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</td>

									<td>

										<div class="form_sep">
											<label for="reg_input_no" class="">DEPT</label>
											<select name="dept_purchase" id="dept_purchase" class="form-control" style="font-size:14px">
												<option value="">-- select--</option>
												<?php $stmt = $db->query("SELECT distinct PO_dept_name FROM stock_table_procurment order by PO_dept_name");
												if ($stmt->rowCount() > 0) { ?>
													<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row['PO_dept_name']; ?>"><?php echo $row['PO_dept_name']; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</td>
									<td width="=20%">
										<div class="form_sep">
											<label for="reg_input_no" class="">REQUEST STATUS</label>
											<select name="request_status" id="request_status" class="form-control" style="font-size:14px">
												<option value="">-- select--</option>
												<option value="no">Pending</option>
												<option value="yes">Approved</option>
												<option value="reverse">Reverse</option>
											</select>
										</div>
									</td>
									<?php if ($_SESSION['stock_mgr'] == '1' and $_SESSION['stock_mgr_pharm'] == '1') {

									?>
										<td width="20%">
											<div class="form_sep">
												<label for="reg_input_no" class="req">Store</label>
												<select name="stock_table_" id="stock_table_" class="form-control" style="font-size:14px" required>
													<option value="">-- select--</option>
													<option value="Pharmacy">Pharmacy</option>
													<option value="Store">Store</option>
												</select>
											</div>
										</td>
									<?php } ?>



									<?php
									$default_date = isset($_POST['start2']) ? $_POST['start2'] : date("Y-m-d", strtotime("-1 week"));
									?>



									<td width="30%">
										<div class="form_sep" id="">
											<label class="">SELECT DATES</label>
											<div class="input-daterange input-group" id="">
												<input type="date" class="form-control" name="start2" id="start2" value="<?= $default_date; ?>" />
												<span class="input-group-addon">to</span>
												<input type="date" class="form-control" name="end2" id="end2" value="<?php if (isset($_POST['end2'])) {
																															echo $_POST['end2'];
																														} else {
																															echo date("Y-m-d");
																														} ?>" />
											</div>
										</div>
									</td>
								</tr>
							</table>
							<hr>
							<button class="btn btn-primary btn" type="submit" name="apply_search_rpt">Show Pending Requests</button>
							&nbsp;
							<button class="btn btn-success btn" type="submit" name="apply_approve_rpt">Show Approved Requests</button>


						</form>

						<hr>
						<?php

						$set_date = date('Y-m-d');

						if ($_SESSION['navigate'] == 'pharmacy' or $_SESSION['navigate'] == 'Pharmacy' or $_GET['stock'] == 'p') {
							$stock_table_search = "p.stock_table='pharmacy'";
							$stock_table = 'Pharmacy';
						} else {

							if (isset($_POST['stock_table_']) and $_POST['stock_table_'] != '') {
								$stock_table = $_POST['stock_table_'];
								$stock_table_search = "p.stock_table='$stock_table'";
							} else {

								if ($_SESSION['rights'] == 'AC') {
									$stock_table_search = 'p.order_qty>0';  //// show all for accountant
								} else {
									$stock_table_search = "p.stock_table='Store'";
									$stock_table = 'Store';
								}
							}
						}
						///echo $_SESSION['purchase_approval'];

						if (isset($_GET['approve_for_accountant']) and $_SESSION['purchase_approval'] == 1) {
							$search_string = " and p.approval_stages=2";
						} elseif (isset($_GET['procurement_manager_ack'])) {
							$search_string = " and p.approval_stages=1";
						} elseif (isset($_POST['apply_search_rpt']) or isset($_POST['apply_approve_rpt'])) {


							if (isset($_POST['apply_search_rpt'])) {
								$request_status = 'no';
							} elseif (isset($_POST['apply_approve_rpt'])) {
								$request_status = 'yes';
							} else {
								$request_status = 'reserve';
							}



							$dept_purchase = $_POST['dept_purchase'];
							$start2 = $_POST['start2'];
							$end2 = $_POST['end2'];
							$supplier_id = $_POST['supplier_id']; //// all or specific

							if ($request_status != '') {
								$search_string = " and p.status='$request_status'";
							}
							if ($start2 != '' and $end2 != '') {
								$search_string .= " and p.order_date between '$start2' and '$end2'";
							}
							if ($supplier_id != 'all' and $supplier_id != '') {
								$search_string .= " and p.supplier_id='$supplier_id'";
							}
							if ($dept_purchase != '') {
								$search_string .= " and p.PO_dept_name='$dept_purchase'";
							}
						} elseif (($_SESSION['rights'] == 'AC' or isset($_GET['account_store']) or isset($_GET['account_pharm'])) and $_SESSION['PO_payment'] == 1) {
							$search_string = " and p.status='yes'";
							//echo $stock_table_search;
						} else {
							$search_string = " and  p.status='no' and date(p.order_date) between '$set_date' and '$set_date'";
						}

						$stmt = $db->query("SELECT distinct p.batch_no, c.name, p.order_by,p.PO_dept_name,p.approval_stages,p.view_status, p.remarks FROM stock_table_procurment as p 
							inner join stock_company as c on p.supplier_id=c.sn 
							where $stock_table_search $search_string $search_plus order by p.sn desc");

						//echo $stock_table_search . $search_string . $search_plus;
						///echo '<br>';


						if ($stmt->rowCount() > 0) { ?>

							<br>
							<h3><strong style="color: RED;">STEP 3:</strong> GENERATED PURCHASE ORDER (PO) LIST</h3>
							<h4><strong style="color: red;">What to do here:</strong> <i>Edit</i> | <i>View</i> | <i>Approve</i> | <i>Payment</i> | <i>Print Generated PO</i></h4>
							<hr>
							<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size:14px; ">
								<thead>
									<tr>
										<th width="15%">Batch No</th>
										<th width="15%">Supplier</th>
										<th width="15%">Ordered By</th>
										<th width="15%">Total Stock(s)</th>
										<th width="10%">Total</th>
										<th width="30%">Action</th>
									</tr>
								</thead>
								<tbody>

									<?php
									$n = 1;

									while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

										$batch_no = $roww['batch_no'];
										$order_by = $roww['order_by'];
										$approval_stages = $roww['approval_stages'];
										$view_status = $roww['view_status'];
										$delete_status = 'enabled';
										$approve_status = '';
										$reserve_status = '';
										$edit_status = '';
										$total_cost = 0;
										$remarks = $roww['remarks'];

										$stt = $db->query("SELECT total_cost,status,pay_status FROM stock_table_procurment where 
		stock_table='$stock_table' and batch_no='$batch_no' and order_by='$order_by' $search_plus");

										$paid = '';
										$pending = '';

										while ($rowxw = $stt->fetch(PDO::FETCH_ASSOC)) {

											$total_cost = $total_cost + $rowxw['total_cost'];
											////$approval_stages=$rowxw['approval_stages'];
											$set_acct_post_to_ledge = 0;

											if ($rowxw['status'] == 'yes' or $rowxw['pay_status'] == 1) { /// check for delete status
												$delete_status = 'disabled';
											}
											if ($rowxw['status'] == 'no' and $rowxw['pay_status'] == 0) { /// check for payment 
												$pay_status = 'enabled';
											}
											if ($rowxw['status'] == 'no' and $rowxw['pay_status'] == 0) { /// check for approve status
												$approve_status = 'enabled';
											}
											if ($rowxw['status'] == 'reverse' and $rowxw['pay_status'] == 1) { /// check for approve status
												$reserve_status = 'enabled';
											}
											if ($rowxw['status'] == 'no' and $rowxw['pay_status'] == 0) { /// check for approve status
												$edit_status = 'enabled';
											}
											if ($rowxw['status'] == 'no' and $rowxw['pay_status'] == 1) {
												$await = '<strong>Paid/Auth.Pending.</strong>';
											}
											if ($rowxw['status'] == 'yes' and $rowxw['pay_status'] == 1) { /// check for delete status
												$paid = '<strong>Paid</strong>';
												$set_acct_post_to_ledge = 0;
											}
											if ($rowxw['status'] == 'yes' and $rowxw['pay_status'] == 0) { /// check for delete status
												$pending = "<strong style='color:red;' >Payment Pending</strong>";
												$set_acct_post_to_ledge = 1;
											}
										}

									?>
										<tr>
											<td>
												<?php echo $batch_no ?>
												<i class="badge badge-primary"><?php echo (stripos($remarks, 'Express') != false) ? 'Express' : '' ?></i>
											</td>
											<td><?php echo $supplier_name_desc = $roww['name'];

												echo '<br><b>Dept: </b>' . $roww['PO_dept_name'];
												$supplier_name_desc = $supplier_name_desc . ' (' . $roww['PO_dept_name'] . ' )';
												?></td>
											<td><?php echo $roww['order_by']; ?></td>
											<td><?= $stt->rowCount(); ?></td>
											<td><?= number_format($total_cost); ?></td>
											<td>

												<?php

												$approval_stages = $roww['approval_stages'];

												/* 		echo $_SESSION['Procurement_Officer_ack'];
												echo '<br>';
												echo $approval_stages;
												echo '<br>'; */



												$idd = $batch_no . '/' . $order_by . '/' . $stock_table . '/' . $stock;
												if ($approval_stages == 0) {
													$value_btn = 'Submit to Procurement Officer';
													$btn_color = "warning";
												} elseif ($approval_stages == 1 and $_SESSION['Procurement_Officer_ack'] == 1) {
													$value_btn = 'View & Acknownledge';
													$btn_color = "success";
												} elseif ($approval_stages == 2) {
													$value_btn = 'Submitted for Approval';
													$btn_color = "danger";
												} elseif ($approval_stages == 1 and $_SESSION['Procurement_Officer_ack'] == 0) {
													$value_btn = 'View Submitted';
													$btn_color = "danger";
												}

												///$view_statuss=null;

												if ($view_status == 'ack' && $approval_stages > 0 && $_SESSION['purchase_approval'] == 0 && $_SESSION['Procurement_Officer_ack'] == 0) {
													$view_statuss = 'Seen: Procurement Officer';
												} else {
													$view_statuss = null;
												}

												///	echo $pay_status . '   ' . $_SESSION['rights'] . '   ' . $_SESSION['purchase_approval'];
												?>

												<?php
												///if($_SESSION['purchase_approval'] == 1){}

												if ($pay_status == 'enabled' and $_SESSION['rights'] != 'AC') { ?>
													<input type="button" name="edit" value="<?= $value_btn; ?>" data-target="#myModal5" id="<?php echo $idd . '/edit/' . $supplier_name_desc . '/' . $approval_stages; ?>"
														class="btn btn-<?= $btn_color; ?> btn-xs edit_order" />
												<?php } ?>
												<!-- TODO:Merge with mr Habu, made preview show at all statges -->
												<?php if (1) { ?>
													<input type="button" name="print" value="Preview" data-target="#myModal5" id="<?php echo $idd . '/' . $approval_stages;; ?>"
														class="btn btn-success btn-xs print_order" />
												<?php } ?>

												<?php if ($approval_stages >= 2 && $set_acct_post_to_ledge == 1 && ($_SESSION['PO_payment'] == 1 or $_SESSION['rights'] == 'AC')) { ?>
													<input type="button" name="payment" value="Post to Ledger" data-target="#myModal5" id="<?php echo $idd . '/payment/' . $supplier_name_desc . '/' . $approval_stages; ?>"
														class="btn btn-primary btn-xs edit_order" />
												<?php } ?>

												<?php if ($approve_status == 'enabled' && $_SESSION['purchase_approval'] == 1 && $approval_stages == 2) { ?>
													<input type="button" name="approve" value="Approve" data-target="#myModal5" id="<?php echo $idd . '/approve/' . $supplier_name_desc . '/' . $approval_stages;; ?>"
														class="btn btn-warning btn-xs edit_order" />
												<?php } else {
													echo $await;
												} ?>

												<?php if ($reserve_status == 'enabled' and $_SESSION['purchase_approval'] == 1) { ?>
													<input type="button" name="reverse" value="Authorize Reverse" data-target="#myModal5" id="<?php echo $idd . '/reverse/' . $approval_stages;; ?>"
														class="btn btn-danger btn-xs edit_order" />
												<?php } ?>



												<?php
												// Check if payment already exists for this batch_no
												$stmt_chk = $db->prepare("SELECT 1 FROM stock_table_procure_pay WHERE batch_no = :batch_no LIMIT 1");
												$stmt_chk->execute([':batch_no' => $batch_no]);
												$exists = $stmt_chk->fetchColumn();

												$canDelete = !$exists
													&& $delete_status === 'enabled'
													&& ($_SESSION['fullname'] === $order_by || $speciality_admin === 'Administrator');

												if ($canDelete): ?>
													<a href="index.php?del_ordered=<?php echo $idd; ?>&stock=<?php echo htmlspecialchars($stock_, ENT_QUOTES, 'UTF-8'); ?>"
														onclick="return confirm('Are you sure you want to delete?')"
														class="btn btn-danger btn-xs">Del</a>
												<?php endif; ?>


												<?= $paid . ' : ' . $pending;

												echo $view_statuss;

												?>
											</td>
										</tr>
									<?php
										$g_ttotal = $g_ttotal + $total_cost;
										$n++;
									} ?>

								</tbody>
							</table>
							<h2>Total: <?= number_format($g_ttotal); ?></h2>
						<?php } else {
							echo 'No Record Found';
						}
					} elseif (isset($_POST['apply_range'])) {

						$start = $_POST['start'];
						$end = $_POST['end'];
						$stock_id = $_POST['stock_name'];
						$dept_rq = $_POST['dept_rq'];


						?>
						<hr>
						<?php
						//echo '=========' . $q_dept;
						//exit;
						if ($dept_rq != '') {
							$pp = explode("__", $dept_rq);
							$q_dept = " and cust_patient_id=:cust_patient_id";
							$report_title = "<h2>Department Requisition Inventory Report : $pp[1] </h2>";
						} else {
							$q_dept = "";
							$report_title = "<h2>Department Requisition Inventory Report</h2>";
						}

						$stmt = $db->prepare('SELECT t.* FROM (SELECT * FROM stock_table_inven 
												WHERE stock_sn = :stock_sn AND date(captured_date) BETWEEN :start AND :end ' . $q_dept . ' ORDER BY sn ASC) t ORDER BY t.sn ASC');
						$stmt->bindParam(':stock_sn', $stock_id);
						$stmt->bindParam(':start', $start);
						$stmt->bindParam(':end', $end);

						if ($dept_rq != '') {
							$stmt->bindParam(':cust_patient_id', $pp[0]);
						}

						$stmt->execute();

						if ($stmt->rowCount() > 0) {
							echo $report_title; ?>
							<table class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
									<tr>
										<th>#</th>
										<th width="30%">Description</th>
										<th>IN</th>
										<th>OUT</th>
										<th>Bal</th>
										<th>Date</th>
										<th>Captured By</th>
									</tr>
								</thead>
								<tbody>

									<?php
									$n = 1;
									$qtyIN = 0;
									$qtyOUT = 0;
									$qtyConsumed = 0;
									$unknownQty = 0;
									$no_of_test = 0;
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
									?>
										<tr>
											<td><?php echo $n; ?></td>
											<td><?php echo $row['inven_desc']; ?></td>
											<td><?php echo $row['qtyIN'];
												$qtyIN = $qtyIN + $row['qtyIN']; ?></td>
											<td><?php echo $row['qtyOUT'];
												$qtyOUT = $qtyOUT + $row['qtyOUT'];  ?></td>
											<td><?php echo $row['bal']; ?></td>
											<td><?php echo date('d M,Y', strtotime($row['insertion_date_time'])) . '<br>';
												echo date('h:i:s a', strtotime($row['insertion_date_time'])); ?></td>
											<td><?php echo $row['enter_by']; ?></td>

										</tr>
									<?php
										$colordecide++;
										$n++;
									} ?>
								</tbody>
							</table>
						<?php } else {
							echo 'No Inventory Records Found';
						} ?>



						<hr>

						<?php
						$stmt2 = $db->prepare('SELECT * FROM stock_table WHERE sn = :sn');
						$stmt2->bindParam(':sn', $stock_id);
						$stmt2->execute();
						$rows = $stmt2->fetch(PDO::FETCH_ASSOC); ?>

						<div class=" alert alert-success">
							SUMMARY OF REPORT &nbsp; - <?php echo $rows['product_name'] . ' [ ' .  $rows['category'] . ' ]'; ?>
						</div>

						<table class="table table-striped table-bordered table-hover">
							<tr>
								<td>Quantity<br> IN</td>
								<td>Quantity<br> OUT</td>
							</tr>
							<tr>
								<td><?php echo $qtyIN; ?></td>
								<td><?php echo $qtyOUT; ?></td>
							</tr>
						</table>

						<a href="index.php?stock=rpt" class="btn btn-success btn btn-sm">Back</a>
					<?php } else { ?>


						<form action="index.php?stock=<?php echo $stock; ?>" method="post">
							<div class="row">
								<div class="col-lg-3">
									<div class="form_sep">
										<label for="reg_input_no" class="req">FILTER BY CATEGORY</label>
										<select name="sort_by_category_text" id="" class="form-control" style="font-size:14px">
											<option value="">-- select--</option>
											<?php $stmt_c = $db->query("SELECT distinct category FROM stock_table  WHERE $search_part $search_plus $search order by category");
											if ($stmt_c->rowCount() > 0) { ?>
												<?php while ($row = $stmt_c->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $row['category']; ?>"><?php echo $row['category']; ?></option>
											<?php }
											} ?>
										</select>
									</div>
								</div>
								<div class="col-lg-2">
									<div class="form_sep">
										<label for="reg_input_no" class="">.</label><br>
										<button class="btn btn-primary btn-sm" type="submit" name="sort_by_category">Show Report</button>
									</div>
								</div>


								<?php if (!in_array($_SESSION['rights'], ['LB', 'NS'], true)) {  ?>
									<div class="col-lg-3">
										<div class="form_sep">
											<label for="reg_input_no" class="req" style="color:red; ">SEE OTHER DEPT CURRENT QTY.</label>
											<select name="store_department" id="" class="form-control">
												<option selected="selected" value="">--Select--</option>

												<?php
												$stmtxx = $db->query("SELECT * FROM department order by department");
												while ($row = $stmtxx->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
												<?php }  ?>
											</select>
										</div>

									</div>

									<div class="col-lg-2">
										<div class="form_sep">
											<label for="reg_input_no" class="">.</label><br>
											<button class="btn btn-danger btn-sm" type="submit" name="show_dept_store">SHOW ME</button>
										</div>
									</div>

								<?php }  ?>
								<div class="col-lg-2">
									<div class="form_sep">
										<label for="reg_input_no" class="">.</label><br>
										<a href="#" class="btn btn-default btn-sm" onclick="refreshCurrentURL()">Refresh Page</a>

										<script>
											function refreshCurrentURL() {
												// Get the current URL
												var currentURL = window.location.href;

												// Reload the page using the current URL
												window.location.href = currentURL;
											}
										</script>

									</div>
								</div>
							</div>
						</form>
						<hr>


						<?php
						/// default here ///

						//TODO:fix the expiry query to fetch from procurment insted of stock_table
						$setdate = date("Y-m-d");
						$back_yr = date('Y-m-d', strtotime('-1 year'));
						$exp_3m = date('Y-m-d', strtotime('+3 months'));
						$exp_6m = date('Y-m-d', strtotime('+6 months'));
						if (isset($_GET['delete'])) {
							$search = " and status='delete'";
						} elseif (isset($_GET['total'])) {
							$search = " and status='active'";
						} elseif (isset($_GET['dormant'])) {
							$search = " and status='active' and date_last_update<='$back_yr'";
						} elseif (isset($_GET['reorder'])) {
							$search = " and status='active' and main_qty=reorder_level";
						} elseif (isset($_GET['expired'])) {
							$setdate = date("Y-m-d");
							$search = " and status='active' and date(expire_date) != '0000-00-00' and date(expire_date)<='$setdate'";
						} elseif (isset($_GET['exp_now'])) {
							$setdate = date("Y-m-d");
							$search = " and status='active' and date(expire_date) != '0000-00-00' and date(expire_date)<='$setdate'";
						} elseif (isset($_GET['exp_3m'])) {
							$setdate = date("Y-m-d");
							///$search = " and status='active' and date(expire_date) != '0000-00-00' and date(expire_date)<='$exp_3m' and date(expire_date) > '$setdate'";
							/// DATE(date_entry2) BETWEEN '$begin' AND '$end'
							$search = " and status='active' and date(expire_date) != '0000-00-00' and DATE(expire_date) BETWEEN '$setdate' AND '$exp_3m'";
						} elseif (isset($_GET['exp_6m'])) {
							$setdate = date("Y-m-d");
							$search = " and status='active' and date(expire_date) != '0000-00-00' and DATE(expire_date) BETWEEN '$exp_3m' AND '$exp_6m'";
						} elseif (isset($_GET['exp_invalid'])) {
							$search = " and status='active' and (expire_date = '0000-00-00' OR expire_date IS NULL)";
						} else {
							$search = " and status='active'";
						}


						////	echo $search_part . $category . $search . $search_plus;


						if (isset($_POST['sort_by_category'])) {
							$sort_by_category_text  = $_POST['sort_by_category_text'];
							$sort_cat = " and category='$sort_by_category_text'";
						} else {
							$sort_cat = "";
						}



						if (isset($_POST['show_dept_store'])) {
							$dept_id_status =	$_POST['store_department'];
							$stock_sn =	$_POST['sn'];
							$stmt = $db->query("SELECT distinct s.sn,s.package_type,s.product_name,s.category, 
							s.buying_cost,s.cash_price,s.stock_total_unit,s.qty,s.reorder_level,s.expire_date,s.status FROM stock_table as s 
							INNER JOIN stock_table_inven as i
							ON i.stock_sn = s.sn
							 WHERE cust_patient_id='$dept_id_status' AND $search_part $search_plus $search $sort_cat order by date_last_update");

							$stmt45 = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :dept_id ORDER BY sn DESC LIMIT 1");
						} else {
							////echo $search_part . $search_plus . $search . $sort_cat;
							$stmt = $db->query("SELECT * FROM stock_table WHERE $search_part $search_plus $search $sort_cat order by date_last_update");
						}
						if ($stmt->rowCount() > 0) {
							if ($_SESSION['pharm_request_from_store'] == 1) { ?><h3 style="color:red; ">The quantity of pharmacy stock is obtained through requisitions from the store.</h3> <?php } ?>

							<table class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
									<tr>
										<th width="3%">#</th>
										<th width="5%">Code</th>
										<th width="31%">Name</th>
										<th width="4%">Buying</th>
										<th width="4%">Selling</th>
										<th width="4%">Unit</th>
										<th width="4%">Qty</th>
										<th width="4%">PK</th>
										<th width="4%">Pcs</th>
										<th width="4%">R/L</th>
										<th width="5%"><span style="color: red;">Edit Exp</span></th>
										<th width="15%">Action</th>
									</tr>


								</thead>
								<tbody>

									<?php
									$n = 1;
									while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
									?>
										<tr>
											<td><?php echo $n; ?></td>
											<td>
												<?php if (($_SESSION['stock_mgr'] == '1' || $_SESSION['stock_mgr_pharm'] == '1') || ($_SESSION['unit_head'] == '1' && $_SESSION['rights'] == 'NS')) { ?>
													<input type="button" name="edit" value="Edit" <?php if ($roww["status"] == 'delete') { ?>disabled<?php } ?> data-target="#myModal5" id="<?php echo $roww["sn"]; ?>" class="btn btn-warning btn-xs edit_view_stock" />
												<?php } ?>

											</td>
											<td><?php echo $roww['product_name']; ?></td>
											<td><?php echo number_format($roww['buying_cost'], 2); ?></td>
											<td><?php echo number_format($roww['cash_price'], 2); ?></td>
											<td><?php echo $unit = $roww['stock_total_unit']; ?></td>
											<td>
												<?php
												if (isset($_POST['show_dept_store'])) {

													$dept_id_status = $_POST['store_department'];
													$stock_sn = $roww['sn'];

													$stmt45 = $db->prepare("
        SELECT bal 
        FROM stock_table_inven 
        WHERE stock_sn = :stock_sn 
        AND cust_patient_id = :dept_id 
        ORDER BY sn DESC 
        LIMIT 1
    ");
													$stmt45->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
													$stmt45->bindParam(':dept_id', $dept_id_status, PDO::PARAM_STR);
													$stmt45->execute();

													if ($stmt45->rowCount() > 0) {
														$rwx = $stmt45->fetch(PDO::FETCH_ASSOC);
														$qty = (int)$rwx['bal'];
													} else {
														$qty = 0;
													}
												} else {
													$qty = (int)$roww['qty'];
												}

												/* ===== SAFE CALCULATION (PHP 5.6+) ===== */
												$unit = (int)$unit;

												if ($unit > 0) {
													$pack = (int)($qty / $unit); // PHP 5.6 compatible
													$bb   = $qty % $unit;

													if ($unit > 1) {
														echo $pack;
													}
												} else {
													echo 0;
												}

												?></td>
											<td><?php echo $roww['package_type']; ?></td>
											<td><?php

												if ($unit > 1) {
													echo $bb;
												} else {
													echo $qty;
												}

												?></td>
											<td><?php echo $roww['reorder_level']; ?></td>
											<td>
												<input type="button" name="edit" <?php if ($roww["status"] == 'delete') { ?>disabled<?php } ?> value="<?php if ($roww['expire_date'] != '') {
																																							echo date("d-m-Y", strtotime($roww['expire_date']));
																																						} else {
																																							echo '-';
																																						} ?>" data-target="#myModal5" id="<?php echo $roww["sn"] . '__' . $roww["product_name"]; ?>"
													class="btn btn-<?php if ($roww['expire_date'] < $setdate) { ?>danger<?php } else { ?>success<?php } ?> btn-xs mgt_expire_date" />

												<?php displayRemainingDays($roww['expire_date']); ?>

											</td>
											<td>


												<?php
												$mgt_stock = '0'; // default
												if (
													($_SESSION['rights'] == 'ST' && $_SESSION['stock_mgr'] == '1') ||
													($_SESSION['rights'] == 'PH' && $_SESSION['stock_mgr_pharm'] == '1' && $_SESSION['mgt_stock'] == '1') ||
													($_SESSION['unit_head'] == '1')
												) {
													$mgt_stock = '1';
												}

												if ($_SESSION['pharm_request_from_store'] == 1 && $_SESSION['rights'] == 'PH') { ?>
												<?php } elseif (isset($_POST['show_dept_store'])) { ?>
													<b>Request via Requisition</b>
												<?php } elseif (in_array($_SESSION['rights'], ['LB', 'NS'], true)) { ?>
												<?php } elseif (!isset($_POST['show_dept_store']) &&  $mgt_stock == '1') { ?>

													<input type="button" name="edit" value="Add & Remove" <?php if ($roww["status"] == 'delete') { ?>disabled<?php } ?> data-target="#myModal5" id="<?php echo $roww["sn"] . '__' . $roww["product_name"]; ?>"
														class="btn btn-success btn-xs mgt_inven" />

													<?php

													if (isset($_GET['delete'])) { ?>
														<a href="index.php?stock=<?php echo $stock_; ?>&ac=<?php echo $roww["sn"]; ?>" class="btn btn-success btn-xs" onclick="return confirm('Are you sure you want to Activate this Stock?')">Enable</a>
														<?php } else {
														if ($roww['qty'] == 0) { ?>
															<a href="index.php?stock=<?php echo $stock_; ?>&dl=<?php echo $roww["sn"]; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete?')">Del.</a>
												<?php }
													}
												}
												?>
												<input type="button" name="inv" value="INV." data-target="#myModal5" id="<?php echo $roww["sn"] . '/' . $dept_id_inv; ?>"
													class="btn btn-primary btn-xs inven_report" />
											</td>

										</tr>
									<?php
										$n++;
									} ?>

								</tbody>
							</table>
						<?php } else { ?>
							<div class="alert alert-warning">No Records to show </div>

						<?php } ?>
					<?php } ?>




				</div>
			</div>
		</div>

	</div>



<?php } ?>


<?php

function displayRemainingDays($expireDate)
{
	try {
		// Create DateTime objects
		$currentDate = new DateTime();
		$expirationDate = new DateTime($expireDate);

		// Calculate remaining days
		$remainingDays = $currentDate->diff($expirationDate)->days;

		// Determine the color based on remaining days
		if ($expireDate == "0000-00-00") {
			echo "<span style='color: orange;'>Invalid Date</span>";
		} elseif ($remainingDays <= 0) {
			// Expired
			echo "<span style='color: gray;'>Expired</span>";
		} elseif ($remainingDays > 30 && $remainingDays < 90) { // Between 1 and 3 months
			echo "<sup style='color: red; font-size:12px;'>Exp.$remainingDays days</sup>";
		} elseif ($remainingDays >= 90 && $remainingDays < 180) { // Between 3 and 6 months
			echo "<sup style='color: red; font-size:12px;'>Exp.$remainingDays days</sup>";
		} else { // More than 6 months
			echo "";
		}
	} catch (Exception $e) {
		// Handle invalid date
		echo "<span style='color: orange;'>Invalid expiration date</span>";
	}
}
?>

<div class="modal inmodal fade" id="add_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Adding New Stock</h4>
			</div>

			<div class="modal-body">
				<form action="index.php?stock=<?php echo $stock_; ?>" method="POST">
					<div class="form_sep">
						<label for="reg_input_no" class="req"> <?php if ($stock == 'p') { ?>Drug/Product Name<?php } else { ?>Product/Stock Name<?php } ?></label>
						<input type="text" id="stockname" name="stockname" class="form-control" required maxlength="100">
					</div>



					<div class="form_sep">
						<label for="reg_input_no">Category</label>
						<select name="category" id="category" class="form-control" style="font-size:14px">
							<option value="">-- select--</option>
							<?php $stmt = $db->query("SELECT * FROM stock_cat_table where navigation='$navigation'");
							if ($stmt->rowCount() > 0) { ?>
								<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
							<?php }
							} ?>
						</select>
					</div>


					<?php if ($stock == 'p') { /// pharmacy section /// 
					?>

						<div class="form_sep">
							<label for="reg_input_no" class="">Generic Name</label>
							<input type="text" id="GenericName" name="GenericName" class="form-control">
						</div>


						<input type="hidden" name="dosage" id="dosage">
						<div class="form_sep">
							<label for="reg_input_no" class="">Presentation</label>
							<select name="presentation" id="presentation" class="form-control" style="font-size:14px">
								<option value="">-- select--</option>

								<option value="vial">vial</option>
								<option value="Cap">Cap</option>
								<option value="Tab">Tab</option>
								<option value="Card">Card</option>
								<option value="bottle">bottle</option>
								<option value="Ampoule">Ampoule</option>
								<option value="nebules">nebules</option>
								<option value="pack">pack</option>
								<option value="tube">tube</option>
								<option value="eye drops">eye drops</option>
								<option value="gutt">gutt</option>
								<option value="sachet">sachet</option>
								<option value="TIN">TIN</option>
								<option value="amp">amp</option>
								<option value="TABLET">TABLET</option>
								<option value="syringe">syringe</option>
								<option value="VIALS">VIALS</option>
								<option value="supp">supp</option>
								<option value="needle">needle</option>
								<option value="CAPSULE">CAPSULE</option>
								<option value="tabs">tabs</option>
								<option value="INJ">INJ</option>
								<option value="caps">caps</option>
								<option value="CARTON">CARTON</option>
								<option value="GUAZE">GUAZE</option>
								<option value="Other">Other</option>
							</select>

						</div>
						<input type="hidden" id="strength" name="strength" class="form-control">
					<?php } ?>



					<div class="form_sep" id="">
						<label for="reg_input_no">Expirying Date</label>
						<div class="input-group date">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="date" name="expired" id="expired" class="form-control">
						</div>
					</div>

					<div class="form_sep">
						<label for="reg_input_no" class="req">Re-order level</label>
						<input type="number" id="reorder" name="reorder" class="form-control" min="0" value="1" required>
					</div>

					<input type="hidden" id="qty" name="qty" value="0">


					<div class="form_sep">
						<label for="reg_input_no" class="req">Package Type</label>
						<select name="packagetype" id="packagetype" class="form-control" style="font-size:14px" required>
							<option value="">-- select--</option>

							<?php $stmtt = $db->query("SELECT * FROM stock_table_package");
							if ($stmtt->rowCount() > 0) { ?>
								<?php while ($roww = $stmtt->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $roww['name']; ?>"><?php echo $roww['name']; ?></option>
							<?php }
							} ?>
						</select>
					</div>

					<div class="form_sep">
						<label for="reg_input_no" class="req">Total Units/Piece in each Package above</label>
						<input type="number" id="units" name="units" class="form-control" value="1" onkeyup="sum();" min="1" required>
					</div>
					<hr>
					<?php

					if ($_SESSION['rights'] == 'PH') {
						$username = $_SESSION['username'];
						$stmtt = $db->query("SELECT manage_price FROM pharm_users where username='$username'");
						if ($stmtt->rowCount() > 0) {
							$roww = $stmtt->fetch(PDO::FETCH_ASSOC);
							$manage_price = $roww['manage_price'];
						} else {
							$manage_price = 0;
						}
					}



					//if ($_SESSION['manage_price'] == 1 or ($_SESSION['rights'] == 'PH' and $manage_price == 1)  or $_SESSION['rights'] == 'NS') { 


					if ($_SESSION['stock_mgr']  == 1 || $manage_price == 1) { ?>

						<input type="hidden" name="priveleges" id="priveleges" value="yes" />

						<div class="form_sep">
							<label for="reg_input_no" class="">Purchase Cost</label>
							<input type="number" step="any" id="purchase_cost" name="purchase_cost" value="1" class="form-control" min="0">
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="">Percentage Mark up</label>
							<input type="number" id="percentage_markup" name="percentage_markup" class="form-control" value="0" onkeyup="percentage_markup_cal();" min="0">
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="req">HMO/General Hospital Price</label>
							<input type="number" step="any" id="hosp_price" name="hosp_price" class="form-control" value="0" required>
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="req">Cash Price (HMO Claims Not Computed on this Price)</label>
							<input type="number" step="any" id="cash_price" name="cash_price" class="form-control" value="0" required>
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="">NHIS Price (Enter Price if Covered by NHIS or Skip it)</label>
							<input type="number" step="any" id="NHIS_price" name="NHIS_price" class="form-control">
						</div>

						<div class="form_sep">
							<label for="reg_input_no" class="">NHIS Care Service Type (Covarage Extension)</label>
							<select name="insurance_type" id="insurance_type" class="form-control">
								<option selected="selected" value="">Select...</option>

								<option value="1">Primary Care (Without Authorization Code)</option>
								<option value="2">Secondary Care (Authorization Code Required)</option>
							</select>
						</div>

					<?php
					} else {
					?>



						<input type="hidden" step="any" id="hosp_price" name="hosp_price">
						<input type="hidden" step="any" id="cash_price" name="cash_price">
						<input type="hidden" step="any" id="NHIS_price" name="NHIS_price">
						<input type="hidden" id="percentage_markup" name="percentage_markup">

						<input type="hidden" name="priveleges" id="priveleges" value="no" />

					<?php  } ?>

					<div class="form_sep"></div>

					<div class="pull-left">
						<button class="btn btn-primary" type="submit" name="add_stock">Save</button>
					</div>

					<div class="pull-right">
						<button class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
					<br>
					<input type="hidden" name="stock_id" id="stock_id" />
					<input type="hidden" name="stock_table" id="stock_table" value="<?php echo $stock_table; ?>" />


				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="upload_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Upload Stock</h4>
			</div>

			<div class="modal-body">

				<form action="excel_stock_download.php" id="" method="POST" enctype="multipart/form-data">

					<!--<form action="" id="stock_upload_form" method="POST" enctype="multipart/form-data">		-->
					<div class="form_sep">
						<label for="reg_input_no" class="req"> Select the type of stock record you wish to upload</label>
						<select name="upload_type" id="stock_download_type" class="form-control" onchange="show_hmo_field1(this.value)" required>
							<option value="">--select the type of stock record you wish to upload--</option>
							<option value="hmo">HMO/Corporate Specific stock records</option>
							<option value="full">Full stock record</option>
						</select>
					</div>
					<div class="form_sep" id="hmo_select_field_upload" style="display: none;">
						<?php
						$statment = $db->query("SELECT * FROM insurance_tbl WHERE insurance_type='PHIS' OR insurance_type='Corporate'");
						?>
						<label for="reg_input_no" class="req"> Select the HMO/Corporate whose stock record you wish to upload</label>
						<select name="upload_hmo" id="stock_upload_hmo" class="form-control">
							<option value="">--select the HMO whose data you wish to upload--</option>
							<?php if ($statment->rowCount() > 0) { ?>
								<?php while ($roww = $statment->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $roww['insurance_no']; ?>"><?php echo $roww['insurance_name']; ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
					<div class="form_sep">
						<label for="">Select excel file</label>
						<input type="file" name="the_upload" class="form-control">
					</div>
					<div class="form_sep" id="upload_the_stock_spinner" style="display: none;">
						<div class="spinner-border" role="status">
							<span>Loading...</span>
						</div>
					</div>
					<div id="err_stock_upload"></div>
					<div class="form_sep" id="upload_the_stock_btn">
						<br>

						<!--			<input class="btn btn-primary btn-xs" value="Upload" type="submit" name="upload_the_stock" id="">
-->

						<input class="btn btn-primary btn-xs" value="Upload" type="submit" name="upload_the_stock" id="">
					</div>

					<input type="hidden" name="stock_table" id="stock_table" value="<?php echo $stock_table; ?>" />
				</form>
				<script>
					function show_hmo_field1(download_type) {
						//console.log(download_type);
						if (download_type == 'hmo') {
							$('#stock_upload_hmo').prop('required', true);
							$('#stock_upload_hmo').prop("disabled", false);
							$('#hmo_select_field_upload').show();

						} else {
							$('#hmo_select_field_upload').hide();
							$('#stock_upload_hmo').prop("disabled", true);
							$('#stock_upload_hmo').val('');
						}
					}
				</script>
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="download_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Download Stock</h4>
			</div>

			<div class="modal-body">
				<!--<form action="excel_stock_download.php" method="POST" id="download_stock_form">-->
				<form action="excel_stock_download.php" method="POST" id="">
					<div class="form_sep">
						<label for="reg_input_no" class="req"> Select the type stock record you wish to download</label>
						<select name="download_type" id="stock_download_type" class="form-control" required onchange="show_hmo_field(this.value)">
							<option value="">--select the type of stock record you wish to download--</option>
							<option value="hmo">HMO/Corporate Specific stock records</option>
							<option value="full">Full stock record</option>
						</select>
					</div>
					<div class="form_sep" id="hmo_select_field" style="display: none;">
						<?php
						$statment = $db->query("SELECT * FROM insurance_tbl WHERE insurance_type='PHIS' OR insurance_type='Corporate'");
						?>
						<label for="reg_input_no" class="req"> Select the HMO/Corporate whose of stock record you wish to download</label>
						<select name="download_hmo" id="stock_download_hmo" class="form-control">
							<option value="">--select the HMO whose data you wish to download--</option>
							<?php if ($statment->rowCount() > 0) { ?>
								<?php while ($roww = $statment->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?php echo $roww['insurance_no'] . '__' . $roww['insurance_name']; ?>"><?php echo $roww['insurance_name']; ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
					<div class="form_sep">
						<button class="btn btn-primary btn-xs" type="submit" name="download_the_stock">Download</button>
					</div>

					<input type="hidden" name="stock_table" id="stock_table" value="<?php echo $stock_table; ?>" />
				</form>
				<script>
					function show_hmo_field(download_type) {
						//console.log(download_type);
						if (download_type == 'hmo') {
							$('#stock_download_hmo').prop('required', true);
							$('#stock_download_hmo').prop("disabled", false);
							$('#hmo_select_field').show();

						} else {
							$('#hmo_select_field').hide();
							$('#stock_download_hmo').prop("disabled", true);
							$('#stock_download_hmo').val('');
						}
					}
				</script>
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="add_category_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Adding New Stock Category</h4>
			</div>
			<div class="modal-body" id="add_category_body">
			</div>
		</div>
	</div>
</div>


<div class="modal inmodal fade" id="edit_order_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Edit Order</h4>
			</div>
			<div class="modal-body" id="edit_order_body">
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="print_ordered_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Print Order</h4>
			</div>
			<div class="modal-body" id="print_ordered_body">
			</div>
		</div>
	</div>
</div>


<div class="modal inmodal fade" id="edit_category_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Edit Stock Category</h4>
			</div>
			<div class="modal-body" id="edit_category_body">
			</div>


		</div>
	</div>


</div>


<div class="modal inmodal fade" id="edit_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Stock Details</h4>
			</div>
			<div class="modal-body" id="edit_stock_body">
			</div>


		</div>
	</div>
</div>


<!-- Inventory Modal -->
<div class="modal inmodal" id="inventory_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" style="width:95vw;max-width:95vw;margin:1rem auto;">

		<div class="modal-content animated bounceInRight">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Stocks Inventory and Purchase Order (PO)</h4>
			</div>

			<div class="modal-body" id="inventory_body">

			</div>
		</div>
	</div>
</div>


<div class="modal inmodal" id="inventory_modal2" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">

		<div class="modal-content animated bounceInRight">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title">Stocks Inventory and Purchase Order (PO)</h4>
			</div>

			<div class="modal-body" id="inventory_body2">

			</div>

		</div>
	</div>
</div>

<div class="modal inmodal fade" id="edit_hmo_prices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">HMO/Corporate prices</h4>
			</div>
			<div class="modal-body" id="edit_hmo_prices_body">
			</div>


		</div>
	</div>
</div>
<div class="modal inmodal fade" id="mgt_expire_date_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Manage Procurement(s) Expiring Dates</h4>
			</div>

			<div class="modal-body" id="mgt_expire_date_body">

			</div>
		</div>
	</div>
</div>



<div class="modal inmodal fade" id="approve_request_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Approve Request</h4>
			</div>
			<div class="modal-body" id="approve_request_body">
			</div>


		</div>
	</div>
</div>


<div class="modal inmodal fade" id="add_company_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Company Details</h4>
			</div>
			<div class="modal-body" id="add_company_body">
			</div>


		</div>
	</div>
</div>

<div class="modal inmodal fade" id="edit_company_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Edit Company Details</h4>
			</div>
			<div class="modal-body" id="edit_company_body">
			</div>


		</div>
	</div>
</div>

<div class="modal inmodal fade" id="approve_procurement_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Approve Procurement Request</h4>
			</div>
			<div class="modal-body" id="approve_procurement_body">
			</div>


		</div>
	</div>
</div>


<div class="modal inmodal fade" id="procurement_report_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Procurement Report</h4>
			</div>
			<div class="modal-body" id="procurement_report_body">
			</div>


		</div>
	</div>
</div>

<div class="modal inmodal fade" id="create_stock_combo_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Create Stock Combo</h4>
			</div>
			<div class="modal-body" id="create_stock_combo_body">
			</div>
		</div>
	</div>
</div>


<div class="modal inmodal fade" id="po_reorder_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Create Stock Combo</h4>
			</div>
			<div class="modal-body" id="po_reorder_body">
			</div>
		</div>
	</div>
</div>

<?php
$dept_id = $_SESSION['dept_id'];
if ($_SESSION['navigate'] == 'pharmacy') {
	$search = "AND stock_table='Pharmacy' AND dept_incharge_stock='$dept_id'";
} else {
	$search = "AND stock_table!='Pharmacy'";
}

//echo $search;
///echo $_SESSION['navigate'];

?>
<?php
$stmt = $db->query("SELECT r.sn,r.order_date,d.department,r.order_qty,r.stock_name,r.order_by 
FROM stock_table_request r
INNER JOIN department d on d.sn=r.order_dept 
WHERE seen=1 and status='pending' $search $search_plus");

////echo $stmt->rowCount();

if ($stmt->rowCount() == 0) {
	$stmt = $db->query("SELECT r.sn,r.order_date,d.department,r.order_qty,r.stock_name,r.order_by 
	FROM stock_table_request r
	INNER JOIN department d on d.sn=r.order_dept 
	WHERE dept_incharge_stock='$dept_id' AND seen=1 and status='pending' $search_plus");
}


if (
	$stmt->rowCount() > 0 &&
	($_SESSION['stock_mgr'] == '1' or $_SESSION['unit_head'] == '1' or $_SESSION['b4_rq_approval'] == '1' or $_SESSION['rq_approval'] == '1')
) { ?>

	<div class="modal inmodal fade" id="requisition_popup_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Requisition Queue</h4>
				</div>
				<div class="modal-body" id="requisition_popup_body">
					<div align="center"> <a href="#" class="btn btn-danger" onclick="seen_popup('all')">Seen All</a></div>
					<table class="table table-striped table-bordered ">
						<thead>
							<tr>
								<th data-toggle="true">#</th>
								<th data-toggle="true">Stock Name</th>
								<th data-toggle="true">Oty</th>
								<th data-toggle="true">Department</th>
								<th data-toggle="true">Date</th>
								<th data-toggle="true">Ordered By</th>
								<th data-toggle="true"></th>
							</tr>
						</thead>
						<tbody>

							<?php $n = 1;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							?>
								<tr>
									<td><?php echo $n; ?></td>
									<td><?php echo $row['stock_name']; ?></td>
									<td><?php echo $row['order_qty']; ?></td>
									<td><?php echo $row['department']; ?></td>
									<td><?php echo date('d M,y', strtotime($row['order_date'])); ?></td>
									<td><?php echo $row['order_by']; ?></td>
									<td>
										<a href="#" class="btn btn-primary btn-xs" onclick="seen_popup('<?php echo $row['sn']; ?>')">Seen</a>
										<?php echo $row['enter_by']; ?>
									</td>

								</tr>
							<?php
								$n++;
							} ?>
						</tbody>
					</table>
				</div>


			</div>
		</div>
	</div>
<?php } ?>


<style>
	@keyframes blinker {
		50% {
			opacity: 0;
		}
	}

	.blink {
		animation: blinker 1s linear infinite;
		color: red;
		font-weight: bold;
	}
</style>


<script>
	function save_edit_product_stock() {

		var stockname = document.getElementById('stockname_edit_2').value;
		var expired = document.getElementById('expired_edit_2').value;
		var reorder = document.getElementById('reorder_2').value;
		var packagetype = document.getElementById('packagetype_2').value;
		var stock_snn = document.getElementById('stock_snn').value;


		$.ajax({
			url: "fetch_stock.php",
			method: "POST",
			data: {
				edit_stockname: stockname,
				expired: expired,
				reorder: reorder,
				packagetype: packagetype,
				stock_snn: stock_snn
			},
			success: function(data) {
				alert(data);

				///document.getElementById('pharm_doctor_chat_displayed').innerHTML = data;

			}
		});




	}
</script>