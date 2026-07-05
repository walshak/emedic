<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('declared.php');
?>
<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>
<?php


if (isset($_GET['invest'])) {
	$_SESSION['navigate'] = 'investigations';
} elseif (isset($_GET['admin'])) {
	$_SESSION['navigate'] = 'admin';
}

$speciality_admin = $_SESSION['speciality_admin'];
if (isset($_POST['delete_confirm'])) {

	$table_id = $_POST["table_id"];
	$item_id = $_POST["item_id"];
	///$emr=$_POST["emr"];

	$update = "DELETE FROM patient_discount WHERE individual_group_no='$item_id'";
	$db->exec($update);
	$update = "DELETE FROM patient_discount_services WHERE individual_group_no='$item_id'";
	$db->exec($update);

	header("location:index.php?discount");
}

$payment_domain = "(p.serv_group='Laboratory' or p.serv_group='Radiology')";
$setdate = date("Y-m-d");
$responsible = $_SESSION['fullname'];
$setdate = date("Y-m-d");
$year = date("Y");
$patient_acct_status = 1;
?>


<body class="fixed-navigation">

	<div id="wrapper">
		<?php include("../inc/nav_side_bill.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">
			<?php include("nav_header.php"); ?>
			<?php include("../inc/billing_side_bar.php"); ?>

			<div class="wrapper wrapper-content">

				<?php if (isset($_GET['discount'])) {
					include("dsc.php");
				} elseif (isset($_GET['vchr']) or isset($_POST['apply_vchr'])) {
					include("vchr.php");
				} else { ?>



					<div class="row">
						<div class="col-lg-12">
							<div class="ibox float-e-margins">
								<div class="ibox-title">

									<h5>Biller Dashboard</h5>
								</div>
								<div class="ibox-content">
									<div class="row">




										<div class="col-lg-4 b-r">

											<?php if ($_SESSION['discount'] == 1) { ?>
												<a href="index.php?discount" class="btn btn-app"><i class="fa fa-edit"></i> Discount</a>
											<?php } ?>
											<?php if ($_SESSION['vouchers'] == 1) { ?>
												<a href="index.php?vchr" class="btn btn-app"><i class="fa fa-ticket"></i>Vouchers</a>
											<?php } ?>
											<hr>
											<a href="transc.php" class="btn btn-app"><i class="fa fa-archive"></i>Sales Reports</a>
											<?php if ($_SESSION['ext_sales'] == 1) { ?>
												<a href="../admin/index.php?sale" class="btn btn-app"><i class="fa fa-shopping-cart"></i>External Services</a><?php } ?>
											<a href="../admin/index.php?enq" class="btn btn-app"><i class="fa fa-search-plus"></i>Prices Enquiry</a>

										</div>


										<div class="col-lg-4 b-r">
											<h2>Waiting for Payments</h2>



											<form method="POST" action="pacct.php">
												<?php $bckdate = date("Y-m-d", strtotime($setdate . " -1 day")); ?>
												<strong style="color: red;"> Selected Dates: <?php echo date("d-m-Y", strtotime($bckdate)) . ' to ' . date("d-m-Y"); ?></strong>
												<hr>

												<div class="form_sep">
													<label for="reg_input_no" class="req">Search for Patient here</label>
													<select name="search2" class="input-sm chosen-select" style="width:350px;">
														<option selected="selected" value="">Search and Select Patient</option>
														<?php
														$query = "SELECT DISTINCT ap.hospital_no, en.surname, en.fname, en.oname
              FROM patient_ap_services AS ap
              INNER JOIN enrollee AS en ON ap.hospital_no = en.hospital_no
              WHERE DATE(ap.date_entry) BETWEEN :bckdate AND :setdate
              AND ap.paystatus = '0' AND ap.pay > 0
              ORDER BY ap.date_entry DESC
              LIMIT 100"; // Optional: Add limit for performance

														$stmt = $db->prepare($query);
														$stmt->bindParam(':bckdate', $bckdate);
														$stmt->bindParam(':setdate', $setdate);
														$stmt->execute();

														while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
															$fullName = htmlspecialchars("{$row['surname']}, {$row['fname']} {$row['oname']}");
															$hospitalNo = htmlspecialchars($row['hospital_no']);
															echo "<option value=\"$hospitalNo\">$fullName</option>";
														}
														?>
													</select>


												</div>
												<div class="form_sep">
													<button type="submit" class="btn btn-success btn btn-sm" name="apply_search" id="apply_search"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
													| <button type="submit" class="btn btn-danger  btn-sm" name="refresh_list" id="refresh_list"><i class="fa fa-refresh"></i>&nbsp;Refresh List</button>
												</div>
											</form>
											<script>
												document.getElementById("refresh_list").addEventListener("click", function() {
													location.reload();
												});
											</script>



										</div>




										<div class="col-lg-4">
											<h2>General Patient Search</h2><strong></strong>

											<div class="form_sep">
												<label for="reg_input_no" class="">(Name or Phone or Hospital Number)</label>
												<input type="text" id="search" name="search" class="form-control" required>
											</div>
											<br>
											<div class="form_sep">
												<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action" id="apply_action" onclick="search_patient('billing')"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
											</div>

											<hr>



											<div class="form_sep">
												<label for="reg_input_no" class="">External Number</label>
												<input type="text" id="search_2" name="search_2" class="form-control" required>
											</div>
											<br>
											<div class="form_sep">

												<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action_2" id="apply_action_2" onclick="search_patient('Billing_Ex_sales')"><i class="fa fa-search"></i>&nbsp;Apply Search</button>

											</div>


										</div>

									</div>
								</div>
							</div>


							<div class="ibox-title">
								<h5>Admitted Patients</h5>
							</div>
							<div class="ibox-content">
								<div class="table-responsive">
									<?php
									$stmt = $db->prepare("
										SELECT 
											d.hospital_no, d.app_no, d.room_bed, d.room_bed_sn, 
											d.date_admit, d.doc_incharge, d.floor, dd.department,
											e.surname, e.fname, e.oname
										FROM admission d
										INNER JOIN department dd ON dd.sn = d.dept_id
										LEFT JOIN enrollee e ON e.hospital_no = d.hospital_no
										WHERE d.adm_status = '3'
										ORDER BY d.date_admit DESC
									");
									$stmt->execute();
									?>
									<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size:14px;">
										<thead>
											<tr>
												<th>No</th>
												<th>Hosp. No</th>
												<th>Name</th>
												<th>Adm. By</th>
												<th>Room/Ward</th>
												<th>Floor</th>
												<th>Dept.</th>
												<th>Adm. Date</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$n = 1;
											while ($roww = $stmt->fetch()) {
											?>
												<tr>
													<td><?= $n; ?></td>
													<td><?= htmlspecialchars($roww['hospital_no']); ?></td>
													<td><?= htmlspecialchars($roww['surname'] . ', ' . $roww['fname'] . ' ' . $roww['oname']); ?></td>
													<td><?= htmlspecialchars($roww['doc_incharge']); ?></td>
													<td><?= htmlspecialchars($roww['room_bed']); ?></td>
													<td><?= htmlspecialchars($roww['floor']); ?></td>
													<td><?= htmlspecialchars($roww['department']); ?></td>
													<td><?= date('d, M h:i a', strtotime($roww['date_admit'])); ?></td>
													<td>
														<a href="pacct.php?emr=<?= urlencode($roww['hospital_no']); ?>" class="btn btn-sm btn-primary">View</a>
													</td>
												</tr>
											<?php $n++;
											} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>


				<?php include("../inc/footer.php"); ?>

			</div>
		</div>

		<div class="modal inmodal fade" id="patient_search_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Patient Search Modal</h4>
					</div>
					<div class="modal-body" id="patient_search_body">

					</div>
				</div>
			</div>
		</div>



		<div class="modal inmodal fade bannerformmodal" id="bannerformmodal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<!--<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Open Patient Account</h4>
			</div>-->

					<div class="modal-body">
						<form method="POST" action="pacct.php">

							<h4>Enter Hospital Number </h4>
							<hr>

							<div class="row">

								<div class="col-md-7">
									<div class="pull-left">
										<div class="input-group"><input type="text" name="search" placeholder="Search hosp.# " class="input-sm form-control"> <span class="input-group-btn">
												<button type="submit" name="go_search" class="btn btn-sm btn-primary"><i class="fa fa-search-plus"></i> &nbsp;Go </button> </span></div>
									</div>
								</div>




								<div class="col-md-5">
									<div class="pull-right">

										<a href="index.php" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> &nbsp; Close</a>
									</div>
								</div>


							</div>

							<br><br>



						</form>
					</div>


				</div>
			</div>
		</div>






		<?php include('../modal_lock.php'); ?>
		<?php include('mdl.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>




		<script>
			$(document).ready(function() {
				var input = document.getElementById("search");
				input.addEventListener("keyup", function(event) {
					if (event.keyCode === 13) {
						event.preventDefault();
						document.getElementById("apply_action").click();
					}
				});
			});

			function search_patient_ext() {
				//alert();
				var search_ext = document.getElementById("search_ext").value;
				window.location = 'pacct.php?emr=' + search_ext;
			}


			function search_patient(target) {

				if (target == 'Billing_Ex_sales') {
					var two = '_2';
					var text = document.getElementById("search_2").value;
				} else {
					var two = '';
					var text = document.getElementById("search").value;
				}


				document.getElementById('apply_action' + two).innerHTML = 'Wait ..';
				document.getElementById("apply_action" + two).disabled = true;

				///var search_detials = document.getElementById("search").value;

				let search_detials = text.replace(/^\s+|\s+$/gm, '');
				let length = search_detials.length;

				if (length < 3 && target == 'Billing_Ex_sales') {
					toastr.error('Search must be more than 3 characters', 'Invalid Data ', {
						timeOut: 9000
					});
					document.getElementById('apply_action' + two).innerHTML = 'Apply Search';
					document.getElementById("apply_action" + two).disabled = false;
					//	window.location = 'index.php';
					//exit;
					return 0;

				} else if (length < 4 && target != 'Billing_Ex_sales') {
					toastr.error('Search must be more than 4 characters', 'Invalid Data ', {
						timeOut: 9000
					});
					document.getElementById('apply_action' + two).innerHTML = 'Apply Search';
					document.getElementById("apply_action" + two).disabled = false;
					//	window.location = 'index.php';
					//exit;
					return 0;
				}


				$.ajax({
					url: "../admin/fetch_set2.php",
					method: "POST",
					data: {
						search_detials: search_detials,
						target: target
					},
					success: function(data) {

						///alert(data);

						setTimeout(function() {
							$("#overlay").fadeOut();
						}, 500);
						//toastr.info(data, 'Attention', {timeOut: 5000})//
						console.log(data)
						if (data.redirect != undefined) {
							/// index.php?presc&hos_no=000002 index.php?ptm=all/$in_patient

							window.location = 'pacct.php?emr=' + data.hosp_no;


						} else {

							////alert(data);

							document.getElementById('apply_action' + two).innerHTML = 'Apply Search';
							document.getElementById("apply_action" + two).disabled = false;

							if (data.trim() == 'NotFound') {
								toastr.error('Not match found', 'Error', {
									timeOut: 5000
								})
							} else {
								$('.modal-title').text('Search Patient');
								$('#patient_search_body').html(data);
								$('#patient_search_modal').modal('show');
							}
						}
					}

				});


			}


			function search_patient1233() {
				document.getElementById('apply_action').innerHTML = 'Wait ..';
				document.getElementById("apply_action").disabled = true;

				///var search_detials = document.getElementById("search").value;
				var text = document.getElementById("search").value;
				let search_detials = text.replace(/^\s+|\s+$/gm, '');

				let length = search_detials.length;

				if (length < 4) {
					toastr.error('Search must be more than 4 characters', 'Invalid Data ', {
						timeOut: 5000
					});
					document.getElementById('apply_action').innerHTML = 'Search';
					document.getElementById("apply_action").disabled = false;
					///exit;
					return 0;

				}
				$.ajax({
					url: "../admin/fetch_set2.php",
					method: "POST",
					data: {
						search_detials: search_detials,
						link: 'pacct.php?emr='
					},
					success: function(data) {

						setTimeout(function() {
							$("#overlay").fadeOut();
						}, 500);
						//toastr.info(data, 'Attention', {timeOut: 5000})//
						console.log(data)
						if (data.redirect != undefined) {
							/// index.php?presc&hos_no=000002
							window.location = 'pacct.php?emr=' + data.hosp_no;
						} else {

							document.getElementById('apply_action').innerHTML = 'Search';
							document.getElementById("apply_action").disabled = false;

							if (data.trim() == 'NotFound') {
								toastr.error('Not match found', 'Error', {
									timeOut: 5000
								})
							} else {
								$('.modal-title').text('Search Patient');
								$('#patient_search_body').html(data);
								$('#patient_search_modal').modal('show');
							}
						}
					}
				});



			}
			// });


			$(document).on('click', '.add_services', function() {
				// $('#view_lab_option_modal').modal('hide'); 


				var add_services_id_grp = $(this).attr("id");

				if (add_services_id_grp != '') {
					$.ajax({
						url: "fetch_set.php",
						method: "POST",
						data: {
							add_services_id_grp: add_services_id_grp
						},
						success: function(data) {

							$('.modal-title').text('Add Services/Items');

							$('#add_services_modal').modal('show');
							$('#add_services_body').html(data);
						}
					});
				}
			});

			$(document).on('click', '.add_services_item_del', function() {
				var service_item_no = $(this).attr("id");

				var res = service_item_no.split("__");

				if (service_item_no != '') {
					$.ajax({
						url: "delete.php",
						method: "POST",
						data: {
							service_item_no: res[0]
						},
						success: function(data) {

							var service_item_no = res[1] + '__' + res[2] + '__' + res[3];

							$.ajax({
								url: "fetch_set.php",
								method: "POST",
								data: {
									add_services_id: service_item_no
								},
								success: function(data) {

									$('#add_services_modal').modal('show');
									$('#add_services_body').html(data);
								}
							});

							// $('#view_lab_modal').modal('show');  
							//$('#view_lab_body').html(data); 
						}
					});
				}
			});


			$(document).on('click', '.edit_discount', function() {

				var edit_id = $(this).attr("id");
				/// var res = edit_id.split("__");

				$('.modal-title').text('Edit Discount & Charge' + edit_id);
				$('#discount_edit_modal').modal('show');

				$.ajax({
					url: "fetch.php",
					method: "POST",
					data: {
						edit_id: edit_id
					},
					dataType: "json",
					success: function(data) {

						$('#sn').val(data.sn);
						$('#discount_charge1').val(data.discount_charge);
						$('#mode1').val(data.percentage_flat);
						$('#mode_value1').val(data.percentage_flat_value);
						$('#how_long1').val(data.duration);
						$('#specify_count1').val(data.specify_count);
						$('#service_type1').val(data.apply_to_services);
						$('#old_service_type').val(data.apply_to_services);
						$('#discount_charge1').val(data.discount_charge);
						$('#history').val(data.history);

						$('.modal-title').text('Edit Discount & Charge');
						$('#discount_edit_modal').modal('show');
					}
				});
			});




			$('#discount_edit_body').on("submit", function(event) {
				event.preventDefault();

				// $('#discount_edit_modal').modal('hide');

				$.ajax({
					url: "insert.php",
					method: "POST",
					data: $('#discount_edit_body').serialize(),
					beforeSend: function() {
						$('#save').val("Updating");
					},
					success: function(data) {

						var service_type = $("#service_type1").val();

						location.href = "index.php?discount&sv"


					},
					complete: function() {
						$('#save').val("Updated");
					},
					error: function(data) {

						alert("Oops...", "Something went wrong :(", "error");
						//swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					}
				});
			});



			$(document).on('click', '.delete_confirm', function() {
				var delete_id = $(this).attr("id");

				var res = delete_id.split("__");
				var delete_id = res[0] + '__' + res[2] + '__' + res[3];

				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						delete_id: delete_id
					},
					success: function(data) {

						$('.modal-title').text('Delete Confirmation: ' + res[1]);
						$('#delete_confirmation_modal').modal('show');
						$('#delete_confirmation_body').html(data);
					}
				});
			});
		</script>

		<!-- Data Tables -->
		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

		<script>
			$('.dataTables-example').dataTable({
				responsive: true,
				"dom": 'T<"clear">lfrtip',
				"tableTools": {
					"sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
				}

			});
			$('#data_5 .input-daterange').datepicker({
				keyboardNavigation: false,
				forceParse: false,
				autoclose: true
			});


			<?php if (isset($_GET['Nex'])) { ?>
				toastr.error('<?php echo 'Patient number does not exist. Please try again '; ?>', 'Error', {
					timeOut: 5000
				})
			<?php } ?>
			<?php if (isset($_GET['err_selection'])) { ?>
				toastr.error('<?php echo 'Invalid Selection or Already Exist!!'; ?>', 'Error', {
					timeOut: 2000
				})
			<?php } ?>

			<?php if (isset($_GET['sx'])) { ?>
				toastr.success('<?php echo 'Payment was successful'; ?>', 'Successfully', {
					timeOut: 5000
				})
			<?php } ?>
		</script>

		<script src="../js/idle.js"></script>
</body>

</html>