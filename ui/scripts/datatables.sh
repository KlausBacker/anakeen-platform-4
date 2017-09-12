#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/../scripts/datatables-content/media
DEST_PATH=$2/jquery-dataTables
mkdir -p $DEST_PATH
cp -R $BASE_PATH/css $DEST_PATH
cp -R $BASE_PATH/images $DEST_PATH
cp -R $BASE_PATH/js $DEST_PATH