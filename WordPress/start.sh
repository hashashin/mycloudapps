#!/bin/sh
path=$1

echo "Addon Wordpress (start.sh): starting ..."

echo "Addon Wordpress (start.sh): linking database ..."
if [ -e /mnt/HD_a4/.@database@ ]; then
	rm -f /mnt/HD_a4/.@database@/WordPress
	ln -sf $path/database/WordPress /mnt/HD_a4/.@database@/ 2>/dev/null
else
	rm -f /mnt/HD_b4/.@database@/WordPress
	ln -sf $path/database/WordPress /mnt/HD_b4/.@database@/ 2>/dev/null
fi

echo "Addon Wordpress (start.sh): linking webpages ..."
rm -f /var/www/WordPress
ln -s $path/WordPress /var/www/WordPress 2>/dev/null

ln -s /mnt/HD/HD_a2/Nas_Prog/WordPress/WordPress /var/www/wordpress

loadphp add WordPress

echo "Addon Wordpress (start.sh): Done ..."
