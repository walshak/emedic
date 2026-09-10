<script>
     $(document).on('click', '.edit_lab', function() {
          var edit_lab_id = $(this).attr("id");
          var res = edit_lab_id.split("__");

          $.ajax({
               url: "fetch_labtest.php",
               method: "POST",
               data: {
                    edit_lab_id: res[0]
               },
               dataType: "json",
               success: function(data) {

                    $('#lab_test_no2').val(data.sn);
                    $('#lab_test_name2').val(data.test);
                    $('#Department2').val(data.dept);
                    $('#labcombos').val(data.combo_test);
                    $('#category').val(data.category);
                    $('#sub_category').val(data.sub_category);
                    $('#doctor_can_upload').val(data.doctor_result_status);

                    $('.modal-title').text('Edit Investigation:  ' + res[1]);
                    $('#edit_lab_modal').modal('show');
               }
          });
     });


     $(document).on('click', '.manage_price', function() {

          var manage_price_id = $(this).attr("id");
          var res = manage_price_id.split("__");

          $.ajax({
               url: "fetch_labtest.php",
               method: "POST",
               data: {
                    manage_price_id: res[0]
               },
               dataType: "json",
               success: function(data) {
                    $('#lab_test_no').val(data.sn);
                    $('#lab_test_name').val(data.test);
                    $('#coverage').val(data.coverage);
                    $('#insurance_type').val(data.insurance_type);
                    $('#hosp_price').val(data.hosp_price);
                    $('#external_price').val(data.ext_price);
                    $('#NHIS_price').val(data.nhis_price);
                    $('#Department3').val(data.dept);

                    $('.modal-title').text('Manage Prices for ' + res[1]);
                    $('#manage_price_modal').modal('show');
               }
          });
     });

     $(document).ready(function() {
          $('.i-checks').iCheck({
               checkboxClass: 'icheckbox_square-green',
               radioClass: 'iradio_square-green',
          });


          $('.summernote').summernote();

     });


     $(document).on('click', '.add_fields_items', function() {
          $('#view_lab_modal').modal('hide');

          var lab_test_no = $(this).attr("id");
          var res = lab_test_no.split("__");

          /*         $.ajax({  
                        url:"fetch_labtest.php",  
                        method:"POST",  
                        data:{lab_test_no_:res[0]},  
                        dataType:"json",  
                        success:function(data){ */

          ///	alert(data);

          $('#test_no').val(res[0]);
          $('#test_name').val(res[1]);
          $('#field_name').val(res[1]);

          // $('#add_option_body').html(data);  
          $('#add_Lab_TestParameters_Modal').modal('show');
          ///}  
          //});  
     });



     $('#insert_form_fields').on("submit", function(event) {
          event.preventDefault();

          $.ajax({
               url: "insert.php",
               method: "POST",
               data: $('#insert_form_fields').serialize(),
               beforeSend: function() {
                    $('#insert').val("Inserting");
               },
               success: function(data) {
                    // $('#insert_form_fields')[0].reset(); 
                    //	swal({ title: 'Success!', text: 'Data Added Successfully', timer: 1000 })
                    //$('#field_name').val("");
                    $('#field_type').val("");
                    $('#Reference').val("");


                    $('#add_Lab_TestParameters_Modal').modal('hide');

                    ///==================================

                    var test_no = $("#test_no").val();
                    var field_name = $("#field_name").val();
                    var field_no = test_no + '__' + field_name;
                    $('#insert_form_fields')[0].reset();
                    //swal(field_no)
                    //var field_no=$('#test_no').val(data.test_no);
                    //var field_no="4";
                    $.ajax({
                         url: "fetch_set.php",
                         method: "POST",
                         data: {
                              field_no: field_no
                         },
                         success: function(data) {

                              //  $('.modal-title').text('Field Names for ' + res[1]);
                              $('#view_lab_modal').modal('show');
                              $('#view_lab_body').html(data);
                         }
                    });
                    ///==========================	


                    // $('#view_lab_modal').modal('show'); 
                    $('#test_fields').html(data);
               },
               complete: function() {
                    $('#insert').val("Insert");
               },
               error: function(data) {

                    alert("Oops...", "Something went wrong :(", "error");
                    swal({
                         title: 'Oops...!',
                         text: 'Something went wrong ',
                         type: 'error',
                         timer: 500
                    })
               }
          });

          //	   }
     });



     $('#add_option_forms').on("submit", function(event) {
          event.preventDefault();
          if ($('#Options').val() == "") {
               swal("Option Name is required");
          } else {
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#add_option_forms').serialize(),
                    beforeSend: function() {
                         $('#insert').val("Inserting");
                    },
                    success: function(data) {
                         //swal({ title: 'Success!', text: 'Data Added Successfully', timer: 1000 })
                         $('#Options').val("");
                         $('#add_Lab_TestParameters_Modal').modal('show');
                         // $('#add_option_div_table').html(data);  
                    },
                    complete: function() {
                         $('#insert').val("Insert");
                    },
                    error: function(data) {

                         alert("Oops...", "Something went wrong :(", "error");
                         swal({
                              title: 'Oops...!',
                              text: 'Something went wrong ',
                              type: 'error',
                              timer: 500
                         })
                    }
               });

          }
     });

     $(document).on('click', '.view_option', function() {
          var field_id_opt = $(this).attr("id");
          if (field_id_opt != '') {
               $.ajax({
                    url: "fetch.php",
                    method: "POST",
                    data: {
                         field_id_opt: field_id_opt
                    },
                    success: function(data) {
                         $('#field_option').html(data);
                         $('#dataModal').modal('show');
                    }
               });
          }
     });


     $(document).on('click', '.view_lab', function() {
          var field_no = $(this).attr("id");
          var res = field_no.split("__");


          if (field_no != '') {
               $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                         field_no: field_no
                    },
                    success: function(data) {

                         $('.modal-title').text('Field Names for ' + res[1]);
                         $('#view_lab_modal').modal('show');
                         $('#view_lab_body').html(data);
                    }
               });
          }
     });



     $(document).on('click', '.view_lab_option', function() {

          var field_no_option = $(this).attr("id");
          //  var res = field_no_option.split("__");

          if (field_no_option != '') {
               $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                         field_no_option: field_no_option
                    },
                    success: function(data) {

                         // $('.modal-title').text('Options for ' + res[1]);
                         $('#view_lab_option_modal').modal('show');
                         $('#view_lab_option_body').html(data);
                    }
               });
          }
     });


     $(document).on('click', '.view_lab_values', function() {
          var field_no_values = $(this).attr("id");
          //  var res = field_no_option.split("__");

          if (field_no_values != '') {
               $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                         field_no_values: field_no_values
                    },
                    success: function(data) {

                         // $('.modal-title').text('Options for ' + res[1]);
                         $('#view_lab_values_modal').modal('show');
                         $('#view_lab_values_body').html(data);
                    }
               });
          }
     });


     $(document).on('click', '.add_lab_field_options', function() {
          var field_no_option_add = $(this).attr("id");
          if (field_no_option_add != '') {
               $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                         field_no_option_add: field_no_option_add
                    },
                    success: function(data) {
                         $('#field_opt_no').val(data.field_id_no);
                         // $('#test_name').val(data.test);

                         $('#add_field_option_Modal').modal('show');
                         // $('#add_field_option_body').html(data); 
                    }
               });
          }
     });


     $(document).on('click', '.lab_scan_fields_del', function() {
          // $('#view_lab_modal').modal('hide'); 
          var field_no_del = $(this).attr("id");
          var res = field_no_del.split("__");

          if (field_no_del != '') {
               $.ajax({
                    url: "delete.php",
                    method: "POST",
                    data: {
                         field_no_del: res[0]
                    },
                    success: function(data) {

                         var field_no = res[1] + '__' + res[2];
                         $.ajax({
                              url: "fetch_set.php",
                              method: "POST",
                              data: {
                                   field_no: field_no
                              },
                              success: function(data) {

                                   //  $('.modal-title').text('Field Names for ' + res[1]);
                                   $('#view_lab_modal').modal('show');
                                   $('#view_lab_body').html(data);
                              }
                         });
                         ///==========================					
                         //$('#view_lab_modal').modal('hide'); 
                         //	  $('#view_lab_modal').modal('hide');  
                         //$('#view_lab_body').html(data); 
                    }
               });
          }
     });

     $(document).on('click', '.lab_scan_options_del', function() {
          $('#view_lab_option_modal').modal('hide');
          var option_no_del = $(this).attr("id");
          if (option_no_del != '') {
               $.ajax({
                    url: "delete.php",
                    method: "POST",
                    data: {
                         option_no_del: option_no_del
                    },
                    success: function(data) {

                         // $('#view_lab_modal').modal('show');  
                         //$('#view_lab_body').html(data); 
                    }
               });
          }
     });


     $(document).on('click', '.lab_scan_values_del', function() {
          $('#view_lab_values_modal').modal('hide');
          var values_no_del = $(this).attr("id");
          if (values_no_del != '') {
               $.ajax({
                    url: "delete.php",
                    method: "POST",
                    data: {
                         values_no_del: values_no_del
                    },
                    success: function(data) {

                         // $('#view_lab_modal').modal('show');  
                         //$('#view_lab_body').html(data); 
                    }
               });
          }
     });

     $(document).on('click', '.enable_labTest', function() {
          var enable_labTest_id = $(this).attr("id");
          if (enable_labTest_id != '') {
               $.ajax({
                    url: "fetch.php",
                    method: "POST",
                    data: {
                         enable_labTest_id: enable_labTest_id
                    },
                    success: function(data) {

                         $('#refresh').html(data);
                         // $('#view_lab_modal').modal('show');  
                         //$('#view_lab_body').html(data); 
                    }
               });
          }
     });

     $(document).on('click', '.disable_labTest', function() {
          var disable_labTest_id = $(this).attr("id");
          if (disable_labTest_id != '') {
               $.ajax({
                    url: "fetch.php",
                    method: "POST",
                    data: {
                         disable_labTest_id: disable_labTest_id
                    },
                    success: function(data) {

                         $('#refresh').html(data);
                         //$('#view_lab_body').html(data); 
                    }
               });
          }
     });

     $(document).on('click', '.view_combos', function() {
          // $('#view_lab_option_modal').modal('hide'); 
          var combos_id = $(this).attr("id");
          if (combos_id != '') {
               $.ajax({
                    url: "fetch_set.php",
                    method: "POST",
                    data: {
                         combos_id: combos_id
                    },
                    success: function(data) {

                         $('#view_combos_modal').modal('show');
                         $('#view_combos_body').html(data);
                    }
               });
          }
     });


     $(document).on('click', '.combos_list_del', function() {
          var combo_no = $(this).attr("id");

          var res = combo_no.split("__");

          if (combo_no != '') {
               $.ajax({
                    url: "delete.php",
                    method: "POST",
                    data: {
                         combo_no: res[0]
                    },
                    success: function(data) {

                         var combo_no = res[1] + '__' + res[2];

                         $.ajax({
                              url: "fetch_set.php",
                              method: "POST",
                              data: {
                                   combos_id: combo_no
                              },
                              success: function(data) {

                                   $('#view_combos_modal').modal('show');
                                   $('#view_combos_body').html(data);
                              }
                         });

                         // $('#view_lab_modal').modal('show');  
                         //$('#view_lab_body').html(data); 
                    }
               });
          }
     });




     $('#manage_price_body').on("submit", function(event) {
          event.preventDefault();
          var clear_status = '0';
          var coverage = $('#coverage').val();
          var clear_status = $('#NHIS_price').val();

          if ($('#hosp_price').val() == '0') {
               swal("Hospital Price is required");
               var clear_status = '1';
          } else if ($('#insurance_type').val() != '' && $('#NHIS_price').val() == '0') {
               swal("NHIS Price is required");
               var clear_status = '1';
          } else if ($('#NHIS_price').val() == '0' && $('#insurance_type').val() != '') {
               swal("Insurance Type is required");
               var clear_status = '1';
          } else
          //(clear_status == '0')  
          {
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#manage_price_body').serialize(),
                    beforeSend: function() {
                         $('#Save').val("Saving");
                    },
                    success: function(data) {
                         //swal({ title: 'Success!', text: 'Price Has Been Updated Successfully', timer: 1000 })
                         $('#manage_price_modal').modal('hide');
                         //$('#refresh').html(data);

                         window.location.reload();

                    },
                    complete: function() {
                         $('#Save').val("Saved");
                    },
                    error: function(data) {

                         alert("Oops...", "Something went wrong :(", "error");
                         //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
                    }
               });

          }
     });




     $('#edit_lab_body').on("submit", function(event) {
          event.preventDefault();
          var clear_status = '0';

          if ($('#lab_test_name2').val() == '') {
               swal("Test Name is required");
               var clear_status = '1';
          } else if ($('#Department2').val() == '') {
               swal("Department is required");
               var clear_status = '1';
          } else if ($('#labcombos').val() == '') {
               swal("Lab Combination is required");
               var clear_status = '1';
          } else
          //(clear_status == '0')  
          {
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#edit_lab_body').serialize(),
                    beforeSend: function() {
                         $('#Save').val("Saving");
                    },
                    success: function(data) {
                         swal({
                              title: 'Success!',
                              text: 'Updated Successfully',
                              timer: 1000
                         })
                         $('#edit_lab_modal').modal('hide');
                         //	$('#refresh').html(data);

                         window.location.reload();

                    },
                    complete: function() {
                         $('#Save').val("Saved");
                    },
                    error: function(data) {

                         alert("Oops...", "Something went wrong :(", "error");
                         //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
                    }
               });

          }
     });

     $('#Add_Test_form').on("submit", function(event) {
          event.preventDefault();

          $.ajax({
               url: "insert.php",
               method: "POST",
               data: $('#Add_Test_form').serialize(),
               beforeSend: function() {
                    $('#Save').val("Saving");
               },
               success: function(data) {
                    alert('Saved Successful');
                    //	swal({ title: 'Success!', text: 'Saved Successfully', timer: 1000 })
                    $('#Add_Test_Modal').modal('hide');
                    $('#refresh').html(data);


               },
               complete: function() {
                    $('#Save').val("Saved");
               },
               error: function(data) {

                    alert("Oops...", "Something went wrong :(", "error");
                    //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
               }
          });

     });


     $('#add_new_combos_form').on("submit", function(event) {
          event.preventDefault();

          if ($('#combos').val() == "") {
               swal("Combination is required");
          } else {
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#add_new_combos_form').serialize(),
                    beforeSend: function() {
                         $('#Save').val("Saving");
                    },
                    success: function(data) {
                         //	swal({ title: 'Success!', text: 'Saved Successfully', timer: 1000 })
                         $('#add_new_combos_modal').modal('hide');

                         $('#refresh').html(data);
                    },
                    complete: function() {
                         $('#Save').val("Saved");
                    },
                    error: function(data) {

                         alert("Oops...", "Something went wrong :(", "error");
                         swal({
                              title: 'Oops...!',
                              text: 'Something went wrong ',
                              type: 'error',
                              timer: 500
                         })
                    }
               });
          }
     });


     $(document).on('click', '.delete_combos', function() {
          var del_combs_name_id = $(this).attr("id");

          if (del_combs_name_id != '') {
               $.ajax({
                    url: "delete.php",
                    method: "POST",
                    data: {
                         del_combs_name_id: del_combs_name_id
                    },
                    success: function(data) {

                         // $('#view_lab_modal').modal('show');  
                         $('#refresh').html(data);
                    }
               });
          }
     });


     $(document).on('click', '.add_fields_items_close', function() {
          $('#view_lab_modal').modal('hide');
     });

     $(document).on('click', '.add_lab_testname', function() {

          $('.modal-title').text('New Investigation Name');
          // $('#Add_Test_form')[0].reset(); 
          $('#Add_Test_Modal').modal('show');
     });

     $(document).on('click', '.add_lab_combos', function() {
          $('.modal-title').text('Setup New Lab Combination');
          $('#add_new_combos_form')[0].reset();
          $('#add_new_combos_modal').modal('show');
     });

     // External LIS Non-Cron Application-Level Auto Sync & Manual Trigger
     if (typeof window.syncLisResults !== 'function') {
          window.syncLisResults = function(force) {
               var url = 'lis_poll_sync.php' + (force ? '?force=1' : '');
               $.getJSON(url, function(res) {
                    if (res && res.status === 'success' && res.items_processed > 0) {
                         console.log('LIS Sync: Processed ' + res.items_processed + ' new result(s).');
                         if (force || window.location.href.indexOf('mgt.php') !== -1 || window.location.href.indexOf('fillrslt') !== -1) {
                              location.reload();
                         }
                    } else {
                         console.log('LIS Sync Status:', (res ? res.status : 'ok'), (res ? res.message : ''));
                         if (force) {
                              alert('LIS Sync Complete: ' + (res && res.message ? res.message : ((res && res.items_processed ? res.items_processed : 0) + ' new items processed')));
                         }
                    }
               }).fail(function(err) {
                    console.log('LIS Sync Error:', err);
                    if (force) alert('Failed to connect to LIS sync service.');
               });
          };
     }

     // LIS Dispatch & Retry Modal Action Handlers
     if (typeof window.retryLisDispatch !== 'function') {
          window.retryLisDispatch = function(labrequestNo, testId, testName, patientNo) {
               toastr.info('Communicating with External LIS...', '', { timeOut: 3000 });
               $.ajax({
                    url: 'lis_action.php',
                    method: 'POST',
                    data: {
                         action: 'dispatch',
                         labrequest_no: labrequestNo,
                         test_id: testId,
                         test_name: testName,
                         patient_no: patientNo
                    },
                    success: function(res) {
                         if (res && res.success) {
                              toastr.success(res.message, 'LIS Dispatch');
                              if (typeof load_table === 'function') {
                                   load_table();
                              } else {
                                   location.reload();
                              }
                         } else {
                              toastr.error((res && res.error) ? res.error : 'Dispatch failed', 'LIS Error');
                         }
                    },
                    error: function() {
                         toastr.error('Failed to connect to LIS service.', 'Network Error');
                    }
               });
          };
     }

     if (typeof window.pollLisModal !== 'function') {
          window.pollLisModal = function(btn) {
               var $btn = $(btn);
               var originalHtml = $btn.html();
               $btn.html('<i class="fa fa-spin fa-spinner"></i> Polling LIS...').prop('disabled', true);
               $.ajax({
                    url: 'lis_action.php',
                    method: 'POST',
                    data: { action: 'poll' },
                    success: function(res) {
                         $btn.html(originalHtml).prop('disabled', false);
                         if (res && res.success) {
                              toastr.success(res.message, 'LIS Poll Complete');
                              if (typeof load_table === 'function') {
                                   load_table();
                              } else {
                                   location.reload();
                              }
                         } else {
                              toastr.error((res && res.error) ? res.error : 'Poll failed', 'LIS Poll');
                         }
                    },
                    error: function() {
                         $btn.html(originalHtml).prop('disabled', false);
                         toastr.error('Failed to sync with LIS.', 'Network Error');
                    }
               });
          };
     }
</script>