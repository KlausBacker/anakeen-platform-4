#!/usr/bin/env bash

set -e

LIB_SRC=./node_modules
DEST_PATH=../Document-uis/src/public/uiAssets
THIRD_PARTY=$DEST_PATH/externals
ANAKEEN=$DEST_PATH/anakeen
mkdir -p "$THIRD_PARTY"
mkdir -p "$ANAKEEN"
for script in scripts/*.sh
do
    if [ "$script" != "$0" ]; then
        "$script" "$LIB_SRC" "$THIRD_PARTY"
    fi
done