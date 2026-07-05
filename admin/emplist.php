<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// new code
if (isset($_GET['remove_member'])) {
    $ECode = $_GET['ECode'];

    $emr_hospital = base64_decode(base64_decode($_GET['remove_member']));
    $stmt = $db->prepare("UPDATE enrollee SET nhis_no = '', discount_set='' WHERE hospital_no = ? ");
    $stmt->execute([$emr_hospital]);

    $stmtcv = $db->query("SELECT * FROM enrollee WHERE nhis_no='$ECode'");
    $staff_discount = $stmtcv->fetchAll();
    $total_rows = 'Member(s): ' . count($staff_discount);

    $stmt = $db->prepare("UPDATE hremp SET emr_no = '$total_rows' WHERE EmployeeCode = ? ");
    $stmt->execute([$ECode]);
}

if (isset($_POST['add_emr_staff'])) {

    $emr_hospital = $_POST['emr_hospital'];
    $__EmployeeCode_ = $_POST['__EmployeeCode_'];

    $stmt = $db->prepare("UPDATE enrollee SET nhis_no = '$__EmployeeCode_', discount_set=1 WHERE hospital_no = ? ");
    $stmt->execute([$emr_hospital]);

    if ($stmt->rowCount() > 0) {
        $sv_emr = 1;
    } else {
        $sv_emr = 0; // or any other value to indicate no update was made
    }


    $stmtcv = $db->query("SELECT * FROM enrollee WHERE nhis_no='$__EmployeeCode_'");
    $staff_discount = $stmtcv->fetchAll();
    $total_rows = 'Member(s): ' . count($staff_discount);

    $stmt = $db->prepare("UPDATE hremp SET emr_no = '$total_rows' WHERE EmployeeCode = ? ");
    $stmt->execute([$__EmployeeCode_]);
}



if (isset($_POST['do_block'])) {

    $action = $_POST['action'];
    $staff_no = $_POST['ECode'];

    if ($action == 'block') {
        $stmt = $db->prepare("UPDATE admin_users SET status = 0 WHERE EmployeeCode = ? ");
        if ($stmt->execute([$staff_no])) {
            $sv = 1;
        }
    } elseif ($action == 'unblock') {
        $stmt = $db->prepare("UPDATE admin_users SET status = 1 WHERE EmployeeCode = ? ");
        if ($stmt->execute([$staff_no])) {
            $sv = 1;
        }
    }
}
// end new code



// new code
if (isset($_POST["emp_id"]) && isset($_POST['disengage_reason'])) {
    $ECode = $_POST["emp_id"];
    $disengage_reason = $_POST['disengage_reason'];
    $done_by = $_SESSION['fullname'];
    $date = $_POST['date'];
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

                    $one = 1;
                    $zero = 0;
                    $error_status = 2;
                    $error_msg = 'Document is uploaded successfully... User Disengaged';

                    // Update admin_users table
                    $updateAdminUsersSQL = "UPDATE admin_users SET status = :status WHERE EmployeeCode = :employeeCode";
                    $stmt1 = $db->prepare($updateAdminUsersSQL);
                    $stmt1->bindValue(':status', $zero, PDO::PARAM_STR);
                    $stmt1->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
                    $stmt1->execute();

                    // Update hremp table
                    $updateHrempSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
                    $stmt2 = $db->prepare($updateHrempSQL);
                    $stmt2->bindValue(':status', $one, PDO::PARAM_STR);
                    $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
                    $stmt2->execute();

                    // Insert into emp_engagement_history table
                    $historySQL = "INSERT INTO emp_engagement_history (staff_no, engaged, reason, done_by, date, document) 
                                   VALUES (:staffNo, :engaged, :reason, :doneBy, :date, :document)";
                    $stmt3 = $db->prepare($historySQL);
                    $stmt3->bindValue(':staffNo', $ECode, PDO::PARAM_STR);
                    $stmt3->bindValue(':engaged', $zero, PDO::PARAM_INT);
                    $stmt3->bindValue(':reason', $disengage_reason, PDO::PARAM_STR);
                    $stmt3->bindValue(':doneBy', $done_by, PDO::PARAM_STR);
                    $stmt3->bindValue(':date', $date, PDO::PARAM_STR);
                    $stmt3->bindValue(':document', $fileName, PDO::PARAM_STR);
                    $stmt3->execute();

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
        $zero = 0;
        $one = 1;
        $updateAdminUsersSQL = "UPDATE admin_users SET status = :status WHERE EmployeeCode = :employeeCode";
        $stmt1 = $db->prepare($updateAdminUsersSQL);
        $stmt1->bindValue(':status', $zero, PDO::PARAM_STR);
        $stmt1->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
        $stmt1->execute();

        // Update hremp table
        $updateHrempSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
        $stmt2 = $db->prepare($updateHrempSQL);
        $stmt2->bindValue(':status', $one, PDO::PARAM_STR);
        $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
        $stmt2->execute();

        // Insert into emp_engagement_history table
        $historySQL = "INSERT INTO emp_engagement_history (staff_no, engaged, reason, done_by, date) 
                       VALUES (:staffNo, :engaged, :reason, :doneBy, :date)";
        $stmt3 = $db->prepare($historySQL);
        $stmt3->bindValue(':staffNo', $ECode, PDO::PARAM_STR);
        $stmt3->bindValue(':engaged', $zero, PDO::PARAM_INT);
        $stmt3->bindValue(':reason', $disengage_reason, PDO::PARAM_STR);
        $stmt3->bindValue(':doneBy', $done_by, PDO::PARAM_STR);
        $stmt3->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt3->execute();

        $sv = 1;
        //header("location:index.php?hr&ECode=$ECode");				
    }
}

if (isset($_POST["emp_id"]) && isset($_POST['reengage_reason'])) {
    $ECode = $_POST["emp_id"];
    $reengage_reason = $_POST['reengage_reason'];
    $done_by = $_SESSION['fullname'];
    $date = $_POST['date'];

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

                    $one = 1;
                    $zero = 0;
                    $error_status = 2;
                    $error_msg = 'Document is uploaded successfully... User Engaged';

                    // Update admin_users table
                    $updateAdminUsersSQL = "UPDATE admin_users SET status = :status WHERE EmployeeCode = :employeeCode";
                    $stmt1 = $db->prepare($updateAdminUsersSQL);
                    $stmt1->bindValue(':status', $one, PDO::PARAM_STR);
                    $stmt1->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
                    $stmt1->execute();

                    // Update hremp table
                    $updateHrempSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
                    $stmt2 = $db->prepare($updateHrempSQL);
                    $stmt2->bindValue(':status', $zero, PDO::PARAM_STR);
                    $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
                    $stmt2->execute();

                    // Insert into emp_engagement_history table
                    $historySQL = "INSERT INTO emp_engagement_history (staff_no, engaged, reason, done_by, date, document) 
                                   VALUES (:staffNo, :engaged, :reason, :doneBy, :date, :document)";
                    $stmt3 = $db->prepare($historySQL);
                    $stmt3->bindValue(':staffNo', $ECode, PDO::PARAM_STR);
                    $stmt3->bindValue(':engaged', $one, PDO::PARAM_INT);
                    $stmt3->bindValue(':reason', $reengage_reason, PDO::PARAM_STR);
                    $stmt3->bindValue(':doneBy', $done_by, PDO::PARAM_STR);
                    $stmt3->bindValue(':date', $date, PDO::PARAM_STR);
                    $stmt3->bindValue(':document', $fileName, PDO::PARAM_STR);
                    $stmt3->execute();

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


        $zero = 0;
        $one = 1;
        // Update admin_users table
        $updateAdminUsersSQL = "UPDATE admin_users SET status = :status WHERE EmployeeCode = :employeeCode";
        $stmt1 = $db->prepare($updateAdminUsersSQL);
        $stmt1->bindValue(':status', $one, PDO::PARAM_STR);
        $stmt1->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
        $stmt1->execute();

        // Update hremp table
        $updateHrempSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
        $stmt2 = $db->prepare($updateHrempSQL);
        $stmt2->bindValue(':status', $zero, PDO::PARAM_STR);
        $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
        $stmt2->execute();

        // Insert into emp_engagement_history table
        $historySQL = "INSERT INTO emp_engagement_history (staff_no, engaged, reason, done_by, date) 
                  VALUES (:staffNo, :engaged, :reason, :doneBy, :date)";
        $stmt3 = $db->prepare($historySQL);
        $stmt3->bindValue(':staffNo', $ECode, PDO::PARAM_STR);
        $stmt3->bindValue(':engaged', $one, PDO::PARAM_INT);
        $stmt3->bindValue(':reason', $reengage_reason, PDO::PARAM_STR);
        $stmt3->bindValue(':doneBy', $done_by, PDO::PARAM_STR);
        $stmt3->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt3->execute();

        $sv = 1;
    }
    //header("location:index.php?hr&ECode=$ECode");				
}
// new code
if (isset($_POST["emp_id"]) && isset($_POST['query_reason'])) {
    $ECode = $_POST["emp_id"];
    $query_reason = $_POST['query_reason'];
    $done_by = $_SESSION['fullname'];
    $date = $_POST['date'];

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



                    //insert query history
                    $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, file, date) 
                    VALUES (:Ecode, :reason, :done_by, :file, :date)";
                    $stmt = $db->prepare($historySQL);
                    $stmt->bindValue(':Ecode', $ECode, PDO::PARAM_STR);
                    $stmt->bindValue(':reason', $query_reason, PDO::PARAM_STR);
                    $stmt->bindValue(':done_by', $done_by, PDO::PARAM_STR);
                    $stmt->bindValue(':file', $fileName, PDO::PARAM_STR);
                    $stmt->bindValue(':date', $date, PDO::PARAM_STR);
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
        $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, date) 
        VALUES (:Ecode, :reason, :done_by, :date)";
        $stmt = $db->prepare($historySQL);
        $stmt->bindValue(':Ecode', $ECode, PDO::PARAM_STR);
        $stmt->bindValue(':reason', $query_reason, PDO::PARAM_STR);
        $stmt->bindValue(':done_by', $done_by, PDO::PARAM_STR);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        $sv = 1;
    }
    //header("location:index.php?hr&ECode=$ECode");				
}

if (isset($_POST["emp_id"]) && isset($_POST['restore_reason'])) {
    if (null != $_POST['em']) {
        $ECode = $_POST["emp_id"];
    } else {
        $ECode = $_GET["ECode"];
    }
    $restore_reason = $_POST['restore_reason'];
    $done_by = $_SESSION['fullname'];
    $date = $_POST['date'];

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


                    //insert query history
                    $zero = 0;
                    $is_suspension_or_restore = 2;
                    $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, file, date, is_suspension_or_restore) 
                    VALUES (:Ecode, :reason, :done_by, :file, :date, :is_suspension_or_restore)";
                    $stmt1 = $db->prepare($historySQL);
                    $stmt1->bindValue(':Ecode', $ECode, PDO::PARAM_STR);
                    $stmt1->bindValue(':reason', $restore_reason, PDO::PARAM_STR);
                    $stmt1->bindValue(':done_by', $done_by, PDO::PARAM_STR);
                    $stmt1->bindValue(':file', $fileName, PDO::PARAM_STR);
                    $stmt1->bindValue(':date', $date, PDO::PARAM_STR);
                    $stmt1->bindValue(':is_suspension_or_restore', $is_suspension_or_restore, PDO::PARAM_INT);
                    $stmt1->execute();

                    // Update hremp table
                    $updateHrempSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
                    $stmt2 = $db->prepare($updateHrempSQL);
                    $stmt2->bindValue(':status', $zero, PDO::PARAM_STR);
                    $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
                    $stmt2->execute();

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

        $zero = 0;
        $two = 2;
        $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, date, is_suspension_or_restore) 
        VALUES (:Ecode, :reason, :done_by, :date, :is_suspension_or_restore)";
        $stmt1 = $db->prepare($historySQL);
        $stmt1->bindValue(':Ecode', $ECode, PDO::PARAM_STR);
        $stmt1->bindValue(':reason', $restore_reason, PDO::PARAM_STR);
        $stmt1->bindValue(':done_by', $done_by, PDO::PARAM_STR);
        $stmt1->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt1->bindValue(':is_suspension_or_restore', $two, PDO::PARAM_INT);
        $stmt1->execute();

        // Update hremp table
        $updateHrempSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
        $stmt2 = $db->prepare($updateHrempSQL);
        $stmt2->bindValue(':status', $zero, PDO::PARAM_STR);
        $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
        $stmt2->execute();

        $sv = 1;
    }
    //header("location:index.php?hr&ECode=$ECode");				
}

if (isset($_POST['query_staff_action'])) {
    try {

        // Assuming $db is your PDO connection and the necessary variables are defined
        $query_staff_action = $_POST['query_staff_action'];
        $ECode = $_POST['ECode'];
        $fullname = $_SESSION['fullname'];
        $one = 1;
        $Pardon = 'Pardon';
        $Suspension = 'Suspension';
        $two = 2;

        if ($query_staff_action != "") {
            if ($query_staff_action == '0') {
                // Insert into emp_query_history table for Pardon
                $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, is_pardon) 
                               VALUES (:Ecode, :reason, :done_by, :is_pardon)";
                $stmt1 = $db->prepare($historySQL);
                $stmt1->bindValue(':Ecode', $ECode, PDO::PARAM_STR);
                $stmt1->bindValue(':reason', $Pardon, PDO::PARAM_STR);
                $stmt1->bindValue(':done_by', $fullname, PDO::PARAM_STR);
                $stmt1->bindValue(':is_pardon', $one, PDO::PARAM_INT);
                $stmt1->execute();
                $sv = 1;
            } else {
                // Insert into emp_query_history table for Suspension
                $historySQL = "INSERT INTO emp_query_history (Ecode, reason, done_by, is_suspension_or_restore) 
                               VALUES (:Ecode, :reason, :done_by, :is_suspension_or_restore)";
                $stmt1 = $db->prepare($historySQL);
                $stmt1->bindValue(':Ecode', $ECode, PDO::PARAM_STR);
                $stmt1->bindValue(':reason', $Suspension, PDO::PARAM_STR);
                $stmt1->bindValue(':done_by', $fullname, PDO::PARAM_STR);
                $stmt1->bindValue(':is_suspension_or_restore', $one, PDO::PARAM_INT);
                $stmt1->execute();

                // Update hremp table for Suspension
                $updateSQL = "UPDATE hremp SET status = :status WHERE EmployeeCode = :employeeCode";
                $stmt2 = $db->prepare($updateSQL);
                $stmt2->bindValue(':status', $two, PDO::PARAM_STR);
                $stmt2->bindValue(':employeeCode', $ECode, PDO::PARAM_STR);
                $stmt2->execute();
                $sv = 1;
            }
        }
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
    }
}
// end new code

/* Delete  */
if (isset($_POST['delete_dc'])) {
    $del_id = $_POST['del_id'];
    $deleteSQL = "DELETE FROM admin_users WHERE username = :del_id";
    $stmt = $db->prepare($deleteSQL);
    $stmt->execute([':del_id' => $del_id]);
}



?>



<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Employee Masters</h5>
            </div>

            <div class="ibox-content">


                <?php
                if (!isset($_GET["ECode"])) {
                    $zero = 0;
                    if (isset($_GET["Designation"])) {
                        $sql = "SELECT * FROM hremp WHERE Designation = :designation";
                        $stmt_list = $db->prepare($sql);
                        $stmt_list->bindValue(':designation', $_GET["Designation"], PDO::PARAM_STR);
                        $stmt_list->execute();
                    } elseif (isset($_GET["Department"])) {
                        $sql = "SELECT * FROM hremp WHERE Department = :department";
                        $stmt_list = $db->prepare($sql);
                        $stmt_list->bindValue(':department', $_GET["Department"], PDO::PARAM_STR);
                        $stmt_list->execute();
                    } elseif (isset($_GET["Status"])) {
                        if ($_GET["Status"] == "0") {

                            $sql = "SELECT * FROM hremp WHERE status = :status";
                            $stmt_list = $db->prepare($sql);
                            $stmt_list->bindValue(':status', $_GET["Status"], PDO::PARAM_STR);
                            $stmt_list->execute();
                        } elseif ($_GET['Status'] == "1") {

                            $sql = "SELECT * FROM hremp WHERE status = :status";
                            $stmt_list = $db->prepare($sql);
                            $stmt_list->bindValue(':status', $_GET["Status"], PDO::PARAM_STR);
                            $stmt_list->execute();
                        } elseif ($_GET['Status'] == "2") {

                            $sql = "SELECT * FROM hremp WHERE status = :status";
                            $stmt_list = $db->prepare($sql);
                            $stmt_list->bindValue(':status', $_GET["Status"], PDO::PARAM_STR);
                            $stmt_list->execute();
                        }
                    } else {
                        $sql = "SELECT * FROM hremp WHERE status = :status ORDER BY sn";
                        $stmt_list = $db->prepare($sql);
                        $stmt_list->bindValue(':status', $zero, PDO::PARAM_STR);
                        $stmt_list->execute();
                    }

                    if ($stmt_list->rowCount() > 0) { ?>

                        <div class="row">
                            <div class="col-sm-3">
                                <br>
                                <a href="index.php?Add" class="btn btn-primary btn-sm">Add New Staff</a>
                                <a href="index.php?Specialist" class="btn btn-success btn-sm">Assign Specialist to Service</a>

                                <form action="../download_data.php" method="post">

                                    <button type="submit" class="btn btn-warning btn btn-sm" name="download_staff_data" id="Save_patient">Download Staff Data</button>

                                </form>


                            </div>
                            <div class="col-sm-3">
                                <label for="reg_select" class="">Sort by Designation</label>
                                <select name="cat" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="cat" class="form-control" data-required="true">
                                    <option selected="selected" value="<?php echo $cat; ?>"></option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct designation FROM designation ORDER BY designation ASC");
                                    while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo 'index.php?hr&' . 'Designation=' . $row2['designation']; ?>"><?php echo $row2['designation']; ?>
                                        <?php } ?>
                                </select>

                            </div>
                            <div class="col-sm-3">
                                <label for="reg_select" class="">Sort by Department</label>
                                <select name="cat" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="cat" class="form-control" data-required="true">
                                    <option selected="selected" value="<?php echo $cat; ?>"></option>
                                    <?php
                                    $stmt = $db->query("SELECT * FROM department ORDER BY department ASC");

                                    while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo 'index.php?hr&' . 'Department=' . $row2['sn']; ?>"><?php echo $row2['department']; ?>
                                        <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="reg_select" class="">Sort by Status</label>
                                <select name="cat" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="cat" class="form-control" data-required="true">
                                    <option selected value="<?php echo $cat; ?>"></option>
                                    <option value="<?php echo 'index.php?hr&' . 'Status=1' ?>">Disengaged</option>
                                    <option value="<?php echo 'index.php?hr&' . 'Status=0' ?>">Engaged</option>
                                    <option value="<?php echo 'index.php?hr&' . 'Status=2' ?>">Suspended</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Emp.#</th>
                                    <th>Name</th>
                                    <th>Cadre</th>
                                    <th>Designation</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                    <th width="7%">Manage</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $c = 1;
                                while ($row = $stmt_list->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td><?php echo $c; ?></td>
                                        <td><?php echo $row['EmployeeCode']; ?></td>
                                        <td><?php echo $row['FirstName'] . ', ' . $row['LastName'] . ' ' . $row['MiddleName']; ?></td>
                                        <td><?php echo $row['Cadre']; ?></td>
                                        <td><?php echo $row['Designation']; ?></td>
                                        <td><?php echo $row['emr_no']; ?></td>
                                        <td>
                                            <?php
                                            if ($row['status'] == 0) {
                                                echo 'Engaged';
                                            } elseif ($row['status'] == 2) {
                                                echo 'Suspended';
                                            } else {
                                                echo 'Disengaged';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="index.php?hr&ECode=<?php echo $row['EmployeeCode']; ?>" class="btn btn-primary btn-xs">View</a>
                                        </td>

                                    </tr>
                                <?php

                                    $c++;
                                } ?>

                            </tbody>
                        </table>

                    <?php } else { ?>
                        <br>
                        <strong style="font-size:14px">No Data to display. Click Here .. </strong> <a href="index.php?hr"><i class="fa fa-refresh"></i>&nbsp;Refresh</a>
                        <br>
                    <?php }
                } else {

                    $ECode = $_GET["ECode"];

                    $stmt = $db->prepare('SELECT * FROM hremp WHERE EmployeeCode = :EmployeeCode');
                    $stmt->bindParam(':EmployeeCode', $ECode);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);

                    ?>

                        <div class="row">


                            <div class="col-sm-8">
                                <div class="panel panel-default">
                                    <div class="panel-body">


                                        <table id="resp_table" class="table toggle-square " data-filter="#table_search" data-page-size="40">
                                            <thead>
                                                <tr>
                                                    <th>Employee Code</th>
                                                    <th>Joining Date</th>
                                                    <th>Department</th>
                                                    <th>Designation</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['EmployeeCode']; ?></td>
                                                    <td><?php $date_join = $row_rstSelect['JoiningDate'];
                                                        echo $row_rstSelect['JoiningDate']; ?></td>
                                                    <td><?php $dept = $row_rstSelect['Department'];
                                                        $stt = $db->query("SELECT * FROM department where sn='$dept'");
                                                        $rw = $stt->fetch(PDO::FETCH_ASSOC);
                                                        echo $rw['department']; ?></td>
                                                    <td><?php $dgnt = $row_rstSelect['Designation'];
                                                        echo $row_rstSelect['Designation']; ?></td>
                                                </tr>

                                                <tr>
                                                    <th>Qualification</th>
                                                    <th>Total Experience</th>
                                                    <th>Cadre</th>
                                                    <th>Years of service</th>
                                                </tr>
                                                </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['Qualification']; ?></td>
                                                    <td><?php echo $row_rstSelect['TotalExperience']; ?></td>
                                                    <td><?php echo $row_rstSelect['Cadre']; ?></td>
                                                    <td><?php
                                                        echo date('Y-m-d') - $row_rstSelect['JoiningDate'];
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                </tr>
                                                <tr bgcolor="#FFFFCC">
                                                    <th>Title</th>
                                                    <th>First Name</th>
                                                    <th>Middle Name</th>
                                                    <th>LastName</th>

                                                </tr>
                                                </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['Title']; ?></td>
                                                    <td><?php echo $FirstName = $row_rstSelect['FirstName']; ?></td>
                                                    <td><?php echo $row_rstSelect['MiddleName']; ?></td>
                                                    <td><?php echo $LastName = $row_rstSelect['LastName']; ?></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                </tr>
                                                <tr>
                                                    <th>Phone Numbers</th>
                                                    <th>Date of Birth</th>
                                                    <th>Gender</th>
                                                    <th>Marital Status</th>
                                                </tr>
                                                </thead>
                                            </tbody>

                                            <tr>
                                                <td><?php echo $row_rstSelect['phone']; ?></td>
                                                <td><?php echo $row_rstSelect['DateofBirth']; ?></td>
                                                <td><?php echo $row_rstSelect['Gender']; ?></td>
                                                <td><?php echo $row_rstSelect['MaritalStatus']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Religion</th>
                                                <th>Tribe</th>
                                                <th>Gender</th>
                                                <th>B/Group</th>
                                                <th>No of dependants</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['Religion']; ?></td>
                                                    <td><?php echo $row_rstSelect['Tribe']; ?></td>
                                                    <td><?php echo $row_rstSelect['Gender']; ?></td>
                                                    <td><?php echo $blood_grp = $row_rstSelect['blood_grp']; ?></td>
                                                    <td><?php echo $blood_grp = $row_rstSelect['no_of_dependents']; ?></td>
                                                </tr>
                                                <tr>

                                                    <th colspan="2">Present Address</th>
                                                    <th colspan="2">Permanent Address</th>
                                                </tr>
                                                </thead>
                                            </tbody>

                                            <tr>
                                                <td colspan="2"><?php echo $row_rstSelect['PresentAddress']; ?></td>
                                                <td colspan="2"><?php echo $row_rstSelect['PermanentAddress']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>State/LGA</th>
                                                <th>Nationality</th>
                                                <th colspan="2">Email Address</th>

                                            </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['StateLGA']; ?></td>
                                                    <td><?php echo $row_rstSelect['Nationality']; ?></td>
                                                    <td colspan="2"><?php echo $row_rstSelect['EmailAddress']; ?></td>

                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                </tr>

                                                <tr bgcolor="#CCFF99">
                                                    <th>Bank Name</th>
                                                    <th>Branch</th>
                                                    <th>Bank Address</th>
                                                    <th>Account No</th>
                                                </tr>
                                                </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['BankName']; ?></td>
                                                    <td><?php echo $row_rstSelect['Branch']; ?></td>
                                                    <td><?php echo $row_rstSelect['BankAddress']; ?></td>
                                                    <td><?php echo $row_rstSelect['AccountNo']; ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="4"></td>
                                                </tr>
                                                <tr>
                                                    <th>Referee FullName</th>
                                                    <th>Phone</th>
                                                    <th colspan="2"> Address</th>

                                                </tr>
                                                </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['rFullName']; ?></td>
                                                    <td><?php echo $row_rstSelect['rPhone']; ?></td>
                                                    <td colspan="2"><?php echo $row_rstSelect['rAddress']; ?></td>

                                                </tr>
                                                <tr>
                                                    <th>Next of Kin FullName</th>
                                                    <th>Phone</th>
                                                    <th colspan="2"> Address</th>

                                                </tr>
                                                </thead>
                                            <tbody>

                                                <tr>
                                                    <td><?php echo $row_rstSelect['kFullName']; ?></td>
                                                    <td><?php echo $row_rstSelect['kPhone']; ?></td>
                                                    <td colspan="2"><?php echo $row_rstSelect['kAddress']; ?></td>

                                                </tr>


                                            </tbody>
                                        </table>

                                        <div class="form_sep">
                                            <a href="index.php?Add&ECode=<?php echo $EmployeeCode = $row_rstSelect['EmployeeCode']; ?>&Edit" class="btn btn-warning block full-width m-b "> <i class="fa fa-edit"></i> &nbsp; Edit Employee Data </a><br>
                                        </div>
                                        <form action="index.php?hr&ECode=<?= $row_rstSelect['EmployeeCode']; ?>" method="POST">


                                            <div class="form-group">
                                                <label for="">ENTER EMR NUMBER: </label>
                                                <input type="text" name="emr_hospital" id="" class="form-control" maxlength="10">
                                            </div>

                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit" name="add_emr_staff">Add EMR to Staff Profile</button>
                                                <input type="hidden" name="__EmployeeCode_" value="<?php echo $row_rstSelect['EmployeeCode']; ?>" />
                                            </div>



                                        </form>

                                        <?php

                                        if (isset($_POST['add_emr_staff']) && $sv_emr == 0) {
                                            echo '<b style="color:red;">EMR Number Entered Not Available!</b>';
                                        }

                                        $stmt = $db->query("SELECT * FROM enrollee WHERE nhis_no='$EmployeeCode' ORDER BY sn");
                                        if ($stmt->rowCount() > 0) {

                                        ?>

                                            <h3>STAFF MEMBERS ATTACHED TO THIS PROFILE:</h3>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th width="2%">No</th>
                                                        <th width="3%">Hospital No</th>
                                                        <th width="5%">Surname</th>
                                                        <th width="5%">First Name</th>
                                                        <th width="3%">Gender</th>
                                                        <th width="3%">Credit Limit</th>
                                                        <th width="7%">Date</th>
                                                        <th width="7%">Captured By</th>
                                                        <th width="7%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php
                                                    $n = 1;
                                                    while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $n; ?></td>
                                                            <td><?php echo $rwx['hospital_no']; ?></td>
                                                            <td><?php echo $rwx['surname']; ?></td>
                                                            <td><?php echo $rwx['fname']; ?></td>
                                                            <td><?php echo $rwx['gender']; ?></td>
                                                            <td><?php echo number_format($rwx['credit_limit']); ?></td>
                                                            <td><?php echo date("d,M y", strtotime($rwx['date_capture'])); ?></td>
                                                            <td><?php echo $rwx['captured_by']; ?></td>
                                                            <td><a href="index.php?hr&ECode=<?php echo $EmployeeCode; ?>&remove_member=<?php echo base64_encode(base64_encode($rwx['hospital_no'])); ?>" onclick="return confirm('Are you sure you want to remove this member?')">Remove </a> </td>
                                                        </tr>
                                                    <?php $n++;
                                                    } ?>
                                                </tbody>
                                            </table>
                                        <?php } else {
                                            echo '<hr><b>No Principal or Member Attached.</b>';
                                        } ?>


                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="panel panel-default">
                                    <div class="panel-body">

                                        <?php if ($row_rstSelect['status'] == '0') { ?>
                                            <strong>Employment Status:</strong>
                                            <strong style="color:#00C; font-size:20px"> ENGAGED </strong><br>
                                            <!-- new code -->
                                            <a class="disengage_staff_btn" id="<?php echo $row_rstSelect['EmployeeCode']; ?>"><strong>[ Disengage Staff ]</strong></a>&nbsp;
                                            <a class="engagement_history_btn"><strong>[ Engagement History ]</strong></a>



                                            <!-- end new code -->

                                            <div class="modal inmodal fade" id="emp_engage_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                            <h4 class="modal-title" id="">Disengage Employee</h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <form action="" method="POST" enctype="multipart/form-data">
                                                                <div class="form_sep">
                                                                    <label for="" class="req">Enter disengagement reason </label>
                                                                    <textarea name="disengage_reason" id="disengage_reason_field" class="form-control req" required placeholder="Enter your notes..." rows="10"></textarea>
                                                                </div>
                                                                <div class="form_sep">
                                                                    <input type="date" name="date" class="form-control req" required>
                                                                </div>
                                                                <div class="form_sep">
                                                                    <label for="reg_input_no">Document [jpg, png, pdf](optional) </label>
                                                                    <input type="file" name="document_file" id="document_file" class="form-control">
                                                                </div>

                                                                <input type="hidden" class="form-control" name="emp_id" id="emp_id_field" readonly />
                                                                <input type="hidden" maxlength="100" name="title" id="title" value="Disengament_<?php echo date('d-m-Y:H:i:s') ?>" required>
                                                                <br>
                                                                <button class="btn btn-primary">Disengage</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } elseif ($row_rstSelect['status'] == '2') { ?>
                                            <strong style=" color:#F00; font-size:20px"> SUSPENDED </strong><br>
                                            <a class="restore_staff_btn" id="<?php echo $row_rstSelect['EmployeeCode']; ?>"><strong>[ Restore Staff ]</strong></a>&nbsp;
                                            <div class="modal inmodal fade" id="emp_restore_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                            <h4 class="modal-title" id="">Restore Employee</h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <form action="" method="POST" enctype="multipart/form-data">
                                                                <div class="form_sep">
                                                                    <label for="" class="req">Enter Note </label>
                                                                    <textarea name="restore_reason" id="restore_reason_field" class="form-control req" required placeholder="Enter Note..." rows="10">Welcome back</textarea>
                                                                </div>
                                                                <div class="form_sep">
                                                                    <input type="date" name="date" class="form-control req" required>
                                                                </div>
                                                                <br>
                                                                <div>
                                                                    <label for="reg_input_no">Document [jpg, png, pdf](optional) </label>
                                                                    <input type="file" name="document_file" id="document_file" class="form-control">
                                                                </div>
                                                                <input type="hidden" class="form-control" name="emp_id" id="the_emp_id_field" readonly />
                                                                <input type="hidden" maxlength="100" name="title" id="title" value="Restore_<?php echo date('d-m-Y:H:i:s') ?>" required>
                                                                <br>
                                                                <button class="btn btn-primary">Restore</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <strong style=" color:#F00; font-size:20px"> DISENGAGED </strong><br>
                                            <a class="disengage_staff_btn" id="<?php echo $row_rstSelect['EmployeeCode']; ?>"><strong>[ Re-engage Staff ]</strong></a>&nbsp;
                                            <a class="engagement_history_btn"><strong>[ Engagement History ]</strong></a>
                                            <div class="modal inmodal fade" id="emp_engage_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                            <h4 class="modal-title" id="">Re-engage Employee</h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <form action="" method="POST" enctype="multipart/form-data">
                                                                <div class="form_sep">
                                                                    <label for="" class="req">Enter re-engagement note </label>
                                                                    <textarea name="reengage_reason" id="disengage_reason_field" class="form-control req" required placeholder="Enter your notes..." rows="10"></textarea>
                                                                </div>
                                                                <div class="form_sep">
                                                                    <input type="date" name="date" class="form-control req" required>
                                                                </div>
                                                                <br>
                                                                <div>
                                                                    <label for="reg_input_no">Document [jpg, png, pdf](optional) </label>
                                                                    <input type="file" name="document_file" id="document_file" class="form-control">
                                                                </div>
                                                                <input type="hidden" class="form-control" name="emp_id" id="emp_id_field" readonly />
                                                                <input type="hidden" maxlength="100" name="title" id="title" value="Re-engament_<?php echo date('d-m-Y:H:i:s') ?>" required>
                                                                <br>
                                                                <button class="btn btn-primary">Re-engage</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <hr>
                                        <a class="suspension_history_btn"><strong>[ Suspension History ]</strong></a>
                                        <div class="modal inmodal fade" id="emp_suspension_history_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <?php
                                                    $staff_id = $_GET['ECode'];
                                                    $query = $db->prepare("SELECT * FROM emp_query_history 
                                                            INNER JOIN hremp ON emp_query_history.Ecode = hremp.EmployeeCode 
                                                            WHERE emp_query_history.Ecode = ? AND emp_query_history.is_suspension_or_restore != 0");
                                                    if ($query->execute([$staff_id])) {
                                                        $history = $query->fetchAll();
                                                    }
                                                    ?>
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                        <h4 class="modal-title" id="">Suspension History</h4>
                                                    </div>
                                                    <?php if (!empty($history)) : ?>
                                                        <div class="modal-body" id="engagement_history_body">
                                                            <h3>Suspension History for <?php echo $history[0]['FirstName'] . " " . $history[0]['LastName']; ?></h3>
                                                            <table class=" table table-striped dataTables-example">
                                                                <thead>
                                                                    <th>Date</th>
                                                                    <th>Reason</th>
                                                                    <th>Document(if any)</th>
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
                                                                                <?php echo date('d M Y', strtotime($entry['date'])); ?>
                                                                            </td>
                                                                            <td>

                                                                            </td>
                                                                            <td>
                                                                                <p><?php echo $entry['reason'] ?></p>
                                                                                <?php if ($entry['is_suspension_or_restore'] == 1) : ?>
                                                                                    <span class="badge badge-success">Suspended by <?php echo $entry['done_by'] ?></span>
                                                                                <?php elseif ($entry['is_suspension_or_restore'] == 2) : ?>
                                                                                    <span class="badge badge-danger">Restored by <?php echo $entry['done_by'] ?></span>
                                                                                <?php endif ?>
                                                                            </td>
                                                                            <td>
                                                                                <?php if (!empty($entry['document'])) : ?>
                                                                                    <a href="<?php echo $target_dir . $entry['document'] ?>">
                                                                                        <strong>
                                                                                            <?php echo $entry['document'] ?>
                                                                                        </strong>
                                                                                    </a>
                                                                                <?php else : ?>
                                                                                    N/A
                                                                                <?php endif ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $i++; ?>
                                                                    <?php endforeach ?>
                                                                </tbody>
                                                            </table>
                                                            <a href="javascript:Clickheretoprint_history()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                                                        </div>
                                                    <?php else : ?>
                                                        <p>No data to show</p>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <a class="query_staff_btn" id="<?php echo $row_rstSelect['EmployeeCode']; ?>"><strong>[ Query Staff ]</strong></a>&nbsp;
                                        <a class="query_history_btn"><strong>[ Query History ]</strong></a>
                                        <div class="modal inmodal fade" id="emp_query_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                        <h4 class="modal-title" id="">Query Employee</h4>
                                                    </div>

                                                    <div class="modal-body">
                                                        <form action="" method="POST" enctype="multipart/form-data">
                                                            <div class="form_sep">
                                                                <label for="" class="req">Enter Query Note/Reason </label>
                                                                <textarea name="query_reason" id="query_reason_field" class="form-control req" required placeholder="Enter your notes..." rows="10"></textarea>
                                                            </div>
                                                            <div class="form_sep">
                                                                <input type="date" name="date" class="form-control req" required>
                                                            </div>
                                                            <div class="form_sep">
                                                                <label for="reg_input_no">Document [jpg, png, pdf] (optional) </label>
                                                                <input type="file" name="document_file" id="document_file" class="form-control">
                                                            </div>

                                                            <input type="hidden" class="form-control" name="emp_id" id="the_emp_id_field" readonly />
                                                            <input type="hidden" maxlength="100" name="title" id="title" value="Query_<?php echo date('d-m-Y:H:i:s') ?>" required>
                                                            <br>
                                                            <button class="btn btn-primary">Save</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal inmodal fade" id="emp_query_history_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <?php
                                                    $staff_id = $_GET['ECode'];
                                                    $query = $db->prepare("SELECT * FROM emp_query_history  INNER JOIN hremp ON emp_query_history.Ecode = hremp.EmployeeCode WHERE emp_query_history.Ecode = ?");
                                                    if ($query->execute([$staff_id])) {
                                                        $history = $query->fetchAll();
                                                    }
                                                    ?>
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                        <h4 class="modal-title" id="">Query History</h4>
                                                    </div>

                                                    <div class="modal-body" id="query_history_body">
                                                        <?php if (!empty($history)) : ?>
                                                            <h3>Query History for <?php echo $history[0]['FirstName'] . " " . $history[0]['LastName']; ?></h3>
                                                            <table class=" table table-striped dataTables-example">
                                                                <thead>
                                                                    <th>Date</th>
                                                                    <th>Note</th>
                                                                    <th>Document(if any)</th>
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
                                                                                <small><?php echo date('dS M y', strtotime($entry['date'])); ?></small>
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
                                                                        </tr>
                                                                        <?php $i++; ?>
                                                                    <?php endforeach ?>
                                                                </tbody>
                                                            </table>
                                                            <a href="javascript:Clickheretoprint_history_query()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                                                        <?php else : ?>
                                                            No data to show
                                                        <?php endif ?>
                                                    </div>
                                                    <div class="modal-footer" style="display:flex; align-items: left;">
                                                        <form action="" method="POST" class="form-inline">
                                                            <div class="form_sep">
                                                                <label for="">Select action</label>
                                                                <select name="query_staff_action" id="" class="form-control">
                                                                    <option value="" selected>--select action--</option>
                                                                    <option value="0">Pardon</option>
                                                                    <option value="1">Suspend</option>
                                                                </select>
                                                                <input type="hidden" value="<?php echo $history[0]['EmployeeCode'] ?>" name="ECode">
                                                                <button type="submit" class="btn btn-primary">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal inmodal fade" id="emp_engagement_history_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <?php
                                                    $staff_id = $_GET['ECode'];
                                                    $query = $db->prepare("SELECT * FROM emp_engagement_history INNER JOIN hremp ON emp_engagement_history.staff_no = hremp.EmployeeCode WHERE emp_engagement_history.staff_no = ?");
                                                    if ($query->execute([$staff_id])) {
                                                        $history = $query->fetchAll();
                                                    }
                                                    ?>
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                        <h4 class="modal-title" id="">Engagement History</h4>
                                                    </div>

                                                    <div class="modal-body" id="engagement_history_body">
                                                        <?php if (!empty($history)) : ?>
                                                            <h3>Engagement History for <?php echo $history[0]['FirstName'] . " " . $history[0]['LastName']; ?></h3>
                                                            <table class="table table-striped dataTables-example">
                                                                <thead>
                                                                    <th>#</th>
                                                                    <th>Action</th>
                                                                    <th>Reason</th>
                                                                    <th>Done by</th>
                                                                    <th>File(if any)</th>
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
                                                                                <?php echo $i; ?>

                                                                            </td>
                                                                            <td>
                                                                                <?php if ($entry['engaged'] == 1) : ?>
                                                                                    <span class="badge badge-success">Engaged</span>
                                                                                <?php else : ?>
                                                                                    <span class="badge badge-danger">Disengaged</span>
                                                                                <?php endif ?>
                                                                            </td>
                                                                            <td>

                                                                                <?php echo $entry['reason'] ?>
                                                                                <br>
                                                                                <small><?php echo date('d-m-Y', strtotime($entry['date'])); ?></small>
                                                                            </td>
                                                                            <td>
                                                                                <?php echo $entry['done_by'] ?>
                                                                            </td>
                                                                            <td>
                                                                                <?php if (!empty($entry['document'])) : ?>
                                                                                    <a href="<?php echo $target_dir . $entry['document'] ?>">
                                                                                        <strong>
                                                                                            <?php echo $entry['document'] ?>
                                                                                        </strong>
                                                                                    </a>
                                                                                <?php else : ?>
                                                                                    N/A
                                                                                <?php endif ?>

                                                                            </td>
                                                                        </tr>
                                                                        <?php $i++; ?>
                                                                    <?php endforeach ?>
                                                                </tbody>
                                                            </table>
                                                            <a href="javascript:Clickheretoprint_history()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                                                        <?php else : ?>
                                                            No data to show
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- new code -->
                                        <hr>
                                        <a class="leave_history_btn"><strong>[ Leave History ]</strong></a>
                                        <hr>
                                        <div class="form_sep">
                                            <img src="<?php echo staff_p . 'port_' . $row_rstSelect['EmployeeCode'] . '.' . 'jpg'; ?>" alt="No Passport" width="150" height="150" class="img-thumbnail user_avatar">
                                        </div>

                                        <br><br><br>
                                        <strong style="font-size:14px; color:#006">[ Activity ]</strong>
                                        <hr>


                                        <table align="center">
                                            <tr>
                                                <td style="padding:10px;">
                                                    <input type="button" name="emr_idfront" value="ID Card /Front" data-target="#myModal5" id="<?php echo $row_rstSelect['EmployeeCode'] . '/front'; ?>" class="btn btn-primary btn-xs emr_id_front" />
                                                </td>
                                                <td>
                                                    <input type="button" name="emr_idback" value="ID Card /Back" data-target="#myModal5" id="<?php echo $row_rstSelect['EmployeeCode'] . '/back'; ?>" class="btn btn-primary btn-xs emr_id_back" />
                                                </td>
                                            </tr>
                                        </table>

                                        <div class="form_sep"></div>




                                        <div class="form_sep">
                                            <a href="index.php?sal=<?php echo $row_rstSelect["EmployeeCode"]; ?>" class="btn btn-success block full-width m-b">Salary Settings</a>
                                        </div>


                                        <!-- new code -->
                                        <hr>
                                        <strong style="font-size:14px; color:#006">[ Other Documents]</strong> <br>
                                        <hr>
                                        <?php
                                        $target_dir = "../uploads/staff/";
                                        $staff_id = $_GET['ECode'];
                                        if (isset($_GET['del_file'])) {
                                            $file_to_delete = explode("_", $_GET['del_file']);
                                            $file_to_delete = $file_to_delete[1];
                                            $del = $db->prepare("UPDATE staff_other_docs SET status = ? WHERE doc_sn = ?");
                                            if ($del->execute([0, $file_to_delete])) {
                                                $sv = 1;
                                            }
                                        }
                                        $query = $db->prepare("SELECT * FROM staff_other_docs WHERE staff_no = ? AND status = ?");
                                        if ($query->execute([$staff_id, 1])) {
                                            $files = $query->fetchAll();

                                        ?>
                                            <table class="table table-striped dataTables-example">
                                                <thead>
                                                    <th>Document name</th>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($files as $file) { ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?php echo $target_dir . $file['document_file'] ?>">
                                                                    <strong>
                                                                        <?php echo $file['document_title'] ?>
                                                                    </strong>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        <?php } ?>

                                        <div class="form_sep">
                                            <a href="index.php?hr" class="btn btn-danger btn-sm">Close</a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>


                <?php }
                } ?>



            </div>
        </div>
    </div>
</div>

<?php

$stmt_users = $db->prepare('SELECT * FROM admin_users WHERE EmployeeCode = :EmployeeCode');
$stmt_users->bindParam(':EmployeeCode', $_GET['ECode']);
$stmt_users->execute();

if ($stmt_users->rowCount() == 0) {
    $data_mode = 0;
} else {
    $row_rstSelect = $stmt_users->fetch(PDO::FETCH_ASSOC);
    $data_mode = 1;
}
?>





<div class="modal inmodal fade" id="emr_front_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>

            <div class="modal-body" id="emr_id_body">


                <div id="content_front" style="font-size: 18px;">

                    <table width="200" border="0">
                        <tr>
                            <td width="200" height="20" align="center">
                                <img src="../img/logo.png" width="200" height="90">
                            </td>
                        </tr>

                        <tr>
                            <td width="200" height="20" align="center">
                                <span style="font-family: Tahoma, Geneva, sans-serif; font-size:17px;"><b>STAFF ID NO: <?php echo $_GET['ECode']; ?></b></span>
                            </td>
                        </tr>

                        <tr>
                            <td width="160" height="20">
                                <strong style="font-size:14px; color:#900">Blood Group: <?php echo $blood_grp; ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td width="160" height="20">&nbsp;

                            </td>
                        </tr>

                        <tr>
                            <td width="160" height="20" align="center">
                                <img src="<?php echo staff_p . 'port_' . $row_rstSelect['EmployeeCode'] . '.' . 'jpg'; ?>" alt="No Passport" width="80" height="100" class="img-thumbnail user_avatar">
                            </td>
                        </tr>

                        <tr>
                            <td width="160" height="20" align="center">
                                <strong style="font-family:Verdana, Geneva, sans-serif; font-size:16px">
                                    <?php echo $FirstName . ' ' . $LastName; ?><br>
                                    <?php echo $dgnt; ?></strong>
                            </td>
                        </tr>

                        <tr>
                            <td width="160" height="20" align="center">
                                <img src="<?php echo staff_p . 'sign_' . $row_rstSelect['EmployeeCode'] . '.' . 'jpg'; ?>" alt="No Passport" width="101" height="25" class="img-thumbnail user_avatar">

                            </td>
                        </tr>
                    </table>


                </div>

                <div class="form_sep" align="right" style="font-family:Arial, Helvetica, sans-serif">
                    <div class="pull-right" style="margin-right:100px;">
                        <a href="javascript:Clickheretoprint_front()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="emr_back_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>

            <div class="modal-body" id="emr_id_body">

                <div id="content_back">

                    <table width="170" border="0">
                        <tr>
                            <td width="170">
                                <div style="font-size:18px; font-family:Georgia, 'Times New Roman', Times, serif" align="center">
                                    This Card is the Property of<br>
                                    <b></b>
                                    <br>



                                    <br><br>
                                    email:
                                    <br>

                                    <b>Tel: </b>
                                    <br />
                                    If found, please return to the above address or to the nearest Police Station.
                                    <br><br>

                                    <br /><img src="../img/sign.jpg" alt="" width="101" height="50" />
                                    <br>
                                    <strong>Medical Director's Signature</strong>

                                </div>
                            </td>
                        </tr>
                    </table>



                </div>

                <div class="form_sep" align="right" style="font-family:Arial, Helvetica, sans-serif">
                    <div class="pull-right" style="margin-right:100px;">
                        <a href="javascript:Clickheretoprint_back()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>


<script language="javascript">
    function Clickheretoprint_front() {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById("content_front").innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.open();
        docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
        docprint.document.write(content_vlue);
        docprint.document.close();
        docprint.focus();
    }


    function Clickheretoprint_back() {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById("content_back").innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.open();
        docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
        docprint.document.write(content_vlue);
        docprint.document.close();
        docprint.focus();
    }

    // new code
    function Clickheretoprint_history() {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById("engagement_history_body").innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.open();
        docprint.document.write('</head><body onLoad="self.print()" style="width: 100%; font-size: 16px; font-family: arial;">');
        docprint.document.write(content_vlue);
        docprint.document.close();
        docprint.focus();
    }

    function Clickheretoprint_history_query() {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById("query_history_body").innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.open();
        docprint.document.write('<title>Query history</title></head><body onLoad="self.print()" style="width: 100%; font-size: 16px; font-family: arial;">');
        docprint.document.write(content_vlue);
        docprint.document.close();
        docprint.focus();
    }

    function Clickheretoprint_history_leave() {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById("leave_history_body").innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.open();
        docprint.document.write('<title>Leave history</title></head><body onLoad="self.print()" style="width: 100%; font-size: 16px; font-family: arial;">');
        docprint.document.write(content_vlue);
        docprint.document.close();
        docprint.focus();
    }
    // end new code
</script>