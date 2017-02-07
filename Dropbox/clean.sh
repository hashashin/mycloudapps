#!/bin/sh

path=$1
source ${path}/inc.sh

#stop daemon
remove_dropbox_component $path

#remove link
rm -rf /var/www/Dropbox/
