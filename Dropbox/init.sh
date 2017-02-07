#!/bin/sh

path=$1
#echo "intializing Dropbox..." > /dev/console
source ${path}/inc.sh

#remove link
remove_dropbox_component $path

#create link
init_dropbox_component $path

#create a folder for webpage
mkdir /var/www/Dropbox

WEBPATH="/var/www/Dropbox"
ln -s $path/web/* $WEBPATH

#cmd on pre-install

apache restart web