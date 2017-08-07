#!/bin/sh

INSTALL_DIR=$1
# rsync
ln -sf $INSTALL_DIR/files/rsync /usr/sbin/rsync

# add which, who, whoami, whois, nc, hwclock, free, pstree, fdisk, tar, cal
# via busybox
ln -sf $INSTALL_DIR/files/busybox /bin/which
ln -sf $INSTALL_DIR/files/busybox /bin/who
ln -sf $INSTALL_DIR/files/busybox /bin/whoami
ln -sf $INSTALL_DIR/files/busybox /bin/whois
ln -sf $INSTALL_DIR/files/busybox /sbin/nc
ln -sf $INSTALL_DIR/files/busybox /sbin/hwclock
ln -sf $INSTALL_DIR/files/busybox /bin/less
ln -sf $INSTALL_DIR/files/busybox /usr/bin/free
ln -sf $INSTALL_DIR/files/busybox /usr/bin/pstree
ln -sf $INSTALL_DIR/files/busybox /usr/sbin/fdisk
ln -sf $INSTALL_DIR/files/busybox /bin/tar
ln -sf $INSTALL_DIR/files/busybox /bin/cal
ln -sf $INSTALL_DIR/files/busybox /bin/ash
ln -sf $INSTALL_DIR/files/busybox /bin/sh

# more in busybox itself
ln -sf $INSTALL_DIR/files/busybox /bin/busybox-1.26.2
/bin/busybox-1.26.2 ln -sf $INSTALL_DIR/files/busybox /bin/busybox

# ssh update orig
sudo killall sshd # stop sshd
ln -sf $INSTALL_DIR/files/scp /usr/bin/scp
ln -sf $INSTALL_DIR/files/sshd /usr/sbin/sshd
ln -sf $INSTALL_DIR/files/ssh /usr/bin/ssh
ln -sf $INSTALL_DIR/files/ssh-keygen /usr/bin/ssh-keygen
ln -sf $INSTALL_DIR/files/sftp /usr/bin/sftp
ln -sf $INSTALL_DIR/files/sftp-server /usr/bin/sftp-server

# ssh add new
ln -sf $INSTALL_DIR/files/ssh-add /usr/bin/ssh-add
sudo /usr/sbin/sshd -f /etc/ssh/sshd_config # restart sshd

# wget
ln -sf $INSTALL_DIR/files/wget /usr/bin/wget

# nano
ln -sf $INSTALL_DIR/files/nano /usr/bin/nano
ln -sf $INSTALL_DIR/files/nanorc /etc/nanorc

# mutt
ln -sf $INSTALL_DIR/files/mutt /usr/bin/mutt

# openvpn
ln -sf $INSTALL_DIR/files/openvpn /usr/sbin/openvpn

# tcpdump
ln -sf $INSTALL_DIR/files/tcpdump /usr/sbin/tcpdump

# procps pgrep,ps,top,vmstat,w,tload,slabtop
ln -sf $INSTALL_DIR/files/lib/libprocps.so.6.0.0 /lib/libprocps.so.6
ln -sf $INSTALL_DIR/files/pgrep /usr/bin/pgrep
ln -sf $INSTALL_DIR/files/ps /bin/ps
ln -sf $INSTALL_DIR/files/top /usr/bin/top
ln -sf $INSTALL_DIR/files/vmstat /usr/bin/vmstat
ln -sf $INSTALL_DIR/files/w /bin/w
ln -sf $INSTALL_DIR/files/tload /bin/tload
ln -sf $INSTALL_DIR/files/slabtop /bin/slabtop

# nmap, libncursesw.so.5.7, ncdu, htop, libpcap.so.1.6.2, nping, ncat, ndiff
ln -sf $INSTALL_DIR/files/lib/libpcap.so.1.6.2 /lib/libpcap.so.1
ln -sf $INSTALL_DIR/files/nmap /usr/sbin/nmap
ln -sf $INSTALL_DIR/files/nping /usr/sbin/nping
ln -sf $INSTALL_DIR/files/ncat /usr/sbin/ncat
ln -sf $INSTALL_DIR/files/ndiff /usr/sbin/ndiff
ln -sf $INSTALL_DIR/files/share/nmap /usr/local/share/nmap
ln -sf $INSTALL_DIR/files/lib/libncursesw.so.5.7 /lib/libncursesw.so.5
ln -sf $INSTALL_DIR/files/ncdu /usr/bin/ncdu
ln -sf $INSTALL_DIR/files/htop /usr/bin/htop

# web info
ln -sf $INSTALL_DIR/web /var/www/UtilsUpdate
