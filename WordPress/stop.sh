#!/bin/sh

echo "Addon Wordpress (stop.sh): stoping ..."

echo "Addon Wordpress (stop.sh): remove webpages ..."
#rm -f /var/www/wordpress 2>/dev/null
loadphp remove wordpress

echo "Addon Wordpress (stop.sh): Done ..."
