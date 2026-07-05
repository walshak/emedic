<?php
include("../inc/session.php");
include("../Connections/Conn.php");

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}



$setdate = date("Y-m-d H:i:s");


if (isset($_POST["query_id"]) && isset($_POST['reply_note'])) {
    $ECode = $_SESSION['EmployeeCode'];
    $query_id = $_POST["query_id"];
    $reply_note = $_POST['reply_note'];
    $done_by = $_SESSION['fullname'];
    // $date = $_POST['date'];

    if (!empty($_FILES['document_file']['name'])) {
        $title = $_POST['title'];
        $target_dir = "../uploads/staff/";
        $fileName = $ECode . "_" . $title . "_" . basename($_FILES["document_file"]["name"]);
        $target_file = $target_dir . $fileName;
        $uploadOk = 1;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($imageFileType == 'pdf' || $imageFileType == 'jpg' || $imageFileType == 'png' || $imageFileType == 'jpeg') {
            $tmp_name = $_FILES['document_file']['tmp_name'];

            if (move_uploaded_file($tmp_name, $target_file)) {

                $save = $db->prepare("INSERT INTO staff_other_docs (staff_no,document_file,document_title,done_by) VALUES (?,?,?,?)");

                if ($save->execute([$ECode, $fileName, $title, $done_by])) {
                    $error_status = 2;
                    $error_msg = 'Document is uploaded successfully... User queried';



                    // Insert into `emp_query_history` using prepared statements
                    $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, file, is_reply) 
                        VALUES (:ecode, :reason, :done_by, :file, :is_reply)";
                    $stmt = $db->prepare($historySQL);

                    // Bind parameters
                    $stmt->bindParam(':ecode', $ECode, PDO::PARAM_STR);
                    $stmt->bindParam(':reason', $reply_note, PDO::PARAM_STR);
                    $stmt->bindParam(':done_by', $done_by, PDO::PARAM_STR);
                    $stmt->bindParam(':file', $fileName, PDO::PARAM_STR);
                    $stmt->bindValue(':is_reply', 1, PDO::PARAM_INT);

                    // Execute the insert statement
                    $stmt->execute();

                    // Update `emp_query_history` using prepared statements
                    $updateSQL = "UPDATE emp_query_history SET has_reply = :has_reply WHERE sn = :sn";
                    $stmt = $db->prepare($updateSQL);

                    // Bind parameters
                    $stmt->bindValue(':has_reply', 1, PDO::PARAM_INT);
                    $stmt->bindParam(':sn', $query_id, PDO::PARAM_STR);

                    // Execute the update statement
                    $stmt->execute();

                    $sv = 1;
                    //die($error_msg);
                } else {
                    $error_msg = 'Operation Failed / Already uploaded...';
                    $upload_err = $error_msg;
                    //die($error_msg);
                }
            } else {
                $error_msg = "Sorry, there was an error uploading your file.";
                $upload_err = $error_msg;
                //die($error_msg);
            }
        } else {
            $error_msg = "Bad Request! Invalid file... ";
            $upload_err = $error_msg;
            //die($error_msg);
        }
?>
<?php
    } else {

        //insert query history
        // Insert into `emp_query_history` using prepared statements
        $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, is_reply) 
            VALUES (:ecode, :reason, :done_by, :is_reply)";
        $stmt = $db->prepare($historySQL);

        // Bind parameters
        $stmt->bindParam(':ecode', $ECode, PDO::PARAM_STR);
        $stmt->bindParam(':reason', $reply_note, PDO::PARAM_STR);
        $stmt->bindParam(':done_by', $done_by, PDO::PARAM_STR);
        $stmt->bindValue(':is_reply', 1, PDO::PARAM_INT);

        // Execute the insert statement
        $stmt->execute();

        // Update `emp_query_history` using prepared statements
        $updateSQL = "UPDATE emp_query_history SET has_reply = :has_reply WHERE sn = :sn";
        $stmt = $db->prepare($updateSQL);

        // Bind parameters
        $stmt->bindValue(':has_reply', 1, PDO::PARAM_INT);
        $stmt->bindParam(':sn', $query_id, PDO::PARAM_STR);

        $stmt->execute();
        $sv = 1;
    }
    //header("location:index.php?hr&ECode=$ECode");				
}

?>


<!DOCTYPE html>
<html>

<head>

    <?php include("../inc/header.php"); ?>

<body>

    <div id="wrapper">

        <?php include("nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("nav_header.php"); ?>

            <div class="wrapper wrapper-content">

                <div class="row">

                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Query history </h5>
                            </div>

                            <div class="ibox-content">

                                <?php
                                $staff_id = $_SESSION['EmployeeCode'];
                                $query = $db->prepare("SELECT * FROM emp_query_history  INNER JOIN hremp ON emp_query_history.Ecode = hremp.EmployeeCode WHERE emp_query_history.Ecode = ?");
                                if ($query->execute([$staff_id])) {
                                    $history = $query->fetchAll();
                                }
                                ?>
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <!-- <h4 class="modal-title" id="">Query History</h4> -->
                                </div>
                                <?php if (!empty($history)) : ?>
                                    <div class="modal-body" id="query_history_body">
                                        <h3>Query History for <?php echo $history[0]['FirstName'] . " " . $history[0]['LastName']; ?></h3>
                                        <table class=" table table-striped">
                                            <thead>
                                                <th>Date</th>
                                                <th>Note</th>
                                                <th>Document(if any)</th>
                                                <th>Action</th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                                $target_dir = "../uploads/staff/";
                                                $staff_id = $_GET['ECode'];
                                                ?>
                                                <?php foreach ($history as $entry) : ?>

                                                    <tr>
                                                        <td>
                                                            <small><?php echo date('d M y', strtotime($entry['date'])); ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if ($entry['is_reply'] == 1) : ?>
                                                                <p><?php echo $entry['reason']; ?></p>
                                                                <span class="badge"><small>Replied By <?php echo $entry['done_by'] ?></small></span>
                                                            <?php elseif ($entry['is_pardon'] == 1) : ?>
                                                                <p><?php echo $entry['reason']; ?></p>
                                                                <span class="badge"><small>Pardoned By <?php echo $entry['done_by'] ?></small></span>
                                                            <?php elseif ($entry['is_suspension_or_restore'] == 1) : ?>
                                                                <p><?php echo $entry['reason']; ?></p>
                                                                <span class="badge badge-danger"><small>Suspended By <?php echo $entry['done_by'] ?></small></span>
                                                            <?php elseif ($entry['is_suspension_or_restore'] == 2) : ?>
                                                                <p><?php echo $entry['reason']; ?></p>
                                                                <span class="badge badge-success"><small>Restored By <?php echo $entry['done_by'] ?></small></span>
                                                            <?php else : ?>
                                                                <p><?php echo $entry['reason']; ?></p>
                                                                <span class="badge"><small>Issued By <?php echo $entry['done_by'] ?></small></span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($entry['file'])) : ?>
                                                                <a href="<?php echo $target_dir . $entry['file'] ?>">
                                                                    <strong>
                                                                        <?php echo $entry['file'] ?>
                                                                    </strong>
                                                                </a>
                                                            <?php else : ?>
                                                                N/A
                                                            <?php endif ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($entry['is_reply'] == 0 && $entry['has_reply'] == 0 && $entry['is_pardon'] == 0 && $entry['is_suspension_or_restore'] == 0) : ?>
                                                                <a class="reply_query_btn" onclick="show_reply_modal(<?php echo $entry[0] ?>)"><strong>[ Reply Query ]</strong></a>
                                                            <?php endif ?>
                                                        </td>
                                                    </tr>
                                                    <?php $i++; ?>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                        <a href="javascript:Clickheretoprint_history_query()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                                    </div>
                                <?php else : ?>
                                    <p>No Data to show</p>
                                <?php endif ?>



                                <div class="modal inmodal fade" id="reply_query_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h4 class="modal-title" id="">Reply Query</h4>
                                            </div>

                                            <div class="modal-body">
                                                <form action="" method="POST" enctype="multipart/form-data">
                                                    <div class="form_sep">
                                                        <label for="" class="req">Enter Notes</label>
                                                        <textarea name="reply_note" id="query_reason_field" class="form-control req" required placeholder="Enter your notes..." rows="10"></textarea>
                                                    </div>
                                                    <!-- <div class="form_sep">
                                                        <input type="date" name="date" class="form-control req" required>
                                                    </div> -->
                                                    <div class="form_sep">
                                                        <label for="reg_input_no">Document [jpg, png, pdf] (optional) </label>
                                                        <input type="file" name="document_file" id="document_file" class="form-control">
                                                    </div>

                                                    <input type="hidden" class="form-control" name="query_id" id="the_emp_id_field" readonly />
                                                    <input type="hidden" maxlength="100" name="title" id="title" value="Query_Reply<?php echo date('d-m-Y:H:i:s') ?>" required>
                                                    <br>
                                                    <button class="btn btn-primary">Reply</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>



            </div>
            <?php include("../inc/footer.php"); ?>

        </div>
    </div>

    <?php include('../modal_lock.php'); ?>
    <?php include("../inc/footer_scripts.php"); ?>

    <!-- Mainly scripts -->
    <script src="../js/jquery-2.1.1.js"></script>

    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>

    <!-- iCheck -->
    <script src="../js/plugins/iCheck/icheck.min.js"></script>
    <script>
        $('#data_1 .input-group.date').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });

        $(document).ready(function() {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
    </script>
    <script>
        function show_reply_modal(query_id) {
            $('#the_emp_id_field').val(query_id);
            $('#reply_query_modal').modal('show');
        }
    </script>
    <script>
        <?php if (isset($_GET['sv']) or isset($_GET['drn']) or $sv == '1') { ?>
            toastr.success('<?php echo 'Successful'; ?>', 'Successfully', {
                timeOut: 5000
            })
        <?php } ?>
    </script>
    <script src="../js/idle.js"></script>


</body>

</html>