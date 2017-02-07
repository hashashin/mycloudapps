#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

path=$1
echo "Install Path is $path"

WEBPATH="/var/www/Transmission"
FIRST_MOUNT=/mnt/HD/HD_a2
[ ! -d $FIRST_MOUNT ] && FIRST_MOUNT=/mnt/HD/HD_b2
[ ! -d $FIRST_MOUNT ] && FIRST_MOUNT=/mnt/HD/HD_c2
[ ! -d $FIRST_MOUNT ] && FIRST_MOUNT=/mnt/HD/HD_d2

OLD_DOWNLOAD_FOLDER=$FIRST_MOUNT/Public/Transmission
DOWNLOAD_FOLDER=$FIRST_MOUNT/Transmission
ISUPDATE=

if [ -d ${OLD_DOWNLOAD_FOLDER} ]; then
   mv -f ${OLD_DOWNLOAD_FOLDER} ${DOWNLOAD_FOLDER}
   ISUPDATE="true"
fi

smbif -a ${DOWNLOAD_FOLDER}
chmod a+rw -R ${DOWNLOAD_FOLDER}

counter=10
while [ ! -d "$WEBPATH" -a "$counter" -ne 0 ]
do
	echo waiting some more for pre_inst.sh to finish, count "$counter" > /dev/console
	sleep 1
	counter=`expr $counter - 1`
done

export TRANSMISSION_WEB_HOME=$path/web_transmission
PID_FILE=/var/run/addon_transmission.pid
mkdir -p $path/config
# mkdir -p ${BT_DOWNLOAD_DIR}
if [ -f $path/config/settings.json ] ; then
	echo 'Start Transmission with existance settings.' > /dev/console
	transmission-daemon-addon -M --port 9092 --config-dir $path/config --download-dir $DOWNLOAD_FOLDER --pid-file $PID_FILE
	if [ "${ISUPDATE}" == "true" ]; then
		echo "Yes, it is update" > /dev/console
		sleep 3
		transmission-remote-addon localhost:9092 --torrent all --find ${DOWNLOAD_FOLDER}
		transmission-remote-addon localhost:9092 --torrent all --verify
	fi
else
	echo 'Start Transmission with default settings.' > /dev/console
#	transmission-daemon --allowed *.*.*.* --no-portmap --auth --username admin --password '' --config-dir $path/config --no-watch-dir --no-incomplete-dir --download-dir $FIRST_MOUNT/Downloads
	transmission-daemon-addon --port 9092 --allowed *.*.*.* --portmap --config-dir $path/config --no-watch-dir --no-incomplete-dir --download-dir $DOWNLOAD_FOLDER --pid-file $PID_FILE
fi
sleep 3
#cp -f $path/config/settings.json /web/apkg/$MODULE
# start all torrents. when system starts, the state of torrents sometimes are paused.
transmission-remote-addon localhost:9092 --torrent all --start
