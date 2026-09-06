<?php

$patient_procedure_stmt = $db->prepare("SELECT * from procedures WHERE hospital_no = ? AND sn=? ORDER BY sn DESC");
$patient_procedure_stmt->execute(array($hospital_no, $procedure_sn));
if ($patient_procedure_stmt->rowCount() > 0) {
?>


    <?php
    $procedure = $patient_procedure_stmt->fetch(PDO::FETCH_ASSOC);

    // foreach ($procedures as $key  => $procedure) {
    $procedure_sn = $procedure["sn"];
    $p_app_no = $procedure["app_no"];
    $sn = 1;
    $amount = 0;
    $d1 = strtotime($procedure["sDate"]);
    $d2 = strtotime(date('Y-m-d h:i:s'));
    $totalSecondsDiff = $d1 - $d2;
    $day =  ceil($totalSecondsDiff / (3600 * 24));
    $week =  floor($totalSecondsDiff / (3600 * 24 * 7));
    $paystatus = 0;

    $stmt = $db->prepare("SELECT * from patient_ap_services WHERE hospital_no = $hospital_no AND app_no = '$p_app_no' LIMIT 1");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $procedure_service = $stmt->fetch(PDO::FETCH_ASSOC);
        $amount = $procedure_service["pay"];
        $paystatus = $procedure_service["paystatus"];
    }

    // foreach ($procedure_services as $key  => $procedure_service) {

    // }


    // }
    ?>
    <?php
    if ($procedure["status"] == 0) {


        // if ($totalSecondsDiff > 0   ) {
        if ($paystatus == 0) {
            echo '<a href="procedures.php?hosp_no=' . $hospital_no . '&pr=' . base64_encode($procedure_sn) . '&cancel" class="btn btn-danger btn-xs "> Cancel</a>';
            echo ' | <a href="procedures.php?hosp_no=' . $hospital_no . '&pr=' . base64_encode($procedure_sn) . '&edit" class="btn btn-info btn-xs "> Edit</a>';
        } else {
    ?><a href="#" class="btn btn-default btn-xs " onclick="lauchPrint('printable_area')"> Print </a>
            | <a href="procedures.php?<?= 'hosp_no=' . $hospital_no . '&pr=' . base64_encode($procedure_sn) . '&optnotes'; ?>" class="btn btn-primary btn-xs "> Add Data</a><?php
                                                                                                                                                                        }
                                                                                                                                                                    } else if ($procedure["status"] == 1) {
                                                                                                                                                                        echo ' <a href="procedures.php?view" class="btn btn-primary btn-xs "> View </a>';
                                                                                                                                                                    }; ?>

    <div id="printable_area">
        <table class="table  table-stripped table-active" border="2">

            <tr>
                <td colspan="4">
                    <table>
                        <tr>
                            <td style=" padding-right:10px;">
                                <img src="<?php if (file_exists(enrollee_p . $hospital_no . '.' . 'jpg')) {
                                                echo enrollee_p . $hospital_no . '.' . 'jpg';
                                            } else {
                                                echo '../img/no_photo.jpg';
                                            } ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar">
                            </td>
                            <td>
                                <span style="font-size:18px"><?php echo $hospital_no; ?></span>
                                <span style="font-size:25px"><?php echo ' / ' . $patient_name; ?>
                                </span>

                                <br>

                                <table class="table border">
                                    <tr>
                                        <td><strong>Gender:</strong>&nbsp; <?= $patient_info->gender; ?></td>
                                        <td><strong>Age:&nbsp; <?= $patient_info->gender; ?></strong></td>
                                        <td><strong>Blood/Group:&nbsp; <?= $patient_info->blood_g; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Genotype:</strong> <?= $patient_info->geno_type; ?><br>
                                        </td>

                                        <td><strong>Marital Status:</strong><br>
                                        </td>

                                        <td><strong>Insurance Type:</strong> <?= $patient_info->insurance; ?><br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <h4 class="text-center">Procedure Information</h4>
                </td>
            </tr>
            <tr>
                <td><b>Procedure Type:</b> </td>
                <td><?= $procedure_name; ?> </td>
                <td><b>Date Added: </b></td>
                <td><?= dateFormat_($date_requested); ?> </td>
            </tr>
            <tr>
                <td><b>Procedure Amount: </b> </td>
                <td><?= $amount; ?> </td>
                <td><b>Requested By: </b></td>
                <td><?= $prepared_by; ?> </td>
            </tr>
            <tr>
                <td><b>Anaesthetia Type: </b> </td>
                <td><?= $procedure_anaesthetia_type; ?> </td>
                <td><b>Result/OutCome: </b></td>
                <td><?= !empty($procedure['post_op_results']) ? $procedure['post_op_results'] : $procedure['Findings']; ?> </td>
            </tr>
            <tr>
                <td><b>Indication: </b> </td>
                <td colspan="3"><?= $procedure_indication; ?> </td>
            </tr>
            <tr>
                <td><b>Incision: </b> </td>
                <td colspan="3"><?= $procedure_Incision; ?> </td>
            </tr>
            <tr>
                <td><b>Findings: </b> </td>
                <td colspan="3"><?= $procedure_Findings; ?> </td>
            </tr>
            <tr>
                <td colspan="4">
                    <h4 class="text-center">Resource Persons</h4>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <table class="table" width="100%">
                        <tr>
                            <th>SN</th>
                            <th>NAME</th>
                            <th>ROLE</th>
                            <th></th>
                        </tr>
                        <?php
                        $check_stmt = $db->prepare("SELECT * FROM procedure_resources where prdure_sn= ? ");
                        $check_stmt->execute(array($procedure_sn));
                        if ($check_stmt->rowCount() > 0) {
                            $sn = 1;
                            $procedure_resources = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($procedure_resources as $key => $procedure_resource) {
                                $rsn = $procedure_resource["sn"];
                                $prdure_sn = $procedure_resource["prdure_sn"];
                                $name = $procedure_resource["name"];
                                $role = $procedure_resource["role"];

                        ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td><?= $name; ?></td>
                                    <td><?= $role; ?> </td>
                                    <td>

                                        <form action="procedures.php" method="get">
                                            <input type="hidden" name="hosp" value="<?= $hospital_no; ?>">
                                            <input type="hidden" name="pr" value="<?= base64_encode($prdure_sn); ?>">
                                            <input type="hidden" name="rsn" value="<?= $rsn; ?>">
                                            <input type="hidden" name="optnotes" value="<?= base64_encode('optnotes'); ?>">
                                            <input type="submit" name="remove" class="btn remove-donor-btn" value="Remove">
                                        </form>


                                    </td>
                                </tr>
                        <?php
                            }
                        }

                        ?>
                    </table>
                </td>
            </tr>
        </table>

    </div>





<?php
} else {
    echo '<span class="text-danger">No Precedure Histories</span>';
}

?>