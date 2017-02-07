#!/bin/sh
echo "aMule stop"
amulecmd -P admin -c shutdown
kill -9 `pidof amuled`
kill -9 `pidof amuleweb`