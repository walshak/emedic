<?php
session_start();
include("../Connections/Conn.php");


if ( isset( $_POST[ 'hospital_no' ] ) ) {
	
    $allergies_notes = $_POST[ 'allergies_notes' ];
    $hospital_no = $_POST[ 'hospital_no' ];
    $app_no = $_POST[ 'app_no' ];	
	
   	$stmt = $db->prepare( "SELECT * FROM c_d_remarks WHERE hospital_no=? and cat_type='DH' ORDER BY sn DESC LIMIT 1" );
    $stmt->execute( array( $hospital_no ) );
    if ( $stmt->rowCount() == 0 ) {
        $save_allergy_stmt = $db->prepare( 'INSERT INTO c_d_remarks (complain, app_no, hospital_no, cat_type, prepared_by, created_by)  VALUES (?, ?, ?, ?, ?, ?) ' );
        $save = $save_allergy_stmt->execute( array( $allergies_notes, $app_no, $hospital_no, 'DH', $_SESSION[ 'fullname' ], $_SESSION[ 'id' ] ) );
		
		if($save_allergy_stmt == TRUE ){
			echo 'Saved Successfully';
		}else{
			echo 'Not Successfully';
		}
    
    }else{
        $allergies_row = $stmt->fetch( PDO::FETCH_ASSOC );
        $update_allergy_stmt = $db->prepare( 'UPDATE c_d_remarks SET complain = ?, updated_at = now(), prepared_by = ? WHERE sn=?' );
            $update = $update_allergy_stmt->execute( array( $allergies_notes, $_SESSION[ 'fullname' ], $allergies_row["sn"] ) );
   if($update_allergy_stmt == TRUE ){
			echo 'Saved Successfully';
		}else{
			echo 'Not Successfully';
		}
			

	}
}





if ( isset( $_POST[ 'update_allergies_btn' ] ) ) {
    $allergies_notes = $_POST[ 'allergies_notes' ];
    // $hospital_no = $_POST[ 'hospital_no' ];
    $app_no = $_POST[ 'app_no' ];
    $allergy_sn = $_POST[ 'sn' ];

    $error_status = 1;
    $error_msg = 'Oops! something went wrong...';

    $update_allergy_stmt = $db->prepare( 'UPDATE c_d_remarks SET complain = ?, updated_at = now(), updated_by = ? WHERE sn=?' );
    $update = $update_allergy_stmt->execute( array( $allergies_notes, $_SESSION[ 'id' ], $allergy_sn ) );

    if ( $update == true ) {
        $error_status = 2;
        $error_msg = 'Success: Allergies saved';
        $show_edit_allergy_modal = true;
    } else {
        $show_edit_allergy_modal = true;
    }
}


?>