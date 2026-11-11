#!/bin/sh
# $1 = app path. Starts the scheduler daemon.
APP_DIR="${1:-$(cd "$(dirname "$0")" && pwd)}"
. "$APP_DIR/bin/common.sh"

load_data_dir
ensure_data_dir
if daemon_pid >/dev/null; then
	apkg_log "daemon already running"
	exit 0
fi
nohup /bin/sh "$APP_DIR/bin/schedulerd.sh" </dev/null >/dev/null 2>&1 &
apkg_log "daemon started"
exit 0
