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

# dispatch TRIGGER AWK-ARGS...
dispatch() {
	_trigger="$1"; shift
	load_data_dir
	[ -f "$SCHEDULE_FILE" ] || return 0
	awk -f "$APP_DIR/bin/cron.awk" "$@" "$SCHEDULE_FILE" | while IFS="$TAB" read -r _script _args; do
		daemon_log "starting $_script ($_trigger)"
		# shellcheck disable=SC2086
		"$APP_DIR/bin/run.sh" "$_script" "$_trigger" $_args </dev/null >/dev/null 2>&1 &
	done
}

daemon_log "daemon started (PID $$)"

# @boot entries: once per boot, and only if the NAS really just came up
# (not when the app is merely re-enabled or updated later).
if [ ! -f "$RUN_DIR/boot.done" ]; then
	touch "$RUN_DIR/boot.done"
	_up="$(cut -d. -f1 /proc/uptime)"
	if [ "${_up:-99999}" -lt 900 ]; then
		dispatch boot -v mode=boot
	fi
fi

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
	dispatch schedule -v mode=match -v "m=$2" -v "h=$3" -v "dom=$4" -v "mon=$5" -v "dow=$6"
done
