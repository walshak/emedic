<div class="row">
	<div class="col-lg-12">
		<div class="ibox-content">

			<?php

			session_start();
			require_once('../Connections/Conn.php');
			$hospital_no = $_POST['hospital_no'];
			$appointment_number = $_POST['appointment_number'];

			$stmt = $db->prepare("SELECT app_no FROM admission WHERE hospital_no=:hospital_no and adm_status='3' order by sn desc limit 1");
			$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$app_no = $row['app_no'];
			}
			if ($app_no == '') {
				$app_no = $appointment_number;
			}


			$close_status = 0;
			$stmtt = $db->prepare("SELECT * FROM fluidchart WHERE app_no=:app_no and hospital_no=:hospital_no and start_close_status=0 ORDER BY id desc LIMIT 1");
			$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
			$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmtt->execute();
			if ($stmtt->rowCount() == 0) {
				$starting = date('dmh');
			} else {
				$rowx = $stmtt->fetch(PDO::FETCH_ASSOC);
				$starting = $rowx['start_code'];
				$close_status = 1;
			}



			$stmtt = $db->prepare("SELECT distinct(start_code) FROM fluidchart 
WHERE app_no=:app_no and hospital_no=:hospital_no ORDER BY id LIMIT 30");
			$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
			$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmtt->execute();
			if ($stmtt->rowCount() > 0) {
				$output = '';
				$input  = '';
				$total_in = 0;
				$total_out = 0;

				while ($row = $stmtt->fetch(PDO::FETCH_ASSOC)) {

					$start_code = $row['start_code'];

					$stmttx = $db->prepare("SELECT * FROM fluidchart 
			WHERE app_no=:app_no and hospital_no=:hospital_no and start_code=:start_code order by id");
					$stmttx->bindParam(':app_no', $app_no, PDO::PARAM_STR);
					$stmttx->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
					$stmttx->bindParam(':start_code', $start_code, PDO::PARAM_STR);
					$stmttx->execute();

					while ($rowx = $stmttx->fetch(PDO::FETCH_ASSOC)) {
						$tme = date('h:i a', strtotime($rowx['entry_time']));
						$dd = date('d-M', strtotime($rowx['entry_date']));

						$id = $rowx['id'];
						$hospital_no = $rowx['hospital_no'];
						$entry_date = $rowx['entry_date'];
						$start_close_status = $rowx['start_close_status'];
						$remarks = $rowx['remarks'];


						if ($_SESSION['fullname'] == $rowx['nurse'] and $entry_date == date('Y-m-d')) {
							$nurse = "<a href=patient.php?hosp_no=$hospital_no&in_out=$id>Delete</a>";
						}

						if ($rowx['chart'] == 'in') {
							$total_in = $total_in + $rowx['amount'];
							$input .= '<tr><td>' . $dd . ':' . $tme . '</td><td>' . $rowx['fluid_type'] . '/' . $rowx['remark']  . '</td><td>' .
								$rowx['amount'] . '</td><td>' . $rowx['nurse'] . '</td><td>' . $nurse . '</td></tr>';
						}

						if ($rowx['chart'] == 'out') {
							$total_out = $total_out + $rowx['amount'];
							$output .= '<tr><td>' . $dd . ':' . $tme . '</td><td>' . $rowx['fluid_type'] . '/' . $rowx['remark']  . '</td><td>' .
								$rowx['amount'] . '</td><td>' . $rowx['nurse'] . '</td><td>' . $nurse . '</td></tr>';
						}
						///<tr>
					}

			?>







					<h3>Start Code: <?= $start_code; ?></h3>

					<div class="row">
						<div class="col-lg-6">

							<h4>INTAKE FLUIDS</h4>
							<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
								<thead>
									<tr>
										<th data-hide="phone,tablet">Date/Time</th>
										<th data-toggle="true">Input Type</th>
										<th data-toggle="true">Amount(ml)</th>
										<th data-toggle="true">Entered by</th>
										<th> .</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $input; ?>
								</tbody>
							</table>
							<h4>TOTAL INTAKE: <?= $total_in . ' ml'; ?></h4>

						</div>
						<div class="col-lg-6">
							<h4>OUTPUT FLUIDS</h4>
							<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
								<thead>
									<tr>
										<th data-hide="phone,tablet">Date/Time</th>
										<th data-toggle="true">Input Type</th>
										<th data-toggle="true">Amount(ml)</th>
										<th data-toggle="true">Entered by</th>
										<th> .</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $output; ?>
								</tbody>
							</table>

							<h4>TOTAL OUTPUT: <?= $total_out . ' ml'; ?></h4>

							<?php if ($start_close_status == 1) { ?>

								<h4><u>REMARKS:</u> <?= $remarks; ?></h4>
								<h3 style="color: darkorange"><u>BALANCE:</u> <?= $total_in - $total_out . ' ml'; ?> / <?= date('d M y', strtotime($entry_date)); ?></h3>
							<?php } ?>

						</div>
					</div>


			<?php
					$output = '';
					$input  = '';
					$total_in = 0;
					$total_out = 0;
				}
			}
			?>



			<?php if ($close_status == 1) { ?>
				<a href="#" class="btn btn-sm btn-danger" onclick="openModal_fx('fluids_modal')" arial-modal="patient-alert-modal">&nbsp;
					<i class="fa fa-stop"></i>&nbsp;Stop</a>
			<?php } ?>

		</div>
	</div>
</div>

<hr>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox ">
			<div class="ibox-content">
				<h2 align="center">Fluid Chart - Intake and Output Record</h2>
				<hr>

				<form action="patient.php?hosp_no=<?php echo $hospital_no; ?>" method="POST">

					<div class="form-group" id="">



						<table width="100%">
							<tr>
								<td width="50%">
									<label class="font-normal">Date</label>
									<input type="date" class="input-sm form-control" id="" name="entry_date" value="<?= date('Y-m-d'); ?>" required />
								</td>
								<td>
									<label class="font-normal">Time</label>
									<input type="time" class="input-sm form-control" id="" name="entry_time" value="<?= date('H:i'); ?>" required />
								</td>

							</tr>
						</table>


					</div>


					<div class="row">
						<div class="col-lg-6">


							<table class="table" style="font-size: 14px; ">
								<thead>
									<tr>
										<th>Fluid Type (<strong style="color: brown; font-size: 14px; ">INTAKE</strong>)</th>
										<th width="12%">Amount(ml)</th>
										<th>Remarks</th>
									</tr>

								</thead>
								<tbody>

									<tr>
										<td><strong>Oral</strong>
											<select class="select2_demo_3  form-control" id="oral_remark" name="oral_remark">
												<option value="">Select</option>
												<option value="Water">Water</option>
												<option value="Beverages">Beverages</option>
												<option value="Medications">Medications</option>
												<option value="Soups">Soups</option>
												<option value="Ice chips">Ice chips</option>
											</select>

										</td>
										<td><br>
											<input type="text" placeholder="Amount" class="form-control" id="" name="oral_amount">
										</td>
										<td class="text-warning"><br>
											<input type="text" placeholder="Remarks" class="form-control" id="" name="oral_remark2">
										</td>
									</tr>
									<tr>
										<td><strong>Intravenous (IV)</strong>
											<select class="select2_demo_3  form-control" id="intravenous_remark" name="intravenous_remark">
												<option value="">Select</option>
												<option value="Normal Saline">Normal Saline (0.9% NaCl)</option>
												<option value="Ringers Lactate">Ringer's Lactate</option>
												<option value="5% Dextrose Water">5% Dextrose Water (D5W)</option>
												<option value="5% Dextrose Saline">5% Dextrose Saline (D5NS)</option>
												<option value="10% Dextrose">10% Dextrose (D10W)</option>
												<option value="Paediatric Saline">Paediatric Saline</option>
												<option value="Mannitol">Mannitol</option>
												<option value="Medications">Medications</option>

											</select>

										</td>
										<td><br>
											<input type="text" placeholder="Amount" class="form-control" id="intravenous_amount" name="intravenous_amount">
										</td>
										<td class="text-warning"> <br>
											<input type="text" placeholder="Remarks" class="form-control" id="" name="intravenous_remark2">
										</td>
									</tr>
									<tr>
										<td><strong>Tube</strong><br></td>
										<td>
											<input type="text" placeholder="Amount" class="form-control" id="tube_amount" name="tube_amount">
										</td>
										<td>
											<input type="text" placeholder="Remarks" class="form-control" name="tube_remarks" id="">
										</td>
									</tr>

									<tr>
										<td><strong>Blood</strong></td>
										<td>
											<input type="text" placeholder="Amount" class="form-control" id="blood_amount" name="blood_amount">
										</td>
										<td class="text-warning">
											<input type="text" placeholder="Remarks" class="form-control" name="blood_remarks">
										</td>
									</tr>

									<tr>
										<td>
											<input type="text" placeholder="Others" class="form-control" id="" name="input_others">
										</td>
										<td>
											<input type="text" placeholder="Amount" class="form-control" id="others_amount" name="others_amount">
										</td>
										<td class="text-warning">
											<input type="text" placeholder="Remarks" class="form-control" id="" name="others_remark">
										</td>
									</tr>
								</tbody>
							</table>

						</div>

						<div class="col-lg-6">

							<table class="table" style="font-size: 14px; ">
								<thead>
									<tr>
										<th>Fluid Type (<strong style="color: blueviolet; font-size: 14px; ">OUTPUT</strong>)</th>
										<th width="12%">Amount(ml)</th>
										<th>Remarks</th>
									</tr>

								</thead>
								<tbody>

									<tr>
										<td><strong>Urine</strong>

											<select class="select2_demo_3  form-control" id="urine_remark" name="urine_remark">
												<option value="">Select</option>
												<option value="No Bowel Movement">No Bowel Movement</option>
												<option value="From Surgical Site">From Surgical Site</option>
												<option value="Diaper Soaked">Diaper Soaked</option>
												<option value="Underlay soaked">Underlay soaked</option>
												<option value="Patient is on Diaper">Patient is on Diaper</option>
												<option value="Urine Bag">Urine Bag</option>
											</select>
										</td>
										<td><br>
											<input type="text" placeholder="Amount" class="form-control" id="urine_amount" name="urine_amount">
										</td>
										<td class="text-warning"> <br>
											<input type="text" placeholder="Remarks" class="form-control" id="" name="urine_remark2">
										</td>
									</tr>
									<tr>
										<td><strong>Stool</strong>
											<select class="select2_demo_3  form-control" id="stool_remark" name="stool_remark">
												<option value="">Select</option>
												<option value="Diarrhea">Diarrhea</option>
												<option value="Liquid stool">Liquid stool</option>
											</select>
										</td>
										<td><br>
											<input type="text" placeholder="Amount" class="form-control" id="stool_amount" name="stool_amount">
										</td>
										<td class="text-warning"> <br>
											<input type="text" placeholder="Remarks" class="form-control" id="" name="stool_remark2">
										</td>
									</tr>
									<tr>
										<td><strong>Drainage</strong>
											<select class="select2_demo_3  form-control" id="drainage_remark" name="drainage_remark">
												<option value="">Select</option>
												<option value="JP drain output">JP drain output</option>
												<option value="From Surgical Site">From Surgical Site</option>
												<option value="Serosanguinous drainage">Serosanguinous drainage</option>
											</select>
										</td>
										<td><br>
											<input type="text" placeholder="Amount" class="form-control" id="drainage_amount" name="drainage_amount">
										</td>
										<td class="text-warning"><br>
											<input type="text" placeholder="Remarks" class="form-control" id="" name="drainage_remark2">
										</td>
									</tr>
									<tr>
										<td><strong>Vomit</strong>
											<select class="select2_demo_3  form-control" id="vomit_remark" name="vomit_remark">
												<option value="">Select</option>
												<option value="undigested food">undigested food</option>
												<option value="Frequent vomiting">Frequent vomiting</option>
												<option value="antiemetics administered">antiemetics administered</option>
											</select>
										</td>
										<td><br>
											<input type="text" placeholder="Amount" class="form-control" id="vomit_amount" name="vomit_amount">
										</td>
										<td class="text-warning"><br>
											<input type="text" placeholder="Remarks" class="form-control" id="" name="vomit_remark2">
										</td>
									</tr>
									<tr>
										<td><strong>UF Goal</strong></td>
										<td>
											<input type="text" placeholder="Amount" class="form-control" id="ufgoal_amount" name="ufgoal_amount">
										</td>
										<td class="text-warning">
											<input type="text" placeholder="Remarks" class="form-control" id="ufgoal_remark" name="ufgoal_remark">
										</td>
									</tr>


									<tr>
										<td>
											<input type="text" placeholder="Others" class="form-control" id="" name="others_output">
										</td>
										<td>
											<input type="text" placeholder="Amount" class="form-control" id="" name="others_amount_output">
										</td>
										<td class="text-warning">
											<input type="text" placeholder="Remarks" class="form-control" id="" name="others_remark_output">
										</td>
									</tr>

								</tbody>
							</table>
							<div class="form_sep">
								<input type="submit" value="SAVE DATA" name="add_output" class="btn btn-primary">



								<input type="hidden" value="<?php echo $starting;  ?>" name="starting">
							</div>


						</div>
					</div>

					</br>

					<input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
					<input type="hidden" name="hosp_no" value="<?php echo $hospital_no; ?>" />
				</form>






			</div>
		</div>
	</div>
</div>