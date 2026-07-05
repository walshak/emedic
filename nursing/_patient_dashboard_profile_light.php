 <?php
    if (!empty($patient_info)) {
        $dob = $patient_info->dob;
        $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
        $sex = $patient_info->gender;
        $age = $patient_info->age;
        ///$age = 0;
        $age_full = null;


        if ($hosp_no) {
            $hospital_no = $hosp_no;
        }
    }
    ?>

 <div class="row" style="margin-bottom:0">
     <div class="col-md-5 text-center">
         <?php
            //$photoPath = enrollee_p . $hospital_no . '.jpg';
            //$photo = file_exists($photoPath) ? $photoPath : '../img/no_photo.jpg';
            ?>
         <p>
             <img src="<?php echo htmlspecialchars($photo); ?>"
                 alt="Patient Photo"
                 height="80"
                 width="80"
                 class="img-thumbnail user_avatar">
         </p>

         <h4>
             <span style="font-size:14px"><?php echo htmlspecialchars($hospital_no); ?></span>
             <strong><span style="font-size:16px"><?php echo ' / ' . htmlspecialchars($patient_name); ?></span></strong><br>

             <?php if (!empty($isOnAppoint) && $isOnAppoint === true): ?>
                 <small>Appointment No: <?php echo htmlspecialchars($appointment_number); ?></small>
             <?php endif; ?>
         </h4>
     </div>

     <div class="col-md-7">
         <table width='100%'>

             <tr>
                 <td><strong><I style="color:blue;">DOB: </I><?php

                                                                if (!empty($dob)) {
                                                                    echo date('jS F Y', strtotime($dob));
                                                                } else {
                                                                    echo "<i style='color:red;'>Invalid DOB</i>";
                                                                } ?></strong>&nbsp;</td>
                 <td><strong><I style="color:blue;">AGE: </I><?= $age; ?></strong></td>
             </tr>
             <tr>
                 <td><strong><I style="color:blue;">GENDER: </I> <?= ucfirst($patient_info->gender); ?></strong>&nbsp;</td>
                 <td><strong><I style="color:blue;">BLOOD GROUP: </I><?php echo $patient_info->blood_g; ?></strong></td>
             </tr>
             <tr>
                 <td><strong><I style="color:blue;">TOTAL VISITS: </I> <?php
                                                                        $stmt = $db->query("SELECT hospital_no FROM apptm where hospital_no='$hospital_no' ");
                                                                        echo '<strong>' . $stmt->rowCount() . '</strong>'; ?></strong></td>
                 <td>
                     <strong><I style="color:blue;">TOTAL ADMISSIONS: </I><?php

                                                                            $stmt = $db->query("SELECT hospital_no FROM admission where hospital_no='$hospital_no' and adm_status='4'");
                                                                            echo '<strong>' . $stmt->rowCount() . '</strong>';
                                                                            $admission_counter = $stmt->rowCount();

                                                                            ?>
                 </td>
             </tr>

             <tr>
                 <td><strong><I style="color:blue;">LAST VISIT: </I></strong>
                     <b>
                         <?php
                            $stmt = $db->query("SELECT ap_date_time FROM apptm where hospital_no='$hospital_no' and appt_no!='$appointment_number' ORDER BY sn DESC LIMIT 1");
                            if ($stmt->rowCount() > 0) {
                                $rw = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo date("jS M Y h:i a", strtotime($rw['ap_date_time']));
                            } else {
                                echo '';
                            }
                            ?>
                     </b>
                 </td>
                 <td>
                     <strong><I style="color:blue;">PHONE #: </I><?php echo  $patient_info->phone; ?></strong>
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
                 </td>
             </tr>
         </table>

     </div>
 </div>