#!/bin/sh
path=$1
WEBPATH="/var/www/phpSysInfo"

ln -sf $path/web $WEBPATH
