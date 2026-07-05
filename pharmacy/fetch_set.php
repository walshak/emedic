 <?php

	use PHPUnit\Util\Printer;

	include("../Connections/Conn.php"); ?>
 <?php

	$C = null;
	$D = null;
	$Notes = null;
	$plan = null;
	$Pharmacy = null;
	$lab = null;

	session_start();

	if (isset($_POST["note_inv_id"])) {
		$note_inv_id = $_POST["note_inv_id"];
		$part = explode("__", $note_inv_id);
		$sn = $part[0];
		$stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = :sn");
		$stmt->execute([':sn' => $sn]);
		$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($row_d) {
			$emr = $row_d['hospital_no'];
			$item_services = $row_d['item_services'];
			$pay = $row_d['pay'];
			$drug_sn = $row_d['drug_sn'];
		}


		$dept_id = $_SESSION['dept_id'];

		$stmt = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id ORDER BY sn DESC LIMIT 1");
		$stmt->execute([':stock_sn' => $drug_sn, ':cust_patient_id' => $dept_id]);

		if ($stmt->rowCount() > 0) {
			$rw = $stmt->fetch(PDO::FETCH_ASSOC);
			$current_qty = $rw['bal'];
		} else {
			$stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :sn");
			$stmt->execute([':sn' => $drug_sn]);

			if ($stmt->rowCount() > 0) {
				$rw = $stmt->fetch(PDO::FETCH_ASSOC);
				$current_qty = $rw['qty'];
			} else {
				$current_qty = 0;
			}
		}
	?>

 	<table class="table table-bordered">
 		<tbody>

 			<tr>
 				<td><b>Item Services:</b> </td>
 				<td><b><?php echo $row_d['item_services']; ?></b></td>
 			</tr>
 			<tr>
 				<td><b>Prescribed By:</b> &nbsp; <?php echo $row_d['prepared_by']; ?></td>
 				<td><b>Date Prescribed</b> &nbsp; <?php echo date("d M Y H:i:s a ", strtotime($row_d['date_entry'])); ?></td>
 			</tr>
 			<tr>
 				<td><b>Hospital Price:</b> &nbsp; <?php echo $row_d['hosp_price']; ?></td>
 				<td><b>Claim Price:</b> &nbsp; <?php echo $row_d['claim_amt']; ?></td>
 			</tr>
 			<tr>
 				<td><strong style="color: red;">Current Quantity / Request Quantity::</strong> </td>
 				<td><?php echo $current_qty; ?> / <?php echo $row_d['qty']; ?></td>
 			</tr>



 			<tr>
 				<td><b>Invoice Date / Status: </b></td>
 				<td><?php if ($row_d['invoice_date'] == '' or $row_d['invoice_date'] == '0000-00-00') {
							echo '';
						} else {
							echo date("d M Y H:i:s a ", strtotime($row_d['invoice_date']));
						} ?> / <?php if ($row_d['invoice_status'] == 0) {
									echo 'Invoice Pending';
								} else {
									echo 'Invoice Ready';
								} ?></td>
 			</tr>
 			<tr>
 				<td><b>Invoice By:</b> </td>
 				<td><?php echo $row_d['invoice_by']; ?></td>
 			</tr>
 			<tr>
 				<td><b>Amount: </b></td>
 				<td><?php echo $pay; ?>&nbsp; / Credit Status:<?php if ($row_d['cr'] == 1) {
																	echo 'Credit';
																} else {
																	echo 'No';
																} ?></td>
 			</tr>
 			<tr>
 				<td><b>Paid Status:</b> </td>
 				<td><?php if ($row_d['paystatus'] == 1) {
							echo 'Amount Paid';
						} else {
							echo 'Pending';
						} ?></td>
 			</tr>
 			</tr>

 		</tbody>
 	</table>

 	<div class="alert alert-info">
 		<b>Notify/Send a Message to the Requester/Doctor regarding the selected drug name. </b>

 		<div class="form-group">
 			<div id="edit__mode" style="color: red;"></div>
 			<textarea name="" class="form-control" cols="45" rows="2" placeholder="" style="font-size:18px" id="drug_note" required></textarea>
 		</div>

 		<button type="button" class="btn btn-sm btn-success" id="notify_requester_btn" name="" onclick="notify_requester()">Send Notes</button>
 		<input type="hidden" name="" id="drug_name_" value="<?= $row_d['item_services']; ?>">
 		<input type="hidden" name="" id="reciever" value="<?= $row_d['prepared_by']; ?>">
 		<input type="hidden" name="" id="patient_hosp_no" value="<?= $row_d['hospital_no']; ?>">
 	</div>
 	<div id="pharm_doctor_chat_displayed"><strong style="color: red;">Loading ... Please Wait!</strong></div>


 <?php }


	if (isset($_POST["add_drug_id"])) {
		$add_drug_id = $_POST["add_drug_id"];
		$part = explode("__", $add_drug_id);
		$sn = $part[0];
		$hmo = $part[2];
		$hmo_type = $part[3];

		$stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = :sn");
		$stmt->execute([':sn' => $sn]);
		$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

		$hospital_no = $row_d['hospital_no'];
		$item_services = $row_d['item_services'];
		$pay = $row_d['pay'];
		$claim_amt = $row_d['claim_amt'];
		$drug_sn = $row_d['drug_sn'];

		$stmt = $db->prepare("SELECT * FROM hmo_stocks_tariff WHERE stock_sn = :stock_sn AND (hmo = :hmo OR hmo = :hmo_type)");
		$stmt->execute([':stock_sn' => $drug_sn, ':hmo' => $hmo, ':hmo_type' => $hmo_type]);

		if ($stmt->rowCount() > 0) {
			$row_dx = $stmt->fetch(PDO::FETCH_ASSOC);
			$claim_amt = $row_dx['price'];
		}


	?>

 	<h4>Are you sure you want to add drug?</h4>
 	<hr>

 	<table class="table table-bordered">
 		<tbody>
 			<tr>
 				<td>Item Services: </td>
 				<td><?php echo $row_d['item_services']; ?></td>
 			</tr>
 			</tr>
 		</tbody>
 	</table>

 	<form action="index.php?presc&hos_no=<?php echo $hospital_no; ?>" method="POST">

 		<div class="form_sep">
 			<label for="reg_textarea_message" class="req">Enter Qty</label>
 			<input type="number" id="qty" name="qty" class="form-control" onkeyup="sum();" min="1" value="1" required />
 		</div>
 		</div>

 		<div class="form_sep">
 			<button class="btn btn-success btn btn-sm" type="submit" name="add_drug_invoice" id="add_drug_invoice">Add & Invoice</button>
 		</div>
 		<input type="hidden" name="pay" value="<?php echo $pay; ?>" />
 		<input type="hidden" name="claim_amt" value="<?php echo $claim_amt; ?>" />
 		<input type="hidden" name="sale_sn" value="<?php echo $sn; ?>" />
 		<input type="hidden" name="drug_sn" value="<?php echo $drug_sn; ?>" />
 		<input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>" />
 	</form>

 	<?php }


	if (isset($_POST["refill_drug_id"]) or isset($_POST["dsp_oncredit_id"]) or isset($_POST["reverse_id"])) {
		// echo $_POST["refill_drug_id"];
		$target = "presc&hos_no=";
		include("../inc/dsp_rvs.php");
	}

	if (isset($_POST["plan"])) {
		$hos_no = $_POST["plan"];

		$stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no = :hospital_no AND notes_type = 'plan' ORDER BY sn DESC LIMIT 300");
		$stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() > 0) { ?>

 		<a id="target2"></a>
 		<table class="table table-striped table-bordered table-hover dataTables-example">
 			<thead>
 				<tr>
 					<th width="15%">Date</th>
 					<th width="60%">Notes</th>
 					<th width="20%">Noted By</th>
 				</tr>
 			</thead>
 			<tbody>
 				<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
 					<tr>
 						<td><?php echo date('d M,Y h:i a', strtotime($row['date_entry'])); ?></td>
 						<td><?php echo $row['notes']; ?> </td>
 						<td><?php echo $row['prepared_by']; ?></td>
 					</tr>
 				<?php } ?>
 			</tbody>
 			<tfoot class="hide-if-no-paging">
 				<tr>
 					<td colspan="6" class="text-center">
 						<ul class="pagination pagination-sm"></ul>
 					</td>
 				</tr>
 			</tfoot>
 		</table>

 	<?php } else { ?>

 		<div class="alert alert-info"><strong>No Existing Note(s)</strong></div>

 <?php }
	}
	?>


 <?php if (isset($_POST["bio_data_id"])) {
		$hos_no = $_POST["bio_data_id"];
		include("../inc/bio_data.php");
	}

	?>
 <?php
	if (isset($_POST["encounters"])) {



		$hospitalNo = $_POST["encounters"];
		$consultationType = $_POST["encounters_form_c_type"];
		$startDate = $_POST["encounters_form_start_date"];
		$endDate = $_POST["encounters_form_end_date"];

		// Validate required fields
		if (empty($consultationType) || empty($startDate) || empty($endDate)) {
			return;
		}

		// Handle regular consultation notes
		if ($consultationType !== 'VITALS') {

			displayConsultationNotes($db, $hospitalNo, $consultationType, $startDate, $endDate);
		} else {
			displayVitalSigns($db, $hospitalNo, $startDate, $endDate);
		}
	}

	/**
	 * Display consultation notes for a given period
	 */
	function displayConsultationNotes($db, $hospitalNo, $consultationType, $startDate, $endDate)
	{
		$dates = fetchUniqueDates($db, $hospitalNo, $startDate, $endDate);

		if (empty($dates)) {
			echo '<div class="alert alert-danger"><strong> Records Not Available </strong></div>';
			return;
		}

		displayConsultationTable($db, $hospitalNo, $dates, $consultationType);
	}

	/**
	 * Fetch unique dates for consultation records
	 */
	function fetchUniqueDates($db, $hospitalNo, $startDate, $endDate)
	{
		$sql = "SELECT DISTINCT date_entry FROM notes 
            WHERE hospital_no = :hospital_no 
            AND DATE(date_entry) BETWEEN :start_date AND :end_date 
            ORDER BY sn DESC";

		$stmt = $db->prepare($sql);
		$stmt->bindParam(':hospital_no', $hospitalNo, PDO::PARAM_STR);
		$stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
		$stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
		$stmt->execute();

		$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Extract and uniquify dates
		$dates = array();
		foreach ($entries as $entry) {
			$dates[] = date('Y-m-d', strtotime($entry['date_entry']));
		}

		return array_unique($dates);
	}

	/**
	 * Build SQL query based on consultation type
	 */
	function buildNotesQuery($consultationType)
	{
		$baseQuery = "SELECT DISTINCT DATE(date_entry), sn, hospital_no, notes_type, notes, prepared_by 
                  FROM notes 
                  WHERE hospital_no = :hospital_no 
                  AND DATE(date_entry) = :effect_date";

		switch ($consultationType) {
			case 'COMPLAINT':
				return $baseQuery . " AND notes_type = 'C' ORDER BY date_entry";
			case 'DIAGNOSIS':
				return $baseQuery . " AND notes_type = 'D' ORDER BY date_entry";
			default:
				return $baseQuery . " ORDER BY date_entry";
		}
	}

	/**
	 * Display consultation notes table
	 */
	function displayConsultationTable($db, $hospitalNo, $dates, $consultationType)
	{
		echo '<a id="target2"></a>
    <table class="table table-striped table-bordered table-hover dataTables-example">
        <thead>
            <tr>
                <th>Date</th>
                <th>Complaints</th>
                <th>Diagnosis</th>
                <th>Notes</th>
                <th>Plan/Medication</th>
            </tr>
        </thead>
        <tbody>';

		foreach ($dates as $date) {
			$effectDate = date("Y-m-d", strtotime($date));

			// Fetch notes
			$sql = buildNotesQuery($consultationType);
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':hospital_no', $hospitalNo, PDO::PARAM_STR);
			$stmt->bindParam(':effect_date', $effectDate, PDO::PARAM_STR);
			$stmt->execute();

			$notes = array(
				'C' => '',      // Complaints
				'D' => '',      // Diagnosis
				'plan' => '',   // Plan
				'other' => ''   // Other notes
			);

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$noteText = $row['notes'] . '<br>' . $row['prepared_by'] . '<br>';

				if (isset($notes[$row['notes_type']])) {
					$notes[$row['notes_type']] .= $noteText;
				} else {
					$notes['other'] .= $noteText;
				}
			}

			// Fetch services
			$services = fetchPatientServices($db, $hospitalNo, $effectDate);

			if (!empty(array_filter($notes)) || !empty(array_filter($services))) {
				displayConsultationRow($effectDate, $notes, $services);
			}
		}

		echo '</tbody></table>';
	}

	/**
	 * Fetch patient services (Lab, Radiology, Pharmacy)
	 */
	function fetchPatientServices($db, $hospitalNo, $effectDate)
	{
		$stmt = $db->prepare("SELECT item_services, serv_group 
                         FROM patient_ap_services 
                         WHERE (serv_group IN ('Laboratory', 'Radiology', 'Pharmacy'))
                         AND hospital_no = :hospital_no 
                         AND DATE(date_entry) = :effect_date 
                         ORDER BY date_entry");

		$stmt->bindParam(':hospital_no', $hospitalNo, PDO::PARAM_STR);
		$stmt->bindParam(':effect_date', $effectDate, PDO::PARAM_STR);
		$stmt->execute();

		$services = array(
			'Pharmacy' => array(),
			'Investigations' => array()
		);

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($row['serv_group'] === 'Pharmacy') {
				$services['Pharmacy'][] = $row['item_services'];
			} else {
				$services['Investigations'][] = $row['item_services'];
			}
		}

		return $services;
	}

	/**
	 * Display a single consultation row
	 */
	function displayConsultationRow($date, $notes, $services)
	{
		echo '<tr>
        <td>' . date('d', strtotime($date)) . ' /<br>' . date('M,y', strtotime($date)) . '</td>
        <td>' . $notes['C'] . '</td>
        <td>' . $notes['D'] . '</td>
        <td>' . $notes['other'] . '</td>
        <td>';

		if (!empty($notes['plan'])) {
			echo '<strong>Plan: </strong><br>' . $notes['plan'] . '<br>';
		}

		if (!empty($services['Pharmacy'])) {
			echo '<strong>Medications: </strong><br>' . implode(', ', $services['Pharmacy']) . '<br>';
		}

		if (!empty($services['Investigations'])) {
			echo '<br><strong>Investigations: </strong><br>' . implode(', ', $services['Investigations']);
		}

		echo '</td></tr>';
	}

	/**
	 * Display vital signs with color coding
	 */
	function displayVitalSigns($db, $hospitalNo, $startDate, $endDate)
	{
		if (empty($startDate) || empty($endDate)) {
			return;
		}

		$sql = "SELECT * FROM vital_sign 
            WHERE hospital_no = :hospital_no 
            AND DATE(date_ap) BETWEEN :start_date AND :end_date 
            ORDER BY date_ap DESC";

		$stmt = $db->prepare($sql);
		$stmt->bindParam(':hospital_no', $hospitalNo, PDO::PARAM_INT);
		$stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
		$stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
		$stmt->execute();

		if ($stmt->rowCount() === 0) {
			echo '<div class="alert alert-danger"><strong>No Vitals Records Available</strong></div>';
			return;
		}

		displayVitalSignsTable($stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	/**
	 * Get color coding for vital signs based on values
	 */
	function getVitalSignColor($type, $value)
	{
		if (empty($value)) return '';

		$ranges = array(
			'pulse' => array(
				'danger' => array(0, 60, 100, 999),
				'warning' => array(60, 65, 95, 100),
				'normal' => array(65, 95)
			),
			'bp_systolic' => array(
				'danger' => array(0, 90, 140, 999),
				'warning' => array(90, 100, 120, 140),
				'normal' => array(90, 120) // Normal updated to 90–120
			),
			'temp' => array(
				'danger' => array(0, 35, 38.5, 999),
				'warning' => array(35, 36, 37.8, 38.5),
				'normal' => array(36, 37.8)
			),
			'spo2' => array(
				'danger' => array(0, 90, 94, 999),
				'warning' => array(90, 94),
				'normal' => array(94, 100)
			)
		);


		if (!isset($ranges[$type])) return '';

		$range = $ranges[$type];
		$value = floatval($value);

		if ($value <= $range['danger'][0] || $value >= $range['danger'][3]) {
			return 'background-color: #ff4444;'; // Red for danger
		} elseif ($value <= $range['warning'][0] || $value >= $range['warning'][2]) {
			return 'background-color: #ffd700;'; // Yellow for warning
		} else {
			return 'background-color: #90EE90;'; // Light green for normal
		}
	}

	/**
	 * Display vital signs table with color coding
	 */
	function displayVitalSignsTable($vitalData)
	{
		echo '<table class="table table-bordered table-hover dataTables-example">
        <thead>
            <tr>
                <th>Date</th>
                <th>Vitals</th>
                <th>Additional Measurements</th>
                <th>Comments</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>';

		foreach ($vitalData as $vital) {
			// Extract systolic BP for color coding
			$bpParts = explode('/', isset($vital['bp']) ? $vital['bp'] : '');
			$systolic = isset($bpParts[0]) ? trim($bpParts[0]) : '';

			echo '<tr>
            <td>' . date('d M, Y', strtotime($vital['date_ap'])) . '</td>
            <td>
                <div style="padding: 5px;">
                    <div style="' . getVitalSignColor('pulse', isset($vital['pulse_read']) ? $vital['pulse_read'] : '') . ' padding: 3px;">
                        <strong>Pulse:</strong> ' . (isset($vital['pulse_read']) ? $vital['pulse_read'] : 'N/A') . ' bpm
                    </div>
                    <div style="' . getVitalSignColor('bp_systolic', $systolic) . ' padding: 3px;">
                        <strong>BP:</strong> ' . (isset($vital['bp']) ? $vital['bp'] : 'N/A') . ' mmHg
                    </div>
                    <div style="' . getVitalSignColor('temp', isset($vital['temp']) ? $vital['temp'] : '') . ' padding: 3px;">
                        <strong>Temp:</strong> ' . (isset($vital['temp']) ? $vital['temp'] : 'N/A') . ' °C
                    </div>
                    <div style="padding: 3px;">
                        <strong>Resp. Rate:</strong> ' . (isset($vital['resp_rate']) ? $vital['resp_rate'] : 'N/A') . ' bpm
                    </div>
                    <div style="' . getVitalSignColor('spo2', isset($vital['spo2']) ? $vital['spo2'] : '') . ' padding: 3px;">
                        <strong>SPO2:</strong> ' . (isset($vital['spo2']) ? $vital['spo2'] : 'N/A') . ' %
                    </div>
					<div style="padding: 3px;">
                        <strong>Height:</strong> ' . (isset($vital['height']) ? $vital['height'] : 'N/A') . ' m<br>
                    </div>
					<div style="padding: 3px;">
                        <strong>Weight:</strong> ' . (isset($vital['weight']) ? $vital['weight'] : 'N/A') . ' kg<br>
                    </div>
                    <div style="padding: 3px;">
                        <strong>BMI:</strong> ' . (isset($vital['bmi']) ? $vital['bmi'] : 'N/A') . '
                    </div>
                </div>
            </td>
            <td>
                <strong>MUAC:</strong> ' . (isset($vital['muac_read']) ? $vital['muac_read'] : 'N/A') . ' cm<br>
                <strong>FBS:</strong> ' . (isset($vital['fbs']) ? $vital['fbs'] : 'N/A') . ' mg/dL<br>
                <strong>RBS:</strong> ' . (isset($vital['rbs']) ? $vital['rbs'] : 'N/A') . ' mg/dL<br>
                <strong>PPBS:</strong> ' . (isset($vital['ppbs']) ? $vital['ppbs'] : 'N/A') . ' mg/dL<br>
                <strong>GTT:</strong> ' . (isset($vital['GTT']) ? $vital['GTT'] : 'N/A') . '<br>
                <strong>FHR:</strong> ' . (isset($vital['FHR']) ? $vital['FHR'] : 'N/A') . '<br>
                <strong>Others:</strong> ' . (isset($vital['Others']) ? $vital['Others'] : 'N/A') . '
            </td>
            <td>' . (isset($vital['comments']) ? $vital['comments'] : 'N/A') . '</td>
            <td>' . (isset($vital['prepared_by']) ? $vital['prepared_by'] : 'N/A') . '</td>
        </tr>';
		}

		echo '</tbody></table>';
	}
	?>
 <?php
	if (isset($_GET["encounters_form"])) { ?>

 	<form action="" method="get" class="from form-inline">
 		<label for="encounters_form_c_type">Note Type</label>
 		<select name="encounters_form_c_type" id="encounters_form_c_type" class="form-control" required>
 			<option value="ALL">ALL NOTES</option>
 			<option value="COMPLAINT">COMPLAINT</option>
 			<option value="DIAGNOSIS">DIAGNOSIS</option>
 			<option value="VITALS">VITALS</option>

 		</select>

 		<label for="encounters_form_start_date">Start Date</label>
 		<input type="date" class="form-control" id="encounters_form_start_date" name="encounters_form_start_date" required>

 		<label for="encounters_form_end_date">End Date</label>
 		<input type="date" class="form-control" id="encounters_form_end_date" name="encounters_form_start_date" required>

 		<button type="button" id="encounters_form_submit_btn" class="btn btn-primary" data-id="<?php echo $_GET["hos_no"] ?>">Fetch Records</button>
 	</form>
 <?php } ?>

 <?php
	if (isset($_GET["lab_res_modal_form"])) { ?>

 	<form action="" method="get" class="from form-inline">
 		<label for="lab_res_modal_form_c_type">Department</label>
 		<select name="lab_res_modal_form_c_type" id="lab_res_modal_form_c_type" class="form-control" required>
 			<option value="ALL">ALL</option>
 			<option value="RADIOLOGY">RADIOLOGY</option>
 			<option value="LAB">LAB</option>
 		</select>

 		<label for="lab_res_modal_form_start_date">Start Date</label>
 		<input type="date" class="form-control" id="lab_res_modal_form_start_date" name="lab_res_modal_form_start_date" required>

 		<label for="lab_res_modal_form_end_date">End Date</label>
 		<input type="date" class="form-control" id="lab_res_modal_form_end_date" name="lab_res_modal_form_start_date" required>

 		<button type="button" id="lab_res_modal_form_submit_btn" class="btn btn-primary" data-id="<?php echo $_GET["hos_no"] ?>">Fetch Records</button>
 	</form>
 <?php } ?>
 <?php
	if (isset($_POST["lab_res_modal"])) {
		// Retrieve POST data
		$hos_no = $_POST["lab_res_modal"];
		$c_type = $_POST["lab_res_modal_form_c_type"];
		$start_date = $_POST["lab_res_modal_form_start_date"];
		$end_date = $_POST["lab_res_modal_form_end_date"];
		if (!empty($c_type) && !empty($start_date) && !empty($end_date)) {


			// Define the base query
			$ss = "SELECT lab_result.* 
			FROM lab_result 
			LEFT JOIN lab_manage 
			ON lab_manage.labrequest_no = lab_result.lab_no 
			WHERE lab_manage.patient = :hospital_no 
			AND lab_result.result_date BETWEEN :start_date AND :end_date";

			// Modify the query based on the `c_type`
			if ($c_type == 'RADIOLOGY') {
				$ss .= " AND lab_manage.section = :section ORDER BY sn DESC";
				$stmt = $db->prepare($ss);
				$stmt->bindParam(':section', $section = 'Radiology', PDO::PARAM_STR);
			} elseif ($c_type == 'LAB') {
				$ss .= " AND lab_manage.section = :section ORDER BY sn DESC";
				$stmt = $db->prepare($ss);
				$stmt->bindParam(':section', $section = 'Laboratory', PDO::PARAM_STR);
			} else {
				$ss .= " ORDER BY sn DESC";
				$stmt = $db->prepare($ss);
			}

			// Bind other parameters
			$stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
			$stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
			$stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);

			// Execute the query
			$stmt->execute();

			$reults__ = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($stmt->rowCount() > 0) { ?>

 			<a id="target2"></a>
 			<table class="table table-striped table-bordered table-hover dataTables-example">
 				<thead>
 					<tr>
 						<th>Details</th>
 						<th>Result</th>
 					</tr>
 				</thead>
 				<tbody>
 					<?php foreach ($reults__ as $re): ?>
 						<tr>
 							<td>
 								<h3>Investigation Name:</h3><b><?php echo $re['test_name'] ?></b>
 								<p><b>Result Date : </b> <?php echo date("Y-m-d", strtotime($re["result_date"])) ?></p>
 								<p><b>Done By: </b><?php echo $re['lab_sci_name'] . " (" . $re['lab_sci_speciality'] . ")" ?></p>
 								<p><b>Reported By: </b><?php echo $re['entered_by'] ?></p>
 							</td>
 							<td>
 								<?php echo ($re['field_value']) ?>
 							</td>
 						</tr>
 					<?php endforeach ?>
 				</tbody>
 			</table>

 		<?php } else { ?>

 			<div class="alert alert-danger"><strong> Records Not Available </strong></div>

 <?php
			}
		}
	}
	?>


 <?php

	if (isset($_POST["search_detials"])) {

		$search_detials = trim($_POST["search_detials"]);
		$search_detials_split = $search_detials;
		$search_detials = "%$search_detials%";
		$length_search = strlen($search_detials);

		$word_count = str_word_count($search_detials);
		$partt = explode(" ", $search_detials_split);
		if (count($partt) == 1) {














			if (preg_match("/[a-z]/i", $search_detials)) {

				$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
							FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
								WHERE surname like :surname or fname like :fname or oname like :oname");
				$query->bindParam(':surname', $search_detials);
				$query->bindParam(':fname', $search_detials);
				$query->bindParam(':oname', $search_detials);
			} elseif ($length_search >= 3  && $length_search <= 8) {
				$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname,e.phone , e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE e.hospital_no like :hospital_no or e.old_hospital_no like :old_hospital_no");
				$query->bindParam(':hospital_no', $search_detials);
				$query->bindParam(':old_hospital_no', $search_detials);
			} elseif ($length_search >= 10) {
				$query = $db->prepare("SELECT e.hospital_no, e.surname,e.fname,e.phone , e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no WHERE e.phone like :phone");
				$query->bindParam(':phone', $search_detials);
			} else {
				echo 'Search Text Specified Not Available!';
				exit;
			}

			$query->execute();
		} else if (count($partt) == 2) {

			$partt = explode(" ", $search_detials_split);
			$name1 = $partt[0];
			$name2 = $partt[1];
			$name1 =  "$name1%";
			$name2 =  "$name2%";

			$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE 
							(e.surname LIKE :name1 AND e.fname LIKE :name2)  OR
							(e.surname LIKE :name3 AND e.fname LIKE :name4)  OR
							(e.oname LIKE :name5 AND e.surname LIKE :name6) OR
							(e.oname LIKE :name7 AND e.surname LIKE :name8) OR
							(e.fname LIKE :name9 AND e.oname LIKE :name10) OR 
							(e.fname LIKE :name11 AND e.oname LIKE :name12) OR
							(e.fname LIKE :name13 AND e.oname LIKE :name14) 
							
							");

			$query->bindParam(':name1', $name1);
			$query->bindParam(':name2', $name2);
			$query->bindParam(':name3', $name2);
			$query->bindParam(':name4', $name1);
			$query->bindParam(':name5', $name1);
			$query->bindParam(':name6', $name2);
			$query->bindParam(':name7', $name2);
			$query->bindParam(':name8', $name1);
			$query->bindParam(':name9', $name1);
			$query->bindParam(':name10', $name2);
			$query->bindParam(':name11', $name2);
			$query->bindParam(':name12', $name1);
			$query->bindParam(':name13', $search_detials_split);
			$query->bindParam(':name14', $search_detials_split);
			$query->execute();
		} else if (count($partt) == 3) {

			$partt = explode(" ", $search_detials_split);
			$name1 = $partt[0];
			$name2 = $partt[1];
			$name3 = $partt[2];



			$name1 =  "$name1%";
			$name2 =  "$name2%";
			$name3 =  "$name3%";

			$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
						FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE 
							(e.fname LIKE :name2 AND e.surname LIKE :name1 AND e.oname LIKE :name3)  OR
							(e.fname LIKE :name4 AND e.surname LIKE :name5 AND e.oname LIKE :name6)  OR
							(e.fname LIKE :name7 AND e.surname LIKE :name8 AND e.oname LIKE :name9)  OR
							(e.fname LIKE :name10 AND e.surname LIKE :name11 AND e.oname LIKE :name12)  OR
							(e.fname LIKE :name13 AND e.surname LIKE :name14 AND e.oname LIKE :name15)  OR
							(e.fname LIKE :name16 AND e.surname LIKE :name17 AND e.oname LIKE :name18)  OR

							(e.surname LIKE :name22 AND e.oname LIKE :name23 ) OR
							(e.surname LIKE :name24 AND e.fname LIKE :name25 ) OR
							(e.fname LIKE :name26 AND e.surname LIKE :name27 ) OR
							(e.fname LIKE :name28 AND e.oname LIKE :name29 ) OR
							(e.oname LIKE :name30 AND e.fname LIKE :name31 ) OR
							(e.oname LIKE :name32 AND e.surname LIKE :name33 ) 
							
							");

			// surname fname oname : 1 2 3
			$query->bindParam(':name1', $name2);
			$query->bindParam(':name2', $name1);
			$query->bindParam(':name3', $name3);

			// fname surname oname 
			$query->bindParam(':name4', $name1);
			$query->bindParam(':name5', $name2);
			$query->bindParam(':name6', $name3);
			//
			//  oname fname surname
			$query->bindParam(':name7', $name2);
			$query->bindParam(':name8', $name3);
			$query->bindParam(':name9', $name1);

			//  fname  oname surname
			$query->bindParam(':name10', $name1);
			$query->bindParam(':name11', $name3);
			$query->bindParam(':name12', $name2);

			// surname oname fname 
			$query->bindParam(':name13', $name3);
			$query->bindParam(':name14', $name1);
			$query->bindParam(':name15', $name2);

			//  oname  surname fname
			$query->bindParam(':name16', $name3);
			$query->bindParam(':name17', $name2);
			$query->bindParam(':name18', $name1);

			$name22 = $name2 . ' ' . $name1;
			$name23 = $name3;
			$name24 = $name2 . ' ' . $name3;
			$name25 = $name1;
			$name26 = $name1 . ' ' . $name2;
			$name27 = $name3;
			$name28 = $name1 . ' ' . $name3;
			$name29 = $name2;
			$name30 = $name3 . ' ' . $name2;
			$name31 = $name2;
			$name32 = $name3 . ' ' . $name1;
			$name33 = $name1;

			$query->bindParam(':name22', $name21); // sf :21
			$query->bindParam(':name23', $name22); // o : 3
			$query->bindParam(':name24', $name23); // so :23
			$query->bindParam(':name25', $name24); // f : 1
			$query->bindParam(':name26', $name25); // fs :12
			$query->bindParam(':name27', $name26); // o  : 3
			$query->bindParam(':name28', $name27); // fo 13
			$query->bindParam(':name29', $name28); // s : 2
			$query->bindParam(':name30', $name29); // os : 32
			$query->bindParam(':name31', $name30); // f : 1
			$query->bindParam(':name32', $name31); // os :32
			$query->bindParam(':name33', $name32); // o  : 1
			$query->execute();
		} else {

			$partt = explode(" ", $search_detials_split);
			$query = $db->prepare("SELECT e.hospital_no, e.surname,fname, e.phone , e.oname, i.insurance_name  
					FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
						WHERE e.surname like :surname and e.fname like :fname");
			$query->bindParam(':surname', $partt[0]);
			$query->bindParam(':fname', $partt[1]);
			$query->bindParam(':fname', $partt[1]);
			$query->execute();
		}





		if ($query->rowCount() > 0) {

			if ($query->rowCount() == 1) {
				header('Content-Type: application/json');

				$roww = $query->fetch(PDO::FETCH_ASSOC);
				echo json_encode(["status" => 200, "redirect" => true, "hosp_no" => $roww['hospital_no']]);
				exit;
			}

	?>

 		<table class="table table-striped" id="search_table">
 			<thead>
 				<tr>
 					<th data-toggle="true">Hospital No</th>
 					<th data-toggle="true">Surname</th>
 					<th data-toggle="true">First Name</th>
 					<th data-toggle="true">Other Name</th>
 					<th data-toggle="true">Phone</th>
 					<th data-toggle="true">Insurance</th>
 					<th data-toggle="true">Action</th>
 				</tr>
 			</thead>
 			<tbody>
 				<?php
					$n = 1;
					while ($roww = $query->fetch(PDO::FETCH_ASSOC)) {
					?>
 					<tr>
 						<td><?php echo $hospital_no = $roww['hospital_no']; ?></td>
 						<td><?php echo $roww['surname']; ?></td>
 						<td><?php echo $roww['fname']; ?></td>
 						<td><?php echo $roww['oname']; ?></td>
 						<td><?php echo $roww['phone']; ?></td>
 						<td><?php echo $roww['insurance_name']; ?></td>
 						<td>
 							<a href="index.php?presc&hos_no=<?php echo $hospital_no; ?>" class="btn btn-primary btn-xs"><i class="fa fa-search-plus"></i> &nbsp;Go</a>
 						</td>

 					</tr>
 				<?php } ?>
 			</tbody>
 		</table>

 <?php   } else {

			echo '<div class="alert alert-danger">
    <h3>PATIENT NOT FOUND. PRESS THE ESC KEY OR CLICK ANYWHERE TO CLOSE THE SCREEN AND TRY AGAIN.</h3>
</div>';
			exit;
		}
		exit;
	}

	if (isset($_POST['pharm_form_body'])) {

		try {
			$hospital_detatils = "SELECT * FROM hospital_details LIMIT 1";
			$hospital_detatils = $db->prepare($hospital_detatils);
			$hospital_detatils->execute();
			$hospital_detatils = $hospital_detatils->fetch(PDO::FETCH_ASSOC);

			$the_text = $_POST['pharm_form_body'];

			$hospital_detatils = '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr>
							<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
							<td width="50%" align="right">
								<div style="font-size:18px; font:Verdana, Geneva, sans-serif""><strong>' . $hospital_detatils['name'] . '</strong></div> <br><div style=" font-size:14px">' . $hospital_detatils['address'] . ' <br><br> ' . $hospital_detatils['phones'] . ' </div>
							</td>
						</tr>
					</table>
					<hr>';

			$title = '<h4>' . ucfirst(str_replace('_', ' ', $_POST['pharmacy_form'])) . '</h4>';

			$the_text = $hospital_detatils . $title . $the_text;


			// Prepare the SQL statement
			$stmt = $db->prepare("
				INSERT INTO notes_services (
					hospital_no,
					app_no,
					notes,
					service,
					template_name,
					isCompleted,
					created_at,
					created_by
				) VALUES (
					:hospital_no,
					:app_no,
					:notes,
					:service,
					:template_name,
					1,
					NOW(),
					:created_by
				)
			");

			// Bind the form data to the statement
			$stmt->bindParam(':hospital_no', $_POST['hospital_no'], PDO::PARAM_STR);
			$stmt->bindParam(':app_no', $_POST['appt_no'], PDO::PARAM_STR);
			$stmt->bindParam(':notes', $the_text, PDO::PARAM_STR);
			$stmt->bindParam(':service', $_POST['pharmacy_form'], PDO::PARAM_STR);
			$stmt->bindParam(':template_name', $_POST['pharmacy_form'], PDO::PARAM_STR);
			$stmt->bindParam(':created_by', $_SESSION['fullname'], PDO::PARAM_INT);

			// Execute the statement
			if ($stmt->execute()) {
				echo $the_text;
			} else {
				echo 'Error: Could not insert the data.';
			}
		} catch (PDOException $e) {
			header('HTTP/1.1 500 Internal Server Error');
			echo 'Error: ' . $e->getMessage();
			error_log('Error: ' . $e->getMessage());
			exit;
		}
	}

	if (isset($_POST['clinical_form_del_id'])) {
		$id = $_POST['clinical_form_del_id'];

		try {
			$stmt = $db->prepare('DELETE FROM notes_services WHERE id = :id');
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			if ($stmt->execute()) {
				echo 'success';
			} else {
				echo 'Failed to delete record.';
			}
		} catch (Exception $e) {
			header('HTTP/1.1 500 Internal Server Error');
			echo 'Error: ' . $e->getMessage();
			error_log('Error: ' . $e->getMessage());
			exit;
		}
	}

	if (isset($_POST['clinical_form_print_id'])) {
		$id = $_POST['clinical_form_print_id'];

		try {
			// Fetch the clinical form note
			$stmt = $db->prepare('SELECT notes FROM notes_services WHERE id = :id');
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($result) {
				// Return the notes 
				echo $result['notes'];
			} else {
				echo 'No data found.';
			}
		} catch (Exception $e) {
			header('HTTP/1.1 500 Internal Server Error');
			echo 'Error: ' . $e->getMessage();
			error_log('Error: ' . $e->getMessage());
			exit;
		}
	}
	?>


 <script>
 	$(document).ready(function() {
 		$('#div2').hide('fast');
 		$('#div1').hide('fast');

 		$('#drug_replace').click(function() {
 			$('#div2').hide('fast');
 			$('#div1').show('fast');
 		});
 		$('#drug_new').click(function() {
 			$('#div1').hide('fast');
 			$('#div2').show('fast');
 		});
 	});
 </script>
 <?php
	// Fetch the drug_sn and hos_no from the GET request
	$drug_sn_rem = $_GET['drug_sn_rem_hist'];
	$hos_no_rem = $_GET['hos_no_rem_hist'];

	if (isset($_GET['drug_sn_rem_hist']) && isset($_GET['hos_no_rem_hist'])) {

		// Get the current date
		$current_date = date('Y-m-d');

		// Prepare the SQL query to select reminders based on the drug_sn and hos_no
		$stmt = $db->prepare("
    SELECT pharm_drug_reminder.*, 
           stock_table.product_name, 
           enrollee.surname, 
           enrollee.fname, 
           enrollee.oname, 
           CASE WHEN pharm_drug_reminder.reminder_date < :current_date1 THEN true ELSE false END AS overdue
    FROM pharm_drug_reminder
    LEFT JOIN stock_table ON stock_table.sn = pharm_drug_reminder.drug_sn
    LEFT JOIN enrollee ON enrollee.hospital_no = pharm_drug_reminder.hos_no
    WHERE pharm_drug_reminder.drug_sn = :drug_sn_rem
          AND pharm_drug_reminder.hos_no = :hos_no_rem
          
");

		$stmt->bindParam(':drug_sn_rem', $drug_sn_rem);
		$stmt->bindParam(':hos_no_rem', $hos_no_rem);
		$stmt->bindParam(':current_date1', $current_date);
		// $stmt->bindParam(':current_date2', $current_date);
		$stmt->execute();

		$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (count($reminders) > 0) {
			// Start rendering the reminders table
			echo '<table class="table table-bordered">';
			echo '<thead>
        <tr>
            <th>Drug</th>
            <th>Patient</th>
            <th>Due Date</th>
            <th>Action</th>
        </tr>
      </thead>';
			echo '<tbody>';

			foreach ($reminders as $reminder) {
				echo '<tr>';
				echo '<td>' . $reminder['product_name'] . '</td>';
				echo '<td>' . $reminder['surname'] . ' ' . $reminder['fname'] . ' ' . $reminder['oname'] . '</td>';
				echo '<td>' . date('d M, Y', strtotime($reminder['reminder_date'])) .
					($reminder['overdue'] ? ' <span class="text-danger">Overdue</span>' : '') . '</td>';
				if ($reminder['status'] == 'enabled') {
					echo '<td>
						<a href="?dismiss_rem=' . $reminder['sn'] . '" class="btn btn-xs btn-success">Deactivate</a><br><br>
					</td>';
				} else {
					echo '<td>
						<a href="#" class="btn btn-xs btn-secondary disabled">Disabled</a><br><br>
					</td>';
				}
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';
		} else {
			echo "<p>No previous reminders for this item</p>";
		}
	}
	?>