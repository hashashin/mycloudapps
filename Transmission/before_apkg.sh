#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

# DO NOT REMOVE!!
# This is for APKG Transmission backward compatibility(version 1.00).
# backup config files SHOULD BE wrote into preinst.sh, and this JUST for APKG Transmission backward compatibility.
APKG_MODULE="Transmission"

APKG_PATH=""

