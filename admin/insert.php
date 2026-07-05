<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

date_default_timezone_set('Africa/Lagos');
$setdate = date("Y-m-d");
$setdatetime = date("Y-m-d H:i:s");

if (isset($_POST["combo_name"])) {

	$combo_name = $_POST["combo_name"];
	$stock_items = $_POST["stock_items"];
	$qty_combo = $_POST["qty_combo"];

	$stmt = $db->prepare("SELECT * FROM stock_combo WHERE combo_name=:combo_name and items=:stock_items");
	$stmt->bindParam(":combo_name", $combo_name);
	$stmt->bindParam(":stock_items", $stock_items);
	$stmt->execute();

	if ($stmt->rowCount() == 0) {
		$stmt = $db->prepare("INSERT INTO stock_combo(combo_name, items, qty) VALUES (:combo_name, :stock_items, :qty_combo)");
		$stmt->bindParam(":combo_name", $_POST['combo_name']);
		$stmt->bindParam(":stock_items", $_POST['stock_items']);
		$stmt->bindParam(":qty_combo", $qty_combo);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			echo 'Record Saved Successfully';
		} else {
			echo 'Error';
		}
	}
}


if (isset($_POST['fetch_data'])) {

	$response_main = array(
		'message' => '',
	);
	$t = 1;
	$body = '';
	$stmt_2 = $db->prepare("SELECT c.*, s.product_name FROM stock_combo c inner join stock_table s on s.sn=c.items WHERE combo_name=:upload_combo");
	$stmt_2->bindParam(':upload_combo', $_POST['fetch_data'], PDO::PARAM_STR);
	$stmt_2->execute();
	if ($stmt_2->rowCount() > 0) {

		$header = '<table class="table table-striped table-bordered"><tr>
                    <th>S/N</th>
                    <th>Item</th>
					<th>Qty</th>
                    <th>.</th>
                </tr>';

		while ($rowx = $stmt_2->fetch(PDO::FETCH_ASSOC)) {
			$body = $body . '<tr><td>' . $t++ . '</td><td>' . $rowx['product_name'] . '</td><td>' . $rowx['qty'] . '</td><td>
					<button type="submit" class="btn btn-danger btn-xs" name="del_upload" onclick="return confirm("Are you sure you want to DELETE this ITEM")" value="' . $rowx['sn'] . '">Delete</button>
					</td></tr>';
		}
		$table = $header . $body . '</table>';
		$response_main['message'] = $table;
		echo  json_encode($response_main);
	}
}

function add_me($descriptn, $descriptn_sn, $setdatetime)
{

	include("../Connections/Conn.php");
	$ECode = $_POST['ECode'];


	if ($_POST['duration2'] == '0') {
		$bal = 'always';
		$duration = '0';
	} elseif ($_POST['duration2'] > 0) {
		$bal = $_POST['duration2'];
		$duration = $_POST['duration2'];
	} else {
		$bal = '1';
		$duration = '1';
	}

	$stmt = $db->prepare("SELECT * FROM hred_income_services WHERE employee_no=:ECode and descriptn=:descriptn");
	$stmt->bindParam(":ECode", $ECode);
	$stmt->bindParam(":descriptn", $descriptn);
	$stmt->execute();

	if ($stmt->rowCount() == 0) {
		$stmt = $db->prepare("INSERT INTO hred_income_services(employee_no,id,flat_percent,flat_percent_value,duration,bal,descriptn_sn,descriptn,type,setby,set_date) VALUES (:ECode,:id,:flat_percent,:flat_percent_value,:duration,:bal,:descriptn_sn,:descriptn,:type,:setby,:set_date)");
		$stmt->bindParam(":ECode", $_POST['ECode']);
		$stmt->bindParam(":id", $_POST['id']);
		$stmt->bindParam(":flat_percent", $_POST['flat_percent2']);
		$stmt->bindParam(":flat_percent_value", $_POST['flat_percent_amt2']);
		$stmt->bindParam(":duration", $duration);
		$stmt->bindParam(":bal", $bal);
		$stmt->bindParam(":descriptn_sn", $descriptn_sn);
		$stmt->bindParam(":descriptn", $descriptn);
		$stmt->bindParam(":type", $_POST['type']);
		$stmt->bindParam(":setby", $_SESSION['fullname']);
		$stmt->bindParam(":set_date", $setdatetime);
		$stmt->execute();
	}
}




if (isset($_POST["MM_update"]) == "add_new_patient_start") {

	if (isset($_POST["PatientExist"]) and $_POST["PatientExist"] != '') {
		$PatientExist = '1';
	} else {
		$PatientExist = '0';
	}

	$setdate = date("Y-m-d H:i:s");
	$stmt = $db->query("SELECT hospital_no FROM enrollee ORDER BY hospital_no DESC LIMIT 1");
	if ($stmt->rowCount() == 0) {
		$hospital_no = '000001';
	} else {
		$rwx = $stmt->fetch(PDO::FETCH_ASSOC);
		$hospital_no = $rwx['hospital_no'] + 1;
		$hospital_no = sprintf('%006d', $hospital_no);
	}

	date_default_timezone_set('Africa/Lagos');
	$Current_date = date('Y-m-d');
	$date1 = new DateTime($Current_date);
	$date2 = new DateTime($_POST['dob']);
	$diff = $date2->diff($date1);
	$yrs = $diff->format('%y');
	$mnth = $diff->format('%m');

	if ($yrs == 0) {
		$age = $mnth . 'mths';
	} else {
		$age = $yrs . '/' . $mnth;
	}

	$surname = $_POST['surname'];
	$fname = $_POST['fname'];
	$oname = $_POST['oname'];
	$gender = $_POST['gender'];
	$dob = $_POST['dob'];
	$phoneno = $_POST['phoneno'];

	$stmt = $db->prepare("SELECT hospital_no FROM enrollee WHERE surname=:surname and fname=:fname and gender=:gender and dob=:dob");
	$stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
	$stmt->bindValue(':fname', $fname, PDO::PARAM_STR);
	$stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
	$stmt->bindValue(':dob', $dob, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() == 0 or $PatientExist == '1') {
		$token_ = md5($hospital_no);
		$hmo_no = "1000";
		$insurance = "Private(Self)";
		$visit_status = "new";

		$surname = trim($_POST['surname']);
		$surname = str_replace("'", "", $surname);
		$surname = str_replace('"', '', $surname);

		$fname = trim($_POST['fname']);
		$fname = str_replace("'", "", $fname);
		$fname = str_replace('"', '', $fname);

		$oname = trim($_POST['oname']);
		$oname = str_replace("'", "", $oname);
		$oname = str_replace('"', '', $oname);



		$checkSQL = "SELECT hospital_no FROM enrollee WHERE hospital_no = :hospital_no";
		$checkStmt = $db->prepare($checkSQL);
		$checkStmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
		$checkStmt->execute();

		if ($checkStmt->rowCount() > 0) {
			// Row(s) exist
			echo "Hospital number already exists.";
		} else {
			// Proceed with insertion ALTER TABLE `enrollee` ADD `captured_by` VARCHAR(255) NULL DEFAULT NULL AFTER `command_formation`;

			$insertSQL = "INSERT INTO enrollee 
                  (hospital_no, hmo_no, insurance, surname, fname, oname, gender, dob, age, phone, date_capture, visit_status, token, captured_by)
                  VALUES 
                  (:hospital_no, :hmo_no, :insurance, :surname, :fname, :oname, :gender, :dob, :age, :phone, :date_capture, :visit_status, :token, :captured_by)";

			// Prepare the statement
			$stmt = $db->prepare($insertSQL);

			// Bind parameters
			$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmt->bindParam(':hmo_no', $hmo_no, PDO::PARAM_STR); // Assuming $hmo_no is defined elsewhere
			$stmt->bindParam(':insurance', $insurance, PDO::PARAM_STR); // Assuming $insurance is defined elsewhere
			$stmt->bindParam(':surname', strtoupper($surname), PDO::PARAM_STR);
			$stmt->bindParam(':fname', strtoupper($fname), PDO::PARAM_STR);
			$stmt->bindParam(':oname', strtoupper($oname), PDO::PARAM_STR);
			$stmt->bindParam(':gender', $_POST['gender'], PDO::PARAM_STR); // Assuming gender is from $_POST
			$stmt->bindParam(':dob', $_POST['dob'], PDO::PARAM_STR); // Assuming dob is from $_POST
			$stmt->bindParam(':age', $age, PDO::PARAM_STR); // Assuming $age is defined elsewhere
			$stmt->bindParam(':phone', $_POST['phoneno'], PDO::PARAM_STR); // Assuming phoneno is from $_POST
			$stmt->bindParam(':date_capture', $setdate, PDO::PARAM_STR); // Assuming $setdate is defined elsewhere
			$stmt->bindParam(':visit_status', $visit_status, PDO::PARAM_STR); // Assuming $visit_status is defined elsewhere
			$stmt->bindParam(':token', $token_, PDO::PARAM_STR); // Assuming $token_ is defined elsewhere
			$stmt->bindParam(':captured_by', $_SESSION['fullname'], PDO::PARAM_STR); // Assuming $token_ is defined elsewhere

			// Execute the statement
			$stmt->execute();

			// Optionally, you can check if the execution was successful
			if ($stmt->rowCount() > 0) {

				$stmt2 = $db->query("SELECT * FROM prices_table 
	 	WHERE hosp_price>0 and ext_price>0 and item_service='New File'");
				$roww = $stmt2->fetch(PDO::FETCH_ASSOC);
				$item_sn = $roww['sn'];
				$hosp_price = $roww['hosp_price'];
				$ext_price = $roww['ext_price'];
				$item_sn = $roww['sn'];
				$zero = '0';

				$setdate = date('Y-m-d H:i:s');
				$serv_group = 'Registration';
				$invoice_no = rand(10000, 99999) . $hospital_no;
				$cat_type = 'New File';
				$item_service = 'New File';
				$pay_mode = 'cash';
				$paystatus = '0';
				$one = '1';


				$insertSQL = "INSERT INTO patient_ap_services 
	(app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, date_entry, pay, pay_mode, paystatus)
	VALUES 
	(:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, :interest, :qty, :invoice_status, :invoice_no, :invoice_date, :invoice_by, :prepared_by, :date_entry, :pay, :pay_mode, :paystatus)";

				// Prepare the statement
				$stmt = $db->prepare($insertSQL);

				// Bind parameters
				$stmt->bindParam(':app_no', $hospital_no, PDO::PARAM_STR); // Assuming $hospital_no is same as $app_no
				$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
				$stmt->bindParam(':serv_group', $serv_group, PDO::PARAM_STR);
				$stmt->bindParam(':cat_type', $cat_type, PDO::PARAM_STR);
				$stmt->bindParam(':dept_id', $_SESSION['dept_id'], PDO::PARAM_STR); // Assuming $_SESSION['dept_id'] contains dept_id
				$stmt->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
				$stmt->bindParam(':item_services', $item_service, PDO::PARAM_STR);
				$stmt->bindParam(':hosp_price', $ext_price, PDO::PARAM_STR);
				$stmt->bindParam(':claim_amt', $zero, PDO::PARAM_STR);
				$stmt->bindParam(':interest', $zero, PDO::PARAM_STR);
				$stmt->bindParam(':qty', $one, PDO::PARAM_INT); // Assuming qty is always '1'
				$stmt->bindParam(':invoice_status', $one, PDO::PARAM_INT); // Assuming invoice_status is always '1'
				$stmt->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
				$stmt->bindParam(':invoice_date', $setdate, PDO::PARAM_STR);
				$stmt->bindParam(':invoice_by', $_SESSION['fullname'], PDO::PARAM_STR); // Assuming $_SESSION['fullname'] contains invoice_by
				$stmt->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR); // Assuming $_SESSION['fullname'] contains prepared_by
				$stmt->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
				$stmt->bindParam(':pay', $ext_price, PDO::PARAM_STR); // Assuming $pay is same as $ext_price
				$stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
				$stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);

				// Execute the statement
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					echo $hospital_no;
				} else {
					$delete = $db->prepare("DELETE FROM enrollee WHERE hospital_no = '$hospital_no'");
					$deleted = $delete->execute();
					header("location:index.php?Error");
				}
			}
		}
	} else {
		echo 'PatientExist';
	}
}

?>
 
 
 