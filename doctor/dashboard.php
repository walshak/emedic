<style>
    .card-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-item {
        text-align: center;
        margin: 10px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
    }
</style>
<?php

$setdate = date("Y-m-d");
$yr = date("Y");
$mth = date("m");
$profile = $_SESSION['username'];

if (isset($_REQUEST['open_patient_page'])) {
    $hospital_no = $_REQUEST['hospital_no'];
?>
    <script type="text/javascript">
        location = "index.php?hosp_no=<?= $hospital_no; ?>";
    </script>
<?php
}


$setdate = date("Y-m-d");
$dr_specialist = null;
$appointment_focal_time = date("Y-m-d h:i:s", strtotime("-1 day", time())); /// Hmm! my focal_time means assume start time;
$cur_date_time = date('Y-m-d H:i:s');

$noOntheQueue = 0;

$cur_date_time = date('Y-m-d H:i:s');
$isOnAppoint = false;
$appointment_number = null;
$patientAppoint = null;

$patient_access_type = null;
$appointment_interest = null;
$patient_insurance = null;

/////////////////////// CHECK IF PATIENT HAS OPPENED APPOINTMENT, THEN GET THE ///////////////
/////////// IF THE LOGGED IN USER IS A SPECIALIST
$params = [];

if (isset($_GET['c'])) {
    $other_doc = base64_decode($_GET['c']);
}

if (isset($other_doc) && $other_doc === 'other_doc') {

    $SQL_STRING = " (app_by != ? AND dept = ?) ";
    $params[] = $_SESSION['username'];
    $params[] = $_SESSION['dept_id'];
} elseif (!empty($_SESSION['specialist'])) {

    $SQL_STRING = " (app_by = ? OR app_by = ? OR doctor_id = ?) ";
    $params[] = $_SESSION['username'];
    $params[] = $_SESSION['specialist'];
    $params[] = $_SESSION['id'];
} else {

    $SQL_STRING = " (app_by = ? OR app_by = 'anydoctor' OR referal_doc = ? OR doctor_id = ?) ";
    $params[] = $_SESSION['username'];
    $params[] = $_SESSION['username'];
    $params[] = $_SESSION['id'];
}


$appt_table_tr = '';

// Current datetime
$now = date('Y-m-d H:i:s');

/*
 $SQL_STRING must contain ONLY conditions
 e.g. "doctor_id = ? AND clinic_id = ?"
 and $params must match it in order
*/

// SQL
$sql = "
    SELECT *
    FROM apptm
    WHERE app_expiration_date >= ?
      AND $SQL_STRING
      AND status NOT IN ('discharge', 'cancelled')
      AND (queue_lock = 0 OR re_queue_lock = 0)
      AND queue_time_stamp BETWEEN DATE_SUB(?, INTERVAL 12 HOUR) AND ?
    ORDER BY sn DESC
";

// VERY IMPORTANT: parameter order must match ? order in SQL
$finalParams = array_merge(
    [$now],        // app_expiration_date >= ?
    $params,       // parameters used inside $SQL_STRING
    [$now, $now]   // BETWEEN (lower, upper)
);

// Prepare & execute
$patientOnQueuestmt = $db->prepare($sql);
$patientOnQueuestmt->execute($finalParams);


///echo $noOntheQueue = $patientOnQueuestmt->rowCount();

if ($patientOnQueuestmt->rowCount() > 0) {
    $sn = 0;
    while ($patientAppoint = $patientOnQueuestmt->fetch()) {
        $appointment_number = $patientAppoint['appt_no'];
        $ap_type = $patientAppoint['ap_type'];
        $patient_insurance = $patientAppoint['insurance'];
        $patient_access_type = $patientAppoint['ap_type'];
        $appointment_interest = $patientAppoint['interest'];
        $cr = $patientAppoint['cr'];

        $isOnAppoint = true;

        $stmt = $db->prepare("SELECT serv_group,paystatus FROM patient_ap_services WHERE app_no = ? AND serv_group = 'Consultation'");
        $stmt->execute([$appointment_number]);

        $result = $stmt->fetch();
        $servGroup = $result['serv_group'];
        $paystatus = $result['paystatus'];

        $hasPaid = $paystatus > 0;
        $disabled = $hasPaid ? "" : "disabled";
        $title = $hasPaid ? "View Patient" : "Un-Paid";
        $Color = $hasPaid ? "success" : "danger";
        if ($title == "Un-Paid" and $cr > 0) {
            $disabled = null;
            $title = "Consult On-Credit";
        }
        ///echo 'folakemi';
        if ($servGroup == 'Consultation') {
            ++$noOntheQueue;
            $ap_date_time = $patientAppoint['ap_date_time'];
            $appt_table_tr .= '
                <tr>
                    <td>' . ++$sn . '</td>
                    <td>' . $patientAppoint['hospital_no'] . '</td>
                    <td>' . $patientAppoint['patient_name'] . '</td>
                    <td>' . $patientAppoint['services_name'] . '</td>
                    <td>' . date('d, M', strtotime("" . $ap_date_time)) . ' (' . dateDifference_format($patientAppoint["ap_date_time"]) . ' ago)</td>
                    <td class="text-center"><a href = "patient.php?c=' . $source_cdd . '&hosp_no=' . $patientAppoint['hospital_no'] . '&mgt&app=' . $patientAppoint['appt_no'] . '&new_" class="btn btn-' . $Color . ' "' . $disabled . ' >' . $title . '</a></td>
                </tr>
             ';
        }

        ///echo $appt_table_tr;


    }
}
?>

<div class="row">
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-success pull-right">Today</span>
                <h5>Patient(s) On Queue</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">

                    <?= $noOntheQueue; ?>

                </h1>
                <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                <small>Total Patients</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-success pull-right">Today</span>
                <h5>Patient(s) Seen</h5>
            </div>
            <div class="ibox-content">

                <div class="row">
                    <div class="col-md-6">
                        <h1 class="no-margins" id="seen_today_count">0
                        </h1>
                        <small> Today:</small>

                    </div>
                    <div class="col-md-6">

                        <h1 class="no-margins" id="patient_seen_thismonth_count">0</h1>
                        <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                        <small> Month:</small>

                    </div>


                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-primary pull-right">Today: <span id="admitted_today_count">0</span></span>
                <h5>Doctor's Admission Counter</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-6">

                        <h1 class="no-margins" id="no_on_admission_count">0
                        </h1>
                        <small> Admitted</small>

                    </div>
                    <div class="col-md-6">

                        <h1 class="no-margins" id="discharged_today_count">0</h1>
                        <small> Discharged</small>

                    </div>


                </div>


            </div>
        </div>
    </div>

    <?php
    $exp_today = 0;
    $odr_today = 0;

    ?>

    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-info pull-right">Counts</span>
                <h5>Procedure(s)</h5>
            </div>
            <div class="ibox-content">
                <table width="100%">
                    <tr>
                        <td>
                            <h1 class="no-margins" id="total_procedure_request">0</h1>
                            <small>Requested</small>
                        </td>
                        <td>
                            <h1 class="no-margins" id="total_procedure_performed">0</h1>
                            <small>Performed </small>
                        </td>
                        <td>
                            <h1 class="no-margins" id="total_procedure_pending">0</h1>
                            <small>Notes Pending </small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>



</div>

<?php
$current_tab = 'queue';
if (isset($_POST['patient_seen_report_btn'])) {
    $current_tab = 'patient_seen_report';
}

if (isset($_POST['__patient__seen__btn__'])) {
    $current_tab = 'patient_seen';
}
if (isset($_GET['adm'])) {
    $current_tab = 'adm';
}



?>

<div class="row">

    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">

                <h3>DOCTOR'S DASHBOARD</h3>
                <div id='count_chats'></div>
            </div>
            <div class="ibox-content">
                <div class="row">


                    <div class="tabs-container">
                        <ul class="nav nav-tabs">
                            <li class="<?php echo ($current_tab == 'queue' ? 'active' : ''); ?>"><a data-toggle="tab" href="#tab-1" style="color: black; font-size:15px;"><i class="fa fa-group"></i>Queue</a></li>
                            <?php if ($_SESSION['view_patient_on_adm'] == 0 && $_SESSION['dispensory'] != 1) { ?>
                                <li class="<?= ($current_tab == 'adm' ? "active" : ""); ?>"><a data-toggle="tab" href="#med-hx-tab" style="color: black; font-size:15px;"><i class="fa fa-bed"></i>On-Admission</a></li>
                            <?php } ?>
                            <li class="<?php echo ($current_tab == 'patient_seen' ? 'active' : ''); ?>"><a data-toggle="tab" href="#tab-2" style="color: black; font-size:15px;"><i class="fa fa-folder-open-o"></i><b style="color:coral; ">Patients Seen</b></a></li>

                            <li class="<?php echo ($current_tab == 'patient_seen_report' ? 'active' : ''); ?>"><a data-toggle="tab" href="#patient-seen-report-tab" style="color: black; font-size:15px;"><i class="fa fa-list" style="color: red;;"></i>Seen By Service</a></li>

                            <li class="<?php echo ($current_tab == 'patient_manage' ? 'active' : ''); ?>" onClick="see_patient_manage('<?= $_SESSION['fullname']; ?>')"><a data-toggle="tab" href="#tab-patient_manage" style="color: black; font-size:15px;"><i class="fa fa-folder-open-o"></i><b style="color:blue">VIP & Patients Manage By Me</b></a></li>
                            <li class="<?php echo ($current_tab == 'procedure_tab' ? 'active' : ''); ?>">
                                <a data-toggle="tab" href="#tab-procedure_tab" style="color: black; font-size:15px;"><i class="fa fa-folder-open-o"></i>Procedure Statistics</a>
                            </li>


                        </ul>
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane <?php echo ($current_tab == 'queue' ? 'active' : ''); ?>">
                                <div class="panel-body">
                                    <?php
                                    $service_options = '';
                                    $query = "
                         SELECT sn, item_service 
                         FROM prices_table 
                         WHERE status = '0' 
                           AND price_table IN ('Consultation', 'Medical Services') 
                         ORDER BY item_service";

                                    $stmt2 = $db->query($query);
                                    if ($stmt2) {
                                        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                                            $sn = htmlspecialchars($row['sn'], ENT_QUOTES);
                                            $item = htmlspecialchars($row['item_service'], ENT_QUOTES);
                                            $service_options .= "<option value=\"$sn\">$item</option>";
                                        }
                                    }

                                    include('_patient_on_queue_table.php');
                                    ?>
                                </div>
                            </div>

                            <div id="med-hx-tab" class="tab-pane <?= ($current_tab == 'adm' ? "active" : ""); ?>">
                                <div class="panel-body">
                                    <?php
                                    include_once('../nursing/_patients_on_admission_list.php');
                                    ?>

                                </div>
                            </div>

                            <div id="tab-2" class="tab-pane <?php echo ($current_tab == 'patient_seen' ? 'active' : ''); ?>">
                                <div class="panel-body">
                                    <?php
                                    include('_patients_seen_list.php');
                                    ?>
                                </div>
                            </div>





                            <div id="patient-seen-report-tab" class="tab-pane <?php echo ($current_tab == 'patient_seen_report' ? 'active' : ''); ?>">
                                <div class="panel-body">

                                    <?php
                                    /// $coming_from = 'personal_doc';
                                    include('_patients_seen_report.php');
                                    ?>

                                </div>
                            </div>



                            <div id="tab-patient_manage" class="tab-pane <?php echo ($current_tab == 'patient_manage' ? 'active' : ''); ?>">
                                <div class="panel-body">


                                    <H2 id="wait_patient_mgt" style="color: red; ">Please Wait Loading Patient(s) ... </H2>
                                    <div id="wait_patient_mgt2"></div>
                                    <?php
                                    ///include('_patients_seen_list.php');
                                    ?>
                                </div>
                            </div>


                            <div id="tab-procedure_tab" class="tab-pane <?php echo ($current_tab == 'procedure_tab' ? 'active' : ''); ?>">
                                <div class="panel-body">


                                    <?php include_once('../inc/procedure_stats.php');
                                    ?>

                                </div>
                            </div>

                        </div>


                    </div>

                    <hr>



                </div>
            </div>
        </div>
    </div>

    <div class="modal inmodal fade" id="patient_pharm_doctor_chats_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Messages from Pharmacy</h4>
                </div>
                <div id="patient_pharm_doctor_chats_modal_body">
                </div>
                <div>
                    <hr>
                    <p class="text-center"><button class="btn btn-danger" class="close" data-dismiss="modal" aria-hidden="true"> Close </button></p>
                </div>
            </div>
        </div>
    </div>



    <?php
    include_once('../inc/procedure_attach_to_file.php');
    include_once('../inc/pharm_doctor_chats.php');
    ?>


    <script>
        $(document).ready(function() {
            $.ajax({
                url: "_dashboard_counts.php",
                method: "POST",
                data: {
                    get_dashboard_counts: 'get_dashboard_counts'
                },
                success: function(response) {

                    if (response.status == 200) {
                        $('#seen_today_count').text(response.seen_today_count);
                        $('#patient_seen_thismonth_count').text(response.patient_seen_thismonth_count);
                        $('#admitted_today_count').text(response.admitted_today_count);
                        $('#no_on_admission_count').text(response.no_on_admission_count);
                        $('#discharged_today_count').text(response.discharged_today_count);
                        $('#total_procedure_request').text(response.total_procedure_request);
                        $('#total_procedure_performed').text(response.total_procedure_performed);
                        $('#total_procedure_pending').text(response.total_procedure_pending);
                    }
                },
                error: function(err) {
                    console.log(err)
                }
            });

        })
    </script>

    <script>
        check_for_chat();


        function check_for_chat() {
            var patient_id; // Replace with the actual patient ID or chat ID


            $.ajax({
                url: "pharm_doctor_chats_patient.php",
                method: "POST",
                data: {
                    patient_id: patient_id
                },
                success: function(data) {

                    ///alert(data);
                    if (data) { // Check if the response is not empty
                        try {
                            var jsonData = JSON.parse(data);
                            if (jsonData["message0"] > 0) {
                                document.getElementById('count_chats').innerHTML = '<h5 class="pull-right"><a href="javascript:void(0)" class="text-danger" onclick="show_chats()"><i>New Pharmacy Chats Message (' + jsonData["message0"] + ')</i></a>';
                            }
                            if (jsonData["message1"] > 0) {
                                document.getElementById('count_chats').innerHTML = '<h5 class="pull-right"><a href="javascript:void(0)" class="text-danger" onclick="show_chats()">View Pharmacy Chats</a>';
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response:", e);
                        }
                    } else {
                        console.error("Empty response from server");
                    }

                }
            });
        }

        function show_chats() {
            var patient_id;
            $.ajax({
                url: "../inc/pharm_doctor_chats_show.php",
                method: "POST",
                data: {
                    patient_id: patient_id
                },
                success: function(data) {

                    if (data) { // Check if the response is not empty
                        $('#patient_pharm_doctor_chats_modal_body').html(data);
                        $('#patient_pharm_doctor_chats_modal').modal('show');
                    } else {
                        console.error("Empty response from server");
                    }
                }
            });
        }

        function reply_sn_notes(sn) {

            var reply_text = document.getElementById('reply_text').value;
            $.ajax({
                url: "../inc/pharm_doctor_chats.php",
                method: "POST",
                data: {
                    sn_reply: sn,
                    reply_text: reply_text
                },
                success: function(response) {
                    alert(response);
                    check_for_chat();
                },
                error: function(err) {
                    console.log(err)
                }
            });


        }
    </script>