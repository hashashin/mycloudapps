#!/bin/sh

INSTALL_DIR=$1
ORIGINAL_FILE=/var/www/xml/app_info.xml

mv $ORIGINAL_FILE $INSTALL_DIR/$ORIGINAL_FILE.orig
ln -sf $INSTALL_DIR/web /var/www/CustomAppList
