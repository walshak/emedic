<div class="modal fade" id="printDialysisModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="immunization-dialog" style="width: 40%; margin: 0px auto; ">

        <!-- Modal content-->
        <div class="modal-content">

            <div id="printable_area" style="padding: 20px;">
                <?php
                $hospital_info = $Hospital->get([]);
                if (!empty($hospital_info[0])) {
                    echo ' 
                         <p class="text-center"> <img src="../img/logo.png" width="100px"> </p>
                        <h4 class="text-center"> ' . $hospital_info[0]->name . ' <br> ' . $hospital_info[0]->address . ' </h4> 
                  
                       ';

                    $performed_date = '';
                    if (!empty($dialysis_data_info->performed_date)) {
                        $performed_date = date('d/m/Y', strtotime("$dialysis_data_info->performed_date"));
                    }
                }

                ?>
                <h2 class="text-center" style="font-family: Verdana, Geneva, Tahoma, sans-serif;">Dialysis Report</h2>
                <table class="table" border="2" width="100%">
                    <tr>
                        <td colspan="2"><b>Name: </b> <?= $patient_name; ?></td>
                        <td><b>Sex: </b> <?= $sex; ?></td>
                    </tr>
                    <tr>
                        <td><b>Date/Time:</b> <?= $performed_date . ' ' . $dialysis_data_info->performed_time; ?></td>
                        <td><b>Duration:</b> <?= $dialysis_data_info->Duration; ?></td>
                        <td><b>Shift:</b> <?= $dialysis_data_info->Duty_Shift; ?></td>
                    </tr>
                    <tr>
                        <td><b>BP (mmHg):</b> <?= $dialysis_data_info->bp; ?></td>
                        <td><b>Pulse (b/min):</b> <?= $dialysis_data_info->pulse; ?></td>
                        <td><b>PCV:</b> <?= $dialysis_data_info->PCV; ?></td>
                    </tr>
                    <tr>
                        <td><b>UF:</b> <?= $dialysis_data_info->uf; ?></td>
                        <td><b>Blood Flow:</b> <?= $dialysis_data_info->blood_flow; ?></td>
                        <td><b>Pre Weight:</b> <?= $dialysis_data_info->vp_pre_weight; ?></td>
                    </tr>
                    <tr>
                        <td><b>Post Weight:</b> <?= $dialysis_data_info->ap_post_weight; ?></td>
                        <td><b>UFR (m/s/hr):</b> <?= $dialysis_data_info->ufr; ?></td>
                        <td><b>HEP:</b> <?= $dialysis_data_info->hep; ?></td>
                    </tr>
                    <tr>
                        <td><b>Access: </b> <?= $dialysis_data_info->Access; ?></td>
                        <td><b>Dialyzer:</b> <?= $dialysis_data_info->Dialyzer; ?></td>
                        <td><b>SPO2:</b> <?= $dialysis_data_info->SPO2; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Diagnosis: </b> <?= $dialysis_data_info->Diagnosis; ?></td>
                        <td><b>Fluid Loss: </b> <?= $dialysis_data_info->fluid_loss; ?></td>
                    </tr>
                    <tr>
                        <td colspan="3"><b>Intradialysis Complication:</b> <?= $dialysis_data_info->complication; ?> </td>
                    </tr>
                    <tr>
                        <td colspan="3"><b>Dialysis Notes:</b> <?= $dialysis_data_info->dialysis_note; ?> </td>
                    </tr>
                </table>
                <p class="text-right"><b><?= $dialysis_data_info->Name_Nurse; ?> <br> <?= date('d M, Y h:i:sA'); ?></b></p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="ClickheretoprintDiv('printable_area')"> Print </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>

</div>
</div>