#!/bin/sh
path=$1
WEBPATH="/var/www/Adminer/"

mkdir -p $WEBPATH
cp -a $path/web/* $WEBPATH
