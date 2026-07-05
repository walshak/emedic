<?php
include_once('opthalmology/_opthalmology_modal.php');
?>
<script>
    var opth_list = <?php echo json_encode($opth_list); ?>;
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;
    var vaccines = <?php echo json_encode($vaccines); ?>;
    var ocular_hx_id = null;
    var view_mode = null;

    $(document).ready(function() {
        $('#opthalmology-modal').modal('show');


        $('#result_comment_form').on('submit', function(ev) {
            ev.preventDefault();

            let data = {
                hospital_no: hospital_no,
                appointment_number: appointment_number,
                sn: ocular_hx_id,
                view_mode: view_mode,
                ocular_result: $('#ocular_result').val(),
                left: $('#LeftLeft').val(),
                right: $('#RightRight').val(),
                action: 'saveOpthResult'
            };
            $.ajax({
                url: 'controllers/_opthalmology.php',
                type: "POST",
                data: data,
                success: function(response) {

                    console.log(response)
                    if (response.status == 200) {
                        toastr.success('Saved successfully...', 'Success', {
                            timeOut: 3000
                        });

                        if (data.left == '') {
                            var html_ = data.ocular_result;
                        } else {
                            var html_ = data.ocular_result + ' Left: ' + data.left + ' Right: ' + data.right;
                        }

                        $('#ocular_result_' + data.sn).html(html_)
                    }
                },
                error: function(response) {
                    console.log(response)
                }
            });

        });

        $('#result_comment_form_two').on('submit', function(ev) {
            ev.preventDefault();
            alert(hospital_no)

        });


    })




    function closeModal(modal_id) {
        $('#' + modal_id).modal('hide');
    }


    function openOcularExamination() {
        //buttons
        $('#ocular_hx_btn').fadeIn('slow');
        $('#ocular_examination_btn').fadeOut('slow');
        ///div
        $('#ocular_hx_div').fadeOut('fast');
        $('#ocular_examination_div').fadeIn('slow');

    }

    function openOcularHx() {
        //buttons
        $('#ocular_hx_btn').fadeOut('slow');
        $('#ocular_examination_btn').fadeIn('slow');
        ///div
        $('#ocular_hx_div').fadeIn('fast');
        $('#ocular_examination_div').fadeOut('slow');

    }





    function getOpthDetails(sn) {

        opth_list.forEach(element => {

            if (element.sn == sn) {
                return element;
                alert('found!')
            }
        });

        return null;
    }

    function openResult(sn) {

        // let opth = opth_list[index];
        let opth = null;
        opth_list.forEach(element => {

            if (element.sn == sn) {
                opth = element;
            }
        });

     

        if (opth == null) {
            alert('Record not found!')
        } else {


            ocular_hx_id = opth.sn
            view_mode = opth.view_mode

            let data = {
                hospital_no: hospital_no,
                appointment_number: appointment_number,
                sn: opth.sn,
                action: 'loadResultForm'
            };
            $.ajax({
                url: 'controllers/_opthalmology.php',
                type: "POST",
                data: data,
                success: function(response) {

                    console.log(response)
                    $('#opthalmology_result_modal').modal('show');
                    $('#opthalmology_result_heading').html('Provide History/result for <br/>' + opth.ocular_list)
                    $('#result_comment_form').html(response.body);
                },
                error: function(response) {
                    console.log(response)
                }
            });


        }


    }
</script>