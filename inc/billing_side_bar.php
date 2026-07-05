<div class="sidebard-panel">
    <br>
    <span class="pull-left"><a href="<?php if ($rights == "PH") { ?>../pharmacy/index.php<?php } elseif ($rights == "NS") { ?>../nursing/index.php<?php } else { ?>../admin/index.php<?php } ?>" class="btn btn-danger btn-lg"> <i class="fa fa-times"></i> &nbsp; Close Window</a></span>
    <hr>
    <br>


    <?php
    if (isset($_SESSION['username'])) {
        $usern = $_SESSION['username'];

        $stmt_inbox = $db->prepare("SELECT * FROM mails WHERE on_contact_username = :usern AND mail_status = 'send' AND read_status = '0' ORDER BY sn DESC LIMIT 10");
        $stmt_inbox->bindParam(':usern', $usern, PDO::PARAM_STR);
        $stmt_inbox->execute();

        $newemail = $stmt_inbox->rowCount();
    } else {
        $newemail = 0;
    }
    ?>

    <div>
        <h4>Messages <span class="badge badge-info pull-right"><?php echo $newemail; ?></span></h4>
        <?php if ($newemail > 0): ?>
            <?php while ($row_i = $stmt_inbox->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="feed-element">
                    <a href="#" class="pull-left">
                        <img alt="image" class="img-circle" src="../img/mail.jpg">
                    </a>
                    <div class="media-body">
                        <?php
                        echo empty($row_i['subject']) ? 'No Subject' : htmlspecialchars(substr($row_i['subject'], 0, 50));
                        ?>
                        <br>
                        <small class="text-muted">
                            <?php
                            include_once("dd.php");
                            echo getTheDay($row_i['mail_date']) . ' ago';
                            ?>
                        </small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <hr>

    <?php if ($shw_side == 'yes') { ?>

        <div class="m-t-md">
            <h4 id="msg">Alerts & Information</h4>
            <div>
                <ul class="list-group">


                    <li class="list-group-item">
                        <span class="badge badge-danger" id="dob_count" style="font-size: 14px;"></span>
                        <h3 style="color:red; ">Birthday Alert:</h3>
                        <div id="dob_count"></div>


                        <input type="button" name="eidt_gd" value="View Celebrating Patient(s)" data-target="#modal" id="<?php echo '1'; ?>" class="btn btn-success btn-sm view_birthday_request" />

                    </li>

                    <hr>

                    <li class="list-group-item">
                        <span class="badge badge-danger" id="see_specialist_count" style="font-size: 14px;"></span>
                        <strong id="make_me_blink">Specialist Consult(s):</strong>
                        <div id="see_specialist_count_count"></div>
                        <input type="button" name="eidt_gd" value="View Request" data-target="#modal" id="<?php echo '1'; ?>" class="btn btn-warning btn-xs view_speccialist_request" />
                    </li>

                    <?php if ($_SESSION['rights'] == 'RE') { ?>
                        <span class="badge badge-primary" id="see_test_results_count" style="font-size: 14px;">0</span>
                        <strong id="make_me_blink2">New Results Available:</strong>
                        <audio id="alertSound">
                            <source src="../sounds/notification.wav" type="audio/wav">
                            <source src="../sounds/notification.mp3" type="audio/mpeg">
                        </audio>
                        <input type="button" name="" value="View New Results" data-target="#modal" id="" class="btn btn-success btn-xs view_results_available" />
                        <br>
                        <br>
                    <?php } ?>

                    <li class="list-group-item">
                        <input type="button" value="Upcoming Procedures" class="btn btn-success btn-sm" onclick="check_for_procedures_front_deck()" />
                    </li>



                    <a data-toggle="modal" data-target="#myModal5" class="btn btn-info btn-sm add_consults_room"><strong>Add Consult Room & Assign</strong></a>



                    <?php if ($_SESSION['biller'] == 1) { ?>
                        <li class="list-group-item ">
                            <span class="badge badge-info" id="All_patient_cr" style="font-size: 14px;"></span>
                            <strong>Total Credits:</strong>
                            <div id="All_patient_cr"></div>

                            <input type="button" name="eidt_gd" value="View Creditors" data-target="#modal" id="view_admitted_patient_bal1" class="btn btn-danger btn-xs view_admitted_patient_bal" />
                            <label id="notification_label">
                                <input type="checkbox" id="view_admitted_patient_visble" onclick="view_admitted_patient_bal_open()" />
                                Unhide Notification
                            </label>


                        </li>
                    <?php } ?>
                    <br>

                    <hr>

                    <?php if ($bill_account_status == 1) { ?>
                        <li class="list-group-item">

                            <h2 style="color: red;"><strong>Total <u>Amount</u> Billed to your ACCOUNT: </strong></h2>
                            <p style="font-size: 20px;" id="bill_account_amount"></p>


                            <input type="button" name="eidt_gd" value="View Beneficiary" data-target="#modal" id="<?php echo $ECode_logged; ?>" class="btn btn-warning bill_account_bene" />

                        </li>
                </ul>
            <?php } ?>



            <!-- Modal -->
            <div class="modal fade" id="testResultsModal">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>New Test Results</h4>
                        </div>

                        <div class="modal-body" style="overflow-x:scroll;overflow-y:scroll;width:auto; height:350px;">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Patient</th>
                                        <th>Test</th>
                                        <th>Staff</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="test_results_body"></tbody>
                            </table>
                        </div>


                        <!-- Modal footer with close button -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($_SESSION['Procurement_Officer_ack']) && $_SESSION['Procurement_Officer_ack'] == 1): ?>
                <?php
                // Store Requests
                $stmt_inbox = $db->query("SELECT DISTINCT batch_no FROM stock_table_procurment WHERE approval_stages = 1 AND navigation = 'Store'");
                $store_count = $stmt_inbox->rowCount();
                if ($store_count > 0): ?>
                    <div>
                        <h4 style="color: red;">
                            <strong>Total P.O Request(s) for Approval: <?= $store_count; ?></strong>
                        </h4>
                        <a href="index.php?stock=o&pdr&procurement_manager_ack">View Store & Acknowledge</a>
                    </div>
                <?php endif; ?>

                <?php
                // Pharmacy Requests
                $stmt_inbox = $db->query("SELECT DISTINCT batch_no FROM stock_table_procurment WHERE approval_stages = 1 AND navigation = 'Pharmacy'");
                $pharmacy_count = $stmt_inbox->rowCount();
                if ($pharmacy_count > 0): ?>
                    <div>
                        <h4 style="color: red;">
                            <strong>Total P.O Request(s) for Approval: <?= $pharmacy_count; ?></strong>
                        </h4>
                        <a href="index.php?stock=p&pdr&procurement_manager_ack">View Pharmacy & Acknowledge</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['purchase_approval']) && $_SESSION['purchase_approval'] == 1): ?>
                <?php
                // Store Approvals
                $stmt_inbox = $db->query("SELECT DISTINCT batch_no FROM stock_table_procurment WHERE approval_stages = 2 AND status = 'no' AND navigation = 'Store'");
                $store_approval_count = $stmt_inbox->rowCount();
                if ($store_approval_count > 0): ?>
                    <div>
                        <h4>
                            <span class="blink">
                                <strong>Total P.O Store <br>Request(s): <?= $store_approval_count; ?></strong>
                            </span>
                        </h4>
                        <a href="index.php?stock=o&pdr&approve_for_accountant">Approve for Accountant</a>
                    </div>
                <?php endif; ?>

                <?php
                // Pharmacy Approvals
                $stmt_inbox = $db->query("SELECT DISTINCT batch_no FROM stock_table_procurment WHERE approval_stages = 2 AND status = 'no' AND navigation = 'Pharmacy'");
                $pharmacy_approval_count = $stmt_inbox->rowCount();
                if ($pharmacy_approval_count > 0): ?>
                    <div>
                        <h4 style="color: red;">
                            <strong>Total P.O Pharmacy <br>Request(s): <?= $pharmacy_approval_count; ?></strong>
                        </h4>
                        <a href="index.php?stock=p&pdr&approve_for_accountant">Approve for Accountant</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>



            <br>
            <br>
            <br>
            <?php if ($_SESSION['webmedic_admin_right'] == 1) { ?>
                <input type="button" name="eidt_gd" value="Admin Control Panel" data-target="#modal" id="1" class="btn btn-default admin_settings" />

            <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

<div class="modal inmodal fade" id="specialist_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Specialist Request</h4>
            </div>

            <div class="modal-body" id="specialist_body">


            </div>
        </div>
    </div>
</div>
<div class="modal inmodal fade" id="view_admitted_patient_bal_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body" id="view_admitted_patient_bal_body"></div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="add_consults_room_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Add Room And Assign Consultation Room to Doctors</h4>
            </div>

            <div class="modal-body">
                <div class="">
                    <h3>Consultation Rooms Management</h3>

                    <div class="">

                        <form id="room-form">
                            <input type="hidden" id="id" name="id" value="">
                            <div class="form-group">
                                <label for="room" class="form-label">To add consultation room type into a box below and click the green button: <b>Save/Assign</b></label>
                                <input type="text" class="form-control" id="room" name="room">
                            </div>




                            <a href="index.php" style="font-size: 14px; ;">Click <u>HERE</u> to refresh all rooms added, so you can select from the dropdown below and assign a room to the doctor using the same green button.</a>

                            <HR>

                            <div class="form-group">
                                <h3 id="form-title" style="color:red;"></h3>
                                <h3>Edit below to assign a doctor to this consultation room. Click Assign Button</h3>
                                <select name="room_2" id="room_2" class="form-control">
                                    <option value=""> -- Select from list -- </option>
                                    <?php
                                    $stmt = $db->prepare("SELECT DISTINCT room FROM consultations_rooms ORDER BY room");
                                    $stmt->execute(); // no parameter needed

                                    while ($service = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . htmlspecialchars($service["room"]) . '">' . htmlspecialchars($service["room"]) . '</option>';
                                    }
                                    ?>
                                </select>


                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary me-2">Save/Assign</button>
                                <button type="button" id="cancel-btn" class="btn btn-secondary" style="display:none;">Cancel</button>
                            </div>

                        </form>
                    </div>

                    <div class="">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>

                                    <th>Room</th>
                                    <th>Doctor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="rooms-table">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>



<style>
    @keyframes blinker {
        50% {
            opacity: 0;
        }
    }

    .blink {
        animation: blinker 1s linear infinite;
        color: red;
        font-weight: bold;
    }
</style>