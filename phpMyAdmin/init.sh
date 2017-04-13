#!/bin/sh

path=$1
APKG_WWW_DIR="/var/www/phpMyAdmin"
APKG_MODULE_WEB_DIR="phpMyAdmin-4.0.10.20"
APKG_ICON_FILE_NAME="phpMyAdmin.png"
APKG_MULTI_LANG_DESC_XML="desc.xml"

#remove link
rm -rf $APKG_WWW_DIR  2> /dev/null

#create link
mkdir /var/www/phpMyAdmin
ln -sf $path/$APKG_MODULE_WEB_DIR/phpMyAdmin_on.png $APKG_WWW_DIR/phpMyAdmin_on.png
ln -sf $path/$APKG_MODULE_WEB_DIR/phpMyAdmin_off.png $APKG_WWW_DIR/phpMyAdmin_off.png
ln -sf $path/$APKG_MODULE_WEB_DIR/phpMyAdmin_display.png $APKG_WWW_DIR/phpMyAdmin_display.png
ln -sf $path/$APKG_MODULE_WEB_DIR/$APKG_ICON_FILE_NAME $APKG_WWW_DIR/$APKG_ICON_FILE_NAME
ln -sf $path/$APKG_MODULE_WEB_DIR/$APKG_MULTI_LANG_DESC_XML $APKG_WWW_DIR/$APKG_MULTI_LANG_DESC_XML

#link launch interface
ln -sf $path/$APKG_MODULE_WEB_DIR/phpMyAdmin.html $APKG_WWW_DIR/phpMyAdmin.html
ln -sf $path/$APKG_MODULE_WEB_DIR/phpMyAdmin_sub.html $APKG_WWW_DIR/phpMyAdmin_sub.html
ln -sf $path/$APKG_MODULE_WEB_DIR/help_phpMyAdmin.html $APKG_WWW_DIR/help_phpMyAdmin.html
ln -sf $path/$APKG_MODULE_WEB_DIR/custom $APKG_WWW_DIR/custom 

#cmd on pre-install

