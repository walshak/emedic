#!/bin/bash

# Generate ~/.my.cnf automatically from Conn.php
php -r '
$connFile = __DIR__."/Connections/Conn.php";
if(file_exists($connFile)){
    $content = file_get_contents($connFile);
    if(preg_match("/mysql:host=([^;]+);dbname=([^;]+).*?,\s*[\x27\"](.*?)[\x27\"],\s*[\x27\"](.*?)[\x27\"]/", $content, $matches)){
        $host = $matches[1];
        $user = $matches[3];
        $pass = $matches[4];
        $cnf = "[client]\nuser=\"$user\"\npassword=\"$pass\"\nhost=\"$host\"\n";
        $cnf_file = getenv("HOME") ? getenv("HOME")."/.my.cnf" : "/root/.my.cnf";
        file_put_contents($cnf_file, $cnf);
        chmod($cnf_file, 0600);
    }
}
'

# Config
MYSQL_DEFAULTS_FILE=~/.my.cnf
DB_NAME=webmedic
BACKUP_DIR=~/backups
LOG_FILE=~/webmedic_mysql_backup.log
RETENTION_DAYS=7

# Clear previous log
> "$LOG_FILE"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Locate mysqldump
if [ -x "/opt/lampp/bin/mysqldump" ]; then
    MYSQLDUMP="/opt/lampp/bin/mysqldump"
elif command -v mysqldump >/dev/null 2>&1; then
    MYSQLDUMP="$(command -v mysqldump)"
else
    echo "$(date): Error - mysqldump not found in /opt/lampp/bin or system PATH" >> "$LOG_FILE"
    exit 1
fi

# Get yesterday's date
YESTERDAY=$(date -d "yesterday" +%Y%m%d)

# Main backup function
create_backup() {
    local dest_dir=$1
    local backup_file="backup_${YESTERDAY}.sql"
    
    "$MYSQLDUMP" --defaults-file="$MYSQL_DEFAULTS_FILE" "$DB_NAME" > "${dest_dir}/${backup_file}" 2>> "$LOG_FILE"
    
    if [ $? -eq 0 ]; then
        echo "$(date): Backup created successfully in ${dest_dir}" >> "$LOG_FILE"
        # Remove backups older than retention period
        find "$dest_dir" -name "backup_*.sql" -mtime +$RETENTION_DAYS -delete
    else
        echo "$(date): Backup failed for ${dest_dir}" >> "$LOG_FILE"
        exit 1
    fi
}

# Create primary backup
create_backup "$BACKUP_DIR"

# Backup to USB devices
echo "$(date): Starting USB device scan" >> "$LOG_FILE"
devices=$(lsblk -o MOUNTPOINT | grep "^/media\|^/mnt")

if [ -z "$devices" ]; then
    echo "$(date): No USB devices found" >> "$LOG_FILE"
else
    echo "$(date): Found mounted devices: $devices" >> "$LOG_FILE"
    
    while IFS= read -r device; do
        echo "$(date): Processing device: $device" >> "$LOG_FILE"
        
        if [ ! -d "$device" ]; then
            echo "$(date): Error - $device is not a directory" >> "$LOG_FILE"
            continue
        fi
        
        if [ ! -w "$device" ]; then
            echo "$(date): Error - $device is not writable" >> "$LOG_FILE"
            continue
        fi
        
        backup_usb_dir="${device}/webmedic_mysql_backups"
        echo "$(date): Creating backup directory: $backup_usb_dir" >> "$LOG_FILE"
        
        if ! mkdir -p "$backup_usb_dir"; then
            echo "$(date): Failed to create directory $backup_usb_dir" >> "$LOG_FILE"
            continue
        fi
        
        create_backup "$backup_usb_dir"
    done <<< "$devices"
fi