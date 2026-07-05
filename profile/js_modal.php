

<?php
//new code
// alert for new query
$staff_id = $_SESSION['EmployeeCode'];
$query = $db->prepare("SELECT * FROM emp_query_history WHERE Ecode = ? 
			AND is_suspension_or_restore = 0 AND is_reply = 0 AND is_pardon = 0 AND has_reply = 0");
if ($query->execute([$staff_id])) {
    $history = $query->fetchAll();
    //print_r($history);
    if ($query->rowCount() > 0) {
        echo "<script>
					$('#query_notice_modal').modal('show');
				</script>";
    }
}
?>

<div class="modal inmodal fade" id="query_notice_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Query Notice</h4>
            </div>

            <div class="modal-body">
                <p>
                    Hello, <br>
                    You have unanswerd query notice(s) from HR, click <a href="../profile/query_history.php">Here</a> to view and respond to them
                </p>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="emp_leave_history_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?php
            $staff_id = $_GET['ECode'];
            $query = $db->prepare("SELECT hrlvapply.*, hremp.FirstName, hremp.LastName FROM hrlvapply  INNER JOIN hremp ON hrlvapply.Ecode = hremp.EmployeeCode WHERE hrlvapply.Ecode = ?");
            $query2 = $db->prepare("SELECT hremp.FirstName, hremp.LastName FROM hremp WHERE hremp.EmployeeCode = ?");
            $query->execute([$staff_id]);
            if ($query2->execute([$staff_id])) {
                $h = $query2->fetchAll();
            }
            ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Leave History</h4>
            </div>

            <div class="modal-body" id="leave_history_body">
                <?php if ($query->rowCount() > 0) : ?>
                    <h3>Leave History for <?php echo $h[0]['FirstName'] . " " . $h[0]['LastName']; ?></h3>
                    <table class=" table table-striped dataTables-example">
                        <thead>
                            <tr>
                                <th>Date/Applied</th>
                                <th>Date/Starts</th>
                                <th>Leave Type</th>
                                <th>Reason</th>
                                <th>Resumption Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $n = 1;
                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) { ?>


                                <tr>
                                    <td><?php echo date('d M Y', strtotime($row['date_apply'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['starting_date'])); ?></td>
                                    <td><?php echo $row['type_leave']; ?></td>
                                    <td><?php echo $row['reason']; ?></td>
                                    <td><?php

                                        $d = $row['days'];
                                        $ddate = $row['starting_date'];
                                        echo $resumpdate2 = date('d M Y', strtotime($ddate . '+' . $d . ' days'));
                                        $ddate = strtotime($row['starting_date']);


                                        if ($row['status'] == 'Approve') {
                                           // print_r("hello");
                                            date_default_timezone_set('Africa/Lagos');
                                            $today = date("U");
                                            if ($ddate > $today) {
                                                $main_date = $ddate;
                                            } else {
                                                $main_date = $today;
                                            }
                                            $date1 = date_create($main_date);
                                            $date2 = date_create($resumpdate2);
                                            $diff = date_diff($date1, $date2);
                                            echo '<br>Days Remaining: ' . $days = $diff->format("%a");

                                            if ($days <= 0) {
                                                $sn = $row['sn'];
                                                $stmt2 = sprintf("UPDATE hrlvapply SET status='finish' WHERE sn='$sn'");
                                                $db->exec($stmt2);
                                            }
                                        }

                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['status'] == 'Approve') {
                                            echo "Approved";
                                        } elseif ($row['status'] == 'reject') {
                                            echo "Rejected";
                                        } elseif ($row['status'] == 'finish') {
                                            echo "Finished";
                                        } else {
                                            echo $row['status'];
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                    <a href="javascript:Clickheretoprint_history_leave()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
                <?php else : ?>
                    No data to show
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- stock related js -->
<script>
    $(document).on('click', '#upload_stock_btn', function() {
        //	$('.modal-title').text('Patient Medical Report'); 
        $('#upload_stock_modal').modal('show');
    });
    $(document).on('click', '.disengage_staff_btn', function() {
        $('#emp_id_field').val($('.disengage_staff_btn').attr('id'));
        $('#emp_engage_modal').modal('show');
    });
    $(document).on('click', '.engagement_history_btn', function() {
        $('#emp_engagement_history_modal').modal('show');
    });
    // another new code
    $(document).on('click', '.query_staff_btn', function() {
        $('#the_emp_id_field').val($('.query_staff_btn').attr('id'));
        $('#emp_query_modal').modal('show');
    });
    $(document).on('click', '.query_history_btn', function() {
        $('#emp_query_history_modal').modal('show');
    });
    $(document).on('click', '.restore_staff_btn', function() {
        $('#the_emp_id_field').val($('.restore_staff_btn').attr('id'));
        $('#emp_restore_modal').modal('show');
    });
    $(document).on('click', '.suspension_history_btn', function() {
        $('#emp_suspension_history_modal').modal('show');
    });
    $(document).on('click', '.leave_history_btn', function() {
        $('#emp_leave_history_modal').modal('show');
    });
    // end another new code
    // some more new code
    function show_reject_leave_modal(request_id) {
        $('#the_reject_request_id').val(request_id);
        remark_no = $('#the_reject_remark_no');
        $('#reject_leave_modal').modal('show');
        // make ajax request to check if a first remark has been added
        $.ajax({
            url: "fetch_leave_remarks.php",
            method: "POST",
            data: {
                request_id: request_id
            },
            success: function(data) {
                if (data != "") {
                    data = JSON.parse(data);
                    if (data.remarks != '') {
                        $('#reject_remarks1').text(data.remarks);
                    }
                    remark_no.val('2');
                    $('#reject_request_loader').hide();
                    $('#reject_leave_form').show();
                }
            },
            error: function(e) {
                $("#reject_request_loader").html("<span class='text-danger'>Failed to load some data, please refresh this page</span>");
            }
        });

    }

    function show_approve_leave_modal(request_id) {
        $('#the_approve_request_id').val(request_id);
        remark_no = $('#the_approve_remark_no');
        $('#approve_leave_modal').modal('show');
        // make ajax request to check if a first remark has been added
        $.ajax({
            url: "fetch_leave_remarks.php",
            method: "POST",
            data: {
                request_id: request_id
            },
            success: function(data) {
                if (data != "") {
                    data = JSON.parse(data);
                    if (data.remarks != '') {
                        $('#approve_remarks1').text(data.remarks);
                    }
                    remark_no.val('2');
                    $('#approve_request_loader').hide();
                    $('#approve_leave_form').show();
                }
            },
            error: function(e) {
                $("#approve_request_loader").html("<span class='text-danger'>Failed to load some data, please refresh this page</span>");
            }
        });
    }
    // end some more new code
    $(document).ready(function(e) {
        $("#stock_upload_form").on('submit', (function(e) {
            e.preventDefault();
            $('#upload_the_stock_btn').prop("disabled", true);
            $('#upload_the_stock_spinner').show();
            $.ajax({
                url: "excel_stock_download.php",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    //$("#preview").fadeOut();
                    $("#err_stock_upload").fadeOut();
                },
                success: function(data) {
                    if (data == 'invalid') {
                        // invalid file format.
                        $("#err_stock_upload").html("Invalid File !").fadeIn();
                    } else {
                        //console.log('done');
                        data = JSON.parse(data);
                        if (data.msg) {
                            toastr.success(data.msg, 'Successfully', {
                                timeOut: 5000
                            });
                        } else if (data.err) {
                            toastr.warning(data.err, 'Error', {
                                timeOut: 30000
                            });
                        }
                        $('#upload_the_stock_btn').prop("disabled", false);
                        $('#upload_the_stock_spinner').hide();
                        $('#upload_stock_modal').modal('hide');
                    }
                },
                error: function(e) {
                    $("#err_stock_upload").html(e).fadeIn();
                }
            });
        }));
    });

    $(document).on('click', '#download_stock_btn', function() {
        //	$('.modal-title').text('Patient Medical Report'); 
        $('#download_stock_modal').modal('show');
    });



    $(document).on('click', '.staff_passort', function() {
        //	$('.modal-title').text('Patient Medical Report'); 
        $('#staff_port_modal').modal('show');
    });

    // new code
    $(document).on('click', '.staff_other_documents', function() {
        //	$('.modal-title').text('Patient Medical Report'); 
        $('#uploadDocumentModal').modal('show');
    });



    $(document).on('click', '.edit_view_stock', function() {
        ///			  $('#edit_stock_modal').modal('show');  

        var edit_stock_id = $(this).attr("id");
        if (edit_stock_id != '') {
            $.ajax({
                url: "fetch_set.php",
                method: "POST",
                data: {
                    edit_stock_id: edit_stock_id
                },
                success: function(data) {

                    $('.modal-title').text('Stock Details');
                    $('#edit_stock_body').html(data);
                    $('#edit_stock_modal').modal('show');

                }
            });
        }

    });

    // new code
    $(document).on('click', '.edit_hmo_price', function() {
        ///			  $('#edit_stock_modal').modal('show');  

        var edit_hmo_stock_id = $(this).attr("id");
        if (edit_hmo_stock_id != '') {
            $.ajax({
                url: "fetch_set.php",
                method: "POST",
                data: {
                    edit_hmo_stock_id: edit_hmo_stock_id
                },
                success: function(data) {

                    $('.modal-title').text('HMO prices');
                    $('#edit_hmo_prices_body').html(data);
                    $('#edit_stock_modal').modal('hide');
                    $('#edit_hmo_prices_modal').modal('show');

                }
            });
        }

    });
</script>
<!-- /stock related js -->
<!-- stock upload error js -->
<script>
    <?php if (isset($upload_err)) { ?>
        toastr.error('<?php echo $upload_err; ?>', 'Error', {
            timeOut: 5000
        })
    <?php } ?>
    <?php if (isset($_GET['errors'])  or $errors == '1') { ?>
        toastr.success('<?php echo 'Error'; ?>', 'An error occured', {
            timeOut: 5000
        })
    <?php } ?>
</script>

<!-- /stock upload errror js -->