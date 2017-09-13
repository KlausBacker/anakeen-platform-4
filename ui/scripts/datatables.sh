#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/
DEST_PATH=$2/jquery-dataTables
git clone git@github.com:DataTables/DataTables.git
mkdir -p $DEST_PATH
mv ./DataTables/media/css $DEST_PATH
mv ./DataTables/media/images $DEST_PATH
mv ./DataTables/media/js $DEST_PATH
rm -rf ./DataTables