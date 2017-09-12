#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2
mkdir -p $DEST_PATH/TraceKit/
mv $BASE_PATH/tracekit/README.md $DEST_PATH/TraceKit/
mv $BASE_PATH/tracekit/tracekit.js $DEST_PATH/TraceKit/