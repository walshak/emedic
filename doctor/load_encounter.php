<?php
session_start();
include("../Connections/Conn.php"); // $db = PDO

if (!isset($_POST['visit_hospital_no'])) {
    exit("<div class='alert alert-danger'>No hospital number provided</div>");
}

$hos_no = $_POST['visit_hospital_no'];
$app_no_filter = isset($_POST['app_no']) && $_POST['app_no'] !== "" ? $_POST['app_no'] : null;
$status = isset($_POST['status']) ? strtolower($_POST['status']) : '';

$statusLabels = [
    'checkin'   => 'Active',
    'discharge' => 'Discharged',
    'pending'   => 'Pending',
    'cancelled' => 'Cancelled'
];
$displayStatus = isset($statusLabels[$status]) ? $statusLabels[$status] : ucfirst($status);

try {
    // 1️⃣ Fetch visits    ////  AND notes_type = 'C'
    $queryVisits = "
        SELECT DISTINCT app_no, hospital_no, date_entry, prepared_by
        FROM notes
        WHERE hospital_no = :hos_no AND status = '1'";
    if ($app_no_filter) {
        $queryVisits .= " AND app_no = :app_no";
    }
    $queryVisits .= " ORDER BY date_entry DESC";

    $stmt = $db->prepare($queryVisits);
    $params = [':hos_no' => $hos_no];
    if ($app_no_filter) $params[':app_no'] = $app_no_filter;
    $stmt->execute($params);
    $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$visits) {
        //echo '<div class="alert alert-info"><strong>No Note(s) Found</strong></div>';
    }

    $visitKeys = [];
    foreach ($visits as $v) {
        $visitKeys[$v['app_no']] = $v;
    }

    $notesGrouped = [];
    $servicesGrouped = [];
    $remarksGrouped = [];

    if (!empty($visitKeys)) {
        $appNos = array_keys($visitKeys);
        $placeholders = implode(',', array_fill(0, count($appNos), '?'));
        $params2 = array_merge([$hos_no], $appNos);

        // 2️⃣ Fetch notes
        $queryNotes = "
            SELECT app_no, notes_type, notes, prepared_by,date_entry
            FROM notes
            WHERE hospital_no = ?
              AND status = '1'
              AND app_no IN ($placeholders)";
        $stmt2 = $db->prepare($queryNotes);
        $stmt2->execute($params2);
        $notes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // valid types and friendly labels
        $validNoteTypes = [
            'C',
            'D',
            'PHY',
            'note',
            'ward_round',
            'plan',
            'pre_opt_notes',
            'REQ_REMINDER',
            'post_opt_notes',
            'treatment',
            'Pharm',
            'Antenatal',
            'dialysis',
            'serv_review'
        ];

        $noteLabels = [
            'C'             => 'Complaints',
            'D'             => 'Diagnosis',
            'PHY'           => 'Physical Examination',
            'note'          => 'Notes',
            'ward_round'    => 'Ward Round',
            'plan'          => 'Plan',
            'pre_opt_notes' => 'Pre-Operative Notes',
            'REQ_REMINDER'  => 'Request Reminder',
            'post_opt_notes' => 'Post-Operative Notes',
            'treatment'     => 'Treatment',
            'Pharm'         => 'Pharmacy Notes',
            'Antenatal'     => 'Antenatal Notes',
            'dialysis'      => 'Dialysis Notes',
            'serv_review'   => 'Service Review'
        ];

        // canonical map for case-insensitive matching
        $canonicalMap = [];
        foreach ($validNoteTypes as $t) {
            $canonicalMap[strtolower($t)] = $t;
        }

        foreach ($notes as $n) {
            $key = $n['app_no'];
            $ntRaw = isset($n['notes_type']) ? $n['notes_type'] : '';
            $ntLower = strtolower($ntRaw);
            $ntKey = isset($canonicalMap[$ntLower]) ? $canonicalMap[$ntLower] : null;

            // Initialize all note types for this appointment if missing
            if (!isset($notesGrouped[$key])) {
                // prefer prepared_by from the visit if available
                $preparedFromVisit = isset($visitKeys[$key]['prepared_by']) ? $visitKeys[$key]['prepared_by'] : null;
                $notesGrouped[$key] = array_fill_keys($validNoteTypes, '');
                $notesGrouped[$key]['doctor'] = $preparedFromVisit ? $preparedFromVisit : $n['prepared_by'];
            }

            if ($ntKey) {
                // sanitize and keep line breaks
                $safeNote = nl2br(
                    $n['notes'] .
                        "<b><small><em>By: " . htmlspecialchars($n['prepared_by']) .
                        " on " . date('d,M Y h:i:s a', strtotime($n['date_entry'])) .
                        "</em></small></b><br>"
                );
                $notesGrouped[$key][$ntKey] .= $safeNote . "<br>";
                // if doctor not set yet and prepared_by present, set it
                if (empty($notesGrouped[$key]['doctor']) && !empty($n['prepared_by'])) {
                    $notesGrouped[$key]['doctor'] = $n['prepared_by'];
                }
            } else {
                // if note type not recognized, append it under 'note' if exists, else create fallback
                $fallback = 'note';
                if (!isset($notesGrouped[$key][$fallback])) {
                    $notesGrouped[$key][$fallback] = '';
                }
                $notesGrouped[$key][$fallback] .= nl2br(($n['notes']));
            }
        }

        // 3️⃣ Fetch services (unchanged)
        $queryServices = "
            SELECT app_no, serv_group, item_services, drug_status, remarks, paystatus
            FROM patient_ap_services
            WHERE hospital_no = ?
              AND serv_group IN ('Pharmacy','Laboratory','Radiology')
              AND app_no IN ($placeholders)";
        $stmt3 = $db->prepare($queryServices);
        $stmt3->execute($params2);
        $services = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        foreach ($services as $s) {
            $key = $s['app_no'];
            if (!isset($servicesGrouped[$key])) {
                $servicesGrouped[$key] = ['Pharmacy' => [], 'Laboratory' => [], 'Radiology' => []];
            }
            if ($s['serv_group'] == 'Pharmacy') {
                $item = ($s['item_services']);
                $item .= ($s['drug_status'] == 1) ? " <span style='color:green'>(Dispensed)</span>" : " <span style='color:red'>(Not Dispensed)</span>";
                if (!empty($s['remarks'])) $item .= " <br><small><em>Prescription: " . ($s['remarks']) . "</em></small>";
                $servicesGrouped[$key]['Pharmacy'][] = $item;
            } else { // Laboratory or Radiology
                $item = ($s['item_services']);
                $item .= ($s['paystatus'] == 1) ? " <span style='color:blue'>(Paid)</span>" : " <span style='color:red'>(Not Paid)</span>";
                $servicesGrouped[$key][$s['serv_group']][] = $item;
            }
        }

        // 4️⃣ Fetch remarks (unchanged)
        $queryRemarks = "
            SELECT app_no, complain, cat_type
            FROM c_d_remarks
            WHERE hospital_no = ?
              AND app_no IN ($placeholders)
              AND status = '1'
            ORDER BY date_entry ASC";
        $stmt4 = $db->prepare($queryRemarks);
        $stmt4->execute($params2);
        $remarks = $stmt4->fetchAll(PDO::FETCH_ASSOC);

        foreach ($remarks as $r) {
            $key = $r['app_no'];
            if (!isset($remarksGrouped[$key])) $remarksGrouped[$key] = ['pmh' => '', 'SH' => '', 'R' => ''];
            if ($r['cat_type'] == 'pmh') $remarksGrouped[$key]['pmh'] .= ($r['complain']) . "<br>";
            elseif ($r['cat_type'] == 'SH') $remarksGrouped[$key]['SH'] .= ($r['complain']) . "<br>";
            elseif ($r['cat_type'] == 'R') $remarksGrouped[$key]['R'] .= ($r['complain']) . "<br>";
        }
    } else {
        // No visits found, nothing to display
        echo '<div class="alert alert-info"><strong>No notes were found for the current appointment. Click the button to see previous notes.</strong></div>';
        exit;
    }

    // 5️⃣ Loop through visits or fallback
    $appNosToDisplay = !empty($visitKeys) ? array_keys($visitKeys) : [$app_no_filter];

    foreach ($appNosToDisplay as $app_no) {
        if (!$app_no) continue;

        $visit = isset($visitKeys[$app_no]) ? $visitKeys[$app_no] : null;
        $date_entry = $visit['date_entry'];

        // safe notes access
        $notesForApp = isset($notesGrouped[$app_no]) ? $notesGrouped[$app_no] : array_fill_keys($validNoteTypes, '');
        $dr = isset($notesForApp['doctor']) ? $notesForApp['doctor'] : (isset($visit['prepared_by']) ? $visit['prepared_by'] : '');
        $C = isset($notesForApp['C']) ? $notesForApp['C'] : '';
        $D = isset($notesForApp['D']) ? $notesForApp['D'] : '';
        $medications = isset($servicesGrouped[$app_no]['Pharmacy']) ? implode("<br>", $servicesGrouped[$app_no]['Pharmacy']) : '';
        $labsArr = [];
        if (isset($servicesGrouped[$app_no]['Laboratory'])) $labsArr = array_merge($labsArr, $servicesGrouped[$app_no]['Laboratory']);
        if (isset($servicesGrouped[$app_no]['Radiology']))  $labsArr = array_merge($labsArr, $servicesGrouped[$app_no]['Radiology']);
        $labs = count($labsArr) > 0 ? implode("<br>", $labsArr) : '';

        if (isset($remarksGrouped[$app_no])) {
            $remarksForApp = $remarksGrouped[$app_no];
        } else {
            $remarksForApp = ['pmh' => '', 'SH' => '', 'R' => ''];
        }

        // 6️⃣ Fetch Admission
        $stmt = $db->prepare("
            SELECT app_no, doc_incharge, discharge_name, room_bed, dept_id, reason_adm, date_admit, date_discharge, discharge_note, adm_status
            FROM admission
            WHERE app_no = :app_no
              AND hospital_no = :hos_no
            ORDER BY sn DESC
            LIMIT 1
        ");
        $stmt->execute([':app_no' => $app_no, ':hos_no' => $hos_no]);
        $admission = $stmt->fetch(PDO::FETCH_ASSOC);

        // 7️⃣ Fetch Dialysis
        $stmtDia = $db->prepare("
            SELECT hospital_no, app_no, request_type, request_by, request_note, request_date
            FROM dialysis
            WHERE app_no = :app_no
            ORDER BY request_date DESC
            LIMIT 1
        ");
        $stmtDia->execute([':app_no' => $app_no]);
        $dialysis = $stmtDia->fetch(PDO::FETCH_ASSOC);

        // 8️⃣ Fetch Procedures
        $stmtProc = $db->prepare("
            SELECT hospital_no, app_no, procedures, prepared_by, consultant_name, date_entry
            FROM procedures
            WHERE app_no = :app_no
            ORDER BY date_entry DESC
        ");
        $stmtProc->execute([':app_no' => $app_no]);
        $procedures = $stmtProc->fetchAll(PDO::FETCH_ASSOC); ?>
        <h2><i>APPOINTMENT STATUS:</i> <?php echo ($displayStatus); ?></h2>

        <?php if ($admission) {
            $admStatusMap = [3 => "On Admission", 4 => "Discharged"];
            $admStatus = isset($admStatusMap[$admission['adm_status']]) ? $admStatusMap[$admission['adm_status']] : "Unknown"; ?>
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white">
                    <h2>Admission Details (Appointment <?php echo ($app_no); ?>)</h2>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped" style="font-size:18px;" width="50%">
                        <tr>
                            <th>Doctor in Charge</th>
                            <td><?php echo ($admission['doc_incharge']); ?></td>
                            <th>Status</th>
                            <td><span class="badge <?php echo ($admission['adm_status'] == 3) ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $admStatus; ?></span></td>
                        </tr>
                        <tr>
                            <th>Room/Bed</th>
                            <td><?php echo ($admission['room_bed']); ?></td>
                            <th>Reason for Admission</th>
                            <td><?php echo ($admission['reason_adm']); ?></td>
                        </tr>
                        <tr>
                            <th>Date of Admission</th>
                            <td><?php echo $admission['date_admit'] ? date('d M Y H:i', strtotime($admission['date_admit'])) : "-"; ?></td>
                            <th>Date of Discharge</th>
                            <td><?php echo $admission['date_discharge'] ? date('d M Y H:i', strtotime($admission['date_discharge'])) : "-"; ?></td>
                        </tr>
                        <tr>
                            <th>Discharge Note</th>
                            <td><?php echo nl2br(($admission['discharge_note'])); ?></td>
                            <th>Discharge By</th>
                            <td><?php echo ($admission['discharge_name']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php } ?>

        <?php if ($dialysis) { ?>
            <div class="card border-info mb-3">
                <div class="card-header bg-info text-white">
                    <h2>Dialysis Request (Appointment <?php echo ($app_no); ?>)</h2>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped" style="font-size:18px;" width="50%">
                        <tr>
                            <th>Request Type</th>
                            <td><?php echo ($dialysis['request_type']); ?></td>
                            <th>Request By</th>
                            <td><?php echo ($dialysis['request_by']); ?></td>
                        </tr>
                        <tr>
                            <th>Request Note</th>
                            <td><?php echo nl2br(($dialysis['request_note'])); ?></td>
                            <th>Request Date</th>
                            <td><?php echo $dialysis['request_date'] ? date('d M Y H:i', strtotime($dialysis['request_date'])) : "-"; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php } ?>

        <?php if ($procedures) { ?>
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning text-dark">
                    <h2>Procedure Details (Appointment <?php echo ($app_no); ?>)</h2>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size:18px;">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Procedure</th>
                                <th>Prepared By</th>
                                <th>Consultant Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($procedures as $proc) { ?>
                                <tr>
                                    <td><?php echo $proc['date_entry'] ? date('d M Y H:i', strtotime($proc['date_entry'])) : "-"; ?></td>
                                    <td><?php echo ($proc['procedures']); ?></td>
                                    <td><?php echo ($proc['prepared_by']); ?></td>
                                    <td><?php echo ($proc['consultant_name']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>

        <table class="table table-striped encounter-table" style="font-size:18px;">
            <tbody>
                <tr>

                    <td class="col-complaints">
                        <?php echo $C; ?>
                        <?php if ($dr) echo "<br><i><b>Doctor's Name: </b></i>" . ($dr);
                        echo $date_entry ? '<br>' . date('d M,Y h:i a', strtotime($date_entry)) : '-';
                        ?>

                    </td>
                    <td class="col-diagnosis">
                        <?php if ($D) echo "<strong>Diagnosis:</strong><br>" . $D; ?>
                    </td>
                    <td class="col-plan">
                        <?php
                        // Show all note types except C and D, using friendly labels
                        foreach ($validNoteTypes as $type) {
                            if ($type === 'C' || $type === 'D') continue;
                            if (!empty($notesForApp[$type])) {
                                $label = isset($noteLabels[$type]) ? $noteLabels[$type] : ucwords(str_replace('_', ' ', $type));
                                echo "<strong>{$label}:</strong><br>" . $notesForApp[$type] . "<hr>";
                            }
                        }
                        if ($medications) echo "<strong>Medications:</strong><br>$medications<hr>";
                        if ($labs) echo "<strong>Investigations:</strong><br>$labs<hr>";
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="row" style="font-size:18px;">
            <div class="col-sm-6"><?php if ($remarksForApp['SH']) { ?><h3>Social History:</h3><?php echo $remarksForApp['SH']; ?>
                    <hr><?php } ?>
            </div>
            <div class="col-sm-3"><?php if ($remarksForApp['pmh']) { ?><h3>Past Medical History:</h3><?php echo $remarksForApp['pmh']; ?>
                    <hr><?php } ?>
            </div>
            <div class="col-sm-3"><?php if ($remarksForApp['R']) { ?><h3>Review of Systems:</h3><?php echo $remarksForApp['R']; ?>
                    <hr><?php } ?>
            </div>
        </div>

        <style>
            .encounter-table {
                table-layout: fixed;
                width: 100%;
            }

            .encounter-table td {
                word-wrap: break-word;
                vertical-align: top;
                padding: 5px;
            }

            .encounter-table .col-date {
                width: 7%;
            }

            .encounter-table .col-complaints {
                width: 41%;
            }

            .encounter-table .col-diagnosis {
                width: 27%;
            }

            .encounter-table .col-plan {
                width: 25%;
            }
        </style>

<?php
    }
} catch (Exception $e) {
    echo "<div style='color:red;'><strong>Error:</strong> " . ($e->getMessage()) . "</div>";
}
?>