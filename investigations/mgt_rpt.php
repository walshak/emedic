<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

$error_sel = 0;
?>


<!DOCTYPE html>
<html>

<?php include("../inc/header.php"); ?>



<body>

    <div id="wrapper">

        <?php include("../inc/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>


            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Investigation Management Report</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li class="active">
                            <strong>Investigation Report</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-2">

                    <br>
                    <div id="labAlertBox" style="display:none; cursor:pointer; background:#f0fff0; padding:10px; border-radius:5px;">


                        <table width="100%">
                            <tr>
                                <td>🚶 Outpatients</td>
                                <td><strong id="lab_outpatient">0</strong></td>
                            </tr>
                            <tr>
                                <td>🏥 Inpatient:</td>
                                <td><strong id="lab_inpatient">0</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="wrapper wrapper-content  animated fadeInRight">
                <?php if (isset($_GET['rpt_type'])) {
                    include("print2.php");
                } else { ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox ">
                                <div class="ibox-title">
                                    <h5> Investigation Report</h5>
                                    <div class="ibox-tools">
                                    </div>
                                </div>
                                <div class="ibox-content">



                                    <?php

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


                                        <div class="row">
                                            <div class="col-md-6">
                                                <form action="mgt_rpt.php" method="POST" id="subject" name="subject" enctype="multipart/form-data">

                                                    <strong style="color:#F00">What to do here ... </strong><br>
                                                    Search for investigation report to see details. Results waiting for approval, specimens collection reports, and all pending tasks ...
                                                    <hr>

                                                    <div class="form_sep">
                                                        <label for="reg_input_no" class="">Select Management Type</label>
                                                        <select name="mgt_type" id="mgt_type" class="form-control" data-required="true">
                                                            <option selected="selected" value="">Select ...</option>
                                                            <option value="queue">Open Request(Queue)</option>
                                                            <option value="specimen">[Pending] Specimen Report</option>
                                                            <option value="capture">[Pending] Captured Report</option>
                                                            <option value="result">[pending] Result Report</option>
                                                            <option value="approve">Approval Report</option>
                                                            <option value="all_pending">All Pending Requests</option>
                                                            <option value="Summary">Summary Investigation Done</option>

                                                        </select>
                                                    </div>


                                                    <div class="form_sep">
                                                        <label for="reg_input_no" class="">Departments OR </label>
                                                        <select name="Department" id="Department" class="form-control" data-required="true">
                                                            <option selected="selected" value="">Select Departments...</option>

                                                            <?php
                                                            $stmt = $db->query("SELECT * FROM department WHERE $where");
                                                            while ($row_rstdepartment = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                                <option value="<?php echo $row_rstdepartment['sn'] . '/' . $row_rstdepartment["department"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div class="form_sep">
                                                        <label for="reg_input_no" class="">Staff</label>
                                                        <select name="Staff" id="Staff" class="form-control" data-required="true">
                                                            <option selected="selected" value="">Select Staff Name...</option>

                                                            <?php
                                                            $stmt = $db->query("SELECT u.fullname, i.username FROM admin_users as u inner join invsti_users as i on i.username=u.username where (section='Laboratory' or section='Radiology')");
                                                            while ($row_rstdepartment = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                                <option value="<?php echo $row_rstdepartment['fullname']; ?>"><?php echo $row_rstdepartment["fullname"]; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>


                                                    <div class="form_sep" id="">
                                                        <label for="reg_input_no" class="">Set Dates Range</label>
                                                        <div class="input-group" id="">
                                                            <input type="date" class="input-sm form-control" name="start" value="<?php echo date("Y-m-d"); ?>" />
                                                            <span class="input-group-addon">to</span>
                                                            <input type="date" class="input-sm form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="form_sep">
                                                        <label for="reg_input_no" class="">.</label><br>
                                                        <button class="btn btn-primary btn btn-sm" type="submit" name="apply_approve">Apply</button>
                                                    </div>

                                                </form>
                                            </div>



                                            <div class="col-md-6">
                                                <form action="mgt_rpt.php?print" method="get">

                                                    <div class="form_sep">
                                                        <label for="reg_select" class="req">Report type</label>
                                                        <select name="rpt_type" id="rpt_type" class="form-control" data-required="true">
                                                            <option selected="selected" value="">Select...</option>
                                                            <option value="ps">Patient(s) Seen</option>
                                                            <option value="dcr">Credit Dispensed</option>
                                                            <option value="requester">Investigations Sent By Requester</option>
                                                            <option value="approver">Investigations By Approver</option>
                                                            <option value="item_services">Report by Investigations (Select Insurance Below if desired)</option>
                                                            <?php if ($unit_head == 1) { ?>
                                                                <option value="transact_rpt">Transaction Reports (Select Insurance Below if desired)</option>
                                                            <?php } ?>

                                                        </select>
                                                    </div>



                                                    <div class="form_sep" id="inv_item">
                                                        <label for="reg_input_no" class="">Search for Investigations</label>
                                                        <select name="item_services[]" id="item_services" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">

                                                            <option value="">Search and Select</option>
                                                            <?php
                                                            $stmt = $db->query("SELECT distinct item_services FROM patient_ap_services where serv_group IN ('laboratory', 'radiology') ORDER BY item_services");
                                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                                <option value="<?php echo $row['item_services']; ?>"><?php echo $row['item_services']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <?php
                                                    $stmt = $db->prepare("SELECT DISTINCT fullname FROM admin_users WHERE rights = :rights AND status = :status ORDER BY count DESC");
                                                    $stmt->bindValue(':rights', 'PH', PDO::PARAM_STR);
                                                    $stmt->bindValue(':status', '1', PDO::PARAM_STR);
                                                    $stmt->execute();
                                                    ?>



                                                    <div class="form_sep"></div>
                                                    <?php
                                                    $stmt = $db->prepare("SELECT * FROM insurance_tbl WHERE status = :status AND (insurance_type = :type1 OR insurance_type = :type2 OR insurance_type = :type3) ORDER BY insurance_name");
                                                    $stmt->bindValue(':status', 'active', PDO::PARAM_STR);
                                                    $stmt->bindValue(':type1', 'PHIS', PDO::PARAM_STR);
                                                    $stmt->bindValue(':type2', 'NHIS', PDO::PARAM_STR);
                                                    $stmt->bindValue(':type3', 'Corporate', PDO::PARAM_STR);
                                                    $stmt->execute();
                                                    ?>
                                                    <label for="reg_select" class="">Insurance List</label>
                                                    <select name="hmo_nhis[]" id="hmo_nhis" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">
                                                        <option value="all">All</option>
                                                        <?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                                                            <option value="<?php echo $row_rstSelect["insurance_name"]; ?>"><?php echo $row_rstSelect["insurance_name"]; ?></option>
                                                        <?php } ?>
                                                    </select>



                                                    <div class="form_sep"></div>
                                                    <div class="form_sep">
                                                        <strong>Search All Requests by Dates</strong>
                                                        <div class="form-group">
                                                            <div class="input-daterange input-group">
                                                                <input type="date" class="form-control" name="from_date" value="<?php echo date('Y-m-d'); ?>" />
                                                                <span class="input-group-addon">to</span>
                                                                <input type="date" class="form-control" name="to_date" value="<?php echo date('Y-m-d'); ?>" />

                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="form_sep">
                                                        <table width="100%">
                                                            <tr>
                                                                <td>
                                                                    <button class="btn btn-success" type="submit" name="apply_task2" id="apply_task2">Apply Search</button>
                                                                </td>
                                                                <td>
                                                                    <div align="right">
                                                                        <a href="mgt_rpt.php" class="btn btn-warning">Reset</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>

                                                    <hr>
                                                </form>

                                            </div>

                                        </div>



                                        <?php
                                        /// double copy found here on this link


                                        if (isset($_POST['mgt_type'])) {
                                            $mgt_type = $_POST['mgt_type'];
                                        }

                                        if (isset($_POST['apply_approve']) and $mgt_type != 'Summary') {

                                            if ($_POST['Department'] != '') {
                                                $dept = $_POST['Department'];
                                                $parts = explode("/", $dept);
                                                $dept_id = $parts[0];
                                                $dept_name = $parts[1];

                                                $dept_part = "lab_cat='$dept_id' and ";



                                                if ($mgt_type == 'all_pending' or $mgt_type == '') {
                                                    $mgt_type_part = "(data_capture_status!='approve' or data_capture_status!='cancel') and ";
                                                    $search_lab = "lab_manage WHERE $dept_part $mgt_type_part";
                                                } else {
                                                    $mgt_type_part = "data_capture_status='$mgt_type'";
                                                    $search_lab = "lab_manage WHERE $dept_part $mgt_type_part and ";
                                                }
                                            } elseif ($_POST['Staff'] != '') {
                                                $Staff = $_POST['Staff'];


                                                if ($mgt_type == 'specimen' or $mgt_type == 'capture') {
                                                    $search_lab = "lab_manage WHERE collected_by='$Staff' and (data_capture_status='specimen' or data_capture_status='capture') and ";
                                                } elseif ($mgt_type == 'result') {
                                                    $search_result = "lab_result WHERE (lab_sci_name='$Staff' or entered_by='$Staff') and ";
                                                } elseif ($mgt_type == 'approve' or $mgt_type == 'reject') {
                                                    $search_lab = "lab_manage WHERE data_capture_status='$mgt_type' and approved_by='$Staff' and ";
                                                } else {
                                                    $search_lab = "lab_manage WHERE collected_by='$Staff' and (data_capture_status!='approve' or data_capture_status!='cancel') and ";
                                                }
                                            } else {

                                                $mgt_type_part = "data_capture_status='$mgt_type'";
                                                $search_lab = "lab_manage WHERE $mgt_type_part and ";
                                            }


                                            if ($_POST['start'] != '' and $_POST['end'] != '') {
                                                $start = $_POST['start'];
                                                $end = $_POST['end'];

                                                if ($mgt_type == 'result' and $_POST['Staff'] != '') {
                                                    //echo 'echo here';
                                                    $stmt = $db->query("SELECT * FROM $search_result date(result_date) BETWEEN '$start' AND '$end' order by sn DESC");
                                                } else {
                                                    $stmt = $db->query("SELECT * FROM $search_lab date(request_date) BETWEEN '$start' AND '$end' order by sn DESC");
                                                }

                                                if ($stmt->rowCount() > 0) { ?>


                                                    <div class="alert alert-info">
                                                        <?php echo '<strong>' . $stmt->rowCount() . ' Results found </strong>' . '[ ' . $dept_name . ' ]' . '[ ' . $mgt_type . ' ]'; ?>
                                                    </div>

                                                    <table class="table table-striped table-bordered table-hover dataTables-example">
                                                        <thead>
                                                            <tr>
                                                                <th data-toggle="true">Patient # & <br> Name </th>
                                                                <th data-toggle="true">Investigation</th>
                                                                <th data-toggle="true">Requesting Physician</th>
                                                                <th data-toggle="true">Requested By</th>
                                                                <th data-toggle="true">Specimen/Image <br> Collected by</th>
                                                                <th data-toggle="true">Investigation<br>Performed By:</th>
                                                                <th data-toggle="true">Result <br>Entered By:</th>
                                                                <th data-toggle="true">Result <br>Approved By</th>
                                                                <th data-toggle="true">Current <br>Status</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            $n = 1;



                                                            if ($mgt_type == 'result' and $_POST['Staff'] != '') {

                                                                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                                                    $RQ = $roww['lab_no'];
                                                                    $stmt_rlst = $db->query("SELECT * FROM lab_manage WHERE labrequest_no='$RQ'");
                                                                    $rowx = $stmt_rlst->fetch(PDO::FETCH_ASSOC);
                                                            ?>

                                                                    <tr>
                                                                        <td><?php echo $rowx['patient'] . '<br>' . $rowx['patient_name']; ?></td>
                                                                        <td><?php echo $rowx['labrequest_no'] . '<br>' . $rowx['test_name']; ?></td>

                                                                        <td><?php echo $rowx['requesting_physician']; ?></td>
                                                                        <td><?php echo $rowx['request_by'] . '<br>' .  date('d,M y h:i a', strtotime($rowx['request_date'])); ?></td>

                                                                        <td><?php echo $rowx['collected_by'] . '<br>' . $rowx['collected_specimen'] . '<br>';
                                                                            if ($rowx['collected_date'] != '') {
                                                                                echo date('d,M y h:i a', strtotime($rowx['collected_date']));
                                                                            } ?></td>

                                                                        <td><?php echo $roww['lab_sci_name']; ?></td>
                                                                        <td><?php echo $roww['entered_by'] . '<br>';
                                                                            if ($roww['result_date'] != '') {
                                                                                echo date('d,M y h:i a', strtotime($roww['result_date']));
                                                                            }
                                                                            ?></td>

                                                                        <td><?php
                                                                            if ($rowx['approved_by'] != '') {
                                                                                echo $rowx['approved_by']  . '<br>';
                                                                                echo date('d,M y h:i a', strtotime($roww['result_date']));
                                                                            } else {
                                                                                echo 'Pending';
                                                                            } ?></td>
                                                                        <td><?php echo $rowx['data_capture_status']; ?></td>
                                                                    </tr>
                                                                <?php $n++;
                                                                } ?>

                                                        </tbody>
                                                    </table>



                                                    <?php

                                                            } else {



                                                                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                                                    if ($roww['data_capture_status'] == 'result' or $roww['data_capture_status'] == 'approve') {
                                                                        $RQ = $roww['labrequest_no'];
                                                                        $stmt_rlst = $db->query("SELECT result_date,lab_sci_name,entered_by FROM lab_result WHERE lab_no='$RQ'");
                                                                        $rowx = $stmt_rlst->fetch(PDO::FETCH_ASSOC);
                                                                    }

                                                    ?>

                                                        <tr>
                                                            <td><?php echo $roww['patient'] . '<br>' . $roww['patient_name']; ?></td>
                                                            <td><?php echo $roww['labrequest_no'] . '<br>' . $roww['test_name']; ?></td>

                                                            <td><?php echo $roww['requesting_physician']; ?></td>
                                                            <td><?php echo $roww['request_by'] . '<br>' .  date('d,M y h:i a', strtotime($roww['request_date'])); ?></td>

                                                            <td><?php echo $roww['collected_by'] . '<br>' . $roww['collected_specimen'] . '<br>';
                                                                    if ($roww['collected_date'] != '') {
                                                                        echo date('d,M y h:i a', strtotime($roww['collected_date']));
                                                                    } ?></td>

                                                            <td><?php echo $rowx['lab_sci_name']; ?></td>
                                                            <td><?php echo $rowx['entered_by'] . '<br>';
                                                                    if ($rowx['result_date'] != '') {
                                                                        echo date('d,M y h:i a', strtotime($rowx['result_date']));
                                                                    }
                                                                ?></td>

                                                            <td><?php
                                                                    if ($roww['approved_by'] != '') {
                                                                        echo $roww['approved_by']  . '<br>';
                                                                        echo date('d,M y h:i a', strtotime($rowx['result_date']));
                                                                    } else {
                                                                        echo 'Pending';
                                                                    } ?></td>
                                                            <td><?php echo $roww['data_capture_status']; ?></td>
                                                        </tr>
                                                    <?php $n++;
                                                                } ?>

                                                    </tbody>
                                                    </table>



                                                <?php }    ?>


                                            <?php } else {
                                                    echo '<br>No Data Available!';
                                                }
                                            } else {
                                                echo '<br>Invalid Dates';
                                            }
                                        } elseif (isset($_POST['apply_approve']) && $mgt_type == 'Summary') {

                                            $mgt_type_part = "(data_capture_status='result' OR data_capture_status='approve')";

                                            if ($_POST['start'] != '' && $_POST['end'] != '') {

                                                $start = $_POST['start'];
                                                $end   = $_POST['end'];

                                                // 🔹 SINGLE QUERY (GROUPED)
                                                $stmt = $db->query("
            SELECT test_name, test_id, COUNT(test_id) AS number_test
            FROM lab_manage
            WHERE $mgt_type_part
            AND DATE(request_date) BETWEEN '$start' AND '$end'
            GROUP BY test_id
            ORDER BY number_test DESC
        ");

                                                // 🔹 FETCH ALL DATA
                                                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                if (count($data) > 0) {

                                                    // 🔹 CALCULATE TOTAL
                                                    $TOTAL_number_test = 0;
                                                    foreach ($data as $row) {
                                                        $TOTAL_number_test += $row['number_test'];
                                                    }

                                                    // 🔹 PREPARE DATA FOR CHART AND CSV
                                                    $chart_labels = [];
                                                    $chart_values = [];
                                                    $csv_data = [];

                                                    foreach ($data as $row) {
                                                        $chart_labels[] = $row['test_name'];
                                                        $percentage = ($TOTAL_number_test > 0)
                                                            ? round(($row['number_test'] / $TOTAL_number_test) * 100, 2)
                                                            : 0;
                                                        $chart_values[] = $percentage;

                                                        // CSV row
                                                        $csv_data[] = [
                                                            'Investigation' => $row['test_name'],
                                                            'No_of_Requests' => $row['number_test'],
                                                            'Percentage' => $percentage
                                                        ];
                                                    }
                                            ?>

                                                <div class="alert alert-info">
                                                    <?php echo '<strong>' . count($data) . ' Results found </strong>' . '[ ' . $dept_name . ' ]' . '[ ' . $mgt_type . ' ]'; ?>
                                                </div>

                                                <!-- 🔹 DOWNLOAD CSV BUTTON -->
                                                <button id="downloadCsvBtn" class="btn btn-success" style="margin-bottom:10px;">Download CSV</button>

                                                <!-- 🔹 LAB TABLE -->
                                                <table id="labTable" class="table table-striped table-bordered table-hover dataTables-example">
                                                    <thead>
                                                        <tr>
                                                            <th>SN</th>
                                                            <th>Investigation</th>
                                                            <th>No of Requests</th>
                                                            <th>Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $n = 1;
                                                        foreach ($data as $row) {
                                                            $test_name   = $row['test_name'];
                                                            $number_test = $row['number_test'];
                                                            $percentage  = ($TOTAL_number_test > 0) ? ($number_test / $TOTAL_number_test) * 100 : 0;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $n; ?></td>
                                                                <td><?php echo $test_name; ?></td>
                                                                <td><?php echo $number_test; ?></td>
                                                                <td><?php echo number_format($percentage, 2); ?>%</td>
                                                            </tr>
                                                        <?php $n++;
                                                        } ?>
                                                        <tr style="font-weight:bold; background:#f1f1f1;">
                                                            <td colspan="2" style="text-align:right;">Grand Total</td>
                                                            <td><?php echo $TOTAL_number_test; ?></td>
                                                            <td>100%</td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!-- 🔹 CHART CONTAINER -->
                                                <canvas id="labChart" width="400" height="400"></canvas>

                                                <!-- 🔹 CHART.JS SCRIPT -->
                                                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                                <script>
                                                    // Pie Chart
                                                    var ctx = document.getElementById('labChart').getContext('2d');
                                                    var labChart = new Chart(ctx, {
                                                        type: 'pie',
                                                        data: {
                                                            labels: <?php echo json_encode($chart_labels); ?>,
                                                            datasets: [{
                                                                label: 'Lab Test Distribution (%)',
                                                                data: <?php echo json_encode($chart_values); ?>,
                                                                backgroundColor: [
                                                                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                                                                    '#9966FF', '#FF9F40', '#C9CBCF', '#8B0000',
                                                                    '#008000', '#00008B'
                                                                ],
                                                                borderWidth: 1
                                                            }]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            plugins: {
                                                                legend: {
                                                                    position: 'right'
                                                                },
                                                                tooltip: {
                                                                    callbacks: {
                                                                        label: function(context) {
                                                                            return context.label + ': ' + context.raw + '%';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    });

                                                    // CSV Download
                                                    var csvData = <?php echo json_encode($csv_data); ?>;

                                                    document.getElementById('downloadCsvBtn').addEventListener('click', function() {
                                                        var csvContent = "data:text/csv;charset=utf-8,";
                                                        csvContent += "Investigation,No of Requests,Percentage\n";

                                                        csvData.forEach(function(row) {
                                                            csvContent += row.Investigation + "," + row.No_of_Requests + "," + row.Percentage + "\n";
                                                        });

                                                        var encodedUri = encodeURI(csvContent);
                                                        var link = document.createElement("a");
                                                        link.setAttribute("href", encodedUri);
                                                        link.setAttribute("download", "lab_summary.csv");
                                                        document.body.appendChild(link);
                                                        link.click();
                                                        document.body.removeChild(link);
                                                    });
                                                </script>

                                    <?php
                                                } else {
                                                    echo '<br>No Data Available!';
                                                }
                                            } else {
                                                echo '<br>Invalid Dates';
                                            }
                                        }
                                    ?>





                                    </div>










                                </div>
                            </div>


                        </div>
                    </div>

                <?php  } ?>
                <?php include("../inc/lab_mdl.php"); ?>

                <?php include("search_modal.php") ?>
                <?php include('../modal_lock.php'); ?>



            </div>

        </div>
    </div>
    <?php include("../inc/footer_scripts.php"); ?>

    <?php if (isset($_GET['inr'])) { ?>
        <script>
            toastr.error('This patient is not currently under any Insurance status!', 'Error', {
                timeOut: 5000
            })
        </script>
    <?php } ?>








    <script src="../js/idle.js"></script>

    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/datatables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: [{
                        extend: 'copy'
                    },
                    {
                        extend: 'csv'
                    },
                    {
                        extend: 'excel',
                        title: 'ExampleFile'
                    },
                    {
                        extend: 'pdf',
                        title: 'ExampleFile'
                    },

                    {
                        extend: 'print',
                        customize: function(win) {
                            $(win.document.body).addClass('white-bg');
                            $(win.document.body).css('font-size', '10px');

                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    }
                ]

            });

        });
    </script>

    <?php include('alert.php'); ?>
    <script>
        var prevOut = localStorage.getItem('lab_outpatient') ?
            parseInt(localStorage.getItem('lab_outpatient')) : 0;

        var prevIn = localStorage.getItem('lab_inpatient') ?
            parseInt(localStorage.getItem('lab_inpatient')) : 0;


        function checkLab() {


            ////alert();


            $.ajax({
                url: 'fetch_lab_count.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {

                    let outpatient = parseInt(data.outpatient) || 0;
                    let inpatient = parseInt(data.inpatient) || 0;

                    let total = outpatient + inpatient;




                    if (total > 0) {

                        $('#labAlertBox').fadeIn();

                        $('#lab_outpatient').text(outpatient);
                        $('#lab_inpatient').text(inpatient);

                        // 🔊 SOUND TRIGGER
                        if (outpatient > prevOut || inpatient > prevIn) {

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

                    prevOut = outpatient;
                    prevIn = inpatient;

                    localStorage.setItem('lab_outpatient', outpatient);
                    localStorage.setItem('lab_inpatient', inpatient);
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

</body>

</html>