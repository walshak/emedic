<?php
session_start();
include("../Connections/Conn.php");
include("../inc/header.php");
include('../session_time_out.php');
?>

<!DOCTYPE html>
<html>
<title>WebMedic | <?php if ($_SESSION['Designation'] != "") {
                        echo $_SESSION['Designation'];
                    } else {
                        echo $_SESSION['speciality'];
                    } ?></title>

<body>
    <div id="wrapper">
        <?php include("../inc/nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">

            <?php include("nav_header.php");

            if (isset($_GET['manage'])) {
                include("../inc/breadcrumb.php");
            }

            include_once("../inc/alert_msg.php");

            ?>

            <div class="wrapper wrapper-content">

                <?php

                if (strtoupper($_SESSION['password']) == 'STAFF123') {
                    header("location:../profile/index.php?profile=$uname&changepassword");
                }

                include("../inc/dashb_lab.php");

                ?>
            </div>


            <?php include("../inc/footer.php"); ?>
        </div>
    </div>

    <?php include("../inc/lab_mdl.php") ?>
    <?php include("search_modal.php") ?>
    <?php include('../modal_lock.php'); ?>


    <?php include("../inc/footer_scripts.php"); ?>

    <?php if (isset($_GET['Nex'])) { ?>
        <script>
            toastr.error('This patient Number Does Not Exist. Please try again!', 'Error', {
                timeOut: 5000
            })
        </script>
    <?php } ?>

    <script>
        <?php if ($display_status == 1) { ?>
            $(document).ready(function() {
                $("#discharge_booking_modal").modal('show');
            });
        <?php  } ?>
    </script>



    <?php
    if ($display_status == 1) { ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#myModalx").modal('show');
            });
        </script>

    <?php  } ?>
    <script src="../js/idle.js"></script>

</body>

</html>