#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

PID_FILE=/var/run/addon_transmission.pid
RETRY=5
while [ -e $PID_FILE -a x"$(cat $PID_FILE)" != x"" -a $RETRY -gt 0 ] ; do
	kill -SIGTERM $(cat $PID_FILE)
	RETRY=`expr $RETRY - 1`
	sleep 1
done

