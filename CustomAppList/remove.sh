#!/bin/sh

INSTALL_DIR=$1
ORIGINAL_FILE=/var/www/xml/app_info.xml
DEFAULT_FILE=$INSTALL_DIR/app_info.xml.orig

rm $ORIGINAL_FILE
cp $DEFAULT_FILE $ORIGINAL_FILE
mv $ORIGINAL_FILE.orig $ORIGINAL_FILE
rm -f /var/www/CustomAppList
rm -rf $INSTALL_DIR
