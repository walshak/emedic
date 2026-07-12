<?php
if (isset($_POST['deleteVital'])) {
    session_start();
    include("../Connections/Conn.php");
    include('../doctor/objects.php');
    include('../doctor/helpers.php');
    $sn = intval($_POST['vital']);
    $del_stmt = $db->prepare("UPDATE  vital_sign SET status = '0' WHERE sn = ? AND status = '1'");
    $deleted = $del_stmt->execute([$sn]);
    if ($deleted) {
        $error_status = 2;
        echo 'Success : Vitals are deleted Successfully';
        exit;
    }
    echo 'Error: We could not remove these vitals';
    exit;
}


if (!isset($appointment_number)) {
    $appointment_number = null;
}
if (isset($_POST['loadVitalsHx'])) {
    session_start();
    include("../Connections/Conn.php");
    include('../doctor/objects.php');
    include('../doctor/helpers.php');
    $hospital_no = $_POST['hospital_no'];
}






$vitals__stmt = $db->prepare("SELECT * FROM vital_sign WHERE hospital_no = ? AND status = '1' ORDER BY sn DESC LIMIT 25");
$vitals__stmt->execute(array($hospital_no));
if ($vitals__stmt->rowCount() > 0) {
    $_vitals_  = $vitals__stmt->fetchAll(PDO::FETCH_ASSOC);
    $last_vitals = $_vitals_[0];
?>
    <div class="row">
        <div class="col-md-12">
            <p class="text-center"><i><b>Last Vitals:
                        Temp: <?= (!empty($last_vitals['temp']) ? $last_vitals['temp'] . '<sup>o</sup>C   | ' : ''); ?>
                        BP: <?= (!empty($last_vitals['bp']) ? $last_vitals['bp'] . 'mmHg   | ' : ''); ?>
                        Pulse: <?= (!empty($last_vitals['pulse_read']) ? $last_vitals['pulse_read'] . 'bpm   | ' : ''); ?>
                        Weight: <?= (!empty($last_vitals['weight']) ? $last_vitals['weight'] . 'kg   | ' : ''); ?>
                        Height: <?= (!empty($last_vitals['height']) ? $last_vitals['height'] . 'm   | ' : ''); ?>
                        FBS: <?= (!empty($last_vitals['fbs']) ? $last_vitals['fbs'] . 'mmol/L    ' : ''); ?>
                        BMI: <?= (!empty($last_vitals['bmi']) ? $last_vitals['bmi'] . 'kg/m&sup2;    ' : ''); ?>
                        <br>
                        Comments: <?= $last_vitals['comments']; ?>
                    </b></i></p>
            <h4>Vitals History</h4>
            <div style="overflow-x: auto; white-space: nowrap;">
                <table class="table dataTables-example table-responsive" border="2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Temp.</th>
                            <th>BP(mmHg)</th>
                            <th>Pulse</th>
                            <th>Resp.</th>
                            <th>Weight</th>
                            <th>Height</th>
                            <th>BMI</th>
                            <th>FBS</th>
                            <th>RBS</th>
                            <th>SPO2</th>
                            <th>PPBS</th>
                            <th>FHR</th>
                            <th>DATE</th>
                            <th>CAPTURED BY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ($_vitals_ as $key => $vital) {
                        ?>
                            <tr id="vit-tr-<?= $vital['sn']; ?>" style="">

                                <td><?= ($key + 1); ?></td>
                                <?php
                                $temp = $vital['temp'];
                                $bg = '#fff';

                                if (!empty($temp)) {
                                    $temp = $vital['temp'];
                                    if ($temp < 36.3) {
                                        $bg = '#CEF6EC';
                                    } elseif ($temp > 36.3 && $temp < 37.5) {
                                        $bg = '#01A9DB';
                                    } elseif ($temp > 37.5 && $temp < 38.4) {
                                        $bg = '#FE9A2E';
                                    } elseif ($temp > 38.4 && $temp < 38.9) {
                                        $bg = '#F78181';
                                    } else {
                                        $bg = '#FE2E2E';
                                    }
                                }

                                ?>
                                <td style="background:<?= $bg; ?> !important;color:white"><?php if (!empty($temp)) {
                                                                                                echo $temp . '<sup>o</sup>C';
                                                                                            } ?></td>
                                <?php
                                $bp = $vital['bp'];
                                $bg = '#fff';
                                if (!empty($bp)) {

                                    $bp_vals = explode('/', $bp);


                                    if ($bp_vals[0] >  120 || $bp_vals[1] > 80) {
                                        $bg =  'red';
                                    } else {
                                        $bg =  '#CEF6EC;color:green';
                                    }
                                }





                                ?>
                                <td style="background:<?= $bg; ?> !important;color:white"><?php if (!empty($bp)) {
                                                                                                echo $bp . '';
                                                                                            } ?></td>
                                <td class="text-center"><?= $vital['pulse_read']; ?></td>
                                <td class="text-center"><?= $vital['resp_rate']; ?></td>
                                <td class="text-center"><?= $vital['weight']; ?></td>
                                <td class="text-center"><?= $vital['height']; ?></td>
                                <td class="text-center"><?= $vital['bmi']; ?></td>
                                <td class="text-center"><?= $vital['fbs']; ?></td>
                                <td class="text-center"><?= $vital['rbs']; ?></td>
                                <td class="text-center"><?= $vital['spo2']; ?></td>
                                <td class="text-center"><?= $vital['ppbs']; ?></td>
                                <td class="text-center"><?= $vital['FHR']; ?></td>
                                <td class="text-center"><?php echo date('d M,Y h:i:s a', strtotime($vital['date_ap'])); ?>
                                    <br>
                                    <?php

                                    if (date_diff_day($vital['date_ap']) < 1 && $_SESSION['fullname'] == $vital['prepared_by']) {
                                        $sn = $vital['sn'];
                                    ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteVitals(<?= $sn; ?>)" id="vit-btn-<?= $sn; ?>">[ Delete ]</button>

                                    <?php
                                    }
                                    ?>

                                </td>
                                <td><?php echo $vital['prepared_by']; ?></td>
                            </tr>
                        <?php


                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
}

?>

<script src="../js/jquery-3.1.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../doctor/chart.js"></script>
<script>
    function deleteVitals(vital) {
        toastr.info('Deleting, pls wait...', '', {
            timeOut: 5000
        });
        if (confirmDelete()) {
            $.ajax({
                url: "../inc/_ward_round_vitals.php",
                method: "POST",
                data: {
                    vital,
                    deleteVital: true
                },
                success: function(data) {
                    toastr.success(data, 'Action Complete:', {
                        timeOut: 3000
                    })
                    $('#vit-btn-' + vital).fadeOut('slow');
                    $('#vit-tr-' + vital).css('color', 'red');
                    $('#vit-tr-' + vital).css('text-decoration', 'line-through');
                    // toastr.clear();
                }
            });
        }

    }

    function confirmDelete() {
        return confirm("Are you sure you want to delete this Vital Sign?");
    }
</script>