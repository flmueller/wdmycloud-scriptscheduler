#!/bin/sh
# $1 = app path. Uninstall (also runs during an update).
# The data folder (scripts, schedule, logs) next to the app folder is kept on
# purpose; delete it by hand if you no longer need it.
APP_DIR="$1"

[ -x "$APP_DIR/stop.sh" ] && "$APP_DIR/stop.sh" "$APP_DIR"
rm -rf /var/www/apps/scriptscheduler
case "$APP_DIR" in
	*/scriptscheduler) rm -rf "$APP_DIR" ;;
esac
exit 0
