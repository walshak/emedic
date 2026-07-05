<div class="modal inmodal fade" id="bookProcedureModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Procedure Booking</h4>
            </div>

            <div class="modal-body">
                <form method="post" id="subject" action="<?php echo $editFormAction; ?>">
                    <div class="form_sep">
                        <?php
                        if (isset($_GET['patient'])) {
                        ?><label for="reg_input_no" class="req">Patients</label>
                            <h3>Patient ID: <?= cleanInput($_GET['patient']); ?></h3>
                            <p><input type="hidden" name="hospital_no" value="<?= cleanInput($_GET['patient']); ?>" required></p>
                        <?php
                        } else {
                        ?>
                            <label for="search_patient_input">Search for Hospital Patient/External Patients (EX)</label>
                            <input type="text" id="search_patient_input" class="form-control" placeholder="Search by Name or Hospital No">
                            <input type="hidden" id="search_patient_hospital_no" name="hospital_no">
                        <?php
                        } ?>
                    </div>

                    <div class="form_sep">
                        <label class="req">Procedure Category</label>
                        <select class="form-control" name="category_of_procedure" id="category_of_procedure">
                            <option value="" selected>All</option>
                            <?php
                            $stmt = $db->prepare("SELECT distinct category FROM `prices_table` WHERE price_table='Medical Services' AND category!='' ORDER BY category ASC");
                            $stmt->execute();
                            if ($stmt->execute() > 0) {
                                while ($service = $stmt->fetch()) { ?>
                                    <option value="<?= $service["category"]; ?>"> <?= $service["category"]; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form_sep" id="data_1">
                        <label class="req"> Type of Procedure: </label><small style="color: red;"> &nbsp;No medical service found — not marked as surgical.</small>
                        <select data-placeholder="Choose Procedures" class="chosen-select" name="procedure_list" id="procedure_list" style="width:350px;" tabindex="4" required>
                            <option value="">Select</option>
                        </select>
                    </div>


                    <br>
                    <div class="form_sep">
                        <label> Please describe the reason for the procedure, any previous procedures, and any other relevant notes.</label>
                        <textarea name="reason_procedure" rows="3" cols="50" class="form-control" placeholder="Enter your note here..."></textarea>
                    </div>

                    <div class="form_sep">
                        <label> Procedure Date/Time </label>
                        <input type="datetime-local" id="start_date" min="<?= date('Y-m') . '-01'; ?>T08:30" max="<?= date('Y') + (1); ?>-01-30T16:30" name="start_date" value="<?= date('Y-m-d') . 'T' . date('h:i'); ?>" class="form-control">
                    </div>

                    <div class="form_sep">
                        <label class=""> Require Theater Use: </label>
                        <select class="form-control" name="require_theater" id="require_theater">
                            <option value="No" selected>Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="form_sep" id="theater_section" style="display:none;">
                        <label for="theater_select" class="req">Select Theater:</label>
                        <select class="form-control" id="theater_select" name="theater_select">
                            <option value="">Select Theater</option>
                            <option value="Other">Other (Please specify)</option>
                            <?php
                            $stmt = $db->prepare("SELECT distinct theater FROM procedures WHERE theater!='' ORDER BY theater ASC");
                            $stmt->execute();
                            if ($stmt->execute() > 0) {
                                while ($service = $stmt->fetch()) { ?>
                                    <option value="<?= $service["theater"]; ?>"> <?= $service["theater"]; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                        <input type="text" id="new_theater" name="new_theater" placeholder="Enter new theater location" class="form-control" style="display:none;" maxlength="50" />
                    </div>
                    <div class="form_sep">
                        <label class="req"> Estimate time for Procedure: </label>
                        <select class="form-control" name="procedure_time" id="procedure_time" required>
                            <option value="" selected>Select</option>
                            <option value="Not Certain">Not Certain</option>
                            <option value="Less Than 1hr">Less Than 1hr</option>
                            <option value="1hr">1hr</option>
                            <option value="1 to 2hrs">1 to 2hrs</option>
                            <option value="2 to 3hrs">2 to 3hrs</option>
                            <option value="3 to 4hrs">3 to 4hrs</option>
                            <option value="4 to 5hrs">4 to 5hrs</option>
                            <option value="5 to 6hrs">5 to 6hrs</option>
                            <option value="6 to 7hrs">6 to 7hrs</option>
                            <option value="7 to 8hrs">7 to 8hrs</option>
                            <option value="8 to 9hrs">8 to 9hrs</option>
                            <option value="9 to 10hrs">9 to 10hrs</option>
                            <option value="More than 10hrs">More than 10hrs</option>
                        </select>

                        <br>

                        <div class="form_sep">
                            <label for="reg_input_no" class="req">Doctor/Consultant</label>
                            <select name="consultant_id" class="input-sm chosen-select" style="width:350px;" required>
                                <option selected="selected" value="">Search </option>
                                <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'AD' OR rights = 'DR' OR rights = 'MD') AND  status='1' order by fullname");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                    <option value="<?php echo $row["id"] . '__' . $row["fullname"]; ?>"><?php echo $row["fullname"]; ?></option>
                                <?php endwhile ?>
                            </select>
                        </div>
                        <br>
                    </div>
                    <div class="form_sep">

                        <div class="pull-left">
                            <button type="submit" class="btn btn-success btn btn-sm" name="book_procedure" id="book_procedure">Save Request</button>
                        </div>

                        <div class="pull-right">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="bookFolloUpProcedureModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Follow Up Procedure Booking</h4>
            </div>

            <div class="modal-body">
                <form method="post" id="subject" action="<?php echo $editFormAction; ?>">
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Patient</label>
                        <h3>Patient ID: <span id="_follow_up_patient_id_text"></span></h3>
                        <p><input type="hidden" name="hospital_no" id="_follow_up_patient_id" required></p>
                        <p><input type="hidden" name="old_procedure_id" id="_old_procedure_id" required></p>
                        <p><input type="hidden" name="old_procedure_name" id="_old_procedure_name" required></p>

                    </div>

                    <div class="form_sep">
                        <label class="req">Procedure Category</label>
                        <select class="form-control" name="category_of_procedure" id="category_of_procedure_2">
                            <option value="" selected>All</option>
                            <?php
                            $stmt = $db->prepare("SELECT distinct category FROM `prices_table` WHERE price_table='Medical Services' AND category!='' ORDER BY category ASC");
                            $stmt->execute();
                            if ($stmt->execute() > 0) {
                                while ($service = $stmt->fetch()) { ?>
                                    <option value="<?= $service["category"]; ?>"> <?= $service["category"]; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form_sep" id="data_1">
                        <label class="req"> Type of Procedure: </label>
                        <select data-placeholder="Choose Procedures" class="chosen-select" name="procedure_list" id="procedure_list_2" style="width:350px;" tabindex="4" required>
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div>
                        <br>
                        <div class="form_sep">
                            <label> Please describe the reason for the procedure, any previous procedures, and any other relevant notes.</label>
                            <textarea name="reason_procedure" rows="3" cols="50" class="form-control" placeholder="Enter your note here..."></textarea>
                        </div>

                        <div class="form_sep">
                            <label> Procedure Date/Time </label>
                            <input type="datetime-local" id="start_date" min="<?= date('Y-m') . '-01'; ?>T08:30" max="<?= date('Y') + (1); ?>-01-30T16:30" name="start_date" value="<?= date('Y-m-d') . 'T' . date('h:i'); ?>" class="form-control">
                        </div>

                        <div class="form_sep">
                            <label class=""> Require Theater Use: </label>
                            <select class="form-control" name="require_theater" id="require_theater_2" >
                                <option value="No" selected>Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form_sep" id="theater_section_2" style="display:none;">
                            <label for="theater_select_2" class="req">Select Theater:</label>
                            <select class="form-control" id="theater_select_2" name="theater_select">
                                <option value="">Select Theater</option>
                                <option value="Other">Other (Please specify)</option>
                                <?php
                                $stmt = $db->prepare("SELECT distinct theater FROM procedures WHERE theater!='' ORDER BY theater ASC");
                                $stmt->execute();
                                if ($stmt->execute() > 0) {
                                    while ($service = $stmt->fetch()) { ?>
                                        <option value="<?= $service["theater"]; ?>"> <?= $service["theater"]; ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                            <input type="text" id="new_theater_2" name="new_theater" placeholder="Enter new theater location" class="form-control" style="display:none;" maxlength="50" />
                        </div>
                        <div class="form_sep">
                            <label class="req"> Estimate time for Procedure: </label>
                            <select class="form-control" name="procedure_time" id="procedure_time_2" required>
                                <option value="" selected>Select</option>
                                <option value="Not Certain">Not Certain</option>
                                <option value="Less Than 1hr">Less Than 1hr</option>
                                <option value="1hr">1hr</option>
                                <option value="1 to 2hrs">1 to 2hrs</option>
                                <option value="2 to 3hrs">2 to 3hrs</option>
                                <option value="3 to 4hrs">3 to 4hrs</option>
                                <option value="4 to 5hrs">4 to 5hrs</option>
                                <option value="5 to 6hrs">5 to 6hrs</option>
                                <option value="6 to 7hrs">6 to 7hrs</option>
                                <option value="7 to 8hrs">7 to 8hrs</option>
                                <option value="8 to 9hrs">8 to 9hrs</option>
                                <option value="9 to 10hrs">9 to 10hrs</option>
                                <option value="More than 10hrs">More than 10hrs</option>
                            </select>

                            <br>


                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Doctor/Consultant</label>
                                <select name="update_doctor" class="input-sm chosen-select" style="width:350px;" required>
                                    <option selected="selected" value="">Search </option>
                                    <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'AD' OR rights = 'DR' OR rights = 'MD') AND  status='1' order by fullname");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                        <option value="<?php echo $row["id"] . '__' . $row["fullname"]; ?>" <?= ($row["fullname"] == $procedure['consultant_name'] ? 'selected' : ''); ?>><?php echo $row["fullname"]; ?></option>
                                    <?php endwhile ?>
                                </select>
                                <input type="hidden" name="old_consultant_name" value="<?= $procedure['consultant_name']; ?>">
                            </div>


                            <div>
                                <label for="reg_select" class="req">Doctor/Consultant333</label>
                                <input type="text" class="typeahead form-control  " data-provide="typeahead" id="typeahead_search_specialist_2" placeholder="Search for Doctor/Specialist" autocomplete="off">
                                <input type="hidden" name="consultant_id" id="typeahead_search_specialist_id_2">
                                <input type="hidden" name="consultant_name" id="typeahead_search_specialist_name_2">
                            </div>
                            <br>
                        </div>
                        <div class="form_sep">

                            <div class="pull-left">
                                <button type="submit" class="btn btn-success btn btn-sm" name="book_procedure" id="book_procedure_2">Save Request</button>
                            </div>

                            <div class="pull-right">
                                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>

                            </div>
                        </div>

                </form>
            </div>
        </div>
    </div>
</div>