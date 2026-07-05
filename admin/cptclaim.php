                           <?php
							if (isset($_POST['save_income'])) {
								$hmo_no = $_POST['hmo_no'];
								$last_month = $_POST['last_month'];
								$last_yr = $_POST['last_yr'];

								$chk = $db->query("SELECT * FROM insurance_claim_rpt WHERE insurance_no='$hmo_no' and month='$last_month' and year='$last_yr'");
								if ($chk->rowCount() == 0) {
									$stmt = $db->prepare('INSERT INTO insurance_claim_rpt (insurance_no, settlement, 
							month, 
							year, 
							date_post, 
							post_by) 
							VALUES (
							:insurance_no, 
							:settlement, 
							:month, 
							:year, 
							:date_post, 
							:post_by)');

									$stmt->execute([
										':insurance_no' => $_POST['hmo_no'],
										':settlement' => $_POST['recovered_claims'],
										':month' => $_POST['last_month'],
										':year' => $_POST['last_yr'],
										':date_post' => $setdatetime,
										':post_by' => $_SESSION['fullname']
									]);
									$sv = 1;
								}
							}

							if (isset($_POST["tranfer_sub_code"])) {
								$hos_no = ($_POST['hos_no']);
								$app_no = ($_POST['ap']);

								if (isset($_POST['auth_code'])) {
									$a_code = "0000";
								} else {
									$a_code = $_POST['a_code'];
								}

								// request for referree
								if ($a_code != '') {
									$ap_type = 2;
									$stmt = $db->prepare('UPDATE apptm 
									SET ap_type = :ap_type, 
										auth_code = :auth_code 
									WHERE appt_no = :appt_no');

									$stmt->bindParam(':ap_type', $ap_type);
									$stmt->bindParam(':auth_code', $a_code);
									$stmt->bindParam(':appt_no', $_POST['ap']);

									$stmt->execute();
									// header ("Location:index.php?cptclaim");
								}
							}
							?>


                           <div class="row">

                           	<div class="col-lg-12">
                           		<div class="ibox float-e-margins">
                           			<div class="ibox-title">
                           				<h5>LEDGER POSTING</h5>
                           			</div>
                           			<div class="ibox-content">

                           				<?php
											if ($ERROR == '1') { ?>
                           					<strong style="color:#F00">Invalid Report Selection</strong>
                           				<?php } ?>

                           				<?php


											$currentDate = date('Y-m-d');
											$firstDayOfCurrentMonth = date('Y-m-01', strtotime($currentDate));
											$lastDayOfLastMonth = date('Y-m-d', strtotime('-1 day', strtotime($firstDayOfCurrentMonth)));
											$firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month', strtotime($firstDayOfCurrentMonth)));

											try {
												// Use GROUP BY to combine counts and sums in a single query
												$stt_enl = $db->prepare("
        SELECT i.insurance_name, i.insurance_no, SUM(ap.claim_amt) AS total_claim
        FROM enrollee AS enl
        INNER JOIN patient_ap_services AS ap ON ap.hospital_no = enl.hospital_no
        INNER JOIN insurance_tbl AS i ON i.insurance_no = enl.hmo_no
        WHERE ap.paystatus = '1'
        AND ap.claim_amt > 0
        AND ap.ledger_TX IS NULL
        AND DATE(ap.date_entry) BETWEEN :start_date AND :end_date
        GROUP BY i.insurance_no
    ");

												$stt_enl->execute([
													':start_date' => $firstDayOfLastMonth,
													':end_date' => $lastDayOfLastMonth
												]);

												$results = $stt_enl->fetchAll(PDO::FETCH_ASSOC);

												$valid_error = !empty($results) ? 1 : 0;
												$grand_total = 0;
												$valid_count = count($results);

												foreach ($results as $row) {
													$grand_total += floatval($row['total_claim']);
												}
											} catch (PDOException $e) {
												// Log error or handle gracefully
												error_log("Database error: " . $e->getMessage());
												$valid_error = 0;
											}
											?>

                           				<?php if ($valid_error == 1): ?>
                           					<strong style="color:#F00;">
                           						This Month <?= date('d M, Y', strtotime($firstDayOfLastMonth)) . ' - ' . date('d M, Y', strtotime($lastDayOfLastMonth)); ?> Ledger Posting Not Completed!
                           					</strong><br>
                           					<?php if ($valid_count > 0): ?>
                           						<h2><?= $valid_count; ?> Total Claims Pending: <?= number_format($grand_total); ?></h2>
                           						<hr>
                           					<?php endif; ?>
                           				<?php endif; ?>


                           				<form action="index.php?cptclaim" method="POST" id="subject" name="subject">

                           					<table>
                           						<tr>
                           							<td>
                           								<div class="form_sep">
                           									<label for="reg_select" class="req">Select folder</label>
                           									<select name="insurance_type2" id="insurance_type2" class="form-control" required>
                           										<option selected="selected" value="">Select ...</option>
                           										<!--                                <option value="nhis">NHIS</option>
-->
                           										<option value="phis">PHIS</option>
                           										<option value="corporate">Corporate</option>
                           									</select>
                           								</div>
                           							</td>

                           							<td>
                           								<div class="form_sep">
                           									<label for="reg_select" class="req">Month</label>
                           									<select name="month" id="month" class="form-control" required>
                           										<option selected="selected" value="">Select...</option>
                           										<option value="1">January</option>
                           										<option value="2">February</option>
                           										<option value="3">March</option>
                           										<option value="4">April</option>
                           										<option value="5">May</option>
                           										<option value="6">June</option>
                           										<option value="7">July</option>
                           										<option value="8">August</option>
                           										<option value="9">September</option>
                           										<option value="10">October</option>
                           										<option value="11">November</option>
                           										<option value="12">December</option>
                           									</select>
                           								</div>
                           							</td>
                           							<td>
                           								<div class="form_sep">
                           									<label for="reg_select" class="req">Year</label>
                           									<input type="text" id="year" name="year" class="form-control" value="<?php echo date("Y"); ?>" maxlength="4" required>
                           								</div>
                           							</td>
                           							<td>

                           								<div class="form_sep">
                           									<label for="reg_select" class="req">.</label><br>
                           									<button class="btn btn-primary btn-sm" type="submit" name="apply_rpt">Apply</button>
                           								</div>
                           							</td>
                           						</tr>
                           					</table>


                           				</form>

                           			</div>

                           		</div>
                           	</div>

                           </div>




                           <div class="row">
                           	<div class="col-lg-12">
                           		<div class="ibox float-e-margins">
                           			<div class="ibox-title">
                           				<h5>Insurance Claims</h5>
                           			</div>

                           			<div class="ibox-content">
                           				<?php


											if (isset($_POST['print_report'])) {

												$hmo_no = $_POST['hmo_no'];
												$last_month = $_POST['month'];
												$last_yr = $_POST['year'];
												$dates = $_POST['dates'];
												$dates2 = $_POST['dates2'];
											?>

                           					<div id="content">
                           						<div align="center">

                           							<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                           								<tr>
                           									<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
                           									<td width="50%" align="right"></td>
                           								</tr>
                           							</table><br>


                           							<?php
														$qury = $db->query("SELECT * FROM insurance_tbl WHERE insurance_no='$hmo_no'");
														$row_s = $qury->fetch(PDO::FETCH_ASSOC);
														?>
                           							<div style="font:bold 18px 'Arial';"><?php echo $row_s['insurance_name']; ?></div>
                           							<?php echo $row_s['addr']; ?> <br><br>
                           							<div style="font:bold 14px 'Arial';"><?php echo 'CLAIMS REPORT FOR THE MONTH ' . $last_month . ' / ' . $last_yr; ?></div>
                           							<hr>
                           						</div>

                           						<?php

													$stmt = $db->query("SELECT DISTINCT en.surname,en.fname,en.hospital_no FROM patient_ap_services as ap INNER JOIN enrollee as en ON ap.hospital_no=en.hospital_no WHERE ap.paystatus='1' and ap.process_claim='1' and ap.claim_amt>0 $dates");
													if ($stmt->rowCount() > 0) { ?>

                           							<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                           								<thead>
                           									<tr>
                           										<th style="border-bottom: 1px solid #ddd;">No</th>
                           										<th style="border-bottom: 1px solid #ddd;">EMR #</th>
                           										<th style="border-bottom: 1px solid #ddd;">Patient Name</th>
                           										<th style="border-bottom: 1px solid #ddd;">Billed</th>
                           									</tr>
                           								</thead>
                           								<tbody>

                           									<?php $n = 1;
																while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {

																	//// compute income for each enrollesssssssssss
																	$hos_no = $rwx['hospital_no'];
																	$qury = $db->query("SELECT SUM(claim_amt) AS total_cliam FROM patient_ap_services WHERE hospital_no='$hos_no' and paystatus='1' and process_claim='1' and claim_amt>0 $dates2");
																	$row_s = $qury->fetch(PDO::FETCH_ASSOC);
																	$total_cliam = $row_s['total_cliam'];
																	$grand_total_cliam = $grand_total_cliam + $total_cliam;
																?>
                           										<tr>
                           											<td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
                           											<td style="border-bottom: 1px solid #ddd;"><?php echo $rwx['hospital_no']; ?></td>
                           											<td style="border-bottom: 1px solid #ddd;"><?php echo $rwx['surname'] . ' ' . $rwx['fname']; ?></td>
                           											<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($total_cliam, 2, '.', ','); ?></td>
                           										</tr>
                           								<?php $n++;
																}
															}
															?>
                           								</tbody>
                           							</table>
                           							<hr>
                           							<strong>Total Claim: </strong>&nbsp;<strong><?php echo number_format($grand_total_cliam, 2, '.', ','); ?></strong>
                           					</div><br>


                           					<div class="form_sep">

                           						<a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="icon-print"></i> Print</button></a> &nbsp; &nbsp;

                           						<a href="index.php?cptclaim"><button class="btn btn-danger btn-sm"><i class="icon-print"></i> Close</button></a>
                           					</div>


                           					<?php } elseif (isset($_POST['apply_rpt'])) {

												$year = $_POST['year'];
												$month = $_POST['month'];

												if (isset($_POST['insurance_type2']) and $_POST['insurance_type2'] != '') {
												}


												$firstDay = date('Y-m-d', strtotime("$year-$month-01"));
												$lastDay = date('Y-m-t', strtotime("$year-$month-01"));


												$stt_enl = $db->prepare("SELECT distinct i.insurance_name , i.insurance_no FROM enrollee AS enl 
INNER JOIN patient_ap_services AS ap ON ap.hospital_no=enl.hospital_no
INNER JOIN insurance_tbl as i ON i.insurance_no = enl.hmo_no
WHERE paystatus='1' and claim_amt>0 and ledger_TX is null and date(date_entry) between '$firstDay' and '$lastDay'");
												$stt_enl->execute();
												if ($stt_enl->rowCount() > 0) {	?>
                           						<table class="table table-striped table-bordered table-hover dataTables-example">
                           							<thead>
                           								<tr>
                           									<th>SN</th>
                           									<th>HMO/COPERATE</th>
                           									<th>CLAIM</th>
                           									<th></th>
                           								</tr>
                           							</thead>
                           							<tbody>

                           								<?php
															$valid_count = $stt_enl->rowCount();
															$sn = 1;
															$grand_total = 0;
															while ($row = $stt_enl->fetch(PDO::FETCH_ASSOC)) {

																$insurance_no = $row['insurance_no'];
																$stt_enl2 = $db->prepare("SELECT SUM(claim_amt) AS total_cliam FROM enrollee AS enl 
INNER JOIN patient_ap_services AS ap ON ap.hospital_no=enl.hospital_no
WHERE paystatus='1' and claim_amt>0 and ledger_TX is null and enl.hmo_no= '$insurance_no' and date(date_entry) between '$firstDay' and '$lastDay'");
																$stt_enl2->execute();
																$rowx = $stt_enl2->fetch(PDO::FETCH_ASSOC);
																$grand_total = $grand_total + $rowx['total_cliam'];


															?>
                           									<tr>
                           										<td><?php echo $sn++; ?></td>
                           										<td><?php echo $row['insurance_name']; ?></td>
                           										<td><?php echo $rowx['total_cliam'];
																		$pack = $insurance_no . '__' . $firstDay . '__' . $lastDay . '__' . $sn;

																		?></td>
                           										<td> <button class="btn btn-danger btn-xs" id="btn_<?= $sn; ?>" onClick="post_ledger('<?= $pack; ?>')"><i class="icon-print"></i>Post</button></td>
                           									</tr>
                           							<?php		 }
														} ?>
                           							</tbody>
                           						</table>
                           						<a href="index.php?cptclaim"><button class="btn btn-white btn-sm"><i class="icon-print"></i> Refresh</button></a>



                           						<?php

												} elseif (isset($_GET['url'])) {
													$ERROR = '0';
													$typerpt = $_POST['typerpt'];
													$insurance_type = $_POST['insurance_type2'];
													if ($insurance_type == 'nhis') {
														$insurance_type = $_POST['nhis'];
														if ($_POST['nhis'] == '') {
															$ERROR = '1';
														}
													} elseif ($insurance_type == 'phis') {
														$insurance_type = $_POST['phis'];
														if ($_POST['phis'] == '') {
															$ERROR = '1';
														}
													} elseif ($insurance_type == 'corporate') {
														$insurance_type = $_POST['corporate'];
														if ($_POST['corporate'] == '') {
															$ERROR = '1';
														}
													} elseif ($insurance_type == '') {
														$ERROR = '1';
													}

													$year = $_POST['year'];
													$month = $_POST['month'];

													if ($typerpt == 'Claims Settlement') {
														if ($insurance_type != '') {
															$qury = $db->query("SELECT * FROM insurance_tbl WHERE insurance_no='$insurance_type'");
															if ($qury->rowCount() > 0) {
																$row_s = $qury->fetch(PDO::FETCH_ASSOC); ?>
                           									<div style="font:bold 18px 'Arial';"><?php echo $row_s['insurance_name']; ?></div>
                           									<hr>
                           								<?php }
														}

														$stmt = $db->query("SELECT * FROM insurance_claim_rpt WHERE insurance_no='$insurance_type' and year='$year' order by month,year");
														if ($stmt->rowCount() > 0) { ?>

                           								<table class="table table-striped table-bordered table-hover dataTables-example">
                           									<thead>
                           										<tr>
                           											<th>Month/Year</th>
                           											<th>Insured Claim</th>
                           											<th>Settlement</th>
                           											<th>Difference</th>
                           											<th>Status</th>
                           											<th>Date/By</th>
                           										</tr>
                           									</thead>
                           									<tbody>

                           										<?php $n = 1;
																	$tRC = 0;
																	$tIC = 0;
																	while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                           											<tr>
                           												<td><?php echo $rwx['month'] . ' / ' . $rwx['year']; ?></td>
                           												<td><?php
																				//// query claims and number of enrollleeeee
																				$month = $rwx['month'];
																				$year = $rwx['year'];
																				$qury = $db->query("SELECT SUM(ap.claim_amt) AS total_cliam FROM patient_ap_services as ap INNER JOIN enrollee as en ON ap.hospital_no=en.hospital_no WHERE ap.paystatus='1' and ap.claim_amt>0 and MONTH(ap.date_entry)='$month' and YEAR(ap.date_entry)='$year'");

																				if ($qury->rowCount() > 0) {
																					$row_s = $qury->fetch(PDO::FETCH_ASSOC);
																					$total_claim = $row_s['total_cliam'];
																					$total_enrollee = $qury->rowCount();
																				} else {
																					$total_claim = '0';
																					$total_enrollee = '0';
																				}

																				echo number_format($total_claim, 2, '.', ',') . '<br> Enrollees(' . $total_enrollee . ')'; ?></td>
                           												<td><?php echo number_format($rwx['settlement'], 2, '.', ','); ?></td>
                           												<td><?php $amount_recieve = $rwx['settlement'];
																				$tIC = $tic + $total_claim;
																				$tRC = $tRC + $amount_recieve;
																				$diff = $amount_recieve - $total_claim;
																				echo number_format($diff, 2, '.', ',') ?></td>
                           												<td><?php if ($diff < 0) {
																					echo 'Loss';
																				} else {
																					echo 'Gained';
																				} ?></td>
                           												<td><?php echo date("d M Y", strtotime($rwx['date_post'])) . '/<br>' . $rwx['post_by']; ?></td>
                           											</tr>
                           										<?php $n++;
																	} ?>
                           									</tbody>
                           								</table>

                           								<hr>
                           								<strong>Total Insured Claims:</strong>
                           								<strong style="font-size:18px; "><?php echo number_format($tIC, 2, '.', ','); ?></strong>
                           								&nbsp;/&nbsp; <strong>Total Settlement Paid:</strong>
                           								<strong style="font-size:18px; "><?php echo number_format($tRC, 2, '.', ','); ?></strong><br>
                           								&nbsp;/&nbsp; <strong>Differenced:</strong>
                           								<strong style="font-size:18px; "><?php
																							$diff2 = $tRC - $tIC;
																							echo number_format($diff2, 2, '.', ','); ?></strong> / <?php if ($diff2 < 0) {
																																						echo '<strong style="color: #F00"><u>Loss</u></strong>';
																																					} else {
																																						echo '<strong style="color:#00C"><u>Gained</u></strong>';
																																					} ?><br><br>

                           								<a href="index.php?cptclaim" class="btn btn-danger btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>

                           							<?php } else { ?>
                           								<strong>No Records Found</strong><br><br>
                           								<a href="index.php?cptclaim" class="btn btn-danger btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>

                           							<?php }
													} elseif ($typerpt == 'No_authcodes' or isset($_GET['url'])) {

														if (isset($_GET['url'])) {
															$url = $_GET['url'];
															$part = explode("/", $url);
															$last_month = $part['1'];
															$insurance_type = $part['0'];
															$last_yr = $part['2'];
														} else {
															$last_month = $_POST['month'];
															$insurance_type = $_POST['insurance_type2'];
															$last_yr = $_POST['year'];
														}
														$ERROR = 0;
														$auth = $insurance_type . '__' . $last_month . '__' . $last_yr;
														include_once("auth_list.php");
													}
													///year month  
												} elseif (isset($_POST['show_report'])) {

													if ($_POST['more_details'] == 'summary') {

														include_once("claim_transaction_smry.php");
													} else {
														include_once("claim_transaction.php");
													}
													///
													///

												} elseif (isset($_POST['apply_enrollees'])) {

													$target = 'dates';
													include_once("cptclaim_body.php");
												} elseif (isset($_GET['cptclaim'])) {
													$cptclaim = $_GET['cptclaim'];

													if ($cptclaim == '') { ?>

                           							<form action="index.php?cptclaim" method="POST" id="subject" name="subject">

                           								<table width="100%">
                           									<tr>
                           										<td>Today's Enrollee<br> Claims Report</td>
                           										<td style="border-left:solid; padding-left:10px;  ">
                           											<div class="form_sep">
                           												<label for="reg_input_no" class="">Show Enrollees Visit by Dates Range</label><br>
                           												<div class="form_sep" id="">
                           													<div class="input-daterange input-group" id="">
                           														<input type="date" class="form-control" name="start" value="<?php echo date("Y-m-d"); ?>" required />
                           														<span class="input-group-addon">to</span>
                           														<input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" required />
                           													</div>
                           												</div>
                           											</div>
                           										</td>


                           										<td style="padding-left:10px;  ">

                           											<div class="form_sep">
                           												<?php
																			$stmt = $db->query("SELECT distinct insurance_no,insurance_name FROM insurance_tbl 
WHERE status='active' and (insurance_type='PHIS' or insurance_type='NHIS' or insurance_type='Corporate') order by insurance_name");
																			?>
                           												<label for="reg_select" class="">PHIS/NHIS/Corporate</label>
                           												<select name="insurance_tbl" id="insurance_tbl" data-placeholder="Select.." class="form-control" required>
                           													<option value="" selected>-- select --</option>
                           													<?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                           														<option value="<?php echo $row_rstSelect["insurance_no"] . '___' . $row_rstSelect["insurance_name"]; ?>"><?php echo $row_rstSelect["insurance_name"]; ?></option>
                           													<?php  } ?>
                           												</select>
                           											</div>

                           										</td>
                           										<td style="padding-left:10px;  ">
                           											<label for="reg_input_no" class="">.</label><br>
                           											<button class="btn btn-primary btn-sm" type="submit" name="apply_enrollees">Apply</button>

                           										</td>


                           									</tr>
                           								</table>
                           							</form>



                           							<HR>

                           							<br>
                           							<br>
                           							<h2> VIEW HMO/CORPORATE CLAIMS / TRANSACTION REPORTS </h2>
                           							<?php
														// Database connection assumed as $db (PDO instance)

														// Fetch insurance options (PHIS, NHIS, Corporate)
														$insurance_query = "SELECT insurance_no, insurance_name 
                    FROM insurance_tbl 
                    WHERE status = 'active' AND insurance_type IN ('PHIS', 'NHIS', 'Corporate') 
                    ORDER BY insurance_name";
														$insurance_stmt = $db->query($insurance_query);
														$insurance_data = $insurance_stmt->fetchAll(PDO::FETCH_ASSOC);

														// Fetch department options
														$department_query = "SELECT sn, department FROM department ORDER BY department";
														$department_stmt = $db->query($department_query);
														$departments_data = $department_stmt->fetchAll(PDO::FETCH_ASSOC);
														?>

                           							<form action="index.php?cptclaim" method="POST" id="subject" name="subject">
                           								<div class="form_sep">
                           									<label for="hmo_nhis">PHIS/NHIS/Corporate</label>
                           									<select name="hmo_nhis[]" id="hmo_nhis" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">
                           										<?php foreach ($insurance_data as $insurance): ?>
                           											<option value="<?= $insurance['insurance_no'] . '__' . $insurance['insurance_name'] ?>">
                           												<?= htmlspecialchars($insurance['insurance_name']) ?>
                           											</option>
                           										<?php endforeach; ?>
                           									</select>
                           								</div>

                           								<hr>

                           								<div class="row">
                           									<div class="col-lg-6">
                           										<div class="form_sep">
                           											<label for="hmo_type">PHIS/NHIS Type</label>
                           											<select name="hmo_type" id="hmo_type" class="form-control" style="font-size:15px;" required>
                           												<option value="" selected>Select HMO...</option>
                           												<option value="PHIS">PHIS</option>
                           												<option value="NHIS">NHIS</option>
                           												<option value="Corporate">Corporate</option>
                           											</select>
                           										</div>

                           										<div class="form_sep">
                           											<label for="Department">Filter by Department (Optional)</label>
                           											<select name="Department" id="Department" class="form-control">
                           												<option value="">Select Department...</option>
                           												<?php foreach ($departments_data as $dept): ?>
                           													<option value="<?= $dept['sn'] ?>"><?= htmlspecialchars($dept['department']) ?></option>
                           												<?php endforeach; ?>
                           											</select>
                           										</div>

                           										<div class="form_sep">
                           											<input type="checkbox" name="more_details" id="more_details" value="summary">
                           											<strong style="color: firebrick;">Check to see Summary Report</strong>
                           										</div>
                           									</div>

                           									<div class="col-lg-6">
                           										<div class="form_sep">
                           											<label>Show Enrollees Visit by Dates Range</label>
                           											<div class="input-daterange input-group">
                           												<input type="date" class="form-control" name="from_date" value="<?= date("Y-m-d") ?>" required>
                           												<span class="input-group-addon">to</span>
                           												<input type="date" class="form-control" name="to_date" value="<?= date("Y-m-d") ?>" required>
                           											</div>
                           										</div>

                           										<div class="form_sep">
                           											<label for="validation_status2">Validation Status</label>
                           											<select name="validation_status2" id="validation_status2" class="form-control" style="font-size:15px;" required>
                           												<option value="" selected>Select ...</option>
                           												<option value="1">Posted Claims</option>
                           												<option value="0">Non Posted Claims</option>
                           											</select>
                           										</div>

                           										<div class="form_sep">
                           											<button class="btn btn-primary btn-sm" type="submit" name="show_report">Apply</button>
                           										</div>
                           									</div>
                           								</div>
                           							</form>


                           							<hr>

                           							<?php $target = 'daily';
														// include_once("cptclaim_body.php"); 
														?>

                           					<?php

													} else {
														$target = 'last_month';
														include_once("cptclaim_body.php");
													}
												}
												?>

                           			</div>

                           		</div>
                           	</div>

                           </div>


                           <div class="modal inmodal fade" id="claim_amount_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                           	<div class="modal-dialog modal-lg">
                           		<div class="modal-content">
                           			<div class="modal-header">
                           				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                           				<h4 class="modal-title" id="">Claim Income Reports</h4>
                           			</div>

                           			<div class="modal-body" id="claim_amount_body">

                           			</div>
                           		</div>
                           	</div>
                           </div>

                           <div class="modal inmodal fade" id="auth_code_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                           	<div class="modal-dialog modal-sm">
                           		<div class="modal-content">
                           			<div class="modal-header">
                           				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                           				<h4 class="modal-title" id=""></h4>
                           			</div>
                           			<div class="modal-body" id="auth_code_body">



                           			</div>
                           		</div>
                           	</div>
                           </div>

                           <script language="javascript">
                           	function Clickheretoprint() {
                           		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
                           		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
                           		var content_vlue = document.getElementById("content").innerHTML;

                           		var docprint = window.open("", "", disp_setting);
                           		docprint.document.open();
                           		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
                           		docprint.document.write(content_vlue);
                           		docprint.document.close();
                           		docprint.focus();
                           	}

                           	function post_ledger(pack) {

                           		var parts = pack.split("__");
                           		var insurance_no = parts[0];
                           		var firstDay = parts[1];
                           		var lastDay = parts[2];
                           		var sn = parts[3];

                           		var button = document.getElementById("btn_" + sn);
                           		button.disabled = true;
                           		document.getElementById("btn_" + sn).innerHTML = 'Wait ...'


                           		$.ajax({
                           			url: "post_ledger_hmo.php",
                           			method: "POST",
                           			data: {
                           				insurance_no: insurance_no,
                           				firstDay: firstDay,
                           				lastDay: lastDay
                           			},
                           			success: function(data) {

                           				var jsonn = JSON.parse(data);
                           				if (jsonn["status"] == 1) {
                           					document.getElementById("btn_" + sn).innerHTML = 'Post'
                           					document.getElementById("btn_" + sn).disabled = false;
                           					toastr.error(jsonn["message"], 'Attention', {
                           						timeOut: 5000
                           					})

                           				} else {
                           					document.getElementById("btn_" + sn).innerHTML = 'Posted'
                           					toastr.info(jsonn["message"], 'Attention', {
                           						timeOut: 5000
                           					})
                           				}



                           			}
                           		});

                           	}
                           </script>