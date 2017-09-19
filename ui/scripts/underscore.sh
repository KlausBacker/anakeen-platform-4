#!/usr/bin/env bash
set -e
if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/underscore
DEST_PATH=$2/underscore
mkdir -p "$DEST_PATH"
rsync --delete -azvr "$BASE_PATH"/*.js "$DEST_PATH"
rsync --delete -azvr "$BASE_PATH"/*.map "$DEST_PATH"
rsync --delete -azvr "$BASE_PATH"/README.md "$DEST_PATH"
rsync --delete -azvr "$BASE_PATH"/LICENSE "$DEST_PATH"