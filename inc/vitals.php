     <?php

        if (isset($_POST['vitals_links'])) {
            include("../Connections/Conn.php");

            $vital = $_POST['vistal_hx'];

            if (!empty($vital)) {



                if ($vital == 'temp') {
                    $title = 'Temperature<sup>o</sup>C';
                }
                if ($vital == 'weight') {
                    $title = 'Weight <em>(Kg)</em>';
                }
                if ($vital == 'resp_rate') {
                    $title = 'Resperatory Rate';
                }
                if ($vital == 'height') {
                    $title = 'Height <em>(m)</em>';
                }
                if ($vital == 'pulse') {
                    $title = 'Pulse<em> (bpm)</em>';
                }
                if ($vital == 'pulse_read') {
                    $title = 'Pulse<em> (bpm)</em>';
                }
                if ($vital == 'muac_read') {
                    $title = 'MUAC<em> (cm)</em>';
                }
                if ($vital == 'spo2') {
                    $title = 'SpO2';
                }
                if ($vital == 'fbs') {
                    $title = 'FBS<em> (ml/dL)</em>';
                }
                if ($vital == 'rbs') {
                    $title = 'RBS<em> (ml/dL)</em>';
                }
                if ($vital == 'ppbs') {
                    $title = 'PPBS';
                }
                if ($vital == 'GTT') {
                    $title = 'GTT';
                }
                if ($vital == 'FHR') {
                    $title = 'FHR';
                }
                if ($vital == 'bp') {
                    $title = 'Blood Pressure<em> (mmHg)</em>';
                }
                if ($vital == 'bmi') {
                    $title = 'Body Mass Index <em> kg/m&sup2;</em>';
                }

                $hospital_no = $_POST['vitals_links'];


                $hx_stmt = $db->prepare("SELECT * FROM vital_sign WHERE hospital_no = ? and $vital IS NOT NULL AND $vital != '' AND status = '1' ORDER BY sn desc LIMIT 150");
                $hx_stmt->execute(array($hospital_no));


                $values = [];
                $values2 = [];
                $labels = [];

                if ($hx_stmt->rowCount() > 0) { ?>
                 <div style=" max-height:800px; overflow:auto">
                     <table class="table table-bordered" style="font-size:14px;  ">
                         <thead>
                             <tr>
                                 <td><strong>#</strong></td>
                                 <td><strong><?= $title; ?> Reading</strong></td>
                                 <td><strong>Captured Date</strong></td>
                                 <td><strong>Captured by</strong></td>
                             </tr>
                         </thead>
                         <tbody id="">
                             <?php
                                $sn = 1;
                                $vitals = $hx_stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($vitals as $key => $rwx) {
                                    $date = date('d M, Y h:i A', strtotime('' . $rwx["date_ap"]));
                                    array_push($values, floatval($rwx[$vital]));
                                    array_push($labels, $date);

                                ?>
                                 <tr>
                                     <td><?php echo $sn++; ?></td>
                                     <td><?php echo $rwx[$vital];  ?></td>
                                     <td><?php echo date('d M,Y h:i:s a', strtotime($rwx['date_ap'])); ?></td>
                                     <td><?php echo $rwx['prepared_by']; ?></td>
                                 </tr>
                             <?php
                                }
                                ?>
                         </tbody>
                     </table>
                 </div>




             <?php } else { ?>

                 <h2>No Record Found!</h2>
             <?php } ?>

     <?php
            }
            exit;
        }

        $save_bp = null;
        $save_temp = null;
        $save_weight = null;
        $save_resp_rate = null;
        $save_height = null;
        $save_pulse_read = null;
        $save_spo2_read = null;
        $save_fbs_read = null;
        $save_rbs_read = null;
        $save_ppbs_read = null;
        $save_GTT_read = null;
        $save_muac_read = null;




        if (isset($_POST['save-vitals-btn'])) {
            $post_keys = array_keys(array_filter($_POST));
            $hospital_no = $_POST['hospital_no'];
            $date_ap = $_POST['date_ap'];
            /// $vital_lock = $_POST['vital_lock'];

            $columns = '';
            $col_values = '';

            $keys = [];

            $height = floatval($_POST['height']);
            $weight = floatval($_POST['weight']);

            $bmi = '';
            if ($height != 0 && $weight != 0) {
                $bmi = $weight / ($height * $height);
                $bmi = round($bmi, 2);
                // BMI = weight (in kilograms) / (height (in meters))^2
            }

            $col_value_arr = [$hospital_no, $date_ap, $_SESSION["fullname"], $bmi];
            foreach ($post_keys as $key => $_key_) {
                if ($_key_ === 'hospital_no' || $_key_ === 'vistal_hx' || $_key_ === 'date_ap') {
                    continue;
                }

                $value = $_POST[$_key_];


                if ($_key_ === 'bp' || $_key_ === 'bp2') {
                    $_key_ = 'bp';
                    $value1 = isset($_POST['bp']) ? $_POST['bp'] : "";
                    $value2 = isset($_POST['bp2']) ? $_POST['bp2'] : "";
                    $value = $value1 . '/' . $value2;
                }

                if (!in_array($_key_, $keys)) {
                    array_push($col_value_arr, $value);
                    array_push($keys, $_key_);
                    $columns .= " $_key_ ";
                    $col_values .= " ? ";
                    if ($key < count($post_keys) - 1) {
                        $columns .= ",";
                        $col_values .= ",";
                    }
                }
            }




            $columns = rtrim($columns, ','); // Remove the last comma from columns
            $col_values = rtrim($col_values, ','); // Remove the last comma from column values
            if (!empty($col_values)) {
                $placeholders = rtrim(str_repeat('?,', count($col_value_arr)), ',');
                $sql = "INSERT INTO `vital_sign` (`hospital_no`, `date_ap`, `prepared_by`,`bmi`, $columns) VALUES ( $placeholders)";
                $stmt = $db->prepare($sql);
                $saved = $stmt->execute($col_value_arr);
                if ($saved) {
                    echo 'Success: Vitals Saved Successfully';
                } else {
                    echo 'Error: Vitals could not be saved';
                }
            }

            exit;
        }
        ?>




     <?php
        $admission_ = 0;

        if (strtoupper($_SESSION['h_code']) !== 'ZMKC') {
            $stmt = $db->prepare("SELECT 1 FROM diagnosis WHERE item = ? AND description = ? LIMIT 1");
            $stmt->execute(['Birth injury to femur (ICD10: P132)', '1']);

            // If at least one match exists, disable admission
            if ($stmt->fetchColumn()) {
                $admission_ = 1;  // Admission disabling flag
            }
        }
        ?>


     <form id="vitals-form">

         <h3 style="color:red; ">ENTER NEW <u><i>READINGS</i></u> BELOW: </h3>


         <label>Blood Pressure (BP):</label>
         <table width="100%" border="0">
             <tbody>
                 <tr>
                     <td><input type="number" id="bp" name="bp" class="form-control" maxlength="3" onkeyup="checkPass(); return false;" placeholder="systolic"></td>
                     <td><input type="number" id="bp2" name="bp2" class="form-control" maxlength="3" onkeyup="checkPass2(); return false;" placeholder="diastolic"></td>
                 </tr>
                 <tr>
                     <td align="center"><span class="confirmMessage" id="confirmMessage"></span></td>
                     <td align="center"><span class="confirmMessage2" id="confirmMessage2"></span></td>
                 </tr>
             </tbody>
         </table>

         <div class="row">
             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Temperature (<sup>o</sup>C):</label>
                     <input type="text" id="temp" name="temp" class="form-control" maxlength="8" onkeyup="checkPass3()">
                     <span class="confirmMessage3" id="confirmMessage3"></span>
                 </div>
             </div>


             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Weight(kg): </label>
                     <input type="text" id="weight" name="weight" class="form-control" maxlength="8">
                     <span class="confirmMessage_" id="confirmMessage_"></span>
                 </div>
             </div>
         </div>
         <div class="row">
             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Respiratory Rate:</label>
                     <input type="text" id="resp_rate" name="resp_rate" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>

             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Height (m):</label>
                     <input type="text" id="height" name="height" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>
         </div>
         <div class="row">
             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Pulse (bpm):</label>
                     <input type="text" id="pulse_read" name="pulse_read" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>

             <div class="col-md-6">
                 <div class="form_sep">
                     <label>MUAC (cm):</label>
                     <input type="text" id="muac_read" name="muac_read" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>
         </div>
         <div class="row">
             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Oxygen SPO2:</label>
                     <input type="text" id="spo2" name="spo2" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>

             <div class="col-md-6">
                 <div class="form_sep">
                     <label>FBS(mg/dL):</label>
                     <input type="text" id="fbs" name="fbs" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>
         </div>
         <div class="row">


             <div class="col-md-6">
                 <div class="form_sep">
                     <label>RBS(mg/dL):</label>
                     <input type="text" id="rbs" name="rbs" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="form_sep">
                     <label>PPBS:</label>
                     <input type="text" id="ppbs" name="ppbs" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>
         </div>

         <div class="row">

             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Fetal Heart Rate (FHR)</label>
                     <input type="text" id="FHR" name="FHR" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>

             </div>
             <div class="col-md-6">
                 <div class="form_sep">
                     <label>Glucose tolerance (GTT)</label>
                     <input type="text" id="GTT" name="GTT" class="form-control" maxlength="8">
                     <span class="confirmMessage" id="confirmMessage"></span>
                 </div>
             </div>



         </div>
         <div class="row">
             <div class="col-md-12">

                 <div class="form_sep">
                     <label>Comments</label>
                     <textarea name="comments" class="form-control" cols="4" rows="5"></textarea>
                 </div>


                 <div class="form_sep">
                     <input type="datetime-local" name="date_ap" id="date_ap" value="<?= date('Y-m-d H:i'); ?>" class="form-control" required>
                 </div>

                 <?php if ($admission_ == 0) { ?>

                     <div align="left" class="form_sep">
                         <button type="submit" class="btn btn-success" name="save-vitals-btn" id="save-vitals-btn">Save Vitals</button>
                     </div>

                 <?php } else { ?>
                     <hr>
                     <h4 style="color:brown;">Vital Rights Disabled</h4>
                 <?php } ?>
             </div>
         </div>



         <hr>
         <h3>Patient Vital Signs History</h3>
         <div class="form_sep">
             <label for="">Select Report Type:</label>
             <select name='vistal_hx' id="vistal_hx" class="form-control">
                 <option selected='selected' value=''>-- Select Vital Report Type</option>
                 <option value='bp'>Blood Pressure</option>
                 <option value='temp'>Temperature</option>
                 <option value='weight'>Weight</option>
                 <option value='resp_rate'>Resperatory Rate</option>
                 <option value='height'>Height</option>
                 <option value='pulse_read'>Pulse</option>
                 <option value='muac_read'>muac_read</option>
                 <option value='spo2'>SpO2</option>
                 <option value='fbs'>FBS</option>
                 <option value='rbs'>RBS</option>
                 <option value='ppbs'>PPBS</option>
                 <option value='FHR'>FHR</option>
                 <option value='GTT'>GTT</option>
                 <option value='bmi'>BMI</option>
             </select>
         </div><br>
         <input type="button" name="edit_users" value="Show Report" data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-sm btn-success vitals_links" />

         &nbsp; : &nbsp;
         <?php $emr = base64_encode(base64_encode($hospital_no)); ?>
         <a href="vitals_only.php?v_6474747=<?php echo  $emr; ?>" class="btn btn-sm btn-primary">Print Vital Sign Report</a>
         <hr>


         <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">

         <br>

     </form>


     <div class="modal inmodal" id="vitals_links_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog modal-xl">
             <div class="modal-content animated bounceInRight">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                     <h4 class="modal-title">Vitals Sign</h4>
                 </div>

                 <div class="modal-body" id="vitals_links_body">


                 </div>
                 <div>

                     <!-- Add canvas element -->
                     <canvas id="myChart"></canvas>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                 </div>

             </div>
         </div>
     </div>






     <script>
         function checkPass() {
             var bp = document.getElementById('bp');
             var message = document.getElementById('confirmMessage');

             var value = parseFloat(bp.value);

             var high = "#FE2E2E";
             var warning = "#FF9900";
             var normal = "#3ADF00";
             var low = "#58ACFA";

             if (isNaN(value)) {
                 message.innerHTML = "Enter valid BP";
                 message.style.color = high;
                 bp.style.backgroundColor = high;
                 return;
             }

             if (value < 90) {
                 message.innerHTML = "Low BP";
                 bp.style.backgroundColor = low;
                 message.style.color = low;

             } else if (value <= 120) {
                 message.innerHTML = "Normal BP";
                 bp.style.backgroundColor = normal;
                 message.style.color = normal;

             } else if (value <= 129) {
                 message.innerHTML = "Elevated BP";
                 bp.style.backgroundColor = warning;
                 message.style.color = warning;

             } else if (value <= 139) {
                 message.innerHTML = "Hypertension Stage 1";
                 bp.style.backgroundColor = high;
                 message.style.color = high;

             } else {
                 message.innerHTML = "Hypertension Stage 2";
                 bp.style.backgroundColor = high;
                 message.style.color = high;
             }
         }


         function checkPass2() {
             var bp2 = document.getElementById('bp2');
             var message = document.getElementById('confirmMessage2');

             var value = parseFloat(bp2.value);

             var high = "#FE2E2E";
             var warning = "#FF9900";
             var normal = "#3ADF00";
             var low = "#58ACFA";

             if (isNaN(value)) {
                 message.innerHTML = "Enter valid BP";
                 message.style.color = high;
                 bp2.style.backgroundColor = high;
                 return;
             }

             if (value < 60) {
                 message.innerHTML = "Low BP";
                 bp2.style.backgroundColor = low;
                 message.style.color = low;

             } else if (value <= 80) {
                 message.innerHTML = "Normal BP";
                 bp2.style.backgroundColor = normal;
                 message.style.color = normal;

             } else if (value <= 89) {
                 message.innerHTML = "Hypertension Stage 1";
                 bp2.style.backgroundColor = warning;
                 message.style.color = warning;

             } else {
                 message.innerHTML = "Hypertension Stage 2";
                 bp2.style.backgroundColor = high;
                 message.style.color = high;
             }
         }


         function checkPass3() {
             var temp = document.getElementById('temp');
             var message = document.getElementById('confirmMessage3');

             var value = parseFloat(temp.value); // IMPORTANT

             var goodColor = "#66cc66";
             var badColor = "#ff6666";

             if (isNaN(value)) {
                 message.innerHTML = "Enter valid temperature";
                 message.style.color = badColor;
                 temp.style.backgroundColor = badColor;
                 return;
             }

             if (value < 36.0) {
                 message.innerHTML = "Low Temperature (Hypothermia)";
                 temp.style.backgroundColor = badColor;
                 message.style.color = badColor;

             } else if (value >= 36.0 && value <= 37.5) {
                 message.innerHTML = "Normal Temperature";
                 temp.style.backgroundColor = goodColor;
                 message.style.color = goodColor;

             } else if (value >= 37.6 && value <= 38.0) {
                 message.innerHTML = "Low-grade Fever";
                 temp.style.backgroundColor = "#ffcc66";
                 message.style.color = "#ff9900";

             } else if (value >= 38.1 && value <= 39.0) {
                 message.innerHTML = "Fever";
                 temp.style.backgroundColor = badColor;
                 message.style.color = badColor;

             } else if (value > 39.0) {
                 message.innerHTML = "High Fever";
                 temp.style.backgroundColor = badColor;
                 message.style.color = badColor;
             }
         }


         function checkPass4() {
             var weight = document.getElementById('weight');
             var message = document.getElementById('confirmMessage');
             var goodColor = "#66cc66";
             var badColor = "#ff6666";

             if (weight.value < 97.7) {
                 weight.style.backgroundColor = goodColor;
                 message.style.color = goodColor;
             } else {
                 weight.style.backgroundColor = badColor;
                 message.style.color = badColor;
             }
         }
     </script>
