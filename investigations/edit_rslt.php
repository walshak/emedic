<?php  include("../Connections/Conn.php");?>

<?php

if (isset($_POST["labrequest_no_results"])) {
    $stmt = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :labrequest_no ORDER BY sn");
    $stmt->bindParam(':labrequest_no', $_POST["labrequest_no_results"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

?>
 
         
        <script>
		
		 $('#fill_result_form').on("submit", function(event){  
           event.preventDefault(); 

                $.ajax({  
                     url:"insert.php",  
                     method:"POST",  
                     data:$('#fill_result_form').serialize(),  
                     beforeSend:function(){  
                          $('#Save').val("Saving");  
                     },  
                     success:function(data){  
			swal({ title: 'Success!', text: 'Added Successfully', timer: 1000 })
						 $('#preferred_specimen').val("");
                          $('#fill_result_Modal').modal('hide');  
                          //$('#test_fields').html(data);  
                     },
					 complete:function(){  
                          $('#insert').val("Insert");  
                     }, 
					error:function(data){
						
							alert("Oops...", "Something went wrong :(", "error");
							swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
					} 
                }); 
				
      });  
	   
      $(document).on('click', '.edit_result', function(){  
           var lab_rslt_editNo = $(this).attr("id"); 
		             if(lab_rslt_editNo != '')  
           {  
                $.ajax({  
                     url:"fetch.php",  
                     method:"POST",  
                     data:{lab_rslt_editNo:lab_rslt_editNo}, 
                     success:function(data){  
                       //   $('#view_result_body').html(data);  
                          $('#edit_result_modal').modal('show');  
                     }  
                });  
           }            
      }); 
	  
	  	   
	   
		</script>
        

    
