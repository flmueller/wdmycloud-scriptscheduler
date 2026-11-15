#!/bin/sh
# secure.sh [--force]
# Hands the data folder to root alone (owner root, no access for group/others).
# Without --force this only happens if the folder itself already belongs to
# root, i.e. it is the folder this app created; --force is the explicit
# decision of the admin in the web interface.
# Also moves the data back from a share (possible before version 1.3.0).

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
. "$APP_DIR/bin/common.sh"
load_data_dir

# before 1.3.0 the data folder could be put into a share
if [ -f "$CONF_FILE" ]; then
	_old="$(sed -n 's/^DATA_DIR=//p' "$CONF_FILE" | head -n 1 | tr -d '\r')"
	if [ -n "$_old" ] && [ "$_old" != "$DATA_DIR" ] && [ -f "$_old/schedule.conf" ] && [ ! -L "$_old/schedule.conf" ]; then
		if [ -e "$DATA_DIR" ]; then
			mv "$DATA_DIR" "$DATA_DIR.old-$(date +%Y%m%d%H%M%S)"
		fi
		mkdir -p "$DATA_DIR/scripts" && chmod 700 "$DATA_DIR" "$DATA_DIR/scripts"
		cp "$_old/schedule.conf" "$DATA_DIR/schedule.conf"
		for _f in "$_old"/scripts/*; do
			[ -f "$_f" ] && [ ! -L "$_f" ] && cp "$_f" "$DATA_DIR/scripts/"
		done
		apkg_log "data moved from $_old to $DATA_DIR (the old folder was left in place)"
	fi
	rm -f "$CONF_FILE"
fi

[ -d "$DATA_DIR" ] && [ ! -L "$DATA_DIR" ] || exit 0
if [ "$1" != "--force" ] && [ "$(stat -c %u "$DATA_DIR" 2>/dev/null)" != 0 ]; then
	apkg_log "$DATA_DIR does not belong to root, left untouched"
	exit 1
fi
# symbolic links are left alone: chown/chmod would act on their targets
find "$DATA_DIR" ! -type l -exec chown root:root {} \;
find "$DATA_DIR" ! -type l -exec chmod go-rwx {} \;
find "$DATA_DIR" -type d -exec chmod u+rwx {} \;
apkg_log "data folder secured"
exit 0
