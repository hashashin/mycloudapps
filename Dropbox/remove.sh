#!/bin/sh

#echo "removing Dropbox..." > /dev/console

path=$1
source ${path}/inc.sh

if [ -e /tmp/dropnas_reinstall ]; then
	rm -f /tmp/dropnas_reinstall 2>/dev/null
else
	if [ ! -e /usr/sbin/dropnasctl ]; then
		init_dropbox_component $path
	fi
	dropnasctl --factory_reset
fi

remove_dropbox_component $path

#remove intstalled directory
rm -rf $path
