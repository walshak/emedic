<?php

if (isset($_POST['hos_no'])) {
	$hos_no = $_POST['hos_no'];
	$getvalue = $_POST['getvalue'];
	$auth_code = $_POST['$auth_code'];
	$cpt = $_POST['cpt'];

	if (isset($_POST['plan_show']) and $_POST['plan_show'] != '') {
		$plan_show = 1;
	} else {
		$plan_show = 0;
	}



	$stmt = $db->query("SELECT e.*,i.insurance_name,i.interest,i.payment_mode,i.services_access,i.insurance_type FROM enrollee as e 
	INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no 
	WHERE hospital_no='$hos_no' and status='active'");
	if ($stmt->rowCount() == 0) {
		header("location:index.php?Invalid_details");
	}
	$row_rstSelect_d = $stmt->fetch(PDO::FETCH_ASSOC);

	$stmt2 = $db->query("SELECT * FROM apptm WHERE hospital_no='$hos_no' ORDER BY sn DESC LIMIT 1");
	if ($stmt2->rowCount() > 0) {
		$row_rstSelect = $stmt2->fetch(PDO::FETCH_ASSOC);
		$app_no = $row_rstSelect['appt_no'];
		$date_ap = $row_rstSelect['date_ap'];
		$ap_time = $row_rstSelect['ap_time'];
		$ap_type = $row_rstSelect['ap_type'];
		$auth_code = $row_rstSelect['auth_code'];
	} else {
		$date_ap = '';
	}
}

if ($hos_no == '') {
	header("location:index.php?Invalid_details");
}
?>


<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Fee for service claim</h5>
			</div>

			<div class="ibox-content">

				<div class="deposit_reciept_customer">

					<div align="center">
						<div style="font:bold 18px 'Arial';"><?php echo $row_rstSelect_d['insurance_name']; ?></div>

						<?php echo $row_rstSelect_d['addr']; ?> <br><br>
						<div style="font:bold 14px 'Arial';"><?php echo 'FEE FOR SERVICE CLAIMS REPORT'; ?></div>
						<div style="font:bold 18px 'Arial'; width:200px; position: absolute;right: 0px;"><?php
																											echo 'INV:' . $app_no; ?></div>
						<br></br>
					</div>
					<div align="left" style="font:bold 14px 'Arial';">
					</div>
					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr>
							<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
							<td width="50%" align="right"><img src="<?php if (file_exists(enrollee_p . $hos_no . '.' . 'jpg')) {
																		echo enrollee_p . $hos_no . '.' . 'jpg';
																	} else {
																		echo '../img/user_avatar_lg.png';
																	} ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar"></td>
						</tr>

					</table><br>
					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr bgcolor="#FFCC66" style="font-weight:100">
							<td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
							<td style="font:bold 14px 'Arial';" width="30%">Insurance Coverage</td>
							<td style="font:bold 14px 'Arial';" width="30%">Patient Name: </td>
						</tr>
						<tr>
							<td><?php echo '<i><strong>Hospital Number:</strong></i>' . ' ' . $row_rstSelect_d['hospital_no']; ?></td>
							<td><?php echo $row_rstSelect_d['nhis_no'] . ' (' . $row_rstSelect_d['insurance_type'] . ')'; ?></td>
							<td><?php echo $row_rstSelect_d['surname'] . ', ' . $row_rstSelect_d['fname'] . ' ' . $row_rstSelect_d['oname']; ?></td>
						</tr>
						<tr bgcolor="#FFCC66">
							<td style="font:bold 14px 'Arial';">Age (Yrs/Mnts): </td>
							<td style="font:bold 14px 'Arial';">Sex: </td>
							<td style="font:bold 14px 'Arial';">Report Dates: </td>
						</tr>
						<tr>
							<td><?php echo $row_rstSelect_d['age']; ?></td>
							<td><?php echo ucfirst($row_rstSelect_d['gender']); ?></td>
							<td><?php
								$from = $_POST['from'];
								$to = $_POST['to'];
								echo date('d M, Y', strtotime($from)) . ' - ' . date('d M, Y', strtotime($to));
								?></td>
						</tr>
					</table>

					<br></br>
					<div align="center" style="font:bold 18px 'Arial';"><?php echo 'Description of Claims'; ?></div>


					<?php

					$total_hmo_amt = 0;
					$hos_no = $_POST['hos_no'];
					$from = $_POST['from'];
					$to = $_POST['to'];
					$getvalue = $_POST['getvalue'];  ?>



					<?php
					//// BEFORE YOU START LOOPING LOOK FOR NEW FILE ////

					$qury_file = $db->query("SELECT date_entry, claim_amt 
	FROM patient_ap_services WHERE hospital_no='$hos_no' and item_services='New File' 
	and paystatus='1' and process_claim='1' and claim_amt>0 and date(date_entry) between '$from' AND '$to'");
					if ($qury_file->rowCount() > 0) { ?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th width="12%">Date</th>
									<th> Service Name </th>
									<th width="17%"> Category </th>
									<th width="10%"> Price </th>
									<th width="6%"> Qty </th>
									<th width="10%"> Amount(=N=) </th>
								</tr>
							</thead>
							<tbody>
								<?php

								$row_s = $qury_file->fetch(PDO::FETCH_ASSOC);

								?>
								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;"><?php echo date('d, M Y', strtotime($row_s['date_entry'])); ?></td>
									<td style="border-bottom: 1px solid #ddd;">New File</td>
									<td style="border-bottom: 1px solid #ddd;">Consultation</td>
									<td style="border-bottom: 1px solid #ddd;"><?php
																				$unit_price = $row_s['claim_amt'];
																				echo number_format($unit_price, 2, '.', ',');
																				?></td>
									<td style="border-bottom: 1px solid #ddd;">1</td>
									<td style="border-bottom: 1px solid #ddd; text-align:right" bgcolor="#999999">
										<?php
										echo '<strong>' . number_format($row_s['claim_amt'], 2, '.', ',') . '</strong>';
										$New_file_amt = $row_s['claim_amt'];
										//$total_hmo_amt=$total_hmo_amt+$row_s['claim_amt'];
										//$sub_total_hmo_amt=$sub_total_hmo_amt+$row_s['claim_amt'];
										?>
									</td>
								</tr>
							</tbody>
						</table>

						<?php
					} else {
						$New_file_amt = 0;
					}
					/////==================================================================



					// Initialize totals
					$total_hmo_amt = 0;
					$New_file_amt = 0;  // assuming this should be initialized

					// Use prepared statements for main query
					$stmt_appt = $db->prepare("
								SELECT DISTINCT date_entry, app_no 
								FROM patient_ap_services 
								WHERE hospital_no = :hos_no 
								AND (serv_group = 'Consultation' OR serv_group = 'Medical Services')
								AND paystatus = '1' 
								AND item_services != 'New File' 
								AND claim_amt > 0 
								AND DATE(date_entry) BETWEEN :from AND :to 
								ORDER BY date_entry
							");
					$stmt_appt->execute([':hos_no' => $hos_no, ':from' => $from, ':to' => $to]);

					if ($stmt_appt->rowCount() > 0) {
						$data = $stmt_appt->fetchAll(PDO::FETCH_ASSOC);

						foreach ($data as $row_app) {
							$appt_date = date('Y-m-d', strtotime($row_app['date_entry']));
							$appt_no = $row_app['app_no'];

							// Get appointment details safely
							$stmt25 = $db->prepare("SELECT date_ap, auth_code, ap_type, insurance FROM apptm WHERE appt_no = :appt_no");
							$stmt25->execute([':appt_no' => $appt_no]);

							if ($stmt25->rowCount() > 0) {
								$RWX = $stmt25->fetch(PDO::FETCH_ASSOC);
								$auth_code = $RWX['auth_code'];
								$ap_type = $RWX['ap_type'];
								$insurance = $RWX['insurance'];
							} else {
								$stmt25 = $db->prepare("SELECT insurance FROM enrollee WHERE hospital_no = :hos_no");
								$stmt25->execute([':hos_no' => $hos_no]);
								$RWX = $stmt25->fetch(PDO::FETCH_ASSOC);
								$auth_code = '0000';
								$ap_type = 'N/A';
								$insurance = $RWX['insurance'];
							}

							// Get discharge date
							$stmt_ap = $db->prepare("
								SELECT date_entry 
								FROM patient_ap_services 
								WHERE hospital_no = :hos_no AND app_no = :appt_no 
								ORDER BY date_entry DESC 
								LIMIT 1
							");
							$stmt_ap->execute([':hos_no' => $hos_no, ':appt_no' => $appt_no]);

							if ($stmt_ap->rowCount() > 0) {
								$row_last_date = $stmt_ap->fetch(PDO::FETCH_ASSOC);
								$dschgr_date = date("Y-m-d", strtotime($row_last_date['date_entry']));
							} else {
								$dschgr_date = $appt_date;
							}

							// Get distinct services for this appointment
							$stmt3 = $db->prepare("
									SELECT DISTINCT item_services 
									FROM patient_ap_services 
									WHERE hospital_no = :hos_no 
									AND paystatus = '1' 
									AND process_claim = '1' 
									AND item_services != 'New File' 
									AND claim_amt > 0 
									AND app_no = :appt_no 
									AND DATE(date_entry) BETWEEN :appt_date AND :dschgr_date
									ORDER BY sn
								");
							$stmt3->execute([
								':hos_no' => $hos_no,
								':appt_no' => $appt_no,
								':appt_date' => $appt_date,
								':dschgr_date' => $dschgr_date
							]);

							if ($stmt3->rowCount() > 0) { ?>
								<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
									<thead>
										<tr bgcolor="#CCCCCC">
											<th width="12%">Date</th>
											<th>Service Name</th>
											<th width="17%">Category</th>
											<th width="10%">Price</th>
											<th width="6%">Qty</th>
											<th width="10%">Amount(=N=)</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$data = $stmt3->fetchAll(PDO::FETCH_ASSOC);
										$sub_total_hmo_amt = 0;
										foreach ($data as $row) {

											$item_services = $row['item_services'];

											// Fetch aggregated claim and qty for the service
											$qury = $db->prepare("
													SELECT *, SUM(claim_amt) AS total_claim, SUM(qty) AS total_qty 
													FROM patient_ap_services 
													WHERE hospital_no = :hos_no 
													AND app_no = :appt_no 
													AND item_services = :item_services 
													AND item_services != 'New File'  
													AND paystatus = '1' 
													AND process_claim = '1' 
													AND claim_amt > 0 
													AND DATE(date_entry) BETWEEN :appt_date AND :dschgr_date
													GROUP BY serv_group
												");
											$qury->execute([
												':hos_no' => $hos_no,
												':appt_no' => $appt_no,
												':item_services' => $item_services,
												':appt_date' => $appt_date,
												':dschgr_date' => $dschgr_date
											]);
											$row_s = $qury->fetch(PDO::FETCH_ASSOC);

											$unit_price = $row_s['total_claim'] / $row_s['total_qty'];
											$total_hmo_amt += $New_file_amt + $row_s['total_claim'];
											$sub_total_hmo_amt += $New_file_amt + $row_s['total_claim'];
											$New_file_amt = 0;




										?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd;"><?php echo date('d, M Y', strtotime($row_s['date_entry'])); ?></td>
												<td style="border-bottom: 1px solid #ddd;">
													<?php
													echo $row['item_services'];
													if ($row_s['serv_group'] == 'Pharmacy' && $row_s['remarks'] != 'pharmacist' && $plan_show == 1) {
														echo ' [' . $row_s['remarks'] . ']';
													}
													?>
												</td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row_s['cat_type']); ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($unit_price, 2, '.', ','); ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $row_s['total_qty']; ?></td>
												<td style="border-bottom: 1px solid #ddd; text-align:right" bgcolor="#999999">
													<strong><?php echo number_format($row_s['total_claim'], 2, '.', ','); ?></strong>
												</td>
											</tr>
										<?php } ?>

										<tr>
											<td style="border-bottom: 1px solid #ddd; text-align:left" colspan="2">
												<strong style="font-size: 11px; color: #222222;">
													<?php echo 'Presentation Dates: ' . date('d M, Y', strtotime($appt_date)) . ' - ' . date('d M, Y', strtotime($dschgr_date)); ?>
												</strong>
											</td>
											<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2">
												<strong style="font-size: 11px; color: #222222;">
													<?php
													if (!empty($auth_code)) {
														if (strlen($auth_code) <= 6) {
															echo 'No Authorization Code';
															$auth_code_status = 1;
														} else {
															echo '<b>AUTH. CODE: </b>' . htmlspecialchars($auth_code);
														}
													}
													?>
												</strong>
											</td>
											<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2">
												<strong style="font-size: 15px; color: #222222;">
													&#8358;<?php echo number_format($sub_total_hmo_amt, 2, '.', ','); ?>
												</strong>
											</td>
										</tr>
									</tbody>
								</table>
								<br>
					<?php
							} // end if stmt3 rowCount > 0
						} // end foreach data
					} else {
						// No appointments found
					}
					?>

					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr>
							<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2"><strong style="font-size: 10px; color: #222222;">
									Total Visit(s): <?php echo $CONSULTATION_COUNT; ?>

								</strong></td>

							<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2"><strong style="font-size: 10px; color: #222222;">
									Bill to: <?php echo $row_rstSelect_d['insurance_name']; ?>

								</strong></td>
							<td style="border-bottom: 1px solid #ddd; text-align:right" colspan="2"><strong style="font-size: 25px; color: #222222;">
									&#8358;<?php echo  number_format($total_hmo_amt, 2, '.', ','); ?>
								</strong></td>
						</tr>
					</table>

					<br></br>

					<?php if ($row_rstSelect_d['vip'] == 0) { ?>
						<div align="center" style="font:bold 18px 'Arial';"><?php echo 'Medical Reports'; ?></div>

						<table class="table table-striped table-bordered">
							<tr>

								<th width="45%" style="border-bottom: 1px solid #ddd;">Complaints/Diagnosis</th>
								<th width="45%" style="border-bottom: 1px solid #ddd;">Other Notes</th>
							</tr>


							<?php
							$start = $_POST['from'];
							$end = $_POST['to'];

							$effect_date = $start;

							/////////// looping start here
							$stmt = $db->query("SELECT notes_type,notes,prepared_by,date_entry FROM notes WHERE hospital_no='$hos_no' and date(date_entry) between '$start' and '$end' order by date_entry limit 30");
							if ($stmt->rowCount() > 0) {
								$notes = 1;
								$C = '';
								$D = '';
								$plan = '';
								$Notes = '';
								$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
								foreach ($data as $key => $row) {
									///echo $row['notes'];



									if ($row['notes_type'] == 'C' or $row['notes_type'] == 'D') {
										$C = $C . $row['notes'] . '<br>' . $row['prepared_by'] . '<br>' . date('d M,y', strtotime($row['date_entry'])) . '<hr>';
									}
									if ($row['notes_type'] != 'C' and $row['notes_type'] != 'D') {
										$D = $D . $row['notes'] . '<br>' . $row['prepared_by'] . '<br>' . date('d M,y', strtotime($row['date_entry'])) . '<hr>';
									}



									if ($row['notes_type'] == 'plan') {
										$plan = $plan . $row['notes'] . '<br>' . $row['prepared_by'] . '<br>' . date('d M,y', strtotime($row['date_entry'])) . '<hr>';
									}

									$effect_date = $row['date_entry'];
								}
							} else {
								$notes = 0;
							}
							if ($notes == '1') { ?>

								<tr>

									<td style="border: 1px solid #ddd; padding:5px; "><?php if ($C != '') {
																							echo $C;
																						} ?></td>
									<td style="border: 1px solid #ddd; padding:5px; "><?php if ($D != '') {
																							echo $D;
																						} ?></td>
								</tr>
								<?php if ($plan != '' and $plan_show == 1) {	?>
									<tr>
										<td style="border: 1px solid #ddd; padding:5px; "><strong>Medication/Plan</strong></td>
										<td style="border: 1px solid #ddd; padding:5px; " colspan="2"><?php echo $plan; ?></td>
									</tr>
								<?php } ?>

							<?php
							}

							$C = '';
							$D = '';
							$plan = '';
							$effect_date = date("Y-m-d", strtotime($effect_date . " +1 day"));
							// }
							?>
						</table>

					<?php } ?>
				</div>





				<div class="form_sep" align="right">
					<div class="pull-right" style="margin-right:100px;">
						<!-- <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary" value="Print"/>-->
						<button onclick="printdeposit('deposit_reciept_customer')">Print</button>

					</div>
				</div>



				<a href="index.php?claims=<?php echo $hos_no . '&A=' . '/' . $from . '/' . $to . '/' . '' . '/' . $cpt; ?>" style="font-size:20px;"><button class="btn btn-danger btn-sm"><i class="icon-print"></i> Close</button></a>




			</div>

		</div>
	</div>

</div>




<script>
	function printdeposit(deposit_reciept) {
		var printWindow = window.open('', 'PRINTOUT', 'height=400,width=600');
		printWindow.document.write('<html><head><title>PRINTOUT</title></head><body>');
		printWindow.document.write(document.getElementsByClassName(deposit_reciept)[0].innerHTML);
		printWindow.document.write('</body></html>');
		printWindow.print();
		printWindow.close();
	}
</script>