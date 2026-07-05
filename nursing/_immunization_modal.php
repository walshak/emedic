<style>
    .card-heading {
        color: #fff;
        background-color: #428bca;
        text-align: center;
        padding: 5px;
    }
</style>
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
                            <h4 class="text-center"> <u>PATIENT INFORMATION</u> </h4>
                            <br>
                            <table class="table table-active table-hover" width="100%">
                                <tr>
                                    <td> <strong>Card No./Hospital No.</strong></td>
                                    <td><?= $hosp_no; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Patient's Name</strong></td>
                                    <td><?= $patient_name; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Date of Birth / Age</strong></td>
                                    <td><?php echo (!empty($dob) ? (date('d M, Y', strtotime("" . $dob))) : "") . ' / ' . $age_full; ?></td>
                                </tr>
                            </table>
                        </div>
                    </section>
                    <section>
                        <h5 class="text-left"> <u>PAEDIATRIC INITIAL HISTORY (FILL THE FOLLWING FOR INFANT OR SKIP)</u> </h5>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-4"><strong>
                                        <h4>Mother's Name</h4>
                                    </strong></div>
                                <div class="col-xs-8">
                                    <input type="text" name="mother_name" id="mother_name" class="form-control" placeholder="Mother's Name">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-4"><strong>
                                        <h4>Mother's Name</h4>
                                    </strong></div>
                                <div class="col-xs-8">
                                    <input type="text" name="father_name" id="father_name" class="form-control" placeholder="Father's Name">
                                </div>
                            </div>
                        </div>



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
                        <div class="text-center"> <button class="btn btn-primary" onclick="enroll_child_for_immunization()">Continue / Skip</button></div>
                    </section>
                </div>

                <div id="vaccination_wrap" style="display: <?= !empty($immunization_) ? 'block' : 'none'; ?>">
                    <section class=" panel card">
                        <h1 class="text-center bg-primary">Patient e-Vaccination Card</h1>

                        <div class="" style="padding:0;">
                            <p class="text-center"><img src="../img/immunization-clinic.jpg" alt="image" style="width:90px"></p>
                            <h4 class="text-center"> INFORMATION</h4>
                            <table class="table-bordered" width="100%" border>
                                <tr>
                                    <td> <strong>Card No./Hospital No.</strong></td>
                                    <td><?= ($hosp_no); ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Patient's Name</strong></td>
                                    <td><?= $patient_name; ?></td>
                                </tr>
                                <tr>
                                    <td> <strong>Date of Birth / Age</strong></td>
                                    <td><?php echo ((!empty($dob) ? (date('d M, Y', strtotime("" . $dob))) : "") . ' / ' . $age_full); ?></td>
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

                            <div class="alert alert-danger">
                                To add a vaccine, go to the General Store/Pharmacy store and create a category named Vaccine or Immunization.<br>
                                Add all vaccinations under the category created, called Vaccine or Immunization.<br>
                                Enter the hospital/cash price if you want the patient to pay for the vaccination.
                            </div>


                            <div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Vaccine:</label>
                                            <select name="antigen" id="antigen" class="form-control">
                                                <option value=""> Select Vaccine </option>
                                                <?php
                                                $distinct_appt = $Immunization->raw(" DISTINCT appointment_number ", ['hospital_no' => $hospital_no], true);

                                                $appt_count = count($distinct_appt);

                                                $sql = "SELECT product_name, sn id,hosp_price,hosp_price,cash_price AS ext_price FROM stock_table WHERE status = 'active' AND (category ='immunization' or category ='vaccine') ";
                                                $stmt = $db->prepare($sql);
                                                $stmt->execute();
                                                if ($stmt->rowCount() > 0) {
                                                    $vaccines = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($vaccines as $key => $vaccine) {
                                                        $value = $vaccine['id'] . '||' . $vaccine['product_name'] . '||' . $vaccine['hosp_price'] . '||' . $vaccine['ext_price'];
                                                        echo '<option value="' . $value . '" id="vaccine_' . $vaccine['id'] . '">' . $vaccine['product_name'] . '</option>';
                                                    }
                                                } else { ?>
                                                    <option value="">No Immunization/Vaccine Available.</option>
                                                <?php  }
                                                ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Date Given</label>
                                            <input type="date" name="date_given" id="date_given" class="form-control" placeholder="" value="<?= date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Next Date</label>
                                            <input type="date" name="next_vaccination_date" id="next_vaccination_date" class="form-control" placeholder="" value="">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div>
                                        <label for="">Comment [Optional]:</label>
                                        <textarea name="comment" id="comment" rows="5" class="form-control"></textarea>
                                    </div>
                                    <br>

                                    <div class="text-right" style="padding-right:20px"><button class="btn btn-success mt-2" onclick="addVacinceGiven()">Give Vaccine</button></div>
                                </div>

                            </div>

                            <br>
                            <br>

                            <div>
                                <?php
                                // $vaccines_given = $Immunization->immunization_vaccine_given($hospital_no);
                                $sql = "SELECT * FROM  immunization_vaccines  WHERE hospital_no = '$hospital_no' ";
                                $stmt = $db->prepare($sql);
                                $stmt->execute();
                                $vaccines_given = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
                                ?>
                                <h4>History of Vaccines Taken:</h4>
                                <table id="vaccines_given_tbl" class="table" width="100%" border='2'>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Antigen</th>
                                            <th>Details</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table body will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="printbtn" onclick="ClickheretoprintDiv('printable-immunization-div')">
                    <i class="fa fa-print"></i> Print
                </button>
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
                    <p class="text-center"> <img src="../img/immunization-clinic.jpg" alt="" width="150px"></p>
                    <h1 class="text-center bg-primary p-10">Patient e-Vaccination Card</h1>

                    <div class="panel" style="padding: 15px;">
                        <p class="text-center"><img src="breastfeeding_trans.png" alt="image" style="width:90px"></p>

                        <h2 class="card-heading" style="  color: #fff;
        background-color: #428bca;
        text-align: center;
        padding: 5px;"> Patient Information</h2>
                        <br>
                        <table class="table" width="100%" border='2'>
                            <tr>
                                <td> <strong>Card No./Hospital No.</strong></td>
                                <td><?= ($hosp_no); ?></td>
                            </tr>
                            <tr>
                                <td> <strong>Patient's Name</strong></td>
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
                        <h2 class="card-heading" style="  color: #fff;
        background-color: #428bca;
        text-align: center;
        padding: 5px;"> Vaccinations Given</h2>
                        <div>
                            <table id="vaccines_given_tbl2" class="table" width="100%" border='2'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Antigen</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JavaScript -->
                                </tbody>
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
    $(document).ready(function() {
        // $("#printbtn").fadeOut('fast');
    });
    const vaccinesGiven = <?php echo json_encode($vaccines_given); ?>;

    // Function to populate the table
    function populateVaccineTable(vaccines) {
        const tableBody = document.querySelector('#vaccines_given_tbl tbody');
        const tableBody2 = document.querySelector('#vaccines_given_tbl2 tbody');
        tableBody.innerHTML = ''; // Clear existing rows
        tableBody2.innerHTML = ''; // Clear existing rows

        if (vaccines.length > 0) {
            $("#printbtn").fadeIn('slow');
        }

        vaccines.forEach((vaccine, index) => {
            const row = document.createElement('tr');
            const row2 = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${vaccine.vaccine_name}</td>
                <td>
                    <b>Given by:</b> ${vaccine.given_by} <br/>
                    <b>Given on:</b> ${new Date(vaccine.date_given).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })} <br/>
                    <b>To be given again on:</b> ${new Date(vaccine.next_vaccination_date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })} <br/>
                    <hr/>
                    <b>Comment:</b> ${vaccine.comment} <br/>
                </td>
                <td>
                <button class="btn btn-sm btn-danger" onclick="deleteVaccine(${vaccine.id},${vaccine.patient_ap_services_id}, ${vaccine.hospital_no} )"><i class="fa fa-trash"></i></button>
                </td>
            `;
            row2.innerHTML = `
                <td>${index + 1}</td>
                <td>${vaccine.vaccine_name}</td>
                <td>
                    <b>Given by:</b> ${vaccine.given_by} <br/>
                    <b>Given on:</b> ${new Date(vaccine.date_given).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })} <br/>
                    <b>To be given again on:</b> ${new Date(vaccine.next_vaccination_date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })} <br/>
                    <hr/>
                    <b>Comment:</b> ${vaccine.comment} <br/>
                </td>
            `;
            tableBody.appendChild(row);
            tableBody2.appendChild(row2);
        });
    }

    // Initial population of the table
    populateVaccineTable(vaccinesGiven);

    // Function to refresh the table with new data
    function refreshVaccineTable(hospital_no) {
        fetch('controllers/_immunization.php?populateVaccine&hospital_no=' + hospital_no) // Replace with your actual API endpoint to get the updated list
            .then(response => response.json())
            .then(data => {
                populateVaccineTable(data);
            })
            .catch(error => console.error('Error fetching vaccine data:', error));
    }

    function deleteVaccine(vaccineId, service_id, hospital_no) {
        if (confirm('Are you sure you want to delete this vaccine?')) {

            $.ajax({
                url: 'controllers/_immunization.php',
                type: "POST",
                data: {
                    delete_vaccine_id: vaccineId,
                    patient_ap_services_id: service_id,
                    delete_vaccine: 'delete_vaccine'
                },
                success: function(response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, 'Success', {
                            timeOut: 3000
                        })
                        refreshVaccineTable(hospital_no)
                    }
                },
                error: function(response) {
                    console.log(response)
                }
            });

        }
    }
</script>
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


        // if (
        //     mother_name == '' ||
        //     father_name == '' ||
        //     gestation_at_birth == '' ||
        //     neonatal_complication == '' ||
        //     mode_of_delivery == ''
        // ) {
        //     return alert("Error: All the fields are required..")
        // }



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
                    toastr.success('Saved...', 'Success', {
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
        var next_vaccination_date = $("#next_vaccination_date").val();
        var comment = $("#comment").val();

        if (antigen == '') {
            alert('Please select antigen ')
        } else if (date_given == '') {
            alert('Please select date given ')
        } else {


            let dateGivenArr = date_given.split('-');
            let dateGiven = dateGivenArr[2] + ' ' + dateGivenArr[1] + ' ' + dateGivenArr[0];

            let = nextDate = '';
            if (next_vaccination_date != '') {
                let dateGivenArr = next_vaccination_date.split('-');
                nextDate = dateGivenArr[2] + ' ' + dateGivenArr[1] + ' ' + dateGivenArr[0];
            }

            let data = {
                hospital_no: hospital_no,
                appointment_number: appointment_number,
                antigen: antigen,
                date_given: date_given,
                next_vaccination_date: next_vaccination_date,
                comment: comment,
                action: 'addVaccine'
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
                        toastr.clear()
                        toastr.success('Vaccine Given...', 'Success', {
                            timeOut: 3000
                        });
                        let vaccine = null;
                        refreshVaccineTable(hospital_no)
                        $("#comment").val("")
                        $("#antigen").val("")



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

    $(document).on('click', '#immunization-button', function() {

        $('#immunization-modal').modal('show');
    })
</script>
<script>
    function ClickheretoprintDiv(div_id) {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById(div_id).innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.write('<html><head><title>.::Webmedic </title> <link rel="stylesheet" href="../css/bootstrap.min.css">');
        docprint.document.write('</head><body onLoad="self.print()" style="width: 100%; height="auto" font-size:16px; font-family:arial;">');
        docprint.document.write(content_vlue);
        docprint.document.write('</body></html>');
        docprint.document.close();
        docprint.focus();
    }
</script>