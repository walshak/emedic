<?php

 

?>

<div class="modal  fade" id="dialysis-view-details-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 50%; margin: 0px auto; ">

        <!-- Modal content-->
        <div class="modal-content">
        <form action="#" method="POST" id="subject" name="subject" enctype="multipart/form-data">
            <div class="modal-body" style="min-height: 400px">
            
                <h2> Dialysis Note</h2>
                <hr>
                    <?php 

                      
                        
                        if(isset($_POST['view_detials'])){
                            $id = $_POST['view_detials'];    
                        }
                     
                        $dialysis_info = $Dialysis->find($id);
                       
                    ?>
                    <h4>
                       <b> Patient Name: </b><?= $dialysis_info->patient_name; ?> <br>
                       <b> Patient No.: </b><?= $dialysis_info->hospital_no; ?> <br>
                    </h4>
                    <table width="100%" border="2">
                        
                        <tr>
                            <td> <b> Request Date: </b></td>
                            <td><?= $dialysis_info->request_date; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Request Type: </b></td>
                            <td><?= $dialysis_info->request_type; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Request By: </b></td>
                            <td><?= $dialysis_info->request_by; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Scheduled Date Time: </b></td>
                            <td><?= $dialysis_info->schedule_date.' | '.$dialysis_info->schedule_time; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Request Note: </b></td>
                            <td><?= $dialysis_info->request_note; ?> </td>
                        </tr>
                        
                    </table>
                    <hr>
                    <table width="100%" border="2">
                        <tr>
                            <td> <b> Performed Date Time: </b></td>
                            <td><?= $dialysis_info->performed_date.' | '.$dialysis_info->performed_time; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> UF: </b></td>
                            <td><?= $dialysis_info->uf; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Blood Flow: </b></td>
                            <td><?= $dialysis_info->blood_flow; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Pre Weight: </b></td>
                            <td><?= $dialysis_info->vp_pre_weight; ?> </td>
                        </tr>
                        <tr>
                            <td> <b> Post Weight: </b></td>
                            <td><?= $dialysis_info->ap_post_weight; ?> </td>
                        </tr>

                        <tr>
                            <td> <b> UFR: </b></td>
                            <td><?= $dialysis_info->ufr; ?> </td>
                        </tr>
                    </table>
           
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

            </div>

            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dialysis-view-details-modal').modal('show');
    })
</script>