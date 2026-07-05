<form method="POST" id="ext_form" action="index.php">

    <div class="row">
        <div class="col-md-4">
            <label for="reg_input_no" class="">Filter By Day(s)</label>
            <select name="by_days" data-placeholder="Search..." id="patient_seen_consultation_services" class="input-sm chosen-select patient_seen_report_elem" style="font-size: 18px;">
                <option selected value="">-- select --</option>
                <option value="today">Today</option>
                <option value="-1 day">Yesterday</option>
                <option value="-2 days"> 2 Days ago</option>
                <option value="-3 days"> 3 Days ago</option>
                <option value="-7 days"> 7 Days ago</option>
                <option value="-14 days"> 14 Days ago</option>
            </select>
            <?php if ($_SESSION['rights'] != 'PY') { ?>
                <br><br>
                <b><input type="checkbox" name="showSeenByOthers" id="showSeenByOthers" style="width: 12px;height:12px;
            transform: scale(1.5);margin-right: 10px;"> <span class="text-danger" style="font-size: 14px;;">Tick to See Patients Seen by other Doctors</span></b>
            <?php } ?>
        </div>
        <!--
        <div class="col-md-4">
            <label for="reg_input_no" class="">Filter By Date</label>
            <input type="date" class="input-sm form-control patient_seen_report_elem" id="patient_seen_date" name="patient_seen_date" value="" />
        </div>
            -->
        <div class="form-group">
            <label class="font-normal">Select Dates</label>
            <div class="input-daterange input-group" id="">
                <input type="date" class="input-sm form-control" name="start_date" />
                <span class="input-group-addon">to</span>
                <input type="date" class="input-sm form-control" name="end_date" />
            </div>
        </div>


        <div class="col-md-4">

            <label for="reg_input_no" class="">.</label><BR>
            <button class="btn btn-success btn-sm" type="submit" name="__patient__seen__btn__">Apply</button>

        </div>
    </div>


</form>
<div>
    <?php
    if (isset($_POST['__patient__seen__btn__'])) {
        $by_days = cleanInput($_POST['by_days']);
        //$__date__ = cleanInput($_POST['patient_seen_date']);
        $start_date = cleanInput($_POST['start_date']);
        $end_date = cleanInput($_POST['end_date']);
        $showSeenByOthers = false;

        $doctor_name = $_SESSION['fullname'];
        $doctor_id = $_SESSION['id'];

        $created_by_sql = " created_by = $doctor_id ";

        if (isset($_POST['showSeenByOthers'])) {
            $showSeenByOthers = true;
            $created_by_sql = " created_by != $doctor_id ";
        };


        $SQL_DATE_SERVICE_STRING = '';
        if (!empty($by_days)) {
            if ($by_days == 'today') {
                $__date__ = date('Y-m-d');
            } else {
                $__date__ = date('Y-m-d', strtotime($by_days));
            }
            $stmt = $db->query(" SELECT DISTINCT app_no FROM notes WHERE ( $created_by_sql )  
                             AND date_entry LIKE '$__date__%' ORDER BY sn DESC ");
            $date_title = date('d M Y', strtotime($__date__));
        } elseif (!empty($start_date) && !empty($end_date)) {
            $stmt = $db->query(" SELECT DISTINCT app_no FROM notes WHERE ( $created_by_sql )  
                             AND DATE(date_entry) BETWEEN '$start_date' AND '$end_date' ORDER BY sn DESC");

            $date_title = date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date));
        } else {
            $__date__ = date('Y-m-d');
            $stmt = $db->query(" SELECT DISTINCT app_no FROM notes WHERE ( $created_by_sql )  
                             AND date_entry LIKE '$__date__%' ORDER BY sn DESC ");
            $date_title = date('d M Y', strtotime($__date__));
        }


        // if (!empty($__date__)) {
        $sn = 0;

    ?>
        <br>
        <h3>Date: <?= $date_title; ?></h3>
        <table class='table table-striped table-bordered table-hover dataTables-example' style="font-size: 15px;">
            <thead>
                <tr>
                    <th width='2%'>No</th>
                    <th>Patient</th>
                    <th>Consultation/Service</th>
                    <th>Appointment Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php


                while ($row = $stmt->fetch()) {
                    $app_no = $row['app_no'];

                    $stmt2 = $db->query(" SELECT patient_name, services_name, doctor_id, hospital_no,date_ap, ap_date_time FROM apptm WHERE appt_no = '$app_no'  LIMIT 1 ");

                    if ($stmt2->rowCount() > 0) {
                        $row2 = $stmt2->fetch();
                        $ap_date_time = $row2['ap_date_time'];

                        $doctorName = '';
                        if ($row2['doctor_id'] != $doctor_id) {
                            $user = $AdminUser->find($row2['doctor_id']);
                            if (!empty($user)) {
                                $doctorName = $user->fullname;
                            }
                        } else {
                            $doctorName = 'You';
                        }



                        echo '
                                    <tr>
                                    <td>' . ++$sn . '</td>
                                    <td>' . $row2['patient_name'] . ' <b>(' . $row2['hospital_no'] . ')</b>' . '</td>
                                    <td>' . $row2['services_name'] . ' <br><b>Seen by: ' . $doctorName . '</b></td>
                                    <td>' . date('d, M', strtotime("" . $ap_date_time)) . ' (' . dateDifference_format($row2["ap_date_time"]) . ')</td>
                                    <td class="text-center"><a href = "patient.php?hosp_no=' . $row2['hospital_no'] . '&app=' . $app_no . '" class="btn btn-sm btn-primary">View Patient</a></td>
                                </tr>
                                    ';
                    }
                }
                ?>
            </tbody>
        </table>
    <?php

        //  }
    }
    ?>
</div>