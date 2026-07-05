<?php include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');

?>

<?php
session_start();

if (strtoupper($_SESSION['password']) == 'STAFF123') {
    header("location:../profile/index.php?profile=$uname&changepassword");
}

$error_sel = 0;


if (isset($_POST['uploadDocumentBtn'])) {

    $title = $_POST['title'];
    $related_table_id = $_POST['related_table_id'];
    $related_table = $_POST['related_table'];
    $hospital_no = $_POST['hospital_no'];
    $module = $_POST['module'];
    $target_dir = $_POST['target_dir'];

    $error_status = 1;
    $error_msg = "Oops! Something went wrong";


    if (!empty($hospital_no)) {
        // $target_dir = dirname(__FILE__) . "/documents/transplant/";
        // $target_dir = "../documents/transplant/";
        $target_file = $target_dir . $transplant_id . getToken(20) . preg_replace('/ /i', '', $title . basename($_FILES["document_file"]["name"]));
        $uploadOk = 1;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($imageFileType == 'pdf' || $imageFileType == 'jpg' || $imageFileType == 'png' || $imageFileType == 'jpeg') {
            $tmp_name = $_FILES['document_file']['tmp_name'];

            if (move_uploaded_file($tmp_name, $target_file)) {

                $save = $Document->save($hospital_no, $title, $target_file, $imageFileType, $module,  $related_table, $related_table_id, $_SESSION["id"]);

                if ($save) {
                    $error_status = 2;
                    $error_msg = 'Document is uploaded successfully...';
                } else {
                    $error_msg = 'Operation Failed / Already uploaded...';
                }
            } else {
                $error_msg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error_msg = "Bad Request! Invalid file... ";
        }
    } else {
        $error_msg = "Oops! Patient ID not recognized...";
    }
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

include("invst_users.php");

?>



<body>

    <div id="wrapper">

        <?php include("../inc/nav_side.php");



        ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>


            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
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
                <div class="col-lg-2">

                </div>
            </div>
            <?php
            if (isset($_GET['hosp_no'])) {
                $hospital_number = $_GET['hosp_no'];
            } else {
                $hospital_number = $_POST['hospital_no'];
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
                                        <a href="logout_as_radiologist.php" class="btn btn-success btn-xs" style="font-size: 14px; color: white; ">Patient Consultation</a> &nbsp; : &nbsp;
                                    <?php } ?>
                                    <a href="mgt.php?hosp_no=<?= $hospital_number; ?>" class="btn btn-info btn-xs" style="font-size: 14px; color: white; "><i class="fa fa-file-pdf"></i><i class="fa fa-refresh font-noraml"></i>&nbsp;&nbsp;
                                        <strong style="color:white;">Refresh Investigation</strong></a>

                                    &nbsp;&nbsp;

                                    <?php if (isset($_GET['emr']) and $_SESSION['request'] == 1) {
                                        $emr = $_GET['emr'];
                                        $stmt_RQ = $db->query("SELECT patient, patient_name,business_service_center,referral FROM lab_manage where patient='$emr'");
                                        if ($stmt_RQ->rowCount() > 0) {
                                            $rowxx = $stmt_RQ->fetch(PDO::FETCH_ASSOC); ?>

                                            <input type="button" name="edit" value="Add New Investigation Request" data-target="#myModal5" id="<?php echo $emr . '__' . $rowxx['patient_name'] . '__' . $rowxx['business_service_center'] . '__' . $rowxx['referral']; ?>" class="btn btn-primary btn-xs lab_request" />
                                        <?php } ?>
                                    <?php } ?>



                                </div>
                            </div>
                            <div class="ibox-content">

                                <div class="panel-options">
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


                                        <div id="queue" class="tab-pane <?php echo $queue; ?>">
                                            <div class="row">
                                                <form method="post" action="mgt.php" name="form_me" id="form_me">
                                                    <div class="col-lg-4">

                                                        <div class="form_sep">
                                                            <label for="reg_input_no" class="" style="font-size: 15px; color: red; ">SEARCH FOR PATIENT:</label>
                                                            <select name="hospital_no" class="chosen-select" class="form-control" style="width:350px;" required>

                                                                <option selected="selected" value="">Search and Select Patient</option>
                                                                <?php
                                                                $stmt = $db->query("SELECT DISTINCT patient, patient_name FROM lab_manage order by patient");
                                                                if ($stmt->rowCount() > 0) {
                                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                                        <option value="<?php echo $patient = $row['patient']; ?>"><?php echo $row["patient_name"]; ?></option>
                                                                <?php }
                                                                } ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-1">
                                                        <label for="reg_input_no" class="" style="color: red;">Tick to View New</label><br>
                                                        <input type="checkbox" value="today" name="today" style="display:block; height:18px; width:18px;">

                                                    </div>
                                                    <div class="col-lg-2">
                                                        <label for="reg_input_no" class="">Click Here</label><br>


                                                        <button class="btn btn-primary btn-sm" type="submit" name="apply_narrow_search_patient">Search Patient</button>
                                                    </div>
                                                </form>

                                                <form method="post" action="mgt.php" name="form_me" id="form_me">

                                                    <div class="col-lg-4">
                                                        <div class="form-sep" id="data_5">
                                                            <label for="reg_input_no" class="">+ Set Dates</label>
                                                            <table>
                                                                <tr>
                                                                    <td><input type="date" class="input-sm form-control" name="start_" value="<?php $setdate = date("Y-m-d");
                                                                                                                                                echo date("Y-m-d", strtotime($setdate . " -7 day")); ?>" required></td>
                                                                    <td><span class="input-group-addon">to</span></td>
                                                                    <td><input type="date" class="input-sm form-control" name="end_" value="<?php echo date("Y-m-d"); ?>" required></td>
                                                                    <td>&nbsp;</td>
                                                                    <td><button class="btn btn-primary btn-sm" type="submit" name="apply_narrow_search">Apply</button></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>


                                            <?php

                                            ///// ZENTH ABUJA DONT WANT TO SEE THIS BELOW //////////////////////

                                            //if((isset($_POST['hospital_no']) and $_POST['hospital_no']!='') or (isset($_GET['hosp_no']) and $_GET['hosp_no']!='')){	 
                                            $sub = queue($db);

                                            //}
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

        <?php if ($alert_count > 0) { ?>
            <script>
                toastr.error('<?php echo $alert_msg; ?>', 'PATIENT ALERT!!', {
                    timeOut: 10000
                })
            </script>
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


        <?php


        echo '===============' . $_SESSION['section'];
        $stmt_list = $db->query("SELECT sn,patient,patient_name,test_name,request_date FROM lab_manage WHERE sms_status='1'");
        if ($stmt_list->rowCount() > 0) {  ?>
            <div class="modal inmodal fade" id="reminder_test_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Test Investigation Reminder</h4>
                        </div>
                        <div class="modal-body" id="">

                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th data-toggle="true">No</th>
                                        <th data-toggle="true">Patient</th>
                                        <th data-toggle="true">Patient Name</th>
                                        <th data-toggle="true">Investigation</th>
                                        <th data-toggle="true">request_date</th>
                                        <th data-toggle="true"></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $n = 1;
                                    $data = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($data as $key => $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $n; ?></td>
                                            <td><?php echo $row['patient']; ?></td>
                                            <td><?php echo $row['patient_name']; ?></td>
                                            <td><?php echo $row['test_name']; ?></td>
                                            <td><?php echo date('d,M y h:i a', strtotime($row['request_date'])); ?></td>
                                            <td><button class="btn btn-success btn-sm" onclick="send_reminder('<?php echo $row['sn'] ?>')">Seen</button></td>

                                        </tr>
                                    <?php
                                        $n++;
                                    } ?>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>

        <?php        }
        ?>

        <?php include('../modal_lock.php'); ?>
        <script>
            $('#reminder_test_mdl').modal('show');
        </script>
        <script src="../js/mgt.js"></script>



        <!-- Data Tables -->
        <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
        <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
        <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
        <script src="../js/plugins/datapicker/select2.full.min.js"></script>



        <script>
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

                const notes_type = $(this).attr('arial-data');
                const hospital_no = $(this).attr('arial-hospitalnno');
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

            $(document).ready(function() {
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
                            // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data
                            return {
                                results: data.items
                            };
                        },
                        cache: true
                    },
                    escapeMarkup: function(markup) {
                        return markup;
                    }, // let our custom formatter work
                    minimumInputLength: 1,
                    //templateResult: formatRepo, // omitted for brevity, see the source of this page
                    //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
                });
            });



            pay_now<?php echo $sn_; ?>


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
        </script>
        <script src="../js/idle.js"></script>
</body>

</html>