#!/bin/sh
# -----------------------------------------------------------------------------
# dropnas_helper.sh
# A script to help dropnas app
#
# !!! The helper_def.h must be also changed if any constant changing !!!
# 
# Functions:
# aciton [param1] [param2]
#
# resume-timer [job name] [seconds to resume job]
#     This will run as a timer to resume job.
#
# created by Tim Tsay @ 2015-03-12
# -----------------------------------------------------------------------------

MYPID=$$
PROG_NAME=$0
action=$1
param1=$2
param2=$3


resume_timer () {
	JOB_NAME=$1
	SECONDS=$2
	TIMER_PID=$MYPID
	
	if [ -z "$JOB_NAME" -o -z "$SECONDS" ]; then
		echo "parameter error"
		return 0;
	fi
	
	if [ $SECONDS -le 0 ]; then
		echo "parameter error"
		return 0;
	fi
	
	PID_PATH="/tmp/dropnas_${JOB_NAME}_resume-timer.pid"
	echo $TIMER_PID > $PID_PATH
	
	sleep $SECONDS
	
	rm $PID_PATH
	
	echo "${PROG_NAME}: resuming job: ${JOB_NAME} ..."
	dropnasctl --resume -j $JOB_NAME &
	
	return 1;
}

case $action in
resume-timer)
	if resume_timer $param1 $param2
	then
		echo "run resume-timer failed for job:$param1"
		exit 1
	fi
	exit 0
;;
*)
	echo "Action:$action is not supported."
	exit 0
;;
esac

exit 1