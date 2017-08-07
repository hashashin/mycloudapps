#!/bin/sh

INSTALL_DIR=$1
MODULES=/usr/local/modules
# rsync
ln -sf $MODULES/sbin/rsync /usr/sbin/rsync

# ssh
sudo killall sshd
ln -sf $MODULES/bin/scp /usr/bin/scp
ln -sf $MODULES/sbin/sshd /usr/sbin/sshd
ln -sf $MODULES/bin/ssh /usr/bin/ssh
ln -sf $MODULES/bin/ssh-keygen /usr/bin/ssh-keygen
ln -sf $MODULES/bin/sftp /usr/bin/sftp
ln -sf $MODULES/bin/sftp-server /usr/bin/sftp-server
# ssh add new
rm -f /usr/bin/ssh-add
sudo /usr/sbin/sshd -f /etc/ssh/sshd_config

# add which, who, whoami, whois, nc, hwclock, less, free, pstree via busybox
rm -f /bin/which
rm -f /bin/who
rm -f /bin/whoami
rm -f /bin/whois
rm -f /sbin/nc
rm -f /sbin/hwclock
rm -f /bin/less
ln -sf /bin/busybox /usr/bin/free
rm -f /usr/bin/pstree
rm -f /usr/sbin/fdisk
ln -sf /bin/busybox /bin/tar
rm -f /bin/cal
ln -sf /bin/busybox /bin/ash
ln -sf /bin/busybox /bin/sh
# busybox itself
$MODULES/bin/busybox ln -sf $MODULES/bin/busybox /bin/busybox
rm -f /bin/busybox-1.26.2

# wget
ln -sf $MODULES/bin/wget /usr/bin/wget

# nano
rm -f /usr/bin/nano
rm -f /etc/nanorc

# mutt
ln -sf $MODULES/bin/mutt /usr/bin/mutt

# openvpn
ln -sf $MODULES/sbin/openvpn /usr/sbin/openvpn

# tcpdump
rm -f /usr/sbin/tcpdump

# procps ,pgrep,ps,top,vmstat,w,tload,slabtop
rm -f /lib/libprocps.so.6
rm -f /usr/bin/pgrep
ln -sf /bin/busybox /bin/ps
ln -sf /bin/busybox /usr/bin/top
rm -f /usr/bin/vmstat
rm -f /bin/w
rm -f /bin/tload
rm -f /bin/slabtop

# nmap, libncursesw.so.5.7, ncdu, htop, libpcap.so.1.6.2, nping, ncat, ndiff
rm -f /usr/sbin/nmap
rm -f /usr/sbin/nping
rm -f /usr/sbin/ncat
rm -f /usr/sbin/ndiff
rm -f /usr/local/share/nmap
rm -f /lib/libncursesw.so.5
rm -f /usr/bin/ncdu
rm -f /usr/bin/htop
rm -f /lib/libpcap.so.1

rm -f /var/www/UtilsUpdate
rm -rf $INSTALL_DIR
