<?php session_start();
include( '../../Connections/Conn.php' );
header( 'Content-Type: application/json' );
include( '../../doctor/objects.php' );
include( '../../doctor/helpers.php' );

$request = json_decode( json_encode(
    [
        'appointment_number' => $_POST[ 'appointment_number' ],
        'hospital_no' => $_POST[ 'hospital_no' ],
        'drug' => $_POST[ 'drug' ],
        'action' => $_POST[ 'action' ]
    ]
) );

if ( isset( $request->drug ) && isset( $request->appointment_number ) ) {

    if ( $request->action == 'add' ) {
        $appointment_info = $Appointment->get( [ 'appt_no' => $request->appointment_number, 'hospital_no' => $request->hospital_no ] );
        $save = false;

        $drug = $request->drug;
        //// Medication

        $serv_group = 'Drug';
        $cat_type = 'Pharmacy';
        $dept_id = 7;
        //// use the id of the drug to get the department
        $drug_sn = $drug->sn;
        $item_services = $drug->product_name;
        $hosp_price = $drug->hosp_price;

        $drug->med_dosage = intval( $drug->med_dosage ) < 1 ? 1 : intval( $drug->med_dosage );
        $drug->med_duration = intval( $drug->med_duration ) < 1 ? 1 : intval( $drug->med_duration );

        /// Check if the Medication has been saved bfore
        $checkMedicationStmt = $db->prepare( "SELECT * FROM  patient_ap_services WHERE app_no = ? AND hospital_no = ? AND drug_sn = ? and serv_group='Drug'" );
        $checkMedicationStmt->execute(
            array(
                $request->appointment_number,
                $request->hospital_no,
                $drug->sn
            )
        );

        $checkMedicationRows = $checkMedicationStmt->rowCount();
        if ( $checkMedicationRows == 0 ) {
            ///  save if not saved before

            $item_amt = ( $appointment_info->insurance == 'NHIS' && $appointment_info->ap_type <= 2 ) ? $drug->nhis_price : $drug->hosp_price;
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
                $amount_invoice[ 'pay_mode' ],
                $drug->med_frequency,
                $drug->med_dosage,
                $drug->med_dosage_unit,
                $drug->med_duration,
                $drug->med_duration_unit
            );
        }

        echo json_encode( [ 'status' => 200, 'message' => $save ] );
        exit;
    }

    if ( $request->action == 'remove' ) {
        $drug = $request->drug;
        $mesage = 'Cannot remove, Already Paid';

        $PatientApService_ = $PatientApService->get(
            [
                'drug_sn' => $drug->sn,
                'serv_group' => 'Drug',
                'app_no' => $request->appointment_number,
                'hospital_no' => $request->hospital_no,
                'paystatus' => 1
            ]
        );

        if ( empty( $PatientApService_ ) ) {
            /// not paid for the service
            $delete = $PatientApService->delete(
                [

                    'app_no' => $request->appointment_number,
                    'drug_sn' => $drug->sn,
                    'serv_group' => 'Drug',
                    'hospital_no' => $request->hospital_no,
                    'paystatus' => 0
                ]
            );

            $mesage = $delete ? 'Removed' : 'Action Failed';
        }
        echo json_encode( [ 'status' => 200, 'message' => $mesage ] );
        exit;
    }
}
