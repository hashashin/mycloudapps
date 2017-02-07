#!/bin/sh
path=$1
WEBPATH="/var/www/pawfaliki"

ln -sf $path/web $WEBPATH
mkdir -p $WEBPATH/PawfalikiPages
mkdir -p $WEBPATH/PawfalikiTemp
cp -a /mnt/HD/HD_a2/.temp/PawfalikiPages/* $WEBPATH/PawfalikiPages/
cp -a /mnt/HD/HD_a2/.temp/PawfalikiTemp/* $WEBPATH/PawfalikiTemp/
rm -rf /mnt/HD/HD_a2/.temp/PawfalikiPages /mnt/HD/HD_a2/.temp/PawfalikiTemp
chmod -R 777 $WEBPATH/PawfalikiPages $WEBPATH/PawfalikiTemp
