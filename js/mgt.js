
    var edit = function() {
            $('.click2edit').summernote({focus: true});
        };
        var save = function() {
            var aHTML = $('.click2edit').code(); //save HTML If you need(aHTML: array).
            $('.click2edit').destroy();
        };

			
function checkTextField(){
  if (document.getElementById("field_name_type").value != null) {
   alert("Oops...", "Something went wrong :(", "error");
  }
};


  

 $('#cancel_scan_form').on("submit", function(event){  
   event.preventDefault(); 
		$.ajax({  
			 url:"insert.php",  
			 method:"POST",  
			 data:$('#cancel_scan_form').serialize(),  
			 beforeSend:function(){  
				  $('#cancel_yes').val("Saving");  
			 },  
			 success:function(data){  
	swal({ title: 'Success!', text: 'Canceled Successfully', timer: 250 })
				 window.location.reload() ;
			 },
			 complete:function(){  
				  $('#add_request').val("Done");  
			 }, 
			error:function(data){
				
					alert("Oops...", "Something went wrong :(", "error");
					swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
			} 
		}); 
});
	


 $('#approve_scan_form').on("submit", function(event){  
   event.preventDefault(); 
		$.ajax({  
			 url:"insert.php",  
			 method:"POST",  
			 data:$('#approve_scan_form').serialize(),  
			 beforeSend:function(){  
				  $('#save').val("Saving");  
			 },  
			 success:function(data){  
	swal({ title: 'Success!', text: 'Saved Successfully', timer: 250 })
				 window.location.reload() ;
			 },
			 complete:function(){  
				  $('#add_request').val("Saved");  
			 }, 
			error:function(data){
				
					alert("Oops...", "Something went wrong :(", "error");
					swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
			} 
		}); 
});
	     

 $(document).ready(function(){  
	




      $(document).on('click', '.view_notes', function(){ 
           

          
           var note_lab_id = $(this).attr("id");
		//   var res = note_lab_id.split("__"); 
		   
		             if(note_lab_id != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{note_lab_id:note_lab_id}, 
                     success:function(data){
						 
						  $('.modal-title').text('Requested ID Notes:: ' + note_lab_id);   
                          $('#view_notes_body').html(data);  
                          $('#view_notes_modal').modal('show');  
                     }  
                });  
           }            
      }); 
	  
	  

		        $(document).on('click', '.Cancel_lab_request', function(){ 
                    // $('#Lab_Request_Modal').modal('hide');
					 window.location.reload();  
          
      });   
	     

      $(document).on('click', '.fill_results', function(){  
           var labrequest_no_add = $(this).attr("id");
		   var res = labrequest_no_add.split("__"); 
		   
		             if(labrequest_no_add != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_no_add:res[0],group_id:res[1]}, 
                     success:function(data){  
					 
					 $('.modal-title').text('Fill Result for ' + res[2]);  
                          $('#fill_result_body').html(data);  
                          $('#fill_result_Modal').modal('show');  
                     }  
                });  
           }            
      }); 
	  
	
	  $(document).on('click', '.edit_results', function(){  
		  $('#view_results_modal').modal('hide'); 
		  
           var labrequest_no_edit = $(this).attr("id"); 
		             if(labrequest_no_edit != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_no_edit:labrequest_no_edit}, 
                     success:function(data){  
                          $('#fill_result_body').html(data);  
                          $('#fill_result_Modal').modal('show');  
                     }  
                });  
           }            
      }); 
	     
		$(document).on('click', '.reject_results', function(){  
		  $('#reject_confirm_modal').modal('hide'); 
		  
           var labrequest_reject_no = $(this).attr("id"); 
		             if(labrequest_reject_no != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_reject_no:labrequest_reject_no}, 
                     success:function(data){  
					// swal({ title: 'Rejected!', text: 'Rejected Successfully', timer: 1000 })
                         $('#refresh_approve').html(data);  
                        //  $('#reject_confirm_modal').modal('show');  
                     }  
                });  
           }            
      }); 		 
	  
		$(document).on('click', '.reject_confirmation', function(){  
		  $('#view_results_modal').modal('hide'); 
		  
           var labrequest_reject_confirm = $(this).attr("id"); 
		             if(labrequest_reject_confirm != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_reject_confirm:labrequest_reject_confirm}, 
                     success:function(data){  
                          $('#reject_body').html(data);  
                          $('#reject_confirm_modal').modal('show');  
                     }  
                });  
           }            
      }); 
	  
		$(document).on('click', '.approve_reject_confirmation', function(){  
		  $('#view_results_modal').modal('hide'); 
		  
           var labrequest_approve_confirm = $(this).attr("id"); 
		             if(labrequest_approve_confirm != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_approve_confirm:labrequest_approve_confirm}, 
                     success:function(data){  
                          $('#approve_body').html(data);  
                          $('#approve_confirm_modal').modal('show');  
                     }  
                });  
           }            
      }); 
	  
	$(document).on('click', '.approve_results', function(){  
			
		  $('#approve_confirm_modal').modal('hide'); 
		  
           var labrequest_approve_no = $(this).attr("id"); 
		             if(labrequest_approve_no != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_approve_no:labrequest_approve_no}, 
                     success:function(data){ 
					 swal({ title: 'Approved!', text: 'Approved Successfully', timer: 1000 }) 
                          $('#referesh_cancel').html(data);  
                        //  $('#reject_confirm_modal').modal('show');  
                     }  
                });  
           }            
      }); 		  
		  	
	 
	  
		  
		  
		  
      $(document).on('click', '.view_results', function(){  
           var labrequest_no_results = $(this).attr("id"); 
		      var res = labrequest_no_results.split("__"); 
			  
		             if(labrequest_no_results != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_no_results:res[0]}, 
                     success:function(data){ 
					  
					 $('.modal-title').text('Result for: ' + res[1]); 
                          $('#view_result_body').html(data);  
                          $('#view_results_modal').modal('show');  
                     }  
                });  
           }            
      });  
	  
	  
      $(document).on('click', '.view_results_cancel', function(){  
           var labrequest_no_results_cancel = $(this).attr("id"); 
		      var res = labrequest_no_results_cancel.split("__"); 
			  
		             if(labrequest_no_results_cancel != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{labrequest_no_results_cancel:res[0]}, 
                     success:function(data){ 
					  
					 $('.modal-title').text('Result for: ' + res[1]); 
                          $('#view_result_body').html(data);  
                          $('#view_results_modal').modal('show');  
                     }  
                });  
           }            
      });  
	  
	 });  


  $(document).ready(function(){
    $('#myModal').on('show.bs.modal', function (e) {
        var labrequest_no_results = $(e.relatedTarget).data('id');
        $.ajax({  
                     url:"edit_rslt.php",  
                     method:"POST",  
                     data:{labrequest_no_results:labrequest_no_results}, 
                     success:function(data){  
                       //   $('#view_result_body').html(data); 
					      
                          $('#myModal').modal('show');
						   $('#view_results_modal').modal('hide');   
                     }  
                });
     });
});


		$(document).on('click', '.cancel_request', function(){  
				  
           var lab_requestCancel = $(this).attr("id"); 
		             if(lab_requestCancel != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{lab_requestCancel:lab_requestCancel}, 
                     success:function(data){  
                          $('#cancel_request_body').html(data);  
                          $('#cancel_req_confirm_modal').modal('show');  
                     }  
                });  
           }            
      }); 
	  
	  
		$(document).on('click', '.reorder_request', function(){  
				  
           var lab_request_reorder = $(this).attr("id"); 
		             if(lab_request_reorder != '')  
           {  
                $.ajax({  
                     url:"fetch_labtest.php",  
                     method:"POST",  
                     data:{lab_request_reorder:lab_request_reorder}, 
                     success:function(data){  
                          $('#reorder_request_body').html(data);  
                          $('#reorder_request_modal').modal('show');  
                     }  
                });  
           }            
      }); 	  
	  
			$(document).on('click', '.reorder_request_post', function(){  
				  
           var reorder_request_post_id = $(this).attr("id"); 
		             if(reorder_request_post_id != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{reorder_request_post_id:reorder_request_post_id}, 
                     success:function(data){ 
					 swal({ title: 'Success!', text: 'Reorder Successfully', timer: 250 }) 
                        
                          $('#reorder_request_modal').modal('hide');  
						 $('#referesh_cancel').html(data); 
                     }  
                }); 
           }            
      });   
	  
	  
	  
			$(document).on('click', '.yes_cancel_lab_request', function(){  
				  
           var yes_cancel_lab_req_ID = $(this).attr("id"); 
		             if(yes_cancel_lab_req_ID != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{yes_cancel_lab_req_ID:yes_cancel_lab_req_ID}, 
                     success:function(data){  
                         $('#test_fields').html(data);  
                          $('#cancel_req_confirm_modal').modal('hide');  
                     }  
                });  
           }            
      }); 
	  
	  
		$(document).on('click', '.scan_cancel', function(){  
          var lab_request_no = $(this).attr("id"); 
		  	 var res = lab_request_no.split("__");
			 
				$('#scan_request_no').val(res[0]);
				$('.modal-title').text('Investigation:  ' + res[1]); 
				$('#cancel_scan_modal').modal('show');  
				$('#scan_body').html(data); 
                
      });   
	  
		$(document).on('click', '.submit_approval', function(){  
          var lab_request_no2 = $(this).attr("id"); 
		  	 var res = lab_request_no2.split("__");
			 
				$('#scan_request_no2').val(res[0]);
				$('.modal-title').text('Investigation:  ' + res[1]); 
				$('#approve_scan_modal').modal('show');  
				$('#approve_scan_form').html(data); 
                
      });	
	  
	  $(document).on('click', '.collect_specimen', function(){
		    
					 var collect_specimen_lab_request_no = $(this).attr("id"); 
					 var res = collect_specimen_lab_request_no.split("__");
           $.ajax({  
					url:"fetch.php",  
					method:"POST",  
					data:{collect_specimen_lab_request_no:res[0],group_id:res[1]},
					
					success:function(data){
                           
						    $('.modal-title').text('Take Specimen for: ' + res[2]);
                          $('#take_speciment_Modal').modal('show');  
						$('#take_speciment_body').html(data);
						}  
           });  
      });


	  $(document).on('click', '.capture_investigation', function(){
		    
					 var capture_request_no = $(this).attr("id"); 
					 var res = capture_request_no.split("__");
           $.ajax({  
					url:"fetch.php",  
					method:"POST",  
					data:{capture_request_no:res[0],group_id:res[1]},
					
					success:function(data){
                           
						    $('.modal-title').text('Request Capture for: ' + res[2]);
                          $('#capture_modal').modal('show');  
						$('#capture_body').html(data);
						}  
           });  
      });

	
	  $(document).on('click', '.cancel_results', function(){  
		  $('#reject_confirm_modal').modal('hide'); 
      });
	  
	
	  $(document).on('click', '.cancel_approve', function(){  
		  $('#approve_confirm_modal').modal('hide'); 
      }); 
	
	  $(document).on('click', '.cancel_reorder', function(){  
		  $('#reorder_request_modal').modal('hide'); 
      });
	   
		  $(document).on('click', '.cancel_request_scrn', function(){  
		  $('#cancel_req_confirm_modal').modal('hide'); 
      }); 
	   
		  $(document).on('click', '.cancel_no', function(){  
		  $('#cancel_scan_modal').modal('hide'); 
      }); 	  
	  
	  
	  
	  ///=============================================================
	

	  

