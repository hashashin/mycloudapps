#!/bin/sh
INSTALL_DIR=$1

sudo /usr/sbin/hamachid -c ${INSTALL_DIR}/config
sleep 5
sudo /usr/bin/hamachi login
