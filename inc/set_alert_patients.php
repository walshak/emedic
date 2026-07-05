<?php
session_start();
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');

/* if (isset($_POST['check_alert_2'])) {

    $response = array(
        'status' => 0,
        'data' => ''
    );

    $patient_alerts =  $PatientAlert->get(['hospital_no' => $_POST['check_alert_2']], true);
    if ($patient_alerts == TRUE) {

        $data = '<center><strong>THIS PATIENT ALERT ! </strong></center><hr>';
        foreach ($patient_alerts as $key => $patient_alert) {
            $data .= $patient_alert->alert . '<hr>';
        }
        $response['data'] = $data;
        $response['status'] = '0';
        echo  json_encode($response);
    } else {
        $response['data'] = '';
        $response['status'] = '1';
        echo  json_encode($response);
    }
}
 */

if (isset($_POST['check_alert_2X'])) {

    $hospital_no = $_POST['check_alert_2X'];
    $staff = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'guest';

    $patient_alerts = $PatientAlert->get(
        array('hospital_no' => $hospital_no),
        true
    );

    if ($patient_alerts) {

        foreach ($patient_alerts as $alert) {
            if ($alert->where_to_show == '1') {
                $who = 'Clinical Staff Only';
            } elseif ($alert->where_to_show == '2') {
                $who = 'All Staff';
            } else {
                $who = '';
            }
            echo "<div class='mb-3 p-3 border-start border-4 border-danger bg-light rounded shadow-sm' style='font-size:20px; color:black;'>";
            echo htmlentities($alert->alert) . " <span class='badge bg-danger'>$who</span>";
            echo "</div>";

            // Date display
            if (!empty($alert->created_at) && strtotime($alert->created_at)) {
                $formattedDate = date('d M Y, h:i a', strtotime($alert->created_at));
                echo "<small class='text-muted'>{$formattedDate} by " . htmlentities($alert->created_by) . "</small>";
            } else {
                echo "<small class='text-muted'>by " . htmlentities($alert->created_by) . "</small>";
            }

            // Delete permission
            $canDelete = false;

            if (isset($_SESSION['rights']) && $_SESSION['rights'] == 'MD') {
                $canDelete = true;
            } elseif (
                isset($_SESSION['fullname']) &&
                $_SESSION['fullname'] == $alert->created_by
            ) {
                $canDelete = true;
            }

            /* &&
                !empty($alert->created_at) &&
                (time() - strtotime($alert->created_at)) <= (5 * 24 * 60 * 60) */

            if ($canDelete) {
                echo "<div class='mt-2'>
                        <button class='btn btn-xs btn-danger' onclick='delete_alert(\"{$alert->id}\")'>
                            Delete
                        </button>
                      </div>";
            }
        }
    } else {
        echo "";
        exit;
    }

    exit;
}


if (isset($_POST['check_alert'])) {

    $hospital_no = $_POST['check_alert'];
    $staff = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'guest';
    if (isset($_SESSION['rights']) && in_array($_SESSION['rights'], array('MD', 'LB', 'NS', 'DR', 'PH'))) {
        $who = 1;
    } else {
        $who = 2;
    }
    // Unique session key per patient + staff
    $sessionKey = 'alert_shown_' . md5($hospital_no . '_' . $staff);

    // If already shown in this session, don't display again
    if (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] === true) {
        echo "";
        exit;
    }

    $patient_alerts = $PatientAlert->get(
        array(
            'hospital_no' => $hospital_no,
            'where_to_show' => $who
        ),
        true
    );

    if ($patient_alerts) {

        // Mark as shown for this session
        $_SESSION[$sessionKey] = true;

        foreach ($patient_alerts as $alert) {

            echo "<div class='mb-3 p-3 border-start border-4 border-danger bg-light rounded shadow-sm' style='font-size:20px; color:black;'>";
            echo htmlentities($alert->alert);
            echo "</div>";

            // Date display
            if (!empty($alert->created_at) && strtotime($alert->created_at)) {
                $formattedDate = date('d M Y, h:i a', strtotime($alert->created_at));
                echo "<small class='text-muted'>{$formattedDate} by " . htmlentities($alert->created_by) . "</small>";
            } else {
                echo "<small class='text-muted'>by " . htmlentities($alert->created_by) . "</small>";
            }

            // Delete permission
            $canDelete = false;

            if (isset($_SESSION['rights']) && $_SESSION['rights'] == 'MD') {
                $canDelete = true;
            } elseif (
                isset($_SESSION['fullname']) &&
                $_SESSION['fullname'] == $alert->created_by
            ) {
                $canDelete = true;
            }

            if ($canDelete) {
                echo "<div class='mt-2'>
                        <button class='btn btn-xs btn-danger' onclick='delete_alert(\"{$alert->id}\")'>
                            Delete
                        </button>
                      </div>";
            }
        }

        echo "<div align='center' style='color:black;'><small>Click Outside to Close</small></div>";
        exit;
    } else {
        echo "";
        exit;
    }
}


if (isset($_POST['delete_notes'])) {

    $id = $_POST['delete_notes'];

    $update = $db->prepare("UPDATE tbl_patient_alerts SET status = '0' WHERE id = ?");
    $updated = $update->execute(array($id));
}

if (isset($_POST['save'])) {

    $message = '';
    $response_data = '';
    $status = 401;
    $data['hospital_no'] = $_POST['hospital_no'];
    $data['who_should_see'] = $_POST['who_should_see'];
    $data['alert'] = $_POST['alert'];
    $data['created_by'] = $_SESSION['fullname'];

    $check = $PatientAlert->get(['hospital_no' => $data['hospital_no'], 'alert' => $data['alert'], 'where_to_show' => $data['who_should_see']]);
    if (empty($check)) {
        $save = $PatientAlert->save($data);
        $status = 200;
        $message = 'Alert Saved';
        $sn = 1;
        $patient_alerts =  $PatientAlert->get(['hospital_no' => $data['hospital_no']], true);
        foreach ($patient_alerts as $key => $patient_alert) {
            $response_data .= ' <tr>
                    <td>' . $sn++ . '</td>
                    <td>' . $patient_alert->alert . '</td>
                    <td> <button class="btn btn-xs btn-info edit-alert-btn" arial-data="' . $patient_alert->id . '"><i class="fa fa-edit"></i> </button>
                    <button class="btn btn-xs btn-danger delete-alert-btn" arial-data="' . $patient_alert->id . '"><i class="fa fa-trash"></i> </button>
                </td>
                </tr>';
        }
    } else {
        $status = 401;
        $message = 'Alert is already saved...';
    }
    echo json_encode(['status' => $status, 'data' => $response_data, 'message' => $message]);
    exit;
}
