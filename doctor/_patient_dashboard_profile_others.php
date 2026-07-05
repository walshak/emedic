<div class="panel-group" id="accordion">
     <div class="panel panel-default">
         <div class="panel-heading">
             <h4 class="panel-title">
                 <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"> [ See more details ] </a>
             </h4>
         </div>
         <div id="collapseThree" class="panel-collapse collapse">
             <div class="panel-body">

                 <table class="table border">
               
                     <tr>
                         <td><strong>Marital Status:</strong> </td>
                         <td colspan="2"><strong>Date Captured: </strong></td>
                     </tr>
                     <tr>
                         <td><?php echo $patient_info->marital_status; ?></td>
                         <td colspan="2">
                         <?php if ($patient_info->date_capture == ''  or $patient_info->date_capture == '0000-00-00') {
                                    echo '';
                                } else {
                                    echo date('d M,Y h:i a', strtotime($patient_info->date_capture));
                                } ?>
                         </td>
                     </tr>
                     <tr>
                         <td><strong>Tribe</strong></td>
                         <td><strong>State/LGA:</strong> </td>
                         <td><strong>Nationality:</strong> </td>
                     </tr>
                     <tr>
                         <td><?php echo $patient_info->tribe; ?></td>
                         <td><?php echo $patient_info->state_lga; ?></td>
                         <td><?php echo $patient_info->nationality; ?></td>
                     </tr>
                     <tr>
                         <td><strong>Occupation</strong></td>
                         <td></td>
                         <td></td>
                     </tr>
                     <tr>
                         <td><?php echo $patient_info->occupation; ?></td>
                         <td></td>
                         <td></td>
                     </tr>
                 </table>

                 <?php if ($patient_info->insurance != 'Private(Self)') { ?>

                     <br>
                     <h3 class="heading_a" style="color:#F00">Insurance / <?php echo $patient_info->insurance_name ?></h3>

                     <table class="table border">
                         <tr style="background: #666; color: #FFF;">
                             <td width="33%"><strong>Insurance Name:</strong> </td>
                             <td width="34%"><strong>Membership/NHIS No.:</strong></td>
                             <td width="33%"><strong>Membership:</strong> </td>
                         </tr>
                         <tr>
                             <td><?php echo $patient_info->hmo_no . ' / ' . $patient_info->insurance; ?></td>
                             <td><?php echo $patient_info->nhis_no . '-' . $patient_info->nhis_no_ext; ?></td>
                             <td><?php if ($patient_info->nhis_no_ext == '0') {
                                        echo 'Principal';
                                    } elseif ($patient_info->nhis_no_ext == '1') {
                                        echo 'Spouse';
                                    } elseif ($patient_info->nhis_no_ext == '2') {
                                        echo 'Dependant 2';
                                    } elseif ($patient_info->nhis_no_ext == '3') {
                                        echo 'Dependant 3';
                                    } elseif ($patient_info->nhis_no_ext == '4') {
                                        echo 'Dependant 4';
                                    } else {
                                        echo 'Extra Dependant';
                                    }
                                    ?></td>
                         </tr>
                     </table>

                 <?php } else { ?>
                     <br>
                     <h3 class="heading_a" style="color:#F00">Insurance/PRIVATE (SELF)</h3>
                 <?php } ?>

                 <br>
                 <h3 class="heading_a">Contact information</h3>
                 <table class="table border">
                     <tr style="background: #666; color: #FFF;">
                         <td><strong>Phone No:</strong> </td>
                         <td><strong>Email:</strong> </td>
                         <td><strong>Address:</strong> </td>
                     </tr>
                     <tr>
                         <td><?php echo $patient_info->phone; ?></td>
                         <td><?php echo $patient_info->email; ?></td>
                         <td><?php echo $patient_info->addr; ?></td>
                     </tr>
                 </table>


                 <br>
                 <h3 class="heading_a">Next of kin Information</h3>
                 <?php
                    $stmt = $db->query("SELECT * FROM guardian_tbl WHERE patient_id='$hosp_no' order by guardian_id");
                    if ($stmt->rowCount() > 0) { ?>

                     <table class="table border">
                         <tr style="background: #666; color: #FFF;">
                             <td width="1%">#</td>
                             <td width="20%">Next of Kin Name: </td>
                             <td>Phone No / Relationship: </td>
                             <td width="20%">Address: </td>
                         </tr>

                         <?php
                            $n = 1;
                            while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                             <tr>
                                 <td><?php echo $n; ?></td>
                                 <td><?php echo $patient_info->guardian_Name; ?></td>
                                 <td><?php echo $patient_info->guardian_phone . ' / ' . $patient_info->guardian_relationship; ?></td>
                                 <td><?php echo $patient_info->guardian_address; ?></td>
                             </tr>
                         <?php $n++;
                            } ?>

                     </table>
                 <?php } ?>


             </div>
         </div>
     </div>
 </div>