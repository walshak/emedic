<?php
ob_start();
session_start();

function send_json($data) {
    if (ob_get_level() > 0) ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['username'])) {
    send_json(['status' => 'error', 'msg' => 'Unauthorized']);
}
// Release session lock to prevent blocking other concurrent AJAX requests (e.g. polling vs remote DB connect)
session_write_close();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$root_dir = dirname(__DIR__);

// Helper to get crontab
function get_crontab() {
    $output = [];
    $return_var = 0;
    exec("crontab -l 2>/dev/null", $output, $return_var);
    return $output;
}

// Helper to write crontab
function write_crontab($lines) {
    $tmp_file = tempnam(sys_get_temp_dir(), 'cron');
    file_put_contents($tmp_file, implode("\n", $lines) . "\n");
    exec("crontab $tmp_file 2>&1", $output, $return_var);
    unlink($tmp_file);
    return ['status' => $return_var === 0, 'output' => implode("\n", $output)];
}

if ($action === 'get_schedule') {
    $crontab = get_crontab();
    
    $usb_hour = ''; $usb_min = '';
    $cpanel_hour = ''; $cpanel_min = '';
    
    foreach ($crontab as $line) {
        if (strpos($line, 'backup_to_usb.sh') !== false) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 5) {
                $usb_min = $parts[0];
                $usb_hour = $parts[1];
            }
        }
        if (strpos($line, 'replicate_to_cpanel.sh') !== false) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 5) {
                $cpanel_min = $parts[0];
                $cpanel_hour = $parts[1];
            }
        }
    }
    
    send_json([
        'status' => 'success',
        'usb' => ['hour' => $usb_hour, 'min' => $usb_min],
        'cpanel' => ['hour' => $cpanel_hour, 'min' => $cpanel_min]
    ]);
}

if ($action === 'save_schedule') {
    $type = $_POST['type'];
    $hour = $_POST['hour'];
    $min = $_POST['min'];
    
    // Validate
    if ($hour === '' || $min === '') {
        send_json(['status' => 'error', 'msg' => 'Invalid time']);
    }

    $crontab = get_crontab();
    $new_crontab = [];
    
    $script_name = ($type === 'usb') ? 'backup_to_usb.sh' : 'replicate_to_cpanel.sh';
    $found = false;
    
    // Commands to run using __DIR__ equivalent ($root_dir)
    $usb_cmd = "$root_dir/backup_to_usb.sh";
    $cpanel_cmd = "cd $root_dir && ./replicate_to_cpanel.sh > ~/replicate_cron.log 2>&1";
    
    $cmd_to_insert = ($type === 'usb') ? "$min $hour * * * $usb_cmd" : "$min $hour * * * $cpanel_cmd";

    foreach ($crontab as $line) {
        if (strpos($line, $script_name) !== false) {
            $new_crontab[] = $cmd_to_insert;
            $found = true;
        } else {
            $new_crontab[] = $line;
        }
    }
    
    if (!$found) {
        $new_crontab[] = $cmd_to_insert;
    }
    
    $result = write_crontab($new_crontab);
    
    if ($result['status']) {
        send_json(['status' => 'success']);
    } else {
        send_json(['status' => 'error', 'msg' => 'Failed to save crontab. ' . $result['output']]);
    }
}

if ($action === 'run_now') {
    $type = $_POST['type'];
    if ($type === 'usb') {
        $cmd = "bash -c 'nohup $root_dir/backup_to_usb.sh > /dev/null 2>&1 &'";
        exec($cmd);
    } else if ($type === 'cpanel') {
        $cmd = "bash -c 'cd $root_dir && nohup ./replicate_to_cpanel.sh > ~/replicate_cron.log 2>&1 &'";
        exec($cmd);
    }
    send_json(['status' => 'success']);
}

if ($action === 'stream_replicate') {
    while (ob_get_level() > 0) ob_end_flush();
    $cmd = "cd " . escapeshellarg($root_dir) . " && ./replicate_to_cpanel.sh 2>&1 | tee ~/replicate_cron.log";
    $handle = popen($cmd, 'r');
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fread($handle, 1024);
            echo $buffer;
            flush();
        }
        pclose($handle);
    }
    exit;
}

if ($action === 'stop_replication') {
    exec("pkill -f replicate_to_cpanel.sh");
    send_json(['status' => 'success']);
}

if ($action === 'stop_local_backup') {
    file_put_contents($root_dir . '/backup/stop_backup.flag', 'stop');
    send_json(['status' => 'success']);
}

if ($action === 'poll_log') {
    $type = $_GET['type'];
    $log_file = ($type === 'usb') ? '~/webmedic_mysql_backup.log' : '~/replicate_cron.log';
    
    // We use bash to expand ~ properly
    $output = shell_exec("bash -c 'tail -n 100 $log_file 2>/dev/null'");
    
    if (!$output) {
        $output = "Log file is empty or does not exist.";
    }
    
    send_json(['status' => 'success', 'log' => $output]);
}

if ($action === 'check_permissions') {
    $current_user = exec('whoami');
    // Try to execute crontab -l to check if user has access to crontab
    exec("crontab -l 2>&1", $out1, $ret_l);
    $crontab_output = implode("\n", $out1);
    
    $debug_info = [];
    $debug_info[] = "System Probing Results:";
    $debug_info[] = "-----------------------";
    $debug_info[] = "PHP/Web Server User : $current_user";
    
    if ($ret_l !== 0) {
        $debug_info[] = "Crontab Access      : DENIED OR UNAVAILABLE";
        $debug_info[] = "Error Output        : $crontab_output";
        $debug_info[] = "";
        $debug_info[] = "== Troubleshooting Commands ==";
        $debug_info[] = "1. To allow this user to create crontabs (if restricted):";
        $debug_info[] = "   sudo bash -c 'echo $current_user >> /etc/cron.allow'";
        $debug_info[] = "";
        $debug_info[] = "2. Alternatively, set up the jobs manually using the web server user:";
        $debug_info[] = "   sudo crontab -u $current_user -e";
        $debug_info[] = "";
        $debug_info[] = "3. If you must run as another user (e.g., 'root'), add to root's crontab:";
        $debug_info[] = "   sudo crontab -e";
    } else {
        $debug_info[] = "Crontab Access      : OK (User can edit crontab)";
        $debug_info[] = "If changes aren't saving, verify the web folder has proper execute permissions.";
    }
    
    send_json(['status' => 'success', 'debug' => implode("\n", $debug_info)]);
}

if ($action === 'get_replicate_config') {
    $script_file = $root_dir . '/replicate_to_cpanel.sh';
    $env_file = $root_dir . '/replicate_config.env';
    $config = [
        'REMOTE_HOST' => '',
        'REMOTE_USER' => '',
        'REMOTE_PASS' => '',
        'REMOTE_DB' => ''
    ];
    
    // First read defaults from the bash script
    if (file_exists($script_file)) {
        $lines = file($script_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, 'REMOTE_') === 0) {
                list($key, $val) = explode('=', $line, 2);
                $config[trim($key)] = trim($val, ' "');
            }
        }
    }

    // Then override with .env file
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $val) = explode('=', $line, 2);
                $config[trim($key)] = trim($val, ' "');
            }
        }
    }
    send_json(['status' => 'success', 'config' => $config]);
}

if ($action === 'save_replicate_config') {
    $host = isset($_POST['host']) ? $_POST['host'] : '';
    $user = isset($_POST['user']) ? $_POST['user'] : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';
    $db = isset($_POST['db']) ? $_POST['db'] : '';
    
    $env_file = $root_dir . '/replicate_config.env';
    $content = "REMOTE_HOST=\"$host\"\n";
    $content .= "REMOTE_USER=\"$user\"\n";
    $content .= "REMOTE_PASS=\"$pass\"\n";
    $content .= "REMOTE_DB=\"$db\"\n";
    
    file_put_contents($env_file, $content);
    send_json(['status' => 'success']);
}

if ($action === 'list_usb_devices') {
    $devices = [];
    
    // Check local ~/backups
    $local_dir = trim(shell_exec("bash -c 'echo ~/backups'"));
    if (is_dir($local_dir)) {
        $devices[] = [
            'id' => 'local',
            'name' => 'Local Backups (~/backups)',
            'path' => $local_dir
        ];
    }

    // Check USB mounts
    $mounts = [];
    exec("lsblk -o MOUNTPOINT | grep '^/media\|^/mnt'", $mounts);
    foreach ($mounts as $index => $mount) {
        $mount = trim($mount);
        if (is_dir($mount)) {
            $devices[] = [
                'id' => 'usb_' . $index,
                'name' => 'USB Drive (' . $mount . ')',
                'path' => $mount . '/webmedic_mysql_backups'
            ];
        }
    }

    $html = '';
    if (count($devices) > 0) {
        $html .= '<div class="list-group">';
        foreach ($devices as $dev) {
            $path_safe = htmlspecialchars($dev['path'], ENT_QUOTES, 'UTF-8');
            $html .= '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="loadBackupsForDevice(\'' . $path_safe . '\', \'' . htmlspecialchars($dev['name'], ENT_QUOTES, 'UTF-8') . '\')">';
            $html .= '<i class="fa ' . ($dev['id'] === 'local' ? 'fa-hdd-o' : 'fa-usb') . '"></i> <strong>' . htmlspecialchars($dev['name']) . '</strong>';
            $html .= '<p class="text-muted" style="margin-bottom:0; font-size: 11px;">Path: ' . $path_safe . '</p>';
            $html .= '</a>';
        }
        $html .= '</div>';
    } else {
        $html = '<p class="text-warning">No backup locations found.</p>';
    }

    send_json(['status' => 'success', 'html' => $html]);
}

if ($action === 'list_backups_for_device') {
    $path = isset($_POST['path']) ? $_POST['path'] : '';
    
    // Security check: ensure path is either ~/backups or under /media or /mnt
    $local_dir = trim(shell_exec("bash -c 'echo ~/backups'"));
    
    $is_valid = false;
    if ($path === $local_dir) {
        $is_valid = true;
    } else if (strpos($path, '/media/') === 0 || strpos($path, '/mnt/') === 0) {
        // Ensure there are no path traversal tricks in the requested path
        if (strpos($path, '..') === false) {
            $is_valid = true;
        }
    }

    if (!$is_valid) {
        send_json(['status' => 'error', 'msg' => 'Invalid or unauthorized path.']);
    }

    $html = '';
    if (is_dir($path)) {
        $files = glob($path . '/backup_*.sql');
        if ($files && count($files) > 0) {
            $html .= '<ul class="list-group">';
            // Sort files by modified time descending (newest first)
            usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
            
            foreach ($files as $file) {
                // Strict basename() usage to render safely
                $filename = htmlspecialchars(basename($file));
                $size = round(filesize($file)/1024/1024, 2);
                $date = date("Y-m-d H:i:s", filemtime($file));
                
                $html .= '<li class="list-group-item">';
                $html .= '<i class="fa fa-file-code-o"></i> ' . $filename;
                $html .= '<span class="pull-right text-muted" style="font-size: 11px;">' . $size . ' MB | ' . $date . '</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html = '<p class="text-warning">No SQL backup files found in this directory.</p>';
        }
    } else {
        $html = '<p class="text-danger">Directory does not exist. A backup may not have run yet.</p>';
    }

    send_json(['status' => 'success', 'html' => $html]);
}

if ($action === 'probe_remote_db') {
    $script_file = $root_dir . '/replicate_to_cpanel.sh';
    $env_file = $root_dir . '/replicate_config.env';
    $config = [
        'REMOTE_HOST' => '',
        'REMOTE_USER' => '',
        'REMOTE_PASS' => '',
        'REMOTE_DB' => ''
    ];
    if (file_exists($script_file)) {
        $lines = file($script_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, 'REMOTE_') === 0) {
                list($key, $val) = explode('=', $line, 2);
                $config[trim($key)] = trim($val, ' "');
            }
        }
    }
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $val) = explode('=', $line, 2);
                $config[trim($key)] = trim($val, ' "');
            }
        }
    }

    if (empty($config['REMOTE_HOST']) || empty($config['REMOTE_DB'])) {
        send_json(['status' => 'error', 'msg' => 'Remote DB not fully configured.']);
    }

    try {
        $pdo = new PDO("mysql:host=" . $config['REMOTE_HOST'] . ";dbname=" . $config['REMOTE_DB'], $config['REMOTE_USER'], $config['REMOTE_PASS'], array(
            PDO::ATTR_TIMEOUT => 3, // 3 seconds timeout so it doesnt hang forever
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ));
        
        $html = '<div class="alert alert-success">Successfully connected to ' . htmlspecialchars($config['REMOTE_HOST']) . '</div>';
        
        $stmt = $pdo->query("SHOW TABLE STATUS");
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($tables) > 0) {
            $total_size_mb = 0;
            $total_rows = 0;
            $total_tables = count($tables);

            foreach ($tables as $t) {
                $total_size_mb += ($t['Data_length'] + $t['Index_length']) / 1024 / 1024;
                $total_rows += $t['Rows'];
            }

            // Summary stats
            $html .= '<div class="row text-center" style="margin-bottom: 15px;">';
            $html .= '<div class="col-xs-4"><h3 style="margin: 0;">' . $total_tables . '</h3><small class="text-muted">Total Tables</small></div>';
            $html .= '<div class="col-xs-4"><h3 style="margin: 0;">' . number_format($total_rows) . '</h3><small class="text-muted">Total Rows</small></div>';
            $html .= '<div class="col-xs-4"><h3 style="margin: 0;">' . round($total_size_mb, 2) . ' MB</h3><small class="text-muted">Total Size</small></div>';
            $html .= '</div>';

            $html .= '<div style="max-height: 400px; overflow: auto;">';
            $html .= '<table class="table table-bordered table-striped" style="font-size: 12px; background: white;">';
            $html .= '<thead><tr><th>Table Name</th><th>Rows</th><th>Data Size</th><th>Update Time</th><th>Sample Data</th></tr></thead><tbody>';
            foreach ($tables as $t) {
                $size_mb = round(($t['Data_length'] + $t['Index_length']) / 1024 / 1024, 2);
                $update_time = $t['Update_time'] ? htmlspecialchars($t['Update_time']) : '<span class="text-muted">N/A (InnoDB)</span>';
                
                $html .= '<tr>';
                $html .= '<td><strong>' . htmlspecialchars($t['Name']) . '</strong></td>';
                $html .= '<td>' . number_format((int)$t['Rows']) . '</td>';
                $html .= '<td>' . $size_mb . ' MB</td>';
                $html .= '<td>' . $update_time . '</td>';
                
                // Fetch sample data (Lazy loaded)
                $safe_table = htmlspecialchars($t['Name'], ENT_QUOTES, 'UTF-8');
                $sample_html = '<button class="btn btn-xs btn-default" onclick="loadRemoteTableSample(\'' . $safe_table . '\', this)">View (10)</button><div class="sample-container" style="display:none; margin-top:5px; max-height:200px; overflow:auto;"><pre class="sample-content" style="font-size:10px;">Loading...</pre></div>';
                
                $html .= '<td>' . $sample_html . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
        } else {
            $html .= '<p>Connected, but database is empty.</p>';
        }

        send_json(['status' => 'success', 'html' => $html]);
    } catch(PDOException $e) {
        send_json(['status' => 'error', 'msg' => 'Connection failed: ' . $e->getMessage()]);
    }
}

if ($action === 'probe_remote_table_sample') {
    $table = isset($_POST['table']) ? $_POST['table'] : '';
    if (empty($table)) send_json(['status' => 'error', 'msg' => 'No table provided.']);

    // Load remote DB config
    $script_file = $root_dir . '/replicate_to_cpanel.sh';
    $env_file = $root_dir . '/replicate_config.env';
    $config = [
        'REMOTE_HOST' => '',
        'REMOTE_USER' => '',
        'REMOTE_PASS' => '',
        'REMOTE_DB' => ''
    ];
    if (file_exists($script_file)) {
        $lines = file($script_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, 'REMOTE_') === 0) {
                list($key, $val) = explode('=', $line, 2);
                $config[trim($key)] = trim($val, ' "');
            }
        }
    }
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                list($key, $val) = explode('=', $line, 2);
                $config[trim($key)] = trim($val, ' "');
            }
        }
    }

    if (empty($config['REMOTE_HOST']) || empty($config['REMOTE_DB'])) {
        send_json(['status' => 'error', 'msg' => 'Remote DB not fully configured.']);
    }

    try {
        $pdo = new PDO("mysql:host=" . $config['REMOTE_HOST'] . ";dbname=" . $config['REMOTE_DB'], $config['REMOTE_USER'], $config['REMOTE_PASS'], array(
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ));
        
        $sample = $pdo->query("SELECT * FROM `" . str_replace('`', '', $table) . "` ORDER BY 1 DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        if(empty($sample)){
            send_json(['status' => 'success', 'data' => 'No data in table.']);
        } else {
            send_json(['status' => 'success', 'data' => json_encode($sample, JSON_PRETTY_PRINT)]);
        }
    } catch(PDOException $e) {
        send_json(['status' => 'error', 'msg' => 'Query failed: ' . $e->getMessage()]);
    }
}
