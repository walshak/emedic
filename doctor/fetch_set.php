 <?php include("../Connections/Conn.php");


    if (isset($_POST["bio_data_id"])) {
        $hos_no = $_POST["bio_data_id"];
        include("../inc/bio_data.php");
    }


    if (isset($_POST["appointment_number"])) {
        $response = array(
            'status' => 0,
            'message' => '',
        );

        $appointment_number = $_POST["appointment_number"];
        $hospital_no = $_POST["hospital_no"];
        if (empty($appointment_number)) {
            $response['message'] = '';
            $response['status'] = 1;
            echo  json_encode($response);
            exit;
        }

        $status = '0'; // Default status

        $sql = "SELECT 1 FROM notes WHERE hospital_no = ? AND app_no = ? AND status = '1' LIMIT 1";
        $stmt2 = $db->prepare($sql);
        $stmt2->execute(array($hospital_no, $appointment_number));

        if ($stmt2->fetchColumn()) {
            $status = '1';
        }


        $response['message'] = '';
        $response['status'] = 1; //$status; 
        echo  json_encode($response);
        exit;
    }


    if (isset($_POST["medication_id"])) {
        $hosp_no = $_POST["medication_id"]; ?>

     <div class="row">
         <div class="col-lg-12">
             <div class="ibox ">

                 <?php
                    $stmt = $db->prepare("
                            SELECT 
                                p.serv_group, p.remarks, p.pay, p.claim_amt, p.date_entry, p.prepared_by, 
                                p.paystatus, p.qty, p.item_services, stock_table.product_name, 
                                stock_table.dosage, stock_table.strength, p.sn, p.cr 
                            FROM 
                                patient_ap_services AS p 
                            INNER JOIN 
                                stock_table ON p.drug_sn = stock_table.sn 
                            WHERE 
                                p.hospital_no = ? 
                                AND p.invoice_status = '1' 
                                AND p.serv_group = 'Pharmacy' 
                                AND p.drug_status = '1' 
                            ORDER BY 
                                p.transact_date DESC 
                            LIMIT 50
                        ");

                    $stmt->execute(array($hosp_no));
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($results) > 0) {
                    ?>
                     <br>
                     <div class="sideScrollStyle3">
                         <h2>Doctor's Prescription</h2>
                         <table class="table table-striped table-bordered">
                             <thead>
                                 <tr>
                                     <th width="20%">Medicine</th>
                                     <th>Prescription</th>
                                     <th width="5%">Qty</th>
                                     <th width="15%">Date/Entered By</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php foreach ($results as $row): ?>
                                     <tr>
                                         <td><?php echo $row['product_name']; ?></td>
                                         <td><?php echo $row['remarks']; ?></td>
                                         <td><?php echo $row['qty']; ?></td>
                                         <td>
                                             <?php
                                                echo date('d M, Y', strtotime($row['date_entry'])) . '<br>';
                                                echo date('h:i:s a', strtotime($row['date_entry'])) . '<br>';
                                                echo '<b>' . $row['prepared_by'] . '</b>';
                                                ?>
                                         </td>
                                     </tr>
                                 <?php endforeach; ?>
                             </tbody>
                         </table>
                     </div>
                     <div align="right"><i><b>Total Result(s): <?php echo count($results); ?></b></i></div>
                 <?php } else { ?>
                     <div><strong style="color:#F00">No Past Medications</strong></div>
                 <?php } ?>




                 <?php

                    $stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no='$hosp_no' and notes_type='plan' order by date_entry desc limit 100");
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) { ?>

                     <a id="target2"></a>
                     <h2>Doctor's Plan</h2>
                     <table class="table table-striped table-bordered">
                         <thead>
                             <tr>
                                 <th width="15%">Date</th>
                                 <th width="60%">Notes</th>
                                 <th width="20%">Noted By</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                 <tr>
                                     <td><?php echo date('d M,Y', strtotime($row['date_entry'])) . '<br>' . date('h:i a', strtotime($row['date_entry'])); ?></td>
                                     <td><?php echo $row['notes']; ?> </td>
                                     <td><?php echo $row['prepared_by']; ?></td>
                                 </tr>
                             <?php     } ?>

                         </tbody>
                         <tfoot class="hide-if-no-paging">
                             <tr>
                                 <td colspan="6" class="text-center">
                                     <ul class="pagination pagination-sm"></ul>
                                 </td>
                             </tr>
                         </tfoot>
                     </table>

                 <?php } else { ?>

                     <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>

                 <?php } ?>


             </div>
         </div>
     </div>






 <?php }


    if (isset($_POST["investigation_id"])) {
        $hos_no = $_POST["investigation_id"];
    ?>


     <div class="row">
         <div class="col-lg-12">
             <div class="ibox ">
                 <div class="ibox-title">
                     <h5><span class="fa fa-flask"></span>&nbsp; / Laboratory Investigations</h5>
                 </div>

                 <div class="ibox-content">

                     <?php

                        $stmt = $db->query("SELECT * FROM lab_manage WHERE section='Laboratory' and patient='$hos_no' order by sn");
                        if ($stmt->rowCount() > 0) { ?>

                         <table class="table table-striped table-bordered table-hover dataTables-example">
                             <thead>
                                 <tr>
                                     <th width="10%">Requested Date</th>
                                     <th>RQ.#</th>
                                     <th>Type</th>
                                     <th>.</th>
                                 </tr>
                             </thead>
                             <tbody>

                                 <?php
                                    $n = 1;
                                    while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        if ($roww['business_service_center'] == "IN") {
                                            $BSC = "i";
                                        } else {
                                            $BSC = "e";
                                        }
                                    ?>
                                     <tr>
                                         <td><?php echo date('d,M y h:i a', strtotime($roww['request_date'])); ?></td>
                                         <td><?php echo $roww['labrequest_no']; ?></td>
                                         <td><?php echo $roww['test_name']; ?></td>
                                         <td>
                                             <?php
                                                if ($roww['data_capture_status'] == 'queue') {
                                                    echo '<strong>Request On Queue</strong>';
                                                } elseif ($roww['data_capture_status'] == 'specimen' or $roww['data_capture_status'] == 'capture') {
                                                    echo '<strong>Specimen Collected/Captured</strong>';
                                                } elseif ($roww['data_capture_status'] == 'result') {
                                                    echo '<strong>Result Ready awaits approval</strong>';
                                                } else {
                                                ?>

                                                 <input type="button" name="Add" value="Print Result" data-target=".slacker-modal" id="<?php echo $roww['labrequest_no']; ?>" class="btn btn-success btn-xs add_fields_items" />


                                             <?php } ?>
                                         </td>
                                     </tr>
                                 <?php
                                        $n++;
                                    } ?>

                             </tbody>
                         </table>

                     <?php } else { ?>

                         <div class="alert alert-warning">No Laboratory Investigation found</div>
                     <?php } ?>




                 </div>


             </div>
         </div>
     </div>



     <div class="row">
         <div class="col-lg-12">
             <div class="ibox ">
                 <div class="ibox-title">
                     <h5><span class="fa fa-file-movie-o"></span>&nbsp; / Radiology Investigations</h5>
                 </div>

                 <div class="ibox-content">

                     <?php

                        $stmt = $db->query("SELECT * FROM lab_manage WHERE section='Radiology' and data_capture_status='approve' and patient='$hos_no' order by sn");
                        if ($stmt->rowCount() > 0) { ?>

                         <table class="table table-striped table-bordered table-hover dataTables-example">
                             <thead>
                                 <tr>
                                     <th width="10%">Requested Date</th>
                                     <th width="10%">RQ.#</th>
                                     <th width="15%">Type</th>
                                     <th>.</th>
                                 </tr>
                             </thead>
                             <tbody>

                                 <?php
                                    $n = 1;
                                    while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        if ($roww['business_service_center'] == "IN") {
                                            $BSC = "i";
                                        } else {
                                            $BSC = "e";
                                        }
                                    ?>
                                     <tr>
                                         <td><?php echo date('d,M y h:i a', strtotime($roww['request_date'])); ?></td>
                                         <td><?php echo $roww['labrequest_no']; ?></td>
                                         <td><?php echo $roww['test_name']; ?></td>



                                         <td>
                                             <?php
                                                if ($roww['data_capture_status'] == 'queue') {
                                                    echo '<strong>Request On Queue</strong>';
                                                } elseif ($roww['data_capture_status'] == 'specimen' or $roww['data_capture_status'] == 'capture') {
                                                    echo '<strong>Specimen Collected/Captured</strong>';
                                                } elseif ($roww['data_capture_status'] == 'result') {
                                                    echo '<strong>Result Ready awaits approval</strong>';
                                                } else {
                                                ?>

                                                 <input type="button" name="Add" value="Print Result" data-target=".slacker-modal" id="<?php echo $roww['labrequest_no']; ?>" class="btn btn-success btn-xs add_fields_items_2" />

                                             <?php } ?>
                                         </td>
                                     </tr>
                                 <?php
                                        $n++;
                                    } ?>

                             </tbody>
                         </table>

                     <?php } else { ?>

                         <div class="alert alert-warning">No Radiology Investigations found</div>
                     <?php } ?>




                 </div>


             </div>
         </div>
     </div>

 <?php }




    if (isset($_POST["note_inv_id"])) {
        $note_inv_id = $_POST["note_inv_id"];
        $part = explode("__", $note_inv_id);
        $sn = $part[0];


        $stmt = $db->query("SELECT * FROM patient_ap_services WHERE sn='$sn'");
        $row_d = $stmt->fetch(PDO::FETCH_ASSOC);
        $emr = $row_d['hospital_no'];
        $item_services = $row_d['item_services'];
        $pay = $row_d['pay'];
    ?>

     <table class="table table-bordered">
         <tbody>

             <tr>
                 <td>Item Services: </td>
                 <td><?php echo $row_d['item_services']; ?></td>
             </tr>
             <tr>
                 <td>Hospital Price: </td>
                 <td><?php echo $row_d['hosp_price']; ?></td>
             </tr>
             <tr>
                 <td>Claim Price: </td>
                 <td><?php echo $row_d['claim_amt']; ?></td>
             </tr>
             <tr>
                 <td>Quantity: </td>
                 <td><?php echo $row_d['qty']; ?></td>
             </tr>
             <tr>
                 <td>Invoice Number: </td>
                 <td><?php echo $row_d['invoice_no']; ?></td>
             </tr>
             <tr>
                 <td>Invoice Date: </td>
                 <td><?php if ($row_d['invoice_date'] == '' or $row_d['invoice_date'] == '0000-00-00') {
                            echo '';
                        } else {
                            echo date("d M Y H:i:s a ", strtotime($row_d['invoice_date']));
                        } ?></td>
             </tr>
             <tr>
                 <td>Invoice Status: </td>
                 <td><?php if ($row_d['invoice_status'] == 0) {
                            echo 'Invoice Pending';
                        } else {
                            echo 'Invoice Ready';
                        } ?></td>
             </tr>
             <tr>
                 <td>Invoice By: </td>
                 <td><?php echo $row_d['invoice_by']; ?></td>
             </tr>
             <tr>
                 <td>Amount: </td>
                 <td><?php echo $pay; ?></td>
             </tr>

             <tr>
                 <td>Credit Status: </td>
                 <td><?php if ($row_d['cr'] == 1) {
                            echo 'Credit';
                        } else {
                            echo 'No';
                        } ?></td>
             </tr>
             <tr>
                 <td>Dispense Status: </td>
                 <td><?php if ($row_d['dsp_by'] != '') {
                            echo 'Delivered';
                        } else {
                            echo 'No';
                        } ?></td>
             </tr>
             <tr>
                 <td>Paid Status: </td>
                 <td><?php if ($row_d['paystatus'] == 1) {
                            echo 'Amount Paid';
                        } else {
                            echo 'Pending';
                        } ?></td>
             </tr>

             </tr>

         </tbody>
     </table>

 <?php }





    if (isset($_POST["add_drug_id"])) {
        $add_drug_id = $_POST["add_drug_id"];
        $part = explode("__", $add_drug_id);
        $sn = $part[0];


        $stmt = $db->query("SELECT * FROM patient_ap_services WHERE sn='$sn'");
        $row_d = $stmt->fetch(PDO::FETCH_ASSOC);
        $hospital_no = $row_d['hospital_no'];
        $item_services = $row_d['item_services'];
        $pay = $row_d['pay'];
        $claim_amt = $row_d['claim_amt'];
        $drug_sn = $row_d['drug_sn'];
        $claim_interest = $row_d['interest'];
    ?>

     <h4>Are you sure you want to add drug?</h4>
     <hr>

     <table class="table table-bordered">
         <tbody>
             <tr>
                 <td>Item Services: </td>
                 <td><?php echo $row_d['item_services']; ?></td>
             </tr>
             </tr>
         </tbody>
     </table>

     <form action="index.php?presc&hos_no=<?php echo $hospital_no; ?>" method="POST">

         <div class="form_sep">
             <label for="reg_textarea_message" class="req">Enter Qty</label>
             <input type="number" id="qty" name="qty" class="form-control" onkeyup="sum();" min="1" value="1" required />
         </div>
         </div>

         <div class="form_sep">
             <button class="btn btn-success btn btn-sm" type="submit" name="add_drug_invoice" id="add_drug_invoice">Add & Invoice</button>
         </div>
         <input type="hidden" name="pay" value="<?php echo $pay; ?>" />
         <input type="hidden" name="claim_amt" value="<?php echo $claim_amt; ?>" />
         <input type="hidden" name="sale_sn" value="<?php echo $sn; ?>" />
         <input type="hidden" name="drug_sn" value="<?php echo $drug_sn; ?>" />
         <input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>" />
         <input type="hidden" name="claim_interest" value="<?php echo $claim_interest; ?>" />
     </form>

 <?php }


    if (isset($_POST["refill_drug_id"]) or isset($_POST["dsp_oncredit_id"]) or isset($_POST["reverse_id"])) {
        include("../inc/dsp_rvs_cnsumb.php");
    } ?>


 <?php if (isset($_POST["med_rpt_id"])) {
        $hosp_no = $_POST["med_rpt_id"];


        $stmt = $db->prepare("SELECT distinct (date(date_entry)) as dd FROM notes WHERE hospital_no='$hosp_no' order by date_entry desc");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
    ?>
         <h2>Medical Report</h2>
         <table class="footable table table-stripped toggle-arrow-tiny">
             <thead>
                 <tr>
                     <th></th>
                     <th></th>
                 </tr>
             </thead>
             <tbody>

                 <?php
                    $n = 0001;
                    while ($row_visit = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $date_entry = date('Y-m-d', strtotime($row_visit['dd']));
                        //	$doc_name=$row_visit['prepared_by'];
                        //	$entry_date=$row_visit['date_entry'];
                        //echo $row_visit['dd'];
                    ?>
                     <?php
                        $stmt2 = $db->prepare("SELECT * FROM notes WHERE hospital_no='$hosp_no' and date(date_entry)='$date_entry' order by sn desc limit 100");
                        $stmt2->execute();
                        if ($stmt2->rowCount() > 0) {
                            $C = '';
                            $D = '';
                            $plan = '';
                            $note = '';
                            $cons = '';
                            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {

                                if ($row['notes_type'] == 'C') {
                                    $C = $C . $row['notes'] . '<br>';
                                    $dr_name = $row['prepared_by'];
                                }
                                if ($row['notes_type'] == 'D') {
                                    $D .=  ' ' . ($row["status"] == '0' ? "<span style='color:red'>[Edited]</span>" : "") . ' ' . $row['notes'] . '<br>';
                                }
                                if ($row['notes_type'] == 'plan') {
                                    $plan = $plan . $row['notes'] . '<br>';
                                }
                                if ($row['notes_type'] == 'CONS') {
                                    $cons .= '<b>[' . $row["specialty"] . '] </b>' . ($row["status"] == '0' ? "<span style='color:red'>[Edited]</span>" : "") . ' <br> '  . $row['notes'] . '<br>';
                                }
                            }
                        ?>

                         <?php if ($C != '' or $D != '') { ?>
                             <tr>
                                 <td width="10%x"><?php echo date('d M Y', strtotime($row_visit['dd'])); ?></td>
                                 <td width="60%">
                                     <?php if ($C != '') {
                                            echo '<strong>Complaints</strong><br>' . $C . '' . "<i><b>Entered by: </b>" . $dr_name . '</i>';
                                        } else {
                                            echo '<strong>Complaints N/A</strong>';
                                        } ?>
                                 </td>
                                 <td width="35%">
                                     <?php if ($D != '') {
                                            echo '<strong>Diagnosis</strong><br>' . $D;
                                        } else {
                                            echo '<strong> Diagnosis N/A</strong>';
                                        } ?>
                                 </td>
                             </tr>
                         <?php } ?>

                         <?php
                            $stmt44 = $db->prepare("SELECT item_services,cat_type FROM patient_ap_services WHERE
(serv_group='Pharmacy' or serv_group='Laboratory' or serv_group='Radiology') and hospital_no='$hosp_no' and date(date_entry)='$date_entry' order by sn desc");
                            $stmt44->execute();
                            $medication = '';
                            $lab = '';
                            while ($row_details = $stmt44->fetch(PDO::FETCH_ASSOC)) {
                                if ($row_details['serv_group'] == 'Pharmacy') {
                                    $medication = $medication . $row_details['item_services'] . ', ';
                                }
                                if ($row_details['serv_group'] == 'Laboratory' or $row_details['serv_group'] == 'Radiology') {
                                    $lab = $lab . $row_details['item_services'] . ', ';
                                }
                            }

                            $stmt45 = $db->prepare("SELECT * FROM notes 
WHERE hospital_no='$hosp_no' and date(date_entry)='$date_entry' and notes_type='note' order by sn desc");
                            $stmt45->execute();
                            if ($stmt45->rowcount() > 0) {
                                while ($row = $stmt45->fetch(PDO::FETCH_ASSOC)) {
                                    $note = $note . $row['notes'] . '<br>';
                                }
                            }
                            ?>

                         <?php if ($medication != '' or $lab != '' or $plan != '' or $note != '') { ?>
                             <tr>
                                 <td width="10%">
                                     <?php if ($C == '' and $D == '') {
                                            echo date('d M Y', strtotime($row_visit['dd']));
                                        } ?>
                                 </td>
                                 <td width="60%">
                                     <?php if ($medication != '' or $lab != '' or $plan != '') { ?>
                                         <?php if ($plan != '') {
                                                echo '<strong>Plan: </strong><br>' . $plan . '<br>';
                                            } ?>
                                         <?php
                                            if ($medication != '') {
                                                echo '<strong>Medications: </strong> <br>' . $medication . '<br>';
                                            }
                                            if ($lab != '') {
                                                echo '<br><strong>Investigations: </strong> <br>' . $lab . '<br>';
                                            }

                                            if ($cons != '') {
                                                echo '<br><strong>Consultation Notes: </strong> <br> ' . $cons . '<br>';
                                            }
                                            ?>
                                     <?php } else {
                                            echo '<strong>N/A</strong>';
                                        } ?>
                                 </td>
                                 <td width="35%">
                                     <?php if ($note != '') {
                                            echo '<strong>Other Notes</strong><br>' . $note;
                                        } ?>
                                 </td>
                             </tr>
                 <?php             }
                            $n++;
                        }
                    }
                    ?>


             </tbody>
             <tfoot>
                 <tr>
                     <td colspan="2">
                         <ul class="pagination pull-right"></ul>
                     </td>
                 </tr>
             </tfoot>
         </table>



 <?php }
    }

    ?>





 <div class="modal inmodal fade" id="print_lab_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 <h4 class="modal-title" id="">Investigation</h4>
             </div>
             <div class="modal-body" id="print_lab_body">
             </div>
         </div>
     </div>
 </div>


 <script>
     $(document).ready(function() {
         $('#div2').hide('fast');
         $('#div1').hide('fast');

         $('#drug_replace').click(function() {
             $('#div2').hide('fast');
             $('#div1').show('fast');
         });
         $('#drug_new').click(function() {
             $('#div1').hide('fast');
             $('#div2').show('fast');
         });
     });
 </script>