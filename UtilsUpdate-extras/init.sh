#!/bin/sh

INSTALL_DIR=$1
# app
ln -sf $INSTALL_DIR/web /var/www/UtilsUpdate-extras
# tmux
ln -sf $INSTALL_DIR/files/tmux /bin/tmux
ln -sf $INSTALL_DIR/files/tmux.conf /etc/tmux.conf
# mc
ln -sf $INSTALL_DIR/files/mc/bin/mc /bin/mc
ln -sf $INSTALL_DIR/files/mc/bin/mcview /bin/mcview
ln -sf $INSTALL_DIR/files/mc/bin/mcedit /bin/mcedit
ln -sf $INSTALL_DIR/files/mc/bin/mcdiff /bin/mcdiff
ln -sf $INSTALL_DIR/files/mc/libexec /usr/local/libexec
ln -sf $INSTALL_DIR/files/mc/etc/mc  /usr/local/etc/mc
ln -sf $INSTALL_DIR/files/mc/share/locale /usr/local/share/locale
ln -sf $INSTALL_DIR/files/mc/share/man /usr/local/share/man
ln -sf $INSTALL_DIR/files/mc/share/mc /usr/local/share/mc

