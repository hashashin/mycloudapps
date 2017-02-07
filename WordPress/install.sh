#!/bin/sh

path_src=$1
path_des=$2

echo "Addon Wordpress (install.sh): installing ..."
cp -R $path_src $path_des

if [ -d /mnt/HD/HD_a2/.systemfile/wp-backup/database ]; then
	echo "Addon Wordpress (install.sh): restore database ..."
	cp -R /mnt/HD/HD_a2/.systemfile/wp-backup/database $path_des/WordPress/
	rm -rf /mnt/HD/HD_a2/.systemfile/wp-backup
fi

echo "Addon Wordpress (install.sh): Done ..."
