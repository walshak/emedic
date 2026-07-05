<?php
include_once('paediatric/_paediatric_modal.php');
?>
<script>
    $(document).ready(function() {
        $('#paediatric-modal').modal('show');

        $('#paediatric_history_form').on('submit', function(ev) {
            ev.preventDefault();

            toastr.info('Saving,please wait..', 'Saving', {
                timeOut: 3000
            })
            $.ajax({
                url: 'controllers/_paediatrics.php',
                type: "POST",
                data: $('#paediatric_history_form').serialize(),
                success: function(response) {

                    console.log(response)
                    if (response.status == 200) {
                        toastr.success('Completed successfully...', 'Success', {
                            timeOut: 3000
                        });
                        load_paediatric_complains_and_medication(response.hospital_no);
                        $('#paediatric_initial_hx_btn').fadeIn('slow');
                    }
                },
                error: function(response) {
                    console.log(response)
                }
            });

        })


        $('#paediatric_followup_form').on('submit', function(ev) {
            ev.preventDefault();
            var hospital_no =  $('hospital_no').val();
            if ($('#PC').val() == '') {
               alert('Please enter complain')
            } else {
                toastr.info('Saving,please wait..', 'Saving', {
                    timeOut: 3000
                })
                $.ajax({
                    url: 'controllers/_paediatrics.php',
                    type: "POST",
                    data: $('#paediatric_followup_form').serialize(),
                    success: function(response) {

                        console.log(response)
                        if (response.status == 200) {
                            toastr.success('Completed successfully...', 'Success', {
                                timeOut: 3000
                            });

                            load_paediatric_sumary(hospital_no);
                        }
                    },
                    error: function(response) {
                        console.log(response)
                    }
                });
            }

        });


    })


    function saveData(){
        var hospital_no =  $('#hospital_no').val();
            if ($('#PC').val() == '') {
               alert('Please enter complain')
            } else {
                toastr.info('Saving,please wait..', 'Saving', {
                    timeOut: 3000
                })
                $.ajax({
                    url: 'controllers/_paediatrics.php',
                    type: "POST",
                    data: $('#paediatric_followup_form').serialize(),
                    success: function(response) {

                        console.log(response)
                        if (response.status == 200) {
                            toastr.success('Completed successfully...', 'Success', {
                                timeOut: 3000
                            });

                            load_paediatric_sumary(hospital_no);
                        }
                    },
                    error: function(response) {
                        console.log(response)
                    }
                });
            }

    }

    function load_paediatric_sumary(hospital_no) {
        var appointment_number = $('#appointment_number').val();
        $.ajax({
            url: 'paediatric/_paediatric_summary.php',
            type: "POST",
            data: {
                hospital_no: hospital_no,
                appointment_number:appointment_number
            },
            success: function(response) {
                $('#paediatric_modal_boday').html(response);
            },
            error: function(response) {
                console.log(response)
            }
        });
    }


    function load_paediatric_complains_and_medication(hospital_no) {
        var appointment_number = $('#appointment_number').val();
        $.ajax({
            url: 'paediatric/_paediatric_complains_and_medication.php',
            type: "POST",
            data: {
                hospital_no: hospital_no,
                appointment_number:appointment_number
            },
            success: function(response) {
                $('#paediatric_modal_boday').html(response);
                $(".chosen-select").chosen({width: "95%"});
                $(".chosen-select").trigger("chosen:updated");
            },
            error: function(response) {
                console.log(response)
            }
        });
    }

    function closeModal(modal_id) {
        $('#' + modal_id).modal('hide');
    }
</script>