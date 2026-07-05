<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Report Settings</h5>
			</div>

			<div class="ibox-content">
				<form method="post" action="index.php?doc">

					<div class="row">
						<div class="col-sm-3">
							<div class="form_sep">

								<?php

								if (isset($_POST['doctors']) and $_POST['doctors'] != '') {
									$staffname = $_POST['doctors'];
									if ($staffname == 'All Doctors') {
										$staff_part = "";
									} else {
										//$parts=explode($staffname,"-");
										/// 175__FOLAKEMI HABU__doctor
										$parts = explode("__", $staffname);
										$doctor_id = $parts[0];
										$staffname = $parts[1];
										////$staff_part = $staffname;
									}
								}


								if (isset($_POST['Services']) and $_POST['Services'] != '') {
									$Services = $_POST['Services'];
									if ($Services == 'All Services') {
										$Services = "All Services";
									} else {
										$Services = $_POST['Services'];
									}
								}

								?>
								<label for="reg_input_no" class="req">Doctors</label>
								<select name="doctors" id="doctors" class="form-control" required>

									<option value="" selected>Select ...</option>
									<option value="All Doctors" <?php if ($staffname == 'All Doctors') { ?>selected <?php } ?>>All Doctors</option>

									<?php
									$stmt = $db->query("
			SELECT 
			distinct A.doctor_id, A.fullname, A.username 
			FROM admin_users_logs as A
			INNER JOIN admin_users as L on A.username=L.username		
			WHERE (A.rights='AD' OR A.rights='DR' OR A.rights='MD') and L.status=1 order by A.fullname");

									while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
										<option value="<?php echo $rwx['doctor_id'] . '__' . $rwx['fullname'] . '__' . $rwx['username']; ?>"
											<?php if ($doctor_id == $rwx['doctor_id']) { ?>selected <?php } ?>><?php echo $rwx["fullname"]; ?></option>
									<?php } ?>
								</select>
							</div>



							<div class="form_sep">
								<label for="reg_input_no" class="req">Services</label>
								<select name="Services" id="Services" class="form-control" required>

									<option value="">Select ...</option>
									<option value="All Services">All Services</option>
									<?php if ($Services != '') { ?>
										<option selected="selected" value="<?php echo $Services; ?>"><?php echo $Services; ?></option>
									<?php } ?>
									<option value="Nursing Services">Nursing Services</option>
									<option value="Investigations">Investigations</option>
									<option value="Pharmacy">Pharmacy/Consumables</option>
									<option value="Medical Services">Medical Services</option>
									<option value="Consultation">Consultation</option>
									<option value="Other Services">Other Services</option>

								</select>
							</div>
						</div>

						<div class="col-sm-4">

							<div class="form_sep">
								<label for="reg_input_no" class="req">Set Dates Range</label><br>
								<div class="form_sep" id="">
									<div class="input-daterange input-group" id="">
										<input type="date" class="form-control" name="start" value="<?php if (isset($_POST['start']) and $_POST['start'] != '') {
																										echo $start = $_POST['start'];
																									} else {
																										echo '';
																									}
																									?>" required />
										<span class="input-group-addon">to</span>
										<input type="date" class="form-control" name="end" value="<?php if (isset($_POST['end']) and $_POST['end'] != '') {
																										echo $end = $_POST['end'];
																									} else {
																										echo '';
																									}
																									?>" required />
									</div>

								</div>
							</div>

						</div>

						<div class="col-sm-3">
							<strong>Choose of the option below: </strong>
							<div class="form_sep"></div>

							<div class="radio i-checks"><label> <input type="radio" value="details" name="report" required
										<?php if (isset($_POST['report']) and $_POST['report'] == 'details') { ?>checked<?php } ?>>&nbsp; Detail Report</label></div>
							<div class="form_sep"></div>
							<div class="radio i-checks"><label> <input type="radio" value="summary" name="report" required
										<?php if (isset($_POST['report']) and $_POST['report'] == 'summary') { ?>checked<?php } ?>>&nbsp; Summary Report</label></div>
							<div class="form_sep"></div>
							<div class="radio i-checks"><label> <input type="radio" value="notes" name="report" required
										<?php if (isset($_POST['report']) and $_POST['report'] == 'notes') { ?>checked<?php } ?>>&nbsp;<strong style="color: brown;">Advance: </strong>Based On Doctors Documentation/Notes</label></div>

						</div>
						<div class="col-sm-2">

							<div class="form_sep">
								<button class="btn btn-success" type="submit" name="apply_income" id="apply_income">PROCESS HERE</button>
							</div>
							<div class="form_sep">
								<a href="index.php?doc" class="btn btn-default">REFRESH</a>
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
				<h5>Doctors' Service and Revenue Generated</h5>
			</div>

			<div class="ibox-content">

				<?php
				if (isset($_POST['apply_income'])) {
					$fullname = $_SESSION['fullname'];

					if (isset($_POST['report']) and $_POST['report'] != '') {
						$report = $_POST['report'];

						$update = sprintf("DELETE FROM temp_doc_income where staffname='$fullname'");
						$db->exec($update);
					}


					/* ---------------- DEFAULT DATE RANGE ---------------- */

					// Default: last 1 month → today
					$start = !empty($_POST['start'])
						? $_POST['start']
						: date('Y-m-d', strtotime('-1 month'));

					$end = !empty($_POST['end'])
						? $_POST['end']
						: date('Y-m-d');

					/* ---------------- VALIDATE FORMAT ---------------- */

					if (
						!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) ||
						!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)
					) {

						echo "<div class='alert alert-danger'>Invalid date format.</div>";
						exit;
					}

					/* ---------------- DATE OBJECTS ---------------- */

					$startDate = new DateTime($start);
					$endDate   = new DateTime($end);

					/* ---------------- LOGICAL CHECKS ---------------- */

					// End date before start date
					if ($endDate < $startDate) {
						echo "<div class='alert alert-danger'>
            End date cannot be earlier than start date.
          </div>";
						exit;
					}

					// Difference in days
					$diffDays = $startDate->diff($endDate)->days;

					/* ---------------- ONE MONTH FLAG ---------------- */

					$moreThanOneMonth = false;

					// Allow max 31 days
					if ($diffDays > 31) {
						$moreThanOneMonth = true;
					}

					/* ---------------- ACTION ON FLAG ---------------- */

					if ($moreThanOneMonth) {
						echo "<div class='alert alert-warning'>
            Date range cannot exceed one month (31 days).
          </div>";
						exit; // ❗ Remove this line if you only want a warning
					}

					/* ---------------- DATES ARE SAFE TO USE ---------------- */

					// At this point:
					// $start and $end are valid
					// ≤ 1 month range
					// Safe for SQL queries




					if ($report == 'notes') {
						include_once('../doctor/doc_incom2.php');
					} else {

						if ($staffname == 'All Doctors') {
							//// all doctors
							$stt_usr = $db->query("SELECT distinct doctor_id, fullname, username 
				FROM admin_users_logs WHERE rights='AD' or rights='DR' or rights='MD'");
							$PPay = 0;
							$PClaim = 0;
							$GrandPPay = 0;
							$GrandPClaim = 0;

							while ($rw = $stt_usr->fetch(PDO::FETCH_ASSOC)) {
								$prepared_by = $rw['fullname'];
								$username = $rw['username'];
								$doctor_id = $rw['doctor_id'];

								include("doc_incom_body.php");
							}
						} else {
							//echo 'hello';
							///// one paerson /////////	
							$staffname = $_POST['doctors'];
							$parts = explode("__", $staffname);
							$doctor_id = $parts[0];
							$prepared_by = $parts[1];
							$username = $parts[2];
							/// $username=$parts[1];
							///$username=$rw['username'];
							include("doc_incom_body.php");
						}
				?>

						<div align="center"><strong>Summary Report</strong></div>
						<div align="center"><strong>Insured Billed: <?php echo number_format($GrandPClaim); ?></strong>
							&nbsp; | &nbsp;
							<strong>Amount Paid: <?php echo number_format($GrandPPay); ?></strong>
						</div>

				<?php }
				} ?>

				<?php if ($report == 'summary') {
					header("location:index.php?dsry");
				} ?>

			</div>

		</div>
	</div>

</div>