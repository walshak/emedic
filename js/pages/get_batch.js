<!--Get Batch-->
function get_batch(course_id)  
{  
    $("#loading").html('<img src="_layout/images/loading.gif"> loading...');  
    $.ajax({  
        type: "GET",  
        url: "inc/get_batch.php",  
        data: "id="+course_id,  
        success: function(msg){  
            $("#batch_div").html(msg); 
			 $("#loading").html(''); 
        }  
    });  
}
<!--Get Batch-->