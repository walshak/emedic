<script src="../js/jquery-3.1.1.min.js"></script>

<script src="../js/bootstrap.min.js"></script>


<script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- FooTable -->
<script src="../js/plugins/footable/footable.all.min.js"></script>
<!-- walshak 9/6/2023 -->
<!-- typeahead -->
<!-- <script src="../js/plugins/typeahead/typeahead.min.js"></script> -->
<script src="../js/plugins/typeahead/typeahead.jquery.min.js"></script>
<!-- walshak 9/6/2023 -->

<!-- Custom and plugin javascript -->
<script src="../js/inspinia.js"></script>
<script src="../js/plugins/pace/pace.min.js"></script>
<script src="../js/plugins/chosen/chosen.jquery.js"></script>

<script src="../js/plugins/chartJs/Chart.min.js"></script>
<script src="../js/plugins/sparkline/jquery.sparkline.min.js"></script>

<script src="../js/plugins/d3/d3.min.js"></script>
<script src="../js/plugins/c3/c3.min.js"></script>

<!-- iCheck -->
<script src="../js/plugins/iCheck/icheck.min.js"></script>

<!-- SUMMERNOTE -->
<script src="../js/plugins/summernote/summernote.min.js"></script>
<script src="../js/plugins/peity/jquery.peity.min.js"></script>

<!-- Peity -->
<script src="../js/demo/peity-demo.js"></script>
<script src="../js/sweetalert2.min.js"></script>
<script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="../js/plugins/toastr/toastr.min.js"></script>
<script src="../js/bootstrap-timepicker.min.js"></script>





<script>
     function dispense_xternal(sale_sn) {

          var rr = confirm("Are you sure you want to Dispense ?");

          if (rr === true) {
               var transaction = 'dispense_only';

               $.ajax({
                    url: "../inc/inv_pro.php",
                    method: "POST",
                    data: {
                         dispense_oncredit_external_sales: transaction,
                         sale_sn: sale_sn
                    },
                    success: function(data) {

                         document.getElementById("dispense_external").disabled = true;

                         var jsonn = JSON.parse(data);
                         if (jsonn["status"] == 1) {
                              document.getElementById("dispense_external").disabled = false;
                              toastr.error(jsonn["message"], 'Attention', {
                                   timeOut: 5000
                              })
                         } else {
                              toastr.success(jsonn["message"], 'Attention', {
                                   timeOut: 5000
                              })
                              ///window.location.href = 'index.php?presc&hos_no=' + hosp_no;
                         }

                    }
               });

          }


     }


     function dispense_oncredit_xternal() {

          var sale_sn = $('#sale_sn').val();
          var transaction = 'credit';
          $.ajax({
               url: "../inc/inv_pro.php",
               method: "POST",
               data: {
                    dispense_oncredit_external_sales: transaction,
                    sale_sn: sale_sn
               },
               success: function(data) {

                    document.getElementById("dispense_oncredit_xternal").disabled = true;

                    var jsonn = JSON.parse(data);
                    if (jsonn["status"] == 1) {
                         document.getElementById("dispense_oncredit_xternal").disabled = false;
                         toastr.error(jsonn["message"], 'Attention', {
                              timeOut: 5000
                         })
                    } else {
                         toastr.success(jsonn["message"], 'Attention', {
                              timeOut: 5000
                         })
                         ///window.location.href = 'index.php?presc&hos_no=' + hosp_no;
                    }

               }
          });
     }




     function toggle(source) {
          checkboxes = document.getElementsByName('inv[]');
          for (var i = 0, n = checkboxes.length; i < n; i++) {
               checkboxes[i].checked = source.checked;
          }
     }

     $(document).on('click', '.view_results_list', function() {
          var view_results_list = $(this).attr("id");
          if (view_results_list != '') {

               $.ajax({
                    url: "result_list_preview.php",
                    method: "POST",
                    data: {
                         view_results_list: view_results_list
                    },
                    success: function(data) {
                         $('#view_results_list_body').html(data);
                         $('#view_results_list_modal').modal('show');
                    }
               });
          }
     });

     $(document).on('click', '.enter_results', function() {
          var enter_results_id = $(this).attr("id");
          if (enter_results_id != '') {

               $.ajax({
                    url: "view_dasboard_results.php",
                    method: "POST",
                    data: {
                         enter_results_id: enter_results_id
                    },
                    success: function(data) {

                         $('#enter_results_body').html(data);
                         $('#enter_results_modal').modal('show');
                    }
               });
          }
     });


     $(document).on('click', '.consultations_vitals', function() {
          var consultations_vitals_id = $(this).attr("id");
          if (consultations_vitals_id != '') {
               $.ajax({

                    url: "view_dasboard_results.php",
                    method: "POST",
                    data: {
                         consultations_vitals_id: consultations_vitals_id
                    },
                    success: function(data) {
                         $('#consultations_vitals_body').html(data);
                         $('#consultations_vitals_modal').modal('show');
                    }
               });
          }
     });




     $(document).ready(function() {

          $('.footable').footable();
          $('.footable2').footable();

     });



     $(document).ready(function() {
          $('.i-checks').iCheck({
               checkboxClass: 'icheckbox_square-green',
               radioClass: 'iradio_square-green',
          });


          $('.summernote').summernote();

     });

     $(".chosen-select").chosen({
          allow_single_deselect: true,
          enable_search_threshold: 10,
          no_results_text: 'Oops, nothing found!',
          width: "100%"
     });
     $('.chosen-drop').css({
          "width": "100%",
          "white-space": "nowrap"
     })


     $(document).on('click', '.lab_request', function() {

          ///$hosp_no .'__' .$patient_name.'__'.'insurance'.'__'.'int'.'__'.'insur_no'.'__'.'add'.'__EX__'.$referral;	
          var parts = $(this).attr("id");
          var res = parts.split("__");

          $('#patient_no').val(res[0]);
          $('#patient_name').val(res[1]);
          $('#insurance').val(res[2]);
          $('#interest').val(res[3]);
          $('#insurance_no').val(res[4]);
          $('#add_minus').val(res[5]);
          $('#buz').val(res[6]);
          $('#Referred').val(res[7]);
          $('#Lab_Request_Modal').modal('show');

     });


     $(document).on('click', '.add_new_sale', function() {
          $('#new_sale_form')[0].reset();
          $('#new_sale_modal').modal('show');

     });



     $('#data_1 .input-group.date').datepicker({
          todayBtn: "linked",
          keyboardNavigation: false,
          forceParse: false,
          calendarWeeks: true,
          autoclose: true
     });

     $('#data_5 .input-daterange').datepicker({
          keyboardNavigation: false,
          forceParse: false,
          autoclose: true
     });


     $(document).on('click', '.edit_discount', function() {

          //$('#discount_edit_modal').modal('show');  

          var edit_id = $(this).attr("id");
          /// var res = edit_id.split("__");

          $('.modal-title').text('Edit Discount & Charge' + edit_id);
          $('#discount_edit_modal').modal('show');

          $.ajax({
               url: "fetch.php",
               method: "POST",
               data: {
                    edit_id: edit_id
               },
               dataType: "json",
               success: function(data) {

                    $('#sn').val(data.sn);
                    $('#discount_charge1').val(data.discount_charge);
                    $('#mode1').val(data.percentage_flat);
                    $('#mode_value1').val(data.percentage_flat_value);
                    $('#how_long1').val(data.duration);
                    $('#specify_count1').val(data.specify_count);
                    $('#service_type1').val(data.apply_to_services);
                    $('#old_service_type').val(data.apply_to_services);
                    $('#discount_charge1').val(data.discount_charge);
                    $('#history').val(data.history);

                    $('.modal-title').text('Edit Discount & Charge');
                    $('#discount_edit_modal').modal('show');
               }
          });
     });




     $('#new_sale_form').on("submit", function(event) {
          event.preventDefault();

          $.ajax({
               url: "insert.php",
               method: "POST",
               data: $('#new_sale_form').serialize(),
               beforeSend: function() {
                    $('#save').val("Saving");
               },
               success: function(data) {
                    $('#new_sale_form')[0].reset();
                    location.href = "xsale.php?sv";
               },
               complete: function() {
                    $('#save').val("Saved");
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

     });


     $('#lab_request_form').on("submit", function(event) {
          event.preventDefault();
          if ($('#patient').val() == "") {
               swal("Patient Details is required");
          } else {

               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#lab_request_form').serialize(),
                    beforeSend: function() {
                         $('#add_request').val("Adding");
                    },
                    success: function(data) {
                         var json = JSON.parse(data);
                         if (json["rights"] == 'LB') {
                              location.href = "mgt.php?hosp_no=" + json["patient"];
                         } else {
                              location.href = "xsale.php?emr=" + json["patient"] + '&investigations';
                         }
                    },
                    complete: function() {
                         $('#add_request').val("Add Request");
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


     $(document).ready(function() {
          $("#patient_list").hide();
          $("#ext_patient").hide();
          //$("#apply_button").hide();

          $('#business_unit_lab_req').on('change', function() {


               if (this.value == 'IN') {
                    $("#patient_list").show();
                    $("#ext_patient").hide();
                    //$("#apply_button").show();
               }
               if (this.value == 'EX') {
                    $("#ext_patient").show();
                    $("#patient_list").hide();
                    //	$("#apply_button").show();
               }

               if (this.value == '') {
                    $("#ext_patient").hide();
                    $("#patient_list").hide();
                    //$("#apply_button").hide();
               }

          });
     });


     $(document).on('click', '.doc_', function() {

          var doc_ = $(this).attr("id");
          if (doc_ != '') {
               $.ajax({

                    url: "view_dasboard_results.php",
                    method: "POST",
                    data: {
                         doc_: doc_
                    },
                    success: function(data) {
                         $('#enter_results_body').html(data);
                         $('#enter_results_modal').modal('show');
                    }
               });
          }
     });




     $(document).on('click', '.edit_ex', function() {

          var edit_ext_patient = $(this).attr("id");
          var res = edit_ext_patient.split("__");
          //////edit_lab_id
          $.ajax({
               url: "fetch_labtest.php",
               method: "POST",
               data: {
                    edit_ext_patient: res[0]
               },
               dataType: "json",
               success: function(data) {

                    $('#name_edit').val(data.cust_name);
                    $('#gender_edit').val(data.gender);
                    $('#dob_edit').val(data.dob);
                    $('#phone_edit').val(data.phone);
                    $('#addr_edit').val(data.address);
                    $('#email_addr_edit').val(data.email_address);
                    $('#referral_edit').val(data.referral);
                    $('#referral_edit_2').val(data.referral);
                    $('#transc_code_edit').val(data.transc_code);

                    $('.modal-title').text('Edit Patient Record:  ' + res[1]);
                    $('#edit_ext_modal').modal('show');
               }
          });
     });


     $('#edit_ext_form').on("submit", function(event) {
          event.preventDefault();
          $.ajax({
               url: "insert.php",
               method: "POST",
               data: $('#edit_ext_form').serialize(),
               beforeSend: function() {
                    $('#add_request').val("Adding");
               },
               success: function(data) {
                    $('#edit_ext_modal').modal('hide');
                    //	$('#test_fields').html(data);  
                    location.href = "xsale.php?sv";
               },
               complete: function() {
                    $('#save').val("Updating");
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

     });



     function setModalMaxHeight(element) {
          this.$element = $(element);
          this.$content = this.$element.find('.modal-content');
          var borderWidth = this.$content.outerHeight() - this.$content.innerHeight();
          var dialogMargin = $(window).width() < 768 ? 20 : 60;
          var contentHeight = $(window).height() - (dialogMargin + borderWidth);
          var headerHeight = this.$element.find('.modal-header').outerHeight() || 0;
          var footerHeight = this.$element.find('.modal-footer').outerHeight() || 0;
          var maxHeight = contentHeight - (headerHeight + footerHeight);

          this.$content.css({
               'overflow': 'hidden'
          });

          this.$element
               .find('.modal-body').css({
                    'max-height': maxHeight,
                    'overflow-y': 'auto'
               });
     }

     $('.modal').on('show.bs.modal', function() {
          $(this).show();
          setModalMaxHeight(this);

     });

     $(window).resize(function() {
          if ($('.modal.in').length != 0) {
               setModalMaxHeight($('.modal.in'));
          }
     });

     $(document).ready(function() {
          $("#ref").hide();
          $("#fam").hide();

          $('#entity').on('change', function() {

               if (this.value == 'Family') {
                    $("#fam").show();
                    $("#ref").hide();
               }
               if (this.value == 'Referral') {
                    $("#ref").show();
                    $("#fam").hide();
               }

               if (this.value == '') {
                    $("#ref").hide();
                    $("#fam").hide();
               }

          });
     });



     $(document).ready(function() {
          $("#mode_payment_status").show();
          $("#bene").hide();

          $('#transaction_type').on('change', function() {

               if (this.value == 'Transfer_to_patient') {
                    $("#bene").show();
                    $("#mode_payment_status").hide();
               }

               if (this.value != 'Transfer_to_patient') {
                    $("#bene").hide();
                    $("#mode_payment_status").show();
               }

          });
     });


     $(document).ready(function() {
          $("#invest_grp").hide();
          $("#med_grp").hide();
          $("#pharm_grp").hide();
          $("#other_serv_grp").hide();
          $("#nurs_grp").hide();

          $('#service_type3').on('change', function() {

               if (this.value == 'investigations') {
                    $("#invest_grp").show();
                    $("#med_grp").hide();
                    $("#pharm_grp").hide();
                    $("#other_serv_grp").hide();
                    $("#nurs_grp").hide();
               }

               if (this.value == 'medical') {
                    $("#med_grp").show();
                    $("#invest_grp").hide();
                    $("#pharm_grp").hide();
                    $("#other_serv_grp").hide();
                    $("#nurs_grp").hide();
               }

               if (this.value == 'pharma') {
                    $("#med_grp").hide();
                    $("#invest_grp").hide();
                    $("#pharm_grp").show();
                    $("#other_serv_grp").hide();
                    $("#nurs_grp").hide();
                    $("#nurs_grp").hide();
               }

               if (this.value == 'nursing') {
                    $("#med_grp").hide();
                    $("#invest_grp").hide();
                    $("#pharm_grp").hide();
                    $("#other_serv_grp").hide();
                    $("#nurs_grp").show();
               }

               if (this.value == 'others') {
                    $("#med_grp").hide();
                    $("#invest_grp").hide();
                    $("#pharm_grp").hide();
                    $("#other_serv_grp").show();
                    $("#nurs_grp").hide();
               }

          });
     });



     $(document).on('click', '.dsp_oncredit', function() {
          var dsp_oncredit_id = $(this).attr("id");

          if (dsp_oncredit_id != '') {
               $.ajax({
                    url: "../pharmacy/fetch_set.php",
                    method: "POST",
                    data: {
                         dsp_oncredit_id: dsp_oncredit_id
                    },
                    success: function(data) {

                         $('.modal-title').text('Dispense On/Credit');

                         $('#dsp_oncredit_body').html(data);
                         $('#dsp_oncredit_modal').modal('show');
                    }
               });
          }
     });

     function setSearchVal(r, o) {
          $('#search-in-patient').val(r);
          $('#select-in-patient').html('');
          $('#in-patient-search-box').val(o);
     }

     function searchInPatient(q) {
          var searchRequest;
          if (q != '') {
               if (searchRequest !== undefined) {
                    // Abort the previous AJAX request if it exists
                    searchRequest.abort();
               }
               $('#narrow-search-status').text('Fetching patients...');
               searchRequest = $.ajax({
                    url: "live_search_patients.php",
                    method: "GET",
                    dataType: 'html',
                    data: {
                         term: q
                    },
                    success: function(data) {
                         // Clear existing options from the select field
                         $('#select-in-patient').html('');
                         data = JSON.parse(data);
                         for (var i = 0; i < data.length; i++) {
                              // Append the new options to the list field
                              var name = `${data[i].surname}, ${data[i].fname} ${(data[i].oname) ? data[i].oname : ""} [${data[i].hospital_no}]`;
                              var mk = `<li class='list-group-item' 
                                   style="background-color: #f0f0f0;"
                                   onclick="setSearchVal('${data[i].hospital_no}__${data[i].fname} ${data[i].surname}__IN', '${name}')">
                                   [${data[i].surname}, ${data[i].fname} ${(data[i].oname) ? data[i].oname : ""} [${data[i].hospital_no}]</li>`;
                              $('#select-in-patient').append(mk);
                              // $('#search-in-patient').val(`${data[i].hospital_no}__${data[i].fname} ${data[i].surname}__IN`);
                         }
                         // alert($('#select-in-patient'));
                         // $('#search-in-patient').html(data);
                         // console.log(data);
                         // $('#narrow-search-status').text('Narrowed list of patients fetched, Proceed to select from the list');
                    }
               });
          }
     }

     var search_patient_result_ex = [];
     $('#typeahead_search_patient_ex').typeahead({
          source: function(query, query_response) {
               $.ajax({
                    url: "live_search_patients.php",
                    method: "POST",
                    data: {
                         typeahead_search_patients_ex: true,
                         action: 'typeahead_search_patients_ex',
                         input_text: $('#typeahead_search_patient_ex').val()
                    },
                    dataType: "json",
                    success: function(data) {
                         search_specialist_result = data;
                         query_response($.map(data, function(item) {
                              return item.name;
                         }));
                    }

               })
          },
          updater: function(item) {
               search_specialist_result.forEach(element => {
                    if (element.name == item) {

                         $('#search-ex-patient').val(`${element.transc_code}__${element.cust_name}__EX`);
                         // $('#typeahead_search_patient_encoded_hospital_no').val(element.hospital_no)
                         // $('#typeahead_search_patient_id').val(element.id)
                         // $('#typeahead_search_patient_name').val(element.name)
                         return item;
                    }
               });
               return item
          }
     });


     //walshak 27/6/2023
</script>

<?php

include_once('../profile/notification_modal.php');
$employeeCode = $_SESSION['EmployeeCode'];
$userChannels = getUserDefaultChannels($db, $employeeCode);
subscribeToDefaultChannels($db, $employeeCode);

///if ($_SESSION['convert_patient_insur'] == 1 && $_SESSION['hmo__notification'] == 1) {
//  check_unvalidated_claims($db); //////
///}

/* if ($_SESSION['notify_pharmacy'] == 1) {
     check_pharmacy_post($db);
} */
?>
<!-- 
<script>
     alert('Notifications have been checked and sent if any pending requests found.');
     //walshak 27/6/2023
</script> -->