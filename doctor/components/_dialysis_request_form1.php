<form action="#" method="POST" id="subject" name="subject" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">


            <h2 align="center">Dialysis Session Booking</h2>
            <hr>
            <div class="form_sep">
                <?php
                if (isset($_GET['patient'])) {
                ?>
                    <h3>Patient ID: <?= cleanInput($_GET['patient']); ?></h3>
                    <p><input type="hidden" name="hospital_no" value="<?= cleanInput($_GET['patient']); ?>" required></p>
                    <div class="text-danger">
                        <?php
                        $stmt = $db->prepare("SELECT * FROM tbl_patient_alerts WHERE hospital_no=? and status = '1' ");
                        $stmt->execute(array($_GET['patient']));
                        $clinical_count = $stmt->rowCount();
                        if ($clinical_count > 0):
                            $alerts = $stmt->fetchAll();
                            echo '<b>Patient Alert(s): </b><br/>';
                            foreach ($alerts as $key => $alert):
                                echo ' - ' . $alert['alert'] . '<br/>';
                            endforeach;
                        endif;
                        ?>
                    </div>

                <?php
                } else {
                ?>

                    <div class="form_sep">
                        <label class="req">Search for Patient:</label>
                        <input type="text" style="padding: 20px" class="typeahead form-control  " data-provide="typeahead" id="search_patient_input" placeholder="Enter Name, Hospital No or Phone No" autocomplete="off">
                        <input type="hidden" name="hospital_no" id="search_patient_hospital_no">
                    </div>
                    <div id="patient_alerts" class="text-danger">-</div>
                <?php
                }
                ?>
            </div>


            <div class="form_sep">
                <label class="req">Request Type:</label>
                <select name="request_type" id="request_type" class="form-control" style="font-size: 14px;" required>
                    <option value="">Select Request Type</option>
                    <?php
                    $get_services = $Dialysis->get_services();
                    foreach ($get_services as $key => $service_) {
                        echo ' <option value="' . $service_->sn . '">' . $service_->item_service . '</option>';
                    }
                    ?>
                </select>

            </div>

            <input type="hidden" value="1" name="number_of_session" id="number_of_session">

            <div class="form_sep"></div>

            <div class="form_sep">

                <table width="100%">

                    <tr>
                        <td width="25%"><label class="req">Duration(Hr): </label></td>
                        <td width="25%"><input type="number" max="5" min="1" name="duration" class="form-control" required></td>

                        <td width="25%"><strong>UF Goal:</strong></td>
                        <td width="25%"><input type="number" max="10" min="1" step="any" name="uf_goal" class="form-control"></td>
                    </tr>

                    <tr>
                        <td width="25%"><label class="req">Heparin Dose: </label></td>
                        <td width="25%"><input type="text" name="heparin_dose" maxlength="30" class="form-control" required></td>

                        <td width="25%"><strong>Dialysate Flow Rate :</strong></td>
                        <td width="25%">
                            <select name="Dialysate_flow_rate" id="Dialysate_flow_rate" class="form-control" style="font-size: 14px;">
                                <option value="" selected>-- select--</option>
                                <option value="100ml/min">100ml/min</option>
                                <option value="200ml/min">200ml/min</option>
                                <option value="300ml/min">300ml/min</option>
                                <option value="400ml/min">400ml/min</option>
                                <option value="500ml/min">500ml/min</option>
                                <option value="600ml/min">600ml/min</option>
                                <option value="700ml/min">700ml/min</option>
                                <option value="800ml/min">800ml/min</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td width="25%"><strong>Anticoagulation: </strong></td>
                        <td width="25%"><input type="text" name="Anticoagulation" maxlength="30" class="form-control"></td>

                        <td width="25%"><strong>Blood Flow Rate :</strong></td>
                        <td width="25%">
                            <select name="blood_flow_rate" id="blood_flow_rate" class="form-control" style="font-size: 14px;">
                                <option value="" selected>-- select--</option>
                                <option value="100ml/min">100ml/min</option>
                                <option value="200ml/min">200ml/min</option>
                                <option value="300ml/min">300ml/min</option>
                                <option value="400ml/min">400ml/min</option>
                                <option value="500ml/min">500ml/min</option>
                                <option value="600ml/min">600ml/min</option>
                                <option value="700ml/min">700ml/min</option>
                                <option value="800ml/min">800ml/min</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">

                            <div class="form_sep"></div>
                            <label class="">Other Note </label>
                            <textarea name="request_note" id="request_note" class="form-control" cols="30" rows="5"></textarea>

                        </td>
                    </tr>

                </table>




            </div>


            <?php
            $hospital_no = $_GET['patient'];
            $patient_info = $Patient->getByHospitalNo($hospital_no);
            if (!empty($patient_info)) {
                $add_minus = $patient_info->add_minus;
            }
            ?>
            <input type="hidden" name="add_minus" value="<?php echo $add_minus; ?>">
            <?php if ($_SESSION['rights'] == 'DR' or $_SESSION['rights'] == 'AD' or $_SESSION['rights'] == 'MD') { ?>
                <br>

                <p class="text-center">
                    <button class="btn btn-primary btn btn-sm" type="submit" name="dialysisFirstPhaseBtn">Proceed</button>

                </p>
            <?php } else { ?>
                <h3 style="color: red;">ONLY DOCTORS ARE ALLOWED TO BOOK!</h3>
            <?php } ?>
        </div>
    </div>
</form>