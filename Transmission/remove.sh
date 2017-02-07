#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

path=$1

PID=`ps | grep transmission-daemon-addon | grep "port 9092" | awk '{ print $1 }'`
while [ -n "$PID" ] ; do
	kill -SIGTERM $PID
	sleep 1
	PID=`ps | grep transmission-daemon-addon | grep "port 9092" | awk '{ print $1 }'`
done

echo "Remove $path"
rm -rf $path

WEBPATH="/var/www/Transmission"

#remove bin
rm -f /usr/sbin/transmission-daemon-addon
rm -f /usr/sbin/transmission-remote-addon

#remove lib

#remove web
rm -rf $WEBPATH
