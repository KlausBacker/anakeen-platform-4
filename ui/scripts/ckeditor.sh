#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2
PACKAGE_VERSION=$(npm view ckeditor version)
NEW_DIR=$(echo $PACKAGE_VERSION | cut -d'.' -f1)
FILES_LIST=$(ls $BASE_PATH/ckeditor)
mkdir -p $DEST_PATH/ckeditor/$NEW_DIR
mv $BASE_PATH/ckeditor/* $DEST_PATH/ckeditor/$NEW_DIR