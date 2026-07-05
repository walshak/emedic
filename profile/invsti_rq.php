<?php
include("../inc/session.php");
include("../Connections/Conn.php");



?>

<?php

$msg = null;

if (isset($_POST["edit_update_price"])) {
	//   hosp_price  sn edit_update_price

	$updateSQL = "UPDATE stock_table_dispensory SET hosp_price = :hosp_price, cash_price = :cash_price, nhis_price = :nhis_price WHERE id = :id";
	$stmt = $db->prepare($updateSQL);
	$stmt->bindParam(':hosp_price', $_POST["hosp_price"]);
	$stmt->bindParam(':cash_price', $_POST["cash_price"]);
	$stmt->bindParam(':nhis_price', $_POST["nhis_price"]);
	$stmt->bindParam(':id', $_POST["sn"]);
	$stmt->execute();
}



if (isset($_POST["process_select_rq"])) {
	$pro_inv = $_POST['inv_all'];
	$destination = $_POST['destination'];

	if (!empty($pro_inv)) {

		for ($i = 0; $i < count($pro_inv); $i++) {
			$inv_id = $pro_inv[$i];

			$stmt = "UPDATE stock_table_request SET seen=1,order_dept='$destination'  WHERE sn='$inv_id'";
			$db->exec($stmt);
		}

		//if ($stmt->rowCount()>0){
		$msg = "Request Has been Processed Successful";
		$status = 'success';
		//}
	} else {
		$msg = "Check the Request to Process";
		$status = 'error';
	}
}



if (isset($_GET["unlist"])) {

	$unlist = $_GET["unlist"];
	$stmt = "UPDATE stock_table_request SET remove_status='1' WHERE sn='$unlist'";
	$db->exec($stmt);
	header("location:invsti_rq.php");
}



?>

<!DOCTYPE html>
<html>

<?php include("../inc/header.php"); ?>


<body>

	<div id="wrapper">

		<nav class="navbar-default navbar-static-side" role="navigation">
			<div class="sidebar-collapse">
				<ul class="nav" id="side-menu">
					<li class="nav-header">
						<div class="dropdown profile-element"> <span>
								<img alt="image" class="img-circle" src="<?php if (file_exists(staff_p . 'port_' . $uname . '.' . 'jpg')) {
																				echo staff_p . 'port_' . $uname . '.' . 'jpg';
																			} else {
																				echo '../img/user_avatar_lg.png';
																			} ?>" height="50" width="50">

							</span>
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname ?></strong>
									</span> <span class="text-muted text-xs block"><?php echo $_SESSION['Designation'] ?> <b class="caret"></b></span> </span> </a>
							<ul class="dropdown-menu animated fadeInRight m-t-xs">
								<li><a href="index.php?profile=<?php echo $_SESSION['username'] ?>">Profile</a></li>
								<li><a href="mailbox.php">Mailbox</a></li>
								<li class="divider"></li>
								<li><a href="../index.php">Logout</a></li>
							</ul>
						</div>
						<div class="logo-element">
							IN+
						</div>
					</li>

					<li>
						<a href="../<?php echo $_SESSION['navigate']; ?>/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
					</li>

					<?php include("../inc/nav_side_profile.php"); ?>

				</ul>

			</div>
		</nav>


		<div id="page-wrapper" class="gray-bg">
			<?php include("../inc/nav_header.php"); ?>



			<?php
			if (isset($_GET["del"])) {
				$del = $_GET["del"];
				$stmt = $db->prepare("DELETE FROM stock_table_request WHERE (status='reject' or status='pending') and sn='$del'");
				$stmt->execute();
			}

			$stmt = $db->prepare("DELETE FROM stock_table_request 
			WHERE `order_date` <= NOW() - INTERVAL 60 DAY AND `status` LIKE 'pending'");
			$stmt->execute();
			?>


			<div class="row wrapper border-bottom white-bg page-heading">
				<div class="col-lg-10">
					<h2>Stock Requisition Interface</h2>
				</div>
				<div class="col-lg-2">
				</div>
			</div>

			<div class="wrapper wrapper-content  animated fadeInRight">
				<div class="row">
					<div class="col-lg-5">
						<?php


						$stock_table = $_POST['stock_table'];
						if ($_POST['category'] != '') {
							$category = $_POST['category'];
							$search_cat = " and category ='$category'";
						} else {
							$search_cat = '';
						}

						?>


						<div class="ibox ">
							<div class="ibox-title">
								<h5>Stock List</h5>
							</div>
							<div class="ibox-content">

								<form action="invsti_rq.php" method="post">
									<div class="form_sep">
										<label for="reg_input_no" class="req">Department/Unit</label>
										<select name="stock_table_dept" id="stock_table_dept" class="form-control" required style="font-size:14px">
											<option value="">-- select--</option>
											<?php
											$stock_table_dept = $_POST['stock_table_dept'];

											$stmt = $db->query("SELECT * FROM department where department_type='pharmacy' or department_type='Main Store' or department_type='store'");
											if ($stmt->rowCount() > 0) {
												while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $row["sn"]; ?>" <?php if ($stock_table_dept == $row["sn"]) { ?>selected<?php } ?>><?php echo  $row["department"]; ?></option>
											<?php }
											}
											?>
										</select>
									</div>


									<div class="form_sep">
										<label for="reg_input_no" class="req">Select Category</label>
										<select name="stock_table" id="stock_table" class="form-control" required style="font-size:14px">
											<option value="">-- select--</option>

											<?php $stmt = $db->query(sprintf("SELECT distinct stock_table FROM stock_table where stock_table!=''"));
											if ($stmt->rowCount() > 0) {
												while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $row["stock_table"]; ?>" <?php if ($stock_table == $row["stock_table"]) { ?> selected <?php } ?>><?php echo  $row["stock_table"]; ?></option>
											<?php }
											}
											?>
										</select>
									</div>




									<div class="form_sep">
										<label for="reg_input_no" class="">Select Sub Category (Optional)</label>
										<select name="category" id="category" class="form-control" style="font-size:14px">
											<option value="">-- select--</option>

											<?php $stmt = $db->query(sprintf("SELECT distinct category FROM stock_table where category!=''"));
											if ($stmt->rowCount() > 0) {
												while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $row["category"]; ?>" <?php if ($category == $row["category"]) { ?> selected <?php } ?>><?php echo  $row["category"]; ?></option>
											<?php }
											}
											?>
										</select>
									</div>



									<div class="form_sep">
										<button class="btn btn-primary btn-sm" type="submit" name="display">Display</button>
									</div>
								</form>


								<?php

								if (isset($_POST['display'])) {

									$stmt = $db->query("SELECT DISTINCT s.sn,s.product_name,s.buying_cost,s.stock_total_unit,s.stock_total_unit FROM stock_table AS s INNER JOIN stock_table_inven AS i ON i.stock_sn = s.sn WHERE stock_table='$stock_table' AND cust_patient_id='$stock_table_dept' $search_cat and status='active' order by product_name");
								?>
									<hr>
									<table class="table table-striped table-bordered table-hover dataTables-example">
										<thead>
											<tr>
												<th with="50%">Stock/Item</th>
												<th width="2%">Qty</th>
												<th width="5%">Pck/Pcs</th>
												<th width="3%"></th>

											</tr>
										</thead>
										<tbody>

											<?php
											$n = 1;
											$ttotal = 0;
											$g_ttotal = 0;
											$approve_amt = 0;
											while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
												$purchase_price = $roww['buying_cost'];
												if ($purchase_price == '') {
													$purchase_price = 0;
												}

											?>
												<tr>
													<td><?php echo $roww['product_name']; ?>

														<input type="hidden" class="input-sm form-control" id="stockName_<?php echo $n; ?>" name="stockName_<?php echo $n; ?>" required value="<?php echo $roww['product_name']; ?>" />
														<input type="hidden" class="input-sm form-control" id="stock_sn_<?php echo $n; ?>" name="stock_sn_<?php echo $n; ?>" required value="<?php echo $roww['sn']; ?>" />
														<input type="hidden" class="input-sm form-control" id="stock_total_unit_<?php echo $n; ?>" name="stock_total_unit_<?php echo $n; ?>" required value="<?php echo $roww['stock_total_unit']; ?>" />
													</td>

													<td width="25%">

														<input type="number" class="input-sm form-control" id="order_qty_<?php echo $n; ?>" name="order_qty_<?php echo $n; ?>" required value="1" onkeyup="UpdateCost()" min="1" style="width:90%;" />

													</td>
													<td> <select name="pcs_pack" id="pcs_pack_<?php echo $n; ?>" class="form-control">
															<option value="pcs">Piece</option>
															<?php if ($roww['stock_total_unit'] > 1) { ?> <option value="pck">Packed</option><?php } ?>
														</select>
													</td>
													<td>
														<button class="btn btn-xs btn-primary" name="" onClick="add_order('<?= $n; ?>')" style="font-size:20px;">+</button>

													</td>


												</tr>

											<?php
												$g_ttotal = $g_ttotal + $ttotal;
												$n++;
											} ?>

										</tbody>
									</table>

									<input type="hidden" name="p_list" id="p_list" value="<?php echo $n - 1; ?>">


								<?php } ?>








								<br>
								<hr><br>

								<strong style="color:#F00">What to do here ... </strong><br>
								See consumable inventory report and how you manage requisition orders ...
								<hr>

								<form action="invsti_rq.php" method="post">

									<div class="form_sep">

										<label for="reg_input_no" class="req">Select Product Name for Inventory Report</label>
										<select name="consumable2" class="form-control">
											<option selected="selected" value="">Select ...</option>

											<?php
											$dept_id = $_SESSION['dept_id'];
											$stmt = $db->query("SELECT distinct s.sn,s.product_name FROM stock_table s inner join stock_table_inven c on s.sn=c.stock_sn where c.cust_patient_id='$dept_id'");
											if ($stmt->rowCount() > 0) {
												while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
													<option value="<?php echo $row["sn"] . '__' . $row["product_name"]; ?>"><?php echo $row["product_name"]; ?></option>
											<?php }
											}
											?>
										</select>
									</div>


									<div class="form_sep">
										<strong>Filter By Date</strong>
										<div class="form-group" id="">
											<div class="input-daterange input-group" id="datepicker">
												<input type="date" class="form-control" name="start" value="<?php echo date("Y-m-d"); ?>" />
												<span class="input-group-addon">to</span>
												<input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
											</div>
										</div>
									</div>


									<div class="form_sep">
										<button class="btn btn-success btn-sm" type="submit" name="show_report">Show Report </button>
									</div>
								</form>

								<?php if ($_SESSION['dispensory'] == 1) { ?>
									<hr>
									<form action="invsti_rq.php" method="post">

										<div class="form_sep">
											<button class="btn btn-primary" type="submit" name="dispensary_items">Show Dispensary Item </button>
										</div>
									</form>
								<?php } ?>

							</div>
						</div>
					</div>





					<div class="col-lg-7">


						<div class="ibox ">
							<div class="ibox-title">
								<h5>Inventory/Tracking List</h5>
							</div>
							<div class="ibox-content">

								<h4>Display Requisition History</h4>



								<table width="100%">
									<tr>
										<td>
											<div class="form_sep" id="">
												<label class="font-noraml"><strong>Select Dates / Click Show Report</strong> </label>
												<div class="input-daterange input-group" id="datepicker">
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
										</td>
										<td>&nbsp;&nbsp;
										</td>
										<td>
											<div class="form_sep">
												<label class="font-noraml">. </label><br>
												<button class="btn btn-success btn-sm" name="show_report_list" onClick="show_report()">Show Report </button>
											</div>
										</td>
										<td>&nbsp;&nbsp;</td>
										<td align="right">
											<div align="right"> <label class="font-noraml">. </label><br>
												<input type="button" name="edit" value="Deduct Stock(s) HERE" data-target="#myModal5" id="<?php echo $_SESSION['dept_id']; ?>"
													class="btn btn-danger btn-sm dept_inven" />

												<a href="invsti_rq.php" class="btn btn-default btn-sm">Refresh</a>
											</div>
										</td
											</tr>
								</table>
								<hr>
								<?php


								if (isset($_POST['dispensary_items']) or isset($_POST["edit_update_price"])) {
									///echo $dept_id;

									$stmt = $db->prepare("SELECT d.*, s.product_name,s.stock_table FROM stock_table_dispensory d 
									INNER JOIN stock_table s ON s.sn=d.stock_table_id WHERE d.dept_id=:dept_id");
									$stmt->bindParam(':dept_id', $dept_id);
									$stmt->execute();

									if ($stmt->rowCount() > 0) { ?>
										<table class="table table-striped table-bordered table-hover dataTables-example">
											<thead>
												<tr>
													<th data-toggle="true">#</th>
													<th data-toggle="true" width="30%">Item</th>
													<th data-toggle="true">Table</th>
													<th data-toggle="true">nhis_price</th>
													<th data-toggle="true">hosp_price</th>
													<th data-toggle="true">cash_price</th>
													<th data-toggle="true"></th>

												</tr>
											</thead>
											<tbody>

												<?php
												$n = 1;
												while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

												?>
													<tr>
														<td><?php echo $n; ?></td>
														<td><?php echo $row['product_name']; ?></td>
														<td><?php echo $row['stock_table']; ?></td>
														<td><?php echo $row['nhis_price']; ?></td>
														<td><?php echo $row['hosp_price']; ?></td>
														<td><?php echo $row['cash_price']; ?></td>
														<td><input type="button" name="edit_price" value="Edit Amount" data-target="#modal" id="<?php echo $row['id']; ?>" class="btn btn-danger btn-xs edit_price_entry" /></td>

													</tr>
												<?php
													$colordecide++;
													$n++;
												} ?>
											</tbody>
										</table>
									<?php } else {
										echo 'No Auto Records Found';
									}
								} elseif (isset($_POST['show_report'])) {
									$consumable2 =  $_POST['consumable2'];
									$part = explode("__", $consumable2);
									$stock_name = $part[1];
									$consumable2 = $part[0];
									$start =  $_POST['start'];
									$end =  $_POST['end'];
									?>

									<h3>Stock Name: &nbsp; <?php echo $stock_name; ?></h3>

									<?php
									$stmt = $db->prepare("SELECT t.* FROM (SELECT * FROM stock_table_inven
							WHERE stock_sn=:stock_sn and cust_patient_id=:cust_patient_id and date(captured_date) BETWEEN :start_date and :end_date order by sn desc) t order by t.sn asc");
									$stmt->bindParam(':stock_sn', $consumable2);
									$stmt->bindParam(':cust_patient_id', $_SESSION['dept_id']);
									$stmt->bindParam(':start_date', $start);
									$stmt->bindParam(':end_date', $end);
									$stmt->execute();

									if ($stmt->rowCount() > 0) { ?>
										<table class="table table-striped table-bordered table-hover dataTables-example">
											<thead>
												<tr>
													<th data-toggle="true">#</th>
													<th data-toggle="true" width="30%">Description</th>
													<th data-toggle="true">IN</th>
													<th data-toggle="true">OUT</th>
													<th data-toggle="true">Bal</th>
													<th data-toggle="true">Date</th>
													<th data-toggle="true">Captured By</th>
												</tr>
											</thead>
											<tbody>

												<?php
												$n = 1;
												$qtyIN = 0;
												$qtyOUT = 0;
												$no_of_tests = 0;
												$unknownQty = 0;
												while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

													if ($row['return_status'] == '1') {
														$no_of_tests = $no_of_tests + 1;
													}

												?>
													<tr>
														<td><?php echo $n; ?></td>
														<td><?php echo $row['inven_desc']; ?></td>
														<td><?php echo $row['qtyIN'];
															$qtyIN = $qtyIN + $row['qtyIN']; ?></td>
														<td><?php echo $row['qtyOUT'];
															$qtyOUT = $qtyOUT + $row['qtyOUT'];  ?></td>
														<td><?php echo $row['bal']; ?></td>
														<td><?php echo date('d M,y', strtotime($row['captured_date'])); ?></td>
														<td><?php echo $row['enter_by']; ?></td>

													</tr>
												<?php
													$colordecide++;
													$n++;
												} ?>
											</tbody>
										</table>
									<?php } else {
										echo 'No Auto Records Found';
									} ?>

									<hr>
									<br>
									<a href="invsti_rq.php" class="btn btn-default btn-sm">Refresh</a>


								<?php } else {  ?>

									<div id="request_list"></div>

								<?php } ?>

							</div>
						</div>



					</div>

				</div>

			</div>

		</div>
	</div>




	<div class="modal inmodal fade" id="dept_inven_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Stocks Inventory</h4>
				</div>

				<div class="modal-body" id="dept_inven_body">

				</div>
			</div>
		</div>
	</div>


	<div class="modal inmodal fade" id="edit_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title" id="">Edit Price</h4>
				</div>
				<div class="modal-body" id="edit_price_body">
				</div>


			</div>
		</div>
	</div>

	<?php include('../modal_lock.php'); ?>
	<?php include("../inc/footer_scripts.php"); ?>

	<script>
		$(document).ready(function() {

			$("#stock_combo").hide();
			$("#stock_combo1").hide();
			$("#stock_combo_cat").hide();
			$("#pharmacy_list").hide();
			$("#store_list").hide();

			$("#stock_table").change(function() {

				var category_id = $(this).val();
				let substr = 'Combo';

				if (category_id.includes(substr) == true) {
					$("#stock_combo").hide();
					$("#stock_combo_cat").hide();
					$("#stock_combo1").show();
				} else {


					if (category_id == 'Pharmacy') {
						$("#pharmacy_list").show();
						$("#store_list").hide();
					}

					if (category_id == 'Store') {
						$("#store_list").show();
						$("#pharmacy_list").hide();
					}


					// $("#stock_combo").show();
					// $("#stock_combo_cat").show();
					$("#stock_combo1").hide();
				}


				if (category_id != "") {
					$.ajax({
						url: "../inc/get_stock_table.php",
						data: {
							stock_combo_cat: category_id
						},
						type: 'POST',
						success: function(response) {
							var resp = $.trim(response);
							$("#stock_category").html(resp);

						}
					});
				} else {
					$("#stock_category").html("<option value=''>------- Select --------</option>");
				}

			});



			$("#stock_category").change(function() {
				var stock_table = document.getElementById('stock_table').value;

				var item_sort_id = $(this).val();

				if (item_sort_id != "") {
					$.ajax({
						//alert(category_id);
						url: "../inc/get_stock_table.php",
						data: {
							category_item: item_sort_id,
							stock_table: stock_table
						},
						type: 'POST',
						success: function(response) {
							///alert(response);
							var resp = $.trim(response);
							$("#stock_items").html(resp);

						}
					});
				} else {
					$("#stock_items").html("<option value=''>------- Select --------</option>");
				}

			});




		});



		<?php

		if ($msg != '') { ?>
			toastr.<?= $status; ?>('<?php echo $msg; ?>', '<?= $status;  ?>', {
				timeOut: 5000
			})
		<?php } ?>


		<?php if (isset($_GET['sv'])) { ?>
			toastr.success('<?php echo 'Save Successfully '; ?>', 'Success', {
				timeOut: 5000
			})
		<?php } ?>
		<?php if (isset($_GET['main_err'])) { ?>
			toastr.error('<?php echo 'Requested Stock already exists! '; ?>', 'Error', {
				timeOut: 5000
			})
		<?php } ?>
		<?php if (isset($_GET['invalid_sel'])) { ?>
			toastr.error('<?php echo 'Invalid Selection! '; ?>', 'Error', {
				timeOut: 5000
			})
		<?php } ?>
		<?php if (isset($_GET['invalid_qty'])) { ?>
			toastr.error('<?php echo 'Invalid Quantity! '; ?>', 'Error', {
				timeOut: 5000
			})
		<?php } ?>
	</script>

	<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
	<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
	<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

	<script>
		$(document).on('click', '.edit_price_entry', function() {
			var editprice_id = $(this).attr("id");
			if (editprice_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						editprice_id: editprice_id
					},

					success: function(data) {

						$('.modal-title').text('Edit Price');
						$('#edit_price_body').html(data);
						$('#edit_price_modal').modal('show');
					}
				});
			}
		});

		request_list();

		function show_report() {


			var start2 = document.getElementById("start2").value;
			var end2 = document.getElementById("end2").value;
			var request_list_id = 100;



			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					start2: start2,
					end2: end2,
					request_list_id: request_list_id
				},
				success: function(data) {

					////alert(data);

					$("#request_list").html(data);

				}
			});


		}



		function request_list() {
			var request_list_id = 100;
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					request_list_id: request_list_id
				},
				success: function(data) {
					$("#request_list").html(data);
				}
			});
		}


		function add_order(sn) {

			var stock_sn_ = document.getElementById("stock_sn_" + sn).value;
			var stock_name = document.getElementById("stockName_" + sn).value;
			var order_qty_ = document.getElementById("order_qty_" + sn).value;
			var pcs_pack_ = document.getElementById("pcs_pack_" + sn).value;
			var stock_table_dept = document.getElementById("stock_table_dept").value;

			///alert(stock_table_dept);


			if (order_qty_ <= 0) {
				alert('Invalid Quantity');
				exit;
			}


			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					stock_name: stock_name,
					order_qty_: order_qty_,
					pcs_pack_: pcs_pack_,
					stock_table_dept: stock_table_dept,
					stock_sn_: stock_sn_
				},
				success: function(data) {

					var json = JSON.parse(data);

					if (json["batch_no"] == 0) {
						toastr.success(json["message"], 'Attention', {
							timeOut: 5000
						})

						request_list();
					} else {
						toastr.error(json["message"], 'Attention', {
							timeOut: 5000
						})

					}


				}
			});

		}



		$(document).ready(function() {
			function get_combo_stock() {
				alert();

			}

		});


		$(document).on('click', '.dept_inven', function() {
			//	 alert();

			var dept_inven_id = $(this).attr("id");
			$.ajax({
				url: "insert.php",
				method: "POST",
				data: {
					dept_inven_id: dept_inven_id
				},
				success: function(data) {
					$('#dept_inven_body').html(data);
					$('#dept_inven_modal').modal('show');
				}
			});
		});



		$(document).ready(function() {
			$('.dataTables-example').dataTable({
				responsive: true,
				"dom": 'T<"clear">lfrtip',
				"tableTools": {
					"sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
				}
			});

			/* Init DataTables */
			var oTable = $('#editable').dataTable();

			/* Apply the jEditable handlers to the table */
			oTable.$('td').editable('../example_ajax.php', {
				"callback": function(sValue, y) {
					var aPos = oTable.fnGetPosition(this);
					oTable.fnUpdate(sValue, aPos[0], aPos[1]);
				},
				"submitdata": function(value, settings) {
					return {
						"row_id": this.parentNode.getAttribute('id'),
						"column": oTable.fnGetPosition(this)[2]
					};
				},

				"width": "90%",
				"height": "100%"
			});


		});

		function fnClickAddRow() {
			$('#editable').dataTable().fnAddData([
				"Custom row",
				"New row",
				"New row",
				"New row",
				"New row"
			]);

		}
	</script>

	<script src="../js/idle.js"></script>
</body>

</html>