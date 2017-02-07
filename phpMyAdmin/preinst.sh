#!/bin/sh

path_src=$1
path_des=$2

APKG_PATH=$1
APKG_WWW_DIR="/var/www/phpMyAdmin"
APKG_MODULE="phpMyAdmin"
APKG_MODULE_WEB_DIR="phpMyAdmin-4.6.5.2"
APKG_MODULE_SettingFile="config.inc.php"
APKG_BACKUP_PATH=${APKG_PATH}/../${APKG_MODULE}_backup
#stop daemon

#remove link
rm -rf $APKG_WWW_DIR  2> /dev/null

# backup config files and users settings
if [ ! -d ${APKG_BACKUP_PATH} ] ; then
	mkdir -p ${APKG_BACKUP_PATH}
fi
# copy config to tmp dir
cp -af $APKG_PATH/${APKG_MODULE_WEB_DIR}/${APKG_MODULE_SettingFile} ${APKG_BACKUP_PATH}

#cmd on reinstall
rm -rf $path_des

#copy file to installed directory

