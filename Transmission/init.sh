#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

path=$1

echo "Link file from : "$path

# Fix lib's
cd /lib
ln -s ld-2.15.so ld-linux-armhf.so.3 2>&1 >/dev/null

# Create links
ln -sf $path/sbin/transmission-daemon-addon /usr/sbin/transmission-daemon-addon
ln -sf $path/sbin/transmission-remote-addon /usr/sbin/transmission-remote-addon

# Webpage for WebGUI
WEBPATH="/var/www/Transmission/"
mkdir -p $WEBPATH
ln -sf $path/web/* $WEBPATH
