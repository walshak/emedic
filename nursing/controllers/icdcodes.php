<?php session_start();
include( '../../Connections/Conn.php' );
include( '../objects.php' );
include( '../helpers.php' );

if ( isset( $_POST['search_icdcodes'] ) ) {
    header( 'Content-Type: application/json' );
   
    $input_text = $_POST['input_text'];
    
    $stmt= $db->query("SELECT item AS name  FROM diagnosis WHERE (item LIKE '%$input_text%') ");
    $icdcodes =  $stmt->fetchAll(PDO::FETCH_ASSOC) ;
     echo json_encode($icdcodes);
     exit;
}

?>


