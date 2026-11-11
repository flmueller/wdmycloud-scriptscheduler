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
