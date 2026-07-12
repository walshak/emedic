#!/bin/bash

# Configuration
# Dynamically extract local credentials from Conn.php
LOCAL_USER=$(php -r '
$c = file_get_contents(__DIR__."/Connections/Conn.php");
preg_match("/mysql:.*?,\s*[\x27\"](.*?)[\x27\"],\s*[\x27\"](.*?)[\x27\"]/", $c, $m);
echo $m[1] ?? "root";
')
LOCAL_PASS=$(php -r '
$c = file_get_contents(__DIR__."/Connections/Conn.php");
preg_match("/mysql:.*?,\s*[\x27\"](.*?)[\x27\"],\s*[\x27\"](.*?)[\x27\"]/", $c, $m);
echo $m[2] ?? "";
')
LOCAL_DB=$(php -r '
$c = file_get_contents(__DIR__."/Connections/Conn.php");
preg_match("/dbname=([^;]+)/", $c, $m);
echo $m[1] ?? "webmedic";
')


REMOTE_HOST="srv561.hstgr.io"
REMOTE_USER="u129178536_test_replicate"
REMOTE_PASS="18781875Test"
REMOTE_DB="u129178536_test_replicate"

# Load overrides from config file if exists
CONFIG_FILE="$(dirname "$0")/replicate_config.env"
if [ -f "$CONFIG_FILE" ]; then
    source "$CONFIG_FILE"
fi

echo "========================================="
echo "Starting Database Sync to Hostinger cPanel"
echo "Source (Local): $LOCAL_DB"
echo "Target (Remote): $REMOTE_DB ($REMOTE_HOST)"
echo "Time: $(date)"
echo "========================================="

# Locate mysqldump
if [ -x "/opt/lampp/bin/mysqldump" ]; then
    MYSQLDUMP="/opt/lampp/bin/mysqldump"
elif command -v mysqldump >/dev/null 2>&1; then
    MYSQLDUMP="$(command -v mysqldump)"
else
    echo "❌ Error: mysqldump not found in /opt/lampp/bin or system PATH"
    exit 1
fi

# Locate mysql
if [ -x "/opt/lampp/bin/mysql" ]; then
    MYSQL="/opt/lampp/bin/mysql"
elif command -v mysql >/dev/null 2>&1; then
    MYSQL="$(command -v mysql)"
else
    echo "❌ Error: mysql not found in /opt/lampp/bin or system PATH"
    exit 1
fi

TABLES=$("$MYSQL" -u "$LOCAL_USER" -p"$LOCAL_PASS" -D "$LOCAL_DB" -e 'SHOW TABLES;' | awk '{ print $1}' | grep -v '^Tables_in')
TOTAL=$(echo "$TABLES" | wc -w)
CURRENT=0

for t in $TABLES; do
    CURRENT=$((CURRENT+1))
    echo "Replicating table \`$t\`... ($CURRENT/$TOTAL)"
    "$MYSQLDUMP" -u "$LOCAL_USER" -p"$LOCAL_PASS" \
      --add-drop-table \
      --quick \
      --single-transaction \
      --set-gtid-purged=OFF \
      "$LOCAL_DB" "$t" 2>/dev/null | \
      sed -e 's/DEFINER[ ]*=[ ]*`[^`]*`@`[^`]*`//g' | \
      "$MYSQL" -h "$REMOTE_HOST" -u "$REMOTE_USER" -p"$REMOTE_PASS" "$REMOTE_DB"
      
    PIPES=("${PIPESTATUS[@]}")
    if [ "${PIPES[0]}" -ne 0 ]; then
        echo "❌ Error: Failed to dump table \`$t\` from local database '$LOCAL_DB'."
        exit 1
    elif [ "${PIPES[2]}" -ne 0 ]; then
        echo "❌ Error: Failed to import table \`$t\` into remote database '$REMOTE_DB'."
        exit 1
    fi
done

echo "✅ Database replication completed successfully!"
