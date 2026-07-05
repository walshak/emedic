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
    $vista_table = 'old_notes';
    if (!empty($notes_type)) {
        if (in_array($notes_type, ['Lab', 'Scan'])) {
            $vista_table = 'vista_investigations';
            $notesTitle = 'Investigation Notes';
        }
        $notes_type_sql = " AND notes_type = '$notes_type' ";
    }


    $sqlQuery = "SELECT * FROM $vista_table WHERE hospital_no = ? $notes_type_sql  ORDER BY date_entry DESC ";
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
                    <td width="20%"><?php echo $row['prepared_by'] . '<br>' . date('d M,Y', strtotime($row['date_entry'])) . ' ' .
                                        date('h:i a', strtotime($row['date_entry'])); ?>
                        <hr>

                    </td>
                </tr>
            <?php }    ?>
        </table>
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

    $hospital_no = $_POST['hospital_no'];
    $rstSelect2 = $db->prepare("SELECT * FROM notes WHERE hospital_no = ? and status ='1' and (notes_type='C' or notes_type='REQ_REMINDER') ORDER BY sn DESC LIMIT 80");
    $rstSelect2->execute(array($hospital_no));
    if ($rstSelect2->rowCount() > 0) {
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
            while ($row = $rstSelect2->fetch(PDO::FETCH_ASSOC)) {
            ?>
                <tr>
                    <td width="2%"><?php echo $n++; ?></td>
                    <td width="78%">
                        <?php
                        if ($row['notes_type'] == 'REQ_REMINDER') { ?><strong style="color: darkred; ">Specialist Request:</strong><br><?php } ?>
                        <?php echo $row['notes']; ?></td>
                    <td width="20%"><?php echo $row['prepared_by'] . '<br>' . date('d M,Y', strtotime($row['date_entry'])) . ' ' .
                                        date('h:i a', strtotime($row['date_entry'])); ?>
                        <hr>
                        <?php
                        if (($row['created_by'] == $_SESSION['id'] && !empty($row['created_by'])) || ($row['prepared_by'] == $_SESSION['fullname'])) {
                            if (date_diff_day($row['date_entry']) < 1) { ?>
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
    <?php } else { ?>
        <div class="alert alert-info"><strong>No Existing Note(s)</strong></div>
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
    // nurse_report

    if ($search_catera == 'procedure') {
        $notes_type = " (notes_type = 'post_opt_notes' or notes_type='pre_opt_notes') and tag='DR' and ";
    } elseif ($search_catera == 'nurse_report') {
        $notes_type = " notes_type = 'note' and tag='NS' and ";
    } elseif ($search_catera == 'plan') {
        $notes_type = " (notes_type = 'treatment' or notes_type='plan') and tag='DR' and ";
    } elseif ($search_catera == 'ward_round') {
        $notes_type = " (notes_type = 'ward_round' or notes_type='serv_review') and tag='DR' and ";
    } elseif ($search_catera == 'physio') {
        $notes_type = " notes_type = 'Physio' and tag='PY' and ";
    }


    $rstSelect = $db->prepare("SELECT notes,notes_type,date_entry,prepared_by  FROM notes 
	WHERE hospital_no = ? and $notes_type status='1' ORDER BY date_entry DESC LIMIT 50");
    $rstSelect->execute(array($hospital_no));
    if ($rstSelect->rowCount() > 0) { ?>

        <table id="" class="table">
            <thead>
                <tr>
                    <th width="10%">Date</th>
                    <th width="40%">Details</th>
                </tr>
            </thead>
            <?php
            while ($row_visit = $rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
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

    exit;
} elseif (isset($_POST['loadMedicalHx_v2'])) {



    session_start();
    include("../Connections/Conn.php");
    include("_session.php");
    include('objects.php');
    include('helpers.php');

    $hospital_no = cleanInput($_POST['hospital_no']);
    $medication_hx_stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no = ? and status = '1' ORDER BY date_entry DESC LIMIT 250");
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
                            echo '<strong>Medication Notes: </strong>';
                        }

                        echo '<br>' . $mdication_['notes']; ?>

                        <br>
                        <small><b><i>Entered by: <?php echo $mdication_['prepared_by']; ?> Date: <?php echo formatDateTime_($mdication_['date_entry']); ?></i></b></small>
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