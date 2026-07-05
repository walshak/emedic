<script>
    var vitals_hx_url = "<?php echo $vitals_hx_url; ?>";
    $(document).ready(function() {
        setTimeout(function() {
            $.ajax({
                url: vitals_hx_url,
                method: "POST",
                data: {
                    loadVitalsHx: true,
                    hospital_no: "<?php echo $hospital_no; ?>",
                    current_page: 1
                },
                success: function(response) {
                    $("#vitals_hx__wrap").html(response)
                    //   console.log(response)
                },
                error: function(err) {
                    console.log(err)
                }
            });
        }, 500)

    })


    function load_more_vital(current_page_) {
        setTimeout(function() {
            $.ajax({
                url: vitals_hx_url,
                method: "POST",
                data: {
                    loadVitalsHx: true,
                    hospital_no: "<?php echo $hospital_no; ?>",
                    current_page: current_page_
                },
                success: function(response) {
                    $("#vitals_hx__wrap").html(response)
                    //   console.log(response)
                },
                error: function(err) {
                    console.log(err)
                }
            });
        }, 500)
    }
</script>