#!/bin/bash

# Configuration
PROJECT_DIR="/var/www/study-center-nias"
URL="https://studycenter.nanoprojectdevindonesia.com/login"
LOG_FILE="${PROJECT_DIR}/storage/logs/auto-recovery.log"

cd $PROJECT_DIR

# Check HTTP status code (timeout 10 seconds)
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" -I "$URL" --max-time 10)

# If status code is not 200 or 302 (redirect), consider it failed
if [ "$STATUS_CODE" != "200" ] && [ "$STATUS_CODE" != "302" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Error: Status code $STATUS_CODE. Attempting recovery..." >> $LOG_FILE
    
    # Force recreate the mysql container (fixes issue where it loses network attachment)
    docker compose rm -s -f mysql >> $LOG_FILE 2>&1
    docker compose up -d mysql >> $LOG_FILE 2>&1
    
    # Restart php and queue to reconnect to the newly created mysql container
    docker compose restart php queue >> $LOG_FILE 2>&1
    
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Recovery executed." >> $LOG_FILE
fi
