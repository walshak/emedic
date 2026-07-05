<div class="col-lg-3">
                <div class="ibox float-e-margins">
                    <div class="ibox-content mailbox-content">
                        <div class="file-manager">
                            <div class="space-25"></div>
                            <a href="mail_compose.php" class="btn btn-primary btn-block m-b-md" style="margin-bottom: 15px;">
                                <i class="fa fa-pencil-square-o"></i> Compose Mail
                            </a>
                            <h5>Folders</h5>
                            <ul class="folder-list m-b-md" style="padding: 0">
                                <li><a href="mailbox.php?inbox"> <i class="fa fa-inbox "></i> Inbox <span class="label label-warning pull-right"><?php echo $inbox; ?></span> </a></li>
                                <li><a href="mailbox.php?send"> <i class="fa fa-envelope"></i> Sent Mails</a></li>
                                <li><a href="mailbox.php?draft"> <i class="fa fa-file-text-o"></i> Drafts<span class="label label-danger pull-right"><?php echo $count_draft; ?></span></a></li>
                                <li><a href="mailbox.php?trash"> <i class="fa fa-trash-o"></i> Trash</a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>