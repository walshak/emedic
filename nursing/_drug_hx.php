<?php

if(!isset($appointment_number)){
    $appointment_number = null;
}

if(isset($_POST['save_medication_plan']) || 
    isset($_POST['update_medication_plan']) || 
    isset($_POST['loadDrugHx']) || 
    isset($_POST['delete_medication_plan']) 
    ){
session_start();
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
include("_session.php");



if(isset($_POST['save_medication_plan']) || 
    isset($_POST['update_medication_plan']) || 
    isset($_POST['delete_medication_plan']) 
    ){
        header('Content-Type: application/json');
    }

    

    if(isset($_POST['loadDrugHx'])){
        $nn = 1;
        $hospital_no = $_POST['hospital_no'];
        $appointment_number = $_POST['appointment_number'];
        $drug_hx_stmt = $db->prepare("SELECT item_services, sn,app_no, remarks, prepared_by,claim_amt, pay, date_entry,drug_status,paystatus,drug_sn   FROM  patient_ap_services 
            WHERE hospital_no = ? AND serv_group = 'Pharmacy' ORDER BY date_entry DESC LIMIT 100");
        $drug_hx_stmt->execute(array($hospital_no));
        $drug_hx  = $drug_hx_stmt->fetchAll(PDO::FETCH_ASSOC);

        $tr ='';
            
            foreach ($drug_hx as $key => $drug_) {
                $amount = $drug_['pay'] != 0 ? $drug_['pay']:$drug_['claim_amt'];
                $id =  $drug_['sn'];
                $drug_sn =  $drug_['drug_sn'];
                if($drug_['drug_status']=='1'){	$drug_status='Dispensed';
				}else{ 
					$drug_status='<strong style="color:red; ">Not Disp.</strong>';
				}
                if($drug_['paystatus']=='1'){
					$paystatus='Paid';
				}else{ 
					$paystatus='<strong style="color:red; ">Not Paid</strong>';
				}				
								
				$days=date_diff_day($drug_['date_entry']);
					
                $rp = '';
                if($appointment_number != "" && $_SESSION['rights'] != 'NS'){
                    $rp  = "<input type='checkbox' class='represcribe-checkbox' value='".$id."'> &nbsp;";
                }
                $tr .= "<tr>
                    <td>".$rp."".$drug_['item_services']."</td>
                    <td>".$drug_['remarks']."</td>
                    <td>".$paystatus .' <strong>/ </strong>'. $drug_status."</td>
                    <td>". $drug_['prepared_by']."</td>
                    <td>". date('d M,y h:iA', strtotime(''.$drug_['date_entry']))."</td>
                    <td>". $days." day/ago</td>
                </tr>";
          
            }

            echo $tr;

            exit;
    }
exit;
}



 if(!isset($drug_plan_hx_url)){
     $drug_plan_hx_url = '_drug_hx.php';
 }

     

?>
  <div id="medication_plan_note_wrap<?php $appointment_number; ?>"></div>

            <div class="ibox-content">                      
			       <table class="table table-striped" id="drug__hx__table">				
                  	<thead>
                        <tr>
                            <th>Medication(s)</th>
                            <th>Prescription</th>
                            <th>Status</th>
                            <th>Entered By</th>
							<th>Entered Date</th>
							<th>Days</th>
                        </tr>
                    </thead>
                    <tbody id="drug_hx_tbl"></tbody>
                </table>
				
	
			</div>				

<div class="modal inmodal" id="chart_me_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Medication History </h4>
			</div>
                <div class="modal-body" id="chart_me_body"> 
                        <h4 class="text-center text-danger">Loading, please wait...</h4>
                </div>
			
			           <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


<div class="modal inmodal" id="med_hx_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Medication History </h4>
			</div>
                <div class="modal-body" id="med_hx_body"> 
                        <h4 class="text-center text-danger">Loading, please wait...</h4>
                </div>
			
			           <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
