<?php

if (isset($_GET["see_doctor_now"])) {

    $app_no = $_GET['see_doctor_now'];
    $s_id   = $_GET['s_id'];

    try {
        // Start Transaction
        $db->beginTransaction();

        // 1. Get specialist_id
        $stmt = $db->prepare("SELECT specialist_id FROM prices_table WHERE sn = ? LIMIT 1");
        $stmt->execute(array($s_id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $app_by = (!empty($row['specialist_id'])) ? $row['specialist_id'] : 'anydoctor';

        // 2. Update appointment safely
        $cur_date_time = date('Y-m-d H:i:s');

        $update = $db->prepare("
            UPDATE apptm 
            SET app_by = ?, 
                re_queue_lock = 0, 
                vital_lock = 0,
                queue_time_stamp = ?
            WHERE appt_no = ?
        ");

        $success = $update->execute(array($app_by, $cur_date_time, $app_no));

        if (!$success || $update->rowCount() == 0) {
            // Something wrong → Rollback
            $db->rollBack();
            $sv = 0;
        } else {
            // Successful → Commit
            $db->commit();
            $sv = 1;
        }
    } catch (Exception $e) {
        // On exception → Rollback
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $sv = 0;

        // Optional: log error
        // error_log("See doctor now error: " . $e->getMessage());
    }
}


if (isset($_POST["tranfer_sub_code"])) {

    $hos_no = isset($_POST['hos_no']) ? $_POST['hos_no'] : '';
    $app_no = isset($_POST['ap']) ? $_POST['ap'] : '';

    // Determine authorization code
    if (isset($_POST['auth_code'])) {
        $a_code = "0000";
    } else {
        $a_code = isset($_POST['a_code']) ? $_POST['a_code'] : '';
    }

    // Only proceed if authorization code is not empty
    if ($a_code != '') {

        try {
            $stmt = $db->prepare('
                UPDATE apptm
                SET ap_type = :ap_type, auth_code = :auth_code
                WHERE appt_no = :appt_no
            ');

            $ap_type = 2;

            $stmt->bindParam(':ap_type', $ap_type);
            $stmt->bindParam(':auth_code', $a_code);
            $stmt->bindParam(':appt_no', $app_no);

            $success = $stmt->execute();

            if ($success) {
                echo '<div class="alert alert-success">Authorization code updated successfully.</div>';
            } else {
                echo '<div class="alert alert-danger">Failed to update authorization code.</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Database error: ' . $e->getMessage() . '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">Authorization code cannot be empty.</div>';
    }
}


$alert = '';

if (isset($_POST["save_refer"])) {

    $hosp_no = $_POST['hosp_no'];
    $app_no  = $_POST['app_no'];
    $dr_name = $_POST['dr_name'];

    try {
        $db->beginTransaction();

        $queue_time_stamp = date("Y-m-d H:i:s");

        // FIRST UPDATE
        $sql1 = "UPDATE apptm 
                 SET re_queue_lock = 0,
                     vital_lock = 0,
                     queue_time_stamp = ?
                 WHERE hospital_no = ? 
                   AND appt_no = ?";

        $stmt1 = $db->prepare($sql1);
        $stmt1->execute(array($queue_time_stamp, $hosp_no, $app_no));

        if ($stmt1->rowCount() == 0) {
            throw new Exception("Queue update failed or record not found.");
        }

        // SECOND UPDATE (MANDATORY NOW)
        if (empty($dr_name)) {
            throw new Exception("Doctor name is required.");
        }

        $sql2 = "UPDATE apptm 
                 SET app_by = ? 
                 WHERE hospital_no = ? 
                   AND appt_no = ?";

        $stmt2 = $db->prepare($sql2);
        $stmt2->execute(array($dr_name, $hosp_no, $app_no));

        if ($stmt2->rowCount() == 0) {
            throw new Exception("Doctor assignment failed.");
        }

        // IF BOTH SUCCEED → COMMIT
        $db->commit();

        $alert = '<div class="alert alert-success alert-dismissible fade in">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <strong>Success!</strong> Patient Referral Updated Successfully.
                  </div>';
    } catch (Exception $e) {

        // ANY FAILURE → ROLLBACK EVERYTHING
        $db->rollBack();

        $alert = '<div class="alert alert-danger alert-dismissible fade in">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <strong>Error!</strong> ' . $e->getMessage() . '
                  </div>';
    }
}


?>
<?php if (!empty($alert)) echo $alert; ?>

<div class="row">

    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Set Enquiry Panel</h5>
            </div>

            <div class="ibox-content">

                <div class="row">

                    <div class="col-lg-4">

                        <form action="index.php?equiry" method="POST">

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Patient Appointment History</label>
                                <select name="in_patient" class="input-sm chosen-select" style="width:350px;">
                                    <option selected="selected" value="">Search and Select Patient</option>
                                    <?php $stmt = $db->query("SELECT distinct e.hospital_no, e.surname, e.fname,oname FROM enrollee as e inner join apptm as p on p.hospital_no=e.hospital_no WHERE e.hmo_no!='' AND e.insurance!='' order by e.hospital_no");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["hospital_no"]; ?>"><?php echo $row["hospital_no"] . ' ' . $row["fname"]  . ', ' . $row["surname"] . ' ' . $row["oname"]; ?></option>
                                    <?php } ?>
                                </select>

                            </div>
                            <div class="form_sep">
                                <button type="submit" class="btn btn-success btn btn-sm" autofocus name="apply_patient" id="apply_patient">Apply Search</button>
                            </div>
                        </form>

                    </div>



                    <?php
                    // Get the current date in the format YYYY-MM-DD
                    $currentDate = date('Y-m-d');
                    ?>

                    <div class="col-lg-5">

                        <form action="index.php?equiry" method="POST">
                            <div class="form_sep">
                                <label for="reg_input_no" class="req">
                                    <b style="color: red;">Search and Refer Appointments by Date:</b> </label><br>
                                <div class="form_sep" id="">
                                    <div class="input-daterange input-group" id="">
                                        <input type="date" class="input-sm form-control" name="start" value="<?php echo $currentDate; ?>" required />
                                        <span class="input-group-addon">to</span>
                                        <input type="date" class="input-sm form-control" name="end" value="<?php echo $currentDate; ?>" required />
                                    </div>

                                </div>
                            </div>

                            <div class="form_sep">
                                <button type="submit" class="btn btn-warning btn btn-sm" name="apply_dates" id="apply_dates">Apply Search</button>

                            </div>

                        </form>
                    </div>

                    <div class="col-lg-3">
                        <a href="index.php?equiry&admitted" class="btn btn-primary btn-sm">View Patient On-Admission</a>

                        <?php if ($_SESSION['unit_head'] == true): ?>
                            <br><br>
                            <h4 style="color:chocolate;">Download Data Below:</h4>
                            <!-- Export CSV Button -->
                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#exportCsvModal" style="margin-bottom:15px;">Patients Visit</button>
                            <!-- Export CSV Button -->
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exportAllEnrolleeCsvModal" style="margin-bottom:15px;">Patients List</button>


                        <?php endif ?>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Contact Details</h5>

                <div class="ibox-tools">
                    <a href="index.php?equiry" class="btn btn-success btn-xs ">Refresh</a> &nbsp; : &nbsp;
                    <span style="color:brown; "><i>View Registered Patient(s):</i> </span>
                    <a href="index.php?equiry&registered=today" class="btn btn-primary btn-xs ">Today</a>
                    <a href="index.php?equiry&registered=yesterday" class="btn btn-success btn-xs ">Yesterday</a>
                    <a href="index.php?equiry&registered=month" class="btn btn-white btn-xs ">Month</a>
                    <a href="index.php?equiry&registered=year" class="btn btn-default btn-xs ">Year</a>
                    &nbsp;|&nbsp;
                    <a href="index.php?equiry&ME=<?php echo $_SESSION['fullname']; ?>" class="btn btn-primary btn-xs "> Current User Booking</a>
                </div>

            </div>

            <div class="ibox-content">

                <?php if (isset($_GET['admitted'])) {
                    $stmt = $db->prepare("SELECT d.hospital_no,d.app_no,d.room_bed,d.room_bed_sn,d.date_admit,d.doc_incharge,d.floor,dd.department FROM admission as d inner join department as dd on dd.sn=d.dept_id WHERE adm_status='3' order by date_admit DESC");
                    $stmt->execute(); ?>
                    <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size:14px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Hosp. No</th>
                                <th>Name</th>
                                <th>Adm. By</th>
                                <th>Room/Ward </th>
                                <th>Floor </th>
                                <th>Dept. </th>
                                <th>Adm. Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $n = 1;
                            while ($roww = $stmt->fetch()) {

                                $app_no = $roww['app_no'];

                                $patient_stmt = $db->prepare("SELECT surname,fname FROM enrollee where hospital_no = ?  LIMIT  1 ");
                                $patient_stmt->execute(array($roww['hospital_no']));
                                $patient = $patient_stmt->fetch();
                            ?>
                                <tr>
                                    <td><?php echo $n; ?></td>
                                    <td>
                                        <a href="index.php?ptm=all/<?php echo urlencode($roww['hospital_no']); ?>">
                                            <?php echo htmlspecialchars($roww['hospital_no']); ?>
                                        </a>
                                    </td>

                                    <td><?php echo $patient['surname'] . ' ' . $patient['fname'] . ' '; ?></td>
                                    <td><?php echo $roww['doc_incharge']; ?></td>
                                    <td><?php echo $roww['room_bed']; ?></td>
                                    <td><?php echo $roww['floor']; ?></td>
                                    <td><?php echo $roww['department']; ?></td>
                                    <td><?php
                                        $date_admit =  $roww['date_admit'];
                                        $last_date_ = null;

                                        echo '' . date('d,M h:i a', strtotime("$date_admit")) . '
                                        ';

                                        ?></td>
                                </tr>

                                <?php

                                ?>

                            <?php

                                $n++;
                            } ?>


                        </tbody>
                    </table>

                <?php } elseif (isset($_POST['apply_patient'])) {

                    $in_patient = $_POST['in_patient'];
                    $patient_stmt = $db->prepare("SELECT surname,fname FROM enrollee where hospital_no = ?  LIMIT  1 ");
                    $patient_stmt->execute(array($in_patient));
                    $patient = $patient_stmt->fetch();  ?>

                    <div class="alert=succes">
                        <h2 style="color:blue; ">EMR: <?= $_POST['in_patient'] . ' : ' . $patient['fname'] . ' ' . $patient['surname']  ?></h2>

                    </div>
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>Visit No</th>
                                <th>Consultation</th>
                                <th>Department</th>
                                <th>Doctor</th>
                                <th></th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $stmt_chk = $db->query("
                                SELECT *
                                FROM apptm
                                WHERE hospital_no = '$in_patient'
                                ORDER BY ap_date_time DESC
                            ");
                            while ($row = $stmt_chk->fetch(PDO::FETCH_ASSOC)) {
                                $app_duration = $row['app_duration'];
                                $ap_date_time = $row['ap_date_time'];

                            ?>
                                <tr>
                                    <td><?php echo $row['appt_no']; ?></td>
                                    <td><?php echo $row['services_name'] . '<br><strong>Checkin By:</strong> ' . $row['checkin_by']; ?></td>
                                    <td><?php
                                        $dept_id = $row['dept'];
                                        $doctor_id = $row['doctor_id'];
                                        $service_id = $row['service_id'];
                                        $app_by = $row['app_by'];
                                        $stmtxx = $db->query("SELECT fullname from admin_users WHERE username='$app_by' or id='$doctor_id'");
                                        if ($stmtxx->rowCount() > 0) {
                                            $row_xs = $stmtxx->fetch(PDO::FETCH_ASSOC);
                                            $doc2 = $row_xs['fullname'];
                                        } else {
                                            $doc2 = '';
                                        }



                                        $stmt = $db->query("SELECT department from department WHERE sn='$dept_id'");
                                        if ($stmt->rowCount() > 0) {
                                            $row_s = $stmt->fetch(PDO::FETCH_ASSOC);
                                            echo $row_s['department'];
                                        } ?></td>

                                    <td><?php echo $doc2; ?></td>
                                    <?php
                                    //////////////---------------------------------------------------------------------------
                                    date_default_timezone_set('Africa/Lagos');
                                    $appt_no = $row['appt_no'];
                                    $hos_no = $row['hospital_no'];
                                    $stmt = $db->query("SELECT a.*,d.department FROM admission as a inner join department as d on d.sn=a.dept_id WHERE hospital_no='$hos_no' and app_no='$appt_no'");
                                    if ($stmt->rowCount() > 0) {
                                        $admitted = 'yes';
                                        $row_s = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $department = $row_s['adm_status']; ///=='4'){
                                        if ($row_s['adm_status'] == '4') {
                                            $admitted = 'no';
                                            /// discharge details		
                                            $Current_date = date('Y-m-d H:i:s');
                                            $date1 = new DateTime($row_s['date_discharge']);
                                            $date2 = new DateTime($row_s['date_admit']);
                                            $diff = $date2->diff($date1);
                                            $hr = $diff->format('%h');
                                            $day = $diff->format('%a');

                                            echo '<td><strong>Discharged</strong><br>(' . $day . 'days/' . $hr . 'hrs' . ')' . '<br><strong>Bed:</strong><br>' . $row_s['room_bed']; ?>
                                    <?php echo '</td>';
                                        } elseif ($row_s['adm_status'] == '3') {
                                            $admitted = 'yes';
                                            $Current_date = date('Y-m-d H:i:s');
                                            $date1 = new DateTime($Current_date);
                                            $date2 = new DateTime($row_s['date_admit']);
                                            $diff = $date2->diff($date1);
                                            $hr = $diff->format('%h');
                                            $day = $diff->format('%a');

                                            echo '<td><strong>Admitted</strong><br>(' . $day . 'days/' . $hr . 'hrs' . ')' . '<br><strong>Bed/Floor/Dept:</strong><br>' . $row_s['room_bed'] . '/<br>' . $row_s['floor'] . '(<strong>' . $row_s['department'] . '</strong>)</td>';
                                        } else {
                                            $admitted = 'no';
                                        }

                                        //---------------------------------------------------------------------------------------							
                                    } else {
                                        $admitted = 'no';
                                        echo '<td></td>';
                                    }


                                    date_default_timezone_set('Africa/Lagos');
                                    $Current_date = date('Y-m-d H:i:s');
                                    $date1 = new DateTime($Current_date);
                                    $date2 = new DateTime($row['app_expiration_date']);
                                    $diff = $date2->diff($date1);
                                    $hr = $diff->format('%h');
                                    $day = $diff->format('%a');
                                    if ($Current_date > $date2) {
                                        $app_status = 'past';
                                    } else {
                                        $app_status = 'notyet';
                                    }


                                    $pay = 'no';
                                    $sql = "SELECT paystatus, sn, pay_mode, item_services 
                                        FROM patient_ap_services 
                                        WHERE hospital_no = ? 
                                        AND app_no = ? 
                                        AND serv_group = 'Consultation'
                                        ORDER BY sn DESC 
                                        LIMIT 1";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute([$hos_no, $appt_no]);

                                    if ($rowxx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $sn            = $rowxx['sn'];
                                        $item_services = $rowxx['item_services'];
                                        $pay_mode      = $rowxx['pay_mode'];
                                        $pay           = ($rowxx['paystatus'] == 1 ? 'yes' : 'no');
                                    }
                                    $ap_status = ($row['status'] == 'checkin') ? '<b>Appointment Valid Until</b>' : '';
                                    echo '<td>' . $ap_status;
                                    ?>

                                    <?php if ($pay == 'yes' and ($days == 100 || $days <= $app_duration)  and $admitted == 'no' and $row['status'] == 'checkin') { ?>

                                        <br><input type="button" name="Change ppt" value="Refer Patient" data-target="#myModal5" id="<?php echo $hos_no . '/' . $appt_no; ?>" class="btn btn-primary btn-xs patient_refer" />
                                        &nbsp;:&nbsp;
                                        <br>
                                        <a href="index.php?equiry&see_doctor_now=<?= $appt_no; ?>&s_id=<?= $service_id ?>">Add to Doctor's Queue List</a>
                                    <?php } ?>

                                    <?php echo '</td>'; ?>
                                    <td><?php echo date("d M Y", strtotime($row['ap_date_time'])) . '<br>' . date("h:i:s a", strtotime($row['ap_date_time']));; ?></td>


                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>




                <?php } else { ?>


                    <?php

                    if (isset($_GET['registered'])) {

                        $filter = $_GET['registered'];
                        $filter_type = "";
                        $report_title = "";

                        // Pagination Settings
                        $limit = 20;
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        if ($page < 1) $page = 1;
                        $offset = ($page - 1) * $limit;

                        // Determine date filter
                        switch ($filter) {

                            case 'today':
                                $filter_type = date("Y-m-d");
                                $report_title = "Report for Today (" . date("d M Y") . ")";
                                $dateWhere = "DATE(date_capture) = :filter_date";
                                $params = [':filter_date' => $filter_type];
                                break;

                            case 'yesterday':
                                $filter_type = date("Y-m-d", strtotime("-1 day"));
                                $report_title = "Report for Yesterday (" . date("d M Y", strtotime("-1 day")) . ")";
                                $dateWhere = "DATE(date_capture) = :filter_date";
                                $params = [':filter_date' => $filter_type];
                                break;

                            case 'week':
                                $start_date = date("Y-m-d", strtotime("-7 days"));
                                $end_date   = date("Y-m-d");
                                $report_title = "Report for Last 7 Days (" . $start_date . " - " . $end_date . ")";
                                $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
                                $params = [':start' => $start_date, ':end' => $end_date];
                                break;

                            case 'month':
                                $start_date = date("Y-m-01");
                                $end_date   = date("Y-m-t");
                                $report_title = "Report for This Month (" . date("F Y") . ")";
                                $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
                                $params = [':start' => $start_date, ':end' => $end_date];
                                break;

                            case 'quarter':
                                $current_month = date('n');
                                $quarter = ceil($current_month / 3);
                                $start_month = ($quarter - 1) * 3 + 1;
                                $start_date = date("Y-$start_month-01");
                                $end_date = date("Y-m-t", strtotime($start_date . " +2 months"));
                                $report_title = "Report for Quarter $quarter (" . date("Y") . ")";
                                $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
                                $params = [':start' => $start_date, ':end' => $end_date];
                                break;

                            case 'year':
                                $start_date = date("Y-01-01");
                                $end_date   = date("Y-12-31");
                                $report_title = "Report for Year (" . date("Y") . ")";
                                $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
                                $params = [':start' => $start_date, ':end' => $end_date];
                                break;

                            default:
                                echo "<b>Invalid filter selected</b>";
                                exit;
                        }

                        // COUNT total rows
                        $countStmt = $db->prepare("SELECT COUNT(*) FROM enrollee WHERE $dateWhere");
                        $countStmt->execute($params);
                        $total_rows = $countStmt->fetchColumn();
                        $total_pages = ceil($total_rows / $limit);

                        // FETCH rows with pagination
                        $stmt = $db->prepare("SELECT * FROM enrollee 
                          WHERE $dateWhere 
                          ORDER BY sn 
                          LIMIT $limit OFFSET $offset");
                        $stmt->execute($params);

                        // Display data
                        if ($stmt->rowCount() > 0) {
                    ?>
                            <div id="printArea">

                                <h3><?php echo $report_title; ?></h3>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Hospital No</th>
                                            <th>Surname</th>
                                            <th>First Name</th>
                                            <th>Gender</th>
                                            <th>Credit Limit</th>
                                            <th>Date</th>
                                            <th>Captured By</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $n = $offset + 1;
                                        while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $n; ?></td>
                                                <td>
                                                    <a href="index.php?ptm=<?php echo htmlspecialchars('all/' . $rwx['hospital_no']); ?>&app">
                                                        <?php echo htmlspecialchars($rwx['hospital_no']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo $rwx['surname']; ?></td>
                                                <td><?php echo $rwx['fname']; ?></td>
                                                <td><?php echo $rwx['gender']; ?></td>
                                                <td><?php echo number_format($rwx['credit_limit']); ?></td>
                                                <td><?php echo date("d, M Y", strtotime($rwx['date_capture'])); ?></td>
                                                <td><?php echo $rwx['captured_by']; ?></td>
                                            </tr>
                                        <?php $n++;
                                        } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ========================= -->
                            <!-- DOWNLOAD + PRINT BUTTONS -->
                            <!-- ========================= -->

                            <?php if ($_SESSION['unit_head'] == 1) { ?>
                                <div style="margin-top:20px;">
                                    <a href="registered_patients.php?registered=<?php echo $filter; ?>"
                                        class="btn btn-success">Download CSV</a>
                                </div>
                            <?php } ?>

                            <!-- ========================= -->
                            <!-- PAGINATION LINKS -->
                            <!-- ========================= -->

                            <nav aria-label="Page navigation" style="margin-top:20px;">
                                <ul class="pagination">

                                    <!-- Previous -->
                                    <li class="page-item <?php echo ($page <= 1 ? 'disabled' : ''); ?>">
                                        <a class="page-link" href="?equiry&registered=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>

                                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                        <li class="page-item <?php echo ($page == $i ? 'active' : ''); ?>">
                                            <a class="page-link" href="?equiry&registered=<?php echo $filter; ?>&page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <!-- Next -->
                                    <li class="page-item <?php echo ($page >= $total_pages ? 'disabled' : ''); ?>">
                                        <a class="page-link" href="?equiry&registered=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>">Next</a>
                                    </li>

                                </ul>
                            </nav>

                        <?php
                        } else {
                            echo '<hr><b>No Record Found</b>';
                        }
                    } else { ?>

                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th>EMR/Name</th>
                                    <th>.</th>
                                    <th>Status</th>
                                    <th>Doctor/Consultation Type</th>
                                    <th>Date</th>
                                    <th>.</th>
                                    <th>.</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $c = 1;
                                if (isset($_POST['apply_dates'])) {
                                    $start = $_POST['start'];
                                    $end = $_POST['end'];
                                    $stmt_chk = $db->query("SELECT * FROM apptm where date(ap_date_time) between '$start' and '$end' order by ap_date_time desc");
                                } elseif (isset($_GET['queue'])) {
                                    $stmt_chk = $db->query("SELECT * FROM apptm where date(ap_date_time)='$setdate' and status='checkin' and queue_lock='0' order by ap_date_time desc");
                                } elseif (isset($_GET['adm'])) {
                                    $stmt_chk = $db->query("SELECT appt.*,adm.date_admit,adm.date_discharge FROM apptm as appt inner join admission as adm on adm.hospital_no=appt.hospital_no where (date(date_admit)='$setdate' or date(date_discharge)='$setdate')");
                                } elseif (isset($_GET['ME'])) {
                                    $ME = $_GET['ME'];
                                    $setdate = date("Y-m-d");
                                    $stmt_chk = $db->query("SELECT * FROM apptm where date(ap_date_time)='$setdate' and checkin_by='$ME' order by sn desc");
                                } else {
                                    $setdate = date("Y-m-d");
                                    $stmt_chk = $db->query("SELECT * FROM apptm WHERE DATE(ap_date_time) = '$setdate' ORDER BY CAST(CONVERT(sn, SIGNED) AS SIGNED) DESC");

                                    //$stmt_chk = $db->query("SELECT * FROM apptm 
                                    ///	WHERE DATE(ap_date_time) = '$setdate' ORDER BY CAST(ap_date_time AS DATETIME) DESC");
                                }

                                while ($row = $stmt_chk->fetch(PDO::FETCH_ASSOC)) {
                                    $service_id = $row['service_id']; ?>
                                    <tr>
                                        <td>
                                            <?php echo $row['hospital_no']; ?>
                                            <?php if (($row['insurance_type'] == 'NHIS' or $row['insurance_type'] == 'PHIS') and $row['ap_type'] == '1' and $row['status'] == 'checkin') { ?>
                                                <small style="color:#F00">[Primary/Care]</small>
                                            <?php } ?>
                                            <?php echo '/<br>' . $row['patient_name']; ?>
                                        </td>
                                        <td><a href="index.php?ptm=all/<?php echo $row['hospital_no']; ?>" class="btn btn-primary btn-sm">View</a></td>

                                        <?php
                                        $doctor_id = $row['doctor_id'];
                                        $app_by = $row['app_by'];
                                        $stmtxx = $db->query("SELECT fullname from admin_users WHERE username='$app_by' or id='$doctor_id'");
                                        if ($stmtxx->rowCount() > 0) {
                                            $row_xs = $stmtxx->fetch(PDO::FETCH_ASSOC);
                                            $doc = $row_xs['fullname'];
                                        } else {
                                            $doc = '<b>Any Doctor Available</b>';
                                        }

                                        $appt_no = $row['appt_no'];
                                        $app_duration = $row['app_duration'];
                                        $hos_no = $row['hospital_no'];

                                        $stmt = $db->query("SELECT adm_status,doc_incharge,room_bed,date_admit,date_discharge,cond FROM admission WHERE hospital_no='$hos_no' and app_no='$appt_no'");
                                        if ($stmt->rowCount() > 0) {
                                            $admitted = 'yes';
                                            $row_s = $stmt->fetch(PDO::FETCH_ASSOC);
                                            if ($row_s['adm_status'] == '4') {
                                                /// discharge details		
                                                date_default_timezone_set('Africa/Lagos');
                                                $Current_date = date('Y-m-d H:i:s');
                                                $date1 = new DateTime($row_s['date_discharge']);
                                                $date2 = new DateTime($row_s['date_admit']);
                                                $diff = $date2->diff($date1);
                                                $hr = $diff->format('%h');
                                                $day = $diff->format('%a');

                                                echo '<td><strong>Discharged</strong><br>(' . $day . 'days/' . $hr . 'hrs' . ')' . '<br><strong>Bed:</strong><br>' . $row_s['room_bed'] . '</td>';
                                            } else {

                                                /// discharge details		
                                                date_default_timezone_set('Africa/Lagos');
                                                $Current_date = date('Y-m-d H:i:s');
                                                $date1 = new DateTime($Current_date);
                                                $date2 = new DateTime($row_s['date_admit']);
                                                $diff = $date2->diff($date1);
                                                $hr = $diff->format('%h');
                                                $day = $diff->format('%a');

                                                echo '<td><strong>Admitted</strong><br>(' . $day . 'days/' . $hr . 'hrs' . ')' . '<br><strong>Bed:</strong><br>' . $row_s['room_bed'] . '</td>';
                                            }
                                        } else { ?>
                                            <td><?php
                                                ////// NON - ADMITTED STATU SHOW
                                                $admitted = 'no';

                                                date_default_timezone_set('Africa/Lagos');
                                                $Current_date = date('Y-m-d H:i:s');
                                                $date1 = new DateTime($Current_date);
                                                $date2 = new DateTime($row['ap_date_time']);
                                                $diff = $date2->diff($date1);
                                                $hr = $diff->format('%h');
                                                $day = $diff->format('%a');
                                                if ($Current_date > $date2) {
                                                    $app_status = 'past';
                                                } else {
                                                    $app_status = 'notyet';
                                                }


                                                $chkpay = $db->query("SELECT paystatus,sn,pay_mode,pay,claim_amt,transact_date,item_services,cat_type FROM patient_ap_services WHERE hospital_no='$hos_no' and app_no='$appt_no' and (serv_group='Consultation' or cat_type='Dialysis') order by sn desc limit 1");
                                                if ($chkpay->rowCount() > 0) {
                                                    $rwxx = $chkpay->fetch(PDO::FETCH_ASSOC);
                                                    $sn = $rwxx['sn'];
                                                    $item_services = $rwxx['item_services'];
                                                    $cat_type = $rwxx['cat_type'];
                                                    $pay_mode = $rwxx['pay_mode'];
                                                    $paystatus = $rwxx['paystatus'];
                                                    $transact_date = date('Y-m-d', strtotime($rwxx['transact_date'])); ///$rwxx['transact_date'];

                                                    if ($rwxx['paystatus'] == 1 && $rwxx['pay'] == 0 && $rwxx['claim_amt'] == 0) {
                                                        $pay = 'free'; // free = both values zero
                                                    } elseif ($rwxx['paystatus'] == 1 && ($rwxx['pay'] > 0 || $rwxx['claim_amt'] > 0)) {
                                                        $pay = 'yes'; // paid = one or both values > 0
                                                    } else {
                                                        $pay = 'no'; // paystatus is not 1
                                                    }
                                                } else {
                                                    $pay = 'no';
                                                }

                                                if ($pay == 'no' and  $row['ap_date_time'] > $Current_date) {
                                                    echo 'Appointment <br><strong style="color:#F00">[ Pay Pending ]</strong><br> ' . $day . ' days/' . $hr . 'hrs ' . '<br><strong style="color:#00F"> [ Remaining ] </strong>';
                                                } elseif ($row['queue_lock'] == '0' and $row['status'] == 'checkin' and $row['ap_date_time'] <= $Current_date) {
                                                    echo 'Waiting: <br> ' . $day . ' days/' . $hr . 'hrs ago';
                                                    if ($row['app_state'] == 'em') {
                                                        echo '<br><strong style="color:#F00">[ Emergency ]</strong>';
                                                    }
                                                } elseif ($row['queue_lock'] == '0' and $row['status'] == 'checkin' and $row['ap_date_time'] > $Current_date) {
                                                    echo 'Appointment [ Paid ]: <br> ' . $day . ' days/' . $hr . 'hrs ' . '<br><strong style="color:#00F"> [ Remaining ] </strong>';
                                                } elseif ($row['queue_lock'] == '1' and $row['status'] == 'checkin') {
                                                    echo 'Seen Doctor:<br> ' . $day . ' days/' . $hr . 'hrs ago';
                                                } elseif ($row['queue_lock'] == '1' and $row['status'] == 'discharge') {
                                                    echo 'Discharged seen:<br> ' . $day . ' days/' . $hr . 'hrs ago';
                                                } elseif ($row['status'] == 'cancelled') {
                                                    echo 'Cancelled';
                                                } else {
                                                    echo $row['status'];
                                                }
                                                ?>
                                            </td>


                                        <?php
                                        }

                                        ?>

                                        <td><?php    // '<br><strong>Checkin By:</strong> '.$row['checkin_by'];
                                            echo '<strong>Contact: </strong>' . $doc; // $row['referal_doc'];
                                            if (strtoupper($cat_type) == 'DIALYSIS') {
                                                echo '<br>' . '<strong>DIALYSIS</strong>';
                                            } else {
                                                echo '<br>' . '<strong>' . $item_services . '</strong>';
                                            } ?></td>
                                        <td><?php echo date("d M Y", strtotime($row['ap_date_time'])) . '/<br>' . date("h:i:s a", strtotime($row['ap_date_time'])); ?></td>
                                        <td>
                                            <?php

                                            $insurance = strtoupper($row['insurance_type']);
                                            $validInsurance = ($insurance === 'NHIS' || $insurance === 'PHIS' || $insurance === 'CORPORATE');
                                            $statusValid = ($row['status'] === 'checkin' || $row['status'] === 'discharge');
                                            $authMissing = ($row['auth_code'] === '' || $row['auth_code'] === '0' || $row['auth_code'] === '0000');
                                            $buttonId = $hos_no . '/' . $row['appt_no'] . '/equiry';

                                            if ($validInsurance  && $statusValid  && $authMissing) {
                                                // Case 1: AP type 1 + valid insurance + status checkin/discharge
                                                echo '<input type="button" value="Auth. Code" data-target="#myModal5" 
            id="' . $buttonId . '" class="btn btn-primary btn-sm auth_code" />';
                                            } elseif ($validInsurance && $row['ap_type'] == '2' && $authMissing) {

                                                // Case 2: AP type 2 + valid insurance + missing auth code
                                                echo '<strong style="color:#F00">No Auth/Code</strong>&nbsp;';
                                                echo '<input type="button" value="Auth. Code" data-target="#myModal5" 
            id="' . $buttonId . '" class="btn btn-primary btn-sm auth_code" />';
                                            } elseif (!$authMissing) {

                                                // Case 3: Auth code exists
                                                echo '<strong style="color:#009">Auth. Code:<br>' . $row['auth_code'] . '</strong>';
                                            }
                                            ?>

                                        </td>
                                        <td>
                                            <?php if ($insurance == 'PRIVATE(SELF)') {
                                                $dis_insur = 'SELF';
                                                $tag = 'success';
                                            } elseif (strtoupper($insurance) == 'CORPORATE') {
                                                $dis_insur = 'CORP.';
                                                $tag = 'warning';
                                            } else {
                                                $tag = 'primary';
                                                $dis_insur = $insurance;
                                            }
                                            ?>
                                            <span style="font-size: 14px;;" class="label label-<?= $tag; ?>"><?php echo $dis_insur; ?></span>

                                        </td>
                                        <td>

                                            <?php  ///echo $app_duration . ' - ' .$day . '-' . $admitted .'' . $row['status']; 
                                            ?>
                                            <div class="btn-group">
                                                <button data-toggle="dropdown" class="btn btn-danger btn-sm dropdown-toggle">Select Action&nbsp;<span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    <br>
                                                    <li><?php //($pay == 'yes' || $pay == 'free') and 
                                                        if (($day == 100 || $day <= $app_duration)  and $admitted == 'no' and $row['status'] == 'checkin') { ?>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="Change ppt" value="Refer Patient" data-target="#myModal5" id="<?php echo $hos_no . '/' . $row['appt_no']; ?>" class="btn btn-primary btn-xs patient_refer" />
                                                        <?php } ?>
                                                    </li>
                                                    <?php if ($pay == 'yes' and $row['queue_lock'] == '0' &&  $row['status'] != 'cancelled' && $setdate >= $transact_da) { ?>
                                                        <li><a href="index.php?ptm=all/<?php echo $hos_no . '&drv=' . $hos_no . '/' . $row['appt_no'] . '/' . $sn . '/' . $pay_mode; ?>" onclick="return confirm('Are you sure you want to delete appointment ?')">Delete Appointment & Reverse Pay/Claim</a></li>
                                                    <?php } elseif ($pay == 'yes' and $row['queue_lock'] == '0' and  $row['status'] != 'cancelled' and $transact_date != $setdate) { ?>
                                                        <li><a href="index.php?equiry&see_doctor_now=<?php echo $row['appt_no'] . '&s_id=' . $service_id; ?>"><b>Add Patient to Doctor Queue List</b></a></li>
                                                    <?php } ?>

                                                    <?php if ($pay == 'yes' and $row['queue_lock'] == '1' and  $row['status'] != 'cancelled') { ?>
                                                        <li><a href="index.php?equiry&see_doctor_now=<?php echo $row['appt_no'] . '&s_id=' . $service_id; ?>"><b>Add Patient to Doctor Queue List</b></a></li>
                                                        <li><a href="index.php?ptm=all/<?php echo $hos_no . '&dgr=' . $hos_no . '/' . $row['appt_no'] . '/' . $sn . '/' . $pay_mode; ?>" onclick="return confirm('Are you sure you want to Close Existing appointment ?')">Close Appointment & Open New</a></li>
                                                    <?php } ?>

                                                    <?php if ($pay == 'no' && $row['status'] != 'cancelled' && strtoupper($cat_type) != 'DIALYSIS') { ?>
                                                        <li><a href="index.php?ptm=all/<?php echo $hos_no . '&dlp=' . $hos_no . '/' . $row['appt_no']; ?>" onclick="return confirm('Are you sure you want to Delete appointment?')">Delete Appointment</a></li>
                                                    <?php } ?>

                                                    <?php if (($app_status = 'past' or $app_status = 'notyet') and $pay == 'yes' and $row['status'] == 'future') { ?>
                                                        <li><a href="index.php?ptm=all/<?php echo $hos_no . '&drn=' . $row['appt_no']; ?>">See Doctor Now</a></li>
                                                    <?php } ?>
                                                </ul>
                                            </div>

                                            <?php
                                            if (strtoupper($rwxx['pay_mode']) == 'CASH' && $rwxx['paystatus'] == 0 && strtoupper($cat_type) != 'DIALYSIS') {  ?>
                                                <button class="btn btn-warning btn-sm dropdown-toggle" onClick="payfrom_wallet('<?php echo $hos_no . '___' . $sn; ?>')">Pay</button>
                                            <?php } ?>


                                        </td>

                                    </tr>
                                <?php     } ?>
                            </tbody>
                        </table>

                    <?php } ?>


                    <hr>

                    <?php
                    $c = 1;
                    $stmt = $db->query("SELECT * FROM discharge_fellowup order by sn ");
                    if ($stmt->rowCount() > 0) { ?>

                        <h4 style="color:#F00">Discharge Requests</h4>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th data-toggle="true">#</th>
                                    <th data-toggle="true">Hospital No</th>
                                    <th data-toggle="true">Name</th>
                                    <th data-toggle="true">Sent by</th>
                                    <th data-toggle="true">Time</th>
                                    <th data-toggle="true">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $n = 1;
                                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $Current_date = date('Y-m-d H:i:s');
                                    $date1 = new DateTime($Current_date);
                                    $date2 = new DateTime($roww['date_ap'] . ' ' . $roww['ap_time']);
                                    $diff = $date2->diff($date1);
                                    $hr = $diff->format('%h');
                                    $day = $diff->format('%a');

                                ?>
                                    <tr>
                                        <td><?php echo $n; ?></td>
                                        <td><?php echo $roww['hospital_no']; ?></td>
                                        <td><?php echo $roww['patient_name']; ?></td>
                                        <td><?php echo $roww['referal_doc']; ?></td>
                                        <td><?php echo date('d,M y', strtotime($roww['date_ap'])) . ' ' . date('H:i:s a', strtotime($roww['ap_time'])) . '/<br>' . $day . 'day' . ':' . $hr . 'hr ago'; ?></td>
                                        <td><?php echo $roww['status']; ?></td>
                                    </tr>
                                <?php $n++;
                                } ?>
                            </tbody>
                        </table>
                        <hr>
                    <?php } ?>

                <?php } ?>
            </div>
        </div>
    </div>




    <div class="modal inmodal fade" id="refer_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id=""></h4>
                </div>
                <div class="modal-body" id="refer_body">

                </div>
            </div>
        </div>
    </div>



    <div class="modal inmodal fade" id="auth_code_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id=""></h4>
                </div>
                <div class="modal-body" id="auth_code_body">



                </div>
            </div>
        </div>
    </div>



    <div class="modal inmodal fade" id="payfrom_wallet_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id=""></h4>
                </div>
                <div class="modal-body" id="payfrom_wallet_body">



                </div>
            </div>
        </div>
    </div>



    <!-- Export CSV Modal -->
    <div class="modal fade" id="exportCsvModal" tabindex="-1" role="dialog" aria-labelledby="exportCsvModalLabel">
        <div class="modal-dialog" role="document">
            <form id="exportCsvForm" method="post" action="export_enrollee_csv.php" target="_blank">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="exportCsvModalLabel">Export Patient Visits (CSV)</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Date Range</label>
                            <div class="input-daterange input-group">
                                <input type="date" class="form-control" name="start_date" required>
                                <span class="input-group-addon">to</span>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Select Columns</label><br>
                            <small><i>Select the colums that you want in the exported data</i></small><br><br>
                            <label><input type="checkbox" name="cols[]" value="name" checked> Name</label>
                            <label><input type="checkbox" name="cols[]" value="insurance" checked> Insurance</label>
                            <label><input type="checkbox" name="cols[]" value="hospital_no" checked> Hospital Number</label>
                            <label><input type="checkbox" name="cols[]" value="email"> Email</label>
                            <label><input type="checkbox" name="cols[]" value="phone"> Phone</label>
                            <label><input type="checkbox" name="cols[]" value="date_of_visit"> Date of Visit</label>
                            <label><input type="checkbox" name="cols[]" value="doctor_name"> Specialist/Doctor Name</label>
                            <label><input type="checkbox" name="cols[]" value="was_admitted"> Was Admitted</label>
                            <label><input type="checkbox" name="cols[]" value="appt_status"> Appointment Status</label>
                            <label><input type="checkbox" name="cols[]" value="consultation_type"> Clinic/Consultation Type</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Export CSV</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Export CSV Modal -->
    <div class="modal fade" id="exportAllEnrolleeCsvModal" tabindex="-1" role="dialog" aria-labelledby="exportAllEnrolleeCsvModalLabel">
        <div class="modal-dialog" role="document">
            <form id="exportAllEnrolleeCsvForm" method="post" action="export_enrollee_all_csv.php" target="_blank">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="exportCsvModalLabel">Export Patients (CSV)</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Columns</label><br>
                            <small><i>Select the colums that you want in the exported data</i></small><br><br>
                            <label><input type="checkbox" name="cols[]" value="name" checked> Name</label>
                            <label><input type="checkbox" name="cols[]" value="hospital_no" checked> Hospital Number</label>
                            <label><input type="checkbox" name="cols[]" value="email"> Email</label>
                            <label><input type="checkbox" name="cols[]" value="phone"> Phone</label>
                            <label><input type="checkbox" name="cols[]" value="dob"> Date of Birth</label>
                            <label><input type="checkbox" name="cols[]" value="address"> address</label>
                            <label><input type="checkbox" name="cols[]" value="gender"> Gender</label>
                            <label><input type="checkbox" name="cols[]" value="blood_goup"> Blood Group</label>
                            <label><input type="checkbox" name="cols[]" value="genotype"> Genotype </label>
                            <label><input type="checkbox" name="cols[]" value="hmo_no"> HMO Number </label>
                            <label><input type="checkbox" name="cols[]" value="insurance"> Insurance Type </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Export CSV</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        function payfrom_wallet(get_details) {

            ///var pay_wall;
            ///	alert(get_details);

            $.ajax({
                url: "../payfrom_wallet.php",
                method: "POST",
                data: {
                    pay_wall: get_details
                },
                success: function(data) {

                    $('.modal-title').text('Pay From Wallet / Patient Deposits');

                    $('#payfrom_wallet_modal').modal('show');
                    $('#payfrom_wallet_body').html(data);
                }
            });

        }
    </script>