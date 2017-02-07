#!/bin/sh

path=$1

amulecmd -P admin -c shutdown
kill -9 `pidof amuled` 2>/dev/null
kill -9 `pidof amuleweb` 2>/dev/null

echo "Remove $path"
rm -rf $path

#rm -f /lib/libpng12.so.0 2> /dev/null
rm -f /lib/libwx_baseu-2.8.so.0 2> /dev/null
rm -f /lib/libwx_baseu_net-2.8.so.0 2> /dev/null

rm -f /usr/sbin/amuled 2> /dev/null
rm -f /usr/sbin/amulecmd 2> /dev/null
rm -f /usr/sbin/amuleweb 2> /dev/null
rm -f /usr/sbin/ed2k 2> /dev/null
rm -f /usr/sbin/amule_start 2> /dev/null

#remove webpage,function,css         
rm -rf /var/www/aMule/

newvalue=`echo $path | sed -e 's|/Nas_Prog||'`
chmod 777 -R $newvalue
newvalue="${newvalue}/Temp"
rm -r $newvalue


