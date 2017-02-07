#!/bin/sh

path=$1
echo "aMule start $path"

rwpath=`echo $path | sed -e 's|/Nas_Prog||'`
sleep 1

mkdir -p $rwpath
mkdir -p $rwpath/Temp
mkdir -p $rwpath/Incoming
chmod 777 -R $rwpath

smbcmd -m

HOME=$path
amuled -o -f --config-dir="$path/.aMule/"
