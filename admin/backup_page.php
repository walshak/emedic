<?php

session_start();
include("../Connections/Conn.php");
$backupDir = '../backup';

$username = $_SESSION['username'];
$stmt = $db->prepare("SELECT backup_db FROM admin_users_rights WHERE username = :username LIMIT 1");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$backup_db = $row['backup_db'];

// Check if the directory exists
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Get the list of all directories in the backup folder
$directories = array_filter(glob($backupDir . '/*'), 'is_dir');


// Handle delete action
if (isset($_GET['delete'])) {
    $dirToDelete = $backupDir . '/' . basename($_GET['delete']);

    // Ensure the directory exists and is not `.` or `..`
    if (is_dir($dirToDelete) && !in_array($dirToDelete, ['.', '..'])) {
        // Delete the directory and its contents
        deleteDirectory($dirToDelete);
        echo "<script>alert('Directory deleted successfully!'); window.location.href = './backup_page.php';</script>";
    }
}

// Recursive function to delete a directory and its contents
function deleteDirectory($dir)
{
    if (!is_dir($dir)) return;

    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = "$dir/$item";
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}
?>

<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>

</head>
<title>WebMedic | <?php echo $_SESSION['Designation']; ?></title>

</head>

<body class="fixed-navigation">
    <div id="wrapper">

        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element"> <span>
                                <img alt="image" class="img-circle" src="<?php if (file_exists(staff_p . 'port_' . $uname . '.' . 'jpg')) {
                                                                                echo staff_p . 'port_' . $uname . '.' . 'jpg';
                                                                            } else {
                                                                                echo '../img/user_avatar_lg.png';
                                                                            } ?>" height="50" width="50">

                            </span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname ?></strong>
                                    </span> <span class="text-muted text-xs block"><?php echo $_SESSION['Designation'] ?> <b class="caret"></b></span> </span> </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="index.php?profile=<?php echo $_SESSION['username'] ?>">Profile</a></li>
                                <li><a href="mailbox.php">Mailbox</a></li>
                                <li class="divider"></li>
                                <li><a href="index.php">Logout</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            IN+
                        </div>
                    </li>

                    <li>
                        <a href="index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
                    </li>

                    <?php include("../inc/nav_side_profile.php");
                    ?>

                </ul>

            </div>
        </nav>


        <div id="page-wrapper" class="gray-bg sidebar-content">

            <div class="container">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Manage Backups</h5>
                    </div>
                    <div class="ibox-content">
                        <h2>Perfom backup</h2>
                        <?php if ($backup_db == 1): ?>
                            <a href="./backup_script.php" target="_blank" class="btn btn-lg btn-primary" onclick="return confirm('Are you sure you wish to initiate a Database backup sequence?')">Do Backup</a>
                        <?php elseif ($backup_db == 0): ?>
                            <h3 style="color: red;">ACCESSED DENIED</h3>
                        <?php endif; ?>

                        <hr>
                        <h2>Backup Directory Listing</h2>
                        <div class="row folder-listing">
                            <?php if (count($directories) > 0): ?>
                                <?php foreach ($directories as $dir):
                                    $folderName = basename($dir);
                                ?>
                                    <div class="col-md-3 folder-item">
                                        <span class="glyphicon glyphicon-folder-open folder-icon" aria-hidden="true"></span>
                                        <div class="folder-name"><?= htmlspecialchars($folderName) ?></div>
                                        <button class="btn btn-danger" onclick="confirmDelete('<?= htmlspecialchars($folderName) ?>')">
                                            <span class="glyphicon glyphicon-trash"></span> Delete
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-md-12">
                                    <p>No directories found.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <style>
                    .folder-listing {
                        margin-top: 20px;
                    }

                    .folder-item {
                        text-align: center;
                        margin-bottom: 20px;
                    }

                    .folder-icon {
                        font-size: 50px;
                    }

                    .folder-name {
                        margin-top: 10px;
                        font-weight: bold;
                    }
                </style>
                <script>
                    function confirmDelete(dir) {
                        if (confirm("Are you sure you want to delete this folder?, this action is permanent!!")) {
                            window.location.href = "?delete=" + encodeURIComponent(dir);
                        }
                    }
                </script>

                <?php include('../modal_lock.php'); ?>
                <?php include("../inc/footer_scripts.php"); ?>

                <?php include("../admin/admin_js_script.php"); ?>

                <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
                <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
                <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
                <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
                <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
                <script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
                <script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>

                <script src="../js/idle.js"></script>
</body>

</html>