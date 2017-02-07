#!/bin/sh

#remove
#rm -f /lib/libpng12.so.0 2> /dev/null
rm -f /lib/libwx_baseu-2.8.so.0 2> /dev/null
rm -f /lib/libwx_baseu_net-2.8.so.0 2> /dev/null

rm -f /usr/sbin/amuled 2> /dev/null
rm -f /usr/sbin/amulecmd 2> /dev/null
rm -f /usr/sbin/amuleweb 2> /dev/null
rm -f /usr/sbin/ed2k 2> /dev/null
rm -f /usr/sbin/amule_start 2> /dev/null
rm -f /usr/sbin/inotify_amule 2> /dev/null

#remove webpage,function,css         
rm -rf /var/www/aMule/
