
<script>

$(document).ready(function(){	 
	 var input = document.getElementById("search");
input.addEventListener("keyup", function(event) {
  if (event.keyCode === 13) {
   event.preventDefault();
    document.getElementById("apply_action").click();
  }
});
}); 
	 
	 
	 
	// $(document).ready(function(){
	function search_patient_x()
		{ 
			document.getElementById('apply_action').innerHTML='Wait ..';
			document.getElementById("apply_action").disabled = true;
			
			var text = document.getElementById("search").value;
			let search_detials = text.replace(/^\s+|\s+$/gm,'');
		
			let length = search_detials.length;
			
			if(length<4){
				toastr.error('Search must be more than 4 characters', 'Invalid Data ', {timeOut: 5000});
							document.getElementById('apply_action').innerHTML='Search';
									document.getElementById("apply_action").disabled = false;
				return 0;
				
			}
			$.ajax({
					url:"../search_patient_code.php",
					method:"POST",
					data:{search_detials:search_detials},
					success:function(data){

			setTimeout(function(){$("#overlay").fadeOut();},500);
				
					if(data.redirect != undefined){
						window.location = 'patient.php?&hosp_no='+data.hosp_no ;
					}else{

						document.getElementById('apply_action').innerHTML='Search';
							document.getElementById("apply_action").disabled = false;
							
						if(data.trim() == 'NotFound'){
							toastr.error('Not match found', 'Error', {timeOut: 5000})
						}else{
							$('.modal-title').text('Search Patient');   
					$('#patient_search_body').html(data); 
					$('#patient_search_modal').modal('show'); 
						}
						
				
				
					}
						
						
					 }
				});


}
	
	</script>