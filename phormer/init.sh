#!/bin/sh
path=$1
WEBPATH="/var/www/phormer"

ln -sf $path/web $WEBPATH

mkdir -p $WEBPATH/data
mkdir -p $WEBPATH/images
mkdir -p $WEBPATH/temp
cp -a /mnt/HD/HD_a2/.temp/phormer/data/* $WEBPATH/data/
cp -a /mnt/HD/HD_a2/.temp/phormer/images/* $WEBPATH/images/
cp -a /mnt/HD/HD_a2/.temp/phormer/temp/* $WEBPATH/temp/
rm -rf /mnt/HD/HD_a2/.temp/phormer
chmod 777 -R $WEBPATH
