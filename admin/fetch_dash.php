<?php
session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");


if (isset($_POST["admin_settings_id"])) {

	$stmt_d = $db->query("SELECT * FROM hospital_details");
	$rwxx = $stmt_d->fetch(PDO::FETCH_ASSOC); 
	
	$llm_config = isset($rwxx['llm_config']) ? json_decode($rwxx['llm_config'], true) : [];
	if (!is_array($llm_config)) $llm_config = [];
	$llm_enabled = isset($llm_config['enabled']) && $llm_config['enabled'] ? 'checked' : '';
	$llm_voice = isset($llm_config['summary_voice_enabled']) && $llm_config['summary_voice_enabled'] ? 'checked' : '';
	$llm_provider = isset($llm_config['active_provider']) ? $llm_config['active_provider'] : '';
	$llm_model = isset($llm_config['active_model']) ? $llm_config['active_model'] : '';
	$openai_key = isset($llm_config['providers']['openai']['api_key']) ? $llm_config['providers']['openai']['api_key'] : '';
	$anthropic_key = isset($llm_config['providers']['anthropic']['api_key']) ? $llm_config['providers']['anthropic']['api_key'] : '';
	$gemini_key = isset($llm_config['providers']['gemini']['api_key']) ? $llm_config['providers']['gemini']['api_key'] : '';
	$prompt_summary = isset($llm_config['system_prompts']['patient_summary']) ? $llm_config['system_prompts']['patient_summary'] : '';
	$prompt_polish = isset($llm_config['system_prompts']['polish_note']) ? $llm_config['system_prompts']['polish_note'] : '';
	?>

	<div class="modal-body">
		<form method="post" action="index.php">


			<div class="form_sep">
				<label for="reg_input_no" class="">drug_reversal_period (days)</label>
				<input type="number" id="drug_reversal_period" name="drug_reversal_period" class="form-control" value="<?= $rwxx['drug_reversal_period']; ?>" required>
			</div>

			<div class="form_sep">
				<label for="reg_input_no" class="">phones</label>
				<input type="text" id="phones" name="phones" class="form-control" value="<?= $rwxx['phones']; ?>" required>
			</div>

			<div class="form_sep">
				<label for="reg_input_no" class="">investigation_edit_period(days)</label>
				<input type="number" id="investigation_edit_period" name="investigation_edit_period" class="form-control" value="<?= $rwxx['investigation_edit_period']; ?>" required>
			</div>

			<div class="form_sep">
				<label for="reg_textarea_message" class="">billing_remarks for patient on admission</label>
				<textarea name="billing_remarks" id="" cols="30" rows="4" class="form-control" data-required="true" data-minlength="15"><?php echo $rwxx['billing_remarks'] ?></textarea>
			</div>


			<div class="form_sep">
				<label for="" class="">Total Member Allowed to Family Folder (Family Card Registration)</label>
				<input type="number" name="total_family_member_allow" class="form-control" data-required="true" value="<?php echo $rwxx['total_family_member_allow'] ?>">
			</div>


			<div class="form-group">
				<label for="reg_select">Enable Pay from Wallet for Units</label>
				<select name="payfrom_status_" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['payfrom_status'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['payfrom_status'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>

			<div class="form_sep">
				<label for="" class="">Logo Size (WIDTH/HEIGHT pixels)</label>
				<input type="number" name="wx" class="form-control" data-required="true" value="<?php echo $rwxx['wx'] ?>">
				<input type="number" name="hx" class="form-control" data-required="true" value="<?php echo $rwxx['hx'] ?>">
			</div>

			<div class="form-group">
				<label for="reg_select">Enable DAILYSIS module</label>
				<select name="dialysis" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['dialysis'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['dialysis'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>


			<div class="form-group">
				<label for="reg_select">Enable IVF module</label>
				<select name="ivf" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['ivf'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['ivf'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>



			<div class="form-group">
				<label for="reg_select">Enable allow part pay medical service</label>
				<select name="allow_part_pay_medical_service" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['allow_part_pay_medical_service'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['allow_part_pay_medical_service'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>
			<div class="form-group">
				<label for="reg_select">Enable ADMINSTRATOR to Approve Stock Requisition</label>
				<select name="b4_approve_requisition_setup" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['b4_approve_requisition_setup'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['b4_approve_requisition_setup'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>
			<div class="form-group">
				<label for="reg_select">Enable pharmacy to REQUEST from store</label>
				<select name="pharm_request_from_store" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['pharm_request_from_store'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['pharm_request_from_store'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>

			<div class="form-group">
				<label for="reg_select">Enable WEBMEDIC FLASH</label>
				<select name="col3" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['col3'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['col3'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>

			<div class="form_sep">
				<label for="" class="">General Credit Limit for All Patients</label>
				<input type="text" name="credit_limit_status" class="form-control" data-required="true" value="<?php echo $rwxx['credit_limit_status'] ?>">
			</div>
			<div class="form-group">
				<label for="reg_select">Enable investigation should display based on staff department</label>
				<select name="col4" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['col4'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['col4'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>


			<div class="form-group">
				<label for="reg_select">Enable HMO DRUG VALIDATION STATUS SET YES MEANS PHARMACY TO VALIDATE NOT HMO DESKOFFICES</label>
				<select name="col5" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['col5'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['col5'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>

			<div class="form_sep">
				<label for="" class="">smtp_host</label>
				<input type="text" name="smtp_host" class="form-control" data-required="true" value="<?php echo $rwxx['smtp_host'] ?>">
			</div>



			<div class="form_sep">
				<label for="" class="">smtp_username</label>
				<input type="text" name="smtp_username" class="form-control" data-required="true" value="<?php echo $rwxx['smtp_username'] ?>">
			</div>
			<div class="form_sep">
				<label for="" class="">smtp_password</label>
				<input type="text" name="smtp_password" class="form-control" data-required="true" value="<?php echo $rwxx['smtp_password'] ?>">
			</div>
			<div class="form_sep">
				<label for="" class="">smtp_port</label>
				<input type="text" name="smtp_port" class="form-control" data-required="true" value="<?php echo $rwxx['smtp_port'] ?>">
			</div>
			<div class="form_sep">
				<label for="" class="">smtp_encryption</label>
				<input type="text" name="smtp_encryption" class="form-control" data-required="true" value="<?php echo $rwxx['smtp_encryption'] ?>">
			</div>
			<div class="form_sep">
				<label for="" class="">color_code_hex</label>
				<input type="text" name="color_code_hex" class="form-control" data-required="true" value="<?php echo $rwxx['color_code_hex'] ?>">
			</div>
			<div class="form-group">
				<label for="reg_select">Enable use_simple_prescription for doctor</label>
				<select name="use_simple_presc" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['use_simple_presc'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['use_simple_presc'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>
			<div class="form-group">
				<label for="reg_select">Enable notify_pharm alerts</label>
				<select name="notify_pharm" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['notify_pharm'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['notify_pharm'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>
			<div class="form-group">
				<label for="reg_select">Enable notify_lab alerts</label>
				<select name="notify_lab" id="" class="form-control" require>
					<option selected value="">Select</option>

					<option value="1" <?php echo ($rwxx['notify_lab'] == '1') ? 'selected' : '';  ?>>Yes</option>
					<option value="0" <?php echo ($rwxx['notify_lab'] == '0') ? 'selected' : '';  ?>>No</option>
				</select>
			</div>


			<div class="form_sep">
				<label for="" class="">slider text1</label>
				<textarea name="slider_text1" class="form-control" data-required="true"><?php echo $rwxx['slider_text1'] ?></textarea>
			</div>

			<div class="form_sep">
				<label for="" class="">slider text2</label>
				<textarea name="slider_text2" class="form-control" data-required="true"><?php echo $rwxx['slider_text2'] ?></textarea>
			</div>

			<hr>
			<h3 style="color:#2c3e50;"><i class="fa fa-robot"></i> AI / LLM Configuration</h3>
			<div style="background:#f8f9fa; padding:15px; border-radius:8px; border:1px solid #ddd; margin-bottom:20px;">
				<div class="form-group">
					<label><input type="checkbox" name="llm_config[enabled]" <?= $llm_enabled ?>> Enable AI Features (Dictation, Polishing, Summary)</label>
				</div>
				<div class="form-group">
					<label><input type="checkbox" name="llm_config[summary_voice_enabled]" <?= $llm_voice ?>> Enable Text-to-Speech (Voice Synthesizer)</label>
				</div>
				<div class="form-group">
					<label>Active LLM Provider</label>
					<select name="llm_config[active_provider]" class="form-control">
						<option value="">Select Provider...</option>
						<option value="gemini" <?= $llm_provider == 'gemini' ? 'selected' : '' ?>>Google (Gemini)</option>
						<option value="openai" <?= $llm_provider == 'openai' ? 'selected' : '' ?>>OpenAI (GPT)</option>
						<option value="anthropic" <?= $llm_provider == 'anthropic' ? 'selected' : '' ?>>Anthropic (Claude)</option>
					</select>
				</div>
				<div class="form-group">
					<label>Active Model Name (e.g. gemini-2.5-flash, gpt-4o-mini)</label>
					<input type="text" name="llm_config[active_model]" class="form-control" value="<?= htmlspecialchars($llm_model) ?>" placeholder="Leave blank for provider default">
				</div>
				
				<hr>
				<h4>Provider API Keys</h4>
				<div class="form-group">
					<label>Google Gemini API Key</label>
					<input type="password" name="llm_config[providers][gemini][api_key]" class="form-control" placeholder="<?= !empty($gemini_key) ? '******** (configured)' : 'Enter API Key' ?>">
				</div>
				<div class="form-group">
					<label>OpenAI API Key</label>
					<input type="password" name="llm_config[providers][openai][api_key]" class="form-control" placeholder="<?= !empty($openai_key) ? '******** (configured)' : 'Enter API Key' ?>">
				</div>
				<div class="form-group">
					<label>Anthropic API Key</label>
					<input type="password" name="llm_config[providers][anthropic][api_key]" class="form-control" placeholder="<?= !empty($anthropic_key) ? '******** (configured)' : 'Enter API Key' ?>">
				</div>

				<hr>
				<h4>Custom System Prompts (Optional)</h4>
				<div class="form-group">
					<label>Patient Summary Prompt</label>
					<textarea name="llm_config[system_prompts][patient_summary]" class="form-control" rows="3" placeholder="Default prompt will be used if empty..."><?= htmlspecialchars($prompt_summary) ?></textarea>
				</div>
				<div class="form-group">
					<label>Note Polishing Prompt</label>
					<textarea name="llm_config[system_prompts][polish_note]" class="form-control" rows="3" placeholder="Default prompt will be used if empty..."><?= htmlspecialchars($prompt_polish) ?></textarea>
				</div>
			</div>


			<div class="form_sep">
				<button class="btn btn-success btn-sm" type="submit" name="save_setting">Submit</button>

			</div>




		</form>


		<hr>




		<!DOCTYPE html>
		<html>

		<head>
			<title>Header Upload</title>
		</head>

		<body>
			<h2>Upload Header Images (1920x500 JPG only)</h2>

			<form method="post" enctype="multipart/form-data" action="index.php">
				<input type="hidden" name="upload_type" value="header_one">
				<label>Upload Header One:</label>
				<input type="file" name="image" accept=".jpg,.jpeg" required>
				<button type="submit">Upload Header One</button>
			</form>

			<br>

			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="upload_type" value="header_two">
				<label>Upload Header Two:</label>
				<input type="file" name="image" accept=".jpg,.jpeg" required>
				<button type="submit">Upload Header Two</button>
			</form>
		</body>

		</html>
	</div>
<?php }

if (isset($_POST["bill_account_bene_id"])) {
	$ECode_logged = $_POST["bill_account_bene_id"];

	$acct_b_table = '';
	$n = 1;
	$t_pay = 0;

	$stmt_d = $db->query("SELECT distinct hospital_no FROM patient_ap_services WHERE acct_billed_staff='$ECode_logged' and cr='2' and acct_billed_ack=0");
	if ($stmt_d->rowCount() > 0) {
		while ($rwxx = $stmt_d->fetch(PDO::FETCH_ASSOC)) {
			$hospital_no = $rwxx['hospital_no'];

			$stmt_profile = $db->query("SELECT surname,fname,oname,phone FROM enrollee WHERE hospital_no='$hospital_no'");
			$rwxxc = $stmt_profile->fetch(PDO::FETCH_ASSOC);
			$phone = '<u>' . $rwxxc['phone'] . '</u>';
			$fullname = $rwxxc['fname'] . ' ' . $rwxxc['oname'] . ' ' . $rwxxc['surname'];


			$acct_b_table .= "
					<tr>
					<td></td>
					<td>Details</td>
					<td colspan='3'><h3>$hospital_no / $fullname / $phone</h3></td>
					</tr>";

			$stmt = $db->query("SELECT 
		pay,
		item_services,
		hospital_no,
		created_by,
		transact_date,
		sn
		FROM patient_ap_services
		WHERE acct_billed_staff='$ECode_logged' and hospital_no='$hospital_no' and cr='2' and acct_billed_ack=0");
			if ($stmt->rowCount() > 0) {
				$t_pay_sub = 0;
				while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$t_pay = $t_pay + $rwx['pay'];
					$t_pay_sub = $t_pay_sub + $rwx['pay'];
					$item_services = $rwx['item_services'];
					$hospital_no = $rwx['hospital_no'];
					$pay = number_format($rwx['pay']);
					$created_by = $rwx['created_by'];
					$transact_date = $rwx['transact_date'];
					$transact_date = date('d M,y H:i:s a', strtotime("$transact_date"));
					$sn = $rwx['sn'];



					$acct_b_table .= "
						<tr>
						<td><input type='checkbox' checked value=" . $sn . " name='item[]' style='height: 18px; width: 18px;'/></td>
						<td>$item_services</td>
						<td>$pay</td>
						<td>$transact_date</td>
						<td>$created_by</td>
						</tr>";
				}
				$t_pay_sub = '<strong>&#8358;' . number_format($t_pay_sub) . '</strong>';
				$acct_b_table .= "
					<tr>
					<td></td>
					<td></td>
					<td></td>
					<td>Sub - Total: </td>
					<td>$t_pay_sub</td>
					</tr>";
			}
		}
	}	?>


	<form method="post" action="index.php">

		<div id="blink" align="center">
			<h2 style="color:#F00">Patient(s) and Services billed to your account</h2>
		</div>
		<hr>

		<table class="table table-striped table-bordered" style="font-size: 18px; ">
			<thead>
				<tr>
					<th>#</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $acct_b_table; ?>
			</tbody>
		</table>
		<h1>Grand Total Amount Billed: / <?php echo '&#8358;' . number_format($t_pay); ?></h1>

		<hr>
		<div class="form_sep">
			<label for="reg_select" class="req" style="font-size: 18px; color: red; ">Select Action (APPROVE OR DECLINE) </label>
			<select name="select_action" id="select_action" class="form-control" style="font-size: 18px; " required>

				<option selected="selected" value="">Select...</option>
				<option value="1">Approve</option>
				<option value="0">Decline</option>
			</select>
		</div>
		<br>

		<table width="100%">
			<tr>
				<td>
					<button class="btn btn-success btn-lg" type="submit" name="billed_acct" id="billed_acct" onclick="return confirm('Are you sure you want to submit this action?');">Submit</button>
				</td>
				<td>
					<div align="right"><button type="button" data-dismiss="modal" class="btn btn-danger btn-lg" aria-hidden="true">Close</button></div>
				</td>
			</tr>
		</table>

	</form>

<?php }

if (isset($_POST["admitted_patient_outstanding"])) {

	$stmt_main = $db->query("
        SELECT a.hospital_no, a.dept_id, d.min_amount_adm, d.require_amount_b4_adm
        FROM admission a
        LEFT JOIN department d ON a.dept_id = d.sn 
            AND d.require_amount_b4_adm > 1 
            AND d.min_amount_adm > 0
        WHERE a.adm_status = '3'
        ORDER BY a.date_admit DESC");

	$cr_table = '';
	$all_patient_cr_count = 0;
	$All_patient_cr = 0;
	$show_status = ''; // You can adjust this or build a status string if needed

	if ($stmt_main->rowCount() > 0) {
		$general_credit_limit = $_SESSION['credit_limit_status'];

		while ($row2 = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
			$hos_no = $row2['hospital_no'];

			// Call balance function
			$items = call_current_balance($db, $hos_no, $general_credit_limit);
			$current_balance = $items['current_balance'];
			$working_current_bal = $items['current_balance'];
			$patient_name = $items['patient_name'];
			$Total_total_credits = $items['Total_total_credits'];

			$admitted_dept = $items['admitted_dept'];
			$working_current_bal -= $Total_total_credits;

			// Pre-calculate required deposit
			if (!empty($row2['min_amount_adm']) && !empty($row2['require_amount_b4_adm'])) {
				$require_amount_b4_adm = ($row2['require_amount_b4_adm'] / 100) * $row2['min_amount_adm'];
			} else {
				$require_amount_b4_adm = 0;
			}

			if ($working_current_bal < 0) {
				$Outstanding = abs($working_current_bal);
				$Required_now = $Total_total_credits + $require_amount_b4_adm + abs($current_balance);

				$cr_table .= "<tr>
                    <td>{$hos_no}</td>
                    <td>{$patient_name}</td>
                    <td>" . number_format($current_balance, 2) . "</td>
                    <td style='color:red'>" . number_format($Outstanding, 2) . "</td>
                    <td style='color:red'>" . number_format($Required_now, 2) . "</td>
                    <td style='color:red'>URGENT PAYMENT</td>
                </tr>";

				$all_patient_cr_count++;
				$All_patient_cr += $Required_now;
			} elseif ($working_current_bal > 0 && $working_current_bal < $require_amount_b4_adm) {
				$diff_to_deposite = $require_amount_b4_adm - $working_current_bal;
				$Outstanding = abs($working_current_bal);
				$Required_now = $Total_total_credits + $diff_to_deposite;

				$cr_table .= "<tr>
                    <td>{$hos_no}</td>
                    <td>{$patient_name}</td>
                    <td>" . number_format($current_balance, 2) . "</td>
                    <td style='color:red'>" . number_format($Outstanding, 2) . "</td>
                    <td style='color:red'>" . number_format($Required_now, 2) . "</td>
                    <td style='color:red'>MINIMUM PAYMENT</td>
                </tr>";

				$all_patient_cr_count++;
				$All_patient_cr += $Required_now;
			}
		}
	}

	// Add summary rows (optional)
	$cr_table .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
	$cr_table .= "<tr><td>&nbsp;</td><td>Total ($all_patient_cr_count)</td><td>&nbsp;</td><td>&nbsp;</td><td>" . number_format($All_patient_cr, 2) . "</td><td>&nbsp;</td></tr>";

	echo json_encode([
		'All_patient_cr' => number_format($All_patient_cr, 2),
		'All_patient_cr2' => $All_patient_cr,
		'all_patient_cr_count' => $all_patient_cr_count,
		'cr_table' => $cr_table,
		'show_status' => $show_status
	]);
	exit;
}

if (isset($_POST["sn_accept"])) {

	$sn_accept = $_POST["sn_accept"];
	$fullname  = $_POST["fullname"];

	$remark = "<strong>Acknowledged by:</strong> " . $fullname;
	$ack = 1;

	if ($sn_accept == 1) {

		// Get all unacknowledged reminders
		$stmt = $db->prepare("SELECT sn, notes FROM notes WHERE ack=0 AND notes_type='REQ_REMINDER'");
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($rows) {

			foreach ($rows as $row) {

				$remark2 = $row['notes'] . '<br>' . $remark;

				$update = $db->prepare("
                    UPDATE notes 
                    SET notes = :notes, ack = :ack
                    WHERE sn = :sn
                ");

				$update->execute(array(
					':notes' => $remark2,
					':ack'   => $ack,
					':sn'    => $row['sn']
				));
			}

			echo 'Accepted all pending reminders';
		} else {
			echo 'No pending reminders';
		}
	} else {

		// Fetch the selected note
		$stmt = $db->prepare("SELECT notes FROM notes WHERE sn = :sn");
		$stmt->execute(array(':sn' => $sn_accept));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($row) {

			$remark2 = $row['notes'] . '<br>' . $remark;

			$update = $db->prepare("
                UPDATE notes 
                SET notes = :notes, ack = :ack
                WHERE sn = :sn
            ");

			$update->execute(array(
				':notes' => $remark2,
				':ack'   => $ack,
				':sn'    => $sn_accept
			));

			if ($update->rowCount() > 0) {
				echo 'Accepted';
			} else {
				echo 'Error Occurred';
			}
		} else {
			echo 'Record not found';
		}
	}
}


if (isset($_POST["view_birthday_request_id"])) { ?>
	<h2>BIRTHDAY NOTIFICATION</h2>
	<button onclick="copyToClipboard()" class="btn btn-success btn-sm">Copy Phone Numbers</button>
	<hr>

	<table class="table table-striped table-bordered table-hover dataTables-example">
		<thead>
			<tr>
				<th>#</th>
				<th>Hospital Number</th>
				<th>Name</th>
				<th>DOB</th>
				<th>Phone</th>
				<th>Email</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$currentDate = date('m-d');

			$stmt = $db->prepare("
                SELECT hospital_no, surname, fname, oname, dob, phone, email
                FROM enrollee
                WHERE MONTH(dob) = :m AND DAY(dob) = :d
            ");
			$stmt->execute([
				':m' => date('m'),
				':d' => date('d')
			]);

			$n = 1;
			$phoneNumbersArr = [];

			while ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$phoneNumbersArr[] = $rw['phone'];
			?>
				<tr>
					<td><?= $n++ ?></td>
					<td><?= htmlspecialchars($rw['hospital_no']) ?></td>
					<td><?= htmlspecialchars($rw['surname'] . ' ' . $rw['fname']) ?></td>
					<td><?= date('d M, Y', strtotime($rw['dob'])) ?></td>
					<td><?= htmlspecialchars($rw['phone']) ?></td>
					<td><?= htmlspecialchars($rw['email']) ?></td>
				</tr>
			<?php
			}

			$phoneNumbers = implode(',', $phoneNumbersArr);
			?>
		</tbody>
	</table>

	<script>
		function copyToClipboard() {
			var phoneNumbers = <?= json_encode($phoneNumbers) ?>;
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(phoneNumbers).then(function() {
					alert("Phone numbers copied to clipboard!");
				}, function(err) {
					alert("Failed to copy: " + err);
				});
			} else {
				// Fallback for older browsers
				var temp = document.createElement("textarea");
				temp.value = phoneNumbers;
				document.body.appendChild(temp);
				temp.select();
				document.execCommand("copy");
				document.body.removeChild(temp);
				alert("Phone numbers copied to clipboard!");
			}
		}
	</script>
<?php
}
?>


<?php

if (isset($_POST["view_specialist_request_id"])) {

	$stmt = $db->prepare("SELECT n.*, e.surname, e.fname FROM notes n 
            INNER JOIN enrollee e ON e.hospital_no = n.hospital_no where n.status='1' AND ack=0 AND notes_type='REQ_REMINDER'");
	$stmt->execute();
	$n = 1;
	if ($stmt->rowCount() > 0) { ?>

		<input type="button" name="" value="Acknowledge All Specialist Requests" onclick="accept_req(1,'<?php echo $_SESSION['fullname']; ?>')" class="btn btn-danger" />

		<h2>Specialist Requests</h2>
		<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 16px;">
			<thead>
				<tr>
					<th><strong>#</strong></th>
					<th><strong>Patient</strong></th>
					<th><strong>Notes</strong></th>
					<th><strong>Requested by </strong></th>
					<th><strong>Date</strong></th>
					<th><strong>Accept</strong></th>
				</tr>
			</thead>
			<tbody>
				<?php while ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
					<tr>
						<td><?php echo $n++; //['reason_adm']; 
							?></td>
						<td><?php echo $rw['fname'] . ' ' . $rw['surname'] . '(' . $rw['hospital_no'] . ')';  ?></td>
						<td><?php echo $rw['notes']; ?></td>
						<td><?php echo $rw['prepared_by']; ?></td>
						<td><?php echo  date("d M,y H:i:s", strtotime($rw['date_entry'])); ?></td>
						<td><a href="?ptm=all/<?php echo $rw['hospital_no']; ?>" class="btn btn-success btn-xs">Book</a></td>
						<td> <input type="button" name="eidt_gd" value="Acknowledge" class="btn btn-primary btn-xs" onClick="accept_req('<?php echo $rw['sn']; ?>','<?php echo $_SESSION['fullname']; ?>')" /></td>
					</tr>
				<?php  } ?>
			</tbody>
		</table>

	<?php } ?>



	<?php
	/////include("../doctor/bookings_by_doctor.php");


	$stmt = $db->prepare("SELECT f.*, e.surname, e.fname 
                      FROM apptm_fellowup f 
                      INNER JOIN enrollee e ON e.hospital_no = f.hospital_no 
                      WHERE f.date_time_stamp BETWEEN NOW() - INTERVAL 48 HOUR AND NOW() 
                      ORDER BY f.sn DESC");


	$stmt->execute();
	$n = 1;
	if ($stmt->rowCount() > 0) { ?>

		<input type="button" name="" value="Acknowledge All" onclick="Accknl_booking(1)" class="btn btn-danger" />

		<h2>Other Requests</h2>
		<table class="table table-bordered" style="font-size: 16px;">
			<thead>
				<tr>
					<th><strong>#</strong></th>
					<th width="">Date</th>
					<?php if ($interfc == 1 && $interfaccc == "front-desk") { ?><th>Name</th><?php } ?>
					<th width="">Patient Name</th>
					<th width="">Notes</th>
					<th width="">Noted By</th>
					<?php if ($interfc == 1 && $interfaccc == "front-desk") { ?><th>Book</th><?php } ?>
					<th width=""></th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$sn = $row['sn'];
				?>
					<tr>
						<td><?= $n++; ?></td>
						<td><?php echo date('d M,Y', strtotime($row['date_time'])) . '<br>' . date('h:i a', strtotime($row['date_time'])); ?></td>
						<td><?php echo $row['fname'] . ' ' . $row['surname']; ?></td>
						<td><?php echo '<b>' . strtoupper($row['service_type']) . '</b>' . '<br>' . $row['request_note_description']; ?> </td>
						<td><?php echo $row['doctor_name']; ?></td>
						<td><a href="?ptm=all/<?php echo $row['hospital_no']; ?>" class="btn btn-success btn-xs">Book</a></td>

						<td><?php if ($row['status'] == 1) {
								echo '<b>Acknowledged</b>';
							} elseif ($row['status'] == 0) {
							?>

								<input type="button" name="" value="Acknowledge" onclick="Accknl_booking('<?= $sn; ?>')"
									class="btn btn-warning btn-xs" />
							<?php } ?>
						</td>
					</tr>
				<?php     } ?>

			</tbody>
		</table>

	<?php } ?>

<?php
}
?>