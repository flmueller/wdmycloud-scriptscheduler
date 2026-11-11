#!/bin/sh
# run.sh NAME TRIGGER [ARGS...]
# Runs one script from the scripts folder: no overlapping runs, output goes to
# logs/NAME.log, result goes to logs/.status/NAME.

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
. "$APP_DIR/bin/common.sh"
load_data_dir
set -f

name="$1"
trigger="${2:-manual}"
[ $# -ge 2 ] && shift 2 || shift $#

case "$name" in
	""|.*|*[!A-Za-z0-9._-]*) echo "invalid script name" >&2; exit 2 ;;
esac

ensure_data_dir
script="$SCRIPTS_DIR/$name"
log="$LOG_DIR/$name.log"
status="$STATUS_DIR/$name"

ts() { date '+%Y-%m-%d %H:%M:%S'; }

# log texts in the language of the NAS dashboard
if [ "$(ui_lang)" = de ]; then
	case "$trigger" in schedule) tname="Zeitplan" ;; manual) tname="manuell" ;; boot) tname="Systemstart" ;; *) tname="$trigger" ;; esac
	M_SKIP="Übersprungen ($tname): vorheriger Lauf ist noch aktiv"
	M_MISSING="Fehler ($tname): Skript nicht gefunden:"
	M_START="Start ($tname)"
	M_CRLF="(Hinweis: Skript hat Windows-Zeilenenden (CRLF); es wird eine bereinigte Kopie ausgeführt.)"
	M_END="Ende: Exit-Code %s, Dauer %s s"
else
	case "$trigger" in schedule) tname="schedule" ;; manual) tname="manual" ;; boot) tname="boot" ;; *) tname="$trigger" ;; esac
	M_SKIP="Skipped ($tname): previous run is still active"
	M_MISSING="Error ($tname): script not found:"
	M_START="Start ($tname)"
	M_CRLF="(Note: the script has Windows line endings (CRLF); running a cleaned copy.)"
	M_END="End: exit code %s, duration %s s"
fi

write_status() {
	printf '%s\n' "$@" > "$status.$$" && mv -f "$status.$$" "$status"
}

exec 9>"$RUN_DIR/locks/$name.lock"
if ! flock -n 9; then
	echo "===== $(ts) $M_SKIP =====" >> "$log"
	exit 0
fi

# keep logs bounded: above 1 MB keep the newest 512 KB
if [ -f "$log" ] && [ "$(wc -c < "$log")" -gt 1048576 ]; then
	tail -c 524288 "$log" > "$log.$$" && mv -f "$log.$$" "$log"
fi

start="$(date +%s)"
if [ ! -f "$script" ]; then
	echo "===== $(ts) $M_MISSING $script =====" >> "$log"
	write_status "state=done" "exit=127" "start=$start" "end=$start" "trigger=$trigger"
	exit 127
fi

interp="/bin/sh"
first="$(head -n 1 "$script" | tr -d '\r')"
case "$first" in
	'#!'*) [ -n "$(echo "${first#\#!}" | tr -d ' \t')" ] && interp="${first#\#!}" ;;
esac

echo "===== $(ts) $M_START =====" >> "$log"

runfile="$script"
if grep -q "$(printf '\r')" "$script"; then
	# Windows line endings break shell scripts: run a converted copy
	runfile="$RUN_DIR/tmp/$name"
	tr -d '\r' < "$script" > "$runfile"
	echo "$M_CRLF" >> "$log"
fi

# shellcheck disable=SC2086
( cd "$SCRIPTS_DIR" && exec $interp "$runfile" "$@" ) >> "$log" 2>&1 9>&- </dev/null &
child=$!
write_status "state=running" "pid=$child" "wrapper=$$" "start=$start" "trigger=$trigger"

wait "$child"
rc=$?
end="$(date +%s)"
# shellcheck disable=SC2059
echo "===== $(ts) $(printf "$M_END" "$rc" "$((end - start))") =====" >> "$log"
write_status "state=done" "exit=$rc" "start=$start" "end=$end" "trigger=$trigger"
[ "$runfile" != "$script" ] && rm -f "$runfile"
exit "$rc"
