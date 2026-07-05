<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

if (isset($_POST["edit_update_price"])) {



	$hospital_no = $_POST["emr"];
	$balance = $_POST["balance"];
	$sn = $_POST["sn"];
	$current_balance = $_POST["current_balance"];

	$updatttt = $db->prepare("UPDATE patient_billing SET bal = '$balance' WHERE sn = ? AND hospital_no = ? ");
	$deleted = $updatttt->execute(array($sn, $hospital_no));

	$desc = 'Edit Balance: ' . 'Old Bal.: ' . $current_balance . '/ New Bal: ' . $balance;
	$staff = $_SESSION['fullname'];
	$pid = $hospital_no;
	$pname = '';
	$action = 'Patient Deposit';
	include_once("../logs.php");
}




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

								<a href="index.php?report" class="btn btn-danger">Close</a>&nbsp;:&nbsp; &nbsp;:&nbsp;

								<a href="patients_balance.php?billed_account" class="btn btn-warning">Show Billed to Account</a> &nbsp;:&nbsp; &nbsp;:&nbsp;
								<a href="patients_balance.php?deposits" class="btn btn-success">Show Patients Deposit</a> &nbsp;:&nbsp;&nbsp;:&nbsp;
								<a href="patients_balance.php?credits" class="btn btn-danger">Show Patients Credits</a> &nbsp;:&nbsp;&nbsp;:&nbsp;
								<a href="patients_balance.php" class="btn btn-default">Show All Patient(s)</a>
								<hr>
								<div class="alert alert-success">
									<h4><?php

										if (isset($_GET['credits'])) {
											echo 'PATIENTS ON CREDITS';
										} elseif (isset($_GET['deposits'])) {
											echo 'PATIENTS DEPOSIT';
										} elseif (isset($_GET['billed_account'])) {
											echo 'PATIENTS BILLED TO ACCOUNTS';
										} else {
											echo 'ALL PATIENTS';
										}

										///echo $report_title .' // ' . $bank_name . ' // ' . $ref_value ; 
										?></h4>
								</div>
								<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">
									<thead>
										<tr>
											<th></th>
											<th>Patient No</th>
											<th>Name</th>
											<th>Total Billing (Paid)</th>
											<th><strong style="color: blue;">Balance (Deposit)</strong></th>
											<th><strong style="color: red;">Credit (Owing)</strong></th>
											<th><strong style="color: chocolate;">Billed To Account</strong></th>
											<th><strong>Attention</strong></th>
											<th>Last Visit</th>
											<th>.</th>
											<th>.</th>
										</tr>
									</thead>
									<tbody>


										<?php

										$grand_deposits = 0;
										$grand_credits = 0;
										$grand_billed = 0;

										$stmt_main = $db->query("SELECT distinct hospital_no FROM patient_billing");
										if ($stmt_main->rowCount() > 0) {

										?>

											<?php

											$total_credit = 0;
											$total_claim_credit = 0;
											$total_inv = 0;
											$sn;
											while ($row = $stmt_main->fetch(PDO::FETCH_ASSOC)) {

												$hospital_no = $row['hospital_no'];


												if (isset($_GET['credits'])) {
													$stmt_dep = $db->query("SELECT sn FROM patient_ap_services WHERE hospital_no='$hospital_no' and cr=1 order by sn DESC limit 1");
													if ($stmt_dep->rowCount() > 0) {
														$hospital_no = $row['hospital_no'];
													} else {
														$hospital_no = '';
													}
												} elseif (isset($_GET['billed_account'])) {
													$stmt_dep = $db->query("SELECT sn FROM patient_ap_services WHERE hospital_no='$hospital_no' and cr=2 order by sn DESC limit 1");
													if ($stmt_dep->rowCount() > 0) {
														$hospital_no = $row['hospital_no'];
													} else {
														$hospital_no = '';
													}
												} elseif (isset($_GET['deposits'])) {
													$stmt_dep = $db->query("SELECT bal FROM patient_billing WHERE hospital_no='$hospital_no' order by sn DESC limit 1");
													if ($stmt_dep->rowCount() > 0) {
														$rwDep = $stmt_dep->fetch(PDO::FETCH_ASSOC);
														$deposit = $rwDep['bal'];
													} else {
														$deposit = 0;
													}
													if ($deposit > 0) {
														$hospital_no = $row['hospital_no'];
													} else {
														$hospital_no = '';
													}
												} else {
													$hospital_no = $row['hospital_no'];


													$stmt_dep = $db->query("SELECT bal FROM patient_billing WHERE hospital_no='$hospital_no' order by sn DESC limit 1");
													if ($stmt_dep->rowCount() > 0) {
														$rwDep = $stmt_dep->fetch(PDO::FETCH_ASSOC);
														$deposit = $rwDep['bal'];
													} else {
														$deposit = 0;
													}
												}


												if ($hospital_no != '') {

													/// run other feeds

													$stmt_last = $db->query("SELECT patient_name,ap_date_time FROM apptm WHERE hospital_no='$hospital_no' order by sn DESC limit 1");
													$rwx = $stmt_last->fetch(PDO::FETCH_ASSOC);

													$stmt_bill = $db->query("SELECT sum(cr_amt) as total_bill_paid FROM patient_billing WHERE hospital_no='$hospital_no'");
													$rwxx = $stmt_bill->fetch(PDO::FETCH_ASSOC);

													$stmt_bill_cr = $db->query("SELECT sum(pay) as credits_bill FROM patient_ap_services WHERE hospital_no='$hospital_no' and cr=1");
													$rwCr = $stmt_bill_cr->fetch(PDO::FETCH_ASSOC);

													$stmt_billed = $db->query("SELECT sum(pay) as billed_account FROM patient_ap_services WHERE hospital_no='$hospital_no' and cr=2 and paystatus=1");
													$rwAcct = $stmt_billed->fetch(PDO::FETCH_ASSOC);

													$grand_deposits = $grand_deposits + $deposit;
													$grand_credits = $grand_credits + $rwCr['credits_bill'];
													$grand_billed = $grand_billed + $rwAcct['billed_account'];

											?>
													<tr>
														<td><?php echo $sn; //; 
															?></td>
														<td><?php echo $hospital_no; ?></td>
														<td><?php echo $rwx['patient_name']; ?></td>
														<td><?php echo number_format($rwxx['total_bill_paid']); ?></td>
														<td><?php echo number_format($deposit); //['bal']; 
															?>
														</td>
														<td><?php echo number_format($rwCr['credits_bill']); ?></td>
														<td><?php echo number_format($rwAcct['billed_account']); ?></td>
														<td>
															<?php

															if ($deposit > 0 and $rwCr['credits_bill'] > 0) {
																echo "<strong style='color:red;'>Pending</strong>";
															}

															?>
														</td>
														<td><?php echo date('d-m-Y h:i:s a', strtotime($rwx['ap_date_time'])); ?></td>
														<td><a href="../billing/pacct.php?emr=<?= $hospital_no; ?>" target="_blank" class="btn btn-info btn-xs">view</a></td>
														<td><input type="button" name="edit_price" value="Edit" data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-danger btn-xs edit_price_entry" />
														</td>
													</tr>


											<?php	}
											}

											?>


											<tr style="background-color:darkkhaki;  ">
												<td></td>
												<td></td>
												<td><strong><i>GRAND TOTAL</i></strong></td>
												<td></td>
												<td>
													<H3><?php echo number_format($grand_deposits); //['bal']; 
														?></H3>
												</td>
												<td>
													<H3><?php echo number_format($grand_credits); //['credits_bill']); 
														?></H3>
												</td>
												<td>
													<H3><?php echo number_format($grand_billed); //['billed_account']); 
														?></H3>
												</td>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
											</tr>

										<?php


										}


										?>











									</tbody>
									<tfoot>
										<tr>
											<th></th>
											<th>Patient No</th>
											<th>Name</th>
											<th>Total Billing (Paid)</th>
											<th><strong style="color: blue;">Balance (Deposit)</strong></th>
											<th><strong style="color: red;">Credit (Owing)</strong></th>
											<th><strong style="color: chocolate;">Billed To Account</strong></th>
											<th>Last Visit</th>
											<th>.</th>
											<th>.</th>
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