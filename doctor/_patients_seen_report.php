<?php
// Initialize variables for start and end dates
$startDate = date('Y-m-d'); // Default to current date
$endDate = date('Y-m-d');   // Default to current date

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if start date is posted and not empty
    if (!empty($_POST['start'])) {
        $startDate = $_POST['start'];
    }

    // Check if end date is posted and not empty
    if (!empty($_POST['end'])) {
        $endDate = $_POST['end'];
    }
}
?>



<h4 class="text-danger">Filter Patient(s) Seen By Dates/Services Type</h4>
<hr>

<form method="POST" id="ext_form" action="index.php">

    <div class="row">
        <div class="col-md-4">
            <div class="form_sep">
                <label for="reg_input_no" class="">Set Dates Range</label><br>
                <div class="form_sep" id="">
                    <div class="input- input-group" id="">
                        <input type="date" class="input-sm form-control patient_seen_report_elem" id="start" name="start" value="<?php echo htmlspecialchars($startDate); ?>" />
                        <span class="input-group-addon">to</span>
                        <input type="date" class="input-sm form-control patient_seen_report_elem" id="end" name="end" value="<?php echo htmlspecialchars($endDate); ?>" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form_sep">
                <label for="reg_input_no" class="">Select All or Specify Service Type</label><br>
                <div class="form_sep" id="">
                    <select name="patient_seen_consultation_services" data-placeholder="Search..." id="patient_seen_consultation_services" class="input-sm chosen-select patient_seen_report_elem">
                        <option selected value="">-- select --</option>
                        <option value="">All Services</option>
                        <?php echo $service_options; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form_sep">
                <label for="reg_input_no" class="">.</label><br>
                <button class="btn btn-success btn-sm" type="submit" name="patient_seen_report_btn" id="">Apply</button>
            </div>
        </div>
    </div>
</form>
<hr>




<?php

if (isset($_POST['patient_seen_report_btn'])) {

    $fullname = $_SESSION['fullname'];
    $doctort_id = $_SESSION['id'];
    $created_by = "n.created_by = '$doctort_id'";
    $search_query_2 = "doctor_id = '$doctort_id'";

    $query_service = "";
    echo $services_name = $_POST['patient_seen_consultation_services'];
    $from = $_POST['start'];
    $to = $_POST['end'];

    $sql_strings = " ";
    $sql_strings_notes = '';
    $sql_strings2 = '';
    if (!empty($from)) {
        if (empty($to)) {
            $to = date('Y-m-d');
        }
        $sql_strings2 .= " AND date(n.date_entry) BETWEEN '$from'  AND '$to' ";
    }


    $tr = '';
    $sn = 1;
    $total_cost = 0;
    $search_query = $pp = null;
    $counts[] = null;

    if (empty($services_name)) {
        $stmt = $db->prepare(" SELECT distinct n.app_no,n.hospital_no FROM notes n WHERE $created_by $sql_strings2 order by n.sn desc");
        $stmt->execute();
        $stmt_count = $stmt->rowCount();
        $distict_appoint_of_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {

        $stmt = $db->prepare(" SELECT distinct n.app_no,n.hospital_no 
		FROM notes n 
		inner join apptm a on n.app_no = a.appt_no 
		WHERE $created_by $sql_strings2
            AND a.service_id = ?  order by a.sn");
        $stmt->execute(array($services_name));
        $stmt_count = $stmt->rowCount();
        $distict_appoint_of_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($stmt_count == 0) {
        echo '<strong style="color:red;">No Record to Display!</strong>';
    }





    //////////// dynamic arrays
    $cat_types = [];
    $cat_types_values = [];

    foreach ($distict_appoint_of_patients as $key => $seen_by_app) {
        $sttmt = $db->prepare(" SELECT pay,claim_amt,hospital_no, item_services,cat_type FROM patient_ap_services WHERE  app_no = ? AND paystatus = 1 and serv_group='Consultation'");
        $sttmt->execute(array($seen_by_app['app_no']));

        $sttmt_count = $sttmt->execute();
        if ($sttmt_count > 0) {

            while ($seen_by_row = $sttmt->fetch(PDO::FETCH_ASSOC)) {
                $service_name = $seen_by_row['cat_type'] . ' (' . $seen_by_row['item_services'] . ')';


                if (!in_array($seen_by_row['cat_type'], $cat_types)) {
                    array_push($cat_types, $seen_by_row['cat_type']);
                }

                $index =  array_search($seen_by_row['cat_type'], $cat_types);

                if ($seen_by_row['pay'] > 0) {
                    $pay = $seen_by_row['pay'];
                } else {
                    $pay = $seen_by_row['claim_amt'];
                }

                $total_cost += $pay;
                $cat_types_values[$index] += $pay;
                $counts[$index] += $sttmt_count;

                // if ($coming_from != 'personal_doc') {
                /// $pp = 'N' . number_format($pay);
                //  } else {
                /// $pp = '';
                // }

                $tr .= '
                                <tr>
                                <td>' . $sn++ . '</td>
                                <td>' . $seen_by_app["hospital_no"] . '</td>
                                <td>' . $seen_by_app["app_no"] . '</td>
                                <td>' . $service_name . '</td>
                                <td>' . $pp . '</td>
                                </tr>
                            ';
            }
        }

        //// other services aside appointment ///

        $sttmt = $db->prepare(" SELECT sum(pay) pay,sum(claim_amt) claim_amt, item_services, cat_type,prepared_by
			FROM patient_ap_services WHERE app_no = ? 
              AND paystatus = 1 and serv_group!='Consultation' GROUP BY cat_type ");
        $sttmt->execute(array(
            $seen_by_app['app_no']
        ));

        $sttmt_count = $sttmt->execute();
        if ($sttmt_count > 0) {
            while ($seen_by_row = $sttmt->fetch(PDO::FETCH_ASSOC)) {

                if (!in_array($seen_by_row['cat_type'], $cat_types)) {
                    array_push($cat_types, $seen_by_row['cat_type']);
                }

                $index =  array_search($seen_by_row['cat_type'], $cat_types);

                if ($seen_by_row['pay'] > 0) {
                    $pay = $seen_by_row['pay'];
                } else {
                    $pay = $seen_by_row['claim_amt'];
                }

                $total_cost += $pay;
                $cat_types_values[$index] += $pay;
                $counts[$index] += $sttmt_count;

                // if ($coming_from != 'personal_doc') {
                //    $pp = 'N' . number_format($pay);
                // } else {
                //     $pp = '';
                //  }

                $tr .= '
                                <tr>
                                <td>' . $sn++ . '</td>
                                <td>' . $seen_by_app["hospital_no"] . '</td>
                                <td>' . $seen_by_app["app_no"] . '</td>
                                <td>' . $seen_by_row["cat_type"] . '</td>
                                <td>' . $pp . '</td>
                                </tr>
                            ';
            }
        }
    }


    $labels = $cat_types;
    $amount_value = $cat_types_values;
}








?>


<div class="row">
    <div class="col-md-6"><canvas id="barChart" height="200"></canvas></div>
    <div class="col-md-6"><canvas id="barChart2" height="200"></canvas></div>
</div>


<div>
    <?php
    if (!empty($tr)) {
    ?>
        <div class="text-right">
            <button class="btn btn-sm btn-primary" onclick="exportTableToExcel('report_tbl', 'Patients seen statistics')">Export to excel</button>
        </div>
        <table class="table table-bordered" style="font-size:15px" id="report_tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hospital #</th>
                    <th>Appointment #</th>
                    <th>Service Type</th>
                    <?php //if ($coming_from != 'personal_doc') { 
                    ?><th>Cost</th><?php // } 
                                    ?>
                </tr>
            </thead>
            <tbody>
                <?php echo $tr; ?>
                <tr>
                    <td></td>
                    <td></td>
                    <?php ///if ($coming_from != 'personal_doc') { 
                    ?><td>Total =</td>
                    <td>N<?php //echo number_format($total_cost); 
                            ?></td> <?php // } 
                                    ?>
                </tr>
            </tbody>
        </table>
    <?php
    }
    ?>
</div>



<!-- ChartJS-->
<script src="../js/plugins/chartJs/Chart.min.js"></script>
<script>
    $(document).ready(function() {
        $('#reportDatable').DataTable();



        var ctx = document.getElementById("barChart").getContext("2d");
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [

                    {
                        label: "Total",
                        backgroundColor: 'rgba(26,179,148,0.5)',
                        borderColor: "rgba(26,179,148,0.7)",
                        pointBackgroundColor: "rgba(26,179,148,1)",
                        pointBorderColor: "#fff",
                        data: <?php echo json_encode($counts); ?>
                    },
                ]
            },
            options: {
                responsive: true
            }
        });

        var ctx2 = document.getElementById("barChart2").getContext("2d");
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: " Cost",
                    backgroundColor: 'rgba(26,179,255,0.5)',
                    borderColor: "rgba(26,179,255,0.7)",
                    pointBackgroundColor: "rgba(26,179,255,1)",
                    pointBorderColor: "#fff",
                    data: <?php echo json_encode($amount_value); ?>
                }]
            },
            options: {
                responsive: true
            }
        });

    });

    function exportTableToExcel(table_id, filename = 'Unnamed_file') {

        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(table_id);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

        // Specify file name
        filename = filename ? filename + '.xls' : 'excel_data.xls';

        // Create download link element
        downloadLink = document.createElement("a");

        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            // Create a link to the file
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

            // Setting the file name
            downloadLink.download = filename;

            //triggering the function
            downloadLink.click();
        }
    }
</script>