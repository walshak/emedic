<?php
include_once '../../Connections/Conn.php'; // Make sure this file establishes the database connection

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Combined query to get both regular patients and walk-in patients
$sql = "
-- Regular patients from enrollee table
SELECT 
    s.surname, 
    s.fname, 
    s.oname, 
    s.hospital_no as patient_id, 
    s.hmo_no, 
    COALESCE(c.TOTAL_DEBITS, 0) AS TOTAL_DEBITS,
    COALESCE(c.TOTAL_CREDITS, 0) AS TOTAL_CREDITS,
    'regular' as patient_type
FROM 
    enrollee AS s
LEFT JOIN (
    SELECT 
        hospital_no as patient_ref, 
        SUM(dr_amt) AS TOTAL_DEBITS, 
        SUM(cr_amt) AS TOTAL_CREDITS
    FROM 
        chart_ledger
    WHERE 
        account_no = 2121 AND hospital_no IS NOT NULL
    GROUP BY 
        hospital_no
) AS c ON c.patient_ref = s.hospital_no
WHERE 
    (s.surname LIKE :search1 OR 
    s.fname LIKE :search2 OR 
    s.oname LIKE :search3 OR 
    s.hospital_no LIKE :search4)

UNION ALL

-- Walk-in patients from pharm_ext table
SELECT 
    SUBSTRING_INDEX(p.cust_name, ' ', 1) as surname,
    SUBSTRING_INDEX(SUBSTRING_INDEX(p.cust_name, ' ', 2), ' ', -1) as fname,
    CASE 
        WHEN CHAR_LENGTH(p.cust_name) - CHAR_LENGTH(REPLACE(p.cust_name, ' ', '')) >= 2 
        THEN SUBSTRING_INDEX(p.cust_name, ' ', -1)
        ELSE ''
    END as oname,
    p.transc_code as patient_id,
    1000 as hmo_no,
    COALESCE(w.TOTAL_DEBITS, 0) AS TOTAL_DEBITS,
    COALESCE(w.TOTAL_CREDITS, 0) AS TOTAL_CREDITS,
    'walk_in' as patient_type
FROM 
    pharm_ext AS p
LEFT JOIN (
    SELECT 
        hospital_no as patient_ref, 
        SUM(dr_amt) AS TOTAL_DEBITS, 
        SUM(cr_amt) AS TOTAL_CREDITS
    FROM 
        chart_ledger
    WHERE 
        account_no = 2121 AND hospital_no IS NOT NULL
    GROUP BY 
        hospital_no
) AS w ON w.patient_ref = p.transc_code
WHERE 
    (p.cust_name LIKE :search5 OR 
    p.transc_code LIKE :search6)

ORDER BY 
    patient_id
LIMIT 10";

$accounts = $db->prepare($sql);
$accounts->bindParam(':search1', $search, PDO::PARAM_STR);
$accounts->bindParam(':search2', $search, PDO::PARAM_STR);
$accounts->bindParam(':search3', $search, PDO::PARAM_STR);
$accounts->bindParam(':search4', $search, PDO::PARAM_STR);
$accounts->bindParam(':search5', $search, PDO::PARAM_STR);
$accounts->bindParam(':search6', $search, PDO::PARAM_STR);
$t = $accounts->execute();



$patient_data = array();

while ($account = $accounts->fetch(PDO::FETCH_ASSOC)) {
    $patient_id = $account['patient_id'];
    $patient_type = $account['patient_type'];
    $hmo_no = $account['hmo_no'];

    // Use COALESCE in the SQL query to default to zero if there are no transactions
    $TOTAL_DEBITS = $account['TOTAL_DEBITS'];
    $TOTAL_CREDITS = $account['TOTAL_CREDITS'];
    $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;

    $patient_data[] = array(
        'patient_id' => $patient_id,
        'patient_type' => $patient_type,
        'hospital_no' => $patient_type == 'regular' ? $patient_id : null,
        'transc_code' => $patient_type == 'walk_in' ? $patient_id : null,
        'surname' => $account['surname'],
        'fname' => $account['fname'],
        'oname' => $account['oname'],
        'balance' => number_format($current_balance, 2)
    );
}

echo json_encode($patient_data);
