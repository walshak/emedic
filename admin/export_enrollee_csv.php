<?php
require_once("../Connections/Conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $cols = isset($_POST['cols']) ? $_POST['cols'] : [];

    if (!$start || !$end || !$cols) {
        exit('Invalid request');
    }

    // Build SQL select fields
    $fieldMap = [
        'name' => "CONCAT(e.surname, ' ', e.fname, ' ', e.oname) as name",
        'insurance' => "e.insurance",
        'hospital_no' => "CONCAT('', e.hospital_no) AS hospital_no", // Preserves leading zeros
        'email' => "e.email",
        'phone' => "e.phone",
        'date_of_visit' => "a.ap_date_time as date_of_visit",
        'doctor_name' => "au.fullname as doctor_name",
        'was_admitted' => "IF(adm.sn IS NULL, 'No', 'Yes') as was_admitted",
        'appt_status' => "(
            CASE 
                WHEN adm.adm_status = '4' THEN 
                    CONCAT('Discharged (', TIMESTAMPDIFF(DAY, adm.date_admit, adm.date_discharge), ' days/', TIMESTAMPDIFF(HOUR, adm.date_admit, adm.date_discharge) % 24, ' hrs) Bed: ', adm.room_bed)
                WHEN adm.adm_status = '3' THEN 
                    CONCAT('Admitted (', TIMESTAMPDIFF(DAY, adm.date_admit, NOW()), ' days/', TIMESTAMPDIFF(HOUR, adm.date_admit, NOW()) % 24, ' hrs) Bed: ', adm.room_bed)
                WHEN a.queue_lock = '1' AND a.status = 'checkin' THEN 
                    CONCAT('Seen Doctor: ', TIMESTAMPDIFF(DAY, a.ap_date_time, NOW()), ' days/', TIMESTAMPDIFF(HOUR, a.ap_date_time, NOW()) % 24, ' hrs ago')
                WHEN a.queue_lock = '1' AND a.status = 'discharge' THEN 
                    CONCAT('Discharged seen: ', TIMESTAMPDIFF(DAY, a.ap_date_time, NOW()), ' days/', TIMESTAMPDIFF(HOUR, a.ap_date_time, NOW()) % 24, ' hrs ago')
                WHEN a.status = 'cancelled' THEN 'Cancelled'
                ELSE a.status
            END
        ) as appt_status",
        'consultation_type' => "CONCAT('Contact: ', IFNULL(au.fullname,''), ' ', IFNULL(a.services_name,'')) as consultation_type"
    ];

    $selectFields = [];
    foreach ($cols as $col) {
        if (isset($fieldMap[$col])) {
            $selectFields[] = $fieldMap[$col];
        }
    }

    if (empty($selectFields)) {
        exit('No valid columns selected');
    }

    $select = implode(", ", $selectFields);

    $sql = "SELECT $select
        FROM apptm a
        INNER JOIN enrollee e ON a.hospital_no = e.hospital_no
        LEFT JOIN admin_users au ON (a.app_by = au.username OR a.doctor_id = au.id)
        LEFT JOIN admission adm ON adm.hospital_no = a.hospital_no AND adm.app_no = a.appt_no
        WHERE DATE(a.ap_date_time) BETWEEN :start AND :end
        GROUP BY a.appt_no
        ORDER BY a.ap_date_time DESC";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':start', $start);
    $stmt->bindParam(':end', $end);
    $stmt->execute();

    // Output CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="enrollee_visits_' . $start . '_to_' . $end . '.csv"');


    $out = fopen('php://output', 'w');
    fputcsv($out, $cols);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $csvRow = [];
        foreach ($cols as $col) {
            $value = $row[$col];

            // Preserve hospital_no as string (leading zeros)
            if ($col === 'hospital_no') {
                $value = '# ' . (string)$value;
            }

            $csvRow[] = $value;
        }
        fputcsv($out, $csvRow);
    }

    fclose($out);
    exit;
}
