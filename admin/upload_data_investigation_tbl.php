<?php
// Database configuration
require_once('../Connections/Conn.php');

function sanitize($str)
{
    $charactersToRemove = '*=%",;:\'.';
    $pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
    return preg_replace($pattern, '', $str);
}

$upload_type = isset($_POST['upload_type']) ? $_POST['upload_type'] : '';

if (!isset($_FILES['the_upload']['tmp_name'])) {
    die("No file uploaded");
}

$the_file = $_FILES['the_upload']['tmp_name'];
$upload_error = $_FILES['the_upload']['error'];

if ($upload_type == 'hmo') {
    $upload_hmo = isset($_POST['upload_hmo']) ? $_POST['upload_hmo'] : '';
    $typeoftable = isset($_POST['typeoftable']) ? $_POST['typeoftable'] : 'Consultation';

    switch ($typeoftable) {
        case 'Consultation':
            $title = 'Consultation';
            $traff_table = "hmo_medical_tariff";
            break;
        case 'Medical Services':
            $title = 'Medical Services';
            $traff_table = "hmo_medical_tariff";
            break;
        case 'Nursing Services':
            $title = 'Nursing Services';
            $traff_table = "hmo_medical_tariff";
            break;
        case 'Other Services':
            $title = 'Other Services';
            $traff_table = "hmo_medical_tariff";
            break;
        case 'Investigations':
            $table_type = 'invest';
            $title = 'Investigations';
            $traff_table = "hmo_investigation_tariff";
            break;
        case 'Bed':
            $title = 'Bed / Accommodation';
            $traff_table = "hmo_bed_tariff";
            break;
        default:
            $title = 'Consultation';
            $traff_table = "hmo_medical_tariff";
            break;
    }

    if (($handle = fopen($the_file, 'r')) !== FALSE) {
        $header = fgetcsv($handle); // read header
        $expected_headers = ['sn', 'stock_sn', 'hmo', 'item', 'insurance_name', 'price', 'delete(YES or NO)'];
        if (array_diff($expected_headers, $header)) {
            die("Invalid CSV header columns");
        }

        $errors = '';
        $i = 1;
        while (($data = fgetcsv($handle)) !== FALSE) {
            $data = array_map('trim', $data);
            if (empty(array_filter($data))) {
                $i++;
                continue;
            }

            list($sn, $stock_sn, $hmo, $item, $insurance_name, $price, $del) = $data;

            if ($hmo != $upload_hmo) {
                $errors .= "Row $i: Invalid HMO number<br>";
                $i++;
                continue;
            }

            if (strtoupper($del) == 'YES' && $sn != '') {
                $stmt = $db->prepare("DELETE FROM $traff_table WHERE sn=? AND hmo=?");
                $stmt->execute([$sn, $hmo]);
                $i++;
                continue;
            }

            if ($price == '' || !is_numeric($price) || $stock_sn == '' || !is_numeric($stock_sn) || $item == '') {
                $errors .= "Row $i: Invalid data<br>";
                $i++;
                continue;
            }

            if ($sn != '') {
                $stmt = $db->prepare("UPDATE $traff_table SET price=? WHERE sn=? AND hmo=?");
                $stmt->execute([$price, $sn, $hmo]);
            } else {
                $stmt = $db->prepare("INSERT INTO $traff_table (hmo, stock_sn, price) VALUES (?, ?, ?)");
                $stmt->execute([$hmo, $stock_sn, $price]);
            }

            $i++;
        }

        fclose($handle);

        echo "<div align='center'>
                <h2 style='color: blue;'>HMO Records processed</h2>
                <hr>
                <a href='index.php?updown' class='btn btn-danger btn-xs'>[ Close ]</a>";
        if ($errors != '') echo "<hr><h6>Some rows had errors:</h6><p>$errors</p>";
        echo "</div>";
    } else {
        die("Error opening file");
    }
} else { // Non-HMO Upload
    $typeoftable = isset($_POST['typeoftable']) ? $_POST['typeoftable'] : 'Consultation';

    switch ($typeoftable) {
        case 'Consultation':
            $table_type = 'price_table';
            $title = 'Consultation';
            break;
        case 'Medical Services':
            $table_type = 'price_table';
            $title = 'Medical Services';
            break;
        case 'Nursing Services':
            $table_type = 'price_table';
            $title = 'Nursing Services';
            break;
        case 'Other Services':
            $table_type = 'price_table';
            $title = 'Other Services';
            break;
        case 'Investigations':
            $table_type = 'invest';
            $title = 'Investigations';
            break;
        case 'Bed':
            $table_type = 'bed';
            $title = 'Bed / Accommodation';
            break;
        default:
            $table_type = 'price_table';
            $title = 'Consultation';
            break;
    }

    if (($handle = fopen($the_file, 'r')) !== FALSE) {
        $header = fgetcsv($handle); // read header
        $errors = '';
        $i = 1;

        while (($r = fgetcsv($handle)) !== FALSE) {
            $r = array_map('trim', $r);
            if (empty(array_filter($r))) {
                $i++;
                continue;
            }

            if ($table_type == 'price_table') {
                $sn = isset($r[0]) ? $r[0] : '';
                $item_service = isset($r[1]) ? sanitize($r[1]) : '';
                $category = isset($r[2]) ? $r[2] : '';
                $dept = isset($r[3]) ? $r[3] : '';
                $price_table = isset($r[4]) ? $r[4] : '';
                $hosp_price = isset($r[5]) ? $r[5] : 0;
                $ext_price = isset($r[6]) ? $r[6] : 0;
                $nhis_price = isset($r[7]) ? $r[7] : 0;
                $status = isset($r[8]) ? $r[8] : 0;

                if ($item_service == '' || $price_table == '' || !is_numeric($hosp_price)) {
                    $errors .= "Row $i: Invalid data<br>";
                    $i++;
                    continue;
                }

                if ($sn != '') {
                    $stmt = $db->prepare("UPDATE prices_table SET item_service=?, category=?, dept=?, price_table=?, hosp_price=?, ext_price=?, nhis_price=?, status=? WHERE sn=? AND price_table=?");
                    $stmt->execute([$item_service, $category, $dept, $price_table, $hosp_price, $ext_price, $nhis_price, $status, $sn, $title]);
                } else {
                    $stmt = $db->prepare("INSERT INTO prices_table (item_service, category, dept, price_table, hosp_price, ext_price, nhis_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$item_service, $category, $dept, $price_table, $hosp_price, $ext_price, $nhis_price, $status]);
                }
            } elseif ($table_type == 'invest') {
                $sn = isset($r[0]) ? $r[0] : '';
                $test = isset($r[1]) ? sanitize($r[1]) : '';
                $category = isset($r[2]) ? $r[2] : '';
                $dept = isset($r[3]) ? $r[3] : '';
                $hosp_price = isset($r[4]) ? $r[4] : 0;
                $ext_price = isset($r[5]) ? $r[5] : 0;
                $nhis_price = isset($r[6]) ? $r[6] : 0;
                $status = isset($r[8]) ? $r[8] : 0;

                if ($test == '' || $category == '' || !is_numeric($hosp_price)) {
                    $errors .= "Row $i: Invalid data<br>";
                    $i++;
                    continue;
                }

                if ($sn != '') {
                    $stmt = $db->prepare("UPDATE lab_scan SET test=?, category=?, dept=?, hosp_price=?, ext_price=?, nhis_price=?, status=? WHERE sn=?");
                    $stmt->execute([$test, $category, $dept, $hosp_price, $ext_price, $nhis_price, $status, $sn]);
                } else {
                    $stmt = $db->prepare("INSERT INTO lab_scan (test, category, dept, hosp_price, ext_price, nhis_price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$test, $category, $dept, $hosp_price, $ext_price, $nhis_price, $status]);
                }
            } elseif ($table_type == 'bed') {
                $sn = isset($r[0]) ? $r[0] : '';
                $room_name = isset($r[1]) ? $r[1] : '';
                $bed_no = isset($r[2]) ? $r[2] : '';
                $nhis_price = isset($r[3]) ? $r[3] : 0;
                $hosp_price = isset($r[4]) ? $r[4] : 0;
                $ext_price = isset($r[5]) ? $r[5] : $hosp_price;

                if ($room_name == '' || $bed_no == '') {
                    $errors .= "Row $i: Invalid data<br>";
                    $i++;
                    continue;
                }

                if ($sn != '') {
                    $stmt = $db->prepare("UPDATE bed_mgt SET room_name=?, bed_no=?, nhis_price=?, hosp_price=?, ext_price=? WHERE sn=?");
                    $stmt->execute([$room_name, $bed_no, $nhis_price, $hosp_price, $ext_price, $sn]);
                } else {
                    $stmt = $db->prepare("INSERT INTO bed_mgt (room_name, bed_no, nhis_price, hosp_price, ext_price) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$room_name, $bed_no, $nhis_price, $hosp_price, $ext_price]);
                }
            }

            $i++;
        }

        fclose($handle);

        echo "<div align='center'>
                <h2 style='color: blue;'>Records processed</h2>
                <hr>
                <a href='index.php?updown' class='btn btn-danger btn-xs'>[ Close ]</a>";
        if ($errors != '') echo "<hr><h6>Some rows had errors:</h6><p>$errors</p>";
        echo "</div>";
    } else {
        die("Error opening file");
    }
}
