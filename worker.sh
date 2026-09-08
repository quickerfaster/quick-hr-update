#!/bin/bash
PROJECT_DIR="/home/quickerf/quick_hr"
LOCKFILE="$PROJECT_DIR/storage/framework/queue-worker.lock"
# PHP_BIN: find the correct PHP binary for cPanel cron.
# .cpanel.yml uses 'ea-php84' as a bare command (works in deployment context),
# but cron has a minimal PATH so bare names fail with "command not found".
# We try absolute paths first (cron-safe), then fall back to bare 'php'.
if command -v /opt/cpanel/ea-php84/root/usr/bin/php &> /dev/null; then
    PHP_BIN="/opt/cpanel/ea-php84/root/usr/bin/php"
elif command -v /opt/alt/php84/usr/bin/php &> /dev/null; then
    PHP_BIN="/opt/alt/php84/usr/bin/php"
elif command -v /usr/local/bin/php &> /dev/null; then
    PHP_BIN="/usr/local/bin/php"
elif command -v /usr/bin/php &> /dev/null; then
    PHP_BIN="/usr/bin/php"
else
    # Last resort: rely on PATH (may fail in minimal cron environments)
    PHP_BIN="php"
fi
ARTISAN="$PROJECT_DIR/artisan"
LOG="$PROJECT_DIR/storage/logs/queue-worker.log"

# Rotate queue-worker.log if it exceeds 10MB
rotate_log() {
    local LOG_FILE="$1"
    local MAX_SIZE=$((10 * 1024 * 1024))  # 10MB

    if [ -f "$LOG_FILE" ]; then
        local SIZE=$(stat -f%z "$LOG_FILE" 2>/dev/null || stat -c%s "$LOG_FILE" 2>/dev/null || echo 0)
        if [ "$SIZE" -gt "$MAX_SIZE" ]; then
            mv "$LOG_FILE" "${LOG_FILE}.1"
            echo "$(date '+%Y-%m-%d %H:%M:%S') - Log rotated (previous size: $SIZE bytes)" > "$LOG_FILE"
        fi
    fi
}

# Delete rotated logs older than 30 days
find "$(dirname "$LOG")" -name "queue-worker.log.*" -mtime +30 -delete 2>/dev/null

log() { echo "$(date '+%Y-%m-%d %H:%M:%S') - $*" >> "$LOG"; }

# Rotate before writing
rotate_log "$LOG"

# Log which PHP binary and version is being used (diagnostic)
echo "$(date '+%Y-%m-%d %H:%M:%S') - Using PHP: $PHP_BIN ($($PHP_BIN -v 2>&1 | head -1))" >> "$LOG"

# Clean up stale lock file if the PID that created it no longer exists.
# This prevents a crashed worker from blocking all subsequent cron runs.
cleanup_stale_lock() {
    if [ -f "$LOCKFILE" ]; then
        # flock doesn't store PID in the file, but we can check if it's truly locked.
        # If flock -n succeeds on a test, the lock is stale.
        if flock -n "$LOCKFILE" -c "true" 2>/dev/null; then
            log "Removing stale lock file (no process holds it)"
            rm -f "$LOCKFILE"
        fi
    fi
}

cleanup_stale_lock

(
    flock -w 5 -n 200 || {
        log "Another worker is running. Exiting."
        exit 0
    }
    log "Starting queue worker (max 55 seconds)"

    # Trap EXIT so the lock is always released, even on crash
    trap 'log "Worker exiting (trap)"; exit 0' EXIT

    cd "$PROJECT_DIR"

    # --timeout=310 gives 10s buffer above job $timeout=300 (Laravel best practice).
    # This prevents the race condition where the worker kills a job before
    # its own timeout fires. Works for both payroll and UI library jobs.
    # --queue=default is now explicit (was previously implicit).
    # STDERR is captured to the log so PHP fatal errors are visible.
    $PHP_BIN "$ARTISAN" queue:work \
        --queue=default \
        --max-time=55 \
        --timeout=310 \
        --sleep=3 \
        --tries=3 \
        --quiet \
        2>>"$LOG"

    EXIT_CODE=$?
    log "Worker finished with exit code ${EXIT_CODE}"
) 200>"$LOCKFILE"

###### Add cron jobs ######
### running worker ###
# * * * * * /home/quickerf/quick_hr/worker.sh >> /dev/null 2>&1
### cpanel's 2 minutes process restriction limit  test ###
# * * * * * /usr/local/bin/php -r "sleep(120); echo 'done';" >> ~/sleep-test.log 2>&1
