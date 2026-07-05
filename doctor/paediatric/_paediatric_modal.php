<!-- Modal -->
<div class="modal  fade" id="paediatric-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 60%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 600px">

            <?php
                $paediatric_history = $Paediatric->get(['hospital_no' => $hospital_no]);
           
                if(empty($paediatric_history)){
                    include_once('paediatric/_paediatric_history_form.php');
                }else{
                    
                    // include_once('paediatric/_paediatric_complains_and_medication.php');
                    include_once('paediatric/_paediatric_summary.php');
                }
            ?>

             


                <?php 
                    $paediatric_initial_hx_btn = empty($paediatric_history) ? 'none' : 'block';
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="paediatric_initial_hx_btn" style="display: <?= $paediatric_initial_hx_btn; ?>;" data-toggle="modal" data-target="#paediatric-hx-modal"> << Paediatric Initial Hx</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal  fade" id="paediatric-hx-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 60%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 400px">

            <?php
           
                if(!empty($paediatric_history)){
                    
                    include_once('paediatric/_paediatric_history.php');
                    include_once('paediatric/_paediatric_complains_and_medication.php');
                }
            ?>

                <?php 
                    $paediatric_initial_hx_btn = empty($paediatric_history) ? 'none' : 'block';
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
