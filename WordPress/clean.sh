#!/bin/sh

echo "Addon Wordpress (clean.sh): cleaning ..."

echo "Addon Wordpress (clean.sh): clean webpages ..."
rm -f /var/www/WordPress 2>/dev/null
loadphp remove WordPress

echo "Addon Wordpress (clean.sh): destroy MySQL database ..."
#destroy MySQL database .
#mysqlmgr -d -u wordpress -p yBhAUdqCtSRUdegl -n wordpress
rm -f /mnt/HD_a4/.@database@/WordPress >/dev/null

echo "Addon Wordpress (clean.sh): Done ..."
