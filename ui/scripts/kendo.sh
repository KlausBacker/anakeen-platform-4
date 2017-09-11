#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "Usage : $0 lib_sources_dir destination_dir"
fi
BASE_PATH=$1
DEST_PATH=$2
PACKAGE_VERSION=$(npm view kendo-ui-core version)
mkdir -p $DEST_PATH/KendoUI/$PACKAGE_VERSION/
$BASE_PATH/.bin/r.js -o scripts/build.js && $BASE_PATH/.bin/r.js -o scripts/build-minified.js
mv $BASE_PATH/kendo-ui-core/js $DEST_PATH/KendoUI/$PACKAGE_VERSION/
mv $BASE_PATH/kendo-ui-core/css $DEST_PATH/KendoUI/$PACKAGE_VERSION/styles
mv $DEST_PATH/KendoUI/js/* $DEST_PATH/KendoUI/$PACKAGE_VERSION/js
ln -s $DEST_PATH/KendoUI/$PACKAGE_VERSION $DEST_PATH/KendoUI/ddui
rmdir $DEST_PATH/KendoUI/js
