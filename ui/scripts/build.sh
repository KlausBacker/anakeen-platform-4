#!/usr/bin/env bash

LIB_SRC=./node_modules
DEST_PATH=../Document-uis/src/public/uiAssets
THIRD_PARTY=$DEST_PATH/externals
ANAKEEN=$DEST_PATH/anakeen
rm -rf $THIRD_PARTY
rm -rf $LIB_SRC
npm install
mkdir -p $THIRD_PARTY
mkdir -p $ANAKEEN
for script in scripts/*.sh
do
    if [ $script != $0 ]; then
        $script $LIB_SRC $THIRD_PARTY
    fi
done