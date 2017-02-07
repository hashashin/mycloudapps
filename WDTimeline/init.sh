#!/bin/sh
path=$1
WEBPATH="/var/www/WDTimeline"

ln -sf $path/web $WEBPATH
