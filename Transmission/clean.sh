#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

WEBPATH="/var/www/Transmission"

#remove bin

#remove lib

#remove web
rm -rf $WEBPATH
