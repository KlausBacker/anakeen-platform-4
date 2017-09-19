#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1/mustache
DEST_PATH=$2/mustache.php
mkdir -p $DEST_PATH
rsync --delete -azvr $BASE_PATH/mustache/* $DEST_PATH/