#!/bin/sh
# $1 = app path. Runs on every boot and after install: publish the web page.
APP_DIR="$1"
WEBDIR="/var/www/apps/scriptscheduler"

mkdir -p "$WEBDIR"
ln -sf "$APP_DIR"/web/* "$WEBDIR"/
