<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");
?>
<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>
<?php




if (strtoupper($_SESSION['password']) == 'STAFF123') {
	header("location:../profile/index.php?profile=$uname&changepassword");
}

$speciality_admin = $_SESSION['speciality_admin'];

////// BILLABLE ACCOUNT TO MD AND HEAD OF ORGANISATION ////============================================
///////////============================================================================================




// Path to upload folder
$uploadDir = "../index/img/";

// Ensure folder exists
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0777, true);
}

// Function to resize image
function resizeImage($source, $destination, $targetWidth = 1920, $targetHeight = 500)
{
	// Get original image size
	list($width, $height) = getimagesize($source);

	// Create source image
	$src = imagecreatefromjpeg($source);

	// Create blank true color image
	$dst = imagecreatetruecolor($targetWidth, $targetHeight);

	// Resize
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

	// Save resized image
	imagejpeg($dst, $destination, 90);

	// Free memory
	imagedestroy($src);
	imagedestroy($dst);
}

if (isset($_POST['upload_type']) && !empty($_POST['upload_type'])) {
	$fileKey = $_POST['upload_type']; // header_one or header_two

	if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
		$fileTmp = $_FILES['image']['tmp_name'];
		$fileType = mime_content_type($fileTmp);

		if ($fileType === 'image/jpeg') {
			$savePath = $uploadDir . $fileKey . ".jpg";

			// Resize & save
			resizeImage($fileTmp, $savePath);

			echo "<p style='color:green;'>Successfully uploaded and resized to $savePath.jpg!</p>";
		} else {
			echo "<p style='color:red;'>Only JPG images are allowed.</p>";
		}
	} else {
		//echo "<p style='color:red;'>Please select a file to upload.</p>";
	}
}


if (isset($_POST['save_setting'])) {
	// Example: Update hospital_details with error/success prompt


	try {
		$updateSQL = "UPDATE hospital_details SET 
        drug_reversal_period = :drug_reversal_period,
        phones = :phones,
        investigation_edit_period = :investigation_edit_period,
        billing_remarks = :billing_remarks,
        total_family_member_allow = :total_family_member_allow,
        payfrom_status = :payfrom_status_,
        wx = :wx,
        hx = :hx,
        dialysis = :dialysis,
        ivf = :ivf,
        allow_part_pay_medical_service = :allow_part_pay_medical_service,
        b4_approve_requisition_setup = :b4_approve_requisition_setup,
        pharm_request_from_store = :pharm_request_from_store,
        col3 = :col3,
        credit_limit_status = :credit_limit_status,
        col4 = :col4,
        col5 = :col5,
        smtp_host = :smtp_host,
        smtp_username = :smtp_username,
        smtp_password = :smtp_password,
        smtp_port = :smtp_port,
        smtp_encryption = :smtp_encryption,
        color_code_hex = :color_code_hex,
        use_simple_presc = :use_simple_presc,
        notify_pharm = :notify_pharm,
        notify_lab = :notify_lab,
        slider_text1 = :slider_text1,
        slider_text2 = :slider_text2,
        twilio_sid = :twilio_sid,
        twilio_auth_token = :twilio_auth_token,
        twilio_phone_number = :twilio_phone_number,
        welcome_email_template = :welcome_email_template,
        welcome_sms_template = :welcome_sms_template,
        llm_config = :llm_config
        WHERE sn = 1";

		$stmt = $db->prepare($updateSQL);

		// Bind all parameters
		$stmt->bindParam(':drug_reversal_period', $_POST['drug_reversal_period'], PDO::PARAM_STR);
		$stmt->bindParam(':phones', $_POST['phones'], PDO::PARAM_STR);
		$stmt->bindParam(':investigation_edit_period', $_POST['investigation_edit_period'], PDO::PARAM_STR);
		$stmt->bindParam(':billing_remarks', $_POST['billing_remarks'], PDO::PARAM_STR);
		$stmt->bindParam(':total_family_member_allow', $_POST['total_family_member_allow'], PDO::PARAM_STR);
		$stmt->bindParam(':payfrom_status_', $_POST['payfrom_status_'], PDO::PARAM_STR);
		$stmt->bindParam(':wx', $_POST['wx'], PDO::PARAM_INT);
		$stmt->bindParam(':hx', $_POST['hx'], PDO::PARAM_INT);
		$stmt->bindParam(':dialysis', $_POST['dialysis'], PDO::PARAM_INT);
		$stmt->bindParam(':ivf', $_POST['ivf'], PDO::PARAM_INT);
		$stmt->bindParam(':allow_part_pay_medical_service', $_POST['allow_part_pay_medical_service'], PDO::PARAM_INT);
		$stmt->bindParam(':b4_approve_requisition_setup', $_POST['b4_approve_requisition_setup'], PDO::PARAM_INT);
		$stmt->bindParam(':pharm_request_from_store', $_POST['pharm_request_from_store'], PDO::PARAM_INT);
		$stmt->bindParam(':col3', $_POST['col3'], PDO::PARAM_INT);
		$stmt->bindParam(':credit_limit_status', $_POST['credit_limit_status'], PDO::PARAM_INT);
		$stmt->bindParam(':col4', $_POST['col4'], PDO::PARAM_INT);
		$stmt->bindParam(':col5', $_POST['col5'], PDO::PARAM_INT);
		$stmt->bindParam(':smtp_host', $_POST['smtp_host'], PDO::PARAM_STR);
		$stmt->bindParam(':smtp_username', $_POST['smtp_username'], PDO::PARAM_STR);
		$stmt->bindParam(':smtp_password', $_POST['smtp_password'], PDO::PARAM_STR);
		$stmt->bindParam(':smtp_port', $_POST['smtp_port'], PDO::PARAM_STR);
		$stmt->bindParam(':smtp_encryption', $_POST['smtp_encryption'], PDO::PARAM_STR);
		$stmt->bindParam(':color_code_hex', $_POST['color_code_hex'], PDO::PARAM_STR);
		$stmt->bindParam(':use_simple_presc', $_POST['use_simple_presc'], PDO::PARAM_INT);
		$stmt->bindParam(':notify_pharm', $_POST['notify_pharm'], PDO::PARAM_INT);
		$stmt->bindParam(':notify_lab', $_POST['notify_lab'], PDO::PARAM_INT);
		$stmt->bindParam(':slider_text1', $_POST['slider_text1'], PDO::PARAM_STR);
		$stmt->bindParam(':slider_text2', $_POST['slider_text2'], PDO::PARAM_STR);
		$stmt->bindParam(':twilio_sid', $_POST['twilio_sid'], PDO::PARAM_STR);
		$stmt->bindParam(':twilio_auth_token', $_POST['twilio_auth_token'], PDO::PARAM_STR);
		$stmt->bindParam(':twilio_phone_number', $_POST['twilio_phone_number'], PDO::PARAM_STR);
		$stmt->bindParam(':welcome_email_template', $_POST['welcome_email_template'], PDO::PARAM_STR);
		$stmt->bindParam(':welcome_sms_template', $_POST['welcome_sms_template'], PDO::PARAM_STR);

		// Handle LLM Config
		$llmConfigJson = null;
		if (isset($_POST['llm_config']) && is_array($_POST['llm_config'])) {
			$llmConfig = $_POST['llm_config'];
			$llmConfig['enabled'] = isset($llmConfig['enabled']) ? true : false;
			$llmConfig['summary_voice_enabled'] = isset($llmConfig['summary_voice_enabled']) ? true : false;
			
			// Load existing config to preserve passwords if empty
			$stmt_d = $db->query("SELECT llm_config FROM hospital_details WHERE sn = 1");
			$existing = $stmt_d->fetch(PDO::FETCH_ASSOC);
			$existingConfig = !empty($existing['llm_config']) ? json_decode($existing['llm_config'], true) : [];
			if (!is_array($existingConfig)) $existingConfig = [];

			if (isset($llmConfig['providers'])) {
				foreach ($llmConfig['providers'] as $provider => $providerData) {
					if (empty($providerData['api_key'])) {
						$llmConfig['providers'][$provider]['api_key'] = isset($existingConfig['providers'][$provider]['api_key']) ? $existingConfig['providers'][$provider]['api_key'] : '';
					}
				}
			}
			$llmConfigJson = json_encode($llmConfig);
		}
		$stmt->bindParam(':llm_config', $llmConfigJson, PDO::PARAM_STR);

		// Execute
		if ($stmt->execute()) {
			echo "<div class='alert alert-success'>✅ Update successful!</div>";
		} else {
			echo "<div class='alert alert-warning'>⚠️ Update failed. Please try again.</div>";
		}
	} catch (PDOException $e) {
		echo "<div class='alert alert-danger'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
	}
}


if (isset($_POST['billed_acct'])) {
	$select_action = $_POST['select_action'];
	// 0 ack 1= decline

	if (!empty($_REQUEST['item'])) {
		$SystemSelected = $_REQUEST['item'];

		for ($i = 0; $i < count($SystemSelected); $i++) {

			$inv_id = $SystemSelected[$i];
			if ($select_action == 1) {
				$cr = '2';
				$acct_billed_ack = 1;
				$paystatus = 1;
			} else {
				$cr = '1';
				$acct_billed_ack = 0;
				$paystatus = 0;
			}
			$stmt = $db->prepare("UPDATE patient_ap_services SET cr = :cr, paystatus = :paystatus, acct_billed_ack = :acct_billed_ack WHERE sn = :sn");
			$stmt->bindParam(':cr', $cr);
			$stmt->bindParam(':paystatus', $paystatus);
			$stmt->bindParam(':acct_billed_ack', $acct_billed_ack);
			$stmt->bindParam(':sn', $inv_id);

			$stmt->execute();
		}
	}
}


///================================== END OF CODE FOR MD/ GM ===================================================================	

$responsible = $_SESSION['fullname'];
$setdate = date("Y-m-d");
$year = date("Y");
$patient_acct_status = 1;


$allowed_roles = ['MD', 'GM', 'AC', 'AD', 'AO', 'US'];
$today = date('Y-m-d'); // current date

if (
	isset($_SESSION['system_alert']) &&
	in_array($_SESSION['rights'], $allowed_roles)
) {

	// Check if already shown today
	if (!isset($_SESSION['alert_last_shown']) || $_SESSION['alert_last_shown'] != $today) {

		$alert_message = $_SESSION['system_alert'];

		// mark as shown today
		$_SESSION['alert_last_shown'] = $today;
?>

		<!-- MODAL -->
		<div id="systemModal" style="
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.6);
            display:flex;
            align-items:center;
            justify-content:center;
            z-index:9999;
        ">
			<div style="
                background:#fff;
                padding:20px;
                border-radius:8px;
                width:400px;
                text-align:center;
                box-shadow:0 0 10px #000;
            ">
				<h3 style="color:red;">System Notification</h3>
				<p><?= htmlspecialchars($alert_message) ?></p>
				<button onclick="document.getElementById('systemModal').style.display='none'"
					style="padding:8px 15px; background:#007bff; color:#fff; border:none;">
					OK
				</button>
			</div>
		</div>

<?php
	}
}
?>


<body class="fixed-navigation">
	<div id="wrapper">

		<?php

		$shw_side = '';

		if (isset($_GET['hr']) or isset($_GET['Cadre']) or isset($_GET['Designation']) or isset($_GET['Department']) or isset($_GET['Bank']) or isset($_GET['Add']) or isset($_GET['ED']) or isset($_GET['sal']) or isset($_GET['LV']) or isset($_GET['yLV']) or isset($_GET['rLV']) or isset($_GET['gpay']) or isset($_GET['srp']) or isset($_GET['slp']) or isset($_GET['eval']) or isset($_GET['evalCat']) or isset($_GET['rEval']) or isset($_GET['evalPeriod'])) {
			include("../inc/payroll_side_bar.php");
		} elseif (isset($_GET['price'])) {
			include("../inc/price_side_bar.php");
		} elseif (isset($_GET['doc']) or isset($_GET['dsry']) or isset($_GET['vts'])) {
			include("../inc/doc_side_bar.php");
		} elseif (isset($_GET['ptm'])) {
			include("../inc/patient_master_sidebar.php");
		} elseif (isset($_GET['rit']) or isset($_GET['min']) or isset($_GET['bill']) or isset($_GET['rdc']) or isset($_GET['phm'])) {
			include("../inc/rights_side_bar.php");
		} elseif (isset($_GET['transc']) or isset($_GET['patients_balance']) or isset($_GET['datab']) or isset($_GET['sta']) or isset($_GET['report'])) {
			///include("../inc/report_side_bar.php");
			if ($rights == 'PH') {
				include("../pharmacy/nav_side.php");
			} elseif ($rights == 'NS') {
				include("../nursing/nav_side.php");
			} else {
				include("../inc/report_side_bar.php");
			}
		} elseif ($rights == 'PH') {
			include("../pharmacy/nav_side.php");
		} elseif ($rights == 'LB') {
			include("../inc/nav_side.php");
		} elseif ($rights == 'NS') {
			include("../nursing/nav_side.php");
		} elseif (isset($_GET['stock'])) {
			include("../inc/stock_side_bar.php");
		} else {
			include("../inc/nav_admin_side_bar.php");
			$shw_side = 'yes';
		}

		?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include("../inc/nav_header.php"); ?>
			<?php if ($_SESSION['enquiry'] == 1) {
				include("../inc/billing_side_bar.php");
			}


			?>

			<div class="wrapper wrapper-content">

				<?php
				if (isset($_POST['show_claims_change'])) {
					$hospital_emr_numb = $_POST['hospital_emr_numb'];
					$start = $_POST['start'];
					$end = $_POST['end'];
					$date_range = $start . '/' . $end;
					header("location:index.php?claims=$hospital_emr_numb&date_range=$date_range");
				} elseif (isset($_GET['hr'])) {
					include("emplist.php");
				} elseif (isset($_GET['Cadre']) or isset($_GET['Designation']) or isset($_GET['Department']) or isset($_GET['Bank'])) {
					include("empset.php");
				} elseif (isset($_GET['Specialist'])) {
					include("specialist.php");
				} elseif (isset($_GET['Add'])) {
					include("empadd.php");
				} elseif (isset($_GET['updown'])) {
					include("upload_download.php");
				} elseif (isset($_GET['ED'])) {
					include("empddt.php");
				} elseif (isset($_GET['LV'])) {
					include("leave.php");
				} elseif (isset($_GET['yLV'])) {
					include("apLeav.php");
				} elseif (isset($_GET['rLV'])) {
					include("rptLV.php");
				} elseif (isset($_GET['sal'])) {
					include("salary.php");
				} elseif (isset($_GET['lip'])) {
					include("payslip.php");
				} elseif (isset($_GET['gpay'])) {
					include("genPayslip.php");
				} elseif (isset($_GET['srp'])) {
					include("sal_rpt.php");
				} elseif (isset($_GET['slp'])) {
					include("emppayslip.php");
				} elseif (isset($_GET['price'])) {
					include("hosp_price.php");
				} elseif (isset($_GET['bed'])) {
					include("bed.php");
				} elseif (isset($_GET['bed_enq'])) {
					include("bed_enq.php");
				} elseif (isset($_GET['doc'])) {
					include("doc_incom.php");
				} elseif (isset($_GET['dsry'])) {
					include("smry.php");
				} elseif (isset($_GET['edit_price'])) {
					include("edit_price.php");
				} elseif (isset($_GET['vts'])) {
					include("doc_actv.php");
				} elseif (isset($_GET['ptm'])) {
					include("patient_masters.php");
				} elseif (isset($_GET['med'])) {
					include("medical_report.php");
				} elseif (isset($_GET['tRpt'])) {
					include("tRpt.php");
				} elseif (isset($_GET['cr'])) {
					include("adm_credit.php");
				} elseif (isset($_GET['rit'])) {
					include("all_rights.php");
				} elseif (isset($_GET['rdc'])) {
					include("rights.php");
				} elseif (isset($_GET['bill'])) {
					include("biller_rights.php");
				} elseif (isset($_GET['min'])) {
					include("admin_rights.php");
				} elseif (isset($_GET['med_rights'])) {
					include("med_rights.php");
				} elseif (isset($_GET['phm'])) {
					include("pharm_rights.php");
				} elseif (isset($_GET['right_report'])) {
					include("rights_report.php");
				} elseif (isset($_GET['sale'])) {
					include("sale_mgr.php");
				} elseif (isset($_POST['prt_claim_selected'])) {
					include("claims_printout_selected.php");
				} elseif (isset($_GET['claims']) or isset($_GET['view_claims'])) {

					include("claims_setup.php");
				} elseif (isset($_GET['printout'])) {
					include("claims_printout.php");
				} elseif (isset($_GET['cptclaim'])) {
					include("cptclaim.php");
				} elseif (isset($_GET['equiry'])) {
					include("enquiry.php");
				} elseif (isset($_GET['report'])) {
					include("rpt_main.php");
				} elseif (isset($_GET['transc'])) {
					include("transc.php");
				} elseif (isset($_GET['stock'])) {
					include("stock_mgr.php");
				} elseif (isset($_GET['today'])) {
					include("today_sales.php");
				} elseif (isset($_GET['pend'])) {
					include("pendin.php");
				} elseif (isset($_GET['datab'])) {
					include("datab.php");
				} elseif (isset($_GET['temp'])) {
					include("templates.php");
				} elseif (isset($_GET['patients_bal'])) {
					include("patients_balance.php");
				} elseif (isset($_GET['enq'])) {
					include("price_enq.php");
				} elseif ($speciality_admin == 'Administrator' or $speciality_admin == 'Receptionist' or $rights == 'NS' or $rights == 'CA') {


				?>



					<?php if ($_SESSION['add_new_patient'] == 1) { ?>

						<div class="row">
							<div class="col-md-3">
								<div class="ibox float-e-margins">
									<div class="ibox-title">
										<span class="label label-success pull-right">Today</span>
										<h5>Queue List</h5>
									</div>
									<div class="ibox-content">
										<table width="100%">
											<tr>
												<td width="50%">
													<h1 class="no-margins" id="queue_">0</h1>
													<small>Queue</small>
												</td>
												<?php if ($_SESSION['dialysis_visible'] == 1) { ?>
													<td>
														<h1 class="no-margins" id="queue_dialysis">0</h1>
														<small>Dailysis</small>
													</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>

							<div class="col-md-5">
								<div class="ibox float-e-margins">
									<div class="ibox-title">
										<span class="pull-right">
											<?php if ($_SESSION['enquiry'] == 1) { ?>
												<a href="index.php?equiry&adm">See Details </a>
											<?php } ?>
										</span>
										<h5>Today's Admission Details</h5>
									</div>
									<div class="ibox-content">

										<?php
										$stmt = $db->query("SELECT COUNT(*) FROM discharge_fellowup");
										$discharge_fellowup = $stmt->fetchColumn();
										?>

										<div class="row">
											<div class="col-md-3">
												<h1 class="no-margins" id="today_adm">0</h1>
												<h1 class="no-margins"></h1>
												<small>Admitted</small>
											</div>

											<div class="col-md-3">
												<h1 class="no-margins" id="today_discharge">0</h1>
												<small>Discharged</small>
											</div>

											<div class="col-md-6">
												<h1 class="no-margins"><?php echo $discharge_fellowup; ?> </h1>
												<small>
													<div <?php if ($discharge_fellowup > 0) { ?> id="blink" <?php } ?>>Waiting for Discharge</div>
												</small>
											</div>

										</div>


									</div>
								</div>
							</div>

							<div class="col-md-4">



								<div class="ibox float-e-margins">
									<div class="ibox-title">
										<span class="label label-success pull-right"><?= $title; ?></span>
										<h5>Discount/Charges</h5>
									</div>
									<div class="ibox-content">

										<div class="row">
											<div class="col-md-6">
												<h1 class="no-margins" id="discount_report">0</h1>
												<small>Total Discount</small>

											</div>

											<div class="col-md-6">
												<h1 class="no-margins" id="extra_charge">0</h1>
												<small>Total Extra-Charge</small>
											</div>
										</div>


									</div>
								</div>
							</div>
						</div>
					<?php } ?>


					<div id="claimAlertBox" class="claim-queue-alert" style="display:none; cursor:pointer; background:#e2f0ff; padding:10px; border-radius:5px;">
						New Claim Validation Requests:
						<strong id="claim_count">0</strong>
					</div>


					<div class="row">
						<div class="col-lg-5">
							<div class="ibox float-e-margins">
								<div class="ibox-title">

									<h5>Main Dashboard <?php echo date('h:i a'); ?></h5>
								</div>
								<div class="ibox-content">
									<div class="row">

										<?php if ($_SESSION['enquiry'] == 1) { ?> <a href="index.php?equiry" class="btn btn-app"><i class="fa fa-question-circle"></i>Enquiry</a><?php } ?>
										<?php if ($_SESSION['patient_master'] == 1) { ?> <a href="index.php?ptm" class="btn btn-app"><i class="fa fa-user"></i>Patients Master</a><?php } ?>
										<?php if ($_SESSION['ext_sales'] == 1) { ?> <a href="index.php?sale" class="btn btn-app"><i class="fa fa-shopping-cart"></i>External Services</a><?php } ?>
										<?php if ($_SESSION['biller'] == 1) { ?> <a href="../billing/index.php?admin" class="btn btn-app"><i class="fa fa-money"></i>Biller</a><?php } ?>
										<?php if ($_SESSION['rdc'] == 1) { ?> <a href="../investigations/index.php" class="btn btn-app"><i class="fa fa-ticket"></i>Lab/Image Request</a><?php } ?>
										<a href="index.php?enq" class="btn btn-app"><i class="fa fa-search-plus"></i>Prices Enquiry</a>
										<a href="index.php?bed_enq" class="btn btn-app"><i class="fa fa-search-plus"></i>Bed Enquiry</a>
										<?php if ($_SESSION['gen_claim_rpt'] == 1) { ?> <a href="index.php?cptclaim" class="btn btn-app"><i class="fa fa-credit-card"></i>Claim Manager</a><?php } ?>
										<?php if ($_SESSION['admitted_patient_alert'] == 1) { ?> <a href="index.php?cr" class="btn btn-app" style="color: red;"><i class="fa fa-tags"></i>Deposits</a><?php } ?>

										<?php
										if ($_SESSION['hr_payroll'] == 1) { ?> <a href="index.php?hr" class="btn btn-app"><i class="fa fa-edit"></i> HR/Payroll</a> <?php } ?>
										<?php if ($_SESSION['doc_income'] == 1) { ?> <a href="index.php?doc" class="btn btn-app"><i class="fa fa-user-md"></i>Doctor's Income</a><?php } ?>
										<?php if ($_SESSION['report_mgr'] == 1) { ?> <a href="index.php?report" class="btn btn-app"><i class="fa fa-archive"></i>Report Manager</a><?php } ?>
										<?php if ($_SESSION['price_mgr'] == 1) { ?> <a href="index.php?price" class="btn btn-app"><i class="fa fa-question"></i>Price Manager</a><?php } ?>
										<?php if ($_SESSION['bed_mgr'] == 1) { ?> <a href="index.php?bed" class="btn btn-app"><i class="fa fa-bed"></i>Bed Manager</a><?php } ?>
										<?php if ($_SESSION['discount'] == 1) { ?> <a href="../billing/index.php?discount" class="btn btn-app"><i class="fa fa-minus-square-o"></i>Discount</a><?php } ?>
										<?php if ($_SESSION['voucher'] == 1) { ?> <a href="../billing/index.php?vchr" class="btn btn-app"><i class="fa fa-vine"></i>Vouchers</a><?php } ?>
										<?php if ($_SESSION['webmedic_admin_right'] == 1) { ?> <a href="index.php?rit" class="btn btn-app">
												<span class="badge bg-orange">All Rights</span><i class="fa fa-key"></i>Webmedic Rights</a><?php } ?>


										<?php if ($_SESSION['stock_mgr'] == 1 or $_SESSION['rq_approval'] == 1 or $_SESSION['procure'] == 1) { ?>
											<a href="index.php?stock" class="btn btn-app"><i class="fa fa-database"></i>Stock Manager</a>
										<?php } ?>

										<?php if ($_SESSION['pharmacy'] == 1) { ?> <a href="../pharmacy/index.php" class="btn btn-app"><i class="fa fa-plus-square"></i>Pharmacy</a><?php } ?>
										<?php if ($_SESSION['nursing'] == 1) { ?> <a href="../nursing/index.php" class="btn btn-app"><i class="fa fa-plus-square"></i>Nursing</a><?php } ?>
										<?php if ($_SESSION['doctor'] == 1) { ?> <a href="../doctor/index.php" class="btn btn-app"><i class="fa fa-plus-square"></i>Consultation</a><?php } ?>
										<?php if (isset($_SESSION['statistic_rpt']) && $_SESSION['statistic_rpt'] == 1) { ?>
											<a href="index.php?datab" class="btn btn-app">
												<i class="fa fa-plus-square"></i>Statistic/Data Bank
											</a>
										<?php } ?>

										<?php if ($_SESSION['transplant'] == 1 and $_SESSION['h_code'] == 'zmkc') { ?>
											<a href="../doctor/index.php?transplant" class="btn btn-app"><i class="fa fa-recycle"></i>Transplant</a><?php } ?>
										<?php if ($_SESSION['dialysis'] == 1 and $_SESSION['dialysis_visible'] == '1') { ?>
											<a href="../doctor/dialysis.php" class="btn btn-app"><i class="fa fa-stack-overflow"></i>Dialysis</a><?php } ?>
										<?php if ($_SESSION['procedure_module'] == 1) { ?>
											<a href="../doctor/procedure.php?procedure" class="btn btn-app"><i class="fa fa-scissors"></i>Procedure/Theatre</a><?php } ?>
										<?php if ($_SESSION['report_mgr'] == 1) { ?><a href="../accounts/index.php" class="btn btn-app"><i class="fa fa-database"></i>Accounts</a><?php } ?>

										<?php if ($_SESSION['Procurement_Officer_ack'] == 1) {
											echo '<hr>';

											$stmt_inbox = $db->query("SELECT distinct batch_no FROM stock_table_procurment WHERE approval_stages=1 and navigation='Store'");
											if ($stmt_inbox->rowCount() > 0) { ?>
												<h4 style="color: red;"><strong>Total P.O Request(s) for Approval: <?= $stmt_inbox->rowCount(); ?> </strong></h4>
												<a href="index.php?stock=o&pdr&procurement_manager_ack">View Store & Acknowledge</a>
											<?php }
											$stmt_inbox = $db->query("SELECT distinct batch_no FROM stock_table_procurment WHERE approval_stages=1 and navigation='Pharmacy'");
											if ($stmt_inbox->rowCount() > 0) { ?>
												<h4 style="color: red;"><strong>Total P.O Request(s) for Approval: <?= $stmt_inbox->rowCount(); ?> </strong></h4>
												<a href="index.php?stock=p&pdr&procurement_manager_ack">View Pharmacy & Acknowledge</a>
											<?php } ?>
										<?php } ?>


									</div>
								</div>
							</div>
						</div>




						<?php



						if ($_SESSION['see_statistic_rpt'] == 1) { ?>

							<?php
							if (isset($_GET['income'])) {


								$highest_sale = 0;
								$highest_sale_m = '';
								$highest_claim = 0;
								$highest_claim_m = '';
								$data_inc = '';
								$t_income = 0;

								$year = date("Y");
								$paystatus = '1';

								// Fetch data from database
								$stmt = $db->prepare("
										SELECT 
											DATE_FORMAT(transact_date, '%m') AS month,
											SUM(pay) AS tpay,
											SUM(claim_amt) AS tclaim
										FROM patient_ap_services
										WHERE paystatus = :paystatus
										AND cr = 0 
										AND acct_billed_ack = 0 
										AND (pay_mode = 'claim' OR pay_mode = 'cash')
										AND YEAR(transact_date) = :year
										GROUP BY month
										ORDER BY month
									");

								$stmt->bindValue(':paystatus', $paystatus);
								$stmt->bindValue(':year', $year);
								$stmt->execute();

								$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

								// Pre-fill months array for quick lookup
								$monthlyDataAssoc = array();
								foreach ($monthlyData as $row) {
									$monthStr = str_pad($row['month'], 2, '0', STR_PAD_LEFT);
									$monthlyDataAssoc[$monthStr] = array(
										'sales' => (float)$row['tpay'],
										'claim' => (float)$row['tclaim']
									);
								}

								// Loop through all 12 months
								for ($x = 1; $x <= 12; $x++) {
									$monthStr = str_pad($x, 2, '0', STR_PAD_LEFT);

									// Get sales and claims, default to 0 if no data
									$sales = isset($monthlyDataAssoc[$monthStr]['sales']) ? $monthlyDataAssoc[$monthStr]['sales'] : 0;
									$claim = isset($monthlyDataAssoc[$monthStr]['claim']) ? $monthlyDataAssoc[$monthStr]['claim'] : 0;

									$income = $sales + $claim;
									$data_inc .= $income . ',';
									$t_income += $income;

									// Track highest sale
									if ($sales > $highest_sale) {
										$highest_sale = $sales;
										$highest_sale_m = $monthStr;
									}

									// Track highest claim
									if ($claim > $highest_claim) {
										$highest_claim = $claim;
										$highest_claim_m = $monthStr;
									}
								}

								// Remove trailing comma
								$data_inc = rtrim($data_inc, ',');

								// Optional: Debug
								// echo "Monthly income: $data_inc\n";
								// echo "Total income: $t_income\n";
								// echo "Highest sale: $highest_sale in month $highest_sale_m\n";
								// echo "Highest claim: $highest_claim in month $highest_claim_m\n";




								///////////////===============================================================

								$data_pc = '';
								$total_sale_1wk = 0;
								$paystatus = '1';

								// Get last 7 days as array
								$last7days = [];
								for ($x = 1; $x <= 7; $x++) {
									$last7days[] = date('Y-m-d', strtotime("-$x days"));
								}

								$stmt = $db->prepare("
									SELECT 
										DATE(transact_date) AS day,
										SUM(pay) AS sales,
										SUM(claim_amt) AS claims
									FROM patient_ap_services
									WHERE 
										paystatus = :paystatus 
										AND cr = 0 
										AND acct_billed_ack = 0 
										AND (pay_mode = 'claim' OR pay_mode = 'cash') 
										AND transact_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
									GROUP BY day");

								$stmt->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
								$stmt->execute();
								$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

								// Map results by day for quick lookup
								$dailyData = [];
								foreach ($results as $row) {
									$dailyData[$row['day']] = [
										'sales' => (float)$row['sales'],
										'claims' => (float)$row['claims'],
									];
								}

								// Build final data set
								foreach ($last7days as $day) {
									if (isset($dailyData[$day])) {
										$sum = $dailyData[$day]['sales'] + $dailyData[$day]['claims'];
										$data_pc .= $sum . ',';
										$total_sale_1wk += $dailyData[$day]['sales'];
									} else {
										$data_pc .= '0,';
									}
								}

								$data_pc = rtrim($data_pc, ',');
							?>

								<div class="col-lg-7">
									<div class="row m-t-xs">
										<div class="col-xs-4">
											<h5 class="m-b-xs">Highest Income (Cash)</h5>
											<h4 class="no-margins"><?php echo number_format($highest_sale, 2); ?></h4>
											<div class="font-bold text-navy"><?php echo $highest_sale_m . ', ' . date('Y'); ?> <i class="fa fa-bolt"></i></div>
										</div>
										<div class="col-xs-4">
											<h5 class="m-b-xs">Highest Claim </h5>
											<h4 class="no-margins"><?php echo number_format($highest_claim, 2); ?></h4>
											<div class="font-bold text-navy"><?php echo $highest_claim_m . ', ' . date('Y'); ?> <i class="fa fa-bolt"></i></div>
										</div>

										<div class="col-xs-4">
											<h5 class="m-b-xs">Cash & Claim (Total)</h5>
											<h4 class="no-margins"><?php $ttl = $highest_claim + $highest_sale;
																	echo number_format($ttl, 2); ?></h4>
											<div class="font-bold text-navy"><i class="fa fa-bolt"></i></div>
										</div>


									</div>

									<small style="color:#F60;">
										Sales in last seven(7) days
									</small>
									<div id="sparkline2" class="m-b-sm"></div>
									<div class="row">
										<?php
										$today_date = date("Y-m-d");
										$ystday_date = date('Y-m-d', strtotime("-1 days"));
										$paystatus = '1';

										// Optimized single query for both days
										$stmt = $db->prepare("
											SELECT 
												DATE(transact_date) AS tdate,
												SUM(pay) AS total
											FROM patient_ap_services
											WHERE 
												paystatus = :paystatus 
												AND cr = 0 
												AND acct_billed_ack = 0 
												AND (pay_mode = 'claim' OR pay_mode = 'cash')
												AND DATE(transact_date) IN (:today, :yesterday)
											GROUP BY tdate
										");

										$stmt->bindParam(':paystatus', $paystatus);
										$stmt->bindParam(':today', $today_date);
										$stmt->bindParam(':yesterday', $ystday_date);
										$stmt->execute();

										$today_sales = 0;
										$ystday_sales = 0;

										while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
											if ($rwx['tdate'] === $today_date) {
												$today_sales = $rwx['total'];
											} elseif ($rwx['tdate'] === $ystday_date) {
												$ystday_sales = $rwx['total'];
											}
										}

										// Display block for 7-day total (unchanged logic)
										$data_pc = '';
										$total_p_c = 0;
										$total_sale_1wk = 0;
										for ($x = 1; $x <= 7; $x++) {
											$ddate = date('Y-m-d', strtotime("-$x days"));
											$stmt7 = $db->prepare("SELECT sum(pay) as sales_7, sum(claim_amt) as claim_7 FROM patient_ap_services 
											WHERE paystatus=:paystatus AND cr=0 AND acct_billed_ack=0 AND (pay_mode='claim' OR pay_mode='cash') 
											AND DATE(transact_date)=:ddate");
											$stmt7->bindParam(':paystatus', $paystatus);
											$stmt7->bindParam(':ddate', $ddate);
											$stmt7->execute();

											$row7 = $stmt7->fetch(PDO::FETCH_ASSOC);
											$day_total = $row7['sales_7'] + $row7['claim_7'];
											$total_sale_1wk += $row7['sales_7'];
											$data_pc .= ($day_total ?: 0) . ',';
										}
										$data_pc = rtrim($data_pc, ',');
										?>

										<div class="col-xs-4">
											<small class="stats-label">Today's Income / <a href="index.php?today"><strong>More details</strong></a></small>
											<h4><?php echo number_format($today_sales, 2); ?></h4>
										</div>

										<div class="col-xs-4">
											<small class="stats-label">Yesterday's Income</small>
											<h4><?php echo number_format($ystday_sales, 2); ?></h4>
										</div>

										<div class="col-xs-4">
											<small class="stats-label">Last 7 Days Income (Cash only)</small>
											<h4><?php echo number_format($total_sale_1wk, 2); ?></h4>
										</div>
									</div>



								</div>

								<div class="col-lg-7">
									<div class="ibox float-e-margins">
										<div class="ibox-title">
											<h5>Graph Reports</h5>

											<div class="ibox-tools">

												<a href="index.php" class="btn btn-success btn-xs "><span style="color:#FFF;">Consultation/Admission</span></a> &nbsp;|&nbsp;
												<a href="index.php?income" class="btn btn-danger btn-xs ">Income</a> &nbsp;|&nbsp;
												<a href="index.php?data" class="btn btn-primary btn-xs ">More Data ... </a>
											</div>



										</div>
										<div class="ibox-content">
											<div>
												<canvas id="lineChart" height="140"></canvas>
											</div>
										</div>
									</div>
								</div>

							<?php } elseif (isset($_GET['data'])) {

								if (isset($_GET['yr'])) {
									$y = $_GET['yr'];
									$datyr = $_GET['yr'];
								} else {
									$datyr = date("Y");
									$y = date("Y");
								}

								$stmt = $db->prepare("SELECT insurance,gender,dob,date_capture FROM enrollee");
								$stmt->execute();
								$totalPatient = $stmt->rowCount();

								if ($stmt->rowCount() > 0) {
									$female = 0;
									$male = 0;
									$jan = 0;
									$feb = 0;
									$mar = 0;
									$apr = 0;
									$may = 0;
									$jun = 0;
									$jul = 0;
									$aug = 0;
									$sep = 0;
									$oct = 0;
									$nov = 0;
									$dec = 0;
									$children = 0;
									$youths = 0;
									$adults = 0;
									$senior = 0;
									while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
										////===============================================================================	
										if ($rwx['gender'] == 'Female' or $rwx['gender'] == 'female') {
											$female++;
										}
										if ($rwx['gender'] == 'Male' or $rwx['gender'] == 'male') {
											$male++;
										}




										//-------------------------------------MOTHLY ENROLLEEE 
										if (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-01') {
											$jan++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-02') {
											$feb++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-03') {
											$mar++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-04') {
											$apr++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-05') {
											$may++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-06') {
											$jun++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-07') {
											$jul++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-08') {
											$aug++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-09') {
											$sep++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-10') {
											$oct++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-11') {
											$nov++;
										} elseif (date("Y-m", strtotime($rwx['date_capture'])) == $y . '-12') {
											$dec++;
										}

										//-----------------DOB FOR YEARS
										if ($rwx['dob'] != '') {
											$dob1 = date("Y-m-d", strtotime($rwx['dob']));

											date_default_timezone_set('Africa/Lagos');
											$Current_date = date('Y-m-d');

											$date1 = new DateTime($Current_date);
											$date2 = new DateTime($dob1);
											$diff = $date2->diff($date1);

											$age = $diff->format('%y');
											if ($age >= 0 and $age <= 14) {
												$children++;
											} elseif ($age >= 15 and $age <= 24) {
												$youths++;
											} elseif ($age >= 25 and $age <= 64) {
												$adults++;
											} elseif ($age >= 65 and $age <= 300) {
												$senior++;
											}
										}


										//------------------------------------- INSURANCE

										if ($rwx['insurance'] == "Private(Self)") {
											$private++;
										} elseif ($rwx['insurance'] == "PHIS") {
											$PHIS++;
										} elseif ($rwx['insurance'] == "NHIS") {
											$NHIS++;
										} elseif ($rwx['insurance'] == "Corporate") {
											$Corporate++;
										} elseif ($rwx['insurance'] == "Family") {
											$Family++;
										}
									}
								}
								////////////////////////////////////===============================
								$gender = $male + $female;
								$male_per = round(($male / $gender) * 100, 1);
								$female_per = round(($female / $gender) * 100, 1);
								///================================================================================				 
								$dob = $children + $youths + $adults + $senior;
								$children = round(($children / $dob) * 100, 1);
								$youths = round(($youths / $dob) * 100, 1);
								$adults = round(($adults / $dob) * 100, 1);
								$senior = round(($senior / $dob) * 100, 1);
								////==========================================================

								$insurance = $private + $PHIS + $NHIS + $Corporate + $Family;
								$private = round(($private / $insurance) * 100, 1);
								$PHIS = round(($PHIS / $insurance) * 100, 1);
								$NHIS = round(($NHIS / $insurance) * 100, 1);
								$Family = round(($Family / $insurance) * 100, 1);							?>

								<div class="col-lg-7">
									<div class="ibox ">
										<div class="ibox-content">
											<div>


												<div class="pull-right">
													<a href="index.php" class="btn btn-success btn-xs "><span style="color:#FFF;">Consultation/Admission</span></a> &nbsp;|&nbsp;
													<a href="index.php?income" class="btn btn-danger btn-xs ">Income</a> &nbsp;|&nbsp;
													<a href="index.php?data" class="btn btn-primary btn-xs ">More Data ... </a>
												</div>
											</div>

											<div class="m-t-sm">

												<div class="row">
													<div class="col-md-6">
														<div>
															<div align="center">
																<br>
																<a href="index.php?data"><span style="color:#063;">Gender</span></a> &nbsp;|&nbsp;
																<a href="index.php?data=age"><span style="color:#063;">Age Group</span></a> &nbsp;|&nbsp;
																<a href="index.php?data=insur"><span style="color:#063;">Insurance</span></a>
															</div>

															<div id="pie" style="height:200px;"></div>
														</div>
													</div>
													<div class="col-md-4">
														<ul class="stat-list m-t-lg">
															<li>
																<h2 class="no-margins"><?php echo number_format($male); ?></h2>
																<small>Total Male</small>
																<div class="progress progress-mini">
																	<div class="progress-bar" style="width: <?php echo $male_per; ?>%;"></div>
																</div>
															</li>
															<li>
																<h2 class="no-margins "><?php echo number_format($female); ?></h2>
																<small>Total Female</small>
																<div class="progress progress-mini">
																	<div class="progress-bar" style="width: <?php echo $female_per; ?>%;"></div>
																</div>
															</li>
														</ul>
													</div>
												</div>

											</div>

											<div class="m-t-md">
												<span style="font-size:12px;" class="float-right">
													The current total number of patients in the database: <br><strong><u><?php echo $totalPatient; ?></u></strong>
													See the breakdown by year and months ...
												</span>
												<hr>

											</div>

											<div>
												<canvas id="barChart2" height="140"></canvas>
												<br>
												<div align="center">
													<?php
													$yr = date("Y") - 10;
													$cur_yr = date("Y"); ?>See year :
													<?php for ($x = $yr; $x <= $cur_yr; $x++) { ?>
														<a href="index.php?data&yr=<?php echo $x; ?>"><span style="color:#063;"><?php echo $x; ?></span></a>&nbsp;:
													<?php } ?>
												</div>
											</div>
										</div>
									</div>
								</div>

							<?php } else {

								$y = date("Y");
								$data = '';
								$data2 = '';
								$label = 'Monthly Consultation';
								$label2 = 'Admitted Patient(s)';
								$paystatus = '1';
								$adm_status = '4';

								for ($x = 1; $x <= 12; $x++) {
									$month = str_pad($x, 2, '0', STR_PAD_LEFT);
									$transact_date = "$y-$month";

									// Consultation count
									$stmt = $db->prepare("SELECT pay, claim_amt, cat_type, serv_group 
                          FROM patient_ap_services 
                          WHERE paystatus = :paystatus AND transact_date LIKE :transact_date");
									$stmt->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
									$stmt->bindValue(':transact_date', "%$transact_date%", PDO::PARAM_STR);
									$stmt->execute();

									$consl = 0;
									while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
										if ($rwx['serv_group'] == 'Consultation') {
											$consl++;
										}
									}
									$data .= $consl . ',';

									// Admitted patients count
									$stmt = $db->prepare("SELECT sn FROM admission WHERE adm_status IN ('3','4') AND date_admit LIKE :transact_date");
									//$stmt->bindValue(':adm_status', $adm_status, PDO::PARAM_STR);
									$stmt->bindValue(':transact_date', "%$transact_date%", PDO::PARAM_STR);
									$stmt->execute();

									$data2 .= $stmt->rowCount() . ',';
								}

								$data = rtrim($data, ',');
								$data2 = rtrim($data2, ',');

							?>

								<div class="col-lg-7">
									<div class="ibox float-e-margins">
										<div class="ibox-title">
											<h5>Graph Reports</h5>

											<div class="ibox-tools">

												<a href="index.php" class="btn btn-success btn-xs "><span style="color:#FFF;">Appointment/Admission</span></a> &nbsp;|&nbsp;
												<a href="index.php?income" class="btn btn-danger btn-xs ">Income</a> &nbsp;|&nbsp;
												<a href="index.php?data" class="btn btn-primary btn-xs ">More Data ... </a>
											</div>



										</div>
										<div class="ibox-content">
											<div>
												<canvas id="barChart" height="140"></canvas>
											</div>
										</div>
									</div>
								</div>


							<?php } ?>

						<?php } ?>

						<div class="col-lg-7">
							<div class="ibox float-e-margins">


								<?php if ($_SESSION['add_new_patient'] == 1  and ($_SESSION['rights'] == 'RE' or $speciality_admin == 'Administrator')) { ?>

									<div class="ibox-title">
										<h5>Add & Search Patient</h5>
										<div class="ibox-tools">
											<?php if ($_SESSION['report_mgr'] == 1) { ?>
												<a href="index.php?pend" class="btn btn-success btn-xs "><span style="color:#FFF">Services Rendered</span> </a> &nbsp; | &nbsp;
												<a href="index.php?today" class="btn btn-primary btn-xs "> Today's Sale </a> &nbsp; | &nbsp;
											<?php } ?>
											<a href="index.php?equiry" class="btn btn-primary btn-xs "> More Enquiry</a>
										</div>
									</div>
									<div class="ibox-content">

										<div align="center">


											<div class="row">
												<div class="col-md-4">
													<a class="btn btn-app add_patient_dashboard" data-toggle="modal" data-target="#myModal5" style="background-color: aquamarine; ">
														<i class="fa fa-user add_patient_dashboard"></i> <strong>+ Add New Patient</strong></a>

												</div>
												<div class="col-md-8">

													<form action="index.php" method="POST">


														<div class="form_sep">
															<p style="color:red; font-size: 24px; ">[ Search for Patient ] </p><strong>(Name or Phone or Hospital No.)</strong>

															<input type="text" id="search" name="search" class="form-control" style="border-color:black;" required>
														</div>
														<br>
														<div class="form_sep">


															<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action" id="apply_action" onclick="search_patient('frontdesk')"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
														</div>
													</form>


												</div>
											</div>
										</div>



										<div id="display_queue_table"></div>


									</div>

								<?php } ?>



							</div>
						</div>
					</div>

				<?php } ?>


				<?php include("../inc/footer.php"); ?>

			</div>
		</div>


		<?php include("../inc/footer_scripts.php"); ?>

		<script>
			$(function() {
				$("#show_billing").click(function() {

					if ($(this).is(":checked")) {
						$("#custom_billing").show();
						$("#list_services").hide();
					} else {
						$("#custom_billing").hide();
						$("#list_services").show();
					}
				});
			});




			$(document).ready(function() {
				var input = document.getElementById("search");
				if (input) {
					input.addEventListener("keyup", function(event) {
						if (event.keyCode === 13) {
							event.preventDefault();
							var applyAction = document.getElementById("apply_action");
							if (applyAction) applyAction.click();
						}
					});
				}
			});



			function consults_status() {
				$.ajax({
					url: "_count_admin.php",
					method: "POST",
					data: {
						consults_status: true
					},
					success: function(data) {
						$('#display_queue_table').html(data);
					}
				});

			}


			function select_edit(sn) {

				var textbox = document.getElementById("qty_" + sn);
				textbox.disabled = false;
				textbox.focus();
			}


			function add_order(sn) {

				var stock_name = document.getElementById("stockName_" + sn).value;
				var order_qty_ = document.getElementById("order_qty_" + sn).value;
				var buying_cost_ = document.getElementById("buying_cost_" + sn).value;
				var total_ = document.getElementById("total_" + sn).value;
				var stock_sn_ = document.getElementById("stock_sn_" + sn).value;
				var stock_total_unit_ = document.getElementById("stock_total_unit_" + sn).value;
				var navigation = document.getElementById("navigation").value;
				var supplier_id = document.getElementById("supplier_id").value;
				var stock = document.getElementById("stock").value;
				var generated_po = document.getElementById("generated_po").value;
				///var p_order_dept = document.getElementById("p_order_dept").value;

				const deptSelect = document.getElementById('p_order_dept');
				const p_order_dept_id = deptSelect.value;
				const p_order_dept_name = deptSelect.options[deptSelect.selectedIndex].text;


				var batch_number = document.getElementById("batch_number").value;
				var status_result = 0;

				if (supplier_id == '') {
					alert('Select Vendor/Supplier Name!');
					return;
				}

				if (generated_po == '') {
					alert('Invalid Selection - Select NEW PO / EXISTING BATCH');
					return;
				}

				if (p_order_dept_id == '' && batch_number === 'new_po') {
					alert('Invalid Selection - Select PURCHASING ORDER DEPARTMENT');
					return;
				}



				if (order_qty_ <= 0) {
					alert('Invalid Quantity');
					return;
				}

				// ✅ If NEW PO, check status FIRST
				if (batch_number === 'new_po') {

					$.ajax({
						url: "fetch_set_PO.php",
						method: "POST",
						dataType: "json",
						data: {
							check_batch_status: true,
							supplier_id: supplier_id,
							batch_number: batch_number,
							p_order_dept_id: p_order_dept_id,
							p_order_dept_name: p_order_dept_name
						},
						success: function(json) {
							console.log("SUCCESS:", json);

							if (Number(json.status_result) === 1) {
								if (!confirm(
										'A pending Purchase Order already exists for this Supplier.\n\n' +
										'Do you want to continue and create a new PO?'
									)) {
									return;
								}
							}

							submitOrder(json.status_result);
						},
						error: function(xhr, status, error) {
							console.error("AJAX ERROR:", status, error);
							console.log(xhr.responseText);
						}
					});


				} else {
					// existing PO
					submitOrder(status_result);
				}

				// ✅ actual order submission
				function submitOrder(status_result) {

					$.ajax({
						url: "fetch_set_PO.php",
						method: "POST",
						dataType: "json",
						data: {
							stock_name: stock_name,
							order_qty_: order_qty_,
							buying_cost_: buying_cost_,
							total_: total_,
							supplier_id: supplier_id,
							stock: stock,
							stock_sn_: stock_sn_,
							navigation: navigation,
							stock_total_unit_: stock_total_unit_,
							generated_po: generated_po,
							batch_number: batch_number,
							status_result: status_result,
							p_order_dept_id: p_order_dept_id,
							p_order_dept_name: p_order_dept_name
						},
						success: function(json) {
							document.getElementById("batch_number").value = json.batch_no;
							toastr.success(json.message, 'Attention', {
								timeOut: 5000
							});
							selected_PO_LIST(json.batch_no);
						}
					});
				}
			}


			function selected_batch() {
				var supplier_id = document.getElementById("supplier_id").value;
				$.ajax({

					url: "fetch_set_PO.php",
					data: {
						supplier_id: supplier_id
					},
					type: 'POST',
					success: function(response) {

						$("#generated_po").html(response);

					}
				});
			}

			function selected_PO_LIST(BN) {

				if (BN == 'empty') {
					var batch_no = document.getElementById("generated_po").value;
					document.getElementById("batch_number").value = batch_no;
				} else if (BN != '') {
					var batch_no = BN;
				} else {

					var batch_no = document.getElementById("generated_po").value;
					document.getElementById("batch_number").value = batch_no;
				}

				$.ajax({
					url: "fetch_set_PO.php",
					method: "POST",
					data: {
						batch_no: batch_no
					},
					success: function(data) {
						$("#selected_PO_LIST").html(data);

					}
				});
				///selected_batch();	

			}

			function remove_order(item_to_delete) {
				var result = confirm("Are you sure you want to delete this item?");
				if (result) {
					$.ajax({
						url: "fetch_set_PO.php",
						method: "POST",
						data: {
							item_to_delete: item_to_delete
						},
						success: function(data) {
							toastr.success(data, 'Attention', {
								timeOut: 5000
							})

							var batch_no = document.getElementById("batch_number").value;
							selected_PO_LIST(batch_no);

						}
					});
				}
			}



			function search_patient(target) {

				if (target == 'Ex_sales') {
					var two = '_2';
					var text = document.getElementById("search_2").value;

				} else {
					var two = '';
					var text = document.getElementById("search").value;


				}


				document.getElementById('apply_action' + two).innerHTML = 'Wait ..';
				document.getElementById("apply_action" + two).disabled = true;

				///var search_detials = document.getElementById("search").value;

				let search_detials = text.replace(/^\s+|\s+$/gm, '');

				let length = search_detials.length;

				if (length < 4 && target != 'Ex_sales') {
					toastr.error('Search must be more than 4 characters', 'Invalid Data ', {
						timeOut: 9000
					});
					document.getElementById('apply_action' + two).innerHTML = 'Search';
					document.getElementById("apply_action" + two).disabled = false;
					//	window.location = 'index.php';
					//exit;
					return 0;

				}

				$.ajax({
					url: "fetch_set2.php",
					method: "POST",
					data: {
						search_detials: search_detials,
						target: target
					},
					success: function(data) {


						///alert(data);

						setTimeout(function() {
							$("#overlay").fadeOut();
						}, 500);
						//toastr.info(data, 'Attention', {timeOut: 5000})//
						console.log(data)
						if (data.redirect != undefined) {
							/// index.php?presc&hos_no=000002 index.php?ptm=all/$in_patient

							if (target == 'sales' || target == 'Ex_sales') {
								window.location = 'index.php?sale=' + data.hosp_no;
							} else {
								window.location = 'index.php?ptm=all/' + data.hosp_no + '&app';
							}

						} else {

							////alert(data);

							document.getElementById('apply_action' + two).innerHTML = 'Search';
							document.getElementById("apply_action" + two).disabled = false;

							if (data.trim() == 'NotFound') {
								toastr.error('Not match found', 'Error', {
									timeOut: 5000
								})
							} else {
								$('.modal-title').text('Search Patient');
								$('#patient_search_body').html(data);
								$('#patient_search_modal').modal('show');
							}
						}
					}

				});


			}
		</script>



		<script>
			<?php
			if (isset($error_status) && $error_status == 1) { ?>toastr.error('<?php echo isset($error_msg) ? $error_msg : ''; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } else if (isset($error_status) && $error_status == 2) { ?>toastr.success(' <?php echo isset($error_msg) ? $error_msg : ''; ?> ', 'Success', {
				timeOut: 5000
			})
			<?php } ?>

			<?php if (isset($_GET['data'])) {
				$data = $_GET['data'];
			?>
				c3.generate({
					bindto: '#pie',
					data: {
						columns: [
							<?php if ($data == '') { ?>['Male', <?php echo $male_per; ?>],
								['Female', <?php echo $female_per; ?>]
							<?php } elseif ($data == 'age') { ?>['0-14 yr', <?php echo $children; ?>],
								['15-24 yr', <?php echo $youths; ?>],
								['25-64 yr', <?php echo $adults; ?>],
								['65-Above yr', <?php echo $senior; ?>]
							<?php } elseif ($data == 'insur') { ?>['Private', <?php echo $private; ?>],
								['PHIS', <?php echo $PHIS; ?>],
								['NHIS', <?php echo $NHIS; ?>],
								['Corporate', <?php echo $Corporate; ?>],
								['Family', <?php echo $Family; ?>]
							<?php } ?>
						],
						colors: {
							<?php if ($data == '') { ?>
								data1: '#1ab394',
								data2: '#BABABA'
							<?php } elseif ($data == 'age') { ?>
								data1: '#1ab394',
								data2: '#BABABA',
								data3: '#1ab394',
								data4: '#BABABA'
							<?php } elseif ($data == 'insur') { ?>
								data1: '#1ab394',
								data2: '#BABABA',
								data3: '#1ab394',
								data4: '#BABABA',
								data5: '#BABABA'
							<?php } ?>
						},
						type: 'pie'
					}
				});

			<?php } ?>

			<?php if (isset($_GET['income'])) { ?>
				var lineData = {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					datasets: [

						{
							label: "Monthly Income Year <?php echo date("Y"); ?>",
							backgroundColor: 'rgba(26,179,148,0.5)',
							borderColor: "rgba(26,179,148,0.7)",
							pointBackgroundColor: "rgba(26,179,148,1)",
							pointBorderColor: "#fff",
							data: [<?php echo $data_inc; ?>]
						}
					]
				};

				var lineOptions = {
					responsive: true
				};


				var lineChartElement = document.getElementById("lineChart");
				if (lineChartElement) {
					var ctx = lineChartElement.getContext("2d");
					new Chart(ctx, {
						type: 'line',
						data: lineData,
						options: lineOptions
					});
				}

			<?php } ?>

			<?php if (isset($_GET['data'])) { ?>
				var barData_data = {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					datasets: [

						{
							label: "<?php echo "$datyr Patient(s) Enrolled by Months"; ?>",
							backgroundColor: 'rgba(26,179,148,0.5)',
							borderColor: "rgba(26,179,148,0.7)",
							pointBackgroundColor: "rgba(26,179,148,1)",
							pointBorderColor: "#fff",
							data: [<?php echo $jan . ',' . $feb . ',' . $mar . ',' . $apr . ',' . $may . ',' . $jun . ',' . $jul . ',' . $aug . ',' . $sep . ',' . $oct . ',' . $nov . ',' . $dec; ?>]
						}
					]
				};

				var barOptions = {
					responsive: true
				};


				var barChart2Element = document.getElementById("barChart2");
				if (barChart2Element) {
					var ctx2 = barChart2Element.getContext("2d");
					new Chart(ctx2, {
						type: 'bar',
						data: barData_data,
						options: barOptions
					});
				}
			<?php } ?>

			var barData = {
				labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
				datasets: [

					{
						label: "<?php echo isset($label) ? $label : ''; ?>",
						backgroundColor: 'rgba(26,179,148,0.5)',
						borderColor: "rgba(26,179,148,0.7)",
						pointBackgroundColor: "rgba(26,179,148,1)",
						pointBorderColor: "#fff",
						data: [<?php echo isset($data) ? $data : ''; ?>]
					}, {
						label: "<?php echo isset($label2) ? $label2 : ''; ?>",
						backgroundColor: 'rgba(220, 220, 220, 0.5)',
						pointBorderColor: "#fff",
						data: [<?php echo isset($data2) ? $data2 : ''; ?>]
					}

				]
			};

			var barOptions = {
				responsive: true
			};


			var barChartElement = document.getElementById("barChart");
			if (barChartElement) {
				var ctx2 = barChartElement.getContext("2d");
				new Chart(ctx2, {
					type: 'bar',
					data: barData,
					options: barOptions
				});
			}



			<?php if (isset($data_pc) && $data_pc != '') { ?>

				$(document).ready(function() {

					var sparklineCharts = function() {
						$("#sparkline2").sparkline([<?php echo $data_pc; ?>], {
							type: 'line',
							width: '100%',
							height: '50',
							lineColor: '#1ab394',
							fillColor: "transparent"
						});
					};

					var sparkResize;

					$(window).resize(function(e) {
						clearTimeout(sparkResize);
						sparkResize = setTimeout(sparklineCharts, 500);
					});

					sparklineCharts();

					$("#flot-dashboard5-chart").length && $.plot($("#flot-dashboard5-chart"), [], {
						series: {
							lines: {
								show: false,
								fill: true
							},
							splines: {
								show: true,
								tension: 0.4,
								lineWidth: 1,
								fill: 0.4
							},
							points: {
								radius: 0,
								show: true
							},
							shadowSize: 2
						},
						grid: {
							hoverable: true,
							clickable: true,

							borderWidth: 2,
							color: 'transparent'
						},
						colors: ["#1ab394", "#1C84C6"],
						xaxis: {},
						yaxis: {},
						tooltip: false
					});

				});

			<?php } ?>
		</script>

		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
		<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
		<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
		<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>


		<script>
			$(document).ready(function() {
				$('.trumbowygEditor').trumbowyg({
					btns: [
						['viewHTML'],
						['undo', 'redo'], // Only supported in Blink browsers
						['formatting'],
						['strong', 'em', 'del'],
						['superscript', 'subscript'],
						['fontsize'],
						['foreColor', 'backColor'],
						['link'],
						['insertImage'],
						['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
						['unorderedList', 'orderedList'],
						['horizontalRule'],
						['removeformat'],
						['fullscreen']
					],
					plugins: {
						fontsize: {
							sizeList: [
								'12px',
								'14px',
								'16px',
								'18px',
								'20px',
								'24px',
								'32px',
								'48px',
							]
						}
					}
				});


			});
		</script>

		<div class="modal inmodal fade" id="merge_patient_number_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Hospital Number Merge</h4>
					</div>

					<div class="modal-body">



						<div class="form_sep">
							<label for="reg_input_name" class="req">Correct Hospital Number</label>
							<input type="text" id="correct_number" name="correct_number" class="form-control" required>
						</div>

						<div class="form_sep">
							<label for="reg_input_name" class="req">Delete Hospital Number</label>
							<input type="text" id="delete_hospital_number" name="delete_hospital_number" class="form-control" required>
						</div>


						<div class="form_sep">
							<label>Remarks (Required) <b style="color: red;">Remarks must be at least 50 characters.</b></label>
							<textarea class="input-sm form-control" cols="5" rows="2" name="remark_for_marger" id="remark_for_marger" maxlength="100"><?php echo isset($_POST['payment_remarks']) ? htmlspecialchars($_POST['payment_remarks']) : ''; ?></textarea>
						</div>



						<div class="form_sep" id="merge_hosp_no_preview_btn_wrap">
							<div class="pull-left">
								<button type="submit" class="btn btn-success btn btn-sm" onClick="merge_hosp_no_preview()" id="merge_hosp_no_preview_btn">Verify Records</button>
							</div>

							<div class="pull-right">
								<button type="button" class="btn btn-danger btn btn-sm" data-dismiss="modal" aria-hidden="true">Close</button>

							</div>
						</div>
						<div id="merge_hosp_no_details"></div>

						<div class="form_sep" id="merge_hosp_no_submit_btn_wrap" style="display: none;">
							<hr>
							<div class="pull-left">
								<button type="submit" class="btn btn-primary btn btn-sm" onClick="merge_hosp_no()" id="merge_hosp_no_submit_btn">Apply</button>
							</div>

							<div class="pull-right">
								<button type="button" class="btn btn-danger btn btn-sm" onClick="cancel_merge_hosp_no_preview()">Cancel</button>
								<button type="button" class="btn btn-danger btn btn-sm" data-dismiss="modal" aria-hidden="true">Close</button>

							</div>
						</div>



					</div>
				</div>
			</div>
		</div>



		<div class="modal inmodal fade" id="new_patient_dash_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Add New Patient</h4>
					</div>

					<div class="modal-body">

						<form method="post" id="new_patient_dash_body">


							<div class="form_sep">
								<label for="reg_input_name" class="req">01. Surname</label>
								<input type="text" id="surname" name="surname" class="form-control" required>
							</div>

							<div class="form_sep">
								<label for="reg_input_name" class="req">02. First Name</label>
								<input type="text" id="fname" name="fname" class="form-control" required>
							</div>

							<div class="form_sep">
								<label for="reg_input_no" class=""> 03. Other Name</label>
								<input type="text" id="oname" name="oname" class="form-control">
							</div>

							<div class="form_sep">
								<label for="reg_select" class="req">04. Gender</label>
								<select name="gender" id="gender" class="form-control" required>

									<option selected="selected" value="">Select...</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
									<option value="Other">Other</option>
								</select>
							</div>

							<div class="form_sep" id="">
								<label class="req">05. Date of Birth</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="date" class="form-control" name="dob" id="dob" required>
								</div>
							</div>

							<div class="form_sep">
								<label><input type="checkbox" name="send_welcome_sms" id="send_welcome_sms" value="1"> Send Welcome SMS</label>
							</div>

							<div class="form_sep">
								<label for="phoneno">06. Phone No:</label>
								<input type="text" id="phoneno" name="phoneno" class="form-control">
							</div>

							<div class="form_sep">
								<label><input type="checkbox" name="send_welcome_email" id="send_welcome_email" value="1"> Send Welcome Email</label>
							</div>

							<div class="form_sep" id="email_field_container">
								<label for="email" id="email_label">Email Address:</label>
								<input type="email" id="email" name="email" class="form-control">
							</div>

							<script>
								document.addEventListener("DOMContentLoaded", function() {
									const smsToggle = document.getElementById("send_welcome_sms");
									const emailToggle = document.getElementById("send_welcome_email");
									const phoneInput = document.getElementById("phoneno");
									const emailInput = document.getElementById("email");
									const emailContainer = document.getElementById("email_field_container");

									function updateInteractivity() {
										if (smsToggle.checked) {
											phoneInput.setAttribute("required", "required");
											phoneInput.previousElementSibling.classList.add("req");
										} else {
											phoneInput.removeAttribute("required");
											phoneInput.previousElementSibling.classList.remove("req");
										}

										if (emailToggle.checked) {
											emailContainer.style.display = "block";
											emailInput.setAttribute("required", "required");
											document.getElementById("email_label").classList.add("req");
										} else {
											emailContainer.style.display = "none";
											emailInput.removeAttribute("required");
											document.getElementById("email_label").classList.remove("req");
										}
									}

									smsToggle.addEventListener("change", updateInteractivity);
									emailToggle.addEventListener("change", updateInteractivity);
									updateInteractivity(); // Initialize on load
								});
							</script>

							<div class="form_sep">
								<div class="pull-left">

									<?php $stmt = $db->query("SELECT * FROM prices_table 
	 	WHERE hosp_price>0 and ext_price>0 and (item_service='New File')");
									if ($stmt->rowCount() > 0) {
									?>
										<button type="submit" class="btn btn-success btn btn-sm" name="Save_patient" id="Save_patient">Save</button>
										<input type="hidden" name="MM_update" value="add_new_patient_start" />
									<?php } else { ?>
										<h3>The amount for the new file has not been set. Goto Price Manager to Add 'New File'. </h3>
									<?php } ?>

								</div>

								<div class="pull-right">
									<a href="index.php" class="btn btn-danger btn btn-sm">Close</a>

								</div>
							</div>

						</form>


					</div>
				</div>
			</div>
		</div>




		<div class="modal inmodal fade" id="new_patient_page2_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Add New Patient</h4>
					</div>

					<div class="modal-body" id="new_patient_page2_body">

					</div>
				</div>
			</div>
		</div>


		<div class="modal inmodal fade" id="refer_modal2" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id=""></h4>
					</div>

					<div class="modal-body" id="refer_body2">

					</div>
				</div>
			</div>
		</div>

		<div class="modal inmodal fade" id="patient_search_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Patient Search Modal</h4>
					</div>
					<div class="modal-body" id="patient_search_body">

					</div>
				</div>
			</div>
		</div>

		<div class="modal inmodal fade" id="sp_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id=""></h4>
					</div>
					<div class="modal-body" id="sp_body">

					</div>


				</div>
			</div>
		</div>


		<div class="modal inmodal fade" id="discharge_booking_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Attention!</h4>
					</div>

					<div class="modal-body" id="claims_body">

						<?php if ($dischargetable != '') { ?>
							<h4 style="color:#C03">
								The following patients has been auto-discharge!
							</h4>
							<hr>
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Hospital #</th>
										<th>Appointment Date</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $dischargetable; ?>
								</tbody>
							</table>

						<?php } ?>

						<?php if ($display_status == 1) { ?>

							<form action="index.php" method="POST">

								<p><?php echo $my_note; ?></p>

								<button type="submit" name="read_msg" class="btn btn-primary">Yes, I have read it</button>
							</form>

						<?php } ?>
					</div>
				</div>
			</div>
		</div>


		<div class="modal inmodal fade" id="auth_reminder_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="">Message!</h4>
					</div>

					<div class="modal-body" id="claims_body">

						<?php if ($auth_table != '') { ?>

							<div id="blink">
								<h4 style="color:#F00">APPOINTMENTS WITHOUT <U><strong>AUTHORIZATION CODES</strong></U> FOR <?php echo date("M") . ', ' . date("Y"); ?></h4>
							</div>
							<hr>

							<table class="table table-striped table-bordered">
								<thead>
									<tr>

										<th>Hospital #</th>
										<th>Patient Name</th>
										<th>Service Name</th>
										<th>Date</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $auth_table; ?>
								</tbody>
							</table>

						<?php } ?>

					</div>
				</div>
			</div>
		</div>
		<?php include('../modal_lock.php'); ?>

		<script>
			$(document).ready(function() {
				$('#myForm').submit(function(event) {

					event.preventDefault(); // Prevent the default form submission
					var formData = $(this).serialize();
					var action = '';

					// Check which button was clicked
					if ($('#approve_all_issue').data('clicked')) {
						action = 'approve_all_issue';
					} else if ($('#approve_all_request').data('clicked')) {
						action = 'approve_all_request';
					}

					// Add the action parameter to the form data
					formData += '&action=' + action;
					$.ajax({
						type: 'POST',
						url: 'approve_all_request.php',
						data: formData,
						success: function(response) {

							////alert(response);

							var jsonn = JSON.parse(response);

							/* 					if (jsonn["status"] == 1) {
													toastr.success(jsonn["message"], 'Attention', {
														timeOut: 5000
													});

													$('#approve_all_request').prop('disabled', true);
													$('#approve_all_issue').prop('disabled', true);

													// Refresh after 2 seconds
													setTimeout(function() {
														location.reload();
													}, 1000);

												} else {
													toastr.error(jsonn["message"], 'Attention', {
														timeOut: 5000
													});
												} */




							if (jsonn["status"] == 1) {

								toastr.success(jsonn["message"], 'Attention', {
									timeOut: 4000
								});

								// Show partial warnings (if any)
								if (jsonn["warnings"] && jsonn["warnings"].length > 0) {

									let warnMsg = "<ul style='padding-left:15px'>";
									jsonn["warnings"].forEach(function(w) {
										warnMsg += "<li>" + w + "</li>";
									});
									warnMsg += "</ul>";

									toastr.warning(warnMsg, 'Some items were skipped', {
										timeOut: 8000,
										closeButton: true,
										escapeHtml: false
									});
								}

								$('#approve_all_request').prop('disabled', true);
								$('#approve_all_issue').prop('disabled', true);

								setTimeout(function() {
									location.reload();
								}, 1200);

							} else {

								let errMsg = jsonn["message"] || 'Operation failed';

								// Backend debug feedback (if any)
								if (jsonn["debug"] && jsonn["debug"].length > 0) {

									errMsg += "<ul style='padding-left:15px'>";
									jsonn["debug"].forEach(function(e) {
										errMsg += "<li>" + e + "</li>";
									});
									errMsg += "</ul>";
								}

								toastr.error(errMsg, 'Attention', {
									timeOut: 9000,
									closeButton: true,
									escapeHtml: false
								});
							}


						},
						error: function(xhr, status, error) {
							console.log(xhr.responseText);
						}
					});

					// Reset the clicked button data
					$('#approve_all_issue').data('clicked', false);
					$('#approve_all_request').data('clicked', false);
				});

				// Set the clicked button data when a button is clicked
				$('#approve_all_issue').click(function() {
					$(this).data('clicked', true);
				});

				$('#approve_all_request').click(function() {
					$(this).data('clicked', true);
				});
			});


			$(document).ready(function() {
				$('#myForm_procurement_entry').submit(function(event) {
					event.preventDefault();

					var formData = $(this).serialize();
					$.ajax({
						type: 'POST',
						url: 'approve_procurement.php', // Change this to the URL of the PHP file that will handle the request
						data: formData,
						success: function(response) {

							var jsonn = JSON.parse(response);

							if (jsonn["status"] == 1) {
								toastr.success(jsonn["message"], 'Attention', {
									timeOut: 5000
								})
								$('#approve_procurement').prop('disabled', true);
							} else {

								$('#approve_all_issue').data('clicked', false);
								$('#approve_all_request').data('clicked', false);
								toastr.error(jsonn["message"], 'Attention', {
									timeOut: 5000
								})

							}
						},
						error: function(xhr, status, error) {
							console.log(xhr.responseText);
						}
					});
				});
			});


			function percentage_markup_cal() {
				var purchase_cost = parseFloat(document.getElementById('purchase_cost').value);

				// Check if purchase_cost is a valid number
				if (isNaN(purchase_cost) || purchase_cost <= 0) {
					alert('Invalid Purchase Price');
					return; // Use return instead of exit
				}

				var units = parseFloat(document.getElementById('units').value);
				var percentage_markup = parseFloat(document.getElementById('percentage_markup').value);

				// Check if units or percentage_markup are valid numbers
				if (isNaN(units) || units <= 0) {
					alert('Invalid Units');
					return;
				}

				if (isNaN(percentage_markup) || percentage_markup < 0) {
					alert('Invalid Percentage Markup');
					return;
				}

				var amt = purchase_cost / units;
				var markup_amount = (amt * percentage_markup) / 100;
				var selling_price = amt + markup_amount;

				if (selling_price > 0) {
					document.getElementById('hosp_price').value = selling_price.toFixed(2);
					document.getElementById('cash_price').value = selling_price.toFixed(2);
				}

				// Optionally alert the selling price
				// alert("Selling Price: " + selling_price.toFixed(2));
			}





			function payNow(sale_sn) {
				document.getElementById('pay_now').innerHTML = "Wait ...";
				document.getElementById('pay_now').disabled = true;

				$.ajax({
					url: "../payfrom_wallet.php",
					method: "POST",
					data: {
						sale_sn: sale_sn
					},
					success: function(data) {

						var jsonn = JSON.parse(data);
						if (jsonn["status"] == 1) {
							///document.getElementById("pay_now").disabled = false;
							document.getElementById("pay_now").innerHTML = 'Pay';
							toastr.error(jsonn["message"], 'Attention', {
								timeOut: 5000
							})

						} else {

							document.getElementById("pay_now").innerHTML = 'Done';
							toastr.success('Successful', 'Attention', {
								timeOut: 5000
							})
						}

					}
				});
			}



			<?php if (isset($_GET['pay_from_deposit'])) {
				$pay_from_deposit = $_GET['pay_from_deposit']; ?>

				$.ajax({
					url: "../payfrom_wallet.php",
					method: "POST",
					data: {
						sale_sn: <?= $pay_from_deposit; ?>
					},
					success: function(data) {
						var jsonn = JSON.parse(data);
						if (jsonn["status"] == 1) {
							toastr.error(jsonn["message"], 'Attention', {
								timeOut: 5000
							})
						} else {
							toastr.success('Successful', 'Attention', {
								timeOut: 5000
							})
						}
					}
				});
			<?php } ?>



			$(document).on('click', '.edit_patient_ext', function() {
				var edit_patient_id_ext = $(this).attr("id");
				$.ajax({
					url: "fetch_set2.php",
					method: "POST",
					data: {
						edit_patient_id_ext: edit_patient_id_ext
					},
					success: function(data) {

						$('.modal-title').text('Edit Patient Data');
						$('#edit_pat_ext_body').html(data);
						$('#edit_patient_data_ext_modal').modal('show');

					}
				});
			});


			$(document).on('click', '.add_credit_limit', function() {
				var add_credit_limit_id = $(this).attr("id");


				$.ajax({
					url: "../add_credit_limit.php",
					method: "POST",
					data: {
						add_credit_limit_id: add_credit_limit_id
					},
					success: function(data) {
						$('.modal-title').text('add_credit_limit');
						$('#add_credit_limit_body').html(data);
						$('#add_credit_limit_modal').modal('show');

					}
				});
			});




			$(document).on('click', '.document_note', function() {

				var add_review = $(this).attr("id");
				$.ajax({
					url: "../inc/documentation.php",
					method: "POST",
					data: {
						add_documentation: add_review
					},
					success: function(data) {

						$('.modal-title').text("Patient's Documentation");
						$('#document_note_mdl').modal('show');
						$('#document_note_body').html(data);

						patient_review();
					}
				});
			});


			function patient_review() {

				/// $('#loader').show();

				var hospital_no = document.getElementById('hospital_no').value;

				$.ajax({
					url: "../inc/_ward_review_fetch.php",
					method: "POST",
					data: {
						patient_review_doc: hospital_no
					},
					success: function(data) {

						document.getElementById('data_displayed').innerHTML = data;

					}
				});
			}


			function delete_notes(sn) {

				var rr = confirm("Are you sure you want to DELETE?");
				if (rr === true) {
					$.ajax({
						url: "../inc/documentation.php",
						method: "POST",
						data: {
							delete_notes: sn
						},
						success: function(data) {

							//// alert(data);

							var json = JSON.parse(data);
							//alert(json["status"]);
							if (json["status"] == 0) {
								patient_review();
								refresh_form();
								toastr.success(json["message"], 'Deleted', {
									timeOut: 5000
								});
							} else {
								toastr.error(json["message"], 'Error', {
									timeOut: 5000
								});
							}
						}
					});
				}
			}






			/////================================ BEGINING VALIDATION

			$(document).on('click', '.validate_package', function() {

				var validate_package_id = $(this).attr("id");
				if (validate_package_id != '') {
					$.ajax({
						url: "../validate_package.php",
						method: "POST",

						data: {
							validate_package_id: validate_package_id
						},

						success: function(data) {

							$('.modal-title').text('Validate Package');

							$('#validate_package_body').html(data);
							$('#validate_package_modal').modal('show');
						}
					});
				}
			});

			function un_validate_pckage_item(tag) {
				if (confirm("Are you sure you want to UNDO?")) {
					$.ajax({
						url: "../validate_package.php",
						method: "POST",
						data: {
							un_validate_: tag
						},
						success: function(data) {

							document.getElementById("undo_" + tag).disabled = true;
							document.getElementById("undo_" + tag).innerHTML = 'Done';
						},
						error: function(jqXHR, textStatus, errorThrown) {
							console.error("Error:", textStatus, errorThrown);
						}
					});
				}
			}

			function validate_pckage_item(tag) {
				if (confirm("Are you sure you want to proceed with this action?")) {
					$.ajax({
						url: "../validate_package.php",
						method: "POST",
						data: {
							validate_: tag
						},
						success: function(data) {
							document.getElementById("undo_" + tag).disabled = true;
							document.getElementById("undo_" + tag).innerHTML = 'Done';
						}
					});
				}
			}


			////========================================= VALIDATIONS
		</script>




		<?php include("admin_js_script.php"); ?>
		<?php include("js_modal.php");

		include_once('../inc/procedure_attach_to_file.php');
		?>

		<div class="modal fade" id="claimModal">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4>New Claims Validation Requests</h4>
					</div>

					<div class="modal-body" style="max-height:400px; overflow:auto;">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Hospital No</th>
									<th>Time</th>
									<th>Prepared By</th>
									<th>Total Requests</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="claim_body"></tbody>
						</table>
					</div>

					<div class="modal-footer">
						<button class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<script>
			<?php if (isset($_GET['success'])) { ?>
				toastr.success('Successful', 'Success', {
					timeOut: 5000
				})
			<?php } ?>

			<?php

			if (isset($error_status) && $error_status == 1) { ?>
				toastr.error('<?php echo isset($error_msg) ? $error_msg : ''; ?>', 'Error', {
					timeOut: 5000
				})

			<?php } else if (isset($error_status) && $error_status == 2) {
			?>
				toastr.success(' <?php echo isset($error_msg) ? $error_msg : ''; ?> ', 'Success', {
					timeOut: 5000
				})
			<?php

			}

			?>
		</script>

		<script>
			function Accknl_booking(sn) {

				////	alert(sn);

				$.ajax({
					url: "_count_admin.php",
					method: "POST",
					data: {
						Accknl_booking: sn
					},
					success: function(data) {
						toastr.success(data, 'Attention', {
							timeOut: 5000
						})
						$('#specialist_modal').modal('hide'); //hide
					}
				});
			}

			$(document).ready(function() {
				$.ajax({
					url: "_count_admin.php",
					method: "POST",
					dataType: "json",
					data: {
						get_dashboard_counts: true
					},
					success: function(response) {

						if (response.status == 200) {
							$('#queue_').text(response.today_queue);
							$('#today_adm').text(response.today_adm);
							$('#today_discharge').text(response.today_discharge);
							$('#discount_report').text(response.discount_report);
							$('#extra_charge').text(response.extra_charge);

							<?php if ($_SESSION['dialysis_visible'] == 1) { ?>
								$('#queue_dialysis').text(response.TO_QUEUE_dialysis);
							<?php } ?>
						}

						console.log(response);
					},
					error: function(err) {
						console.log(err);
					}
				});



				$.ajax({
					url: "_count_admin.php",
					method: "POST",
					data: {
						display_queue_table: true
					},
					success: function(data) {
						$('#display_queue_table').html(data);
					}
				});

			})
		</script>

		<script src="../js/idle.js"></script>


		<?php if ($_SESSION['rights'] == 'RE') { ?>

			<script>
				/**
				 * Global Lab Result Notification System
				 * Works on all pages
				 */

				var previousCount = localStorage.getItem('lab_previous_count') ?
					parseInt(localStorage.getItem('lab_previous_count')) :
					0;

				var originalTitle = document.title;
				var flashTitleInterval = null;

				// Request browser notification permission
				if (Notification.permission !== "granted") {
					Notification.requestPermission();
				}

				// ====================
				// Initialize badge on page load
				// ====================
				$(document).ready(function() {
					var badge = document.getElementById('see_test_results_count');
					if (badge) {
						badge.innerText = previousCount;
					}
				});

				// ====================
				// Play alert sound
				// ====================
				function playAlertSound() {
					var sound = document.getElementById("alertSoundGlobal");
					if (!sound) {
						sound = document.createElement("audio");
						sound.id = "alertSoundGlobal";
						sound.src = "../sounds/notification.wav";
						document.body.appendChild(sound);
					}
					sound.pause();
					sound.currentTime = 0;
					sound.play().catch(() => {});
				}

				// ====================
				// Flash tab title
				// ====================
				function flashTabTitle(message) {
					if (flashTitleInterval) clearInterval(flashTitleInterval);
					var visible = true;
					flashTitleInterval = setInterval(function() {
						document.title = visible ? message : originalTitle;
						visible = !visible;
					}, 1000);
				}

				function stopTabFlash() {
					if (flashTitleInterval) {
						clearInterval(flashTitleInterval);
						flashTitleInterval = null;
						document.title = originalTitle;
					}
				}

				// ====================
				// Main function: check new lab results
				// ====================
				function checkNewResultsGlobal() {
					$.ajax({
						url: 'fetch_new_results.php', // returns {"new_count": 12}
						method: 'GET',
						dataType: 'json',
						success: function(data) {

							var newCount = parseInt(data.new_count) || 0;

							if (newCount > previousCount) {

								// Sound alert
								playAlertSound();

								// Browser notification if tab inactive
								if (Notification.permission === "granted") {
									new Notification("New Lab Result", {
										body: "You have new test results available",
										icon: "../images/notification_icon.png"
									});
								}

								// Flash tab title
								flashTabTitle("🔴 New Lab Results!");

								// Update badge if exists
								var badge = document.getElementById('see_test_results_count');
								if (badge) badge.innerText = newCount;

								// Blink label if exists
								var label = $('#make_me_blink2');
								if (label.length) {
									label.fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300).css('color', 'red');
									setTimeout(function() {
										label.css('color', '');
									}, 3000);
								}

								// Update localStorage and memory
								previousCount = newCount;
								localStorage.setItem('lab_previous_count', newCount);
							}
						},
						error: function(err) {
							console.error("Error checking lab results:", err);
						}
					});
				}

				// Stop flashing when user clicks anywhere
				$(document).on('click', function() {
					stopTabFlash();
				});

				// Poll every 10 seconds
				setInterval(checkNewResultsGlobal, 10000);

				// Run immediately
				checkNewResultsGlobal();
			</script>

		<?php } ?>

		<script>
			$(document).on('click', '.view_results_available', function() {

				$.ajax({
					url: 'fetch_new_results_details.php',
					method: 'GET',
					success: function(data) {
						$('#test_results_body').html(data);
						$('#testResultsModal').modal('show');
					}
				});

			});
		</script>


		<script>
			function loadResults() {

				$.ajax({
					url: 'fetch_new_results_details.php',
					method: 'GET',
					success: function(data) {

						$('#test_results_body').html(data);

						// Destroy previous instance
						if ($.fn.DataTable.isDataTable('#resultsTable')) {
							$('#resultsTable').DataTable().destroy();
						}

						// Reinitialize with scroll + search
						$('#resultsTable').DataTable({
							searching: true,
							paging: true,
							ordering: true,

							// ✅ Vertical scroll
							scrollY: "300px",
							scrollCollapse: true,

							// Optional
							pageLength: 10,

							language: {
								search: "Search Results:"
							}
						});

					}
				});
			}

			// Button click
			$(document).on('click', '.view_results_available', function() {
				loadResults();
				$('#testResultsModal').modal('show');
			});
		</script>

		<script>
			var previousPharmacyCount = localStorage.getItem('claim_count') ?
				parseInt(localStorage.getItem('claim_count')) : 0;

			function checkClaim() {

				$.ajax({
					url: 'claim_alert_count.php',
					method: 'GET',
					dataType: 'json',
					success: function(data) {


						let count = parseInt(data.total) || 0;

						if (count > 0) {

							$('#claimAlertBox').fadeIn();
							$('#claim_count').text(count);

							if (count > previousPharmacyCount) {

								// 🔊 Sound
								let sound = new Audio('../sounds/notification.wav');
								sound.play().catch(() => {});

								// 🔵 Flash
								$('#claimAlertBox')
									.css('background', '#cce5ff')
									.fadeOut(200).fadeIn(200)
									.fadeOut(200).fadeIn(200);

								setTimeout(() => {
									$('#claimAlertBox').css('background', '#e2f0ff');
								}, 3000);
							}

						} else {
							$('#claimAlertBox').fadeOut();
						}

						previousPharmacyCount = count;
						localStorage.setItem('claim_count', count);
					}
				});
			}


			// CLICK → LOAD MODAL
			$('#claimAlertBox').on('click', function() {

				$('#claimModal').modal('show');

				$.ajax({
					url: 'claim_alert_list.php',
					success: function(data) {
						$('#claim_body').html(data);
					}
				});

			});


			// RUN
			<?php if ($_SESSION['convert_patient_insur'] == 1 && $_SESSION['rights'] == 'RE') { ?>
				setInterval(checkClaim, 60000);
				checkClaim();
			<?php } ?>
		</script>

</body>

</html>