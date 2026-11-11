#!/bin/sh
# stop-job.sh NAME - terminate a running script (and everything it started)

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
. "$APP_DIR/bin/common.sh"
load_data_dir

name="$1"
case "$name" in
	""|.*|*[!A-Za-z0-9._-]*) exit 2 ;;
esac

status="$STATUS_DIR/$name"
[ -f "$status" ] || exit 1
grep -q '^state=running$' "$status" || exit 1
pid="$(sed -n 's/^pid=//p' "$status")"
wrapper="$(sed -n 's/^wrapper=//p' "$status")"
case "$pid$wrapper" in ""|*[!0-9]*) exit 1 ;; esac

# only act if the run.sh wrapper for this job is really still alive (guards against PID reuse)
grep -q "run.sh" "/proc/$wrapper/cmdline" 2>/dev/null || exit 1
[ "$(sed -n 's/^PPid:[[:space:]]*//p' "/proc/$pid/status" 2>/dev/null)" = "$wrapper" ] || exit 1

M_STOP="Stop requested"
[ "$(ui_lang)" = de ] && M_STOP="Abbruch angefordert"
echo "===== $(date '+%Y-%m-%d %H:%M:%S') $M_STOP =====" >> "$LOG_DIR/$name.log"
kill_tree "$pid" TERM
i=0
while [ -d "/proc/$pid" ] && [ $i -lt 10 ]; do sleep 1; i=$((i + 1)); done
[ -d "/proc/$pid" ] && kill_tree "$pid" KILL
exit 0
