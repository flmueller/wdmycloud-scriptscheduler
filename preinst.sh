#!/bin/sh
# Called before a re-install/update. $1 = current app path.
# Scripts, schedule and logs live outside the app folder, so nothing to back up.
[ -x "$1/stop.sh" ] && "$1/stop.sh" "$1"
exit 0
