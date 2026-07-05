<?php session_start();
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
include("../inc/credit_current_balance.php");


if (isset($_POST['patient_trs_display'])) {
    $hospital_no = $_POST['hospital_no'];
    header("location:dialysis.php?patient=$hospital_no");
    exit;
}

if (isset($_GET['cancel_request'])) {
    $hospital_no = cleanInput($_GET['patient']);

    $selectDrugSn = "SELECT drug_sn,date_entry,app_no,hospital_no FROM patient_ap_services 
                        WHERE hospital_no='$hospital_no' 
                        AND paystatus = 0
                        AND drug_status = 0
                        AND cat_type = 'Dialysis'
                        AND cr = 0";
    $drugSnRows = $db->query($selectDrugSn)->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($drugSnRows)) {
        foreach ($drugSnRows as $row) {

            $app_no = $row['app_no'];
            $hospital_no = $row['hospital_no'];

            $db->beginTransaction();

            try {
                // Delete from patient_ap_services
                $deleted1 = "DELETE FROM patient_ap_services 
        WHERE app_no = :app_no 
        AND hospital_no = :hospital_no 
        AND cat_type = 'Dialysis'
        AND paystatus = 0
        AND cr = 0";
                $stmt1 = $db->prepare($deleted1);
                $stmt1->bindParam(':app_no', $app_no);
                $stmt1->bindParam(':hospital_no', $hospital_no);
                $stmt1->execute();
                $deletedCount1 = $stmt1->rowCount();

                // Delete from dialysis
                $deleted2 = "DELETE FROM dialysis WHERE app_no = :app_no AND hospital_no = :hospital_no";
                $stmt2 = $db->prepare($deleted2);
                $stmt2->bindParam(':app_no', $app_no);
                $stmt2->bindParam(':hospital_no', $hospital_no);
                $stmt2->execute();
                $deletedCount2 = $stmt2->rowCount();

                // Delete from apptm
                $deleted3 = "DELETE FROM apptm WHERE appt_no = :app_no AND hospital_no = :hospital_no";
                $stmt3 = $db->prepare($deleted3);
                $stmt3->bindParam(':app_no', $app_no);
                $stmt3->bindParam(':hospital_no', $hospital_no);
                $stmt3->execute();
                $deletedCount3 = $stmt3->rowCount();

                // Check if all deletes were successful
                if ($deletedCount1 == 1 or $deletedCount2 == 1 or $deletedCount3 == 1) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            } catch (PDOException $e) {
                $db->rollBack();
            }
        }
    }
}


if (isset($_GET['undoCompletion'])) {

    $undoCompletion = base64_decode($_GET['undoCompletion']);
    $mark_completed_by = null;
    $date_marked_completed = null;

    $update_completed_stmt = $db->prepare("UPDATE  dialysis SET completed = 'no', date_marked_completed=?, mark_completed_by=? WHERE id = ?");
    $update_dialysis = $update_completed_stmt->execute(array($date_marked_completed, $mark_completed_by, $undoCompletion));
    if ($update_dialysis == true) {
        $error_status = 2;
        $error_msg = 'Success: Dialysis marked as Uncompleted';
    }
}

if (isset($_POST['getPatientAlerts'])) {

    $hospital_no = cleanInput($_POST['hospital_no']);
    $alerts_string = '';
    $stmt = $db->prepare("SELECT * FROM tbl_patient_alerts WHERE hospital_no=? AND status = '1' ");
    $stmt->execute(array($hospital_no));
    $clinical_count = $stmt->rowCount();
    if ($clinical_count > 0):
        $alerts = $stmt->fetchAll();
        $alerts_string = '<b>Patient Alert(s): </b><br/>';
        foreach ($alerts as $key => $alert):
            $alerts_string .= $alert['alert'] . '<br/>';
        endforeach;
    endif;

    echo $alerts_string;
    exit;
}


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
<style>
    #patient_ #nav li {
        list-style: none;
        display: inline-block;
    }

    #patient_ #nav li a:hover,
    #nav li a.selected {
        background: #FFF;
        background-color: #FFF;
        border-top: 0px solid #FFF;
        border-bottom: 1px solid #FFF;
    }

    #patient_ #nav li a {
        display: block;
        padding: 8px 15px;
        text-decoration: none;
        border-right: 1px solid #ccc;
    }

    .btn-full {
        display: block;
        width: 100%;

    }

    .light-card {
        background: #FFF !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        transition: 0.3s;
        margin: 5px;
        padding: 20px;
        color: #888;
    }

    .btn-card {
        background: #FFF !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        transition: 0.3s;
        margin: 5px;
        padding: 10px;
        color: #888;
    }

    .btn-card:hover {
        box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
        background: #1ab394 !important;
        border-color: #1ab394;
        color: #FFFFFF;
    }

    .btn-card:active {
        box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
        background: #FFFFFF !important;
        border-color: #FFFFFF !important;
        color: #1ab394;
    }


    .btn-text-left {
        text-align: left !important;
    }

    .pd-20-left {
        padding-left: 20px;
    }


    .btn-card-active {
        box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
        background: #1ab394 !important;
        border-color: #1ab394;
        color: #FFFFFF;
    }

    tr td {
        padding: 4px;
    }
</style>


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

        if ($rights == 'PH') {
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

        include('../modal_lock.php');
        ?>

        <div id="page-wrapper" class="gray-bg">

            <?php include("nav_header.php"); ?>
            <script src="../js/jquery-2.1.1.js"></script>
            <div class="wrapper wrapper-content">
                <?php
                $page = null;
                if (isset($_GET['p'])) {
                    $page = base64_decode($_GET['p']);
                }

                if ($page == 'New_request') {

                    include_once('components/_dialysis_request.php');
                    include_once('dialysis/_request_list.php');
                } else if ($page == 'Open_dialysis') {
                    include_once('dialysis/_details.php');
                } else if ($page == 'Completed_List') {
                    include_once('dialysis/_completed.php');
                } else if ($page == 'statistics') {
                    $page = 'statistics';
                    include_once('dialysis/_statistics.php');
                } else {
                    $page = 'home';
                    include_once('dialysis/_request_list.php');
                }
                ?>



                <?php

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

    <link rel="stylesheet" href="../js/select2/css/select2.min.css">
    <script src="../js/select2/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            // Internal Patient Search
            $('#search_for_patient').select2({
                placeholder: 'Search and Select Patient',
                minimumInputLength: 2,
                ajax: {
                    url: 'get_search.php',
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


        $(document).ready(function() {

            // Internal Patient Search
            $('#search_for_patient').select2({
                placeholder: 'Search and Select Patient',
                minimumInputLength: 3,
                ajax: {
                    url: 'get_patients.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            mode: 'all_patients_dailysis' // Key difference
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
    </script>

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
            var search_result = [];
            $('#search_patient_input').typeahead({

                source: function(query, query_response) {
                    console.log($('#search_patient_input').val())
                    $.ajax({
                        url: "_search_patient_json.php",
                        method: "POST",
                        data: {
                            search_patient_json: true,
                            input_text: $('#search_patient_input').val()
                        },
                        dataType: "json",
                        success: function(data) {

                            search_result = data;
                            query_response($.map(data, function(item) {

                                return item.name;

                            }));
                        }

                    })
                },
                updater: function(item) {
                    search_result.forEach(element => {
                        if (element.name == item) {
                            $('#search_patient_hospital_no').val(element.hospital_no)
                            $.ajax({
                                url: "dialysis.php",
                                method: "POST",
                                data: {
                                    getPatientAlerts: true,
                                    hospital_no: element.hospital_no
                                },
                                success: function(data) {
                                    $('#patient_alerts').html(data)
                                }

                            })


                            return item;
                        }
                    });
                    return item
                }
            });

        });



        $(function() {


            console.log(<?= json_encode($ufrs); ?>)
            var lineData = {
                labels: <?= json_encode($labels); ?>,
                datasets: [

                    {

                        label: 'Blood_Flow',
                        backgroundColor: 'rgba(26,179,148,0.5)',
                        borderColor: "rgba(26,179,148,0.7)",
                        pointBackgroundColor: "rgba(26,179,148,1)",
                        pointBorderColor: "#fff",
                        data: <?= json_encode($blood_flows); ?>
                    }, {
                        label: 'UF',
                        backgroundColor: 'rgba(220, 220, 220, 0.5)',
                        pointBorderColor: "#fff",
                        data: <?= json_encode($ufs); ?>
                    },
                    // {

                    //     label: 'vp_pre_weights',
                    //     backgroundColor: 'rgba(26,179,148,0.5)',
                    //     borderColor: "rgba(26,179,148,0.7)",
                    //     pointBackgroundColor: "rgba(26,179,148,1)",
                    //     pointBorderColor: "#fff",
                    //     data: <?= json_encode($vp_pre_weights); ?>
                    // }
                    // , {

                    //     label: 'ap_post_weights',
                    //     backgroundColor: 'rgba(220, 220, 220, 0.5)',
                    //     pointBorderColor: "#fff",
                    //     data: <?= json_encode($ap_post_weights); ?>
                    // },
                    {

                        label: 'ufrs',
                        backgroundColor: 'rgba(26,179,148,0.5)',
                        borderColor: "rgba(26,179,148,0.7)",
                        pointBackgroundColor: "rgba(46,159,148,1)",
                        pointBorderColor: "#fff",
                        data: <?= json_encode($ufrs); ?>
                    },
                    {

                        label: 'Fluid Loss',
                        backgroundColor: 'rgba(255, 255, 255, 0.7)',
                        pointBorderColor: "#fff",
                        data: <?= json_encode($fluid_loss_ar); ?>
                    },
                    {
                        label: 'HEP',
                        backgroundColor: 'rgba(220, 220, 220, 0.5)',
                        pointBorderColor: "#fff",
                        data: <?= json_encode($hep_ar); ?>
                    }, {

                        label: 'Pulse',
                        backgroundColor: 'rgba(26,179,148,0.5)',
                        borderColor: "rgba(26,179,148,0.7)",
                        pointBackgroundColor: "rgba(46,159,148,1)",
                        pointBorderColor: "#fff",
                        data: <?= json_encode($pulse_ar); ?>
                    },
                ]
            };

            var lineOptions = {
                responsive: true
            };
            var ctx = document.getElementById("lineChart").getContext("2d");
            new Chart(ctx, {
                type: 'line',
                data: lineData,
                options: lineOptions
            });



        });


        $(document).on('click', '.med_rpt', function() {
            $('#med_rpt_modal').modal('hide');
            var med_rpt_id = $(this).attr("id");
            if (med_rpt_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        med_rpt_id: med_rpt_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Medical Report');
                        $('#med_rpt_body').html(data);
                        $('#med_rpt_modal').modal('show');
                    }
                });
            }
        });



        $(document).on('click', '.add_fields_items', function() {
            ///$('#investigation_modal').modal('hide');
            var lab_test_no = $(this).attr("id");
            $.ajax({
                url: "fetch_set2.php",
                method: "POST",
                data: {
                    lab_test_no: lab_test_no
                },
                success: function(data) {
                    $('#add_Lab_TestParametersbody').html(data);
                    $('#add_Lab_TestParameters_Modal').modal('show');
                }
            });
        });

        delete_dailysis_error();

        function delete_dailysis_error() {
            $.ajax({
                url: "delete_dailysis_error.php",
                method: "POST",
                data: {
                    delete_error: true
                },
                success: function(data) {}
            });

        }

        var clinical_count = <?= $clinical_count; ?>;
        $(document).ready(function() {


            if (clinical_count > 0) {
                $('#pending_task_reminder_modal').modal('show');
            }


            $('.dataTables-example').dataTable();

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



        function cancelReminder(sn) {

            if (confirm('Are you sure you want to proceed?')) {
                toastr.info('Cancelling, please wait...', 'Alert', {
                    timeOut: 5000
                })
                $.ajax({
                    url: 'dialysis/_dialysis_reminder.php',
                    type: "POST",
                    data: {
                        cancelReminder: true,
                        ajaxPosting: true,
                        sn: sn
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            toastr.success(response.message, 'Success', {
                                timeOut: 5000
                            })
                            clinical_count -= 1;

                            if (clinical_count == 0) {
                                $(".notification-widget").removeClass("red-bg").addClass("blue-bg");
                            }

                            $("#pending_tasK_list_" + sn).hide('slow');
                            $(".notification-widget-count").html(clinical_count)
                        } else {
                            toastr.error(response.message, 'Error', {
                                timeOut: 5000
                            })
                        }

                    },
                    error: function(response) {
                        console.log(response)
                    }
                });
            }

        }


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


    <script>
        $(document).on('submit', '#show_report_by_type_and_date_form', function(evt) {
            evt.preventDefault();
            if ($('#request_type').val() != '' || $('#start').val() != '' || $('#end').val() != '') {
                $('#show_report_by_type_and_date_form').submit();
            }
        });


        $(document).on('change', '#report_year', function(evt) {
            location.href = 'dialysis.php?p=c3RhdGlzdGljcw==&yr=' + $('#report_year').val();
            // $('#report_year_form').submit();
        });



        function payNow(sale_sn, target, hospital_no) {

            if (target == 'dailysis') {
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