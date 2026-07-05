<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Report Settings ( Doctor's Activities & Logs )</h5>
			</div>

			<div class="ibox-content">
				<form method="post" action="index.php?vts">


					<div class="row">

						<div class="col-sm-3">
							<strong>Choose of the option below: </strong>
							<div class="form_sep"></div>

							<div class="radio i-checks"><label> <input type="radio" value="avts" name="report_option" required>&nbsp; Activities Report</label></div>
							<div class="form_sep"></div>

							<div class="radio i-checks"><label> <input type="radio" value="logs" name="report_option" required>&nbsp; Logs Report</label></div>

						</div>



						<div class="col-sm-3">

							<div class="form_sep">

								<?php
								if (isset($_POST['doctors']) and $_POST['doctors'] != '') {
									$doctors = $_POST['doctors'];
									$part = explode("__", $doctors);
									$doc_username = $part[0];
									$doc_fullname = $part[1];
									$doc_id = $part[2];
								}

								if (isset($_POST['report_type']) and $_POST['report_type'] != '') {
									$report_type = $_POST['report_type'];
								}
								?>

								<label for="reg_input_no" class="req">Doctors</label>
								<select name="doctors" id="doctors" class="form-control" required>

									<option value="">Select ...</option>
									<?php if ($doc_fullname != '') { ?>
										<option selected="selected" value="<?php echo $doc_username . '__' . $doc_fullname; ?>"><?php echo $doc_fullname; ?></option>
									<?php } ?>

									<?php
									$stmt = $db->query("SELECT fullname,username,id FROM admin_users WHERE rights='DR' OR rights='AD'");
									while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
										<option value="<?php echo $rwx['username'] . '__' . $rwx['fullname'] . '__' . $rwx['id']; ?>"><?php echo $rwx["fullname"]; ?></option>
									<?php } ?>
								</select>
							</div>



							<div class="form_sep">
								<label for="reg_input_no" class="">Report Type</label>
								<select name="report_type" id="report_type" class="form-control">

									<option value="">Select ...</option>
									<?php if ($Services != '') { ?>
										<option selected="selected" value="<?php echo $report_type; ?>"><?php echo $report_type; ?></option>
									<?php } ?>

									<option value="Consultation">Consultation</option>
									<option value="Ward Rounds">Ward Rounds</option>
								</select>
							</div>
						</div>

						<div class="col-sm-4">

							<div class="form_sep">
								<label for="reg_input_no" class="req">Set Dates Range</label><br>
								<div class="form_sep" id="">
									<div class="input-daterange input-group" id="">
										<input type="date" class="input-sm form-control" name="start" value="<?php if (isset($_POST['start']) and $_POST['start'] != '') {
																													echo $start = $_POST['start'];
																												} else {
																													echo '';
																												}
																												?>" required />
										<span class="input-group-addon">to</span>
										<input type="date" class="input-sm form-control" name="end" value="<?php if (isset($_POST['end']) and $_POST['end'] != '') {
																												echo $end = $_POST['end'];
																											} else {
																												echo '';
																											}
																											?>" required />
									</div>

								</div>
							</div>

						</div>


						<div class="col-sm-2">

							<div class="form_sep"> <label for="reg_input_no" class="">.</label><br>
								<button class="btn btn-info btn btn-sm" type="submit" name="apply_income" id="apply_income">Apply</button>
							</div>
						</div>
					</div>

				</form>

			</div>

		</div>
	</div>
</div>




<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Report Panel</h5>
			</div>

			<div class="ibox-content">

				<?php
				if (isset($_POST['apply_income'])) {
					$fullname = $_SESSION['fullname'];

					if (isset($_POST['report']) and $_POST['report'] != '') {
						$report = $_POST['report'];
					}

					if (isset($_POST['report_option']) and $_POST['report_option'] != '') {
						$report_option = $_POST['report_option'];
					}


					if (isset($_POST['start']) and $_POST['start'] != '') {
						$start = $_POST['start'];
					} else {
						$start = date("Y-m-d");
					}
					if (isset($_POST['end']) and $_POST['end'] != '') {
						$end = $_POST['end'];
					} else {
						$end = date("Y-m-d");
					}



					if ($report_option == 'logs') {


						try {

							$sql = "SELECT sn, descriptions, patient_id, action, date_and_time 
            FROM patient_staff_logs 
            WHERE (staff_name = :username OR staff_name = :fullname) 
            AND date_and_time BETWEEN :start AND :end 
            ORDER BY sn ASC";

							$stmt = $db->prepare($sql);
							$stmt->bindParam(':username', $doc_username, PDO::PARAM_STR);
							$stmt->bindParam(':fullname', $doc_fullname, PDO::PARAM_STR);
							$stmt->bindParam(':start', $start, PDO::PARAM_STR);
							$stmt->bindParam(':end', $end, PDO::PARAM_STR);
							$stmt->execute();

							$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

							if (!empty($results)) {
				?>

								<table class="table table-striped table-bordered table-hover dataTables-example">
									<thead>
										<tr>
											<th>#</th>
											<th>Description</th>
											<th width="12%">Patient ID</th>
											<th width="15%">Action</th>
											<th width="15%">Date</th>
										</tr>
									</thead>
									<tbody>

										<?php
										$n = 1;
										foreach ($results as $row) { ?>
											<tr>
												<td><?php echo $n++; ?></td>
												<td><?php echo htmlspecialchars($row['descriptions']); ?></td>
												<td><?php echo htmlspecialchars($row['patient_id']); ?></td>
												<td><?php echo htmlspecialchars($row['action']); ?></td>
												<td>
													<?php
													echo date("d M, Y h:i:s A", strtotime($row['date_and_time']));
													?>
												</td>
											</tr>
										<?php } ?>

									</tbody>
								</table>

							<?php
							} else {
								echo '<div class="alert alert-warning">No records found for selected period.</div>';
							}
						} catch (PDOException $e) {
							echo '<div class="alert alert-danger">Error loading logs.</div>';
						}
					} elseif ($report_option == 'avts' and $report_type == 'Consultation') {
						try {

							/* ==========================
       1️⃣ GET ALL NOTES AT ONCE
    ========================== */

							$sqlNotes = "SELECT hospital_no, notes_type, notes, prepared_by
                 FROM notes
                 WHERE (prepared_by = :fullname 
                        OR prepared_by = :username 
                        OR created_by = :doc_id)
                 AND DATE(date_entry) BETWEEN :start AND :end
                 ORDER BY hospital_no, sn ASC";

							$stmtNotes = $db->prepare($sqlNotes);
							$stmtNotes->execute(array(
								':fullname' => $doc_fullname,
								':username' => $doc_username,
								':doc_id'   => $doc_id,
								':start'    => $start,
								':end'      => $end
							));

							$notesData = array();

							while ($row = $stmtNotes->fetch(PDO::FETCH_ASSOC)) {

								$hosp = trim($row['hospital_no']);

								if (!isset($notesData[$hosp])) {
									$notesData[$hosp] = array(
										'C'    => '',
										'D'    => '',
										'plan' => ''
									);
								}

								if ($row['notes_type'] == 'C') {
									$notesData[$hosp]['C'] .= ($row['notes']) . "<br>";
								}

								if ($row['notes_type'] == 'D') {
									$notesData[$hosp]['D'] .= ($row['notes']) . "<br>";
								}

								if ($row['notes_type'] == 'plan') {
									$notesData[$hosp]['plan'] .= ($row['notes']) . "<br>";
								}
							}

							/* ================================
       2️⃣ GET ALL SERVICES AT ONCE
    ================================ */

							$sqlServices = "SELECT hospital_no, item_services, serv_group
                    FROM patient_ap_services
                    WHERE (serv_group IN ('Pharmacy','Radiology','Laboratory'))
                    AND DATE(date_entry) BETWEEN :start AND :end
                    AND (prepared_by = :fullname 
                         OR prepared_by = :username)
                    ORDER BY hospital_no, sn DESC";

							$stmtServices = $db->prepare($sqlServices);
							$stmtServices->execute(array(
								':fullname' => $doc_fullname,
								':username' => $doc_username,
								':start'    => $start,
								':end'      => $end
							));

							$servicesData = array();

							while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {

								$hosp = trim($row['hospital_no']);

								if (!isset($servicesData[$hosp])) {
									$servicesData[$hosp] = array(
										'Pharmacy' => '',
										'Lab'      => ''
									);
								}

								if ($row['serv_group'] == 'Pharmacy') {
									$servicesData[$hosp]['Pharmacy'] .= ($row['item_services']) . ', ';
								}

								if ($row['serv_group'] == 'Laboratory' || $row['serv_group'] == 'Radiology') {
									$servicesData[$hosp]['Lab'] .= ($row['item_services']) . ', ';
								}
							}

							/* ==========================
       3️⃣ DISPLAY TABLE
    ========================== */

							if (!empty($notesData)) {
							?>

								<table class="table table-striped table-bordered table-hover dataTables-example">
									<thead>
										<tr>
											<th>#</th>
											<th>EMR#</th>
											<th>Presenting Complaints</th>
											<th>Diagnosis</th>
											<th>Plans</th>
											<th>Medication/Investigations</th>
										</tr>
									</thead>
									<tbody>

										<?php
										$n = 1;
										foreach ($notesData as $hospital_no => $data) {
										?>
											<tr>
												<td><?php echo $n++; ?></td>
												<td><?php echo htmlspecialchars($hospital_no); ?></td>
												<td><?php echo $data['C']; ?></td>
												<td><?php echo $data['D']; ?></td>
												<td>
													<?php
													if ($data['plan'] != '') {
														echo "<strong>Plan:</strong><br>" . $data['plan'];
													}
													?>
												</td>
												<td>
													<?php
													if (isset($servicesData[$hospital_no])) {

														if ($servicesData[$hospital_no]['Pharmacy'] != '') {
															echo "<strong>Medications:</strong><br>" .
																rtrim($servicesData[$hospital_no]['Pharmacy'], ', ') . "<br><br>";
														}

														if ($servicesData[$hospital_no]['Lab'] != '') {
															echo "<strong>Investigations:</strong><br>" .
																rtrim($servicesData[$hospital_no]['Lab'], ', ');
														}
													}
													?>
												</td>
											</tr>
										<?php } ?>

									</tbody>
								</table>

								<hr>
								<strong>Total Patients: <?php echo count($notesData); ?></strong>

							<?php
							} else {
								echo '<div class="alert alert-warning">No records found for selected period.</div>';
							}
						} catch (PDOException $e) {
							echo '<div class="alert alert-danger">Error loading report.</div>';
						}
					} elseif ($report_option == 'avts' and $report_type == 'Ward Rounds') {
						///// WARDS ROUNDS AND OTHER NOTES

						$stmt = $db->query("SELECT distinct hospital_no FROM notes WHERE (prepared_by='$doc_fullname' or prepared_by='$doc_username' or created_by='$doc_id') and note_type ='ward_round' AND date(date_entry) between '$start' and '$end'");
						if ($stmt->rowCount() > 0) { ?>

							<table class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
									<tr>
										<th>#</th>
										<th>EMR#</th>
										<th>Notes</th>
										<th>Plans</th>
										<th>Medication/Investigations</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$n = 1;
									while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$hospital_no = trim($rwx['hospital_no']);

										$stmt2 = $db->query("SELECT * FROM notes WHERE hospital_no='$hospital_no' and date(date_entry) between '$start' and '$end' and (prepared_by='$doc_fullname' or prepared_by='$doc_username' or created_by='$doc_id') order by sn");

										if ($stmt2->rowCount() > 0) {
											$NOTES = '';
											$D = '';
											$plan = '';
											while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {

												if ($row['notes_type'] != 'C' and $row['notes_type'] != 'D') {
													$NOTES = $NOTES . $row['notes'] . '<br>';
												}

												if ($row['notes_type'] == 'plan') {
													$plan = $plan . $row['notes'] . '<br>';
												}
											}

									?>

											<tr>
												<td width="7%"><?php echo $n; ?></td>
												<td width="7%"><?php echo $hospital_no; ?></td>
												<td width="25%"><?php if ($NOTES != '') {
																	echo $NOTES;
																} ?></td>
												<td width="25%"><?php if ($plan != '') {
																	echo '<strong>Plan: </strong><br>' . $plan . '<br>';
																} ?></td>


												<td width="25%">
													<?php
													$stmt3 = $db->query("SELECT item_services,cat_type,serv_group FROM patient_ap_services WHERE (serv_group='Pharmacy' or serv_group='Radiology' or serv_group='Laboratory') and date(date_entry) between '$start' and '$end' and hospital_no='$hospital_no' and (prepared_by='$doc_fullname' or prepared_by='$doc_username' ) order by sn desc");
													$Pharmacy = '';
													$lab = '';
													while ($row_details = $stmt3->fetch(PDO::FETCH_ASSOC)) {
														if ($row_details['serv_group'] == 'Pharmacy') {
															$Pharmacy = $Pharmacy . $row_details['item_services'] . ', ';
														}
														if ($row_details['serv_group'] == 'Laboratory' or $row_details['serv_group'] == 'Radiology') {
															$lab = $lab . $row_details['item_services'] . ', ';
														}
													}

													if ($Pharmacy != '') {
														echo '<strong>Medications: </strong><br>' . $Pharmacy . '<br>';
													}
													if ($lab != '') {
														echo '<br><strong>Investigations: </strong><br>' . $lab . '<br>';
													}
													?>
												</td>
											</tr>

									<?php }
										$n++;
									} ?>


								</tbody>
							</table>
							<hr>
							<strong>Total Patients: <?php echo $stmt->rowCount(); ?> </strong>

				<?php }
					}
				}
				?>

			</div>

		</div>
	</div>

</div>