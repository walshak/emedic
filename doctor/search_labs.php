<?php session_start();
include( '../Connections/Conn.php' );
include('/objects.php' );
include( 'helpers.php' );

if ( isset( $_POST['search_labs'] ) ) {
    header( 'Content-Type: application/json' );
   
    $input_text = $_POST['input_text'];
    $stmt= $db->query("SELECT test AS name, sn  FROM lab_scan WHERE test LIKE '%$input_text%'  ");
    $labs =  $stmt->fetchAll(PDO::FETCH_ASSOC) ;
     echo json_encode($labs);
     exit;
}

?>