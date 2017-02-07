#!/bin/sh

INSTALL_DIR=$1

ln -sf $INSTALL_DIR/certs /usr/share/ca-certificates
ln -sf $INSTALL_DIR/web /var/www/ca-certificates

