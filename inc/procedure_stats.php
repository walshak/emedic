<style>
    .card-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-item {
        text-align: center;
        margin: 10px;
        cursor: pointer;
        box-shadow: inset;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #888;
    }

    .stat-label {
        font-size: 14px;
        font-weight: bold;
        color: #1c84c6;
    }
</style>

<div class="container mt-5">
    <?php
    // Initialize statistics
    $totalProcedures = 0;
    $doneAWeekAgo = 0;
    $toBeDoneAWeekTime = 0;
    $doneAMonthAgo = 0;
    $toBeDoneAMonthTime = 0;

    // Fetch data from the procedures table
    $sql = "SELECT * FROM procedures";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalProcedures = count($procedures);

    $current_date = new DateTime();

    foreach ($procedures as $procedure) {
        // Process performed_date
        if ($procedure['performed_date'] !== NULL) {
            $performed_date = new DateTime($procedure['performed_date']);
            $interval = $current_date->diff($performed_date);

            if ($interval->days == 7 && $interval->invert == 1) {
                $doneAWeekAgo++;
            } elseif ($interval->days == 30 && $interval->invert == 1) {
                $doneAMonthAgo++;
            }
        }

        // Process sDate (date to start procedure)
        if ($procedure['sDate'] !== NULL) {
            $start_date = new DateTime($procedure['sDate']);
            $interval = $current_date->diff($start_date);

            if ($interval->days <= 7 && $interval->invert == 0) {
                $toBeDoneAWeekTime++;
            } elseif ($interval->days <= 30 && $interval->invert == 0) {
                $toBeDoneAMonthTime++;
            }
        }
    }
    ?>
    <!-- Card Stats -->
    <div class="card">
        <div class="card-header text-white">
            <h4 class="card-title">STATISTICS</h4>
        </div>
        <div class="card-body">
            <div class="card-stats">
                <div class="stat-item" data-toggle="modal" data-target="#totalProceduresModal">
                    <div class="stat-label" style="font-size: 15px;"><?php echo $totalProcedures; ?> : &nbsp;Total Request(s)</div>
                </div>
                <div class="stat-item" data-toggle="modal" data-target="#doneAWeekAgoModal">
                    <div class="stat-label" style="font-size: 15px;"><?php echo $doneAWeekAgo; ?> : &nbsp;Done a Week Ago</div>
                </div>
                <div class="stat-item" data-toggle="modal" data-target="#toBeDoneAWeekTimeModal">
                    <div class="stat-label" style="font-size: 15px;"><?php echo $toBeDoneAWeekTime; ?> : &nbsp;To be Done in a Week</div>
                </div>
                <div class="stat-item" data-toggle="modal" data-target="#toBeDoneAMonthTimeModal">
                    <div class="stat-label" style="font-size: 15px;"><?php echo $toBeDoneAMonthTime; ?> : &nbsp;To be Done in a Month</div>
                </div>
                <div class="stat-item" data-toggle="modal" data-target="#doneAMonthAgoModal">
                    <div class="stat-label" style="font-size: 15px;"><?php echo $doneAMonthAgo; ?> : &nbsp;Done a Month Ago</div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <?php
    $sql = "SELECT p.*, pas.paystatus 
                FROM procedures p 
                JOIN patient_ap_services pas 
                ON p.sale_no = pas.sn AND p.hospital_no = pas.hospital_no AND p.service_id = pas.drug_sn 
                WHERE p.status = '1' AND p.pre_opt_notes_id IS NULL AND pas.paystatus = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $awaitingAnesthetics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalCount = count($awaitingAnesthetics);
    if ($totalCount > 0):
    ?>
        <h4 class="text-primary" style="color: red;">Anesthesia & Pre-Operation Notes Pending for Patient(s) Below: </h4>
        <table id="awaitingAnestheticsTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Hospital No</th>
                    <th>App No</th>
                    <th>Name</th>
                    <th>Procedure</th>
                    <th>Date Entry</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch and display data for Awaiting Anesthetics List
                $serial_no = 0;
                foreach ($awaitingAnesthetics as $procedure) {
                    $serial_no++;
                    echo "<tr>
                        <td>{$serial_no}</td>
                        <td>{$procedure['hospital_no']}</td>
                        <td>{$procedure['app_no']}</td>
                        <td>{$procedure['name']}</td>
                        <td>{$procedure['procedures']}</td>
                        <td>{$procedure['date_entry']}</td>
                        <td>
                            <a href='index.php?procedure&pr=" . base64_encode(base64_encode($procedure["sn"])) . "' class='btn btn-xs btn-primary" .  "'>&nbsp;View&nbsp;</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php
    $sql = "SELECT p.*, pas.paystatus FROM procedures p JOIN patient_ap_services pas ON p.sale_no = pas.sn AND p.hospital_no = pas.hospital_no AND p.service_id = pas.drug_sn WHERE p.status = '1' AND p.pre_opt_notes_id IS NOT NULL AND p.performed_date IS NULL AND pas.paystatus = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $awaitingProcedure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalCount = count($awaitingProcedure);

    if ($totalCount > 0):
    ?>
        <h4 class="text-primary" style="color: red;">Post-Operation Notes Pending for Patient(s) Below: </h4>
        <table id="awaitingProcedureTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Hospital No</th>
                    <th>App No</th>
                    <th>Name</th>
                    <th>Procedure</th>
                    <th>Date Entry</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serial_no = 0;
                foreach ($awaitingProcedure as $procedure) {
                    $serial_no++;
                    echo "<tr>
                        <td>{$serial_no}</td>
                        <td>{$procedure['hospital_no']}</td>
                        <td>{$procedure['app_no']}</td>
                        <td>{$procedure['name']}</td>
                        <td>{$procedure['procedures']}</td>
                        <td>{$procedure['date_entry']}</td>
                        <td>
                            <a href='index.php?procedure&pr=" . base64_encode(base64_encode($procedure["sn"])) . "' class='btn btn-xs btn-primary" .  "'>&nbsp;View&nbsp;</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>
<!-- Modals -->
<!-- Total Procedures Modal -->
<div class="modal fade" id="totalProceduresModal" tabindex="-1" role="dialog" aria-labelledby="totalProceduresModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="totalProceduresModalLabel">Total Procedures Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Fetch and display total procedures list -->
                <table class="table table stripped table-bordered table-active table-hover">
                    <tr>
                        <th>#</th>
                        <th>Procedure</th>
                        <th></th>
                    </tr>

                    <?php
                    $serial_no = 1;
                    foreach ($procedures as $procedure) { ?>
                        <tr>
                            <td><?= $serial_no++; ?></td>
                            <td><?= "{$procedure['name']} - {$procedure['procedures']}"; ?></td>
                            <td> <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-success">&nbsp;View&nbsp;</a></td>
                        </tr>


                    <?php }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Done a Week Ago Modal -->
<div class="modal fade" id="doneAWeekAgoModal" tabindex="-1" role="dialog" aria-labelledby="doneAWeekAgoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doneAWeekAgoModalLabel">Done a Week Ago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Fetch and display procedures done a week ago list -->
                <table class="table table stripped table-bordered table-active table-hover">
                    <tr>
                        <th>#</th>
                        <th>Procedure</th>
                        <th></th>
                    </tr>

                    <?php
                    $serial_no = 1;
                    foreach ($procedures as $procedure) {
                        $performed_date = new DateTime($procedure['performed_date']);
                        $interval = $current_date->diff($performed_date);
                        if ($interval->days == 7 && $interval->invert == 1) { ?>
                            <tr>
                                <td><?= $serial_no++; ?></td>
                                <td><?= "{$procedure['name']} - {$procedure['procedures']}"; ?></td>
                                <td> <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-success">&nbsp;View&nbsp;</a></td>
                            </tr>


                    <?php }
                    }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- To be Done in a Week Modal -->
<div class="modal fade" id="toBeDoneAWeekTimeModal" tabindex="-1" role="dialog" aria-labelledby="toBeDoneAWeekTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toBeDoneAWeekTimeModalLabel">To be Done in a Week</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Fetch and display procedures to be done in a week list -->
                <table class="table table stripped table-bordered table-active table-hover">
                    <tr>
                        <th>#</th>
                        <th>Procedure</th>
                        <th></th>
                    </tr>

                    <?php
                    $serial_no = 1;
                    foreach ($procedures as $procedure) {
                        $start_date = new DateTime($procedure['sDate']);
                        $interval = $current_date->diff($start_date);
                        if ($interval->days <= 7 && $interval->invert == 0) { ?>
                            <tr>
                                <td><?= $serial_no++; ?></td>
                                <td><?= "{$procedure['name']} - {$procedure['procedures']}"; ?></td>
                                <td> <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-success">&nbsp;View&nbsp;</a></td>
                            </tr>


                    <?php }
                    }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- To be Done in a Month Modal -->
<div class="modal fade" id="toBeDoneAMonthTimeModal" tabindex="-1" role="dialog" aria-labelledby="toBeDoneAMonthTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toBeDoneAMonthTimeModalLabel">To be Done in a Month</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Fetch and display procedures to be done in a month list -->
                <table class="table table stripped table-bordered table-active table-hover">
                    <tr>
                        <th>#</th>
                        <th>Procedure</th>
                        <th></th>
                    </tr>

                    <?php
                    $serial_no = 1;
                    foreach ($procedures as $procedure) {
                        $start_date = new DateTime($procedure['sDate']);
                        $interval = $current_date->diff($start_date);
                        if ($interval->days <= 30 && $interval->invert == 0) { ?>
                            <tr>
                                <td><?= $serial_no++; ?></td>
                                <td><?= "{$procedure['name']} - {$procedure['procedures']}"; ?></td>
                                <td> <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-success">&nbsp;View&nbsp;</a></td>
                            </tr>


                    <?php }
                    }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Done a Month Ago Modal -->
<div class="modal fade" id="doneAMonthAgoModal" tabindex="-1" role="dialog" aria-labelledby="doneAMonthAgoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doneAMonthAgoModalLabel">Done a Month Ago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Fetch and display procedures done a month ago list -->
                <table class="table table stripped table-bordered table-active table-hover" border="2" width="100%">
                    <tr>
                        <th>#</th>
                        <th>Procedure</th>
                        <th></th>
                    </tr>

                    <?php
                    $serial_no = 1;
                    foreach ($procedures as $procedure) {
                        $performed_date = new DateTime($procedure['performed_date']);
                        $interval = $current_date->diff($performed_date);
                        if ($interval->days == 30 && $interval->invert == 1) { ?>
                            <tr>
                                <td><?= $serial_no++; ?></td>
                                <td><?= "{$procedure['name']} - {$procedure['procedures']}"; ?></td>
                                <td> <a href="index.php?procedure&pr=<?= base64_encode(base64_encode($procedure["sn"])); ?>" class="btn btn-xs btn-success">&nbsp;View&nbsp;</a></td>
                            </tr>


                    <?php }
                    }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#awaitingAnestheticsTable').DataTable();
        $('#awaitingProcedureTable').DataTable();
    });
</script>