#!/bin/sh
INSTALL_DIR=$1

rm -f /usr/sbin/hamachid
rm -f /usr/bin/hamachi
rm -rf $INSTALL_DIR
rm -f /var/www/hamachi
rm -f /var/www/cgi-bin/hamachi.py
