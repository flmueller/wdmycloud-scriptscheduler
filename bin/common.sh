#!/bin/sh
# Shared settings for Script Scheduler. Source with APP_DIR already set.

APP_NAME="scriptscheduler"
NAS_PROG="$(dirname "$APP_DIR")"
CONF_FILE="$NAS_PROG/$APP_NAME.conf"   # only read by secure.sh (versions before 1.3.0)
DEFAULT_DATA_DIR="$NAS_PROG/${APP_NAME}_data"
RUN_DIR="/tmp/$APP_NAME"
PID_FILE="$RUN_DIR/schedulerd.pid"
DAEMON_LOG="$RUN_DIR/schedulerd.log"
APKG_LOG="/tmp/debug_apkg"

# Scripts, schedule and logs always live next to the app folder, outside every
# share, and belong to root alone: whoever can change them is root on the NAS.
load_data_dir() {
	DATA_DIR="$DEFAULT_DATA_DIR"
	SCRIPTS_DIR="$DATA_DIR/scripts"
	LOG_DIR="$DATA_DIR/logs"
	STATUS_DIR="$LOG_DIR/.status"
	SCHEDULE_FILE="$DATA_DIR/schedule.conf"
}

# Language of the NAS dashboard: "de" if it is set to German, otherwise "en".
ui_lang() {
	case "$(xmldbc -g /language 2>/dev/null)" in
		3) echo de ;;
		*) echo en ;;
	esac
}

# is_private PATH - true if PATH belongs to root and neither group nor others
# may write to it (symlinks are judged by their target).
is_private() {
	case "$(stat -L -c '%u %A' "$1" 2>/dev/null)" in
		"0 "?????-??-?) return 0 ;;
	esac
	return 1
}

# data_trusted - true if nobody but root can change the data folders and the
# schedule. Prints the first offending path otherwise.
data_trusted() {
	for _t in "$RUN_DIR" "$DATA_DIR" "$SCRIPTS_DIR" "$LOG_DIR" "$STATUS_DIR" "$SCHEDULE_FILE"; do
		[ "$_t" = "$SCHEDULE_FILE" ] && [ ! -e "$_t" ] && continue
		is_private "$_t" || { echo "$_t"; return 1; }
	done
	return 0
}

mkdir_private() {
	[ -d "$1" ] && return 0
	mkdir -p "$1" && chmod 700 "$1"
}

ensure_data_dir() {
	mkdir -p "$RUN_DIR/locks" "$RUN_DIR/tmp"
	chmod 700 "$RUN_DIR"
	mkdir_private "$DATA_DIR"
	# never create anything inside a folder somebody else controls
	is_private "$DATA_DIR" || return 1
	mkdir_private "$SCRIPTS_DIR"
	mkdir_private "$LOG_DIR"
	mkdir_private "$STATUS_DIR"
	if [ ! -e "$SCHEDULE_FILE" ]; then
		_lang="$(ui_lang)"
		_example="example.sh"
		[ "$_lang" = de ] && _example="beispiel.sh"
		sed "s/@EXAMPLE@/$_example/g" "$APP_DIR/bin/schedule.sample.$_lang" > "$SCHEDULE_FILE"
		chmod 600 "$SCHEDULE_FILE"
		if [ -z "$(ls -A "$SCRIPTS_DIR" 2>/dev/null)" ]; then
			sed "s/@EXAMPLE@/$_example/g" "$APP_DIR/bin/example.sample.$_lang" > "$SCRIPTS_DIR/$_example"
			chmod 700 "$SCRIPTS_DIR/$_example"
		fi
	fi
	return 0
}

daemon_log() {
	echo "$(date '+%Y-%m-%d %H:%M:%S') $*" >> "$DAEMON_LOG"
	# keep the in-memory log small
	if [ "$(wc -c < "$DAEMON_LOG")" -gt 65536 ]; then
		tail -c 32768 "$DAEMON_LOG" > "$DAEMON_LOG.tmp" && mv "$DAEMON_LOG.tmp" "$DAEMON_LOG"
	fi
}

apkg_log() {
	echo "$(date '+%Y-%m-%d %H:%M:%S') [$APP_NAME] [$(basename "$0")] $*" >> "$APKG_LOG"
}

daemon_pid() {
	[ -f "$PID_FILE" ] || return 1
	_p="$(cat "$PID_FILE" 2>/dev/null)"
	[ -n "$_p" ] && [ -d "/proc/$_p" ] && grep -q schedulerd "/proc/$_p/cmdline" 2>/dev/null || return 1
	echo "$_p"
}

# kill_tree PID [SIGNAL] - signal a process and all of its descendants.
# The parent is signalled first so it cannot start new children meanwhile.
kill_tree() {
	_kids="$(grep -l "^PPid:[[:space:]]*$1\$" /proc/[0-9]*/status 2>/dev/null | cut -d/ -f3)"
	kill "-${2:-TERM}" "$1" 2>/dev/null
	for _c in $_kids; do
		kill_tree "$_c" "${2:-TERM}"
	done
}
