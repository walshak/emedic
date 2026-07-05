<?php session_start();
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');


$FormAction_ = $_SERVER['PHP_SELF'];
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


?>
<!DOCTYPE html>
<html>
<?php

include("../inc/header.php");

?>



<title>WebMedic | <?php if ($_SESSION['Designation'] != "") {
                        echo $_SESSION['Designation'];
                    } else {
                        echo $_SESSION['specialist'];
                    } ?></title>
<link rel="stylesheet" href="../css/rem.css">

<body>
    <div id="wrapper">
        <?php

        $rights = $_SESSION['rights'];

        if (isset($_GET['hr']) or isset($_GET['Cadre']) or isset($_GET['Designation']) or isset($_GET['Department']) or isset($_GET['Bank']) or isset($_GET['Add']) or isset($_GET['ED']) or isset($_GET['sal']) or isset($_GET['LV']) or isset($_GET['yLV']) or isset($_GET['rLV']) or isset($_GET['gpay']) or isset($_GET['srp']) or isset($_GET['slp']) or isset($_GET['eval']) or isset($_GET['evalCat']) or isset($_GET['rEval']) or isset($_GET['evalPeriod'])) {
            include("../inc/payroll_side_bar.php");
        } elseif ($rights == 'PH') {
            include("../pharmacy/nav_side.php");
        } elseif ($rights == 'LB') {
            include("../inc/nav_side.php");
        } elseif ($rights == 'NS') {
            include("../nursing/nav_side.php");
        } elseif ($rights == 'DR' or $rights == 'AD') {
            include("nav_side.php");
        } else {
            //include("../inc/nav_admin_side_bar.php"); 
            include("nav_side.php");
        }
        ?>



        <div id="page-wrapper" class="gray-bg">

            <?php include("nav_header.php"); ?>
            <script src="../js/jquery-2.1.1.js"></script>
            <div class="wrapper wrapper-content">
                <?php

                include_once('procedures/index.php');
                include('../modal_lock.php');
                ?>



            </div>

            <div id="resultsAlertBox" class="results-available-alert" style="display:none; cursor:pointer;">
                <div>
                    <b> 👉 View New Results</b>
                    <strong id="doctor_results_count">0</strong>
                </div>
            </div>
            <div id="doctorQueueAlert" class="queue-alert" style="display:none; cursor:pointer; background:#fff3cd; padding:10px; border-radius:5px;">
                <b>🧑‍⚕️ View Patients Waiting:</b>
                <strong id="doctor_queue_count">0</strong>
            </div>
            <?php include('new_results.php'); ?>

            <?php include("../inc/footer.php"); ?>
        </div>
    </div>
    <?php include("../inc/footer_scripts.php"); ?>
    <script>
        $(document).ready(function() {

            $('.footable').footable();
            $('.footable2').footable();

        });

        <?php

        if ($error_status == 1) { ?>
            toastr.error('<?php echo $error_msg ?>', 'Error', {
                timeOut: 5000
            })

        <?php } else if ($error_status == 2) {
        ?>
            toastr.success(' <?php echo $error_msg ?> ', 'Success', {
                timeOut: 5000
            })
        <?php

        } elseif (isset($_GET['hosp_no'])) { ?>
            let sentence_ = "Welcome to  <?php echo $patient_name; ?>  Dashboard";
            toastr.success(sentence_, 'Dashboard', {
                timeOut: 5000
            })
        <?php }

        ?>
    </script>

    <script src="../inc/procedure_script.js"></script>
    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="../js/plugins/datapicker/select2.full.min.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <script src="../js/typeahead.min.js"></script>
    <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
    <script src="../js/idle.js"></script>