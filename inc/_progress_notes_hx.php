<?php
session_start();
include("../Connections/Conn.php");

function date_diff_day($date_entry)
{
    $now = new DateTime();
    $entry = new DateTime($date_entry);
    $diff = $now->diff($entry);
    return $diff->days;
}



$records_per_page = 5;
$current_page = isset($_POST['page_num']) ? (int) $_POST['page_num'] : 1;
$current_page = max($current_page, 1); // Ensure page >= 1
$offset = ($current_page - 1) * $records_per_page;

$hospital_no = isset($_POST['hospital_no']) ? $_POST['hospital_no'] : $hospital_no;
$appointment_number = isset($_POST['appointment_number']) ? $_POST['appointment_number'] : '';

$search_tag = "AND notes_type = 'note' AND tag = 'NS'"; // Default nurse notes filter

// Total count (used for pagination)
$total_stmt = $db->prepare("
    SELECT COUNT(*) FROM notes 
    WHERE hospital_no = :hospital_no AND status = '1' $search_tag
");
$total_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Paginated notes query
$notes_stmt = $db->prepare("
    SELECT * FROM notes 
    WHERE hospital_no = :hospital_no AND status = '1' $search_tag 
    ORDER BY date_entry DESC 
    LIMIT :limit OFFSET :offset
");
$notes_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$notes_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
$notes_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$notes_stmt->execute();

if ($notes_stmt->rowCount() > 0):
?>
    <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 16px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Nurse Report</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $n = $offset + 1;
            foreach ($notes_stmt->fetchAll(PDO::FETCH_ASSOC) as $note):
                // Format created/edited info
                if (!empty($note['date_entry2'])) {
                    $date_ = "<b>Created At:</b> " . date('d-m-Y h:i a', strtotime($note['date_entry2'])) . "<br>";
                    $date_ .= "<b>Edited At:</b> " . date('d-m-Y h:i a', strtotime($note['date_entry'])) . "<br>";
                    $date_ .= "<b>Number of Edits:</b> " . htmlspecialchars($note['no_updates']);
                } else {
                    $date_ = "<b>Created At:</b> " . date('d M,Y h:i a', strtotime($note['date_entry'])) . "<br>";
                }
                $date_ = "<div style='font-size:14px;'>$date_</div>";

                // Permission to edit
                $can_edit = (
                    (!empty($note['created_by']) && $note['created_by'] == $_SESSION['id']) ||
                    ($note['prepared_by'] == $_SESSION['fullname'])
                );
            ?>
                <tr class="gradeX">
                    <td><?php echo $n++; ?></td>
                    <td>
                        <?php
                        $app_no = $note["app_no"];
                        if ($appointment_number == $note["app_no"]) {
                            echo '<b style="color:red;">Current Admission Note: </b>';
                        }

                        echo nl2br($note["notes"]); ?>
                        <small>
                            <b><i>Entered by: <?php echo htmlspecialchars($note['prepared_by']); ?></i></b><br>
                            <?php echo $date_; ?>
                        </small>
                        <?php
                        if ($can_edit && function_exists('date_diff_day') && date_diff_day($note['date_entry']) <= 1 && $note['notes_type'] != 'serv_review'): ?>
                            <br>
                            <button class="btn btn-xs btn-warning" onclick="edit_progress_note('<?php echo $note['sn']; ?>')">
                                <i class="fa fa-edit"></i> &nbsp;Edit Note
                            </button>



                            <?php
                            $entry_time = strtotime($note['date_entry']); // وقت الإدخال
                            $current_time = time(); // الوقت الحالي

                            $allowed_minutes = 5; // المدة المسموحة (يمكن تغييرها)
                            $time_diff = ($current_time - $entry_time) / 60; // الفرق بالدقائق

                            if ($time_diff <= $allowed_minutes) {
                            ?>
                                <form action="<?= $editFormAction; ?>" method="post" style="display:inline-block">
                                    <input type="hidden" name="id" value="<?= $note['sn']; ?>" />

                                    <button type="submit" name="delete_btn_supa" class="btn btn-danger btn-xs"
                                        onclick="return confirm('Are you sure you want to delete this note?');">
                                        Delete Note
                                    </button>
                                </form>
                            <?php
                            }
                            ?>


                        <?php endif; ?>






                        <?php if ($_SESSION['super_admin'] == 1) { ?>
                            <button class="btn btn-xs btn-warning" onclick="edit_progress_note('<?php echo $note['sn']; ?>')">
                                <i class="fa fa-edit"></i> &nbsp;Edit Note
                            </button>
                            <form action="<?= $editFormAction; ?>" method="post" style="display:inline-block">
                                <input type="hidden" name="id" value="<?= $note['sn']; ?>" />

                                <button type="submit" name="delete_btn_supa" class="btn btn-danger btn-xs"
                                    onclick="return confirm('Are you sure you want to delete this note?');">
                                    Delete Note
                                </button>
                            </form>
                        <?php } ?>




                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="text-center" style="margin-top: 10px;">
        <?php if ($current_page > 1): ?>
            <a href="#" onclick="ns_report_page('<?= $app_no; ?>','<?php echo $current_page - 1; ?>')">Previous</a>
        <?php endif; ?>

        <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

        <?php if ($current_page < $total_pages): ?>
            <a href="#" onclick="ns_report_page('<?= $app_no; ?>','<?php echo $current_page + 1; ?>')">Next</a>
        <?php endif; ?>
    </div>
<?php
else:
    echo '<h4 class="text-center">No Report Found!</h4>';
endif;
?>