<?php
include("../Connections/Conn.php");
// if (isset($_GET['term']) && $_GET['term'] != "") {
//     $term = $_GET['term'];
//     $stmt = $db->query("SELECT hospital_no, surname,fname FROM enrollee WHERE hmo_no!='' AND insurance!='' AND (hospital_no LIKE '%$term%' OR surname LIKE '%$term%' OR fname LIKE '%$term%') order by hospital_no LIMIT 100");
//     $res = $stmt->fetchAll();
//     $data = '';
//     foreach ($res as $row) {
//         $data .= '<option value="' . $row["hospital_no"] . '__' . $row["fname"] . ' ' .  $row["surname"] . '__IN' . '">EMR ID: ' . $row["hospital_no"] . ' ' . $row["fname"]  . ', ' . $row["surname"] . '</option>';
//     }
//     echo $data;
// }

if (isset($_GET['term']) && $_GET['term'] != "") {
    header('Content-Type: application/json');

    $input_text = $_GET['term'];

    $word_count = str_word_count($input_text);

    if ($word_count == 1) {
        $stmt = $db->query("SELECT sn AS id, hospital_no, fname, surname, oname FROM 
            enrollee WHERE hmo_no IS NOT NULL AND insurance IS NOT NULL AND (surname LIKE '$input_text%' OR fname LIKE '$input_text%' OR oname LIKE '$input_text%' OR hospital_no LIKE '%$input_text%') 
            LIMIT 20");
        $enrolleess =  $stmt->fetchAll(PDO::FETCH_ASSOC);
        // exit;
    } else {
        // echo $word_count;
        $partt = explode(" ", $input_text);
        $name1 = $partt[0];
        $name2 = $partt[1];
        $name1 =  "$name1%";
        $name2 =  "$name2%";

        $query = $db->prepare("SELECT sn AS id, hospital_no, fname, surname, oname FROM 
            enrollee WHERE hmo_no!='' AND insurance!='' AND (
        		(surname LIKE :name1 AND fname LIKE :name2)  OR
                (surname LIKE :name3 AND fname LIKE :name4)  OR
                (oname LIKE :name5 AND surname LIKE :name6) OR
                (oname LIKE :name7 AND surname LIKE :name8) OR
                (fname LIKE :name9 AND oname LIKE :name10) OR 
                (fname LIKE :name11 AND oname LIKE :name12))
                LIMIT 20");

        $query->bindParam(':name1', $name1);
        $query->bindParam(':name2', $name2);
        $query->bindParam(':name3', $name2);
        $query->bindParam(':name4', $name1);
        $query->bindParam(':name5', $name1);
        $query->bindParam(':name6', $name2);
        $query->bindParam(':name7', $name2);
        $query->bindParam(':name8', $name1);
        $query->bindParam(':name9', $name1);
        $query->bindParam(':name10', $name2);
        $query->bindParam(':name11', $name2);
        $query->bindParam(':name12', $name1);
        $query->execute();
        $enrolleess =  $query->fetchAll(PDO::FETCH_ASSOC);
        // exit;
    }
    echo trim(json_encode($enrolleess));
}

if (isset($_POST['typeahead_search_patients_ex'])) {
    header('Content-Type: application/json');

    $input_text = $_POST['input_text'];
    $stmt = $db->query("SELECT CONCAT(transc_code, ' ', cust_name)  
        AS name, transc_code, cust_name, referral FROM pharm_ext 
        WHERE 1 AND (transc_code LIKE '$input_text%' OR cust_name LIKE '$input_text%' OR referral LIKE '$input_text%') order by transc_code");
    $enrolleess =  $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($enrolleess);
    exit;
}
