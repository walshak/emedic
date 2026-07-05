<?php
if (isset($_POST['apply_income'])) {

    $Services = $_POST['Services'];
    $staffname = $_POST['doctors'];

    if ($staffname == 'All Doctors') {
        echo '<h3 style="color:red;">All Doctors Option Selected Not Allowed. Choose Individual Doctor to See Reports</h3>';
        exit;
    }


    $parts = explode("__", $staffname);
    $doctort_id = $parts[0];
    $fullname = $parts[1];

    $search_query_2 = "doctor_id = '$doctort_id'";
    $search_created_by = "AND created_by = '$doctort_id'";

    $from = $_POST['start'];
    $to = $_POST['end'];

    $sql_strings2 = '';
    $sql_strings2x = '';
    if (!empty($from)) {
        if (empty($to)) {
            $to = date('Y-m-d');
        }
        $sql_strings2 .= "date(p.date_entry) BETWEEN '$from'  AND '$to'";
        $sql_strings2x .= "date(date_entry) BETWEEN '$from'  AND '$to'";
    }

    if ($Services == 'All Services') {
        $query_service = $sql_strings2;
        $query_service2 = $sql_strings2x;
    } else {

        if ($Services == 'Investigations') {
            $query_service = "(serv_group='Laboratory' or serv_group='Radiology')";
        } elseif ($Services == 'Consultation') {
            $query_service = "(serv_group='Consultation' or serv_group='Other Services')";
        } else {
            $query_service = "serv_group='$Services'";
        }
        $query_service2 = $query_service . ' AND ' . $sql_strings2x;
        $query_service = $query_service . ' AND ' . $sql_strings2;
    }

    $tr = '';
    $sn = 1;
    $total_cost = 0;

    $stmt = $db->prepare("SELECT distinct p.app_no,p.hospital_no 
    FROM notes n INNER JOIN patient_ap_services p on n.hospital_no = p.hospital_no 
		WHERE $query_service AND p.created_by = '$doctort_id'");
    $stmt->execute();
    $stmt_count = $stmt->rowCount();
    $distict_appoint_of_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($staffname != 'All Doctors' and $stmt_count == 0) {
        echo '<strong style="color:red;">No Record to Display!</strong>';
    }


    //////////// dynamic arrays
    $cat_types = [];
    $cat_types_values = [];

    foreach ($distict_appoint_of_patients as $key => $seen_by_app) {
        $sttmt = $db->prepare(" SELECT pay,claim_amt,hospital_no, item_services,cat_type
	  FROM patient_ap_services 
	  WHERE  app_no = ? AND paystatus = 1 and serv_group='Consultation'");
        $sttmt->execute(array($seen_by_app['app_no']));
        $sttmt_count = $sttmt->execute();
        if ($sttmt_count > 0) {

            while ($seen_by_row = $sttmt->fetch(PDO::FETCH_ASSOC)) {
                $service_name = $seen_by_row['cat_type'] . ' (' . $seen_by_row['item_services'] . ')';

                if ($seen_by_row['pay'] > 0) {
                    $pay = $seen_by_row['pay'];
                } else {
                    $pay = $seen_by_row['claim_amt'];
                }

                $total_cost += $pay;
                $cat_types_values[$index] += $pay;
                $counts[$index] += $sttmt_count;

                $pp = 'N' . number_format($pay);

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
			FROM patient_ap_services WHERE  $query_service2 AND  app_no = ? 
              AND paystatus = 1 and serv_group!='Consultation'  GROUP BY cat_type ");
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

                $pp = 'N' . number_format($pay);

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
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $tr; ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td>Total =</td>
                    <td>N<?php echo number_format($total_cost); ?></td>
                </tr>
            </tbody>
        </table>
    <?php
    }
    ?>
</div>