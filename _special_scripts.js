

$(document).on('click', '.delete-ars-btn', function(evt){
    evt.preventDefault();
    
    let sn = $(this).attr('arial-sn');
    const elem = $(this);
    let app_no = $(this).attr('arial-app-no');
 let con = confirm('Do you really want to remove this service? ')
    if (con) {
        toastr.info('Removing, please wait...', 'Attention', {
            timeOut: 3000
        })
        $.ajax({
            url: "../_add_services.php",
            method: "POST",
            data: { action: 'delete_patient_review_service',sn: sn, app_no:app_no},
            success: function(response) {
                console.log(response);
                if (response.status == 200) {
                    elem.hide();
                    $('#edit-ars-btn-'+sn).hide();
                        toastr.success(response.message, 'Success', {
                        timeOut: 5000
                    })
                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 5000
                    })
                }
            },
            error: function(err){
                console.log(err)
            }
        });
    }
})

$(document).on('click', '.edit-ars-btn', function(evt){
    evt.preventDefault();
    let sn = $(this).attr('arial-sn');
    let app_no = $(this).attr('arial-app-no');
        toastr.info('Loading, please wait...', 'Attention', {
            timeOut: 3000
        })
        $.ajax({
            url: "../_add_services.php",
            method: "POST",
            data: { action: 'edit_patient_review_service',sn: sn, app_no:app_no},
            success: function(response) {
                console.log(response);
                if (response.status == 200) {
                    $("#serviceReviewModal").modal('hide')
                    $("#editserviceReviewModal").modal('show')
                    $("#edit_review_note").val(response.notes)
                    $("#review_service_note_id").val(response.id)
                    $("#review_service_app_no").val(app_no)
                    $("#review_service_id").val(sn)
                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 5000
                    })
                }
            },
            error: function(err){
                console.log(err)
            }
        });
    
})

$(document).on('click', '.close-edit-ars-btn', function(evt){
    $("#editserviceReviewModal").modal('hide')
    $("#serviceReviewModal").modal('show')
})

$(document).on('click', '.delete-added-services-btn', function(evt){
    evt.preventDefault();
    let sn = $(this).attr('arial-sn');
    const elem = $(this);
    let hospital_no = $(this).attr('arial-hosp-no');
    let con = confirm('Do you really want to remove this service? ')
    if (con) {
        toastr.info('Removing, please wait...', 'Attention', {
            timeOut: 3000
        })
        $.ajax({
            url: "../_add_services.php",
            method: "POST",
            data: { action: 'delete_patient_service',sn: sn, hospital_no:hospital_no},
            success: function(response) {
                console.log(response);
                if (response.status == 200) {
                    elem.hide();
                        toastr.success(response.message, 'Success', {
                        timeOut: 5000
                    })
                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 5000
                    })
                }
            },
            error: function(err){
                console.log(err)
            }
        });
    }
})


$(document).on('click', '.update_service_review_btn', function(evt){
    evt.preventDefault();

    let notes = $("#edit_review_note").val();
    let sn = $("#review_service_note_id").val();
    let app_no = $("#review_service_app_no").val();
    let service_id = $("#review_service_id").val();

        toastr.info('Saving, please wait...', 'Attention', {
            timeOut: 3000
        })
        $.ajax({
            url: "../_add_services.php",
            method: "POST",
            data: { action: 'update_service_review',sn: sn, service_id:service_id, app_no:app_no,notes:notes},
            success: function(response) {
                console.log(response);
                if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                        timeOut: 5000
                    })
                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 5000
                    })
                }
            },
            error: function(err){
                console.log(err)
            }
        });
    
})



            $(document).on('click', '.edit-alert-btn', function() {
                let id = $(this).attr('arial-data');
                toastr.info('Loading, please wait...', 'Info', {
                    timeOut: 10000
                })

                $.ajax({
                    url: "controllers/_patient_alert.php",
                    method: "POST",
                    data: {
                        id: id,
                        load_edit: true
                    },
                    success: function(response) {
                        toastr.clear();
                        $('#patient-edit-alert').show('slow');
                        $('#patient-alerts').hide('slow');
                        $('#edit_alert_id').val(id);
                        $('#patient-alert-notes-edit').val(response.data);
                    }
                });

            });
            
            $(document).on('click', '.patient-other-charts-btn', function() {
                $('#patient-other-charts-modal').modal('show');
            });

            $(document).on('click', '.delete-alert-btn', function() {
                let id = $(this).attr('arial-data');

                if (confirm('Are you sure you want to delete this alert?')) {


                    toastr.info('Deleting, please wait...', 'Info', {
                        timeOut: 10000
                    })

                    $.ajax({
                        url: "controllers/_patient_alert.php",
                        method: "POST",
                        data: {
                            id: id,
                            hospital_no: $('#patient_alert_hospital_no').val(),
                            delete: true
                        },
                        success: function(response) {
                            toastr.clear();
                            $('#patient-alert-tbody').html(response.data);
                        }
                    });
                }

            });

            $(document).on('click', '#cancel-new-alert', function() {
                $('#patient-new-alert').hide('slow');
                $('#patient-edit-alert').hide('slow');
                $('#patient-alerts').show('slow');
            });

            $(document).on('click', '#new-alert-note-btn', function() {
                $('#patient-new-alert').show('slow');
                $('#patient-alerts').hide('slow');
            });




            $(document).on('click', '#update-patient-alert', function() {
                var alert_note = $('#patient-alert-notes-edit').val();
                var id = $('#edit_alert_id').val();
                if (alert_note != '') {
                    toastr.info('Saving, please wait...', 'Info', {
                        timeOut: 10000
                    })

                    $.ajax({
                        url: "controllers/_patient_alert.php",
                        method: "POST",
                        data: {
                            alert: alert_note,
                            id: id,
                            hospital_no: $('#patient_alert_hospital_no').val(),
                            update_alert: true
                        },
                        success: function(response) {
                            toastr.clear();
                            if (response.status == 200) {
                                toastr.success(response.message, 'Sucess', {
                                    timeOut: 5000
                                })
                                $('#patient-new-alert').slideUp('slow');
                                $('#patient-edit-alert').slideUp('slow');
                                $('#patient-alerts').slideDown('slow');
                                $('#patient-alert-tbody').html(response.data);
                                $('#patient-alert-notes-edit').val("");

                            } else {
                                toastr.error(response.message, 'Error', {
                                    timeOut: 5000
                                })
                            }

                        }
                    });

                }
            });




            $(document).on('click', '#save-patient-alert', function() {
                var alert_note = $('#patient-alert-notes').val();
           

					alert(alert_note);
				
                    $.ajax({
                        url: "controllers/_patient_alert.php",
                        method: "POST",
                        data: {
                            alert: alert_note,
                            hospital_no: $('#patient_alert_hospital_no').val(),
                            save: true
                        },
                        success: function(response) {
                            toastr.clear();
                            if (response.status == 200) {
                                toastr.success(response.message, 'Sucess', {
                                    timeOut: 5000
                                })
                                $('#patient-new-alert').slideUp('slow');
                                $('#patient-alerts').slideDown('slow');
                                $('#patient-alert-tbody').html(response.data);
                                $('#patient-alert-notes').val("");

                            } else {
                                toastr.error(response.message, 'Error', {
                                    timeOut: 5000
                                })
                            }

                        }
                    });
                }
            });
            $(document).on('click', '.opt-note-btn', function() {
                $('#' + $(this).attr('arial-modal')).modal('show')
            });




