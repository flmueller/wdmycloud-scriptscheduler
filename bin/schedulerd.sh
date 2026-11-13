#!/bin/sh
# Script Scheduler daemon: wakes up every full minute and starts the scripts
# that are due according to schedule.conf. Independent of the system crond.

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
. "$APP_DIR/bin/common.sh"

set -f   # no filename expansion when splitting script arguments
TAB="$(printf '\t')"

mkdir -p "$RUN_DIR"
if _other="$(daemon_pid)" && [ "$_other" != "$$" ]; then
	exit 0
fi
echo $$ > "$PID_FILE"
trap 'daemon_log "daemon stopped"; [ "$(cat "$PID_FILE" 2>/dev/null)" = "$$" ] && rm -f "$PID_FILE"; exit 0' TERM INT

# dispatch AWK-ARGS...
dispatch() {
	load_data_dir
	[ -f "$SCHEDULE_FILE" ] || return 0
	awk -f "$APP_DIR/bin/cron.awk" "$@" "$SCHEDULE_FILE" | while IFS="$TAB" read -r _trigger _script _args; do
		daemon_log "starting $_script ($_trigger)"
		# shellcheck disable=SC2086
		"$APP_DIR/bin/run.sh" "$_script" "$_trigger" $_args </dev/null >/dev/null 2>&1 &
	done
}

daemon_log "daemon started (PID $$)"

# @boot / @boot+N entries run once per boot, N minutes after the NAS came up.
# $BOOT_FILE (in RAM, gone after a reboot) holds the uptime in seconds up to
# which they have been handled, so every entry is started exactly once even
# if the daemon is restarted in between.
BOOT_FILE="$RUN_DIR/boot.done"
uptime_s() { cut -d. -f1 /proc/uptime; }

_up="$(uptime_s)"
if [ ! -f "$BOOT_FILE" ]; then
	# nothing handled yet: start from zero if the NAS really just came up,
	# not when the app is installed or enabled for the first time much later
	if [ "$_up" -lt 900 ]; then _boot_last=-1; else _boot_last="$_up"; fi
else
	_boot_last="$(cat "$BOOT_FILE" 2>/dev/null)"
	case "$_boot_last" in
		''|*[!0-9-]*) _boot_last="$_up" ;;
	esac
	# daemon was off for a while (app disabled): don't run missed entries late
	[ $((_up - _boot_last)) -gt 180 ] && _boot_last="$_up"
fi
dispatch -v mode=boot -v "from=$_boot_last" -v "to=$_up"
_boot_last="$_up"
echo "$_boot_last" > "$BOOT_FILE"

_last=""
while :; do
	_now="$(date +%s)"
	sleep $((60 - _now % 60))
	# shellcheck disable=SC2046
	set -- $(date '+%Y%m%d%H%M %M %H %d %m %w')
	if [ "$1" = "$_last" ]; then
		sleep 1
		continue
	fi
	_last="$1"
	_up="$(uptime_s)"
	dispatch -v mode=match -v "m=$2" -v "h=$3" -v "dom=$4" -v "mon=$5" -v "dow=$6" -v "from=$_boot_last" -v "to=$_up"
	_boot_last="$_up"
	echo "$_boot_last" > "$BOOT_FILE"
done
