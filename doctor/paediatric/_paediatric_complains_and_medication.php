<?php
if(isset($_POST['hospital_no'])){
session_start();
include("../../Connections/Conn.php");
include('../objects.php');
include('../helpers.php'); 

$appointment_number = $_POST['appointment_number'];
$hospital_no = $_POST['hospital_no'];
}

$PC = null;
$HPC = null;
$note_ = $Note->get(['app_no' => $appointment_number, 'hospital_no' => $hospital_no, 'notes_type' => 'C', 'tag' => 'DR']);

    if(!empty($note_)){
        $complains =   preg_split("/  /", $note_->notes);
        $PC = $complains[0];
        $HPC = $complains[1];
    }
?>
<h3 class="text-center">Paediatric Clinical Follow Up</h3>
<form  id="paediatric_followup_form">
<div class="panel well well-sm">
    <div class="panel-body">
        <div class="form_sep">
            <div style="color: #006; font-size:12px"><strong>Complaints </strong></div>
        </div>
        <br>
        <div>
            <table width="100%" cellpadding="5">
                <tr>
                    <td>
                        <div class="form_sep">
                            <label for="reg_textarea_message" class="req">Presenting Complaints Below [ Type Below ]</label>
                            <textarea name="PC" id="PC" cols="30" rows="3" class="form-control" data-required="true" data-minlength="10" style="font-size:15px"><?= $PC; ?></textarea>
                        </div>
                    </td>
                    <td>

                        <div class="form_sep">
                            <label for="reg_textarea_message" class="">History Presenting Complaints Below [ Type Below ]</label>
                            <textarea name="HPC" id="HPC" cols="30" rows="3" class="form-control" data-required="true" data-minlength="10" style="font-size:15px"><?= $HPC; ?></textarea>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>

<div class="panel well well-sm" >


    
<strong style="font-size:14px">Review of Systems</strong><small style="color:#F00;">&nbsp;<i>(Select appropriately)</i></small>
<div class="form_sep" id="data_1">
    <select data-placeholder="Choose ROS" class="chosen-select" name="review_of_system[]" multiple style="width:350px;" tabindex="4" required value="['Haemoptysis']">
        <option value="">Select</option>
        <option value="Irritability">CONSTITUTIONAL : Irritability</option>
        <option value="Inconsoble Crying">CONSTITUTIONAL : Inconsoble Crying</option>
        <option value="Fatigue (Tired)">CONSTITUTIONAL : Fatigue (Tired)</option>
        <option value="Fever">CONSTITUTIONAL : Fever</option>
        <option value="Night Sweats">CONSTITUTIONAL : Night Sweats</option>
        <option value="Weight Loss">CONSTITUTIONAL : Weight Loss</option>
        <option value="Appetite Loss">CONSTITUTIONAL : Appetite Loss</option>
        <option value="Diarrhoea">GASTROINTESTINAL : Diarrhoea</option>
        <option value="Nausea and/or Vomiting">GASTROINTESTINAL : Nausea and/or Vomiting</option>
        <option value="Oral lesion">GASTROINTESTINAL : Oral lesion</option>
        <option value="Pain/difficult swallowing">GASTROINTESTINAL : Pain/difficult swallowing</option>
        <option value="Abdominal pain">GASTROINTESTINAL : Abdominal pain</option>
        <option value="Productive Cough">CARDIO-RESPIRATORY : Productive Cough</option>
        <option value="Non Productive Cough">CARDIO-RESPIRATORY : Non Productive Cough</option>
        <option value="Haemoptysis">CARDIO-RESPIRATORY : Haemoptysis</option>
        <option value="Difficult breathing/SOB">CARDIO-RESPIRATORY : Difficult breathing/SOB</option>
        <option value="Dizziness">CARDIO-RESPIRATORY : Dizziness</option>
        <option value="Palpitation">CARDIO-RESPIRATORY : Palpitation</option>
        <option value="Swelling of legs">CARDIO-RESPIRATORY : Swelling of legs</option>
        <option value="Delayed milestone">NEUROLOGICAL : Delayed milestone</option>
        <option value="Encephalopathy">NEUROLOGICAL : Encephalopathy</option>
        <option value="Changes in developmental milestones">NEUROLOGICAL : Changes in developmental milestones</option>
        <option value="Headache">NEUROLOGICAL : Headache</option>
        <option value="Memory Problems">NEUROLOGICAL : Memory Problems</option>
        <option value="Visual Problems">NEUROLOGICAL : Visual Problems</option>
        <option value="Confusion">NEUROLOGICAL : Confusion</option>
        <option value="Numberness/Pain/burning in legs/feet">NEUROLOGICAL : Numberness/Pain/burning in legs/feet</option>
        <option value="Weakness in limbs">NEUROLOGICAL : Weakness in limbs</option>
        <option value="Seizures">NEUROLOGICAL : Seizures</option>
        <option value="GENTAL-URINARY">GENTAL-URINARY : GENTAL-URINARY</option>
        <option value="Haematuria">GENTAL-URINARY : Haematuria</option>
        <option value="Napkin dermatitis">OTHERS : Napkin dermatitis</option>
        <option value="Rash">OTHERS : Rash</option>
        <option value="Join pain/swelling">OTHERS : Join pain/swelling</option>
        


    </select>
</div>
<br>
<br>
<input type="hidden" name="action" value="saveData"> 
<input type="hidden" id="hospital_no" name="hospital_no" value="<?= $hospital_no;?>">
<input type="hidden" id="appointment_number" name="appointment_number" value="<?= $appointment_number;?>">



</div>
</form>
<p>
    <button   class="btn  btn-primary " onclick="saveData()" style="width:100% !important" >Save Data</button>
</p>
<hr>
<div>
<a href="#" class="btn btn-app " data-toggle="modal" data-target="#paediatric-medication-modal" id="<?php echo $hosp_no; ?>"><i class="fa fa-eyedropper"></i>Medication</a>
<a href="#" class="btn btn-app " data-toggle="modal" data-target="#paediatric-investigation-modal" id="<?php echo $hosp_no; ?>"><i class="fa fa-flask"></i>Investigation</a>
</div>



<!-- Modal -->
<div class="modal  fade" id="paediatric-medication-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="immunization-dialog" style="width: 70%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 380px">

            <?php
           
               // if(!empty($paediatric_history)){
                    
                    include_once('../components/_medications.php');
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
    <div class="immunization-dialog" style="width: 70%; margin: 0px auto">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body" id="paediatric_modal_boday" style="min-height: 380px">

            <?php
           
               // if(!empty($paediatric_history)){
                    
                    include_once('../components/_investigations.php');
                //}
            ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"  onclick="closeModal('paediatric-investigation-modal')">Close</button>
            </div>
        </div>
    </div>
</div>