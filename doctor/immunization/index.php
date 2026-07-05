<?php
    if(!empty($hospital_no)){
        include_once('immunization/_immunization_modal.php');
    }else{
        ?>
            <script>
                alert('Oops! something went wrong...')
            </script>
        <?php
    }
    
?>
<script>

    $(document).ready(function() {
        $('#immunization-modal').modal('show');
    })
</script>