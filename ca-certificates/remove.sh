#!/bin/sh

INSTALL_DIR=$1

rm -f /var/www/ca-certificates
rm -f /usr/share/ca-certificates

rm -rf $INSTALL_DIR
