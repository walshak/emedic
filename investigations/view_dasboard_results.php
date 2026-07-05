<?php

include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');

session_start();


if (isset($_POST["doc_"])) {

	$hospital_no = $_POST["doc_"];
	$module = "Dashboard";
	include_once('../doctor/_uploadDocumentModal.php');
?>


	<form action="mgt.php" method="POST" name="subject" enctype="multipart/form-data">
		<div>
			<label for="reg_input_no" class="req">Title: </label>
			<input type="text" maxlength="100" name="title" id="title" class="form-control" placeholder="Document Title" required>
		</div>
		<br>
		<div>
			<label for="reg_input_no" class="req">Document [jpg, png, .pdf] </label>
			<input type="file" name="document_file" id="document_file" class="form-control" required>
		</div>
		<br>

		<div>
			<input type="hidden" name="related_table_id" value="<?= $related_table_id; ?>">
			<input type="hidden" name="target_dir" value="<?= $target_dir; ?>">
			<input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
			<input type="hidden" name="related_table" value="<?= $related_table; ?>">
			<input type="hidden" name="module" value="<?= $module; ?>">
			<button type="submit" class="btn btn-primary" name="uploadDocumentBtn" style="display: block; width:100%;">Submit</button>
		</div>
	</form>

	<div class="card">
		<?php

		$patient_docs = $Document->get(['hospital_no' => $hospital_no], true);

		if (count($patient_docs) > 0) {
		?>
			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>Sn</th>
						<th>Title</th>
						<th>Date Uploaded</th>
						<th>Uploaded By</th>
						<th>Document</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sn = 1;
					foreach ($patient_docs as $key => $patient_doc) {
						$uploaded_by = null;
						$user = $AdminUser->find($patient_doc->created_by);

						if (!empty($user)) {
							$uploaded_by = $user->fullname;
						}
					?>
						<tr>
							<th><?= $sn++; ?></th>
							<th><?= $patient_doc->title; ?></th>
							<th><?= date('d M, Y', strtotime($patient_doc->created_at)); ?></th>
							<th><?= $uploaded_by; ?></th>
							<th><a href="<?= $patient_doc->link; ?>" target="_BLANK"> View Document</a></th>

							<th>
								<?php

								$created = new DateTime($patient_doc->created_at);
								$now = new DateTime();

								$interval = $created->diff($now);
								$within_60days = ($interval->days <= 60);
								if (
									(trim($_SESSION['fullname']) == trim($user->fullname) && $within_60days)
									|| $_SESSION['delete_doc'] == 1
								) {


								?>
									<a href="mgt.php?hosp_no=<?= $hospital_no; ?>&third_party_c=<?= base64_encode($patient_doc->id); ?>"
										onclick="return confirm('Are you sure you want to delete this document?');">
										Delete
									</a>
								<?php
								}

								?>


							</th>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		<?php
		} else {
			echo '<h3> Not available </h3>';
		}
		?>

	</div>



	<?php }



if (isset($_POST["consultations_vitals_id"])) {

	$consultations_vitals_id = $_POST["consultations_vitals_id"];
	$parts = explode('____', $consultations_vitals_id);
	$appointment_no = $parts[0];
	$hospital_number = $parts[1];


	$stmt = $db->prepare("
    SELECT e.*, i.insurance_name, i.interest, i.insurance_type 
    FROM enrollee AS e 
    INNER JOIN insurance_tbl AS i ON e.hmo_no = i.insurance_no 
    WHERE e.hospital_no = ? AND i.status = 'active'");
	$stmt->execute([$hospital_number]);

	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		// Assign values to variables
		$hospital_no = htmlspecialchars($row['hospital_no']);
		$surname = htmlspecialchars($row['surname']);
		$fname = htmlspecialchars($row['fname']);
		$oname = htmlspecialchars($row['oname']);
		$full_name = trim("$surname $fname $oname");

		$insurance_name = htmlspecialchars($row['insurance_name']);
		$insurance_type = htmlspecialchars($row['insurance_type']);
		$age = htmlspecialchars($row['age']);
		$gender = htmlspecialchars($row['gender']);
		$blood_group = htmlspecialchars($row['blood_g']);
		$genotype = htmlspecialchars($row['geno_type']); ?>

		<table class="table table-striped table-bordered table-hover dataTables-example">
			<tr style="background: #666; color: #FFF;">
				<td style="font:bold 14px 'Arial';" width="25%">Patient No:</td>
				<td style="font:bold 14px 'Arial';" width="25%">Patient Name:</td>
				<td style="font:bold 14px 'Arial';" width="25%">Insurance Name:</td>
				<td style="font:bold 14px 'Arial';" width="25%">Insurance Type:</td>
			</tr>
			<tr>
				<td><?= $hospital_no ?></td>
				<td><?= $full_name ?></td>
				<td><?= $insurance_name ?></td>
				<td><?= $insurance_type ?></td>
			</tr>
			<tr style="background: #666; color: #FFF;">
				<td style="font:bold 14px 'Arial';">Age:</td>
				<td style="font:bold 14px 'Arial';">Gender:</td>
				<td style="font:bold 14px 'Arial';">Blood Group:</td>
				<td style="font:bold 14px 'Arial';">Genotype:</td>
			</tr>
			<tr>
				<td><?= $age ?></td>
				<td><?= $gender ?></td>
				<td><?= $blood_group ?></td>
				<td><?= $genotype ?></td>
			</tr>
		</table>

	<?php }


	$notes_type = ($_SESSION['section'] == 'Laboratory') ? 'D' : 'C';

	$rstSelect2 = $db->prepare("
    SELECT * FROM notes 
    WHERE hospital_no = ? 
      AND app_no = ? 
      AND status = '1' 
      AND notes_type IN ('D', 'C') 
    ORDER BY sn DESC 
    LIMIT 10");
	$rstSelect2->execute([$hospital_number, $appointment_no]);

	if ($rstSelect2->rowCount() > 0):
		$n = 1;	?>
		<table id="resp_table" class="table toggle-square" style="font-size: 15px;">
			<thead>
				<tr>
					<th width="2%">Visits</th>
					<th width="78%">Medical Notes</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $rstSelect2->fetch(PDO::FETCH_ASSOC)): ?>
					<tr>
						<td><?= $n++ ?></td>
						<td>
							<?= nl2br(($row['notes'])) ?>
							<br><strong>Entered by:</strong>
							<?= htmlspecialchars($row['prepared_by']) ?> :
							<?= date('d M, Y h:i a', strtotime($row['date_entry'])) ?>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
	<?php endif; ?>


	<strong>VITAL SIGNS</strong>

	<div class="row">
		<div class="col-lg-12">
			<?php
			$vitals_stmt = $db->prepare("
            SELECT * FROM vital_sign 
            WHERE hospital_no = :hospital_no AND status = '1' 
            ORDER BY sn DESC LIMIT 10
        ");
			$vitals_stmt->bindParam(':hospital_no', $hospital_number, PDO::PARAM_STR);
			$vitals_stmt->execute();

			if ($vitals_stmt->rowCount() > 0):
				$_vitals = $vitals_stmt->fetchAll(PDO::FETCH_ASSOC);
				$last = $_vitals[0];
			?>
				<div class="row">
					<div class="col-md-12">
						<p class="text-center"><i><b>
									Last Vitals:
									<?= !empty($last['temp']) ? "Temp: {$last['temp']}<sup>o</sup>C | " : '' ?>
									<?= !empty($last['bp']) ? "BP: {$last['bp']}mmHg | " : '' ?>
									<?= !empty($last['pulse_read']) ? "Pulse: {$last['pulse_read']}bpm | " : '' ?>
									<?= !empty($last['weight']) ? "Weight: {$last['weight']}kg | " : '' ?>
									<?= !empty($last['height']) ? "Height: {$last['height']}m | " : '' ?>
									<?= !empty($last['fbs']) ? "FBS: {$last['fbs']}mmol/L | " : '' ?>
									<?= !empty($last['bmi']) ? "BMI: {$last['bmi']}kg/m&sup2;" : '' ?>
									<br>
									Comments: <?= htmlspecialchars($last['comments']) ?>
								</b></i></p>

						<h4>Vitals History</h4>
						<div style="overflow-x: auto; white-space: nowrap;">
							<table class="table dataTables-example table-bordered" border="2">
								<thead>
									<tr>
										<th>#</th>
										<th>Temp.</th>
										<th>BP(mmHg)</th>
										<th>Pulse</th>
										<th>Resp.</th>
										<th>Weight</th>
										<th>Height</th>
										<th>BMI</th>
										<th>FBS</th>
										<th>RBS</th>
										<th>SPO2</th>
										<th>PPBS</th>
										<th>FHR</th>
										<th>DATE</th>
										<th>CAPTURED BY</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($_vitals as $index => $v): ?>
										<?php
										// Temperature background logic
										$temp = $v['temp'];
										$temp_bg = '#fff';
										if (!empty($temp)) {
											$temp = floatval($temp);
											if ($temp < 36.3) $temp_bg = '#CEF6EC';
											elseif ($temp < 37.5) $temp_bg = '#01A9DB';
											elseif ($temp < 38.4) $temp_bg = '#FE9A2E';
											elseif ($temp < 38.9) $temp_bg = '#F78181';
											else $temp_bg = '#FE2E2E';
										}

										// Blood Pressure logic
										$bp = $v['bp'];
										$bp_bg = '#fff';
										if (!empty($bp)) {
											$bp_vals = explode('/', $bp);
											if (count($bp_vals) === 2) {
												$systolic = intval($bp_vals[0]);
												$diastolic = intval($bp_vals[1]);
												$bp_bg = ($systolic > 120 || $diastolic > 80) ? 'red' : '#CEF6EC';
											}
										}

										$sn = intval($v['sn']);
										?>
										<tr id="vit-tr-<?= $sn ?>">
											<td><?= $index + 1 ?></td>
											<td style="background:<?= $temp_bg ?>; color:white">
												<?= !empty($temp) ? $temp . '<sup>o</sup>C' : '' ?>
											</td>
											<td style="background:<?= $bp_bg ?>; color:white">
												<?= htmlspecialchars($bp) ?>
											</td>
											<td class="text-center"><?= htmlspecialchars($v['pulse_read']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['resp_rate']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['weight']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['height']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['bmi']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['fbs']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['rbs']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['spo2']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['ppbs']) ?></td>
											<td class="text-center"><?= htmlspecialchars($v['FHR']) ?></td>
											<td class="text-center">
												<?= date('d M, Y h:i:s a', strtotime($v['date_ap'])) ?><br>
												<?php if (function_exists('date_diff_day') && date_diff_day($v['date_ap']) < 1 && $_SESSION['fullname'] == $v['prepared_by']): ?>
													<button class="btn btn-sm btn-danger" onclick="deleteVitals(<?= $sn ?>)" id="vit-btn-<?= $sn ?>">[ Delete ]</button>
												<?php endif; ?>
											</td>
											<td><?= htmlspecialchars($v['prepared_by']) ?></td>
										</tr>
									<?php endforeach ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php }

if (isset($_POST["enter_results_id"])) {
	$enter_results_id = $_POST["enter_results_id"];
	$pp = explode("____", $enter_results_id);

	///000001____000005____1----WIDAL TEST----IN----151----Laboratory----2024-07-03 20:45:17----LB0377131651000005----Super Admin--------1----Not Specified------------queue----0------------000001----,________SANI ABUBAKAR ____new____IN____________________new____
	////echo $app_no .'____' . $hosp_no .'____' .$list_tests_sn.'____' .$list_tests.'____' .$patient_name.'____new____' .$type_patient;

	$hosp_no = $pp[1];
	$details = $pp[2];
	$patient_name = $pp[4];
	$type_patient = $pp[6]; ?>

	<form id="move_top">

		<div style="text-align: right;">
			<input type="button" id="closeBtn" value="Close All" onClick="close_dashboard_all()" class="btn btn-danger" />
		</div>

		<table width="100%">
			<tr>
				<td>

					<div align="center">
						<h3><?= $hosp_no  . ' : ' . $patient_name; ?></h3>
						<div id="labrequest_no_display"></div>
						<div id="att_display"></div>
					</div>
				</td>
				<td>
					<div align="right">
						<input type="button" id="enter_rlst_closeBtn" value="Close" onClick="close_dashboard()" class="btn btn-danger" style="display: none;" />
					</div>


				</td>

			</tr>
		</table>



		<div id="enter_result">




			<?php if ($_SESSION['section'] == 'Radiology') { ?>
				<input name="spm" id="spm" value="capture" type="checkbox" style="height: 15px; width: 15px;">&nbsp;<strong style="color:darkblue; font-size: 15px;">Capture Image?</strong>
			<?php } else { ?>
				<div class="form-group">
					<label for="reg_input_no" class="" style="font-size:14px; color: black;">SELECT SPECIMEN</label>
					<select name="Specimen" data-placeholder="Select" class="form-control" id="speciment_taken" onChange="specimen_taken()" required>
						<option value="">Select...</option>
						<option value="Aspirate">Aspirate</option>
						<option value="Urine">Urine</option>
						<option value="Blood">Blood</option>
						<option value="C.S.F">C.S.F</option>
						<option value="Ear Swab">Ear Swab</option>
						<option value="Eye Swab">Eye Swab</option>
						<option value="Fluids">Fluids</option>
						<option value="No Specimen Required">No Specimen Required</option>
						<option value="Pap Smear">Pap Smear</option>
						<option value="Semen">Semen</option>
						<option value="Skin Scraping">Skin Scraping</option>
						<option value="Sputum">Sputum</option>
						<option value="Stool">Stool</option>
						<option value="Throat Swab">Throat Swab</option>
						<option value="Tissue">Tissue</option>
						<option value="Urethral Swab">Urethral Swab</option>
						<option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
						<option value="Vaginal Swab">Vaginal Swab</option>
						<option value="Wound Swab">Wound Swab</option>
					</select>
				</div>
			<?php } ?>


			<div id="collection_notes"></div>

			<div id="mgt_notes_div">
				<?php $section = $_SESSION['section']; ?>
				<div class="row">

					<div class="col-lg-4">

					</div>
					<div class="col-lg-8">
						<div class="form-group">
							<label style="font-size:14px; color: black;"><u>SELECT</u> RESULT TEMPLATE: </label>
							<select class="form-control chosen-select" name="doc_template" id="doc_template" onChange="load_template()" style="font-size:17px;">
								<option value="">-- Select --</option>
								<option value="">Blank Document</option>
								<?php
								$stmt2 = $db->query("SELECT * FROM services_templates where category='$section' order by template_name");
								if ($stmt2->rowCount() > 0) {
									while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
								?>
										<option value="<?php echo $row['id']; ?>"><?php echo $row['template_name']; ?></option>
								<?php }
								} ?>
							</select>
						</div>
					</div>
				</div>


				<div class="ibox-content no-padding">
					<div name="mgt_notes" id="mgt_notes" class="trumbowygEditor" cols="30" rows="10"></div>
				</div>

			</div>

			<div id="form_"></div>
			<div class="form-group">
				<label for="reg_input_no" class="" style="color: black;">COMMENT: </label>
				<input type="text" maxlength="100" name="comment" id="comment" class="form-control" placeholder="Enter Comment" required>
			</div>

			<div style="display: flex; gap: 20px; align-items: flex-start;">
				<!-- Upload Section -->
				<div id="upload_status">
					<table>
						<tr>
							<td>
								<div class="form-group">
									<label for="file_upload" style="color: black;">ATTACH FILE (Optional):</label>
									<input type="file" name="file_upload" id="file_upload" class="form-control">
								</div>
							</td>
							<td>
								<input type="button" name="upload_file" value="Upload Attachment" onClick="uploadFile()" class="btn btn-sm btn-default" />
							</td>
						</tr>
					</table>
				</div>

				<!-- Radio Buttons Section -->
				<div>
					<label for="" style="color: black;">RESULT STATUS (Optional):</label>

					<select id="result_status" class="form-control">
						<option value="">-- Select Result Status --</option>
						<option value="Normal">Normal</option>
						<option value="Abnormal">Abnormal</option>
						<option value="High">High</option>
						<option value="Low">Low</option>
						<option value="Critical">Critical</option>
						<option value="Pending">Pending</option>
						<option value="Inconclusive">Inconclusive</option>
						<option value="Invalid">Invalid</option>
						<option value="Corrected">Corrected</option>
						<option value="Preliminary">Preliminary</option>
						<option value="Final">Final</option>
						<option value="Suppressed">Suppressed</option>
					</select>

				</div>

			</div>

			<input type="hidden" id="test_id" value="">
			<input type="hidden" id="test_name" value="">
			<input type="hidden" id="labrequest_no" value="">
			<input type="hidden" id="labrequest_no_main" value="">
			<input type="hidden" id="cr" value="">
			<input type="hidden" id="Specimen" value="">
			<input type="hidden" id="paystatus" value="">
			<input type="hidden" id="result_type" value="report">
			<?php

			if ($_SESSION['approve'] == 1) { ?>
				<input name="approve_result" id="approve_result" value="1" type="checkbox" style="height: 15px; width: 15px;">&nbsp;
				<strong style="color: red; font-size: 15px;">Check to complete or approve result for visibility.</strong>
				<input type="hidden" value="<?php echo $_SESSION['username']  ?>" id="fullname_approver">
				<input type="hidden" value="<?php echo $_SESSION['username']; ?>" id="main_fullname_approver">
			<?php } else { ?>
				<h4 style="color:red;">
					You are not authorized to approve this result. Please select an approver from the list, or you may save it as "Resulted Only."
				</h4>

				<input name="approve_result" id="approve_result" type="checkbox" value="0" style="height: 15px; width: 15px;" />&nbsp;

				<label style="font-size:14px; color: black;">
					Tick the checkbox and select a result approver from the list:
				</label>

				<select class="form-control" name="fullname_approver" id="fullname_approver" style="font-size:17px;">
					<option value="">-- Select --</option>
					<?php
					$sql = "SELECT a.fullname, a.username 
									FROM admin_users a 
									INNER JOIN invsti_users i ON i.username = a.username 
									INNER JOIN hremp h ON h.EmployeeCode = a.EmployeeCode 
									WHERE a.rights = 'LB' AND i.approve = 1 AND i.section = :section
									ORDER BY a.fullname";

					$stmt = $db->prepare($sql);
					$stmt->bindValue(':section', $_SESSION['section']);
					$stmt->execute();
					$approvers = $stmt->fetchAll(PDO::FETCH_ASSOC);

					foreach ($approvers as $row) {
						$fullname = htmlspecialchars($row['fullname']);
						$username = htmlspecialchars($row['username']);
						echo "<option value=\"$username\">$fullname</option>";
					}
					?>
				</select>
				<input type="hidden" value="" id="main_fullname_approver">



			<?php } ?>
			<div style="display: flex; justify-content: space-between; width: 100%;">
				<input type="button" name="save_result" value="Save Result" onClick="save_results()" data-target="#modal" class="btn btn-primary" />
			</div>

		</div>

		<input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>">
		<input type="hidden" id="session_status" value="<?php echo $_SESSION['section']; ?>">
		<input type="hidden" name="type_patient" value="<?php echo $type_patient; ?>">
		<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>">
		<input type="hidden" name="app_no" value="<?php echo $app_no; ?>">
		<input type="hidden" name="data_capture_status" id="data_capture_status" value="<?php echo $data_capture_status; ?>">

	</form>

	<div id="display_message"></div>
	<div id="show_result_only"></div>
	<input type="hidden" id="load_table_items" value="<?= $enter_results_id; ?>">

<?php } ?>

<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>

<script>
	$(document).ready(function() {
		$('.trumbowygEditor').trumbowyg({
			btns: [
				['viewHTML'],
				['undo', 'redo'], // Only supported in Blink browsers
				['formatting'],
				['strong', 'em', 'del'],
				['superscript', 'subscript'],
				['fontsize'],
				['foreColor', 'backColor'],
				['link'],
				['insertImage'],
				['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
				['unorderedList', 'orderedList'],
				['horizontalRule'],
				['removeformat'],
				['fullscreen']
			],
			plugins: {
				fontsize: {
					sizeList: [
						'12px',
						'14px',
						'16px',
						'18px',
						'20px',
						'24px',
						'32px',
						'48px',
					]
				}
			}
		});

	});
</script>

<script>
	load_table();

	$("#enter_result").hide();

	function close_dashboard_all() {
		$("#enter_results_modal").modal('hide');
	}


	function close_dashboard() {
		const btn = document.getElementById("closeBtn");
		if (btn.style.display === "none") {
			btn.style.display = "inline-block";
		}
		const btn2 = document.getElementById("enter_rlst_closeBtn");
		btn2.style.display = "none";

		$("#display_message").show();
		$("#show_result_only").hide();
		$("#enter_result").hide();
		$("#labrequest_no_display").hide();
		$("#att_display").hide();
		load_table();
		scrollToTop();


	}

	function save_results() {

		var test_id = document.getElementById('test_id').value
		var test_name = document.getElementById('test_name').value
		var labrequest_no = document.getElementById('labrequest_no').value
		var labrequest_no_main = document.getElementById('labrequest_no_main').value
		var cr = document.getElementById('cr').value
		var Specimen = document.getElementById('Specimen').value
		var paystatus = document.getElementById('paystatus').value
		var data_capture_status = document.getElementById('data_capture_status').value
		var result_type = document.getElementById('result_type').value
		var comment = document.getElementById('comment').value
		var session_status = document.getElementById('session_status').value
		var isApproved = document.getElementById('approve_result').checked;
		var main_fullname_approver = document.getElementById('main_fullname_approver').value
		var result_status = document.getElementById('result_status').value

		if (session_status === 'Laboratory') {
			var speciment_taken = document.getElementById('speciment_taken').value
		} else if (session_status === 'Radiology') {
			var speciment_taken = document.getElementById('spm').value
		}

		if (speciment_taken === '' && session_status === 'Laboratory') {
			alert('Please enter a value for Specimen.');
			Specimen.focus();
			return false; // optional: prevents form submission or further execution
		}

		if (isApproved) {
			var approve_result = 1;
			var data_capture_status = 'approve';

			if (main_fullname_approver != '') {
				var fullname_approver = main_fullname_approver
			} else {
				var fullname_approver = document.getElementById('fullname_approver').value
			}


			if (fullname_approver === '') {
				alert('Select Approver before you can continue ..');
				return
			}
		} else {
			var data_capture_status = 'result';
			var fullname_approver = '';
		}



		if (session_status == 'Radiology') {
			var speciment_taken = 'image';
		} else {
			var speciment_taken = document.getElementById('speciment_taken').value
		}

		if (result_type == 'values') {
			///var mgt_notes = document.getElementById('value_input').value

		} else if (result_type == 'options') {
			var mgt_notes = document.getElementById('option_input').value
		} else if (result_type == 'single') {
			var mgt_notes = document.getElementById('single_input').value
		} else {
			var mgt_notes = $("#mgt_notes").html()
		}

		toastr.warning('Please wait...', '', {
			timeOut: 200
		})

		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				mgt_notes: mgt_notes,
				test_id: test_id,
				test_name: test_name,
				labrequest_no: labrequest_no,
				labrequest_no_main: labrequest_no_main,
				cr: cr,
				Specimen: Specimen,
				paystatus: paystatus,
				approve_result: approve_result,
				fullname_approver: fullname_approver,
				comment: comment,
				result_status: result_status,
				speciment_taken: speciment_taken
			},
			success: function(data) {

				toastr.info(data, 'Attention', {
					timeOut: 2000
				})

				$("#enter_result").hide();
				load_table();
				close_dashboard();
			}
		});
	}

	function view_result_only(details) {

		scrollToTop();
		const btn = document.getElementById("closeBtn");
		btn.style.display = "none";
		const btn2 = document.getElementById("enter_rlst_closeBtn");
		document.getElementById("collection_notes").innerHTML = '';

		if (btn2.style.display === "none") {
			btn2.style.display = "inline-block";
		}

		$("#enter_result").hide();
		$("#display_message").hide();
		///$("#display_view_results_list").hide();
		toastr.warning('Wait Please ...', '', {
			timeOut: 2000
		})
		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				show_result_only: details
			},
			success: function(data) {

				$("#show_result_only").show();
				$("#show_result_only").html(data);
				$("#display_message").hide();

			}
		});
	}

	function show_result_sheet(details, labrequest_no) {

		scrollToTop();
		const btn = document.getElementById("closeBtn");
		btn.style.display = "none";

		const btn2 = document.getElementById("enter_rlst_closeBtn");
		if (btn2.style.display === "none") {
			btn2.style.display = "inline-block";
		}

		document.getElementById('result_status').value = '';
		document.getElementById('doc_template').value = '';
		document.getElementById('file_upload').value = '';
		document.getElementById("collection_notes").innerHTML = '';

		$("#enter_result").show();
		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				details: details
			},
			success: function(data) {
				var json = JSON.parse(data);

				///alert(json["data_capture_status"]);
				// = json["data_capture_status"];
				///alert(data);
				///alert(json["attachment"]);

				if (json["attachment"] != '' && json["attachment"] != null && json["attachment"] != 'null') {
					$("#att_display").show();
					$("#upload_status").hide();

					// Construct the file URL correctly
					var fileUrl = 'uploads/' + json["labrequest_no"] + '.' + json["attachment"]; // Added a dot before 'pdf'

					///$("#att_display").html('<i class="fa fa-paperclip"></i> <a href="' + fileUrl + '">Download/View</a> &nbsp : &nbsp <i class="fa fa-trash" style="cursor:pointer;" onclick="if(confirm(\'Are you sure you want to delete this item?\')) { window.location.href=\'mgt.php?hosp_no=' + json["hosp_no"] + '&delRQ=' + json["labrequest_no"] + '\'; }"> Delete</i>');
					$("#att_display").html('<i class="fa fa-paperclip"></i> <a href="' + fileUrl + '" target="_blank">Download/View</a> &nbsp; : &nbsp; <i class="fa fa-trash" style="cursor:pointer;" onclick="if(confirm(\'Are you sure you want to delete this item?\')) { window.location.href=\'mgt.php?hosp_no=' + json["hosp_no"] + '&delRQ=' + json["labrequest_no"] + '\'; }"> Delete</i>');

				} else {
					$("#upload_status").show();
				}


				$("#labrequest_no_display").show();
				document.getElementById('test_id').value = json["test_id"];
				document.getElementById('test_name').value = json["test_name"];
				document.getElementById('labrequest_no').value = json["labrequest_no"];
				$("#labrequest_no_display").html(json["labrequest_no"] + ' : ' + json["test_name"] + ' <b>(LB' + json["lb"] + ')</b>');

				document.getElementById('labrequest_no_main').value = json["labrequest_no_main"];
				document.getElementById('cr').value = json["cr"];
				document.getElementById('Specimen').value = json["Specimen"];
				document.getElementById('paystatus').value = json["paystatus"];
				document.getElementById('result_type').value = json["result_type"];
				document.getElementById('comment').value = json["result_comment"];
				document.getElementById('fullname_approver').value = json["username"];
				document.getElementById('result_status').value = json["result_status"];


				if (json["result_type"] == 'single_report') {
					$("#mgt_notes").html(json["form"]);
					$("#mgt_notes_div").show();
					$("#form_").hide();


				} else if (json["result_type"] == 'report') {
					$("#mgt_notes").html(json["result_note"]);
					$("#mgt_notes_div").show();
					$("#form_").hide();


				} else if (json["result_type"] != 'report') {
					$("#form_").html(json["form"]);
					$("#mgt_notes_div").hide();
					$("#form_").show();

				} else {
					$("#mgt_notes").html(json["result_note"]);
					//	$("#mgt_notes_div").show();
					$("#form_").hide();
				}

				if (json["data_capture_status"] === 'capture') {
					document.getElementById("spm").checked = true;
				}

				if (json["section"] == 'Laboratory') {
					document.getElementById('speciment_taken').value = json["specimen_collected"];

				}

				if (json["collected_notes"] != '') {
					document.getElementById('collection_notes').innerHTML = '<strong>Collection Notes: <br>' + json["collected_notes"] + '</strong><hr>';
				}
				if (json["data_capture_status"] == 'approve') {
					document.getElementById("approve_result").checked = true;

				}

				$("#display_message").hide();

			}
		});
	}

	function load_table() {

		$("#display_message").show();
		var load_table_items = document.getElementById('load_table_items').value;

		document.getElementById("display_message").innerHTML = '<b style="color: red; font-size: 18px;">Please wait...</b>';
		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				load_table_items: load_table_items
			},
			success: function(data) {

				$("#display_message").html(data);
			}
		});

	}

	function load_template() {

		var doc_template = document.getElementById('doc_template').value;
		toastr.info('Please wait...', '', {
			timeOut: 5000
		})
		$.ajax({
			url: "../inc/text_editor2.php",
			method: "POST",
			data: {
				load_template: doc_template
			},
			success: function(data) {


				toastr.clear();
				$("#mgt_notes").html(data);
				// $("#display_message").hide();
			}
		});
	}

	$(document).ready(function() {
		$('.trumbowygEditor').trumbowyg({
			btns: [
				['viewHTML'],
				['undo', 'redo'], // Only supported in Blink browsers
				['formatting'],
				['strong', 'em', 'del'],
				['superscript', 'subscript'],
				['link'],
				['insertImage'],
				['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
				['unorderedList', 'orderedList'],
				['horizontalRule'],
				['removeformat'],
				['fullscreen']
			]
		});
	})

	function scrollToTop() {
		const element = document.getElementById("move_top");
		if (element) {
			element.scrollIntoView({
				behavior: "smooth",
				block: "start"
			});
		}
	}
</script>