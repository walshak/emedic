<?php include("../Connections/Conn.php"); ?>
<?php
ob_start();
session_start();

$username = $_SESSION['username'];


$last_login_id = $_SESSION['last_login_id'];
$setdate = date("Y-m-d H:i:s");

$update = "UPDATE admin_users_logs SET Log_out = :setdate WHERE sn = :last_login_id";
$stmt = $db->prepare($update);
$stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR);
$stmt->bindParam(':last_login_id', $last_login_id, PDO::PARAM_INT);
$stmt->execute();

unset($_SESSION['username']);
unset($_SESSION['rights']);
unset($_SESSION['fullname']);
unset($_SESSION['specialist']);
unset($_SESSION['inventory']);
unset($_SESSION['navigate']);
unset($_SESSION['dept_name']);
unset($_SESSION['dept_id']);
unset($_SESSION['dept_group_name']);

session_destroy();
//header('location:index.php');

$stmt = $db->prepare("SELECT * FROM admin_users WHERE username = :username");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    session_start(); // Start session if not already started
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
    $_SESSION['rights'] = 'DR';
    $_SESSION['primary_rights'] = $row_s['rights'];
    $_SESSION['specialist'] = $row_s['specialist'];
    $_SESSION['status'] = $row_s['status'];
    $_SESSION['unit_head'] = $row_s['unit_head'];
    $_SESSION['EmployeeCode'] = $row_s['EmployeeCode'];
    $_SESSION['bill_account_status'] = $row_s['bill_account_status'];
    $_SESSION['Consult_Room'] = $row_s['Consult_Room'];
    $_SESSION['last_action'] = time();
    $_SESSION['password'] = $_POST['password'];

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
        $_SESSION['inpatient_credit_limit'] = $row['inpatient_credit_limit'];
        $_SESSION['allow_part_pay_medical_service'] = $row['allow_part_pay_medical_service'];
        $_SESSION['credit_limit_status'] = $row['credit_limit_status'];
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
    }

    /// invsti_users
    $log_rights = $_SESSION['rights'];

    $_SESSION['navigate'] = 'doctor';
    header("location:../doctor/index.php");
}





?>