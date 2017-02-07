#!/bin/sh
path=$1
WEBPATH="/var/www/WDFacebook"

ln -sf $path/web $WEBPATH
