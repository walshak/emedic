<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
?>
<!DOCTYPE html>
<html>




<?php include("../inc/header.php"); ?>


</head>

<body class="fixed-navigation">

	<div id="wrapper">
		<?php include("../inc/nav_admin_side_bar.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">
			<?php include("../inc/nav_header.php"); ?>

			<?php include("../inc/billing_side_bar.php"); ?>

			<div class="wrapper wrapper-content">





				<div class="row">


					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Report Panel</h5>
							</div>

							<div class="ibox-content" id="content">

								<h2><u>GRAND TOTAL DISCOUNTS SINCE INCEPTION: </u>
									<?php

									$stmt_main = $db->query("SELECT sum(discount) as discount FROM patient_ap_services where paystatus=1");
									if ($stmt_main->rowCount() > 0) {
										$row = $stmt_main->fetch(PDO::FETCH_ASSOC);
										echo number_format($row['discount']);
									}

									?></h2>



								<table width="100%">
									<tr>

										<td width="30%">

											<form action="discount.php" method="post">
												<div class="form_sep" id="bill_account"><br>
													<label for="reg_input_no" class=""><strong style="color: #F00">Sort by Patient</strong></label>
													<select name="sort_by_discount" id="auth_staff" class="form-control" required>
														<option value=''>Select...</option>
														<?php
														$stmt_bnk = $db->query("SELECT distinct d.individual_group_name,d.individual_group_no 
FROM patient_discount as d 
inner join patient_ap_services as p

on p.hospital_no = d.individual_group_no 
where paystatus=1 and (discount>0 or add_charge>0) and individual_group='individual'");
														while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row_rstbank["individual_group_no"]; ?>"><?php echo $row_rstbank["individual_group_name"]; ?></option>
														<?php } ?>
													</select>


												</div>


												<div class="form_sep">
													<a href="index.php?report" class="btn btn-danger">Close</a>
													&nbsp; &nbsp;
													<button class="btn btn-primary btn" type="submit" name="sort_by_patient">Apply</button>
												</div>

											</form>

										</td>
										<td width="2%">&nbsp;</td>
										<td width="25%" align="">


											<form action="discount.php" method="post">
												<div class="form_sep" id="bill_account"><br>
													<label for="reg_input_no" class=""><strong style="color: #F00">Sort by Responsible</strong></label>
													<select name="sort_by_staff" id="auth_staff" class="form-control" required>
														<option value=''>Select...</option>
														<?php
														$stmt_bnk = $db->query("SELECT distinct prepared_by
FROM patient_discount as d 
inner join patient_ap_services as p

on p.hospital_no = d.individual_group_no 
where paystatus=1 and (discount>0 or add_charge>0) and individual_group='individual'");
														while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row_rstbank["prepared_by"]; ?>"><?php echo $row_rstbank["prepared_by"]; ?></option>
														<?php } ?>
													</select>


												</div>


												<div class="form_sep">
													<button class="btn btn-primary btn" type="submit" name="sort_by_responsible">Apply</button>
												</div>

											</form>


										</td>
										<td width="2%">&nbsp;</td>
										<td width="38%">

											<form action="discount.php" method="post">

												<div class="form_sep" id="">
													<label class="font-noraml"><strong>Select Dates Report </strong></label>
													<div class="input-daterange input-group" id="" required>
														<input type="date" class="form-control" name="start" value="<?php echo date("Y-m-d"); ?>" />
														<span class="input-group-addon">to</span>
														<input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
													</div>
												</div>

												<div class="form_sep">


													<button class="btn btn-primary btn" type="submit" name="sort_by_dates">Apply Dates</button>
													&nbsp;:&nbsp;
													<a href="discount.php" class="btn btn-default">Show All</a>

													&nbsp;:&nbsp;
													<a href="discount.php?on-going" class="btn btn-danger">Show On-going</a>
												</div>

											</form>

										</td>

									</tr>


								</table>



								<?php

								if (isset($_POST['sort_by_responsible'])) {
									$sort_by_staff = $_POST['sort_by_staff'];
									$search4 = " and prepared_by='$sort_by_staff'";
								} else {
									$search4 = '';
								}
								if (isset($_GET['on-going'])) {
									$search3 = " and status='On-going'";
								} else {
									$search3 = '';
								}




								if (isset($_POST['sort_by_dates'])) {

									$start = $_POST['start'];
									$end = $_POST['end'];

									$search2 = " and date(transact_date) between '$start' and '$end'";
								} else {

									$search2 = '';
								}




								if (isset($_POST['sort_by_patient'])) {



									$individual_group_no = $_POST['sort_by_discount'];
									$search = " and individual_group_no='$individual_group_no'";

									$stmt_main = $db->query("SELECT * FROM patient_discount 
		where individual_group_no='$individual_group_no'");
									if ($stmt_main->rowCount() > 0) {
										$rwx = $stmt_main->fetch(PDO::FETCH_ASSOC);
								?>





										<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">
											<tr>
												<td>Hospital No</td>
												<td>Name</td>
												<td>Discount/Charge</td>
												<td>Flat/Percent</td>
												<td>Flat/Pecent Value</td>
												<td>Duration</td>
											</tr>

											<tr>
												<td><?= $rwx['individual_group_no']; ?></td>
												<td><?= $rwx['individual_group_name']; ?></td>
												<td><?= $rwx['discount_charge']; ?></td>
												<td><?= $rwx['percentage_flat']; ?></td>
												<td><?= $rwx['percentage_flat_value']; ?></td>
												<td><?= $rwx['duration']; ?></td>
											</tr>



											<tr>
												<td>No of count</td>
												<td>Services</td>
												<td>Count Balance</td>
												<td>Set By</td>
												<td>Date</td>
												<td>Status</td>
											</tr>

											<tr>
												<td><?= $rwx['specify_count']; ?></td>
												<td><?= $rwx['apply_to_services']; ?></td>
												<td><?= $rwx['count_bal']; ?></td>
												<td><?= $rwx['setby']; ?></td>
												<td><?= $rwx['status_date']; ?></td>
												<td><strong style="color: red; "><?= $rwx['status']; ?></strong></td>
											</tr>

										</table>
										<br>
										<strong>Remarks</strong><br>
										<p style="font-size: 15px; "><?= $rwx['history']; ?></p>
										<hr>

									<?php	}
									?>



								<?php } else {

									$search = '';
								} ?>



								<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">
									<thead>
										<tr>
											<th></th>
											<th>Patient No</th>
											<th>Name</th>
											<th>Item/Service</th>
											<th>Hosp. Price</th>
											<th>Discount</th>
											<th>Charge</th>
											<th>Paid</th>
											<th>Responsible</th>
											<th>Date</th>
										</tr>
									</thead>
									<tbody>


										<?php


										$stmt_main = $db->query("SELECT d.*, 
p.discount, p.add_charge, p.transact_date, p.item_services,p.prepared_by,p.hosp_price,p.pay 
FROM 
patient_ap_services as p 
inner join patient_discount as d 
on p.hospital_no = d.individual_group_no 
where paystatus=1 and (discount>0 or add_charge>0) and individual_group='individual' $search $search2 $search3 $search4");
										if ($stmt_main->rowCount() > 0) {

										?>

											<?php

											$total_discount = 0;
											$total_charge = 0;;
											$sn;
											while ($rwx = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
												$total_discount = $total_discount +	$rwx['discount'];
												$total_charge = $total_charge +	$rwx['add_charge'];

											?>
												<tr>
													<td><?php echo $sn; //; 
														?></td>

													<td><?php echo $rwx['individual_group_no']; ?></td>
													<td><?php echo $rwx['individual_group_name']; ?></td>
													<td><?php echo $rwx['item_services']; ?></td>
													<td><?php echo number_format($rwx['hosp_price']); ?></td>
													<td><?php echo number_format($rwx['discount']); ?></td>
													<td><?php echo number_format($rwx['add_charge']); ?></td>
													<td><?php echo number_format($rwx['pay']); ?></td>
													<td><?php echo $rwx['prepared_by']; ?></td>
													<td><?php echo date('d-m-Y H:i:s a', strtotime($rwx['transact_date'])); ?></td>

												</tr>


											<?php } ?>


											<tr style="background-color:cornsilk;  ">
												<td></td>
												<td></td>
												<td></td>
												<td></td>
												<td><strong><i>GRAND TOTAL</i></strong></td>
												<td>
													<h3><?php echo number_format($total_discount); ?></h3>
												</td>
												<td>
													<h3><?php echo number_format($total_charge); ?></h3>
												</td>
												<td></td>
												<td></td>
												<td></td>
											</tr>

										<?php


										} else {
										}


										?>











									</tbody>
									<tfoot>
										<tr>
											<th></th>
											<th>Patient No</th>
											<th>Name</th>
											<th>Item/Service</th>
											<th>Hosp. Price</th>
											<th>Discount</th>
											<th>Charge</th>
											<th>Paid</th>
											<th>Responsible</th>
											<th>Date</th>
										</tr>
									</tfoot>
								</table>
							</div>



						</div>
					</div>


				</div>




				<?php include("../inc/footer.php"); ?>

			</div>
		</div>
		<div class="modal inmodal fade" id="edit_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-sm">
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




		<?php include("../inc/footer_scripts.php"); ?>

		<!-- Data Tables -->
		<script src="../js/jquery-3.1.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
		<script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

		<script src="../js/plugins/dataTables/datatables.min.js"></script>

		<!-- Custom and plugin javascript -->
		<script src="../js/inspinia.js"></script>
		<script src="../js/plugins/pace/pace.min.js"></script>

		<!-- Page-Level Scripts -->
		<script>
			$(document).on('click', '.edit_price_entry', function() {
				var editprice_id = $(this).attr("id");

				if (editprice_id != '') {
					$.ajax({
						url: "fetch_set_edit_patient_bal.php",
						method: "POST",
						// data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
						data: {
							editprice_id: editprice_id
						},

						success: function(data) {

							$('.modal-title').text('Edit Deposit');

							$('#edit_price_body').html(data);
							$('#edit_price_modal').modal('show');
						}
					});
				}
			});




			$(document).ready(function() {
				$('.dataTables-example').DataTable({
					pageLength: 25,
					responsive: true,
					dom: '<"html5buttons"B>lTfgitp',
					buttons: [{
							extend: 'copy'
						},
						{
							extend: 'csv'
						},
						{
							extend: 'excel',
							title: 'ExampleFile'
						},
						{
							extend: 'pdf',
							title: 'ExampleFile'
						},

						{
							extend: 'print',
							customize: function(win) {
								$(win.document.body).addClass('white-bg');
								$(win.document.body).css('font-size', '10px');

								$(win.document.body).find('table')
									.addClass('compact')
									.css('font-size', 'inherit');
							}
						}
					]

				});

			});
		</script>


</body>

</html>