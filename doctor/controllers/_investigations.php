<?php session_start();
include('../../Connections/Conn.php');
include('../objects.php');
include('../helpers.php');

if (isset($_POST['patient_appointment_investigations'])) {
    header('Content-Type: application/json');
    $message = '';

    $hospital_no = $_POST['hospital_no'];
    $appointment_number = $_POST['appointment_number'];
    $investigation_type = $_POST['investigation_type'];
    //// SELECTED investigations
    $selected_labs_stmt = $db->prepare("SELECT * FROM lab_manage  
	WHERE app_no = ? and patient = ? and section = ? AND data_capture_status != 'result' AND data_capture_status != 'approve'  ORDER BY request_date DESC   ");
    $selected_labs_stmt->execute(array($appointment_number, $hospital_no, $investigation_type));
    $selected_labs_rows = $selected_labs_stmt->fetchAll(PDO::FETCH_ASSOC);
    $selected_labtests = [];

    foreach ($selected_labs_rows as $key => $selected_labs_row) {
        $investigation = $Investigation->find($selected_labs_row['test_id']);
        $investigation->specimen = $selected_labs_row['preferred_specimen'];
        $investigation->request_note = $selected_labs_row['request_note'];
        $labrequest_no = $selected_labs_row['labrequest_no'];

        $stmt_chk_pay_status = $db->prepare("SELECT paystatus FROM patient_ap_services WHERE drug_sn = '$labrequest_no' and paystatus=0");
        $stmt_chk_pay_status->execute();
        if ($stmt_chk_pay_status->rowCount() > 0) {
            $investigation->paystatus = 0;
        } else {
            $investigation->paystatus = 1;
        }

        // if($selected_labs_row['created_by'] != $_SESSION['id']){
        //   $investigation->invoice_status = 1;
        // }else{
        //    $investigation->invoice_status = 0; 
        //}



        array_push($selected_labtests, $investigation);
    }

    echo json_encode(['status' => 200, 'data' => $selected_labtests, 'message' => $message]);
    exit;
}


if (isset($_POST['load_patient_labs_on_queue'])) {
    $table = '';
    $hospital_no = $_POST['hospital_no'];
    $section = $_POST['lab_section'];

    $patient_info = $Patient->getByHospitalNo($hospital_no);
    $ap_type = 0;
    if (!empty($patient_info)) {
        $interest = $patient_info->interest;
        $insurance_type = $patient_info->insurance_type;
        $services_access = $patient_info->services_access;
        $insurance_no = $patient_info->insurance_no;
        $add_minus = $patient_info->add_minus;
        $payment_mode = $patient_info->payment_mode;
    } else {
        echo json_encode(["status" => 404, "message" => "Invalid Patient Number"]);
        exit;
    }


    $n = 1;
    $distinct_investigation_hx_stmt = $db->prepare("SELECT *  FROM lab_manage WHERE patient = ? AND section = ? AND (data_capture_status = 'queue' OR data_capture_status = 'specimen' OR data_capture_status = 'capture')  ORDER BY request_date DESC LIMIT 30 ");
    $distinct_investigation_hx_stmt->execute(array($hospital_no, $section));

    if ($distinct_investigation_hx_stmt->rowCount() > 0) {
        $table = '
            <table class="table table-bordered table-stripped " id="labs-on-queue-datatable" style="font-size:16px; ">
                <tr>
                    <th>#</th>
                    <th>Test Name</th>
                    <th>Request Status</th>
                    <th>Date Time</th>
                    <th>Requested By</th>
                    <th>Send/Result </th>
                    <th></th>
                </tr>
        ';
        $distinct_investigation_hx_of_labs_on_queue  = $distinct_investigation_hx_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($distinct_investigation_hx_of_labs_on_queue as $key => $investigation_) {

            $labrequest_no = $investigation_["labrequest_no"];
            $test_id = $investigation_["test_id"];
            $appointment_number = $investigation_["app_no"];
            $request_date = $investigation_["request_date"];
            $doctor_result_status = $investigation_["doctor_result_status"];
            //// if for error 


            $stmt2 = $db->query("SELECT sn FROM patient_ap_services WHERE drug_sn='$labrequest_no'");
            if ($stmt2->rowCount() == 0) {
                /// add ooooo

                $stmt_get = $db->query("SELECT * FROM lab_scan WHERE sn='$test_id'");
                $rowx = $stmt_get->fetch(PDO::FETCH_ASSOC);
                $dept_id = $rowx['dept'];
                $serv_group = $rowx['category'];
                $drug_sn = $labrequest_no;
                $testname = $rowx['test'];
                $hosp_price = $rowx['hosp_price'];
                $ext_price = $rowx['ext_price'];
                $nhis_price = $rowx['nhis_price'];
                /// $nhis_price = $investigation->nhis_price;
                $cat_type = $rowx['sub_category'];
                $item_sn = $rowx['sn'];


                $target_sn = $item_sn;
                $NHIS_DRUG_CONSUMBL_STATE = 0;
                $_tariff_table = "hmo_medical_tariff";
                $amount_invoice = new_service_amount_cal(
                    $db,
                    $hospital_no,
                    $appointment_number,
                    $interest,
                    $insurance_type,
                    $insurance_no,
                    $hosp_price,
                    $ext_price,
                    $nhis_price,
                    $services_access,
                    $add_minus,
                    $payment_mode,
                    $_tariff_table,
                    $target_sn,
                    $NHIS_DRUG_CONSUMBL_STATE
                );
                $claim_amt = $amount_invoice["claim_amt"];
                $amount_paying = $amount_invoice["amount_paying"];
                $pay_mode = $amount_invoice["pay_mode"];
                $ccop_int_charge = $amount_invoice["ccop_int_charge"];


                $save = save_patient_ap_service(
                    $db,
                    $appointment_number,
                    $hospital_no,
                    $services_access,
                    $serv_group,
                    $cat_type,
                    $dept_id,
                    $drug_sn,
                    $testname,
                    $amount_invoice["item_amt"],
                    $claim_amt,
                    $amount_invoice["ccop_int_charge"],
                    "",
                    $amount_invoice["invoice_no"],
                    $_SESSION['fullname'],
                    $amount_invoice["amount_paying"],
                    $amount_invoice["pay_mode"],
                    " ",
                    " ",
                    " ",
                    " ",
                    " ",
                    " ",
                    true
                );
            } else {
                /// calculate and delete after one month 

                $date1 = date_create(date("d-m-Y"));
                $date2 = date_create($request_date);
                $diff = date_diff($date1, $date2);

                ////echo $diff->format("%a");
            }

            $fullname = $_SESSION['fullname'];


            if ($diff->format("%a") <= 20) {   /// DISPLAY INVESTIGATION IS LESS THAN 20 DAYS

                $lab_status = '';
                $sn = $investigation_["sn"];



                if ($doctor_result_status == '1') {
                    $btn = '<td><button class="btn btn-warning btn-sm" onclick="doctor_result_status(' . $sn . ')">Enter Result</button></td>';;
                } else {
                    $btn = '<td><button class="btn btn-success btn-sm" onclick="send_reminder(' . $sn . ')">Reminder</button></td>';
                }

                if ($investigation_['data_capture_status'] == 'queue') {
                    $lab_status = 'On-Queue';
                } else if ($investigation_['data_capture_status'] == 'specimen') {
                    $lab_status = 'Specimen Taken';
                } else if ($investigation_['data_capture_status'] == 'capture') {
                    $lab_status = 'Captured';
                } else if ($investigation_['data_capture_status'] == 'result') {
                    $lab_status = 'Resulted/Await Approval';
                } else if ($investigation_['data_capture_status'] == 'approve') {
                    $lab_status = 'Approved';
                }
                $table .= '
								<tr>
									<td>' . $n++ . '</td>
									<td>' . $investigation_["test_name"] . '</td>
									<td>' . $lab_status . '</td>
									<td>' . formatDateTime_($investigation_["request_date"]) . '</td>
									<td>' . $investigation_["request_by"] . '</td>
									' . $btn . '
									<td><button class="btn btn-danger btn-sm" onclick="delete_investig(' . $sn . ')">-</button></td>
								</tr>
							';
            } else {

                //// DELETE INVESTIGATION IS TOO OLD 



                /*				
	$deleted = "DELETE FROM patient_ap_services WHERE drug_sn='$labrequest_no' AND paystatus=0";
    $deletedCount = $db->exec($deleted);

    if ($deletedCount > 0) {
        $deleted2 = "DELETE FROM lab_manage WHERE labrequest_no='$labrequest_no'";
        $deletedCount2 = $db->exec($deleted2);

	}
				
		*/
            }
        } //// LOOPING  ////



        $table .= ' </table>';
    } else {
        echo '<h3 class="text-center text-danger">No Pending Investigations</h3>';
    }

    echo $table;
}



if (isset($_POST['openInvestigationResults'])) {
    $hospital_no = $_POST['hospital_no'];
    $test_id = $_POST['test_id'];
    $investigation_type = $_POST['investigation_type'];


    $investigation_hx_stmt = $db->prepare("SELECT data_capture_status,labrequest_no,entered_by, request_by, request_date,
		  result_note,result_date, entered_by, approved_by, lab_combos,result_comment,abnormal_results FROM lab_manage 
		  WHERE patient = ? AND test_id = ? AND data_capture_status = 'approve' ORDER BY request_date DESC");
    $investigation_hx_stmt->execute(array($hospital_no, $test_id));

    if ($investigation_hx_stmt->rowCount() > 0) {
        $investigation_hx  = $investigation_hx_stmt->fetchAll(PDO::FETCH_ASSOC);

        $numbering = 0;

        foreach ($investigation_hx as $key => $investigation_) {


            if (!in_array($investigation_['abnormal_results'], ['0', '', 'Normal'])) {
                echo $rlst = " <h3 style='color:brown;'><i> RESULT STATUS: {$investigation_['abnormal_results']}</i></h3>";
            }

            if ($investigation_type == 'Radiology') {

                if ($investigation_['result_date'] == '') {
                    $result_date = '';
                } else {
                    $result_date = date("d, M Y h:i:s A", strtotime($investigation_["result_date"]));
                }

                if ($investigation_['data_capture_status'] == 'queue') {
                    $lab_status = 'On-Queue';
                } else if ($investigation_['data_capture_status'] == 'capture') {
                    $lab_status = 'Captured';
                } else if ($investigation_['data_capture_status'] == 'result') {
                    $lab_status = 'Resulted/Await Approval';
                } else if ($investigation_['data_capture_status'] == 'approve') {
                    $lab_status = 'Approved ';
                }

                echo '<div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"> 
                <h2 class="text-center">' . ++$numbering . '</h2>
                ' . $investigation_['result_note'] . '
                <br>
                <span style="font-size:14px;">
                <b>Requested by: ' . $investigation_['request_by'] . ' <br>
                <b>Requested On: ' . dateFormat_($investigation_['request_date']) . ' <br>';

                if (!empty($investigation_['result_comment'])) {
                    echo '<hr><b><span style="color:red;">Comment:</span> ' . $investigation_['result_comment'] . ' <hr>';
                } else {
                    echo '<b>Comment: ' . $investigation_['result_comment'] . ' <br>';
                }

                echo '<b>Status: ' . $lab_status . ' <br>
                <b>Entered by: ' . $investigation_['entered_by'] . ' <br> 
                Date: ' . $result_date . '</b></span>
            </div>';
            } else {

                if ($investigation_['data_capture_status'] == 'approve' || $investigation_['data_capture_status'] == 'result') {
                    $lab_status = '';
                    if ($investigation_['data_capture_status'] == 'result') {
                        $lab_status = '<span class="text-success">Await Approval</span>';
                    } else if ($investigation_['data_capture_status'] == 'approve') {
                        $lab_status = '<span class="text-success">Approved</span>';
                    }

                    if ($investigation_['lab_combos'] == 1) {
                        $cb_no = $test_id;
                        $stmt2 = $db->query("SELECT c.test_id, l.test FROM lab_combos_items as c 
								inner join lab_scan as l on c.test_id=l.sn WHERE combos_id='$cb_no'");
                        while ($row_test = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                            $test_name = $row_test['test'];
                            $test_id = $row_test['test_id'];
                            include('_investigaton_results_subset.php');
                        }
                    } else {
                        include('_investigaton_results_subset.php');
                    }
                } else {
                    if ($investigation_['app_no'] == $appointment_number) {
                        //// Format the status
                        if ($investigation_['data_capture_status'] == 'queue') {
                            $lab_status = 'On-Queue';
                        } else if ($investigation_['data_capture_status'] == 'specimen') {
                            $lab_status = 'Specimen Taken';
                        } else if ($investigation_['data_capture_status'] == 'result') {
                            $lab_status = 'Await Approval';
                        } else if ($investigation_['data_capture_status'] == 'approve') {
                            $lab_status = 'Approved ';
                        }

                        echo '
                        <div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"><br>
                            <hr>
                            <span style="font-size:14px;">
                            <b>Requested by: ' . $investigation_['request_by'] . ' <br>
                            <b>Requested On: ' . dateFormat_($investigation_['request_date']) . ' <br>';

                        if (!empty($investigation_['result_comment'])) {
                            echo '<hr><b><span style="color:red;">Comment:</span> ' . $investigation_['result_comment'] . ' <hr>';
                        } else {
                            echo '<b>Comment: ' . $investigation_['result_comment'] . ' <br>';
                        }

                        echo '<b>Status: <span class="text-danger">' . $lab_status . '</span> <br>
                            <b>Approved by: <br> 
                            Date: </b></span>
                        </div>
                    ';
                    }
                }
            }
        }
    } else {
        //  echo 'Lab. test has been removed';
    }
}


if (isset($_POST['openInvestigationResult'])) {
    $hospital_no = $_POST['hospital_no'];
    $lab_sn = $_POST['lab_sn'];
    $investigation_type = $_POST['investigation_type'];


    $investigation_hx_stmt = $db->prepare("SELECT * FROM lab_manage 
		  WHERE patient = ? AND sn = ? AND  (data_capture_status = 'result'  OR data_capture_status = 'approve')  LIMIT 1");
    $investigation_hx_stmt->execute(array($hospital_no, $lab_sn));

    if ($investigation_hx_stmt->rowCount() > 0) {
        $investigation_  = $investigation_hx_stmt->fetch(PDO::FETCH_ASSOC);
        $test_id = $investigation_['test_id'];
        $attachment = $investigation_['attachment'];
        $labrequest_no = $investigation_['labrequest_no'];

        if ($investigation_['result_date'] == '') {
            $result_date = '';
        } else {
            $result_date = date("d, M Y h:i:s A", strtotime($investigation_["result_date"]));
        }

        if (!in_array($investigation_['abnormal_results'], ['0', '', 'Normal'])) {
            echo $rlst = " <h3 style='color:brown;'><i>RESULT STATUS: {$investigation_['abnormal_results']}</i></h3>";
        }


        $numbering = 0;
        if ($investigation_type == 'Radiology') {


            if ($investigation_['data_capture_status'] == 'queue') {
                $lab_status = 'On-Queue';
            } else if ($investigation_['data_capture_status'] == 'specimen') {
                $lab_status = 'Pending';
            } else if ($investigation_['data_capture_status'] == 'result') {
                $lab_status = 'Await Approval';
            } else if ($investigation_['data_capture_status'] == 'approve') {
                $lab_status = 'Approved ';
            }


            echo '<div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"> 
            <h2 class="text-center">' . ++$numbering . '</h2>
            ' . $investigation_['result_note'] . '
            <br>
            <span style="font-size:14px;">
            <b>Requested by: ' . $investigation_['request_by'] . ' <br>
            <b>Requested On: ' . dateFormat_($investigation_['request_date']) . ' <br>';

            if (!empty($investigation_['result_comment'])) {
                echo '<hr><b><span style="color:red;">Comment:</span> ' . $investigation_['result_comment'] . ' <hr>';
            } else {
                echo '<b>Comment: ' . $investigation_['result_comment'] . ' <br>';
            }

            echo '<b>Status: ' . $lab_status . ' <br>
            <b>Entered by: ' . $investigation_['entered_by'] . ' <br> 
            Date: ' . $result_date . '</b></span>
        </div>';
        } else {


            //echo $investigation_[ 'lab_combos'];
            if ($investigation_['data_capture_status'] == 'approve' || $investigation_['data_capture_status'] == 'result') {
                $lab_status = '';
                if ($investigation_['data_capture_status'] == 'result') {
                    $lab_status = '<span class="text-success">Await Approval</span>';
                } else if ($investigation_['data_capture_status'] == 'approve') {
                    $lab_status = '<span class="text-success">Approved</span>';
                }

                if ($investigation_['lab_combos'] == 1) {
                    $cb_no = $test_id;
                    $stmt2 = $db->query("SELECT c.test_id, l.test FROM lab_combos_items as c inner join lab_scan as l on c.test_id=l.sn WHERE combos_id='$cb_no'");
                    while ($row_test = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                        $test_name = $row_test['test'];
                        $test_id = $row_test['test_id'];
                        include('_investigaton_results_subset.php');
                    }
                } else {
                    include('_investigaton_results_subset.php');
                }
            } else {

                if ($investigation_['app_no'] == $appointment_number) {
                    //// Format the status
                    if ($investigation_['data_capture_status'] == 'queue') {
                        $lab_status = 'On-Queue';
                    } else if ($investigation_['data_capture_status'] == 'specimen') {
                        $lab_status = 'Pending';
                    } else if ($investigation_['data_capture_status'] == 'result') {
                        $lab_status = 'Await Approval';
                    } else if ($investigation_['data_capture_status'] == 'approve') {
                        $lab_status = 'Approved ';
                    }

                    echo '
                    <div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"><br>
                        <hr>
                        <span style="font-size:14px;">
                        <b>Requested by: ' . $investigation_['request_by'] . ' <br>';

                    if (!empty($investigation_['result_comment'])) {
                        echo '<hr><b><span style="color:red;">Comment:</span> ' . $investigation_['result_comment'] . ' <hr>';
                    } else {
                        echo '<b>Comment: ' . $investigation_['result_comment'] . ' <br>';
                    }

                    echo '<b>Requested On: ' . dateFormat_($investigation_['request_date']) . ' <br>
                        <b>Status: <span class="text-danger">' . $lab_status . '</span> <br>
                        <b>Approved by: <br> 
                        Date: </b></span>
                    </div>
                ';
                }
            }
        }

        if ($attachment != '' && $attachment != null) {
            echo '<br>';
            $fileUrl = '../investigations/uploads/' . $labrequest_no . '.' . $attachment;
            echo '<a href="' . htmlspecialchars($fileUrl) . '" target="_blank">View Attached Document</a>';
        }
    } else {
        echo 'Lab. test has been removed';
    }
}
