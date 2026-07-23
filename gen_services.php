<!DOCTYPE html>
<html>
<?php
include("inc/session.php");
include("Connections/Conn.php");
include("inc/header_others_doc.php");
include('doctor/objects.php');
include('doctor/helpers.php');
date_default_timezone_set('Africa/Lagos');
$setdate = date("Y-m-d");
$setdatetime = date("Y-m-d H:i:s");

// Determine folder based on rights
$folder = ($rights === 'NS') ? 'nursing' : 'doctor';

// =========================
// GET TEMPLATE AJAX REQUEST
// =========================
if (isset($_POST['getTemplate'])) {

    // Clean and extract template ID
    $templateId = intval(cleanInput($_POST['template']));

    $stmt = $db->prepare("SELECT template FROM services_templates WHERE id = ?");
    $stmt->execute([$templateId]);

    if ($stmt->rowCount() > 0) {
        $temp = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $temp['template'];
    }

    exit;
}

// =========================
// DECODE HOSPITAL/APP NUMBER
// =========================
$decoded = cleanInput(isset($_GET['hosp_no']) ? $_GET['hosp_no'] : '');
$decoded = base64_decode(base64_decode($decoded));

list($hosp_no, $app_no) = array_pad(explode('||', $decoded), 2, null);

// =========================
// FETCH PATIENT DATA
// =========================
$patient_info = $Patient->getByHospitalNo($hosp_no);

if (empty($patient_info)) {
    header('Location: ../index.php');
    exit;
}

// Assign patient details
$hospital_no   = $hosp_no;
$patient_name  = $patient_info->surname . ' ' . $patient_info->fname;
$sex           = $patient_info->gender;
$dob           = $patient_info->dob;
$visit_status  = $patient_info->visit_status;
$insurance_type = $patient_info->insurance_type;
$interest      = $patient_info->interest;
$token         = $patient_info->token;
$age           = 0;  // you can add age calculation if needed
$ap_type       = 0;


?>
<title>WebMedic | <?= !empty($_SESSION['Designation'])
                        ? htmlspecialchars($_SESSION['Designation'])
                        : htmlspecialchars(isset($_SESSION['speciality']) ? $_SESSION['speciality'] : '') ?>
</title>

<script src="js/jquery-3.1.1.min.js"></script>

<body>
    <div id="wrapper">

        <div id="page-wrapper" class="gray-bg">
            <?php // include("$folder/nav_header.php"); 
            ?>
            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-lg-12">
                        <?php

                        if ($hospital_no != null) {
                            if (isset($_REQUEST['add-services-btn'])) {

                                $error_status = 1;
                                $error_msg = 'Oops! Something went wrong..';

                                $notes = $_POST['genserviceNote'];
                                $serviceTemplate = $_POST['serviceTemplate'];

                                $consultant_id = null;
                                $consultant_name = cleanInput($_POST['consultant_name']);
                                $data = explode('||', $serviceTemplate);
                                $template_id = $data[0];
                                $template_name = $data[1];

                                $app_service_tbl_id = null;
                                $isCompleted = false;
                                $service = null;


                                if (isset($_POST['isCompleted'])) {
                                    $isCompleted = true;
                                }

                                $stmt = $db->prepare("UPDATE notes_services SET status = '0' WHERE hospital_no = ? AND app_no = ? AND template_id = ? AND status = '1'");
                                $stmt->execute([$hospital_no, $app_no, $template_id]);

                                $stmt = $db->prepare("INSERT INTO notes_services (hospital_no, app_no, notes, app_service_tbl_id, created_at, 
                                created_by, prepared_by, status, consultant_id, consultant_name, isCompleted, template_id, template_name, service) VALUES (?, ?, ?, ?, now(), ?,?, '1', ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $hospital_no,
                                    $app_no,
                                    $notes,
                                    $app_service_tbl_id,
                                    $_SESSION['id'],
                                    $_SESSION['fullname'],
                                    $consultant_id,
                                    $consultant_name,
                                    $isCompleted,
                                    $template_id,
                                    $template_name,
                                    $template_name
                                ]);

                                $error_status = 2;
                                $error_msg = 'Saved';
                            }
                        ?>
                            <div class="ibox float-e-margins">
                                <div class="ibox-title">
                                    <h3>Detail: <?= $patient_name . ' [ ' . $hospital_no . ' ] '; ?> </h3>
                                    <h5 class="pull-right">
                                        <a href="<?= $folder; ?>/patient.php?hosp_no=<?= $hosp_no; ?>&charts" class="text-danger "><strong>[ Back ]</strong></a>
                                    </h5>
                                </div>
                                <div class="ibox-content">
                                    <div class="row">
                                        <?php
                                        if (isset($_GET['print'])) {
                                            $id = intval(base64_decode($_GET['print']));
                                            $stmt = $db->prepare("SELECT * FROM notes_services WHERE id = ?");
                                            $stmt->execute([$id]);
                                            if ($stmt->rowCount() > 0) {
                                                $note = $stmt->fetch();
                                                include_once('_gen_services_print.php');
                                            }
                                        } else {
                                        ?>
                                            <div class="col-lg-3">
                                                <?php include_once('_gen_services_hx.php'); ?>

                                            </div>
                                            <div class="col-lg-9">
                                                <div class="">
                                                    <?php
                                                    if (isset($_GET['edit'])) {
                                                        $id = intval(base64_decode($_GET['edit']));
                                                        $stmt = $db->prepare("SELECT * FROM notes_services WHERE id = ?");
                                                        $stmt->execute([$id]);
                                                        if ($stmt->rowCount() > 0) {
                                                            $note = $stmt->fetch();
                                                            include_once('_gen_services_editform.php');
                                                        } else {
                                                            include_once('_gen_services_form.php');
                                                        }
                                                    } else {
                                                        include_once('_gen_services_form.php');
                                                    }


                                                    ?>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>

                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>

                    </div>
                </div>
                <?php include("inc/footer.php"); ?>
            </div>
        </div>
    </div>
    <script src="js/plugins/chosen/chosen.jquery.js"></script>
    <script src="js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="js/plugins/datapicker/select2.full.min.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/typeahead.jquery.min.js"></script>
    <script src="inc/_special_scripts.js"></script>
    <script src="js/vendors/editor/dist/trumbowyg.js"></script>
    <script>
        function openModal_fx(id) {
            $('#' + id).modal('show');
        }

        function load_template() {
            document.getElementById('icd_10_type').style.display = 'none';
            document.getElementById('template_type').style.display = 'block';
            var doc_template = document.getElementById('doc_template').value;
            toastr.info('Please wait...', '', {
                timeOut: 5000
            })
            $.ajax({
                url: "inxtext_editor2.php",
                method: "POST",
                data: {
                    load_template: doc_template
                },
                success: function(data) {


                    toastr.clear();
                    $("#mgt_notes").html(data);
                }
            });
        }

        $(document).ready(function() {
            $('.trumbowygEditor').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['undo', 'redo'], // Only supported in Blink browsers
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                ]
            });
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
        }
        ?>
    </script>
    <script>
        $(document).on('keyup', '#__genserviceNote___', function() {
            $('#genserviceNote').val($('#__genserviceNote___').html())
        })


        $(document).ready(function() {
            var search_specialist_result = [];
            $('#typeahead_search_specialist').typeahead({
                source: function(query, query_response) {
                    $.ajax({
                        url: "doctor/controllers/specialist.php",
                        method: "POST",
                        data: {
                            search_specialist_nurses: true,
                            action: 'search_specialist_nurses',
                            input_text: $('#typeahead_search_specialist').val()
                        },
                        dataType: "json",
                        success: function(data) {
                            search_specialist_result = data;
                            query_response($.map(data, function(item) {
                                return item.name;
                            }));
                        }

                    })
                },
                updater: function(item) {
                    search_specialist_result.forEach(element => {
                        if (element.name == item) {
                            $('#typeahead_search_specialist_id').val(element.id)
                            $('#typeahead_search_specialist_name').val(element.name)
                            return item;
                        }
                    });
                    return item
                }
            });

        });

        function refreshEditor(id) {
            $('#' + id).trumbowyg({});
        }
    </script>
    <script>
        $(document).on('change', '#isServiceBillable', function(e) {
            if (e.target.checked == true) {
                $('#billableServices').show('slow');
            } else {
                $('#billableServices').hide('slow');
            }
        })


        $(document).on('change', '#serviceTemplate', function(e) {
            let template = $('#serviceTemplate').val();
            $('#genserviceNote').html('');
            if (template != '') {
                $.ajax({
                    url: "gen_services.php",
                    method: "POST",
                    data: {
                        getTemplate: true,
                        template: template
                    },
                    success: function(data) {
                        $('#__genserviceNote___').html(data);
                        $('#genserviceNote').val($('#__genserviceNote___').html())


                    }
                });
            }
        })
    </script>
</body>

</html>