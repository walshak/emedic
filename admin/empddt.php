<?php

if (isset($_GET["dl"])) {

	$dl = $_GET['dl'];
	$deleteSQL = $db->prepare("DELETE FROM hred WHERE sn='$dl'");
	$deleteSQL->execute();

	header("location:index.php?ED");
}

if (isset($_GET["ED"])) {
	$ED = $_GET["ED"];
	if ($ED != '') {
		$edit_mode = 1;
		$stmt = $db->query("Select * from hred where sn='$ED'");
		$rowx = $stmt->fetch(PDO::FETCH_ASSOC);
	} else {
		$edit_mode = 0;
	}
}


if (isset($_POST["ED"])) {
	if ($_POST["edit_mode"] == 0) {
		$descriptn = $_POST['descriptn'];
		$stmt = $db->prepare('SELECT * FROM hred WHERE descriptn = :descriptn');
		$stmt->bindParam(':descriptn', $descriptn);
		$stmt->execute();

		if ($stmt->rowCount() == 0) {
			$stmt = $db->prepare('INSERT INTO hred (descriptn, type) VALUES (:descriptn, :type)');
			$stmt->bindParam(':descriptn', $_POST["descriptn"]);
			$stmt->bindParam(':type', $_POST["typee"]);
			$stmt->execute();
		}
	} else {
		$stmt = $db->prepare('UPDATE hred SET descriptn = :descriptn, type = :type WHERE sn = :sn');
		$stmt->bindParam(':descriptn', $_POST['descriptn']);
		$stmt->bindParam(':type', $_POST['typee']);
		$stmt->bindParam(':sn', $_POST['sn']);
		$stmt->execute();
	}
	header("location:index.php?ED");
}
?>


<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Set Allowance/Earning and Deductions Setup</h5>
			</div>
			<div class="ibox-content">


				<form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

					<div class="form_sep">
						<label for="reg_input_no" class="req">Enter Description</label>
						<input type="text" id="descriptn" name="descriptn" class="form-control" data-required="true" placeholder="" maxlength="50" value="<?php echo $rowx['descriptn']; ?>" required>
					</div>

					<div class="form_sep">
						<label for="reg_input_no" class="req">Select Allowance/Earnings or Deductions</label>
						<select name="typee" data-placeholder="Select.." class="form-control" required>
							<option value="<?php echo $rowx['type']; ?>">
								<?php if ($rowx['type'] == 'E') {
									echo 'Earning/Allowance';
								} elseif ($rowx['type'] == 'D') {
									echo 'Deduction';
								} else {
									echo '-- select --';
								} ?></option>

							<option value="E">Earning/Allowance</option>
							<option value="D">Deduction</option>

						</select>
					</div>


					<div class="form_sep">
						<div class="pull-left">
							<button class="btn btn-success" type="submit" name="ED" id="ED">Save</button>
						</div>

						<div class="pull-right">
							<a href="index.php?ED" class="btn btn-warning">Cancel</a>
						</div>
					</div>
					<input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />
					<input type="hidden" name="sn" value="<?php echo $ED; ?>" />
				</form>

				<hr>

				<?php



				$stmt = $db->query("SELECT * FROM hred order by sn");
				if ($stmt->rowCount() > 0) { ?>

					<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9">
						<thead>
							<tr>
								<th data-toggle="true">SI.No</th>
								<th data-toggle="true">Description</th>
								<th data-toggle="true">Type</th>
								<th data-toggle="true">Manage</th>
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
									<td><?php echo $row['sn']; ?></td>
									<td><?php echo $row['descriptn']; ?></td>
									<td><?php
										if ($row['type'] == 'E') {
											echo 'Earning/Allowance';
										} else {
											echo 'Deduction';
										}
										?></td>
									<td><a href="index.php?<?php echo 'ED=' . $row['sn']; ?>"> [ Edit ] </a>
										&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
										<a href="index.php?ED&<?php echo 'dl=' . $row['sn']; ?>"> [ Delete ] </a>


									</td>
								</tr>
							<?php
								$colordecide++;
								$n++;
							} ?>

						</tbody>

					</table>
				<?php } else {
					echo 'No Records Found';
				}

				?>

			</div>
		</div>
	</div>
</div>