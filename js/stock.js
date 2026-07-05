			
	$(document).on('click', '.add_stock', function(){  
			$('#add_stock_form')[0].reset(); 
			
			 $('.modal-title').text('Add New Stock');
			 $('#add_stock_modal').modal('show');  
  
	 }); 
	 
	$(document).on('click', '.add_cat', function(){  
			$('#add_category_form')[0].reset(); 
			 $('#add_category_modal').modal('show');  
  
	 }); 
	 
	 
	$(document).on('click', '.close_inven', function(){  
			$('#inventory_form')[0].reset(); 
			 $('#inventory_modal').modal('hide');  
	 });
	 	 
	 
	 $(document).on('click', '.mgt_inven', function(){ 
       var mgt_stock_id = $(this).attr("id"); 
		   var res = mgt_stock_id.split("__");	 
		    
           $.ajax({  
                url:"fetch_labtest.php",  
                method:"POST",  
                data:{mgt_stock_id:res[0]},  
                dataType:"json",  
                success:function(data){ 
				 	$('#mgt_old_qty').val(data.qty);
					$('#mgt_stock_id').val(data.stock_sn);  
					$('#mgt_unit').val(data.stock_total_unit); 
					$('#mgt_expired').val(data.expire_date);  
					 
	 $('.modal-title').text('Inventory Management for ' + res[1])
			 $('#inventory_modal').modal('show'); 
				//	  $('#inventory_form').html(data); 
                }  
           });  
      });

  
	  
	 $(document).on('click', '.edit_mode', function(){
		    
           var stock_id = $(this).attr("id");  
           $.ajax({  
                url:"fetch_labtest.php",  
                method:"POST",  
                data:{stock_id:stock_id},  
                dataType:"json",  
                success:function(data){ 
				 	$('#stockname').val(data.stock_name);
                    $('#category').val(data.cat);
					 $('#purchase_cost').val(data.buying_cost);
					 $('#batchno').val(data.batch_no);
					 $('#expired').val(data.expire_date);
					 $('#reorder').val(data.reorder_level);
					 $('#qty').val(data.qty); 
					 $('#units').val(data.stock_total_unit); 
					 $('#measurement').val(data.measurement); 
					 $('#stock_id').val(data.stock_sn);  
					 
					 
					 $('.modal-title').text('Edit Stock');
                     $('#add_stock_modal').modal('show');  
					  $('#add_stock_body').html(data); 
                }  
           });  
      });

	 
	 
	 
	 
	$('#add_stock_form').on("submit", function(event){  
           event.preventDefault(); 
		//              if($('#patient').val() == "")  
      //     {  
      //          swal("Patient Details is required");  
      //     }  
       ////     else  
         //  {  
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#add_stock_form').serialize(),  
                     beforeSend:function(){  
                          $('#add_btn').val("Adding");  
                     },  
                     success:function(data){  
			//swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
						// $('#specimen_notes').val("");
						//  $('#Specimen').val(""); 
                     $('#add_stock_modal').modal('hide');  
                          $('#test_fields').html(data);
						  
						  location.href="stocks.php?sv"  
						//   window.location.reload() ;
                     },
					 complete:function(){  
                          $('#insadd_requestert').val("Add Request");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
		 //  }
      });  


	$('#add_category_form').on("submit", function(event){  
           event.preventDefault(); 
		              if($('#cat').val() == "")  
           {  
                swal("Category is required");  
           }  
            else  
           {  
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#add_category_form').serialize(),  
                     beforeSend:function(){  
                          $('#add_cat').val("Adding");  
                     },  
                     success:function(data){  
			//swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
                  //   $('#add_category_modal').modal('hide');  
                   //       $('#test_fields').html(data);  
				
				 location.href="stocks.php?sv" 
				 
                     },
					 complete:function(){  
                          $('#cat').val("Added");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
		   }
      });   


	$('#inventory_form').on("submit", function(event){  
           event.preventDefault(); 
		  	if($('#qty_type').val() == "")  
           {  
                swal("Quantity Type is required");          
           }
		   else if ($('#qty').val() == "0")
		   {
			   swal("Quantity is required"); 
		   }
		   else if ($('#desc').val() == "")
		   {
			   swal("Description is required"); 
		   }
            else  
           {  
                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#inventory_form').serialize(),  
                     beforeSend:function(){  
                          $('#add_inven').val("Saving");  
                     },  
                     success:function(data){  
			//swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
                  //   $('#add_category_modal').modal('hide');  
                   //       $('#test_fields').html(data);  
				 location.href="stocks.php?sv" 
				 
                     },
					 complete:function(){  
                          $('#add_inven').val("Saved");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
		   }
      });  	  	 
	 
	$('#data_1 .input-group.date').datepicker({
		todayBtn: "linked",
		keyboardNavigation: false,
		forceParse: false,
		calendarWeeks: true,
		autoclose: true
	});
    
