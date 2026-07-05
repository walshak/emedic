<?php
session_start();
require_once('Connections/Conn.php');
$messages = [];

if (isset($_GET['err'])) {
    $messages[] = "Oops! You have entered an invalid username OR password.";
}
if (isset($_GET['adm_right'])) {
    $messages[] = "Oops! You don't have admin privileges!";
}
if (isset($_GET['timeout'])) {
    $minute = $_GET['minute'];
    $messages[] = "Oops! Session Expired - " . $minute;
}

if (isset($_GET['access']) || isset($_GET['lock'])) {
    $messages[] = "Oops! Access Denied. <br>See the Administrator for Access.";
}

if (isset($_GET['emp_error'])) {
    $messages[] = "Oops! Employee Details Not Available. <br>See the Administrator for Access.";
}

if (isset($_GET['browser_error'])) {
    $messages[] = "<strong>THIS IS NOT A RECOMMENDED BROWSER!</strong><br>
                   USE ANY OF THESE BROWSERS: Microsoft Edge, Google Chrome, Opera, Safari.";
}

if ($_SERVER['SERVER_NAME'] !== 'localhost') {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: no-referrer-when-downgrade");
    header("Feature-Policy: geolocation 'self'");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https://placehold.co; connect-src 'self'");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); // Enable with HTTPS
    header("Permissions-Policy: geolocation=(), microphone=()");
}

// Use openssl fallback for random_bytes
/* if (!function_exists('random_bytes')) {
	function random_bytes($length)
	{
		return openssl_random_pseudo_bytes($length);
	}
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
 */

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$install_status = null;
if (strpos($currentUrl, "webmedic.ng") !== false) {
    // echo "The substring 'webmedic.ng' exists in the string.";
    $install_status = "cloud";
} else {
    $install_status = "local";
    //echo "The substring 'webmedic.ng' does not exist in the string.";
}


function fetchAdminUserRights($db, $username)
{
    $stmt = $db->prepare("SELECT * FROM admin_users_rights WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION = array_merge($_SESSION, [
            'hr_payroll'                 => $row['hr_payroll'],
            'doc_income'                 => $row['doc_income'],
            'rdc'                        => $row['rdc'],
            'report_mgr'                => $row['report_mgr'],
            'price_mgr'                 => $row['price_mgr'],
            'bed_mgr'                   => $row['bed_mgr'],
            'accom_price'               => $row['accom_price'],
            'writeoff_discharge'        => $row['writeoff_discharge'],
            'see_statistic_rpt'         => $row['see_statistic_rpt'],
            'patient_master'            => $row['patient_master'],
            'create_edit_insur'         => $row['create_edit_insur'],
            'add_new_patient'           => $row['add_new_patient'],
            'convert_patient_insur'     => $row['convert_patient_insur'],
            'see_med_rpt'               => $row['see_med_rpt'],
            'see_patient_log'           => $row['see_patient_log'],
            'see_patient_tranc_writeoff' => $row['see_patient_tranc_writeoff'],
            'admitted_patient_alert'    => $row['admitted_patient_alert'],
            'ext_sales'                 => $row['ext_sales'],
            'biller'                    => $row['biller'],
            'discount'                  => $row['discount'],
            'voucher'                   => $row['voucher'],
            'voucher_unit'              => $row['voucher_unit'],
            'webmedic_admin_right'      => $row['webmedic_admin_right'],
            'enquiry'                   => $row['enquiry'],
            'stock_mgr'                 => $row['stock_mgr'],
            'stock_mgr_pharm'           => $row['stock_mgr_pharm'],
            'purchase_approval'         => $row['purchase_approval'],
            'procure'                   => $row['procure'],
            'rq_approval'               => $row['rq_approval'],
            'b4_rq_approval'            => $row['b4_rq_approval'],
            'gen_claim_rpt'             => $row['gen_claim_rpt'],
            'writeoff'                  => $row['writeoff'],
            'pharmacy'                  => $row['pharmacy'],
            'nursing'                   => $row['nursing'],
            'doctor'                    => $row['doctor'],
            'remove_stock_qty'          => $row['remove_stock_qty'],
            'transplant'                => $row['transplant'],
            'dialysis'                  => $row['dialysis'],
            'procedure_module'          => $row['procedure_module'],
            'PO_payment'                => $row['PO_payment'],
            'print_med_report'          => $row['print_med_report'],
            'Procurement_Officer_ack'   => $row['Procurement_Officer_ack'],
            'template_setup'   => $row['template_setup'],
            'backup_db'   => $row['backup_db'],
            'delete_doc'   => $row['delete_doc'],
            'speciality_admin'   => $row['speciality']

        ]);
        // If you need this variable outside session
        ///return $row['speciality'];
    }
}

function fetchBillerUserData($db, $username)
{
    $stmt = $db->prepare("SELECT * FROM biller_users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION = array_merge($_SESSION, [
            'invoice'                => $row['invoice'],
            'reciept'                => $row['reciept'],
            'deposit'                => $row['deposit'],
            'lab'                    => $row['lab'],
            'pharm'                  => $row['pharm'],
            'nursing'                => $row['nursing'],
            'other_bill'            => $row['other_bill'],
            'reprint'                => $row['reprint'],
            'transfer'               => $row['transfer'],
            'refund'                 => $row['refund'],
            'discount'               => $row['discount'],
            'claims'                 => $row['claims'],
            'vouchers'               => $row['vouchers'],
            'reversal'               => $row['reversal'],
            'writeoff'               => $row['writeoff'],
            'edit_price_at_point'    => $row['edit_price_at_point']
        ]);
    }
}

function fetchInvstiUserData($db, $username)
{
    $section_ = isset($_SESSION['section_']) ? $_SESSION['section_'] : '';
    $stmt = $db->prepare("SELECT * FROM invsti_users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        // Determine section and speciality
        if ($row['section'] === 'Radiology' || $row['section'] === 'Laboratory') {
            $section = $row['section'];
            $speciality = $row['speciality'];
        } elseif ($section_ === 'RAD') {
            $section = 'Radiology';
            $speciality = 'Radiologist';
        } elseif ($section_ === 'LB') {
            $section = 'Laboratory';
            $speciality = 'Laboratory Scientist';
        } else {
            $section = $row['section'];
            $speciality = $row['speciality'];
        }

        // Merge all rights and data into session
        $_SESSION = array_merge($_SESSION, [
            'section'       => $section,
            'speciality'    => $speciality,
            'create1'       => $row['create1'],
            'price'         => $row['price'],
            'request'       => $row['request'],
            'specimen'      => $row['specimen'],
            'enter'         => $row['enter'],
            'approve'       => $row['approve'],
            'stock'         => $row['stock'],
            'inventory'     => $row['inventory'],
            'report'        => $row['report'],
            'users'         => $row['users'],
            'oncredit'      => $row['oncredit'],
            'oncredit_adm'  => $row['oncredit_adm'],
            'sms'           => $row['sms'],
            'equipmnt'      => $row['equipmnt'],
            'inv'           => $row['inv'],
            'bill'          => $row['bill'],
            'view_lab_result' => $row['view_lab_result'],
            'edit_appr'     => $row['edit_appr']
        ]);
    }
}

function fetchUserData($db, $username, $lastInsertedId)
{
    $stmt = $db->prepare("SELECT * FROM pharm_users WHERE username = :username");
    $stmt->execute([':username' => $username]);

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION = array_merge($_SESSION, [
            'speciality' => $row['speciality'],
            'mgt_stock' => $row['mgt_stock'],
            'print_invoice' => $row['print_invoice'],
            'manage_price' => $row['manage_price'],
            'external_sale' => $row['external_sale'],
            'recve_cash' => $row['ext_sale_recieve_cash'],
            'dsp_oncredit' => $row['dsp_oncredit'],
            'view_consult_notes' => $row['view_consult_notes'],
            'edit_price_point_sale' => $row['edit_price_point_sale'],
            'reverse_drug' => $row['reverse_drug'],
            'last_login_id' => $lastInsertedId
        ]);
    }
}


$user_agent = $_SERVER['HTTP_USER_AGENT'];
$block_browser_status = 0;
if (strpos($user_agent, 'Edg') !== false) {
    //echo 'Microsoft Edge';
} elseif (strpos($user_agent, 'Firefox') !== false) {
    ///$block_browser_status = 1;
} elseif (strpos($user_agent, 'Chrome') !== false) {
    //echo 'Google Chrome';
} elseif (strpos($user_agent, 'Safari') !== false) {
    // echo 'Safari';
} elseif (strpos($user_agent, 'Opera') !== false) {
    // echo 'Opera';
} elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
    //  echo 'Internet Explorer';
    $block_browser_status = 1;
} else {
    $block_browser_status = 1;
    /// echo 'Unknown browser';
}

$stmt = $db->query("SELECT code,hx,wx,name,slider_text1,slider_text2,show_hospital_name_frontpage FROM hospital_details");
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $logo = $row['code'];
    $hx = $row['hx'];
    $wx = $row['wx'];
    $name_hos = $row['name'];
    $slider_text1 = $row['slider_text1'];
    $slider_text2 = $row['slider_text2'];
    $show_hospital_name_frontpage = $row['show_hospital_name_frontpage'];
}





if (isset($_POST['login'])) {

    //if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //	die(" CSRF token validation failed.");
    //}
    // Validate and sanitize input
    $super_admin = 0;
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $username_2 = filter_input(INPUT_POST, 'username_2', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $navigation = filter_input(INPUT_POST, 'navigation', FILTER_SANITIZE_STRING);
    if (empty($username) || empty($password)) {
        die("Username and password are required");
    }

    $password = md5(strtolower($password));

    if ($navigation === 'admin') {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt_check_admx = $db->prepare("SELECT * FROM admin_users_rights WHERE username = :username AND administrative_login=1");
            $stmt_check_admx->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt_check_admx->execute();
            if ($stmt_check_admx->rowCount() > 0) {
                $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = :username");
                $stmt->bindParam(':username', $username_2, PDO::PARAM_STR);
                $stmt->execute();
                $navigation = null;

                $action = 'ADMIN LOG IN AS';
                $desc = 'ADMIN LOG IN AS: ' . $username_2;
                $setdatetime = date("Y-m-d H:i:s");
                $sql = $db->prepare("INSERT INTO patient_staff_logs (descriptions,staff_name,action,date_and_time) 
VALUES (:descriptions,:staff_name,:action,:date_and_time)");
                $sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
                $sql->bindParam(':staff_name', $username, PDO::PARAM_STR);
                $sql->bindParam(':action', $action, PDO::PARAM_STR);
                $sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
                $sql->execute();
                $username = $username_2;
                $super_admin = 1;
            } else {
                $super_admin = 0;
                header("location:index.php?adm_right");
                exit;
            }
        }
    } else {

        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }
    $one = 1;



    if ($stmt->rowCount() > 0) {

        $row_s = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row_s['status'] == 0 && $row_s['rights'] != 'MD') {
            header("location:index.php?lock");
            exit;
        }
        $_SESSION['id'] = $row_s['id'];
        $_SESSION['fullname'] =  str_replace("'", "", trim($row_s["fullname"])); // stripslashes($row_s['fullname']);
        $_SESSION['title'] = $row_s['title'];
        $_SESSION['username'] = $row_s['username'];
        $_SESSION['email'] = $row_s['email'];
        $_SESSION['phone'] = $row_s['phone_number'];
        $_SESSION['rights'] = $row_s['rights'];
        $_SESSION['primary_rights'] = $row_s['rights'];
        $_SESSION['specialist'] = $row_s['specialist'];
        $_SESSION['status'] = $row_s['status'];
        $_SESSION['unit_head'] = $row_s['unit_head'];
        $_SESSION['EmployeeCode'] = $row_s['EmployeeCode'];
        $_SESSION['bill_account_status'] = $row_s['bill_account_status'];
        $_SESSION['Consult_Room'] = $row_s['Consult_Room'];
        $_SESSION['last_action'] = time();
        $_SESSION['password'] = $_POST['password'];
        $_SESSION['super_admin'] = $super_admin;

        ////-----------------------------------------------------------------			

        $employeeCode = $row_s['EmployeeCode'];

        $stmt2 = $db->prepare("
		SELECT 
			d.department, 
			d.sn AS dept_id, 
			d.department_type AS dept_group_name, 
			h.Designation, 
			d.dispensory, 
			d.requistn_disp_ByMainPharm_or_depensory, 
			d.view_patient_on_adm
		FROM hremp AS h
		INNER JOIN department AS d ON d.sn = h.Department
		WHERE h.EmployeeCode = :employeeCode
		");
        $stmt2->bindParam(':employeeCode', $employeeCode, PDO::PARAM_STR);
        $stmt2->execute();

        // Fetch data and set session values
        if ($row_ss = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['Designation'] = $row_ss['Designation'];
            $_SESSION['dept_name'] = $row_ss['department'];
            $_SESSION['dept_id'] = $row_ss['dept_id'];
            $_SESSION['dept_group_name'] = $row_ss['dept_group_name'];
            $_SESSION['dispensory'] = $row_ss['dispensory'];
            $_SESSION['view_patient_on_adm'] = $row_ss['view_patient_on_adm'];
            $_SESSION['main_or_dispensory_requisition'] = $row_ss['requistn_disp_ByMainPharm_or_depensory']; /// MLUTH requestssss

            if ($row_ss['dept_id'] == '') {
                header("location:index.php?emp_error");
                exit;
            }
        } else {
            header("location:index.php?emp_error");
            exit;
        }


        $stmt = $db->prepare("
		SELECT 
		*
		FROM hospital_details
		LIMIT 1
		");
        // Execute the prepared statement
        $stmt->execute();
        // Fetch the row and set session values
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['h_name'] = $row['name'];
            $_SESSION['h_address'] = $row['address'];
            $_SESSION['h_phone'] = $row['phones'];
            $_SESSION['h_code'] = $row['code'];
            $_SESSION['h_color_code_hex'] = $row['color_code_hex'];
            $h_code = $row['code'];
            $_SESSION['drug_reversal_period'] = $row['drug_reversal_period'];
            $_SESSION['investigation_edit_period'] = $row['investigation_edit_period'];
            $_SESSION['billing_remarks'] = $row['billing_remarks'];
            $_SESSION['payfrom_status'] = $row['payfrom_status'];
            $_SESSION['dialysis_visible'] = $row['dialysis'];
            $_SESSION['ivf'] = $row['ivf'];
            $_SESSION['allow_part_pay_medical_service'] = $row['allow_part_pay_medical_service'];
            $_SESSION['credit_limit_status'] = $row['credit_limit_status']; /// General Depos
            $_SESSION['b4_approve_requisition_setup'] = $row['b4_approve_requisition_setup'];
            $_SESSION['pharm_request_from_store'] = $row['pharm_request_from_store'];
            $_SESSION['total_family_member_allow'] = $row['total_family_member_allow'];
            $_SESSION['col1'] = $row['col1'];
            $_SESSION['col2'] = $row['col2'];
            $_SESSION['col3'] = $row['col3'];  /// webmedic subscription due flash set 1 to disable the flash on front page .... 
            $_SESSION['col4'] = $row['col4'];   //// use this for show lab investigation should display based on staff department
            $_SESSION['col5'] = $row['col5'];  ///// Use this HMO DRUG VALIDATION STATUS SET 1 MEANS PHARMACY TO VALIDATE NOT HMO DESKOFFICES 
            $logo = $row['code'];
            $_SESSION['notify_pharm'] = $row['notify_pharm'];
            $_SESSION['notify_lab'] = $row['notify_lab'];
            $_SESSION['hmo__notification'] = $row['hmo__notification'];
            $_SESSION['credit_discharge_notification'] = $row['credit_discharge_notification'];
        }

        /// invsti_users
        $log_rights = $_SESSION['rights'];

        if ($row_s['rights'] == 'LB') {
            // Use a single query with direct condition check
            $stmt2 = $db->prepare("SELECT section FROM invsti_users WHERE username = :username AND LOWER(section) = 'laboratory' LIMIT 1");
            $stmt2->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt2->execute();
            // Directly set rights based on whether row was found
            $log_rights = $stmt2->fetch(PDO::FETCH_ASSOC) ? 'LB' : 'RD';
        }


        // Prepare and execute securely
        $stmt2 = $db->prepare("SELECT pharmacy,nursing,lab_image_unit FROM admin_users_rights WHERE username = :username LIMIT 1");
        $stmt2->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt2->execute();
        $row_invst = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($row_invst) {
            $lab_image_unit      = isset($row_invst['lab_image_unit'])      ? $row_invst['lab_image_unit']      : 0;
            $pharmacy = isset($row_invst['pharmacy']) ? $row_invst['pharmacy'] : 0;
            $nursing  = isset($row_invst['nursing'])  ? $row_invst['nursing']  : 0;
        } else {
            $lab_image_unit = 0;
            $pharmacy = 0;
            $nursing = 0;
        }
        ///}


        $setdate = date("Y-m-d H:i:s");
        $delete = $db->prepare("UPDATE admin_users SET date_updated = '$setdate' WHERE username = ? ");
        $deleted = $delete->execute(array($username));

        // Prepare the SQL statement
        $add_row = "INSERT INTO admin_users_logs (username, fullname, Log_in, rights, doctor_id, emp_code) VALUES (:username, :fullname, :log_in, :rights, :doctor_id, :emp_code)";
        $stmt = $db->prepare($add_row);

        // Bind parameters
        $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
        $stmt->bindParam(':fullname', $_SESSION['fullname'], PDO::PARAM_STR);
        $stmt->bindParam(':log_in', $setdate, PDO::PARAM_STR);
        $stmt->bindParam(':rights', $log_rights, PDO::PARAM_STR);
        $stmt->bindParam(':doctor_id', $_SESSION['id'], PDO::PARAM_STR);
        $stmt->bindParam(':emp_code', $_SESSION['EmployeeCode'], PDO::PARAM_STR);
        $stmt->execute();
        $lastInsertedId = $db->lastInsertId();

        if ($row_s['rights'] == 'LB' or $row_s['specialist'] == 'Radiologist') {
            $_SESSION['navigate'] = 'investigations';
            $_SESSION['rights'] = 'LB';
            $_SESSION['section_'] = 'LB';

            $get_invst = fetchInvstiUserData($db, $username);

            header("location:investigations/mgt.php");
        } elseif ($row_s['rights'] == 'PH') {
            $get_pharmacy = fetchUserData($db, $username, $lastInsertedId);
            $admin = fetchAdminUserRights($db, $username);
            if ($block_browser_status == 1) {
                header("location:index.php?browser_error");
            } else {
                $_SESSION['navigate'] = 'pharmacy';
                header("location:pharmacy/index.php");
            }
        } elseif ($row_s['rights'] == 'NS') {
            if ($block_browser_status == 1) {
                header("location:index.php?browser_error");
            } else {
                $_SESSION['navigate'] = 'nursing';
                header("location:nursing/index.php");
            }
        } elseif ($row_s['rights'] == 'DR' or $row_s['rights'] == 'PY') {

            ///include_once('navigation.php');
            if ($block_browser_status == 1) {
                header("location:index.php?browser_error");
            } else {
                $_SESSION['navigate'] = 'doctor';
                header("location:doctor/index.php");
            }
        } else {
            ////////////=============================== NAVIGATION	
            if ($navigation != '') {
                if ($navigation == 'RAD' and $lab_image_unit == 1) {
                    $_SESSION['navigate'] = 'investigations';
                    $_SESSION['rights'] = 'LB';
                    $_SESSION['section_'] = 'RAD';
                    $get_invst = fetchInvstiUserData($db, $username);

                    header("location:investigations/mgt.php");
                    exit;
                } elseif ($navigation == 'LB' and $lab_image_unit == 1) {
                    $_SESSION['navigate'] = 'investigations';
                    $_SESSION['rights'] = 'LB';
                    $_SESSION['section_'] = 'LB';
                    $get_invst = fetchInvstiUserData($db, $username);

                    header("location:investigations/mgt.php");
                    exit;
                } elseif ($navigation == 'PH' and $pharmacy == 1) {
                    if ($block_browser_status == 1) {
                        header("location:index.php?browser_error");
                    } else {


                        // Get department ID for 'pharmacy'
                        $stmt2 = $db->prepare("SELECT sn FROM department WHERE department = 'pharmacy' LIMIT 1");
                        $stmt2->execute();

                        if ($row_ss = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                            $_SESSION['dept_id'] = $row_ss['sn'];
                        }


                        $_SESSION['navigate'] = 'pharmacy';
                        $_SESSION['rights'] = 'PH';
                        $get_pharmacy = fetchUserData($db, $username, $lastInsertedId);
                        $admin = fetchAdminUserRights($db, $username);

                        header("location:pharmacy/index.php");
                    }
                    exit;
                } elseif ($navigation == 'NS' and $nursing == 1) {
                    if ($block_browser_status == 1) {
                        header("location:index.php?browser_error");
                    } else {
                        $_SESSION['rights'] = 'NS';
                        $_SESSION['navigate'] = 'nursing';
                        header("location:nursing/index.php");
                    }
                    exit;
                } else {

                    echo '<div class="alert alert-danger">
					<h3> Invalid Right to Navigate Selected Module! </h3>
				</div>';
                    echo '<a href="index.php">Close</a>';
                    exit;
                }
            }

            ////========================================= ///////////////////////////////////=================

            if ($block_browser_status == 1) {
                header("location:index.php?browser_error");
            } else {
                $_SESSION['navigate'] = 'admin';
                if ($_SESSION['dept_id'] == '') {
                    header("location:index.php?emp_error");
                } else {
                    if ($row_s['rights'] == 'ST') {
                        header("location:admin/index.php");
                    } elseif ($row_s['rights'] == 'CA') {
                        header("location:billing/index.php?main");
                    } else {
                        header("location:admin/index.php");
                    }
                }

                $bill = fetchBillerUserData($db, $username);
                $admin = fetchAdminUserRights($db, $username);
                $get_invst = fetchInvstiUserData($db, $username);
            }
        }
    } else {
        sleep(2);
        header('location:index.php?err');
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>WebMEDIC | Login</title>

    <!-- Bootstrap core CSS -->
    <link href="index/css/bootstrap.min.css" rel="stylesheet">

    <link href="index/css/animate.min.css" rel="stylesheet">

    <link href="index/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="index/css/style.css" rel="stylesheet">
</head>

<body id="page-top">
    <div class="navbar-wrapper">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header page-scroll">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <img src="img/webmedic_logo.png" alt="laptop" width="25%" height="10%" class="navbar-brand" />

                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a class="page-scroll" href="#page-top">Home</a></li>
                        <li><a class="page-scroll" href="#features">Features</a></li>
                        <li><a class="page-scroll" href="#contact">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <div id="inSlider" class="carousel carousel-fade" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#inSlider" data-slide-to="0" class="active"></li>
            <li data-target="#inSlider" data-slide-to="1"></li>
        </ol>
        <div class="carousel-inner" role="listbox">
            <div class="item active">
                <div class="container">
                    <div class="carousel-caption">
                        <?= $slider_text1; ?>
                    </div>
                    <div class="carousel-image wow zoomIn">
                        <img src="img/<?= $logo . '_laptop'; ?>.png" alt="laptop" width="336px" height="200px" />
                    </div>
                </div>
                <!-- Set background for slide in css -->
                <div class="header-back one"></div>

            </div>
            <div class="item">
                <div class="container">
                    <div class="carousel-caption blank">
                        <?= $slider_text2; ?>
                    </div>
                </div>
                <!-- Set background for slide in css -->
                <div class="header-back two"></div>
            </div>
        </div>

    </div>


    <section id="features" class="container services">

        <?php
        $login_button_visiblity = 0;

        ?>
        <div class="row">


            <div class=" col-sm-5 b-r">
                <h5 class="font-bold"><b>LOGIN: </b></h5>

                <?php
                foreach ($messages as $msg): ?>
                    <div class="alert alert-danger fade-alert">
                        <p><?= $msg ?></p>
                    </div>
                <?php endforeach; ?>



                <form class="m-t" role="form" action="index.php" method="post">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Type username here" required data-required-message="Please enter a valid Username" value="" maxlength="60">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Type password here" required data-minlength-message="Password should have at least 6 characters." data-required-message="Please enter a valid Password" value="" maxlength="30">
                    </div>
                    <div class="form-group">
                        <label for="reg_select">Navigation</label>
                        <select name="navigation" id="navigation" class="form-control">
                            <option selected="selected" value="">Select or Skip</option>
                            <option value="RAD">Radiology</option>
                            <option value="PH">Pharmacy</option>
                            <option value="LB">Lab</option>
                            <option value="NS">Nursing</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div class="form-group" id="admin" style="display: none;">
                        <input type="text" class="form-control" name="username_2" maxlength="60" placeholder="Enter Login Username">
                    </div>

                    <script>
                        document.getElementById("navigation").addEventListener("change", function() {
                            var adminDiv = document.getElementById("admin");
                            if (this.value === "admin") {
                                adminDiv.style.display = "block";
                            } else {
                                adminDiv.style.display = "none";
                            }
                        });
                    </script>


                    <?php if ($login_button_visiblity == 0) { ?>
                        <button type="submit" class="btn btn-primary block full-width m-b" name="login">Login</button>
                    <?php } else {
                        echo '<h4 style="color:red;">Login disabled due to subscription payment issues.</h4>';
                    } ?>

                    &nbsp;:&nbsp;

                    <a href="#">
                        <small>Forgot password?</small>
                    </a>

                    <br>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                </form>
            </div>

            <div class="col-sm-1">
            </div>
            <div class="col-sm-6">
                <h5 class="font-bold"><b>USER'S TIPS: </b></h5>
                <?php
                $stmt = $db->query("SELECT my_tips, fullname FROM admin_users WHERE my_tips != '' AND status = '1' ORDER BY RAND() LIMIT 1");
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo htmlspecialchars($row['my_tips']);
                    echo '<br><strong>' . htmlspecialchars($row['fullname']) . '</strong>';
                } else {
                    echo "Be the first to share tips! Add from your profile page.";
                }

                ?>
            </div>


        </div>
    </section>

    <footer class="text-center py-3 border-top">
        <?php if ($logo === 'xyz'): ?>
            <!-- No footer text for xyz -->
        <?php elseif ($logo === 'police'): ?>
            <p class="m-t">
                <small>Powered by <strong>HEALTH BRIDGE</strong></small>
            </p>
        <?php else: ?>
            <p class="m-t">
                <small>Prosoft Multi Systems (Webmedic) &copy; <?= date("Y"); ?></small>
            </p>
        <?php endif; ?>
    </footer>

    <script src="index/js/jquery-2.1.1.js"></script>
    <script src="index/js/pace.min.js"></script>
    <script src="index/js/bootstrap.min.js"></script>
    <script src="index/js/classie.js"></script>
    <script src="index/js/cbpAnimatedHeader.js"></script>
    <script src="index/js/wow.min.js"></script>
    <script src="index/js/inspinia.js"></script>
</body>

</html>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            document.querySelectorAll(".fade-alert").forEach(el => {
                el.style.transition = "opacity 0.8s ease";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 800); // remove after fade
            });
        }, 5000); // 5 seconds delay
    });
</script>

<?php
include('notify_cleanup.php');
?>