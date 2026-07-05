<?php


if (isset($_POST["save_notes"])) {
	$app_no = $_POST['app_no'];
	$sn = $_POST['sn'];
	$hosp_no = $_POST['hos_no'];
	$setdate = date('Y-m-d h:i:sa');

	$mode = $_POST['mode'];


	if ($mode == 'edit') {
		$stmt = $db->prepare("UPDATE notes SET notes=:notes WHERE sn=:sn");
		$stmt->bindParam(':notes', $_POST['pro_note']);
		$stmt->bindParam(':sn', $sn);
		$stmt->execute();
		header("location:index.php?progress&hp=$hosp_no");
	} else {
		$compl = '<strong>' . 'Nurse Progress Note: ' . '</strong></br>' . 'FOCUS: ' . $_POST['focus'] . '</br>' . 'NOTE: ' . $_POST['pro_note'];

		if ($compl != '') {
			$insertSQL = $db->prepare("INSERT INTO notes(app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry) VALUES (:app_no, :hospital_no, :notes, :tag, :notes_type, :prepared_by, :date_entry)");
			$insertSQL->bindParam(':app_no', $_POST['app_no']);
			$insertSQL->bindParam(':hospital_no', $_POST['hos_no']);
			$insertSQL->bindParam(':notes', $compl);
			$insertSQL->bindParam(':tag', $_SESSION['rights']);
			$insertSQL->bindParam(':notes_type', 'note');
			$insertSQL->bindParam(':prepared_by', $_SESSION['fullname']);
			$insertSQL->bindParam(':date_entry', $setdate);
			$insertSQL->execute();
			header("location:index.php?progress&hp=$hosp_no");
		} else {
			$err = 1;
		}
	}
}



if (isset($_GET['del'])) {

	$del = $_GET['del'];
	$update = "DELETE FROM notes WHERE sn='$del'";
	$db->exec($update);
	//$delete_del = "DELETE FROM notes WHERE sn='$del";
	//		$db->exec($delete_del);		
	header("location:index.php?progress&deleted");
}



if (isset($_GET['hp'])) {
	$hp = $_GET['hp'];
	$hos_no = $_GET['hp'];
	$searchpart = "and a.hospital_no='$hp'";

	$stmt = $db->query("SELECT e.surname,e.gender,e.age,e.fname,e.blood_g,i.insurance_name,i.interest,i.payment_mode,i.services_access,i.insurance_type,i.insurance_no FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE hospital_no='$hos_no' and status='active'");
	if ($stmt->rowCount() > 0) {
		$rowap = $stmt->fetch(PDO::FETCH_ASSOC);
		$insurance = $rowap['insurance_type'];
		$interest = $rowap['interest'];
		$names = $rowap['surname'] . ' ' . $rowap['fname'];
		$insurance_no = $rowap['insurance_no'];
		$insurance_name = $rowap['insurance_name'];
		$payment_mode = $rowap['payment_mode'];
		$insurance_status = ($insurance === 'NHIS') ? 1 : 0;
	}
} else {
	$searchpart = "";
}


?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox ">
			<!--<div class="ibox-title"><h5>Nursing Consumables</h5></div>
-->

			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r">
						<form action="index.php?cn=<?php echo $hos_no; ?>" method="POST" id="subject" name="subject">
							<h3>Patient On-admission Progress Notes</h3>
							<table width="100%">
								<tr>
									<td>
										<table width="100%">
											<tr>
												<td>
													<div class="form_sep">
														<label for="reg_select" class="">View by Doctors/Nurses</label>
														<select name="staff" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="staff" class="form-control" data-required="true">
															<option selected="selected" value="<?php if (isset($_GET['staff'])) {
																									echo $_GET['staff'];
																								} ?>"><?php if (isset($_GET['staff'])) {
																											echo $_GET['staff'];
																										} else {
																											echo 'Choose Staff';
																										} ?></option>
															<?php
															$stmt = $db->query(sprintf("SELECT distinct prepared_by FROM notes where notes_type='note' GROUP BY prepared_by ASC"));
															while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
																<option value="<?php echo 'index.php?progress' . '&staff=' . $row2['prepared_by']; ?>">
																	<?php echo $row2['prepared_by']; ?></option>
															<?php } ?>
														</select>
													</div>
												</td>

												<td style=" padding-left:5px;">
													<div class="form_sep">
														<label for="reg_select" class="">View by Category</label>
														<select name="cat" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="cat" class="form-control" data-required="true">
															<option selected="selected" value="<?php if (isset($_GET['cat'])) {
																									echo $_GET['cat'];
																								} ?>"><?php if (isset($_GET['cat'])) {
																											if ($_GET['cat'] == 'DR') {
																												echo 'Doctors Notes';
																											} else {
																												echo 'Nurses Notes';
																											}
																										} else {
																											echo 'Choose Category';
																										} ?></option>
															<option value="<?php echo 'index.php?progress' . '&cat=DR'; ?>">Doctors Notes </option>
															<option value="<?php echo 'index.php?progress' . '&cat=NS'; ?>">Nurses Notes </option>
														</select>
													</div>
												</td>
											</tr>
											<tr>
												<td style=" padding-top:10px;">
													<div class="form_sep">

														<a href="index.php?progress" class="btn btn-success btn-sm"><span class="fa fa-comments-o"></span>&nbsp;Show all Notes</a>
														&nbsp; &nbsp;

														<?php if (isset($_GET['hp'])) { ?>
															<a href="index.php?hosp_no=<?php echo $hos_no; ?>" class="btn btn-info btn-sm"><span class="fa fa-user"></span>&nbsp;Patient Home</a>
														<?php } else { ?>
															<a href="index.php" class="btn btn-warning btn-sm"><span class="fa fa-times"></span>&nbsp;Close</a>
														<?php } ?>

													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</form>


					</div>

					<div class="col-sm-6">

						<div class="panel-body">

							<?php if (isset($_GET['hp'])) { ?>
								<table>
									<tr>
										<td style=" padding-right:10px;">
											<img src="<?php if (file_exists(enrollee_p . $hos_no . '.' . 'jpg')) {
															echo enrollee_p . $hos_no . '.' . 'jpg';
														} else {
															echo '../img/no_photo.jpg';
														} ?>" alt="" height="75" width="75" class="img-thumbnail user_avatar">
										</td>
										<td>
											<span style="font-size:18px"><?php echo $hos_no; ?></span>
											<span style="font-size:25px"><?php echo ' / ' . $rowap['surname'] . ', ' . $rowap['fname'] . ' ' . $rowap['oname']; ?>
											</span>

											<br>

											<table class="table border">
												<tr>
													<td><strong>Gender:</strong>&nbsp;<?php echo $rowap['gender']; ?></td>
													<td><strong>Age:&nbsp;</strong><?php echo $rowap['age']; ?></td>
													<td><strong>Blood/Group:&nbsp;</strong><?php echo $rowap['blood_g']; ?></td>
												</tr>
												<tr>
												</tr>
											</table>

										</td>
									</tr>
								</table>

							<?php } else { ?>
								<br>
								<div class="form_sep">
									<label for="reg_select" class="">View Progress Notes by patients</label>
									<select name="staff" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="staff" class="form-control" data-required="true">
										<option selected="selected" value="">Sort by patients</option>
										<?php
										$stmt = $db->query("SELECT a.*,e.surname,e.fname FROM admission as a inner join enrollee as e on e.hospital_no=a.hospital_no WHERE adm_status=3");
										while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
											<option value="<?php echo 'index.php?progress' . '&hp=' . $row2['hospital_no']; ?>">
												<?php echo $row2['hospital_no'] . '/ ' . $row2['surname'] . ' ' . $row2['fname']; ?></option>
										<?php } ?>
									</select>
								</div>


							<?php } ?>



						</div>
					</div>

				</div>
			</div>

		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="ibox ">

			<div class="ibox-content">



				<table class="table table-striped table-bordered table-hover dataTables-example">
					<thead>
						<tr>
							<th width="7%"></th>
							<th data-toggle="true" width="62%">Notes</th>
						</tr>
					</thead>
					<tbody>
						<?php



						$stmt = $db->query("SELECT a.*,e.surname,e.fname FROM admission as a inner join enrollee as e on e.hospital_no=a.hospital_no WHERE adm_status=3 $searchpart");
						if ($stmt->rowCount() == 0) {
							echo '<strong>No Records Found!</strong>';
						} else {
							while ($row_adm = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$app_no = $row_adm['app_no'];
								$hospital_no = $row_adm['hospital_no'];

								if (isset($_GET['staff'])) {
									$staff = $_GET['staff'];
									$stmt22 = $db->prepare("SELECT * FROM notes WHERE app_no=:app_no AND hospital_no=:hospital_no AND prepared_by=:prepared_by AND (notes_type='note' OR notes_type='plan') ORDER BY sn DESC");
									$stmt22->bindParam(':app_no', $row_adm['app_no']);
									$stmt22->bindParam(':hospital_no', $row_adm['hospital_no']);
									$stmt22->bindParam(':prepared_by', $staff);
									$stmt22->execute();
								} elseif (isset($_GET['cat'])) {
									$stmt22 = $db->prepare("SELECT * FROM notes WHERE app_no=:app_no AND hospital_no=:hospital_no AND tag=:tag AND (notes_type='note' OR notes_type='plan') ORDER BY sn DESC");
									$stmt22->bindParam(':app_no', $row_adm['app_no']);
									$stmt22->bindParam(':hospital_no', $row_adm['hospital_no']);
									$stmt22->bindParam(':tag', $_GET['cat']);
									$stmt22->execute();
								} else {
									$stmt22 = $db->prepare("SELECT * FROM notes WHERE app_no=:app_no AND hospital_no=:hospital_no AND (notes_type='note' OR notes_type='plan') ORDER BY sn DESC");
									$stmt22->bindParam(':app_no', $app_no);
									$stmt22->bindParam(':hospital_no', $hospital_no);
									$stmt22->execute();
								}


								if ($stmt22->rowCount() > 0) {
						?>


									<tr>
										<td></td>
										<td bgcolor="#CCFF99">
											<div>
												<?php $names = $row_adm['surname'] . ' ' . $row_adm['fname'];
												echo $row_adm['hospital_no'] . '<strong style="font-size:20px;">' . ' / ' . $row_adm['surname'] . ' ' . $row_adm['fname'] . '</strong>'; ?>
											</div>

											<div>
												<a href="index.php?mc=<?php echo $row_adm['hospital_no']; ?>" class="btn btn-info btn-xs">Medication Charts</a>
											</div>

										</td>



									</tr>

									<?php while ($row = $stmt22->fetch(PDO::FETCH_ASSOC)) { ?>
										<tr>
											<td><?php echo date('d, M Y', strtotime($row['date_entry'])) . '<br>' .
													date('h:i:s a', strtotime($row['date_entry'])); ?></td>
											<td><?php echo $row['notes'] .
													'<br>'; ?>

												<?php if ($_SESSION['fullname'] == $row['prepared_by']) { ?>
													<input type="button" name="fill_result" value="Edit" data-target="#modal" id="<?php echo $row["sn"] . '__' . $row_adm['surname'] . ' ' . $row_adm['fname'] . '__' . $hospital_no; ?>" class="btn btn-warning btn-xs edit_notes" />
													&nbsp;|&nbsp;
													<a href="index.php?progress&del=<?php echo $row['sn']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
												<?php }

												echo '<strong><i>' . 'Noted by: ' . $row['prepared_by'] . '</i></strong>'; ?>

											</td>
										</tr>
									<?php 	} ?>
									<tr>
										<td bgcolor="#CCCC99"></td>
										<td bgcolor="#CCCC99">
											<div align="">

												<?php echo $names; ?>
												&nbsp;|&nbsp;
												<input type="button" name="fill_result" value="Add a New Note" data-target="#modal" id="<?php echo $app_no . '__' . $hospital_no . '__' . $names; ?>" class="btn btn-success btn-xs progress_notes" />
											</div>
										</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
									</tr>
								<?php
								} else { ?>

									<tr>
										<td></td>
										<td bgcolor="#CCFF99">
											<div>
												<?php $names = $row_adm['surname'] . ' ' . $row_adm['fname'];
												echo $row_adm['hospital_no'] . '<strong style="font-size:20px;">' . ' / ' . $row_adm['surname'] . ' ' . $row_adm['fname'] . '</strong>'; ?>
											</div>

											<div>
												<input type="button" name="fill_result" value="Add a New Note" data-target="#modal" id="<?php echo $row_adm["app_no"] . '__' . $row_adm["hospital_no"] . '__' . $row_adm['surname'] . ' ' . $row_adm['fname']; ?>" class="btn btn-success btn-xs progress_notes" />

												&nbsp;|&nbsp;

												<input type="button" name="fill_result" value="Medication Charts" data-target="#modal" id="<?php echo $row_adm["app_no"] . '__' . $row_adm["hospital_no"] . '__' . $row_adm['surname'] . ' ' . $row_adm['fname']; ?>" class="btn btn-info btn-xs progress_notes" />


											</div>
										</td>
									</tr>
									<tr>
										<td></td>
										<td></td>
									</tr>

						<?php			}
							}
						}
						?>

					</tbody>
					<tfoot class="hide-if-no-paging">
						<tr>
							<td colspan="6" class="text-center">
								<ul class="pagination pagination-sm"></ul>
							</td>
						</tr>
					</tfoot>
				</table>



			</div>


		</div>
	</div>
</div>



<div class="modal inmodal fade" id="view_progress_note_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Progress Note</h4>
			</div>
			<div class="modal-body" id="view_progress_note">
			</div>
		</div>
	</div>
</div>