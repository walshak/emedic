<?php


function request()
{
    return  json_decode(file_get_contents('php://input'));
}

function post_request()
{
    return  json_decode(json_encode($_POST));
}
///  $lab_reqno = generateRequestNo($db, $code, $request->appointment_number, $request->hospital_no);
function generateRequestNo($db, $code, $app_no = null, $hospital_no, $combo)
{

    //if($app_no == null){ //// incase the function is used somewhere without passing app_no
    //  ;
    // }
    $app_no = rand(10000, 9999999);
    $qty = 1;

    $C = ($combo == 1) ? "C" : "S";

    $stmt = $db->prepare('SELECT sn FROM lab_manage order by sn DESC limit 1');
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $sn = 1;
    } else {
        $roww = $stmt->fetch(PDO::FETCH_ASSOC);
        $sn = $roww['sn'] + 1;
    }
    // return $lab_reqno = $code . date('d').$app_no.sprintf('%08d', $sn).$qty;

    $concat = $app_no . $sn . $hospital_no;
    return $lab_reqno = $code .  $C  . date('d') . $concat . $qty;

    // return $lab_reqno = $code . date('m') . sprintf('%04d', $sn);
    // $lab_reqno = $code . date('d').$app_no.sprintf('%08d', $sn).$x;

}



function cleanInput($data)
{
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    $data = trim($data);
    return $data;
}



function genGroupId($db, $hosp_no)
{
    $today = date('Y-m-d');

    $stmt = $db->prepare("SELECT app_no FROM admission WHERE adm_status='3' and hospital_no='$hosp_no' order by sn desc limit 1");
    $stmt->execute(array($hosp_no));
    if ($stmt->rowCount() > 0) {
        $row_adm = $stmt->fetch(PDO::FETCH_ASSOC);
        $adm_status = 1;
        $adm_code = $row_adm['app_no'];
    } else {
        $adm_status = 0;
    }

    $stmt = $db->prepare('SELECT * FROM lab_manage WHERE patient=? and date(request_date)=?');
    $stmt->execute(array($hosp_no, $today));
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($adm_status == '0') {
            /// lab was added before
            $roww = $stmt->fetch(PDO::FETCH_ASSOC);
            $group_id = $roww['group_id'];
        } elseif ($adm_status == '1') {
            $group_id = 'ADM_' . $adm_code;
        } else {
            $group_id = date('d') . '' . mt_rand(10000, 99999);
        }
    } else {
    }
}

function save_patient_ap_service(
    $db,
    $appointment_number,
    $hospital_no,
    $patient_access_type,
    $serv_group,
    $cat_type,
    $dept_id,
    $drug_sn,
    $item_services,
    $hosp_price,
    $claim_amt,
    $ccop_int_charge,
    $invoice_status,
    $invoice_no,
    $prepared_by,
    $amt_paying,
    $pay_mode,
    $med_frequency = null,
    $med_dosage = null,
    $med_dosage_unit = null,
    $med_duration = null,
    $med_duration_unit = null,
    $remarks = '',
    $allow_duplicate = false,
    $cr = 0,
    $prescription = null,
    $paystatus = 0

) {
    //$prepared_bby='Doctor';
    $save = false;
    $check = $db->prepare('SELECT sn FROM patient_ap_services 
	WHERE app_no = ? AND hospital_no = ? AND serv_group = ? AND drug_sn = ? AND prepared_by = "' . $prepared_by . '" AND paystatus = "0" ');
    $check->execute(array($appointment_number, $hospital_no, $serv_group, $drug_sn));



    if ($amt_paying > 0 and $cat_type != 'Pharmacy') {
        $invoice_status = 1;
        $invoice_by = $prepared_by;
        $invoice_date = date('Y-m-d H:i:s');
        $invoice_no = $appointment_number . '' . mt_rand(100, 999);
    } else {
        $invoice_status = 0;
        $invoice_date = NULL;
        $invoice_by = null;
    }



    if ($check->rowCount() == 0 || $allow_duplicate == true) {
        $stmt = $db->prepare("INSERT INTO patient_ap_services (app_no,hospital_no,access,serv_group,cat_type,dept_id,drug_sn,item_services,tag,hosp_price,claim_amt,interest,qty,remarks,
          drug_status,invoice_status,invoice_no,invoice_date,invoice_by,prepared_by,transact_date,pay,pay_mode,paystatus,process_claim, med_frequency, med_dosage, med_dosage_unit, med_duration, med_duration_unit, created_by, cr, prescription) 
          VALUES  (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ");

        $date = date('Y-m-d');
        $save = $stmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $patient_access_type,
                $serv_group,
                $cat_type,
                $dept_id,
                $drug_sn,
                $item_services,
                'drug',
                $hosp_price,
                $claim_amt,
                $ccop_int_charge,
                1,
                $remarks,
                '0',
                $invoice_status,
                $invoice_no,
                $invoice_date,
                $invoice_by,
                $prepared_by,
                NULL,
                $amt_paying,
                $pay_mode,
                $paystatus,
                '0',
                $med_frequency,
                $med_dosage,
                $med_dosage_unit,
                $med_duration,
                $med_duration_unit,
                $_SESSION['id'],
                $cr,
                $prescription

            )
        );
        if ($save === true) {
            $id = $db->lastInsertId();
            return json_decode(json_encode(['status' => 200, 'message' => 'Save successfully..', 'id' => $id]));
        } else {
            return json_decode(json_encode(['status' => 401, 'message' => 'Failed...', 'id' => null]));
        }
    } else {

        return json_decode(json_encode(['status' => 401, 'message' => 'Already saved before', 'id' => null]));
    }
}
function save_patient_ap_service_medication(
    $db,
    $appointment_number,
    $hospital_no,
    $patient_access_type,
    $serv_group,
    $cat_type,
    $dept_id,
    $dept_dispensory_id,
    $dispensory_status,
    $drug_sn,
    $item_services,
    $hosp_price,
    $claim_amt,
    $ccop_int_charge,
    $invoice_status,
    $invoice_no,
    $prepared_by,
    $amt_paying,
    $pay_mode,
    $med_frequency = null,
    $med_dosage = null,
    $med_dosage_unit = null,
    $med_duration = null,
    $med_duration_unit = null,
    $remarks = '',
    $allow_duplicate = false,
    $cr = 0,
    $prescription = null,
    $qty,
    $paystatus = 0
) {
    try {
        // Enable detailed error tracking
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Duplicate check
        $check = $db->prepare("
            SELECT sn FROM patient_ap_services 
            WHERE app_no = ? AND hospital_no = ? AND serv_group = ? AND drug_sn = ? 
            AND prepared_by = ? AND paystatus = 0
        ");
        $check->execute(array($appointment_number, $hospital_no, $serv_group, $drug_sn, $prepared_by));

        $invoice_status = 0;
        $invoice_by = null;
        $date_entry = date('Y-m-d H:i:s');

        if ($check->rowCount() == 0 || $allow_duplicate == true) {
            $stmt = $db->prepare("
                INSERT INTO patient_ap_services (
                    app_no, hospital_no, access, serv_group, cat_type, dept_id, dept_dispensory_id,
                    dispensory_status_at_phamcy, drug_sn, item_services, tag, hosp_price, claim_amt,
                    interest, qty, remarks, drug_status, invoice_status, invoice_no, invoice_by,
                    prepared_by,date_entry, pay, pay_mode, paystatus, process_claim, med_frequency, med_dosage,
                    med_dosage_unit, med_duration, med_duration_unit, created_by, cr, prescription
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            $save = $stmt->execute(array(
                $appointment_number,
                $hospital_no,
                $patient_access_type,
                $serv_group,
                $cat_type,
                $dept_id,
                $dept_dispensory_id,
                $dispensory_status,
                $drug_sn,
                $item_services,
                'drug',
                $hosp_price,
                $claim_amt,
                $ccop_int_charge,
                $qty,
                $remarks,
                '0',
                $invoice_status,
                $invoice_no,
                $invoice_by,
                $prepared_by,
                $date_entry,
                $amt_paying,
                $pay_mode,
                $paystatus,
                '0',
                $med_frequency,
                $med_dosage,
                $med_dosage_unit,
                $med_duration,
                $med_duration_unit,
                $_SESSION['id'],
                $cr,
                $prescription
            ));

            if ($save) {
                $id = $db->lastInsertId();
                return ['status' => 200, 'message' => 'Save successfully..', 'id' => $id];
            } else {
                $errorInfo = implode(' | ', $stmt->errorInfo());
                return ['status' => 401, 'message' => 'Database error: ' . $errorInfo, 'id' => null];
            }
        } else {
            return ['status' => 401, 'message' => 'Already saved before', 'id' => null];
        }
    } catch (PDOException $e) {
        return ['status' => 500, 'message' => 'PDO Exception: ' . $e->getMessage(), 'id' => null];
    } catch (Exception $e) {
        return ['status' => 500, 'message' => 'General Error: ' . $e->getMessage(), 'id' => null];
    }
}


function new_service_amount_cal(
    $db,
    $hos_no,
    $appointment_number,
    $interest,
    $insurance,
    $insurance_no,
    $hosp_price,
    $ext_price,
    $nhis_price,
    $service_access,
    $add_minus,
    $payment_mode,
    $_tariff_table,
    $target_sn,
    $NHIS_DRUG_CONSUMBL_STATE,
    $ext = false // extra flag to determine include path
) {
    $invoice_no = $appointment_number . mt_rand(100, 999);

    // Choose correct path
    if ($ext) {
        include("../inc/price_calc.php");  // extension version
    } else {
        include("../../inc/price_calc.php"); // default version
    }

    return [
        'claim_amt' => $claim_amt,
        'amount_paying' => $amt_paying,
        'pay_mode' => $pay_mode,
        'invoice_no' => $invoice_no,
        'ccop_int_charge' => $ccop_int_charge,
        'item_amt' => $item_amt
    ];
}


function date_diff_day($date_)
{
    $last = new DateTime("$date_");
    $now = new DateTime("now");
    $days_last = $last->diff($now);
    return $days_last->format('%a');
}

function dateFormat_($date_)
{
    $var = substr("$date_", 0, 10);
    return date('d, M Y', strtotime($var));
}

function formatDateTime_($date_)
{
    $var = substr("$date_", 0, 10);
    return date('d, M Y h:i A', strtotime("$date_"));
}


function dateDifference_array($date1)
{
    $start_date = new DateTime("$date1");

    $current_dat_time = date('Y-m-d H:i');
    $since_start = $start_date->diff(new DateTime("$current_dat_time"));

    return ['year' => $since_start->y, 'day' => $since_start->d, 'month' => $since_start->m, 'minute' => $since_start->i, 'second' => $since_start->s];
}

function lockApppointment($db, $appt_no)
{
    /// lock the appointment
    $lockStmt = $db->prepare("UPDATE apptm SET queue_lock = 1, doctor_id = ? WHERE appt_no = ? ");
    return $lockStmt->execute(array($appt_no, $_SESSION["id"]));
}


function dateDifference_format($date_)
{
    $start_date = new DateTime("$date_");

    $current_dat_time = date('Y-m-d H:i:s');
    $since_start = $start_date->diff(new DateTime("$current_dat_time"));
    // return $since_start->days.' days total<br>';

    if ($since_start->y > 0) {
        return $since_start->y . 'yrs ' . $since_start->m . 'mnth ' . $since_start->d . 'dys ' . $since_start->h . 'hr ';
    } else if ($since_start->m > 0) {

        return $since_start->m . 'mnth ' . $since_start->d . 'dys ';
    } else if ($since_start->d > 0) {
        return $since_start->d . 'dys ' . $since_start->h . 'hrs ';
    } else if ($since_start->h > 0) {
        return $since_start->h . 'hrs ' . $since_start->i . 'min ';
    } else {
        return $since_start->i . 'min ' . $since_start->s . 'sec ';
    }
}

function editNotes($db, $id, $notes, $date_entry, $date_entry2)
{


    if ($date_entry2 == '') {
        $update_dated = $date_entry;
    } else {
        $update_dated = $date_entry2;
    }

    $check = $db->prepare("SELECT * FROM notes WHERE sn = ? ");
    $check->execute(array($id));
    if ($check->rowCount() > 0) {
        $row = $check->fetch(PDO::FETCH_ASSOC);

        $prev_note = "";
        $last_notes = $db->prepare("SELECT * FROM notes WHERE app_no = ? AND hospital_no = ? AND notes_type = ? ORDER BY sn desc LIMIT 1");
        $last_notes->execute(array($row['app_no'], $row['hospital_no'], $row['notes_type']));
        if ($last_notes->rowCount() > 0) {
            $row2 = $last_notes->fetch(PDO::FETCH_ASSOC);

            $prev_note = $row2['notes'];
        }


        $plainText = strip_tags($notes);
        $safeText = htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8');
        $wordCount = str_word_count($safeText);

        if ($notes != $prev_note && $notes != '' && $wordCount >= 5) {
            $update = $db->prepare("UPDATE  notes SET status = '0' WHERE sn = ?");
            $update->execute(array($id));


            $stmt = $db->prepare("SELECT COUNT(*) FROM notes WHERE date_entry2 = ? AND hospital_no =?");
            $stmt->execute(array($date_entry2, $row['hospital_no']));
            $rowCount = $stmt->fetchColumn() + 1;


            $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, service_id,date_entry2,no_updates,dept_id)  VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?, ?, ?,?)');
            return $stmt->execute(
                array(
                    $row['app_no'],
                    $row['hospital_no'],
                    $notes,
                    $row['tag'],
                    $row['notes_type'],
                    $_SESSION['fullname'],
                    $row['specialty'],
                    $_SESSION['id'],
                    $row['service_id'],
                    $update_dated,
                    $rowCount,
                    $_SESSION['dept_id'],
                )
            );
        } else {
            return false;
        }
    }
    return false;
}

function saveToNotes($db, $appointment_number, $hospital_no, $notes, $tag, $notes_type, $prepared_by, $specialty = null, $created_by = null, $save_direct = false, $service_id = null)
{

    $dept_id = $_SESSION['dept_id'];
    $now = date('Y-m-d H:i:s');

    if ($specialty != null) {
        $check = $db->prepare("SELECT sn, notes FROM notes 
		WHERE app_no = ? AND hospital_no = ? AND notes_type = ? AND specialty = ? AND service_id = ? AND created_by = ?  AND status = '1' LIMIT 1");
        $check->execute(array($appointment_number, $hospital_no, $notes_type, $specialty, $service_id, $created_by));
    } else {
        $check = $db->prepare("SELECT sn, notes FROM notes WHERE app_no = ? AND hospital_no = ? AND notes_type = ? AND service_id = ? AND created_by = ? AND  status = '1' LIMIT 1");
        $check->execute(array($appointment_number, $hospital_no, $notes_type, $service_id, $created_by));
    }

    if ($check->rowCount() > 0 && $save_direct == false) {
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['notes'])) {
            if ($row['notes'] != $notes) {
                $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, service_id,dept_id)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)');
                $stmt->execute(
                    array(
                        $appointment_number,
                        $hospital_no,
                        $notes,
                        $tag,
                        $notes_type,
                        $prepared_by,
                        $now,
                        $specialty,
                        $created_by,
                        $service_id,
                        $dept_id
                    )
                );

                $check = $db->prepare("UPDATE notes SET status = '0' WHERE sn = ? ");
                return  $check->execute(array($row['sn']));
            } else {
                return true;
            }
        } else {
            return false;
        }
    } else {
        $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, service_id,dept_id)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)');
        return $stmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $notes,
                $tag,
                $notes_type,
                $prepared_by,
                $now,
                $specialty,
                $created_by,
                $service_id,
                $dept_id
            )
        );
    }
}

function saveToNotesOld($db, $appointment_number, $hospital_no, $notes, $tag, $notes_type, $prepared_by, $specialty = null, $created_by = null)
{

    $now = date('Y-m-d H:i:s');

    $check = $db->prepare("SELECT sn, notes FROM notes WHERE app_no = ? AND hospital_no = ? AND notes_type = ? and status = '1' LIMIT 1");
    $check->execute(array($appointment_number, $hospital_no, $notes_type));

    if ($check->rowCount() > 0) {
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['notes'])) {
            if ($row['notes'] != $notes) {
                $notes = $row['notes'] . '  <b>Edited to:</b> ' . $notes;
            }

            $check = $db->prepare('UPDATE notes SET notes = ?, date_entry = ?, prepared_by = ? WHERE app_no = ? AND hospital_no = ? AND notes_type = ? ');
            return  $check->execute(array($notes, $now, $prepared_by, $appointment_number, $hospital_no, $notes_type));
        } else {
            return false;
        }
    } else {
        $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $notes,
                $tag,
                $notes_type,
                $prepared_by,
                $now,
                $specialty,
                $created_by
            )
        );
    }
}

function saveToRemarks($db, $appointment_number, $hospital_no, $complain, $cat_type, $prepared_by)
{
    $now = date('Y-m-d H:i:s');
    $check = $db->prepare("SELECT sn, complain FROM c_d_remarks WHERE app_no = ? AND hospital_no = ? AND cat_type = ? and status = '1' ORDER BY sn DESC LIMIT 1  ");
    $check->execute(array($appointment_number, $hospital_no, $cat_type));

    if ($check->rowCount() > 0) {
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['complain'])) {
            if ($row['complain'] != $complain) {
                $stmt = $db->prepare('INSERT INTO  c_d_remarks (app_no, hospital_no,complain,cat_type,prepared_by, date_entry)  VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute(
                    array(
                        $appointment_number,
                        $hospital_no,
                        $complain,
                        $cat_type,
                        $prepared_by,
                        $now

                    )
                );

                $check = $db->prepare("UPDATE c_d_remarks SET status = '0' WHERE sn = ? ");
                return  $check->execute(array($row['sn']));
            } else {
                return true;
            }
            // $check = $db->prepare( 'UPDATE c_d_remarks SET complain = ?, date_entry = now(), prepared_by = ? WHERE app_no = ? AND hospital_no = ? AND cat_type = ? ' );
            // return  $check->execute( array( $complain, $prepared_by, $appointment_number, $hospital_no, $cat_type ) );
        } else {
            return false;
        }
    } else {
        $stmt = $db->prepare('INSERT INTO  c_d_remarks (app_no, hospital_no,complain,cat_type,prepared_by, date_entry)  VALUES (?, ?, ?, ?, ?,?)');
        return $stmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $complain,
                $cat_type,
                $prepared_by,
                $now

            )
        );
    }
}

function save_complains($db, $appointment_number, $hospital_no, $group, $cat, $item, $selected, $status, $answer, $comment)
{

    $check = $db->prepare('SELECT sn FROM management_complains WHERE app_no = ? AND hospital_no = ? AND cat = ? AND item = ? ');
    $check->execute(array($appointment_number, $hospital_no, $cat, $item));

    if ($check->rowCount() > 0) {
        $check = $db->prepare('UPDATE management_complains SET answer = ?, comment =?  WHERE app_no = ? AND hospital_no = ? AND cat = ? AND item = ? ');
        return  $check->execute(array($answer, $comment, $appointment_number, $hospital_no, $cat, $item));
    } else {

        $stmt = $db->prepare('INSERT INTO  management_complains (app_no, hospital_no,group_name, cat, item, selected, status, answer, comment)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $group,
                $cat,
                $item,
                $selected,
                $status,
                $answer,
                $comment
            )
        );
    }
}

function getToken($length)
{
    $token = '';
    $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
    $codeAlphabet .= '0123456789';
    $max = strlen($codeAlphabet);
    // edited

    for ($i = 0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max - 1)];
    }

    return $token;
}

function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min;
    // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1;
    // length in bytes
    $bits = (int) $log + 1;
    // length in bits
    $filter = (int) (1 << $bits) - 1;
    // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter;
        // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}


function months_array()
{
    return json_decode(json_encode([
        ['index' => 1, 'mnth' => 'Jan', 'month' => 'January', 'value' => '01'],
        ['index' => 2, 'mnth' => 'Feb', 'month' => 'February', 'value' => '02'],
        ['index' => 3, 'mnth' => 'Mar', 'month' => 'March', 'value' => '03'],
        ['index' => 4, 'mnth' => 'Apr', 'month' => 'April', 'value' => '04'],
        ['index' => 5, 'mnth' => 'May', 'month' => 'May', 'value' => '05'],
        ['index' => 6, 'mnth' => 'Jun', 'month' => 'June', 'value' => '06'],
        ['index' => 7, 'mnth' => 'Jul', 'month' => 'July', 'value' => '07'],
        ['index' => 8, 'mnth' => 'Aug', 'month' => 'August', 'value' => '08'],
        ['index' => 9, 'mnth' => 'Sep', 'month' => 'September', 'value' => '09'],
        ['index' => 10, 'mnth' => 'Oct', 'month' => 'October', 'value' => '10'],
        ['index' => 11, 'mnth' => 'Nov', 'month' => 'November', 'value' => '11'],
        ['index' => 12, 'mnth' => 'Dec', 'month' => 'December', 'value' => '12']
    ]));
}
