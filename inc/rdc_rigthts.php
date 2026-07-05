                           			<?php



										if (isset($_POST["apply_set"])) {

											if (isset($_POST["create1"])) {
												$create = 1;
											} else {
												$create = 0;
											}
											if (isset($_POST["price"])) {
												$price = 1;
											} else {
												$price = 0;
											}
											if (isset($_POST["request"])) {
												$request = 1;
											} else {
												$request = 0;
											}
											if (isset($_POST["specimen"])) {
												$specimen = 1;
											} else {
												$specimen = 0;
											}
											if (isset($_POST["enter"])) {
												$enter = 1;
											} else {
												$enter = 0;
											}
											if (isset($_POST["approve"])) {
												$approve = 1;
											} else {
												$approve = 0;
											}
											if (isset($_POST["stock"])) {
												$stock = 1;
											} else {
												$stock = 0;
											}
											if (isset($_POST["inventory"])) {
												$inventory = 1;
											} else {
												$inventory = 0;
											}
											if (isset($_POST["report"])) {
												$report = 1;
											} else {
												$report = 0;
											}
											if (isset($_POST["users"])) {
												$users = 1;
											} else {
												$users = 0;
											}
											if (isset($_POST["oncredit"])) {
												$oncredit = 1;
											} else {
												$oncredit = 0;
											}
											if (isset($_POST["oncredit_adm"])) {
												$oncredit_adm = 1;
											} else {
												$oncredit_adm = 0;
											}
											if (isset($_POST["sms"])) {
												$sms = 1;
											} else {
												$sms = 0;
											}
											if (isset($_POST["equipmnt"])) {
												$equipmnt = 1;
											} else {
												$equipmnt = 0;
											}
											if (isset($_POST["inv"])) {
												$inv = 1;
											} else {
												$inv = 0;
											}
											if (isset($_POST["bill"])) {
												$bill = 1;
											} else {
												$bill = 0;
											}
											if (isset($_POST["edit_appr"])) {
												$edit_appr = 1;
											} else {
												$edit_appr = 0;
											}


											$stmt = $db->prepare("SELECT username FROM invsti_users WHERE username=:username");
											$stmt->bindParam(':username', $_POST["username"]);
											$stmt->execute();



											$speciality = $_POST["speciality"];
											if ($speciality == "Administrator" or $speciality == "Receptionist") {
												$section = "";
											} else {
												$section = $_POST["section"];
											}


											if ($stmt->rowCount() > 0) {
												$stmt = $db->prepare("UPDATE invsti_users SET 
												speciality=:speciality, 
												section=:section, 
												create1=:create1, 
												price=:price, 
												request=:request, 
												specimen=:specimen, 
												enter=:enter, 
												approve=:approve, 
												stock=:stock, 
												inventory=:inventory, 
												report=:report, 
												users=:users, 
												oncredit=:oncredit, 
												oncredit_adm=:oncredit_adm, 
												sms=:sms, 
												equipmnt=:equipmnt, 
												inv=:inv, 
												bill=:bill, 
												edit_appr=:edit_appr 
												WHERE username=:username");

												$stmt->bindParam(':speciality', $_POST["speciality"]);
												$stmt->bindParam(':section', $section);
												$stmt->bindParam(':create1', $create);
												$stmt->bindParam(':price', $price);
												$stmt->bindParam(':request', $request);
												$stmt->bindParam(':specimen', $specimen);
												$stmt->bindParam(':enter', $enter);
												$stmt->bindParam(':approve', $approve);
												$stmt->bindParam(':stock', $stock);
												$stmt->bindParam(':inventory', $inventory);
												$stmt->bindParam(':report', $report);
												$stmt->bindParam(':users', $users);
												$stmt->bindParam(':oncredit', $oncredit);
												$stmt->bindParam(':oncredit_adm', $oncredit_adm);
												$stmt->bindParam(':sms', $sms);
												$stmt->bindParam(':equipmnt', $equipmnt);
												$stmt->bindParam(':inv', $inv);
												$stmt->bindParam(':bill', $bill);
												$stmt->bindParam(':edit_appr', $edit_appr);
												$stmt->bindParam(':username', $_POST["username"]);
												$stmt->execute();
										?>
                           					<div class="alert alert-success">
                           						Saved Successfully
                           					</div>

                           				<?php
											} else {
												$stmt = $db->prepare("INSERT INTO invsti_users(username, speciality, section, create1, price, request, specimen, enter, approve, stock, inventory, report, users, oncredit, oncredit_adm, sms, equipmnt, inv, bill, edit_appr) VALUES (:username, :speciality, :section, :create1, :price, :request, :specimen, :enter, :approve, :stock, :inventory, :report, :users, :oncredit, :oncredit_adm, :sms, :equipmnt, :inv, :bill, :edit_appr)");
												$stmt->bindParam(':username', $_POST["username"]);
												$stmt->bindParam(':speciality', $_POST["speciality"]);
												$stmt->bindParam(':section', $section);
												$stmt->bindParam(':create1', $create);
												$stmt->bindParam(':price', $price);
												$stmt->bindParam(':request', $request);
												$stmt->bindParam(':specimen', $specimen);
												$stmt->bindParam(':enter', $enter);
												$stmt->bindParam(':approve', $approve);
												$stmt->bindParam(':stock', $stock);
												$stmt->bindParam(':inventory', $inventory);
												$stmt->bindParam(':report', $report);
												$stmt->bindParam(':users', $users);
												$stmt->bindParam(':oncredit', $oncredit);
												$stmt->bindParam(':oncredit_adm', $oncredit_adm);
												$stmt->bindParam(':sms', $sms);
												$stmt->bindParam(':equipmnt', $equipmnt);
												$stmt->bindParam(':inv', $inv);
												$stmt->bindParam(':bill', $bill);
												$stmt->bindParam(':edit_appr', $edit_appr);
												$stmt->execute();

											?>
                           					<div class="alert alert-success">
                           						Saved Successfully
                           					</div>

                           			<?php

											}
										}

										////////////////////////////////////////////
										?>
                           			<form action="<?php echo $file_name; ?>" method="POST">

                           				<div class="row">
                           					<div class="col-md-8">
                           						<div class="form_sep">
                           							<label for="reg_input_no" class="req">Select a User & Setup Rights</label>
                           							<select name="staff_id" class="input-sm chosen-select" style="width:350px;">
                           								<option selected="selected" value="">Search and Select Staff</option>
                           								<?php $stmt = $db->query("SELECT u.id, h.FirstName, h.MiddleName, h.LastName , h.Cadre 
						FROM admin_users as u inner join hremp as h on h.EmployeeCode=u.EmployeeCode WHERE u.status='1' and
						(rights='RE' or rights='MD' or rights='GM' or rights='DR' or rights='AC' or rights='LB')
						order by FirstName");
															while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                           									<option value="<?php echo $row["id"]; ?>"><?php echo $row["FirstName"] . ' ' . $row["LastName"] . ' ' . $row["MiddleName"] . ' (' . $row["Cadre"] . ')'; ?></option>

                           								<?php } ?>
                           							</select>

                           						</div>
                           						<div class="form_sep">
                           							<button type="submit" class="btn btn-success btn btn-sm" autofocus name="apply_">Apply Search</button>
                           						</div>
                           					</div>

                           					<div class="col-md-4">
                           						<label for="reg_input_no" class="">.</label><br>
                           						<a rel="" href="index.php?rit" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>
                           					</div>
                           				</div>
                           			</form>




                           			<hr>

                           			<?php if (isset($_POST['apply_'])) {
											$stmt = $db->prepare("SELECT fullname, username FROM admin_users WHERE id=:staff_id");
											$stmt->bindParam(':staff_id', $_POST['staff_id']);
											$stmt->execute();
											if ($stmt->rowCount() > 0) {
												$row = $stmt->fetch(PDO::FETCH_ASSOC);
										?>



                           					<form action="<?php echo $file_name; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

                           						<div class="alert alert-success">
                           							<label for="" class="">Current User : <?php echo $row['fullname']; ?></label>
                           							<input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                           						</div>


                           						<table class="table table-striped table-bordered table-hover">
                           							<thead>
                           								<tr>
                           									<th width="5%" data-toggle="true">&nbsp;</th>
                           									<th width="46%" data-toggle="true">Rights</th>
                           									<th width="5%" data-toggle="true">&nbsp;</th>
                           									<th width="44%" data-toggle="true">Rights</th>
                           								</tr>
                           							</thead>
                           							<tbody>
                           								<?php
															$stmt = $db->prepare("SELECT * FROM invsti_users WHERE username=:username");
															$stmt->bindParam(':username', $row['username']);
															$stmt->execute();
															$row2 = $stmt->fetch(PDO::FETCH_ASSOC);
															?>
                           								<tr>
                           									<td><input type="checkbox" name="create1" value="1" <?php if ($row2['create1'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Create New Investigations (Lab Tests/Radiology Investigations)</td>
                           									<td><input type="checkbox" name="price" value="1" <?php if ($row2['price'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Manage Investigations Price List <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="request" value="1" <?php if ($row2['request'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Add Patient Investigations Request</td>
                           									<td><input type="checkbox" name="specimen" value="1" <?php if ($row2['specimen'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Take Specimen or capture Scan</td>
                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="enter" value="1" <?php if ($row2['enter'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Enter Lab/Scan Results</td>
                           									<td><input type="checkbox" name="approve" value="1" <?php if ($row2['approve'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Approve Final Results <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="stock" value="1" <?php if ($row2['stock'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Stock Control <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           									<td><input type="checkbox" name="inventory" value="1" <?php if ($row2['inventory'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Stock Inventory (Adding & Removing from Stock) <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="report" value="1" <?php if ($row2['report'] == 1) { ?> checked <?php } ?>></td>
                           									<td>View General Reports/Transactions <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           									<td><input type="checkbox" name="users" value="1" <?php if ($row2['users'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Manage Users (Signing roles and privileges) <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="oncredit" value="1" <?php if ($row2['oncredit'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Take Specimen/Run Investigation ON-CREDIT <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           									<td><input type="checkbox" name="oncredit_adm" value="1" <?php if ($row2['oncredit_adm'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Take Specimen/Run Investigation ON-CREDIT(Admitted Patients Only) <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="sms" value="1" <?php if ($row2['sms'] == 1) { ?> checked <?php } ?>></td>
                           									<td>SMS Result Notification</td>
                           									<td><input type="checkbox" name="equipmnt" value="1" <?php if ($row2['equipmnt'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Equipment Maintenance Logsheet</td>

                           								</tr>

                           								<tr>
                           									<td><input type="checkbox" name="inv" value="1" <?php if ($row2['inv'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Generate Invoices</td>
                           									<td><input type="checkbox" name="bill" value="1" <?php if ($row2['bill'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Billing (Collecting Cash, Invoice, etc) <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                           								</tr>
                           								<tr>
                           									<td><input type="checkbox" name="edit_appr" value="1" <?php if ($row2['edit_appr'] == 1) { ?> checked <?php } ?>></td>
                           									<td>Edit Approved Results (After 24hrs) <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                           									<td>&nbsp;</td>
                           									<td>&nbsp;</td>

                           								</tr>



                           							</tbody>
                           						</table>
                           						<div class="form_sep">
                           							<label for="reg_input_no" class="">User Category</label>
                           							<select name="speciality" id="speciality" class="form-control" data-required="true">

                           								<?php if ($row2['speciality'] == '') { ?>
                           									<option selected="selected" value="">Select ...</option>
                           								<?php } else { ?>
                           									<option selected="selected" value="<?php echo $row2['speciality'] ?>"><?php echo $row2['speciality'] ?></option>
                           								<?php } ?>

                           								<option value="Chemical Pathologist">Chemical Pathologist</option>
                           								<option value="Medical Microbiologist">Medical Microbiologist</option>
                           								<option value="Radiologist">Radiologist</option>
                           								<option value="Haematogist">Haematogist</option>
                           								<option value="X-ray Imaging Technician">X-ray Imaging Technician</option>
                           								<option value="Radiographer">Radiographer</option>
                           								<option value="Laboratory Technician">Laboratory Technician</option>
                           								<option value="Medical Microbiologist">Medical Microbiologist</option>
                           								<option value="Laboratory Scientist">Laboratory Scientist</option>
                           								<option value="Data Operator">Data Entry Staff</option>
                           								<option value="Receptionist">Receptionist</option>
                           								<option value="Administrator">Administrator</option>
                           							</select>
                           						</div>


                           						<div class="form_sep" id="investigation">
                           							<label for="reg_input_no" class="">Investigation Unit</label>
                           							<select name="section" id="section" class="form-control">

                           								<?php if ($row2['section'] == '') { ?>
                           									<option selected="selected" value="">Select ...</option>
                           								<?php } else { ?>
                           									<option selected="selected" value="<?php echo $row2['section'] ?>"><?php echo $row2['section'] ?></option>
                           								<?php } ?>

                           								<option value="Laboratory">Laboratory</option>
                           								<option value="Radiology">Radiology</option>
                           							</select>
                           						</div>

                           						<div class="form_sep">
                           							<button class="btn btn-primary" type="submit" name="apply_set">Apply</button>
                           						</div>

                           					</form>
                           				<?php } else { ?>
                           					<div class="alert alert-warning">
                           						<p>No User Found / Select User from List above to Display Here </p>
                           					</div>

                           				<?php } ?>
                           			<?php } else { ?>

                           				<div class="alert alert-warning">
                           					<p>Select User from List above to Display Here</p>
                           				</div>


                           			<?php } ?>