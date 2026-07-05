    <!-- Mainly scripts -->
    <script src="../js/jquery-2.1.1.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>
    <script src="../js/plugins/chosen/chosen.jquery.js"></script>

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
       
      
      
          <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script src="../js/plugins/datapicker/select2.full.min.js"></script>
     
    
<script>


	
$(document).ready(function(){
	$('.i-checks').iCheck({
		checkboxClass: 'icheckbox_square-green',
		radioClass: 'iradio_square-green',
	});


	$('.summernote').summernote();

}); 
   
		$(".chosen-select").chosen({ allow_single_deselect: true, enable_search_threshold: 10,no_results_text:'Oops, nothing found!', width:"100%" });
		$('.chosen-drop').css({"width": "100%", "white-space": "nowrap"})

  
		$(document).on('click', '.lab_request', function(){ 
		
			var parts = $(this).attr("id");
			var res = parts.split("__"); 
			
			$('#patient_no').val(res[0]);
			$('#patient_name').val(res[1]);
			$('#buz').val(res[2]);
			$('#Referred').val(res[3]);
			$('#Lab_Request_Modal').modal('show');  
		
		}); 
	
  
   	   $(document).on('click', '.add_new_sale', function(){  
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
			
		
	  $(document).on('click', '.edit_discount', function(){  
		    
			//$('#discount_edit_modal').modal('show');  
			
           var edit_id = $(this).attr("id");  
		  /// var res = edit_id.split("__");
		   
		   $('.modal-title').text('Edit Discount & Charge' + edit_id );
		  $('#discount_edit_modal').modal('show'); 
		   
           $.ajax({  
                url:"fetch.php",  
                method:"POST",  
                data:{edit_id:edit_id},  
                dataType:"json",  
                success:function(data){
				 
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
		
		

			
	$('#new_sale_form').on("submit", function(event){  
           event.preventDefault();
		   
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#new_sale_form').serialize(),  
                     beforeSend:function(){  
                          $('#save').val("Saving");  
                     },  
                     success:function(data){  
					 $('#new_sale_form')[0].reset();
							location.href = "xsale.php?sv";
                     },
					 complete:function(){  
                          $('#save').val("Saved");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
      });  
	  

	 $('#lab_request_form').on("submit", function(event){  
           event.preventDefault(); 
		              if($('#patient').val() == "")  
           {  
                swal("Patient Details is required");  
           }  
            else  
           {  
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#lab_request_form').serialize(),  
                     beforeSend:function(){  
                          $('#add_request').val("Adding");  
                     },  
                     success:function(data){  
			//swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
						// $('#specimen_notes').val("");
						//  $('#Specimen').val(""); 
                          $('#Lab_Request_Modal').modal('hide');  
                         $('#test_fields').html(data);  
						location.href = "mgt.php?sv";
                     },
					 complete:function(){  
                          $('#add_request').val("Add Request");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
		   }
      });  
	   	  
	  
	  $(document).ready(function(){
			$("#patient_list").hide();
		$("#ext_patient").hide();
		//$("#apply_button").hide();
		
    $('#business_unit_lab_req').on('change', function() {
		
	
      if ( this.value == 'IN')
      {
		$("#patient_list").show();
		$("#ext_patient").hide();
		//$("#apply_button").show();
      }
         if ( this.value == 'EX')
      {
        $("#ext_patient").show();
        $("#patient_list").hide();
	//	$("#apply_button").show();
      }

         if ( this.value == '')
      {
        $("#ext_patient").hide();
        $("#patient_list").hide();
		//$("#apply_button").hide();
      }
	  
    });
});





 $(document).on('click', '.edit_ex', function(){
		    
           var edit_ext_patient = $(this).attr("id");  
		   var res = edit_ext_patient.split("__");
		   //////edit_lab_id
           $.ajax({  
                url:"fetch_labtest.php",  
                method:"POST",  
                data:{edit_ext_patient:res[0]},  
                dataType:"json",  
                success:function(data){ 
				
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
 
 
 	$('#edit_ext_form').on("submit", function(event){  
           event.preventDefault(); 
 
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#edit_ext_form').serialize(),  
                     beforeSend:function(){  
                          	$('#add_request').val("Adding");  
                     },  
                     success:function(data){  
							$('#edit_ext_modal').modal('hide');  
						//	$('#test_fields').html(data);  
							location.href = "xsale.php?sv";
                     },
					 complete:function(){  
                          $('#save').val("Updating");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
      });  
	   	  
		  
		  
function setModalMaxHeight(element) {
  this.$element     = $(element);  
  this.$content     = this.$element.find('.modal-content');
  var borderWidth   = this.$content.outerHeight() - this.$content.innerHeight();
  var dialogMargin  = $(window).width() < 768 ? 20 : 60;
  var contentHeight = $(window).height() - (dialogMargin + borderWidth);
  var headerHeight  = this.$element.find('.modal-header').outerHeight() || 0;
  var footerHeight  = this.$element.find('.modal-footer').outerHeight() || 0;
  var maxHeight     = contentHeight - (headerHeight + footerHeight);

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

	  	$(document).ready(function(){
			$("#ref").hide();
			$("#fam").hide();
		
    $('#entity').on('change', function() {
	
      if ( this.value == 'Family')
      {
		$("#fam").show();
		$("#ref").hide();
      }
         if ( this.value == 'Referral')
      {
        $("#ref").show();
        $("#fam").hide();
      }

         if ( this.value == '')
      {
        $("#ref").hide();
        $("#fam").hide();
      }
	  
    });
});
 
	
	
	$(document).ready(function(){
			$("#bene").hide();
		
    $('#transaction_type').on('change', function() {
	
      if ( this.value == 'Transfer')
      {
		$("#bene").show();
      }

         if ( this.value != 'Transfer')
      {
        $("#bene").hide();
      }
	  
    });
});

 
 	$(document).ready(function(){
		$("#invest_grp").hide();
		$("#med_grp").hide();
		$("#pharm_grp").hide();
	$("#other_serv_grp").hide();
		 $("#nurs_grp").hide();
		
    $('#service_type3').on('change', function() {

      if ( this.value == 'investigations')
      {
		$("#invest_grp").show();
		$("#med_grp").hide();
		$("#pharm_grp").hide();
		$("#other_serv_grp").hide();
		 $("#nurs_grp").hide();
      }
      
	  if ( this.value == 'medical')
      {
        $("#med_grp").show();
        $("#invest_grp").hide();
		$("#pharm_grp").hide();
		$("#other_serv_grp").hide();
		 $("#nurs_grp").hide();
      }
	 
	 if ( this.value == 'pharma')
      {
        $("#med_grp").hide();
        $("#invest_grp").hide();
		$("#pharm_grp").show();
		 $("#other_serv_grp").hide();
		 $("#nurs_grp").hide();
		  $("#nurs_grp").hide();
      }

	 if ( this.value == 'nursing')
      {
        $("#med_grp").hide();
        $("#invest_grp").hide();
		$("#pharm_grp").hide();
		 $("#other_serv_grp").hide();
		 $("#nurs_grp").show();
      }
	  
	 if ( this.value == 'others')
      {
        $("#med_grp").hide();
        $("#invest_grp").hide();
		$("#pharm_grp").hide();
		 $("#other_serv_grp").show();
		 $("#nurs_grp").hide();
      }	  
	  
    });
});

		  

</script>