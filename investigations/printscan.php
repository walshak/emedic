<?php include("../Connections/Conn.php");
session_start();
if (!defined('staff_p')) {
    define('staff_p', '../uploads/staff/');
}
?>

<?php


if (isset($_POST['generate_bill'])) {
    $hospital_no = $_POST['hospital_no'];

    $ledger_TX = 1;
    $sql = $db->prepare("INSERT INTO patient_ap_bill_group (ledger_id) VALUES (:ledger_id)");
    $sql->bindParam(':ledger_id', $ledger_TX, PDO::PARAM_STR);

    // Execute the statement and check if it was successful
    if ($sql->execute()) {
        // Get the last inserted ID
        $last_id = $db->lastInsertId();

        // Check if last_id is greater than zero
        if ($last_id > 0) {
            $last_id = str_pad($last_id, 3, "0", STR_PAD_LEFT);


            if ($last_id != '') {
                if (isset($_POST['inv_bill']) && is_array($_POST['inv_bill'])) {
                    $selectedRequests = $_POST['inv_bill'];

                    // Loop through each selected request
                    foreach ($selectedRequests as $request) {

                        $updateSQL = "UPDATE lab_manage SET bill = :bill WHERE labrequest_no = :labrequest_no AND bill is null";
                        $stmt_update = $db->prepare($updateSQL);

                        // Bind parameters
                        $stmt_update->bindParam(':bill', $last_id, PDO::PARAM_STR);
                        $stmt_update->bindParam(':labrequest_no', $request, PDO::PARAM_STR);
                        $stmt_update->execute();
                    }

                    header("Location: mgt.php?hosp_no=$hospital_no");
?>

<?php  } else {
                    echo '<h2>No Item checkboxes selected.</h2>';
                    echo '<a href="mgt.php">Return</a>';
                }
            } else {
                echo 'Empty Lab Number';
            }
        }
    } else {
        // Handle the error if the execution failed
        echo "Insertion failed: " . implode(", ", $sql->errorInfo());
    }
    exit;
}



if (isset($_GET["i"])) {
    $item_to_search = $_GET["i"];
    $type_patient = "IN";
    $labrequest_no = $_GET['i'];
} elseif (isset($_GET["e"])) {
    $item_to_search = $_GET["e"];
    $type_patient = "EX";
    $labrequest_no = $_GET['e'];
}
$stmt = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :item_to_search");
$stmt->bindParam(':item_to_search', $item_to_search, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $roww = $stmt->fetch(PDO::FETCH_ASSOC);
    $hosp_no = $roww['patient'];
} else {
    // header("Location: mgt.php");
    // exit;

}




if (isset($_GET["i"])) {
    $sms = "i";
    $stmtx = $db->prepare("SELECT phone, insurance, gender, nationality, addr, dob, email FROM enrollee WHERE hospital_no = :hosp_no");
    $stmtx->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
    $stmtx->execute();

    if ($stmtx->rowCount() > 0) {
        $roww2 = $stmtx->fetch(PDO::FETCH_ASSOC);
        $nat = $roww2['nationality'];
        $gender = $roww2['gender'];
        $addr = $roww2['addr'];
        $insurance = $roww2['insurance'];
        $phone = $roww2['phone'];
        $birthDate = $roww2['dob'];
        $email = $roww2['email'] ?? '';
    } else {
        var_dump($hosp_no);
        die('hdhdh');
        header("Location: mgt.php");
        exit;
    }
}



if (isset($_GET["e"])) {
    $sms = "e";
    $stmtx = $db->prepare("SELECT phone, gender, address, dob FROM pharm_ext WHERE transc_code = :hosp_no");
    $stmtx->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
    $stmtx->execute();

    if ($stmtx->rowCount() > 0) {
        $roww2 = $stmtx->fetch(PDO::FETCH_ASSOC);
        $gender = $roww2['gender'];
        $addr = $roww2['address'];
        $insurance = 'Private(Self Pay)';
        $phone = $roww2['phone'];
        $birthDate = $roww2['dob'];
        $nat = '';
    } else {
        header("Location: mgt.php");
        exit;
    }
}


date_default_timezone_set('Africa/Lagos');
$Current_date = date('Y-m-d');
$date1 = new DateTime($Current_date);
$date2 = new DateTime($birthDate);
$diff = $date2->diff($date1);
$age = $diff->format('%y');

?>


<!DOCTYPE html>
<html>
<style>
    .td_s {
        padding-right: 60px;
        padding-bottom: 10px;
    }
</style>
<?php include("../inc/header.php"); ?>


<body>

    <div id="wrapper">

        <?php include("../inc/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>

            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-8">
                    <h2>Investigation Report</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li>
                            <a href="mgt.php">Managment</a>
                        </li>
                        <li class="active">
                            <strong>Scan/Imaging Results</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-4">
                    <div class="title-action">
                        <?php /*?>  <a href="sms.php?<?php echo $sms.'=' . $item_to_search .'&scan'; ?>" class="btn btn-white btn-xs"><i class="fa fa-reply-all "></i> SMS Result </a><?php */ ?>
                        <?php if ($_SESSION['unit_head'] == 1) { ?> <a href="printscan.php?i=<?php echo $labrequest_no . '&old'; ?>" class="btn btn-danger btn-xs">Old Result </a> <?php } ?>
                        <input type="button" onClick="fun()" id="a1b" target="_blank" class="btn btn-success btn-xs" value="eMail Result" />
                        <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report" />
                        <a href="mgt.php?hosp_no=<?php echo $hosp_no; ?>" class="btn btn-info btn-xs" title="Close & Return to Previous Page"><i class="fa fa-arrow-circle-o-right"></i>Return</a>

                    </div>
                </div>
            </div>


            <div class="row" id="content">
                <div class="col-lg-12">
                    <div class="wrapper wrapper-content animated fadeInRight main-res-content">
                        <?php
                        function adjustBrightness($hex, $factor)
                        {
                            $hex = str_replace('#', '', $hex);

                            // Convert to RGB
                            $r = hexdec(substr($hex, 0, 2));
                            $g = hexdec(substr($hex, 2, 2));
                            $b = hexdec(substr($hex, 4, 2));

                            // Adjust brightness
                            $r = max(0, min(255, $r * $factor));
                            $g = max(0, min(255, $g * $factor));
                            $b = max(0, min(255, $b * $factor));

                            // Create lighter/darker color
                            $r = str_pad(dechex((int)$r), 2, '0', STR_PAD_LEFT);
                            $g = str_pad(dechex((int)$g), 2, '0', STR_PAD_LEFT);
                            $b = str_pad(dechex((int)$b), 2, '0', STR_PAD_LEFT);

                            return '#' . $r . $g . $b;
                        }
                        ?>
                        <?php
                        $h_theme_color = '';
                        if (!empty($_SESSION['h_color_code_hex'])) {
                            $h_theme_color = $_SESSION['h_color_code_hex'];
                        } elseif (isset($db)) {
                            $h_stmt = $db->query("SELECT color_code_hex FROM hospital_details LIMIT 1");
                            if ($h_stmt && $h_row = $h_stmt->fetch(PDO::FETCH_ASSOC)) {
                                if (!empty($h_row['color_code_hex'])) {
                                    $h_theme_color = $h_row['color_code_hex'];
                                }
                            }
                        }
                        if (empty($h_theme_color)) {
                            $h_theme_color = '#1ab394';
                        }
                        ?>
                        <div class="ibox-content p-xl">
                            <!-- Header with logo and hospital info -->
                            <div class="row">
                                <table width="100%">
                                    <tr>
                                        <td width="50%">
                                            <img alt="hospital logo" src="../img/logo.png" style="max-height: 80px;">
                                        </td>
                                        <td width="50%">
                                            <div class="hospital-info pull-right" style="border-left: 3px solid <?php echo $h_theme_color; ?>; padding-left: 15px;">
                                                <h3 style="margin-bottom: 5px; color: <?php echo $h_theme_color; ?>;"><?php echo $_SESSION['h_name'] ?></h3>
                                                <div style="color: #777;">
                                                    <?php echo $_SESSION['h_address']; ?><br>
                                                    <?php echo $_SESSION['h_phone']; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Report Title -->
                            <div align="center" style="margin: 20px 0;">
                                <h2 style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; font-weight: bold; padding: 10px 15px; border-radius: 4px; margin: 0; display: inline-block; width: 100%;"><?php echo !empty($roww['section']) ? htmlspecialchars($roww['section']) : 'Investigation'; ?> Report</h2>
                            </div>

                            <!-- Patient Information -->
                            <div class="patient-info" style="margin-bottom: 20px;">
                                <table cellpadding="6" cellspacing="0" class="table table-bordered" style="font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%;">
                                    <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; font-weight: bold;">
                                        <td width="15%" style="color: #ffffff !important; padding: 8px;"><strong>Patient Name:</strong></td>
                                        <td width="40%" style="color: #ffffff !important; padding: 8px;"><?php echo htmlspecialchars($roww['patient_name'] ?? ''); ?></td>
                                        <td width="15%" style="color: #ffffff !important; padding: 8px;"><strong>Sex:</strong> &nbsp; <?php echo htmlspecialchars($gender ?? ''); ?></td>
                                        <td width="30%" style="color: #ffffff !important; padding: 8px;"><strong>Age:</strong> &nbsp; <?php echo htmlspecialchars($age ?? ''); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px;"><strong>Patient No:</strong></td>
                                        <td style="padding: 8px;"><?php echo htmlspecialchars($roww['patient'] ?? ''); ?></td>
                                        <td colspan="2" style="padding: 8px;"><strong>Requesting Physician:&nbsp;</strong><?php echo htmlspecialchars(($roww['requesting_physician'] ?? '') ?: ($roww['request_by'] ?? '')); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px;"><strong>Address / Phone:</strong></td>
                                        <td colspan="3" style="padding: 8px;"><?php echo htmlspecialchars($addr ?? ''); ?> / <?php echo htmlspecialchars($phone ?? ''); ?></td>
                                    </tr>
                                </table>

                                <table cellpadding="6" cellspacing="0" class="table table-bordered" style="font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%; margin-top: 10px;">
                                    <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; font-weight: bold;">
                                        <td style="color: #ffffff !important; padding: 8px;"><strong>Investigation Requested:</strong> &nbsp; <?php echo htmlspecialchars($roww['test_name'] ?? ''); ?></td>
                                        <td style="color: #ffffff !important; padding: 8px;"><strong>Requested Date:</strong> &nbsp;<?php echo !empty($roww['request_date']) ? date('d-m-Y', strtotime($roww['request_date'])) : '-'; ?></td>
                                        <td style="color: #ffffff !important; padding: 8px;"><strong>Result Date:</strong> &nbsp;<?php echo !empty($roww['result_date']) ? date('d-m-Y', strtotime($roww['result_date'])) : '-'; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <hr style="border-top: 1px solid <?php echo $h_theme_color; ?>;">

                            <!-- Test Results Section -->
                            <?php
                            if (isset($_GET['old'])) {
                                $lab_no = $roww['labrequest_no'];
                                $stmtx = $db->prepare("SELECT * FROM lab_result_old WHERE lab_no = :lab_no ORDER BY sn ASC");
                                $stmtx->execute([':lab_no' => $lab_no]);
                                if ($stmtx->rowCount() > 0) {
                                    while ($roww2 = $stmtx->fetch(PDO::FETCH_ASSOC)) {
                                        $result_note = $roww2['field_value'];
                                        $result_date = $roww2['result_date'];
                                        $entered_by = $roww2['entered_by'];
                            ?>
                                        <div class="test-result" style="margin-bottom: 20px; border: 1px solid <?php echo $h_theme_color; ?>; border-radius: 4px; padding: 15px;">
                                            <div style="color: red; border-left: 4px solid red; padding: 10px; margin-bottom: 15px;">
                                                <h2 style="color:red;">Previous Reports EDITED (NOT TO BE USED)</h2>
                                            </div>
                                            <div style="padding: 10px;">
                                                <?php echo $result_note; ?><br>
                                                <?php
                                                $formatted_date = date('d F Y', strtotime($result_date));
                                                echo '<b>Result Date/Captured by: </b>' . $formatted_date . ' / ' . $entered_by;
                                                ?>
                                            </div>
                                        </div>
                                <?php }
                                }
                            } else {
                                $lab_no = $roww['labrequest_no'];

                                // Query lab_result
                                $stmt_res = $db->prepare("SELECT * FROM lab_result WHERE lab_no = :lab_no ORDER BY sn ASC");
                                $stmt_res->execute([':lab_no' => $lab_no]);
                                $lab_results = $stmt_res->fetchAll(PDO::FETCH_ASSOC);

                                // Query lab_scan_input_results
                                $stmt_inp = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :lab_no ORDER BY sn ASC");
                                $stmt_inp->execute([':lab_no' => $lab_no]);
                                $input_results = $stmt_inp->fetchAll(PDO::FETCH_ASSOC);

                                // Query lab_scan_fields for test template info
                                $field_type = '';
                                if (!empty($roww['test_id'])) {
                                    $stmt_fld = $db->prepare("SELECT field_type FROM lab_scan_fields WHERE test_no = :test_no LIMIT 1");
                                    $stmt_fld->execute([':test_no' => $roww['test_id']]);
                                    if ($f_row = $stmt_fld->fetch(PDO::FETCH_ASSOC)) {
                                        $field_type = $f_row['field_type'];
                                    }
                                }

                                $is_html_report = false;
                                $report_html_content = '';

                                if (!empty($lab_results)) {
                                    $single_row = (count($lab_results) === 1);
                                    $first_val = $lab_results[0]['field_value'] ?? '';
                                    $first_name = trim($lab_results[0]['field_name'] ?? '');
                                    $first_ref = trim($lab_results[0]['field_ref'] ?? '');
                                    $has_html = ($first_val !== strip_tags($first_val));

                                    if ($single_row && ($has_html || (empty($first_name) && empty($first_ref)))) {
                                        $is_html_report = true;
                                        $report_html_content = $first_val;
                                    }
                                } elseif (empty($input_results)) {
                                    $raw_note = trim($roww['result_note'] ?? '');
                                    if (!empty($raw_note) && $raw_note !== strip_tags($raw_note)) {
                                        $is_html_report = true;
                                        $report_html_content = $raw_note;
                                    }
                                }
                                ?>

                                <div class="test-result-container" style="margin-bottom: 20px;">
                                    <?php if ($is_html_report): ?>
                                        <style>
                                            .native-html-report table {
                                                width: 100% !important;
                                                max-width: 100% !important;
                                                float: none !important;
                                                margin-left: 0 !important;
                                                margin-right: 0 !important;
                                                margin-bottom: 15px !important;
                                                border-collapse: collapse !important;
                                                table-layout: auto !important;
                                            }
                                            .native-html-report table td, .native-html-report table th {
                                                border: 1px solid #ccc !important;
                                                padding: 8px 12px !important;
                                                word-wrap: break-word !important;
                                            }
                                            .native-html-report table tr:first-child td, .native-html-report table tr:first-child th {
                                                background-color: <?php echo $h_theme_color; ?> !important;
                                                color: #ffffff !important;
                                                font-weight: bold !important;
                                            }
                                            .native-html-report table tr:first-child p, .native-html-report table tr:first-child span {
                                                color: #ffffff !important;
                                            }
                                            .native-html-report::after {
                                                content: "";
                                                display: block;
                                                clear: both;
                                            }
                                        </style>
                                        <div class="native-html-report" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #fff; font-size: 14px; line-height: 1.6; color: #222; overflow: hidden; clear: both;">
                                            <?php echo $report_html_content; ?>
                                        </div>
                                    <?php elseif (!empty($lab_results)): ?>
                                        <table class="table table-bordered table-striped" style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; font-family: Arial, Helvetica, sans-serif;">
                                             <thead>
                                                <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important;">
                                                    <th width="35%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-size: 13px; font-weight: bold;">Test Parameter / Component</th>
                                                    <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-size: 13px; font-weight: bold;">Result Value</th>
                                                    <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-size: 13px; font-weight: bold;">Reference Range</th>
                                                    <th width="15%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-size: 13px; font-weight: bold; text-align: center;">Flag / Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($lab_results as $res_row): 
                                                    $fname = !empty($res_row['field_name']) ? $res_row['field_name'] : $roww['test_name'];
                                                    $fval = htmlspecialchars($res_row['field_value'] ?? '');
                                                    $fref = htmlspecialchars($res_row['field_ref'] ?? '');
                                                    $comment = trim($res_row['comment'] ?? '');

                                                    $flag_badge = '-';
                                                    if (!empty($comment)) {
                                                        if (preg_match('/\b(H|High)\b/i', $comment)) {
                                                            $flag_badge = '<span style="background-color:#ed5565; color:white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">High</span>';
                                                        } elseif (preg_match('/\b(L|Low)\b/i', $comment)) {
                                                            $flag_badge = '<span style="background-color:#f8ac59; color:white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">Low</span>';
                                                        } elseif (preg_match('/\b(N|Normal)\b/i', $comment)) {
                                                            $flag_badge = '<span style="background-color:#1ab394; color:white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">Normal</span>';
                                                        } else {
                                                            $flag_badge = '<span style="background-color:#23c6c8; color:white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">' . htmlspecialchars($comment) . '</span>';
                                                        }
                                                    }
                                                ?>
                                                    <tr>
                                                        <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #222;"><?php echo htmlspecialchars($fname); ?></td>
                                                        <td style="padding: 8px; border: 1px solid #ddd; color: #222;"><?php echo $fval !== '' ? $fval : '-'; ?></td>
                                                        <td style="padding: 8px; border: 1px solid #ddd; color: #222;"><?php echo $fref !== '' ? $fref : '-'; ?></td>
                                                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $flag_badge; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                    <?php elseif (!empty($input_results)): ?>
                                        <table class="table table-bordered table-striped" style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; font-family: Arial, Helvetica, sans-serif;">
                                             <thead>
                                                <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important;">
                                                    <th width="40%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold;">Test Parameter</th>
                                                    <th width="30%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold;">Result Value</th>
                                                    <th width="30%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold;">Expected Value / Reference</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($input_results as $inp_row): ?>
                                                    <tr>
                                                        <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #222;"><?php echo htmlspecialchars($inp_row['value_title']); ?></td>
                                                        <td style="padding: 8px; border: 1px solid #ddd; color: #222;"><?php echo htmlspecialchars($inp_row['result']); ?></td>
                                                        <td style="padding: 8px; border: 1px solid #ddd; color: #222;"><?php echo htmlspecialchars($inp_row['value_ref'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                    <?php else: 
                                        $raw_note = trim($roww['result_note'] ?? '');
                                        if (!empty($raw_note)):
                                            $lines = array_filter(array_map('trim', explode("\n", $raw_note)));
                                            $parsed_rows = [];
                                            $is_structured = true;
                                            foreach ($lines as $line) {
                                                if (preg_match('/^([^:]+):\s*([^(]+?)(?:\s*\(Ref:\s*([^)]+)\))?(?:\s*\[([^\]]+)\])?$/i', $line, $m)) {
                                                    $parsed_rows[] = [
                                                        'name' => trim($m[1]),
                                                        'value' => trim($m[2]),
                                                        'ref' => isset($m[3]) ? trim($m[3]) : '',
                                                        'flag' => isset($m[4]) ? trim($m[4]) : ''
                                                    ];
                                                } else {
                                                    $is_structured = false;
                                                    break;
                                                }
                                            }
                                            if ($is_structured && !empty($parsed_rows)): ?>
                                                <table class="table table-bordered table-striped" style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; font-family: Arial, Helvetica, sans-serif;">
                                                     <thead>
                                                        <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important;">
                                                            <th width="35%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold;">Test Parameter</th>
                                                            <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold;">Result Value</th>
                                                            <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold;">Reference Range</th>
                                                            <th width="15%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 10px; border: 1px solid #1a242f; font-weight: bold; text-align: center;">Flag / Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($parsed_rows as $p_row): 
                                                            $flag_badge = '-';
                                                            if (!empty($p_row['flag'])) {
                                                                if (preg_match('/\b(H|High)\b/i', $p_row['flag'])) {
                                                                    $flag_badge = '<span style="background-color:#ed5565; color:white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">High</span>';
                                                                } elseif (preg_match('/\b(L|Low)\b/i', $p_row['flag'])) {
                                                                    $flag_badge = '<span style="background-color:#f8ac59; color:white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">Low</span>';
                                                                } else {
                                                                    $flag_badge = '<span style="background-color:#1ab394; color:white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">' . htmlspecialchars($p_row['flag']) . '</span>';
                                                                }
                                                            }
                                                        ?>
                                                            <tr>
                                                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #222;"><?php echo htmlspecialchars($p_row['name']); ?></td>
                                                                <td style="padding: 8px; border: 1px solid #ddd; color: #222;"><?php echo htmlspecialchars($p_row['value']); ?></td>
                                                                <td style="padding: 8px; border: 1px solid #ddd; color: #222;"><?php echo !empty($p_row['ref']) ? htmlspecialchars($p_row['ref']) : '-'; ?></td>
                                                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $flag_badge; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <div style="border: 1px solid <?php echo $h_theme_color; ?>; border-radius: 4px; padding: 15px; background-color: #f9f9f9; font-size: 14px; line-height: 1.6; color: #222;">
                                                    <?php echo nl2br(htmlspecialchars($raw_note)); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="alert alert-warning">No result recorded yet for this investigation.</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <?php 
                                $show_outcome = !empty($roww['abnormal_results']);
                                $show_comment = !empty($roww['result_comment']);
                                $show_attachment = !empty($roww['attachment']);

                                if ($show_outcome || $show_comment || $show_attachment): 
                                ?>
                                    <div style="clear: both; display: block; overflow: hidden; margin-top: 20px; margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background-color: #f9f9f9; font-size: 13px; font-family: Arial, Helvetica, sans-serif;">
                                        <?php if ($show_outcome): ?>
                                            <div style="margin-bottom: 5px;">
                                                <strong>Result Outcome:</strong> 
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-weight: bold; color: white; background-color: <?php echo (in_array(strtolower($roww['abnormal_results']), ['high', 'abnormal', 'critical']) ? '#ed5565' : (strtolower($roww['abnormal_results']) == 'low' ? '#f8ac59' : '#1ab394')); ?>;">
                                                    <?php echo htmlspecialchars($roww['abnormal_results']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($show_comment): ?>
                                            <div style="margin-bottom: 5px;">
                                                <strong>Comment / Notes:</strong> <?php echo nl2br(htmlspecialchars($roww['result_comment'])); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($show_attachment): 
                                            $att_ext = strtolower(pathinfo($roww['attachment'], PATHINFO_EXTENSION) ?: $roww['attachment']);
                                            $is_img = in_array($att_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        ?>
                                            <div>
                                                <strong>Attachment:</strong> <i class="fa fa-paperclip"></i> <a href="uploads/<?php echo htmlspecialchars($roww['labrequest_no'] . '.' . $roww['attachment']); ?>" target="_blank" style="color: #1ab394; font-weight: bold;">View/Download Attached Document (.<?php echo htmlspecialchars($roww['attachment']); ?>)</a>
                                                <?php if ($is_img): ?>
                                                    <div style="margin-top: 10px;">
                                                        <img src="uploads/<?php echo htmlspecialchars($roww['labrequest_no'] . '.' . $roww['attachment']); ?>" alt="Attachment Preview" style="max-width: 100%; max-height: 450px; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <hr style="border-top: 1px solid <?php echo $h_theme_color; ?>;">

                                <!-- Signature Section -->
                                <div style="margin-top: 30px; clear: both;">
                                    <?php
                                    $approved_by = $roww['approved_by'];
                                    $stmt = $db->query("Select username from admin_users where fullname like '%$approved_by%'");
                                    if ($stmt->rowCount() > 0) {
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $uname = $row['username'];
                                    ?>

                                        <br><br>
                                        <?php
                                        if ($_SESSION['h_code'] == 'zmkc' or $_SESSION['h_code'] == 'mluth') {
                                        } elseif ($_SESSION['h_code'] == 'RMS') {
                                            include("signatures.php");
                                        } else { ?>
                                            <div style="border-top: 2px solid <?php echo $_SESSION['h_color_code_hex'] ?>; width: 300px; float: right; padding: 10px; margin-bottom: 20px;">
                                                <div style="text-align: center;">
                                                    <strong style="font-size: 14px; color: <?php echo $_SESSION['h_color_code_hex'] ?>;"><?php echo $roww['approved_by']; ?></strong>
                                                    <div style="font-size: 12px;"><?php echo $roww['lab_sci_speciality']; ?></div>
                                                </div>
                                                <?php if (file_exists(staff_p . 'sign_' . $uname . '.' . 'jpg')) { ?>
                                                    <div style="text-align: center; margin-top: 10px;">
                                                        <img src="<?php echo staff_p . 'sign_' . $uname . '.' . 'jpg'; ?>" style="max-height: 70px; max-width: 150px;">
                                                    </div>
                                                <?php } ?>
                                            </div>

                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <div style="clear: both;"></div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <!-- <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body" id="modal_body">

                    <div id="">
                        <label><strong>Enter eMail Address: </strong></label>

                        <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="" required>
                        <br>


                        <button type="button" onClick="sent_rslt()" id="" class="btn btn-primary btn-sm"><i class="fa fa-mail-forward"></i>&nbsp; Send Result</button>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div> -->

    <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body" id="modal_body">
                    <div id="">
                        <input type="hidden" id="test_to_send" name="test_to_send" value="<?php echo is_array($test_req_ids ?? null) ? implode(',', $test_req_ids) : htmlspecialchars($labrequest_no ?? ''); ?>">
                        <label><strong>Enter eMail Address: </strong></label>
                        <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="<?= htmlspecialchars($email ?? ''); ?>" required>
                        <br>
                        
                        <label>
                            <input type="checkbox" id="send_sms" name="send_sms" value="yes"> <strong>Send SMS Notification</strong>
                        </label>
                        <div id="sms_phone_section" style="display:none; margin-top: 10px;">
                            <label><strong>Phone Number: </strong></label>
                            <input type="text" name="result_phone" id="result_phone" class="form-control" value="<?= $phone; ?>">
                        </div>
                        <br><br>
                        <div class="form-sep form-group">
                            <label for="service_to_use_instant_res">Use InstantResult NG</label>
                            <input type="radio" value="instant_res" name="service_to_use" id="service_to_use_instant_res" checked>
                            <br>
                            <label for="service_to_use_facility">Use Facility Mail Server</label>
                            <input type="radio" value="facility" name="service_to_use" id="service_to_use_facility">
                        </div>
                        <!-- New encryption option -->
                        <div class="form-sep form-group">
                            <label for="encrypt_pdf">
                                <input type="checkbox" id="encrypt_pdf" name="encrypt_pdf">
                                Password Protect PDF
                            </label>
                            <!-- Password input field that shows only when checkbox is checked -->
                            <div id="password_section" style="display: none; margin-top: 10px;">
                                <label for="pdf_password"><small>Default is patient hospital no</small></label>
                                <input type="password" id="pdf_password" name="pdf_password" value="<?php echo $hosp_no ?>" class="form-control" placeholder="Enter password for PDF">
                            </div>
                        </div>
                        <span id="send_res_status_str_good" style="color:green;"></span>
                        <br>
                        <span id="send_res_status_str_bad" style="color:red;"></span>
                        <br>
                        <button type="button" id="send_res_btn" onClick="sent_rslt()" class="btn btn-primary btn-sm"><i class="fa fa-mail-forward"></i>&nbsp; Send Result</button>
                    </div>

                    <script>
                        document.getElementById('encrypt_pdf').addEventListener('change', function() {
                            var passwordSection = document.getElementById('password_section');
                            passwordSection.style.display = this.checked ? 'block' : 'none';
                        });

                        document.getElementById('send_sms').addEventListener('change', function() {
                            var phoneSection = document.getElementById('sms_phone_section');
                            phoneSection.style.display = this.checked ? 'block' : 'none';
                        });
                    </script>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <?php include("search_modal.php") ?>
    <?php include("../inc/footer_scripts.php"); ?>


    <script>
        function fun() {
            $("#send_mdl").modal('show');
        }

        // function sent_rslt() {
        //     var text1 = document.getElementById("content");
        //     var text2 = document.getElementById("content");

        //     function emailIsValid(email) {
        //         return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        //     }

        //     var email = document.getElementById("result_email_address").value;
        //     var err = emailIsValid(email); // false	
        //     if (err) {} else {
        //         alert('Invalid eMail Address!');
        //         exit;
        //     }



        //     ///alert(text1.innerHTML);

        //     $.ajax({
        //         url: "send-email.php",
        //         data: {
        //             text2: text1.innerHTML,
        //             email: email
        //         },
        //         type: 'POST',
        //         success: function(response) {
        //             alert(response);
        //         }
        //     });
        // }
    </script>
    <script>
        function sent_rslt() {
            var email = document.getElementById("result_email_address").value;
            var test_to_send = document.getElementById("test_to_send").value;
            var encrypt_pdf = document.getElementById("encrypt_pdf").checked ? 'yes' : 'no';
            var pdf_password = document.getElementById("pdf_password").value;
            var send_sms = document.getElementById("send_sms").checked ? 'yes' : 'no';
            var phone = document.getElementById("result_phone").value;
            var hosp_no = "<?= $hosp_no; ?>";
            var labrequest_no = "<?= $labrequest_no; ?>";
            var type_patient = "<?= $type_patient; ?>";
            var service_to_use = document.querySelector('input[name="service_to_use"]:checked').value;




            function emailIsValid(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            if (!emailIsValid(email)) {
                //alert('Invalid eMail Address!');
                $('#send_res_status_str_bad').text('Invalid eMail Address! ');
                return;
            }
            $('#send_res_btn').attr('disabled', true);
            $('#send_res_status_str_good').text('Sending result...please wait!');
            $.ajax({
                url: "send-email-instant-result.php",
                data: {
                    hosp_no: hosp_no,
                    labrequest_no: labrequest_no,
                    type_patient: type_patient,
                    email: email,
                    phone: phone,
                    send_sms: send_sms,
                    test_to_send: labrequest_no,
                    service_to_use: service_to_use,
                    encrypt_pdf: encrypt_pdf,
                    pdf_password: pdf_password,
                },
                type: 'POST',
                success: function(response) {
                    //alert(response);
                    console.log(response);
                    $('#send_res_btn').attr('disabled', false);
                    $('#send_res_status_str_good').text(response);
                    $('#send_res_status_str_bad').text('');
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    //alert('An error occurred: ' + error);
                    $('#send_res_btn').attr('disabled', false);
                    $('#send_res_status_str_bad').text('An error occurred: ' + error);
                    $('#send_res_status_str_good').text('');
                }
            });
        }
    </script>
    <script>
        function Clickheretoprint() {
            var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
            var content_vlue = document.getElementById("content").innerHTML;

            var docprint = window.open("", "", disp_setting);
            docprint.document.open();

            // Add print-specific CSS to force background colors and images
            var printCSS = `
                <style>
                    @media print {
                        * {
                            -webkit-print-color-adjust: exact !important; /* Chrome/Safari/Edge */
                            color-adjust: exact !important;               /* Firefox */
                            print-color-adjust: exact !important;         /* Future standard */
                        }
                        body {
                            width: 800px; 
                            font-size: 13px; 
                            font-family: arial;
                        }
                    }
                </style>
            `;

            docprint.document.write('<html><head>' + printCSS + '</head>');
            docprint.document.write('<body onLoad="self.print()">');
            docprint.document.write(content_vlue);
            docprint.document.write('</body></html>');
            docprint.document.close();
            docprint.focus();
        }
    </script>

    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

    <script>
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
    </script>

</body>

</html>