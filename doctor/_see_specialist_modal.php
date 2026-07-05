<?php
if ( isset( $_POST[ 'cancelConsultationServiceBtn' ] ) ) {
    $error_status = 1;
    $error_msg = 'Oops! something went wrong...';

    $sn = $_POST[ 'sn' ];
    $service_id = $_POST[ 'service_id' ];
    $app_no = $_POST[ 'app_no' ];
    $hospital_no = $_POST[ 'hospital_no' ];

    $price_table_info = $PriceTable->find( $service_id );
    if ( !empty( $price_table_info ) ) {
        if ( $price_table_info->paystatus == 0 ) {
            $item_service = $price_table_info->item_service;
            $deleted = $PatientApService->delete( [ 'sn' => $sn ] );
            if ( $deleted ) {
                $stmt = $db->prepare( "DELETE FROM notes WHERE app_no = ? AND hospital_no = ? and notes_type = 'CONS' AND specialty = ? " );
                $removed = $stmt->execute( array( $app_no, $hospital_no, $item_service ) );
                $error_status = 2;
                $error_msg = 'Consultation has been removed...';
            }
        } else {
            $error_msg = 'Service already paid for...';
        }

    } else {
        $error_msg = 'Service not found...';
    }
}

if ( isset( $_POST[ 'seeSpecialistBtn' ] ) ) {
    echo $consulation_service = intVal( $_POST[ 'consulation_service' ] );
    $price_table_info = $PriceTable->find( $consulation_service );
    $error_status = 1;
    $error_msg = 'Oops! something went wrong...';

    if ( !empty( $price_table_info ) ) {
        $insurance_type = $patient_info->patient_info;
        if ( strtoupper( $insurance_type ) != 'NHIS' ) {
            $price = doubleval( $price_table_info->hosp_price );
        } else {
            $price = doubleval( $price_table_info->nhis_price );
        }
        if ( $price > 0 ) {

            if ( $insurance === 'Private(Self)' ) {
                $claim_amt = 0;
                $amt_paying = $price;
                $pay_mode = 'cash';
            } else {
                $claim_amt = $price;
                $amt_paying = 0;
                $pay_mode = 'cash';
            }

            $dept = $price_table_info->dept;
            $serv_group = 'Consultation';

            $amount_invoice = service_amount_cal( $appointment_number,  $patientAppoint->interest, $patientAppoint->insurance, $patientAppoint->ap_type, $price );
            $save_ = save_patient_ap_service(
                $db,
                $appointment_number,
                $hospital_no,
                $patientAppoint->ap_type,
                $serv_group,
                $serv_group,
                $dept,
                $price_table_info->sn,
                $price_table_info->item_service,
                $price_table_info->hosp_price,
                $amount_invoice[ 'claim_amt' ],
				 $amount_invoice["ccop_int_charge"],
                '',
                $amount_invoice[ 'invoice_no' ],
                $_SESSION[ 'fullname' ],
                $amount_invoice[ 'amount_paying' ],
                $amount_invoice[ 'pay_mode' ]
            );

            if ( $save_->status == 200 ) {
                $error_status = 2;
                $error_msg = 'Good! The service has been added';
            } else {
                $error_msg = 'Failed or service already added before. ';
            }
        } else {
            $error_msg = 'Price for this service is not defined...Pls see the admin';
        }
    }
}

?>
<div class = 'modal inmodal fade' id = 'seeSpecialistModal' tabindex = '-1' role = 'dialog' aria-hidden = 'true' data-keyboard = 'false' data-backdrop = 'static'>
<div class = 'modal-dialog modal-lg'>
<div class = 'modal-content'>
    <form action = "<?php echo $editFormAction; ?>" method = 'post' onsubmit = "return confirm('Are you sure you want to add this service for the patient?')">
<div class = 'modal-header'>
<button type = 'button' class = 'close' data-dismiss = 'modal' aria-hidden = 'true'>×</button>
<h4 class = 'modal-title' id = ''>Specialist Service </h4>
</div>
<div class = 'modal-body' style = 'min-height: 300px'>

        <div>
               <?php

    $pending_requests = $PatientApService->get( [ 'serv_group' => 'Consultation', 'cat_type' => 'Consultation', 'hospital_no' => $hospital_no, 'app_no' => $appointment_number, 'paystatus' => '0' ], true );
    if ( count( $pending_requests ) > 0 ) {
        ?>

        <h5>Pending  Requests</h2>
        <table class = 'table table-bordered'>
        <thead>
        <tr>
        <td>SN</td>
        <td>SERVICE</td>
        <td>REQUEST BY</td>
        <td></td>
        </tr>
        </thead>
        <tbody>
        <?php
        $sn = 1;
        foreach ( $pending_requests as $key => $pending_request ) {
            ?>
            <tr>
            <td><?php echo $sn++;
            ?></td>
            <td><?php echo $pending_request->item_services;
            ?></td>
            <td><?php echo $pending_request->prepared_by;
            ?></td>
            <td>
            <form action = "<?php echo $editFormAction; ?>" method = 'post' onsubmit = "return confirm('Are you sure you want to cancel this requesst ?')">
            <input type = 'hidden' name = 'sn' value = "<?php echo $pending_request->sn; ?>">
            <input type = 'hidden' name = 'app_no' value = "<?php echo $appointment_number; ?>">
            <input type = 'hidden' name = 'hospital_no' value = "<?php echo $hospital_no; ?>">
            <input type = 'hidden' name = 'service_id' value = "<?php echo $pending_request->drug_sn; ?>">
            <input type = 'submit' value = 'Cancel' class = 'btn btn-sm btn-danger' name = 'cancelConsultationServiceBtn'>
            </form>
            </td>
            </tr>
            <?php
        }
        ?>

        </tbody>

        </table>
        <?php
    }
    ?>
        </div>
    <br>

<div>
<div>
<label for = 'specailist'>Select Service</label>
<select name = 'consulation_service' id = 'consulation_service' class = 'input-sm chosen-select' style = 'width:350px;' required = 'true' required>
<option value = ''> Select</option>
<?php $stmt = $db->query( "SELECT sn, item_service, hosp_price, nhis_price FROM prices_table
                                     WHERE  price_table = 'Consultation'  AND  ((hosp_price != 0 AND hosp_price  IS NOT NULL) OR (nhis_price != 0 AND nhis_price IS NOT NULL)) " );
while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
    ?>
    <option value = "<?php echo $row['sn']; ?>"><?php echo $row[ 'item_service' ];
    ?></option>
    <?php }
    ?>
    </select>
    <br>
    <br>
    <div class = 'text-left'>


    </div>
    </div>
    </div>
 
    <div>
 
    </div>
    </div>
    <div class="modal-footer">
                           <button type = 'submit' class = 'btn btn-primary btn btn-sm' name = 'seeSpecialistBtn' id = 'seeSpecialistBtn'>Add Service</button>
                         <button type="button" class="btn btn-danger " data-dismiss="modal">Close</button>
                    </div>
                       </form>
    </div>
    </div>
    </div>

 