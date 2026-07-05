<?php
session_start();
include("../Connections/Conn.php");
header('Content-Type: application/json');
include('objects.php');
include('helpers.php');

if (isset($_POST['search_patient_json'])) {

    $input = trim($_POST['input_text']);
    $input_like = '%' . $input . '%';

    try {
        $query = $db->prepare("
    SELECT 
        CONCAT(e.fname, ' ', e.surname, ' ', e.oname, ' [', i.insurance_name, ']') AS name,
        e.sn AS id, e.hospital_no
    FROM enrollee e
    INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
    WHERE e.fname LIKE :fname
       OR e.surname LIKE :surname
       OR e.oname LIKE :oname
       OR e.hospital_no LIKE :hospital_no
    LIMIT 50");

        $input_like = '%' . $input . '%';
        $query->bindValue(':fname', $input_like, PDO::PARAM_STR);
        $query->bindValue(':surname', $input_like, PDO::PARAM_STR);
        $query->bindValue(':oname', $input_like, PDO::PARAM_STR);
        $query->bindValue(':hospital_no', $input_like, PDO::PARAM_STR);

        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);


        if (empty($results)) {

            $query2 = $db->prepare("
                SELECT 
                    cust_name AS name,
                    sn AS id,
                    transc_code AS hospital_no
                FROM pharm_ext
                WHERE cust_name LIKE :cust_name
                   OR transc_code LIKE :transc_code
                LIMIT 50
            ");

            $query2->bindValue(':cust_name', $input_like, PDO::PARAM_STR);
            $query2->bindValue(':transc_code', $input_like, PDO::PARAM_STR);
            $query2->execute();

            $results = $query2->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}
