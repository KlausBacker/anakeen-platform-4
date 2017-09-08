#!/usr/bin/env bash

LIB_SRC=./node_modules
DEST_PATH=../Document-uis/src/public/lib
mkdir -p $DEST_PATH
for script in scripts/*.sh
do
    if [ $script != $0 ]; then
        $script $LIB_SRC $DEST_PATH
    fi
done