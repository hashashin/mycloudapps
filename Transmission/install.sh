#!/bin/sh

[ -f /tmp/debug_apkg ] && echo "APKG_DEBUG: $0 $@" >> /tmp/debug_apkg

path_src=$1
path_des=$2

APKG_MODULE="Transmission"
APKG_PATH=${path_des}/${APKG_MODULE}
APKG_BACKUP_PATH=${APKG_PATH}/../${APKG_MODULE}_backup

mkdir -p ${APKG_BACKUP_PATH}
cp -arf ${APKG_PATH}/config ${APKG_BACKUP_PATH}

cp -rf $path_src $path_des

# restore config files if they are saved in preinst.sh (or before_apkg.sh)
if [ -d ${APKG_BACKUP_PATH}/config ] ; then
	#mv ${APKG_PATH}/config ${APKG_PATH}/new_config
	cp -arf ${APKG_BACKUP_PATH}/config ${APKG_PATH}
	# use new default settings
	#if [ -f ${APKG_PATH}/new_config/settings.json ] ; then
	#	cp -f ${APKG_PATH}/new_config/settings.json ${APKG_PATH}/config/settings.json
	#fi
	#rm -rf ${APKG_PATH}/new_config
	rm -rf ${APKG_BACKUP_PATH}
fi

