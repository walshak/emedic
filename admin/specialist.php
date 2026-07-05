 <?php ////include("../Connections/Conn.php");
	?>
 <?php
	$colordecide = null;

	if (isset($_POST["assign_add_specialist"]) and $_POST["doctor_specialist_selected"] != '') {

		$doc_id = $_POST["doctor_specialist_selected"];
		$specialist_id_ = $_POST["specialist_id_"];

		$delete = $db->prepare("UPDATE admin_users SET specialist = '$specialist_id_' WHERE id = '$doc_id'");
		$deleted = $delete->execute();

		$error_status = 2;
		$error_msg = 'Specialist Assigned';
	} elseif (isset($_POST["assign_add_specialist"]) and $_POST["doctor_consultation"] != '') {

		$serv_id = $_POST["doctor_consultation"];
		$specialist_id_ = $_POST["specialist_id_"];

		$delete = $db->prepare("UPDATE prices_table SET specialist_id = '$specialist_id_' WHERE sn = '$serv_id'");
		$deleted = $delete->execute();

		$error_status = 2;
		$error_msg = 'Service Assigned';
	}


	if (isset($_POST["add_specialist"])) {

		$edit_mode = $_POST["edit_mode"];
		$sn_id = $_POST["sn_id"];
		$specialist = $_POST["specialist"];

		if ($edit_mode == 0) {
			$stmt = $db->prepare('INSERT INTO  specialists (name)  VALUES (?)');
			$updated = $stmt->execute(
				array($specialist)
			);

			$error_status = 2;
			$error_msg = 'Specialty Added Successfully!';
		} else {

			$update = $db->prepare("UPDATE  specialists SET name = '$specialist' WHERE id = ?");
			$update->execute(array($sn_id));
			$error_status = 2;
			$error_msg = 'Specialty Updated Successfully!';
		}
	}



	if (isset($_GET["dlx"])) {

		$dl = $_GET['dlx'];
		$stmt = $db->query("SELECT * FROM admin_users where specialist='$dl'");
		if ($stmt->rowCount() == 0) {

			$stmt = $db->query("SELECT * FROM prices_table where specialist_id='$dl'");
			if ($stmt->rowCount() == 0) {

				$deleteSQL = $db->prepare("DELETE FROM specialists WHERE id='$dl'");
				$deleteSQL->execute();
				$error_status = 2;
				$error_msg = 'Deleted Successfully!';
			} else {

				$error_status = 1;
				$error_msg = 'Unable to Deleted!';
			}
		}
	}


	if (isset($_GET["rmv"])) {

		$rmv = $_GET['rmv'];
		$update = $db->prepare("UPDATE  admin_users SET specialist =null WHERE id = ?");
		$update->execute(array($rmv));

		$error_status = 2;
		$error_msg = 'Removed Successfully!';
	}
	if (isset($_GET["rmv_service"])) {

		$rmv = $_GET['rmv_service'];
		$update = $db->prepare("UPDATE  prices_table SET specialist_id =null WHERE sn = ?");
		$update->execute(array($rmv));

		$error_status = 2;
		$error_msg = 'Removed Successfully!';
	}




	?>


 <div class="row">
 	<div class="col-lg-12">
 		<div class="ibox float-e-margins">
 			<div class="ibox-title">
 				<h5><?php echo $title; ?></h5>
 			</div>
 			<div class="ibox-content">

 				<?php

					if (isset($_GET["Specialist"])) {
						/// there is someting there ////
						$edit_mode = 1;
						$sn = $_GET["Specialist"];


						$stmt = $db->query("SELECT * FROM specialists where id='$sn'");
						if ($stmt->rowCount() > 0) {
							$row = $stmt->fetch(PDO::FETCH_ASSOC);
						} else {
							$edit_mode = 0;
						}
					} else {
						$edit_mode = 0;
					}
					?>


 				<form action="" method="POST" id="subject" name="subject" enctype="multipart/form-data">

 					<div class="form_sep">
 						<h3 class="req">Enter New or Edit Specialist</h3>
 						<input type="text"
 							name="specialist"
 							class="form-control"
 							maxlength="100"
 							value="<?php echo isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>"
 							required
 							style="font-size:16px;">
 					</div>



 					<div class="form_sep">
 						<button class="btn btn-success" type="submit" name="add_specialist"><?php if ($edit_mode == 1) { ?> Save <?php } else { ?>Create <?php $edit_mode = 0;
																																						} ?></button>
 						&nbsp;&nbsp; : &nbsp;&nbsp;
 						<a href="index.php" class="btn btn-warning">Cancel</a>
 					</div>
 					<input type="hidden" name="sn_id" value="<?php echo $row['id']; ?>" />
 					<input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
 				</form>

 				<hr>

 				<?php

					$stmt = $db->query("SELECT * FROM specialists order by name");
					if ($stmt->rowCount() > 0) {
					?>

 					<h2 style="color:red;">
 						This page is strictly for assigning consultants to their areas of specialty and the services they attend to for appointment/queuing purposes — not <u> <i>Medical Officers (MOs), Nurses or other staff</i></u>. A doctor will not see a patient if the specialist is not properly mapped, and services must also be mapped to the appropriate specialty.
 						<br>
 						Avoid mapping the same service to multiple specialties, and avoid assigning a consultant to multiple specialties.
 					</h2>
 					<hr>



 					<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9" style="font-size: 16px;">
 						<thead>
 							<tr>
 								<th width="2%">#</th>
 								<th width="28%">List of Specialties</th>
 								<th width="35%" style="color:darkred; font-size:16px;"><i>CONSULTANT(s)</i> Assigned to Each Specialty</th>
 								<th width="35%" style="color:blue; font-size:16px;"><i>SERVICE(s)</i> Assigned to Each Specialty</th>
 							</tr>
 						</thead>
 						<tbody>

 							<?php
								$n = 1;
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
									if ($colordecide % 2 == 0) {
										$bgcolor = "#F4F4F4";
									} else {
										$bgcolor = "#FFFFFF";
									}
								?>
 								<tr bgcolor="<?php echo $bgcolor; ?>">
 									<td><?php echo $n; ?></td>
 									<td><b style="font-size: 16px;"><?php echo $row['name']; ?></b><br>
 										<a href="index.php?Specialist=<?= $row['id']; ?>" class="btn btn-warning btn-xs">Edit</a>
 										<a href="index.php?Specialist&dlx=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete?')" class="btn btn-danger btn-xs">Del</a>
 									</td>
 									<td>
 										<?php
											$id = $row['id'];
											$stmyt = $db->query("SELECT id,fullname,EmployeeCode,phone_number FROM admin_users where specialist='$id'");
											if ($stmyt->rowCount() > 0) {
												while ($rowxx = $stmyt->fetch(PDO::FETCH_ASSOC)) {
													echo '<strong>' . $rowxx['fullname'] . '</strong> <br>( ' . $rowxx['phone_number'] . ' )'; ?>
 												<a href="index.php?Specialist&rmv=<?= $rowxx['id']; ?>" onclick="return confirm('Are you sure you want to remove?');" class="btn btn-danger btn-xs">Remove</a><br>
 										<?php

												}
											} else {
												echo '<b>No Consultant Assign this Specialty</b>';
											}
											?>
 										<br>
 										<input type="button" name="edit" value="Click to Assign Consultant" data-target="#myModal5" id="<?php echo $row["id"] . '___consultant'; ?>" class="btn btn-success btn-xs add_specialist" />

 									</td>
 									<td><?php

											$stmyt = $db->query("SELECT * FROM prices_table 
				where specialist_id='$id' and price_table='Consultation' order by item_service");
											if ($stmyt->rowCount() > 0) {
												while ($rowx = $stmyt->fetch(PDO::FETCH_ASSOC)) {
													$hosp_price = str_replace(',', '', trim($rowx['hosp_price']));
													$file_amt   = str_replace(',', '', trim($rowx['file_amt']));

													echo '- ' . $rowx['item_service'] .
														'<br>&nbsp;&nbsp;<b><u>Amount:</u></b> N' .
														(is_numeric($hosp_price) ? number_format($hosp_price) : $rowx['hosp_price']) .
														' & <b><u>File/Duration:</u></b> ' .
														(is_numeric($file_amt) ? number_format($file_amt) : $rowx['file_amt']) .
														' /' . $rowx['duration'] . ' day(s)';											?>
 												<a href="index.php?Specialist&rmv_service=<?= $rowx['sn']; ?>" onclick="return confirm('Are you sure you want to remove?');" class="btn btn-danger btn-xs">Remove</a><br>

 										<?php }
											} else {
												echo '<strong>No Service Assign this Specialty</strong>';
											}
											?>

 										<br>
 										<input type="button" name="edit" value="Click to Assign Service" data-target="#myModal5" id="<?php echo $row["id"] . '___services'; ?>" class="btn btn-info btn-xs add_specialist" />

 									</td>

 								</tr>
 							<?php
									$colordecide++;
									$n++;
								} ?>

 						</tbody>
 						<tfoot class="hide-if-no-paging">
 							<tr>
 								<td colspan="8" class="text-center">
 									<ul class="pagination pagination-sm"></ul>
 								</td>
 							</tr>
 						</tfoot>
 					</table>
 				<?php } else {
						echo 'No Records Found';
					}

					?>

 			</div>
 		</div>
 	</div>
 </div>