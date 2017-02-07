#!/bin/sh

path_src=$1
path_des=$2

APKG_PATH=$1
APKG_MODULE="dropbox"
APKG_MODULE_SettingFile="dropnas.xml"
APKG_BACKUP_PATH=/tmp/${APKG_MODULE}_backup

#stop daemon
#remove link

#echo "preinstalling Dropbox..." > /dev/console

# backup config files and users settings
if [ ! -d ${APKG_BACKUP_PATH} ] ; then
	mkdir -p ${APKG_BACKUP_PATH}
fi

# copy config to tmp dir
if [ -e "${APKG_PATH}/${APKG_MODULE_SettingFile}" ]; then
	cp -af "${APKG_PATH}/${APKG_MODULE_SettingFile}" ${APKG_BACKUP_PATH}
fi

#cmd on reinstall
rm -rf $path_des
touch /tmp/dropnas_reinstall

#copy file to installed directory
