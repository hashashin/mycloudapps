#!/bin/sh

path=$1

echo "Link file from : "$path

#link library
#ln -s $path/lib/libpng12.so.0.35.0 /lib/libpng12.so.0
ln -s $path/lib/libwx_baseu-2.8.so.0.5.0 /lib/libwx_baseu-2.8.so.0
ln -s $path/lib/libwx_baseu_net-2.8.so.0.5.0 /lib/libwx_baseu_net-2.8.so.0

#link program,cgi
ln -s $path/bin/amulecmd /usr/sbin/amulecmd
ln -s $path/bin/amuled /usr/sbin/amuled
ln -s $path/bin/amuleweb /usr/sbin/amuleweb
ln -s $path/bin/ed2k /usr/sbin/ed2k
ln -s $path/bin/amule_start /usr/sbin/amule_start


cp -f $path/*.cgi /var/www/cgi-bin/

#create a folder for webpage
mkdir /var/www/aMule/

WEBPATH="/var/www/aMule/"

#webpage,function,css,js,cgi
ln -s $path/web/css/*.css $WEBPATH
ln -s $path/web/images/*.png $WEBPATH
ln -s $path/web/js/*.js $WEBPATH
ln -s $path/web/js/jQuery/*.js $WEBPATH
ln -s $path/web/js/jScrollPane/images/*.png $WEBPATH
ln -s $path/web/js/jScrollPane/scripts/*.js $WEBPATH
ln -s $path/web/js/jScrollPane/styles/*.css $WEBPATH
ln -s $path/web/js/NiftyCube/*.txt $WEBPATH
ln -s $path/web/js/NiftyCube/*.css $WEBPATH
ln -s $path/web/js/NiftyCube/*.js $WEBPATH
ln -s $path/web/js/curvycorners-2.0.4/*.js $WEBPATH
ln -s $path/web/*.html $WEBPATH
ln -s $path/web/gui_info.xml $WEBPATH
ln -s $path/web/desc.xml $WEBPATH
if [ -f $path/aMule.png ]; then
	ln -s $path/aMule.png $WEBPATH
fi	

if [ -f $path/aMule.html ]; then
	ln -s $path/aMule.html $WEBPATH
fi
