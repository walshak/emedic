<?php
if (isset($_POST['load_vistamedic_notes'])) {
    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    $hospital_no = cleanInput($_POST['hospital_no']);
    $notes_type = $_POST['notes_type'];


    $notes_type_sql = "";
    $notesTitle = 'Consultation Notes';
    $vista_table = 'notes_old';
    if (!empty($notes_type)) {
        if (in_array($notes_type, ['Lab', 'Scan'])) {
            $vista_table = 'notes_old';
            $notesTitle = 'Investigation Notes';
        }
        $notes_type_sql = " AND note_type = '$notes_type' ";
    }

    $sqlQuery = "SELECT * FROM $vista_table WHERE hospital_no = ? $notes_type_sql  ORDER BY entry_date";
    $rstSelect2 = $db->prepare($sqlQuery);
    $rstSelect2->execute([$hospital_no]);
    if ($rstSelect2->rowCount() > 0) {
        $n = 1; ?>
        <table id="resp_table" class="table toggle-square">
            <thead>
                <tr>
                    <th width="2%">#</th>

                    <th width="78%"><?= $notesTitle; ?></th>
                    <th width="20%"><i>Entered by</i></th>
                </tr>
            </thead>

            <?php
            while ($row = $rstSelect2->fetch(PDO::FETCH_ASSOC)) {
            ?>
                <tr>
                    <td width="2%"><?php echo $n++; ?></td>
                    <td width="78%">
                        <?= $row['service_type'] ?>
                        <?php echo $row['notes']; ?></td>
                    <td width="20%"><?php echo $row['createdBy'] .
                                        '<br>' . $row['entry_date']; //)) . ' ' .
                                    ///date('h:i a', strtotime($row['date_entry'])); 
                                    ?>
                        <hr>

                    </td>
                </tr>
            <?php }    ?>
        </table>

        <?php if ($_SESSION['h_code'] == 'CCHM') {

            $sqlQuery = "SELECT * FROM old_med WHERE hospital_no = ? ORDER BY dDate DESC ";
            $rstSelect2 = $db->prepare($sqlQuery);
            $rstSelect2->execute([$hospital_no]);
            if ($rstSelect2->rowCount() > 0) {
                $n = 1; ?>

                <hr>
                <h3>DRUG HISTORY</h3>
                <table id="resp_table" class="table toggle-square">
                    <thead>
                        <tr>
                            <th width="2%">#</th>
                            <th width="78%">Drug & Description</th>
                            <th width="20%"><i>Entered by</i></th>
                        </tr>
                    </thead>

                    <?php
                    while ($row = $rstSelect2->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <tr>
                            <td width="2%"><?php echo $n++; ?></td>
                            <td width="78%">
                                <?= $row['Medicine'] . ': ' . $row['Dose_Interval'] . ' ' . $row['Dose_Duration'] . ' ' . $row['Instruction'] ?></td>
                            <td width="20%"><?php echo $row['Prescribe_By'] .
                                                '<br>' . $row['dDate']; //)) . ' ' .
                                            ///date('h:i a', strtotime($row['date_entry'])); 
                                            ?>
                                <hr>

                            </td>
                        </tr>
                    <?php }    ?>
                </table>
            <?php } ?>
        <?php } ?>




    <?php } else { ?>
        <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
    <?php } ?>


    <?php exit;
}

if (isset($_POST['loadMedicalHx'])) {

    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    $hospital_no       = isset($_POST['hospital_no']) ? $_POST['hospital_no'] : '';
    $records_per_page  = isset($_POST['records_per_page']) ? (int) $_POST['records_per_page'] : 10;
    $documentation     = isset($_POST['documentation']) ? $_POST['documentation'] : '';
    $doctor_names      = isset($_POST['doctor_names']) ? $_POST['doctor_names'] : '';
    $search_anything   = isset($_POST['search_anything']) ? $_POST['search_anything'] : '';
    $start_date        = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date          = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $current_page      = isset($_POST['page_num']) ? (int) $_POST['page_num'] : 1;
    $offset            = ($current_page - 1) * $records_per_page;

    $search_tag = " AND 1=1";
    $params = array(':hospital_no' => $hospital_no);

    // Session filters
    if ($_SESSION['dispensory'] == '1') {
        $dept_id = $_SESSION['dept_id'];
        $search_tag .= " AND dept_id = :dept_id";
        $params[':dept_id'] = $dept_id;
    } elseif ($_SESSION['rights'] == 'PY') {
        $search_tag .= " AND notes_type = 'Physio'";
    } elseif ($search_anything == '' && $documentation == '' && $doctor_names == '' && $start_date == '' && $end_date == '') {
        $search_tag .= " AND notes_type IN ('D', 'C','Pharm', 'REQ_REMINDER')";
    } else {
        $search_tag .= "";
    }

    // Date range
    if ($start_date != '' && $end_date != '') {
        $search_tag .= " AND DATE(date_entry) BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    // Text search
    if ($search_anything != '') {
        $search_tag .= " AND notes LIKE :search_anything";
        $params[':search_anything'] = '%' . $search_anything . '%';
    }

    // Notes type
    if ($documentation != '') {
        $search_tag .= " AND notes_type = :documentation";
        $params[':documentation'] = $documentation;
    }

    // Doctor filter
    if ($doctor_names != '') {
        $search_tag .= " AND created_by = :doctor_names";
        $params[':doctor_names'] = $doctor_names;
    }

    // Final query
    $sql = "
        SELECT notes.*, hremp.Designation, specialists.name AS spec
        FROM notes 
        LEFT JOIN admin_users ON admin_users.id = notes.created_by
        LEFT JOIN hremp ON admin_users.EmployeeCode = hremp.EmployeeCode
        LEFT JOIN specialists ON admin_users.specialist = specialists.id
        WHERE notes.hospital_no = :hospital_no AND notes.status = '1'
        $search_tag
        ORDER BY sn DESC
        LIMIT :limit OFFSET :offset
    ";

    // Prepare and bind
    $medic_hx_stmt = $db->prepare($sql);

    // Bind dynamic filters
    foreach ($params as $key => $val) {
        $medic_hx_stmt->bindValue($key, $val);
    }

    // Limit and offset bindings
    $medic_hx_stmt->bindValue(':limit', (int) $records_per_page, PDO::PARAM_INT);
    $medic_hx_stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

    // Execute
    $medic_hx_stmt->execute();


    if ($medic_hx_stmt->rowCount() > 0) {
        $n = 1; ?>
        <table id="resp_table" class="table toggle-square">
            <thead>
                <tr>
                    <th width="2%">#</th>
                    <th width="78%">Notes</th>
                    <th width="20%"><i>Entered by</i></th>
                </tr>
            </thead>

            <?php
            while ($row = $medic_hx_stmt->fetch(PDO::FETCH_ASSOC)) {


                if ($row['date_entry2'] != '') {
                    $date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($row['date_entry2'])) . '<br>';
                    $date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($row['date_entry'])) . '<br>';
                    $date_ .= "<b>Number of Edits </b>" .  $row['no_updates'];
                } else {
                    $date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($row['date_entry'])) . '<br>';
                }
                $date_ = "<div style='font-size:14px;'>$date_</div>";

            ?>
                <tr>
                    <td width="2%"><?php echo $n++; ?></td>
                    <td width="78%">
                        <?php if ($row['notes_type'] == 'REQ_REMINDER') { ?><strong style="color: darkred; ">Specialist Request:</strong><br><?php } ?>
                        <?php echo $row['notes']; ?></td>
                    <td width="20%">
                        <?php echo "<b>" . $row['prepared_by'] . " <br><i>(" . (($row['spec']) ? $row['spec'] : $row['Designation']) . ")</i>" . "</br>"  . $date_; ///date('d M,Y h:i a', strtotime($row['date_entry'])); 
                        ?>
                        <hr>

                        <?php
                        if (($row['created_by'] == $_SESSION['id'] && !empty($row['created_by'])) || ($row['prepared_by'] == $_SESSION['fullname']) || $_SESSION['administrative_login'] == 1) {
                            if (date_diff_day($row['date_entry']) <= 1 || $_SESSION['administrative_login'] == 1) { ?>
                                <?php
                                if ($row['notes_type'] == 'REQ_REMINDER' and $row['ack'] == 0) { ?>

                                    <form action="patient.php?hosp_no=<?= $hospital_no; ?>" method="post" style="display:inline-block">
                                        <input type="hidden" name="id" value="<?= $row['sn']; ?>" />
                                        <button type="submit" name="delete_btn" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to DELETE?')">Delete</button>
                                    </form>

                                <?php } ?>
                                <?php if ($row['notes_type'] != 'REQ_REMINDER' and $row['ack'] == 0) { ?>
                                    <form action="<?= $editFormAction; ?>" method="post" style="display:inline-block">
                                        <input type="hidden" name="id" value="<?= $row['sn']; ?>" />
                                        <input type="hidden" name="date_entry" value="<?= $row['date_entry']; ?>" />
                                        <input type="hidden" name="date_entry2" value="<?= $row['date_entry2']; ?>" />
                                        <input type="hidden" name="notes_type" value="<?= $row['notes_type']; ?>" />
                                        <button type="submit" name="edit-note-btn" class="btn btn-warning btn-xs">Edit Note</button>

                                        <?php
                                        $entry_time = strtotime($row['date_entry']); // وقت الإدخال
                                        $current_time = time(); // الوقت الحالي

                                        $allowed_minutes = 5; // المدة المسموحة (يمكن تغييرها)
                                        $time_diff = ($current_time - $entry_time) / 60; // الفرق بالدقائق

                                        if ($time_diff <= $allowed_minutes) {
                                        ?>
                                            <button type="submit" name="delete_btn_supa" class="btn btn-danger btn-xs"
                                                onclick="return confirm('Are you sure you want to delete this note?');">
                                                Delete Note
                                            </button>
                                        <?php
                                        }
                                        ?>

                                    </form>
                        <?php
                                }
                            }
                        }
                        ?>

                        <form action="<?= $editFormAction; ?>" method="post" style="display:inline-block">
                            <input type="hidden" name="id" value="<?= $row['sn']; ?>" />
                            <?php if ($_SESSION['super_admin'] == 1) { ?>
                                <button type="submit" name="edit-note-btn" class="btn btn-warning btn-xs">Edit Note</button>
                                <button type="submit" name="delete_btn_supa" class="btn btn-danger btn-xs"
                                    onclick="return confirm('Are you sure you want to delete this note?');">
                                    Delete Note
                                </button>
                            <?php } ?>
                        </form>


                    </td>
                </tr>
            <?php }    ?>
        </table>


        <?php

        $count_sql = "
        SELECT COUNT(*) 
        FROM notes 
        LEFT JOIN admin_users ON admin_users.id = notes.created_by
        WHERE notes.hospital_no = :hospital_no AND notes.status = '1'
        $search_tag";

        $total_stmt = $db->prepare($count_sql);
        $total_stmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);

        // Re-bind same dynamic params as data query
        foreach ($params as $key => $val) {
            // Avoid rebinding :hospital_no again
            if ($key == ':hospital_no') continue;
            $total_stmt->bindValue($key, $val);
        }

        $total_stmt->execute();
        $total_records = $total_stmt->fetchColumn();
        $total_pages   = ceil($total_records / $records_per_page);



        ?>

    <?php } else { ?>
        <div class="alert alert-info"><strong>No Existing Note(s) ...</strong></div>
    <?php } ?>

    <?php if ($medic_hx_stmt->rowCount() > 0) { ?>
        <table>
            <tr>
                <td> <?php if ($current_page > 1): $pg_no = $current_page - 1; ?>
                        <a href="#" onClick="load_more_mx_hx('<?= $pg_no; ?>')">Previous</a>
                    <?php endif; ?>
                </td>
                <td> <span>Page <?= $current_page; ?> of <?= $total_pages; ?></span></td>
                <td> <?php if ($current_page < $total_pages):  $pg_no = $current_page + 1; ?>
                        <a href="#" onClick="load_more_mx_hx('<?= $pg_no; ?>')">Next</a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php } ?>

    <?php exit;
} elseif (isset($_POST['loadMedicalHx_v3'])) {

    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    $hospital_no = $_POST['hospital_no'];
    $search_catera = $_POST['search_catera'];
    $current_page = $_POST['page_num'];

    if ($_SESSION['dispensory'] == '1') {
        $dept_id = $_SESSION['dept_id'];
        $search_tag = "and dept_id='$dept_id'";
    } elseif ($_SESSION['rights'] == 'PY') {
        $search_tag = "and notes_type='Physio'";
    } elseif ($_SESSION['dispensory'] == '0') {
        $search_tag = "";
    } else {
        $search_tag = "and (notes_type='D' or notes_type='C' or notes_type='Pharm' or notes_type='REQ_REMINDER') ";
    }

    if ($search_catera == 'procedure') {
        $notes_type = " (notes_type = 'post_opt_notes' or notes_type='pre_opt_notes') and tag='DR' and ";
    } elseif ($search_catera == 'nurse_report') {
        $notes_type = " (tag='NS' or tag='MF' ) and ";
    } elseif ($search_catera == 'plan') {
        $notes_type = " (notes_type = 'treatment' or notes_type='plan') and tag='DR' and ";
    } elseif ($search_catera == 'ward_round') {
        $notes_type = " (notes_type = 'ward_round' or notes_type='serv_review' or notes_type='pre-adm') and tag='DR' and ";
    } elseif ($search_catera == 'physio') {
        $notes_type = " notes_type = 'Physio' and tag='PY' and ";
    }
    $records_per_page = 10;
    $offset = ($current_page - 1) * $records_per_page;


    $medic_hx_stmt = $db->prepare("SELECT notes,notes_type,date_entry,date_entry2,prepared_by,no_updates FROM notes 
	WHERE hospital_no = :hospital_no and $notes_type status='1' $search_tag ORDER BY date_entry DESC LIMIT :limit OFFSET :offset");
    $medic_hx_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
    $medic_hx_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $medic_hx_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $medic_hx_stmt->execute();

    if ($medic_hx_stmt->rowCount() > 0) { ?>

        <table id="" class="table">
            <thead>
                <tr>
                    <th width="20%">Date</th>
                    <th width="80%">Details</th>
                </tr>
            </thead>
            <?php
            while ($row_visit = $medic_hx_stmt->fetch(PDO::FETCH_ASSOC)) {


                if ($row_visit['date_entry2'] != '') {
                    $date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($row_visit['date_entry2'])) . '<br>';
                    $date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($row_visit['date_entry'])) . '<br>';
                    $date_ .= "<b>Number of Edits </b>" .  $row_visit['no_updates'];
                } else {
                    $date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($row_visit['date_entry'])) . '<br>';
                }
                $date_ = "<div style='font-size:14px;'>$date_</div>";

            ?>
                <tr>
                    <td width="20%"><?php echo $date_; ?></td>
                    <td width="80%"><?php echo $row_visit['notes'] . '<br>' . "<i><b>Entered by: </b></i>" . $row_visit['prepared_by']; ?></td>
                </tr>
            <?php } ?>
        </table>

    <?php } else { ?>
        <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
    <?php }

    if ($medic_hx_stmt->rowCount() > 0) {
        $total_stmt = $db->prepare("SELECT COUNT(*) FROM notes WHERE notes.hospital_no = :hospital_no and $notes_type status='1' $search_tag");
        $total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $total_stmt->execute();
        $total_records = $total_stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);
    ?>


        <br>
        <?php if ($current_page > 1): $pg_no = $current_page - 1; ?>
            <a href="#" onClick="general_view2('<?= $search_catera; ?>','<?= $pg_no; ?>')"> Previous </a>
        <?php endif; ?>
        <span>Page <?= $current_page; ?> of <?= $total_pages; ?></span></td>
        <?php if ($current_page < $total_pages):  $pg_no = $current_page + 1; ?>
            <a href="#" onClick="general_view2('<?= $search_catera; ?>','<?= $pg_no; ?>')"> Next </a>
        <?php endif; ?>
    <?php } ?>

    <?php exit; ?>



    <?php } elseif (isset($_POST['loadMedicalHx_v2'])) {

    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    $general_view = $_POST['general_view'];
    $current_page = $_POST['page_num'];
    $hospital_no = $_POST['hospital_no'];
    $records_per_page = 10;

    if ($general_view == 'all_notes') {

        if ($_SESSION['dispensory'] == '1') {
            $dept_id = $_SESSION['dept_id'];
            $search_tag = "and dept_id='$dept_id'";
        } elseif ($_SESSION['rights'] == 'PY') {
            $search_tag = "and notes_type='Physio'";
        } else {
            $search_tag = "";
        }


        $offset = ($current_page - 1) * $records_per_page;

        $medic_hx_stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no = :hospital_no and status='1' $search_tag ORDER BY date_entry DESC LIMIT :limit OFFSET :offset");
        $medic_hx_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $medic_hx_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
        $medic_hx_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $medic_hx_stmt->execute();

        if ($medic_hx_stmt->rowCount() > 0) { ?>
            <table>
                <?php
                $n = 1;
                while ($mdication_ = $medic_hx_stmt->fetch(PDO::FETCH_ASSOC)) {

                    if ($mdication_['date_entry2'] != '') {
                        $date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($mdication_['date_entry2'])) . '<br>';
                        $date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($mdication_['date_entry'])) . '<br>';
                        $date_ .= "<b>Number of Edits </b>" .  $mdication_['no_updates'];
                    } else {
                        $date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($mdication_['date_entry'])) . '<br>';
                    }
                    $date_ = "<div style='font-size:14px;'>$date_</div>";


                ?>
                    <tr>
                        <td style="padding-left: 20px;">

                            <?php

                            $noteLabels = [
                                'C' => 'Consultation Notes/Complaints',
                                'D' => 'Diagnosis/Findings',
                                'note' => 'Progress Note',
                                'plan' => 'Medication Plan',
                                'PH' => 'Physical Examinations',
                                'CONS' => 'Consultation Notes',
                                'mgt' => 'Management',
                                'post_opt_notes' => 'Post Operation Notes',
                                'pre_opt_notes' => 'Pre Operation Notes',
                                'REQ_REMINDER' => 'Add Service Request',
                                'serv_review' => 'Ward Review Notes'
                            ];

                            $noteType = $mdication_['notes_type'];
                            $label = isset($noteLabels[$noteType]) ? $noteLabels[$noteType] : 'Medication Notes';

                            echo '<strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong>';

                            echo '<br>' . $mdication_['notes']; ?>

                            <br>
                            <small><b><i>Entered by: <?php echo $mdication_['prepared_by']; ?> <?php echo $date_; ///formatDateTime_($mdication_['date_entry']); 
                                                                                                ?></i></b></small>
                        </td>
                    </tr>
                <?php
                }
                ?>

            </table>

            <?php } else {
            echo 'Medical History Not Available!';
        }

        if ($medic_hx_stmt->rowCount() > 0) {
            $total_stmt = $db->prepare("SELECT COUNT(*) FROM notes WHERE hospital_no = :hospital_no and status='1' $search_tag");
            $total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $total_stmt->execute();
            $total_records = $total_stmt->fetchColumn();
            $total_pages = ceil($total_records / $records_per_page);
            echo '<br>';

            if ($current_page > 1): $pg_no = $current_page - 1; ?>
                <a href="#" onClick="general_view('<?= $general_view; ?>','<?= $pg_no; ?>')"> Previous </a>
            <?php endif; ?>
            <span>Page <?= $current_page; ?> of <?= $total_pages; ?></span></td>
            <?php if ($current_page < $total_pages):  $pg_no = $current_page + 1; ?>
                <a href="#" onClick="general_view('<?= $general_view; ?>','<?= $pg_no; ?>')"> Next </a>
        <?php endif;
        }
        ?>

        <?php exit;
    } else {

        ///////////////////////// MEDICAL SERVICES NOTES HERE 
        $offset = ($current_page - 1) * $records_per_page;


        $medic_hx_stmt = $db->prepare("SELECT notes,service,consultant_name,created_at FROM notes_services 
        WHERE hospital_no = :hospital_no and  status='1' and notes!='' ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $medic_hx_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $medic_hx_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
        $medic_hx_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $medic_hx_stmt->execute();

        if ($medic_hx_stmt->rowCount() > 0) { ?>

            <table id="" class="table">
                <thead>
                    <tr>
                        <th width="10%">Date</th>
                        <th width="40%">Details</th>
                    </tr>
                </thead>
                <?php
                while ($row_visit = $medic_hx_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr>
                        <td width="10%"><?php echo date('d M,Y', strtotime($row_visit['created_at'])) . '<br>' .
                                            date('h:i a', strtotime($row_visit['created_at'])); ?></td>
                        <td width="25%"><?php echo $row_visit['notes'] . '<br>' . "<i><b>Entered by: </b></i>" . $row_visit['consultant_name']; ?></td>
                    </tr>
                <?php } ?>
            </table>

        <?php } else { ?>
            <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
            <?php }

        if ($medic_hx_stmt->rowCount() > 0) {
            $total_stmt = $db->prepare("SELECT COUNT(*) FROM notes_services WHERE hospital_no = :hospital_no and notes!='' and status='1'");
            $total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $total_stmt->execute();
            $total_records = $total_stmt->fetchColumn();
            $total_pages = ceil($total_records / $records_per_page);
            echo '<br>';

            if ($current_page > 1): $pg_no = $current_page - 1; ?>
                <a href="#" onClick="general_view('<?= $general_view; ?>','<?= $pg_no; ?>')"> Previous </a>
            <?php endif; ?>
            <span>Page <?= $current_page; ?> of <?= $total_pages; ?></span></td>
            <?php if ($current_page < $total_pages):  $pg_no = $current_page + 1; ?>
                <a href="#" onClick="general_view('<?= $general_view; ?>','<?= $pg_no; ?>')"> Next </a>
<?php endif;
        }
    }
    exit;
} ?>


<div id="medication___notes___table_scroll_me">

    <form id="reset_form">
        <table>
            <tr>
                <td>
                    <label>Per Page</label><br>
                    <select name="records_per_page" id="records_per_page" class="form-control" onchange="records_per_page_()">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </td>

                <td>

                    <label for="" class="">Search for Anything/Keywords</label><br>
                    <select id="search_anything" name="search_anything" style="width:350px;" onchange="documentation()"></select>
                </td>

                <td>

                    <label>Sort Medical Hx By</label><br>
                    <select name="documentation_type" id="documentation_type" class="form-control" onchange="documentation()">
                        <option value="" selected>All</option>
                    </select>


                </td>

                <td>
                    <label for="" class="">Search By Name</label><br>
                    <select id="doctor_names" name="doctor_names" style="width:350px;" onchange="documentation()"></select>
                </td>

                <td>
                    <label class="font-normal">Filter Date Range</label>
                    <div class="input-daterange input-group" id="">
                        <input type="date" class="input-sm form-control" name="start_date" id="start_date" onchange="documentation()" />
                        <span class="input-group-addon">to</span>
                        <input type="date" class="input-sm form-control" name="end_date" id="end_date" onchange="documentation()" />
                    </div>
                </td>

                <td> <label class="font-normal">.</label><br>
                    <button type="button" class="btn btn-primary btn btn-sm" onclick="resetControls()">Reset</button>
                </td>
            </tr>
        </table>
    </form>


    <hr>


    <table class="table table-striped no-footer dtr-inline" id="medication___notes___table" style="font-size:15px;">
        <tbody id="medical-history-content-area">
            <tr class="gradeX">
                <td colspan="2">
                    <h3 class="text-danger text-center">Loading, please wait...</h3>
                </td>
            </tr>
        </tbody>
    </table>


</div>
<?php
if (!isset($med_hx_url)) {
    $med_hx_url = '_medication_hx.php';
}
?>