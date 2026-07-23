<?php


$query_rstSelect = $db->query("SELECT EmployeeCode FROM hremp ORDER BY sn DESC LIMIT 1");
if ($query_rstSelect->rowCount() == 0) {
	$EmployeeCode = sprintf('%003d', '1');
} else {
	$row_rstSelect = $query_rstSelect->fetch(PDO::FETCH_ASSOC);
	$EmployeeCode = 1 + (int)$row_rstSelect['EmployeeCode'];
	$EmployeeCode = sprintf('%003d', $EmployeeCode);
}
include('_uploadDocumentModal.php'); //include the file that contains the generic modal for uploading other staff docs

///echo staff_p ; 




if (isset($_POST["save_employeee"])) {
	if (isset($_POST["old_emr"]) && !empty($_POST["old_emr"])) {
		$updateSQL = "UPDATE enrollee SET discount_set = 0 WHERE hospital_no = :hospital_no";
		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':hospital_no', $_POST['old_emr']);
		$stmt->execute();
	}

	if (isset($_POST["hospital_no"]) && !empty($_POST["hospital_no"])) {
		$updateSQL = "UPDATE enrollee SET discount_set = 1 WHERE hospital_no = :hospital_no";
		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':hospital_no', $_POST['hospital_no']);
		$stmt->execute();
	}


	if (isset($_POST["Edit_emp"]) and !empty($_POST["Edit_emp"])) {
		$ECode = $_POST['EmployeeCode'];

		// 1. Get the old data before updating
		$getOldSQL = "SELECT * FROM hremp WHERE EmployeeCode = :EmployeeCode";
		$getStmt = $db->prepare($getOldSQL);
		$getStmt->bindParam(':EmployeeCode', $ECode);
		$getStmt->execute();
		$oldData = $getStmt->fetch(PDO::FETCH_ASSOC);

		// 2. Perform the update
		$updateSQL = "UPDATE hremp SET 
			JoiningDate = :JoiningDate, 
			Department = :Department, 
			Designation = :Designation, 
			Qualification = :Qualification, 
			TotalExperience = :TotalExperience, 
			Cadre = :Cadre, 
			Title = :Title, 
			FirstName = :FirstName, 
			MiddleName = :MiddleName, 
			LastName = :LastName, 
			phone = :Phone, 
			DateofBirth = :DateofBirth, 
			Gender = :Gender, 
			blood_grp = :blood_grp, 
			MaritalStatus = :MaritalStatus, 
			no_of_dependents = :no_of_dependents, 
			Religion = :Religion, 
			Tribe = :Tribe, 
			PresentAddress = :PresentAddress, 
			PermanentAddress = :PermanentAddress, 
			StateLGA = :StateLGA, 
			Nationality = :Nationality, 
			EmailAddress = :EmailAddress, 
			BankName = :BankName, 
			Branch = :Branch, 
			BankAddress = :BankAddress, 
			AccountNo = :AccountNo, 
			rFullName = :rFullName, 
			rPhone = :rPhone, 
			rAddress = :rAddress, 
			kFullName = :kFullName, 
			kPhone = :kPhone, 
			kAddress = :kAddress,
			emr_no = :emr_no,
			ID_card_no = :ID_card_no
			WHERE EmployeeCode = :EmployeeCode";

		$stmt = $db->prepare($updateSQL);
		$stmt->bindParam(':JoiningDate', $_POST['JoiningDate']);
		$stmt->bindParam(':Department', $_POST['Department']);
		$stmt->bindParam(':Designation', $_POST['Designation']);
		$stmt->bindParam(':Qualification', $_POST['Qualification']);
		$stmt->bindParam(':TotalExperience', $_POST['TotalExperience']);
		$stmt->bindParam(':Cadre', $_POST['Cadre']);
		$stmt->bindParam(':Title', $_POST['Title']);
		$stmt->bindParam(':FirstName', $_POST['FirstName']);
		$stmt->bindParam(':MiddleName', $_POST['MiddleName']);
		$stmt->bindParam(':LastName', $_POST['LastName']);
		$stmt->bindParam(':Phone', $_POST['Phone']);
		$stmt->bindParam(':DateofBirth', $_POST['dob']);
		$stmt->bindParam(':Gender', $_POST['Gender']);
		$stmt->bindParam(':blood_grp', $_POST['blood_group']);
		$stmt->bindParam(':MaritalStatus', $_POST['marital']);
		$stmt->bindParam(':no_of_dependents', $_POST['no_of_dependents']);
		$stmt->bindParam(':Religion', $_POST['religion']);
		$stmt->bindParam(':Tribe', $_POST['tribe']);
		$stmt->bindParam(':PresentAddress', $_POST['PresentAddress']);
		$stmt->bindParam(':PermanentAddress', $_POST['PermanentAddress']);
		$stmt->bindParam(':StateLGA', $state);
		$stmt->bindParam(':Nationality', $_POST['nationality']);
		$stmt->bindParam(':EmailAddress', $_POST['email']);
		$stmt->bindParam(':BankName', $_POST['bank']);
		$stmt->bindParam(':Branch', $_POST['Branch']);
		$stmt->bindParam(':BankAddress', $_POST['BankAddress']);
		$stmt->bindParam(':AccountNo', $_POST['Accountno']);
		$stmt->bindParam(':rFullName', $_POST['referee_fullname']);
		$stmt->bindParam(':rPhone', $_POST['referee_phone_no']);
		$stmt->bindParam(':rAddress', $_POST['referee_PermanentAddress']);
		$stmt->bindParam(':kFullName', $_POST['kin_fullname']);
		$stmt->bindParam(':kPhone', $_POST['kin_phone_no']);
		$stmt->bindParam(':kAddress', $_POST['kin_PermanentAddress']);
		$stmt->bindParam(':EmployeeCode', $ECode);
		$stmt->bindParam(':emr_no', $_POST['hospital_no']);
		$stmt->bindParam(':ID_card_no', $_POST['ID_card_no']);
		$stmt->execute();

		// 3. Compare old and new values
		$changes = [];
		foreach ($oldData as $field => $oldValue) {
			if (isset($_POST[$field])) {
				$newValue = $_POST[$field];
				if ($oldValue != $newValue) {
					$changes[] = "$field changed from '{$oldValue}' to '{$newValue}'";
				}
			}
		}

		// 4. If there are changes, log them
		if (!empty($changes)) {
			$description = implode("; ", $changes);
			$action_to_take = "Staff record update";
			$setdatetime = date('Y-m-d H:i:s');
			$staff_name = $_SESSION['fullname'];

			$logSQL = "INSERT INTO  patient_staff_logs 
					   (patient_id, descriptions, staff_name, action, date_and_time) 
					   VALUES (:patient_id, :descriptions, :staff_name, :action, :date_and_time)";
			$logStmt = $db->prepare($logSQL);
			$logStmt->bindParam(':patient_id', $ECode);
			$logStmt->bindParam(':descriptions', $description);
			$logStmt->bindParam(':staff_name', $staff_name);
			$logStmt->bindParam(':action', $action_to_take);
			$logStmt->bindParam(':date_and_time', $setdatetime);
			$logStmt->execute();
		}

		header("location:index.php?hr&ECode=$ECode&sv");
	} else {
		try {
			// Start the transaction
			$db->beginTransaction();

			// Determine state value
			if ($_POST['state_lga'] == 'others' && $_POST['other_state'] != '') {
				$state = $_POST['other_state'];
			} elseif ($_POST['state_lga'] == 'others' && $_POST['other_state'] == '') {
				$state = "Unknown";
			} else {
				$state = $_POST['state_lga'];
			}

			$setdate = date("Y-m-d");

			$no_of_dependents = isset($_POST['no_of_dependents']) && $_POST['no_of_dependents'] !== ''
				? $_POST['no_of_dependents']
				: 0;


			// Get last EmployeeCode
			$query_rstSelect = $db->query("SELECT EmployeeCode FROM hremp ORDER BY sn DESC LIMIT 1");

			if ($query_rstSelect->rowCount() == 0) {
				$EmployeeCode = '001';
			} else {
				$row_rstSelect = $query_rstSelect->fetch(PDO::FETCH_ASSOC);
				$lastCode = (int)$row_rstSelect['EmployeeCode'];
				$EmployeeCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
			}

			// Check for duplicate EmployeeCode (safety check)
			$stmt = $db->prepare("SELECT 1 FROM hremp WHERE EmployeeCode = :EmployeeCode LIMIT 1");
			$stmt->bindParam(':EmployeeCode', $EmployeeCode);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				throw new Exception("EmployeeCode already exists: " . $EmployeeCode);
			}

			// Prepare insert query
			$insertSQL = "INSERT INTO hremp (
        EmployeeCode, JoiningDate, Department, Designation, Qualification, TotalExperience, Cadre, Title, FirstName, 
        MiddleName, LastName, phone, DateofBirth, Gender, blood_grp, MaritalStatus, no_of_dependents, Religion, Tribe, 
        PresentAddress, PermanentAddress, StateLGA, Nationality, EmailAddress, BankName, Branch, BankAddress, AccountNo, 
        rFullName, rPhone, rAddress, kFullName, kPhone, kAddress, date_captured, captured_by, emr_no, ID_card_no
    ) VALUES (
        :EmployeeCode, :JoiningDate, :Department, :Designation, :Qualification, :TotalExperience, :Cadre, :Title, :FirstName, 
        :MiddleName, :LastName, :phone, :DateofBirth, :Gender, :blood_grp, :MaritalStatus, :no_of_dependents, :Religion, :Tribe, 
        :PresentAddress, :PermanentAddress, :StateLGA, :Nationality, :EmailAddress, :BankName, :Branch, :BankAddress, :AccountNo, 
        :rFullName, :rPhone, :rAddress, :kFullName, :kPhone, :kAddress, :date_captured, :captured_by, :emr_no, :ID_card_no
    )";

			$stmt = $db->prepare($insertSQL);

			// Bind parameters
			$stmt->bindParam(':EmployeeCode', $EmployeeCode);
			$stmt->bindParam(':JoiningDate', $_POST['JoiningDate']);
			$stmt->bindParam(':Department', $_POST['Department']);
			$stmt->bindParam(':Designation', $_POST['Designation']);
			$stmt->bindParam(':Qualification', $_POST['Qualification']);
			$stmt->bindParam(':TotalExperience', $_POST['TotalExperience']);
			$stmt->bindParam(':Cadre', $_POST['Cadre']);
			$stmt->bindParam(':Title', $_POST['Title']);
			$stmt->bindParam(':FirstName', $_POST['FirstName']);
			$stmt->bindParam(':MiddleName', $_POST['MiddleName']);
			$stmt->bindParam(':LastName', $_POST['LastName']);
			$stmt->bindParam(':phone', $_POST['Phone']);
			$stmt->bindParam(':DateofBirth', $_POST['dob']);
			$stmt->bindParam(':Gender', $_POST['Gender']);
			$stmt->bindParam(':blood_grp', $_POST['blood_group']);
			$stmt->bindParam(':MaritalStatus', $_POST['marital']);
			$stmt->bindParam(':no_of_dependents', $no_of_dependents);
			$stmt->bindParam(':Religion', $_POST['religion']);
			$stmt->bindParam(':Tribe', $_POST['tribe']);
			$stmt->bindParam(':PresentAddress', $_POST['PresentAddress']);
			$stmt->bindParam(':PermanentAddress', $_POST['PermanentAddress']);
			$stmt->bindParam(':StateLGA', $state);
			$stmt->bindParam(':Nationality', $_POST['nationality']);
			$stmt->bindParam(':EmailAddress', $_POST['email']);
			$stmt->bindParam(':BankName', $_POST['bank']);
			$stmt->bindParam(':Branch', $_POST['Branch']);
			$stmt->bindParam(':BankAddress', $_POST['BankAddress']);
			$stmt->bindParam(':AccountNo', $_POST['Accountno']);
			$stmt->bindParam(':rFullName', $_POST['referee_fullname']);
			$stmt->bindParam(':rPhone', $_POST['referee_phone_no']);
			$stmt->bindParam(':rAddress', $_POST['referee_PermanentAddress']);
			$stmt->bindParam(':kFullName', $_POST['kin_fullname']);
			$stmt->bindParam(':kPhone', $_POST['kin_phone_no']);
			$stmt->bindParam(':kAddress', $_POST['kin_PermanentAddress']);
			$stmt->bindParam(':date_captured', $setdate);
			$stmt->bindParam(':captured_by', $_SESSION['fullname']);
			$stmt->bindParam(':emr_no', $_POST['hospital_no']);
			$stmt->bindParam(':ID_card_no', $_POST['ID_card_no']);

			// Execute insert
			$stmt->execute();

			// Commit if all goes well
			$db->commit();

			// Redirect after successful insert
			$ECode = $EmployeeCode;
			header("location:index.php?hr&ECode=$ECode&sv");
			exit;
		} catch (Exception $e) {
			// Rollback on error
			if ($db->inTransaction()) {
				$db->rollBack();
			}

			// Show error details (for debugging only — in production, log this instead)
			echo "<div style='color:red; font-weight:bold;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
		}
	}
}





?>

<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5><?php echo $title; ?></h5>
			</div>
			<div class="ibox-content">



				<div class="row">

					<?php

					$ECode = $_GET["ECode"];

					$stmt = $db->prepare("SELECT * FROM hremp WHERE EmployeeCode = :ECode");
					$stmt->bindParam(':ECode', $ECode, PDO::PARAM_STR);
					$stmt->execute();
					$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);

					?>
					<form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

						<div class="col-sm-4">
							<div class="panel panel-default">
								<div class="panel-body">


									<strong style="font-size:14px; color:#006">[ Employee Registration ]</strong>
									<hr>

									<div class="form_sep">
										<label for="reg_input_no" class="req">Employee Code</label>
										<input type="text" id="EmployeeCode" name="EmployeeCode" class="form-control" required readonly value="<?php
																																				if ($row_rstSelect['EmployeeCode'] == '') {
																																					echo $EmployeeCode;
																																				} else {
																																					echo $row_rstSelect['EmployeeCode'];
																																				} ?>">
									</div>

									<?php

									$joiningDate = $row_rstSelect['JoiningDate'];
									if ($joiningDate == '') {
										$formattedDate = ''; /// date('Y-m-d');
									} else {

										$formattedDate = date('Y-m-d', strtotime($joiningDate));
									}

									?>

									<div class="form_sep" id="">
										<label for="reg_input_no" class="req">Joining Date</label>
										<div class="input-group date">
											<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
											<input type="date" class="form-control" name="JoiningDate" value="<?= $formattedDate; ?>" required>
										</div>
									</div>
									<div class="form_sep" id="">
										<label for="ID_card_no">Staff ID Card No. <br> <small><i>(Optional, can be updated later)</i></small></label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-user"></i></span>
											<input type="text" class="form-control" name="ID_card_no" id="ID_card_no" value="">
										</div>
									</div>
									<div class="form_sep">
										<?php $dp = $row_rstSelect['Department']; ?>
										<label for="reg_select" class="req">Department</label>
										<select name="Department" id="Department" class="form-control" required>
											<option value="">Select Department...</option>
											<?php

											$stt = $db->query("SELECT * FROM department order by department");
											while ($row_rstdepartment = $stt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option <?php if ($dp == $row_rstdepartment["sn"]) { ?>selected<?php } ?> value="<?php echo $row_rstdepartment["sn"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
											<?php } ?>
										</select>
									</div>


									<div class="form_sep">
										<label for="reg_select" class="req">Designation</label>
										<select name="Designation" id="Designation" class="form-control" required>
											<?php if ($row_rstSelect['Designation'] != '') { ?>
												<option value="<?php echo $row_rstSelect['Designation']; ?>"><?php echo $row_rstSelect['Designation']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select Designation...</option>
											<?php } ?>

											<?php
											$stmt_dsg = $db->query("SELECT * FROM designation order by designation");
											while ($row_rstrstdesignationt = $stmt_dsg->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $row_rstrstdesignationt["designation"]; ?>"><?php echo $row_rstrstdesignationt["designation"]; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class=""> Qualification</label>
										<input type="text" id="Qualification" name="Qualification" class="form-control" value="<?php echo $row_rstSelect['Qualification']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class=""> Total Experience</label>
										<input type="text" id="TotalExperience" name="TotalExperience" class="form-control" value="<?php echo $row_rstSelect['TotalExperience']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_select" class="req">User Type/Cadre</label>
										<select name="Cadre" id="Cadre" class="form-control" required>
											<?php if ($row_rstSelect['Cadre'] != '') { ?>
												<option value="<?php echo $row_rstSelect['Cadre']; ?>"><?php echo $row_rstSelect['Cadre'];; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select User Type/Cadre...</option>
											<?php } ?>

											<?php
											$stt = $db->query("SELECT * FROM cadre order by cadre");
											while ($row_rstcedre = $stt->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $row_rstcedre["cadre"]; ?>"><?php echo $row_rstcedre["cadre"]; ?></option>
											<?php } ?>
										</select>
									</div>

									<br><br><br>
									<strong style="font-size:14px; color:#006">[ Personal Details ]</strong>
									<hr>

									<div class="form_sep">
										<label for="reg_select" class="req">Title</label>
										<select name="Title" id="Title" class="form-control" required>

											<?php if ($row_rstSelect['Title'] != '') { ?>
												<option value="<?php echo $row_rstSelect['Title']; ?>"><?php echo $row_rstSelect['Title']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select Title...</option>
											<?php } ?>

											<option value="Mr ">Mr</option>
											<option value="Mrs ">Mrs </option>
											<option value="Miss">Miss </option>
											<option value="Dr">Dr</option>
										</select>
									</div>
									<?php /// echo '===========' .  $_SESSION['unit_head']; 
									?>

									<div class="form_sep">
										<label for="reg_input_no" class="req">First Name</label>
										<input type="text" id="FirstName" name="FirstName" class="form-control" required value="<?php echo $row_rstSelect["FirstName"]; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="">Middle Name</label>
										<input type="text" id="MiddleName" name="MiddleName" class="form-control" value="<?php echo $row_rstSelect["MiddleName"]; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="req">Last Name</label>
										<input type="text" id="LastName" name="LastName" class="form-control" required value="<?php echo $row_rstSelect["LastName"]; ?>">
									</div>


									<div class="form_sep" id="">
										<label for="reg_input_no" class="">Date of Birth</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="date" class="form-control" name="dob" value="<?php if ($row_rstSelect['DateofBirth'] != '') {
																																												echo $row_rstSelect['DateofBirth'];
																																											} else {
																																												echo '';
																																											} ?>">
										</div>
									</div>

									<div class="form_sep">
										<label for="reg_select" class="req">Gender</label>
										<select name="Gender" id="Gender" class="form-control" required>

											<?php if ($row_rstSelect['Gender'] != '') { ?>
												<option value="<?php echo $row_rstSelect['Gender']; ?>"><?php echo $row_rstSelect['Gender']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select...</option>
											<?php } ?>
											<option value="Male">Male</option>
											<option value="Female">Female</option>
										</select>
									</div>

									<div class="form_sep">
										<label for="reg_select">Blood Group</label>
										<select name="blood_group" id="blood_group" class="form-control">

											<?php if ($row_rstSelect['blood_grp'] != '') { ?>
												<option value="<?php echo $row_rstSelect['blood_grp']; ?>"><?php echo $row_rstSelect['blood_grp']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select...</option>
											<?php } ?>

											<option value="O-">O-</option>
											<option value="O+">O+</option>
											<option value="A+">A+</option>
											<option value="A-">A-</option>
											<option value="B-">B-</option>
											<option value="B+">B+</option>
											<option value="AB-">AB-</option>
											<option value="AB+">AB+</option>
										</select>
									</div>


									<div class="form_sep">
										<label for="reg_select" class="">Marital Status</label>
										<select name="marital" id="marital" class="form-control">

											<?php if ($row_rstSelect['MaritalStatus'] != '') { ?>
												<option value="<?php echo $row_rstSelect['MaritalStatus']; ?>"><?php echo $row_rstSelect['MaritalStatus']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select Marital...</option>
											<?php } ?>

											<option value="Single ">Single </option>
											<option value="Married ">Married </option>
											<option value="Widowed ">Widowed </option>
											<option value="Divorced ">Divorced </option>
											<option value="Separated ">Separated </option>
										</select>
									</div>
									<div class="form_sep">
										<label for="">No of dependents(childeren,wards e.t.c)</label>
										<input type="number" name="no_of_dependents" class="form-control" value="<?php echo $row_rstSelect['no_of_dependents']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_select" class="">Religion</label>
										<select name="religion" id="religion" class="form-control">

											<?php if ($row_rstSelect['Religion'] != '') { ?>
												<option value="<?php echo $row_rstSelect['Religion']; ?>"><?php echo $row_rstSelect['Religion']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select Religion...</option>
											<?php } ?>
											<option value="Christianity">Christianity</option>
											<option value="Islam">Islam</option>
											<option value="Hindu">Hindu</option>
											<option value="Judaism">Judaism</option>
											<option value="Buddhism">Buddhism</option>
											<option value="Other">Others</option>
										</select>
									</div>

									<div class="form_sep">
										<label for="reg_input_name"> Tribe:</label>
										<input type="text" id="tribe" name="tribe" class="form-control" value="<?php echo $row_rstSelect['Tribe']; ?>">
									</div>



								</div>
							</div>
						</div>


						<div class="col-sm-4">
							<div class="panel panel-default">
								<div class="panel-body">

									<strong style="font-size:14px ; color:#006">[ Contact Details ]</strong>
									<hr>

									<div class="form_sep">
										<label for="reg_textarea_message" class="">Present Address</label>
										<textarea name="PresentAddress" id="PresentAddress" cols="30" rows="4" class="form-control" data-minlength="15"><?php echo $row_rstSelect['PresentAddress']; ?></textarea>
									</div>

									<div class="form_sep">
										<label for="reg_textarea_message" class="">Permanent Address</label>
										<textarea name="PermanentAddress" id="PermanentAddress" cols="30" rows="4" class="form-control" data-minlength="15"><?php echo $row_rstSelect['PermanentAddress']; ?></textarea>
									</div>

									<div class="form_sep">
										<label for="reg_select" class="">State/LGA</label>
										<select name="state_lga" id="state_lga" class="form-control">

											<?php if ($row_rstSelect['StateLGA'] != '') { ?>
												<option value="<?php echo $row_rstSelect['StateLGA']; ?>"><?php echo $row_rstSelect['StateLGA']; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select...</option>
											<?php } ?>
											<?php include("state_lga.php"); ?>
										</select>
									</div>

									<div class="form_sep">
										<label for="reg_input_name"> Specify Other State :</label>
										<input type="text" id="other_state" name="other_state" class="form-control">
									</div>


									<div class="form_sep">
										<label for="reg_input_name" class=""> Nationality:</label>
										<input type="text" id="nationality" name="nationality" class="form-control" value="<?php echo $row_rstSelect['Nationality']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_name" class=""> Phone/Mobile Number:</label>
										<input type="text" id="Phone" name="Phone" class="form-control" value="<?php echo $row_rstSelect['phone']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_name" class=""> Email Address:</label>
										<input type="email" id="email" name="email" class="form-control" data-type="email" value="<?php echo $row_rstSelect['EmailAddress']; ?>">
									</div>


									<br><br><br>
									<strong style="font-size:14px; color:#006">[ Bank Details ]</strong>
									<hr>


									<div class="form_sep">
										<label for="reg_select" class="">Bank Name</label>
										<select name="bank" id="bank" class="form-control">
											<?php if ($row_rstSelect['BankName'] != '') { ?>
												<option value="<?php echo $row_rstSelect['BankName']; ?>"><?php echo $row_rstSelect['BankName'];; ?></option>
											<?php } else { ?>
												<option selected="selected" value="">Select Bank Name...</option>
											<?php } ?>

											<?php
											$stmt_bnk = $db->query("SELECT * FROM bank order by bank");
											while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) { ?>
												<option value="<?php echo $row_rstbank["bank"]; ?>"><?php echo $row_rstbank["bank"]; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form_sep">
										<label for="reg_input_name"> Branch:</label>
										<input type="text" id="Branch" name="Branch" class="form-control" value="<?php echo $row_rstSelect['Branch']; ?>">
									</div>

									<div class="form_sep">

										<label for="reg_input_name"> Bank Address:</label>
										<input type="text" id="BankAddress" name="BankAddress" class="form-control" value="<?php echo $row_rstSelect['BankAddress']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_name" class=""> Account No:</label>
										<input type="text" id="Accountno" name="Accountno" class="form-control" value="<?php echo $row_rstSelect['AccountNo']; ?>">
									</div>


									<input type="hidden" name="old_emr" value="<?php echo $row_rstSelect['emr_no']; ?>">
									<div class="form_sep">
										<label for="reg_input_name" class=""> HOSPITAL EMR No:</label>
										<input type="text" id="hospital_no" name="hospital_no" class="form-control" value="<?php echo $row_rstSelect['emr_no']; ?>">
									</div>


								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="panel panel-default">
								<div class="panel-body">

									<strong style="font-size:14px; color:#006">[ Referee Details ]</strong>
									<hr>

									<div class="form_sep">
										<label for="reg_input_no" class="">Full Name</label>
										<input type="text" id="referee_fullname" name="referee_fullname" class="form-control" value="<?php echo $row_rstSelect['rFullName']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="">Phone Number</label>
										<input type="text" id="referee_phone_no" name="referee_phone_no" class="form-control" value="<?php echo $row_rstSelect['rPhone']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_textarea_message" class="">Permanent Address</label>
										<textarea name="referee_PermanentAddress" id="referee_PermanentAddress" cols="30" rows="4" class="form-control" data-minlength="15"><?php echo $row_rstSelect['rAddress']; ?></textarea>
									</div>

									<br><br><br>
									<strong style="font-size:14px">[ Next of Kin Details ]</strong>
									<hr>

									<div class="form_sep">
										<label for="reg_input_no" class="">Full Name</label>
										<input type="text" id="kin_fullname" name="kin_fullname" class="form-control" value="<?php echo $row_rstSelect['kFullName']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_input_no" class="">Phone Number</label>
										<input type="text" id="kin_phone_no" name="kin_phone_no" class="form-control" value="<?php echo $row_rstSelect['kPhone']; ?>">
									</div>

									<div class="form_sep">
										<label for="reg_textarea_message" class="">Permanent Address</label>
										<textarea name="kin_PermanentAddress" id="kin_PermanentAddress" cols="30" rows="4" class="form-control" data-minlength="15"><?php echo $row_rstSelect['kAddress']; ?></textarea>
									</div>

									<?php if (isset($_GET['Edit'])) { ?>
										<input type="hidden" name="Edit_emp" value="<?= $row_rstSelect['EmployeeCode']; ?>">
									<?php } ?>

									<div class="form_sep">
										<div class="pull-left">
											<button class="btn btn-success" type="submit" name="save_employeee" id="save_employeee">Save</button>
										</div>

										<div class="pull-right">
											<a href="index.php?hr" class="btn btn-warning">Cancel</a>
										</div>
									</div>

					</form>


					<hr>


					<div class="form_sep">
						<?php if ($row_rstSelect['EmployeeCode'] == '') {
							$EmployeeCode = $EmployeeCode;
						} else {
							$EmployeeCode = $row_rstSelect['EmployeeCode'];
						} ?>
						<img src="<?php echo staff_p . 'port_' . $EmployeeCode . '.' . 'jpg'; ?>" alt="" width="150" height="150" class="img-thumbnail user_avatar">
						<hr>
						<img src="<?php echo staff_p . 'sign_' . $EmployeeCode . '.' . 'jpg'; ?>" alt="" width="150" height="150" class="img-thumbnail user_avatar">

					</div>

					<br>

					<input type="button" name="edit" value="Upload Passport/Signature" data-target="#myModal5" class="btn btn-info staff_passort" />
					<br>
					<input type="button" name="edit" value="Upload other documents" data-target="#uploadStaffDocumentModal" class="btn btn-info staff_other_documents" /> <br>
					<div class="modal inmodal fade" id="uploadStaffDocumentModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									<h4 class="modal-title" id="">Upload Staff Document</h4>
								</div>
								<div class="modal-body" style="min-height: 300px;">
									<form action="" method="POST" name="subject" enctype="multipart/form-data">
										<div>
											<label for="reg_input_no" class="req">Title: </label>
											<input type="text" maxlength="100" name="title" id="title" class="form-control" placeholder="Document Title" required>
										</div>
										<br>
										<div>
											<label for="reg_input_no" class="req">Document [jpg, png, pdf] </label>
											<input type="file" name="document_file" id="document_file" class="form-control" required>
										</div>
										<br>

										<div>
											<input type="hidden" name="emp_code" value="<?= $_GET['ECode']; ?>">
											<button type="submit" class="btn btn-primary" name="uploadStaffDocumentBtn" style="display: block; width:100%;">Submit</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<hr>
					<strong style="font-size:14px; color:#006">[ Other Documents]</strong>
					<hr>
					<?php
					$target_dir = "../uploads/staff/";
					$staff_id = $_GET['ECode'];
					if (isset($_POST['uploadStaffDocumentBtn'])) {
						$title = $_POST['title'];
						$ECode = $_POST['emp_code'];

						$fileName = $ECode . "_" . $title . "_" . basename($_FILES["document_file"]["name"]);
						$target_file = $target_dir . $fileName;
						$uploadOk = 1;
						$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
						if ($imageFileType == 'pdf' || $imageFileType == 'jpg' || $imageFileType == 'png' || $imageFileType == 'jpeg') {
							$tmp_name = $_FILES['document_file']['tmp_name'];

							if (move_uploaded_file($tmp_name, $target_file)) {
								$save = $db->prepare("INSERT INTO staff_other_docs (staff_no,document_file,document_title,done_by) VALUES (?,?,?,?)");

								if ($save->execute([$ECode, $fileName, $title, $done_by])) {

									$one = 1;
									$zero = 0;
									$error_status = 2;
									$error_msg = 'Document is uploaded successfully...';
								} else {
									$error_status = 1;
									$error_msg = 'A DB Error occured';
								}
							} else {
								$error_status = 1;
								$error_msg = 'An Upload Error Occured';
							}
						} else {
							$error_status = 1;
							$error_msg = 'File type not supported';
						}
					}

					if (isset($_GET['del_file'])) {
						$file_to_delete = explode("_", $_GET['del_file']);
						$file_to_delete = $file_to_delete[1];
						$del = $db->prepare("UPDATE staff_other_docs SET status = ? WHERE doc_sn = ?");
						if ($del->execute([0, $file_to_delete])) {
							$sv = 1;
						}
					}
					$query = $db->prepare("SELECT * FROM staff_other_docs WHERE staff_no = ? AND status = ?");
					if ($query->execute([$staff_id, 1])) {
						$files = $query->fetchAll();
					?>
						<table class="table">
							<?php foreach ($files as $file) { ?>
								<tr>
									<td>
										<a target="_blank" href="<?php echo $target_dir . $file['document_file'] ?>">
											<strong>
												<?php echo $file['document_title'] ?>
											</strong>
										</a>&nbsp;&nbsp;
										<a href="?Add&ECode=<?php echo $staff_id ?>&del_file=<?php echo uniqid() . "_" . $file['doc_sn'] ?>" onclick="return confirm('Are you sure you want to delete the document?');">[ Delete ]</a>
									</td>
								</tr>
							<?php } ?>
						</table>
					<?php } ?>
				</div>
			</div>
		</div>

	</div>




</div>
</div>
</div>
</div>


<div class="modal inmodal fade" id="staff_port_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Passport & Signature</h4>
			</div>

			<div class="modal-body">

				<form method="POST" id="passport_body" enctype="multipart/form-data" action="pro_pass.php">

					<div class="form_sep">
						<strong>Add New/Change Passport/Signature</strong>
					</div>

					<div class="form_sep">
						<input type="file" name="file_foto" id="file_foto" class="form-control" />
					</div>

					<div class="form_sep">
						<label>Upload Type</label>
						<select name="upload_type" id="upload_type" class="form-control" required>

							<option selected="selected" value="">Select...</option>
							<option value="port">Passport</option>
							<option value="sign">Signature</option>
						</select>
					</div>


					<div class="form_sep">

						<button class="btn btn-primary btn-xs" type="submit" name="upload_pass">Upload</button>&nbsp;&nbsp;
						<a href="" class="btn btn-warning btn-xs">Cancel</a>
					</div>
					<input type="hidden" name="pass_code" id="pass_code" value="<?php if ($row_rstSelect['EmployeeCode'] == '') {
																					echo $EmployeeCode;
																				} else {
																					echo $row_rstSelect['EmployeeCode'];
																				} ?>" />
					<input type="hidden" name="MM_update" value="passport" />

				</form>
			</div>
		</div>
	</div>
</div>