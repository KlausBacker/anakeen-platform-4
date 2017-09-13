#!/usr/bin/env bash
if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2/bootstrap
mkdir -p $DEST_PATH/js
mv $BASE_PATH/bootstrap/dist/css $DEST_PATH
mv $BASE_PATH/bootstrap/fonts $DEST_PATH
mv $BASE_PATH/bootstrap/js/*.js $DEST_PATH/js
mv $BASE_PATH/bootstrap/dist/js/bootstrap.js $DEST_PATH/js
mv $BASE_PATH/bootstrap/dist/js/bootstrap.min.js $DEST_PATH/js
mv $BASE_PATH/bootstrap/less $DEST_PATH
