<?php
session_start();
include("../Connections/Conn.php"); ?>

<!DOCTYPE html>
<html>

<head>

    <?php include("../inc/header.php"); ?>

<body>

    <div id="wrapper">

        <?php include("../inc/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>

            <div class="wrapper wrapper-content">
                <div class="row">
                    <?php include("mail_counters.php"); ?>
                    <?php include("mail_icons.php"); ?>
                    <?php
                    if (isset($_GET['m'])) {
                        $reader = $_GET['m'];
                        $update = "UPDATE mails SET read_status=1 WHERE sn='$reader'";
                        $db->exec($update);

                        $stmt = $db->query("SELECT * FROM mails WHERE sn='$reader'");
                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    } elseif (isset($_GET['del'])) {
                        $reader = $_GET['del'];
                        $update = "UPDATE mails SET mail_status='trash' WHERE sn='$reader'";
                        $db->exec($update);
                        header("location:mailbox.php");
                    }
                    ?>

                    <div class="col-lg-9 animated fadeInRight">
                        <div class="mail-box-header">
                            <div class="pull-right tooltip-demo">
                                <a href="mail_compose.php?reply=<?php echo htmlspecialchars($reader); ?>" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Reply">
                                    <i class="fa fa-reply"></i> Reply
                                </a>
                                <a href="#" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Print email" onclick="printDiv('mail_print_content')">
                                    <i class="fa fa-print"></i>
                                </a>
                                <a href="mail_detail.php?del=<?php echo htmlspecialchars($reader); ?>" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Move to trash">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            </div>
                            <h2>View Message</h2>

                            <div class="mail-tools tooltip-demo m-t-md">
                                <h3>
                                    <span class="font-normal">Subject: </span>
                                    <?php echo htmlspecialchars($row['subject']); ?>
                                </h3>
                                <?php
                                // Prepare the SQL statement to fetch sender information including Designation and unit head status
                                $stmt24 = $db->prepare("
                                    SELECT admin_users.*, hremp.Designation
                                    FROM admin_users 
                                    LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
                                    WHERE admin_users.username = :from_name
                                ");

                                // Execute the statement with the provided username
                                $l = $stmt24->execute([':from_name' => $row['from_username']]);
                                $sender_r = $stmt24->fetch(PDO::FETCH_ASSOC);

                                // Extract designation and unit head status
                                $designation = isset($sender_r['Designation']) ? htmlspecialchars($sender_r['Designation']) : '';
                                $unitHeadStatus = (isset($sender_r['unit_head']) && $sender_r['unit_head'] == 1) ? ', Unit Head' : '';
                                ?>
                                <h5>
                                    <span class="pull-right font-normal">
                                        <?php echo htmlspecialchars(date("h:i A d M Y", strtotime($row['mail_date']))); ?>
                                    </span>
                                    <span class="font-normal">From: </span>
                                    <?php
                                    echo htmlspecialchars($row['from_name']);
                                    if ($designation || $unitHeadStatus) {
                                        echo ' (' . $designation . $unitHeadStatus . ')';
                                    }
                                    ?>
                                </h5>

                            </div>
                        </div>

                        <div class="mail-box">
                            <div class="mail-body">
                                <p><?php echo nl2br($row['msg']); ?></p>
                            </div>

                            <?php if ($row['attachment_status'] == 1 && !empty($row['attachments'])): ?>
                                <div class="mail-attachment">
                                    <p>
                                        <span><i class="fa fa-paperclip"></i>
                                            <?php
                                            $attachments = explode(';', $row['attachments']);
                                            echo count($attachments) . ' attachment' . (count($attachments) > 1 ? 's' : '');
                                            ?>
                                        </span>
                                    </p>

                                    <div class="attachment">
                                        <?php foreach ($attachments as $attachment):
                                            // Extract filename and file extension
                                            $filename = basename($attachment);
                                            $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                                            // Determine icon based on file type
                                            $icon = 'fa-file-o'; // default
                                            switch ($fileext) {
                                                case 'doc':
                                                case 'docx':
                                                    $icon = 'fa-file-word-o';
                                                    break;
                                                case 'pdf':
                                                    $icon = 'fa-file-pdf-o';
                                                    break;
                                                case 'xls':
                                                case 'xlsx':
                                                    $icon = 'fa-file-excel-o';
                                                    break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif':
                                                    $icon = 'fa-file-image-o';
                                                    break;
                                            }
                                        ?>
                                            <div class="file-box">
                                                <div class="file">
                                                    <a href="../<?php echo ($attachment); ?>" download>
                                                        <span class="corner"></span>
                                                        <div class="icon">
                                                            <i class="fa <?php echo $icon; ?>"></i>
                                                        </div>
                                                        <div class="file-name">
                                                            <?php echo htmlspecialchars($filename); ?>
                                                            <br />
                                                            <!-- <small>Added: <?php echo date("M d, Y", filemtime($attachment)); ?></small> -->
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mail-body text-right tooltip-demo">
                                <a class="btn btn-sm btn-white" href="mail_compose.php?reply=<?php echo htmlspecialchars($reader); ?>">
                                    <i class="fa fa-reply"></i> Reply
                                </a>
                                <a class="btn btn-sm btn-white" href="mail_compose.php?fwd=<?php echo htmlspecialchars($reader); ?>">
                                    <i class="fa fa-arrow-right"></i> Forward
                                </a>
                                <button title="Print" data-placement="top" data-toggle="tooltip" type="button" class="btn btn-sm btn-white" onclick="printDiv('mail_print_content')">
                                    <i class="fa fa-print"></i> Print
                                </button>
                                <a class="btn btn-sm btn-white" href="mail_detail.php?del=<?php echo htmlspecialchars($reader); ?>" title="Trash">
                                    <i class="fa fa-trash-o"></i> Remove
                                </a>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="col-lg-9 animated fadeInRight" id="mail_print_content" style="display:none;">
                        <div class="mail-box-header">
                            <h2>
                                Message
                            </h2>
                            <div class="mail-tools tooltip-demo m-t-md">
                                <h3>
                                    <span class="font-noraml">Subject: </span><?php echo $row['subject'] ?>
                                </h3>
                                <h5>
                                    <span class="pull-right font-noraml"><?php echo date("h:m A d M Y", strtotime($row['mail_date'])); ?></span>
                                    <span class="font-noraml">From: </span><?php echo $row['from_name'] ?>
                                </h5>
                            </div>
                        </div>
                        <div class="mail-box">


                            <div class="mail-body">
                                <p>
                                    <?php echo $row['msg'] ?>
                                </p>
                            </div>
                            <?php if ($row['attachment_status'] == 1 && !empty($row['attachments'])): ?>
                                <div class="mail-attachment">
                                    <p>
                                        <span><i class="fa fa-paperclip"></i>
                                            <?php
                                            $attachments = explode(';', $row['attachments']);
                                            echo count($attachments) . ' attachment' . (count($attachments) > 1 ? 's' : '');
                                            ?>
                                        </span>
                                    </p>

                                    <div class="attachment">
                                        <?php foreach ($attachments as $attachment):
                                            // Extract filename and file extension
                                            $filename = basename($attachment);
                                            $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                                            // Determine icon based on file type
                                            $icon = 'fa-file-o'; // default
                                            switch ($fileext) {
                                                case 'doc':
                                                case 'docx':
                                                    $icon = 'fa-file-word-o';
                                                    break;
                                                case 'pdf':
                                                    $icon = 'fa-file-pdf-o';
                                                    break;
                                                case 'xls':
                                                case 'xlsx':
                                                    $icon = 'fa-file-excel-o';
                                                    break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif':
                                                    $icon = 'fa-file-image-o';
                                                    break;
                                            }
                                        ?>
                                            <div class="file-box">
                                                <div class="file">
                                                    <a href="../<?php echo urlencode($attachment); ?>" download>
                                                        <span class="corner"></span>
                                                        <div class="icon">
                                                            <i class="fa <?php echo $icon; ?>"></i>
                                                        </div>
                                                        <div class="file-name">
                                                            <?php echo htmlspecialchars($filename); ?>
                                                            <br />
                                                            <!-- <small>Added: <?php echo date("M d, Y", filemtime($attachment)); ?></small> -->
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div>


                        </div>
                    </div>
                </div>
            </div>
            <?php include("../inc/footer.php"); ?>

        </div>
    </div>

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
        $(document).ready(function() {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
    </script>
    <script>
        function printDiv(divId) {
            var content = document.getElementById(divId).innerHTML;
            var popupWindow = window.open('', '_blank', 'width=600,height=600');
            popupWindow.document.open();
            popupWindow.document.write('<html><head><title>' + document.title + '</title>');
            // Reference the external stylesheet from the main page
            popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
            popupWindow.document.write('</head><body>');
            popupWindow.document.write(content);
            popupWindow.document.write('</body></html>');
            popupWindow.document.close();
            popupWindow.print();
        }
    </script>
</body>

</html>