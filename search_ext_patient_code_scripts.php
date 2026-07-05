<script>
    document.getElementById('ex_search').addEventListener('input', function() {
        const query = this.value.trim();
        const searchResults = document.getElementById('search_results');
        const selectedDetails = document.getElementById('selected_patient_details');
        const formContainer = document.getElementById('patient_form_container');

        if (query.length >= 2) {
            fetch(`../search_ext_patient_code.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success" && data.data.length > 0) {
                        let resultsHtml = '<ul class="list-group">';
                        data.data.forEach(patient => {
                            resultsHtml += `
                                <li class="list-group-item" data-cust_name="${patient.cust_name}" 
                                    data-transc_code="${patient.transc_code}" 
                                    data-hospital_no="${patient.transc_code}" 
                                    data-dob="${patient.dob}" 
                                    data-gender="${patient.gender}" 
                                    data-appointment_no="${patient.transc_code}"
                                >
                                    <strong>Name:</strong> ${patient.cust_name}<br>
                                    <strong>EX No.:</strong> ${patient.transc_code}
                                </li>`;
                        });
                        resultsHtml += '</ul>';
                        searchResults.innerHTML = resultsHtml;

                        document.querySelectorAll('#search_results .list-group-item').forEach(item => {
                            item.addEventListener('click', function() {

                                searchResults.innerHTML = "";

                                const name = this.dataset.cust_name;
                                const code = this.dataset.transc_code;
                                const gender = this.dataset.gender;
                                const dob = this.dataset.dob;
                                const hospitalNo = this.dataset.hospital_no;
                                const appointmentNo = this.dataset.appointment_no;

                                selectedDetails.innerHTML = `
                                    <h5>Selected Patient Details:</h5>
                                    <p><strong>Name:</strong> ${name}</p>
                                    <p><strong>EX No.:</strong> ${code}</p>
                                    <p><strong>Gender.:</strong> ${gender}</p>
                                    <p><strong>DOB.:</strong> ${dob}</p>
                                `;

                                // Populate and show the form
                                formContainer.style.display = 'block';
                                formContainer.innerHTML = `
                                    <form action="" id="remita_form2" name="remita_form2" method="POST">
                                        <div class="form-group">
                                            <h4>Enter Notes/Document below:</h4>
                                            <div id="edit__mode" style="color: red;"></div>
                                            <textarea name="selected_review_note" class="form-control" cols="45" rows="5" placeholder="" style="font-size:18px" id="selected_review_note" required></textarea>
                                        </div>
                                        <input type="hidden" name="hospital_no" id="hospital_no" value="${hospitalNo}">
                                        <input type="hidden" name="appointment_number" id="appointment_number" value="${appointmentNo}">
                                        <input type="hidden" name="mode" id="mode" value="new">
                                        <input type="hidden" name="notes_sn" id="notes_sn" value="">
                                        <input type="hidden" name="dept_id" id="dept_id" value="<?= $_SESSION['dept_id']; ?>">
                                        <button type="button" class="btn btn-sm btn-success" id="pay_now" name="pay_now" onclick="saveNote()">Save Notes</button>
        
                                    </form>
                                `;

                                //load existing notes. 
                                triggerLoadNotes();
                            });
                        });
                    } else {
                        searchResults.innerHTML = `<p>No results found</p>`;
                        selectedDetails.innerHTML = '';
                        formContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    searchResults.innerHTML = `<p>Error fetching data</p>`;
                });
        } else {
            searchResults.innerHTML = `<p>Start typing to search for patients...</p>`;
            selectedDetails.innerHTML = '';
            formContainer.style.display = 'none';
        }
    });

    // function triggerForm(hospitalNo, appointmentNo) {
    //     const formContainer = document.getElementById('patient_form_container');

    //     // Populate and show the form
    //     formContainer.style.display = 'block';
    //     formContainer.innerHTML = `
    //         <form action="" id="remita_form2" name="remita_form2" method="POST">
    //             <div class="form-group">
    //                 <h4>Enter Notes/Document below:</h4>
    //                 <div id="edit__mode" style="color: red;"></div>
    //                 <textarea name="selected_review_note" class="form-control" cols="45" rows="5" placeholder="" style="font-size:18px" id="selected_review_note" required></textarea>
    //             </div>
    //             <input type="hidden" name="hospital_no" id="hospital_no" value="${hospitalNo}">
    //             <input type="hidden" name="appointment_number" id="appointment_number" value="${appointmentNo}">
    //             <input type="hidden" name="mode" id="mode" value="new">
    //             <input type="hidden" name="notes_sn" id="notes_sn" value="">
    //             <input type="hidden" name="dept_id" id="dept_id" value="<?= $_SESSION['dept_id']; ?>">
    //             <button type="button" class="btn btn-sm btn-success" id="pay_now" name="pay_now" onclick="saveNote()">Add Notes</button>

    //         </form>
    //     `;

    //     //load existing notes. 
    //     triggerLoadNotes();
    // }

    function loadNotes(hospitalNo, appointmentNo) {
        $.ajax({
            url: '../search_ext_patient_code.php?get_notes',
            type: 'POST',
            data: {
                hospital_no: hospitalNo,
                appointment_no: appointmentNo
            },
            dataType: 'json',
            success: function(response) {
                const tableBody = $('#notes_table tbody');
                tableBody.empty();

                if (response.status === 'success') {
                    response.data.forEach((note, index) => {
                        const canEdit = (note.status == 1 && note.prepared_by == '<?php echo $_SESSION['fullname'] ?>' && dateDiffInDays(note.date_entry) <= 1);

                        const actions = canEdit ?
                            `
                            <button class="btn btn-sm btn-primary" onclick="editNote(${note.sn}, '${note.notes}')">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteNote(${note.sn})">Delete</button>
                          ` :
                            '';

                        tableBody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${note.prepared_by}</td>
                            <td>${note.date_entry}</td>
                            <td>${note.notes}</td>
                            <td>${actions}</td>
                        </tr>
                    `);
                    });
                    toastr.success('Success', 'Notes loaded successfully', {
                        timeOut: 5000
                    });
                } else {
                    tableBody.append('<tr><td colspan="4">No notes found.</td></tr>');
                }
            },
            error: function() {
                toastr.error('Error', 'Failed to load notes', {
                    timeOut: 5000
                });
            }
        });
    }

    /**
     * Helper function to calculate the difference in days between today and a given date.
     * @param {string} dateTime - The date string to compare.
     * @returns {number} - The difference in days.
     */
    function dateDiffInDays(dateTime) {
        const noteDate = new Date(dateTime);
        const today = new Date();
        const diffTime = today - noteDate;
        return Math.floor(diffTime / (1000 * 60 * 60 * 24)); // Convert ms to days
    }


    function loadPharmacyDrugs(hospitalNo, appointmentNo) {
        $.ajax({
            url: '../search_ext_patient_code.php?get_pharmacy_drugs',
            type: 'GET',
            data: {
                hospital_no: hospitalNo,
                appointment_no: appointmentNo
            },
            success: function(response) {
                const drugs = JSON.parse(response);
                const tableBody = $('#pharmacy_drugs_table tbody');
                tableBody.empty();

                if (drugs.length === 0) {
                    tableBody.append('<tr><td colspan="6">No drugs found for this patient.</td></tr>');
                } else {
                    drugs.forEach((drug, index) => {
                        // Badge for paystatus
                        let payStatusBadge;
                        if (drug.paystatus === 1) {
                            payStatusBadge = '<span class="badge badge-success">Paid</span>';
                        } else if (drug.paystatus === 0) {
                            payStatusBadge = '<span class="badge badge-danger">Unpaid</span>';
                        } else {
                            payStatusBadge = '<span class="badge badge-warning">Unknown</span>';
                        }

                        const invoicedInfo = drug.invoice_date ?
                            `${drug.invoice_date} by ${drug.invoice_by || 'Unknown'} ${payStatusBadge}` :
                            'Not Invoiced';

                        const preparedInfo = drug.prepared_by ?
                            `Prepared by ${drug.prepared_by}` :
                            'Not Prepared';

                        const dispensedInfo = drug.dsp_by ?
                            `Dispensed by ${drug.dsp_by}` :
                            'Not Dispensed';

                        tableBody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${drug.date_entry || 'N/A'}</td>
                            <td>${drug.item_services || 'N/A'}</td>
                            <td>${drug.qty || 'N/A'}</td>
                            <td>${invoicedInfo}</td>
                            <td>${preparedInfo}</td>
                            <td>${dispensedInfo}</td>
                        </tr>
                    `);
                    });
                }
            },
            error: function() {
                toastr.error('Error', 'Failed to load drugs', {
                    timeOut: 5000
                });
            }
        });
    }


    function loadLabResults(hospitalNo, appointmentNo) {
        $.ajax({
            url: '../search_ext_patient_code.php?get_lab_results',
            type: 'GET',
            data: {
                hospital_no: hospitalNo,
                appointment_no: appointmentNo
            },
            success: function(response) {
                const results = JSON.parse(response);
                const tableBody = $('#lab_results_table tbody');
                tableBody.empty();

                if (results.length === 0) {
                    tableBody.append('<tr><td colspan="8">No lab results found for this patient.</td></tr>');
                } else {
                    results.forEach((result, index) => {
                        tableBody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${result.item_services}</td>
                        <td>${result.field_value || 'N/A'}</td>
                        <td>${result.specimen_collected || ''}</td>
                        <td>${result.notes || ''}</td>
                        <td>${result.result_date || result.date_entry}</td>
                        <td>${result.prepared_by || 'N/A'}</td> <!-- Requester (Prepared By) -->
                        <td>${result.lab_sci_name || 'N/A'}</td> <!-- Results Entered By -->
                    </tr>
                    `);
                    });
                }
            },
            error: function() {
                toastr.error('Error', 'Failed to load Investigations', {
                    timeOut: 5000
                });
            }
        });
    }



    function saveNote() {
        const formData = {
            review_note: $('#selected_review_note').val(),
            hospital_no: $('#hospital_no').val(),
            appointment_number: $('#appointment_number').val(),
            dept_id: $('#dept_id').val(),
            mode: $('#mode').val(),
            notes_sn: $('#notes_sn').val()
        };

        $.ajax({
            url: '../search_ext_patient_code.php?post_notes',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 1) {
                    toastr.success('Success', 'Notes saved', {
                        timeOut: 5000
                    });
                    triggerLoadNotes();
                    $('#selected_review_note').val('');
                    $('#mode').val('new'); //sitch back to new, so that the next note will not be posted as an edit
                } else {
                    toastr.error('Error', 'Failed to save notes', {
                        timeOut: 5000
                    });
                }
            },
            error: function() {
                toastr.error('Error', 'Error saving note', {
                    timeOut: 5000
                });
            }
        });
    }

    function editNote(sn, noteText) {
        $('#selected_review_note').val(noteText);
        $('#mode').val('edit');
        $('#notes_sn').val(sn);
    }

    function deleteNote(sn) {
        if (!confirm('Are you sure you want to delete this note?')) return;

        $.ajax({
            url: '../search_ext_patient_code.php?delete_post',
            type: 'POST',
            data: {
                delete_notes: sn
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === '0') {
                    toastr.success('Success', 'Note deleted', {
                        timeOut: 5000
                    });
                    loadNotes($('#hospital_no').val(), $('#appointment_number').val());
                } else {
                    toastr.error('Error', 'Fsiled to delete note', {
                        timeOut: 5000
                    });
                }
            },
            error: function() {
                toastr.error('error', 'failed to delete note', {
                    timeOut: 5000
                });
            }
        });
    }

    // Trigger saveNote when the "Add Notes" button is clicked
    // $('#pay_now').on('click', saveNote);
</script>