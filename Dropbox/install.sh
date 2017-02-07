#!/bin/sh

path_src=$1
path_des=$2

APKG_PATH=$1
APKG_MODULE="dropbox"
APKG_MODULE_SettingFile="dropnas.xml"
APKG_BACKUP_PATH=/tmp/${APKG_MODULE}_backup

#echo "installing Dropbox..." > /dev/console

change_wdcloud_name() {
	sed -i -e 's/WD My Cloud/WD Cloud/g' ${path_des}/Dropbox/web/desc.xml > /dev/null 2>&1
}

cp -R $path_src $path_des

# restore config files if they are saved in preinst.sh (or before_apkg.sh)
if [ -d ${APKG_BACKUP_PATH} ] ; then
	#copy setting file
	if [ -e ${APKG_BACKUP_PATH}/${APKG_MODULE_SettingFile} ]; then
		cp -af ${APKG_BACKUP_PATH}/${APKG_MODULE_SettingFile} ${path_des}/Dropbox
	fi
	rm -rf ${APKG_BACKUP_PATH}
fi

model_name=`cat /etc/model | awk '{print tolower($0)}'`
if [ "$model_name" = "wdcloud" ]; then
	#echo "This is MirrorMan... Change the WD cloud brand name..." > /dev/console
	change_wdcloud_name
fi
