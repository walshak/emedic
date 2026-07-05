<?php
$patient_info = $Patient->getByHospitalNo($_GET['patient']);
if (isset($_POST['save_ivf_from'])) {
    //clean each input
    // Iterate through the $_POST array
    foreach ($_POST as $key => $value) {
        // Replace the value with the cleaned input
        $_POST[$key] = cleanInput($value);
    }

    //crate a varible to hold each of the posted values
    foreach ($_POST as $key => $value) {
        // Assign the value to a variable with the key name
        ${$key} = $value;
    }
    // Prepare the SQL statement
    $sql = "INSERT INTO ivf_form (ivf_date, ivf_hosp_no, ivf_husband_name, ivf_wife_name, ivf_age, ivf_tel, ivf_treat_plan, ivf_protocol, start_date, ivf_gnrha, ivf_gonadotrophin, ivf_days_of_stimulation, ivf_hcg, ivf_dose, ivf_date_administered, ivf_sample_type, ivf_date_of_analysis, ivf_volume, ivf_vicosity, ivf_conc_count, ivf_mortile_count, ivf_morphology, ivf_remarks, ivf_no_of_folicles, ivf_retrival_date, ivf_no_of_eggs, ivf_no_fertilized, ivf_fertiliztion_method, ivf_no_cleaved, ivf_no_frozen, ivf_no_transferd, ivf_transfer_date, ivf_embrayo_grade, ivf_blastocyst, ivf_blastocyst_no_frozen, ivf_pregnancy_test_date, ivf_support_drugs, ivf_embryologist, ivf_fertility_specialist, ivf_IVF_nurse)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $db->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(1, $ivf_date);
    $stmt->bindParam(2, $ivf_hosp_no);
    $stmt->bindParam(3, $ivf_husband_name);
    $stmt->bindParam(4, $ivf_wife_name);
    $stmt->bindParam(5, $ivf_age);
    $stmt->bindParam(6, $ivf_tel);
    $stmt->bindParam(7, $ivf_treat_plan);
    $stmt->bindParam(8, $ivf_protocol);
    $stmt->bindParam(9, $start_date);
    $stmt->bindParam(10, $ivf_gnrha);
    $stmt->bindParam(11, $ivf_gonadotrophin);
    $stmt->bindParam(12, $ivf_days_of_stimulation);
    $stmt->bindParam(13, $ivf_hcg);
    $stmt->bindParam(14, $ivf_dose);
    $stmt->bindParam(15, $ivf_date_administered);
    $stmt->bindParam(16, $ivf_sample_type);
    $stmt->bindParam(17, $ivf_date_of_analysis);
    $stmt->bindParam(18, $ivf_volume);
    $stmt->bindParam(19, $ivf_vicosity);
    $stmt->bindParam(20, $ivf_conc_count);
    $stmt->bindParam(21, $ivf_mortile_count);
    $stmt->bindParam(22, $ivf_morphology);
    $stmt->bindParam(23, $ivf_remarks);
    $stmt->bindParam(24, $ivf_no_of_folicles);
    $stmt->bindParam(25, $ivf_retrival_date);
    $stmt->bindParam(26, $ivf_no_of_eggs);
    $stmt->bindParam(27, $ivf_no_fertilized);
    $stmt->bindParam(28, $ivf_fertiliztion_method);
    $stmt->bindParam(29, $ivf_no_cleaved);
    $stmt->bindParam(30, $ivf_no_frozen);
    $stmt->bindParam(31, $ivf_no_transferd);
    $stmt->bindParam(32, $ivf_transfer_date);
    $stmt->bindParam(33, $ivf_embrayo_grade);
    $stmt->bindParam(34, $ivf_blastocyst);
    $stmt->bindParam(35, $ivf_blastocyst_no_frozen);
    $stmt->bindParam(36, $ivf_pregnancy_test_date);
    $stmt->bindParam(37, $ivf_support_drugs);
    $stmt->bindParam(38, $ivf_embryologist);
    $stmt->bindParam(39, $ivf_fertility_specialist);
    $stmt->bindParam(40, $ivf_IVF_nurse);

    // Execute the statement
    $stmt->execute();

    // Check if the insertion was successful
    if ($stmt->rowCount() > 0) {
        $error_status = 2;
        $error_msg = 'Success : Form Saved Successfully!';
    } else {
        $error_status = 1;
        $error_msg = 'Failiure : Form not Saved!';
    }
}

if (isset($_GET['pr'])) {
    include_once('procedure_detail.php');
} else if (isset($_GET['report'])) {
    include_once('_reports.php');
} else {
?>
    <div class="ibox ">
        <div class="ibox-title">
            <h5>IVF Details</h5>
            <div class="ibox-tools">
            </div>
        </div>

        <div class="ibox-content">
            <?php
            if (isset($_GET['patient']) and ($_SESSION['rights'] == 'NS' or $_SESSION['rights'] == 'AD' or $_SESSION['rights'] == 'DR')) {
            ?>

                <?php


                if ($rights == 'NS') {
                    $href = "nursing";
                } else {
                    $href = "doctor";
                } ?>
                <a href="../<?= $href; ?>/patient.php?hosp_no=<?php echo $_GET['patient']; ?>" class="btn btn-primary">
                    <i class="fa fa-user"></i>&nbsp;Goto Patient Dashboard</a>
                &nbsp; | &nbsp;

            <?php
            }
            ?>
            <a href="index.php?ivf_form" class="btn btn-success" style="color:#fff"> <i class="fa fa-home"></i>&nbsp;Goto All IVF Details List </a>

            <?php if (isset($_GET['patient'])): ?>
                <?php if (!empty($_GET['patient'])): ?>
                    &nbsp; | &nbsp; <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#bookProcedureModal"> + Fill New details </button> &nbsp; | &nbsp;
                <?php else: ?>
                    &nbsp; | &nbsp; <a href="index.php" type="button" class="btn btn-danger"> Close </a> &nbsp; | &nbsp;
                <?php endif ?>
            <?php else: ?>
                &nbsp; | &nbsp; <a href="index.php" type="button" class="btn btn-danger"> Close </a> &nbsp; | &nbsp;
            <?php endif ?>
            <a href="<?php echo $editFormAction . '&report'; ?>" class="btn btn-success disabled" style="color:#fff">&nbsp;Reports</a>



            <hr>
            <?php
            include_once('_procedure_list.php');
            ?>
        </div>
    </div>
<?php
}
?>


<?php
$procedure_history = [];
include_once('_book_procedure_modal.php');
?>
<script>
    $("#bookProcedureModal").modal('show');
</script>
<script>
    function load_template() {
        var template_id1 = document.getElementById('template_id1').value;
        toastr.info('Please wait...', '', {
            timeOut: 5000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                template_id: template_id1
            },
            success: function(data) {
                toastr.clear();
                $("#pre_opt_notes").html(data);
            }
        });
    }

    function load_template2() {
        var template_id2 = document.getElementById('template_id2').value;
        toastr.info('Please wait...', '', {
            timeOut: 5000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                template_id: template_id2
            },
            success: function(data) {
                toastr.clear();
                $("#post_opt_notes").html(data);
            }
        });
    }


    function pre_operation_save() {
        let mgt_notes = $("#pre_opt_notes").html();
        var sn = document.getElementById('sn').value;
        var sn_code = document.getElementById('sn_code').value;
        var hospital_no = document.getElementById('hospital_no').value;

        toastr.info('Please wait...', 'Saving', {
            timeOut: 1000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                save_pre_operation: true,
                pre_opt_notes: mgt_notes,
                sn: sn,
                hospital_no: hospital_no
            },
            success: function(response) {
                ///document.getElementById("pre_operation_btn").disabled = true; 
                toastr.success(response, 'Attention', {
                    timeOut: 1000
                })
                window.location.href = "index.php?procedure&pr=" + sn_code;
            }
        });
    }


    function post_operation_save() {
        let mgt_notes = $("#post_opt_notes").html();
        var sn = document.getElementById('sn').value;
        var sn_code = document.getElementById('sn_code').value;
        var hospital_no = document.getElementById('hospital_no').value;
        var post_op_results = document.getElementById('post_op_results').value;
        var performed_date = document.getElementById('performed_date').value;

        toastr.info('Please wait...', 'Saving', {
            timeOut: 1000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                save_post_operation: true,
                post_opt_notes: mgt_notes,
                sn: sn,
                hospital_no: hospital_no,
                post_op_results: post_op_results,
                performed_date: performed_date
            },
            success: function(response) {
                ///document.getElementById("pre_operation_btn").disabled = true; 
                toastr.success(response, 'Attention', {
                    timeOut: 1000
                })
                window.location.href = "index.php?procedure&pr=" + sn_code;
            }
        });
    }





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
    $(document).ready(function() {

        var search_specialist_result = [];
        $('#typeahead_search_specialist').typeahead({
            source: function(query, query_response) {

                $.ajax({
                    url: "controllers/specialist.php",
                    method: "POST",
                    data: {
                        search_specialist: true,
                        action: 'search_specialist',
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
</script>