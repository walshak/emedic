<?php

if ((isset($_POST['hospital_no']) and $_POST['hospital_no'] != '') or (isset($_GET['hosp_no']) and $_GET['hosp_no'] != '')) {

	if ($hospital_no != '') {
		$hospital_number = $hospital_no;
	} elseif (isset($_GET['hosp_no'])) {
		$hospital_number = $_GET['hosp_no'];
	} else {
		$hospital_number = $_POST['hospital_no'];
	}

	////echo $hospital_number;


	$stmt12 = $db->query("SELECT e.*,i.insurance_name,i.interest,i.insurance_type,g.guardian_Name,g.guardian_phone 
		FROM enrollee as e 
		INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no
		INNER JOIN guardian_tbl as g ON g.patient_id = e.hospital_no
		WHERE e.hospital_no='$hospital_number' and status='active'");
	if ($stmt12->rowCount() > 0) {
		$rwxv = $stmt12->fetch(PDO::FETCH_ASSOC);

?>


		<table class="table table-striped table-bordered" style="font-size: 15px;">
			<tr style="">
				<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Patient Detail: </td>
				<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Insurance: </td>
				<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Phone/Email: </td>
				<td style="font:bold 14px 'Arial'; background: #666; color: #FFF;" width="20%">Next of Kin Name: </td>
				<td width="20%" rowspan="4" class='text-right'>
					<br>
					Patient Document(s)
					<input type="button" name="" value="Upload & View Documents" data-target="#modal" id="<?php echo $rwxv['hospital_no']; ?>" class="btn btn-success doc_" />
					<?php
					if (isset($rwxv['old_hospital_no'])) {
						if ($rwxv['old_hospital_no'] != '' && $rwxv['old_hospital_no'] != null && $rwxv['old_hospital_no'] != 'NULL') {

							$category = $_SESSION['section'];
							$notes_type = $category == 'Laboratory' ? 'Lab' : 'Scan';


							echo '
							<hr/>
							<buttton class="btn btn btn-danger" id="vista_notes_modal_btn" 
								arial-data = "' . $notes_type . '"
								arial-hospitalnno = "' . $rwxv['old_hospital_no'] . '"
								>OLD EMR LAB</buttton>
						';
						} else {
							//echo 'empty';
						}
					} else {
						//echo 'not found';
					}


					?>
				</td>

			</tr>
			<tr>

				<td><?php echo $rwxv['hospital_no']; ?> / <?php echo $rwxv['surname'] . ', ' . $rwxv['fname'] . ' ' . $rwxv['oname'];
															$name = trim($rwxv['surname'] . ' ' . $rwxv['fname'] . ' ' . $rwxv['oname']); ?></td>
				<td><?php echo $rwxv['insurance_name']; ?> / <?php echo $rwxv['insurance_type']; ?></td>
				<td><?php echo $rwxv['phone']; ?> / <?php echo $rwxv['email']; ?></td>
				<td><?php echo $rwxv['guardian_Name']; ?></td>
				<td></td>

			</tr>
			<tr style="background: #666; color: #FFF;">
				<td style="font:bold 14px 'Arial';">Age: </td>
				<td style="font:bold 14px 'Arial';">Gender: </td>
				<td style="font:bold 14px 'Arial';">Blood Group: </td>
				<td style="font:bold 14px 'Arial';">Next of Kin Phone No: </td>
			</tr>
			<tr>
				<td><?php echo $rwxv['age']; ?></td>
				<td><?php echo $rwxv['gender']; ?></td>
				<td><?php echo $rwxv['blood_g']; ?></td>
				<td><?php echo $rwxv['guardian_phone']; ?></td>

			</tr>
		</table>

<?php
	} else {
	}
}


?>