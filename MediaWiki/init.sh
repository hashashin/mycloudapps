#!/bin/sh
path=$1
WEBPATH="/var/www/MediaWiki"

#mkdir -p $WEBPATH
ln -sf $path/web $WEBPATH
cp /mnt/HD/HD_a2/.temp/LocalSettings.php $WEBPATH/
rm -rf /mnt/HD/HD_a2/.temp/LocalSettings.php
