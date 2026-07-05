<?php
function getPatientStatusInfo($db, $hos_no, $session)
{
    $result = array(
        'patient_type' => '',
        'app_no' => $hos_no,
        'ap_type' => null,
        'encounter' => 0,
        'back_date' => date('Y-m-d'),
        'visit_date' => '(First Visit)',
        'location_dept' => null,
        'bed_ad_at' => null,
        'department' => null,
    );

    // Check for admission
    $stmt = $db->prepare("SELECT date_admit, app_no, dept_id, room_bed, admit_type FROM admission 
        WHERE hospital_no = :hos_no AND adm_status = '3'");
    $stmt->bindParam(':hos_no', $hos_no);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $admit_dated = date('Y-m-d', strtotime($row['date_admit']));
        $result['back_date'] = $admit_dated;
        $result['app_no'] = $row['app_no'];
        $result['location_dept'] = $row['dept_id'];
        $result['bed_ad_at'] = $row['room_bed'];
        $result['admit_type'] = $row['admit_type'];
        $result['ap_type'] = '2';

        // Get department name
        $result['department'] = getDepartmentName($db, $result['location_dept']);
        $result['patient_type'] = "<b style='color:red;'>Patient On-Admission:</b>&nbsp Department: " .
            $result['department'] . ' (' . ($result['bed_ad_at'] ?: 'A&E') . ') // Admitted On: ' .
            date('d M Y', strtotime($admit_dated)) . ".  👉 <a href='../admission_billing.php?emr=" . urlencode($hos_no) . "'> <i style ='color: red;'>See Daily Invoices.</i></a>";
    } else {
        // No admission: fallback to appointment
        $stmt = $db->prepare("SELECT appt_no, ap_type, dept, services_name, date_ap, status 
            FROM apptm 
            WHERE hospital_no = :hos_no 
            ORDER BY sn ASC LIMIT 1");
        $stmt->bindParam(':hos_no', $hos_no);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result['app_no'] = $row['appt_no'];
            $result['ap_type'] = $row['ap_type'];
            $result['location_dept'] = $row['dept'];
            $appointment_date = $row['date_ap'];
            $result['encounter'] = ($row['status'] === 'checkin') ? 1 : 0;
        } else {
            // No appointment found
            $appointment_date = date('Y-m-d', strtotime('-1 days'));
            $result['encounter'] = 0;
        }

        // Calculate visit date difference
        $diff_days = max(1, (new DateTime())->diff(new DateTime($appointment_date))->days);
        $result['back_date'] = date('Y-m-d');

        // Fetch last visit (excluding the most recent)
        $stmt = $db->prepare("SELECT date_ap 
            FROM apptm 
            WHERE hospital_no = :hos_no 
            ORDER BY date_ap DESC 
            LIMIT 1 OFFSET 1");
        $stmt->bindParam(':hos_no', $hos_no);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $result['visit_date'] = ' // Last Visit: ' . date('d M Y', strtotime($row['date_ap']));
        }

        // Get department name
        $result['department'] = getDepartmentName($db, $result['location_dept']);

        // Set patient type string
        $result['patient_type'] = "<b style='color:blue;'>Out-Patient:</b> &nbsp Department: " .
            $result['department'] . $result['visit_date'];
    }

    return $result;
}

function getDepartmentName($db, $dept_id)
{
    if (!$dept_id) return null;
    $stmt = $db->prepare("SELECT department FROM department WHERE sn = :id");
    $stmt->bindParam(':id', $dept_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return isset($row['department']) ? $row['department'] : null;
}
