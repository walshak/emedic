  
<script>
  
var drug_plan_hx_url = "<?php echo $drug_plan_hx_url;?>";
$(document).ready(function() {
    $("#drug_hx_tbl").html('<h6>Loading, please wait...</h6>');
    setTimeout(function(){
			$.ajax({
            url: drug_plan_hx_url,
            method: "POST",
            data: { loadDrugHx: true, hospital_no: "<?php echo $hospital_no;?>", appointment_number: "<?php echo $appointment_number;?>"},
            success: function(response) {
              $("#drug_hx_tbl").html(response);

             
            },
            error: function(err){console.log(err)}
        });
		}, 1000)

})

</script>