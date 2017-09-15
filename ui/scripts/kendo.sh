#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2/KendoUI
mkdir -p $DEST_PATH/js $DEST_PATH/bootstrap-theme
cp -r ./scripts/kendo-builder $DEST_PATH/
mv $BASE_PATH/kendo-ui-core/src/* $DEST_PATH/js/
mv $BASE_PATH/kendo-ui-core/styles $DEST_PATH/styles
mv $BASE_PATH/@progress/kendo-theme-bootstrap/dist $DEST_PATH/bootstrap-theme
mv $BASE_PATH/@progress/kendo-theme-bootstrap/scss $DEST_PATH/bootstrap-theme
mv $BASE_PATH/@progress/kendo-theme-bootstrap/modules $DEST_PATH/bootstrap-theme
$BASE_PATH/.bin/r.js -o $DEST_PATH/kendo-builder/build.js && $BASE_PATH/.bin/r.js -o $DEST_PATH/kendo-builder/build-minified.js
rm -rf $DEST_PATH/kendo-builder