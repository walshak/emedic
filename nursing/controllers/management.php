<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');



if (isset($_POST['checkMgtNotes'])) {
    $appointment_number = $_POST['appointment_number'];
    $hospital_no = $_POST['hospital_no'];
    $message =  'Management notes and complains not taken yet';
    $is_saved = false;


    $stmt = $db->prepare("SELECT * FROM notes WHERE app_no = ? AND hospital_no = ? AND notes_type = 'mgt' ");
    $stmt->execute(array($appointment_number, $hospital_no));
        if($stmt->rowCount() > 0){
            $is_saved = true;
            $message =  'Management notes and complains not taken yet';
        }
    echo json_encode(["status" => 200, "is_saved" => $is_saved, "message" => $message]);
exit;
}

if (isset($_POST['saveManagementData'])) {
    $appointment_number = $_POST['appointment_number'];
    $hospital_no = $_POST['hospital_no'];
    $complains = $_POST['complains'];
    $complains2 = $_POST['complains2'];
    $review_of_system = $_POST['review_of_system'];
    $physical_examinations = $_POST['physical_examinations'];
    $diagnosis = $_POST['diagnosis'];
    $first_visit = $_POST['first_visit'];

    $social_histories = json_decode(json_encode($_POST['social_histories']));
    $selected_allergies = json_decode(json_encode($_POST['selected_allergies']));

    $social_history_string = '';
    $allergies_string = '';

    /// complains
    $saveComplains = saveToNotes($db, $appointment_number, $hospital_no, $complains, 'DR', 'C',  $_SESSION['fullname']);
    $saveComplains = saveToNotes($db, $appointment_number, $hospital_no, $complains2, 'DR', 'HC',  $_SESSION['fullname']);


    if (strlen($diagnosis) > 0) {
        /// diagnosis
        $saveDiagnosis = saveToNotes($db, $appointment_number, $hospital_no, $diagnosis, 'DR', 'D',  $_SESSION['fullname']);
    } else {
        $saveDiagnosis = true;
    }

    /// physical_examinations
    if (!empty($physical_examinations)) {
        $savePhysicalExamination = saveToNotes($db, $appointment_number, $hospital_no, $physical_examinations, 'DR', 'PH',  $_SESSION['fullname']);
    } else {
        $savePhysicalExamination = true;
    }

    /// review_of_system
    $saveReviewOfSystem = saveToRemarks($db, $appointment_number, $hospital_no, $review_of_system, 'R', $_SESSION['fullname']);


    ///  Drop Social Histories :
    foreach ($social_histories as $key => $social_history) {
        $social_history_string .= ' ' . $social_history->cat . ' ' . $social_history->answer . ' - ' . $social_history->comment . ' <br> ';
        save_complains($db, $appointment_number, $hospital_no, "Socail_Histories", $social_history->cat, $social_history->item, null, null, $social_history->answer, $social_history->comment);
    }
    if (!empty($social_history_string)) {
        $saveSocialHistory = saveToRemarks($db, $appointment_number, $hospital_no, $social_history_string, 'sh', $_SESSION['fullname']);
    }



    ///  Drop Allergies :
    foreach ($selected_allergies as $key => $selected_allergy) {
        $allergies_string .=  ' ' . $selected_allergy->item . ':   ' . $social_history->comment . ' <br> ';
        save_complains($db, $appointment_number, $hospital_no, "Allergies", $selected_allergy->cat, $selected_allergy->item, null, null, NULL, $selected_allergy->comment);
    }
    if (!empty($allergies_string)) {
        $saveAllergies = saveToRemarks($db, $appointment_number, $hospital_no, $allergies_string, 'DH', $_SESSION['fullname']);
    }


    $stmt = $db->prepare("UPDATE apptm SET doctor_id = ?, referal_doc = ? WHERE appt_no = ? and hospital_no = ? and doctor_id IS NULL");
    $stmt->execute(array($_SESSION['id'], $_SESSION['username'], $appointment_number, $hospital_no));

    // if ($saveComplains && $saveReviewOfSystem) {
    echo json_encode(["status" => 200, "data" => null, "message" => "Saved"]);
    // } else {
    //     echo json_encode(["status" => 401, "data" => null, "message" => "Failed"]);
    // }
    exit;
}









if (isset($_POST['pickDrugFromGroup'])) {
    $cat = $_POST['cat'];
    $group_id = $_POST['group_id'];
    $hospital_no = $_POST['hospital_no'];
    $appointment_number = $_POST['appointment_number'];

    $appointment_info = $Appointment->get(["appt_no" => $appointment_number, "hospital_no" => $hospital_no]);
    $save = false;

    $medications_in_the_group = $DrugGroup->get(['group_id' => $group_id, 'created_by' => $_SESSION['id']], true);
    foreach ($medications_in_the_group as $key => $medication_in_the_group) {
        $drug_info = $DrugStock->find($medication_in_the_group->drug_id);

        $serv_group = "Pharmacy";
        $cat_type = "Pharmacy";
        $dept_id = 7;
        $drug_sn = $medication_in_the_group->drug_id;
        $item_services = $drug_info->product_name;
        $hosp_price = $drug_info->hosp_price;

        /// Check if the Medication has been saved bfore
        $checkMedicationStmt = $db->prepare("SELECT * FROM  patient_ap_services WHERE app_no = ? AND hospital_no = ? AND drug_sn = ?");
        $checkMedicationStmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $medication_in_the_group->drug_id
            )
        );


        $checkMedicationRows = $checkMedicationStmt->rowCount();
        if ($checkMedicationRows == 0) {
            ///  save if not saved before

            $item_amt = ($appointment_info->insurance == 'NHIS' && $appointment_info->ap_type <= 2) ? $drug_info->nhis_price : $drug_info->hosp_price;
            ///// amount and invoice
            $amount_invoice = service_amount_cal($appointment_number, $appointment_info->interest, $appointment_info->insurance, $appointment_info->ap_type, $item_amt);
            $save = save_patient_ap_service(
                $db,
                $appointment_number,
                $hospital_no,
                $appointment_info->ap_type,
                $serv_group,
                $cat_type,
                $dept_id,
                $drug_sn,
                $item_services,
                $hosp_price,
                $amount_invoice["claim_amt"],
				 $amount_invoice["ccop_int_charge"],
                "",
                $amount_invoice["invoice_no"],
                $_SESSION['fullname'],
                $amount_invoice["amount_paying"],
                $amount_invoice["pay_mode"],
                $medication_in_the_group->frequency,
                $medication_in_the_group->dosage,
                $medication_in_the_group->dosage_unit,
                $medication_in_the_group->duration,
                $medication_in_the_group->duration_unit
            );
        }
    }

    //// SELECTED drugs
    $selected_drugs_stmt = $db->prepare("SELECT sn, drug_sn,med_frequency, med_dosage, med_dosage_unit, med_duration, med_duration_unit,invoice_status, pay as cash_price   FROM patient_ap_services WHERE app_no = ? and hospital_no = ? and serv_group = 'Pharmacy' ");
    $selected_drugs_stmt->execute(array($appointment_number, $hospital_no));
    $selected_drugs_rows = $selected_drugs_stmt->fetchAll(PDO::FETCH_ASSOC);
    $selected_drug_stocks = [];
    foreach ($selected_drugs_rows as $key => $selected_drugs_row) {

        $drug_stmt_row = $DrugStock->find($selected_drugs_row["drug_sn"]);

        $drug_stmt_row->ap_services_id = $selected_drugs_row["sn"];
        $drug_stmt_row->med_dosage = $selected_drugs_row["med_dosage"];
        $drug_stmt_row->med_dosage_unit = $selected_drugs_row["med_dosage_unit"];
        $drug_stmt_row->med_frequency = $selected_drugs_row["med_frequency"];
        $drug_stmt_row->med_duration = $selected_drugs_row["med_duration"];
        $drug_stmt_row->med_duration_unit = $selected_drugs_row["med_duration_unit"];
        $drug_stmt_row->invoice_status = $selected_drugs_row["invoice_status"];
        $drug_stmt_row->cash_price = $selected_drugs_row["cash_price"];
        array_push($selected_drug_stocks, $drug_stmt_row);
    }

    $message = 'Failed';
    $status = 401;
    if (count($selected_drug_stocks) > 0) {
        $message = 'Selected';
        $status = 200;
    }
    echo json_encode(["status" => $status, "data" => $selected_drug_stocks, 'message' => $message]);
    exit;
}


if (isset($_POST['addDrugToGroup'])) {
    $group_id = $_POST['group_id'];
    $drug_id = $_POST['drug_id'];
    $dosage = $_POST['dosage'];
    $dosage_unit = $_POST['dosage_unit'];
    $frequency = $_POST['frequency'];
    $duration = $_POST['duration'];
    $duration_unit = $_POST['duration_unit'];

    $isExist = $DrugGroup->get(['drug_id' => $drug_id, 'group_id' => $group_id]);
    $data = null;
    if (empty($isExist)) {
        $data['group_id'] = $group_id;
        $data['drug_id'] = $drug_id;
        $data['dosage'] = $dosage;
        $data['dosage_unit'] = $dosage_unit;
        $data['frequency'] = $frequency;
        $data['duration'] = $duration;
        $data['duration_unit'] = $duration_unit;
        $data['created_by'] = $_SESSION['id'];

        $saved = $DrugGroup->save($data);
        if ($saved != null) {
            $message = 'Saved';
            $status = 200;
        } else {
            $message = 'Failed';
            $status = 401;
        }
    } else {
        $status = 401;
        $message = "Already Exist";
    }

    echo json_encode(["status" => $status, "data" => $data, 'message' => $message]);
    exit;
}


if (isset($_POST['pickLabFromGroup'])) {
    $cat = $_POST['cat'];
    $group_id = $_POST['group_id'];
    $hospital_no = $_POST['hospital_no'];
    $appointment_number = $_POST['appointment_number'];

    $labs_in_the_group = $LabGroup->get(['group_id' => $group_id, 'created_by' => $_SESSION['id']], true);
    foreach ($labs_in_the_group as $key => $lab_in_the_group) {
        $lab_id = $lab_in_the_group->lab_id;

        $appointment_info = $Appointment->get(["appt_no" => $appointment_number, "hospital_no" => $hospital_no]);
        $save = false;

        $investigation = $Investigation->find($lab_in_the_group->lab_id);

        $category = $investigation->category;
        if ($cat == 'lab') {

            $code = "LB";
        } else {
            $code = "RD";
        }

        $setdate2 = date("Y-m-d");
        $datetime = date("Y-m-d H:i:s");
        $lab_reqno = generateRequestNo($db, $code);
        $lab_cats = ["Laboratory" => 1, "Radiology" => 3];

        /// Check if the Investigation has been saved bfore
        $checkLabManagestmt = $db->prepare("SELECT * FROM  lab_manage WHERE app_no = ? AND patient = ? AND test_id = ?");
        $checkLabManagestmt->execute(
            array(
                $appointment_number,
                $hospital_no,
                $investigation->sn
            )
        );


        $checkLabManageRows = $checkLabManagestmt->rowCount();
        if ($checkLabManageRows == 0) {
            ///  save if not saved before
            $data = json_decode(json_encode([
                'app_no' =>   $appointment_number,
                'labrequest_no' =>  $lab_reqno,
                'patient' =>  $hospital_no,
                'patient_name' => $appointment_info->patient_name,
                'test_id' =>  $investigation->sn,
                'test_name' =>  $investigation->test,
                'lab_cat' =>     $lab_cats[$investigation->category],
                'section' =>   $investigation->category,
                'group_id' =>   genGroupId($db, $hospital_no),
                'preferred_specimen' => $lab_in_the_group->specimen,
                'request_note' =>  $lab_in_the_group->request_note,
                'request_date' =>   $datetime,
                'request_by' =>  $_SESSION['fullname'],
                'lab_combos' => $investigation->combo_test,
                'created_by' => $_SESSION['id']
            ]));

            $Investigation->save($data);


            $serv_group = $investigation->category;
            $cat_type = $investigation->category;
            $dept_id = $investigation->dept;
            $drug_sn = $lab_reqno;
            $item_services = $investigation->test;
            $hosp_price = $investigation->hosp_price;

            $item_amt = ($appointment_info->insurance == 'NHIS' && $appointment_info->ap_type <= 2) ? $investigation->nhis_price : $investigation->hosp_price;
            ///// amount and invoice
            $amount_invoice = service_amount_cal($appointment_number, $appointment_info->interest, $appointment_info->insurance, $appointment_info->ap_type, $item_amt);
            $save = save_patient_ap_service(
                $db,
                $appointment_number,
                $hospital_no,
                $appointment_info->ap_type,
                $serv_group,
                $cat_type,
                $dept_id,
                $drug_sn,
                $item_services,
                $hosp_price,
                $amount_invoice["claim_amt"],
				 $amount_invoice["ccop_int_charge"],
                "",
                $amount_invoice["invoice_no"],
                $_SESSION['fullname'],
                $amount_invoice["amount_paying"],
                $amount_invoice["pay_mode"]
            );
        }
    }

    $category = 'Laboratory';
    if ($cat == 'rad') {
        $category = 'Radiology';
    }
    //// SELECTED investigation
    $selected_labs_stmt = $db->prepare("SELECT sn lab_manage_id, test_id, preferred_specimen specimen,request_note FROM lab_manage WHERE app_no = ? and patient = ? AND section = ? ");
    $selected_labs_stmt->execute(array($appointment_number, $hospital_no, $category));
    $selected_labs_rows = $selected_labs_stmt->fetchAll(PDO::FETCH_ASSOC);
    $selected_labtests = [];

    foreach ($selected_labs_rows as $key => $selected_labs_row) {

        $investigation = $Investigation->find($selected_labs_row["test_id"]);

        $investigation->specimen = $selected_labs_row["specimen"];
        $investigation->request_note = $selected_labs_row["request_note"];
        array_push($selected_labtests, $investigation);
    }

    $message = 'Failed';
    $status = 401;
    if (count($selected_labtests) > 0) {
        $message = 'Selected';
        $status = 200;
    }
    echo json_encode(["status" => $status, "data" => $selected_labtests, 'message' => $message]);
    exit;
}

if (isset($_POST['removeFromGroup'])) {
    $cat = $_POST['cat'];
    $id = $_POST['id'];

    $data['id'] = $id;
    $message = 'Failed';
    $status = 401;

    if ($cat == 'lab' || $cat == 'rad') {
        $deleted = $LabGroup->delete($data);


        if ($deleted) {
            $message = 'Removed';
            $status = 200;
        }
    } else if ($cat == 'drug') {
        $deleted = $DrugGroup->delete($data);


        if ($deleted) {
            $message = 'Removed';
            $status = 200;
        }
    }


    echo json_encode(["status" => $status, "data" => $data, 'message' => $message]);
    exit;
}



if (isset($_POST['addRadToGroup'])) {

    $lab_id = $_POST['lab_id'];
    $group_id = $_POST['group_id'];
    $request_note = $_POST['rad_request_note_'];
    $specimen = null;

    $isExist = $LabGroup->get(['lab_id' => $lab_id, 'group_id' => $group_id]);
    $data = null;
    if (empty($isExist)) {
        $data['lab_id'] = $lab_id;
        $data['group_id'] = $group_id;
        $data['specimen'] = $specimen;
        $data['request_note'] = $request_note;
        $data['created_by'] = $_SESSION['id'];

        $save = $LabGroup->save($data);
        if ($save != null) {
            $message = 'Saved';
            $status = 200;
        } else {
            $message = 'Failed';
            $status = 401;
        }
    } else {
        $status = 401;
        $message = "Already Exist";
    }

    echo json_encode(["status" => $status, "data" => $data, 'message' => $message]);
    exit;
}


if (isset($_POST['addLabToGroup'])) {

    $lab_id = $_POST['lab_id'];
    $group_id = $_POST['group_id'];
    $request_note = $_POST['request_note'];
    $specimen = $_POST['specimen'];

    $isExist = $LabGroup->get(['lab_id' => $lab_id, 'group_id' => $group_id]);
    $data = null;
    if (empty($isExist)) {
        $data['lab_id'] = $lab_id;
        $data['group_id'] = $group_id;
        $data['specimen'] = $specimen;
        $data['request_note'] = $request_note;
        $data['created_by'] = $_SESSION['id'];

        $save = $LabGroup->save($data);
        if ($save != null) {
            $message = 'Saved';
            $status = 200;
        } else {
            $message = 'Failed';
            $status = 401;
        }
    } else {
        $status = 401;
        $message = "Already Exist";
    }

    echo json_encode(["status" => $status, "data" => $data, 'message' => $message]);
    exit;
}

if (isset($_POST['creatCustomGroup'])) {

    $name = $_POST['name'];
    $category = $_POST['category'];



    $isExist = $CustomGroup->get(['name' => $name, 'created_by' => $_SESSION["id"]]);

    if (!empty($isExist)) {
        $status = 401;
        $message = "Already exist";
        $data = null;
    } else {

        $data = null;
        $create = $CustomGroup->save([
            'name' => $name,
            'created_by' => $_SESSION['id'],
            'category' => $category
        ]);

        if ($create != false) {
            echo json_encode(["status" => 200, "data" => $create, 'message' => 'Created']);
        } else {
            echo json_encode(["status" => 401, "data" => $data, 'message' => 'Failed']);
        }
    }
    exit;
}

if (isset($_POST['loadDiagnosis'])) {
    $input = $_POST['input'];
    $diagnosis = $Diagnosis->get($input, true);
    echo json_encode(["status" => 200, "data" => $diagnosis]);
    exit;
}



if ($_SERVER['HTTP_AJAX_ACTION'] == 'dischargeAppoint') {
    $request = request();

    $appointment_info = $Appointment->get(["appt_no" => $request->appointment_number, "hospital_no" => $request->hospital_no]);

    $data["status"] = 'discharge';
    $update = $Appointment->update($data, $appointment_info->sn);
    if ($update) {
        echo json_encode(["status" => 200, "message" => "Saved"]);
    } else {
        echo json_encode(["status" => 401, "message" => "Discharge Failed"]);
    }
}


if ($_SERVER['HTTP_AJAX_ACTION'] == 'saveDoctorManagementData') {
    $request = request();

    $appointment_info = $Appointment->get(["appt_no" => $request->appointment_number, "hospital_no" => $request->hospital_no]);

    ///  Drop Note : PRESENTING COMPLAINTS FORM THE MED NOTE
    $stmt = $db->prepare("DELETE FROM notes WHERE app_no = ? ");
    $stmt->execute(array($request->appointment_number));

    $stmt = $db->prepare("DELETE FROM c_d_remarks WHERE app_no = ? ");
    $stmt->execute(array($request->appointment_number));

    $stmt = $db->prepare("DELETE FROM physical_examination WHERE app_no = ? ");
    $stmt->execute(array($request->appointment_number));

    // $stmt = $db->prepare("DELETE FROM lab_manage WHERE app_no = ? ");
    // $stmt->execute(array($request->appointment_number));

    // $stmt = $db->prepare("DELETE FROM patient_ap_services WHERE app_no = ? and paystatus = '0'");
    // $stmt->execute(array($request->appointment_number));

    $stmt = $db->prepare("DELETE FROM management_diagnosis WHERE app_no = ? ");
    $stmt->execute(array($request->appointment_number));

    $stmt = $db->prepare("DELETE FROM management_complains WHERE app_no = ? and group_name != 'Socail_Histories' AND group_name != 'Allergies' ");
    $stmt->execute(array($request->appointment_number));


    $stmt = $db->prepare("INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry)  VALUES (?, ?, ?, ?, ?, ?, now())");
    $stmt->execute(
        array(
            $request->appointment_number,
            $request->hospital_no,
            $request->complains->notes,
            $request->complains->tag,
            $request->complains->notes_type,
            $request->complains->prepared_by
        )
    );


    $stmt = $db->prepare("UPDATE apptm SET referal_doc=?,queue_lock='1' WHERE appt_no=? and hospital_no=?");
    $stmt->execute(
        array(
            $request->complains->prepared_by,
            $request->appointment_number,
            $request->hospital_no

        )
    );


    //// WORK FOR ONLY FIRST VISIT
    if ($request->first_visit == true) {

        $stmt = $db->prepare("DELETE FROM management_complains WHERE app_no = ? and (group_name = 'Socail_Histories' OR group_name = 'Allergies') ");
        $stmt->execute(array($request->appointment_number));

        ///  Drop Social Histories :
        foreach ($request->social_histories as $key => $social_history) {
            $stmt = $db->prepare("INSERT INTO  c_d_remarks (app_no, hospital_no,complain,cat_type,prepared_by,date_entry)  VALUES (?, ?, ?, ?, ?, now())");
            $stmt->execute(
                array(
                    $request->appointment_number,
                    $request->hospital_no,
                    ' <strong>' . $social_history->cat . '</strong> : ' . $social_history->answer . '  ' . $social_history->comment . ' ',
                    'sh',
                    $request->complains->prepared_by

                )
            );

            save_complains($db, $request->appointment_number, $request->hospital_no, "Socail_Histories", $social_history->cat, $social_history->item, null, null, $social_history->answer, $social_history->comment);
        }


        ///  Drop Allergies :
        foreach ($request->selected_allergies as $key => $selected_allergy) {
            $stmt = $db->prepare("INSERT INTO  c_d_remarks (app_no, hospital_no,complain,cat_type,prepared_by,date_entry)  VALUES (?, ?, ?, ?, ?, now())");
            $stmt->execute(
                array(
                    $request->appointment_number,
                    $request->hospital_no,
                    ' <strong>' . $selected_allergy->item . '</strong> :   ' . $social_history->comment . ' ',
                    'DH',
                    $request->complains->prepared_by

                )
            );

            save_complains($db, $request->appointment_number, $request->hospital_no, "Allergies", $selected_allergy->cat, $selected_allergy->item, null, null, NULL, $selected_allergy->comment);
        }
    }



    ///  Drop Review of System :
    foreach ($request->ros as $key => $reveiewOfSystem) {
        $stmt = $db->prepare("INSERT INTO  c_d_remarks (app_no, hospital_no,complain,cat_type,prepared_by,date_entry)  VALUES (?, ?, ?, ?, ?, now())");
        $stmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                ' <strong>' . $reveiewOfSystem->cat . '</strong> : ' . $reveiewOfSystem->item . ' ',
                'R',
                $request->complains->prepared_by

            )
        );

        save_complains($db,  $request->appointment_number, $request->hospital_no, "review_of_system", $reveiewOfSystem->cat, $reveiewOfSystem->item, $reveiewOfSystem->selected, null, null, null);
    }



    ///  Drop Physical Examination :
    $stmt = $db->prepare("INSERT INTO  physical_examination (hospital_no, app_no, general_a, neck, eyes, ent, res, card, abdo, gas, ano, gen, lymph, skin, extre, mus, neu, nu, 
      others,doctor_name,date_captured)  
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, now())
          ");
    $stmt->execute(
        array(
            $request->hospital_no,
            $request->appointment_number,
            $request->physical_examinations[0]->status,
            $request->physical_examinations[1]->status,
            $request->physical_examinations[2]->status,
            $request->physical_examinations[3]->status,
            $request->physical_examinations[4]->status,
            $request->physical_examinations[5]->status,
            $request->physical_examinations[6]->status,
            $request->physical_examinations[7]->status,
            $request->physical_examinations[8]->status,
            $request->physical_examinations[9]->status,
            $request->physical_examinations[10]->status,
            $request->physical_examinations[11]->status,
            $request->physical_examinations[12]->status,
            $request->physical_examinations[13]->status,
            $request->physical_examinations[14]->status,
            $request->physical_examinations[15]->status,
            $request->physical_examinations[16]->status,
            $request->complains->prepared_by
        )
    );

    foreach ($request->physical_examinations as $key => $physical_exam) {
        save_complains($db,  $request->appointment_number, $request->hospital_no, "physical_exams", $physical_exam->cat, null, null, $physical_exam->status, null, null);
    }



    ///  Drop DIAGNOSIS : DIAGNOSIS FORM THE MED NOTE
    foreach ($request->selected_diagnosis as $key => $diagnosis) {
        # code...
        $stmt = $db->prepare("INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry)  VALUES (?, ?, ?, ?, ?, ?, now())");
        $stmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                $diagnosis->diagnosis . '[' . $diagnosis->comment1 . '] ' . ' [' . $diagnosis->comment2 . '] ' . ' [' . $diagnosis->comment3 . '] ',
                $request->complains->tag,
                'D',
                $request->complains->prepared_by
            )
        );

        $stmt = $db->prepare("INSERT INTO  management_diagnosis (app_no, hospital_no, diagnosis, comment1, comment2, comment3)  VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                $diagnosis->diagnosis,
                $diagnosis->comment1,
                $diagnosis->comment2,
                $diagnosis->comment3
            )
        );
    }








    echo json_encode(["status" => 200, "message" => "Saved"]);
    exit;
}
