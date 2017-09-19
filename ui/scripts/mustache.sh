#!/usr/bin/env bash
set -e
if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2/mustache.js
mkdir -p "$DEST_PATH"
rsync --delete -azvr "$BASE_PATH"/mustache/README.md "$DEST_PATH"/
rsync --delete -azvr "$BASE_PATH"/mustache/mustache*.js "$DEST_PATH"/