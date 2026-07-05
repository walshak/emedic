<?php

$current_date = date('Y-m-d');

if (isset($_POST['show_report_by_type_and_date'])) {
    $request_type = $_POST['request_type'];
    $start = $_POST['start'];
    $end = $_POST['end'];
}

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
                <a href="dialysis.php" class="btn btn-info btn-xs pull-right"><i class="fa fa-home"></i> Dialysis List</a>
            </div>
            <div class="ibox-content">


                <!-- <strong style="color:#F00">What to do here ... </strong><br> -->

                <form action="#" method="post" id="show_report_by_type_and_date_form_">

                    <div class="form_sep">

                        <label for="reg_input_no" class="req">Select Dialysis Type</label>
                        <select name="request_type" id="request_type" class="form-control">
                            <option selected="selected" value="">Select ...</option>

                            <?php
                            $dept_id = $_SESSION['dept_id'];
                            $stmt = $db->query("SELECT DISTINCT request_type FROM dialysis WHERE date_marked_completed IS NULL ORDER BY request_type");
                            $distinct_request_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($distinct_request_types as $key => $row) { ?>
                                <option value="<?php echo $row["request_type"] ?>"><?php echo $row["request_type"]; ?></option>
                            <?php }

                            ?>
                        </select>
                    </div>

                    <div class="form_sep">
                        <strong>Filter By Date Performed</strong>
                        <div class="form-group" id="">
                            <div class="input-daterange input-group" id="">
                                <input type="date" class="input-sm form-control" name="start" id="start" value="<?php echo date("Y-m-d"); ?>" />
                                <span class="input-group-addon">to</span>
                                <input type="date" class="input-sm form-control" name="end" id="end" value="<?php echo date("Y-m-d"); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form_sep">
                        <button class="btn btn-success btn-sm" type="submit" name="show_report_by_type_and_date">Show Report </button>
                    </div>
                </form>


            </div>
        </div>

        <h4>Monthly Summary </h4>
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Report Form</h5>
            </div>
            <div class="ibox-content">
                <form action="#" method="post" id="show_report_by_type_and_date_form_">
                    <div class="form_sep">
                        <strong>Month-Year</strong>
                        <div class="form-group" id="data_5">
                            <input type="month" name="month-year" id="month-year" class="form-control">
                        </div>
                    </div>
                    <div class="form_sep">
                        <button class="btn btn-success btn-sm" type="submit" name="show_monhly_report">Show Report </button>
                    </div>
                </form>


            </div>
        </div>

        <div class="ibox ">
            <div class="ibox-title">
                <h5>Report Form</h5>
            </div>
            <div class="ibox-content">


                <!-- <strong style="color:#F00">What to do here ... </strong><br> -->

                <form action="<?php echo $editFormAction; ?>" method="post" id="show_report_by_patient_form_">
                    <div class="form_sep">
                        <strong>Filter By Patient</strong>
                        <div class="form-group" id="data_5">
                            <input type="text" class="form-control" name="hospital_no" id="hospital_no" placeholder="Patient Hospital No" required>
                        </div>
                    </div>




                    <div class="form_sep">
                        <button class="btn btn-success btn-sm" type="submit" name="show_report_by_hospital_no">Show Report </button>
                    </div>
                </form>


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
                    $request_type = $_POST['request_type'];
                    $start = $_POST['start'];
                    $end = $_POST['end'];

                    $query_sting = "";
                    if ($current_date != $start || $current_date != $end || $request_type != "") {

                        if ($current_date != $start || $current_date != $end) {
                            $query_sting .= " AND DATE(d.date_marked_completed) BETWEEN '$start' and '$end' ";
                        }

                        if ($request_type != "") {
                            $query_sting .= " AND d.request_type='$request_type'";
                        }

                        $sql = "SELECT 
                                                            d.*, 
                                                            e.gender 
                                                        FROM 
                                                            dialysis d 
                                                        INNER JOIN 
                                                            enrollee e 
                                                        ON 
                                                            d.hospital_no = e.hospital_no 
                                                        WHERE 
                                                            d.status = '1' 
                                                            $query_sting
                                                            AND EXISTS (
                                                                SELECT 1 
                                                                FROM dialysis_data dd 
                                                                WHERE dd.dialysis_id = d.id
                                                            )
                                                        LIMIT 
                                                            20000;
                                                        ";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        $sn = 1;
                        $tr = '';
                        $total_male = 0;
                        $total_female = 0;

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $patient_name = $row['patient_name'];
                            $request_type = $row['request_type'];
                            $performed_date_time = date('d M, Y h:i:s A', strtotime('' . $row['performed_date'] . ' ' . $row['performed_time']));
                            if ($row['gender'] == 'Male') {
                                $total_male++;
                            } else if ($row['gender'] == 'Female') {
                                $total_female++;
                            }
                            $tr .= '
                                                    <tr>
                                                        <td>' . $sn++ . '</td>
                                                        <td>' . $patient_name . '</td>
                                                        <td>' . $request_type . '</td>
                                                        <td>' . $performed_date_time . '</td>
                                                        <td><button class="btn btn-xs btn-success">view</button></td>
                                                    </tr>
                                                ';
                        }                ?>
                        <h4>
                            Total Session: <?php echo $stmt->rowCount(); ?><br>
                            Total Male: <?php echo $total_male; ?><br>
                            Total Female: <?php echo $total_female; ?><br>
                        </h4>
                        <table class="table table-stripped table-bordered dataTables-example" id="reportDatable">
                            <thead>
                                <tr>
                                    <td>SN</td>
                                    <td>NAME</td>
                                    <td>REQUEST TYPE</td>
                                    <td>PERFORM DATE/TIME</td>
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
                    } else {
                        echo '<hr><h3 class="text-center"> <a href="' . $editFormAction . '" class="btn btn-sm btn-success">Refresh</a></h3>';
                    }
                } else if (isset($_POST['show_report_by_hospital_no'])) {
                    $hospital_no = $_POST['hospital_no'];

                    $sql = "SELECT * FROM dialysis where status = '1' AND hospital_no = ? ORDER BY id DESC ";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(array($hospital_no));

                    if ($stmt->rowCount()) {


                    ?>
                        <table class="table table-stripped table-bordered dataTables-example" id="reportDatable">
                            <thead>
                                <tr>
                                    <td>SN</td>
                                    <td>NAME</td>
                                    <td>REQUEST TYPE</td>
                                    <td>PERFORM DATE/TIME</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sn = 1;
                                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($rows as $key => $row) {
                                    $patient_name = $row['patient_name'];
                                    $request_type = $row['request_type'];
                                    $performed_date_time = date('d M, Y h:i:s A', strtotime('' . $row['performed_date'] . ' ' . $row['performed_time']));

                                    echo  '
                                                    <tr>
                                                        <td>' . $sn++ . '</td>
                                                        <td>' . $patient_name . '</td>
                                                        <td>' . $request_type . '</td>
                                                        <td>' . $performed_date_time . '</td>
                                                        <td><button class="btn btn-xs btn-success">view</button></td>
                                                    </tr>
                                                ';
                                }
                                ?>

                            </tbody>
                        </table>
                    <?php
                    } else {
                        echo '<hr><h3 class="text-center"> <span style="font-size:100px"> &#x1F615; </span> <br>No match found <br> <hr> <a href="' . $editFormAction . '" class="btn btn-sm btn-success">Refresh</a></h3>';
                    }
                } else if (isset($_REQUEST['show_monhly_report'])) {


                    $monthYear = cleanInput($_POST['month-year']) . '%';

                    /* ======================================================
   MAIN PER-PATIENT QUERY
   ====================================================== */
                    $sql = "
SELECT 
    d.hospital_no,
    d.patient_name,
    d.request_type,

    COUNT(DISTINCT d.id) AS total_sessions,

    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%Plasmapheresis%' THEN 1 ELSE 0 END),0) AS total_plex,
    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%Erythropioetin%' THEN 1 ELSE 0 END),0) AS total_erythr,
    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%iron%' THEN 1 ELSE 0 END),0) AS total_iron,
    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%Blood Transfusion%' THEN 1 ELSE 0 END),0) AS total_blood

FROM dialysis d
LEFT JOIN patient_ap_services pas 
    ON pas.hospital_no = d.hospital_no
    AND pas.date_entry LIKE :pasMonth
    AND pas.paystatus = '1'

WHERE d.date_marked_completed LIKE :dialMonth
AND d.status = '1'

GROUP BY d.hospital_no
ORDER BY d.patient_name ASC
";

                    $stmt = $db->prepare($sql);
                    $stmt->execute(array(
                        'pasMonth'  => $monthYear,
                        'dialMonth' => $monthYear
                    ));
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    /* ======================================================
   REQUEST TYPE SUMMARY QUERY
   ====================================================== */
                    $sqlType = "
SELECT 
    d.request_type,

    COUNT(DISTINCT d.id) AS total_sessions,

    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%Plasmapheresis%' THEN 1 ELSE 0 END),0) AS total_plex,
    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%Erythropioetin%' THEN 1 ELSE 0 END),0) AS total_erythr,
    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%iron%' THEN 1 ELSE 0 END),0) AS total_iron,
    COALESCE(SUM(CASE WHEN pas.item_services LIKE '%Blood Transfusion%' THEN 1 ELSE 0 END),0) AS total_blood

FROM dialysis d
LEFT JOIN patient_ap_services pas 
    ON pas.hospital_no = d.hospital_no
    AND pas.date_entry LIKE :pasMonth
    AND pas.paystatus = '1'

WHERE d.date_marked_completed LIKE :dialMonth
AND d.status = '1'

GROUP BY d.request_type
ORDER BY d.request_type ASC
";

                    $stmtType = $db->prepare($sqlType);
                    $stmtType->execute(array(
                        'pasMonth'  => $monthYear,
                        'dialMonth' => $monthYear
                    ));
                    $typeRows = $stmtType->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!empty($rows)) { ?>

                        <h3 class="text-center mb-3">
                            MONTHLY REPORT STATISTIC (Dialysis Mark Completed Only)
                        </h3>

                        <!-- ================== PER PATIENT TABLE ================== -->
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>SN</th>
                                    <th>SEX</th>
                                    <th>AGE</th>
                                    <th>HOSP ID</th>
                                    <th>NAME</th>
                                    <th>REQUEST TYPE</th>
                                    <th>HD/HDF</th>
                                    <th>PLEX</th>
                                    <th>ERYTHR</th>
                                    <th>IRON</th>
                                    <th>BLOOD</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $sn = 1;
                                $gSession = $gPlex = $gEry = $gIron = $gBlood = 0;

                                foreach ($rows as $row) {

                                    $patient = $Patient->getByHospitalNo($row['hospital_no']);

                                    $sex = '';
                                    $age_full = '';

                                    if ($patient) {
                                        $sex = strtoupper(isset($patient->gender) ? $patient->gender : '');
                                        if (!empty($patient->dob)) {
                                            $dob = new DateTime($patient->dob);
                                            $age = $dob->diff(new DateTime())->y;
                                            $age_full = $age . ' yrs';
                                        }
                                    }

                                    $gSession += $row['total_sessions'];
                                    $gPlex    += $row['total_plex'];
                                    $gEry     += $row['total_erythr'];
                                    $gIron    += $row['total_iron'];
                                    $gBlood   += $row['total_blood'];
                                ?>
                                    <tr>
                                        <td><?php echo $sn++; ?></td>
                                        <td><?php echo $sex; ?></td>
                                        <td><?php echo $age_full; ?></td>
                                        <td><?php echo htmlspecialchars($row['hospital_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['request_type']); ?></td>
                                        <td><?php echo $row['total_sessions']; ?></td>
                                        <td><?php echo $row['total_plex']; ?></td>
                                        <td><?php echo $row['total_erythr']; ?></td>
                                        <td><?php echo $row['total_iron']; ?></td>
                                        <td><?php echo $row['total_blood']; ?></td>
                                    </tr>
                                <?php } ?>

                                <tr class="table-dark fw-bold">
                                    <td colspan="6" class="text-end">TOTAL</td>
                                    <td><?php echo $gSession; ?></td>
                                    <td><?php echo $gPlex; ?></td>
                                    <td><?php echo $gEry; ?></td>
                                    <td><?php echo $gIron; ?></td>
                                    <td><?php echo $gBlood; ?></td>
                                </tr>

                            </tbody>
                        </table>


                        <!-- ================== REQUEST TYPE SUMMARY ================== -->
                        <?php if (!empty($typeRows)) { ?>

                            <h4 class="mt-5 text-center">SUMMARY BY REQUEST TYPE</h4>

                            <table class="table table-bordered table-striped">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>REQUEST TYPE</th>
                                        <th>HD/HDF</th>
                                        <th>PLEX</th>
                                        <th>ERYTHR</th>
                                        <th>IRON</th>
                                        <th>BLOOD</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $tSession = $tPlex = $tEry = $tIron = $tBlood = 0;

                                    foreach ($typeRows as $tr) {

                                        $tSession += $tr['total_sessions'];
                                        $tPlex    += $tr['total_plex'];
                                        $tEry     += $tr['total_erythr'];
                                        $tIron    += $tr['total_iron'];
                                        $tBlood   += $tr['total_blood'];
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tr['request_type']); ?></td>
                                            <td><?php echo $tr['total_sessions']; ?></td>
                                            <td><?php echo $tr['total_plex']; ?></td>
                                            <td><?php echo $tr['total_erythr']; ?></td>
                                            <td><?php echo $tr['total_iron']; ?></td>
                                            <td><?php echo $tr['total_blood']; ?></td>
                                        </tr>
                                    <?php } ?>

                                    <tr class="table-dark fw-bold">
                                        <td>TOTAL</td>
                                        <td><?php echo $tSession; ?></td>
                                        <td><?php echo $tPlex; ?></td>
                                        <td><?php echo $tEry; ?></td>
                                        <td><?php echo $tIron; ?></td>
                                        <td><?php echo $tBlood; ?></td>
                                    </tr>

                                </tbody>
                            </table>

                        <?php } ?>



                        <div class="mb-2 text-end">
                            <a href="export_monthly_report_dialysis.php?month=<?php echo urlencode($_POST['month-year']); ?>"
                                class="btn btn-success btn-sm">
                                Download
                            </a>
                        </div>

                    <?php } else { ?>

                        <h4 class="text-center text-muted">No result found!</h4>

                    <?php }
                } else {
                    ?>
                    <form action="<?php echo $editFormAction; ?>" method="get" id="report_year_form">
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
                        $sql = "SELECT COUNT(id) total  FROM `dialysis` WHERE `date_marked_completed` LIKE '%$year_-$months_arr->value-%'  LIMIT 20000";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                        array_push($dialysis_by_month, $row['total']);
                        array_push($months_arr_label, $months_arr->month);
                    }
                    ?>
                    <div class="row">

                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-title">
                                    <h5>[<?php echo $year_; ?>] Dialysis Report By Month
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
                    </div>

                    <div>
                        <h4>[<?php echo $year_; ?>] Dialysis Report By Type
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
                                foreach ($distinct_request_types as $key => $distinct_request_type) {
                                    echo '<tr>
                                                    <td>#</td>
                                                      <td>' . $distinct_request_type["request_type"] . '</td>
                                                ';
                                    $total = 0;
                                    foreach ($months_array as $key => $months_arr_lab) {

                                        $sql = "SELECT COUNT(id) total  FROM `dialysis` WHERE `date_marked_completed` LIKE '%$year_-$months_arr_lab->value-%' AND request_type = ?";
                                        $stmt = $db->prepare($sql);
                                        $stmt->execute(array($distinct_request_type["request_type"]));
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
                    label: "No. of sessions",
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

        //     var barData = {
        //         labels: ["January", "February", "March", "April", "May", "June", "July"],
        //         datasets: [
        //             {
        //                 label: "Data 1",
        //                 backgroundColor: 'rgba(220, 220, 220, 0.5)',
        //                 pointBorderColor: "#fff",
        //                 data: [65, 59, 80, 81, 56, 55, 40]
        //             },
        //             {
        //                 label: "Data 2",
        //                 backgroundColor: 'rgba(26,179,148,0.5)',
        //                 borderColor: "rgba(26,179,148,0.7)",
        //                 pointBackgroundColor: "rgba(26,179,148,1)",
        //                 pointBorderColor: "#fff",
        //                 data: [28, 48, 40, 19, 86, 27, 90]
        //             }
        //         ]
        //     };

        // });


        //   var barOptions = {
        //         responsive: true
        //     };

        //   var ctx2 = document.getElementById("barChart").getContext("2d");
        //     new Chart(ctx2, {type: 'bar', data: barData, options:barOptions});

    });
</script>