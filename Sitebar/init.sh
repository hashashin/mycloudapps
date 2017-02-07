#!/bin/sh
path=$1
WEBPATH="/var/www/Sitebar"

ln -sf $path/web $WEBPATH
mv /mnt/HD/HD_a2/.temp/config.inc.php $WEBPATH/adm/
