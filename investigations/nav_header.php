<div class="row border-bottom">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>

        </div>
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <span class="m-r-sm text-muted welcome-message">WebMedic (<b><?= $_SESSION['dept_name']; ?></b>)</span>
            </li>
            <?php

            $usern = $_SESSION['username'];
            $stmt_inbox = $db->query("SELECT * FROM mails WHERE on_contact_username='$usern' and mail_status='send' and read_status='0' order by sn desc limit 5");
            $newemail = $stmt_inbox->rowCount();

            ?>
            <?php if ($newemail > 0) { ?>
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-envelope"></i> <span class="label label-warning"><?php echo $newemail; ?></span>
                    </a>

                    <ul class="dropdown-menu dropdown-messages">
                        <?php while ($row_i = $stmt_inbox->fetch(PDO::FETCH_ASSOC)) { ?>
                            <li>
                                <div class="dropdown-messages-box">
                                    <a href="" class="pull-left">
                                        <img alt="image" class="img-circle" src="../img/mail.jpg">
                                    </a>
                                    <div class="media-body">
                                        <small class="pull-right"><?php include_once("../inc/dd.php");
                                                                    echo dateDiff($row_i['mail_date']); ?></small>
                                        <?php echo substr($row_i['subject'], 0, 30); ?>
                                        <strong><?php if ($row_i['subject'] == '') {
                                                    echo 'No Subject';
                                                } else {
                                                    echo substr($row_i['subject'], 0, 50);
                                                } ?></strong>. <br>
                                        <small class="text-muted"><?php include_once("../inc/dd.php");
                                                                    echo getTheDay2($row_i['mail_date']); ?>
                                        </small>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                        <?php } ?>


                        <li>
                            <div class="text-center link-block">
                                <a href="../profile/mailbox.php">
                                    <i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>
            <?php } ?>



            <?php if ($alertCount > 0) { ?>
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell"></i> <span class="label label-primary"><?php echo $alertCount; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                        <?php if ($malert->rowCount() > 0) { ?>
                            <li class="divider"></li>
                            <li>
                                <a href="#">
                                    <div>
                                        <i class="fa fa-gears fa-fw"></i> <?php echo $malert->rowCount() ?> Equipment(s) Due for Services.
                                        <!--<span class="pull-right text-muted small">Some minutes ago</span>-->
                                    </div>
                                </a>
                            </li>
                        <?php } ?>
                        <li class="divider"></li>
                        <li>
                            <a href="grid_options.html">
                                <div>
                                    <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>

            <li>
                <a href="../logout.php">
                    <i class="fa fa-sign-out"></i> Log out
                </a>
            </li>
        </ul>

    </nav>
</div>