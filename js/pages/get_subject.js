<!--Get Subject-->
function get_subject(batch_id)  
{  
    $("#loading").html('<img src="_layout/images/loading.gif"> loading...');  
    $.ajax({  
        type: "GET",  
        url: "inc/get_subject.php",  
        data: "id="+batch_id,  
        success: function(msg){  
            $("#subject_div").html(msg); 
			 $("#loading").html(''); 
        }  
    });  
}
<!--Get Subject-->