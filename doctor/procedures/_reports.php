<?php
$current_date = date('Y-m-d');
if (isset($_GET['yr'])) {
    $year_ = $_GET['yr'];
    $month_ = $_GET['mnth'];
} else {
    $year_ = date('Y');
    $month_ = date('m');
}
?>


<div class="row animated fadeInRight">
    <div class="col-lg-4">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Report Form</h5>
                <a href="index.php?procedure" class="btn btn-xs btn-success pull-right"><i class="fa fa-home"></i> Home</a>
            </div>
            <div class="ibox-content">

                <form action="#" method="post" id="show_report_by_type_and_date_form_">

                    <div class="form_sep">
                        <label for="reg_input_no" class="">Select Procedure Type</label>
                        <select name="procedures" id="procedures" class="form-control">
                            <option selected="selected" value="">Select ...</option>
                            <?php
                            $dept_id = $_SESSION['dept_id'];
                            $stmt = $db->query("SELECT  DISTINCT procedures FROM procedures WHERE procedures IS NOT NULL AND procedures !='' ORDER BY procedures ");
                            $distinct_request_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($distinct_request_types as $key => $row) { ?>
                                <option value="<?php echo $row["procedures"] ?>"><?php echo $row["procedures"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="">Filter By Result/Outcome</label>
                        <select name="post_op_results" id="post_op_results" class="form-control">
                            <option selected="selected" value="">Select ...</option>
                            <?php
                            $dept_id = $_SESSION['dept_id'];
                            $stmt = $db->query("SELECT  DISTINCT post_op_results FROM procedures");
                            $distinct_post_op_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($distinct_post_op_results as $key => $row) {
                                $outcome = $row["post_op_results"];
                                if ($row["post_op_results"] == null) {
                                    $outcome = 'No Result';
                                }
                            ?>
                                <option value="<?php echo $row["post_op_results"] ?>"><?php echo $outcome; ?></option>
                            <?php } ?>
                        </select>
                    </div>


                    <div class="form_sep">
                        <strong>Filter By Consultant</strong>
                        <div class="form-group" id="data_5">
                            <select name="consultant" id="consultant" class="form-control">
                                <option selected="selected" value="">Select ...</option>
                                <?php
                                $dept_id = $_SESSION['dept_id'];
                                $stmt = $db->query("SELECT DISTINCT consultant_name FROM procedures where consultant_name!=''");
                                $distinct_post_op_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_post_op_results as $key => $row) {
                                    // if(!empty($row["consultant_name"])){
                                ?>
                                    <option value="<?php echo $row["consultant_name"]; ?>"><?php echo $row["consultant_name"]; ?></option>
                                <?php
                                    //    }
                                } ?>
                            </select>

                        </div>
                    </div>

                    <div class="form_sep">
                        <strong>Filter By Date Performed</strong>
                        <div class="form-group" id="data_5">
                            <div class="col-md-6"><input type="date" class="input-sm form-control" name="start" id="start" value="<?php echo date("Y-m-d"); ?>" /></div>
                            <div class="col-md-6"> <input type="date" class=" form-control" name="end" id="end" value="<?php echo date("Y-m-d"); ?>" /></div>

                        </div>
                    </div>




                    <div class="form_sep">
                        <button class="btn btn-success btn-sm" type="submit" name="show_report_by_type_and_date">Show Report </button>
                    </div>
                </form>



                <hr>
                <h4>RESOURCE PERSONS STATISTICS</h4>

                <form action="#" method="post" id="show_report_by_type_and_date_form_">

                    <div class="form_sep">
                        <strong>Filter By Resource Persons</strong>
                        <div class="form-group" id="data_5">
                            <select name="resourse_ppl" id="resourse_ppl" class="form-control">
                                <option selected="selected" value="">Select ...</option>
                                <?php
                                $dept_id = $_SESSION['dept_id'];
                                $stmt = $db->query("SELECT DISTINCT name,resource_sn FROM procedure_resources where resource_sn!=''");
                                $distinct_post_op_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_post_op_results as $key => $row) {
                                    // if(!empty($row["consultant_name"])){
                                ?>
                                    <option value="<?php echo $row["resource_sn"]; ?>"><?php echo $row["name"]; ?></option>
                                <?php
                                    //    }
                                } ?>
                            </select>

                        </div>
                    </div>


                    <div class="form_sep">
                        <strong>Filter By Date Performed</strong>
                        <div class="form-group" id="data_5">
                            <div class="col-md-6"><input type="date" class="input-sm form-control" name="start" id="start" value="<?php echo date("Y-m-d"); ?>" /></div>
                            <div class="col-md-6"> <input type="date" class=" form-control" name="end" id="end" value="<?php echo date("Y-m-d"); ?>" /></div>

                        </div>
                    </div>

                    <div class="form_sep">
                        <button class="btn btn-success btn-sm" type="submit" name="show_resource_stat">Show Report </button>
                    </div>


                </form>



                <hr>

                <div class="form_sep">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#detailedReportModal">
                        <i class="fa fa-bar-chart"></i> Generate Detailed Procedure Report
                    </button>
                </div>



            </div>

        </div>



    </div>
    <div class="col-lg-8">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Reports</h5>
            </div>
            <div class="ibox-content">

                <?php

                if (isset($_POST['show_report_by_type_and_date'])) {
                    $procedures = $_POST['procedures'];
                    $post_op_results = $_POST['post_op_results'];
                    ////performed date
                    $start = $_POST['start'];
                    $end = $_POST['end'];
                    $consultant = $_POST['consultant'];
                    $query_sting = " ";
                    if ($current_date != $start || $current_date != $end) {
                        $query_sting .= " AND p.performed_date BETWEEN '$start' AND '$end' ";
                    }

                    if ($consultant != "") {
                        $query_sting .= " AND p.consultant_name='$consultant'";
                    }

                    if ($procedures != "") {
                        $query_sting .= " AND p.procedures='$procedures'";
                    }

                    if ($post_op_results != "") {
                        $query_sting .= " AND p.post_op_results='$post_op_results'";
                    }
                    // $sql = "SELECT p.*, e.gender FROM procedures p INNER JOIN enrollee e on p.hospital_no = e.hospital_no where 1 $query_sting LIMIT 20000";
                    $sql = "SELECT p.* FROM procedures p  where 1 $query_sting LIMIT 20000";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    $sn = 1;
                    $tr = '';
                    $total_male = 0;
                    $total_female = 0;

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $pr = $row['sn'];
                        $patient_name = $row['name'];
                        $procedures = $row['procedures'];
                        $post_opt_notes_id = $row['post_opt_notes_id'];
                        $post_op_results = $row['post_op_results'];
                        $sale_no = $row['sale_no'];
                        $performed_date_time = date('d M, Y h:i:s A', strtotime('' . $row['performed_date '] . ' ' . $row['performed_time']));

                        /////////////// PATIENT GENDER 
                        $stmt2 = $db->prepare("SELECT gender FROM enrollee  WHERE hospital_no = ? ");
                        $stmt2->execute([$row['hospital_no']]);
                        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                        if ($row2['gender'] == 'Male') {
                            $total_male++;
                        } else if ($row2['gender'] == 'Female') {
                            $total_female++;
                        }
                        /////////////// END PATIENT GENDER 

                        /////////////// PAYMENT STATUS
                        $stmt = $db->prepare("SELECT sn,paystatus FROM patient_ap_services WHERE sn = ? AND hospital_no = ? AND drug_sn = ?  ");
                        $stmt->execute(array($sale_no, $row["hospital_no"], $row["service_id"]));
                        if ($stmt->rowCount() > 0) {
                            $rowx = $stmt->fetch();
                            $payment_sn = $rowx['sn'];

                            if ($rowx['paystatus'] == 1) {
                                $pay_status = 'Paid';
                            } else {
                                $pay_status = 'Pending';
                            }
                            /////////////// END  PAYMENT STATUS

                            /////////////// RESOURCE PERSONS
                            $resourse_persons_text = '<ul>';
                            $stmt3 = $db->prepare("SELECT name,role,resource_code FROM procedure_resources WHERE prdure_sn = ?  ");
                            $stmt3->execute(array($row['sn']));
                            if ($stmt3->rowCount() > 0) {
                                $resourse_persons = $stmt3->fetchAll();
                                foreach ($resourse_persons as $key => $resourse_person) {
                                    $resource_code = $resourse_person["resource_code"];

                                    if ($resource_code == 'rss') {
                                        $role = 'Surgeon';
                                    } else  if ($resource_code == 'ras') {
                                        $role = 'Assistant Surgeon';
                                    } else  if ($resource_code == 'ran') {
                                        $role = 'Anaesthetist';
                                    } else  if ($resource_code == 'rsn') {
                                        $role = 'Nurse';
                                    } else {
                                        $role = '';
                                    }


                                    $resourse_persons_text .= ' <li>' . $resourse_person["name"] . ' - ' . $role . '</li> ';
                                }
                            }
                            $resourse_persons_text .= '</ul>';
                            /////////////// END RESOURCE PERSONS


                            $tr .= '
                                                                <tr>
                                                                    <td>' . $sn++ . '</td>
                                                                    <td>' . $patient_name . '</td>
                                                                    <td>' . $procedures . '</td>
                                                                    <td>' . (!empty($post_op_results) ? 'Done <br> [' . $post_op_results . ']' : '<span class="text-danger">Not Done</span>') . '</td>
                                                                    <td>' . $pay_status . '</td>
                                                                    <td>' . $resourse_persons_text . '</td>
                                                                    <td>
                                                                    <b>Booked By:</b> ' . $row["prepared_by"] . '
                                                                    <br>  <b>Consultant</b> :' . $row["consultant_name"] . ' 
                                                                   <br> <b>Date Time:</b>' . date("d M, Y H:i A", strtotime("" . $row["date_entry"])) . '
                                                                    </td>
                                                                    <td><a href="index.php?procedure&pr=' . base64_encode(base64_encode($pr)) . '==" class="btn btn-xs btn-success">view</a></td>
                                                                </tr>
                                                            ';
                        }
                    }
                ?>
                    <h4>
                        Total Procedures: <?php echo $stmt->rowCount(); ?><br>
                        Total Male: <?php echo $total_male; ?><br>
                        Total Female: <?php echo $total_female; ?><br>
                        <?php
                        if ($current_date != $start || $current_date != $end) {
                            echo '<h4> Report from ' . date('d M, Y ', strtotime('' . $start)) . ' to ' . date('d M, Y', strtotime('' . $end)) . '</h4>';
                        } else {
                            echo '<h4> Report is based on current date </h4>';
                        }
                        ?>
                    </h4>
                    <table class="table table-stripped table-bordered dataTables-example" id="reportDatable">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Patient</td>
                                <td>Procedure </td>
                                <td>Status </td>
                                <td>Payment </td>
                                <td>Resource Persons </td>
                                <td>Datails</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            echo $tr;
                            ?>

                        </tbody>
                    </table>
                    <?php
                    // }else{
                    //     echo '<hr><h3 class="text-center"> <a href="'.$editFormAction.'" class="btn btn-sm btn-success">Refresh</a></h3>';

                    // }




                } elseif (isset($_POST['show_resource_stat'])) {
                    $resourse_ppl = $_POST['resourse_ppl'];
                    $start = $_POST['start'];
                    $end = $_POST['end'];


                    if (empty($resourse_ppl) || empty($start) || empty($end)) {
                        echo "Please provide all required fields.";
                        exit;
                    }

                    try {
                        $stmt = $db->prepare("SELECT p.* FROM procedures AS p 
                            INNER JOIN procedure_resources AS r ON r.prdure_sn = p.sn
                            WHERE DATE(date_entry) BETWEEN :start AND :end 
                            AND post_opt_notes_id IS NOT NULL 
                            AND resource_sn = :resource_sn
                            ORDER BY p.sn");

                        $stmt->bindParam(':start', $start);
                        $stmt->bindParam(':end', $end);
                        $stmt->bindParam(':resource_sn', $resourse_ppl);
                        $stmt->execute();

                        // Check row count
                        if ($stmt->rowCount() === 0) {
                            echo "<p>No records found for the specified criteria.</p>";
                        } else {
                            // Fetch procedures
                            $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $sn = 1; // Initialize the counter

                            // Initialize summary array
                            $summary = [];

                            // Process procedures and create a summary
                            foreach ($procedures as $procedure) {
                                $procName = $procedure['procedures']; // Assuming the procedure name field is named 'procedures'
                                if (!isset($summary[$procName])) {
                                    $summary[$procName] = 0; // Initialize count for this procedure
                                }
                                $summary[$procName]++; // Increment count for this procedure
                            }

                            // Display procedures in a table
                    ?>
                            <table class="table table-striped dataTables-example" border="2">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Procedure</th>
                                        <th>Consultant</th>
                                        <th>Result</th>
                                        <th>Performed</th>
                                        <th>Theater</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($procedures as $procedure) { ?>
                                        <tr>
                                            <td><?= $sn++; ?></td>
                                            <td><?= htmlspecialchars($procedure["procedures"]); ?></td>
                                            <td><?= htmlspecialchars($procedure["consultant_name"]); ?></td>
                                            <td><?= htmlspecialchars($procedure["post_op_results"]); ?></td>
                                            <td><?= date('d M, Y H:i A', strtotime($procedure["performed_date"])); ?></td>
                                            <td><?= htmlspecialchars($procedure["theater"]); ?></td>
                                            <td><?= htmlspecialchars($procedure["procedure_time"]); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <h3>Summary Report</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Procedure</th>
                                        <th>Total Occurrences</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($summary as $procName => $total) {
                                        echo "<tr>
                                                <td>" . htmlspecialchars($procName) . "</td>
                                                <td>" . $total . "</td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                    <?php
                        }
                    } catch (PDOException $e) {
                        echo "Error executing query: " . $e->getMessage();
                        exit;
                    }
                    ?>

                <?php } else {
                ?>
                    <form action="<?php echo $editFormAction; ?>" method="get" id="report_year_form" arial-url="">
                        <div class="text-right">
                            Year:
                            <select name="yr" id="report_year">
                                <?php


                                for ($i = $year_ - 3; $i <= date('Y'); $i++) {
                                    echo '<option value="' . $i . '" ' . ($year_ == $i ? 'selected' : '') . '>' . $i . '</option>';
                                }
                                ?>>
                            </select>
                        </div>
                    </form>
                    <?php
                    $dialysis_by_month = [];
                    $months_arr_label = [];
                    $months_array = months_array();

                    foreach ($months_array as $key => $months_arr) {
                        // print_r($months_arr->value);
                        $months_arr->value;
                        $sql = "SELECT COUNT(sn) total  FROM `procedures` WHERE `performed_date` LIKE '%$year_-$months_arr->value-%'  AND post_op_results IS NOT NULL LIMIT 20000";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                        array_push($dialysis_by_month, $row['total']);
                        array_push($months_arr_label, $months_arr->month);
                    }
                    ?>
                    <div class="row">

                        <div class="col-lg-9">
                            <div class="ibox float-e-margins light-card">
                                <div class="ibox-title">
                                    <h5>[<?php echo $year_; ?>] Procedure Report By Month
                                        <small></small>
                                    </h5>
                                </div>
                                <div class="ibox-content">
                                    <div>
                                        <canvas id="lineChart" height="140"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-3">
                            <ul class="list-group clear-list m-t">
                                <?php
                                $sn = 1;
                                $color = ["success", "info", "primary"];
                                $stmt = $db->query("SELECT  DISTINCT post_op_results FROM procedures WHERE   post_op_results IS NOT NULL ORDER BY procedures ");
                                $distinct_post_op_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_post_op_results as $key => $distinct_post_op_result) {

                                    if (strtolower($distinct_post_op_result['post_op_results']) == 'death') {
                                        $color_ = 'danger';
                                    } else if (strtolower($distinct_post_op_result['post_op_results']) == 'successful') {
                                        $color_ = 'primary';
                                    } else {
                                        $color_ = 'success';
                                    }


                                    $sql = "SELECT COUNT(sn) total  FROM `procedures` WHERE `post_op_results` LIKE ? AND `performed_date` LIKE '%$year_-%'  AND post_op_results IS NOT NULL ";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute(array($distinct_post_op_result['post_op_results']));
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                    <li class="list-group-item fist-item">
                                        <span class="pull-right">
                                            <?= $row["total"]; ?>
                                        </span>
                                        <span class="label label-<?= $color_; ?>"><?= $sn++; ?></span> <?= (empty($distinct_post_op_result['post_op_results']) ? 'No Result' : $distinct_post_op_result['post_op_results']); ?>
                                    </li>

                                <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <h4>[<?php echo $year_; ?>] Procedure Report By Type
                            <small></small>
                        </h4>
                        <table class="table table-bordered table-stripped">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Type</td>
                                    <?php
                                    foreach ($months_array as $key => $months_arr_lab) {
                                        echo '<td>' . $months_arr_lab->mnth . '</td>';
                                    }
                                    ?>
                                    <td>Total</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sn = 1;
                                foreach ($distinct_request_types as $key => $distinct_request_type) {
                                    echo '<tr>
                                                    <td>' . $sn++ . '</td>
                                                      <td>' . $distinct_request_type["procedures"] . '</td>
                                                ';
                                    $total = 0;
                                    foreach ($months_array as $key => $months_arr_lab) {

                                        $sql = "SELECT COUNT(sn) total  FROM `procedures` WHERE `performed_date` LIKE '%$year_-$months_arr_lab->value-%' AND procedures = ?  AND post_op_results IS NOT NULL";
                                        $stmt = $db->prepare($sql);
                                        $stmt->execute(array($distinct_request_type["procedures"]));
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo '<td class="text-center">' . $row["total"] . '</td>';
                                        $total += $row["total"];
                                    }
                                    echo '<td class="text-center">' . $total . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php


                        ?>
                    </div>
                <?php
                }

                ?>


            </div>
        </div>
    </div>


    <!-- Detailed Report Modal -->
    <div class="modal fade" id="detailedReportModal" tabindex="-1" role="dialog" aria-labelledby="detailedReportModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="detailedReportModalLabel"><i class="fa fa-file-text-o"></i> Detailed Procedure Report</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <form id="procedureDetailedReportForm" target="_blank" method="GET" action="procedure_detailed_report.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Department</label>
                                <select name="dept_id" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    // Select only departments that have matching dept_id in procedures
                                    $stmt = $db->query("
            SELECT DISTINCT d.sn, d.department 
            FROM department d
            INNER JOIN procedures p ON p.dept_id = d.sn
            WHERE d.department IS NOT NULL AND d.department != ''
            ORDER BY d.department ASC
        ");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
                                        echo "<option value='{$d['sn']}'>{$d['department']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label>Procedure</label>
                                <select name="procedure" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT procedures FROM procedures WHERE procedures!='' ORDER BY procedures ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                                        echo "<option value='{$p['procedures']}'>{$p['procedures']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Insurance Type</label>
                                <select name="insurance_type" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT insurance_type FROM procedures WHERE insurance_type!='' ORDER BY insurance_type ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                        echo "<option value='{$row['insurance_type']}'>{$row['insurance_type']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Consultant</label>
                                <select name="consultant_name" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT consultant_name FROM procedures WHERE consultant_name!='' ORDER BY consultant_name ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                        echo "<option value='{$row['consultant_name']}'>{$row['consultant_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Prepared By</label>
                                <select name="prepared_by" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT prepared_by FROM procedures WHERE prepared_by!='' ORDER BY prepared_by ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                        echo "<option value='{$row['prepared_by']}'>{$row['prepared_by']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Created By</label>
                                <select name="created_by" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT created_by FROM procedures WHERE created_by!='' ORDER BY created_by ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                        echo "<option value='{$row['created_by']}'>{$row['created_by']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Theater</label>
                                <select name="theater" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT theater FROM procedures WHERE theater!='' ORDER BY theater ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                        echo "<option value='{$row['theater']}'>{$row['theater']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Procedure Time</label>
                                <select name="procedure_time" class="form-control">
                                    <option value="">All</option>
                                    <?php
                                    $stmt = $db->query("SELECT DISTINCT procedure_time FROM procedures WHERE procedure_time!='' ORDER BY procedure_time ASC");
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                        echo "<option value='{$row['procedure_time']}'>{$row['procedure_time']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Payment Mode</label>
                                <select name="pay_mode" class="form-control">
                                    <option value="">All</option>
                                    <option value="CASH">Cash</option>
                                    <option value="POS">POS</option>
                                    <option value="TRANSFER">Transfer</option>
                                    <option value="CREDIT">Credit</option>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Payment Status</label>
                                <select name="paystatus" class="form-control">
                                    <option value="">All</option>
                                    <option value="1">Paid</option>
                                    <option value="0">Unpaid</option>
                                </select>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Hospital No (Optional)</label>
                                <input type="text" name="hospital_no" class="form-control" placeholder="Enter Hospital No">
                            </div>

                            <div class="col-md-6 mt-2">
                                <label>Date Range <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6"><input type="date" name="start_date" class="form-control" required></div>
                                    <div class="col-md-6"><input type="date" name="end_date" class="form-control" required></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="view_report" class="btn btn-primary"><i class="fa fa-print"></i> View & Print</button>
                        <button type="submit" name="export_csv" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>






</div>
<!-- ChartJS-->
<script src="../js/plugins/chartJs/Chart.min.js"></script>
<script>
    $(document).ready(function() {
        $('#reportDatable').DataTable();
        var lineData = {
            labels: <?php echo json_encode($months_arr_label); ?>,
            datasets: [

                {
                    label: "No. of procedures",
                    backgroundColor: 'rgba(26,179,148,0.5)',
                    borderColor: "rgba(26,179,148,0.7)",
                    pointBackgroundColor: "rgba(26,179,148,1)",
                    pointBorderColor: "#fff",
                    data: <?php echo json_encode($dialysis_by_month); ?>
                }
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
</script>