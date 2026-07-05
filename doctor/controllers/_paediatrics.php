<?php session_start();
include("../../Connections/Conn.php");
include('../objects.php');
include('../helpers.php');
header('Content-Type: application/json');


// if (isset($_POST['action'])) {
    if ($_POST['action'] == 'regData') {

        $HIV_initial_test = $_POST['HIV_initial_test'];
        $HIV_initial_test_rslt = $_POST['HIV_initial_test_rslt'];
        $birth_weight = $_POST['birth_weight'];
        $gestation_at_birth = $_POST['gestation_at_birth'];
        $neonatal_comp = $_POST['neonatal_comp'];
        $delivery_mode = $_POST['delivery_mode'];
        $breastfeeding = $_POST['breastfeeding'];
        $dura = $_POST['dura'];
        $past_other = $_POST['past_other'];
        $TB_drugs = $_POST['TB_drugs'];
        $TB_date = $_POST['TB_date'];
        $TB_type = $_POST['TB_type'];
        // $current_med = $_POST['current_med'];
        $other_med = $_POST['other_med'];
        $drug_allergies = $_POST['drug_allergies'];
        $hospital_no = $_POST['hospital_no'];

        $past_med_hx = null;
        $current_med = null;
        if (isset($_POST['past_med_hx'])) {
            $past_med_hx_arr = $_POST['past_med_hx'];
            $past_med_hx = '';
            for ($i = 0; $i < count($past_med_hx_arr); $i++) {
                if ($i > 0) {
                    $past_med_hx .= ',';
                }
                $past_med_hx .= $past_med_hx_arr[$i];
            }
        }

        if (isset($_POST['current_med'])) {
            $current_med_arr = $_POST['current_med'];
            $current_med = '';
            for ($i = 0; $i < count($current_med_arr); $i++) {
                if ($i > 0) {
                    $current_med .= ',';
                }
                $current_med .= $current_med_arr[$i];
            }
        }


        $paediatric_history = $Paediatric->get(['hospital_no' => $hospital_no]);


        if (empty($paediatric_history)) {

            $insert = $db->prepare(" INSERT INTO paediatrics 
    (
    hospital_no,
    HIV_initial_test,
    HIV_initial_test_rslt,
    birth_weight,
    gestation_at_birth,
    neonatal_comp,
    delivery_mode,
    breastfeeding,
    past_med_hx,
    past_other,
    TB_drugs,
    TB_date,
    TB_type,
    current_med,
    other_med,
    drug_allergies
    ) 
    VALUES  ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) 
    ");
            $insert->execute(
                array(
                    $hospital_no,
                    $HIV_initial_test,
                    $HIV_initial_test_rslt,
                    $birth_weight,
                    $gestation_at_birth,
                    $neonatal_comp,
                    $delivery_mode,
                    $breastfeeding,
                    $past_med_hx,
                    $past_other,
                    $TB_drugs,
                    $TB_date,
                    $TB_type,
                    $current_med,
                    $other_med,
                    $drug_allergies
                )
            );
        } else {

            $insert = $db->prepare(" UPDATE  paediatrics SET 
    HIV_initial_test = ?,
    HIV_initial_test_rslt = ?,
    birth_weight = ?,
    gestation_at_birth = ?,
    neonatal_comp = ?,
    delivery_mode = ?,
    breastfeeding = ?,
    past_med_hx = ?,
    past_other = ?,
    TB_drugs = ?,
    TB_date = ?,
    TB_type = ?,
    current_med = ?,
    other_med = ?,
    drug_allergies = ? WHERE id = ?");
            $insert->execute(
                array(
                    $HIV_initial_test,
                    $HIV_initial_test_rslt,
                    $birth_weight,
                    $gestation_at_birth,
                    $neonatal_comp,
                    $delivery_mode,
                    $breastfeeding,
                    $past_med_hx,
                    $past_other,
                    $TB_drugs,
                    $TB_date,
                    $TB_type,
                    $current_med,
                    $other_med,
                    $drug_allergies,
                    $paediatric_history->id
                )
            );
        }

       
        $status = $insert ? 200 : 401;
        $message = $insert ? "Saved Successfully..." : "Oops! Something went wrong...";
        echo json_encode(["status" => $status, "message" => $message, 'hospital_no' => $hospital_no]);
        exit;
    }




    //////////////// FOLLOW UP DATA
    if ($_POST['action'] == 'saveData') {
        $PC = $_POST['PC'];
        $HPC = $_POST['HPC'];
        $hospital_no = $_POST['hospital_no'];
        $appointment_number = $_POST['appointment_number'];
        $message = null;

        $noteS = $PC . '  ' . $HPC;

        if (isset($_POST['review_of_system'])) {
            if (count($_POST['review_of_system']) > 0) {
                $ros_string = '';
                for ($i = 0; $i < count($_POST['review_of_system']); $i++) {
                    if ($i > 0) {
                        $ros_string .= ', ';
                    }
                    $ros_string .= $_POST['review_of_system'][$i];
                }

                $ros_ = $CdRemark->get(['app_no' => $appointment_number, 'hospital_no' => $hospital_no ,'cat_type' => 'R', 'prepared_by' => $_SESSION['fullname']]);
                if(empty($ros_)){
                    $stmt = $db->prepare("INSERT INTO  c_d_remarks (app_no, hospital_no,complain,cat_type,prepared_by,date_entry)  VALUES (?, ?, ?, ?, ?, now())");
                    $stmt->execute(
                        array(
                            $appointment_number,
                            $hospital_no,
                            $ros_string,
                            'R',
                            $_SESSION['fullname']
    
                        )
                    );
                }else{
                    $stmt = $db->prepare("UPDATE  c_d_remarks SET complain = ? WHERE sn = ?   ");
                    $stmt->execute(
                        array(
                            $ros_string,
                            $ros_->sn
    
                        )
                    );
                }
            
            } else {
                $message = 'You need to review the patient system...';
                echo json_encode(["status" => 401, "message" => $message]);
                exit;
            }
        } else {
            $message = 'You need to review the patient system...';
            echo json_encode(["status" => 401, "message" => $message]);
            exit;
        }


        $note_ = $Note->get(['app_no' => $appointment_number, 'hospital_no' => $hospital_no]);

        if(empty($note_)){
            $stmt = $db->prepare("INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry)  VALUES (?, ?, ?, ?, ?, ?, now())");
            $save = $stmt->execute(
                array(
                    $appointment_number,
                    $hospital_no,
                    $noteS,
                    'DR',
                    'C',
                    $_SESSION['fullname']
                )
            );
        }else{
            $stmt = $db->prepare("UPDATE notes  SET notes = ? where sn = ?");
            $save = $stmt->execute(
                array(
                    $noteS,
                    $note_->sn
                )
            );
        }
     

        $status = $save ? 200 : 401;
        $message = $save ? "Saved Successfully..." : 'Oops! Failed';
        echo json_encode(["status" => $status, "message" => $message, 'hospital_no' => $hospital_no]);
        exit;
    }
// }
