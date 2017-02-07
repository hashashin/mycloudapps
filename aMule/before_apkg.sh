#!/bin/sh
THIS_DIR=`dirname "${0}"`

MODULE_NAME=`echo "${THIS_DIR}" | awk 'BEGIN{FS="/"}{print $NF}'`
PREINST_SRC=${THIS_DIR}/preinst.sh
PREINST_DES=${THIS_DIR}/../../${MODULE_NAME}/preinst.sh

#echo "THIS_DIR=$THIS_DIR" > /dev/console
#echo "MODULE_NAME=$MODULE_NAME" > /dev/console
#echo "PREINST_SRC=$PREINST_SRC" > /dev/console
#echo "PREINST_DES=$PREINST_DES" > /dev/console

cp -vf ${PREINST_SRC} ${PREINST_DES}

