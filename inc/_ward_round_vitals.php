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
    $current_page = $_POST['current_page'];
    if ($current_page == '') {
        $current_page = 1;
    }
}

$records_per_page = 15;
$offset = ($current_page - 1) * $records_per_page;

$vitals__stmt = $db->prepare("SELECT * FROM vital_sign WHERE hospital_no = :hospital_no AND status = '1' ORDER BY sn DESC LIMIT :limit OFFSET :offset");
$vitals__stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$vitals__stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
$vitals__stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$vitals__stmt->execute();

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
                        <?php foreach ($_vitals_ as $key => $vital) { ?>

                            <tr id="vit-tr-<?= $vital['sn']; ?>">

                                <td><?= ($key + 1); ?></td>

                                <!-- ================= TEMPERATURE ================= -->
                                <?php
                                $temp = $vital['temp'];
                                $bg = '#fff';
                                $color = '#000';

                                if (!empty($temp)) {

                                    if ($temp < 36.0) {
                                        $bg = '#58ACFA'; // Low
                                    } elseif ($temp <= 37.5) {
                                        $bg = '#3ADF00'; // Normal
                                    } elseif ($temp <= 38.0) {
                                        $bg = '#F7FE2E'; // Mild
                                        $color = '#000';
                                    } elseif ($temp <= 39.0) {
                                        $bg = '#FE9A2E'; // Fever
                                    } else {
                                        $bg = '#FE2E2E'; // High fever
                                    }
                                }
                                ?>
                                <td style="background:<?= $bg ?>; color:<?= $color ?>;">
                                    <?= !empty($temp) ? $temp . '<sup>o</sup>C' : '' ?>
                                </td>

                                <!-- ================= BLOOD PRESSURE ================= -->
                                <?php
                                $bp = $vital['bp'];
                                $bg = '#fff';
                                $color = '#fff';

                                if (!empty($bp)) {

                                    $bp_vals = explode('/', $bp);
                                    $sys = isset($bp_vals[0]) ? (int)$bp_vals[0] : 0;
                                    $dia = isset($bp_vals[1]) ? (int)$bp_vals[1] : 0;

                                    if ($sys < 90 || $dia < 60) {
                                        $bg = '#58ACFA'; // Low

                                    } elseif ($sys <= 120 && $dia <= 80) {
                                        $bg = '#3ADF00'; // Normal

                                    } elseif ($sys <= 129 && $dia < 80) {
                                        $bg = '#F7FE2E'; // Elevated
                                        $color = '#000';
                                    } elseif (($sys >= 130 && $sys <= 139) || ($dia >= 80 && $dia <= 89)) {
                                        $bg = '#FE9A2E'; // Stage 1

                                    } elseif ($sys >= 140 || $dia >= 90) {
                                        $bg = '#FE2E2E'; // Stage 2
                                    }
                                }
                                ?>
                                <td style="background:<?= $bg ?>; color:<?= $color ?>;">
                                    <?= !empty($bp) ? $bp : '' ?>
                                </td>

                                <!-- ================= OTHER VITALS ================= -->
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

                                <!-- ================= DATE ================= -->
                                <td class="text-center">
                                    <?= date('d M,Y h:i:s a', strtotime($vital['date_ap'])); ?>
                                    <br>

                                    <?php
                                    if (date_diff_day($vital['date_ap']) < 1 && $_SESSION['fullname'] == $vital['prepared_by']) {
                                        $sn = $vital['sn'];
                                    ?>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="deleteVitals(<?= $sn; ?>)"
                                            id="vit-btn-<?= $sn; ?>">
                                            [ Delete ]
                                        </button>
                                    <?php } ?>
                                </td>

                                <td><?= $vital['prepared_by']; ?></td>

                            </tr>

                        <?php } ?>
                    </tbody>
                </table>

                <?php
                // Pagination controls
                $total_stmt = $db->prepare("SELECT COUNT(*) FROM vital_sign WHERE hospital_no = :hospital_no AND status = '1'");
                $total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                $total_stmt->execute();
                $total_records = $total_stmt->fetchColumn();

                // Calculate total pages
                $total_pages = ceil($total_records / $records_per_page);


                ?>

                <div class="pagination">
                    <?php if ($current_page > 1): $pg_no = $current_page - 1; ?>
                        <a href="#" onClick="load_more_vital('<?= $pg_no; ?>')">Previous</a>
                    <?php endif; ?>

                    <span>Page <?= $current_page; ?> of <?= $total_pages; ?></span>

                    <?php if ($current_page < $total_pages):  $pg_no = $current_page + 1; ?>
                        <a href="#" onClick="load_more_vital('<?= $pg_no; ?>')">Next</a>
                    <?php endif; ?>
                </div>



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