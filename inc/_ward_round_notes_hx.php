<?php
session_start();
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');

if (isset($_POST['patient_ward_rounds'])) {

    $appointment_number = $_POST['appointment_number'];
    $hospital_no = $_POST['patient_ward_rounds'];
    $current_page = $_POST['page_num'];

    $records_per_page = 10;
    $offset = ($current_page - 1) * $records_per_page;

    $medication_hx_stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no = :hospital_no AND (notes_type = 'ward_round' OR notes_type = 'plan' OR notes_type = 'treatment') AND status = '1' ORDER BY date_entry DESC LIMIT :limit OFFSET :offset");
    $medication_hx_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
    $medication_hx_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $medication_hx_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $medication_hx_stmt->execute();

    if ($medication_hx_stmt->rowCount() > 0) {
        $mdication_hx = $medication_hx_stmt->fetchAll(PDO::FETCH_ASSOC);
?>


        <?php
        $total_stmt = $db->prepare("SELECT COUNT(*) FROM notes WHERE hospital_no = :hospital_no 
    AND (notes_type = 'ward_round' OR notes_type = 'plan' OR notes_type = 'treatment') 
    AND status = '1'");
        $total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $total_stmt->execute();
        $total_records = $total_stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);

        ?>
        <div style="max-height: 800px; overflow: auto;">
            <table class="table table-bordered" style="font-size: 16px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i>Total Reports Found</i> ( <?= $total_records; ?> )</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $n = 1;
                    foreach ($mdication_hx as $key => $mdication_) {

                        if ($mdication_['date_entry2'] != '') {
                            $date_ = "<b>Created At</b> " .  date('d-m-Y h:i a', strtotime($mdication_['date_entry2'])) . ' & ';
                            $date_ .= "<b>Edited At</b> " .  date('d-m-Y h:i a', strtotime($mdication_['date_entry'])) . '<br>';
                            $date_ .= "<b>Number of Edits </b>" .  $mdication_['no_updates'];
                        } else {
                            $date_ = "<b>Created At</b> " .  date('d M,Y h:i a', strtotime($mdication_['date_entry'])) . '<br>';
                        }
                        $date_ = "<div style='font-size:14px;'>$date_</div>";

                    ?>
                        <tr class="gradeX">
                            <td><?= $n; ?></td>
                            <td>
                                <small>
                                    <?php
                                    if ($mdication_["notes_type"] == 'ward_round') {
                                        echo "<strong>[ Doctor's Ward Round ]</strong><br>";
                                    }
                                    if ($mdication_["notes_type"] == 'plan') {
                                        echo "<strong>[ Plan ]</strong><br>";
                                    }
                                    if ($mdication_["notes_type"] == 'treatment') {
                                        echo "<strong>[ Treatment ]</strong><br>";
                                    }
                                    ?>
                                </small>
                                <?= $mdication_["notes"]; ?>
                                <small><b><i>Entered by: <?= $mdication_['prepared_by']; ?><?= $date_; ///formatDateTime_($mdication_['date_entry']); 
                                                                                            ?></i></b></small>
                                <?php
                                if (($mdication_['created_by'] == $_SESSION['id'] && !empty($mdication_['created_by'])) || ($mdication_['prepared_by'] == $_SESSION['fullname'])) {


                                    // Assuming $mdication_['date_entry'] is in the format 'Y-m-d H:i:s'


                                    if (date_diff_day($mdication_['date_entry']) <= 1 && $mdication_['notes_type'] != 'serv_review') {
                                ?>
                                        <br>
                                        <button class="btn btn-xs btn-warning" onClick="edit_progress_note('<?= $mdication_['sn']; ?>')">
                                            <i class="fa fa-edit"></i>&nbsp;Edit
                                        </button>


                                        <?php
                                        $entry_time = strtotime($mdication_['date_entry']); // وقت الإدخال
                                        $current_time = time(); // الوقت الحالي

                                        $allowed_minutes = 5; // المدة المسموحة (يمكن تغييرها)
                                        $time_diff = ($current_time - $entry_time) / 60; // الفرق بالدقائق

                                        if ($time_diff <= $allowed_minutes) {
                                        ?>
                                            <form action="<?= $editFormAction; ?>" method="post" style="display:inline-block">
                                                <input type="hidden" name="id" value="<?= $mdication_['sn']; ?>" />

                                                <button type="submit" name="delete_btn_supa" class="btn btn-danger btn-xs"
                                                    onclick="return confirm('Are you sure you want to delete this note?');">
                                                    Delete Note
                                                </button>
                                            </form>
                                        <?php
                                        }
                                        ?>
                                <?php
                                    }
                                }
                                ?>


                                <?php if ($_SESSION['super_admin'] == 1) { ?>
                                    <button class="btn btn-xs btn-warning" onClick="edit_progress_note('<?= $mdication_['sn']; ?>')">
                                        <i class="fa fa-edit"></i>&nbsp;Edit
                                    </button>
                                    <form action="<?= $editFormAction; ?>" method="post" style="display:inline-block">
                                        <input type="hidden" name="id" value="<?= $mdication_['sn']; ?>" />

                                        <button type="submit" name="delete_btn_supa" class="btn btn-danger btn-xs"
                                            onclick="return confirm('Are you sure you want to delete this note?');">
                                            Delete Note
                                        </button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php
                        $n++;
                    }
                    ?>
                </tbody>
            </table>




            <br>
            <?php if ($current_page > 1): $pg_no = $current_page - 1; ?>
                <a href="#" onClick="show_ward_notes('<?= $pg_no; ?>')"> <b> Previous</b> </a>
            <?php endif; ?>
            <span>Page <?= $current_page; ?> of <?= $total_pages; ?></span></td>
            <?php if ($current_page < $total_pages):  $pg_no = $current_page + 1; ?>
                <a href="#" onClick="show_ward_notes('<?= $pg_no; ?>')"> <b> Next</b> </a>
            <?php endif; ?>


        </div>
<?php
    } else {
        echo '<h4 class="text-center"> Notes Not Available! </h4>';
    }
}




if (isset($_POST['edit_sn'])) {

    $response = array(
        'notes' => '',
        'date_entry' => '',
        'date_entry2' => '',
        'notes_sn' => ''
    );

    $edit_sn = $_POST['edit_sn'];
    $stmt_getd = $db->prepare("SELECT notes,date_entry,date_entry2,notes_type FROM notes WHERE sn = ?");
    $stmt_getd->execute(array($edit_sn));
    $rwxx = $stmt_getd->fetch(PDO::FETCH_ASSOC);
    $response['notes'] = $rwxx['notes'];
    $response['date_entry'] = $rwxx['date_entry'];
    $response['date_entry2'] = $rwxx['date_entry2'];
    $response['notes_type'] = $rwxx['notes_type'];
    $response['notes_sn'] = $edit_sn;
    echo  json_encode($response);
    exit;
}
?>