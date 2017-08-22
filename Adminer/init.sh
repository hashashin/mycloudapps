#!/bin/sh
path=$1
WEBPATH="/var/www/Adminer"

ln -sf $path/web $WEBPATH
