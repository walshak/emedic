<!-- Modal -->
<div class="modal  fade" id="opthalmology-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 60%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 600px">

                <?php
                if (isset($_GET['emr'])) {
                    include_once('opthalmology/_emr_print.php');
                } else {
                ?>
                    <div class="panel well well-sm">
                        <div>
                            <a href="#" class="btn btn-app " onclick="openOcularHx()" id="ocular_hx_btn" style="display:none"><i class="fa fa-eyedropper"></i>Ocular Review/hX</a>
                            <a href="#" class="btn btn-app " onclick="openOcularExamination()" id="ocular_examination_btn"><i class="fa fa-eyedropper"></i>Ocular Examination</a>
                            <a href="#" class="btn btn-app " data-toggle="modal" data-target="#paediatric-medication-modal" id="<?php echo $hosp_no; ?>"><i class="fa fa-eyedropper"></i>Medication</a>
                            <a href="#" class="btn btn-app " data-toggle="modal" data-target="#paediatric-investigation-modal" id="<?php echo $hosp_no; ?>"><i class="fa fa-flask"></i>Investigation</a>
                            <a href="?hosp_no=<?= $hospital_no; ?>&opth&emr" class="btn btn-app " ><i class="fa fa-print"></i>Print EMR</a>
                        </div>
                    </div>


                    <div class="table-responsive scroll_content" id="ocular_hx_div" style="max-height: 440px;overflow:auto">
                        <?php
                        $opth_list =  $Opthalmology->getList(['view_mode' => 'hx'], true);

                        ?>
                        <h2 class="text-center"> Ocular Review/History</h4>
                            <table class="table table-active table-hover table-bordered table-stripped" border="2" width="100%">
                                <tr>
                                    <th>SN</th>
                                    <th>EXAMINATION</th>
                                    <th>NOTES</th>
                                    <th></th>
                                </tr>
                                <?php
                                $sn = 1;
                                $index = 0;
                                foreach ($opth_list as $key => $list) {
                                    $hx =  $Opthalmology->get(['hospital_no' => $hospital_no, 'ocular_list' => $list->ocular_list]);
                                    $result = null;
                                    if (!empty($hx)) {
                                        if (!empty($hx->result)) {
                                            if ($hx->editorr == 0) {
                                                $result = $hx->result . ',  <strong>Left:</strong> ' . $hx->left . '  <strong>Right:</strong> ' . $hx->right;
                                            } else {
                                                $result = $hx->result;
                                            }
                                        }
                                    } else {
                                        $result = '';
                                    }

                                    $btn =  '<button class="btn btn-sm btn-info" onclick="openResult(' . $list->sn . ')">Add result</button>';
                                    echo '
                                            <tr>
                                            <td>' . $sn++ . '</td>
                                            <td>' . $list->ocular_list . '</td>
                                            <td id="ocular_result_' . $list->sn . '">' . $result . '</td>
                                            <td> ' . $btn . '</td>
                                        </tr>
                                            ';
                                }
                                ?>
                            </table>
                    </div>

                    <div class="table-responsive scroll_content" id="ocular_examination_div" style="max-height: 440px;overflow:auto; display:none">
                        <?php
                        $opth_list =  $Opthalmology->getList(['view_mode' => 'rp'], true);

                        ?>
                        <h2 class="text-center"> Ocular Examination</h4>
                            <table class="table table-active table-hover table-bordered table-stripped" border="2" width="100%">
                                <tr>
                                    <th>SN</th>
                                    <th>EXAMINATION</th>
                                    <th>NOTES</th>
                                    <th></th>
                                </tr>
                                <?php
                                $sn = 1;
                                $index = 0;
                                foreach ($opth_list as $key => $list) {
                                    $hx =  $Opthalmology->get(['hospital_no' => $hospital_no, 'ocular_list' => $list->ocular_list]);
                                    $result = null;
                                    if (!empty($hx)) {
                                        if (!empty($hx->result)) {
                                            if ($hx->editorr == 0) {
                                                $result = $hx->result . ',  <strong>Left:</strong> ' . $hx->left . '  <strong>Right:</strong> ' . $hx->right;
                                            } else {
                                                $result = $hx->result;
                                            }
                                        }
                                    } else {
                                        $result = '';
                                    }

                                    $btn =  '<button class="btn btn-sm btn-info" onclick="openResult(' . $list->sn . ')">Add result</button>';
                                    echo '
                                            <tr>
                                            <td>' . $sn++ . '</td>
                                            <td>' . $list->ocular_list . '</td>
                                            <td id="ocular_result_' . $list->sn . '">' . $result . '</td>
                                            <td> ' . $btn . '</td>
                                        </tr>
                                            ';
                                }
                                ?>
                            </table>
                    </div>
                <?php
                }
                ?>

            </div>
            <div class="modal-footer">
                <?php 
                    if(isset($_GET['emr'])){
                        ?>
                            <button class="btn btn-success btn-large" onclick="ClickheretoprintDiv('emr_print_area')"><i class="fa fa-print"></i> Print</button>
                            <a href="?hosp_no=<?= $hospital_no; ?>&opth" type="button" class="btn btn-info" >Back</a>
                        <?php
                    }
                ?>
                 <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal  fade" id="opthalmology_result_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 20%; margin: 0px auto">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-heading">
                <hr>

                <h4 class="text-center" id="opthalmology_result_heading"></h4>
                <hr />
            </div>
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 400px">

                <form action="#" method="post" id="result_comment_form">


                </form>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>





<?php
$opth_list =  $Opthalmology->all();

?>

<!-- Modal -->
<div class="modal  fade" id="paediatric-medication-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 80%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 400px">

                <?php

                // if(!empty($paediatric_history)){

                include_once('components/_medications.php');
                //}
                ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="closeModal('paediatric-medication-modal')">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal  fade" id="paediatric-investigation-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 80%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 400px">

                <?php

                // if(!empty($paediatric_history)){

                include_once('components/_investigations.php');
                //}
                ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="closeModal('paediatric-investigation-modal')">Close</button>
            </div>
        </div>
    </div>
</div>