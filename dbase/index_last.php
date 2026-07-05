<?php include("../Connections/Conn.php");
session_start();

if (isset($_SESSION['username']) and $_SESSION['username'] != '' and $_SESSION['fullname'] != '') {

    try {
        $uname = $_SESSION['username'];
        $rights = $_SESSION['rights'];
        $unit_head = $_SESSION['unit_head'];

        $stmt22 = $db->query("SELECT sn FROM admin_users_logs WHERE username='$uname' order by sn desc limit 1");
        if ($stmt22->rowCount() > 0) {
            $rowx = $stmt22->fetch(PDO::FETCH_ASSOC);
            //$sn=$rowx['sn'];
            $_SESSION['last_login_id'] = $rowx['sn'];
        }
    } catch (Exception $e) {
        //var_dump($e);
    }
} else {
    try {
        $time = time();
        //			error_reporting(0);
        ob_start();
        ob_clean();

        if (!empty($_SESSION['username'])) {


            if (isset($_SESSION['last_action'])) {
                if (($time - $_SESSION['last_action']) > 60000) {
                    unset($_SESSION['username']);
                    unset($_SESSION['rights']);
                    unset($_SESSION['primary_rights']);
                    unset($_SESSION['fullname']);
                    unset($_SESSION['last_action']);
                    unset($_SESSION['specialist']);
                    unset($_SESSION['unit_head']);
                    session_destroy();
                    header('location:../index.php');
                }
            } else {
                $_SESSION['last_action'] = time();
                //					return true;
            }
        } else {
            //	var_dump($_SESSION);
            header("location: ../index.php");
        }
    } catch (Exception $e) {
        var_dump($e);
    }
}

$view_consultation_notes = 0;
if ($rights == 'NS' or $rights == 'DR'  or $rights == 'PH' or $rights == 'MD') {
    $view_consultation_notes = 1;
} else {
    $stmt22 = $db->query("SELECT see_med_rpt FROM admin_users_rights WHERE username='$uname'");
    if ($stmt22->rowCount() > 0) {
        $rowx = $stmt22->fetch(PDO::FETCH_ASSOC);
        $view_consultation_notes = $rowx['see_med_rpt'];
    } else {
        $view_consultation_notes = 0;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Database</title>

    <!-- Bootstrap -->
    <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <!-- iCheck -->

    <!-- Datatables -->
    <link href="vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <!-- <link href="build/css/custom.min.css" rel="stylesheet"> -->
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">


            <!-- top navigation -->

            <!-- /top navigation -->

            <!-- page content -->

            <div class="page-title dont-show-in-print" style="background-color:#FFF">
                <div class="title_left">
                    <table width="100%" align="right" style="padding:20px; ">
                        <tr>
                            <td width="80%">
                                <h3>Database Reports</h3>
                            </td>
                            <td><a href="../admin/index.php?datab" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a></td>
                        </tr>
                    </table>


                </div>

            </div>



            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">

                        <div class="x_content">

                            <strong class="dont-show-in-print">Click CSV button to download to excel</strong><br><br><br>


                            <?php

                            if (isset($_POST['general_report_service'])) {
                                // Assuming $db is a valid PDO instance

                                // Prepare the parameters
                                $serv_group = ($_POST['serv_group'] == '' || $_POST['serv_group'] == 'all') ? '' : " and serv_group=:serv_group";
                                $cat_type = ($_POST['cat_type'] == '') ? '' : " and cat_type=:cat_type";
                                $department = ($_POST['department'] == '') ? '' : " and dept_id=:department";

                                $start4 = $_POST['start4'];
                                $end4 = $_POST['end4'];

                                // Prepare the main query
                                $query = "SELECT distinct item_services FROM patient_ap_services WHERE paystatus=1 $cat_type $serv_group $department AND date_entry BETWEEN :start4 AND :end4 ORDER BY date_entry";
                                $stmt = $db->prepare($query);

                                // Bind parameters
                                if ($_POST['serv_group'] != '' && $_POST['serv_group'] != 'all') {
                                    $stmt->bindParam(':serv_group', $_POST['serv_group']);
                                }
                                if ($_POST['cat_type'] != '') {
                                    $stmt->bindParam(':cat_type', $_POST['cat_type']);
                                }
                                if ($_POST['department'] != '') {
                                    $stmt->bindParam(':department', $_POST['department']);
                                }
                                $stmt->bindParam(':start4', $start4);
                                $stmt->bindParam(':end4', $end4);

                                // Execute the query
                                $stmt->execute();

                                if ($stmt->rowCount() > 0) {
                                    echo '<table id="datatable-buttons" class="table table-striped table-bordered">';
                                    echo '<thead>
                            <tr>
                                <th width="3%">#</th>
                                <th width="5%">SERVICE NAME</th>
                                <th width="5%">TOTAL DISPENSED/DELIVERED</th>
                            </tr>
                          </thead>
                          <tbody>';

                                    $n = 1;
                                    while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        /// $Drug_sn = $rwx['sn'];
                                        $item_services = $rwx['item_services'];

                                        // Prepare the second query
                                        $stmt2 = $db->prepare("SELECT SUM(qty) as qty, SUM(pay) as pay, SUM(claim_amt) as claim 
                                                FROM patient_ap_services 
                                                WHERE paystatus=1 $cat_type $serv_group $department 
                                                AND item_services=:item_services 
                                                AND date_entry BETWEEN :start4 AND :end4");
                                        // Bind parameters for the second query
                                        $stmt2->bindParam(':item_services', $item_services);
                                        if ($_POST['cat_type'] != '') {
                                            $stmt2->bindParam(':cat_type', $_POST['cat_type']);
                                        }
                                        if ($_POST['serv_group'] != '' && $_POST['serv_group'] != 'all') {
                                            $stmt2->bindParam(':serv_group', $_POST['serv_group']);
                                        }
                                        if ($_POST['department'] != '') {
                                            $stmt2->bindParam(':department', $_POST['department']);
                                        }
                                        $stmt2->bindParam(':start4', $start4);
                                        $stmt2->bindParam(':end4', $end4);

                                        // Execute the second query
                                        $stmt2->execute();

                                        if ($stmt2->rowCount() > 0) {
                                            $rwxx = $stmt2->fetch(PDO::FETCH_ASSOC);
                                            $pay = $rwxx['pay'];
                                            $claim_amt = $rwxx['claim'];
                                            $qty = $rwxx['qty'];
                                            if ($qty > 0) {
                                                echo "<tr>
                                        <td>" . htmlspecialchars($n) . "</td>
                                        <td>" . htmlspecialchars($item_services) . "</td>
                                        <td>" . htmlspecialchars($qty) . "</td>
                                      </tr>";
                                            }
                                        }
                                        $n++;
                                    }

                                    echo '</tbody></table>';
                                } else {
                                    echo 'No Records Found';
                                }
                            }




                            if (isset($_POST['apply_button4'])) {

                                $start4 = $_POST['start4'];
                                $end4 = $_POST['end4'];

                                if (isset($_POST['product_name_drug']) and $_POST['product_name_drug'] != '') {
                                    echo $product_name_drug = $_POST['product_name_drug'];
                                    $stmt = $db->query("SELECT * FROM stock_table WHERE product_name='$product_name_drug' ORDER BY sn");
                                } elseif (isset($_POST['AllDrugData']) and $_POST['AllDrugData'] != '') {
                                    $stmt = $db->query("SELECT * FROM stock_table WHERE stock_table='Pharmacy' ORDER BY sn");
                                }


                                if ($stmt->rowCount() > 0) {
                            ?>

                                    <table id="datatable-buttons" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="3%">#</th>
                                                <th width="5%">DRUG NAME</th>
                                                <th width="5%">TOTAL DISPENSED</th>
                                            </tr>
                                        </thead>
                                        <tbody>


                                            <?php $n = 1;
                                            while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $Drug_sn = $rwx['sn'];
                                                $Drug_name = $rwx['product_name'];

                                                $stmt2 = $db->query("SELECT sum(qty) as qty, sum(pay) as pay, sum(claim_amt) as claim FROM patient_ap_services WHERE  date(date_entry) between '$start4' and '$end4' and item_services='$Drug_name' and cat_type='Pharmacy' and drug_status=1");
                                                ///echo $stmt2->rowCount();
                                                if ($stmt2->rowCount() > 0) {
                                                    $rwxx = $stmt2->fetch(PDO::FETCH_ASSOC);
                                                    $pay = $rwxx['pay'];
                                                    $claim_amt = $rwxx['claim'];
                                                    $qty = $rwxx['qty'];
                                                    if ($qty > 0) { ?>
                                                        <tr>
                                                            <td><?php echo $n; ?></td>
                                                            <td><?php echo $Drug_name;  ?></td>
                                                            <td><?php echo $qty;  ?></td>
                                                        </tr>
                                            <?php    }
                                                }

                                                $n++;
                                            } ?>

                                        </tbody>
                                    </table>
                                <?php
                                    //////////// NO RECORDS AVAILABLEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
                                } else {
                                    echo 'No Records Found';
                                }
                            }





                            if (isset($_POST['PATIENT_DELETE_INVES'])) {

                                $staff_name = $_POST['staff_name'];

                                if ($staff_name != '') {
                                    $staff_name = " entered_by='$staff_name' and ";
                                }

                                $start2 = $_POST['start_new'];
                                $end2 = $_POST['end_new'];


                                $sql = "SELECT * FROM lab_result_old WHERE $staff_name DATE(result_date) between '$start2' and '$end2'
        GROUP BY result_date";

                                $stmt = $db->prepare($sql);
                                $stmt->execute();
                                if ($stmt->rowCount() > 0) {


                                ?>


                                    <table id="datatable-buttons" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="3%">#</th>
                                                <th width="5%">lab_no #</th>
                                                <th width="5%">Staff name</th>
                                                <th width="5%">test_name</th>
                                                <th width="5%">RESULTS</th>
                                                <th width="7%">date</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <tr>
                                                    <td><?php echo $n; ?></td>
                                                    <td><?php echo $rwx['lab_no']; ?></td>
                                                    <td><?php echo $rwx['entered_by']; ?></td>
                                                    <td><?php echo $rwx['test_name']; ?></td>
                                                    <td><?php echo $rwx['field_value']; ?></td>
                                                    <td><?php echo date("d,M y H:i:s a", strtotime($rwx['result_date'])); ?></td>
                                                    </td>
                                                </tr>
                                            <?php $n++;
                                            } ?>
                                        </tbody>
                                    </table>
                                <?php } else {
                                    echo 'No Records Found.';
                                }
                            }





                            if (isset($_POST['PATIENT_DELETE_NOTES'])) {
                            }


                            if (isset($_POST['viwe_log'])) {

                                $staff_name = $_POST['staff_name'];
                                $patient_id = $_POST['patient_id'];

                                if ($staff_name != '') {
                                    $staff_name = " staff_name='$staff_name' and ";
                                }

                                if ($patient_id != '') {
                                    $patient_id = " patient_id='$patient_id' and ";
                                }

                                $start2 = $_POST['start_new'];
                                $end2 = $_POST['end_new'];


                                $sql = "SELECT * FROM patient_staff_logs WHERE $patient_id $staff_name DATE(date_and_time) between '$start2' and '$end2'
        GROUP BY date_and_time";

                                $stmt = $db->prepare($sql);
                                $stmt->execute();
                                if ($stmt->rowCount() > 0) {


                                ?>


                                    <table id="datatable-buttons" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="3%">#</th>
                                                <th width="5%">Hospital #</th>
                                                <th width="5%">Staff name</th>
                                                <th width="5%">descriptions</th>
                                                <th width="3%">action</th>
                                                <th width="7%">date</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <tr>
                                                    <td><?php echo $n; ?></td>
                                                    <td><?php echo $rwx['patient_id']; ?></td>
                                                    <td><?php echo $rwx['staff_name'];
                                                        ?></td>
                                                    <td><?php echo $rwx['descriptions']; ?></td>
                                                    <td><?php echo $rwx['action'];
                                                        ?></td>

                                                    <td><?php echo date("d,M y H:i:s a", strtotime($rwx['date_and_time'])); ?></td>
                                                    </td>
                                                </tr>
                                            <?php $n++;
                                            } ?>
                                        </tbody>
                                    </table>
                                <?php } else {
                                    echo 'No Records Found.';
                                } ?>
                                <?php






                            }







                            if (isset($_POST['apply_button3'])) {

                                $appt_rpt = $_POST['appt_rpt'];
                                $start2 = $_POST['start2'];
                                $end2 = $_POST['end2'];


                                if ($appt_rpt == 'doctor_contact') {

                                    ///  echo 'doctor_contact';





                                    /*
                  $sql = "SELECT apptm.appt_no, DATE(notes.date_entry) AS note_date 
                  FROM apptm 
                  LEFT JOIN notes ON apptm.appt_no = notes.app_no 
                  GROUP BY apptm.appt_no, DATE(notes.date_entry)";
*/
                                    $sql = "SELECT apptm.appt_no, 
               DATE(notes.date_entry) AS note_date, 
               apptm.patient_name, 
               apptm.appt_no, 
               apptm.services_name, 
               apptm.checkin_by, 
               notes.prepared_by, 
               apptm.hospital_no 
        FROM apptm 
        LEFT JOIN notes ON apptm.appt_no = notes.app_no 
        WHERE notes_type='C' AND DATE(date_entry) between '$start2' and '$end2'
        GROUP BY apptm.appt_no, note_date, apptm.patient_name, apptm.hospital_no";





                                    $stmt = $db->prepare($sql);
                                    $stmt->execute();
                                    if ($stmt->rowCount() > 0) {


                                ?>


                                        <table id="datatable-buttons" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="3%">#</th>
                                                    <th width="5%">Hospital #</th>
                                                    <th width="5%">Appointment #</th>
                                                    <th width="5%">Name</th>
                                                    <th width="3%">Doctor</th>
                                                    <th width="7%">Service Charge</th>
                                                    <th width="7%">Date/Time</th>
                                                    <th width="7%">Booked By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                    <tr>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php echo $rwx['hospital_no']; ?></td>
                                                        <td><?php echo $rwx['appt_no'];
                                                            ?></td>
                                                        <td><?php echo $rwx['patient_name']; ?></td>
                                                        <td><?php echo $rwx['prepared_by'];
                                                            ?></td>
                                                        <td><?php echo $rwx['services_name'];
                                                            ?></td>
                                                        <td><?php echo date("d,M y H:i:s a", strtotime($rwx['note_date'])); ?></td>
                                                        <td><?php echo $rwx['checkin_by'];
                                                            ?></td>
                                                    </tr>
                                                <?php $n++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    <?php } else {
                                        echo 'No Records Found.';
                                    } ?>
                                    <?php

                                } else {








                                    if ($appt_rpt == 'Admitted and Discharge') {
                                        ///// admission table
                                        $stmt = $db->query("SELECT ap.* FROM apptm as ap inner join admission as ad on ap.hospital_no=ad.hospital_no where ap.date_ap between '$start2' and '$end2' and ap.status='discharge' and ap.queue_lock='1' ORDER BY ap.sn");
                                    } else {

                                        //// apptm table
                                        if ($appt_rpt == 'Appointment') {
                                            $search = "status='discharge' and queue_lock='1'";
                                        } else {
                                            $search = "(status='$appt_rpt' or discharge_remarks='$appt_rpt')";
                                        }


                                        $stmt = $db->query("SELECT * FROM apptm where date_ap between '$start2' and '$end2' and $search  ORDER BY sn");
                                    }
                                    if ($stmt->rowCount() > 0) {
                                    ?>

                                        <table id="datatable-buttons" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="3%">#</th>
                                                    <th width="5%">Hospital #</th>
                                                    <th width="5%">Name</th>
                                                    <th width="3%">Doctor</th>
                                                    <th width="7%">Service Charge</th>
                                                    <th width="7%">Date/Time</th>
                                                    <th width="7%">Booked By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $n = 1;
                                                while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                    <tr>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php echo $rwx['hospital_no']; ?></td>
                                                        <td><?php echo $rwx['patient_name']; ?></td>
                                                        <td><?php echo $rwx['app_by']; ?></td>
                                                        <td><?php echo $rwx['services_name']; ?></td>
                                                        <td><?php echo date("d,M y H:i:s a", strtotime($rwx['ap_date_time'])); ?></td>
                                                        <td><?php echo $rwx['checkin_by']; ?></td>
                                                    </tr>
                                                <?php $n++;
                                                } ?>
                                            </tbody>
                                        </table>

                                    <?php } else { ?>

                                        <div class="alert alert-danger">No Records Found</div>
                            <?php }
                                }
                            }

                            ?>

                            <?php
                            if (isset($_POST['apply_button_fam'])) {
                                $appt_rpt = $_POST['appt_rpt_fam'];
                                $start2 = $_POST['start_fam'];
                                $end2 = $_POST['end_fam'];
                                $rpt_scope_fam = $_POST['rpt_scope_fam'];
                                $insurance_fam = $_POST['insurance_fam'];

                                // Fetch hospital details from the database
                                $hospital_details_query = 'SELECT * FROM hospital_details LIMIT 1';
                                $hospital_details_stmt = $db->prepare($hospital_details_query);
                                $hospital_details_stmt->execute();
                                $hospital_details = $hospital_details_stmt->fetch(PDO::FETCH_ASSOC);

                                // Get family name if insurance is selected
                                $familyName = '';
                                if (!empty($insurance_fam)) {
                                    $familyStmt = $db->prepare("SELECT insurance_name FROM insurance_tbl WHERE insurance_no = :insurance_no");
                                    $familyStmt->execute([':insurance_no' => $insurance_fam]);
                                    $familyData = $familyStmt->fetch(PDO::FETCH_ASSOC);
                                    $familyName = $familyData['insurance_name'];
                                }

                                if ($rpt_scope_fam == 'Summary') {
                                    $sql = "SELECT SUM(patient_ap_services.hosp_price) AS price, 
                                        patient_ap_services.hospital_no, patient_ap_services.date_entry, enrollee.surname, enrollee.fname, enrollee.oname, 
                                        insurance_tbl.insurance_name
                                        FROM patient_ap_services
                                        INNER JOIN enrollee ON enrollee.hospital_no = patient_ap_services.hospital_no
                                        LEFT JOIN insurance_tbl ON insurance_tbl.insurance_no = enrollee.hmo_no
                                        WHERE insurance_tbl.insurance_type = 'Family'
                                        AND patient_ap_services.date_entry BETWEEN :start AND :end ";

                                    if (!empty($insurance_fam)) {
                                        $sql .= " AND insurance_tbl.insurance_no = :insurance_no";
                                    }

                                    if ($appt_rpt == 'Invoice') {
                                        $sql .= " AND (patient_ap_services.paystatus = 0 or patient_ap_services.wallet_debt_bill_to_acct = 'CREDIT')";
                                    } elseif ($appt_rpt == 'Paid') {
                                        $sql .= " AND patient_ap_services.paystatus = 1 AND patient_ap_services.cr = 1";
                                    } else if ($appt_rpt == 'UnPaid') {
                                        $sql .= " AND patient_ap_services.paystatus = 0 AND patient_ap_services.cr = 1";
                                    } else {
                                        $sql .= " AND (patient_ap_services.paystatus = 0 OR patient_ap_services.paystatus = 1) AND patient_ap_services.cr = 1";
                                    }

                                    $sql .= " GROUP BY patient_ap_services.hospital_no";
                                } elseif ($rpt_scope_fam == 'Detailed') {
                                    $sql = "SELECT patient_ap_services.hosp_price AS price, patient_ap_services.item_services AS bill_desc,
                                        patient_ap_services.hospital_no, patient_ap_services.date_entry, enrollee.surname, enrollee.fname, enrollee.oname, 
                                        insurance_tbl.insurance_name, patient_ap_services.paystatus
                                        FROM patient_ap_services
                                        INNER JOIN enrollee ON enrollee.hospital_no = patient_ap_services.hospital_no
                                        LEFT JOIN insurance_tbl ON insurance_tbl.insurance_no = enrollee.hmo_no
                                        WHERE insurance_tbl.insurance_type = 'Family'
                                        AND patient_ap_services.date_entry BETWEEN :start AND :end ";

                                    if (!empty($insurance_fam)) {
                                        $sql .= " AND insurance_tbl.insurance_no = :insurance_no";
                                    }
                                    if ($appt_rpt == 'Invoice') {
                                        $sql .= " AND (patient_ap_services.paystatus = 0 or patient_ap_services.wallet_debt_bill_to_acct = 'CREDIT')";
                                    } elseif ($appt_rpt == 'Paid') {
                                        $sql .= " AND patient_ap_services.paystatus = 1 AND patient_ap_services.cr = 1";
                                    } else if ($appt_rpt == 'UnPaid') {
                                        $sql .= " AND patient_ap_services.paystatus = 0 AND patient_ap_services.cr = 1";
                                    } else {
                                        $sql .= " AND (patient_ap_services.paystatus = 0 OR patient_ap_services.paystatus = 1) AND patient_ap_services.cr = 1";
                                    }

                                    $sql .= " ORDER BY patient_ap_services.hospital_no";
                                }

                                // Prepare and execute the query with the optional insurance parameter
                                $stmt = $db->prepare($sql);
                                $params = [':start' => $start2, ':end' => $end2];
                                if (!empty($insurance_fam)) {
                                    $params[':insurance_no'] = $insurance_fam;
                                }
                                $stmt->execute($params);

                                // Initialize totals
                                $totalPaid = $totalUnpaid = $grandTotal = 0;
                            ?>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        margin: 20px;
                                    }

                                    h4,
                                    h5 {
                                        color: #007bff;
                                        margin-top: 20px;
                                    }

                                    .table thead th {
                                        background-color: #f8f9fa;
                                        font-weight: bold;
                                    }

                                    .table th {
                                        text-align: center;
                                        vertical-align: middle;
                                    }

                                    .table-striped>tbody>tr:nth-of-type(odd) {
                                        background-color: #f9f9f9;
                                    }

                                    .print-section {
                                        margin-bottom: 30px;
                                        border: 1px solid #ddd;
                                        padding: 20px;
                                        border-radius: 5px;
                                    }

                                    .print-buttons {
                                        text-align: right;
                                        margin-bottom: 10px;
                                    }

                                    @media print {
                                        .print-buttons {
                                            display: none;
                                        }

                                        .dont-show-in-print {
                                            display: none;
                                        }
                                    }
                                </style>
                                <script>
                                    function printAll() {
                                        window.print();
                                    }
                                </script>

                                <!-- Hospital Header -->
                                <?php
                                echo '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">';
                                echo '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
                                echo '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
                                echo '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
                                echo '</table><br>';
                                ?>

                                <!-- Print All Button -->
                                <div class="text-right">
                                    <button class="btn btn-primary dont-show-in-print" onclick="printAll()">Print All</button>
                                </div>

                                <h4>Report Of Services delivered on credit to patients with Family Insurance</h4>
                                <?php if (!empty($familyName)): ?>
                                    <h3 style="font-weight: bold;">Name: <?php echo htmlspecialchars($familyName); ?></h3>
                                <?php endif; ?>
                                <p>Period [<?php echo date('Y-m-d', strtotime($start2)) ?> - <?php echo date('Y-m-d', strtotime($end2)) ?>]</p>
                                <table id="datatable-buttons" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="3%">#</th>
                                            <th width="5%">Hospital #</th>
                                            <th width="5%">Insurance</th>
                                            <th width="5%">Name</th>
                                            <th width="3%">Bill</th>
                                            <th width="3%">Date</th>
                                            <th width="3%">Bill Desc</th>
                                            <th width="3%">Pay Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $n = 1;
                                        $currentHospitalNo = null;
                                        $subtotal = 0;

                                        while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            // Check if we're moving to a new patient
                                            if ($currentHospitalNo !== null && $currentHospitalNo !== $rwx['hospital_no']) {
                                                // Display subtotal for the last patient
                                                echo "<tr><td colspan='4'><strong>Subtotal for Patient #{$currentHospitalNo}</strong></td><td colspan='3'><strong>" . number_format($subtotal, 2) . "</strong></td></tr>";
                                                $subtotal = 0; // Reset subtotal for new patient
                                            }

                                            $currentHospitalNo = $rwx['hospital_no'];
                                            $price = $rwx['price'];
                                            $subtotal += $price; // Add to subtotal for this patient
                                            $grandTotal += $price; // Add to grand total

                                            if ($rwx['paystatus'] == 1) {
                                                $totalPaid += $price;
                                                $payStatus = 'Paid';
                                            } else {
                                                $totalUnpaid += $price;
                                                $payStatus = 'Unpaid';
                                            }

                                            // Display patient bill details
                                            echo "<tr>
                                                <td>{$n}</td>
                                                <td>{$rwx['hospital_no']}</td>
                                                <td>{$rwx['insurance_name']}</td>
                                                <td>{$rwx['surname']} {$rwx['fname']} {$rwx['oname']}</td>
                                                <td>" . number_format($price, 2) . "</td>
                                                <td>{$rwx['date_entry']}</td>
                                                <td>{$rwx['bill_desc']}</td>
                                                <td>{$payStatus}</td>
                                            </tr>";

                                            $n++;
                                        }

                                        // Final subtotal for the last patient in the loop
                                        if ($currentHospitalNo !== null) {
                                            echo "<tr><td colspan='4'><strong>Subtotal for Patient #{$currentHospitalNo}</strong></td><td colspan='3'><strong>" . number_format($subtotal, 2) . "</strong></td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4"><strong>Total Paid</strong></td>
                                            <td colspan="4"><?php echo number_format($totalPaid, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"><strong>Total Unpaid</strong></td>
                                            <td colspan="4"><?php echo number_format($totalUnpaid, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"><strong>Grand Total</strong></td>
                                            <td colspan="4"><?php echo number_format($grandTotal, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php } ?>

                            <?php
                            if (isset($_POST['generate_report_appt_stats'])) {
                                $startDate = $_POST['start_date'];
                                $endDate = $_POST['end_date'];

                                // Fetch hospital details from the database
                                $hospital_details_query = 'SELECT * FROM hospital_details LIMIT 1';
                                $hospital_details_stmt = $db->prepare($hospital_details_query);
                                $hospital_details_stmt->execute();
                                $hospital_details = $hospital_details_stmt->fetch(PDO::FETCH_ASSOC);

                                // Fetch totals for A&E/Observation and Ward Admissions
                                $stmt = $db->prepare("SELECT COUNT(*) AS ae_observations FROM admission WHERE room_bed IS NULL AND date_admit BETWEEN :start AND :end");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $aeObservations = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT COUNT(*) AS ward_admissions FROM admission WHERE room_bed IS NOT NULL AND date_admit BETWEEN :start AND :end");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $wardAdmissions = $stmt->fetchColumn();

                                // SQL Queries for fetching statistics remain the same
                                $stmt = $db->prepare("SELECT COUNT(DISTINCT appt_no) AS total_appointments FROM apptm WHERE date_ap BETWEEN :start AND :end");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $totalAppointments = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT services_name, COUNT(*) AS count FROM apptm WHERE date_ap BETWEEN :start AND :end GROUP BY services_name");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $appointmentsBySpecialist = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $stmt = $db->prepare("SELECT COUNT(DISTINCT sn) AS total_admissions FROM admission WHERE date_admit BETWEEN :start AND :end");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $totalAdmissions = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT discharge_status, COUNT(*) AS count FROM admission WHERE date_discharge BETWEEN :start AND :end GROUP BY discharge_status");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $dischargeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $stmt = $db->prepare("SELECT sn FROM admission WHERE date_discharge BETWEEN :start AND :end AND discharge_status = 'Death'");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $mortalityStats = $stmt->rowCount(PDO::FETCH_ASSOC);

                                $stmt = $db->prepare("SELECT sn AS count FROM admission WHERE date_discharge BETWEEN :start AND :end ");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $dischargesCount = $stmt->rowCount();

                                $stmt = $db->prepare("SELECT COUNT(DISTINCT item_services) AS total_services FROM patient_ap_services WHERE date_entry BETWEEN :start AND :end");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $serviceUtilization = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT COUNT(DISTINCT insurance_type) AS insurance_types FROM apptm WHERE date_ap BETWEEN :start AND :end");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $insuranceStats = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT AVG(DATEDIFF(date_discharge, date_admit)) AS avg_length_of_stay FROM admission WHERE date_admit BETWEEN :start AND :end AND date_discharge IS NOT NULL AND room_bed IS NOT NULL");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $averageLengthOfStay = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT AVG(DATEDIFF(date_discharge, date_admit)) AS avg_length_of_stay FROM admission WHERE date_admit BETWEEN :start AND :end AND date_discharge IS NOT NULL AND room_bed IS NULL");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $averageLengthOfStayObservation = $stmt->fetchColumn();

                                $stmt = $db->prepare("SELECT status, COUNT(*) AS count FROM apptm WHERE date_ap BETWEEN :start AND :end GROUP BY status");
                                $stmt->execute(['start' => $startDate, 'end' => $endDate]);
                                $cancellationStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Fetch data from notes_services table, grouped by service
                                $query = "
                                    SELECT 
                                        service as service_name, 
                                        SUM(isCompleted = 1) AS complete,
                                        SUM(isCompleted = 0) AS incomplete,
                                        COUNT(*) AS total
                                    FROM 
                                        notes_services
                                    GROUP BY 
                                        service
                                    ORDER BY 
                                        service ASC
                                    ";

                                $result = $db->query($query);
                                $serviceStats = [];

                                if ($result->rowCount() > 0) {
                                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                        $serviceStats[] = $row;
                                    }
                                }
                            ?>

                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        margin: 20px;
                                    }

                                    h4,
                                    h5 {
                                        color: #007bff;
                                        margin-top: 20px;
                                    }

                                    .table thead th {
                                        background-color: #f8f9fa;
                                        font-weight: bold;
                                    }

                                    .table th {
                                        text-align: center;
                                        vertical-align: middle;
                                    }

                                    .table-striped>tbody>tr:nth-of-type(odd) {
                                        background-color: #f9f9f9;
                                    }

                                    .print-section {
                                        margin-bottom: 30px;
                                        border: 1px solid #ddd;
                                        padding: 20px;
                                        border-radius: 5px;
                                    }

                                    .print-buttons {
                                        text-align: right;
                                        margin-bottom: 10px;
                                    }

                                    @media print {
                                        .print-buttons {
                                            display: none;
                                        }

                                        .dont-show-in-print {
                                            display: none;
                                        }
                                    }
                                </style>
                                <script>
                                    function printSection(sectionId) {
                                        var sectionContent = document.getElementById(sectionId).innerHTML;
                                        var originalContent = document.body.innerHTML;
                                        document.body.innerHTML = sectionContent;
                                        window.print();
                                        document.body.innerHTML = originalContent;
                                        location.reload();
                                    }

                                    function printAll() {
                                        window.print();
                                    }
                                </script>

                                <!-- Hospital Header -->
                                <?php
                                echo '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">';
                                echo '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
                                echo '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
                                echo '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
                                echo '</table><br>';
                                ?>

                                <!-- Print All Button -->
                                <div class="text-right">
                                    <button class="btn btn-primary dont-show-in-print" onclick="printAll()">Print All</button>
                                </div>

                                <h4 class="text-center">Appointment Statistics Report</h4>
                                <p class="text-center">Period: <?php echo date('Y-m-d', strtotime($startDate)) ?> to <?php echo date('Y-m-d', strtotime($endDate)) ?></p>

                                <!-- Appointment Statistics Section -->
                                <div class="print-section" id="appointment-stats">
                                    <div class="print-buttons">
                                        <button class="btn btn-secondary" onclick="printSection('appointment-stats')">Print This Section</button>
                                    </div>
                                    <h4 class="text-center">Appointment Summary</h4>
                                    <p class="text-center">Period: <?php echo date('Y-m-d', strtotime($startDate)) ?> to <?php echo date('Y-m-d', strtotime($endDate)) ?></p>

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Total Appointments</th>
                                                <th>Total Admissions</th>
                                                <th>Unique Service Utilization</th>
                                                <th>Insurance Types Used</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $totalAppointments; ?></td>
                                                <td><?php echo $totalAdmissions; ?></td>
                                                <td><?php echo $serviceUtilization; ?></td>
                                                <td><?php echo $insuranceStats; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="print-section" id="admission-stats">
                                    <div class="print-buttons">
                                        <button class="btn btn-secondary" onclick="printSection('admission-stats')">Print This Section</button>
                                    </div>
                                    <h4 class="text-center">Admission Statistics Report</h4>
                                    <p class="text-center">Period: <?php echo date('Y-m-d', strtotime($startDate)); ?> to <?php echo date('Y-m-d', strtotime($endDate)); ?></p>

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ward Admissions</th>
                                                <th>A&E / Observations</th>
                                                <th>Total Admissions</th>
                                                <th>Average Length of Stay In Ward (days)</th>
                                                <th>Average Length of Stay In Observation (days)</th>
                                                <th>Discharges</th>
                                                <th>Mortality Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $wardAdmissions; ?></td>
                                                <td><?php echo $aeObservations; ?></td>
                                                <td><?php echo $totalAdmissions; ?></td>
                                                <td><?php echo round($averageLengthOfStay, 2); ?></td>
                                                <td><?php echo round($averageLengthOfStayObservation, 2); ?></td>
                                                <td><?php echo $dischargesCount; ?></td>
                                                <td><?php echo  round(($mortalityStats / $dischargesCount) * 100, 4); ?>% (<?php echo $mortalityStats ?> Deaths)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Appointments by Specialist Type Section -->
                                <div class="print-section" id="appointments-specialist">
                                    <div class="print-buttons">
                                        <button class="btn btn-secondary" onclick="printSection('appointments-specialist')">Print This Section</button>
                                    </div>
                                    <h5>Appointments by Specialist Type</h5>
                                    <p class="text-center">Period: <?php echo date('Y-m-d', strtotime($startDate)) ?> to <?php echo date('Y-m-d', strtotime($endDate)) ?></p>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Specialist</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appointmentsBySpecialist as $specialist) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($specialist['services_name']); ?></td>
                                                    <td><?php echo $specialist['count']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="print-section" id="medical-services">
                                    <div class="print-buttons">
                                        <button class="btn btn-secondary" onclick="printSection('medical-services')">Print This Section</button>
                                    </div>
                                    <h5 class="text-center">Medical Services and Procedures</h5>
                                    <small class="text-danger"><i>*Some services might not have a dedcated 'is complete' marker.</i></small>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Completed</th>
                                                <th>Incomplete</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($serviceStats as $service): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                                                    <td><?php echo $service['complete']; ?></td>
                                                    <td><?php echo $service['incomplete']; ?></td>
                                                    <td><?php echo $service['total']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Discharge Statistics Section -->
                                <div class="print-section" id="discharge-stats">
                                    <div class="print-buttons">
                                        <button class="btn btn-secondary" onclick="printSection('discharge-stats')">Print This Section</button>
                                    </div>
                                    <h5>Discharge Statistics</h5>
                                    <p class="text-center">Period: <?php echo date('Y-m-d', strtotime($startDate)) ?> to <?php echo date('Y-m-d', strtotime($endDate)) ?></p>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Discharge Reason</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dischargeStats as $discharge) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($discharge['discharge_status']); ?></td>
                                                    <td><?php echo $discharge['count']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Cancellation Statistics Section -->
                                <div class="print-section" id="cancellation-stats">
                                    <div class="print-buttons">
                                        <button class="btn btn-secondary" onclick="printSection('cancellation-stats')">Print This Section</button>
                                    </div>
                                    <h5>Appointment Conclusion Statistics</h5>
                                    <p class="text-center">Period: <?php echo date('Y-m-d', strtotime($startDate)) ?> to <?php echo date('Y-m-d', strtotime($endDate)) ?></p>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cancellationStats as $status) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($status['status']); ?></td>
                                                    <td><?php echo $status['count']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>


                            <?php } ?>


                            <?php








                            if (isset($_POST['apply_button2'])) {

                                if (isset($_POST['med_rpt'])) {
                                    $med_rpt = $_POST['med_rpt'];
                                }

                                if (isset($_POST['diagnosis']) and $_POST['diagnosis'] != '') {
                                    $search_compl = $_POST['diagnosis'];
                                    /// notes table

                                } elseif (isset($_POST['keyword']) and $_POST['keyword'] != '') {
                                    $search_compl = $_POST['keyword'];
                                }

                                $start3 = $_POST['start3'];
                                $end3 = $_POST['end3'];

                                /////        						med_rpt /Complaints /Diagnosis
                                ///             diagnosis  ////  keyword        keyword
                                if ($med_rpt == 'C') { ?>
                                    <table id="datatable-buttons" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Hospital #</th>
                                                <th>Name</th>
                                                <th>Complaints</th>
                                                <th>Doctor</th>
                                                <th>Date/Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php


                                            $stmt = $db->query("SELECT med.*,e.surname,e.fname FROM enrollee as e 
	inner join notes as med on e.hospital_no=med.hospital_no 
	where e.vip=0 and date(med.date_entry) between '$start3' and '$end3' and med.notes like '%$search_compl%'  ORDER BY med.sn");
                                            if ($stmt->rowCount() > 0) { ?>

                                                <?php
                                                $n = 1;
                                                while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['hospital_no'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['surname'] . ', ' . $rwx['fname'];
                                                            } ?></td>
                                                        <td><?php
                                                            if ($view_consultation_notes == 1) {
                                                                if (isset($_POST['show_notes']) and $_POST['show_notes'] == 'show_notes') {
                                                                    echo $rwx['notes'];
                                                                }
                                                            }
                                                            ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['prepared_by'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo date("d,M y H:i:s a", strtotime($rwx['date_entry']));
                                                            } ?></td>
                                                    </tr>

                                                <?php
                                                    $n++;
                                                }
                                            } else {

                                                echo 'no record';
                                            }

                                            /////================================================================

                                            $stmt = $db->query("SELECT med.*,e.surname,e.fname FROM enrollee as e 
                      inner join notes as med on e.hospital_no=med.hospital_no where e.vip =0 and  date(med.date_entry) between '$start3' and '$end3' and med.notes like '%$search_compl%' and med.notes_type='C'  ORDER BY sn");
                                            if ($stmt->rowCount() > 0) {

                                                while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                                                    <tr>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['hospital_no'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['surname'] . ', ' . $rwx['fname'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['notes'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['prepared_by'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo date("d,M y H:i:s a", strtotime($rwx['date_entry']));
                                                            } ?></td>
                                                    </tr>

                                            <?php
                                                    $n++;
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>

                                <?php

                                } else {
                                    /// DIAGNOSIS	
                                ?>

                                    <table id="datatable-buttons" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Hospital #</th>
                                                <th>Name</th>
                                                <th>Diagnosis</th>
                                                <th>Doctor</th>
                                                <th>Date/Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            $stmt = $db->query("SELECT med.*,e.surname,e.fname FROM enrollee as e 
                      inner join notes as med on e.hospital_no=med.hospital_no where e.vip=0 and date(med.date_entry) between '$start3' and '$end3' and med.notes like '%$search_compl%' and med.notes_type='D'  ORDER BY sn");
                                            if ($stmt->rowCount() > 0) { ?>

                                                <?php
                                                $n = 1;
                                                while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $n; ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['hospital_no'];
                                                            } ?></td>
                                                        <td><?php if ($view_consultation_notes == 1) {
                                                                echo $rwx['surname'] . ', ' . $rwx['fname'];
                                                            } ?></td>
                                                        <td><?php
                                                            echo $rwx['notes'];
                                                            ?></td>
                                                        <td><?php echo $rwx['prepared_by']; ?></td>
                                                        <td><?php echo date("d,M y H:i:s a", strtotime($rwx['date_entry'])); ?></td>
                                                    </tr>

                                            <?php
                                                    $n++;
                                                }
                                            }
                                        }
                                        //// end diagnosis ============================================

                                    }

                                    if (isset($_POST['apply_button'])) {

                                        if (isset($_POST['AllPatientsData'])) {
                                            $stmt = $db->query("SELECT * FROM enrollee ORDER BY sn");
                                            $query_type = 'normal';
                                        } elseif (isset($_POST['genderr']) and $_POST['genderr'] != '') {
                                            $genderr = $_POST['genderr'];
                                            $stmt = $db->query("SELECT * FROM enrollee where gender='$genderr' ORDER BY sn");
                                            $query_type = 'normal';
                                        } elseif (isset($_POST['state_lga']) and $_POST['state_lga'] != '') {
                                            $state_lga = $_POST['state_lga'];
                                            $stmt = $db->query("SELECT * FROM enrollee where state_lga='%$state_lga%' ORDER BY sn");
                                            $query_type = 'normal';
                                        } elseif (isset($_POST['enroll_start']) and isset($_POST['enroll_end']) and $_POST['enroll_start'] != '' and $_POST['enroll_end'] != '') {
                                            $enroll_start = $_POST['enroll_start'];
                                            $enroll_end = $_POST['enroll_end'];
                                            $stmt = $db->query("SELECT * FROM enrollee where date(date_capture) between '$enroll_start' and '$enroll_end' ORDER BY sn");
                                            $query_type = 'normal';
                                        } elseif (isset($_POST['age_range']) and $_POST['age_range'] != "") {
                                            $query_type = 'CYAS';
                                            $age_range = $_POST['age_range'];
                                            $stmt = $db->query("SELECT * FROM enrollee ORDER BY sn");
                                        } else {
                                            header("location:../admin/index.php?datab&ERROR");
                                        }
                                        if ($stmt->rowCount() > 0) {
                                            ?>

                                            <table id="datatable-buttons" class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>NHIS #</th>
                                                        <th>NHIS EX</th>
                                                        <th>HOSPITAL NO</th>
                                                        <th>HMO NO.</th>
                                                        <th>INSURANCE</th>
                                                        <th>MEMBERSHIP</th>
                                                        <th>SURNAME</th>
                                                        <th>FIRSTNAME</th>
                                                        <th>OTHERS</th>
                                                        <th>OCCUPATION</th>
                                                        <th>GENDER</th>
                                                        <th>MARITAL/STATUS</th>
                                                        <th>BLOOD/GROUP</th>
                                                        <th>GENO TYPE</th>
                                                        <th>DOB</th>
                                                        <th>AGE</th>
                                                        <th>PHONE</th>
                                                        <th>EMAIL</th>
                                                        <th>STATE/LGA</th>
                                                        <th>TRIBE</th>
                                                        <th>NATIONALITY</th>
                                                        <th>ADDRESS</th>
                                                        <th>RELIGION</th>
                                                        <th>DATE CAPTURED</th>
                                                    </tr>
                                                </thead>
                                                <tbody>


                                                    <?php
                                                    $n = 1;
                                                    while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                                        if ($query_type == 'CYAS') {
                                                            $dob = date("Y-m-d", strtotime($rwx['dob']));

                                                            date_default_timezone_set('Africa/Lagos');
                                                            $Current_date = date('Y-m-d');
                                                            $date1 = new DateTime($Current_date);
                                                            $date2 = new DateTime($dob);
                                                            $diff = $date2->diff($date1);
                                                            $age = $diff->format('%y');
                                                            if ($age_range == 'C') {
                                                                $age1 = 0;
                                                                $age2 = 14;
                                                            }
                                                            if ($age_range == 'Y') {
                                                                $age1 = 15;
                                                                $age2 = 24;
                                                            }
                                                            if ($age_range == 'A') {
                                                                $age1 = 25;
                                                                $age2 = 64;
                                                            }
                                                            if ($age_range == 'S') {
                                                                $age1 = 65;
                                                                $age2 = 300;
                                                            }
                                                        }

                                                        if (($query_type == 'CYAS' and $age >= $age1 and $age <= $age2) or $query_type == 'normal') { ?>

                                                            <tr>
                                                                <td><?php echo $n; ?></td>
                                                                <td><?php echo $rwx['nhis_no']; ?></td>
                                                                <td><?php echo $rwx['nhis_no_ext']; ?></td>
                                                                <td><?php echo $rwx['hospital_no']; ?></td>
                                                                <td><?php echo $rwx['hmo_no']; ?></td>
                                                                <td><?php echo $rwx['insurance']; ?></td>
                                                                <td><?php echo $rwx['member']; ?></td>
                                                                <td><?php echo $rwx['surname']; ?></td>
                                                                <td><?php echo $rwx['fname']; ?></td>
                                                                <td><?php echo $rwx['oname']; ?></td>
                                                                <td><?php echo $rwx['occupation']; ?></td>
                                                                <td><?php echo $rwx['gender']; ?></td>
                                                                <td><?php echo $rwx['marital_status']; ?></td>
                                                                <td><?php echo $rwx['blood_g']; ?></td>
                                                                <td><?php echo $rwx['geno_type']; ?></td>
                                                                <td><?php echo $rwx['dob']; ?></td>
                                                                <td><?php echo $rwx['age']; ?></td>
                                                                <td><?php echo $rwx['phone']; ?></td>
                                                                <td><?php echo $rwx['email']; ?></td>
                                                                <td><?php echo $rwx['state_lga']; ?></td>
                                                                <td><?php echo $rwx['tribe']; ?></td>
                                                                <td><?php echo $rwx['nationality']; ?></td>
                                                                <td><?php echo $rwx['addr']; ?></td>
                                                                <td><?php echo $rwx['religion']; ?></td>
                                                                <td><?php echo date("d,M y", strtotime($rwx['date_capture'])); ?></td>
                                                            </tr>
                                                        <?php } ?>

                                                    <?php $n++;
                                                    } ?>
                                                </tbody>
                                            </table>
                                        <?php } else { ?>

                                            <div class="alert alert-danger">No Records Found</div>
                                    <?php }
                                    } ?>
                                    <?php
                                    if (isset($_POST['version_2'])) {

                                        $patient_way = $_POST['patient_way'];
                                        $department = $_POST['department'];
                                        $room_bed = $_POST['room_bed'];
                                        $start2 = $_POST['start_new'];
                                        $end2 = $_POST['end_new'];

                                        if ($department == 'all') {
                                            $tag = '';
                                        } else {
                                            if ($patient_way == "OUT-PATIENT") {
                                                $tag = " and dept='$department'";
                                            } else {
                                                $tag = " and dept_id='$department'";
                                            }
                                        }

                                        if ($patient_way == "OUT-PATIENT") {
                                            $stmt = $db->query("SELECT a.*,e.nhis_no,e.phone FROM apptm a 
                      INNER JOIN enrollee AS e ON e.hospital_no = a.hospital_no
                       where doctor_id >0  $tag AND e.vip =0 and date_ap between '$start2' and '$end2' ORDER BY sn");
                                        } else {

                                            if ($patient_way == "DEATH") {
                                                $DEATH = " and discharge_status ='death'";
                                            } else {
                                                $DEATH = '';
                                            }

                                            if ($room_bed != "") {
                                                $room_bed = " and room_bed ='$room_bed'";
                                            } else {
                                                $room_bed = '';
                                            }

                                            $stmt = $db->query("SELECT ad.*,e.nhis_no,e.surname,e.fname,e.oname,e.phone FROM admission ad 
                      INNER JOIN enrollee AS e ON e.hospital_no = ad.hospital_no
                       where e.vip=0 and  doc_incharge !='' $tag $room_bed $DEATH AND DATE(date_admit) between '$start2' and '$end2' ORDER BY sn");
                                        }

                                        if ($stmt->rowCount() > 0) {  ?>

                                            <?php

                                            if ($department != 'all') {
                                                $sql = "SELECT department FROM department WHERE sn = :dept LIMIT 1";
                                                $diagnosis = $db->prepare($sql);
                                                $diagnosis->bindParam(':dept', $department);
                                                $diagnosis->execute();
                                                $result = $diagnosis->fetchColumn();
                                                echo '<h2> DEPARTMENT : ' .   $result . '</h2>'; // This will output the single item_services value
                                            }
                                            ?>


                                            <table id="datatable-buttons" class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Hospital #</th>
                                                        <th>Name</th>
                                                        <th>age</th>
                                                        <th>Phone</th>
                                                        <th>NHIS #</th>
                                                        <th>VISIT #</th>
                                                        <th>VISIT TYPE</th>
                                                        <th>DEPARTMENT</th>
                                                        <th>DIAGNOSIS</th>
                                                        <th>Complaints</th>
                                                        <th>PLAN</th>
                                                        <th>LAB Investiagtion</th>
                                                        <th>Imaging</th>
                                                        <th>Drugs</th>
                                                        <th>Medical Services</th>
                                                        <th>Doctor</th>
                                                        <th>Appointment Date/Time</th>
                                                        <th>Booked By</th>
                                                        <th>Appointment Status</th>
                                                        <th>Appointment Expiring Date</th>
                                                        <th>Duration</th>

                                                        <?php if ($patient_way == "IN-PATIENT") { ?>
                                                            <th>Adm. Doctor Incharge</th>
                                                            <th>Adm. room_bed</th>
                                                            <th>Adm. floor</th>
                                                            <th>Adm. dept</th>
                                                            <th>reason_adm</th>
                                                            <th>date_admit</th>
                                                            <th>date_discharge</th>
                                                            <th>Days on Admission</th>
                                                            <th>discharge_status</th>
                                                            <th>discharge_note</th>
                                                            <th>Adm. Doctor discharge_name</th>
                                                            <th>discharge_by_nurse</th>
                                                            <th>care_giver</th>
                                                            <th>care_giver_phone</th>
                                                            <th>relationship</th>
                                                        <?php } ?>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $n = 1;
                                                    while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                                        $appt_no = $rwx['appt_no'];

                                                        if ($patient_way == "OUT-PATIENT") {
                                                            $services_name = $rwx['services_name'];
                                                            $dept = $rwx['dept'];
                                                            $doctor_id = $rwx['doctor_id'];
                                                            $ap_date_time = $rwx['ap_date_time'];
                                                            $ap_date_time = date("d,M y H:i:s a", strtotime($ap_date_time));
                                                            $checkin_by = $rwx['checkin_by'];
                                                            $status = $rwx['status'];
                                                            $app_expiration_date = date("d,M y H:i:s a", strtotime($rwx['app_expiration_date']));
                                                            $app_duration = $rwx['app_duration'];
                                                        }

                                                        if ($patient_way == "IN-PATIENT") {

                                                            $stmt_APP = $db->query("SELECT appt_no, services_name, dept, ap_date_time, checkin_by, doctor_id FROM apptm WHERE appt_no='$appt_no'");
                                                            $rwx2 = $stmt_APP->fetch(PDO::FETCH_ASSOC);
                                                            if ($rwx2) {
                                                                // Assign the values to variables
                                                                $appt_no = $rwx2['appt_no'];
                                                                $services_name = $rwx2['services_name'];
                                                                $dept = $rwx2['dept'];
                                                                $doctor_id = $rwx2['doctor_id'];
                                                                $ap_date_time = $rwx2['ap_date_time'];
                                                                $ap_date_time = date("d,M y H:i:s a", strtotime($ap_date_time));
                                                                $checkin_by = $rwx2['checkin_by'];
                                                            } else {
                                                                $appt_no = '';
                                                                $services_name = '';
                                                                $dept =  '';
                                                                $doctor_id = '';
                                                                $ap_date_time = '';
                                                                $checkin_by = '';
                                                                $status = '';
                                                                $app_expiration_date = '';
                                                                $app_duration = '';
                                                            }
                                                        }

                                                    ?>
                                                        <tr>
                                                            <td><?php echo $n; ?></td>
                                                            <td><?php if ($view_consultation_notes == 1) {
                                                                    echo $hospital_no = $rwx['hospital_no'];
                                                                } ?></td>
                                                            <td><?php if ($view_consultation_notes == 1) {
                                                                    echo $rwx['surname'] . ' ' . $rwx['fname'] . ' ' . $rwx['oname'];
                                                                } ?></td>
                                                            <td><?php echo $rwx['age']; ?></td>
                                                            <td><?php if ($view_consultation_notes == 1) {
                                                                    echo $rwx['phone'];
                                                                } ?></td>
                                                            <td><?php if ($view_consultation_notes == 1) {
                                                                    echo $rwx['nhis_no'];
                                                                } ?></td>
                                                            <td><?php if ($view_consultation_notes == 1) {
                                                                    echo $appt_no;
                                                                } ?></td>
                                                            <td><?php echo $services_name; ?></td>
                                                            <td>
                                                                <?php


                                                                $sql = "SELECT department FROM department WHERE sn = :dept LIMIT 1";
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->bindParam(':dept', $dept);
                                                                $diagnosis->execute();
                                                                $result = $diagnosis->fetchColumn();
                                                                echo $result; // This will output the single item_services value

                                                                ?>
                                                            </td>
                                                            <td><?php
                                                                $sql = "SELECT distinct diagnosis FROM view_diagnosis_tracking where hospital_no ='$hospital_no' AND DATE(created_at) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->execute();
                                                                $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                $comma_separated_data = implode(', ', $results);
                                                                echo $comma_separated_data; ?>
                                                            </td>
                                                            <td><?php
                                                                if ($view_consultation_notes == 1) {
                                                                    $sql = "SELECT distinct notes FROM notes where hospital_no ='$hospital_no' AND notes_type='C' AND status=1 AND DATE(date_entry) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                    $diagnosis = $db->prepare($sql);
                                                                    $diagnosis->execute();
                                                                    $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                    $comma_separated_data = implode(', ', $results);
                                                                    echo $comma_separated_data;
                                                                } ?>
                                                            </td>

                                                            <td><?php
                                                                if ($view_consultation_notes == 1) {
                                                                    $sql = "SELECT notes FROM notes where hospital_no ='$hospital_no' AND notes_type='plan' AND status=1 AND DATE(date_entry) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                    $diagnosis = $db->prepare($sql);
                                                                    $diagnosis->execute();
                                                                    $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                    $comma_separated_data = implode(', ', $results);
                                                                    echo $comma_separated_data;
                                                                } ?>
                                                            </td>


                                                            <td><?php
                                                                $sql = "SELECT distinct test_name FROM lab_manage where patient ='$hospital_no' AND section='Laboratory' AND data_capture_status='approve' AND DATE(request_date) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->execute();
                                                                $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                $comma_separated_data = implode(', ', $results);
                                                                echo $comma_separated_data; ?>
                                                            </td>
                                                            <td><?php
                                                                $sql = "SELECT distinct test_name FROM lab_manage where patient ='$hospital_no' AND section='Radiology' AND data_capture_status='approve' AND DATE(request_date) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->execute();
                                                                $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                $comma_separated_data = implode(', ', $results);
                                                                echo $comma_separated_data; ?>
                                                            </td>


                                                            <td><?php
                                                                $sql = "SELECT distinct item_services FROM patient_ap_services 
                                  where hospital_no ='$hospital_no' AND drug_status='1' AND serv_group='Pharmacy' AND DATE(date_entry) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->execute();
                                                                $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                $comma_separated_data = implode(', ', $results);
                                                                echo $comma_separated_data; ?>
                                                            </td>


                                                            <td><?php
                                                                $sql = "SELECT distinct item_services FROM patient_ap_services 
                                  where hospital_no ='$hospital_no' AND paystatus='1' AND serv_group='Medical Services' AND DATE(date_entry) between '$start2' and '$end2'"; // Replace with your actual table and column name
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->execute();
                                                                $results = $diagnosis->fetchAll(PDO::FETCH_COLUMN);
                                                                $comma_separated_data = implode(', ', $results);
                                                                echo $comma_separated_data; ?>
                                                            </td>

                                                            <td>
                                                                <?php


                                                                $sql = "SELECT fullname FROM admin_users WHERE id = :doctor_id LIMIT 1";
                                                                $diagnosis = $db->prepare($sql);
                                                                $diagnosis->bindParam(':doctor_id', $doctor_id);
                                                                $diagnosis->execute();
                                                                $result = $diagnosis->fetchColumn();
                                                                echo $result; // This will output the single item_services value

                                                                ?>
                                                            </td>


                                                            <td><?php echo $ap_date_time; ?></td>
                                                            <td><?php echo $checkin_by; ?></td>
                                                            <td><?php echo $status; ?></td>
                                                            <td><?php echo $app_expiration_date; ?></td>
                                                            <td><?php echo $app_duration; ?></td>

                                                            <?php if ($patient_way == "IN-PATIENT") { ?>
                                                                <td><?php echo $rwx['doc_incharge']; ?></td>
                                                                <td><?php echo $rwx['room_bed']; ?></td>
                                                                <td><?php echo $rwx['floor']; ?></td>
                                                                <td><?php


                                                                    $dept = $rwx['dept_id'];
                                                                    $sql = "SELECT department FROM department WHERE sn = :dept LIMIT 1";
                                                                    $diagnosis = $db->prepare($sql);
                                                                    $diagnosis->bindParam(':dept', $dept);
                                                                    $diagnosis->execute();
                                                                    $result = $diagnosis->fetchColumn();
                                                                    echo $result; // This will output the single item_services value
                                                                    ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['reason_adm'];
                                                                    } ?></td>
                                                                <td>
                                                                    <?php
                                                                    // Check if date_admit is not null or empty before formatting
                                                                    if ($view_consultation_notes == 1) {
                                                                        if (!empty($rwx['date_admit'])) {
                                                                            echo date("d,M y H:i:s a", strtotime($rwx['date_admit']));
                                                                        } else {
                                                                            echo ''; // Output an empty string if date_admit is null or empty
                                                                        }
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    // Check if date_discharge is not null or empty before formatting
                                                                    if ($view_consultation_notes == 1) {
                                                                        if (!empty($rwx['date_discharge'])) {
                                                                            echo date("d,M y H:i:s a", strtotime($rwx['date_discharge']));
                                                                        } else {
                                                                            echo ''; // Output an empty string if date_discharge is null or empty
                                                                        }
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    // Check if both date_admit and date_discharge are not null or empty
                                                                    if ($view_consultation_notes == 1) {
                                                                        if (!empty($rwx['date_admit']) && !empty($rwx['date_discharge'])) {
                                                                            // Convert dates to timestamps
                                                                            $date_admit = strtotime($rwx['date_admit']);
                                                                            $date_discharge = strtotime($rwx['date_discharge']);

                                                                            // Calculate the difference in seconds
                                                                            $difference = $date_discharge - $date_admit;

                                                                            // Convert seconds to days
                                                                            $total_days = floor($difference / (60 * 60 * 24)); // 60 seconds * 60 minutes * 24 hours

                                                                            // Output the total days
                                                                            echo $total_days;
                                                                        } else {
                                                                            echo 'N/A'; // Output 'N/A' if any date is null or empty
                                                                        }
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td><?php echo $rwx['discharge_status']; ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['discharge_note'];
                                                                    } ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['discharge_name'];
                                                                    } ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['discharge_by_nurse'];
                                                                    } ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['care_giver'];
                                                                    } ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['care_giver_phone'];
                                                                    } ?></td>
                                                                <td><?php if ($view_consultation_notes == 1) {
                                                                        echo $rwx['relationship'];
                                                                    } ?></td>

                                                            <?php } ?>

                                                        </tr>
                                                    <?php $n++;
                                                    } ?>
                                                </tbody>
                                            </table>

                                        <?php } else { ?>

                                            <div class="alert alert-danger">No Records Found</div>
                                    <?php }
                                    }
                                    ?>



                        </div>
                    </div>
                </div>
            </div>

            <!-- /page content -->

            <!-- footer content -->

            <!-- /footer content -->
        </div>
    </div>

    <!-- jQuery -->
    <script src="vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <!-- NProgress -->
    <!-- iCheck -->
    <!-- Datatables -->
    <script src="vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="vendors/jszip/dist/jszip.min.js"></script>
    <script src="vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="vendors/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="build/js/custom.min.js"></script>

</body>

</html>