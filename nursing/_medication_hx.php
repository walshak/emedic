<?php

if (isset($_POST['loadMedicalHx'])) {

    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    if ($_SESSION['dispensory'] == '1') {
        $dept_id = $_SESSION['dept_id'];
        $search_tag = "and dept_id='$dept_id'";
    } elseif ($_SESSION['rights'] == 'PY') {
        $search_tag = "and notes_type='Physio'";
    } else {
        $search_tag = "and (notes_type='D' or notes_type='C' or notes_type='REQ_REMINDER') ";
    }

    $current_page = $_POST['page_num'];
    if ($current_page == '') {
        $current_page = 1;
    }

    $records_per_page = 20;
    $offset = ($current_page - 1) * $records_per_page;


    $hospital_no = $_POST['hospital_no'];
    $medic_hx_stmt = $db->prepare("SELECT notes.*, hremp.Designation, specialists.name as spec FROM notes 
    LEFT JOIN admin_users ON admin_users.id = notes.created_by
    LEFT JOIN hremp ON admin_users.EmployeeCode = hremp.EmployeeCode
    LEFT JOIN specialists ON admin_users.specialist = specialists.id
WHERE notes.hospital_no = :hospital_no and notes.status ='1' $search_tag ORDER BY sn DESC LIMIT :limit OFFSET :offset");
    $medic_hx_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
    $medic_hx_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $medic_hx_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $medic_hx_stmt->execute();

    if ($medic_hx_stmt->rowCount() > 0) {
        $n = 1; ?>
        <table id="resp_table" class="table toggle-square">
            <thead>
                <tr>
                    <th width="2%">#</th>
                    <th width="78%">Consultation Notes</th>
                    <th width="20%"><i>Entered by</i></th>
                </tr>
            </thead>

            <?php
            while ($row = $medic_hx_stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
                <tr>
                    <td width="2%"><?php echo $n++; ?></td>
                    <td width="78%">
                        <?php
                        if ($row['notes_type'] == 'REQ_REMINDER') { ?><strong style="color: darkred; ">Specialist Request:</strong><br><?php } ?>
                        <?php echo $row['notes']; ?></td>
                    <td width="20%"><?php echo "<b>" . $row['prepared_by'] . " <br><i>(" . (($row['spec']) ? $row['spec'] : $row['Designation']) . ")</i>" . "</br>" . '<br>' . date('d M,Y', strtotime($row['date_entry'])) . ' ' .
                                        date('h:i a', strtotime($row['date_entry'])); ?>
                        <hr>
                        <?php
                        if (($row['created_by'] == $_SESSION['id'] && !empty($row['created_by'])) || ($row['prepared_by'] == $_SESSION['fullname'])) {
                            if (date_diff_day($row['date_entry']) <= 1) { ?>
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
                                        <input type="hidden" name="notes_type" value="<?= $row['notes_type']; ?>" />
                                        <button type="submit" name="edit-note-btn" class="btn btn-warning btn-xs">Edit Note</button>
                                    </form>
                        <?php
                                }
                            }
                        }
                        ?>
                    </td>
                </tr>
            <?php }    ?>
        </table>


        <?php

        $total_stmt = $db->prepare("SELECT COUNT(*) FROM notes 
LEFT JOIN admin_users ON admin_users.id = notes.created_by
LEFT JOIN hremp ON admin_users.EmployeeCode = hremp.EmployeeCode
LEFT JOIN specialists ON admin_users.specialist = specialists.id
WHERE notes.hospital_no = :hospital_no and notes.status ='1' $search_tag");
        $total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $total_stmt->execute();
        $total_records = $total_stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);


        ?>

    <?php } else { ?>
        <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
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
        $search_tag = "and (notes_type='D' or notes_type='C' or notes_type='REQ_REMINDER') ";
    }

    if ($search_catera == 'procedure') {
        $notes_type = " (notes_type = 'post_opt_notes' or notes_type='pre_opt_notes') and tag='DR' and ";
    } elseif ($search_catera == 'nurse_report') {
        $notes_type = " (tag='NS' or tag='MF' ) and ";
    } elseif ($search_catera == 'plan') {
        $notes_type = " (notes_type = 'treatment' or notes_type='plan') and tag='DR' and ";
    } elseif ($search_catera == 'ward_round') {
        $notes_type = " (notes_type = 'ward_round' or notes_type='serv_review') and tag='DR' and ";
    } elseif ($search_catera == 'physio') {
        $notes_type = " notes_type = 'Physio' and tag='PY' and ";
    }


    $records_per_page = 20;
    $offset = ($current_page - 1) * $records_per_page;


    $medic_hx_stmt = $db->prepare("SELECT notes,notes_type,date_entry,prepared_by FROM notes 
	WHERE hospital_no = :hospital_no and $notes_type status='1' $search_tag ORDER BY date_entry DESC LIMIT :limit OFFSET :offset");
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
                    <td width="10%"><?php echo date('d M,Y', strtotime($row_visit['date_entry'])) . '<br>' .
                                        date('h:i a', strtotime($row_visit['date_entry'])); ?></td>
                    <td width="25%"><?php echo $row_visit['notes'] . '<br>' . "<i><b>Entered by: </b></i>" . $row_visit['prepared_by']; ?></td>
                </tr>
            <?php } ?>
        </table>

    <?php } else { ?>
        <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
    <?php }

    if ($medic_hx_stmt->rowCount() > 0) {
        $total_stmt = $db->prepare("SELECT COUNT(*) FROM notes WHERE hospital_no = :hospital_no and $notes_type status='1' $search_tag");
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
    <?php endif;
    }
    ?>

    <?php exit;
} elseif (isset($_POST['loadMedicalHx_v2'])) {



    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    $hospital_no = cleanInput($_POST['hospital_no']);
    $medication_hx_stmt = $db->prepare(
        "SELECT notes.*, hremp.Designation, specialists.name as spec FROM notes 
        LEFT JOIN admin_users ON admin_users.id = notes.created_by
        LEFT JOIN hremp ON admin_users.EmployeeCode = hremp.EmployeeCode
        LEFT JOIN specialists ON admin_users.specialist = specialists.id
        WHERE notes.hospital_no = ? and notes.status ='1' $search_tag ORDER BY sn DESC LIMIT 250"
    );
    $medication_hx_stmt->execute(array($hospital_no));

    if ($medication_hx_stmt->rowCount() > 0) { ?>
        <table>
            <?php
            $n = 1;
            while ($mdication_ = $medication_hx_stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
                <tr>
                    <td style="padding-left: 20px;">

                        <?php

                        if ($mdication_['notes_type'] == 'C') {
                            echo '<strong>Complaints</strong>';
                        } else  if ($mdication_['notes_type'] == 'D') {
                            echo '<strong>Diagnosis/Findings: </strong>';
                        } else  if ($mdication_['notes_type'] == 'note') {
                            echo '<strong>Progress Note: </strong>';
                        } else  if ($mdication_['notes_type'] == 'plan') {
                            echo '<strong>Medication Plan: </strong>';
                        } else  if ($mdication_['notes_type'] == 'PH') {
                            echo '<strong>Physical Examinations: </strong>';
                        } else  if ($mdication_['notes_type'] == 'CONS') {
                            echo '<strong>Consultation Notes: </strong>';
                        } else  if ($mdication_['notes_type'] == 'mgt') {
                            echo '<strong>Management: </strong>';
                        } else  if ($mdication_['notes_type'] == 'post_opt_notes') {
                            echo '<strong>Post Operation Notes: </strong>';
                        } else  if ($mdication_['notes_type'] == 'pre_opt_notes') {
                            echo '<strong>Pre Operation Notes: </strong>';
                        } else  if ($mdication_['notes_type'] == 'REQ_REMINDER') {
                            echo '<strong>Add Service Request: </strong>';
                        } else  if ($mdication_['notes_type'] == 'serv_review') {
                            echo '<strong>Ward Review Notes: </strong>';
                        } else {
                            echo '<strong>Notes: </strong>';
                        }

                        echo '<br>' . $mdication_['notes']; ?>

                        <br>
                        <small><b><i>Entered by: <?php echo $mdication_['prepared_by'] . " (" . (($mdication_['spec']) ? $mdication_['spec'] : $mdication_['Designation']) . ")"; ?> Date: <?php echo formatDateTime_($mdication_['date_entry']); ?></i></b></small>
                    </td>
                </tr>
            <?php
            }
            ?>

        </table>

<?php
    } else {
        echo 'Medical History Not Available!';
    }

    exit;
} ?>


<div style=" max-height:800px; overflow:auto">

    <input type="text" class="form-control" Style="height:40px; padding:10px;border-bottom:solid #544E4E #000;font-weight:bolder; color:#88696A" id="medication_search_input" onkeydown="filterTbleFunction(this.id, 'medication___notes___table', 0, 1 )"
        placeholder="Search patient's medical history here ... ">
    <br>

    <table class="table table-striped  no-footer dtr-inline" id="medication___notes___table" style="font-size:15px;">
        <tbody id="medical-history-content-area">
            <tr class="gradeX">
                <td>
                    <h3 class="text-danger text-center">Loading, please wait...</h3>
                </td>
                <td>
                </td>
        </tbody>
    </table>
</div>
<?php
if (!isset($med_hx_url)) {
    $med_hx_url = '_medication_hx.php';
}
?>