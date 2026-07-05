 <?php include("Connections/Conn.php");?>

<?php
session_start();


if(isset($_POST["validate_"])) { 

	$validate_=$_POST["validate_"];
    $PARTT = explode("__", $validate_);

    $sn = $PARTT[0];
    $service_table_id = $PARTT[1];

    $paystatus = 1;
    $invoice_status = 1;
    $pay_mode = 'spkage';
    $claim_valid_by = $_SESSION['fullname'] . '/<br>' . date('d-m-y h:i:s a');
    $transc_date = date("Y-m-d H:i:s");
    
    // Prepare the SQL statement
    $updateSQL = "UPDATE patient_ap_services SET 
        paystatus = :paystatus,
        invoice_status = :invoice_status,
        transact_date = :transact_date,
        claim_valid_by = :claim_valid_by,
        pay_mode = :pay_mode,
        med_frequency = :med_frequency
        WHERE sn = :sn AND paystatus = 0";
    
    // Prepare the statement
    $stmt = $db->prepare($updateSQL);
    
    // Bind the parameters
    $stmt->bindParam(':paystatus', $paystatus, PDO::PARAM_INT);
    $stmt->bindParam(':invoice_status', $invoice_status, PDO::PARAM_INT);
    $stmt->bindParam(':transact_date', $transc_date, PDO::PARAM_STR);
    $stmt->bindParam(':claim_valid_by', $claim_valid_by, PDO::PARAM_STR);
    $stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
    $stmt->bindParam(':med_frequency', $service_table_id, PDO::PARAM_STR);
    $stmt->bindParam(':sn', $sn, PDO::PARAM_STR);
    
    // Execute the statement
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Validated was successful.";
    } else {
        echo "Already Validated.";
    }

    exit;


}

if(isset($_POST["un_validate_"])) { 

	$un_validate_=$_POST["un_validate_"];
    $PARTT = explode("__", $un_validate_);

    $sn = $PARTT[0];
    $service_table_id = $PARTT[1];

    $updateSQL34 = "UPDATE patient_ap_services SET invoice_status=1, paystatus=0,transact_date='', claim_valid_by='', med_frequency='' 
    WHERE sn='$sn' and drug_status =0";
	$db->exec($updateSQL34);

}



if(isset($_POST["validate_package_id"])) { 
		$hospital_no=$_POST["validate_package_id"];
    $stmt = $db->prepare("SELECT s.* FROM patient_ap_services as s
    INNER JOIN prices_table as p ON s.drug_sn = p.sn
    WHERE s.hospital_no = :hospital_no and s.paystatus=1 and p.special_package=1 ");
    $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount()>0){
    while($row_d = $stmt->fetch(PDO::FETCH_ASSOC)){

        $service_table_id = $row_d['drug_sn']; 
        $date_entry = $row_d['date_entry']; 
        $item_services = $row_d['item_services']; 

        ?>
            <h2>PACKAGE NAME: <?= $item_services; ?></h2> <hr>
        <?php

                    $stmtc = $db->prepare("SELECT * FROM special_package WHERE service_table_id = :service_table_id order by service_type");
                    $stmtc->bindParam(':service_table_id', $service_table_id, PDO::PARAM_STR);
                    $stmtc->execute();
                    if($stmtc->rowCount()>0){
                    while($row_d1 = $stmtc->fetch(PDO::FETCH_ASSOC)){

                        $service_title = $row_d1['service_title'] ;
                        $service_type =  $row_d1['service_type'] ;
                        $duration =  $row_d1['duration'];
                        if($duration ==''  or $duration =='0'){
                            $validity = $duration = 1;
                                 }else{
                            $validity = $duration =  $row_d1['duration'] ;
                                }


                        $service_id =  $row_d1['service_id'] ;

                        if($service_type == 'investigations'){
                            $search_pp = " AND (serv_group ='Radiology' or serv_group ='Laboratory') AND item_services='$service_title'";
                        }elseif($service_type == 'pharmacy'){
                            $search_pp = " AND serv_group ='Pharmacy'  AND drug_sn = '$service_id'";
                        //]elseif($service_type == 'medical_services'){
                         //  $serv_group ='Medical Services';
                         //  AND serv_group = :serv_group 
                        }else{
                            $search_pp = " AND drug_sn = '$service_id'";
                        }
                         
                        $date_entry = date('Y-m-d', strtotime($date_entry));

                        // Prepare the SQL statement with placeholders
                        $stmtc2 = $db->prepare("SELECT * FROM patient_ap_services 
                                                    WHERE DATE(date_entry) >= :date_entry 
                                                   
                                                    AND hospital_no = :hospital_no  $search_pp
                                                    ORDER BY item_services");
                        
                        // Bind the parameters
                        $stmtc2->bindParam(':date_entry', $date_entry);
                        //$stmtc2->bindParam(':serv_group', $serv_group);
                      ///  $stmtc2->bindParam(':service_id', $service_id);
                        $stmtc2->bindParam(':hospital_no', $hospital_no);
                        
                        // Execute the statement
                        $stmtc2->execute();
                        
                        if ($stmtc2->rowCount() > 0) {?>
<h3><span style="color:blue; ">ADDED: </span><?= $service_title; ?></h3>
<table class="table table-striped" id="search_table">                  
                            <thead>
                            <tr>
                                <th data-toggle="true">Date Entered</th>
                                <th data-toggle="true">Entered By</th>
                                <th data-toggle="true">Price</th>
                                <th data-toggle="true">Qty</th>
                                <th data-toggle="true">Pay</th>
                                <th data-toggle="true">Processed</th>
                                <th data-toggle="true">Processed By</th>
                                <th data-toggle="true">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                         <?php   
                         while($rwx2 = $stmtc2->fetch(PDO::FETCH_ASSOC)){
                                $paystatus = $rwx2['paystatus'];
                                $drug_status = $rwx2['drug_status'];
                                $transact_date = $rwx2['transact_date'];
                                $pay_mode = $rwx2['pay_mode'];

                                date_default_timezone_set('Africa/Lagos');
                                $Current_date=date('Y-m-d H:i:s');
                                            $date1 = new DateTime($Current_date);
                                            $date2 = new DateTime($transact_date);
                                            $diff = $date2->diff($date1);	
                                            $day=$diff->format('%a');
                                                
                                $med_frequency = $rwx2['med_frequency'];   /// tag for service_table_id
                                $tag = $rwx2['sn'] . '__' . $service_table_id;
                                $tag2 = $rwx2['sn'] ;


                                if($paystatus == 1 && $med_frequency == $service_table_id){
                                   $Validated = 'Validated';
                                }else{
                                    $Validated = 'Paid'; 
                                }
                         ?>


<tr>  
                                    <td><?php echo date('d-m-Y', strtotime($rwx2['date_entry']));; ?></td>
                                    <td><?php echo $rwx2['prepared_by']; ?></td>
                                    <td><?php echo number_format($rwx2['hosp_price']); ?></td>
                                    <td><?php echo $rwx2['qty']; ?></td>
                                    <td><?php echo number_format($rwx2['pay']); ?></td>
                                    <td><?php if($rwx2['transact_date'] == '0000-00-00 00:00:00') { echo 'Pending';}else{echo date('d-m-Y', strtotime($rwx2['transact_date'])); } ?></td>
                                    <td><?php echo $rwx2['claim_valid_by']; ?></td>
                                    <td>
                        <?php if ($paystatus == 1 && $pay_mode == 'spkage' && $day <= 2 && $drug_status == 0){ ?>
<b style="color:blue;"><?= $Validated; ?></b><br>   
<button class="btn btn-danger btn-xs" id="undo_<?= $tag; ?>" onClick="un_validate_pckage_item('<?= $tag; ?>')" >Undo Validated</button>   

                                       <?php  }elseif ($paystatus == 1){  ?>
                                            <b style="color:blue;"><?= $Validated; ?></b>   
                                        <?php 
                                            $validity = $validity - 1; 
                                    }elseif ($paystatus == 0 && $validity <= $duration){ 
                                         
                                            ?>

<?php if($validity==0){ ?> 
    <b style="color:red;">Exceed</b>    
<?php }else{
    
    if($rwx2['invoice_status'] == 0 ){ ?>
    <b style="color:red;">Invoice Pending</b>  
    <?php }else{ ?>
    <button class="btn btn-warning btn-xs" id="undo_<?= $tag; ?>" onClick="validate_pckage_item('<?= $tag; ?>')" >&nbsp;Validate&nbsp;</button>   
    <?php } ?>
 
<?php } ?>    
                                <?php 
                                       $validity = $validity - 1;


                                    }else{
                                        
                                        ?>
                                        <b style="color:red;">Exceed</b>    
                                        <?php } ?>   
                                             
                                 </td>

   
             
                      </tr>




                        <?php  }  ?>

           
                        </tbody>
              </table>

            <?php } else { ?>

                    <h3><span style="color:red; ">NOT ADDED: </span><?= $service_title; ?></h3>

            <?php  }


?>








<?php
                    

                    }
                }

            


       
    }
    }else{
        echo 'Not Available';
    }
exit;

}
?>







