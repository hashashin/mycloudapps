#!/bin/sh
WEBPATH="/var/www/phormer"
mkdir -p /mnt/HD/HD_a2/.temp/phormer/data
mkdir -p /mnt/HD/HD_a2/.temp/phormer/images
mkdir -p /mnt/HD/HD_a2/.temp/phormer/temp
cp -a $WEBPATH/data/* /mnt/HD/HD_a2/.temp/phormer/data
cp -a $WEBPATH/images/* /mnt/HD/HD_a2/.temp/phormer/images
cp -a $WEBPATH/temp/* /mnt/HD/HD_a2/.temp/phormer/temp
