<?php
include("../inc/session.php");
include("../Connections/Conn.php");

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


$setdate = date("Y-m-d H:i:s");


if (isset($_GET["del"])) {
    $del = $_GET['del'];

    // Prepare the SQL delete statement with a placeholder for `sn`
    $update = $db->prepare("DELETE FROM hrlvapply WHERE sn = :sn");

    // Bind the `:sn` parameter to the `$del` variable, treating it as a string
    $update->bindParam(':sn', $del, PDO::PARAM_STR);

    // Execute the prepared statement
    $update->execute();
}



if (isset($_POST["add_leave"])) {
    //print_r($_POST);

    $leave_type = $_POST['leave_type'];
    $part = explode("__", $leave_type);

    $req_days = $part[1];
    $leave_type = $part[0];
    $apply_type = $part[2];
    $approvers_list = $part[3];
    ///exit;

    $EmployeeCode = $_POST['EmployeeCode'];
    // // 
    // $part = explode("__", $EmployeeCode);
    // $EmployeeCode = $part[0];
    // // end 
    $t_days = $_POST['t_days'];

    /// check if 
    $error = 0;
    if ($t_days <= $req_days) {

        if ($apply_type == 'Yearly') {
            $yr = date("Y");
            $stmt = $db->query("Select * from hrlvapply where year='$yr' and type_leave='$leave_type' and status='finish' and ECode='$EmployeeCode'");
            // 
            $stmt2 = $db->query("Select * from hrlvapply where year='$yr' and type_leave='$leave_type' and status='pending' and ECode='$EmployeeCode'");
            // end 
            if ($stmt->rowCount() == 0) {
                $error = '0';
            } else {
                $error = '1';
            }
            // 
            if ($stmt2->rowCount() == 0) {
                $error = 0;
            } else {
                $error = 2;
            }
            // end 
        } elseif ($apply_type == 'Monthly') {
            $month = date("m");
            $yr = date("Y");

            $stmt = $db->query("Select * from hrlvapply where year='$yr' and month='$month' and type_leave='$leave_type' and status='finish' and ECode='$EmployeeCode'");
            // 
            $stmt2 = $db->query("Select * from hrlvapply where year='$yr' and month='$month' and type_leave='$leave_type' and status='pending' and ECode='$EmployeeCode'");
            // end 
            if ($stmt->rowCount() == 0) {
                $error = '0';
            } else {
                $error = '1';
            }
            // 
            if ($stmt2->rowCount() == 0) {
                $error = 0;
            } else {
                $error = 2;
            }
            // end 
        } elseif ($apply_type == 'Any Time') {
            //$sub=Add_leave($leave_type);
            $error = '0';
        }


        //// TOTAL DAYS REQUIRED
    } else {
        $err_title = '<strong>Leave Days specified is more than required days</strong>';
    }

    if ($error == 1) {
        $err_title = 'Duplicate Leave Request Found... This request has been approved or request before.';
    } elseif ($error == 2) { //
        $err_title = 'Pending Leave Request Exist!';
        // end 
    } else {
        Add_leave($leave_type, $approvers_list);
    }
}


function Add_leave($leave_type, $approvers_list)
{
    global $db;

    $setdate = date("Y-m-d H:i:s");
    $m = date("m");
    $yr = date("Y");
    $Ecode_name = $_SESSION['fullname'];
    $EmployeeCode = $_POST['EmployeeCode'];
    $backup_staff = $_POST["backup_staff"];
    // Prepare the SQL statement with placeholders for each value
    $update = $db->prepare("INSERT INTO hrlvapply 
    (ECode, Name, dept_id, approvers_list, date_apply, starting_date, ending, month, year, reason, type_leave, days, status, backup_staff) 
    VALUES (:ECode, :Name, :dept_id, :approvers_list, :date_apply, :starting_date, :ending, :month, :year, :reason, :type_leave, :days, :status, :backup_staff)");

    // Bind the parameters with their corresponding values
    $update->bindParam(':ECode', $EmployeeCode, PDO::PARAM_STR);
    $update->bindParam(':Name', $Ecode_name, PDO::PARAM_STR);
    $update->bindParam(':dept_id', $_SESSION['dept_id'], PDO::PARAM_STR);
    $update->bindParam(':approvers_list', $approvers_list, PDO::PARAM_STR);
    $update->bindParam(':date_apply', $setdate, PDO::PARAM_STR);
    $update->bindParam(':starting_date', $_POST["start"], PDO::PARAM_STR);
    $update->bindParam(':ending', date('y-m-d', strtotime($_POST["start"] . '+' . $_POST["t_days"] . ' days')), PDO::PARAM_STR);
    $update->bindParam(':month', $m, PDO::PARAM_STR);
    $update->bindParam(':year', $yr, PDO::PARAM_STR);
    $update->bindParam(':reason', $_POST["reason"], PDO::PARAM_STR);
    $update->bindParam(':type_leave', $leave_type, PDO::PARAM_STR);
    $update->bindParam(':days', $_POST["t_days"], PDO::PARAM_STR);
    $update->bindParam(':status', $status = 'pending', PDO::PARAM_STR);
    $update->bindParam(':backup_staff', $backup_staff, PDO::PARAM_STR);

    // Execute the prepared statement
    $update->execute();

    $approvers_list_arr = explode(',', $approvers_list);
    $approvers_list_arr = array_filter($approvers_list_arr);

    foreach ($approvers_list_arr as $app) {
        //notifiy the aprovers
        $l = global_notify_(
            $db,
            'designation',
            '%' . $app . '%',
            'Leave Request From: ' . $Ecode_name,
            'There is a new leave request, you are getting this message because you are one of the parties eligible for review and approval',
            'SYSTEM'
        );
    }


    //if HOD is part of the approvers, get the rights of the person, and use that to find the unit head, and then notify them
    if (in_array('HOD', $approvers_list_arr)) {
        $get_user = $db->prepare("SELECT * FROM admin_users WHERE EmployeeCode = ?");
        $get_user->execute([$EmployeeCode]);
        $get_user = $get_user->fetch(PDO::FETCH_ASSOC);

        $user_rights = $get_user['rights'];

        //get his unit heads, based on the rights
        $get_heads = $db->prepare("SELECT * FROM admin_users WHERE rights = ? AND unit_head = 1");
        $get_heads->execute([$user_rights]);
        $get_heads = $get_heads->fetchAll(PDO::FETCH_ASSOC);



        foreach ($get_heads as $head) {
            global_notify_(
                $db,
                'user',
                '%' . $head['username'],
                'Leave Request From: ' . $Ecode_name,
                'There is a new leave request, you are getting this message because you are the Unit Head, and thus eligible for review and approval',
                'SYSTEM'
            );
        }
    }
    // 
    global $sv;
    return $sv = 1;
    // end 


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

                    <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Appy for Leave</h5>
                            </div>

                            <div class="ibox-content">

                                <?php if ($err_title != '') { ?><strong style="color:#F00"><?php echo $err_title;  ?></strong>
                                    <hr><?php } ?>

                                <form action="staff_leave_appl.php" method="POST">

                                    <input type="hidden" id="EmployeeCode" name="EmployeeCode" value="<?php echo $_SESSION['EmployeeCode']; ?>">
                                    <input type="hidden" id="fullname" name="fullname" value="<?php echo $_SESSION['fullname']; ?>">


                                    <div class="form_sep">
                                        <label for="reg_select" class="req">Leave Type</label>
                                        <select name="leave_type" id="leave_type" class="form-control" required onchange="do_leave_calculation()">
                                            <option selected="selected" value="">Select ...</option>

                                            <?php
                                            $stmt_lv = $db->query("SELECT * FROM hrlv");
                                            while ($rwx = $stmt_lv->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <option value="<?php echo $rwx["leave_type"] . '__' . $rwx["days"] . '__' . $rwx["apply_type"] . '__' . $rwx["approver1"] . ',' . $rwx["approver2"]; ?>"><?php echo $rwx["leave_type"] . '/ Days: ' . $rwx["days"]; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form_sep">
                                        <label for="reg_textarea_message" class="req">Reason for Application</label>
                                        <textarea name="reason" id="reason" cols="30" rows="14" class="form-control" required></textarea>
                                    </div>

                                    <div class="form_sep" id="">
                                        <label for="reg_input_no" class="req">
                                            Who is filling in <br>
                                            <small><i>Name of the staff that is filling in for the applicant</i></small>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user">
                                                </i></span><input type="text" class="form-control" name="backup_staff" id="backup_staff" required>
                                        </div>
                                    </div>


                                    <div class="form_sep" id="data_1">
                                        <label for="reg_input_no" class="req"> Starting Date</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="date" class="form-control" name="start" id="start_date" onchange="do_leave_calculation()" required>
                                        </div>
                                    </div>



                                    <div class="form_sep">
                                        <label for="reg_input_no" class="req">Total Days</label>&nbsp; &nbsp;<span id="days-left"></span>
                                        <input type="number" id="t_days" name="t_days" min="1" class="form-control" required style="display: none;">
                                    </div>

                                    <div class="form_sep">
                                        <button class="btn btn-info btn btn-sm" type="submit" name="add_leave" id="add_leave" disabled>Submit Request</button>
                                    </div>
                                    <hr>
                                    <div style="color: red;" id="message"></div>

                                </form>
                                <script>
                                    function do_leave_calculation() {

                                        var ECode = document.getElementById('EmployeeCode').value;
                                        ECode = ECode.split('__');
                                        ECode = ECode[0];
                                        var leave_type = document.getElementById('leave_type').value;
                                        leave_type = leave_type.split('__');
                                        var leave_type_days = leave_type[1];
                                        var leave_type = leave_type[0];
                                        //console.log(leave_type_days);
                                        if (ECode != '') {
                                            // console.log(ECode);
                                            $('#leave_type').show();
                                        } else {
                                            $('#leave_type').hide();
                                        }


                                        if (leave_type != '') {
                                            // start_date = $('#start_date').val();
                                            var start_date = document.getElementById('start_date').value;

                                            if (start_date != '') {
                                                d = new Date(start_date);
                                                var year = d.getFullYear();
                                                ///alert(year);
                                                $.ajax({
                                                    url: "fetch_leave_remarks.php",
                                                    method: "POST",
                                                    data: {
                                                        ECode: ECode,
                                                        year: year,
                                                        leave_type: leave_type,
                                                        start_date: start_date
                                                    },
                                                    success: function(data) {
                                                        ///	alert(data);

                                                        var json = JSON.parse(data);
                                                        //  data = JSON.parse(data);
                                                        if (data.length) {
                                                            //console.log(data);
                                                            $('#t_days').show();
                                                            $('#message').text(json["message"]);

                                                            if (json["status"] == 0) {
                                                                $('#add_leave').attr('disabled', false);
                                                                $('#days-left').text(json["days"] + " Days available");

                                                            } else {
                                                                $('#t_days').hide();
                                                                $('#add_leave').attr('disabled', true);

                                                            }
                                                            $('#t_days').attr('max', (json["days"]));



                                                        } else {
                                                            $('#t_days').show();
                                                            if (json["status"] == 0) {
                                                                $('#add_leave').attr('disabled', false);
                                                                $('#days-left').text(json["days"] + " Days available");
                                                            } else {
                                                                $('#t_days').hide();
                                                                $('#add_leave').attr('disabled', true);

                                                            }
                                                            $('#t_days').attr('max', json["days"]);

                                                        }
                                                    }
                                                });
                                            }

                                        } else {
                                            $('#t_days').hide();
                                            $('#add_leave').attr('disabled', true);
                                        }
                                    }
                                </script>

                            </div>

                        </div>
                    </div>




                    <div class="col-lg-8">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Report Panel </h5>
                            </div>

                            <div class="ibox-content">

                                <?php

                                $stmt = $db->query("SELECT * FROM hrlvapply where ECode='$ECode_logged' order by sn");
                                if ($stmt->rowCount() > 0) { ?>

                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>

                                                <th>Dates</th>
                                                <th>Details</th>
                                                <th>Resume Date</th>
                                                <th>Approvers:</th>
                                                <th>Remarks</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $n = 1;
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                                                <tr>

                                                    <td><?php echo '<strong>Apply Date: </strong><br>' . date("d M,y", strtotime($row['date_apply'])); ?><br>
                                                        <?php echo '<strong>Start Date: </strong><br>' .  date("d M,y", strtotime($row['starting_date'])); ?></td>
                                                    <td><?php echo '<strong>Reason:</strong><br>' . $row['reason'] . '<br> <strong>Leave Type:</strong><br>' . $row['type_leave'] . '/ days:' . $row['days']; ?></td>
                                                    <td><?php $d = $row['days'];
                                                        echo date("d,M Y", strtotime($row['starting_date'] . " +$d day")); ?></td>
                                                    <td><?php if ($row['approver1'] != '') {
                                                            echo '<strong>Approver I: </strong><br>' . $row['approver1'];
                                                        } ?>
                                                        <?php if ($row['approver2'] != '') {
                                                            echo '<br><strong>Approver II: </strong><br>' . $row['approver2'];
                                                        } ?></td>
                                                    <td>
                                                        <!--  -->
                                                        <?php
                                                        echo $row['remarks'];
                                                        echo "<hr>";
                                                        echo $row['remarks2'];
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($row['status'] == 'Approve') {
                                                            $d = $row['days'];
                                                            if (strtotime($row['starting_date']) < date('U') && strtotime($row['starting_date'] . " +$d day") > date('U')) {
                                                                echo "Approved <br> [Ongoing]";
                                                            } else {
                                                                echo "Approved";
                                                            }
                                                        } elseif ($row['status'] == 'reject') {
                                                            echo "Rejected";
                                                        } elseif ($row['status'] == 'finish' or (strtotime($row['starting_date']) < date('U') && (strtotime($row['starting_date'] . " +$d day") < date('U')))) {
                                                            echo "Finished";
                                                        } else {
                                                        }
                                                        // echo $row['status'];
                                                        ?> <?php if ($row['status'] == 'pending') { ?>
                                                            <a href="staff_leave_appl.php?del=<?php echo $row['sn']; ?>"> [Delete]</a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>

                                        </tbody>
                                    </table>
                                <?php } else {
                                    echo '<strong>No Leave Records Found </strong>';
                                }

                                ?>

                            </div>

                        </div>
                    </div>
                    <div class="col-sm-8">
                        <?php
                        $leave_types = $db->query("SELECT * FROM hrlv");
                        $year = date('Y');
                        ?>
                        <table class="table table-striped dataTables-example table-responsive">
                            <caption>
                                <h3>Leave summary for <?php echo $year; ?></h3>
                            </caption>
                            <thead>
                                <th>Dates</th>
                                <th>Details</th>
                                <th>Resume Date</th>
                                <th>Days taken</th>
                                <th>Days remaining</th>
                            </thead>
                            <tbody>
                                <?php while ($leave_type = $leave_types->fetch()) : ?>
                                    <?php
                                    $leave = $leave_type['leave_type'];
                                    $ECode = $_SESSION['EmployeeCode'];
                                    $stmt = $db->prepare("SELECT p.*, p.days as days_taken,l.days as days from hrlvapply as p inner join hrlv as l on p.type_leave = l.leave_type 
                                            where p.year=? and (p.status='finish' or p.status = 'Approve') and p.ECode=? and l.leave_type =?");
                                    $stmt->execute([$year, $ECode, $leave]);
                                    $res = $stmt->fetchAll();
                                    $i = 1;
                                    ?>
                                    <?php foreach ($res as $entry) : ?>
                                        <tr>
                                            <td>
                                                <?php echo '<strong>Apply Date: </strong><br>' . date("d M,y", strtotime($entry['date_apply'])); ?><br>
                                                <?php echo '<strong>Start Date: </strong><br>' .  date("d M,y", strtotime($entry['starting_date'])); ?>
                                            </td>
                                            <td>
                                                <?php echo '<strong>Reason:</strong><br>' . $entry['reason'] . '<br> <strong>Leave Type:</strong><br>' . $entry['type_leave'] . '/ days:' . $entry['days']; ?>
                                            </td>
                                            <td>
                                                <?php $d = $entry['days_taken'];
                                                echo date("d,M Y", strtotime($entry['starting_date'] . " +$d day")); ?>
                                            </td>
                                            <td>
                                                <?php echo $entry['days_taken']; ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($i == 1) {
                                                    $rest = $entry['days'] - $entry['days_taken'];
                                                    echo $rest;
                                                } else {
                                                    $rest = $rest - $entry[16];
                                                    echo $rest;
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php $i++; ?>
                                    <?php endforeach; ?>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </div>



            </div>
            <?php include("../inc/footer.php"); ?>

        </div>
    </div>
    <?php include('../modal_lock.php'); ?>
    <?php ///include("../inc/footer_scripts.php"); 
    ?>

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
        $('.dataTables-example').dataTable({
            responsive: true,
            "dom": 'T<"clear">lfrtip',
            "tableTools": {
                "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
            }
        });
    </script>

    <script src="../js/idle.js"></script>
</body>

</html>