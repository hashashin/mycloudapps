#!/bin/sh
path_src=${1}

#killed by stop.sh
#echo "aMule stop" > /dev/console
#${path_src}/bin/amulecmd -P admin -c shutdown
#kill -9 `pidof amuled`
#kill -9 `pidof amuleweb`


aMule_conf_path=${path_src}/.aMule/amule.conf
tmp_conf_path=/tmp/amule.conf

cp -f ${aMule_conf_path} ${tmp_conf_path}	#for re-install




