<!-- Procedures & Medical Services Management Modal -->
<div class="modal inmodal fade" id="proc_medserv_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Procedures &amp; Medical Services</h4>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">

                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#tab-procedures"><i class="fa fa-stethoscope"></i> Procedures Management</a></li>
                        <li class=""><a data-toggle="tab" href="#tab-medservices"><i class="fa fa-medkit"></i> Medical Services Management</a></li>
                    </ul>
                    <div class="tab-content">

                        <!-- TAB 1: PROCEDURES -->
                        <div id="tab-procedures" class="tab-pane active">
                            <div class="panel-body">
                                <div class="row m-b-sm">
                                <div class="col-sm-4">
                                    <input type="text" id="proc_search_input" class="form-control" placeholder="Search procedure name, doctor..." onkeyup="fetchProcedures(1)">
                                </div>
                                <div class="col-sm-3">
                                    <select id="proc_status_filter" class="form-control" onchange="fetchProcedures(1)">
                                        <option value="all">-- All Statuses --</option>
                                        <option value="unpaid">Unpaid Only</option>
                                        <option value="paid">Paid Only</option>
                                        <option value="pending">Pending Execution</option>
                                        <option value="completed">Completed Execution</option>
                                    </select>
                                </div>
                                <div class="col-sm-5 text-right">
                                    <button class="btn btn-primary" id="btn_book_proc_trigger" onclick="openBookProcedureModal()"><i class="fa fa-plus-circle"></i> Book New Procedure</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" style="font-size: 13px;">
                                    <thead>
                                        <tr style="background-color: #1ab394;">
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Date Req / Scheduled</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Procedure Name</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Cost</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Payment Status</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Execution Status</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Attending Doctor</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Outcome / Post-Op Notes</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="proc_table_body">
                                        <tr><td colspan="8" class="text-center">Loading Procedures...</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-6" id="proc_pagination_info" style="margin-top: 10px; font-weight: bold;"></div>
                                <div class="col-sm-6 text-right" id="proc_pagination_links"></div>
                            </div>
                            </div>
                        </div>

                        <!-- TAB 2: MEDICAL SERVICES -->
                        <div id="tab-medservices" class="tab-pane">
                            <div class="panel-body">
                                <div class="row m-b-sm">
                                <div class="col-sm-4">
                                    <input type="text" id="medserv_search_input" class="form-control" placeholder="Search medical service name, doctor..." onkeyup="fetchMedServices(1)">
                                </div>
                                <div class="col-sm-3">
                                    <select id="medserv_status_filter" class="form-control" onchange="fetchMedServices(1)">
                                        <option value="all">-- All Statuses --</option>
                                        <option value="unpaid">Unpaid Only</option>
                                        <option value="paid">Paid Only</option>
                                        <option value="pending">Pending Notes</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="col-sm-5 text-right">
                                    <button class="btn btn-primary" id="btn_book_medserv_trigger" onclick="openBookMedServiceModal()"><i class="fa fa-plus-circle"></i> Book Medical Service</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" style="font-size: 13px;">
                                    <thead>
                                        <tr style="background-color: #1ab394;">
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Entry Date</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Service Name</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Price</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Payment Status</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Completion Status</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Doctor</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Clinical Notes / Outcome</th>
                                            <th style="color: #ffffff !important; background-color: #1ab394 !important;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="medserv_table_body">
                                        <tr><td colspan="8" class="text-center">Loading Medical Services...</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-6" id="medserv_pagination_info" style="margin-top: 10px; font-weight: bold;"></div>
                                <div class="col-sm-6 text-right" id="medserv_pagination_links"></div>
                            </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Sub-Modal: Book New Procedure -->
<div class="modal fade" id="book_procedure_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="$('#book_procedure_modal').modal('hide')">&times;</button>
                <h4 class="modal-title">Book New Procedure</h4>
            </div>
            <form id="form_book_procedure" onsubmit="submitBookProcedure(event)">
                <div class="modal-body">
                    <input type="hidden" name="hosp_no" value="<?= htmlspecialchars($hosp_no); ?>">
                    <div class="form-group">
                        <label class="req">Select Procedure Item</label>
                        <select name="item_sn" id="proc_select_item" class="form-control" onchange="updateProcedureCost()" required>
                            <option value="">-- Select Procedure --</option>
                            <?php
                            $pStmt = $db->prepare("SELECT sn, item_service, hosp_price, ext_price FROM prices_table WHERE price_table = 'Medical Services' ORDER BY item_service ASC");
                            $pStmt->execute();
                            while ($prow = $pStmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $prow['sn'] . '" data-price="' . $prow['hosp_price'] . '" data-name="' . htmlspecialchars($prow['item_service']) . '">' . htmlspecialchars($prow['item_service']) . ' (&#8358;' . number_format($prow['hosp_price'], 2) . ')</option>';
                            }
                            ?>
                        </select>
                        <input type="hidden" name="procedure_name" id="proc_item_name_hidden">
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label class="req">Scheduled Date & Time</label>
                            <input type="datetime-local" name="date_timee" class="form-control" required value="<?= date('Y-m-d\TH:i'); ?>">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label class="req">Cost / Price (&#8358;)</label>
                            <input type="number" step="0.01" name="cost" id="proc_cost_input" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Performing Consultant / Doctor</label>
                            <select name="consultant_id" id="proc_doctor_select" class="form-control">
                                <option value="0">-- Select Doctor --</option>
                                <?php
                                $dStmt = $db->query("SELECT id, fullname FROM admin_users WHERE rights IN ('DR', 'MD', 'SA') ORDER BY fullname ASC");
                                while ($drow = $dStmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $drow['id'] . '" data-name="' . htmlspecialchars($drow['fullname']) . '">' . htmlspecialchars($drow['fullname']) . '</option>';
                                }
                                ?>
                            </select>
                            <input type="hidden" name="consultant_name" id="proc_doctor_name_hidden">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Payment Mode</label>
                            <select name="pay_mode" class="form-control">
                                <option value="Cash">Cash</option>
                                <option value="Wallet">Patient Wallet</option>
                                <option value="HMO / Credit">HMO / Credit</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Require Theater?</label>
                            <select name="require_theater" class="form-control">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Theater Name / Room</label>
                            <input type="text" name="theater_select" class="form-control" placeholder="Main Operating Theater">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Reason / Indication for Procedure</label>
                        <textarea name="indication" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save & Book Procedure</button>
                    <button type="button" class="btn btn-white" onclick="$('#book_procedure_modal').modal('hide')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sub-Modal: Book New Medical Service -->
<div class="modal fade" id="book_medservice_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="$('#book_medservice_modal').modal('hide')">&times;</button>
                <h4 class="modal-title">Book Medical Service</h4>
            </div>
            <form id="form_book_medservice" onsubmit="submitBookMedService(event)">
                <div class="modal-body">
                    <input type="hidden" name="hosp_no" value="<?= htmlspecialchars($hosp_no); ?>">
                    <div class="form-group">
                        <label class="req">Select Medical Service</label>
                        <select name="item_sn" id="medserv_select_item" class="form-control" onchange="updateMedServiceCost()" required>
                            <option value="">-- Select Service --</option>
                            <?php
                            $mStmt = $db->prepare("SELECT sn, item_service, hosp_price FROM prices_table WHERE price_table = 'Medical Services' ORDER BY item_service ASC");
                            $mStmt->execute();
                            while ($mrow = $mStmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $mrow['sn'] . '" data-price="' . $mrow['hosp_price'] . '" data-name="' . htmlspecialchars($mrow['item_service']) . '">' . htmlspecialchars($mrow['item_service']) . ' (&#8358;' . number_format($mrow['hosp_price'], 2) . ')</option>';
                            }
                            ?>
                        </select>
                        <input type="hidden" name="service_name" id="medserv_item_name_hidden">
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label class="req">Cost / Price (&#8358;)</label>
                            <input type="number" step="0.01" name="cost" id="medserv_cost_input" class="form-control" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Payment Mode</label>
                            <select name="pay_mode" class="form-control">
                                <option value="Cash">Cash</option>
                                <option value="Wallet">Patient Wallet</option>
                                <option value="HMO / Credit">HMO / Credit</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Attending Consultant / Doctor</label>
                        <select name="consultant_id" id="medserv_doctor_select" class="form-control">
                            <option value="0">-- Select Doctor --</option>
                            <?php
                            $dStmt = $db->query("SELECT id, fullname FROM admin_users WHERE rights IN ('DR', 'MD', 'SA') ORDER BY fullname ASC");
                            while ($drow = $dStmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $drow['id'] . '" data-name="' . htmlspecialchars($drow['fullname']) . '">' . htmlspecialchars($drow['fullname']) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="hidden" name="consultant_name" id="medserv_doctor_name_hidden">
                    </div>
                    <div class="form-group">
                        <label>Notes / Instructions</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save & Book Service</button>
                    <button type="button" class="btn btn-white" onclick="$('#book_medservice_modal').modal('hide')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sub-Modal: Edit Procedure Outcome -->
<div class="modal fade" id="procedure_outcome_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="$('#procedure_outcome_modal').modal('hide')">&times;</button>
                <h4 class="modal-title">Procedure Outcome & Post-Op Notes</h4>
            </div>
            <form id="form_procedure_outcome" onsubmit="submitProcedureOutcome(event)">
                <div class="modal-body">
                    <input type="hidden" name="procedure_sn" id="outcome_proc_sn">
                    <div class="form-group">
                        <label class="req">Post-Op Results / Outcome Summary</label>
                        <textarea name="post_op_results" id="outcome_post_op_results" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Intra-Operative Findings</label>
                        <textarea name="findings" id="outcome_findings" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Incision Details</label>
                            <input type="text" name="incision" id="outcome_incision" class="form-control">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Anaesthetia Type</label>
                            <input type="text" name="anaesthetia_type" id="outcome_anaesthetia_type" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="is_completed" value="1" id="outcome_is_completed"> Mark Procedure as Completed Execution</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Outcome</button>
                    <button type="button" class="btn btn-white" onclick="$('#procedure_outcome_modal').modal('hide')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sub-Modal: Edit Medical Service Notes -->
<div class="modal fade" id="medservice_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="$('#medservice_notes_modal').modal('hide')">&times;</button>
                <h4 class="modal-title">Medical Service Notes & Clinical Outcome</h4>
            </div>
            <form id="form_medservice_notes" onsubmit="submitMedServiceNotes(event)">
                <div class="modal-body">
                    <input type="hidden" name="app_service_tbl_id" id="notes_app_service_tbl_id">
                    <div class="form-group">
                        <label>Attending Consultant / Doctor</label>
                        <select name="consultant_id" id="notes_doctor_select" class="form-control">
                            <option value="0">-- Select Doctor --</option>
                            <?php
                            $dStmt = $db->query("SELECT id, fullname FROM admin_users WHERE rights IN ('DR', 'MD', 'SA') ORDER BY fullname ASC");
                            while ($drow = $dStmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $drow['id'] . '" data-name="' . htmlspecialchars($drow['fullname']) . '">' . htmlspecialchars($drow['fullname']) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="hidden" name="consultant_name" id="notes_doctor_name_hidden">
                    </div>
                    <div class="form-group">
                        <label class="req">Clinical Notes / Findings / Result</label>
                        <textarea name="notes" id="notes_text" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="is_completed" value="1" id="notes_is_completed"> Mark Service as Completed</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Notes</button>
                    <button type="button" class="btn btn-white" onclick="$('#medservice_notes_modal').modal('hide')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var currentHospNo = '<?= htmlspecialchars($hosp_no); ?>';
window.currentHospNo = currentHospNo;
var procCurrentPage = 1;
window.procCurrentPage = procCurrentPage;
var medservCurrentPage = 1;
window.medservCurrentPage = medservCurrentPage;

function loadProcMedServData() {
    fetchProcedures(1);
    fetchMedServices(1);
}
window.loadProcMedServData = loadProcMedServData;

function fetchProcedures(page) {
    procCurrentPage = page || 1;
    window.procCurrentPage = procCurrentPage;
    var search = $('#proc_search_input').val() || '';
    var status_filter = $('#proc_status_filter').val() || 'all';

    $.ajax({
        url: 'fetch_patient_services_procedures.php',
        type: 'GET',
        data: {
            action: 'fetch_procedures',
            hosp_no: currentHospNo,
            search: search,
            status_filter: status_filter,
            page: procCurrentPage,
            limit: 5
        },
        success: function(res) {
            if (res.status === 200) {
                renderProceduresTable(res.data, res.can_edit_outcomes_notes, res.can_handle_billing);
                renderPagination('#proc_pagination_info', '#proc_pagination_links', res.current_page, res.total_pages, res.total_records, 'fetchProcedures');
                if (!res.can_book_procedures) {
                    $('#btn_book_proc_trigger').prop('disabled', true).attr('title', 'Frontdesk booking disabled in hospital settings');
                } else {
                    $('#btn_book_proc_trigger').prop('disabled', false);
                }
            }
        }
    });
}
window.fetchProcedures = fetchProcedures;

function renderProceduresTable(data, canEditNotes, canHandleBilling) {
    var html = '';
    if (!data || data.length === 0) {
        html = '<tr><td colspan="8" class="text-center">No procedure records found.</td></tr>';
    } else {
        $.each(data, function(i, row) {
            var payBadge = row.paystatus == 1 ? '<span class="badge badge-primary">Paid (' + (row.pay_mode || 'Cash') + ')</span>' : '<span class="badge badge-danger">Unpaid</span>';
            var execBadge = row.status == 1 ? '<span class="badge badge-primary">Completed</span>' : '<span class="badge badge-warning">Pending</span>';
            var cost = parseFloat(row.cost || row.pay || 0).toFixed(2);
            var outcome = row.post_op_results || row.Findings || '<i>No outcome recorded yet</i>';
            
            var actions = '';
            if (canHandleBilling) {
                if (row.paystatus != 1) {
                    actions += '<a href="../billing/pacct.php?emr=' + window.currentHospNo + '&pay" target="_blank" class="btn btn-xs btn-warning" title="Open Patient Account / Billing Ledger"><i class="fa fa-credit-card"></i> Patient Account</a> ';
                }
            }
            if (canEditNotes) {
                actions += '<button class="btn btn-xs btn-primary" onclick="openProcedureOutcomeModal(' + JSON.stringify(row).replace(/"/g, '&quot;') + ')"><i class="fa fa-edit"></i> Outcome</button>';
            }
            if (!canHandleBilling && !canEditNotes && row.paystatus == 1) {
                actions += '<span class="text-muted"><i class="fa fa-check"></i> Paid</span>';
            }

            var docInfo = '<strong>Dr:</strong> ' + (row.consultant_name && row.consultant_name != '0' ? row.consultant_name : 'Unassigned');
            if (row.prepared_by) {
                docInfo += '<br><small class="text-muted">Req: ' + row.prepared_by + '</small>';
            }

            html += '<tr>' +
                '<td>' + (row.sDate || row.date_entry) + '</td>' +
                '<td><strong>' + row.procedures + '</strong></td>' +
                '<td>&#8358;' + cost + '</td>' +
                '<td>' + payBadge + '</td>' +
                '<td>' + execBadge + '</td>' +
                '<td>' + docInfo + '</td>' +
                '<td>' + outcome + '</td>' +
                '<td>' + (actions || '-') + '</td>' +
                '</tr>';
        });
    }
    $('#proc_table_body').html(html);
}
window.renderProceduresTable = renderProceduresTable;

function fetchMedServices(page) {
    medservCurrentPage = page || 1;
    window.medservCurrentPage = medservCurrentPage;
    var search = $('#medserv_search_input').val() || '';
    var status_filter = $('#medserv_status_filter').val() || 'all';

    $.ajax({
        url: 'fetch_patient_services_procedures.php',
        type: 'GET',
        data: {
            action: 'fetch_med_services',
            hosp_no: currentHospNo,
            search: search,
            status_filter: status_filter,
            page: medservCurrentPage,
            limit: 5
        },
        success: function(res) {
            if (res.status === 200) {
                renderMedServicesTable(res.data, res.can_edit_outcomes_notes, res.can_handle_billing);
                renderPagination('#medserv_pagination_info', '#medserv_pagination_links', res.current_page, res.total_pages, res.total_records, 'fetchMedServices');
                if (!res.can_book_med_services) {
                    $('#btn_book_medserv_trigger').prop('disabled', true).attr('title', 'Frontdesk booking disabled in hospital settings');
                } else {
                    $('#btn_book_medserv_trigger').prop('disabled', false);
                }
            }
        }
    });
}
window.fetchMedServices = fetchMedServices;

function renderMedServicesTable(data, canEditNotes, canHandleBilling) {
    var html = '';
    if (!data || data.length === 0) {
        html = '<tr><td colspan="8" class="text-center">No medical service records found.</td></tr>';
    } else {
        $.each(data, function(i, row) {
            var payBadge = row.paystatus == 1 ? '<span class="badge badge-primary">Paid (' + (row.pay_mode || 'Cash') + ')</span>' : '<span class="badge badge-danger">Unpaid</span>';
            var isComp = (row.drug_status == 1 || row.isCompleted == 1);
            var compBadge = isComp ? '<span class="badge badge-primary">Completed</span>' : '<span class="badge badge-warning">Pending</span>';
            var price = parseFloat(row.hosp_price || row.pay || 0).toFixed(2);
            var notes = row.notes || '<i>No notes recorded yet</i>';
            
            var reqBy = row.ns_prepared_by || row.prepared_by || '';
            var docInfo = '<strong>Dr:</strong> ' + (row.consultant_name && row.consultant_name != '0' ? row.consultant_name : 'Unassigned');
            if (reqBy) {
                docInfo += '<br><small class="text-muted">Req: ' + reqBy + '</small>';
            }

            var actions = '';
            if (canHandleBilling) {
                if (row.paystatus != 1) {
                    actions += '<a href="../billing/pacct.php?emr=' + window.currentHospNo + '&pay" target="_blank" class="btn btn-xs btn-warning" title="Open Patient Account / Billing Ledger"><i class="fa fa-credit-card"></i> Patient Account</a> ';
                }
            }
            if (canEditNotes) {
                actions += '<button class="btn btn-xs btn-primary" onclick="openMedServiceNotesModal(' + JSON.stringify(row).replace(/"/g, '&quot;') + ')"><i class="fa fa-edit"></i> Notes</button>';
            }
            if (!canHandleBilling && !canEditNotes && row.paystatus == 1) {
                actions += '<span class="text-muted"><i class="fa fa-check"></i> Paid</span>';
            }

            html += '<tr>' +
                '<td>' + (row.date_entry || '-') + '</td>' +
                '<td><strong>' + row.item_services + '</strong></td>' +
                '<td>&#8358;' + price + '</td>' +
                '<td>' + payBadge + '</td>' +
                '<td>' + compBadge + '</td>' +
                '<td>' + docInfo + '</td>' +
                '<td>' + notes + '</td>' +
                '<td>' + (actions || '-') + '</td>' +
                '</tr>';
        });
    }
    $('#medserv_table_body').html(html);
}
window.renderMedServicesTable = renderMedServicesTable;

function renderPagination(infoSelector, linksSelector, currentPage, totalPages, totalRecords, fetchFuncName) {
    $(infoSelector).html('Page ' + currentPage + ' of ' + totalPages + ' (' + totalRecords + ' items)');
    
    var html = '<ul class="pagination pagination-sm m-n">';
    var prevDisabled = currentPage <= 1 ? 'disabled' : '';
    html += '<li class="' + prevDisabled + '"><a href="javascript:void(0)" onclick="' + (currentPage > 1 ? fetchFuncName + '(' + (currentPage - 1) + ')' : '') + '">&laquo; Prev</a></li>';
    
    for (var p = 1; p <= totalPages; p++) {
        var active = p === currentPage ? 'active' : '';
        html += '<li class="' + active + '"><a href="javascript:void(0)" onclick="' + fetchFuncName + '(' + p + ')">' + p + '</a></li>';
    }
    
    var nextDisabled = currentPage >= totalPages ? 'disabled' : '';
    html += '<li class="' + nextDisabled + '"><a href="javascript:void(0)" onclick="' + (currentPage < totalPages ? fetchFuncName + '(' + (currentPage + 1) + ')' : '') + '">Next &raquo;</a></li>';
    html += '</ul>';
    
    $(linksSelector).html(html);
}
window.renderPagination = renderPagination;

window.openBookProcedureModal = function() {
    $('#form_book_procedure')[0].reset();
    $('#book_procedure_modal').modal('show');
};

window.updateProcedureCost = function() {
    var opt = $('#proc_select_item option:selected');
    var price = opt.data('price') || 0;
    var name = opt.data('name') || '';
    $('#proc_cost_input').val(price);
    $('#proc_item_name_hidden').val(name);
};

window.submitBookProcedure = function(e) {
    e.preventDefault();
    var docOpt = $('#proc_doctor_select option:selected');
    $('#proc_doctor_name_hidden').val(docOpt.data('name') || '');

    var formData = $('#form_book_procedure').serialize() + '&action=book_procedure';
    $.post('fetch_patient_services_procedures.php', formData, function(res) {
        if (res.status === 200) {
            $('#book_procedure_modal').modal('hide');
            window.fetchProcedures(1);
            if (res.can_handle_billing) {
                if (confirm(res.message + '\n\nWould you like to open Patient Account / Billing to collect payment now?')) {
                    window.open('../billing/pacct.php?emr=' + window.currentHospNo + '&pay', '_blank');
                }
            } else {
                alert(res.message + '\n(Patient can proceed to Accounts to complete payment)');
            }
        } else {
            alert(res.message);
        }
    }, 'json');
};

window.openBookMedServiceModal = function() {
    $('#form_book_medservice')[0].reset();
    $('#book_medservice_modal').modal('show');
};

window.updateMedServiceCost = function() {
    var opt = $('#medserv_select_item option:selected');
    var price = opt.data('price') || 0;
    var name = opt.data('name') || '';
    $('#medserv_cost_input').val(price);
    $('#medserv_item_name_hidden').val(name);
};

window.submitBookMedService = function(e) {
    e.preventDefault();
    var docOpt = $('#medserv_doctor_select option:selected');
    $('#medserv_doctor_name_hidden').val(docOpt.data('name') || '');

    var formData = $('#form_book_medservice').serialize() + '&action=book_med_service';
    $.post('fetch_patient_services_procedures.php', formData, function(res) {
        if (res.status === 200) {
            $('#book_medservice_modal').modal('hide');
            window.fetchMedServices(1);
            if (res.can_handle_billing) {
                if (confirm(res.message + '\n\nWould you like to open Patient Account / Billing to collect payment now?')) {
                    window.open('../billing/pacct.php?emr=' + window.currentHospNo + '&pay', '_blank');
                }
            } else {
                alert(res.message + '\n(Patient can proceed to Accounts to complete payment)');
            }
        } else {
            alert(res.message);
        }
    }, 'json');
};

window.openProcedureOutcomeModal = function(row) {
    $('#outcome_proc_sn').val(row.sn);
    $('#outcome_post_op_results').val(row.post_op_results || '');
    $('#outcome_findings').val(row.Findings || '');
    $('#outcome_incision').val(row.Incision || '');
    $('#outcome_anaesthetia_type').val(row.anaesthetia_type || '');
    $('#outcome_is_completed').prop('checked', row.status == 1);
    $('#procedure_outcome_modal').modal('show');
};

window.submitProcedureOutcome = function(e) {
    e.preventDefault();
    var formData = $('#form_procedure_outcome').serialize() + '&action=save_procedure_outcome';
    $.post('fetch_patient_services_procedures.php', formData, function(res) {
        if (res.status === 200) {
            alert(res.message);
            $('#procedure_outcome_modal').modal('hide');
            window.fetchProcedures(window.procCurrentPage);
        } else {
            alert(res.message);
        }
    }, 'json');
};

window.openMedServiceNotesModal = function(row) {
    $('#notes_app_service_tbl_id').val(row.sn);
    $('#notes_text').val(row.notes || '');
    if (row.consultant_id) {
        $('#notes_doctor_select').val(row.consultant_id);
    }
    $('#notes_is_completed').prop('checked', (row.drug_status == 1 || row.isCompleted == 1));
    $('#medservice_notes_modal').modal('show');
};

window.submitMedServiceNotes = function(e) {
    e.preventDefault();
    var docOpt = $('#notes_doctor_select option:selected');
    $('#notes_doctor_name_hidden').val(docOpt.data('name') || '');

    var formData = $('#form_medservice_notes').serialize() + '&action=save_med_service_notes';
    $.post('fetch_patient_services_procedures.php', formData, function(res) {
        if (res.status === 200) {
            alert(res.message);
            $('#medservice_notes_modal').modal('hide');
            window.fetchMedServices(window.medservCurrentPage);
        } else {
            alert(res.message);
        }
    }, 'json');
};

window.processPayment = function(serviceSn, payMode) {
    if (!confirm('Are you sure you want to process payment via ' + payMode + '?')) return;

    $.post('fetch_patient_services_procedures.php', {
        action: 'pay_service_procedure',
        service_sn: serviceSn,
        pay_mode: payMode,
        hosp_no: window.currentHospNo
    }, function(res) {
        if (res.status === 200) {
            alert(res.message);
            window.fetchProcedures(window.procCurrentPage);
            window.fetchMedServices(window.medservCurrentPage);
        } else {
            alert(res.message);
        }
    }, 'json');
};
</script>
