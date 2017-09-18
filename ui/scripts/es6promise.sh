#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2/es6-promise
mkdir -p "$DEST_PATH"
mv "$BASE_PATH"/es6-promise/dist/es6-promise.js "$DEST_PATH"
mv "$BASE_PATH"/es6-promise/dist/es6-promise.m*.js "$DEST_PATH"