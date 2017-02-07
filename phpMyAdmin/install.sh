#!/bin/sh

path_src=$1
path_des=$2

APKG_WWW_DIR="/var/www/phpMyAdmin"
APKG_MODULE="phpMyAdmin"
APKG_MODULE_WEB_DIR="phpMyAdmin-4.6.5.2"
APKG_MODULE_SettingFile="config.inc.php"
APKG_PATH=${path_des}/${APKG_MODULE}
APKG_BACKUP_PATH=${APKG_PATH}/../${APKG_MODULE}_backup

cp -R $path_src $path_des

# restore config files if they are saved in preinst.sh (or before_apkg.sh)
if [ -d ${APKG_BACKUP_PATH} ] ; then
	#copy setting file
	cp -af ${APKG_BACKUP_PATH}/${APKG_MODULE_SettingFile} ${APKG_PATH}/${APKG_MODULE_WEB_DIR}/
	rm -rf ${APKG_BACKUP_PATH}
else
	mysql --user=root --password=XP4VddgD0zd8IbKQ < $path_des/phpMyAdmin/admin.sql
fi
