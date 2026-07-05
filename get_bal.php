<?php
$debug = true;

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=emedic;charset=utf8mb4',
        'root',
        'surepass098',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("<h3 style='color:red;'>DB CONNECTION FAILED:</h3><pre>{$e->getMessage()}</pre>");
}




// First, create a derived table for latest balances
$stmt = $db->query("
    SELECT 
        e.hospital_no,
        e.surname,
        e.fname,
        e.gender,
        e.addr,
        e.discount_set,
        i.insurance_name,
        i.interest,
        i.insurance_type,
        i.insurance_no,
        COALESCE(pb_last.bal, 0) AS current_balance
    FROM enrollee AS e
    INNER JOIN insurance_tbl AS i ON e.hmo_no = i.insurance_no
    LEFT JOIN (
        -- Get last balance for family insurance
        SELECT insurance_no, MAX(sn) AS last_sn, bal
        FROM patient_billing
        WHERE insurance_no IS NOT NULL
        GROUP BY insurance_no
        UNION ALL
        -- Get last balance for individual patients
        SELECT NULL AS insurance_no, MAX(sn) AS last_sn, bal, hospital_no
        FROM patient_billing
        WHERE insurance_no IS NULL
        GROUP BY hospital_no
    ) AS pb_last
    ON (i.insurance_type = 'Family' AND pb_last.insurance_no = i.insurance_no)
       OR (i.insurance_type != 'Family' AND pb_last.hospital_no = e.hospital_no)
    WHERE e.status = 'active'
");

$patients = [];
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $patients[] = [
            'hospital_no' => $row['hospital_no'],
            'patient_name' => $row['surname'] . ', ' . $row['fname'],
            'gender' => $row['gender'],
            'address' => $row['addr'],
            'discount_set' => $row['discount_set'],
            'insurance_name' => $row['insurance_name'],
            'interest' => $row['interest'],
            'insurance_type' => $row['insurance_type'],
            'insurance_no' => $row['insurance_no'],
            'current_balance' => $row['current_balance'],
            'insur_title' => $row['insurance_type'] == 'Family' ?
                $row['insurance_name'] . ' <i>[' . $row['insurance_type'] . ']</i>' : $row['insurance_name'],
            'save_insurance_no' => $row['insurance_type'] == 'Family' ? $row['insurance_no'] : 'private'
        ];
    }
}



















exit;
// ===== FAST SINGLE QUERY =====
$query = "
SELECT 
    i.insurance_name,
    i.insurance_type,
    i.insurance_no,
    COALESCE(family_balances.bal, individual_balances.bal) AS balance,
    CASE 
        WHEN i.insurance_type='Family' THEN fam_emr.hospital_no
        ELSE e.hospital_no
    END AS display_emr
FROM enrollee e
INNER JOIN insurance_tbl i ON e.hmo_no = i.insurance_no
LEFT JOIN (
    -- Latest balance per insurance_no (family)
    SELECT pb.insurance_no, pb.bal
    FROM patient_billing pb
    INNER JOIN (
        SELECT insurance_no, MAX(sn) AS max_sn
        FROM patient_billing
        WHERE insurance_no IS NOT NULL
        GROUP BY insurance_no
    ) latest_pb ON pb.insurance_no = latest_pb.insurance_no AND pb.sn = latest_pb.max_sn
) AS family_balances ON i.insurance_no = family_balances.insurance_no
LEFT JOIN (
    -- Latest balance per hospital_no (individual)
    SELECT pb.hospital_no, pb.bal
    FROM patient_billing pb
    INNER JOIN (
        SELECT hospital_no, MAX(sn) AS max_sn
        FROM patient_billing
        WHERE hospital_no IS NOT NULL
        GROUP BY hospital_no
    ) latest_pb ON pb.hospital_no = latest_pb.hospital_no AND pb.sn = latest_pb.max_sn
) AS individual_balances ON e.hospital_no = individual_balances.hospital_no
LEFT JOIN (
    -- One EMR per family insurance
    SELECT hmo_no, MIN(hospital_no) AS hospital_no
    FROM enrollee
    GROUP BY hmo_no
) AS fam_emr ON i.insurance_no = fam_emr.hmo_no
WHERE i.status='active'
HAVING balance > 0
ORDER BY i.insurance_no, e.hospital_no
";

$stmt = $db->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== DISPLAY TABLE =====
echo "<h2>Insurance Balances Report</h2>";
echo "<table border='1' cellpadding='5'>
        <tr>
            <th>SN</th>
            <th>Insurance Type</th>
            <th>Insurance Name</th>
            <th>Insurance No</th>
            <th>Balance</th>
            <th>EMR</th>
        </tr>";

$sn = 1;
foreach ($results as $row) {
    echo "<tr>
            <td>{$sn}</td>
            <td>{$row['insurance_type']}</td>
            <td>{$row['insurance_name']}</td>
            <td>{$row['insurance_no']}</td>
            <td>{$row['balance']}</td>
            <td>{$row['display_emr']}</td>
         </tr>";
    $sn++;
}

echo "</table>";
