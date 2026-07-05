#!/bin/bash

# Configuration
LOCAL_USER="root"
LOCAL_PASS="18781875"
LOCAL_DB="webmedic"

REMOTE_HOST="srv561.hstgr.io"
REMOTE_USER="u129178536_test_replicate"
REMOTE_PASS="18781875Test"
REMOTE_DB="u129178536_test_replicate"

echo "========================================="
echo "Starting Database Sync to Hostinger cPanel"
echo "Source (Local): $LOCAL_DB"
echo "Target (Remote): $REMOTE_DB ($REMOTE_HOST)"
echo "Time: $(date)"
echo "========================================="

# Dump local and import to remote
# --single-transaction: avoids locking tables during dump (useful for InnoDB)
# --quick: streams the dump instead of buffering it in memory
mysqldump -u "$LOCAL_USER" -p"$LOCAL_PASS" \
  --add-drop-table \
  --quick \
  --single-transaction \
  "$LOCAL_DB" | \
mysql -h "$REMOTE_HOST" -u "$REMOTE_USER" -p"$REMOTE_PASS" "$REMOTE_DB"

if [ ${PIPESTATUS[0]} -ne 0 ]; then
    echo "❌ Error: Failed to dump local database '$LOCAL_DB'."
    exit 1
elif [ ${PIPESTATUS[1]} -ne 0 ]; then
    echo "❌ Error: Failed to import dump into remote database '$REMOTE_DB'."
    exit 1
else
    echo "✅ Database replication completed successfully!"
fi
