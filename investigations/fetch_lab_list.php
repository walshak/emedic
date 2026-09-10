<?php
session_start();
include("../Connections/Conn.php");
require_once __DIR__ . '/../inc/lis/LisService.php';

try {
    $section = isset($_SESSION['section']) ? $_SESSION['section'] : '';
    $username = $_SESSION['uname'] ?? $_SESSION['fullname'] ?? 'system_user';
    $lisEnabled = LisDriverFactory::isLisEnabled($db);

    // 1. Fetch Pending Requests
    $query = "
        SELECT 
            lm.labrequest_no,
            lm.patient AS hospital_no,
            lm.test_id,
            lm.test_name,
            lm.section,
            lm.request_date,
            lm.request_by,
            admission.hospital_no AS admitted_patient,
            lo.clinos_order_id,
            lo.status AS lis_status
        FROM lab_manage lm
        LEFT JOIN admission 
            ON lm.patient = admission.hospital_no 
            AND admission.adm_status = 3
        LEFT JOIN lis_orders lo 
            ON lo.labrequest_no = lm.labrequest_no
        WHERE (lm.section = ? OR ? = '')
        AND lm.data_capture_status = 'queue'
        AND (
            (admission.hospital_no IS NULL 
                AND lm.request_date >= NOW() - INTERVAL 48 HOUR)
            OR
            (admission.hospital_no IS NOT NULL 
                AND lm.request_date >= NOW() - INTERVAL 48 HOUR)
        )
        ORDER BY lm.request_date DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute(array($section, $section));

    $queueRows = '';
    $sn = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sn++;
        $labrequestNo = $row['labrequest_no'];
        $hospital_no  = $row['hospital_no'];
        $test_name    = $row['test_name'];
        $test_id      = $row['test_id'];
        $request_by   = $row['request_by'];
        $request_date = $row['request_date'];
        $isAdmitted   = $row['admitted_patient'];
        $lisStatus    = $row['lis_status'];

        $badge = $isAdmitted ? '<span class="badge badge-danger">🏥 Inpatient</span>' : '<span class="badge badge-info">🚶 Outpatient</span>';
        $formatted_time = date('d M Y h:i A', strtotime($request_date));

        $lisBadge = '';
        if ($lisEnabled) {
            if ($lisStatus === 'sent' || $lisStatus === 'validated') {
                $lisBadge = '<br><span class="label label-primary" style="font-size:10px;"><i class="fa fa-paper-plane"></i> Sent to LIS</span>';
            } else {
                $lisBadge = '<br><button type="button" onclick="retryLisDispatch(\'' . htmlspecialchars($labrequestNo) . '\', \'' . htmlspecialchars($test_id) . '\', \'' . htmlspecialchars($test_name) . '\', \'' . htmlspecialchars($hospital_no) . '\')" class="btn btn-xs btn-outline btn-success" style="margin-top:3px;"><i class="fa fa-send"></i> Send to LIS</button>';
            }
        }

        $queueRows .= '
        <tr>
            <td>' . $sn . '</td>
            <td><strong>' . htmlspecialchars($hospital_no) . '</strong></td>
            <td>' . htmlspecialchars($test_name) . $lisBadge . '</td>
            <td>' . $badge . '</td>
            <td>' . htmlspecialchars($request_by) . '</td>
            <td>' . $formatted_time . '</td>
            <td>
                <a href="mgt.php?hosp_no=' . urlencode($hospital_no) . '" class="btn btn-success btn-xs">Open</a>
            </td>
        </tr>';
    }

    if ($sn == 0) {
        $queueRows = '<tr><td colspan="7" class="text-center text-muted">No pending lab/radiology requests found.</td></tr>';
    }

    // 2. Fetch LIS Results (Unseen & Recent 48 hrs)
    $lisResultsRows = '';
    $unseenCount = 0;
    if ($lisEnabled) {
        $unseenItems = LisService::getUnseenNotifications($db, $username, 48);
        $unseenCount = count($unseenItems);

        $resStmt = $db->prepare("
            SELECT lm.labrequest_no, lm.patient AS hospital_no, lm.test_name, lm.section, lm.result_date, lm.approved_by, lm.entered_by, lm.data_capture_status, lo.clinos_order_id,
                   (s.id IS NOT NULL) AS is_seen
            FROM lab_manage lm
            INNER JOIN lis_orders lo ON lo.labrequest_no = lm.labrequest_no
            LEFT JOIN lis_notifications_seen s 
                ON s.labrequest_no = lm.labrequest_no 
                AND s.username = :username 
                AND s.event_type = 'result_received'
            WHERE lm.data_capture_status IN ('result', 'approve')
              AND lm.result_date >= NOW() - INTERVAL 48 HOUR
            ORDER BY lm.result_date DESC
            LIMIT 50
        ");
        $resStmt->execute([':username' => $username]);
        $resSn = 0;

        while ($rRow = $resStmt->fetch(PDO::FETCH_ASSOC)) {
            $resSn++;
            $seenStatus = $rRow['is_seen'] ? '<span class="label label-default">Seen</span>' : '<span class="label label-danger">NEW RESULT</span>';
            $formattedResTime = date('d M Y h:i A', strtotime($rRow['result_date']));
            $statusLabel = ($rRow['data_capture_status'] === 'approve') ? '<span class="label label-primary">Approved</span>' : '<span class="label label-warning">Result Ready</span>';

            $lisResultsRows .= '
            <tr ' . (!$rRow['is_seen'] ? 'style="background:#f0fdf4;"' : '') . '>
                <td>' . $resSn . '</td>
                <td><strong>' . htmlspecialchars($rRow['hospital_no']) . '</strong></td>
                <td>' . htmlspecialchars($rRow['test_name']) . '<br><small class="text-muted">Order ID: ' . htmlspecialchars($rRow['clinos_order_id'] ?? '-') . '</small></td>
                <td>' . $statusLabel . '</td>
                <td>' . htmlspecialchars($rRow['entered_by'] ?: $rRow['approved_by'] ?: 'LIS System') . '</td>
                <td>' . $formattedResTime . '</td>
                <td>' . $seenStatus . '</td>
                <td>
                    <a href="mgt.php?hosp_no=' . urlencode($rRow['hospital_no']) . '&labrequest_no=' . urlencode($rRow['labrequest_no']) . '" onclick="markItemSeen(\'' . htmlspecialchars($rRow['labrequest_no']) . '\')" class="btn btn-info btn-xs">View Result</a>
                </td>
            </tr>';
        }

        if ($resSn == 0) {
            $lisResultsRows = '<tr><td colspan="8" class="text-center text-muted">No recent LIS results received in the last 48 hours.</td></tr>';
        }
    }

    // 3. Fetch LIS Order Status
    $lisOrderRows = '';
    if ($lisEnabled) {
        $ordStmt = $db->query("
            SELECT lo.*, lm.test_name, lm.patient AS hospital_no
            FROM lis_orders lo
            LEFT JOIN lab_manage lm ON lm.labrequest_no = lo.labrequest_no
            ORDER BY lo.updated_at DESC
            LIMIT 50
        ");
        $ordSn = 0;
        while ($oRow = $ordStmt->fetch(PDO::FETCH_ASSOC)) {
            $ordSn++;
            $statusBadge = '<span class="label label-info">' . htmlspecialchars(strtoupper($oRow['status'])) . '</span>';
            if ($oRow['status'] === 'sent' || $oRow['status'] === 'validated') {
                $statusBadge = '<span class="label label-primary">' . htmlspecialchars(strtoupper($oRow['status'])) . '</span>';
            } elseif ($oRow['status'] === 'failed') {
                $statusBadge = '<span class="label label-danger">FAILED</span>';
            }

            $retryBtn = '';
            if ($oRow['status'] === 'failed') {
                $retryBtn = '<button type="button" onclick="retryLisDispatch(\'' . htmlspecialchars($oRow['labrequest_no']) . '\', \'\', \'' . htmlspecialchars($oRow['test_name']) . '\', \'' . htmlspecialchars($oRow['hospital_no']) . '\')" class="btn btn-xs btn-warning"><i class="fa fa-refresh"></i> Retry</button>';
            }

            $lisOrderRows .= '
            <tr>
                <td>' . $ordSn . '</td>
                <td>' . htmlspecialchars($oRow['labrequest_no']) . '</td>
                <td><strong>' . htmlspecialchars($oRow['hospital_no'] ?? '-') . '</strong></td>
                <td>' . htmlspecialchars($oRow['test_name'] ?? 'Lab Order') . '</td>
                <td><code>' . htmlspecialchars($oRow['clinos_order_id'] ?? '-') . '</code></td>
                <td>' . $statusBadge . '</td>
                <td>' . date('d M Y h:i A', strtotime($oRow['updated_at'])) . '</td>
                <td>' . $retryBtn . '</td>
            </tr>';
        }
        if ($ordSn == 0) {
            $lisOrderRows = '<tr><td colspan="8" class="text-center text-muted">No LIS orders tracked yet.</td></tr>';
        }
    }

    // Output Modal Tab Container
    ?>
    <ul class="nav nav-tabs" id="modalLisTabs">
        <li class="active"><a data-toggle="tab" href="#tab_queue">🧪 Pending Queue (<?php echo $sn; ?>)</a></li>
        <?php if ($lisEnabled): ?>
            <li><a data-toggle="tab" href="#tab_lis_results">📥 New LIS Results <?php echo $unseenCount > 0 ? '<span class="badge badge-danger">' . $unseenCount . '</span>' : ''; ?></a></li>
            <li><a data-toggle="tab" href="#tab_lis_status">🚀 LIS Order Status</a></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" style="padding-top:15px;">
        <div id="tab_queue" class="tab-pane active">
            <table class="table table-bordered table-striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Test Investigation</th>
                        <th>Type</th>
                        <th>Requested By</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $queueRows; ?>
                </tbody>
            </table>
        </div>

        <?php if ($lisEnabled): ?>
            <div id="tab_lis_results" class="tab-pane">
                <div style="margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                    <span class="text-muted" style="font-size:12px;">Showing LIS results received in the last 48 hours.</span>
                    <?php if ($unseenCount > 0): ?>
                        <button type="button" class="btn btn-xs btn-primary" onclick="markAllLisSeen()"><i class="fa fa-check-circle"></i> Mark All as Seen</button>
                    <?php endif; ?>
                </div>
                <table class="table table-bordered table-striped" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Test / Order</th>
                            <th>Status</th>
                            <th>Validated By</th>
                            <th>Result Date</th>
                            <th>Notice</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $lisResultsRows; ?>
                    </tbody>
                </table>
            </div>

            <div id="tab_lis_status" class="tab-pane">
                <table class="table table-bordered table-striped" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Req #</th>
                            <th>Patient</th>
                            <th>Test Name</th>
                            <th>ClinOS Order ID</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $lisOrderRows; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($lisEnabled): ?>
        <script>
            function markAllLisSeen() {
                $.post('mark_lis_seen.php', function(res) {
                    if (res && res.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success('All LIS notifications marked as seen');
                        if (typeof checkLab === 'function') checkLab();
                        $.get('fetch_lab_list.php', function(html) {
                            $('#lab_body').html(html);
                            $('#modalLisTabs a[href="#tab_lis_results"]').tab('show');
                        });
                    }
                });
            }
            function markItemSeen(labrequestNo) {
                $.post('mark_lis_seen.php', { labrequest_no: labrequestNo });
            }
        </script>
    <?php endif; ?>
    <?php
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

