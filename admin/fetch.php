<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

if (isset($_POST["seen_request"])) {
	$rej = $_POST["seen_request"];
	if ($rej == 'all') {
		$stmt = "UPDATE stock_table_request SET seen=2 WHERE seen='1'";
		$db->exec($stmt);
		if ($stmt) {
			echo 'Done for All! Please Close!';
		}
	} else {
		$stmt = "UPDATE stock_table_request SET seen=2 WHERE sn='$rej'";
		$db->exec($stmt);
		if ($stmt) {
			echo 'Done!';
		}
	}
}



if (isset($_POST["finish_status"])) {
	$finish_status = $_POST["finish_status"];
	$stmt = "UPDATE stock_table_procurment SET finish_status='1' WHERE sn='$finish_status'";
	$db->exec($stmt);
	if ($stmt) {
		echo 'Done!';
	}
}

if (isset($_POST["edit_exp_date"])) {
	$sn = $_POST["edit_exp_date"];
	$new_expire_date = $_POST["new_expire_date"];
	$stock_sn = $_POST["stock_sn"];
	$stmt = "UPDATE stock_table_procurment SET expiry_date='$new_expire_date' WHERE sn='$sn'";
	$db->exec($stmt);
	$stmt = "UPDATE stock_table SET expire_date='$new_expire_date' WHERE sn='$stock_sn'";
	$db->exec($stmt);


	if ($stmt) {
		echo 'Save Successfully!';
	}
}


/// :sn,:new_expire_date


if (isset($_POST["edit_bed_id"])) {


	$stmt = $db->prepare('SELECT * FROM bed_mgt WHERE sn = :sn ORDER BY sn');
	$stmt->bindParam(':sn', $_POST["edit_bed_id"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		///echo json_encode([]);
	}
}

if (isset($_POST["edit_room_id"])) {
	$stmt = $db->prepare(
		"SELECT * FROM bed WHERE rooms=?"
	);
	$stmt->execute([$_POST["edit_room_id"]]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	echo json_encode($row);
}



if (isset($_POST["manage_reg_price"])) {
	$stmt = $db->query("SELECT hosp_price,nhis_price FROM prices_table WHERE item_service='New File' and hosp_price>0");
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>
	<form action="index.php?price=<?php echo $price; ?>" method="POST">
		<h3>File Amount: <?php echo $row['hosp_price']; ?> </h3>
		<h3>File Amount (NHIS): <?php echo $row['nhis_price']; ?> </h3>
		<div class="form_sep">
			<input type="text" name="file_amount" class="form-control" value="<?php echo $row['hosp_price']; ?>" required>
		</div>
		<div class="form_sep">
			<input type="text" name="file_amount_nhis" class="form-control" value="<?php echo $row['nhis_price']; ?>" required>
		</div>

		<div class="form_sep">
			<input type="submit" name="Update_Amount_reg_amt" value="Update" class="btn btn-primary btn-sm" />
		</div>

		<strong style="color: red"> Note: The Updated Amount Applies to All Consultation File Registrations</strong>
		<input type="hidden" name="table_name" value="<?php echo $title; ?>" />


	</form>
<?php
}

if (isset($_POST["manage_price_id"])) {
	header('Content-Type: application/json');
	$stmt = $db->prepare('SELECT * FROM prices_table WHERE sn = :sn');
	$stmt->bindParam(':sn', $_POST["manage_price_id"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		echo json_encode([]);
	}
	exit;
}

if (isset($_POST["manage_price_stock_id"])) {
	$stmt = $db->prepare('SELECT * FROM stock_table WHERE sn = :sn');
	$stmt->bindParam(':sn', $_POST["manage_price_stock_id"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		echo json_encode([]);
	}
}

if (isset($_POST["manage_price_invest_id"])) {
	$stmt = $db->prepare('SELECT * FROM lab_scan WHERE sn = :sn');
	$stmt->bindParam(':sn', $_POST["manage_price_invest_id"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		echo json_encode([]);
	}
}

if (isset($_POST["gd_edit"])) {
	$stmt = $db->prepare('SELECT * FROM guardian_tbl WHERE guardian_id = :guardian_id');
	$stmt->bindParam(':guardian_id', $_POST["gd_edit"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		echo json_encode([]);
	}
}


if (isset($_POST["edit_patient_id"])) {
	$stmt = $db->prepare("SELECT * FROM enrollee WHERE hospital_no = :hospital_no");
	$stmt->bindParam(':hospital_no', $_POST["edit_patient_id"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		echo json_encode([]);
	}
}

if (isset($_POST["edit_stock_id"])) {
	$stmt = $db->prepare("SELECT * FROM stock_table WHERE sn = :sn");
	$stmt->bindParam(':sn', $_POST["edit_stock_id"]);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		echo json_encode($row);
	} else {
		echo json_encode([]);
	}
}
?>