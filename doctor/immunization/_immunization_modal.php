<!-- Modal -->
<div class="modal  fade" id="immunization-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 50%; margin: 10px auto">

        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-body" style="min-height: 400px">
                <?php
                $immunization_ =  $Immunization->get(['hospital_no' => $hospital_no]);
                ?>

                <div id="immunization_enrolment_wrap" style="display: <?= empty($immunization_) ? 'block' : 'none'; ?>">
                    <section>
                        <div>
                            <h4 class="text-center"> <u>CHILD INFORMATION</u> </h4>
                            <br>
                            <table class="table table-active table-hover" width="100%">
                                <tr>
                                    <td> <strong>Card No./Hospital No.</strong></td>
                                    <td><?= $hosp_no; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Child's Name</strong></td>
                                    <td><?= $patient_name; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Date of Birth / Age</strong></td>
                                    <td><?php echo $dob . ' / ' . $age_full; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Mother's Name</strong></td>
                                    <td><input type="text" name="mother_name" id="mother_name" class="form-control" placeholder="Mother's Name"></td>
                                </tr>
                                <tr>
                                    <td><strong>Father's Name</strong></td>
                                    <td><input type="text" name="father_name" id="father_name" class="form-control" placeholder="Father's Name"></td>
                                </tr>
                            </table>
                        </div>
                    </section>
                    <section>
                        <h3 class="text-center"> <u>PAEDIATRIC INITIAL HISTORY</u> </h3>
                        <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-4"><strong>
                                            <h4>Gestation at Birth</h4>
                                        </strong></div>
                                    <div class="col-xs-8">
                                        <select name="gestation_at_birth" id="gestation_at_birth" class="form-control">
                                            <option selected="selected" value="">Select...</option>
                                            <option value="Term">Term</option>
                                            <option value="Preterm">Preterm</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-4"><strong>
                                            <h4>Neonatal Complication</h4>
                                        </strong></div>
                                    <div class="col-xs-8">
                                        <select name="neonatal_complication" id="neonatal_complication" class="form-control">
                                            <option selected="selected" value="">Select...</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-4"><strong>
                                            <h4>Delivery Mode</h4>
                                        </strong></div>
                                    <div class="col-xs-8">
                                        <select name="delivery_mode" id="delivery_mode" class="form-control">
                                            <option selected="selected" value="">Select...</option>
                                            <option value="SVD">SVD</option>
                                            <option value="Emergency C-section">Emergency C-section</option>
                                            <option value="Elective C-section">Elective C-section</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <br>
                        <div class="text-center"> <button class="btn btn-primary" onclick="enroll_child_for_immunization()">Enroll Now</button></div>
                    </section>
                </div>


                <div id="vaccination_wrap" style="display: <?= !empty($immunization_) ? 'block' : 'none'; ?>">


                    <section class=" panel card">
                        <h1 class="text-center bg-primary p-10">Child Health e-Card</h1>
                        <div style="height:150px">
                            <p class="text-center"><img src="breastfeeding_trans.png" alt="image" style="width:30%"></p>
                        </div>

                        <br>
                        <br>
                        <div class="panel" style="padding: 15px;">
                            <h4 class="text-center">CHILD INFORMATION (Write in CAPITAL letters) </h4>
                            <br>

                            <table width="100%" border>
                                <tr>
                                    <td> <strong>Card No./Hospital No.</strong></td>
                                    <td><?= ($hosp_no); ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Child's Name</strong></td>
                                    <td><?= $patient_name; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Date of Birth / Age</strong></td>
                                    <td><?php echo ($dob . ' / ' . $age_full); ?></td>
                                </tr>

                                <?php
                                if (!empty($immunization_)) {
                                ?>
                                    <tr>
                                        <td>Mother's Name</td>
                                        <td><?= ($immunization_->mother_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Father's Name</td>
                                        <td><?= ($immunization_->father_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Care giver's name</td>
                                        <td><?= ($_SESSION["fullname"]); ?></td>
                                    </tr>
                                <?php
                                }
                                ?>


                            </table>
                        </div>



                        <div class="panel" style="padding:0px 15px">
                            <h4>VACCINATION </h4>
                            <div>
                                <table width="100%">
                                    <tr>
                                        <td><strong><u>ANTIGEN</u></strong></td>
                                        <td><strong><u>DATE GIVEN </u></strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>

                                            <select name="antigen" id="antigen" class="form-control">
                                                <option value=""> Select vaccine given </option>
                                                <?php
                                                $distinct_appt = $Immunization->raw(" DISTINCT appointment_number ", ['hospital_no' => $hospital_no], true);

                                                $appt_count = count($distinct_appt);
                                                $vaccines = $Vaccine->all();

                                                foreach ($vaccines as $key => $vaccine) {
                                                    if ($vaccine->immunization_stage <= ($appt_count + 1)) {
                                                        if ($Immunization->is_vaccine_given(['hospital_no' => $hospital_no, 'antigen' => $vaccine->id]) == false) {
                                                            echo '<option value="' . $vaccine->id . '" id="vaccine_' . $vaccine->id . '">' . $vaccine->name . '</option>';
                                                        }
                                                    }
                                                }

                                                ?>
                                                <option value=""></option>
                                            </select>
                                        </td>
                                        <td><input type="date" name="date_given" id="date_given" class="form-control" placeholder="" value="<?= date('Y-m-d'); ?>"></td>
                                        <td><button class="btn btn-info" onclick="addVacinceGiven()">Add</button></td>
                                    </tr>

                                </table>
                            </div>

                            <br>
                            <br>

                            <div>
                                <?php
                                $vaccines_given = $Immunization->immunization_vaccine_given($hospital_no);

                                ?>
                                   <table id="vaccines_given_tbl" width="100%" border='2'>
                                    <tr>
                                        <td><strong><u>ANTIGEN</u></strong></td>
                                        <td><strong><u>DATE GIVEN (MM-DD-YYYY)</u></strong></td>
                                    </tr>
                                    <?php

                                    foreach ($vaccines_given as $key => $vaccine) {
                                    ?>
                                        <tr>
                                            <td><?= $vaccine->name; ?></td>
                                            <td><?= $vaccine->date_given; ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>


                            </div>

                        </div>






                    </section>


                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" 
                    onclick="ClickheretoprintDiv('printable-immunization-div')" 
                    style="display: <?= !empty($immunization_) ? 'block' : 'none'; ?>">
                    <i class="fa fa-print"></i> Print </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal  fade" id="immunization-print-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 50%; margin: 10px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" style="min-height: 400px">
                <section class=" panel card" id="printable-immunization-div">
                <p class="text-center"> <img src="../img/logo.png" alt=""></p>               
                    <h1 class="text-center bg-primary p-10">Child Health e-Card</h1>
                    <div style="height:150px">
                        <p class="text-center"><img src="breastfeeding_trans.png" alt="image" style="width:30%"></p>
                    </div>
                    <br>
                    <div class="panel" style="padding: 15px;">
                        <br>
                        <br>
                        <h4 class="text-center">CHILD INFORMATION (Write in CAPITAL letters) </h4>
                        <br>
                        <table width="100%" border='2'>
                            <tr>
                                <td> <strong>Card No./Hospital No.</strong></td>
                                <td><?= ($hosp_no); ?></td>
                            </tr>
                            <tr>
                                <td> <strong>Child's Name</strong></td>
                                <td><?= $patient_name; ?></td>
                            </tr>
                            <tr>
                                <td> <strong>Date of Birth / Age</strong></td>
                                <td><?php echo ($dob . ' / ' . $age_full); ?></td>
                            </tr>

                            <?php
                            if (!empty($immunization_)) {
                            ?>
                                <tr>
                                    <td>Mother's Name</td>
                                    <td><?= ($immunization_->mother_name); ?></td>
                                </tr>
                                <tr>
                                    <td>Father's Name</td>
                                    <td><?= ($immunization_->father_name); ?></td>
                                </tr>
                                <tr>
                                    <td>Care giver's name</td>
                                    <td><?= ($_SESSION["fullname"]); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>

                    <div class="panel card well well-sm" style="padding:15px">
                        <h4 class="text-center">VACCINATION </h4>
                        <div>
                            <?php
                            $vaccines_given = $Immunization->immunization_vaccine_given($hospital_no);
                            ?>
                            <table id="vaccines_given_tbl2" width="100%" border='2'>
                                <tr>
                                    <td><strong><u>ANTIGEN</u></strong></td>
                                    <td><strong><u>DATE GIVEN (MM-DD-YYYY)</u></strong></td>
                                </tr>
                                <?php
                                foreach ($vaccines_given as $key => $vaccine) {
                                ?>
                                    <tr>
                                        <td><?= $vaccine->name; ?></td>
                                        <td><?= $vaccine->date_given; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </table>
                        </div>

                    </div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="ClickheretoprintDiv('printable-immunization-div')">Save PDF </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                
            </div>
        </div>
    </div>
</div>

<script>
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;
    var vaccines = <?php echo json_encode($vaccines); ?>;


    function enroll_child_for_immunization() {
        var mother_name = $("#mother_name").val();
        var father_name = $("#father_name").val();
        var neonatal_complication = $("#neonatal_complication").val();
        var gestation_at_birth = $("#gestation_at_birth").val();
        var mode_of_delivery = $("#delivery_mode").val();

        vaccines
        if (
            mother_name == '' ||
            father_name == '' ||
            gestation_at_birth == '' ||
            neonatal_complication == '' ||
            mode_of_delivery == ''
        ) {
            return alert("Error: All the fields are required..")
        }

        let data = {
            hospital_no: hospital_no,
            appointment_number: appointment_number,
            mother_name: mother_name,
            father_name: father_name,
            neonatal_complication: neonatal_complication,
            gestation_at_birth: gestation_at_birth,
            mode_of_delivery: mode_of_delivery,
            action: 'enrol'
        };

        toastr.info('Saving,please wait..', 'Saving', {
            timeOut: 3000
        })
       
        $.ajax({
            url: 'controllers/_immunization.php',
            type: "POST",
            data: data,
            success: function(response) {
                console.log(response)
                if (response.status == 200) {
                    toastr.success('Enrolled successfully...', 'Success', {
                        timeOut: 3000
                    });
                    $("#immunization_enrolment_wrap").fadeOut('slow');
                    $("#vaccination_wrap").fadeIn('slow');
                    $("#immunization-print-btn").fadeIn('slow');

                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 3000
                    })
                }

                console.log(response)
            },
            error: function(response) {
                toastr.error(response.message, 'Error', {
                    timeOut: 3000
                })
                console.log(response)

            }
        });
    }


    function addVacinceGiven() {
        var antigen = $("#antigen").val();
        var date_given = $("#date_given").val();

        if (antigen == '') {
            alert('Please select antigen ')
        } else if (date_given == '') {
            alert('Please select date given ')
        } else {

            let data = {
                hospital_no: hospital_no,
                appointment_number: appointment_number,
                antigen: antigen,
                date_given: date_given,
                action: 'addVaccine'
            };

            $.ajax({
                url: 'controllers/_immunization.php',
                type: "POST",
                data: data,
                success: function(response) {
                    console.log(response)
                    if (response.status == 200) {
                        toastr.success('Vaccine Given saved successfully...', 'Success', {
                            timeOut: 3000
                        });
                        let vaccine = null;

                        vaccines.forEach(element => {
                            if (element.id == antigen) {
                                vaccine = element;
                            }
                        });
                        $("#vaccine_" + antigen).fadeOut("slow");
                        $("#antigen").val("");
                        $("#vaccines_given_tbl").fadeIn("slow");
                        $("#vaccines_given_tbl").append("<tr><td> " + vaccine.name + "</td><td>" + date_given + "</td> </tr>");
                        $("#vaccines_given_tbl2").append("<tr><td> " + vaccine.name + "</td><td>" + date_given + "</td> </tr>");

                    } else {
                        toastr.error(response.message, 'Error', {
                            timeOut: 3000
                        });
                    }
                    console.log(response)
                },
                error: function(response) {
                    toastr.error("Error", 'Error', {
                        timeOut: 3000
                    })
                    console.log(response)

                }
            });
        }
    }
</script>

