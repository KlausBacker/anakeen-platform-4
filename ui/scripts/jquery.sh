#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2/jquery
mkdir -p "$DEST_PATH"
mv "$BASE_PATH"/jquery/dist/jquery.js "$DEST_PATH"
mv "$BASE_PATH"/jquery/dist/jquery.min.js "$DEST_PATH"