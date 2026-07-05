<?php session_start();
include("../inc/header.php");
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');

if (isset($_GET['third_party_c'])) {
    try {
        // Decode and validate the ID
        $third_party_c = base64_decode($_GET['third_party_c'], true);
        if ($third_party_c === false || !is_numeric($third_party_c)) {
            throw new Exception("Invalid or corrupted document reference.");
        }

        // Prepare the update statement
        $update = $db->prepare("UPDATE patients_documents SET status = 0 WHERE id = ?");
        if (!$update) {
            throw new Exception("Failed to prepare database statement.");
        }

        // Execute the query
        if ($update->execute(array($third_party_c))) {
            if ($update->rowCount() > 0) {
                echo "<div class='alert alert-success'>Document has been successfully disabled.</div>";
            } else {
                echo "<div class='alert alert-warning'>No matching document found or already inactive.</div>";
            }
        } else {
            $errorInfo = implode(' | ', $update->errorInfo());
            throw new Exception("Database execution error: $errorInfo");
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Operation failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}



if (!isset($_GET['token'])) {
    header('location:index.php');
    exit;
}


$hospital_no = null;
$patient_name = null;
$sex = null;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


$token = cleanInput($_GET['token']);
$patient_info = $Patient->getByHospitalNo($token);

$ap_type = 0;
if (!empty($patient_info)) {
    $dob = $patient_info->dob;
    $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
    $sex = $patient_info->gender;
    $insurance_type = $patient_info->insurance_type;
    $interest = $patient_info->interest;
    // $token = $patient_info->token;
    $age = 0;
    $age_full = null;
    if (!empty($dob)) {
        $components = preg_split("/-/", $dob);
        $year = $components[0];
        $age = date('Y') - $year;
        $age_full = $age . ' yrs';

        if ($age == 0) {
            $month = abs(date('m') - $components[1]);
            $age_full = $month . ' Months';
        }
    }
    $hospital_no = $token;
} else {
    header('location:../index.php');
    exit;
}

?>

<body>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">

            <?php include("nav_header.php"); ?>
            <script src="../js/jquery-2.1.1.js"></script>
            <div class="wrapper wrapper-content">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5>
                                    <a style="float:right">
                                        <a href="patient.php?hosp_no=<?= $token; ?>" class="btn btn-xs btn-primary">Home</a>
                                    </a>
                                    Patient Dashboard
                                </h5>
                            </div>
                            <?php
                            $module = "Dashboard";
                            include_once('_uploadDocumentModal.php');
                            ?>

                            <div class="ibox-content">
                                <div class="row">
                                    <div class="col-sm-8 b-r">
                                        <a href="#uploadDocumentModal" class="btn btn-lg btn-primary " data-toggle="modal" data-target="#uploadDocumentModal"> Upload New Document </a>
                                        <br>
                                        <h1 class="text-center"> <u>Documents Attached to <br>
                                                <?= $patient_name; ?>'s Profile</u></h1>
                                        <br>

                                        <div class="card">
                                            <?php

                                            $patient_docs = $Document->get(['hospital_no' => $hospital_no], true);

                                            if (count($patient_docs) > 0) {
                                            ?>
                                                <table class="table table-striped table-bordered table-hover dataTables-example">
                                                    <thead>
                                                        <tr>
                                                            <th>Sn</th>
                                                            <th>Title</th>
                                                            <th>Date Uploaded</th>
                                                            <th>Uploaded By</th>
                                                            <th>Document</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sn = 1;
                                                        foreach ($patient_docs as $key => $patient_doc) {
                                                            $uploaded_by = null;
                                                            $user = $AdminUser->find($patient_doc->created_by);

                                                            if (!empty($user)) {
                                                                $uploaded_by = $user->fullname;
                                                            }
                                                        ?>
                                                            <tr>
                                                                <th><?= $sn++; ?></th>
                                                                <th><?= $patient_doc->title; ?></th>
                                                                <th><?= date('d M, Y h:i:s a', strtotime($patient_doc->created_at)); ?></th>
                                                                <th><?= $uploaded_by; ?></th>
                                                                <th><a href="<?= $patient_doc->link; ?>" target="_BLANK"> View Document</a></th>
                                                                <th>
                                                                    <?php
                                                                    $created_time = strtotime($patient_doc->created_at);
                                                                    $now = time();
                                                                    $within_60days = ($now - $created_time) < (30 * 24 * 60 * 60); // 5,184,000 seconds = 60 days
                                                                    if (
                                                                        (trim($_SESSION['fullname']) == trim($user->fullname) && $within_60days)
                                                                        || $_SESSION['delete_doc'] == 1
                                                                    ) { ?>
                                                                        <a href="document.php?token=<?= $hospital_no; ?>&third_party_c=<?= base64_encode($patient_doc->id); ?>"
                                                                            onclick="return confirm('Are you sure you want to delete this document?');">
                                                                            Delete
                                                                        </a>
                                                                    <?php } ?>
                                                                </th>


                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php
                                            } else {
                                                echo '<h3> Not available </h3>';
                                            }
                                            ?>

                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <?php include_once('_patient_dashboard_profile.php'); ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>





            </div>
            <?php include("../inc/footer.php"); ?>
        </div>
    </div>
    <?php include('../modal_lock.php'); ?>
    <?php include("../inc/footer_scripts.php"); ?>
    <script src="../js/plugins/chosen/chosen.jquery.js"></script>
    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="../js/plugins/datapicker/select2.full.min.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <script src="../js/typeahead.min.js"></script>

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







    <script>

    </script>

    <script>
        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            $('.dataTables-example2').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            $('.dataTables-example3').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });


            $('.med_datatale').dataTable({
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

        $('#data_5 .input-daterange').datepicker({
            keyboardNavigation: false,
            forceParse: false,
            autoclose: true
        });




        function ClickheretoprintDiv(div_id) {
            var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
            var content_vlue = document.getElementById(div_id).innerHTML;

            var docprint = window.open("", "", disp_setting);
            docprint.document.write('<html><head><title>.::Webmedic </title> <link rel="stylesheet" href="../css/bootstrap.min.css">');
            docprint.document.write('</head><body onLoad="self.print()" style="width: 100%; height="auto" font-size:16px; font-family:arial;">');
            docprint.document.write(content_vlue);
            docprint.document.write('</body></html>');
            docprint.document.close();
            docprint.focus();
        }
    </script>


    <script src="../js/idle.js"></script>