#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/typeface-titillium-web/files
DEST_PATH=$2
mkdir -p "$DEST_PATH"/titillium-web-font
FILES=$(ls "$BASE_PATH" | grep "titillium-web-latin-\(300\|300italic\|600\|700\)\.woff2\?")
for fontFile in $FILES
do
    rsync --delete -azvr "$BASE_PATH"/"$fontFile" "$DEST_PATH"/titillium-web-font
done