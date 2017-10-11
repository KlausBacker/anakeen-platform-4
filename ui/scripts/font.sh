#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/roboto-fontface/
DEST_PATH=$2
mkdir -p "$DEST_PATH"/roboto-fontface
rsync --delete -azvr "$BASE_PATH" "$DEST_PATH"/roboto-fontface