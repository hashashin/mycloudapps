#!/bin/sh
path=$1

#echo "starting Dropbox..." > /dev/console

#start daemon
dropnasctl --init
if [ -e ${path}/dropnas.xml ]; then
	dropnasctl --set_module_state 1
fi

#cmd on start daemon

