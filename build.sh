#!/bin/sh
# Builds the installable .bin for the WD My Cloud (single bay, Gen2, OS 5).
# Usage: ./build.sh [model]     model defaults to WDMyCloud (see /etc/model on the NAS)
set -e
cd "$(dirname "$0")"

MODEL="${1:-WDMyCloud}"
MKSAPKG="${MKSAPKG:-../wdpksrc/mksapkg-OS5}"
VERSION="$(awk '/^Version/{print $NF}' apkg.rc)"
OUT="../packages"

[ -x "$MKSAPKG" ] || { echo "mksapkg not found: $MKSAPKG (set MKSAPKG=/path/to/mksapkg-OS5)" >&2; exit 1; }

chmod 755 ./*.sh bin/*.sh
# keep build-only files out of the package
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT
mkdir "$STAGE/scriptscheduler"
cp -a apkg.rc ./*.sh bin web "$STAGE/scriptscheduler/"
rm -f "$STAGE/scriptscheduler/build.sh"
MKSAPKG="$(cd "$(dirname "$MKSAPKG")" && pwd)/$(basename "$MKSAPKG")"
OUT="$(mkdir -p "$OUT" && cd "$OUT" && pwd)"
(cd "$STAGE/scriptscheduler" && OPENSSL_CONF="$OLDPWD/openssl-legacy.cnf" "$MKSAPKG" -E -s -m "$MODEL" > "$STAGE/mksapkg.log" 2>&1) || { cat "$STAGE/mksapkg.log" >&2; exit 1; }
if grep -qi "error" "$STAGE/mksapkg.log"; then cat "$STAGE/mksapkg.log" >&2; exit 1; fi
BIN="$(ls "$STAGE"/*scriptscheduler*.bin* | head -n 1)"
mv "$BIN" "$OUT/scriptscheduler_${VERSION}_${MODEL}.bin"
echo "$OUT/scriptscheduler_${VERSION}_${MODEL}.bin"
