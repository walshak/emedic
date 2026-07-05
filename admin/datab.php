<?php



if (isset($_POST['apply_button3'])) {

    echo '------------------------------------------------------------------------------------------';
?>

    <div class="row">

        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Bed Manager</h5>
                </div>

                <div class="ibox-content">

                    <?php


                    $appt_rpt = $_POST['appt_rpt'];



                    if ($appt_rpt == 'doctor_contact') {


                        $sql = "SELECT apptm.*, notes.*
                        FROM apptm
                        LEFT JOIN notes ON apptm.id = notes.appt_no"; // Adjust based on your schema

                        // Step 3: Prepare and execute the query
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {


                    ?>


                            <table id="datatable-buttons" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="3%">#</th>
                                        <th width="5%">Hospital #</th>
                                        <th width="5%">Name</th>
                                        <th width="3%">Doctor</th>
                                        <th width="7%">Service Charge</th>
                                        <th width="7%">Date/Time</th>
                                        <th width="7%">Booked By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php echo $n; ?></td>
                                            <td><?php echo $rwx['hospital_no']; ?></td>
                                            <td><?php echo $rwx['patient_name']; ?></td>
                                            <td><?php echo $rwx['app_by']; ?></td>
                                            <td><?php echo $rwx['services_name']; ?></td>
                                            <td><?php echo date("d,M y H:i:s a", strtotime($rwx['ap_date_time'])); ?></td>
                                            <td><?php echo $rwx['checkin_by']; ?></td>
                                        </tr>
                                    <?php $n++;
                                    } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                        <?php

                    } else {



                        if ($appt_rpt == 'Appointment') {
                            $stmt = $db->query("SELECT * FROM apptm where date_ap between '$start2' and '$end2' and $search  ORDER BY sn");

                            /// echo  $search = "status='discharge' and queue_lock='1'";
                        } else {
                            $search = "(status='$appt_rpt' or discharge_remarks='$appt_rpt')";
                        }
                        $start2 = $_POST['start2'];
                        $end2 = $_POST['end2'];

                        $stmt = $db->query("SELECT * FROM apptm where date_ap between '$start2' and '$end2' and $search  ORDER BY sn");
                        if ($stmt->rowCount() > 0) {
                        ?>

                            <table id="datatable-buttons" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="3%">#</th>
                                        <th width="5%">Hospital #</th>
                                        <th width="5%">Name</th>
                                        <th width="3%">Doctor</th>
                                        <th width="7%">Service Charge</th>
                                        <th width="7%">Date/Time</th>
                                        <th width="7%">Booked By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php echo $n; ?></td>
                                            <td><?php echo $rwx['hospital_no']; ?></td>
                                            <td><?php echo $rwx['patient_name']; ?></td>
                                            <td><?php echo $rwx['app_by']; ?></td>
                                            <td><?php echo $rwx['services_name']; ?></td>
                                            <td><?php echo date("d,M y H:i:s a", strtotime($rwx['ap_date_time'])); ?></td>
                                            <td><?php echo $rwx['checkin_by']; ?></td>
                                        </tr>
                                    <?php $n++;
                                    } ?>
                                </tbody>
                            </table>

                        <?php } else { ?>

                            <div class="alert alert-danger">No Records Found ...</div>
                        <?php } ?>



                    <?php   } ?>


                </div>

            </div>
        </div>

    </div>

<?php } else {


?>

    <?php if (isset($_GET['ERROR'])) { ?>
        <div class="alert alert-danger">Select options before you continue ... </div>
    <?php } ?>


    <div class="row">

        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Patient Data</h5>
                </div>

                <div class="ibox-content">

                    <div class="row">




                        <div class="form_sep">
                            <form action="../download_data.php" method="post">
                                <div class="form_sep">
                                    <button type="submit" class="btn btn-primary btn btn-sm" name="download_patient_data" id="Save_patient">Download Entire Patient Data</button>
                                </div>
                            </form>



                            <!-- <div class="checkbox i-checks"><label> <input name="AllPatientsData" type="checkbox" value="all"> <i></i>&nbsp;All Patients Data </label></div>-->
                        </div>

                        <hr>

                        <form action="../dbase/index.php" method="POST">
                            <div class="form_sep">
                            </div>

                            <div class="col-lg-5 b-r">


                                <div class="form_sep">
                                    <label for="reg_select" class="">Gender</label>
                                    <select name="genderr" id="genderr" class="form-control">
                                        <option selected="selected" value="">Select...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>

                                <div class="form_sep">
                                    <label for="reg_select" class="">State/LGA</label>
                                    <select name="state_lga" id="state_lga" class="form-control">
                                        <option selected="selected" value="">Select...</option>
                                        <?php include("state_lga.php"); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-7">
                                <div class="form_sep">
                                    <label for="reg_select" class="">Age Range</label>
                                    <select name="age_range" id="age_range" class="form-control" style="font-size:14px">
                                        <option selected="selected" value="">Select...</option>

                                        <option value="C">Children (00-14 years)</option>
                                        <option value="Y">Youth (15-24 years)</option>
                                        <option value="A">Adults (25-64 years)</option>
                                        <option value="S">Seniors (65 years and over)</option>

                                    </select>
                                </div>


                                <div class="form_sep">
                                    <label for="reg_input_no" class="">Enrollee/Registration Date</label><br>
                                    <div class="form_sep" id="">
                                        <div class="input-daterange input-group" id="datepicker">
                                            <input type="date" class="input-sm form-control" name="enroll_start" />
                                            <span class="input-group-addon">to</span>
                                            <input type="date" class="input-sm form-control" name="enroll_end" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <hr>
                    <div class="form_sep">
                        <button class="btn btn-success btn-sm" type="submit" name="apply_button" id="apply_button">Apply</button>
                    </div>

                    </form>


                    <!--break
-->

                    <hr>


                    <h2>ADVANCED STATISTICS <sup style="color:red;">New</sup></h2>
                    <a href="../dbase/patient_dbase/index.php" style="font-size: larger;">Check Here</a>
                </div>

            </div>
        </div>




        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Medical Reports </h5>
                </div>

                <div class="ibox-content">

                    <form action="../dbase/index.php" method="POST">

                        <strong>1./ Choose an option below</strong><br>

                        <div class="form_sep">
                            <div class="radio i-checks"><label> <input type="radio" value="C" name="med_rpt" required> <i></i> Complaints </label></div>
                            <div class="radio i-checks"><label> <input type="radio" value="D" name="med_rpt" required> <i></i> Diagnosis </label></div>
                        </div>

                        <div class="form_sep">
                            <label for="reg_input_no" class="">2./ Exact Search</label>
                            <select name="diagnosis" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Search and Select </option>
                                <?php
                                $stmt = $db->query("SELECT * FROM diagnosis ORDER BY item ASC");
                                echo $stmt->rowCount();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["item"]; ?>"><?php echo $row["item"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form_sep">
                            <label for="reg_input_name" class="">3./ Or Type any keyword</label>
                            <input type="text" id="keyword" name="keyword" class="form-control">
                        </div>

                        <label for="reg_input_name" class="" style="color: brown; ">Check this box to Show Notes
                            <input type="checkbox" name="show_notes" value="show_notes" class="form-control">
                        </label>



                        <div class="form_sep">
                            <label for="reg_input_no" class="">Dates</label><br>
                            <div class="form_sep" id="">
                                <div class="input-daterange input-group" id="">
                                    <input type="date" class="input-sm form-control" name="start3" required />
                                    <span class="input-group-addon">to</span>
                                    <input type="date" class="input-sm form-control" name="end3" required />
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="apply_button2" id="apply_button2">Apply</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>


    <div class="row">

        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Pharmacy and Other Service Category <U>(COUNTER/SUMMARY)</U></h5>
                </div>

                <div class="ibox-content">
                    <form action="../dbase/index.php" method="POST">
                        <div class="form_sep">
                            &nbsp;&nbsp;&nbsp;Check box below to view entire drugs.
                            <div class="checkbox i-checks"><label> <input name="AllDrugData" type="checkbox" value="all"> <i></i>&nbsp;All Drugs Data </label></div>
                        </div>
                        <div class="form_sep">
                        </div>

                        <div class="form_sep">
                            <label for="reg_input_no" class="">Search and choose drug name</label>
                            <select name="product_name_drug" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Search and Select </option>
                                <?php
                                $cur_date = date("Y-m-d");
                                $stmt = $db->query("SELECT * FROM stock_table where stock_table='Pharmacy' ORDER BY product_name ASC");
                                echo $stmt->rowCount();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["product_name"]; ?>"><?php echo $row["product_name"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_input_no" class="">Dates</label><br>
                            <div class="form_sep" id="">
                                <div class="input-daterange input-group" id="">
                                    <input type="date" class="input-sm form-control" name="start4" required />
                                    <span class="input-group-addon">to</span>
                                    <input type="date" class="input-sm form-control" name="end4" required />
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="apply_button4" id="apply_button4">Apply</button>
                        </div>
                    </form>
                </div>

                <br>
                <br>
                <br>
                <hr>

                <div class="ibox-content">
                    <form action="../dbase/index.php" method="POST">

                        <div class="form_sep">
                            <label for="reg_input_no" class="">SERVICE GROUPING</label>
                            <select name="serv_group" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Search and Select </option>
                                <option value="all">All SERVICE GROUP</option>
                                <?php
                                $cur_date = date("Y-m-d");
                                $stmt = $db->query("SELECT distinct serv_group FROM patient_ap_services ORDER BY serv_group ASC");
                                echo $stmt->rowCount();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["serv_group"]; ?>"><?php echo $row["serv_group"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form_sep">
                            <label for="reg_input_no" class="">SERVICE SUB-GROUPING (optional)</label>
                            <select name="cat_type" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Search and Select </option>
                                <?php
                                $cur_date = date("Y-m-d");
                                $stmt = $db->query("SELECT distinct cat_type FROM patient_ap_services ORDER BY cat_type ASC");
                                echo $stmt->rowCount();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["cat_type"]; ?>"><?php echo $row["cat_type"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_input_no" class="">Department (Optional)</label>
                            <select name="department" class="form-control">
                                <option selected="selected" value="">Select </option>
                                <option value="all">All</option>
                                <?php
                                $stmt = $db->query("SELECT * FROM department ORDER BY department ASC");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form_sep">
                            <label for="reg_input_no" class="">Dates</label><br>
                            <div class="form_sep" id="">
                                <div class="input-daterange input-group" id="">
                                    <input type="date" class="input-sm form-control" name="start4" required />
                                    <span class="input-group-addon">to</span>
                                    <input type="date" class="input-sm form-control" name="end4" required />
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="general_report_service" id="">Apply</button>
                        </div>
                    </form>


                    <?php
                    // Retrieve distinct values for each dropdown
                    $serv_groups = getDistinctValues($db, 'serv_group');
                    $cat_types = getDistinctValues($db, 'cat_type');
                    $prepared_bys = getDistinctValues($db, 'prepared_by');
                    $invoice_bys = getDistinctValues($db, 'invoice_by');
                    $dept_ids = getDistinctValues($db, 'dept_id');
                    $hospital_nos = getDistinctValues($db, 'hospital_no');
                    $item_services = getDistinctValues($db, 'item_services');
                    $created_bys = getDistinctValues($db, 'created_by');
                    $dsp_bys = getDistinctValues($db, 'dsp_by');
                    $pay_modes = getDistinctValues($db, 'pay_mode');


                    ?>

                    <hr>
                    <form method="POST" action="download_code_for_ap_services.php">
                        <label for="serv_group">Service Group:</label>
                        <select id="serv_group" name="serv_group" class="form-control">
                            <option value="">Select Service Group</option>
                            <?php foreach ($serv_groups as $serv_group) { ?>
                                <option value="<?php echo $serv_group; ?>"><?php echo $serv_group; ?></option>
                            <?php } ?>
                        </select>

                        <label for="cat_type">Category Type:</label>
                        <select id="cat_type" name="cat_type" class="form-control">
                            <option value="">Select Category Type</option>
                            <?php foreach ($cat_types as $cat_type) { ?>
                                <option value="<?php echo $cat_type; ?>"><?php echo $cat_type; ?></option>
                            <?php } ?>
                        </select>

                        <label for="prepared_by">Prepared By:</label>
                        <select id="prepared_by" name="prepared_by" class="form-control">
                            <option value="">Select Prepared By</option>
                            <?php foreach ($prepared_bys as $prepared_by) { ?>
                                <option value="<?php echo $prepared_by; ?>"><?php echo $prepared_by; ?></option>
                            <?php } ?>
                        </select>

                        <label for="invoice_by">Invoice By:</label>
                        <select id="invoice_by" name="invoice_by" class="form-control">
                            <option value="">Select Invoice By</option>
                            <?php foreach ($invoice_bys as $invoice_by) { ?>
                                <option value="<?php echo $invoice_by; ?>"><?php echo $invoice_by; ?></option>
                            <?php } ?>
                        </select>

                        <label for="dept_id">Department ID:</label>
                        <select id="dept_id" name="dept_id" class="form-control">
                            <option value="">Select Department ID</option>
                            <?php foreach ($dept_ids as $dept_id) { ?>
                                <option value="<?php echo $dept_id; ?>"><?php echo $dept_id; ?></option>
                            <?php } ?>
                        </select>

                        <label for="hospital_no">Hospital No:</label>
                        <select id="hospital_no" name="hospital_no" class="form-control">
                            <option value="">Select Hospital No</option>
                            <?php foreach ($hospital_nos as $hospital_no) { ?>
                                <option value="<?php echo $hospital_no; ?>"><?php echo $hospital_no; ?></option>
                            <?php } ?>
                        </select>

                        <label for="item_services">Item Services:</label>
                        <select id="item_services" name="item_services" class="form-control">
                            <option value="">Select Item Services</option>
                            <?php foreach ($item_services as $item_service) { ?>
                                <option value="<?php echo $item_service; ?>"><?php echo $item_service; ?></option>
                            <?php } ?>
                        </select>

                        <label for="created_by">Created By:</label>
                        <select id="created_by" name="created_by" class="form-control">
                            <option value="">Select Created By</option>
                            <?php foreach ($created_bys as $created_by) { ?>
                                <option value="<?php echo $created_by; ?>"><?php echo $created_by; ?></option>
                            <?php } ?>
                        </select>

                        <label for="dsp_by">DSP By:</label>
                        <select id="dsp_by" name="dsp_by" class="form-control">
                            <option value="">Select DSP By</option>
                            <?php foreach ($dsp_bys as $dsp_by) { ?>
                                <option value="<?php echo $dsp_by; ?>"><?php echo $dsp_by; ?></option>
                            <?php } ?>
                        </select>

                        <label for="pay_mode">Pay Mode:</label>
                        <select id="pay_mode" name="pay_mode" class="form-control">
                            <option value="">Select Pay Mode</option>
                            <?php foreach ($pay_modes as $pay_mode) { ?>
                                <option value="<?php echo $pay_mode; ?>"><?php echo $pay_mode; ?></option>
                            <?php } ?>
                        </select>

                        <button class="btn btn-success btn-sm" type="submit" name="download" id="download-btn">Download CSV</button>
                    </form>

                </div>
            </div>


            <!-- Appointment Statistics -->
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Appointment Statistics </h5>
                </div>
                <div class="ibox-content">
                    <form action="../dbase/index.php" method="POST">

                        <!-- Date Range -->
                        <div class="form_sep">
                            <label for="report_dates" class="">Dates</label><br>
                            <div class="input-daterange input-group">
                                <input type="date" class="input-sm form-control" name="start_date" value="<?php echo date("Y-m-d"); ?>" required />
                                <span class="input-group-addon">to</span>
                                <input type="date" class="input-sm form-control" name="end_date" value="<?php echo date("Y-m-d"); ?>" required />
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="generate_report_appt_stats">Generate Report</button>
                        </div>

                    </form>
                </div>
            </div>
            <br>
            <!-- /Appointment Statistics -->

            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Family Folder Services Report </h5>
                </div>

                <?php
                // Fetch distinct family insurances
                $insuranceOptions = $db->query("SELECT insurance_no, insurance_name FROM insurance_tbl WHERE insurance_type = 'Family'")->fetchAll(PDO::FETCH_ASSOC);
                // print_r($insuranceOptions);
                // die();
                ?>

                <div class="ibox-content">

                    <form action="../dbase/index.php" method="POST">
                        <div class="form_sep">
                            <label for="appt_rpt_fam" class="">Select Report Type</label>
                            <select name="appt_rpt_fam" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Select Report ... </option>
                                <option value="Paid">Paid Credits</option>
                                <option value="UnPaid">UnPaid Credits</option>
                                <option value="Invoice">Invoice only</option>
                                <option value="All">All Credits</option>
                            </select>
                        </div>
                        <div class="form_sep">
                            <label for="insurance_fam" class="">Select Family Insurance (Optional)</label>
                            <select name="insurance_fam" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Select Family Insurance...</option>
                                <?php foreach ($insuranceOptions as $insurance) { ?>
                                    <option value="<?php echo $insurance['insurance_no']; ?>"><?php echo $insurance['insurance_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form_sep">
                            <label for="rpt_scope_fam" class="">Select Report Scope</label>
                            <select name="rpt_scope_fam" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Select Report Scope... </option>
                                <option value="Detailed">Detailed</option>
                                <option value="Summary">Summary</option>
                            </select>
                        </div>
                        <div class="form_sep">
                            <label for="reg_input_no" class="">Dates</label><br>
                            <div class="form_sep" id="">
                                <div class="input-daterange input-group" id="">
                                    <input type="date" class="input-sm form-control" name="start_fam" value="<?php echo date("Y-m-d"); ?>" required />
                                    <span class="input-group-addon">to</span>
                                    <input type="date" class="input-sm form-control" name="end_fam" value="<?php echo date("Y-m-d"); ?>" required />
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="apply_button_fam" id="">Get Data</button>
                        </div>

                    </form>


                </div>

            </div>
        </div>

        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>MEDICAL REPORTS VERSION 2 STATISTICS</h5>
                </div>

                <div class="ibox-content">

                    <form action="../dbase/index.php" method="POST">

                        <div class="form_sep">
                            <label for="reg_input_no" class="">Department</label>
                            <select name="department" class="form-control" required>
                                <option selected="selected" value="">Select </option>
                                <option value="all">All</option>
                                <?php
                                $stmt = $db->query("SELECT * FROM department where department_type='medical services' ORDER BY department ASC");
                                echo $stmt->rowCount();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["sn"]; ?>"><?php echo $row["department"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_input_no" class="">SELECT IN/OUT/DEATH PATIENT</label>
                            <select name="patient_way" class="form-control" required>
                                <option selected="selected" value="">Select </option>
                                <option value="IN-PATIENT">IN-PATIENT</option>
                                <option value="OUT-PATIENT">OUT-PATIENT</option>
                                <option value="DEATH">DEATH</option>

                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_input_no" class="">IN-PATIENT (Accommodation) </label>
                            <select name="room_bed" class="form-control">
                                <option selected="selected" value="">Select </option>
                                <?php
                                $stmt = $db->query("SELECT distinct room_bed FROM admission ORDER BY room_bed ASC");
                                echo $stmt->rowCount();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row["room_bed"]; ?>"><?php echo $row["room_bed"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_input_no" class="">Dates</label><br>
                            <div class="form_sep" id="">
                                <div class="input-daterange input-group" id="">
                                    <input type="date" class="input-sm form-control" name="start_new" required />
                                    <span class="input-group-addon">to</span>
                                    <input type="date" class="input-sm form-control" name="end_new" required />
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="version_2" id="version_2">Apply</button>
                        </div>


                    </form>

                </div>

            </div>
        </div>




        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Appointment Reports </h5>
                </div>

                <div class="ibox-content">

                    <form action="../dbase/index.php" method="POST">

                        <div class="form_sep">
                            <label for="reg_input_no" class="">Select Report</label>
                            <select name="appt_rpt" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Select Report ... </option>
                                <option value="Admitted and Discharge">Admitted and Discharge</option>
                                <option value="Appointment">Booking</option>
                                <option value="doctor_contact">Doctors Contact</option>
                                <option value="System Discharge">System Discharged</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="Death">Death</option>
                                <option value="Referral">Referral</option>
                            </select>
                        </div>

                        <div class="form_sep">
                            <label for="reg_input_no" class="">Dates</label><br>
                            <div class="form_sep" id="">
                                <div class="input-daterange input-group" id="">
                                    <input type="date" class="input-sm form-control" name="start2" value="<?php echo date("Y-m-d"); ?>" required />
                                    <span class="input-group-addon">to</span>
                                    <input type="date" class="input-sm form-control" name="end2" value="<?php echo date("Y-m-d"); ?>" required />
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="apply_button3" id="">Apply Data</button>
                        </div>

                    </form>


                </div>

            </div>

            <!-- disease notification -->
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Monthly Disease Notification Report </h5>
                </div>
                <div class="ibox-content">
                    <form action="./disease_notification_report.php" method="POST">
                        <div class="form_sep">
                            <label for="reg_input_no" class="">Month</label><br>
                            <div class="form_sep" id="">
                                <input type="month" class="input-sm form-control" name="report_month" value="<?php echo date("Y-m"); ?>" required />
                            </div>
                        </div>
                        <div class="form_sep">
                            <label for="reg_input_no" class="">Select Report</label>
                            <select name="report_type" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Select Report ... </option>
                                <option value="Out-patient">Out-patient</option>
                                <option value="In-patient">In-patient</option>
                                <option value="Death">Death</option>

                            </select>
                        </div>
                        <hr>
                        <div class="form_sep">
                            <button class="btn btn-success btn-sm" type="submit" name="gen_disease_notification" id="">Apply</button>
                        </div>
                </div>
                </form>
            </div>
            <!-- Disease notification -->

            <?php if ($_SESSION['rights'] == 'MD') { ?>


                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>VIEW LOGS</h5>
                    </div>

                    <div class="ibox-content">

                        <form action="../dbase/index.php" method="POST">

                            <div class="form_sep">
                                <label for="reg_input_no" class="">staff_name</label>
                                <select name="staff_name" class="form-control">
                                    <option selected="selected" value="">Select </option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct staff_name FROM patient_staff_logs ORDER BY staff_name ASC");
                                    echo $stmt->rowCount();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["staff_name"]; ?>"><?php echo $row["staff_name"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">patient_id</label>
                                <select name="patient_id" class="form-control">
                                    <option selected="selected" value="">Select </option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct patient_id FROM patient_staff_logs ORDER BY patient_id ASC");
                                    echo $stmt->rowCount();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["patient_id"]; ?>"><?php echo $row["patient_id"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>


                            <div class="form_sep">
                                <label for="reg_input_no" class="">Dates</label><br>
                                <div class="form_sep" id="">
                                    <div class="input-daterange input-group" id="">
                                        <input type="date" class="input-sm form-control" name="start_new" required />
                                        <span class="input-group-addon">to</span>
                                        <input type="date" class="input-sm form-control" name="end_new" required />
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="form_sep">
                                <button class="btn btn-success btn-sm" type="submit" name="viwe_log" id="viwe_log">Apply</button>
                            </div>


                        </form>

                    </div>

                </div>


                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>VIEW PAIENT DELETED NOTES</h5>
                    </div>

                    <div class="ibox-content">

                        <form action="../dbase/index.php" method="POST">

                            <div class="form_sep">
                                <label for="reg_input_no" class="">staff_name</label>
                                <select name="staff_name" class="form-control">
                                    <option selected="selected" value="">Select </option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct prepared_by FROM notes where status=0 ORDER BY prepared_by ASC");
                                    echo $stmt->rowCount();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["prepared_by"]; ?>"><?php echo $row["prepared_by"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">hospital_no</label>
                                <select name="patient_id" class="form-control">
                                    <option selected="selected" value="">Select </option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct hospital_no FROM notes where status=0 ORDER BY hospital_no ASC");
                                    echo $stmt->rowCount();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["hospital_no"]; ?>"><?php echo $row["hospital_no"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>


                            <div class="form_sep">
                                <label for="reg_input_no" class="">Dates</label><br>
                                <div class="form_sep" id="">
                                    <div class="input-daterange input-group" id="">
                                        <input type="date" class="input-sm form-control" name="start_new" required />
                                        <span class="input-group-addon">to</span>
                                        <input type="date" class="input-sm form-control" name="end_new" required />
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="form_sep">
                                <button class="btn btn-success btn-sm" type="submit" name="PATIENT_DELETE_NOTES" id="viwe_log">Apply</button>
                            </div>


                        </form>

                    </div>

                </div>





                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>VIEW PAIENT DELETED INVESTIGATION</h5>
                    </div>

                    <div class="ibox-content">

                        <form action="../dbase/index.php" method="POST">

                            <div class="form_sep">
                                <label for="reg_input_no" class="">staff_name</label>
                                <select name="staff_name" class="form-control">
                                    <option selected="selected" value="">Select </option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct entered_by FROM lab_result_old ORDER BY entered_by ASC");
                                    echo $stmt->rowCount();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["entered_by"]; ?>"><?php echo $row["entered_by"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">Dates</label><br>
                                <div class="form_sep" id="">
                                    <div class="input-daterange input-group" id="">
                                        <input type="date" class="input-sm form-control" name="start_new" required />
                                        <span class="input-group-addon">to</span>
                                        <input type="date" class="input-sm form-control" name="end_new" required />
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="form_sep">
                                <button class="btn btn-success btn-sm" type="submit" name="PATIENT_DELETE_INVES" id="viwe_log">Apply</button>
                            </div>


                        </form>

                    </div>

                </div>

            <?php } ?>








        </div>


    </div>

    </div>


<?php } ?>

<?php

// Function to get distinct values for a specific column
function getDistinctValues($db, $column)
{
    $sql = "SELECT DISTINCT `$column` FROM `patient_ap_services` WHERE `$column` IS NOT NULL";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    // Fetch all distinct values
    $values = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $values;
}
