// JavaScript to handle the visibility of the theater input field
document.getElementById('require_theater').addEventListener('change', function () {
    const theaterSection = document.getElementById('theater_section');
    if (this.value === 'Yes') {
        theaterSection.style.display = 'block';
    } else {
        theaterSection.style.display = 'none';
        document.getElementById('theater_select').value = '';
        document.getElementById('new_theater').style.display = 'none';
    }
});

document.getElementById('theater_select').addEventListener('change', function () {
    const newTheaterInput = document.getElementById('new_theater');
    if (this.value === 'Other') {
        newTheaterInput.style.display = 'block';
    } else {
        newTheaterInput.style.display = 'none';
    }
});

// JavaScript to handle the visibility of the theater input field
document.getElementById('require_theater_2').addEventListener('change', function () {
    const theaterSection = document.getElementById('theater_section_2');
    if (this.value === 'Yes') {
        theaterSection.style.display = 'block';
    } else {
        theaterSection.style.display = 'none';
        document.getElementById('theater_select_2').value = '';
        document.getElementById('new_theater_2').style.display = 'none';
    }
});

document.getElementById('theater_select_2').addEventListener('change', function () {
    const newTheaterInput = document.getElementById('new_theater_2');
    if (this.value === 'Other') {
        newTheaterInput.style.display = 'block';
    } else {
        newTheaterInput.style.display = 'none';
    }
});

// follow up procedure modal
document.addEventListener('DOMContentLoaded', function () {
    // Get the button that opens the modal
    var btn = document.querySelector(
        '[data-toggle="modal"][data-target="#bookFolloUpProcedureModal"]',
    );

    // Add event listener to the button
    btn.addEventListener('click', function () {
        // Get values from button's data attributes
        var patientId = this.getAttribute('data-_follow_up_patient_id');
        var procedureName = this.getAttribute('data-_old_procedure_name');
        var procedureId = this.getAttribute('data-_old_procedure_id');

        // Set values to the hidden fields in the modal
        document.getElementById('_follow_up_patient_id').value = patientId;
        document.getElementById('_old_procedure_id').value = procedureId;
        document.getElementById('_old_procedure_name').value = procedureName;

        // Also update the visible patient ID text
        document.getElementById('_follow_up_patient_id_text').textContent = patientId;
    });
});

$(document).ready(function () {
    $('.chosen-select').chosen();
    $('#category_of_procedure').change(function () {
        var category = $(this).val();
        var procedureList = $('#procedure_list');
        procedureList.empty();
        procedureList.append('<option value="">Select</option>'); // Add default option

        // If no category is selected, fetch all procedures
        if (category === '') {
            $.ajax({
                url: '../inc/fetch_procedures.php',
                method: 'GET',
                data: {
                    category: 'all',
                },
                success: function (data) {
                    var procedures = JSON.parse(data);
                    $.each(procedures, function (index, procedure) {
                        var priceText = procedure.ext_price ? ' - N' + procedure.ext_price : '';
                        procedureList.append(
                            '<option value="' +
                                procedure.sn +
                                '">' +
                                procedure.item_service +
                                priceText +
                                '</option>',
                        );
                    });
                    // Re-initialize Chosen after adding new options
                    procedureList.trigger('chosen:updated');
                },
            });
        } else {
            // Fetch procedures based on selected category
            $.ajax({
                url: '../inc/fetch_procedures.php',
                method: 'GET',
                data: {
                    category: category,
                },
                success: function (data) {
                    var procedures = JSON.parse(data);
                    $.each(procedures, function (index, procedure) {
                        var priceText = procedure.ext_price ? ' - N' + procedure.ext_price : '';
                        procedureList.append(
                            '<option value="' +
                                procedure.sn +
                                '">' +
                                procedure.item_service +
                                priceText +
                                '</option>',
                        );
                    });
                    // Re-initialize Chosen after adding new options
                    procedureList.trigger('chosen:updated');
                },
            });
        }
    });

    // Trigger change event on page load to populate the procedure list if needed
    $('#category_of_procedure').trigger('change');
});

$(document).ready(function () {
    var search_specialist_result = [];
    $('#typeahead_search_specialist').typeahead({
        source: function (query, query_response) {
            $.ajax({
                url: '../inc/specialist.php',
                method: 'POST',
                data: {
                    search_specialist: true,
                    action: 'search_specialist',
                    input_text: $('#typeahead_search_specialist').val(),
                },
                dataType: 'json',
                success: function (data) {
                    search_specialist_result = data;
                    query_response(
                        $.map(data, function (item) {
                            return item.name;
                        }),
                    );
                },
            });
        },
        updater: function (item) {
            search_specialist_result.forEach((element) => {
                if (element.name == item) {
                    $('#typeahead_search_specialist_id').val(element.id);
                    $('#typeahead_search_specialist_name').val(element.name);
                    return item;
                }
            });
            return item;
        },
    });

    //follow up procedure
    var search_specialist_result = [];
    $('#typeahead_search_specialist_2').typeahead({
        source: function (query, query_response) {
            $.ajax({
                url: '../inc/specialist.php',
                method: 'POST',
                data: {
                    search_specialist: true,
                    action: 'search_specialist',
                    input_text: $('#typeahead_search_specialist_2').val(),
                },
                dataType: 'json',
                success: function (data) {
                    search_specialist_result = data;
                    query_response(
                        $.map(data, function (item) {
                            return item.name;
                        }),
                    );
                },
            });
        },
        updater: function (item) {
            search_specialist_result.forEach((element) => {
                if (element.name == item) {
                    $('#typeahead_search_specialist_id_2').val(element.id);
                    $('#typeahead_search_specialist_name_2').val(element.name);
                    return item;
                }
            });
            return item;
        },
    });
});
