#!/bin/sh

path=$1

echo "Addon Wordpress (preinst.sh): Try reinstalling ..."

echo "Addon Wordpress (preinst.sh): backup database ..."
rm -rf /mnt/HD/HD_a2/.systemfile/wp-backup
mkdir -p /mnt/HD/HD_a2/.systemfile/wp-backup
cp -R $path/database /mnt/HD/HD_a2/.systemfile/wp-backup/

echo "Addon Wordpress (preinst.sh): Done ..."
