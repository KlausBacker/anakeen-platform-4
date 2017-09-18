#!/usr/bin/env bash

set -e

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/
DEST_PATH=$2/jquery-dataTables

mkdir -p "$DEST_PATH"
curl -sL "https://github.com/DataTables/DataTables/tarball/1.10.16"|tar -C "$DEST_PATH" --strip-components=2 -zxvf - --wildcards "*/media/css" "*/media/js" "*/media/images"

