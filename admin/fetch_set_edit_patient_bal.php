<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

if (isset($_POST["edit_update_price"])) {



	$hospital_no = $_POST["emr"];
	$balance = $_POST["balance"];
	$sn = $_POST["sn"];
	$current_balance = $_POST["current_balance"];

	/*	$updatttt = $db->prepare("UPDATE patient_billing SET bal = '$balance' WHERE sn = ? AND hospital_no = ? ");
    $deleted = $updatttt->execute(array($sn, $hospital_no));
	
			$desc='Edit Balance'. 'Old Bal.: ' . $current_balance .'New Bal' . $balance;
				$staff=$_SESSION['fullname'];
	$pid=$$hospital_no;
	$pname=''; $action='Patient Wallet';
include_once("../logs.php"); */
}





?>





<div class="modal inmodal fade" id="write_off_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Write Off</h4>
			</div>
			<div class="modal-body" id="write_off_body">
			</div>

		</div>
	</div>
</div>

<?php


if (isset($_POST["editprice_id"])) {

	$emr = $_POST["editprice_id"];

	$stmt_en = $db->query("SELECT 
		i.insurance_name,
		i.interest,
		i.insurance_type,
		i.insurance_no,
		i.add_minus,
		e.surname,
		e.fname,
		e.oname,
		e.gender,
		e.addr,
		e.discount_set, 
		e.phone, 
		e.email 
		FROM enrollee as e 
		INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no 
		WHERE hospital_no='$emr' and status='active'");
	if ($stmt_en->rowCount() > 0) {

		$row = $stmt_en->fetch(PDO::FETCH_ASSOC);
		$insurance_name = $row['insurance_name'];
		$insurance = $row['insurance_name'];
		$interest = $row['interest'];
		$insurance_type = $row['insurance_type'];
		$insurance_no = $row['insurance_no'];
		$add_minus = $row['add_minus'];
		$patient_name = $row['surname'] . ', ' . $row['fname'] . ' ' . $row['oname'];
		$gender = $row['gender'];
		$address = $row['addr'];
		$discount_set = $row['discount_set'];
		$phone = $row['phone'];
		$email = $row['email'];

		/// INTERNAL PATIENTS============================================================
		$patient_type = $insurance_type;
		$referral_name = ' ';
		if ($insurance_type == 'Family') {
			$stmt = $db->query("SELECT bal,sn FROM patient_billing WHERE insurance_no='$insurance_no' ORDER BY sn DESC LIMIT 1");
			$save_insurance_no = $insurance_no;
			$insur_title = $insurance_name . ' <i>[' . $insurance_type . ']</i>';
		} else {
			$stmt = $db->query("SELECT bal,sn FROM patient_billing WHERE hospital_no='$emr' ORDER BY sn DESC LIMIT 1");
			$save_insurance_no = 'private';
			$insur_title = $insurance_name;
		}

		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$current_balance = $row['bal'];
		} else {
			$current_balance = '0';
			$active_bal = 'family';
		}
	}

?>

	<form method="POST" action="patients_balance.php?deposits">



		<div class="form_sep">
			<label for="reg_input_no" class="req">Current Balance</label>
			<input type="number" step="any" id="balance" name="balance" class="form-control" value="<?php echo $current_balance; ?>" required>
		</div>



		<div class="form_sep">
			<button class="btn btn-primary btn-sm" type="submit" name="edit_update_price" id="edit_update_price">Save Changes</button>
		</div>
		<input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
		<input type="hidden" name="sn" id="sn" value="<?php echo $row['sn']; ?>" />
		<input type="hidden" name="insurance_type" id="insurance_type" value="<?php echo $insurance_type; ?>" />
		<input type="hidden" name="insurance_no" id="insurance_no" value="<?php echo $insurance_no; ?>" />
		<input type="hidden" name="current_balance" id="current_balance" value="<?php echo $current_balance; ?>" />
	</form>

<?php
}
?>