<div class="modal  fade wow fadeInRight  " id="dialysis-request-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 40%; margin: 0px auto; ">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" >
                <div>
                    <?php include_once('components/_dialysis_request_form.php'); ?>
                </div>
                
                <div>
                   <?php
                            if (isset($_GET['hosp_no']) && $hospital_no != null) {
                             ?>
                       
                                
                             <?php

                                $patient_dialysis_list = $Dialysis->get(['hospital_no' => $hospital_no], true);

                                if(count($patient_dialysis_list) > 0){
                                    ?>
                                               <hr>
                     <h3>Request History</h3>
                                <table class="table" border="2">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Request Type</th>
                                            <th>Requested By</th>
                                            <th> Date Requested</th>
                                            <th> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                                <?php
                                                      foreach ($patient_dialysis_list as $key => $patient_dialysis) {
                                    $user = $AdminUser->find($patient_dialysis->created_by);
                                    $created_by = null;
                                    if(!empty($user)){
                                        $created_by = $user->fullname;
                                    }
                                  ?>
                                    <tr>
                                        <td><?php echo $sn++; ?></td>
                                        <td><?php echo $patient_dialysis->request_type; ?></td>
                                        <td><?php echo $created_by; ?></td>
                                        <td><?php echo date('d M, Y', strtotime(''.$patient_dialysis->request_date.'')); ?></td>
                                        <td>
                                            <a href="dialysis.php?p=<?php echo base64_encode('Open_dialysis') ;?>&d=<?php echo $patient_dialysis->token;?>" class="btn btn-xs btn-success"> View</a>
                                        </td>
                                    </tr>
                                  <?php
                                }
                                                ?>
                                     </tbody>
                                </table>
                                    <?php
                                }
                                
                            }
                            ?>
                </div>
            </div>
            <div class="modal-footer">
                <?php
                    if(isset($_GET['hosp_no'])){
                        ?><button class="btn btn-danger" class="btn btn-danger" data-dismiss="modal">Close</button> <?php
                    }else{
                         ?>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				<?php
                    }
                ?>
                

            </div>
        </div>
    </div>
</div>