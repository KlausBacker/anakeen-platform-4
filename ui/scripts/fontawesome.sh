#!/usr/bin/env bash
if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2
mkdir -p $DEST_PATH/font-awesome
mv $BASE_PATH/font-awesome/css $DEST_PATH/font-awesome
mv $BASE_PATH/font-awesome/scss $DEST_PATH/font-awesome
mv $BASE_PATH/font-awesome/less $DEST_PATH/font-awesome
mv $BASE_PATH/font-awesome/fonts $DEST_PATH/font-awesome