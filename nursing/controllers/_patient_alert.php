<?php //session_start();
include('../../Connections/Conn.php');
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');

if (isset($_POST['save2'])) {
    $message = '';
    $response_data = '';
    $status = 401;
    $data['hospital_no'] = $_POST['hospital_no'];
    $data['alert'] = $_POST['alert'];
    $data['created_by'] = $_SESSION['id'];

    $check = $PatientAlert->get(['hospital_no' => $data['hospital_no'], 'alert' => $data['alert']]);
    if (empty($check)) {
        $save = $PatientAlert->save($data);
        //  if ($save) {
        $status = 200;
        $message = 'Alert is saved...';
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
        //  }
    } else {
        $status = 401;
        $message = 'Alert is already saved...';
    }
    echo json_encode(['status' => $status, 'data' => $response_data, 'message' => $message]);
    exit;
}

if (isset($_POST['load_edit'])) {
    $id = $_POST['id'];
    $status = 401;
    $data = null;
    $alert = $PatientAlert->find($id);
    if (!empty($alert)) {
        $data = $alert->alert;
        $status = 200;
    }

    echo json_encode(['status' => $status, 'data' => $data]);
    exit;
}




if (isset($_POST['update_alert'])) {
    $message = '';
    $response_data = '';
    $status = 401;
    $data['id'] = $_POST['id'];
    $data['alert'] = $_POST['alert'];
    $data['hospital_no'] = $_POST['hospital_no'];


    $save = $PatientAlert->update($data);
    $status = 200;
    $message = 'Alert is saved...';
    $sn = 1;
    $patient_alerts =  $PatientAlert->get(['hospital_no' => $data['hospital_no']], true);
    foreach ($patient_alerts as $key => $patient_alert) {
        $response_data .= ' <tr>
                    <td>' . $sn++ . '</td>
                    <td>' . $patient_alert->alert . '</td>
                   <td> <button class="btn btn-xs btn-info edit-alert-btn" arial-data="' . $patient_alert->id . '"><i class="fa fa-edit"></i> </button>
                    <button class="btn btn-xs btn-danger delete-alert-btn" arial-data="' . $patient_alert->id . '"><i class="fa fa-trash"></i> </button></td>
                </tr>';
    }


    echo json_encode(['status' => $status, 'data' => $response_data, 'message' => $message]);
    exit;
}


if (isset($_POST['delete'])) {
    $message = '';
    $response_data = '';
    $status = 401;
    $data['id'] = $_POST['id'];
    $data['hospital_no'] = $_POST['hospital_no'];


    $save = $PatientAlert->delete($data);
    $status = 200;
    $message = 'Alert is deleted...';
    $sn = 1;
    $patient_alerts =  $PatientAlert->get(['hospital_no' => $data['hospital_no']], true);
    foreach ($patient_alerts as $key => $patient_alert) {
        $response_data .= ' <tr>
                    <td>' . $sn++ . '</td>
                    <td>' . $patient_alert->alert . '</td>
                   <td> <button class="btn btn-xs btn-info edit-alert-btn" arial-data="' . $patient_alert->id . '"><i class="fa fa-edit"></i> </button>
                    <button class="btn btn-xs btn-danger delete-alert-btn" arial-data="' . $patient_alert->id . '"><i class="fa fa-trash"></i> </button></td>
                </tr>';
    }


    echo json_encode(['status' => $status, 'data' => $response_data, 'message' => $message]);
    exit;
}
