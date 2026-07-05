<?php session_start();
include("../Connections/Conn.php");
include('objects.php');
include("_session.php");
include('helpers.php');
$error_status = null;
$inbox = null;
$labels_string = null;
$data_string = null;
$data_string2 = null;
$count_draft = null;
$labels_string_tem = null;
$data_string_tem = null;
$labels_string_weight = null;
$data_string_weight = null;
$labels_string_height = null;
$data_string_height = null;

$labels_string_resp = null;
$labels_string_pulse = null;
$data_string_pulse = null;
$labels_string_muac = null;
$data_string_muac = null;
$labels_string_head = null;
$data_string_head = null;
$data_string_resp = null;


$qty_err = null;
$qty_err = null;
$qty_err = null;
$qty_err = null;
$qty_err = null;
$qty_err = null;


/*
if ($_SESSION['rights'] != 'DR') {
    if (!($_SESSION['rights'] == 'RE' && isset($_GET['procedure']) )) {
        header('location:../index.php');
        exit;
    }
}*/


$templates = null;
$appointment_number = null;

?>

<!DOCTYPE html>
<html>
<?php

include("../inc/header.php");
include_once("../inc/alert_msg.php");

if (isset($_POST['apply_approve'])) {

    $in_patient = $_POST['in_patient'];
    //$part=explode($in_patient,"_");
    header("location:index.php?vitals=$in_patient");
}


///echo '===' . $_SESSION['doctor'];

?>


<title>WebMedic | <?php if ($_SESSION['Designation'] != "") {
                        echo $_SESSION['Designation'];
                    } else {
                        echo $_SESSION['specialist'];
                    } ?></title>

<script src="ckeditor.js"></script>
<script src="../js/jquery-3.1.1.min.js"></script>

<body>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">
            <?php include("nav_header.php"); ?>
            <div class="wrapper wrapper-content">

                <?php

                if (strtoupper($_SESSION['password']) == 'STAFF123') {
                    header("location:../profile/index.php?profile=$uname&changepassword");
                }

                if (isset($_GET['hosp_no'])) {

                    header('location:patient.php?hosp_no=' . $_GET['hosp_no']);
                } elseif (isset($_GET['md'])) {
                    include("med_rpt.php");
                } elseif (isset($_GET['search'])) {
                    include("search.php");
                } elseif (isset($_GET['procedure'])) {
                    include("procedures/index.php");
                } elseif (isset($_GET['ivf_form'])) {
                    include("ivf_form/index.php"); //walshak
                } elseif (isset($_GET['transplant'])) {
                    include("_transplant.php");
                } else {
                    include("dashboard.php");
                }

                ?>
            </div>

            <div class="modal inmodal fade" id="staff_alert_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Staff Alert !</h4>
                        </div>

                        <div class="modal-body" id="claims_body">

                            <?php if ($display_status == 1) { ?>

                                <form action="index.php" method="POST">

                                    <p><?php echo $my_note; ?></p>

                                    <button type="submit" name="read_msg" class="btn btn-primary">Yes, I have read it</button>
                                </form>

                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal inmodal fade" id="bio_data_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Patient Biodata</h4>
                        </div>
                        <div class="modal-body" id="bio_data_body">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal inmodal fade" id="manage_patient_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Manage Patient</h4>
                        </div>
                        <div class="modal-body" id="manage_patient_body">
                        </div>
                    </div>
                </div>
            </div>


            <?php include('../modal_lock.php'); ?>
            <?php include("../inc/footer.php"); ?>
        </div>
    </div>






    <script>
        <?php if ($display_status == 1) { ?>
            $(document).ready(function() {
                $("#staff_alert_modal").modal('show');
            });
        <?php  } ?>
    </script>


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






    <script>
        <?php if ($clinical_count > 0) { ?>
            $(document).ready(function() {

            });
        <?php } ?>


        $(document).on('click', '.open_search', function() {
            $('#search_modal').modal('show');
        });
    </script>

    <script src="../js/idle.js"></script>

    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="../js/plugins/datapicker/select2.full.min.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <script src="../js/typeahead.jquery.min.js"></script>
    <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
    <script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
    <script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>

    <script>
        $(document).ready(function() {
            $('.trumbowygEditor').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['undo', 'redo'], // Only supported in Blink browsers
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['fontsize'],
                    ['foreColor', 'backColor'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                ],
                plugins: {
                    fontsize: {
                        sizeList: [
                            '12px',
                            '14px',
                            '16px',
                            '18px',
                            '20px',
                            '24px',
                            '32px',
                            '48px',
                        ]
                    }
                }
            });

        });

        $(document).ready(function() {
            var search_result = [];
            $('#search_patient_input').typeahead({

                source: function(query, query_response) {
                    console.log($('#search_patient_input').val())
                    $.ajax({
                        url: "_search_patient_json.php",
                        method: "POST",
                        data: {
                            search_patient_json: true,
                            input_text: $('#search_patient_input').val()
                        },
                        dataType: "json",
                        success: function(data) {

                            search_result = data;
                            query_response($.map(data, function(item) {

                                return item.name;

                            }));
                        }

                    })
                },
                updater: function(item) {
                    search_result.forEach(element => {
                        if (element.name == item) {
                            $('#search_patient_hospital_no').val(element.hospital_no)
                            return item;
                        }
                    });
                    return item
                }
            });

        });
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
    <?php

    if (isset($_POST['patient_seen_report_btn'])) {
    ?>
        <script>
            $('.patient-seen-dataTables').dataTable({
                responsive: true
            });
        </script>
    <?php
    }
    ?>

    <script>
        $('#patient_seen_report_btn').attr('disabled', true)
        $(document).on('change', '.patient_seen_report_elem', function() {
            $('#patient_seen_report_btn').attr('disabled', true)
            if ($('#patient_seen_from').val() != '' ||
                $('#patient_seen_to').val() != '' ||
                $('#patient_seen_consultation_services').val() != '') {
                $('#patient_seen_report_btn').attr('disabled', false)
            }
        });


        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            $('.dataTables-example2').dataTable({
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

        function fnClickAddRow() {
            $('#editable').dataTable().fnAddData([
                "Custom row",
                "New row",
                "New row",
                "New row",
                "New row"
            ]);

        }

        $('#data_5 .input-daterange').datepicker({
            keyboardNavigation: false,
            forceParse: false,
            autoclose: true
        });

        $(document).ready(function() {
            $("#cCustomer").select2({
                ajax: {
                    url: "fetch_labtest.php",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function(data, page) {
                        // parse the results into the format expected by Select2.
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data
                        return {
                            results: data.items
                        };
                    },
                    cache: true
                },
                escapeMarkup: function(markup) {
                    return markup;
                }, // let our custom formatter work
                minimumInputLength: 1,
                //templateResult: formatRepo, // omitted for brevity, see the source of this page
                //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
            });
        });


        $(document).on('click', '.progress_notes', function() {
            var patient_nos = $(this).attr("id");
            var res = patient_nos.split("__");

            if (patient_nos != '') {
                $.ajax({
                    url: "notes_modal.php",
                    method: "POST",
                    data: {
                        patient_nos: patient_nos
                    },
                    success: function(data) {

                        $('.modal-title').text('Progress Note / ' + res[2]);
                        $('#view_progress_note').html(data);
                        $('#view_progress_note_modal').modal('show');
                    }
                });
            }
        });



        $(document).on('click', '.edit_notes', function() {
            var note_id = $(this).attr("id");
            var res = note_id.split("__");

            if (note_id != '') {
                $.ajax({
                    url: "notes_modal.php",
                    method: "POST",
                    data: {
                        note_id: note_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Progress Note / ' + res[1]);
                        $('#view_progress_note').html(data);
                        $('#view_progress_note_modal').modal('show');
                    }
                });
            }
        });




        $(document).on('click', '.full_task', function() {
            $('#view_clinical_task_modal').modal('hide');
            var full_task_id = $(this).attr("id");
            // var res = note_id.split("__"); 
            if (full_task_id != '') {
                $.ajax({
                    url: "clinical_tasks.php",
                    method: "POST",
                    data: {
                        full_task_id: full_task_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Fulfil Clinical Tasks');
                        $('#fulfil_clinical_task').html(data);
                        $('#fulfil_clinical_task_modal').modal('show');
                    }
                });
            }
        });



        //med_rpt_modal

        $(document).on('click', '.med_rpt', function() {
            $('#med_rpt_modal').modal('hide');
            var med_rpt_id = $(this).attr("id");
            if (med_rpt_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        med_rpt_id: med_rpt_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Medical Report');
                        $('#med_rpt_body').html(data);
                        $('#med_rpt_modal').modal('show');
                    }
                });
            }
        });








        $(document).on('click', '.open-modal-btn', function() {
            $('#' + $(this).attr('arial-modal')).modal('show')
        });


        function load_templates(template_id, editor_) {

            if (template_id == '') {
                $('#' + editor_ + '_wrap').html('<textarea name="' + editor_ + '" id="' + editor_ + '" cols="30" rows="10" class="summernote"></textarea>')
                $('#' + editor_).summernote();
            } else {
                toastr.info('Loading, please wait...', 'Info', {
                    timeOut: 3000
                })
                $.ajax({
                    url: "_load_template.php",
                    method: "POST",
                    data: {
                        template_id: template_id
                    },
                    success: function(response) {
                        toastr.clear();
                        console.log(response)
                        $('#' + editor_ + '_wrap').html('<textarea name="' + editor_ + '" id="' + editor_ + '" cols="30" rows="10" class="summernote">' + response + '</textarea>')
                        $('#' + editor_).summernote();
                    },
                    error: function(error) {
                        alert('Error Occured')
                    }
                });
            }

        }



        $(document).on('click', '#reset-patient-donor-form', function(evt) {
            evt.preventDefault();
            $('#search-patient-donor-wrap').show('slow')
            $('#search-patient-donor-form-wrap').hide('slow')
        });



        $(document).on('click', '.open-edit-trans-cons-note-btn', function() {
            var modal_ = $(this).attr("arial-modal");
            var trans_note_id = $(this).attr("arial-id");
            toastr.info('Loading: please wait...', 'Info', {
                timeOut: 3000
            })
            $.ajax({
                url: "_transplant_details.php",
                method: "POST",
                data: {
                    trans_note_id: trans_note_id,
                    fetch_edit_trans_note: true
                },
                success: function(response) {
                    toastr.clear();
                    //  $(this).html('<i class="fa fa-edit"></i>Edit');

                    $('#edit_consultation_notes_modal_content').html(response);
                    $('#' + modal_).modal('show');
                    $('#edit_consulation_notes').summernote();
                },
                error: function(error) {
                    alert('Error Occured')
                }
            });
        });


        $(document).on('click', '#search-patient-donor-btn', function() {
            let hospital_no = $('#patient-id-input').val();
            $('#search-patient-donor-form-wrap').hide('slow')
            if (hospital_no.trim() != '') {
                hospital_no = hospital_no.trim();
                $('#search-patient-donor-btn').text('Searching...')
                $('#search-patient-donor-btn').attr('disabled', true)
                $.ajax({
                    url: "_transplant_details.php",
                    method: "POST",
                    data: {
                        hospital_no: hospital_no,
                        transplant_id: transplant_id,
                        search_patient_donor: true
                    },
                    success: function(response) {
                        toastr.clear();
                        $('#search-patient-donor-btn').attr('disabled', false)
                        $('#search-patient-donor-btn').text('Search')
                        if (response.status == undefined) {
                            $('#search-patient-donor-wrap').hide('slow')
                            $('#search-patient-donor-form-wrap').show('slow')
                            $('#search-patient-donor-form-wrap').html(response)

                        } else {
                            toastr.error(response.message, 'Error', {
                                timeOut: 3000
                            })
                            $('#search-patient-donor-wrap').show('slow')
                            $('#search-patient-donor-form-wrap').hide('slow')
                        }

                    },
                    error: function(error) {
                        alert('Error Occured')
                    }
                });
            } else {
                toastr.info('Error: please provide hospital number...', 'Info', {
                    timeOut: 3000
                })
            }
        });

        $(document).on('click', '.bio_data_link', function() {
            var bio_data_id = $(this).attr("id");
            ///	alert();

            if (bio_data_id != '') {
                $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                        bio_data_id: bio_data_id
                    },
                    success: function(data) {

                        $('.modal-title').text('Patient Bio - Data');

                        $('#bio_data_body').html(data);
                        $('#bio_data_modal').modal('show');
                    }
                });
            }
        });






        function payNow(sale_sn, target, hospital_no) {

            if (target == 'procedure') {
                var rr = confirm("Are you sure you want to Pay from Patient's Deposit?");
            } else {
                rr = true;
            }

            if (rr === true) {

                document.getElementById('pay_now' + sale_sn).innerHTML = "Wait ...";
                document.getElementById('pay_now' + sale_sn).disabled = true;

                $.ajax({
                    url: "../payfrom_wallet.php",
                    method: "POST",
                    data: {
                        sale_sn: sale_sn
                    },
                    success: function(data) {

                        var jsonn = JSON.parse(data);
                        if (jsonn["status"] == 1) {

                            document.getElementById("pay_now" + sale_sn).innerHTML = 'Pay from Wallet';
                            document.getElementById('pay_now' + sale_sn).disabled = false;
                            toastr.error(jsonn["message"], 'Attention', {
                                timeOut: 5000
                            })
                        } else {

                            document.getElementById("pay_now" + sale_sn).innerHTML = 'Paid';
                            toastr.success('Successful!', 'Success', {
                                timeOut: 5000
                            })
                        }

                    }
                });
            }
        }


        function see_patient_manage(fullname) {

            $.ajax({
                url: "_patients_manage_list.php",
                method: "POST",
                data: {
                    fullname: fullname
                },
                success: function(data) {

                    document.getElementById("wait_patient_mgt").innerHTML = '';
                    document.getElementById("wait_patient_mgt2").innerHTML = data;
                    //// alert(data);


                }
            });

        }
    </script>








    <?php

    ///////////////////////
    ////////////////// DRUG & PLAN SCRIPTS /////////////////////
    include('_drug_plan_scripts.php');
    ////////////////// END DRUG & PLAN SCRIPTS  /////////////////////
    /////////////////// MEDICAL HISTORY SCRIPTS /////////////////////
    ///include('_medical_hx_scripts.php'); 
    /////////////////// END MEDICAL HISTORY SCRIPTS /////////////////////

    include_once('../search_patient_code_scripts.php');

    ///////////////// ADMISSION PAGE ///////////////
    // include_once('_admission_scripts.php'); 
    ////////////////////// ADMISSION PAGE ENDS HERE /////////////////////////// 

    //////////////// PROCEDURES 
    include_once('_procedures_scripts.php');



    if (isset($_GET['procedure']) || isset($_GET['transplant'])) {
        // include('_medication_investigation_modal.php');

        if (isset($_GET['transplant'])) {
            include('components/_transplant_request.php');

            if (isset($_GET['patient'])) {
    ?>
                <script>
                    $("#bookTransplantModal").modal('show');
                </script>
    <?php
            }
        };
    }
    ?>





</body>

</html>