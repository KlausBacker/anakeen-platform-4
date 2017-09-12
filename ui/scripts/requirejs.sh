#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2
mkdir -p $DEST_PATH/RequireJS
mv $BASE_PATH/requirejs/require.js $DEST_PATH/RequireJS
mv $BASE_PATH/requirejs-text/text.js $DEST_PATH/RequireJS
