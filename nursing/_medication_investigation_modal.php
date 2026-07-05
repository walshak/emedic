<div class="modal inmodal fade" id="medication_modal_" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 90%;top:-20px !important;margin-top:-20x !important">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Medications</h4>
            </div>

            <div class="modal-body">
                <?php

                include_once("components/_medications.php");
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>


        </div>
    </div>
</div>

<div class="modal inmodal fade" id="labtest_modal_" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 90%;top:0px !important">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Investigations</h4>
            </div>

            <div class="modal-body">
                <?php include_once("components/_investigations.php"); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<div class="modal inmodal fade" id="radiology_modal_" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Investigations</h4>
            </div>

            <div class="modal-body">
                <?php include_once("components/_radiology.php"); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>


        </div>
    </div>
</div>


<div class="modal inmodal fade" id="investigation_results_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Results</h4>
            </div>

            <div class="modal-body" id="investigation_results_modal_body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).on('click', '.labs-on-queue-btn', function() {

        let hospital_no = $(this).attr('arial-hospital-no');
        let lab_section = $(this).attr('arial-lab-section');

        $('#labs-on-queue-modal').modal('show');
        $('#labs-on-queue-body').html('<h4 class="text-center text-danger">Loading, please wait...</h4>');
        $.ajax({
            url: "controllers/_investigations.php",
            method: "POST",
            data: {
                load_patient_labs_on_queue: true,
                hospital_no: hospital_no,
                lab_section: lab_section
            },
            success: function(response) {
                $('#labs-on-queue-body').html(response);
                //    $('#labs-on-queue-datatable').dataTable();

            }
        });
    });



    function openInvestigationResultModal(hospital_no, test_id, investigation_type) {
        window.hospital_no = hospital_no;

        $('#investigation_results_modal').modal('show');
        $('#investigation_results_modal_body').html('<h6 class="text-center text-danger">Loading, please wait...</h6>');

        $.ajax({
            url: "controllers/_investigations.php",
            method: "POST",
            data: {
                openInvestigationResults: true,
                hospital_no: hospital_no,
                test_id: test_id,
                investigation_type: investigation_type
            },
            success: function(response) {

                $('#investigation_results_modal_body').html(response);

            }
        });

    }

    function filterradiologies() {
        filter_radiologies = radiologies


        filter_radiologies.forEach((element, index) => {
            selected_radiologies.forEach(elem => {
                if (elem.sn == element.sn) {
                    filter_radiologies.splice(index, 1);
                }
            })
        })

        // return filter_radiologies;
        showRadTable1(filter_radiologies);
        showRadTable2(selected_radiologies);
    }



    function openLabTestModal(hospital_no, appointment_number, investigation_type) {
        window.hospital_no = hospital_no;
        window.appointment_number = appointment_number;

        if (investigation_type == 'Laboratory') {
            $('#labtest_modal_').modal('show');
            filterLabtests();

        } else {
            filterradiologies();
            $('#radiology_modal_').modal('show');
        }

        $.ajax({
            url: "controllers/_investigations.php",
            method: "POST",
            data: {
                patient_appointment_investigations: true,
                hospital_no: hospital_no,
                appointment_number: appointment_number,
                investigation_type: investigation_type
            },
            success: function(response) {
                if (response.status == 200) {
                    if (investigation_type == 'Laboratory') {
                        window.selected_labtests = response.data;
                        filterLabtests();
                    } else {
                        window.selected_radiologies = response.data;
                        filterradiologies();
                    }

                }

            }
        });

    }


    openMedicationModal(<?php echo json_encode($hospital_no); ?>, <?php echo json_encode($appointment_number); ?>);

    function openMedicationModal(hospital_no, appointment_number) {
        window.hospital_no = hospital_no;
        window.appointment_number = appointment_number;



        filterDrugStocks()
        $.ajax({
            url: "controllers/_medication.php",
            method: "POST",
            data: {
                patient_appointment_medications: true,
                hospital_no: hospital_no,
                appointment_number: appointment_number
            },
            success: function(response) {
                if (response.status == 200) {

                    window.selected_drug_stocks = response.data;
                    console.log(selected_drug_stocks)
                    filterDrugStocks()
                }

            }
        });

    }

    <?php
    $sql = "SELECT  sn,product_name, CONCAT(product_name, '  Qty: [',qty,']') AS name, sn id, hosp_price, nhis_price,cash_price,expire_date,qty FROM stock_table 
                    WHERE status = 'active' AND hosp_price > 0 ";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $drug_stocks_list = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));

    $sql = "SELECT DISTINCT remarks name FROM patient_ap_services WHERE remarks IS NOT NULL AND remarks !=''";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $prescription_list = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
    ?>


    window.type_ahead_drugs = <?php echo json_encode($drug_stocks_list); ?>;
    window.drug_stocks = type_ahead_drugs;


    setTimeout(function() {
        $('#typeahead1').typeahead({
            source: function(query, result) {
                result(<?php echo json_encode($drug_stocks_list); ?>);
            }
        });


        $('#typeahead2').typeahead({
            source: function(query, result) {
                result(<?php echo json_encode($prescription_list); ?>);
            }
        });


        setTimeout(function() {
            $.ajax({
                url: "controllers/_medication.php",
                method: "POST",
                data: {
                    loadLabAndRad: true
                },
                success: function(response) {
                    if (response.status == 200) {
                        window.labtests = response.labtests;
                        window.radiologies = response.radiologies;
                    }
                }
            });
        }, 1800)


        var investigations_hx_url = "<?php echo $investigations_hx_url; ?>";
        $(document).ready(function() {

            /// alert("Investigations History URL: " + investigations_hx_url);
            setTimeout(function() {
                $.ajax({
                    url: investigations_hx_url,
                    method: "POST",
                    data: {
                        loadInvsHx: true,
                        hospital_no: "<?php echo $hospital_no; ?>"
                    },
                    success: function(response) {
                        $('#lab_hx__wrap').html(response.data.labs)
                        $('#rad_hx__wrap').html(response.data.rads)
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });
            }, 1500)

        })

    });
</script>

<script>
    $(document).ready(function() {
        var search_result = [];
        $('#typeahead_sample').typeahead({
            source: function(query, query_response) {

                $.ajax({
                    url: "controllers/_medication.php",
                    method: "POST",
                    data: {
                        drug_search: true,
                        hospital_no: "<?php echo $hospital_no; ?>"
                    },
                    dataType: "json",
                    success: function(data) {
                        search_result = data;
                        // query_response(search_result);
                        query_response($.map(data, function(item) {
                            console.log(item)
                            return item.name;

                        }));
                    }

                })
            },
            updater: function(item) {
                ///location.href = 'page99.php?choice=' + item
                return item
            }
        });

    });
</script>