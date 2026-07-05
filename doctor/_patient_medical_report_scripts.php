<script>
     $(document).on('click', '.med_report_div_btn', function() {
         let id = $(this).attr('arial-data');
            // $('.med_report_div_wrap').hide('slow');
            // $('#'+id).show('fast');

            if(id == 'draft-med-report'){
                load_med_report_drafts();
            }else if(id == 'compose-med-report'){
                reset_med_report();
            }
            else if(id == 'sent-med-report'){
                // $('.med_report_div_wrap').hide('slow');
                //  $('#'+id).show('fast');
                load_med_report_sent();
            }
            
         
        });

         $(document).on('click', '#close-copy-from-med-hx-btn', function() { 
              $('#copy-from-med-hx-wrap').hide('slow')
                  $('#open-copy-from-med-hx-btn').show('slow')
                  $('#close-copy-from-med-hx-btn').hide('slow')
         })

         $(document).on('click', '#open-copy-from-med-hx-btn', function() { 
              $('#copy-from-med-hx-wrap').show('slow')
                  $('#open-copy-from-med-hx-btn').hide('slow')
                  $('#close-copy-from-med-hx-btn').show('slow')
         })

          $(document).on('click', '#reset-med-report-btn', function() { 
                 reset_med_report()
         })

         function reset_med_report(){
             $('#copy-from-med-hx-btn').show('slow')
                  $('#open-copy-from-med-hx-btn').hide('slow')
                  $('#close-copy-from-med-hx-btn').hide('slow')

                  $('.med_report_div_wrap').hide('slow');
                   $('#compose-med-report').show('slow')
                  
                //editted
         }

        
         function load_med_report_drafts(){
            $('.med_report_div_wrap').hide('slow');
            $('#draft-med-report').show('slow');
            $('#draft-med-report-status').html('<h2 class="text-center text-danger">Loading, Please wait....</h2>')
               $.ajax({
                url: "_patient_med_report_drafts.php",
                method: "POST",
                data: {
                    load_med_report_drafts: true,
                    hospital_no: '<?php echo $hospital_no; ?>'
                },success: function(response) {
                     $('#draft-med-report-status').html('')
                     $('#draft-med-report-content').html(response)
                },
                error: function(error){
                    console.log(error)
                }
            });
         }

         function get_med_report_counter(){
               $.ajax({
                url: "_patient_med_report_ajax.php",
                method: "POST",
                data: {
                    get_med_report_counter: true,
                    hospital_no: '<?php echo $hospital_no; ?>'
                },success: function(response) {
                    console.log(response)
                     $('#sent_counter').text(response.sent)
                     $('#draft_counter').text(response.draft)
                },
                error: function(error){
                    console.log(error)
                }
            });
         }

          function load_med_report_sent(){
            $('.med_report_div_wrap').hide('slow');
            $('#sent-med-report').show('slow');
            $('#sent-med-report-status').html('<h2 class="text-center text-danger">Loading, Please wait....</h2>')
               $.ajax({
                url: "_patient_med_report_sent.php",
                method: "POST",
                data: {
                    load_med_report_sent: true,
                    hospital_no: '<?php echo $hospital_no; ?>'
                },success: function(response) {
                     $('#sent-med-report-status').html('')
                     $('#sent-med-report-content').html(response)
                },
                error: function(error){
                    console.log(error)
                }
            });
         }
         
        
         $(document).on('click', '#copy-from-med-hx-btn', function() {
              $('#copy-from-med-hx-wrap').html('<h2 class="text-center text-danger">Loading, Please wait....</h2>')
                   $.ajax({
                url: "_patient_med_report_ajax.php",
                method: "POST",
                data: {
                    copy_from_med_hx: true,
                    hospital_no: '<?php echo $hospital_no; ?>'
                },
                success: function(response) {
                   
                   $('#copy-from-med-hx-wrap').html(response)
                    $('#close-copy-from-med-hx-btn').show('slow')
                    $('#open-copy-from-med-hx-btn').hide('slow')
                    $('#copy-from-med-hx-btn').hide('slow')
                },
                error: function(error){
                    $('#close-copy-from-med-hx-btn').hide('slow')
                        $('#open-copy-from-med-hx-btn').hide('slow')
                    $('.med_report_div_wrap').hide('Eroor loading medical hx');
                    console.log(error)
                }
            });
        });

         $(document).on('click', '.copy-med-hx-button', function() { 
            let sn = $(this).attr('arial-data');  
            let html = $('#med-to-copy-content-'+sn).html();
            let editor_content_  = $('#med_report_notes').html();
            
            let new_content = editor_content_+''+html;
            $('#med_report_notes_main').val(new_content);
            $('#med-to-copy-wrap-'+sn).hide('slow')

          //editted
           
         })

         
        

        $(document).on('click', '#send-med-report-printing-btn', function(evt) { 
            $('#med_report_notes_action').val('save');
            $('#med_report_notes_form').submit();
        });

        $(document).on('click', '#save-med-report-as-draft-btn', function(evt) { 
               $('#med_report_notes_action').val('draft');
               $('#med_report_notes_form').submit();
        }) 


         function save_med_report_step_one(action){
              var formData = $('#med_report_notes_form').serializeArray();
               formData = $('#med_report_notes_form').serializeArray();
       
              console.log(formData)

              if(formData[0].value != ''){
                formData.push({ name: 'action',  value: action});
                formData.push({ name: 'save_med_report',  value: true});
                save_med_report(action);
              }
         }

        function save_med_report(action){
            //editted
              var formData = $('#med_report_notes_form').serializeArray();
              console.log(formData)

              if(formData[0].value != ''){
                formData.push({ name: 'action',  value: action});
                formData.push({ name: 'save_med_report',  value: true});

                    toastr.info('Saving, please wait...', 'Info', { timeOut: 10000  })
                    $.ajax({
                        url: "_patient_med_report_ajax.php",
                        method: "POST",
                        data: formData,
                        success: function(response) {
                            toastr.clear();
                            if(response.status == 200){
                                toastr.success(response.message, 'Sucess', { timeOut: 5000  })
                                reset_med_report()
                                get_med_report_counter()
                            }else{
                                toastr.error(response.message, 'Error', { timeOut: 5000  })
                            }
                        
                        }
                    });
              }
         }

          $(document).on('click', '.edit-med-report-button', function(evt) { 
            let id = $(this).attr('arial-data');
            toastr.info('Fetching data, please wait...', 'Info', { timeOut: 10000  })
            $.ajax({
                url: "_patient_med_report_ajax.php",
                method: "POST",
                data: {
                    get_med_report:true,
                    id:id
                },
                success: function(response) {
                    toastr.clear();
                    reset_med_report();
                    // console.log(response);
                    $('#med_report_notes').html(response);
                    $('#med_report_notes_main').val($('#med_report_notes').html(response));
                    $('#med_report_notes').trumbowygEditor();
                // Editted
               
                }
            });
         }); 

            $(document).on('click', '.delete-med-report-button', function(evt) { 
                let con = confirm('Are you sure you want to delete medical report?');
                    if(con == true){
                            let id = $(this).attr('arial-data');
                    toastr.info('Deleting, please wait...', 'Info', { timeOut: 10000  })
                    $.ajax({
                            url: "_patient_med_report_ajax.php",
                            method: "POST",
                            data: {
                                delete_med_report:true,
                                id:id
                            },
                            success: function(response) {
                                toastr.clear();
                                if(response.status == 200){
                                    toastr.info('Deleted', 'Success', { timeOut: 5000  })
                                    load_med_report_drafts();
                                    load_med_report_sent();
                                    get_med_report_counter()
                                }else{
                                    toastr.info(response.message, 'Success', { timeOut: 5000  })
                                }
                            
                            }
                        });
                    }
                
            });
              
</script>