 <div class="row animated fadeInRight">
     <div class="col-md-4">
         <div class="ibox float-e-margins">
             <div class="ibox-title">
                 <h5>Profile Detail</h5>
             </div>
             <div>
                 <div class="ibox-content no-padding border-left-right">

                     <img src="<?php
                                if (file_exists(staff_p . 'port_' . $profile . '.' . 'jpg')) {
                                    echo staff_p . 'port_' . $profile . '.' . 'jpg';
                                } else {
                                    echo '../img/no_photo.jpg';
                                } ?>" alt="" height="150" width="400" class="img-responsive">


                 </div>
                 <div class="ibox-content profile-content">
                     <h4><strong><?php echo $row['fullname'] . ' [' . $row['title'] . ']';
                                    $fullname = $row['fullname']; ?></strong></h4>
                     <p><i class="fa fa-at"></i> <?php echo $row['email']; ?></p>
                     <p><i class="fa fa-phone"></i> <?php echo $row['phone_number']; ?></p>
                     <hr>
                     <h5>
                         About me
                     </h5>
                     <p>
                         <?php if ($row['about_me'] == '') {
                                echo "Not Available";
                            } else {
                                echo $row['about_me'];
                            } ?>
                     </p>

                     <hr>
                     <div class="user-button">
                         <div class="row">
                             <div class="col-md-6">
                                 <a href="mailbox.php" class="btn btn-primary btn-sm btn-block"><i class="fa fa-envelope"></i>&nbsp; Sent Message</a>
                             </div>

                             <div class="col-md-6">
                                 <input type="button" name="edit" value="Add Tips" data-target="#myModal5" id="<?php echo $profile; ?>"
                                     class="btn btn-success btn-sm btn-block add_tips" />
                             </div>
                         </div>
                         <hr>
                         <div class="row">
                             <div class="col-md-6">

                                 <input type="button" name="Change Password" value="Change Password" data-target="#myModal5" id="<?php echo $profile; ?>" class="btn btn-primary btn-sm btn-block change_password" />

                             </div>
                             <div class="col-md-6">

                                 <input type="button" name="what_todo" value="Reminder & What todo " data-target="#myModal5" id="<?php echo $profile; ?>" class="btn btn-success btn-sm btn-block what_todo" />


                             </div>
                         </div>

                         <hr>
                         <div class="row">
                             <div class="col-md-6">

                                 <input type="button" name="Change ppt" value="Passport & Signature" data-target="#myModal5" id="<?php echo $profile; ?>" class="btn btn-primary btn-sm change_ppt" />

                             </div>
                             <div class="col-md-6">
                                 <img src="<?php
                                            if (file_exists(staff_p . 'sign_' . $profile . '.' . 'jpg')) {
                                                echo staff_p . 'sign_' . $profile . '.' . 'jpg';
                                            } else {
                                                echo '../img/no_sign.jpg';
                                            } ?>" alt="" height="150" width="150" class="img-responsive">
                             </div>
                         </div>

                     </div>
                 </div>
             </div>
         </div>
     </div>
     <div class="col-md-8">
         <div class="ibox">
             <div class="ibox-content">
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="m-b-md">

                             <input type="button" name="edit" value="Edit Profile" data-target="#myModal5" id="<?php echo $profile; ?>"
                                 class="btn btn-warning pull-right edit_profile" />
                             <h2><?php echo $fullname; ?></h2>
                         </div>
                         <dl class="dl-horizontal">
                             <dt>Status:</dt>
                             <dd><span class="label label-primary">Active</span></dd>
                         </dl>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-lg-5">
                         <dl class="dl-horizontal">

                             <dt>Log Counter:</dt>
                             <dd>
                                 <?php
                                    $stmt_logC = $db->query("SELECT Log_in FROM admin_users_logs WHERE username='$profile'");
                                    echo $stmt_logCOUNT = $stmt_logC->rowCount();
                                    ?>

                             </dd>
                             <dt>Messages:</dt>
                             <dd><?php
                                    $stmt = $db->query("SELECT sn FROM mails WHERE on_contact_username='$profile'");
                                    echo $stmt->rowCount();
                                    ?></dd>
                         </dl>
                     </div>
                     <div class="col-lg-7" id="cluster_info">
                         <dl class="dl-horizontal">

                             <dt>Last Time Login:</dt>
                             <dd>
                                 <?php $stmt_log = $db->query("SELECT Log_in FROM admin_users_logs WHERE username='$profile' order by sn limit 1");
                                    if ($stmt_log->rowCount() > 0) {
                                        $row_log = $stmt_log->fetch(PDO::FETCH_ASSOC);
                                        echo date("d M Y H:i:s a", strtotime($row_log['Log_in']));
                                    }
                                    ?>
                             </dd>
                             <dt>Created:</dt>
                             <dd> <?php if ($row['date_updated'] == '' or $row['date_updated'] == '0000-00-00') {
                                        echo 'Unknown';
                                    } else {
                                        echo date("d M Y", strtotime($row['date_updated']));
                                    } ?></dd>

                         </dl>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-lg-12">

                     </div>
                 </div>
                 <div class="row m-t-sm">
                     <div class="col-lg-12">
                         <div class="panel blank-panel">
                             <div class="panel-heading">
                                 <div class="panel-options">
                                     <ul class="nav nav-tabs">
                                         <li class="active"><a href="#tab-1" data-toggle="tab">Transactions</a></li>

                                         <?php $stmt_log = $db->query("SELECT Log_in FROM admin_users_logs WHERE username='$profile' order by sn limit 1");
                                            ?>
                                         <li class=""><a href="#tab-2" data-toggle="tab">Logs <?php echo '(' . $stmt_logCOUNT . ')'; ?></a></li>
                                     </ul>
                                 </div>
                             </div>

                             <div class="panel-body">

                                 <div class="tab-content">
                                     <div class="tab-pane active" id="tab-1">
                                         <div class="feed-activity-list">

                                             <div class="feed-element">
                                                 <div class="media-body ">
                                                     <div class="pull-left">
                                                         <span class="bar">5,3,9,6,5,9,7,3,5,2</span>
                                                         Total Investigation Requested
                                                     </div>
                                                     <div class="pull-right">
                                                         <?php

                                                            $setdate = date("Y-m-d");

                                                            $fullname = str_replace("'", "", trim($row_s["fullname"]));
                                                            //// QUEUE
                                                            $yr = date("Y");
                                                            $stmt = $db->query("SELECT labrequest_no FROM lab_manage WHERE data_capture_status='queue' and YEAR(request_date)='$yr' and request_by='$fullname'");
                                                            echo $queue = $stmt->rowCount();
                                                            ?>
                                                     </div>

                                                 </div>
                                             </div>

                                             <div class="feed-element">
                                                 <div class="media-body ">
                                                     <div class="pull-left">
                                                         <span class="bar">5,3,1,6,5,4,7,3,5,2</span>
                                                         Total Investigation Conducted
                                                     </div>
                                                     <div class="pull-right">
                                                         <?php
                                                            $setdate = date("Y-m-d");
                                                            //// approve
                                                            $yr = date("Y");
                                                            $stmt = $db->query("SELECT labrequest_no FROM lab_manage WHERE data_capture_status='approve' and YEAR(request_date)='$yr' and lab_sci_name='$fullname'");
                                                            echo $approve = $stmt->rowCount();
                                                            ?>

                                                     </div>

                                                 </div>
                                             </div>

                                             <div class="feed-element">
                                                 <div class="media-body ">
                                                     <div class="pull-left">
                                                         <span class="bar">5,3,2,-1,-3,-2,2,3,5,2</span>
                                                         Total Investigation Conducted On-Credit
                                                     </div>
                                                     <div class="pull-right">
                                                         <?php
                                                            $TotalPay = 0;
                                                            $stmt_cr = $db->query("SELECT P.pay FROM patient_ap_services AS P INNER JOIN lab_manage AS L ON L.labrequest_no=P.drug_sn WHERE (P.serv_group='Laboratory' or P.serv_group='Radiology') and YEAR(P.date_entry)='$yr' and P.cr='1' and L.collected_by='$fullname'");
                                                            if ($stmt_cr->rowCount() > 0) {
                                                                $c = $stmt_cr->rowCount();
                                                                while ($roww = $stmt_cr->fetch(PDO::FETCH_ASSOC)) {
                                                                    $TotalPay = $TotalPay + $roww['pay'];
                                                                }
                                                            }
                                                            echo $c; ?> | <?php if ($c == 0) {
                    echo "Zero Naira";
                } else {
                    echo 'N ' . $TotalPay;
                }
                ?>
                                                     </div>

                                                 </div>
                                             </div>

                                         </div>

                                     </div>
                                     <div class="tab-pane" id="tab-2">

                                         <?php
                                            $n = 1;
                                            $stmt_log = $db->query("SELECT * FROM admin_users_logs WHERE username='$profile' order by sn desc limit 20");
                                            if ($stmt_log->rowCount() > 0) {


                                            ?>
                                             <table class="table table-striped">
                                                 <thead>
                                                     <tr>
                                                         <th>#</th>
                                                         <th>Time/In</th>
                                                         <th>Time/Out</th>
                                                         <th>Activities</th>
                                                     </tr>
                                                 </thead>
                                                 <tbody>
                                                     <?php $n = 1;
                                                        while ($row_log = $stmt_log->fetch(PDO::FETCH_ASSOC)) { ?>
                                                         <tr>
                                                             <td>
                                                                 <?php echo $n; ?>
                                                             </td>
                                                             <td>
                                                                 <?php echo $row_log['Log_in']; ?>
                                                             </td>
                                                             <td>
                                                                 <?php echo $row_log['Log_out']; ?>
                                                             </td>
                                                             <td>
                                                                 .
                                                             </td>

                                                         </tr>
                                                     <?php $n++;
                                                        } ?>
                                                 </tbody>
                                             </table>
                                         <?php } ?>
                                     </div>
                                 </div>

                             </div>

                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>


     <div class="modal inmodal fade" id="tips_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog modal-lg">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                     <h4 class="modal-title" id="">Add Tips</h4>
                 </div>

                 <div class="modal-body">
                     <form method="POST" id="tips_form">
                         <strong>Add tips you want other users to see on the login page</strong>
                         <hr>
                         <div class="mail-text h-200">

                             <textarea name="msg" id="msg" cols="45" rows="5" maxlength="160" class=" form-control" placeholder="Type Your Tips Here"><?php echo $my_tips; ?></textarea>

                             <div class="clearfix"></div>
                         </div>

                         <button class="btn btn-sm btn-primary" type="submit" name="save" id="save"><i class="fa fa-reply"></i> Save</button>
                         <input type="hidden" name="username" id="username" value="<?php echo $profile; ?>" />
                         <input type="hidden" name="MM_update" value="add_tips_info" />
                     </form>
                 </div>
             </div>
         </div>
     </div>

     <div class="modal inmodal fade" id="password_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog modal-lg">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                     <h4 class="modal-title" id="">Change Password</h4>
                 </div>

                 <div class="modal-body">
                     <form method="POST" id="password_form">

                         <H2 style="color: darkred; ">CHANGE PASSWORD </H2>
                         <small style="color: indianred;">System Automatically Logout After Changed to Re-login</small>
                         <hr>

                         <div class="form_sep">
                             <label for="password">Enter New Password</label>
                             <input type="password" name="password2" id="password2" class="form-control" maxlength="20">
                         </div>

                         <div class="form_sep">
                             <label for="password">Re-enter New Password</label>
                             <input type="password" name="re_password" id="re_password" class="form-control" maxlength="20">
                         </div>
                         <div class="form_sep">
                             <button class="btn btn-sm btn-primary" type="submit" name="save" id="save"><i class="fa fa-save"></i>&nbsp; Save</button>
                         </div>

                         <input type="hidden" name="username" id="username" value="<?php echo $profile; ?>" />
                         <input type="hidden" name="MM_update" value="change_password" />
                     </form>
                 </div>
             </div>
         </div>
     </div>


     <div class="modal inmodal fade" id="profile_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog modal-lg">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                     <h4 class="modal-title" id="">Edit Profile</h4>
                 </div>

                 <div class="modal-body">
                     <form method="POST" id="profile_form">

                         <div class="form_sep">
                             <label>Title</label>
                             <select name="title" id="title" class="form-control" data-required="true">
                                 <?php if ($row['title'] != '') { ?>
                                     <option value="<?php echo $row['title']; ?>" selected><?php echo $row['title']; ?></option>
                                 <?php } else { ?>
                                     <option selected="selected" value="">Select...</option>
                                 <?php } ?>

                                 <option value="Dr.">Dr.</option>
                                 <option value="Mr.">Mr.</option>
                                 <option value="Mrs. ">Mrs. </option>
                                 <option value="Miss. ">Miss. </option>
                                 <option value="Pharm.">Pharm. </option>
                             </select>
                         </div>


                         <div class="form_sep">
                             <label for="password">Name</label>
                             <input type="test" name="name" id="name" value="<?php echo $row['fullname']; ?>" class="form-control" disabled>
                         </div>

                         <div class="form_sep">
                             <label>Email</label>
                             <input type="text" id="email" name="email" class="form-control" data-required="true" maxlength="50" value="<?php echo $row['email']; ?>">

                         </div>
                         <div class="form_sep">
                             <label>Phone Number</label>

                             <input type="text" id="phone" name="phone" class="form-control" data-required="true" maxlength="24" value="<?php echo $row['phone_number']; ?>">

                         </div>


                         <div class="form_sep">
                             <label>Short Note About Me</label>
                             <textarea name="about_me" id="about_me" cols="45" rows="5" class=" form-control" placeholder="Type  Here"><?php echo $row['about_me']; ?></textarea>

                         </div>

                         <div class="form_sep">
                             <button class="btn btn-sm btn-primary" type="submit" name="save" id="save"><i class="fa fa-save"></i>&nbsp; Save</button>
                         </div>

                         <input type="hidden" name="username" id="username" value="<?php echo $profile; ?>" />
                         <input type="hidden" name="MM_update" value="edit_profile" />
                     </form>
                 </div>
             </div>
         </div>
     </div>

     <div class="modal inmodal fade" id="what_todo_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog modal-lg">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                     <h4 class="modal-title" id="">What todo & Reminder</h4>
                 </div>

                 <div class="modal-body">
                     <form method="POST" id="what_todo_form">

                         <div class="form_sep">
                             <label>Short Note</label>
                             <input type="text" name="note" id="note" class="form-control" maxlength="30">
                         </div>

                         <div class="form_sep">
                             <label class="req"><strong>Enter Time:</strong></label>
                             <div class="input-group bootstrap-timepicker timepicker">


                                 <input id="timepicker2" name="note_time" type="text" class="form-control input-small">
                                 <span class="input-group-addon">
                                     <i class="glyphicon glyphicon-time"></i>
                                 </span>
                             </div>
                         </div>

                         <div class="form_sep" id="data_1">
                             <label class="req"><strong>Date:</strong></label>
                             <div class="input-group date">
                                 <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                 <input type="text" name="note_date" class="form-control" value="<?php echo $row['next_due_date']; ?>">
                             </div>
                         </div>


                         <div class="form_sep">
                             <button class="btn btn-sm btn-primary" type="submit" name="save" id="save"><i class="fa fa-save"></i>&nbsp; Save</button>
                         </div>

                         <input type="hidden" name="username" id="username" value="<?php echo $profile; ?>" />
                         <input type="hidden" name="MM_update" value="what_to_do" />
                     </form>
                 </div>
             </div>
         </div>
     </div>


     <div class="modal inmodal fade" id="passport_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog modal-sm">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                     <h4 class="modal-title" id="">Passport & Signature</h4>
                 </div>

                 <div class="modal-body">

                     <form method="POST" id="passport_body" enctype="multipart/form-data" action="index.php">

                         <div class="form_sep">
                             <strong>Add New/Change Passport/Signature</strong>
                         </div>

                         <div class="form_sep">
                             <input type="file" name="file_foto" id="file_foto" class="form-control" />
                         </div>

                         <div class="form_sep">
                             <label>Upload Type</label>
                             <select name="upload_type" id="upload_type" class="form-control" required>

                                 <option selected="selected" value="">Select...</option>
                                 <option value="port">Passport</option>
                                 <option value="sign">Signature</option>
                             </select>
                         </div>


                         <div class="form_sep">

                             <button class="btn btn-primary btn-xs" type="submit" name="upload_pass">Upload</button>&nbsp;&nbsp;
                             <a href="" class="btn btn-warning btn-xs Cancel_lab_request">Cancel</a>

                         </div>
                         <input type="hidden" name="username" id="username" value="<?php echo $profile; ?>" />
                         <input type="hidden" name="MM_update" value="passport" />

                     </form>
                 </div>
             </div>
         </div>
     </div>