<?php
session_start();
include('../Connections/Conn.php');
include('../doctor/objects.php');
include('../doctor/helpers.php');

if (isset($_POST['merge_hosp_no_preview'])) {

    $delete_hospital_number = cleanInput($_POST['delete_hospital_number']);
    $correct_number = cleanInput($_POST['correct_number']);

    if ($delete_hospital_number != $correct_number) {
        $patient_info1 = $Patient->getByHospitalNo($delete_hospital_number);

        if (!empty($patient_info1)) {
            $patient_info2 = $Patient->getByHospitalNo($correct_number);

            if (!empty($patient_info2)) {
                $patient_name1 =  $patient_info1->surname . ' ' . $patient_info1->fname . ' ' . $patient_info1->oname . ' (' . $patient_info1->insurance_type . ') ';
                $patient_name2 =  $patient_info2->surname . ' ' . $patient_info2->fname . ' ' . $patient_info2->oname . ' (' . $patient_info2->insurance_type . ') ';
?>
                <div>
                    <h4><b>Patient whose record is to be merged is: </b> <?= $patient_name1; ?> </h4>
                    <h4><b>Patient to be merged with: </b> <?= $patient_name2; ?> </h4>
                    <p class="text-danger"><B>Note: Please confirm as reversal won't be possible</B></p>
                </div>
<?php
            } else {
                echo '<h4>Failed to find hospital No. [ ' . $correct_number . ' ]</h4>';
            }
        } else {
            echo '<h4>Failed to find hospital No. [ ' . $delete_hospital_number . ' ]</h4>';
        }
    } else {
        echo '<h4>Hospital numbers cannot be the same for merging.</h4>';
    }
    exit;
}


if (isset($_POST['merge_hosp_no'])) {
    header('Content-Type: application/json');
    $response = [
        'status' => 200,
        'message' => 'Merge failed'
    ];

    $delete_hospital_number = cleanInput($_POST['delete_hospital_number']);
    $correct_number = cleanInput($_POST['correct_number']);

    if ($delete_hospital_number != $correct_number) {

        // Use a prepared statement
        $stmtxx = $db->prepare("SELECT * FROM enrollee WHERE hospital_no = ?");
        $stmtxx->execute([$delete_hospital_number]);
        $row = $stmtxx->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $patient_id = $row['sn'];
            $info = "SN: {$row['sn']}, NHIS NO: {$row['nhis_no']}, HMO NO: {$row['hmo_no']}, Insurance: {$row['insurance']}, " .
                "Surname: {$row['surname']}, First Name: {$row['fname']}, Other Name: {$row['oname']}, Full Name: {$row['fullname']}, " .
                "Occupation: {$row['occupation']}, Gender: {$row['gender']}, Marital Status: {$row['marital_status']}, " .
                "Blood Group: {$row['blood_g']}, Genotype: {$row['geno_type']}, Date of Birth: {$row['dob']}, Age: {$row['age']}, " .
                "Phone: {$row['phone']}, Email: {$row['email']}, State/LGA: {$row['state_lga']}, Tribe: {$row['tribe']}, " .
                "Nationality: {$row['nationality']}, Address: {$row['addr']}, Religion: {$row['religion']}, " .
                "Captured By: {$row['captured_by']}, Credit Limit: {$row['credit_limit']}, VIP: {$row['vip']}.";

            $remark_for_marger = "Deleted INFO : " . $info . '<BR>Corrected No.: ' . $correct_number . '<br>' . cleanInput($_POST['remark_for_marger']);

            $correct_info = $Patient->getByHospitalNo($correct_number);
            $patient_name = $correct_info->surname . ' ' . $correct_info->fname . ' ' . $correct_info->oname;
            $hmo_no = $correct_info->hmo_no;

            $tables_to_update = [
                ["apptm", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["apptm", "patient_name", "hospital_no = ?", [$patient_name, $delete_hospital_number]],
                ["admission", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["discharge_fellowup", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["notes", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["lab_manage", "patient", "patient = ?", [$correct_number, $delete_hospital_number]],
                ["lab_manage", "patient_name", "patient = ?", [$patient_name, $delete_hospital_number]],
                ["c_d_remarks", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["patient_ap_services", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["dialysis", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["dialysis", "patient_name", "hospital_no = ?", [$patient_name, $delete_hospital_number]],
                ["transplants", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["transplants", "patient_name", "hospital_no = ?", [$patient_name, $delete_hospital_number]],
                ["notes_services", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["diagnosis_tracking", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["drug_charts", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["drug_charts_inven", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["fbs_rbs", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["feedings", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["fluidchart", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["immunization", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["immunization_vaccines", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["intake_out", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["ivf_form", "ivf_hosp_no", "ivf_hosp_no = ?", [$correct_number, $delete_hospital_number]],
                ["manage_patients_vip_staff", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["oxygen", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["oxygen_consumption_chart", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["patients_documents", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["patients_remarks_tbl", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["patient_admission_note", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["pharm_drug_reminder", "hos_no", "hos_no = ?", [$correct_number, $delete_hospital_number]],
                ["pharm_doctor_notes", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["physical_examination", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["procedures", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["procedure_note", "hosp_no", "hosp_no = ?", [$correct_number, $delete_hospital_number]],
                ["procedure_resources", "hosp_no", "hosp_no = ?", [$correct_number, $delete_hospital_number]],
                ["progress_note", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["seizure_chart", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["tbl_patient_alerts", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["transplants_donors", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["vouchers_inventory", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["chart_ledger", "hospital_no", "hospital_no = ?", [$correct_number, $delete_hospital_number]],
                ["chart_ledger", "insurance_no", "hospital_no = ?", [$hmo_no, $delete_hospital_number]],
                ["consultations", "hosp_no", "hosp_no = ?", [$correct_number, $delete_hospital_number]],
                ["consultations", "patient_name", "hosp_no = ?", [$patient_name, $delete_hospital_number]],
            ];

            foreach ($tables_to_update as $t) {
                list($table, $field, $condition, $params) = $t;
                $db->prepare("UPDATE {$table} SET {$field} = ? WHERE {$condition}")->execute($params);
            }

            // Delete from enrollee and guardian
            $db->prepare("DELETE FROM enrollee WHERE hospital_no = ?")->execute([$delete_hospital_number]);
            $db->prepare("DELETE FROM guardian_tbl WHERE patient_id = ?")->execute([$patient_id]);

            // Insert merge remark
            $stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no, service_list, total_amount, remark, staff_name) 
                VALUES (:hospital_no, :list_desc, '', :remark, :staff_name)");
            $stmt->bindParam(':hospital_no', $correct_number);
            $stmt->bindValue(':list_desc', null, PDO::PARAM_NULL);
            $stmt->bindParam(':remark', $remark_for_marger);
            $stmt->bindParam(':staff_name', $_SESSION['fullname']);
            $stmt->execute();

            // Insert merge log
            $sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn, descriptions, staff_name, patient_id, action, date_and_time) 
                VALUES (:item_sn, :descriptions, :staff_name, :patient_id, :action, :date_and_time)");
            $now = date("Y-m-d H:i:s");
            $merge_label = "Merged hospital Nos";
            $sql->bindValue(':item_sn', null, PDO::PARAM_NULL);
            $sql->bindParam(':descriptions', $remark_for_marger);
            $sql->bindParam(':staff_name', $_SESSION['fullname']);
            $sql->bindParam(':patient_id', $correct_number);
            $sql->bindParam(':action', $merge_label);
            $sql->bindParam(':date_and_time', $now);
            $sql->execute();

            $response['message'] = 'Merged successfully.';
        } else {
            $response['message'] = 'Delete hospital number not found.';
        }
    } else {
        $response['message'] = 'Hospital numbers cannot be the same.';
    }

    echo json_encode($response);
    exit;
}
?>