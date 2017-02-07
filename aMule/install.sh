#!/bin/sh
path_src=$1
path_des=$2

aMule_conf_path=${path_des}/aMule/.aMule/amule.conf
tmp_conf_path=/tmp/amule.conf


cp -R $path_src $path_des

[ -e ${tmp_conf_path} ] && mv ${tmp_conf_path} ${aMule_conf_path}
