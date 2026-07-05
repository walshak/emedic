 <?php
            $stmt = $db->prepare("SELECT  hospital_no,app_no, room_bed, room_bed_sn, date_admit, doc_incharge FROM admission  
              WHERE adm_status='3' order by date_admit DESC");
            $stmt->execute();
            ?>
<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab-1"> Admission List (<?php echo $stmt->rowCount(); ?>)</a></li>
    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">

        
            <br>
            <form action="<?= $editFormAction; ?>" method="post">
                <div class="panel-body">
                    
                   
                    <table class="table table-striped table-bordered table-hover dataTables-example" id="adm_list_tbl">

                        <thead>
                            <tr>
                                <th width="2%">No</th>
                                <th>Patient Name</th>
                                <th>Service Name</th>
                                <th>Doctor In Charge </th>
                                <th>Bed </th>
                                <th>Details</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>


                            <?php

                            $n = 1;
                            
                            while ( $roww = $stmt->fetch()) {

                                $app_no = $roww['app_no'];
                                $appointment_info = $Appointment->get(['appt_no' => $app_no]);

                                $patient_stmt = $db->prepare("SELECT surname,fname FROM enrollee where hospital_no = ?  LIMIT  1 ");
                                $patient_stmt->execute(array($roww['hospital_no']));
                                $patient = $patient_stmt->fetch();

                                

                                ///// doctor in charge 
                                $doc_stmt = $db->prepare("SELECT fullname FROM admin_users where id = ? OR username = ?  LIMIT  1 ");
                                $doc_stmt->execute(array($roww['doc_incharge'], $roww['doc_incharge']));
                                $doctor_incharge = '';
                                if($doc_stmt->rowCount() > 0){
                                    $doctor = $doc_stmt->fetch();
                                    $doctor_incharge = $doctor['fullname'];
                                }
                                
                               

                                


                            ?>
                                <tr>
                                    <td><?php echo $n; ?></td>
                                    <!-- <td> <input type="checkbox" name="patient_list[]" id="patient_list" class="patient_list form-control" VALUE="<?= $roww['hospital_no']; ?>"></td> -->
                                    <td><?php echo $patient['surname'] . ' ' . $patient['fname'] . ' '; ?></td>
                                    <td><?php echo $appointment_info->services_name; ?></td>
                                    <td><?php echo $doctor_incharge; ?></td>
                                    <td><?php echo $roww['room_bed']; ?></td>
                                    <td><?php 
                                        $date_admit =  $roww['date_admit'];
                                        $last_date_ = null;

                                          if($no_of_rounds > 0){
                                            $last_round_rows = $no_of_round_stmt->fetch(PDO::FETCH_ASSOC);
                                            $last_date_  = $last_round_rows[($no_of_rounds-1)]['date_entry'];
                                           $last_date_ = date( 'd, M Y H:i:s A',strtotime( "$last_date_" ) );
                                           
                                        }

                                        echo '<b> Admitted: </b>'.date( 'd, M Y H:i A',strtotime("$date_admit")).' 
                                        <br>   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ['.dateDifference_format( $date_admit ).']
                                        '; 
                                        // echo '<br><b> No. of Rounds: </b>'.$no_of_rounds;
                                        //  echo '<br><b>Last Round: </b>'.$last_date_;
                                      
                                        ?></td>
                                    <td class="text-center">
                                        <a href="patient.php?hosp_no=<?php echo $roww['hospital_no']; ?>" class="btn btn-xs btn-primary">Dashboard  </a>
                                        <a href="index.php?progress&hp=<?php echo $roww['hospital_no']; ?>" class="btn btn-xs btn-success">Progress Note </a>
                                        <a href="index.php?mc=<?php echo $roww['hospital_no']; ?>" class="btn btn-xs btn-info">Drug Chart </a>
                                    </td>
                                </tr>

                                <?php

                                ?>

                            <?php

                                $n++;
                            } ?>


                        </tbody>
                    </table>
                </div>

            </form>
        </div>

    </div>


</div>