<?php session_start();
include( '../../Connections/Conn.php' );
header( 'Content-Type: application/json' );
include( '../../doctor/objects.php' );
include( '../../doctor/helpers.php' );

if ( isset( $_POST[ 'patient_appointment_medications' ] ) ) {

    $message = '';

    $hospital_no = $_POST[ 'hospital_no' ];
    $appointment_number = $_POST[ 'appointment_number' ];

    //// SELECTED drugs
    $selected_drugs_stmt = $db->prepare( "SELECT drug_sn,med_frequency, med_dosage, med_dosage_unit, med_duration, med_duration_unit   FROM patient_ap_services WHERE app_no = ? and hospital_no = ? and serv_group = 'Drug' " );
    $selected_drugs_stmt->execute( array( $appointment_number, $hospital_no ) );
    $selected_drugs_rows = $selected_drugs_stmt->fetchAll( PDO::FETCH_ASSOC );
    $selected_drug_stocks = [];
    foreach ( $selected_drugs_rows as $key => $selected_drugs_row ) {

        $drug_stmt_row = $DrugStock->find( $selected_drugs_row[ 'drug_sn' ] );

        $drug_stmt_row->med_dosage = $selected_drugs_row[ 'med_dosage' ];
        $drug_stmt_row->med_dosage_unit = $selected_drugs_row[ 'med_dosage_unit' ];
        $drug_stmt_row->med_frequency = $selected_drugs_row[ 'med_frequency' ];
        $drug_stmt_row->med_duration = $selected_drugs_row[ 'med_duration' ];
        $drug_stmt_row->med_duration_unit = $selected_drugs_row[ 'med_duration_unit' ];

        array_push( $selected_drug_stocks, $drug_stmt_row );
    }

    echo json_encode( [ 'status' => 200, 'data' => $selected_drug_stocks, 'message' => $message ] );
    exit;
}

?>