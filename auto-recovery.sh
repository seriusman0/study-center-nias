#!/bin/bash
# auto-recovery.sh
# Diperbaiki 2026-10-30: tambah flock, skip saat jaringan bermasalah (000),
# retry sekali, dan soft-restart sebelum recreate container MySQL.
# Tujuan: menghentikan loop recreate container yang memicu churn veth + banjir log.

PROJECT_DIR="/var/www/study-center-nias"
URL="https://studycenter.nanoprojectdevindonesia.com/login"
LOG_FILE="${PROJECT_DIR}/storage/logs/auto-recovery.log"
LOCK_FILE="/tmp/study-center-auto-recovery.lock"

cd "$PROJECT_DIR" || exit 1

# Cegah eksekusi bertumpuk
exec 9>"$LOCK_FILE"
flock -n 9 || exit 0

check() {
  curl -s -o /dev/null -w "%{http_code}" -I "$URL" --max-time 10 2>/dev/null || echo "000"
}

healthy() {
  case "$1" in
    200|301|302) return 0 ;;
    *) return 1 ;;
  esac
}

STATUS_CODE=$(check)

# Respons kosong = gangguan jaringan/DNS sesaat, JANGAN sentuh container
if [ "$STATUS_CODE" = "000" ]; then
  echo "$(date '+%F %T') - SKIP: tidak ada respons (000), kemungkinan gangguan jaringan." >> "$LOG_FILE"
  exit 0
fi
healthy "$STATUS_CODE" && exit 0

# Beri kesempatan kedua sebelum tindakan apa pun
sleep 10
STATUS_CODE=$(check)
if [ "$STATUS_CODE" = "000" ]; then
  echo "$(date '+%F %T') - SKIP: tidak ada respons pada percobaan kedua (000)." >> "$LOG_FILE"
  exit 0
fi
healthy "$STATUS_CODE" && exit 0

echo "$(date '+%F %T') - Error: Status code $STATUS_CODE. Attempting recovery..." >> "$LOG_FILE"

# Langkah 1: restart ringan tanpa menghapus container
docker compose restart php queue >> "$LOG_FILE" 2>&1
sleep 20
STATUS_CODE=$(check)
if healthy "$STATUS_CODE"; then
  echo "$(date '+%F %T') - Recovered via soft restart (php/queue)." >> "$LOG_FILE"
  exit 0
fi

# Langkah 2: recreate MySQL hanya bila soft restart gagal
echo "$(date '+%F %T') - Soft restart gagal (status $STATUS_CODE). Recreating mysql." >> "$LOG_FILE"
docker compose rm -s -f mysql >> "$LOG_FILE" 2>&1
docker compose up -d mysql >> "$LOG_FILE" 2>&1
docker compose restart php queue >> "$LOG_FILE" 2>&1
echo "$(date '+%F %T') - Recovery executed (mysql recreated)." >> "$LOG_FILE"
