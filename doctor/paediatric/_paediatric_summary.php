<?php
if (isset($_POST['hospital_no'])) {
    
    $appointment_number = $_POST['appointment_number'];
    $hospital_no = $_POST['hospital_no'];
}

if(!isset($Paediatric)){
    session_start();
    include("../../Connections/Conn.php");
    include('../objects.php');
    include('../helpers.php');
    include('../objects.php');
    $appointment_number = $_POST['appointment_number'];
    $hospital_no = $_POST['hospital_no'];
}
$paediatric_history = $Paediatric->get(['hospital_no' => $hospital_no]);
$Patient_info = $Patient->get(['hospital_no' => $hospital_no]);



?>


<div class="p-10">
    <h2 class="text-center">Paediatric Initial History</h2>
    <table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40" border="2">
        <tr>
            <td><strong>HIV Initial Test</strong></td>
            <td><?php echo $paediatric_history->HIV_initial_test . '( ' . $paediatric_history->HIV_initial_test_rslt . ')'; ?></td>
        </tr>
        <tr>
            <td><strong>Birth Weight/Gestation at Birth:</strong></td>
            <td><?php echo $paediatric_history->birth_weight . 'kg' . ' / ' . $paediatric_history->gestation_at_birth; ?></td>
        </tr>
        <tr>
            <td><strong>Neonatal Complications/Delivery Mode:</strong></td>
            <td><?php echo $paediatric_history->neonatal_comp . ' / ' . $paediatric_history->delivery_mode; ?></td>
        </tr>
        <tr>
            <td><strong>Breast feeding:</strong></td>
            <td><?php echo $paediatric_history->breastfeeding; ?></td>
        </tr>
        <tr>
            <td><strong>Past Medical History:</strong></td>
            <td><?php echo $paediatric_history->past_med_hx; ?></td>
        </tr>
        <tr>
            <td><strong>TB History:</strong></td>
            <td><?php echo $paediatric_history->TB_drugs . ' / ' . $paediatric_history->TB_date . ' / ' . $paediatric_history->TB_type; ?></td>
        </tr>
        <tr>
            <td><strong>Current Medication:</strong></td>
            <td><?php echo $paediatric_history->current_med; ?></td>
        </tr>

        <tr>
            <td><strong>Allergies:</strong></td>
            <td><?php echo $paediatric_history->drug_allergies; ?></td>
        </tr>
    </table>

    <br>
    <div class="text-center">
        <button class="btn btn-primary" onclick="load_paediatric_complains_and_medication('<?= $hospital_no; ?>')">New Complain and Medications</button>
    </div>

    <div>
        <?php

        $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE hospital_no= ? and cat_type = 'R' ORDER BY sn DESC LIMIT 10 ");
        $stmt->execute(array($hospital_no));
        $ros_ = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));



        $stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no= ? and tag = 'C' ORDER BY sn DESC  LIMIT 10 ");
        $stmt->execute(array($hospital_no));
        $notes_ = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));



        if (count($notes_) > 0 || count($ros_) > 0) {
        ?>
            <br>
            <br>
            <h3 class="text-center">Previous Complains</h3>

            <table class="table" border="1">
                <tr>
                    <td>SN</td>
                    <td>Complains</td>
                    <td>Date Time</td>
                </tr>
                <?php
                $sn = 1;
                foreach ($ros_ as $key => $ros) {
                ?>
                    <tr>
                        <td><?= $sn++; ?></td>
                        <td><?= $ros->complain; ?></td>
                        <td><?= $ros->date_entry; ?></td>
                    </tr>
                <?php
                }

                foreach ($notes_ as $key => $notes) {
                ?>
                    <tr>
                        <td><?= $sn++; ?></td>
                        <td><?= $notes->notes; ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>

        <?php
        }

        ?>
    </div>
</div>