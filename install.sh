#!/bin/sh
# $1 = unpacked package, $2 = install root (Nas_Prog)
SRC="$1"
DEST="$2/scriptscheduler"

rm -rf "$DEST"
mv -f "$SRC" "$DEST" || cp -R "$SRC" "$DEST"
# the app code runs as root: make sure only root can change it
chown -R root:root "$DEST"
chmod -R go-w "$DEST"
chmod 755 "$DEST"/*.sh "$DEST"/bin/*.sh
# scripts, schedule and logs: root only (also converts folders of older versions)
/bin/sh "$DEST/bin/secure.sh" >/dev/null 2>&1
exit 0
