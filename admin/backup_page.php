<?php

session_start();
if (!defined('staff_p')) {
    define('staff_p', '../uploads/staff/');
}
include("../Connections/Conn.php");
$backupDir = '../backup';

$username = $_SESSION['username'];
$uname = $_SESSION['username'];
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $uname;
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
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#local" aria-controls="local" role="tab" data-toggle="tab">Local Server Backup</a></li>
                            <li role="presentation"><a href="#usb" aria-controls="usb" role="tab" data-toggle="tab">USB Drive Backup</a></li>
                            <li role="presentation"><a href="#remote" aria-controls="remote" role="tab" data-toggle="tab">Remote Replication</a></li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content" style="padding-top: 20px;">
                            
                            <!-- TAB 1: LOCAL -->
                            <div role="tabpanel" class="tab-pane active" id="local">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <strong>Local Server Backup:</strong> This performs an immediate database dump and saves it directly to a folder on this server. This is useful for creating quick, manual snapshots before making major configuration changes.
                                </div>
                                
                                <h2>Perform Backup</h2>
                                <?php if ($backup_db == 1): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">Execute Backup</div>
                                                <div class="panel-body">
                                                    <p class="text-muted"><small><strong>Run Now:</strong> Manually trigger the local backup sequence.</small></p>
                                                    <button class="btn btn-warning" id="btn_local_backup" onclick="runLocalBackup(this)">Run Local Backup Now</button>
                                                    <button class="btn btn-danger" id="btn_stop_local" style="display: none;" onclick="stopLocalBackup(this)">Stop Backup</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">Live Log</div>
                                                <div class="panel-body">
                                                    <p class="text-muted"><small><strong>Live Log:</strong> View the real-time execution log of the backup process.</small></p>
                                                    <pre id="local_log" style="height: 200px; overflow-y: scroll; background: #2f4050; color: #fff; font-size: 11px;">Awaiting backup...</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($backup_db == 0): ?>
                                    <h3 style="color: red;">ACCESS DENIED</h3>
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
                            
                            <!-- TAB 2: USB -->
                            <div role="tabpanel" class="tab-pane" id="usb">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <strong>USB Drive Backup:</strong> This performs a scheduled database dump and automatically synchronizes it to any connected USB flash drives or external storage. This is an essential offline, cold-storage backup strategy.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">USB Backup Schedule</div>
                                            <div class="panel-body">
                                                <p class="text-muted"><small><strong>Schedule (Hour/Minute):</strong> The specific time (in 24-hour format) the cron job will execute daily.</small></p>
                                                <!-- Read-Only View -->
                                                <div id="usb_readonly_view">
                                                    <p><strong>Scheduled:</strong> <span id="usb_display">Not set</span></p>
                                                    <button class="btn btn-default btn-sm" onclick="toggleEdit('usb', true)">Edit</button>
                                                </div>
                                                
                                                <!-- Edit View -->
                                                <div id="usb_edit_view" style="display: none;">
                                                    <div class="form-inline">
                                                        <div class="form-group">
                                                            <label>Hour (0-23):</label>
                                                            <input type="number" id="usb_hour" class="form-control" min="0" max="23" style="width: 70px;">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Minute (0-59):</label>
                                                            <input type="number" id="usb_min" class="form-control" min="0" max="59" style="width: 70px;">
                                                        </div>
                                                        <button class="btn btn-primary" onclick="saveSchedule('usb', this)">Save</button>
                                                        <button class="btn btn-default" onclick="toggleEdit('usb', false)">Cancel</button>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p class="text-muted"><small><strong>Run Now:</strong> Manually trigger the USB synchronization process in the background.</small></p>
                                                <button class="btn btn-warning" onclick="runNow('usb', this)">Run USB Backup Now</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Live Log</div>
                                            <div class="panel-body">
                                                <p class="text-muted"><small><strong>Live Log:</strong> View the real-time execution log to ensure drives are mounted and written to successfully. <button class="btn btn-xs btn-info pull-right" onclick="pollLogs()"><i class="fa fa-refresh"></i> Load Latest Logs</button></small></p>
                                                <pre id="usb_log" style="height: 200px; overflow-y: scroll; background: #2f4050; color: #fff; font-size: 11px;">Loading...</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h3>Existing USB Backups</h3>
                                <button class="btn btn-info btn-sm" onclick="probeUsbBackups(this)"><i class="fa fa-refresh"></i> Scan Drives</button>
                                <div class="row" style="margin-top: 15px;">
                                    <div class="col-md-4">
                                        <div id="usb_backups_list">
                                            <p class="text-muted">Click "Scan Drives" to detect connected drives.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <span id="selected_usb_name">Drive Backups</span>
                                            </div>
                                            <div class="panel-body" id="usb_backups_files">
                                                <p class="text-muted">Select a drive to view its backups.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- TAB 3: REMOTE -->
                            <div role="tabpanel" class="tab-pane" id="remote">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <strong>Remote Replication:</strong> This performs a scheduled synchronization of your local database to a secure remote server provided by Prosoft, the makers of WebMedic. Please contact WebMedic support for your setup settings. This ensures an off-site, live copy of your data is always available in the event of catastrophic hardware failure.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Remote Replication Schedule</div>
                                            <div class="panel-body">
                                                <p class="text-muted"><small><strong>Schedule (Hour/Minute):</strong> The specific time the replication script will execute daily.</small></p>
                                                <!-- Read-Only View -->
                                                <div id="cpanel_readonly_view">
                                                    <p><strong>Scheduled:</strong> <span id="cpanel_display">Not set</span></p>
                                                    <button class="btn btn-default btn-sm" onclick="toggleEdit('cpanel', true)">Edit</button>
                                                </div>

                                                <!-- Edit View -->
                                                <div id="cpanel_edit_view" style="display: none;">
                                                    <div class="form-inline">
                                                        <div class="form-group">
                                                            <label>Hour (0-23):</label>
                                                            <input type="number" id="cpanel_hour" class="form-control" min="0" max="23" style="width: 70px;">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Minute (0-59):</label>
                                                            <input type="number" id="cpanel_min" class="form-control" min="0" max="59" style="width: 70px;">
                                                        </div>
                                                        <button class="btn btn-primary" onclick="saveSchedule('cpanel', this)">Save</button>
                                                        <button class="btn btn-default" onclick="toggleEdit('cpanel', false)">Cancel</button>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p class="text-muted"><small><strong>Run Now:</strong> Manually trigger the replication immediately.</small></p>
                                                <button class="btn btn-warning" id="btn_remote_repl" onclick="runRemoteReplication(this)">Run Replication Now</button>
                                                <button class="btn btn-danger" id="btn_stop_remote" style="display: none;" onclick="stopRemoteReplication(this)">Stop Replication</button>
                                            </div>
                                        </div>
                                        
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Remote DB Configuration</div>
                                            <div class="panel-body">
                                                <p class="text-muted"><small><strong>Configuration:</strong> The connection details of the remote target server.</small></p>
                                                <!-- Read-Only View -->
                                                <div id="rep_readonly_view">
                                                    <p><strong>Host:</strong> <span id="disp_rep_host"></span></p>
                                                    <p><strong>User:</strong> <span id="disp_rep_user"></span></p>
                                                    <p><strong>DB:</strong> <span id="disp_rep_db"></span></p>
                                                    <p><strong>Password:</strong> ********</p>
                                                    <button class="btn btn-default btn-sm" onclick="toggleRepEdit(true)">Edit</button>
                                                </div>

                                                <!-- Edit View -->
                                                <div id="rep_edit_view" style="display: none;">
                                                    <div class="form-group">
                                                        <label>Host:</label>
                                                        <input type="text" id="rep_host" class="form-control" placeholder="e.g. srv561.hstgr.io">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>User:</label>
                                                        <input type="text" id="rep_user" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Password:</label>
                                                        <input type="password" id="rep_pass" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Database:</label>
                                                        <input type="text" id="rep_db" class="form-control">
                                                    </div>
                                                    <button class="btn btn-info btn-sm" onclick="saveRepConfig(this)">Save Remote Config</button>
                                                    <button class="btn btn-default btn-sm" onclick="toggleRepEdit(false)">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Remote Database Status</div>
                                            <div class="panel-body">
                                                <button class="btn btn-info btn-sm" onclick="probeRemoteDb(this)"><i class="fa fa-refresh"></i> Probe Remote DB</button>
                                                <div id="remote_db_status" style="margin-top: 15px;">
                                                    <p class="text-muted">Click to connect and fetch table telemetry.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Live Log</div>
                                            <div class="panel-body">
                                                <p class="text-muted"><small><strong>Live Log:</strong> View real-time execution logs. <button class="btn btn-xs btn-info pull-right" onclick="pollLogs()"><i class="fa fa-refresh"></i> Load Latest Logs</button></small></p>
                                                <pre id="cpanel_log" style="height: 200px; overflow-y: scroll; background: #2f4050; color: #fff; font-size: 11px;">Loading...</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h2>Cron Permission Troubleshooter</h2>
                        <button class="btn btn-info" onclick="checkPermissions()">Check Cron Permissions</button>
                        <pre id="perm_debug" style="margin-top: 10px; display: none; background: #f3f3f4; border: 1px solid #ccc;"></pre>

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
                    function runLocalBackup(btn) {
                        swal({
                            title: "Are you sure?",
                            text: "Do you want to initiate a Database backup sequence?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes, run it!"
                        }).then(function() {
                            var $btn = $(btn);
                            var oldText = $btn.html();
                            $btn.html('<i class="fa fa-spinner fa-spin"></i> Backing up...').prop('disabled', true).hide();
                            $('#btn_stop_local').show();
                            
                            var logBox = document.getElementById('local_log');
                            logBox.textContent = 'Initializing connection...\n';
                            
                            var xhr = new XMLHttpRequest();
                            xhr.open('GET', 'backup_script.php', true);
                            
                            var seenBytes = 0;
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState > 2) {
                                    var newData = xhr.responseText.substring(seenBytes);
                                    logBox.textContent += newData;
                                    seenBytes = xhr.responseText.length;
                                    // Auto-scroll to bottom
                                    logBox.scrollTop = logBox.scrollHeight;
                                }
                                
                                if (xhr.readyState === 4) {
                                    $btn.html(oldText).prop('disabled', false).show();
                                    $('#btn_stop_local').hide();
                                    if (xhr.status === 200) {
                                        swal("Completed!", "Local backup has finished. Refreshing list...", "success");
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 1500);
                                    } else {
                                        swal("Error", "Backup failed with status: " + xhr.status, "error");
                                    }
                                }
                            };
                            xhr.send();
                        });
                    }

                    function confirmDelete(dir) {
                        if (confirm("Are you sure you want to delete this folder?, this action is permanent!!")) {
                            window.location.href = "?delete=" + encodeURIComponent(dir);
                        }
                    }

                    function loadSchedules() {
                        $.get('backup_cron_handler.php?action=get_schedule', function(res) {
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    $('#usb_hour').val(data.usb.hour);
                                    $('#usb_min').val(data.usb.min);
                                    $('#cpanel_hour').val(data.cpanel.hour);
                                    $('#cpanel_min').val(data.cpanel.min);

                                    if (data.usb.hour !== '') $('#usb_display').text(String(data.usb.hour).padStart(2, '0') + ':' + String(data.usb.min).padStart(2, '0'));
                                    if (data.cpanel.hour !== '') $('#cpanel_display').text(String(data.cpanel.hour).padStart(2, '0') + ':' + String(data.cpanel.min).padStart(2, '0'));
                                }
                            } catch(e) {
                                console.error('Failed to parse schedule');
                            }
                        });
                    }
                    
                    function loadRepConfig() {
                        $.get('backup_cron_handler.php?action=get_replicate_config', function(res) {
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    $('#rep_host').val(data.config.REMOTE_HOST);
                                    $('#rep_user').val(data.config.REMOTE_USER);
                                    $('#rep_pass').val(data.config.REMOTE_PASS);
                                    $('#rep_db').val(data.config.REMOTE_DB);

                                    $('#disp_rep_host').text(data.config.REMOTE_HOST || 'Not set');
                                    $('#disp_rep_user').text(data.config.REMOTE_USER || 'Not set');
                                    $('#disp_rep_db').text(data.config.REMOTE_DB || 'Not set');
                                }
                            } catch(e) {}
                        });
                    }

                    // Initialize
                    window.addEventListener('DOMContentLoaded', function() {
                        loadSchedules();
                        loadRepConfig();
                    });

                    function toggleEdit(type, show) {
                        if (show) {
                            $('#'+type+'_readonly_view').hide();
                            $('#'+type+'_edit_view').show();
                        } else {
                            $('#'+type+'_readonly_view').show();
                            $('#'+type+'_edit_view').hide();
                        }
                    }

                    function toggleRepEdit(show) {
                        if (show) {
                            $('#rep_readonly_view').hide();
                            $('#rep_edit_view').show();
                        } else {
                            $('#rep_readonly_view').show();
                            $('#rep_edit_view').hide();
                        }
                    }

                    function saveRepConfig(btn) {
                        var $btn = $(btn);
                        var oldText = $btn.html();
                        $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
                        $.post('backup_cron_handler.php', {
                            action: 'save_replicate_config',
                            host: $('#rep_host').val(),
                            user: $('#rep_user').val(),
                            pass: $('#rep_pass').val(),
                            db: $('#rep_db').val()
                        }, function(res) {
                            $btn.html(oldText).prop('disabled', false);
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    swal("Success", "Remote DB config saved successfully!", "success");
                                    loadRepConfig();
                                    toggleRepEdit(false);
                                } else {
                                    swal("Error", data.msg || "Failed to save config.", "error");
                                }
                            } catch(e) {
                                swal("Error", "Failed to parse server response.", "error");
                            }
                        }).fail(function() {
                            $btn.html(oldText).prop('disabled', false);
                            swal("Error", "Network error.", "error");
                        });
                    }

                    function saveSchedule(type, btn) {
                        var h = $('#'+type+'_hour').val();
                        var m = $('#'+type+'_min').val();
                        if(h === '' || m === '') {
                            swal("Wait!", "Please enter a valid hour and minute.", "warning");
                            return;
                        }
                        
                        var $btn = $(btn);
                        var oldText = $btn.html();
                        $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
                        
                        $.post('backup_cron_handler.php', {
                            action: 'save_schedule',
                            type: type,
                            hour: h,
                            min: m
                        }, function(res) {
                            $btn.html(oldText).prop('disabled', false);
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    swal("Success", "Schedule saved successfully!", "success");
                                    loadSchedules();
                                    toggleEdit(type, false);
                                } else {
                                    swal("Error", data.msg || "Failed to save schedule.", "error");
                                }
                            } catch(e) {
                                swal("Error", "Failed to parse server response.", "error");
                            }
                        }).fail(function() {
                            $btn.html(oldText).prop('disabled', false);
                            swal("Error", "Network error.", "error");
                        });
                    }

                    function runNow(type, btn) {
                        swal({
                            title: "Are you sure?",
                            text: "Do you want to forcefully trigger this background script right now?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes, run it!"
                        }).then(function() {
                            var $btn = $(btn);
                            var oldText = $btn.html();
                            $btn.html('<i class="fa fa-spinner fa-spin"></i> Triggering...').prop('disabled', true);
                            
                            $.post('backup_cron_handler.php', {
                                action: 'run_now',
                                type: type
                            }, function(res) {
                                $btn.html(oldText).prop('disabled', false);
                                swal("Triggered!", "The script has been triggered in the background. Check the live logs.", "success");
                                pollLogs();
                            }).fail(function() {
                                $btn.html(oldText).prop('disabled', false);
                                swal("Error", "Network error.", "error");
                            });
                        });
                    }

                    function runRemoteReplication(btn) {
                        swal({
                            title: "Are you sure?",
                            text: "Do you want to forcefully trigger the remote replication sequence right now?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes, run it!"
                        }).then(function() {
                            var $btn = $(btn);
                            var oldText = $btn.html();
                            $btn.html('<i class="fa fa-spinner fa-spin"></i> Replicating...').prop('disabled', true).hide();
                            $('#btn_stop_remote').show();
                            
                            var logBox = document.getElementById('cpanel_log');
                            logBox.textContent = 'Initializing connection...\n';
                            
                            var xhr = new XMLHttpRequest();
                            xhr.open('GET', 'backup_cron_handler.php?action=stream_replicate', true);
                            
                            var seenBytes = 0;
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState > 2) {
                                    var newData = xhr.responseText.substring(seenBytes);
                                    logBox.textContent += newData;
                                    seenBytes = xhr.responseText.length;
                                    // Auto-scroll to bottom
                                    logBox.scrollTop = logBox.scrollHeight;
                                }
                                
                                if (xhr.readyState === 4) {
                                    $btn.html(oldText).prop('disabled', false).show();
                                    $('#btn_stop_remote').hide();
                                    if (xhr.status === 200) {
                                        swal("Completed!", "Remote replication has finished.", "success");
                                    } else {
                                        swal("Error", "Replication failed with status: " + xhr.status, "error");
                                    }
                                }
                            };
                            xhr.send();
                        });
                    }

                    function stopLocalBackup(btn) {
                        var $btn = $(btn);
                        $btn.html('<i class="fa fa-spinner fa-spin"></i> Stopping...').prop('disabled', true);
                        $.get('backup_cron_handler.php?action=stop_local_backup', function() {
                            $btn.html('Stop Backup').prop('disabled', false).hide();
                            $('#btn_local_backup').show();
                        });
                    }

                    function stopRemoteReplication(btn) {
                        var $btn = $(btn);
                        $btn.html('<i class="fa fa-spinner fa-spin"></i> Stopping...').prop('disabled', true);
                        $.get('backup_cron_handler.php?action=stop_replication', function() {
                            $btn.html('Stop Replication').prop('disabled', false).hide();
                            $('#btn_remote_repl').show();
                        });
                    }

                    function pollLogs() {
                        if ($('#usb').hasClass('active')) {
                            $.get('backup_cron_handler.php?action=poll_log&type=usb', function(res) {
                                try {
                                    var data = typeof res === 'string' ? JSON.parse(res) : res;
                                    $('#usb_log').text(data.log);
                                } catch(e) {}
                            });
                        }
                        if ($('#remote').hasClass('active')) {
                            $.get('backup_cron_handler.php?action=poll_log&type=cpanel', function(res) {
                                try {
                                    var data = typeof res === 'string' ? JSON.parse(res) : res;
                                    $('#cpanel_log').text(data.log);
                                } catch(e) {}
                            });
                        }
                    }

                    window.addEventListener('DOMContentLoaded', function() {
                        pollLogs();
                        // Poll every 1 minute to save network and server resources
                        setInterval(pollLogs, 60000);
                        
                        // Force a poll immediately when a tab is switched
                        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                            pollLogs();
                        });
                    });

                    function checkPermissions() {
                        $.get('backup_cron_handler.php?action=check_permissions', function(res) {
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                $('#perm_debug').show().text(data.debug);
                            } catch(e) {}
                        });
                    }

                    function probeUsbBackups(btn) {
                        var $btn = $(btn);
                        var oldText = $btn.html();
                        $btn.html('<i class="fa fa-spinner fa-spin"></i> Scanning...').prop('disabled', true);
                        
                        $('#usb_backups_list').html('<p>Scanning...</p>');
                        $('#usb_backups_files').html('<p class="text-muted">Select a drive to view its backups.</p>');
                        $('#selected_usb_name').text('Drive Backups');
                        
                        $.get('backup_cron_handler.php?action=list_usb_devices', function(res) {
                            $btn.html(oldText).prop('disabled', false);
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    $('#usb_backups_list').html(data.html);
                                } else {
                                    $('#usb_backups_list').html('<p class="text-danger">' + data.msg + '</p>');
                                }
                            } catch(e) {
                                $('#usb_backups_list').html('<p class="text-danger">Failed to parse response.</p>');
                            }
                        }).fail(function() {
                            $btn.html(oldText).prop('disabled', false);
                            $('#usb_backups_list').html('<p class="text-danger">Network error.</p>');
                        });
                    }

                    function loadBackupsForDevice(path, name) {
                        $('.list-group-item').removeClass('active');
                        // Highlight the selected item safely by finding the a tag with the matching onclick text
                        // We can just rely on the user clicking it or we could use event.target in the onclick.
                        $('#selected_usb_name').text(name);
                        $('#usb_backups_files').html('<p>Loading backups...</p>');
                        $.post('backup_cron_handler.php', { action: 'list_backups_for_device', path: path }, function(res) {
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    $('#usb_backups_files').html(data.html);
                                } else {
                                    $('#usb_backups_files').html('<p class="text-danger">' + data.msg + '</p>');
                                }
                            } catch(e) {
                                $('#usb_backups_files').html('<p class="text-danger">Failed to parse response.</p>');
                            }
                        });
                    }

                    function probeRemoteDb(btn) {
                        var $btn = $(btn);
                        var oldText = $btn.html();
                        $btn.html('<i class="fa fa-spinner fa-spin"></i> Connecting...').prop('disabled', true);
                        
                        $('#remote_db_status').html('<p>Connecting to remote DB...</p>');
                        
                        $.get('backup_cron_handler.php?action=probe_remote_db', function(res) {
                            $btn.html(oldText).prop('disabled', false);
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    $('#remote_db_status').html(data.html);
                                } else {
                                    $('#remote_db_status').html('<p class="text-danger">Error: ' + data.msg + '</p>');
                                }
                            } catch(e) {
                                $('#remote_db_status').html('<p class="text-danger">Failed to parse response.</p>');
                            }
                        }).fail(function() {
                            $btn.html(oldText).prop('disabled', false);
                            $('#remote_db_status').html('<p class="text-danger">Network error.</p>');
                        });
                    }

                    function loadRemoteTableSample(table, btn) {
                        var $container = $(btn).next('.sample-container');
                        
                        // Toggle logic
                        if ($container.is(':visible')) {
                            $container.hide();
                            return;
                        }
                        
                        $container.show();
                        var $pre = $container.find('pre.sample-content');
                        
                        // If already loaded, just return
                        if ($pre.text() !== 'Loading...') return;
                        
                        $.post('backup_cron_handler.php', { action: 'probe_remote_table_sample', table: table }, function(res) {
                            try {
                                var data = typeof res === 'string' ? JSON.parse(res) : res;
                                if(data.status === 'success') {
                                    $pre.text(data.data);
                                } else {
                                    $pre.text('Error: ' + data.msg);
                                }
                            } catch(e) {
                                $pre.text('Error parsing response.');
                            }
                        }).fail(function() {
                            $pre.text('Network error.');
                        });
                    }
                </script>

                <?php include('../modal_lock.php'); ?>
                <?php include("../inc/footer_scripts.php"); ?>

                <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
                <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
                <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
                <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

                <?php include("../admin/admin_js_script.php"); ?>

                <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
                <script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
                <script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>

                <script src="../js/idle.js"></script>
</body>

</html>