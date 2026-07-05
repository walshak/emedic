<?php  include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php'); ?>   

 <?php
if(isset($_POST['patient_on_icu_hdu_link'])){
	$dept_noo = $_POST['patient_on_icu_hdu_link'];
}

 $stmt = $db->prepare("SELECT d.hospital_no,d.app_no,d.room_bed,d.room_bed_sn,d.date_admit,d.doc_incharge,d.floor,
			dd.department FROM admission as d inner join department as dd on dd.sn=d.dept_id
              WHERE adm_status='3' and dd.department LIKE '%$dept_noo%' order by date_admit DESC");
            $stmt->execute();
            ?>


<br>
<h2>Patient(s) On-Admission </h2>
<hr>
<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size:14px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Hosp. No</th>
                                <th>Name</th>
                                <th>Adm. By</th>
                                <th>Service Type</th>
                                <th>Adm. Date</th>
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
                            ?>
                                <tr>
                                    <td><?php echo $n; ?></td>
                                    <td><?php echo $roww['hospital_no']; ?></td>
                                    <td><?php echo $patient['surname'] . ' ' . $patient['fname'] . ' '; ?></td>
                                    <td><?php echo $roww['doc_incharge']; ?></td>									
                                    <td><?php echo $appointment_info->services_name; ?></td>
                                    <td><?php 
                                        $date_admit =  $roww['date_admit'];
                                        $last_date_ = null;

                                        echo ''.date( 'd,M h:i a',strtotime("$date_admit")).'
                                        '; 
                                       
                                        ?></td>
                                    <td align="center">
                                        <a href="patient.php?hosp_no=<?php echo $roww['hospital_no']; ?>&adm" class="btn btn-primary">&nbsp;View&nbsp;</a>
                                    </td>
                                </tr>

                                <?php

                                ?>

                            <?php

                                $n++;
                            } ?>


                        </tbody>
                    </table>
 