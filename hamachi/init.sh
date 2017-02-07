#!/bin/sh

INSTALL_DIR=$1

ln -sf $INSTALL_DIR/hamachid /usr/sbin/hamachid
ln -sf $INSTALL_DIR/hamachid /usr/bin/hamachi
cp -a /mnt/HD/HD_a2/.temp/hamachi/* $INSTALL_DIR/config/
rm -rf /mnt/HD/HD_a2/.temp/hamachi
ln -sf $INSTALL_DIR/web /var/www/hamachi
ln -sf $INSTALL_DIR/hamachi.py /var/www/cgi-bin/hamachi.py
