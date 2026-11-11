#!/bin/sh
# $1 = app path. Stops the scheduler daemon. Scripts that are running finish normally.
APP_DIR="${1:-$(cd "$(dirname "$0")" && pwd)}"
. "$APP_DIR/bin/common.sh"

if _pid="$(daemon_pid)"; then
	kill_tree "$_pid" TERM
	apkg_log "daemon stopped"
fi
rm -f "$PID_FILE"
exit 0
