#!/bin/sh

remove_dropbox_component() {
	# $1 = Dropbox addon dir path
	addon_path=$1
	rm -f /usr/sbin/dropnascmd 2>/dev/null
	rm -f /usr/sbin/dropnasctl 2>/dev/null
	rm -f /usr/sbin/dropnasd 2>/dev/null
	rm -f /usr/sbin/dropnas_monitor 2>/dev/null
	rm -f /usr/sbin/dropnas_helper.sh 2>/dev/null
	rm -f /lib/libdropnas.so 2>/dev/null
	rm -f /lib/libdropnasd.so 2>/dev/null
	rm -f /lib/libdropnascom.so 2>/dev/null
	
	if [ -e ${addon_path}/using-app-libjson ]; then
		rm -f ${addon_path}/using-app-libjson 2>/dev/null
		rm -f /lib/libjson-c.so.2 2>/dev/null
		rm -f /lib/libjson-c.so 2>/dev/null
	fi
	
	if [ -e ${addon_path}/using-app-libmojibake ]; then
		rm -f ${addon_path}/using-app-libmojibake 2>/dev/null
		rm -f /lib/libmojibake.so 2>/dev/null
	fi
	
	rm -f /etc/dropnas.xml 2>/dev/null

	if [ -e /etc/ssl/certs/using-dbox-app-cacert ]; then
		#echo "removing link to dbox app ca-cert file..." > /dev/console
		rm -f /etc/ssl/certs/using-dbox-app-cacert 2>/dev/null
		rm -f /etc/ssl/certs/ca-certificates.crt 2>/dev/null
	fi
}

check_inst_root_ok() {
	binspc=`du -s -k ${addon_path}/bin/dropnascmd 2>/dev/null | awk '{print $1}'`
	caspc=`du -s -k ${addon_path}/certs 2>/dev/null | awk '{print $1}'`
	needspc=$( expr $binspc + $caspc )
	
	freespc=`df -k -P / | grep -v Filesystem | awk '{print $4}'`
	
	if [ "$freespc" -gt "$needspc" ]; then
		return 0
	fi
	
	return 1
}

init_dropbox_component() {
	# $1 = Dropbox addon dir path
	addon_path=$1
	
	if check_inst_root_ok ; then
		INST_CMD="cp -f"
	else
		INST_CMD="ln -sf"
	fi
	
	${INST_CMD} ${addon_path}/bin/dropnascmd /usr/sbin/dropnascmd
	ln -sf ${addon_path}/bin/dropnasctl /usr/sbin/dropnasctl
	ln -sf ${addon_path}/bin/dropnasd /usr/sbin/dropnasd
	ln -sf ${addon_path}/bin/dropnas_helper.sh /usr/sbin/dropnas_helper.sh
	ln -sf ${addon_path}/lib/libdropnas.so /lib/libdropnas.so
	ln -sf ${addon_path}/lib/libdropnasd.so /lib/libdropnasd.so
	ln -sf ${addon_path}/lib/libdropnascom.so /lib/libdropnascom.so
	
	ln -sf ${addon_path}/bin/dropnas_monitor /usr/sbin/dropnas_monitor
	
	if [ ! -e /lib/libjson-c.so ]; then
		touch ${addon_path}/using-app-libjson
		ln -sf ${addon_path}/lib/libjson-c.so.2 /lib/libjson-c.so.2
		ln -sf /lib/libjson-c.so.2 /lib/libjson-c.so
	fi
	
	if [ ! -e /lib/libmojibake.so ]; then
		touch ${addon_path}/using-app-libmojibake
		ln -sf ${addon_path}/lib/libmojibake.so /lib/libmojibake.so
	fi
	
	if [ ! -e /etc/ssl/certs/ca-certificates.crt ]; then
		#echo "apply app ca-cert..." > /dev/console
		if [ ! -e /etc/ssl/certs ]; then
			mkdir -p /etc/ssl/certs
		fi
		
		${INST_CMD} ${addon_path}/certs/ca-certificates.crt /etc/ssl/certs/ca-certificates.crt
		touch /etc/ssl/certs/using-dbox-app-cacert
	fi
	
	# set config path, it is not exist by default
	ln -s ${addon_path}/dropnas.xml /etc/dropnas.xml
}
