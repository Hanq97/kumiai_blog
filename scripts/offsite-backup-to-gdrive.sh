#!/usr/bin/env bash
# Push ./backups to a configured rclone remote (defaults to "gdrive:kumiai-backups").
# Designed to run on the production VPS, scheduled by cron a little after the in-container
# mysqldump (which fires at 03:00 UTC). Independent of where the remote actually lives —
# point RCLONE_REMOTE at any rclone-supported backend (Google Drive, Backblaze B2,
# Dropbox, OneDrive, S3, …) and the script is the same.
#
# One-time prerequisites on the VPS:
#   1. Install rclone:
#        curl https://rclone.org/install.sh | sudo bash
#   2. Configure the remote (interactive — opens an OAuth URL for Drive/Dropbox/etc.):
#        rclone config
#      Create a remote named "gdrive" (or whatever you set RCLONE_REMOTE to).
#   3. Smoke test:
#        rclone lsf gdrive:
#
# Schedule daily at 04:30 UTC (90 min after the in-container backup) — as root:
#   crontab -e
#   30 4 * * * /opt/kumiai-wp/scripts/offsite-backup-to-gdrive.sh >> /var/log/kumiai-offsite.log 2>&1
#
# Env overrides:
#   RCLONE_REMOTE       rclone remote name              (default: gdrive)
#   RCLONE_REMOTE_PATH  folder inside the remote        (default: kumiai-backups)
#   LOCAL_BACKUP_DIR    where the mysqldump files live  (default: ../backups relative to script)
#   REMOTE_MAX_AGE      delete remote dumps older than  (default: 30d)

set -euo pipefail

REMOTE="${RCLONE_REMOTE:-gdrive}"
REMOTE_PATH="${RCLONE_REMOTE_PATH:-kumiai-backups}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOCAL_BACKUP_DIR="${LOCAL_BACKUP_DIR:-$SCRIPT_DIR/../backups}"
REMOTE_MAX_AGE="${REMOTE_MAX_AGE:-30d}"

ts() { date -u +%FT%TZ; }
log() { echo "[$(ts)] $*"; }

if ! command -v rclone >/dev/null 2>&1; then
    echo "rclone not installed. Install: curl https://rclone.org/install.sh | sudo bash" >&2
    exit 1
fi

if ! rclone listremotes | grep -qx "${REMOTE}:"; then
    echo "rclone remote '${REMOTE}:' not configured. Run: rclone config" >&2
    exit 1
fi

if [[ ! -d "$LOCAL_BACKUP_DIR" ]]; then
    echo "Local backup directory not found: $LOCAL_BACKUP_DIR" >&2
    exit 1
fi

if ! ls "$LOCAL_BACKUP_DIR"/*.sql.gz >/dev/null 2>&1; then
    log "No *.sql.gz files in $LOCAL_BACKUP_DIR — has the backup service produced anything yet?"
    exit 0
fi

log "Uploading new dumps from $LOCAL_BACKUP_DIR to ${REMOTE}:${REMOTE_PATH}/"
# copy (not sync) so files removed locally by the 7-day retention stay on the remote longer.
rclone copy \
    "$LOCAL_BACKUP_DIR" \
    "${REMOTE}:${REMOTE_PATH}/" \
    --include "*.sql.gz" \
    --transfers 2 \
    --checkers 4 \
    --quiet

log "Trimming remote dumps older than $REMOTE_MAX_AGE"
rclone delete \
    "${REMOTE}:${REMOTE_PATH}/" \
    --min-age "$REMOTE_MAX_AGE" \
    --include "*.sql.gz" \
    --quiet || log "WARNING: remote cleanup returned non-zero (likely no eligible files yet) — continuing."

# Summary
remote_count=$(rclone lsf "${REMOTE}:${REMOTE_PATH}/" --include "*.sql.gz" 2>/dev/null | wc -l)
log "Done. Remote currently holds ${remote_count} dump(s)."
