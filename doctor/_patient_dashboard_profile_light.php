 <?php

    $total_admission_count = null;

    if (!empty($patient_info)) {
        $dob = $patient_info->dob;
        $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
        $sex = $patient_info->gender;
        $tbl_age = $patient_info->age;
        $phone = $patient_info->phone;
        $patient_manage_by = $patient_info->patient_manage_by;
        $patient_review = $patient_info->patient_review;
        $patient_manage_hx = $patient_info->patient_manage_hx;
        $age = 0;
        $age_full = null;
        if (!empty($dob)) {
            $components = preg_split("/-/", $dob);
            $year = $components[0];
            $age = date('Y') - $year;
            $age_full = $age . ' yrs';

            if ($age == 0) {
                $month = abs(date('m') - $components[1]);
                $age_full = $month . ' Months';
            }
        }

        if ($hosp_no) {
            $hospital_no = $hosp_no;
        }
    }
    ?>

 <div class="row" style="margin-bottom:0">
     <div class="col-md-5">
         <p class="text-center"> <img src="<?php if (file_exists(enrollee_p . $hospital_no . '.' . 'jpg')) {
                                                echo enrollee_p . $hospital_no . '.' . 'jpg';
                                            } else {
                                                echo '../img/no_photo.jpg';
                                            } ?>" alt="" height="80" width="80" class="img-thumbnail user_avatar"></p>
         <h4 class="text-center">
             <span style="font-size:14px"><?php echo $hospital_no; ?></span>
             <strong> <span style="font-size:16px"><?php echo ' / ' . $patient_name; ?></span></strong> <br>
             <?php
                if ($isOnAppoint == true) {
                    echo 'Appointment No: ' . $appointment_number;
                }
                ?>
         </h4>
         &nbsp; : &nbsp;
         <table>
             <tr>
                 <td>
                     <p id="manageBy"></p>
                     <div id="manageHistory_"></div>
                 </td>
                 <td>
                     <?php if ($_SESSION['rights'] != 'PY') {
                            if ($vip == 1 && $_SESSION['rights'] != 'MD') {
                            } else {        ?>
                             <input type="button" name="on_cr" id="mgt_by" data-target="#modal" class="btn btn-success btn-xs" onclick="patient_manager_modal()" style="font-size: 12px; color:white; " />
                     <?php }
                        } ?>
                 </td>
             </tr>
         </table>
     </div>
     <div class="col-md-7">
         <table width='100%'>

             <tr>
                 <td><strong><I style="color:blue;">DOB: </I>
                         <?php

                            if (!empty($dob)) {
                                echo date('jS F Y', strtotime($dob));
                            } else {
                                echo "<i style='color:red;'>Invalid DOB</i>";
                            }

                            ?>
                     </strong>&nbsp;</td>
                 <td><strong><I style="color:blue;">AGE: </I><?= $tbl_age; ?></strong></td>
             </tr>
             <tr>
                 <td><strong><I style="color:blue;">GENDER: </I> <?= ucfirst($patient_info->gender); ?></strong>&nbsp;</td>
                 <td><strong><I style="color:blue;">BLOOD GROUP: </I><?php echo $patient_info->blood_g; ?></strong></td>
             </tr>

             <tr>
                 <td>
                     <strong><i style="color:blue;">TOTAL VISITS: </i>
                         <strong><span id="totalVisits">Loading...</span></strong>
                     </strong>
                 </td>

                 <td>
                     <strong><i style="color:blue;">TOTAL ADMISSIONS: </i>
                         <strong><span id="totalAdmissions">Loading...</span></strong>
                     </strong>
                 </td>
             </tr>

             <tr>
                 <td>
                     <strong><i style="color:blue;">LAST VISIT: </i></strong>
                     <b>
                         <span id="lastVisit">Loading...</span>
                     </b>
                 </td>
                 <td>
                     <strong><i style="color:blue;">PHONE #: </i><?= htmlspecialchars($patient_info->phone); ?></strong>
                 </td>
             </tr>

             <tr>
                 <td colspan="2">
                     <strong><I style="color:blue;">INSURANCE: </I><?php echo $patient_info->insurance_name . ' / ' . $patient_info->insurance; ?></strong>
                 </td>
             </tr>
             <tr>
                 <td>
                     <input type="button" name="on_cr" value="See More Biodata" data-target="#modal"
                         id="<?php echo $hospital_no; ?>" class="btn btn-primary btn-xs bio_data_link" style="font-size: 12px; color:white; " />

                     <div class="consultation-timer">
                         <div>Consultation Time:</div>
                         <div class="timer-display" id="timerDisplay">00:00:01</div>
                     </div>

                     <div id="resultsAlertBox" class="results-available-alert" style="display:none; cursor:pointer;">
                         <div>
                             <b> 👉 View New Results</b>
                             <strong id="doctor_results_count">0</strong>
                         </div>
                     </div>
                     <div id="doctorQueueAlert" class="queue-alert" style="display:none; cursor:pointer; background:#fff3cd; padding:10px; border-radius:5px;">
                         <b>🧑‍⚕️ View Patients Waiting:</b>
                         <strong id="doctor_queue_count">0</strong>
                     </div>

                 </td>
             </tr>

         </table>

     </div>
 </div>