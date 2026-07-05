<?php session_start();
include( '../../Connections/Conn.php' );
include( '../objects.php' );
include( '../helpers.php' );

if ( isset( $_POST['patient_appointment_investigations'] ) ) {
    header( 'Content-Type: application/json' );
    $message = '';

    $hospital_no = $_POST['hospital_no'];
    $appointment_number = $_POST['appointment_number'];
    $investigation_type = $_POST['investigation_type'];

    //// SELECTED investigations
    $selected_labs_stmt = $db->prepare( 'SELECT l.*, p.invoice_status,p.pay as cash_price FROM lab_manage l INNER JOIN patient_ap_services p ON l.labrequest_no = p.drug_sn  WHERE l.app_no = ? and l.patient = ? and l.section = ?  ' );
    $selected_labs_stmt->execute( array( $appointment_number, $hospital_no, $investigation_type ) );
    $selected_labs_rows = $selected_labs_stmt->fetchAll( PDO::FETCH_ASSOC );
    $selected_labtests = [];

    foreach ( $selected_labs_rows as $key => $selected_labs_row ) {

        $investigation = $Investigation->find( $selected_labs_row['test_id'] );
            $investigation->specimen = $selected_labs_row['specimen'];
            $investigation->request_note = $selected_labs_row['request_note'];
            $investigation->invoice_status = $selected_labs_row['invoice_status'];
            $investigation->cash_price = $selected_labs_row['cash_price'];
            array_push( $selected_labtests, $investigation );
       
        
    }

    echo json_encode( ['status' => 200, 'data' => $selected_labtests, 'message' => $message] );
    exit;
}


if ( isset( $_POST['load_patient_labs_on_queue'] ) ) {
    $table = '';
    $hospital_no = $_POST['hospital_no'];
	$section = $_POST['lab_section'];
	
    $sn = 1;
    $distinct_investigation_hx_stmt = $db->prepare( "SELECT *  FROM lab_manage WHERE patient = ? AND section = ? AND (data_capture_status = 'queue' OR data_capture_status = 'specimen')  ORDER BY request_date DESC " );
    $distinct_investigation_hx_stmt->execute( array( $hospital_no, $section ) );
    if ( $distinct_investigation_hx_stmt->rowCount() > 0 ) {
        $table = '
            <table class="table table-bordered table-stripped " id="labs-on-queue-datatable">
                <tr>
                    <th>#</th>
                    <th>Test Name</th>
                    <th>Status</th>
                    <th>Date Time</th>
                    <th>Requested By</th>
                </tr>
        ';
     $distinct_investigation_hx_of_labs_on_queue  = $distinct_investigation_hx_stmt->fetchAll( PDO::FETCH_ASSOC );
     foreach ($distinct_investigation_hx_of_labs_on_queue as $key => $investigation_) {
        $lab_status = '';
        if ( $investigation_[ 'data_capture_status' ] == 'queue' ) {
            $lab_status = 'On Queue';
        } else if ( $investigation_[ 'data_capture_status' ] == 'specimen' ) {
            $lab_status = 'Specimen Taken';
        } 
        $table .= '
            <tr>
                <td>'.$sn++.'</td>
                <td>'.$investigation_["test_name"].'</td>
                <td>'.$lab_status.'</td>
                <td>'.formatDateTime_($investigation_["request_date"]).'</td>
                <td>'.$investigation_["request_by"].'</td>
            </tr>
        '; 
     }
     $table .= ' </table>';
    }

    echo $table;
    
}



	if ( isset( $_POST['openInvestigationResults'] ) ) {
		$hospital_no = $_POST['hospital_no'];
		$test_id = $_POST['test_id'];
		$investigation_type = $_POST['investigation_type'];
		
		
		  $investigation_hx_stmt = $db->prepare( "SELECT data_capture_status,labrequest_no,entered_by, request_by, request_date FROM lab_manage WHERE patient = ? AND test_id = ? AND section = ?  ORDER BY request_date DESC LIMIT 10" );
            $investigation_hx_stmt->execute( array( $hospital_no, $test_id, $investigation_type ) );

            if ( $investigation_hx_stmt->rowCount() > 0 ) {
                $investigation_hx  = $investigation_hx_stmt->fetchAll( PDO::FETCH_ASSOC );

                foreach ( $investigation_hx as $key => $investigation_ ) {

                    if ( $investigation_[ 'data_capture_status' ] == 'approve' || $investigation_[ 'data_capture_status' ] == 'result' ) {
						$lab_status = '';
						if ( $investigation_[ 'data_capture_status' ] == 'result' ) {
                                $lab_status = '<span class="text-success">Await Approval</span>';
                            } else if ( $investigation_[ 'data_capture_status' ] == 'approve' ) {
                                $lab_status = '<span class="text-success">Approved</span>';
                            }
						
						
                        $investigation_field_stmt = $db->prepare( 'SELECT * FROM lab_scan_fields WHERE test_no = ? LIMIT 1' );
                        $investigation_field_stmt->execute( array( $test_id ) );

                        if ( $investigation_field_stmt->rowCount() > 0 ) {

                            $investigation_field_info  = $investigation_field_stmt->fetch( PDO::FETCH_ASSOC );
                            $field_type = $investigation_field_info[ 'field_type' ];
                            $field_ = $investigation_field_info[ 'field' ];

                            ///// Single value /////////////
                            if ( $field_type == 'value' ) {
                                $investigation_result_stmt = $db->prepare( 'SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ? LIMIT 1' );
                                $investigation_result_stmt->execute( array( $test_id, $investigation_[ 'labrequest_no' ] ) );
                                if ( $investigation_result_stmt->rowCount() > 0 ) {
                                    $investigation_result_info  = $investigation_result_stmt->fetch( PDO::FETCH_ASSOC );
                                    $result = $investigation_result_info[ 'field_value' ];
                                    $field_name = $investigation_result_info[ 'field_name' ];
                                    echo '<div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"> 
                                                    <table class="table" border="2">
                                                            <tr><th> Result</th><th> Value</th> </tr>
                                                            <tr>
                                                            <td> ' . $field_name . '</td> 
                                                            <td> ' . $result . '</td> 
                                                            </tr>
                                                    </table>
                                                    <br>
                                    <small>
									<b>Requested by:  '.$investigation_[ 'request_by' ].' <br>
									<b>Requested On:  '.dateFormat_( $investigation_[ 'request_date' ]).' <br>
									<b>Status:  '.$lab_status.' <br>
									<b>Entered by:  '.$investigation_result_info[ 'entered_by' ].' <br> 
									Date:  '.dateFormat_( $investigation_result_info[ 'result_date' ] ).'</b></small>
                                                </div>';
                                }
                            }

                            ///// options  values /////////////
                            if ( $field_type == 'options' ) {
                                $investigation_result_stmt = $db->prepare( 'SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ? LIMIT 1' );
                                $investigation_result_stmt->execute( array( $test_id, $investigation_[ 'labrequest_no' ] ) );
                                if ( $investigation_result_stmt->rowCount() > 0 ) {
                                    $investigation_result_info  = $investigation_result_stmt->fetch( PDO::FETCH_ASSOC );
                                    $result = $investigation_result_info[ 'field_value' ];
                                    $field_name = $investigation_result_info[ 'field_name' ];
                                    echo '<div class="col-md-4"> 
                                                    <table class="table" border="2">
                                                            <tr><th> Result</th><th> Value</th> </tr>
                                                            <tr>
                                                            <td> ' . $field_name . '</td> 
                                                            <td> ' . $result . '</td> 
                                                            </tr>
                                                    </table>
                                                    <br>
                                    <small>
									<b>Requested by:  '.$investigation_[ 'request_by' ].' <br>
									<b>Requested On:  '.dateFormat_( $investigation_[ 'request_date' ]).' <br>
									<b>Status:  '.$lab_status.' <br>
									<b>Entered by:  '.$investigation_result_info[ 'entered_by' ].' <br> 
									Date:  '.dateFormat_( $investigation_result_info[ 'result_date' ] ).'</b></small>
                                                </div>';
                                }
                            }

                            ///// Multile  values /////////////
                            if ( $field_type == 'values' ) {
                                $investigation_result_stmt = $db->prepare( 'SELECT * FROM lab_scan_input_results WHERE test_no = ? AND lab_request_no = ? ' );
                                $investigation_result_stmt->execute( array( $test_id, $investigation_[ 'labrequest_no' ] ) );

                                if ( $investigation_result_stmt->rowCount() > 0 ) {
                                    $investigation_result_info_list  = $investigation_result_stmt->fetchAll( PDO::FETCH_ASSOC );
                                    echo '<div class="col-md-4"> `<table class="table" border="2">';
                                    echo ' <tr><th class="text-center"> Result</th> <th class="text-center"> Value</th> <th class="text-center">Expected Value/Ref</th> </tr> ';
                                    foreach ( $investigation_result_info_list as $key => $investigation_result_info_ ) {
                                        $result = $investigation_result_info_[ 'result' ];
                                        $value_title = $investigation_result_info_[ 'value_title' ];
                                        $value_ref = $investigation_result_info_[ 'value_ref' ];
                                        echo ' <tr>
                                                <td> ' . $value_title . '</td> 
                                                <td class="text-center"> ' . $result . '</td> 
                                                <td class="text-center"> ' . $value_ref . '</td> 
                                                </tr> ';
                                    }

                                    echo '</table> 
                                   <br>
                                    <small>
									<b>Requested by:  '.$investigation_[ 'request_by' ].' <br>
									<b>Requested On:  '.dateFormat_( $investigation_[ 'request_date' ]).' <br>
									<b>Status:  '.$lab_status.' <br>
									<b>Entered by:  '.$investigation_result_info[ 'entered_by' ].' <br> 
									Date:  '.dateFormat_( $investigation_result_info[ 'result_date' ] ).'</b></small>
                                    </div>
                                    ';
                                }
                            }

                            ///// Report Format /////////////
                            if ( $field_type == 'report' ) {
                                $investigation_result_stmt = $db->prepare( 'SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ? LIMIT 1' );
                                $investigation_result_stmt->execute( array( $test_id, $investigation_[ 'labrequest_no' ] ) );
                                if ( $investigation_result_stmt->rowCount() > 0 ) {
                                    $investigation_result_info  = $investigation_result_stmt->fetch( PDO::FETCH_ASSOC );
                                    $result = $investigation_result_info[ 'field_value' ];
                                    $field_name = $investigation_result_info[ 'field_name' ];
                                    echo '<div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block">  ' . $result . '
                                    <br>
                                    <small>
									<b>Requested by:  '.$investigation_[ 'request_by' ].' <br>
									<b>Requested On:  '.dateFormat_( $investigation_[ 'request_date' ]).' <br>
									<b>Status:  '.$lab_status.' <br>
									<b>Entered by:  '.$investigation_result_info[ 'entered_by' ].' <br> 
									Date:  '.dateFormat_( $investigation_result_info[ 'result_date' ] ).'</b></small>
                                    </div>';
                                } else {
                                    echo '';
                                }
                            }
                        } else {
                            echo 'Test result format not defined...';
                        }
                    } else {
                        if ( $investigation_[ 'app_no' ] == $appointment_number ) {
                            //// Format the status
                            if ( $investigation_[ 'data_capture_status' ] == 'queue' ) {
                                $lab_status = 'Pending';
                            } else if ( $investigation_[ 'data_capture_status' ] == 'specimen' ) {
                                $lab_status = 'Pending';
                            } else if ( $investigation_[ 'data_capture_status' ] == 'result' ) {
                                $lab_status = 'Await Approval';
                            } else if ( $investigation_[ 'data_capture_status' ] == 'approve' ) {
                                $lab_status = 'Approved ';
                            }

                            echo '
							 <div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"><br>
							 
                                   
									 <hr>
                                    <small>
									<b>Requested by:  '.$investigation_[ 'request_by' ].' <br>
									<b>Requested On:  '.dateFormat_( $investigation_[ 'request_date' ]).' <br>
									<b>Status:  <span class="text-danger">'.$lab_status.'</span> <br>
									<b>Approved by: <br> 
										Date:  </b></small>
									</div>
							';
                        }

                    }
                }
            }else{
                echo 'Lab. test has been removed';
            }

	}

?>