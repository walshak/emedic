<?php include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
$error_status = null;
session_start();

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


$patientAppoint = null;
if (isset($_GET['hosp_no'])) {
    $hosp_no = $_GET['hosp_no'];
    $hospital_no = $hosp_no;
    $patientAppoint = $Appointment->get([
        'hospital_no' => $hosp_no,
        "status" => 'checkin',
        "date_ap" => "" . date('Y-m-d') . ""
    ]);
}




$setdate = date('Y-m-d');

if (isset($_REQUEST['addPostOptNoteBtn'])) {
	
    $data['id'] = $_POST['transplant_id'];
    $data['hla_machine'] = $_POST['hla_machine'];
    $data['dsa_findings'] = $_POST['dsa_findings'];
    $data['plasma_exchange'] = $_POST['plasma_exchange'];
    $data['surgical_notes'] = $_POST['surgical_notes'];
    $data['warm_ischemic_time'] = $_POST['warm_ischemic_time'];
    $data['cold_ischemic_time'] = $_POST['cold_ischemic_time'];

    $error_msg = 'Opps something went wrong...';
    $error_status = 1;
    $update = $Transplant->update($data, $data['id']);
    if ($update == true) {
        $error_status = 2;
        $error_msg = 'Saved successfully';
    }
}


if (isset($_REQUEST['addDonorBtn'])) {

    $patient_info = $Patient->get(['hospital_no' => $_POST['hospital_no']]);

    $gender = null;
    if (!empty($patient_info)) {
        $gender = $patient_info->gender;
    }


    $data['transplant_id'] = $_POST['transplant_id'];
    $data['hospital_no'] = $_POST['hospital_no'];
    $data['name'] = $_POST['name'];
    $data['phone_number'] = $_POST['phone_number'];
    $data['address'] = $_POST['address'];
    $data['nok_name'] = $_POST['nok_name'];
    $data['nok_phone_number'] = $_POST['nok_phone_number'];
    $data['nok_address'] = $_POST['nok_address'];
    $data['comment'] = null;
    $data['percentage'] = null;
    $data['blood_group'] = $_POST['blood_group'];
    $data['gender'] = $gender;
    $data['genotype'] = $_POST['genotype'];

    $error_msg = 'Opps something went wrong...';
    $error_status = 1;



    $percent = intval($data['percentage']);

    if ($percent < 0 || $percent > 100) {
        $error_msg = 'The percentage value is invalid';
    } else {
        $data['is_matched'] = false;
        if ($percent >= 60) {
            $data['is_matched']  =  true;
        }


        $addDonor = $Transplant->addDonor($data);

        if ($addDonor == true) {
            $error_status = 2;
            $error_msg = 'Saved successfully...';
            $update = $db->prepare("UPDATE enrollee SET blood_g = ?, geno_type = ? WHERE hospital_no = ? ");
            $update = $update->execute(array(
                $data['blood_group'],
                $data['genotype'],
                $data['hospital_no']
            ));
        } else {
            $error_msg = $addDonor;
        }
    }
}


if (isset($_REQUEST['update_booked_procedure'])) {
}




$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$hospital_no = null;

if (isset($_GET['hosp_no'])) {

    $hosp_no = $_GET['hosp_no'];
    $stmt = $db->query("SELECT e.*,i.insurance_name,i.interest,i.insurance_type FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE hospital_no='$hosp_no' and status='active'");
    if ($stmt->rowCount() > 0) {
        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
        $nhis_no = $row_rstSelect['nhis_no'];
        $nhis_no_ext = $row_rstSelect['nhis_no_ext'];
        $insurance = $row_rstSelect['insurance'];
        $interest = $row_rstSelect['interest'];
        $hospital_no = $row_rstSelect['hospital_no'];
        $validation_status = $row_rstSelect['validation_status'];
        $insurance_name = $row_rstSelect['insurance_name'];
        $insurance_type = $row_rstSelect['insurance_type'];
        $patient_name = $row_rstSelect["fname"] . ' ' .  $row_rstSelect["surname"];

        $stmt2 = $db->prepare("SELECT * FROM apptm where hospital_no='$hosp_no' and referal_doc!='Any Doctor' and status='checkin' ORDER BY sn DESC LIMIT 1");
        $stmt2->execute();
        if ($stmt2->rowCount() > 0) {
            $rwx = $stmt2->fetch(PDO::FETCH_ASSOC);
            $app_no = $rwx['appt_no'];
            $ap_type = $rwx['ap_type'];
            $status = $rwx['status'];
            $auth_code = $rwx['auth_code'];
            $auth_status = $rwx['status'];
        } else {
        }
    }
}



?>

<!DOCTYPE html>
<html>
<?php

include("../inc/header.php");

?>
<title>WebMedic | <?php if ($_SESSION['Designation'] != "") {
                        echo $_SESSION['Designation'];
                    } else {
                        echo $_SESSION['speciality'];
                    } ?></title>

<body>
    <script src="../js/jquery-2.1.1.js"></script>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>
        <div id="page-wrapper" class="gray-bg">
            <?php include("nav_header.php"); ?>
            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-md-12">

                      <?php
                                if (isset($_GET['hosp_no'])) {
                                    include("_transplant_details.php");
                                }else{
                            ?>

                            <div class="ibox float-e-margins">
                            <div class="ibox-title">

                                <h5>Transplant Panel</h5>
                                <div class="ibox-tools">
                                   
                                        <a href="#" class="btn btn-primary btn-xs " data-toggle="modal" data-target="#bookTransplantModal"> + Book New</a> |
                                        <a href="transplant.php" class="btn btn-danger btn-xs "> Home</a>
                                   
                                </div>
                            </div>
                            <div class="ibox-content">
                                

                            </div>
                        </div>
                        <?php
                                }

                        ?>
                      


                    </div>
                  
                </div>


            </div>










            <!--- MODALS HERE -->

            <div class="modal inmodal fade" id="editbookTransplantModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Procedure Booking</h4>
                        </div>

                        <div class="modal-body">
                            <?php
                            $sDate = null;
                            $sTime = null;
                            if (isset($_REQUEST['edit']) && isset($_REQUEST['p']) && isset($_GET['hosp_no']) && $hospital_no != null) {
                                $procedure_sn = $_REQUEST['p'];
                                $hosp_no = $_REQUEST['hosp_no'];

                                $patient_procedure_stmt = $db->prepare("SELECT * from procedures WHERE hospital_no = '$hosp_no' AND sn = $procedure_sn ORDER BY sn DESC LIMIT 1");
                                $patient_procedure_stmt->execute();
                                if ($patient_procedure_stmt->rowCount() > 0) {
                                    $procedure = $patient_procedure_stmt->fetch(PDO::FETCH_ASSOC);
                                    $procedure_sn = $procedure["sn"];
                                    $sDate = preg_split('/ /', $procedure["sDate"])[0];
                                    $sTime = preg_split('/ /', $procedure["sDate"])[1];

                            ?>
                                    <h4>Selected Procedure: <?= str_replace('||', ' , ', $procedure["procedures"]); ?></h4>
                            <?php
                                }
                            }
                            ?>
                            <form method="post" id="subject" action="procedures.php?hosp_no=<?= $hosp_no; ?>">
                                <div class="form_sep">

                                </div>

                                <div class="form_sep">
                                    <table cellpadding="1" width="100%">

                                        <tr>
                                            <td>
                                                <div class="form_sep">
                                                    <label for="reg_textarea_message" class="req">Start:</label>
                                                    <div class="" id="data_1">
                                                        <div class="input-group date">
                                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                            <input type="text" name="start_date" class="form-control" maxlength="10" value="<?= $sDate; ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form_sep">
                                                    <label for="reg_select" class="req">Time</label>
                                                    <select name="ap_time" id="ap_time" class="form-control" data-required="true">
                                                        <option value="<?php echo date('h:i:s a'); ?>">Now</option>
                                                        <?php
                                                        $meridians = ['AM', 'PM'];

                                                        foreach ($meridians as $key => $meridian) {
                                                            for ($i = 1; $i <= 12; $i++) {
                                                                if (strlen($i) == 1) {
                                                                    $i = '0' . $i;
                                                                }
                                                                $t1 = $i . ':00';
                                                                $t2 = $i . ':30';

                                                                echo '<option value="' . $t1 . ':00" ' . ($t1 . ':00' == $sTime ? 'selected' : '') . '>' . $t1 . ' ' . $meridian . '</option>';

                                                                echo '<option value="' . $t2 . ':00" ' . ($t2 . ':00' == $sTime ? 'selected' : '') . '>' . $t2 . ' ' . $meridian . '</option>';
                                                            }
                                                        }

                                                        ?>

                                                    </select>

                                                </div>

                                            </td>
                                            <td> </td>
                                        </tr>
                                    </table>
                                </div>
                                <br>
                                <br>

                        </div>
                        <div class="form_sep" style="padding: 20px;">
                            <p><input type="hidden" name="procedure_sn" value="<?= $procedure_sn; ?>"></p>
                            <p><input type="hidden" name="hospital_no" value="<?= $hosp_no; ?>"></p>
                            <div class="pull-left">
                                <button type="submit" class="btn btn-success btn btn-sm" name="update_booked_procedure" id="update_booked_procedure">Update Book Request</button>
                            </div>

                            <div class="pull-right">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel / Close</button>

                            </div>
                        </div>
                        <br>
                        <br>

                        </form>


                    </div>
                </div>
            </div>





            <?php include("../inc/footer.php"); ?>
        </div>
    </div>


    <?php include("../inc/footer_scripts.php"); ?>


    <script>
        $(document).ready(function() {

            $('.footable').footable();
            $('.footable2').footable();

        });
    </script>

    <script>
        <?php

        if ($error_status == 1) { ?>
            toastr.error('<?php echo $error_msg ?>', 'Error', {
                timeOut: 5000
            })

        <?php } else if ($error_status == 2) {
        ?>
            toastr.success(' <?php echo $error_msg ?> ', 'Success', {
                timeOut: 5000
            })
        <?php

        } elseif (isset($_GET['hosp_no'])) { ?>
            toastr.success('<?php echo 'Welcome to ' . $name . ' Dashboard'; ?>', 'Dashboard', {
                timeOut: 5000
            })
        <?php }

        ?>
    </script>







    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="../js/plugins/datapicker/select2.full.min.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <script src="../js/typeahead.min.js"></script>
    <!-- Chosen -->
    <script src="../js/plugins/chosen/chosen.jquery.js"></script>

    <?php

    if (isset($_REQUEST['edit']) && isset($_REQUEST['p']) && isset($_GET['hosp_no']) && $hospital_no != null) {
    ?>
        <script>
            $("#editbookTransplantModal").modal('show');
        </script>
    <?php
    }
    ?>
    <script>
        $(document).on('click', '#load_donor_record_btn', function() {
            let hospital_no = $('#donor_hospital_no').val();
            let transplant_id = $('#transplant_id_donor_form').val();

            if (hospital_no != "") {
                $("#donorFormModal").modal('show');
                $.ajax({
                    url: "donor_form.php",
                    method: "POST",
                    data: {
                        loadDonorForm: true,
                        hospital_no: hospital_no,
                        transplant_id: transplant_id,
                    },
                    success: function(data) {
                        $('#donor_form_wrap').html(data);

                    }
                });

            }
        });

        // $(document).on('submit', '#donor_form_wrap', function(event) {
        //     event.preventDefault();
        //     let donor = $(this).serializeArray().map(function(x) {
        //         this[x.name] = x.value;
        //         return this;
        //     }.bind({}))[0];

        //     $.ajax({
        //         url: "controllers/_donor.php",
        //         method: "POST",
        //         data: donor,
        //         success: function(data) {
        //             console.log(data);

        //         }
        //     });

        // });


        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            /* Init DataTables */
            var oTable = $('#editable').dataTable();

            /* Apply the jEditable handlers to the table */
            oTable.$('td').editable('../example_ajax.php', {
                "callback": function(sValue, y) {
                    var aPos = oTable.fnGetPosition(this);
                    oTable.fnUpdate(sValue, aPos[0], aPos[1]);
                },
                "submitdata": function(value, settings) {
                    return {
                        "row_id": this.parentNode.getAttribute('id'),
                        "column": oTable.fnGetPosition(this)[2]
                    };
                },

                "width": "90%",
                "height": "100%"
            });


        });
    </script>
    <script>
        $('.chosen-select').chosen({
            width: "100%"
        });
    </script>



</body>

</html>