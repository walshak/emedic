<?php
session_start();
include("../Connections/Conn.php"); // $db = PDO

if (!isset($_POST['visit_hospital_no'])) {
    exit("<div class='alert alert-danger'>No hospital number provided</div>");
}

$hos_no = $_POST['visit_hospital_no'];

try {
    // 📌 Summary stats
    $querySummary = "
        SELECT COUNT(DISTINCT appt_no) AS total_visits,
               MIN(ap_date_time) AS first_visit,
               MAX(ap_date_time) AS last_visit
        FROM apptm
        WHERE hospital_no = :hos_no";
    $stmtSummary = $db->prepare($querySummary);
    $stmtSummary->execute([':hos_no' => $hos_no]);
    $summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

    $totalVisits = $summary['total_visits'] ?: 0;
    $firstVisit  = $summary['first_visit'] ? date('d M, Y', strtotime($summary['first_visit'])) : '-';
    $lastVisit   = $summary['last_visit'] ? date('d M, Y', strtotime($summary['last_visit'])) : '-';

    // 📌 Fetch appointments
    $queryAppNos = "
        SELECT a.appt_no,
               a.ap_date_time,
               a.patient_name,
               a.doctor_id,
               a.dept,
               a.services_name,
               a.status,
               u.fullname AS doctor_name,
               d.department AS department_name
        FROM apptm a
        LEFT JOIN admin_users u ON u.id = a.doctor_id
        LEFT JOIN department d ON d.sn = a.dept
        WHERE a.hospital_no = :hos_no
        ORDER BY a.ap_date_time DESC";
    $stmtAppNos = $db->prepare($queryAppNos);
    $stmtAppNos->execute([':hos_no' => $hos_no]);
    $appNos = $stmtAppNos->fetchAll(PDO::FETCH_ASSOC);

    if (!$appNos) {
        echo '<div class="alert alert-info"><strong>No Existing Appointment(s)</strong></div>';
        exit;
    }

    $appList = array_column($appNos, 'appt_no');
?>

    <!-- 📌 Summary -->
    <div align="center" class=" alert alert-secondary" style="font-size: larger;">
        <strong style="font-size: 18px; color:blue; ">TOTAL APPOINTMENTS: <?php echo $totalVisits; ?></strong> |
        <strong style="font-size: 18px; color:brown; ">FIRST APPOINTMENT: <?php echo $firstVisit; ?> </strong> |
        <strong style="font-size: 18px; color:green; ">LAST APPOINTMENT: <?php echo $lastVisit; ?></strong>
        <BR>
        <button id="firstBtn" class="btn btn-lg btn-secondary" style="font-size: 18px; color:black; ">First Visit</button>
        <button id="prevBtn" class="btn btn-lg btn-primary" style="font-size: 18px; color:black; ">Next Visit</button>
        <button id="nextBtn" class="btn btn-lg btn-primary" style="font-size: 18px; color:black; ">Previous Visit</button>
        <button id="lastBtn" class="btn btn-lg btn-secondary" style="font-size: 18px; color:black; ">Last Visit</button>

        <br> <br>
        <table>
            <TR>
                <TD><label style="color: brown; ">Select Appointment to Jump to Visit: </label> &nbsp;</TD>
                <TD>
                    <select id="appointmentSelect" class="form-control" style="font-weight:bold;">
                        <option value="">-- Select Appointment --</option>
                        <?php foreach ($appNos as $row): ?>
                            <option value="<?php echo htmlspecialchars($row['appt_no']); ?>"
                                data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                style="font-weight:bold;">
                                <?php echo htmlspecialchars($row['appt_no']); ?> |
                                <?php echo htmlspecialchars($row['services_name']); ?> |
                                <?php echo htmlspecialchars($row['doctor_name']); ?> |
                                <?php echo htmlspecialchars($row['department_name']); ?>
                                (<?php echo date('d M,y H:i', strtotime($row['ap_date_time'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </TD>
            </TR>
        </table>

        <div id="message_err"></div>
    </div>

    <div id="encounterContainer">
        <!-- Appointment details will load here -->
    </div>

    <script>
        // Appointments list
        var appList = <?php echo json_encode(array_map('strval', $appList)); ?>;
        var appData = <?php echo json_encode($appNos); ?>;
        var currentIndex = -1;
        var hosNo = "<?php echo $hos_no; ?>";


        function loadEncounter(appNo) {
            appNo = String(appNo);

            // Find appointment row
            var row = appData.find(r => String(r.appt_no) === appNo);
            var status = row ? row.status : "";
            $("#message_err").html('<div class="alert alert-danger"><h3>Please Wait ... </h3></div>');
            $.post("load_encounter.php", {
                visit_hospital_no: hosNo,
                app_no: appNo,
                status: status
            }, function(data) {
                $("#encounterContainer").html(data);
                currentIndex = appList.indexOf(appNo);
                $("#appointmentSelect").val(appNo);
                $("#message_err").html('');

            });
        }

        // Dropdown change
        $("#appointmentSelect").change(function() {
            var appNo = $(this).val();
            var status = $(this).find(':selected').data('status') || "";
            $("#message_err").html('<div class="alert alert-danger"><h3>Please Wait ... </h3></div>');

            if (appNo) {
                $.post("load_encounter.php", {
                    visit_hospital_no: hosNo,
                    app_no: appNo,
                    status: status
                }, function(data) {
                    $("#encounterContainer").html(data);
                    currentIndex = appList.indexOf(String(appNo));
                    $("#message_err").html('');

                });
            } else {
                $("#encounterContainer").html("");

                currentIndex = -1;
            }
        });

        // Navigation
        $("#nextBtn").click(function() {
            $("#message_err").html('');
            if (currentIndex >= 0 && currentIndex < appList.length - 1) {
                var nextApp = appList[currentIndex + 1];
                loadEncounter(nextApp);
            } else {
                $("#message_err").html('<div class="alert alert-danger"><h3>You are already at the last appointment.</h3></div>');
            }
        });

        $("#prevBtn").click(function() {
            $("#message_err").html('');
            if (currentIndex > 0) {
                var prevApp = appList[currentIndex - 1];
                loadEncounter(prevApp);
            } else {
                $("#message_err").html('<div class="alert alert-danger"><h3>You are already at the first appointment.</h3></div>');
            }
        });

        $("#firstBtn").click(function() {
            $("#message_err").html('');
            if (appList.length > 0) {
                if (currentIndex === appList.length - 1) {
                    $("#message_err").html('<div class="alert alert-warning"><h3>You are already at the first appointment.</h3></div>');
                } else {
                    loadEncounter(appList[appList.length - 1]); // oldest
                }
            }
        });

        $("#lastBtn").click(function() {
            $("#message_err").html('');
            if (appList.length > 0) {
                if (currentIndex === 0) {
                    $("#message_err").html('<div class="alert alert-warning"><h3>You are already at the last appointment.</h3></div>');
                } else {
                    loadEncounter(appList[0]); // newest
                }
            }
        });



        // Auto-load last visit
        $(document).ready(function() {
            if (appList.length > 0) {
                loadEncounter(appList[0]); // newest
            }
        });
    </script>

<?php
} catch (Exception $e) {
    echo "<div style='color:red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>