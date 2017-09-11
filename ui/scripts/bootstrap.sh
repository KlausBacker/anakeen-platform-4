#!/usr/bin/env bash
if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2
PACKAGE_VERSION=$(npm view bootstrap version)
NEW_DIR=$(echo $PACKAGE_VERSION | cut -d'.' -f1)
mkdir -p $DEST_PATH/bootstrap/$NEW_DIR/js
mv $BASE_PATH/bootstrap/dist/css $DEST_PATH/bootstrap/$NEW_DIR
mv $BASE_PATH/bootstrap/dist/js/bootstrap.js $DEST_PATH/bootstrap/$NEW_DIR/js
mv $BASE_PATH/bootstrap/dist/js/bootstrap.min.js $DEST_PATH/bootstrap/$NEW_DIR/js
mv $BASE_PATH/bootstrap/dist/fonts $DEST_PATH/bootstrap/$NEW_DIR/
mv $BASE_PATH/bootstrap/less $DEST_PATH/bootstrap/$NEW_DIR/
