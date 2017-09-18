#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/
DEST_PATH=$2/jquery-dataTables
if [ ! -d ./DataTables ]; then
    git clone git@github.com:DataTables/DataTables.git
fi
mkdir -p "$DEST_PATH"
cp -r ./DataTables/media/css "$DEST_PATH"
cp -r ./DataTables/media/images "$DEST_PATH"
cp -r ./DataTables/media/js "$DEST_PATH"