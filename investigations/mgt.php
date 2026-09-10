<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('../doctor/objects.php');
require_once(__DIR__ . '/../inc/lis/LisDriverFactory.php');
$lisEnabled = LisDriverFactory::isLisEnabled($db);
///include("../inc/credit_current_balance.php");
if ($_SESSION['Designation'] == 'Radiologist') {
    $_SESSION['section'] = 'Radiology';
}
?>

<?php
session_start();

if (strtoupper($_SESSION['password']) == 'STAFF123') {
    header("location:../profile/index.php?profile=$uname&changepassword");
}

$error_sel = 0;

if (isset($_POST['bio_data_id'])) {
    echo 'jjjjjjjjjjjjjjjjjjjjjjjjjjjjj';
}



if (isset($_GET['clear_field']) && isset($_GET['labrequest_no'])) {
    $allowed_fields = array('collected_by', 'collected_notes');

    $field = $_GET['clear_field'];
    $labrequest_no = $_GET['labrequest_no'];

    if (in_array($field, $allowed_fields)) {
        // Verify ownership
        $stmt = $db->prepare("SELECT collected_by FROM lab_manage WHERE labrequest_no = :labrequest_no");
        $stmt->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && isset($_SESSION['fullname']) && $_SESSION['fullname'] === $row['collected_by']) {
            // Clear all related collection data
            $stmt_clear = $db->prepare("
                UPDATE lab_manage SET 
                    collected_by = NULL, 
                    collected_date = NULL, 
                    collected_specimen = NULL, 
                    collected_notes = '', 
                    data_capture_status = 'queue' 
                WHERE labrequest_no = :labrequest_no
            ");
            if ($stmt_clear->execute(array(':labrequest_no' => $labrequest_no))) {
                echo "<script>alert('Collection info cleared successfully.'); location.href = location.pathname + '?labrequest_no=" . urlencode($labrequest_no) . "';</script>";
                exit;
            } else {
                echo "<script>alert('Failed to clear collection info.');</script>";
            }
        } else {
            echo "<script>alert('You are not authorized to clear this data.');</script>";
        }
    }
}

if (isset($_POST['uploadDocumentBtn'])) {

    // Basic form fields
    $title            = trim($_POST['title']);
    $related_table_id = isset($_POST['related_table_id']) ? $_POST['related_table_id'] : '';
    $related_table    = isset($_POST['related_table']) ? $_POST['related_table'] : '';
    $hospital_no      = isset($_POST['hospital_no']) ? $_POST['hospital_no'] : '';
    $module           = isset($_POST['module']) ? $_POST['module'] : '';
    $created_by       = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

    $error_status     = 1;
    $error_msg        = "Oops! Something went wrong";

    // Upload directory
    $target_dir = "../documents/patients/";
    if (!file_exists($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    // Check if hospital_no and file are valid
    try {

        if (!empty($hospital_no) && isset($_FILES['document_file']['tmp_name']) && !empty($_FILES['document_file']['name'])) {

            $filename = preg_replace('/\s+/', '', $title . '_' . basename($_FILES["document_file"]["name"]));
            $target_file = $target_dir . $hospital_no . '_' . $filename;

            $file_type = '';
            $info = pathinfo($target_file);

            if (isset($info['extension'])) {
                $file_type = strtolower($info['extension']);
            }

            $allowed = array('pdf', 'jpg', 'jpeg', 'png');

            if (in_array($file_type, $allowed)) {

                $tmp_name = $_FILES['document_file']['tmp_name'];
                $link     = $target_file;

                if (move_uploaded_file($tmp_name, $target_file)) {

                    $today_date = date('Y-m-d');
                    $table = 'patients_documents';

                    $stmt = $db->prepare("SELECT id FROM $table WHERE hospital_no=? AND title=? AND created_at LIKE ? AND status='1'");
                    $stmt->execute(array($hospital_no, $title, "$today_date%"));

                    if ($stmt->rowCount() == 0) {

                        $stmt = $db->prepare("
                        INSERT INTO $table
                        (hospital_no,title,link,file_type,module,related_table,related_table_id,created_by)
                        VALUES (?,?,?,?,?,?,?,?)
                    ");

                        if ($stmt->execute(array(
                            $hospital_no,
                            $title,
                            $link,
                            $file_type,
                            $module,
                            $related_table,
                            $related_table_id,
                            $created_by
                        ))) {

                            echo "<div class='alert alert-success'>✅ Document uploaded successfully.</div>";
                        } else {

                            echo "<div class='alert alert-warning'>⚠️ Database insert failed. Please try again.</div>";
                        }
                    } else {

                        echo "<div class='alert alert-warning'>⚠️ Document already uploaded today.</div>";
                    }
                } else {

                    echo "<div class='alert alert-danger'>❌ Could not move uploaded file. Check folder permissions.</div>";
                }
            } else {

                echo "<div class='alert alert-warning'>⚠️ Invalid file type. Only PDF, JPG, JPEG, and PNG allowed.</div>";
            }
        } else {

            echo "<div class='alert alert-warning'>⚠️ Patient ID or file missing.</div>";
        }
    } catch (PDOException $e) {

        echo "<div class='alert alert-danger'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }


    // Return JSON for AJAX
    ///echo json_encode(array('status' => $error_status, 'message' => $error_msg));
}

if (isset($_POST['apply_all'])) {
    if (!empty($_REQUEST['appprove_all'])) {

        $SelectedItems = $_REQUEST['appprove_all'];

        for ($i = 0; $i < count($SelectedItems); $i++) {
            $RQ_No = $SelectedItems[$i];

            include("apr_combl.php");
            $setdatetime = date("Y-m-d H:i:s a");
            $status = "approve";
            $zero = "0";
            $stmt = $db->prepare("UPDATE lab_manage SET abnormal_results = :abnormal_results, data_capture_status = :data_capture_status, approved_by = :approved_by, result_date = :result_date WHERE labrequest_no = :labrequest_no");

            $stmt->bindValue(':abnormal_results', $zero, PDO::PARAM_STR);
            $stmt->bindValue(':data_capture_status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':approved_by', $_SESSION["fullname"], PDO::PARAM_STR);
            $stmt->bindValue(':result_date', $setdatetime, PDO::PARAM_STR);
            $stmt->bindValue(':labrequest_no', $RQ_No, PDO::PARAM_STR);

            $stmt->execute();
        }

        header("location:mgt.php?rp");
    } else {
        $error_sel = 1;
    }
}

if (isset($_GET['cp'])) {
    $Request_ID = $_GET['cp']; //, FILTER_SANITIZE_STRING);
    $setdate = date('Y-m-d H:i:s');

    $stmt = $db->prepare("UPDATE lab_manage SET collected_by = :collected_by, collected_date = :collected_date, data_capture_status = :data_capture_status WHERE labrequest_no = :labrequest_no");
    $stmt->bindValue(':collected_by', $_SESSION["fullname"], PDO::PARAM_STR);
    $stmt->bindValue(':collected_date', $setdate, PDO::PARAM_STR);
    $stmt->bindValue(':data_capture_status', 'capture', PDO::PARAM_STR);
    $stmt->bindValue(':labrequest_no', $Request_ID, PDO::PARAM_STR);
    $stmt->execute();
}

if (isset($_GET['delRQ'])) {

    $Request_ID = $_GET['delRQ'];
    $fullname = $_SESSION['fullname'];

    // First, fetch the current record to check constraints
    $stmt = $db->prepare("SELECT request_date, lab_sci_name, approved_by,entered_by FROM lab_manage WHERE labrequest_no = :labrequest_no");
    $stmt->bindValue(':labrequest_no', $Request_ID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $requestDate = new DateTime($row['request_date']);
        $today = new DateTime();
        $interval = $requestDate->diff($today)->days;

        if ($interval <= 30 && ($row['lab_sci_name'] == $fullname or $row['approved_by'] == $fullname or $row['entered_by'] == $fullname)) {
            $updateStmt = $db->prepare("UPDATE lab_manage SET attachment = '' WHERE labrequest_no = :labrequest_no");
            $updateStmt->bindValue(':labrequest_no', $Request_ID, PDO::PARAM_STR);
            $updateStmt->execute();
        } else {
            // Optionally handle rejection here
            echo "<script>alert('Update not allowed due to time or user constraints.');</script>";
        }
    }
}


if (isset($_GET['third_party_c'])) {
    $third_party_c = base64_decode($_GET['third_party_c']);

    $update = $db->prepare("UPDATE patients_documents SET status = '0' WHERE id = ?");
    $updated = $update->execute(array($third_party_c));

    if ($updated && $update->rowCount() > 0) {
        //// echo "Status updated successfully.";
        echo "<div class='alert alert-success'>✅ Document Deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ No update made. Either ID not found or status already set to 0.</div>";
    }
}

if (isset($_GET['aE'])) {
    $Request_ID = $_GET['aE']; ///, FILTER_SANITIZE_STRING);

    $stmt = $db->prepare("UPDATE lab_manage SET result_date = :result_date, approved_by = :approved_by, data_capture_status = :data_capture_status WHERE labrequest_no = :labrequest_no");
    $stmt->bindValue(':result_date', '', PDO::PARAM_STR);
    $stmt->bindValue(':approved_by', '', PDO::PARAM_STR);
    $stmt->bindValue(':data_capture_status', 'result', PDO::PARAM_STR);
    $stmt->bindValue(':labrequest_no', $Request_ID, PDO::PARAM_STR);
    $stmt->execute();
?>
    <script>
        alert('Click the Requests to Approve link to Edit Request');
    </script>
<?php
}



if (isset($_POST['add_request'])) {
    include_once("insert.php");
    $sub = Add_lab_request();
}

/// SET ALL TO ZEROOOO

$approve = '';
$queue = '';
$slab = '';
$cancelreq = '';
$prices = '';
$admission = '';

if (isset($_POST['apply_approve']) or isset($_GET["ap_rf"]) or isset($_GET["rp"]) or $error_sel == 1) {
    $approve = 'active';
} elseif (isset($_POST["apply"]) or isset($_POST["open_image"]) or  isset($_GET["slab"])) {
    $slab = 'active';
} elseif (isset($_GET["refresh"])) {
    $cancelreq = 'active';
} elseif (isset($_POST["apply_date"])) {
    $sdate = 'active';
} elseif (isset($_GET["pid"])) {
    $prices = 'active';
} else {
    $queue = 'active';
}
?>



<!DOCTYPE html>
<html>

<?php include("../inc/header.php");


if ($_SESSION['col4'] == "1") {
    $dept_id = $_SESSION['dept_id'];
    $sort_by_dept = " AND lab_cat='$dept_id'";
} else {
    $sort_by_dept = "";
}

if (isset($_GET['url'])) {
    $url = $_GET['url'];
}
?>



<body>

    <div id="wrapper">

        <?php include("../inc/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>


            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-9">
                    <h2>Investigation Management</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li class="active">
                            <strong>Investigation</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-3">
                    <br>

                    <div id="labAlertBox"
                        title="Click to view details"
                        style="display:none; cursor:pointer; background:#f0fff0; padding:12px; border-radius:6px; border:1px solid #d4edda; transition:0.3s;">

                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <strong>🧪 Lab Requests</strong>
                            <span style="font-size:12px; color:#007bff;">👉 Click to view</span>
                        </div>



                        <table width="100%">
                            <tr>
                                <td>🚶 Outpatients</td>
                                <td><strong id="lab_outpatient">0</strong></td>
                            </tr>
                            <tr>
                                <td>🏥 Inpatients</td>
                                <td><strong id="lab_inpatient">0</strong></td>
                            </tr>
                            <tr id="lis_result_row" style="display:none;">
                                <td>📥 LIS Results</td>
                                <td><strong id="lab_lis_unseen" class="text-danger">0</strong></td>
                            </tr>
                        </table>

                    </div>


                </div>
            </div>
            <?php
            $hospital_number = '';

            if (isset($_GET['hosp_no']) && !empty($_GET['hosp_no'])) {
                $hospital_number = trim($_GET['hosp_no']);
            } elseif (isset($_POST['hospital_no']) && !empty($_POST['hospital_no'])) {
                $hospital_number = trim($_POST['hospital_no']);
            }

            $stmt = $db->prepare("SELECT * FROM tbl_patient_alerts WHERE hospital_no = :hospital_no");
            $stmt->bindValue(':hospital_no', $hospital_number, PDO::PARAM_STR);
            $stmt->execute();

            $alert_count = $stmt->rowCount();
            $rowxx = $stmt->fetch(PDO::FETCH_ASSOC);
            $alert_msg = $rowxx['alert'];


            ?>

            <div class="wrapper wrapper-content  animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5> Investigation Tabs</h5>
                                <div class="ibox-tools">
                                    <?php if ($_SESSION['Designation'] == 'Radiologist') { ?>
                                        <a href="logout_as_radiologist.php" class="btn btn-success btn-xs" style="font-size: 13px; color: white; ">Patient Consultation</a> &nbsp; : &nbsp;
                                    <?php }

                                    if (!empty($hospital_number)) {
                                        $refreshUrlWithHosp = "mgt.php?hosp_no={$hospital_number}";
                                    ?>
                                        <a href="<?= $refreshUrlWithHosp; ?>" class="btn btn-info btn-xs" style="font-size: 13px; color: white;">
                                            Refresh Patient
                                        </a>
                                    <?php
                                    }
                                    ?>


                                    <a href="mgt.php" class="btn btn-default btn-xs" style="font-size: 13px; color: white;">
                                        Refresh All
                                    </a>

                                    &nbsp;&nbsp;
                                    <?php if ($hospital_number != '' && $_SESSION['request'] == 1) {
                                        $emr = $hospital_number;
                                        $stmt_RQ = $db->query("SELECT patient, patient_name,business_service_center,referral FROM lab_manage where patient='$emr'");
                                        if ($stmt_RQ->rowCount() > 0) {
                                            $rowxx = $stmt_RQ->fetch(PDO::FETCH_ASSOC); ?>

                                            <input type="button" name="edit" style="font-size: 13px; color: white; " value="+ Add New Investigation Request" data-target="#myModal5" id="<?php echo $emr . '__' . $rowxx['patient_name'] . '__' . $rowxx['business_service_center'] . '__' . $rowxx['referral']; ?>" class="btn btn-danger btn-xs lab_request" />
                                        <?php } ?>
                                    <?php } ?>



                                </div>
                            </div>
                            <div class="ibox-content">

                                <div class="panel-options">
                                    <?php if ($lisEnabled): ?>
                                        <button type="button" class="btn btn-sm btn-info pull-right" style="margin-top: 5px;" onclick="syncLisResults(true)">
                                            <i class="fa fa-refresh"></i> Sync LIS Results
                                        </button>
                                    <?php endif; ?>
                                    <ul class="nav nav-tabs">
                                        <li class="<?php echo $queue; ?>"><a data-toggle="tab" href="#queue" style="font-size: 15px; color: black;"><i class="fa fa-angle-double-down"></i>Patients Investigation List</a></li>
                                        <li class="<?php echo $cancelrequest; ?>"><a data-toggle="tab" href="#cancelrequest" style="font-size: 15px; color: black;" onClick="adm_records()"><i class="fa fa-bed"></i>Patient On-Admission</a></li>
                                        <li class="<?php echo $prices; ?>"><a data-toggle="tab" href="#prices" style="font-size: 15px; color: black;"><i class="fa fa-shopping-cart"></i>Investigation Prices</a></li>
                                    </ul>
                                </div>

                                <?php
                                ///echo '====' . $row_invst['section'];
                                include("mgt_main.php");

                                if ($_SESSION['section'] == "") {
                                    $lab_mgt_where = "(section='Radiology' or section='Laboratory') ";
                                } else {
                                    $category = $_SESSION['section'];
                                    $lab_mgt_where = "section='$category' ";
                                }

                                if ($_SESSION['section'] == "") {
                                    $where = "(department_type='Radiology' or department_type='Laboratory') ";
                                } else {
                                    $category = $_SESSION['section'];
                                    $where = "department_type='$category' ";
                                }


                                $start = date("Y-m-d", strtotime($setdate . " -7 day"));
                                $end = date("Y-m-d");
                                $setdates = " and date(request_date) between '$start' and '$end'";
                                ?>

                                <div class="panel-body">
                                    <div class="tab-content">

                                        <!--  QUEUE TAB PANE ======================================================================                              
-->

                                        <?php
                                        $style = "display: none;";
                                        if (isset($_POST['apply_narrow_search_patient']) || isset($_POST['uploadDocumentBtn']) || (isset($_GET['hosp_no']) && $_GET['hosp_no'] != '')) {

                                            if (isset($_GET['hosp_no']) && $_GET['hosp_no'] != '') {
                                                $hospital_number = $_GET['hosp_no'];
                                            }

                                            if (isset($_POST['advance_filter']) && $_POST['advance_filter'] != '') {
                                                $style = "";
                                            } else {
                                                $style = "display: none;";
                                            }
                                            if (isset($_POST['hospital_no']) && $_POST['hospital_no'] != '') {
                                                $hospital_number = $_POST['hospital_no'];
                                            }


                                            $notes_status = null;
                                            $stmtss = $db->prepare("SELECT old_hospital_no, vip,surname,fname,oname,hmo_no FROM enrollee WHERE hospital_no = :hospital_no");
                                            $stmtss->bindParam(':hospital_no', $hospital_number);
                                            $stmtss->execute();
                                            $_count_stmtss = $stmtss->rowCount();

                                            if ($rowx = $stmtss->fetch(PDO::FETCH_ASSOC)) {
                                                $old_hospital_no = $rowx['old_hospital_no'];
                                                $hmo_no = $rowx['hmo_no'];
                                                $patient_fullname = $rowx['surname'] . ' ' . $rowx['fname'] . ' ' . $rowx['oname'];
                                                $vip_status = $rowx['vip']; // Retrieve the vip column

                                            } else {
                                                $notes_status = 'empty';
                                            }
                                        }


                                        ?>

                                        <div id="queue" class="tab-pane <?php echo $queue; ?>">
                                            <div class="row">

                                                <div class="col-lg-8">
                                                    <form method="post" action="mgt.php" name="form_me" id="form_me">

                                                        <table width="100%">
                                                            <tr>
                                                                <td width="40%">
                                                                    <label for="reg_input_no" class="" style="font-size: 15px; color: red; ">SEARCH FOR PATIENT:</label><br>
                                                                    <select id="search_for_patient" name="hospital_no" style="width:350px;">
                                                                        <option value="<?php echo $hospital_number; ?>" selected>
                                                                            <?php echo $hospital_number . ' ' . $patient_fullname; ?>
                                                                        </option>
                                                                    </select>

                                                                     <script>
                                                                        $(document).ready(function() {
                                                                            if (typeof $.fn.select2 !== 'undefined') {
                                                                                $('#search_for_patient').select2();
                                                                            }
                                                                        });
                                                                     </script>
                                                                </td>
                                                                <td width="25%">
                                                                    <label for="reg_input_no" class="">+ Advanced Filter (Optional)</label><br>
                                                                    <select name="advance_filter" id="advance_filter" class="form-control" data-required="true">
                                                                        <option value="" <?= (!isset($_POST['advance_filter']) || $_POST['advance_filter'] == '') ? 'selected' : '' ?>>View Advanced Filter</option>
                                                                        <option value="payment" <?= (isset($_POST['advance_filter']) && $_POST['advance_filter'] == 'payment') ? 'selected' : '' ?>>Queue Requests Paid/Posted</option>
                                                                        <option value="queue" <?= (isset($_POST['advance_filter']) && $_POST['advance_filter'] == 'queue') ? 'selected' : '' ?>>Queue Requests Un-Paid</option>
                                                                        <option value="approve" <?= (isset($_POST['advance_filter']) && $_POST['advance_filter'] == 'approve') ? 'selected' : '' ?>>Approved Results</option>
                                                                        <option value="result" <?= (isset($_POST['advance_filter']) && $_POST['advance_filter'] == 'result') ? 'selected' : '' ?>>Un-Approve Results</option>
                                                                    </select>


                                                                    <div id="date_range" style="<?= $style; ?>"> <!-- Hidden by default -->
                                                                        <label for="reg_input_no" id="label_date" style="color:red;">+ Set Dates Range:</label>
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td>
                                                                                    <input type="date" class="input-sm form-control" name="start_2"
                                                                                        value="<?php echo isset($_POST['start_2']) ? $_POST['start_2'] : date('Y-m-d'); ?>">
                                                                                </td>
                                                                                <td><span class="input-group-addon">to</span></td>
                                                                                <td>
                                                                                    <input type="date" class="input-sm form-control" name="end_2"
                                                                                        value="<?php echo isset($_POST['end_2']) ? $_POST['end_2'] : date('Y-m-d'); ?>">
                                                                                </td>
                                                                            </tr>
                                                                        </table>

                                                                    </div>

                                                                    <script>
                                                                        document.getElementById("advance_filter").addEventListener("change", function() {
                                                                            var lb;
                                                                            var selectedValue = this.value;
                                                                            var dateRangeDiv = document.getElementById("date_range");
                                                                            var advance_filter = document.getElementById("advance_filter").value;
                                                                            if (advance_filter === 'payment') {
                                                                                lb = '+ Set Payment Dates';
                                                                            } else if (advance_filter === 'queue') {
                                                                                lb = '+ Set Entry Dates';
                                                                            } else if (advance_filter === 'approve' ||
                                                                                advance_filter === 'result') {
                                                                                lb = '+ Set Result Dates';
                                                                            }

                                                                            if (selectedValue !== "") {
                                                                                dateRangeDiv.style.display = "block";
                                                                            } else {
                                                                                dateRangeDiv.style.display = "none";
                                                                            }

                                                                            document.getElementById("label_date").innerHTML = lb;

                                                                        });
                                                                    </script>

                                                                </td>


                                                                <td> <label for="reg_input_no" class="">Click Here</label><br>
                                                                    <button class="btn btn-primary btn-sm" type="submit" name="apply_narrow_search_patient">Search Patient</button>
                                                                </td>

                                                            </tr>
                                                        </table>
                                                    </form>
                                                </div>
                                                <div class="col-lg-4">
                                                    <form method="post" action="mgt.php" name="form_me" id="form_me">
                                                        <div class="form-sep" id="data_5">
                                                            <label for="reg_input_no" class="" style="color:red;">See Current's Request(s) or Set Dates Range:</label>
                                                            <table width="100%">
                                                                <tr>
                                                                    <td>
                                                                        <input type="date" class="input-sm form-control" name="start_"
                                                                            value="<?php echo isset($_POST['start_']) ? $_POST['start_'] : date('Y-m-d'); ?>" required>
                                                                    </td>
                                                                    <td><span class="input-group-addon">to</span></td>
                                                                    <td>
                                                                        <input type="date" class="input-sm form-control" name="end_"
                                                                            value="<?php echo isset($_POST['end_']) ? $_POST['end_'] : date('Y-m-d'); ?>" required>
                                                                    </td>

                                                                    <td>&nbsp;</td>
                                                                    <td><button class="btn btn-primary btn-sm" type="submit" name="apply_narrow_search">Apply</button></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>


                                            <?php

                                            ///// ZENTH ABUJA DONT WANT TO SEE THIS BELOW //////////////////////

                                            if ((isset($_POST['hospital_no']) and $_POST['hospital_no'] != '') or isset($_POST['apply_narrow_search']) or isset($_GET['url']) or (isset($_GET['hosp_no']) and $_GET['hosp_no'] != '')) {
                                                $user_type = $_SESSION['speciality'] == 'Administrator' || $_SESSION['speciality'] == 'Receptionist' ? 'user' : 'lab_img_user';
                                                $sub = queue($db, $sort_by_dept, $user_type, $old_hospital_no, $patient_fullname, $notes_status, $vip_status, $hospital_number, $url, $_count_stmtss, $hmo_no);
                                            }
                                            ?>
                                        </div>


                                        <div id="cancelrequest" class="tab-pane <?php echo $cancelreq; ?>">
                                            <div id="admission_display"></div>
                                        </div>


                                        <div id="prices" class="tab-pane <?php echo $prices; ?>">

                                            <strong style="color:#F00">What to do here ... </strong><br>
                                            Use the search to enquire about investigation prices list, department and insurance coverage here ...
                                            <hr>


                                            <?php
                                            $stmt_price = $db->query("SELECT * FROM department WHERE department_type='Laboratory' or department_type='Radiology'");
                                            ?>

                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">See Investigation Prices by Department</label>
                                                <select name="Department" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="Department" class="form-control" data-required="true">
                                                    <option selected="selected" value="">Select Investigation by Department...</option>

                                                    <?php while ($rw_price = $stmt_price->fetch(PDO::FETCH_ASSOC)) { ?>
                                                        <option value="<?php echo 'mgt.php?pid=' . $rw_price['sn']; ?>"><?php echo $rw_price["department"]; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <hr>

                                            <?php

                                            if (isset($_GET['pid'])) {    /// if t=dept id is set

                                                $dept_id = $_GET['pid'];
                                                $stmt_list = $db->prepare("SELECT lab.*, d.department FROM lab_scan AS lab INNER JOIN department AS d ON d.sn = lab.dept WHERE d.sn = :dept_id ORDER BY lab.sn");
                                                $stmt_list->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
                                                $stmt_list->execute();

                                                if ($stmt_list->rowCount() > 0) { ?>

                                                    <table class="table table-striped table-bordered table-hover dataTables-example">


                                                        <thead>
                                                            <tr>
                                                                <th data-toggle="true">No</th>
                                                                <th data-toggle="true">Investigation</th>
                                                                <th data-toggle="true">Department</th>
                                                                <th data-toggle="true">Covarage</th>
                                                                <th data-toggle="true">INS</th>
                                                                <th data-toggle="true">Hospital<br>Price</th>
                                                                <th data-toggle="true">External<br>Price</th>
                                                                <th data-toggle="true">NHIS<br>Price</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            $n = 1;
                                                            $data = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
                                                            foreach ($data as $key => $row) {
                                                                // while($row=$stmt_list->fetch(PDO::FETCH_ASSOC)) { 
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $row['sn'];
                                                                        $field = $row['sn']; ?></td>
                                                                    <td><?php echo $row['test']; ?></td>
                                                                    <td><?php echo $row['department']; ?></td>
                                                                    <td><?php echo $row['coverage']; ?></td>
                                                                    <td><?php if ($row['insurance_type'] == 1) {
                                                                            echo 'PRI';
                                                                        } elseif ($row['insurance_type'] == 2) {
                                                                            echo 'SEC';
                                                                        } else {
                                                                            echo '-';
                                                                        } ?></td>
                                                                    <td><?php echo $row['hosp_price']; ?></td>
                                                                    <td><?php echo $row['ext_price']; ?></td>
                                                                    <td><?php echo $row['nhis_price']; ?></td>


                                                                </tr>
                                                            <?php
                                                                $n++;
                                                            } ?>

                                                        </tbody>
                                                    </table>

                                            <?php } else {
                                                    echo ' <br>No Records Found';
                                                }
                                            }
                                            ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <?php include("../inc/lab_mdl.php"); ?>
                    <?php include("search_modal.php") ?>

                </div>

            </div>
        </div>

        <?php include("../inc/footer_scripts.php"); ?>

        <?php if ($alert_count > 0) {

            $hospital_no = $hospital_number;
            include("../inc/patient_alert.php");

        ?>

        <?php } ?>

        <?php if (isset($_GET['inr'])) { ?>
            <script>
                toastr.error('This patient is not currently under any Insurance status!', 'Error', {
                    timeOut: 5000
                })
            </script>
        <?php } ?>

        <?php if (isset($_GET['invalid_selection'])) { ?>
            <script>
                toastr.error('Invalid Selection! Tick the CheckBox to make a Selection!', 'Error', {
                    timeOut: 5000
                })
            </script>
        <?php } ?>

        <?php if (isset($_GET['sv'])) { ?>
            <script>
                toastr.success('Data Save Successfully', 'Saved', {
                    timeOut: 5000
                })
            </script>
        <?php } ?>

        <?php if (isset($_GET['rp'])) { ?>
            <script>
                toastr.success('Successful', 'Saved', {
                    timeOut: 5000
                })
            </script>
        <?php
            /// clear the message
            //header("location:mgt.php");

        } ?>
        <?php if ($error_sel == 1) { ?>
            <script>
                toastr.error('Select Investigation you want to approve!', 'Error', {
                    timeOut: 5000
                })
            </script>
        <?php } ?>





        <?php include('../modal_lock.php'); ?>
        <?php include('alert.php'); ?>
        <script>
            $('#reminder_test_mdl').modal('show');



            $(document).on('click', '.bio_data_link', function() {
                var bio_data_id = $(this).attr("id");

                if (bio_data_id != '') {
                    $.ajax({
                        url: "fetch_set.php",
                        method: "POST",
                        data: {
                            bio_data_id: bio_data_id
                        },
                        success: function(data) {

                            $('.modal-title').text('Patient Bio - Data');

                            $('#bio_data_body').html(data);
                            $('#bio_data_modal').modal('show');
                        }
                    });
                }
            });



            function uploadFile() {
                var formData = new FormData();
                var fileInput = document.getElementById('file_upload');
                var labRequestNo = document.getElementById('labrequest_no').value;

                // Check if a file is selected
                if (fileInput.files.length === 0) {
                    alert("Please select a file to upload.");
                    return;
                }

                // Append the file and lab request number to the FormData object
                formData.append('file', fileInput.files[0]);
                formData.append('labrequest_no', labRequestNo);

                // Create an AJAX request
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'upload_file.php', true); // Change 'upload_file.php' to your server-side upload script

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Parse the JSON response
                        var response = JSON.parse(xhr.responseText);
                        // Handle success or error based on the response
                        if (response.status === "success") {
                            alert(response.message);
                            // Optionally, you can refresh the modal content or perform other actions
                        } else {
                            alert(response.message);
                        }
                    } else {
                        alert("An error occurred while uploading the file.");
                    }
                };

                xhr.send(formData);
            }
        </script>

        <script>
            function submitCheckboxes() {
                // Get all checked checkboxes
                var selectedValues = $('input[name="inv_bill[]"]:checked').map(function() {
                    return $(this).val();
                }).get();

                // Check if any checkbox is selected
                if (selectedValues.length === 0) {
                    alert('Please select at least one checkbox.');
                    return;
                }

                // AJAX request
                $.ajax({
                    url: 'generate_bill.php', // Change this to your server-side script
                    type: 'POST',
                    data: {
                        inv: selectedValues,
                        csrf_token: 'your_csrf_token_here' // Include CSRF token if needed
                    },
                    beforeSend: function() {
                        // Show loading indicator
                        $('#loadingIndicator').show();
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            alert('Data submitted successfully: ' + data.message);
                        } catch (e) {
                            alert('Data submitted successfully, but response could not be parsed.');
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 404) {
                            alert('Requested resource not found.');
                        } else if (xhr.status === 500) {
                            alert('Internal server error. Please try again later.');
                        } else {
                            alert('An error occurred: ' + error);
                        }
                    },
                    complete: function() {
                        // Hide loading indicator
                        $('#loadingIndicator').hide();
                    }
                });
            }
        </script>


        <script src="../js/mgt.js"></script>



        <!-- Data Tables -->
        <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
        <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
        <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
        <script src="../js/plugins/datapicker/select2.full.min.js"></script>

        <link rel="stylesheet" href="../js/select2/css/select2.min.css">
        <script src="../js/select2/js/select2.min.js"></script>

        <script>
            function specimen_taken_() {

                var speciment_taken = document.getElementById('speciment_taken_collector').value
                var labrequest_no = document.getElementById('labrequest_no_taken').value
                var datetime = document.getElementById('datetime').value
                var collected_notes = document.getElementById('collected_notes').value
                var data_capture_status_ = document.getElementById('data_capture_status_').value
                $.ajax({
                    url: "enter_result_process.php",
                    method: "POST",
                    data: {
                        speciment_taken_: speciment_taken,
                        datetime: datetime,
                        data_capture_status_: data_capture_status_,
                        collected_notes: collected_notes,
                        labrequest_no_: labrequest_no
                    },
                    success: function(data) {
                        toastr.info(data, 'Attention', {
                            timeOut: 2000
                        })
                        $('#view_notes_modal').modal('hide');
                    }
                });

            }


            $(document).on('click', '.capture_take_spm', function() {
                var capture_take_spm = $(this).attr("id");

                if (capture_take_spm != '') {
                    $.ajax({
                        url: "fetch.php",
                        method: "POST",
                        data: {
                            capture_take_spm_id: capture_take_spm
                        },
                        success: function(data) {

                            $('.modal-title').text('Take Specimen / Scan: ' + capture_take_spm);
                            $('#view_notes_body').html(data);
                            $('#view_notes_modal').modal('show');
                        }
                    });
                }
            });

            function toggle_check() {
                var checkboxes = document.querySelectorAll('input[name="inv_bill_2[]"], input[name="inv[]"], .inv-checkbox');
                var button = document.querySelector('button[name="generate_bill"]');
                var lisBtn = document.getElementById('btn_send_lis_batch');

                var anyChecked = Array.prototype.some.call(checkboxes, function(chk) {
                    return chk.checked;
                });

                if (button) button.disabled = !anyChecked;
                if (lisBtn) lisBtn.disabled = !anyChecked;
            }

            function toggle_check_2() {
                // Get all checkboxes with name inv_bill_2[]
                /*  var checkboxes = document.querySelectorAll('input[name="inv[]"]');
                 var button = document.querySelector('button[name="display_result_print"]');

                 // Check if any checkbox is checked
                 var anyChecked = Array.prototype.some.call(checkboxes, function(chk) {
                     return chk.checked;
                 });

                 // Enable button if any checked, else disable
                 button.disabled = !anyChecked; */
            }
            /*//display_requester();

            function display_requester() {
                var requester = 100;

                $.ajax({
                    url: "get-requester.php",
                    data: {
                        requester_post: requester
                    },
                    type: 'POST',
                    success: function(response) {
                        $("#hospital_no_get_list").html(response);
                        // Initialize Chosen library after AJAX request has completed
                        $(".chosen-select").chosen();
                        // Update Chosen library to reflect new options
                        $(".chosen-select").trigger("chosen:updated");
                    }
                });
            }
*/


            $(document).ready(function() {

                // Internal Patient Search
                $('#search_for_patient').select2({
                    placeholder: 'Search and Select Patient',
                    minimumInputLength: 3,
                    ajax: {
                        url: 'get_ext_patients.php',
                        type: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term,
                                mode: 'all_patients' // Key difference
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });
            });

            function adm_records() {
                var patient_on_adm = 900;

                $.ajax({
                    url: "patient_on_admission.php",
                    method: "POST",
                    data: {
                        patient_on_adm: patient_on_adm
                    },
                    success: function(data) {
                        $("#admission_display").html(data);
                    }
                });

            }


            function send_reminder_all(catgeory) {

                $.ajax({
                    url: "../inc/send_remainder.php",
                    method: "POST",
                    data: {
                        send_reminder2_all: catgeory
                    },
                    success: function(data) {

                        toastr.info('Acknowledged!', 'REMINDER !! ', {
                            timeOut: 5000
                        })
                        $("#reminder_test_mdl").modal('hide')

                    }
                });
            }

            function send_reminder(sn) {
                $.ajax({
                    url: "../inc/send_remainder.php",
                    method: "POST",
                    data: {
                        send_reminder2: sn
                    },
                    success: function(data) {
                        toastr.info('Acknowledged!', 'REMINDER !! ', {
                            timeOut: 5000
                        })
                    }
                });
            }



            $(document).on('click', '#vista_notes_modal_btn', function() {
                $('#vista_notes_modal').modal('show')
                toastr.info('Please Wait .... ', 'Processing', {
                    timeOut: 5000
                })

                ///const notes_type = $(this).attr('arial-data');
                const hospital_no = $(this).attr('data-hospital-no');
                const notes_type = $(this).attr('data-notes-type');

                $.ajax({
                    url: '../doctor/_medication_hx.php',
                    method: "POST",
                    data: {
                        load_vistamedic_notes: true,
                        hospital_no: hospital_no,
                        notes_type: notes_type
                    },
                    success: function(response) {
                        $("#vista_notes_modal_body").html(response);

                        toastr.clear();
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });
            })


            $(document).ready(function() {
                $('.dataTables-example').dataTable({
                    responsive: true,
                    "dom": 'T<"clear">lfrtip',
                    "tableTools": {
                        "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                    }
                });

                /* Init DataTables */
                var oTable = $('#editable').dataTable();

                /* Apply the jEditable handlers to the table */
                if (typeof $.fn.editable !== 'undefined') {
                    oTable.$('td').editable('../example_ajax.php', {
                        "callback": function(sValue, y) {
                            var aPos = oTable.fnGetPosition(this);
                            oTable.fnUpdate(sValue, aPos[0], aPos[1]);
                        },
                        "submitdata": function(value, settings) {
                            return {
                                "row_id": this.parentNode.getAttribute('id'),
                                "column": oTable.fnGetPosition(this)[2]
                            };
                        },

                        "width": "90%",
                        "height": "100%"
                    });
                }


            });

            function fnClickAddRow() {
                $('#editable').dataTable().fnAddData([
                    "Custom row",
                    "New row",
                    "New row",
                    "New row",
                    "New row"
                ]);

            }

            $('#data_5 .input-daterange').datepicker({
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true
            });

            $(document).ready(function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $("#cCustomer").select2({
                        ajax: {
                            url: "fetch_labtest.php",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term, // search term
                                    page: params.page
                                };
                            },
                            processResults: function(data, page) {
                                return {
                                    results: data.items
                                };
                            },
                            cache: true
                        },
                        escapeMarkup: function(markup) {
                            return markup;
                        },
                        minimumInputLength: 1
                    });
                }
            });

            function payNow(sale_sn, target, hospital_no) {


                if (target == 'pharmacy') {
                    var rr = confirm("Are you sure you want to Pay from Patient's Deposit?");
                } else {
                    rr = true;
                }

                if (rr === true) {

                    document.getElementById('pay_now' + sale_sn).innerHTML = "Wait ...";
                    document.getElementById('pay_now' + sale_sn).disabled = true;

                    $.ajax({
                        url: "../payfrom_wallet.php",
                        method: "POST",
                        data: {
                            sale_sn: sale_sn
                        },
                        success: function(data) {

                            var jsonn = JSON.parse(data);
                            if (jsonn["status"] == 1) {

                                document.getElementById("pay_now" + sale_sn).innerHTML = 'Pay from Wallet';
                                document.getElementById('pay_now' + sale_sn).disabled = false;
                                toastr.error(jsonn["message"], 'Attention', {
                                    timeOut: 5000
                                })
                            } else {

                                document.getElementById("pay_now" + sale_sn).innerHTML = 'Refresh';
                                toastr.success('Successful! Close This Window And Open Again To Enter Result.', 'Success', {
                                    timeOut: 5000
                                })
                            }

                        }
                    });
                }
            }


            function add_more_commment() {
                var labRequestNo = document.getElementById('more_labrequest_no').value;
                var more_comment = document.getElementById('more_comment').value;
                var who_is_add = document.getElementById('who_is_add').value;
                $.ajax({
                    url: "fetch_labtest.php",
                    method: "POST",
                    data: {
                        add_more_commment: labRequestNo,
                        more_comment: more_comment,
                        who_is_add: who_is_add
                    },
                    success: function(data) {
                        toastr.info(data, 'Message', {
                            timeOut: 5000
                        })
                    }
                });

            }
        </script>



        <script>
            /* Toggle ALL row checkboxes */
            function toggleAllInv(source) {
                var checkboxes = document.querySelectorAll('input[name="inv_bill_2[]"], input[name="inv[]"], .inv-checkbox');
                checkboxes.forEach(function(chk) {
                    chk.checked = source.checked;
                });

                toggle_check();
            }

            /* Update header checkbox when rows are clicked */
            function updateSelectAll() {
                var checkboxes = document.querySelectorAll('.inv-checkbox');
                var selectAll = document.getElementById('select_all_inv');

                var allChecked = true;
                var anyChecked = false;

                checkboxes.forEach(function(chk) {
                    if (!chk.checked) allChecked = false;
                    if (chk.checked) anyChecked = true;
                });

                selectAll.checked = allChecked;
                toggleActionButton();
            }

            /* Enable / Disable button */
            function toggleActionButton() {
                var checkboxes = document.querySelectorAll('.inv-checkbox');
                var button = document.querySelector('button[name="display_result_print"]');

                var anyChecked = Array.from(checkboxes).some(chk => chk.checked);
                if (button) {
                    button.disabled = !anyChecked;
                }
            }
        </script>

        <script src="../js/idle.js"></script>



        <script>
            var prevOut = localStorage.getItem('lab_outpatient') ?
                parseInt(localStorage.getItem('lab_outpatient')) : 0;

            var prevIn = localStorage.getItem('lab_inpatient') ?
                parseInt(localStorage.getItem('lab_inpatient')) : 0;

            var prevLisUnseen = localStorage.getItem('lis_unseen_total') ?
                parseInt(localStorage.getItem('lis_unseen_total')) : 0;

            function checkLab() {
                $.ajax({
                    url: 'fetch_lab_count.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let outpatient = parseInt(data.outpatient) || 0;
                        let inpatient = parseInt(data.inpatient) || 0;
                        let lisUnseen = parseInt(data.lis_unseen_results) || 0;
                        let totalAlerts = outpatient + inpatient + lisUnseen;

                        if (totalAlerts > 0) {
                            $('#labAlertBox').fadeIn();
                            $('#lab_outpatient').text(outpatient);
                            $('#lab_inpatient').text(inpatient);

                            if (data.lis_enabled && lisUnseen > 0) {
                                $('#lis_result_row').show();
                                $('#lab_lis_unseen').text(lisUnseen);
                            } else {
                                $('#lis_result_row').hide();
                            }

                            // 🔊 SOUND TRIGGER for new requests or new unseen LIS results
                            if (outpatient > prevOut || inpatient > prevIn || lisUnseen > prevLisUnseen) {
                                let sound = new Audio('../sounds/notification.wav');
                                sound.play().catch(() => {});

                                $('#labAlertBox')
                                    .css('background', '#d4edda')
                                    .fadeOut(200).fadeIn(200)
                                    .fadeOut(200).fadeIn(200);

                                setTimeout(() => {
                                    $('#labAlertBox').css('background', '#f0fff0');
                                }, 3000);
                            }

                        } else {
                            $('#labAlertBox').fadeOut();
                        }

                        if (data.lis_enabled && typeof syncLisResults === 'function') {
                            syncLisResults(false);
                        }

                        prevOut = outpatient;
                        prevIn = inpatient;
                        prevLisUnseen = lisUnseen;

                        localStorage.setItem('lab_outpatient', outpatient);
                        localStorage.setItem('lab_inpatient', inpatient);
                        localStorage.setItem('lis_unseen_total', lisUnseen);
                    }
                });
            }


            // CLICK
            $('#labAlertBox').on('click', function() {

                $('#labModal').modal('show');

                $.ajax({
                    url: 'fetch_lab_list.php',
                    success: function(data) {
                        $('#lab_body').html(data);
                    }
                });

            });


            setInterval(checkLab, 10000);
            checkLab();
        </script>

        <script>
            window.retryLisDispatch = function(labrequestNo, testId, testName, patientNo) {
                if (typeof toastr !== 'undefined') {
                    toastr.info('Communicating with External LIS...', '', { timeOut: 3000 });
                }
                $.ajax({
                    url: 'lis_action.php',
                    method: 'POST',
                    data: {
                        action: 'dispatch',
                        labrequest_no: labrequestNo,
                        test_id: testId,
                        test_name: testName,
                        patient_no: patientNo
                    },
                    success: function(res) {
                        if (res && res.success) {
                            if (typeof toastr !== 'undefined') toastr.success(res.message, 'LIS Dispatch');
                            else alert(res.message);
                            if (typeof load_table === 'function') {
                                load_table();
                            } else {
                                location.reload();
                            }
                        } else {
                            var err = (res && res.error) ? res.error : 'Dispatch failed';
                            if (typeof toastr !== 'undefined') toastr.error(err, 'LIS Error');
                            else alert('LIS Error: ' + err);
                        }
                    },
                    error: function() {
                        if (typeof toastr !== 'undefined') toastr.error('Failed to connect to LIS service.', 'Network Error');
                        else alert('Network Error: Failed to connect to LIS service.');
                    }
                });
            };

            window.pollLisModal = function(btn) {
                var $btn = $(btn);
                var originalHtml = $btn.html();
                $btn.html('<i class="fa fa-spin fa-spinner"></i> Polling LIS...').prop('disabled', true);
                $.ajax({
                    url: 'lis_action.php',
                    method: 'POST',
                    data: { action: 'poll' },
                    success: function(res) {
                        $btn.html(originalHtml).prop('disabled', false);
                        if (res && res.success) {
                            if (typeof toastr !== 'undefined') toastr.success(res.message, 'LIS Poll Complete');
                            else alert(res.message);
                            if (typeof load_table === 'function') {
                                load_table();
                            } else {
                                location.reload();
                            }
                        } else {
                            var err = (res && res.error) ? res.error : 'Poll failed';
                            if (typeof toastr !== 'undefined') toastr.error(err, 'LIS Poll');
                            else alert('LIS Poll Error: ' + err);
                        }
                    },
                    error: function() {
                        $btn.html(originalHtml).prop('disabled', false);
                        if (typeof toastr !== 'undefined') toastr.error('Failed to sync with LIS.', 'Network Error');
                        else alert('Network Error: Failed to sync with LIS.');
                    }
                });
            };
        </script>
</body>

</html>