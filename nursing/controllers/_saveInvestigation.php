<?php session_start();
include( '../../Connections/Conn.php' );
header( 'Content-Type: application/json' );
include( '../../doctor/objects.php' );
include( '../../doctor/helpers.php' );

$request = json_decode( json_encode(
    [
        'appointment_number' => $_POST[ 'appointment_number' ],
        'hospital_no' => $_POST[ 'hospital_no' ],
        'investigation' => $_POST[ 'investigation' ],
        'action' => $_POST[ 'action' ]
    ]
) );

if ( isset( $request->investigation ) && isset( $request->appointment_number ) ) {

    if ( $request->action == 'add' ) {
        $appointment_info = $Appointment->get( [ 'appt_no' => $request->appointment_number, 'hospital_no' => $request->hospital_no ] );
        $save = false;
        //// Investigation
        $investigation = $request->investigation;

        if ( $investigation->category == 'Laboratory' ) {
            $code = 'LB';
        } else {
            $code = 'RD';
        }

        $setdate2 = date( 'Y-m-d' );
        $datetime = date( 'Y-m-d H:i:s' );
        $lab_reqno = generateRequestNo( $db, $code );
        $lab_cats = [ 'Laboratory' => 1, 'Radiology' => 3 ];

        /// Check if the Investigation has been saved bfore
        $checkLabManagestmt = $db->prepare( 'SELECT * FROM  lab_manage WHERE app_no = ? AND patient = ? AND test_id = ?' );
        $checkLabManagestmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                $investigation->sn
            )
        );

        $checkLabManageRows = $checkLabManagestmt->rowCount();
        if ( $checkLabManageRows == 0 ) {
            ///  save if not saved before
            $data = json_decode( json_encode( [
                'app_no' =>   $request->appointment_number,
                'labrequest_no' =>  $lab_reqno,
                'patient' =>  $request->hospital_no,
                'patient_name' => $appointment_info->patient_name,
                'test_id' =>  $investigation->sn,
                'test_name' =>  $investigation->test,
                'lab_cat' =>     $lab_cats[ $investigation->category ],
                'section' =>   $investigation->category,
                'group_id' =>   genGroupId( $db, $request->hospital_no ),
                'preferred_specimen' => $investigation->specimen,
                'request_note' =>  $investigation->request_note,
                'request_date' =>   $datetime,
                'request_by' =>  $_SESSION[ 'fullname' ],
                'lab_combos' => $investigation->combo_test,
                'created_by' => $_SESSION[ 'id' ]
            ] ) );

            $Investigation->save( $data );

            $serv_group = 'Laboratory';
            $cat_type = 'Laboratory';
            $dept_id = $investigation->dept;
            $drug_sn = $lab_reqno;
            $item_services = $investigation->test;
            $hosp_price = $investigation->hosp_price;

            $item_amt = ( $appointment_info->insurance == 'NHIS' && $appointment_info->ap_type <= 2 ) ? $investigation->nhis_price : $investigation->hosp_price;
            ///// amount and invoice
            $amount_invoice = service_amount_cal( $request->appointment_number, $appointment_info->interest, $appointment_info->insurance, $appointment_info->ap_type, $item_amt );
            $save = save_patient_ap_service(
                $db,
                $request->appointment_number,
                $request->hospital_no,
                $appointment_info->ap_type,
                $serv_group,
                $cat_type,
                $dept_id,
                $drug_sn,
                $item_services,
                $hosp_price,
                $amount_invoice[ 'claim_amt' ],
				 $amount_invoice["ccop_int_charge"],
                '',
                $amount_invoice[ 'invoice_no' ],
                $_SESSION[ 'fullname' ],
                $amount_invoice[ 'amount_paying' ],
                $amount_invoice[ 'pay_mode' ]
            );
        }

        echo json_encode( [ 'status' => 200, 'message' => $save ] );
        exit;
    }

    ///////////////////////// REMOVE ACTION ////////////////////////////////////////////
    if ( $request->action == 'remove' ) {
        $investigation = $request->investigation;

        $checkLabManagestmt = $db->prepare( 'SELECT * FROM  lab_manage WHERE app_no = ? AND patient = ? AND test_id = ?' );
        $checkLabManagestmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                $investigation->sn,
            )
        );
        if ( $checkLabManagestmt->rowCount() > 0 ) {
            $row = $checkLabManagestmt->fetch( PDO::FETCH_ASSOC );
            $labrequest_no = $row[ 'labrequest_no' ];

            $PatientApService_ = $PatientApService->get(
                [
                    'drug_sn' => $labrequest_no,
                    'app_no' => $request->appointment_number,
                    'hospital_no' => $request->hospital_no,
                ]
            );






            if ( !empty( $PatientApService_ ) ) {

                try {
                    // Begin transaction
                    $db->beginTransaction();
                
                    if ($PatientApService_->paystatus == '0') {
                        // Perform the DELETE operation
                        $stmtDelete = $db->prepare("DELETE FROM patient_ap_services 
                                                    WHERE paystatus = 0 
                                                      AND app_no = ? 
                                                      AND hospital_no = ? 
                                                      AND drug_sn = ? 
                                                      AND created_by = ?");
                        $stmtDelete->execute([
                            $request->appointment_number, 
                            $request->hospital_no, 
                            $labrequest_no, 
                            $_SESSION["id"]
                        ]);
                
                        $deletedCount = $stmtDelete->rowCount();
                
                        if ($deletedCount > 0) {
                            // Prepare and execute the second DELETE statement
                            $stmt = $db->prepare("DELETE FROM lab_manage WHERE labrequest_no = ?");
                            $stmt->execute([$labrequest_no]);
                
                            $deletedCount2 = $stmt->rowCount();
                
                            if ($deletedCount2 > 0) {
                                // Commit the transaction if both deletes are successful
                                $db->commit();
                                echo json_encode(['status' => 200, 'message' => 'Removed...']);
                                exit;
                            } else {
                                // Rollback the transaction if the second delete fails
                                $db->rollBack();
                                echo json_encode(['status' => 200, 'message' => 'Failed...']);
                                exit;
                            }
                        } else {
                            // Rollback the transaction if the first delete fails
                            $db->rollBack();
                            echo json_encode(['status' => 200, 'message' => 'Failed...']);
                            exit;
                        }
                    } else {
                        echo json_encode(['status' => 401, 'message' => 'Cannot delete, already been paid for']);
                        exit;
                    }
                } catch (Exception $e) {
                    // Rollback the transaction in case of any exception
                    $db->rollBack();
                    echo json_encode(['status' => 500, 'message' => 'An error occurred: ' . $e->getMessage()]);
                    exit;
                }
                    


            } else {
                echo json_encode( [ 'status' => 401, 'message' => 'Something went wrong' ] );
                exit;
            }
        }
    }
}
