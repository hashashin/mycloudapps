#!/bin/sh
path=$1
WEBPATH="/var/www/Dokuwiki"

ln -sf $path/web $WEBPATH
