#!/bin/sh
path=$1
WEBPATH="/var/www/phpweather"

ln -sf $path/web $WEBPATH
