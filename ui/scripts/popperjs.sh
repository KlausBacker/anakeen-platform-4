#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/popper.js
DEST_PATH=$2/popper.js
mkdir -p "$DEST_PATH"
mv "$BASE_PATH"/* "$DEST_PATH"/
pwd
cp $1/../scripts/globalPopper.js "$DEST_PATH"
