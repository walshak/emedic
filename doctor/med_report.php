<?php session_start();
include("../Connections/Conn.php");
include("../inc/header.php");
include("../session.php");
include('objects.php');
include('helpers.php');
define('staff_p', '../uploads/staff/');
define('enrollee_p', '../uploads/enrollee/');
ob_start();
$setdate = date("Y-m-d");
$setdatetime = date("Y-m-d H:i:s");


include_once('_session.php');
if ($_SESSION['rights'] != 'DR' || !isset($_GET['hosp_no'])) {
    // header('location:../index.php');
    // exit;
}

$first_visit = false;
$hosp_no = cleanInput($_GET['hosp_no']);
$hospital_no = null;
$patient_name = null;
$sex = null;


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('_patient_styles.php'); ?>
    <link rel="stylesheet" href="bootstrap-select.min.css">
</head>

<body>
    <script src="../js/jquery-3.1.1.min.js"></script>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">
            <?php

            ///////////////////// GET PATIENT INFO //////////////////////
            // $patient_info = $Patient->get(['hospital_no' => $hosp_no]);
            $patient_info = $Patient->getByHospitalNo($hosp_no);
            $ap_type = 0;
            if (!empty($patient_info)) {
                $dob = $patient_info->dob;
                $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
                $sex = $patient_info->gender;
                $insurance_type = $patient_info->insurance_type;
                $interest = $patient_info->interest;
                $token = $patient_info->token;
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

            ?>


            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5>Prepare Medical Report</h5>
                                <h5 class="pull-right">
                                    <a href="patient.php?hosp_no=<?= $hosp_no; ?>" class=""><strong>[ Close <i class="fa fa-sign-out"></i> ]</strong></a>
                                </h5>
                            </div>
                            <div class="ibox-content">



                                <div class="row">
                                    <div class="col-md-8 col-lg-12">
                                        <div class="light-card">
                                            <div class="tab-content">
                                                <?php include('_patient_med_report.php'); ?>
                                            </div>
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

    <script src="../js/bootstrap.min.js"></script>
    <?php include("../inc/footer_scripts.php"); ?>
    <script src="../js/plugins/chosen/chosen.jquery.js"></script>
    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="../js/plugins/datapicker/select2.full.min.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <!-- <script src="../js/typeahead.min.js"></script> -->
    <script src="../js/typeahead.jquery.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>   -->
    <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
    <script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
    <script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>
    <!-- <script src="bootstrap-select.min.js"></script> -->
    <script src="../_special_scripts.js"></script>

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

            $('#__genserviceNote___')
                .trumbowyg()
                .on('tbwchange', function() {
                    $('#genserviceNote').val($('#__genserviceNote___').html())
                });

        });
    </script>


    <script>
        function openModal_fx(id) {
            $('#' + id).modal('show');
        }


        function fnClickAddRow() {
            $('#editable').dataTable().fnAddData([
                "Custom row",
                "New row",
                "New row",
                "New row",
                "New row"
            ]);

        }

        $(document).on('change', '#filter_adm_room_bed', function() {
            filterTbleFx('filter_adm_room_bed', 'adm_list_tbl')
        })

        $(document).on('keyup', '#search_adm_room_bed_input', function() {
            filterTbleFx('search_adm_room_bed_input', 'adm_list_tbl')
        })

        function filterTbleFx(id_, table_) {
            var filter = $('#' + id_).val();
            filter = filter.toUpperCase();

            var rows = document.querySelector("#" + table_ + " tbody").rows;

            for (var i = 0; i < rows.length; i++) {
                var firstCol = rows[i].cells[1].textContent.toUpperCase();
                var secondCol = rows[i].cells[2].textContent.toUpperCase();
                var thirdCol = rows[i].cells[3].textContent.toUpperCase();
                var fourCol = rows[i].cells[4].textContent.toUpperCase();
                // var fiveCol = rows[i].cells[5].textContent.toUpperCase();
                if (
                    firstCol.indexOf(filter) > -1 ||
                    secondCol.indexOf(filter) > -1 ||
                    thirdCol.indexOf(filter) > -1 ||
                    fourCol.indexOf(filter) > -1
                    // fiveCol.indexOf(filter) > -1
                ) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        var substringMatcher = function(strs) {
            return function findMatches(q, cb) {
                var matches, substringRegex;

                // an array that will be populated with substring matches
                matches = [];

                // regex used to determine if a string contains the substring `q`
                substrRegex = new RegExp(q, 'i');

                // iterate through the pool of strings and for any string that
                // contains the substring `q`, add it to the `matches` array
                $.each(strs, function(i, str) {
                    if (substrRegex.test(str)) {
                        matches.push(str);
                    }
                });

                cb(matches);
            };
        };
    </script>
    <?php
    /// Other included files
    include_once('_patient_medical_report_scripts.php');

    ?>
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

        }

        ?>
        $(document).ready(() => {
            $('#mgt_notes').summernote();
            $('.summernote').summernote();
            // $('#med_report_notes').summernote();

        });

        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: false,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            /* Init DataTables */
            var oTable = $('#editable').dataTable();




        });
    </script>

</body>

</html>