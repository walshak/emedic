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

<div class="modal inmodal fade" id="investigation_results_modal2" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 60%;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Result</h4>
            </div>

            <div class="modal-body" id="investigation_results_modal2_body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<script>
    var investigations_controller_url = "<?php echo $investigations_controller_url; ?>";
    var medication_controller_url = "<?php echo $medication_controller_url; ?>";

    $(document).on('click', '.labs-on-queue-btn', function() {

        let hospital_no = $(this).attr('arial-hospital-no');
        let lab_section = $(this).attr('arial-lab-section');

        $('#labs-on-queue-modal').modal('show');
        $('#labs-on-queue-body').html('<h4 class="text-center text-danger">Loading, please wait...</h4>');
        $.ajax({
            url: investigations_controller_url,
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
            url: investigations_controller_url,
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

    function openInvestigationResultModal2(hospital_no, lab_sn, investigation_type) {
        window.hospital_no = hospital_no;

        /// alert(investigations_controller_url);

        $('#investigation_results_modal2').modal('show');
        $('#investigation_results_modal2_body').html('<h6 class="text-center text-danger">Loading, please wait...</h6>');

        $.ajax({
            url: investigations_controller_url,
            method: "POST",
            data: {
                openInvestigationResult: true,
                hospital_no: hospital_no,
                lab_sn: lab_sn,
                investigation_type: investigation_type
            },
            success: function(response) {

                ////alert(response);

                $('#investigation_results_modal2_body').html(response);

            }
        });

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
            url: investigations_controller_url,
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
                        filterLabtests()
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
            url: medication_controller_url,
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


    setTimeout(function() {

        setTimeout(function() {
            $.ajax({
                url: medication_controller_url,
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


        var investigations_lab_hx_url = "<?php echo $investigations_lab_hx_url; ?>";
        $(document).ready(function() {
            setTimeout(function() {
                $.ajax({
                    url: investigations_lab_hx_url,
                    method: "POST",
                    data: {
                        loadInvsHx: true,
                        hospital_no: "<?php echo $hospital_no; ?>",
                        lab_page: "1"
                    },
                    success: function(response) {
                        $('#page_bottom').html(response.data.page)
                        $('#lab_hx__wrap').html(response.data.labs)
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });

                $.ajax({
                    url: '_imaging_hx.php',
                    method: "POST",
                    data: {
                        loadInvsHx: true,
                        hospital_no: "<?php echo $hospital_no; ?>",
                        rad_page: "1"
                    },
                    success: function(response) {
                        /// alert(response.data.page);
                        $('#page_rad_bottom').html(response.data.page_rad)
                        $('#rad_hx__wrap').html(response.data.rads)
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });
            }, 1500)

        })

    });





    function applyLabFilters() {
        loadInvHistory(1);
    }

    function applyLabFilters_rd() {
        loadInvHistory_rd(1);
    }

    function loadInvHistory_rd(page) {
        var investigations_hx_url_rd = "<?php echo $investigations_image_hx_url; ?>";
        $.ajax({
            url: investigations_hx_url_rd,
            method: "POST",
            data: {
                loadInvsHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                lab_page: "1",
                rad_page: "1",
                test_name_rd: $("#filter_test_name_rd").val(),
                app_no_rd: $("#filter_visits_rd").val(),
                from_date_rd: $("#filter_from_date_rd").val(),
                to_date_rd: $("#filter_to_date_rd").val()
            },
            success: function(response) {
                $('#page_rad_bottom').html(response.data.page_rad)
                $('#rad_hx__wrap').html(response.data.rads)
            }
        });
    }

    function loadInvHistory(page) {
        var investigations_hx_url = "<?php echo $investigations_hx_url; ?>";
        $.ajax({
            url: investigations_hx_url,
            method: "POST",
            data: {
                loadInvsHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                lab_page: "1",
                test_name: $("#filter_test_name").val(),
                app_no: $("#filter_visits").val(),
                from_date: $("#filter_from_date").val(),
                to_date: $("#filter_to_date").val(),
            },
            success: function(response) {
                $('#page_bottom').html(response.data.page)
                $('#lab_hx__wrap').html(response.data.labs)
            }
        });
    }

    function load_more_lab(current_page_) {
        var investigations_lab_hx_url = "<?php echo $investigations_lab_hx_url; ?>";
        $.ajax({
            url: investigations_lab_hx_url,
            method: "POST",
            data: {
                loadInvsHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                lab_page: current_page_
            },
            success: function(response) {
                $('#page_bottom').html(response.data.page)
                $('#lab_hx__wrap').html(response.data.labs)
            },
            error: function(err) {
                console.log(err)
            }
        });
    }

    function load_more_rad(current_page_) {

        var investigations_image_hx_url = "<?php echo $investigations_image_hx_url; ?>";
        $.ajax({
            url: investigations_image_hx_url,
            method: "POST",
            data: {
                loadInvsHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                rad_page: current_page_
            },
            success: function(response) {
                ///  alert(response.data.page);
                $('#page_rad_bottom').html(response.data.page_rad)
                $('#rad_hx__wrap').html(response.data.rads)
            },
            error: function(err) {
                console.log(err)
            }
        });

    }
</script>
<script>
    $(document).ready(function() {

        var drug_search_results = [];

        // Create a container for the dropdown results
        $('<div id="drug_search_dropdown" class="dropdown-menu" style="position: absolute; display: none; z-index: 1000;"></div>').insertAfter('#drug_search_typeahead_d');

        // Handle input on the search field
        $('#drug_search_typeahead_d').on('input', function() {
            var query = $(this).val();

            if (query.length < 2) {
                $('#drug_search_dropdown').hide();
                return;
            }

            // Make AJAX request
            $.ajax({
                url: "search_drugs.php",
                method: "POST",
                data: {
                    drug_search_typeahead: true,
                    action: 'drug_search_typeahead',
                    input_text: query
                },
                dataType: "json",
                success: function(data) {
                    drug_search_results = data;

                    // Clear previous results
                    $('#drug_search_dropdown').empty();

                    if (data.length > 0) {
                        // Build dropdown items
                        $.each(data, function(index, item) {
                            var displayName = item.name || item.product_name;

                            // Apply red color to expiry warning if expires_soon is 1
                            if (item.expires_soon == 1) {
                                var expiryMatch = displayName.match(/\*\*exp in \d+ days\*\*/);
                                if (expiryMatch) {
                                    var expiryText = expiryMatch[0] || '';
                                    displayName = displayName.replace(
                                        expiryText,
                                        '<span style="color: red">' + expiryText + '</span>'
                                    );
                                }
                            }

                            $('<a href="#" class="dropdown-item">' + displayName + '</a>')
                                .appendTo('#drug_search_dropdown')
                                .data('item', item)
                                .on('click', function(e) {
                                    e.preventDefault();
                                    var selectedItem = $(this).data('item');

                                    // Fill in the values
                                    $('#drug_search_typeahead_d').val(selectedItem.name || selectedItem.product_name);
                                    $('#drug_search_typeahead_id').val(selectedItem.id);
                                    $('#drug_search_typeahead_product_name').val(selectedItem.product_name);
                                    $('#drug_search_typeahead_hosp_price').val(selectedItem.hosp_price);
                                    $('#drug_search_typeahead_cash_price').val(selectedItem.cash_price);
                                    $('#drug_search_typeahead_nhis_price').val(selectedItem.nhis_price);

                                    // Hide dropdown
                                    $('#drug_search_dropdown').hide();
                                });
                        });

                        // Position and show dropdown
                        var inputPosition = $('#drug_search_typeahead_d').position();
                        var inputHeight = $('#drug_search_typeahead_d').outerHeight();

                        $('#drug_search_dropdown').css({
                            top: inputPosition.top + inputHeight,
                            left: inputPosition.left,
                            width: $('#drug_search_typeahead_d').outerWidth(),
                            maxHeight: '400px',
                            overflowY: 'auto'
                        }).show();
                    } else {
                        $('#drug_search_dropdown').hide();
                    }
                }
            });
        });

        // Hide dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#drug_search_typeahead, #drug_search_dropdown').length) {
                $('#drug_search_dropdown').hide();
            }
        });

        // Handle keyboard navigation
        $('#drug_search_typeahead_d').on('keydown', function(e) {
            var dropdown = $('#drug_search_dropdown');
            var items = dropdown.find('.dropdown-item');
            var current = dropdown.find('.active');
            var index = items.index(current);

            if (dropdown.is(':visible')) {
                // Down arrow
                if (e.keyCode === 40) {
                    e.preventDefault();
                    if (items.length > 0) {
                        if (index < 0 || index >= items.length - 1) {
                            items.removeClass('active');
                            $(items[0]).addClass('active');
                        } else {
                            items.removeClass('active');
                            $(items[index + 1]).addClass('active');
                        }
                    }
                }
                // Up arrow
                else if (e.keyCode === 38) {
                    e.preventDefault();
                    if (items.length > 0) {
                        if (index <= 0) {
                            items.removeClass('active');
                            $(items[items.length - 1]).addClass('active');
                        } else {
                            items.removeClass('active');
                            $(items[index - 1]).addClass('active');
                        }
                    }
                }
                // Enter
                else if (e.keyCode === 13) {
                    e.preventDefault();
                    var active = dropdown.find('.active');
                    if (active.length > 0) {
                        active.trigger('click');
                    }
                }
                // Escape
                else if (e.keyCode === 27) {
                    dropdown.hide();
                }
            }
        });

        // Add some basic styling for the dropdown items
        $('<style>\
            #drug_search_dropdown .dropdown-item {\
                display: block;\
                padding: 8px 12px;\
                text-decoration: none;\
                color: #333;\
                border-bottom: 1px solid #eee;\
            }\
            #drug_search_dropdown .dropdown-item:hover,\
            #drug_search_dropdown .dropdown-item.active {\
                background-color: #f8f8f8;\
            }\
        </style>').appendTo('head');
    });
</script>

<script>
    $(document).ready(function() {
        var search_result = [];
        $('#typeahead_sample').typeahead({
            source: function(query, query_response) {

                $.ajax({
                    url: medication_controller_url,
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
                location.href = 'page99.php?choice=' + item
                return item
            }
        });

    });
</script>