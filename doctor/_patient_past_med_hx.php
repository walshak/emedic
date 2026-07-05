<div class='modal inmodal fade' id='____past_med_hx_modal____' tabindex='-1' role='dialog' aria-hidden='true' data-keyboard='false'>
    <div class='modal-dialog modal-lg' style='width: 60%;'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
                <h4 class='modal-title'>Add/Edit Patient Past Medical History</h4>
            </div>
            <div class='modal-body' style='min-height: 200px'>
                <div id="____pmh_modal_body____">

                    <!-- Diagnosis Searchable Dropdown -->
                    <label for="pmh_diagnosis">Diagnosis</label>
                    <select class="form-control" id="pmh_diagnosis" style="width:100%;">
                        <option value="">-- Search Diagnosis --</option>
                    </select>


                    <textarea class="typeahead form-control" data-provide="typeahead" id="search_icdcodes_input" name="search_icdcodes_input" cols="30" rows="1" style="font-size:17px" placeholder="Enter Diagnosis Here"></textarea>


                    <br>

                    <!-- Free comment box -->
                    <label for="pmh_notes">Additional Notes</label>
                    <textarea class="form-control" id="pmh_notes" rows="4" placeholder="Enter extra details..."></textarea>




                    <div class="text-center">
                        <input type="hidden" id="pmh_sn" value="">
                        <input type="hidden" id="pmh_hospital_no" value="<?= $hospital_no; ?>">
                        <input type="hidden" id="pmh_app_no" value="<?= $appointment_number; ?>">
                        <button class="btn btn-primary" onclick="savePMH()">Save</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>

                    <hr>

                    <div id="____pmh_wrap____">
                        <p class="text-muted">Loading...</p>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>


<script>
    var sh_hospital_no = "<?php echo $hospital_no; ?>";
    var sh_app_no = "<?php echo $appointment_number; ?>";

    $(document).on('click', '#____add_past_med_hx_btn____', function() {

        $('#____past_med_hx_modal____').modal('show')
        $('#____social_hx_wrap____').html('<h6 class="text-center text-danger"> Loading, please wait...</h6>')


        load_pmh();

        $.ajax({
            url: '_patient_social_hx.php',
            method: "POST",
            data: {
                loadSocialHx: true,
                hospital_no: sh_hospital_no,
                app_no: sh_app_no
            },
            success: function(response) {

                $('#sh_notes').html(response.social_hx)
                refreshEditor('sh_notes')
                $('#sh_sn').val(response.sh_sn)



            },
            error: function(err) {
                console.log(err)
            }
        });


    });



    $(document).ready(function() {

        $('#____past_med_hx_modal____').on('shown.bs.modal', function() {

            if (!$('#pmh_diagnosis').hasClass("select2-hidden-accessible")) {

                $('#pmh_diagnosis').select2({
                    placeholder: "Search diagnosis...",
                    allowClear: true,
                    width: '100%',
                    minimumInputLength: 2,
                    dropdownParent: $('#____past_med_hx_modal____'),
                    ajax: {
                        url: '_search_diagnosis.php',
                        type: 'POST',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                search: params.term
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

            }

        });

    });



    // Save PMH
    function savePMH() {
        let diagnosis_id = $('#pmh_diagnosis').val();
        let diagnosis_text = $('#pmh_diagnosis option:selected').text();
        let notes = $('#pmh_notes').val();


        if (!diagnosis_id && notes.trim().length < 5) {
            alert('Please select a diagnosis or enter notes.');
            return;
        }


        $.ajax({
            url: '_patient_pmh.php',
            method: "POST",
            data: {
                savePMH: true,
                diagnosis_id: diagnosis_id,
                diagnosis_text: diagnosis_text,
                pmh_notes: notes,
                pmh_sn: $('#pmh_sn').val(),
                app_no: $('#pmh_app_no').val(),
                hospital_no: $('#pmh_hospital_no').val()
            },
            success: function(response) {
                alert(response.message);
                //  $("#____pmh_wrap____").html(response.pmh_html);
                load_pmh()
                $('#pmh_diagnosis').val(null).trigger('change');
                $('#pmh_notes').val('');
            },
            error: function(err) {
                console.log(err);


            }
        });
    }



    function load_pmh() {
        var sh_hospital_no = "<?php echo $hospital_no; ?>";
        var sh_app_no = "<?php echo $appointment_number; ?>";

        $.ajax({
            url: '_patient_pmh.php',
            method: "POST",
            data: {
                loadPMH: true,
                app_no: sh_app_no,
                hospital_no: sh_hospital_no
            },
            success: function(response) {
                // Instead of alert, inject the HTML into your container
                $("#____pmh_wrap____").html(response);
            },

            error: function(xhr, status, error) {
                console.log("XHR:", xhr.responseText);
                console.log("Status:", status);
                console.log("Error:", error);
                alert("PMH load failed: " + error);

            }
        });
    }


    function deletePMH(sn) {
        if (!confirm("Are you sure you want to delete this record?")) return;

        $.ajax({
            url: "_patient_pmh.php",
            method: "POST",
            data: {
                deletePMH: true,
                sn: sn
            },
            success: function(response) {
                // Reload the PMH table after deletion
                load_pmh();
            },
            error: function(err) {
                console.log(err);
                alert("Error deleting PMH record.");
            }
        });
    }
</script>