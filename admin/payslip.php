<?php
include_once('../Connections/Conn.php');
$hospital_detatils = "SELECT * FROM hospital_details LIMIT 1";
$hospital_detatils = $db->prepare($hospital_detatils);
$hospital_detatils->execute();
$hospital_detatils = $hospital_detatils->fetch(PDO::FETCH_ASSOC);



if (isset($_GET['Ecode']) or isset($_POST['send_to_email'])) {

	if (isset($_GET['Ecode'])) {
		$Ecode_ur = $_GET['Ecode'];
		$Ecode = $_GET['Ecode'];
	} elseif (isset($_POST['send_to_email'])) {

		$Ecode_ur = $_POST['Ecode_ur'];
		$Ecode = $_POST['Ecode'];
	} else {
		header("location:index.php");
	}
	$parts = explode("/", $Ecode);
	$Ecode = $parts['0'];
	$month = $parts['1'];
	$year = $parts['2'];
	$date_range = $parts['3'];
}


if (isset($_POST['send_to_email'])) {

	$month = $_POST['month'];
	$year = $_POST['year'];
	$date_range = $_POST['date_range'];

	include_once("payslip_send_body.php");
}

if (isset($_GET['delete'])) {

	$yes_del = $_GET['delete'];

	$parts = explode("/", $yes_del);
	echo $Ecode = $parts['0'];
	echo $date_range = $parts['1'];

	$update = "DELETE FROM hrpayslip WHERE date_range='$date_range' and ECode='$Ecode'";
	$db->exec($update);
}



?>







<div class="row">
	<div class="col-lg-12">
		<div class="ibox-content">

			<?php if (isset($_GET['delete'])) {	?>

				<strong style="color:#F33">Deleted Successfully. Close Now</strong>
				<hr>
				<button class="btn btn-danger btn-xs" type="submit" name="closeme" id="" onclick="window.close();"><i class="fa fa-times "></i> Close</button>


			<?php } else { ?>
				<div id="content">

					<div align="left" style="font:bold 14px 'Arial';"></div>

					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr>
							<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
							<td width="50%" align="right">
								<div style="font-size:18px; font:Verdana, Geneva, sans-serif""><strong><?php echo $hospital_detatils['name']  ?></strong></div> <br><div style=" font-size:14px"><?php echo $hospital_detatils['address'] ?> <br><br> <?php echo $hospital_detatils['phones'] ?> </div>
							</td>
						</tr>
					</table>
					<hr>

					<div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
						Pay Advice For <?php echo $month . ', ' . $year  . '<br>'; ?>
					</div>

					<hr>

					<table width="100%">
						<tr>
							<td width="70%" style="padding-right:20px; ">



								<table border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">
									<thead>
										<tr bgcolor="#CCCCCC">
											<th style="padding:5px; ">PAY ITEM</th>
											<th width="15%">AMOUNT</th>
											<!-- <th width="15%">TODATE</th> -->
										</tr>
									</thead>
									<tbody>
										<?php
										$T_E = 0;
										$T_D = 0;
										$B = 0;
										$stmt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range' and pay_head='B' and amount>0");
										if ($stmt->rowCount() > 0) { ?>

											<?php

											while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
												$B = $row['amount']; ?>
												<tr class="record">
													<td style="border-bottom: 1px solid #ddd; padding:5px;">&nbsp;&nbsp;<?php echo $row['descriptn']; ?></td>
													<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['amount']); ?></td>
													<!-- <td style="border-bottom: 1px solid #ddd;">
														<?php
														$stmt = $db->query("SELECT sum(amount) as basic_sal FROM hrpayslip WHERE ECode='$Ecode' and pay_head='B'");
														$rwc = $stmt->fetch(PDO::FETCH_ASSOC);
														echo number_format($rwc['basic_sal']); ?>
													</td> -->
												</tr>
										<?php }
										} ?>

										<tr>
											<td colspan="3">&nbsp;&nbsp;</td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;<strong>EARNINGS</strong> </td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>

										<?php
										$stmt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range' and pay_head='E' and amount>0");
										if ($stmt->rowCount() > 0) { ?>

											<?php
											$descriptn = '';
											$amount = '';
											$total_amount = '';
											$table_row = '';
											while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
												$T_E = $T_E + $row['amount']; ?>
												<tr class="record">
													<td style="border-bottom: 1px solid #ddd; padding:5px;">&nbsp;&nbsp;<?php echo $row['descriptn']; ?></td>
													<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['amount']); ?></td>
													<!-- <td style="border-bottom: 1px solid #ddd;"><?php
																									$desc = $row['descriptn'];
																									$stmtt = $db->query("SELECT sum(amount) as earn FROM hrpayslip WHERE ECode='$Ecode' and pay_head='E' and descriptn='$desc'");
																									$rwc = $stmtt->fetch(PDO::FETCH_ASSOC);
																									echo number_format($rwc['earn']); ?>

													</td> -->
												</tr>
											<?php
											} ?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd; padding:5px;  text-align:right">&nbsp;&nbsp;<strong>Total:</strong> </td>
												<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($T_E); ?></strong></td>
												<td style="border-bottom: 1px solid #ddd;"></td>
											</tr>

										<?php } ?>


										<tr>
											<td colspan="3">&nbsp;&nbsp;</td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;<strong>DEDUCTIONS</strong> </td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>

										<?php
										$stmt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$Ecode' and month='$month' and year='$year' and date_range='$date_range' and pay_head='D' and amount>0");
										if ($stmt->rowCount() > 0) { ?>

											<?php
											$descriptn = '';
											$amount = '';
											$total_amount = '';
											$table_row = '';
											while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
												$T_D = $T_D + $row['amount']; ?>
												<tr class="record">
													<td style="border-bottom: 1px solid #ddd; padding:5px; ">&nbsp;&nbsp;<?php echo $row['descriptn']; ?></td>
													<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['amount']); ?></td>
													<!-- <td style="border-bottom: 1px solid #ddd;">

														<?php
														$desc = $row['descriptn'];
														$stmtt = $db->query("SELECT sum(amount) as deduct FROM hrpayslip WHERE ECode='$Ecode' and pay_head='D' and descriptn='$desc'");
														$rwc = $stmtt->fetch(PDO::FETCH_ASSOC);
														echo number_format($rwc['deduct']); ?>
													</td> -->
												</tr>
											<?php } ?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd; padding:5px; text-align:right">&nbsp;&nbsp;<strong>Total:</strong> </td>
												<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($T_D); ?></strong></td>
												<td style="border-bottom: 1px solid #ddd;"></td>
											</tr>
										<?php } ?>

										<tr>
											<td colspan="3">&nbsp;&nbsp;</td>
										</tr>
										<tr>
											<td colspan="3">&nbsp;<strong></strong> </td>
										</tr>
										<tr>
											<td><strong>NET PAY:</strong> </td>
											<td colspan="2"><?php $netpay = ($B + $T_E) - $T_D;
															echo number_format($netpay); ?></td>
										</tr>

									</tbody>
								</table>

							</td>
							<td width="30%">
								<?php

								/// get the name
								$stmt = $db->query("SELECT e.FirstName , e.MiddleName, e.LastName,e.JoiningDate,e.Department,e.BankName,e.AccountNo,e.Designation, d.department as deptName FROM hremp as e left join admin_users as u on u.EmployeeCode=e.EmployeeCode left join department as d on e.Department = d.sn  WHERE e.EmployeeCode='$Ecode'");
								$rwxx = $stmt->fetch(PDO::FETCH_ASSOC);
								$emplname = $rwxx['LastName'] . ', ' . $rwxx['FirstName'] . ' ' . $rwxx['MiddleName'];
								?>
								<table class="table table-bordered" width="100%" style="font-size:14px">
									<tr>
										<td colspan="2"><strong><?php echo $emplname ?></strong></td>
									</tr>
									<tr>
										<td><strong>Emp No:</strong></td>
										<td width="60%"><?php echo $Ecode ?></td>
									</tr>

									<tr>
										<td colspan="2"><strong>Department:</strong><BR><?php echo $rwxx['deptName'] ?></td>
									</tr>
									<tr>
										<td colspan="2"><strong>Designation:</strong><BR><?php echo $rwxx['Designation'] ?></td>
									</tr>

									<tr>
										<td><strong>Date Engaged</strong>:</td>
										<td width="60%"><?php echo date("d M, Y", strtotime($rwxx['JoiningDate'])); ?></td>
									</tr>
									<tr>
										<td colspan="2"><strong>Pay Point:</strong><br><?php echo $rwxx['BankName'] . ' [ ' . $rwxx['AccountNo'] . ' ]'; ?></td>
									</tr>
								</table>

							</td>
						</tr>
					</table>

				</div>

				<div class="title-action">

					<form action="index.php?Ecode=<?php echo $Ecode . '/' . $month . '/' . $year . '/' . $date_range . '&lip'; ?>" method="POST">

						<?php if ($done == '1') { ?>
							<strong style="color:#00C">Payslip Sent Successfully.</strong>
						<?php } ?>


						<?php if (isset($_GET['del'])) { ?>
							<a href="index.php?lip&delete=<?php echo $Ecode . '/' . $date_range ?>" class="btn btn-warning btn-xs"> Yes Delete </a> &nbsp;&nbsp; | &nbsp;&nbsp;
							<button class="btn btn-danger btn-xs" type="submit" name="closeme" id="" onclick="window.close();"><i class="fa fa-times "></i> Close</button>

						<?php } else { ?>
							<button class="btn btn-success btn-xs" type="submit" name="send_to_email" id="">Send to staff Webmedic mailbox</button>
							<button onclick="printDiv('content')" class="btn btn-primary btn-xs"><i class="fa fa-print"></i> Print/ Save Report </button>

							<?php if (isset($_GET['one'])) { ?>
								<a href="index.php?slp=<?php echo $Ecode; ?>" class="btn btn-danger btn-xs"><i class="fa fa-times "></i> Close </a>
							<?php } else { ?>
								<button class="btn btn-danger btn-xs" type="submit" name="closeme" id="" onclick="window.close();"><i class="fa fa-times "></i> Close</button>
							<?php } ?>

						<?php } ?>

						<input type="hidden" name="month" value="<?php echo $month; ?>" />
						<input type="hidden" name="year" value="<?php echo $year; ?>" />
						<input type="hidden" name="date_range" value="<?php echo $date_range; ?>" />
						<input type="hidden" name="Ecode_ur" value="<?php echo $Ecode_ur; ?>" />
						<input type="hidden" name="Ecode" value="<?php echo $Ecode; ?>" />
					</form>
				</div>

				<script>
					function printDiv(id) {
						var content = document.getElementById(id).innerHTML;
						var popupWindow = window.open('', '_blank', 'width=700,height=800');
						popupWindow.document.open();
						popupWindow.document.write('<html><head><title>' + document.title + '</title>');
						// Reference the external stylesheet from the main page
						popupWindow.document.write('<link rel="stylesheet" type="text/css" href="' + window.location.href + '">');
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


		</div>

	<?php } ?>
	</div>
</div>