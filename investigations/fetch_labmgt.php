<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

$oncredit = $_SESSION['oncredit'];
$oncredit_adm = $_SESSION['oncredit_adm'];


if (isset($_POST["enter_results_id"])) {
	/// 000012__000002__3,2,1,__Creatinine,Potassium,Sodium,
	// $app_no .'____' . $hosp_no .'____' .$list_tests_sn.'____' .
	$enter_results_id = $_POST["enter_results_id"];
	$pp = explode("____", $enter_results_id);
	$app_no = $pp[0];
	$hosp_no = $pp[1];
	$list_tests_sn = $pp[2];
	$list_tests = $pp[3];
	$patient_name = $pp[4];
	$interface = $pp[5];
	$type_patient = $pp[6];
	$list_tests_sn = substr_replace($list_tests_sn, "", -1);
	$th_arrays = explode(",", $list_tests_sn);
	$result = count($th_arrays);



	/// check if patient on-admission
	if ($oncredit == 1) {
		$enable_cr_post = 1;
	} else {
		$stmt_list = $db->query("SELECT hospital_no FROM admission WHERE hospital_no='$hosp_no' and adm_status='3'");
		if ($stmt_list->rowCount() > 0 and $oncredit_adm == 1) {
			$enable_cr_post = 1;
		} else {
			$enable_cr_post = 0;
		}
	}


	//echo $enable_cr_post;


?>



	<?php if ($interface == 'new') {
		$chk = 0;
		$action = 'fillrslt_sheet.php'; ?><h2 style="color: blue;">Mode: ENTER NEW TEST RESULTS</h2><?php } ?>
	<?php if ($interface == 'print') {
		$chk = 1;
		$action = 'printlab.php'; ?><h2 style="color:darkblue;">Mode: PRINT TEST RESULTS</h2><?php } ?>
	<?php if ($interface == 'edit') {
		$chk = 1;
		$action = 'fillrslt_sheet.php'; ?><h2 style="color: coral;">Mode: EDIT TEST RESULTS</h2><?php } ?>

	<p>Check the Square box by the side to make a list of test(s) <br>Click the button at the bottom</p>
	<hr>
	<form method="post" action="<?php echo $action; ?>" id="" name="">
		<?php ///echo '====' . $action; 
		?>




		<div id="enter_result">
			<div class="form-group">
				<label><strong style="font-size:14px; color: black;"><u>Consult</u> using existing Template(s) Below:</strong> </label>
				<select class="form-control" name="doc_template" id="doc_template" onChange="load_template()" style="font-size:17px;">
					<option value="">-- Select --</option>
					<option value="">Blank Document</option>
					<?php
					$stmt2 = $db->query("SELECT * FROM services_templates where category='Consultation'");
					if ($stmt2->rowCount() > 0) {
						while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
					?>
							<option value="<?php echo $row['id']; ?>"><?php echo $row['template_name']; ?></option>
					<?php }
					} ?>
				</select>
			</div>

			<div class="ibox-content no-padding">
				<div name="mgt_notes" id="mgt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px; height: 500px;"></div>
			</div>
			<input type="hidden" id="test_id" value="">
			<input type="hidden" id="test_name" value="">
			<input type="hidden" id="labrequest_no" value="">
			<input type="hidden" id="cr" value="">
			<input type="hidden" id="Specimen" value="">
			<input type="hidden" id="paystatus" value="">

			<?php if ($data_capture_status == 'approve') { ?>checked <?php } ?>

		<input name="approve_result" id="approve_result" type="checkbox" value="1">&nbsp;<strong style="color: red;">Check to Complete/Approve Result</strong>
		<input type="button" name="save_result" value="Save Resultdddddd" onClick="save_results()" data-target="#modal" class="btn btn-primary" />

		</div>


		<hr>
		<table class="table table-striped table-bordered table-hover dataTables-example">
			<thead>
				<tr>
					<th>#</th>
					<th><?php if ($interface == 'new') {
							echo 'Requested Date';
						} else {
							echo 'Requested Date';
						} ?></th>
					<th><?php if ($interface == 'new') {
							echo '';
						} else {
							echo 'Result Date';
						} ?></th>
					<th>Investigation</th>
					<th><?php if ($interface == 'new') {
							echo 'Requested By';
						} else {
							echo 'Result Entered By';
						} ?></th>
					<th>.</th>
					<th>Pay Status</th>
					<th><!--<div class=""><label><input type="checkbox" value="" name="inv[]" onClick="toggle(this)" style="display:block; height:18px; width:18px; "></label></div>--> Check Here</th>
				</tr>
			</thead>
			<tbody>

				<?php
				$n = 1;
				$result_status = '';
				for ($x = 0; $x < $result; $x++) {

					//$roww_['sn']0.'----'.$roww_['test_name']1.'----'.$roww_['business_service_center']2.'----'.$roww_['test_id']3.'----'.$roww_['section']4.'----'.$roww_['request_date']5.'----'.$roww_['labrequest_no']6.'----'.$roww_['request_by']7.'----'.$roww_['requesting_physician']8.'----'.$roww_['lab_combos']9.'----'.$roww_['preferred_specimen']10.'----'.$roww_['result_date']11.'----'.$roww_['entered_by']12.'----'.$roww_['data_capture_status']13.'----'.$day.'----'.$roww_['lab_cat']14 //// 15;


					//$detail=$roww_['sn']0.'----'.$roww_['test_name']1.'----'.$roww_['business_service_center']2.'----'.$roww_['test_id']3.'----'.$roww_['section']4.'----'.$roww_['request_date']5.'----'.$roww_['labrequest_no']6.'----'.$roww_['request_by']7.'----'.$roww_['requesting_physician']8.'----'.$roww_['lab_combos']9.'----'.$roww_['preferred_specimen']10.'----'.$roww_['result_date']11.'----'.$roww_['entered_by']12.'----'.$roww_['data_capture_status']13.'----'.$day.14'----'.$roww_['request_note']15;.14'----'.$roww_['collected_specimen']16;

					$detail = $th_arrays[$x];

					$roww = explode("----", $detail);
					$_detail = $roww[3] . '__' . $roww[1] . '__' . $roww[6] . '__' . $roww[10] . '__' . $roww[12] . '__' . $roww[9] . '__' . $roww[15] . '__' . $roww[13] . '__' . $roww[16];
					///echo '<br>';

					if ($interface == 'new') {

						$dsp_date = $roww[5];
						$dsp_date = date('d,M y h:i a', strtotime($roww[5]));
						$dsp_date11 = '-';
					} else {
						$dsp_date = date('d,M y h:i a', strtotime($roww[5]));
						$dsp_date11 = date('d,M y h:i a', strtotime($roww[11]));
						//$dsp_date=$dsp_date5."/<br>".$dsp_date11;
					}

					if ($interface == 'new') {
						$dsp_by = $roww[7];
					} else {
						$dsp_by = $roww[12];
					}
					$day = $roww[14];
					$data_capture_status = $roww[13];
					if ($data_capture_status == 'result' and $_SESSION['approve'] == '1') {
						$result_status = 'yes';
					}

					//	print $roww[15];
				?>
					<tr>
						<td><?php echo $n; ?></td>
						<td><?php echo $dsp_date; ?></td>
						<td><?php echo $dsp_date11; ?></td>
						<td><?php echo $roww[1]; ?></td>
						<td><?php echo $dsp_by; ?></td>
						<td>
							<input type="button" name="edit" value="Notes " data-target="#modal" id="<?php echo $roww[6]; ?>"
								class="btn btn-success btn-xs view_notes"
								<?php if ($_SESSION['speciality'] == 'Administrator' or $_SESSION['speciality'] == 'Receptionist') { ?> disabled <?php } ?> />
						</td>
						<td>




							<?php
							/// check if 
							$labrequest_no = $roww[6];
							$stmt_list = $db->query("SELECT paystatus,pay_mode,cr FROM patient_ap_services WHERE drug_sn='$labrequest_no'");
							if ($stmt_list->rowCount() > 0) {
								$row = $stmt_list->fetch(PDO::FETCH_ASSOC);
								$paystatus = $row['paystatus'];
								$pay_mode = $row['pay_mode'];
								$cr = $row['cr'];
							} else {
								$pay_lock = 0;
							}


							$pay_lock = 0;
							if ($paystatus == 1 and ($pay_mode == 'cash' or $pay_mode == 'spkage')) {
							?>
								<strong style="color:#009">Paid</strong>
							<?php } elseif ($paystatus == 1 and $pay_mode == 'claim') {
							?>
								<strong style="color:#009">Posted</strong>
							<?php } else {

								if ($enable_cr_post == 1) {
									$pay_lock = 0;
									$b_title = 'Not Paid/Bill(Cr)';
								} elseif ($data_capture_status == 'queue') {
									$pay_lock = 1;
									$b_title = 'Not Paid';
								} else {
									$pay_lock = 0;
									$b_title = 'Not Paid';
								}

							?>
								<strong style="color: #F00"><?= $b_title; ?></strong>
							<?php } ?>

							<?php if ($interface == 'edit' and $day > 2) {
								$pay_lock = 1;
								echo $edit_status = ' [Exceed: 2days]';
							} else {
								$edit_status = '';
							}
							if ($interface == 'print' and $data_capture_status == 'result') {
								$pay_lock = 1;
								echo $edit_status = ' [Approve Pending]';
							} else {
								$edit_status = 'yes';
							}

							?>




						</td>

						<td>

							<?php if ($roww[4] == 'Radiology' and ($_SESSION['speciality'] == 'Administrator'
								or $_SESSION['speciality'] == 'Receptionist') and $data_capture_status == 'approve') { ?>

								<a href="printscan.php?i=<?php echo $labrequest_no; ?>">Print Result</a>

							<?php } elseif ($_SESSION['section'] == 'Radiology') { ?>

								<?php if ($interface == 'print' and $data_capture_status == 'approve') { ?>
									<a href="printscan.php?i=<?php echo $labrequest_no; ?>">Print Result</a>
								<?php } elseif ($pay_lock == 0) { ?>
									<a href="scan_report.php?e=<?php echo $labrequest_no; ?>" class="btn btn-sm btn-info">View</a>
								<?php } ?>
							<?php } else { ?>

								<div class=""><label>
										<input type="checkbox" value="<?php echo $_detail . '__' . $paystatus . '__' . $cr; ?>" name="inv[]" <?php if ($pay_lock == 1) { ?>disabled <?php } ?>
											<?php if ($chk == 1) { ?>checked <?php } ?> style="display:block; height:18px; width:18px;"></label>

									<input type="button" name="edit" value="Notes " onClick=" show_result_sheet('<?php echo $_detail . '__' . $paystatus . '__' . $cr; ?>')" class="btn btn-success btn-xs" />

								</div>

							<?php } ?>


						</td>



					</tr>
				<?php

					$n++;
				} ?>

			</tbody>
		</table>
		<?php

		if ($interface == 'print' and ($_SESSION['section'] == 'Laboratory' or $roww[4] == 'Laboratory')) { ?>
			<table>
				<tr>
					<td style="padding-right: 12px;">
						<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
					</td>

					<td style="padding-right: 12px;">
						<select name="department" class="input-sm chosen-select" style="width:350px;">
							<option selected="selected" value="">Print by Department</option>
							<?php $stmt = $db->query("SELECT * FROM department  where department_type ='Laboratory' order by sn");
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
								<option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
							<?php } ?>
						</select>

					</td>

					<td style="padding-right: 20px;">
						<button type="submit" class="btn btn-success btn-sm" name="display_result_print">Display Result Printout</button>
					</td>
					<td style="padding-right:5px;">
						<input type="checkbox" name="prev_result" value="prev" style="display:block; height:18px; width:18px;">
					</td>
					<td style="padding-top: 10px;"><strong style="color: chocolate;">Check here to see previous/edited result(s)</strong></td>
				</tr>
			</table>

			</td>

		<?php } else { ?>

			<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;

			<?php if ($_SESSION['section'] == 'Laboratory') { ?>
				<button type="submit" class="btn btn-primary btn-sm" name="display_result_sheet">Display Result Sheet</button>
			<?php } ?>

			<?php if ($result_status == 'yes' and $_SESSION['section'] != 'Radiology' and ($_SESSION['speciality'] != 'Administrator' and $_SESSION['speciality'] != 'Receptionist')) { ?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button type="submit" class="btn btn-success btn-sm" name="complete_result" onclick="return confirm('Are you sure you want to Approve?');">Approve/Complete</button>
			<?php } ?>
		<?php } ?>
		<input type="hidden" name="patient_name" value="<?php echo $patient_name; ?>">
		<input type="hidden" name="type_patient" value="<?php echo $type_patient; ?>">
		<input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>">
		<input type="hidden" name="app_no" value="<?php echo $app_no; ?>">


	</form>

<?php

}


?>


<script src="../js/vendors/editor/dist/trumbowyg.js"></script>

<script>
	$("#enter_result").hide();

	function save_results() {


		let mgt_notes = $("#mgt_notes").html()
		///var doc_template = document.getElementById('mgt_notes').innerHTML;
		var test_id = document.getElementById('test_id').value
		var test_name = document.getElementById('test_name').value
		var labrequest_no = document.getElementById('labrequest_no').value
		var cr = document.getElementById('cr').value
		var Specimen = document.getElementById('Specimen').value
		var paystatus = document.getElementById('paystatus').value
		var approve_result = document.getElementById('approve_result').value
		///alert();
		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				mgt_notes: mgt_notes,
				test_id: test_id,
				test_name: test_name,
				labrequest_no: labrequest_no,
				cr: cr,
				Specimen: Specimen,
				paystatus: paystatus,
				approve_result: approve_result
			},
			success: function(data) {
				alert(data);

				////var json = JSON.parse(data);			

			}
		});
	}




	function show_result_sheet(details) {

		///	alert();

		$("#enter_result").show();
		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				details: details
			},
			success: function(data) {
				var json = JSON.parse(data);
				///alert(json["test_id"]);				 
				document.getElementById('test_id').value = json["test_id"];
				document.getElementById('test_name').value = json["test_name"];
				document.getElementById('labrequest_no').value = json["labrequest_no"];
				document.getElementById('cr').value = json["cr"];
				document.getElementById('Specimen').value = json["Specimen"];
				document.getElementById('paystatus').value = json["paystatus"];
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
</script>