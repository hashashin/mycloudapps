#!/bin/sh

INSTALL_DIR=$1

# tmux
rm -f /bin/tmux
rm -f /etc/tmux.conf

# mc
rm -f /bin/mc
rm -f /bin/mcview
rm -f /bin/mcedit
rm -f /bin/mcdiff
rm -f /usr/local/libexec
rm -f /usr/local/etc/mc
rm -f /usr/local/share/locale
rm -f /usr/local/share/man
rm -f /usr/local/share/mc
# app
rm -f /var/www/UtilsUpdate-extras
rm -rf $INSTALL_DIR
