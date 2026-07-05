<br>
<?php if ($status == 'medical') { ?>

	<table class="table table-striped table-bordered">
		<tr>
			<th width="10%">Date</th>
			<th width="22%">Complaints</th>
			<th width="22%">Diagnosis</th>
			<th width="22%">Notes</th>
			<th width="22%">Plan/Medication</th>
		</tr>

		<?php
		date_default_timezone_set('Africa/Lagos');

		$date1 = new DateTime($end);
		$date2 = new DateTime($start);
		$diff = $date2->diff($date1);
		$day = (int)$diff->format('%a');

		if ($day < 360) {
			// 1. Fetch all notes in one query
			$stmt = $db->prepare("SELECT DATE(date_entry) AS entry_date, notes_type, notes, prepared_by FROM notes WHERE hospital_no = :hos_no AND date(date_entry) BETWEEN :start AND :end ORDER BY date_entry");
			$stmt->execute([':hos_no' => $hos_no, ':start' => $start, ':end' => $end]);
			$notesData = [];

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$entryDate = $row['entry_date'];
				if (!isset($notesData[$entryDate])) {
					$notesData[$entryDate] = ['C' => '', 'D' => '', 'plan' => '', 'Notes' => ''];
				}
				$noteContent = $row['notes'] . '<br>' . $row['prepared_by'] . '<br>';
				$type = $row['notes_type'];
				$notesData[$entryDate][$type === 'C' ? 'C' : ($type === 'D' ? 'D' : ($type === 'plan' ? 'plan' : 'Notes'))] .= $noteContent;
			}

			// 2. Fetch all services in one query
			$stmt2 = $db->prepare("SELECT DATE(date_entry) AS entry_date, item_services, serv_group FROM patient_ap_services WHERE (serv_group IN ('Laboratory', 'Radiology', 'Pharmacy')) AND hospital_no = :hos_no AND date(date_entry) BETWEEN :start AND :end ORDER BY date_entry");
			$stmt2->execute([':hos_no' => $hos_no, ':start' => $start, ':end' => $end]);
			$serviceData = [];

			while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
				$entryDate = $row['entry_date'];
				if (!isset($serviceData[$entryDate])) {
					$serviceData[$entryDate] = ['Pharmacy' => '', 'Lab' => ''];
				}

				if ($row['serv_group'] === 'Pharmacy') {
					$serviceData[$entryDate]['Pharmacy'] .= $row['item_services'] . ', ';
				} else {
					$serviceData[$entryDate]['Lab'] .= $row['item_services'] . ', ';
				}
			}

			// 3. Combine and output only dates with any data
			$allDates = array_unique(array_merge(array_keys($notesData), array_keys($serviceData)));
			sort($allDates);

			foreach ($allDates as $effect_date) {
				$C = $D = $plan = $Notes = $Pharmacy = $lab = '';

				if (isset($notesData[$effect_date])) {
					$C = $notesData[$effect_date]['C'];
					$D = $notesData[$effect_date]['D'];
					$plan = $notesData[$effect_date]['plan'];
					$Notes = $notesData[$effect_date]['Notes'];
				}

				if (isset($serviceData[$effect_date])) {
					$Pharmacy = rtrim($serviceData[$effect_date]['Pharmacy'], ', ');
					$lab = rtrim($serviceData[$effect_date]['Lab'], ', ');
				}

				echo '<tr>';
				echo '<td>' . date('d', strtotime($effect_date)) . ' /<br>' . date('M,y', strtotime($effect_date)) . '</td>';
				echo '<td>' . $C . '</td>';
				echo '<td>' . $D . '</td>';
				echo '<td>' . $Notes . '</td>';
				echo '<td>';
				if ($plan) echo '<strong>Plan: </strong><br>' . $plan . '<br>';
				if ($Pharmacy) echo '<strong>Medications: </strong><br>' . $Pharmacy . '<br>';
				if ($lab) echo '<br><strong>Investigations: </strong><br>' . $lab;
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '</table>';
			echo '<div class="alert alert-danger"><strong>Date Range Should Not Exceed 360 days</strong></div>';
		}
		?>
	</table>


<?php }



if ($status == 'logs') {
	$t = 0;
	$n = 1;

	if (!empty($hos_no)) {
		// First query: Patient remarks log
		$stmt = $db->prepare("SELECT p.hospital_no, p.service_list, p.total_amount, p.remark, p.date_time, p.staff_name, a.fullname 
                               FROM patients_remarks_tbl AS p 
                               LEFT JOIN admin_users AS a ON a.EmployeeCode = p.bill_to_who
                               WHERE p.hospital_no = :hospital_no
                               ORDER BY p.date_time DESC");
		$stmt->execute([':hospital_no' => $hos_no]);

		if ($stmt->rowCount() > 0) {
			echo '<h2 style="color:brown;">Patient Transactions log</h2>';
			echo '<table class="table table-striped table-bordered table-hover dataTables-example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service List</th>
                            <th>Total Amount</th>
                            <th>Remark</th>
                            <th>Staff Name</th>
                            <th>Bill To (If Any)</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>';

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$t += $row['total_amount'];
				echo '<tr>
                        <td>' . ($n++) . '</td>
                        <td>' . ($row['service_list']) . '</td>
                        <td>' . htmlspecialchars(number_format($row['total_amount'], 2)) . '</td>
                        <td>' . ($row['remark']) . '</td>
                        <td>' . htmlspecialchars($row['staff_name']) . '</td>
                        <td>' . (!empty($row['fullname']) ? htmlspecialchars($row['fullname']) : '<span style="color: gray;">N/A</span>') . '</td>
                        <td>' . date('d M Y h:i A', strtotime($row['date_time'])) . '</td>
                    </tr>';
			}

			echo '</tbody></table>';
			echo '<h3>Total Amount: ' . number_format($t, 2) . '</h3>';
		}

		echo '<hr>';

		// Second query: Patient staff logs
		$start = $_POST["start"];
		$end = $_POST["end"];

		$stmt2 = $db->prepare("SELECT * FROM patient_staff_logs WHERE patient_id = :hos_no AND date(date_and_time) BETWEEN :start AND :end ORDER BY date_and_time");
		$stmt2->execute([':hos_no' => $hos_no, ':start' => $start, ':end' => $end]);

		if ($stmt2->rowCount() > 0) {
			echo '<table class="table table-striped table-bordered table-hover dataTables-example">
                    <thead>
                        <tr>
                            <th width="10%">Date</th>
                            <th width="35%">Description</th>
                            <th width="20%">Action</th>
                            <th width="15%">Staff Name</th>
                        </tr>
                    </thead>
                    <tbody>';

			while ($rowx = $stmt2->fetch(PDO::FETCH_ASSOC)) {
				echo '<tr>
                        <td>' . date("d M Y H:i", strtotime($rowx['date_and_time'])) . '</td>
                        <td>' . ($rowx['descriptions']) . '</td>
                        <td>' . htmlspecialchars($rowx['action']) . '</td>
                        <td>' . htmlspecialchars($rowx['staff_name']) . '</td>
                    </tr>';
			}

			echo '</tbody></table>';
		} else {
			echo '<strong>No Reports to display</strong>';
		}
	}
}


?>

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
</script>