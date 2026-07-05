 <?php

    $requestError = false;
    $requestErrorMessage = null;
    $transplants = [];
    ?>

 <!--- MODALS HERE -->

 <div class="modal inmodal fade" id="bookTransplantModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 <h4 class="modal-title" id="">Transplant Request</h4>
             </div>

             <?php


                if (isset($_POST['patient_for_transplant'])) {
                    $hospital_no =  $_POST['patient_for_transplant'];

                    $patient_info = $Patient->getByHospitalNo($hospital_no);
                    if (!empty($patient_info)) {
                        $patient_insurance = $patient_info->insurance_type;
                        $patient_access_type = $patient_info->services_access;

                        $interest = $patient_info->interest;
                        $insurance = $patient_info->insurance_type;
                        $services_access = $patient_info->services_access;
                        $insurance_no = $patient_info->insurance_no;
                        $add_minus = $patient_info->add_minus;
                        $payment_mode = $patient_info->payment_mode;
                    }

                    $transplant_type =  $_POST['transplant_type'];
                    $done_transplant_before =  $_POST['done_transplant_before'];
                    $number_of_transplant =  $_POST['number_of_transplant'];
                    if (empty($number_of_transplant)) {
                        $number_of_transplant = 0;
                    }



                    $stmt = $db->prepare("SELECT * FROM admission WHERE hospital_no=? AND adm_status = 3 order by sn desc limit 1");
                    $stmt->execute(array($hospital_no));
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $appointment_number = $row["app_no"];
                    } else {

                        $stmt = $db->prepare("SELECT * FROM apptm WHERE hospital_no = ? order by sn DESC LIMIT 1");
                        $stmt->execute(array($hospital_no));
                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $appointment_number = $row["appt_no"];
                        } else {
                            $appointment_number = $hospital_no; ///["appt_no"];			
                        }
                    }




                    $patient_info = $Patient->get(['hospital_no' => $hospital_no]);

                    if (!empty($patient_info)) {
                        $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;

                        $insurance = $patient_info->insurance;

                        $request_type_info = $Transplant->get_services(['sn' => $transplant_type]);

                        $request_type = $request_type_info->item_service;
                        $hosp_price = $request_type_info->hosp_price;
                        $ext_price = $request_type_info->ext_price;
                        $nhis_price = $request_type_info->nhis_price;
                        $item_sn = $request_type_info->sn;
                        $service_id = $request_type_info->sn;
                        $serv_group = $request_type_info->category;

                        $check = $db->prepare('SELECT sn FROM patient_ap_services WHERE app_no = ? AND hospital_no = ? AND serv_group = ? AND drug_sn = ?  AND paystatus = "0" ');
                        $check->execute(array($appointment_number, $hospital_no, $serv_group, $transplant_type));

                        if ($check->rowCount() == 0) {



                            $target_sn = $item_sn;
                            $NHIS_DRUG_CONSUMBL_STATE = 0;
                            $_tariff_table = "hmo_medical_tariff";
                            $amount_invoice = new_service_amount_cal(
                                $db,
                                $hospital_no,
                                $appointment_number,
                                $interest,
                                $insurance_type,
                                $insurance_no,
                                $hosp_price,
                                $ext_price,
                                $nhis_price,
                                $services_access,
                                $add_minus,
                                $payment_mode,
                                $_tariff_table,
                                $target_sn,
                                $NHIS_DRUG_CONSUMBL_STATE
                            );

                            $claim_amt = $amount_invoice["claim_amt"];
                            $amount_paying = $amount_invoice["amount_paying"];
                            $pay_mode = $amount_invoice["pay_mode"];
                            $ccop_int_charge = $amount_invoice["ccop_int_charge"];
                            $hosp_price = $amount_invoice["item_amt"];
                            $invoice_no = $amount_invoice["invoice_no"];

                            if ($amount_paying > 0) {
                                $procedure_amount = $amount_paying;
                            } else {
                                $procedure_amount = $claim_amt;
                            }


                            $dept = $request_type_info->dept;
                            $data['patient_name'] = $patient_name;
                            $data['hospital_no'] = $hospital_no;
                            $data['price_table_id'] = $transplant_type;
                            $data['request_type'] = $request_type;
                            $data['number_of_transplant'] = $number_of_transplant;
                            $data['done_transplant_before'] = $done_transplant_before;
                            $data['amount'] = $procedure_amount;
                            $data['appointment_number'] = $appointment_number;
                            $data['created_by'] = $_SESSION['id'];


                            $error_status = 1;
                            $error_msg = "Request failed";

                            ////////// Before saving transplant request
                            // prepare investigation data
                            $datetime = date("Y-m-d H:i:s");
                            $lab_reqno = generateRequestNo($db, "LB", $data['appointment_number']);
                            $lab_data = json_decode(json_encode([
                                'app_no' => $data['appointment_number'],
                                'labrequest_no' => $lab_reqno,
                                'patient' => $hospital_no,
                                'patient_name' => $patient_name,
                                'test_id' => intval($_POST['test_id']),
                                'test_name' => $_POST['test_name'],
                                'lab_cat' => $_POST['test_dept'],
                                'section' => 'Laboratory',
                                'group_id' => $data['appointment_number'],
                                'preferred_specimen' => "",
                                'request_note' => "",
                                'request_date' => $datetime,
                                'request_by' => $_SESSION['fullname'],
                                'lab_combos' => $_POST['test_combo'],
                                'created_by' => $_SESSION['id']
                            ]));

                            $save = $Transplant->save($data);
                            if ($amount_paying != null or $claim_amt != '') {
                                if ($save["save"] == true) {
                                    $save_ = save_patient_ap_service(
                                        $db,
                                        $data['appointment_number'],
                                        $hospital_no,
                                        null,
                                        'Medical Services',
                                        $serv_group,
                                        $dept,
                                        $service_id,
                                        $request_type,
                                        $item_amt,
                                        $claim_amt,
                                        0,
                                        "",
                                        $amount_invoice["invoice_no"],
                                        $_SESSION['fullname'],
                                        $amount_paying,
                                        $pay_mode
                                    );

                                    $investigationSaved = $Investigation->save($lab_data);
                                    $save_lab = save_patient_ap_service(
                                        $db,
                                        $data['appointment_number'],
                                        $hospital_no,
                                        null,
                                        'Laboratory',
                                        'Laboratory',
                                        $_POST['test_dept'],
                                        $_POST['test_id'],
                                        $_POST['test_name'],
                                        0,
                                        0,
                                        0,
                                        "",
                                        $amount_invoice["invoice_no"],
                                        $_SESSION['fullname'],
                                        0,
                                        $pay_mode
                                    );


                                    $error_status = 2;
                                    $requestErrorMessage = $save["message"];
                                    $error_msg = $requestErrorMessage;
                                    $disabled = "disabled";
                                } else {
                                    $error_status = 1;
                                    $requestErrorMessage =  $save["message"];
                                    $error_msg = $requestErrorMessage;
                                    $disabled = "disabled";
                                }
                            } else {
                                $requestError = true;
                                $requestErrorMessage = "Amount cannot be Zero";
                                $error_msg = "Amount cannot be Zero";
                                $error_status = 1;
                            }
                        } else {
                            $requestError = true;
                            $requestErrorMessage = "This patient has a pending request";
                            $error_msg = "This patient has a pending request";
                            $error_status = 1;
                        }
                    } else {
                        $requestError = true;
                        $requestErrorMessage = "Patient data not found";
                        $error_msg = "Patient data not found";
                        $error_status = 1;
                    }
                }

                ?>

             <?php
                if ($requestErrorMessage != null) {
                ?>
                 <div class="alert alert-<?= $requestError ? 'danger' : 'success'; ?>"><?= $requestErrorMessage; ?></div>
             <?php
                }
                ?>

             <div class="modal-body" style="min-height: 300px;">

                 <form action="#" method="post" id="transplant_request_form">


                     <div class="form-group">


                         <?php
                            if (isset($_GET['patient'])) {
                            ?>
                             <label for="reg_input_no" class="req">Patients</label>
                             <h3>Patient ID: <?= cleanInput($_GET['patient']); ?></h3>
                             <p><input type="hidden" name="patient_for_transplant" value="<?= cleanInput($_GET['patient']); ?>" required></p>
                         <?php
                                $transplants = $Transplant->get(['hospital_no' => $hospital_no], true);
                            } else {
                            ?>

                             <label for="reg_input_no" class="req">Search for Patient</label>
                             <input type="text" style="padding: 20px" class="typeahead form-control  " data-provide="typeahead" id="search_patient_input" placeholder="Search for Patient [Name, Hospital ID or Phone Number" autocomplete="off">
                             <input type="hidden" name="patient_for_transplant" id="search_patient_hospital_no">

                         <?php
                            }


                            ?>

                     </div>


                     <div class="form-group">
                         <label class="req">Transplant Type:</label>
                         <select data-placeholder="Choose Procedures" class="form-control" name="transplant_type" id="transplant_type" required>
                             <option value="">Select</option>

                             <?php
                                $get_services = $Transplant->get_services();
                                foreach ($get_services as $key => $service_) {

                                    echo ' <option value="' . $service_->sn . '">' . $service_->item_service . '</option>';
                                }
                                ?>
                         </select>
                     </div>
                     <br>
                     <div class="form-group">

                         <?php
                            $no_of_transplants = count($transplants);
                            if ($no_of_transplants == 0) {
                            ?>
                             <label for="reg_input_no" class="req">Have you done transplant before ? </label>
                             <select data-placeholder="Choose Procedures" class="form-control" name="done_transplant_before" id="done_transplant_before" required>
                                 <option value="">Select</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                             </select>
                         <?php
                            } else {
                            ?>
                             <input type="hidden" name="done_transplant_before" id="done_transplant_before" value="Yes">

                         <?php
                            }
                            ?>

                     </div>
                     <br>
                     <div class="form_sep" id="number_of_transplant_wrap">
                         <label for="reg_input_no" class="req">Number of Transplant: </label>
                         <input type="number" name="number_of_transplant" id="number_of_transplant" class="form-control">
                     </div>
                     <br>
                     <div class="form_sep">
                         <label for="reg_input_no" class="req">Invenstigation: </label>
                         <input type="text" style="padding: 20px" class="typeahead form-control  " data-provide="typeahead" id="search_lab_typeahead" placeholder="Search for Lab Invenstigation" autocomplete="off" required>
                         <input type="hidden" name="test_id" id="search_lab_typeahead_id" required>
                         <input type="hidden" name="test_name" id="search_lab_typeahead_name" required>
                         <input type="hidden" name="test_dept" id="search_lab_typeahead_dept" required>
                         <input type="hidden" name="test_combo" id="search_lab_typeahead_combo" required>
                     </div>

                 </form>
                 <br>
                 <br>
                 <div class="form_sep">



                     <div class="pull-left">
                         <button type="submit" class="btn btn-success btn btn-sm" name="transplant_request_btn" id="transplant_request_btn" <?= $disabled; ?>>Send Request</button>
                     </div>

                     <div class="pull-right">
                         <a href="index.php?transplant" class="btn btn-danger btn-sm">Close</a>
                     </div>

                 </div>

                 <div>

                     <?php
                        if (isset($_GET['hosp_no']) && $hospital_no != null) {
                        ?>
                         <hr>
                         <h3>Request History</h3>
                         <table class="table" border="2">
                             <thead>
                                 <tr>
                                     <th>#</th>
                                     <th>Request Type</th>
                                     <th>Requested By</th>
                                     <th> Date Requested</th>
                                     <th> </th>
                                 </tr>
                             </thead>
                             <tbody>

                                 <?php




                                    foreach ($transplants as $key => $transplant) {
                                        $user = $AdminUser->find($transplant->created_by);
                                        $created_by = null;
                                        if (!empty($user)) {
                                            $created_by = $user->fullname;
                                        }
                                    ?>
                                     <tr>
                                         <td><?php echo $sn++; ?></td>
                                         <td><?php echo $transplant->transplant_type; ?></td>
                                         <td><?php echo $created_by; ?></td>
                                         <td><?php echo date('d M, Y', strtotime('' . $transplant->created_at . '')); ?></td>
                                         <td>
                                             <a href="index.php?transplant&trs=<?php echo base64_encode(base64_encode($transplant->id)); ?>" class="btn btn-xs btn-success"> View</a>
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




             </div>
         </div>
     </div>
 </div>
 <?php

    if (isset($_POST['patient_for_transplant']) || $requestErrorMessage != null) {
    ?>
     <script>
         $('#bookTransplantModal').modal('show');
     </script>
 <?php
    }

    ?>

 <script>
     $(document).on('click', '#transplant_request_btn', function() {
         let patient = $('#patient_for_transplant').val();
         let transplant_type = $('#transplant_type').val();

         if (patient == '' || transplant_type == '') return alert('Please select patient and transplant type');

         $('#transplant_request_form').submit()
     });

     $(document).on('change', '#done_transplant_before', function() {

         if ($('#done_transplant_before').val() == 'Yes') {
             $('#number_of_transplant_wrap').show("fast");
             $('#number_of_transplant').val("1");
         } else {
             $('#number_of_transplant').val("0");
             $('#number_of_transplant_wrap').hide("fast");
         }


     });
     $('#number_of_transplant_wrap').hide("fast");
 </script>
 <script>
     var lab_results = [];
     $('#search_lab_typeahead').typeahead({

         source: function(query, query_response) {
             console.log($('#search_lab_typeahead').val())
             $.ajax({
                 url: "search_labs.php",
                 method: "POST",
                 data: {
                     search_labs: true,
                     input_text: $('#search_lab_typeahead').val()
                 },
                 dataType: "json",
                 success: function(data) {

                     lab_results = data;
                     query_response($.map(data, function(item) {

                         return item.name;

                     }));
                 }

             })
         },
         updater: function(item) {
             lab_results.forEach(element => {
                 if (element.name == item) {
                     $('#search_lab_typeahead_id').val(element.sn)
                     $('#search_lab_typeahead_name').val(element.name)
                     $('#search_lab_typeahead_dept').val(element.dept)
                     $('#search_lab_typeahead_combo').val(element.combo_test)
                     return item;
                 }
             });
             return item
         }
     });
 </script>