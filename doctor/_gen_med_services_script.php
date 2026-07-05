<script>
     var gen_med_services_hx_url = "<?php echo $gen_med_services_hx_url;?>";
	
	
	///	alert(gen_med_services_hx_url);
    $(document).ready(function() {
		setTimeout(function(){
			$.ajax({
            url: gen_med_services_hx_url,
            method: "POST",
            data: { gen_med_services_hx: true, hospital_no: "<?php echo $hospital_no;?>"},
            success: function(response) {
				
				
var element = document.getElementById("wait_loading");
if (element) {
  element.style.display = "none";
}
				
                $("#gen_med_services__wrap").html(response)
            //   console.log(response)
            },
            error: function(err){console.log(err)}
        });
		}, 500)

})
</script>