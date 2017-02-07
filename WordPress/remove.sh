#!/bin/sh

path=$1

echo "Addon Wordpress (remove.sh): removing ..."

echo "Addon Wordpress (remove.sh): destroy MySQL database ..."
mysqlmgr -d -u WordPress -p XP4VddgD0zd8IbKQ -n WordPress
rm -f /mnt/HD_a4/.@database@/WordPress >/dev/null
rm -f /var/www/WordPress >/dev/null
rm -f /var/www/wordpress >/dev/null

echo "Addon Wordpress (remove.sh): remove all installed files ..."
rm -rf $path

loadphp remove WordPress

echo "Addon Wordpress (remove.sh): Done ..."
