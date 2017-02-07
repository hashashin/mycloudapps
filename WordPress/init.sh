#!/bin/sh

path=$1

echo "Addon Wordpress (init.sh): init ..."

if [ ! `pidof mysqld` ]; then
	echo "Addon Wordpress (init.sh): invoke MySQL service ..."
	startup-mysql
	sleep 1
fi

echo "Addon Wordpress (init.sh): setup database ..."
ln -s $path/database/WordPress /mnt/HD_a4/.@database@/ 2>/dev/null
mysqlmgr -c -u WordPress -p XP4VddgD0zd8IbKQ -n WordPress
$path/init-wp $path

echo "Addon Wordpress (init.sh): linking webpages ..."
rm -f /var/www/WordPress
ln -s $path/WordPress /var/www/WordPress

ln -s $path/WordPress/desc.xml /var/www/WordPress

mkdir $path/tmp

echo "Addon Wordpress (init.sh): Done ..."
