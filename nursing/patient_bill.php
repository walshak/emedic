<!DOCTYPE html>
<html>
<?php
session_start();
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');
include("../inc/credit_current_balance.php");

$setdate = date("Y-m-d");
$setdatetime = date("Y-m-d H:i:s");
include("../inc/header.php");




if (isset($_GET['dl'])) {
    $sn = $_GET['dl'];

    $qry_drug = "DELETE FROM patient_ap_services WHERE sn='$sn'";
    $db->exec($qry_drug);

    if ($qry_drug == TRUE) {
        $error_status = 2;
        $error_msg = 'Success : Deleted';
    }
}

?>

<body>
    <div id="wrapper">

        <div id="page-wrapper" class="gray-bg">

            <?php
            $first_visit = false;
            $hosp_no = cleanInput($_GET['hosp_no']);
            ///$hosp_no = '000013'; //cleanInput($_GET['hosp_no']);


            $hospital_no = null;
            $patient_name = null;
            $sex = null;
            $window_mode = 'bill';


            $patient_info = $Patient->getByHospitalNo($hosp_no);
            $ap_type = 0;
            if (!empty($patient_info)) {
                $dob = $patient_info->dob;
                $visit_status = $patient_info->visit_status;
                $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
                $sex = $patient_info->gender;
                $insurance_type = $patient_info->insurance_type;
                $insurance_no = $patient_info->hmo_no;
                $interest = $patient_info->interest;
                $token = $patient_info->token;
                $add_minus = $patient_info->add_minus;
                $payment_mode = $patient_info->payment_mode;
                $insurance_no = $patient_info->insurance_no;

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
                $hospital_no = $hosp_no;
            } else {
                header('location:../index.php');
                exit;
            }


            $cur_date_time = date('Y-m-d H:i:s');
            $isOnAppoint = false;
            $appointment_number = null;
            $patientAppoint = null;
            $patient_access_type = null;
            $appointment_interest = null;
            $patient_insurance = null;
            /////////////////////// CHECK IF PATIENT HAS OPPENED APPOINTMENT, THEN GET THE ///////////////
            /////////// IF THE LOGGED IN USER IS A SPECIALIST

            $patientOnQueuestmt = $db->prepare("SELECT * FROM apptm  
		WHERE hospital_no = '$hospital_no' AND status != 'cancelled' ORDER BY sn LIMIT 1 ");
            $patientOnQueuestmt->execute();

            if ($patientOnQueuestmt->rowCount() > 0) {
                $patientAppoint = $patientOnQueuestmt->fetch();
                $appointment_number = $patientAppoint['appt_no'];
                $ap_type = $patientAppoint['ap_type'];
                $patient_insurance = $patientAppoint['insurance'];
                $patient_access_type = $patientAppoint['ap_type'];
                $appointment_interest = $interest;
            }

            $editFormAction = $_SERVER['PHP_SELF'];
            if (isset($_SERVER['QUERY_STRING'])) {
                $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
            }

            if (
                isset($_POST["add_services"]) or isset($_POST["service_apply"]) or
                isset($_GET["del_service"]) or isset($_GET["services"])
            ) {
                $current_tab = 'services';
            } elseif (
                isset($_POST["add_consumble"]) or isset($_GET["cn"]) or
                isset($_POST["service_apply_consumable"]) or
                isset($_POST["all_submit"]) or isset($_POST["specify_submit"])
            ) {
                $current_tab = 'con';
            } elseif (isset($_POST['process_inv'])) {
                $current_tab = 'invoice';
            } else {
                $current_tab = 'invoice';
            }

            //if (isset($_GET["con"])) {
            //	$current_tab='con';
            // }
            ?>


            <div class="wrapper wrapper-content">



                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Patient Dashboard </h5>
                                <h5 class="pull-right">

                                </h5>
                            </div>
                            <div class="ibox-content">

                                <div class="row">
                                    <div class="col-sm-8 b-r">
                                        <?php include_once('_patient_dashboard_profile_light.php');
                                        ?>
                                    </div>
                                    <div class="col-sm-4">

                                        <?php include_once('_patient_dashboard_links.php'); ?>


                                    </div>

                                </div>


                                <hr>



                                <div class="row">
                                    <div class="col-md-8 col-lg-12">
                                        <div class="light-card">




                                            <?php

                                            if (isset($_GET['Consumable'])) {
                                                include_once('consumables.php');
                                            } elseif (isset($_GET['Services'])) {
                                                include_once('nursing_services.php');
                                            } elseif (isset($_GET['Invoice'])) {
                                                include_once('invoice.php');
                                            }

                                            ?>




                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include("../inc/footer.php"); ?>
        </div>
    </div>


    <?php include('../modal_lock.php'); ?>
    <?php include("../inc/footer_scripts.php"); ?>
    <script src="../js/typeahead.jquery.min.js"></script>
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

        } ?>

        $(document).on('click', '.view_notes', function() {
            var note_inv_id = $(this).attr("id");
            var res = note_inv_id.split("__");

            if (note_inv_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        note_inv_id: res[0] + '__' + res[1]
                    },
                    success: function(data) {

                        $('.modal-title').text('Notes: ' + res[1]);

                        $('#view_notes_body').html(data);
                        $('#view_notes_modal').modal('show');
                    }
                });
            }
        });


        $(document).on('click', '.add_drug', function() {
            var add_drug_id = $(this).attr("id");
            var res = add_drug_id.split("__");

            if (add_drug_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        add_drug_id: res[0] + '__' + res[1]
                    },
                    success: function(data) {

                        $('.modal-title').text('Adding : ' + res[1]);

                        $('#add_drug_body').html(data);
                        $('#add_drug_modal').modal('show');
                    }
                });
            }
        });

        $(document).on('click', '.refill_drug', function() {
            var refill_drug_id = $(this).attr("id");
            //  var res = add_drug_id.split("__"); 

            if (refill_drug_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        refill_drug_id: refill_drug_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Add Consumable');

                        $('#refill_body').html(data);
                        $('#refill_modal').modal('show');
                    }
                });
            }
        });




        $(document).on('click', '.reverse', function() {
            var reverse_id = $(this).attr("id");



            if (reverse_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        reverse_id: reverse_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Reverse Consumable');

                        $('#reverse_body').html(data);
                        $('#reverse_modal').modal('show');
                    }
                });
            }
        });

        function reverse_drug_now(mode, tr) {

            var app_no = $('#app_no').val();
            var hosp_no = $('#hosp_no').val();
            var sale_sn = $('#sale_sn').val();
            var drug_sn = $('#drug_sn').val();
            var EX_or_IN = $('#EX_or_IN').val();
            var where = 'nursing';
            var names = $('#names').val();
            var specify_qtyy = $('#specify_qtyy').val();
            var all_qtyy = $('#all_qtyy').val();


            if (mode == 'specify_qty' && specify_qtyy > all_qtyy) {

                toastr.error('Invalid Quantity!', 'Error', {
                    timeOut: 3000
                })
                exit;

            } else {

                $.ajax({
                    url: "../inc/inv_pro.php",
                    method: "POST",
                    data: {
                        reverse_drug: mode,
                        hosp_no: hosp_no,
                        app_no: app_no,
                        sale_sn: sale_sn,
                        drug_sn: drug_sn,
                        EX_or_IN: EX_or_IN,
                        where: where,
                        specify_qtyy: specify_qtyy,
                        all_qtyy: all_qtyy
                    },
                    success: function(data) {

                        document.getElementById("reverse_btn").disabled = true;

                        var jsonn = JSON.parse(data);
                        if (jsonn["status"] == 1) {
                            document.getElementById("reverse_btn").disabled = true;
                            toastr.error(jsonn["message"], 'Attention', {
                                timeOut: 5000
                            })
                        } else {
                            toastr.success(jsonn["message"], 'Attention', {
                                timeOut: 5000
                            })
                            window.location.href = 'patient_bill.php?hosp_no=' + hosp_no + '&con';
                        }
                    }
                });
            }
        }
    </script>

    <script>
        $(document).ready(function() {

            $('#stock_select').select2({
                width: '350px',
                placeholder: '-- Select Stock you wish to Deduct --',
                minimumInputLength: 1, // start searching after 1 char

                ajax: {
                    url: 'fetch_stocks.php',
                    dataType: 'json',
                    delay: 250,

                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },

                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },

                    cache: true
                }
            });

        });



        $(document).ready(function() {

            var consumables = [];

            $('#stock_consumbl_list').typeahead({
                minLength: 2,
                items: 10,
                autoSelect: false,

                source: function(query, process) {
                    $.ajax({
                        url: "consumable_table.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                            consumable_table: true,
                            input_text: query
                        },
                        success: function(data) {

                            consumables = data;

                            var names = $.map(data, function(item) {
                                return item.product_name;
                            });

                            process(names);
                        }
                    });
                },

                afterSelect: function(item) {

                    // Find selected object
                    var selected = consumables.find(function(obj) {
                        return obj.product_name === item;
                    });

                    if (selected) {
                        $('#consumable_sn').val(selected.sn);
                    }
                }
            });

        });


        $('#stock_consumbl_list').on('input', function() {
            $('#consumable_sn').val('');
        });




        function reverse_drug_now(mode, tr) {

            var app_no = $('#app_no').val();
            var hosp_no = $('#hosp_no_nursing').val();
            var sale_sn = $('#sale_sn').val();
            var drug_sn = $('#drug_sn').val();
            var EX_or_IN = $('#EX_or_IN').val();
            var where = 'pharmacy';
            var names = $('#names').val();
            var specify_qtyy = $('#specify_qtyy').val();
            var all_qtyy = $('#all_qtyy').val();

            if (mode == 'specify_qty' && specify_qtyy > all_qtyy) {

                toastr.error('Invalid Quantity!', 'Error', {
                    timeOut: 3000
                })
                exit;

            } else {

                $.ajax({
                    url: "../inc/inv_pro.php",
                    method: "POST",
                    data: {
                        reverse_drug: mode,
                        hosp_no: hosp_no,
                        app_no: app_no,
                        sale_sn: sale_sn,
                        drug_sn: drug_sn,
                        EX_or_IN: EX_or_IN,
                        where: where,
                        specify_qtyy: specify_qtyy,
                        all_qtyy: all_qtyy
                    },
                    success: function(data) {
                        document.getElementById("reverse_btn").disabled = true;

                        var jsonn = JSON.parse(data);
                        if (jsonn["status"] == 1) {
                            document.getElementById("reverse_btn").disabled = true;
                            toastr.error(jsonn["message"], 'Attention', {
                                timeOut: 5000
                            })
                        } else {
                            toastr.success(jsonn["message"], 'Attention', {
                                timeOut: 5000
                            })
                            document.getElementById("reverse_btn2").disabled = true;
                            document.getElementById("reverse_btn").disabled = true;
                            ///window.location.href = 'index.php?presc&hos_no=' + hosp_no;
                        }
                    }
                });
            }
        }
    </script>


    <script src="../js/idle.js"></script>
</body>

</html>